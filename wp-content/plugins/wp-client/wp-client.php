<?php
/*
Plugin Name: WP-Client
Plugin URI: http://www.WP-Client.com
Description:  WP-Client WordPress Client Portal is a Client Management Plugin that gives you the ultimate in flexibility.  Integrate powerful client management and relations features into your current site.<a href="http://WP-Client.com">Visit Plugin Website</a>
Author: WP-Client.com
Version: 4.6.1
Author URI: http://www.WP-Client.com
*/

// uncomment this line for testing get updates message, but comment before do update
//set_site_transient( 'update_plugins', null );


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.


require_once 'includes/class-functions.php';
require_once 'includes/compatibility-functions.php';

if ( ! class_exists( 'WPClient' ) ) :

/**
 * Main WPClient Class.
 *
 * @class WPClient
 * @version	4.5.0
 */
final class WPClient extends WPClient_Functions {

	/**
	 * The single instance of the class.
	 *
	 * @var WPClient
	 * @since 4.5
	 */
	protected static $_instance = null;

	/**
	 * Main WPClient Instance.
	 *
	 * Ensures only one instance of WPClient is loaded or can be loaded.
	 *
	 * @since 4.5
	 * @static
	 * @return WPClient - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			self::$_instance->_wpclient_construct();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 4.5
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-client' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 4.5
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-client' ), '1.0' );
	}

	/**
	 * WP-Client Empty Constructor - fix for use WPC() inside of other classes on init plugin
	 */
	private function __construct() {
		//Please use _wpclient_construct() and do not write here anything
	}


	/**
	 * WP-Client Custom Constructor
	 */
	public function _wpclient_construct() {

        //setup proper directories
        $this->plugin_dir = WPC()->gen_plugin_dir( __FILE__ );
        $this->plugin_url = WPC()->gen_plugin_url( __FILE__ );

		$this->define_constants();
		$this->pre_load_includes();
		$this->init_hooks();

		load_plugin_textdomain( WPC_CLIENT_TEXT_DOMAIN, false, dirname( 'wp-client/wp-client.php' ) . '/languages/' );
	}

	/**
	 * Hook into actions and filters.
	 * @since  4.5.0
	 */
	private function init_hooks() {

		register_activation_hook( WPC_PLUGIN_FILE, array( $this->hooks(), 'WPC_Install->install' ) );
		register_deactivation_hook( WPC_PLUGIN_FILE, array( $this->hooks(), 'WPC_Install->deactivation' ) );

	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {

        $this->define( 'WPC_CLIENT_VER', '4.6.1' );
        $this->define( 'WPC_PLUGIN_FILE', __FILE__ );
		$this->define( 'WPC_CLIENT_TEXT_DOMAIN', 'wp-client' );
		$this->define( 'WPC_CLIENT_EXTERNAL_FONTS_DIR', 'wpclient/fonts' );

    }

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function pre_load_includes() {

		if ( ! class_exists( 'WPC_License' ) ) {
			require_once( 'includes/class-license.php' );
		}

		/**
		 * Class autoloader
		 */
		require_once( $this->plugin_dir . 'includes/class-autoloader.php' );

		require_once( $this->plugin_dir . 'includes/class-hooks-handler.php' );

		require_once( $this->plugin_dir . 'includes/class-hooks-pre-loads.php' );
	}

	/**
	 * Returns the Hooks Class of WPClient.
	 *
	 * @since  4.5.0
	 * @return WPC_Hooks
	 */
	function hooks() {
		return WPC_Hooks::instance();
	}

	/**
	 * @return WPC_Install
	 */
	function install() {
		return WPC_Install::instance();
	}

	/**
	 *
	 * @param string $prefix
	 *
	 * @return WPC_Update
	 *
	 */
	function update() {
		return WPC_Update::instance();
	}

	/**
	 * @return WPC_Extensions
	 */
	function extensions() {
		return WPC_Extensions::instance();
	}

	/**
	 * @return WPC_Files
	 */
	function files() {
		return WPC_Files::instance();
	}

	/**
	 * @return WPC_Private_Messages
	 */
	function private_messages() {
		return WPC_Private_Messages::instance();
	}

	/**
	 * @return WPC_Admin_Functions
	 */
	function admin() {
		return WPC_Admin_Functions::instance();
	}

	/**
	 * @return WPC_Members
	 */
	function members() {
		return WPC_Members::instance();
	}

	/**
	 * @return WPC_Emails
	 */
	function mailer() {
		return WPC_Emails::instance();
	}

	/**
	 * @return WPC_Pages
	 */
	function pages() {
		return WPC_Pages::instance();
	}

	/**
	 * @return WPC_Categories
	 */
	function categories() {
		return WPC_Categories::instance();
	}

	/**
	 * @return WPC_Groups
	 */
	function groups() {
		return WPC_Groups::instance();
	}

	/**
	 * @return WPC_Settings
	 */
	function settings() {
		return WPC_Settings::instance();
	}

	/**
	 * @return WPC_Notices
	 */
	function notices() {
		return WPC_Notices::instance();
	}

	/**
	 * @return WPC_Assigns
	 */
	function assigns() {
		return WPC_Assigns::instance();
	}

	/**
	 * @return WPC_Cron
	 */
	function cron() {
		return WPC_Cron::instance();
	}

	/**
	 * @return WPC_Custom_Fields
	 */
	function custom_fields() {
		return WPC_Custom_Fields::instance();
	}

	/**
	 * @return WPC_Shortcodes
	 */
	function shortcodes() {
		return WPC_Shortcodes::instance();
	}

	/**
	 * @return WPC_Templates
	 */
	function templates() {
		return WPC_Templates::instance();
	}

	/**
	 * @return WPC_Captcha
	 */
	function captcha() {
		return WPC_Captcha::instance();
	}

	/**
	 * @return WPC_Widgets
	 */
	function widgets() {
		return WPC_Widgets::instance();
	}


}

endif;

/**
 * Main instance of WPClient.
 *
 * Returns the main instance of WPC to prevent the need to use globals.
 *
 * @since  4.4.0
 * @return WPClient
 */
function WPC() {
	return WPClient::instance();
}

WPC();