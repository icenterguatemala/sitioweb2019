<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="mkd-post-content">
		<div class="mkd-post-image-video">
			<?php
			servicemaster_mikado_get_module_template_part('templates/lists/parts/image', 'blog');

			$_video_type = get_post_meta(get_the_ID(), "mkd_video_type_meta", true);
			$_video_link_meta = get_post_meta(get_the_ID(), "mkd_post_video_id_meta", true);
			$_video_link = $_video_link_meta !== '' ? $_video_link_meta : '#';

			if ($_video_type == "youtube") {
				$_video_link = 'https://www.youtube.com/watch?v=' . $_video_link_meta;
			} elseif ($_video_type == "vimeo") {
				$_video_link = 'https://www.vimeo.com/' . $_video_link_meta;
			} elseif ($_video_type == "self") {
				$_video_link = get_post_meta(get_the_ID(), "mkd_post_video_mp4_link_meta", true) . '?iframe=true&width50%&height=50%';
			}
			?>
			<div class="mkd-post-video">
				<a class="mkd-video-post-link" href="<?php echo esc_html($_video_link); ?>"
				   data-rel="prettyPhoto[<?php the_ID(); ?>]">
					<?php echo servicemaster_mikado_icon_collections()->renderIcon('arrow_triangle-right', 'font_elegant'); ?>
				</a>
			</div>
		</div>

		<div class="mkd-post-text">
			<div class="mkd-post-text-inner">
				<div class="mkd-categories-list">
					<?php servicemaster_mikado_get_module_template_part('templates/parts/post-info-category', 'blog'); ?>
				</div>
				<?php servicemaster_mikado_get_module_template_part('templates/lists/parts/title', 'blog'); ?>
				<?php
				servicemaster_mikado_excerpt($excerpt_length);
				$args_pages = array(
					'before'      => '<div class="mkd-single-links-pages"><div class="mkd-single-links-pages-inner">',
					'after'       => '</div></div>',
                    'link_before' => '<span>' . esc_html__('Post Page Link: ', 'servicemaster'),
					'link_after'  => '</span> ',
					'pagelink'    => '%'
				);

				wp_link_pages($args_pages);
				?>
			</div>
			<div class="mkd-post-info">
				<?php servicemaster_mikado_post_info(array(
					'date'     => 'yes',
					'comments' => (servicemaster_mikado_options()->getOptionValue('blog_single_comments') == 'yes') ? 'yes' : 'no',
					'share'    => 'yes'))
				?>
			</div>
		</div>
	</div>
</article>