<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
if( isset( $wpc_clients_staff['registration_using_captcha'] ) && 'yes' == $wpc_clients_staff['registration_using_captcha'] ) {
    $wpc_clients_staff['using_captcha'] = 'yes';
    $wpc_clients_staff['registration_form_using_captcha'] = 'yes';
    WPC()->settings()->update( $wpc_clients_staff, 'clients_staff' );
}

$wpdb->delete(
    "{$wpdb->prefix}wpc_client_objects_assigns",
    array( 'assign_id' => '0' )
);