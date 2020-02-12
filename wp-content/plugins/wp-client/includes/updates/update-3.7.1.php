<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$user_ids = $wpdb->get_col(
    "SELECT u.ID
    FROM {$wpdb->users} u
    WHERE u.ID > 0
    AND u.ID NOT IN
    ( SELECT DISTINCT um.user_id FROM {$wpdb->usermeta} um WHERE um.meta_key = 'wpc_cl_hubpage_id' AND
    um.meta_value > 0 )
    AND u.ID NOT IN
    ( SELECT DISTINCT um.user_id FROM {$wpdb->usermeta} um WHERE um.meta_key = 'to_approve' AND
    um.meta_value = '1' )
    AND u.ID NOT IN
    ( SELECT DISTINCT um.user_id FROM {$wpdb->usermeta} um WHERE um.meta_key = 'wpc_need_pay' AND
    um.meta_value = '1' )
    AND u.ID IN
    ( SELECT DISTINCT um.user_id FROM {$wpdb->usermeta} um WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND
    um.meta_value LIKE '%\"wpc_client\"%' )"
);

if( count( $user_ids ) ) {
    WPC()->pages()->remove_shortcodes();
    $wpc_templates_hubpage = WPC()->get_settings( 'templates_hubpage', '' );
    $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

    //create hub page for the user
    $post = array();
    $post['post_type']      = 'hubpage'; //could be 'page' for example
    $post['post_content']   = html_entity_decode( $wpc_templates_hubpage );
    $post['post_author']    = 1;
    $post['post_status']    = 'publish'; //draft
    $post['comment_status'] = 'closed';
    $post['post_parent']    = 0;
    $post['post_status']    = "publish";

    foreach( $user_ids as $user_id ) {
        $business_name = get_user_meta( $user_id, 'wpc_cl_business_name', true );
        $post['post_title'] = $business_name;
        $postid = wp_insert_post($post);

        if ( 0 < $postid )
            update_user_meta( $user_id, 'wpc_cl_hubpage_id', $postid );

        // add Portal Page for this user
        $wpc_templates_clientpage = WPC()->get_settings( 'templates_clientpage', '' );
        $wpc_templates_clientpage = html_entity_decode( $wpc_templates_clientpage );
        $wpc_templates_clientpage = str_replace( "{client_business_name}", $business_name, $wpc_templates_clientpage );
        $wpc_templates_clientpage = str_replace( "{page_title}", $business_name, $wpc_templates_clientpage );

        if ( !isset( $wpc_clients_staff['create_portal_page'] ) || 'yes' == $wpc_clients_staff['create_portal_page'] ) {

            $clients = array(
                'comment_status'    => 'closed',
                'ping_status'       => 'closed',
                'post_author'       => get_current_user_id(),
                'post_content'      => $wpc_templates_clientpage,
                'post_name'         => $business_name,
                'post_status'       => 'publish',
                'post_title'        => $business_name,
                'post_type'         => 'clientspage'
            );

            $client_page_id = wp_insert_post( $clients );

            //update Ignore Theme Link Pages option
            if( isset( $wpc_clients_staff['use_portal_page_settings'] ) && '1' == $wpc_clients_staff['use_portal_page_settings'] )
                update_post_meta( $client_page_id, '_wpc_use_page_settings', 1 );
            else
                update_post_meta( $client_page_id, '_wpc_use_page_settings', 0 );

            $user_ids = array();
            $user_ids[] = $user_id;
            WPC()->assigns()->set_assigned_data( 'portal_page', $client_page_id, 'client', array( $user_id ) );
        }
    }
}