<?php

if(!function_exists('servicemaster_mikado_register_full_screen_menu_nav')) {
	function servicemaster_mikado_register_full_screen_menu_nav() {
		register_nav_menus(
			array(
				'popup-navigation' => esc_html__('Fullscreen Navigation', 'servicemaster')
			)
		);
	}

	add_action('after_setup_theme', 'servicemaster_mikado_register_full_screen_menu_nav');
}

if(!function_exists('servicemaster_mikado_register_full_screen_menu_sidebars')) {

	function servicemaster_mikado_register_full_screen_menu_sidebars() {

		register_sidebar(array(
			'name'          => esc_html__('Fullscreen Menu Top', 'servicemaster'),
			'id'            => 'fullscreen_menu_above',
			'description'   => esc_html__('This widget area is rendered above fullscreen menu', 'servicemaster'),
			'before_widget' => '<div class="%2$s mkd-fullscreen-menu-above-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="mkd-fullscreen-widget-title">',
			'after_title'   => '</h4>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Fullscreen Menu Bottom', 'servicemaster'),
			'id'            => 'fullscreen_menu_below',
			'description'   => esc_html__('This widget area is rendered below fullscreen menu', 'servicemaster'),
			'before_widget' => '<div class="%2$s mkd-fullscreen-menu-below-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="mkd-fullscreen-widget-title">',
			'after_title'   => '</h4>'
		));

	}

	add_action('widgets_init', 'servicemaster_mikado_register_full_screen_menu_sidebars');

}

if(!function_exists('servicemaster_mikado_fullscreen_menu_body_class')) {
	/**
	 * Function that adds body classes for different full screen menu types
	 *
	 * @param $classes array original array of body classes
	 *
	 * @return array modified array of classes
	 */
	function servicemaster_mikado_fullscreen_menu_body_class($classes) {


        if(servicemaster_mikado_get_meta_field_intersect('header_type') == 'header-minimal') {

			$classes[] = 'mkd-'.servicemaster_mikado_options()->getOptionValue('fullscreen_menu_animation_style');

		}

		return $classes;
	}

	add_filter('body_class', 'servicemaster_mikado_fullscreen_menu_body_class');
}

if(!function_exists('servicemaster_mikado_get_full_screen_menu')) {
	/**
	 * Loads fullscreen menu HTML template
	 */
	function servicemaster_mikado_get_full_screen_menu() {

        if(servicemaster_mikado_get_meta_field_intersect('header_type') == 'header-minimal') {

            extract(servicemaster_mikado_get_fullscreeen_page_options());

            $parameters = array(
                'fullscreen_menu_in_grid' => servicemaster_mikado_options()->getOptionValue('fullscreen_in_grid') === 'yes' ? true : false,
                'have_logo' => servicemaster_mikado_options()->getOptionValue('fullscreen_logo') !== '' ? true : false,
                'fullscreen_background_image' => $fullscreen_background_image
            );

			servicemaster_mikado_get_module_template_part('templates/fullscreen-menu', 'fullscreenmenu', '', $parameters);

		}

	}

}

if(!function_exists('servicemaster_mikado_get_full_screen_menu_navigation')) {
	/**
	 * Loads fullscreen menu navigation HTML template
	 */
	function servicemaster_mikado_get_full_screen_menu_navigation() {

		servicemaster_mikado_get_module_template_part('templates/parts/navigation', 'fullscreenmenu');

	}
}

if(!function_exists('servicemaster_mikado_get_fullscreeen_logo')) {
    /**
     * Loads logo HTML
     */
    function servicemaster_mikado_get_fullscreeen_logo() {

        $logo_image = servicemaster_mikado_options()->getOptionValue('fullscreen_logo');

        //get logo image dimensions and set style attribute for image link.
        $logo_dimensions = servicemaster_mikado_get_image_dimensions($logo_image);

        $logo_styles          = '';
        $logo_dimensions_attr = array();
        if(is_array($logo_dimensions) && array_key_exists('height', $logo_dimensions)) {
            $logo_height = $logo_dimensions['height'];
            $logo_styles = 'height: '.intval($logo_height / 2).'px;'; //divided with 2 because of retina screens

            if(!empty($logo_dimensions['height']) && $logo_dimensions['width']) {
                $logo_dimensions_attr['height'] = $logo_dimensions['height'];
                $logo_dimensions_attr['width']  = $logo_dimensions['width'];
            }
        }

        $params = array(
            'logo_image'           => $logo_image,
            'logo_styles'          => $logo_styles,
            'logo_dimensions_attr' => $logo_dimensions_attr
        );

        servicemaster_mikado_get_module_template_part('templates/parts/logo', 'fullscreenmenu', '', $params);

    }

}

if(!function_exists('servicemaster_mikado_get_fullscreeen_page_options')) {
    /**
     * Gets options from page
     */
    function servicemaster_mikado_get_fullscreeen_page_options() {
        $id                                = servicemaster_mikado_get_page_id();
        $page_options                      = array();
        $fullscreen_background_image       = '';

        if(get_post_meta($id, 'mkd_disable_fullscreen_menu_background_image_meta', true) == 'yes') {
            $fullscreen_background_image = 'background-image:none';
        } elseif(($meta_temp = get_post_meta($id, 'mkd_fullscreen_menu_background_image_meta', true)) !== '') {
            $fullscreen_background_image = 'background-image:url('.$meta_temp.')';
        }

        $page_options['fullscreen_background_image'] = $fullscreen_background_image;

        return $page_options;
    }
}