<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//remove files_download_log for deleted owners and files
$file_ids = $wpdb->get_col(
    "SELECT id FROM {$wpdb->prefix}wpc_client_files"
);

$args = array(
    'blog_id'      => get_current_blog_id(),
    'fields'       => 'ID',
);

$user_ids = get_users( $args );

$wpdb->query(
    "DELETE
    FROM {$wpdb->prefix}wpc_client_files_download_log
    WHERE file_id NOT IN('" . implode( "','", $file_ids ) . "') OR
        client_id NOT IN('" . implode( "','", $user_ids ) . "')"
);