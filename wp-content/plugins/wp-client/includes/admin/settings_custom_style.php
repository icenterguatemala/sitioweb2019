<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {

    WPC()->settings()->update( $_POST['wpc_settings'], 'custom_style' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_custom_style = WPC()->get_settings( 'custom_style' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Custom CSS Style Settings', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => __( 'This CSS code will be included on all pages in user area (in head side by default).', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'style',
        'type' => 'textarea',
        'size' => 'max',
        'label' => __( 'CSS Style', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_style['style'] ) ) ? $wpc_custom_style['style'] : '',
        'description' => '<b>' . __( 'Be sure that CSS code is valid!', WPC_CLIENT_TEXT_DOMAIN ) . '</b>',
    ),

    array(
        'id' => 'in_footer',
        'type' => 'checkbox',
        'label' => __( 'In Footer', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_style['in_footer'] ) ) ? $wpc_custom_style['in_footer'] : 'no',
        'description' => __( 'Add this CSS style to theme footer. Please note that it might not work with some themes.', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );