<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$current_tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'dashboard'; ?>
<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <?php echo WPC()->admin()->gen_tabs_menu( 'dashboard' );

    if( WPC()->flags['easy_mode'] ) { ?>
        <div id="message" class="updated wpc_notice fade"><p>
            <?php printf( __( 'Easy Mode Currently Active. You can change it on <a href="$s" target="_blank">General Settings</a>.', WPC_CLIENT_TEXT_DOMAIN ),
                    admin_url( 'admin.php?page=wpclients_settings' ) ) ?>
            </p></div>
    <?php }

    switch ( $current_tab ) {
        case 'system_status':
            if ( ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) && !WPC()->plugin['hide_system_status_tab'] ) {
                include_once( WPC()->plugin_dir . 'includes/admin/dashboard_system_status.php' );
            } else {
                WPC()->redirect( admin_url( 'admin.php?page=wpclients' ) );
            }
        break;
        case 'get_started':
            if ( ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) && !WPC()->plugin['hide_get_started_tab'] ) {
                include_once( WPC()->plugin_dir . 'includes/admin/dashboard_get_started.php' );
            } else {
                WPC()->redirect( admin_url( 'admin.php?page=wpclients' ) );
            }
        break;
        case 'licenses':
            if ( ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) && !WPC()->plugin['hide_licenses_tab'] ) {
                include_once( WPC()->plugin_dir . 'includes/admin/dashboard_licenses.php' );
            } else {
                WPC()->redirect( admin_url( 'admin.php?page=wpclients' ) );
            }
        break;
        default:

            if( ! WPC()->is_licensed( 'WP-Client' ) && current_user_can( 'administrator' ) && ! WPC()->plugin['hide_licenses_tab'] ) {
                include_once( WPC()->plugin_dir . 'includes/admin/dashboard_licenses.php' );
            } else {
                if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                    include_once( WPC()->plugin_dir . 'includes/admin/dashboard_dashboard.php' );
                } elseif ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    include_once( WPC()->plugin_dir . 'includes/admin/dashboard_dashboard.php' );
                } else {
                    include_once( WPC()->plugin_dir . 'includes/admin/dashboard_managers.php' );
                }
            }
        break;
    }

 ?>

</div>