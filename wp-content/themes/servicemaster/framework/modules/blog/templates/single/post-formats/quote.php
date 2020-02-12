<?php $quote_color = get_post_meta(get_the_ID(), "mkd_post_quote_color", true); ?>
<div class="mkd-container-inner">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="mkd-post-content">
			<div class="mkd-post-quote clearfix" <?php if ($quote_color !== ''): ?> style="background-color: <?php echo esc_html($quote_color); ?>" <?php endif; ?>>
				<div class="mkd-post-mark">
					<?php echo servicemaster_mikado_icon_collections()->renderIcon('icon_quotations', 'font_elegant'); ?>
				</div>
				<h1 class="mkd-post-title">
					<span>"</span>
					<a href="<?php the_permalink(); ?>"
					   title="<?php the_title_attribute(); ?>"><?php echo esc_html(get_post_meta(get_the_ID(), "mkd_post_quote_text_meta", true)); ?></a>
					<span>"</span>
				</h1>
			</div>
			<div class="mkd-post-text">
				<div class="mkd-post-text-inner clearfix">
					<div class="mkd-post-info">
						<?php servicemaster_mikado_post_info(array('date' => 'yes')) ?>
						<div class="mkd-post-info-category">
							<?php echo servicemaster_mikado_icon_collections()->renderIcon('lnr-bookmark', 'linear_icons'); ?>
							<?php the_category(', '); ?>
						</div>
					</div>
					<?php the_content(); ?>
				</div>
				<div class="mkd-tags-share-holder clearfix">
					<?php do_action('servicemaster_mikado_before_blog_article_closed_tag'); ?>
					<div class="mkd-share-icons-single">
						<?php $post_info_array['share'] = servicemaster_mikado_options()->getOptionValue('enable_social_share') == 'yes'; ?>
						<?php if ($post_info_array['share'] == 'yes'): ?>
							<span class="mkd-share-label"><?php esc_html_e('Share', 'servicemaster'); ?></span>
						<?php endif; ?>
						<?php echo servicemaster_mikado_get_social_share_html(array(
							'type'      => 'list',
							'icon_type' => 'normal'
						)); ?>
					</div>
				</div>
			</div>
		</div>
	</article>
</div>