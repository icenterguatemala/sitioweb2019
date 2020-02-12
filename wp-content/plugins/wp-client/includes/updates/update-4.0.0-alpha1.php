<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for sending settings profile
$core_email_profile = get_option( 'wpc_email_sending', array() );
if ( !empty( $core_email_profile ) ) {
    $core_email_profile['profile_name'] = 'Core Email Profile';

    $all_email_profiles = get_option( 'wpc_email_sending_profiles', array() );

    $core_profile_id = uniqid();

    $all_email_profiles[ $core_profile_id ] = $core_email_profile;

    update_option( 'wpc_email_sending_profiles', $all_email_profiles);
    update_option( 'wpc_email_sending_profile_for_core', $core_profile_id);
    //delete_option( 'wpc_email_sending' );
}
//end