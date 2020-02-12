<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$invoices = $wpdb->get_results(
    "SELECT p.ID as id, p.post_title as title, p.post_date as date
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_parrent_id' )
    WHERE p.post_type='wpc_invoice' AND pm1.meta_value IS NOT NULL",
ARRAY_A );

$pattern = '/%CreationDateFormat="([^"]*)"%/';
foreach ( $invoices as $invoice ) {
    $InvoiceDate = preg_match($pattern, $invoice['title'], $format);

    if ( $InvoiceDate ) {
        $format = ! empty($format[1]) ? $format[1] : '';
        $date = ! empty( $invoice['date'] ) ? WPC()->date_format( strtotime( $invoice['date'] ), 'date', $format ) : '';
        $title = preg_replace( $pattern, $date, $invoice['title'] );
        $wpdb->update( $wpdb->posts, array( 'post_title' => $title ), array( 'ID' => $invoice['id'] ), array('%s'), array('%d') );
    }

}