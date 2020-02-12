<?php
/*
Plugin Name: WP-Client: Paid Registration
Plugin URI: http://www.WP-Client.com
Description: Configure the self registration system to only give clients access after they have paid using one of the provided payment gateways.
Author: WP-Client.com
Version: 1.4.2
Author URI: http://www.WP-Client.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//pre init extension
add_action( 'wpc_client_pre_init', 'wpc_pre_pr', -10 );

if ( ! function_exists( 'wpc_pre_pr' ) ) {
    function wpc_pre_pr() {

        define( 'WPC_PR_VER', '1.4.2' );
        define( 'WPC_PR_REQUIRED_VER', '4.5.9' );

        if ( ! defined( 'WPC_CLIENT_PAYMENTS' ) ) {
            define( 'WPC_CLIENT_PAYMENTS', 1 );
        }


        //set extension data for using in other products
        WPC()->extensions()->add( 'pr', array(
            'title' => 'Paid Registration',
            'plugin' => 'wp-client-paid-registration/wp-client-paid-registration.php',
            'dir' => WPC()->gen_plugin_dir( __FILE__ ),
            'url' => WPC()->gen_plugin_url( __FILE__ ),
            'defined_version'   => WPC_PR_VER,
            'required_version'  => WPC_PR_REQUIRED_VER,
            'product_name'      => 'Paid-Registration',
            'is_free'           => true,
        ) );



        function wpc_activation_pr() {
            require_once 'includes/pr_class.common.php';

            $install = require_once 'includes/pr_class.install.php';
            $install->install();
        }

        //Install and Updates
        add_action( 'wpc_client_extension_install_pr', 'wpc_activation_pr' );


        //maybe create class var
        add_action( 'wpc_client_pre_init', 'wpc_init_classes_pr', -8 );

        if ( ! function_exists( 'wpc_init_classes_pr' ) ) {
            function wpc_init_classes_pr() {

                //checking for version required
                if ( WPC()->compare_versions( 'pr' ) && ! WPC()->update()->is_crashed( 'pr' ) ) {

                    require_once 'includes/pr_class.common.php';

                    if ( defined( 'DOING_AJAX' ) ) {
                        require_once 'includes/pr_class.ajax.php';
                    } elseif ( is_admin() ) {
                        require_once 'includes/pr_class.admin.php';
                    } else {
                        require_once 'includes/pr_class.user.php';
                    }


                    if ( defined( 'DOING_AJAX' ) ) {
                        $GLOBALS['wpc_pr'] = new WPC_PR_AJAX();
                    } elseif ( is_admin() ) {
                        $GLOBALS['wpc_pr'] = new WPC_PR_Admin();
                    } else {
                        $GLOBALS['wpc_pr'] = new WPC_PR_User();
                    }

                }
            }
        }


        /*
        * Function deactivation
        *
        * @return void
        */
        function wpc_deactivation_pr() {

            WPC()->update()->deactivation( 'pr' );
        }

        register_deactivation_hook( WPC()->extensions()->get_plugin( 'pr' ), 'wpc_deactivation_pr' );

    }
}