<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$old_settings = WPC()->get_settings( 'private_post_types' );

$old_settings = ( is_array( $old_settings ) && count( $old_settings ) ) ? $old_settings : array();

$new_settings = array(
    'action'    => 'redirect',
    'types'     => $old_settings
);

update_option( 'wpc_private_post_types', $new_settings );