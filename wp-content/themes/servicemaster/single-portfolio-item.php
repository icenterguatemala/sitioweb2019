<?php
get_header();
if (have_posts()) : while (have_posts()) : the_post();
	servicemaster_mikado_get_title();
    get_template_part('slider');
	servicemaster_mikado_single_portfolio();
endwhile; endif;
get_footer();