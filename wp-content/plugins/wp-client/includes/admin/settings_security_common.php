<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {
    $settings = $_POST['wpc_settings'];

    if( !WPC()->permalinks ) {
        $settings['login_url'] = '';
        $settings['hide_admin'] = 'no';
    }

    if( !empty( $settings['login_url'] ) ) {

        $settings['login_url'] = str_replace( get_home_url( null, '', 'http' ), '', $settings['login_url'] );
        $settings['login_url'] = str_replace( get_home_url( null, '', 'https' ), '', $settings['login_url'] );
        $settings['login_url'] = sanitize_title_with_dashes( $settings['login_url'] );
        $settings['login_url'] = str_replace( '/', '', $settings['login_url'] );

        $disallowed = array(
            'user', 'wp-admin', 'wp-content', 'wp-includes', 'wp-feed.php', 'index', 'feed', 'rss', 'robots', 'robots.txt', 'wp-login.php',
            'wp-login', 'wp-config', 'blog', 'sitemap', 'sitemap.xml',
        );

        if ( in_array( $settings['login_url'], $disallowed ) ) {
            WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=cl_url' );
        }
    }

    WPC()->settings()->update( $settings, 'common_secure' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$common_secure = WPC()->get_settings( 'common_secure' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Common Secure', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'hide_site',
        'type' => 'checkbox',
        'label' => __( 'Protect Whole Site', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $common_secure['hide_site'] ) ) ? $common_secure['hide_site'] : 'no',
        'description' => __( 'Yes, redirect non-logged in users to login page', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'pages_white_list',
        'type' => 'textarea',
        'label' => __( 'Exclude URLs', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $common_secure['pages_white_list'] ) ) ? $common_secure['pages_white_list'] : '',
        'description' => __( 'Enter URLs here to allow showing for all users. One per line', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'hide_site', '=', 'yes' ),
    ),
    array(
        'id' => 'hide_admin',
        'type' => 'checkbox',
        'label' => __( 'Hide /wp-admin/', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $common_secure['hide_admin'] ) ) ? $common_secure['hide_admin'] : 'no',
        'description' => __( 'Yes, non-logged in users will receive 404 page when trying to access /wp-admin/ area', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'login_url',
        'type' => 'text',
        'size' => 'small',
        'label' => __( 'Secure Login URL', WPC_CLIENT_TEXT_DOMAIN ),
        'placeholder' => __( 'Example', WPC_CLIENT_TEXT_DOMAIN ) . ': login',
        'value' => ( isset( $common_secure['login_url'] ) ) ? $common_secure['login_url'] : '',
        'before_field' => get_home_url() . '/',
        'description' =>    '<b>' . wp_guess_url() . '/wp-login.php' . '</b> ' .
            __( 'will be changed to whatever you put in this box ', WPC_CLIENT_TEXT_DOMAIN ) . '. <br />' .
            __( 'For example, if you put "login" into the box, your new login URL will be ' , WPC_CLIENT_TEXT_DOMAIN ) . home_url() . '/login/',
    ),
);

WPC()->settings()->render_settings_section( $section_fields );