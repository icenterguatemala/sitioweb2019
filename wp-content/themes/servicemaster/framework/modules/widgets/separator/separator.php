<?php

/**
 * Widget that adds separator boxes type
 *
 * Class Separator_Widget
 */
class ServiceMasterMikadoSeparatorWidget extends ServiceMasterMikadoWidget {
	/**
	 * Set basic widget options and call parent class construct
	 */
	public function __construct() {
		parent::__construct(
			'mkd_separator_widget', // Base ID
			esc_html__('Mikado Separator Widget', 'servicemaster') // Name
		);

		$this->setParams();
	}

	/**
	 * Sets widget options
	 */
	protected function setParams() {
		$this->params = array(
			array(
				'type'    => 'dropdown',
				'title'   => esc_html__('Type', 'servicemaster'),
				'name'    => 'type',
				'options' => array(
					'normal'     => esc_html__('Normal', 'servicemaster'),
					'full-width' => esc_html__('Full Width', 'servicemaster')
				)
			),
			array(
				'type'    => 'dropdown',
				'title'   => esc_html__('Position', 'servicemaster'),
				'name'    => 'position',
				'options' => array(
					'center' => esc_html__('Center', 'servicemaster'),
					'left'   => esc_html__('Left', 'servicemaster'),
					'right'  => esc_html__('Right', 'servicemaster')
				)
			),
			array(
				'type'    => 'dropdown',
				'title'   => esc_html__('Style', 'servicemaster'),
				'name'    => 'border_style',
				'options' => array(
					'solid'  => esc_html__('Solid', 'servicemaster'),
					'dashed' => esc_html__('Dashed', 'servicemaster'),
					'dotted' => esc_html__('Dotted', 'servicemaster')
				)
			),
			array(
				'type'  => 'textfield',
				'title' => esc_html__('Color', 'servicemaster'),
				'name'  => 'color'
			),
			array(
				'type'        => 'textfield',
				'title'       => esc_html__('Width', 'servicemaster'),
				'name'        => 'width',
				'description' => ''
			),
			array(
				'type'        => 'textfield',
				'title'       => esc_html__('Thickness (px)', 'servicemaster'),
				'name'        => 'thickness',
				'description' => ''
			),
			array(
				'type'        => 'textfield',
				'title'       => esc_html__('Top Margin', 'servicemaster'),
				'name'        => 'top_margin',
				'description' => ''
			),
			array(
				'type'        => 'textfield',
				'title'       => esc_html__('Bottom Margin', 'servicemaster'),
				'name'        => 'bottom_margin',
				'description' => ''
			)
		);
	}

	/**
	 * Generates widget's HTML
	 *
	 * @param array $args args from widget area
	 * @param array $instance widget's options
	 */
	public function widget($args, $instance) {

		extract($args);

		//prepare variables
		$params = '';

		//is instance empty?
		if (is_array($instance) && count($instance)) {
			//generate shortcode params
			foreach ($instance as $key => $value) {
				$params .= " $key='$value' ";
			}
		}

		echo '<div class="widget mkd-separator-widget">';

		//finally call the shortcode
		echo do_shortcode("[mkd_separator $params]"); // XSS OK

		echo '</div>'; //close div.mkd-separator-widget
	}
}