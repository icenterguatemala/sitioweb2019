<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//remove old temp file

$uploads            = wp_upload_dir();
$uploads['basedir'] = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] );

$tempDir   = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'wpclient' . DIRECTORY_SEPARATOR . '_file_sharing' . DIRECTORY_SEPARATOR . '_uberloader_temp' . DIRECTORY_SEPARATOR;

if ( is_dir( $tempDir ) ) {
    rmdir( $tempDir );
}