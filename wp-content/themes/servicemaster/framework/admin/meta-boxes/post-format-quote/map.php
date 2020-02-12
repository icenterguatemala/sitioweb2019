<?php

/*** Quote Post Format ***/

if (!function_exists('servicemaster_mikado_quote_post_meta_box_map')) {
	function servicemaster_mikado_quote_post_meta_box_map() {

		$quote_post_format_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('post'),
				'title' => esc_html__('Quote Post Format', 'servicemaster'),
				'name'  => 'post_format_quote_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_quote_text_meta',
				'type'        => 'text',
				'label'       => esc_html__('Quote Text', 'servicemaster'),
				'description' => esc_html__('Enter Quote text', 'servicemaster'),
				'parent'      => $quote_post_format_meta_box,

			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_quote_color',
				'type'        => 'color',
				'label'       => esc_html__('Quote Background Color', 'servicemaster'),
				'description' => esc_html__('Post background color', 'servicemaster'),
				'parent'      => $quote_post_format_meta_box,

			)
		);
	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_quote_post_meta_box_map');
}