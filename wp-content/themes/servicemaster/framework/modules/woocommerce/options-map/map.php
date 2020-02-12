<?php

if (!function_exists('servicemaster_mikado_woocommerce_options_map')) {

	/**
	 * Add Woocommerce options page
	 */
	function servicemaster_mikado_woocommerce_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_woocommerce_page',
				'title' => esc_html__('Woocommerce', 'servicemaster'),
				'icon'  => 'icon_cart_alt'
			)
		);

		/**
		 * Product List Settings
		 */
		$panel_product_list = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_woocommerce_page',
				'name'  => 'panel_product_list',
				'title' => esc_html__('Product List', 'servicemaster')
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'mkd_woo_product_list_columns',
			'type'          => 'select',
			'label'         => esc_html__('Product List Columns', 'servicemaster'),
			'default_value' => 'mkd-woocommerce-columns-4',
			'description'   => esc_html__('Choose number of columns for product listing and related products on single product', 'servicemaster'),
			'options'       => array(
				'mkd-woocommerce-columns-3' => esc_html__('3 Columns (2 with sidebar)', 'servicemaster'),
				'mkd-woocommerce-columns-4' => esc_html__('4 Columns (3 with sidebar)', 'servicemaster')
			),
			'parent'        => $panel_product_list,
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'mkd_woo_products_per_page',
			'type'          => 'text',
			'label'         => esc_html__('Number of products per page', 'servicemaster'),
			'default_value' => '',
			'description'   => esc_html__('Set number of products on shop page', 'servicemaster'),
			'parent'        => $panel_product_list,
			'args'          => array(
				'col_width' => 3
			)
		));

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'mkd_products_list_title_tag',
			'type'          => 'select',
			'label'         => esc_html__('Products Title Tag', 'servicemaster'),
			'default_value' => 'h5',
			'description'   => '',
			'options'       => array(
				'h2' => 'h2',
				'h3' => 'h3',
				'h4' => 'h4',
				'h5' => 'h5',
				'h6' => 'h6',
			),
			'parent'        => $panel_product_list,
		));

		/**
		 * Single Product Settings
		 */
		$panel_single_product = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_woocommerce_page',
				'name'  => 'panel_single_product',
				'title' => esc_html__('Single Product', 'servicemaster')
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'mkd_single_product_title_tag',
			'type'          => 'select',
			'label'         => esc_html__('Single Product Title Tag', 'servicemaster'),
			'default_value' => 'h2',
			'description'   => '',
			'options'       => array(
				'h2' => 'h2',
				'h3' => 'h3',
				'h4' => 'h4',
				'h5' => 'h5',
				'h6' => 'h6',
			),
			'parent'        => $panel_single_product,
		));

        servicemaster_mikado_add_admin_field(
            array(
                'type'          => 'select',
                'name'          => 'woo_enable_single_product_zoom_image',
                'default_value' => 'no',
                'label'         => esc_html__( 'Enable Zoom Maginfier', 'servicemaster' ),
                'description'   => esc_html__( 'Enabling this option will show magnifier image on featured image hover', 'servicemaster' ),
                'parent'        => $panel_single_product,
                'options'       => array(
                    'no'       => esc_html__('No', 'servicemaster'),
                    'yes'      => esc_html__('Yes', 'servicemaster')
                ),
                'args'          => array(
                    'col_width' => 3
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'name'          => 'woo_set_single_images_behavior',
                'type'          => 'select',
                'default_value' => '',
                'label'         => esc_html__( 'Set Images Behavior', 'servicemaster' ),
                'options'       => array(
                    ''             => esc_html__( 'No Behavior', 'servicemaster' ),
                    'pretty-photo' => esc_html__( 'Pretty Photo Lightbox', 'servicemaster' ),
                    'photo-swipe'  => esc_html__( 'Photo Swipe Lightbox', 'servicemaster' )
                ),
                'parent'        => $panel_single_product
            )
        );

		/**
		 * DropDown Cart Widget Settings
		 */
		$panel_dropdown_cart = servicemaster_mikado_add_admin_panel(
			array(
				'page'  => '_woocommerce_page',
				'name'  => 'panel_dropdown_cart',
				'title' => esc_html__('Dropdown Cart Widget', 'servicemaster')
			)
		);

		servicemaster_mikado_add_admin_field(array(
			'name'          => 'mkd_woo_dropdown_cart_description',
			'type'          => 'text',
			'label'         => esc_html__('Cart Description', 'servicemaster'),
			'default_value' => '',
			'description'   => esc_html__('Enter dropdown cart description', 'servicemaster'),
			'parent'        => $panel_dropdown_cart
		));
	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_woocommerce_options_map', 21);
}