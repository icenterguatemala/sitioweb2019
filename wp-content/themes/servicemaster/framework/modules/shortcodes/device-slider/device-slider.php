<?php

namespace ServiceMaster\Modules\Shortcodes\DeviceSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class DeviceSlider
 */
class DeviceSlider implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_device_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 *
	 */
	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Device Slider', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-device-slider extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'attach_images',
					'heading'     => esc_html__('Images', 'servicemaster'),
					'param_name'  => 'images',
					'description' => esc_html__('Select images from media library', 'servicemaster')
				)

			)
		));
	}

	/**
	 * @param array $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {
		$args = array(
			'images'                => ''
		);

		$params = shortcode_atts($args, $atts);
		$params['images'] = $this->getSliderImages($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/device-slider-template', 'device-slider', '', $params);
	}

	/**
	 * Return images for slider
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getSliderImages($params) {
		$image_ids = array();
		$images    = array();
		$i         = 0;

		if($params['images'] !== '') {
			$image_ids = explode(',', $params['images']);
		}

		foreach($image_ids as $id) {

			$image['image_id'] = $id;
			$image_original    = wp_get_attachment_image_src($id, 'full');
			$image['url']      = $image_original[0];
			$image['title']    = get_the_title($id);

			$images[$i] = $image;
			$i++;
		}

		return $images;

	}
}