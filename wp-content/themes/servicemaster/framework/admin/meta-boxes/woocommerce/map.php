<?php

//WooCommerce
if (servicemaster_mikado_is_woocommerce_installed()) {


	if (!function_exists('servicemaster_mikado_woocommerce_meta_box_map')) {
		function servicemaster_mikado_woocommerce_meta_box_map() {

			$woocommerce_meta_box = servicemaster_mikado_add_meta_box(
				array(
					'scope' => array('product'),
					'title' => esc_html__('Product Meta', 'servicemaster'),
					'name'  => 'woo_product_meta'
				)
			);

			servicemaster_mikado_add_meta_box_field(array(
				'name'        => 'mkd_single_product_new_meta',
				'type'        => 'select',
				'label'       => esc_html__('Enable New Product Mark', 'servicemaster'),
				'description' => esc_html__('Enabling this option will show new product mark on your product lists and product single', 'servicemaster'),
				'parent'      => $woocommerce_meta_box,
				'options'     => array(
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster')
				)
			));

			servicemaster_mikado_add_meta_box_field(array(
				'name'          => 'mkd_masonry_product_list_dimensions_meta',
				'type'          => 'select',
				'label'         => esc_html__('Dimensions for Masonry Product list', 'servicemaster'),
				'description'   => esc_html__('Choose image layout when it appears in Masonry Product list', 'servicemaster'),
				'parent'        => $woocommerce_meta_box,
				'options'       => array(
					'standard'           => esc_html__('Standard', 'servicemaster'),
					'large-width'        => esc_html__('Large width', 'servicemaster'),
					'large-height'       => esc_html__('Large height', 'servicemaster'),
					'large-width-height' => esc_html__('Large width/height', 'servicemaster'),
				),
				'default_value' => 'standard'
			));

		}

		add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_woocommerce_meta_box_map');
	}
}