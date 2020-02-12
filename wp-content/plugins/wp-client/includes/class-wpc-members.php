<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists('WPC_Members') ) :

final class WPC_Members {

    /**
     * The single instance of the class.
     *
     * @var WPC_Members
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Members is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Members - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

    }



    /*
     * get excluded clients
     *
     * @param bool|string $what
     *
     * @return array
     */
    function get_excluded_clients( $what = false ) {
        $excluded_clients = array();
        if ( 'to_approve' == $what ) {
            $excluded_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'to_approve', 'meta_value' => '1', 'fields' => 'ID' ) );
        } else if ( 'archive' == $what ) {
            $excluded_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'archive', 'meta_value' => '1', 'fields' => 'ID' ) );
        } else if ( !$what ) {
            $archive_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'archive', 'meta_value' => '1', 'fields' => 'ID' ) );
            $not_approve_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'to_approve', 'meta_value' => '1', 'fields' => 'ID' ) );
            $excluded_clients = array_merge( $archive_clients, $not_approve_clients );
        }
        return $excluded_clients;
    }

    public function get_client_id() {
        return isset( WPC()->current_plugin_page['client_id'] ) ? WPC()->current_plugin_page['client_id'] : '';
    }


    /*
     * Auto convert uer roles to our roles
     *
     * @param int $user_id
     * @param string $new_role
     *
     * @return void
     */
    function auto_convert_user_register( $user_id, $new_role ) {
        global $wpdb;

        $current_rule = array();

        update_user_meta( $user_id, 'wpc_first_time_entered', 1 );

        $user_object = new WP_User( $user_id );

        $user_roles = get_user_meta( $user_id, $wpdb->prefix . 'capabilities', true ) ;

        if ( is_array( $user_roles ) ) {
            //don't convert WPC Role user
            foreach( $user_roles as $key => $role ) {
                if ( $role && ( 'wpc_' == substr( $key, 0, 4 ) || 'administrator' == $key ) )
                    return;
            }
        }


        $wpc_auto_convert_rules = WPC()->get_settings( 'auto_convert_rules' );

        //do auto-convert
        if ( isset( $wpc_auto_convert_rules ) && is_array( $wpc_auto_convert_rules ) ) {

            //convert all roles except our roles and administrator
            if ( isset( $wpc_auto_convert_rules['__all_roles'] ) ) {
                $current_rule = $wpc_auto_convert_rules['__all_roles'];
            } else {
                //convert just roles for which exists rules
                if( is_array( $user_roles ) ) {
                    //convert just roles for which exists rules
                    foreach( $user_roles as $key => $role ) {
                        if ( isset( $wpc_auto_convert_rules[$key] ) ) {
                            $current_rule = $wpc_auto_convert_rules[$key];
                            break;
                        }
                    }
                }
            }
        }

        //do not need convert
        if ( !$current_rule )
            return;


        switch( $current_rule['to_role'] ) {
            case 'wpc_client':
                if( isset( $current_rule['save_role'] ) && 'yes' == $current_rule['save_role'] ) {
                    //Save role
                    $user_object->add_role( 'wpc_client' );
                } else {
                    // replace role
                    update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_client' => '1' ) );
                }

                //get business name from feild
                $business_name = '';
                if ( isset( $current_rule['business_name_field'] ) && '' !=  trim( $current_rule['business_name_field'] ) ) {
                    $all_metafields = get_user_meta( $user_id, '', true );

                    $business_name = $current_rule['business_name_field'];
                    foreach( $all_metafields as $meta_key=>$meta_value ) {
                        if ( isset( $all_metafields[$meta_key] ) && strpos( $current_rule['business_name_field'], '{' . $meta_key . '}' ) !== false ) {
                            $metavalue = maybe_unserialize( $all_metafields[$meta_key][0] );
                            $metavalue = ( isset( $metavalue ) && !empty( $metavalue ) ) ? $metavalue : '';

                            $business_name = str_replace( '{' . $meta_key . '}', $metavalue, $business_name );
                        }
                    }

                    if( $business_name == $current_rule['business_name_field'] ) {
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


                update_user_option( $user_id, 'unqiue', md5( time() ) );


                //assign to circles & managers
                if( isset( $current_rule['wpc_circles'] ) && !empty( $current_rule['wpc_circles'] ) ) {
                    $current_rule['wpc_circles'] = explode( ',', $current_rule['wpc_circles'] );

                    if( is_array( $current_rule['wpc_circles'] ) ) {
                        foreach ( $current_rule['wpc_circles'] as $circle_id ) {
                            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $circle_id,  $user_id ) );
                        }
                    }
                }

                $manager_ids = array();
                if( isset( $current_rule['wpc_managers'] ) && !empty( $current_rule['wpc_managers'] ) ) {
                    $current_rule['wpc_managers'] = explode( ',', $current_rule['wpc_managers'] );
                    if( is_array( $current_rule['wpc_managers'] ) ) {
                        $manager_ids = array_merge( $manager_ids, $current_rule['wpc_managers'] );
                    }
                }

                //get managers with auto-assigned option
                $args = array(
                    'role'          => 'wpc_manager',
                    'meta_key'      => 'wpc_auto_assigned_clients',
                    'meta_value'    => '1',
                    'fields'        => 'ID'
                );
                $auto_assigned_managers = get_users( $args );
                if( is_array( $auto_assigned_managers ) ) {
                    $manager_ids = array_merge( $manager_ids, $auto_assigned_managers );
                }
                WPC()->assigns()->set_reverse_assigned_data( 'manager', $manager_ids, 'client', $user_id );


                //create HUB and portal
                $args = array(
                    'client_id' => $user_id,
                    'business_name' => $business_name,
                );

                $create_portal = ( isset( $current_rule['create_page'] ) && 'yes' == $current_rule['create_page'] ) ? true : false;

                WPC()->pages()->create_hub_page( $args, $create_portal );


                $user = get_userdata( $user_id );
                if( !empty( $user->user_email ) ) {
                    $args = array( 'client_id' => $user_id );
                    WPC()->mail( 'convert_to_client', $user->user_email, $args, 'convert_to_wp_user' );
                }

                break;
            case 'wpc_client_staff':
                if( isset( $current_rule['save_role'] ) && 'yes' == $current_rule['save_role'] ) {
                    //Save role
                    $user_object->add_role( 'wpc_client_staff' );
                } else {
                    // replace role
                    update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_client_staff' => '1' ) );
                }

                //assign Employee to client
                if ( isset( $current_rule['wpc_clients'] ) && 0 < $current_rule['wpc_clients'] ) {
                    update_user_meta( $user_id, 'parent_client_id', $current_rule['wpc_clients'] );
                }

                $user = get_userdata( $user_id );
                if( !empty( $user->user_email ) ) {
                    $args = array( 'client_id' => $user_id );
                    WPC()->mail( 'convert_to_staff', $user->user_email, $args, 'convert_to_wp_user' );
                }

                break;
            case 'wpc_manager':
                if( isset( $current_rule['save_role'] ) && 'yes' == $current_rule['save_role'] ) {
                    //Save role
                    $user_object->add_role( 'wpc_manager' );
                    update_user_meta( $user_id, 'wpc_auto_assigned_clients', '0' );
                } else {
                    // replace role
                    update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( 'wpc_manager' => true ) );
                    update_user_meta( $user_id, 'wpc_auto_assigned_clients', '0' );
                }

                //set manager for clients
                if ( !empty( $current_rule['wpc_clients'] ) ) {
                    if( $current_rule['wpc_clients'] == 'all' ) {
                        $assign_data = $this->get_client_ids();
                    } else {
                        $assign_data = explode( ',', $current_rule['wpc_clients'] );
                    }

                    WPC()->assigns()->set_assigned_data( 'manager', $user_id, 'client', $assign_data );
                }

                //set manager for circles
                if ( isset( $current_rule['wpc_circles'] ) && !empty( $current_rule['wpc_circles'] ) ) {
                    $assign_data = explode( ',', $current_rule['wpc_circles'] );

                    if( is_array( $assign_data ) ) {
                        WPC()->assigns()->set_assigned_data( 'manager', $user_id, 'circle', $assign_data );
                    }
                }


                $user = get_userdata( $user_id );
                if( !empty( $user->user_email ) ) {
                    $args = array( 'client_id' => $user_id );
                    WPC()->mail( 'convert_to_manager', $user->user_email, $args, 'convert_to_wp_user' );
                }

                break;
            case 'wpc_admin':
                if( isset( $current_rule['save_role'] ) && 'yes' == $current_rule['save_role'] ) {
                    //Save role
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

                break;

            default:
                if( isset( $current_rule['save_role'] ) && 'yes' == $current_rule['save_role'] ) {
                    //Save role
                    $user_object->add_role( $current_rule['to_role'] );
                } else {
                    // replace role
                    update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( $current_rule['to_role'] => '1' ) );
                }

                break;
        }


        /*our_hook_
            hook_name: wpc_client_auto_convert_user
            hook_title: Hook after auto convert user
            hook_description:
            hook_type: action
            hook_in: wp-client
            hook_location class.common.php
            hook_param: string $wpc_role, int $user_id
            hook_since: 3.7.8.1
        */

        do_action( 'wpc_client_auto_convert_user', $current_rule['to_role'], $user_id );
    }


    /**
     *
     *  Function for get client managers
     *
     */
    function get_client_managers( $client_id, $from = 'all' ) {

        if( $from == 'individual' ) {

            $response = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client_id );

        } elseif( $from == 'circle' ) {

            $groups_client = WPC()->groups()->get_client_groups_id( $client_id );

            $response = array();
            if ( 0 < count( $groups_client ) ) {
                $add_assign_id = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'circle', $groups_client );
                $response = array_merge( $response, $add_assign_id );
            }

            $response = array_unique( $response );

        } else {

            $response = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client_id );

            $groups_client = WPC()->groups()->get_client_groups_id( $client_id );

            if ( 0 < count( $groups_client ) ) {
                $add_assign_id = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'circle', $groups_client );
                $response = array_merge( $response, $add_assign_id );
            }

            $response = array_unique( $response );
        }

        return apply_filters( 'wpc_get_client_managers', $response, $client_id, $from );
    }

	/**
     * Funtion returns client list for manager
     *
	 * @param int $manager_id
	 * @param string $from
	 *
	 * @return array
	 */
    function get_manager_clients( $manager_id, $from = 'all' ) {
        if( $from == 'individual' ) {
            $response = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'client' );
        } elseif( $from == 'circle' ) {
            $response = array();
            $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'circle' );
            foreach ( $manager_circles as $circle_id ) {
                $response = array_merge( $response, WPC()->groups()->get_group_clients_id( $circle_id ) );
            }
            $response = array_unique( $response );
        } else {
            $response = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'client' );
            $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'circle' );
            foreach ( $manager_circles as $circle_id ) {
                $response = array_merge( $response, WPC()->groups()->get_group_clients_id( $circle_id ) );
            }
            $response = array_unique( $response );
        }

        return apply_filters( 'wpc_get_manager_clients', $response, $manager_id, $from );
    }


    /*
    * get clients ids from popups
    */
    function get_clients_from_popups( $clients = '', $circles = '' ) {

        //for clients
        if( 'all' == $clients )     {
            $selected_clients = $this->get_client_ids();
        } elseif ( '' == $clients ) {
            $selected_clients = array();
        } else {
            $selected_clients = explode( ',', $clients );
        }

        //for circle
        if( 'all' == $circles ) {
            $selected_circles = WPC()->groups()->get_group_ids();
        } elseif ( '' == $circles ) {
            $selected_circles = array();
        } else {
            $selected_circles = explode( ',', $circles );
        }

        //get client from circles
        if ( count( $selected_circles ) ) {
            $clients_from_circles = array();
            foreach ( $selected_circles as $id_group ) {
                $add_client = WPC()->groups()->get_group_clients_id( $id_group );
                $clients_from_circles = array_merge( $clients_from_circles, $add_client );
            }
            $clients_from_circles = array_unique( $clients_from_circles );
        }


        if ( isset( $clients_from_circles ) && count( $clients_from_circles )  ) {
            $selected_clients = array_merge( $clients_from_circles, $selected_clients );
        }

        $selected_clients = array_unique( $selected_clients );

        return $selected_clients;
    }


    function get_client_ids() {
        //all clients
        $excluded_clients = $this->get_excluded_clients();
        $args = array(
            'role'      => 'wpc_client',
            'exclude'   => $excluded_clients,
            'fields'    => array( 'ID' ),
            'orderby'   => 'user_login',
            'order'     => 'ASC',
        );

        $clients = get_users( $args );
        $clients_array = array();
        foreach( $clients as $client ) {
            $clients_array[] = $client->ID;
        }

        return $clients_array;
    }


    /**
     * Function to archive wpc-client
     *
     * @param int $user_id id of archiving user.
     */
    function archive_client( $user_id ) {
        $user = get_userdata( $user_id );
        if ( isset( $user ) && in_array ( 'wpc_client', $user->roles ) ) {
            /*our_hook_
                hook_name: wpc_client_archive_client
                hook_title: Archive client process
                hook_description: Hook starts before setup to client archive flag
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $user_id
                hook_since: 4.4.6
            */
            do_action( 'wpc_client_archive_client', $user_id );
            update_user_meta( $user_id, 'archive', '1' );
        }
    }

    /*
    * add & update the wp client as users
    */
    function client_update_func( $userdata ) {
        global $wpdb;

        //get custom fields
        if ( isset( $userdata['custom_fields'] ) ) {
            $custom_fields = $userdata['custom_fields'];
            unset( $userdata['custom_fields'] );
        }

        //import: get client circles
        $import_circles = array();
        if ( isset( $userdata['client_circles'] ) ) {
            $import_circles = $userdata['client_circles'];
            unset( $userdata['client_circles'] );
        }

        //temporary password
        $temp_password = false;
        if( !empty( $userdata['user_pass'] ) ) {
            if ( isset( $userdata['ID'] ) ) {
                delete_user_meta( $userdata['ID'], 'wpc_temporary_password');
            }
            if( !empty( $userdata['temp_password'] ) ) {
                $temp_password = true;
            }
            unset( $userdata['temp_password'] );
        }

        //import: get client file categories
        $import_categories = array();
        if ( isset( $userdata['client_file_categories'] ) ) {
            $import_categories = $userdata['client_file_categories'];
            unset( $userdata['client_file_categories'] );
        }

        if ( !isset( $userdata['ID'] ) ) {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

            // insert new user
            $new_user = wp_insert_user($userdata);

            if ( isset( $wpc_clients_staff['auto_login_after_registration'] ) && 'yes' == $wpc_clients_staff['auto_login_after_registration'] ) {
                $this->_set_auth_cookie( $new_user );
            }

            //add Client Circles auto assign
            if ( isset( $userdata['self_registered'] ) && 1 == $userdata['self_registered'] ) {
                $add_groups = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_self = 1" );
            } else {
                $add_groups = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_manual = 1" );
            }

            $import_circles = array_merge( $import_circles, $add_groups ) ;

            if ( isset( $_REQUEST['wpc_circles'] ) && is_string( $_REQUEST['wpc_circles'] ) && 0 < $new_user && '' != $_REQUEST['wpc_circles'] ) {
                $import_circles = array_merge( $import_circles, explode( ',', $_REQUEST['wpc_circles'] ) );
            }

            $import_circles = array_unique( $import_circles ) ;

            //import: add client circles
            if ( 0 < $new_user ) {
                foreach ( $import_circles as $circle_id ) {
                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $circle_id,  $new_user ) );
                }
            }

            WPC()->assigns()->set_reverse_assigned_data( 'file_category', $import_categories, 'client', $new_user );

            update_user_option( $new_user, 'contact_phone', $userdata['contact_phone'], false );
            update_user_option( $new_user, 'unqiue', md5( time() ) );

            //set business name
            if ( isset( $userdata['business_name'] ) ) {
                update_user_meta( $new_user, 'wpc_cl_business_name', $userdata['business_name'] );
            }

            //save custom fileds
            if ( isset( $custom_fields ) && 0 < count( $custom_fields ) ) {
                $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

                foreach( $custom_fields as $key => $value ) {
                    if ( isset( $wpc_custom_fields[$key]['type'] ) && 'file' == $wpc_custom_fields[$key]['type'] ) {
                        //for file custom field
                        if ( !empty( $value['name'] ) ) {
                            $new_name = basename( rand( 0000, 9999 ) . $value['name'] );
                            $filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $new_name;

                            if ( move_uploaded_file( $value['tmp_name'], $filepath ) ) {
                                update_user_meta( $new_user, $key, array( 'origin_name' => $value['name'], 'filename' => $new_name ) );
                            }
                        }
                    } else {
                        update_user_meta( $new_user, $key, $value );
                    }

                    //set value to related user_meta with this custom field
                    if ( isset( $wpc_custom_fields[$key]['relate_to'] ) && '' != trim( $wpc_custom_fields[$key]['relate_to'] ) ) {
                        update_user_meta( $new_user, trim( $wpc_custom_fields[$key]['relate_to'] ), $value );
                    }
                }
            }

            $assign_data = array();
            if ( isset( $userdata['admin_manager'] ) && '' != $userdata['admin_manager'] ) {

                if( $userdata['admin_manager'] == 'all' ) {
                    $args = array(
                        'role'      => 'wpc_manager',
                        'orderby'   => 'user_login',
                        'order'     => 'ASC',
                        'fields'    => array( 'ID' ),
                    );

                    $userdata['admin_manager'] = get_users( $args );
                    foreach( $userdata['admin_manager'] as $key=>$value) {
                        $assign_data[] = $value->ID;
                    }
                } else {
                    $assign_data = explode( ',', $userdata['admin_manager'] );
                }

            }

            //get managers with auto-assigned option
            $args = array(
                'role'       => 'wpc_manager',
                'meta_key'   => 'wpc_auto_assigned_clients',
                'meta_value' => '1',
                'fields'     => 'ID'

            );


            //auto assigned new user to managers
            $assigned_managers = array_unique( array_merge( $assign_data, get_users( $args ) ) );

            WPC()->assigns()->set_reverse_assigned_data( 'manager', $assigned_managers, 'client', $new_user );


            //create HUB and portal
            if ( ! ( isset( $userdata['to_approve'] ) && '1' == $userdata['to_approve'] ) ) {
                $create_portal = false;
                if ( ! isset( $wpc_clients_staff['create_portal_page'] ) || 'yes' == $wpc_clients_staff['create_portal_page'] ) {
                    $create_portal = true;
                }

                $args = array(
                    'client_id' => $new_user,
                    'business_name' => $userdata['business_name'],
                );
                WPC()->pages()->create_hub_page( $args, $create_portal );
            }

            //when import client admin can turn off send verify email to clients
            if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] && !( isset( $userdata['verify_email'] ) && '0' == $userdata['verify_email'] ) ) {
                $key = md5( $new_user . time() );
                update_user_meta( $new_user, 'verify_email_key', $key );

                //make link
                if ( WPC()->permalinks ) {
                    $link = WPC()->make_url( '/portal/acc-activation/' . $key, get_home_url() );
                } else {
                    $link = add_query_arg( array( 'wpc_page' => 'acc_activation', 'wpc_page_value' => $key ), get_home_url() );
                }
                $args = array( 'client_id' => $new_user, 'verify_url' => $link );
                //send email
                WPC()->mail( 'new_client_verify_email', $userdata['user_email'], $args, 'new_client' );

            }
            if ( !( isset( $userdata['to_approve'] ) && '1' == $userdata['to_approve'] ) ) {
                $link = WPC()->get_hub_link();
            } else {
                $link = WPC()->get_login_url();
            }


            $client_id = $new_user;

            //for assign avatar
            if( isset( $userdata['avatar'] ) && !empty( $userdata['avatar'] ) && strpos( $userdata['avatar'], 'temp_' ) === 0 ) {
                $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );

                if( file_exists( $avatars_dir . $userdata['avatar'] ) ) {

                    //delete temp files
                    $files = scandir( $avatars_dir );
                    $current_time = time();
                    foreach( $files as $file ) {
                        if( $file != "." && $file != ".." ) {
                            if( file_exists( $avatars_dir . DIRECTORY_SEPARATOR . $file ) ) {
                                if( strpos( $file, 'temp_' ) === 0 ) {
                                    $name_array = explode( '_', $file );
                                    if( isset( $name_array[1] ) && is_numeric( $name_array[1] ) && ( $current_time - $name_array[1] ) > 60*60*24 ) {
                                        unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                    }
                                }

                                if( strpos( $file, md5( $client_id . 'wpc_avatar' ) ) === 0 ) {
                                    unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                }
                            }
                        }
                    }

                    //rename avatar from temp and save in user meta
                    $fileinfo = pathinfo( $avatars_dir . $userdata['avatar'] );

                    $avatar_file = md5( $client_id . 'wpc_avatar' ) . time() . '.' . $fileinfo['extension'];
                    rename( $avatars_dir . $userdata['avatar'] , $avatars_dir . $avatar_file );
                    update_user_meta( $client_id, 'wpc_avatar', $avatar_file );
                }
            }


            //for client registered from registration form
            if ( !empty( $userdata['to_approve'] ) ) {


                if ( '1' == $userdata['to_approve'] ) {
                    update_user_meta( $new_user, 'to_approve', '1' );
                }

                update_user_meta( $new_user, 'wpc_self_registration', 1 );

                //send email to admin
                if ( !isset( $wpc_clients_staff['new_client_admin_notify'] ) || 'yes' == $wpc_clients_staff['new_client_admin_notify'] ) {

                    //email to admins
                    $args = array(
                        'role'      => 'wpc_admin',
                        'fields'    => array( 'user_email' )
                    );
                    $admin_emails = get_users( $args );


                    $emails_array = array();
                    if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                        foreach( $admin_emails as $admin_email ) {
                            $emails_array[] = $admin_email->user_email;
                        }
                    }

                    $emails_array[] = get_option( 'admin_email' );


                    //email to managers if it can approve
                    $args = array(
                        'role'      => 'wpc_manager',
                        'fields'    => array( 'user_email', 'ID' )
                    );

                    $managers = get_users( $args );
                    if( isset( $managers ) && !empty( $managers ) ) {
                        foreach( $managers as $manager ) {
                            if( user_can( $manager->ID, 'wpc_approve_clients' ) ) {
                                $emails_array[] = $manager->user_email;
                            }
                        }
                    }

                    $emails_array = array_unique( $emails_array );

                    $args = array( 'client_id' => $new_user );
                    foreach( $emails_array as $to_email ) {
                        WPC()->mail( 'new_client_registered', $to_email, $args, 'to_approve' );
                    }

                }


                if ( '1' != $userdata['to_approve'] ) {

                    //send approval email
                    if ( isset( $wpc_clients_staff['send_approval_email'] ) && 'yes' == $wpc_clients_staff['send_approval_email'] ) {

                        $args = array( 'client_id' => $new_user );

                        //send email
                        WPC()->mail( 'account_is_approved', $userdata['user_email'], $args, 'account_is_approved' );
                    }
                }

            }


            if( isset( $userdata['send_password'] ) && $userdata['send_password'] == '1' ) {

                $args = array(
                    'client_id' => $new_user,
                    'user_password' => $userdata['user_pass'],
                    'page_id' => $link,
                    'page_title' => $userdata['business_name']
                );

                //send email
                if ( !empty( $userdata['to_approve'] ) ) {
                    WPC()->mail( 'self_client_registration', $userdata['user_email'], $args, 'new_client' );
                } else {
                    WPC()->mail( 'new_client_password', $userdata['user_email'], $args, 'new_client' );
                }
            }



        } else {
            add_filter( 'send_password_change_email', function() { return false; } );
            /*our_hook_
                hook_name: wpc_client_before_client_updated
                hook_title: Before Client Updated
                hook_description: Hook runs when Client account is updated.
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $client_id, array $userdata
                hook_since: 3.4.1
            */
            do_action( 'wpc_client_before_client_updated', $userdata['ID'], $userdata );
            wp_update_user( $userdata );
            //sending email to client for updated password information
            if ( isset( $userdata['send_password'] ) && '1' == $userdata['send_password'] ) {

                $args = array( 'client_id' => $userdata['ID'], 'user_password' => $userdata['user_pass'] );

                //send email
                WPC()->mail( 'client_updated', $userdata['user_email'], $args, 'client_updated' );
            }

            //sending email to client for updated password information
            if( isset( $userdata['contact_phone'] ) && !empty( $userdata['contact_phone'] ) ) {
                update_user_option( $userdata['ID'], 'contact_phone', $userdata['contact_phone'], false );
            }

            //set business name




            if ( isset( $userdata['business_name'] ) ) {
                update_user_meta( $userdata['ID'], 'wpc_cl_business_name', $userdata['business_name'] );
            }

            if ( isset( $userdata['admin_manager'] ) && '' != $userdata['admin_manager'] ) {

                $assign_data = array();
                if( $userdata['admin_manager'] == 'all' ) {
                    $args = array(
                        'role'      => 'wpc_manager',
                        'orderby'   => 'user_login',
                        'order'     => 'ASC',
                        'fields'    => array( 'ID' ),
                    );

                    $userdata['admin_manager'] = get_users( $args );
                    foreach( $userdata['admin_manager'] as $key=>$value) {
                        $assign_data[] = $value->ID;
                    }
                } else {
                    $assign_data = explode( ',', $userdata['admin_manager'] );
                }
                WPC()->assigns()->set_reverse_assigned_data( 'manager', $assign_data, 'client', $userdata['ID'] );
            } elseif( !( isset( $_POST['wpc_action'] ) && 'client_profile' == $_POST['wpc_action'] && current_user_can( 'wpc_modify_profile' ) ) ) {
                WPC()->assigns()->delete_assign_data_by_assign( 'manager', 'client', $userdata['ID'] );
            }


            if ( isset( $_REQUEST['wpc_circles'] ) && is_string( $_REQUEST['wpc_circles'] ) ) {
                $group_ids = ( '' != $_REQUEST['wpc_circles'] ) ? explode( ',', $_REQUEST['wpc_circles'] ) : array();

                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = '%d'", $userdata['ID'] ) );
                foreach ( $group_ids as $group_id ) {
                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $group_id,  $userdata['ID'] ) );
                }
            }


            $client_groups_id   = $wpdb->get_col( $wpdb->prepare( "SELECT group_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = %d", $userdata['ID'] ) );

            //import: add client circles
            $import_circles = array_diff( $import_circles, $client_groups_id ) ;

            foreach ( $import_circles as $circle_id ) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = %d", $circle_id, $userdata['ID'] ) );
            }

            //save custom fileds
            if ( isset( $custom_fields ) && 0 < count( $custom_fields ) ) {
                $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

                foreach( $custom_fields as $key => $value ) {
                    if ( isset( $wpc_custom_fields[$key]['type'] ) && 'file' == $wpc_custom_fields[$key]['type'] ) {
                        //for file custom field
                        if( !empty( $value['name'] ) ) {
                            $new_name = basename( rand(0000, 9999 ) . $value['name'] );
                            $filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $new_name;

                            if ( move_uploaded_file( $value['tmp_name'], $filepath ) ) {
                                $filedata = get_user_meta( $userdata['ID'], $key, true );
                                if ( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
                                    $filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $filedata['filename'];
                                    if ( file_exists( $filepath ) ) {
                                        unlink( $filepath );
                                    }
                                }

                                update_user_meta( $userdata['ID'], $key, array( 'origin_name' => $value['name'], 'filename' => $new_name ) );
                            }
                        } else {
                            $filedata = get_user_meta( $userdata['ID'], $key, true );
                            if ( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
                                $filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $filedata['filename'];
                                if( file_exists( $filepath ) ) {
                                    unlink( $filepath );
                                }

                                delete_user_meta( $userdata['ID'], $key );
                            }
                        }
                    } else {
                        update_user_meta( $userdata['ID'], $key, $value );
                    }

                    //set value to related user_meta with this custom feild
                    if ( isset( $wpc_custom_fields[$key]['relate_to'] ) && '' !=  trim( $wpc_custom_fields[$key]['relate_to'] ) ) {
                        update_user_meta( $userdata['ID'], trim( $wpc_custom_fields[$key]['relate_to'] ), $value );
                    }
                }
            }

            $client_id = $userdata['ID'];

            //for assign avatar
            if( isset( $userdata['avatar'] ) && !empty( $userdata['avatar'] ) && strpos( $userdata['avatar'], 'temp_' ) === 0 ) {
                $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );

                if( file_exists( $avatars_dir . $userdata['avatar'] ) ) {

                    //delete temp files
                    $files = scandir( $avatars_dir );
                    $current_time = time();
                    foreach( $files as $file ) {
                        if( $file != "." && $file != ".." ) {
                            if( file_exists( $avatars_dir . DIRECTORY_SEPARATOR . $file ) ) {
                                if( strpos( $file, 'temp_' ) === 0 ) {
                                    $name_array = explode( '_', $file );
                                    if( isset( $name_array[1] ) && is_numeric( $name_array[1] ) && ( $current_time - $name_array[1] ) > 60*60*24 ) {
                                        unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                    }
                                }

                                if( strpos( $file, md5( $client_id . 'wpc_avatar' ) ) === 0 ) {
                                    unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                }
                            }
                        }
                    }

                    //rename avatar from temp and save in user meta
                    $fileinfo = pathinfo( $avatars_dir . $userdata['avatar'] );

                    $avatar_file = md5( $client_id . 'wpc_avatar' ) . time() . '.' . $fileinfo['extension'];
                    rename( $avatars_dir . $userdata['avatar'] , $avatars_dir . $avatar_file );
                    update_user_meta( $client_id, 'wpc_avatar', $avatar_file );
                }
            }

            /*our_hook_
                hook_name: wpc_client_client_updated
                hook_title: Client Updated
                hook_description: Hook runs when Client account is updated.
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $client_id, array $userdata
                hook_since: 3.4.1
            */
            do_action( 'wpc_client_client_updated', $userdata['ID'], $userdata );
        }

        if( $temp_password ) {
            $this->set_temp_password( $client_id );
        }

        /*our_hook_
            hook_name: wpc_client_client_saved
            hook_title: Client Saved
            hook_description: Hook runs when Client account is registered or added by admin or updated.
            hook_type: action
            hook_in: wp-client
            hook_location class.common.php
            hook_param: int $client_id, array $userdata, array $client_groups_id
            hook_since: 3.4.1
        */
        do_action( 'wpc_client_client_saved', $client_id, $userdata, $client_groups_id );


        //for client registered from registration form
        if ( !empty( $userdata['to_approve'] ) ) {
            /*our_hook_
                hook_name: wpc_client_new_client_registered
                hook_title: New Client Registered
                hook_description: Hook runs when Client account is registered.
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $client_id, array $userdata
                hook_since: 3.4.1
            */
            do_action( 'wpc_client_new_client_registered', $new_user, $userdata );

        } else {

            /*our_hook_
                hook_name: wpc_client_new_client_added
                hook_title: New Client Added by Admin
                hook_description: Hook runs when Client account is added by Admin.
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $client_id, array $userdata
                hook_since: 3.4.1
            */
            do_action( 'wpc_client_new_client_added', $new_user, $userdata );
        }




        return $client_id;
    }



    /**
     * Getting user Avatar
     *
     * @param string $user_id Current user ID
     * @param bool|false $empty_avatar Set True if you want to show empty avatar
     * @return string
     */
    function user_avatar( $user_id = '', $empty_avatar = false ) {

        $wpc_general = WPC()->get_settings( 'general' );
        $shape = ( isset( $wpc_general['avatars_shapes'] ) && !empty( $wpc_general['avatars_shapes'] ) ) ? $wpc_general['avatars_shapes'] : 'square';

        if( $empty_avatar ) {
            wp_enqueue_style( 'wp-client-avatar-style' );

            ob_start(); ?>
            <div class="wpc_avatar_output <?php if( 'circle' == $shape ) { ?>wpc_avatar_circle<?php } ?>">
                <img class="wpc_user_avatar_image" src="<?php echo WPC()->plugin_url . "images/avatars/empty_avatar.png" ?>" />
            </div>

            <?php $avatar = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }
            return $avatar;
        }

        if( empty( $user_id ) ) $user_id = get_current_user_id();
        if( (int)$user_id <= 0 ) return '';

        $user = get_user_by( 'id', $user_id );

        //field style
        wp_enqueue_style( 'wp-client-avatar-style' );

        ob_start(); ?>
        <div class="wpc_avatar_output <?php if( 'circle' == $shape ) { ?>wpc_avatar_circle<?php } ?>">
            <?php if( !empty( $user ) ) {
                $src = ( $user_id ) ? $this->wpc_get_avatar_src( $user_id ) : WPC()->plugin_url . "images/avatars/empty_avatar.png"; ?>
                <img class="wpc_user_avatar_image" src="<?php echo $src ?>" />

                <?php //default avatar
                if( !$this->is_avatar_gravatar( $user_id ) ) { ?>
                    <div class="wpc_user_avatar_literal"><?php echo substr( $user->user_login, 0, 1 ) ?></div>
                <?php }
            } else { ?>
                <img class="wpc_user_avatar_image" src="<?php echo WPC()->plugin_url . "images/avatars/empty_avatar.png" ?>" />
            <?php } ?>
        </div>

        <?php $avatar = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $avatar;
    }


    function is_avatar_gravatar( $user_id ) {
        $current_avatar = get_user_meta( $user_id, 'wpc_avatar', true );
        $upload_dir = wp_upload_dir();

        if( !empty( $current_avatar ) && file_exists( $upload_dir['basedir'] . DIRECTORY_SEPARATOR . "wpclient" . DIRECTORY_SEPARATOR . "_avatars" . DIRECTORY_SEPARATOR . $current_avatar ) ) {
            return true;
        } else {
            $request = get_user_meta( $user_id, 'wpc_gravatar_request', true );
            if( !empty( $request ) ) {
                if( $request['date'] > time() - 60*60*12 && isset( $request['is_gravatar'] ) ) {
                    return $request['is_gravatar'];
                } else {
                    $user = get_userdata( $user_id );
                    $hash = md5( strtolower( trim( $user->get('user_email') ) ) );
                    $profile = wp_remote_post('http://www.gravatar.com/' . $hash . '.php', array(
                        'timeout' => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0'
                    ));

                    if( !( is_array( $profile ) && isset( $profile['response']['code'] ) && $profile['response']['code'] == 404 ) ) {
                        $is_gravatar = true;
                    } else {
                        $is_gravatar = false;
                    }
                    update_user_meta( $user_id, 'wpc_gravatar_request', array( 'is_gravatar' => $is_gravatar, 'date'=>time() ) );
                }
            } else {
                $user = get_userdata( $user_id );
                if( !empty( $user ) ) {
                    $hash = md5(strtolower(trim($user->get('user_email'))));
                    $profile = wp_remote_post('http://www.gravatar.com/' . $hash . '.php', array(
                        'timeout' => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0'
                    ));

                    if (!(is_array($profile) && isset($profile['response']['code']) && $profile['response']['code'] == 404)) {
                        $is_gravatar = true;
                    } else {
                        $is_gravatar = false;
                    }
                    update_user_meta( $user_id, 'wpc_gravatar_request', array( 'is_gravatar' => $is_gravatar, 'date' => time() ) );
                } else {
                    return true;
                }
            }

            return $is_gravatar;
        }
    }


    function build_avatar_field( $field_name, $field_value = false, $user_id = false ) {
        if( !( isset( $field_name ) && is_string( $field_name ) && !empty( $field_name ) ) ) {
            return '';
        }

        //field style
        wp_enqueue_style( 'wp-client-avatar-style' );

        //file uploader
        wp_enqueue_style( 'wp-client-uploadifive' );

        wp_enqueue_script( 'wp-client-uploadifive', false, array(), WPC_CLIENT_VER, true );

        wp_localize_script( 'wp-client-uploadifive', 'wpc_flash_uploader', array(
            'cancelled' => ' ' . __( "- Cancelled", WPC_CLIENT_TEXT_DOMAIN ),
            'completed' => ' ' . __( "- Completed", WPC_CLIENT_TEXT_DOMAIN ),
            'error_1'   => __( "404 Error", WPC_CLIENT_TEXT_DOMAIN ),
            'error_2'   => __( "403 Forbidden", WPC_CLIENT_TEXT_DOMAIN ),
            'error_3'   => __( "Forbidden File Type", WPC_CLIENT_TEXT_DOMAIN ),
            'error_4'   => __( "File Too Large", WPC_CLIENT_TEXT_DOMAIN ),
            'error_5'   => __( "Unknown Error", WPC_CLIENT_TEXT_DOMAIN )
        ));

        $upload_dir = wp_upload_dir();
        if( $user_id ) {
            $user = get_user_by('id', $user_id);
        } else {
            $user = false;
        }

        $wrapper_classes = '';
        $user_agent     =   $_SERVER['HTTP_USER_AGENT'];
        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        $fix_array       =   array(
            'Linux',
            'Ubuntu',
            'iPhone',
            'iPod',
            'iPad',
            'Android',
            'BlackBerry',
            'Mobile'
        );

        foreach( $os_array as $regex => $value ) {
            if( preg_match( $regex, $user_agent ) ) {
                $os_platform    =   $value;
            }
        }

        if( wp_is_mobile() || in_array( $os_platform, $fix_array ) ) {
            $wrapper_classes .= 'wpc_avatar_mobile';
        }
        //if user ID false, then build field for Add user, else for Edit user
        ob_start(); ?>

        <input type="hidden" class="hidden_value_avatar" name="<?php echo $field_name ?>" value="<?php echo ( isset( $field_value ) ) ? $field_value : ''  ?>" />
        <div id="wpc_avatar_preview_wrapper" data-user_id="<?php echo ( $user_id ) ? $user_id : '' ?>" class="<?php echo $wrapper_classes ?>">

            <?php $src = ( $user_id ) ? $this->wpc_get_avatar_src( $user_id ) : WPC()->plugin_url . "images/avatars/empty_avatar.png"; ?>

            <img class="wpc_avatar_preview" src="<?php echo $src ?>" />
            <div class="wpc_avatar_literal" <?php if( $this->is_avatar_gravatar( $user_id ) ) { ?>style="display:none;"<?php } ?>><?php echo ( isset( $user ) && is_object( $user ) ) ? substr( $user->user_login, 0, 1 ) : ''; ?></div>

            <div class="wpc_avatar_top_bubble_wrap">
                <div class="wpc_avatar_top_bubble">
                    <div class="wpc_avatar_delete <?php echo ( !$user_id || !( isset( $field_value ) && !empty( $field_value ) ) ) ? 'add' : 'edit' ?>" title="<?php _e( 'Remove Avatar', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                        &times;
                    </div>
                </div>
            </div>
            <div class="wpc_avatar_bottom_bubble_wrap">
                <div class="wpc_avatar_bottom_bubble">
                    <div class="wpc_avatar_upload">
                        <input type="file" name="Filedata" id="wpc_avatar_uploader" />
                    </div>
                </div>
            </div>
        </div>


        <script type="text/javascript">
            jQuery( document ).ready( function() {

                jQuery( '.wpc_avatar_delete').click( function() {
                    var user_id = jQuery('#wpc_avatar_preview_wrapper').data('user_id');
                    if( typeof( user_id ) !== 'undefined' && user_id != '' ) {
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                            data: 'action=wpc_avatar_remove&user_id=' + user_id,
                            dataType: "json",
                            success: function( data ) {
                                if( !data.status ) {
                                    alert( data.message );
                                } else {
                                    jQuery( '.wpc_avatar_preview' ).attr( 'src', data.current_avatar );
                                    jQuery( '.wpc_avatar_delete' ).addClass('add');
                                    jQuery( '.hidden_value_avatar' ).val('');
                                    if( !data.is_gravatar ) {
                                        jQuery('.wpc_avatar_literal').show();
                                    }
                                }
                            }
                        });
                    } else {
                        jQuery( '.wpc_avatar_preview' ).attr( 'src', '<?php echo WPC()->plugin_url ?>images/avatars/empty_avatar.png' );
                        jQuery( '.wpc_avatar_delete').addClass('add');
                        jQuery( '.hidden_value_avatar' ).val('');
                    }
                });

                jQuery('#wpc_avatar_uploader').uploadifive({
                    'formData'          : {},
                    'fileType'          : ['image/jpeg','image/png'],
                    'auto'              : true,
                    'dnd'               : true,
                    'multi'             : false,
                    'itemTemplate'      : '<div class="uploadifive-queue-item"><span class="filename"></span><span class="fileinfo"></span><div class="close"></div><div class="avatar_loading"></div></div>',
                    'buttonText'        : '<?php echo esc_js( __( 'Upload New', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                    'queueID'           : 'wpc_avatar_preview_wrapper',
                    'uploadScript'      : '<?php echo get_admin_url() ?>admin-ajax.php?action=wpc_upload_avatar',
                    'onUploadComplete'  : function( file, filename ) {
                        if( filename.indexOf( 'temp_' ) == 0 ) {
                            jQuery( '.uploadifive-queue-item.complete' ).remove();
                            jQuery( '.wpc_avatar_preview' ).attr( 'src', '<?php echo $upload_dir['baseurl'] . "/wpclient/_avatars/" ?>' + filename );
                            jQuery( '.hidden_value_avatar' ).val( filename );
                            jQuery( '.wpc_avatar_delete').removeClass('add');
                            jQuery( '.wpc_avatar_literal' ).hide();
                        } else {
                            jQuery( '.wpc_avatar_literal' ).show();
                            jQuery( '.uploadifive-queue-item.complete' ).remove();
                            alert( filename );
                        }
                        return false;
                    },
                    'onError'      : function(errorType) {
                        jQuery(this).uploadifive('clearQueue');
                        jQuery( '.uploadifive-queue-item.error' ).remove();
                        jQuery('.wpc_avatar_literal').show();
                        alert( errorType );
                    }
                });
            });
        </script>
        <?php $field = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        return $field;
    }



    public function generate_password( $password = '' ) {
        if ( has_filter( 'random_password', array( $this, 'generate_password' ) ) ) {
            remove_filter( 'random_password', array( $this, 'generate_password' ) );
        }

        $settings = WPC()->get_settings( 'password' );
        $min_length = ( isset( $settings['password_minimal_length'] ) && (int)$settings['password_minimal_length'] >= 10 ) ? (int)$settings['password_minimal_length'] : 10;

        if( isset( $settings['password_black_list'] ) && !empty( $settings['password_black_list'] ) ) {
            $black_list = explode( "\n", str_replace( array( "\n\r", "\r\n", "\r" ), "\n", $settings['password_black_list'] ) );
        } else {
            $black_list = array();
        }

        $j = 0;
        while ( $j < 1000 ) {
            $j++;

            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $chars .= '!@#$%^&*()';
            $chars .= '-_[]{}~`+=,.;:/?|';

            $password = '';
            for ( $i = 0; $i < $min_length; $i++ ) {
                $password .= substr($chars, wp_rand(0, strlen($chars) - 1), 1);
            }

            if( in_array( $password, $black_list ) ) continue;

            if( isset( $settings['password_mixed_case'] ) && $settings['password_mixed_case'] == 'yes' &&
                ( strtolower( $password ) == $password || strtoupper( $password ) == $password ) )
                continue;

            if( isset( $settings['password_numeric_digits'] ) && $settings['password_numeric_digits'] == 'yes' &&
                !preg_match( "/\d/", $password ) )
                continue;

            if( isset( $settings['password_special_chars'] ) && $settings['password_special_chars'] == 'yes' &&
                !preg_match( "/[^a-zA-Z0-9 ]/", $password ) )
                continue;

            break;
        }

        return $password;
    }


    /**
     * Get all clients for manager
     *
     * @param int $manager_id
     * @return array
     */
    function get_all_clients_manager( $manager_id = '' ) {
        if ( empty( $manager_id ) ) {
            $manager_id = get_current_user_id();
        }
        $clients_ids = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'client' );
        $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'circle' );
        foreach ( $manager_groups as $group_id ) {
            $add_client = WPC()->groups()->get_group_clients_id( $group_id );
            $clients_ids = array_merge( $clients_ids, $add_client );
        }
        $clients_ids = array_unique( $clients_ids );

        return $clients_ids;
    }


    function wpc_get_avatar_src( $user_id, $size = '128' ) {
        $avatar = get_user_meta( $user_id, 'wpc_avatar', true );

        if( !empty( $avatar ) ) {
            $upload_dir = wp_upload_dir();

            if (!file_exists($upload_dir['basedir'] . DIRECTORY_SEPARATOR . "wpclient" . DIRECTORY_SEPARATOR . "_avatars" . DIRECTORY_SEPARATOR . $avatar)) {
                $avatar = false;
            } else {
                $avatar = $upload_dir['baseurl'] . '/wpclient/_avatars/' . $avatar;
            }
        }

        if( empty( $avatar ) ) {
            if( $this->is_avatar_gravatar( $user_id ) ) {
                $default = WPC()->plugin_url . 'images/avatars/' . $user_id%10 . '.png';
                $alt = false;

                if( get_option('show_avatars') ) {
                    $str = get_avatar( $user_id, $size, $default, $alt );
                } else {
                    $str = "<img alt='{$alt}' src='{$default}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
                    $str = apply_filters( 'get_avatar', $str, $user_id, $size, $default, $alt );
                }

                if( $str != false ) {
                    preg_match('/<img[^>]*?src=[\"|\']([^\'\">]+)[\'|\"][^>]*?>/ims', $str, $regex_result);
                    if (isset($regex_result[1]) && !empty($regex_result[1])) {
                        $avatar = $regex_result[1];
                    }
                }
            } else {
                $avatar = WPC()->plugin_url . 'images/avatars/' . $user_id%10 . '.png';
            }
        }

        if( empty( $avatar ) ) {
            $avatar = WPC()->plugin_url . 'images/avatars/' . $user_id%10 . '.png';
        }

        return $avatar;
    }


    /**
     * Set temp password for user
     *
     * @global object $wpdb
     * @param int $userId
     */
    function set_temp_password( $userId ) {
        global $wpdb;

        $password = $wpdb->get_var( $wpdb->prepare( "SELECT user_pass AS pass FROM {$wpdb->users} "
            . "WHERE ID = %d", $userId ) );

        if ( isset( $password ) ) {
            update_user_meta( $userId, 'wpc_temporary_password', md5( $password ) );
        }
    }



    function _set_auth_cookie($user_id, $remember = false, $secure = '', $token = '') {
        if( !headers_sent() ) {
            wp_set_auth_cookie( $user_id, $remember, $secure, $token );
            return true;
        }

        //copy from WP-core
        if ($remember) {
            /**
             * Filter the duration of the authentication cookie expiration period.
             *
             * @since 2.8.0
             *
             * @param int  $length   Duration of the expiration period in seconds.
             * @param int  $user_id  User ID.
             * @param bool $remember Whether to remember the user login. Default false.
             */
            $expiration = time() + apply_filters('auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user_id, $remember);

            /*
             * Ensure the browser will continue to send the cookie after the expiration time is reached.
             * Needed for the login grace period in wp_validate_auth_cookie().
             */
            $expire = $expiration + ( 12 * HOUR_IN_SECONDS );
        } else {
            /** This filter is documented in wp-includes/pluggable.php */
            $expiration = time() + apply_filters('auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user_id, $remember);
            $expire = 0;
        }

        if ('' === $secure) {
            $secure = is_ssl();
        }

        // Front-end cookie is secure when the auth cookie is secure and the site's home URL is forced HTTPS.
        $secure_logged_in_cookie = $secure && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME);

        /**
         * Filter whether the connection is secure.
         *
         * @since 3.1.0
         *
         * @param bool $secure  Whether the connection is secure.
         * @param int  $user_id User ID.
         */
        $secure = apply_filters('secure_auth_cookie', $secure, $user_id);

        /**
         * Filter whether to use a secure cookie when logged-in.
         *
         * @since 3.1.0
         *
         * @param bool $secure_logged_in_cookie Whether to use a secure cookie when logged-in.
         * @param int  $user_id                 User ID.
         * @param bool $secure                  Whether the connection is secure.
         */
        $secure_logged_in_cookie = apply_filters('secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, $secure);

        if ($secure) {
            $auth_cookie_name = SECURE_AUTH_COOKIE;
            $scheme = 'secure_auth';
        } else {
            $auth_cookie_name = AUTH_COOKIE;
            $scheme = 'auth';
        }

        if ('' === $token) {
            $manager = WP_Session_Tokens::get_instance($user_id);
            $token = $manager->create($expiration);
        }

        $auth_cookie = wp_generate_auth_cookie($user_id, $expiration, $scheme, $token);
        $logged_in_cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in', $token);

        /**
         * Fires immediately before the authentication cookie is set.
         *
         * @since 2.5.0
         *
         * @param string $auth_cookie Authentication cookie.
         * @param int    $expire      Login grace period in seconds. Default 43,200 seconds, or 12 hours.
         * @param int    $expiration  Duration in seconds the authentication cookie should be valid.
         *                            Default 1,209,600 seconds, or 14 days.
         * @param int    $user_id     User ID.
         * @param string $scheme      Authentication scheme. Values include 'auth', 'secure_auth', or 'logged_in'.
         */
        do_action('set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme);

        /**
         * Fires immediately before the secure authentication cookie is set.
         *
         * @since 2.6.0
         *
         * @param string $logged_in_cookie The logged-in cookie.
         * @param int    $expire           Login grace period in seconds. Default 43,200 seconds, or 12 hours.
         * @param int    $expiration       Duration in seconds the authentication cookie should be valid.
         *                                 Default 1,209,600 seconds, or 14 days.
         * @param int    $user_id          User ID.
         * @param string $scheme           Authentication scheme. Default 'logged_in'.
         */
        do_action('set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in');

        //fix for some sites !?
        if ( 1 < ob_get_level() ) {
            while ( ob_get_level() > 1 ) {
                ob_end_clean();
            }
        } ?>

        <script src="<?php echo WPC()->plugin_url ?>js/cookies.min.js"></script>

        <script type="text/javascript">
            <?php
            $expire_js = $expiration - time();

            $secure_js = 'false';
            if ( $secure ) {
                $secure_js = 'true';
            }

            $secure_logged_in_cookie_js = 'false';
            if ( $secure_logged_in_cookie ) {
                $secure_logged_in_cookie_js = 'true';
            }

                echo "Cookies.set( '{$auth_cookie_name}', '{$auth_cookie}', {
                    expires: {$expire_js},
                    secure: {$secure_js},
                    domain: '" . COOKIE_DOMAIN . "',
                    path: '" . PLUGINS_COOKIE_PATH . "'
                }); ";

                echo "Cookies.set( '{$auth_cookie_name}', '{$auth_cookie}', {
                    expires: {$expire_js},
                    secure: {$secure_js},
                    domain: '" . COOKIE_DOMAIN . "',
                    path: '" . ADMIN_COOKIE_PATH . "'
                }); ";

                echo "Cookies.set( '{$auth_cookie_name}', '{$auth_cookie}', {
                    expires: {$expire_js},
                    secure: {$secure_logged_in_cookie_js},
                    domain: '" . COOKIE_DOMAIN . "',
                    path: '" . COOKIEPATH . "'
                }); ";

                if ( SITECOOKIEPATH != COOKIEPATH ) {
                    echo "Cookies.set( '{$auth_cookie_name}', '{$auth_cookie}', {
                        expires: {$expire_js},
                        secure: {$secure_logged_in_cookie_js},
                        domain: '" . COOKIE_DOMAIN . "',
                        path: '" . SITECOOKIEPATH . "'
                    }); ";
                }
            ?>
        </script>
        <?php
        return true;
    }


    /**
     * Filter for user meta when creates users
     *
     * @param $user_id
     */
    function insert_screen_options_meta( $user_id ) {

        if ( user_can( $user_id, 'administrator' ) || user_can( $user_id, 'wpc_manager' ) || user_can( $user_id, 'wpc_admin' ) ) {
            $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

            if ( ! empty( $wpc_custom_fields ) ) {
                $hidden_columns = array();
                foreach ( $wpc_custom_fields as $custom_field_name => $custom_field ) {
                    if ( empty( $custom_field['display_screen_options'] ) && isset( $custom_field['nature'] ) &&
                        ( 'client' == $custom_field['nature'] || 'both' == $custom_field['nature'] ) )
                        $hidden_columns[] = $custom_field_name;
                }

                $already_hidden_columns = get_user_meta( $user_id, 'managewp-client_page_wpclient_clientscolumnshidden', true );
                if ( empty( $already_hidden_columns ) )
                    $already_hidden_columns = array();

                $hidden_columns = array_merge( $already_hidden_columns, $hidden_columns );
                update_user_meta( $user_id, 'managewp-client_page_wpclient_clientscolumnshidden', $hidden_columns );
            }
        }
    }

    function validate_password_reset() {
        add_filter('random_password', array( $this, 'generate_password' ));
    }


    function replace_retrieve_password_message( $message ) {
        $wpc_common_secure = WPC()->get_settings( 'common_secure' );
        $login_url = ( !empty( $wpc_common_secure['login_url'] ) ) ? $wpc_common_secure['login_url'] : '';

        if ( !empty( $login_url ) ) {
            $message = str_replace( 'wp-login.php', $login_url, $message );
        }

        return $message;
    }


    /**
     * Save wpc role when user data is changed
     */
    function save_wpc_role( $user_id ) {
        global $wpdb;
        $user_date = get_userdata( $user_id );

        //not remove wpc role
        if ( !empty( $_POST['role'] ) && ( !isset( $_POST['wpc_not_save_role'] ) || '1' != $_POST['wpc_not_save_role'] ) ) {
            //user have several roles
            if ( is_array( $user_date->roles ) && 1 < count( $user_date->roles ) ) {
                foreach( $user_date->roles as $role ) {
                    //user have wpc role
                    if ( WPC()->admin()->is_our_role( $role ) ) {
                        //update roles for user
                        update_user_meta( $user_id, $wpdb->prefix . 'capabilities', array( $_POST['role'] => '1', $role => '1' ) );
                        unset( $_POST['role'] );

                    }
                }
            }
        }
    }


    /**
     * add field to edit user page
     */
    function add_field_to_user_edit_page( $profileuser ) {

        //user have several roles
        if ( is_array( $profileuser->roles ) && 1 < count( $profileuser->roles ) ) {
            foreach( $profileuser->roles as $role ) {
                //user have wpc role
                if ( WPC()->admin()->is_our_role( $role ) ) {
                    //add field for remove wpc role
                    echo '<tr>
                                  <td><b>' . sprintf( __( '%s Role:', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</b></td>
                                  <td>
                                    <label>
                                        <input type="checkbox" name="wpc_not_save_role" value="1" />
                                        <b>' . sprintf( __( 'Remove %s role', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</b>
                                        <br>
                                        <span class="description">Current roles: ' . implode( ' + ', $profileuser->roles ) . ' </span>
                                    </label>
                                  </td>
                                </tr>';
                }
            }
        }
    }


    /**
     * Function to delete wpc-client
     *
     * @param int $user_id id of deleting user.
     */
    function delete_client( $user_id ) {
        global $wpdb;
        $user = get_userdata( $user_id );
        if ( isset( $user ) && in_array ( 'wpc_client', $user->roles ) ) {
            $user_data  = get_userdata( $user_id );
            //delete redirect rules for client
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value=%s", $user_data->user_login ) );

            //delete client from Client Circle
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id=%d ", $user_id ) );

            //delete client from Payment History
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_payments WHERE client_id=%d ", $user_id ) );

            if( !empty( $_GET['delete_user_settings'] ) ) {
                $settings = $_GET['delete_user_settings'];
                if( isset( $settings['files'] ) && 'reassign' == $settings['files'] && !empty( $settings['files_user'] ) ) {
                    $wpdb->update( "{$wpdb->prefix}wpc_client_files",
                        array( 'user_id'   => $settings['files_user'] ),
                        array( 'user_id'   => $user_id )
                    );
                } else {
                    $client_files = $wpdb->get_col($wpdb->prepare(
                        "SELECT id
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE user_id=%d", $user_id
                    ));
                    foreach ( $client_files as $file_id ) {
                        WPC()->files()->delete_file( $file_id );
                        //WPC()->assigns()->delete_all_object_assigns( 'file', $client_file );
                    }
                    //$wpdb->delete( "{$wpdb->prefix}wpc_client_files", array( 'user_id'   => $user_id ) );
                }

                $args = array(
                    'role'          => 'wpc_client_staff',
                    'meta_key'      => 'parent_client_id',
                    'meta_value'    => $user_id,
                    'fields'        => 'ID',
                );

                $client_staff_ids = get_users( $args );
                if( isset( $settings['staff'] ) && 'remove' == $settings['staff'] ) {
                    if ( is_array( $client_staff_ids ) && 0 < count( $client_staff_ids ) )
                        foreach( $client_staff_ids as $client_staff_id ) {
                            if( is_multisite() ) {
                                wpmu_delete_user( $client_staff_id );
                            } else {
                                wp_delete_user( $client_staff_id );
                            }
                        }
                } else {
                    //unassign staff
                    if ( is_array( $client_staff_ids ) && 0 < count( $client_staff_ids ) )
                        foreach( $client_staff_ids as $client_staff_id ) {
                            update_user_meta( $client_staff_id, 'parent_client_id', '' );
                        }
                }

                if( isset( $settings['messages'] ) && 'remove' == $settings['messages'] ) {
                    //delete messages
                    $chains = $wpdb->get_col(
                        "SELECT DISTINCT chain_id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                    );

                    $message_ids = $wpdb->get_col(
                        "SELECT id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                    );

                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_messages",
                        array(
                            'author_id' => $user_id,
                        )
                    );

                    if( isset( $message_ids ) && !empty( $message_ids ) ) {
                        foreach( $message_ids as $message_id ) {
                            WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                        }
                    }


                    if( isset( $chains ) && !empty( $chains ) ) {
                        foreach( $chains as $chain_id ) {
                            $message_ids = $wpdb->get_col(
                                "SELECT id
                                    FROM {$wpdb->prefix}wpc_client_messages
                                    WHERE chain_id={$chain_id}"
                            );

                            if( empty( $message_ids ) ) {
                                $wpdb->delete(
                                    "{$wpdb->prefix}wpc_client_chains",
                                    array( 'id' => $chain_id )
                                );

                                WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id );
                                WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id );
                                WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id );
                            }
                        }
                    }
                }
            }

            //remove custom field's files if user delete
            $custom_fields = WPC()->get_settings( 'custom_fields' );
            if( isset( $custom_fields ) && !empty( $custom_fields ) ) {
                foreach( $custom_fields as $key=>$value ) {
                    if( isset( $value['type'] ) && 'file' == $value['type'] ) {
                        if ( !isset( $value['nature'] ) || 'client' == $value['nature'] || 'both' == $value['nature'] ) {
                            $filedata = get_user_meta( $user_id, $key, true );
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

            //delete options in `usermeta`
            $wpdb->delete( "{$wpdb->usermeta}", array( 'user_id' => $user_id ) );

            /*our_hook_
            hook_name: wpc_client_delete_client
            hook_title: Delete Client
            hook_description: Hook runs when Client account is deleted.
            hook_type: action
            hook_in: wp-client
            hook_location class.admin.php
            hook_param: int $client_id
            hook_since: 3.5.9
            */
            //action delete client
            do_action( 'wpc_client_delete_client', $user_id );

            //for delete assigns
            WPC()->assigns()->delete_all_assign_assigns( 'client', $user_id );

            //clear files_download_log
            $wpdb->query( $wpdb->prepare(
                "DELETE
                    FROM {$wpdb->prefix}wpc_client_files_download_log
                    WHERE client_id = %d",
                $user_id
            ) );
        } else if( isset( $user ) && in_array ( 'wpc_manager', $user->roles ) ) {
            $client_files = $wpdb->get_col($wpdb->prepare(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_files
                    WHERE user_id=%d", $user_id
            ));

            if( !empty( $_GET['delete_user_settings'] ) ) {
                $settings = $_GET['delete_user_settings'];
                if( isset( $settings['files'] ) && 'reassign' == $settings['files'] && !empty( $settings['files_user'] ) ) {
                    $wpdb->update( "{$wpdb->prefix}wpc_client_files",
                        array( 'user_id'   => $settings['files_user'] ),
                        array( 'user_id'   => $user_id )
                    );
                } else {
                    foreach ( $client_files as $client_file ) {
                        WPC()->assigns()->delete_all_object_assigns( 'file', $client_file );
                    }
                    $wpdb->delete( "{$wpdb->prefix}wpc_client_files", array( 'user_id'   => $user_id ) );
                }

                if( isset( $settings['messages'] ) && 'remove' == $settings['messages'] ) {
                    //delete messages
                    $chains = $wpdb->get_col(
                        "SELECT DISTINCT chain_id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                    );

                    $message_ids = $wpdb->get_col(
                        "SELECT id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                    );

                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_messages",
                        array(
                            'author_id' => $user_id,
                        )
                    );

                    if( isset( $message_ids ) && !empty( $message_ids ) ) {
                        foreach( $message_ids as $message_id ) {
                            WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                        }
                    }


                    if( isset( $chains ) && !empty( $chains ) ) {
                        foreach( $chains as $chain_id ) {
                            $message_ids = $wpdb->get_col(
                                "SELECT id
                                    FROM {$wpdb->prefix}wpc_client_messages
                                    WHERE chain_id={$chain_id}"
                            );

                            if( empty( $message_ids ) ) {
                                $wpdb->delete(
                                    "{$wpdb->prefix}wpc_client_chains",
                                    array( 'id' => $chain_id )
                                );

                                WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id );
                                WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id );
                                WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id );
                            }
                        }
                    }
                }

                if( isset( $settings['assign'] ) && 'reassign' == $settings['assign'] && !empty( $settings['user_assign'] ) ) {
                    $assigned_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $user_id, 'client' );
                    $assigned_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', $user_id, 'circle' );
                    WPC()->assigns()->delete_all_object_assigns( 'manager', $user_id );
                    $new_manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $settings['user_assign'], 'client' );
                    $new_manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', $settings['user_assign'], 'circle' );
                    $merged_clients = array_unique( array_merge( $assigned_clients, $new_manager_clients ) );
                    $merged_circles = array_unique( array_merge( $assigned_circles, $new_manager_circles ) );
                    WPC()->assigns()->set_assigned_data( 'manager', $settings['user_assign'], 'client', $merged_clients );
                    WPC()->assigns()->set_assigned_data( 'manager', $settings['user_assign'], 'circle', $merged_circles );
                } else {
                    WPC()->assigns()->delete_all_object_assigns( 'manager', $user_id );
                }
            } else {
                foreach ( $client_files as $client_file ) {
                    WPC()->assigns()->delete_all_object_assigns( 'file', $client_file );
                }
                $wpdb->delete( "{$wpdb->prefix}wpc_client_files", array( 'user_id'   => $user_id ) );

                //delete messages
                $chains = $wpdb->get_col(
                    "SELECT DISTINCT chain_id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                );

                $message_ids = $wpdb->get_col(
                    "SELECT id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                );

                $wpdb->delete(
                    "{$wpdb->prefix}wpc_client_messages",
                    array(
                        'author_id' => $user_id,
                    )
                );

                if( isset( $message_ids ) && !empty( $message_ids ) ) {
                    foreach( $message_ids as $message_id ) {
                        WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                    }
                }


                if( isset( $chains ) && !empty( $chains ) ) {
                    foreach( $chains as $chain_id ) {
                        $message_ids = $wpdb->get_col(
                            "SELECT id
                                    FROM {$wpdb->prefix}wpc_client_messages
                                    WHERE chain_id={$chain_id}"
                        );

                        if( empty( $message_ids ) ) {
                            $wpdb->delete(
                                "{$wpdb->prefix}wpc_client_chains",
                                array( 'id' => $chain_id )
                            );

                            WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id );
                            WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id );
                            WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id );
                        }
                    }
                }

                WPC()->assigns()->delete_all_object_assigns( 'manager', $user_id );
            }

            /*our_hook_
            hook_name: wpc_client_delete_manager
            hook_title: Delete Manager
            hook_description: Hook runs when WPC Manager account is deleted.
            hook_type: action
            hook_in: wp-client
            hook_location class.admin.php
            hook_param: int $client_id
            hook_since: 3.5.9
            */
            //action delete client
            do_action( 'wpc_client_delete_manager', $user_id );
        } else if( isset( $user ) && in_array ( 'wpc_admin', $user->roles ) ) {
            $client_files = $wpdb->get_col($wpdb->prepare(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_files
                    WHERE user_id=%d", $user_id
            ));

            if( !empty( $_GET['delete_user_settings'] ) ) {
                $settings = $_GET['delete_user_settings'];
                if( isset( $settings['files'] ) && 'reassign' == $settings['files'] && !empty( $settings['files_user'] ) ) {
                    $wpdb->update( "{$wpdb->prefix}wpc_client_files",
                        array( 'user_id'   => $settings['files_user'] ),
                        array( 'user_id'   => $user_id )
                    );
                } else {
                    foreach ( $client_files as $client_file ) {
                        WPC()->assigns()->delete_all_object_assigns( 'file', $client_file );
                    }
                    $wpdb->delete( "{$wpdb->prefix}wpc_client_files", array( 'user_id'   => $user_id ) );
                }

                if( isset( $settings['messages'] ) && 'remove' == $settings['messages'] ) {
                    //delete messages
                    $chains = $wpdb->get_col(
                        "SELECT DISTINCT chain_id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                    );

                    $message_ids = $wpdb->get_col(
                        "SELECT id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                    );

                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_messages",
                        array(
                            'author_id' => $user_id,
                        )
                    );

                    if( isset( $message_ids ) && !empty( $message_ids ) ) {
                        foreach( $message_ids as $message_id ) {
                            WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                        }
                    }


                    if( isset( $chains ) && !empty( $chains ) ) {
                        foreach( $chains as $chain_id ) {
                            $message_ids = $wpdb->get_col(
                                "SELECT id
                                    FROM {$wpdb->prefix}wpc_client_messages
                                    WHERE chain_id={$chain_id}"
                            );

                            if( empty( $message_ids ) ) {
                                $wpdb->delete(
                                    "{$wpdb->prefix}wpc_client_chains",
                                    array( 'id' => $chain_id )
                                );

                                WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id );
                                WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id );
                                WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id );
                            }
                        }
                    }
                }
            } else {
                foreach ( $client_files as $client_file ) {
                    WPC()->assigns()->delete_all_object_assigns( 'file', $client_file );
                }
                $wpdb->delete( "{$wpdb->prefix}wpc_client_files", array( 'user_id'   => $user_id ) );

                //delete messages
                $chains = $wpdb->get_col(
                    "SELECT DISTINCT chain_id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                );

                $message_ids = $wpdb->get_col(
                    "SELECT id
                            FROM {$wpdb->prefix}wpc_client_messages
                            WHERE author_id={$user_id}"
                );

                $wpdb->delete(
                    "{$wpdb->prefix}wpc_client_messages",
                    array(
                        'author_id' => $user_id,
                    )
                );

                if( isset( $message_ids ) && !empty( $message_ids ) ) {
                    foreach( $message_ids as $message_id ) {
                        WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                    }
                }


                if( isset( $chains ) && !empty( $chains ) ) {
                    foreach( $chains as $chain_id ) {
                        $message_ids = $wpdb->get_col(
                            "SELECT id
                                    FROM {$wpdb->prefix}wpc_client_messages
                                    WHERE chain_id={$chain_id}"
                        );

                        if( empty( $message_ids ) ) {
                            $wpdb->delete(
                                "{$wpdb->prefix}wpc_client_chains",
                                array( 'id' => $chain_id )
                            );

                            WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id );
                            WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id );
                            WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id );
                        }
                    }
                }
            }

            /*our_hook_
            hook_name: wpc_client_delete_wpc_admin
            hook_title: Delete WPC Admin
            hook_description: Hook runs when WPC Admin account is deleted.
            hook_type: action
            hook_in: wp-client
            hook_location class.admin.php
            hook_param: int $client_id
            hook_since: 3.7.8.1
            */
            //action delete client
            do_action( 'wpc_client_delete_wpc_admin', $user_id );
        }
    }


    function check_manager_access() {
        if( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
            $post_id = $_GET['post'];
            $post = get_post( $post_id );

            if ( current_user_can( 'wpc_manager' ) && ! current_user_can( 'administrator' ) ) {
                $client_ids = $this->get_all_clients_manager();

                if ( 'clientspage' == $post->post_type ) {
                    //Portal Pages in Portal Pages Categories with Clients access
                    $category_id = get_post_meta( $post->ID, '_wpc_category_id', true );

                    $users_category = ( isset( $category_id ) ) ? WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $category_id, 'client' ) : array();

                    //Portal Pages with Clients access
                    $user_ids = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'client' );
                    $user_ids = array_merge( $users_category, $user_ids );

                    //Portal Pages in Portal Pages Categories with Client Circles access
                    $groups_category = ( isset( $category_id ) ) ? WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $category_id, 'circle' ) : array();

                    //Portal Pages with Client Circles access
                    $groups_id = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'circle' );
                    $groups_id = array_merge( $groups_category, $groups_id );

                    //get clients from Client Circles
                    if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                        foreach( $groups_id as $group_id ) {
                            $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }

                    if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                        $user_ids = array_unique( $user_ids );


                    if ( !( is_array( $client_ids ) && count( $client_ids ) && count( array_intersect( $client_ids, $user_ids ) ) ) &&
                        $post->post_author != get_current_user_id() ) {
                        WPC()->redirect( admin_url( 'edit.php?post_type=' . $post->post_type ) );
                    }
                } else if( 'portalhub' == $post->post_type ) {

                    if ( ! current_user_can( 'wpc_edit_portalhubs' ) ) {
                        WPC()->redirect( admin_url( 'admin.php?page=wpclients_content&tab=portalhubs' ) );
                    }
                }
            }
        }
    }


    function add_avatar_to_profile( $user ) {
        if( user_can( $user, 'wpc_client' ) || user_can( $user, 'wpc_client_staff' ) ||
            user_can( $user, 'wpc_manager' ) || user_can( $user, 'wpc_admin' ) || user_can( $user, 'administrator' ) ) {

            $avatar         = get_user_meta( $user->ID, 'wpc_avatar', true ); ?>

            <table class="form-table wpc_avatar_on_profile">
                <tr>
                    <th><label for="wpc_avatar"><?php printf( __( '%s Avatar', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ); ?></label></th>
                    <td>
                        <?php echo $this->build_avatar_field( 'wpc_avatar', $avatar, $user->ID ); ?>
                    </td>
                </tr>
            </table>
        <?php }
    }


    function update_avatar_in_profile() {
        if( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
            $user_id = get_current_user_id();

            if( isset( $_POST['wpc_avatar'] ) && !empty( $_POST['wpc_avatar'] ) && strpos( $_POST['wpc_avatar'], 'temp_' ) === 0 ) {
                $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );

                if( file_exists( $avatars_dir . $_POST['wpc_avatar'] ) ) {

                    //delete temp files
                    $files = scandir($avatars_dir);
                    $current_time = time();
                    foreach ($files as $file) {
                        if ($file != "." && $file != "..") {
                            if (file_exists($avatars_dir . DIRECTORY_SEPARATOR . $file)) {
                                if (strpos($file, 'temp_') === 0) {
                                    $name_array = explode('_', $file);
                                    if (isset($name_array[1]) && is_numeric($name_array[1]) && ($current_time - $name_array[1]) > 60 * 60 * 24) {
                                        unlink($avatars_dir . DIRECTORY_SEPARATOR . $file);
                                    }
                                }

                                if (strpos($file, md5($user_id . 'wpc_avatar')) === 0) {
                                    unlink($avatars_dir . DIRECTORY_SEPARATOR . $file);
                                }
                            }
                        }
                    }

                    //rename avatar from temp and save in user meta
                    $fileinfo = pathinfo($avatars_dir . $_POST['wpc_avatar']);

                    $avatar_file = md5($user_id . 'wpc_avatar') . time() . '.' . $fileinfo['extension'];
                    rename($avatars_dir . $_POST['wpc_avatar'], $avatars_dir . $avatar_file);
                    update_user_meta($user_id, 'wpc_avatar', $avatar_file);
                }

            }
        }
    }

    function add_columns_for_screen_options_for_client( $column_headers ) {
        if ( isset( $_GET['tab'] ) && $_GET['tab'] != 'approve' ) {
            return false;
        }
        $fields = array(
            "contact_name"  => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
            "business_name" => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
            "circles"       => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
            "email"         => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
            "creation_date" => __( 'Creation Date', WPC_CLIENT_TEXT_DOMAIN ),
        ) ;
        if( !WPC()->flags['easy_mode'] && ( !current_user_can( 'wpc_manager' ) || current_user_can('administrator') ) ) {
            $fields['managers'] = WPC()->custom_titles['manager']['p'];
        }

        if ( ( isset( $_GET['tab'] ) ) && $_GET['tab'] == 'approve' ) {
            unset( $fields['creation_date'] );
            unset( $fields['circles'] );
            unset( $fields['managers'] );
        }

        if( !WPC()->flags['easy_mode'] ) {
            $add_cf = array();
            $custom_fields = WPC()->custom_fields()->get_custom_fields_for_users();
            if ( 0 < count( $custom_fields ) ) {
                foreach ( $custom_fields as $key => $val ) {
                    $add_cf[ $key ] = ( isset( $val['title'] ) && '' != $val['title'] ) ? $val['title'] : __( 'Not Title', WPC_CLIENT_TEXT_DOMAIN ) ;
                }
            }

            $fields = array_merge( $fields, $add_cf );
        }

        return $fields;
    }

    function add_columns_for_screen_options_for_staff( $column_headers ) {
        if ( ( isset( $_GET['tab'] ) && ( 'staff' != $_GET['tab'] ) ) && ( isset( $_GET['tab'] ) && 'staff_approve' != $_GET['tab'] ) ) {
            return false;
        }
        $fields = array(
            'email'  => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
            'client' => sprintf( __( 'Assigned to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
        );
        if( ! WPC()->flags['easy_mode'] ) {
            $add_cf = array();
            $custom_fields = WPC()->custom_fields()->get_custom_fields_for_users('admin_screen','staff');
            if ( 0 < count( $custom_fields ) ) {
                foreach ( $custom_fields as $key => $val ) {
                    $add_cf[ $key ] = ( isset( $val['title'] ) && '' != $val['title'] ) ? $val['title'] : __( 'Not Title', WPC_CLIENT_TEXT_DOMAIN ) ;
                }
            }

            $fields = array_merge( $fields, $add_cf );
        }
        return $fields;
    }


    /**
     * Function to restore wpc-client
     *
     * @param int $user_id id of restoring user.
     */
    function restore_client( $user_id ) {
        $user = get_userdata( $user_id );
        if ( isset( $user ) && in_array ( 'wpc_client', $user->roles ) ) {
            delete_user_meta( $user_id, 'archive' );
        }
    }


    function added_role( $role, $wpc_capabilities ) {
        if( is_string( $role ) && '' != $role && isset( $wpc_capabilities[ $role ] ) ) {
            global $wp_roles;

            $caps = WPC()->get_capabilities_maps();
            if( isset( $caps[ $role ]['permanent'] ) && is_array( $caps[ $role ]['permanent'] ) ) {
                $caps = array_merge( $caps[ $role ]['permanent'], $wpc_capabilities[$role] );
            } else {
                $caps = $wpc_capabilities[ $role ];
            }

            $role_name = isset( $wp_roles->roles[ $role ]['name'] ) ? $wp_roles->roles[ $role ]['name'] : '';
            //remore role for update capabilities
            $wp_roles->remove_role( $role );
            //add role
            $wp_roles->add_role( $role, $role_name, $caps );
        }
    }


    /**
     * Approve Client
     **/
    function client_approve( $client_id, $groups_id ) {
        global $wpdb;

        //Add clients to the Client Circles
        if ( is_array( $groups_id ) && 0 < count( $groups_id ) ) {
            foreach( $groups_id as $group_id ) {
                $wpdb->query( $wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $group_id,  $client_id
                ) );
            }
        }

        if( isset( $_REQUEST['wpc_managers'] ) && '' != $_REQUEST['wpc_managers'] ) {
            if( 'all' == $_REQUEST['wpc_managers'] ) {
                $args = array(
                    'role'      => 'wpc_manager',
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                    'fields'    => array( 'ID' ),
                );

                $managers = get_users( $args );

                $manager_ids = array();
                foreach( $managers as $manager ) {
                    $manager_ids[] = $manager->ID;
                }

            } else {
                $manager_ids = explode( ',', $_REQUEST['wpc_managers'] );
            }
            WPC()->assigns()->set_reverse_assigned_data( 'manager', $manager_ids, 'client', $client_id );
        }


        //create HUB and portal
        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

        $create_portal = false;
        if ( !isset( $wpc_clients_staff['create_portal_page'] ) || 'yes' == $wpc_clients_staff['create_portal_page'] ) {
            $create_portal = true;
        }

        $business_name = get_user_meta( $client_id, 'wpc_cl_business_name', true );

        $args = array(
            'client_id' => $client_id,
            'business_name' => $business_name,
        );
        WPC()->pages()->create_hub_page( $args, $create_portal );

        delete_user_meta( $client_id, 'to_approve' );


        /*our_hook_
            hook_name: wpc_client_account_is_approved
            hook_title: Member's account is approved
            hook_description: Hook runs after member's account is approved.
            hook_type: action
            hook_in: wp-client
            hook_location wpc-members core
            hook_param: int $client_id
            hook_since: 4.5.1
        */
        do_action( 'wpc_client_account_is_approved', $client_id );

        //send approval email
        if ( isset( $wpc_clients_staff['send_approval_email'] ) && 'yes' == $wpc_clients_staff['send_approval_email'] ) {
            $userdata = get_userdata( $client_id );

            $args = array( 'client_id' => $client_id );

            //send email
            WPC()->mail( 'account_is_approved', $userdata->get( 'user_email' ), $args, 'account_is_approved' );
        }
    }


    function resend_welcome_email( $user_id ) {
        $userdata = get_userdata( $user_id );

        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

        $new_password = __( 'Your current password', WPC_CLIENT_TEXT_DOMAIN );
        if ( !isset( $wpc_clients_staff['reset_password'] ) || 'yes' == $wpc_clients_staff['reset_password'] ) {
            $new_password = WPC()->members()->generate_password();
            $update_data = array( 'ID' => $user_id, 'user_pass' => $new_password );

            add_filter( 'send_password_change_email', function() { return false; } );

            wp_update_user( $update_data );
        }


        $args = array(
            'client_id' => $user_id,
            'user_password' => $new_password
        );

        update_user_meta( $user_id, 'wpc_send_welcome_email', time() );
        //send email
        if( user_can( $user_id, 'wpc_client' ) ) {
            $self_registration = get_user_meta( $user_id, 'wpc_self_registration', true );
            if ( !empty( $self_registration ) ) {
                WPC()->mail( 'self_client_registration', $userdata->user_email, $args, 'new_client' );
            } else {
                WPC()->mail( 'new_client_password', $userdata->user_email, $args, 'new_client' );
            }
        } else if( user_can( $user_id, 'wpc_manager' ) ) {
            WPC()->mail( 'manager_created', $userdata->user_email, $args, 'manager_created' );
        } else if( user_can( $user_id, 'wpc_admin' ) ) {
            WPC()->mail( 'admin_created', $userdata->user_email, $args, 'admin_created' );
        }
    }


    /*
    * Check whether the user is not archive
    */
    function verification_archive_user( $user, $login, $password ) {
        if( ! ( $user instanceof WP_User ) ) return $user;
        if ( '' == $login && ''  == $password ) {
            return $user;
        } else {
            if ( is_email( $login ) ) {
                $client = get_user_by('email', $login);
            } else {
                $client = get_user_by('login', $login);
            }

            if ( isset( $client->ID ) && get_user_meta( $client->ID, 'archive' ) ) {
                return null;
            } else return $user;
        }
    }


    /*
    * Function to save user data after client edit profile
    */
    function save_password_after_edit() {
        if ( isset( $_POST['wpc_action'] ) && in_array( $_POST['wpc_action'], array( 'client_profile', 'staff_profile' ) ) && current_user_can( 'wpc_modify_profile' ) ) {
            if ( isset( $_POST['edit_nonce_field'] ) && wp_verify_nonce( $_POST['edit_nonce_field'], 'verify_edit_user' ) ) {
                $error = 0;

                if ( current_user_can( 'wpc_client_staff' ) ) {

                    $custom_fields = ( isset( $_REQUEST['custom_fields'] ) && !empty( $_REQUEST['custom_fields'] ) ) ? $_REQUEST['custom_fields'] : array();

                    if( isset( $_FILES['custom_fields'] ) ) {
                        $files_custom_fields = array();
                        foreach( $_FILES['custom_fields'] as $key1 => $value1)
                            foreach( $value1 as $key2 => $value2 )
                                $files_custom_fields[$key2][$key1] = $value2;

                        $custom_fields = array_merge( $custom_fields, $files_custom_fields );
                    }

                    //empty email
                    if ( !isset( $_REQUEST['user_data']['email'] ) )
                        $error++;

                    $user_email = apply_filters( 'pre_user_email', isset( $_REQUEST['user_data']['email'] ) ? $_REQUEST['user_data']['email'] : '' );
                    // email already exists
                    if( email_exists( $user_email ) ) {
                        $current_client = get_user_by( 'email', $user_email );
                        $current_client_id = ( isset( $current_client ) && !empty( $current_client ) ) ? $current_client->ID : 0;

                        if( get_current_user_id() != $current_client_id ) {
                            // email already exist
                            $error++;
                        }
                    }

                    if ( current_user_can( 'wpc_reset_password' ) && !empty( $_REQUEST['contact_password'] ) ) {
                        if ( empty( $_REQUEST['contact_password2'] ) || $_REQUEST['contact_password'] != $_REQUEST['contact_password2'] ) {
                            $error++;
                        }
                    }

                    $user_id = get_current_user_id() ;

                    $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_profile_staff', $user_id );

                    if ( isset( $custom_fields ) && count( $custom_fields ) > count( $all_custom_fields ) ) {
                        error_log( 'Wrong count of custom fields' );
                        exit;
                    }

                    if ( isset( $custom_fields ) && is_array( $custom_fields ) && is_array( $all_custom_fields ) ) {

                        foreach( $custom_fields as $key=>$value ) {
                            if ( !array_key_exists( $key, $all_custom_fields ) ) {
                                error_log( 'Wrong custom field keys' );
                                exit;
                            }
                        }

                        foreach( $all_custom_fields as $all_key=>$all_value ) {
                            if ( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $custom_fields ) ) {
                                $custom_fields[$all_key] = '';
                            }

                            foreach( $custom_fields as $key=>$value ) {
                                if( $key == $all_key && ( isset( $all_value['required'] ) && '1' == $all_value['required'] ) && '' == $value ) {
                                    $error++;
                                }
                            }
                        }
                    }

                    if ( !$error ) {

                        $userdata = array(
                            'ID'                => get_current_user_id(),
                            'user_email'        => $_REQUEST['user_data']['email'],
                            'first_name'        => esc_attr( $_REQUEST['user_data']['first_name'] ),
                            'last_name'         => esc_attr( $_REQUEST['user_data']['last_name'] ),
                        );

                        if ( current_user_can( 'wpc_reset_password' ) && !empty( $_REQUEST['contact_password2'] ) ) {
                            $userdata['user_pass'] = WPC()->prepare_password( $_REQUEST['contact_password2'] );
                        }

                        //save custom fileds
                        if ( isset( $custom_fields ) && 0 < count( $custom_fields ) ) {
                            $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

                            foreach( $custom_fields as $key => $value ) {
                                if ( isset( $wpc_custom_fields[$key]['type'] ) && 'file' == $wpc_custom_fields[$key]['type'] ) {
                                    //for file custom field
                                    if( !empty( $value['name'] ) ) {
                                        $new_name = basename(rand(0000, 9999) . $value['name']);
                                        $filepath = WPC()->get_upload_dir('wpclient/_custom_field_files/' . $key . '/') . $new_name;

                                        if ( move_uploaded_file( $value['tmp_name'], $filepath ) ) {
                                            $filedata = get_user_meta( $user_id, $key, true );
                                            if ( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
                                                $filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $filedata['filename'];
                                                if( file_exists( $filepath ) ) {
                                                    unlink( $filepath );
                                                }
                                            }
                                            update_user_meta($user_id, $key, array( 'origin_name' => $value['name'], 'filename' => $new_name ) );
                                        }
                                    } else {
                                        $filedata = get_user_meta( $user_id, $key, true );
                                        if ( !empty( $filedata ) && isset( $filedata['filename'] ) ) {
                                            $filepath = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $key . '/' ) . $filedata['filename'];
                                            if( file_exists( $filepath ) ) {
                                                unlink( $filepath );
                                            }

                                            delete_user_meta( $user_id, $key );
                                        }
                                    }
                                } else {
                                    update_user_meta( $user_id, $key, $value );
                                }
                                //set value to related user_meta with this custom feild
                                if ( isset( $wpc_custom_fields[$key]['relate_to'] ) && '' !=  trim( $wpc_custom_fields[$key]['relate_to'] ) ) {
                                    update_user_meta( $user_id, trim( $wpc_custom_fields[$key]['relate_to'] ), $value );
                                }
                            }
                        }

                        wp_update_user( $userdata );

                        if( isset( $_REQUEST['avatar'] ) && !empty( $_REQUEST['avatar'] ) && strpos( $_REQUEST['avatar'], 'temp_' ) === 0 ) {
                            $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );

                            if( file_exists( $avatars_dir . $_REQUEST['avatar'] ) ) {

                                //delete temp files
                                $files = scandir( $avatars_dir );
                                $current_time = time();
                                foreach( $files as $file ) {
                                    if( $file != "." && $file != ".." ) {
                                        if( file_exists( $avatars_dir . DIRECTORY_SEPARATOR . $file ) ) {
                                            if( strpos( $file, 'temp_' ) === 0 ) {
                                                $name_array = explode( '_', $file );
                                                if( isset( $name_array[1] ) && is_numeric( $name_array[1] ) && ( $current_time - $name_array[1] ) > 60*60*24 ) {
                                                    unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                                }
                                            }

                                            if( strpos( $file, md5( $user_id . 'wpc_avatar' ) ) === 0 ) {
                                                unlink( $avatars_dir . DIRECTORY_SEPARATOR . $file );
                                            }
                                        }
                                    }
                                }

                                //rename avatar from temp and save in user meta
                                $fileinfo = pathinfo( $avatars_dir . $_REQUEST['avatar'] );

                                $avatar_file = md5( $user_id . 'wpc_avatar' ) . time() . '.' . $fileinfo['extension'];
                                rename( $avatars_dir . $_REQUEST['avatar'] , $avatars_dir . $avatar_file );
                                update_user_meta( $user_id, 'wpc_avatar', $avatar_file );
                            }
                        }
                    }

                } else {

                    //fix for IDE
	                /**
	                 * @var $ID
                     * @var $avatar
	                 */
                    extract($_REQUEST);

                    if ( isset( $wpc_submit_profile ) ) {
                        $ID = get_current_user_id();

                        // validate at php side
                        if ( isset( $contact_name ) && empty( $contact_name ) ) // empty username
                            $error++;

                        if ( isset( $contact_email ) && empty( $contact_email ) ) // empty email
                            $error++;

                        $contact_email = apply_filters( 'pre_user_email', isset( $contact_email ) ? $contact_email : '' );
                        if ( email_exists( $contact_email ) ) {
                            if ( $ID != get_user_by( 'email', $contact_email )->ID ) {
                                // email already exist
                                $error++;
                            }
                        }

                        if ( current_user_can( 'wpc_reset_password' ) && !empty( $contact_password ) ) {
                            if ( empty( $contact_password2 ) || $contact_password != $contact_password2 ) {
                                $error++;
                            }
                        }

                        $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_edit_client', $ID );

                        if( isset( $_FILES['custom_fields'] ) ) {
                            if ( !isset( $custom_fields ) ) {
                                $custom_fields = array();
                            }

                            $files_custom_fields = array();
                            foreach( $_FILES['custom_fields'] as $key1 => $value1)
                                foreach( $value1 as $key2 => $value2 )
                                    $files_custom_fields[$key2][$key1] = $value2;

                            $custom_fields = array_merge( $custom_fields, $files_custom_fields );
                        }

                        if ( isset( $custom_fields ) && count( $custom_fields ) > count( $all_custom_fields ) ) {
                            exit;
                        }

                        if ( isset( $custom_fields ) && is_array( $custom_fields ) && is_array( $all_custom_fields ) ) {

                            foreach( $custom_fields as $key=>$value ) {
                                if ( !array_key_exists( $key, $all_custom_fields ) ) {
                                    exit;
                                }
                            }

                            foreach( $all_custom_fields as $all_key=>$all_value ) {
                                if ( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $custom_fields ) ) {
                                    $custom_fields[$all_key] = '';
                                }

                                foreach( $custom_fields as $key=>$value ) {
                                    if( $key == $all_key && ( isset( $all_value['required'] ) && '1' == $all_value['required'] ) && '' == $value ) {
                                        $error++;
                                    }
                                }
                            }
                        }

                        if ( 0 == $error ) {

                            if( !isset( $contact_name ) && !isset( $contact_email ) && !isset( $contact_phone ) ) {
                                $userdata = array(
                                    'ID'                => esc_attr( $ID ),
                                    'user_login'        => esc_attr( get_userdata( $ID )->get( 'user_login' ) ),
                                    'send_password'     => ( isset( $send_password ) && '1' == $send_password ) ? '1' : '0',
                                    'avatar'            => esc_attr( $avatar ),
                                );
                            } else {
                                $userdata = array(
                                    'ID'                => esc_attr( $ID ),
                                    'user_login'        => esc_attr( get_userdata( $ID )->get( 'user_login' ) ),
                                    'display_name'      => esc_attr( trim( $contact_name ) ),
                                    'user_email'        => $contact_email,
                                    'contact_phone'     => esc_attr( $contact_phone ),
                                    'send_password'     => ( isset( $send_password ) && '1' == $send_password ) ? '1' : '0',
                                    'avatar'            => esc_attr( $avatar ),
                                );
                            }

                            //set custom fields
                            if ( isset( $custom_fields ) )
                                $userdata['custom_fields'] = $custom_fields;

                            if ( current_user_can( 'wpc_reset_password' ) && !empty( $contact_password2 ) ) {
                                $userdata['user_pass'] = WPC()->prepare_password( $contact_password2 );
                            }

                            $this->client_update_func( $userdata );
                            //email to admins
                            $args = array(
                                'role'      => 'wpc_admin'
                            );
                            $admin_emails = get_users( $args );
                            $emails_array = array();
                            if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                                foreach( $admin_emails as $admin_email ) {
                                    $emails_array[ $admin_email->ID ] = array(
                                        'email' => $admin_email->user_email,
                                        'contact_name' => $admin_email->display_name
                                    );
                                }
                            }

                            $emails_array['0'] = array(
                                'email' => get_option( 'admin_email' ),
                                'contact_name' => ''
                            );

                            $manager_ids = $this->get_client_managers( get_current_user_id() );

                            if( is_array( $manager_ids ) && count( $manager_ids ) ) {
                                foreach( $manager_ids as $manager_id ) {
                                    if ( 0 < $manager_id ) {
                                        $manager = get_userdata( $manager_id );
                                        if ( $manager ) {
                                            $emails_array[ $manager_id ] = array(
                                                'email' => $manager->get( 'user_email' ),
                                                'contact_name' => $manager->get('display_name')
                                            );
                                        }

                                    }
                                }
                            }

                            foreach( $emails_array as $to_user ) {
                                $args = array(
                                    'client_id' => get_current_user_id(),
                                    'contact_name' => $to_user['contact_name']
                                );
                                WPC()->mail( 'profile_updated', $to_user['email'], $args, 'profile_updated' );
                            }
                        }
                    }
                }
            }
        }
    }


    function password_protect_css_js( $on_current_page = false ) {
        global $wp_scripts;
        if( ( isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) && ( in_array( 'user-profile', $wp_scripts->queue ) || in_array( 'wpc_login_page', $wp_scripts->queue ) ) ) ||
            ( isset( $_GET['page'] ) && 'wpclient_clients' == $_GET['page'] ) ||
            $on_current_page
        ) {
            wp_enqueue_script( 'wp-client-password-protect' );
            wp_localize_script( 'wp-client-password-protect', 'wpc_text_var', array( 'pwsL10n' => array(
                'empty' => __( "Strength Indicator", WPC_CLIENT_TEXT_DOMAIN ),
                'short' => __("Too Short", WPC_CLIENT_TEXT_DOMAIN ),
                'weak' => __("Very Weak", WPC_CLIENT_TEXT_DOMAIN ),
                'bad' => __("Weak", WPC_CLIENT_TEXT_DOMAIN ),
                'good' => __("Medium", WPC_CLIENT_TEXT_DOMAIN ),
                'strong' => __("Strong", WPC_CLIENT_TEXT_DOMAIN ),
                'mismatch' => __("Password Mismatch", WPC_CLIENT_TEXT_DOMAIN ),
                'mixed_case' => __("Needs Mixed Case", WPC_CLIENT_TEXT_DOMAIN ),
                'numbers' => __("Needs Numbers", WPC_CLIENT_TEXT_DOMAIN ),
                'special_chars' => __("Needs Special Chars", WPC_CLIENT_TEXT_DOMAIN ),
                'blacklist' => __("Password in Blacklist", WPC_CLIENT_TEXT_DOMAIN )
            )));

            $settings = WPC()->get_settings( 'password' );

            if( isset( $settings['password_black_list'] ) && !empty( $settings['password_black_list'] ) ) {
                $black_list = explode( "\n", str_replace( array( "\n\r", "\r\n", "\r" ), "\n", $settings['password_black_list'] ) );
            } else {
                $black_list = array();
            }

            $min_length = ( isset( $settings['password_minimal_length'] ) && is_numeric( $settings['password_minimal_length'] ) && $settings['password_minimal_length'] > 0 ) ? $settings['password_minimal_length'] : 1;
            $hint_message = __( 'Hint - The password', WPC_CLIENT_TEXT_DOMAIN ) . ':<br />';
            if( $min_length > 1 ) {
                $hint_message .= '<span class="wpc_requirement_min_length">- ' . sprintf( __( 'Should be at least %d characters long.', WPC_CLIENT_TEXT_DOMAIN ), $min_length ) . '</span><br />';
            }

            $strength = ( isset( $settings['password_strength'] ) && is_numeric( $settings['password_strength'] ) ) ? $settings['password_strength'] : 5;
            switch( $strength ) {
                case '2':
                    $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), __('Weak', WPC_CLIENT_TEXT_DOMAIN ) ) . '</span><br />';
                    break;
                case '3':
                    $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), _x('Medium', 'password strength', WPC_CLIENT_TEXT_DOMAIN) ) . '</span><br />';
                    break;
                case '4':
                    $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), __('Strong', WPC_CLIENT_TEXT_DOMAIN ) ) . '</span><br />';
                    break;
                default:
                    $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), __('Very weak', WPC_CLIENT_TEXT_DOMAIN ) ) . '</span><br />';
                    break;
            }

            $mixed_case = !empty( $settings['password_mixed_case'] ) ? $settings['password_mixed_case'] : 'no';
            if( 'yes' == $mixed_case ) {
                $hint_message .= '<span class="wpc_requirement_mixed_case">- ' . __( 'Should contain upper and lower case letters.', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />';
            }

            $numeric_digits = !empty( $settings['password_numeric_digits'] ) ? $settings['password_numeric_digits'] : 'no';
            if( 'yes' == $numeric_digits ) {
                $hint_message .= '<span class="wpc_requirement_numeric_digits">- ' . __( 'Should contain numbers.', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />';
            }

            $special_chars = !empty( $settings['password_special_chars'] ) ? $settings['password_special_chars'] : 'no';
            if( 'yes' == $special_chars ) {
                $hint_message .= '<span class="wpc_requirement_special_chars">- ' . __( 'Should contain special characters like ! " ? $ % ^ & ).', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />';
            }

            wp_localize_script( 'wp-client-password-protect', 'wpc_password_protect', array(
                'blackList' => $black_list,
                'min_length' => $min_length,
                'strength' => $strength,
                'mixed_case' => $mixed_case,
                'numeric_digits' => $numeric_digits,
                'special_chars' => $special_chars,
                'hint_message' => $hint_message,
                'ajax_url' => get_admin_url() . 'admin-ajax.php'
            ));
        }
    }


    /**
     * Client Login from widget
     */
    function client_login_from_() {
        global $wpdb;
        if ( !is_user_logged_in() ) {
            if( isset( $_GET['wpc_action'] ) && 'set_session' == $_GET['wpc_action'] && !empty( $_GET['login_key'] ) ) {
                $key = $_GET['login_key'];
                $user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'wpc_first_login_key' AND meta_value = '%s'", md5( $key ) ) );
                if( (int)$user_id > 0 ) {
                    wp_set_auth_cookie( $user_id, true );
                    delete_user_meta( $user_id, 'wpc_first_login_key' );
                    $redirect_to = WPC_Redirect_Rules::login_redirect_rules( WPC()->get_hub_link() , '', get_userdata( $user_id ) );
                    WPC()->redirect( $redirect_to );
                }
            }

            $ip_settings = WPC()->get_settings( 'limit_ips' );

            if( isset( $_GET['wpc_login_error'] ) && 'captcha' == $_GET['wpc_login_error'] ) {
                $GLOBALS['wpclient_login_msg'] = __( 'Invalid captcha', WPC_CLIENT_TEXT_DOMAIN );
            }

            if( isset( $_GET['wpc_login_error'] ) && 'terms' == $_GET['wpc_login_error'] ) {
                $GLOBALS['wpclient_login_msg'] = __( 'Sorry, you must agree to the Terms/Conditions to continue', WPC_CLIENT_TEXT_DOMAIN );
            }
            if ( isset( $_POST['wpclient_login_button'] ) ) {
                do_action( 'login_form_login' );
                //login from widget
                if ( !isset( $_POST['wpclient_login'] ) || '' == $_POST['wpclient_login'] ) {
                    $GLOBALS['wpclient_login_msg'] = __( 'Please enter your username!', WPC_CLIENT_TEXT_DOMAIN );
                    return '';
                }

                if ( !isset( $_POST['wpclient_pass'] ) || '' == $_POST['wpclient_pass'] ) {
                    $GLOBALS['wpclient_login_msg'] = __( 'Please enter your password!', WPC_CLIENT_TEXT_DOMAIN );
                    return '';
                }


                if ( isset( $ip_settings['enable_limit'] ) && $ip_settings['enable_limit'] == 'yes' && !empty( $_SERVER['REMOTE_ADDR'] ) ) {
                    $ip_list = array();
                    if( !empty( $ip_settings['ips'] ) ) {
                        $ip_list = explode( "\n", str_replace( array( "\n\r", "\r\n", "\r" ), "\n", $ip_settings['ips'] ) );
                    }

                    if ( !in_array( $_SERVER['REMOTE_ADDR'], $ip_list ) ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Invalid IP address!', WPC_CLIENT_TEXT_DOMAIN );
                        return '';
                    }
                }

                $args = array(
                    'user_login'    => $_POST['wpclient_login'],
                    'user_password' => $_POST['wpclient_pass'],
                    'remember'      => isset( $_POST['wpclient_rememberme'] ) ? $_POST['wpclient_rememberme'] : false,
                );

                $user = wp_signon( $args );

                if ( is_wp_error( $user ) ) {

                    $errors = $user->get_error_message();

                    if ( isset( $user->errors['invalid_username'] ) || isset( $user->errors['incorrect_password'] ) ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                    } else {
                        $GLOBALS['wpclient_login_msg'] = apply_filters( 'login_errors', $errors );
                    }

                    return '';

                }

                if( isset( $_POST['wpclient_disable_redirect'] ) && '1' == $_POST['wpclient_disable_redirect'] ) {
                    $redirect_to = $_SERVER['HTTP_REFERER'];
                } else {
                    $redirect_to = WPC_Redirect_Rules::login_redirect_rules( site_url() , '', $user );
                }

                WPC()->redirect( $redirect_to );

            } elseif ( isset( $_POST['wpc_login'] ) && 'login_form' == $_POST['wpc_login'] ) {
                do_action( 'login_form_login' );
                //login from login form
                if ( !isset( $_POST['log'] ) || '' == $_POST['log'] ) {
                    $GLOBALS['wpclient_login_msg'] = __( 'Please enter your username!', WPC_CLIENT_TEXT_DOMAIN );
                    return '';
                }

                if ( !isset( $_POST['pwd'] ) || '' == $_POST['pwd'] ) {
                    $GLOBALS['wpclient_login_msg'] = __( 'Please enter your password!', WPC_CLIENT_TEXT_DOMAIN );
                    return '';
                }


                if ( isset( $ip_settings['enable_limit'] ) && $ip_settings['enable_limit'] == 'yes' && !empty( $_SERVER['REMOTE_ADDR'] ) ) {
                    $ip_list = array();
                    if( !empty( $ip_settings['ips'] ) ) {
                        $ip_list = explode( "\n", str_replace( array( "\n\r", "\r\n", "\r" ), "\n", $ip_settings['ips'] ) );
                    }

                    if ( !in_array( $_SERVER['REMOTE_ADDR'], $ip_list ) ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Invalid IP address!', WPC_CLIENT_TEXT_DOMAIN );
                        return '';
                    }
                }

                $args = array(
                    'user_login'    => isset( $_POST['log'] ) ? $_POST['log'] : '',
                    'user_password' => isset( $_POST['pwd'] ) ? $_POST['pwd'] : '',
                    'remember'      => isset( $_POST['rememberme'] ) ? $_POST['rememberme'] : false,
                );

                $user = wp_signon( $args );
                if ( isset( $user->errors ) ) {

                    $errors = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                    $GLOBALS['wpclient_login_msg'] = apply_filters( 'login_errors', $errors );

                    if ( isset( $user->errors['invalid_username'] ) || isset( $user->errors['incorrect_password'] ) ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                        return '';
                    } else {
                        return '';
                    }
                }

                $wpc_to_redirect = ( isset( $_GET['wpc_to_redirect'] ) && !empty( $_GET['wpc_to_redirect'] ) ) ? $_GET['wpc_to_redirect'] : get_home_url();

                $redirect_to = apply_filters( 'login_redirect', $wpc_to_redirect , '', $user );

                wp_safe_redirect( $redirect_to );
                exit();

            }
        } else if( isset( $_REQUEST['wpc_action'] ) && 'temp_password' == $_REQUEST['wpc_action'] ) {
            if( !empty( $_POST['pass1'] ) && !empty( $_POST['pass2'] ) && $_POST['pass1'] == $_POST['pass2'] ) {
                $user_data = wp_get_current_user();
                $user = $user_data->data;
                wp_set_password( $_POST['pass1'], $user->ID );
                wp_set_auth_cookie( $user->ID, true );
                $redirect_to = WPC_Redirect_Rules::login_redirect_rules( '', '', get_userdata( $user->ID ) );
                WPC()->redirect( $redirect_to );
            }
        }

        return '';
    }


    /*
    * Send alert when login successful
    */
    function alert_login_successful( $username, $user ) {

        $wpc_login_alerts = WPC()->get_settings( 'login_alerts' );

        if ( isset( $wpc_login_alerts['email'] ) && '' != $wpc_login_alerts['email']
            && !empty( $user->ID ) ) {

            $args = array(
                'client_id'     => $user->ID,
                'ip_address'    => $_SERVER['REMOTE_ADDR'],
                'current_time'  => current_time( 'mysql' ),
            );

            //send email
            WPC()->mail( 'la_login_successful', $wpc_login_alerts['email'], $args, 'la_login_successful' );
        }
    }


    /*
    * Send alert when login failed
    */
    function alert_login_failed( $username ) {

        $wpc_login_alerts = WPC()->get_settings( 'login_alerts' );

        if ( isset( $wpc_login_alerts['email'] ) && '' != $wpc_login_alerts['email'] ) {
            if ( username_exists( $username ) )
                $status = __( 'Incorrect Password', WPC_CLIENT_TEXT_DOMAIN );
            else
                $status = __( 'Unknown User', WPC_CLIENT_TEXT_DOMAIN );

            $args = array(
                'la_user_name'  => $username,
                'la_status'     => $status,
                'ip_address'    => $_SERVER['REMOTE_ADDR'],
                'current_time'  => current_time( 'mysql' ),
            );

            //send email
            WPC()->mail( 'la_login_failed', $wpc_login_alerts['email'], $args, 'la_login_failed' );
        }
    }

    function get_client_staff_ids( $client_id ) {
        $args = array(
            'role'          => 'wpc_client_staff',
            'meta_key'      => 'parent_client_id',
            'meta_value'    => $client_id,
            'fields'        => 'ID',
        );

        return get_users( $args );
    }


}

endif;