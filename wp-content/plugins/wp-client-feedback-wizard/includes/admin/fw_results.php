<?php

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_show_feedback_results' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_feedback_wizard&tab=results';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        //delete result wizard
        case 'delete':
            $ids = array();
            if ( isset( $_GET['result_id'] ) ) {
                check_admin_referer( 'wpc_result_delete' .  $_GET['result_id'] . get_current_user_id() );
                $ids = (array) $_REQUEST['result_id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Results', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            if ( count( $ids ) ) {
                foreach ( $ids as $result_id ) {
                    //delete result
                    $wpdb->delete( "{$wpdb->prefix}wpc_client_feedback_results", array( 'result_id'   => $result_id ) );
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;
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
        'wizard_name',
        'u.user_login',
    ) );
}

//filter
$where_filter ='';
if ( isset( $_GET['filter'] ) && 0 < (int)$_GET['filter'] ) {
    $where_filter = " AND client_id='" . mysqli_real_escape_string( (int)$_GET['filter'] ) . "'" ;
}

$order_by = 'time';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'wizard_name' :
            $order_by = 'wizard_name';
            break;
        case 'time' :
            $order_by = 'time';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Results_List_Table extends WP_List_Table {

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['result_id']
        );
    }

    function column_time( $item ) {
        return WPC()->date_format( $item['time'] );
    }

    function column_client( $item ) {
        return $item['client_login'];
    }

    function column_wizard_name( $item ) {

        $actions = array();

        $actions['view'] = '<a href="admin.php?page=wpclients_feedback_wizard&tab=view_result&result_id=' . $item['result_id'] . '" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Result?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_feedback_wizard&tab=results&action=delete&result_id=' . $item['result_id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_result_delete' . $item['result_id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<a href="admin.php?page=wpclients_feedback_wizard&tab=view_result&result_id=' . $item['result_id'] .'" title="view ' . $item['wizard_name'] . '">' . $item['wizard_name'] . '</a>', $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb;
            $all_clients = $wpdb->get_results(
                "SELECT client_id as id, u.user_login as login
                    FROM {$wpdb->prefix}wpc_client_feedback_results cfr
                    LEFT JOIN {$wpdb->users} u ON u.ID = cfr.client_id
                    GROUP BY client_id",
                ARRAY_A);

        ?>


        <div class="alignleft actions">
            <select name="filter" id="client_filter">
                <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?>&nbsp;</option>
                <?php
                if ( is_array( $all_clients ) && 0 < count( $all_clients ) )
                    foreach( $all_clients as $client ) {
                        $selected = ( isset( $_GET['filter'] ) && $client['id'] == $_GET['filter'] ) ? 'selected' : '';
                        echo '<option value="' . $client['id'] . '" ' . $selected . ' >' . $client['login'] . '</option>';
                    }
                ?>

            </select>
            <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="client_filter_button" style="float:left;" name="" />
            <a class="add-new-h2 cancel_filter" id="cancel_filter" style="float:left; cursor: pointer; margin-top: 4px;<?php if( !isset( $_GET['filter'] ) || 0 > $_GET['filter'] )  echo ' display: none;'; ?>" ><?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?><span class="ez_cancel_button" style="margin: 1px 0px 0px 7px;"></span></a>
        </div>
        <?php $this->search_box( __( 'Search Results', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Results_List_Table( array(
    'singular'  => __( 'Result', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Results', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));


$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_fw_results_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'wizard_name'          => 'wizard_name',
    'time'                 => 'time',
) );

$ListTable->set_bulk_actions(array(
    'delete'    => 'Delete',
));

$ListTable->set_columns(array(
    'wizard_name'              => __( 'Wizard Name', WPC_CLIENT_TEXT_DOMAIN ),
    'wizard_version'    => __( 'Wizard Version', WPC_CLIENT_TEXT_DOMAIN ),
    'client'            => WPC()->custom_titles['client']['s'],
    'time'              => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
));

$sql = "SELECT count( result_id )
    FROM {$wpdb->prefix}wpc_client_feedback_results cfr
    LEFT JOIN {$wpdb->users} u ON u.ID = cfr.client_id
    WHERE 1=1
    {$where_filter}
    {$where_clause}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT result_id, wizard_name, wizard_version, client_id, time, u.user_login as client_login
    FROM {$wpdb->prefix}wpc_client_feedback_results cfr
    LEFT JOIN {$wpdb->users} u ON u.ID = cfr.client_id
    WHERE 1=1
    {$where_filter}
    {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$items = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $items;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>
<style type="text/css">
    #items_form .search-box {
        float:left;
        padding: 2px 8px 0 0;
    }

    #items_form .search-box input[type="search"] {
        margin-top: 1px;
    }

    #items_form .search-box input[type="submit"] {
        margin-top: 1px;
    }
</style>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Result(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>


    <div id="wpc_container">

        <?php echo $this->gen_feedback_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_fw_results" style="position: relative;">
             <form method="get" id="items_form" name="items_form" >
                <input type="hidden" name="page" value="wpclients_feedback_wizard" />
                <input type="hidden" name="tab" value="results" />

                <?php $ListTable->display(); ?>
             </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function(){

                    //reassign file from Bulk Actions
                    jQuery( '#doaction2' ).click( function() {
                        var action = jQuery( 'select[name="action2"]' ).val() ;
                        jQuery( 'select[name="action"]' ).attr( 'value', action );
                        return true;
                    });

                    jQuery( '#cancel_filter' ).click( function() {
                        var req_uri = "<?php echo preg_replace( '/&filter=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                        window.location = req_uri;
                        return false;
                    });

                    //filter by client
                    jQuery( '#client_filter_button' ).click( function() {
                        if ( '-1' != jQuery( '#client_filter' ).val() ) {
                            window.location = 'admin.php?page=wpclients_feedback_wizard&tab=results&filter=' + jQuery( '#client_filter' ).val();
                        }
                        return false;
                    });
            });
        </script>
    </div>

</div>