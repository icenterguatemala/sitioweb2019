<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$all_inv_and_est = $wpdb->get_results( "SELECT p.ID as id, "
    . "pm1.meta_value as prefix, pm2.meta_value as number, pm0.meta_value as type "
    . "FROM {$wpdb->posts} p "
    . "INNER JOIN {$wpdb->postmeta} pm0 ON ("
    . "p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' "
    . "AND ( pm0.meta_value = 'inv' OR pm0.meta_value = 'est' ) ) "
    . "LEFT JOIN {$wpdb->postmeta} pm1 ON ("
    . "p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_prefix' ) "
    . "LEFT JOIN {$wpdb->postmeta} pm2 ON ("
    . "p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_number' ) "
    . "WHERE p.post_type='wpc_invoice'"
    , ARRAY_A );

if ( count( $all_inv_and_est ) ) {

    $wpc_invoicing = WPC()->get_settings( 'invoicing' );
    $types = array( 'inv', 'est' );
    foreach ( $types as $type ) {
        $count_zeros[ $type ] = 0;
        $ending = ( 'est' == $type ) ? '_est' : '' ;
        if ( ! isset( $wpc_invoicing['display_zeros' . $ending] ) || 'yes' == $wpc_invoicing['display_zeros' . $ending] ) {
            if ( ! isset( $wpc_invoicing['digits_count' . $ending] )
                || !is_numeric( $wpc_invoicing['digits_count' . $ending] )
                || 3 > $wpc_invoicing['digits_count' . $ending] ) {
                $count_zeros[ $type ] = 8;
            } else {
                $count_zeros[ $type ] = $wpc_invoicing['digits_count' . $ending];
            }
        }
    }

    foreach ( $all_inv_and_est as $item ) {
        $new_number = $item['prefix']
            . str_pad( $item['number'], $count_zeros[ $item['type'] ], '0', STR_PAD_LEFT );

        update_post_meta( $item['id'], 'wpc_inv_number', $new_number );
    }

}

delete_post_meta_by_key('wpc_inv_prefix') ;