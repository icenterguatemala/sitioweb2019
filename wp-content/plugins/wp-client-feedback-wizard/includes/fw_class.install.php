<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( "WPC_FW_Install" ) ) {

    class WPC_FW_Install extends WPC_FW_Admin_Common {

        private static $instance = NULL;

        static public function getInstance() {
            if ( self::$instance === NULL )
                self::$instance = new WPC_FW_Install();
            return self::$instance;
        }

        /**
         * PHP 5 constructor
         **/
        function __construct() {
            $this->fw_common_construct();
            $this->fw_admin_common_construct();
        }

        function install() {

            $this->creating_db();
            $this->default_templates();

            //first install
            if ( false === get_option( 'wpc_fw_ver', false ) ) {
                //create default pages
                WPC()->install()->create_pages( $this->pre_set_pages( array() ) );

                //update rewrite rules
                WPC()->reset_rewrite_rules();
            }

            WPC()->update()->check_updates( 'fw' );
        }


        /*
        * Create DB tables
        */
        function creating_db() {
            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $charset_collate = '';

            if ( ! empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";

            // specific tables.
            $tables = "CREATE TABLE {$wpdb->prefix}wpc_client_feedback_wizards (
 wizard_id int(11) NOT NULL auto_increment,
 name varchar(100) NOT NULL default '',
 feedback_type varchar(100) default NULL,
 version varchar(10) default NULL,
 clients_id text NULL,
 groups_id text NULL,
 time text NULL,
 PRIMARY KEY  (wizard_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_feedback_items (
 item_id int(11) NOT NULL auto_increment,
 name varchar(200) default NULL,
 description text NULL,
 file_name text NULL,
 file text NULL,
 type varchar(8) default NULL,
 PRIMARY KEY  (item_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_feedback_wizard_items (
 id int(11) NOT NULL auto_increment,
 wizard_id int(11) NOT NULL default '0',
 item_id int(11) NOT NULL default '0',
 PRIMARY KEY  (id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_feedback_results (
 result_id int(11) NOT NULL auto_increment,
 wizard_id int(11) NOT NULL  default '0',
 wizard_name varchar(100) NOT NULL default '',
 wizard_version varchar(10) default NULL,
 result_text text NULL,
 time text NULL,
 client_id int(11) NOT NULL default '0',
 PRIMARY KEY  (result_id)
) $charset_collate\n;";

            dbDelta( $tables );

        }


        /**
         * Set Default Templates
         **/
        function default_templates() {

            //email when Client created
            $wpc_default_templates['templates_emails']['wizard_notify'] = array(
                'subject'               => 'Please leave feedback for wizard {wizard_name}',
                'body'                  => '<p>Hi {user_name},</p>
                    <p>We would like to get your feedback on some of the project components.</p>
                    <p>Please follow the link below - you will need to login, and then will be led through a step by step process that allows you to leave your feedback.</p>
                    <p><a href="{wizard_url}">Leave Feedback</a></p>
                    <p>Thanks,</p>
                    <p>YOUR COMPANY NAME HERE</p>',
            );

            //Set templates
            foreach( $wpc_default_templates as $key => $values ) {
                add_option( 'wpc_' . $key, $values );

                if ( is_array( $values ) && count( $values ) ) {
                    $current_setting = get_option( 'wpc_' . $key );
                    $new_setting = array_merge( $values, $current_setting );
                    update_option( 'wpc_' . $key, $new_setting );
                }
            }

        }
        //end class
    }

}

return WPC_FW_Install::getInstance();