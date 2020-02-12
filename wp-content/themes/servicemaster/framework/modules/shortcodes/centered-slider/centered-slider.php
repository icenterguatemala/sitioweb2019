<?php
namespace ServiceMaster\Modules\Shortcodes\CenteredSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class CenteredSlider implements ShortcodeInterface {

	private $base;

	/**
	 * Image Gallery constructor.
	 */
	public function __construct() {
		$this->base = 'mkd_centered_slider';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * Maps shortcode to Visual Composer. Hooked on vc_before_init
	 *
	 * @see mkd_core_get_carousel_slider_array_vc()
	 */
	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Centered Slider', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-centered-slider extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'attach_images',
					'holder'      => 'div',
					'heading'     => esc_html__('Images', 'servicemaster'),
					'param_name'  => 'images',
					'description' => esc_html__('Select images from media library', 'servicemaster')
				)
			)
		));

	}

	/**
	 * Renders shortcodes HTML
	 *
	 * @param $atts array of shortcode params
	 * @param $content string shortcode content
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {

		$args = array(
			'images' => ''
		);

		$params = shortcode_atts($args, $atts);

		$params['images'] = $this->getSliderImages($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/centered-slider-template', 'centered-slider', '', $params);

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
}