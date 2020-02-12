<?php

/*** Gallery Post Format ***/


if (!function_exists('servicemaster_mikado_gallery_post_meta_box_map')) {
	function servicemaster_mikado_gallery_post_meta_box_map() {
		$gallery_post_format_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('post'),
				'title' => esc_html__('Gallery Post Format', 'servicemaster'),
				'name'  => 'post_format_gallery_meta'
			)
		);

		servicemaster_mikado_add_multiple_images_field(
			array(
				'name'        => 'mkd_post_gallery_images_meta',
				'label'       => esc_html__('Gallery Images', 'servicemaster'),
				'description' => esc_html__('Choose your gallery images', 'servicemaster'),
				'parent'      => $gallery_post_format_meta_box,
			)
		);
	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_gallery_post_meta_box_map');
}