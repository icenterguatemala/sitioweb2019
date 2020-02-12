<div <?php servicemaster_mikado_class_attribute($holder_classes); ?>>
	<div class="mkd-pl-outer clearfix">
		<div class="mkd-product-list-masonry-grid-sizer"></div>
		<?php if ($query_result->have_posts()): while ($query_result->have_posts()) :
			$query_result->the_post(); ?>
			<?php
			$product = servicemaster_mikado_return_woocommerce_global_variable();

			$current_id = get_the_ID();

			$thumb_size = $productListObject->getMasonryProductListThumbnail($current_id);

			$rating_enabled = false;
			if ($show_rating == 'yes' && get_option('woocommerce_enable_review_rating') !== 'no') {
				$rating_enabled = true;
				$average = $product->get_average_rating();
			}
			?>
			<div class="mkd-pl-item <?php echo esc_attr($thumb_size);?>">
				<div class="mkd-pl-item-inner">
					<a class="mkd-product-thumbnail-link" href="<?php the_permalink(); ?>"
					   title="<?php the_title_attribute(); ?>">
						<div class="product-thumbnail">
							<?php echo get_the_post_thumbnail(get_the_ID(), $thumb_size); ?>
						</div>
						<?php if (get_post_meta($product->get_id(), 'mkd_single_product_new_meta', true) === 'yes') : ?>
							<span class="mkd-new-product mkd-product-mark">
								<?php esc_html_e('NEW', 'servicemaster'); ?>
							</span>
						<?php endif;?>
						<?php if (!$product->is_in_stock()) : ?>
							<span class="mkd-out-of-stock mkd-product-mark">
								<?php esc_html_e('SOLD OUT', 'servicemaster'); ?>
							</span>
						<?php endif; ?>
						<?php if ($product->is_on_sale()) : ?>
							<span class="mkd-on-sale mkd-product-mark">
								<?php esc_html_e('SALE', 'servicemaster'); ?>
							</span>
						<?php endif; ?>
					</a>

					<div class="add-to-cart-holder">
						<?php
						echo sprintf('<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s" data-title="%s">%s</a>',
							esc_url($product->add_to_cart_url()),
							esc_attr(isset($quantity) ? $quantity : 1),
							esc_attr($product->get_id()),
							esc_attr($product->get_sku()),
							esc_attr('mkd-btn mkd-btn-small mkd-btn-solid add_to_cart_button ajax_add_to_cart'),
							esc_html($product->add_to_cart_text()),
							esc_html($product->add_to_cart_text())
						);
						?>
					</div>
					<div class="mkd-pl-content-holder"
						 style="background-color: <?php echo esc_attr($box_background_color);?>">
						<div class="mkd-pl-content-holder-inner"
							 style="background-color: <?php echo esc_attr($box_background_color);?>">
							<<?php echo esc_html($title_tag); ?> class="product-title"><a href="<?php the_permalink(); ?>"
														 title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</<?php echo esc_html($title_tag); ?>>

							<div class="product-price">
								<?php print $product->get_price_html();?>
							</div>
							<?php if ($rating_enabled) { ?>
								<div class="mkd-pl-rating-holder">
									<div class="star-rating"
										 title="<?php printf(esc_html__('Rated %s out of 5', 'servicemaster'), $average); ?>">
										<span style="width:<?php echo(($average / 5) * 100); ?>%"></span>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		<?php endwhile;
		else: ?>
			<div class="mkd-pl-messsage">
				<p><?php esc_html_e('No posts were found.', 'servicemaster'); ?></p>
			</div>
		<?php endif;
		wp_reset_postdata();
		?>
	</div>
</div>