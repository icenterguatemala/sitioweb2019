<?php
if ( !class_exists( 'WPC_LL_Common' ) ) {

    class WPC_LL_Common {

        var $extension_dir;
        var $extension_url;

        /**
        * PHP 5 constructor
        **/
        function common_construct() {

            $this->extension_dir = WPC()->extensions()->get_dir( 'll' );
            $this->extension_url = WPC()->extensions()->get_url( 'll' );

            add_filter( 'wp_client_capabilities_maps', array( &$this, 'capabilities_map' ) );


            add_action( 'wp_login', array( &$this, 'login_successful'), 1 );
            add_action( 'wp_login_failed', array( &$this, 'login_failed'), 1 );
        }


        function capabilities_map( $cap = array() ) {
            $cap['wpc_manager']['variable']['wpc_show_login_logs'] = array( 'cap' => false, 'label' => __( 'Show Login Logs', WPC_CLIENT_TEXT_DOMAIN ) );
            return $cap;
        }


        /*
        * when login successful
        */
        function login_successful( $username ) {
            global $wpdb;

            $ip = !empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

            $login_from = serialize( array(
                'referer'       => !empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
                'request_uri'   => !empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '',
                'script_url'    => !empty( $_SERVER['SCRIPT_URL'] ) ? $_SERVER['SCRIPT_URL'] : '',
            ) );

            $user = get_user_by( 'login', $username );
            if ( $user ) {
                $wpdb->insert( "{$wpdb->prefix}wpc_client_login_logs", array( 'id' => null, 'user_id' => $user->ID, 'login_time' => time(), 'ip_address' => $ip, 'login_from' => $login_from, 'status' => 'Log in' ) );
            }
        }


        /*
        * when login failed
        */
        function login_failed( $username ) {
            global $wpdb;

            $ip = !empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

            $login_from = serialize( array(
                'referer'       => !empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
                'request_uri'   => !empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '',
                'script_url'    => !empty( $_SERVER['SCRIPT_URL'] ) ? $_SERVER['SCRIPT_URL'] : '',
            ) );

            $user = get_user_by( 'login', $username );
            if( $user ) {
                $wpdb->insert( "{$wpdb->prefix}wpc_client_login_logs", array( 'id' => null, 'user_id'=> $user->ID, 'login_time' => time(), 'ip_address' => $ip, 'login_from' => $login_from, 'status' => 'Incorrect Password' ) );
            }
        }

        //end class
    }
}
