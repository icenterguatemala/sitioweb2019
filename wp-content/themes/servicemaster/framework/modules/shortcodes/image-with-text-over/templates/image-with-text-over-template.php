<div class="mkd-iwt-over">
	<?php if ($link != '') { ?>
		<a href="<?php echo esc_attr($link) ?>" target="<?php echo esc_attr($link_target) ?>"></a>
	<?php } ?>
	<div class="mkd-image-holder">
		<?php echo wp_get_attachment_image($image, 'full'); ?>
	</div>
	<div class="mkd-text-holder" <?php servicemaster_mikado_inline_style($text_style); ?> <?php echo servicemaster_mikado_get_inline_attrs($text_data); ?>>
		<div class="mkd-text-holder-table">
			<div class="mkd-text-holder-cell">
				<h3 class="mkd-iwt-text">
					<?php echo esc_html($text); ?>
				</h3>
			</div>			
			<div class="mkd-text-holder-cell">
				<span class="mkd-iwt-icon"></span>
			</div>			
		</div>
	</div>
</div>