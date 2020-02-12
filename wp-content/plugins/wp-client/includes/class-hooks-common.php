<?php

// Exit if executed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPC_Hooks_Common' ) ) :

class WPC_Hooks_Common {

    /**
     * WPC_Hooks_Common Constructor.
     */
    public function __construct() {

        //check and create crons
        add_filter( 'cron_schedules', array( WPC()->hooks(), 'WPC_Cron->wpc_cron_add_periods' ), 10, 1 );
        add_action( 'wpc_main_cron', array( WPC()->hooks(), 'WPC_Cron->main_cron' ) );

        //add rewrite rules
        add_filter( 'rewrite_rules_array', array( &$this, '_insert_rewrite_rules' ) );

        if ( ! defined( 'DOING_AJAX' ) ) {
            add_filter( 'wp_loaded', array( WPC()->hooks(), 'WPClient->maybe_flush_rewrite_rules' ) );
        }

        add_action( 'parse_request', array( &$this, 'load_shortcodes' ), 10001 );

        add_filter( 'load_textdomain_mofile', array( &$this, 'wpc_load_textdomain_mofile' ), 10, 2 );


        //downloaders
        add_action( 'wpc_core_file_downloader', array( WPC()->hooks(), 'WPC_Files->core_file_downloader' ) );
        add_action( 'wpc_custom_fields_file_downloader', array( WPC()->hooks(), 'WPC_Files->custom_fields_file_downloader' ) );


        /* members hooks */
        add_action( 'set_user_role', array( WPC()->hooks(), 'WPC_Members->auto_convert_user_register' ), 10, 2 );
        add_action( 'add_user_role', array( WPC()->hooks(), 'WPC_Members->auto_convert_user_register' ), 10, 2 );
        add_action( 'user_register', array( WPC()->hooks(), 'WPC_Members->insert_screen_options_meta' ), 10, 1 );
        add_action( 'validate_password_reset', array( WPC()->hooks(), 'WPC_Members->validate_password_reset' ) );
        add_filter( 'retrieve_password_message', array( WPC()->hooks(), 'WPC_Members->replace_retrieve_password_message' ), 10, 1 );
        add_action( 'delete_user', array( WPC()->hooks(), 'WPC_Members->delete_client' ) );
        add_action( 'wpmu_delete_user', array( WPC()->hooks(), 'WPC_Members->delete_client' ) );

//            add_action( 'wpmu_new_user', array( &$this, 'auto_convert_user_register' ), 99 );


        //login\logout redirect
        add_filter( 'login_redirect', array( WPC()->hooks(), 'WPC_Redirect_Rules->login_redirect_rules' ), 100, 3 );
        add_action( 'wp_logout', array( WPC()->hooks(), 'WPC_Redirect_Rules->logout_redirect_rules' ), 1000 );

        //replace placeholders for Start Pages title
        add_action( 'wp_head', array( WPC()->hooks(), 'WPC_Pages->wp_head' ), -1 );
        add_filter( 'wp_title', array( WPC()->hooks(), 'WPC_Pages->replace_wp_title' ), 200000, 2 );
        add_filter( 'the_title', array( WPC()->hooks(), 'WPC_Pages->replace_wp_title' ), 200000, 2 );
        add_filter( 'the_content', array( WPC()->hooks(), 'WPC_Pages->replace_placeholders_in_content' ) );

        add_filter( 'post_type_link', array( WPC()->hooks(), 'WPC_Pages->portalhub_permalink' ), 10, 2 );

        add_action( 'wpc_client_init', array( &$this, 'init_php_templates' ) );

        add_action( 'wpc_client_init', array( &$this, 'include_payment_core' ), 10 );

        add_action( 'wpc_client_ftp_synchronization', array( WPC()->hooks(), 'WPC_Files->wpc_cron_synchronization' ) );

        add_action( 'wpc_assign_popup_update_additional_data', array( WPC()->hooks(), 'WPC_Assigns->assign_popup_send_notification' ), 10, 2 );

        add_action( 'send_email_notification_cron', array( &$this, 'send_email_notification_cron' ), 10, 4 );

        add_filter( 'all_plugins', array( &$this, 'white_label_change_plugin_info' ), 10 );

        //Tutorials
        add_filter( 'wpc_set_array_help', array( WPC()->hooks(), 'WPC_Tutorials->set_array_help' ), 99, 2 );
        add_filter( 'wpc_get_started_content', array( WPC()->hooks(), 'WPC_Tutorials->get_started_content' ) );
        add_action( 'wp_loaded', array( WPC()->hooks(), 'WPC_Tutorials->init' ), 999 );


        /*
         * Some hooks
         */
        //todo: Needs to review
        add_action( 'init', array( &$this, 'redo_login_form' ) );
        add_action( 'init', array( &$this, 'hide_admin_bar_2' ) );

        add_filter( 'login_url', array( &$this, 'replace_wp_login_url' ), 10, 2 );
        add_filter( 'logout_url', array( &$this, 'replace_wp_logout_url' ), 10, 2 );

        add_action( 'admin_enqueue_scripts', array( &$this, 'include_admin_login_js' ), 99 );
        add_action( 'wp_enqueue_scripts', array( &$this, 'include_admin_login_js' ), 99 );

        add_action( 'clear_auth_cookie', array( &$this, 'clear_login_key_cookie' ) );

        add_action( 'current_screen', array( &$this, 'hide_admin_bar' ) );

        add_filter( 'wpc_assign_popup_add_blocks', array( WPC()->hooks(), 'WPC_Files->file_sharing_checkbox' ), 10, 2 );

        WPC()->cron()->add_crons( array( 'wpc_main_cron' => array( 'period' => 'twicedaily' ) ) );
        /*
         * END - Some hooks
         */
    }


    /**
     * Adding our rewrite rules
     *
     * @param array $rules
     *
     * @return array
     */
    function _insert_rewrite_rules( $rules ) {
        $newrules = array();

        //Start Pages
        $pages = WPC()->get_settings( 'pages' );
        $portalhub_slug = isset( $pages['portal_hub_slug'] ) ? $pages['portal_hub_slug'] : 'portal/portal-hub';

        if ( ! empty( $portalhub_slug ) )
            $newrules[WPC()->make_url( $portalhub_slug )] = 'index.php?wpc_page=portalhub';

        //varify email
        $newrules[WPC()->make_url( 'portal/acc-activation/(.+?)/?$' )] = 'index.php?wpc_page=acc_activation&wpc_page_value=$matches[1]';

        //edit portal page
        $newrules[WPC()->get_slug( 'edit_portal_page_id', false, false ) . '/(.+?)/?$'] = 'index.php?wpc_page=edit_portal_page&wpc_page_value=$matches[1]';

        //edit staff
        $newrules[WPC()->get_slug( 'edit_staff_page_id', false, false ) . '/(\d*)/?$'] = 'index.php?wpc_page=edit_staff&wpc_page_value=$matches[1]';

        //downloader
        $newrules[WPC()->make_url( 'wpc_downloader/([^/]+?)/?$' )] = 'index.php?wpc_page=wpc_downloader&wpc_page_value=$matches[1]';
        $newrules[WPC()->make_url( 'wpc_downloader/([^/]+?)/([^/]+?)/([^/]+?)/?$' )] = 'index.php?wpc_page=wpc_downloader&wpc_page_value=$matches[1]&wpc_google_hash=$matches[2]&ext=$matches[3]';

        //ipn handling for payment gateways
        $newrules[WPC()->make_url( 'wpc-ipn-handler-url/(.+?)/?$' )] = 'index.php?wpc_page=payment_ipn&wpc_page_value=$matches[1]';
        $newrules[WPC()->get_slug( 'payment_process_page_id', false, false ) . '/(.+?)/(.+?)/?$'] = 'index.php?wpc_page=payment_process&wpc_order_id=$matches[1]&wpc_page_value=$matches[2]';

        return $newrules + $rules;
    }

    /**
     * Set plugin data
     *
     * @return bool
     */
    function load_shortcodes() {
        $install = get_option( 'wp_client_ver', false );

        if ( !$install || ! WPC()->is_licensed( 'WP-Client' ) ) {
            return false;
        }

        if ( !defined( 'DOING_AJAX' ) && !is_admin() && !defined('REST_REQUEST') ) {
            WPC()->shortcodes()->us_register_shortcode();
        }

        return true;
    }


    function wpc_load_textdomain_mofile( $mofile, $domain ) {
        if ( 0 !== strpos( $domain, 'wp-client' ) )
            return $mofile;

        /*
         * select mo file from main language for dialects if dialect translation does not exists
         * For example use fr_FR mo file for language fr_CA
         * */
        if( !file_exists( $mofile ) ) {
            $pathinfo = pathinfo( $mofile );
            $array = explode( '-', $pathinfo['filename'] );
            $lang_code_array = explode('_', $array[ count( $array ) - 1 ]);
            if( !isset( $lang_code_array[1] ) || $lang_code_array[0] != $lang_code_array[1] ) {
                $new_mo = trailingslashit( $pathinfo['dirname'] ) .
                    str_replace( $array[ count( $array ) - 1 ], $lang_code_array[0] . '_' . strtoupper( $lang_code_array[0] ), $pathinfo['basename'] );
                    if( file_exists( $new_mo ) ) {
                        $mofile = $new_mo;
                    }
            }
        }
        preg_match( '/[^\/]+$/', $mofile, $matches );
        $selected_file = ( isset( $matches['0'] ) ) ? $matches['0'] : '';

        if ( '' == $selected_file )
            return $mofile;

        $target_path = WPC()->get_upload_dir( 'wpclient/_languages/' );

        if ( file_exists( $target_path . $selected_file ) ) {
            return $target_path . $selected_file;
        } else {
            return $mofile;
        }
    }

    function init_php_templates() {
        /*our_hook_
            hook_name: wpc_extend_php_templates
            hook_title: Extend PHP Templates
            hook_description: Can be used for extending PHP Shortcode Templates.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: array $templates ( $key => $templates_dir )
            hook_since: 4.4.0
        */
        WPC()->templates()->php_templates = apply_filters( 'wpc_extend_php_templates', array( '' => WPC()->plugin_dir . 'templates' ) );
    }


    function include_payment_core() {

        //include payments core
        include_once WPC()->plugin_dir . 'includes/payments_core.php';
    }


    function send_email_notification_cron( $data, $params, $send_client_ids, $key ) {
        global $wpdb;

        if( isset( $data['id'] ) && is_numeric( $data['id'] ) ) {
            $file_id = $data['id'];
        } else {
            return;
        }
        //send notify

        $cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_files WHERE id = %d", $file_id ) );
        $orig_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}wpc_client_files WHERE id = %d", $file_id ) );
        $new_name = $wpdb->get_var( $wpdb->prepare( "SELECT filename FROM {$wpdb->prefix}wpc_client_files WHERE id = %d", $file_id ) );
        $args = array(
            'cat_id'    => $cat_id,
            'filename'  => $new_name
        );
        $filepath    = WPC()->files()->get_file_path( $args );

        $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

        foreach( $send_client_ids as $send_client_id ) {
            $send_client_user = get_userdata( $send_client_id );

            if ( '' != $send_client_id && false !== $send_client_user ) {

                $email_args = array( 'client_id' => $send_client_id, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id) );

                $client_email = $send_client_user->get( 'user_email' );
                //send email to client
                if( isset( $data['send_attach_file_user'] ) && '1' == $data['send_attach_file_user'] && isset( $filepath ) ) {
                    WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff', $filepath );
                } else {
                    WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff' );
                }
                //get client staff
                $args = array(
                    'role'          => 'wpc_client_staff',
                    'meta_key'      => 'parent_client_id',
                    'meta_value'    => $send_client_id,
                );
                $staffs = get_users( $args );

                //send email to staff
                if ( is_array( $staffs ) && 0 < count( $staffs ) ) {
                    foreach( $staffs as $staff ) {
                        $email_args = array( 'client_id' => $staff->ID, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id) );
                        if( isset( $data['send_attach_file_user'] ) && '1' == $data['send_attach_file_user'] && isset( $filepath ) ) {
                            //send email
                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff', $filepath );
                        } else {
                            //send email
                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff' );
                        }
                    }
                }
            }
        }
    }


    /**
     * replace plugins info
     */
    function white_label_change_plugin_info( $plugins ) {
        if ( $plugins ) {
            foreach( $plugins as $key => $value ) {
                if ( false !== get_option( 'whtlwpc_settings' ) && !empty( WPC()->plugin['old_title'] ) && 0 === strpos( $value['Name'],  WPC()->plugin['old_title'] ) ) {
                    if ( strlen( $value['Name'] ) == strlen( WPC()->plugin['old_title'] ) ) {
                        $plugins[$key]['Description'] = WPC()->plugin['description'];
                    }

                    $plugins[$key]['Name'] = str_replace( WPC()->plugin['old_title'], WPC()->plugin['title'], $value['Name'] );
                    $plugins[$key]['Author'] = WPC()->plugin['author'];
                    $plugins[$key]['AuthorURI'] = WPC()->plugin['author_uri'];
                    $plugins[$key]['PluginURI'] = WPC()->plugin['plugin_uri'];
                }
            }

        }
        return $plugins;

    }


    function redo_login_form() {

        // for default permalinks
        if( !WPC()->permalinks )
            return '';

        $wpc_common_secure = WPC()->get_settings( 'common_secure' );

        $cs_login_url = ( !empty( $wpc_common_secure['login_url'] ) ) ? $wpc_common_secure['login_url'] : '';

        // It's not enabled.
        if ( '' == $cs_login_url )
            return '';

        // The blog's URL
        $blog_url = trailingslashit( get_bloginfo( 'url' ) );
        $blog_url = str_replace( 'https://', '', $blog_url );
        $blog_url = str_replace( 'http://', '', $blog_url );

        // The Current URL
        $current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $request_url = str_replace( $blog_url, '', $current_url );
        $request_url = str_replace( 'index.php/', '', $request_url );

        $url_parts = explode( '?', $request_url, 2 );
        $base = $url_parts[0];

        // Remove trailing slash
        $base = rtrim( $base, "/" );


        if( $base === $cs_login_url ) {
            $cookie_time = time() + 3600*24;
            $cookie_array = array(
                'time' => time(),
                'hash' => md5( time() . $cs_login_url )
            );

            nocache_headers();

            WPC()->setcookie( "wpc_custom_login_nonce", urlencode( serialize( $cookie_array ) ), $cookie_time );

            if( isset( $_GET ) && !empty( $_GET ) ) {
                WPC()->redirect( add_query_arg( $_GET, site_url( 'wp-login.php' ) ) );
            } else {
                WPC()->redirect( site_url( 'wp-login.php' ) );
            }


            /*if( isset( $_GET ) && !empty( $_GET ) ) {
                WPC()->redirect( add_query_arg( $_GET, wp_login_url() ) );
            } else {
                WPC()->redirect( wp_login_url() );
            }*/
        }


        if( isset( $_COOKIE['wpc_custom_login_nonce'] ) && !empty( $_COOKIE['wpc_custom_login_nonce'] ) ) {
            $cookie_array = unserialize( urldecode( $_COOKIE['wpc_custom_login_nonce'] ) );
            $cookie_time = isset( $cookie_array['time'] ) ? $cookie_array['time'] : 0;
            if( isset( $cookie_array['hash'] ) && md5( $cookie_time . $cs_login_url ) == $cookie_array['hash'] ) {
                if( time() - $cookie_time > 3600 ) {
                    WPC()->setcookie( "wpc_custom_login_nonce", "", time() - 1 );
                }
            } else {
                WPC()->setcookie( "wpc_custom_login_nonce", "", time() - 1 );
            }
        }


        // Are they visiting wp-login.php?
        if( WPC()->is_wp_login() && !is_user_logged_in() &&
            !( isset( $_GET['action'] ) && $_GET['action'] == 'postpass' ) ) {

            if ( !isset( $_COOKIE['wpc_custom_login_nonce'] ) ) {
                WPC()->throw_404();
            } else {
                $cookie_array = unserialize( urldecode( $_COOKIE['wpc_custom_login_nonce'] ) );
                if( isset( $cookie_array['hash'] ) && md5( $cookie_array['time'] . $cs_login_url ) != $cookie_array['hash'] ) {
                    WPC()->throw_404();
                }
            }
        }

        return '';
    }

    function hide_admin_bar_2( $content ) {
        //hide admin bar for client\staff
        if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'manage_network_options' ) )  {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
            if ( !isset( $wpc_clients_staff['hide_admin_bar'] ) || 'yes' == $wpc_clients_staff['hide_admin_bar'] ) {
                add_filter( 'show_admin_bar', function() { return false; } );
            }
        }


    }


    function replace_wp_login_url( $login_url, $redirect ) {

        if( !WPC()->permalinks )
            return $login_url;

        if( WPC()->is_wp_login() )
            return $login_url;

        $wpc_common_secure = WPC()->get_settings( 'common_secure' );

        if ( !empty( $wpc_common_secure['login_url'] ) ) {
            $login_url = home_url() . '/' . $wpc_common_secure['login_url'];
        }

        return $login_url;
    }


    function replace_wp_logout_url( $logout_url, $redirect ) {
        if( !WPC()->permalinks )
            return $logout_url;

        $wpc_common_secure = WPC()->get_settings( 'common_secure' );

        if ( !empty( $wpc_common_secure['login_url'] ) ) {
            $parse_url = parse_url( $logout_url );
            $logout_url = home_url() . '/' . $wpc_common_secure['login_url'] . '?' . $parse_url['query'];
        }

        return $logout_url;
    }


    function include_admin_login_js() {
        global $wpdb, $post;
        if( !empty( $_COOKIE['wpc_key'] ) && is_user_logged_in() ) {
            $key = $_COOKIE['wpc_key'];
            $user_data = $wpdb->get_row( $wpdb->prepare( "SELECT umeta_id, user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'wpc_client_admin_secure_data' AND meta_value LIKE '%s'", '%"' . md5( $key ) . '"%' ), ARRAY_A );
            if( isset( $user_data['user_id'] ) && user_can( $user_data['user_id'], 'wpc_admin_user_login') ) {
                if( !empty( $user_data['meta_value'] ) ) {
                    $secure_array = unserialize( $user_data['meta_value'] );
                    if( isset( $secure_array['end_date'] ) && $secure_array['end_date'] > time() &&
                        isset( $secure_array['client_id'] ) && get_current_user_id() == $secure_array['client_id'] &&
                        isset( $secure_array['ip'] ) && $_SERVER['REMOTE_ADDR'] == $secure_array['ip'] ) {
                        wp_enqueue_script( 'wpc_client_admin_login' );

                        $schema = is_ssl() ? 'https://' : 'http://';
                        $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                        $wpc_var = array(
                            'message' => sprintf( __( "Remaining %d minutes", WPC_CLIENT_TEXT_DOMAIN ), round( ( $secure_array['end_date'] - time() ) / 60 ) ),
                            'button_value' => __( "Return to admin panel", WPC_CLIENT_TEXT_DOMAIN ),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'secure_key' => wp_create_nonce( get_current_user_id() . $user_data['user_id'] ),
                            'current_url' => $current_url
                        );


                        if ( $post->post_type == 'clientspage' ) {
                            $wpc_var['page_id'] = $post->ID;
                            $users = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'client' );
                            if ( user_can( $user_data['user_id'], 'wpc_manager' ) && !user_can( $user_data['user_id'], 'administrator' )  ) {
                                //show only manager clients
                                $clients = WPC()->members()->get_all_clients_manager( $user_data['user_id'] );
                                $users = array_intersect( $users, $clients );
                            }
                            if( count( $users ) ) {
                                $users = $wpdb->get_results( "SELECT ID, user_login as login FROM {$wpdb->users} WHERE ID IN ('" . implode( "','", $users ) . "')", ARRAY_A );
                                $wpc_var['current_user_id'] = get_current_user_id();
                            } else {
                                $users = array();
                            }

                            $wpc_var['clients_list'] = $users;
                        }

                        wp_localize_script( 'wpc_client_admin_login', 'wpc_var', $wpc_var );
                    } else {
                        $wpdb->delete( $wpdb->usermeta,
                            array(
                                'umeta_id' => $user_data['umeta_id']
                            )
                        );

                        WPC()->setcookie( "wpc_key", '', time() - 1 );
                    }
                }
            }
        }
    }


    function clear_login_key_cookie() {
        WPC()->setcookie( "wpc_key", '', time() - 1 );
    }


    function hide_admin_bar( $screen ) {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return $screen;

        if ( !empty( $screen->id ) && preg_match( '/_page_wpclients_customize$/', $screen->id) ) {
            add_action( 'admin_print_scripts-' . $screen->id, array( &$this, 'hide_admin_bar_settings' ) );
        }

        return $screen;
    }


    function hide_admin_bar_settings() {
        ?>
        <style type="text/css">
            #wpadminbar {
                display: none;
            }

            html.wp-toolbar {
                padding-top: 0;
            }

        </style>
        <?php
    }





} //end class

endif;

new WPC_Hooks_Common();