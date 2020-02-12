<?php

if (!function_exists('servicemaster_mikado_portfolio_settings_meta_box_map')) {
	function servicemaster_mikado_portfolio_settings_meta_box_map() {

		$meta_box = servicemaster_mikado_add_meta_box(array(
			'scope' => 'portfolio-item',
			'title' => esc_html__('Portfolio Settings', 'servicemaster'),
			'name'  => 'portfolio_settings_meta_box'
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_portfolio_single_template_meta',
			'type'        => 'select',
			'label'       => esc_html__('Portfolio Type', 'servicemaster'),
			'description' => esc_html__('Choose a default type for Single Project pages', 'servicemaster'),
			'parent'      => $meta_box,
			'options'     => array(
				''                  => esc_html__('Default', 'servicemaster'),
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

		$all_pages = array();
		$pages = get_pages();
		foreach ($pages as $page) {
			$all_pages[$page->ID] = $page->post_title;
		}

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'portfolio_single_back_to_link',
			'type'        => 'select',
			'label'       => esc_html__('"Back To" Link', 'servicemaster'),
			'description' => esc_html__('Choose "Back To" page to link from portfolio Single Project page', 'servicemaster'),
			'parent'      => $meta_box,
			'options'     => $all_pages
		));

		$group_portfolio_external_link = servicemaster_mikado_add_admin_group(array(
			'name'        => 'group_portfolio_external_link',
			'title'       => esc_html__('Portfolio External Link', 'servicemaster'),
			'description' => esc_html__('Enter URL to link from Portfolio List page', 'servicemaster'),
			'parent'      => $meta_box
		));

		$row_portfolio_external_link = servicemaster_mikado_add_admin_row(array(
			'name'   => 'row_gradient_overlay',
			'parent' => $group_portfolio_external_link
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'portfolio_external_link',
			'type'        => 'textsimple',
			'label'       => esc_html__('Link', 'servicemaster'),
			'description' => '',
			'parent'      => $row_portfolio_external_link,
			'args'        => array(
				'col_width' => 3
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'portfolio_external_link_target',
			'type'        => 'selectsimple',
			'label'       => esc_html__('Target', 'servicemaster'),
			'description' => '',
			'parent'      => $row_portfolio_external_link,
			'options'     => array(
				'_self'  => esc_html__('Same Window', 'servicemaster'),
				'_blank' => esc_html__('New Window', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'portfolio_masonry_dimenisions',
				'type'        => 'select',
				'label'       => esc_html__('Dimensions for Masonry', 'servicemaster'),
				'description' => esc_html__('Choose image layout when it appears in Masonry type portfolio lists', 'servicemaster'),
				'parent'      => $meta_box,
				'options'     => array(
					'default'            => esc_html__('Default', 'servicemaster'),
					'large_width'        => esc_html__('Large width', 'servicemaster'),
					'large_height'       => esc_html__('Large height', 'servicemaster'),
					'large_width_height' => esc_html__('Large width/height', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'portfolio_background_color',
				'type'        => 'color',
				'label'       => esc_html__('Portfolio Background Color', 'servicemaster'),
				'description' => esc_html__('Portfolio background color used for some portfolio list hover animations', 'servicemaster'),
				'parent'      => $meta_box,

			)
		);

	}


	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_portfolio_settings_meta_box_map');
}