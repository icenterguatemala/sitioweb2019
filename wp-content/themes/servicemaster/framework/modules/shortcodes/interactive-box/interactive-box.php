<?php
namespace ServiceMaster\Modules\Shortcodes\InteractiveBox;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class InteractiveBox
 * @package ServiceMaster\Modules\Shortcodes\InteractiveBox
 */
class InteractiveBox implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	/**
	 *
	 */
	public function __construct() {
		$this->base = 'mkd_interactive_box';

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
			'name'                      => esc_html__('Interactive Box', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-icon-with-text extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array_merge(
				servicemaster_mikado_icon_collections()->getVCParamsArray(array(), '', true),
				array(
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Icon Color', 'servicemaster'),
						'param_name' => 'icon_color',
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Icon Hover Color', 'servicemaster'),
						'param_name' => 'icon_hover_color',
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
						)
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Title Color', 'servicemaster'),
						'param_name' => 'title_color',
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Title Hover Color', 'servicemaster'),
						'param_name' => 'title_hover_color',
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Separator Color', 'servicemaster'),
						'param_name' => 'separator_color',
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Separator Hover Color', 'servicemaster'),
						'param_name' => 'separator_hover_color',
						'dependency' => array(
							'element'   => 'title',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Background Color', 'servicemaster'),
						'param_name' => 'background_color',
					),
					array(
						'type'       => 'colorpicker',
						'heading'    => esc_html__('Background Hover Color', 'servicemaster'),
						'param_name' => 'background_hover_color',
					),
					array(
						'type'       => 'attach_image',
						'heading'    => esc_html__('Hover Image', 'servicemaster'),
						'param_name' => 'hover_image'
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Link', 'servicemaster'),
						'param_name'  => 'link',
						'value'       => '',
						'admin_label' => true
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Target', 'servicemaster'),
						'param_name'  => 'link_target',
						'value'       => array(
							esc_html__('Self', 'servicemaster')  => '_self',
							esc_html__('Blank', 'servicemaster') => '_blank'
						),
						'save_always' => true,
						'dependency'  => array(
							'element'   => 'link',
							'not_empty' => true
						),
					),
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
			'icon_color'             => '',
			'icon_hover_color'       => '',
			'title'                  => '',
			'title_tag'              => 'h3',
			'title_color'            => '',
			'title_hover_color'      => '',
			'separator_color'        => '',
			'separator_hover_color'  => '',
			'background_color'       => '',
			'background_hover_color' => '',
			'hover_image'            => '',
			'link'                   => '',
			'link_target'            => '',
		);

		$default_atts = array_merge($default_atts, servicemaster_mikado_icon_collections()->getShortcodeParams());
		$params = shortcode_atts($default_atts, $atts);

		$params['icon_parameters'] = $this->getIconParameters($params);
		$params['styles'] = $this->getStyles($params);
		$params['hover_styles'] = $this->getHoverStyles($params);
		$params['holder_classes'] = $this->getHolderClasses($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/interactive-box-template', 'interactive-box', '', $params);
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

		$params_array['icon_pack'] = $params['icon_pack'];
		if ($params['icon_pack']) {
			$params_array[$iconPackName] = $params[$iconPackName];
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
	private function getHoverStyles($params) {
		$hover_styles = array();

		if ($params['icon_hover_color'] !== '') {
			$hover_styles['data-icon_hover_color'] = $params['icon_hover_color'];
		}

		if ($params['title_hover_color'] !== '') {
			$hover_styles['data-title_hover_color'] = $params['title_hover_color'];
		}

		if ($params['separator_hover_color'] !== '') {
			$hover_styles['data-separator_hover_color'] = $params['separator_hover_color'];
		}

		return $hover_styles;
	}

	/**
	 * Returns parameters for icon shortcode as a string
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getStyles($params) {
		$styles = array();

		//content background styles
		$styles['mkd-ib-content-background'] = array();

		if (!empty($params['background_color'])) {
			$styles['mkd-ib-content-background'][] = 'background-color:' . $params['background_color'];
		}

		//hover image styles
		$styles['mkd-ib-hover-image'] = array();

		if (!empty($params['background_hover_color'])) {
			$styles['mkd-ib-hover-image'][] = 'background-color:' . $params['background_hover_color'];
		}
		if (!empty($params['hover_image'])) {
			$styles['mkd-ib-hover-image'][] = 'background-image: url(' . wp_get_attachment_url($params['hover_image']) . ')';
		}

		//icon styles
		$styles['mkd-ib-icon'] = array();

		if (!empty($params['icon_color'])) {
			$styles['mkd-ib-icon'][] = 'color:' . $params['icon_color'];
		}

		//title styles
		$styles['mkd-ib-title'] = array();

		if (!empty($params['title_color'])) {
			$styles['mkd-ib-title'][] = 'color:' . $params['title_color'];
		}

		return $styles;
	}


	/**
	 * Returns holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderClasses($params) {
		$classes = array('mkd-ib-holder');

		if (empty($params['hover_image'])) {
			$classes[] = 'mkd-ib-without-hover-image';
		}

		return $classes;
	}
}