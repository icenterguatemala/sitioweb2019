<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

// No caps
if ( !( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
    WPC()->redirect( get_admin_url( 'index.php' ) );
}

global $wpdb;

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'u.user_email',
        'u.user_nicename',
    ) );
}

//filter group
$include_managers = array();
if ( isset( $_GET['change_filter'] ) ) {
    if ( 'client' ==  $_GET['change_filter'] && isset( $_GET['filter_client'] ) ) {
        $client = $_GET['filter_client'];
        if ( is_numeric( $client ) && 0 < $client ) {
            //$include_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client );
            $include_managers = WPC()->members()->get_client_managers( $client );
        }
    }

    if ( 'circle' ==  $_GET['change_filter'] && isset( $_GET['filter_circle'] ) ) {
        $circle = $_GET['filter_circle'];
        if ( is_numeric( $circle ) && 0 < $circle ) {
            $include_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'circle', $circle );
        }
    }
}
if ( count( $include_managers ) )
    $include_managers = " AND u.ID IN ('" . implode( "','", $include_managers ) . "')";
else $include_managers = '';

$order_by = 'u.user_registered';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'username' :
            $order_by = 'u.user_login';
            break;
        case 'nickname' :
            $order_by = 'u.user_nicename';
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

class WPC_Managers_List_Table extends WP_List_Table {

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

    function column_auto_add_clients( $item ) {
        if( '1' == $item['auto_add_clients'] )
            return __( 'Yes', WPC_CLIENT_TEXT_DOMAIN );
        else
            return __( 'No', WPC_CLIENT_TEXT_DOMAIN );
    }

    function column_clients( $item ) {
        $clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $item['id'], 'client' );
        $clients_ids = count( $clients );

        $link_array = array(
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . ' ' . $item['username'],
            'data-ajax' => true,
            'data-id' => $item['id'],
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['id'],
            'value' => implode( ',', $clients )
        );
        $additional_array = array(
            'counter_value' => $clients_ids
        );
        $html = WPC()->assigns()->assign_popup('client', 'wpclients_managers', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_circles( $item ) {
        $client_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', $item['id'], 'circle' );
        $count = count( $client_groups );

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
        $html = WPC()->assigns()->assign_popup('circle', 'wpclients_managers', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_username( $item ) {
        $actions = $hide_actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=managers_edit&id=' . $item['id'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        $hide_actions['wpc_capability'] = '<a href="#wpc_capability" data-id="' . $item['id'] . '_' . md5( 'wpc_manager' . SECURE_AUTH_SALT . $item['id'] ) . '" class="various_capabilities">' . __( 'Individual Capabilities', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        if ( !get_user_meta( $item['id'], 'wpc_temporary_password', true ) ) {
            $hide_actions['wpc_temp_password'] = '<a onclick=\'return confirm("' . sprintf( __( 'Do you want to mark the password as temporary for this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) . '");\' '
                    . 'href="admin.php?page=wpclient_clients&tab=managers&action=temp_password&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'manager_temp_password' . $item['id'] . get_current_user_id() ) .'">' . __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        if ( !isset( $item['time_resend'] ) || ( $item['time_resend'] + 3600*23 ) < time() ) {
            $hide_actions['wpc_resend_welcome'] = '<a onclick=\'return confirm("' . __( 'Are you sure you want to Re-Send Welcome Email?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclient_clients&tab=managers&action=send_welcome&user_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_re_send_welcome' . $item['id'] . get_current_user_id() ) .'">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        } else {
            $hide_actions['wpc_resend_welcome'] = '<span title="' . sprintf( __( 'Wait around %s hours for re-send it.', WPC_CLIENT_TEXT_DOMAIN ), round( ( ( $item['time_resend'] + 3600*24 ) -  time() ) / 3600 ) ) . '">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
        }

        $hide_actions['delete'] = '<a class="delete_action" data-nonce="' . wp_create_nonce( 'wpc_manager_delete' . $item['id'] . get_current_user_id() ) . '" data-id="' . $item['id'] . '" href="javascript: void(0);">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        /*our_hook_
        hook_name: wpc_client_more_actions_managers
        hook_title: Add more actions on Managers page
        hook_description: Hook runs before display more actions on Managers page.
        hook_type: filter
        hook_in: wp-client
        hook_location managers.php
        hook_param: string $error
        hook_since: 3.9.5
        */
        $hide_actions = apply_filters( 'wpc_client_more_actions_managers', $hide_actions );

        if( count( $hide_actions ) ) {
            $actions['wpc_actions'] = WPC()->admin()->more_actions( $item['id'], __( 'Actions', WPC_CLIENT_TEXT_DOMAIN ), $hide_actions );
        }
        return sprintf('%1$s %2$s', '<span id="client_username_' . $item['id'] . '">' . $item['username'] . '</span>', $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb;
            $all_filter = array( WPC()->custom_titles['client']['s'] => 'client', WPC()->custom_titles['circle']['s'] => 'circle',  );

            $all_groups            = array();
            $all_circles_groups    = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups", "ARRAY_A" );

            //change structure of array for display circle name in row in table and selectbox
            foreach ( $all_circles_groups as $value ) {
                $all_groups[ $value['group_id'] ] = $value['group_name'];
            } ?>

            <div class="alignleft actions">
                <select name="change_filter" id="change_filter" style="float: left;">
                    <option value="-1" <?php if( !isset( $_GET['change_filter'] ) || !in_array( $_GET['change_filter'], $all_filter ) ) echo 'selected'; ?>><?php _e( 'Select Filter', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php
                        foreach ( $all_filter as $key => $type_filter ) {
                         $selected = ( isset( $_GET['change_filter'] ) && $type_filter == $_GET['change_filter'] ) ? ' selected' : '' ;
                         echo '<option value="' . $type_filter . '"' . $selected . ' >';
                         echo $key;
                         echo '</option>';
                        }
                     ?>
                </select>
                <select name="select_filter" id="select_filter" style="float: left; <?php if ( !isset( $_GET['change_filter'] ) || !in_array( $_GET['change_filter'], $all_filter ) ) echo 'display: none;'; ?>">
                    <?php
                        if ( isset( $_GET['change_filter'] ) ) {
                            if ( 'client' == $_GET['change_filter'] && isset( $_GET['filter_client'] ) ) {
                                $unique_clients = WPC()->assigns()->get_assign_data_by_object_assign( 'manager', 'client' ); ?>

                                <option value="-1" <?php if ( !in_array( $_GET['filter_client'], $unique_clients ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>

                                <?php if ( is_array( $unique_clients ) && 0 < count( $unique_clients ) )
                                foreach( $unique_clients as $client_id ) {
                                    if ( '' != $client_id ) {
                                        $selected = ( $client_id == $_GET['filter_client'] ) ? 'selected' : '';
                                        echo '<option value="' . $client_id . '" ' . $selected . ' >' . get_userdata( $client_id )->user_login . '</option>';
                                    }
                                }
                            }
                            elseif ( 'circle' == $_GET['change_filter'] && isset( $_GET['filter_circle'] ) ) {
                                $unique_circles = WPC()->assigns()->get_assign_data_by_object_assign( 'manager', 'circle' );
                                $all_circles_groups = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups", "ARRAY_A" );
                                foreach ( $all_circles_groups as $value ) {
                                    $all_groups[ $value['group_id'] ] = $value['group_name'];
                                } ?>

                                <option value="-1" <?php if ( !in_array( $_GET['filter_circle'], $unique_circles ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></option>

                                <?php foreach( $unique_circles as $circle_id ) {
                                    $selected = ( $circle_id == $_GET['filter_circle'] ) ? 'selected' : '';
                                    echo '<option value="' . $circle_id . '" ' . $selected . ' >' . $all_groups[ $circle_id ] . '</option>';
                                }
                            }
                        }
                    ?>
                </select>
                <span id="load_select_filter" style="float: left;"></span>
                <input type="button" style="float: left;" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="filtered" name="" />
                <a class="add-new-h2 cancel_filter" id="cancel_filter" style="<?php if( !isset( $_GET['filter_author']) && !isset( $_GET['filter_client']) && !isset($_GET['filter_circle']) ) echo 'display: none;'; ?>">
                    <?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <span style="color: #bc0b0b;">&times;</span>
                </a>
            </div>

            <?php $this->search_box( sprintf( __( 'Search %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}

$ListTable = new WPC_Managers_List_Table( array(
    'singular'  => WPC()->custom_titles['manager']['s'],
    'plural'    => WPC()->custom_titles['manager']['p'],
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_managers_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'username'          => 'username',
    'nickname'          => 'nickname',
    'email'             => 'email',
) );

$ListTable->set_bulk_actions(array(
    'temp_password' => __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ),
    'send_welcome'  => __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ),
    'delete'        => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'cb'                => '<input type="checkbox" />',
    'username'          => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'nickname'          => __( 'Nickname', WPC_CLIENT_TEXT_DOMAIN ),
    'email'             => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
    'auto_add_clients'  => sprintf( __( 'Auto-Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    'clients'           => WPC()->custom_titles['client']['p'],
    'circles'           => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
));


/*
not delete

$args = array(
    'role'       => 'wpc_manager',
    'include'    => $include_managers,
    'orderby'    => $order_by,
    'order'      => $order,
    'offset'     => ( $paged - 1 ) * $per_page,
    'number'     => $per_page,
    'count_total'=> true,
);
$user_search = new WP_User_Query($args);
$managers = (array) $user_search->get_results();
var_export($user_search->total_users);exit;
*/


$sql = "SELECT count( u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%\"wpc_manager\"%'
        {$where_clause}
        {$include_managers}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT u.ID as id, u.user_login as username, u.user_nicename as nickname, u.user_email as email, um2.meta_value as auto_add_clients, um3.meta_value as time_resend
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'wpc_auto_assigned_clients'
    LEFT JOIN {$wpdb->usermeta} um3 ON ( u.ID = um3.user_id AND um3.meta_key = 'wpc_send_welcome_email' )
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%\"wpc_manager\"%'
        {$where_clause}
        {$include_managers}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";


    $managers = $wpdb->get_results( $sql, ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $managers;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=managers';
}

switch ( $ListTable->current_action() ) {
    /* delete action */
    case 'delete':
        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_manager_delete' .  $_REQUEST['id'] . get_current_user_id() );
            $clients_id = (array) $_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['manager']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
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

    case 'temp_password':
        $managers_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'manager_temp_password' .  $_REQUEST['id'] . get_current_user_id() );
            $managers_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['manager']['p'] ) );
            $managers_id = $_REQUEST['item'];
        }

        foreach ( $managers_id as $manager_id ) {
            WPC()->members()->set_temp_password( $manager_id );
        }

        if( 1 < count( $managers_id ) ) {
            WPC()->redirect( add_query_arg( 'msg', 'pass_s', $redirect ) );
        } else if( 1 === count( $managers_id ) ) {
            WPC()->redirect( add_query_arg( 'msg', 'pass', $redirect ) );
        } else {
            WPC()->redirect( $redirect );
        }

    case 'send_welcome':

        $managers_id = array();
        if ( isset( $_REQUEST['user_id'] ) ) {
            check_admin_referer( 'wpc_re_send_welcome' .  $_REQUEST['user_id'] . get_current_user_id() );
            $managers_id = ( is_array( $_REQUEST['user_id'] ) ) ? $_REQUEST['user_id'] : (array) $_REQUEST['user_id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['manager']['p'] ) );
            $managers_id = $_REQUEST['item'];
        }

        if ( count( $managers_id ) ) {
            foreach ( $managers_id as $manager_id ) {
                //re send welcome
                WPC()->members()->resend_welcome_email( $manager_id );
            }
            WPC()->redirect( add_query_arg( 'msg', 'wel', $redirect ) );
            exit;
        }
        WPC()->redirect( $redirect );

        break;
    default:

        //remove extra query arg
        if ( !empty( $_GET['_wp_http_referer'] ) ) {
            WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
        }


    break;
} ?>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>
        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">
            <?php if ( isset( $_GET['msg'] ) ) {
                $msg = $_GET['msg'];
                switch( $msg ) {
                    case 'a':
                        echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) . '</p></div>';
                        break;
                    case 'u':
                        echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) . '</p></div>';
                        break;
                    case 'd':
                        echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) . '</p></div>';
                        break;
                    case 'wel':
                        echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Re-Sent Email for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) . '</p></div>';
                        break;
                    case 'pass':
                        echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The password marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) . '</p></div>';
                        break;
                    case 'pass_s':
                        echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The passwords marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ) . '</p></div>';
                        break;
                        }
            } ?>

            <a class="add-new-h2" href="admin.php?page=wpclient_clients&tab=managers_add"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>

            <form action="" method="get" name="wpc_clients_form" id="wpc_managers_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="managers" />
                <?php $ListTable->display(); ?>
            </form>
        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';

            jQuery(document).ready(function(){
                var user_id = 0;
                var nonce = '';

                jQuery('.delete_action').each( function() {
                    var obj = jQuery(this);

                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '500px',
                        type            : 'ajax',
                        dataType        : 'json',
                        href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                        ajax_data       : "action=wpc_get_user_list&exclude=" + obj.data( 'id' ),
                        setAjaxResponse : function( data ) {
                            user_id = obj.data( 'id' );
                            nonce = obj.data( 'nonce' );

                            jQuery( '.sb_lightbox_content_title' ).html( data.title );
                            jQuery( '.sb_lightbox_content_body' ).html( data.content );
                        }
                    });
                });


                jQuery('#wpc_managers_form').submit(function() {
                    if( jQuery('select[name="action"]').val() == 'delete' ) {
                        user_id = new Array();
                        jQuery("input[name^=item]:checked").each(function() {
                            user_id.push( jQuery(this).val() );
                        });
                        nonce = jQuery('input[name=_wpnonce]').val();

                        if( user_id.length ) {
                            jQuery('.delete_action').shutter_box({
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

                            jQuery('.delete_action').shutter_box('show');
                        }

                        bulk_action_runned = true;
                        return false;
                    }
                });

                jQuery(document).on('click', '.cancel_delete_button', function() {
                    jQuery('.delete_action').shutter_box( 'close' );
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
                            window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&tab=managers&action=delete' + item_string + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name=_wp_http_referer]').val() );
                        }
                    } else {
                        window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&tab=managers&action=delete&id=' + user_id + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=<?php echo urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ); ?>';
                    }
                    jQuery('.delete_action').shutter_box( 'close' );
                    user_id = 0;
                    nonce = '';
                    return false;
                });

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
                            data: 'action=wpc_get_options_filter_for_managers&filter=' + filter,
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
                        switch( jQuery( '#change_filter' ).val() ) {
                            case 'client':
                                window.location = req_uri + '&filter_client=' + jQuery( '#select_filter' ).val() + '&change_filter=client';
                                break;
                            case 'circle':
                                window.location = req_uri + '&filter_circle=' + jQuery( '#select_filter' ).val() + '&change_filter=circle';
                                break;
                    }
                    }
                    return false;
                });


                jQuery( '#cancel_filter' ).click( function() {
                    var req_uri = "<?php echo preg_replace( '/&filter_client=[0-9]+|&filter_circle=[0-9]+|&change_filter=[a-z]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                    window.location = req_uri;
                    return false;
                });


                //open view Capabilities
                jQuery('.various_capabilities').each( function() {
                    var id = jQuery( this ).data( 'id' );

                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '300px',
                        type            : 'ajax',
                        dataType        : 'json',
                        href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                        ajax_data       : "action=wpc_get_user_capabilities&id=" + id + "&wpc_role=wpc_manager",
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
                        data: 'action=wpc_update_capabilities&id=' + id + '&wpc_role=wpc_manager&capabilities=' + JSON.stringify(caps),
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

    </div>

</div>