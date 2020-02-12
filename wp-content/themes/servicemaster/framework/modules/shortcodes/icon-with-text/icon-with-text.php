<?php
namespace ServiceMaster\Modules\Shortcodes\IconWithText;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class IconWithText
 * @package ServiceMaster\Modules\Shortcodes\IconWithText
 */
class IconWithText implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	/**
	 *
	 */
	public function __construct() {
		$this->base = 'mkd_icon_with_text';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 *
	 */
	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Icon With Text', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-icon-with-text extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array_merge(
				servicemaster_mikado_icon_collections()->getVCParamsArray(array(), '', true),
				array(
					array(
						'type'       => 'attach_image',
						'heading'    => esc_html__('Custom Icon', 'servicemaster'),
						'param_name' => 'custom_icon'
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Icon Position', 'servicemaster'),
						'param_name'  => 'icon_position',
						'value'       => array(
							esc_html__('Top', 'servicemaster')             => 'top',
							esc_html__('Left', 'servicemaster')            => 'left',
							esc_html__('Left From Title', 'servicemaster') => 'left-from-title',
							esc_html__('Right', 'servicemaster')           => 'right'
						),
						'description' => esc_html__('Icon Position', 'servicemaster'),
						'save_always' => true,
						'admin_label' => true
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Icon Type', 'servicemaster'),
						'param_name'  => 'icon_type',
						'value'       => array(
							esc_html__('Normal', 'servicemaster') => 'normal',
							esc_html__('Circle', 'servicemaster') => 'circle',
							esc_html__('Square', 'servicemaster') => 'square',
							esc_html__('Gradient', 'servicemaster') => 'gradient',
						),
						'save_always' => true,
						'admin_label' => true,
						'group'       => esc_html__('Icon Settings', 'servicemaster'),
						'description' => esc_html__('This attribute doesn\'t work when Icon Position is Top. In This case Icon Type is Normal', 'servicemaster'),
					),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Gradient Style', 'servicemaster'),
                        'param_name'  => 'icon_gradient_style',
                        'admin_label' => true,
                        'value'       => array_flip(servicemaster_mikado_get_gradient_left_bottom_to_right_top_styles('-text')),
                        'dependency'  => array(
							'element' => 'icon_type',
							'value' => array('gradient')
						),
                        'group'       => esc_html__('Icon Settings', 'servicemaster'),
                        'save_always' => true
                    ),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Icon Shadow', 'servicemaster'),
						'param_name'  => 'icon_shadow',
						'admin_label' => true,
						'value'       => array(
							esc_html__('No', 'servicemaster')  => 'no',
							esc_html__('Yes', 'servicemaster') => 'yes'
						),
						'group'       => esc_html__('Icon Settings', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_type',
							'value' => array('circle')
						),
						'save_always' => true
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Icon Size', 'servicemaster'),
						'param_name'  => 'icon_size',
						'value'       => array(
							esc_html__('Tiny', 'servicemaster')       => 'mkd-icon-tiny',
							esc_html__('Small', 'servicemaster')      => 'mkd-icon-small',
							esc_html__('Medium', 'servicemaster')     => 'mkd-icon-medium',
							esc_html__('Large', 'servicemaster')      => 'mkd-icon-large',
							esc_html__('Very Large', 'servicemaster') => 'mkd-icon-huge'
						),
						'admin_label' => true,
						'save_always' => true,
						'group'       => esc_html__('Icon Settings', 'servicemaster'),
						'description' => esc_html__('This attribute doesn\'t work when Icon Position is Top', 'servicemaster')
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Custom Icon Size (px)', 'servicemaster'),
						'param_name' => 'custom_icon_size',
						'group'      => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Enable Loading Animation', 'servicemaster'),
						'param_name'  => 'loading_animation',
						'value'       => array(
							'No'  => 'no',
							'Yes' => 'yes'
						),
						'group'       => esc_html__('Advanced Settings', 'servicemaster'),
						'save_always' => true,
						'admin_label' => true
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Loading Animation Delay (ms)', 'servicemaster'),
						'param_name' => 'loading_animation_delay',
						'group'      => esc_html__('Advanced Settings', 'servicemaster'),
						'dependency' => array(
							'element' => 'loading_animation',
							'value'   => array('yes')
						)
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Icon Margin', 'servicemaster'),
						'param_name'  => 'icon_margin',
						'value'       => '',
						'description' => esc_html__('Margin should be set in a top right bottom left format', 'servicemaster'),
						'admin_label' => true,
						'group'       => esc_html__('Icon Settings', 'servicemaster'),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Shape Size (px)', 'servicemaster'),
						'param_name'  => 'shape_size',
						'description' => '',
						'admin_label' => true,
                        'dependency'  => array(
                            'element' => 'icon_type',
                            'value'   => array('normal', 'square', 'circle')
                        ),
						'group'       => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Icon Color', 'servicemaster'),
						'param_name' => 'icon_color',
                        'dependency'  => array(
                            'element' => 'icon_type',
                            'value'   => array('normal', 'square', 'circle')
                        ),
						'group'      => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Icon Hover Color', 'servicemaster'),
						'param_name' => 'icon_hover_color',
                        'dependency'  => array(
                            'element' => 'icon_type',
                            'value'   => array('normal', 'square', 'circle')
                        ),
						'group'      => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Background Color', 'servicemaster'),
						'param_name'  => 'icon_background_color',
						'description' => esc_html__('Icon Background Color (only for square and circle icon type)', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_type',
							'value'   => array('square', 'circle')
						),
						'group'       => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Hover Background Color', 'servicemaster'),
						'param_name'  => 'icon_hover_background_color',
						'description' => esc_html__('Icon Hover Background Color (only for square and circle icon type)', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_type',
							'value'   => array('square', 'circle')
						),
						'group'       => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Border Color', 'servicemaster'),
						'param_name'  => 'icon_border_color',
						'description' => esc_html__('Only for Square and Circle Icon type', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_type',
							'value'   => array('square', 'circle')
						),
						'group'       => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Icon Border Hover Color', 'servicemaster'),
						'param_name'  => 'icon_border_hover_color',
						'description' => esc_html__('Only for Square and Circle Icon type', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_type',
							'value'   => array('square', 'circle')
						),
						'group'       => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Border Width', 'servicemaster'),
						'param_name'  => 'icon_border_width',
						'description' => esc_html__('Only for Square and Circle Icon type', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_type',
							'value'   => array('square', 'circle')
						),
						'group'       => esc_html__('Icon Settings', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Title', 'servicemaster'),
						'param_name'  => 'title',
						'value'       => '',
						'admin_label' => true
					),
					array(
						'type'       => 'dropdown',
						'heading'    => esc_html__('Title Tag', 'servicemaster'),
						'param_name' => 'title_tag',
						'value'      => array(
							''   => '',
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5',
							'h6' => 'h6',
						),
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						),
						'group'      => esc_html__('Text Settings', 'servicemaster')
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Title Text Transform', 'servicemaster'),
						'param_name'  => 'title_text_transform',
						'value'       => array_flip(servicemaster_mikado_get_text_transform_array(true)),
						'save_always' => true,
						'group'       => esc_html__('Text Settings', 'servicemaster')
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Title Text Font Weight', 'servicemaster'),
						'param_name'  => 'title_text_font_weight',
						'value'       => array_flip(servicemaster_mikado_get_font_weight_array(true)),
						'save_always' => true,
						'group'       => esc_html__('Text Settings', 'servicemaster')
					),

					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Title Color', 'servicemaster'),
						'param_name' => 'title_color',
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						),
						'group'      => esc_html__('Text Settings', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Title Letter Spacing', 'servicemaster'),
						'param_name'  => 'title_letter_spacing',
						'value'       => '',
						'admin_label' => true,
						'group'      => esc_html__('Text Settings', 'servicemaster'),
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'textarea',
						'heading'    => esc_html__('Text', 'servicemaster'),
						'param_name' => 'text'
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Text Color', 'servicemaster'),
						'param_name' => 'text_color',
						'dependency' => array(
							'element'   => 'text',
							'not_empty' => true
						),
						'group'      => esc_html__('Text Settings', 'servicemaster')
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Text Align', 'servicemaster'),
						'param_name'  => 'text_align',
						'value'       => array(
							esc_html__('Left', 'servicemaster')    => 'left',
							esc_html__('Center', 'servicemaster')  => 'center',
							esc_html__('Right', 'servicemaster')   => 'right',
							esc_html__('Justify', 'servicemaster') => 'justify'
						),
						'save_always' => true,
						'dependency'  => array(
							'element' => 'icon_position',
							'value'   => array('top')
						),
						'group'       => esc_html__('Text Settings', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Link', 'servicemaster'),
						'param_name'  => 'link',
						'value'       => '',
						'admin_label' => true
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Holder Margin', 'servicemaster'),
						'param_name'  => 'holder_margin',
						'value'       => '',
						'admin_label' => true,
						'description'=> esc_html__('Margin should be set in a top right bottom left format', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_position',
							'value'   => 'left-from-title'
						),
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
						'param_name' => 'target',
						'value'      => array(
							''                                 => '',
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
						'type'        => 'textfield',
						'heading'     => esc_html__('Text Padding (px)', 'servicemaster'),
						'param_name'  => 'text_padding',
						'description' => esc_html__('Padding (top right bottom left)', 'servicemaster'),
						'dependency'  => array(
							'element' => 'icon_position',
							'value'   => array('top', 'left', 'right')
						),
						'group'       => esc_html__('Text Settings', 'servicemaster')
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Text Right Padding (px)', 'servicemaster'),
						'param_name' => 'text_right_padding',
						'dependency' => array(
							'element' => 'icon_position',
							'value'   => array('right')
						),
						'group'      => esc_html__('Text Settings', 'servicemaster')
					)
				)
			)
		));
	}

	/**
	 * @param array $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {
		$default_atts = array(
			'custom_icon'                 => '',
			'icon_position'               => '',
			'icon_type'                   => '',
			'icon_gradient_style'         => '',
			'icon_shadow'                 => 'no',
			'icon_size'                   => '',
			'custom_icon_size'            => '',
			'icon_margin'                 => '',
			'shape_size'                  => '',
			'icon_color'                  => '',
			'icon_hover_color'            => '',
			'icon_background_color'       => '',
			'icon_hover_background_color' => '',
			'icon_border_color'           => '',
			'icon_border_hover_color'     => '',
			'icon_border_width'           => '',
			'title'                       => '',
			'title_tag'                   => 'h3',
			'title_text_transform'        => '',
			'title_text_font_weight'      => '',
			'title_color'                 => '',
			'text'                        => '',
			'text_color'                  => '',
			'text_align'                  => 'left',
			'link'                        => '',
			'link_text'                   => '',
			'target'                      => '_self',
			'color'                       => '',
			'text_padding'                => '',
			'text_right_padding'          => '',
			'title_letter_spacing'		  => '',
			'holder_margin'				  => '',
			'loading_animation'        	  => '',
			'loading_animation_delay'     => '',
		);

		$default_atts = array_merge($default_atts, servicemaster_mikado_icon_collections()->getShortcodeParams());
		$params = shortcode_atts($default_atts, $atts);

		$params['element_styles'] = $this->getElementStyles($params);
		$params['icon_parameters'] = $this->getIconParameters($params);
		$params['holder_classes'] = $this->getHolderClasses($params);
		$params['holder_data'] = $this->getHolderData($params);
		$params['title_styles'] = $this->getTitleStyles($params);
		$params['content_styles'] = $this->getContentStyles($params);
		$params['custom_icon_styles'] = $this->getCustomIconStyles($params);
		$params['text_styles'] = $this->getTextStyles($params);
		$params['left_from_title_styles'] = $this->getLeftFromTitleHolderStyle($params);

		$params['button_parameters'] = $this->getButtonParameters($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/iwt', 'icon-with-text', $params['icon_position'], $params);
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

			if (!empty($params['icon_size'])) {
				$params_array['size'] = $params['icon_size'];
			}

			if (!empty($params['custom_icon_size'])) {
				$params_array['custom_size'] = $params['custom_icon_size'];
			}

			if (!empty($params['icon_type'])) {
				$params_array['type'] = $params['icon_type'];
			}

			if (!empty($params['icon_shadow'])) {
				$params_array['icon_shadow'] = $params['icon_shadow'];
			}

			$params_array['shape_size'] = $params['shape_size'];

			if (!empty($params['icon_border_color'])) {
				$params_array['border_color'] = $params['icon_border_color'];
			}

			if (!empty($params['icon_border_hover_color'])) {
				$params_array['hover_border_color'] = $params['icon_border_hover_color'];
			}

			if (!empty($params['icon_border_width'])) {
				$params_array['border_width'] = $params['icon_border_width'];
			}

			if (!empty($params['icon_background_color'])) {
				$params_array['background_color'] = $params['icon_background_color'];
			}

			if (!empty($params['icon_hover_background_color'])) {
				$params_array['hover_background_color'] = $params['icon_hover_background_color'];
			}

			$params_array['icon_color'] = $params['icon_color'];

			if (!empty($params['icon_hover_color'])) {
				$params_array['hover_icon_color'] = $params['icon_hover_color'];
			}

			$params_array['margin'] = $params['icon_margin'];
		}

		return $params_array;
	}

	/**
	 * Returns array of holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderClasses($params) {
		$classes = array('mkd-iwt', 'clearfix');

		if (!empty($params['icon_position'])) {
			switch ($params['icon_position']) {
				case 'top':
					$classes[] = 'mkd-iwt-icon-top';
					break;
				case 'left':
					$classes[] = 'mkd-iwt-icon-left';
					break;
				case 'right':
					$classes[] = 'mkd-iwt-icon-right';
					break;
				case 'left-from-title':
					$classes[] = 'mkd-iwt-left-from-title';
					break;
				default:
					break;
			}
		}

		if (!empty($params['icon_size'])) {
			$classes[] = 'mkd-iwt-' . str_replace('mkd-', '', $params['icon_size']);
		}

        if($params['icon_type'] == 'gradient') {
            $classes[] = $params['icon_gradient_style'];
        }

        if($params['loading_animation'] == 'yes') {
            $classes[] = 'mkd-iwt-loading-animation';
        }

		return $classes;
	}

	/**
	 * Returns array of holder data
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderData($params) {
		$data = array();

        if (($params['loading_animation'] == 'yes') && !empty($params['loading_animation_delay'])) {
			$data['data-loading-animation-delay'] = $params['loading_animation_delay'];
        }

		return $data;
	}

	private function getElementStyles($params) {
		$styles = array();

		if ($params['icon_position'] == 'top' && !empty($params['text_align'])) {
			$styles[] = 'text-align: ' . $params['text_align'];
		}

		return $styles;
	}

	private function getTitleStyles($params) {
		$styles = array();

		if (!empty($params['title_color'])) {
			$styles[] = 'color: ' . $params['title_color'];
		}

		if (!empty($params['title_text_transform'])) {
			$styles[] = 'text-transform: ' . $params['title_text_transform'];
		}

		if (!empty($params['title_text_font_weight'])) {
			$styles[] = 'font-weight: ' . $params['title_text_font_weight'];
		}

		if (!empty($params['title_letter_spacing'])) {
			$styles[] = 'letter-spacing: ' . $params['title_letter_spacing'];
		}

		return $styles;
	}

	private function getTextStyles($params) {
		$styles = array();

		if (!empty($params['text_color'])) {
			$styles[] = 'color: ' . $params['text_color'];
		}

		return $styles;
	}

	private function getContentStyles($params) {
		$styles = array();

		if (!empty($params['text_padding'])) {
			$styles[] = 'padding: ' . $params['text_padding'];
		}

		if ($params['icon_position'] == 'right' && !empty($params['text_right_padding'])) {
			$styles[] = 'padding-right: ' . servicemaster_mikado_filter_px($params['text_right_padding']) . 'px';
		}

		return $styles;
	}

	private function getCustomIconStyles($params) {
		$styles = array();

		if (!empty($params['icon_margin'])) {
			$styles[] = 'margin: ' . $params['icon_margin'];
		}

		return $styles;
	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		$button_params_array['type'] = 'underline';

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

	private function getLeftFromTitleHolderStyle($params){
		$styles = array();

		if (!empty($params['holder_margin'])) {
			$styles[] = 'margin: ' . $params['holder_margin'];
		}

		return $styles;
	}
}