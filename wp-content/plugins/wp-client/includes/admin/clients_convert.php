<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
	WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
	WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

global $wpdb, $wp_roles, $role;

/*
* Convert to WP-Client's roles
*/

$wpc_roles = array( 'wpc_client' => WPC()->custom_titles['client']['s'] . ' (WPC Client)',
	'wpc_client_staff' => WPC()->custom_titles['staff']['s'] . ' (WPC Client Staff)',
	'wpc_manager' => WPC()->custom_titles['manager']['s'] . ' (WPC Manager)',
	'wpc_admin' => WPC()->custom_titles['admin']['s'] . ' (WPC Admin)'
);

/*our_hook_
	hook_name: wpc_client_convertible_user_roles
	hook_title: List of convertible roles
	hook_description: Filter add to list convertible roles
	hook_type: filter
	hook_in: wp-client
	hook_location clients_convert.php
	hook_param: array $wpc_roles
	hook_since: 3.7.5.2
*/
$wpc_roles = apply_filters( 'wpc_client_convertible_user_roles', $wpc_roles );
$exclude_roles = array_keys( $wpc_roles );
array_push( $exclude_roles, 'administrator' );
if ( isset( $_REQUEST['_wpnonce2'] ) && wp_verify_nonce( $_REQUEST['_wpnonce2'], 'wpc_convert_form' ) ) {
	if ( isset( $_REQUEST['convert_to'] ) && in_array( $_REQUEST['convert_to'], $exclude_roles ) ) {
		if ( isset( $_REQUEST['ids'] ) && is_array( $_REQUEST['ids'] ) && 0 < count( $_REQUEST['ids'] ) ) {
			$convert_to = $_REQUEST['convert_to'];
			$ids        = $_REQUEST['ids'];

			switch( $convert_to ) {
				case 'wpc_client':
					foreach( $ids as $user_id ) {
						$user_object = new WP_User( $user_id );
						if ( isset( $_REQUEST['save_role'] ) && 1 == $_REQUEST['save_role'] ) {
							//Save role
							$user_object->add_role( 'wpc_client' );
						} else {
							// replace role
							update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_client' => '1' ) );
						}

						$all_metafields = get_user_meta( $user_id, '', true );

						//get business name from feild
						$business_name = '';
						if ( isset($_REQUEST['business_name_field'] ) && '' !=  trim( $_REQUEST['business_name_field'] ) ) {
							/*$some_meta = get_user_meta( $user_id,  trim( $_REQUEST['business_name_field'] ), true );
							if ( '' != $some_meta ) {
									$business_name = $some_meta;
							}*/
							$business_name = $_REQUEST['business_name_field'];
							foreach( $all_metafields as $meta_key=>$meta_value ) {
								if ( isset( $all_metafields[$meta_key] ) && strpos( $_REQUEST['business_name_field'], '{' . $meta_key . '}' ) !== false ) {
									$metavalue = maybe_unserialize( $all_metafields[$meta_key][0] );
									$metavalue = ( isset( $metavalue ) && !empty( $metavalue ) ) ? $metavalue : '';

									$business_name = str_replace( '{' . $meta_key . '}', $metavalue, $business_name );
								}
							}

							if( $business_name == $_REQUEST['business_name_field'] ) {
								$business_name = '';
							}
						}

						//get business name from first_name
						if ( '' == $business_name ) {
							$first_name = get_user_meta( $user_id, 'first_name', true );
							if ( '' != $first_name ) {
								$business_name = $first_name;
							}
						}

						//get business name from user_login
						if ( '' == $business_name ) {
							$business_name = $user_object->get( 'user_login' );
						}

						//set business name
						update_user_meta( $user_id, 'wpc_cl_business_name', $business_name );


						//set Client Circles
						if ( isset( $_REQUEST['wpc_circles'] ) && is_string( $_REQUEST['wpc_circles'] ) && !empty( $_REQUEST['wpc_circles'] ) ) {
							$groups = explode( ',', $_REQUEST['wpc_circles'] );
							foreach ( $groups as $group_id ) {
								$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $group_id,  $user_id ) );
							}
						}

						update_user_option( $user_id, 'unqiue', md5( time() ) );

						//set manager
						if ( isset( $_REQUEST['wpc_managers'] ) && '' != $_REQUEST['wpc_managers'] ) {

							$assign_data = array();
							if( $_REQUEST['wpc_managers'] == 'all' ) {
								$args = array(
									'role'      => 'wpc_manager',
									'orderby'   => 'user_login',
									'order'     => 'ASC',
									'fields'    => array( 'ID' ),
								);

								$_REQUEST['wpc_managers'] = get_users( $args );
								foreach( $_REQUEST['wpc_managers'] as $key=>$value) {
										$assign_data[] = $value->ID;
								}
							} else {
								$assign_data = explode( ',', $_REQUEST['wpc_managers'] );
							}

							WPC()->assigns()->set_reverse_assigned_data( 'manager', $assign_data, 'client', $user_id );
							/* to delete
							foreach( $assign_data as $value ) {
									WPC()->assigns()->set_assigned_data( 'manager', $value, 'client', array( $user_id ) );
							}*/
						}



						//create HUB and portal
						$create_portal = false;
						if ( isset( $_REQUEST['create_client_page'] ) && 1 == $_REQUEST['create_client_page'] ) {
							$create_portal = true;
						}

						$args = array(
							'client_id' => $user_id,
							'business_name' => $business_name,
						);
						WPC()->pages()->create_hub_page( $args, $create_portal );

						/*our_hook_
						hook_name: wpc_client_convert_to_client
						hook_title: Convert to client
						hook_description: Hook runs when user converted to Client.
						hook_type: action
						hook_in: wp-client
						hook_location clients_convert.php
						hook_param: int $client_id
						hook_since: 4.1.6
						*/
						do_action( 'wpc_client_convert_to_client', $user_id );

						$user = get_userdata( $user_id );
						if( !empty( $user->user_email ) ) {
							$args = array( 'client_id' => $user_id );
							WPC()->mail( 'convert_to_client', $user->user_email, $args, 'convert_to_wp_user' );
						}
					}
					$msg = 'ac';

					break;

				case 'wpc_client_staff':
					foreach( $ids as $user_id ) {
						if ( isset( $_REQUEST['save_role'] ) && 1 == $_REQUEST['save_role'] ) {
							//Save role
							$user_object = new WP_User( $user_id );
							$user_object->add_role( 'wpc_client_staff' );
						} else {
							// replace role
							update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_client_staff' => '1' ) );
						}

						//assign Employee to client
						if ( isset( $_REQUEST['wpc_clients'] ) && 0 < $_REQUEST['wpc_clients'] ) {
							update_user_meta( $user_id, 'parent_client_id', $_REQUEST['wpc_clients'] );
						}

						$user = get_userdata( $user_id );
						if( !empty( $user->user_email ) ) {
							$args = array( 'client_id' => $user_id );
							WPC()->mail( 'convert_to_staff', $user->user_email, $args, 'convert_to_wp_user' );
						}
					}
					$msg = 'as';
					break;

				case 'wpc_manager':

					foreach( $ids as $user_id ) {

						if ( isset( $_REQUEST['save_role'] ) && 1 == $_REQUEST['save_role'] ) {
							//Save role
							$user_object = new WP_User( $user_id );
							$user_object->add_role( 'wpc_manager' );
							update_user_meta( $user_id, 'wpc_auto_assigned_clients', '0' );
						} else {
							// replace role
							update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_manager' => true ) );
							update_user_meta( $user_id, 'wpc_auto_assigned_clients', '0' );
						}

						//set manager for clients
						if ( isset( $_REQUEST['wpc_clients'] ) && !empty( $_REQUEST['wpc_clients'] ) ) {

							$assign_data = array();
							if( isset( $_POST['data'] ) && !empty( $_POST['data'] ) ) {
								if( $_POST['data'] == 'all' ) {
									$assign_data = WPC()->members()->get_client_ids();
								} else {
									$assign_data = explode( ',', $_POST['data'] );
								}

								WPC()->assigns()->set_assigned_data( 'manager', $user_id, 'client', $assign_data );
							}
						}

						$user = get_userdata( $user_id );
						if( !empty( $user->user_email ) ) {
							$args = array( 'client_id' => $user_id );
							WPC()->mail( 'convert_to_manager', $user->user_email, $args, 'convert_to_wp_user' );
						}
					}
					$msg = 'am';
					 break;

				case 'wpc_admin':
					foreach( $ids as $user_id ) {

						if ( isset( $_REQUEST['save_role'] ) && 1 == $_REQUEST['save_role'] ) {
							//Save role
							$user_object = new WP_User( $user_id );
							$user_object->add_role( 'wpc_admin' );
						} else {
							// replace role
							update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_admin' => true ) );
						}

						$user = get_userdata( $user_id );
						if( !empty( $user->user_email ) ) {
							$args = array( 'client_id' => $user_id );
							WPC()->mail( 'convert_to_admin', $user->user_email, $args, 'convert_to_wp_user' );
						}
					}
					$msg = 'aa';
					break;
				default:
					/*our_hook_
					hook_name: wpc_client_convert_user
					hook_title: Action that convert users by ID
					hook_description: Action that convert users by ID
					hook_type: action
					hook_in: wp-client
					hook_location clients_convert.php
					hook_param: string $convert_to, array $user_ids
					hook_since: 3.7.5.2
					*/
					do_action( 'wpc_client_convert_user', $convert_to, $ids );
					$msg = $convert_to;
					break;
			}

			WPC()->redirect( get_admin_url(). 'admin.php?page=wpclient_clients&tab=convert&msg=' . $msg );
		}

	}
}


if ( isset($_REQUEST['_wp_http_referer']) ) {
	$redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
	$redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=convert';
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
	WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
}

$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

//exclude WP Clients users
$exclude_users = array();
foreach( $exclude_roles as $val ) {
	$exclude_users = array_merge( $exclude_users, get_users( array( 'role' => $val, 'fields' => 'ID' ) ) );
}
$exclude_users = array_unique($exclude_users);
//$exclude_users = " AND u.ID NOT IN ('" . implode( "','", $exclude_users ) . "')";

//$order_by = 'u.user_registered';
$order_by = 'user_registered';
if ( isset( $_GET['orderby'] ) ) {
	switch( $_GET['orderby'] ) {
		case 'user_login' :
			//$order_by = 'u.user_login';
			$order_by = 'user_login';
			break;
		case 'nickname' :
			//$order_by = 'um.nickname';
			$order_by = 'user_nicename';
			break;
		case 'user_email' :
			//$order_by = 'u.user_email';
			$order_by = 'user_email';
			break;
	}
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Convert_Users_List_Table extends WP_List_Table {

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

	function column_username ( $item ) {
		return '<span id="assign_name_block_' . $item['ID'] . '">' .  $item['user_login'] . '</span>';
	}

	function column_role( $item ) {
		global $wp_roles;
		$roles_arr = $item['role'];
		$roles_str = '';
		if ( is_array( $roles_arr ) )
			foreach ( $roles_arr as $key => $value ) {
				$roles_str .= isset( $wp_roles->role_names[ $value ] ) ? translate_user_role( $wp_roles->role_names[ $value ] ) : $value;
				$roles_str .= ', ' ;
			}
		if ( '' != $roles_str )
			$roles_str = substr( $roles_str, 0, -2 );
		return $roles_str;
	}

	function column_cb( $item ) {
		if( isset( $_POST['selected_obj'] ) && is_string( $_POST['selected_obj'] ) ) {
			$obj_array = explode(',', $_POST['selected_obj']);
		} else {
			$obj_array = array();
		}
		$html = sprintf(
			'<span class="user_checkbox"><input type="checkbox" name="ids[]" value="%s" ', $item['ID']
		);
		if( in_array( $item['ID'], $obj_array ) )
			$html .= 'checked="checked"';
		$html .= '/></span';
		return $html;
	}


	function wpc_set_pagination_args( $attr = array() ) {
		$this->set_pagination_args( $attr );
	}

	function extra_tablenav( $which ){
		if ( 'top' == $which ) {
			$this->search_box( __( 'Search Users' , WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' );
		}
	}
}


$ListTable = new WPC_Convert_Users_List_Table( array(
	'singular'  => __( 'User', WPC_CLIENT_TEXT_DOMAIN ),
	'plural'    => __( 'Users', WPC_CLIENT_TEXT_DOMAIN ),
	'ajax'      => false
));

$per_page = WPC()->admin()->get_list_table_per_page( 'wpc_convert_users_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
	'username'          => 'user_login',
	'user_nicename'     => 'nickname',
	'user_email'        => 'user_email',
) );

$ListTable->set_bulk_actions(array(
));

$ListTable->set_columns(array(
	'cb'                => '<input type="checkbox" />',
	'username'          => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
	'user_nicename'     => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
	'user_email'        => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
	'role'              => __( 'Role', WPC_CLIENT_TEXT_DOMAIN ),
));


$args_count = array(
	'blog_id'      => get_current_blog_id(),
	'exclude'      => $exclude_users,
	'orderby'      => $order_by,
	'order'        => $order,
);

$args = array(
	'blog_id'      => get_current_blog_id(),
	'exclude'      => $exclude_users,
	'orderby'      => $order_by,
	'order'        => $order,
	'offset'       => ( $per_page * ( $paged - 1 ) ),
	'number'       => $per_page
);

if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
	$search_text = strtolower( trim( esc_sql( $_GET['s'] ) ) );
	$args_count['search'] = $args['search'] = '*' . $search_text . '*';
}

if( isset( $_REQUEST['role'] ) && !empty( $_REQUEST['role'] ) ) {
	$args_count['role'] = $_REQUEST['role'];
	$args['role'] = $_REQUEST['role'];
}

$items_count = get_users( $args_count );
$items_count = count( $items_count );

$convert_clients = get_users( $args );

foreach( $convert_clients as $key=>$convert_client ) {
	$convert_clients[$key] = (array)$convert_client->data;
	$convert_clients[$key]['role'] = $convert_client->roles;
}

$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_groups ORDER BY group_name ASC", ARRAY_A );

//get managers
$args = array(
	'role'      => 'wpc_manager',
	'orderby'   => 'user_login',
	'order'     => 'ASC',
);

$managers = get_users( $args );


//all clients
$excluded_clients = WPC()->members()->get_excluded_clients();
$args = array(
	'role'      => 'wpc_client',
	'exclude'   => $excluded_clients,
	'fields'    => array( 'ID', 'display_name' ),
	'orderby'   => 'user_login',
	'order'     => 'ASC',
);

$clients = get_users( $args );


$ListTable->prepare_items();
$ListTable->items = $convert_clients;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );
$wpnonce = wp_create_nonce( 'wpc_convert_form' );

$wpc_convert_users = WPC()->get_settings( 'convert_users' );

$business_name_field    = ( isset( $wpc_convert_users['client_business_name_field'] ) ) ? $wpc_convert_users['client_business_name_field'] : '{first_name}' ;


/*if ( isset( $wpc_convert_users['client_wpc_circles'] ) ) {
	$client_wpc_circles = $wpc_convert_users['client_wpc_circles'] ;
} else {
	$client_wpc_circles = array();
	$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_groups ORDER BY group_name ASC", ARRAY_A );
	if ( is_array( $groups ) && 0 < count( $groups ) ) {
		foreach ( $groups as $group ) {
			if( '1' == $group['auto_select'] ) {
				$client_wpc_circles[] = $group['group_id'];
			}
		}
	}
	$client_wpc_circles = implode( ',', $client_wpc_circles );
} */

$client_wpc_circles     = ( isset( $wpc_convert_users['client_wpc_circles'] ) && '' != $wpc_convert_users['client_wpc_circles'] ) ? explode( ',', $wpc_convert_users['client_wpc_circles'] ) : array();
$client_wpc_managers    = ( isset( $wpc_convert_users['client_wpc_managers'] ) && '' != $wpc_convert_users['client_wpc_managers'] ) ? explode( ',', $wpc_convert_users['client_wpc_managers'] ) : array();

$staff_wpc_clients      = ( isset( $wpc_convert_users['staff_wpc_clients'] ) && '' != $wpc_convert_users['staff_wpc_clients'] ) ? $wpc_convert_users['staff_wpc_clients'] : '';

$manager_wpc_clients    = ( isset( $wpc_convert_users['manager_wpc_clients'] ) && '' != $wpc_convert_users['manager_wpc_clients'] ) ? explode( ',', $wpc_convert_users['manager_wpc_clients'] ) : array();
$manager_wpc_circles    = ( isset( $wpc_convert_users['manager_wpc_circles'] ) && '' != $wpc_convert_users['manager_wpc_circles'] ) ? explode( ',', $wpc_convert_users['manager_wpc_circles'] ) : array();



$client_checked_create_pp   = ( isset( $wpc_convert_users['client_create_page'] ) && 'yes' == $wpc_convert_users['client_create_page'] ) ? 'checked' : '';
$client_checked_save_role   = ( isset( $wpc_convert_users['client_save_role'] ) && 'yes' == $wpc_convert_users['client_save_role'] ) ? 'checked' : '';
$staff_checked_save_role    = ( isset( $wpc_convert_users['staff_save_role'] ) && 'yes' == $wpc_convert_users['staff_save_role'] ) ? 'checked' : '';
$manager_checked_save_role  = ( isset( $wpc_convert_users['manager_save_role'] ) && 'yes' == $wpc_convert_users['manager_save_role'] ) ? 'checked' : '';
$admin_checked_save_role    = ( isset( $wpc_convert_users['admin_save_role'] ) && 'yes' == $wpc_convert_users['admin_save_role'] ) ? 'checked' : ''; ?>

<div class="wrap">

	<?php echo WPC()->admin()->get_plugin_logo_block() ?>

	<?php
		if ( isset( $_GET['msg'] ) ) {
			$msg = $_GET['msg'];
			switch( $msg ) {
				case 'ac':
					echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'User(s) <strong>Converted</strong> to Client(s) Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
					break;
				case 'as':
					echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'User(s) <strong>Converted</strong> to Staff(s) Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
					break;
				case 'am':
					echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'User(s) <strong>Converted</strong> to Manager(s) Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
					break;
				case 'aa':
					echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'User(s) <strong>Converted</strong> to Admin(s) Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
					break;
				default:
					if( in_array( $msg, $exclude_roles ) && isset( $wpc_roles[ $msg ] ) ) {
						echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'User(s) <strong>Converted</strong> to %s(s) Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $wpc_roles[ $msg ] ) . '</p></div>';
					}
					break;
			}
		}
	?>

	<div class="wpc_clear"></div>

	<div id="wpc_container">

		<?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>

		<div class="wpc_clear"></div>

		<div class="wpc_tab_container_block convert_users">
			<div class="wpc_clear"></div>

				<span class="description"><?php _e( "Note: Please test this first before converting a large number of users to be sure your intended result is achieved", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
				<div class="wpc_clear"></div>

			<?php if( $items_count > 0 ) { ?>
				<ul class="subsubsub">
					<li class="all"><a href="admin.php?page=wpclient_clients&tab=convert" <?php echo ( '' == $role ) ? 'class="current"' : '' ?>><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?><span class="count"> (<?php echo $items_count ?>)</span></a></li>
					<?php $users_of_blog = count_users();

					$args = array(
						'exclude'   => $exclude_users,
						'orderby'   => 'user_login',
						'order'     => 'ASC',
						'fields'    => 'ID',
						'blog_id'   => get_current_blog_id()
					);

					$user_ids_of_blog = get_users( $args );

					$role_counter = array();
					if( isset( $user_ids_of_blog ) && !empty( $user_ids_of_blog ) && isset( $users_of_blog['avail_roles'] ) && is_array( $users_of_blog['avail_roles'] ) ) {

						foreach( $user_ids_of_blog as $user_id ) {

							foreach( $users_of_blog['avail_roles'] as $convert_role => $num ) {
								if ( !in_array( $convert_role, $exclude_roles ) ) {
									if( user_can( $user_id, $convert_role ) ) {
										if( !isset( $role_counter[$convert_role] ) ) {
											$role_counter[$convert_role] = 0;
										}
										$role_counter[$convert_role]++;
									}
								}
							}

						}

					}


					if ( isset( $users_of_blog['avail_roles'] ) && is_array( $users_of_blog['avail_roles'] ) ) {
						$role_names = $wp_roles->get_names();
						foreach( $users_of_blog['avail_roles'] as $convert_role => $num ) {
							if ( !in_array( $convert_role, $exclude_roles ) ) {
								$class = ( $role == $convert_role ) ? 'class="current"' : '';
								if( isset( $role_counter[$convert_role] ) ) {
									echo ' | <li class="' . $role . '"><a href="admin.php?page=wpclient_clients&tab=convert&role=' . $convert_role . '" ' . $class . '>' . $role_names[$convert_role] . ' (' . $role_counter[$convert_role] . ')</a></li>';
								}
							}
						}

					} ?>
				</ul>
			<?php } ?>



			<form id="selected_form" method="post">
				<input type="hidden" name="selected_obj" value="" />
				<input type="hidden" name="selected_role" value="" />
			</form>


		   <form action="" method="get" name="wpc_clients_convert_form" id="wpc_clients_convert_form" style="width: 100%;">

				<input type="hidden" value="<?php echo $wpnonce ?>" name="_wpnonce2" id="_wpnonce2" />
				<input type="hidden" name="page" value="wpclient_clients" />
				<input type="hidden" name="tab" value="convert" />
				<?php $ListTable->display(); ?>

				<div class="alignleft actions">
					<span><?php _e( 'Convert to:', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
					<select name="convert_to" id="convert_to">
						<option value="-1"><?php _e( 'Select Role', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
						<?php foreach( $wpc_roles as $role_key=>$val ) { ?>
							<option value="<?php echo $role_key; ?>" <?php selected( isset( $_POST['selected_role'] ) ? $_POST['selected_role'] : '', $role_key ); ?>><?php echo $val; ?></option>
						<?php } ?>
					</select>
				</div>

				<?php if( !empty( $_POST['selected_role'] ) ) {
					switch( $_POST['selected_role'] ) {
						case 'wpc_client': { ?>
							<div id="for_wpc_client" style="display: block;">
								<table>
									<tr>
										<td colspan="2">
											<strong> <?php printf( __( '>>Select Options for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?>:</strong>
										</td>
									</tr>
									<tr>
										<td width="40"></td>
										<td>
											<table cellspacing="6">
												<tr>
													<td>
														<label for="business_name_field"><?php _e( 'Which User Meta Field Use For Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
														<br>
														<input type="text" name="business_name_field" id="business_name_field" value="<?php echo $business_name_field ?>" />
														<span class="description"><?php _e( 'by default "first_name", or "user_login" if meta values and "first_name" is empty.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
													</td>
												</tr>

												<?php if ( is_array( $groups ) && 0 < count( $groups ) ) { ?>
													<tr>
														<td>
															<?php $selected_groups = array();

															if( isset( $_REQUEST['wpc_circles'] ) && count( $_REQUEST['wpc_circles'] ) ) {
																$selected_groups = is_array( $_REQUEST['wpc_circles'] ) ? $_REQUEST['wpc_circles'] : array();
															} else {
																if( isset( $client_wpc_circles ) && 0 < count( $client_wpc_circles ) ) {
																	$selected_groups = $client_wpc_circles;
																} else {
																	foreach ( $groups as $group ) {
																		if( '1' == $group['auto_select'] ) {
																			$selected_groups[] = $group['group_id'];
																		}
																	}
																}
															}

															$link_array = array(
																'title'   => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
																'text'    => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] )
															);
															$input_array = array(
																'name'  => 'wpc_circles',
																'id'    => 'wpc_circles',
																'value' => implode( ',', $selected_groups )
															);
															$additional_array = array(
																'counter_value' => count( $selected_groups )
															);
															WPC()->assigns()->assign_popup( 'circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
														</td>
													</tr>
												<?php } ?>

												<?php if ( is_array( $managers ) && 0 < count( $managers ) ) { ?>
													<tr>
														<td>
															<?php $selected_managers = array();

															if( isset( $_REQUEST['wpc_managers'] ) && count( $_REQUEST['wpc_managers'] ) ) {
																$selected_managers = is_array( $_REQUEST['wpc_managers'] ) ? $_REQUEST['wpc_managers'] : array();
															} else {
																if( isset( $client_wpc_managers ) && 0 < count( $client_wpc_managers ) ) {
																	$selected_managers = $client_wpc_managers;
																}
															}

															$link_array = array(
																'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
																'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['p']
															);
															$input_array = array(
																'name'  => 'wpc_managers',
																'id'    => 'wpc_managers',
																'value' => implode( ',', $selected_managers )
															);
															$additional_array = array(
																'counter_value' => count( $selected_managers )
															);
															WPC()->assigns()->assign_popup( 'manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
														</td>
													</tr>
												<?php } ?>
												<tr>
													<td>
														<label for="create_client_page"><input type="checkbox" name="create_client_page" id="create_client_page" value="1" <?php echo $client_checked_create_pp ?> /> <?php printf( __( 'Create %s', WPC_CLIENT_TEXT_DOMAIN ) , WPC()->custom_titles['portal_page']['s'] ) ?></label>
													</td>
												</tr>
												<tr>
													<td>
														<label for="save_role"><input type="checkbox" name="save_role" id="save_role" value="1" <?php echo $client_checked_save_role ?> /> <?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
														<br>
														<span class="description"><?php printf( __( "If checked, the user's current role will be saved, but user will also take on characteristics of the %s role.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
													</td>
												</tr>
												<tr>
													<td>
														<input type="button" value="<?php printf( __( 'Convert to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?>" class="button-secondary action" name="convert" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
							<?php break;
						}
						case 'wpc_client_staff': { ?>
							<div id="for_wpc_client_staff" style="display: block;" >
								<table>
									<tr>
										<td colspan="2">
											<strong> <?php printf( __( '>> Select Options for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) ?></strong>
										</td>
										</tr>
									<tr>
										<td width="40"></td>
										<td>
											<table cellspacing="6">
												<tr>
													<td>
														<?php $selected_client = '';
														if( isset( $_REQUEST['wpc_clients'] ) && !empty( $_REQUEST['wpc_clients'] ) ) {
															$selected_client = $_REQUEST['wpc_clients'];
														} else {
															$selected_client = $staff_wpc_clients;
														}

														$link_array = array(
															'title'         => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
															'text'          => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
															'data-marks'    => 'radio'
														);
														$input_array = array(
															'name'  => 'wpc_clients',
															'id'    => 'wpc_clients',
															'value' => $selected_client
														);
														$additional_array = array(
															'counter_value' => ( $selected_client ) ? get_userdata( $selected_client )->user_login : ''
														);
														WPC()->assigns()->assign_popup( 'client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
													</td>
												</tr>
												<tr>
													<td>
														<label for="save_role"><input type="checkbox" name="save_role" id="save_role" value="1" <?php echo $staff_checked_save_role ?> /> <?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
														<br>
														<span class="description"><?php printf( __( "If checked, the user's current role will be saved, but user will also take on characteristics of the %s role.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
													</td>
												</tr>
												<tr>
													<td>
														<input type="button" value="<?php printf( __( 'Convert to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ) ?>" class="button-secondary action" name="convert" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
							<?php break;
						}
						case 'wpc_manager': { ?>
							<div id="for_wpc_manager" style="display: block;">
								<table>
									<tr>
										<td colspan="2">
											<strong> <?php printf( __( '>> Select Options for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) ?></strong>
										</td>
										</tr>
									<tr>
										<td width="40"></td>
										<td>
											<table cellspacing="6">
												<tr>
													<td>
														<?php $selected_clients = array();

														if( isset( $_REQUEST['wpc_clients'] ) && count( $_REQUEST['wpc_clients'] ) ) {
															$selected_clients = is_array( $_REQUEST['wpc_clients'] ) ? $_REQUEST['wpc_clients'] : array();
														} else {
															if( isset( $manager_wpc_clients ) && 0 < count( $manager_wpc_clients ) ) {
																$selected_clients = $manager_wpc_clients;
															}
														}

														$link_array = array(
															'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
															'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
														);
														$input_array = array(
															'name'  => 'wpc_clients',
															'id'    => 'wpc_clients',
															'value' => implode( ',', $selected_clients )
														);
														$additional_array = array(
															'counter_value' => count( $selected_clients )
														);
														WPC()->assigns()->assign_popup( 'client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
													</td>
												</tr>

												<?php if ( is_array( $groups ) && 0 < count( $groups ) ) { ?>
													<tr>
														<td>
															<?php $selected_groups = array();

															if( isset( $_REQUEST['wpc_circles'] ) && count( $_REQUEST['wpc_circles'] ) ) {
																$selected_groups = is_array( $_REQUEST['wpc_circles'] ) ? $_REQUEST['wpc_circles'] : array();
															} else {
																if( isset( $manager_wpc_circles ) && 0 < count( $manager_wpc_circles ) ) {
																	$selected_groups = $manager_wpc_circles;
																} else {
																	foreach ( $groups as $group ) {
																		if( '1' == $group['auto_select'] ) {
																			$selected_groups[] = $group['group_id'];
																		}
																	}
																}
															}

															$link_array = array(
																'title'   => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'], WPC()->custom_titles['circle']['p'] ),
																'text'    => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'], WPC()->custom_titles['circle']['p'] )
															);
															$input_array = array(
																'name'  => 'wpc_circles',
																'id'    => 'wpc_circles',
																'value' => implode( ',', $selected_groups )
															);
															$additional_array = array(
																'counter_value' => count( $selected_groups )
															);
															WPC()->assigns()->assign_popup( 'circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array ); ?>
														</td>
													</tr>
												<?php } ?>

												<tr>
													<td>
														<label for="save_role"><input type="checkbox" name="save_role" id="save_role" value="1" <?php echo $manager_checked_save_role ?> /> <?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
														<br>
														<span class="description"><?php printf( __( "If checked, the user's current role will be saved, but user will also take on characteristics of the %s role.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
													</td>
												</tr>
												<tr>
													<td>
														<input type="button" value="<?php printf( __( 'Convert to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ) ?>" class="button-secondary action" name="convert" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
							<?php break;
						}
						case 'wpc_admin': { ?>
							<div id="for_wpc_admin" style="display: block;">
								<table>
									<tr>
										<td colspan="2">
											<strong> <?php printf( __( '>> Select Options for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) ?></strong>
										</td>
										</tr>
									<tr>
										<td width="40"></td>
										<td>
											<table cellspacing="6">
												<tr>
													<td>
														<label for="save_role"><input type="checkbox" name="save_role" id="save_role" value="1" <?php echo $admin_checked_save_role ?> /> <?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
														<br>
														<span class="description"><?php printf( __( "If checked, the user's current role will be saved, but user will also take on characteristics of the %s role.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
													</td>
												</tr>
												<tr>
													<td>
														<input type="button" value="<?php printf( __( 'Convert to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) ?>" class="button-secondary action" name="convert" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
							<?php break;
						}
						default: {
							if( in_array( $_POST['selected_role'], $exclude_roles ) ) {
								/*our_hook_
									hook_name: wpc_client_convert_user_settings
									hook_title: Convert user settings
									hook_description: Filter add settings for convert user
									hook_type: filter
									hook_in: wp-client
									hook_location clients_convert.php
									hook_param: string $html, string $role
									hook_since: 3.7.5.2
								*/
								echo apply_filters( 'wpc_client_convert_user_settings', '', $_POST['selected_role'] );
							}
						}
					}
				} ?>
			</form>


			<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery(".over").hover(function(){
						jQuery(this).css("background-color","#bcbcbc");
						},function(){
						jQuery(this).css("background-color","transparent");
					});



					//show reassign cats
					jQuery( '#convert_to' ).change( function() {
						/*jQuery( '#for_wpc_client' ).hide();
						jQuery( '#for_wpc_client_staff' ).hide();
						jQuery( '#for_wpc_manager' ).hide();

						if ( '-1' != jQuery( this ).val() ) {
							jQuery( '#for_' + jQuery( this ).val() ).slideToggle( 'slow' );
//                            jQuery( '#for_' + jQuery( this ).val() ).show();
						}*/

						var selected_obj = '';
						jQuery('span.user_checkbox input[name="ids[]"]:checked').each(function() {
							if ('undefined' != typeof(jQuery( this ).val()))
								selected_obj += ',' + jQuery( this ).val();
						});

						jQuery("#selected_form input[name=selected_role]").val( jQuery( this ).val() );
						jQuery("#selected_form input[name=selected_obj]").val( selected_obj.substr(1) );
						jQuery("#selected_form").submit();

						return false;
					});

					//Send convert data
					jQuery( 'input[name="convert"]' ).click( function() {
						jQuery( '#for_wpc_client:hidden' ).remove();
						jQuery( '#for_wpc_client_staff:hidden' ).remove();
						jQuery( '#for_wpc_manager:hidden' ).remove();

						jQuery( '#wpc_clients_convert_form' ).submit();
						return false;
					});

				});

			</script>


		</div>

	</div>

</div>