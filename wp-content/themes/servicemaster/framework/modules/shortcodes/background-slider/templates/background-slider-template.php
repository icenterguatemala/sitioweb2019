<div class="mkd-bckg-slider-holder">
	<div <?php servicemaster_mikado_class_attribute($classes) ?>>
		<?php
		foreach ($images as $image) :
			$image_url = wp_get_attachment_url($image['image_id'], 'full');?>

			<div class="mkd-bckg-slider-item" style="background-image:url(<?php echo esc_attr($image_url);?>)">
			</div>
		<?php endforeach; ?>
	</div>
</div>