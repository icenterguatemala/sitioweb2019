<div <?php servicemaster_mikado_class_attribute($holder_classes); ?> <?php echo servicemaster_mikado_get_inline_attrs($holder_data); ?>>
	<?php if ($query->have_posts()) : ?>
		<?php while ($query->have_posts()) :
			$query->the_post();

			$excerpt = ($text_length > 0) ? substr(get_the_excerpt(), 0, intval($text_length)) : get_the_excerpt(); ?>

			<div class="mkd-blog-slider-item">
				<div class="mkd-categories-list">
					<?php servicemaster_mikado_get_module_template_part('templates/parts/post-info-category', 'blog'); ?>
				</div>
				<h2 class="mkd-blog-slider-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php if ($text_length != '0') : ?>
					<p class="mkd-bs-item-excerpt"><?php echo esc_html($excerpt) ?></p>
				<?php endif; ?>
				<div class="mkd-avatar-date-author">
					<div class="mkd-avatar">
						<a href="<?php echo esc_url(servicemaster_mikado_get_author_posts_url()); ?>">
							<?php echo servicemaster_mikado_kses_img(get_avatar(get_the_author_meta('ID'), 75)); ?>
						</a>
					</div>
					<div class="mkd-date-author">
						<div class="mkd-date">
							<span><?php the_time(get_option('date_format')); ?></span>
						</div>
						<div class="mkd-author">
							<?php echo '<span>' . esc_html__('by', 'servicemaster') . ' </span>';?>
							<a href="<?php echo esc_url(servicemaster_mikado_get_author_posts_url()); ?>">
								<?php echo servicemaster_mikado_get_the_author_name(); ?>
							</a>
						</div>
					</div>
				</div>
			</div>

		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	<?php else: ?>
		<p><?php esc_html_e('No posts were found.', 'servicemaster'); ?></p>
	<?php endif; ?>
</div>