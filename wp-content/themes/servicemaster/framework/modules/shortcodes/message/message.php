<?php
namespace ServiceMaster\Modules\Message;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Message
 */
class Message implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_message';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Message', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-message extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array_merge(
				array(
					array(
						'type'        => 'dropdown',
						'admin_label' => true,
						'heading'     => esc_html__('Type', 'servicemaster'),
						'param_name'  => 'type',
						'value'       => array(
							esc_html__('Normal', 'servicemaster')    => 'normal',
							esc_html__('With Icon', 'servicemaster') => 'with_icon'
						),
						'save_always' => true
					)
				),
				\ServiceMasterMikadoIconCollections::get_instance()->getVCParamsArray(array(
					'element' => 'type',
					'value'   => 'with_icon'
				)),
				array(
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Color', 'servicemaster'),
						'param_name'  => 'icon_color',
						'description' => '',
						'dependency'  => array(
							'element' => 'type',
							'value'   => array('with_icon')
						)
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Background Color', 'servicemaster'),
						'param_name'  => 'icon_background_color',
						'description' => '',
						'dependency'  => array(
							'element' => 'type',
							'value'   => array('with_icon')
						)
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Background Color', 'servicemaster'),
						'param_name'  => 'background_color',
						'description' => ''
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Border Color', 'servicemaster'),
						'param_name'  => 'border_color',
						'description' => ''
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Border Width (px)', 'servicemaster'),
						'param_name'  => 'border_width',
						'description' => esc_html__('Default value is 0', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Close Icon Color', 'servicemaster'),
						'param_name'  => 'close_icon',
						'description' => ''
					),
					array(
						'type'        => 'textarea_html',
						'heading'     => esc_html__('Content', 'servicemaster'),
						'param_name'  => 'content',
						'value'       => '<p>' . esc_html__('I am test text for Message shortcode.', 'servicemaster') . '</p>',
						'description' => ''
					)
				)
			)
		));

	}

	public function render($atts, $content = null) {

		$args = array(
			'type'                  => '',
			'background_color'      => '',
			'border_color'          => '',
			'border_width'          => '',
			'icon_size'             => '',
			'icon_custom_size'      => '',
			'icon_color'            => '',
			'icon_background_color' => '',
			'close_icon'            => ''
		);

		$args = array_merge($args, servicemaster_mikado_icon_collections()->getShortcodeParams());
		$params = shortcode_atts($args, $atts);
		$params['content'] = $content;

		//Extract params for use in method
		extract($params);
		//Get HTML from template based on type of team
		$iconPackName = servicemaster_mikado_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);
		$message_classes = '';

		if ($type == 'with_icon') {
			$message_classes .= ' mkd-with-icon';
			$params['icon'] = $params[$iconPackName];
			$params['icon_attributes'] = $this->getIconStyle($params);
		}

		$params['message_classes'] = $message_classes;
		$params['message_styles'] = $this->getHolderStyle($params);
		$params['close_icon_style'] = $this->getCloseColorStyle($params);

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/message-holder-template', 'message', '', $params);

		return $html;
	}

	/**
	 * Generates message icon styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getIconStyle($params) {
		$iconStyles = array();

		if ($params['icon_color'] != '') {
			$iconStyles[] = 'color: ' . $params['icon_color'];
		}

		if ($params['icon_background_color'] != '') {
			$iconStyles[] = 'background-color:' . $params['icon_background_color'] . '';
		}

		return $iconStyles;
	}

	/**
	 * Generates message holder styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderStyle($params) {
		$holderStyles = array();

		if (!empty($params['background_color'])) {
			$holderStyles[] = 'background-color: ' . $params['background_color'];
		}

		if (!empty($params['border_color'])) {
			$holderStyles[] = 'border-color:' . $params['border_color'] . '';
		}
		if (!empty($params['border_width'])) {
			$holderStyles[] = 'border-width:' . $params['border_width'] . 'px';
		}

		return implode(';', $holderStyles);
	}

	private function getCloseColorStyle($params) {
		$close_icon_style = array();

		if ($params['close_icon'] != '') {
			$close_icon_style[] = 'color: ' . $params['close_icon'];
		}

		return $close_icon_style;
	}
}