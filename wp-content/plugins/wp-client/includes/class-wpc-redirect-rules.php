<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Redirect_Rules' ) ) :

final class WPC_Redirect_Rules {

    /**
     * The single instance of the class.
     *
     * @var WPC_Redirect_Rules
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Redirect_Rules is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Redirect_Rules - Main instance.
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
    * login redirect rules
    */
    static function login_redirect_rules( $redirect_to, $requested_redirect_to, $user ) {

        // If they're on the login page, don't do anything
        if ( !isset( $user->user_login ) ) {
            return $redirect_to;
        }

        if (  isset( $_GET['wpc_to_redirect'] ) && !empty( $_GET['wpc_to_redirect'] ) ) {
            return $_GET['wpc_to_redirect'];
        }


        //redirect by login/logout redirect table
        $wpc_enable_custom_redirects = WPC()->get_settings( 'enable_custom_redirects', 'no' );

        if ( 'yes' == $wpc_enable_custom_redirects ) {
            global $wpdb;
            if( get_user_meta( $user->ID, 'wpc_first_time_entered', true ) > 0 ) {
                $field = 'rul_first_url';
                $meta = 'first_login';
                delete_user_meta( $user->ID, 'wpc_first_time_entered' );
            } else {
                $field = 'rul_url';
                $meta = 'login';
            }
            //get individual redirect for users
            $new_redirect_to = $wpdb->get_var( $wpdb->prepare( "SELECT $field FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value = '%s' AND rul_type='user'", $user->user_login ) );

            if ( $new_redirect_to ) {
                return $new_redirect_to;
            } else {
                //redirects for circles
                $client_groups = WPC()->groups()->get_client_groups_id( $user->ID );
                if ( 0 < count( $client_groups ) ) {
                    $new_redirect_to = $wpdb->get_var( "SELECT $field FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type='circle' AND rul_url != '' AND rul_value IN('" . implode( "','", $client_groups ) . "') ORDER BY rul_order DESC LIMIT 1" );
                    if ( $new_redirect_to )
                        return $new_redirect_to;
                }


                //redirects for roles
                $userdata = get_userdata( $user->ID );
                $userroles = $userdata->roles;

                foreach( $userroles as $key=>$userrole ) {
                    $userroles[$key] = "'" . $userrole . "'";
                }
                $userroles = implode( ',', $userroles );
                if( !empty( $userroles ) ) {
                    $new_role_redirect_to = $wpdb->get_var( "SELECT $field FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value IN(" . $userroles . ") AND rul_type='role' AND rul_url != '' ORDER BY rul_order DESC LIMIT 1" );
                }

                if ( !empty( $new_role_redirect_to ) ) {
                    return $new_role_redirect_to;
                }
                //if not find redirect for user, circle and role use default redirect
                $wpc_default_redirects = WPC()->get_settings( 'default_redirects' );
                if ( isset( $wpc_default_redirects[ $meta ] ) && '' != $wpc_default_redirects[ $meta ] ) {
                    return $wpc_default_redirects[ $meta ];
                } else {
                    //redirection for administrators
                    if ( ( user_can( $user, 'wpc_admin' ) || user_can( $user, 'wpc_manager' ) || user_can( $user, 'administrator' ) ) && !user_can( $user, 'manage_network_options' ) ) {

                        if (  isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] ) ) {
                            return $redirect_to;
                        }

                        return admin_url();
                    }

                    //redirection for client staff
                    if ( user_can( $user, 'wpc_client_staff' ) && !user_can( $user, 'manage_network_options' ) )  {
                        $client_id = get_user_meta( $user->get( 'ID' ), 'parent_client_id', true );
                        if ( 0 < $client_id )
                            $user = get_userdata( $client_id );
                    }

                    //redirect Client and Staff to my-hub page
                    if ( ( user_can( $user, 'wpc_client' ) || user_can( $user, 'wpc_client_staff' ) ) && !user_can( $user, 'manage_network_options' ) )  {
                        return WPC()->get_hub_link();
                    }

                    if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
                        // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
                        if ( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin( $user->ID ) )
                            $redirect_to = user_admin_url();
                        elseif ( is_multisite() && !$user->has_cap('read') )
                            $redirect_to = get_dashboard_url( $user->ID );
                        elseif ( !$user->has_cap('edit_posts') )
                            $redirect_to = admin_url('profile.php');
                    }

                    //redirect for another users
                    return $redirect_to;
                }
            }
        } else {
            //redirection for administrators
            if ( ( user_can( $user, 'wpc_admin' ) || user_can( $user, 'wpc_manager' ) || user_can( $user, 'administrator' ) ) && !user_can( $user, 'manage_network_options' ) ) {

                if (  isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] ) ) {
                    return $redirect_to;
                }

                return admin_url();
            }

            //redirection for client staff
            if ( user_can( $user, 'wpc_client_staff' ) && !user_can( $user, 'manage_network_options' ) )  {
                $client_id = get_user_meta( $user->get( 'ID' ), 'parent_client_id', true );
                if ( 0 < $client_id )
                    $user = get_userdata( $client_id );
            }

            //redirect Client and Staff to my-hub page
            if ( ( user_can( $user, 'wpc_client' ) || user_can( $user, 'wpc_client_staff' ) ) && !user_can( $user, 'manage_network_options' ) )  {
                return WPC()->get_hub_link();
            }

            if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
                // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
                if ( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin( $user->ID ) )
                    $redirect_to = user_admin_url();


                elseif ( is_multisite() && !$user->has_cap('read') )
                    $redirect_to = get_dashboard_url( $user->ID );
                elseif ( !$user->has_cap('edit_posts') )
                    $redirect_to = admin_url('profile.php');
            }

            //redirect for another users
            return $redirect_to;
        }

    }

    /*
    * logout redirect rules
    */
    function logout_redirect_rules() {
        global $current_user;

        //Compatibility  with Duo Two-Factor Authentication plugin
        if ( false === has_action( 'authenticate', 'wp_authenticate_username_password' ) ) {
            return '';
        }

        $wpc_common_secure = WPC()->get_settings( 'common_secure' );

        //for widget - doing redirect if it set in parameter
        if ( isset( $_REQUEST['logout'] ) && 'true' == $_REQUEST['logout'] ) {
            if ( isset( $_REQUEST['redirect_to'] ) && '' != $_REQUEST['redirect_to'] ) {
                WPC()->redirect( $_REQUEST['redirect_to'] );
            }
        }

        //redirect by login/logout redirect table
        $wpc_enable_custom_redirects = WPC()->get_settings( 'enable_custom_redirects', 'no' );
        if ( isset( $wpc_enable_custom_redirects ) && 'yes' == $wpc_enable_custom_redirects ) {
            global $wpdb;

            $redirect_to = $wpdb->get_var( $wpdb->prepare( "SELECT rul_url_logout FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value = '%s' AND rul_type='user'", $current_user->user_login ) );

            if ( $redirect_to ) {
                WPC()->redirect( $redirect_to );
            } else {
                //redirects for circles
                $client_groups = WPC()->groups()->get_client_groups_id( $current_user->ID );
                if ( 0 < count( $client_groups ) ) {
                    $redirect_to = $wpdb->get_var( "SELECT rul_url_logout FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type='circle' AND rul_url_logout != '' AND rul_value IN('" . implode( "','", $client_groups ) . "') ORDER BY rul_order DESC LIMIT 1" );
                    if ( $redirect_to ) {
                        WPC()->redirect( $redirect_to );
                    }
                }

                //redirects for roles
                $userroles = $current_user->roles;

                foreach( $userroles as $key=>$userrole ) {
                    $userroles[$key] = "'" . $userrole . "'";
                }
                $userroles = implode( ',', $userroles );
                if( !empty( $userroles ) ) {
                    $redirect_to = $wpdb->get_var( "SELECT rul_url_logout FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value IN(" . $userroles . ") AND rul_type='role' AND rul_url_logout != '' ORDER BY rul_order DESC LIMIT 1" );
                }

                if ( !empty( $redirect_to ) ) {
                    WPC()->redirect( $redirect_to );
                }

                $wpc_default_redirects = WPC()->get_settings( 'default_redirects' );
                if ( isset( $wpc_default_redirects['logout'] ) && '' != $wpc_default_redirects['logout'] ) {
                    WPC()->redirect( $wpc_default_redirects['logout'] );
                } else {
                    //redirection for administrators
                    if ( ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) && !current_user_can( 'manage_network_options' ) )  {
                        if (  !empty( $wpc_common_secure['login_url'] ) && WPC()->permalinks ) {
                            WPC()->redirect( $wpc_common_secure['login_url'] );
                        }
                        WPC()->redirect( wp_login_url() );
                    }

                    //redirect for all if not set another
                    if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) )  {
                        $client_id = get_user_meta( $current_user->ID , 'parent_client_id', true );
                        if ( 0 < $client_id ) {
                            $user = get_userdata( $client_id );
                            if ( ( user_can( $user, 'wpc_client' ) || user_can( $user, 'wpc_client_staff' ) ) && !user_can( $user, 'manage_network_options' ) )  {
                                WPC()->redirect( WPC()->get_login_url() );
                            }
                        }
                    }

                    //redirect Client and Staff to my-hub page
                    if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'manage_network_options' ) )  {
                        WPC()->redirect( WPC()->get_login_url() );
                    }
                    //redirect for another users
                    if ( !empty( $wpc_common_secure['login_url'] ) && WPC()->permalinks ) {
                        WPC()->redirect( $wpc_common_secure['login_url'] );
                    }
                    WPC()->redirect( wp_login_url() );
                }
            }
        } else {
            //redirection for administrators
            if ( ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) && !current_user_can( 'manage_network_options' ) )  {
                if ( !empty( $wpc_common_secure['login_url'] ) && WPC()->permalinks ) {
                    WPC()->redirect( $wpc_common_secure['login_url'] );
                }
                WPC()->redirect( wp_login_url() );
            }

            //redirect for all if not set another
            if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) )  {
                $client_id = get_user_meta( $current_user->ID , 'parent_client_id', true );
                if ( 0 < $client_id ) {
                    $user = get_userdata( $client_id );
                    if ( ( user_can( $user, 'wpc_client' ) || user_can( $user, 'wpc_client_staff' ) ) && !user_can( $user, 'manage_network_options' ) )  {
                        WPC()->redirect( WPC()->get_login_url() );
                    }
                }
            }

            //redirect Client and Staff to my-hub page
            if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'manage_network_options' ) )  {
                WPC()->redirect( WPC()->get_login_url() );
            }

            if ( !empty( $wpc_common_secure['login_url'] ) && WPC()->permalinks ) {
                WPC()->redirect( $wpc_common_secure['login_url'] );
            }
            WPC()->redirect( wp_login_url() );
        }
    }






}

endif;