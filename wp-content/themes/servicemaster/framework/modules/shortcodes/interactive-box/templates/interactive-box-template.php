<div <?php servicemaster_mikado_class_attribute($holder_classes); ?>>
	<div class="mkd-ib-content" <?php echo servicemaster_mikado_get_inline_attrs($hover_styles); ?>>
		<?php if ($icon_parameters['icon_pack']) { ?>
			<div class="mkd-ib-icon" <?php servicemaster_mikado_inline_style($styles['mkd-ib-icon']); ?>>
				<?php echo servicemaster_mikado_get_shortcode_module_template_part('templates/icon', 'interactive-box', '', array('icon_parameters' => $icon_parameters)); ?>
			</div>
		<?php }
		if ($title !== '') {?>
		<<?php print $title_tag; ?> class="mkd-ib-title"  <?php servicemaster_mikado_inline_style($styles['mkd-ib-title']); ?>>
		<?php echo esc_attr($title); ?>
	</<?php print $title_tag; ?>>
	<?php echo do_shortcode('[mkd_separator position="center" width="73" thickness="2" color="' . esc_attr($separator_color) . '"]');
	} ?>
	<?php if ($link !== '') { ?>
		<a class="mkd-ib-link" href="<?php echo esc_attr($link); ?>" target="<?php echo esc_attr($link_target); ?>"></a>
	<?php } ?>
</div>
<div class="mkd-ib-content-background" <?php servicemaster_mikado_inline_style($styles['mkd-ib-content-background']); ?>>
</div>
<div class="mkd-ib-hover-image" <?php servicemaster_mikado_inline_style($styles['mkd-ib-hover-image']); ?>>
</div>
</div>