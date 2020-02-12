<?php

if(!function_exists('servicemaster_mikado_header_register_main_navigation')) {
	/**
	 * Registers main navigation
	 */
	function servicemaster_mikado_header_register_main_navigation() {
		register_nav_menus(
			array(
				'main-navigation' => esc_html__('Main Navigation', 'servicemaster'),
                'vertical-navigation' => esc_html__('Vertical Navigation', 'servicemaster'),
                'vertical-compact-navigation' => esc_html__('Vertical Compact Navigation', 'servicemaster'),
                'divided-left-navigation' => esc_html__('Divided Left Navigation', 'servicemaster'),
                'divided-right-navigation' => esc_html__('Divided Right Navigation', 'servicemaster'),
			)
		);
	}

	add_action('after_setup_theme', 'servicemaster_mikado_header_register_main_navigation');
}

if(!function_exists('servicemaster_mikado_is_top_bar_transparent')) {
	/**
	 * Checks if top bar is transparent or not
	 *
	 * @return bool
	 */
	function servicemaster_mikado_is_top_bar_transparent() {
		$top_bar_enabled = servicemaster_mikado_is_top_bar_enabled();

		$top_bar_bg_color     = servicemaster_mikado_get_meta_field_intersect('top_bar_background_color');
		$top_bar_transparency = servicemaster_mikado_get_meta_field_intersect('top_bar_background_transparency');

		if($top_bar_enabled && $top_bar_bg_color !== '' && $top_bar_transparency !== '') {
			return $top_bar_transparency >= 0 && $top_bar_transparency < 1;
		}

		return false;
	}
}

if(!function_exists('servicemaster_mikado_is_top_bar_completely_transparent')) {
	function servicemaster_mikado_is_top_bar_completely_transparent() {
		$top_bar_enabled = servicemaster_mikado_is_top_bar_enabled();

		$top_bar_bg_color     = servicemaster_mikado_options()->getOptionValue('top_bar_background_color');
		$top_bar_transparency = servicemaster_mikado_options()->getOptionValue('top_bar_background_transparency');

		if($top_bar_enabled && $top_bar_bg_color !== '' && $top_bar_transparency !== '') {
			return $top_bar_transparency === '0';
		}

		return false;
	}
}

if(!function_exists('servicemaster_mikado_is_top_bar_enabled')) {
	function servicemaster_mikado_is_top_bar_enabled() {
		$id = servicemaster_mikado_get_page_id();

		$top_bar_enabled = servicemaster_mikado_get_meta_field_intersect('top_bar', $id) === 'yes';
        if(servicemaster_mikado_get_meta_field_intersect('header_type', $id) === 'header-box'){
            $top_bar_enabled = true;
        }

		return $top_bar_enabled;
	}
}

if(!function_exists('servicemaster_mikado_get_top_bar_height')) {
    /**
     * Returns top bar height
     *
     * @return bool|int|void
     */
    function servicemaster_mikado_get_top_bar_height() {
        if(servicemaster_mikado_is_top_bar_enabled()) {
            $top_bar_height = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('top_bar_height'));
            $header_type = servicemaster_mikado_get_meta_field_intersect('header_type');
            switch ($header_type){
                case 'header-box':

                    $def_top_bar_height = 38; //10 is added since this header type has 10px larger height

                    break;
                default:
                    $def_top_bar_height = 38;
                    break;
            }
            return $top_bar_height !== '' ? intval($top_bar_height) : $def_top_bar_height;
        }

        return 0;
    }
}

if(!function_exists('servicemaster_mikado_get_top_bar_background_height')) {
    /**
     * Returns top bar background height
     *
     * @return bool|int|void
     */
    function servicemaster_mikado_get_top_bar_background_height() {

        $top_bar_height = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('top_bar_height'));
        $header_height = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('menu_area_height_header_box'));

        if($top_bar_height == ''){
            $top_bar_height = 38;
        }
        if($header_height == ''){
            $header_height = 100;
        }

        $top_bar_background_height = round($top_bar_height) + round($header_height / 4);
        return $top_bar_background_height;
    }
}

if(!function_exists('servicemaster_mikado_get_sticky_header_height')) {
	/**
	 * Returns top sticky header height
	 *
	 * @return bool|int|void
	 */
	function servicemaster_mikado_get_sticky_header_height() {
		//sticky menu height, needed only for sticky header on scroll up
		if(servicemaster_mikado_options()->getOptionValue('header_type') !== 'header-vertical' &&
		   in_array(servicemaster_mikado_options()->getOptionValue('header_behaviour'), array('sticky-header-on-scroll-up'))
		) {

			$sticky_header_height = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('sticky_header_height'));

			return $sticky_header_height !== '' ? intval($sticky_header_height) : 60;
		}

		return 0;

	}
}

if(!function_exists('servicemaster_mikado_get_sticky_header_height_of_complete_transparency')) {
	/**
	 * Returns top sticky header height it is fully transparent. used in anchor logic
	 *
	 * @return bool|int|void
	 */
	function servicemaster_mikado_get_sticky_header_height_of_complete_transparency() {

		if(servicemaster_mikado_options()->getOptionValue('header_type') !== 'header-vertical') {
			if(servicemaster_mikado_options()->getOptionValue('sticky_header_transparency') === '0') {
				$stickyHeaderTransparent = servicemaster_mikado_options()->getOptionValue('sticky_header_grid_background_color') !== '' &&
				                           servicemaster_mikado_options()->getOptionValue('sticky_header_grid_transparency') === '0';
			} else {
				$stickyHeaderTransparent = servicemaster_mikado_options()->getOptionValue('sticky_header_background_color') !== '' &&
				                           servicemaster_mikado_options()->getOptionValue('sticky_header_transparency') === '0';
			}

			if($stickyHeaderTransparent) {
				return 0;
			} else {
				$sticky_header_height = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('sticky_header_height'));

				return $sticky_header_height !== '' ? intval($sticky_header_height) : 60;
			}
		}

		return 0;
	}
}

if(!function_exists('servicemaster_mikado_get_sticky_scroll_amount')) {
	/**
	 * Returns top sticky scroll amount
	 *
	 * @return bool|int|void
	 */
	function servicemaster_mikado_get_sticky_scroll_amount() {

		//sticky menu scroll amount
		if(servicemaster_mikado_options()->getOptionValue('header_type') !== 'header-vertical' &&
		   in_array(servicemaster_mikado_options()->getOptionValue('header_behaviour'), array(
			   'sticky-header-on-scroll-up',
			   'sticky-header-on-scroll-down-up'
		   ))
		) {

			$sticky_scroll_amount = servicemaster_mikado_filter_px(servicemaster_mikado_get_meta_field_intersect('scroll_amount_for_sticky'));

			return $sticky_scroll_amount !== '' ? intval($sticky_scroll_amount) : 0;
		}

		return 0;

	}
}