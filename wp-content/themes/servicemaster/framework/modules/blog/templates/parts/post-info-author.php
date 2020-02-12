<div class="mkd-post-info-author mkd-post-info-item">
	<div class="mkd-avatar">
		<a href="<?php echo esc_url(servicemaster_mikado_get_author_posts_url()); ?>">
			<?php echo servicemaster_mikado_kses_img(get_avatar(get_the_author_meta('ID'), 40)); ?>
		</a>
	</div>
	<div class="mkd-author">
		<?php echo '<span>'.esc_html__('by','servicemaster').' </span>';?>
		<a href="<?php echo esc_url(servicemaster_mikado_get_author_posts_url()); ?>">
			<?php echo servicemaster_mikado_get_the_author_name(); ?>
		</a>
	</div>
</div>
