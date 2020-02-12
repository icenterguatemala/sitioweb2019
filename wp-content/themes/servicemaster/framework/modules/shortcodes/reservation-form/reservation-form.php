<?php
namespace ServiceMaster\Modules\Shortcodes\ReservationForm;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class ReservationForm implements ShortcodeInterface {
	private $base;

	public function __construct() {
		$this->base = 'mkd_reservation_form';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => 'Reservation Form',
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-reservation-form extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__('OpenTable ID', 'servicemaster'),
					'param_name'  => 'open_table_id',
					'admin_label' => true
				),
				array(
					'type'        => 'dropdown',
					'heading'     => esc_html__('Skin', 'servicemaster'),
					'param_name'  => 'skin',
					'value'       => array(
						esc_html__('Dark', 'servicemaster')  => 'dark',
						esc_html__('Light', 'servicemaster') => 'light'
					),
					'admin_label' => true
				),
			)
		));
	}

	public function render($atts, $content = null) {
		$default_atts = array(
			'open_table_id' => '',
			'skin'          => ''
		);

		$params = shortcode_atts($default_atts, $atts);
		if ($params['skin'] !== '') {
			$params['holder_classes'] = 'mkd-skin-' . $params['skin'];
		}

		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/reservation-form', 'reservation-form', '', $params);

		return $html;
	}

}