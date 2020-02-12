<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

if ( !ini_get( 'safe_mode' ) ) {
    @set_time_limit(0);
}

$zero_files = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_files WHERE size = '0'", ARRAY_A );
if( isset( $zero_files ) && !empty( $zero_files ) ) {
    foreach( $zero_files as $file ) {

        $filepath = WPC()->files()->get_file_path( $file );

        if( !file_exists( $filepath ) ) {
            $wpdb->update(
                "{$wpdb->prefix}wpc_client_files",
                array(
                    'external'  => '1'
                ),
                array(
                    'id'    => $file['id']
                )
            );
        }
    }
}