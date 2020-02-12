<?php
if (!function_exists('servicemaster_mikado_blog_meta_box_map')) {
	function servicemaster_mikado_blog_meta_box_map() {

		$mkd_blog_categories = array();
		$categories = get_categories();
		foreach ($categories as $category) {
			$mkd_blog_categories[$category->term_id] = $category->name;
		}

		$blog_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('page'),
				'title' => esc_html__('Blog', 'servicemaster'),
				'name'  => 'blog_meta'
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_blog_category_meta',
				'type'        => 'selectblank',
				'label'       => esc_html__('Blog Category', 'servicemaster'),
				'description' => esc_html__('Choose category of posts to display (leave empty to display all categories)', 'servicemaster'),
				'parent'      => $blog_meta_box,
				'options'     => $mkd_blog_categories
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_show_posts_per_page_meta',
				'type'        => 'text',
				'label'       => esc_html__('Number of Posts', 'servicemaster'),
				'description' => esc_html__('Enter the number of posts to display', 'servicemaster'),
				'parent'      => $blog_meta_box,
				'options'     => $mkd_blog_categories,
				'args'        => array("col_width" => 3)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_blog_split_background_image_meta',
				'type'        => 'image',
				'label'       => esc_html__('Blog Split Background Image', 'servicemaster'),
				'description' => esc_html__('Set background image if Blog Split page template is selected', 'servicemaster'),
				'parent'      => $blog_meta_box,
				'options'     => $mkd_blog_categories,
				'args'        => array("col_width" => 3)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_blog_split_title_meta',
				'type'        => 'text',
				'label'       => esc_html__('Blog Split Title', 'servicemaster'),
				'description' => esc_html__('Set title if Blog Split page template is selected', 'servicemaster'),
				'parent'      => $blog_meta_box,
				'options'     => $mkd_blog_categories,
				'args'        => array("col_width" => 12)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_blog_split_subtitle_meta',
				'type'        => 'text',
				'label'       => esc_html__('Blog Split Subtitle', 'servicemaster'),
				'description' => esc_html__('Set subtitle if Blog Split page template is selected', 'servicemaster'),
				'parent'      => $blog_meta_box,
				'options'     => $mkd_blog_categories,
				'args'        => array("col_width" => 12)
			)
		);

	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_blog_meta_box_map');
}