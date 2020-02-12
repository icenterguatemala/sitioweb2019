<?php
namespace ServiceMaster\Modules\RestaurantItem;

use ServiceMaster\Modules\Shortcodes\Lib\ShortcodeInterface;

class RestaurantItem implements ShortcodeInterface{
    private $base;

    function __construct() {
        $this->base = 'mkd_restaurant_item';
        add_action('vc_before_init', array($this, 'vcMap'));
    }
    public function getBase() {
        return $this->base;
    }

    public function vcMap() {
        if(function_exists('vc_map')){
            vc_map(
                array(
                    'name' => esc_html__('Restaurant Item', 'servicemaster'),
                    'base' => $this->base,
                    'as_child' => array('only' => 'mkd_restaurant_menu'),
                    'category' => 'by MIKADO',
                    'icon' => 'icon-wpb-restaurant-menu-item extended-custom-icon',
                    'params' =>	array(
                        array(
                            'type' => 'textfield',
                            'class' => '',
                            'heading' =>  esc_html__('Item Title','servicemaster'),
                            'param_name' => 'title',
                            'value' => '',
                            'description' => ''
                        ),
                        array(
                            'type'       => 'dropdown',
                            'heading'    =>  esc_html__('Title Tag','servicemaster'),
                            'param_name' => 'title_tag',
                            'value'      => array(
                                ''   => '',
                                'h2' => 'h2',
                                'h3' => 'h3',
                                'h4' => 'h4',
                                'h5' => 'h5',
                                'h6' => 'h6',
                            ),
                            'dependency' => array(
								'element' => 'title',
								'not_empty' => true
							)
                        ),
                        array(
                            'type' => 'textfield',
                            'class' => '',
                            'heading' => esc_html__( 'Currency','servicemaster'),
                            'param_name' => 'currency',
                            'value' => '',
                            'description' =>  esc_html__('Default is "$"','servicemaster'),
                        ),
                        array(
                            'type' => 'textfield',
                            'class' => '',
                            'heading' =>  esc_html__('Price','servicemaster'),
                            'param_name' => 'price',
                            'value' => '',
                            'description' => ''
                        ),
                        array(
                            'type' => 'textfield',
                            'class' => '',
                            'heading' =>  esc_html__('Old Price','servicemaster'),
                            'param_name' => 'old_price',
                            'value' => '',
                            'description' => ''
                        ),
                        array(
                            'type' => 'attach_image',
                            'class' => '',
                            'heading' => esc_html__( 'Item Image','servicemaster'),
                            'param_name' => 'item_image',
                            'value' => '',
                            'description' => ''
                        ),
                        array(
                            'type' => 'textfield',
                            'class' => '',
                            'heading' => esc_html__( 'Description','servicemaster'),
                            'param_name' => 'description',
                            'value' => '',
                            'description' => ''
                        ),
                        array(
                            'type'       => 'dropdown',
                            'heading'    =>  esc_html__('Number of Stars','servicemaster'),
                            'param_name' => 'number_of_stars',
                            'value'      => array(
                                '0'   => '0',
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '5' => '5',
                            ),
                        ),
                        array(
                            'type'        => 'checkbox',
                            'heading'     => esc_html__('Mark Item as Recommended', 'servicemaster'),
                            'param_name'  => 'item_recommended',
                            'value'       => array( esc_html__('Recommended?','servicemaster' )=> 'yes'),
                            'description' => ''
                        )
                    )
                )
            );
        }
    }

    public function render($atts, $content = null) {
        $args = array(
            'title' => '',
            'title_tag' => 'h4',
            'currency' => '$',
            'item_image' => '',
            'price' => '',
            'description' => '',
            'number_of_stars'=>'0',
            'item_recommended'=>'',
            'old_price'=>''
        );

        $params = shortcode_atts($args, $atts);
        $params['content'] = $content;
        $params['item_holder_classes'] = $this->getItemHolderClasses($params);

        $html = servicemaster_mikado_get_shortcode_module_template_part('templates/item-template', 'restaurant-menu', '', $params);

        return $html;

    }

    private function getItemHolderClasses($params){
        $classes = array('mkd-rstrnt-item');

        if($params['item_recommended'] == 'yes'){
            $classes [] = 'mkd-recommended-enabled';
        }

        return $classes;
    }
}
