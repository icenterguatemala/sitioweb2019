<?php

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_modify_feedback_wizards' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_feedback_wizard';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        //delete wizard
        case 'delete':
            $ids = array();
            if ( isset( $_GET['wizard_id'] ) ) {
                check_admin_referer( 'wpc_wizard_delete' .  $_GET['wizard_id'] . get_current_user_id() );
                $ids = (array) $_REQUEST['wizard_id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Wizards', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) ) {
                foreach ( $ids as $wizard_id ) {
                     //delete wizard_id
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_feedback_wizards WHERE wizard_id = %d", $wizard_id ) );

                    //delete items from wizard
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_feedback_wizard_items WHERE wizard_id = %d", $wizard_id ) );

                    //delete all assigns
                    WPC()->assigns()->delete_all_object_assigns( 'feedback_wizard', $wizard_id );

                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;
        case 'sent':
            //send emails to clients
            if (  isset( $_GET['wizard_id'] ) ) {
                $wizard_id = $_REQUEST['wizard_id'];
                if ( 0 < $wizard_id ) {
                    $wizard_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_wizards WHERE wizard_id = %d", $wizard_id ), ARRAY_A );
                    //get clients id
                    $send_client_ids = array();
                    $send_client_ids = WPC()->assigns()->get_assign_data_by_object( 'feedback_wizard', $wizard_id, 'client' );
                    $send_group_ids = WPC()->assigns()->get_assign_data_by_object( 'feedback_wizard', $wizard_id, 'circle' );

                    if ( is_array( $send_group_ids ) ) {
                        foreach( $send_group_ids as $group_id ) {
                            $send_client_ids = array_merge( $send_client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }
                    }
                    $send_client_ids = array_unique( $send_client_ids );
                    
                    //for managers
                    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                        $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                        foreach( $manager_circles as $c_id ) {
                            $manager_clients = array_merge( $manager_clients, WPC()->groups()->get_group_clients_id( $c_id ) );
                        }
                        $manager_clients = array_unique( $manager_clients );
                        
                        $send_client_ids = array_intersect( $send_client_ids, $manager_clients );
                    }

                    /* to delete if ( '' != $wizard_data['clients_id'] ) {
                        $send_client_ids = explode( ',', str_replace( '#', '', $wizard_data['clients_id'] ) );
                    }

                    //get clients id from Client Circles
                    if ( '' != $wizard_data['groups_id'] ) {
                        $send_group_ids = explode( ',', str_replace( '#', '', $wizard_data['groups_id'] ) );
                        if ( is_array( $send_group_ids ) )
                            foreach( $send_group_ids as $group_id )
                                $send_client_ids = array_merge( $send_client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );

                        $send_client_ids = array_unique( $send_client_ids );
                    }
                    */

                    //send email
                    if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {
                        $send1 = 0;
                        $send2 = 0;
                        foreach( $send_client_ids as $send_client_id ) {
                            if ( '' != $send_client_id ) {
                                //there are any assigned clients
                                $send1 = 1;

                                //check if client not left feedback for this version
                                $sql = "SELECT result_id FROM {$wpdb->prefix}wpc_client_feedback_results WHERE wizard_id = %d AND client_id = %d AND wizard_version = '%s' ";
                                $result_id = $wpdb->get_var( $wpdb->prepare( $sql, $wizard_data['wizard_id'], $send_client_id, $wizard_data['version'] ) );
                                if ( empty( $result_id ) || 0 > $result_id  ) {
                                    //there are any clients for leave feedback
                                    $send2 = 1;

                                    //make link
                                    if ( WPC()->permalinks ) {
                                        $wizard_link = WPC()->get_slug( 'feedback_wizard_page_id' ) . $wizard_data['wizard_id'];
                                    } else {
                                        $wizard_link = add_query_arg( array( 'wpc_page' => 'feedback_wizard', 'wpc_page_value' => $wizard['wizard_id'] ), WPC()->get_slug( 'feedback_wizard_page_id', false ) );
                                    }

                                    $args = array( 'client_id' => $send_client_id, 'wizard_name' => $wizard_data['name'], 'wizard_url' => $wizard_link );
                                    $client_email = get_userdata( $send_client_id )->get( 'user_email' );
                                    //send email
                                    WPC()->mail( 'wizard_notify', $client_email, $args, 'wizard_notify' );

                                }
                            }
                        }

                        if ( 0 == $send1 && 0 == $send2 ) {
                            //no any clients
                            $msg = 'ns1';
                        } else if ( 1 == $send1 && 0 == $send2 ) {
                            //all left feedback
                            $msg = 'ns2';
                        } else {
                            //sent email for clients
                            $msg = 's';
                        }

                    } else {
                        //no any clients
                        $msg = 'ns1';
                    }

                    WPC()->redirect( add_query_arg( 'msg', $msg, $redirect ) );
                    exit;
                }

                //do nothing
                WPC()->redirect( $redirect );
                exit;

            }
    }
}




//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause .= WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'fw.name',
        'fi.name',
    ) );
}

//filter
$where_filter = '';
if ( isset( $_GET['change_filter'] ) ) {
    switch ( $_GET['change_filter'] ) {
        case 'client':
            if ( isset( $_GET['select_filter'] ) ) {
                $select_filter = $_GET['select_filter'];

                $client_wizards = WPC()->assigns()->get_assign_data_by_assign( 'feedback_wizard', 'client', $select_filter );
                $where_filter .= " AND fw.wizard_id IN('" . implode( "','", $client_wizards ) . "')";


                /* to delete if ( is_numeric( $select_filter ) )
                    $where_filter = " WHERE clients_id LIKE '%#" . $select_filter . "%'";*/
            }
            break;
        case 'circle':
            if ( isset( $_GET['select_filter'] ) ) {
                $select_filter = $_GET['select_filter'];

                $circle_wizards = WPC()->assigns()->get_assign_data_by_assign( 'feedback_wizard', 'circle', $select_filter );
                $where_filter .= " AND fw.wizard_id IN('" . implode( "','", $circle_wizards ) . "')";


               /* to delete if ( is_numeric( $select_filter ) )
                    $where_filter = " WHERE groups_id LIKE '%#" . $select_filter . "%'"; */
            }
            break;
    }
}

$order_by = 'time';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'name' :
            $order_by = 'name';
            break;
        case 'items' :
            $order_by = 'items';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Feedback_List_Table extends WP_List_Table {

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['wizard_id']
        );
    }

    function column_time( $item ) {
        return WPC()->date_format( $item['time'] );
    }

    function column_clients( $item ) {
        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $clients = WPC()->assigns()->get_assign_data_by_object( 'feedback_wizard', $item['wizard_id'], 'client' );
        $clients_ids = count( $clients );

        $link_array = array(
            'data-id' => $item['wizard_id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . ' ' . stripslashes( $item['name'] )
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['wizard_id'],
            'value' => implode( ',', $clients )
        );
        $additional_array = array(
            'counter_value' => $clients_ids,
            'wpc_ajax_prefix' => 'fw'
        );
        $html = WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_circles( $item ) {
        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $client_groups = WPC()->assigns()->get_assign_data_by_object( 'feedback_wizard', $item['wizard_id'], 'circle' );
        $count = count( $client_groups );

        $link_array = array(
            'data-id' => $item['wizard_id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . ' ' . stripslashes( $item['name'] )
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['wizard_id'],
            'value' => implode( ',', $client_groups )
        );
        $additional_array = array(
            'counter_value' => $count,
            'wpc_ajax_prefix' => 'fw'
        );
        $html = WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $html    ;
    }

    function column_name( $item ) {

        $actions = array();

        $actions['edit'] = '<a href="admin.php?page=wpclients_feedback_wizard&tab=edit_wizard&wizard_id=' . $item['wizard_id'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Wizard?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_feedback_wizard&action=delete&wizard_id=' . $item['wizard_id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_wizard_delete' . $item['wizard_id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        $actions['send'] = '<a href="admin.php?page=wpclients_feedback_wizard&action=sent&wizard_id=' . $item['wizard_id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_wizard_send' . $item['wizard_id'] . get_current_user_id() ) . '">' . sprintf( __( 'Send Email to %s(s)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</a>';

        return sprintf('%1$s %2$s', '<a href="admin.php?page=wpclients_feedback_wizard&tab=edit_wizard&wizard_id=' . $item['wizard_id'] . '">' . $item['name'] . '</a>', $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            global $wpdb;
            $all_filter = array( WPC()->custom_titles['client']['s'] => 'client', WPC()->custom_titles['circle']['s'] => 'circle',  );

            $all_groups            = array();
            $all_circles_groups    = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups", "ARRAY_A" );

            //change structure of array for display circle name in row in table and selectbox
            foreach ( $all_circles_groups as $value ) {
                $all_groups[ $value['group_id'] ] = $value['group_name'];
            }


        ?>


        <div class="alignleft actions">
            <select name="change_filter" id="change_filter" style="float:left;">
                <option value="-1" <?php if( !isset( $_GET['change_filter'] ) || !in_array( $_GET['change_filter'], $all_filter ) ) echo 'selected'; ?>><?php _e( 'Select Filter', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                <?php
                    foreach ( $all_filter as $key => $type_filter ) {
                        $selected = ( isset( $_GET['change_filter'] ) && $type_filter == $_GET['change_filter'] ) ? ' selected' : '' ;
                        echo '<option value="' . $type_filter . '"' . $selected . ' >' . __( $key, WPC_CLIENT_TEXT_DOMAIN ) . '</option>';
                    }
                 ?>
            </select>
            <select name="select_filter" id="select_filter" style="float:left; <?php if ( !isset( $_GET['change_filter'] ) || !in_array( $_GET['change_filter'], $all_filter ) ) echo ' display: none;'; ?>">
                <?php
                    if ( isset( $_GET['change_filter'] ) ) {
                        if ( 'client' == $_GET['change_filter'] && isset( $_GET['select_filter'] ) ) {

                            $unique_client = WPC()->assigns()->get_assign_data_by_object_assign( 'feedback_wizard', 'client' );

                            ?>
                            <option value="-1" <?php if ( !in_array( $_GET['select_filter'], $unique_client ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                            <?php
                            if ( is_array( $unique_client ) && 0 < count( $unique_client ) )
                            foreach( $unique_client as $client_id ) {
                                if ( '' != $client_id ) {
                                    $selected = ( $client_id == $_GET['select_filter'] ) ? 'selected' : '';
                                    echo '<option value="' . $client_id . '" ' . $selected . ' >' . get_userdata( $client_id )->user_login . '</option>';
                                }
                            }
                        }
                        elseif ( 'circle' == $_GET['change_filter'] && isset( $_GET['select_filter'] ) ) {

                            $unique_circle = WPC()->assigns()->get_assign_data_by_object_assign( 'feedback_wizard', 'circle' );

                            $unique_circle = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN('" . implode( "','", $unique_circle ) . "')", ARRAY_A );

                            ?>
                            <option value="-1" <?php if ( !in_array( $_GET['select_filter'], $unique_circle ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></option>
                            <?php
                            if ( is_array( $unique_circle ) && 0 < count( $unique_circle ) ) {
                                foreach( $unique_circle as $circle_id ) {
                                    if ( '' != $circle_id['group_id'] ) {
                                        $selected = ( $circle_id['group_id'] == $_GET['select_filter'] ) ? 'selected' : '';
                                        echo '<option value="' . $circle_id['group_id'] . '" ' . $selected . ' >' . $circle_id['group_name'] . '</option>';
                                    }
                                }
                            }
                        }
                    }
                ?>
            </select>
            <span id="load_select_filter"></span>
            <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="filtered" name="" style="float:left;" />
            <a class="add-new-h2 cancel_filter" id="cancel_filter" style="float:left; cursor: pointer; margin-top: 4px;<?php if( !isset( $_GET['filter_author']) && !isset( $_GET['select_filter']) && !isset($_GET['select_filter']) ) echo ' display: none;'; ?>" ><?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?><span class="ez_cancel_button" style="margin: 1px 0px 0px 7px;"></span></a>
        </div>



        <?php $this->search_box( sprintf( __( 'Search %s Wizards', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Feedback_List_Table( array(
    'singular'  => __( 'Wizard', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Wizards', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_fw_wizards_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'name'              => 'name',
    'items'             => 'items',
) );

$ListTable->set_bulk_actions(array(
    'delete'    => 'Delete',
));

$ListTable->set_columns(array(
    'cb'                => '<input type="checkbox" />',
    'name'              => __( 'Wizard Name', WPC_CLIENT_TEXT_DOMAIN ),
    'version'           => __( 'Version', WPC_CLIENT_TEXT_DOMAIN ),
    'items'             => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
    'clients'           => WPC()->custom_titles['client']['p'],
    'circles'           => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
    'time'              => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
));

$sql = "SELECT COUNT( DISTINCT fw.wizard_id )
    FROM {$wpdb->prefix}wpc_client_feedback_wizards fw
    LEFT JOIN {$wpdb->prefix}wpc_client_feedback_wizard_items fwi ON fw.wizard_id = fwi.wizard_id
    LEFT JOIN {$wpdb->prefix}wpc_client_feedback_items fi ON fi.item_id = fwi.item_id
    WHERE 1=1
        {$where_clause}
        {$where_filter}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT fw.wizard_id as wizard_id, version, fw.name as name, time, count(fwi.wizard_id) as items
    FROM {$wpdb->prefix}wpc_client_feedback_wizards fw
    LEFT JOIN {$wpdb->prefix}wpc_client_feedback_wizard_items fwi ON fw.wizard_id = fwi.wizard_id
    LEFT JOIN {$wpdb->prefix}wpc_client_feedback_items fi ON fi.item_id = fwi.item_id
    WHERE 1=1
        {$where_clause}
        {$where_filter}
    GROUP BY fw.wizard_id
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$wizards = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $wizards;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients';
} ?>

<style type="text/css">
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
    if ( '' == WPC()->get_slug( 'feedback_wizard_page_id' ) ) {
        WPC()->admin()->get_install_page_notice();
    }
    ?>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        $msg = $_GET['msg'];
        switch( $msg ) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Wizard <strong>Created</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Wizard <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Wizard(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'ac':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s are assigned', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p></div>';
                break;
            case 'ag':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s are assigned', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ) . '</p></div>';
                break;
            case 'ae':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Some error with assigning permission for wizard.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 's':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Email sent to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'ns1':
                echo '<div id="message" class="error wpc_notice fade"><p>' . sprintf( __( 'Email are not sent: no assigned %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p></div>';
                break;
            case 'ns2':
                echo '<div id="message" class="error wpc_notice fade"><p>' . sprintf( __( 'Email are not sent: %s already left feedback for this wizard version.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p></div>';
                break;
        }
    }
    ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_feedback_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_fw_wizards" style="position: relative;">
            <a href="admin.php?page=wpclients_feedback_wizard&tab=create_wizard " class="add-new-h2">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>

            <form action="" method="get" name="wpc_clients_form" id="wpc_clients_form">
                <input type="hidden" name="page" value="wpclients_feedback_wizard" />
                <input type="hidden" name="tab" value="wizards" />
                <?php $ListTable->display(); ?>
            </form>
        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';

            jQuery(document).ready(function(){
                var site_url = '<?php echo site_url();?>';

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
                        data: 'action=fw_filter_for_wizards&filter=' + filter,
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
                        var req_uri = "<?php echo preg_replace( '/&select_filter=[0-9]+|&change_filter=[a-z]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                        //if ( in_array() )
                        switch( jQuery( '#change_filter' ).val() ) {
                            case 'client':
                                window.location = req_uri + '&select_filter=' + jQuery( '#select_filter' ).val() + '&change_filter=client';
                                break;
                            case 'circle':
                                window.location = req_uri + '&select_filter=' + jQuery( '#select_filter' ).val() + '&change_filter=circle';
                                break;
                    }
                    }
                    return false;
                });

                jQuery( '#cancel_filter' ).click( function() {
                    var req_uri = "<?php echo preg_replace( '/&select_filter=[0-9]+|&change_filter=[a-z]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                    window.location = req_uri;
                    return false;
                });

            });

        </script>

    </div>
</div>