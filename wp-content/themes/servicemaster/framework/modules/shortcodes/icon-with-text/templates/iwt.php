<div <?php servicemaster_mikado_class_attribute($holder_classes); ?> <?php servicemaster_mikado_inline_style($element_styles); ?> <?php echo servicemaster_mikado_get_inline_attrs($holder_data); ?>>
    <?php if ($link !== '') { ?>
        <a class="mkd-iwt-icon-link" href="<?php echo esc_attr($link); ?>" target="<?php echo esc_attr($target); ?>">
    <?php } ?>
    <div class="mkd-iwt-icon-holder">
		<?php if (!empty($custom_icon)) : ?>
			<span
				class="mkd-iwt-custom-icon" <?php servicemaster_mikado_inline_style($custom_icon_styles); ?>><?php echo wp_get_attachment_image($custom_icon, 'full'); ?></span>
		<?php else: ?>
			<?php echo servicemaster_mikado_get_shortcode_module_template_part('templates/icon', 'icon-with-text', '', array('icon_parameters' => $icon_parameters)); ?>
		<?php endif; ?>
	</div>
    <?php if ($link !== '') { ?>
        </a>
    <?php } ?>
	<div class="mkd-iwt-content-holder" <?php servicemaster_mikado_inline_style($content_styles); ?>>
		<?php if ($title != ''){ ?>
        <?php if ($link !== '') { ?>
        <a class="mkd-iwt-title-link" href="<?php echo esc_attr($link); ?>" target="<?php echo esc_attr($target); ?>">
            <?php } ?>
		<div class="mkd-iwt-title-holder">
			<<?php echo esc_attr($title_tag); ?>
			class="mkd-iwt-title" <?php servicemaster_mikado_inline_style($title_styles); ?>
			><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
        </div>
        <?php if ($link !== '') { ?>
        </a>
        <?php } ?>
	<?php } ?>
	<div class="mkd-iwt-text-holder">
		<p <?php servicemaster_mikado_inline_style($text_styles); ?>><?php echo esc_html($text); ?></p>

		<?php
		if (!empty($link) && !empty($link_text)) :
			echo servicemaster_mikado_get_button_html($button_parameters);
		endif;
		?>
	</div>
</div>
</div>