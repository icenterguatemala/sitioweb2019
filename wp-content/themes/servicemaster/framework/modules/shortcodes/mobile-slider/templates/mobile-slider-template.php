<div class="mkd-mobile-slider-holder">
	<img alt="" src="<?php echo MIKADO_ASSETS_ROOT; ?>/img/mobile.png" class="mkd-frame-image">

	<div class="mkd-mobile-slider">

		<?php foreach ($images as $image) { ?>
			<div class="mkd-mobile-slide clearfix">
				<div class="mkd-mobile-slide-inner">
					<?php echo wp_get_attachment_image($image['image_id'], 'full'); ?>
				</div>
			</div>
		<?php } ?>

	</div>
</div>
