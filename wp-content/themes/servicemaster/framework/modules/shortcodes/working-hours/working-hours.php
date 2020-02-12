<?php
namespace ServiceMaster\Modules\Shortcodes\WorkingHours;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class WorkingHours implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_working_hours';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Working Hours', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-working-hours extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Title', 'servicemaster'),
					'param_name'  => 'title',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Text', 'servicemaster'),
					'param_name'  => 'text',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Working Hours Style', 'servicemaster'),
					'param_name'  => 'style',
					'admin_label' => true,
					'value'       => array(
						esc_html__('Dark', 'servicemaster')  => 'dark',
						esc_html__('Light', 'servicemaster') => 'light'
					),
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Use Shortened Version?', 'servicemaster'),
					'param_name'  => 'use_shortened_version',
					'admin_label' => true,
					'value'       => array(
						esc_html__('Yes', 'servicemaster') => 'yes',
						esc_html__('No', 'servicemaster')  => 'no'
					),
					'save_always' => true
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Monday To Friday', 'servicemaster'),
					'param_name'  => 'monday_to_friday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'yes'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Weekend', 'servicemaster'),
					'param_name'  => 'weekend',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'yes'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Monday', 'servicemaster'),
					'param_name'  => 'monday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Tuesday', 'servicemaster'),
					'param_name'  => 'tuesday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Wednesday', 'servicemaster'),
					'param_name'  => 'wednesday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Thursday', 'servicemaster'),
					'param_name'  => 'thursday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Friday', 'servicemaster'),
					'param_name'  => 'friday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Saturday', 'servicemaster'),
					'param_name'  => 'saturday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('Sunday', 'servicemaster'),
					'param_name'  => 'sunday',
					'admin_label' => true,
					'value'       => '',
					'save_always' => true,
					'group'       => esc_html__('Settings', 'servicemaster'),
					'dependency'  => array(
						'element' => 'use_shortened_version',
						'value'   => 'no'
					)
				)
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'title'                 => '',
			'text'                  => '',
			'style'                 => '',
			'use_shortened_version' => '',
			'monday_to_friday'      => '',
			'weekend'               => '',
			'monday'                => '',
			'tuesday'               => '',
			'wednesday'             => '',
			'thursday'              => '',
			'friday'                => '',
			'saturday'              => '',
			'sunday'                => ''
		);

		$params = shortcode_atts($default_atts, $atts);

		$params['working_hours'] = $this->getWorkingHours($params);
		$params['holder_classes'] = $this->getHolderClasses($params);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/working-hours-template', 'working-hours', '', $params);
	}

	private function getWorkingHours($params) {
		$workingHours = array();

		if (!empty($params['use_shortened_version']) && $params['use_shortened_version'] === 'yes') {
			if (!empty($params['monday_to_friday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Monday - Friday', 'servicemaster'),
					'time'  => $params['monday_to_friday']
				);
			}

			if (!empty($params['weekend'])) {
				$workingHours[] = array(
					'label' => esc_html__('Saturday - Sunday', 'servicemaster'),
					'time'  => $params['weekend']
				);
			}
		} else {
			if (!empty($params['monday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Monday', 'servicemaster'),
					'time'  => $params['monday']
				);
			}

			if (!empty($params['tuesday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Tuesday', 'servicemaster'),
					'time'  => $params['tuesday']
				);
			}

			if (!empty($params['wednesday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Wednesday', 'servicemaster'),
					'time'  => $params['wednesday']
				);
			}

			if (!empty($params['thursday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Thursday', 'servicemaster'),
					'time'  => $params['thursday']
				);
			}

			if (!empty($params['friday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Friday', 'servicemaster'),
					'time'  => $params['friday']
				);
			}

			if (!empty($params['saturday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Saturday', 'servicemaster'),
					'time'  => $params['saturday']
				);
			}

			if (!empty($params['sunday'])) {
				$workingHours[] = array(
					'label' => esc_html__('Sunday', 'servicemaster'),
					'time'  => $params['sunday']
				);
			}
		}

		return $workingHours;
	}

	private function getHolderClasses($params) {
		$classes = array(
			'mkd-working-hours-holder',
			'mkd-working-hours-' . $params['style']
		);

		return $classes;
	}

}
