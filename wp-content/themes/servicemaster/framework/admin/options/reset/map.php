<?php

if(!function_exists('servicemaster_mikado_reset_options_map')) {
	/**
	 * Reset options panel
	 */
	function servicemaster_mikado_reset_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_reset_page',
				'title' => esc_html__('Reset', 'servicemaster'),
				'icon'  => 'icon_refresh'
			)
		);

		$panel_reset = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_reset_page',
				'name'  => 'panel_reset',
				'title' => esc_html__('Reset', 'servicemaster'),
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'reset_to_defaults',
			'default_value' => 'no',
			'label'         => esc_html__('Reset to Defaults', 'servicemaster'),
			'description'   => esc_html__('This option will reset all Mikado Options values to defaults', 'servicemaster'),
			'parent'        => $panel_reset
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_reset_options_map', 19);

}