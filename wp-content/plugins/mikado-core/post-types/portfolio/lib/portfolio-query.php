<?php
namespace MikadoCore\CPT\Portfolio\Lib;

class PortfolioQuery {
	/**
	 * @var private instance of current class
	 */
	private static $instance;

	/**
	 * Private constuct because of Singletone
	 */
	private function __construct() {
	}

	/**
	 * Private sleep because of Singletone
	 */
	private function __wakeup() {
	}

	/**
	 * Private clone because of Singletone
	 */
	private function __clone() {
	}

	/**
	 * Returns current instance of class
	 * @return ShortcodeLoader
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			return new self;
		}

		return self::$instance;
	}

	public function queryVCParams() {
		return array(
			array(
				'type'        => 'dropdown',
				'heading'     => esc_html__('Order By', 'mikado-core'),
				'param_name'  => 'order_by',
				'value'       => array(
					esc_html__('Menu Order', 'mikado-core') => 'menu_order',
					esc_html__('Title', 'mikado-core')      => 'title',
					esc_html__('Date', 'mikado-core')       => 'date'
				),
				'admin_label' => true,
				'save_always' => true,
				'description' => '',
				'group'       => esc_html__('Query Options', 'mikado-core')
			),
			array(
				'type'        => 'dropdown',
				'heading'     => esc_html__('Order', 'mikado-core'),
				'param_name'  => 'order',
				'value'       => array(
					esc_html__('ASC', 'mikado-core')  => 'ASC',
					esc_html__('DESC', 'mikado-core') => 'DESC',
				),
				'admin_label' => true,
				'save_always' => true,
				'description' => '',
				'group'       => esc_html__('Query Options', 'mikado-core')
			),
			array(
				'type'        => 'textfield',
				'heading'     => esc_html__('One-Category Portfolio List', 'mikado-core'),
				'param_name'  => 'category',
				'value'       => '',
				'admin_label' => true,
				'description' => esc_html__('Enter one category slug (leave empty for showing all categories)', 'mikado-core'),
				'group'       => esc_html__('Query Options', 'mikado-core')
			),
			array(
				'type'        => 'textfield',
				'heading'     => esc_html__('Number of Portfolios Per Page', 'mikado-core'),
				'param_name'  => 'number',
				'value'       => '-1',
				'admin_label' => true,
				'description' => esc_html__('Enter -1 to show all', 'mikado-core'),
				'group'       => esc_html__('Query Options', 'mikado-core')
			),
			array(
				'type'        => 'textfield',
				'heading'     => esc_html__('Show Only Projects with Listed IDs', 'mikado-core'),
				'param_name'  => 'selected_projects',
				'value'       => '',
				'admin_label' => true,
				'description' => esc_html__('Delimit ID numbers by comma (leave empty for all)', 'mikado-core'),
				'group'       => esc_html__('Query Options', 'mikado-core')
			)
		);
	}

	public function getShortcodeAtts() {
		return array(
			'order_by'          => 'date',
			'order'             => 'ASC',
			'number'            => '-1',
			'category'          => '',
			'selected_projects' => '',
			'next_page'         => ''
		);
	}

	public function buildQueryObject($params) {
		$queryArray = array(
			'post_type'      => 'portfolio-item',
			'orderby'        => $params['order_by'],
			'order'          => $params['order'],
			'posts_per_page' => $params['number']
		);

		if (!empty($params['category'])) {
			$queryArray['portfolio-category'] = $params['category'];
		}

		$projectIds = null;
		if (!empty($params['selected_projects'])) {
			$projectIds = explode(',', $params['selected_projects']);
			$queryArray['post__in'] = $projectIds;
		}

		if (!empty($params['next_page'])) {
			$queryArray['paged'] = $params['next_page'];

		} else {
			$queryArray['paged'] = 1;
		}

		return new \WP_Query($queryArray);
	}
}