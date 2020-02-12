<?php

if(!function_exists('servicemaster_mikado_button_typography_styles')) {
	/**
	 * Typography styles for all button types
	 */
	function servicemaster_mikado_button_typography_styles() {
		$selector = '.mkd-btn';
		$styles   = array();

		$font_family = servicemaster_mikado_options()->getOptionValue('button_font_family');
		if(servicemaster_mikado_is_font_option_valid($font_family)) {
			$styles['font-family'] = servicemaster_mikado_get_font_option_val($font_family);
		}

		$text_transform = servicemaster_mikado_options()->getOptionValue('button_text_transform');
		if(!empty($text_transform)) {
			$styles['text-transform'] = $text_transform;
		}

		$font_style = servicemaster_mikado_options()->getOptionValue('button_font_style');
		if(!empty($font_style)) {
			$styles['font-style'] = $font_style;
		}

		$letter_spacing = servicemaster_mikado_options()->getOptionValue('button_letter_spacing');
		if($letter_spacing !== '') {
			$styles['letter-spacing'] = servicemaster_mikado_filter_px($letter_spacing).'px';
		}

		$font_weight = servicemaster_mikado_options()->getOptionValue('button_font_weight');
		if(!empty($font_weight)) {
			$styles['font-weight'] = $font_weight;
		}

		echo servicemaster_mikado_dynamic_css($selector, $styles);
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_button_typography_styles');
}

if(!function_exists('servicemaster_mikado_button_outline_styles')) {
	/**
	 * Generate styles for outline button
	 */
	function servicemaster_mikado_button_outline_styles() {
		//outline styles
		$outline_styles   = array();
		$outline_selector = '.mkd-btn.mkd-btn-outline';

		if(servicemaster_mikado_options()->getOptionValue('btn_outline_text_color')) {
			$outline_styles['color'] = servicemaster_mikado_options()->getOptionValue('btn_outline_text_color');
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_outline_border_color')) {
			$outline_styles['border-color'] = servicemaster_mikado_options()->getOptionValue('btn_outline_border_color');
		}

		echo servicemaster_mikado_dynamic_css($outline_selector, $outline_styles);

		//outline hover styles
		if(servicemaster_mikado_options()->getOptionValue('btn_outline_hover_text_color')) {
			echo servicemaster_mikado_dynamic_css(
				'.mkd-btn.mkd-btn-outline:not(.mkd-btn-custom-hover-color):hover',
				array('color' => servicemaster_mikado_options()->getOptionValue('btn_outline_hover_text_color').'!important')
			);
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_outline_hover_bg_color')) {
			echo servicemaster_mikado_dynamic_css(
				'.mkd-btn.mkd-btn-outline:not(.mkd-btn-custom-hover-bg):hover',
				array('background-color' => servicemaster_mikado_options()->getOptionValue('btn_outline_hover_bg_color').'!important')
			);
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_outline_hover_border_color')) {
			echo servicemaster_mikado_dynamic_css(
				'.mkd-btn.mkd-btn-outline:not(.mkd-btn-custom-border-hover):hover',
				array('border-color' => servicemaster_mikado_options()->getOptionValue('btn_outline_hover_border_color').'!important')
			);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_button_outline_styles');
}

if(!function_exists('servicemaster_mikado_button_solid_styles')) {
	/**
	 * Generate styles for solid type buttons
	 */
	function servicemaster_mikado_button_solid_styles() {
		//solid styles
		$solid_selector = '.mkd-btn.mkd-btn-solid';
		$solid_styles   = array();

		if(servicemaster_mikado_options()->getOptionValue('btn_solid_text_color')) {
			$solid_styles['color'] = servicemaster_mikado_options()->getOptionValue('btn_solid_text_color');
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_solid_border_color')) {
			$solid_styles['border-color'] = servicemaster_mikado_options()->getOptionValue('btn_solid_border_color');
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_solid_bg_color')) {
			$solid_styles['background-color'] = servicemaster_mikado_options()->getOptionValue('btn_solid_bg_color');
		}

		echo servicemaster_mikado_dynamic_css($solid_selector, $solid_styles);

		//solid hover styles
		if(servicemaster_mikado_options()->getOptionValue('btn_solid_hover_text_color')) {
			echo servicemaster_mikado_dynamic_css(
				'.mkd-btn.mkd-btn-solid:not(.mkd-btn-custom-hover-color):hover',
				array('color' => servicemaster_mikado_options()->getOptionValue('btn_solid_hover_text_color').'!important')
			);
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_solid_hover_bg_color')) {
			echo servicemaster_mikado_dynamic_css(
				'.mkd-btn.mkd-btn-solid:not(.mkd-btn-custom-hover-bg):hover',
				array('background-color' => servicemaster_mikado_options()->getOptionValue('btn_solid_hover_bg_color').'!important')
			);
		}

		if(servicemaster_mikado_options()->getOptionValue('btn_solid_hover_border_color')) {
			echo servicemaster_mikado_dynamic_css(
				'.mkd-btn.mkd-btn-solid:not(.mkd-btn-custom-hover-bg):hover',
				array('border-color' => servicemaster_mikado_options()->getOptionValue('btn_solid_hover_border_color').'!important')
			);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_button_solid_styles');
}