<?php

//Testimonials

if (!function_exists('servicemaster_mikado_testimonial_meta_box_map')) {
	function servicemaster_mikado_testimonial_meta_box_map() {

		$testimonial_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('testimonials'),
				'title' => esc_html__('Testimonial', 'servicemaster'),
				'name'  => 'testimonial_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_testimonaial_logo_image',
				'type'        => 'image',
				'label'       => esc_html__('Logo Image', 'servicemaster'),
				'description' => esc_html__('Choose testimonial logo image ', 'servicemaster'),
				'parent'      => $testimonial_meta_box
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_testimonial_title',
				'type'        => 'text',
				'label'       => esc_html__('Title', 'servicemaster'),
				'description' => esc_html__('Enter testimonial title', 'servicemaster'),
				'parent'      => $testimonial_meta_box,
			)
		);


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_testimonial_author',
				'type'        => 'text',
				'label'       => esc_html__('Author', 'servicemaster'),
				'description' => esc_html__('Enter author name', 'servicemaster'),
				'parent'      => $testimonial_meta_box,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_testimonial_author_position',
				'type'        => 'text',
				'label'       => esc_html__('Job Position', 'servicemaster'),
				'description' => esc_html__('Enter job position', 'servicemaster'),
				'parent'      => $testimonial_meta_box,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_testimonial_text',
				'type'        => 'text',
				'label'       => esc_html__('Text', 'servicemaster'),
				'description' => esc_html__('Enter testimonial text', 'servicemaster'),
				'parent'      => $testimonial_meta_box,
			)
		);
	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_testimonial_meta_box_map');
}