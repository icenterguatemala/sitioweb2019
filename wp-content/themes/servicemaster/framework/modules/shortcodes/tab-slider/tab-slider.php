<?php
namespace ServiceMaster\Modules\Shortcodes\TabSlider;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class TabSlider implements ShortcodeInterface {
    private $base;

    public function __construct() {
        $this->base = 'mkd_tab_slider';

        add_action('vc_before_init', array($this, 'vcMap'));
    }

    public function getBase() {
        return $this->base;
    }

    public function vcMap() {
        vc_map(array(
            'name'                    => esc_html__('Tab Slider', 'servicemaster'),
            'base'                    => $this->base,
            'as_parent'               => array('only' => 'mkd_tab_slider_item'),
            'content_element'         => true,
            'show_settings_on_create' => false,
            'category'                => 'by MIKADO',
            'icon'                    => 'icon-wpb-tab-slider extended-custom-icon',
            'js_view'                 => 'VcColumnView',
            'params'                  => array(
                array(
                    'type'       => 'dropdown',
                    'heading'    => esc_html__('Tabs Skin', 'servicemaster'),
                    'param_name' => 'skin',
                    'value'      => array(
                        ''                                 => '',
                        esc_html__('Light', 'servicemaster')  => 'light',
                        esc_html__('Dark', 'servicemaster') => 'dark'
                    )
                )
            )
        ));
    }

    public function render($atts, $content = null) {
        $default_atts = array(
            'skin' => ''
        );

        $params = shortcode_atts($default_atts, $atts);
        $params['content'] = $content;
        $params['classes'] = $this -> getHolderClasses($params);

        return servicemaster_mikado_get_shortcode_module_template_part('templates/tab-slider-holder', 'tab-slider', '', $params);
    }


    /**
     * Return additional classes
     *
     * @param $params
     *
     * @return array
     */
    private function getHolderClasses($params) {
        $classes = '';

        if($params['skin'] !== '') {
            $classes .= 'mkd-tab-slider-'.$params['skin'];
        }

        return $classes;

    }
}