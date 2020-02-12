<div <?php mkd_core_class_attribute($holder_classes); ?>>
	<?php if($query->have_posts()) : ?>
		<div class="mkd-portfolio-slider-list" <?php echo mkd_core_get_inline_attrs($holder_data); ?>>
			<?php while($query->have_posts()) : $query->the_post(); ?>
				<?php echo mkd_core_get_shortcode_module_template_part('portfolio-slider/templates/portfolio-slider-item', 'portfolio', '', $params); ?>
			<?php endwhile; ?>
		</div>
		<?php wp_reset_postdata(); ?>
	<?php else: ?>
		<p><?php esc_html_e('Sorry, no posts matched your criteria.', 'mikado-core'); ?></p>
	<?php endif; ?>
</div>