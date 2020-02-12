<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'clients_staff' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => ( ( !WPC()->flags['easy_mode'] ) ? sprintf( __( '%s/%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) : sprintf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) .
           ' ' . __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'create_portal_page',
        'type' => 'checkbox',
        'label' => sprintf( __( 'Auto Create %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ),
        'value' => ( isset( $wpc_clients_staff['create_portal_page'] ) ) ? $wpc_clients_staff['create_portal_page'] : 'yes',
        'description' => sprintf( __( 'Automatically create %s when new %s is created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'], WPC()->custom_titles['client']['s'] ),
    ),
    array(
        'id' => 'hide_dashboard',
        'type' => 'checkbox',
        'label' => __( 'Dashboard/Backend', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['hide_dashboard'] ) ) ? $wpc_clients_staff['hide_dashboard'] : 'no',
        'description' => ( !WPC()->flags['easy_mode'] ) ? sprintf( __( 'Hide WordPress admin dashboard/backend from %s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) : sprintf( __( 'Hide WordPress admin dashboard/backend from %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'hide_admin_bar',
        'type' => 'checkbox',
        'label' => __( 'Admin Bar', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['hide_admin_bar'] ) ) ? $wpc_clients_staff['hide_admin_bar'] : 'yes',
        'description' => ( !WPC()->flags['easy_mode'] ) ? sprintf( __( 'Hide top WordPress Admin Bar from %s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) : sprintf( __( 'Hide top WordPress Admin Bar from %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'lost_password',
        'type' => 'checkbox',
        'label' => __( 'Lost Password', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['lost_password'] ) ) ? $wpc_clients_staff['lost_password'] : 'no',
        'description' => __( 'Display "Lost your password" link on login form', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'reset_password',
        'type' => 'checkbox',
        'label' => __( 'Resend Welcome Email', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['reset_password'] ) ) ? $wpc_clients_staff['reset_password'] : 'yes',
        'description' => __( 'Reset password when resending welcome email', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'type' => 'title',
        'label' => sprintf( __( '%s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    ),
    array(
        'id' => 'client_registration',
        'type' => 'checkbox',
        'label' => sprintf( __( '%s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
        'value' => ( isset( $wpc_clients_staff['client_registration'] ) ) ? $wpc_clients_staff['client_registration'] : 'no',
        'description' => sprintf( __( 'Allow self-registration of %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'tips' => sprintf( __( 'Allows %2$s to self-register using %1$s Registration Form. By default, self-registered %2$s require Admin approval before their account is active', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'avatar_on_registration',
        'type' => 'checkbox',
        'label' => __( 'Avatar', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['avatar_on_registration'] ) ) ? $wpc_clients_staff['avatar_on_registration'] : 'no',
        'description' => __( 'Show avatar field on Registration Form', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'client_registration', '=', 'yes' ),
    ),
    array(
        'id' => 'new_client_admin_notify',
        'type' => 'checkbox',
        'label' => __( 'New Registration Notification', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['new_client_admin_notify'] ) ) ? $wpc_clients_staff['new_client_admin_notify'] : 'yes',
        'description' => __( 'Notify Admin about new registrations', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'client_registration', '=', 'yes' ),
    ),
    array(
        'id' => 'auto_client_approve',
        'type' => 'checkbox',
        'label' => sprintf( __( '%s Approval', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
        'value' => ( isset( $wpc_clients_staff['auto_client_approve'] ) ) ? $wpc_clients_staff['auto_client_approve'] : 'no',
        'description' => sprintf( __( 'Automatically approve %s self-registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
        'conditional' => array( 'client_registration', '=', 'yes' ),
    ),
    array(
        'id' => 'send_approval_email',
        'type' => 'checkbox',
        'label' => __( 'Approval Notification', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['send_approval_email'] ) ) ? $wpc_clients_staff['send_approval_email'] : 'no',
        'description' => sprintf( __( 'Send email notification to %s once their accounts have been approved by %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['admin']['s']  ),
        'conditional' => array( 'client_registration', '=', 'yes' ),
    ),
    array(
        'id' => 'auto_login_after_registration',
        'type' => 'checkbox',
        'label' => __( 'Auto Login', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['auto_login_after_registration'] ) ) ? $wpc_clients_staff['auto_login_after_registration'] : 'no',
        'description' => sprintf( __( 'Automatically login %s after their registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'conditional' => array( 'client_registration', '=', 'yes' ),
    ),
    array(
        'id' => 'verify_email',
        'type' => 'checkbox',
        'label' => __( 'Verify Email', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['verify_email'] ) ) ? $wpc_clients_staff['verify_email'] : 'no',
        'description' => sprintf( __( 'Require self-registered %s to verify email address before they can access their portal', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'conditional' => array( 'client_registration', '=', 'yes' ),
    ),
    array(
        'id' => 'url_after_verify',
        'type' => 'text',
        'label' => __( 'Verify Email Redirect', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_clients_staff['url_after_verify'] ) ) ? $wpc_clients_staff['url_after_verify'] : '',
        'description' => sprintf( __( 'Redirect URL after Email verifying for logged in %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'conditional' => array( 'verify_email', '=', 'yes' ),
    ),
    array(
        'type' => WPC()->flags['easy_mode'] ? '' : 'title',
        'label' => sprintf( __( '%s Creation', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    ),
    array(
        'id' => 'staff_registration',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'is_option' => true,
        'label' => sprintf( __( '%s Creation', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
        'value' => ( isset( $wpc_clients_staff['staff_registration'] ) ) ? $wpc_clients_staff['staff_registration'] : 'no',
        'description' => sprintf( __( 'Allow %s to create %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ),
        'tips' => sprintf( __( 'Allows %1$s to register their own %2$s users. By default, %2$s users require %3$s approval before their account is active', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['s'], WPC()->custom_titles['admin']['s'] ),
    ),
    array(
        'id' => 'auto_client_staff_approve',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'is_option' => true,
        'label' => sprintf( __( '%s Approval', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
        'value' => ( isset( $wpc_clients_staff['auto_client_staff_approve'] ) ) ? $wpc_clients_staff['auto_client_staff_approve'] : 'no',
        'description' => sprintf( __( 'Automatically approve %s created by %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'], WPC()->custom_titles['client']['p'] ),
        'conditional' => array( 'staff_registration', '=', 'yes' ),
    ),
);

/*our_hook_
    hook_name: wpc_client_settings_client_staff
    hook_title: You can create your custom options at client/staff section
    hook_description: Hook runs before render settings section.
    hook_type: filter
    hook_in: wp-client
    hook_location settings core
    hook_param: array $section_fields
    hook_since: 4.5.1
*/
$section_fields = apply_filters( 'wpc_client_settings_client_staff', $section_fields );

WPC()->settings()->render_settings_section( $section_fields );
