<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients';
}

// actions
if ( isset( $_GET['action'] ) || isset( $_GET['action2'] ) ) {
    $action = ( isset( $_GET['action'] ) && -1 != $_GET['action'] ) ? $_GET['action'] : $_GET['action2'];

    switch ( $action ) {
        case 'assign_client':
            $ids = array();
            if ( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            $assigns = ( !empty( $_REQUEST['assigns'] ) ) ? explode( ',', $_REQUEST['assigns'] ) : array();

            foreach ( $ids as $file_id ) {
                $send_client_ids = array();
                if ( !empty( $_REQUEST['send_file_notification'] ) ) {
                    $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_files WHERE id = %d", $file_id ) );

                    $old_users = WPC()->assigns()->get_assign_data_by_object( 'file', $file_id, 'client' );
                    $old_users = array_merge( $old_users, WPC()->assigns()->get_assign_data_by_object( 'file_category', $cat_id, 'client' ) );

                    $old_circles = WPC()->assigns()->get_assign_data_by_object( 'file', $file_id, 'circle' );
                    $old_circles = array_merge( $old_circles, WPC()->assigns()->get_assign_data_by_object( 'file_category', $cat_id, 'circle' ) );
                    $old_circles = array_unique( $old_circles );
                    foreach( $old_circles as $group_id ) {
                        $old_users = array_merge( $old_users, WPC()->groups()->get_group_clients_id( $group_id ) );
                    }
                    $send_client_ids = array_diff( $assigns, $old_users );
                }

                WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', $assigns );

                if ( !empty( $_REQUEST['send_file_notification'] ) ) {
                    if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {
                        $data['id'] = $file_id;
                        $data['send_attach_file_user'] = $_REQUEST['send_attach_file_user'];

                        wp_schedule_single_event( time() - 1, 'send_email_notification_cron', array( $data, array(), $send_client_ids, uniqid() ) );
                    }
                }
            }

            WPC()->redirect( add_query_arg( 'msg', 'cla', $redirect ) );

            break;
        case 'assign_circle':
            $ids = array();
            if ( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            $assigns = ( !empty( $_REQUEST['assigns'] ) ) ? explode( ',', $_REQUEST['assigns'] ) : array();

            $client_ids = array();
            $send_group_ids = $assigns;
            foreach( $send_group_ids as $group_id ) {
                $client_ids = array_merge( $client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
            }
            $client_ids = array_unique( $client_ids );

            foreach ( $ids as $file_id ) {
                $send_client_ids = array();
                if ( !empty( $_REQUEST['send_file_notification'] ) ) {
                    $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_files WHERE id = %d", $file_id ) );

                    $old_users = WPC()->assigns()->get_assign_data_by_object( 'file', $file_id, 'client' );
                    $old_users = array_merge( $old_users, WPC()->assigns()->get_assign_data_by_object( 'file_category', $cat_id, 'client' ) );

                    $old_circles = WPC()->assigns()->get_assign_data_by_object( 'file', $file_id, 'circle' );
                    $old_circles = array_merge( $old_circles, WPC()->assigns()->get_assign_data_by_object( 'file_category', $cat_id, 'circle' ) );
                    $old_circles = array_unique( $old_circles );
                    foreach( $old_circles as $group_id ) {
                        $old_users = array_merge( $old_users, WPC()->groups()->get_group_clients_id( $group_id ) );
                    }

                    $send_client_ids = array_diff( $client_ids, $old_users );
                }

                WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $assigns );

                if ( !empty( $_REQUEST['send_file_notification'] ) ) {
                    if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {
                        $data['id'] = $file_id;
                        $data['send_attach_file_user'] = $_REQUEST['send_attach_file_user'];

                        wp_schedule_single_event( time() - 1, 'send_email_notification_cron', array( $data, array(), $send_client_ids, uniqid() ) );
                    }
                }
            }

            WPC()->redirect( add_query_arg( 'msg', 'cia', $redirect ) );

            break;
        /* reassign action */
        case 'reassign':

            if ( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ) );

                if ( 0 < count( $_REQUEST['item'] ) && isset( $_REQUEST['new_cat_id'] ) && 0 < $_REQUEST['new_cat_id'] ) {

                    foreach( $_REQUEST['item'] as $file_id ) {
                        //reassing files
                        $old_category_id = $wpdb->get_var( $wpdb->prepare(
                            "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE id = %d",
                            $file_id
                        ) );

                        WPC()->files()->reassign_files_from_category( $old_category_id, $_REQUEST['new_cat_id'], false, $file_id );

                        /*$wpdb->query( $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}wpc_client_files
                            SET cat_id = %d
                            WHERE id = %d",
                            $_REQUEST['new_cat_id'],
                            $file_id
                        ) );*/
                    }
                    WPC()->redirect( add_query_arg( 'msg', 'r', $redirect ) );
                }
            }

            WPC()->redirect( $redirect );

            break;

        /* delete action */
        case 'delete':

            $files_id = array();
            if ( isset( $_REQUEST['file_id'] ) ) {
                check_admin_referer( 'wpc_file_delete' .  $_REQUEST['file_id'] . get_current_user_id() );
                $files_id = ( is_array( $_REQUEST['file_id'] ) ) ? $_REQUEST['file_id'] : (array) $_REQUEST['file_id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $files_id = $_REQUEST['item'];
            }

            if ( count( $files_id ) && ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
                foreach( $files_id as $file_id ) {
                    WPC()->files()->delete_file( $file_id );
                }

                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
            }

            WPC()->redirect( $redirect );

            break;

    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
}




if ( !ini_get( 'safe_mode' ) ) {
    @set_time_limit(0);
}

$filter             = '';
$where_search       = '';
$where_circle_ctegories = '';
$where_filter       = '';
$where_owner_filter = '';
global $where_manager;
$where_manager      = '';
$manager_authors    = '';

$wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

if ( isset( $_GET['filter']  ) ) {
    $filter = $_GET['filter'];

    if ( '_wpc_admin' == $filter )
        $where_owner_filter .= ' AND f.page_id = 0';
    elseif ( '_wpc_for_admin' == $filter )
        $where_owner_filter .= ' AND f.page_id != 0';
    elseif ( '_wpc_your' == $filter )
        $where_owner_filter .= ' AND f.user_id = ' . get_current_user_id();

}

//filter
if ( isset( $_GET['change_filter'] ) ) {
    switch ( $_GET['change_filter'] ) {
        case 'tag':
            if ( isset( $_GET['filter_tag'] ) ) {
                //$data_tag = get_term_by( 'name', $_GET['filter_tag'], $taxonomy, ARRAY_A, $filter );
                //if ( $data_tag ) {
                $ids_files = get_objects_in_term( (int)$_GET['filter_tag'], 'wpc_file_tags' ) ;
                $where_filter .= " AND f.id IN('" . implode( "','", $ids_files ) . "')" ;
                //}
            }
            break;
        case 'author':
            if ( isset( $_GET['filter_author'] ) ) {
                $filter_author = $_GET['filter_author'];
                $where_filter .= " AND f.user_id='" . (int)$filter_author . "'" ;
            }
            break;
        case 'client_username':
            if ( isset( $_GET['filter_client_username'] ) ) {
                $filter_client = $_GET['filter_client_username'];
                $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $filter_client );
                $where_filter .= " AND (f.id IN('" . implode( "','", $client_files ) . "') OR f.user_id=" . (int)$filter_client . ")";
            }
            break;
        case 'client_business_name':
            if ( isset( $_GET['filter_client_business_name'] ) ) {
                $filter_client = $_GET['filter_client_business_name'];
                $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $filter_client );
                $where_filter .= " AND (f.id IN('" . implode( "','", $client_files ) . "') OR f.user_id=" . (int)$filter_client . ")";
            }
            break;
        case 'client_contact_name':
            if ( isset( $_GET['filter_client_contact_name'] ) ) {
                $filter_client = $_GET['filter_client_contact_name'];
                $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $filter_client );
                $where_filter .= " AND (f.id IN('" . implode( "','", $client_files ) . "') OR f.user_id=" . (int)$filter_client . ")";
            }
            break;
        case 'circle':
            if ( isset( $_GET['filter_circle'] ) ) {
                $filter_circle = $_GET['filter_circle'];
                $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $filter_circle );
                $where_filter .= " AND f.id IN('" . implode( "','", $circle_files ) . "')";
            }
            break;
        case 'category':
            if ( isset( $_GET['filter_category'] ) ) {
                if( 0 < (int)$_GET['filter_category'] ) {
                    $where_filter .= " AND f.cat_id = " . (int)$_GET['filter_category'];
                }
            }
            break;
    }
}

//search
if( !empty( $_GET['s'] ) ) {
    $where_search = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'f.name',
        'f.title',
        'f.description',
        'u.user_login',
        'fc.cat_name',
    ) );
}

//order
$order_by = 'f.time';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'file_title' :
            $order_by = 'f.title';
            break;
        case 'author' :
            $order_by = 'u.user_login';
            break;
        case 'categories' :
            $order_by = 'fc.cat_name';
            break;
        case 'date' :
            $order_by = 'f.time';
            break;
        case 'last_download' :
            $order_by = 'f.last_download';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

//information for manager
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
    $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
    foreach( $manager_circles as $c_id ) {
        $manager_clients = array_merge( $manager_clients, WPC()->groups()->get_group_clients_id( $c_id ) );
    }
    $manager_clients = array_unique( $manager_clients );

    foreach( $manager_clients as $client_id ) {
        $manager_circles = array_merge( $manager_circles, WPC()->groups()->get_client_groups_id( $client_id ) );
    }
    $manager_circles = array_unique( $manager_circles );

    $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $manager_clients );
    $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $manager_circles );
    $client_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $manager_clients );
    $circle_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $manager_circles );
    $cc_categories = array_unique( array_merge( $client_categories, $circle_categories ) );

    if( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
        $cc_categories_temp = $cc_categories;
        foreach( $cc_categories as $cat_id ) {
            $cc_categories_temp = array_merge( $cc_categories_temp, WPC()->files()->get_category_children_ids( $cat_id ) );
        }
        $cc_categories = array_unique( $cc_categories_temp );
    }

    $all_files = array_merge( $client_files, $circle_files );
    $all_files = array_unique( $all_files );

    if( count( $cc_categories ) ) {
        $where = " f.cat_id IN ( '" . implode( "','", $cc_categories ) . "' ) OR ";
    } else {
        $where = '';
    }

    if ( current_user_can( 'wpc_view_admin_managers_files' ) ) {
        $where_manager .= " AND (
            $where
            f.user_id = " . get_current_user_id() . " OR
            f.user_id IN('" . implode( "','", $manager_clients ) . "') OR
            f.page_id = 0 OR
            f.id IN('" . implode( "','", $all_files ) . "')
        )";
    } else {
        $where_manager .= " AND (
            $where
            f.user_id = " . get_current_user_id() . " OR
            f.user_id IN('" . implode( "','", $manager_clients ) . "') OR
            f.id IN('" . implode( "','", $all_files ) . "')
        )";
    }
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Clients_List_Table extends WP_List_Table {

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


    function column_file_title( $item ) {
        $download_link = get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $item['id'] ) . '&id=' . $item['id'];


        $file_type = explode( '.', $item['filename'] );
        $file_type = strtolower( end( $file_type ) );

        $file_title = ( isset( $item['title'] ) && '' != $item['title'] ) ? stripslashes( $item['title'] ) : stripslashes( $item['name'] );

        $additional_class = ( isset( $item['external'] ) && $item['external'] == '1' ) ? 'class = "wpc_external_file"' : '';

        $external_info = '';
        if( isset( $item['external'] ) && $item['external'] == '1' ) {
            $external_info = '<span class="external_info url">' . $item['filename'] . '</span><span class="external_info url_protect">' . $item['protect_url'] . '</span>';
        }

        $title_html = '<input type="hidden" id="assign_name_block_' . $item['id'] . '" value="' . $item['name'] . '" />
                    <span id="file_name_block_' . $item['id'] . '" ' . $additional_class . '>
                        <a href="' . $download_link . '" title="' .  __( 'Description', WPC_CLIENT_TEXT_DOMAIN )  . ': ' .  $item['description'] . '" data-title="' . base64_encode( $file_title ) . '" data-description="' . base64_encode( $item['description'] ) . '">'. $file_title .'</a>
                        <br>
                        <span class="description" style="font-size: 10px;" >' .  $item['name'] . '</span>' . $external_info .
            '</span>';

        //todo: add description
        $title_html = apply_filters( 'wp_client_file_sharing_title_html', $title_html, $item );

        $actions = array();
        $data_file_tags = wp_get_object_terms( $item['id'], 'wpc_file_tags', array( 'fields' => 'names') ) ;

        foreach ( $data_file_tags as $key => $tag ) {
            $data_file_tags[ $key ] = addslashes( $tag ) ;
        }

        $data_file_tags = '"' . implode( '","', $data_file_tags ) . '"' ;
        if ( '""' == $data_file_tags )
            $data_file_tags = '[]';
        else
            $data_file_tags = '[' . $data_file_tags . ']' ;
        $actions['edit'] = '<a class="various" href="#edit_file" title="" rel="' . $item['id'] . '" data-file_tags=\'' . $data_file_tags . '\' >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        if( ( isset( $wpc_file_sharing['google_doc_embed'] ) && 'yes' == $wpc_file_sharing['google_doc_embed'] && in_array( $file_type, array_keys( WPC()->files()->files_for_google_doc_view ) ) ) ||
            in_array( $file_type, WPC()->files()->files_for_regular_view ) ) {
            $actions['view'] = '<a href="' . get_admin_url() . 'admin.php?wpc_action=view&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $item['id'] ) . '&id=' . $item['id'] . '&d=false&t=' . $file_type .'" target="_blank" title="' . __( 'view', WPC_CLIENT_TEXT_DOMAIN ) . '" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        $actions['download'] = '<a href="' . get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $item['id'] ) . '&id=' . $item['id'] . '" title="' . __( 'download', WPC_CLIENT_TEXT_DOMAIN ) . ' \' ' . $item['name'] . '\'" >' . __( 'Download', WPC_CLIENT_TEXT_DOMAIN ). '</a>';


        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )  {
            $actions['delete'] = '<a onclick=\'return confirm("' .  __( 'Are you sure you want to delete this file?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_content&tab=files&action=delete&file_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_file_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        } else {
            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {

                //delete admin\managers files
                if ( current_user_can( 'wpc_view_admin_managers_files' ) && current_user_can( 'wpc_delete_admin_managers_files' ) ) {
                    $actions['delete'] = '<a onclick=\'return confirm("' .  __( 'Are you sure move to delete this file?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_content&tab=files&action=delete&file_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_file_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                } elseif ( 0 < $item['page_id'] || $item['user_id'] == get_current_user_id() ) {
                    $actions['delete'] = '<a onclick=\'return confirm("' .  __( 'Are you sure move to delete this file?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_content&tab=files&action=delete&file_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_file_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                }
            }
        }



        //take type from real file name\url
        $file_type = explode( '.', $item['title'] );

        if ( 1 == count( $file_type ) ) {
            //take type from file name
            $file_type = explode( '.', $item['name'] );
        }

        if ( 1 == count( $file_type ) ) {
            //take type from file title
            $file_type = explode( '.', $item['filename'] );
        }


        $file_type = strtolower( end( $file_type ) );
        $file_type = ( 6 >= strlen( $file_type ) ) ? $file_type : 'unknown';

        $file_icon = '<span style="width:50px;float:left;margin:0;padding:0;"><img width="40" height="40" src="' . WPC()->files()->get_fileicon( $file_type ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" /></span><span style="width:calc( 100% - 50px );float:left;margin:0;padding:0;">';

        //todo: add description
        $actions = apply_filters( 'wp_client_file_sharing_actions', $actions, $item );

        return sprintf('%1$s %2$s %3$s</span>', $file_icon, $title_html, $this->row_actions( $actions ) );
    }

    function column_order( $item ) {
        return '<input type="number" form="formformfromfrom123" name="file_order_' . $item['id'] . '" id="file_order_' . $item['id'] . '" style="width: 55px;" value="' . $item['order_id'] . '" onblur="update_order(' . $item['id'] . ')" />
                <br /><span class="wpc_ajax_loading" style="display: none;" id="order_' . $item['id'] . '"></span>';
    }

    function column_author( $item ) {
        if ( 0 == $item['user_id'] ) {
            return __( 'Synchronization', WPC_CLIENT_TEXT_DOMAIN );
        }

        return $item['username'];
    }

    function column_clients( $item ) {
        $id_array = WPC()->assigns()->get_assign_data_by_object( 'file', $item['id'], 'client' );
        $count = 0;
        if( is_array( $id_array ) ) {
            //select managers clients
            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                $manager_clients = WPC()->members()->get_all_clients_manager();
            }

            foreach ( $id_array as $client_id ) {
                if ( 0 < $client_id ) {
                    //if manager - skip not manager's clients
                    if ( isset( $manager_clients ) && !in_array( $client_id, $manager_clients ) )
                        continue;
                    if( !empty( $client_id ) ) {
                        $count++;
                    }
                }
            }
        }

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign to %s "%s"', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], $item['name'] )
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => $count
        );

        $html = WPC()->assigns()->assign_popup('client', 'wpclients_files', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_circles( $item ) {
        $id_array = WPC()->assigns()->get_assign_data_by_object( 'file', $item['id'], 'circle' );

        if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            $id_array = array_intersect( $manager_clients, $id_array );
        }

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to "%s"', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'], $item['name'] )
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => count( $id_array )
        );

        $html = WPC()->assigns()->assign_popup('circle', 'wpclients_files', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_categories( $item ) {
        return ( isset( $item['cat_name'] ) ) ? $item['cat_name'] : '';
    }

    function column_date( $item ) {
        return WPC()->date_format( $item['time'], 'date' ) . '<br />' . WPC()->date_format( $item['time'], 'time' );
    }

    function column_last_download( $item ) {
        if ( isset( $item['last_download'] ) && '' != $item['last_download'] ) {
            return WPC()->date_format( $item['last_download'], 'date' ) . '<br />' . WPC()->date_format( $item['last_download'], 'time' );
        }

        return '';
    }

    function bulk_actions( $which = '' ) {
        if ( is_null( $this->_actions ) ) {
            $no_new_actions = $this->_actions = $this->get_bulk_actions();
            // This filter can currently only be used to remove actions.
            $this->_actions = apply_filters( 'bulk_actions-' . $this->screen->id, $this->_actions );
            $this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
            $two = '';
        } else {
            $two = '2';
        }

        if ( empty( $this->_actions ) )
            return;

        echo "<select name='action$two'>\n";
        echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', WPC_CLIENT_TEXT_DOMAIN ) . "</option>\n";

        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

            echo "\t<option value='$name'$class>$title</option>\n";
        }

        echo "</select>\n";
        ?>

        <select name="new_cat_id" id="new_cat_id<?php echo $two?>" style="display: none;" >
            <?php WPC()->files()->render_category_list_items(); ?>
        </select>

        <?php
        submit_button( __( 'Apply', WPC_CLIENT_TEXT_DOMAIN ), 'action', false, false, array( 'id' => "doaction$two" ) );
        echo "\n";
    }

    function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            $all_filter = array(
                'category' => __( 'Category', WPC_CLIENT_TEXT_DOMAIN ),
                'tag' => __( 'Tag', WPC_CLIENT_TEXT_DOMAIN ),
                'author' => __( 'Author', WPC_CLIENT_TEXT_DOMAIN ),
                'client_username' => __( 'Client (Username)', WPC_CLIENT_TEXT_DOMAIN ),
                'client_business_name' => __( 'Client (Business Name)', WPC_CLIENT_TEXT_DOMAIN ),
                'client_contact_name' => __( 'Client (Contact Name)', WPC_CLIENT_TEXT_DOMAIN ),
                'circle' => WPC()->custom_titles['circle']['s']
            );
            if ( WPC()->flags['easy_mode'] ) {
                unset( $all_filter['category'] );
                unset( $all_filter['tag'] );
            }
            ?>
            <div class="alignleft actions">
                <select name="change_filter" id="change_filter" style="float: left;">
                    <option value="-1" <?php if( !isset( $_GET['change_filter'] ) || !array_key_exists( $_GET['change_filter'], $all_filter ) ) echo 'selected'; ?>><?php _e( 'Select Filter', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php

                    foreach ( $all_filter as $type_filter => $name_filter ) {
                        $selected = ( isset( $_GET['change_filter'] ) && $type_filter == $_GET['change_filter'] ) ? ' selected' : '' ;
                        echo '<option value="' . $type_filter . '"' . $selected . ' >' . $name_filter . '</option>';
                    }
                    ?>
                </select>

                <select name="select_filter" id="select_filter" style="float: left; <?php if ( !isset( $_GET['change_filter'] ) || !array_key_exists( $_GET['change_filter'], $all_filter ) ) echo " display: none;"; ?>">
                    <option value="-1"><?php _e( 'Select Category', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                </select>
                <span id="load_select_filter" style="float: left; margin: 3px 5px 0 0;"></span>
                <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="filtered" name="" style="float: left;" />
                <a class="add-new-h2 cancel_filter" id="cancel_filter" style="float:left; cursor: pointer; margin-top: 4px;<?php if( !isset( $_GET['filter_author']) && !isset( $_GET['filter_client_username']) && !isset( $_GET['filter_client_business_name']) && !isset( $_GET['filter_client_contact_name']) && !isset($_GET['filter_circle']) && !isset($_GET['filter_category']) && !isset($_GET['filter_tag']) ) echo ' display: none;'; ?>" ><?php _e( "Remove Filter", WPC_CLIENT_TEXT_DOMAIN ) ?><span class="ez_cancel_button" style="margin: 1px 0 0 7px;"></span></a>
            </div>
            <?php $this->search_box( __( 'Search Files' , WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }


}

$excluded_clients = "'" . implode( "','", WPC()->members()->get_excluded_clients() ) . "'";


$ListTable = new WPC_Clients_List_Table( array(
    'singular'  => __( 'File', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Files', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_files_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'file_title'        => 'file_title',
    'author'            => 'author',
    'categories'        => 'categories',
    'date'              => 'date',
    'last_download'     => 'last_download',
) );

$bulk_action_array = array();
if( !WPC()->flags['easy_mode'] ) {
    $bulk_action_array['reassign']    =  __( 'Reassign Category', WPC_CLIENT_TEXT_DOMAIN );
}
$bulk_action_array['delete'] = __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN );
$bulk_action_array['download_files']    = __( 'Download File(s)', WPC_CLIENT_TEXT_DOMAIN );
$bulk_action_array['assign_client']   = sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] );
$bulk_action_array['assign_circle']     = sprintf( __( 'Assign To %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] );

//todo: add description
$bulk_action_array = apply_filters( 'wp_client_file_sharing_bulk_actions', $bulk_action_array );

$ListTable->set_bulk_actions( $bulk_action_array );

$columns_array = array(
    'cb'                => '<input type="checkbox" />',
    'file_title'        => __( 'File Title', WPC_CLIENT_TEXT_DOMAIN ),
);

if( !WPC()->flags['easy_mode'] ) {
    $columns_array['categories']    = __( 'Category', WPC_CLIENT_TEXT_DOMAIN );
}

$columns_array = array_merge( $columns_array, array(
    'author'            => __( 'Author', WPC_CLIENT_TEXT_DOMAIN ),
    'clients'           => WPC()->custom_titles['client']['p'],
    'circles'           => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
    'date'              => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
    'last_download'     => __( 'Last Download', WPC_CLIENT_TEXT_DOMAIN ),
    'order'             => __( 'Order', WPC_CLIENT_TEXT_DOMAIN )
) );

$ListTable->set_columns($columns_array);

$items_count = $wpdb->get_var(
    "SELECT count(f.id)
    FROM {$wpdb->prefix}wpc_client_files f
    LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
    LEFT JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
    WHERE 1=1 " . $where_manager. " " . $where_search . " " . $where_owner_filter . " " . $where_filter

);


$count_all_files = $wpdb->get_var( "SELECT count(f.id) FROM {$wpdb->prefix}wpc_client_files f WHERE 1=1 $where_manager" );

$count_admin_files  = $wpdb->get_var(
    "SELECT count(f.id)
    FROM {$wpdb->prefix}wpc_client_files f
    LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
    LEFT JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
    WHERE f.page_id=0 AND f.page_id = 0 "
);

$count_for_admin = $count_all_files - $count_admin_files;

//count files for manager
$count_your = $wpdb->get_var(
    "SELECT count(f.id)
    FROM {$wpdb->prefix}wpc_client_files f
    LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
    LEFT JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
    WHERE f.user_id=" . get_current_user_id()

);


$files = $wpdb->get_results(
    "SELECT f.*, u.user_login as username, fc.cat_name as cat_name
    FROM {$wpdb->prefix}wpc_client_files f
    LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
    LEFT JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
    WHERE 1=1 ". $where_search . " " . $where_owner_filter . " " . $where_filter . " " . $where_manager . "
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", {$per_page}
    ", ARRAY_A );

$ListTable->prepare_items();
$ListTable->items               = $files;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );


//get not assign files in wpclient dir
$target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );

$all_files = $wpdb->get_col( "SELECT filename FROM {$wpdb->prefix}wpc_client_files" );
foreach( $all_files as $all_file ) {
    $file_type = explode( '.', $all_file );
    $file_type = strtolower( end( $file_type ) );

    if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
        $all_files[] = 'thumbnails_' . $all_file;
    }
}

$ftp_files = array();

$handle = opendir( $target_path );
while ( false !== ( $file = readdir( $handle ) ) ) {
    if ($file != "." && $file != "..") {
        if ( !is_dir( $target_path . $file ) ) {
            if ( !in_array( $file, $all_files ) && '.htaccess' != $file )
                $ftp_files[] = array (
                    'name' => $file,
                    'size' => WPC()->format_bytes( filesize( $target_path . $file ) ),
                );
        }
    }
}


//Display status message
if ( isset( $_GET['updated'] ) ) { ?>
    <div id="message" class="updated wpc_notice fade">
        <p><?php echo urldecode( $_GET['dmsg'] ); ?></p>
    </div>
<?php } ?>

<style>
    #new_form_panel table tr td:first-child {
        width: 120px;
    }
</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>
    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <?php
        if ( isset( $_GET['msg'] ) ) {
            switch( $_GET['msg'] ) {
                case 'r':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Files assigned to another category!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'd':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'File(s) are Deleted.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'ad':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The file has been added', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'up':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The file has been uploaded!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'as':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The file has been uploaded!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'sync':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'FTP synchronization was successful!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'm':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The file size more than allowed!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'er':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'er_as':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error assign the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'er_as2':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Some error with assigning permission for file.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'ne':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Error: File not exist!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'cerr':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Error: Category name is wrong!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
            }
        }
        ?>

        <div class="wpc_tab_container_block">

            <a class="add-new-h2 wpc_form_link" id="wpc_new"><?php _e( 'Upload New File', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            <a class="add-new-h2 wpc_form_link" id="wpc_assign_file"><?php _e( 'Assign File From FTP', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            <a class="add-new-h2 wpc_form_link" id="wpc_add_external"><?php _e( 'Add External File', WPC_CLIENT_TEXT_DOMAIN ) ?></a>

            <?php if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_files_sync' ) ) ) { ?>
                <a class="add-new-h2 wpc_form_link" id="wpc_synchronize"><?php _e( 'Synchronize with FTP', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            <?php } ?>

            <div id="new_form_panel" class="upload_file_panel">
                <form method="post" name="upload_file" id="upload_file" enctype="multipart/form-data">
                    <?php
                    if ( is_multisite() && !is_upload_space_available() ) {
                        echo '<p>' . __( 'Sorry, you have used all of your storage quota.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
                    } else {

                        if( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'html5' == $wpc_file_sharing['admin_uploader_type'] ) { ?>
                            <!--Flash uploader-->
                        <input type="hidden" name="wpc_action" id="wpc_action2" value="" />
                            <table class="">
                                <tr>
                                    <td>
                                        <label for="file_description"><?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>
                                        <textarea id="file_description" name="file_description" cols="50" rows="2"></textarea>
                                    </td>
                                </tr>
                                <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                    <td>
                                        <label for="file_cat_id"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>

                                        <select name="file_cat_id" id="file_cat_id" >
                                            <?php WPC()->files()->render_category_list_items(); ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_show_file_categories' ) ) { ?>
                                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                        <td>
                                            <label for="file_category_new"><?php _e( 'New Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                        </td>
                                        <td>
                                            <input type="text"  name="file_category_new" id="file_category_new" value="" />
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_file_tags' ) ) { ?>
                                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                        <td>
                                            <label for="file_tags"><?php _e( 'Tags', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                        </td>
                                        <td>
                                            <textarea id="file_tags" name="file_tags" rows="1"></textarea><br>
                                            <span class="description"><?php _e( 'Note: Press Enter for add tag.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td>
                                        <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                                    </td>
                                    <td>
                                        <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'data-input' => 'new_file_wpc_clients_1'
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_clients',
                                            'id'    => 'new_file_wpc_clients_1',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('client', 'wpclients_files', $link_array, $input_array, $additional_array );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                                    </td>
                                    <td>
                                        <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                            'data-input'    => 'new_file_wpc_circles_1',
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_circles',
                                            'id'    => 'new_file_wpc_circles_1',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('circle', 'wpclients_files', $link_array, $input_array, $additional_array );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <label><input type="checkbox" name="new_file_notify" id="new_file_notify1" value="1" <?php checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, true ) ?> /> <?php printf( __( 'Send notification to the assigned %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                        <br>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <label><input type="checkbox" name="attach_file_user" id="attach_file_user1" value="1" /> <?php printf( __( 'Attach uploaded file(s) to the email notification sent to %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                        <span class="description"><?php _e( '(size may be limited by email providers)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php _e( 'File(s)', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>
                                        <div id="queue"></div>
                                        <input id="file_upload" name="Filedata" type="file" multiple="multiple">
                                        <a style="position: relative; top: 8px;" href="javascript:jQuery('#file_upload').uploadifive('upload')"><?php _e( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                    </td>
                                </tr>
                            </table>


                            <script type="text/javascript">
                                <?php $timestamp = time();?>

                                var wpc_flash_uploader = {
                                    cancelled: '<?php echo esc_js( ' ' . __( "- Cancelled", WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                                    completed: '<?php echo esc_js( ' ' . __( "- Completed", WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                                    error_1: '<?php echo esc_js( __( "404 Error", WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                                    error_2: '<?php echo esc_js( __( "403 Forbidden", WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                                    error_3: '<?php echo esc_js( __( "Forbidden File Type", WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                                    error_4: '<?php echo esc_js( __( "File Too Large", WPC_CLIENT_TEXT_DOMAIN ) ); ?>',
                                    error_5: '<?php echo esc_js( __( "Unknown Error", WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                                };

                                jQuery( function() {
                                    var client_ids  = '';
                                    var group_ids   = '';
                                    var files;

                                    jQuery( '#file_upload' ).uploadifive({
                                        'auto'             : false,
                                        'sizeLimit'        : '<?php echo ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) ? $wpc_file_sharing['file_size_limit'] : '' ?>',
                                        'itemTemplate'      : '<div class="uploadifive-queue-item"><a class="close" href="#">X</a><div><span class="filename"></span><span class="fileinfo"></span></div><div class="progress"><div class="progress-bar"></div></div><textarea name="file_note[]" class="file_note" rows="3" cols="50" placeholder="<?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea></div>',
                                        'formData'         : {},
                                        'queueID'          : 'queue',
                                        'uploadScript'     : '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_client_admin_upload_files',
                                        'onUpload' : function( file ) {

                                            if( jQuery("#file_category_new").length > 0 && jQuery("#file_category_new").val().match( /[\/\:\*\?\"\<\>\\\|\%\$]/ ) ) {
                                                self.location.href="admin.php?page=wpclients_content&tab=files&msg=cerr";
                                                return false;
                                            }

                                            client_ids = jQuery('#new_file_wpc_clients_1').val();
                                            group_ids = jQuery('#new_file_wpc_circles_1').val();

                                            new_file_notify = 0;
                                            if ( 'checked' == jQuery( '#new_file_notify1' ).attr( 'checked') ) {
                                                new_file_notify = 1;
                                            }

                                            attach_file_user = 0;
                                            if ( 'checked' == jQuery( '#attach_file_user1' ).attr( 'checked') ) {
                                                attach_file_user = 1;
                                            }

                                            this.data( 'uploadifive' ).settings.formData = {
                                                'timestamp'         : '<?php echo $timestamp ?>',
                                                'token'             : '<?php echo md5( 'unique_salt' . $timestamp ) ?>',
                                                'new_file_notify'   : new_file_notify,
                                                'attach_file_user'  : attach_file_user,
                                                'file_cat_id'       : jQuery( '#file_cat_id' ).val(),
                                                'file_tags'         : jQuery( 'input[name="file_tags"]' ).val(),
                                                <?php echo ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_show_file_categories' ) ) ? "'file_category_new' : jQuery( '#file_category_new' ).val()," : '' ?>
                                                'wpc_clients'   : client_ids,
                                                'wpc_circles'   : group_ids
                                            };
                                        },
                                        'onBeforeUploadFile'          : function( file ) {
                                            var description = jQuery('#file_description').val();
                                            if( typeof file.queueItem != 'undefined' && file.queueItem.length ) {
                                                var personal_description = jQuery( file.queueItem[0] ).find( '.file_note' ).val();
                                                if( personal_description != '' ) {
                                                    description = personal_description;
                                                }
                                            }
                                            this.formData.file_description = description;
                                        },
                                        'onUploadComplete' : function( file, data ) {
                                            //files.push(file.name);
                                        },
                                        'onQueueComplete' : function( queueData ) {
                                            self.location.href="admin.php?page=wpclients_content&tab=files";
                                            return false;
                                        },
                                        'buttonText' : '<?php echo esc_js( __( "SELECT FILES", WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                                    });
                                });
                            </script>

                        <?php
                        }
                        elseif( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'plupload' == $wpc_file_sharing['admin_uploader_type'] ) {

                        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
                        $max_filesize = ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) ? $wpc_file_sharing['file_size_limit'] : '0'; ?>
                            <!--Plupload uploader-->
                        <input type="hidden" name="wpc_action" id="wpc_action2" value="" />
                            <table>
                                <tr>
                                    <td>
                                        <label for="file_description"><?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>
                                        <textarea id="file_description" name="file_description" cols="50" rows="2"></textarea>
                                    </td>
                                </tr>
                                <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                    <td>
                                        <label for="file_cat_id"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>

                                        <select name="file_cat_id" id="file_cat_id" >
                                            <?php WPC()->files()->render_category_list_items(); ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_show_file_categories' ) ) { ?>
                                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                        <td>
                                            <label for="file_category_new"><?php _e( 'New Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                        </td>
                                        <td>
                                            <input type="text"  name="file_category_new" id="file_category_new" value="" />
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_file_tags' ) ) { ?>
                                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                        <td>
                                            <label for="file_tags"><?php _e( 'Tags', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                        </td>
                                        <td>
                                            <textarea id="file_tags" name="file_tags" rows="1"></textarea>
                                            <span class="description"><?php _e( 'Note: Press Enter for add tag.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td>
                                        <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                                    </td>
                                    <td>
                                        <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'data-input'    => 'new_file_wpc_clients_1',
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_clients',
                                            'id'    => 'new_file_wpc_clients_1',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('client', 'wpclients_files', $link_array, $input_array, $additional_array );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                                    </td>
                                    <td>
                                        <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                            'data-input'    => 'new_file_wpc_circles_1',
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_circles',
                                            'id'    => 'new_file_wpc_circles_1',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('circle', 'wpclients_files', $link_array, $input_array, $additional_array );
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <label><input type="checkbox" name="new_file_notify" id="new_file_notify1" value="1" <?php checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, true ) ?> /> <?php printf( __( 'Send notification to the assigned %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                        <br>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <label><input type="checkbox" name="attach_file_user" id="attach_file_user1" value="1" /> <?php printf( __( 'Attach uploaded file(s) to the email notification sent to %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                        <span class="description"><?php _e( '(size may be limited by email providers)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label><?php _e( 'File(s)', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>

                                        <div id="queue">
                                            <p><?php _e( "Your browser doesn't have Flash, Silverlight or HTML5 support.", WPC_CLIENT_TEXT_DOMAIN ) ?></p>
                                        </div>
                                    </td>
                                </tr>
                            </table>


                            <script type="text/javascript">
                                <?php $timestamp = time();?>

                                jQuery( function() {
                                    var client_ids  = '';
                                    var group_ids   = '';
                                    var new_file_notify = 0;
                                    var attach_file_user = 0;
                                    var file_category_new = '';
                                    var file_cat_id = '';
                                    var file_tags = '';

                                    //file upload
                                    jQuery("#queue").pluploadQueue({
                                        // General settings
                                        runtimes : 'html5,browserplus,silverlight,flash,gears,html4',
                                        //runtimes : 'html5,browserplus,silverlight,flash,gears,html4',
                                        url : '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_client_admin_plupload_upload_files',

                                        chunk_size : '<?php echo apply_filters( 'wpc_client_plupload_chunk_size', '9mb' ) ?>',
                                        rename : true,
                                        dragdrop: true,
                                        max_retries : 3,
                                        filters : {
                                            <?php if( isset( $max_filesize ) && !empty( $max_filesize ) ) { ?>
                                            // Maximum file size
                                            max_file_size : '<?php echo $max_filesize ?>kb'
                                            <?php } ?>
                                            // Specify what files to browse for
                                            /* mime_types: [
                                                 {title : "Image files", extensions : "jpg,gif,png"},
                                                 {title : "Zip files", extensions : "zip"}
                                             ] */
                                        },
                                        init : {
                                            FilesAdded: function(uploader, files) {
                                                for( key in files ) {
                                                    jQuery( '#' + files[ key ].id ).append('<textarea name="note[]" id="note_' + files[ key ].id + '" class="note_field" rows="3" cols="50" placeholder="<?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea>');
                                                }
                                            },
                                            BeforeUpload: function(uploader, file) {
                                                // Called right before the upload for a given file starts, can be used to cancel it if required
                                                if( jQuery("#file_category_new").length > 0 && jQuery("#file_category_new").val().match( /[\/\:\*\?\"\<\>\\\|\%\$]/ ) ) {
                                                    self.location.href="admin.php?page=wpclients_content&tab=files&msg=cerr";
                                                    return false;
                                                }

                                                client_ids = jQuery('#new_file_wpc_clients_1').val();
                                                group_ids = jQuery('#new_file_wpc_circles_1').val();
                                                description = jQuery('#file_description').val();
                                                var personal_description = jQuery('#note_' + file.id).val();
                                                if( personal_description != '' ) {
                                                    description = personal_description;
                                                }

                                                if ( 'checked' == jQuery( '#new_file_notify1' ).attr( 'checked') ) {
                                                    new_file_notify = 1;
                                                }

                                                if ( 'checked' == jQuery( '#attach_file_user1' ).attr( 'checked') ) {
                                                    attach_file_user = 1;
                                                }

                                                if( jQuery( '#file_category_new' ).length > 0 ) {
                                                    file_category_new = jQuery( '#file_category_new' ).val();
                                                }

                                                file_cat_id = jQuery( '#file_cat_id' ).val();
                                                file_tags = jQuery( 'input[name="file_tags"]' ).val();

                                                if( '' != file_tags && 'undefined' != typeof( file_tags ) && file_tags.length > 0 )
                                                    file_tags = '&file_tags=' + file_tags ;
                                                else
                                                    file_tags = '';


                                                //file_tags = file_tags.replace( /\"/g, "'" );
                                                uploader.settings.url = '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_client_admin_plupload_upload_files&file_category_new=' + encodeURIComponent( file_category_new ) + '&file_cat_id=' + file_cat_id + file_tags + '&wpc_clients=' + encodeURIComponent( client_ids ) + '&wpc_circles=' + encodeURIComponent( group_ids ) + '&new_file_notify=' + new_file_notify + '&attach_file_user=' + attach_file_user + '&file_description=' + encodeURIComponent( description );
                                            },
                                            UploadComplete: function(up, files) {
                                                // Called when all files are either uploaded or failed
                                                self.location.href="admin.php?page=wpclients_content&tab=files";
                                                return false;
                                            }
                                        },
                                        // Flash settings
                                        flash_swf_url : '<?php echo WPC()->plugin_url ?>js/plupload/Moxie.swf',

                                        // Silverlight settings
                                        silverlight_xap_url : '<?php echo WPC()->plugin_url ?>js/plupload/Moxie.xap'
                                    });

                                    plupload.addI18n({
                                        "Stop Upload":"<?php echo esc_js( __( 'Stop Upload', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Upload URL might be wrong or doesn't exist.":"<?php echo esc_js( __( 'Upload URL might be wrong or doesn\'t exist.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "tb":"<?php echo esc_js( __( 'tb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Size":"<?php echo esc_js( __( 'Size', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Close":"<?php echo esc_js( __( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Init error.":"<?php echo esc_js( __( 'Init error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Add files to the upload queue and click the start button.":"<?php echo esc_js( __( 'Add files to the upload queue and click the start button.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Filename":"<?php echo esc_js( __( 'Filename', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Image format either wrong or not supported.":"<?php echo esc_js( __( 'Image format either wrong or not supported.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Status":"<?php echo esc_js( __( 'Status', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "HTTP Error.":"<?php echo esc_js( __( 'HTTP Error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Start Upload":"<?php echo esc_js( __( 'Start Upload', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "mb":"<?php echo esc_js( __( 'mb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "kb":"<?php echo esc_js( __( 'kb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Duplicate file error.":"<?php echo esc_js( __( 'Duplicate file error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "File size error.":"<?php echo esc_js( __( 'File size error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "N/A":"<?php echo esc_js( __( 'N/A', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "gb":"<?php echo esc_js( __( 'gb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Error: Invalid file extension:":"<?php echo esc_js( __( 'Error: Invalid file extension:', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Select files":"<?php echo esc_js( __( 'Select files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "%s already present in the queue.":"<?php echo esc_js( __( '%s already present in the queue.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "File: %s":"<?php echo esc_js( __( 'File: %s', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "b":"<?php echo esc_js( __( 'b', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Uploaded %d/%d files":"<?php echo esc_js( __( 'Uploaded %d/%d files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Upload element accepts only %d file(s) at a time. Extra files were stripped.":"<?php echo esc_js( __( 'Upload element accepts only %d file(s) at a time. Extra files were stripped.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "%d files queued":"<?php echo esc_js( __( '%d files queued', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "File: %s, size: %d, max file size: %d":"<?php echo esc_js( __( 'File: %s, size: %d, max file size: %d', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Drag files here.":"<?php echo esc_js( __( 'Drag files here.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Runtime ran out of available memory.":"<?php echo esc_js( __( 'Runtime ran out of available memory.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "File count error.":"<?php echo esc_js( __( 'File count error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "File extension error.":"<?php echo esc_js( __( 'File extension error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Error: File too large:":"<?php echo esc_js( __( 'Error: File too large:', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                                        "Add Files":"<?php echo esc_js( __( 'Add Files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>"
                                    });

                                });
                            </script>

                        <?php }
                        else { ?>
                            <!--Regular uploader-->
                        <input type="hidden" name="wpc_action" value="upload_file" />
                            <table>
                                <tr>
                                    <td>
                                        <label for="file"><?php _e( 'File', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>
                                        <input type="file" name="file" id="file" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="file_title"><?php _e( 'File Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>
                                        <input type="text" name="file_title" id="file_title" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="file_description"><?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>
                                        <textarea cols="50" rows="2" name="file_description" id="file_description"></textarea>
                                    </td>
                                </tr>
                                <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                    <td>
                                        <label for="file_cat_id"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                    </td>
                                    <td>

                                        <select name="file_cat_id" id="file_cat_id">
                                            <?php WPC()->files()->render_category_list_items(); ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_show_file_categories' ) ) { ?>
                                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                        <td>
                                            <label for="file_category_new"><?php _e( 'New Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                        </td>
                                        <td>
                                            <input type="text" name="file_category_new" id="file_category_new" />
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_file_tags' ) ) { ?>
                                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                        <td>
                                            <label for="file_tags"><?php _e( 'Tags', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                        </td>
                                        <td>
                                            <textarea id="file_tags" name="file_tags" rows="1"></textarea>
                                            <div class="description"><?php _e( 'Note: Press Enter for add tag.', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <td>
                                        <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                                    </td>
                                    <td>
                                        <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                            'data-input' => 'new_file_wpc_clients_1'
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_clients',
                                            'id'    => 'new_file_wpc_clients_1',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('client', 'wpclients_files', $link_array, $input_array, $additional_array );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                                    </td>
                                    <td>
                                        <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                            'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                            'data-input' => 'new_file_wpc_circles_1'
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_circles',
                                            'id'    => 'new_file_wpc_circles_1',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );
                                        WPC()->assigns()->assign_popup('circle', 'wpclients_files', $link_array, $input_array, $additional_array );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <label><input type="checkbox" name="new_file_notify" id="new_file_notify1" value="1" <?php checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, true ) ?> /> <?php printf( __( 'Send notification to the assigned %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                        <br>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                    </td>
                                    <td>
                                        <label><input type="checkbox" name="attach_file_user" id="attach_file_user2" value="1" /> <?php printf( __( 'Attach uploaded file(s) to the email notification sent to %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                        <span class="description"><?php _e( '(size may be limited by email providers)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <br><br>
                                    </td>
                                </tr>


                            </table>
                        <input type="submit" class='button-primary' id="upload_1" value="<?php _e( 'Upload File', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                            <?php
                        }
                    }
                    ?>
                </form>
            </div>

            <div id="add_external_file_panel" class="upload_file_panel">
                <form method="post" name="upload_file" id="upload_file2">
                    <input type="hidden" name="wpc_action" value="upload_file" />
                    <table>
                        <tr>
                            <td style="width: 120px;">
                                <label for="file_name"><?php _e( 'File Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="file_name" id="file_name" />
                                <span class="description"><?php _e( 'ex. file.zip', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="file_url"><?php _e( 'File URL', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="file_url" id="file_url" />
                                <span class="description"><?php _e( 'ex. http://www.site.com/file.zip', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr class="file_protect_url_content">
                            <td>
                            </td>
                            <td>
                                <input type="checkbox" name="file_protect_url" id="file_protect_url" value="1" />
                                <b><label for="file_protect_url"><?php _e( 'Protect URL', WPC_CLIENT_TEXT_DOMAIN ) ?></label></b>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <span class="description"><?php _e( 'May not work with some URLs', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="file_title2"><?php _e( 'File Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="file_title2" id="file_title2" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="file_description2"><?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <textarea cols="50" rows="2" name="file_description2" id="file_description2"></textarea>
                            </td>
                        </tr>
                        <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                            <td>
                                <label for="file_cat_id_2"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="file_cat_id" id="file_cat_id_2">
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                        <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_show_file_categories' ) ) { ?>
                            <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                <td>
                                    <label for="file_category_new_2"><?php _e( 'New Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                </td>
                                <td>
                                    <input type="text" name="file_category_new" id="file_category_new_2" />
                                </td>
                            </tr>
                        <?php } ?>

                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'data-input' => 'new_file_wpc_clients_2'
                                );
                                $input_array = array(
                                    'name'  => 'wpc_clients',
                                    'id'    => 'new_file_wpc_clients_2',
                                    'value' => ''
                                );
                                $additional_array = array(
                                    'counter_value' => 0,
                                    'input_ref' => 'wpc_clients'
                                );
                                WPC()->assigns()->assign_popup('client', 'wpclients_files', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                    'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                    'data-input' => 'new_file_wpc_circles_2',
                                );
                                $input_array = array(
                                    'name'  => 'wpc_circles',
                                    'id'    => 'new_file_wpc_circles_2',
                                    'value' => ''
                                );
                                $additional_array = array(
                                    'counter_value' => 0,
                                    'input_ref' => 'wpc_circles'
                                );
                                WPC()->assigns()->assign_popup('circle', 'wpclients_files', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                            </td>
                            <td>
                                <label><input type="checkbox" name="new_file_notify" id="new_file_notify2" value="1" <?php checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, true ) ?> /> <?php printf( __( 'Send notification to the assigned %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                <br><br>
                            </td>
                        </tr>

                    </table>
                    <div class="save_button">
                        <input type="submit" class='button-primary' id="upload_2" value="<?php _e( 'Add External File', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>
            </div>

            <div id="assign_file_panel" class="upload_file_panel">
                <form method="post" name="upload_file" id="upload_file3">
                    <input type="hidden" name="wpc_action" value="upload_file" />
                    <h3 style="margin: 0;"><?php printf( __( '%s protects the files in this directory', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></h3>
                    <br>
                    <span class="description"><?php printf( __( 'To assign files, you should upload it by FTP into folder %s', WPC_CLIENT_TEXT_DOMAIN ), $target_path ) ?></span>
                    <table>
                        <tr>
                            <td style="width: 120px;">
                                <label for="ftp_selected_file"><?php _e( 'File', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <?php if ( 0 <  count( $ftp_files ) ) { ?>
                                    <select name="ftp_selected_files[]" id="ftp_selected_file" data-placeholder="<?php _e( 'Select Files', WPC_CLIENT_TEXT_DOMAIN ) ?>" multiple style="width: 300px;">
                                        <option value=""></option>
                                        <?php foreach ( $ftp_files as $ftp_file ) {
                                            echo '<option value="' . $ftp_file['name'] .'">'. $ftp_file['name'] .' (' . $ftp_file['size'] . ')</option>';
                                        } ?>
                                    </select>
                                <?php } else { ?>
                                    <select name="ftp_selected_file" id="ftp_selected_file" data-placeholder="<?php _e( '- No Files For Select -', WPC_CLIENT_TEXT_DOMAIN ) ?>" style="width: 300px;"></select>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="file_title3"><?php _e( 'File Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="file_title" id="file_title3" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="file_description3"><?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <textarea cols="50" rows="2" name="file_description" id="file_description3"></textarea>
                            </td>
                        </tr>
                        <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                            <td>
                                <label for="file_cat_id_3"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="file_cat_id" id="file_cat_id_3">
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                        <?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_show_file_categories' ) ) { ?>
                            <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                                <td>
                                    <label for="file_category_new_2"><?php _e( 'New Category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                                </td>
                                <td>
                                    <input type="text" name="file_category_new" id="file_category_new_2" />
                                </td>
                            </tr>
                        <?php } ?>

                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'data-input' => 'new_file_wpc_clients_3'
                                );
                                $input_array = array(
                                    'name'  => 'wpc_clients',
                                    'id'    => 'new_file_wpc_clients_3',
                                    'value' => ''
                                );
                                $additional_array = array(
                                    'counter_value' => 0,
                                    'input_ref' => 'wpc_clients'
                                );
                                WPC()->assigns()->assign_popup('client', 'wpclients_files', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                    'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                    'data-input' => 'new_file_wpc_circles_3'
                                );
                                $input_array = array(
                                    'name'  => 'wpc_circles',
                                    'id'    => 'new_file_wpc_circles_3',
                                    'value' => ''
                                );
                                $additional_array = array(
                                    'counter_value' => 0,
                                    'input_ref' => 'wpc_circles'
                                );
                                WPC()->assigns()->assign_popup('circle', 'wpclients_files', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                            </td>
                            <td>
                                <label><input type="checkbox" name="new_file_notify" id="new_file_notify3" value="1" <?php checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, true ) ?> /> <?php printf( __( 'Send notification to the assigned %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                <br>
                            </td>
                        </tr>

                        <tr>
                            <td>
                            </td>
                            <td>
                                <label><input type="checkbox" name="attach_file_user" id="attach_file_user3" value="1" /> <?php printf( __( 'Attach uploaded file(s) to the email notification sent to %s and associated %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></label>
                                <span class="description"><?php _e( '(size may be limited by email providers)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                <br><br>
                            </td>
                        </tr>

                    </table>
                    <div class="save_button">
                        <input type="submit" class='button-primary' id="upload_3" value="<?php _e( 'Assign File', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>
            </div>

            <?php if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_files_sync' ) ) ) { ?>
                <div id="synchronize_panel" class="upload_file_panel">
                    <form method="post" name="upload_file">
                        <input type="hidden" name="wpc_action" value="synchronize_with_ftp" />
                        <table>
                            <tr>
                                <td>
                                    <h3><?php printf( __( '%s protects the files in this directory %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], $target_path ) ?></h3>
                                    <span class="description" style="float: left;width: 100%;margin-bottom: 15px;"><?php _e( 'Click "Synchronize now" to synchronize database Files and File Categories with files and folders on your server', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    <div class="save_button">
                                        <input type="submit" class="button-primary" id="upload_4" value="<?php _e( 'Synchronize now', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            <?php } ?>

            <div class="wpc_clear"></div>

            <ul class="subsubsub">
                <li class="all"><a class="<?php echo ( '' == $filter ) ? 'current' : '' ?>" href="admin.php?page=wpclients_content&tab=files"  ><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo $count_all_files ?>)</span></a> |</li>

                <?php if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) { ?>
                    <li class="image"><a class="<?php echo ( '_wpc_your' == $filter ) ? 'current' : '' ?>" href="admin.php?page=wpclients_content&tab=files&filter=_wpc_your"><?php _e( 'Your files', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo ( 0 < $count_your ) ? $count_your : '0' ?>)</span></a></li>
                <?php } else { ?>
                    <li class="image"><a class="<?php echo ( '_wpc_admin' == $filter ) ? 'current' : '' ?>" href="admin.php?page=wpclients_content&tab=files&filter=_wpc_admin"><?php _e( 'Admin files', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo ( 0 < $count_admin_files ) ? $count_admin_files : '0' ?>)</span></a> |</li>
                    <li class="image"><a class="<?php echo ( '_wpc_for_admin' == $filter ) ? 'current' : '' ?>" href="admin.php?page=wpclients_content&tab=files&filter=_wpc_for_admin"><?php _e( 'Files for Admin', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo ( 0 < $count_for_admin ) ? $count_for_admin : '0' ?>)</span></a> |</li>
                    <li class="image"><a class="<?php echo ( '_wpc_your' == $filter ) ? 'current' : '' ?>" href="admin.php?page=wpclients_content&tab=files&filter=_wpc_your"><?php _e( 'Your files', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo ( 0 < $count_your ) ? $count_your : '0' ?>)</span></a></li>
                <?php } ?>

            </ul>

            <!-- empty form for clear order feilds before submit -->
            <form action="" method="get" name="formformfromfrom123" id="formformfromfrom123">
            </form>

            <form action="" method="get" name="wpc_file_form" id="wpc_file_form" style="width: 100%;">
                <div style="display: none;">
                    <?php $link_array = array(
                        'title'         => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                        'text'          => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                        'data-input'    => 'bulk_assign_wpc_clients',
                    );
                    $input_array = array(
                        'name'  => 'bulk_assign_wpc_clients',
                        'id'    => 'bulk_assign_wpc_clients',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0,
                        'additional_classes'    => 'bulk_assign'
                    );
                    WPC()->assigns()->assign_popup( 'client', 'wpclients_files', $link_array, $input_array, $additional_array );

                    $link_array = array(
                        'title'         => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                        'text'          => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                        'data-input'    => 'bulk_assign_wpc_circles',
                    );
                    $input_array = array(
                        'name'  => 'bulk_assign_wpc_circles',
                        'id'    => 'bulk_assign_wpc_circles',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0,
                        'additional_classes'    => 'bulk_assign'
                    );
                    WPC()->assigns()->assign_popup( 'circle', 'wpclients_files', $link_array, $input_array, $additional_array ); ?>
                </div>
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="files" />
                <?php $ListTable->display(); ?>
            </form>
        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';
            var request_uri = "<?php echo $_SERVER['REQUEST_URI'];?>";

            //save order
            function update_order( file_id ) {
                file_order = jQuery( '#file_order_' + file_id ).val();
                jQuery( '#order_' + file_id ).css( 'display', 'inline-block' );
                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_files_update_order&file_id='+file_id+'&file_order=' + file_order,
                    success: function( json_data ){
                        jQuery( '#order_' + file_id ).css( 'display', 'none' );
                        jQuery( '#file_order_' + file_id ).val( json_data.my_value );
                    }
                });
            }

            jQuery( document ).ready( function() {

                jQuery( '.file_protect_url_content' ).hide();

                jQuery( '#file_url' ).change( function() {
                    var file_url = jQuery(this).val();

                    if( file_url.indexOf( 'http://' ) === 0 || file_url.indexOf( 'https://' ) === 0 ) {
                        jQuery( '.file_protect_url_content' ).show();
                    } else {
                        jQuery( '.file_protect_url_content' ).hide();
                        jQuery( '#file_protect_url' ).attr( 'checked', false );
                    }
                });

                jQuery( '#edit_file_url' ).change( function() {
                    var file_url = jQuery(this).val();

                    if( file_url.indexOf( 'http://' ) === 0 || file_url.indexOf( 'https://' ) === 0 ) {
                        jQuery( '.edit_file_protect_url_content' ).show();
                    } else {
                        jQuery( '.edit_file_protect_url_content' ).hide();
                        jQuery( '#edit_file_protect_url' ).attr( 'checked', false );
                    }
                });

                //remove extra fields before submit form
                jQuery( '#wpc_file_form' ).submit( function() {
                    jQuery( '.change_clients' ).remove();
                    jQuery( '.change_circles' ).remove();
                    return true;
                });

                jQuery( '#ftp_selected_file' ).chosen({
                    no_results_text: '<?php echo esc_js( __( 'No results matched', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                    allow_single_deselect: true
                });

                //Upload file form 2
                jQuery( '#upload_1' ).click( function() {
                    if ( '' == jQuery( '#file' ).val() ) {
                        alert("<?php echo esc_js( __( 'Please select file to upload.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>");
                        return false;
                    }
                });

                jQuery( '#upload_2' ).click( function() {
                    if ( '' == jQuery( '#file_url' ).val() ) {
                        alert("<?php echo esc_js( __( 'Please write file url.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>");
                        return false;
                    }
                });

                jQuery( '#upload_3' ).click( function() {
                    if ( !jQuery( '#ftp_selected_file' ).val() ) {
                        alert("<?php echo esc_js( __( 'Please select file to assign.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>");
                        return false;
                    }
                });


                jQuery( '#wpc_new' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '800px',
                    type            : 'inline',
                    href            : '#new_form_panel',
                    title           : '<?php echo esc_js( __('Upload File(s)', WPC_CLIENT_TEXT_DOMAIN) ); ?>',
                    afterLoad : function() {
                        jQuery('#file_tags').textext({
                            plugins : 'tags prompt focus autocomplete ajax arrow',
                            prompt : 'Add tag...',
                            ajax : {
                                url : '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_get_all_file_tags',
                                dataType : 'json',
                                cacheResults : true
                            }
                        });
                    }

                });


                jQuery( '#wpc_assign_file' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '700px',
                    type            : 'inline',
                    href            : '#assign_file_panel',
                    title           : '<?php echo esc_js( __( 'Assign File From FTP', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                });


                jQuery( '#wpc_add_external' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '650px',
                    type            : 'inline',
                    href            : '#add_external_file_panel',
                    title           : '<?php echo esc_js( __('Add an external file | From onsite or offsite server location', WPC_CLIENT_TEXT_DOMAIN) ); ?>'
                });


                jQuery( '#wpc_synchronize' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '700px',
                    type            : 'inline',
                    href            : '#synchronize_panel',
                    title           : '<?php echo esc_js( __( 'Synchronize files with FTP', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                });


                //reassign file from Bulk Actions
                jQuery( '#doaction' ).click( function() {
                    if ( 'reassign' == jQuery( 'select[name="action"]' ).val() ) {
                        jQuery( '#new_cat_id2' ).remove();
                        jQuery( 'select[name="action2"]' ).attr( 'name', 'aaaaaaaaaa' );
                    }
                    return true;
                });


                //reassign file from Bulk Actions
                jQuery( '#doaction2' ).click( function() {
                    if ( 'reassign' == jQuery( 'select[name="action2"]' ).val() ) {
                        jQuery( '#new_cat_id' ).remove();
                        jQuery( 'select[name="action"]' ).attr( 'name', 'aaaaaaaaaa' );
                    }
                    return true;
                });


                var post_id = [];
                var nonce = '';

                jQuery('#wpc_file_form').submit(function() {
                    if( jQuery('select[name="action"]').val() == 'assign_client' || jQuery('select[name="action2"]').val() == 'assign_client' ) {
                        post_id = [];
                        jQuery("input[name^=item]:checked").each(function() {
                            post_id.push( jQuery(this).val() );
                        });
                        nonce = jQuery('input[name=_wpnonce]').val();

                        if( post_id.length ) {
                            jQuery('.wpc_fancybox_link[data-input="bulk_assign_wpc_clients"]').trigger('click');
                        }

                        bulk_action_runned = true;
                        return false;
                    } else if( jQuery('select[name="action"]').val() == 'assign_circle' || jQuery('select[name="action2"]').val() == 'assign_circle' ) {
                        post_id = [];
                        jQuery("input[name^=item]:checked").each(function() {
                            post_id.push( jQuery(this).val() );
                        });
                        nonce = jQuery('input[name=_wpnonce]').val();

                        if( post_id.length ) {
                            jQuery('.wpc_fancybox_link[data-input="bulk_assign_wpc_circles"]').trigger('click');
                        }

                        bulk_action_runned = true;
                        return false;
                    }
                });

                jQuery( 'body' ).on( 'click', '.wpc_ok_popup', function(event) {
                    if ( bulk_action_runned ) {
                        if( post_id instanceof Array ) {
                            if( post_id.length ) {
                                var action = '';
                                var current_value = '';

                                jQuery( '#' + wpc_input_ref ).val( checkbox_session.join(',') ).trigger('change').triggerHandler( 'wpc_change_assign_value' );

                                var current_block = jQuery(this).parents('.wpc_assign_popup').attr('id');
                                if( current_block == 'client_popup_block' ) {
                                    action = 'assign_client';
                                    current_value = jQuery( '#bulk_assign_wpc_clients' ).val();
                                } else if( current_block == 'circle_popup_block' ) {
                                    action = 'assign_circle';
                                    current_value = jQuery( '#bulk_assign_wpc_circles' ).val();
                                }

                                var send_file_notification = jQuery( '.send_file_notification' ).is(':checked') ? '1' : '';
                                var send_attach_file_user = jQuery( '.send_attach_file_user' ).is(':checked') ? '1' : '';

                                var item_string = '';
                                post_id.forEach(function( item, key ) {
                                    item_string += '&item[]=' + item;
                                });

                                window.location = '<?php echo admin_url(); ?>admin.php?page=wpclients_content&tab=files&action=' + action + item_string + '&assigns=' + current_value + '&send_file_notification=' + send_file_notification + '&send_attach_file_user=' + send_attach_file_user + '&_wpnonce=' + nonce + '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name=_wp_http_referer]').val() );
                            }
                        } else {
                            window.location = '<?php echo admin_url(); ?>admin.php?page=wpclients_content&tab=files';
                        }
                        post_id = [];
                        nonce = '';
                        return false;
                    }
                });

                jQuery( "body" ).on( 'click', ".wpc_cancel_popup,.sb_close,.sb_background", function( e ) {
                    bulk_action_runned = false;
                });

                //show reassign cats
                jQuery( 'select[name="action"]' ).change( function() {
                    if ( 'reassign' == jQuery( this ).val() ) {
                        jQuery( '#new_cat_id' ).show();
                    } else {
                        jQuery( '#new_cat_id' ).hide();
                    }
                    return false;
                });

                //show reassign cats
                jQuery( 'select[name="action2"]' ).change( function() {
                    if ( 'reassign' == jQuery( this ).val() ) {
                        jQuery( '#new_cat_id2' ).show();
                    } else {
                        jQuery( '#new_cat_id2' ).hide();
                    }
                    return false;
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
                            data: 'action=wpc_get_options_filter_for_files&filter=' + filter,
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
                        var req_uri = "<?php echo preg_replace( '/&filter_category=[0-9]+|&filter_author=[0-9]+|&filter_tag=[0-9]+|&filter_client_username=[0-9]+|&filter_client_business_name=[0-9]+|&filter_client_contact_name=[0-9]+|&filter_circle=[0-9]+|&change_filter=[a-z_]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                        //if ( in_array() )
                        switch( jQuery( '#change_filter' ).val() ) {
                            case 'author':
                                window.location = req_uri + '&filter_author=' + jQuery( '#select_filter' ).val() + '&change_filter=author';
                                break;
                            case 'tag':
                                window.location = req_uri + '&filter_tag=' + jQuery( '#select_filter' ).val() + '&change_filter=tag';
                                break;
                            case 'client_username':
                                window.location = req_uri + '&filter_client_username=' + jQuery( '#select_filter' ).val() + '&change_filter=client_username';
                                break;
                            case 'client_business_name':
                                window.location = req_uri + '&filter_client_business_name=' + jQuery( '#select_filter' ).val() + '&change_filter=client_business_name';
                                break;
                            case 'client_contact_name':
                                window.location = req_uri + '&filter_client_contact_name=' + jQuery( '#select_filter' ).val() + '&change_filter=client_contact_name';
                                break;
                            case 'circle':
                                window.location = req_uri + '&filter_circle=' + jQuery( '#select_filter' ).val() + '&change_filter=circle';
                                break;
                            case 'category':
                                window.location = req_uri + '&filter_category=' + jQuery( '#select_filter' ).val() + '&change_filter=category';
                                break;
                        }
                    }
                    return false;
                });


                jQuery( '#cancel_filter' ).click( function() {
                    var req_uri = "<?php echo preg_replace( '/&filter_category=[0-9]+|&filter_author=[0-9]+|&filter_tag=[0-9]+|&filter_client_username=[0-9]+|&filter_client_business_name=[0-9]+|&filter_client_contact_name=[0-9]+|&filter_circle=[0-9]+|&change_filter=[a-z_]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                    window.location = req_uri;
                    return false;
                });

                function stripslashes( str ) {
                    str = str.replace(/\\'/g, '\'');
                    str = str.replace(/\\"/g, '"');
                    str = str.replace(/\\0/g, '\0');
                    str = str.replace(/\\\\/g, '\\');
                    return str;
                }


                //show edit file form
                jQuery('.various').each( function() {
                    var id = jQuery( this ).attr( 'rel' );

                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '500px',
                        type            : 'ajax',
                        dataType        : 'json',
                        href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                        ajax_data       : "action=wpc_file_edit_form&id=" + id,
                        setAjaxResponse : function( data ) {
                            jQuery( '.sb_lightbox_content_title' ).html( data.title );
                            jQuery( '.sb_lightbox_content_body' ).html( data.content );

                            var file_tags = jQuery('#span_for_file_tags').data('file_tags');
                            jQuery('#edit_file_tags').textext({
                                tagsItems : file_tags,
                                plugins : 'tags focus autocomplete ajax arrow prompt',
                                prompt : '<?php echo esc_js( __( 'Add tag...', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                                ajax : {
                                    url : '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_get_all_file_tags',
                                    dataType : 'json',
                                    cacheResults : true
                                }
                            });

                            if( jQuery( '.sb_lightbox').height() > jQuery( '#wpc_edit_file').height() + 70 ) {
                                jQuery( '.sb_lightbox' ).css('min-height', jQuery( '#wpc_edit_file').height() + 70 + 'px').animate({
                                    'height': jQuery('#wpc_edit_file').height()+70
                                },500);
                            }
                        }
                    });
                });

                // AJAX - update file data
                jQuery('body').on('click', '#update_file', function() {
                    file_id     = jQuery( '#edit_file_id' ).val();
                    title       = jQuery( '#edit_file_title' ).val();
                    description = jQuery( '#edit_file_description' ).val();
                    var category = jQuery( '#edit_file_cat_id' ).val();
                    var category_name = jQuery( '#edit_file_cat_id' ).find('option[value="' + category + '"]').data('cat_name');

                    file_tags   = jQuery( 'input[name="edit_file_tags"]' ).val();

                    var external = ( jQuery( '#edit_external_file' ).val() == '1' ) ? true : false;
                    var additional_query = '';

                    if( external ) {
                        var url         = jQuery( '#edit_file_url' ).val();
                        var protect_url = ( jQuery( '#edit_file_protect_url:checked' ).length == 1 ) ? 1 : 0;

                        additional_query = '&url=' + url + '&protect_url=' + protect_url;
                    }

                    jQuery( 'body' ).css( 'cursor', 'wait' );

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_update_file_data&file_id=' + file_id + '&title=' + jQuery.base64Encode( title ) + '&file_tags=' + file_tags +'&description=' + jQuery.base64Encode( description ) +'&category=' + category + additional_query,
                        dataType: "json",
                        success: function( data ){
                            jQuery( 'body' ).css( 'cursor', 'default' );
                            if ( data.id ) {
                                jQuery( '#file_name_block_' + data.id + ' a' ).html( title );
                                jQuery( '#file_name_block_' + data.id + ' a' ).data( 'title', jQuery.base64Encode( title ) );
                                jQuery( '#file_name_block_' + data.id + ' a' ).data( 'description', jQuery.base64Encode( description ) );
                                jQuery( '#file_name_block_' + data.id + ' a' ).attr( 'title', 'Description: ' + jQuery.base64Encode( description ) );

                                jQuery( '#file_name_block_' + data.id ).parents( 'tr' ).find( 'td.categories.column-categories' ).html( category_name );

                                if( data.file_tags )
                                    jQuery( 'a[rel="' + data.id + '"]'  ).data( 'file_tags', data.file_tags );

                                if( external ) {
                                    jQuery( '#file_name_block_' + data.id + ' span.external_info.url' ).html( data.url );
                                    jQuery( '#file_name_block_' + data.id + ' span.external_info.url_protect' ).html( data.protect_url );
                                }
                            }

                            jQuery('.various').shutter_box('close');
                        }
                    });
                });


                <?php if( !empty( $_GET['change_filter'] ) && isset( $_GET[ 'filter_' . $_GET['change_filter'] ] ) ) { ?>
                var filter = '<?php echo $_GET['change_filter']; ?>';
                jQuery( '#change_filter' ).val( filter );
                jQuery( '#select_filter' ).css( 'display', 'none' );
                jQuery( '#select_filter' ).html( '' );
                jQuery( '#load_select_filter' ).addClass( 'wpc_ajax_loading' );
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_get_options_filter_for_files&filter=' + filter,
                    dataType: 'html',
                    success: function( data ){
                        jQuery( '#select_filter' ).html( data );
                        jQuery( '#load_select_filter' ).removeClass( 'wpc_ajax_loading' );
                        jQuery( '#select_filter' ).css( 'display', 'block' );
                        jQuery( '#select_filter' ).val( '<?php echo $_GET[ 'filter_' . $_GET['change_filter'] ]; ?>' );
                    }
                });
                <?php } ?>

                if( typeof plupload != 'undefined' ) {
                    plupload.addI18n({
                        "Stop Upload":"<?php echo esc_js( __( 'Stop Upload', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Upload URL might be wrong or doesn't exist.":"<?php echo esc_js( __( 'Upload URL might be wrong or doesn\'t exist.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "tb":"<?php echo esc_js( __( 'tb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Size":"<?php echo esc_js( __( 'Size', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Close":"<?php echo esc_js( __( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Init error.":"<?php echo esc_js( __( 'Init error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Add files to the upload queue and click the start button.":"<?php echo esc_js( __( 'Add files to the upload queue and click the start button.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Filename":"<?php echo esc_js( __( 'Filename', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Image format either wrong or not supported.":"<?php echo esc_js( __( 'Image format either wrong or not supported.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Status":"<?php echo esc_js( __( 'Status', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "HTTP Error.":"<?php echo esc_js( __( 'HTTP Error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Start Upload":"<?php echo esc_js( __( 'Start Upload', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "mb":"<?php echo esc_js( __( 'mb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "kb":"<?php echo esc_js( __( 'kb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Duplicate file error.":"<?php echo esc_js( __( 'Duplicate file error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File size error.":"<?php echo esc_js( __( 'File size error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "N/A":"<?php echo esc_js( __( 'N/A', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "gb":"<?php echo esc_js( __( 'gb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Error: Invalid file extension:":"<?php echo esc_js( __( 'Error: Invalid file extension:', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Select files":"<?php echo esc_js( __( 'Select files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "%s already present in the queue.":"<?php echo esc_js( __( '%s already present in the queue.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File: %s":"<?php echo esc_js( __( 'File: %s', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "b":"<?php echo esc_js( __( 'b', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Uploaded %d/%d files":"<?php echo esc_js( __( 'Uploaded %d/%d files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Upload element accepts only %d file(s) at a time. Extra files were stripped.":"<?php echo esc_js( __( 'Upload element accepts only %d file(s) at a time. Extra files were stripped.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "%d files queued":"<?php echo esc_js( __( '%d files queued', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File: %s, size: %d, max file size: %d":"<?php echo esc_js( __( 'File: %s, size: %d, max file size: %d', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Drag files here.":"<?php echo esc_js( __( 'Drag files here.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Runtime ran out of available memory.":"<?php echo esc_js( __( 'Runtime ran out of available memory.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File count error.":"<?php echo esc_js( __( 'File count error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File extension error.":"<?php echo esc_js( __( 'File extension error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Error: File too large:":"<?php echo esc_js( __( 'Error: File too large:', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Add Files":"<?php echo esc_js( __( 'Add Files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>"
                    });
                }

            });
        </script>

    </div>