<div class="mkd-table-shortcode-item">

	<?php if ($table_item_title !== '') { ?>

		<div class="mkd-table-shortcode-item-title" <?php servicemaster_mikado_inline_style($title_styles); ?>>
			<h3 class="mkd-table-shortcode-item-inner">
				<?php echo esc_html($table_item_title); ?>
			</h3>
		</div>

	<?php } ?>

	<div class="mkd-table-shortcode-item-content">
		<?php echo do_shortcode($content); ?>
	</div>

</div>