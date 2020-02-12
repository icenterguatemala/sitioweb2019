<?php
$filter = esc_attr(servicemaster_mikado_options()->getOptionValue('masonry_filter'));

if($filter == 'yes') {
    $page_category = get_post_meta(servicemaster_mikado_get_page_id(), "mkd_blog_category_meta", true);
    if(is_category()) {
        $page_category = get_query_var('cat');
    }
    if($page_category == "" && !is_category()) {
        $args       = array(
            'parent' => 0
        );
        $categories = get_categories($args);
    } else {
        $args       = array(
            'parent' => $page_category
        );
        $categories = get_categories($args);
    }
    if(count($categories) > 0) { ?>
        <div class="mkd-filter-blog-holder">
            <div class="mkd-filter-blog">
                <ul>
                    <li class="mkd-filter mkd-active" data-filter="*">
                        <span><?php esc_html_e('All', 'servicemaster'); ?></span></li>
                    <?php foreach($categories as $category) { ?>
                        <li class="mkd-filter" data-filter="<?php echo ".category-".esc_attr($category->slug); ?>">
                            <span><?php echo esc_html($category->name); ?></span></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php }
}
?>
