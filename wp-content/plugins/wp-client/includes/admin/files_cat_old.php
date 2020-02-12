<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclients_content' ) );
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=files_categories&display=old';
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}


global $wpdb;

//search
$where_search = '';
if( !empty( $_GET['s'] ) ) {
    $where_search = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'fc.cat_name',
    ) );
}

$order_by = 'fc.cat_id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'cat_name' :
            $order_by = 'fc.cat_name';
            break;
        case 'cat_id' :
            $order_by = 'fc.cat_id';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'DESC' : 'ASC';


if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WPC_File_Categories_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();

    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        $this->no_items_message = $args['plural'] . ' ' . __( 'not found.', WPC_CLIENT_TEXT_DOMAIN );

        parent::__construct( $args );


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

    function no_items() {
        echo $this->no_items_message;
    }

    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach( $args as $k=>$val ) {
            if( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } elseif ( is_string( $k ) ) {
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

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['cat_id']
        );
    }

    function column_cat_name( $item ) {
        $actions = array();
        $after_delete = '';
        if ( 'General' != $item['cat_name'] ) {
            $actions['edit'] = '<a id="edit_button_' . $item['cat_id'] . '" onclick="jQuery(this).editGroup( ' . $item['cat_id'] . ', \'edit\' );">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>
                            <span id="save_block_' . $item['cat_id'] . '"></span>';
            $show_or_delete = ( 0 < $item['files'] ) ? 'show' : 'delete' ;
            $actions['delete'] = '<a class="group_delete" onclick="jQuery(this).deleteCat( ' . $item['cat_id'] . ' , \'' . $show_or_delete . '\');">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            if ( 0 < $item['files'] ) {
                $after_delete = '<div class="cat_reassign_block" id="cat_reassign_block_' . $item['cat_id'] . '">
                                <hr />
                                <span><b>' . __( 'Category have Files. What do with Files', WPC_CLIENT_TEXT_DOMAIN ) . ':</b></span>
                                <br>
                                <select name="cat_reassign">';

                $exclude_cats = WPC()->files()->get_category_children_ids( $item['cat_id'] );
                $exclude_cats[] = $item['cat_id'];

                $after_delete .= WPC()->files()->render_category_list_items( array( 'exclude' => $exclude_cats ), '', false );
                $after_delete .= '</select>
                        <input type="button" value="' . __( 'Reassign Files', WPC_CLIENT_TEXT_DOMAIN ) . '" onclick="jQuery(this).deleteCat( ' . $item['cat_id'] . ', \'reassign\' );" />
                        or
                        <input type="button" value="' . __( 'Delete Files', WPC_CLIENT_TEXT_DOMAIN ) . '" onclick="jQuery(this).deleteCat( ' . $item['cat_id'] . ', \'delete\' );" />
                        </div>';
            }

        }
        return sprintf( '%1$s %2$s', '<span id="cat_name_block_' . $item['cat_id'] . '">' . $item['cat_name'] . '</span>
                        <div id="save_or_close_block_' . $item['cat_id'] . '" style="display:none"><a href="javascript:void(0);" id="close_button_' . $item['cat_id'] . '" onclick="jQuery(this).editGroup(' . $item['cat_id'] . ', \'close\' );" >' . __( 'Close', WPC_CLIENT_TEXT_DOMAIN ) . '</a>&nbsp;|&nbsp;
                    <a onClick="jQuery(this).saveGroup();" href="javascript:void(0);">' . __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) . '</a></div>
                        ' , $this->row_actions( $actions ) ) . $after_delete ;
    }


    function column_folder_name( $item ) {

        return '<span id="folder_name_block_' . $item['cat_id'] . '">' . $item['folder_name'] . '</span>';
    }

    function column_circles( $item ) {
        $id_array = WPC()->assigns()->get_assign_data_by_object( 'file_category', $item['cat_id'], 'circle' );
        $return = '';
        if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_manager' ) ) {
            $link_array = array(
                'data-id' => $item['cat_id'],
                'data-ajax' => 1,
                'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ) . ' ' . $item['cat_name']
            );
            $input_array = array(
                'name'  => 'wpc_circles_ajax[]',
                'id'    => 'wpc_circles_' . $item['cat_id'],
                'value' => implode( ',', $id_array )
            );
            $additional_array = array(
                'counter_value' => count( $id_array )
            );

            $return .= WPC()->assigns()->assign_popup('circle', 'wpclients_filescat', $link_array, $input_array, $additional_array, false );
        }
        return $return;
    }

    function column_clients( $item ) {
        $id_array = WPC()->assigns()->get_assign_data_by_object( 'file_category', $item['cat_id'], 'client' );

        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $manager_clients = WPC()->members()->get_all_clients_manager();
        }
        $user_count = 0;
        foreach ( $id_array as $client_id ) {
            if ( 0 < $client_id ) {
                //if manager - skip not manager's clients
                if ( isset( $manager_clients ) && !in_array( $client_id, $manager_clients ) )
                    continue;
                if( !empty( $client_id ) ) {
                    $user_count++;
                }
            }
        }

        $link_array = array(
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . ' ' . $item['cat_name'],
            'data-ajax' => true,
            'data-id' => $item['cat_id'],
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['cat_id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => $user_count
        );
        $return = WPC()->assigns()->assign_popup('client', 'wpclients_filescat', $link_array, $input_array, $additional_array, false );

        return $return;
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_File_Categories_List_Table( array(
    'singular'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false

));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_file_categories_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'cat_id'          => 'cat_id',
    'cat_name'        => 'cat_name'
) );

$ListTable->set_bulk_actions(array(
));

$ListTable->set_columns(array(
    'cat_id'            => __( 'Category ID', WPC_CLIENT_TEXT_DOMAIN ),
    'cat_name'          => __( 'Category Name', WPC_CLIENT_TEXT_DOMAIN ),
    'folder_name'       => __( 'Folder Name', WPC_CLIENT_TEXT_DOMAIN ),
    'files'             => __( 'Files', WPC_CLIENT_TEXT_DOMAIN ),
    'clients'           => WPC()->custom_titles['client']['p'] ,
    'circles'           => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ,
));

$items_count = $wpdb->get_var(
    "SELECT COUNT( cat_id )
    FROM {$wpdb->prefix}wpc_client_file_categories fc
    WHERE 1=1 $where_search"
);

$cats = $wpdb->get_results(
    "SELECT fc.cat_id AS cat_id,
            cat_name,
            folder_name,
            COUNT(f.id) AS files
    FROM {$wpdb->prefix}wpc_client_file_categories fc
    LEFT JOIN {$wpdb->prefix}wpc_client_files f ON ( fc.cat_id = f.cat_id )
    WHERE 1=1 $where_search
    GROUP BY fc.cat_id
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page",
ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $cats;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php
        if ( isset( $_GET['msg'] ) ) {
            $msg = $_GET['msg'];
            switch($msg) {
            case 'null':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category name is null!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fnull':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category Folder Name is null!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'cne':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category already exists!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fne':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category already exists!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fe':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category already exists!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fnerr':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category Folder Name Error!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'cr':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category has been created!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'reas':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category is reassigned!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 's':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The changes of the Category are saved!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category is deleted!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            }
        }
    ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <a class="add-new-h2 wpc_form_link" id="wpc_new_cat">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>
            <a class="add-new-h2 wpc_form_link" id="wpc_reasign">
                <?php _e( 'Reassign Files', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>
            <span class="display_link_block">
                <a class="display_link" href="admin.php?page=wpclients_content&tab=files_categories&display=new"><?php _e( 'Tree View', WPC_CLIENT_TEXT_DOMAIN ) ?></a> |
                <a class="display_link selected_link" href="#"><?php _e( 'List View', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            </span>

            <div id="new_form_panel">
                <form method="post" name="new_cat" id="new_cat" >
                    <input type="hidden" name="wpc_action" value="create_file_cat" />
                    <table>
                        <tr>
                            <td style="width: 120px;">
                                <label for="cat_name_new"><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="cat_name_new" id="cat_name_new" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="cat_folder_new"><?php _e( 'Folder name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="cat_folder_new" id="cat_folder_new" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="parent_cat"><?php _e( 'Parent', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="parent_cat" id="parent_cat">
                                    <option value="0"><?php _e( '(no parent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign %s to File Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                        'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_clients',
                                        'id'    => 'wpc_clients',
                                        'value' => ''
                                    );
                                    $additional_array = array(
                                        'counter_value' => 0
                                    );
                                    WPC()->assigns()->assign_popup('client', 'wpclients_filescat', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign %s to File Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                        'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_circles',
                                        'id'    => 'wpc_circles',
                                        'value' => ''
                                    );
                                    $additional_array = array(
                                        'counter_value' => 0
                                    );
                                    WPC()->assigns()->assign_popup('circle', 'wpclients_filescat', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div class="save_button">
                        <input type="submit" class="button-primary" value="<?php _e( 'Create Category', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="create_cat" />
                    </div>
                </form>
            </div>

            <div id="reasign_form_panel">
                <form method="post" name="reassign_files_cat" id="reassign_files_cat" >
                    <input type="hidden" name="wpc_action" id="wpc_action3" value="" />
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 120px;">
                                <label for="old_cat_id"><?php _e( 'Category From', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="old_cat_id" id="old_cat_id">
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="new_cat_id"><?php _e( 'Category To', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="new_cat_id" id="new_cat_id">
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div class="save_button">
                        <input type="button" class="button-primary" name="" value="<?php _e( 'Reassign', WPC_CLIENT_TEXT_DOMAIN ) ?>" id="reassign_files" />
                    </div>
                </form>
            </div>
            <form action="" method="get" name="wpc_files_category_search_form" id="wpc_files_category_search_form">
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="files_categories" />
                <input type="hidden" name="display" value="old" />
                <?php $ListTable->search_box( __( 'Search File Categories' , WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' ); ?>
            </form>
            <form action="" method="get" name="edit_cat" id="edit_cat" style="width: 100%;">
                <input type="hidden" name="wpc_action" id="wpc_action2" value="" />
                <input type="hidden" name="cat_id" id="cat_id" value="" />
                <input type="hidden" name="reassign_cat_id" id="reassign_cat_id" value="" />
                <input type="hidden" name="display" id="display" value="old" />

                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="files_categories" />
                <?php $ListTable->display(); ?>
            </form>
        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';

            jQuery( document ).ready( function() {

                jQuery( '#wpc_new_cat' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'inline',
                    href            : '#new_form_panel',
                    title           : '<?php echo esc_js( __( 'New File Category', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                });

                jQuery( '#wpc_reasign' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'inline',
                    href            : '#reasign_form_panel',
                    title           : '<?php echo esc_js( __( 'Reassign Files Category', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                });

                //reassign file from Bulk Actions
                jQuery( '#doaction2' ).click( function() {
                    var action = jQuery( 'select[name="action2"]' ).val() ;
                    jQuery( 'select[name="action"]' ).attr( 'value', action );

                    return true;
                });

                var group_name  = "";
                var folder_name = "";

                jQuery.fn.editGroup = function ( id, action ) {
                    if ( action == 'edit' ) {
                        group_name = jQuery( '#cat_name_block_' + id ).html();
                        group_name = group_name.replace(/(^\s+)|(\s+$)/g, "");

                        folder_name = jQuery( '#folder_name_block_' + id ).html();
                        folder_name = folder_name.replace(/(^\s+)|(\s+$)/g, "");


                        jQuery( '#cat_name_block_' + id ).html( '<input type="text" name="cat_name" size="30" id="edit_cat_name"  value="' + group_name + '" /><input type="hidden" name="cat_id" value="' + id + '" />' );
                        jQuery( '#folder_name_block_' + id ).html( '<input type="text" name="folder_name" size="30" id="edit_folder_name"  value="' + folder_name + '" />' );

                        jQuery( '#edit_cat input[type="button"]' ).attr( 'disabled', true );

                        jQuery( this ).parent().parent().attr('style', "display:none" );
                        jQuery( '#save_or_close_block_' + id ).attr('style', "display:block;" );

                        return '';

                    } else if ( action == 'close' ) {
                        jQuery( '#cat_name_block_' + id ).html( group_name );
                        jQuery( '#folder_name_block_' + id ).html( folder_name );

                        jQuery( '#save_or_close_block_' + id ).attr('style', "display:none;" );
                        jQuery( this ).parent().next().attr('style', "display:block" );

                        return '';
                    }


                };


                jQuery.fn.saveGroup = function ( ) {

                    jQuery( '#edit_cat_name' ).parent().parent().attr( 'class', '' );

                    if ( '' == jQuery( '#edit_cat_name' ).val() ) {
                        jQuery( '#edit_cat_name' ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    }

                    jQuery( '#wpc_action2' ).val( 'edit_file_cat' );
                    jQuery( '#edit_cat' ).submit();
                };

                //block for delete cat
                jQuery.fn.deleteCat = function ( id, act ) {
                    if ( 'show' == act ) {
                        jQuery( '#cat_reassign_block_' + id ).slideToggle( 'slow' );

                        if( jQuery(this).html() == '<?php echo esc_js( __( 'Cancel Delete', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' ) {
                            jQuery(this).html( '<?php echo esc_js( __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' );
                        } else {
                            jQuery(this).html( '<?php echo esc_js( __( 'Cancel Delete', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' );
                        }
                    } else if( 'reassign' == act ) {
                        if( confirm("<?php echo esc_js( __( 'Are you sure want to delete permanently this category and reassign all files and parent categories to another category? ', WPC_CLIENT_TEXT_DOMAIN ) ) ?>") ) {
                            jQuery( '#wpc_action2' ).val( 'delete_file_category' );
                            jQuery( '#cat_id' ).val( id );
                            jQuery( '#reassign_cat_id' ).val( jQuery( '#cat_reassign_block_' + id + ' select' ).val() );
                            jQuery( '#edit_cat' ).submit();
                        }
                    } else if( 'delete' == act ) {
                        if( confirm("<?php echo esc_js( __( 'Are you sure want to delete permanently this category with all files and parent categories? ', WPC_CLIENT_TEXT_DOMAIN ) ) ?>") ) {
                            jQuery( '#wpc_action2' ).val( 'delete_file_category' );
                            jQuery( '#cat_id' ).val( id );
                            jQuery( '#edit_cat' ).submit();
                        }
                    }
                };

                //Reassign files to another cat
                jQuery( '#reassign_files' ).click( function() {
                    if ( jQuery( '#old_cat_id' ).val() == jQuery( '#new_cat_id' ).val() ) {
                        jQuery( '#old_cat_id' ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    }
                    jQuery( '#wpc_action3' ).val( 'reassign_files_from_category' );
                    jQuery( '#reassign_files_cat' ).submit();
                    return false;
                });

                jQuery( 'input[name=create_cat]' ).click( function() {
                    if( jQuery( '#cat_name_new' ).val() != '' ) {
                        return true;
                    }
                    return false;
                });



                jQuery( '.wp-list-table').attr("id", "sortable");

                var fixHelper = function(e, ui) {
                    ui.children().each(function() {
                        jQuery(this).width(jQuery(this).width());
                    });
                    return ui;
                };

                jQuery( '#sortable tbody' ).sortable({
                    axis: 'y',
                    helper: fixHelper,
                    handle: '.order',
                    items: 'tr'
                });

                jQuery( '#sortable' ).bind( 'sortupdate', function(event, ui) {

                    new_order = new Array();
                    jQuery('#sortable tbody tr td.order div').each( function(){
                        new_order.push( jQuery(this).attr("id") );
                    });
                    jQuery( 'body' ).css( 'cursor', 'wait' );
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=change_cat_order&new_order=' + new_order,
                        success: function( html ) {
                            var i = 1;
                            jQuery( '.order_num' ).each( function () {
                                jQuery( this ).html(i);
                                i++;
                            });
                            jQuery( 'body' ).css( 'cursor', 'default' );
                        }
                     });
                });
            });
        </script>

    </div>

</div>