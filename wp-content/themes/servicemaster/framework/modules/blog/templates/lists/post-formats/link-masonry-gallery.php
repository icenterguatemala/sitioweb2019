<?php
$link_style = '';
$link_color = get_post_meta(get_the_ID(), "mkd_post_link_color", true);
if($link_color !== ''){
	$link_style = 'background-color: '.esc_attr($link_color);
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class($post_class); ?> <?php echo servicemaster_mikado_get_inline_style($link_style)?>>
	<div class="mkd-masonry-gallery-link">
		<div class="mkd-masonry-gallery-link-inner">
			<?php servicemaster_mikado_get_module_template_part('templates/lists/parts/title', 'blog'); ?>
		</div>
	</div>
</article>