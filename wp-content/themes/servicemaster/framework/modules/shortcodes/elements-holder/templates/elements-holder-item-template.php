<div <?php servicemaster_mikado_class_attribute($elements_holder_item_class)?> <?php echo servicemaster_mikado_get_inline_attrs($elements_holder_item_data); ?> <?php servicemaster_mikado_inline_style($elements_holder_item_style) ?>>
	<div class="mkd-elements-holder-item-inner">
		<div
			class="mkd-elements-holder-item-content <?php echo esc_attr($elements_holder_item_content_class); ?>" <?php servicemaster_mikado_inline_style($elements_holder_item_content_style);?>>
			<div class="mkd-elements-holder-item-content-inner">
				<?php echo do_shortcode($content); ?>
			</div>
		</div>
	</div>
</div>