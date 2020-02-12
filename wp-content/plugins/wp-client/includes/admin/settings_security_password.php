<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {
    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'password' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_password = WPC()->get_settings( 'password' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Password Requirements', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'password_minimal_length',
        'type' => 'text',
        'label' => __( 'Minimum Length', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_password['password_minimal_length'] ) ) ? $wpc_password['password_minimal_length'] : '1',
        'description' => '',
    ),
    array(
        'id' => 'password_strength',
        'type' => 'selectbox',
        'label' => __( 'Strength', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_password['password_strength'] ) ) ? $wpc_password['password_strength'] : '5',
        'options' => array(
            '5' => __( 'Very Weak', WPC_CLIENT_TEXT_DOMAIN ),
            '2' => __( 'Weak', WPC_CLIENT_TEXT_DOMAIN ),
            '3' => __( 'Medium', WPC_CLIENT_TEXT_DOMAIN ),
            '4' => __( 'High', WPC_CLIENT_TEXT_DOMAIN ),
        ),
    ),
    array(
        'id' => 'password_black_list',
        'type' => 'textarea',
        'label' => __( 'Black List', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_password['password_black_list'] ) ) ? $wpc_password['password_black_list'] : "password\nqwerty\n123456789",
        'description' => __( 'Enter passwords (one per line) here to prevent users from choosing them', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'password_mixed_case',
        'type' => 'checkbox',
        'label' => __( 'Mixed Cases', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_password['password_mixed_case'] ) ) ? $wpc_password['password_mixed_case'] : 'no',
        'description' => __( 'Password must contain a mixture of uppercase and lowercase characters', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'password_numeric_digits',
        'type' => 'checkbox',
        'label' => __( 'Digits', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_password['password_numeric_digits'] ) ) ? $wpc_password['password_numeric_digits'] : 'no',
        'description' => __( 'Password must contain digits (0-9)', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'password_special_chars',
        'type' => 'checkbox',
        'label' => __( 'Special Characters', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_password['password_special_chars'] ) ) ? $wpc_password['password_special_chars'] : 'no',
        'description' => __( 'Password must contain special characters (eg: .,!#$%_+)', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );