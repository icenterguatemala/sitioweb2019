<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$private_posts = $wpdb->get_col(
    "SELECT p.ID
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id
    WHERE pm1.meta_key = '_wpc_protected' AND
          pm1.meta_value = '1'"
);

if( !empty( $private_posts ) ) {
    foreach( $private_posts as $private_post ) {

        $id_array = get_post_meta( $private_post, 'user_ids', true );
        if( !is_array( $id_array ) ) {
            $id_array = array();
        }

        if( !empty( $id_array ) ) {
            WPC()->assigns()->set_assigned_data( 'private_post', $private_post, 'client', $id_array );
        }

        $id_array = get_post_meta( $private_post, 'groups_id', true );
        if( !is_array( $id_array ) ) {
            $id_array = array();
        }

        if( !empty( $id_array ) ) {
            WPC()->assigns()->set_assigned_data( 'private_post', $private_post, 'circle', $id_array );
        }
    }
}