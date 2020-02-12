<div <?php servicemaster_mikado_class_attribute($pricing_table_classes) ?>>
	<div class="mkd-price-table-inner">
		<?php if ($active == 'yes') { ?>
			<div class="mkd-active-label">
				<div class="mkd-active-label-inner">
				</div>
			</div>
		<?php } ?>
		<ul>
			<li class="mkd-table-prices">
				<div class="mkd-price-in-table" <?php servicemaster_mikado_inline_style($price_styles); ?>>
					<?php if (!empty($price)) : ?>
						<span class="mkd-price-currency"><sup class="mkd-currency"><?php echo esc_html($currency); ?></sup><?php echo esc_html($price); ?></span>
					<?php endif; ?>
				</div>
			</li>
			<li class="mkd-table-title">
				<h2 <?php servicemaster_mikado_inline_style($title_styles); ?>
					class="mkd-title-content"><?php echo esc_html($title) ?></h2>
			</li>
			<?php if (!empty($price_period)) : ?>
				<li class="mkd-pt-price-period">
					<span class="mkd-pt-price-period-content"><?php echo esc_html($price_period) ?></span>
				</li>
			<?php endif; ?>
			<li class="mkd-table-content">
				<?php echo do_shortcode(preg_replace('#^<\/p>|<p>$#', '', $content)); ?>
			</li>
			<?php
			if (is_array($button_params) && count($button_params)) { ?>
				<li class="mkd-price-button">
					<?php echo servicemaster_mikado_get_button_html($button_params); ?>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
