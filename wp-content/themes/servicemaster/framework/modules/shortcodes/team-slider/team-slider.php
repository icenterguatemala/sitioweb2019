<?php

namespace ServiceMaster\Modules\Shortcodes\TeamSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class TeamSlider implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_team_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'         => esc_html__('Team Slider', 'servicemaster'),
			'base'         => $this->base,
			'category'     => 'by MIKADO',
			'icon'         => 'icon-wpb-team-slider extended-custom-icon',
			'is_container' => true,
			'js_view'      => 'VcColumnView',
			'as_parent'    => array('only' => 'mkd_team_slider_item'),
			'params'       => array(
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Slider type', 'servicemaster'),
					'param_name'  => 'slider_type',
					'description' => '',
					'value'       => array(
						esc_html__('Boxed', 'servicemaster')  => 'boxed',
						esc_html__('Simple', 'servicemaster') => 'simple',
						esc_html__('Hover', 'servicemaster')  => 'hover'
					)
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Number of Items in Row', 'servicemaster'),
					'param_name'  => 'number_of_items',
					'description' => '',
					'value'       => array(
						'3' => '3',
						'4' => '4',
						'5' => '5'
					)
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Show Navigation Dots', 'servicemaster'),
					'param_name'  => 'dots',
					'value'       => array(
						esc_html__('Yes', 'servicemaster') => 'yes',
						esc_html__('No', 'servicemaster')  => 'no'
					),
					'save_always' => true,
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'slider_type'     => 'boxed',
			'number_of_items' => '3',
			'dots'            => 'yes'
		);

		$params = shortcode_atts($default_atts, $atts);

		/* proceed slider type parameter to nested shortcode in order to call proper template */
		$params['content'] = preg_replace('/\[mkd_team_slider_item\b/i', '[mkd_team_slider_item slider_type="' . $params['slider_type'] . '"', $content);

		$params['holder_classes'] = $this->getHolderClasses($params);
		$params['data_attrs'] = $this->getDataAttribute($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/team-slider-template', 'team-slider', '', $params);
	}

	/**
	 * Returns array of holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderClasses($params) {
		$classes = array('mkd-team-slider-holder');

		$classes[] = $params['slider_type'];


		return $classes;
	}

	/**
	 * Return Team Slider data attribute
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
}