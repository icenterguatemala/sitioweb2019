<?php

if (!function_exists('servicemaster_mikado_tabs_map')) {
	function servicemaster_mikado_tabs_map() {

		$panel = servicemaster_mikado_add_admin_panel(array(
			'title' => esc_html__('Tabs', 'servicemaster'),
			'name'  => 'panel_tabs',
			'page'  => '_elements_page'
		));

		//Typography options
		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'typography_section_title',
			'title'  => esc_html__('Tabs Navigation Typography', 'servicemaster'),
			'parent' => $panel
		));

		$typography_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'typography_group',
			'title'       => esc_html__('Tabs Navigation Typography', 'servicemaster'),
			'description' => esc_html__('Setup typography for tabs navigation', 'servicemaster'),
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
			'name'          => 'tabs_font_family',
			'default_value' => '',
			'label'         => esc_html__('Font Family', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'selectsimple',
			'name'          => 'tabs_text_transform',
			'default_value' => '',
			'label'         => esc_html__('Text Transform', 'servicemaster'),
			'options'       => servicemaster_mikado_get_text_transform_array()
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'selectsimple',
			'name'          => 'tabs_font_style',
			'default_value' => '',
			'label'         => esc_html__('Font Style', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_style_array()
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'textsimple',
			'name'          => 'tabs_letter_spacing',
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
			'name'          => 'tabs_font_weight',
			'default_value' => '',
			'label'         => esc_html__('Font Weight', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_weight_array()
		));

		//Initial Tab Color Styles

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'tab_color_section_title',
			'title'  => esc_html__('Tab Navigation Color Styles', 'servicemaster'),
			'parent' => $panel
		));
		$tabs_color_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'tabs_color_group',
			'title'       => esc_html__('Tab Navigation Color Styles', 'servicemaster'),
			'description' => esc_html__('Set color styles for tab navigation', 'servicemaster'),
			'parent'      => $panel
		));
		$tabs_color_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'tabs_color_row',
			'next'   => true,
			'parent' => $tabs_color_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $tabs_color_row,
			'type'          => 'colorsimple',
			'name'          => 'tabs_color',
			'default_value' => '',
			'label'         => esc_html__('Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $tabs_color_row,
			'type'          => 'colorsimple',
			'name'          => 'tabs_back_color',
			'default_value' => '',
			'label'         => esc_html__('Background Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $tabs_color_row,
			'type'          => 'colorsimple',
			'name'          => 'tabs_border_color',
			'default_value' => '',
			'label'         => esc_html__('Border Color', 'servicemaster')
		));

		//Active Tab Color Styles

		$active_tabs_color_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'active_tabs_color_group',
			'title'       => esc_html__('Active and Hover Navigation Color Styles', 'servicemaster'),
			'description' => esc_html__('Set color styles for active and hover tabs', 'servicemaster'),
			'parent'      => $panel
		));
		$active_tabs_color_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'active_tabs_color_row',
			'next'   => true,
			'parent' => $active_tabs_color_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_tabs_color_row,
			'type'          => 'colorsimple',
			'name'          => 'tabs_color_active',
			'default_value' => '',
			'label'         => esc_html__('Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_tabs_color_row,
			'type'          => 'colorsimple',
			'name'          => 'tabs_back_color_active',
			'default_value' => '',
			'label'         => esc_html__('Background Color', 'servicemaster')
		));
		servicemaster_mikado_add_admin_field(array(
			'parent'        => $active_tabs_color_row,
			'type'          => 'colorsimple',
			'name'          => 'tabs_border_color_active',
			'default_value' => '',
			'label'         => esc_html__('Border Color', 'servicemaster')
		));

	}

	add_action('servicemaster_mikado_options_elements_map', 'servicemaster_mikado_tabs_map');
}