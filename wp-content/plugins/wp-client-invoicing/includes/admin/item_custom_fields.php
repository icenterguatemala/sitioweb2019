<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_inv_custom_fields' ) ) {
    $this->redirect_available_page();
}

global $wpdb;
$fields = array();
$wpc_custom_fields = WPC()->get_settings( 'inv_custom_fields' );
$types = array();
$i = 0;
foreach ( $wpc_custom_fields as $key => $value ) {
    $i++;
    $value['id'] = $i;
    $value['name'] = $key;
    $types[] = $value;
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=item_custom_fields';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        //delete wizard
        case 'delete':
            $ids = array();
            if ( isset( $_GET['name'] ) ) {
                check_admin_referer( 'wpc_field_delete' .  $_GET['name'] . get_current_user_id() );
                $ids = (array) $_GET['name'];
            } elseif( isset( $_REQUEST['item'] ) ) {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Fields', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) ) {
                foreach ( $ids as $item_id ) {
                    unset( $wpc_custom_fields[ $item_id ] );
                }
                WPC()->settings()->update( $wpc_custom_fields, 'inv_custom_fields' );
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

class WPC_Invoice_Fields_List_Table extends WP_List_Table {

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['name']
        );
    }

    function column_type( $item ) {
        switch( $item['type'] ) {
            case 'text':
                return __( 'Text Box', WPC_CLIENT_TEXT_DOMAIN );
            case 'textarea':
                return __( 'Multi-line Text Box', WPC_CLIENT_TEXT_DOMAIN );
            case 'radio':
                return __( 'Radio Buttons', WPC_CLIENT_TEXT_DOMAIN );
            case 'checkbox':
                return __( 'Checkbox', WPC_CLIENT_TEXT_DOMAIN );
            case 'selectbox':
                return __( 'Select Box', WPC_CLIENT_TEXT_DOMAIN );
            case 'multiselectbox':
                return __( 'Multi Select Box', WPC_CLIENT_TEXT_DOMAIN );
            case 'hidden':
                return __( 'Hidden Field', WPC_CLIENT_TEXT_DOMAIN );

        }
    }



    function column_options( $item ) {
        $html = '<input type="checkbox" disabled ';
        $html .= ( isset( $item['display'] ) && '1' == $item['display'] ) ? 'checked' : '' ;
        $html .= ' />&nbsp;&nbsp;' . __( 'Checked by Default', WPC_CLIENT_TEXT_DOMAIN ) . '<br />' ;
        $html .= '<input type="checkbox" disabled ';
        $html .= ( isset( $item['field_readonly'] ) && '1' == $item['field_readonly'] ) ? 'checked' : '';
        $html .= ' />&nbsp;&nbsp;' . __( 'Readonly', WPC_CLIENT_TEXT_DOMAIN ) ;

        return $html;
    }

    function column_id( $item ) {
        return '<span class="order_num">' . $item['id'] . '</span><span class="order_img"></span>' ;
    }

    function column_title( $item ) {
        return ( isset( $item['title'] ) ) ? htmlspecialchars( $item['title'] ) : '' ;
    }

    function column_description( $item ) {
        return ( isset( $item['description'] ) ) ? htmlspecialchars( $item['description'] ) : '' ;
    }

    function column_cf_placeholder( $item ) {
        return '{' . $item['name'] . '}' ;
    }


    function column_name( $item ) {
        $actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclients_invoicing&tab=item_custom_field_edit&edit=' . $item['name'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Custom Filed?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&tab=item_custom_fields&_wpnonce=' . wp_create_nonce( 'wpc_field_delete' . $item['name'] . get_current_user_id() ) . '&action=delete&name=' . $item['name'] . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;

        return sprintf('%1$s %2$s', '<span class="this_name" id="field_' . $item['name'] . '">' . $item['name'] . '</span>', $this->row_actions( $actions ) );
    }


    function wpc_get_items_per_page( $attr = false ) {
        return $this->get_items_per_page( $attr );
    }

    function wpc_set_pagination_args( $attr = false ) {
        return $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_Invoice_Fields_List_Table( array(
    'singular'  => __( 'Field', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Fields', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false

));

/*$per_page   = $ListTable->get_items_per_page( 'users_per_page' );
$paged      = $ListTable->get_pagenum();  */

$ListTable->set_sortable_columns( array(
) );

$ListTable->set_bulk_actions(array(
    'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'id'                => __( 'Order', WPC_CLIENT_TEXT_DOMAIN ),
    'name'              => __( 'Field Slug (ID)', WPC_CLIENT_TEXT_DOMAIN ),
    //'cf_placeholder'    => __( 'Placeholder', WPC_CLIENT_TEXT_DOMAIN ),
    'title'             => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
    'description'       => __( 'Description', WPC_CLIENT_TEXT_DOMAIN ),
    'type'              => __( 'Type', WPC_CLIENT_TEXT_DOMAIN ),
    'options'           => __( 'Options', WPC_CLIENT_TEXT_DOMAIN ),
));

$items_count = count( $types );
$items = $types;

$ListTable->prepare_items();
$ListTable->items = $items;
//$ListTable->set_pagination_args( array( 'total_items' => $items_count, 'per_page' => 10000000 ) );
$ListTable->_pagination_args = array();
?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo '<div id="message" class="updated"><p>' . __( 'Custom Field <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated"><p>' . __( 'Custom Field <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated"><p>' . __( 'Custom Field <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_item_custom_fields" style="position: relative;">
            <a href="admin.php?page=wpclients_invoicing&tab=item_custom_field_edit&add=1" class="add-new-h2">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>

            <form method="get" id="items_form" name="items_form" >
                <input type="hidden" name="page" value="wpclients_invoicing" />
                <input type="hidden" name="tab" value="item_custom_fields" />
                <?php $ListTable->display(); ?>
                <p>
                    <span class="description" ><img src="<?php echo WPC()->plugin_url . 'images/sorting_button.png' ?>" style="vertical-align: middle;" /> - <?php _e( 'Drag&Drop to change the order in which these fields appear on the item block for invoice.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                </p>
            </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function(){
                    jQuery( 'table.fields' ).attr( 'id', 'sortable' );
                    /*
                    * sorting
                    */

                    var fixHelper = function(e, ui) {
                        ui.children().each(function() {
                            jQuery(this).width(jQuery(this).width());
                        });
                        return ui;
                    };

                    jQuery( '#sortable tbody' ).sortable({
                        axis: 'y',
                        helper: fixHelper,
                        handle: '.column-id',
                        items: 'tr'
                    });

                    jQuery( '#sortable' ).bind( 'sortupdate', function(event, ui) {
                        new_order = '';
                        jQuery('.this_name').each(function() {
                                var id = jQuery(this).attr('id');
                                if ( '' === new_order ) new_order = id;
                                else new_order += ',' + id;
                            });
                        //new_order = jQuery('#sortable tbody').sortable('toArray');
                        //alert(new_order);
                        jQuery( 'body' ).css( 'cursor', 'wait' );

                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                            data: 'action=change_inv_custom_field_order&type=item&new_order=' + new_order,
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

