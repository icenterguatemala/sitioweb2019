<?php
namespace ServiceMaster\Modules\Shortcodes\TableItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class TableItem implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'mkd_table_item';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		if (function_exists('vc_map')) {
			vc_map(
				array(
					'name'                    => esc_html__('Table Item', 'servicemaster'),
					'base'                    => $this->base,
					'as_parent'               => array('only' => 'mkd_table_content_item'),
					'as_child'                => array('only' => 'mkd_table_holder'),
					'content_element'         => true,
					'category'                => esc_html__('by MIKADO', 'servicemaster'),
					'icon'                    => 'icon-wpb-table-item extended-custom-icon',
					'show_settings_on_create' => true,
					'js_view'                 => 'VcColumnView',
					'params'                  => array(
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__('Title', 'servicemaster'),
							'admin_label' => true,
							'save_always' => true,
							'param_name'  => 'table_item_title'
						),
						array(
							'type'        => 'colorpicker',
							'heading'     => esc_html__('Color', 'servicemaster'),
							'admin_label' => true,
							'save_always' => true,
							'param_name'  => 'table_item_title_color'
						),
						array(
							'type'        => 'colorpicker',
							'heading'     => esc_html__('Background color', 'servicemaster'),
							'admin_label' => true,
							'save_always' => true,
							'param_name'  => 'table_item_title_background_color'
						),

					)
				)
			);
		}
	}

	public function render($atts, $content = null) {
		$args = array(
			'table_item_title'                  => '',
			'table_item_title_color'            => '',
			'table_item_title_background_color' => ''
		);

		$params = shortcode_atts($args, $atts);
		extract($params);

		//additional params
		$params['title_styles'] = $this->getStyles($params)['title'];

		$params['content'] = $content;

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/table-item', 'table', '', $params);

		return $html;
	}

	public function getStyles($params) {
		$styles['title'] = array();

		if (!empty($params['table_item_title_color'])) {
			$styles['title'][] = 'color: ' . $params['table_item_title_color'];
		}

		if (!empty($params['table_item_title_background_color'])) {
			$styles['title'][] = 'background-color: ' . $params['table_item_title_background_color'];
		}

		return $styles;
	}
}
