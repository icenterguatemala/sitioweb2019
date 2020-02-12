<?php

namespace ServiceMaster\Modules\Shortcodes\MobileSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class MobileSlider implements ShortcodeInterface {

	private $base;

	function __construct() {
		$this->base = 'mkd_mobile_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Mobile Slider', 'servicemaster'),
			'base'                      => $this->base,
			'icon'                      => 'icon-wpb-mobile-slider extended-custom-icon',
			'category'                  => 'by MIKADO',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'attach_images',
					'heading'     => esc_html__('Images','servicemaster'),
					'param_name'  => 'images',
					'description' => esc_html__('Select images from media library','servicemaster')
				)
			)
		));

	}

	public function render($atts, $content = null) {

		$args = array(
			'images' => ''
		);

		$html = '';

		$params = shortcode_atts($args, $atts);
		extract($params);

		$params['images'] = $this->getGalleryImages($params);

		$html .= servicemaster_mikado_get_shortcode_module_template_part('templates/mobile-slider-template', 'mobile-slider', '', $params);
		return $html;

	}

	/**
	 * Return images for gallery
	 *
	 * @param $params
	 * @return array
	 */
	private function getGalleryImages($params) {
		$image_ids = array();
		$images = array();
		$i = 0;

		if ($params['images'] !== '') {
			$image_ids = explode(',', $params['images']);
		}

		foreach ($image_ids as $id) {

			$image['image_id'] = $id;
			$image['title'] = get_the_title($id);

			$images[$i] = $image;
			$i++;
		}

		return $images;

	}
}
