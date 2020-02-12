<?php

use ServiceMaster\Modules\Header\Lib\HeaderFactory;

if (!function_exists('servicemaster_mikado_get_header')) {
    /**
     * Loads header HTML based on header type option. Sets all necessary parameters for header
     * and defines servicemaster_mikado_header_type_parameters filter
     */
    function servicemaster_mikado_get_header()
    {

        //will be read from options
        $header_type = servicemaster_mikado_options()->getOptionValue('header_type');
        $header_behavior = servicemaster_mikado_get_meta_field_intersect('header_behaviour', servicemaster_mikado_get_page_id());

        extract(servicemaster_mikado_get_page_options());

        if (HeaderFactory::getInstance()->validHeaderObject()) {
            $parameters = array(
                'hide_logo' => servicemaster_mikado_options()->getOptionValue('hide_logo') == 'yes' ? true : false,
                'show_sticky' => in_array($header_behavior, array(
                    'sticky-header-on-scroll-up',
                    'sticky-header-on-scroll-down-up'
                )) ? true : false,
                'show_fixed_wrapper' => in_array($header_behavior, array('fixed-on-scroll')) ? true : false,
                'menu_area_background_color' => $menu_area_background_color,
                'vertical_header_background_color' => $vertical_header_background_color,
                'vertical_header_opacity' => $vertical_header_opacity,
                'vertical_background_image' => $vertical_background_image,
            );

            $parameters = apply_filters('servicemaster_mikado_header_type_parameters', $parameters, $header_type);

            HeaderFactory::getInstance()->getHeaderObject()->loadTemplate($parameters);
        }
    }
}

if (!function_exists('servicemaster_mikado_get_header_top')) {
    /**
     * Loads header top HTML and sets parameters for it
     */
    function servicemaster_mikado_get_header_top()
    {

        //generate column width class
        switch (servicemaster_mikado_options()->getOptionValue('top_bar_layout')) {
            case ('two-columns'):
                $column_widht_class = 'mkd-' . servicemaster_mikado_options()->getOptionValue('top_bar_two_column_widths');
                break;
            case ('three-columns'):
                $column_widht_class = 'mkd-' . servicemaster_mikado_options()->getOptionValue('top_bar_column_widths');
                break;
        }

        $params = array(
            'column_widths' => $column_widht_class,
            'show_widget_center' => servicemaster_mikado_options()->getOptionValue('top_bar_layout') == 'three-columns' ? true : false,
            'show_header_top' => servicemaster_mikado_is_top_bar_enabled(),
            'show_header_top_background_div' => servicemaster_mikado_get_meta_field_intersect('header_type') == 'header-box' ? true : false,
            'top_bar_in_grid' => servicemaster_mikado_get_meta_field_intersect('top_bar_in_grid') == 'yes' ? true : false,
            'gradient_class' => servicemaster_mikado_get_meta_field_intersect('top_area_gradient_header_box') !== '' ? servicemaster_mikado_get_meta_field_intersect('top_area_gradient_header_box') : ''
        );

        $params = apply_filters('servicemaster_mikado_header_top_params', $params);

        servicemaster_mikado_get_module_template_part('templates/parts/header-top', 'header', '', $params);
    }
}

if (!function_exists('servicemaster_mikado_get_header_top_line')) {
    /**
     * Loads header top line HTML and sets parameters for it
     */
    function servicemaster_mikado_get_header_top_line()
    {

        $id = servicemaster_mikado_get_page_id();
        $colors = array();

        if (get_post_meta($id, 'mkd_top_line_meta', true) !== '') {
            $colors['color1'] = get_post_meta($id, 'mkd_top_line_color_1_meta', true);
            $colors['color2'] = get_post_meta($id, 'mkd_top_line_color_2_meta', true);
            $colors['color3'] = get_post_meta($id, 'mkd_top_line_color_3_meta', true);
            $colors['color4'] = get_post_meta($id, 'mkd_top_line_color_4_meta', true);
        } elseif (servicemaster_mikado_options()->getOptionValue('top_line') == 'yes') {
            $colors['color1'] = servicemaster_mikado_options()->getOptionValue('top_line_color_1');
            $colors['color2'] = servicemaster_mikado_options()->getOptionValue('top_line_color_2');
            $colors['color3'] = servicemaster_mikado_options()->getOptionValue('top_line_color_3');
            $colors['color4'] = servicemaster_mikado_options()->getOptionValue('top_line_color_4');
        }

        $i = 0;
        foreach ($colors as $name => $value) {
            if ($value !== "") {
                $i++;
            }
        }

        $params = array(
            'show_header_top_line' => servicemaster_mikado_get_meta_field_intersect('top_line') == 'yes' ? true : false,
            'number_of_colors' => $i,
            'colors' => $colors
        );

        servicemaster_mikado_get_module_template_part('templates/parts/header-top-line', 'header', '', $params);

    }
}

if (!function_exists('servicemaster_mikado_get_logo')) {
    /**
     * Loads logo HTML
     *
     * @param $slug
     */
    function servicemaster_mikado_get_logo($slug = '')
    {
        $id = servicemaster_mikado_get_page_id();

        if ($slug == 'sticky') {
            $logo_image = servicemaster_mikado_get_meta_field_intersect('logo_image_sticky', $id);
        } else {
            $logo_image = servicemaster_mikado_get_meta_field_intersect('logo_image', $id);
        }

        $logo_image_dark = servicemaster_mikado_get_meta_field_intersect('logo_image_dark', $id);
        $logo_image_light = servicemaster_mikado_get_meta_field_intersect('logo_image_light', $id);


        //get logo image dimensions and set style attribute for image link.
        $logo_dimensions = servicemaster_mikado_get_image_dimensions($logo_image);

        $logo_styles = '';
        $logo_dimensions_attr = array();
        if (is_array($logo_dimensions) && array_key_exists('height', $logo_dimensions)) {
            $logo_height = $logo_dimensions['height'];
            $logo_styles = 'height: ' . intval($logo_height / 2) . 'px;'; //divided with 2 because of retina screens

            if (!empty($logo_dimensions['height']) && $logo_dimensions['width']) {
                $logo_dimensions_attr['height'] = $logo_dimensions['height'];
                $logo_dimensions_attr['width'] = $logo_dimensions['width'];
            }
        }

        $params = array(
            'logo_image' => $logo_image,
            'logo_image_dark' => $logo_image_dark,
            'logo_image_light' => $logo_image_light,
            'logo_styles' => $logo_styles,
            'logo_dimensions_attr' => $logo_dimensions_attr
        );

        servicemaster_mikado_get_module_template_part('templates/parts/logo', 'header', $slug, $params);
    }
}

if (!function_exists('servicemaster_mikado_get_main_menu')) {
    /**
     * Loads main menu HTML
     *
     * @param string $additional_class addition class to pass to template
     */
    function servicemaster_mikado_get_main_menu($additional_class = 'mkd-default-nav')
    {
        servicemaster_mikado_get_module_template_part('templates/parts/navigation', 'header', '', array('additional_class' => $additional_class));
    }
}

if (!function_exists('servicemaster_mikado_get_sticky_menu')) {
    /**
     * Loads sticky menu HTML
     *
     * @param string $additional_class addition class to pass to template
     */
    function servicemaster_mikado_get_sticky_menu($additional_class = 'mkd-default-nav')
    {
        servicemaster_mikado_get_module_template_part('templates/parts/sticky-navigation', 'header', '', array('additional_class' => $additional_class));
    }
}

if (!function_exists('servicemaster_mikado_get_divided_left_main_menu')) {
    /**
     * Loads main menu HTML
     *
     * @param string $additional_class addition class to pass to template
     */
    function servicemaster_mikado_get_divided_left_main_menu($slug = '', $additional_class = 'mkd-default-nav')
    {
        servicemaster_mikado_get_module_template_part('templates/parts/divided-left-navigation', 'header', $slug, array('additional_class' => $additional_class));
    }
}

if (!function_exists('servicemaster_mikado_get_divided_right_main_menu')) {
    /**
     * Loads main menu HTML
     *
     * @param string $additional_class addition class to pass to template
     */
    function servicemaster_mikado_get_divided_right_main_menu($slug = '', $additional_class = 'mkd-default-nav')
    {
        servicemaster_mikado_get_module_template_part('templates/parts/divided-right-navigation', 'header', $slug, array('additional_class' => $additional_class));
    }
}

if (!function_exists('servicemaster_mikado_get_vertical_main_menu')) {
    /**
     * Loads vertical menu HTML
     */
    function servicemaster_mikado_get_vertical_main_menu($slug = '')
    {
        servicemaster_mikado_get_module_template_part('templates/parts/vertical-navigation', 'header', $slug);
    }
}

if (!function_exists('servicemaster_mikado_vertical_haeder_holder_class')) {
    /**
     * return holder class
     */
    function servicemaster_mikado_vertical_haeder_holder_class()
    {
        $holder_class = '';
        $center_content = servicemaster_mikado_get_meta_field_intersect('vertical_header_center_content');
        if ($center_content == 'yes') {
            $holder_class .= 'mkd-vertical-alignment-center';
        } else {
            $holder_class .= 'mkd-vertical-alignment-top';
        }

        return $holder_class;
    }
}

if (!function_exists('servicemaster_mikado_get_sticky_header')) {
    /**
     * Loads sticky header behavior HTML
     */
    function servicemaster_mikado_get_sticky_header($slug = '')
    {
        $id = servicemaster_mikado_get_page_id();

        $menu_area_position = servicemaster_mikado_get_meta_field_intersect('menu_area_position_header_' . $slug, $id);


        $parameters = array(
            'hide_logo' => servicemaster_mikado_options()->getOptionValue('hide_logo') == 'yes' ? true : false,
            'sticky_header_in_grid' => servicemaster_mikado_get_meta_field_intersect('sticky_header_in_grid', $id) == 'yes' ? true : false,
            'menu_area_position' => !empty($menu_area_position) ? $menu_area_position : 'left'
        );

        servicemaster_mikado_get_module_template_part('templates/behaviors/sticky-header', 'header', $slug, $parameters);
    }
}

if (!function_exists('servicemaster_mikado_get_mobile_header')) {
    /**
     * Loads mobile header HTML only if responsiveness is enabled
     */
    function servicemaster_mikado_get_mobile_header()
    {
        if (servicemaster_mikado_is_responsive_on()) {
            $header_type = servicemaster_mikado_options()->getOptionValue('header_type');

            //this could be read from theme options
            $mobile_header_type = 'mobile-header';

            $parameters = array(
                'show_logo' => servicemaster_mikado_options()->getOptionValue('hide_logo') == 'yes' ? false : true,
                'menu_opener_icon' => servicemaster_mikado_icon_collections()->getMobileMenuIcon(servicemaster_mikado_options()->getOptionValue('mobile_icon_pack'), true),
                'show_navigation_opener' => has_nav_menu('main-navigation')
            );

            servicemaster_mikado_get_module_template_part('templates/types/' . $mobile_header_type, 'header', $header_type, $parameters);
        }
    }
}

if (!function_exists('servicemaster_mikado_get_mobile_logo')) {
    /**
     * Loads mobile logo HTML. It checks if mobile logo image is set and uses that, else takes normal logo image
     *
     * @param string $slug
     */
    function servicemaster_mikado_get_mobile_logo($slug = '')
    {
        $id = servicemaster_mikado_get_page_id();

        //check if mobile logo has been set and use that, else use normal logo
        if (servicemaster_mikado_get_meta_field_intersect('logo_image_mobile', $id)) {
            $logo_image = servicemaster_mikado_get_meta_field_intersect('logo_image_mobile', $id);
        } else {
            $logo_image = servicemaster_mikado_get_meta_field_intersect('logo_image', $id);
        }

        //get logo image dimensions and set style attribute for image link.
        $logo_dimensions = servicemaster_mikado_get_image_dimensions($logo_image);

        $logo_height = '';
        $logo_styles = '';
        $logo_dimensions_attr = array();
        if (is_array($logo_dimensions) && array_key_exists('height', $logo_dimensions)) {
            $logo_height = $logo_dimensions['height'];
            $logo_styles = 'height: ' . intval($logo_height / 2) . 'px'; //divided with 2 because of retina screens

            if (!empty($logo_dimensions['height']) && $logo_dimensions['width']) {
                $logo_dimensions_attr['height'] = $logo_dimensions['height'];
                $logo_dimensions_attr['width'] = $logo_dimensions['width'];
            }
        }

        //set parameters for logo
        $parameters = array(
            'logo_image' => $logo_image,
            'logo_dimensions' => $logo_dimensions,
            'logo_height' => $logo_height,
            'logo_styles' => $logo_styles,
            'logo_dimensions_attr' => $logo_dimensions_attr
        );

        servicemaster_mikado_get_module_template_part('templates/parts/mobile-logo', 'header', $slug, $parameters);
    }
}

if (!function_exists('servicemaster_mikado_get_mobile_nav')) {
    /**
     * Loads mobile navigation HTML
     */
    function servicemaster_mikado_get_mobile_nav()
    {

        $slug = servicemaster_mikado_options()->getOptionValue('header_type');

        servicemaster_mikado_get_module_template_part('templates/parts/mobile-navigation', 'header', $slug);
    }
}

if (!function_exists('servicemaster_mikado_get_page_options')) {
    /**
     * Gets options from page
     */
    function servicemaster_mikado_get_page_options()
    {
        $id = servicemaster_mikado_get_page_id();

        $page_options = array();
        $menu_area_background_color_rgba = '';
        $menu_area_background_color = '';
        $menu_area_background_transparency = '';
        $vertical_header_background_color = '';
        $vertical_header_opacity = '';
        $vertical_background_image = '';

        $header_type = servicemaster_mikado_get_meta_field_intersect('header_type', $id);
        switch ($header_type) {
            case 'header-standard':

                if (($meta_temp = get_post_meta($id, 'mkd_menu_area_background_color_header_standard_meta', true)) != '') {
                    $menu_area_background_color = $meta_temp;
                }

                if (($meta_temp = get_post_meta($id, 'mkd_menu_area_background_transparency_header_standard_meta', true)) != '') {
                    $menu_area_background_transparency = $meta_temp;
                }

                if (servicemaster_mikado_rgba_color($menu_area_background_color, $menu_area_background_transparency) !== null) {
                    $menu_area_background_color_rgba = 'background-color:' . servicemaster_mikado_rgba_color($menu_area_background_color, $menu_area_background_transparency);
                }

                break;

            case 'header-vertical':
                if (($meta_temp = get_post_meta($id, 'mkd_vertical_header_background_color_meta', true)) !== '') {
                    $vertical_header_background_color = 'background-color:' . $meta_temp;
                }

                if (get_post_meta($id, 'mkd_disable_vertical_header_background_image_meta', true) == 'yes') {
                    $vertical_background_image = 'background-image:none';
                } elseif (($meta_temp = get_post_meta($id, 'mkd_vertical_header_background_image_meta', true)) !== '') {
                    $vertical_background_image = 'background-image:url(' . $meta_temp . ')';
                }

                break;
            case 'header-vertical-compact':
                if (($meta_temp = get_post_meta($id, 'mkd_vertical_compact_header_background_color_meta', true)) !== '') {
                    $vertical_header_background_color = 'background-color:' . $meta_temp;
                }

                break;
        }

        $page_options['menu_area_background_color'] = $menu_area_background_color_rgba;
        $page_options['vertical_header_background_color'] = $vertical_header_background_color;
        $page_options['vertical_header_opacity'] = $vertical_header_opacity;
        $page_options['vertical_background_image'] = $vertical_background_image;

        return $page_options;
    }
}