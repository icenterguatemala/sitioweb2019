<?php
$quote_style = '';
$quote_color = get_post_meta(get_the_ID(), 'mkd_post_quote_color', true);
if ($quote_color !== '') {
	$quote_style = 'background-color: ' . esc_attr($quote_color);
}
$quote_image = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'full');
if (isset($quote_image) && is_array($quote_image)) {
	$quote_style = 'background-image: url(' . esc_url($quote_image[0]) . ')';
}
?>
<article
	id="post-<?php the_ID(); ?>" <?php post_class($post_class); ?> <?php echo servicemaster_mikado_get_inline_style($quote_style) ?>>
	<div class="mkd-masonry-gallery-quote-author">
		<div class="mkd-masonry-gallery-quote-author-inner">
			<h2 class="mkd-masonry-gallery-quote">
				<span>"<?php echo esc_html(get_post_meta(get_the_ID(), "mkd_post_quote_text_meta", true)); ?>"</span>
			</h2>

			<div class="mkd-masonry-gallery-author">
				<span><?php the_title(); ?></span>
			</div>
		</div>
	</div>
</article>