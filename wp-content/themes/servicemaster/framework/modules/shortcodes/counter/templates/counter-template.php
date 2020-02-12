<?php
/**
 * Counter shortcode template
 */
?>
<div <?php servicemaster_mikado_class_attribute($counter_classes); ?>>
	<span class="mkd-counter <?php echo esc_attr($type) ?>">
		<?php echo esc_attr($digit); ?>
	</span>

	<div class="mkd-counter-content">
		<h3 class="mkd-counter-title"> <?php echo esc_attr($title); ?> </h3>
		<?php if (!empty($text)) { ?>
			<p class="mkd-counter-text"><?php echo esc_html($text); ?></p>
		<?php } ?>
		<?php
		if (!empty($link) && !empty($link_text)) :
			echo servicemaster_mikado_get_button_html($button_parameters);
		endif;
		?>
	</div>
</div>