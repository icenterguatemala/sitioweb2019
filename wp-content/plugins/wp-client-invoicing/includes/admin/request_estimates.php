<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_estimate_requests' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=request_estimates';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {

        //delete
        case 'delete':
            $ids = array();
            if ( isset( $_GET['id'] ) ) {
                check_admin_referer( 'wpc_request_estimate_delete' .  $_GET['id'] . get_current_user_id() );
                $ids = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }
            if ( count( $ids ) ) {
                //delete request estimate

                $this->delete_data( $ids );
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;

        // Convert to INV
        case 'convert':
            if ( isset( $_GET['id'] ) ) {
                check_admin_referer( 'wpc_r_estimate_convert_to' .  $_GET['id'] . get_current_user_id() );
                $convert_to = ( !empty( $_GET['to'] ) ) ? $_GET['to'] : '' ;
                $message = $this->convert_request_estimate( $_REQUEST['id'], 'convert', $convert_to );
                WPC()->redirect( add_query_arg( 'msg', $message, $redirect ) );
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
    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client');
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
    $where_status = $wpdb->prepare( " AND p.post_status = '%s'", $_GET['filter_status']) ;
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'p.post_title',
        'pm1.meta_value',
    ) );
}

$order_by = 'p.ID';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'status' :
            $order_by = 'p.post_status';
            break;
        case 'client' :
            $order_by = 'u.user_login';
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

class WPC_Request_Estimates_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();

    function __construct( $args = array() ) {
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

    function column_date( $item ) {
        return WPC()->date_format( strtotime( $item['date'] ) );
    }

    function column_total( $item ) {
        global $wpc_inv;
        $selected_curr = get_post_meta( $item['id'], 'wpc_inv_currency', true ) ;
        return $wpc_inv->get_currency( $item['total'], false, $selected_curr );
    }

    function column_client( $item ) {
        return $item['client_login'];
    }

    function column_status( $item ) {
        global $wpc_inv;
        return $wpc_inv->display_status_name( $item['status'] ) ;
    }

    function column_title( $item ) {
        $archive_client = WPC()->members()->get_excluded_clients( 'archive' );
        $title = $item['title'];

        if ( ( !in_array( $item['client_id'], $archive_client ) ) ) {
            $actions['edit'] = '<a href="admin.php?page=wpclients_invoicing&tab=request_estimate_edit&id=' . $item['id'] . '" title="' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . ' &quot;'  . $title . '&quot;" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            if ( in_array( $item['status'], array( 'new', 'waiting_admin' ) ) ) {
                $actions['convert_to_inv'] = '<a href="admin.php?page=wpclients_invoicing&tab=request_estimates&id=' . $item['id'] . '&action=convert&to=inv&_wpnonce=' . wp_create_nonce( 'wpc_r_estimate_convert_to' . $item['id'] . get_current_user_id() ) . '"  title="' . __( 'Convert to Invoice', WPC_CLIENT_TEXT_DOMAIN ) . ' &quot;'  . $title . '&quot;" >' . __( 'Convert to Invoice', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                $actions['convert_to_est'] = '<a href="admin.php?page=wpclients_invoicing&tab=request_estimates&id=' . $item['id'] . '&action=convert&to=est&_wpnonce=' . wp_create_nonce( 'wpc_r_estimate_convert_to' . $item['id'] . get_current_user_id() ) . '"  title="' . __( 'Convert to Invoice', WPC_CLIENT_TEXT_DOMAIN ) . ' &quot;'  . $title . '&quot;" >' . __( 'Convert to Estimate', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
        }
        /*$actions['download'] = '<a href="admin.php?page=wpclients_invoicing&wpc_action=download_pdf&id=' . $item['id'] . '" title="' . __( 'Download PDF', WPC_CLIENT_TEXT_DOMAIN ) . ' &quot;'  . $title . '&quot;" >' . __( 'Download&nbsp;PDF', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';*/

        if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_estimates' ) ) {
            $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Estimate Request?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&tab=request_estimates&action=delete&id=' .$item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_request_estimate_delete' . $item['id'] . get_current_user_id() ) .'">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        return sprintf('%1$s %2$s', '<strong><a href="admin.php?page=wpclients_invoicing&tab=request_estimate_edit&id=' . $item['id'] . '" title="' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . ' &quot;'  .  $title . '&quot;">'
                . $title . '</a></strong>', $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ) {

        if ( 'top' == $which ) {
            global $wpdb, $where_manager;
            $all_clients = $wpdb->get_col( "SELECT DISTINCT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns coa WHERE object_type='request_estimate' {$where_manager}" );
            ?>

            <div class="alignleft actions">
                <select name="filter_client" id="filter_client">
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    if ( is_array( $all_clients ) && 0 < count( $all_clients ) ) {
                        foreach( $all_clients as $client_id ) {
                            $selected = ( isset( $_GET['filter_client'] ) && $client_id == $_GET['filter_client'] ) ? 'selected' : '';
                            echo '<option value="' . $client_id . '" ' . $selected . ' >' .  get_userdata( $client_id )->user_login . '</option>';
                        }
                    }
                    ?>
                </select>
                <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="client_filter_button" name="" />
                <a class="add-new-h2" id="cancel_filter" <?php if( !isset( $_GET['filter_client'] ) || 0 > $_GET['filter_client'] ) { echo 'style="display: none;"'; } ?> ><?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?><span style="color: #BC0B0B;"> x </span></a>
            </div>

            <?php
            $this->search_box( __( 'Search Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_Request_Estimates_List_Table( array(
    'singular'  => __( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));


$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_inv_request_estimates_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'client'           => 'client',
    'status'           => 'status',
    'date'             => 'date',
    'total'            => 'total',
) );

$ListTable->set_bulk_actions(array(
    'delete'        => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'title'                 => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
    'status'                => __( 'Status', WPC_CLIENT_TEXT_DOMAIN ),
    'client'                => WPC()->custom_titles['client']['s'],
    'total'                 => __( 'Total', WPC_CLIENT_TEXT_DOMAIN ),
    'date'                  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
));


$sql = "SELECT count( p.ID )
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'r_est' )
    LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'request_estimate' )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total')
    WHERE p.post_type='wpc_invoice'
        {$where_client}
        {$where_manager}
        {$where_status}
        {$where_clause}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT
        p.ID as id,
        p.post_title as title,
        p.post_date as date,
        coa.assign_id as client_id,
        p.post_status as status,
        u.user_login as client_login,
        pm1.meta_value as total
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'r_est' )
    LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'request_estimate' )
    LEFT JOIN {$wpdb->users} u ON ( u.ID = coa.assign_id )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
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
    #wpc_form .search-box {
        float:left;
        padding: 2px 8px 0 0;
    }

    #wpc_form .search-box input[type="search"] {
        margin-top: 1px;
    }

    #wpc_form .search-box input[type="submit"] {
        margin-top: 1px;
    }
</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'rs':
                echo '<div id="message" class="updated"><p>' . __( 'Estimate Request <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'con':
                echo '<div id="message" class="updated"><p>' . __( 'Estimate Request <strong>Converted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated"><p>' . __( 'Estimate Request(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'ae':
                echo '<div id="message" class="error"><p>' . __( 'Error of Action.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;

        /*    case 'cs':
                 echo '<div class="wpc_message_update"><p>'
                    . __( 'Comment Successfully Sent.', WPC_CLIENT_TEXT_DOMAIN )
                    . '</p></div>';
                break;
            case 'ce':
                 echo '<div class="wpc_message_error"><p>'
                    . __( 'Comment is Empty.', WPC_CLIENT_TEXT_DOMAIN )
                    . '</p></div>';
                break;*/
        }
    }
    ?>

    <div class="clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_estimate_request" style="position: relative;">
            <?php
            global $wpdb;

            $count_all = 0;
            $all_count_status = $wpdb->get_results( "SELECT post_status, count(p.ID) as count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'r_est' )
                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'request_estimate' )
                WHERE post_type='wpc_invoice' {$where_manager} GROUP BY post_status", ARRAY_A );
            foreach ( $all_count_status as $status ) {
                $count_all += $status['count'];
            }

            $filter_status = ( isset($_GET['filter_status']) ) ? (string)$_GET['filter_status'] : '';
            $filter_client = ( isset($_GET['filter_client']) ) ? (string)$_GET['filter_client'] : '';
            ?>

            <ul class="subsubsub" style="margin: 0" >
                <li class="all"><a class="<?php echo ( '' == $filter_status ) ? 'current' : '' ?>" href="admin.php?page=wpclients_invoicing&tab=request_estimates<?php echo ( '' != $filter_client ) ? '&filter_client=' . $filter_client : '' ?>"  ><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo $count_all ?>)</span></a></li>
            <?php
                foreach ( $all_count_status as $status ) {
                    $stat = strtolower( $status['post_status'] );
                    $class = ( $stat == $filter_status ) ? 'current' : '';
                    $params = ( '' != $filter_client ) ? '&filter_client=' . $filter_client : '';
                    echo ' | <li class="image"><a class="' . $class . '" href="admin.php?page=wpclients_invoicing&tab=request_estimates' . $params . '&filter_status=' . $stat . '">' . sprintf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), $this->display_status_name( $stat ) ) . '<span class="count"> (' . $status['count'] . ')</span></a></li>';
                }
            ?>
            </ul>
            <form action="" method="get" name="wpc_form" id="wpc_form">
                <input type="hidden" name="page" value="wpclients_invoicing" />
                <input type="hidden" name="tab" value="request_estimates" />
                <?php $ListTable->display(); ?>
            </form>

        </div>
    </div>
</div>

<script type="text/javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery(document).ready(function(){
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

        //reassign file from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });

    });
</script>