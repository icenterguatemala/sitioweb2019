<?php

if (!function_exists('servicemaster_mikado_header_class')) {
    /**
     * Function that adds class to header based on theme options
     *
     * @param array array of classes from main filter
     *
     * @return array array of classes with added header class
     */
    function servicemaster_mikado_header_class($classes)
    {
        $header_type = servicemaster_mikado_get_meta_field_intersect('header_type', servicemaster_mikado_get_page_id());

        $classes[] = 'mkd-' . $header_type;

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_header_class');
}

if (!function_exists('servicemaster_mikado_header_behaviour_class')) {
    /**
     * Function that adds behaviour class to header based on theme options
     *
     * @param array array of classes from main filter
     *
     * @return array array of classes with added behaviour class
     */
    function servicemaster_mikado_header_behaviour_class($classes)
    {
        $id = servicemaster_mikado_get_page_id();

        $classes[] = 'mkd-' . servicemaster_mikado_get_meta_field_intersect('header_behaviour', $id);

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_header_behaviour_class');
}

if (!function_exists('servicemaster_mikado_mobile_header_class')) {
    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_mobile_header_class($classes)
    {
        $classes[] = 'mkd-default-mobile-header';

        $classes[] = 'mkd-sticky-up-mobile-header';

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_mobile_header_class');
}

if (!function_exists('servicemaster_mikado_header_class_first_level_bg_color')) {
    /**
     * Function that adds first level menu background color class to header tag
     *
     * @param array array of classes from main filter
     *
     * @return array array of classes with added first level menu background color class
     */
    function servicemaster_mikado_header_class_first_level_bg_color($classes)
    {

        //check if first level hover background color is set
        if (servicemaster_mikado_options()->getOptionValue('menu_hover_background_color') !== '') {
            $classes[] = 'mkd-menu-item-first-level-bg-color';
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_header_class_first_level_bg_color');
}

if (!function_exists('servicemaster_mikado_menu_dropdown_appearance')) {
    /**
     * Function that adds menu dropdown appearance class to body tag
     *
     * @param array array of classes from main filter
     *
     * @return array array of classes with added menu dropdown appearance class
     */
    function servicemaster_mikado_menu_dropdown_appearance($classes)
    {

        if (servicemaster_mikado_options()->getOptionValue('menu_dropdown_appearance') !== 'default') {
            $classes[] = 'mkd-' . servicemaster_mikado_options()->getOptionValue('menu_dropdown_appearance');
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_menu_dropdown_appearance');
}

if (!function_exists('servicemaster_mikado_header_skin_class')) {

    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_header_skin_class($classes)
    {

        $id = servicemaster_mikado_get_page_id();

        if (($meta_temp = get_post_meta($id, 'mkd_header_style_meta', true)) !== '') {
            $classes[] = 'mkd-' . $meta_temp;
        } else if (servicemaster_mikado_options()->getOptionValue('header_style') !== '') {
            $classes[] = 'mkd-' . servicemaster_mikado_options()->getOptionValue('header_style');
        }

        return $classes;

    }

    add_filter('body_class', 'servicemaster_mikado_header_skin_class');

}

if (!function_exists('servicemaster_mikado_header_scroll_style_class')) {

    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_header_scroll_style_class($classes)
    {

        if (servicemaster_mikado_get_meta_field_intersect('enable_header_style_on_scroll') == 'yes') {
            $classes[] = 'mkd-header-style-on-scroll';
        }

        return $classes;

    }

    add_filter('body_class', 'servicemaster_mikado_header_scroll_style_class');

}

if (!function_exists('servicemaster_mikado_header_global_js_var')) {
    /**
     * @param $global_variables
     *
     * @return mixed
     */
    function servicemaster_mikado_header_global_js_var($global_variables)
    {

        $global_variables['mkdTopBarHeight'] = servicemaster_mikado_get_top_bar_height();
        $global_variables['mkdStickyHeaderHeight'] = servicemaster_mikado_get_sticky_header_height();
        $global_variables['mkdStickyHeaderTransparencyHeight'] = servicemaster_mikado_get_sticky_header_height_of_complete_transparency();

        return $global_variables;
    }

    add_filter('servicemaster_mikado_js_global_variables', 'servicemaster_mikado_header_global_js_var');
}

if (!function_exists('servicemaster_mikado_header_per_page_js_var')) {
    /**
     * @param $perPageVars
     *
     * @return mixed
     */
    function servicemaster_mikado_header_per_page_js_var($perPageVars)
    {
        $id = servicemaster_mikado_get_page_id();

        $perPageVars['mkdStickyScrollAmount'] = servicemaster_mikado_get_sticky_scroll_amount();
        $perPageVars['mkdStickyScrollAmountFullScreen'] = get_post_meta($id, 'mkd_scroll_amount_for_sticky_fullscreen_meta', true) === 'yes';

        return $perPageVars;
    }

    add_filter('servicemaster_mikado_per_page_js_vars', 'servicemaster_mikado_header_per_page_js_var');
}

if (!function_exists('servicemaster_mikado_full_width_wide_menu_class')) {
    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_full_width_wide_menu_class($classes)
    {
        if (servicemaster_mikado_get_meta_field_intersect('enable_wide_menu_background') === 'yes') {
            $classes[] = 'mkd-full-width-wide-menu';
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_full_width_wide_menu_class');
}

if (!function_exists('servicemaster_mikado_header_bottom_border_class')) {
    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_header_bottom_border_class($classes)
    {
        $id = servicemaster_mikado_get_page_id();

        $disable_border = get_post_meta($id, 'mkd_menu_area_bottom_border_disable_header_standard_meta', true) == 'yes';
        if ($disable_border) {
            $classes[] = 'mkd-header-standard-border-disable';
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_header_bottom_border_class');
}

if (!function_exists('servicemaster_mikado_header_bottom_shadow_class')) {
    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_header_bottom_shadow_class($classes)
    {
        $id = servicemaster_mikado_get_page_id();
        $header_type = servicemaster_mikado_get_meta_field_intersect('header_type', $id);
        switch ($header_type) {
            case 'header-standard':
                $disable_shadow_standard = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_standard', $id) == 'no';
                if ($disable_shadow_standard) {
                    $classes[] = 'mkd-header-standard-shadow-disable';
                }

                $disable_grid_shadow_standard = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_shadow_header_standard', $id) == 'no';
                if ($disable_grid_shadow_standard) {
                    $classes[] = 'mkd-header-standard-in-grid-shadow-disable';
                }
                break;
            case 'header-standard-extended':
                $disable_logo_border_standard_extended = servicemaster_mikado_get_meta_field_intersect('logo_area_border_header_standard_extended', $id) == 'no';
                if ($disable_logo_border_standard_extended) {
                    $classes[] = 'mkd-header-standard-extended-logo-border-disable';
                }

                $disable_menu_shadow_standard_extended = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_standard_extended', $id) == 'no';
                if ($disable_menu_shadow_standard_extended) {
                    $classes[] = 'mkd-header-standard-extended-menu-shadow-disable';
                }

                $disable_logo_grid_border_standard_extended = servicemaster_mikado_get_meta_field_intersect('logo_area_in_grid_border_header_standard_extended', $id) == 'no';
                if ($disable_logo_grid_border_standard_extended) {
                    $classes[] = 'mkd-header-standard-extended-logo-in-grid-border-disable';
                }

                $disable_menu_grid_shadow_standard_extended = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_shadow_header_standard_extended', $id) == 'no';
                if ($disable_menu_grid_shadow_standard_extended) {
                    $classes[] = 'mkd-header-standard-extended-menu-in-grid-shadow-disable';
                }
                break;
            case 'header-box':
                break;
            case 'header-minimal':
                $disable_shadow_minimal = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_minimal', $id) == 'no';
                if ($disable_shadow_minimal) {
                    $classes[] = 'mkd-header-minimal-shadow-disable';
                }

                $disable_grid_shadow_minimal = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_shadow_header_minimal', $id) == 'no';
                if ($disable_grid_shadow_minimal) {
                    $classes[] = 'mkd-header-minimal-in-grid-shadow-disable';
                }
                break;
            case 'header-divided':
                $disable_shadow_divided = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_divided', $id) == 'no';
                if ($disable_shadow_divided) {
                    $classes[] = 'mkd-header-divided-shadow-disable';
                }

                $disable_grid_shadow_divided = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_shadow_header_divided', $id) == 'no';
                if ($disable_grid_shadow_divided) {
                    $classes[] = 'mkd-header-divided-in-grid-shadow-disable';
                }
                break;
            case 'header-centered':
                $disable_logo_border_centered = servicemaster_mikado_get_meta_field_intersect('logo_area_border_header_centered', $id) == 'no';
                if ($disable_logo_border_centered) {
                    $classes[] = 'mkd-header-centered-logo-border-disable';
                }

                $disable_menu_shadow_centered = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_centered', $id) == 'no';
                if ($disable_menu_shadow_centered) {
                    $classes[] = 'mkd-header-centered-menu-shadow-disable';
                }

                $disable_logo_grid_border_centered = servicemaster_mikado_get_meta_field_intersect('logo_area_in_grid_border_header_centered', $id) == 'no';
                if ($disable_logo_grid_border_centered) {
                    $classes[] = 'mkd-header-centered-logo-in-grid-border-disable';
                }

                $disable_menu_grid_border_centered = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_border_header_centered', $id) == 'no';
                if ($disable_menu_grid_border_centered) {
                    $classes[] = 'mkd-header-centered-menu-in-grid-border-disable';
                }
                break;
            case 'header-tabbed':
                $disable_shadow_tabbed = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_tabbed', $id) == 'no';
                if ($disable_shadow_tabbed) {
                    $classes[] = 'mkd-header-tabbed-shadow-disable';
                }
                break;
            case 'header-vertical':
                $disable_shadow_vertical = servicemaster_mikado_get_meta_field_intersect('vertical_header_shadow', $id) == 'no';
                if ($disable_shadow_vertical) {
                    $classes[] = 'mkd-header-vertical-shadow-disable';
                }
                break;
            case 'header-vertical-compact':
                $disable_shadow_vertical_compact = servicemaster_mikado_get_meta_field_intersect('vertical_compact_header_shadow', $id) == 'no';
                if ($disable_shadow_vertical_compact) {
                    $classes[] = 'mkd-header-vertical-compact-shadow-disable';
                }
                break;
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_header_bottom_shadow_class');
}

if (!function_exists('servicemaster_mikado_get_top_bar_styles')) {
    /**
     * Sets per page styles for header top bar
     *
     * @param $styles
     *
     * @return array
     */
    function servicemaster_mikado_get_top_bar_styles($styles)
    {
        $id = servicemaster_mikado_get_page_id();
        $class_prefix = servicemaster_mikado_get_unique_page_class();
        $top_bar_style = array();

        $current_style = '';

        $top_bar_bg_color = get_post_meta($id, 'mkd_top_bar_background_color_meta', true);

        $top_bar_selector = array(
            $class_prefix . ' .mkd-top-bar',
            $class_prefix . ' .mkd-top-bar-background'
        );

        if ($top_bar_bg_color !== '') {
            $top_bar_transparency = get_post_meta($id, 'mkd_top_bar_background_transparency_meta', true);
            if ($top_bar_transparency === '') {
                $top_bar_transparency = 1;
            }

            $top_bar_style['background-color'] = servicemaster_mikado_rgba_color($top_bar_bg_color, $top_bar_transparency);
        }

        $current_style .= servicemaster_mikado_dynamic_css($top_bar_selector, $top_bar_style);

        $styles = $current_style . $styles;

        return $styles;
    }

    add_filter('servicemaster_mikado_add_page_custom_style', 'servicemaster_mikado_get_top_bar_styles');
}

if (!function_exists('servicemaster_mikado_get_main_menu_styles')) {
    /**
     * Sets per page styles for header top bar
     *
     * @param $style
     *
     * @return array
     */
    function servicemaster_mikado_get_main_menu_styles($style)
    {
        $id = servicemaster_mikado_get_page_id();
        $class_prefix = servicemaster_mikado_get_unique_page_class();

        $current_style = '';


        //Main Menu COLOR
        $main_menu_color_style = array();

        $main_menu_color = get_post_meta($id, 'mkd_menu_color_meta', true);

        $main_menu_color_selector = array(
            $class_prefix . ' .mkd-main-menu > ul > li > a',
            $class_prefix . ' .mkd-page-header #lang_sel > ul > li > a',
            $class_prefix . ' .mkd-page-header #lang_sel_click > ul > li > a',
            $class_prefix . ' .mkd-page-header #lang_sel ul > li:hover > a',
        );

        if ($main_menu_color !== '') {
            $main_menu_color_style['color'] = $main_menu_color;
        }

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_color_selector, $main_menu_color_style);

        //Main Menu HOVER COLOR
        $main_menu_hovercolor_style = array();

        $main_menu_hovercolor = get_post_meta($id, 'mkd_menu_hovercolor_meta', true);

        $main_menu_hover_color_selector = array(
            $class_prefix . ' .mkd-main-menu > ul > li:hover > a',
            $class_prefix . ' .mkd-main-menu > ul > li.mkd-active-item:hover > a',
            $class_prefix . ':not(.mkd-menu-item-first-level-bg-color) .mkd-main-menu > ul > li:hover > a',
            $class_prefix . ':not(.mkd-menu-item-first-level-bg-color) .mkd-main-menu > ul > li.mkd-active-item:hover > a',
            $class_prefix . ' .mkd-page-header #lang_sel ul li a:hover',
            $class_prefix . ' .mkd-page-header #lang_sel_click > ul > li a:hover'
        );

        if ($main_menu_hovercolor !== '') {
            $main_menu_hovercolor_style['color'] = $main_menu_hovercolor;
        }

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_hover_color_selector, $main_menu_hovercolor_style);

        //Main Menu ACTIVE COLOR
        $main_menu_activecolor_style = array();

        $main_menu_activecolor = get_post_meta($id, 'mkd_menu_activecolor_meta', true);

        $main_menu_active_color_selector = array(
            $class_prefix . ' .mkd-main-menu > ul > li.mkd-active-item > a',
            $class_prefix . ':not(.mkd-menu-item-first-level-bg-color) .mkd-main-menu > ul > li.mkd-active-item > a',
        );

        if ($main_menu_activecolor !== '') {
            $main_menu_activecolor_style['color'] = $main_menu_activecolor;
        }

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_active_color_selector, $main_menu_activecolor_style);

        //Main Menu BACKGROUND TEXT COLOR
        $main_menu_text_background_color_style = array();

        $main_menu_text_background_color = get_post_meta($id, 'mkd_menu_text_background_color_meta', true);

        $main_menu_text_background_color_selector = array(
            $class_prefix . ' .mkd-main-menu > ul > li > a span.item_inner',
            $class_prefix . ' .mkd-page-header #lang_sel .lang_sel_sel',
            $class_prefix . ' .mkd-top-bar #lang_sel .lang_sel_sel'
        );

        if ($main_menu_text_background_color !== '') {
            $main_menu_text_background_color_style['background-color'] = $main_menu_text_background_color;
        }

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_text_background_color_selector, $main_menu_text_background_color_style);

        //Main Menu HOVER BACKGROUND TEXT COLOR
        $main_menu_hover_background_color_style = array();

        $main_menu_hover_background_color = get_post_meta($id, 'mkd_menu_hover_background_color_meta', true);

        $main_menu_hover_background_color_selector = array(
            $class_prefix . ' .mkd-main-menu > ul > li:hover > a span.item_inner',
            $class_prefix . ' .mkd-main-menu > ul > li.mkd-active-item:hover > a span.item_inner',
            $class_prefix . ' .mkd-page-header #lang_sel li:hover .lang_sel_sel'
        );

        if ($main_menu_hover_background_color !== '') {
            $main_menu_hover_background_color_style['background-color'] = $main_menu_hover_background_color;
        }

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_hover_background_color_selector, $main_menu_hover_background_color_style);

        //Main Menu ACTIVE BACKGROUND TEXT COLOR
        $main_menu_active_background_color_style = array();

        $main_menu_active_background_color = get_post_meta($id, 'mkd_menu_active_background_color_meta', true);

        $main_menu_active_background_color_selector = array(
            $class_prefix . ' .mkd-main-menu > ul > li.mkd-active-item > a span.item_inner'
        );

        if ($main_menu_active_background_color !== '') {
            $main_menu_active_background_color_style['background-color'] = $main_menu_active_background_color;
        }

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_active_background_color_selector, $main_menu_active_background_color_style);

        $style = $current_style . $style;

        return $style;
    }

    add_filter('servicemaster_mikado_add_page_custom_style', 'servicemaster_mikado_get_main_menu_styles');
}

if (!function_exists('servicemaster_mikado_top_bar_skin_class')) {
    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_top_bar_skin_class($classes)
    {
        $id = servicemaster_mikado_get_page_id();
        $top_bar_skin = get_post_meta($id, 'mkd_top_bar_skin_meta', true);

        if ($top_bar_skin !== '') {
            $classes[] = 'mkd-top-bar-' . $top_bar_skin;
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_top_bar_skin_class');
}