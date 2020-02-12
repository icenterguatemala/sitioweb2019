<div <?php servicemaster_mikado_class_attribute($holder_classes); ?>>
    <div class="mkd-cards-gallery">
        <?php
        $i = 1;
        foreach($images as $image) { ?>
            <div class="card" <?php servicemaster_mikado_inline_style($background_color); ?>>
                <div class="mkd-bundle-item" data-bundle-move-top="<?php echo esc_attr($i*300); ?>">
                    <?php if($image['image_link'] !== ''){ ?>
                    <a href="<?php echo esc_url($image['image_link']) ?>" target="<?php echo esc_attr($image['image_target']) ?>">
                        <?php } ?>
                        <img  src="<?php echo esc_url($image['url']);?>" alt="#" />
                    <?php if($image['image_link'] !== ''){ ?>
                    </a>
                    <?php }
                    $i++;
                    ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="fake_card">
        <img src="<?php echo esc_url(end($images)['url']);?>" alt="">
    </div>
</div>