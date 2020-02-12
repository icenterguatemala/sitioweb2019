<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
    $redirect = remove_query_arg( array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients';
}


WPC()->custom_fields()->add_custom_datepicker_scripts();

$where_circle          = '';

//filter group
if ( isset( $_GET['circle'] ) && is_numeric( $_GET['circle'] ) && 0 < $_GET['circle'] ) {
    $where_circle = " AND u.ID IN (SELECT d.client_id "
            . "FROM {$wpdb->prefix}wpc_client_group_clients d "
            . "WHERE d.group_id = " . esc_sql( $_GET['circle'] ) . " )";
}

$where_clause = '';
if ( ! empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'u.display_name',
        'u.user_email',
        array(
            'table'         => 'um4',
            'meta_key'      => 'wpc_cl_business_name',
            'meta_value'    => 'um4.meta_value'
        ),
        array(
            'table'         => 'um4',
            'meta_key'      => 'wpc_cf_%',
            'meta_value'    => 'um4.meta_value'
        )
    ) );
}

$order_by = 'u.user_registered';
$left_join_for_order_cf = '';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'user_login' :
            $order_by = 'user_login';
            break;
        case 'display_name' :
            $order_by = 'display_name';
            break;
        case 'business_name' :
            $order_by = 'um.meta_value';
            break;
        case 'user_email' :
            $order_by = 'user_email';
            break;
        default:

            /*our_hook_
            hook_name: wpc_client_order_by_of_clients
            hook_title: Change default value of columns on Clients page
            hook_description: Hook runs before echo default value of columns on Clients page.
            hook_type: filter
            hook_in: wp-client
            hook_location clients.php
            hook_param: string $order_by, string $_GET['orderby']
            hook_since: 4.3.1
            */
            $order_by = apply_filters( 'wpc_client_order_by_of_clients', null, $_GET['orderby'] );

            if ( is_null( $order_by ) ) {
                $left_join_for_order_cf = $wpdb->prepare( " LEFT JOIN {$wpdb->usermeta} um0 "
                    . "ON ( u.ID = um0.user_id AND um0.meta_key = %s ) ", 'wpc_cf_' . $_GET['orderby'] );
                $order_by = 'um0.meta_value';
            }
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

$add_column_custom_fields = $add_sort_columns = array();
$custom_fields = WPC()->custom_fields()->get_custom_fields_for_users();

if ( ! WPC()->flags['easy_mode'] ) {
    foreach ( $custom_fields as $key_cf => $val_cf ) {
        $custom_fields[ $key_cf ]['name'] = $key_cf;
        $add_column_custom_fields[ $key_cf ] = ( isset( $val_cf['title'] ) && '' != $val_cf['title'] ) ? $val_cf['title'] : __( 'Not Title', WPC_CLIENT_TEXT_DOMAIN ) ;
        $add_sort_columns[ $key_cf ] = preg_replace( '/^wpc_cf_/', '', $key_cf ) ;
    }
}

//for manager
$mananger_clients = '';
if ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) {
    $clients_ids = WPC()->members()->get_all_clients_manager();
    $mananger_clients = " AND u.ID IN ('" . implode( "','", $clients_ids ) . "')";
}

$excluded_clients = " AND u.ID NOT IN ('" . implode( "','", WPC()->members()->get_excluded_clients() ) . "')";

if ( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WPC_Clients_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $bulk_actions = array();
    var $columns = array();
    var $custom_fields = array();

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
            hook_name: wpc_client_{$column_name}_custom_column_of_clients
            hook_title: Change default value of columns on Clients page
            hook_description: Hook runs before echo default value of columns on Clients page.
            hook_type: filter
            hook_in: wp-client
            hook_location clients.php
            hook_param: mixed $value
            hook_since: 4.3.1
            */
            return apply_filters( 'wpc_client_' . $column_name . '_custom_column_of_clients', $value );
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

    function column_managers( $item ) {
        $current_manager_ids = WPC()->members()->get_client_managers( $item['id'], 'individual' );
        //WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $item['id'] );
        $count = ( $current_manager_ids ) ? count( $current_manager_ids ) : 0;

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] )
        );
        $input_array = array(
            'name'  => 'wpc_managers_ajax[]',
            'id'    => 'wpc_managers_' . $item['id'],
            'value' => implode( ',', $current_manager_ids )
        );
        $additional_array = array(
            'counter_value' => $count
        );

        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $html = WPC()->assigns()->assign_popup('manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_circles( $item ) {
        $client_groups = WPC()->groups()->get_client_groups_id( $item['id'] );
        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $all_manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            $client_groups = array_intersect( $client_groups, $all_manager_groups );
        }

        $count = ( $client_groups ) ? count( $client_groups ) : 0;

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ) . ' ' . $item['username']
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['id'],
            'value' => implode( ',', $client_groups )
        );
        $additional_array = array(
            'counter_value' => $count
        );

        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $circle_popup_html = WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $circle_popup_html;
    }

    function column_creation_date( $item ) {
        return WPC()->date_format( strtotime( $item['creation_date'] ) );
    }

    function column_username( $item ) {
        $actions = $hide_actions = array();

        if ( current_user_can( 'wpc_edit_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=edit_client&id=' . $item['id'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        if ( current_user_can( 'wpc_view_client_details' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $actions['view'] = '<a href="javascript:void(0);" rel="' . $item['id'] . '_' . md5( 'wpcclientview_' . $item['id'] ) . '" class="various" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        $hide_actions['wpc_files'] = '<a target="_blank" href="admin.php?page=wpclients_content&tab=files&change_filter=client&filter_client=' . $item['id'] . '">' . __( 'Files', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        $hide_actions['wpc_messages'] = '<a target="_blank" href="admin.php?page=wpclients_content&tab=private_messages&filter_client=' . $item['id'] . '">' . __( 'Messages', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        $hide_actions['wpc_send_message'] = '<a target="_blank" href="admin.php?page=wpclients_content&tab=private_messages&send_message=' . $item['id'] . '">' . __( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_view_client_internal_notes' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $hide_actions['wpc_internal_notes'] = '<a href="javascript:void(0);" rel="' . $item['id'] . '_' . md5( 'wpcclientinternalnote_' . $item['id'] ) . '" class="various_notes" >' . __( 'Internal Notes', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        if ( !get_user_meta( $item['id'], 'wpc_temporary_password', true ) ) {
            $hide_actions['wpc_temp_password'] = '<a onclick=\'return confirm("' . sprintf( __( 'Do you want to mark the password as temporary for this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\' '
                    . 'href="admin.php?page=wpclient_clients&action=temp_password&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'client_temp_password' . $item['id'] . get_current_user_id() ) .'">' . __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email']
                && get_user_meta( $item['id'], 'verify_email_key', true ) ) {
            $hide_actions['wpc_verify_email'] = '<a onclick=\'return confirm("' . sprintf( __( 'Do you want approve email of this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\' href="admin.php?page=wpclient_clients&action=verify_email&user_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'client_email_verify' . $item['id'] . get_current_user_id() ) .'">' . __( 'Verify Email', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        if ( current_user_can( 'wpc_admin_user_login' ) ) {
            $schema = is_ssl() ? 'https://' : 'http://';
            $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $hide_actions['wpc_login_admin_as_client'] = '<a href="admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . get_current_user_id() . $item['id'] ) . '&id=' . $item['id'] . '&referer_url=' . urlencode( $current_url ) . '">' . sprintf( __( 'Login to %s account', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</a>';
        }

        if ( ! WPC()->flags['easy_mode'] ) {
            if ( current_user_can( 'wpc_edit_cap_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                $hide_actions['wpc_capability'] = '<a href="javascript:void(0);" data-id="' . $item['id'] . '_' . md5( 'wpc_client' . SECURE_AUTH_SALT . $item['id'] ) . '" class="various_capabilities">' . __( 'Individual Capabilities', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
        }

        if ( !isset( $item['time_resend'] ) || ( $item['time_resend'] + 3600*23 ) < time() ) {
            $hide_actions['wpc_resend_welcome'] = '<a onclick=\'return confirm("' . __( 'Are you sure you want to Re-Send Welcome Email?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclient_clients&action=send_welcome&user_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_re_send_welcome' . $item['id'] . get_current_user_id() ) .'">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        } else {
            $hide_actions['wpc_resend_welcome'] = '<span title="' . sprintf( __( 'Wait around %s hours for re-send it.', WPC_CLIENT_TEXT_DOMAIN ), round( ( ( $item['time_resend'] + 3600*24 ) -  time() ) / 3600 ) ) . '">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
        }

        if ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $hide_actions['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to move this %s to the Archive?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\' href="admin.php?page=wpclient_clients&action=archive&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_client_archive' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
        }

        /*our_hook_
        hook_name: wpc_client_more_actions_clients
        hook_title: Add more actions on Clients page
        hook_description: Hook runs before display more actions on Clients page.
        hook_type: filter
        hook_in: wp-client
        hook_location clients.php
        hook_param: array $actions, array $client_data
        hook_since: 3.7.6.1
        */
        $hide_actions = apply_filters( 'wpc_client_more_actions_clients', $hide_actions, $item );

        if( count( $hide_actions ) ) {
            $actions['wpc_actions'] = WPC()->admin()->more_actions( $item['id'], __( 'Actions', WPC_CLIENT_TEXT_DOMAIN ), $hide_actions );
        }

        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] ) {
            $not_verify = get_user_meta( $item['id'], 'verify_email_key', true );
            $email_class = ( $not_verify ) ? 'not_verify_email' : 'verify_email';
        } else {
            $email_class = '';
        }

        return sprintf('%1$s %2$s', '<span id="client_username_' . $item['id'] . '">' . $item['username'] . ' (#' . $item['id'] . ') <span class="wpc_mobile_contact_name"> | ' . $item['contact_name'] . ' | <span class="' . $email_class . '">' . $item['email'] . '</span></span></span>', $this->row_actions( $actions ) );
    }

    function column_email( $item ) {
        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] ) {
            $not_verify = get_user_meta( $item['id'], 'verify_email_key', true );
            $class = ( $not_verify ) ? 'not_verify_email' : 'verify_email';
        } else {
            $class = '';
        }
        return '<span class="' . $class . '">' . $item['email'] . '</span>';
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb;

            $all_groups            = array();
            $all_circles_groups    = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups", "ARRAY_A" );

            //change structure of array for display circle name in row in table and selectbox
            foreach ( $all_circles_groups as $value ) {
                $all_groups[ $value['group_id'] ] = $value['group_name'];
            }

            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                $temp_groups = array();
                foreach ( $manager_groups as $manager_group ) {
                    if ( isset( $all_groups[$manager_group] ) ) {
                        $temp_groups[$manager_group] = $all_groups[$manager_group];
                    }
                }
                $all_groups = $temp_groups ;
                asort( $all_groups );
            } ?>

            <div class="alignleft actions">
                <select name="circle" id="circle">
                    <option value="-1" <?php if( !isset( $_GET['circle'] ) || !in_array( $_GET['circle'], $all_groups ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></option>
                    <?php foreach ( $all_groups as $circle_id => $circle_name ) { ?>
                         <option value="<?php echo $circle_id ?>" <?php selected( isset( $_GET['circle'] ) && $circle_id == $_GET['circle'] ) ?>><?php echo $circle_name ?></option>
                    <?php } ?>
                </select>
                <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="button_filter_circle" name="" />
                <a class="add-new-h2" id="cancel_filter" <?php if ( !isset( $_GET['circle'] ) || -1 == $_GET['circle'] ) echo 'style="display: none;"'; ?> >
                    <?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <span style="color: #bc0b0b;">&times;</span>
                </a>
            </div>

            <?php $this->search_box( sprintf( __( 'Search %s' , WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }


}

$ListTable = new WPC_Clients_List_Table( array(
    'singular'  => WPC()->custom_titles['client']['s'],
    'plural'    => WPC()->custom_titles['client']['p'],
    'ajax'      => false
));


switch ( $ListTable->current_action() ) {
    /* archive action */
    case 'archive':

        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_archive' .  $_REQUEST['id'] . get_current_user_id() );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
            foreach ( $clients_id as $client_id ) {
                //move to archive
                WPC()->members()->archive_client( $client_id );
            }
            WPC()->redirect( add_query_arg( 'msg', 't', $redirect ) );
        }
        WPC()->redirect( $redirect );

        break;
    case 'send_welcome':

        $clients_id = array();
        if ( isset( $_REQUEST['user_id'] ) ) {
            check_admin_referer( 'wpc_re_send_welcome' .  $_REQUEST['user_id'] . get_current_user_id() );
            $clients_id = ( is_array( $_REQUEST['user_id'] ) ) ? $_REQUEST['user_id'] : (array) $_REQUEST['user_id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        set_time_limit(0);
        if ( count( $clients_id ) ) {
            foreach ( $clients_id as $client_id ) {
                //re send welcome
                WPC()->members()->resend_welcome_email( $client_id );
            }
            WPC()->redirect( add_query_arg( 'msg', 'wel', $redirect ) );
        }
        WPC()->redirect( $redirect );
        break;
    case 'verify_email':
        $user_id = isset( $_GET['user_id'] ) ? (int)$_GET['user_id'] : 0;
        if ( 0 < $user_id ) {
            check_admin_referer( 'client_email_verify' . $user_id . get_current_user_id() );
            delete_user_meta( $user_id, 'verify_email_key' );
            WPC()->redirect( add_query_arg( 'msg', 'ver', $redirect ) );
        } else {
            WPC()->redirect( $redirect );
        }
        break;

    case 'temp_password':
        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'client_temp_password' .  $_REQUEST['id'] . get_current_user_id() );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array)$_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        foreach ( $clients_id as $client_id ) {
            WPC()->members()->set_temp_password( $client_id );
        }

        if( 1 < count( $clients_id ) ) {
            WPC()->redirect( add_query_arg( 'msg', 'pass_s', $redirect ) );
        } else if( 1 === count( $clients_id ) ) {
            WPC()->redirect( add_query_arg( 'msg', 'pass', $redirect ) );
        } else {
            WPC()->redirect( $redirect );
        }

    case 'delete_permanently':

        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_archive' .  $_REQUEST['id'] . get_current_user_id() );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
            /*our_hook_
                hook_name: wpc_client_clients_deleted
                hook_title: Client Deleted
                hook_description: Hook runs when Client account is deleted.
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $client_id, array $userdata
                hook_since: 3.4.1
            */
            do_action( 'wpc_client_before_clients_deleted', $clients_id );
            foreach ( $clients_id as $client_id ) {
                //delete client
                if( is_multisite() ) {
                    wpmu_delete_user( $client_id );
                } else {
                    wp_delete_user( $client_id );
                }
            }

            if( 1 == count( $clients_id ) )
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            else
                WPC()->redirect( add_query_arg( 'msg', 'ds', $redirect ) );
        }
        WPC()->redirect( $redirect );

        break;

    default:

        //remove extra query arg
        if ( !empty( $_GET['_wp_http_referer'] ) ) {
            WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
        }


        break;
}

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_clients_per_page' );
$paged      = $ListTable->get_pagenum();

$default_columns = array(
    'username'          => 'user_login',
    'contact_name'      => 'display_name',
    'business_name'     => 'business_name',
    'email'             => 'user_email',
    'creation_date'     => 'user_registered',
);

$sortable_columns = array_merge( $default_columns, $add_sort_columns );

/*our_hook_
hook_name: wpc_client_sortable_columns_of_clients
hook_title: Add more columns for sortable on Clients page
hook_description: Hook runs before set columns for sortable on Clients page.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $sortable_columns
hook_since: 4.3.1
*/
$sortable_columns = apply_filters( 'wpc_client_sortable_columns_of_clients', $sortable_columns );

$ListTable->set_sortable_columns( $sortable_columns );

$bulk_actions = array(
    'temp_password' => __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ),
    'send_welcome'  => __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN )
);

$add_actions = array();
if ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
    $add_actions['archive'] = __( 'Move to Archive', WPC_CLIENT_TEXT_DOMAIN );
}

if ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
    $add_actions['delete_permanently'] = __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN );
}

$ListTable->set_bulk_actions( array_merge( $bulk_actions, $add_actions ) );

$set_columns = array(
    'cb'                => '<input type="checkbox" />',
    'username'          => __( 'Username (#ID)', WPC_CLIENT_TEXT_DOMAIN ),
    'contact_name'      => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
    'business_name'     => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
    'email'             => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
    'creation_date'     => __( 'Creation Date', WPC_CLIENT_TEXT_DOMAIN ),
    'circles'           => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
);//if change column name - change for add_columns_for_screen_options_for_client function too

if( !WPC()->flags['easy_mode'] && ( !current_user_can( 'wpc_manager' ) || current_user_can('administrator') ) ) {
    $set_columns['managers'] = WPC()->custom_titles['manager']['p'];
}

$set_columns = array_merge( $set_columns, $add_column_custom_fields );

/*our_hook_
hook_name: wpc_client_columns_of_clients
hook_title: Add more columns on Clients page
hook_description: Hook runs before set columns on Clients page.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $columns
hook_since: 4.3.1
*/
$set_columns = apply_filters( 'wpc_client_columns_of_clients', $set_columns );

$ListTable->custom_fields = $custom_fields;
$ListTable->set_columns( $set_columns );

$sql = "SELECT COUNT( DISTINCT u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id
    LEFT JOIN {$wpdb->usermeta} um4 ON u.ID = um4.user_id
    WHERE um2.meta_key = '{$wpdb->prefix}capabilities' AND 
        um2.meta_value LIKE '%s:10:\"wpc_client\";%'
        {$mananger_clients}
        {$excluded_clients}
        {$where_clause}
        {$where_circle}
    ";

$items_count = $wpdb->get_var( $sql );

$vars = array(
    'select' => 'DISTINCT u.ID as id, u.user_login as username, u.user_registered as creation_date, u.display_name as contact_name, u.user_email as email, um.meta_value as business_name, um3.meta_value as time_resend',
    'left_joins' => $left_join_for_order_cf,
);
/*our_hook_
hook_name: wpc_client_clients_table_query_args
hook_title: Query arguments for list table on Clients page
hook_description: Hook runs before get clients on Client list table.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $vars
hook_since: 4.3.1
*/
$vars = apply_filters( 'wpc_client_clients_table_query_args', $vars );

$wpdb->query( 'SET SQL_BIG_SELECTS = 1;' );

$sql = "SELECT {$vars['select']}
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = 'wpc_cl_business_name' )
    LEFT JOIN {$wpdb->usermeta} um2 ON ( u.ID = um2.user_id AND um2.meta_key = '{$wpdb->prefix}capabilities' )
    LEFT JOIN {$wpdb->usermeta} um3 ON ( u.ID = um3.user_id AND um3.meta_key = 'wpc_send_welcome_email' )
    LEFT JOIN {$wpdb->usermeta} um4 ON u.ID = um4.user_id
    {$vars['left_joins']}
    WHERE um2.meta_value LIKE '%s:10:\"wpc_client\";%'
        {$mananger_clients}
        {$excluded_clients}
        {$where_clause}
        {$where_circle}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";

$users = $wpdb->get_results( $sql, ARRAY_A );

//add all custom fields
$user_ids = array_map( function( $user ) {
    return $user['id'];
}, $users);

$users_custom_fields = $wpdb->get_results("SELECT user_id as id, meta_key as k, meta_value as val FROM {$wpdb->usermeta} WHERE user_id IN ('" . implode( "','", $user_ids ) . "') AND meta_key IN ('" . implode( "','", array_keys( $custom_fields ) ) . "')", ARRAY_A );

$new_array_cf = array();
foreach( $users_custom_fields as $cf ) {
    $new_array_cf[ $cf['id'] ][ $cf['k'] ] = $cf['val'] ;
}

$users = array_map( function( $user ) use ( $new_array_cf ) {
    return isset( $new_array_cf[ $user['id'] ] ) ? array_merge( $user, $new_array_cf[ $user['id'] ] ) : $user;
}, $users);
//end added all custom fields

$ListTable->prepare_items();
$ListTable->items = $users;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );

$code = md5( 'wpc_client_' . get_current_user_id() . '_send_mess' ); ?>

<script type="text/javascript">
    jQuery(document).ready( function() {

        var user_id = 0;
        var nonce = '';

        jQuery('#wpc_clients_list_form').submit(function() {
            if( jQuery('select[name="action"]').val() == 'delete_permanently' || jQuery('select[name="action2"]').val() == 'delete_permanently' ) {
                user_id = new Array();
                jQuery("input[name^=item]:checked").each(function() {
                    user_id.push( jQuery(this).val() );
                });
                nonce = jQuery('input[name=_wpnonce]').val();

                if( user_id.length ) {
                    jQuery('#wpc_clients_list_form').shutter_box({
                        view_type       : 'lightbox',
                        width           : '500px',
                        type            : 'ajax',
                        dataType        : 'json',
                        href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                        ajax_data       : "action=wpc_get_user_list&exclude=" + user_id.join(','),
                        setAjaxResponse : function( data ) {
                            jQuery( '.sb_lightbox_content_title' ).html( data.title );
                            jQuery( '.sb_lightbox_content_body' ).html( data.content );
                        },
                        self_init       : false
                    });

                    jQuery('#wpc_clients_list_form').shutter_box('show');
                }

                bulk_action_runned = true;
                return false;
            }
        });

        jQuery(document).on('click', '.cancel_delete_button', function() {
            jQuery('#wpc_clients_list_form').shutter_box('close');
            user_id = 0;
            nonce = '';
        });

        jQuery(document).on('click', '.delete_user_button', function() {
            if( user_id instanceof Array ) {
                if( user_id.length ) {
                    var item_string = '';
                    user_id.forEach(function( item, key ) {
                        item_string += '&item[]=' + item;
                    });
                    window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&action=delete_permanently' + item_string + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name=_wp_http_referer]').val() );
                }
            } else {
                window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&action=delete_permanently&id=' + user_id + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=<?php echo urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ); ?>';
            }
            jQuery('#wpc_clients_list_form').shutter_box('close');
            user_id = 0;
            nonce = '';
            return false;
        });


        //remove extra fields before submit form
        jQuery( '#wpc_clients_list_form' ).submit( function() {
            jQuery( '.change_circles' ).remove();
            return true;
        });


        //filter group
        jQuery( '#button_filter_circle' ).click( function() {
            if ( '-1' != jQuery( '#circle' ).val() ) {
                var req_uri = "<?php echo preg_replace( '/&circle=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                window.location = req_uri + '&circle=' + jQuery( '#circle' ).val();
            }
            return false;
        });


        jQuery( '#cancel_filter' ).click( function() {
            var req_uri = "<?php echo preg_replace( '/&circle=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
            window.location = req_uri;
            return false;
        });


        <?php if ( current_user_can( 'wpc_view_client_details' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) { ?>
            //open view client
            jQuery('.various').each( function() {
                var id = jQuery( this ).attr( 'rel' );

                jQuery(this).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'ajax',
                    dataType        : 'json',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data       : "action=wpc_view_client&id=" + id,
                    setAjaxResponse : function( data ) {
                        jQuery( '.sb_lightbox_content_title' ).html( data.title );
                        jQuery( '.sb_lightbox_content_body' ).html( data.content );
                    }
                });
            });
        <?php } ?>


        <?php if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_view_client_internal_notes' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) { ?>
            //open view Internal Notes
            jQuery('.various_notes').each( function() {
                var id = jQuery( this ).attr( 'rel' );

                jQuery(this).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    height          : '310px',
                    type            : 'ajax',
                    dataType        : 'json',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data       : "action=wpc_get_client_internal_notes&id=" + id,
                    setAjaxResponse : function( data ) {
                        jQuery( '.sb_lightbox_content_title' ).html( data.title );
                        jQuery( '.sb_lightbox_content_body' ).html( data.content );
                    }
                });
            });
        <?php } ?>


        <?php if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) { ?>
            // AJAX - Update Internal Notes
            jQuery('body').on('click', '#update_internal_notes', function() {
                var id              = jQuery( '#wpc_client_id' ).val();
                var content         = jQuery( '#wpc_internal_notes' ).val();
                var crypt_content   = jQuery.base64Encode( content );
                crypt_content       = crypt_content.replace( /\+/g, "-" );

                var notice = jQuery( '.wpc_ajax_result' );

                notice.html( '<div class="wpc_ajax_loading"></div>' ).show();
                jQuery( 'body' ).css( 'cursor', 'wait' );
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_update_client_internal_notes&id=' + id + '&notes=' + crypt_content,
                    dataType: "json",
                    success: function( data ){
                        jQuery( 'body' ).css( 'cursor', 'default' );

                        if( data.status ) {
                            notice.css( 'color', 'green' );
                        } else {
                            notice.css( 'color', 'red' );
                        }

                        notice.html( data.message );
                        setTimeout( function() {
                            notice.fadeOut(1500);
                        }, 2500 );
                    },
                    error: function( data ) {
                        jQuery( 'body' ).css( 'cursor', 'default' );

                        notice.css( 'color', 'red' ).html( '<?php echo esc_js( __( 'Unknown error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' );
                        setTimeout( function() {
                            notice.fadeOut( 1500 );
                        }, 2500 );
                    }
                });

            });
        <?php } ?>


        //display client capabilities
        jQuery('.various_capabilities').each( function() {
            var id = jQuery( this ).data( 'id' );

            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '300px',
                type            : 'ajax',
                dataType        : 'json',
                href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                ajax_data       : "action=wpc_get_user_capabilities&id=" + id + "&wpc_role=wpc_client",
                setAjaxResponse : function( data ) {
                    jQuery( '.sb_lightbox_content_title' ).html( data.title );
                    jQuery( '.sb_lightbox_content_body' ).html( data.content );
                }
            });
        });


        // AJAX - Update Capabilities
        jQuery('body').on('click', '#update_wpc_capabilities', function () {
            var id = jQuery('#wpc_capability_id').val();
            var caps = {};

            jQuery('#wpc_all_capabilities input').each(function () {
                if ( jQuery(this).is(':checked') )
                    caps[jQuery(this).attr('name')] = jQuery(this).val();
                else
                    caps[jQuery(this).attr('name')] = '';
            });

            var notice = jQuery( '.wpc_ajax_result' );

            notice.html('<div class="wpc_ajax_loading"></div>').show();
            jQuery( 'body' ).css( 'cursor', 'wait' );
            jQuery.ajax({
                type: 'POST',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: 'action=wpc_update_capabilities&id=' + id + '&wpc_role=wpc_client&capabilities=' + JSON.stringify(caps),
                dataType: "json",
                success: function (data) {
                    jQuery('body').css('cursor', 'default');

                    if (data.status) {
                        notice.css('color', 'green');
                    } else {
                        notice.css('color', 'red');
                    }
                    notice.html(data.message);
                    setTimeout(function () {
                        notice.fadeOut(1500);
                    }, 2500);

                },
                error: function (data) {
                    notice.css('color', 'red').html('<?php echo esc_js( __( 'Unknown error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>');
                    setTimeout(function () {
                        notice.fadeOut(1500);
                    }, 2500);
                }
            });
        });

    });
</script>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'wel':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Re-Sent Email for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'ver':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Approved Email for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'pass':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The password marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'pass_s':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The passwords marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'ds':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 't':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Moved to the Archive</strong>.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'uf':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'id':
                echo '<div id="message" class="error wpc_notice fade"><p>' . sprintf( __( 'Wrong %s ID.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
        }
    }
    ?>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <div class="wpc_clear"></div>
        <div class="wpc_tab_container_block">
            <?php if ( current_user_can( 'wpc_add_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) { ?>
                <a class="add-new-h2 wpc_form_link" href="?page=wpclient_clients&tab=add_client"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            <?php } ?>
            <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) { ?>
                <a class="add-new-h2 wpc_form_link" href="<?php echo get_admin_url()?>admin.php?page=wpclients&tab=import-export" target="_blank"><?php _e( 'Import/Export', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            <?php } ?>

            <form action="" method="get" name="wpc_clients_list_form" id="wpc_clients_list_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclient_clients" />
                <?php $ListTable->display(); ?>
            </form>
        </div>
    </div>
</div>
