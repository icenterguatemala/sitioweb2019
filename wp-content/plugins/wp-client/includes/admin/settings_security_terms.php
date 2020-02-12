<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {
    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'terms' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_terms = WPC()->get_settings( 'terms' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'using_terms',
        'type' => 'checkbox',
        'label' => __( 'Use Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_terms['using_terms'] ) ) ? $wpc_terms['using_terms'] : 'no',
        'description' => __( 'Enable Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'using_terms_form',
        'type' => 'multi-checkbox',
        'label' => __( 'Use on', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_terms['using_terms_form'] ) ) ? $wpc_terms['using_terms_form'] : '',
        'options' => array(
            'registration' => __( 'Registration Form', WPC_CLIENT_TEXT_DOMAIN ),
            'login' => __( 'Login Form', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'conditional' => array( 'using_terms', '=', 'yes' ),
    ),
    array(
        'id' => 'terms_default_checked',
        'type' => 'checkbox',
        'label' => __( 'Checked By Default', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_terms['terms_default_checked'] ) ) ? $wpc_terms['terms_default_checked'] : 'no',
        'description' => __( 'Yes, check Terms/Conditions by default on form', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'using_terms', '=', 'yes' ),
    ),
    array(
        'id' => 'terms_text',
        'type' => 'text',
        'label' => __( 'Agree Text', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_terms['terms_text'] ) ) ? $wpc_terms['terms_text'] : __( 'I agree.', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => '',
        'conditional' => array( 'using_terms', '=', 'yes' ),
    ),
    array(
        'id' => 'terms_hyperlink',
        'type' => 'text',
        'label' => __( 'Terms URL', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_terms['terms_hyperlink'] ) ) ? $wpc_terms['terms_hyperlink'] : '',
        'description' => '',
        'conditional' => array( 'using_terms', '=', 'yes' ),
    ),
    array(
        'id' => 'terms_notice',
        'type' => 'textarea',
        'label' => __( 'Terms Error Text', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_terms['terms_notice'] ) ) ? $wpc_terms['terms_notice'] : __( 'Sorry, you must agree to the Terms/Conditions to continue', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => '',
        'conditional' => array( 'using_terms', '=', 'yes' ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );