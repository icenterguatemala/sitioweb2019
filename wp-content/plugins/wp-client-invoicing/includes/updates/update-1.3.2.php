<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$all['inv']            = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'inv' " );
$all['est']            = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'est' " );
$all['accum_inv']      = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'accum_inv' " );
$all['repeat_inv']     = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'repeat_inv' " );
foreach ( $all as $type => $ids ) {
    foreach ( $ids as $id ) {
        update_post_meta( $id, 'wpc_inv_post_type', $type ) ;
    }
}
$all_inv_posts = array_merge( $all['inv'], $all['est'], $all['accum_inv'], $all['repeat_inv'] ) ;
$wpdb->query( "UPDATE {$wpdb->posts} SET post_type = 'wpc_invoice' WHERE ID IN ('" . implode( "','", $all_inv_posts ) . "')" ) ;