<div <?php servicemaster_mikado_class_attribute($holder_classes); ?> <?php echo servicemaster_mikado_get_inline_attrs($data); ?>>
	<?php if($icon !== '' && $number_of_icons) : ?>
		<?php for($i = 0; $i < $number_of_icons; $i++) : ?>
			<span class="mkd-ipb-icon">
				<?php echo servicemaster_mikado_icon_collections()->renderIcon($icon, $icon_pack, array(
					'icon_attributes' => array(
						'style' => $icon_styles,
						'class' => 'mkd-ipb-icon-elem'
					)
				)); ?>
			</span>
		<?php endfor; ?>
	<?php endif; ?>
</div>