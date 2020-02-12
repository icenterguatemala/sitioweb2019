<?php

/*** Video Post Format ***/

if (!function_exists('servicemaster_mikado_video_post_meta_box_map')) {
	function servicemaster_mikado_video_post_meta_box_map() {

		$video_post_format_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('post'),
				'title' => esc_html__('Video Post Format', 'servicemaster'),
				'name'  => 'post_format_video_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_video_type_meta',
				'type'          => 'select',
				'label'         => esc_html__('Video Type', 'servicemaster'),
				'description'   => esc_html__('Choose video type', 'servicemaster'),
				'parent'        => $video_post_format_meta_box,
				'default_value' => 'youtube',
				'options'       => array(
					'youtube' => esc_html__('Youtube', 'servicemaster'),
					'vimeo'   => esc_html__('Vimeo', 'servicemaster'),
					'self'    => esc_html__('Self Hosted', 'servicemaster')
				),
				'args'          => array(
					'dependence' => true,
					'hide'       => array(
						'youtube' => '#mkd_mkd_video_self_hosted_container',
						'vimeo'   => '#mkd_mkd_video_self_hosted_container',
						'self'    => '#mkd_mkd_video_embedded_container'
					),
					'show'       => array(
						'youtube' => '#mkd_mkd_video_embedded_container',
						'vimeo'   => '#mkd_mkd_video_embedded_container',
						'self'    => '#mkd_mkd_video_self_hosted_container'
					)
				)
			)
		);

		$mkd_video_embedded_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $video_post_format_meta_box,
				'name'            => 'mkd_video_embedded_container',
				'hidden_property' => 'mkd_video_type_meta',
				'hidden_value'    => 'self'
			)
		);

		$mkd_video_self_hosted_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $video_post_format_meta_box,
				'name'            => 'mkd_video_self_hosted_container',
				'hidden_property' => 'mkd_video_type_meta',
				'hidden_values'   => array('youtube', 'vimeo')
			)
		);


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_video_id_meta',
				'type'        => 'text',
				'label'       => esc_html__('Video ID', 'servicemaster'),
				'description' => esc_html__('Enter Video ID', 'servicemaster'),
				'parent'      => $mkd_video_embedded_container,

			)
		);


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_video_image_meta',
				'type'        => 'image',
				'label'       => esc_html__('Video Image', 'servicemaster'),
				'description' => esc_html__('Upload video image', 'servicemaster'),
				'parent'      => $mkd_video_self_hosted_container,

			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_video_webm_link_meta',
				'type'        => 'text',
				'label'       => esc_html__('Video WEBM', 'servicemaster'),
				'description' => esc_html__('Enter video URL for WEBM format', 'servicemaster'),
				'parent'      => $mkd_video_self_hosted_container,

			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_video_mp4_link_meta',
				'type'        => 'text',
				'label'       => esc_html__('Video MP4', 'servicemaster'),
				'description' => esc_html__('Enter video URL for MP4 format', 'servicemaster'),
				'parent'      => $mkd_video_self_hosted_container,

			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_video_ogv_link_meta',
				'type'        => 'text',
				'label'       => esc_html__('Video OGV', 'servicemaster'),
				'description' => esc_html__('Enter video URL for OGV format', 'servicemaster'),
				'parent'      => $mkd_video_self_hosted_container,

			)
		);
	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_video_post_meta_box_map');
}