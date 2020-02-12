<?php
namespace ServiceMaster\Modules\Shortcodes\Playlist;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class Playlist implements ShortcodeInterface{
    private $base;
    function __construct() {
        $this->base = 'mkd_playlist';
        add_action('vc_before_init', array($this, 'vcMap'));
    }
    public function getBase() {
        return $this->base;
    }

    public function vcMap() {
        vc_map( array(
            'name' => esc_html__('Playlist', 'servicemaster'),
            'base' => $this->base,
            'icon' => 'icon-wpb-playlist extended-custom-icon',
            'category' => 'by MIKADO',
            'as_parent' => array('only' => 'mkd_playlist_item'),
            'js_view' => 'VcColumnView',
            'params' => array(
                array(
                    'type'        => 'colorpicker',
                    'admin_label' => true,
                    'heading'     => esc_html__('Background Color', 'servicemaster'),
                    'param_name'  => 'background_color',
                    'description' => ''
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Playlist Title','servicemaster'),
                    'param_name' => 'title',
                    'description' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Playlist Subtitle', 'servicemaster'),
                    'param_name' => 'subtitle',
                    'description' => '',
                    'admin_label' => true
                ),

            )
        ));
    }

    public function render($atts, $content = null) {
        $args = array(
            'background_color' => '',
            'title' =>'',
            'subtitle'=>''
        );

        $params = shortcode_atts($args, $atts);
        $params['content'] = $content;
        $params['styles'] = $this->getStyleAttributes($params);
        return servicemaster_mikado_get_shortcode_module_template_part('templates/playlist-template', 'playlist', '', $params);

    }

    private function getStyleAttributes($params) {
        $styles = array();

        if($params['background_color'] !== ''){
            $styles[] = 'background-color:'.$params['background_color'];
        }

        return $styles;
    }
}