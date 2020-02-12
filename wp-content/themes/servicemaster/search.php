<?php
$mkd_sidebar = servicemaster_mikado_sidebar_layout();
$mkd_excerpt_length_array = servicemaster_mikado_blog_lists_number_of_chars();

$mkd_excerpt_length = 0;
if (is_array($mkd_excerpt_length_array) && array_key_exists('standard', $mkd_excerpt_length_array)) {
    $mkd_excerpt_length = $mkd_excerpt_length_array['standard'];
}

?>

<?php get_header(); ?>
<?php
global $wp_query;

if (get_query_var('paged')) {
    $mkd_paged = get_query_var('paged');
} elseif (get_query_var('page')) {
    $mkd_paged = get_query_var('page');
} else {
    $mkd_paged = 1;
}

if (servicemaster_mikado_options()->getOptionValue('blog_page_range') != "") {
    $mkd_blog_page_range = esc_attr(servicemaster_mikado_options()->getOptionValue('blog_page_range'));
} else {
    $mkd_blog_page_range = $wp_query->max_num_pages;
}
?>
<?php servicemaster_mikado_get_title(); ?>
    <div class="mkd-container">
        <?php do_action('servicemaster_mikado_action_after_container_open'); ?>
        <div class="mkd-container-inner clearfix">

            <div class="mkd-grid-row">

                <div class="mkd-page-content-holder mkd-grid-col-9">
                    <h2><?php echo esc_html__('Results for: ', 'servicemaster');
                        echo get_search_query() ?></h2>

                    <div class="mkd-search-holder">

                        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                                <span class="mkd-date"><?php the_time('d.m.y'); ?></span>
                                <h4><a href="<?php the_permalink(); ?>"
                                       title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
                                <?php $my_excerpt = get_the_excerpt();
                                if ($my_excerpt != '') { ?>
                                    <p class="mkd-post-excerpt"><?php echo esc_html($my_excerpt); ?></p>
                                <?php }
                                $args_pages = array(
                                    'before'      => '<div class="mkd-single-links-pages"><div class="mkd-single-links-pages-inner">',
                                    'after'       => '</div></div>',
                                    'link_before' => '<span>' . esc_html__('Post Page Link: ', 'servicemaster'),
                                    'link_after'  => '</span>',
                                    'pagelink'    => '%'
                                );

                                wp_link_pages($args_pages);
                                ?>
                            </article>
                        <?php
                        endwhile;
                            if (servicemaster_mikado_options()->getOptionValue('pagination') == 'yes') :
                                servicemaster_mikado_pagination($wp_query->max_num_pages, $mkd_blog_page_range, $mkd_paged);
                            endif;
                        else:
                            servicemaster_mikado_get_module_template_part('templates/parts/no-posts', 'blog');
                        endif;
                        ?>
                    </div>
                </div>

                <div class="mkd-sidebar-holder mkd-grid-col-3">
                    <aside class="mkd-sidebar">
                        <?php if (servicemaster_mikado_options()->getOptionValue('search_page_custom_sidebar') != '') {
                            echo servicemaster_mikado_get_dynamic_sidebar(servicemaster_mikado_options()->getOptionValue('search_page_custom_sidebar'));
                        } else {
                            get_sidebar();
                        } ?>
                    </aside>
                </div>


            </div>

        </div>
        <?php do_action('servicemaster_mikado_before_container_close'); ?>
    </div>
<?php get_footer(); ?>