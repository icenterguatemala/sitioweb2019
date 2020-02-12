<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( "WPC_PR_Install" ) ) {

    class WPC_PR_Install extends WPC_PR_Common {

        private static $instance = NULL;

        static public function getInstance() {
            if ( self::$instance === NULL )
                self::$instance = new WPC_PR_Install();
            return self::$instance;
        }

        /**
         * PHP 5 constructor
         **/
        function __construct() {
            $this->pr_common_construct();
        }

        function install() {

            //first install
            if ( false === get_option( 'wpc_pr_ver', false ) ) {
                //update rewrite rules
                WPC()->reset_rewrite_rules();
            }

            WPC()->update()->check_updates( 'pr' );
        }

        //end class
    }

}


return WPC_PR_Install::getInstance();