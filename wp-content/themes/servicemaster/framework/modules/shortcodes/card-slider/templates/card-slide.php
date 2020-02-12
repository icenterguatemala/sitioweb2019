<div class="mkd-card-slide">
	<div class="mkd-card-slide-inner">
		<?php if ($custom_icon !== '') { ?>
			<div class="mkd-card-image" <?php echo servicemaster_mikado_get_inline_style($custom_icon_inline)?>>
				<?php if (!empty($link) && !empty($link_text)) :
					echo "<a href='" . $link . "' target='".$link_target."'>";
				endif;
				?>
				<?php echo wp_get_attachment_image($custom_icon, 'full'); ?>
				<?php if (!empty($link) && !empty($link_text)) :
					echo "</a>";
				endif;
				?>
			</div>
		<?php } else if ($icon_parameters['icon_pack']) { ?>
			<div class="mkd-icon-holder">
				<?php echo servicemaster_mikado_get_shortcode_module_template_part('templates/icon', 'icon-with-text', '', array('icon_parameters' => $icon_parameters)); ?>
			</div>
		<?php } ?>
		<div class="mkd-card-content" <?php echo servicemaster_mikado_get_inline_style($content_style)?>>
			<?php if ($title !== '') :
			if (!empty($link) && !empty($link_text)) :
				echo "<a href='" . $link . "' target='".$link_target."'>";
			endif;
			?>
			<<?php echo esc_html($title_tag); ?>
			class="mkd-card-title" <?php servicemaster_mikado_inline_style($title_inline_styles); ?>>
			<?php echo esc_html($title); ?>
		</<?php echo esc_html($title_tag); ?>>
				<?php if (!empty($link) && !empty($link_text)) :
		echo "</a>";
	endif;

	echo do_shortcode('[mkd_separator position="center" width="73" thickness="2" color="'.$separator_color.'"]');
	?>
	<?php endif; ?>
		<?php if ($subtitle !== ''): ?>
			<p class="mkd-card-subtitle">
				<?php echo esc_html($subtitle); ?>
			</p>
		<?php endif; ?>
		<?php if ($text !== ''): ?>
			<p class="mkd-card-text">
				<?php echo esc_html($text); ?>
			</p>
		<?php endif; ?>
		<?php
		if (!empty($link) && !empty($link_text)) :
			echo servicemaster_mikado_get_button_html($button_parameters);
		endif;
		?>
	</div>
</div>
</div>