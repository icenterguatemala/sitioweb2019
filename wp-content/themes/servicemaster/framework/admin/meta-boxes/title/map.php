<?php

if (!function_exists('servicemaster_mikado_title_meta_box_map')) {
	function servicemaster_mikado_title_meta_box_map() {

		$title_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('page', 'portfolio-item', 'post'),
				'title' => esc_html__('Title', 'servicemaster'),
				'name'  => 'title_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_show_title_area_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Show Title Area', 'servicemaster'),
				'description'   => esc_html__('Disabling this option will turn off page title area', 'servicemaster'),
				'parent'        => $title_meta_box,
				'options'       => array(
					''    => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__( 'Yes', 'servicemaster')
				),
				'args'          => array(
					"dependence" => true,
					"hide"       => array(
						""    => "",
						"no"  => "#mkd_mkd_show_title_area_meta_container",
						"yes" => ""
					),
					"show"       => array(
						""    => "#mkd_mkd_show_title_area_meta_container",
						"no"  => "",
						"yes" => "#mkd_mkd_show_title_area_meta_container"
					)
				)
			)
		);

		$show_title_area_meta_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $title_meta_box,
				'name'            => 'mkd_show_title_area_meta_container',
				'hidden_property' => 'mkd_show_title_area_meta',
				'hidden_value'    => 'no'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_type_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Title Area Type', 'servicemaster'),
				'description'   => esc_html__('Choose title type', 'servicemaster'),
				'parent'        => $show_title_area_meta_container,
				'options'       => array(
					''           => '',
					'standard'   => esc_html__('Standard', 'servicemaster'),
					'breadcrumb' => esc_html__('Breadcrumb', 'servicemaster'),
				),
				'args'          => array(
					"dependence" => true,
					"hide"       => array(
						"standard"   => "",
						"standard"   => "",
						"breadcrumb" => "#mkd_mkd_title_area_type_meta_container"
					),
					"show"       => array(
						""           => "#mkd_mkd_title_area_type_meta_container",
						"standard"   => "#mkd_mkd_title_area_type_meta_container",
						"breadcrumb" => ""
					)
				)
			)
		);

		$title_area_type_meta_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $show_title_area_meta_container,
				'name'            => 'mkd_title_area_type_meta_container',
				'hidden_property' => 'mkd_title_area_type_meta',
				'hidden_value'    => '',
				'hidden_values'   => array('breadcrumb'),
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_enable_breadcrumbs_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Enable Breadcrumbs', 'servicemaster'),
				'description'   => esc_html__('This option will display Breadcrumbs in Title Area', 'servicemaster'),
				'parent'        => $title_area_type_meta_container,
				'options'       => array(
					''    => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster'),
				),
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_in_grid_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Title in Grid', 'servicemaster'),
				'description'   => esc_html__('Set title content to be in grid', 'servicemaster'),
				'parent'        => $show_title_area_meta_container,
				'options'       => array(
					''           => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_animation_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Animations', 'servicemaster'),
				'description'   => esc_html__('Choose an animation for Title Area', 'servicemaster'),
				'parent'        => $show_title_area_meta_container,
				'options'       => array(
					''           => '',
					'no'         => esc_html__('No Animation', 'servicemaster'),
					'right-left' => esc_html__('Text right to left', 'servicemaster'),
					'left-right' => esc_html__('Text left to right', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_vertial_alignment_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Vertical Alignment', 'servicemaster'),
				'description'   => esc_html__('Specify title vertical alignment', 'servicemaster'),
				'parent'        => $show_title_area_meta_container,
				'options'       => array(
					''              => '',
					'header_bottom' => esc_html__('From Bottom of Header', 'servicemaster'),
					'window_top'    => esc_html__('From Window Top', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_content_alignment_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Horizontal Alignment', 'servicemaster'),
				'description'   => esc_html__('Specify title horizontal alignment', 'servicemaster'),
				'parent'        => $show_title_area_meta_container,
				'options'       => array(
					''       => '',
					'left'   => esc_html__('Left', 'servicemaster'),
					'center' => esc_html__('Center', 'servicemaster'),
					'right'  => esc_html__('Right', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_title_text_color_meta',
				'type'        => 'color',
				'label'       => esc_html__('Title Color', 'servicemaster'),
				'description' => esc_html__('Choose a color for title text', 'servicemaster'),
				'parent'      => $show_title_area_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_title_breadcrumb_color_meta',
				'type'        => 'color',
				'label'       => esc_html__('Breadcrumb Color', 'servicemaster'),
				'description' => esc_html__('Choose a color for breadcrumb text', 'servicemaster'),
				'parent'      => $show_title_area_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_title_area_background_color_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for Title Area', 'servicemaster'),
				'parent'      => $show_title_area_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_hide_background_image_meta',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Hide Background Image', 'servicemaster'),
				'description'   => esc_html__('Enable this option to hide background image in Title Area', 'servicemaster'),
				'parent'        => $show_title_area_meta_container,
				'args'          => array(
					"dependence"             => true,
					"dependence_hide_on_yes" => "#mkd_mkd_hide_background_image_meta_container",
					"dependence_show_on_yes" => ""
				)
			)
		);

		$hide_background_image_meta_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $show_title_area_meta_container,
				'name'            => 'mkd_hide_background_image_meta_container',
				'hidden_property' => 'mkd_hide_background_image_meta',
				'hidden_value'    => 'yes'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_title_area_background_image_meta',
				'type'        => 'image',
				'label'       => esc_html__('Background Image', 'servicemaster'),
				'description' => esc_html__('Choose an Image for Title Area', 'servicemaster'),
				'parent'      => $hide_background_image_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_background_image_responsive_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Background Responsive Image', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will make Title background image responsive', 'servicemaster'),
				'parent'        => $hide_background_image_meta_container,
				'options'       => array(
					''    => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster')
				),
				'args'          => array(
					"dependence" => true,
					"hide"       => array(
						""    => "",
						"no"  => "",
						"yes" => "#mkd_mkd_title_area_background_image_responsive_meta_container, #mkd_mkd_title_area_height_meta"
					),
					"show"       => array(
						""    => "#mkd_mkd_title_area_background_image_responsive_meta_container, #mkd_mkd_title_area_height_meta",
						"no"  => "#mkd_mkd_title_area_background_image_responsive_meta_container, #mkd_mkd_title_area_height_meta",
						"yes" => ""
					)
				)
			)
		);

		$title_area_background_image_responsive_meta_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $hide_background_image_meta_container,
				'name'            => 'mkd_title_area_background_image_responsive_meta_container',
				'hidden_property' => 'mkd_title_area_background_image_responsive_meta',
				'hidden_value'    => 'yes'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_title_area_background_image_parallax_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Background Image in Parallax', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will make Title background image parallax', 'servicemaster'),
				'parent'        => $title_area_background_image_responsive_meta_container,
				'options'       => array(
					''         => '',
					'no'       => esc_html__('No', 'servicemaster'),
					'yes'      => esc_html__('Yes', 'servicemaster'),
					'yes_zoom' => esc_html__('Yes, with zoom out', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_title_area_height_meta',
			'type'        => 'text',
			'label'       => esc_html__('Height', 'servicemaster'),
			'description' => esc_html__('Set a height for Title Area', 'servicemaster'),
			'parent'      => $show_title_area_meta_container,
			'args'        => array(
				'col_width' => 2,
				'suffix'    => 'px'
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_disable_title_bottom_border_meta',
			'type'          => 'yesno',
			'label'         => esc_html__('Disable Title Bottom Border', 'servicemaster'),
			'description'   => esc_html__('This option will disable title area bottom border', 'servicemaster'),
			'parent'        => $show_title_area_meta_container,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_title_area_subtitle_meta',
			'type'          => 'text',
			'default_value' => '',
			'label'         => esc_html__('Subtitle Text', 'servicemaster'),
			'description'   => esc_html__('Enter your subtitle text', 'servicemaster'),
			'parent'        => $show_title_area_meta_container,
			'args'          => array(
				'col_width' => 6
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_subtitle_color_meta',
				'type'        => 'color',
				'label'       => esc_html__('Subtitle Color', 'servicemaster'),
				'description' => esc_html__('Choose a color for subtitle text', 'servicemaster'),
				'parent'      => $show_title_area_meta_container
			)
		);

	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_title_meta_box_map');
}