<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb, $wpc_payments_core;
$where_client     = '';
$where_function   = '';
$where_status     = '';
$all_status       = array();
$all_counts       = array();
$all_filter       = array( 'Function' => 'function', WPC()->custom_titles['client']['s'] => 'client' );
$all_functions    = $wpdb->get_col( "SELECT DISTINCT function FROM {$wpdb->prefix}wpc_client_payments ORDER BY function ASC" );
$all_count        = $wpdb->get_var( "SELECT count(id) FROM {$wpdb->prefix}wpc_client_payments WHERE order_status != 'selected_gateway'" );
$all_order_status = $wpdb->get_col( "SELECT DISTINCT order_status FROM {$wpdb->prefix}wpc_client_payments WHERE order_status != 'selected_gateway' ORDER BY order_status ASC" );


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_payments';
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}

if ( !empty( $_REQUEST['act'] ) && 'change_to_paid' == $_REQUEST['act'] ) {

    $order_id = isset( $_GET['order_id'] ) ? $_GET['order_id'] : '';
    if ( $order_id ) {
        check_admin_referer( 'changestatusofpayment' . $order_id . get_current_user_id() );

        $order = $wpc_payments_core->get_order_by( $order_id, 'order_id' );

        $payment_data = array();
        $payment_data['transaction_status'] = "Paid";
        $payment_data['transaction_type'] = 'paid';
        $payment_data['transaction_id'] = $order['transaction_id'];

        $wpc_payments_core->order_update( $order['id'], $payment_data );

        WPC()->redirect( remove_query_arg( array( 'act', 'order_id', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    }
}


//Status filter
foreach ( $all_order_status as $status ) {
    $key = str_replace( '_', ' ', $status );
    $key = ucwords( $key );
    $count = $wpdb->get_var( "SELECT count(id) FROM {$wpdb->prefix}wpc_client_payments WHERE order_status='$status'" );
    $all_status[ $key ] = $status;
    $all_counts[ $key ] = $count;
}

if ( isset( $_GET['filter_status'] ) && in_array( $_GET['filter_status'], $all_status ) ) {
    $where_status = " AND order_status='" . esc_sql( $_GET['filter_status'] ) . "'";
}

//filter
if ( isset( $_GET['change_filter'] ) ) {
    switch ( $_GET['change_filter'] ) {
        case 'client':
            if ( isset( $_GET['filter_client'] ) ) {
                $filter_client = (int)$_GET['filter_client'];
                $where_client = " AND client_id=$filter_client";
            }
            break;
        case 'function':
            if ( isset( $_GET['filter_function'] ) ) {
                $filter_function = $_GET['filter_function'];
                if ( is_array( $all_functions ) && in_array( $filter_function, $all_functions ) )
                    $where_function = " AND function='" . esc_sql( $filter_function ) . "'";
            }
            break;
    }
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'cp.amount',
        'cp.payment_method',
    ) );
}

$order_by = 'time_paid';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'status' :
            $order_by = 'order_status';
            break;
        case 'client' :
            $order_by = 'client_login';
            break;
        case 'payment_method' :
            $order_by = 'payment_method';
            break;
        case 'amount' :
            $order_by = 'amount * 1';
            break;
        case 'date' :
            $order_by = 'time_paid';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Payments_List_Table extends WP_List_Table {

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

    function column_time_paid( $item ) {
        return WPC()->date_format( $item['time_paid'] );
    }

    function column_amount( $item ) {
        return WPC()->get_price_string( $item['amount'], '', $item['currency'] );
    }

    function column_transaction_id( $item ) {
        return $item['transaction_id'];
    }

    function column_client( $item ) {
        return $item['client_login'];
    }

    function column_status( $item ) {
        $note = '';
        if ( 'on_hold' === $item['status'] && !empty( $item['order_data'] ) ) {
            $data = json_decode( $item['order_data'] );
            if( !empty( $data->ipn_note ) ) {
                $note = WPC()->admin()->tooltip( $data->ipn_note );
            }
        } elseif ( 'pending' === $item['status'] && 'invoice-me' == $item['payment_method'] ) {
            $note .= '<a href="' . $_SERVER['REQUEST_URI'] . '&act=change_to_paid&order_id=' . $item['order_id'] . '&_wpnonce=' . wp_create_nonce( 'changestatusofpayment' . $item['order_id'] . get_current_user_id() ) .'"" title="' . __( 'Change this payment to Paid', WPC_CLIENT_TEXT_DOMAIN ) . '"> ' . __( '(set as Paid)', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }
        return $item['status'] . $note;
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }


    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb;
            $all_filter       = array( 'Function' => 'function', WPC()->custom_titles['client']['s'] => 'client' );
            $all_functions    = $wpdb->get_col( "SELECT DISTINCT function FROM {$wpdb->prefix}wpc_client_payments ORDER BY function ASC" ); ?>
            <div class="alignleft actions">
                <select name="change_filter" id="change_filter">
                    <option value="-1" <?php if( !isset( $_GET['change_filter'] ) || !in_array( $_GET['change_filter'], $all_filter ) ) echo 'selected'; ?>><?php _e( 'Select Filter', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php foreach ( $all_filter as $key => $type_filter ) {
                        $selected = ( isset( $_GET['change_filter'] ) && $type_filter == $_GET['change_filter'] ) ? ' selected' : '' ;
                        echo '<option value="' . $type_filter . '"' . $selected . ' >';
                        echo $key;
                        echo '</option>';
                    } ?>
                </select>
                <select name="select_filter" id="select_filter" <?php if ( !isset( $_GET['change_filter'] ) || !in_array( $_GET['change_filter'], $all_filter ) ) echo 'style="display: none;"'; ?>>
                    <?php if ( isset( $_GET['change_filter'] ) ) {
                        if ( 'function' == $_GET['change_filter'] && isset( $_GET['filter_function'] ) ) { ?>
                            <option value="-1" <?php if ( !in_array( $_GET['filter_function'], $all_functions ) ) echo 'selected'; ?>><?php _e( 'Select Function', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <?php if ( is_array( $all_functions ) && 0 < count( $all_functions ) ) {
                                foreach ( $all_functions as $function ) {
                                    if ( '' != $function ) {
                                        $selected = ( $function == $_GET['filter_function'] ) ? 'selected' : '';
                                        echo '<option value="' . $function . '" ' . $selected . ' >' . $function . '</option>';
                                    }
                                }
                            }
                        } elseif ( 'client' == $_GET['change_filter'] && isset( $_GET['filter_client'] ) ) {
                            $unique_clients   = $wpdb->get_col( "SELECT DISTINCT client_id FROM {$wpdb->prefix}wpc_client_payments" ); ?>
                            <option value="-1" <?php if ( !in_array( $_GET['filter_client'], $unique_clients ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                            <?php if ( is_array( $unique_clients ) && 0 < count( $unique_clients ) ) {
                                foreach( $unique_clients as $client_id ) {
                                    if ( '' != $client_id ) {
                                        $selected = ( $client_id == $_GET['filter_client'] ) ? 'selected' : '';
                                        echo '<option value="' . $client_id . '" ' . $selected . ' >' . get_userdata( $client_id )->user_login . '</option>';
                                    }
                                }
                            }
                        }
                    } ?>
                </select>
                <span id="load_select_filter"></span>
                <input type="button" id="filtered" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" name="" />
                <a class="add-new-h2" id="cancel_filter" <?php if( !isset( $_GET['filter_function']) && !isset( $_GET['filter_client']) ) echo 'style="display: none;"'; ?> >
                    <?php _e( "Remove Filter", WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <span style="color: #bc0b0b;">&times;</span>
                </a>
            </div>
            <?php $this->search_box( __( 'Search Payments', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }
}


$ListTable = new WPC_Payments_List_Table( array(
    'singular'  => __( 'Payment', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_payments_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'client'           => 'client',
    'status'           => 'status',
    'payment_method'   => 'payment_method',
    'time_paid'        => 'time_paid',
    'amount'           => 'amount',
) );

$ListTable->set_bulk_actions(array(
));

$ListTable->set_columns(array(
    'order_id'              => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
    'client'                => WPC()->custom_titles['client']['s'],
    'status'                => __( 'Status', WPC_CLIENT_TEXT_DOMAIN ),
    'payment_method'        => __( 'Payment Method', WPC_CLIENT_TEXT_DOMAIN ),
    'transaction_id'        => __( 'Transaction ID', WPC_CLIENT_TEXT_DOMAIN ),
    'amount'                => __( 'Amount', WPC_CLIENT_TEXT_DOMAIN ),
    'time_paid'             => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
));


$sql = "SELECT count( cp.id )
    FROM {$wpdb->prefix}wpc_client_payments cp
    LEFT JOIN {$wpdb->users} u ON (cp.client_id = u.ID)
    WHERE order_status !='selected_gateway'
        {$where_function}
        {$where_client}
        {$where_status}
        {$where_clause}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT cp.order_id as order_id, cp.function as function, cp.order_status as status, cp.payment_method as payment_method, cp.client_id as client_id, u.user_login as client_login, cp.amount as amount, cp.currency as currency, cp.transaction_id as transaction_id, cp.time_paid as time_paid, cp.data as order_data
    FROM {$wpdb->prefix}wpc_client_payments cp
    LEFT JOIN {$wpdb->users} u ON (cp.client_id = u.ID)
    WHERE order_status !='selected_gateway'
        {$where_function}
        {$where_client}
        {$where_status}
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$cols = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $cols;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <div class="icon32" id="icon-link-manager"></div>
        <h2><?php _e( 'Payment History', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <p><?php _e( 'From here, you can see all payment operations.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>

        <ul class="subsubsub">
            <li class="all"><a class="<?php echo ( !isset( $_GET['filter_status'] ) || !in_array( $_GET['filter_status'], $all_status ) ) ? 'current' : '' ?>" href="admin.php?page=wpclients_payments"  ><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo $all_count ?>)</span></a></li>
            <?php foreach ( $all_status as $key => $status ) {
                $count = $all_counts[ $key ]; ?>
                <li class="image"> | <a class="<?php echo ( isset( $_GET['filter_status'] ) && $status == $_GET['filter_status'] ) ? 'current' : '' ?>" href="admin.php?page=wpclients_payments&filter_status=<?php echo $status; ?>"><?php echo $key ?> <span class="count">(<?php echo $count ?>)</span></a></li>
            <?php } ?>
        </ul>

        <span class="wpc_clear"></span>

        <form action="" method="get" name="wpc_clients_form" id="wpc_payments_form" style="width: 100%;">
            <input type="hidden" name="page" value="wpclients_payments" />
            <?php $ListTable->display(); ?>
        </form>
    </div>

    <script type="text/javascript">
        var site_url = '<?php echo site_url();?>';

        jQuery(document).ready(function(){

            //reassign file from Bulk Actions
            jQuery( '#doaction2' ).click( function() {
                var action = jQuery( 'select[name="action2"]' ).val() ;
                jQuery( 'select[name="action"]' ).attr( 'value', action );
                return true;
            });

            //change filter
            jQuery( '#change_filter' ).change( function() {
                if ( '-1' != jQuery( '#change_filter' ).val() ) {
                    var filter = jQuery( '#change_filter' ).val();
                    jQuery( '#select_filter' ).css( 'display', 'none' );
                    jQuery( '#select_filter' ).html( '' );
                    jQuery( '#load_select_filter' ).addClass( 'wpc_ajax_loading' );
                    jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_get_options_filter_for_payments&filter=' + filter,
                    dataType: 'html',
                    success: function( data ){
                        jQuery( '#select_filter' ).html( data );
                        jQuery( '#load_select_filter' ).removeClass( 'wpc_ajax_loading' );
                        jQuery( '#select_filter' ).css( 'display', 'block' );
                    }
                    });
                }
                else jQuery( '#select_filter' ).css( 'display', 'none' );
                return false;
            });

            //filter
            jQuery( '#filtered' ).click( function() {
                if ( '-1' != jQuery( '#change_filter' ).val() && '-1' != jQuery( '#select_filter' ).val() ) {
                    var req_uri = "<?php echo preg_replace( '/&filter_client=[0-9]+|&filter_circle=[0-9]+|&change_filter=[a-z]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                    //if ( in_array() )
                    switch( jQuery( '#change_filter' ).val() ) {
                        case 'function':
                            window.location = req_uri + '&filter_function=' + jQuery( '#select_filter' ).val() + '&change_filter=function';
                            break;
                        case 'client':
                            window.location = req_uri + '&filter_client=' + jQuery( '#select_filter' ).val() + '&change_filter=client';
                            break;
                }
                }
                return false;
            });


            jQuery( '#cancel_filter' ).click( function() {
                var req_uri = "<?php echo preg_replace( '/&filter_client=[0-9]+|&filter_function=[a-z_-]+|&change_filter=[a-z_-]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                window.location = req_uri;
                return false;
            });

        });
    </script>

</div>