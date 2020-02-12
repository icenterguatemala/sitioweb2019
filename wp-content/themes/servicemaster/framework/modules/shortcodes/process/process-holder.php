<?php
namespace ServiceMaster\Modules\Shortcodes\Process;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class ProcessHolder implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_process_holder';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                    => esc_html__('Process', 'servicemaster'),
			'base'                    => $this->getBase(),
			'as_parent'               => array('only' => 'mkd_process_item'),
			'content_element'         => true,
			'show_settings_on_create' => true,
			'category'                => 'by MIKADO',
			'icon'                    => 'icon-wpb-call-to-action extended-custom-icon',
			'js_view'                 => 'VcColumnView',
			'params'                  => array(
				array(
					'type'        => 'dropdown',
					'param_name'  => 'process_type',
					'heading'     => esc_html__('Process type', 'servicemaster'),
					'value'       => array(
						esc_html__('Horizontal', 'servicemaster') => 'horizontal_process',
						esc_html__('Vertical', 'servicemaster')   => 'vertical_process'
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'param_name'  => 'process_skin',
					'heading'     => esc_html__('Skin', 'servicemaster'),
					'value'       => array(
						esc_html__('Dark', 'servicemaster')  => 'dark',
						esc_html__('Light', 'servicemaster') => 'light'
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => ''
				),
				array(
					'type'        => 'dropdown',
					'param_name'  => 'number_of_items',
					'heading'     => esc_html__('Number of Process Items', 'servicemaster'),
					'value'       => array(
						esc_html__('Three', 'servicemaster') => 'three',
						esc_html__('Four', 'servicemaster')  => 'four'
					),
					'dependency'  => array(
						'element' => 'process_type',
						'value'   => 'horizontal_process'
					),
					'save_always' => true,
					'admin_label' => true,
					'description' => ''
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'process_type'    => 'horizontal_process',
			'process_skin'    => '',
			'number_of_items' => ''
		);

		$params = shortcode_atts($default_atts, $atts);
		$params['content'] = $content;
		$params['holder_classes'] = $this->getClasses($params);

		if ($params['process_type'] == 'horizontal_process') {
			return servicemaster_mikado_get_shortcode_module_template_part('templates/horizontal-process-holder-template', 'process', '', $params);
		} else {
			return servicemaster_mikado_get_shortcode_module_template_part('templates/vertical-process-holder-template', 'process', '', $params);
		}
	}

	public function getClasses($params) {
		$holder_classes = array('mkd-process-holder');
		$holder_classes[] = 'mkd-process-' . $params['process_skin'];

		if ($params['process_type'] == 'horizontal_process') {
			$holder_classes[] = 'mkd-process-horizontal';
			$holder_classes[] = 'mkd-process-holder-items-' . $params['number_of_items'];
		} else {
			$holder_classes[] = 'mkd-process-vertical';
		}

		return $holder_classes;
	}
}