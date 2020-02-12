<?php

namespace ServiceMaster\Modules\Shortcodes\HorizontalTimeline;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class HorizontalTimelineItem implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_horizontal_timeline_item';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Horizontal Timeline Item', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-horizontal-timeline-item extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'as_child'                  => array('only' => 'mkd_horizontal_timeline'),
			'params'                    => array(
				array(
					'type'        => 'attach_image',
					'heading'     => esc_html__('Image', 'servicemaster'),
					'param_name'  => 'image',
					'value'       => '',
					'admin_label' => true
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Date', 'servicemaster'),
					'param_name'  => 'date',
					'value'       => '',
					'admin_label' => true,
					'description' => esc_html__('Enter date in dd-mm-yyyy format', 'servicemaster')
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Title', 'servicemaster'),
					'param_name'  => 'title',
					'value'       => '',
					'admin_label' => true
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Subtitle', 'servicemaster'),
					'param_name'  => 'subtitle',
					'value'       => '',
					'admin_label' => true
				),
				array(
					'type'       => 'textarea_html',
					'heading'    => esc_html__('Description', 'servicemaster'),
					'param_name' => 'content',
					'value'      => ''
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'title'    => '',
			'subtitle' => '',
			'image'    => '',
			'date'     => ''
		);

		$params = shortcode_atts($default_atts, $atts);
		$params['content'] = $content;

		$date = new \DateTime($params['date']);

		$params['date'] = $date->format('d/m/Y');

		return servicemaster_mikado_get_shortcode_module_template_part('templates/horizontal-timeline-item', 'horizontal-timeline', '', $params);
	}
}