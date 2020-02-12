<?php

//Carousels

if (!function_exists('servicemaster_mikado_carousel_meta_box_map')) {
    function servicemaster_mikado_carousel_meta_box_map() {

        $carousel_meta_box = servicemaster_mikado_add_meta_box(
            array(
                'scope' => array('carousels'),
                'title' => esc_html__('Carousel', 'servicemaster'),
                'name' => 'carousel_meta'
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'        => 'mkd_carousel_image',
                'type'        => 'image',
                'label'       => esc_html__('Carousel Image', 'servicemaster'),
                'description' => esc_html__('Choose carousel image (min width needs to be 215px)', 'servicemaster'),
                'parent'      => $carousel_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'        => 'mkd_carousel_hover_image',
                'type'        => 'image',
                'label'       => esc_html__('Carousel Hover Image', 'servicemaster'),
                'description' => esc_html__('Choose carousel hover image (min width needs to be 215px)', 'servicemaster'),
                'parent'      => $carousel_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'        => 'mkd_carousel_item_link',
                'type'        => 'text',
                'label'       => esc_html__('Link', 'servicemaster'),
                'description' => esc_html__('Enter the URL to which you want the image to link to (e.g. http://www.example.com)', 'servicemaster'),
                'parent'      => $carousel_meta_box
            )
        );

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'        => 'mkd_carousel_item_target',
                'type'        => 'selectblank',
                'label'       => esc_html__('Target', 'servicemaster'),
                'description' => esc_html__('Specify where to open the linked document', 'servicemaster'),
                'parent'      => $carousel_meta_box,
                'options' => array(
                    '_self' => esc_html__('Self', 'servicemaster'),
                    '_blank' => esc_html__('Blank', 'servicemaster')
                )
            )
        );

    }
    add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_carousel_meta_box_map');
}