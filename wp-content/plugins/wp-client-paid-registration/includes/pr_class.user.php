<?php

if ( !class_exists( "WPC_PR_User" ) ) {

    class WPC_PR_User extends WPC_PR_Common {

        /**
        * constructor
        **/
        function __construct() {

            $this->pr_common_construct();

            add_action( 'wpc_client_new_client_registered', array( &$this, 'start_payment_steps' ), 99 );
            add_action( 'wpc_client_need_pay_for_access', array( &$this, 'start_payment_steps' ), 99 );
            add_filter( 'wpc_client_payment_thank_you_page_link', array( &$this, 'payment_thank_you_page_link' ), 10, 2 );

        }


        function payment_thank_you_page_link( $link, $order ) {
            if( isset( $order['function'] ) && $order['function'] == 'registration' ) {
                $wpc_paid_registration = WPC()->get_settings( 'paid_registration' );
                if( !empty( $wpc_paid_registration['autoreturn'] ) ) {
                    $link = $wpc_paid_registration['autoreturn'];
                }
            }
            return $link;
        }


        /*
        * Start payment steps
        */
        function start_payment_steps( $client_id ) {
            global $wpc_payments_core;

            $wpc_paid_registration = WPC()->get_settings( 'paid_registration' );
            //paid registration enabled
            if ( isset( $wpc_paid_registration['enable'] ) && ( '1' == $wpc_paid_registration['enable'] || 'yes' == $wpc_paid_registration['enable'] ) ) {

                if ( 1 != get_user_meta( $client_id, 'wpc_need_pay', true ) || isset( $_GET['wpc_pay'] ) ) {

                    update_user_meta( $client_id, 'wpc_need_pay', '1' );
                    delete_user_meta( $client_id, 'to_approve' );

                    $data = array();
                    $data['item_name'] = $wpc_paid_registration['description'];


                    //get correct currency
                    $wpc_currency = WPC()->get_settings( 'currency' );

                    $currency = 'USD';
                    if ( isset( $wpc_paid_registration['currency'] ) && isset( $wpc_currency[$wpc_paid_registration['currency']]['code'] ) ) {
                        $currency = $wpc_currency[$wpc_paid_registration['currency']]['code'];
                    }

                    //create new order
                    $order_id = $wpc_payments_core->create_order( 'registration', $wpc_paid_registration['cost'], $currency, $client_id, $data );

                    if ( $order_id ) {
                        $order = $wpc_payments_core->get_order_by( $order_id );

                        //make link
                        $name = $wpc_payments_core->step_names[2];
                        if ( WPC()->permalinks ) {
                            $payment_link = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] ."/$name/";
                        } else {
                            $payment_link = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url() );
                        }

                        WPC()->redirect( $payment_link );
                        exit;
                    }

                } else {
                    $payment_link = ( is_ssl() ? 'https://' : 'http://' ) . add_query_arg( array( 'wpc_pay' => '1' ), $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );

                    printf( __( 'Sorry, access is restricted until the payment process is complete. Please click on this link to <a href="%s">Start Payment Process</a>.', WPC_CLIENT_TEXT_DOMAIN ), $payment_link );
                    echo '<br>';
                    _e( 'If you have already made payment, please wait a few minutes and try logging in again.', WPC_CLIENT_TEXT_DOMAIN );

                    exit;
                }

                die( 'error' );
            }

        }


    //end class
    }

}