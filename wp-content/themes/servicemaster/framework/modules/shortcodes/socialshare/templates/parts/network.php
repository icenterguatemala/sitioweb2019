<li <?php servicemaster_mikado_class_attribute($class_name); ?>>
	<a class="mkd-share-link" href="#" onclick="<?php print $link; ?>">
		<?php if ($custom_icon !== '') : ?>
			<img src="<?php echo esc_url($custom_icon); ?>" alt="<?php echo esc_html($name); ?>"/>

		<?php else : ?>
			<span class="mkd-social-network-icon <?php echo esc_attr($icon); ?>"></span>
		<?php endif; ?>

		<?php if ($type === 'dropdown') : ?>
			<span aria-hidden="true"
				  class="mkd-social-share-label"> <?php echo esc_html($label); ?> </span>
		<?php endif; ?>
	</a>
</li>