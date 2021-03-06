<?php
/*
Template Name: Full Width
*/

$mkd_sidebar = servicemaster_mikado_sidebar_layout();
get_header();
servicemaster_mikado_get_title();
get_template_part('slider');
?>

	<div class="mkd-full-width">
		<div class="mkd-full-width-inner">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<div class="mkd-grid-row">
					<div <?php echo servicemaster_mikado_get_content_sidebar_class(); ?>>
						<?php the_content(); ?>
						<?php do_action('servicemaster_mikado_page_after_content'); ?>
					</div>

					<?php if (!in_array($mkd_sidebar, array('default', ''))) : ?>
						<div <?php echo servicemaster_mikado_get_sidebar_holder_class(); ?>>
							<?php get_sidebar(); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endwhile; endif; ?>
		</div>
	</div>
<?php get_footer(); ?>