<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_accum_invoices' ) ) {
    $this->redirect_available_page();
}

global $wpdb;


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=accum_invoices';
}
if ( isset( $_REQUEST['action'] ) ) {
    switch ( $_REQUEST['action'] ) {

        //delete
        case 'delete':
        case 'delete_inv':
            $ids = array();
            if ( isset( $_POST['delete_option'] ) ) {
                check_admin_referer( 'wpc_accum_invoice_delete' . get_current_user_id() );
                $delete_inv = $_POST['delete_option'];
                $ids = (array) $_POST['id'];
            } elseif ( isset( $_GET['delete_option'] ) && isset( $_GET['id'] ) ) {
                check_admin_referer( 'wpc_accum_invoice_delete' . $_GET['id'] . get_current_user_id() );
                $delete_inv = $_GET['delete_option'];
                $ids = (array) $_GET['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Accumulating Profiles', WPC_CLIENT_TEXT_DOMAIN ) ) );
                if ( 'delete_inv' == $_REQUEST['action'] ) {
                    $delete_inv = 'delete';
                } else {
                    $delete_inv = 'save';
                }
                $ids = $_REQUEST['item'];
            }
            if ( count( $ids ) ) {
                //delete repeat_invoice
                $this->delete_data( $ids );
                if ( 'delete' == $delete_inv ) {
                    $created_inoices = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wpc_inv_parrent_id' AND meta_value IN ('" . implode( "','", $ids ) . "')" ) ;
                    if ( $created_inoices ) {
                        $this->delete_data( $created_inoices );
                    }
                    WPC()->redirect( add_query_arg( 'msg', 'di', $redirect ) );
                    exit;
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

global $where_manager;
$where_manager = $manager_clients = '';
//for manager
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client');
    $where_manager = " AND coa.assign_id IN ('" . implode( "','", $manager_clients ) . "')" ;
}

$where_client = '';
//filter by clients
if ( isset( $_GET['filter_client']  ) ) {
    $client_id = (int)$_GET['filter_client'] ;
    if ( 0 < $client_id ) {

        $client_profiles = WPC()->assigns()->get_assign_data_by_assign( 'accum_invoice', 'client', $client_id );
        $display_items = array_unique( $client_profiles );

        $where_client = " AND p.ID IN ('" . implode( "','", $display_items ) . "')" ;
    }
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'p.post_content',
        'p.post_title',
        'pm1.meta_value',
        'pm3.meta_value',
    ) );
}

$order_by = 'p.ID';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'title' :
            $order_by = 'p.post_title';
            break;
        case 'status' :
            $order_by = 'p.post_status';
            break;
        case 'total' :
            $order_by = 'pm1.meta_value * 1';
            break;
        case 'date' :
            $order_by = 'p.post_date';
            break;
        case 'count' :
            $order_by = 'count';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Accumulating_Profile_List_Table extends WP_List_Table {

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

    function column_status( $item ) {
        global $wpc_inv;
        return $wpc_inv->display_status_name( $item['status'] );
    }

    function column_total( $item ) {
        global $wpc_inv;

        $selected_curr = get_post_meta( $item['id'], 'wpc_inv_currency', true ) ;
        $text = '<span id="total_' . $item['id'] . '">' . $wpc_inv->get_currency( $item['total'], true, $selected_curr ) . '</span>';
        return $text;
    }

    function column_client( $item ) {
        return $item['client_login'];
    }

    function column_date( $item ) {
        return WPC()->date_format( strtotime( $item['date'] ) );
    }

    function column_title( $item ) {

        $actions['edit']        = '<a href="admin.php?page=wpclients_invoicing&tab=accum_invoice_edit&id=' . $item['id'] . '" title="' . __( 'Edit Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ) . '" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_invoices' ) ) {
            $actions['delete'] = '<a href="#delete_permanently" rel="' . $item['id']  . '" class="delete_permanently">' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        return sprintf('%1$s %2$s', '<strong><a href="admin.php?page=wpclients_invoicing&tab=accum_invoice_edit&id=' . $item['id'] . '" title="' . __( 'Edit Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ) . '">'
                . htmlspecialchars( $item['title'] ) . '</a></strong>', $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb, $where_manager;

            $ids_client = $wpdb->get_col( "SELECT DISTINCT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns coa WHERE object_type='accum_invoice' {$where_manager}" );
            ?>
            <div class="alignleft actions">
                <select name="filter_client" id="filter_client">
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    if ( 0 < count( $ids_client ) ) {
                        foreach( $ids_client as $client_id ) {
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
            $this->search_box( __( 'Search Profile', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

}


$ListTable = new WPC_Accumulating_Profile_List_Table( array(
    'singular'  => __( 'Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Accumulating Profiles', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_inv_accum_invoices_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'title'            => 'title',
    'date'             => 'date',
    'total'            => 'total',
    'status'           => 'status',
    'count'            => 'count',
) );

$ListTable->set_bulk_actions(array(
    'delete'        => __( 'Delete Only Profile', WPC_CLIENT_TEXT_DOMAIN ),
    'delete_inv'    => __( 'Delete With Created Invoices', WPC_CLIENT_TEXT_DOMAIN ),
));

$arr_columns = array(
                'title'                => __( 'Title Profile', WPC_CLIENT_TEXT_DOMAIN ),
                'client'                => WPC()->custom_titles['client']['s'],
                'total'                 => __( 'Total', WPC_CLIENT_TEXT_DOMAIN ),
                'status'                => __( 'Status', WPC_CLIENT_TEXT_DOMAIN ),
                'count'                 => __( 'Count', WPC_CLIENT_TEXT_DOMAIN ),
                );

$arr_columns['date'] = __( 'Date', WPC_CLIENT_TEXT_DOMAIN );

$ListTable->set_columns($arr_columns);


$sql = "SELECT count( p.ID )
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'accum_inv' )
    LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'accum_invoice' )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
    LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_number' )
    WHERE p.post_type='wpc_invoice'
        {$where_client}
        {$where_manager}
        {$where_clause}
    ";

$items_count = $wpdb->get_var( $sql );

$sql = "SELECT p.ID as id, p.post_title as title, p.post_date as date, p.post_status as status, pm1.meta_value as total, pm3.meta_value as number, u.user_login as client_login,
    ( SELECT count(*) FROM {$wpdb->postmeta} pm4 WHERE pm4.meta_key = 'wpc_inv_parrent_id' AND pm4.meta_value = p.ID ) as count
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'accum_inv' )
    LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'accum_invoice' )
    LEFT JOIN {$wpdb->users} u ON ( u.ID = coa.assign_id )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
    LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_number' )
    WHERE p.post_type='wpc_invoice'
        {$where_client}
        {$where_manager}
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$cols = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $cols;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );
?>
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

    <?php
        echo WPC()->admin()->get_plugin_logo_block() ?>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo  '<div id="message" class="updated"><p>' . __( 'Accumulating Profile <strong>Created</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'ai':
                echo  '<div id="message" class="updated"><p>' . __( 'Accumulating Profile and Invoice are <strong>Created</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated"><p>' . __( 'Accumulating Profile <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'ui':
                echo '<div id="message" class="updated"><p>' . __( 'Accumulating Profile <strong>Updated</strong> and Invoice <strong>Created</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated"><p>' . __( 'Accumulating Profile(s) <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'di':
                echo '<div id="message" class="updated"><p>' . __( 'Accumulating Profile(s) With Created Invoices <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 's':
                echo '<div id="message" class="updated"><p>' . __( 'Accumulating Profile <strong>Stopped</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    }
    ?>

    <div class="clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_accumulating_inv" style="position: relative;">
            <?php if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_accum_invoices' ) ) { ?>
                <a href="admin.php?page=wpclients_invoicing&tab=accum_invoice_edit" class="add-new-h2">
                    <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </a>
            <?php } ?>

            <form action="" method="get" name="wpc_clients_form" id="wpc_clients_form">
                <input type="hidden" name="page" value="wpclients_invoicing" />
                <input type="hidden" name="tab" value="accum_invoices" />
                <?php $ListTable->display(); ?>
            </form>

            <div class="wpc_delete_permanently" id="delete_permanently" style="display: none;">
                <form method="post" name="wpc_delete_permanently" id="wpc_delete_permanently">
                    <input type="hidden" name="id" id="wpc_delete_id" value="" />
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'wpc_accum_invoice_delete' . get_current_user_id() ) ?>" />
                    <table>
                        <tr>
                            <td>
                                    <?php _e( 'What should be done with created Invoices by this Accumulating Profile?', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <ul>
                                        <li><label>
                                            <input id="delete_option0" type="radio" value="delete" name="delete_option" checked="checked">
                                            <?php
                                                 _e( 'Delete all Invoices', WPC_CLIENT_TEXT_DOMAIN );
                                             ?>
                                        </label></li>
                                        <li><label>
                                            <input id="delete_option1" type="radio" value="save" name="delete_option">
                                            <?php
                                                 _e( 'Save Invoices', WPC_CLIENT_TEXT_DOMAIN );
                                             ?>
                                        </label></li>
                                    </ul>
                                <br />
                                <br />
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div style="clear: both; text-align: center;">
                        <input type="button" class='button-primary' id="check_delete_permanently" value="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <input type="button" class='button' id="close_delete_permanently" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>
            </div>

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

        //from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });

        //open Delete Permanently
        jQuery( '.delete_permanently').each( function() {
            jQuery( '.delete_permanently').shutter_box({
                view_type       : 'lightbox',
                width           : '400px',
                type            : 'inline',
                href            : '#delete_permanently',
                title           : '<?php _e( 'Delete Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>',
                inlineBeforeLoad : function() {
                    var id = jQuery( this ).attr( 'rel' );
                    jQuery( '#wpc_delete_id' ).val( id );
                },
                onClose         : function() {
                    jQuery( '#wpc_delete_id' ).val( '' );
                }
            });
        });

        //close Delete Permanently
        jQuery( '#close_delete_permanently' ).click( function() {
            jQuery( '.delete_permanently').shutter_box('close');
        });

        //save option Delete Permanently
        jQuery( '#check_delete_permanently' ).click( function() {
            jQuery( '#wpc_delete_permanently' ).submit();
        });

    });
</script>
