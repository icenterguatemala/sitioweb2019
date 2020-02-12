<?php

if(!function_exists('servicemaster_mikado_button_map')) {
	function servicemaster_mikado_button_map() {
		$panel = servicemaster_mikado_add_admin_panel(array(
			'title' => esc_html__('Button', 'servicemaster'),
			'name'  => 'panel_button',
			'page'  => '_elements_page'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'        => 'button_hover_animation',
			'type'        => 'select',
			'label'       => esc_html__('Hover Animation', 'servicemaster'),
			'description' => esc_html__('Choose default hover animation type', 'servicemaster'),
			'parent'      => $panel,
			'options'     => servicemaster_mikado_get_btn_hover_animation_types()
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
			'description' => esc_html__('Setup typography for all button types', 'servicemaster'),
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
			'name'          => 'button_font_family',
			'default_value' => '',
			'label'         => esc_html__('Font Family', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'selectsimple',
			'name'          => 'button_text_transform',
			'default_value' => '',
			'label'         => esc_html__('Text Transform', 'servicemaster'),
			'options'       => servicemaster_mikado_get_text_transform_array()
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'selectsimple',
			'name'          => 'button_font_style',
			'default_value' => '',
			'label'         => esc_html__('Font Style', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_style_array()
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $typography_row,
			'type'          => 'textsimple',
			'name'          => 'button_letter_spacing',
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
			'name'          => 'button_font_weight',
			'default_value' => '',
			'label'         => esc_html__('Font Weight', 'servicemaster'),
			'options'       => servicemaster_mikado_get_font_weight_array()
		));

		//Outline type options
		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'type_section_title',
			'title'  => esc_html__('Types', 'servicemaster'),
			'parent' => $panel
		));

		$outline_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'outline_group',
			'title'       => esc_html__('Outline Type', 'servicemaster'),
			'description' => esc_html__('Setup outline button type', 'servicemaster'),
			'parent'      => $panel
		));

		$outline_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'outline_row',
			'next'   => true,
			'parent' => $outline_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $outline_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_outline_text_color',
			'default_value' => '',
			'label'         => esc_html__('Text Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $outline_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_outline_hover_text_color',
			'default_value' => '',
			'label'         => esc_html__('Text Hover Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $outline_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_outline_hover_bg_color',
			'default_value' => '',
			'label'         => esc_html__('Hover Background Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $outline_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_outline_border_color',
			'default_value' => '',
			'label'         => esc_html__('Border Color', 'servicemaster'),
			'description'   => ''
		));

		$outline_row2 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'outline_row2',
			'next'   => true,
			'parent' => $outline_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $outline_row2,
			'type'          => 'colorsimple',
			'name'          => 'btn_outline_hover_border_color',
			'default_value' => '',
			'label'         => esc_html__('Hover Border Color', 'servicemaster'),
			'description'   => ''
		));

		//Solid type options
		$solid_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'solid_group',
			'title'       => esc_html__('Solid Type', 'servicemaster'),
			'description' => esc_html__('Setup solid button type', 'servicemaster'),
			'parent'      => $panel
		));

		$solid_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'solid_row',
			'next'   => true,
			'parent' => $solid_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $solid_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_solid_text_color',
			'default_value' => '',
			'label'         => esc_html__('Text Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $solid_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_solid_hover_text_color',
			'default_value' => '',
			'label'         => esc_html__('Text Hover Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $solid_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_solid_bg_color',
			'default_value' => '',
			'label'         => esc_html__('Background Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $solid_row,
			'type'          => 'colorsimple',
			'name'          => 'btn_solid_hover_bg_color',
			'default_value' => '',
			'label'         => esc_html__('Hover Background Color', 'servicemaster'),
			'description'   => ''
		));

		$solid_row2 = servicemaster_mikado_add_admin_row(array(
			'name'   => 'solid_row2',
			'next'   => true,
			'parent' => $solid_group
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $solid_row2,
			'type'          => 'colorsimple',
			'name'          => 'btn_solid_border_color',
			'default_value' => '',
			'label'         => esc_html__('Border Color', 'servicemaster'),
			'description'   => ''
		));

		servicemaster_mikado_add_admin_field(array(
			'parent'        => $solid_row2,
			'type'          => 'colorsimple',
			'name'          => 'btn_solid_hover_border_color',
			'default_value' => '',
			'label'         => esc_html__('Hover Border Color', 'servicemaster'),
			'description'   => ''
		));
	}

	add_action('servicemaster_mikado_options_elements_map', 'servicemaster_mikado_button_map');
}