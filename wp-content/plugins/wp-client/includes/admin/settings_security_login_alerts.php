<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {
    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'login_alerts' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_login_alerts = WPC()->get_settings( 'login_alerts' );


$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Login Alerts Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'email',
        'type' => 'text',
        'label' => __( 'Email Address', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_login_alerts['email'] ) ) ? $wpc_login_alerts['email'] : '',
        'description' => __( 'You can edit templates for Successful login and Failed login', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);


WPC()->settings()->render_settings_section( $section_fields );