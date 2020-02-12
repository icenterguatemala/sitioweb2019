<?php


if ( !class_exists( "WPC_PR_Common" ) ) {

    class WPC_PR_Common {

        var $extension_dir;
        var $extension_url;
        /**
        * constructor
        **/
        function pr_common_construct() {

            $this->extension_dir = WPC()->extensions()->get_dir( 'pr' );
            $this->extension_url = WPC()->extensions()->get_url( 'pr' );


            //get continue link for payment
            add_filter( 'wpc_payment_get_continue_link_registration', array( &$this, 'get_continue_link' ), 99, 3 );

            //get active payment gateways
            add_filter( 'wpc_payment_get_activate_gateways_registration', array( &$this, 'get_active_payment_gateways' ), 99 );

            add_action( 'wpc_client_payment_paid_registration', array( &$this, 'order_paid' ) );

        }


        /**
         * get Continue link after payment
         **/
        function get_continue_link( $link, $order, $with_text = true ) {
            if ( $with_text ) {
                $link = sprintf( __( 'To continue click <a href="%s">here</a>.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->get_slug( 'login_page_id' ) );
            } else {
                $link = WPC()->get_slug( 'login_page_id' );
            }

            return $link;
        }


        /**
         * get active payment gateways
         **/
        function get_active_payment_gateways( $gateways ) {
            $wpc_gateways = WPC()->get_settings( 'gateways' );
            $wpc_paid_registration = WPC()->get_settings( 'paid_registration' );

            if( isset( $wpc_gateways['allowed'] ) && is_array( $wpc_gateways['allowed'] ) ) {
                foreach( $wpc_gateways['allowed'] as $value ) {
                    if ( isset( $wpc_paid_registration['gateways'][$value] ) && ( '1' == $wpc_paid_registration['gateways'][$value] ) || 'yes' == $wpc_paid_registration['gateways'][$value] ) {
                        $gateways[] = $value;
                    }
                }
            }

            return $gateways;
        }


        /**
         * order paid
         **/
        function order_paid( $order ) {
            update_user_meta( $order['client_id'], 'wpc_paid', '1' );
            delete_user_meta( $order['client_id'], 'wpc_need_pay' );
        }


    //end class
    }
}

