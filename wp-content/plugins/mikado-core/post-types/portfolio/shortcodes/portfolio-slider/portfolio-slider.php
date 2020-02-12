<?php
namespace MikadoCore\CPT\Portfolio\Shortcodes;

use MikadoCore\Lib;
use MikadoCore\CPT\Portfolio\Lib\PortfolioQuery;

/**
 * Class PortfolioSlider
 * @package MikadoCore\CPT\Portfolio\Shortcodes
 */
class PortfolioSlider implements Lib\ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_portfolio_slider';

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
	 * Maps shortcode to Visual Composer
	 *
	 * @see vc_map()
	 */
	public function vcMap() {
		if (function_exists('vc_map')) {
			vc_map(array(
					'name'                      => esc_html__('Portfolio Slider', 'mikado-core'),
					'base'                      => $this->base,
					'category'                  => 'by MIKADO',
					'icon'                      => 'icon-wpb-portfolio-slider extended-custom-icon',
					'allowed_container_element' => 'vc_row',
					'params'                    => array_merge(
						array(
							array(
								'type'        => 'dropdown',
								'admin_label' => true,
								'heading'     => esc_html__('Image size', 'mikado-core'),
								'param_name'  => 'image_size',
								'value'       => array(
									esc_html__('Default', 'mikado-core')       => '',
									esc_html__('Original Size', 'mikado-core') => 'full',
									esc_html__('Square', 'mikado-core')        => 'square',
									esc_html__('Landscape', 'mikado-core')     => 'landscape',
									esc_html__('Portrait', 'mikado-core')      => 'portrait',
									esc_html__('Custom', 'mikado-core')        => 'custom'
								),
								'description' => '',
								'group'       => esc_html__('Layout Options', 'mikado-core')
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__('Hover type', 'mikado-core'),
								'param_name'  => 'slider_hover_type',
								'value'       => array(
									esc_html__('Type 1', 'mikado-core') => 'type-one',
									esc_html__('Type 2', 'mikado-core') => 'type-two',
									esc_html__('Type 3', 'mikado-core') => 'type-three'
								),
								'admin_label' => true,
								'save_always' => true,
								'group'       => esc_html__('Layout Options', 'mikado-core')
							),
							array(
								'type'       => 'colorpicker',
								'heading'    => esc_html__('Shader Background Color', 'mikado-core'),
								'param_name' => 'shader_background_color',
								'group'      => esc_html__('Layout Options', 'mikado-core')
							),
							array(
								'type'        => 'textfield',
								'admin_label' => true,
								'heading'     => esc_html__('Image Dimensions', 'mikado-core'),
								'param_name'  => 'custom_image_dimensions',
								'value'       => '',
								'description' => esc_html__('Enter custom image dimensions. Enter image size in pixels: 200x100 (Width x Height)', 'mikado-core'),
								'group'       => esc_html__('Layout Options', 'mikado-core'),
								'dependency'  => array('element' => 'image_size', 'value' => 'custom')
							),
							array(
								'type'        => 'dropdown',
								'heading'     => esc_html__('Number of Columns', 'mikado-core'),
								'param_name'  => 'columns',
								'admin_label' => true,
								'value'       => array(
									esc_html__('One', 'mikado-core')   => '1',
									esc_html__('Two', 'mikado-core')   => '2',
									esc_html__('Three', 'mikado-core') => '3',
									esc_html__('Four', 'mikado-core')  => '4'
								),
								'description' => esc_html__('Number of portfolios that are showing at the same time in full width (on smaller screens is responsive so there will be less items shown)', 'mikado-core'),
								'group'       => esc_html__('Layout Options', 'mikado-core')
							),
							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => esc_html__('Title Tag', 'mikado-core'),
								'param_name'  => 'title_tag',
								'value'       => array(
									''   => '',
									'h2' => 'h2',
									'h3' => 'h3',
									'h4' => 'h4',
									'h5' => 'h5',
									'h6' => 'h6',
								),
								'description' => '',
								'group'       => esc_html__('Layout Options', 'mikado-core')
							),
							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => esc_html__('Show Categories', 'mikado-core'),
								'param_name'  => 'show_categories',
								'value'       => array(
									esc_html__('No', 'mikado-core')  => 'no',
									esc_html__('Yes', 'mikado-core') => 'yes'
								),
								'save_always' => true,
								'description' => '',
								'group'       => esc_html__('Layout Options', 'mikado-core')
							),
							array(
								'type'        => 'dropdown',
								'class'       => '',
								'heading'     => esc_html__('Enable Navigation?', 'mikado-core'),
								'param_name'  => 'dots',
								'value'       => array(
									esc_html__('Yes', 'mikado-core') => 'yes',
									esc_html__('No', 'mikado-core')  => 'no'
								),
								'save_always' => true,
								'description' => '',
								'group'       => esc_html__('Layout Options', 'mikado-core')
							)

						),
						PortfolioQuery::getInstance()->queryVCParams()
					)
				)
			);
		}
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
			'image_size'              => 'full',
			'title_tag'               => 'h4',
			'show_categories'         => '',
			'columns'                 => '1',
			'dots'                    => 'yes',
			'slider_hover_type'		  => '',
			'shader_background_color' => '',
			'custom_image_dimensions' => ''
		);

		$args = array_merge($args, PortfolioQuery::getInstance()->getShortcodeAtts());
		$params = shortcode_atts($args, $atts);

		$query = PortfolioQuery::getInstance()->buildQueryObject($params);

		$params['query'] = $query;
		$params['holder_data'] = $this->getHolderData($params);
		$params['thumb_size'] = $this->getThumbSize($params);
		$params['caller'] = $this;
		$params['holder_classes'] = $this->getHolderClasses($params);
		$params['shader_styles'] = $this->getShaderStyles($params);

		$params['use_custom_image_size'] = false;
		if ($params['thumb_size'] === 'custom' && !empty($params['custom_image_dimensions'])) {
			$params['use_custom_image_size'] = true;
			$params['custom_image_sizes'] = $this->getCustomImageSize($params['custom_image_dimensions']);
		}

		return mkd_core_get_shortcode_module_template_part('portfolio-slider/templates/portfolio-slider-holder', 'portfolio', '', $params);
	}

	private function getHolderData($params) {
		$data = array();

		$data['data-columns'] = $params['columns'];
		$data['data-dots'] = $params['dots'];

		return $data;
	}

	public function getThumbSize($params) {
		switch ($params['image_size']) {
			case 'landscape':
				$thumbSize = 'servicemaster_mikado_landscape';
				break;
			case 'portrait':
				$thumbSize = 'servicemaster_mikado_portrait';
				break;
			case 'square':
				$thumbSize = 'servicemaster_mikado_square';
				break;
			case 'full':
				$thumbSize = 'full';
				break;
			case 'custom':
				$thumbSize = 'custom';
				break;
			default:
				$thumbSize = 'full';
				break;
		}

		return $thumbSize;
	}

	private function getHolderClasses($params) {
		$classes = array(
			'mkd-portfolio-slider-holder',
			'mkd-carousel-pagination',
			'mkd-portfolio-list-holder-outer',
			'mkd-ptf-gallery',
			'mkd-portfolio-gallery-hover'
		);

		if ($params['slider_hover_type'] != '') {
			$classes[] = 'mkd-hover-' . $params['slider_hover_type'];
		}

		return $classes;
	}

	private function getCustomImageSize($customImageSize) {
		$imageSize = trim($customImageSize);
		//Find digits
		preg_match_all('/\d+/', $imageSize, $matches);
		if (!empty($matches[0])) {
			return array(
				$matches[0][0],
				$matches[0][1]
			);
		}

		return false;
	}

	/**
	 * Generates portfolio item shader styles
	 *
	 * @param $params
	 *
	 * @return html
	 */
	public function getShaderStyles($params) {
		$style = array();

		if ($params['shader_background_color'] !== '') {
			$style[] = 'background-color:' . $params['shader_background_color'];
		}

		return $style;
	}
}