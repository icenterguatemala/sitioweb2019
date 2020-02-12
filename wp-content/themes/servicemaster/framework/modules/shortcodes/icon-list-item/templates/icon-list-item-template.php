<div <?php servicemaster_mikado_inline_style($holder_styles); ?> <?php servicemaster_mikado_class_attribute($holder_classes); ?>>
	<div class="mkd-icon-list-icon-holder">
		<div class="mkd-icon-list-icon-holder-inner clearfix">
			<?php echo servicemaster_mikado_icon_collections()->renderIcon($icon, $icon_pack, $params);
			?>
		</div>
	</div>
	<p class="mkd-icon-list-text" <?php servicemaster_mikado_inline_style($title_subtitle_style); ?>>
		<span class="mkd-icon-list-title"  <?php echo servicemaster_mikado_get_inline_style($title_style) ?>>
			<?php echo esc_attr($title) ?>
		</span>
		<span class="mkd-icon-list-subtitle">
			<?php echo esc_attr($subtitle) ?>
		</span>
	</p>
</div>