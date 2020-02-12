<div <?php servicemaster_mikado_class_attribute($item_classes); ?>>
	<div class="mkd-pi-holder-inner  clearfix">
		<div class="mkd-pi-holder">
			<?php if (!empty($image)) : ?>
				<div class="mkd-pi">
					<div class="mkd-pi-inner">
						<?php echo wp_get_attachment_image($image, 'full'); ?>
					</div>
				</div>
			<?php else: ?>
				<div class="mkd-pi icon" <?php servicemaster_mikado_inline_style($icon_styles)?>>
					<?php echo servicemaster_mikado_get_shortcode_module_template_part('templates/icon', 'process', '', array('icon_parameters' => $icon_parameters)); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="mkd-pi-content-holder">
			<?php if (!empty($title)) : ?>
				<div class="mkd-pi-title-holder">
					<h3 class="mkd-pi-title"><?php echo esc_html($title); ?></h3>
				</div>
			<?php endif; ?>

			<?php if (!empty($text)) : ?>
				<div class="mkd-pi-text-holder">
					<p><?php echo esc_html($text); ?></p>
				</div>
			<?php endif; ?>

			<?php
			if (!empty($link) && !empty($link_text)) :
				echo servicemaster_mikado_get_button_html($button_parameters);
			endif;
			?>
		</div>
	</div>
</div>