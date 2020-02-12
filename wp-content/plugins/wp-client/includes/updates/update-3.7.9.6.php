<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_email = WPC()->get_settings( 'email_sending' );

$wpc_email['sender_name']   = get_option( 'sender_name' );
$wpc_email['sender_email']  = get_option( 'sender_email' );
$wpc_email['reply_email']   = get_option( 'wpc_reply_email' );

if ( isset( $wpc_email['smtp']['sender'] ) && isset( $wpc_email['type'] ) && 'smtp' == $wpc_email['type'] ) {
    $wpc_email['sender_email'] = $wpc_email['smtp']['sender'];
}

WPC()->settings()->update( $wpc_email, 'email_sending' );