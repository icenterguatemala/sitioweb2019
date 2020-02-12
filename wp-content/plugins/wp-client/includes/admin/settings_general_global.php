<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['update_settings'] ) ) {

    $settings = ( !empty( $_POST['wpc_settings'] ) ) ? $_POST['wpc_settings'] : array();

    WPC()->settings()->update( $settings, 'general' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_general = WPC()->get_settings( 'general' );
$wp_uploads = wp_upload_dir();

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'General Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'easy_mode',
        'type' => 'checkbox',
        'label' => __( 'Easy Mode', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_general['easy_mode'] ) ) ? $wpc_general['easy_mode'] : 'no',
        'description' => __( 'Enable Easy Mode', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'resources_folder',
        'type' => 'text',
        'label' => __( 'Resources Directory Path', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_general['resources_folder'] ) ) ? $wpc_general['resources_folder'] : '',
        'description' => '<strong>' . __( 'Important!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</strong>' . __( 'Before changing the path, please copy \'wpclient\' folder to a new path', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />
                        ' . __( 'Current path', WPC_CLIENT_TEXT_DOMAIN ) . ': <strong>' . WPC()->get_upload_dir() . '</strong></span><br />
                        ' . sprintf( __( 'If the field is empty, the default path %s is used', WPC_CLIENT_TEXT_DOMAIN ), '<strong>' . $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . '</strong>' ),
    ),
    array(
        'id' => 'exclude_pp_from_search',
        'type' => 'checkbox',
        'label' => sprintf( __( '%s in Search', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ),
        'value' => ( isset( $wpc_general['exclude_pp_from_search'] ) ) ? $wpc_general['exclude_pp_from_search'] : 'yes',
        'description' => sprintf( __( 'Exclude %s from global Wordpress Search', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ),
    ),
    array(
        'id' => 'avatars_shapes',
        'type' => 'selectbox',
        'label' => __( 'Avatar Shapes', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_general['avatars_shapes'] ) ) ? $wpc_general['avatars_shapes'] : 'square',
        'options' => array(
            'square' => __( 'Square', WPC_CLIENT_TEXT_DOMAIN ),
            'circle' => __( 'Circle', WPC_CLIENT_TEXT_DOMAIN ),
        ),
    ),
    array(
        'id' => 'graphic',
        'type' => 'text',
        'label' => __( 'Graphic', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_general['graphic'] ) ) ? $wpc_general['graphic'] : '',
        'description' => __( 'Graphic for shortcode [wpc_client_graphic]', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );