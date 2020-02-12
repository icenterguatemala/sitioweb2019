<?php
namespace ServiceMaster\Modules\ProductList;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class ProductList
 */
class ProductList implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	function __construct() {
		$this->base = 'mkd_product_list';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Product List', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-product-list extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Type', 'servicemaster'),
					'param_name'  => 'product_list_type',
					'value'       => array(
						esc_html__('Simple', 'servicemaster')           => 'simple',
						esc_html__('Boxed', 'servicemaster')            => 'boxed',
						esc_html__('Hover', 'servicemaster')            => 'hover',
						esc_html__('Masonry', 'servicemaster')          => 'masonry',
						esc_html__('Lookbook', 'servicemaster')         => 'lookbook',
						esc_html__('Lookbook Masonry', 'servicemaster') => 'lookbook-masonry'
					),
					'description' => '',
					'save_always' => true
				),
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Number of Products', 'servicemaster'),
					'param_name'  => 'number_of_posts',
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Number of Columns', 'servicemaster'),
					'param_name'  => 'number_of_columns',
					'value'       => array(
						esc_html__('Three', 'servicemaster') => '3',
						esc_html__('Four', 'servicemaster')  => '4',
						esc_html__('Five', 'servicemaster')  => '5',
						esc_html__('Six', 'servicemaster')   => '6',
					),
					'description' => '',
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Order By', 'servicemaster'),
					'param_name'  => 'order_by',
					'value'       => array(
						esc_html__('Title', 'servicemaster')      => 'title',
						esc_html__('Date', 'servicemaster')       => 'date',
						esc_html__('Random', 'servicemaster')     => 'rand',
						esc_html__('Post Name', 'servicemaster')  => 'name',
						esc_html__('ID', 'servicemaster')         => 'id',
						esc_html__('Menu Order', 'servicemaster') => 'menu_order'
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
					'type'        => 'dropdown',
					'heading'     => esc_html__('Choose Sorting Taxonomy', 'servicemaster'),
					'param_name'  => 'taxonomy_to_display',
					'value'       => array(
						esc_html__('Category', 'servicemaster') => 'category',
						esc_html__('Tag', 'servicemaster')      => 'tag',
						esc_html__('Id', 'servicemaster')       => 'id'
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => esc_html__('If you would like to display only certain products, this is where you can select the criteria by which you would like to choose which products to display.', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Enter Taxonomy Values', 'servicemaster'),
					'param_name'  => 'taxonomy_values',
					'value'       => '',
					'admin_label' => true,
					'description' => esc_html__('Separate values (category slugs, tags, or post IDs) with a comma', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Space', 'servicemaster'),
					'param_name'  => 'space',
					'value'       => array(
						esc_html__('Yes', 'servicemaster') => 'yes',
						esc_html__('No', 'servicemaster')  => 'no',
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => esc_html__('Space between items', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Title tag', 'servicemaster'),
					'param_name'  => 'title_tag',
					'value'       => array(
						''   => '',
						'h1' => 'h1',
						'h2' => 'h2',
						'h3' => 'h3',
						'h4' => 'h4',
						'h5' => 'h6'
					),
					'admin_label' => true,
					'dependency'  => array(
						'element' => 'product_list_type',
						'value'   => array('simple', 'boxed', 'hover', 'masonry')
					),
					'description' => esc_html__('If you would like the rating to be displayed on your product list, you also need to enable the WooCommerce rating option', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Show Rating', 'servicemaster'),
					'param_name'  => 'show_rating',
					'value'       => array(
						esc_html__('Yes', 'servicemaster') => 'yes',
						esc_html__('No', 'servicemaster')  => 'no',
					),
					'save_always' => true,
					'admin_label' => true,
					'dependency'  => array(
						'element' => 'product_list_type',
						'value'   => array('simple', 'boxed', 'hover', 'masonry')
					),
					'description' => esc_html__('If you would like the rating to be displayed on your product list, you also need to enable the WooCommerce rating option', 'servicemaster')
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__('Box Background Color', 'servicemaster'),
					'param_name'  => 'box_background_color',
					'group'       => esc_html__('Design Options', 'servicemaster'),
					'dependency'  => array(
						'element' => 'product_list_type',
						'value'   => array('boxed', 'hover')
					),
					'admin_label' => true
				),
			)
		));
	}

	public function render($atts, $content = null) {

		$default_atts = array(
			'product_list_type'    => 'simple',
			'number_of_posts'      => '8',
			'number_of_columns'    => '4',
			'order_by'             => '',
			'order'                => '',
			'taxonomy_to_display'  => 'category',
			'taxonomy_values'      => '',
			'space'                => 'yes',
			'title_tag'            => 'h5',
			'show_rating'          => 'yes',
			'box_background_color' => ''
		);

		$params = shortcode_atts($default_atts, $atts);
		extract($params);
		$params['holder_classes'] = $this->getHolderClasses($params);

		$params['productListObject'] = $this;

		$queryArray = $this->generateProductQueryArray($params);
		$query_result = new \WP_Query($queryArray);
		$params['query_result'] = $query_result;

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/product-list-template-' . $params['product_list_type'], 'product-list', '', $params);
		return $html;
	}

	/**
	 * Generates holder classes
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getHolderClasses($params) {
		$holderClasses = array('mkd-pl-holder', 'woocommerce');

		$columnNumber = $this->getColumnNumberClass($params);

		$holderClasses[] = $params['product_list_type'];
		$holderClasses[] = $columnNumber;

		if ($params['space'] == 'no') {
			$holderClasses[] = 'no-space';
		}

		return $holderClasses;
	}

	/**
	 * Generates columns number classes for product list holder
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function getColumnNumberClass($params) {

		$columnsNumber = '';
		$columns = $params['number_of_columns'];

		switch ($columns) {
			case 3:
				$columnsNumber = 'mkd-three-columns';
				break;
			case 4:
				$columnsNumber = 'mkd-four-columns';
				break;
			case 5:
				$columnsNumber = 'mkd-five-columns';
				break;
			case 6:
				$columnsNumber = 'mkd-six-columns';
				break;
			default:
				$columnsNumber = 'mkd-four-columns';
				break;
		}

		return $columnsNumber;
	}

	/**
	 * Generates query array
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function generateProductQueryArray($params) {

		$queryArray = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $params['number_of_posts'],
			'orderby'             => $params['order_by'],
			'order'               => $params['order'],
			'meta_query'          => WC()->query->get_meta_query()
		);

		if ($params['taxonomy_to_display'] !== '' && $params['taxonomy_to_display'] === 'category') {
			$queryArray['product_cat'] = $params['taxonomy_values'];
		}

		if ($params['taxonomy_to_display'] !== '' && $params['taxonomy_to_display'] === 'tag') {
			$queryArray['product_tag'] = $params['taxonomy_values'];
		}

		if ($params['taxonomy_to_display'] !== '' && $params['taxonomy_to_display'] === 'id') {
			$idArray = $params['taxonomy_values'];
			$ids = explode(',', $idArray);
			$queryArray['post__in'] = $ids;
		}

		return $queryArray;
	}

	/**
	 * Returns Thumbnail size for Masonry Product list
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function getMasonryProductListThumbnail($id) {
		$masonry_size = 'standard';
		$thumb_size = 'servicemaster_mikado_square';

		$masonry_size = get_post_meta($id, 'mkd_masonry_product_list_dimensions_meta', true);

		switch ($masonry_size):
			case 'standard' :
				$thumb_size = 'servicemaster_mikado_square';
				break;
			case 'large-width' :
				$thumb_size = 'servicemaster_mikado_large_width';
				break;
			case 'large-height' :
				$thumb_size = 'servicemaster_mikado_large_height';
				break;
			case 'large-width-height' :
				$thumb_size = 'servicemaster_mikado_large_width_height';
				break;
		endswitch;

		return $thumb_size;
	}
}