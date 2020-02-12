<?php

if (!function_exists('servicemaster_mikado_logo_meta_box_map')) {
    function servicemaster_mikado_logo_meta_box_map() {

        $logo_meta_box = servicemaster_mikado_add_meta_box(
            array(
                'scope' => array('page', 'portfolio-item', 'post'),
                'title' => esc_html__('Logo', 'servicemaster'),
                'name'  => 'logo_meta'
            )
        );


        servicemaster_mikado_add_meta_box_field(
            array(
                'name'          => 'mkd_logo_image_meta',
                'type'          => 'image',
                'label'         => esc_html__('Logo Image - Default', 'servicemaster'),
                'description'   => esc_html__('Choose a default logo image to display ', 'servicemaster'),
                'parent'        => $logo_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'          => 'mkd_logo_image_dark_meta',
                'type'          => 'image',
                'label'         => esc_html__('Logo Image - Dark', 'servicemaster'),
                'description'   => esc_html__('Choose a default logo image to display ', 'servicemaster'),
                'parent'        => $logo_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'          => 'mkd_logo_image_light_meta',
                'type'          => 'image',
                'label'         => esc_html__('Logo Image - Light', 'servicemaster'),
                'description'   => esc_html__('Choose a default logo image to display ', 'servicemaster'),
                'parent'        => $logo_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'          => 'mkd_logo_image_sticky_meta',
                'type'          => 'image',
                'label'         => esc_html__('Logo Image - Sticky', 'servicemaster'),
                'description'   => esc_html__('Choose a default logo image to display ', 'servicemaster'),
                'parent'        => $logo_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'          => 'mkd_logo_image_mobile_meta',
                'type'          => 'image',
                'label'         => esc_html__('Logo Image - Mobile', 'servicemaster'),
                'description'   => esc_html__('Choose a default logo image to display ', 'servicemaster'),
                'parent'        => $logo_meta_box
            )
        );
    }

    add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_logo_meta_box_map');
}