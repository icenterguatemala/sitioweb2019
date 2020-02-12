<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//update file sharing options

$wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

if ( isset( $wpc_file_sharing['flash_uplader_admin'] ) && 'yes' == $wpc_file_sharing['flash_uplader_admin'] ) {
    $wpc_file_sharing['flash_uplader_admin'] = 'html5';
} else {
    $wpc_file_sharing['flash_uplader_admin'] = 'regular';
}

if ( isset( $wpc_file_sharing['flash_uplader_client'] ) && 'yes' == $wpc_file_sharing['flash_uplader_client'] ) {
    $wpc_file_sharing['flash_uplader_client'] = 'html5';
} else {
    $wpc_file_sharing['flash_uplader_client'] = 'regular';
}

update_option( 'wpc_file_sharing', $wpc_file_sharing );