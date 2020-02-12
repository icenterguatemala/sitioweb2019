<div class="mkd-message  <?php echo esc_attr($message_classes) ?>" <?php echo servicemaster_mikado_get_inline_style($message_styles) ?>>
	<div class="mkd-message-inner">
		<?php
		if($type == 'with_icon') {
			$icon_html = servicemaster_mikado_get_shortcode_module_template_part('templates/'.$type, 'message', '', $params);
			print $icon_html;
		}
		?>
		<a href="#" class="mkd-close">
			<i class="q_font_elegant_icon icon_close" <?php servicemaster_mikado_inline_style($close_icon_style); ?>></i>
		</a>

		<div class="mkd-message-text-holder">
			<div class="mkd-message-text">
				<p class="mkd-message-text-inner">
					<?php echo servicemaster_mikado_remove_auto_ptag($content, true) ?>
				</p>
			</div>
		</div>
	</div>
</div>
