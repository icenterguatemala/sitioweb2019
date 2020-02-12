<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_approve_staff' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=staff_approve';
}
if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        /* delete action */
        case 'delete':

            $clients_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_staff_approve_delete' .  $_REQUEST['id'] . get_current_user_id() );
                $clients_id = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['staff']['p'] ) );
                $clients_id = $_REQUEST['item'];
            }

            if ( count( $clients_id ) ) {
                foreach ( $clients_id as $client_id ) {
                    if( is_multisite() ) {
                        wpmu_delete_user( $client_id );
                    } else {
                        wp_delete_user( $client_id );
                    }
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            }
            WPC()->redirect( $redirect );

        break;
        /* approve action */
        case 'approve':

            $clients_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_staff_approved' .  $_REQUEST['id'] . get_current_user_id() );
                $clients_id = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['staff']['p'] ) );
                $clients_id = $_REQUEST['item'];
            }
            if ( count( $clients_id ) ) {
                foreach ( $clients_id as $client_id ) {
                    delete_user_meta( $client_id, 'to_approve' );
                }
                WPC()->redirect( add_query_arg( 'msg', 'a', $redirect ) );
            }
            WPC()->redirect( $redirect );

        break;
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
}


global $wpdb;

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'u.user_email',
        'um2.meta_value',
    ) );
}

$not_approved = get_users( array( 'role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID', ) );
$not_approved = " AND u.ID IN ('" . implode( "','", $not_approved ) . "')";

$order_by = 'u.user_registered';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'username' :
            $order_by = 'u.user_login';
            break;
        case 'first_name' :
            $order_by = 'um2.meta_value';
            break;
        case 'email' :
            $order_by = 'u.user_email';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Staff_Approve_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();
    var $custom_fields = array();

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
        $hidden   = get_hidden_columns( $this->screen );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {

        if( isset( $this->custom_fields[ $column_name ] ) ) {
            return WPC()->custom_fields()->render_custom_field_value( $this->custom_fields[ $column_name ], array(
                'user_id' => $item['id'],
                'value' => maybe_unserialize ( isset($item[$column_name]) ? $item[$column_name] : '' ),
                'metadata_exists' => isset($item[$column_name]),
                'empty_value' => '<span title="' . __("Undefined", WPC_CLIENT_TEXT_DOMAIN) . '">-</span>'
            ));
        } else {
            $value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';

            /*our_hook_
            hook_name: wpc_client_{$column_name}_custom_column_of_staff_approve
            hook_title: Change default value of columns on Staff Approve page
            hook_description: Hook runs before echo default value of columns on Staff Approve page.
            hook_type: filter
            hook_in: wp-client
            hook_location clients.php
            hook_param: mixed $value
            hook_since: 4.3.1
            */
            return apply_filters( 'wpc_client_' . $column_name . '_custom_column_of_staff_approve', $value );
        }
    }

    function no_items() {
        echo $this->no_items_message;
    }

    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach ( $args as $k => $val ) {
            if ( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } else if ( is_string( $k ) ) {
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

    function column_client( $item ) {
        $parent_client_id = $item['parent_client_id'];
        $client_name = '';
        if ( 0 < $parent_client_id ) {
            $client = get_userdata( $parent_client_id );
            if ( $client ) {
                $client_name = $client->get( 'user_login' );
            }
        }

        return $client_name;
    }

    function column_username( $item ) {
        $actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=staff_approve&action=approve&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_staff_approved' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Approve', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        $actions['delete']  = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '");\' href="admin.php?page=wpclient_clients&tab=staff_approve&action=delete&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_staff_approve_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<span id="staff_username_' . $item['id'] . '">' . $item['username'] . '</span>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( sprintf( __( 'Search %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ), 'search-submit' );
        }
    }
}


$ListTable = new WPC_Staff_Approve_List_Table( array(
    'singular' => WPC()->custom_titles['staff']['s'],
    'plural'   => WPC()->custom_titles['staff']['p'],
    'ajax'     => false
) );

$per_page = WPC()->admin()->get_list_table_per_page( 'wpc_approve_staffs_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_bulk_actions(array(
    'approve'   => __( 'Approve', WPC_CLIENT_TEXT_DOMAIN ),
    'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$add_column_custom_fields = $add_sort_columns = array();
$custom_fields = WPC()->custom_fields()->get_custom_fields_for_users('admin_screen','staff');

if ( ! WPC()->flags['easy_mode'] ) {
    foreach ( $custom_fields as $key_cf => $val_cf ) {
        $custom_fields[ $key_cf ]['name']    = $key_cf;
        $add_column_custom_fields[ $key_cf ] = ( isset( $val_cf['title'] ) && '' != $val_cf['title'] ) ? $val_cf['title'] : __( 'Not Title', WPC_CLIENT_TEXT_DOMAIN );
        $add_sort_columns[ $key_cf ]         = preg_replace( '/^wpc_cf_/', '', $key_cf );
    }
}
$default_columns = array(
    'username' => 'username',
    'first_name'		 => 'first_name',
    'email'		 => 'email',
    'client'	 => 'client'
);
$sortable_columns = array_merge( $default_columns, $add_sort_columns );

/*our_hook_
hook_name: wpc_client_sortable_columns_of_staff_approve
hook_title: Add more columns for sortable on Staff Approve page
hook_description: Hook runs before set columns for sortable on Staff Approve page.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $sortable_columns
hook_since: 4.3.1
*/
$sortable_columns = apply_filters( 'wpc_client_sortable_columns_of_staff_approve', $sortable_columns );

$ListTable->set_sortable_columns( $sortable_columns);

$set_columns = array(
    'cb'       => '<input type="checkbox" />',
    'username' => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'first_name'     => __( 'First name', WPC_CLIENT_TEXT_DOMAIN ),
    'email'    => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
    'client'   => sprintf( __( 'Assigned to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
);

$set_columns = array_merge( $set_columns, $add_column_custom_fields );
/*our_hook_
hook_name: wpc_client_columns_of_staff_approve
hook_title: Add more columns on Staff Approve page
hook_description: Hook runs before set columns on Staff Approve page.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $columns
hook_since: 4.3.1
*/
$set_columns = apply_filters( 'wpc_client_columns_of_staff_approve', $set_columns );

$ListTable->custom_fields = $custom_fields;
$ListTable->set_columns( $set_columns );


$manager_clients = '';
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $clients_ids = WPC()->members()->get_all_clients_manager();
    $manager_clients = " AND um3.meta_value IN ('" . implode( "','", $clients_ids ) . "')";
}


$sql = "SELECT count( u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id
    LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'parent_client_id'
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%s:16:\"wpc_client_staff\";%'
        AND um2.meta_key = 'first_name'
        {$not_approved}
        {$where_clause}
        {$manager_clients}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT u.ID as id, u.user_login as username, u.user_email as email, um2.meta_value as first_name, um3.meta_value AS parent_client_id
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id
    LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'parent_client_id'
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%s:16:\"wpc_client_staff\";%'
        AND um2.meta_key = 'first_name'
        {$not_approved}
        {$where_clause}
        {$manager_clients}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";

$staff = $wpdb->get_results( $sql, ARRAY_A );

//add all custom fields
$user_ids = array_map( function ( $user ) {
    return $user['id'];
}, $staff );

$users_custom_fields = $wpdb->get_results( "SELECT user_id as id, meta_key as k, meta_value as val FROM {$wpdb->usermeta} WHERE user_id IN ('" . implode( "','", $user_ids ) . "') AND meta_key IN ('" . implode( "','", array_keys( $custom_fields ) ) . "')", ARRAY_A );

$new_array_cf = array();
foreach ( $users_custom_fields as $cf ) {
    $new_array_cf[ $cf['id'] ][ $cf['k'] ] = $cf['val'];
}

$staff = array_map( function ( $user ) use ( $new_array_cf ) {
    return isset( $new_array_cf[ $user['id'] ] ) ? array_merge( $user, $new_array_cf[ $user['id'] ] ) : $user;
}, $staff );

$ListTable->prepare_items();
$ListTable->items = $staff;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php if ( isset( $_GET['msg'] ) ) {
        $msg = $_GET['msg'];
        switch($msg) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' .  sprintf( __( '%s is approved.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '</p></div>';
                break;
        }
    } ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block staff_approve">

           <form action="" method="get" name="wpc_clients_form" id="wpc_staff_approve_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="staff_approve" />
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