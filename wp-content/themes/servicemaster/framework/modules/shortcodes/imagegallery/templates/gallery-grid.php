<div class="mkd-image-gallery">
	<div
		class="mkd-image-gallery-grid <?php echo esc_html($columns); ?><?php echo esc_attr($space); ?> <?php echo esc_html($gallery_classes); ?>">
		<?php foreach ($images as $image) { ?>
			<div class="mkd-gallery-image <?php echo esc_attr($hover_overlay); ?>">
				<div class="mkd-image-gallery-holder">
					<?php if ($pretty_photo) { ?>
					<a href="<?php echo esc_url($image['url']) ?>" data-rel="prettyPhoto[single_pretty_photo]"
					   title="<?php echo esc_attr($image['title']); ?>">
						<?php
						$attachment = get_post($image['image_id']);
						?>
						<div
							class="mkd-icon-holder"><?php echo servicemaster_mikado_icon_collections()->renderIcon('icon_plus', 'font_elegant'); ?></div>
						<?php } ?>
						<?php if (is_array($image_size) && count($image_size)) : ?>
							<?php echo servicemaster_mikado_generate_thumbnail($image['image_id'], null, $image_size[0], $image_size[1]); ?>
						<?php else: ?>
							<?php echo wp_get_attachment_image($image['image_id'], $image_size); ?>
						<?php endif; ?>
						<?php if($show_title_desc === 'yes'): ?>
						<div class="mkd-title-description">
							<div class="mkd-title-description-inner">
								<div class="mkd-image-gallery-title-holder clearfix">
									<h2 class="mkd-image-gallery-title">
										<?php
										echo esc_html($attachment->post_title);
										?>
									</h2>
								</div>
								<div class="mkd-image-gallery-description-holder clearfix">
									<p class="mkd-image-gallery-description">
										<?php
										echo esc_html($attachment->post_content);
										?>
									</p>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<?php if ($grayscale !== 'yes'): ?>
							<span class="mkd-image-gallery-hover"></span>
						<?php endif; ?>
						<?php if ($pretty_photo) { ?>
					</a>
				<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>