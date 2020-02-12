<div <?php servicemaster_mikado_class_attribute($holder_classes); ?>>
	<div class="mkd-pl-outer clearfix">
		<?php if ($query_result->have_posts()): while ($query_result->have_posts()) :
			$query_result->the_post(); ?>
			<?php
			$product = servicemaster_mikado_return_woocommerce_global_variable();
			?>
			<div class="mkd-pl-item">
				<div class="mkd-pl-item-inner">
					<div class="product-thumbnail">
						<?php echo get_the_post_thumbnail(get_the_ID(), 'shop_single'); ?>
					</div>
					<a class="mkd-product-thumbnail-link" href="<?php the_permalink(); ?>"
					   title="<?php the_title_attribute(); ?>">
					</a>
					<a class="lightbox" href="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'shop_single'); ?>"
					   data-rel="prettyPhoto[loolbook_pretty_photo]">
						<span aria-hidden="true" class="mkd-icon-font-elegant icon_plus "></span>
					</a>
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