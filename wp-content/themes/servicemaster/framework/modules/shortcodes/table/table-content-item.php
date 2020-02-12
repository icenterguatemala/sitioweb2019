<?php
namespace ServiceMaster\Modules\Shortcodes\TableContentItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class TableContentItem implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_table_content_item';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'      => esc_html__('Table Content Item', 'servicemaster'),
			'base'      => $this->base,
			'icon'      => 'icon-wpb-table-item-content extended-custom-icon',
			'category'  => 'by MIKADO',
			'as_parent' => array('except' => 'vc_row'),
			'as_child'  => array('only' => 'mkd_table_item'),
			'content_element'         => true,
			'js_view'   => 'VcColumnView',
			'params'    => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Title', 'servicemaster'),
					'admin_label' => true,
					'param_name'  => 'table_content_item_title'
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Link', 'servicemaster'),
					'admin_label' => true,
					'param_name'  => 'table_content_item_link'
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Description', 'servicemaster'),
					'admin_label' => true,
					'param_name'  => 'table_content_item_desc'
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Mark as trending?', 'servicemaster'),
					'admin_label' => true,
					'save_always' => true,
					'value'       => array(
						esc_html__('No','servicemaster')  => 'no',
						esc_html__('Yes','servicemaster') =>  'yes'
					),
					'param_name'  => 'table_content_item_trending'
				)
			)
		));
	}

	public function render($atts, $content = null) {

		$args   = array(
			'table_content_item_title' => '',
			'table_content_item_link'   => '',
			'table_content_item_desc' => '',
			'table_content_item_trending' => ''
		);
		$params = shortcode_atts($args, $atts);
		extract($params);

		$params['item_classes'] = $this->getItemClasses($params);

		$params['content']       = $content;

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/table-content-item', 'table', '', $params);

		return $html;

	}

	private function getItemClasses($params){
		$classes = array('mkd-table-content-item-holder');

		if(isset($params['table_content_item_trending']) && $params['table_content_item_trending'] == 'yes'){
			$classes[] = 'mkd-table-content-trending';
		}

		return $classes;
	}

}
