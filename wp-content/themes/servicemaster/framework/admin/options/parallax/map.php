<?php

if(!function_exists('servicemaster_mikado_parallax_options_map')) {
	/**
	 * Parallax options page
	 */
	function servicemaster_mikado_parallax_options_map() {

		$panel_parallax = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_elements_page',
				'name'  => 'panel_parallax',
				'title' => esc_html__('Parallax','servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'onoff',
			'name'          => 'parallax_on_off',
			'default_value' => 'off',
			'label'         => esc_html__('Parallax on touch devices','servicemaster'),
			'description'   => esc_html__('Enabling this option will allow parallax on touch devices','servicemaster'),
			'parent'        => $panel_parallax
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'text',
			'name'          => 'parallax_min_height',
			'default_value' => '400',
			'label'         => esc_html__('Parallax Min Height', 'servicemaster'),
			'description'   => esc_html__('Set a minimum height for parallax images on small displays (phones, tablets, etc.)', 'servicemaster'),
			'args'          => array(
				'col_width' => 3,
				'suffix'    => 'px'
			),
			'parent'        => $panel_parallax
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_parallax_options_map');

}