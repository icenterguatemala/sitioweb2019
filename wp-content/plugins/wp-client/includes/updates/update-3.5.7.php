<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_currency = WPC()->get_settings( 'currency' );

foreach( $wpc_currency as $k=>$val ) {
    if ( ( $val['code'] == 'USD' && $k != substr( md5('USD'), 0, 13) ) ||
        ( $val['code'] == 'EUR' && $k != substr( md5('EUR'), 0, 13) )
    ) {
        unset( $wpc_currency[ $k ] );
    }
}

update_option( 'wpc_currency', $wpc_currency );