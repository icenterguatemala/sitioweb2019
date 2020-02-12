<?php if (servicemaster_mikado_options()->getOptionValue('blog_single_navigation') == 'yes') { ?>
	<?php $navigation_blog_through_category = servicemaster_mikado_options()->getOptionValue('blog_navigation_through_same_category') ?>
	<div class="mkd-blog-single-navigation clearfix">
		<div class="mkd-blog-single-navigation-inner clearfix">
			<div class="mkd-container-inner">
				<?php if ($has_prev_post) : ?>
					<div class="mkd-blog-single-prev clearfix <?php if ($prev_post_has_image) {
						echo esc_attr('mkd-single-nav-with-image');
					} ?>">
						<?php if ($prev_post_has_image) : ?>
							<div class="mkd-single-nav-image-holder">
								<a href="<?php echo esc_url($prev_post['link']); ?>">
									<?php echo servicemaster_mikado_kses_img($prev_post['image']); ?>
								</a>
							</div>
						<?php endif; ?>

						<div class="mkd-single-nav-content-holder">
							<h5 class="mkd-navigation-post-title">
								<a href="<?php echo esc_url($prev_post['link']); ?>">
									<?php echo esc_html($prev_post['title']); ?>
								</a>
							</h5>
							<span class="mkd-navigation-post-date"><?php echo esc_attr($prev_post['date']); ?></span>
						</div>
					</div> <!-- close div.blog_prev -->
				<?php endif; ?>
				<?php if ($has_next_post) : ?>
					<div class="mkd-blog-single-next clearfix <?php if ($next_post_has_image) {
						echo esc_attr('mkd-single-nav-with-image');
					} ?>">
						<?php if ($next_post_has_image) : ?>
							<div class="mkd-single-nav-image-holder">
								<a href="<?php echo esc_url($next_post['link']); ?>">
									<?php echo servicemaster_mikado_kses_img($next_post['image']); ?>
								</a>
							</div>
						<?php endif; ?>

						<div class="mkd-single-nav-content-holder">
							<h5 class="mkd-navigation-post-title">
								<a href="<?php echo esc_url($next_post['link']); ?>">
									<?php echo esc_html($next_post['title']); ?>
								</a>
							</h5>
							<span class="mkd-navigation-post-date"><?php echo esc_attr($next_post['date']); ?></span>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php } ?>