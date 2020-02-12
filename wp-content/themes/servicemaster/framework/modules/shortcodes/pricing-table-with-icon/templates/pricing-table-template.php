<div <?php servicemaster_mikado_class_attribute($classes); ?> <?php servicemaster_mikado_inline_style($inline_styles); ?> <?php echo servicemaster_mikado_get_inline_attrs($data_attrs); ?>>
    <div class="mkd-pricing-table-wi-inner">
        <div class="mkd-pt-icon" <?php servicemaster_mikado_inline_style($icon_inline_styles); ?>>
            <?php echo servicemaster_mikado_icon_collections()->renderIcon($icon, $icon_pack, $params); ?>
        </div>
        <h2 class="mkd-pt-title">
            <?php echo esc_html($title); ?>
        </h2>
        <h4 class="mkd-pt-subtitle">
            <?php echo esc_html($subtitle); ?>
        </h4>
        <?php if (!empty($price)) : ?>
            <div class="mkd-price-currency-period">
                <?php if (!empty($currency)) : ?>
                    <h2 class="mkd-currency" <?php servicemaster_mikado_inline_style($price_inline_styles); ?>><?php echo esc_html($currency); ?></h2>
                <?php endif; ?>

                <h2 class="mkd-price" <?php servicemaster_mikado_inline_style($price_inline_styles); ?>><?php echo esc_html($price); ?></h2>

                <?php if (!empty($price_period)) : ?>
                    <span class="mkd-price-period">/<?php echo esc_html($price_period) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mkd-pt-content">
            <ul>
                <li class="mkd-pt-content-inner">
                    <?php echo do_shortcode(preg_replace('#^<\/p>|<p>$#', '', $content)); ?>
                </li>
            </ul>
        </div>
        <?php if (is_array($button_params) && count($button_params)) : ?>
            <div class="mkd-price-button">
                <?php echo servicemaster_mikado_get_button_html($button_params); ?>
            </div>
        <?php endif; ?>
    </div>
</div>