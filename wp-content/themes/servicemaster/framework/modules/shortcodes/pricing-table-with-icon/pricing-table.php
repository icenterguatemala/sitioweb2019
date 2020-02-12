<?php
namespace ServiceMaster\Modules\PricingTableWithIcon;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class PricingTableWithIcon implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_pricing_table_with_icon';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Pricing Table With Icon', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-pricing-table-wi extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'as_child'                  => array('only' => 'mkd_pricing_tables_with_icon'),
			'params'                    => array_merge(
				servicemaster_mikado_icon_collections()->getVCParamsArray(array(), '', true),
				array(
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Color', 'servicemaster'),
						'param_name'  => 'icon_color',
						'admin_label' => true
					),
					array(
						'type'        => 'dropdown',
						'admin_label' => true,
						'heading'     => esc_html__('Active', 'servicemaster'),
						'param_name'  => 'active',
						'value'       => array(
							esc_html__('No', 'servicemaster')  => 'no',
							esc_html__('Yes', 'servicemaster') => 'yes'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Title', 'servicemaster'),
						'param_name'  => 'title',
						'value'       => esc_html__('Basic Package', 'servicemaster'),
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Subtitle', 'servicemaster'),
						'param_name'  => 'subtitle',
						'value'       => '',
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Price', 'servicemaster'),
						'param_name'  => 'price'
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Currency', 'servicemaster'),
						'param_name'  => 'currency'
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Price Period', 'servicemaster'),
						'param_name'  => 'price_period'
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Price Color', 'servicemaster'),
						'param_name'  => 'price_color',
						'admin_label' => true
					),
					array(
						'type'        => 'dropdown',
						'admin_label' => true,
						'heading'     => esc_html__('Show Button', 'servicemaster'),
						'param_name'  => 'show_button',
						'value'       => array(
							esc_html__('Default', 'servicemaster') => '',
							esc_html__('Yes', 'servicemaster')     => 'yes',
							esc_html__('No', 'servicemaster')      => 'no'
						),
						'description' => '',
						'group'       => esc_html__('Button Options', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Button Text', 'servicemaster'),
						'param_name'  => 'button_text',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => 'yes'
						),
						'group'       => esc_html__('Button Options', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'admin_label' => true,
						'heading'     => esc_html__('Button Link', 'servicemaster'),
						'param_name'  => 'link',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => 'yes'
						),
						'group'       => esc_html__('Button Options', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Color', 'servicemaster'),
						'param_name'  => 'button_color',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => 'yes'
						),
						'group'       => esc_html__('Button Options', 'servicemaster'),
						'admin_label' => true
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Hover Color', 'servicemaster'),
						'param_name'  => 'button_hover_color',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => 'yes'
						),
						'group'       => esc_html__('Button Options', 'servicemaster'),
						'admin_label' => true
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Background Color', 'servicemaster'),
						'param_name'  => 'button_background_color',
						'admin_label' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => 'yes'
						),
						'group'       => esc_html__('Button Options', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Hover Background Color', 'servicemaster'),
						'param_name'  => 'button_hover_background_color',
						'admin_label' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => 'yes'
						),
						'group'       => esc_html__('Button Options', 'servicemaster')
					),
					array(
						'type'       => 'colorpicker',
						'holder'     => 'div',
						'class'      => '',
						'heading'    => esc_html__('Background color', 'servicemaster'),
						'param_name' => 'background_color',
						'value'      => '',
					),
					array(
						'type'       => 'colorpicker',
						'holder'     => 'div',
						'class'      => '',
						'heading'    => esc_html__('Hover background color', 'servicemaster'),
						'param_name' => 'hover_background_color',
						'value'      => '',
					),
					array(
						'type'       => 'attach_image',
						'class'      => '',
						'heading'    => esc_html__('Background image', 'servicemaster'),
						'param_name' => 'background_image',
						'value'      => '',
					),
					array(
						'type'        => 'textarea_html',
						'holder'      => 'div',
						'class'       => '',
						'heading'     => esc_html__('Content', 'servicemaster'),
						'param_name'  => 'content',
						'value'       => '<li>' . esc_html__('content content content', 'servicemaster') . '</li><li>' . esc_html__('content content content', 'servicemaster') . '</li><li>' . esc_html__('content content content', 'servicemaster') . '</li>',
						'description' => ''
					)
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$args = array(
			'icon_color'                    => '',
			'active'                        => 'no',
			'title'                         => esc_html__('Basic Package', 'servicemaster'),
			'subtitle'                      => '',
			'price'                         => '100',
			'currency'                      => '',
			'price_period'                  => '',
			'price_color'                   => '',
			'link'                          => '',
			'button_text'                   => esc_html__('Purchase plan', 'servicemaster'),
			'background_color'              => '',
			'hover_background_color'        => '',
			'background_image'              => '',
			'show_button'                   => 'yes',
			'button_color'                  => '',
			'button_hover_color'            => '',
			'button_background_color'       => '',
			'button_hover_background_color' => '',
		);

		$args = array_merge($args, servicemaster_mikado_icon_collections()->getShortcodeParams());
		$params = shortcode_atts($args, $atts);
		extract($params);

		$params['content'] = $content;

		$iconPackName = servicemaster_mikado_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);
		$params['icon'] = $params[$iconPackName];
		$params['icon_inline_styles'] = $this->getIconInlineStyles($params);

		$params['button_params'] = $this->getButtonParams($params);
		$params['button_data'] = $this->getButtonDataAttr($params);

		$params['price_inline_styles'] = $this->getPriceInlineStyles($params);

		$params['classes'] = $this->getClasses($params);
		$params['inline_styles'] = $this->getInlineStyles($params);
		$params['data_attrs'] = $this->getDataAttr($params);

		$html = '';

		$html .= servicemaster_mikado_get_shortcode_module_template_part('templates/pricing-table-template', 'pricing-table-with-icon', '', $params);

		return $html;
	}

	/**
	 *
	 * Returns array of button data attr
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getButtonDataAttr($params) {
		$data = array();

		if (!empty($params['button_hover_background_color'])) {
			$data['data-hover-bg-color'] = $params['button_hover_background_color'];
		}

		if (!empty($params['button_hover_color'])) {
			$data['data-hover-color'] = $params['button_hover_color'];
		}

		return $data;
	}

	private function getButtonParams($params) {
		$buttonParams = array();

		if ($params['show_button'] === 'yes' && $params['button_text'] !== '') {
			$buttonParams = array(
				'link'       => $params['link'],
				'text'       => $params['button_text'],
				'size'       => 'medium',
				'type'       => 'solid',
				'hover_type' => 'solid',
			);

			if (!empty($params['button_color'])) {
				$buttonParams['color'] = $params['button_color'];
			}

			if (!empty($params['button_hover_color'])) {
				$buttonParams['hover_color'] = $params['button_hover_color'];
			}

			if (!empty($params['button_background_color'])) {
				$buttonParams['background_color'] = $params['button_background_color'];
				$buttonParams['border_color'] = $params['button_background_color'];
			}

			if (!empty($params['button_hover_background_color'])) {
				$buttonParams['hover_background_color'] = $params['button_hover_background_color'];
				$buttonParams['hover_border_color'] = $params['button_hover_background_color'];
			}
		}

		return $buttonParams;
	}

	/**
	 *
	 * Returns array of styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getPriceInlineStyles($params) {
		$styles = array();
		if (!empty($params['price_color'])) {
			$styles[] = 'color: ' . $params['price_color'];
		}

		return $styles;
	}

	/**
	 *
	 * Returns array of styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getIconInlineStyles($params) {
		$styles = array();
		if (!empty($params['icon_color'])) {
			$styles[] = 'color: ' . $params['icon_color'];
		}

		return $styles;
	}

	/**
	 *
	 * Returns array of classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getClasses($params) {
		$classes = array('mkd-pricing-table-wi');

		if ($params['active'] == 'yes') {
			$classes[] = 'mkd-active';
		}

		return $classes;
	}

	/**
	 *
	 * Returns array of styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getInlineStyles($params) {
		$styles = array();
		if (!empty($params['background_color'])) {
			$styles[] = 'background-color: ' . $params['background_color'];
		}

		if ($params['background_image'] !== '') {
			$styles[] = 'background-image: url(' . wp_get_attachment_url($params['background_image']) . ')';
		}

		return $styles;
	}

	/**
	 *
	 * Returns array of data attributes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getDataAttr($params) {
		$data = array();

		if (!empty($params['hover_background_color'])) {
			$data['data-hover-bg-color'] = $params['hover_background_color'];
		}

		return $data;
	}
}