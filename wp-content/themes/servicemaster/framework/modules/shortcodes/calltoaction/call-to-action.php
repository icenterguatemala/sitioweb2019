<?php
namespace ServiceMaster\Modules\CallToAction;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class CallToAction
 */
class CallToAction implements ShortcodeInterface {

	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_call_to_action';

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

		$call_to_action_button_icons_array = array();
		$call_to_action_button_IconCollections = servicemaster_mikado_icon_collections()->iconCollections;
		foreach ($call_to_action_button_IconCollections as $collection_key => $collection) {

			$call_to_action_button_icons_array[] = array(
				'type'        => 'dropdown',
				'heading'     => esc_html__('Button Icon', 'servicemaster'),
				'param_name'  => 'button_' . $collection->param,
				'value'       => $collection->getIconsArray(),
				'save_always' => true,
				'dependency'  => array(
					'element' => 'button_icon_pack',
					'value'   => array($collection_key)
				)
			);

		}

		vc_map(array(
			'name'                      => esc_html__('Call to Action', 'servicemaster'),
			'base'                      => $this->getBase(),
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-call-to-action extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array_merge(
				array(
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Full Width', 'servicemaster'),
						'param_name'  => 'full_width',
						'admin_label' => true,
						'value'       => array(
							'Yes' => 'yes',
							'No'  => 'no'
						),
						'save_always' => true,
						'description' => '',
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Content in grid', 'servicemaster'),
						'param_name'  => 'content_in_grid',
						'value'       => array(
							'Yes' => 'yes',
							'No'  => 'no'
						),
						'save_always' => true,
						'description' => '',
						'dependency'  => array(
							'element' => 'full_width',
							'value'   => 'yes'
						)
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Grid size', 'servicemaster'),
						'param_name'  => 'grid_size',
						'value'       => array(
							'75/25' => '75',
							'50/50' => '50',
							'66/33' => '66'
						),
						'save_always' => true,
						'description' => '',
						'dependency'  => array(
							'element' => 'content_in_grid',
							'value'   => 'yes'
						)
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Type', 'servicemaster'),
						'param_name'  => 'type',
						'admin_label' => true,
						'value'       => array(
							'Normal'    => 'normal',
							'With Icon' => 'with-icon',
						),
						'save_always' => true,
						'description' => ''
					)
				),
				servicemaster_mikado_icon_collections()->getVCParamsArray(array(
					'element' => 'type',
					'value'   => array('with-icon')
				)),
				array(
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Icon Size (px)', 'servicemaster'),
						'param_name'  => 'icon_size',
						'description' => '',
						'dependency'  => array(
							'element' => 'type',
							'value'   => array('with-icon')
						),
						'group'       => esc_html__('Design Options', 'servicemaster'),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Box Padding (top right bottom left) px', 'servicemaster'),
						'param_name'  => 'box_padding',
						'admin_label' => true,
						'description' => esc_html__('Default padding is 20px on all sides', 'servicemaster'),
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Box Shadow', 'servicemaster'),
                        'param_name'  => 'box_shadow',
                        'value'       => array(
                            esc_html__('No', 'servicemaster')  => 'no',
                            esc_html__('Yes', 'servicemaster') => 'yes'
                        ),
                        'admin_label' => true,
                        'save_always' => true,
                        'description' => '',
                        'group'       => esc_html__('Design Options', 'servicemaster')
                    ),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Box Background Color', 'servicemaster'),
						'param_name'  => 'box_background_color',
						'admin_label' => true,
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Box Border Color', 'servicemaster'),
						'param_name'  => 'box_border_color',
						'admin_label' => true,
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Box Border Width (px)', 'servicemaster'),
						'param_name'  => 'box_border_width',
						'admin_label' => true,
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Default Text Font Size (px)', 'servicemaster'),
						'param_name'  => 'text_size',
						'description' => esc_html__('Font size for p tag', 'servicemaster'),
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Show Button', 'servicemaster'),
						'param_name'  => 'show_button',
						'value'       => array(
							esc_html__('Yes', 'servicemaster') => 'yes',
							esc_html__('No', 'servicemaster')  => 'no'
						),
						'admin_label' => true,
						'save_always' => true,
						'description' => ''
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Button Type', 'servicemaster'),
						'param_name'  => 'button_type',
						'value'       => array_flip(servicemaster_mikado_get_btn_types(true)),
						'save_always' => true,
						'admin_label' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						),
						'group'       => esc_html__('Design Options', 'servicemaster'),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Hover Button Type', 'servicemaster'),
						'param_name'  => 'button_hover_type',
						'value'       => array_flip(servicemaster_mikado_get_btn_types(true)),
						'save_always' => true,
						'admin_label' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						),
						'group'       => esc_html__('Design Options', 'servicemaster'),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Button Background Color', 'servicemaster'),
						'param_name'  => 'button_background_color',
						'admin_label' => true,
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => esc_html__('Button Background Hover Color', 'servicemaster'),
						'param_name'  => 'button_hover_color',
						'admin_label' => true,
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => esc_html__('Button Text Color', 'servicemaster'),
                        'param_name'  => 'button_text_color',
                        'admin_label' => true,
                        'group'       => esc_html__('Design Options', 'servicemaster')
                    ),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => esc_html__('Button Text Hover Color', 'servicemaster'),
                        'param_name'  => 'button_text_hover_color',
                        'admin_label' => true,
                        'group'       => esc_html__('Design Options', 'servicemaster')
                    ),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Button Hover Animation', 'servicemaster'),
						'param_name'  => 'button_hover_animation',
						'value'       => array_flip(servicemaster_mikado_get_btn_hover_animation_types(true)),
						'save_always' => true,
						'admin_label' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						),
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Button Position', 'servicemaster'),
						'param_name'  => 'button_position',
						'value'       => array(
							esc_html__('Default/right', 'servicemaster') => '',
							esc_html__('Center', 'servicemaster')        => 'center',
							esc_html__('Left', 'servicemaster')          => 'left'
						),
						'description' => '',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						)
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Button Size', 'servicemaster'),
						'param_name'  => 'button_size',
						'value'       => array(
							esc_html__('Default', 'servicemaster')     => '',
							esc_html__('Small', 'servicemaster')       => 'small',
							esc_html__('Medium', 'servicemaster')      => 'medium',
							esc_html__('Large', 'servicemaster')       => 'large',
							esc_html__('Extra Large', 'servicemaster') => 'big_large'
						),
						'description' => '',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						),
						'group'       => esc_html__('Design Options', 'servicemaster')
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Button Text', 'servicemaster'),
						'param_name'  => 'button_text',
						'admin_label' => true,
						'description' => esc_html__('Default text is "button"', 'servicemaster'),
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						)
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Button Link', 'servicemaster'),
						'param_name'  => 'button_link',
						'description' => '',
						'admin_label' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						)
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Button Target', 'servicemaster'),
						'param_name'  => 'button_target',
						'value'       => array(
							''      => '',
							'Self'  => '_self',
							'Blank' => '_blank'
						),
						'description' => '',
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						)
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Button Icon Pack', 'servicemaster'),
						'param_name'  => 'button_icon_pack',
						'value'       => array_merge(array('No Icon' => ''), servicemaster_mikado_icon_collections()->getIconCollectionsVC()),
						'save_always' => true,
						'dependency'  => array(
							'element' => 'show_button',
							'value'   => array('yes')
						)
					)
				),
				$call_to_action_button_icons_array,
				array(
					array(
						'type'        => 'textarea_html',
						'admin_label' => true,
						'heading'     => esc_html__('Content', 'servicemaster'),
						'param_name'  => 'content',
						'value'       => '<p>' . 'I am test text for Call to action.' . '</p>',
						'description' => ''
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

		$args = array(
			'type'                    => 'normal',
			'button_type'             => '',
			'button_hover_type'       => '',
			'full_width'              => 'yes',
			'content_in_grid'         => 'yes',
			'grid_size'               => '75',
			'icon_size'               => '',
			'box_padding'             => '20px',
            'box_shadow'             => 'no',
			'box_background_color'    => '',
			'box_border_color'        => '',
			'box_border_width'        => '',
			'text_size'               => '',
			'show_button'             => 'yes',
			'button_position'         => 'right',
			'button_size'             => '',
			'button_link'             => '',
			'button_text'             => 'button',
			'button_target'           => '',
			'button_icon_pack'        => '',
			'button_hover_animation'  => '',
			'button_background_color' => '',
			'button_hover_color'      => '',
            'button_text_color'       => '',
            'button_text_hover_color' => ''
		);

		$call_to_action_icons_form_fields = array();

		foreach (servicemaster_mikado_icon_collections()->iconCollections as $collection_key => $collection) {

			$call_to_action_icons_form_fields['button_' . $collection->param] = '';

		}

		$args = array_merge($args, servicemaster_mikado_icon_collections()->getShortcodeParams(), $call_to_action_icons_form_fields);

		$params = shortcode_atts($args, $atts);

		$params['content'] = $content;
        $params['holder_classes'] = $this->getHolderClasses($params);
		$params['text_wrapper_classes'] = $this->getTextWrapperClasses($params);
		$params['content_styles'] = $this->getContentStyles($params);
		$params['call_to_action_styles'] = $this->getCallToActionStyles($params);
		$params['icon'] = $this->getCallToActionIcon($params);
		$params['button_parameters'] = $this->getButtonParameters($params);

		$params['button_type'] = !empty($params['button_type']) ? $params['button_type'] : 'solid';

		//Get HTML from template
		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/call-to-action-template', 'calltoaction', '', $params);

		return $html;

	}

    /**
     * Return Classes for Call To Action text wrapper
     *
     * @param $params
     *
     * @return string
     */
    private function getHolderClasses($params) {
        $classes = array('mkd-call-to-action');

        if ($params['box_shadow'] == 'yes') {
            $classes[] = 'box-shadow';
        }

        $classes[] = $params['type'];

        return $classes;
    }

	/**
	 * Return Classes for Call To Action text wrapper
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getTextWrapperClasses($params) {
		$classes = array('mkd-text-wrapper');

		if ($params['show_button'] == 'yes') {
			$classes[] = 'mkd-call-to-action-column1 mkd-call-to-action-cell';
		}

		return $classes;
	}

	/**
	 * Return CSS styles for Call To Action Icon
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getIconStyles($params) {
		$icon_style = array();

		if ($params['icon_size'] !== '') {
			$icon_style[] = 'font-size: ' . $params['icon_size'] . 'px';
		}

		return implode(';', $icon_style);
	}

	/**
	 * Return CSS styles for Call To Action Content
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getContentStyles($params) {
		$content_styles = array();

		if ($params['text_size'] !== '') {
			$content_styles[] = 'font-size: ' . $params['text_size'] . 'px';
		}

		return implode(';', $content_styles);
	}

	/**
	 * Return CSS styles for Call To Action shortcode
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getCallToActionStyles($params) {
		$call_to_action_styles = array();

		if ($params['box_padding'] != '') {
			$call_to_action_styles[] = 'padding: ' . $params['box_padding'] . ';';
		}

		if ($params['box_background_color'] != '') {
			$call_to_action_styles[] = 'background-color: ' . $params['box_background_color'] . ';';
		}

		if ($params['box_border_color'] != '') {
			$call_to_action_styles[] = 'border-color: ' . $params['box_border_color'] . ';';
			$call_to_action_styles[] = 'border-style: solid';

			$border_width = 2;

			if ($params['box_border_width'] !== '') {
				$border_width = servicemaster_mikado_filter_px($params['box_border_width']);
			}

			$call_to_action_styles[] = 'border-width: ' . $border_width . 'px';
		}

		return $call_to_action_styles;
	}

	/**
	 * Return Icon for Call To Action Shortcode
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	private function getCallToActionIcon($params) {

		$icon = servicemaster_mikado_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);
		$iconStyles = array();
		$iconStyles['icon_attributes']['style'] = $this->getIconStyles($params);
		$call_to_action_icon = '';
		if (!empty($params[$icon])) {
			$call_to_action_icon = servicemaster_mikado_icon_collections()->renderIcon($params[$icon], $params['icon_pack'], $iconStyles);
		}

		return $call_to_action_icon;

	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		if (!empty($params['button_link'])) {
			$button_params_array['link'] = $params['button_link'];
		}

		if (!empty($params['button_size'])) {
			$button_params_array['size'] = $params['button_size'];
		}
		if (!empty($params['button_background_color'])) {
			$button_params_array['background_color'] = $params['button_background_color'];
			$button_params_array['border_color'] = $params['button_background_color'];
		}
		if (!empty($params['button_hover_color'])) {
			$button_params_array['hover_background_color'] = $params['button_hover_color'];
			$button_params_array['hover_border_color'] = $params['button_hover_color'];
		}

        if (!empty($params['button_text_color'])) {
            $button_params_array['color'] = $params['button_text_color'];
        }

        if (!empty($params['button_text_hover_color'])) {
            $button_params_array['hover_color'] = $params['button_text_hover_color'];
        }

		if (!empty($params['button_type'])) {
			$button_params_array['type'] = $params['button_type'];
		}

		if (!empty($params['button_icon_pack'])) {
			$button_params_array['icon_pack'] = $params['button_icon_pack'];
			$iconPackName = servicemaster_mikado_icon_collections()->getIconCollectionParamNameByKey($params['button_icon_pack']);
			$button_params_array[$iconPackName] = $params['button_' . $iconPackName];
		}

		if (!empty($params['button_target'])) {
			$button_params_array['target'] = $params['button_target'];
		}

		if (!empty($params['button_text'])) {
			$button_params_array['text'] = $params['button_text'];
		}

		$button_params_array['hover_type'] = $params['button_hover_type'];
		$button_params_array['hover_animation'] = $params['button_hover_animation'];

		return $button_params_array;
	}
}