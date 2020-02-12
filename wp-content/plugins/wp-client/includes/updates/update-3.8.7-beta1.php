<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$pages_shortcodes_array = array(
    'wpc_client_pagel_tree',
    'wpc_client_pagel',
);

foreach( $pages_shortcodes_array as $key ) {
    $template = WPC()->get_settings( 'shortcode_template_' . $key );
    WPC()->settings()->update( $template, 'temp_sh_' . $key . '_back' );
    delete_option( 'wpc_shortcode_template_' . $key );
}