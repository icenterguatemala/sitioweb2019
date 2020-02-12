<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=admins';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        /* delete action */
        case 'delete':

            $admins_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_admin_delete' .  $_REQUEST['id'] . get_current_user_id() );
                $admins_id = (array)$_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['admin']['p'] ) );
                $admins_id = $_REQUEST['item'];
            }

            if ( count( $admins_id ) ) {
                foreach ( $admins_id as $admin_id ) {
                    $admin_data  = get_userdata( $admin_id );
                    //delete admin redirects
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value=%s", $admin_data->user_login ) );
                    if( is_multisite() ) {
                        wpmu_delete_user( $admin_id );
                    } else {
                        wp_delete_user( $admin_id );
                    }
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            }
            WPC()->redirect( $redirect );
        break;

        case 'temp_password':
            $admins_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'admin_temp_password' .  $_REQUEST['id'] . get_current_user_id() );
                $admins_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['admin']['p'] ) );
                $admins_id = $_REQUEST['item'];
            }

            foreach ( $admins_id as $admin_id ) {
                WPC()->members()->set_temp_password( $admin_id );
            }

            if( 1 < count( $admins_id ) ) {
                WPC()->redirect( add_query_arg( 'msg', 'pass_s', $redirect ) );
            } else if( 1 === count( $admins_id ) ) {
                WPC()->redirect( add_query_arg( 'msg', 'pass', $redirect ) );
            } else {
                WPC()->redirect( $redirect );
            }

            break;

        case 'send_welcome':
            $admins_id = array();
            if ( isset( $_REQUEST['user_id'] ) ) {
                check_admin_referer( 'wpc_re_send_welcome' .  $_REQUEST['user_id'] . get_current_user_id() );
                $admins_id = ( is_array( $_REQUEST['user_id'] ) ) ? $_REQUEST['user_id'] : (array) $_REQUEST['user_id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['admin']['p'] ) );
                $admins_id = $_REQUEST['item'];
            }

            if ( count( $admins_id ) ) {
                foreach ( $admins_id as $admin_id ) {
                    //re send welcome
                    WPC()->members()->resend_welcome_email( $admin_id );
                }
                WPC()->redirect( add_query_arg( 'msg', 'wel', $redirect ) );
            }
            WPC()->redirect( $redirect );

            break;
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}


$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'u.user_nicename',
        'u.user_email',
    ) );
}

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

class WPC_Admins_List_Table extends WP_List_Table {

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

    function column_username( $item ) {
        $actions = $hide_actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=admins_edit&id=' . $item['id'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        $hide_actions['wpc_capability'] = '<a href="#wpc_capability" data-id="' . $item['id'] . '_' . md5( 'wpc_admin' . SECURE_AUTH_SALT . $item['id'] ) . '" class="various_capabilities">' . __( 'Individual Capabilities', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        if ( !get_user_meta( $item['id'], 'wpc_temporary_password', true ) ) {
            $hide_actions['wpc_temp_password'] = '<a onclick=\'return confirm("' . sprintf( __( 'Do you want to mark the password as temporary for this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) . '");\' '
                    . 'href="admin.php?page=wpclient_clients&tab=admins&action=temp_password&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'admin_temp_password' . $item['id'] . get_current_user_id() ) .'">' . __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        if ( !isset( $item['time_resend'] ) || ( $item['time_resend'] + 3600*23 ) < time() ) {
            $hide_actions['wpc_resend_welcome'] = '<a onclick=\'return confirm("' . __( 'Are you sure you want to Re-Send Welcome Email?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclient_clients&tab=admins&action=send_welcome&user_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_re_send_welcome' . $item['id'] .get_current_user_id() ) .'">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        } else {
            $hide_actions['wpc_resend_welcome'] = '<span title="' . sprintf( __( 'Wait around %s hours for re-send it.', WPC_CLIENT_TEXT_DOMAIN ), round( ( ( $item['time_resend'] + 3600*24 ) -  time() ) / 3600 ) ) . '">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
        }

        $hide_actions['delete'] = '<a class="delete_action" data-nonce="' . wp_create_nonce( 'wpc_admin_delete' . $item['id'] . get_current_user_id() ) . '" data-id="' . $item['id'] . '" href="javascript: void(0);">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        /*our_hook_
        hook_name: wpc_client_more_actions_admins
        hook_title: Add more actions on Admins page
        hook_description: Hook runs before display more actions on Admins page.
        hook_type: filter
        hook_in: wp-client
        hook_location admins.php
        hook_param: string $error
        hook_since: 3.9.5
        */
        $hide_actions = apply_filters( 'wpc_client_more_actions_admins', $hide_actions );

        if( count( $hide_actions ) ) {
            $actions['wpc_actions'] = WPC()->admin()->more_actions( $item['id'], __( 'Actions', WPC_CLIENT_TEXT_DOMAIN ), $hide_actions );
        }

        return sprintf('%1$s %2$s', '<span id="admin_username_' . $item['id'] . '">' . $item['username'] . '</span>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( sprintf( __( 'Search %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['p'] ), 'search-submit' );
        }
    }
}


$ListTable = new WPC_Admins_List_Table( array(
    'singular'  => WPC()->custom_titles['admin']['s'],
    'plural'    => WPC()->custom_titles['admin']['p'],
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_admins_per_page' );
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
));


$sql = "SELECT count( u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%s:9:\"wpc_admin\";%'
        {$where_clause}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT u.ID as id, u.user_login as username, u.user_nicename as nickname, u.user_email as email, um3.meta_value as time_resend
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um3 ON ( u.ID = um3.user_id AND um3.meta_key = 'wpc_send_welcome_email' )
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%s:9:\"wpc_admin\";%'
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$admins = $wpdb->get_results( $sql, ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $admins;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <?php if ( isset( $_GET['msg'] ) ) {
        $msg = $_GET['msg'];
        switch( $msg ) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) . '</p></div>';
                break;
            case 'wel':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Re-Sent Email for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) . '</p></div>';
                break;
            case 'pass':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The password marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) . '</p></div>';
                break;
            case 'pass_s':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The passwords marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['p'] ) . '</p></div>';
                break;
        }
    } ?>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <div class="wpc_clear"></div>

            <a class="add-new-h2" href="admin.php?page=wpclient_clients&tab=admins_add"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>

            <form action="" method="get" name="wpc_clients_form" id="wpc_admins_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="admins" />
                <?php $ListTable->display(); ?>
            </form>
        </div>
    </div>

    <script type="text/javascript">

        jQuery(document).ready( function() {
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


            jQuery('#wpc_admins_form').submit(function() {
                if( jQuery('select[name="action"]').val() == 'delete' || jQuery('select[name="action2"]').val() == 'delete' ) {

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
                        window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&tab=admins&action=delete' + item_string + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name=_wp_http_referer]').val() );
                    }
                } else {
                    window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&tab=admins&action=delete&id=' + user_id + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=<?php echo urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ); ?>';
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


            //display client capabilities
            jQuery('.various_capabilities').each( function() {
                var id = jQuery( this ).data( 'id' );

                jQuery(this).shutter_box({
                    view_type       : 'lightbox',
                    width           : '300px',
                    type            : 'ajax',
                    dataType        : 'json',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data       : "action=wpc_get_user_capabilities&id=" + id + "&wpc_role=wpc_admin",
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
                    data: 'action=wpc_update_capabilities&id=' + id + '&wpc_role=wpc_admin&capabilities=' + JSON.stringify(caps),
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