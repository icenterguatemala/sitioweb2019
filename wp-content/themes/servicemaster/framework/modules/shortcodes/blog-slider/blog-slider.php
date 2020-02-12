<?php
namespace ServiceMaster\Modules\Shortcodes\BlogSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class BlogSlider implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	function __construct() {
		$this->base = 'mkd_blog_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Blog Slider', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-blog-slider extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Type', 'servicemaster'),
					'param_name'  => 'slider_type',
					'value'       => array(
						esc_html__('Simple', 'servicemaster')  => 'simple',
						esc_html__('Masonry', 'servicemaster') => 'masonry'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Skin', 'servicemaster'),
					'param_name'  => 'skin',
					'value'       => array(
						esc_html__('Light', 'servicemaster') => 'light',
						esc_html__('Dark', 'servicemaster')  => 'dark'
					),
					'description' => '',
					'dependency'  => array(
						'element' => 'slider_type',
						'value'   => 'simple'
					),
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'class'       => '',
					'heading'     => esc_html__('Enable Navigation?', 'servicemaster'),
					'param_name'  => 'dots',
					'value'       => array(
						esc_html__('Yes', 'servicemaster') => 'yes',
						esc_html__('No', 'servicemaster')  => 'no'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Number of Posts', 'servicemaster'),
					'param_name'  => 'number_of_posts',
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Order By', 'servicemaster'),
					'param_name'  => 'order_by',
					'value'       => array(
						esc_html__('Title', 'servicemaster') => 'title',
						esc_html__('Date', 'servicemaster')  => 'date'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Order', 'servicemaster'),
					'param_name'  => 'order',
					'value'       => array(
						esc_html__('ASC', 'servicemaster')  => 'ASC',
						esc_html__('DESC', 'servicemaster') => 'DESC'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Category Slug', 'servicemaster'),
					'param_name'  => 'category',
					'description' => esc_html__('Leave empty for all or use comma for list', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Text length', 'servicemaster'),
					'param_name'  => 'text_length',
					'description' => esc_html__('Number of characters', 'servicemaster'),
					'dependency'  => array(
						'element' => 'slider_type',
						'value'   => array('simple')
					),
				)
			)
		));

	}

	public function render($atts, $content = null) {

		$default_atts = array(
			'slider_type'     => 'one',
			'dots'            => 'yes',
			'skin'            => '',
			'number_of_posts' => '',
			'order_by'        => '',
			'order'           => '',
			'category'        => '',
			'text_length'     => '90',
		);

		$params = shortcode_atts($default_atts, $atts);

		$queryParams = $this->generateBlogQueryArray($params);

		$query = new \WP_Query($queryParams);

		$params['query'] = $query;

		$params['holder_classes'] = $this->getHolderClasses($params);
		$params['holder_data'] = $this->getHolderData($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/blog-slider-template-' . $params['slider_type'], 'blog-slider', '', $params);
	}


	/**
	 * Generates query array
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function generateBlogQueryArray($params) {

		$queryArray = array(
			'orderby'        => $params['order_by'],
			'order'          => $params['order'],
			'posts_per_page' => $params['number_of_posts'],
			'category_name'  => $params['category']
		);

		return $queryArray;
	}

	/**
	 * Returns array of holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderClasses($params) {
		$classes = array('mkd-blog-slider-holder');

		$classes[] = $params['slider_type'];

		if ($params['skin'] !== '') {
			$classes[] = $params['skin'];
		}

		return $classes;
	}

	/**
	 * Returns array of holder data attributes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderData($params) {
		$data = array();

		$data['data-dots'] = $params['dots'];

		return $data;
	}
}