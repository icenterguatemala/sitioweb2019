<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb, $wp_query;
@ignore_user_abort(true);
@set_time_limit(0);

/*
* Downloader
*/
if( empty( $id ) )
    exit( __( 'Wrong ID', WPC_CLIENT_TEXT_DOMAIN ) );

$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
if( !( isset( $_GET['key'] ) && !empty( $_GET['key'] ) ) || !( isset( $wpc_custom_fields[$_GET['key']] ) ) )
    exit( __( 'Wrong custom_field', WPC_CLIENT_TEXT_DOMAIN ) );

if( !( is_numeric( $id ) && (int)$id > 0 ) ) {
    $id_array = explode( '.', $id );
    $id = $id_array[0];
}

if ( (int)$id <= 0 ) {
    exit( __( 'Invalid file. Please try downloading again!', WPC_CLIENT_TEXT_DOMAIN ) );
}

$access = false;
if ( is_user_logged_in() ) {
    //checking access for file
    if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
        $access = true;
    } elseif ( current_user_can( 'wpc_manager' ) ) {
        if( !empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], $user_id . AUTH_KEY ) ) {
            $access = true;
        } else {
            die( __( 'You do not have access to this file!', WPC_CLIENT_TEXT_DOMAIN ) );
        }
    } elseif ( get_current_user_id() == $id ) {
        //access for file owner
        $access = true;
    } elseif ( current_user_can( 'wpc_client' ) && !current_user_can( 'manage_network_options' ) ) {
        $staff_ids = WPC()->members()->get_client_staff_ids( $user_id );
        if( in_array( $id, $staff_ids ) ) {
            $access         = true;
        }
    }
}

if ( !$access )
    exit( __( 'You do not have access to this file!', WPC_CLIENT_TEXT_DOMAIN ) );

$file = get_user_meta( $id, $_GET['key'], true );
$file_name = $file['filename'];
$target_path = WPC()->get_upload_dir( 'wpclient/_custom_field_files/' . $_GET['key'] . '/' ) . $file_name;

if( !file_exists( $target_path ) ) {
    exit( __( 'File does not exist', WPC_CLIENT_TEXT_DOMAIN ) );
}

header("Pragma: no-cache");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Robots: none");
header("Content-Description: File Transfer");
header("Content-Transfer-Encoding: binary");

$mime_types = wp_get_mime_types();
$extensions = array_keys( $mime_types );

$content_type = '';
$ext = '';
$path_parts = pathinfo( $file_name );
$ext = isset( $path_parts['extension'] ) ? strtolower( $path_parts['extension'] ) : '';
foreach( $extensions as $_extension ) {
    if ( preg_match( "/{$ext}/i", $_extension ) ) {
        $content_type = $mime_types[ $_extension ];
    }
}

if( empty( $content_type ) )
    $content_type = 'application/octet-stream';

header("Content-type: $content_type");


$action = !empty( $_GET['wpc_action'] ) ? $_GET['wpc_action'] : 'download';
switch( $action ) {
    case 'download':
        header( "Content-Disposition: attachment; filename=\"" . $file['origin_name'] . "\"" );
        $fsize = filesize( $target_path );
        header("Content-length: $fsize");
        break;
    case 'view':
        header( "Content-Disposition: inline; filename=\"" . $file['origin_name'] . "\"" );
        break;
    default:
        exit( __( 'Wrong action', WPC_CLIENT_TEXT_DOMAIN ) );
}

$levels = ob_get_level();
for ( $i=0; $i<$levels; $i++ )
    @ob_end_clean();


//for files on server
WPC()->readfile_chunked( $target_path );
exit;