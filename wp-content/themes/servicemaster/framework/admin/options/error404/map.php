<?php

if(!function_exists('servicemaster_mikado_error_404_options_map')) {

	function servicemaster_mikado_error_404_options_map() {

		servicemaster_mikado_add_admin_page(array(
			'slug'  => '__404_error_page',
			'title' => esc_html__('404 Error Page','servicemaster'),
			'icon'  => 'icon_info_alt'
		));

		$panel_404_options = servicemaster_mikado_add_admin_panel(array(
			'page'  => '__404_error_page',
			'name'  => 'panel_404_options',
			'title' => esc_html__('404 Page Option','servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $panel_404_options,
			'type'          => 'text',
			'name'          => '404_title',
			'default_value' => '',
			'label'         => esc_html__('Title','servicemaster'),
			'description'   => esc_html__('Enter title for 404 page','servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $panel_404_options,
			'type'          => 'text',
			'name'          => '404_subtitle',
			'default_value' => '',
			'label'         => esc_html__('Subtitle', 'servicemaster'),
			'description'   => esc_html__('Enter subtitle for 404 page', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $panel_404_options,
			'type'          => 'text',
			'name'          => esc_html__('404_back_to_home', 'servicemaster'),
			'default_value' => '',
			'label'         => esc_html__('Back to Home Button Label', 'servicemaster'),
			'description'   => esc_html__('Enter label for "Back to Home" button', 'servicemaster')
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_error_404_options_map', 17);

}