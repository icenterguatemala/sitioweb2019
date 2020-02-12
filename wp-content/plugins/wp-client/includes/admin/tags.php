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
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=tags';
}

if( !empty( $_POST['wpc_action'] ) ) {
    switch( $_POST['wpc_action'] ) {
        case 'reassign_tag':
            if ( empty( $_POST['old_tag_id'] ) || empty( $_POST['new_tag_id'] )
                    || $_POST['old_tag_id'] === $_POST['new_tag_id'] ) {
                WPC()->redirect( add_query_arg( 'msg', 'n_rat', $redirect ) );
            }
            $file_ids = $wpdb->get_col("SELECT DISTINCT object_id
                FROM {$wpdb->term_relationships}
                WHERE term_taxonomy_id = '" . $wpdb->_real_escape( $_POST['old_tag_id'] ) . "' AND object_id NOT IN (
                    SELECT DISTINCT object_id
                    FROM {$wpdb->term_relationships}
                    WHERE term_taxonomy_id = '" . $wpdb->_real_escape( $_POST['new_tag_id'] ) . "'
                )");
            $wpdb->delete( $wpdb->term_relationships,
                array(
                    'term_taxonomy_id' => $_POST['old_tag_id']
                )
            );
            foreach( $file_ids as $val ) {
                $wpdb->insert( $wpdb->term_relationships,
                    array(
                        'term_taxonomy_id' => $_POST['new_tag_id'],
                        'object_id' => $val
                    )
                );
            }
            WPC()->redirect( add_query_arg( 'msg', 'rat', $redirect ) );

            break;

        case 'create_file_tag':
            $term = $_POST['tag_name_new'];
            if ( !strlen( trim( $term ) ) )
                WPC()->redirect( add_query_arg( 'msg', 'wt', $redirect ) );

            if ( !$term_info = term_exists($term, 'wpc_tags') )
                $term_info = wp_insert_term($term, 'wpc_tags');
            else
                WPC()->redirect( add_query_arg( 'msg', 'aet', $redirect ) );

            WPC()->redirect( add_query_arg( 'msg', 'st', $redirect ) );
            break;
    }
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        case 'delete':
            $ids = array();
            if ( isset( $_GET['tag_id'] ) ) {
                check_admin_referer( 'wpc_file_tag_delete' .  $_GET['tag_id'] . get_current_user_id() );
                $ids = (array) $_GET['tag_id'];
            } elseif( isset( $_REQUEST['item'] ) ) {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Tags', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) && ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_delete_portal_page_tags' ) ) ) {
                foreach ( $ids as $tag_id ) {
                    wp_delete_term( $tag_id, 'wpc_tags' );
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            }
            WPC()->redirect( $redirect );
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
}

$where_search       = '';
$where_manager      = '';


//search
if( !empty( $_GET['s'] ) ) {
    $where_search = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        't.name',
    ) );
}

//order
$order_by = 'tt.term_id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'name' :
            $order_by = 't.name';
            break;
        case 'count' :
            $order_by = 'count';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

//information for manager
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {

    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
    $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
    $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $manager_clients );
    $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $manager_circles );
    $files = array_merge( $client_files, $circle_files );
    $files = array_unique( $files );
    if ( current_user_can( 'wpc_view_admin_managers_files' ) ) {
        $ids_files_manager = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_files WHERE page_id = 0 OR id IN('" . implode( "','", $files ) . "')" ) ;
    } else {
        $ids_files_manager = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_files WHERE user_id = " . get_current_user_id() . " OR id IN('" . implode( "','", $files ) . "')" );
    }
    $where_manager  = " AND tr.object_id IN('" . implode( "','", $ids_files_manager ) . "')" ;
}


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Tags_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $columns = array();
    var $bulk_actions = array();


    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        $this->no_items_message = $args['plural'] . ' ' . __(  'not found.', WPC_CLIENT_TEXT_DOMAIN );

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

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%d" />', $item['id']
        );
    }

    function column_name( $item ) {

        $actions = array();

        if( $item['count'] > 0 ) {
            $actions['view'] = '<a class="various" target="_blank" href="admin.php?page=wpclients_content&tag=' . $item['id'] . '" title="" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_delete_portal_page_tags' ) )  {
            $actions['delete'] = '<a onclick=\'return confirm("' .  __( 'Are you sure you want to delete this Tag?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_content&tab=tags&action=delete&tag_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_file_tag_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        return sprintf('%1$s %2$s', '<span id="wpc_file_tag_' . $item['id'] . '">' . $item['name'] . '</span>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( __( 'Search Tags', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }
}


$ListTable = new WPC_Tags_Table( array(
    'singular'  => __( 'Tag', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Tags', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_tags_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'name'      => 'name',
    'count'     => 'count',
) );

if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_delete_portal_page_tags' ) ) {
    $ListTable->set_bulk_actions( array(
        'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
    ));
}


$ListTable->set_columns(array(
    'name'      => __( 'Tag Name', WPC_CLIENT_TEXT_DOMAIN ),
    'count'     => __( 'Count', WPC_CLIENT_TEXT_DOMAIN ),
));




$items_count = $wpdb->get_var(
    "SELECT COUNT( tt.term_id )
    FROM {$wpdb->term_taxonomy} tt
    LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE tt.taxonomy='wpc_tags' " . $where_search
);


$tags_list = $wpdb->get_results(
    "SELECT tt.term_id as id,
            ( SELECT COUNT(*) FROM {$wpdb->term_relationships} tr WHERE tt.term_taxonomy_id = tr.term_taxonomy_id " . $where_manager . " ) as count,
            t.name as name
    FROM {$wpdb->term_taxonomy} tt
    LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE tt.taxonomy='wpc_tags'
    GROUP BY tt.term_id", ARRAY_A );

$all_file_tags = $wpdb->get_results(
    "SELECT tt.term_id as id,
            ( SELECT COUNT(*) FROM {$wpdb->term_relationships} tr WHERE tt.term_taxonomy_id = tr.term_taxonomy_id " . $where_manager . " ) as count,
            t.name as name
    FROM {$wpdb->term_taxonomy} tt
    LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE tt.taxonomy='wpc_tags' ". $where_search . "
    GROUP BY tt.term_id
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", {$per_page}
    ", ARRAY_A );

$ListTable->prepare_items();
$ListTable->items               = $all_file_tags;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_portal_page_tags' ) ) { ?>
            jQuery( '#wpc_new' ).shutter_box({
                view_type       : 'lightbox',
                width           : '400px',
                type            : 'inline',
                href            : '#new_form_panel',
                title           : '<?php echo esc_js( __( 'New Tag', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
            });
        <?php } ?>
        jQuery( '#wpc_reasign' ).shutter_box({
            view_type       : 'lightbox',
            width           : '400px',
            type            : 'inline',
            href            : '#reasign_form_panel',
            title           : '<?php echo esc_js( __( 'Reassign Tag', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
        });
    });
</script>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <?php if ( isset( $_GET['msg'] ) ) {
            switch( $_GET['msg'] ) {
                case 'd':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Tag(s) are Deleted.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'rat':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Tag reassigned successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'n_rat':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Tag was not reassigned.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'wt':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Wrong Tag name.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'aet':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Tag already exists.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'st':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Tag was added successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
            }
        }
        ?>

        <div class="wpc_tab_container_block">
            <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_portal_page_tags' ) ) { ?>
                <a class="add-new-h2 wpc_form_link" id="wpc_new">
                    <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </a>
            <?php } ?>
            <a class="add-new-h2 wpc_form_link" id="wpc_reasign">
                <?php _e( 'Reassign Tags', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>

            <div id="new_form_panel">
                <form method="post" name="new_tag" id="new_tag">
                    <input type="hidden" name="wpc_action" value="create_file_tag">
                    <table border="0">
                        <tbody>
                            <tr>
                                <td style="width: 100px;">
                                    <label for="tag_name_new"><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                </td>
                                <td>
                                    <input type="text" name="tag_name_new" id="tag_name_new"  style="width: 250px;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <div class="save_button">
                        <input type="submit" class="button-primary" value="Create Tag" name="create_tag" />
                    </div>
                </form>
            </div>

            <div id="reasign_form_panel">
                <form method="post" name="reassign_files_cat" id="reassign_files_tag">
                    <input type="hidden" name="wpc_action" id="wpc_action3" value="reassign_tag">
                    <table cellpadding="0" cellspacing="0">
                        <tbody><tr>
                            <td style="width: 100px;">
                                <label for="old_tag_id"><?php _e( 'Tag From', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="old_tag_id" id="old_tag_id" style="min-width: 200px;">
                                    <?php foreach( $tags_list as $tag ) {
                                        if( (int)$tag['count']  > 0 ) { ?>
                                        <option value="<?php echo $tag['id']; ?>"><?php echo $tag['name']; ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="new_tag_id"><?php _e( 'Tag To', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="new_tag_id" id="new_tag_id" style="min-width: 200px;">
                                    <?php foreach( $tags_list as $tag ) { ?>
                                        <option value="<?php echo $tag['id']; ?>"><?php echo $tag['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    </tbody></table>
                    <br>
                    <div class="save_button">
                        <input type="submit" class="button-primary" name="" value="Reassign" id="reassign_files">
                    </div>
                </form>
            </div>

            <form action="" method="get" name="wpc_file_form" id="wpc_tags_form">
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="tags" />
                <?php $ListTable->display(); ?>
            </form>
        </div>
    </div>
</div>