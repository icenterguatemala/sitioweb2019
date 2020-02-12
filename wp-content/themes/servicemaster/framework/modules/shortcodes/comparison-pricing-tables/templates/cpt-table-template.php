<div <?php servicemaster_mikado_class_attribute($table_classes); ?>>
	<?php if ($featured_package == 'yes') { ?>
		<div class="mkd-featured-comparision-package"><?php esc_html_e('Featured package', 'servicemaster'); ?></div>
	<?php } ?>
	<div class="mkd-cpt-table-holder-inner">
		<?php if ($display_border) : ?>
			<div class="mkd-cpt-table-border-top" <?php servicemaster_mikado_inline_style($border_style); ?>></div>
		<?php endif; ?>

		<div class="mkd-cpt-table-head-holder">
			<div class="mkd-cpt-table-head-holder-inner">
				<?php if ($title !== '') : ?>
					<h3 class="mkd-cpt-table-title"><?php echo esc_html($title); ?></h3>
				<?php endif; ?>

				<?php if ($price !== '') : ?>
					<div class="mkd-cpt-table-price-holder">
						<?php if ($currency !== '') : ?>
						<span class="mkd-cpt-table-currency"><?php echo esc_html($currency); ?></span><!--
						<?php else: ?>
							<!--
						<?php endif; ?>

						 --><span class="mkd-cpt-table-price"><?php echo esc_html($price); ?></span>

						<?php if ($price_period !== '') : ?>
							<span class="mkd-cpt-table-period">
								/ <?php echo esc_html($price_period); ?>
							</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="mkd-cpt-table-content">
			<?php echo do_shortcode(preg_replace('#^<\/p>|<p>$#', '', $content)); ?>
		</div>
		<?php if($show_button == 'yes') { ?>
			<div class="mkd-cpt-table-footer">
				<?php echo servicemaster_mikado_get_button_html($button_parameters); ?>
			</div>
		<?php } ?>
	</div>
</div>