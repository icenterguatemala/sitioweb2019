<?php
/*
Plugin Name: WP-Client: Time Limited Clients
Plugin URI: http://www.WP-Client.com
Description: Easily set an expiration date for each individual client after which that clients login will no longer allow access. Their credentials are still in place, but they receive a customizable error notification explaining that their login has expired.
Author: WP-Client.com
Version: 1.4.1
Author URI: http://www.WP-Client.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//pre init extension
add_action( 'wpc_client_pre_init', 'wpc_pre_tlc', -10 );

if ( ! function_exists( 'wpc_pre_tlc' ) ) {
    function wpc_pre_tlc() {

        define( 'WPC_TLC_VER', '1.4.1' );
        define( 'WPC_TLC_REQUIRED_VER', '4.5.0' );

        //set extension data for using in other products
        WPC()->extensions()->add( 'tlc', array(
            'title' => 'Time Limited Clients',
            'plugin' => 'wp-client-time-limited-clients/wp-client-time-limited-clients.php',
            'dir' => WPC()->gen_plugin_dir( __FILE__ ),
            'url' => WPC()->gen_plugin_url( __FILE__ ),
            'defined_version'   => WPC_TLC_VER,
            'required_version'  => WPC_TLC_REQUIRED_VER,
            'product_name'      => 'Time-Limited-Clients',
            'is_free'           => true,
        ) );


        function wpc_activation_tlc() {

        }

        //Install and Updates
        add_action( 'wpc_client_extension_install_tlc', 'wpc_activation_tlc' );


        //maybe create class var
        add_action( 'wpc_client_pre_init', 'wpc_init_classes_tlc', -8 );

        if ( ! function_exists( 'wpc_init_classes_tlc' ) ) {
            function wpc_init_classes_tlc() {

                //checking for version required
                if ( WPC()->compare_versions( 'tlc' ) && ! WPC()->update()->is_crashed( 'tlc' ) ) {

                    require_once 'includes/tlc_class.common.php';

                    if ( is_admin() ) {
                        require_once 'includes/tlc_class.admin.php';
                    } else {
                        require_once 'includes/tlc_class.user.php';
                    }


                    if ( is_admin() ) {
                        $GLOBALS['wpc_tlc'] = new WPC_TLC_Admin();
                    } else {
                        $GLOBALS['wpc_tlc'] = new WPC_TLC_User();
                    }

                }
            }
        }


        /*
        * Function deactivation
        *
        * @return void
        */
        function wpc_deactivation_tlc() {

            WPC()->update()->deactivation( 'tlc' );
        }

        register_deactivation_hook( WPC()->extensions()->get_plugin( 'tlc' ), 'wpc_deactivation_tlc' );

    }
}