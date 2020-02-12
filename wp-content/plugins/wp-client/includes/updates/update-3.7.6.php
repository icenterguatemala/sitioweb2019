<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$smtp = WPC()->get_settings( 'smtp' );
$email_sending = array();
if( isset( $smtp['enable_smtp'] ) && $smtp['enable_smtp'] ) {
    $email_sending = array(
        'type' => 'smtp',
        'smtp' => array(
            'host' => isset( $smtp['smtp_host'] ) ? $smtp['smtp_host'] : '',
            'secure' => isset( $smtp['secure_prefix'] ) ? $smtp['secure_prefix'] : '',
            'port' => isset( $smtp['smtp_port'] ) ? $smtp['smtp_port'] : '',
            'username' => isset( $smtp['smtp_username'] ) ? $smtp['smtp_username'] : '',
            'password' => isset( $smtp['smtp_password'] ) ? $smtp['smtp_password'] : '',
            'sender' => isset( $smtp['sender'] ) ? $smtp['sender'] : '',
        )
    );
} else {
    $email_sending['type'] = '';
}
WPC()->settings()->update( $email_sending, 'email_sending' );