<?php // This line is needed for mixItUp gutter ?>
<?php
$video_type = servicemaster_mikado_get_portfolio_single_type() === 'video';
$background_color = get_post_meta(get_the_ID(), 'portfolio_background_color', true);
?>
	<article <?php echo mkd_core_get_class_attribute($item_classes); ?>>
		<div class="mkd-portfolio-standard-item">
			<div class="mkd-ptf-wrapper">
				<div class="mkd-ptf-item-image-holder">
					<?php if ($standard_boxed === 'yes'): ?>
					<a <?php echo servicemaster_mikado_get_inline_attrs($link_atts); ?>>
						<?php endif; ?>
						<?php if ($use_custom_image_size && (is_array($custom_image_sizes) && count($custom_image_sizes))) : ?>
							<?php echo servicemaster_mikado_generate_thumbnail(get_post_thumbnail_id(get_the_ID()), null, $custom_image_sizes[0], $custom_image_sizes[1]); ?>
						<?php else: ?>
							<?php the_post_thumbnail($thumb_size); ?>
						<?php endif; ?>
						<?php if ($video_type): ?>
							<div class="mkd-portfolio-video">
								<div class="mkd-portfolio-video-inner">
									<span class="arrow_triangle-right"></span>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($standard_boxed !== 'yes'): ?>
							<div class="mkd-portfolio-standard-overlay" style="background-color: <?php esc_attr_e($standard_background_color); ?>">
								<a class="lightbox mkd-portfolio-lightbox"
								   href="<?php echo get_the_post_thumbnail_url(get_the_ID()); ?>"
								   data-rel="prettyPhoto[portfolio_standard_pretty_photo]">
								<span class="mkd-overlay-icon">
									<i class="mkd-icon-linear-icon lnr lnr-cross"></i>
								</span>
								</a>
							</div>
						<?php endif; ?>
						<?php if ($standard_boxed === 'yes'): ?>
					</a>
				<?php endif; ?>
				</div>
				<div class="mkd-ptf-item-text-holder" style="text-align: <?php esc_attr_e($params['text_align']); ?>">
					<<?php echo esc_attr($title_tag); ?> class="mkd-ptf-item-title">
					<a <?php echo servicemaster_mikado_get_inline_attrs($link_atts); ?>>
						<?php echo esc_attr(get_the_title()); ?>
					</a>
				</<?php echo esc_attr($title_tag); ?>>


				<?php if ($text_length != '0') {
					$excerpt = ($text_length > 0) ? substr(get_the_excerpt(), 0, intval($text_length)) : get_the_excerpt(); ?>
					<p class="mkd-ptf-item-excerpt"><?php echo esc_html($excerpt) ?></p>
				<?php } ?>

				<?php if ($show_categories === 'yes') : ?>
					<?php if (!empty($category_html)) : ?>
						<?php print $category_html; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		</div>
	</article>
<?php // This line is needed for mixItUp gutter ?>