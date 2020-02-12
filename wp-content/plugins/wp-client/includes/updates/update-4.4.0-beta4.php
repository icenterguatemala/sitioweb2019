<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_clients_staff = WPC()->get_settings( 'clients_staff' );


//change password settings structure and move to another option
$wpc_password = array();
if ( isset( $wpc_clients_staff['password_minimal_length'] ) ) {
    $wpc_password['password_minimal_length'] = !empty( $wpc_clients_staff['password_minimal_length'] ) ? $wpc_clients_staff['password_minimal_length'] : '1';
    unset( $wpc_clients_staff['password_minimal_length'] );
}
if ( isset( $wpc_clients_staff['password_strength'] ) ) {
    $wpc_password['password_strength'] = !empty( $wpc_clients_staff['password_strength'] ) ? $wpc_clients_staff['password_strength'] : '5';
    unset( $wpc_clients_staff['password_strength'] );
}
if ( isset( $wpc_clients_staff['password_black_list'] ) ) {
    $wpc_password['password_black_list'] = !empty( $wpc_clients_staff['password_black_list'] ) ? $wpc_clients_staff['password_black_list'] : '';
    unset( $wpc_clients_staff['password_black_list'] );
}
if ( isset( $wpc_clients_staff['password_mixed_case'] ) ) {
    $wpc_password['password_mixed_case'] = !empty( $wpc_clients_staff['password_mixed_case'] ) ? 'yes' : 'no';
    unset( $wpc_clients_staff['password_mixed_case'] );
}
if ( isset( $wpc_clients_staff['password_numeric_digits'] ) ) {
    $wpc_password['password_numeric_digits'] = !empty( $wpc_clients_staff['password_numeric_digits'] ) ? 'yes' : 'no';
    unset( $wpc_clients_staff['password_numeric_digits'] );
}
if ( isset( $wpc_clients_staff['password_special_chars'] ) ) {
    $wpc_password['password_special_chars'] = !empty( $wpc_clients_staff['password_special_chars'] ) ? 'yes' : 'no';
    unset( $wpc_clients_staff['password_special_chars'] );
}

WPC()->settings()->update( $wpc_password, 'password' );


//change terms settings structure and move to another option
$wpc_terms = array();
if ( isset( $wpc_clients_staff['using_terms'] ) ) {
    $wpc_terms['using_terms'] = !empty( $wpc_clients_staff['using_terms'] ) ? $wpc_clients_staff['using_terms'] : 'no';
    unset( $wpc_clients_staff['using_terms'] );
}
if ( isset( $wpc_clients_staff['using_terms_form'] ) ) {
    $wpc_terms['using_terms_form'] = !empty( $wpc_clients_staff['using_terms_form'] ) ? $wpc_clients_staff['using_terms_form'] : '';
    unset( $wpc_clients_staff['using_terms_form'] );
}
if ( isset( $wpc_clients_staff['terms_default_checked'] ) ) {
    $wpc_terms['terms_default_checked'] = !empty( $wpc_clients_staff['terms_default_checked'] ) ? $wpc_clients_staff['terms_default_checked'] : 'no';
    unset( $wpc_clients_staff['terms_default_checked'] );
}
if ( isset( $wpc_clients_staff['terms_text'] ) ) {
    $wpc_terms['terms_text'] = !empty( $wpc_clients_staff['terms_text'] ) ? $wpc_clients_staff['terms_text'] : '';
    unset( $wpc_clients_staff['terms_text'] );
}
if ( isset( $wpc_clients_staff['terms_hyperlink'] ) ) {
    $wpc_terms['terms_hyperlink'] = !empty( $wpc_clients_staff['terms_hyperlink'] ) ? $wpc_clients_staff['terms_hyperlink'] : '';
    unset( $wpc_clients_staff['terms_hyperlink'] );
}
if ( isset( $wpc_clients_staff['terms_notice'] ) ) {
    $wpc_terms['terms_notice'] = !empty( $wpc_clients_staff['terms_notice'] ) ? $wpc_clients_staff['terms_notice'] : '';
    unset( $wpc_clients_staff['terms_notice'] );
}

WPC()->settings()->update( $wpc_terms, 'terms' );



//change captcha settings structure
$wpc_clients_staff['using_captcha_form'] = array();
if ( !empty( $wpc_clients_staff['registration_form_using_captcha'] ) && 'yes' == $wpc_clients_staff['registration_form_using_captcha'] ) {
    $wpc_clients_staff['using_captcha_form'][] = 'registration';
}
if ( !empty( $wpc_clients_staff['login_using_captcha'] ) && 'yes' == $wpc_clients_staff['login_using_captcha'] ) {
    $wpc_clients_staff['using_captcha_form'][] = 'login';
}


//move captcha to another option
$wpc_captcha = array();
if ( isset( $wpc_clients_staff['using_captcha'] ) ) {
    $wpc_captcha['enabled'] = !empty( $wpc_clients_staff['using_captcha'] ) ? $wpc_clients_staff['using_captcha'] : 'no';
    unset( $wpc_clients_staff['using_captcha'] );
}
if ( isset( $wpc_clients_staff['using_captcha_form'] ) ) {
    $wpc_captcha['use_on'] = !empty( $wpc_clients_staff['using_captcha_form'] ) ? $wpc_clients_staff['using_captcha_form'] : '';
    unset( $wpc_clients_staff['using_captcha_form'] );
}
if ( isset( $wpc_clients_staff['captcha_publickey_2'] ) ) {
    $wpc_captcha['publickey_2'] = !empty( $wpc_clients_staff['captcha_publickey_2'] ) ? $wpc_clients_staff['captcha_publickey_2'] : '';
    unset( $wpc_clients_staff['captcha_publickey_2'] );
}
if ( isset( $wpc_clients_staff['captcha_privatekey_2'] ) ) {
    $wpc_captcha['privatekey_2'] = !empty( $wpc_clients_staff['captcha_privatekey_2'] ) ? $wpc_clients_staff['captcha_privatekey_2'] : '';
    unset( $wpc_clients_staff['captcha_privatekey_2'] );
}
if ( isset( $wpc_clients_staff['captcha_theme'] ) ) {
    $wpc_captcha['theme'] = !empty( $wpc_clients_staff['captcha_theme'] ) ? $wpc_clients_staff['captcha_theme'] : '';
    unset( $wpc_clients_staff['captcha_theme'] );
}
if ( isset( $wpc_clients_staff['captcha_version'] ) ) {
    $wpc_captcha['version'] = !empty( $wpc_clients_staff['captcha_version'] ) ? $wpc_clients_staff['captcha_version'] : 'recaptcha_2';
    unset( $wpc_clients_staff['captcha_version'] );
}
if ( isset( $wpc_clients_staff['captcha_publickey'] ) ) {
    $wpc_captcha['publickey'] = !empty( $wpc_clients_staff['captcha_publickey'] ) ? $wpc_clients_staff['captcha_publickey'] : '';
    unset( $wpc_clients_staff['captcha_publickey'] );
}
if ( isset( $wpc_clients_staff['captcha_privatekey'] ) ) {
    $wpc_captcha['privatekey'] = !empty( $wpc_clients_staff['captcha_privatekey'] ) ? $wpc_clients_staff['captcha_privatekey'] : '';
    unset( $wpc_clients_staff['captcha_privatekey'] );
}

WPC()->settings()->update( $wpc_captcha, 'captcha' );


WPC()->settings()->update( $wpc_clients_staff, 'clients_staff' );



//change custom login settings structure
$wpc_custom_login = WPC()->get_settings( 'custom_login' );

$wpc_custom_login['cl_form_border'] = ( !empty( $wpc_custom_login['cl_form_border'] ) && '1' == $wpc_custom_login['cl_form_border'] ) ? 'yes' : 'no';

WPC()->settings()->update( $wpc_custom_login, 'custom_login' );


//change file sharing settings structure
$wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

$wpc_file_sharing['google_doc_embed'] = ( !empty( $wpc_file_sharing['view_type'] ) && 'google_doc' == $wpc_file_sharing['view_type'] ) ? 'yes' : 'no';
$wpc_file_sharing['allow_file_cats'] = ( !isset( $wpc_file_sharing['deny_file_cats'] ) || 'yes' == $wpc_file_sharing['deny_file_cats'] ) ? 'yes' : 'no';
$wpc_file_sharing['admin_uploader_type'] = ( !empty( $wpc_file_sharing['flash_uplader_admin'] ) ) ? $wpc_file_sharing['flash_uplader_admin'] : 'plupload';
$wpc_file_sharing['client_uploader_type'] = ( !empty( $wpc_file_sharing['flash_uplader_client'] ) ) ? $wpc_file_sharing['flash_uplader_client'] : 'plupload';
$wpc_file_sharing['thumbnail_crop'] = ( !empty( $wpc_file_sharing['thumbnail_crop'] ) ) ? 'yes' : 'no';

WPC()->settings()->update( $wpc_file_sharing, 'file_sharing' );


//change limit ips settings structure
$wpc_limit_ips = WPC()->get_settings( 'limit_ips' );

$wpc_limit_ips['ips'] = ( !empty( $wpc_limit_ips['ips'] ) ) ? implode( "\r\n", $wpc_limit_ips['ips'] ) : '';

WPC()->settings()->update( $wpc_limit_ips, 'limit_ips' );