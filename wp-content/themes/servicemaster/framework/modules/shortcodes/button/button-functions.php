<?php

if (!function_exists('servicemaster_mikado_get_button_html')) {
	/**
	 * Calls button shortcode with given parameters and returns it's output
	 *
	 * @param $params
	 *
	 * @return mixed|string
	 */
	function servicemaster_mikado_get_button_html($params) {
		if(servicemaster_mikado_core_installed()) {
            $button_html = servicemaster_mikado_execute_shortcode('mkd_button', $params);
            $button_html = str_replace("\n", '', $button_html);

            return $button_html;
        }
	}
}

if (!function_exists('servicemaster_mikado_get_btn_hover_animation_types')) {
	/**
	 * @param bool $empty_val
	 *
	 * @return array
	 */
	function servicemaster_mikado_get_btn_hover_animation_types($empty_val = false) {
		$types = array(
			'disable'         => 'Disable Animation',
			'fill-from-top'   => 'Fill From Top',
			'fill-from-left'  => 'Fill From Left',
			'fill-from-right' => 'Fill From Right'
		);

		if ($empty_val) {
			$types = array_merge(array(
				'' => 'Default'
			), $types);
		}

		return $types;
	}
}

if (!function_exists('mkd_get_btn_types')) {
	function servicemaster_mikado_get_btn_types($empty_val = false) {
		$types = array(
			'outline'       => 'Outline',
			'solid'         => 'Solid',
			'white'         => 'White',
			'white-outline' => 'White Outline',
			'black'         => 'Black',
			'underline'     => 'Underline'
		);

		if ($empty_val) {
			$types = array_merge(array(
				'' => 'Default'
			), $types);
		}

		return $types;
	}
}