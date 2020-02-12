<li data-date="<?php echo esc_attr($date); ?>">
	<div class="mkd-horizontal-item">
		<div class="mkd-horizontal-item-left">
			<div class="mkd-horizontal-timeline-item-image">
				<?php echo wp_get_attachment_image($image, 'full'); ?>
			</div>
		</div>
		<div class="mkd-horizontal-item-right">
			<?php if (!empty($title)) : ?>
				<h3 class="mkd-horizontal-timeline-item-title">
					<?php echo esc_html($title); ?>
				</h3>
			<?php endif;

			echo do_shortcode('[mkd_separator position="left" width="73" thickness="2"]');

			if (!empty($subtitle)) : ?>
				<h4 class="mkd-horizontal-timeline-item-subtitle">
					<?php echo esc_html($subtitle); ?>
				</h4>
			<?php endif; ?>
			<?php echo servicemaster_mikado_remove_auto_ptag($content, true); ?>
		</div>
	</div>
</li>