<?php $link_color = get_post_meta(get_the_ID(), "mkd_post_link_color", true); ?>
<div class="mkd-container-inner">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="mkd-post-content">
			<div
				class="mkd-post-link clearfix" <?php if ($link_color !== ''): ?> style="background-color: <?php echo esc_html($link_color); ?>" <?php endif; ?>>
				<div class="mkd-post-mark">
					<?php echo servicemaster_mikado_icon_collections()->renderIcon('icon_link', 'font_elegant'); ?>
				</div>
				<h1 class="mkd-post-title">
					<a href="<?php echo esc_html(get_post_meta(get_the_ID(), "mkd_post_link_link_meta", true)); ?>"
					   title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
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