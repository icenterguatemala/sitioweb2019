<?php
namespace ServiceMaster\Modules\UnorderedList;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class unordered List
 */
class UnorderedList implements ShortcodeInterface {

	private $base;

	function __construct() {
		$this->base = 'mkd_unordered_list';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**\
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('List - Unordered', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-unordered-list extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Style', 'servicemaster'),
					'param_name'  => 'style',
					'value'       => array(
						esc_html__('Circle', 'servicemaster') => 'circle',
						esc_html__('Line', 'servicemaster')   => 'line'
					),
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Animate List', 'servicemaster'),
					'param_name'  => 'animate',
					'value'       => array(
						esc_html__('No', 'servicemaster')  => 'no',
						esc_html__('Yes', 'servicemaster') => 'yes'
					),
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Font Weight', 'servicemaster'),
					'param_name'  => 'font_weight',
					'value'       => array(
						esc_html__('Default', 'servicemaster') => '',
						esc_html__('Light', 'servicemaster')   => 'light',
						esc_html__('Normal', 'servicemaster')  => 'normal',
						esc_html__('Bold', 'servicemaster')    => 'bold'
					),
					'description' => ''
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__('Padding left (px)', 'servicemaster'),
					'param_name' => 'padding_left',
					'value'      => ''
				),
				array(
					'type'        => 'textarea_html',
					'heading'     => esc_html__('Content', 'servicemaster'),
					'param_name'  => 'content',
					'value'       => '<ul><li>' . esc_html__('Lorem Ipsum', 'servicemaster') . '</li><li>' . esc_html__('Lorem Ipsum', 'servicemaster') . '</li><li>' . esc_html__('Lorem Ipsum', 'servicemaster') . '</li></ul>',
					'description' => ''
				)
			)
		));

	}


	public function render($atts, $content = null) {
		$args = array(
			'style'        => '',
			'animate'      => '',
			'font_weight'  => '',
			'padding_left' => ''
		);
		$params = shortcode_atts($args, $atts);

		//Extract params for use in method
		extract($params);

		$list_item_classes = "";

		if ($style != '') {
			if ($style == 'circle') {
				$list_item_classes .= ' mkd-circle';
			} elseif ($style == 'line') {
				$list_item_classes .= ' mkd-line';
			}
		}

		if ($animate == 'yes') {
			$list_item_classes .= ' mkd-animate-list';
		}

		$list_style = '';
		if ($padding_left != '') {
			$list_style .= 'padding-left: ' . $padding_left . 'px;';
		}
		$html = '';

		$html .= '<div class="mkd-unordered-list ' . $list_item_classes . '" ' . servicemaster_mikado_get_inline_style($list_style) . '>';
		$html .= servicemaster_mikado_remove_auto_ptag($content, true);
		$html .= '</div>';

		return $html;
	}

}