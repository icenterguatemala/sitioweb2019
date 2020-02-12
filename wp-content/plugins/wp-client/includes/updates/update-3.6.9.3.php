<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_gateways = WPC()->get_settings( 'gateways' );

if ( isset( $wpc_gateways['paypal-express'] ) && !isset( $wpc_gateways['paypal-express']['allow_recurring'] ) ) {
    $wpc_gateways['paypal-express']['allow_recurring'] = 1;

    WPC()->settings()->update( $wpc_gateways, 'gateways' );
}