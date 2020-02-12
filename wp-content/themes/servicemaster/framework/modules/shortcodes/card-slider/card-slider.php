<?php

namespace ServiceMaster\Modules\Shortcodes\CardSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class CardSlider implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_card_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'         => esc_html__('Card Slider', 'servicemaster'),
			'base'         => $this->base,
			'category'     => 'by MIKADO',
			'icon'         => 'icon-wpb-card-slider extended-custom-icon',
			'is_container' => true,
			'js_view'      => 'VcColumnView',
			'as_parent'    => array('only' => 'mkd_card_slider_item'),
			'params'       => array(
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Number of Items in Row', 'servicemaster'),
					'param_name'  => 'number_of_items',
					'description' => '',
					'value'       => array(
						'3' => '3',
						'4' => '4',
					),
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Show Navigation Dots', 'servicemaster'),
					'param_name'  => 'dots',
					'value'       => array(
						'Yes' => 'yes',
						'No'  => 'no'
					),
					'save_always' => true,
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Text Align', 'servicemaster'),
					'param_name'  => 'text_align',
					'value'       => array(
						esc_html__('Left', 'servicemaster')   => 'left',
						esc_html__('Center', 'servicemaster') => 'center',
						esc_html__('Right', 'servicemaster')  => 'right'
					),
					'save_always' => true,
				),
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'number_of_items' => '3',
			'dots'            => 'yes',
			'text_align'      => 'left'
		);

		$params = shortcode_atts($default_atts, $atts);

		/* proceed slider type parameter to nested shortcode in order to call proper template */
		$params['content'] = $content;

		$params['holder_classes'] = $this->getHolderClasses($params);
		$params['data_attrs'] = $this->getDataAttribute($params);
		$params['styles'] = $this->getElementStyles($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/card-slider-template', 'card-slider', '', $params);
	}

	/**
	 * Returns array of holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderClasses($params) {
		$classes = array('mkd-card-slider-holder');

		return $classes;
	}

	/**
	 * Return Card Slider data attribute
	 *
	 * @param $params
	 *
	 * @return string
	 */

	private function getDataAttribute($params) {

		$data_attrs = array();

		if ($params['number_of_items'] !== '') {
			$data_attrs['data-number_of_items'] = $params['number_of_items'];
		}

		if ($params['number_of_items'] !== '') {
			$data_attrs['data-dots'] = ($params['dots'] !== '') ? $params['dots'] : '';
		}

		return $data_attrs;
	}

	/**
	 * Returns array of element styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getElementStyles($params) {
		$styles = array();

		if (!empty($params['text_align'])) {
			$styles[] = 'text-align: ' . $params['text_align'];
		}

		return $styles;
	}
}