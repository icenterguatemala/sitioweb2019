<?php
namespace ServiceMaster\Modules\Shortcodes\MiniTextSliderItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class MiniTextSliderItem implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_mini_text_slider_item';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'      => esc_html__('Mini Text Slider Item', 'servicemaster'),
			'base'      => $this->base,
			'icon'      => 'icon-wpb-mini-text-slider-item extended-custom-icon',
			'category'  => 'by MIKADO',
			'as_child' => array('only' => 'mkd_mini_text_slider'),
			'params'    => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Title', 'servicemaster'),
					'param_name'  => 'title',
					'admin_label' => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Subtitle', 'servicemaster'),
					'param_name'  => 'subtitle',
					'admin_label' => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Text', 'servicemaster'),
					'param_name'  => 'text',
					'admin_label' => true,
				),
			)
		));
	}

	public function render($atts, $content = null) {

		$args   = array(
			'title' => '',
			'subtitle' => '',
			'text' => ''
		);
		$params = shortcode_atts($args, $atts);
		extract($params);

        $html = servicemaster_mikado_get_shortcode_module_template_part('templates/mini-text-slider-item-template', 'mini-text-slider', '', $params);

		return $html;

	}

}
