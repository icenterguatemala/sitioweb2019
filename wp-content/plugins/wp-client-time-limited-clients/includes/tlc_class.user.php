<?php

if ( !class_exists( "WPC_TLC_User" ) ) {

    class WPC_TLC_User extends WPC_TLC_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {
            add_filter( 'authenticate', array( &$this, 'check_expiration_date' ), 99, 3 );
            add_filter( 'wpc_client_replace_placeholders', array( &$this, '_replace_placeholders' ), 10, 3 );
        }


        function _replace_placeholders( $ph_data, $args, $label ) {
            $client_id = '';
            if ( isset( $args['client_id'] ) && 0 < $args['client_id'] ) {
                $client_id = $args['client_id'];
            } else if ( get_current_user_id() && ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) ) {
                $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
            }

            $expiration_date = get_user_meta( $client_id, 'wpc_expiration_date', true );

            $ph_data['{client_expiration_date}'] = !empty( $expiration_date ) ? WPC()->date_format( $expiration_date ) : '';

            return $ph_data;
        }


        /*
        * Check expiration date of client
        */
        function check_expiration_date( $nul, $username, $password ) {
            //get user
            $user = get_user_by( 'login', $username );

            //check that user exist
            if ( isset( $user->ID ) && 0 < $user->ID ) {
                //check that password is correct
                if ( wp_check_password( $password, $user->user_pass, $user->ID ) ) {

                    $parent_client_id = get_user_meta( $user->ID, 'parent_client_id', true );

                    $user_id = $user->ID;
                    if ( is_numeric( $parent_client_id ) && 0 < $parent_client_id )
                        $user_id = $parent_client_id;

                    //get expiration date
                    $wpc_expiration_time = get_user_meta( $user_id, 'wpc_expiration_date', true );
                    //chack expiration date with now
                    if ( false != $wpc_expiration_time && time() > $wpc_expiration_time ) {
                        //set error message
                        add_filter( 'login_errors', array( &$this, 'login_errors_text'), 99 );

                        //return null for stop login
                        return null;
                    }
                }
            }

            //run authenticate function for corect login
            return $nul;
        }


        /*
        * Set error message
        */
        function login_errors_text( $errors ) {
            $wpc_time_limited_clients = WPC()->get_settings( 'time_limited_clients' );
            return ( isset( $wpc_time_limited_clients['tlc_error_text'] ) ) ? $wpc_time_limited_clients['tlc_error_text'] : __( 'Sorry, your access permission has expired', WPC_CLIENT_TEXT_DOMAIN );
        }


    //end class
    }

}