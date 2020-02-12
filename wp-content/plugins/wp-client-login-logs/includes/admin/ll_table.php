 <?php
global $wpdb;


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_login_logs';
}
if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        /* delete action */
        case 'delete':

            $cols_id = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_col_delete' .  $_REQUEST['id'] . get_current_user_id() );
                $cols_id = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-cols' );
                $cols_id = $_REQUEST['item'];
            }

            if ( count( $cols_id ) ) {
                foreach ( $cols_id as $col_id ) {
                    $wpdb->delete( $wpdb->prefix . 'wpc_client_login_logs',
                        array( 'id' => $col_id ),
                        array( '%d' )
                    );
                }
                WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
                exit;
            }
            WPC()->redirect( $redirect );
            exit;

        break;
    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}


global $role;

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause .= WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'u.user_login',
        'cll.ip_address',
    ) );
}

$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';
$where_role = empty($role) ? '' : " AND um.meta_value LIKE '%" . serialize( $role ) . "%'";
//var_dump($where_role);exit;


$order_by = 'cll.id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'username' :
            $order_by = 'u.user_login';
            break;
        case 'user_role' :
            $order_by = 'um.meta_value';
            break;
        case 'login_time' :
            $order_by = 'cll.login_time';
            break;
        case 'status' :
            $order_by = 'cll.status';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Login_Logs_List_Table extends WP_List_Table {

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

    function column_cb( $item ) {
        return '<input type="checkbox" name="item[]" value="' . $item['id'] . '" />';
    }

    function column_user_role( $item ) {
        global $wp_roles;
        $roles_arr = unserialize( $item['user_role'] );
        $roles_str = '';
        if ( is_array( $roles_arr ) )
            foreach ( $roles_arr as $key => $value ) {
                if ( isset( $wp_roles->role_names[ $key ] ) ) {
                    $roles_str .= translate_user_role( $wp_roles->role_names[ $key ] );
                } else {
                    continue;
                }
                $roles_str .= ', ' ;
            }
        if ( '' != $roles_str )
            $roles_str = substr( $roles_str, 0, -2 );
        return $roles_str;
    }

    function column_username( $item ) {
        $actions['delete'] = '<a onclick=\'return confirm("'
            . __( 'Are you sure to delete this Item?', WPC_CLIENT_TEXT_DOMAIN )
            . '");\' href="admin.php?page=wpclients_login_logs&action=delete&id='
            . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_col_delete'
            . $item['id'] . get_current_user_id() ) . '">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf('%1$s %2$s', $item['username'] , $this->row_actions( $actions ) );

    }

    function column_login_time( $item ) {
        return WPC()->date_format( $item['login_time'], 'date' ) . '<br />' . WPC()->date_format( $item['login_time'], 'time' );
    }

    function column_login_from( $item ) {
        $html = '';
        if ( !empty( $item['login_from'] ) ) {
            $login_from = unserialize( $item['login_from'] );

            foreach( $login_from as $key => $val ) {
                if ( !empty( $val ) ) {
                    $html .= "{$key}: {$val} \r\n";
                }
            }
        }

        if ( $html ) {
            return '<a href="javascript:void(0);" title="' . $html . '">Hover to View</a>';
        }

        return '';
    }

    function column_status( $item ) {
        $html = '<p style="color:';
        $html .= ( strtolower( 'Log In' ) != strtolower( $item['status'] ) ) ? 'red' : 'green';
        $html .= ';">' . $item['status'] . '</p>';
        return  $html;
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            $this->search_box( __( 'Search', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
        }
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
        if ( 'top' == $which || 'bottom' == $which )
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
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


    function wpc_set_pagination_args( $attr = false ) {
        $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_Login_Logs_List_Table( array(
    'singular'  => 'Col',
    'plural'    => 'Cols',
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_ll_login_logs_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'username'          => 'user_login',
    'user_role'         => 'user_role',
    'login_time'        => 'login_time',
    'ip_address'        => 'ip_address',
    'status'            => 'status',
) );

$ListTable->set_bulk_actions( array(
    'delete'        => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'username'          => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'user_role'         => __( 'User Role', WPC_CLIENT_TEXT_DOMAIN ),
    'login_time'        => __( 'Login Time', WPC_CLIENT_TEXT_DOMAIN ),
    'ip_address'        => __( 'IP', WPC_CLIENT_TEXT_DOMAIN ),
    'login_from'        => __( 'Login From', WPC_CLIENT_TEXT_DOMAIN ),
    'status'            => __( 'Status', WPC_CLIENT_TEXT_DOMAIN ),
));

$sql = "SELECT um.meta_value as role
    FROM {$wpdb->prefix}wpc_client_login_logs cll
    LEFT JOIN {$wpdb->users} u ON u.ID = cll.user_id
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
    GROUP BY um.meta_value
    ";
$cll_roles = $wpdb->get_col( $sql );
$all_roles = array();
foreach( $cll_roles as $key => $value ) {
    $cll_roles[$key] = unserialize($value);
    foreach ( $cll_roles[$key] as $key_r => $value_r ) {
        $all_roles[] = $key_r;
    }
}
$all_roles = array_unique($all_roles);

$sql = "SELECT count( cll.id )
    FROM {$wpdb->prefix}wpc_client_login_logs cll
    LEFT JOIN {$wpdb->users} u ON u.ID = cll.user_id
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        {$where_role}
        {$where_clause}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT cll.id as id, cll.ip_address as ip_address, cll.login_from as login_from, u.ID as user_id, u.user_login as username, um.meta_value as user_role, cll.login_time as login_time, cll.status as status
    FROM {$wpdb->prefix}wpc_client_login_logs cll
    LEFT JOIN {$wpdb->users} u ON u.ID = cll.user_id
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        {$where_role}
        {$where_clause}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$cols = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $cols;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

 <style>
     .ll_login_from {
         font-size: 10px;
     }

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

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <div class="icon32" id="icon-link-manager"></div>
        <h2><?php _e( 'Log in Users', WPC_CLIENT_TEXT_DOMAIN )?></h2>
        <p><?php printf( __( 'Here you can see all the %s who logged in', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></p>

        <span class="wpc_clear"></span>

        <ul class="subsubsub">
            <?php global $wp_roles, $role;
            $url = get_admin_url(). 'admin.php?page=wpclients_login_logs';
            $count_users = count_users();

            $total_users = $items_count;
            $avail_roles = $all_roles;

            $current_role = false;
            $class = empty($role) ? ' class="current"' : '';
            $role_links = array();
            $limit = ( isset( $_GET['limit'] ) ) ? '&limit=' . $_GET['limit'] : '' ;
            $role_links['all'] = "<a href='$url$limit'$class>All</a>";
            foreach ( $wp_roles->get_names() as $this_role => $name ) {
                if ( !in_array( $this_role, $all_roles) )
                    continue;

                $class = '';

                if ( $this_role == $role ) {
                    $current_role = $role;
                    $class = ' class="current"';
                }

                $name = translate_user_role( $name );
                $role_links[$this_role] = "<a href='" . esc_url( add_query_arg( 'role', $this_role, $url ) ) . "$limit'$class>$name</a>";
            }

            foreach ( $role_links as $class => $view ) {
                $role_links[ $class ] = "\t<li class='$class'>$view";
            }
            echo implode( " |</li>\n", $role_links ) . "</li>\n"; ?>

        </ul>
        <form action="" method="get" name="wpc_clients_form" id="wpc_clients_form">
            <input type="hidden" name="page" value="wpclients_login_logs" />
            <?php $ListTable->display(); ?>
        </form>
    </div>
</div>