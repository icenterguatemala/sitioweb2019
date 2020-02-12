<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//change "wpc_add_media" capability to "upload_files" capability
global $wpdb;

//for individual capabilities of wpc_client
$capabilities = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}usermeta "
    . "WHERE meta_key = '{$wpdb->prefix}capabilities'"
    . "AND meta_value LIKE '%wpc_client%'"
    . "AND meta_value LIKE '%wpc_add_media%'", ARRAY_A );
$new_caps = array();
foreach ( $capabilities as $k => $v ) {
    if ( empty( $v['user_id'] ) || empty( $v['meta_value'] ) )
        continue;
    $meta_value = unserialize( $v['meta_value'] );
    if ( !isset( $meta_value['wpc_add_media'] ) )
        continue;
    $meta_value['upload_files'] =  $meta_value['wpc_add_media'];
    unset( $meta_value['wpc_add_media'] );

    $new_caps[ $v['user_id'] ] = $meta_value;
}
foreach ($new_caps as $user_id => $meta_value ) {
    update_user_meta( $user_id, $wpdb->prefix . 'capabilities', $meta_value);
}

//for role "wpc_client"
$role = get_role('wpc_client');
if( isset( $role ) && !empty( $role ) ) {
    $caps = $role->capabilities;
    $add_media = ( !empty( $caps['wpc_add_media'] ) ) ? true : false;
    $role->remove_cap( 'wpc_add_media' );
    $role->add_cap( 'upload_files', $add_media );
}


$templates_shortcodes = WPC()->get_settings( 'templates_shortcodes' );

if( !empty( $templates_shortcodes ) ) {

    $files_shortcodes_array = array(
        'wpc_client_fileslu',
        'wpc_client_filesla',
        'wpc_client_fileslu_blog',
        'wpc_client_filesla_blog',
        'wpc_client_fileslu_table',
        'wpc_client_filesla_table',
    );

    foreach( $templates_shortcodes as $key=>$templates_shortcode ) {
        if( in_array( $key, $files_shortcodes_array ) ) {
            WPC()->settings()->update( $templates_shortcode, 'temp_sh_' . $key . '_back' );
        } else {
            WPC()->settings()->update( $templates_shortcode, 'shortcode_template_' . $key );
        }
    }

    delete_option( 'wpc_templates_shortcodes' );
}