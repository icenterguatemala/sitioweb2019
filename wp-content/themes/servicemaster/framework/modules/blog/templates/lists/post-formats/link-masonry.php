<?php $link_color = get_post_meta(get_the_ID(), "mkd_post_link_color", true); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="mkd-post-content">
        <div class="mkd-post-text" <?php if ($link_color !== ''): ?> style="background-color: <?php echo esc_html($link_color); ?>" <?php endif; ?>>
            <div class="mkd-post-text-inner">
				<div class="mkd-categories-list">
					<?php servicemaster_mikado_get_module_template_part('templates/parts/post-info-category', 'blog'); ?>
				</div>
                <div class="mkd-post-mark">
                    <span aria-hidden="true" class="icon_link"></span>
                </div>
				<?php servicemaster_mikado_get_module_template_part('templates/lists/parts/title', 'blog'); ?>
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