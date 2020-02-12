<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// If uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
   exit();

global $wpdb;


/*
* Delete "uploads/wpclient/" folder and files
*/
$uploads = wp_upload_dir();
$uploads['basedir'] = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] );
WPC()->remove_dir( $uploads['basedir'] . DIRECTORY_SEPARATOR . 'wpclient' . DIRECTORY_SEPARATOR );


/*
* Delete OLD all options
*/
delete_option( 'parent_page_id' );
delete_option( 'parent_title' );
delete_option( 'hub_template' );
delete_option( 'client_template' );
delete_option( 'clients_page' );
delete_option( 'client_com' );
delete_option( 'hub_com' );
delete_option( 'custom_login_options' );
delete_option( 'wp_client_ver' );
delete_option( 'sender_email' );
delete_option( 'sender_name' );
delete_option( 'new_subject' );
delete_option( 'update_subject' );
delete_option( 'new_email_client_template' );
delete_option( 'update_client_page_email_template' );
delete_option( 'show_sort' );
delete_option( 'wpclients_theme' );
delete_option( 'wp-password-generator-opts' );
delete_option( 'widget_wpc_client_widget' );
delete_option( 'widget_wpc_client_widget_pp' );


/*
* Delete all plugin users
*/
$clients_id = get_users( array( 'role' => 'wpc_client', 'fields' => 'ID', ) );
if ( is_array( $clients_id ) && 0 < count( $clients_id ) )
    foreach( $clients_id as $user_id ) {
        if( is_multisite() ) {
            wpmu_delete_user( $user_id );
        } else {
            wp_delete_user( $user_id );
        }
    }


$staff_id = get_users( array( 'role' => 'wpc_client_staff', 'fields' => 'ID', ) );
if ( is_array( $staff_id ) && 0 < count( $staff_id ) )
    foreach( $staff_id as $user_id ) {
        if( is_multisite() ) {
            wpmu_delete_user( $user_id );
        } else {
            wp_delete_user( $user_id );
        }
    }


$managers_id = get_users( array( 'role' => 'wpc_manager', 'fields' => 'ID', ) );
if ( is_array( $managers_id ) && 0 < count( $managers_id ) )
    foreach( $managers_id as $user_id ) {
        if( is_multisite() ) {
            wpmu_delete_user( $user_id );
        } else {
            wp_delete_user( $user_id );
        }
    }

$admins_id = get_users( array( 'role' => 'wpc_admin', 'fields' => 'ID', ) );
if ( is_array( $admins_id ) && 0 < count( $admins_id ) )
    foreach( $admins_id as $user_id ) {
        if( is_multisite() ) {
            wpmu_delete_user( $user_id );
        } else {
            wp_delete_user( $user_id );
        }
    }



/*
* Remove all plugin roles
*/
global $wp_roles;
//remore roles
$wp_roles->remove_role( "pcc_client" );
$wp_roles->remove_role( "wpc_client" );
$wp_roles->remove_role( "wpc_client_staff" );
$wp_roles->remove_role( "wpc_manager" );
$wp_roles->remove_role( "wpc_admin" );



/*
* Remove all hub pages
*/
$args = array(
    'numberposts'   => -1,
    'post_type'     => 'portalhub',
);
$portalhubs = get_posts( $args );
if ( is_array( $portalhubs ) && 0 < count( $portalhubs ) ) {
    foreach( $portalhubs as $portalhub )
        wp_delete_post( $portalhub->ID );
}



/*
* Remove all clients pages
*/
$args = array(
    'numberposts' => -1,
    'post_type' => 'clientspage',
);
$clint_pages = get_posts( $args );
if ( is_array( $clint_pages ) && 0 < count( $clint_pages ) ) {
    foreach( $clint_pages as $clint_page )
        wp_delete_post( $clint_page->ID );
}



/*
* Remove all plugin pages
*/
$args = array(
    'hierarchical'  => 0,
    'meta_key'      => 'wpc_client_page',
    'post_type'     => 'page',
    'post_status'   => 'publish,trash,pending,draft,auto-draft,future,private,inherit',
);
$wpc_client_pages = get_pages( $args );
if ( is_array( $wpc_client_pages ) && 0 < count( $wpc_client_pages ) ) {
    foreach( $wpc_client_pages as $wpc_client_page )
        wp_delete_post( $wpc_client_page->ID, true );
}



/*
* Delete all our tables (Core + Extensions)
*/
$tables = $wpdb->get_col( "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '{$wpdb->prefix}wpc_client_%' AND table_schema = '" . DB_NAME . "'" );
if ( $tables ) {
    foreach( $tables as $key ) {
        $wpdb->query( "DROP TABLE {$key}" );
    }
}


//delete pages
$wpc_pages = WPC()->get_settings( 'pages' );
if ( is_array( $wpc_pages ) && count( $wpc_pages ) ) {
    foreach( $wpc_pages as $key => $val ) {
        if ( is_int( $val ) )
            wp_delete_post( $val, true );
    }
}

//Delete settings/templates
$wpdb->query("DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'wp_client_%' AND `option_name` NOT LIKE 'wp_client_license%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'wpc_%'");

update_option( 'wpc_wizard_setup', 'true' );

//action for extensions
do_action( 'wp_client_uninstall' );

//Deactivate all activated extensions
$extensions = WPC()->extensions()->get_extensions();
if ( $extensions ) {
    foreach( $extensions as $extension ) {
        if ( ! empty( $extension['plugin'] ) ) {
            deactivate_plugins( $extension['plugin'] );
        }
    }
}
