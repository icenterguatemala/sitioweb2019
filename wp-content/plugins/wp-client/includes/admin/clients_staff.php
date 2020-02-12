<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( WPC()->flags['easy_mode'] ) {
	WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_add_staff' ) ) {
	WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
	$redirect = remove_query_arg( array('_wp_http_referer'), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
}
else {
	$redirect = get_admin_url() . 'admin.php?page=wpclient_clients&tab=staff';
}

if ( isset( $_GET['action'] ) ) {
	switch ( $_GET['action'] ) {
		/* delete action */
		case 'delete': case 'delete_from_blog': case 'delete_mu':

			$clients_id = array();
			if ( isset( $_REQUEST['id'] ) ) {
				check_admin_referer( 'wpc_staff_delete' . $_REQUEST['id'] . get_current_user_id() );
				$clients_id = (array) $_REQUEST['id'];
			}
			elseif ( isset( $_REQUEST['item'] ) ) {
				check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['staff']['p'] ) );
				$clients_id = $_REQUEST['item'];
			}

			if ( count( $clients_id ) ) {
				foreach ( $clients_id as $client_id ) {

					$custom_fields = WPC()->get_settings( 'custom_fields' );
					if ( isset( $custom_fields ) && !empty( $custom_fields ) ) {
						foreach ( $custom_fields as $key => $value ) {
							if ( isset( $value['type'] ) && 'file' == $value['type'] ) {
								if ( isset( $value['nature'] ) && ( 'staff' == $value['nature'] || 'both' == $value['nature'] ) ) {
									$filedata = get_user_meta( $client_id, $key, true );
									if ( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
										$filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $filedata['filename'];
										if ( file_exists( $filepath ) ) {
											unlink( $filepath );
										}
									}
								}
							}
						}
					}

					if ( $_GET['action'] == 'delete_mu' ) {
						wpmu_delete_user( $client_id );
					}
					else {
						wp_delete_user( $client_id );
					}
				}
				WPC()->redirect( add_query_arg( 'msg', 'd', $redirect ) );
			}
			WPC()->redirect( $redirect );

			break;

		case 'temp_password':
			$staff_ids = array();
			if ( isset( $_REQUEST['id'] ) ) {
				check_admin_referer( 'staff_temp_password' . $_REQUEST['id'] . get_current_user_id() );
				$staff_ids = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
			}
			elseif ( isset( $_REQUEST['item'] ) ) {
				check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['staff']['p'] ) );
				$staff_ids = $_REQUEST['item'];
			}

			foreach ( $staff_ids as $staff_id ) {
				WPC()->members()->set_temp_password( $staff_id );
			}

			if ( 1 < count( $staff_ids ) ) {
				WPC()->redirect( add_query_arg( 'msg', 'pass_s', $redirect ) );
			}
			else if ( 1 === count( $staff_ids ) ) {
				WPC()->redirect( add_query_arg( 'msg', 'pass', $redirect ) );
			}
			else {
				WPC()->redirect( $redirect );
			}
	}
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
	WPC()->redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
}


global $wpdb;

$where_clause = '';
if ( !empty( $_GET['s'] ) ) {
	$where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
		'u.user_login',
		'u.user_email',
		'um2.meta_value',
		'um3.meta_value',
		) );
}

$not_approved = get_users( array('role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID',) );
$not_approved = " AND u.ID NOT IN ('" . implode( ',', $not_approved ) . "')";

$order = ( isset( $_GET['order'] ) && 'asc' == strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';
$order_by = 'u.user_registered ' . $order;
if ( isset( $_GET['orderby'] ) ) {
	switch ( $_GET['orderby'] ) {
		case 'username' :
			$order_by = 'u.user_login ' . $order;
			break;
		case 'name' :
			$order_by = 'um2.meta_value ' . $order . ', um3.meta_value ' . $order;
			break;
		case 'email' :
			$order_by = 'u.user_email ' . $order;
			break;
		case 'client' :
			$client_ids = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'parent_client_id'" );
			if ( count( $client_ids ) ) {
				$client_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->users} WHERE ID IN ('" . implode( "','", $client_ids ) . "') ORDER BY user_login $order" );
				$order_by = "FIELD( parent_client_id, '" . implode( "','", $client_ids ) . "', '' )";
			}
			break;
	}
}

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Staff_List_Table extends WP_List_Table {

	var $no_items_message = '';
	var $sortable_columns = array();
	var $default_sorting_field = '';
	var $actions = array();
	var $bulk_actions = array();
	var $columns = array();
    var $custom_fields = array();

	function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'singular' => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
			'plural'	 => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
			'ajax'		 => false
			) );

		$this->no_items_message = $args['plural'] . ' ' . __( 'not found.', WPC_CLIENT_TEXT_DOMAIN );

		/* >> filter by client for staff */
		add_filter( 'clients_staff_sql', array( $this, 'filter_clients_staff_sql' ) );
		/* << filter by client for staff */

		parent::__construct( $args );
	}

	function __call( $name, $arguments ) {
		return call_user_func_array( array($this, $name), $arguments );
	}


    function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = get_hidden_columns( $this->screen );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {
        if( isset( $this->custom_fields[ $column_name ] ) ) {
            return WPC()->custom_fields()->render_custom_field_value( $this->custom_fields[ $column_name ], array(
                'user_id' => $item['id'],
                'value' => maybe_unserialize ( isset($item[$column_name]) ? $item[$column_name] : '' ),
                'metadata_exists' => isset($item[$column_name]),
                'empty_value' => '<span title="' . __("Undefined", WPC_CLIENT_TEXT_DOMAIN) . '">-</span>'
            ));
        } else {
            $value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';

            /*our_hook_
            hook_name: wpc_client_{$column_name}_custom_column_of_staff
            hook_title: Change default value of columns on Staff page
            hook_description: Hook runs before echo default value of columns on Staff page.
            hook_type: filter
            hook_in: wp-client
            hook_location clients.php
            hook_param: mixed $value
            hook_since: 4.3.1
            */
            return apply_filters( 'wpc_client_' . $column_name . '_custom_column_of_staff', $value );
        }
    }

	function no_items() {
		echo $this->no_items_message;
	}

	function set_sortable_columns( $args = array() ) {
		$return_args = array();
		foreach ( $args as $k => $val ) {
			if ( is_numeric( $k ) ) {
				$return_args[$val] = array($val, $val == $this->default_sorting_field);
			}
			else if ( is_string( $k ) ) {
				$return_args[$k] = array($val, $k == $this->default_sorting_field);
			}
			else {
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
		if ( count( $this->bulk_actions ) ) {
			$args = array_merge( array('cb' => '<input type="checkbox" />'), $args );
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

	function column_client( $item ) {
		$parent_client_id = $item['parent_client_id'];
		$client_name = '';
		if ( 0 < $parent_client_id ) {
			$client = get_userdata( $parent_client_id );
			if ( $client ) {
				$client_name = $client->get( 'user_login' );
			}
		}

		return $client_name;
	}

	function column_name( $item ) {
		return $item['first_name'] . ' ' . $item['last_name'];
	}

	function column_username( $item ) {
		$actions = $hide_actions = array();

		$actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=staff_edit&id=' . $item['id'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

		$actions['messege'] = '<a href="admin.php?page=wpclients_content&tab=private_messages&user_id=' . $item['id'] . '">' . __( 'Messages', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

		if ( !get_user_meta( $item['id'], 'wpc_temporary_password', true ) ) {
			$hide_actions['wpc_temp_password'] = '<a onclick=\'return confirm("' . sprintf( __( 'Do you want to mark the password as temporary for this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '");\' '
				. 'href="admin.php?page=wpclient_clients&tab=staff&action=temp_password&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'staff_temp_password' . $item['id'] . get_current_user_id() ) . '">' . __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
		}

		$hide_actions['wpc_capability'] = '<a href="#wpc_capability" data-id="' . $item['id'] . '_' . md5( 'wpc_client_staff' . SECURE_AUTH_SALT . $item['id'] ) . '" class="various_capabilities">' . __( 'Individual Capabilities', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

		if ( is_multisite() ) {
			$hide_actions['delete_from_blog'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '");\' href="admin.php?page=wpclient_clients&tab=staff&action=delete_from_blog&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_staff_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete From Blog', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
			$hide_actions['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '");\' href="admin.php?page=wpclient_clients&tab=staff&action=delete_mu&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_staff_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete From Network', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
		}
		else {
			$hide_actions['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '");\' href="admin.php?page=wpclient_clients&tab=staff&action=delete&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_staff_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
		}

		/* our_hook_
		  hook_name: wpc_client_more_actions_staff
		  hook_title: Add more actions on Client's Staff page
		  hook_description: Hook runs before display more actions on Client's Staff page.
		  hook_type: filter
		  hook_in: wp-client
		  hook_location clients_staff.php
		  hook_param: string $error
		  hook_since: 3.9.5
		 */
		$hide_actions = apply_filters( 'wpc_client_more_actions_clients_staff', $hide_actions );

		if ( count( $hide_actions ) ) {
			$actions['wpc_actions'] = WPC()->admin()->more_actions( $item['id'], __( 'Actions', WPC_CLIENT_TEXT_DOMAIN ), $hide_actions );
		}

		return sprintf( '%1$s %2$s', '<span id="staff_username_' . $item['id'] . '">' . $item['username'] . '</span>', $this->row_actions( $actions ) );
	}

	function wpc_set_pagination_args( $attr = array() ) {
		$this->set_pagination_args( $attr );
	}

	function extra_tablenav( $which ) {
		if ( 'top' == $which ) {

			/* >> filter by client for staff */
			$sc_text = sprintf( __( 'Filter by %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
			?>
			<p class="search-box tagsdiv">
				<label class="screen-reader-text" for="sc"><?php echo $sc_text; ?>:</label>
				<input type="search" id="sc" name="sc" value="<?php echo isset( $_REQUEST['sc'] ) ? esc_attr( wp_unslash( $_REQUEST['sc'] ) ) : ''; ?>" placeholder="<?php echo $sc_text; ?>" title="<?php echo $sc_text; ?>" class="newtag" />
			</p>
			<?php
			/* << filter by client for staff */

			$this->search_box( sprintf( __( 'Search %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ), 'search-submit' );
		}
	}

	/**
	 * filter by client for staff
	 * @global wpdb $wpdb
	 * @param string $sql
	 * @return string
	 */
	public function filter_clients_staff_sql( $sql ){
		global $wpdb;

		if( !empty($_REQUEST['sc']) ){
			$sql = preg_replace( array(
				'/(WHERE)/i',
				'/(WHERE)/i'
            ), array(
				"INNER JOIN {$wpdb->users} `uc` ON (`um4`.`meta_value` = `uc`.`ID`) $1",
				"$1 (`uc`.`user_login` LIKE '%{$_REQUEST['sc']}%' OR `uc`.`user_email` LIKE '%{$_REQUEST['sc']}%') AND"
            ), $sql);
		}

		return $sql;
	}

}

$ListTable = new WPC_Staff_List_Table( array(
	'singular' => WPC()->custom_titles['staff']['s'],
	'plural'	 => WPC()->custom_titles['staff']['p'],
	'ajax'		 => false
	) );

$per_page = WPC()->admin()->get_list_table_per_page( 'wpc_staffs_per_page' );
$paged = $ListTable->get_pagenum();


$bulk_actions = array(
	'temp_password' => __( 'Set Password as Temporary', WPC_CLIENT_TEXT_DOMAIN ),
);

$add_actions = array();
if ( is_multisite() ) {
	$add_actions = array(
		'delete'					 => __( 'Delete From Network', WPC_CLIENT_TEXT_DOMAIN ),
		'delete_from_blog' => __( 'Delete Delete From Blog Network', WPC_CLIENT_TEXT_DOMAIN ),
	);
}
else {
	$add_actions = array(
		'delete' => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
	);
}

$ListTable->set_bulk_actions( array_merge( $bulk_actions, $add_actions ) );

$add_column_custom_fields = $add_sort_columns = array();
$custom_fields = WPC()->custom_fields()->get_custom_fields_for_users( 'admin_screen', 'staff' );

if ( ! WPC()->flags['easy_mode'] ) {
    foreach ( $custom_fields as $key_cf => $val_cf ) {
        $custom_fields[ $key_cf ]['name']    = $key_cf;
        $add_column_custom_fields[ $key_cf ] = ( isset( $val_cf['title'] ) && '' != $val_cf['title'] ) ? $val_cf['title'] : __( 'Not Title', WPC_CLIENT_TEXT_DOMAIN );
        $add_sort_columns[ $key_cf ]         = preg_replace( '/^wpc_cf_/', '', $key_cf );
    }
}

$default_columns  = array(
    'username' => 'username',
    'name'     => 'name',
    'email'    => 'email',
    'client'   => 'client'
);
$sortable_columns = array_merge( $default_columns, $add_sort_columns );
/*our_hook_
hook_name: wpc_client_sortable_columns_of_staff
hook_title: Add more columns for sortable on Staff page
hook_description: Hook runs before set columns for sortable on Staff page.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $sortable_columns
hook_since: 4.3.1
*/
$sortable_columns = apply_filters( 'wpc_client_sortable_columns_of_staff', $sortable_columns );
$ListTable->set_sortable_columns( $sortable_columns );

$set_columns = array(
    'cb'       => '<input type="checkbox" />',
    'username' => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'name'     => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
    'email'    => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
    'client'   => sprintf( __( 'Assigned to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
);

$set_columns = array_merge( $set_columns, $add_column_custom_fields );
/*our_hook_
hook_name: wpc_client_columns_of_staff
hook_title: Add more columns on Staff page
hook_description: Hook runs before set columns on Staff page.
hook_type: filter
hook_in: wp-client
hook_location clients.php
hook_param: array $columns
hook_since: 4.3.1
*/
$set_columns = apply_filters( 'wpc_client_columns_of_staff', $set_columns );

$ListTable->custom_fields = $custom_fields;
$ListTable->set_columns( $set_columns );

$manager_clients = '';
if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
	$clients_ids = WPC()->members()->get_all_clients_manager();
	$manager_clients = " AND um4.meta_value IN ('" . implode( "','", $clients_ids ) . "')";
}

$sql = "SELECT count( u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'first_name'
    LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'last_name'
    LEFT JOIN {$wpdb->usermeta} um4 ON u.ID = um4.user_id AND um4.meta_key = 'parent_client_id'
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%s:16:\"wpc_client_staff\";%'
        {$not_approved}
        {$where_clause}
        {$manager_clients}
    ";
$items_count = $wpdb->get_var( apply_filters( 'clients_staff_sql', $sql ) );

$sql = "SELECT u.ID as id, u.user_login as username, u.user_email as email, um2.meta_value as first_name, um3.meta_value as last_name, um4.meta_value as parent_client_id
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'first_name'
    LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'last_name'
    LEFT JOIN {$wpdb->usermeta} um4 ON u.ID = um4.user_id AND um4.meta_key = 'parent_client_id'
    WHERE
        um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%s:16:\"wpc_client_staff\";%'
        {$not_approved}
        {$where_clause}
        {$manager_clients}
    ORDER BY $order_by
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";

$staff = $wpdb->get_results( apply_filters( 'clients_staff_sql', $sql ), ARRAY_A );

//add all custom fields
$user_ids = array_map( function ( $user ) {
    return $user['id'];
}, $staff );

$users_custom_fields = $wpdb->get_results( "SELECT user_id as id, meta_key as k, meta_value as val FROM {$wpdb->usermeta} WHERE user_id IN ('" . implode( "','", $user_ids ) . "') AND meta_key IN ('" . implode( "','", array_keys( $custom_fields ) ) . "')", ARRAY_A );

$new_array_cf = array();
foreach ( $users_custom_fields as $cf ) {
    $new_array_cf[ $cf['id'] ][ $cf['k'] ] = $cf['val'];
}

$staff = array_map( function ( $user ) use ( $new_array_cf ) {
    return isset( $new_array_cf[ $user['id'] ] ) ? array_merge( $user, $new_array_cf[ $user['id'] ] ) : $user;
}, $staff );
$ListTable->prepare_items();
$ListTable->items = $staff;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );
?>

<div class="wrap">

	<?php echo WPC()->admin()->get_plugin_logo_block() ?>

	<?php
	if ( isset( $_GET['msg'] ) ) {
		$msg = $_GET['msg'];
		switch ( $msg ) {
			case 'a':
				echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '</p></div>';
				break;
			case 'u':
				echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '</p></div>';
				break;
			case 'd':
				echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '</p></div>';
				break;
			case 'uf':
				echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
				break;
			case 'pass':
				echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The password marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) . '</p></div>';
				break;
			case 'pass_s':
				echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'The passwords marked as temporary for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ) . '</p></div>';
				break;
		}
	}
	?>

	<div class="wpc_clear"></div>

	<div id="wpc_container">

			<?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

		<span class="wpc_clear"></span>

		<div class="wpc_tab_container_block staff">

<?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_add_staff' ) ) { ?>
				<a class="add-new-h2" href="?page=wpclient_clients&tab=staff_add"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
<?php } ?>
<?php if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) { ?>
				<a class="add-new-h2 wpc_form_link" href="<?php echo get_admin_url() ?>admin.php?page=wpclients&tab=import-export" target="_blank"><?php _e( 'Import/Export', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
<?php } ?>

			<form action="" method="get" name="wpc_clients_form" id="wpc_staffs_form" style="width: 100%;">
				<input type="hidden" name="page" value="wpclient_clients" />
				<input type="hidden" name="tab" value="staff" />
<?php $ListTable->display(); ?>
			</form>
		</div>

		<script type="text/javascript">

			jQuery(document).ready(function () {

				//reassign file from Bulk Actions
				jQuery('#doaction2').click(function () {
					var action = jQuery('select[name="action2"]').val();
					jQuery('select[name="action"]').attr('value', action);

					return true;
				});


				//display staff capabilities
				jQuery('.various_capabilities').each(function () {
					var id = jQuery(this).data('id');

					jQuery(this).shutter_box({
						view_type: 'lightbox',
						width: '300px',
						type: 'ajax',
						dataType: 'json',
						href: '<?php echo get_admin_url() ?>admin-ajax.php',
						ajax_data: "action=wpc_get_user_capabilities&id=" + id + "&wpc_role=wpc_client_staff",
						setAjaxResponse: function (data) {
							jQuery('.sb_lightbox_content_title').html(data.title);
							jQuery('.sb_lightbox_content_body').html(data.content);

							if (jQuery('.sb_lightbox').height() > jQuery('#wpc_all_capabilities').height() + 70) {
								jQuery('.sb_lightbox').css('min-height', jQuery('#wpc_all_capabilities').height() + 70 + 'px').animate({
									'height': jQuery('#wpc_all_capabilities').height() + 70
								}, 500);
							}
						}
					});
				});


				// AJAX - Update Capabilities
				jQuery('body').on('click', '#update_wpc_capabilities', function () {
					var id = jQuery('#wpc_capability_id').val();
					var caps = {};

					jQuery('#wpc_all_capabilities input').each(function () {
						if (jQuery(this).is(':checked'))
							caps[jQuery(this).attr('name')] = jQuery(this).val();
						else
							caps[jQuery(this).attr('name')] = '';
					});

					var notice = jQuery('.wpc_ajax_result');

					notice.html('<div class="wpc_ajax_loading"></div>').show();
					jQuery('body').css('cursor', 'wait');
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo get_admin_url() ?>admin-ajax.php',
						data: 'action=wpc_update_capabilities&id=' + id + '&wpc_role=wpc_client_staff&capabilities=' + JSON.stringify(caps),
						dataType: "json",
						success: function (data) {
							jQuery('body').css('cursor', 'default');

							if (data.status) {
								notice.css('color', 'green');
							} else {
								notice.css('color', 'red');
							}
							notice.html(data.message);
							setTimeout(function () {
								notice.fadeOut(1500);
							}, 2500);

						},
						error: function (data) {
							notice.css('color', 'red').html('<?php echo esc_js( __( 'Unknown error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>');
							setTimeout(function () {
								notice.fadeOut(1500);
							}, 2500);
						}
					});
				});
			});
		</script>

	</div>

</div>