<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//create folder "_file_sharing" in basedir/wpclient

$wpdb->update(
    "{$wpdb->prefix}wpc_client_file_categories",
    array( 'parent_id' => '0' ),
    array( 'parent_id' => '-1' )
);


$old_filedir = WPC()->get_upload_dir( 'wpclient/' );

$all_categories = $wpdb->get_results(
    "SELECT cat_id,
            cat_name
    FROM {$wpdb->prefix}wpc_client_file_categories",
ARRAY_A );

if ( isset( $all_categories ) && !empty( $all_categories ) ) {
    foreach( $all_categories as $category ) {

        //creating folders for categories
        WPC()->files()->old_create_file_category_folder( $category['cat_id'], $category['cat_name'] );

        //move files from main folder
        $files = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM
            {$wpdb->prefix}wpc_client_files
            WHERE cat_id = %d",
            $category['cat_id']
        ), ARRAY_A );

        if ( isset( $files ) && !empty( $files ) ) {
            foreach( $files as $file ) {
                if ( file_exists( $old_filedir . $file['filename'] ) ) {
                    $new_filepath = WPC()->files()->old_get_file_path( $file );
                    rename( $old_filedir . $file['filename'], $new_filepath );
                }

                //if file is image transfer it thumbnail
                $filedata_array = explode( ".", $file['name'] );
                $ext = ( is_array( $filedata_array ) ) ? end( $filedata_array ) : '';
                if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ) ) ) {
                    if ( file_exists( $old_filedir . 'thumbnails_' . $file['filename'] ) ) {
                        $new_filepath = WPC()->files()->old_get_file_path( $file, true );
                        rename( $old_filedir . 'thumbnails_' . $file['filename'], $new_filepath );
                    }
                }
            }
        }
    }
}