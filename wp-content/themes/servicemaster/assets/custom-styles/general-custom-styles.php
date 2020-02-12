<?php
if (!function_exists('servicemaster_mikado_design_styles')) {
	/**
	 * Generates general custom styles
	 */
	function servicemaster_mikado_design_styles() {

		$preload_background_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('preload_pattern_image') !== "") {
			$preload_background_styles['background-image'] = 'url(' . servicemaster_mikado_options()->getOptionValue('preload_pattern_image') . ') !important';
		} else {
			$preload_background_styles['background-image'] = 'url(' . esc_url(MIKADO_ASSETS_ROOT . "/img/preload_pattern.png") . ') !important';
		}

		echo servicemaster_mikado_dynamic_css('.mkd-preload-background', $preload_background_styles);

		if (servicemaster_mikado_options()->getOptionValue('google_fonts')) {
			$font_family = servicemaster_mikado_options()->getOptionValue('google_fonts');
			if (servicemaster_mikado_is_font_option_valid($font_family)) {
				echo servicemaster_mikado_dynamic_css('body', array('font-family' => servicemaster_mikado_get_font_option_val($font_family)));
			}
		}
		if (servicemaster_mikado_options()->getOptionValue('first_color') !== "") {

			extract(servicemaster_mikado_generate_first_color_selectors());

			echo servicemaster_mikado_dynamic_css($color_selector, array('color' => servicemaster_mikado_options()->getOptionValue('first_color')));
			echo servicemaster_mikado_dynamic_css($color_important_selector, array('color' => servicemaster_mikado_options()->getOptionValue('first_color') . '!important'));
			echo servicemaster_mikado_dynamic_css('::selection', array('background' => servicemaster_mikado_options()->getOptionValue('first_color')));
			echo servicemaster_mikado_dynamic_css('::-moz-selection', array('background' => servicemaster_mikado_options()->getOptionValue('first_color')));
			echo servicemaster_mikado_dynamic_css($background_color_selector, array('background-color' => servicemaster_mikado_options()->getOptionValue('first_color')));
			echo servicemaster_mikado_dynamic_css($border_color_selector, array('border-color' => servicemaster_mikado_options()->getOptionValue('first_color')));
			echo servicemaster_mikado_dynamic_css($border_color_important_selector, array('border-color' => servicemaster_mikado_options()->getOptionValue('first_color') . '!important'));
		}

		if (servicemaster_mikado_options()->getOptionValue('page_background_color')) {
			$background_color_selector = array(
				'.mkd-content .mkd-content-inner > .mkd-container',
				'.mkd-content .mkd-content-inner > .mkd-full-width'
			);
			echo servicemaster_mikado_dynamic_css($background_color_selector, array('background-color' => servicemaster_mikado_options()->getOptionValue('page_background_color')));
		}


		$comments_background_color = servicemaster_mikado_options()->getOptionValue('comments_background_color');
		if ($comments_background_color) {
			$background_color_selector = array(
				'.mkd-comment-holder .mkd-comment'
			);
			echo servicemaster_mikado_dynamic_css($background_color_selector, array('background-color' => $comments_background_color));
		}

		if (servicemaster_mikado_options()->getOptionValue('selection_color')) {
			echo servicemaster_mikado_dynamic_css('::selection', array('background' => servicemaster_mikado_options()->getOptionValue('selection_color')));
			echo servicemaster_mikado_dynamic_css('::-moz-selection', array('background' => servicemaster_mikado_options()->getOptionValue('selection_color')));
		}

		$gradient_style1_start_color = servicemaster_mikado_options()->getOptionValue('gradient_style1_start_color');
		$gradient_style1_end_color = servicemaster_mikado_options()->getOptionValue('gradient_style1_end_color');

		if ($gradient_style1_start_color !== '#0090f0' && $gradient_style1_start_color !== '' &&
			$gradient_style1_end_color !== '#00edfd' && $gradient_style1_end_color !== ''
		) {

			echo servicemaster_mikado_dynamic_css('.mkd-type1-gradient-left-to-right', array(
				'background' => '-webkit-linear-gradient(left,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => '-o-linear-gradient(right,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => '-moz-linear-gradient(right,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => 'linear-gradient(to right,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
			));

            echo servicemaster_mikado_dynamic_css('.mkd-type1-separator-gradient-left-to-right', array(
                'border-image' => '-webkit-linear-gradient(left,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
                'border-image' => '-o-linear-gradient(right,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
                'border-image' => '-moz-linear-gradient(right,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
                'border-image' => 'linear-gradient(to right,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
                'border-image-slice' => '1'
            ));

			echo servicemaster_mikado_dynamic_css('.mkd-type1-gradient-bottom-to-top, .mkd-type1-gradient-bottom-to-top-after:after', array(
				'background' => '-webkit-linear-gradient(bottom,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => '-o-linear-gradient(top,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => '-moz-linear-gradient(top,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => 'linear-gradient(to top,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type1-gradient-left-bottom-to-right-top', array(
				'background' => '-webkit-linear-gradient(right top,' . $gradient_style1_end_color . ', ' . $gradient_style1_start_color . ')',
				'background' => '-o-linear-gradient(right top,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => '-moz-linear-gradient(right top,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
				'background' => 'linear-gradient(to right top,' . $gradient_style1_start_color . ', ' . $gradient_style1_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type1-gradient-left-to-right-2x', array(
				'background'      => '-webkit-linear-gradient(left,' . $gradient_style1_start_color . ' 0%, ' . $gradient_style1_end_color . ' 50% ,' . $gradient_style1_start_color . ' 100%)',
				'background'      => '-o-linear-gradient(right,' . $gradient_style1_start_color . ' 0%, ' . $gradient_style1_end_color . ' 50% ,' . $gradient_style1_start_color . ' 100%)',
				'background'      => '-moz-linear-gradient(right,' . $gradient_style1_start_color . ' 0%, ' . $gradient_style1_end_color . ' 50% ,' . $gradient_style1_start_color . ' 100%)',
				'background'      => 'linear-gradient(to right,' . $gradient_style1_start_color . ' 0%, ' . $gradient_style1_end_color . ' 50%,' . $gradient_style1_start_color . ' 100%)',
				'background-size' => '200% 200%'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type1-gradient-left-to-right-text i, .mkd-type1-gradient-left-to-right-text i:before, .mkd-type1-gradient-left-to-right-text span', array(
				'background'              => '-webkit-linear-gradient(right top,' . $gradient_style1_end_color . ', ' . $gradient_style1_start_color . ')',
				'color'                   => $gradient_style1_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type1-gradient-bottom-to-top-text i, .mkd-type1-gradient-bottom-to-top-text i:before, .mkd-type1-gradient-bottom-to-top-text span', array(
				'background'              => '-webkit-linear-gradient(bottom,' . $gradient_style1_end_color . ', ' . $gradient_style1_start_color . ')',
				'color'                   => $gradient_style1_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

		}

		$gradient_style2_start_color = servicemaster_mikado_options()->getOptionValue('gradient_style2_start_color');
		$gradient_style2_end_color = servicemaster_mikado_options()->getOptionValue('gradient_style2_end_color');

		if ($gradient_style2_start_color !== '#ad6ef0' && $gradient_style2_start_color !== '' &&
			$gradient_style2_end_color !== '#03a9f5' && $gradient_style2_end_color !== ''
		) {

			echo servicemaster_mikado_dynamic_css('.mkd-type2-gradient-left-to-right', array(
				'background' => '-webkit-linear-gradient(left,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => '-o-linear-gradient(right,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => '-moz-linear-gradient(right,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => 'linear-gradient(to right,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
			));

            echo servicemaster_mikado_dynamic_css('.mkd-type2-separator-gradient-left-to-right', array(
                'border-image' => '-webkit-linear-gradient(left,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
                'border-image' => '-o-linear-gradient(right,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
                'border-image' => '-moz-linear-gradient(right,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
                'border-image' => 'linear-gradient(to right,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
                'border-image-slice' => '1'
            ));

			echo servicemaster_mikado_dynamic_css('.mkd-type2-gradient-bottom-to-top, .mkd-type2-gradient-bottom-to-top-after:after', array(
				'background' => '-webkit-linear-gradient(bottom,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => '-o-linear-gradient(top,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => '-moz-linear-gradient(top,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => 'linear-gradient(to top,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type2-gradient-left-bottom-to-right-top', array(
				'background' => '-webkit-linear-gradient(right top,' . $gradient_style2_end_color . ', ' . $gradient_style2_start_color . ')',
				'background' => '-o-linear-gradient(right top,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => '-moz-linear-gradient(right top,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
				'background' => 'linear-gradient(to right top,' . $gradient_style2_start_color . ', ' . $gradient_style2_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type2-gradient-left-to-right-2x', array(
				'background'      => '-webkit-linear-gradient(left,' . $gradient_style2_start_color . ' 0%, ' . $gradient_style2_end_color . ' 50% ,' . $gradient_style2_start_color . ' 100%)',
				'background'      => '-o-linear-gradient(right,' . $gradient_style2_start_color . ' 0%, ' . $gradient_style2_end_color . ' 50% ,' . $gradient_style2_start_color . ' 100%)',
				'background'      => '-moz-linear-gradient(right,' . $gradient_style2_start_color . ' 0%, ' . $gradient_style2_end_color . ' 50% ,' . $gradient_style2_start_color . ' 100%)',
				'background'      => 'linear-gradient(to right,' . $gradient_style2_start_color . ' 0%, ' . $gradient_style2_end_color . ' 50%,' . $gradient_style2_start_color . ' 100%)',
				'background-size' => '200% 200%'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type2-gradient-left-to-right-text i, .mkd-type2-gradient-left-to-right-text i:before, .mkd-type2-gradient-left-to-right-text span', array(
				'background'              => '-webkit-linear-gradient(right top,' . $gradient_style2_end_color . ', ' . $gradient_style2_start_color . ')',
				'color'                   => $gradient_style2_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type2-gradient-bottom-to-top-text i, .mkd-type2-gradient-bottom-to-top-text i:before, .mkd-type2-gradient-bottom-to-top-text span', array(
				'background'              => '-webkit-linear-gradient(bottom,' . $gradient_style2_end_color . ', ' . $gradient_style2_start_color . ')',
				'color'                   => $gradient_style2_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

		}

		$gradient_style3_start_color = servicemaster_mikado_options()->getOptionValue('gradient_style3_start_color');
		$gradient_style3_end_color = servicemaster_mikado_options()->getOptionValue('gradient_style3_end_color');

		if ($gradient_style3_start_color !== '#3b3860' && $gradient_style3_start_color !== '' &&
			$gradient_style3_end_color !== '#5d569f' && $gradient_style3_end_color !== ''
		) {

			echo servicemaster_mikado_dynamic_css('.mkd-type3-gradient-left-to-right', array(
				'background' => '-webkit-linear-gradient(left,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => '-o-linear-gradient(right,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => '-moz-linear-gradient(right,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => 'linear-gradient(to right,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
			));

            echo servicemaster_mikado_dynamic_css('.mkd-type3-separator-gradient-left-to-right', array(
                'border-image' => '-webkit-linear-gradient(left,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
                'border-image' => '-o-linear-gradient(right,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
                'border-image' => '-moz-linear-gradient(right,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
                'border-image' => 'linear-gradient(to right,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
                'border-image-slice' => '1'
            ));

			echo servicemaster_mikado_dynamic_css('.mkd-type3-gradient-bottom-to-top, .mkd-type3-gradient-bottom-to-top-after:after', array(
				'background' => '-webkit-linear-gradient(bottom,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => '-o-linear-gradient(top,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => '-moz-linear-gradient(top,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => 'linear-gradient(to top,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type3-gradient-left-bottom-to-right-top', array(
				'background' => '-webkit-linear-gradient(right top,' . $gradient_style3_end_color . ', ' . $gradient_style3_start_color . ')',
				'background' => '-o-linear-gradient(right top,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => '-moz-linear-gradient(right top,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
				'background' => 'linear-gradient(to right top,' . $gradient_style3_start_color . ', ' . $gradient_style3_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type3-gradient-left-to-right-2x', array(
				'background'      => '-webkit-linear-gradient(left,' . $gradient_style3_start_color . ' 0%, ' . $gradient_style3_end_color . ' 50% ,' . $gradient_style3_start_color . ' 100%)',
				'background'      => '-o-linear-gradient(right,' . $gradient_style3_start_color . ' 0%, ' . $gradient_style3_end_color . ' 50% ,' . $gradient_style3_start_color . ' 100%)',
				'background'      => '-moz-linear-gradient(right,' . $gradient_style3_start_color . ' 0%, ' . $gradient_style3_end_color . ' 50% ,' . $gradient_style3_start_color . ' 100%)',
				'background'      => 'linear-gradient(to right,' . $gradient_style3_start_color . ' 0%, ' . $gradient_style3_end_color . ' 50%,' . $gradient_style3_start_color . ' 100%)',
				'background-size' => '200% 200%'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type3-gradient-left-to-right-text i, .mkd-type3-gradient-left-to-right-text i:before, .mkd-type3-gradient-left-to-right-text span', array(
				'background'              => '-webkit-linear-gradient(right top,' . $gradient_style3_end_color . ', ' . $gradient_style3_start_color . ')',
				'color'                   => $gradient_style3_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type3-gradient-bottom-to-top-text i, .mkd-type3-gradient-bottom-to-top-text i:before, .mkd-type3-gradient-bottom-to-top-text span', array(
				'background'              => '-webkit-linear-gradient(bottom,' . $gradient_style3_end_color . ', ' . $gradient_style3_start_color . ')',
				'color'                   => $gradient_style3_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

		}

		$gradient_style4_start_color = servicemaster_mikado_options()->getOptionValue('gradient_style4_start_color');
		$gradient_style4_end_color = servicemaster_mikado_options()->getOptionValue('gradient_style4_end_color');

		if ($gradient_style4_start_color !== '#3b3860' && $gradient_style4_start_color !== '' &&
			$gradient_style4_end_color !== '#5d569f' && $gradient_style4_end_color !== ''
		) {

			echo servicemaster_mikado_dynamic_css('.mkd-type4-gradient-left-to-right', array(
				'background' => '-webkit-linear-gradient(left,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => '-o-linear-gradient(right,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => '-moz-linear-gradient(right,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => 'linear-gradient(to right,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
			));

            echo servicemaster_mikado_dynamic_css('.mkd-type4-separator-gradient-left-to-right', array(
                'border-image' => '-webkit-linear-gradient(left,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
                'border-image' => '-o-linear-gradient(right,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
                'border-image' => '-moz-linear-gradient(right,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
                'border-image' => 'linear-gradient(to right,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
                'border-image-slice' => '1'
            ));

			echo servicemaster_mikado_dynamic_css('.mkd-type4-gradient-bottom-to-top, .mkd-type4-gradient-bottom-to-top-after:after', array(
				'background' => '-webkit-linear-gradient(bottom,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => '-o-linear-gradient(top,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => '-moz-linear-gradient(top,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => 'linear-gradient(to top,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type4-gradient-left-bottom-to-right-top', array(
				'background' => '-webkit-linear-gradient(right top,' . $gradient_style4_end_color . ', ' . $gradient_style4_start_color . ')',
				'background' => '-o-linear-gradient(right top,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => '-moz-linear-gradient(right top,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
				'background' => 'linear-gradient(to right top,' . $gradient_style4_start_color . ', ' . $gradient_style4_end_color . ')',
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type4-gradient-left-to-right-2x', array(
				'background'      => '-webkit-linear-gradient(left,' . $gradient_style4_start_color . ' 0%, ' . $gradient_style4_end_color . ' 50% ,' . $gradient_style4_start_color . ' 100%)',
				'background'      => '-o-linear-gradient(right,' . $gradient_style4_start_color . ' 0%, ' . $gradient_style4_end_color . ' 50% ,' . $gradient_style4_start_color . ' 100%)',
				'background'      => '-moz-linear-gradient(right,' . $gradient_style4_start_color . ' 0%, ' . $gradient_style4_end_color . ' 50% ,' . $gradient_style4_start_color . ' 100%)',
				'background'      => 'linear-gradient(to right,' . $gradient_style4_start_color . ' 0%, ' . $gradient_style4_end_color . ' 50%,' . $gradient_style4_start_color . ' 100%)',
				'background-size' => '200% 200%'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type4-gradient-left-to-right-text i, .mkd-type4-gradient-left-to-right-text i:before, .mkd-type4-gradient-left-to-right-text span', array(
				'background'              => '-webkit-linear-gradient(right top,' . $gradient_style4_end_color . ', ' . $gradient_style4_start_color . ')',
				'color'                   => $gradient_style4_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

			echo servicemaster_mikado_dynamic_css('.mkd-type4-gradient-bottom-to-top-text i, .mkd-type4-gradient-bottom-to-top-text i:before, .mkd-type4-gradient-bottom-to-top-text span', array(
				'background'              => '-webkit-linear-gradient(bottom,' . $gradient_style4_end_color . ', ' . $gradient_style4_start_color . ')',
				'color'                   => $gradient_style4_start_color,
				'-webkit-background-clip' => 'text',
				'-webkit-text-fill-color' => 'transparent'
			));

		}

        $boxed_class_selector = '.mkd-boxed .mkd-wrapper';

		if (servicemaster_mikado_get_meta_field_intersect('header_footer_in_box') == 'no') {
            $boxed_class_selector = '.mkd-boxed-content .mkd-wrapper';
        }

		$boxed_background_style = array();
		if (servicemaster_mikado_options()->getOptionValue('page_background_color_in_box')) {
			$boxed_background_style['background-color'] = servicemaster_mikado_options()->getOptionValue('page_background_color_in_box');
		}

		if (servicemaster_mikado_options()->getOptionValue('boxed_background_image')) {
			$boxed_background_style['background-image'] = 'url(' . esc_url(servicemaster_mikado_options()->getOptionValue('boxed_background_image')) . ')';
			$boxed_background_style['background-position'] = 'center 0px';
			$boxed_background_style['background-repeat'] = 'no-repeat';
		}

		if (servicemaster_mikado_options()->getOptionValue('boxed_pattern_background_image')) {
			$boxed_background_style['background-image'] = 'url(' . esc_url(servicemaster_mikado_options()->getOptionValue('boxed_pattern_background_image')) . ')';
			$boxed_background_style['background-position'] = '0px 0px';
			$boxed_background_style['background-repeat'] = 'repeat';
		}

		if (servicemaster_mikado_options()->getOptionValue('boxed_background_image_attachment')) {
			$boxed_background_style['background-attachment'] = (servicemaster_mikado_options()->getOptionValue('boxed_background_image_attachment'));
		}

		echo servicemaster_mikado_dynamic_css($boxed_class_selector, $boxed_background_style);

	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_design_styles');
}

if (!function_exists('servicemaster_mikado_h1_styles')) {

	function servicemaster_mikado_h1_styles() {

		$h1_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('h1_color') !== '') {
			$h1_styles['color'] = servicemaster_mikado_options()->getOptionValue('h1_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_google_fonts') !== '-1') {
			$h1_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('h1_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_fontsize') !== '') {
			$h1_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h1_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_lineheight') !== '') {
			$h1_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h1_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_texttransform') !== '') {
			$h1_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('h1_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_fontstyle') !== '') {
			$h1_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('h1_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_fontweight') !== '') {
			$h1_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('h1_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('h1_letterspacing') !== '') {
			$h1_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h1_letterspacing')) . 'px';
		}

		$h1_selector = array(
			'h1'
		);

		if (!empty($h1_styles)) {
			echo servicemaster_mikado_dynamic_css($h1_selector, $h1_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_h1_styles');
}

if (!function_exists('servicemaster_mikado_h2_styles')) {

	function servicemaster_mikado_h2_styles() {

		$h2_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('h2_color') !== '') {
			$h2_styles['color'] = servicemaster_mikado_options()->getOptionValue('h2_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_google_fonts') !== '-1') {
			$h2_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('h2_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_fontsize') !== '') {
			$h2_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h2_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_lineheight') !== '') {
			$h2_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h2_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_texttransform') !== '') {
			$h2_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('h2_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_fontstyle') !== '') {
			$h2_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('h2_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_fontweight') !== '') {
			$h2_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('h2_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('h2_letterspacing') !== '') {
			$h2_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h2_letterspacing')) . 'px';
		}

		$h2_selector = array(
			'h2'
		);

		if (!empty($h2_styles)) {
			echo servicemaster_mikado_dynamic_css($h2_selector, $h2_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_h2_styles');
}

if (!function_exists('servicemaster_mikado_h3_styles')) {

	function servicemaster_mikado_h3_styles() {

		$h3_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('h3_color') !== '') {
			$h3_styles['color'] = servicemaster_mikado_options()->getOptionValue('h3_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_google_fonts') !== '-1') {
			$h3_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('h3_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_fontsize') !== '') {
			$h3_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h3_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_lineheight') !== '') {
			$h3_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h3_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_texttransform') !== '') {
			$h3_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('h3_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_fontstyle') !== '') {
			$h3_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('h3_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_fontweight') !== '') {
			$h3_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('h3_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('h3_letterspacing') !== '') {
			$h3_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h3_letterspacing')) . 'px';
		}

		$h3_selector = array(
			'h3'
		);

		if (!empty($h3_styles)) {
			echo servicemaster_mikado_dynamic_css($h3_selector, $h3_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_h3_styles');
}

if (!function_exists('servicemaster_mikado_h4_styles')) {

	function servicemaster_mikado_h4_styles() {

		$h4_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('h4_color') !== '') {
			$h4_styles['color'] = servicemaster_mikado_options()->getOptionValue('h4_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_google_fonts') !== '-1') {
			$h4_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('h4_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_fontsize') !== '') {
			$h4_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h4_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_lineheight') !== '') {
			$h4_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h4_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_texttransform') !== '') {
			$h4_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('h4_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_fontstyle') !== '') {
			$h4_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('h4_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_fontweight') !== '') {
			$h4_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('h4_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('h4_letterspacing') !== '') {
			$h4_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h4_letterspacing')) . 'px';
		}

		$h4_selector = array(
			'h4'
		);

		if (!empty($h4_styles)) {
			echo servicemaster_mikado_dynamic_css($h4_selector, $h4_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_h4_styles');
}

if (!function_exists('servicemaster_mikado_h5_styles')) {

	function servicemaster_mikado_h5_styles() {

		$h5_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('h5_color') !== '') {
			$h5_styles['color'] = servicemaster_mikado_options()->getOptionValue('h5_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_google_fonts') !== '-1') {
			$h5_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('h5_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_fontsize') !== '') {
			$h5_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h5_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_lineheight') !== '') {
			$h5_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h5_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_texttransform') !== '') {
			$h5_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('h5_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_fontstyle') !== '') {
			$h5_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('h5_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_fontweight') !== '') {
			$h5_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('h5_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('h5_letterspacing') !== '') {
			$h5_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h5_letterspacing')) . 'px';
		}

		$h5_selector = array(
			'h5'
		);

		if (!empty($h5_styles)) {
			echo servicemaster_mikado_dynamic_css($h5_selector, $h5_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_h5_styles');
}

if (!function_exists('servicemaster_mikado_h6_styles')) {

	function servicemaster_mikado_h6_styles() {

		$h6_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('h6_color') !== '') {
			$h6_styles['color'] = servicemaster_mikado_options()->getOptionValue('h6_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_google_fonts') !== '-1') {
			$h6_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('h6_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_fontsize') !== '') {
			$h6_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h6_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_lineheight') !== '') {
			$h6_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h6_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_texttransform') !== '') {
			$h6_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('h6_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_fontstyle') !== '') {
			$h6_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('h6_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_fontweight') !== '') {
			$h6_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('h6_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('h6_letterspacing') !== '') {
			$h6_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('h6_letterspacing')) . 'px';
		}

		$h6_selector = array(
			'h6'
		);

		if (!empty($h6_styles)) {
			echo servicemaster_mikado_dynamic_css($h6_selector, $h6_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_h6_styles');
}

if (!function_exists('servicemaster_mikado_text_styles')) {

	function servicemaster_mikado_text_styles() {

		$text_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('text_color') !== '') {
			$text_styles['color'] = servicemaster_mikado_options()->getOptionValue('text_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('text_google_fonts') !== '-1') {
			$text_styles['font-family'] = servicemaster_mikado_get_formatted_font_family(servicemaster_mikado_options()->getOptionValue('text_google_fonts'));
		}
		if (servicemaster_mikado_options()->getOptionValue('text_fontsize') !== '') {
			$text_styles['font-size'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('text_fontsize')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('text_lineheight') !== '') {
			$text_styles['line-height'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('text_lineheight')) . 'px';
		}
		if (servicemaster_mikado_options()->getOptionValue('text_texttransform') !== '') {
			$text_styles['text-transform'] = servicemaster_mikado_options()->getOptionValue('text_texttransform');
		}
		if (servicemaster_mikado_options()->getOptionValue('text_fontstyle') !== '') {
			$text_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('text_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('text_fontweight') !== '') {
			$text_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('text_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('text_letterspacing') !== '') {
			$text_styles['letter-spacing'] = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('text_letterspacing')) . 'px';
		}

		$text_selector = array(
			'p'
		);

		if (!empty($text_styles)) {
			echo servicemaster_mikado_dynamic_css($text_selector, $text_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_text_styles');
}

if (!function_exists('servicemaster_mikado_link_styles')) {

	function servicemaster_mikado_link_styles() {

		$link_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('link_color') !== '') {
			$link_styles['color'] = servicemaster_mikado_options()->getOptionValue('link_color');
		}
		if (servicemaster_mikado_options()->getOptionValue('link_fontstyle') !== '') {
			$link_styles['font-style'] = servicemaster_mikado_options()->getOptionValue('link_fontstyle');
		}
		if (servicemaster_mikado_options()->getOptionValue('link_fontweight') !== '') {
			$link_styles['font-weight'] = servicemaster_mikado_options()->getOptionValue('link_fontweight');
		}
		if (servicemaster_mikado_options()->getOptionValue('link_fontdecoration') !== '') {
			$link_styles['text-decoration'] = servicemaster_mikado_options()->getOptionValue('link_fontdecoration');
		}

		$link_selector = array(
			'a',
			'p a'
		);

		if (!empty($link_styles)) {
			echo servicemaster_mikado_dynamic_css($link_selector, $link_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_link_styles');
}

if (!function_exists('servicemaster_mikado_link_hover_styles')) {

	function servicemaster_mikado_link_hover_styles() {

		$link_hover_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('link_hovercolor') !== '') {
			$link_hover_styles['color'] = servicemaster_mikado_options()->getOptionValue('link_hovercolor');
		}
		if (servicemaster_mikado_options()->getOptionValue('link_hover_fontdecoration') !== '') {
			$link_hover_styles['text-decoration'] = servicemaster_mikado_options()->getOptionValue('link_hover_fontdecoration');
		}

		$link_hover_selector = array(
			'a:hover',
			'p a:hover'
		);

		if (!empty($link_hover_styles)) {
			echo servicemaster_mikado_dynamic_css($link_hover_selector, $link_hover_styles);
		}

		$link_heading_hover_styles = array();

		if (servicemaster_mikado_options()->getOptionValue('link_hovercolor') !== '') {
			$link_heading_hover_styles['color'] = servicemaster_mikado_options()->getOptionValue('link_hovercolor');
		}

		$link_heading_hover_selector = array(
			'h1 a:hover',
			'h2 a:hover',
			'h3 a:hover',
			'h4 a:hover',
			'h5 a:hover',
			'h6 a:hover'
		);

		if (!empty($link_heading_hover_styles)) {
			echo servicemaster_mikado_dynamic_css($link_heading_hover_selector, $link_heading_hover_styles);
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_link_hover_styles');
}

if (!function_exists('servicemaster_mikado_smooth_page_transition_styles')) {

	function servicemaster_mikado_smooth_page_transition_styles() {

		$loader_style = array();

		if (servicemaster_mikado_options()->getOptionValue('smooth_pt_bgnd_color') !== '') {
			$loader_style['background-color'] = servicemaster_mikado_options()->getOptionValue('smooth_pt_bgnd_color');
		}

		$loader_selector = array('.mkd-smooth-transition-loader');

		if (!empty($loader_style)) {
			echo servicemaster_mikado_dynamic_css($loader_selector, $loader_style);
		}

		$spinner_style = array();

		if (servicemaster_mikado_options()->getOptionValue('smooth_pt_spinner_color') !== '') {
			$spinner_style['background-color'] = servicemaster_mikado_options()->getOptionValue('smooth_pt_spinner_color');
		}

		$spinner_selectors = array(
			'.mkd-st-loader .pulse',
			'.mkd-st-loader .double_pulse .double-bounce1',
			'.mkd-st-loader .double_pulse .double-bounce2',
			'.mkd-st-loader .cube',
			'.mkd-st-loader .rotating_cubes .cube1',
			'.mkd-st-loader .rotating_cubes .cube2',
			'.mkd-st-loader .stripes > div',
			'.mkd-st-loader .wave > div',
			'.mkd-st-loader .two_rotating_circles .dot1',
			'.mkd-st-loader .two_rotating_circles .dot2',
			'.mkd-st-loader .five_rotating_circles .container1 > div',
			'.mkd-st-loader .five_rotating_circles .container2 > div',
			'.mkd-st-loader .five_rotating_circles .container3 > div',
			'.mkd-st-loader .atom .ball-1:before',
			'.mkd-st-loader .atom .ball-2:before',
			'.mkd-st-loader .atom .ball-3:before',
			'.mkd-st-loader .atom .ball-4:before',
			'.mkd-st-loader .clock .ball:before',
			'.mkd-st-loader .mitosis .ball',
			'.mkd-st-loader .lines .line1',
			'.mkd-st-loader .lines .line2',
			'.mkd-st-loader .lines .line3',
			'.mkd-st-loader .lines .line4',
			'.mkd-st-loader .fussion .ball',
			'.mkd-st-loader .fussion .ball-1',
			'.mkd-st-loader .fussion .ball-2',
			'.mkd-st-loader .fussion .ball-3',
			'.mkd-st-loader .fussion .ball-4',
			'.mkd-st-loader .wave_circles .ball',
			'.mkd-st-loader .pulse_circles .ball'
		);

		if (!empty($spinner_style)) {
			echo servicemaster_mikado_dynamic_css($spinner_selectors, $spinner_style);
		}

		if (servicemaster_mikado_options()->getOptionValue('smooth_pt_spinner_type') == 'svg_spinner') {
			$svg_spinner_style = array();

			if (servicemaster_mikado_options()->getOptionValue('smooth_pt_spinner_color') !== '') {
				$svg_spinner_style['stroke'] = servicemaster_mikado_options()->getOptionValue('smooth_pt_spinner_color');
			}

			if (!empty($svg_spinner_style)) {
				echo servicemaster_mikado_dynamic_css('.mkd-svg-spinner-holder svg>path', $svg_spinner_style);
			}
		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_smooth_page_transition_styles');
}

if (!function_exists('servicemaster_mikado_paspartu_styles')) {

	function servicemaster_mikado_paspartu_styles() {
		$paspartu_enabled = servicemaster_mikado_options()->getOptionValue('enable_paspartu') == 'yes';


		if ($paspartu_enabled) {

			$paspartu_style = array();
			$paspartu_selectors = array(
				'body.mkd-paspartu-enabled .mkd-wrapper-paspartu'
			);

			$paspartu_color = servicemaster_mikado_options()->getOptionValue('paspartu_color');
			$paspartu_size = servicemaster_mikado_options()->getOptionValue('paspartu_size');

			if ($paspartu_color !== '') {
				$paspartu_style['background-color'] = $paspartu_color;
			}

			if ($paspartu_size !== '') {
				$paspartu_style['padding'] = servicemaster_mikado_filter_px($paspartu_size) . 'px';
			}

			if (!empty($paspartu_style)) {
				echo servicemaster_mikado_dynamic_css($paspartu_selectors, $paspartu_style);
			}

		}
	}

	add_action('servicemaster_mikado_style_dynamic', 'servicemaster_mikado_paspartu_styles');
}