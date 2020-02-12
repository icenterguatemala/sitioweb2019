<?php
namespace ServiceMaster\Modules\PricingTablesWithIcon;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class PricingTablesWithIcon implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_pricing_tables_with_icon';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                    => esc_html__('Pricing Tables With Icon', 'servicemaster'),
			'base'                    => $this->base,
			'as_parent'               => array('only' => 'mkd_pricing_table_with_icon'),
			'content_element'         => true,
			'category'                => 'by MIKADO',
			'icon'                    => 'icon-wpb-pricing-tables-wi extended-custom-icon',
			'show_settings_on_create' => false,
			'js_view'                 => 'VcColumnView'
		));
	}

	public function render($atts, $content = null) {

		$html = '<div class="mkd-pricing-tables-wi clearfix">';
		$html .= do_shortcode($content);
		$html .= '</div>';

		return $html;
	}
}