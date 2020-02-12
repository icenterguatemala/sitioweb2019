<?php

if(!function_exists('servicemaster_mikado_search_body_class')) {
    /**
     * Function that adds body classes for different search types
     *
     * @param $classes array original array of body classes
     *
     * @return array modified array of classes
     */
    function servicemaster_mikado_search_body_class($classes) {

        if(is_active_widget(false, false, 'mkd_search_opener')) {

            $classes[] = 'mkd-'.servicemaster_mikado_options()->getOptionValue('search_type');

            if(servicemaster_mikado_options()->getOptionValue('search_type') == 'fullscreen-search') {

                $is_fullscreen_bg_image_set = servicemaster_mikado_options()->getOptionValue('fullscreen_search_background_image') !== '';

                if($is_fullscreen_bg_image_set) {
                    $classes[] = 'mkd-fullscreen-search-with-bg-image';
                }

                $classes[] = 'mkd-search-fade';

            }

        }

        return $classes;

    }

    add_filter('body_class', 'servicemaster_mikado_search_body_class');
}

if(!function_exists('servicemaster_mikado_get_search')) {
    /**
     * Loads search HTML based on search type option.
     */
    function servicemaster_mikado_get_search() {

        if(servicemaster_mikado_active_widget(false, false, 'mkd_search_opener')) {

            $search_type = servicemaster_mikado_options()->getOptionValue('search_type');

            if($search_type == 'search-covers-header') {
                servicemaster_mikado_set_position_for_covering_search();

                return;
            } else if($search_type == 'search-slides-from-window-top') {
                servicemaster_mikado_set_search_position_in_menu($search_type);
                if(servicemaster_mikado_is_responsive_on()) {
                    servicemaster_mikado_set_search_position_mobile();
                }

                return;
            } elseif($search_type === 'search-dropdown') {
                servicemaster_mikado_set_dropdown_search_position();

                return;
            }

            servicemaster_mikado_load_search_template();

        }
    }

}

if(!function_exists('servicemaster_mikado_set_position_for_covering_search')) {
    /**
     * Finds part of header where search template will be loaded
     */
    function servicemaster_mikado_set_position_for_covering_search() {

        $containing_sidebar = servicemaster_mikado_active_widget(false, false, 'mkd_search_opener');

        foreach($containing_sidebar as $sidebar) {

            if(strpos($sidebar, 'top-bar') !== false) {
                add_action('servicemaster_mikado_after_header_top_html_open', 'servicemaster_mikado_load_search_template');
            } else if(strpos($sidebar, 'main-menu') !== false) {
                add_action('servicemaster_mikado_after_header_menu_area_html_open', 'servicemaster_mikado_load_search_template');
            } else if(strpos($sidebar, 'mobile-logo') !== false) {
                add_action('servicemaster_mikado_after_mobile_header_html_open', 'servicemaster_mikado_load_search_template');
            } else if(strpos($sidebar, 'logo') !== false) {
                add_action('servicemaster_mikado_after_header_logo_area_html_open', 'servicemaster_mikado_load_search_template');
            } else if(strpos($sidebar, 'sticky') !== false) {
                add_action('servicemaster_mikado_after_sticky_menu_html_open', 'servicemaster_mikado_load_search_template');
            }

        }

    }

}

if(!function_exists('servicemaster_mikado_set_search_position_in_menu')) {
    /**
     * Finds part of header where search template will be loaded
     */
    function servicemaster_mikado_set_search_position_in_menu($type) {

        add_action('servicemaster_mikado_after_header_menu_area_html_open', 'servicemaster_mikado_load_search_template');

    }
}

if(!function_exists('servicemaster_mikado_set_search_position_mobile')) {
    /**
     * Hooks search template to mobile header
     */
    function servicemaster_mikado_set_search_position_mobile() {

        add_action('servicemaster_mikado_after_mobile_header_html_open', 'servicemaster_mikado_load_search_template');

    }

}

if(!function_exists('servicemaster_mikado_load_search_template')) {
    /**
     * Loads HTML template with parameters
     */
    function servicemaster_mikado_load_search_template() {
        global $servicemaster_IconCollections;

        $search_type = servicemaster_mikado_options()->getOptionValue('search_type');

        $search_icon       = '';
        $search_icon_close = '';
        if(servicemaster_mikado_options()->getOptionValue('search_icon_pack') !== '') {
            $search_icon       = $servicemaster_IconCollections->getSearchIcon(servicemaster_mikado_options()->getOptionValue('search_icon_pack'), true);
            $search_icon_close = $servicemaster_IconCollections->getSearchClose(servicemaster_mikado_options()->getOptionValue('search_icon_pack'), true);
        }

        $parameters = array(
            'search_in_grid'    => servicemaster_mikado_options()->getOptionValue('search_in_grid') == 'yes' ? true : false,
            'search_icon'       => $search_icon,
            'search_icon_close' => $search_icon_close
        );

        servicemaster_mikado_get_module_template_part('templates/types/'.$search_type, 'search', '', $parameters);

    }

}

if(!function_exists('servicemaster_mikado_set_dropdown_search_position')) {
    function servicemaster_mikado_set_dropdown_search_position() {
        add_action('servicemaster_mikado_after_search_opener', 'servicemaster_mikado_load_search_template');
    }
}