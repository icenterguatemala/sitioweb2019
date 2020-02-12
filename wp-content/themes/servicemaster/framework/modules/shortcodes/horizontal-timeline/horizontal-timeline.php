<?php
namespace ServiceMaster\Modules\Shortcodes\HorizontalTimeline;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class HorizontalTimeline implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_horizontal_timeline';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Horizontal Timeline', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-horizontal-timeline extended-custom-icon',

			'as_parent'                 => array('only' => 'mkd_horizontal_timeline_item'),
			'js_view'                   => 'VcColumnView',
			'params'                    => array(
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Timeline displays?', 'servicemaster'),
					'param_name'  => 'timeline_format',
					'value'       => array(
						esc_html__('Only Years', 'servicemaster')             => 'Y',
						esc_html__('Years and Months', 'servicemaster')       => 'M Y',
						esc_html__('Years, Months and Days', 'servicemaster') => 'M d, \'y',
						esc_html__('Months and Days', 'servicemaster')        => 'M d'
					),
					'admin_label' => true
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Minimal Distance Between Dates?', 'servicemaster'),
					'param_name'  => 'distance',
					'value'       => '',
					'description' => esc_html__('Default value is 60', 'servicemaster'),
					'admin_label' => true
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'timeline_format' => 'Y',
			'distance'        => '60'
		);

		$params            = shortcode_atts($default_atts, $atts);
		$params['content'] = $content;

		$params['dates'] = $this->getDates($content);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/horizontal-timeline-template', 'horizontal-timeline', '', $params);
	}

	private function getDates($content) {
		$datesArray = '';

		preg_match_all('/date="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE);

		if(isset($matches[0])) {
			$dates = $matches[0];

			if(is_array($dates) && count($dates)) {
				foreach($dates as $date) {
					preg_match('/date="([^\"]+)"/i', $date[0], $dateMatches, PREG_OFFSET_CAPTURE);
					$date = new \DateTime($dateMatches[1][0]);

					$currentDate = array(
						'formatted' => $date->format('d/m/Y'),
						'timestamp' => $date->getTimestamp()
					);

					$datesArray[] = $currentDate;
				}
			}
		}

		return $datesArray;
	}
}