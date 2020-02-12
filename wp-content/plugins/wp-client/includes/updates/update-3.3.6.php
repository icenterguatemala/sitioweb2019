<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

//for transfer assign data for new logic

//assign for files
$files = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_files", ARRAY_A );

foreach( $files as $file ) {
    $file_id = $file['id'];

    $client_ids = array();
    if( isset( $file['clients_id'] ) && !empty( $file['clients_id'] ) ) {
        $client_ids = explode( ',', str_replace( '#', '', $file['clients_id'] ) );
        unset( $client_ids[count( $client_ids ) - 1] );
    }
    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', $client_ids );


    $group_ids = array();
    if( isset( $file['groups_id'] ) && !empty( $file['groups_id'] ) ) {
        $group_ids = explode( ',', str_replace( '#', '', $file['groups_id'] ) );
        unset( $group_ids[count( $group_ids ) - 1] );
    }
    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $group_ids );
}


//assign for file categories
$file_categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_file_categories", ARRAY_A );

foreach( $file_categories as $file_category ) {
    $file_category_id = $file_category['cat_id'];

    $client_ids = array();
    if( isset( $file_category['clients_id'] ) && !empty( $file_category['clients_id'] ) ) {
        $client_ids = explode( ',', str_replace( '#', '', $file_category['clients_id'] ) );
        unset( $client_ids[count( $client_ids ) - 1] );
    }
    WPC()->assigns()->set_assigned_data( 'file_category', $file_category_id, 'client', $client_ids );


    $group_ids = array();
    if( isset( $file_category['groups_id'] ) && !empty( $file_category['groups_id'] ) ) {
        $group_ids = explode( ',', str_replace( '#', '', $file_category['groups_id'] ) );
        unset( $group_ids[count( $group_ids ) - 1] );
    }
    WPC()->assigns()->set_assigned_data( 'file_category', $file_category_id, 'circle', $group_ids );
}


//assign for portal pages
$args = array(
    'orderby'          => 'post_name',
    'order'            => 'ASC',
    'post_type'        => 'clientspage',
    'post_status'      => 'publish'
);
$posts = get_posts( $args );

foreach( $posts as $post ) {
    $post_id = $post->ID;

    $client_ids = get_post_meta( $post_id, 'user_ids', true );
    $group_ids = get_post_meta( $post_id, 'groups_id', true );

    $client_ids = ( is_array( $client_ids ) && 0 < count( $client_ids ) ) ? $client_ids : array();
    $group_ids = ( is_array( $group_ids ) && 0 < count( $group_ids ) ) ? $group_ids : array();

    WPC()->assigns()->set_assigned_data( 'portal_page', $post_id, 'client', $client_ids );
    WPC()->assigns()->set_assigned_data( 'portal_page', $post_id, 'circle', $group_ids );
}


//assign for portal page categories
$portal_page_categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_portal_page_categories", ARRAY_A );

foreach( $portal_page_categories as $portal_page_category ) {
    $portal_page_category_id = $portal_page_category['cat_id'];

    $client_ids = array();
    if( isset( $portal_page_category['clients_id'] ) && !empty( $portal_page_category['clients_id'] ) ) {
        $client_ids = explode( ',', str_replace( '#', '', $portal_page_category['clients_id'] ) );
        unset( $client_ids[count( $client_ids ) - 1] );
    }
    WPC()->assigns()->set_assigned_data( 'portal_page_category', $portal_page_category_id, 'client', $client_ids );


    $group_ids = array();
    if( isset( $portal_page_category['groups_id'] ) && !empty( $portal_page_category['groups_id'] ) ) {
        $group_ids = explode( ',', str_replace( '#', '', $portal_page_category['groups_id'] ) );
        unset( $group_ids[count( $group_ids ) - 1] );
    }
    WPC()->assigns()->set_assigned_data( 'portal_page_category', $portal_page_category_id, 'circle', $group_ids );
}


//assign for ez_hub
$ez_hubs = get_option( 'wpc_ez_hub_templates' );

$temp_time = time();

foreach( $ez_hubs as $ez_hub_id=>$ez_hub ) {

    if( $ez_hub_id == 'default' ) {
        continue;
    }

    $temp_option = get_option( 'wpc_ez_hub_' . $ez_hub_id );

    $new_ez_hub_id = $temp_time;
    update_option( 'wpc_ez_hub_' . $new_ez_hub_id, $temp_option );
    delete_option( 'wpc_ez_hub_' . $ez_hub_id );

    if( isset( $ez_hub['clients_ids'] ) && !empty( $ez_hub['clients_ids'] ) ) {
        WPC()->assigns()->set_assigned_data( 'ez_hub', $new_ez_hub_id, 'client', $ez_hub['clients_ids'] );
        unset( $ez_hub['clients_ids'] );
    }

    if( isset( $ez_hub['groups_ids'] ) && !empty( $ez_hub['groups_ids'] ) ) {
        WPC()->assigns()->set_assigned_data( 'ez_hub', $new_ez_hub_id, 'circle', $ez_hub['groups_ids'] );
        unset( $ez_hub['groups_ids'] );
    }

    $ez_hubs[$new_ez_hub_id] = $ez_hub;
    unset( $ez_hubs[$ez_hub_id] );

    $temp_time = $temp_time - 100;
}
update_option( 'wpc_ez_hub_templates', $ez_hubs );


//assign for managers
$args = array(
    'role' => 'wpc_client',
);

$clients = get_users( $args );

foreach( $clients as $client ) {
    $client_id = $client->ID;

    $client_managers = get_user_meta( $client_id, 'admin_manager', true );

    if( isset( $client_managers ) && !empty( $client_managers ) ) {
        $client_managers = explode( ',', $client_managers );
        WPC()->assigns()->set_reverse_assigned_data( 'manager', $client_managers, 'client', $client_id );
    }
}




//renamed some ez_hub tabs-content class

$wpc_ez_hub_templates = get_option( 'wpc_ez_hub_templates' );

if( is_array( $wpc_ez_hub_templates ) && 0 < count( $wpc_ez_hub_templates ) ) {

    if( !isset( $wpc_ez_hub_templates['default']['type'] ) || ( isset( $wpc_ez_hub_templates['default']['type'] ) && 'advanced' != $wpc_ez_hub_templates['default']['type'] ) ) {
        $wpc_ez_hub_templates['default']['type'] = 'ez';
    }

    foreach( $wpc_ez_hub_templates as $key=>$value ) {
        $value['tabs_content'] = str_replace('wpc-hub-toolbar ', 'wpc-toolbar ', $value['tabs_content'] );
        $wpc_ez_hub_templates[$key] = $value;
    }
    update_option( 'wpc_ez_hub_templates', $wpc_ez_hub_templates );
}