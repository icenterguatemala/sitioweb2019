<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$templates = $wpdb->get_results( $wpdb->prepare(
    "SELECT * 
    FROM {$wpdb->options}
    WHERE option_name LIKE %s",
    "wpc_shortcode_template_wpc_client_%"
), ARRAY_A );

if ( ! empty( $templates ) ) {
    //for TPL archive notice
    $new_notices['tpl_archive_notice'] =
        'All templates were updated. You can download your previously edited templates <a target="_blank" href="' . add_query_arg( array( 'wpc_action' => 'download_tpl' ), admin_url( 'admin.php' ) ) . '">HERE</a>.' .
        '<br> See more details <a target="_blank" href="https://wp-client.com/wp-client-v-4-4-0/">here</a>.';

    WPC()->admin()->add_wpc_notices( $new_notices );
}



//change custom login settings structure and move to another option
$wpc_custom_login = WPC()->get_settings( 'custom_login' );

$wpc_common_secure = array();
if ( isset( $wpc_custom_login['cl_hide_site'] ) ) {
    $wpc_common_secure['hide_site'] = $wpc_custom_login['cl_hide_site'];
}

if ( isset( $wpc_custom_login['cl_pages_white_list'] ) ) {
    $wpc_common_secure['pages_white_list'] = $wpc_custom_login['cl_pages_white_list'];
}

if ( isset( $wpc_custom_login['cl_hide_admin'] ) ) {
    $wpc_common_secure['hide_admin'] = $wpc_custom_login['cl_hide_admin'];
}

if ( isset( $wpc_custom_login['cl_login_url'] ) ) {
    $wpc_common_secure['login_url'] = $wpc_custom_login['cl_login_url'];
}

WPC()->settings()->update( $wpc_common_secure, 'common_secure' );