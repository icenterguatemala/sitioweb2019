<?php $link_color = get_post_meta(get_the_ID(), "mkd_post_link_color", true); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="mkd-post-content" <?php if ($link_color !== ''): ?> style="background-color: <?php echo esc_html($link_color); ?>" <?php endif; ?>>
		<div class="mkd-post-text">
			<div class="mkd-post-text-inner">
				<div class="mkd-post-mark">
					<span aria-hidden="true" class="icon_link"></span>
				</div>
				<?php servicemaster_mikado_get_module_template_part('templates/lists/parts/title', 'blog'); ?>
			</div>
		</div>
	</div>
</article>