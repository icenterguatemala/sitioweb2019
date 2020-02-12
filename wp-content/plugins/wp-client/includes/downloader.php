<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb, $wp_query;
@ignore_user_abort(true);
@set_time_limit(0);

/*
* for download external files by chunks
*/
function wpc_download_curl_params( $handle, $r, $url ) {
    $schema = is_ssl() ? 'https://' : 'http://';
    $ref_link = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    curl_setopt( $handle, CURLOPT_WRITEFUNCTION, 'wpc_stream_echo' );
    curl_setopt( $handle, CURLOPT_REFERER, $ref_link );
}

/*
* download external files by chunks
*/
function wpc_stream_echo( $handle, $data ) {
    $data_length = strlen( $data );

    $max_body_length = 1024 * 1024;

    if ( $max_body_length &&  $data_length > $max_body_length )
        $data = substr( $data, 0, ( $max_body_length - $data_length ) );

        $bytes_written = strlen( $data );

        echo $data;

    return $bytes_written;
}


/*
* Downloader
*/
$hash_text = false;
if( !is_admin() && $hash_text = get_query_var('wpc_google_hash') ) {
    $hash_array = explode( '_', $hash_text );
    $action = 'view';
    $hash = !empty( $hash_array[0] ) ? $hash_array[0] : '';
    $id = !empty( $hash_array[1] ) ? $hash_array[1] : 0;
    $user_id = !empty( $hash_array[2] ) ? $hash_array[2] : 0;
    //var_dump( $id , $user_id  , date('Y-m-d') ); exit;
    if( $hash != md5( $id . NONCE_SALT . $user_id  . NONCE_SALT . date('Y-m-d') ) ) {
        /*our_hook_
        hook_name: wpc_client_download_inaccessible
        hook_title: File Download Inaccessible
        hook_description: Hook runs when User haven't access to download current file.
        hook_type: action
        hook_in: wp-client
        hook_location downloader.php
        hook_param:
        hook_since: 4.1.3
        */
        do_action( 'wpc_client_download_inaccessible' );
        exit( __( 'You do not have access to this file!', WPC_CLIENT_TEXT_DOMAIN ) );
    }
} else {
    if( empty( $id ) ) exit( __( 'Wrong File ID', WPC_CLIENT_TEXT_DOMAIN ) );

    if( !( is_numeric( $id ) && (int)$id > 0 ) ) {
        $id_array = explode( '.', $id );
        $id = $id_array[0];
    }
}

if ( (int)$id <= 0 ) {
    exit( __( 'Invalid file. Please try downloading again!', WPC_CLIENT_TEXT_DOMAIN ) );
}

$line = $wpdb->get_row( $wpdb->prepare(
    "SELECT *
    FROM {$wpdb->prefix}wpc_client_files
    WHERE id = %d",
    $id
), ARRAY_A );

if ( count( $line ) == 0 ) {
    die( __( 'Invalid file. Please try downloading again!', WPC_CLIENT_TEXT_DOMAIN ) );
}

$access         = false;
$admins_notify  = false;
$thumbnail      = !empty( $_GET['thumbnail'] );
if( !$hash_text ) {
    $action = !empty( $_GET['wpc_action'] ) ? $_GET['wpc_action'] : 'download';

    if ( is_user_logged_in() ) {

        if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) )
            $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
        else
            $user_id = get_current_user_id();

        //checking access for file
        if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $access = true;
        } elseif ( current_user_can( 'wpc_manager' ) ) {
            if( !empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], $user_id . AUTH_KEY . $id ) ) {
                $access = true;
            } else {
                /*our_hook_
                hook_name: wpc_client_download_inaccessible
                hook_title: File Download Inaccessible
                hook_description: Hook runs when User haven't access to download current file.
                hook_type: action
                hook_in: wp-client
                hook_location downloader.php
                hook_param:
                hook_since: 4.1.3
                */
                do_action( 'wpc_client_download_inaccessible' );
                die( __( 'You do not have access to this file!', WPC_CLIENT_TEXT_DOMAIN ) );
            }
        } elseif ( $line['user_id'] == $user_id ) {
            //access for file owner
            $access         = true;
            $admins_notify  = true;
        } else {
        	if ( current_user_can( 'wpc_client' ) && !current_user_can( 'manage_network_options' ) ) {
        		$staff_ids = WPC()->members()->get_client_staff_ids( $user_id );
        		if( in_array( $line['user_id'], $staff_ids ) ) {
        			$access         = true;
                    $admins_notify  = true;
		        }
	        }
            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
            $file_ids = array();
            //Files from categories with clients access
            $client_file_caregories = '';

            $client_file_caregories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $user_id );

            //if nested assigns turn ON
            if( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
                $temp_cat_array = array();
                foreach( $client_file_caregories as $file_category ) {
                    $children_categories = WPC()->files()->get_category_children_ids( $file_category );
                    $temp_cat_array = array_merge( $temp_cat_array, $children_categories );
                }
                $client_file_caregories = array_merge( $client_file_caregories, $temp_cat_array );
            }

            $client_file_caregories = " f.cat_id IN('" . implode( "','", $client_file_caregories ) . "')";

            $results = $wpdb->get_col(
                "SELECT f.id
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE " . $client_file_caregories
            );

            if ( 0 < count( $results ) )
                $file_ids = array_merge( $file_ids, $results );


            //Files with clients access
            $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $user_id );
            $client_files = " f.id IN('" . implode( "','", $client_files ) . "')";

            $results = $wpdb->get_col(
                "SELECT f.id
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE " . $client_files
            );

            if ( 0 < count( $results ) )
                $file_ids = array_merge( $file_ids, $results );

            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach( $client_groups_id as $group_id ) {
                    //Files in categories with group access
                    $group_file_caregories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $group_id );

                    //if nested assigns turn ON
                    if( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
                        $temp_cat_array = array();
                        foreach( $group_file_caregories as $file_category ) {
                            $children_categories = WPC()->files()->get_category_children_ids( $file_category );
                            $temp_cat_array = array_merge( $temp_cat_array, $children_categories );
                        }
                        $group_file_caregories = array_merge( $group_file_caregories, $temp_cat_array );
                    }

                    $group_file_caregories = " f.cat_id IN('" . implode( "','", $group_file_caregories ) . "')";

                    $results = $wpdb->get_col(
                        "SELECT f.id
                        FROM {$wpdb->prefix}wpc_client_files f
                        WHERE " . $group_file_caregories
                    );

                    if ( 0 < count( $results ) )
                        $file_ids = array_merge( $file_ids, $results );

                    //Files with group access
                    $group_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $group_id );
                    $group_files = " f.id IN('" . implode( "','", $group_files ) . "')";
                    $results = $wpdb->get_col(
                        "SELECT f.id
                        FROM {$wpdb->prefix}wpc_client_files f
                        WHERE " . $group_files
                    );

                    if ( 0 < count( $results ) )
                        $file_ids = array_merge( $file_ids, $results );
                }
            }

            $file_ids = array_unique( $file_ids );

            if( in_array( $id, $file_ids ) ) {
                $access         = true;
                $admins_notify  = true;
            }
        }

    }

    if ( !$access ) {
        /*our_hook_
        hook_name: wpc_client_download_inaccessible
        hook_title: File Download Inaccessible
        hook_description: Hook runs when User haven't access to download current file.
        hook_type: action
        hook_in: wp-client
        hook_location downloader.php
        hook_param:
        hook_since: 4.1.3
        */
        do_action( 'wpc_client_download_inaccessible' );
        exit( __( 'You do not have access to this file!', WPC_CLIENT_TEXT_DOMAIN ) );
    }
}

if( !$line['external'] ) {
    //for files on server

    if( $action == 'view' && $thumbnail ) {
        $target_path = WPC()->files()->get_file_path( $line, $thumbnail );
    } else {
        $target_path = WPC()->files()->get_file_path( $line );
    }

    if( !file_exists( $target_path ) ) {
        exit( __( 'File does not exist', WPC_CLIENT_TEXT_DOMAIN ) );
    }

    $fsize = $line['size'];

} else {
    //for external
    $target_path = $line['filename'];
    $headers = get_headers( $target_path, 1 );
    $fsize = !empty( $headers['Content-Length'] ) ? $headers['Content-Length'] : 0;
}

//set last download
if( $action == 'download' && !$thumbnail ) {
    $wpdb->update(
        "{$wpdb->prefix}wpc_client_files",
        array(
            'last_download' => time()
        ),
        array(
            'id' => $id
        )
    );

    //update download_log
    $wpdb->insert(
        "{$wpdb->prefix}wpc_client_files_download_log",
        array(
            'file_id' => $id,
            'client_id' => $user_id,
            'download_date' => time()
        )
    );
}


$ext = '';
if( $line['external'] ) {
    $path_parts = pathinfo( $line['filename'] );
    $ext = isset( $path_parts['extension'] ) ? strtolower( $path_parts['extension'] ) : '';
    if( !empty( $ext ) ) {
        $line['name'] = $line['name'] . '.' . $ext;
    }
}
if( empty( $ext ) || !$line['external'] ) {
    $path_parts = pathinfo( $line['name'] );
    $ext = isset( $path_parts['extension'] ) ? strtolower( $path_parts['extension'] ) : '';
    if( in_array( $ext, WPC()->files()->file_video_formats ) && $action == 'view' ) {
        $action = 'play';
        $begin=0;
        $end = $length = $fsize;
        if(isset($_SERVER['HTTP_RANGE'])) {
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            $range  = explode('-', $range);
            $begin = $range[0];
            $end   = ( isset($range[1] ) && is_numeric( $range[1] ) ) ? $range[1] : $fsize;
            header('HTTP/1.1 206 Partial Content');
            $length = $end - $begin;
            header("Content-Range: bytes $begin-" . ( (int)$begin + (int)$length - 1 ) . "/$fsize");
        } else {
            header('HTTP/1.1 200 OK');
            header('Accept-Ranges: bytes');
        }
        header('Content-Length:' . $length );
        header('Etag:' . md5( $path ) );
    }
}

if( !$hash_text ) {
    $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
    if( isset( $wpc_file_sharing['google_doc_embed'] ) && 'yes' == $wpc_file_sharing['google_doc_embed'] && 'view' == $action  ) {
        $formats = array_keys( WPC()->files()->files_for_google_doc_view );
        if( in_array( $ext, $formats ) ) {
            WPC()->files()->generate_google_view( $id, $ext );
        }
    }
}

if( $action != 'play' ) {
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Robots: none");
    header("Content-Description: File Transfer");
    header("Content-Transfer-Encoding: binary");
}

$mime_types = array_merge( wp_get_mime_types(), WPC()->files()->files_for_google_doc_view );
$extensions = array_keys( $mime_types );

$content_type = '';
foreach( $extensions as $_extension ) {
    if ( preg_match( "/{$ext}/i", $_extension ) ) {
        $content_type = $mime_types[ $_extension ];
        break;
    }
}

if( empty( $content_type ) ) $content_type = 'application/octet-stream';
header("Content-type: $content_type");

switch( $action ) {
    case 'download':
        header( "Content-Disposition: attachment; filename=\"" . $line['name'] . "\"" );
//        if ( !$line['external'] ) {
        header("Content-length: $fsize");
//        }
        break;
    case 'view':
        $line['name'] = $thumbnail ? 'thumbnails_' . $line['name'] : $line['name'];
        header( "Content-Disposition: inline; filename=\"" . $line['name'] . "\"" );
        break;
    case 'play':
        break;
    default:
        exit( __( 'Wrong action', WPC_CLIENT_TEXT_DOMAIN ) );
}

$levels = ob_get_level();
for ($i=0; $i<$levels; $i++)
    @ob_end_clean();

if( $action == 'play' ) {
    $cur = $begin;
    $fm = @fopen( $target_path,'rb' );
    fseek( $fm, $begin, 0 );

    while( !feof( $fm ) && $cur < $end && ( connection_status() == 0 ) ) {
        print fread( $fm, min( 1024*16, $end - $cur ) );
        $cur += 1024 * 16;
        usleep( 1000 );
    }
    exit;
} else if( !$line['external'] ) {
    //for files on server
    WPC()->readfile_chunked( $target_path );
} else {
    //for external
    $args = array(
        'timeout' => 10000,
        'stream' => true
    );

    //get file by chunks
    add_action( 'http_api_curl', 'wpc_download_curl_params', 1000, 3  );
    wp_remote_get( $target_path, $args );
}


/*
* notification about download
*/
if ( $admins_notify && !$thumbnail && 'download' == $action ) {

    //email to admins
    $args = array(
        'role'      => 'wpc_admin',
        'fields'    => array( 'user_email' )
    );
    $admin_emails = get_users( $args );
    $emails_array = array();
    if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
        foreach( $admin_emails as $admin_email ) {
             $emails_array[] = $admin_email->user_email;
        }
    }

    $emails_array[] = get_option( 'admin_email' );
    $emails_array = array_unique( $emails_array );

    $args = array( 'client_id' => $user_id, 'file_name' => $line['name'] );

    foreach( $emails_array as $to_email ) {
        WPC()->mail( 'client_downloaded_file', $to_email, $args, 'client_downloaded_file' );
    }


    //send message to client manager
    //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $user_id );
    $manager_ids = WPC()->members()->get_client_managers( $user_id );

    if( is_array( $manager_ids ) && count( $manager_ids ) ) {
        foreach( $manager_ids as $manager_id ) {
            if ( 0 < $manager_id ) {
                $manager = get_userdata( $manager_id );
                if ( $manager ) {
                    $manager_email = $manager->get( 'user_email' );
                    //send email
                    WPC()->mail( 'client_downloaded_file', $manager_email, $args, 'client_downloaded_file' );
                }
            }
        }
    }
}

exit;