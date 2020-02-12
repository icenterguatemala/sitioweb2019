<?php

if(!function_exists('servicemaster_mikado_is_responsive_on')) {
	/**
	 * Checks whether responsive mode is enabled in theme options
	 * @return bool
	 */
	function servicemaster_mikado_is_responsive_on() {
		return servicemaster_mikado_options()->getOptionValue('responsiveness') !== 'no';
	}
}

if(!function_exists('servicemaster_mikado_is_paspartu_on')) {
    /**
     * Checks whether responsive mode is enabled in theme options
     * @return bool
     */
    function servicemaster_mikado_is_paspartu_on() {
        return servicemaster_mikado_get_meta_field_intersect('enable_paspartu',servicemaster_mikado_get_page_id()) == 'yes';
    }
}