<?php
namespace ServiceMaster\Modules\Separator;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class Separator implements ShortcodeInterface
{

    private $base;

    function __construct() {
        $this->base = 'mkd_separator';
        add_action('vc_before_init', array($this, 'vcMap'));
    }

    public function getBase() {
        return $this->base;
    }

    public function vcMap() {

        vc_map(
            array(
                'name'                    => esc_html__('Separator', 'servicemaster'),
                'base'                    => $this->base,
                'category'                => 'by MIKADO',
                'icon'                    => 'icon-wpb-separator extended-custom-icon',
                'show_settings_on_create' => true,
                'class'                   => 'wpb_vc_separator',
                'custom_markup'           => '<div></div>',
                'params'                  => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Extra class name', 'servicemaster'),
                        'param_name'  => 'class_name',
                        'value'       => '',
                        'description' => esc_html__('Style particular content element differently - add a class name and refer to it in custom CSS.', 'servicemaster')
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Type', 'servicemaster'),
                        'param_name'  => 'type',
                        'value'       => array(
                            'Normal'     => 'normal',
                            'Full Width' => 'full-width'
                        ),
                        'description' => ''
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Position', 'servicemaster'),
                        'param_name'  => 'position',
                        'value'       => array(
                            esc_html__('Center', 'servicemaster') => 'center',
                            esc_html__('Left', 'servicemaster')   => 'left',
                            esc_html__('Right', 'servicemaster')  => 'right'
                        ),
                        'save_always' => true,
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => array('normal')
                        )
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Color Type', 'servicemaster'),
                        'param_name'  => 'color_type',
                        'admin_label' => true,
                        'value'       => array(
                            esc_html__('Normal', 'servicemaster')   => 'normal',
                            esc_html__('Gradient', 'servicemaster') => 'gradient'
                        ),
                        'save_always' => true
                    ),
                    array(
                        'type'       => 'colorpicker',
                        'heading'    => esc_html__('Color', 'servicemaster'),
                        'param_name' => 'color',
                        'value'      => '',
                        'dependency' => array(
                            'element' => 'color_type',
                            'value'   => array('normal')
                        )
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Gradient Style', 'servicemaster'),
                        'param_name'  => 'gradient_style',
                        'admin_label' => true,
                        'value'       => array_flip(servicemaster_mikado_get_separator_gradient_left_to_right_styles()),
                        'dependency'  => array(
                            'element' => 'color_type',
                            'value'   => array('gradient')
                        ),
                        'save_always' => true
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Border Style', 'servicemaster'),
                        'param_name' => 'border_style',
                        'value'      => array(
                            esc_html__('Default', 'servicemaster') => '',
                            esc_html__('Dashed', 'servicemaster')  => 'dashed',
                            esc_html__('Solid', 'servicemaster')   => 'solid',
                            esc_html__('Dotted', 'servicemaster')  => 'dotted'
                        )
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Width', 'servicemaster'),
                        'param_name'  => 'width',
                        'value'       => '',
                        'description' => '',
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => array('normal')
                        )
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Thickness (px)', 'servicemaster'),
                        'param_name'  => 'thickness',
                        'value'       => '',
                        'description' => ''
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Top Margin', 'servicemaster'),
                        'param_name'  => 'top_margin',
                        'value'       => '',
                        'description' => ''
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => esc_html__('Bottom Margin', 'servicemaster'),
                        'param_name' => 'bottom_margin',
                        'value'      => '',
                    )
                )
            )
        );

    }

    public function render($atts, $content = null) {
        $args = array(
            'class_name'     => '',
            'type'           => '',
            'position'       => 'center',
            'color_type'     => 'normal',
            'color'          => '',
            'gradient_style' => '',
            'border_style'   => '',
            'width'          => '',
            'thickness'      => '',
            'top_margin'     => '',
            'bottom_margin'  => ''
        );

        $params = shortcode_atts($args, $atts);
        extract($params);
        $params['separator_class'] = $this->getSeparatorClass($params);
        $params['separator_inner_class'] = $this->getSeparatorInnerClass($params);
        $params['separator_style'] = $this->getSeparatorStyle($params);


        $html = servicemaster_mikado_get_shortcode_module_template_part('templates/separator-template', 'separator', '', $params);

        return $html;
    }


    /**
     * Return Separator classes
     *
     * @param $params
     *
     * @return array
     */
    private function getSeparatorClass($params) {

        $separator_class = array();

        if ($params['class_name'] !== '') {
            $separator_class[] = $params['class_name'];
        }
        if ($params['position'] !== '') {
            $separator_class[] = 'mkd-separator-' . $params['position'];
        }
        if ($params['type'] !== '') {
            $separator_class[] = 'mkd-separator-' . $params['type'];
        }

        return implode(' ', $separator_class);

    }

    /**
     * Return Separator inner classes
     *
     * @param $params
     *
     * @return array
     */
    private function getSeparatorInnerClass($params) {

        $separator_inner_class = array('mkd-separator');

        if($params['color_type'] == 'gradient') {
            $separator_inner_class[] = $params['gradient_style'];
        }

        return implode(' ', $separator_inner_class);

    }

    /**
     * Return Elements Holder Item Content style
     *
     * @param $params
     *
     * @return array
     */
    private function getSeparatorStyle($params) {

        $separator_style = array();

        if ($params['color'] !== '') {
            $separator_style[] = 'border-color: ' . $params['color'];
        }
        if ($params['border_style'] !== '') {
            $separator_style[] = 'border-style: ' . $params['border_style'];
        }
        if ($params['width'] !== '') {
            if (servicemaster_mikado_string_ends_with($params['width'], '%') || servicemaster_mikado_string_ends_with($params['width'], 'px')) {
                $separator_style[] = 'width: ' . $params['width'];
            } else {
                $separator_style[] = 'width: ' . $params['width'] . 'px';
            }
        }
        if ($params['thickness'] !== '') {
            $separator_style[] = 'border-bottom-width: ' . $params['thickness'] . 'px';
        }
        if ($params['top_margin'] !== '') {
            if (servicemaster_mikado_string_ends_with($params['top_margin'], '%') || servicemaster_mikado_string_ends_with($params['top_margin'], 'px')) {
                $separator_style[] = 'margin-top: ' . $params['top_margin'];
            } else {
                $separator_style[] = 'margin-top: ' . $params['top_margin'] . 'px';
            }
        }
        if ($params['bottom_margin'] !== '') {
            if (servicemaster_mikado_string_ends_with($params['bottom_margin'], '%') || servicemaster_mikado_string_ends_with($params['bottom_margin'], 'px')) {
                $separator_style[] = 'margin-bottom: ' . $params['bottom_margin'];
            } else {
                $separator_style[] = 'margin-bottom: ' . $params['bottom_margin'] . 'px';
            }
        }

        return implode(';', $separator_style);

    }

}
