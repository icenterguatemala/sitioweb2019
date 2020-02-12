<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//for old groups with "auto_add_clients" flag
$isset_column = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}wpc_client_groups LIKE 'auto_add_clients' ", ARRAY_A ) ;

if( $isset_column ) {
    $group_ids = $wpdb->get_col("SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_clients = 1 " ) ;
    $wpdb->query( "UPDATE {$wpdb->prefix}wpc_client_groups SET auto_add_manual = 1, auto_add_self = 1 WHERE group_id IN ('" . implode( "','", $group_ids ) . "')" ) ;
    $wpdb->query( "ALTER TABLE {$wpdb->prefix}wpc_client_groups DROP COLUMN auto_add_clients" ) ;
}