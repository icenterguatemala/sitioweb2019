<?php
get_header();
if (have_posts()) :
	while (have_posts()) : the_post();
		servicemaster_mikado_get_title();
        get_template_part('slider'); ?>
		<div class="mkd-container">
			<?php servicemaster_mikado_image_title_featured_image(); ?>
			<div class="mkd-container-inner">
				<?php
				do_action('servicemaster_mikado_after_container_open');
				servicemaster_mikado_get_blog_single();
				do_action('servicemaster_mikado_before_container_close');
				?>
			</div>
			<?php servicemaster_mikado_get_single_post_navigation_template(); ?>
		</div>
	<?php
	endwhile; endif;
get_footer();