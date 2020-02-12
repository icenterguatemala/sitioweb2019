<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for advanced locations
$general = WPC()->get_settings( 'general' );

$advanced_locations = array();
if ( isset( $general['show_custom_menu'] ) && 'no' !== $general['show_custom_menu'] ) {
    if ( isset( $general['custom_menu_logged_in'] ) ) {
        $advanced_locations['login'] = $general['custom_menu_logged_in'];
    }
    if ( isset( $general['custom_menu_logged_out'] ) ) {
        $advanced_locations['logout'] = $general['custom_menu_logged_out'];
    }
}
unset(
    $general['show_custom_menu'],
    $general['custom_menu_logged_in'],
    $general['custom_menu_logged_out']
);

WPC()->settings()->update( $general, 'general' );
WPC()->settings()->update( $advanced_locations, 'advanced_locations' );
update_option('wpc_notification_moved_menu_settings', true);