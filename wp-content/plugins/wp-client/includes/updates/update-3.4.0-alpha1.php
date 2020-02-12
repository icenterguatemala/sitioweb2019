<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for customizer

$wpc_client_customize = new WPC_Client_Customize();

$default_style_scheme = $wpc_client_customize->get_style_schemes();
$default_sections = $wpc_client_customize->get_default_sections();

$default_style_scheme['_default_scheme']['key'] = '_default_scheme';

$wpc_client_customize->save_style_settings( $default_style_scheme['_default_scheme'], $default_sections );