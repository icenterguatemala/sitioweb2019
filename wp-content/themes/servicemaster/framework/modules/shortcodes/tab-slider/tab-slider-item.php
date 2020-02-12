<?php
namespace ServiceMaster\Modules\Shortcodes\TabSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class TabSliderItem implements ShortcodeInterface {
    private $base;

    public function __construct() {
        $this->base = 'mkd_tab_slider_item';

        add_action('vc_before_init', array($this, 'vcMap'));
    }

    public function getBase() {
        return $this->base;
    }

    public function vcMap() {
        vc_map(array(
            'name'                    => esc_html__('Tab Slider Item', 'servicemaster'),
            'base'                    => $this->base,
            'category'                => 'by MIKADO',
            'icon'                    => 'icon-wpb-tab-slider-item extended-custom-icon',
            'as_parent'               => array('except' => 'vc_row'),
            'as_child'                => array('only' => 'mkd_tab_slider'),
            'show_settings_on_create' => true,
            'content_element'         => true,
            'js_view'                 => 'VcColumnView',
            'params'                  => array(
                array(
                    'type'        => 'textfield',
                    'heading'     => esc_html__('Slide Title', 'servicemaster'),
                    'param_name'  => 'slide_title',
                    'admin_label' => true
                ),
            )
        ));
    }

    public function render($atts, $content = null) {
        $default_atts = array(
            'slide_title'    => ''
        );

        $params = shortcode_atts($default_atts, $atts);

        $params['content'] = $content;

        return servicemaster_mikado_get_shortcode_module_template_part('templates/tab-slider-item', 'tab-slider', '', $params);
    }
}