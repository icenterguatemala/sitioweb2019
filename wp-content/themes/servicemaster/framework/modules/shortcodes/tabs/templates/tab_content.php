<?php
$tab_data_str = '';
$icon_html    = '';
$tab_data_str .= 'data-icon-pack="'.$icon_pack.'" ';
$icon_html .= servicemaster_mikado_icon_collections()->renderIcon($icon, $icon_pack, array());
$tab_data_str .= 'data-icon-html="'.esc_attr($icon_html).'"';
?>
<div <?php servicemaster_mikado_class_attribute($tab_classes); ?> <?php servicemaster_mikado_inline_style($tab_styles); ?> id="tab-<?php echo sanitize_title( $tab_title )?>" <?php print $tab_data_str?>>
	<?php echo servicemaster_mikado_remove_auto_ptag($content); ?>
</div>
