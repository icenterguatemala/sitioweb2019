<?php
if(!function_exists('servicemaster_mikado_layerslider_overrides')) {
	/**
	 * Disables Layer Slider auto update box
	 */
	function servicemaster_mikado_layerslider_overrides() {
		$GLOBALS['lsAutoUpdateBox'] = false;
	}

	add_action('layerslider_ready', 'servicemaster_mikado_layerslider_overrides');
}
?>