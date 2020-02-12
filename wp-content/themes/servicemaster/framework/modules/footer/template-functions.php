<?php

if (!function_exists('servicemaster_mikado_register_footer_sidebar')) {

	function servicemaster_mikado_register_footer_sidebar() {

		register_sidebar(array(
			'name'          => esc_html__('Footer Column 1', 'servicemaster'),
			'id'            => 'footer_column_1',
			'description'   => esc_html__('Footer Column 1', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-column-1 %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Footer Column 2', 'servicemaster'),
			'id'            => 'footer_column_2',
			'description'   => esc_html__('Footer Column 2', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-column-2 %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Footer Column 3', 'servicemaster'),
			'id'            => 'footer_column_3',
			'description'   => esc_html__('Footer Column 3', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-column-3 %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Footer Column 4', 'servicemaster'),
			'id'            => 'footer_column_4',
			'description'   => esc_html__('Footer Column 4', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-column-4 %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Footer Bottom', 'servicemaster'),
			'id'            => 'footer_text',
			'description'   => esc_html__('Footer Bottom', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-text %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Footer Bottom Left', 'servicemaster'),
			'id'            => 'footer_bottom_left',
			'description'   => esc_html__('Footer Bottom Left', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-bottom-left %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

		register_sidebar(array(
			'name'          => esc_html__('Footer Bottom Right', 'servicemaster'),
			'id'            => 'footer_bottom_right',
			'description'   => esc_html__('Footer Bottom Right', 'servicemaster'),
			'before_widget' => '<div id="%1$s" class="widget mkd-footer-bottom-right %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5 class="mkd-footer-widget-title">',
			'after_title'   => '</h5>'
		));

	}

	add_action('widgets_init', 'servicemaster_mikado_register_footer_sidebar');

}

if (!function_exists('servicemaster_mikado_get_footer')) {
	/**
	 * Loads footer HTML
	 */
	function servicemaster_mikado_get_footer() {

		$parameters = array();
		$id = servicemaster_mikado_get_page_id();
		$parameters['footer_classes'] = servicemaster_mikado_get_footer_classes($id);
		$parameters['display_footer_top'] = (servicemaster_mikado_get_meta_field_intersect('show_footer_top') === 'yes') ? true : false;
		$parameters['display_footer_bottom'] = (servicemaster_mikado_get_meta_field_intersect('show_footer_bottom') === 'yes') ? true : false;

		servicemaster_mikado_get_module_template_part('templates/footer', 'footer', '', $parameters);

	}

}

if (!function_exists('servicemaster_mikado_get_content_bottom_area')) {
	/**
	 * Loads content bottom area HTML with all needed parameters
	 */
	function servicemaster_mikado_get_content_bottom_area() {

		$parameters = array();

		//Current page id
		$id = servicemaster_mikado_get_page_id();

		//is content bottom area enabled for current page?
		$parameters['content_bottom_area'] = servicemaster_mikado_get_meta_field_intersect('enable_content_bottom_area');
		if ($parameters['content_bottom_area'] == 'yes') {
			//Sidebar for content bottom area
			$parameters['content_bottom_area_sidebar'] = servicemaster_mikado_get_meta_field_intersect('content_bottom_sidebar_custom_display');
			//Content bottom area in grid
			$parameters['content_bottom_area_in_grid'] = servicemaster_mikado_get_meta_field_intersect('content_bottom_in_grid');
			//Content bottom area background color
			$parameters['content_bottom_background_color'] = 'background-color: ' . servicemaster_mikado_get_meta_field_intersect('content_bottom_background_color');
		}

		servicemaster_mikado_get_module_template_part('templates/parts/content-bottom-area', 'footer', '', $parameters);

	}

}

if (!function_exists('servicemaster_mikado_get_footer_top')) {
	/**
	 * Return footer top HTML
	 */
	function servicemaster_mikado_get_footer_top() {

		$parameters = array();

		$id = servicemaster_mikado_get_page_id();

		$parameters['footer_in_grid'] = (servicemaster_mikado_get_meta_field_intersect('footer_in_grid') === 'yes') ? true : false;
		$parameters['footer_top_classes'] = servicemaster_mikado_footer_top_classes();
		$parameters['footer_top_columns'] = servicemaster_mikado_get_meta_field_intersect('footer_top_columns', $id);

		servicemaster_mikado_get_module_template_part('templates/parts/footer-top', 'footer', '', $parameters);

	}

}

if (!function_exists('servicemaster_mikado_get_footer_bottom')) {
	/**
	 * Return footer bottom HTML
	 */
	function servicemaster_mikado_get_footer_bottom() {

		$parameters = array();

		$id = servicemaster_mikado_get_page_id();

		$parameters['footer_bottom_border'] = servicemaster_mikado_get_footer_bottom_border();
		$parameters['footer_bottom_border_in_grid'] = (servicemaster_mikado_options()->getOptionValue('footer_bottom_border_in_grid') == 'yes') ? 'mkd-in-grid' : '';
		$parameters['footer_in_grid'] = (servicemaster_mikado_get_meta_field_intersect('footer_in_grid') === 'yes') ? true : false;
		$parameters['footer_bottom_columns'] = servicemaster_mikado_get_meta_field_intersect('footer_bottom_columns', $id);
		$parameters['footer_bottom_border_bottom'] = servicemaster_mikado_get_footer_bottom_bottom_border();
		$parameters['footer_bottom_border_class'] = 'mkd-footer-bottom-disable-border';

		$border = servicemaster_mikado_get_meta_field_intersect('footer_bottom_border');
		if ($border === 'yes') {
			$parameters['footer_bottom_border_class'] = 'mkd-footer-bottom-enable-border';
		}

		servicemaster_mikado_get_module_template_part('templates/parts/footer-bottom', 'footer', '', $parameters);

	}

}


//Functions for loading sidebars

if (!function_exists('servicemaster_mikado_get_footer_sidebar_25_25_50')) {

	function servicemaster_mikado_get_footer_sidebar_25_25_50() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-three-columns-25-25-50', 'footer');
	}
}

if (!function_exists('servicemaster_mikado_get_footer_sidebar_50_25_25')) {

	function servicemaster_mikado_get_footer_sidebar_50_25_25() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-three-columns-50-25-25', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_sidebar_four_columns')) {

	function servicemaster_mikado_get_footer_sidebar_four_columns() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-four-columns', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_sidebar_three_columns')) {

	function servicemaster_mikado_get_footer_sidebar_three_columns() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-three-columns', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_sidebar_two_columns')) {

	function servicemaster_mikado_get_footer_sidebar_two_columns() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-two-columns', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_sidebar_one_column')) {

	function servicemaster_mikado_get_footer_sidebar_one_column() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-one-column', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_bottom_sidebar_one_column')) {

	function servicemaster_mikado_get_footer_bottom_sidebar_one_column() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-bottom-one-column', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_bottom_sidebar_two_columns')) {

	function servicemaster_mikado_get_footer_bottom_sidebar_two_columns() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-bottom-two-columns', 'footer');
	}

}

if (!function_exists('servicemaster_mikado_get_footer_bottom_sidebar_three_columns')) {

	function servicemaster_mikado_get_footer_bottom_sidebar_three_columns() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-bottom-three-columns', 'footer');
	}
}

if (!function_exists('servicemaster_mikado_get_footer_bottom_sidebar_25_50_25')) {

	function servicemaster_mikado_get_footer_bottom_sidebar_25_50_25() {
		servicemaster_mikado_get_module_template_part('templates/sidebars/sidebar-bottom-three-columns-25-50-25', 'footer');
	}
}

