<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
        && ! current_user_can( 'view_others_portalhubs' ) && ! current_user_can( 'edit_others_portalhubs' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients_content&tab=portalhubs' );
}

global $wpdb;

/**
*
* Code for recover HUB pages by DEV Team
*
*/
/*
 * maybe rewrite or delete
 *
 * if( isset( $_GET['dev_mode'] ) && '1' == $_GET['dev_mode'] && isset( $_GET['dev_action'] ) && 'broken_hubs_creation' == $_GET['dev_action'] ) {
    $excluded_clients  = WPC()->members()->get_excluded_clients();
    $wpc_clients = get_users( array(
        'role'  => 'wpc_client',
        'blog_id' => get_current_blog_id(),
        'exclude'   => $excluded_clients,
    ) );

    if( isset( $wpc_clients ) && !empty( $wpc_clients ) ) {
        foreach( $wpc_clients as $user_object ) {

            $business_name = get_user_meta( $user_object->ID, 'wpc_cl_business_name', true );
            $hub_page_id = get_user_meta( $user_object->ID, 'wpc_cl_hubpage_id', true );

            if( !( isset( $business_name ) && !empty($business_name ) ) ) {
                $business_name = $user_object->get( 'user_login' );
                //set business name
                update_user_meta( $user_object->ID, 'wpc_cl_business_name', $business_name );
            }

            if( !( isset( $hub_page_id ) && !empty( $hub_page_id ) ) || !get_post( $hub_page_id ) ) {
                $args = array(
                    'client_id' => $user_object->ID,
                    'business_name' => $business_name,
                );
                WPC()->pages()->create_hub_page( $args );
            }
        }
    }

    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=portalhubs';
    WPC()->redirect( $redirect );
}*/

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=portalhubs';
}


if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        /* delete action */
        case 'delete':

            $hubs_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_delete_portalhub' .  $_REQUEST['id'] . get_current_user_id() );
                $hubs_id = (array)$_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'HUB Pages', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $hubs_id = $_REQUEST['item'];
            }

            if ( count( $hubs_id ) ) {
                foreach ( $hubs_id as $hub_id ) {
                    $hubmeta = get_post_meta( $hub_id, 'wpc_default_template', true );

                    if ( empty( $hubmeta ) ) {
                        wp_delete_post( $hub_id, true );
                        WPC()->assigns()->delete_all_object_assigns( 'portalhub', $hub_id );
                    }
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
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
        'p.post_title',
    ) );
}

$where_date = '';
$m = ( isset( $_GET['m'] ) ) ? (int)$_GET['m'] : 0 ;
if ( 0 < $m && 6 == strlen( $m ) ) {
    $year = substr( $m, 0, 4 );
    $month = substr( $m, 4, 6 );
    $next_month = (int) $month + 1;
    //var_dump( $year, $month, date( "d-m-Y", mktime( 0, 0, 0, $month,1 , $year ) ) );
    $where_date = " AND p.post_modified > '{$year}-{$month}-01 00:00:00' AND p.post_modified < '{$year}-{$next_month}-01 00:00:00'";
}

$order_by = 'p.ID';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'date' :
            $order_by = 'p.post_modified';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Hubpages_List_Table extends WP_List_Table {

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
        if ( isset( $item[ $column_name ] ) ) {
            return $item[ $column_name ];
        } else {
            global $post;

            $post = get_post( $item['id'] );

            do_action( 'manage_portalhubs_custom_column_content', $column_name, $item['id'] );
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


    public function single_row( $item ) {
        $is_default = get_post_meta( $item['id'], 'wpc_default_template', true );

        echo '<tr ' . ( ! empty( $is_default ) ? 'class="wpc_default_portalhub"' : "" ) . '>';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_title( $item ) {
        global $wpdb;

        $actions = array();

        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )
            || current_user_can( 'wpc_edit_portalhubs' ) /*|| current_user_can( 'edit_portalhub', $item['id'] )*/ ) {
            $actions['edit'] = '<a href="post.php?post=' . $item['id'] . '&action=edit" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        //make link
        if( current_user_can('wpc_admin_user_login') || current_user_can('manage_options') ) {
            $user_ids = WPC()->assigns()->get_assign_data_by_object( 'portalhub', $item['id'] );
            $circle_ids = WPC()->assigns()->get_assign_data_by_object( 'portalhub', $item['id'], 'circle' );
            foreach( $circle_ids as $circle_id ) {
                $user_ids = array_merge ( $user_ids, WPC()->groups()->get_group_clients_id( $circle_id ) );
            }
            if( count( $user_ids ) ) {
                $user_id = '';
                foreach( $user_ids as $id ) {
                    $portalhub = WPC()->pages()->get_portalhub_for_client( $id );
                    if( $portalhub->ID == $item['id'] ) {
                        $user_id = $id;
                        break;
                    }
                }
                if( !empty( $user_id ) ) {
	                $schema          = is_ssl() ? 'https://' : 'http://';
	                $current_url     = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	                $hub_preview_url = get_admin_url( null, 'admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . get_current_user_id() . $user_id ) . '&page_name=hub&id=' . $user_id . '&referer_url=' . urlencode( $current_url ) );
	                $actions['view'] = '<a href="' . $hub_preview_url . '" onclick=\'return confirm("' . sprintf( __( "You will be re-logged-in under the role of %s to preview this page. Continue?", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\' >' . __( 'Preview', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                }
            }
        }

        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )
            || current_user_can( 'delete_post', $item['id'] ) ) {

            $actions['delete'] = '<a href="admin.php?page=wpclients_content&tab=portalhubs&id=' . $item['id'] . '&action=delete&_wpnonce=' . wp_create_nonce( 'wpc_delete_portalhub' . $item['id'] . get_current_user_id() ) . '" onclick=\'return confirm("' . __( "Are you sure to delete HUB Page?", WPC_CLIENT_TEXT_DOMAIN ) . '");\' title="' . esc_attr( __( 'Delete this item' ) ) . '">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }


        return sprintf('%1$s %2$s %3$s',
            '<b><a href="post.php?post=' . $item['id'] . '&action=edit">' . ( ( '' != $item['title'] ) ? $item['title'] : '(' . __( 'no title', WPC_CLIENT_TEXT_DOMAIN )  . ')' ) . '</a></b>',
            ! empty( $item['admin_label'] ) ? '<span class="description" title="' . __( 'Admin Label', WPC_CLIENT_TEXT_DOMAIN ) . '">(' . $item['admin_label'] . ')</span>' : '',
            $this->row_actions( $actions )
        );
    }


    function column_default_value( $item ) {

        $parent_post_id = apply_filters( 'wpc_change_portalhub_id', $item['id'] );

        $is_default = get_post_meta( $parent_post_id, 'wpc_default_template', true );

        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_edit_portalhubs' ) ) {
            return '<span id="wpc_ajax_loading_' . $parent_post_id . '"></span>
            <input type="radio" name="default_template_option" class="default_template_option" value="' . $parent_post_id . '" ' . checked( 1 == $is_default, true, false ) . ' />';
        } else {
            return '<input type="radio" disabled name="default_template_option" class="default_template_option" value="' . $parent_post_id . '" ' . checked( 1 == $is_default, true, false ) . ' />';

        }
    }


    function column_order( $item ) {

        $parent_post_id = apply_filters( 'wpc_change_portalhub_id', $item['id'] );

        $priority = get_post_meta( $parent_post_id, 'wpc_template_priority', true );

        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_edit_portalhubs' ) ) {
            return '<input type="number" class="wpc_priority" data-id="' . $parent_post_id . '" id="order_' . $parent_post_id . '" name="hub_template[priority][]" value="' . $priority . '" style="width: 50px;" />
                    <span class="wpc_ajax_loading" style="display: none;" id="load_order_' . $parent_post_id . '"></span>';
        } else {
            return $priority;
        }
    }


    function column_clients( $item ) {

        $parent_post_id = apply_filters( 'wpc_change_portalhub_id', $item['id'] );

        $clients_ids = WPC()->assigns()->get_assign_data_by_object( 'portalhub', $parent_post_id, 'client' );
        $link_array = array(
            'title'   => sprintf( __( 'Assign clients to %s', WPC_CLIENT_TEXT_DOMAIN ), $item['title'] ),
            'data-ajax' => true,
            'data-id' => $parent_post_id,
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $parent_post_id,
            'value' => implode( ',', $clients_ids )
        );
        $additional_array = array(
            'counter_value' => count( $clients_ids ),
        );

/*        if (!$can_edit)
            $additional_array['readonly'] = true;*/

        return WPC()->assigns()->assign_popup( 'client', 'wpc_client_portalhubs', $link_array, $input_array, $additional_array );
    }


    function column_circles( $item ) {
        $parent_post_id = apply_filters( 'wpc_change_portalhub_id', $item['id'] );

        $groups_ids = WPC()->assigns()->get_assign_data_by_object( 'portalhub', $parent_post_id, 'circle' );
        $link_array = array(
            'data-id' => $parent_post_id,
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'], $item['title'] )
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $parent_post_id,
            'value' => implode( ',', $groups_ids )
        );
        $additional_array = array(
            'counter_value' => count( $groups_ids )
        );

/*        if (!$can_edit)
            $additional_array['readonly'] = true;*/

        return WPC()->assigns()->assign_popup( 'circle', 'wpc_client_portalhubs', $link_array, $input_array, $additional_array );
    }


    function extra_tablenav( $which ) {
        do_action( 'wpc_portalhubs_list_table_tablenav', $which );
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}

$ListTable = new WPC_Hubpages_List_Table( array(
    'singular'  => __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'HUB Pages', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_portalhubs_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'title'     => 'title',
    'date'      => 'date',
) );


$columns = array(
    'default_value' => __( 'Default', WPC_CLIENT_TEXT_DOMAIN ),
    'title'         => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
);

if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_edit_portalhubs' ) ) {
    $columns = array_merge( $columns, array(
        'clients'       => WPC()->custom_titles['client']['s'],
        'circles'       => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
    ) );
}

$columns['order'] = __( 'Priority Order', WPC_CLIENT_TEXT_DOMAIN );
$columns['date'] = __( 'Date', WPC_CLIENT_TEXT_DOMAIN );

$columns = apply_filters( 'manage_portalhubs_columns', $columns, 'portalhub' );

$ListTable->set_columns( $columns );

//wpml compatibility
$posts = get_posts( array(
    'post_type' => 'portalhub',
    'numberposts' => -1,
    'post_status' => 'publish',
    'suppress_filters' => 0,
    'fields' => 'ids'
) );

$wpml = '';
if ( $posts )
    $wpml = " AND p.ID IN('" . implode( "','", $posts ) . "')";

$sql = "SELECT count( p.ID )
    FROM {$wpdb->posts} p
    WHERE p.post_type = 'portalhub' AND
          p.post_status = 'publish'
          {$where_clause}
          {$where_date}
          {$wpml}";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT p.ID as id, p.post_title as title, p.post_modified as date, p.post_status as status, pm3.meta_value AS admin_label
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key='wpc_admin_label'
    WHERE p.post_type = 'portalhub' AND
          p.post_status = 'publish'
          {$where_clause}
          {$where_date}
          {$wpml}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$pages = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $pages;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<script type="text/javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery(document).ready(function(){
        var wpc_timeout;
        jQuery('.wpc_priority').on('change', function() {
            if( typeof wpc_timeout != 'undefined' ) {
                clearTimeout( wpc_timeout );
            }

            var block_id = jQuery(this).attr('id');
            var id = jQuery(this).data('id');
            var value = jQuery(this).val();

            wpc_timeout = setTimeout( function() {
                jQuery( '#load_' + block_id ).css( 'display', 'inline-block' );
                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_save_priority&type=portalhub&id=' + id + '&value=' + value,
                    success: function( data ){
                        if( data.status ) {
                            jQuery( '#load_' + block_id ).css( 'display', 'none' );
                            jQuery( '#order_' + id ).val( data.message );
                        } else {
                            alert( data.message );
                        }
                    }
                });
            }, 1000 );
        });

        jQuery(".over").hover(function(){
            jQuery(this).css("background-color","#bcbcbc");
        },function(){
            jQuery(this).css("background-color","transparent");
        });

        jQuery('.default_template_option').click(function() {
            var value = jQuery(this).val();

            add_link();
            jQuery(this).parents( 'tbody' ).find( 'tr' ).removeClass( 'wpc_default_portalhub' );

            jQuery(this).hide();
            jQuery( '#wpc_ajax_loading_' + value ).addClass( 'wpc_ajax_loading' );
            var obj = jQuery(this);
            jQuery.ajax({
                type: 'POST',
                dataType    : 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: 'action=wpc_hub_set_default&id=' + value,
                success: function( data ){
                    jQuery( '#wpc_ajax_loading_' + value ).removeClass( 'wpc_ajax_loading' );
                    obj.show();
                    obj.parents( 'tr' ).addClass( 'wpc_default_portalhub' );
                    remove_link();
                    if ( ! data.status ) {
                        alert( data.message );
                    }
                }
            });
        });

        remove_link();

        function add_link() {
            jQuery('.wpc_default_portalhub').find( '.row-actions .view' ).html( jQuery('.wpc_default_portalhub').find( '.row-actions .view' ).html() + ' | ' );
        }

        function remove_link() {
            var outerhtml = jQuery('<div>').append( jQuery('.wpc_default_portalhub').find( '.row-actions .view a' ).clone() ).html();
            jQuery('.wpc_default_portalhub').find( '.row-actions .view' ).html( outerhtml );
        }
    });

</script>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>
    <?php if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'HUB <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    } ?>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block" style="float: left;width:100%;">
            <?php if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_portalhubs' ) ) { ?>
                <a href="post-new.php?post_type=portalhub" class="add-new-h2" style="float: left;"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            <?php } ?>
            <form action="" method="get" name="wpc_clients_form" id="wpc_portalhubs_form" style="width:100%;">
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="portalhubs" />
                <?php $ListTable->search_box( __( 'Search HUB', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
                $ListTable->display(); ?>
            </form>
        </div>

    </div>

    <script type="text/javascript">

        jQuery(document).ready(function(){

            //reassign file from Bulk Actions
            jQuery( '#doaction2' ).click( function() {
                var action = jQuery( 'select[name="action2"]' ).val() ;
                jQuery( 'select[name="action"]' ).attr( 'value', action );

                return true;
            });
        });
    </script>

</div>