<?php

namespace ServiceMaster\Modules\Shortcodes\BackgroundSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class BackgroundSlider
 */
class BackgroundSlider implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	/**
	 * Sets base attribute and registers shortcode with Visual Composer
	 */
	public function __construct() {
		$this->base = 'mkd_background_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base attribute
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/*
	 * Maps shortcode to Visual Composer. Hooked on vc_before_init
	 */
	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Background Slider', 'servicemaster'),
			'base'                      => $this->getBase(),
			'as_child'                  => array('only' => 'mkd_elements_holder_item'),
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-background-slider extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'attach_images',
					'heading'     => esc_html__('Images', 'servicemaster'),
					'param_name'  => 'images',
					'description' => esc_html__('Select images from media library', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Image Size', 'servicemaster'),
					'param_name'  => 'image_size',
					'description' => esc_html__('Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size', 'servicemaster')
				),
				array(
					'type'        => 'dropdown',
					'class'       => '',
					'heading'     => esc_html__('Navigation skin', 'servicemaster'),
					'param_name'  => 'navigation_skin',
					'value'       => array(
						esc_html__('Default', 'servicemaster') => '',
						esc_html__('Light', 'servicemaster')   => 'light',
						esc_html__('Dark', 'servicemaster')    => 'dark',
					),
					'save_always' => true
				),
			)
		));

	}

	/**
	 * Renders shortcodes HTML
	 *
	 * @param $atts array of shortcode params
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {

		$args = array(
			'images'          => '',
			'image_size'      => 'thumbnail',
			'navigation_skin' => ''
		);

		$params = shortcode_atts($args, $atts);

		$params['images'] = $this->getSliderImages($params);
		$params['classes'] = $this->getClasses($params);

		//Get HTML from template
		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/background-slider-template', 'background-slider', '', $params);

		return $html;
	}

	/**
	 * Return images for gallery
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getSliderImages($params) {
		$image_ids = array();
		$images = array();
		$i = 0;

		if ($params['images'] !== '') {
			$image_ids = explode(',', $params['images']);
		}

		foreach ($image_ids as $id) {

			$image['image_id'] = $id;
			$image_original = wp_get_attachment_image_src($id, 'full');
			$image['url'] = $image_original[0];
			$image['title'] = get_the_title($id);

			$images[$i] = $image;
			$i++;
		}

		return $images;

	}

	/**
	 * Return classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getClasses($params) {
		$classes = array('mkd-bckg-slider');

		$classes[] = $params['navigation_skin'];

		return $classes;
	}
}