<div <?php servicemaster_mikado_class_attribute($holder_classes); ?> <?php echo servicemaster_mikado_get_inline_attrs($holder_data); ?>>
	<div class="mkd-iwt-content-holder" <?php servicemaster_mikado_inline_style($left_from_title_styles) ?>>
        <?php if ($link !== '') { ?>
        <a class="mkd-iwt-link" href="<?php echo esc_attr($link); ?>" target="<?php echo esc_attr($target); ?>">
            <?php } ?>
		<div class="mkd-iwt-icon-title-holder">
			<div class="mkd-iwt-icon-holder">
				<?php if(!empty($custom_icon)) : ?>
				<span class="mkd-iwt-custom-icon" <?php servicemaster_mikado_inline_style($custom_icon_styles);?>><?php echo wp_get_attachment_image($custom_icon, 'full'); ?></span>
				<?php else: ?>
					<?php echo servicemaster_mikado_get_shortcode_module_template_part('templates/icon', 'icon-with-text', '', array('icon_parameters' => $icon_parameters)); ?>
				<?php endif; ?>
			</div>
			<?php if ($title != ''){ ?>
			<div class="mkd-iwt-title-holder">
				<<?php echo esc_attr($title_tag); ?> <?php servicemaster_mikado_inline_style($title_styles); ?>><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
		</div>
        <?php if ($link !== '') { ?>
        </a>
        <?php } ?>
		<?php } ?>
	</div>
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