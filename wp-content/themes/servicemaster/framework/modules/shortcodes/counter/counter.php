<?php
namespace ServiceMaster\Modules\Counter;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Counter
 */
class Counter implements ShortcodeInterface {

	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_counter';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * Maps shortcode to Visual Composer. Hooked on vc_before_init
	 *
	 * @see mkd_core_get_carousel_slider_array_vc()
	 */
	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Counter', 'servicemaster'),
			'base'                      => $this->getBase(),
			'category'                  => 'by MIKADO',
			'admin_enqueue_css'         => array(servicemaster_mikado_get_skin_uri() . '/assets/css/mkd-vc-extend.css'),
			'icon'                      => 'icon-wpb-counter extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    =>
				array(
					array(
						'type'        => 'dropdown',
						'admin_label' => true,
						'heading'     => esc_html__('Type', 'servicemaster'),
						'param_name'  => 'type',
						'value'       => array(
							esc_html__('Zero Counter', 'servicemaster')   => 'zero',
							esc_html__('Random Counter', 'servicemaster') => 'random'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type'        => 'dropdown',
						'admin_label' => true,
						'heading'     => esc_html__('Style', 'servicemaster'),
						'param_name'  => 'counter_style',
						'value'       => array(
                            esc_html__('Default', 'servicemaster')  => 'default',
							esc_html__('Dark', 'servicemaster')  => 'dark',
							esc_html__('Light', 'servicemaster') => 'light'
						),
						'description' => '',
						'save_always' => true
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Digit', 'servicemaster'),
						'param_name'  => 'digit',
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Title', 'servicemaster'),
						'param_name'  => 'title',
						'admin_label' => true,
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Text', 'servicemaster'),
						'param_name'  => 'text',
						'admin_label' => true,
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Link', 'servicemaster'),
						'param_name'  => 'link',
						'value'       => '',
						'admin_label' => true
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Link Text', 'servicemaster'),
						'param_name' => 'link_text',
						'dependency' => array(
							'element'   => 'link',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'dropdown',
						'heading'    => esc_html__('Target', 'servicemaster'),
						'param_name' => 'link_target',
						'value'      => array(
							''                              => '',
							esc_html__('Self', 'servicemaster')  => '_self',
							esc_html__('Blank', 'servicemaster') => '_blank'
						),
						'dependency' => array(
							'element'   => 'link',
							'not_empty' => true
						),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Link Color', 'servicemaster'),
						'param_name'  => 'color',
						'dependency'  => array(
							'element'   => 'link',
							'not_empty' => true
						),
						'admin_label' => true
					)
				)
		));
	}

	/**
	 * Renders shortcodes HTML
	 *
	 * @param $atts array of shortcode params
	 * @param $content string shortcode content
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {

		$args = array(
			'type'          => '',
			'digit'         => '',
			'title'         => '',
			'text'          => '',
			'counter_style' => 'default',
			'link'          => '',
			'link_text'     => '',
			'link_target'   => '_self',
			'color'         => ''
		);

		$params = shortcode_atts($args, $atts);

		$params['counter_classes'] = $this->getCounterClasses($params);
		$params['button_parameters'] = $this->getButtonParameters($params);

		//Get HTML from template
		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/counter-template', 'counter', '', $params);

		return $html;

	}

	/**
	 * Returns array of holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */

	private function getCounterClasses($params) {
		$counter_classes = array('mkd-counter-holder');

		if ($params['counter_style'] === 'light') {
			$counter_classes[] = 'mkd-counter-light';
		} elseif ($params['counter_style'] === 'dark') {
            $counter_classes[] = 'mkd-counter-dark';
        }

		return $counter_classes;
	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		$button_params_array['type'] = 'underline';
		$button_params_array['custom_class'] = 'mkd-counter-link';

		if (!empty($params['link_text'])) {
			$button_params_array['text'] = $params['link_text'];
		}

		if (!empty($params['link'])) {
			$button_params_array['link'] = $params['link'];
		}

		if (!empty($params['target'])) {
			$button_params_array['target'] = $params['target'];
		}

		if (!empty($params['color'])) {
			$button_params_array['color'] = $params['color'];
		}

		return $button_params_array;
	}

}