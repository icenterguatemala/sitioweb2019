<?php

if (!function_exists('servicemaster_mikado_sidearea_options_map')) {

	function servicemaster_mikado_sidearea_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_side_area_page',
				'title' => esc_html__('Side Area', 'servicemaster'),
				'icon'  => 'icon_menu'
			)
		);

		$side_area_panel = servicemaster_mikado_add_admin_panel(
			array(
				'title' => esc_html__('Side Area', 'servicemaster'),
				'name'  => 'side_area',
				'page'  => '_side_area_page'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'select',
				'name'          => 'side_area_type',
				'default_value' => 'side-menu-slide-with-content',
				'label'         => esc_html__('Side Area Type', 'servicemaster'),
				'description'   => esc_html__('Choose a type of Side Area', 'servicemaster'),
				'options'       => array(
					'side-menu-slide-from-right'       => esc_html__('Slide from Right Over Content', 'servicemaster'),
					'side-menu-slide-with-content'     => esc_html__('Slide from Right With Content', 'servicemaster'),
					'side-area-uncovered-from-content' => esc_html__('Side Area Uncovered from Content', 'servicemaster'),
				),
				'args'          => array(
					'dependence' => true,
					'hide'       => array(
						'side-menu-slide-from-right'       => '#mkd_side_area_slide_with_content_container',
						'side-menu-slide-with-content'     => '#mkd_side_area_width_container',
						'side-area-uncovered-from-content' => '#mkd_side_area_width_container, #mkd_side_area_slide_with_content_container'
					),
					'show'       => array(
						'side-menu-slide-from-right'       => '#mkd_side_area_width_container',
						'side-menu-slide-with-content'     => '#mkd_side_area_slide_with_content_container',
						'side-area-uncovered-from-content' => ''
					)
				)
			)
		);

		$side_area_width_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $side_area_panel,
				'name'            => 'side_area_width_container',
				'hidden_property' => 'side_area_type',
				'hidden_value'    => '',
				'hidden_values'   => array(
					'side-menu-slide-with-content',
					'side-area-uncovered-from-content'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_width_container,
				'type'          => 'text',
				'name'          => 'side_area_width',
				'default_value' => '',
				'label'         => esc_html__('Side Area Width', 'servicemaster'),
				'description'   => esc_html__('Enter a width for Side Area (in percentages, enter more than 30)', 'servicemaster'),
				'args'          => array(
					'col_width' => 3,
					'suffix'    => '%'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_width_container,
				'type'          => 'color',
				'name'          => 'side_area_content_overlay_color',
				'default_value' => '',
				'label'         => esc_html__('Content Overlay Background Color', 'servicemaster'),
				'description'   => esc_html__('Choose a background color for a content overlay', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_width_container,
				'type'          => 'text',
				'name'          => 'side_area_content_overlay_opacity',
				'default_value' => '',
				'label'         => esc_html__('Content Overlay Background Transparency', 'servicemaster'),
				'description'   => esc_html__('Choose a transparency for the content overlay background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'args'          => array(
					'col_width' => 3
				)
			)
		);

		$side_area_slide_with_content_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $side_area_panel,
				'name'            => 'side_area_slide_with_content_container',
				'hidden_property' => 'side_area_type',
				'hidden_value'    => '',
				'hidden_values'   => array(
					'side-menu-slide-from-right',
					'side-area-uncovered-from-content'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_slide_with_content_container,
				'type'          => 'select',
				'name'          => 'side_area_slide_with_content_width',
				'default_value' => 'width-470',
				'label'         => esc_html__('Side Area Width', 'servicemaster'),
				'description'   => esc_html__('Choose width for Side Area', 'servicemaster'),
				'options'       => array(
					'width-270' => '270px',
					'width-370' => '370px',
					'width-470' => '470px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(array(
				'parent'      => $side_area_panel,
				'type'        => 'image',
				'name'        => 'side_area_bakground_image',
				'label'       => esc_html__('Side Area Background Image', 'servicemaster'),
				'description' => esc_html__('Choose background image for Side Area', 'servicemaster'),
			)
		);

//init icon pack hide and show array. It will be populated dinamically from collections array
		$side_area_icon_pack_hide_array = array();
		$side_area_icon_pack_show_array = array();

//do we have some collection added in collections array?
		if (is_array(servicemaster_mikado_icon_collections()->iconCollections) && count(servicemaster_mikado_icon_collections()->iconCollections)) {
			//get collections params array. It will contain values of 'param' property for each collection
			$side_area_icon_collections_params = servicemaster_mikado_icon_collections()->getIconCollectionsParams();

			//foreach collection generate hide and show array
			foreach (servicemaster_mikado_icon_collections()->iconCollections as $dep_collection_key => $dep_collection_object) {
				$side_area_icon_pack_hide_array[$dep_collection_key] = '';

				//we need to include only current collection in show string as it is the only one that needs to show
				$side_area_icon_pack_show_array[$dep_collection_key] = '#mkd_side_area_icon_' . $dep_collection_object->param . '_container';

				//for all collections param generate hide string
				foreach ($side_area_icon_collections_params as $side_area_icon_collections_param) {
					//we don't need to include current one, because it needs to be shown, not hidden
					if ($side_area_icon_collections_param !== $dep_collection_object->param) {
						$side_area_icon_pack_hide_array[$dep_collection_key] .= '#mkd_side_area_icon_' . $side_area_icon_collections_param . '_container,';
					}
				}

				//remove remaining ',' character
				$side_area_icon_pack_hide_array[$dep_collection_key] = rtrim($side_area_icon_pack_hide_array[$dep_collection_key], ',');
			}

		}

		$side_area_icon_style_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_panel,
				'name'        => 'side_area_icon_style_group',
				'title'       => esc_html__('Side Area Icon Style', 'servicemaster'),
				'description' => esc_html__('Define styles for Side Area icon', 'servicemaster'),
			)
		);

		$side_area_icon_style_row1 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $side_area_icon_style_group,
				'name'   => 'side_area_icon_style_row1'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_icon_style_row1,
				'type'          => 'colorsimple',
				'name'          => 'side_area_icon_color',
				'default_value' => '',
				'label'         => esc_html__('Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_icon_style_row1,
				'type'          => 'colorsimple',
				'name'          => 'side_area_icon_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Hover Color', 'servicemaster'),
			)
		);

		$side_area_icon_style_row2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $side_area_icon_style_group,
				'name'   => 'side_area_icon_style_row2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_icon_style_row2,
				'type'          => 'colorsimple',
				'name'          => 'side_area_light_icon_color',
				'default_value' => '',
				'label'         => esc_html__('Light Menu Icon Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_icon_style_row2,
				'type'          => 'colorsimple',
				'name'          => 'side_area_light_icon_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Light Menu Icon Hover Color', 'servicemaster'),
			)
		);

		$side_area_icon_style_row3 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $side_area_icon_style_group,
				'name'   => 'side_area_icon_style_row3',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_icon_style_row3,
				'type'          => 'colorsimple',
				'name'          => 'side_area_dark_icon_color',
				'default_value' => '',
				'label'         => esc_html__('Dark Menu Icon Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_icon_style_row3,
				'type'          => 'colorsimple',
				'name'          => 'side_area_dark_icon_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Dark Menu Icon Hover Color', 'servicemaster'),
			)
		);

		$icon_spacing_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_panel,
				'name'        => 'icon_spacing_group',
				'title'       => esc_html__('Side Area Icon Spacing', 'servicemaster'),
				'description' => esc_html__('Define padding and margin for side area icon', 'servicemaster'),
			)
		);

		$icon_spacing_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $icon_spacing_group,
				'name'   => 'icon_spancing_row',
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_icon_padding_left',
				'default_value' => '',
				'label'         => esc_html__('Padding Left', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_icon_padding_right',
				'default_value' => '',
				'label'         => esc_html__('Padding Right', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_icon_margin_left',
				'default_value' => '',
				'label'         => esc_html__('Margin Left', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_icon_margin_right',
				'default_value' => '',
				'label'         => esc_html__('Margin Right', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'yesno',
				'name'          => 'side_area_icon_border_yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Icon Border', 'servicemaster'),
				'descritption'  => esc_html__('Enable border around icon', 'servicemaster'),
				'args'          => array(
					'dependence'             => true,
					'dependence_hide_on_yes' => '',
					'dependence_show_on_yes' => '#mkd_side_area_icon_border_container'
				)
			)
		);

		$side_area_icon_border_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $side_area_panel,
				'name'            => 'side_area_icon_border_container',
				'hidden_property' => 'side_area_icon_border_yesno',
				'hidden_value'    => 'no'
			)
		);

		$border_style_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_icon_border_container,
				'name'        => 'border_style_group',
				'title'       => esc_html__('Border Style', 'servicemaster'),
				'description' => esc_html__('Define styling for border around icon', 'servicemaster'),
			)
		);

		$border_style_row_1 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $border_style_group,
				'name'   => 'border_style_row_1',
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $border_style_row_1,
				'type'          => 'colorsimple',
				'name'          => 'side_area_icon_border_color',
				'default_value' => '',
				'label'         => esc_html__('Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $border_style_row_1,
				'type'          => 'colorsimple',
				'name'          => 'side_area_icon_border_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Hover Color', 'servicemaster'),
			)
		);

		$border_style_row_2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $border_style_group,
				'name'   => 'border_style_row_2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $border_style_row_2,
				'type'          => 'textsimple',
				'name'          => 'side_area_icon_border_width',
				'default_value' => '',
				'label'         => esc_html__('Width', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $border_style_row_2,
				'type'          => 'textsimple',
				'name'          => 'side_area_icon_border_radius',
				'default_value' => '',
				'label'         => esc_html__('Radius', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $border_style_row_2,
				'type'          => 'selectsimple',
				'name'          => 'side_area_icon_border_style',
				'default_value' => '',
				'label'         => esc_html__('Style', 'servicemaster'),
				'options'       => array(
					'solid'  => esc_html__('Solid', 'servicemaster'),
					'dashed' => esc_html__('Dashed', 'servicemaster'),
					'dotted' => esc_html__('Dotted', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'selectblank',
				'name'          => 'side_area_aligment',
				'default_value' => '',
				'label'         => esc_html__('Text Aligment', 'servicemaster'),
				'description'   => esc_html__('Choose text aligment for side area', 'servicemaster'),
				'options'       => array(
					'center' => esc_html__('Center', 'servicemaster'),
					'left'   => esc_html__('Left', 'servicemaster'),
					'right'  => esc_html__('Right', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'text',
				'name'          => 'side_area_title',
				'default_value' => '',
				'label'         => esc_html__('Side Area Title', 'servicemaster'),
				'description'   => esc_html__('Enter a title to appear in Side Area', 'servicemaster'),
				'args'          => array(
					'col_width' => 3,
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'color',
				'name'          => 'side_area_background_color',
				'default_value' => '',
				'label'         => esc_html__('Background Color', 'servicemaster'),
				'description'   => esc_html__('Choose a background color for Side Area', 'servicemaster'),
			)
		);

		$padding_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_panel,
				'name'        => 'padding_group',
				'title'       => esc_html__('Padding', 'servicemaster'),
				'description' => esc_html__('Define padding for Side Area', 'servicemaster'),
			)
		);

		$padding_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $padding_group,
				'name'   => 'padding_row',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $padding_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_padding_top',
				'default_value' => '',
				'label'         => esc_html__('Top Padding', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $padding_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_padding_right',
				'default_value' => '',
				'label'         => esc_html__('Right Padding', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $padding_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_padding_bottom',
				'default_value' => '',
				'label'         => esc_html__('Bottom Padding', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $padding_row,
				'type'          => 'textsimple',
				'name'          => 'side_area_padding_left',
				'default_value' => '',
				'label'         => esc_html__('Left Padding', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'select',
				'name'          => 'side_area_close_icon',
				'default_value' => 'light',
				'label'         => esc_html__('Close Icon Style', 'servicemaster'),
				'description'   => esc_html__('Choose a type of close icon', 'servicemaster'),
				'options'       => array(
					'light' => esc_html__('Light', 'servicemaster'),
					'dark'  => esc_html__('Dark', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'text',
				'name'          => 'side_area_close_icon_size',
				'default_value' => '',
				'label'         => esc_html__('Close Icon Size', 'servicemaster'),
				'description'   => esc_html__('Define close icon size', 'servicemaster'),
				'args'          => array(
					'col_width' => 3,
					'suffix'    => 'px'
				)
			)
		);

		$title_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_panel,
				'name'        => 'title_group',
				'title'       => esc_html__('Title', 'servicemaster'),
				'description' => esc_html__('Define Style for Side Area title', 'servicemaster'),
			)
		);

		$title_row_1 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $title_group,
				'name'   => 'title_row_1',
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_1,
				'type'          => 'colorsimple',
				'name'          => 'side_area_title_color',
				'default_value' => '',
				'label'         => esc_html__('Text Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_1,
				'type'          => 'textsimple',
				'name'          => 'side_area_title_fontsize',
				'default_value' => '',
				'label'         => esc_html__('Font Size', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_1,
				'type'          => 'textsimple',
				'name'          => 'side_area_title_lineheight',
				'default_value' => '',
				'label'         => esc_html__('Line Height', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_1,
				'type'          => 'selectblanksimple',
				'name'          => 'side_area_title_texttransform',
				'default_value' => '',
				'label'         => esc_html__('Text Transform', 'servicemaster'),
				'options'       => servicemaster_mikado_get_text_transform_array()
			)
		);

		$title_row_2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $title_group,
				'name'   => 'title_row_2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_2,
				'type'          => 'fontsimple',
				'name'          => 'side_area_title_google_fonts',
				'default_value' => '-1',
				'label'         => esc_html__('Font Family', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_2,
				'type'          => 'selectblanksimple',
				'name'          => 'side_area_title_fontstyle',
				'default_value' => '',
				'label'         => esc_html__('Font Style', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_style_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_2,
				'type'          => 'selectblanksimple',
				'name'          => 'side_area_title_fontweight',
				'default_value' => '',
				'label'         => esc_html__('Font Weight', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_weight_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $title_row_2,
				'type'          => 'textsimple',
				'name'          => 'side_area_title_letterspacing',
				'default_value' => '',
				'label'         => esc_html__('Letter Spacing', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);


		$text_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_panel,
				'name'        => 'text_group',
				'title'       => esc_html__('Text', 'servicemaster'),
				'description' => esc_html__('Define Style for Side Area text', 'servicemaster'),
			)
		);

		$text_row_1 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $text_group,
				'name'   => 'text_row_1',
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_1,
				'type'          => 'colorsimple',
				'name'          => 'side_area_text_color',
				'default_value' => '',
				'label'         => esc_html__('Text Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_1,
				'type'          => 'textsimple',
				'name'          => 'side_area_text_fontsize',
				'default_value' => '',
				'label'         => esc_html__('Font Size', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_1,
				'type'          => 'textsimple',
				'name'          => 'side_area_text_lineheight',
				'default_value' => '',
				'label'         => esc_html__('Line Height', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_1,
				'type'          => 'selectblanksimple',
				'name'          => 'side_area_text_texttransform',
				'default_value' => '',
				'label'         => esc_html__('Text Transform', 'servicemaster'),
				'options'       => servicemaster_mikado_get_text_transform_array()
			)
		);

		$text_row_2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $text_group,
				'name'   => 'text_row_2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_2,
				'type'          => 'fontsimple',
				'name'          => 'side_area_text_google_fonts',
				'default_value' => '-1',
				'label'         => esc_html__('Font Family', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_2,
				'type'          => 'fontsimple',
				'name'          => 'side_area_text_google_fonts',
				'default_value' => '-1',
				'label'         => esc_html__('Font Family', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_2,
				'type'          => 'selectblanksimple',
				'name'          => 'side_area_text_fontstyle',
				'default_value' => '',
				'label'         => esc_html__('Font Style', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_style_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_2,
				'type'          => 'selectblanksimple',
				'name'          => 'side_area_text_fontweight',
				'default_value' => '',
				'label'         => esc_html__('Font Weight', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_weight_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $text_row_2,
				'type'          => 'textsimple',
				'name'          => 'side_area_text_letterspacing',
				'default_value' => '',
				'label'         => esc_html__('Letter Spacing', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$widget_links_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $side_area_panel,
				'name'        => 'widget_links_group',
				'title'       => esc_html__('Link Style', 'servicemaster'),
				'description' => esc_html__('Define styles for Side Area widget links', 'servicemaster'),
			)
		);

		$widget_links_row_1 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $widget_links_group,
				'name'   => 'widget_links_row_1',
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_1,
				'type'          => 'colorsimple',
				'name'          => 'sidearea_link_color',
				'default_value' => '',
				'label'         => esc_html__('Text Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_1,
				'type'          => 'textsimple',
				'name'          => 'sidearea_link_font_size',
				'default_value' => '',
				'label'         => esc_html__('Font Size', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_1,
				'type'          => 'textsimple',
				'name'          => 'sidearea_link_line_height',
				'default_value' => '',
				'label'         => esc_html__('Line Height', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_1,
				'type'          => 'selectblanksimple',
				'name'          => 'sidearea_link_text_transform',
				'default_value' => '',
				'label'         => esc_html__('Text Transform', 'servicemaster'),
				'options'       => servicemaster_mikado_get_text_transform_array()
			)
		);

		$widget_links_row_2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $widget_links_group,
				'name'   => 'widget_links_row_2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_2,
				'type'          => 'fontsimple',
				'name'          => 'sidearea_link_font_family',
				'default_value' => '-1',
				'label'         => esc_html__('Font Family', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_2,
				'type'          => 'selectblanksimple',
				'name'          => 'sidearea_link_font_style',
				'default_value' => '',
				'label'         => esc_html__('Font Style', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_style_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_2,
				'type'          => 'selectblanksimple',
				'name'          => 'sidearea_link_font_weight',
				'default_value' => '',
				'label'         => esc_html__('Font Weight', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_weight_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_2,
				'type'          => 'textsimple',
				'name'          => 'sidearea_link_letter_spacing',
				'default_value' => '',
				'label'         => esc_html__('Letter Spacing', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$widget_links_row_3 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $widget_links_group,
				'name'   => 'widget_links_row_3',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $widget_links_row_3,
				'type'          => 'colorsimple',
				'name'          => 'sidearea_link_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Hover Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_panel,
				'type'          => 'yesno',
				'name'          => 'side_area_enable_bottom_border',
				'default_value' => 'no',
				'label'         => esc_html__('Border Bottom on Elements', 'servicemaster'),
				'description'   => esc_html__('Enable border bottom on elements in side area', 'servicemaster'),
				'args'          => array(
					'dependence'             => true,
					'dependence_hide_on_yes' => '',
					'dependence_show_on_yes' => '#mkd_side_area_bottom_border_container'
				)
			)
		);

		$side_area_bottom_border_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $side_area_panel,
				'name'            => 'side_area_bottom_border_container',
				'hidden_property' => 'side_area_enable_bottom_border',
				'hidden_value'    => 'no'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $side_area_bottom_border_container,
				'type'          => 'color',
				'name'          => 'side_area_bottom_border_color',
				'default_value' => '',
				'label'         => esc_html__('Border Bottom Color', 'servicemaster'),
				'description'   => esc_html__('Choose color for border bottom on elements in sidearea', 'servicemaster'),
			)
		);

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_sidearea_options_map', 5);

}