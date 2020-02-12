<?php

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_modify_feedback_items' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_feedback_wizard&tab=items';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        //delete wizard
        case 'delete':
            $ids = array();
            if ( isset( $_GET['item_id'] ) ) {
                check_admin_referer( 'wpc_item_delete' .  $_GET['item_id'] . get_current_user_id() );
                $ids = (array) $_REQUEST['item_id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Items', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) ) {
                foreach ( $ids as $item_id ) {
                   //delete item
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_feedback_items WHERE item_id = %d", $item_id ) );

                    //delete item from wizard
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_feedback_wizard_items WHERE item_id = %d", $item_id ) );
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;
    }
}



//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}

$order_by = 'item_id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'type' :
            $order_by = 'type';
            break;
        case 'name' :
            $order_by = 'name';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Items_List_Table extends WP_List_Table {

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
        _e( $this->no_items_message, WPC_CLIENT_TEXT_DOMAIN );
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

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]"` value="%s" />', $item['item_id']
        );
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_type( $item ) {
        switch( $item['type'] ) {
            case 'img':
                return __( 'Image', WPC_CLIENT_TEXT_DOMAIN );
                break;

            case 'pdf':
                return __( 'PDF', WPC_CLIENT_TEXT_DOMAIN );
                break;

            case 'att':
                return __( 'Attachment', WPC_CLIENT_TEXT_DOMAIN );
                break;
        }
    }

    function column_name( $item ) {

        $actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclients_feedback_wizard&tab=edit_item&item_id=' . $item['item_id'] . '" title="edit ' . stripslashes( $item['name'] ) . '" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Items?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_feedback_wizard&tab=items&action=delete&item_id=' . $item['item_id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_item_delete' . $item['item_id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<a href="admin.php?page=wpclients_feedback_wizard&tab=edit_item&item_id=' . $item['item_id'] . '" title="edit ' . $item['name'] . '">' . stripslashes( $item['name'] ) . '</a>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Items_List_Table( array(
    'singular'  => __( 'Item', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_fw_items_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'name'           => 'name',
    'type'           => 'type',
) );

$ListTable->set_bulk_actions(array(
    'delete'    => 'Delete',
));

$ListTable->set_columns(array(
    'name'              => __( 'Item Name', WPC_CLIENT_TEXT_DOMAIN ),
    'type'                => __( 'Item type', WPC_CLIENT_TEXT_DOMAIN ),
));


$sql = "SELECT count( item_id )
    FROM {$wpdb->prefix}wpc_client_feedback_items
    WHERE 1=1
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT item_id, name, type
    FROM {$wpdb->prefix}wpc_client_feedback_items
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$items = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $items;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Item <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Item <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Item(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>

    <div id="wpc_container">

        <?php echo $this->gen_feedback_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_fw_items" style="position: relative;">
            <a href="admin.php?page=wpclients_feedback_wizard&tab=add_item" class="add-new-h2">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>

            <form method="get" id="items_form" name="items_form" >
                <input type="hidden" name="page" value="wpclients_feedback_wizard" />
                <input type="hidden" name="tab" value="items" />

                <?php $ListTable->display(); ?>
            </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function(){

                    //reassign file from Bulk Actions
                    jQuery( '#doaction2' ).click( function() {
                        var action = jQuery( 'select[name="action2"]' ).val() ;
                        jQuery( 'select[name="action"]' ).attr( 'value', action );
                        return true;
                    });
            });
        </script>
    </div>

</div>
