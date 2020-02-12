<?php

/*** Post Options ***/

if (!function_exists('servicemaster_mikado_blog_post_meta_box_map')) {
	function servicemaster_mikado_blog_post_meta_box_map() {

		$post_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('post'),
				'title' => esc_html__('Post', 'servicemaster'),
				'name'  => 'post_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_blog_single_type_meta',
				'type'          => 'select',
				'label'         => esc_html__('Post Type', 'servicemaster'),
				'description'   => esc_html__('Choose post type', 'servicemaster'),
				'parent'        => $post_meta_box,
				'default_value' => 'youtube',
				'options'       => array(
					''             => '',
					'standard'     => esc_html__('Standard', 'servicemaster'),
					'image-title' => esc_html__('Image Title', 'servicemaster'),
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_blog_masonry_gallery_dimensions',
			'type'          => 'select',
			'label'         => esc_html__('Dimensions for Masonry Gallery', 'servicemaster'),
			'description'   => esc_html__('Choose image layout when it appears in Masonry Gallery list', 'servicemaster'),
			'parent'        => $post_meta_box,
			'options'       => array(
				'square'             => esc_html__('Square', 'servicemaster'),
				'large-width'        => esc_html__('Large width', 'servicemaster'),
				'large-height'       => esc_html__('Large height', 'servicemaster'),
				'large-width-height' => esc_html__('Large width/height', 'servicemaster'),
			),
			'default_value' => 'square'
		));


	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_blog_post_meta_box_map');
}