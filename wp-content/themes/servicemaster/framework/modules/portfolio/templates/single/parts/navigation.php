<?php if (servicemaster_mikado_options()->getOptionValue('portfolio_single_hide_pagination') !== 'yes') : ?>

    <?php
    $back_to_link = get_post_meta(get_the_ID(), 'portfolio_single_back_to_link', true);
    $nav_same_category = servicemaster_mikado_options()->getOptionValue('portfolio_single_nav_same_category') == 'yes';
    ?>

    <div class="mkd-portfolio-single-nav">
        <div class="mkd-container-inner clearfix">
            <?php if ($has_prev_post) : ?>
                <div class="mkd-portfolio-prev <?php if ($prev_post_has_image) {
                    echo esc_attr('mkd-single-nav-with-image');
                } ?>">
                    <?php if ($prev_post_has_image) : ?>
                        <div class="mkd-single-nav-image-holder">
                            <a href="<?php echo esc_url($prev_post['link']); ?>">
                                <?php echo servicemaster_mikado_kses_img($prev_post['image']); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="mkd-single-nav-content-holder">
                        <h5>
                            <a href="<?php echo esc_url($prev_post['link']); ?>">
                                <?php echo esc_html($prev_post['title']); ?>
                            </a>
                        </h5>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($back_to_link !== '') : ?>
                <div class="mkd-portfolio-back-btn">
                    <a href="<?php echo esc_url(get_permalink($back_to_link)); ?>">
                        <?php esc_html_e('MAIN LIST', 'servicemaster'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($has_next_post) : ?>
                <div class="mkd-portfolio-next <?php if ($next_post_has_image) {
                    echo esc_attr('mkd-single-nav-with-image');
                } ?>">
                    <div class="mkd-single-nav-content-holder">
                        <h5>
                            <a href="<?php echo esc_url($next_post['link']); ?>">
                                <?php echo esc_html($next_post['title']); ?>
                            </a>
                        </h5>
                    </div>
                    <?php if ($next_post_has_image) : ?>
                        <div class="mkd-single-nav-image-holder">
                            <a href="<?php echo esc_url($next_post['link']); ?>">
                                <?php echo servicemaster_mikado_kses_img($next_post['image']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>