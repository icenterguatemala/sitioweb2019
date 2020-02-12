<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//merge "General" File Category

$isset_general = $wpdb->get_col( "SELECT cat_id FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_name='General' ORDER BY cat_id" );
if ( $isset_general && 1 < count( $isset_general ) ) {
    $new_general = array_shift( $isset_general );
    foreach ( $isset_general as $general_id ) {
        $wpdb->update( $wpdb->prefix . 'wpc_client_files', array( 'cat_id' => $new_general ), array( 'cat_id' => $general_id ) );

        $assign_ids = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='file_category' AND object_id='$general_id' AND assign_type='client'" );
        foreach ( $assign_ids as $assign_id ) {
            $isset_assign = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='file_category' AND object_id='$new_general' AND assign_type='client' AND assign_id='$assign_id'" );
            if ( !$isset_assign ) {
                $wpdb->insert( $wpdb->prefix . 'wpc_client_objects_assigns', array( 'object_type' => 'file_category', 'object_id' => $new_general, 'assign_type' => 'client', 'assign_id' => $assign_id ));
            }
        }

        $assign_ids = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='file_category' AND object_id='$general_id' AND assign_type='circle'" );
        foreach ( $assign_ids as $assign_id ) {
            $isset_assign = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='file_category' AND object_id='$new_general' AND assign_type='circle' AND assign_id='$assign_id'" );
            if ( !$isset_assign ) {
                $wpdb->insert( $wpdb->prefix . 'wpc_client_objects_assigns', array( 'object_type' => 'file_category', 'object_id' => $new_general, 'assign_type' => 'circle', 'assign_id' => $assign_id ));
            }
        }

        $wpdb->delete( $wpdb->prefix . 'wpc_client_objects_assigns', array( 'object_type' => 'file_category', 'object_id' => $general_id ) );
        $wpdb->delete( $wpdb->prefix . 'wpc_client_file_categories', array( 'cat_id' => $general_id ) );
    }
}