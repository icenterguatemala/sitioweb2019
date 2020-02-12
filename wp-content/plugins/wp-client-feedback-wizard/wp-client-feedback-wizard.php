<?php
/*
Plugin Name: WP-Client: Feedback Wizard
Plugin URI: http://www.WP-Client.com
Description: The Project Feedback Wizard is essentially a unique, professional, secure & efficient method whereby the administrator of the site can bundle together a specific set of images, documents, files or links - and effectively present to a client a simple and easy to follow process that allows them to provide formalized and focused feedback.
Author: WP-Client.com
Version: 1.5.1
Author URI: http://www.WP-Client.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//pre init extension
add_action( 'wpc_client_pre_init', 'wpc_pre_fw', -10 );

if ( ! function_exists( 'wpc_pre_fw' ) ) {
    function wpc_pre_fw() {

        define( 'WPC_FW_VER', '1.5.1' );
        define( 'WPC_FW_REQUIRED_VER', '4.5.0' );


        //set extension data for using in other products
        WPC()->extensions()->add( 'fw', array(
            'title' => 'Feedback Wizard',
            'plugin' => 'wp-client-feedback-wizard/wp-client-feedback-wizard.php',
            'dir' => WPC()->gen_plugin_dir( __FILE__ ),
            'url' => WPC()->gen_plugin_url( __FILE__ ),
            'defined_version'   => WPC_FW_VER,
            'required_version'  => WPC_FW_REQUIRED_VER,
            'product_name'      => 'Feedback-Wizard',
            'is_free'           => true,
        ) );



        function wpc_activation_fw() {
            require_once 'includes/fw_class.common.php';
            require_once 'includes/fw_class.admin_common.php';

            $install = require_once 'includes/fw_class.install.php';
            $install->install();
        }

        //Install and Updates
        add_action( 'wpc_client_extension_install_fw', 'wpc_activation_fw' );


        //maybe create class var
        add_action( 'wpc_client_pre_init', 'wpc_init_classes_fw', -8 );

        if ( ! function_exists( 'wpc_init_classes_fw' ) ) {
            function wpc_init_classes_fw() {

                //checking for version required
                if ( WPC()->compare_versions( 'fw' ) && ! WPC()->update()->is_crashed( 'fw' ) ) {

                    require_once 'includes/fw_class.common.php';

                    if ( defined( 'DOING_AJAX' ) ) {
                        require_once 'includes/fw_class.admin_common.php';
                        require_once 'includes/fw_class.ajax.php';
                    } elseif ( is_admin() ) {
                        require_once 'includes/fw_class.admin_common.php';
                        require_once 'includes/fw_class.admin.php';
                    } else {
                        require_once 'includes/fw_class.user_shortcodes.php';
                        require_once 'includes/fw_class.user.php';
                    }

                    if ( defined( 'DOING_AJAX' ) ) {
                        $GLOBALS['wpc_fw'] = new WPC_FW_Ajax();
                    } elseif ( is_admin() ) {
                        $GLOBALS['wpc_fw'] = new WPC_FW_Admin();
                    } else {
                        $GLOBALS['wpc_fw'] = new WPC_FW_User();
                    }

                }
            }
        }


        /*
        * Function deactivation
        *
        * @return void
        */
        function wpc_deactivation_fw() {

            WPC()->update()->deactivation( 'fw' );
        }

        register_deactivation_hook( WPC()->extensions()->get_plugin( 'fw' ), 'wpc_deactivation_fw' );

    }
}