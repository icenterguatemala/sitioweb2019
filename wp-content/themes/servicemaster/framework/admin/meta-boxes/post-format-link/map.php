<?php

/*** Link Post Format ***/
if (!function_exists('servicemaster_mikado_link_post_meta_box_map')) {
	function servicemaster_mikado_link_post_meta_box_map() {

		$link_post_format_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('post'),
				'title' => esc_html__('Link Post Format', 'servicemaster'),
				'name'  => 'post_format_link_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_link_link_meta',
				'type'        => 'text',
				'label'       => esc_html__('Link', 'servicemaster'),
				'description' => esc_html__('Enter link', 'servicemaster'),
				'parent'      => $link_post_format_meta_box,

			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_link_color',
				'type'        => 'color',
				'label'       => esc_html__('Link Background Color', 'servicemaster'),
				'description' => esc_html__('Post background color', 'servicemaster'),
				'parent'      => $link_post_format_meta_box,

			)
		);
	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_link_post_meta_box_map');
}