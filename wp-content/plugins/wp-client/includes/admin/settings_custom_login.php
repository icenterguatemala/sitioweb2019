<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];

    // colour validation
    $settings['cl_color'] = str_replace( '#', '', $settings['cl_color'] );
    $settings['cl_color'] = substr( $settings['cl_color'], 0, 6 );

    // error colour validation
    $settings['cl_error_color'] = str_replace( '#', '', $settings['cl_error_color'] );
    $settings['cl_error_color'] = substr( $settings['cl_error_color'], 0, 6 );

    // background colour validation
    $settings['cl_backgroundColor'] = str_replace( '#', '', $settings['cl_backgroundColor'] );
    $settings['cl_backgroundColor'] = substr( $settings['cl_backgroundColor'], 0, 6 );

    // colour validation
    $settings['cl_linkColor'] = str_replace( '#', '', $settings['cl_linkColor'] );
    $settings['cl_linkColor'] = substr( $settings['cl_linkColor'], 0, 6 );

    // clean image urls
    $settings['cl_background'] = esc_url_raw( $settings['cl_background'] );

    WPC()->settings()->update( $settings, 'custom_login' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_custom_login = WPC()->get_settings( 'custom_login' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Custom Login Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'cl_enable',
        'type' => 'checkbox',
        'label' => __( 'Use Custom Login', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_enable'] ) ) ? $wpc_custom_login['cl_enable'] : 'no',
        'description' => __( 'Use Custom Login Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'cl_logo_link',
        'type' => 'text',
        'label' => __( 'Logo Link URL', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_logo_link'] ) ) ? $wpc_custom_login['cl_logo_link'] : '',
        'description' => __( 'Logo link URL on Login form. Leave empty to use default', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_logo_title',
        'type' => 'text',
        'label' => __( 'Logo Title Text', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_logo_title'] ) ) ? $wpc_custom_login['cl_logo_title'] : '',
        'description' => __( 'Login form logo title text showing on hover. Leave empty to use default', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_background',
        'type' => 'text',
        'label' => __( 'Background Image URL', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_background'] ) ) ? $wpc_custom_login['cl_background'] : WPC()->plugin_url .'images/logo.png',
        'description' => __( 'Image URL used for background (sized 312px in width, and around 600px in height, so that it can be cropped).', WPC_CLIENT_TEXT_DOMAIN ) .
                        __( 'You can upload your image via media uploader', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_backgroundColor',
        'type' => 'text',
        'label' => __( 'Page Background Color', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_backgroundColor'] ) ) ? $wpc_custom_login['cl_backgroundColor'] : 'ffffff',
        'description' => __( 'Hex digits of color code', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_color',
        'type' => 'text',
        'label' => __( 'Text Color', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_color'] ) ) ? $wpc_custom_login['cl_color'] : '000033',
        'description' => __( 'Hex digits of color code', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_error_color',
        'type' => 'text',
        'label' => __( 'Error Text Color', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_error_color'] ) ) ? $wpc_custom_login['cl_error_color'] : '00A5E2',
        'description' => __( 'Hex digits of color code', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_linkColor',
        'type' => 'text',
        'label' => __( 'Text Link Color', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_linkColor'] ) ) ? $wpc_custom_login['cl_linkColor'] : '',
        'description' => __( 'Hex digits of color code', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),
    array(
        'id' => 'cl_form_border',
        'type' => 'checkbox',
        'label' => __( 'Hide Form Border', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_custom_login['cl_form_border'] ) ) ? $wpc_custom_login['cl_form_border'] : 'no',
        'description' => __( 'Yes, hide form border', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'cl_enable', '=', 'yes' ),
    ),

);

WPC()->settings()->render_settings_section( $section_fields );

if( ! WPC()->permalinks ) {

echo '<div id="message" class="error wpc_notice fade">
    <p>' . __( '<strong>Important!:</strong> Security settings for Hide WP Admin & Custom Login URL don\'t work with default permalinks. If you want to use this settings please change your permalink settings ', WPC_CLIENT_TEXT_DOMAIN ) .
    '<a href="' . get_admin_url( get_current_blog_id(), 'options-permalink.php' ) .'" target="_blank">' . __( 'HERE', WPC_CLIENT_TEXT_DOMAIN ). '</a>.</p>
</div>';

}
