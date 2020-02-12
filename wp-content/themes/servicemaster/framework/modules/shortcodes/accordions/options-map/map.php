<?php
if (!function_exists('servicemaster_mikado_accordions_map')) {
	/**
	 * Add Accordion options to elements panel
	 */
	function servicemaster_mikado_accordions_map() {

		$panel = servicemaster_mikado_add_admin_panel(array(
			'title' => esc_html__('Accordions', 'servicemaster'),
			'name'  => 'panel_accordions',
			'page'  => '_elements_page'
		));

		//Typography options
		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'typography_section_title',
			'title'  => esc_html__('Typography', 'servicemaster'),
			'parent' => $panel
		));

		$typography_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'typography_group',
			'title'       => esc_html__('Typography', 'servicemaster'),
			'description' => esc_html__('Setup typography for accordions navigation', 'servicemaster'),
			'parent'      => $panel
		));

		$typography_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'typography_row',
			'next'   => true,
			'parent' => $typography_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'fontsimple',
			'name'          => 'accordions_font_family',
			'default_value' => '',
			'label'         => esc_html__('Font Family', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'selectsimple',
			'name'          => 'accordions_text_transform',
			'default_value' => '',
			'label'         => esc_html__('Text Transform', 'servicemaster'),
			'options'       => servicemaster_mikado_get_text_transform_array()
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'selectsimple',
			'name'          => 'accordions_font_style',
			'default_value' => '',
			'label'         => esc_html__('Font Style', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_style_array()
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'textsimple',
			'name'          => 'accordions_letter_spacing',
			'default_value' => '',
			'label'         => esc_html__('Letter Spacing', 'servicemaster'),
			'args'          => array(
				'suffix' => 'px'
			)
		));

		$typography_row2 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'typography_row2',
			'next'   => true,
			'parent' => $typography_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row2,
			'type'          => 'selectsimple',
			'name'          => 'accordions_font_weight',
			'default_value' => '',
			'label'         => esc_html__('Font Weight', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_weight_array()
		));

		//Initial Accordion Color Styles

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'accordion_color_section_title',
			'title'  => esc_html__('Basic Accordions Color Styles', 'servicemaster'),
			'parent' => $panel
		));

		$accordions_color_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'accordions_color_group',
			'title'       => esc_html__('Accordion Color Styles', 'servicemaster'),
			'description' => esc_html__('Set color styles for accordion title', 'servicemaster'),
			'parent'      => $panel
		));
		$accordions_color_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'accordions_color_row',
			'next'   => true,
			'parent' => $accordions_color_group
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'accordions_title_color',
			'default_value' => '',
			'label'         => esc_html__('Title Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'accordions_icon_color',
			'default_value' => '',
			'label'         => esc_html__('Icon Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'accordions_icon_back_color',
			'default_value' => '',
			'label'         => esc_html__('Icon Background Color', 'servicemaster')
		));

		$active_accordions_color_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'active_accordions_color_group',
			'title'       => esc_html__('Active and Hover Accordion Color Styles', 'servicemaster'),
			'description' => esc_html__('Set color styles for active and hover accordions', 'servicemaster'),
			'parent'      => $panel
		));
		$active_accordions_color_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'active_accordions_color_row',
			'next'   => true,
			'parent' => $active_accordions_color_group
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'accordions_title_color_active',
			'default_value' => '',
			'label'         => esc_html__('Title Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'accordions_icon_color_active',
			'default_value' => '',
			'label'         => esc_html__('Icon Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'accordions_icon_back_color_active',
			'default_value' => '',
			'label'         => esc_html__('Icon Background Color', 'servicemaster')
		));

		//Boxed Accordion Color Styles

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'boxed_accordion_color_section_title',
			'title'  => esc_html__('Boxed Accordion Title Color Styles', 'servicemaster'),
			'parent' => $panel
		));
		$boxed_accordions_color_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'boxed_accordions_color_group',
			'title'       => esc_html__('Boxed Accordion Title Color Styles', 'servicemaster'),
			'description' => esc_html__('Set color styles for boxed accordion title', 'servicemaster'),
			'parent'      => $panel
		));
		$boxed_accordions_color_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'boxed_accordions_color_row',
			'next'   => true,
			'parent' => $boxed_accordions_color_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $boxed_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'boxed_accordions_color',
			'default_value' => '',
			'label'         => esc_html__('Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $boxed_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'boxed_accordions_back_color',
			'default_value' => '',
			'label'         => esc_html__('Background Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $boxed_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'boxed_accordions_border_color',
			'default_value' => '',
			'label'         => esc_html__('Border Color', 'servicemaster')
		));

		//Active Boxed Accordion Color Styles

		$active_boxed_accordions_color_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'active_boxed_accordions_color_group',
			'title'       => esc_html__('Active and Hover Title Color Styles', 'servicemaster'),
			'description' => esc_html__('Set color styles for active and hover accordions', 'servicemaster'),
			'parent'      => $panel
		));
		$active_boxed_accordions_color_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'active_boxed_accordions_color_row',
			'next'   => true,
			'parent' => $active_boxed_accordions_color_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_boxed_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'boxed_accordions_color_active',
			'default_value' => '',
			'label'         => esc_html__('Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_boxed_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'boxed_accordions_back_color_active',
			'default_value' => '',
			'label'         => esc_html__('Background Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_boxed_accordions_color_row,
			'type'          => 'colorsimple',
			'name'          => 'boxed_accordions_border_color_active',
			'default_value' => '',
			'label'         => esc_html__('Border Color', 'servicemaster')
		));

	}

	add_action('servicemaster_mikado_options_elements_map', 'servicemaster_mikado_accordions_map', 11);
}