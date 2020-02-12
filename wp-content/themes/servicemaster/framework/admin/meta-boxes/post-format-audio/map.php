<?php

/*** Audio Post Format ***/

if (!function_exists('servicemaster_mikado_audio_post_meta_box_map')) {
	function servicemaster_mikado_audio_post_meta_box_map() {

		$audio_post_format_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('post'),
				'title' => esc_html__('Audio Post Format', 'servicemaster'),
				'name'  => 'post_format_audio_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_post_audio_link_meta',
				'type'        => 'text',
				'label'       => esc_html__('Link', 'servicemaster'),
				'description' => esc_html__('Enter audion link', 'servicemaster'),
				'parent'      => $audio_post_format_meta_box,

			)
		);

	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_audio_post_meta_box_map');
}