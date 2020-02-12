<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for transfer assign data for new logic

$wpc_ez_hub_templates = get_option( 'wpc_ez_hub_templates' );

if ( is_array( $wpc_ez_hub_templates ) && 0 < count( $wpc_ez_hub_templates ) ) {

    foreach( $wpc_ez_hub_templates as $key=>$value ) {
        $value['tabs_content'] = str_replace( 'http://a.wpplugins.org.ua', get_home_url(), $value['tabs_content'] );
        $wpc_ez_hub_templates[$key] = $value;
    }
    update_option( 'wpc_ez_hub_templates', $wpc_ez_hub_templates );
}