<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( "WPC_LL_Install" ) ) {

    class WPC_LL_Install extends WPC_LL_Common {

        private static $instance = NULL;

        static public function getInstance() {
            if ( self::$instance === NULL )
                self::$instance = new WPC_LL_Install();
            return self::$instance;
        }

        /**
         * PHP 5 constructor
         **/
        function __construct() {
            $this->common_construct();
        }


        function install() {

            //activation
            $this->creating_db();

            WPC()->update()->check_updates( 'll' );
        }


        /*
        * Create DB tables
        */
        function creating_db() {
            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $charset_collate = '';

            if ( ! empty( $wpdb->charset ) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty( $wpdb->collate ) )
                $charset_collate .= " COLLATE $wpdb->collate";

            // specific tables.
            $tables = "CREATE TABLE {$wpdb->prefix}wpc_client_login_logs (
 id int(11) NOT NULL auto_increment,
 user_id int(11) NOT NULL,
 login_time text NOT NULL,
 ip_address varchar(30) NULL,
 login_from text NULL,
 status text NOT NULL,
 PRIMARY KEY  (id)
) $charset_collate\n;";

            dbDelta( $tables );

        }

        //end class
    }

}

return WPC_LL_Install::getInstance();