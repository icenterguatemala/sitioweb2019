<?php

if (!function_exists('servicemaster_mikado_portfolio_options_map')) {

	function servicemaster_mikado_portfolio_options_map() {

		servicemaster_mikado_add_admin_page(array(
			'slug'  => '_portfolio',
			'title' => esc_html__('Portfolio', 'servicemaster'),
			'icon'  => 'icon_images'
		));

		$panel = servicemaster_mikado_add_admin_panel(array(
			'title' => esc_html__('Portfolio Single', 'servicemaster'),
			'name'  => 'panel_portfolio_single',
			'page'  => '_portfolio'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_template',
			'type'          => 'select',
			'label'         => esc_html__('Portfolio Type', 'servicemaster'),
			'default_value' => 'small-images',
			'description'   => esc_html__('Choose a default type for Single Project pages', 'servicemaster'),
			'parent'        => $panel,
			'options'       => array(
				'small-images'      => esc_html__('Portfolio small images', 'servicemaster'),
				'small-slider'      => esc_html__('Portfolio small slider', 'servicemaster'),
				'big-images'        => esc_html__('Portfolio big images', 'servicemaster'),
				'big-slider'        => esc_html__('Portfolio big slider', 'servicemaster'),
				'custom'            => esc_html__('Portfolio custom', 'servicemaster'),
				'full-width-custom' => esc_html__('Portfolio full width custom', 'servicemaster'),
				'gallery'           => esc_html__('Portfolio gallery', 'servicemaster'),
				'video'             => esc_html__('Portfolio video', 'servicemaster'),
			)
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_lightbox_images',
			'type'          => 'yesno',
			'label'         => esc_html__('Lightbox for Images', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will turn on lightbox functionality for projects with images.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'yes'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_lightbox_videos',
			'type'          => 'yesno',
			'label'         => esc_html__('Lightbox for Videos', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will turn on lightbox functionality for YouTube/Vimeo projects.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_hide_categories',
			'type'          => 'yesno',
			'label'         => esc_html__('Hide Categories', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will disable category meta description on Single Projects.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_hide_date',
			'type'          => 'yesno',
			'label'         => esc_html__('Hide Date', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will disable date meta on Single Projects.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_hide_author',
			'type'          => 'yesno',
			'label'         => esc_html__('Hide Author', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will disable author meta on Single Projects.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_comments',
			'type'          => 'yesno',
			'label'         => esc_html__('Show Comments', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will show comments on your page.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_sticky_sidebar',
			'type'          => 'yesno',
			'label'         => esc_html__('Sticky Side Text', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will make side text sticky on Single Project pages', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'yes'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_hide_pagination',
			'type'          => 'yesno',
			'label'         => esc_html__('Hide Pagination', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will turn off portfolio pagination functionality.', 'servicemaster'),
			'parent'        => $panel,
			'default_value' => 'no',
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '#mkd_navigate_same_category_container'
			)
		));

		$container_navigate_category = servicemaster_mikado_add_admin_container(array(
			'name'            => 'navigate_same_category_container',
			'parent'          => $panel,
			'hidden_property' => 'portfolio_single_hide_pagination',
			'hidden_value'    => 'yes'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_nav_same_category',
			'type'          => 'yesno',
			'label'         => esc_html__('Enable Pagination Through Same Category', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will make portfolio pagination sort through current category.', 'servicemaster'),
			'parent'        => $container_navigate_category,
			'default_value' => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'portfolio_single_numb_columns',
			'type'          => 'select',
			'label'         => esc_html__('Number of Columns', 'servicemaster'),
			'default_value' => 'three-columns',
			'description'   => esc_html__('Enter the number of columns for Portfolio Gallery type', 'servicemaster'),
			'parent'        => $panel,
			'options'       => array(
				'two-columns'   => esc_html__('2 columns', 'servicemaster'),
				'three-columns' => esc_html__('3 columns', 'servicemaster'),
				'four-columns'  => esc_html__('4 columns', 'servicemaster'),
			)
		));

		servicemaster_mikado_add_admin_field(array(
			'name'        => 'portfolio_single_slug',
			'type'        => 'text',
			'label'       => esc_html__('Portfolio Single Slug', 'servicemaster'),
			'description' => esc_html__('Enter if you wish to use a different Single Project slug (Note: After entering slug, navigate to Settings -> Permalinks and click "Save" in order for changes to take effect)', 'servicemaster'),
			'parent'      => $panel,
			'args'        => array(
				'col_width' => 3
			)
		));

	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_portfolio_options_map', 14);

}