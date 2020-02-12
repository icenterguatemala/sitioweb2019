<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['update_settings'] ) ) {

    $settings = ( !empty( $_POST['wpc_settings'] ) ) ? $_POST['wpc_settings'] : array();

    WPC()->settings()->update( $settings, 'business_info' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'General Business Information', WPC_CLIENT_TEXT_DOMAIN ),
    ));
$wpc_business_info = WPC()->get_settings( 'business_info' );
$fields = WPC()->get_business_info_fields();

foreach( $fields as $key => $value ) {
    $type = in_array( $key, array( 'business_address', 'business_mailing_address' ) ) ? 'textarea' : 'text';

    $section_fields[] = array(
        'id' => $key,
        'type' => $type,
        'label' => $value,
        'value' => ( isset( $wpc_business_info[$key] ) ) ? $wpc_business_info[$key] : '',
        'description' => __( 'for placeholder', WPC_CLIENT_TEXT_DOMAIN ) . ' {' . $key . '}',
    );
}

WPC()->settings()->render_settings_section( $section_fields );