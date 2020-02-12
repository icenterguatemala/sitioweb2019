<?php

namespace ServiceMaster\Modules\Shortcodes\ComparisonPricingTables;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class ComparisonPricingTable implements ShortcodeInterface {
	private $base;

	/**
	 * ComparisonPricingTable constructor.
	 */
	public function __construct() {
		$this->base = 'mkd_comparison_pricing_table';

		add_action('vc_before_init', array($this, 'vcMap'));
	}


	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Comparison Pricing Table', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-cmp-pricing-table extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'as_child'                  => array('only' => 'mkd_comparison_pricing_tables_holder'),
			'params'                    => array(
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Title', 'servicemaster'),
					'param_name'  => 'title',
					'value'       => esc_html__('Basic Plan', 'servicemaster'),
					'description' => ''
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Title Size (px)', 'servicemaster'),
					'param_name'  => 'title_size',
					'value'       => '',
					'description' => '',
					'dependency'  => array(
						'element'   => 'title',
						'not_empty' => true
					),
					'group'       => esc_html__('Design Options', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Price', 'servicemaster'),
					'param_name'  => 'price',
					'description' => esc_html__('Default value is 100', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Currency', 'servicemaster'),
					'param_name'  => 'currency',
					'description' => esc_html__('Default mark is $', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Price Period', 'servicemaster'),
					'param_name'  => 'price_period',
					'description' => esc_html__('Default label is monthly', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Featured package', 'servicemaster'),
					'param_name'  => 'featured_package',
					'value'       => array(
						esc_html__('No', 'servicemaster')  => 'no',
						esc_html__('Yes', 'servicemaster') => 'yes'
					),
					'description' => '',
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Show Button', 'servicemaster'),
					'param_name'  => 'show_button',
					'value'       => array(
						esc_html__('Yes', 'servicemaster')     => 'yes',
						esc_html__('No', 'servicemaster')      => 'no'
					),
					'description' => '',
					'save_always' => true,
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Button Text', 'servicemaster'),
					'param_name'  => 'button_text',
					'dependency'  => array(
						'element' => 'show_button',
						'value'   => 'yes'
					)
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Button Link', 'servicemaster'),
					'param_name'  => 'button_link',
					'dependency'  => array(
						'element' => 'show_button',
						'value'   => 'yes'
					)
				),
				array(
					'type'        => 'textarea_html',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Content', 'servicemaster'),
					'param_name'  => 'content',
					'value'       => '<li>' . esc_html__('content content content', 'servicemaster') . '</li><li>' . esc_html__('content content content', 'servicemaster') . '</li><li>' . esc_html__('content content content', 'servicemaster') . '</li>',
					'description' => '',
					'admin_label' => false
				),
				array(
					'type'        => 'colorpicker',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Border Top Color', 'servicemaster'),
					'param_name'  => 'border_top_color',
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Design Options', 'servicemaster')
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$args = array(
			'title'            => esc_html__('Basic Plan', 'servicemaster'),
			'title_size'       => '',
			'price'            => '100',
			'currency'         => '',
			'price_period'     => '',
			'show_button'      => 'yes',
			'featured_package' => 'no',
			'button_link'      => '',
			'button_text'      => 'button',
			'border_top_color' => '',
		);

		$params = shortcode_atts($args, $atts);

		$params['content'] = $content;
		$params['border_style'] = $this->getBorderStyles($params);
		$params['display_border'] = is_array($params['border_style']) && count($params['border_style']);
		$params['table_classes'] = $this->getTableClasses($params);
		$params['button_parameters'] = $this->getButtonParameters($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/cpt-table-template', 'comparison-pricing-tables', '', $params);
	}

	private function getTableClasses($params) {
		$classes = array('mkd-comparision-table-holder', 'mkd-cpt-table');

		if ($params['featured_package'] == 'yes') {
			$classes[] = 'mkd-featured-comparision-table';
		}

		return $classes;
	}

	private function getBorderStyles($params) {
		$styles = array();

		if ($params['border_top_color'] !== '') {
			$styles[] = 'background-color: ' . $params['border_top_color'];
		}

		return $styles;
	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		if (!empty($params['button_text'])) {
			$button_params_array['text'] = $params['button_text'];
		}

		if (!empty($params['button_link'])) {
			$button_params_array['link'] = $params['button_link'];
		}

		$button_params_array['size'] = 'small';

		return $button_params_array;
	}
}