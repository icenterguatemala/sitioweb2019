<?php

if (!function_exists('servicemaster_mikado_footer_meta_box_map')) {
	function servicemaster_mikado_footer_meta_box_map() {

		$mkd_custom_widgets = servicemaster_mikado_get_custom_sidebars();
		$footer_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('page', 'portfolio-item', 'post'),
				'title' => esc_html__('Footer', 'servicemaster'),
				'name'  => 'footer_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_enable_footer_image_meta',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Disable Footer Image for this Page', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will hide footer image on this page', 'servicemaster'),
				'parent'        => $footer_meta_box,
				'args'          => array(
					'dependence'             => true,
					'dependence_hide_on_yes' => '#mkd_mkd_footer_background_image_meta',
					'dependence_show_on_yes' => ''
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'            => 'mkd_footer_background_image_meta',
				'type'            => 'image',
				'label'           => esc_html__('Background Image', 'servicemaster'),
				'description'     => esc_html__('Choose Background Image for Footer Area on this page', 'servicemaster'),
				'parent'          => $footer_meta_box,
				'hidden_property' => 'mkd_enable_footer_image_meta',
				'hidden_value'    => 'yes'
			)
		);
		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_footer_background_color_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose Background Color for Footer Area on this page', 'servicemaster'),
				'parent'      => $footer_meta_box
			)
		);
		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_footer_background_color_transparency_meta',
				'type'        => 'text',
				'label'       => esc_html__('Background Color Transparency', 'servicemaster'),
				'description' => esc_html__('Choose Background Color Transparency(0-1) for Footer Area on this page', 'servicemaster'),
				'parent'      => $footer_meta_box,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_disable_footer_meta',
				'default_value' => '',
				'label'         => esc_html__('Disable Footer for this Page', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will hide footer on this page', 'servicemaster'),
				'options'       => array(
					''    => esc_html__('Default', 'servicemaster'),
					'yes' => 'Yes',
					'no'  => 'No'
				),
				'parent'        => $footer_meta_box,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_uncovering_footer_meta',
				'default_value' => '',
				'label'         => esc_html__('Uncovering Footer', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will make Footer gradually appear on scroll', 'servicemaster'),
				'options'       => array(
					''    => esc_html__('Default', 'servicemaster'),
					'yes' => 'Yes',
					'no'  => 'No'
				),
				'parent'        => $footer_meta_box,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_footer_in_grid_meta',
				'default_value' => '',
				'label'         => esc_html__('Footer in Grid', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will place Footer content in grid', 'servicemaster'),
				'options'       => array(
					''    => esc_html__('Default', 'servicemaster'),
					'yes' => 'Yes',
					'no'  => 'No'
				),
				'parent'        => $footer_meta_box,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_show_footer_top_meta',
				'default_value' => '',
				'label'         => esc_html__('Show Footer Top', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will show Footer Top area', 'servicemaster'),
				'options'       => array(
					''    => esc_html__('Default', 'servicemaster'),
					'yes' => 'Yes',
					'no'  => 'No'
				),
				'args'          => array(
					"dependence" => true,
					"hide"       => array(
						""    => "",
						"no"  => "#mkd_mkd_show_footer_top_container_meta",
						"yes" => ""
					),
					"show"       => array(
						""    => "#mkd_mkd_show_footer_top_container_meta",
						"no"  => "",
						"yes" => "#mkd_mkd_show_footer_top_container_meta"
					)
				),
				'parent'        => $footer_meta_box,
			)
		);

		$show_footer_top_container = servicemaster_mikado_add_admin_container(
			array(
				'name'            => 'mkd_show_footer_top_container_meta',
				'hidden_property' => 'mkd_show_footer_top_meta',
				'hidden_value'    => 'no',
				'parent'          => $footer_meta_box
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_footer_top_columns_meta',
				'default_value' => '',
				'label'         => esc_html__('Footer Top Columns', 'servicemaster'),
				'description'   => esc_html__('Choose number of columns for Footer Top area', 'servicemaster'),
				'options'       => array(
					''  => esc_html__('Default', 'servicemaster'),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'5' => '3(25%+25%+50%)',
					'6' => '3(50%+25%+25%)',
					'4' => '4'
				),
				'parent'        => $show_footer_top_container,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_footer_top_columns_alignment_meta',
				'default_value' => '',
				'label'         => esc_html__('Footer Top Columns Alignment', 'servicemaster'),
				'description'   => esc_html__('Text Alignment in Footer Columns', 'servicemaster'),
				'options'       => array(
					''       => esc_html__('Default', 'servicemaster'),
					'left'   => esc_html__('Left', 'servicemaster'),
					'center' => esc_html__('Center', 'servicemaster'),
					'right'  => esc_html__('Right', 'servicemaster')
				),
				'parent'        => $show_footer_top_container
			)
		);


		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_show_footer_bottom_meta',
				'default_value' => '',
				'label'         => esc_html__('Show Footer Bottom', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will show Footer Bottom area', 'servicemaster'),
				'options'       => array(
					''    => esc_html__('Default', 'servicemaster'),
					'yes' => 'Yes',
					'no'  => 'No'
				),
				'args'          => array(
					"dependence" => true,
					"hide"       => array(
						""    => "",
						"no"  => "#mkd_mkd_show_footer_bottom_container_meta",
						"yes" => ""
					),
					"show"       => array(
						""    => "#mkd_mkd_show_footer_bottom_container_meta",
						"no"  => "",
						"yes" => "#mkd_mkd_show_footer_bottom_container_meta"
					)
				),
				'parent'        => $footer_meta_box,
			)
		);

		$show_footer_bottom_container = servicemaster_mikado_add_admin_container(
			array(
				'name'            => 'mkd_show_footer_bottom_container_meta',
				'hidden_property' => 'mkd_show_footer_bottom_meta',
				'hidden_value'    => 'no',
				'parent'          => $footer_meta_box
			)
		);


		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_footer_bottom_columns_meta',
				'default_value' => '',
				'label'         => esc_html__('Footer Bottom Columns', 'servicemaster'),
				'description'   => esc_html__('Choose number of columns for Footer Bottom area', 'servicemaster'),
				'options'       => array(
					''  => esc_html__('Default', 'servicemaster'),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '3 (25%+50%+25%)',
				),
				'parent'        => $show_footer_bottom_container,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'select',
				'name'          => 'mkd_footer_bottom_border_meta',
				'default_value' => '',
				'label'         => esc_html__('Border Top', 'servicemaster'),
				'description'   => esc_html__('Enable Border Top', 'servicemaster'),
				'options'       => array(
					''  => esc_html__('Default', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster'),
					'no'  => esc_html__('No', 'servicemaster')
				),
				'parent'        => $show_footer_bottom_container,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'type'          => 'yesno',
				'name'          => 'show_footer_custom_widget_areas',
				'default_value' => 'no',
				'label'         => esc_html__('Use Custom Widget Areas in Footer', 'servicemaster'),
				'description'   => '',
				'args'          => array(
					'dependence'             => true,
					'dependence_hide_on_yes' => '',
					'dependence_show_on_yes' => '#mkd_footer_custom_widget_areas'
				),
				'parent'        => $footer_meta_box,
			)
		);

		$show_footer_custom_widget_areas = servicemaster_mikado_add_admin_container(
			array(
				'name'            => 'footer_custom_widget_areas',
				'hidden_property' => 'show_footer_custom_widget_areas',
				'hidden_value'    => 'no',
				'parent'          => $footer_meta_box
			)
		);

		$top_cols_num = 4;

		for ($i = 1; $i <= $top_cols_num; $i++) {

			servicemaster_mikado_add_meta_box_field(array(
				'name'        => 'mkd_footer_top_meta_' . $i,
				'type'        => 'selectblank',
				'label'       => esc_html__('Choose Widget Area in Footer Top Column ', 'servicemaster') . $i,
				'description' => esc_html__('Choose Custom Widget area to display in Footer Top Column ', 'servicemaster') . $i,
				'parent'      => $show_footer_custom_widget_areas,
				'options'     => $mkd_custom_widgets
			));

		}

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_footer_text_meta',
			'type'        => 'selectblank',
			'label'       => esc_html__('Choose Widget Area in Footer Bottom', 'servicemaster'),
			'description' => esc_html__('Choose Custom Widget area to display in Footer Bottom', 'servicemaster'),
			'parent'      => $show_footer_custom_widget_areas,
			'options'     => $mkd_custom_widgets
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_footer_bottom_left_meta',
			'type'        => 'selectblank',
			'label'       => esc_html__('Choose Widget Area in Footer Bottom Left', 'servicemaster'),
			'description' => esc_html__('Choose Custom Widget area to display in Footer Bottom', 'servicemaster'),
			'parent'      => $show_footer_custom_widget_areas,
			'options'     => $mkd_custom_widgets
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_footer_bottom_right_meta',
			'type'        => 'selectblank',
			'label'       => esc_html__('Choose Widget Area in Footer Bottom Right', 'servicemaster'),
			'description' => esc_html__('Choose Custom Widget area to display in Footer Right', 'servicemaster'),
			'parent'      => $show_footer_custom_widget_areas,
			'options'     => $mkd_custom_widgets
		));
	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_footer_meta_box_map');
}