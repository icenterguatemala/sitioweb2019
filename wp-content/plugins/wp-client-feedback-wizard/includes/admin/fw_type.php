<?php

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
    $this->redirect_available_page();
}

global $wpdb;
$types = array();

$wpc_feedback_types = get_option( 'wpc_feedback_types' );
if( isset( $wpc_feedback_types ) && is_array( $wpc_feedback_types ) && 0 < count( $wpc_feedback_types ) ) {
    foreach ( $wpc_feedback_types as $key => $value ) {
        $value['id'] = $key;
        $value['name'] = $key;
        $types[] = $value;
    }
}
if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_feedback_wizard&tab=feedback_type';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        //delete wizard
        case 'delete':
            $ids = array();
            if ( isset( $_GET['item_delete'] ) ) {
                check_admin_referer( 'wpc_type_delete' .  $_GET['item_delete'] . get_current_user_id() );
                $ids = (array) $_REQUEST['item_delete'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Types', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) ) {
                foreach ( $ids as $item_id ) {
                    unset( $wpc_feedback_types[ $item_id ] );
                    update_option( 'wpc_feedback_types', $wpc_feedback_types );
                    $client_ids = get_users( array( 'role' => 'wpc_client', 'meta_key' => $item_id, 'fields' => 'ID', ) );
                    if ( is_array( $client_ids ) && 0 < count( $client_ids ) ) {
                        foreach( $client_ids as $id ) {
                            delete_user_meta( $id, $item_id );
                        }
                    }
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
            '<input type="checkbox" name="item[]" value="%s" />', $item['name']
        );
    }

    function column_type( $item ) {
        switch( $item['type'] ) {
            case 'button':
                return __( 'Buttons', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'radio':
                return __( 'Radio Buttons', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'checkbox':
                return __( 'Checkboxes', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'selectbox':
                return __( 'Select Box', WPC_CLIENT_TEXT_DOMAIN );
            break;
        }
    }

    function column_title( $item ) {
        return stripslashes( $item['title'] );
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_name( $item ) {

        $actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclients_feedback_wizard&tab=feedback_type&edit=' . $item['name'] . '" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Feedback Type?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_feedback_wizard&tab=feedback_type&action=delete&item_delete=' . $item['name'] . '&_wpnonce=' . wp_create_nonce( 'wpc_type_delete' . $item['name'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<span>' . $item['name'] . '</span>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Items_List_Table( array(
    'singular'  => __( 'Type', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Types', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_fw_types_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
) );

$ListTable->set_bulk_actions(array(
    'delete'    => 'Delete',
));

$ListTable->set_columns(array(
    'name'              => __( 'Type Name', WPC_CLIENT_TEXT_DOMAIN ),
    'title'              => __( 'Type Title', WPC_CLIENT_TEXT_DOMAIN ),
    'type'                => __( 'Type', WPC_CLIENT_TEXT_DOMAIN ),
));

$items_count = count( $types );
$items = $types;

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
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Feedback Type <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Feedback Type <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Feedback Type <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>

    <div id="wpc_container">

        <?php echo $this->gen_feedback_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_fw_feedback_types" style="position: relative;">
            <a href="admin.php?page=wpclients_feedback_wizard&tab=feedback_type&add=1" class="add-new-h2">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>

            <form method="get" id="items_form" name="items_form" >
                <input type="hidden" name="page" value="wpclients_feedback_wizard" />
                <input type="hidden" name="tab" value="feedback_type" />

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