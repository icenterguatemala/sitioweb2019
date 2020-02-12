<?php
namespace ServiceMaster\Modules\Shortcodes\CardSliderItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Card Slider Item
 */
class CardSliderItem implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_card_slider_item';

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
			'name'                      => esc_html__('Card Slider Item', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'as_child'                  => array('only' => 'mkd_card_slider'),
			'icon'                      => 'icon-wpb-card-slider-item extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array_merge(
				servicemaster_mikado_icon_collections()->getVCParamsArray(array(), '', true),
				array(array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Icon Size (px)', 'servicemaster'),
					'param_name'  => 'icon_size',
					'description' => esc_html__('Default font size is 47px', 'servicemaster'),
				),
					array(
						'type'       => 'attach_image',
						'heading'    => esc_html__('Custom icon', 'servicemaster'),
						'param_name' => 'custom_icon',
					),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Custom Icon Padding', 'servicemaster'),
                        'param_name'  => 'custom_icon_padding',
                        'value'       => '',
                        'description' => esc_html__('Padding should be set in a top right bottom left format', 'servicemaster'),
                        'dependency' => array(
                            'element'   => 'custom_icon',
                            'not_empty' => true
                        ),
                        'admin_label' => true,
                    ),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Icon Margin', 'servicemaster'),
						'param_name'  => 'icon_margin',
						'value'       => '',
						'description' => esc_html__('Margin should be set in a top right bottom left format', 'servicemaster'),
						'admin_label' => true,
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Title', 'servicemaster'),
						'param_name' => 'title'
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Title tag', 'servicemaster'),
						'param_name'  => 'title_tag',
						'value'       => array(
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5'
						),
						'save_always' => true,
					),
                    array(
                        'type'       => 'colorpicker',
                        'heading'    => esc_html__('Separator Color', 'servicemaster'),
                        'param_name' => 'separator_color',
                        'value'      => ''
                    ),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Title Font Family', 'servicemaster'),
						'param_name' => 'title_font_family'
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Subtitle', 'servicemaster'),
						'param_name' => 'subtitle'
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Text', 'servicemaster'),
						'param_name' => 'text'
					),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Card Content Padding', 'servicemaster'),
                        'param_name'  => 'card_content_padding',
                        'value'       => '',
                        'description' => esc_html__('Padding should be set in a top right bottom left format', 'servicemaster'),
                        'admin_label' => true,
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
							''                                   => '',
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
					),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => esc_html__('Button Background Color', 'servicemaster'),
                        'param_name'  => 'button_background_color',
                        'dependency'  => array(
                            'element'   => 'link',
                            'not_empty' => true
                        ),
                        'admin_label' => true
                    ),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => esc_html__('Button Background Hover Color', 'servicemaster'),
                        'param_name'  => 'button_hover_color',
                        'dependency'  => array(
                            'element'   => 'link',
                            'not_empty' => true
                        ),
                        'admin_label' => true
                    )
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
		$default_atts = array(
			'icon_size'             => '',
			'custom_icon'           => '',
            'custom_icon_padding'   => '',
			'icon_margin'           => '',
			'title'                 => '',
			'title_tag'             => 'h3',
			'title_font_family'     => '',
            'separator_color'       => '',
			'subtitle'              => '',
			'text'                  => '',
            'card_content_padding'  => '',
			'link'                  => '',
			'link_text'             => '',
			'link_target'           => '_blank',
			'color'                 => '',
            'button_background_color' => '',
            'button_hover_color'    => ''
		);

		$default_atts = array_merge($default_atts, servicemaster_mikado_icon_collections()->getShortcodeParams());

		$params = shortcode_atts($default_atts, $atts);
		$params['icon_parameters'] = $this->getIconParameters($params);
        $params['content_style'] = $this->getContentStyle($params);
        $params['custom_icon_inline'] = $this->getCustomIconInline($params);
		$params['button_parameters'] = $this->getButtonParameters($params);
		$params['title_inline_styles'] = $this->getInlineStyles($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/card-slide', 'card-slider', '', $params);
	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		$button_params_array['type'] = 'white';
        $button_params_array['size'] = 'small';
		$button_params_array['custom_class'] = 'mkd-card-slider-link';

		if (!empty($params['link_text'])) {
			$button_params_array['text'] = $params['link_text'];
		}

		if (!empty($params['link'])) {
			$button_params_array['link'] = $params['link'];
		}

		if (!empty($params['link_target'])) {
			$button_params_array['target'] = $params['link_target'];
		}

		if (!empty($params['color'])) {
			$button_params_array['color'] = $params['color'];
		}

        if (!empty($params['button_background_color'])) {
            $button_params_array['background_color'] = $params['button_background_color'];
        }

        if (!empty($params['button_hover_color'])) {
            $button_params_array['hover_background_color'] = $params['button_hover_color'];
        }

		return $button_params_array;
	}

	private function getInlineStyles($params) {
		$styles = array();

		if (!empty($params['title_font_family'])) {
			$styles[] = 'font-family: ' . $params['title_font_family'];
		}

		return $styles;
	}

	/**
	 * Returns parameters for icon shortcode as a string
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getIconParameters($params) {
		$params_array = array();

		if (empty($params['custom_icon'])) {
			$iconPackName = servicemaster_mikado_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);

			$params_array['icon_pack'] = $params['icon_pack'];
			if ($params['icon_pack']) {
				$params_array[$iconPackName] = $params[$iconPackName];
			}

			if (!empty($params['custom_icon_size'])) {
				$params_array['custom_size'] = $params['custom_icon_size'];
			}

			$params_array['margin'] = $params['icon_margin'];
		}

		return $params_array;
	}

    /**
     * Returns parameters for icon shortcode as a string
     *
     * @param $params
     *
     * @return array
     */
    private function getCustomIconInline($params) {
        $style = array();

        if (!empty($params['custom_icon_padding'])) {
            $style = 'padding: ' . $params['custom_icon_padding'];
        }

        return $style;
    }


    /**
     * Returns parameters for Card Content as a string
     *
     * @param $params
     *
     * @return array
     */
    private function getContentStyle($params) {
        $style = array();

        if (!empty($params['card_content_padding'])) {
            $style = 'padding: ' . $params['card_content_padding'];
        }

        return $style;
    }

}