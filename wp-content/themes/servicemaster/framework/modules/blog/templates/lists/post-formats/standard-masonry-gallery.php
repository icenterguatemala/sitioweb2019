<article id="post-<?php the_ID(); ?>" <?php post_class($post_class); ?>>
	<?php servicemaster_mikado_get_module_template_part('templates/lists/parts/image', 'blog', '', array('image_size' => $image_size)); ?>
	<div class="mkd-title-date">
		<?php servicemaster_mikado_get_module_template_part('templates/lists/parts/title', 'blog'); ?>
		<span class="mkd-date"><?php the_time(get_option('date_format')); ?></span>
	</div>
</article>