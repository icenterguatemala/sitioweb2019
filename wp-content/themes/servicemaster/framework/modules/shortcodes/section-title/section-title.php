<?php
namespace ServiceMaster\Modules\Shortcodes\SectionTitle;

use ServiceMaster\Modules\Shortcodes\Lib;

class SectionTitle implements Lib\ShortcodeInterface {
	private $base;

	/**
	 * SectionTitle constructor.
	 */
	public function __construct() {
		$this->base = 'mkd_section_title';

		add_action('vc_before_init', array($this, 'vcMap'));
	}


	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Section Title', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-section-title extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Title', 'servicemaster'),
					'param_name'  => 'title',
					'value'       => '',
					'save_always' => true,
					'admin_label' => true,
					'description' => esc_html__('Enter title text', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Size', 'servicemaster'),
					'param_name'  => 'title_size',
					'value'       => array(
						esc_html__('Large', 'servicemaster')  => 'large',
						esc_html__('Medium', 'servicemaster') => 'medium',
						esc_html__('Small', 'servicemaster')  => 'small',
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => esc_html__('Choose one of predefined title sizes', 'servicemaster')
				),
				array(
					'type'        => 'colorpicker',
					'heading'     => esc_html__('Color', 'servicemaster'),
					'param_name'  => 'title_color',
					'value'       => '',
					'save_always' => true,
					'admin_label' => true,
					'description' => esc_html__('Choose color of your title', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Text Align', 'servicemaster'),
					'param_name'  => 'title_text_align',
					'value'       => array(
						''                                  => '',
						esc_html__('Center', 'servicemaster') => 'center',
						esc_html__('Left', 'servicemaster')   => 'left',
						esc_html__('Right', 'servicemaster')  => 'right'
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => esc_html__('Choose text align for title', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Margin Bottom', 'servicemaster'),
					'param_name'  => 'margin_bottom',
					'value'       => '',
					'save_always' => true,
					'admin_label' => true,
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Width (%)', 'servicemaster'),
					'param_name'  => 'width',
					'description' => esc_html__('Adjust the width of section title in percentages. Ommit the unit', 'servicemaster'),
					'value'       => '',
					'save_always' => true,
					'admin_label' => true
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'title'            => '',
			'title_size'       => 'large',
			'title_color'      => '',
			'title_text_align' => '',
			'margin_bottom'    => '',
			'width'            => ''
		);

		$params = shortcode_atts($default_atts, $atts);

		if ($params['title'] !== '') {
			$params['section_title_classes'] = array('mkd-section-title');

			if ($params['title_size'] !== '') {
				$params['section_title_classes'][] = 'mkd-section-title-' . $params['title_size'];
			}

			$params['section_title_styles'] = array();

			if ($params['title_color'] !== '') {
				$params['section_title_styles'][] = 'color: ' . $params['title_color'];
			}

			if ($params['title_text_align'] !== '') {
				$params['section_title_styles'][] = 'text-align: ' . $params['title_text_align'];

				$params['section_title_classes'][] = 'mkd-section-title-' . $params['title_text_align'];
			}

			if ($params['width'] !== '') {
				$params['section_title_styles'][] = 'width: ' . $params['width'] . '%';
			}


			if ($params['margin_bottom'] !== '') {
				$params['section_title_styles'][] = 'margin-bottom: ' . servicemaster_mikado_filter_px($params['margin_bottom']) . 'px';
			}

			$params['title_tag'] = $this->getTitleTag($params);

			return servicemaster_mikado_get_shortcode_module_template_part('templates/section-title-template', 'section-title', '', $params);
		}
	}

	private function getTitleTag($params) {
		switch ($params['title_size']) {
			case 'large':
				$titleTag = 'h1';
				break;
			case 'medium':
				$titleTag = 'h2';
				break;
			case 'small':
				$titleTag = 'h3';
				break;
			default:
				$titleTag = 'h1';
		}

		return $titleTag;
	}
}