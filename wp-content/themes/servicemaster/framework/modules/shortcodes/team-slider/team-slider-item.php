<?php
namespace ServiceMaster\Modules\Shortcodes\TeamSliderItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Team Slider Item
 */
class TeamSliderItem implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_team_slider_item';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Team Slider Item', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'as_child'                  => array('only' => 'mkd_team_slider'),
			'icon'                      => 'icon-wpb-team-slider-item extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'attach_image',
					'heading'     => esc_html__('Logo image', 'servicemaster'),
					'param_name'  => 'logo_image',
					'description' => esc_html__('This image is shown if boxed slider type is selected', 'servicemaster'),
				),
				array(
					'type'       => 'attach_image',
					'heading'    => esc_html__('Team member image', 'servicemaster'),
					'param_name' => 'team_member_image'
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__('Name', 'servicemaster'),
					'param_name' => 'name'
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__('Position', 'servicemaster'),
					'param_name' => 'position'
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__('Text', 'servicemaster'),
					'param_name' => 'text'
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
		$default_atts = array(
			'slider_type'       => $atts['slider_type'],
			'logo_image'        => '',
			'team_member_image' => '',
			'name'              => '',
			'text'              => '',
			'position'          => ''
		);

		$params = shortcode_atts($default_atts, $atts);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/' . $params['slider_type'] . '-team-slide', 'team-slider', '', $params);
	}


}