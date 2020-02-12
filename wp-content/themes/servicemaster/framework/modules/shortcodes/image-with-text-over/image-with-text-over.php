<?php
namespace ServiceMaster\Modules\ImageWithTextOver;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class ImageWithTextOver
 */
class ImageWithTextOver implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_image_with_text_over';

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
			'name'                      => esc_html__('Image With Text Over', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-image-with-text-over extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'attach_image',
					'admin_label' => true,
					'heading'     => esc_html__('Image', 'servicemaster'),
					'param_name'  => 'image',
					'description' => ''
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Text', 'servicemaster'),
					'param_name'  => 'text',
					'description' => ''
				),
				array(
					'type'        => 'textfield',
					'admin_label' => true,
					'heading'     => esc_html__('Link', 'servicemaster'),
					'param_name'  => 'link',
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Link Target', 'servicemaster'),
					'param_name'  => 'link_target',
					'value'       => array(
						esc_html__('Same Window', 'servicemaster')     => '_self',
						esc_html__('New Window', 'servicemaster')      => '_blank'
					),
					'dependency'  => array(
						'element'   => 'link',
						'not_empty' => true
					),
					'save_always' => true
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Text font size (px)', 'servicemaster'),
					'param_name'  => 'font_size',
					'group'		  => esc_html__('Design Options', 'servicemaster')
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__('Text color', 'servicemaster'),
					'param_name'  => 'text_color',
					'group'		  => esc_html__('Design Options', 'servicemaster')
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__('Text Background color', 'servicemaster'),
					'param_name'  => 'text_background_color',
					'group'		  => esc_html__('Design Options', 'servicemaster')
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__('Text hover color', 'servicemaster'),
					'param_name'  => 'text_hover_color',
					'group'		  => esc_html__('Design Options', 'servicemaster')
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__('Text hover background color', 'servicemaster'),
					'param_name'  => 'text_hover_background_color',
					'group'		  => esc_html__('Design Options', 'servicemaster')
				),
			)
		));

	}

	public function render($atts, $content = null) {

		$args = array(
			'image'     => '',
			'text'      => '',
			'link'      => '',
			'link_target'   => '_self',
			'font_size' 	=> '',
			'text_color'	=> '',
			'text_background_color'	=> '',
			'text_hover_color'	=> '',
			'text_hover_background_color'	=> '',
		);

		$params = shortcode_atts($args, $atts);

		$params['text_style'] = $this->getTextStyle($params);
		$params['text_data'] = $this->getTextData($params);

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/image-with-text-over-template', 'image-with-text-over', '', $params);

		return $html;
	}

	/* Return Style for text
	*
	* @param $params
	*
	* @return string
	*/
	private function getTextStyle($params) {

		$styles = array();

		if (!empty($params['font_size'])) {
			$styles[] = 'font-size: ' . servicemaster_mikado_filter_px($params['font_size']) . 'px';
		}

		if (!empty($params['text_color'])) {
			$styles[] = 'color: ' . $params['text_color'];
		}

		if (!empty($params['text_background_color'])) {
			$styles[] = 'background-color: ' . $params['text_background_color'];
		}

		return $styles;
	}


	/* Return Data for text
	*
	* @param $params
	*
	* @return string
	*/
	private function getTextData($params) {

		$data = array();

		if (!empty($params['text_hover_color'])) {
			$data['data-hover-color'] = $params['text_hover_color'];
		}

		if (!empty($params['text_hover_background_color'])) {
			$data['data-hover-background-color'] = $params['text_hover_background_color'];
		}

		return $data;
	}
}