<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//check auth
if ( !current_user_can( 'wpc_archive_clients' ) && !current_user_can( 'wpc_restore_clients' ) && !current_user_can( 'wpc_delete_clients' ) && !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

if ( isset( $_GET['_wp_http_referer'] ) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=archive';
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Archive_User_List_Table extends WP_List_Table {

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
            'ajax'      => false,
            'screen'      => 'wpc_client_archive'
        ) );
        $this->no_items_message = $args['plural'] . ' ' . __(  'not found.', WPC_CLIENT_TEXT_DOMAIN );
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

            do_action( "manage_{$this->screen->id}_table_custom_column", $column_name, $item['id'] );
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
        return apply_filters( "manage_{$this->screen->id}_table_columns", $this->columns );
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
        $actions = array();
        if ( current_user_can( 'wpc_restore_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $actions['restore'] = '<a class="edit" href="'. get_admin_url() . 'admin.php?page=wpclient_clients&tab=archive&action=restore&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_client_restore' . $item['id'] ) . '">Restore</a>';
        }
        if( is_multisite() ) {
            if ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                $actions['delete'] = '<a class="delete_action" data-action="delete_from_blog" data-nonce="' . wp_create_nonce( 'wpc_client_delete' . $item['id'] ) . '" data-id="' . $item['id'] . '" href="javascript: void(0);">' . __( 'Delete Permanently From Blog', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                $actions['delete'] = '<a class="delete_action" data-action="mu_delete" data-nonce="' . wp_create_nonce( 'wpc_client_delete' . $item['id'] ) . '" data-id="' . $item['id'] . '" href="javascript: void(0);">' . __( 'Delete Permanently From Network', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
        } else {
            $actions['delete'] = '<a class="delete_action" data-nonce="' . wp_create_nonce( 'wpc_client_delete' . $item['id'] ) . '" data-id="' . $item['id'] . '" href="javascript: void(0);">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }
        return sprintf('%1$s %2$s', $item['username'], $this->row_actions( $actions ) );
    }

    function column_contact_name( $item ) {
        return $item['contact_name'];
    }

    function column_business_name( $item ) {
        return $item['business_name'];
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }


    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( sprintf( __( 'Search %s' , WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ), 'search-submit' );
        }
    }

//end class
}

$ListTable = new WPC_Archive_User_List_Table(array(
    'singular'  => WPC()->custom_titles['client']['s'],
    'plural'    => WPC()->custom_titles['client']['p'],
    'ajax'      => false
));


switch ( $ListTable->current_action() ) {
    // delete clients
    case 'delete': case 'delete_from_blog': case 'mu_delete':
        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_delete' .  $_REQUEST['id'] );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } else if ( isset( $_REQUEST['item'] ) ) {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
            foreach ( $clients_id as $client_id ) {
                if( $ListTable->current_action() == 'mu_delete' ) {
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

    //restore clients
    case 'restore':
        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_restore' .  $_REQUEST['id'] );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } else if ( isset( $_REQUEST['item'] ) ) {
            check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
            foreach ( $clients_id as $client_id ) {
                //restore client
                WPC()->members()->restore_client( $client_id );
            }
            if( 1 == count( $clients_id ) )
                WPC()->redirect( add_query_arg( 'msg', 'r', $redirect ) );
            else
                WPC()->redirect( add_query_arg( 'msg', 'rs', $redirect ) );
        }
        WPC()->redirect( $redirect );

    default:

        //remove extra query arg
        if ( !empty( $_GET['_wp_http_referer'] ) ) {
            WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
        }
    break;
}

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_clients_archive_per_page' );
$paged      = $ListTable->get_pagenum();

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'u.display_name',
        'u.user_email',
    ) );
}

$order_by = 'u.user_registered';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'user_login' :
            $order_by = 'user_login';
            break;
        case 'display_name' :
            $order_by = 'display_name';
            break;
        case 'business_name' :
            $order_by = 'um2.meta_value';
            break;
        case 'user_email' :
            $order_by = 'user_email';
            break;
    }
}

$sql = "SELECT count( u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE um.meta_key = 'archive' AND um.meta_value = 1
    " . $where_clause;
$items_count = $wpdb->get_var( $sql );


$sql = "SELECT u.ID as id, u.user_login as username, u.display_name as contact_name, u.user_email as email, um2.meta_value as business_name
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'wpc_cl_business_name'
    LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id
    WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND um.meta_value LIKE '%s:10:\"wpc_client\";%' AND um3.meta_key = 'archive' AND um3.meta_value = 1
    " . $where_clause . "
    ORDER BY $order_by
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$users = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->set_sortable_columns( array(
    'username'          => 'user_login',
    'contact_name'      => 'display_name',
    'business_name'     => 'business_name',
    'email'             => 'user_email',
) );
$bulk_actions = array(
    'restore'   => __( 'Restore', WPC_CLIENT_TEXT_DOMAIN ),
);
if( is_multisite() ) {
    $bulk_actions['delete_from_blog'] = __( 'Delete From Blog', WPC_CLIENT_TEXT_DOMAIN );
    $bulk_actions['mu_delete'] = __( 'Delete From Network', WPC_CLIENT_TEXT_DOMAIN );
} else {
    $bulk_actions['delete'] = __( 'Delete', WPC_CLIENT_TEXT_DOMAIN );
}
$ListTable->set_bulk_actions( $bulk_actions );
$ListTable->set_columns(array(
    'cb'                => '<input type="checkbox" />',
    'username'          => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'contact_name'      => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
    'business_name'     => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
    'email'             => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
));


$ListTable->prepare_items();
$ListTable->items = $users;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );



if ( isset( $_GET['msg'] ) ) {
    switch( $_GET['msg'] ) {
        case 'r':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Restored</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
            break;
        case 'rs':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Restored</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p></div>';
            break;
        case 'd':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
            break;
        case 'ds':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p></div>';
            break;
    }
} ?>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <div class="wpc_tab_container_block" style="float:left;width:100%;padding: 0;">
            <form action="" method="get" id="wpc_clients_list_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="archive" />
                <div class="wpc_clear"></div>
                <?php $ListTable->display(); ?>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        var user_id = 0;
        var nonce = '';
        var action = '';

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
                    action = obj.data('action') ? obj.data('action') : 'delete';
                    user_id = obj.data( 'id' );
                    nonce = obj.data( 'nonce' );

                    jQuery( '.sb_lightbox_content_title' ).html( data.title );
                    jQuery( '.sb_lightbox_content_body' ).html( data.content );
                }
            });
        });

        jQuery('#wpc_clients_list_form').submit(function() {
            if( jQuery('select[name="action"]').val() == 'delete' || jQuery('select[name="action2"]').val() == 'delete' ||
                jQuery('select[name="action"]').val() == 'mu_delete' || jQuery('select[name="action2"]').val() == 'mu_delete' ||
                jQuery('select[name="action"]').val() == 'delete_from_blog' || jQuery('select[name="action2"]').val() == 'delete_from_blog' ) {

                action = jQuery('select[name="action"]').val();
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
            action = '';
        });

        jQuery(document).on('click', '.delete_user_button', function() {
            if( user_id instanceof Array ) {
                if( user_id.length ) {
                    var item_string = '';
                    user_id.forEach(function( item, key ) {
                        item_string += '&item[]=' + item;
                    });
                    window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&tab=archive&action=' + action + item_string + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name=_wp_http_referer]').val() );
                }
            } else {
                window.location = '<?php echo admin_url(); ?>admin.php?page=wpclient_clients&tab=archive&action=' + action + '&id=' + user_id + '&_wpnonce=' + nonce + '&' + jQuery('#delete_user_settings').serialize() + '&_wp_http_referer=<?php echo urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ); ?>';
            }
            jQuery('.delete_action').shutter_box( 'close' );
            user_id = 0;
            nonce = '';
            action = '';
            return false;
        });

        //reassign file from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });

    });
</script>