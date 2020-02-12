<?php
if (!function_exists('servicemaster_mikado_contact_form_map')) {
	/**
	 * Map Contact Form 7 shortcode
	 * Hooks on vc_after_init action
	 */
	function servicemaster_mikado_contact_form_map() {

		vc_add_param('contact-form-7', array(
			'type'        => 'dropdown',
			'heading'     => esc_html__('Style', 'servicemaster'),
			'param_name'  => 'html_class',
			'value'       => array(
				esc_html__('Default', 'servicemaster')        => 'default',
				esc_html__('Custom Style 1', 'servicemaster') => 'cf7_custom_style_1',
				esc_html__('Custom Style 2', 'servicemaster') => 'cf7_custom_style_2',
                esc_html__('Custom Style 3', 'servicemaster') => 'cf7_custom_style_3',
                esc_html__('Custom Style 4', 'servicemaster') => 'cf7_custom_style_4'
			),
			'description' => esc_html__('You can style each form element individually in Mikado Options > Contact Form 7', 'servicemaster')
		));

	}

	add_action('vc_after_init', 'servicemaster_mikado_contact_form_map');
}
?>