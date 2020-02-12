<?php
namespace ServiceMaster\Modules\Shortcodes\ElementsHolder;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class ElementsHolder implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_elements_holder';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'      => esc_html__('Elements Holder', 'servicemaster'),
			'base'      => $this->base,
			'icon'      => 'icon-wpb-elements-holder extended-custom-icon',
			'category'  => 'by MIKADO',
			'as_parent' => array('only' => 'mkd_elements_holder_item, mkd_info_box'),
			'js_view'   => 'VcColumnView',
			'params'    => array(
				array(
					'type'       => 'colorpicker',
					'class'      => '',
					'heading'    => esc_html__('Background Color', 'servicemaster'),
					'param_name' => 'background_color',
					'value'      => ''
				),
				array(
					'type'        => 'dropdown',
					'class'       => '',
					'heading'     => esc_html__('Equal height', 'servicemaster'),
					'param_name'  => 'items_float_left',
					'value'       => array(
						esc_html__('Yes', 'servicemaster') => 'yes',
						esc_html__('No', 'servicemaster')  => 'no'
					),
					'save_always' => true
				),
				array(
					'type'       => 'dropdown',
					'class'      => '',
					'heading'    => esc_html__('Border', 'servicemaster'),
					'param_name' => 'border',
					'value'      => array(
						esc_html__('No', 'servicemaster')  => 'no',
						esc_html__('Yes', 'servicemaster') => 'yes'
					)
				),
				array(
					'type'        => 'textfield',
					'class'       => '',
					'heading'     => esc_html__('Border Width', 'servicemaster'),
					'param_name'  => 'border_width',
					'value'       => '',
					'dependency'  => array(
						'element' => 'border',
						'value'   => array('yes')
					),
					'description' => esc_html__('Please insert border width in px. For example: 1 ', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Border Style', 'servicemaster'),
					'param_name'  => 'border_style',
					'value'       => array(
						esc_html__('Solid', 'servicemaster')  => 'solid',
						esc_html__('Dashed', 'servicemaster') => 'dashed',
						esc_html__('Dotted', 'servicemaster') => 'dotted'
					),
					'dependency'  => array(
						'element' => 'border',
						'value'   => array('yes')
					),
					'save_always' => true
				),
				array(
					'type'       => 'colorpicker',
					'class'      => '',
					'heading'    => esc_html__('Border Color', 'servicemaster'),
					'param_name' => 'border_color',
					'value'      => '',
					'dependency' => array(
						'element' => 'border',
						'value'   => array('yes')
					)
				),
				array(
					'type'       => 'dropdown',
					'class'      => '',
					'heading'    => esc_html__('Box Shadow', 'servicemaster'),
					'param_name' => 'box_shadow',
					'value'      => array(
						esc_html__('No', 'servicemaster')  => 'no',
						esc_html__('Yes', 'servicemaster') => 'yes'
					)
				),
				array(
					'type'        => 'dropdown',
					'class'       => '',
					'group'       => esc_html__('Width & Responsiveness', 'servicemaster'),
					'heading'     => esc_html__('Switch to One Column', 'servicemaster'),
					'param_name'  => 'switch_to_one_column',
					'value'       => array(
						esc_html__('Default', 'servicemaster')      => '',
						esc_html__('Below 1440px', 'servicemaster') => '1440',
						esc_html__('Below 1280px', 'servicemaster') => '1280',
						esc_html__('Below 1024px', 'servicemaster') => '1024',
						esc_html__('Below 768px', 'servicemaster')  => '768',
						esc_html__('Below 600px', 'servicemaster')  => '600',
						esc_html__('Below 480px', 'servicemaster')  => '480',
						esc_html__('Never', 'servicemaster')        => 'never'
					),
					'description' => esc_html__('Choose on which stage item will be in one column', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'class'       => '',
					'group'       => esc_html__('Width & Responsiveness', 'servicemaster'),
					'heading'     => esc_html__('Choose Alignment In Responsive Mode', 'servicemaster'),
					'param_name'  => 'alignment_one_column',
					'value'       => array(
						esc_html__('Default', 'servicemaster') => '',
						esc_html__('Left', 'servicemaster')    => 'left',
						esc_html__('Center', 'servicemaster')  => 'center',
						esc_html__('Right', 'servicemaster')   => 'right'
					),
					'description' => esc_html__('Alignment When Items are in One Column', 'servicemaster')
				)
			)
		));
	}

	public function render($atts, $content = null) {

		$args = array(
			'switch_to_one_column' => '',
			'alignment_one_column' => '',
			'items_float_left'     => '',
			'background_color'     => '',
			'border'               => '',
			'border_width'         => '',
			'border_style'         => '',
			'border_color'         => '',
			'box_shadow'           => ''
		);

		$params = shortcode_atts($args, $atts);
		extract($params);

		$html = '';

		$elements_holder_classes = array();
		$elements_holder_classes[] = 'mkd-elements-holder';


		//Elements holder classes
		if ($switch_to_one_column != '') {
			$elements_holder_classes[] = 'mkd-responsive-mode-' . $switch_to_one_column;
		} else {
			$elements_holder_classes[] = 'mkd-responsive-mode-768';
		}

		if ($alignment_one_column != '') {
			$elements_holder_classes[] = 'mkd-one-column-alignment-' . $alignment_one_column;
		}

		if ($items_float_left == 'no') {
			$elements_holder_classes[] = 'mkd-elements-items-float';
		}

		if ($border == 'yes') {
			$elements_holder_classes[] = 'mkd-border';
		}

		if ($box_shadow == 'yes') {
			$elements_holder_classes[] = 'mkd-shadow';
		}


		//Elements holder styles
		$elements_holder_style = array();

		if ($background_color != '') {
			$elements_holder_style[] = 'background-color:' . $background_color . ';';
		}

		if ($params['border_width'] !== '') {
			$elements_holder_style[] = 'border-width: ' . servicemaster_mikado_filter_px($params['border_width']) . 'px';
		}

		if ($params['border_style'] !== '') {
			$elements_holder_style[] = 'border-style: ' . $params['border_style'];
		}

		if ($params['border_color'] !== '') {
			$elements_holder_style[] = 'border-color: ' . $params['border_color'];
		}

		$elements_holder_class = implode(' ', $elements_holder_classes);

		$html .= '<div ' . servicemaster_mikado_get_class_attribute($elements_holder_class) . ' ' . servicemaster_mikado_get_inline_style($elements_holder_style, 'style') . '>';
		$html .= do_shortcode($content);
		$html .= '</div>';

		return $html;

	}

}
