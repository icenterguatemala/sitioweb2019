<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( "WPC_PPT_Install" ) ) {

    class WPC_PPT_Install extends WPC_PPT_Admin_Common {

        private static $instance = NULL;

        static public function getInstance() {
            if ( self::$instance === NULL )
                self::$instance = new WPC_PPT_Install();
            return self::$instance;
        }

        /**
         * PHP 5 constructor
         **/
        function __construct() {
            $this->ppt_common_construct();
            $this->ppt_admin_common_construct();
        }

        function install() {

            $this->default_settings();

            WPC()->update()->check_updates( 'ppt' );
        }


        /**
         * Set Default Settings
         **/
        function default_settings() {

            $wpc_default_settings['private_post_types'] = array(
                'action'    => 'redirect',
                'types'     => array()
            );

            //Set settings
            foreach( $wpc_default_settings as $key => $values ) {
                add_option( 'wpc_' . $key, $values );

                if ( is_array( $values ) && count( $values ) ) {
                    $current_setting = get_option( 'wpc_' . $key );
                    if ( is_array( $current_setting ) ) {
                        $new_setting = array_merge( $values, $current_setting );
                    } else {
                        $new_setting = $values;
                    }
                    update_option( 'wpc_' . $key, $new_setting );
                }
            }

        }
        //end class
    }

}

return WPC_PPT_Install::getInstance();