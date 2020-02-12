<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists('paypalExpressGateway') ) {
    class paypalExpressGateway {

        //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
        var $plugin_name = 'paypal-express';

        var $valid_currencies = array(
            'CAD', //Canadian Dollar
            'EUR', //Euro
            'GBP', //British Pound
            'USD', //U.S. Dollar
            'JPY', //Japanese Yen
            'AUD', //Australian Dollar
            'NZD', //New Zealand Dollar
            'CHF', //Swiss Franc
            'HKD', //Hong Kong Dollar
            'SGD', //Singapore Dollar
            'SEK', //Swedish Krona
            'DKK', //Danish Krone
            'PLN', //Polish Zloty
            'NOK', //Norwegian Krone
            'HUF', //Hungarian Forint
            'CZK', //Czech Koruna
            'ILS', //Israeli New Shekel
            'MXN', //Mexican Peso
            'BRL', //Brazilian Real
            'MYR', //Malaysian Ringgit
            'PHP', //Philippine Peso
            'TWD', //New Taiwan Dollar
            'THB', //Thai Baht
            'TRY', //Turkish Lira
            'RUB', //Russian Ruble
        );

        //name of your gateway, for the admin side.
        var $admin_name = '';

        //public name of your gateway, for lists and such.
        var $public_name = '';

        //url for an image for your checkout method. Displayed on checkout form if set
        var $method_img_url = '';

        //url for an submit button image for your checkout method. Displayed on checkout form if set
        var $method_button_img_url = '';

        //whether or not ssl is needed for checkout page
        var $force_ssl = false;

        //has recurring
        var $recurring = true;

        //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
        var $ipn_url;

        //whether if this is the only enabled gateway it can skip the payment_form step
        var $skip_form = true;

        //only required for global capable gateways. The maximum stores that can checkout at once
        var $max_stores = 10;

        // Payment action
        var $payment_action = 'Sale';

        private $step_names;

        //paypal vars
        var $API_Username, $API_Password, $API_Signature, $SandboxFlag, $API_Endpoint, $paypalURL, $version, $currencyCode, $locale;

        function __construct( $gateway = NULL ) {
            global $wpc_payments_core;

            $wpc_gateways = WPC()->get_settings( 'gateways' );

            if ( !isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) || 1 != $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) {
                $this->recurring = false;
            }

            $this->admin_name = __('PayPal Express Checkout', WPC_CLIENT_TEXT_DOMAIN);
            $this->public_name = __('PayPal', WPC_CLIENT_TEXT_DOMAIN);

            if ( isset( $wpc_gateways[ $this->plugin_name ]['public_name'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['public_name'] ) ) {
                $this->public_name = $wpc_gateways[ $this->plugin_name ]['public_name'];
            }


            $this->method_img_url = WPC()->plugin_url . 'images/PayPal_mark.gif';
            $this->method_button_img_url = WPC()->plugin_url . 'images/PayPal_mark.gif';

            if ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ) {
                $this->method_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
                $this->method_button_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
            }

            $this->gateway = $gateway;

            add_action( 'wpc_cancel_subscription_' . $this->plugin_name, array( &$this, 'wpc_cancel_subscription' ) );

            $this->step_names = $wpc_payments_core->step_names;

            return $this->gateway;
        }


        function wpc_cancel_subscription( $payments ) {
            if ( is_numeric( $payments ) )
                $payments = array( $payments );

            if ( !is_array( $payments ) )
                return '';

            global $wpdb, $wpc_payments_core;
            $data = $wpdb->get_results( "SELECT `id`, `subscription_id` "
                . "FROM {$wpdb->prefix}wpc_client_payments "
                . "WHERE id IN ('" . implode( "','", $payments ) . "') "
                . "GROUP BY `subscription_id`" , ARRAY_A );
            foreach ( $data as $plan ) {
                $subscription_id = $plan['subscription_id'];
                $this->subscription_cancel( $subscription_id );
                $wpc_payments_core->update_payments_of_recurring( $subscription_id, $this->plugin_name, 'canceled' );
            }
        }


        function payment_process( &$order, $step = 3 ) {
            $content = '';

            switch( $step ) {
                case 3: {
                    $settings = WPC()->get_settings( 'gateways' );


                    $this->API_UserName = trim( $settings[ $this->plugin_name ]['api_user'] );
                    $this->API_Password = trim( $settings[ $this->plugin_name ]['api_pass'] );
                    $this->API_Signature = trim( $settings[ $this->plugin_name ]['api_sig'] );


                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                    $content .= '<span class="wpc_notice wpc_info" style="float:left;display:block;margin:0 0 20px 0;">' . __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'] . '</span>';

                    //empty API
                    if ( empty( $this->API_UserName ) || empty( $this->API_Password ) || empty( $this->API_Signature ) ) {
                        //make link
                        $name = $this->step_names[2];
                        if ( WPC()->permalinks ) {
                            $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                        }

                        $content .= '<br /><br />';
                        $content .= __( 'API are empty.', WPC_CLIENT_TEXT_DOMAIN ) . ' ';
                        $content .= '<a href="' . $redirect . '">' . __( 'Return', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

                        break;
                    }

                    if( 'recurring' == $order['payment_type'] ) {
                        $result = $this->subscribe( $order );
                    } else {
                        $this->setExpressCheckout( $order );
                    }

                    break;
                }

                case 4: {
                    if( isset( $_GET['token'] ) && isset( $_GET['PayerID'] ) ) {

                        if( 'recurring' == $order['payment_type'] ) {
                            $result = $this->subscribe( $order );
                        } else {
                            $result = $this->charge( $order );
                        }

                        if( $result ) {
                            //make link
                            $name = $this->step_names[5];
                            if ( WPC()->permalinks ) {
                                $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                            }

                            /*our_hook_
                                hook_name: wpc_client_payment_thank_you_page_link
                                hook_title: Link for thank you page
                                hook_description: Hook runs before redirect to thank you page.
                                hook_type: filter
                                hook_in: wp-client
                                hook_location payment gateways classes
                                hook_param: string $redirect, array $order
                                hook_since: 3.8.8
                            */
                            $redirect = apply_filters( 'wpc_client_payment_thank_you_page_link', $redirect, $order );
                        } else {
                            //make link
                            $name = $this->step_names[3];
                            if ( WPC()->permalinks ) {
                                $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1";
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1 ), get_home_url() );
                            }
                        }

                        WPC()->redirect( $redirect );

                    } else {
                        die('Are you hacker?');
                    }
                    break;
                }

                case 5: {
                    global $wpc_payments_core;
                    $content .= __( 'Thank you for the payment.', WPC_CLIENT_TEXT_DOMAIN );
                    $content .= ' ';
                    $content .= $wpc_payments_core->get_continue_link( $order, true );
                    break;
                }
            }

            /*our_hook_
                hook_name: wpc_client_gateway_step_content
                hook_title: Filter Step Content of payment gateways
                hook_description: Hook runs before return payment gateways step content.
                hook_type: filter
                hook_in: wp-client
                hook_location gateways files
                hook_param: string $content, string $gateway_name, string $step, array $order
                hook_since: 3.9.8
            */
            $content = apply_filters( 'wpc_client_gateway_step_content', $content, $this->plugin_name, $step, $order );

            return $content;
        }


        //PayPal Express, this is run first to authorize from PayPal
        function setExpressCheckout( &$order ) {
            $data       = isset( $order['data'] ) ? json_decode( $order['data'], true ) : array();
            $settings   = WPC()->get_settings( 'gateways' );
            $amount     = isset( $order['amount'] ) ? $order['amount'] : $data['a1'];
            $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );


            //make link
            if ( WPC()->permalinks ) {
                $ipn_url = WPC()->make_url( '/wpc-ipn-handler-url/' . $order['order_id'] . '/', get_home_url() );
            } else {
                $ipn_url = add_query_arg( array( 'wpc_page' => 'payment_ipn', 'wpc_page_value' => $order['order_id'] ), get_home_url() );
            }

            //make link
            $name = $this->step_names[4];
            if ( WPC()->permalinks ) {
                $return_url = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
            } else {
                $return_url = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url() );
            }


            if ( isset( $order['currency'] ) && '' != $order['currency'] ) {
                $currency = $order['currency'];
            } elseif( isset( $settings[ $this->plugin_name ]['currency'] ) ) {
                $currency = $settings[ $this->plugin_name ]['currency'];
            } else {
                $currency = 'USD';
            }


            //paypal profile stuff
            $nvpStr = "";

            $nvpStr .="&CURRENCYCODE=" . $currency;

            if( isset( $data['a1'] ) ) {
                $nvpStr .= "&BILLINGPERIOD=" . urlencode( ucfirst( $data['t3'] ) );
                $nvpStr .= "&BILLINGFREQUENCY=" . $data['p3'];
                $nvpStr .= "&L_BILLINGTYPE0=RecurringPayments";
                $nvpStr .= "&TOTALBILLINGCYCLES=" . $data['c'];
                $nvpStr .= "&AUTOBILLAMT=AddToNextBilling";
            }

            $nvpStr .= "&DESC=" . urlencode( substr( $item_name, 0, 127 ) );
            $nvpStr .= "&NOSHIPPING=1";
            $nvpStr .= "&PAYMENTREQUEST_0_CUSTOM=" . $order['id'];

            $nvpStr .= "&AMT=" . $amount;
            $nvpStr .= "&L_BILLINGAGREEMENTDESCRIPTION0=" . urlencode( substr( $item_name, 0, 127 ) );
            $nvpStr .= "&L_PAYMENTTYPE0=Any";
            $nvpStr .= "&SOLUTIONTYPE=Sole";

            $nvpStr .= '&ReturnUrl=' . $return_url;

            $nvpStr .= "&NOTIFYURL=" . $ipn_url;

            //todo - fix url
            $nvpStr .= "&CANCELURL=" . get_home_url();

            $httpParsedResponseAr = $this->PPHttpPost('SetExpressCheckout', $nvpStr);

            if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
                //redirect to paypal
                $settings = WPC()->get_settings( 'gateways' );
                if( isset( $settings[ $this->plugin_name ]['mode'] ) && $settings[ $this->plugin_name ]['mode'] ) {
                    $paypal_url = "https://www.sandbox.paypal.com/webscr&useraction=commit&cmd=_express-checkout&token="  . $httpParsedResponseAr['TOKEN'];
                } else {
                    $paypal_url = "https://www.paypal.com/webscr&cmd=_express-checkout&useraction=commit&token=" . $httpParsedResponseAr['TOKEN'];
                }

                WPC()->redirect( $paypal_url );

            } else {
                echo !empty( $httpParsedResponseAr['L_SHORTMESSAGE0'] ) ? urldecode( $httpParsedResponseAr['L_SHORTMESSAGE0'] ) : '';
                echo !empty( $httpParsedResponseAr['L_LONGMESSAGE0'] ) ? ' - ' . urldecode( $httpParsedResponseAr['L_LONGMESSAGE0'] ) : '';

                return false;
            }
        }


        function charge( &$order ) {
            $data       = json_decode( $order['data'], true );
            $settings   = WPC()->get_settings( 'gateways' );
            $amount     = isset( $order['amount'] ) ? $order['amount'] : $data['a1'];
            $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

            if ( isset( $order['currency'] ) && '' != $order['currency'] ) {
                $currency = $order['currency'];
            } elseif( isset( $settings[ $this->plugin_name ]['currency'] ) ) {
                $currency = $settings[ $this->plugin_name ]['currency'];
            } else {
                $currency = 'USD';
            }

            //make link
            if ( WPC()->permalinks ) {
                $ipn_url = WPC()->make_url( '/wpc-ipn-handler-url/' . $order['order_id'] . '/', get_home_url() );
            } else {
                $ipn_url = add_query_arg( array( 'wpc_page' => 'payment_ipn', 'wpc_page_value' => $order['order_id'] ), get_home_url() );
            }


            $payment_details = $this->getExpressCheckoutDetails( $_GET['token'] );

            //paypal profile stuff
            $nvpStr = "";

            $nvpStr .="&CURRENCYCODE=" . $currency;
            $nvpStr .= "&DESC=" . urlencode( substr( $item_name, 0, 127 ) );
            $nvpStr .= "&NOSHIPPING=1";
            $nvpStr .= "&PAYMENTREQUEST_0_CUSTOM=" . $order['id'];

            if( isset( $_GET['token'] ) && !empty( $_GET['token'] ) )
                $nvpStr .= "&TOKEN=" . $_GET['token'];

            $nvpStr .= "&AMT=" . $amount;

            $nvpStr .= "&PAYERID=" . $_GET['PayerID'] . "&PAYMENTACTION=sale";
            $nvpStr .= "&NOTIFYURL=" . $ipn_url;

            $httpParsedResponseAr = $this->PPHttpPost('DoExpressCheckoutPayment', $nvpStr);

            if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
                return true;
            } else  {
                return false;
            }
        }

        function subscribe( &$order ) {
            $data               = json_decode( $order['data'], true );
            $settings           = WPC()->get_settings( 'gateways' );

            if ( isset( $order['currency'] ) && '' != $order['currency'] ) {
                $currency = $order['currency'];
            } elseif( isset( $settings[ $this->plugin_name ]['currency'] ) ) {
                $currency = $settings[ $this->plugin_name ]['currency'];
            } else {
                $currency = 'USD';
            }

            $init_amount        = ( isset( $data['a1'] ) && is_numeric( $data['a1'] ) ) ? $data['a1'] : '';
            $amount             = isset( $data['a3'] ) ? $data['a3'] : 0;
            $item_name          = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );


            if( isset( $settings[ $this->plugin_name ]['mode'] ) && $settings[ $this->plugin_name ]['mode'] ) {
                $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
            } else {
                $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
            }

            //make link
            if ( WPC()->permalinks ) {
                $ipn_url = WPC()->make_url( '/wpc-ipn-handler-url/' . $order['order_id'] . '/', get_home_url() );
            } else {
                $ipn_url = add_query_arg( array( 'wpc_page' => 'payment_ipn', 'wpc_page_value' => $order['order_id'] ), get_home_url() );
            }

            //make link
            $name = $this->step_names[5];
            if ( WPC()->permalinks ) {
                $return_url = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
            } else {
                $return_url = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
            }

            /*our_hook_
              hook_name: wpc_client_payment_thank_you_page_link
              hook_title: Link for thank you page
              hook_description: Hook runs before redirect to thank you page.
              hook_type: filter
              hook_in: wp-client
              hook_location payment gateways classes
              hook_param: string $redirect, array $order
              hook_since: 3.8.8
            */
            $return_url = apply_filters( 'wpc_client_payment_thank_you_page_link', $return_url, $order );


          ?>

          <form action="<?php echo $paypal_url ?>" method="post" id="wpc_paypal_sub_f">
            <input type="hidden" name="cmd" value="_xclick-subscriptions">
            <input type="hidden" name="business" value="<?php echo $settings[ $this->plugin_name ]['merchant_email'] ?>">
            <input type="hidden" name="currency_code" value="<?php echo $currency ?>">
            <input type="hidden" name="no_shipping" value="1">

            <?php if ( is_numeric( $init_amount ) ) { ?>
            <input type="hidden" name="a1" value="<?php echo $init_amount ?>">
            <input type="hidden" name="p1" value="<?php echo $data['p3'] ?>">
            <input type="hidden" name="t1" value="<?php echo substr( ucfirst( $data['t3'] ), 0, 1 ) ?>">
            <?php } ?>

            <input type="hidden" name="a3" value="<?php echo $amount ?>">
            <input type="hidden" name="p3" value="<?php echo $data['p3'] ?>">
            <input type="hidden" name="t3" value="<?php echo substr( ucfirst( $data['t3'] ), 0, 1 ) ?>">
            <?php
            if (  isset( $data['c'] ) ) {
                ?>
                <input type="hidden" name="srt" value="<?php echo $data['c'] ?>">
                <?php
            }
            ?>
            <input type="hidden" name="item_name" value="<?php echo $item_name ?>" />
            <input type="hidden" name="return" value="<?php echo $return_url ?>" />
            <input type="hidden" name="cancel_return" value="<?php echo get_home_url() ?>" />
            <input type="hidden" name="notify_url" value="<?php echo $ipn_url ?>" />
            <input type="hidden" name="src" value="1">
            <input type="hidden" name="sra" value="1">
          </form>

          <script type="text/javascript">
            jQuery( '#wpc_paypal_sub_f' ).submit();
          </script>

          <?php
        }


        function check_ipn_notification( $payment_data ) {
            $settings = WPC()->get_settings( 'gateways' );
            $mode = ( isset( $settings[ $this->plugin_name ]['mode'] ) && $settings[ $this->plugin_name ]['mode'] ) ? 'sandbox.' : '';

            $postdata = "";
            foreach ( $payment_data as $k=>$value ) $postdata .= $k . "=" . urlencode( stripslashes( $value ) ) . "&";
            $postdata .= "cmd=_notify-validate";


            $response = wp_remote_get( "https://www.{$mode}paypal.com/cgi-bin/webscr/" . $postdata );

            if ( !empty( $response['body'] ) ) {
                if ( is_array( $response['body'] ) ) {
                    $response['body'] = json_encode( $response['body'] );
                }
                if ( $response['body'] == "VERIFIED" ) return true;
            }

            return false;
        }


        function data_verification( $payment_data, $order_id ) {
            global $wpc_payments_core;
            $settings = WPC()->get_settings( 'gateways' );

            $API_Merchant = trim( $settings[ $this->plugin_name ]['merchant_email'] );

            if ( strtolower( $payment_data['receiver_email'] ) != strtolower( $API_Merchant ) ) {
                $wpc_payments_core->status_on_hold( $order_id, sprintf( __( 'Validation error: PayPal IPN response from a different email address (%s).', WPC_CLIENT_TEXT_DOMAIN ), $payment_data['receiver_email'] ) );
                return false;
            }

            $valid_txn_types = array(
                'recurring_payment_suspended',
                'recurring_payment_profile_created',
                'recurring_payment_profile_cancel',
                'recurring_payment',
                'subscr_signup',
                'subscr_cancel',
                'subscr_payment',
                'web_accept',
                'reversal',
                'express_checkout'
            );

            $valid_payment_status = array(
                'reversed',
                'refunded',
            );

            if ( !( ( isset( $payment_data["txn_type"] ) && in_array( $payment_data["txn_type"], $valid_txn_types ) )
                    || ( isset( $payment_data["payment_status"] ) && in_array( strtolower( $payment_data["payment_status"] ), $valid_payment_status ) ) ) ) {

                $wpc_payments_core->status_on_hold( $order_id, sprintf( __( 'Validation error: PayPal IPN response invalid txn_type - (%s) or invalid payment_staus(%s).', WPC_CLIENT_TEXT_DOMAIN ), $payment_data["txn_type"], $payment_data["payment_status"] ) );
                return false;
            }

            return true;
        }



        /*
        *
        */
        function _ipn( $order ) {
            //listen for gateway IPN returns and tie them in to proper gateway plugin
            if ( isset( $_POST ) && $order ) {

                global $wpc_payments_core;

                if( !$this->check_ipn_notification( $_POST ) ) {
                    wp_die( 'PayPal IPN Request Failure', 'PayPal IPN', array( 'response' => 500 ) );
                }


                if( !$this->data_verification( $_POST, $order['id'] ) ) {
                    die('IPN verification failed 2');
                }

                $payment_data = array();
                $payment_data['transaction_status'] = isset( $_POST["payment_status"] ) ? $_POST["payment_status"] : '';
                $payment_data['transaction_id'] = null;
                $payment_data['subscription_id'] = null;
                $payment_data['subscription_status'] = null;
                $payment_data['parent_txn_id'] = null;
                $payment_data['transaction_type'] = '';

                $_POST["txn_type"] = isset( $_POST["txn_type"] ) ? $_POST["txn_type"] : '';

                switch( $_POST["txn_type"] ) {
                    case 'recurring_payment':
                        $payment_data['transaction_type'] = 'subscription_start';
                        $payment_data['transaction_id'] = $_POST['txn_id'];
                        $payment_data['subscription_id'] = isset( $order['subscription_id'] ) ? $order['subscription_id'] : null;

                        if ( 'Completed' == $payment_data['transaction_status'] ) {
                            $payment_data['subscription_status'] = 'active';
                        } else {
                            $payment_data['subscription_status'] = 'pending';
                        }

                        break;

                    case 'subscr_signup':
                        $payment_data['transaction_type']   = 'subscription_start';
                        $payment_data['subscription_id']    = $_POST['subscr_id'];
                        break;

                    case 'subscr_cancel': //seems is deprecated
                    case 'recurring_payment_profile_cancel':
                        $payment_data['transaction_type']       = 'subscription_canceled';
                        $payment_data['subscription_id']        = $_POST['subscr_id'];
                        $payment_data['subscription_status']    = 'canceled';
    //                    $payment_data['transaction_id']     = $_POST['txn_id'];
                        break;

                    case 'recurring_payment_suspended':
                        $payment_data['transaction_type']       = 'subscription_suspended';
                        $payment_data['subscription_id']        = $_POST['recurring_payment_id'];
                        $payment_data['subscription_status']    = 'suspended';
    //                    $payment_data['transaction_id']     = $_POST['txn_id'];
                        break;

                    case 'subscr_payment':
                        $subscription_id = isset( $_POST['subscr_id'] ) ? $_POST['subscr_id'] : '';
                        $payment_data['transaction_type']   = 'subscription_payment';
                        $payment_data['transaction_id']     = $_POST['txn_id'];
                        $payment_data['subscription_id']    = $subscription_id;
                        $payment_data['amount']             = !empty( $_POST['amount'] ) ? $_POST['amount'] : '';

                        if ( 'Completed' == $payment_data['transaction_status'] ) {
                            $payment_data['subscription_status'] = 'active';
                        } else {
                            $payment_data['subscription_status'] = 'pending';
                        }

                        break;

                    case 'web_accept':
                    case 'express_checkout':

                        if ( 'Completed' == $payment_data['transaction_status'] ) {
                            $payment_data['transaction_type'] = 'paid';
                        } else {
                            $payment_data['transaction_type'] = 'pending';
                        }

                        $payment_data['transaction_id'] = $_POST['txn_id'];
                        break;

                    default:
                        if( 'refunded' == strtolower( $payment_data['transaction_status'] ) ) {

                            $payment_data['transaction_type'] = 'refunded';
                            $payment_data['transaction_id'] = $_POST['parent_txn_id'];

                            if ( isset( $_POST['subscr_id'] ) ) {
                                $payment_data['subscription_id']        = $_POST['subscr_id'];
                                $payment_data['subscription_status']    = 'canceled';
                            }

                        }
                }







                if( isset($_GET['debug']) ) {
                    var_dump( $payment_data );
    //              exit;

                }

                $wpc_payments_core->order_update( $order['id'], $payment_data );

                if ( 'subscr_payment' == $_POST["txn_type"] ) {
                    $function = ( !empty( $order['function'] ) ) ? $order['function'] : '';
                    $expire_subscription = apply_filters( 'wpc_client_check_expire_subscription_'. $function
                            , '', $order );

                    if ( $expire_subscription ) {
                        $wpc_payments_core->update_payments_of_recurring( $subscription_id, $this->plugin_name, 'expired' );
                    }
                }

            }
        }




		function getExpressCheckoutDetails( $token )
		{
			$nvpStr="&TOKEN=" . $token;

			/* Make the API call and store the results in an array.  If the
			call was a success, show the authorization details, and provide
			an action to complete the payment.  If failed, show the error
			*/
			$httpParsedResponseAr = $this->PPHttpPost('GetExpressCheckoutDetails', $nvpStr);
			if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
				return $httpParsedResponseAr;
			} else  {
				return false;
			}
		}


		function subscription_cancel( $subscription_id )
		{
			//paypal profile stuff
			$nvpStr = "";
			$nvpStr .= "&PROFILEID=" . $subscription_id . "&ACTION=Cancel&NOTE=Recurring profile was deleted.";

			$this->httpParsedResponseAr = $this->PPHttpPost('ManageRecurringPaymentsProfileStatus', $nvpStr);

			if( "SUCCESS" == strtoupper($this->httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($this->httpParsedResponseAr["ACK"]) || $this->httpParsedResponseAr['L_ERRORCODE0'] == "11556") {
//				$order->updateStatus("cancelled");
				return true;
				//exit('CreateRecurringPaymentsProfile Completed Successfully: '.print_r($this->httpParsedResponseAr, true));
			} else  {
//				$order->status = "error";
//				$order->errorcode = $this->httpParsedResponseAr['L_ERRORCODE0'];
//				$order->error = urldecode($this->httpParsedResponseAr['L_LONGMESSAGE0']);
//				$order->shorterror = urldecode($this->httpParsedResponseAr['L_SHORTMESSAGE0']);
				return false;
				//exit('CreateRecurringPaymentsProfile failed: ' . print_r($httpParsedResponseAr, true));
			}
		}

		/**
		 * PAYPAL Function
		 * Send HTTP POST Request
		 *
		 * @param	string	The API method name
		 * @param	string	The POST Message fields in &name=value pair format
		 * @return	array	Parsed HTTP Response body
		 */
		function PPHttpPost($methodName_, $nvpStr_) {
			$settings = WPC()->get_settings( 'gateways' );

			$API_UserName = trim( $settings[ $this->plugin_name ]['api_user'] );
			$API_Password = trim( $settings[ $this->plugin_name ]['api_pass'] );
			$API_Signature = trim( $settings[ $this->plugin_name ]['api_sig'] );
            if( isset( $settings[ $this->plugin_name ]['mode'] ) && $settings[ $this->plugin_name ]['mode'] ) {
                $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            } else {
                $API_Endpoint = "https://api-3t.paypal.com/nvp";
            }

			$version = urlencode('80.0');

			// setting the curl parameters.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);

			// turning off the server and peer verification(TrustManager Concept).
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                        curl_setopt($ch, CURLOPT_SSLVERSION, 6);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);

			// NVPRequest for submitting to server
			$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

			// setting the nvpreq as POST FIELD to curl
			curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

			// getting response from server
			$httpResponse = curl_exec($ch);
			if(!$httpResponse) {
                            if( 35 === curl_errno($ch)) {
                                curl_setopt($ch, CURLOPT_SSLVERSION, 0);
                                $httpResponse = curl_exec($ch);
                            }
                            if(!$httpResponse) {
				exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
                            }
			}

			// Extract the RefundTransaction response details
			$httpResponseAr = explode("&", $httpResponse);

			$httpParsedResponseAr = array();
			foreach ($httpResponseAr as $i => $value) {
				$tmpAr = explode("=", $value);
				if(sizeof($tmpAr) > 1) {
					$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
				}
			}

			if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
				exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
			}

			return $httpParsedResponseAr;
		}


        /*
         * HTML for gateway settings form
         */
        function create_settings_form( $wpc_gateways = array() ) {
            $sel_currency = isset( $wpc_gateways[ $this->plugin_name ]['currency'] ) ? $wpc_gateways[ $this->plugin_name ]['currency'] : 'USD';

            $currencies = array(
                'AUD' => 'AUD - Australian Dollar',
                'BRL' => 'BRL - Brazilian Real',
                'CAD' => 'CAD - Canadian Dollar',
                'CHF' => 'CHF - Swiss Franc',
                'CZK' => 'CZK - Czech Koruna',
                'DKK' => 'DKK - Danish Krone',
                'EUR' => 'EUR - Euro',
                'GBP' => 'GBP - Pound Sterling',
                'ILS' => 'ILS - Israeli Shekel',
                'HKD' => 'HKD - Hong Kong Dollar',
                'HUF' => 'HUF - Hungarian Forint',
                'JPY' => 'JPY - Japanese Yen',
                'MYR' => 'MYR - Malaysian Ringgits',
                'MXN' => 'MXN - Mexican Peso',
                'NOK' => 'NOK - Norwegian Krone',
                'NZD' => 'NZD - New Zealand Dollar',
                'PHP' => 'PHP - Philippine Pesos',
                'PLN' => 'PLN - Polish Zloty',
                'SEK' => 'SEK - Swedish Krona',
                'SGD' => 'SGD - Singapore Dollar',
                'TWD' => 'TWD - Taiwan New Dollars',
                'THB' => 'THB - Thai Baht',
                'TRY' => 'TRY - Turkish lira',
                'USD' => 'USD - U.S. Dollar'
            );
            $currency_str = '';
            foreach ($currencies as $k => $v) {
                $currency_str .= '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected="selected"' : '') . '>' . esc_html($v, true) . '</option>' . "\n";
            }

            ?>

            <div id="wpc_<?php echo $this->plugin_name ?>" class="postbox">
                <h3 class='hndle'><span><?php _e('PayPal Express Checkout Settings', WPC_CLIENT_TEXT_DOMAIN) ?></span> - <span class="description"><?php _e('makes it easy to start accepting credit cards directly on your site with full PCI compliance', WPC_CLIENT_TEXT_DOMAIN); ?></span></h3>
                <div class="inside">
                     <span class="description"><?php _e('Express Checkout is PayPal\'s premier checkout solution, which streamlines the checkout process for buyers and keeps them on your site after making a purchase. Unlike PayPal Pro, there are no additional fees to use Express Checkout, though you may need to do a free upgrade to a business account. <a target="_blank" href="https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECGettingStarted">More Info &raquo;</a>', WPC_CLIENT_TEXT_DOMAIN) ?></span>
                     <table class="form-table">
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Developer/Sandbox Mode", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <input class="wpc_ibutton" name="wpc_gateway[<?php echo $this->plugin_name ?>][mode]" value="1" type="checkbox" <?php echo ( ( isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) && $wpc_gateways[ $this->plugin_name ]['mode'] == 1 ) ? 'checked="checked"' : '' ) ?> />
                                <div class="clear"></div>
                                <span style="float: left; font-size: 11px;" class="description"><?php _e( "Only checked this if you've provided Sandbox credentials above.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Allow Recurring", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <label>
                                    <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][allow_recurring]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) && $wpc_gateways[ $this->plugin_name ]['allow_recurring'], '1' ) ?> value="1" />
                                    <?php _e( 'Allow to use this gateway for recurring payments', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "PayPal Merchant E-mail", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <input type="text" name="wpc_gateway[<?php echo $this->plugin_name ?>][merchant_email]" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['merchant_email'] ) ? $wpc_gateways[ $this->plugin_name ]['merchant_email'] : '' ) ?>" />
                                <div class="clear"></div>
                                <span style="float: left; font-size: 11px;" class="description"><?php _e( "Enter the email address you've associated with your PayPal Business account.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "PayPal API Credentials", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <span class="description"><?php _e( 'You must login to PayPal and create an API signature to get your credentials.', WPC_CLIENT_TEXT_DOMAIN ) ?> <a target="_blank" href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&amp;content_ID=developer/e_howto_api_ECAPICredentials"><?php _e( 'Instructions', WPC_CLIENT_TEXT_DOMAIN ) ?> »</a></span>
                                <p>
                                    <label><?php _e('API Username', WPC_CLIENT_TEXT_DOMAIN ) ?><br />
                                        <input size="30" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_user]" type="text" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['api_user'] ) ? $wpc_gateways[ $this->plugin_name ]['api_user'] : '' ) ?>" />
                                    </label>
                                </p>
                                <p>
                                    <label><?php _e('API Password', WPC_CLIENT_TEXT_DOMAIN ) ?><br />
                                        <input size="20" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_pass]" type="text" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['api_pass'] ) ? $wpc_gateways[ $this->plugin_name ]['api_pass'] : '' ) ?>" />
                                    </label>
                                </p>
                                <p>
                                    <label><?php _e('Signature', WPC_CLIENT_TEXT_DOMAIN ) ?><br />
                                        <input size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_sig]" type="text" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['api_sig'] ) ? $wpc_gateways[ $this->plugin_name ]['api_sig'] : '' ) ?>" />
                                    </label>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Currency", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <select name="wpc_gateway[<?php echo $this->plugin_name ?>][currency]" class="form_data"><?php echo $currency_str ?></select>
                                <div class="clear"></div>
                                <span style="float: left; font-size: 11px;" class="description"><?php _e( "This currency will used for your transactions.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Public Name", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                 <input size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][public_name]" type="text" class="form_data" value="<?php echo $this->public_name ?>" />
                                <span style="float: left; font-size: 11px;" class="description"><?php printf( __( '%s will see this during "Choose Gateway" checkout step', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Icon URL", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                 <input size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][icon_url]" type="text" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ? $wpc_gateways[ $this->plugin_name ]['icon_url'] : '' ) ?>" />
                                <span style="float: left; font-size: 11px;" class="description"><?php printf( __( '%s will see this during "Choose Gateway" checkout step', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">&nbsp;</th>
                            <td>
                                <p class="submit">
                                    <input type="hidden" name="key" value="<?php echo $this->plugin_name; ?>" />
                                    <input type="submit" name="submit_settings" class="button-primary" value="<?php _e('Update Settings', WPC_CLIENT_TEXT_DOMAIN) ?>" />
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>


        <?php
        }

	}

    //register shipping plugin
    wpc_register_gateway_plugin( 'paypalExpressGateway', 'paypal-express', __('PayPal Express Checkout', WPC_CLIENT_TEXT_DOMAIN) );
}