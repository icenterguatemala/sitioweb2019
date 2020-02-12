<div <?php servicemaster_mikado_class_attribute($item_classes); ?>>
	<div class="mkd-table-content-item-inner">

		<?php if ($table_content_item_title !== '') { ?>
			<h6 class="mkd-table-content-item-title">
				<?php

				if ($table_content_item_link !== ''){ ?>
				<a href="<?php echo esc_url($table_content_item_link) ?>">
					<?php }

					echo esc_html($table_content_item_title);

					if ($table_content_item_link !== ''){ ?>
				</a>
			<?php }

			?>
			</h6>
		<?php } ?>

		<?php if ($table_content_item_desc !== '') { ?>
			<p class="mkd-table-content-item-desc">
				<?php echo esc_html($table_content_item_desc); ?>
			</p>
		<?php }

		if ($content !== '') {
			?>
			<div class="mkd-table-content-item-content">
				<?php echo do_shortcode($content) ?>
			</div>
		<?php } ?>

	</div>
</div>