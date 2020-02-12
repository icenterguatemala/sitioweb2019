<?php
/*
Plugin Name: WP-Client: Private Post Types
Plugin URI: http://www.WP-Client.com
Description: Allows you to make any page, post or custom post type part of your Portal. You can easily assign permissions, restrict public viewing, and include links to these resources in your Client's HUBs and Portal Pages.
Author: WP-Client.com
Version: 1.6.3
Author URI: http://www.WP-Client.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//pre init extension
add_action( 'wpc_client_pre_init', 'wpc_pre_ppt', -10 );

if ( ! function_exists( 'wpc_pre_ppt' ) ) {
    function wpc_pre_ppt() {

        define( 'WPC_PPT_VER', '1.6.3' );
        define( 'WPC_PPT_REQUIRED_VER', '4.5.9' );

        //set extension data for using in other products
        WPC()->extensions()->add( 'ppt', array(
            'title' => 'Private Post Types',
            'plugin' => 'wp-client-private-post-types/wp-client-private-post-types.php',
            'dir' => WPC()->gen_plugin_dir( __FILE__ ),
            'url' => WPC()->gen_plugin_url( __FILE__ ),
            'defined_version'   => WPC_PPT_VER,
            'required_version'  => WPC_PPT_REQUIRED_VER,
            'product_name'      => 'Private-Post-Types',
            'is_free'           => true,
        ) );



        function wpc_activation_ppt() {
            require_once 'includes/ppt_class.common.php';
            require_once 'includes/ppt_class.admin_common.php';

            $install = require_once 'includes/ppt_class.install.php';
            $install->install();
        }

        //Install and Updates
        add_action( 'wpc_client_extension_install_ppt', 'wpc_activation_ppt' );


        //maybe create class var
        add_action( 'wpc_client_pre_init', 'wpc_init_classes_ppt', -8 );

        if ( ! function_exists( 'wpc_init_classes_ppt' ) ) {
            function wpc_init_classes_ppt() {

                //checking for version required
                if ( WPC()->compare_versions( 'ppt' ) && ! WPC()->update()->is_crashed( 'ppt' ) ) {


                    require_once 'includes/ppt_class.common.php';

                    if ( defined( 'DOING_AJAX' ) ) {
                        require_once 'includes/ppt_class.admin_common.php';
                        require_once 'includes/ppt_class.ajax.php';
                    } elseif ( is_admin() ) {
                        require_once 'includes/ppt_class.admin_common.php';
                        require_once 'includes/ppt_class.admin.php';
                    } else {
                        require_once 'includes/ppt_class.user.php';
                    }


                    if ( defined( 'DOING_AJAX' ) ) {
                        $GLOBALS['wpc_ppt'] = new WPC_PPT_AJAX();
                    } elseif ( is_admin() ) {
                        $GLOBALS['wpc_ppt'] = new WPC_PPT_Admin();
                    } else {
                        $GLOBALS['wpc_ppt'] = new WPC_PPT_User();
                    }

                }
            }
        }


        /*
        * Function deactivation
        *
        * @return void
        */
        function wpc_deactivation_ppt() {

            WPC()->update()->deactivation( 'ppt' );
        }

        register_deactivation_hook( WPC()->extensions()->get_plugin( 'ppt' ), 'wpc_deactivation_ppt' );

    }
}