<?php

if (!function_exists('servicemaster_mikado_title_options_map')) {

	function servicemaster_mikado_title_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_title_page',
				'title' => esc_html__('Title', 'servicemaster'),
				'icon'  => 'icon_archive_alt'
			)
		);

		$panel_title = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_title_page',
				'name'  => 'panel_title',
				'title' => esc_html__('Title Settings', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'show_title_area',
				'type'          => 'yesno',
				'default_value' => 'yes',
				'label'         => esc_html__('Show Title Area', 'servicemaster'),
				'description'   => esc_html__('This option will enable/disable Title Area', 'servicemaster'),
				'parent'        => $panel_title,
				'args'          => array(
					"dependence"             => true,
					"dependence_hide_on_yes" => "",
					"dependence_show_on_yes" => "#mkd_show_title_area_container"
				)
			)
		);

		$show_title_area_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $panel_title,
				'name'            => 'show_title_area_container',
				'hidden_property' => 'show_title_area',
				'hidden_value'    => 'no'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_type',
				'type'          => 'select',
				'default_value' => 'standard',
				'label'         => esc_html__('Title Area Type', 'servicemaster'),
				'description'   => esc_html__('Choose title type', 'servicemaster'),
				'parent'        => $show_title_area_container,
				'options'       => array(
					'standard'   => esc_html__('Standard', 'servicemaster'),
					'breadcrumb' => esc_html__('Breadcrumb','servicemaster'),
				),
				'args'          => array(
			"dependence" => true,
			"hide"       => array(
				"standard"   => "",
				"breadcrumb" => "#mkd_title_area_type_container"
			),
			"show"       => array(
				"standard"   => "#mkd_title_area_type_container",
				"breadcrumb" => ""
			)
		)
			)
		);

		$title_area_type_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $show_title_area_container,
				'name'            => 'title_area_type_container',
				'hidden_property' => 'title_area_type',
				'hidden_value'    => '',
				'hidden_values'   => array('breadcrumb'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_enable_breadcrumbs',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Enable Breadcrumbs', 'servicemaster'),
				'description'   => esc_html__('This option will display Breadcrumbs in Title Area', 'servicemaster'),
				'parent'        => $title_area_type_container,
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_in_grid',
				'type'          => 'yesno',
				'default_value' => 'yes',
				'label'         => esc_html__('Title in Grid', 'servicemaster'),
				'description'   => esc_html__('Set title content to be in grid', 'servicemaster'),
				'parent'        => $show_title_area_container
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_animation',
				'type'          => 'select',
				'default_value' => 'no',
				'label'         => esc_html__('Animations', 'servicemaster'),
				'description'   => esc_html__('Choose an animation for Title Area', 'servicemaster'),
				'parent'        => $show_title_area_container,
				'options'       => array(
					'no'         => esc_html__('No Animation', 'servicemaster'),
					'right-left' => esc_html__('Text right to left', 'servicemaster'),
					'left-right' => esc_html__('Text left to right', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_vertial_alignment',
				'type'          => 'select',
				'default_value' => 'header_bottom',
				'label'         => esc_html__('Vertical Alignment', 'servicemaster'),
				'description'   => esc_html__('Specify title vertical alignment', 'servicemaster'),
				'parent'        => $show_title_area_container,
				'options'       => array(
					'header_bottom' => esc_html__('From Bottom of Header', 'servicemaster'),
					'window_top'    => esc_html__('From Window Top', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_content_alignment',
				'type'          => 'select',
				'default_value' => 'left',
				'label'         => esc_html__('Horizontal Alignment', 'servicemaster'),
				'description'   => esc_html__('Specify title horizontal alignment', 'servicemaster'),
				'parent'        => $show_title_area_container,
				'options'       => array(
					'left'   => esc_html__('Left', 'servicemaster'),
					'center' => esc_html__('Center', 'servicemaster'),
					'right'  => esc_html__('Right', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'        => 'title_area_background_color',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for Title Area', 'servicemaster'),
				'parent'      => $show_title_area_container
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'        => 'title_area_background_image',
				'type'        => 'image',
				'label'       => esc_html__('Background Image', 'servicemaster'),
				'description' => esc_html__('Choose an Image for Title Area', 'servicemaster'),
				'parent'      => $show_title_area_container
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_background_image_responsive',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Background Responsive Image', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will make Title background image responsive', 'servicemaster'),
				'parent'        => $show_title_area_container,
				'args'          => array(
					"dependence"             => true,
					"dependence_hide_on_yes" => "#mkd_title_area_background_image_responsive_container",
					"dependence_show_on_yes" => ""
				)
			)
		);

		$title_area_background_image_responsive_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $show_title_area_container,
				'name'            => 'title_area_background_image_responsive_container',
				'hidden_property' => 'title_area_background_image_responsive',
				'hidden_value'    => 'yes'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'name'          => 'title_area_background_image_parallax',
				'type'          => 'select',
				'default_value' => 'no',
				'label'         => esc_html__('Background Image in Parallax', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will make Title background image parallax', 'servicemaster'),
				'parent'        => $title_area_background_image_responsive_container,
				'options'       => array(
					'no'       => esc_html__('No', 'servicemaster'),
					'yes'      => esc_html__('Yes', 'servicemaster'),
					'yes_zoom' => esc_html__('Yes, with zoom out', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'name'        => 'title_area_height',
			'type'        => 'text',
			'label'       => esc_html__('Height', 'servicemaster'),
			'description' => esc_html__('Set a height for Title Area', 'servicemaster'),
			'parent'      => $title_area_background_image_responsive_container,
			'args'        => array(
				'col_width' => 2,
				'suffix'    => 'px'
			)
		));


		$panel_typography = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_title_page',
				'name'  => 'panel_title_typography',
				'title' => esc_html__('Typography', 'servicemaster'),
			)
		);

		$group_page_title_styles = servicemaster_mikado_add_admin_group(array(
			'name'        => 'group_page_title_styles',
			'title'       => esc_html__('Title', 'servicemaster'),
			'description' => esc_html__('Define styles for page title', 'servicemaster'),
			'parent'      => $panel_typography
		));

		$row_page_title_styles_1 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_title_styles_1',
			'parent' => $group_page_title_styles
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'colorsimple',
			'name'          => 'page_title_color',
			'default_value' => '',
			'label'         => esc_html__('Text Color', 'servicemaster'),
			'parent'        => $row_page_title_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_title_fontsize',
			'default_value' => '',
			'label'         => esc_html__('Font Size', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_title_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_title_lineheight',
			'default_value' => '',
			'label'         => esc_html__('Line Height', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_title_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_title_texttransform',
			'default_value' => '',
			'label'         => esc_html__('Text Transform', 'servicemaster'),
			'options'       => servicemaster_mikado_get_text_transform_array(),
			'parent'        => $row_page_title_styles_1
		));

		$row_page_title_styles_2 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_title_styles_2',
			'parent' => $group_page_title_styles,
			'next'   => true
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'fontsimple',
			'name'          => 'page_title_google_fonts',
			'default_value' => '-1',
			'label'         => esc_html__('Font Family', 'servicemaster'),
			'parent'        => $row_page_title_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_title_fontstyle',
			'default_value' => '',
			'label'         => esc_html__('Font Style', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_style_array(),
			'parent'        => $row_page_title_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_title_fontweight',
			'default_value' => '',
			'label'         => esc_html__('Font Weight', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_weight_array(),
			'parent'        => $row_page_title_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_title_letter_spacing',
			'default_value' => '',
			'label'         => esc_html__('Letter Spacing', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_title_styles_2
		));

		$group_page_subtitle_styles = servicemaster_mikado_add_admin_group(array(
			'name'        => 'group_page_subtitle_styles',
			'title'       => esc_html__('Subtitle', 'servicemaster'),
			'description' => esc_html__('Define styles for page subtitle', 'servicemaster'),
			'parent'      => $panel_typography
		));

		$row_page_subtitle_styles_1 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_subtitle_styles_1',
			'parent' => $group_page_subtitle_styles
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'colorsimple',
			'name'          => 'page_subtitle_color',
			'default_value' => '',
			'label'         => esc_html__('Text Color', 'servicemaster'),
			'parent'        => $row_page_subtitle_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_subtitle_fontsize',
			'default_value' => '',
			'label'         => esc_html__('Font Size', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_subtitle_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_subtitle_lineheight',
			'default_value' => '',
			'label'         => esc_html__('Line Height', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_subtitle_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_subtitle_texttransform',
			'default_value' => '',
			'label'         => esc_html__('Text Transform', 'servicemaster'),
			'options'       => servicemaster_mikado_get_text_transform_array(),
			'parent'        => $row_page_subtitle_styles_1
		));

		$row_page_subtitle_styles_2 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_subtitle_styles_2',
			'parent' => $group_page_subtitle_styles,
			'next'   => true
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'fontsimple',
			'name'          => 'page_subtitle_google_fonts',
			'default_value' => '-1',
			'label'         => esc_html__('Font Family', 'servicemaster'),
			'parent'        => $row_page_subtitle_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_subtitle_fontstyle',
			'default_value' => '',
			'label'         => esc_html__('Font Style', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_style_array(),
			'parent'        => $row_page_subtitle_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_subtitle_fontweight',
			'default_value' => '',
			'label'         => esc_html__('Font Weight', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_weight_array(),
			'parent'        => $row_page_subtitle_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_subtitle_letter_spacing',
			'default_value' => '',
			'label'         => esc_html__('Letter Spacing', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_subtitle_styles_2
		));

		$group_page_breadcrumbs_styles = servicemaster_mikado_add_admin_group(array(
			'name'        => 'group_page_breadcrumbs_styles',
			'title'       => esc_html__('Breadcrumbs', 'servicemaster'),
			'description' => esc_html__('Define styles for page breadcrumbs', 'servicemaster'),
			'parent'      => $panel_typography
		));

		$row_page_breadcrumbs_styles_1 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_breadcrumbs_styles_1',
			'parent' => $group_page_breadcrumbs_styles
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'colorsimple',
			'name'          => 'page_breadcrumb_color',
			'default_value' => '',
			'label'         => esc_html__('Text Color', 'servicemaster'),
			'parent'        => $row_page_breadcrumbs_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_breadcrumb_fontsize',
			'default_value' => '',
			'label'         => esc_html__('Font Size', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_breadcrumbs_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_breadcrumb_lineheight',
			'default_value' => '',
			'label'         => esc_html__('Line Height', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_breadcrumbs_styles_1
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_breadcrumb_texttransform',
			'default_value' => '',
			'label'         => esc_html__('Text Transform', 'servicemaster'),
			'options'       => servicemaster_mikado_get_text_transform_array(),
			'parent'        => $row_page_breadcrumbs_styles_1
		));

		$row_page_breadcrumbs_styles_2 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_breadcrumbs_styles_2',
			'parent' => $group_page_breadcrumbs_styles,
			'next'   => true
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'fontsimple',
			'name'          => 'page_breadcrumb_google_fonts',
			'default_value' => '-1',
			'label'         => esc_html__('Font Family', 'servicemaster'),
			'parent'        => $row_page_breadcrumbs_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_breadcrumb_fontstyle',
			'default_value' => '',
			'label'         => esc_html__('Font Style', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_style_array(),
			'parent'        => $row_page_breadcrumbs_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'selectblanksimple',
			'name'          => 'page_breadcrumb_fontweight',
			'default_value' => '',
			'label'         => esc_html__('Font Weight', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_weight_array(),
			'parent'        => $row_page_breadcrumbs_styles_2
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'textsimple',
			'name'          => 'page_breadcrumb_letter_spacing',
			'default_value' => '',
			'label'         => esc_html__('Letter Spacing', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			),
			'parent'        => $row_page_breadcrumbs_styles_2
		));

		$row_page_breadcrumbs_styles_3 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_page_breadcrumbs_styles_3',
			'parent' => $group_page_breadcrumbs_styles,
			'next'   => true
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'colorsimple',
			'name'          => 'page_breadcrumb_hovercolor',
			'default_value' => '',
			'label'         => esc_html__('Hover/Active Color', 'servicemaster'),
			'parent'        => $row_page_breadcrumbs_styles_3
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_title_options_map', 7);

}