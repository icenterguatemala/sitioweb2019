<?php
namespace ServiceMaster\Modules\RestaurantMenu;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class RestaurantMenu implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_restaurant_menu';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'      => esc_html__('Restaurant Menu', 'servicemaster'),
			'base'      => $this->base,
			'icon'      => 'icon-wpb-restaurant-menu extended-custom-icon',
			'category'  => 'by MIKADO',
			'as_parent' => array('only' => 'mkd_restaurant_item'),
			'js_view'   => 'VcColumnView',
			'params'    => array(
				array(
					'type'       => 'dropdown',
					'heading'    => esc_html__('Skin', 'servicemaster'),
					'param_name' => 'skin',
					'value'      => array(
						esc_html__('Dark','servicemaster')  => 'dark',
						esc_html__('Light','servicemaster')  => 'light',
					)
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$args = array(
			'skin' => '',
		);

		$params = shortcode_atts($args, $atts);
		$html = '';

		$html .= '<div ' . servicemaster_mikado_get_class_attribute($this->getMenuHolderClasses($params)) . '>';
		$html .= '<div class="mkd-restaurant-menu-holder">';
		$html .= do_shortcode($content);
		$html .= '</div>';
		$html .= '</div>';

		return $html;

	}

	private function getMenuHolderClasses($params) {
		$classes = array('mkd-restaurant-menu');

		switch ($params['skin']) {
			case 'light':
				$classes [] = 'mkd-rstrnt-skin-light';
				break;
			default:
				break;
		}

		return $classes;
	}

}
