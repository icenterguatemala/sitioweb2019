<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$template = WPC()->get_settings( 'shortcode_template_wpc_client_inv_inv_page' );
if ( ! empty( $template ) ) {
    WPC()->settings()->update( $template, 'temp_sh_wpc_client_inv_inv_page_back' );
    WPC()->delete_settings( 'shortcode_template_wpc_client_inv_inv_page' );
}