<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
$wpc_convert_users = WPC()->get_settings( 'convert_users' );
if( !empty( $wpc_clients_staff['auto_convert'] ) ) {
    $wpc_convert_users['auto_convert']['client'] = $wpc_clients_staff['auto_convert'];
}
if( !empty( $wpc_clients_staff['role_all'] ) ) {
    $wpc_convert_users['role_all']['client'] = $wpc_clients_staff['role_all'];
}
if( !empty( $wpc_clients_staff['auto_convert_role'] ) ) {
    $wpc_convert_users['auto_convert_role']['client'] = $wpc_clients_staff['auto_convert_role'];
}
WPC()->settings()->update( $wpc_convert_users, 'convert_users' );