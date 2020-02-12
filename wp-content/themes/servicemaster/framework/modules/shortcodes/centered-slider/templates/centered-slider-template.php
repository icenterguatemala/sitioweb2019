<div class="mkd-centered-slider">
	<?php
	foreach ($images as $image) :
		echo wp_get_attachment_image($image['image_id'], 'servicemaster_mikado_large_width');
	endforeach;
	?>
</div>