<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


$wpc_gateways = WPC()->get_settings( 'gateways' );
$wpc_invoicing = WPC()->get_settings( 'invoicing' );
$gateways_settings = array();
if( isset( $wpc_gateways['allowed'] ) && is_array( $wpc_gateways['allowed'] ) ) {
    foreach( $wpc_gateways['allowed'] as $value ) {
        $gateways_settings[ $value ] = ( isset( $wpc_invoicing['gateways'] ) && in_array( $value, $wpc_invoicing['gateways'] ) ) ? 1 : 0;
    }
}
if( isset( $wpc_invoicing['gateways'] ) && array_keys( $wpc_invoicing['gateways'] ) === range(0, count( $wpc_invoicing['gateways'] ) - 1) ) {
    $wpc_invoicing['gateways'] = $gateways_settings;
    WPC()->settings()->update( $wpc_invoicing, 'invoicing' );
}