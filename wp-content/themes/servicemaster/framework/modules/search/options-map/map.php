<?php

if(!function_exists('servicemaster_mikado_search_options_map')) {

	function servicemaster_mikado_search_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_search_page',
				'title' => esc_html__('Search', 'servicemaster'),
				'icon'  => 'icon_search'
			)
		);

		$search_panel = servicemaster_mikado_add_admin_panel(
			array(
				'title' => esc_html__('Search', 'servicemaster'),
				'name'  => 'search',
				'page'  => '_search_page'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_panel,
				'type'          => 'select',
				'name'          => 'search_type',
				'default_value' => 'fullscreen-search',
				'label'         => esc_html__('Select Search Type', 'servicemaster'),
				'description'   => esc_html__("Choose a type of Mikado search bar (Note: Slide From Header Bottom search type doesn't work with transparent header)", 'servicemaster'),
				'options'       => array(
					'fullscreen-search'                => esc_html__('Fullscreen Search', 'servicemaster'),
					'search-covers-header'             => esc_html__('Search Covers Header', 'servicemaster'),
					'search-slides-from-window-top'    => esc_html__('Slide from Window Top', 'servicemaster'),
					'search-dropdown'                  => esc_html__('Search Dropdown', 'servicemaster')
				),
				'args'          => array(
					'dependence' => true,
					'hide'       => array(
						'search-covers-header'             => '#mkd_search_height_container, #mkd_search_animation_container',
						'fullscreen-search'                => '#mkd_search_height_container, #mkd_search_close_container',
						'search-slides-from-window-top'    => '#mkd_search_height_container, #mkd_search_animation_container'
					),
					'show'       => array(
						'search-covers-header'             => '#mkd_search_close_container',
						'fullscreen-search'                => '#mkd_search_animation_container',
						'search-slides-from-window-top'    => '#mkd_search_close_container'
					)
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_panel,
				'type'          => 'select',
				'name'          => 'search_icon_pack',
				'default_value' => 'font_awesome',
				'label'         => esc_html__('Search Icon Pack', 'servicemaster'),
				'description'   => esc_html__('Choose icon pack for search icon', 'servicemaster'),
				'options'       => servicemaster_mikado_icon_collections()->getIconCollectionsExclude(array(
					'linea_icons',
					'simple_line_icons',
					'dripicons'
				))
			)
		);

		$search_height_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $search_panel,
				'name'            => 'search_height_container',
				'hidden_property' => 'search_type',
				'hidden_value'    => '',
				'hidden_values'   => array(
					'fullscreen-search',
					'search-covers-header',
					'search-slides-from-window-top'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_height_container,
				'type'          => 'text',
				'name'          => 'search_height',
				'default_value' => '',
				'label'         => esc_html__('Search bar height', 'servicemaster'),
				'description'   => esc_html__('Set search bar height', 'servicemaster'),
				'args'          => array(
					'col_width' => 3,
					'suffix'    => 'px'
				)
			)
		);

		$search_animation_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $search_panel,
				'name'            => 'search_animation_container',
				'hidden_property' => 'search_type',
				'hidden_value'    => '',
				'hidden_values'   => array(
					'search-covers-header',
					'search-slides-from-window-top'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_panel,
				'type'          => 'yesno',
				'name'          => 'search_in_grid',
				'default_value' => 'yes',
				'label'         => esc_html__('Search area in grid', 'servicemaster'),
				'description'   => esc_html__('Set search area to be in grid', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'name' => 'fullscreen_search_background_image',
			'type' => 'image',
			'parent' => $search_animation_container,
			'label' => esc_html__('Full Screen Search Background Image', 'servicemaster'),
			'description' => esc_html__('Choose full screen search background image', 'servicemaster')
		));

		servicemaster_mikado_add_admin_section_title(
			array(
				'parent' => $search_panel,
				'name'   => 'initial_header_icon_title',
				'title'  => esc_html__('Initial Search Icon in Header', 'servicemaster')
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_panel,
				'type'          => 'text',
				'name'          => 'header_search_icon_size',
				'default_value' => '',
				'label'         => esc_html__('Icon Size', 'servicemaster'),
				'description'   => esc_html__('Set size for icon', 'servicemaster'),
				'args'          => array(
					'col_width' => 3,
					'suffix'    => 'px'
				)
			)
		);

		$search_icon_color_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Icon Colors', 'servicemaster'),
				'description' => esc_html__('Define color style for icon', 'servicemaster'),
				'name'        => 'search_icon_color_group'
			)
		);

		$search_icon_color_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_icon_color_group,
				'name'   => 'search_icon_color_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent' => $search_icon_color_row,
				'type'   => 'colorsimple',
				'name'   => 'header_search_icon_color',
				'label'  => esc_html__('Color', 'servicemaster')
			)
		);
		servicemaster_mikado_add_admin_field(
			array(
				'parent' => $search_icon_color_row,
				'type'   => 'colorsimple',
				'name'   => 'header_search_icon_hover_color',
				'label'  => esc_html__('Hover Color', 'servicemaster')
			)
		);
		servicemaster_mikado_add_admin_field(
			array(
				'parent' => $search_icon_color_row,
				'type'   => 'colorsimple',
				'name'   => 'header_light_search_icon_color',
				'label'  => esc_html__('Light Header Icon Color', 'servicemaster')
			)
		);
		servicemaster_mikado_add_admin_field(
			array(
				'parent' => $search_icon_color_row,
				'type'   => 'colorsimple',
				'name'   => 'header_light_search_icon_hover_color',
				'label'  => esc_html__('Light Header Icon Hover Color', 'servicemaster')
			)
		);

		$search_icon_color_row2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_icon_color_group,
				'name'   => 'search_icon_color_row2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent' => $search_icon_color_row2,
				'type'   => 'colorsimple',
				'name'   => 'header_dark_search_icon_color',
				'label'  => esc_html__('Dark Header Icon Color', 'servicemaster')
			)
		);
		servicemaster_mikado_add_admin_field(
			array(
				'parent' => $search_icon_color_row2,
				'type'   => 'colorsimple',
				'name'   => 'header_dark_search_icon_hover_color',
				'label'  => esc_html__('Dark Header Icon Hover Color', 'servicemaster')
			)
		);


		$search_icon_background_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Icon Background Style', 'servicemaster'),
				'description' => esc_html__('Define background style for icon', 'servicemaster'),
				'name'        => 'search_icon_background_group'
			)
		);

		$search_icon_background_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_icon_background_group,
				'name'   => 'search_icon_background_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_background_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_background_color',
				'default_value' => '',
				'label'         => esc_html__('Background Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_background_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_background_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Background Hover Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_panel,
				'type'          => 'yesno',
				'name'          => 'enable_search_icon_text',
				'default_value' => 'no',
				'label'         => esc_html__('Enable Search Icon Text', 'servicemaster'),
				'description'   => esc_html__("Enable this option to show 'Search' text next to search icon in header", 'servicemaster'),
				'args'          => array(
					'dependence'             => true,
					'dependence_hide_on_yes' => '',
					'dependence_show_on_yes' => '#mkd_enable_search_icon_text_container'
				)
			)
		);

		$enable_search_icon_text_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $search_panel,
				'name'            => 'enable_search_icon_text_container',
				'hidden_property' => 'enable_search_icon_text',
				'hidden_value'    => 'no'
			)
		);

		$enable_search_icon_text_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $enable_search_icon_text_container,
				'title'       => esc_html__('Search Icon Text', 'servicemaster'),
				'name'        => 'enable_search_icon_text_group',
				'description' => esc_html__('Define Style for Search Icon Text', 'servicemaster')
			)
		);

		$enable_search_icon_text_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $enable_search_icon_text_group,
				'name'   => 'enable_search_icon_text_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_text_color',
				'label'         => esc_html__('Text Color', 'servicemaster'),
				'default_value' => ''
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_text_color_hover',
				'label'         => esc_html__('Text Hover Color', 'servicemaster'),
				'default_value' => ''
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row,
				'type'          => 'textsimple',
				'name'          => 'search_icon_text_fontsize',
				'label'         => esc_html__('Font Size', 'servicemaster'),
				'default_value' => '',
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row,
				'type'          => 'textsimple',
				'name'          => 'search_icon_text_lineheight',
				'label'         => esc_html__('Line Height', 'servicemaster'),
				'default_value' => '',
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$enable_search_icon_text_row2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $enable_search_icon_text_group,
				'name'   => 'enable_search_icon_text_row2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_icon_text_texttransform',
				'label'         => esc_html__('Text Transform', 'servicemaster'),
				'default_value' => '',
				'options'       => servicemaster_mikado_get_text_transform_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row2,
				'type'          => 'fontsimple',
				'name'          => 'search_icon_text_google_fonts',
				'label'         => esc_html__('Font Family', 'servicemaster'),
				'default_value' => '-1',
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_icon_text_fontstyle',
				'label'         => esc_html__('Font Style', 'servicemaster'),
				'default_value' => '',
				'options'       => servicemaster_mikado_get_font_style_array(),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_icon_text_fontweight',
				'label'         => esc_html__('Font Weight', 'servicemaster'),
				'default_value' => '',
				'options'       => servicemaster_mikado_get_font_weight_array(),
			)
		);

		$enable_search_icon_text_row3 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $enable_search_icon_text_group,
				'name'   => 'enable_search_icon_text_row3',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $enable_search_icon_text_row3,
				'type'          => 'textsimple',
				'name'          => 'search_icon_text_letterspacing',
				'label'         => esc_html__('Letter Spacing', 'servicemaster'),
				'default_value' => '',
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$search_icon_spacing_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Icon Spacing', 'servicemaster'),
				'description' => esc_html__('Define padding and margins for Search icon', 'servicemaster'),
				'name'        => 'search_icon_spacing_group'
			)
		);

		$search_icon_spacing_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_icon_spacing_group,
				'name'   => 'search_icon_spacing_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'search_padding_left',
				'default_value' => '',
				'label'         => esc_html__('Padding Left', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'search_padding_right',
				'default_value' => '',
				'label'         => esc_html__('Padding Right', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'search_margin_left',
				'default_value' => '',
				'label'         => esc_html__('Margin Left', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_spacing_row,
				'type'          => 'textsimple',
				'name'          => 'search_margin_right',
				'default_value' => '',
				'label'         => esc_html__('Margin Right', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_section_title(
			array(
				'parent' => $search_panel,
				'name'   => 'search_form_title',
				'title'  => esc_html__('Search Bar', 'servicemaster')
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_panel,
				'type'          => 'color',
				'name'          => 'search_background_color',
				'default_value' => '',
				'label'         => esc_html__('Background Color', 'servicemaster'),
				'description'   => esc_html__('Choose a background color for Mikado search bar', 'servicemaster')
			)
		);

		$search_input_text_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Search Input Text', 'servicemaster'),
				'description' => esc_html__('Define style for search text', 'servicemaster'),
				'name'        => 'search_input_text_group'
			)
		);

		$search_input_text_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_input_text_group,
				'name'   => 'search_input_text_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row,
				'type'          => 'colorsimple',
				'name'          => 'search_text_color',
				'default_value' => '',
				'label'         => esc_html__('Text Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row,
				'type'          => 'colorsimple',
				'name'          => 'search_text_disabled_color',
				'default_value' => '',
				'label'         => esc_html__('Disabled Text Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row,
				'type'          => 'textsimple',
				'name'          => 'search_text_fontsize',
				'default_value' => '',
				'label'         => esc_html__('Font Size', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row,
				'type'          => 'selectblanksimple',
				'name'          => 'search_text_texttransform',
				'default_value' => '',
				'label'         => esc_html__('Text Transform', 'servicemaster'),
				'options'       => servicemaster_mikado_get_text_transform_array()
			)
		);

		$search_input_text_row2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_input_text_group,
				'name'   => 'search_input_text_row2'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row2,
				'type'          => 'fontsimple',
				'name'          => 'search_text_google_fonts',
				'default_value' => '-1',
				'label'         => esc_html__('Font Family', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_text_fontstyle',
				'default_value' => '',
				'label'         => esc_html__('Font Style', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_style_array(),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_text_fontweight',
				'default_value' => '',
				'label'         => esc_html__('Font Weight', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_weight_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_input_text_row2,
				'type'          => 'textsimple',
				'name'          => 'search_text_letterspacing',
				'default_value' => '',
				'label'         => esc_html__('Letter Spacing', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$search_label_text_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Search Label Text', 'servicemaster'),
				'description' => esc_html__('Define style for search label text', 'servicemaster'),
				'name'        => 'search_label_text_group'
			)
		);

		$search_label_text_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_label_text_group,
				'name'   => 'search_label_text_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row,
				'type'          => 'colorsimple',
				'name'          => 'search_label_text_color',
				'default_value' => '',
				'label'         => esc_html__('Text Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row,
				'type'          => 'textsimple',
				'name'          => 'search_label_text_fontsize',
				'default_value' => '',
				'label'         => esc_html__('Font Size', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row,
				'type'          => 'selectblanksimple',
				'name'          => 'search_label_text_texttransform',
				'default_value' => '',
				'label'         => esc_html__('Text Transform', 'servicemaster'),
				'options'       => servicemaster_mikado_get_text_transform_array()
			)
		);

		$search_label_text_row2 = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_label_text_group,
				'name'   => 'search_label_text_row2',
				'next'   => true
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row2,
				'type'          => 'fontsimple',
				'name'          => 'search_label_text_google_fonts',
				'default_value' => '-1',
				'label'         => esc_html__('Font Family', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_label_text_fontstyle',
				'default_value' => '',
				'label'         => esc_html__('Font Style', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_style_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row2,
				'type'          => 'selectblanksimple',
				'name'          => 'search_label_text_fontweight',
				'default_value' => '',
				'label'         => esc_html__('Font Weight', 'servicemaster'),
				'options'       => servicemaster_mikado_get_font_weight_array()
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_label_text_row2,
				'type'          => 'textsimple',
				'name'          => 'search_label_text_letterspacing',
				'default_value' => '',
				'label'         => esc_html__('Letter Spacing', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$search_icon_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Search Icon', 'servicemaster'),
				'description' => esc_html__('Define style for search icon', 'servicemaster'),
				'name'        => 'search_icon_group'
			)
		);

		$search_icon_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_icon_group,
				'name'   => 'search_icon_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_color',
				'default_value' => '',
				'label'         => esc_html__('Icon Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_hover_color',
				'default_value' => '',
				'label'         => esc_html__('Icon Hover Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_row,
				'type'          => 'colorsimple',
				'name'          => 'search_icon_disabled_color',
				'default_value' => '',
				'label'         => esc_html__('Icon Disabled Color', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_icon_row,
				'type'          => 'textsimple',
				'name'          => 'search_icon_size',
				'default_value' => '',
				'label'         => esc_html__('Icon Size', 'servicemaster'),
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$search_close_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $search_panel,
				'name'            => 'search_close_container',
				'hidden_property' => 'search_type',
				'hidden_value'    => 'fullscreen-search',
			)
		);

		$search_close_icon_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_close_container,
				'title'       => esc_html__('Search Close', 'servicemaster'),
				'description' => esc_html__('Define style for search close icon', 'servicemaster'),
				'name'        => 'search_close_icon_group'
			)
		);

		$search_close_icon_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_close_icon_group,
				'name'   => 'search_icon_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_close_icon_row,
				'type'          => 'colorsimple',
				'name'          => 'search_close_color',
				'label'         => esc_html__('Icon Color', 'servicemaster'),
				'default_value' => ''
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_close_icon_row,
				'type'          => 'colorsimple',
				'name'          => 'search_close_hover_color',
				'label'         => esc_html__('Icon Hover Color', 'servicemaster'),
				'default_value' => ''
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_close_icon_row,
				'type'          => 'textsimple',
				'name'          => 'search_close_size',
				'label'         => esc_html__('Icon Size', 'servicemaster'),
				'default_value' => '',
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		$search_bottom_border_group = servicemaster_mikado_add_admin_group(
			array(
				'parent'      => $search_panel,
				'title'       => esc_html__('Search Bottom Border', 'servicemaster'),
				'description' => esc_html__('Define style for Search text input bottom border (for Fullscreen search type)', 'servicemaster'),
				'name'        => 'search_bottom_border_group'
			)
		);

		$search_bottom_border_row = servicemaster_mikado_add_admin_row(
			array(
				'parent' => $search_bottom_border_group,
				'name'   => 'search_icon_row'
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_bottom_border_row,
				'type'          => 'colorsimple',
				'name'          => 'search_border_color',
				'label'         => esc_html__('Border Color', 'servicemaster'),
				'default_value' => ''
			)
		);

		servicemaster_mikado_add_admin_field(
			array(
				'parent'        => $search_bottom_border_row,
				'type'          => 'colorsimple',
				'name'          => 'search_border_focus_color',
				'label'         => esc_html__('Border Focus Color', 'servicemaster'),
				'default_value' => ''
			)
		);



		$custom_sidebars = servicemaster_mikado_get_custom_sidebars();

		servicemaster_mikado_add_admin_section_title(
			array(
				'parent' => $search_panel,
				'name'   => 'search_page_options_title',
				'title'  => esc_html__('Search Page', 'servicemaster')
			)
		);

		if(count($custom_sidebars)>0){
			servicemaster_mikado_add_admin_field(
				array(
					'parent'        => $search_panel,
					'type'          => 'selectblank',
					'name'          => 'search_page_custom_sidebar',
					'default_value' => '',
					'label'         => esc_html__('Sidebar for Search Page', 'servicemaster'),
					'options'       => $custom_sidebars
				)
			);
		}

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_search_options_map', 6);

}