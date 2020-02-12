<?php

namespace ServiceMaster\Modules\Shortcodes\AdvancedSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class AdvancedSlider
 */
class AdvancedSlider implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	/**
	 * AdvancedSlider constructor.
	 */
	public function __construct() {
		$this->base = 'mkd_advanced_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 *
	 */
	public function vcMap() {
		vc_map(array(
			'name'                    => esc_html__('Advanced Slider Holder', 'servicemaster'),
			'base'                    => $this->base,
			'as_parent'               => array('only' => 'mkd_advanced_slider_item'),
			'content_element'         => true,
			'show_settings_on_create' => false,
			'category'                => 'by MIKADO',
			'icon'                    => 'icon-wpb-advanced-slider extended-custom-icon',
			'js_view'                 => 'VcColumnView',

		));
	}

	/**
	 * @param array $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {
		$default_attrs = array();
		$params = shortcode_atts($default_attrs, $atts);

		$params['content'] = $content;

		return servicemaster_mikado_get_shortcode_module_template_part('templates/advanced-slider-holder', 'advanced-slider', '', $params);
	}
}