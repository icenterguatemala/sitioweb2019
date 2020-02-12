<?php

if(!function_exists('servicemaster_mikado_register_widgets')) {

	function servicemaster_mikado_register_widgets() {

		$widgets = array(
			'ServiceMasterMikadoLatestPosts',
			'ServiceMasterMikadoSearchOpener',
			'ServiceMasterMikadoSideAreaOpener',
			'ServiceMasterMikadoStickySidebar',
			'ServiceMasterMikadoSocialIconWidget',
			'ServiceMasterMikadoSeparatorWidget',
			'ServiceMasterMikadoCallToActionButton',
			'ServiceMasterMikadoHtmlWidget',
			'ServiceMasterMikadoInfoWidget'
		);

		foreach($widgets as $widget) {
			register_widget($widget);
		}
	}
}

add_action('widgets_init', 'servicemaster_mikado_register_widgets');