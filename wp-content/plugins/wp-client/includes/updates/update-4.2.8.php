<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

if ( ! empty( $wpc_custom_fields ) ) {
    //get all hidden columns from custom fields
    $hidden_columns = array();
    foreach ( $wpc_custom_fields as $custom_field_name => $custom_field ) {
        if ( empty( $custom_field['display_screen_options'] ) && isset( $custom_field['nature'] ) &&
            ( 'client' == $custom_field['nature'] || 'both' == $custom_field['nature'] ) )
            $hidden_columns[] = $custom_field_name;
    }

    if ( ! empty( $hidden_columns ) ) {
        $args = array(
            'blog_id'      => get_current_blog_id(),
            'role__in'     => array('administrator', 'wpc_manager', 'wpc_admin'),
            'fields'       => 'ids',
        );
        $wpc_user_ids = get_users( $args );

        foreach ( $wpc_user_ids as $user_id ) {
            //get screen option user meta
            $already_hidden_columns = get_user_meta( $user_id, 'managewp-client_page_wpclient_clientscolumnshidden', true );

            //for users which don't set screen options hide all hidden custom fields columns
            if ( empty( $already_hidden_columns ) ) {
                update_user_meta( $user_id, 'managewp-client_page_wpclient_clientscolumnshidden', $hidden_columns );
            }
        }
    }
}