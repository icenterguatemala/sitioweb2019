<?php
/*
Plugin Name: WP-Client: Estimates/Invoices
Plugin URI: http://www.WP-Client.com
Description: Easily create estimates and invoices that your clients can pay online using the provided payment gateways. You can display invoices on your website, send in PDF format via email, or print out and send in traditional snail mail.
Author: WP-Client.com
Version: 1.9.6
Author URI: http://www.WP-Client.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


//pre init extension
add_action( 'wpc_client_pre_init', 'wpc_pre_inv', -10 );

if ( ! function_exists( 'wpc_pre_inv' ) ) {
    function wpc_pre_inv() {

        define( 'WPC_INV_VER', '1.9.6' );
        define( 'WPC_INV_REQUIRED_VER', '4.5.8' );

        if ( ! defined( 'WPC_CLIENT_PAYMENTS' ) )
            define( 'WPC_CLIENT_PAYMENTS', 1 );


        //set extension data for using in other products
        WPC()->extensions()->add( 'inv', array(
            'title' => 'Estimates/Invoices',
            'plugin' => 'wp-client-invoicing/wp-client-invoicing.php',
            'dir' => WPC()->gen_plugin_dir( __FILE__ ),
            'url' => WPC()->gen_plugin_url( __FILE__ ),
            'defined_version'   => WPC_INV_VER,
            'required_version'  => WPC_INV_REQUIRED_VER,
            'product_name'      => 'Estimates-Invoices',
            'is_free'           => true,
        ) );



        function wpc_activation_inv() {
            require_once 'includes/inv_class.common.php';
            require_once 'includes/inv_class.admin_common.php';

            $install = require_once 'includes/inv_class.install.php';
            $install->install();
        }

        //Install and Updates
        add_action( 'wpc_client_extension_install_inv', 'wpc_activation_inv' );


        //maybe create class var
        add_action( 'wpc_client_pre_init', 'wpc_init_classes_inv', -8 );

        if ( ! function_exists( 'wpc_init_classes_inv' ) ) {
            function wpc_init_classes_inv() {

                //checking for version required
                if ( WPC()->compare_versions( 'inv' ) && ! WPC()->update()->is_crashed( 'inv' ) ) {

                    require_once 'includes/inv_class.common.php';

                    if ( defined( 'DOING_AJAX' ) ) {
                        require_once 'includes/inv_class.admin_common.php';
                        require_once 'includes/inv_class.ajax.php';
                    } elseif ( is_admin() || defined( 'DOING_CRON' ) ) {
                        require_once 'includes/inv_class.admin_common.php';
                        require_once 'includes/inv_class.admin_meta_boxes.php';
                        require_once 'includes/inv_class.admin.php';
                    } else {
                        require_once 'includes/inv_class.user_shortcodes.php';
                        require_once 'includes/inv_class.user.php';
                    }


                    if ( defined( 'DOING_AJAX' ) ) {
                        $GLOBALS['wpc_inv'] = new WPC_INV_AJAX();
                    } elseif ( is_admin() || defined( 'DOING_CRON' ) ) {
                        $GLOBALS['wpc_inv'] = new WPC_INV_Admin();
                    } else {
                        $GLOBALS['wpc_inv'] = new WPC_INV_User();
                    }

                }
            }
        }


        /*
        * Function deactivation
        *
        * @return void
        */
        function wpc_deactivation_inv() {

            WPC()->update()->deactivation( 'inv' );
            delete_option('wpc_inv_ver');
        }

        register_deactivation_hook( WPC()->extensions()->get_plugin( 'inv' ), 'wpc_deactivation_inv' );

    }
}