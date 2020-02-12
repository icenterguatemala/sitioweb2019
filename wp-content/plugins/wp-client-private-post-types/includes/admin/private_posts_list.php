<?php
global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpc_private_post_types';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        case 'assign_client':
            $ids = array();
            if( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __('post types', WPC_CLIENT_TEXT_DOMAIN) ) );
                $ids = $_REQUEST['item'];
            }

            $assigns = ( !empty( $_REQUEST['assigns'] ) ) ? explode( ',', $_REQUEST['assigns'] ) : array();

            foreach( $ids as $post_id ) {
                WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'client', $assigns );
            }

            WPC()->redirect( add_query_arg( 'msg', 'cla', $redirect ) );
            exit;
        break;
        case 'assign_circle':
            $ids = array();
            if( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __('post types', WPC_CLIENT_TEXT_DOMAIN) ) );
                $ids = $_REQUEST['item'];
            }

            $assigns = ( !empty( $_REQUEST['assigns'] ) ) ? explode( ',', $_REQUEST['assigns'] ) : array();

            foreach( $ids as $post_id ) {
                WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'circle', $assigns );
            }

            WPC()->redirect( add_query_arg( 'msg', 'cia', $redirect ) );
            exit;
        break;
        case 'cancel_protect':
            if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                WPC()->redirect( $redirect );
                exit;
            }

            $ids = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_cancel_protect' .  $_REQUEST['id'] . get_current_user_id() );
                $ids = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( __('post types', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $ids = $_REQUEST['item'];
            }

            foreach( $ids as $post_id ) {
                delete_post_meta( $post_id, '_wpc_protected' );
            }

            WPC()->redirect( add_query_arg( 'msg', 'canceled_protect', $redirect ) );
            exit;
        break;
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
        'p.post_title',
    ) );
}

if( !empty( $_GET['p_type'] ) ) {
    $where_clause .= " AND p.post_type = '" . trim( esc_sql( $_GET['p_type'] ) ) . "'";
}

if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $manager_id = get_current_user_id();

    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'client' );
    $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'circle' );

    $clients_groups = array();
    foreach ( $manager_groups as $group_id ) {
        $clients_groups = array_merge( $clients_groups, WPC()->groups()->get_group_clients_id( $group_id ) );
    }
    $manager_all_clients = array_unique( array_merge( $manager_clients, $clients_groups ) );

    $groups_clients = array();
    foreach ( $manager_clients as $client_id ) {
        $groups_clients = array_merge( $groups_clients, WPC()->groups()->get_client_groups_id( $client_id ) );
    }
    $manager_all_groups = array_unique( array_merge( $manager_groups, $groups_clients ) );

    if( count( $manager_all_clients ) || count( $manager_all_groups ) ) {
        $where_clause .= " AND ( ";
        if( count( $manager_all_clients ) ) {
            $private_ids = WPC()->assigns()->get_assign_data_by_assign( 'private_post', 'client', $manager_all_clients );
            $where_clause .= "p.ID IN('" . implode( "','", $private_ids ) . "')";
        }
        if( count( $manager_all_groups ) ) {
            if( count( $manager_all_clients ) ) {
                $where_clause .= " OR ";
            }
            $private_ids = WPC()->assigns()->get_assign_data_by_assign( 'private_post', 'circle', $manager_all_groups );
            $where_clause .= "p.ID IN('" . implode( "','", $private_ids ) . "')";
        }

        $where_clause .= " )";
    }

}

$order_by = 'p.ID';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'date' :
            $order_by = 'p.post_modified';
            break;
        default :
            $order_by = 'p.' . esc_sql( $_GET['orderby'] );
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Protected_Posts_List_Table extends WP_List_Table {

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_post_title( $item ) {
        $actions = array();
        if( !( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) ) {
            $actions['edit'] = '<a href="post.php?post=' . $item['id'] . '&action=edit" title="' . esc_attr( __( 'Edit page', WPC_CLIENT_TEXT_DOMAIN ) ) . '"  >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }
        $actions['delete'] = '<a onclick=\'return confirm("' . __( 'Are you sure you want to Cancel Protect?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpc_private_post_types&action=cancel_protect&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_cancel_protect' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" title="' . esc_attr( __( 'Cancel Protect for this page', WPC_CLIENT_TEXT_DOMAIN ) ) . '"  >' . __( 'Cancel Protect', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', '<b><a href="post.php?post=' . $item['id'] . '&action=edit">' . ( ( '' != $item['title'] ) ? $item['title'] : '(' . __( 'no title', WPC_CLIENT_TEXT_DOMAIN )  . ')' ) . '</a></b>', $this->row_actions( $actions ) );
    }


    function column_clients( $item ) {
        $users = WPC()->assigns()->get_assign_data_by_object( 'private_post', $item['id'], 'client' );
        if( !is_array( $users ) ) {
            $users = array();
        }

        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' )  ) {
            //show only manager clients
            $clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
            $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            foreach ( $manager_groups as $group_id ) {
                $add_client = WPC()->groups()->get_group_clients_id( $group_id );
                $clients = array_merge( $clients, $add_client );
            }
            $clients = array_unique( $clients );
            $menedger_users = array();
            foreach ( $users as $client_id ) {
                if ( 0 < $client_id ) {
                    if ( !isset( $clients ) || !in_array( $client_id, $clients ) )
                        continue;
                    if( !empty( $client_id ) ) {
                        $menedger_users[] = $client_id;
                    }
                }
            }
            $users = $menedger_users;
        }

        echo '<div class="scroll_data">';

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['id'],
            'value' => implode( ',', $users )
        );
        $additional_array = array(
            'counter_value' => count( $users )
        );
        WPC()->assigns()->assign_popup( 'client', 'wpc_private_post_types', $link_array, $input_array, $additional_array );

        echo '</div>';
    }

    function column_groups( $item ) {
        echo '<div class="scroll_data">';

        $id_array = WPC()->assigns()->get_assign_data_by_object( 'private_post', $item['id'], 'circle' );
        if( !is_array( $id_array ) ) {
            $id_array = array();
        }

        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' )  ) {
            $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            $id_array = array_intersect( $id_array, $manager_clients );
        }

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => count( $id_array )
        );

        WPC()->assigns()->assign_popup( 'circle', 'wpc_private_post_types', $link_array, $input_array, $additional_array );

        echo '</div>';
    }


    function column_post_type( $item ) {
        return ucfirst( $item['post_type'] );
    }


    function column_date( $item ) {
        return '<abbr title="' . $item['date'] . '">' . $item['date'] . '</abbr><br />';
    }


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( __( 'Search Posts', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
    }
}


$ListTable = new WPC_Protected_Posts_List_Table( array(
    'singular'  => __('post type', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __('post types', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_ppt_posts_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'post_title'    => 'post_title',
    'post_type'     => 'post_type',
    'date'          => 'date',
) );

$ListTable->set_columns(array(
    'cb'             => '<input type="checkbox" />',
    'post_title'     => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
    'post_type'      => __( 'Post Type', WPC_CLIENT_TEXT_DOMAIN ),
    'clients'        => WPC()->custom_titles['client']['s'],
    'groups'         => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
    'date'           => __( 'Date', WPC_CLIENT_TEXT_DOMAIN )
));

$ListTable->set_bulk_actions(array(
    'cancel_protect'    => __( 'Cancel Protect', WPC_CLIENT_TEXT_DOMAIN ),
    'assign_client'     => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'assign_circle'     => sprintf( __( 'Assign To %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
));

$items_count = 0;
$pages = array();
$private_post_types = get_option( 'wpc_private_post_types' );
$types = array();
if( isset( $private_post_types['types'] ) && count( $private_post_types['types'] ) ) {
    foreach( $private_post_types['types'] as $key=>$val ) {
        if( $val == '1' || 'yes' == $val ) {
            $types[] = $key;
        }
    }
    if( count( $types ) ) {
        $sql = "SELECT count( p.ID )
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1
            ON p.ID = pm1.post_id
            WHERE
                p.post_status != 'trash' AND
                pm1.meta_key = '_wpc_protected' AND pm1.meta_value = '1' AND p.post_type IN('" . implode( "','", $types ) . "')
                {$where_clause}
            ";
        $items_count = $wpdb->get_var( $sql );
        $sql = "SELECT p.ID as id, p.post_title as title, p.post_modified as date, p.post_status as status, p.post_type
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1
            ON p.ID = pm1.post_id
            WHERE
                p.post_status != 'trash' AND
                pm1.meta_key = '_wpc_protected' AND pm1.meta_value = '1' AND p.post_type IN('" . implode( "','", $types ) . "')
                {$where_clause}
            ORDER BY $order_by $order
            LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
        $pages = $wpdb->get_results( $sql, ARRAY_A );
    }
}

$ListTable->prepare_items();
$ListTable->items = $pages;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<style type="text/css">
    #wpc_private_posts_form .search-box {
        float:left;
        padding: 2px 8px 0 0;
    }

    #wpc_private_posts_form .search-box input[type="search"] {
        margin-top: 1px;
    }

    #wpc_private_posts_form .search-box input[type="submit"] {
        margin-top: 1px;
    }

    #wpc_private_posts_form .alignleft.actions input[type="button"] {
        margin-top: 1px;
    }

    #wpc_private_posts_form .alignleft.actions .add-new-h2 {
        line-height: 20px;
        height:28px;
        float:right;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        margin:1px 0 0 4px;
        top:0;
        cursor: pointer;
    }
</style>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php if( !empty( $_GET['msg'] ) && 'canceled_protect' == $_GET['msg'] ) {
        echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Page protect has canceled successfully', WPC_CLIENT_TEXT_DOMAIN ). '</p></div>';
    }
    if( !empty( $_GET['msg'] ) && ( 'cla' == $_GET['msg'] || 'cia' == $_GET['msg'] ) ) {
        echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Private Posts was assigned successfully', WPC_CLIENT_TEXT_DOMAIN ). '</p></div>';
    } ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <div class="icon32" id="icon-link-manager"></div>
        <h2><?php _e( 'Private Post Types', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <div class="wpc_clear"></div>

        <?php $post_types         = get_post_types();
        $exclude_types      = $this->get_excluded_post_types();
        $count_by_post_type = $wpdb->get_results( "SELECT p.post_type, COUNT( p.ID ) as count
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1
                ON p.ID = pm1.post_id
                WHERE
                    p.post_status != 'trash' AND
                    pm1.meta_key = '_wpc_protected' AND
                    pm1.meta_value = '1' AND
                    p.post_type IN('" . implode( "','", $types ) . "')
                GROUP BY p.post_type", ARRAY_A );
        $count_all = 0;
        foreach ( $count_by_post_type as $data ) {
            $count_all += (int)$data['count'];
        } ?>

        <ul class="subsubsub" style="float: left;margin-top: 10px;">
            <li class="all">
                <a class="<?php echo ( empty( $_GET['p_type'] ) ) ? 'current' : '' ?>" href="admin.php?page=wpc_private_post_types"  ><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo $count_all ?>)</span></a>
            </li>
            <?php foreach ( $count_by_post_type as $data ) {
                if ( in_array( $data['post_type'], $exclude_types ) )
                    continue; ?>
                <li class="image">
                    | <a class="<?php echo ( isset( $_GET['p_type'] ) && $data['post_type'] == $_GET['p_type'] ) ? 'current' : '' ?>" href="admin.php?page=wpc_private_post_types&p_type=<?php echo $data['post_type']; ?>"><?php echo ucfirst( $data['post_type'] ); ?><span class="count">(<?php echo $data['count']; ?>)</span></a>
                </li>
            <?php } ?>
        </ul>

        <form action="" method="get" name="wpc_private_posts_form" id="wpc_private_posts_form" style="float: left;">
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
                WPC()->assigns()->assign_popup( 'client', 'wpc_private_post_types', $link_array, $input_array, $additional_array );

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
                WPC()->assigns()->assign_popup( 'circle', 'wpc_private_post_types', $link_array, $input_array, $additional_array ); ?>
            </div>
            <input type="hidden" name="page" value="wpc_private_post_types" />
            <?php $ListTable->display(); ?>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });

        var post_id = [];
        var nonce = '';

        jQuery('#wpc_private_posts_form').submit(function() {
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

        jQuery( 'body' ).on( 'click', '.bulk_assign .wpc_ok_popup', function() {
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

                    var item_string = '';
                    post_id.forEach(function( item, key ) {
                        item_string += '&item[]=' + item;
                    });
                    window.location = '<?php echo admin_url(); ?>admin.php?page=wpc_private_post_types&action=' + action + item_string + '&assigns=' + current_value + '&_wpnonce=' + nonce + '&_wp_http_referer=' + encodeURIComponent( jQuery('input[name=_wp_http_referer]').val() );
                }
            } else {
                window.location = '<?php echo admin_url(); ?>admin.php?page=wpc_private_post_types';
            }
            post_id = [];
            nonce = '';
            return false;
        });
    });
</script>