<div <?php servicemaster_mikado_class_attribute($holder_classes); ?> <?php echo servicemaster_mikado_get_inline_attrs($holder_data); ?>>
	<?php if ($query->have_posts()) : ?>
		<?php while ($query->have_posts()) :
			$query->the_post();

			servicemaster_mikado_get_post_format_html('masonry-slider');

		endwhile; ?>
		<?php wp_reset_postdata(); ?>
	<?php else: ?>
		<p><?php esc_html_e('No posts were found.', 'servicemaster'); ?></p>
	<?php endif; ?>
</div>