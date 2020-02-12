<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$wpdb->update(
    $wpdb->postmeta,
    array( 'meta_key' => 'wpc_inv_comment' ),
    array( 'meta_key' => 'wpc_inv_declined_note' )
);