<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ),
    array( 'post_type' => 'hubpage', 'post_status' => 'trash' ) );

$files_shortcodes_array = array(
    'wpc_client_fileslu_tree',
    'wpc_client_filesla_tree',
);

foreach( $files_shortcodes_array as $key ) {
    $template = WPC()->get_settings( 'shortcode_template_' . $key );
    WPC()->settings()->update( $template, 'temp_sh_' . $key . '_back' );
    delete_option( 'wpc_shortcode_template_' . $key );
}