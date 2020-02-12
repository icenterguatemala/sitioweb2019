<?php
/*
Template Name: Blog: Masonry Full Width
*/

get_header();
servicemaster_mikado_get_title();
get_template_part('slider');
?>
	<div class="mkd-full-width">
		<div class="mkd-full-width-inner clearfix">
			<?php if (have_posts()) : while (have_posts()) : the_post();
				the_content();
				servicemaster_mikado_get_blog('masonry-full-width');
			endwhile; endif; ?>
			<div class="mkd-blog-after-content">
				<div class="mkd-container-inner">
					<?php do_action('servicemaster_mikado_page_after_content'); ?>
				</div>
			</div>
		</div>
	</div>
<?php get_footer(); ?>