<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_custom_fields' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

global $wpdb;
$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

//Set Values
if ( !empty( $_POST['custom_fields'] ) && $array = $_POST['custom_fields'] ) {
    reset( $array );
    $name = key( $array );
    $value = array_shift( $array );
    if ( !empty( $wpc_custom_fields[ $name ]['nature'] ) ) {
        $for = $wpc_custom_fields[ $name ]['nature'];
        $only_undefined = filter_input( INPUT_POST, 'wpc_only_undefined' );

        $args = array(
            'fields'    => 'ID',
            //'blog_id'   => get_current_blog_id()
        );

        $ids = array();
        if ( in_array( $for, array( 'staff', 'both' ) ) ) {
            $args['role'] = 'wpc_client_staff';
            $staff = get_users( $args );
            $ids = array_merge( $ids, $staff );
        }
        if ( in_array( $for, array( 'client', 'both' ) ) ) {
            $args['role'] = 'wpc_client';
            $clients = get_users( $args );
            $ids = array_merge( $ids, $clients );
        }

        if ( $only_undefined ) {
            $exists = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} "
            . "WHERE meta_key = %s", $name) );

            $ids = array_diff( $ids, $exists );
        }

        foreach( $ids as $id ) {
            update_user_meta( $id, $name, $value );
        }

        $msg = 'cfu';
    } else {
        $msg = 'wd';
    }

    WPC()->redirect( add_query_arg(
            array( 'page' => 'wpclient_clients', 'tab' => 'custom_fields', 'msg' => $msg ), admin_url( 'admin.php' ) ) );
}

$fields = array();
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
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=custom_fields';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        //delete wizard
        case 'delete':
            $ids = array();
            if ( isset( $_GET['name'] ) ) {
                check_admin_referer( 'wpc_field_delete' .  $_GET['name'] . get_current_user_id() );
                $ids = (array) $_GET['name'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Fields', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) ) {
                foreach ( $ids as $item_id ) {
                    $is_filetype = false;
                    if( isset( $wpc_custom_fields[ $item_id ]['type'] ) && 'file' == $wpc_custom_fields[ $item_id ]['type'] ) {
                        $is_filetype = true;
                    }
                    unset( $wpc_custom_fields[ $item_id ] );
                    WPC()->settings()->update( $wpc_custom_fields, 'custom_fields' );
                    $client_ids = get_users( array( 'role' => 'wpc_client', 'meta_key' => $item_id, 'fields' => 'ID', ) );
                    if ( is_array( $client_ids ) && 0 < count( $client_ids ) ) {
                        foreach( $client_ids as $id ) {
                            if( $is_filetype ) {
                                $filedata = get_user_meta( $id, $item_id, true );
                                $filepath = WPC()->get_upload_dir('wpclient/_custom_field_files/' . $item_id . '/') . $filedata['filename'];
                                if( file_exists( $filepath ) ) {
                                    unlink( $filepath );
                                }

                                delete_user_meta( $id, $item_id );
                            }
                        }
                        if( $is_filetype ) {
                            WPC()->files()->recursive_delete_files( WPC()->get_upload_dir('wpclient/_custom_field_files/' . $item_id . '/') );
                        }
                    }
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            }
            WPC()->redirect( $redirect );
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Fields_List_Table extends WP_List_Table {

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
            break;
            case 'datepicker':
                return __( 'Datepicker', WPC_CLIENT_TEXT_DOMAIN );
            case 'cost':
                return __( 'Cost', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'textarea':
                return __( 'Multi-line Text Box', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'radio':
                return __( 'Radio Buttons', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'checkbox':
                return __( 'Checkboxes', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'selectbox':
                return __( 'Select Box', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'multiselectbox':
                return __( 'Multi Select Box', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'hidden':
                return __( 'Hidden Field', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'file':
                return __( 'File', WPC_CLIENT_TEXT_DOMAIN );
            break;
            case 'password':
                return __( 'Password', WPC_CLIENT_TEXT_DOMAIN );
            break;
        }

        return '';
    }

    function column_id( $item ) {
        return '<span class="order_num">' . $item['id'] . '</span><span class="order_img"></span>' ;
    }

    function column_users( $item ) {
        if( isset( $item['nature'] ) && 'staff' == $item['nature'] ) {
            $users = WPC()->custom_titles['staff']['p'];
        } elseif( isset( $item['nature'] ) && 'both' == $item['nature'] ) {
            $users = sprintf( __( '%s and %s' , WPC_CLIENT_TEXT_DOMAIN )
                    , WPC()->custom_titles['client']['p']
                    ,WPC()->custom_titles['staff']['p']
            );
        } else {
            $users = WPC()->custom_titles['client']['p'];
        }

        return $users;
    }

    function column_title( $item ) {
        return ( isset( $item['title'] ) ) ? $item['title'] : '' ;
    }

    function column_cf_placeholder( $item ) {
        return '{' . $item['name'] . '}' ;
    }

    function column_options( $item ) {
        $html = '';
        $html .= '<input type="checkbox" disabled ';
        $html .= ( isset( $item['required'] ) && '1' == $item['required'] ) ? 'checked' : '';
        $html .= ' />&nbsp;&nbsp;' . __( 'Required', WPC_CLIENT_TEXT_DOMAIN ) . '<br />';

        return $html;
    }


    function column_name( $item ) {
        $actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=custom_fields&edit=' . $item['name'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;
        $actions['set_values'] = '<a href="javascript:void(0);" data-name="' . $item['name'] . '__' . md5( 'wpc_custom_field' . SECURE_AUTH_SALT . $item['name'] ) . '" class="set_values">' . __( 'Set Values', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure you want to delete this Custom Filed?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclient_clients&tab=custom_fields&_wpnonce=' . wp_create_nonce( 'wpc_field_delete' . $item['name'] . get_current_user_id() ) . '&action=delete&name=' . $item['name'] . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;

        return sprintf('%1$s %2$s', '<span class="this_name" id="field_' . $item['name'] . '">' . $item['name'] . '</span>', $this->row_actions( $actions ) );
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Fields_List_Table( array(
    'singular'  => __( 'Field', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Fields', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false

));


$ListTable->set_sortable_columns( array(
) );

$ListTable->set_bulk_actions(array(
    'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'id'                => __( 'Order', WPC_CLIENT_TEXT_DOMAIN ),
    'name'              => __( 'Field Slug (ID)', WPC_CLIENT_TEXT_DOMAIN ),
    'cf_placeholder'    => __( 'Placeholder', WPC_CLIENT_TEXT_DOMAIN ),
    'title'             => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
    'users'             => __( 'For', WPC_CLIENT_TEXT_DOMAIN ),
    'type'              => __( 'Type', WPC_CLIENT_TEXT_DOMAIN ),
    'options'           => __( 'Options', WPC_CLIENT_TEXT_DOMAIN ),
));

$items_count = count( $types );
$items = $types;

$ListTable->prepare_items();
$ListTable->items = $items;
//$ListTable->set_pagination_args( array( 'total_items' => $items_count, 'per_page' => 10000000 ) );
?>

<style>
    #id {
        width: 40px;
    }
</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Custom Field <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Custom Field <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Custom Field <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'wd':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Wrong Data.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'cfu':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Values of Custom Field <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block custom_fields">

            <div>
                <a href="admin.php?page=wpclient_clients&tab=custom_fields&add=1" class="add-new-h2"><?php _e( 'Add New Custom Field', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            </div>

             <form method="get" id="items_form" name="items_form" >
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="custom_fields" />
                <?php $ListTable->display(); ?>
                <p>
                    <span class="description" ><img src="<?php echo WPC()->plugin_url . 'images/sorting_button.png' ?>" style="vertical-align: middle;" /> - <?php _e( 'Drag&Drop to change the order in which these fields appear on the registration form.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                </p>
             </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function(){

                //display Set Values
                jQuery('.set_values').each( function() {
                    var name = jQuery( this ).data( 'name' );

                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '300px',
                        type            : 'ajax',
                        dataType        : 'json',
                        href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                        ajax_data       : "action=wpc_custom_field_set_value&name=" + name,
                        setAjaxResponse : function( data ) {
                            jQuery( '.sb_lightbox_content_title' ).html( data.title );
                            jQuery( '.sb_lightbox_content_body' ).html( data.content );
                        }
                    });
                });


                // AJAX - Update Values of CF
                jQuery('body').on('click', '#wpc_update_value', function () {
                    jQuery( '#wpc_set_value' ).submit();
                });


                //close Set Value
                jQuery('body').on('click', '#wpc_close_set_value', function() {
                    jQuery('.set_values').shutter_box('close');
                });

                jQuery( '#items_form table' ).attr( 'id', 'sortable' );
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
                            if ( '' == new_order )
                                new_order = id;
                            else
                                new_order += ',' + id;
                        });
                    //new_order = jQuery('#sortable tbody').sortable('toArray');
                    //alert(new_order);
                    jQuery( 'body' ).css( 'cursor', 'wait' );

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=change_custom_field_order&new_order=' + new_order,
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