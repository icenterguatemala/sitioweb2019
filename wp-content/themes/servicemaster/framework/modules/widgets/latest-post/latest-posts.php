<?php

class ServiceMasterMikadoLatestPosts extends ServiceMasterMikadoWidget {
	protected $params;

	public function __construct() {
		parent::__construct(
			'mkd_latest_posts_widget', // Base ID
			esc_html__('Mikado Latest Posts', 'servicemaster'), // Name
			array('description' => esc_html__('Display posts from your blog', 'servicemaster')) // Args
		);

		$this->setParams();
	}

	protected function setParams() {
		$this->params = array(
			array(
				'name'  => 'title',
				'type'  => 'textfield',
				'title' => esc_html__('Title', 'servicemaster')
			),
			array(
				'name'    => 'type',
				'type'    => 'dropdown',
				'title'   => esc_html__('Type', 'servicemaster'),
				'options' => array(
					'minimal'      => esc_html__('Minimal', 'servicemaster'),
					'image-in-box' => esc_html__('Image in box', 'servicemaster'),
					'simple'       => esc_html__('Simple', 'servicemaster')
				)
			),
			array(
				'name'    => 'number_of_visible_posts',
				'type'    => 'dropdown',
				'title'   => esc_html__('Number of visible posts', 'servicemaster'),
				'options' => array(
					'3' => '3',
					'2' => '2',
					'1' => '1'
				)
			),
			array(
				'name'  => 'number_of_posts',
				'type'  => 'textfield',
				'title' => esc_html__('Number of posts', 'servicemaster')
			),
			array(
				'name'    => 'order_by',
				'type'    => 'dropdown',
				'title'   => esc_html__('Order By', 'servicemaster'),
				'options' => array(
					'title' => esc_html__('Title', 'servicemaster'),
					'date'  => esc_html__('Date', 'servicemaster')
				)
			),
			array(
				'name'    => 'order',
				'type'    => 'dropdown',
				'title'   => esc_html__('Order', 'servicemaster'),
				'options' => array(
					'ASC'  => esc_html__('ASC', 'servicemaster'),
					'DESC' => esc_html__('DESC', 'servicemaster')
				)
			),
			array(
				'name'    => 'image_size',
				'type'    => 'dropdown',
				'title'   => esc_html__('Image Size', 'servicemaster'),
				'options' => array(
					'original'  => esc_html__('Original', 'servicemaster'),
					'landscape' => esc_html__('Landscape', 'servicemaster'),
					'square'    => esc_html__('Square', 'servicemaster'),
					'custom'    => esc_html__('Custom', 'servicemaster')
				)
			),
			array(
				'name'  => 'custom_image_size',
				'type'  => 'textfield',
				'title' => esc_html__('Custom Image Size', 'servicemaster')
			),
			array(
				'name'  => 'category',
				'type'  => 'textfield',
				'title' => esc_html__('Category Slug', 'servicemaster'),
			),
			array(
				'name'  => 'text_length',
				'type'  => 'textfield',
				'title' => esc_html__('Number of characters', 'servicemaster'),
			),
			array(
				'name'    => 'title_tag',
				'type'    => 'dropdown',
				'title'   => esc_html__('Title Tag', 'servicemaster'),
				'options' => array(
					""   => "",
					"h2" => "h2",
					"h3" => "h3",
					"h4" => "h4",
					"h5" => "h5",
					"h6" => "h6"
				)
			)
		);
	}

	public function widget($args, $instance) {
		extract($args);

		//prepare variables
		$content = '';
		$params = array();

		//is instance empty?
		if (is_array($instance) && count($instance)) {
			//generate shortcode params
			foreach ($instance as $key => $value) {
				$params[$key] = $value;
			}
		}
		if (empty($params['title_tag'])) {
			$params['title_tag'] = 'h6';
		}
		echo '<div class="widget mkd-latest-posts-widget">';

		if (!empty($instance['title'])) {
			print $args['before_title'] . $instance['title'] . $args['after_title'];
		}

		if ($params['type'] == 'simple') {
			$params['columns'] = '1';
			$params['data_attrs'] = $this->getDataAttribute($params);
		}

		echo servicemaster_mikado_execute_shortcode('mkd_blog_list', $params);

		echo '</div>'; //close mkd-latest-posts-widget
	}

	/**
	 * Return Latest posts data attribute
	 *
	 * @param $params
	 *
	 * @return string
	 */

	private function getDataAttribute($params) {

		$data_attrs = array();

		if ($params['number_of_visible_posts'] !== '') {
			$data_attrs['data-number_of_visible_posts'] = $params['number_of_visible_posts'];
		}

		return $data_attrs;
	}
}
