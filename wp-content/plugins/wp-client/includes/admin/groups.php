<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// No caps
if ( !( current_user_can( 'wpc_show_circles' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
    WPC()->redirect( get_admin_url( 'index.php' ) );
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=circles';
}

global $wpdb;

if ( isset( $_REQUEST['action'] ) ) {
    switch ( $_REQUEST['action'] ) {
        /* delete action */
        case 'delete':

            $groups_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_group_delete' .  $_REQUEST['id'] . get_current_user_id() );
                $groups_id = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['circle']['p'] ) );
                $groups_id = $_REQUEST['item'];
            }

            if ( count( $groups_id ) ) {
                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                }
                foreach ( $groups_id as $group_id ) {
                    if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        if( !in_array( $group_id, $manager_groups ) ) {
                            continue;
                        }
                    }

                    /*our_hook_
                    hook_name: wpc_client_before_delete_circle
                    hook_title: Action before delete the circle
                    hook_description: Hook runs before delete the circle.
                    hook_type: action
                    hook_in: wp-client
                    hook_location groups.php
                    hook_since: 4.5.7.1
                    */
                    do_action( 'wpc_client_before_delete_circle', $group_id );

                    WPC()->groups()->delete_group( $group_id );
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            }
            WPC()->redirect( $redirect );

        break;

        //action for create new Client Circle
        case 'create_group':
            if ( !empty( $_REQUEST['group_name'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpc_create_circle' . get_current_user_id() ) ) {
                $args = array(
                    'group_name'        => ( isset( $_REQUEST['group_name'] ) ) ? $_REQUEST['group_name'] : '',
                    'auto_select'       => ( isset( $_REQUEST['auto_select'] ) ) ? '1' : '0',
                    'auto_add_files'    => ( isset( $_REQUEST['auto_add_files'] ) ) ? '1' : '0',
                    'auto_add_pps'      => ( isset( $_REQUEST['auto_add_pps'] ) ) ? '1' : '0',
                    'auto_add_manual'   => ( isset( $_REQUEST['auto_add_manual'] ) ) ? '1' : '0',
                    'auto_add_self'     => ( isset( $_REQUEST['auto_add_self'] ) ) ? '1' : '0',
                    'assign'            => ( isset( $_REQUEST['wpc_clients'] ) ) ? $_REQUEST['wpc_clients'] : ''
                );
                $result = WPC()->groups()->create_circle( $args );
                if( is_numeric( $result ) && current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns SET"
                            . " `object_type` = 'manager'"
                            . ", `object_id` = %d"
                            . ", `assign_type` = 'circle'"
                            . ", `assign_id` = %d"
                        , get_current_user_id(),  $result ) );
                }
                if ( $result ) {
                    WPC()->redirect( add_query_arg( 'msg', 'c', get_admin_url(). 'admin.php?page=wpclient_clients&tab=circles' ) );
                } else {
                    WPC()->redirect( add_query_arg( 'msg', 'ae', get_admin_url(). 'admin.php?page=wpclient_clients&tab=circles' ) );
                }
            }

        break;

        //action for edit Client Circle
        case 'edit_group':
            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                if( !empty( $_REQUEST['id'] ) && !in_array( $_REQUEST['id'], $manager_groups ) ) {
                    WPC()->redirect( add_query_arg( 'msg', 'ae', get_admin_url(). 'admin.php?page=wpclients_groups' ) );
                }
            }
            //check_admin_referer( 'wpc_edit_group' .  get_current_user_id() );
            if ( !empty( $_REQUEST['group_name'] ) && !empty( $_REQUEST['id'] ) && !empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpc_update_circle' . get_current_user_id() . $_REQUEST['id'] ) ) {

                $args = array(
                    'id'          => ( $id = filter_input( INPUT_POST, 'id' ) ) ? $id : '0',
                    'group_name'        => $_REQUEST['group_name'],
                    'auto_select'       => ( isset( $_REQUEST['auto_select'] ) ) ? '1' : '0',
                    'auto_add_files'    => ( isset( $_REQUEST['auto_add_files'] ) ) ? '1' : '0',
                    'auto_add_pps'      => ( isset( $_REQUEST['auto_add_pps'] ) ) ? '1' : '0',
                    'auto_add_manual'   => ( isset( $_REQUEST['auto_add_manual'] ) ) ? '1' : '0',
                    'auto_add_self'     => ( isset( $_REQUEST['auto_add_self'] ) ) ? '1' : '0',
                    'assign'            => ( isset( $_REQUEST['wpc_clients'] ) ) ? $_REQUEST['wpc_clients'] : '',
                );
                $result = WPC()->groups()->update_circle( $args );

                if ( $result ) {
                    WPC()->redirect( add_query_arg( 'msg', 's', get_admin_url(). 'admin.php?page=wpclient_clients&tab=circles' ) );
                } else {
                    WPC()->redirect( add_query_arg( 'msg', 'ae', get_admin_url(). 'admin.php?page=wpclient_clients&tab=circles' ) );
                }
            }

        break;

    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}

$order_by = 'id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'group_name' :
            $order_by = 'group_name';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Group_List_Table extends WP_List_Table {

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
            } elseif ( is_string( $k ) ) {
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

    function column_group_id( $item ) {
        return $item['id'];
    }

    function column_group_name( $item ) {
        $actions = array();

        $a_edit = '<a href="javascript:void(0);" data-id="' . $item['id'] . '" class="wpc_edit_circle">';
        $actions['edit'] = $a_edit . __('Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        $actions['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) . '");\''
                . ' href="admin.php?page=wpclient_clients&tab=circles&action=delete&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_group_delete' . $item['id'] . get_current_user_id() ) . '" >' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf( '%1$s %2$s', '<span>' . $item['group_name'] . '</span>', $this->row_actions( $actions ) );
    }

    function get_selectbox( $bool ) {
        return '<input type="checkbox" class="info_checkbox"'
            . disabled(1, 1, false) . checked( $bool, true, false ) . '>';
    }

    function column_auto_select( $item ) {
         return $this->get_selectbox( 1 == $item['auto_select'] );
    }

    function column_auto_add_files( $item ) {
        return $this->get_selectbox( 1 == $item['auto_add_files'] );
    }

    function column_auto_add_pps( $item ) {
        return $this->get_selectbox( 1 == $item['auto_add_pps'] );
    }

    function column_auto_add_manual( $item ) {
        return $this->get_selectbox( 1 == $item['auto_add_manual'] );
    }

    function column_auto_add_self( $item ) {
        return $this->get_selectbox( 1 == $item['auto_add_self'] );
    }

    function column_assign( $item ) {
        $clients_id = WPC()->groups()->get_group_clients_id( $item['id'] );

        $link_array = array(
            'title'   => sprintf( __( 'Assign %s to ', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . $item['group_name'],
            'data-ajax' => true,
            'data-id' => $item['id'],
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['id'],
            'value' => implode( ',', $clients_id )
        );
        $additional_array = array(
            'counter_value' => count( $clients_id )
        );
        $html = WPC()->assigns()->assign_popup('client', 'wpclients_groups', $link_array, $input_array, $additional_array, false );

        return $html;
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Group_List_Table( array(
        'singular'  => WPC()->custom_titles['circle']['s'],
        'plural'    => WPC()->custom_titles['circle']['p'],
        'ajax'      => false

));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_circles_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'group_name'        => 'group_name',
    'id'          => 'id',
) );

$ListTable->set_bulk_actions(array(
    'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'cb'                => '<input type="checkbox" />',
    'id'                => __( 'ID', WPC_CLIENT_TEXT_DOMAIN ),
    'group_name'        => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
    'assign'            => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    'auto_select'       => __( 'Checkbox Select', WPC_CLIENT_TEXT_DOMAIN ) . WPC()->admin()->tooltip( sprintf( "Auto-selects this %s in all assignment popup boxes", WPC()->custom_titles['circle']['s'] ) ),
    'auto_add_files'    => __( 'Files', WPC_CLIENT_TEXT_DOMAIN ) . WPC()->admin()->tooltip( sprintf( "Auto-assigns all newly uploaded files to this %s", WPC()->custom_titles['circle']['s'] ) ),
    'auto_add_pps'      => WPC()->custom_titles['portal_page']['p'] . WPC()->admin()->tooltip( sprintf( "Auto-assigns all newly created %s to this %s", WPC()->custom_titles['portal_page']['p'], WPC()->custom_titles['circle']['s'] ) ),
    'auto_add_manual'   => sprintf( __( 'Manual %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . WPC()->admin()->tooltip( sprintf( "Auto-assigns all new manually created %s to this %s", WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ) ),
    'auto_add_self'     => sprintf( __( 'Registered %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . WPC()->admin()->tooltip( sprintf( "Auto-assigns all new self-registered %s to this %s", WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ) ),
));
$where = '';

if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
    if( count( $manager_groups ) ) {
        $where .= " AND group_id IN (" . implode( ',', $manager_groups ) . ")";
    } else {
        $where .= " AND 1 = 0";
    }
}

$sql = "SELECT count( group_id )
    FROM {$wpdb->prefix}wpc_client_groups
    WHERE 1=1 $where";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT *, group_id as id
    FROM {$wpdb->prefix}wpc_client_groups
    WHERE 1=1 $where
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$groups = $wpdb->get_results( $sql, ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $groups;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );

?>

<style>
    .column-id {
        width: 5%;
    }

    tr .column-assign,
    tr .column-auto_select,
    tr .column-auto_add_files,
    tr .column-auto_add_pps,
    tr .column-auto_add_manual,
    tr .column-auto_add_self
    {
        text-align: center;
    }

    #edit_group table th {
        font-size: 12px !important;
    }

    #wpc_edit_circle {
        display: none;
    }

</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'ae':
                echo '<div id="message" class="error wpc_notice fade"><p>' . sprintf( __( 'The %s already exists! or Something wrong.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) . '</p></div>';
                break;
            case 's':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Changes to %s have been saved', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) . '</p></div>';
                break;
            case 'c':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s has been created!', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s %s is deleted!', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['s'] ) . '</p></div>';
                break;
        }
    }
    ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <a id="add_circle" class="add-new-h2 wpc_form_link"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>

            <form action="" method="get" name="edit_group" id="edit_group">
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="circles" />
                <?php $ListTable->display(); ?>
            </form>

        </div>

        <div id="wpc_edit_circle">

            <form method="post" action="" class="wpc_form">
                <table>
                    <table class="form-table">
                    <tr>
                        <td>
                            <input type="hidden" name="id" id="wpc_id" />
                            <input type="hidden" name="action" id="wpc_action" />
                            <input type="hidden" name="_wpnonce" id="wpc_wpnonce" />
                            <?php printf( __( '%s Name', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ) ?>:<span class="required">*</span>
                            <input type="text" class="input" name="group_name" id="wpc_group_name" value="" size="30" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_select" id="wpc_auto_select" value="1" />
                                <?php printf( __( 'Auto-Select this %s on the Assign Popups', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ) ?>
                            </label>
                        </td>
                    </tr>
                     <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_add_files" id="wpc_auto_add_files" value="1" />
                                <?php printf( __( 'Automatically assign new Files to this %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?>
                            </label>
                        </td>
                    </tr>
                     <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_add_pps" id="wpc_auto_add_pps" value="1" />
                                <?php printf( __( 'Automatically assign new %s to this %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'], WPC()->custom_titles['circle']['s'] ) ?>
                            </label>
                        </td>
                    </tr>
                     <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_add_manual" id="wpc_auto_add_manual" value="1" />
                                <?php printf( __( 'Automatically assign new manual %s to this %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ) ?>
                            </label>
                        </td>
                    </tr>
                     <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_add_self" id="wpc_auto_add_self" value="1" />
                                <?php printf( __( 'Automatically assign new self-registered %s to this %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ) ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ),
                                    'text'    => sprintf( __( 'Assign %s To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ),

//                                    'data-callback' => 'shortcode_popup',
//                                    'data-input' => $attributes['id']
                                );
                                $input_array = array(
                                    'name'  => 'wpc_clients',
                                    'id'    => 'wpc_clients',
                                    'value' => '',

//                                    'data-key' => $attributes['key'],
//                                    'class' => 'clients_field'
                                );
                                $additional_array = array(
                                    'counter_value' => 0
                                );
                                WPC()->assigns()->assign_popup('client', 'wpclients_groups', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <?php
                    /*our_hook_
                    hook_name: wpc_circle_form_fields
                    hook_title: Add/Edit Circle Form
                    hook_description: Hook runs in Add/Edit Circle Form.
                    hook_type: action
                    hook_in: wp-client
                    hook_location groups.php
                    hook_since: 4.1.6
                    */
                    do_action('wpc_circle_form_fields'); ?>
                </table>
                <br>
                <div class="save_button">
                    <input type="submit" class="button-primary wpc_submit" id="add_group" value="<?php printf( __( 'Save %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ) ?>" />
                </div>
            </form>
        </div>

        <script type="text/javascript">

            function clear_form_elements(ele) {
                jQuery(ele).find(':input').each(function() {
                    switch(this.type) {
                        case 'password':
                        case 'select-multiple':
                        case 'select-one':
                        case 'text':
                        case 'textarea':
                        case 'hidden':
                            jQuery(this).val('');
                            break;
                        case 'checkbox':
                        case 'radio':
                            this.checked = false;
                    }
                });

            }

            function set_data( data ) {
                /*if( data.action === undefined ) {
                    //clear
                    jQuery( '#wpc_circle_action' ).val( '' );
                    jQuery( '#wpc_circle_wpnonce' ).val( '' );
                    jQuery( '#wpc_clients' ).val( '' );
                    jQuery( '#wpc_id' ).val( '' );
                    jQuery( '#wpc_group_name' ).val( '' );
                    jQuery( '.counter_wpc_clients' ).text( '(0)' );
                } else*/
                clear_form_elements('.wpc_form');
                for( key in data ) {
                    var obj = jQuery( '#wpc_' + key );
                    if( obj.length > 0 ) {
                        switch(obj[0].type) {
                            case 'password':
                            case 'select-multiple':
                            case 'select-one':
                            case 'text':
                            case 'textarea':
                            case 'hidden':
                                obj.val( data[key] );
                                break;
                            case 'checkbox':
                            case 'radio':
                                obj.prop('checked', data[key] == '1' );
                        }
                    }
                }

                if( 'edit_group' === data.action ) {
                    //edit
                    jQuery( '#wpc_clients' ).val( data.clients );
                    jQuery( '.counter_wpc_clients' ).text( '(' + data.count_clients + ')' );
                } else {
                    //create
                    jQuery( '#wpc_clients' ).val( '' );
                    jQuery( '.counter_wpc_clients' ).text( '(0)' );
                }

            }

            jQuery( document ).ready( function() {

                //reassign file from Bulk Actions
                jQuery( '#doaction2' ).click( function() {
                    var action = jQuery( 'select[name="action2"]' ).val() ;
                    jQuery( 'select[name="action"]' ).attr( 'value', action );

                    return true;
                });

                jQuery( '#add_circle, .wpc_edit_circle').each( function() {
                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '500px',
                        type            : 'inline',
                        href            : '#wpc_edit_circle',
                        title           : ( 'add_circle' === jQuery( this ).prop('id') )
                            ? '<?php echo esc_js( sprintf( __( 'New %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ) ); ?>'
                            : '<?php echo esc_js( sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ) ); ?>',
                    });
                });

                jQuery( '#add_circle, .wpc_edit_circle').click( function() {
                    var obj = jQuery(this);
                    var id = obj.data('id');

                    obj.shutter_box('showPreLoader');
                    jQuery.ajax({
                        type     : 'POST',
                        dataType : 'json',
                        url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                        data     : 'action=get_data_circle&id=' + id,
                        success: function( data ) {
                            set_data( data );
                        },
                        error: function(data) {
                            obj.shutter_box('close');
                        }
                    });

                });


                //Click for save circle
                jQuery('body').on('click', '#add_group', function() {
                    if ( !jQuery(this).parents( 'form').find("#wpc_group_name" ).val() ) {
                        jQuery(this).parents( 'form').find("#wpc_group_name" ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    } else {
                        jQuery(this).parents('form').submit();
                    }
                });

            });
        </script>

    </div>

</div>