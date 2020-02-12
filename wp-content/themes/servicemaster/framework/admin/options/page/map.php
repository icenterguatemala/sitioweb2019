<?php

if(!function_exists('servicemaster_mikado_page_options_map')) {

	function servicemaster_mikado_page_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_page_page',
				'title' => esc_html__('Page','servicemaster'),
				'icon'  => 'icon_document_alt'
			)
		);

		$custom_sidebars = servicemaster_mikado_get_custom_sidebars();

		$panel_sidebar = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_page_page',
				'name'  => 'panel_sidebar',
				'title' => esc_html__('Design Style','servicemaster')
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'page_sidebar_layout',
			'type'          => 'select',
			'label'         => esc_html__('Sidebar Layout','servicemaster'),
			'description'   => esc_html__('Choose a sidebar layout for pages','servicemaster'),
			'default_value' => 'default',
			'parent'        => $panel_sidebar,
			'options'       => array(
				'default'          => esc_html__('No Sidebar','servicemaster'),
				'sidebar-33-right' => esc_html__('Sidebar 1/3 Right','servicemaster'),
				'sidebar-25-right' => esc_html__('Sidebar 1/4 Right','servicemaster'),
				'sidebar-33-left'  => esc_html__('Sidebar 1/3 Left','servicemaster'),
				'sidebar-25-left'  => esc_html__('Sidebar 1/4 Left','servicemaster'),
			)
		));


		if(count($custom_sidebars) > 0) {
			servicemaster_mikado_add_admin_field(array(
				'name'        => 'page_custom_sidebar',
				'type'        => 'selectblank',
				'label'       => esc_html__('Sidebar to Display','servicemaster'),
				'description' => esc_html__('Choose a sidebar to display on pages. Default sidebar is "Sidebar"','servicemaster'),
				'parent'      => $panel_sidebar,
				'options'     => $custom_sidebars
			));
		}

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'page_show_comments',
			'type'          => 'yesno',
			'label'         => esc_html__('Show Comments','servicemaster'),
			'description'   => esc_html__('Enabling this option will show comments on your page', 'servicemaster'),
			'default_value' => 'yes',
			'parent'        => $panel_sidebar
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_page_options_map', 9);

}