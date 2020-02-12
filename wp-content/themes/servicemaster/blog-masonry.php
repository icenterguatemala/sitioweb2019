<?php
/*
Template Name: Blog: Masonry
*/
get_header();
servicemaster_mikado_get_title();
get_template_part('slider');
?>
	<div class="mkd-container">
		<?php do_action('servicemaster_mikado_after_container_open');
		if (have_posts()) : while (have_posts()) : the_post();
			the_content(); ?>
			<div class="mkd-container-inner">
				<?php servicemaster_mikado_get_blog('masonry'); ?>
			</div>
		<?php endwhile; endif; ?>
		<div class="mkd-blog-after-content">
			<div class="mkd-container-inner">
				<?php do_action('servicemaster_mikado_page_after_content'); ?>
			</div>
		</div>
		<?php do_action('servicemaster_mikado_before_container_close'); ?>
	</div>
<?php get_footer(); ?>