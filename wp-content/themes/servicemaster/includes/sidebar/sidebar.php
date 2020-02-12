<?php

if (!function_exists('servicemaster_mikado_register_sidebars')) {
	/**
	 * Function that registers theme's sidebars
	 */
	function servicemaster_mikado_register_sidebars() {

		register_sidebar(array(
			'name'          => esc_html__('Sidebar', 'servicemaster'),
			'id'            => 'sidebar',
			'description'   => esc_html__('Default Sidebar', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5><span class="mkd-sidearea-title">',
			'after_title'   => '</span></h5>'
		));

	}

	add_action('widgets_init', 'servicemaster_mikado_register_sidebars');
}

if (!function_exists('servicemaster_mikado_add_support_custom_sidebar')) {
	/**
	 * Function that adds theme support for custom sidebars. It also creates ServiceMasterMikadoSidebar object
	 */
	function servicemaster_mikado_add_support_custom_sidebar() {
		add_theme_support('ServiceMasterMikadoSidebar');
		if (get_theme_support('ServiceMasterMikadoSidebar')) {
			new ServiceMasterMikadoSidebar();
		}
	}

	add_action('after_setup_theme', 'servicemaster_mikado_add_support_custom_sidebar');
}
