<?php
namespace ServiceMaster\Modules\Shortcodes\TableHolder;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class TableHolder implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_table_holder';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                    => esc_html__('Table Holder', 'servicemaster'),
			'base'                    => $this->base,
			'as_parent'               => array('only' => 'mkd_table_item'),
			'content_element'         => true,
			'category'                => 'by MIKADO',
			'icon'                    => 'icon-wpb-table-holder extended-custom-icon',
			'show_settings_on_create' => true,
			'params'                  => array(
				array(
					'type'        => 'dropdown',
					'holder'      => 'div',
					'class'       => '',
					'heading'     => esc_html__('Columns', 'servicemaster'),
					'param_name'  => 'columns',
					'value'       => array(
						esc_html__('Two', 'servicemaster')   => 'mkd-two-columns',
						esc_html__('Three', 'servicemaster') => 'mkd-three-columns',
						esc_html__('Four', 'servicemaster')  => 'mkd-four-columns',
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'admin_label' => true,
					'heading'     => esc_html__('Animate Table Contents', 'servicemaster'),
					'param_name'  => 'animate',
					'value'       => array(
						esc_html__('No', 'servicemaster')  => 'no',
						esc_html__('Yes', 'servicemaster') => 'yes'
					),
					'description' => ''
				),
			),
			'js_view'                 => 'VcColumnView'
		));

	}

	public function render($atts, $content = null) {
		$args = array(
			'columns' => 'mkd-two-columns',
			'animate' => '',
		);

		$params = shortcode_atts($args, $atts);
		extract($params);

		$table_classes = "";

		$table_classes .= $columns;

		if ($animate == 'yes') {
			$table_classes .= ' mkd-animate-table';
		}

		$html = '<div class="mkd-table-shortcode-holder clearfix ' . $table_classes . '">';
		$html .= do_shortcode($content);
		$html .= '</div>';

		return $html;
	}

}
