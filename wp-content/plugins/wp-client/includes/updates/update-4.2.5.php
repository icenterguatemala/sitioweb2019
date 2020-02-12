<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$general = WPC()->get_settings( 'general' );

if ( isset( $general['show_hub_link'] ) && 'yes' === $general['show_hub_link'] ) {
    $new_notices['hub_page_in_menu'] = 'PLEASE NOTE: '
        . 'The setting "Show HUB Page link in menu" is no longer valid in this latest update. '
        . 'You can still add a HUB Page menu link manually if desired, '
        . 'from <a target="_blank" href="' . admin_url( 'nav-menus.php' ) . '">HERE</a>';

    WPC()->admin()->add_wpc_notices( $new_notices );

    unset(
        $general['show_hub_link'],
        $general['hub_link_text']
    );

    WPC()->settings()->update( $general, 'general' );
}

if ( WPC()->get_settings( 'notification_moved_menu_settings' ) ) {
    $new_notices['notification_moved_menu_settings'] = sprintf( 'NOTICE: Custom Navigation settings have been moved to '
        . '<a href="%s" target="_blank">Manage Locations</a>',
        add_query_arg( 'action', 'locations', admin_url( 'nav-menus.php' ) ) );

    WPC()->admin()->add_wpc_notices( $new_notices );

    WPC()->delete_settings( 'notification_moved_menu_settings' );
}