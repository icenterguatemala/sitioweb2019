<h6 class="clearfix mkd-title-holder">
<span class="mkd-accordion-mark mkd-left-mark">
		<span class="mkd-accordion-mark-icon">
			<span class="icon_plus"></span>
			<span class="icon_minus-06"></span>
		</span>
</span>
<span class="mkd-tab-title">
	<?php if ($params['icon']) : ?>
		<span class="mkd-icon-accordion-holder">
				 <?php echo servicemaster_mikado_icon_collections()->renderIcon($icon, $icon_pack); ?>
		</span>
	<?php endif; ?>
	<span class="mkd-tab-title-inner">
		<?php echo esc_attr($title) ?>
	</span>
</span>
</h6>
<div class="mkd-accordion-content">
	<div class="mkd-accordion-content-inner">
		<?php echo do_shortcode($content) ?>

		<?php if (is_array($link_params) && count($link_params)) : ?>
			<a class="mkd-arrow-link" target="<?php echo esc_attr($link_params['link_target']); ?>"
			   href="<?php echo esc_url($link_params['link']); ?>">
				<span class="mkd-al-icon">
					<span class="icon-arrow-right-circle"></span>
				</span>
				<span class="mkd-al-text"><?php echo esc_html($link_params['link_text']); ?></span>
			</a>
		<?php endif; ?>
	</div>
</div>
