<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_convert_users = WPC()->get_settings( 'convert_users' );

if( isset( $wpc_convert_users['auto_convert_role'] ) && !empty( $wpc_convert_users['auto_convert_role'] ) ) {

    global $wp_roles;

    $all_roles = $wp_roles->roles;
    $temp_auto_convert_role = array();

    foreach( $all_roles as $key=>$role ) {
        if( 'wpc_' == substr( $key, 0, 4 ) ) {
            continue;
        }
        foreach( $wpc_convert_users['auto_convert_role'] as $wpc_role=>$user_roles_array ) {
            if( isset( $user_roles_array[$key] ) && 'yes' == $user_roles_array[$key] ) {
                $temp_auto_convert_role[$key] = 'wpc_' . $wpc_role;
            }
        }
    }

    $wpc_convert_users['auto_convert_role'] = $temp_auto_convert_role;
}

if( isset( $wpc_convert_users['role_all'] ) && !empty( $wpc_convert_users['role_all'] ) ) {
    foreach( $wpc_convert_users['role_all'] as $role=>$enabled ) {
        if( 'yes' == $enabled ) {
            $wpc_convert_users['auto_convert_role']['role_all'] = 'wpc_' . $role;
        }
    }
    unset( $wpc_convert_users['role_all'] );
}

WPC()->settings()->update( $wpc_convert_users, 'convert_users' );