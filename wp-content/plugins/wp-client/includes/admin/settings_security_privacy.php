<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {
    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'privacy' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_privacy = WPC()->get_settings( 'privacy' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Privacy Policy', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'using_privacy',
        'type' => 'checkbox',
        'label' => __( 'Use Privacy Policy', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_privacy['using_privacy'] ) ) ? $wpc_privacy['using_privacy'] : 'no',
        'description' => __( 'Enable Privacy Policy', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'using_privacy_form',
        'type' => 'multi-checkbox',
        'label' => __( 'Use on', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_privacy['using_privacy_form'] ) ) ? $wpc_privacy['using_privacy_form'] : '',
        'options' => array(
            'registration' => __( 'Registration Form', WPC_CLIENT_TEXT_DOMAIN ),
            'login' => __( 'Login Form', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'conditional' => array( 'using_privacy', '=', 'yes' ),
    ),
    array(
        'id' => 'privacy_default_checked',
        'type' => 'checkbox',
        'label' => __( 'Checked By Default', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_privacy['privacy_default_checked'] ) ) ? $wpc_privacy['privacy_default_checked'] : 'no',
        'description' => __( 'Yes, check Privacy Policy by default on form', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'using_privacy', '=', 'yes' ),
    ),
    array(
        'id' => 'privacy_text',
        'type' => 'text',
        'label' => __( 'Agree Text', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_privacy['privacy_text'] ) ) ? $wpc_privacy['privacy_text'] : __( 'I accept {link}', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => '',
        'conditional' => array( 'using_privacy', '=', 'yes' ),
    ),
    array(
        'id' => 'privacy_notice',
        'type' => 'textarea',
        'label' => __( 'Terms Error Text', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_privacy['privacy_notice'] ) ) ? $wpc_privacy['privacy_notice'] : __( 'Sorry, you must agree to the Privacy Policy to continue', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => '',
        'conditional' => array( 'using_privacy', '=', 'yes' ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );