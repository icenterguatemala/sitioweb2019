<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_modify_taxes' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=invoicing_taxes';
}

if ( isset( $_REQUEST['action'] ) ) {
    switch ( $_REQUEST['action'] ) {

        //delete
        case 'delete':
            $ids = array();
            if ( isset( $_GET['name'] ) ) {
                check_admin_referer( 'wpc_tax_delete' .  $_GET['name'] . get_current_user_id() );
                $ids = (array) $_REQUEST['name'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }
            if ( count( $ids ) ) {
                foreach ( $ids as $tax ) {
                    //delete tax
                    $this->delete_tax( $tax );
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;

        case 'save':
           if ( isset( $_POST['tax'] ) ) {
                check_admin_referer( 'wpc_tax_save' . get_current_user_id() );
                $this->save_tax( $_POST['tax'] );
           }
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

class WPC_Taxes_List_Table extends WP_List_Table {

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

    function column_rate( $item ) {
        return '<span id="tax_rate_' . $item['id'] . '">' . $item['rate'] . '</span>';
    }

    function column_description( $item ) {
        return '<span id="tax_desc_' . $item['id'] . '">'
                . htmlspecialchars( $item['description'] ) . '</span>';
    }

    function column_name( $item ) {
        $actions['edit'] = '<a class="various" href="javascript:void(0);" title="" rel="' . $item['id'] . '" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Tax? ', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&tab=invoicing_taxes&action=delete&name=' . $item['name'] . '&_wpnonce='  . wp_create_nonce( 'wpc_tax_delete' . $item['name'] . get_current_user_id() ) . '">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<strong><a href="javascript:void(0);" class="row-title" id="tax_name_' . $item['id'] . '">'
                . htmlspecialchars( $item['name'] ) . '</a></strong>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_Taxes_List_Table( array(
    'singular'  => __( 'Tax', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));


$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_inv_invoicing_taxes_per_page' );
$paged      = $ListTable->get_pagenum();


$ListTable->set_sortable_columns( array(
) );

$ListTable->set_bulk_actions(array(
    'delete'        => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'name'                  => __( 'Tax Name', WPC_CLIENT_TEXT_DOMAIN ),
    'description'           => __( 'Tax Description', WPC_CLIENT_TEXT_DOMAIN ),
    'rate'                  => __( 'Tax Rate (%)', WPC_CLIENT_TEXT_DOMAIN ),
));


$taxes = $this->get_taxes();
$items_count = count( $taxes );

$taxes = array_slice( $taxes, $per_page * ( $paged - 1 ), $per_page );

$ListTable->prepare_items();
$ListTable->items = $taxes;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );



/*$sql = "SELECT *
    FROM {$wpdb->prefix}wpc_client_invoicing_items
    WHERE 1=1
        {$filter_use}
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$cols = $wpdb->get_results( $sql, ARRAY_A );*/


?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 's':
                echo '<div id="message" class="updated"><p>' . __( 'Tax <strong>Saved</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated"><p>' . __( 'Tax is <strong>deleted</strong>.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    } ?>

    <div class="clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_taxes" style="position: relative;">
            <a href="javascript:void(0);" id="new_tax" class="add-new-h2">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>

            <form action="" method="get" name="wpc_clients_form" id="wpc_clients_form">
                <input type="hidden" name="page" value="wpclients_invoicing" />
                <input type="hidden" name="tab" value="invoicing_taxes" />
                <?php $ListTable->display(); ?>
            </form>

            <div class="wpc_edit_tax" id="add_tax_form" style="display: none; float:left;width:100%;">
                <form method="post" name="wpc_add_tax" id="wpc_add_tax" style="float:left;width:100%;">
                    <table style="float:left;width:100%;">
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Tax Name:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <input type="text" name="tax[name]" style="float:left;width:100%;" id="tax_name" class="tax_name" />
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Tax Description:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <textarea name="tax[description]" style="float:left;width:100%;" rows="5" id="tax_description" class="tax_description"></textarea>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Tax Rate:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <input type="text" name="tax[rate]" style="float:left;width:100%;" id="tax_rate" class="tax_rate" />
                                </label>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div style="clear: both; text-align: center;">
                        <input type="hidden" id="action" name="action" value="save" />
                        <input type="hidden" value="<?php echo wp_create_nonce( 'wpc_tax_save' . get_current_user_id() ) ?>" name="_wpnonce" id="_wpnonce" />
                        <input type="button" class="button-primary" id="save_tax" name="save_tax" value="<?php _e( 'Save Tax', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        jQuery('#new_tax').shutter_box({
            view_type       : 'lightbox',
            width           : '560px',
            type            : 'inline',
            href            : '#add_tax_form',
            title           : '<?php _e( 'New Tax', WPC_CLIENT_TEXT_DOMAIN ) ?>'
        });

        jQuery('.various').each( function() {
            var id = jQuery( this ).attr( 'rel' );

            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '560px',
                type            : 'ajax',
                dataType        : 'json',
                href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                ajax_data       : "action=inv_get_tax_data&id=" + id,
                setAjaxResponse : function( data ) {
                    jQuery( '.sb_lightbox_content_title' ).html( data.title );
                    jQuery( '.sb_lightbox_content_body' ).html( data.content );
                }
            });
        });

        jQuery( '.row-title').click( function() {
            jQuery(this).parents('td').find( '.various').trigger('click');
        });



        jQuery('body').on('keypress', '.tax_rate', function(e) {
            if ( e.which === 8 || e.which === 0 ) {
                return true;
            }
            if ( e.which === 44 ) {
                this.value += '.';
                return false;
            }
            if ( ( e.which >= 48 && e.which <= 57 ) || e.which === 46 ) {
                return true;
            }

            return false;
        });

        //Save Tax
        jQuery('body').on('click', '#save_tax', function() {
            jQuery( this).parents('form').submit();
        });


        //reassign file from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });

    });
</script>