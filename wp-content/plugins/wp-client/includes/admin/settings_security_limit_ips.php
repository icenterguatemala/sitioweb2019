<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'limit_ips' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_limit_ips = WPC()->get_settings( 'limit_ips' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'IP Restriction Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'enable_limit',
        'type' => 'checkbox',
        'label' => __( 'Use IP Restriction', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_limit_ips['enable_limit'] ) ) ? $wpc_limit_ips['enable_limit'] : 'no',
        'description' => __( 'Enable IP Restriction', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'ips',
        'type' => 'textarea',
        'label' => __( 'Allowed IP Addresses', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_limit_ips['ips'] ) ) ? $wpc_limit_ips['ips'] : '',
        'description' => __( 'Enter IP address here (one per line)', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'enable_limit', '=', 'yes' ),
    ),
);


WPC()->settings()->render_settings_section( $section_fields );