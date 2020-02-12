<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_invoices' ) ) {
    $this->redirect_available_page();
}

//save payment
if ( isset( $_POST['wpc_payment'] ) ) {
    $filter_array = array('page'=>'wpclients_invoicing', 'msg'=>'pa');
    if ( !empty( $_GET['filter_client'] ) ) {
        $filter_array['filter_client'] = $_GET['filter_client'];
    }
    if ( !empty( $_GET['filter_status'] ) ) {
        $filter_array['filter_status'] = $_GET['filter_status'];
    }
    $error = $this->save_payment( $_POST['wpc_payment'] );
    if ( '' === $error ) {
        WPC()->redirect( add_query_arg( $filter_array, get_admin_url() . 'admin.php' ) );
        exit;
    }
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_invoicing';
}

if ( isset( $_REQUEST['action'] ) ) {
    switch ( $_REQUEST['action'] ) {

        //delete
        case 'delete':
            $ids = array();
            if ( isset( $_GET['id'] ) ) {
                check_admin_referer( 'wpc_invoice_delete' .  $_GET['id'] . get_current_user_id() );
                $ids = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Invoices', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }
            if ( count( $ids ) ) {
                //delete invoice
                $this->delete_data( $ids );
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;

        // Mark as Read
        case 'mark':
            if ( isset( $_REQUEST['id'] )  ) {
                check_admin_referer( 'wpc_invoice_mark' . get_current_user_id() );
                //update last reminder time
                $wpdb->update( $wpdb->posts, array( 'post_status' => 'void' ), array( 'ID' => $_REQUEST['id'] ) ) ;
                update_post_meta( $_REQUEST['id'], 'wpc_inv_void_note', $_REQUEST['void_note'] )  ;

                WPC()->redirect( add_query_arg( 'msg', 'v', $redirect ) );
            } else {
                WPC()->redirect( $redirect );
            }
            exit;
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}

global $where_manager;
$where_manager = '';
//for manager
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $clients_id = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client');
    $groups_id = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );

    $clients_of_groups = array();

    foreach( $groups_id as $group_id ) {
        $clients_of_groups = array_merge( $clients_of_groups, WPC()->groups()->get_group_clients_id( $group_id ) );
    }

    $manager_clients = array_unique( array_merge( $clients_id, $clients_of_groups ) ) ;

    $where_manager = " AND coa.assign_id IN ('" . implode( "','", $manager_clients ) . "')" ;
}

$where_client = '';
//filter by clients
if ( isset( $_GET['filter_client']  ) ) {
    if ( is_numeric( $_GET['filter_client'] ) && 0 < $_GET['filter_client'] ) {
        $where_client = $wpdb->prepare( " AND coa.assign_id = '%s'", $_GET['filter_client'] ) ;
    }
}

//filter by status
$where_status = '';
if ( isset( $_GET['filter_status']  ) ) {
    $where_status = $wpdb->prepare( " AND p.post_status = '%s'", $_GET['filter_status'] ) ;
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'p.post_title',
        'p.post_content',
        'pm1.meta_value',
        'pm3.meta_value',
        'u.user_email',
        'u.display_name',
        'u.user_login',
        'um.meta_value',
    ) );
}

$order_by = 'p.post_date';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'status' :
            $order_by = 'p.post_status';
            break;
        case 'client' :
            $order_by = 'u.user_login';
            break;
        case 'number' :
            $order_by = 'pm3.meta_value';
            break;
        case 'total' :
            $order_by = 'pm1.meta_value * 1';
            break;
        case 'date' :
            $order_by = 'p.post_date';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Invoice_List_Table extends WP_List_Table {

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
            do_action( "manage_wpc_invoice_add_posts_columns", $column_name, $item );
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
        return apply_filters( "manage_wpc_invoice_posts_columns", $this->columns );
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

    /**
     * Generate the table navigation above or below the table
     */
    function display_tablenav( $which ) {
        if ( 'top' == $which || 'bottom' == $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        }
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions(); ?>
            </div>
        <?php
            $this->pagination( $which );
            $this->extra_tablenav( $which );
        ?>
            <br class="wpc_clear" />
        </div>
    <?php
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_total( $item ) {
        global $wpc_inv;

        $selected_curr = $item['currency'] ;
        $allow_partial = get_post_meta( $item['id'], 'wpc_inv_deposit', true );
        //$readonly = ( !$allow_partial ) ? 'readonly' : '';
        $text =
        '<span id="total_' . $item['id'] . '">' . $wpc_inv->get_currency( $item['total'], true, $selected_curr ) . '</span>';
        $text .= '<br />';
        $amount_paid = 0;
        if( 'partial' == $item['status'] ) {
            $amount_paid = $wpc_inv->get_amount_paid( $item['id'] );
            if (  0 < $amount_paid ) {
                $text .= '<span class="description">(<span id="total_amount_paid_' . $item['id'] . '">';
                $text .= $wpc_inv->get_currency( $amount_paid, true, $selected_curr );
                $text .= '</span>)</span>';
            }
        }

        $text .=
            '<span id="total_remaining_' . $item['id'] . '" style="display: none;">' .
            $wpc_inv->get_currency( $item['total'] - $amount_paid, true, $selected_curr ) .
            '<span class="real_amount" style="display:none;">' .
            ( $item['total'] - $amount_paid ) .
            '</span></span>';
        $text .= '<span id="allow_partial_' . $item['id'] . '" style="display: none;">' . $allow_partial . '</span>';
        return $text;
    }

    function column_client( $item ) {
        return $item['client_login'];
    }

    function column_type( $item ) {
        global $wpdb;
        $return = '';
        if ( 0 < (int)$item['parent_id'] ) {
            $item['parent_id'] = (int)$item['parent_id'];
            $parrent_type = get_post_meta( $item['id'], 'wpc_inv_parent_type', true ) ;
            $isset_parrent = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE ID = " . $item['parent_id'] ) ;
            if ( $isset_parrent && $parrent_type ) {
                switch ( $parrent_type ) {
                    case 'accum_inv':
                        $return = '<a href="admin.php?page=wpclients_invoicing&tab=accum_invoice_edit&id=' . $item['parent_id'] . '" target="_blank">' . __( 'Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                    break;
                    case 'repeat_inv':
                        $return = '<a href="admin.php?page=wpclients_invoicing&tab=repeat_invoice_edit&id=' . $item['parent_id'] . '" target="_blank">' . __( 'Recurring Profile', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                    break;
                }
            } elseif ( $parrent_type ) {
                switch ( $parrent_type ) {
                    case 'accum_inv':
                        $return = '<span>' . __( 'Accumulating Profile (deleted)', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
                    break;
                    case 'repeat_inv':
                        $return = '<span>' . __( 'Recurring Profile (deleted)', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
                    break;
                }
            } else {
                $return = __( 'Recurring', WPC_CLIENT_TEXT_DOMAIN );
            }
        } else {
            $return = __( 'One Time', WPC_CLIENT_TEXT_DOMAIN );
        }

        return $return;
    }

    function column_status( $item ) {
        global $wpc_inv;

        $all_statuses = array( 'open', 'sent', 'void', 'refunded', 'paid', 'partial', 'draft', 'pending', 'inprocess' ) ;

        $html = '<div><span id="status_' . $item['id'] . '">';

        if ( 'void' == $item['status'] ) {
            $html .= $wpc_inv->display_status_name( $item['status'] ) . WPC()->admin()->tooltip( get_post_meta( $item['id'], 'wpc_inv_void_note', true ) );
        } else {
            $html .= $wpc_inv->display_status_name( $item['status'] );
        }

        $html .= '</span><div class="status_invoice"></div></div><select name="status" class="change_status" data-id="' . $item['id'] . '" style="display: none;">';
        foreach ( $all_statuses as $status ) {
            $selected = ( $status == $item['status'] ) ? ' selected' : '' ;
            $html .= '<option value="' . $status . '"' . $selected . '>' . $wpc_inv->display_status_name( $status ) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    function column_date( $item ) {
        return WPC()->date_format( strtotime( $item['date'] ) );
    }

    function column_number( $item ) {
        global $wpdb;

        $actions = $hide_actions = array();

        $archive_client = WPC()->members()->get_excluded_clients( 'archive' );
        $not_show_status = array( 'new', 'particular' );
        $recurring_type = get_post_meta( $item['id'], 'wpc_inv_recurring_type', true ) ;
        $number = $item['number'];
        if ( ( !in_array( $item['client_id'], $archive_client ) || !in_array( $item['status'], $not_show_status ) ) ) {
            if ( 'paid' != $item['status'] && 'void' != $item['status'] && 'refunded' != $item['status'] && 'partial' != $item['status'] ) {
                $actions['edit']        = '<a href="admin.php?page=wpclients_invoicing&tab=invoice_edit&id=' . $item['id'] . '" title="Edit ' . $number . '" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                //$actions['mark']  = '<a onclick="return showNotice.warn();" href="admin.php?page=wpclients_invoicing&action=mark&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_invoice_mark' . $item['id'] . get_current_user_id() ) . '">' . __( 'Mark as Void', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                $hide_actions['mark']  = '<a href="#mark_as_void" rel="' . $item['id']  . '" class="void" title="Mark as Void">' . __( 'Mark as Void', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            } else {
                $actions['view'] = '<a href="admin.php?page=wpclients_invoicing&tab=invoice_edit&id=' . $item['id'] . '" title="View ' . $number . '" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
            if( !$recurring_type && !in_array( $item['status'], array( 'paid', 'void', 'refunded' ) ) && ( 0 < $item['total'] ) && ( ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_add_payment' ) ) ) ) {
                $hide_actions['add_payment'] = '<a href="#add_payment" data-currency="' . $item['currency']  . '" rel="' . $item['id']  . '" class="various" title="Add Payment ' . $number . '" >' . __( 'Add Payment', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
        }
        /*if ( 100 > strlen( $item['description'] ) ) {
            $div = wp_trim_words( $item['description'], 25 );
        } elseif ( 140 > strlen( $item['description'] ) ) {
            $div = wp_trim_words( $item['description'], 20 );
        } else {
            $div = wp_trim_words( $item['description'], 15 );
        } */


        $div = '<div>' . htmlspecialchars( $item['title'] ) . '</div>';
        $hide_actions['download'] = '<a href="admin.php?page=wpclients_invoicing&wpc_action=download_pdf&id=' . $item['id'] . '" title="Download PDF ' . $number . '" >' . __( 'Download&nbsp;PDF', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_invoices' ) ) {
            $hide_actions['delete'] = '<span class="delete"><a onclick=\'return confirm("' . __( 'Are you sure to delete this Invoice?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&action=delete&id=' .$item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_invoice_delete' . $item['id'] . get_current_user_id() ) .'">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';

            if( $item['parent_id'] ) {
                $parrent_status = $wpdb->get_var( "SELECT p.post_status
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'repeat_inv' )
                    WHERE p.post_type = 'wpc_invoice' AND p.id = " . (int)$item['parent_id'] );
                if( $parrent_status && 'expired' != $parrent_status && 'void' != $item['status'] && 'refunded' != $item['status'] ) {
                    $hide_actions['delete'] = '<span class="delete"><a onclick=\'return confirm("' . __( 'You cannot delete the Invoice created by an active Recurring Profile.', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="#">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
                }

            }
        }

        /*our_hook_
        hook_name: wpc_inv_more_actions_clients
        hook_title: Add more actions on Invoices page
        hook_description: Hook runs before display more actions on Invoices page.
        hook_type: filter
        hook_in: wp-client-invoicing
        hook_location invoices.php
        hook_param: string $error
        hook_since: 1.4.5
        */
        $hide_actions = apply_filters( 'wpc_inv_more_actions_clients', $hide_actions );

        if( count( $hide_actions ) ) {
            $actions['wpc_actions'] = WPC()->admin()->more_actions( $item['id'], __( 'Actions', WPC_CLIENT_TEXT_DOMAIN ) , $hide_actions );
        }

        return sprintf('%1$s %2$s', '<strong><a href="admin.php?page=wpclients_invoicing&tab=invoice_edit&id=' . $item['id'] . '" title="edit ' . $number . '">' . $number . '</a></strong>' . $div, $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb, $where_manager;

            $all_clients = $wpdb->get_results( "SELECT DISTINCT coa.assign_id as id, u.user_login as login
                FROM {$wpdb->prefix}wpc_client_objects_assigns coa
                LEFT JOIN {$wpdb->users} u ON ( u.ID = coa.assign_id )
                WHERE object_type='invoice' {$where_manager}",
            ARRAY_A ); ?>

            <div class="alignleft actions">
                <select name="filter_client" id="filter_client">
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php if ( is_array( $all_clients ) && 0 < count( $all_clients ) ) {
                        function wpc_inv_cmp( $a, $b ) {
                            return strnatcasecmp( $a['login'], $b['login'] );

                        }
                        usort( $all_clients, "wpc_inv_cmp" );

                        foreach( $all_clients as $client ) {
                            $selected = ( isset( $_GET['filter_client'] ) && $client['id'] == $_GET['filter_client'] ) ? 'selected' : '';
                            echo '<option value="' . $client['id'] . '" ' . $selected . ' >' .  $client['login'] . '</option>';
                        }
                    } ?>
                </select>
                <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="client_filter_button" name="" />
                <a class="add-new-h2" id="cancel_filter" <?php if( !isset( $_GET['filter_client'] ) || 0 > $_GET['filter_client'] ) { echo 'style="display: none;"'; } ?> ><?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?><span style="color: #BC0B0B;"> x </span></a>
            </div>

            <?php $this->search_box( __( 'Search Invoices', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Invoice_List_Table( array(
    'singular'  => __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Invoices', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_inv_invoices_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'client'           => 'client',
    'status'           => 'status',
    'number'           => 'number',
    'date'             => 'date',
    'total'            => 'total',
) );

$ListTable->set_bulk_actions(array(
    'delete'        => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
    'download_zip'  => __( 'Download PDF(s)', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'number'                => __( 'Invoice Number', WPC_CLIENT_TEXT_DOMAIN ),
    'client'                => WPC()->custom_titles['client']['s'],
    'total'                 => __( 'Total', WPC_CLIENT_TEXT_DOMAIN ),
    'status'                => __( 'Status', WPC_CLIENT_TEXT_DOMAIN ),
    'type'                  => __( 'Type', WPC_CLIENT_TEXT_DOMAIN ),
    'date'                  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
));

$wpdb->query("SET SESSION SQL_BIG_SELECTS=1");
$sql = "SELECT count( p.ID )
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
    LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'invoice' )
    LEFT JOIN {$wpdb->users} u ON ( u.ID = coa.assign_id )
    LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = 'wpc_cl_business_name' )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
    LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_number' )
    WHERE p.post_type='wpc_invoice'
        {$where_client}
        {$where_manager}
        {$where_status}
        {$where_clause}
    ";

$items_count = $wpdb->get_var( $sql );

$sql = "SELECT p.ID as id, p.post_title as title, p.post_date as date, coa.assign_id as client_id, p.post_status as status, u.user_login as client_login, pm1.meta_value as total, pm5.meta_value as parent_id, pm3.meta_value as number, pm4.meta_value as type, pm6.meta_value as currency
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
    LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'invoice' )
    LEFT JOIN {$wpdb->users} u ON ( u.ID = coa.assign_id )
    LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = 'wpc_cl_business_name' )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
    LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_number' )
    LEFT JOIN {$wpdb->postmeta} pm4 ON ( p.ID = pm4.post_id AND pm4.meta_key = 'wpc_inv_recurring_type' )
    LEFT JOIN {$wpdb->postmeta} pm5 ON ( p.ID = pm5.post_id AND pm5.meta_key = 'wpc_inv_parrent_id' )
    LEFT JOIN {$wpdb->postmeta} pm6 ON ( p.ID = pm6.post_id AND pm6.meta_key = 'wpc_inv_currency' )
    WHERE p.post_type='wpc_invoice'
        {$where_client}
        {$where_manager}
        {$where_status}
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$cols = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $cols;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<style>
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
        if ( '' == WPC()->get_slug( 'invoicing_page_id' ) ) {
            WPC()->admin()->get_install_page_notice();
        }
    ?>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo  '<div id="message" class="updated"><p>' . __( 'Invoice <strong>Created</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'as':
                echo  '<div id="message" class="updated"><p>' . __( 'Invoice <strong>Created & Sent</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'pa':
                echo  '<div id="message" class="updated"><p>' . __( 'Payment <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated"><p>' . __( 'Invoice <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'us':
                echo '<div id="message" class="updated"><p>' . __( 'Invoice <strong>Updated & Sent</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated"><p>' . __( 'Invoice(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'v':
                echo '<div id="message" class="updated"><p>' . __( 'Invoice Marked as Void', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>
    <div id="message" class="error" <?php echo ( !isset( $error ) || empty( $error ) ) ? 'style="display: none;" ' : '' ?> ><?php echo ( isset( $error ) ) ? $error : '' ?></div>

    <div class="clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_invoices">
            <?php $count_all = 0;
            $all_count_status = $wpdb->get_results(
                "SELECT post_status, count(p.ID) as count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'invoice' )
                WHERE post_type='wpc_invoice' {$where_manager} {$where_client} GROUP BY post_status",
            ARRAY_A );

            foreach ( $all_count_status as $status ) {
                $count_all += $status['count'];
            }

            $filter_status = ( isset( $_GET['filter_status'] )) ? (string)$_GET['filter_status'] : '';
            $filter_client = ( isset( $_GET['filter_client'] )) ? (string)$_GET['filter_client'] : ''; ?>

            <form action="" method="get" name="wpc_clients_form" id="wpc_clients_form">
                <?php if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_invoices' ) ) { ?>
                    <a href="admin.php?page=wpclients_invoicing&tab=invoice_edit" class="add-new-h2" style="float: left;">
                        <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </a>
                <?php } ?>
                <ul class="subsubsub" style="margin: -3px 0 0 20px;" >
                    <li class="all"><a class="<?php echo ( '' == $filter_status ) ? 'current' : '' ?>" href="admin.php?page=wpclients_invoicing<?php echo ( '' != $filter_client ) ? '&filter_client=' . $filter_client : '' ?>"  ><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo $count_all ?>)</span></a></li>
                    <?php foreach ( $all_count_status as $status ) {
                        $stat = strtolower( $status['post_status'] );
                        $class = ( $stat == $filter_status ) ? 'current' : '';
                        $params = ( '' != $filter_client ) ? '&filter_client=' . $filter_client : '';
                        echo ' | <li class="image"><a class="' . $class . '" href="admin.php?page=wpclients_invoicing' . $params . '&filter_status=' . $stat . '">' . $this->display_status_name( $stat ) . '<span class="count"> (' . $status['count'] . ')</span></a></li>';
                    } ?>
                </ul>
                <input type="hidden" name="page" value="wpclients_invoicing" />
                <?php $ListTable->display(); ?>
            </form>

            <div class="wpc_mark_as_void" id="mark_as_void" style="display: none;float:left;width:100%;">
                <form method="post" name="wpc_mark_as_void" id="wpc_mark_as_void" style="float:left;width:100%;">
                    <input type="hidden" name="id" id="wpc_void_inv_id" value="" />
                    <input type="hidden" name="action" value="mark" />
                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'wpc_invoice_mark' . get_current_user_id() ) ?>" />
                    <table style="float:left;width:100%;">
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Notes:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <textarea style="float:left;width:100%;" rows="5" name="void_note" id="wpc_void_note" ></textarea>
                                </label>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div style="clear: both; text-align: center;margin-top:10px;">
                        <input type="button" class='button-primary' id="save_mark_as_void" value="<?php _e( 'Mark as Void', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <input type="button" class='button' id="close_mark_as_void" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery(document).ready( function() {

        jQuery( '.status_invoice' ).click( function() {
            jQuery( this ).parent().css( 'display', 'none' );
            jQuery( this ).parent().next().css( 'display', 'block' );
        });

        jQuery( '.change_status' ).change( function() {
            var id = jQuery( this ).data( 'id' );
            var new_status = jQuery( this ).val();

            if( undefined !== new_status && new_status ) {
                jQuery.ajax({
                    type: 'POST',
                    dataType    : 'json',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=inv_change_status&id=' + id + '&new_status=' + new_status ,
                    success: function() {
                    }
                });
            }
            jQuery( this ).css( 'display', 'none' );
            jQuery( '#status_' + id ).text( jQuery( this ).children(':selected').text() );
            jQuery( this ).prev().css( 'display', 'block' );
        });


        jQuery( '#cancel_filter' ).click( function() {
            var req_uri = "<?php echo preg_replace( '/&filter_client=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
            window.location = req_uri;
            return false;
        });

        //filter by clients
        jQuery( '#client_filter_button' ).click( function() {
            if ( '-1' !== jQuery( '#filter_client' ).val() ) {
                var req_uri = "<?php echo preg_replace( '/&filter_client=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>&filter_client=" + jQuery( '#filter_client' ).val();
                window.location = req_uri;
            }
            return false;
        });


        jQuery('.various').each( function(){
            var id = jQuery( this ).attr( 'rel' );

            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '560px',
                type            : 'ajax',
                dataType        : 'json',
                href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                ajax_data       : "action=inv_get_invoice_data&id=" + id,
                setAjaxResponse : function( data ) {
                    jQuery( '.sb_lightbox_content_title' ).html( data.title );
                    jQuery( '.sb_lightbox_content_body' ).html( data.content );

                    jQuery( '#wpc_payment_date' ).datepicker({
                        dateFormat : 'mm/dd/yy'
                    });
                }
            });
        });

        //close Add Payment
        jQuery('body').on('click', '#close_add_payment', function() {
            jQuery('.various').shutter_box('close');
        });




        //check payment amount
        jQuery('body').on('change', '#wpc_payment_amount', function(e) {
            var val = jQuery(this).val();

            if ( val > jQuery( '#wpc_add_payment_total .amount' ).html() * 1 ) {
                jQuery( this ).parent().parent().attr( 'class', 'wpc_error' );
            } else {
                jQuery( this ).parent().parent().attr( 'class', '' );
            }
        });

        jQuery('body').on('keypress', '#wpc_payment_amount', function(e) {
            if ( e.which === 8 || e.which === 0 ) {
                return true;
            }

            if ( ( e.which >= 48 && e.which <= 57 ) || e.which === 44 || e.which === 46 ) {
                return true;
            }

            return false;
        });

        //check payment amount
       // jQuery( '#wpc_payment_amount' ).keyup( function( e ) {

    //            if ( val > jQuery( '#wpc_add_payment_total' ).html() * 1 ) {
    //                jQuery( '#wpc_payment_amount' ).parent().parent().attr( 'class', 'wpc_error' );
    //            } else {
    //                jQuery( '#wpc_payment_amount' ).parent().parent().attr( 'class', '' );
    //            }

    //        });

        //Save payment
        jQuery('body').on('click', '#save_add_payment', function() {
            var errors = 0;
            if ( '' === jQuery( "#wpc_payment_amount" ).val()
                    || jQuery( '#wpc_payment_amount' ).val() > jQuery( '#wpc_add_payment_total .amount' ).html() * 1 ) {
                errors = 1;

                jQuery( '#wpc_payment_amount' ).parent().parent().attr( 'class', 'wpc_error' );
            } else {
                jQuery( '#wpc_payment_amount' ).parent().parent().attr( 'class', '' );
            }

            if ( '' === jQuery( "#wpc_payment_date" ).val() ) {
                jQuery( '#wpc_payment_date' ).parent().parent().attr( 'class', 'wpc_error' );
                errors = 1;
            } else {
                jQuery( '#wpc_payment_date' ).parent().parent().attr( 'class', '' );
            }

            if ( '' === jQuery( "#wpc_payment_method" ).val() ) {
                jQuery( '#wpc_payment_method' ).parent().parent().attr( 'class', 'wpc_error' );
                errors = 1;
            } else {
                jQuery( '#wpc_payment_method' ).parent().parent().attr( 'class', '' );
            }

            if ( 0 === errors ) {
                jQuery( '#wpc_add_payment' ).submit();
            }
        });




        jQuery('.void').each( function(){
            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '520px',
                type            : 'inline',
                href            : '#mark_as_void',
                title           : '<?php _e( 'Mark as Void', WPC_CLIENT_TEXT_DOMAIN ) ?>',
                inlineBeforeLoad : function() {
                    var id = jQuery( this ).attr( 'rel' );
                    jQuery( '#wpc_void_inv_id' ).val( id );
                },
                onClose         : function() {
                    jQuery( '#wpc_void_inv_id' ).val( '' );
                }
            });
        });

        //close Mark as Void
        jQuery( '#close_mark_as_void' ).click( function() {
            jQuery('.void').shutter_box('close');
        });

        //save option void
        jQuery( '#save_mark_as_void' ).click( function() {
            jQuery( '#wpc_mark_as_void' ).submit();
        });

        // from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });
    });
</script>