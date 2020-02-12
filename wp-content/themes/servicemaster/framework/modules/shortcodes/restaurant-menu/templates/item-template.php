<div <?php echo servicemaster_mikado_get_class_attribute($item_holder_classes) ?>>

    <span class="mkd-rsrnt-recommended"> <?php echo esc_html__("Recommended",'servicemaster'); ?></span>


    <div class="mkd-rsrnt-item-holder">

        <div class="mkd-rstrnt-item-inner">
            <div class="mkd-rstrnt-item-stars">
                <?php for ($x = 1; $x <= 5; $x++) {
                    if($number_of_stars>=$x){
                        echo "<span aria-hidden='true' class='icon_star'></span>";
                    }
                    else{
                        echo "<span aria-hidden='true' class='icon_star_alt'></span>";
                    }
                }
                ?>
            </div>

            <?php if ($title !== '' || $price !== '' ) {?>

            <div class="mkd-rstrnt-title-price-holder">

                <?php if ($title !== '') { ?>

                <<?php echo esc_attr($title_tag);?> class="mkd-rstrnt-title">
                <?php echo esc_html($title); ?>
                </<?php echo esc_attr($title_tag);?>>

        <?php } ?>

             </div>

        <?php } ?>

            <?php if ($description !== '') { ?>
                <p class="mkd-rstrnt-desc"><?php echo esc_html($description); ?></p>
            <?php } ?>
        </div>

        <div class="mkd-rstrnt-bottom-section">
            <?php
            if($item_image !== ''){ ?>

                <div class="mkd-rstrnt-item-image">
                    <?php echo wp_get_attachment_image( $item_image, array(56,56) ); ?>
                </div>

            <?php } ?>
            <?php
            if ($price !== '') { ?>

                    <div class="mkd-rstrnt-price-holder">
                        <?php     if ($old_price !== '') { ?>
                            <h4 class="mkd-rstrnt-old-price">
                                <span class="mkd-rstrnt-currency"><?php echo esc_html($currency); ?></span>
                                <span class="mkd-rstrnt-number"><?php echo esc_html($old_price); ?></span>
                            </h4>
                        <?php    } ?>
                        <h3 class="mkd-rstrnt-price">
                            <span class="mkd-rstrnt-currency"><?php echo esc_html($currency); ?></span>
                            <span class="mkd-rstrnt-number"><?php echo esc_html($price); ?></span>
                        </h3>
                    </div>

            <?php } ?>
        </div>

    </div>

</div>