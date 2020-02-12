<?php
namespace ServiceMaster\Modules\Shortcodes\MiniTextSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class MiniTextSlider implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_mini_text_slider';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'            => esc_html__('Mini Text Slider', 'servicemaster'),
			'base'            => $this->base,
			'icon'            => 'icon-wpb-mini-text-slider extended-custom-icon',
			'category'        => 'by MIKADO',
			'content_element' => true,
			'as_parent'       => array('only' => 'mkd_mini_text_slider_item'),
			'js_view'         => 'VcColumnView',
			'params'          => array(
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Skin', 'servicemaster'),
					'param_name'  => 'skin',
					'value'       => array(
						esc_html__('Dark', 'servicemaster')  => 'dark',
						esc_html__('Light', 'servicemaster') => 'light'
					),
					'save_always' => true,
					'description' => ''
				),
			)
		));
	}

	public function render($atts, $content = null) {

		$args = array(
			'skin' => ''
		);
		
		$params = shortcode_atts($args, $atts);
		$params['content'] = $content;

		$params['classes'] = $this->getClasses($params);

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/mini-text-slider-template', 'mini-text-slider', '', $params);

		return $html;

	}

	private function getClasses($params) {
		$classes = array('mkd-mini-text-slider');

		$classes[] = 'mkd-' . $params['skin'] . '-skin';

		return $classes;
	}
}
