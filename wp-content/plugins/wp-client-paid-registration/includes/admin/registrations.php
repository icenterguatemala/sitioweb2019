<?php
global $wpdb;

if ( isset( $_GET['action'] ) && 'delete' == $_GET['action'] ) {
    $id         = $_GET['id'];
    $t_name     = $wpdb->prefix . "wpc_client_login_redirects";
    $user_data  = get_userdata($id);

    $wpdb->query($wpdb->prepare("DELETE FROM $t_name WHERE rul_value=%s",$user_data->user_login));

    wp_delete_user( $id, $reassign );
    $_GET['msg'] = 'd';
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_PR_Registrations_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();
    var $names_actions = array();


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

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_action( $item ) {
        $all = $this->names_actions;
        return ( isset( $all[ $item['action'] ] ) ) ? $all[ $item['action'] ] : '' ;
    }


    function column_date( $item ) {
        return WPC()->date_format( strtotime( $item['date'] ) );
    }

    function extra_tablenav( $which ) {}

    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }
}

$ListTable = new WPC_PR_Registrations_List_Table( array(
    'singular'  => __( 'Registration', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Registrations', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_pr_registrations_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array() );
$ListTable->set_bulk_actions(array());

$ListTable->set_columns(array(
    'user_login'    => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'order_id'      => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
    'display_name'  => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
    'business_name' => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
    'user_email'    => __( 'Email', WPC_CLIENT_TEXT_DOMAIN ),
));

$args = array(
    'role'          => 'wpc_client',
    'meta_key'      => 'wpc_need_pay',
    'fields'        => 'ids',
);
$ids = get_users( $args );
$items_count = count( $ids );

$sql = "SELECT u.*,
               um.meta_value AS business_name,
               p.order_id AS order_id
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = 'wpc_cl_business_name' )
    LEFT JOIN {$wpdb->prefix}wpc_client_payments p ON ( u.ID = p.client_id )
    WHERE u.ID IN( '" . implode( "','", $ids ) . "' )
    ORDER BY u.ID DESC
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";

$registrations = $wpdb->get_results( $sql, ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $registrations;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>
    <div class="icon32" id="icon-link-manager"></div>
    <h2><?php _e( 'Paid Registrations', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

    <p><?php printf( __( 'Here you can see all the %s who have registered, but not yet completed the payment process.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></p>
    <form action="" method="get" name="pr_form" id="pr_form">
        <input type="hidden" name="page" value="wpclients_paid_registration" />
        <?php $ListTable->display(); ?>
    </form>
</div>
