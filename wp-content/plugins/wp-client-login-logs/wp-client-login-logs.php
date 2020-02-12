<?php
/*
Plugin Name: WP-Client: Login Logs
Plugin URI: http://www.WP-Client.com
Description: When users log into your site, the details are retained in the the database, and a report is generated so you can see a complete record of who has logged into the site.
Author: WP-Client.com
Version: 1.4.1
Author URI: http://www.WP-Client.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//pre init extension
add_action( 'wpc_client_pre_init', 'wpc_pre_ll', -10 );

if ( ! function_exists( 'wpc_pre_ll' ) ) {
    function wpc_pre_ll() {

        define( 'WPC_LL_VER', '1.4.1' );
        define( 'WPC_LL_REQUIRED_VER', '4.5.0' );


        //set extension data for using in other products
        WPC()->extensions()->add( 'll', array(
            'title' => 'Login Logs',
            'plugin' => 'wp-client-login-logs/wp-client-login-logs.php',
            'dir' => WPC()->gen_plugin_dir( __FILE__ ),
            'url' => WPC()->gen_plugin_url( __FILE__ ),
            'defined_version'   => WPC_LL_VER,
            'required_version'  => WPC_LL_REQUIRED_VER,
            'product_name'      => 'Login-Logs',
            'is_free'           => true,
        ) );



        function wpc_activation_ll() {
            require_once 'includes/ll_class.common.php';

            $install = require_once 'includes/ll_class.install.php';
            $install->install();
        }

        //Install and Updates
        add_action( 'wpc_client_extension_install_ll', 'wpc_activation_ll' );


        //maybe create class var
        add_action( 'wpc_client_pre_init', 'wpc_init_classes_ll', -8 );

        if ( ! function_exists( 'wpc_init_classes_ll' ) ) {
            function wpc_init_classes_ll() {

                //checking for version required
                if ( WPC()->compare_versions( 'll' ) && ! WPC()->update()->is_crashed( 'll' ) ) {

                    require_once 'includes/ll_class.common.php';

                    if ( defined( 'DOING_AJAX' ) ) {
                        require_once 'includes/ll_class.ajax.php';
                    } elseif ( is_admin() ) {
                        require_once 'includes/ll_class.admin.php';
                    } else {
                        require_once 'includes/ll_class.user.php';
                    }


                    if ( defined( 'DOING_AJAX' ) ) {
                        $GLOBALS['wpc_login_logs'] = new WPC_LL_Ajax();
                    } elseif ( is_admin() ) {
                        $GLOBALS['wpc_login_logs'] = new WPC_LL_Admin();
                    } else {
                        $GLOBALS['wpc_login_logs'] = new WPC_LL_User();
                    }

                }
            }
        }


        /*
        * Function deactivation
        *
        * @return void
        */
        function wpc_deactivation_ll() {

            WPC()->update()->deactivation( 'll' );
        }

        register_deactivation_hook( WPC()->extensions()->get_plugin( 'll' ), 'wpc_deactivation_ll' );

    }
}