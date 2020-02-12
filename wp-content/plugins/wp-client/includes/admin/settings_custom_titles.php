<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$default_titles = WPC()->get_default_titles();


//save custom titles
if ( !empty( $_REQUEST['wpc_settings'] ) ) {

    if ( is_array( $_REQUEST['wpc_settings'] ) ) {
        $ct = $_REQUEST['wpc_settings'];
        foreach( $default_titles as $key => $values ) {
            $custom_titles[$key]['s'] = ( isset( $ct[$key]['s'] ) && '' != $ct[$key]['s'] ) ? $ct[$key]['s'] : $values['s'];
            $custom_titles[$key]['p'] = ( isset( $ct[$key]['p'] ) && '' != $ct[$key]['p'] ) ? $ct[$key]['p'] : $values['p'];
        }
    } else {
        $custom_titles = $default_titles;
    }

    WPC()->settings()->update( $custom_titles, 'custom_titles' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}


$wpc_custom_titles = WPC()->get_settings( 'custom_titles' );
$wpc_custom_titles = ( is_array( $wpc_custom_titles ) ) ? array_merge( $default_titles, $wpc_custom_titles ) : $default_titles;

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Custom Titles Settings', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => __( 'Use the fields below to change the default text that is used for various aspects of the plugin, such as user role titles.', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);


foreach( $wpc_custom_titles as $key => $values ) {
    $section_fields[] = array(
        'type' => 'title',
        'label' => ucwords( str_replace( array('_'), ' ', $key ) ),
    );
    $section_fields[] = array(
        'id' => $key . '_s',
        'name' => 'wpc_settings[' . $key . '][s]',
        'type' => 'text',
        'label' => __( 'Singular', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => $values['s'],
    );
    $section_fields[] = array(
        'id' => $key . '_p',
        'name' => 'wpc_settings[' . $key . '][p]',
        'type' => 'text',
        'label' => __( 'Plural', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => $values['p'],
    );
}


WPC()->settings()->render_settings_section( $section_fields );