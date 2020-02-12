<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];
    $settings['front_end_admins'] = ( isset( $settings['front_end_admins'] ) ) ? $settings['front_end_admins'] : array();

    WPC()->settings()->update( $settings, 'private_messages' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_private_messages = WPC()->get_settings( 'private_messages' );
$selected_admins = ( isset( $wpc_private_messages['front_end_admins'] ) && !empty( $wpc_private_messages['front_end_admins'] ) ) ? $wpc_private_messages['front_end_admins'] : array();

$args = array(
    'role'      => 'wpc_admin',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
);
$wpc_admins = get_users( $args );
$wpc_admins_opt = array();

if ( !empty( $wpc_admins ) ) {
    foreach( $wpc_admins as $user ) {
        $wpc_admins_opt[$user->ID] = $user->user_login;
    }
}

$args = array(
    'role'      => 'administrator',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
);
$administrators = get_users( $args );
$administrators_opt = array();

if ( !empty( $administrators ) ) {
    foreach( $administrators as $user ) {
        $administrators_opt[$user->ID] = $user->user_login;
    }
}

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Private Messages Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'first_new_chains',
        'type' => 'checkbox',
        'label' => __( 'New Message', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_private_messages['first_new_chains'] ) ) ? $wpc_private_messages['first_new_chains'] : 'no',
        'description' => __( 'Show new message chains at the top', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'front_end_admins',
        'type' => 'multi-selectbox',
        'label' => __( 'Public Admins', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_private_messages['front_end_admins'] ) ) ? $wpc_private_messages['front_end_admins'] : '',
        'description' => __( 'Allow Members to see these Admins on frontend in order to send them private messages', WPC_CLIENT_TEXT_DOMAIN ),
        'optgroups' => array(
            array(
                'label' => WPC()->custom_titles['admin']['p'],
                'attrs' => 'data-single_title="' . WPC()->custom_titles['admin']['s'] . '" data-color="#dc832d"',
                'options' => $wpc_admins_opt,
            ),
            array(
                'label' => __( 'Administrators', WPC_CLIENT_TEXT_DOMAIN ),
                'attrs' => 'data-single_title="' . __( 'Administrators', WPC_CLIENT_TEXT_DOMAIN ) . '" data-color="#b63ad0"',
                'options' => $administrators_opt,
            ),
        ),
    ),
    array(
        'id' => 'relate_client_staff',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'label' => sprintf( __( '%s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ),
        'value' => ( isset( $wpc_private_messages['relate_client_staff'] ) ) ? $wpc_private_messages['relate_client_staff'] : 'yes',
        'description' => sprintf( __( 'Allow %s and %s to communicate with each other', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ),
    ),
    array(
        'id' => 'relate_client_manager',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'label' => sprintf( __( '%s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['manager']['p'] ),
        'value' => ( isset( $wpc_private_messages['relate_client_manager'] ) ) ? $wpc_private_messages['relate_client_manager'] : 'yes',
        'description' => sprintf( __( 'Allow %s and %s to communicate with each other', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['manager']['p'] ),
    ),
    array(
        'id' => 'relate_staff_manager',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'label' => sprintf( __( '%s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'], WPC()->custom_titles['manager']['p'] ),
        'value' => ( isset( $wpc_private_messages['relate_staff_manager'] ) ) ? $wpc_private_messages['relate_staff_manager'] : 'yes',
        'description' => sprintf( __( 'Allow %s and %s to communicate with each other', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'], WPC()->custom_titles['manager']['p'] ),
    ),
    array(
        'id' => 'relate_manager_manager',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'label' => sprintf( __( '%s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'], WPC()->custom_titles['manager']['p'] ),
        'value' => ( isset( $wpc_private_messages['relate_manager_manager'] ) ) ? $wpc_private_messages['relate_manager_manager'] : 'yes',
        'description' => sprintf( __( 'Allow %s and %s to communicate with each other', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'], WPC()->custom_titles['manager']['p'] ),
    ),
    array(
        'id' => 'add_cc_email',
        'type' => 'checkbox',
        'label' => __( 'CC Email in Messages', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_private_messages['add_cc_email'] ) ) ? $wpc_private_messages['add_cc_email'] : 'no',
        'description' => __( 'Add CC Email field to messages', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'send_to_site_admin',
        'type' => 'checkbox',
        'label' => __( 'Email Notifications', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_private_messages['send_to_site_admin'] ) ) ? $wpc_private_messages['send_to_site_admin'] : 'no',
        'description' => __( 'Send messaging email notifications to Site Email.', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Current email is', WPC_CLIENT_TEXT_DOMAIN ) . ' <strong>' . get_option( 'admin_email' ) . '</strong>',
    ),
    array(
        'id' => 'display_name',
        'type' => 'selectbox',
        'label' => __( 'Show Member Name As', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login',
        'options' => array(
            'user_login' => __( 'User Login', WPC_CLIENT_TEXT_DOMAIN ),
            'display_name' => __( 'Display Name', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'description' => '',
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

<style type="text/css">
    .wpc_selector_wrapper {
        width: 60%;
        float: none;
    }
    .wpc_selector_wrapper .wpc_selector {
        float: none;
    }
    .wpc_drop_search {
        width:100% !important;
    }

</style>

<script type="text/javascript">
    jQuery( document ).ready( function() {
        jQuery( '#wpc_settings_front_end_admins' ).wpc_select({
            search:true,
            opacity:'0.2'
        });


        <?php if ( WPC()->flags['easy_mode'] ) { ?>
            jQuery( '#wpc_settings_front_end_admins' ).parents( 'tr' ).hide();
        <?php } ?>

    });
</script>