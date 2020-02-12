<?php
if (!function_exists('servicemaster_mikado_get_blog')) {
	/**
	 * Function which return holder for all blog lists
	 *
	 * @return holder.php template
	 */
	function servicemaster_mikado_get_blog($type) {

		$sidebar = servicemaster_mikado_sidebar_layout();

		$params = array(
			"blog_type" => $type,
			"sidebar"   => $sidebar
		);
		servicemaster_mikado_get_module_template_part('templates/lists/holder', 'blog', '', $params);
	}

}

if (!function_exists('servicemaster_mikado_get_blog_type')) {

	/**
	 * Function which create query for blog lists
	 *
	 * @return blog list template
	 */

	function servicemaster_mikado_get_blog_type($type) {

		$blog_query = servicemaster_mikado_get_blog_query();

		$paged = servicemaster_mikado_paged();

		if (servicemaster_mikado_options()->getOptionValue('blog_page_range') != "") {
			$blog_page_range = esc_attr(servicemaster_mikado_options()->getOptionValue('blog_page_range'));
		} else {
			$blog_page_range = $blog_query->max_num_pages;
		}

		$blog_classes = servicemaster_mikado_get_blog_holder_classes($type);

		$blog_data_params = servicemaster_mikado_set_blog_holder_data_params($type);

		$params = array(
			'blog_query'       => $blog_query,
			'paged'            => $paged,
			'blog_page_range'  => $blog_page_range,
			'blog_type'        => $type,
			'blog_classes'     => $blog_classes,
			'blog_data_params' => $blog_data_params
		);

		servicemaster_mikado_get_module_template_part('templates/lists/' . $type, 'blog', '', $params);
	}

}

if (!function_exists('servicemaster_mikado_get_blog_holder_classes')) {
	function servicemaster_mikado_get_blog_holder_classes($type) {
		$load_more_enabled = servicemaster_mikado_load_more_enabled();
		$infinite_scroll_enabled = servicemaster_mikado_infinite_scroll_enabled();

		$blog_classes = array(
			'mkd-blog-holder'
		);

		if ($load_more_enabled) {
			$blog_classes[] = 'mkd-blog-load-more';
		}

		if ($infinite_scroll_enabled) {
			$blog_classes[] = 'mkd-blog-infinite-scroll';
		}

		switch ($type) {

			case 'standard':
				$blog_classes[] = 'mkd-blog-type-standard';
				break;

			case 'masonry':
				$blog_classes[] = 'mkd-blog-type-masonry';
				break;

			case 'masonry-gallery':
				$blog_classes[] = 'mkd-blog-type-masonry-gallery';
				break;

			case 'masonry-full-width':
				$blog_classes[] = 'mkd-blog-type-masonry mkd-masonry-full-width';
				break;

			default:
				$blog_classes[] = 'mkd-blog-type-standard';
				break;
		}

		return implode(' ', $blog_classes);
	}
}

if (!function_exists('servicemaster_mikado_get_blog_query')) {
	/**
	 * Function which create query for blog lists
	 *
	 * @return wp query object
	 */
	function servicemaster_mikado_get_blog_query() {
		global $wp_query;

		$id = servicemaster_mikado_get_page_id();
		$category = esc_attr(get_post_meta($id, "mkd_blog_category_meta", true));
		if (esc_attr(get_post_meta($id, "mkd_show_posts_per_page_meta", true)) != "") {
			$post_number = esc_attr(get_post_meta($id, "mkd_show_posts_per_page_meta", true));
		} else {
			$post_number = esc_attr(get_option('posts_per_page'));
		}

		$paged = servicemaster_mikado_paged();
		$query_array = array(
			'post_type'      => 'post',
			'paged'          => $paged,
			'cat'            => $category,
			'posts_per_page' => $post_number
		);
		if (is_archive()) {
			$blog_query = $wp_query;
		} else {
			$blog_query = new WP_Query($query_array);
		}

		return $blog_query;
	}
}

if (!function_exists('servicemaster_mikado_get_user_custom_fields')) {
	/**
	 * Function returns links and icons for author social networks
	 *
	 * return array
	 */
	function servicemaster_mikado_get_user_custom_fields() {

		$user_social_array = array();
		$social_network_array = array(
			'instagram',
			'twitter',
			'pinterest',
			'tumblr',
			'facebook',
			'googleplus',
			'linkedin'
		);

		foreach ($social_network_array as $network) {
			if (get_the_author_meta($network) != '') {
				$$network = array(
					'link'  => get_the_author_meta($network),
					'class' => 'social_' . $network . ' mkd-author-social-' . $network . ' mkd-author-social-icon'
				);
				$user_social_array[$network] = $$network;
			}
		}

		return $user_social_array;
	}
}


if (!function_exists('servicemaster_mikado_get_post_format_html')) {

	/**
	 * Function which return html for post formats
	 *
	 * @param $type
	 *
	 * @return post hormat template
	 */

	function servicemaster_mikado_get_post_format_html($type = "", $ajax = '') {

		$post_format = get_post_format();

		$supported_post_formats = array('audio', 'video', 'link', 'quote', 'gallery');
		if (in_array($post_format, $supported_post_formats)) {
			$post_format = $post_format;
		} else {
			$post_format = 'standard';
		}

		$slug = '';
		if ($type !== "") {
			$slug = $type;
		}

		if ($slug === 'masonry-full-width') {
			$slug = 'masonry';
		}

		if ($slug === 'masonry-slider') {
			$slug = 'masonry';

			if ($post_format === 'gallery' || $post_format === 'audio' || $post_format === 'video') {
				$post_format = 'standard'; //set blog slider to use standard masonry layout for gallery posts
			}
		}

		$params = array();

		if ($type == 'masonry-gallery') {

			if (in_array($post_format, array('audio'))) {
				$post_format = 'standard';
			}

			$size = 'square';
			if (get_post_meta(get_the_ID(), 'mkd_blog_masonry_gallery_dimensions', true) !== '') {
				$size = get_post_meta(get_the_ID(), 'mkd_blog_masonry_gallery_dimensions', true);
				$params['post_class'] = 'mkd-post-size-' . $size;
			}
			$params['post_class'] = 'mkd-post-size-' . $size;
			$params['image_size'] = servicemaster_mikado_get_masonry_gallery_image_size($size);
		}

		$chars_array = servicemaster_mikado_blog_lists_number_of_chars();
		if (isset($chars_array[$type])) {
			$params['excerpt_length'] = $chars_array[$type];
		} else {
			$params['excerpt_length'] = '';
		}

		if ($ajax === '') {
			servicemaster_mikado_get_module_template_part('templates/lists/post-formats/' . $post_format, 'blog', $slug, $params);
		}
		if ($ajax === 'yes') {
			return servicemaster_mikado_get_blog_module_template_part('templates/lists/post-formats/' . $post_format, $slug, $params);
		}
	}

}

if (!function_exists('servicemaster_mikado_get_masonry_gallery_image_size')) {
	/**
	 * Function which return post image size for masonry gallery
	 *
	 * @return string
	 */

	function servicemaster_mikado_get_masonry_gallery_image_size($size) {

		$image_size = 'servicemaster_mikado_square';

		switch ($size):

			case 'large-width':
				$image_size = 'servicemaster_mikado_large_width';
				break;
			case 'large-height':
				$image_size = 'servicemaster_mikado_large_height';
				break;
			case 'large-width-height':
				$image_size = 'servicemaster_mikado_large_width_height';
				break;
		endswitch;

		return $image_size;
	}

}

if (!function_exists('servicemaster_mikado_get_default_blog_list')) {
	/**
	 * Function which return default blog list for archive post types
	 *
	 * @return post format template
	 */

	function servicemaster_mikado_get_default_blog_list() {

		$blog_list = servicemaster_mikado_options()->getOptionValue('blog_list_type');

		return $blog_list;

	}

}

if (!function_exists('servicemaster_mikado_comment_form_submit_button')) {
	/**
	 * Override comment form submit button
	 *
	 * @return mixed|string
	 */
	function servicemaster_mikado_comment_form_submit_button() {
		if (servicemaster_mikado_core_installed()) {
			$comment_form_button = servicemaster_mikado_get_button_html(array(
				'html_type'  => 'input',
				'type'       => 'solid',
				'text'       => esc_html__('Post Comment', 'servicemaster'),
				'input_name' => 'submit'
			));
		} else {
			$comment_form_button = $comment_form_button = '<input class="mkd-btn mkd-btn-medium mkd-btn-solid mkd-btn-hover-outline" name="submit" value="' . esc_html__('Post Comment', 'servicemaster') . '" type="submit">';
		}

		return $comment_form_button;

	}

	add_filter('comment_form_submit_button', 'servicemaster_mikado_comment_form_submit_button');

}


if (!function_exists('servicemaster_mikado_pagination')) {

	/**
	 * Function which return pagination
	 *
	 * @return blog list pagination html
	 */

	function servicemaster_mikado_pagination($pages = '', $range = 4, $paged = 1) {

		$showitems = $range + 1;

		if ($pages == '') {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if (!$pages) {
				$pages = 1;
			}
		}

		$pagination_params['pages'] = $pages;
		$pagination_params['paged'] = $paged;
		$pagination_params['range'] = $range;

		$load_more_enabled = servicemaster_mikado_load_more_enabled();
		$infinite_scroll_enabled = servicemaster_mikado_infinite_scroll_enabled();

		$search_template = 'no';
		if (is_search()) {
			$search_template = 'yes';
		}


		if ($pages != 1) {
			if ($search_template == 'yes' || (!$load_more_enabled && !$infinite_scroll_enabled)) {

				echo servicemaster_mikado_default_pagination_html($pagination_params);

			}
		}
	}
}

if (!function_exists('servicemaster_mikado_default_pagination_html')) {

	function servicemaster_mikado_default_pagination_html($params) {

		extract($params);
		$html = '';
		$show_first_page = false;
		$show_last_page = false;
		$prev_first_class = '';
		$next_last_class = '';

		if ($paged > 2 && $paged > $range + 1 && $showitems < $pages) {
			$show_first_page = true;
			$prev_first_class = 'mkd-pagination prev-first';
		}

		if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages) {
			$show_last_page = true;
			$next_last_class = 'mkd-pagination-next-last';
		}

		if ($pages > $paged) {
			$next_link_url = esc_url(get_pagenum_link($paged + 1));
		} else {
			$next_link_url = esc_url(get_pagenum_link($paged));
		}

		$html .= '<div class="mkd-pagination">';
		$html .= '<ul>';

		//get first page html
		if ($show_first_page) {
			$html .= '<li class="mkd-pagination-first-page">';
			$html .= '<a href="' . esc_url(get_pagenum_link(1)) . '">';
			$html .= '<span class="arrow_carrot-2left"></span>';
			$html .= '</a>';
			$html .= '</li>';
		}

		//get previous page html
		$html .= '<li class="mkd-pagination-prev ' . $prev_first_class . '"> ';
		$html .= '<a href="' . esc_url(get_pagenum_link($paged - 1)) . '">';
		$html .= '<span class="arrow_carrot-left"></span>';
		$html .= '</a>';
		$html .= '</li>';


		// get pages links
		for ($i = 1; $i <= $pages; $i++) {

			if ($pages !== 1 && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
				if ($paged === $i) {
					$html .= '<li class="active">';
					$html .= '<span>' . $i . '</span>';
					$html .= '</li>';
				} else {
					$html .= '<li>';
					$html .= '<a href = "' . esc_url(get_pagenum_link($i)) . '" class="inactive">' . $i . '</a>';
					$html .= '</li>';
				}
			}

		}

		//get next page html
		$html .= '<li class="mkd-pagination-next ' . $next_last_class . '" >';
		$html .= '<a href="' . esc_url($next_link_url) . '" >';
		$html .= '<span class="arrow_carrot-right"></span>';
		$html .= '</a>';
		$html .= '</li>';


		//get last page html
		if ($show_last_page) {
			$html .= '<li class="mkd-pagination-last-page">';
			$html .= '<a href="' . esc_url(get_pagenum_link($pages)) . '">';
			$html .= '<span class="arrow_carrot-2right"></span>';
			$html .= '</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';
		$html .= '</div>';

		$html .= '<div class="mkd-pagination-helper">';
		$html .= get_previous_posts_link();
		$html .= get_next_posts_link();
		$html .= '</div>';

		return $html;

	}

}

if (!function_exists('servicemaster_mikado_load_more_button_html')) {

	function servicemaster_mikado_get_load_more_button_html() {
		$button_params = array(
			'text' => esc_html('Load More', 'servicemaster'),
		);
		$button_html = '<div class="mkd-load-more-ajax-pagination">';
		$button_html .= servicemaster_mikado_get_button_html($button_params);
		$button_html .= '</div>';
		return $button_html;
	}
}

if (!function_exists('servicemaster_mikado_post_info')) {
	/**
	 * Function that loads parts of blog post info section
	 * Possible options are:
	 * 1. date
	 * 2. category
	 * 3. author
	 * 4. comments
	 * 5. like
	 * 6. share
	 *
	 * @param $config array of sections to load
	 */
	function servicemaster_mikado_post_info($config, $blog_type = '') {
		$default_config = array(
			'date'     => '',
			'category' => '',
			'author'   => '',
			'comments' => '',
			'like'     => '',
			'share'    => ''
		);

		extract(shortcode_atts($default_config, $config));
		$params['blog_type'] = $blog_type;

		if ($author == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/parts/post-info-author', 'blog');
		}

		if ($date == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/parts/post-info-date', 'blog');
		}

		if ($like == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/parts/post-info-like', 'blog');
		}

		if ($comments == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/parts/post-info-comments', 'blog');
		}

		if ($category == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/parts/post-info-category', 'blog');
		}

		if ($share == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/parts/post-info-share', 'blog');
		}
	}
}

if (!function_exists('servicemaster_mikado_excerpt')) {
	/**
	 * Function that cuts post excerpt to the number of word based on previosly set global
	 * variable $word_count, which is defined in mkd_set_blog_word_count function.
	 *
	 * It current post has read more tag set it will return content of the post, else it will return post excerpt
	 *
	 */
	function servicemaster_mikado_excerpt($excerpt_length = '') {
		global $post;

		if (post_password_required()) {
			echo get_the_password_form();
		} //does current post has read more tag set?
		elseif (servicemaster_mikado_post_has_read_more()) {
			global $more;

			//override global $more variable so this can be used in blog templates
			$more = 0;
			the_content(true);
		} //is word count set to something different that 0?
		elseif ($excerpt_length != '0') {
			//if word count is set and different than empty take that value, else that general option from theme options
			$word_count = '45';
			if (isset($excerpt_length) && $excerpt_length != "") {
				$word_count = $excerpt_length;

			} elseif (servicemaster_mikado_options()->getOptionValue('number_of_chars') != '') {
				$word_count = esc_attr(servicemaster_mikado_options()->getOptionValue('number_of_chars'));
			}
			//if post excerpt field is filled take that as post excerpt, else that content of the post
			$post_excerpt = $post->post_excerpt != "" ? $post->post_excerpt : strip_tags($post->post_content);

			//remove leading dots if those exists
			$clean_excerpt = strlen($post_excerpt) && strpos($post_excerpt, '...') ? strstr($post_excerpt, '...', true) : $post_excerpt;

			//if clean excerpt has text left
			if ($clean_excerpt !== '') {
				//explode current excerpt to words
				$excerpt_word_array = explode(' ', $clean_excerpt);

				//cut down that array based on the number of the words option
				$excerpt_word_array = array_slice($excerpt_word_array, 0, $word_count);

				//add exerpt postfix
				$excert_postfix = apply_filters('servicemaster_mikado_excerpt_postfix', '...');

				//and finally implode words together
				$excerpt = implode(' ', $excerpt_word_array) . $excert_postfix;

				//is excerpt different than empty string?
				if ($excerpt !== '') {
					echo '<p class="mkd-post-excerpt">' . wp_kses_post($excerpt) . '</p>';
				}
			}
		}
	}
}

if (!function_exists('servicemaster_mikado_get_blog_single')) {

	/**
	 * Function which return holder for single posts
	 *
	 * @return single holder.php template
	 */

	function servicemaster_mikado_get_blog_single() {
		$sidebar = servicemaster_mikado_sidebar_layout();
		$single_template = servicemaster_mikado_get_meta_field_intersect('blog_single_type');

		$params = array(
			"sidebar"         => $sidebar,
			"single_template" => 'mkd-blog-' . $single_template
		);

		servicemaster_mikado_get_module_template_part('templates/single/holder', 'blog', '', $params);
	}
}

if (!function_exists('servicemaster_mikado_get_single_html')) {

	/**
	 * Function return all parts on single.php page
	 *
	 *
	 * @return single.php html
	 */

	function servicemaster_mikado_get_single_html() {

		$post_format = get_post_format();
		$supported_post_formats = array('audio', 'video', 'link', 'quote', 'gallery');
		if (in_array($post_format, $supported_post_formats)) {
			$post_format = $post_format;
		} else {
			$post_format = 'standard';
		}

		//Related posts
		$related_posts_params = array();
		$show_related = (servicemaster_mikado_options()->getOptionValue('blog_single_related_posts') == 'yes') ? true : false;
		if ($show_related) {
			$post_id = get_the_ID();
			$related_posts_options = array(
				'posts_per_page' => 4
			);
			$related_posts_params = array(
				'related_posts' => servicemaster_mikado_get_related_post_type($post_id, $related_posts_options)
			);
		}

		$single_options_template = servicemaster_mikado_get_meta_field_intersect('blog_single_type');

		if ($single_options_template == 'image-title') {
			$post_format = 'standard';
		}

		servicemaster_mikado_get_module_template_part('templates/single/post-formats/' . $post_format, 'blog', $single_options_template);
		servicemaster_mikado_get_module_template_part('templates/single/parts/author-info', 'blog');
		if ($show_related) {
			servicemaster_mikado_get_module_template_part('templates/single/parts/related-posts', 'blog', '', $related_posts_params);
		}
		if (servicemaster_mikado_show_comments()) {
			comments_template('', true);
		}
	}

}
if (!function_exists('servicemaster_mikado_additional_post_items')) {

	/**
	 * Function which return parts on single.php which are just below content
	 *
	 * @return single.php html
	 */

	function servicemaster_mikado_additional_post_items() {

		if (has_tag() && servicemaster_mikado_options()->getOptionValue('blog_enable_single_tags') == 'yes') {
			servicemaster_mikado_get_module_template_part('templates/single/parts/tags', 'blog');
		}


		$args_pages = array(
			'before'      => '<div class="mkd-single-links-pages"><div class="mkd-single-links-pages-inner">',
			'after'       => '</div></div>',
            'link_before' => '<span>' . esc_html__('Post Page Link: ', 'servicemaster'),
			'link_after'  => '</span>',
			'pagelink'    => '%'
		);

		wp_link_pages($args_pages);

	}

	add_action('servicemaster_mikado_before_blog_article_closed_tag', 'servicemaster_mikado_additional_post_items');
}


if (!function_exists('servicemaster_mikado_comment')) {

	/**
	 * Function which modify default wordpress comments
	 *
	 * @return comments html
	 */

	function servicemaster_mikado_comment($comment, $args, $depth) {

		$GLOBALS['comment'] = $comment;

		global $post;

		$is_pingback_comment = $comment->comment_type == 'pingback';
		$is_author_comment = $post->post_author == $comment->user_id;

		$comment_class = 'mkd-comment clearfix';

		if ($is_author_comment) {
			$comment_class .= ' mkd-post-author-comment';
		}

		if ($is_pingback_comment) {
			$comment_class .= ' mkd-pingback-comment';
		}

		?>

		<li>
		<div class="<?php echo esc_attr($comment_class); ?>">
			<?php if (!$is_pingback_comment) { ?>
				<div
					class="mkd-comment-image"> <?php echo servicemaster_mikado_kses_img(get_avatar($comment, 75)); ?> </div>
			<?php } ?>
			<div class="mkd-comment-text">
				<div class="mkd-comment-reply-holder">
					<?php
					comment_reply_link(array_merge($args, array(
						'depth'     => $depth,
						'max_depth' => $args['max_depth']
					)));
					edit_comment_link();
					?>
				</div>
				<div class="mkd-comment-info">
					<h5 class="mkd-comment-name">
						<?php if ($is_pingback_comment) {
							esc_html_e('Pingback:', 'servicemaster');
						} ?>
						<?php echo wp_kses_post(get_comment_author_link()); ?>
						<?php if ($is_author_comment) { ?>
							<i class="fa fa-user post-author-comment-icon"></i>
						<?php } ?>
					</h5>
					<span
						class="mkd-comment-date"><?php comment_time(get_option('date_format')); ?><?php esc_html_e(' at ', 'servicemaster'); ?><?php comment_time(get_option('time_format')); ?></span>
				</div>

				<?php if (!$is_pingback_comment) { ?>
					<div class="mkd-text-holder" id="comment-<?php echo comment_ID(); ?>">
						<?php comment_text(); ?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php //li tag will be closed by WordPress after looping through child elements
		?>

	<?php
	}
}

if (!function_exists('servicemaster_mikado_blog_archive_pages_classes')) {

	/**
	 * Function which create classes for container in archive pages
	 *
	 * @return array
	 */

	function servicemaster_mikado_blog_archive_pages_classes($blog_type) {

		$classes = array();
		if (in_array($blog_type, servicemaster_mikado_blog_full_width_types())) {
			$classes['holder'] = 'mkd-full-width';
			$classes['inner'] = 'mkd-full-width-inner';
		} elseif (in_array($blog_type, servicemaster_mikado_blog_grid_types())) {
			$classes['holder'] = 'mkd-container';
			$classes['inner'] = 'mkd-container-inner clearfix';
		}

		return $classes;

	}

}

if (!function_exists('servicemaster_mikado_blog_full_width_types')) {

	/**
	 * Function which return all full width blog types
	 *
	 * @return array
	 */

	function servicemaster_mikado_blog_full_width_types() {

		$types = array('masonry-full-width', 'masonry-gallery');

		return $types;

	}

}

if (!function_exists('servicemaster_mikado_blog_grid_types')) {

	/**
	 * Function which return in grid blog types
	 *
	 * @return array
	 */

	function servicemaster_mikado_blog_grid_types() {

		$types = array('standard', 'masonry');

		return $types;

	}

}

if (!function_exists('servicemaster_mikado_blog_types')) {

	/**
	 * Function which return all blog types
	 *
	 * @return array
	 */

	function servicemaster_mikado_blog_types() {

		$types = array_merge(servicemaster_mikado_blog_grid_types(), servicemaster_mikado_blog_full_width_types());

		return $types;

	}

}

if (!function_exists('servicemaster_mikado_blog_templates')) {

	/**
	 * Function which return all blog templates names
	 *
	 * @return array
	 */

	function servicemaster_mikado_blog_templates() {

		$templates = array();
		$grid_templates = servicemaster_mikado_blog_grid_types();
		$full_templates = servicemaster_mikado_blog_full_width_types();
		foreach ($grid_templates as $grid_template) {
			array_push($templates, 'blog-' . $grid_template);
		}
		foreach ($full_templates as $full_template) {
			array_push($templates, 'blog-' . $full_template);
		}

		return $templates;

	}

}

if (!function_exists('servicemaster_mikado_blog_lists_number_of_chars')) {

	/**
	 * Function that return number of characters for different lists based on options
	 *
	 * @return int
	 */

	function servicemaster_mikado_blog_lists_number_of_chars() {

		$number_of_chars = array();

		if (servicemaster_mikado_options()->getOptionValue('standard_number_of_chars')) {
			$number_of_chars['standard'] = servicemaster_mikado_options()->getOptionValue('standard_number_of_chars');
		}
		if (servicemaster_mikado_options()->getOptionValue('masonry_number_of_chars')) {
			$number_of_chars['masonry'] = servicemaster_mikado_options()->getOptionValue('masonry_number_of_chars');
			$number_of_chars['masonry-slider'] = $number_of_chars['masonry'];
		}
		if (servicemaster_mikado_options()->getOptionValue('masonry_number_of_chars')) {
			$number_of_chars['masonry-full-width'] = servicemaster_mikado_options()->getOptionValue('masonry_number_of_chars');
		}

		return $number_of_chars;

	}

}

if (!function_exists('servicemaster_mikado_get_single_post_navigation_template')) {
	/**
	 * Gets previous and next post and loads single post navigation
	 */
	function servicemaster_mikado_get_single_post_navigation_template() {
		$params = array();

		$in_same_term = servicemaster_mikado_options()->getOptionValue('blog_navigation_through_same_category') === 'yes';

		$prev_post = get_previous_post($in_same_term);
		$next_post = get_next_post($in_same_term);
		$params['has_prev_post'] = false;
		$params['has_next_post'] = false;

		if ($prev_post) {
			$params['prev_post_object'] = $prev_post;
			$params['has_prev_post'] = true;
			$params['prev_post'] = array(
				'title' => get_the_title($prev_post->ID),
				'link'  => get_the_permalink($prev_post->ID),
				'image' => get_the_post_thumbnail($prev_post->ID, 'thumbnail'),
				'date'  => get_the_date(get_option('date_format'))
			);

			$params['prev_post_has_image'] = !empty($params['prev_post']['image']);
		}

		if ($next_post) {
			$params['next_post_object'] = $next_post;
			$params['has_next_post'] = true;
			$params['next_post'] = array(
				'title' => get_the_title($next_post->ID),
				'link'  => get_the_permalink($next_post->ID),
				'image' => get_the_post_thumbnail($next_post->ID, 'thumbnail'),
				'date'  => get_the_date(get_option('date_format'))
			);

			$params['next_post_has_image'] = !empty($params['next_post']['image']);
		}

		servicemaster_mikado_get_module_template_part('templates/single/parts/single-navigation', 'blog', '', $params);
	}
}

if (!function_exists('servicemaster_mikado_excerpt_length')) {
	/**
	 * Function that changes excerpt length based on theme options
	 *
	 * @param $length int original value
	 *
	 * @return int changed value
	 */
	function servicemaster_mikado_excerpt_length($length) {

		if (servicemaster_mikado_options()->getOptionValue('number_of_chars') !== '') {
			return esc_attr(servicemaster_mikado_options()->getOptionValue('number_of_chars'));
		} else {
			return 45;
		}
	}

	add_filter('excerpt_length', 'servicemaster_mikado_excerpt_length', 999);
}

if (!function_exists('servicemaster_mikado_excerpt_more')) {
	/**
	 * Function that adds three dotes on the end excerpt
	 *
	 * @param $more
	 *
	 * @return string
	 */
	function servicemaster_mikado_excerpt_more($more) {
		return '...';
	}

	add_filter('excerpt_more', 'servicemaster_mikado_excerpt_more');
}

if (!function_exists('servicemaster_mikado_post_has_read_more')) {
	/**
	 * Function that checks if current post has read more tag set
	 * @return int position of read more tag text. It will return false if read more tag isn't set
	 */
	function servicemaster_mikado_post_has_read_more() {
		global $post;

		return strpos($post->post_content, '<!--more-->');
	}
}

if (!function_exists('servicemaster_mikado_post_has_title')) {
	/**
	 * Function that checks if current post has title or not
	 * @return bool
	 */
	function servicemaster_mikado_post_has_title() {
		return get_the_title() !== '';
	}
}

if (!function_exists('servicemaster_mikado_modify_read_more_link')) {
	/**
	 * Function that modifies read more link output.
	 * Hooks to the_content_more_link
	 * @return string modified output
	 */
	function servicemaster_mikado_modify_read_more_link() {
		$link = '<div class="mkd-more-link-container">';
		if (servicemaster_mikado_core_installed()) {
			$link .= servicemaster_mikado_get_button_html(array(
				'link' => get_permalink() . '#more-' . get_the_ID(),
				'text' => esc_html__('Continue reading', 'servicemaster'),
				'size' => 'small'
			));
		} else {
			$link .= '<a href="' . get_permalink() . '#more-' . get_the_ID() . '" target="_self" class="mkd-btn mkd-btn-small mkd-btn-solid"><span class="mkd-btn-text">' . esc_html__('Continue reading', 'servicemaster') . '</span></a>';
		}

		$link .= '</div>';

		return $link;
	}

	add_filter('the_content_more_link', 'servicemaster_mikado_modify_read_more_link');
}


if (!function_exists('servicemaster_mikado_has_blog_widget')) {
	/**
	 * Function that checks if latest posts widget is added to widget area
	 * @return bool
	 */
	function servicemaster_mikado_has_blog_widget() {
		$widgets_array = array(
			'mkd_latest_posts_widget'
		);

		foreach ($widgets_array as $widget) {
			$active_widget = is_active_widget(false, false, $widget);

			if ($active_widget) {
				return true;
			}
		}

		return false;
	}
}

if (!function_exists('servicemaster_mikado_has_blog_shortcode')) {
	/**
	 * Function that checks if any of blog shortcodes exists on a page
	 * @return bool
	 */
	function servicemaster_mikado_has_blog_shortcode() {
		$blog_shortcodes = array(
			'mkd_blog_list',
			'mkd_blog_slider',
			'mkd_blog_carousel'
		);

        $slider_field = get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_page_slider_meta', true); //TODO change

		foreach ($blog_shortcodes as $blog_shortcode) {
			$has_shortcode = servicemaster_mikado_has_shortcode($blog_shortcode) || servicemaster_mikado_has_shortcode($blog_shortcode, $slider_field);

			if ($has_shortcode) {
				return true;
			}
		}

		return false;
	}
}


if (!function_exists('servicemaster_mikado_load_blog_assets')) {
	/**
	 * Function that checks if blog assets should be loaded
	 *
	 * @see servicemaster_mikado_is_ajax_enabled_is_blog_template()
	 * @see is_home()
	 * @see is_single()
	 * @see mkd_has_blog_shortcode()
	 * @see is_archive()
	 * @see is_search()
	 * @see mkd_has_blog_widget()
	 * @return bool
	 */
	function servicemaster_mikado_load_blog_assets() {
		return servicemaster_mikado_is_blog_template() || is_home() || is_single() || servicemaster_mikado_has_blog_shortcode() || is_archive() || is_search() || servicemaster_mikado_has_blog_widget();
	}
}

if (!function_exists('servicemaster_mikado_is_blog_template')) {
	/**
	 * Checks if current template page is blog template page.
	 *
	 * @param string current page. Optional parameter.
	 *
	 * @return bool
	 *
	 * @see servicemaster_mikado_get_page_template_name()
	 */
	function servicemaster_mikado_is_blog_template($current_page = '') {

		if ($current_page == '') {
			$current_page = servicemaster_mikado_get_page_template_name();
		}

		$blog_templates = servicemaster_mikado_blog_templates();

		return in_array($current_page, $blog_templates);
	}
}

if (!function_exists('servicemaster_mikado_read_more_button')) {
	/**
	 * Function that outputs read more button html if necessary.
	 * It checks if read more button should be outputted only if option for given template is enabled and post does'nt have read more tag
	 * and if post isn't password protected
	 *
	 * @param string $option name of option to check
	 * @param string $class additional class to add to button
	 *
	 */
	function servicemaster_mikado_read_more_button($option = '', $class = '') {
		if ($option != '') {
			$show_read_more_button = servicemaster_mikado_options()->getOptionValue($option) == 'yes';
		} else {
			$show_read_more_button = 'yes';
		}
		if ($show_read_more_button && !servicemaster_mikado_post_has_read_more() && !post_password_required()) {
			echo servicemaster_mikado_get_button_html(array(
				'size'         => 'small',
				'link'         => get_the_permalink(),
				'text'         => esc_html__('Read More', 'servicemaster'),
				'custom_class' => $class
			));
		}
	}
}

if (!function_exists('servicemaster_mikado_set_blog_holder_data_params')) {
	/**
	 * Function which set data params on blog holder div
	 */
	function servicemaster_mikado_set_blog_holder_data_params($type) {

		$load_more_enabled = servicemaster_mikado_load_more_enabled();
		$infinite_scroll_enabled = servicemaster_mikado_infinite_scroll_enabled();

		if ($load_more_enabled || $infinite_scroll_enabled) {
			$current_query = servicemaster_mikado_get_blog_query();

			$data_params = array();
			$data_return_string = '';

			$data_params['data-blog-type'] = $type;

			$paged = servicemaster_mikado_paged();

			$posts_number = '';
			if (get_post_meta(get_the_ID(), "mkd_show_posts_per_page_meta", true) != "") {
				$posts_number = get_post_meta(get_the_ID(), "mkd_show_posts_per_page_meta", true);
			} else {
				$posts_number = get_option('posts_per_page');
			}
			$category = get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_blog_category_meta', true);


			//set data params
			$data_params['data-next-page'] = $paged + 1;
			$data_params['data-max-pages'] = $current_query->max_num_pages;


			if ($posts_number != '') {
				$data_params['data-post-number'] = $posts_number;
			}

			if ($category != '') {
				$data_params['data-category'] = $category;
			}

			if (is_archive()) {

				if (is_category()) {
					$cat_id = get_queried_object_id();
					$data_params['data-archive-category'] = $cat_id;
				}

				if (is_author()) {
					$author_id = get_queried_object_id();
					$data_params['data-archive-author'] = $author_id;
				}

				if (is_tag()) {
					$current_tag_id = get_queried_object_id();
					$data_params['data-archive-tag'] = $current_tag_id;
				}

				if (is_date()) {
					$day = get_query_var('day');
					$month = get_query_var('monthnum');
					$year = get_query_var('year');

					$data_params['data-archive-day'] = $day;
					$data_params['data-archive-month'] = $month;
					$data_params['data-archive-year'] = $year;
				}
			}

			if (is_search()) {
				$search_query = get_search_query();
				$data_params['data-archive-search-string'] = $search_query; // to do, not finished
			}

			foreach ($data_params as $key => $value) {
				if ($key !== '') {
					$data_return_string .= $key . '= ' . esc_attr($value) . ' ';
				}
			}

			return $data_return_string;

		}
	}
}

if (!function_exists('servicemaster_mikado_pagination_type')) {
	function servicemaster_mikado_pagination_type() {
		return servicemaster_mikado_options()->getOptionValue('blog_list_pagination');
	}
}

if (!function_exists('servicemaster_mikado_load_more_enabled')) {
	/**
	 * Function that check if load more is enabled
	 *
	 * return boolean
	 */
	function servicemaster_mikado_load_more_enabled() {
		$load_more_enabled = false;

		if (servicemaster_mikado_pagination_type() == 'load_more') {
			$load_more_enabled = true;
		}

		return $load_more_enabled;
	}
}

if (!function_exists('servicemaster_mikado_infinite_scroll_enabled')) {
	/**
	 * Function that check if infinite scroll is enabled
	 *
	 * return boolean
	 */
	function servicemaster_mikado_infinite_scroll_enabled() {
		$infinite_scroll_enabled = false;

		if (servicemaster_mikado_pagination_type() == 'infinite_scroll') {
			$infinite_scroll_enabled = true;
		}

		return $infinite_scroll_enabled;
	}
}

if (!function_exists('servicemaster_mikado_get_infinite_scroll_trigger')) {

	function servicemaster_mikado_get_infinite_scroll_trigger() {
		$infinite_scroll_enabled = servicemaster_mikado_infinite_scroll_enabled();

		if (is_home() || is_page_template('blog-masonry.php') || is_page_template('blog-masonry-full-width.php') || is_page_template('blog-standard.php')) {
			if ($infinite_scroll_enabled) {
				echo '<div class="mkd-infinite-scroll-trigger"></div>';
			}
		}
	}

	add_action('servicemaster_mikado_generate_scroll_trigger', 'servicemaster_mikado_get_infinite_scroll_trigger');
}

if (!function_exists('servicemaster_mikado_get_load_more_button')) {

	function servicemaster_mikado_get_load_more_button() {
		$load_more_enabled = servicemaster_mikado_load_more_enabled();

		if (is_home() || is_page_template('blog-masonry.php') || is_page_template('blog-masonry-full-width.php') || is_page_template('blog-standard.php')) {
			if ($load_more_enabled) {
				echo servicemaster_mikado_get_load_more_button_html();
			}
		}
	}

	add_action('servicemaster_mikado_generate_load_more_button', 'servicemaster_mikado_get_load_more_button');
}

if (!function_exists('servicemaster_mikado_set_ajax_url')) {
	/**
	 * load themes ajax functionality
	 *
	 */
	function servicemaster_mikado_set_ajax_url() {
		echo '<script type="application/javascript">var MikadoAjaxUrl = "' . admin_url('admin-ajax.php') . '"</script>';
	}

	add_action('wp_enqueue_scripts', 'servicemaster_mikado_set_ajax_url');

}

if (!function_exists('servicemaster_mikado_get_the_author_name')) {
	/**
	 * Returns current author name. Must be called when in loop
	 *
	 * @return string
	 */
	function servicemaster_mikado_get_the_author_name() {
		if (get_the_author_meta('first_name') !== '' || get_the_author_meta('last_name') !== '') {
			$name = get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name');
		} else {
			$name = get_the_author_meta('display_name');
		}

		return $name;
	}
}

if (!function_exists('servicemaster_mikado_get_author_posts_url')) {
	/**
	 * Returns URL to author posts page
	 *
	 * @return string
	 */
	function servicemaster_mikado_get_author_posts_url() {
		return get_author_posts_url(get_the_author_meta('ID'));
	}
}

/**
 * Return Image title single post featured image part.
 *
 */
if (!function_exists('servicemaster_mikado_image_title_featured_image')) {

	function servicemaster_mikado_image_title_featured_image() {
		if (servicemaster_mikado_get_meta_field_intersect('blog_single_type') === 'image-title'):
			$sidebar_layout = servicemaster_mikado_sidebar_layout();
			$image_title_class = '';
			switch ($sidebar_layout):
				case 'sidebar-33-right':
				case 'sidebar-25-right':
					$image_title_class = 'right';
					break;
				case 'sidebar-33-left':
				case 'sidebar-25-left':
					$image_title_class = 'left';
					break;
			endswitch;

			echo '<div class="mkd-post-image-title ' . esc_attr($image_title_class) . '" ';
			echo 'style="background-image:url(';
			the_post_thumbnail_url();
			echo ')">';
			echo '<div class="mkd-post-image-title-overlay"></div>';
			echo '<div class="mkd-container-inner mkd-post-image-title-inner">';
			servicemaster_mikado_get_module_template_part('templates/single/parts/title', 'blog');
			echo '<div class="mkd-post-info">';
			servicemaster_mikado_post_info(array('author' => 'yes', 'date' => 'yes'));
			echo '<div class="mkd-post-info-category">';
			echo '<span class="mkd-post-info-category-icon">';
			echo servicemaster_mikado_icon_collections()->renderIcon('lnr-bookmark', 'linear_icons');
			echo '</span>';
			the_category(', ');
			echo '</div></div></div></div>';
		endif;

	}
}


/**
 * Loads more function for blog posts.
 *
 */
if (!function_exists('servicemaster_mikado_blog_load_more')) {

	function servicemaster_mikado_blog_load_more() {

		$return_obj = array();
		$paged = $post_number = $category = $blog_type = '';
		$archive_category = $archive_author = $archive_tag = $archive_day = $archive_month = $archive_year = '';

		if (!empty($_POST['nextPage'])) {
			$paged = $_POST['nextPage'];
		}
		if (!empty($_POST['number'])) {
			$post_number = $_POST['number'];
		}
		if (!empty($_POST['category'])) {
			$category = $_POST['category'];
		}
		if (!empty($_POST['blogType'])) {
			$blog_type = $_POST['blogType'];
		}
		if (!empty($_POST['archiveCategory'])) {
			$archive_category = $_POST['archiveCategory'];
		}
		if (!empty($_POST['archiveAuthor'])) {
			$archive_author = $_POST['archiveAuthor'];
		}
		if (!empty($_POST['archiveTag'])) {
			$archive_tag = $_POST['archiveTag'];
		}
		if (!empty($_POST['archiveDay'])) {
			$archive_day = $_POST['archiveDay'];
		}
		if (!empty($_POST['archiveMonth'])) {
			$archive_month = $_POST['archiveMonth'];
		}
		if (!empty($_POST['archiveYear'])) {
			$archive_year = $_POST['archiveYear'];
		}


		$html = '';
		$query_array = array(
			'post_type'      => 'post',
			'paged'          => $paged,
			'posts_per_page' => $post_number
		);
		if ($category != '') {
			$query_array['cat'] = $category;
		}
		if ($archive_category != '') {
			$query_array['cat'] = $archive_category;
		}
		if ($archive_author != '') {
			$query_array['author'] = $archive_author;
		}
		if ($archive_tag != '') {
			$query_array['tag'] = $archive_tag;
		}
		if ($archive_day != '' && $archive_month != '' && $archive_year != '') {
			$query_array['day'] = $archive_day;
			$query_array['monthnum'] = $archive_month;
			$query_array['year'] = $archive_year;
		}
		$query_results = new \WP_Query($query_array);

		if ($query_results->have_posts()):
			while ($query_results->have_posts()) : $query_results->the_post();
				$html .= servicemaster_mikado_get_post_format_html($blog_type, 'yes');
			endwhile;
		else:
			$html .= '<p>' . esc_html__('Sorry, no posts matched your criteria.', 'servicemaster') . '</p>';
		endif;

		$return_obj = array(
			'html' => $html,
		);

		echo json_encode($return_obj);
		exit;
	}
}

if (!function_exists('servicemaster_mikado_taxonomy_custom_fields')) {
	function servicemaster_mikado_taxonomy_custom_fields($tag) {
		$t_id = $tag->term_id; // Get the ID of the category you're editing
		$term_meta = get_option("post_tax_term_$t_id");
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="shortcode"><?php esc_html_e('Category color', 'servicemaster'); ?></label>
			</th>
			<td>
				<input style="width:100px" type="text" name="term_meta[category_color]" id="term_meta[category_color]"
					   size="3"
					   value="<?php if (isset($term_meta['category_color']) && $term_meta['category_color'] != '') {
						   echo esc_attr($term_meta['category_color']);
					   } ?>">

				<p class="description"><?php esc_html_e('Define category color using a hexadecimal (HEX) notation for the combination of Red, Green, and Blue color values (RGB). Default value is: #6abb4f', 'servicemaster'); ?></p>
			</td>
		</tr>
	<?php
	}
}

if (!function_exists('servicemaster_mikado_save_taxonomy_custom_fields')) {
	function servicemaster_mikado_save_taxonomy_custom_fields($term_id) {
		if (isset($_POST['term_meta'])) {
			$t_id = $term_id;
			$term_meta = get_option("post_tax_term_$t_id");

			$cat_keys = array_keys($_POST['term_meta']);
			foreach ($cat_keys as $key) {
				if (isset($_POST['term_meta'][$key])) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			update_option("post_tax_term_$t_id", $term_meta);
		}
	}
}

if (!function_exists('servicemaster_mikado_get_category_color')) {
	function servicemaster_mikado_get_category_color($term_id) {
		$cat_array = get_option("post_tax_term_$term_id");

		$category_color = '';
		if (isset($cat_array['category_color']) && $cat_array['category_color'] != '') {
			$category_color = $cat_array['category_color'];
		}

		return $category_color;
	}
}

if (!function_exists('servicemaster_mikado_get_category_color_name')) {
	function servicemaster_mikado_get_category_color_name($category, $count) {
		$category_color = servicemaster_mikado_get_category_color($category->term_id);
		$category_link = get_category_link($category->term_id);
		$category_count = $category->count;
		$category_name = get_cat_name($category->term_id);

		$html = '';
		$html .= '<a href="' . $category_link . '">';
		$html .= '<span class="mkd-category-name" ';
		if ($category_color !== '') {
			$html .= 'style="color:' . $category_color . ';"';
		}
		$html .= '>' . $category_name . ' </span>';
		$html .= $count ? ' (' . $category_count . ')' : '';
		$html .= '</a>';

		return $html;
	}
}

if (!function_exists('servicemaster_mikado_category_color_name')) {
	function servicemaster_mikado_category_color_name($term_id) {
		echo servicemaster_mikado_get_category_color_name($term_id, false);
	}
}

add_action('category_edit_form_fields', 'servicemaster_mikado_taxonomy_custom_fields', 10, 2);
add_action('edited_term', 'servicemaster_mikado_save_taxonomy_custom_fields', 10, 2);


add_action('wp_ajax_nopriv_servicemaster_mikado_blog_load_more', 'servicemaster_mikado_blog_load_more');
add_action('wp_ajax_servicemaster_mikado_blog_load_more', 'servicemaster_mikado_blog_load_more');