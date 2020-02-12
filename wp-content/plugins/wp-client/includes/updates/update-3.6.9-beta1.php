<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for partition HUB template
$wpc_ez_hub_templates = WPC()->get_settings( 'ez_hub_templates' );

$target_path = WPC()->get_upload_dir( 'wpclient/_hub_templates/' );

if ( is_dir( $target_path ) ) {
    foreach( $wpc_ez_hub_templates as $key => $template ) {
        if( !isset( $template['content'] ) || !isset( $template['tabs_content'] ) )
            continue;

        $content = $template['content'] ;
        $tabs_content = $template['tabs_content'] ;
        unset( $wpc_ez_hub_templates[ $key ]['content'] ) ;
        unset( $wpc_ez_hub_templates[ $key ]['tabs_content'] ) ;

        if ( 'default' == $key ) {
            $tmp_id = time();
            $wpc_ez_hub_templates[ $tmp_id ] = $wpc_ez_hub_templates[ $key ] ;
            $wpc_ez_hub_templates[ $tmp_id ]['not_delete'] = true ;
            unset( $wpc_ez_hub_templates[ $key ] ) ;
            $wpc_ez_hub_default = get_option( 'wpc_ez_hub_default' );
            update_option( 'wpc_ez_hub_' . $tmp_id, $wpc_ez_hub_default );
            delete_option( 'wpc_ez_hub_default' );
        } else {
            $tmp_id = $key;
        }

        $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
        fwrite( $content_file, $content );
        fclose( $content_file );

        $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
        fwrite( $tabs_content_file, $tabs_content );
        fclose( $tabs_content_file );
    }
    WPC()->settings()->update( $wpc_ez_hub_templates, 'ez_hub_templates' );
}

//for changed logic with FTP sync and Folders names
$uploads_dir = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );
wpc_recursive_update_folders_names( 0, $uploads_dir );


function wpc_recursive_update_folders_names( $parent_id, $uploads_dir ) {
    global $wpdb;

    $file_categories = $wpdb->get_results(
        "SELECT *
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE parent_id='{$parent_id}'",
        ARRAY_A );

    if ( ! empty( $file_categories ) ) {
        foreach ( $file_categories as $file_category ) {

            $parent_category_ids = WPC()->files()->get_category_parent_ids( $file_category['cat_id'] );

            $categorypath = '';

            if( is_array( $parent_category_ids ) && 0 < count( $parent_category_ids ) ) {

                foreach( $parent_category_ids as $parent_category_id ) {

                    $current_folder_name = $wpdb->get_var(
                        "SELECT folder_name
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_id='$parent_category_id'"
                    );

                    $categorypath .= $current_folder_name . DIRECTORY_SEPARATOR;

                }
            }

            $category_old_path = $uploads_dir . $categorypath . $file_category['cat_name'] . '__' . $file_category['cat_id'];
            $category_old_path2 = $uploads_dir . $categorypath . strtolower( $file_category['cat_name'] ) . '__' . $file_category['cat_id'];
            $category_new_path = $uploads_dir . $categorypath . $file_category['cat_name'];

            if( is_dir( $category_old_path ) ) {
                rename( $category_old_path, $category_new_path );
                $wpdb->update(
                    "{$wpdb->prefix}wpc_client_file_categories",
                    array(
                        'folder_name'   =>  $file_category['cat_name'],
                    ),
                    array(
                        'cat_id'    => $file_category['cat_id']
                    )
                );
            } elseif( is_dir( $category_old_path2 ) ) {
                rename( $category_old_path2, $category_new_path );
                $wpdb->update(
                    "{$wpdb->prefix}wpc_client_file_categories",
                    array(
                        'folder_name'   =>  $file_category['cat_name'],
                    ),
                    array(
                        'cat_id'    => $file_category['cat_id']
                    )
                );
            }
            wpc_recursive_update_folders_names( $file_category['cat_id'], $uploads_dir );
        }

    }

}