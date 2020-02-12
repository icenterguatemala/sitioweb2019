<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_modify_items' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=invoicing_items';
}

if ( isset( $_REQUEST['action'] ) ) {
    switch ( $_REQUEST['action'] ) {

        //delete
        case 'delete':
            $ids = array();
            if ( isset( $_GET['id'] ) ) {
                check_admin_referer( 'wpc_item_inv_delete' .  $_GET['id'] . get_current_user_id() );
                $ids = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Items', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }
            if ( count( $ids ) ) {
                //delete item
                $this->delete_items( $ids );
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;

        case 'save':
           if ( isset( $_POST['save_item'] ) ) {
                check_admin_referer( 'wpc_inv_items_save' . get_current_user_id() );
                $errors = $this->save_items( $_POST['save_item'] );
                if ( '' == $errors ) {
                    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients_invoicing&tab=invoicing_items&msg=s' );
                }
           }
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}

//filter by Use for Request Estimates
$filter_use = '';
if ( !empty( $_GET['filter_option'] ) ) {
    if ( 'use' == $_GET['filter_option'] ) {
        $filter_use = " AND use_r_est = 1" ;
    } elseif( 'not_use' == $_GET['filter_option'] ) {
        $filter_use = " AND ISNULL(use_r_est)" ;
    }
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'description',
        'name',
        'rate',
    ) );
}

$order_by = 'name';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'name' :
            $order_by = 'name';
            break;
        case 'description' :
            $order_by = 'description';
            break;
        case 'rate' :
            $order_by = 'rate * 1';
            break;
    }
}

$order = ( !isset( $_GET['order'] ) || 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Item_Invoice_List_Table extends WP_List_Table {

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_use_r_est( $item ) {
        $checked = ( !empty( $item['use_r_est'] ) ) ? 'checked' : '' ;
        $html = '<div class="wpc_text_center"><input type="checkbox" disabled ' . $checked . '></div>';
        return $html;
    }

    function column_description( $item ) {
        return  nl2br(htmlspecialchars($item['description']) )
                . '<span style="display: none;" id="item_description_block_' . $item['id'] . '">'
                . htmlspecialchars( $item['description'] ) . '</span>';
    }

    function column_rate( $item ) {
        global $wpc_inv;
       return '<span id="item_rate_block_' . $item['id'] . '">' . $wpc_inv->get_currency( $item['rate'], true ) . '</span>';
    }

    function column_name( $item ) {
        $actions['edit'] = '<a class="various" href="javascript:void(0);" title="" rel="' . $item['id'] . '" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Item?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&tab=invoicing_items&action=delete&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_item_inv_delete' . $item['id'] . get_current_user_id() ) . '">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<strong><a href="javascript:void(0);" class="row-title" id="item_name_block_' . $item['id'] . '">' . htmlspecialchars( $item['name'] ) . '</a></strong>', $this->row_actions( $actions ) );
    }


    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( __( 'Search Items', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Item_Invoice_List_Table( array(
    'singular'  => __( 'Item', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));


$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_inv_invoicing_items_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'name'           => 'name',
    'description'    => 'description',
    'rate'           => 'rate',
) );

$ListTable->set_bulk_actions(array(
    'delete'        => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'name'                  => __( 'Item Name', WPC_CLIENT_TEXT_DOMAIN ),
    'use_r_est'             => '<div class="wpc_text_center">' . __( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN ) . '</div>',
    'description'           => __( 'Description', WPC_CLIENT_TEXT_DOMAIN ),
    'rate'                  => __( 'Rate', WPC_CLIENT_TEXT_DOMAIN ),
));


$sql = "SELECT count( id )
    FROM {$wpdb->prefix}wpc_client_invoicing_items
    WHERE 1=1
        {$filter_use}
        {$where_clause}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT *
    FROM {$wpdb->prefix}wpc_client_invoicing_items
    WHERE 1=1
        {$filter_use}
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$cols = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $cols;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<style>
    #wpc_clients_form .search-box {
        float:left;
        padding: 2px 8px 0 0;
    }

    #wpc_clients_form .search-box input[type="search"] {
        margin-top: 1px;
    }

    #wpc_clients_form .search-box input[type="submit"] {
        margin-top: 1px;
    }
</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 's':
                echo '<div id="message" class="updated"><p>' . __( 'Item is <strong>Saved</strong>.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated"><p>' . __( 'Item(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>

    <div class="clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <div id="message" class="error" <?php echo ( empty( $errors ) ) ? 'style="display: none;">' : '>' . $errors ?></div>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_items">
            <form action="" method="get" name="wpc_clients_form" id="wpc_clients_form">
                <a href="javascript:void(0);" class="add-new-h2 various" style="float:left;">
                    <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </a>
                <ul class="subsubsub" style="margin: -3px 0 0 20px;" >
                    <?php global $wpdb;

                    $array_choices = array(
                        'all'       => __( 'All', WPC_CLIENT_TEXT_DOMAIN ),
                        'use'       => __( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                        'not_use'   => __( 'Not Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                    );
                    $all_keys = array_keys( $array_choices );
                    $first_key = array_shift( $all_keys );

                    $filter_option = !empty( $_GET['filter_option'] ) ? (string)$_GET['filter_option'] : $first_key;

                    $all_count = $count_use = 0;
                    $counts = $wpdb->get_results( "SELECT count(*) as total, use_r_est as used FROM {$wpdb->prefix}wpc_client_invoicing_items GROUP BY use_r_est", ARRAY_A );
                    foreach ( $counts as $val ) {
                        if ( 1 == $val['used'] ) {
                            $count_use = $val['total'];
                        } else {
                            $all_count = $val['total'];
                        }
                    }
                    $all_count += $count_use;

                    foreach ( $array_choices as $key => $item ) {
                        switch( $key ) {
                            case 'all':
                                $count = $all_count;
                                break;

                            case 'use':
                                $count = $count_use;
                                break;

                            case 'not_use':
                                $count = $all_count - $count_use;
                                break;

                        }

                        if ( 'all' !== $key ) {
                            echo ' | ';
                        } ?>

                        <li class="wpc_inv_<?php echo $key ?>">
                            <a class="<?php echo ( $key == $filter_option ) ? 'current' : '' ?>" href="admin.php?page=wpclients_invoicing&tab=invoicing_items<?php echo ( $first_key != $key ) ? '&filter_option=' . $key : '' ?>"  >
                                <?php echo $item ?>
                                <span class="count">(<?php echo $count ?>)</span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <input type="hidden" name="page" value="wpclients_invoicing" />
                <input type="hidden" name="tab" value="invoicing_items" />
                <?php $ListTable->display(); ?>
            </form>

        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        jQuery( '.various').each( function() {
            var id = jQuery( this ).attr( 'rel' );
            if ( id === undefined ) {
                id = '';
            }

            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '560px',
                type            : 'ajax',
                dataType        : 'json',
                href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                ajax_data       : "action=inv_get_item_data&id=" + id,
                setAjaxResponse : function( data ) {
                        jQuery( '.sb_lightbox_content_title' ).html( data.title );
                        jQuery( '.sb_lightbox_content_body' ).html( data.content );

//                        jQuery('textarea[maxlength]').trigger( 'keyup' );
                    }
                 });
        });

        jQuery( '.row-title').click( function() {
            jQuery(this).parents('td').find( '.various').trigger('click');
        });

        jQuery('body').on('keypress', '.item_rate', function(e) {
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

        //Save item
        jQuery('body').on('click', '#save_item', function() {
            var errors = 0;

            if ( '' === jQuery(this).parents('form').find( ".item_name" ).val() ) {
                jQuery(this).parents('form').find( ".item_name" ).parent().parent().attr( 'class', 'wpc_error' );
                errors = 1;
            } else {
                jQuery(this).parents('form').find( ".item_name" ).parent().parent().attr( 'class', '' );
            }
            if ( '' === jQuery(this).parents('form').find( ".item_rate" ).val() ) {
                jQuery(this).parents('form').find( ".item_rate" ).parent().parent().attr( 'class', 'wpc_error' );
                errors = 1;
            } else {
                jQuery(this).parents('form').find( ".item_rate" ).parent().parent().attr( 'class', '' );
            }

            if ( 0 === errors ) {
                jQuery(this).parents('form').submit();
            }

            return false;
        });

        //reassign items from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });
    });
</script>