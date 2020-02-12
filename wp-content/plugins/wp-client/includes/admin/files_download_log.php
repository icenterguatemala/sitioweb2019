<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclients_content' ) );
}

global $wpdb;
if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=files_downloads';
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
}


if ( !ini_get( 'safe_mode' ) ) {
    @set_time_limit(0);
}

$filter             = '';
$where_search       = '';
$where_filter       = '';
$where_manager      = '';

$wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

//filter
if ( isset( $_GET['filter'] ) ) {
    switch ( $_GET['filter'] ) {
        case 'downloaded_by':
            if ( isset( $_GET['downloaded_by'] ) ) {
                $downloaded_by = $_GET['downloaded_by'];
                $where_filter .= " AND dl.client_id='" . (int)$downloaded_by . "'" ;
            }
            if ( isset( $_GET['file'] ) && !empty( $_GET['file'] ) && 'all' != $_GET['file'] ) {
                $file = $_GET['file'];
                $where_filter .= " AND dl.file_id='" . (int)$file . "'" ;
            }
            break;
        case 'file':
            if ( isset( $_GET['downloaded_by'] ) && !empty( $_GET['downloaded_by'] ) && 'all' != $_GET['downloaded_by'] ) {
                $downloaded_by = $_GET['downloaded_by'];
                $where_filter .= " AND dl.client_id='" . (int)$downloaded_by . "'" ;
            }
            if ( isset( $_GET['file'] ) ) {
                $file = $_GET['file'];
                $where_filter .= " AND dl.file_id='" . (int)$file . "'" ;
            }
            break;
    }
}

//search
if( !empty( $_GET['s'] ) ) {
    $where_search = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'f.title',
        'dl.download_date',
        'u.user_login',
    ) );
}

//order
$order_by = 'dl.download_date';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'file_title' :
            $order_by = 'f.title';
            break;
        case 'author' :
            $order_by = 'u.user_login';
            break;
        case 'download_date' :
            $order_by = 'dl.download_date';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

//information for manager
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {

    //view admin\managers files
    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
    $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
    $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $manager_clients );
    $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $manager_circles );
    $all_files = array_merge( $client_files, $circle_files );
    $all_files = array_unique( $all_files );
    if ( current_user_can( 'wpc_view_admin_managers_files' ) ) {
        $where_manager .= " AND ( ( ( f.page_id = 0 ) OR f.id IN('" . implode( "','", $all_files ) . "')) AND dl.client_id IN('" . implode( "','", $manager_clients ) . "'))";
    } else {
        $where_manager .= " AND ( ( ( f.user_id = " . get_current_user_id() . " ) OR f.id IN('" . implode( "','", $all_files ) . "')) AND dl.client_id IN('" . implode( "','", $manager_clients ) . "'))";

    }
}


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Clients_List_Table extends WP_List_Table {

    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $columns = array();
    var $files_for_view = array();
    var $bulk_actions = array();


    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        parent::__construct( $args );

        //available filetype for view
        $this->files_for_view = array(
            'bmp', 'css', 'gif', 'html', 'jpg', 'jpeg', 'pdf', 'png', 'txt', 'xml',
        );
    }

    function __call( $name, $arguments ) {
        return call_user_func_array( array( $this, $name ), $arguments );
    }

    function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {
        if( isset( $item[ $column_name ] ) ) {
            return $item[ $column_name ];
        } else {
            return '';
        }
    }

    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach( $args as $k=>$val ) {
            if( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } else if( is_string( $k ) ) {
                $return_args[ $k ] = array( $val, $k == $this->default_sorting_field );
            } else {
                continue;
            }
        }
        $this->sortable_columns = $return_args;
        return $this;
    }

    function get_sortable_columns() {
        return $this->sortable_columns;
    }

    function set_columns( $args = array() ) {
        if( count( $this->bulk_actions ) ) {
            $args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
        }
        $this->columns = $args;
        return $this;
    }

    function get_columns() {
        return $this->columns;
    }

    function set_actions( $args = array() ) {
        $this->actions = $args;
        return $this;
    }

    function get_actions() {
        return $this->actions;
    }

    function set_bulk_actions( $args = array() ) {
        $this->bulk_actions = $args;
        return $this;
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_file_icon( $item ) {
        $file_type = explode( '.', $item['filename'] );
        $file_type = strtolower( end( $file_type ) );
        $file_type = ( 6 >= strlen( $file_type ) ) ? $file_type : 'unknown';
        return '<img width="40" height="40" src="' . WPC()->files()->get_fileicon( $file_type ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" />';

    }

    function column_file_title( $item ) {
        $file_type = explode( '.', $item['filename'] );
        $file_type = strtolower( end( $file_type ) );

        $file_title = ( isset( $item['title'] ) && '' != $item['title'] ) ? $item['title'] : $item['name'];

        $title_html = '<input type="hidden" id="assign_name_block_' . $item['id'] . '" value="' . $item['name'] . '" />
            <span id="file_name_block_' . $item['id'] . '">
                <a href="' . get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $item['id'] ) . '&id=' . $item['id'] . '" title="' . __( 'download', WPC_CLIENT_TEXT_DOMAIN ) . ' \' ' . $item['name'] . '\'" >'. $file_title .'</a>
                <br>
                <span class="description" style="font-size: 10px;" >' .  $item['name'] . '</span>
            </span>';

        //todo: add description
        $title_html = apply_filters( 'wp_client_file_sharing_title_html', $title_html, $item );

        $actions = array();

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        if( ( isset( $wpc_file_sharing['google_doc_embed'] ) && 'yes' == $wpc_file_sharing['google_doc_embed'] && in_array( $file_type, array_keys( WPC()->files()->files_for_google_doc_view ) ) ) ||
            in_array( $file_type, WPC()->files()->files_for_regular_view ) ) {
                $actions['view'] = '<a href="' . get_admin_url() . 'admin.php?wpc_action=view&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $item['id'] ) . '&id=' . $item['id'] . '&d=false&t=' . $file_type .'" target="_blank" title="' . __( 'view', WPC_CLIENT_TEXT_DOMAIN ) . '" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        $actions['download'] = '<a href="' . get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $item['id'] ) . '&id=' . $item['id'] . '" title="' . __( 'download', WPC_CLIENT_TEXT_DOMAIN ) . ' \' ' . $item['name'] . '\'" >' . __( 'Download', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        //todo: add description
        $actions = apply_filters( 'wp_client_file_download_actions', $actions, $item );

        return sprintf('%1$s %2$s', $title_html, $this->row_actions( $actions ) );
    }

    function column_downloaded_by( $item ) {

        return $item['username'];
    }

    function column_download_date( $item ) {
        if ( isset( $item['download_date'] ) && '' != $item['download_date'] ) {
            return WPC()->date_format( $item['download_date'], 'date' ) . '<br />' . WPC()->date_format( $item['download_date'], 'time' );
        }

        return '';
    }

    function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            global $wpdb;

            $all_filter = array( __( 'File', WPC_CLIENT_TEXT_DOMAIN ) => 'file', __( 'Downloaded By', WPC_CLIENT_TEXT_DOMAIN ) => 'downloaded_by' ); ?>

            <div class="alignleft actions">
                <select name="change_filter" id="change_filter" style="float: left;">
                    <option value="-1" <?php if( !isset( $_GET['filter'] ) || !in_array( $_GET['filter'], $all_filter ) ) echo 'selected'; ?>><?php _e( 'Select Filter', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php foreach ( $all_filter as $key=>$type_filter ) {
                        $selected = ( isset( $_GET['filter'] ) && $type_filter == $_GET['filter'] ) ? ' selected' : '' ;
                        echo '<option value="' . $type_filter . '"' . $selected . ' >' . $key . '</option>';
                    } ?>
                </select>
                <select name="select_filter" id="select_filter" style="float: left; <?php if ( !isset( $_GET['filter'] ) || !in_array( $_GET['filter'], $all_filter ) ) echo " display: none;"; ?>">
                    <?php if( isset( $_GET['filter'] ) ) {
                        if( 'downloaded_by' == $_GET['filter'] ) {
                            $all_authors = $wpdb->get_col( "SELECT DISTINCT client_id FROM {$wpdb->prefix}wpc_client_files_download_log" );
                            if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                $manager_clients = WPC()->members()->get_all_clients_manager();
                                $all_authors = array_intersect( $manager_clients, $all_authors );
                            } ?>
                            <option value="-1" <?php if ( !isset( $_GET['downloaded_by'] ) || ( isset( $_GET['downloaded_by'] ) && !in_array( $_GET['downloaded_by'], $all_authors ) ) ) echo 'selected'; ?>><?php _e( 'Select Client', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <?php if ( is_array( $all_authors ) && 0 < count( $all_authors ) ) {
                                foreach( $all_authors as $author_id ) {
                                    $selected = ( $author_id == $_GET['downloaded_by'] ) ? 'selected' : '';
                                    echo '<option value="' . $author_id . '" ' . $selected . ' >' . get_userdata( $author_id )->user_login . '</option>';
                                }
                            }
                        } elseif( 'file' == $_GET['filter'] ) {
                            $all_files = $wpdb->get_results(
                                "SELECT DISTINCT fdl.file_id,
                                    f.name,
                                    f.title
                                FROM {$wpdb->prefix}wpc_client_files_download_log fdl,
                                    {$wpdb->prefix}wpc_client_files f
                                WHERE f.id = fdl.file_id" ,
                            ARRAY_A );

                            $all_file_ids = $wpdb->get_col(
                                "SELECT DISTINCT file_id
                                FROM {$wpdb->prefix}wpc_client_files_download_log"
                            );
                            /*if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                                $all_authors = array_intersect( $manager_clients, $all_authors );
                            } */
                            ?>
                            <option value="-1" <?php if( !isset( $_GET['file'] ) || ( isset( $_GET['file'] ) && !in_array( $_GET['file'], $all_file_ids ) ) ) echo 'selected'; ?>><?php _e( 'Select File', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <?php
                            if ( is_array( $all_files ) && 0 < count( $all_files ) ) {
                                foreach( $all_files as $file ) {
                                    $selected = ( $file['file_id'] == $_GET['file'] ) ? 'selected' : '';
                                    $filename = ( isset( $file['title'] ) && '' != $file['title'] ) ? $file['title'] : $file['name'];
                                    echo '<option value="' . $file['file_id'] . '" ' . $selected . ' >' . $filename . '</option>';
                                }
                            }
                        }
                    } ?>
                </select>
                <?php $visible = ( isset( $_GET['filter'] ) && in_array( $_GET['filter'], $all_filter ) && ( ( $_GET['filter'] == 'file' && isset( $_GET['file'] ) ) || ( 'downloaded_by' == $_GET['filter'] && isset( $_GET['downloaded_by'] ) ) ) ) ? true : false; ?>
                <select name="select_filter2" id="select_filter2" style="float: left; <?php if ( !$visible ) echo " display: none;"; ?>">
                    <?php if( isset( $_GET['filter'] ) ) {
                        if( 'downloaded_by' == $_GET['filter'] && isset( $_GET['downloaded_by'] ) ) {

                            $all_files = $wpdb->get_results(
                                "SELECT DISTINCT fdl.file_id,
                                    f.name,
                                    f.title
                                FROM {$wpdb->prefix}wpc_client_files_download_log fdl,
                                    {$wpdb->prefix}wpc_client_files f
                                WHERE f.id = fdl.file_id" ,
                            ARRAY_A );

                            $all_file_ids = $wpdb->get_col(
                                "SELECT DISTINCT file_id
                                FROM {$wpdb->prefix}wpc_client_files_download_log"
                            );

                            /*if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                                $all_authors = array_intersect( $manager_clients, $all_authors );
                            } */
                            ?>
                            <option value="-1" <?php if( !isset( $_GET['file'] ) || ( isset( $_GET['file'] ) && !in_array( $_GET['file'], $all_file_ids ) && 'all' != $_GET['file'] ) ) echo 'selected'; ?>><?php _e( 'Select File', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="all" <?php if( $_GET['file'] == 'all' ) echo 'selected'; ?>><?php _e( 'All Files', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <?php
                            if ( is_array( $all_files ) && 0 < count( $all_files ) ) {
                                foreach( $all_files as $file ) {
                                    $selected = ( $file['file_id'] == $_GET['file'] ) ? 'selected' : '';
                                    $filename = ( isset( $file['title'] ) && '' != $file['title'] ) ? $file['title'] : $file['name'];
                                    echo '<option value="' . $file['file_id'] . '" ' . $selected . ' >' . $filename . '</option>';
                                }
                            }


                        } elseif( 'file' == $_GET['filter'] && isset( $_GET['file'] ) ) {


                            $all_authors = $wpdb->get_col( "SELECT DISTINCT client_id FROM {$wpdb->prefix}wpc_client_files_download_log" );
                            if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                $manager_clients = WPC()->members()->get_all_clients_manager();
                                $all_authors = array_intersect( $manager_clients, $all_authors );
                            } ?>
                            <option value="-1" <?php if( !isset( $_GET['downloaded_by'] ) || ( isset( $_GET['downloaded_by'] ) && !in_array( $_GET['downloaded_by'], $all_authors ) && 'all' != $_GET['downloaded_by'] ) ) echo 'selected'; ?>><?php _e( 'Select Client', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="all" <?php if( $_GET['downloaded_by'] == 'all' ) echo 'selected'; ?>><?php _e( 'All Clients', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <?php if ( is_array( $all_authors ) && 0 < count( $all_authors ) ) {
                                foreach( $all_authors as $author_id ) {
                                    $selected = ( $author_id == $_GET['downloaded_by'] ) ? 'selected' : '';
                                    echo '<option value="' . $author_id . '" ' . $selected . ' >' . get_userdata( $author_id )->user_login . '</option>';
                                }
                            }
                        }
                    } ?>
                </select>
                <span id="load_select_filter" style="float: left; margin: 3px 5px 0 0;"></span>
                <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="filtered" name="" style="float: left;" />
                <a class="add-new-h2 cancel_filter" id="cancel_filter" style="float:left; cursor: pointer; margin-top: 4px; <?php if( !isset( $_GET['filter']) ) echo ' display: none;'; ?>" ><?php _e( "Remove Filter", WPC_CLIENT_TEXT_DOMAIN ) ?><span class="ez_cancel_button" style="margin: 1px 0 0 7px;"></span></a>
            </div>
            <?php $this->search_box( __( 'Search Files' , WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}

$excluded_clients = "'" . implode( "','", WPC()->members()->get_excluded_clients() ) . "'";

$ListTable = new WPC_Clients_List_Table( array(
    'singular'  => __( 'file_download', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'file_downloads', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_file_downloads_per_page' );
$paged      = $ListTable->get_pagenum();


$ListTable->set_sortable_columns( array(
    'file_title'        => 'file_title',
    'download_date'     => 'download_date',
) );


$ListTable->set_columns(array(
    'file_icon'         => '',
    'file_title'        => __( 'File Title', WPC_CLIENT_TEXT_DOMAIN ),
    'download_date'     => __( 'Download', WPC_CLIENT_TEXT_DOMAIN ),
    'downloaded_by'     => __( 'Downloaded By', WPC_CLIENT_TEXT_DOMAIN )
));

$items_count = $wpdb->get_var(
    "SELECT COUNT( dl.id )
    FROM {$wpdb->prefix}wpc_client_files_download_log dl
    LEFT JOIN {$wpdb->users} u ON dl.client_id = u.ID
    LEFT JOIN {$wpdb->prefix}wpc_client_files f ON dl.file_id = f.id
    WHERE 1=1 " . $where_manager. " " . $where_search . " " . $where_filter
);


$download_log = $wpdb->get_results(
    "SELECT dl.download_date, u.user_login as username, f.*
    FROM {$wpdb->prefix}wpc_client_files_download_log dl
    LEFT JOIN {$wpdb->users} u ON dl.client_id = u.ID
    LEFT JOIN {$wpdb->prefix}wpc_client_files f ON dl.file_id = f.id
    WHERE 1=1 ". $where_search . " " . $where_filter . " " . $where_manager . "
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", {$per_page}
    ", ARRAY_A );

$ListTable->prepare_items();
$ListTable->items               = $download_log;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );


//Display status message
if ( isset( $_GET['updated'] ) ) { ?>
    <div id="message" class="updated wpc_notice fade"><p><?php echo urldecode( $_GET['dmsg'] ); ?></p></div>
<?php } ?>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <?php if ( isset( $_GET['msg'] ) ) {
            switch( $_GET['msg'] ) {
                case 'r':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Files assigned to another category!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'd':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'File(s) are Deleted.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'ad':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The file has been added', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'up':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The file has been uploaded!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'as':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The file has been uploaded!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'm':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The file size more than allowed!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'er':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'er_as':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error assign the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'er_as2':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Some error with assigning permission for file.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'ne':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Error: File not exist!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
            }
        } ?>

        <div class="wpc_tab_container_block">
            <form action="" method="get" name="wpc_file_form" id="wpc_file_downloads_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="files_downloads" />
                <?php $ListTable->display(); ?>
            </form>
        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';
            var request_uri = "<?php echo $_SERVER['REQUEST_URI'];?>";

            jQuery( document ).ready( function() {

                //change filter
                jQuery( '#change_filter' ).change( function() {
                    if ( '-1' != jQuery( '#change_filter' ).val() ) {
                        var filter = jQuery( '#change_filter' ).val();
                        jQuery( '#select_filter' ).css( 'display', 'none' );
                        jQuery( '#select_filter' ).html( '' );
                        jQuery( '#select_filter2' ).css( 'display', 'none' );
                        jQuery( '#select_filter2' ).html( '' );
                        jQuery( '#load_select_filter' ).addClass( 'wpc_ajax_loading' );
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                            data: 'action=wpc_get_options_filter_for_files_download_log&filter=' + filter,
                            dataType: 'html',
                            success: function( data ){
                                jQuery( '#select_filter' ).html( data );
                                jQuery( '#load_select_filter' ).removeClass( 'wpc_ajax_loading' );
                                jQuery( '#select_filter' ).css( 'display', 'block' );
                            }
                        });
                    } else {
                        jQuery( '#select_filter' ).css( 'display', 'none' );
                        jQuery( '#select_filter2' ).css( 'display', 'none' );
                        jQuery( '#select_filter' ).html( '' );
                        jQuery( '#select_filter2' ).html( '' );
                    }
                    return false;
                });

                jQuery( '#select_filter' ).change( function() {
                    if ( '-1' != jQuery( '#select_filter' ).val() ) {
                        var filter = jQuery( '#change_filter' ).val();
                        var select_filter = jQuery( '#select_filter' ).val();

                        jQuery( '#select_filter2' ).css( 'display', 'none' );
                        jQuery( '#select_filter2' ).html( '' );
                        jQuery( '#load_select_filter' ).addClass( 'wpc_ajax_loading' );
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                            data: 'action=wpc_get_options_filter_for_files_download_log&filter=' + filter + '&select_filter=' + select_filter,
                            dataType: 'html',
                            success: function( data ){
                                jQuery( '#select_filter2' ).html( data );
                                jQuery( '#load_select_filter' ).removeClass( 'wpc_ajax_loading' );
                                jQuery( '#select_filter2' ).css( 'display', 'block' );
                            }
                        });
                    } else {
                        jQuery( '#select_filter2' ).css( 'display', 'none' );
                        jQuery( '#select_filter2' ).html( '' );
                    }
                    return false;
                });


                //filter
                jQuery( '#filtered' ).click( function() {
                    if ( '-1' != jQuery( '#change_filter' ).val() && '-1' != jQuery( '#select_filter' ).val() && '-1' != jQuery( '#select_filter2' ).val() ) {
                        var req_uri = "<?php echo preg_replace( '/&downloaded_by=[a-z0-9]+|&file=[a-z0-9]+|&paged=[0-9]+|&filter=[a-z\_]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                        //if ( in_array() )
                        switch( jQuery( '#change_filter' ).val() ) {
                            case 'downloaded_by':
                                window.location = req_uri + '&downloaded_by=' + jQuery( '#select_filter' ).val() + '&file=' + jQuery( '#select_filter2' ).val() + '&filter=downloaded_by';
                                break;
                            case 'file':
                                window.location = req_uri + '&file=' + jQuery( '#select_filter' ).val() + '&downloaded_by=' + jQuery( '#select_filter2' ).val() + '&filter=file';
                                break;
                        }
                    }
                    return false;
                });


                jQuery( '#cancel_filter' ).click( function() {
                    var req_uri = "<?php echo preg_replace( '/&downloaded_by=[a-z0-9]+|&file=[a-z0-9]+|&filter=[a-z\_]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                    window.location = req_uri;
                    return false;
                });

            });
        </script>
    </div>
</div>