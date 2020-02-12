<?php
namespace ServiceMaster\Modules\Team;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Team
 */
class Team implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_team';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * Maps shortcode to Visual Composer. Hooked on vc_before_init
	 *
	 * @see mkd_core_get_carousel_slider_array_vc()
	 */
	public function vcMap() {

		$team_social_icons_array = array();
		for ($x = 1; $x < 6; $x++) {
			$teamIconCollections = servicemaster_mikado_icon_collections()->getCollectionsWithSocialIcons();
			foreach ($teamIconCollections as $collection_key => $collection) {

				$team_social_icons_array[] = array(
					'type'       => 'dropdown',
					'heading'    => esc_html__('Social Icon ', 'servicemaster') . $x,
					'param_name' => 'team_social_' . $collection->param . '_' . $x,
					'value'      => $collection->getSocialIconsArrayVC(),
					'dependency' => array(
						'element' => 'team_social_icon_pack',
						'value'   => array($collection_key)
					)
				);

			}

			$team_social_icons_array[] = array(
				'type'       => 'textfield',
				'heading'    => esc_html__('Social Icon ', 'servicemaster') . $x . esc_html__(' Link', 'servicemaster'),
				'param_name' => 'team_social_icon_' . $x . '_link',
				'dependency' => array(
					'element' => 'team_social_icon_pack',
					'value'   => servicemaster_mikado_icon_collections()->getIconCollectionsKeys()
				)
			);

			$team_social_icons_array[] = array(
				'type'       => 'dropdown',
				'heading'    => esc_html__('Social Icon ', 'servicemaster') . $x . esc_html__(' Target', 'servicemaster'),
				'param_name' => 'team_social_icon_' . $x . '_target',
				'value'      => array(
					''                                   => '',
					esc_html__('Self', 'servicemaster')  => '_self',
					esc_html__('Blank', 'servicemaster') => '_blank'
				),
				'dependency' => array(
					'element'   => 'team_social_icon_' . $x . '_link',
					'not_empty' => true
				)
			);

		}

		vc_map(array(
			'name'                      => esc_html__('Team', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'icon'                      => 'icon-wpb-team extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array_merge(
				array(
					array(
						'type'       => 'attach_image',
						'heading'    => esc_html__('Image', 'servicemaster'),
						'param_name' => 'team_image'
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Name', 'servicemaster'),
						'admin_label' => true,
						'param_name'  => 'team_name'
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Position', 'servicemaster'),
						'admin_label' => true,
						'param_name'  => 'team_position'
					),
					array(
						'type'        => 'textarea',
						'heading'     => esc_html__('Description', 'servicemaster'),
						'admin_label' => true,
						'param_name'  => 'team_description'
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__('Link', 'servicemaster'),
						'param_name'  => 'link',
						'value'       => '',
						'admin_label' => true
					),
					array(
						'type'       => 'textfield',
						'heading'    => esc_html__('Link Text', 'servicemaster'),
						'param_name' => 'link_text',
						'dependency' => array(
							'element'   => 'link',
							'not_empty' => true
						)
					),
					array(
						'type'       => 'dropdown',
						'heading'    => esc_html__('Target', 'servicemaster'),
						'param_name' => 'link_target',
						'value'      => array(
							''                                   => '',
							esc_html__('Self', 'servicemaster')  => '_self',
							esc_html__('Blank', 'servicemaster') => '_blank'
						),
						'dependency' => array(
							'element'   => 'link',
							'not_empty' => true
						),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__('Social Icon Pack', 'servicemaster'),
						'param_name'  => 'team_social_icon_pack',
						'admin_label' => true,
						'value'       => array_merge(array('' => ''), servicemaster_mikado_icon_collections()->getIconCollectionsVCExclude('linea_icons')),
						'save_always' => true
					)
				),
				$team_social_icons_array
			)
		));

	}

	/**
	 * Renders shortcodes HTML
	 *
	 * @param $atts array of shortcode params
	 * @param $content string shortcode content
	 *
	 * @return string
	 */
	public function render($atts, $content = null) {

		$args = array(
			'team_image'            => '',
			'team_name'             => '',
			'team_position'         => '',
			'team_description'      => '',
			'link'                  => '',
			'link_text'             => '',
			'link_target'           => '_self',
			'phone_number'          => '',
			'team_social_icon_pack' => '',
		);

		$team_social_icons_form_fields = array();
		$number_of_social_icons = 5;

		for ($x = 1; $x <= $number_of_social_icons; $x++) {

			foreach (servicemaster_mikado_icon_collections()->iconCollections as $collection_key => $collection) {
				$team_social_icons_form_fields['team_social_' . $collection->param . '_' . $x] = '';
			}

			$team_social_icons_form_fields['team_social_icon_' . $x . '_link'] = '';
			$team_social_icons_form_fields['team_social_icon_' . $x . '_target'] = '';

		}

		$args = array_merge($args, $team_social_icons_form_fields);

		$params = shortcode_atts($args, $atts);

		$params['number_of_social_icons'] = 5;
		$params['team_image_src'] = $this->getTeamImage($params);
		$params['team_social_icons'] = $this->getTeamSocialIcons($params);

		$params['button_parameters'] = $this->getButtonParameters($params);

		//Get HTML from template based on type of team
		$html = servicemaster_mikado_get_shortcode_module_template_part('templates/team-template-simple', 'team', '', $params);

		return $html;

	}

	/**
	 * Return Team image
	 *
	 * @param $params
	 *
	 * @return false|string
	 */
	private function getTeamImage($params) {

		if (is_numeric($params['team_image'])) {
			$team_image_src = wp_get_attachment_url($params['team_image']);
		} else {
			$team_image_src = $params['team_image'];
		}

		return $team_image_src;

	}

	private function getTeamSocialIcons($params) {

		extract($params);
		$social_icons = array();

		if ($team_social_icon_pack !== '') {

			$icon_pack = servicemaster_mikado_icon_collections()->getIconCollection($team_social_icon_pack);
			$team_social_icon_type_label = 'team_social_' . $icon_pack->param;
			$team_social_icon_param_label = $icon_pack->param;

			for ($i = 1; $i <= $number_of_social_icons; $i++) {

				$team_social_icon = ${$team_social_icon_type_label . '_' . $i};
				$team_social_link = ${'team_social_icon_' . $i . '_link'};
				$team_social_target = ${'team_social_icon_' . $i . '_target'};

				if ($team_social_icon !== '') {

					$team_icon_params = array();
					$team_icon_params['icon_pack'] = $team_social_icon_pack;
					$team_icon_params[$team_social_icon_param_label] = $team_social_icon;
					$team_icon_params['link'] = ($team_social_link !== '') ? $team_social_link : '';
					$team_icon_params['target'] = ($team_social_target !== '') ? $team_social_target : '';
//					$team_icon_params['type']                        = '';

					$social_icons[] = servicemaster_mikado_execute_shortcode('mkd_icon', $team_icon_params);
				}

			}

		}

		return $social_icons;
	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		$button_params_array['type'] = 'underline';
		$button_params_array['custom_class'] = 'mkd-counter-link';

		if (!empty($params['link_text'])) {
			$button_params_array['text'] = $params['link_text'];
		}

		if (!empty($params['link'])) {
			$button_params_array['link'] = $params['link'];
		}

		if (!empty($params['target'])) {
			$button_params_array['target'] = $params['target'];
		}

		return $button_params_array;
	}

}