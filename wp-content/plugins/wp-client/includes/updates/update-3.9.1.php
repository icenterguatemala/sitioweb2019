<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_convert_users = WPC()->get_settings( 'convert_users' );

if ( isset( $wpc_convert_users['auto_convert_role'] ) ) {
    global $wp_roles;
    $all_roles = $wp_roles->roles;

    $wpc_auto_convert_rules = array();
    foreach( $wpc_convert_users['auto_convert_role'] as $key => $to_role ) {

        if ( 'administrator' == $key ) {
            continue;
        }

        $temp_to_role = str_replace( 'wpc_', '', $to_role );
        if ( !isset( $wpc_convert_users['auto_convert'][$temp_to_role] ) || 'yes' != $wpc_convert_users['auto_convert'][$temp_to_role] ) {
            continue;
        }

        $to_role = ( 'wpc_staff' == $to_role ) ? 'wpc_client_staff' : $to_role;

        if ( 'role_all' == $key ) {
            $key = '__all_roles';
        }

        switch( $to_role ) {
            case 'wpc_client': {
                $temp_rule = array(
                    'business_name_field'   => ( isset( $wpc_convert_users['client_business_name_field'] ) ) ? $wpc_convert_users['client_business_name_field'] : '',
                    'create_page'           => ( isset( $wpc_convert_users['client_create_page'] ) ) ? $wpc_convert_users['client_create_page'] : 'no',
                    'save_role'             => ( isset( $wpc_convert_users['client_save_role'] ) ) ? $wpc_convert_users['client_save_role'] : 'no',
                    'wpc_circles'           => ( isset( $wpc_convert_users['client_wpc_circles'] ) ) ? $wpc_convert_users['client_wpc_circles'] : '',
                    'wpc_managers'          => ( isset( $wpc_convert_users['client_wpc_managers'] ) ) ? $wpc_convert_users['client_wpc_managers'] : '',
                );
                break;
            }

            case 'wpc_client_staff': {
                $temp_rule = array(
                    'save_role'             => ( isset( $wpc_convert_users['staff_save_role'] ) ) ? $wpc_convert_users['staff_save_role'] : 'no',
                    'wpc_clients'           => ( isset( $wpc_convert_users['staff_wpc_clients'] ) ) ? $wpc_convert_users['staff_wpc_clients'] : '',
                );
                break;
            }

            case 'wpc_manager': {
                $temp_rule = array(
                    'save_role'             => ( isset( $wpc_convert_users['manager_save_role'] ) ) ? $wpc_convert_users['manager_save_role'] : 'no',
                    'wpc_clients'           => ( isset( $wpc_convert_users['manager_wpc_clients'] ) ) ? $wpc_convert_users['manager_wpc_clients'] : '',
                    'wpc_circles'           => ( isset( $wpc_convert_users['manager_wpc_circles'] ) ) ? $wpc_convert_users['manager_wpc_circles'] : '',
                );
                break;
            }

            case 'wpc_admin': {
                $temp_rule = array(
                    'save_role'             => ( isset( $wpc_convert_users['admin_save_role'] ) ) ? $wpc_convert_users['admin_save_role'] : 'no',
                );
                break;
            }
        }

        $temp_rule['from_title']    = ( isset( $all_roles[$key]['name'] ) ) ? $all_roles[$key]['name'] : __( 'All Roles', WPC_CLIENT_TEXT_DOMAIN );
        $temp_rule['to_title']      = ( isset( $all_roles[$to_role]['name'] ) ) ? $all_roles[$to_role]['name'] : '';
        $temp_rule['to_role']       = $to_role;

        $wpc_auto_convert_rules[$key] = $temp_rule;
    }

    WPC()->settings()->update( $wpc_auto_convert_rules, 'auto_convert_rules' );

}