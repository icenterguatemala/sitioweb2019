<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

//assign for feedback_wizard
$feedback_wizards = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_wizards", ARRAY_A );
if ( ! empty( $feedback_wizards ) ) {
    foreach ( $feedback_wizards as $feedback_wizard ) {
        $feedback_wizard_id = $feedback_wizard['cat_id'];

        $client_ids = array();
        if ( ! empty( $feedback_wizard['clients_id'] ) ) {
            $client_ids = explode( ',', str_replace( '#', '', $feedback_wizard['clients_id'] ) );
            unset( $client_ids[count( $client_ids ) - 1] );
        }
        WPC()->assigns()->set_assigned_data( 'feedback_wizard', $feedback_wizard_id, 'client', $client_ids );


        $group_ids = array();
        if ( ! empty( $feedback_wizard['groups_id'] ) ) {
            $group_ids = explode( ',', str_replace( '#', '', $feedback_wizard['groups_id'] ) );
            unset( $group_ids[count( $group_ids ) - 1] );
        }
        WPC()->assigns()->set_assigned_data( 'feedback_wizard', $feedback_wizard_id, 'circle', $group_ids );
    }
}