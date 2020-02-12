<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for very old installs

$show_link = get_option( 'wpc_show_link' );
$link_text = get_option( 'wpc_link_text' );

if ( !empty( $show_link ) && 'yes' == $show_link ) {
    $wpc_general = WPC()->get_settings( 'general' );
    $wpc_general['show_hub_link'] = 'yes';

    if ( !empty( $link_text ) ) {
        $wpc_general['show_hub_link'] = $link_text;
    }

    WPC()->settings()->update( $wpc_general, 'general' );
}