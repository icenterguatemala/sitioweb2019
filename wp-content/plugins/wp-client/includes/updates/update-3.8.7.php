<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
foreach( $wpc_custom_fields as $key=>$val ) {
    if( $val['type'] == 'datepicker' ) {
        $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->usermeta} WHERE meta_key = '$key'", ARRAY_A );
        foreach( $fields as $meta_row ) {
            $timestamp = WPC_Deprecated::convert_to_time( $meta_row['meta_value'], 'Y-m-d' );
            if( empty( $timestamp ) || $timestamp === false ) {
                $timestamp = '';
            }
            update_user_meta( $meta_row['user_id'], $key . '_wpc_backup', $meta_row['meta_value'] );
            update_user_meta( $meta_row['user_id'], $key, $timestamp );
        }
    }
}