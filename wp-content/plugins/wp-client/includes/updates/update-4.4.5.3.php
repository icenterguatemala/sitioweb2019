<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wpc_caps = WPC()->get_settings( 'capabilities' );

if ( ! empty( $wpc_caps['wpc_manager'] ) ) {

    if ( isset( $wpc_caps['wpc_manager']['read_portalhub'] ) ) {
        $wpc_caps['wpc_manager']['wpc_view_portalhubs'] = $wpc_caps['wpc_manager']['read_portalhub'];
        unset( $wpc_caps['wpc_manager']['read_portalhub'] );
    }

    if ( isset( $wpc_caps['wpc_manager']['edit_portalhub'] ) ) {
        $wpc_caps['wpc_manager']['wpc_edit_portalhub'] = $wpc_caps['wpc_manager']['edit_portalhub'];
        unset( $wpc_caps['wpc_manager']['edit_portalhub'] );
    }

    WPC()->settings()->update( $wpc_caps, 'capabilities' );
}