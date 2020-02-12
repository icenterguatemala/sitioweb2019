<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function wpc_smtp_decrypt_old( $text ) {
    if ( function_exists( 'mcrypt_decrypt' ) ) {
        return trim( @mcrypt_decrypt( MCRYPT_RIJNDAEL_256, DB_PASSWORD, base64_decode( $text ), MCRYPT_MODE_ECB ) );
    } else {
        return strrev( base64_decode( $text ) );
    }
}




//changes after change encrypt/decrypt methods
$email_settings = WPC()->get_settings( 'email_sending' );
if( !empty( $email_settings['smtp']['password'] ) ) {
    $smtp_password = wpc_smtp_decrypt_old( $email_settings['smtp']['password'] );
    $new_password = base64_encode( strrev($smtp_password) );
    if ( $new_password ) {
        $email_settings['smtp']['password'] = $new_password;
        WPC()->settings()->update( $email_settings, 'email_sending' );
    }
}


//Replaces key of 'portal_page' custom title instead 'portal'
$custom_titles = WPC()->custom_titles;
if ( !empty( $custom_titles['portal']['s'] ) ) {
    $custom_titles['portal_page']['s'] = $custom_titles['portal']['s'];
}
if ( !empty( $custom_titles['portal']['p'] ) ) {
    $custom_titles['portal_page']['p'] = $custom_titles['portal']['p'];
}
unset( $custom_titles['portal'] );

WPC()->settings()->update( $custom_titles, 'custom_titles' );


//changes for custom field (added table "view field")
$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
foreach ( $wpc_custom_fields as $key => $val ) {
    $readonly = isset( $val['field_readonly'] ) && '1' == $val['field_readonly'];
    $admin_add = isset( $val['display'] ) && '1' == $val['display'];
    $user_add = isset( $val['display_register'] ) && '1' == $val['display_register'];
    $user_edit = isset( $val['display_user'] ) && '1' == $val['display_user'];

    if ( $admin_add ) {
        $admin_add_cap = $admin_screen_cap = 'edit';
        $admin_edit_cap = $readonly ? 'view' : 'edit' ;
    } else {
        $admin_add_cap = $admin_edit_cap = $admin_screen_cap = 'hide';
    }

    $user_add_cap = $user_add ? 'edit' : 'hide';

    if ( $user_edit ) {
        $user_edit_cap =  $readonly ? 'view' : 'edit' ;
    } else {
        $user_edit_cap = 'hide';
    }

    $view = array(
        'admin_add' => array(
            'administrator' => $admin_add_cap,
            'admin'         => $admin_add_cap,
            'manager'       => $admin_add_cap,
        ),
        'admin_edit' => array(
            'administrator' => $admin_edit_cap,
            'admin'         => $admin_edit_cap,
            'manager'       => $admin_edit_cap,
        ),
        'admin_screen' => array(
            'administrator' => $admin_screen_cap,
            'admin'         => $admin_screen_cap,
            'manager'       => $admin_screen_cap,
        ),

        'user_add' => array(
            'client'        => $user_add_cap,
            'staff'         => $user_add_cap,
        ),
        'user_edit' => array(
            'client'        => $user_edit_cap,
            'staff'         => $user_edit_cap,
        ),
    );

    $wpc_custom_fields[ $key ]['view'] = $view;
}
WPC()->settings()->update( $wpc_custom_fields, 'custom_fields' );



//private messages transfers
global $wpdb;

$sent_from = $wpdb->get_col(
    "SELECT DISTINCT( sent_from )
    FROM {$wpdb->prefix}wpc_client_comments"
);

$sent_to = $wpdb->get_col(
    "SELECT DISTINCT( sent_to )
    FROM {$wpdb->prefix}wpc_client_comments"
);

$already_inserted = array();
if( !empty( $sent_from ) && !empty( $sent_to ) ) {
    foreach( $sent_from as $from_id ) {
        foreach( $sent_to as $to_id ) {
            $messages = $wpdb->get_results( $wpdb->prepare(
                "SELECT *
                FROM {$wpdb->prefix}wpc_client_comments
                WHERE ( sent_from=%s AND sent_to=%s ) OR
                      ( sent_from=%s AND sent_to=%s )",
                $from_id,
                $to_id,
                $to_id,
                $from_id
            ), ARRAY_A );

            if( !empty( $messages ) ) {
                //create chain
                if( !in_array( "$from_id,$to_id", $already_inserted ) &&
                    !in_array( "$to_id,$from_id", $already_inserted ) ) {
                    $wpdb->insert(
                        "{$wpdb->prefix}wpc_client_chains",
                        array(
                            'subject' => addslashes( htmlspecialchars( __( 'No Subject', WPC_CLIENT_TEXT_DOMAIN ) ) ),
                        )
                    );

                    $chain_id = $wpdb->insert_id;

                    //create assigns to chain
                    WPC()->assigns()->set_assigned_data( 'chain', $chain_id, 'client', array( $from_id, $to_id ) );
                    foreach( $messages as $message ) {
                        //create chain's message
                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_messages",
                            array(
                                'chain_id'      => $chain_id,
                                'content'       => $message['comment'],
                                'author_id'     => $message['sent_from'],
                                'date'          => $message['time']
                            )
                        );

                        $message_id = $wpdb->insert_id;

                        //if message new flag
                        if( !empty( $message['new_flag'] ) ) {
                            WPC()->assigns()->set_assigned_data('new_message', $message_id, 'client', $message['sent_to']);
                        }
                    }

                    $already_inserted[] = "$from_id,$to_id";
                    $already_inserted[] = "$to_id,$from_id";
                }
            }
        }
    }
}


//Added more default currencies
WPC()->install()->set_default_currencies( true );

//for disable wizard setup
update_option( 'wpc_wizard_setup', 'false' );