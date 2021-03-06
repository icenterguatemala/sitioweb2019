<?php // This line is needed for mixItUp gutter ?>
	<article <?php echo mkd_core_get_class_attribute($item_classes); ?>>
		<div class="mkd-portfolio-gallery-item">
			<div class="mkd-ptf-wrapper">
				<a class="mkd-portfolio-link" <?php echo servicemaster_mikado_get_inline_attrs($link_atts); ?>></a>
				<div class="mkd-ptf-item-image-holder">
					<?php if ($use_custom_image_size && (is_array($custom_image_sizes) && count($custom_image_sizes))) : ?>
						<?php echo servicemaster_mikado_generate_thumbnail(get_post_thumbnail_id(get_the_ID()), null, $custom_image_sizes[0], $custom_image_sizes[1]); ?>
					<?php else: ?>
						<?php the_post_thumbnail($thumb_size); ?>
					<?php endif; ?>
				</div>
				<div class="mkd-ptf-item-text-overlay" <?php servicemaster_mikado_inline_style($shader_styles); ?>>
					<div class="mkd-ptf-item-text-overlay-inner">
						<div class="mkd-ptf-item-text-holder">
							<<?php echo esc_attr($title_tag); ?> class="mkd-ptf-item-title">
								<a <?php echo servicemaster_mikado_get_inline_attrs($link_atts); ?>>
									<?php echo esc_attr(get_the_title()); ?>
								</a>
							</<?php echo esc_attr($title_tag); ?>>
							<?php if ($show_categories === 'yes') : ?>
								<?php if (!empty($category_html)) : ?>
									<?php print $category_html; ?>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</article>
<?php // This line is needed for mixItUp gutter ?>