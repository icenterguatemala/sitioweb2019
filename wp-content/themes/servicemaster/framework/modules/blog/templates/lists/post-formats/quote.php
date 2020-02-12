<?php $quote_color = get_post_meta(get_the_ID(), "mkd_post_quote_color", true); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="mkd-post-content" <?php if ($quote_color !== ''): ?> style="background-color: <?php echo esc_html($quote_color); ?>" <?php endif; ?>>
		<div class="mkd-post-text">
			<div class="mkd-post-text-inner">
				<div class="mkd-post-mark">
					<span aria-hidden="true" class="icon_quotations"></span>
				</div>
				<h2 class="mkd-post-title">
					<a href="<?php the_permalink(); ?>"
					   title="<?php the_title_attribute(); ?>"><?php echo esc_html(get_post_meta(get_the_ID(), "mkd_post_quote_text_meta", true)); ?></a>
				</h2>
			</div>
		</div>
	</div>
</article>