<?php
namespace ServiceMaster\Modules\Shortcodes\PlaylistItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Playlist Item
 */
class PlaylistItem implements ShortcodeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'mkd_playlist_item';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		vc_map(array(
			'name'                      => esc_html__('Playlist Item', 'servicemaster'),
			'base'                      => $this->base,
			'category'                  => 'by MIKADO',
			'as_child'                  => array('only' => 'mkd_playlist'),
			'icon'                      => 'icon-wpb-playlist-item extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__('Audio file', 'servicemaster'),
					'param_name' => 'audio_file',
				),
				array(
					'type'       => 'textfield',
					'heading'    => esc_html__('Title', 'servicemaster'),
					'param_name' => 'title'
				)
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
		$default_atts = array(
			'audio_file' => '',
			'title'      => ''
		);

		$params = shortcode_atts($default_atts, $atts);

		$params['random_id'] = mt_rand(100000, 1000000);

		return servicemaster_mikado_get_shortcode_module_template_part('templates/playlist-item-template', 'playlist', '', $params);
	}


}