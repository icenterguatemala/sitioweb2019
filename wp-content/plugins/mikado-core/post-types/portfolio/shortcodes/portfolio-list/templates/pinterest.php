<?php // This line is needed for mixItUp gutter ?>
	<article <?php echo mkd_core_get_class_attribute($item_classes); ?>>
		<div class="mkd-ptf-wrapper">
			<a class="mkd-portfolio-link" <?php echo servicemaster_mikado_get_inline_attrs($link_atts); ?>></a>

			<div class="mkd-ptf-item-image-holder">
				<?php echo get_the_post_thumbnail(get_the_ID(), $thumb_size); ?>
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
				<div class="mkd-ptf-item-overlay-bg"></div>
			</div>
		</div>
	</article>
<?php // This line is needed for mixItUp gutter ?>