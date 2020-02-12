<?php

if(!function_exists('servicemaster_mikado_load_elements_map')) {
	/**
	 * Add Elements option page for shortcodes
	 */
	function servicemaster_mikado_load_elements_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_elements_page',
				'title' => esc_html__('Elements','servicemaster'),
				'icon'  => 'icon_star_alt'
			)
		);

		do_action('servicemaster_mikado_options_elements_map');

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_load_elements_map');

}