<?php
namespace ServiceMaster\Modules\Shortcodes\ItemShowcaseItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class ItemShowcaseItem implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_item_showcase_item';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		vc_map(
			array(
				'name' => esc_html__('Mikado Item Showcase List Item', 'servicemaster'),
				'base' => $this->base,
				'as_child' => array('only' => 'mkd_item_showcase'),
				'as_parent' => array('except' => 'vc_row'),
				'content_element' => true,
				'category' => esc_html__('by MIKADO', 'servicemaster'),
				'icon' => 'icon-wpb-item-showcase-item extended-custom-icon',
				'show_settings_on_create' => true,
				'params' => array_merge(
                    servicemaster_mikado_icon_collections()->getVCParamsArray(),
                    array(
                        array(
                            'type'        => 'dropdown',
                            'heading'     => esc_html__('Icon Type', 'servicemaster'),
                            'param_name'  => 'icon_type',
                            'value'       => array(
                                esc_html__('Normal', 'servicemaster') => 'normal',
                                esc_html__('Circle', 'servicemaster') => 'circle',
                                esc_html__('Square', 'servicemaster') => 'square',
                            ),
                            'save_always' => true,
                            'admin_label' => true,
                        ),
                        array(
                            'type'       => 'colorpicker',
                            'heading'    => esc_html__('Icon Color', 'servicemaster'),
                            'param_name' => 'icon_color',
                            'dependency'  => array(
                                'element' => 'icon_type',
                                'value'   => array('normal', 'square', 'circle')
                            )
                        ),
                        array(
                            'type'       => 'dropdown',
                            'param_name' => 'item_position',
                            'heading'    => esc_html__('Item Position', 'servicemaster'),
                            'value'      => array(
                                esc_html__('Left', 'servicemaster') => 'left',
                                esc_html__('Right', 'servicemaster') => 'right'
                            ),
                            'admin_label' => true
                        ),
                        array(
                            'type'        => 'textfield',
                            'param_name'  => 'item_title',
                            'heading'     => esc_html__('Item Title', 'servicemaster'),
                            'admin_label' => true
                        ),
                        array(
                            'type'       => 'textfield',
                            'param_name' => 'item_link',
                            'heading'    => esc_html__('Item Link', 'servicemaster'),
                            'dependency' => array('element' => 'item_title', 'not_empty' => true)
                        ),
                        array(
                            'type'       => 'dropdown',
                            'param_name' => 'item_target',
                            'heading'    => esc_html__('Item Target', 'servicemaster'),
                            'value'       => array(
                                esc_html__('Self', 'servicemaster')  => '_self',
                                esc_html__('Blank', 'servicemaster') => '_blank'
                            ),
                            'dependency' => array('element' => 'item_link', 'not_empty' => true),
                        ),
                        array(
                            'type'        => 'dropdown',
                            'param_name'  => 'item_title_tag',
                            'heading'     => esc_html__('Item Title Tag', 'servicemaster'),
                            'value'       => array(
                                ''   => '',
                                'h1' => 'h1',
                                'h2' => 'h2',
                                'h3' => 'h3',
                                'h4' => 'h4',
                                'h5' => 'h5',
                                'h6' => 'h6',
                            ),
                            'save_always' => true,
                            'dependency'  => array('element' => 'item_title', 'not_empty' => true)
                        ),
                        array(
                            'type'       => 'colorpicker',
                            'param_name' => 'item_title_color',
                            'heading'    => esc_html__('Item Title Color', 'servicemaster'),
                            'dependency' => array('element' => 'item_title', 'not_empty' => true)
                        ),
                        array(
                            'type'       => 'textarea',
                            'param_name' => 'item_text',
                            'heading'    => esc_html__('Item Text', 'servicemaster')
                        ),
                        array(
                            'type'       => 'colorpicker',
                            'param_name' => 'item_text_color',
                            'heading'    => esc_html__('Item Text Color', 'servicemaster'),
                            'dependency' => array('element' => 'item_text', 'not_empty' => true)
					    ),
				    )
			    )
            )

        );
    }

	public function render($atts, $content = null) {
		$args = array(
            'icon_type'        => '',
            'icon_color'       => '',
			'item_position'    => 'left',
			'item_title'       => '',
			'item_link'        => '',
			'item_target'      => '_self',
			'item_title_tag'   => 'h3',
			'item_title_color' => '',
			'item_text'        => '',
			'item_text_color'  => ''
		);

        $args = array_merge($args, servicemaster_mikado_icon_collections()->getShortcodeParams());
		
		$params = shortcode_atts($args, $atts);
		extract($params);
        $params['icon_params'] = $this->getIconParameters($params);
		$params['showcase_item_class'] = $this->getShowcaseItemClasses($params);
		$params['item_target'] = !empty($item_target) ? $params['item_target'] : $args['item_target'];
		$params['item_title_tag'] = !empty($item_title_tag) ? $params['item_title_tag'] : $args['item_title_tag'];
		$params['item_title_styles'] = $this->getTitleStyles($params);
		$params['item_text_styles'] = $this->getTextStyles($params);
		
		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/item-showcase-item-template', 'item-showcase', '', $params);

		return $html;
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

        $iconPackName = servicemaster_mikado_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);

        $params_array['icon_pack']   = $params['icon_pack'];
        $params_array['icon_color']  = $params['icon_color'];

        if (!empty($params['icon_type'])) {
            $params_array['type'] = $params['icon_type'];
        }

        $params_array[$iconPackName] = $params[$iconPackName];

        $params_array['size'] = 'mkd-icon-medium';

        return $params_array;
    }

	
	/**
	 * Return Showcase Item Classes
	 *
	 * @param $params
	 * @return array
	 */
	private function getShowcaseItemClasses($params) {
		$itemClass = array();

		if (!empty($params['item_position'])) {
			$itemClass[] = 'mkd-is-'. $params['item_position'];
		}

		return implode(' ', $itemClass);
	}
	
	private function getTitleStyles($params) {
		$styles = array();
		
		if (!empty($params['item_title_color'])) {
			$styles[] = 'color: '.$params['item_title_color'];
		}
		
		return implode(';', $styles);
	}
	
	private function getTextStyles($params) {
		$styles = array();
		
		if (!empty($params['item_text_color'])) {
			$styles[] = 'color: '.$params['item_text_color'];
		}
		
		return implode(';', $styles);
	}
}
