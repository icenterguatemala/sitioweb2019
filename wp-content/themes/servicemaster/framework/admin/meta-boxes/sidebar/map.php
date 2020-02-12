<?php

if (!function_exists('servicemaster_mikado_sidebar_meta_box_map')) {
	function servicemaster_mikado_sidebar_meta_box_map() {

		$mkd_custom_sidebars = servicemaster_mikado_get_custom_sidebars();

		$mkd_sidebar_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('page', 'portfolio-item', 'post'),
				'title' => esc_html__('Sidebar', 'servicemaster'),
				'name'  => 'sidebar_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_sidebar_meta',
				'type'        => 'select',
				'label'       => esc_html__('Layout', 'servicemaster'),
				'description' => esc_html__('Choose the sidebar layout', 'servicemaster'),
				'parent'      => $mkd_sidebar_meta_box,
				'options'     => array(
					''                 => esc_html__('Default', 'servicemaster'),
					'no-sidebar'       => esc_html__('No Sidebar', 'servicemaster'),
					'sidebar-33-right' => esc_html__('Sidebar 1/3 Right', 'servicemaster'),
					'sidebar-25-right' => esc_html__('Sidebar 1/4 Right', 'servicemaster'),
					'sidebar-33-left'  => esc_html__('Sidebar 1/3 Left', 'servicemaster'),
					'sidebar-25-left'  => esc_html__('Sidebar 1/4 Left', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'    => 'mkd_boxed_widgets_meta',
				'type'    => 'selectblank',
				'label'   => esc_html__('Boxed Widgets', 'servicemaster'),
				'parent'  => $mkd_sidebar_meta_box,
				'options' => array(
					'yes' => esc_html__('Yes', 'servicemaster'),
					'no'  => esc_html__('No', 'servicemaster')
				)
			)
		);

		if (count($mkd_custom_sidebars) > 0) {
			servicemaster_mikado_add_meta_box_field(array(
				'name'        => 'mkd_custom_sidebar_meta',
				'type'        => 'selectblank',
				'label'       => esc_html__('Choose Widget Area in Sidebar', 'servicemaster'),
				'description' => esc_html__('Choose Custom Widget area to display in Sidebar', 'servicemaster'),
				'parent'      => $mkd_sidebar_meta_box,
				'options'     => $mkd_custom_sidebars
			));
		}

	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_sidebar_meta_box_map');
}