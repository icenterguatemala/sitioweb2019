<?php

if(!function_exists('servicemaster_mikado_get_footer_classes')) {
    /**
     * Return all footer classes
     *
     * @param $page_id
     *
     * @return string|void
     */
    function servicemaster_mikado_get_footer_classes($page_id) {

        $footer_classes       = '';
        $footer_classes_array = array('mkd-page-footer');

        //is uncovering footer option set in theme options?
        if(servicemaster_mikado_get_meta_field_intersect('uncovering_footer') == 'yes') {
            $footer_classes_array[] = 'mkd-footer-uncover';
        }

        if(get_post_meta($page_id, 'mkd_disable_footer_meta', true) == 'yes') {
            $footer_classes_array[] = 'mkd-disable-footer';
        }

        //is some class added to footer classes array?
        if(is_array($footer_classes_array) && count($footer_classes_array)) {
            //concat all classes and prefix it with class attribute
            $footer_classes = esc_attr(implode(' ', $footer_classes_array));
        }

        return $footer_classes;

    }

}

if(!function_exists('servicemaster_mikado_get_footer_bottom_border')) {
    /**
     * Return HTML for footer bottom border top
     *
     * @return string
     */
    function servicemaster_mikado_get_footer_bottom_border() {

        $footer_bottom_border = '';

        if(servicemaster_mikado_options()->getOptionValue('footer_bottom_border_color')) {
            if(servicemaster_mikado_options()->getOptionValue('footer_bottom_border_width') !== '') {
                $footer_border_height = servicemaster_mikado_options()->getOptionValue('footer_bottom_border_width');
            } else {
                $footer_border_height = '1';
            }

            $footer_bottom_border = 'height: '.esc_attr($footer_border_height).'px; background-color: '.esc_attr(servicemaster_mikado_options()->getOptionValue('footer_bottom_border_color')).';';
        }

        return $footer_bottom_border;
    }
}


if(!function_exists('servicemaster_mikado_get_footer_bottom_bottom_border')) {
    /**
     * Return HTML for footer bottom border bottom
     *
     * @return string
     */
    function servicemaster_mikado_get_footer_bottom_bottom_border() {

        $footer_bottom_border = '';

        if(servicemaster_mikado_options()->getOptionValue('footer_bottom_border_bottom_color')) {
            if(servicemaster_mikado_options()->getOptionValue('footer_bottom_border_bottom_width') !== '') {
                $footer_border_height = servicemaster_mikado_options()->getOptionValue('footer_bottom_border_bottom_width');
            } else {
                $footer_border_height = '1';
            }

            $footer_bottom_border = 'height: '.esc_attr($footer_border_height).'px; background-color: '.esc_attr(servicemaster_mikado_options()->getOptionValue('footer_bottom_border_bottom_color')).';';
        }

        return $footer_bottom_border;
    }
}

if(!function_exists('servicemaster_mikado_footer_top_classes')) {
    /**
     * Return classes for footer top
     *
     * @return string
     */
    function servicemaster_mikado_footer_top_classes() {

        $footer_top_classes = array();

        if(servicemaster_mikado_get_meta_field_intersect('footer_in_grid') === 'no') {
            $footer_top_classes[] = 'mkd-footer-top-full';
        }

        //footer aligment
        $footer_alignment = servicemaster_mikado_get_meta_field_intersect('footer_top_columns_alignment');
        if($footer_alignment !== '') {
            $footer_top_classes[] = 'mkd-footer-top-aligment-'.$footer_alignment;
        }


        return implode(' ', $footer_top_classes);
    }

}

if(!function_exists('servicemaster_mikado_footer_body_classes')) {
    /**
     * @param $classes
     *
     * @return array
     */
    function servicemaster_mikado_footer_body_classes($classes) {
        $background_image     = servicemaster_mikado_get_meta_field_intersect('footer_background_image', servicemaster_mikado_get_page_id());
        $enable_image_on_page = get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_enable_footer_image_meta', true);
        $is_footer_full_width = servicemaster_mikado_get_meta_field_intersect('footer_in_grid') !== 'yes';

        if($background_image !== '' && $enable_image_on_page !== 'yes') {
            $classes[] = 'mkd-footer-with-bg-image';
        }

        if($is_footer_full_width) {
            $classes[] = 'mkd-fullwidth-footer';
        }

        return $classes;
    }

    add_filter('body_class', 'servicemaster_mikado_footer_body_classes');
}


if(!function_exists('servicemaster_mikado_footer_page_styles')) {
    /**
     * @param $style
     *
     * @return array
     */
    function servicemaster_mikado_footer_page_styles($style) {
        $background_image = get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_footer_background_image_meta', true);
        $background_color = get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_footer_background_color_meta', true);
        $background_color_transparency = get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_footer_background_color_transparency_meta', true);
        $page_prefix      = servicemaster_mikado_get_unique_page_class();

        $current_style = '';

        if($background_image !== '') {
            $footer_bg_image_style_array['background-image'] = 'url('.$background_image.')';

            $current_style .= servicemaster_mikado_dynamic_css('body.mkd-footer-with-bg-image'.$page_prefix.' .mkd-page-footer', $footer_bg_image_style_array);
        }
        if($background_color !== ''){
            $transparency = 1;
            if($background_color_transparency !== ''){
                $transparency = $background_color_transparency;
            }
            $footer_bg_color_selectors = array(
                'footer .mkd-footer-top-holder',
                'footer .mkd-footer-bottom-holder'
            );
            $footer_bg_color_style = array(
                'background-color' => servicemaster_mikado_rgba_color($background_color, $transparency)
            );

            $current_style .= servicemaster_mikado_dynamic_css($footer_bg_color_selectors, $footer_bg_color_style);
        }

        $style = $current_style . $style;

        return $style;
    }

    add_filter('servicemaster_mikado_add_page_custom_style', 'servicemaster_mikado_footer_page_styles');
}