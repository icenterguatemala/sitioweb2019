<?php

namespace ServiceMaster\Modules\BlogList;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class BlogList
 */
class BlogList implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	function __construct() {
		$this->base = 'mkd_blog_list';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Blog List', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-blog-list extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Type', 'servicemaster'),
					'param_name'  => 'type',
					'value'       => array(
						esc_html__('Minimal', 'servicemaster')      => 'minimal',
						esc_html__('Simple', 'servicemaster')       => 'simple',
						esc_html__('Masonry', 'servicemaster')      => 'masonry',
						esc_html__('Image in box', 'servicemaster') => 'image-in-box'
					),
					'description' => '',
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Columns', 'servicemaster'),
					'param_name'  => 'columns',
					'description' => '',
					'value'       => array(
						esc_html__('Default (3)', 'servicemaster') => '',
						'1'                                        => '1',
						'2'                                        => '2',
						'3'                                        => '3',
					),
					'dependency'  => array(
						'element' => 'type',
						'value'   => 'simple'
					),
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Columns', 'servicemaster'),
					'param_name'  => 'masonry_columns',
					'description' => '',
					'value'       => array(
						'3' => '3',
						'4' => '4',
					),
					'dependency'  => array(
						'element' => 'type',
						'value'   => 'masonry'
					),
					'save_always' => true
				),
				array(
					'type'        => 'checkbox',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Simple boxed', 'servicemaster'),
					'param_name'  => 'simple_boxed',
					'dependency'  => array(
						'element' => 'type',
						'value'   => 'simple'
					),
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
					'dependency'  => array(
						'element'   => 'simple_boxed',
						'not_empty' => true
					),
					'description' => '',
					'save_always' => true
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
						'element' => 'type',
						'value'   => array('minimal', 'simple', 'image-in-box')
					),
				)
			)
		));

	}

	public function render($atts, $content = null) {

		$default_atts = array(
			'type'                    => 'minimal',
			'columns'                 => '3',
			'masonry_columns'         => '3',
			'simple_boxed'            => '',
			'skin'                    => '',
			'number_of_posts'         => '',
			'order_by'                => '',
			'order'                   => '',
			'category'                => '',
			'text_length'             => '90',
			//widget attributes
			'data_attrs'              => '',
			'number_of_visible_posts' => ''
		);

		$params = shortcode_atts($default_atts, $atts);
		$params['holder_classes'] = $this->getBlogHolderClasses($params);


		$queryArray = $this->generateBlogQueryArray($params);
		$query_result = new \WP_Query($queryArray);
		$params['query_result'] = $query_result;

		$html = '';
		$html .= servicemaster_mikado_get_shortcode_module_template_part('templates/blog-list-holder', 'blog-list', '', $params);

		return $html;

	}

	/**
	 * Generates holder classes
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getBlogHolderClasses($params) {
		$holderClasses = array(
			'mkd-blog-list-holder',
			'mkd-' . $params['type'],
		);

		if ($params['type'] === 'simple' && $params['simple_boxed']) {
			$holderClasses[] = 'boxed';
		}

		if ($params['type'] === 'masonry') {
			if ($params['masonry_columns'] == '4') {
				$holderClasses[] = 'mkd-four';
			} else {
				$holderClasses[] = 'mkd-three';
			}
		}

		if ($params['type'] === 'simple') {
			$holderClasses[] = 'mkd-' . $params['columns'];
		}


		if ($params['skin'] !== '') {
			$holderClasses[] = $params['skin'];
		}

		if (!empty($params['number_of_visible_posts'])) {
			$holderClasses[] = 'mkd-' . $params['number_of_visible_posts'] . '-visible-post';
		}

		return $holderClasses;

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
}
