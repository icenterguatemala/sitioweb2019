<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wp_widget_factory;
$options = get_option( 'wpc_widget_show_settings', array() );
if( isset( $wp_widget_factory->widgets ) ) {
    foreach( $options as $id=>$value ) {
        foreach( $wp_widget_factory->widgets as $key=>$widget_instance ) {
            if( $id == $widget_instance->id ) {
                $settings = $widget_instance->get_settings();
                $num = isset( $widget_instance->number ) ? (int)$widget_instance->number : 0;
                $settings[ $num ]['wpc_show_widget'] = $value;
                break;
            }
        }
    }
}