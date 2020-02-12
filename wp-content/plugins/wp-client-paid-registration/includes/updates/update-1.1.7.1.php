<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wpc_gateways = WPC()->get_settings( 'gateways' );
$wpc_paid_registration = WPC()->get_settings( 'paid_registration' );
$gateways_settings = array();
if ( isset( $wpc_gateways['allowed'] ) && is_array( $wpc_gateways['allowed'] ) ) {
    foreach ( $wpc_gateways['allowed'] as $value ) {
        $gateways_settings[ $value ] = ( isset( $wpc_paid_registration['gateways'] ) && in_array( $value, $wpc_paid_registration['gateways'] ) ) ? 1 : 0;
    }
}

if ( isset( $wpc_paid_registration['gateways'] ) && array_keys( $wpc_paid_registration['gateways'] ) === range( 0, count( $wpc_paid_registration['gateways'] ) - 1 ) ) {
    $wpc_paid_registration['gateways'] = $gateways_settings;
    WPC()->settings()->update( $wpc_paid_registration, 'paid_registration' );
}