<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( "WPC_Install" ) ):

class WPC_Install {

    /**
     * The single instance of the class.
     *
     * @var WPC_Install
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Install is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Install - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
    * PHP 5 constructor
    **/
    function __construct() {

        //fix for load custom titles for install
        WPC_Hooks_Pre_Loads::set_custom_titles();
    }


    /*
    * Install function for setup WP-Client data when doing plugin activation
    *
    * @return void
    */
    function install() {

        //maybe do update for LITE version
        $this->maybe_lite_version();

        $this->creating_db();
        $this->default_settings();
        $this->default_templates();

        //create custom post type
        WPC_Hooks_Pre_Loads::_create_post_type();

        //maybe do first install
        $this->maybe_first_install();

        $this->roles_capabilities();

        WPC()->update()->check_updates( 'core' );
    }


    /*
    * Function deactivation
    *
    * @return void
    */
    function deactivation() {

        WPC()->update()->deactivation( 'core' );

//        update_option( 'wpc_client_activation', false );
    }



    /*
    * Maybe was LITE version and do update for LITE
    *
    * @return void
    */
    function maybe_lite_version() {
        $lite_ver = get_option( 'wp_client_lite_ver' );
        if ( $lite_ver ) {
            if ( version_compare( $lite_ver, '1.2.0', '<' ) ) {
                add_option( 'wp_client_ver', '3.7.0' );
                update_option( 'wp_client_lite_ver_old', $lite_ver );
                delete_option( 'wp_client_lite_ver' );
            }
        }
    }

    /*
    * Maybe do first install
    *
    * @return void
    */
    function maybe_first_install() {
        if ( false === get_option( 'wp_client_ver', false ) ) {
            //set default pages
            $this->create_pages();

            //create default PortalHUB
            $this->create_default_portalhub();
        }
    }

    /*
    * Create DB tables
    *
    * @return void
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
        $tables = "CREATE TABLE {$wpdb->prefix}wpc_client_groups (
group_id int(11) NOT NULL auto_increment,
group_name varchar(255) NOT NULL,
auto_select varchar(1) NULL,
auto_add_files varchar(1) NULL,
auto_add_pps varchar(1) NULL,
auto_add_manual varchar(1) NULL,
auto_add_self varchar(1) NULL,
PRIMARY KEY  (group_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_group_clients (
group_id int(11) NOT NULL,
client_id int(11) NOT NULL
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_login_redirects (
rul_type enum('user','circle','role','level','all') NOT NULL,
rul_value varchar(255) NOT NULL default '',
rul_url LONGTEXT NULL,
rul_first_url LONGTEXT NULL,
rul_url_logout LONGTEXT NULL,
rul_order int(2) NOT NULL default '0'
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_clients_page (
id mediumint(9) NOT NULL AUTO_INCREMENT,
pagename tinytext NOT NULL,
template tinytext NOT NULL,
users tinytext NOT NULL,
PRIMARY KEY  (id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_chains (
id int(11) NOT NULL AUTO_INCREMENT,
subject text DEFAULT NULL,
PRIMARY KEY  (id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_messages (
id int(11) NOT NULL AUTO_INCREMENT,
chain_id int(11) NOT NULL,
author_id int NOT NULL,
content text NOT NULL,
date text NOT NULL,
PRIMARY KEY  (id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_files (
id int(11) NOT NULL AUTO_INCREMENT,
order_id int(11) NULL,
user_id int NOT NULL,
page_id int NOT NULL,
time text NOT NULL,
last_download text NULL,
size int(32) NOT NULL,
filename text NOT NULL,
name text NOT NULL,
title varchar(255) NULL,
description text NULL,
cat_id int NULL,
protect_url TINYINT(1) NULL,
external TINYINT(1) NULL DEFAULT 0,
PRIMARY KEY  (id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_file_categories (
cat_id int(11) NOT NULL AUTO_INCREMENT,
cat_name text NULL,
folder_name text NULL,
cat_order int NULL,
parent_id int(11) NOT NULL DEFAULT 0,
PRIMARY KEY  (cat_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_objects_assigns (
id bigint(20) NOT NULL AUTO_INCREMENT,
object_type enum('file','file_category','portal_page','portal_page_category','post_category','portalhub','ez_hub','manager','feedback_wizard','invoice','accum_invoice','repeat_invoice','estimate','request_estimate','shutter','shutter_category','form','brand','campaign','shutter_order','chain','new_message','trash_chain','archive_chain','ticket','private_post','ams_service','ams_level') NOT NULL,
object_id bigint(20) NULL,
assign_type enum('circle','client','email_list') NOT NULL,
assign_id bigint(20) NULL,
PRIMARY KEY  (id),
KEY objectid_assignid (object_id,assign_id),
KEY objectid (object_id),
KEY assignid (assign_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_portal_page_categories (
cat_id int(11) NOT NULL AUTO_INCREMENT,
cat_name text NULL,
PRIMARY KEY  (cat_id)
) $charset_collate\n;
CREATE TABLE {$wpdb->prefix}wpc_client_payments (
id int(11) NOT NULL AUTO_INCREMENT,
order_id varchar(50) NULL,
order_status varchar(30) NULL,
function varchar(50) NULL,
payment_method varchar(50) NULL,
payment_type varchar(64) DEFAULT NULL,
client_id int(11) NULL,
amount varchar(30) NULL,
currency varchar(10) NULL,
data text NULL,
transaction_id text NULL,
transaction_status text NULL,
time_created text NULL,
time_paid text NULL,
subscription_id varchar(50) NULL,
subscription_status varchar(50) NULL,
next_payment_date varchar(25) NULL,
PRIMARY KEY  (id)
) $charset_collate\n;
CREATE TABLE {$wpdb->prefix}wpc_client_files_download_log (
id int(11) NOT NULL AUTO_INCREMENT,
file_id int(11) NOT NULL,
client_id int(11) NOT NULL,
download_date text NULL,
PRIMARY KEY  (id)
) $charset_collate\n;
CREATE TABLE {$wpdb->prefix}wpc_client_categories (
id int(11) NOT NULL AUTO_INCREMENT,
parent_id int(11) NOT NULL DEFAULT 0,
name text NULL,
type enum('file','portal_page','shutter','shutter_size','ticket_cats','ticket_types') NOT NULL,
cat_order int NULL,
PRIMARY KEY  (id)
)$charset_collate\n;";

        dbDelta( $tables );
    }

    /*
    * Pre-set all plugin's pages
    *
    * @return array
    */
    function pre_set_pages() {

        $wpc_pre_pages = array(

            array(
                'title'     => __( 'Login Page', WPC_CLIENT_TEXT_DOMAIN ),
                'name'      => 'Login Page',
                'desc'      => __( 'Page content: [wpc_client_loginf]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'login_page_id',
                'old_id'    => 'login',
                'shortcode' => true,
                'content'   => '[wpc_client_loginf]',
            ),
            array(
                'title'     => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                'name'      => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                'desc'      => __( 'Page content: [wpc_client_edit_portal_page]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'edit_portal_page_id',
                'old_id'    => 'edit_clientpage',
                'shortcode' => true,
                'content'   => '[wpc_client_edit_portal_page]',
            ),
            array(
                'title'     => sprintf( __( '%s Directory', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'name'      => sprintf( __( '%s Directory', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'desc'      => __( 'Page content: [wpc_client_staff_directory]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'staff_directory_page_id',
                'old_id'    => 'staff_directory',
                'shortcode' => true,
                'content'   => '[wpc_client_staff_directory]',
            ),
            array(
                'title'     => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'name'      => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'desc'      => __( 'Page content: [wpc_client_add_staff_form]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'add_staff_page_id',
                'old_id'    => 'add_staff',
                'shortcode' => true,
                'content'   => '[wpc_client_add_staff_form]',
            ),
            array(
                'title'     => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'name'      => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'desc'      => __( 'Page content: [wpc_client_edit_staff_form]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'edit_staff_page_id',
                'old_id'    => 'edit_staff',
                'shortcode' => true,
                'content'   => '[wpc_client_edit_staff_form]',
            ),
            array(
                'title'     => sprintf( __( '%s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'name'      => sprintf( __( '%s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'desc'      => __( 'Page content: [wpc_client_registration_form]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'client_registration_page_id',
                'old_id'    => 'registration',
                'shortcode' => true,
                'content'   => '[wpc_client_registration_form]',
            ),
            array(
                'title'     => sprintf( __( 'Successful %s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'name'      => sprintf( __( 'Successful %s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'desc'      => __( 'Page content: [wpc_client_registration_successful]', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'successful_client_registration_page_id',
                'old_id'    => 'registration_successful',
                'shortcode' => true,
                'content'   => '[wpc_client_registration_successful]',
            ),
            array(
                'title'     => __( 'Error', WPC_CLIENT_TEXT_DOMAIN ),
                'name'      => 'Error',
                'desc'      => __( 'Page content: [wpc_client_error_image] or any text', WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'error_page_id',
                'old_id'    => '',
                'shortcode' => false,
                'content'   => '[wpc_client_error_image]',
            ),
            array(
                'title'     => sprintf( __( '%s Profile', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'name'      => sprintf( __( '%s Profile', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'desc'      => __( "Page content: [wpc_client_profile]", WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'profile_page_id',
                'old_id'    => '',
                'shortcode' => true,
                'content'   => "[wpc_client_profile]",
            ),
            array(
                'title'     => sprintf( __( '%s Profile', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'name'      => sprintf( __( '%s Profile', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                'desc'      => __( "Page content: [wpc_staff_profile]", WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'staff_profile_page_id',
                'old_id'    => '',
                'shortcode' => true,
                'content'   => "[wpc_staff_profile]",
            ),
            array(
                'title'     => __( 'Payment Process', WPC_CLIENT_TEXT_DOMAIN ),
                'name'      => 'Payment Process',
                'desc'      => __( "Page content: [wpc_client_payment_process]", WPC_CLIENT_TEXT_DOMAIN ),
                'id'        => 'payment_process_page_id',
                'old_id'    => '',
                'shortcode' => true,
                'content'   => "[wpc_client_payment_process]",
            )
        );

        return $wpc_pre_pages;
    }


    /*
    * Create all plugin's pages
    *
    * @return void
    */
    function create_pages( $pages = array() ) {

        //get pages
        if ( is_array( $pages ) && count( $pages ) ) {
            $wpc_pre_pages = $pages;
        } else {
            $wpc_pre_pages = $this->pre_set_pages();
        }


        $wpc_client_page = get_page_by_title( 'Portal' );

        if ( !isset( $wpc_client_page ) || 0 >= $wpc_client_page->ID ) {

            $current_user = wp_get_current_user();
            //Construct args for the new page
            $args = array(
                'post_title'     => 'Portal',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_content'   => '[wpc_redirect_on_login_hub]',
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            $parent_page_id = wp_insert_post( $args );
        } else {
            $parent_page_id = $wpc_client_page->ID;
        }

        //pages from settings
        $wpc_pages = WPC()->get_settings( 'pages' );

        //create page if needs
        foreach( $wpc_pre_pages as $pre_page ) {

            if( isset( $wpc_pages[$pre_page['id']] ) && is_numeric( $wpc_pages[$pre_page['id']] ) ) {
                $current_page = get_post( $wpc_pages[$pre_page['id']] );
            }

            if ( !isset( $wpc_pages[$pre_page['id']] ) || 0 >= $wpc_pages[$pre_page['id']] || '' == $wpc_pages[$pre_page['id']] || !isset( $current_page->ID ) ) {

                $wpc_client_page = get_page_by_title( $pre_page['name'] );
                if ( !isset( $wpc_client_page ) || 0 >= $wpc_client_page->ID ) {

                    $current_user = wp_get_current_user();
                    //Construct args for the new page
                    $args = array(
                        'post_title'        => $pre_page['name'],
                        'post_status'       => 'publish',
                        'post_author'       => $current_user->ID,
                        'post_content'      => $pre_page['content'],
                        'post_type'         => 'page',
                        'ping_status'       => 'closed',
                        'comment_status'    => 'closed',
                        'post_parent'       => $parent_page_id,
                    );
                    $page_id = wp_insert_post( $args );

                    $wpc_pages[$pre_page['id']] = $page_id;
                } else {
                    $wpc_pages[$pre_page['id']] = $wpc_client_page->ID;
                }
            }
        }

        WPC()->settings()->update( $wpc_pages, 'pages' );
    }



    /**
     * Set default currencies for fist activation
     *
     * @param boolean $hard If true - do anyway
     *
     * @return void
     */
    function set_default_currencies( $hard = false ) {

        $wpc_currency = WPC()->get_settings( 'currency' );

        // first activation or $hard = true
        if ( !empty( $wpc_currency ) && !$hard ) {
            return;
        }

        $exists_codes = $exists_key = array();

        foreach( $wpc_currency as $key => $val ) {
            if ( isset( $val['code'] ) ) {
                $exists_codes[] = $val['code'];
            }
            $exists_key[] = $key;
        }

        $new_currencies = array(
            array(
                'default' => !count($wpc_currency) ? 1 : 0,
                'title' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '&#36;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'European Euro',
                'code' => 'EUR',
                'symbol' => '&#8364;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'UK Pound Sterling',
                'code' => 'GBP',
                'symbol' => '&#163;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Japanese Yen',
                'code' => 'JPY',
                'symbol' => '&#165;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Canadian Dollar',
                'code' => 'CAD',
                'symbol' => 'C&#36;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Australian Dollar',
                'code' => 'AUD',
                'symbol' => 'A&#36;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Chinese Yuan',
                'code' => 'CNY',
                'symbol' => '&#165;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Swiss Franc',
                'code' => 'CHF',
                'symbol' => '&#8355;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Singapore Dollar',
                'code' => 'SGD',
                'symbol' => 'S&#36;',
                'align' => 'left',
            ),
            array(
                'default' => 0,
                'title' => 'Hong Kong Dollar',
                'code' => 'HKD',
                'symbol' => 'HK&#36;',
                'align' => 'left',
            ),
        );

        foreach ( $new_currencies as $value ) {
            if ( in_array( $value['code'], $exists_codes ) ) {
                continue;
            }

            do {
                $key = uniqid('',1);
            } while( in_array( $key, $exists_key ) && $exists_key[] = $key);
            $wpc_currency[ $key ] = $value;
        }

        WPC()->settings()->update( $wpc_currency, 'currency' );
    }


    /**
     * Set Default Settings
     *
     * @return void
     */
    function default_settings() {
        global $wpdb;

        $settings_wizard = WPC()->get_settings( 'wizard_setup' );
        $wpc_default_settings['wizard_setup'] = empty($settings_wizard) ? 'true' : 'false';
        $wpc_default_settings['email_sending'] = array(
            'sender_name'      => get_bloginfo( 'name' ),
            'sender_email'     => '',
            'reply_email'      => '',
        );
        $wpc_default_settings['general'] = array(
            'hub_link_text'                 => 'My HUB',
            'exclude_pp_from_search'        => 'yes',
            'avatars_shapes'                => 'square',
            'graphic'                       => '',
            'easy_mode'                     => 'no',
        );
        $wpc_default_settings['clients_staff'] = array(
            'hide_dashboard'                => 'no',
            'create_portal_page'            => 'yes',
            'use_portal_page_settings'      => '0',
            'hide_admin_bar'                => 'yes',
            'lost_password'                 => 'no',
            'client_registration'           => 'no',
            'avatar_on_registration'        => 'no',
            'auto_client_approve'           => 'no',
            'new_client_admin_notify'       => 'yes',
            'send_approval_email'           => 'no',
            'staff_registration'            => 'no',
        );

        $wpc_default_settings['captcha'] = array(
            'enabled'               => 'no',
            'publickey_2'           => '',
            'privatekey_2'          => '',
            'theme'                 => 'red',
        );

        $wpc_default_settings['file_sharing'] = array(
            'allow_file_cats'               => 'yes',
            'google_doc_embed'              => 'no',
            'admin_uploader_type'           => 'plupload',
            'client_uploader_type'          => 'plupload',
            'file_size_limit'               => '',
            'attach_file_admin'             => 'no',
            'nesting_category_assign'       => 'no',
            'bulk_download_zip'             => 'files',
        );

        $wpc_default_settings['custom_login'] = array(
            'cl_enable'             => 'yes',
            'cl_background'         => WPC()->plugin_url .'images/logo.png',
            'cl_backgroundColor'    => 'ffffff',
            'cl_color'              => '000033',
            'cl_linkColor'          => '00A5E2',
        );

        $wpc_default_settings['common_secure'] = array(
            'login_url'          => '',
            'hide_admin'         => 'no'
        );

        $wpc_default_settings['business_info'] = array();

        $wpc_default_settings['enable_custom_redirects'] = 'no';

        $wpc_default_settings['default_redirects'] = array(
            'login' => '',
            'logout' => '',
        );

        $wpc_default_settings['capabilities'] = array();

        $wpc_default_settings['pages'] = array();

        $wpc_default_settings['gateways'] = array(
            'allowed' => array(),
        );

        $wpc_default_settings['custom_titles'] = array();

        $wpc_default_settings['login_alerts'] = array(
            'email'         => '',
            'successful'    => '0',
            'failed'        => '0',
        );

        $wpc_default_settings['skins'] = 'light';

        $wpc_default_settings['smtp'] = array(
            'enable_smtp'   => false,
            'smtp_host'     => '',
            'smtp_port'     => '',
            'secure_prefix' => '',
            'smtp_username' => '',
            'smtp_password' => ''
        );

        $wpc_default_settings['limit_ips'] = array(
            'enable_limit'  => 'no',
            'ips'           => ''
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

        $this->set_default_currencies();

        //add default style default scheme
        $wpc_style_settings = WPC()->get_settings( 'style__default_scheme_sections' );
        if ( !$wpc_style_settings ) {

            $wpc_customize = new WPC_Customize();

            $default_style_scheme = $wpc_customize->get_style_schemes();
            $default_sections = $wpc_customize->get_default_sections();

            $default_style_scheme['_default_scheme']['key'] = '_default_scheme';

            $wpc_customize->save_style_settings( $default_style_scheme['_default_scheme'], $default_sections );

        }

        //create key for remote synchronization
        add_option( 'wpc_client_sync_key', md5( time() . uniqid() ) );

        //create General category
        if ( ! $wpdb->get_var( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_name = 'General' AND parent_id='0'" ) ) {
            $args = array(
                'cat_id'      => '0',
                'cat_name'    => 'General',
                'folder_name' => 'general',
                'parent_id'   => '0'
            );

            $cat_order = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(cat_id)
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE parent_id=%d",
                $args['parent_id']
            ) );
            $cat_order++;

            //insert when add new category
            $wpdb->insert(
                "{$wpdb->prefix}wpc_client_file_categories",
                array(
                    'cat_name'      => trim( $args['cat_name'] ),
                    'folder_name'   => trim( $args['folder_name'] ),
                    'parent_id'     => $args['parent_id'],
                    'cat_order'     => $cat_order
                ),
                array( '%s', '%s', '%d', '%d' )
            );

            //create category folder
            WPC()->files()->create_file_category_folder( $wpdb->insert_id, trim( $args['folder_name'] ) );
        }
    }


    /**
    * Set Default Templates
     *
     * @return void
     */
    function default_templates() {

        $wpc_default_templates['templates_clientpage'] =  htmlentities( stripslashes( '
<p>[wpc_client]<span style="font-size: medium;">Welcome {client_business_name} to your first Portal Page<span style="font-size: small;"> | [wpc_client_get_page_link page="hub" text="HUB Page"] | [wpc_client_logoutb]</span></span></p>
<p>We\'ll be using this page to relay information and graphics to you.</p>
<p>You can use the private messaging feature at the bottom of each page if you\'d like to communicate with us, and all of our interaction will be here in one place.</p>
<p>Thanks!</p>
<table style="width: 100%;" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="width: 50%; height: 70px;" valign="top"></td>
<td style="width: 50%;" valign="top"></td>
</tr>
<tr>
<td colspan="2" valign="top"><img title="" alt="" src="[wpc_client_theme][/wpc_client_theme]/messages.png" /></td>
</tr>
<tr>
<td colspan="2" valign="top">[wpc_client_com][/wpc_client_com]</td>
</tr>
</tbody>
</table>
<p>[/wpc_client]</p>
' ) );


        //email when Client created by admin
        $wpc_default_templates['templates_emails']['new_client_password'] = array(
            'subject'               => 'Your Private and Unique Client Portal has been created',
            'body'                  => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>Your private and secure Client Portal has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        $wpc_default_templates['templates_emails']['self_client_registration'] = array(
            'subject'               => 'Your Private and Unique Client Portal has been created',
            'body'                  => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>Your private and secure Client Portal has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        $wpc_default_templates['templates_emails']['convert_to_client'] = array(
            'subject'               => 'Your Private and Unique Client Portal has been created',
            'body'                  => '<p>Hello {contact_name},</p>
            <p>Your private and secure Client Portal has been created.</p>
            <p>Your Username is : {user_name}</p>
            <p>You can login by clicking <a target="_blank" href="{login_url}">HERE</a></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        $wpc_default_templates['templates_emails']['convert_to_staff'] = array(
            'subject'               => 'Your Staff account has been created',
            'body'                  => '<p>Hello {contact_name},</p>
            <p>You have been granted access to a private and secure Client Portal.</p>
            <p>Your Username is : {user_name}</p>
            <p>You can login by clicking <a target="_blank" href="{login_url}">HERE</a></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        $wpc_default_templates['templates_emails']['convert_to_manager'] = array(
            'subject'               => 'Your Manager account has been created',
            'body'                  => '<p>Hello {contact_name},</p>
            <p>Your manager account has been created.</p>
            <p>Your Username is : {user_name}</p>
            <p>You can login by clicking <a target="_blank" href="{login_url}">HERE</a></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        $wpc_default_templates['templates_emails']['convert_to_admin'] = array(
            'subject'               => 'Your Admin account has been created',
            'body'                  => '<p>Hello {contact_name},</p>
            <p>Your admin account has been created.</p>
            <p>Your Username is : {user_name}</p>
            <p>You can login by clicking <a target="_blank" href="{login_url}">HERE</a></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email when Client created for verify email
        $wpc_default_templates['templates_emails']['new_client_verify_email'] = array(
            'subject'               => 'Please verify your email address',
            'body'                  => '<p>Hello {contact_name}<br /> <br /> </p>
            <p>Before you can access your portal, please verify your email address by clicking <strong><a href="{verify_url}">HERE</a></strong></p>
            <p>Thanks,</p>
            <p>{business_name}</p>',
        );

        //email when Client updated
        $wpc_default_templates['templates_emails']['client_updated'] = array(
            'subject'   => 'Your Client Password has been updated',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>Your password has been updated. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email when Portal Page is updated
        $wpc_default_templates['templates_emails']['client_page_updated'] = array(
            'subject'   => sprintf( __( 'Your %s has been updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
            'body'      => sprintf( __('<p>Hello {contact_name},</p>
                        <p>Your %s, {page_title} has been updated | <a href="{page_id}">Click HERE to visit</a></p>
                        <p>Thanks, and please contact us if you experience any difficulties,</p>
                        <p>{business_name}</p>', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] )
        );

        //email when Admin/Manager uploaded file
        $wpc_default_templates['templates_emails']['new_file_for_client_staff'] = array(
            'subject'   => 'New file at {site_title}',
            'body'      => '<p>You have been given access to a file at {site_title}</p>
                        <p>Click <a href="{login_url}">HERE</a> to access the file.</p>',
        );

        //email when Client registered
        $wpc_default_templates['templates_emails']['new_client_registered'] = array(
            'subject'   => 'A new client has registered on your site | {site_title}',
            'body'      => '<p>To approve this new client, you will need to login and navigate to > Clients > <strong><a href="{approve_url}">Approve Clients</a></strong></p>',
        );

        //email to Admin and Managers when Client uploaded the file
        $wpc_default_templates['templates_emails']['client_uploaded_file'] = array(
            'subject'   => 'The user {user_name} uploaded a file at {site_title}',
            'body'      => '<p>The user {user_name} uploaded a file. To view/download the file, click <a href="{admin_file_url}">HERE</a>"</p>',
        );

        //email to Admin and Managers when Client downloaded the file
        $wpc_default_templates['templates_emails']['client_downloaded_file'] = array(
            'enable'    => '0',
            'subject'   => 'The user {user_name} downloaded a file {file_name} at {site_title}',
            'body'      => '<p>The user {user_name} downloaded a file "{file_name}".</p>',
        );

        //email when Staff created
        $wpc_default_templates['templates_emails']['staff_created'] = array(
            'subject'   => 'Your Staff account has been created',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>You have been granted access to a private and secure Client Portal. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email when Client registered Staff
        $wpc_default_templates['templates_emails']['staff_registered'] = array(
            'subject'   => 'Your Staff account has been registered',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>You have been granted access to our private and secure Client Portal. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email to admin when Client registered Staff
        $wpc_default_templates['templates_emails']['staff_created_admin_notify'] = array(
            'subject'   => 'A new Staff user has been created on your site | {site_title}',
            'body'      => '<p>To approve this new Staff, you will need to login and navigate to > Clients > <a href="{approve_url}" target="_blank">Staff Approve</a></p>',
        );

        //email when Manager created
        $wpc_default_templates['templates_emails']['manager_created'] = array(
            'subject'   => 'Your Manager account has been created',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>Your manager account has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email when Manager Updated
        $wpc_default_templates['templates_emails']['manager_updated'] = array(
            'subject'   => 'Your Manager Password has been updated',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>Your password has been updated. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>'
        );

        //email when Admin created
        $wpc_default_templates['templates_emails']['admin_created'] = array(
            'subject'   => 'Your Admin account has been created',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
            <p>Your manager account has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email when Admin send message to Client
        $wpc_default_templates['templates_emails']['notify_client_about_message'] = array(
            'subject'   => 'A user: {user_name} from {site_title} has sent you a private message',
            'body'      => '<p>A user: {user_name} has sent you a private message. To see the message login <a href="{login_url}">here</a>.</p>',
        );

        //email when Client send message to CC
        $wpc_default_templates['templates_emails']['notify_cc_about_message'] = array(
            'subject'   => "A new private message from {user_name}, sent from '{site_title}'",
            'body'      => '<p>{user_name} says,
                        <br/>
                        {message}
                        </p>',
        );

        //email when Client send message to Admin/Manager
        $wpc_default_templates['templates_emails']['notify_admin_about_message'] = array(
            'subject'   => "You've received a new private message from {user_name}, sent from '{site_title}'",
            'body'      => '<p>{user_name} says,
                        <br/>
                        {message}
                        <br/>
                        <br/>
                        To view the entire thread of messages and send a reply, click <a href="{admin_url}">HERE</a></p>',
        );

        //email when Client approved
        $wpc_default_templates['templates_emails']['account_is_approved'] = array(
            'subject'   => 'Your account is approved',
            'body'      => '<p>Hello {contact_name},<br /> <br /> Your account is approved.</p>
            <p>You can login by clicking <strong><a href="{login_url}">HERE</a></strong></p>
            <p>Thanks, and please contact us if you experience any difficulties,</p>
            <p>{business_name}</p>',
        );

        //email when Client reset it`s password
        $wpc_default_templates['templates_emails']['reset_password'] = array(
            'subject'   => '[{blog_name}]Password Reset',
            'body'      => '<p>Hi {user_name},</p>
                        <p>You have requested to reset your password.</p>
                        <p>Please follow the link below.</p>
                        <p><a href="{reset_address}">Reset Your Password</a></p>
                        <p>Thanks,</p>
                        <p>{business_name}</p>',
        );

        //email when updated Private Post type page
        $wpc_default_templates['templates_emails']['private_post_type'] = array(
            'subject'   => 'You have been given access to {page_title}',
            'body'      => '<p>Hello {contact_name},</p>
                        <p>You have been given access to {page_title} | <a href="{page_id}">Click HERE to visit</a></p>
                        <p>Thanks, and please contact us if you experience any difficulties,</p>
                        <p>{business_name}</p>',
        );

        //email when updated Private Post type page
        $wpc_default_templates['templates_emails']['profile_updated'] = array(
            'subject'   => 'A client has updated their Profile | {site_title}',
            'body'      => '<p>Hello {contact_name},</p>
                        <p>To view the client\'s updated profile info, login here: <a href="{admin_url}" target="_blank">LOGIN</a></p>
                        <p>{business_name}</p>',
        );

        $wpc_default_templates['templates_emails']['la_login_successful'] = array(
            'subject'   => '{user_name} was logged in successfully',
            'body'      => '<p>User Name: {user_name}</p>
                <p>Description: Was Logged Successfully</p>
                <p>Alert From: {site_url}</p>
                <p>IP Address: {ip_address}</p>
                <p>Date: {current_time}</p>',
        );

        $wpc_default_templates['templates_emails']['la_login_failed'] = array(
            'subject'   => '{la_user_name} was logged in failed',
            'body'      => '<p>User Name: {la_user_name}</p>
                <p>Description: {la_status}</p>
                <p>Alert From: {site_url}</p>
                <p>IP Address: {ip_address}</p>
                <p>Date: {current_time}</p>',
        );

        //Set templates
        foreach( $wpc_default_templates as $key => $values ) {

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


    /**
     * Create default HUB content
     *
     * @return void
     */
    function create_default_portalhub() {

        $content = '
<h2 style="text-align: left;">Hi {contact_name}! Welcome to your private portal!</h2>
<p style="text-align: left;">[wpc_client_logoutb/] &lt; Click here to logout</p>
<p style="text-align: left;">From this HUB Page, you can access all the pages, documents, photos &amp; files that you have access to.</p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your ' . WPC()->custom_titles['portal_page']['p'] . '</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_pagel show_categories_titles="yes" show_current_page="yes" sort_type="date" sort="asc" /]</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your Files</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_filesla show_file_cats="yes" show_sort="yes" show_date="yes" show_size="yes" show_tags="yes" category="" no_text="" exclude_author="yes" /]</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your Uploaded Files</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_fileslu show_file_cats="yes" show_sort="yes" show_date="yes" show_size="yes" show_tags="yes" category="" no_text="" /]</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Upload Files Here</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_uploadf category="" /]</p>
<p dir="ltr" style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Private Messages</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_com redirect_after="" /]</p>';

        $post_id = wp_insert_post( array(
            'post_title'    => __( 'Simple Template', WPC_CLIENT_TEXT_DOMAIN ),
            'post_type'     => 'portalhub',
            'post_content'  => $content,
            'post_status'   => 'publish',
        ) );

        update_post_meta( $post_id, 'wpc_default_template', true );
    }

    /**
     * Update rewrite_rules
     *
     * @return void
     */
    function roles_capabilities() {
        global $wp_roles;

        //remore old role
        $wp_roles->remove_role( "pcc_client" );

        //get capabilities
        $wpc_capabilities = WPC()->get_settings( 'capabilities' );

        $capabilities_maps = WPC()->get_capabilities_maps();
        $wpc_caps = array();
        foreach ( $capabilities_maps as $key_role => $capabilities_map ) {
            if( isset( $capabilities_map['variable'] ) && is_array( $capabilities_map['variable'] ) ) {
                foreach ( $capabilities_map['variable'] as $cap_key=>$cap_val ) {
                    $cap = ( isset( $wpc_capabilities[ $key_role ][ $cap_key ] ) && true == $wpc_capabilities[ $key_role ][ $cap_key ] ) ? true : $cap_val['cap'];
                    $wpc_caps[ $key_role ][ $cap_key ] = $cap;
                }
            }
            if( isset( $capabilities_map['permanent'] ) && is_array( $capabilities_map['permanent'] ) ) {

                if ( ! isset( $wpc_caps[ $key_role ] ) )
                    $wpc_caps[ $key_role ] = array();

                $wpc_caps[ $key_role ] = array_merge( $wpc_caps[ $key_role ], $capabilities_map['permanent'] );
            }
        }

        $caps = isset( $wpc_caps['wpc_client'] ) ? $wpc_caps['wpc_client'] : array();
        //remore role for update capabilities
        $wp_roles->remove_role( "wpc_client" );
        //add role for clients
        $wp_roles->add_role( "wpc_client", 'WPC Client', $caps );


        $caps = isset( $wpc_caps['wpc_client_staff'] ) ? $wpc_caps['wpc_client_staff'] : array();
        //remore role for update capabilities
        $wp_roles->remove_role( "wpc_client_staff" );
        //add role for clients
        $wp_roles->add_role( "wpc_client_staff", 'WPC Client STAFF', $caps );


        $caps = isset( $wpc_caps['wpc_manager'] ) ? $wpc_caps['wpc_manager'] : array();
        //remore role for update capabilities
        $wp_roles->remove_role( 'wpc_manager' );
        //add role for manager
        $wp_roles->add_role( 'wpc_manager', 'WPC Manager', $caps );


        $caps = isset( $wpc_caps['wpc_admin'] ) ? $wpc_caps['wpc_admin'] : array();
        //remore role for update capabilities
        $wp_roles->remove_role( "wpc_admin" );
        //add role for admins
        $wp_roles->add_role( 'wpc_admin', 'WPC Admin', $caps );

        $cpt_capability_map = array_merge(
            array_values( WPC()->get_post_type_caps_map( 'clientspage' ) ),
            array_values( WPC()->get_post_type_caps_map( 'portalhub' ) ),
            array( 'wpc_admin_user_login' )
        );

        //set capability for Portal Pages to Admin
        foreach ( $cpt_capability_map as $capability ) {
            $wp_roles->add_cap( 'administrator', $capability );
        }

        //update rewrite rules
        WPC()->reset_rewrite_rules();
    }


}

endif;