<?php
/*
Plugin Name: Mikado Core
Description: Plugin that adds all post types needed by our theme
Author: Mikado Themes
Version: 1.0.1
*/

require_once 'load.php';

use MikadoCore\CPT;
use MikadoCore\Lib;

add_action('after_setup_theme', array(CPT\PostTypesRegister::getInstance(), 'register'));

Lib\ShortcodeLoader::getInstance()->load();

if (!function_exists('mkd_core_activation')) {
	/**
	 * Triggers when plugin is activated. It calls flush_rewrite_rules
	 * and defines servicemaster_mikado_core_on_activate action
	 */
	function mkd_core_activation() {
		do_action('servicemaster_mikado_core_on_activate');

		MikadoCore\CPT\PostTypesRegister::getInstance()->register();
		flush_rewrite_rules();
	}

	register_activation_hook(__FILE__, 'mkd_core_activation');
}

if (!function_exists('mkd_core_text_domain')) {
	/**
	 * Loads plugin text domain so it can be used in translation
	 */
	function mkd_core_text_domain() {
		load_plugin_textdomain('mikado-core', false, MIKADO_CORE_REL_PATH . '/languages');
	}

	add_action('plugins_loaded', 'mkd_core_text_domain');
}

if (!function_exists('mkd_core_themename_theme_menu')) {
	/**
	 * Function that generates admin menu for options page.
	 * It generates one admin page per options page.
	 */
	function mkd_core_themename_theme_menu() {
		if (mkd_core_theme_installed()) {

			global $servicemaster_Framework;
			servicemaster_mikado_init_theme_options();

			$page_hook_suffix = add_menu_page(
				'Mikado Options',                   // The value used to populate the browser's title bar when the menu page is active
				'Mikado Options',                   // The text of the menu in the administrator's sidebar
				'administrator',                  // What roles are able to access the menu
				'servicemaster_mikado_theme_menu',                // The ID used to bind submenu items to this menu
				array($servicemaster_Framework->getSkin(), 'renderOptions'), // The callback function used to render this menu
				$servicemaster_Framework->getSkin()->getMenuIcon('options'),             // Icon For menu Item
				$servicemaster_Framework->getSkin()->getMenuItemPosition('options')            // Position
			);

			foreach ($servicemaster_Framework->mkdOptions->adminPages as $key => $value) {
				$slug = "";

				if (!empty($value->slug)) {
					$slug = "_tab" . $value->slug;
				}

				$subpage_hook_suffix = add_submenu_page(
					'servicemaster_mikado_theme_menu',
					'Mikado Options - ' . $value->title,                   // The value used to populate the browser's title bar when the menu page is active
					$value->title,                   // The text of the menu in the administrator's sidebar
					'administrator',                  // What roles are able to access the menu
					'servicemaster_mikado_theme_menu' . $slug,                // The ID used to bind submenu items to this menu
					array($servicemaster_Framework->getSkin(), 'renderOptions')
				);

				add_action('admin_print_scripts-' . $subpage_hook_suffix, 'servicemaster_mikado_enqueue_admin_scripts');
				add_action('admin_print_styles-' . $subpage_hook_suffix, 'servicemaster_mikado_enqueue_admin_styles');
			};

			add_action('admin_print_scripts-' . $page_hook_suffix, 'servicemaster_mikado_enqueue_admin_scripts');
			add_action('admin_print_styles-' . $page_hook_suffix, 'servicemaster_mikado_enqueue_admin_styles');

		}
	}

	add_action('admin_menu', 'mkd_core_themename_theme_menu');
}

if (!function_exists('mkd_shortcodes_in_widgets')) {
	/**
	 * Function that enables shortcodes rendering in widgets
	 */
	function mkd_shortcodes_in_widgets() {
		//enable rendering shortcodes in widgets
		add_filter('widget_text', 'do_shortcode');
	}

	add_action('after_setup_theme', 'mkd_shortcodes_in_widgets');
}