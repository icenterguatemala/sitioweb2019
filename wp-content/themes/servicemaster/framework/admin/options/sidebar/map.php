<?php

if(!function_exists('servicemaster_mikado_sidebar_options_map')) {

	function servicemaster_mikado_sidebar_options_map() {

		$panel_widgets = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_page_page',
				'name'  => 'panel_widgets',
				'title' => esc_html__('Widgets', 'servicemaster')
			)
		);

		/**
		 * Navigation style
		 */

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'page_boxed_widgets',
			'type'          => 'yesno',
			'default_value' => 'yes',
			'label'         => esc_html__('Boxed Widgets', 'servicemaster'),
			'parent'        => $panel_widgets
		));

		$group_sidebar_padding = servicemaster_mikado_add_admin_group(array(
			'name'   => 'group_sidebar_padding',
			'title'  => esc_html__('Padding', 'servicemaster'),
			'parent' => $panel_widgets
		));

		$row_sidebar_padding = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_sidebar_padding',
			'parent' => $group_sidebar_padding
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'sidebar_padding_top',
			'default_value' => '',
			'label'         => esc_html__('Top Padding', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_sidebar_padding
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'sidebar_padding_right',
			'default_value' => '',
			'label'         => esc_html__('Right Padding', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_sidebar_padding
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'sidebar_padding_bottom',
			'default_value' => '',
			'label'         => esc_html__('Bottom Padding', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_sidebar_padding
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'sidebar_padding_left',
			'default_value' => '',
			'label'         => esc_html__('Left Padding', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_sidebar_padding
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'select',
			'name'          => 'sidebar_alignment',
			'default_value' => '',
			'label'         => esc_html__('Text Alignment', 'servicemaster'),
			'description'   => esc_html__('Choose text aligment', 'servicemaster'),
			'options'       => array(
				'left'   => esc_html__('Left', 'servicemaster'),
				'center' => esc_html__('Center', 'servicemaster'),
				'right'  => esc_html__('Right', 'servicemaster')
			),
			'parent'        => $panel_widgets
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_sidebar_options_map');

}