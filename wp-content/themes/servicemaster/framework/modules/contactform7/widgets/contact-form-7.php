<?php

class ServiceMasterMikadoContactForm7 extends ServiceMasterMikadoWidget {
	protected $params;

	public function __construct() {
		parent::__construct(
			'mkd_ontact_form7_widget', // Base ID
			'Mikado Contact Form 7', // Name
			array('description' => esc_html__('Display Contact Form 7', 'servicemaster'),) // Args
		);

		$this->setParams();
	}

	protected function setParams() {

		$contact_forms = array();

		$cf7 = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');

		if ($cf7) {
			foreach ($cf7 as $cform) {
				$contact_forms[$cform->ID] = $cform->post_title;
			}

		} else {
			$contact_forms[esc_html__('No contact forms found', 'servicemaster')] = 0;
		}

		$this->params = array(
			array(
				'name'  => 'title',
				'type'  => 'textfield',
				'title' => esc_html__('Title', 'servicemaster')
			),
			array(
				'name'  => 'text',
				'type'  => 'textfield',
				'title' => esc_html__('Text', 'servicemaster')
			),
			array(
				'name'  => 'background_color',
				'type'  => 'textfield',
				'title' => esc_html__('Background Color', 'servicemaster')
			),
			array(
				'name'  => 'background_image',
				'type'  => 'textfield',
				'title' => esc_html__('Background Image Url', 'servicemaster')
			),
			array(
				'name'    => 'id',
				'type'    => 'dropdown',
				'title'   => esc_html__('Contact Form', 'servicemaster'),
				'options' => $contact_forms
			),
			array(
				'name'        => 'html_class',
				'type'        => 'dropdown',
				'title'       => esc_html__('Style', 'servicemaster'),
				'options'     => array(
					'default'            => esc_html__('Default', 'servicemaster'),
					'cf7_custom_style_1' => esc_html__('Custom Style 1', 'servicemaster'),
					'cf7_custom_style_2' => esc_html__('Custom Style 2', 'servicemaster'),
					'cf7_custom_style_3' => esc_html__('Custom Style 3', 'servicemaster'),
                    'cf7_custom_style_4' => esc_html__('Custom Style 4', 'servicemaster'),

				),
				'description' => esc_html__('You can style each form element individually in Mikado Options > Contact Form 7', 'servicemaster')
			),
			array(
				'name'    => 'cf_type',
				'type'    => 'dropdown',
				'title'   => esc_html__('Choose Layout', 'servicemaster'),
				'options' => array(
					'boxed'  => esc_html__('Boxed', 'servicemaster'),
					'normal' => esc_html__('Normal', 'servicemaster')
				),
			),
			array(
				'name'    => 'color_type',
				'type'    => 'dropdown',
				'title'   => esc_html__('Choose Skin', 'servicemaster'),
				'options' => array(
					'light' => esc_html__('Light', 'servicemaster'),
					'dark'  => esc_html__('Dark', 'servicemaster')
				),
			),
		);
	}


	public function widget($args, $instance) {
		extract($args);

//      //prepare variables
//      $content        = '';
		$params = array();
//
		//is instance empty?
		if (is_array($instance) && count($instance)) {
			//generate shortcode params
			foreach ($instance as $key => $value) {
				$params[$key] = $value;
			}
		}

		$layout_type = '';
		if (($instance['cf_type']) == 'boxed') {
			$layout_type = ' mkd-widget-cf-boxed';
		}

		$cfStyles = array();

		if (($instance['background_color']) !== '') {
			$cfStyles[] = 'background-color: ' . $instance['background_color'] . '';
		}

		if (($instance['background_image']) !== '' && ($instance['background_color']) == '') {
			$cfStyles[] = 'background-image: url(' . $instance['background_image'] . ')';
		}

		$layout_color = '';
		if (($instance['color_type']) == 'light') {
			$layout_color = ' mkd-widget-cf-light';
		}

		$cf_custom_style = '';
		if (($instance['html_class']) === 'cf7_custom_style_1') {
			$cf_custom_style = ' cf7_custom_style_1';
		} elseif (($instance['html_class']) === 'cf7_custom_style_2') {
			$cf_custom_style = ' cf7_custom_style_2';
		} elseif (($instance['html_class']) === 'cf7_custom_style_3') {
            $cf_custom_style = ' cf7_custom_style_3';
        } elseif (($instance['html_class']) === 'cf7_custom_style_4') {
            $cf_custom_style = ' cf7_custom_style_4';
        }

		echo '<div class="widget mkd-contact-form-7-widget' . $layout_type . $layout_color . $cf_custom_style . '" ' . servicemaster_mikado_get_inline_style($cfStyles) . '>';

		echo '<div class="mkd-contact-form-title">';
		if (!empty($instance['title'])) {
			print $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		echo '</div>';

		echo '<div class="mkd-contact-form-text">';
		if (!empty($instance['text'])) {
			print $args['before_title'] . $instance['text'] . $args['after_title'];
		}
		echo '</div>';

		echo servicemaster_mikado_execute_shortcode('contact-form-7', $params);

		echo '</div>'; //close mkd-contact-form-7-widget
	}
}

add_action('widgets_init', function () {
	register_widget('ServiceMasterMikadoContactForm7');
});