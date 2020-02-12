<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'WPC_Gateway_AuthorizeNet_AIM' ) ) {
    class WPC_Gateway_AuthorizeNet_AIM {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'authorizenet-aim';

    var $valid_currencies = array(
            'CAD', //Canadian Dollar
            'AUD', //Australian Dollar
            'USD', //U.S. Dollar
            'EUR', //Euro
            'GBP', //British Pound
            'NZD', //New Zealand Dollar
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
    var $force_ssl = true;

    //has recurring
    var $recurring = true;

    //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
    var $ipn_url;

    //whether if this is the only enabled gateway it can skip the payment_form step
    var $skip_form = false;

    var $set_ipn = false;

    private $step_names;

    //credit card vars
    var $API_Username, $API_Password, $SandboxFlag, $API_Endpoint, $API_recurring, $version, $currencyCode, $locale;

    /****** Below are the public methods you may overwrite via a plugin ******/

    /**
    * Runs when your class is instantiated.
    */
    function __construct() {
        global $wpc_payments_core;

        $this->step_names = $wpc_payments_core->step_names;

        $wpc_gateways = WPC()->get_settings( 'gateways' );

        if ( !isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) || 1 != $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) {
            $this->recurring = false;
        }

        //set names here to be able to translate
        $this->admin_name = __('Authorize.net Checkout', WPC_CLIENT_TEXT_DOMAIN);
        $this->public_name = __('Authorize.net Checkout', WPC_CLIENT_TEXT_DOMAIN);

        if ( isset( $wpc_gateways[ $this->plugin_name ]['public_name'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['public_name'] ) ) {
            $this->public_name = $wpc_gateways[ $this->plugin_name ]['public_name'];
        }

        $this->method_img_url = WPC()->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = WPC()->plugin_url . 'images/cc-button.png';

        if ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ) {
            $this->method_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
        }

        $this->version = "63.0"; //api version

        //set credit card vars
        if ( isset( $wpc_gateways[ $this->plugin_name ] ) ) {

          $this->API_Username     = ( isset( $wpc_gateways[ $this->plugin_name ]['api_user'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['api_user'] ) : '';
          $this->API_Password     = ( isset( $wpc_gateways[ $this->plugin_name ]['api_key'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['api_key'] ) : '';
          $this->currencyCode     = ( isset( $wpc_gateways[ $this->plugin_name ]['currency'] ) ) ? $wpc_gateways[ $this->plugin_name ]['currency'] : '';
          $this->md5_hash         = ( isset( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) : '';
          $this->set_ipn          = ( isset( $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) &&  1 == $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) ? true : false;

          //set api urls
          if ( isset( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) )    {
            $this->API_Endpoint = esc_url_raw( $wpc_gateways[ $this->plugin_name ]['custom_api'] );
          } else if ( !isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) || $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox' )    {
            $this->API_Endpoint = "https://test.authorize.net/gateway/transact.dll";
            $this->API_recurring = "https://apitest.authorize.net/xml/v1/request.api";
          } else {
            $this->API_Endpoint = "https://secure.authorize.net/gateway/transact.dll";
            $this->API_recurring = "https://api.authorize.net/xml/v1/request.api";
          }
        }

        //check status of subscription for 'cancel'
        add_action( 'wpc_check_status_subscription_' . $this->plugin_name, array( &$this, 'wpc_check_status_subscription' ) );

        //cancel subscription after cancel in WP admin
        add_action( 'wpc_cancel_subscription_' . $this->plugin_name, array( &$this, 'wpc_cancel_subscription' ) );

    }


    public function wpc_check_status_subscription() {
        global $wpdb, $wpc_payments_core;

        $data = $wpdb->get_results( "SELECT `id`, `subscription_id` "
            . "FROM {$wpdb->prefix}wpc_client_payments "
            . "WHERE `payment_type` = 'recurring' "
            . "AND `order_status` = 'paid' "
            . "AND ( ISNULL(`subscription_status`) OR `subscription_status` = '' OR `subscription_status` = 'active' ) "
            . "AND `subscription_id` IS NOT NULL "
            . "AND `payment_method` = '" . $this->plugin_name . "' "
            . "GROUP BY `subscription_id`" , ARRAY_A );

        foreach ( $data as $payment ) {

            $subscription_id = $payment['subscription_id'];

            //build xml to post
            $content =
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                "<ARBGetSubscriptionStatusRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                    "<merchantAuthentication>".
                        "<name>" . $this->API_Username . "</name>".
                        "<transactionKey>" . $this->API_Password . "</transactionKey>".
                    "</merchantAuthentication>".
                    "<subscriptionId>" . $subscription_id . "</subscriptionId>" .
                "</ARBGetSubscriptionStatusRequest>";


            $args = array(
                'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
                'headers'       => array(
                    'Content-Type'      => 'text/xml',
                    'Content-Length'    => strlen($content),
                    'Connection'        => 'Connection',
                ),
                'body'          => $content,
                'sslverify'     => '',
                'timeout'   => 30,
            );

            //use built in WP http class to work with most server setups
            $response = wp_remote_post( $this->API_recurring, $args );

            if ( is_array( $response ) && isset( $response['body'] ) ) {
                $status = wpc_auth_substring_between($response['body'],'<Status>','</Status>');
            }
            if ( !empty( $status ) && 'canceled' == $status ) {
                $order_data = array();
                $order_data['transaction_type'] = 'subscription_canceled';
                $order_data['subscription_id'] = $subscription_id;
                $wpc_payments_core->order_update( $payment['id'], $order_data );
            }
        }
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
            . "AND `payment_method` = '" . $this->plugin_name . "' "
            . "GROUP BY `subscription_id`" , ARRAY_A );

        foreach ( $data as $plan ) {

            $subscription_id = $plan['subscription_id'];

            //build xml to post
            $content =
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                "<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                    "<merchantAuthentication>".
                        "<name>" . $this->API_Username . "</name>".
                        "<transactionKey>" . $this->API_Password . "</transactionKey>".
                    "</merchantAuthentication>".
                    "<subscriptionId>".
                        $subscription_id .
                    "</subscriptionId>" .
                "</ARBCancelSubscriptionRequest>";


            $args = array(
                'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
                'headers'       => array(
                    'Content-Type'      => 'text/xml',
                    'Content-Length'    => strlen($content),
                    'Connection'        => 'Connection',
                ),
                'body'          => $content,
                'sslverify'     => '',
                'timeout'   => 30,
            );

            //use built in WP http class to work with most server setups
            $response = wp_remote_post( $this->API_recurring, $args );

            $wpc_payments_core->update_payments_of_recurring( $subscription_id, $this->plugin_name, 'canceled' );
        }
    }


    function payment_process( &$order, $step = 3 ) {
        global $wpc_payments_core;
        $wpc_gateways = WPC()->get_settings( 'gateways' );
        require_once( 'authorize_net_cim/AuthorizeNetCIM.php' );
        $cim_request = new AuthorizeNetCIM( $this->API_Username, $this->API_Password, ( !isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) || $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox' ) );

        $content = '';

        /*our_hook_
            hook_name: wpc_client_gateway_before_step
            hook_title: do actions before start Step of payment gateways
            hook_description: Hook runs before start Step of payment gateways.
            hook_type: action
            hook_in: wp-client
            hook_location gateways files
            hook_param: string $gateway_name, string $step, array $order
            hook_since: 3.9.7.3
        */
        do_action( 'wpc_client_gateway_before_step', $this->plugin_name, $step, $order );

        if( 'recurring' == $order['payment_type'] ) {

            //recurring order
            switch( $step ) {
                case 3: {

                    //not confirmed IPN setting
                    if ( !$this->set_ipn ) {

                        //make link
                        $name = $this->step_names[2];
                        if ( WPC()->permalinks ) {
                            $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                        }

                        echo '<br /><br />';
                        echo __( 'Seems IPN settings is not confirmed. Please edit payment gateway settings.', WPC_CLIENT_TEXT_DOMAIN ) . ' ';
                        echo '<a href="' . $redirect . '">' . __( 'Return', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

                        break;
                    }

                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                    //make link
                    $name = $this->step_names[4];
                    if ( WPC()->permalinks ) {
                        $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                    } else {
                        $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                    }


                    $content = '';

                    if( !empty( $_GET['message'] ) ) {
                        $content .= '<div class="wpc_notice wpc_error" style="width: 100%;">' . $_GET['message'] . '</div>';
                    }

                    $content .= '<span class="wpc_notice wpc_info" style="float:left;display:block;margin:0 0 20px 0;">' . __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'] . '</span>';

                    //empty API
                    if ( empty( $this->API_Username ) || empty( $this->API_Password ) ) {
                        //make link
                        $name = $this->step_names[2];
                        if ( WPC()->permalinks ) {
                            $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                        }

                        echo '<br /><br />';
                        echo __( 'API are empty.', WPC_CLIENT_TEXT_DOMAIN ) . ' ';
                        echo '<a href="' . $redirect . '">' . __( 'Return', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

                        break;
                    }


                    add_action( 'wpc_payment_getaway_before_field_authorize', array( &$this, 'payment_getaway_before_field' ) );

                    $fields = array();

                    $fields[] = array(
                        'type'      => 'card_number',
                        'id'        => 'card_num',
                        'name'      => 'card_num',
                        'classes'   => array(
                            'credit_card_number',
                            'input_field',
                            'noautocomplete',
                            'new_card_fields'
                        ),
                        'required'  => true,
                        'validation'=> array(
                            'required' => __('Credit Card Number is required', WPC_CLIENT_TEXT_DOMAIN)
                        ),
                        'label'     => __( 'Credit Card Number', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '22'
                        )
                    );

                    $fields[] = array(
                        'type'      => 'exp_date',
                        'm_id'      => 'exp_month',
                        'm_name'    => 'exp_month',
                        'y_id'      => 'exp_year',
                        'y_name'    => 'exp_year',
                        'm_classes'   => array(
                            'new_card_fields'
                        ),
                        'y_classes'   => array(
                            'new_card_fields'
                        ),
                        'required'  => true,
                        'validation'=> array(
                            'required' => __('Expiration Date is required', WPC_CLIENT_TEXT_DOMAIN)
                        ),
                        'label'     => __( 'Expiration Date', WPC_CLIENT_TEXT_DOMAIN ),
                    );

                    $fields[] = array(
                        'type'      => 'text',
                        'id'        => 'card_code',
                        'name'      => 'card_code',
                        'classes'   => array(
                            'input_field',
                            'noautocomplete',
                            'new_card_fields'
                        ),
                        'required'  => true,
                        'validation'=> array(
                            'required' => __('CVV is required', WPC_CLIENT_TEXT_DOMAIN)
                        ),
                        'label'     => __( 'CVV', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '4',
                            'style'     => 'width:70px;'
                        )
                    );

                    $fields[] = array(
                        'type'      => 'text',
                        'id'        => 'wpc_cart__fname',
                        'name'      => 'fname',
                        'classes'   => array(
                            'input_field',
                            'noautocomplete',
                            'credit_fname'
                        ),
                        'required'  => true,
                        'validation'=> array(
                            'required' => __('First Name is required', WPC_CLIENT_TEXT_DOMAIN)
                        ),
                        'label'     => __( 'First Name', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '50'
                        )
                    );

                    $fields[] = array(
                        'type'      => 'text',
                        'id'        => 'wpc_cart__lname',
                        'name'      => 'lname',
                        'classes'   => array(
                            'input_field',
                            'noautocomplete',
                            'credit_lname'
                        ),
                        'required'  => true,
                        'validation'=> array(
                            'required' => __('Last Name is required', WPC_CLIENT_TEXT_DOMAIN)
                        ),
                        'label'     => __( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '50'
                        )
                    );

                    if ( !empty( $wpc_gateways[ $this->plugin_name ]['avs'] ) ) {
                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__address',
                            'name'      => 'address',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_address'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                'required' => __('Billing Address is required', WPC_CLIENT_TEXT_DOMAIN)
                            ),
                            'label'     => __( 'Billing Address', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '60'
                            )
                        );

                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__city',
                            'name'      => 'city',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_city'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                'required' => __('City is required', WPC_CLIENT_TEXT_DOMAIN)
                            ),
                            'label'     => __( 'City', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '40'
                            )
                        );

                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__state',
                            'name'      => 'state',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_state'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                'required' => __('State is required', WPC_CLIENT_TEXT_DOMAIN)
                            ),
                            'label'     => __( 'State', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '40'
                            )
                        );

                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__zip',
                            'name'      => 'zip',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_zip'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                'required' => __('ZIP Code is required', WPC_CLIENT_TEXT_DOMAIN)
                            ),
                            'label'     => __( 'ZIP Code', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '20'
                            )
                        );

                    }


                    /*our_hook_
                        hook_name: wpc_client_payment_form_fields
                        hook_title: Payment Form Fields (for some payment gateways)
                        hook_description: Hook runs before render form fields.
                        hook_type: filter
                        hook_in: wp-client
                        hook_location payment gateways classes
                        hook_param: array $fields, string $gateway_name
                        hook_since: 4.5.0
                    */
                    $fields = apply_filters( 'wpc_client_payment_form_fields', $fields, $this->plugin_name );

                    $args = array(
                        'action' => $redirect,
                        'submit_id' => 'wpc_payment_confirm',
                        'fields' => $fields
                    );

                    $content .= $wpc_payments_core->build_checkout_form( 'authorize', $args );
                    break;
                }

                case 4: {

                    $wpc_gateways = WPC()->get_settings( 'gateways' );
                    $data       = json_decode( $order['data'], true );

                    $delim_data = ( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) && 'yes' == $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? true : false;
                    $price_of_access = isset( $data['a1'] ) ? $data['a1'] : $data['a3'];

                    //check card before subscribe
                    $first_payment = new WPC_Gateway_Worker_AuthorizeNet_AIM($this->API_Endpoint,
                        $delim_data,
                        $wpc_gateways[ $this->plugin_name ]['delim_char'],
                        $wpc_gateways[ $this->plugin_name ]['encap_char'],
                        $this->API_Username,
                        $this->API_Password,
                        ( $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox')
                    );

                    $first_payment->setParameter('x_invoice_num', $order['order_id']);
                    $first_payment->setParameter('x_first_name', $_POST['fname']);
                    $first_payment->setParameter('x_last_name', $_POST['lname']);
                    if ( !empty( $wpc_gateways[ $this->plugin_name ]['avs'] ) ) {
                        $first_payment->setParameter('x_address', $_POST['address']);
                        $first_payment->setParameter('x_city', $_POST['city']);
                        $first_payment->setParameter('x_state', $_POST['state']);
                        $first_payment->setParameter('x_zip', $_POST['zip']);
                    }
                    $first_payment->setParameter('x_card_num', $_POST['card_num']);
                    $first_payment->setParameter('x_card_code', $_POST['card_code']);
                    $first_payment->setParameter('x_exp_date', $_POST['exp_month'] . '/' . $_POST['exp_year']);

                    //if first payment is 0
                    if ( 0 >= $price_of_access ) {
                        $first_payment->setTransactionType("AUTH_ONLY");
                        $first_payment->setParameter("x_amount", 1);
                    } else {
                        $first_payment->setParameter("x_amount", $price_of_access);
                        $first_payment->setParameter("x_currency_code", $order['currency']);

                        // Order Info
                        $first_payment->setParameter("x_description", urlencode( substr( __( 'First Payment for subscription.', WPC_CLIENT_TEXT_DOMAIN ), 0, 31 ) ) );
                        $first_payment->setParameter("x_test_request", false);
                        $first_payment->setParameter("x_duplicate_window", 30);

                        // E-mail
                        $first_payment->setParameter("x_header_email_receipt", $wpc_gateways[ $this->plugin_name ]['header_email_receipt']);
                        $first_payment->setParameter("x_footer_email_receipt", $wpc_gateways[ $this->plugin_name ]['footer_email_receipt']);
                        $first_payment->setParameter( "x_email_customer", strtoupper( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) );

                        $first_payment->setParameter("x_customer_ip", $_SERVER['REMOTE_ADDR']);

                    }
                    $first_payment->process();

                    if ( $first_payment->isApproved() ) {

                        //void first transaction
//                        $transaction_id = $first_payment->getTransactionID();

                        $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                        if ( 'week' == $data['t3'] ) {
                            $data['t3'] = 'day' ;
                            $data['p3'] = 7 * $data['p3'];
                        }

                        $start_date = date( 'Y-m-d', strtotime( "+" . $data['p3'] . $data['t3'] ) );

                        $totalOccurrences = ( !isset( $data['c'] ) || '' == $data['c'] ) ? '9999' : $data['c'] ;

                        //build xml to post
                        $content =
                            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                            "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                                "<merchantAuthentication>".
                                    "<name>" . $this->API_Username . "</name>".
                                    "<transactionKey>" . $this->API_Password . "</transactionKey>".
                                "</merchantAuthentication>".
                                "<subscription>".
                                    "<name>" . substr( $item_name, 0, 49 ) . "</name>".
                                    "<paymentSchedule>".
                                        "<interval>".
                                            "<length>". $data['p3'] ."</length>".
                                            "<unit>". $data['t3'] . 's' ."</unit>".
                                        "</interval>".
                                        "<startDate>" . $start_date . "</startDate>".
                                        "<totalOccurrences>". $totalOccurrences . "</totalOccurrences>".
                                    "</paymentSchedule>".
                                    "<amount>". $data['a3'] ."</amount>".
                                    "<payment>" .
                                        "<creditCard>" .
                                            "<cardNumber>" . $_POST['card_num'] . "</cardNumber>" .
                                            "<expirationDate>" . $_POST['exp_month'] . '/' . $_POST['exp_year'] . "</expirationDate>" .
                                            "<cardCode>" . $_POST['card_code'] . "</cardCode>" .
                                        "</creditCard>" .
                                    "</payment>" .
                                    "<order>" .
                                        "<invoiceNumber>" . $order['order_id'] .  "</invoiceNumber>" .
                                        "<description>" . substr( $item_name, 0, 254 ) .  "</description>" .
                                    "</order>" .
                                    "<billTo>" .
                                     "<firstName>" . $_POST['fname'] . "</firstName>" .
                                     "<lastName>" . $_POST['lname'] . "</lastName>";
                                if ( !empty( $wpc_gateways[ $this->plugin_name ]['avs'] ) ) {
                                    $content .=
                                        "<address>" . $_POST['address'] . "</address>" .
                                        "<city>" . $_POST['city'] . "</city>" .
                                        "<state>" . $_POST['state'] . "</state>" .
                                        "<zip>" . $_POST['zip'] . "</zip>";
                                }
                               $content .=
                                    "</billTo>" .
                                "</subscription>" .
                            "</ARBCreateSubscriptionRequest>";


                        $args = array(
                            'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
                            'headers'       => array(
                                'Content-Type'      => 'text/xml',
                                'Content-Length'    => strlen($content),
                                'Connection'        => 'Connection',
                            ),
                            'body'          => $content,
                            'sslverify'     => '',
                            'timeout'   => 30,
                        );

                        //use built in WP http class to work with most server setups
                        $response = wp_remote_post( $this->API_recurring, $args );

                        if ( is_array( $response ) && isset( $response['body'] ) ) {

                            $result = array();
                            $result['refId'] = wpc_auth_substring_between($response['body'],'<refId>','</refId>');
                            $result['resultCode'] = wpc_auth_substring_between($response['body'],'<resultCode>','</resultCode>');
                            $result['code'] = wpc_auth_substring_between($response['body'],'<code>','</code>');
                            $result['text'] = wpc_auth_substring_between($response['body'],'<text>','</text>');
                            $result['subscriptionId'] = wpc_auth_substring_between($response['body'],'<subscriptionId>','</subscriptionId>');

                            if ( false === $result['subscriptionId'] ) {
                                //make link
                                $name = $this->step_names[3];
                                if ( WPC()->permalinks ) {
                                    $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1&message=" . urlencode( $result['text'] );
                                } else {
                                    $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1, 'message' => urlencode( $result['text'] ) ), get_home_url()  );
                                }

                                //echo (sprintf(__('There was a problem finalizing your purchase: "%s" ', WPC_CLIENT_TEXT_DOMAIN), $result['text'] ) );
                                //echo '<br>';
                                //echo (sprintf(__('<a href="%s">Please try again</a>', WPC_CLIENT_TEXT_DOMAIN), $redirect ) );
                                //return false;
                            } else {

                                $payment_data = array();
                                $payment_data['transaction_status'] = "paid";
                                $payment_data['subscription_id'] = $result['subscriptionId'];
                                $payment_data['subscription_status'] = '';
                                $payment_data['parent_txn_id'] = null;
                                $payment_data['transaction_type'] = 'subscription_payment';
                                $payment_data['transaction_id'] = null;


                                $wpc_payments_core->order_update( $order['id'], $payment_data );

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
                            }

                        } else {
                            //make link
                            $name = $this->step_names[3];
                            if ( WPC()->permalinks ) {
                                $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1&message=" . urlencode( 'Transaction Failed' );
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1, 'message' => urlencode( 'Transaction Failed' ) ), get_home_url()  );
                            }

                            //_e('There was a problem finalizing your purchase: Transaction Failed ', WPC_CLIENT_TEXT_DOMAIN ) ;
                            //echo '<br>';
                            //echo (sprintf(__('<a href="%s">Please try again</a>', WPC_CLIENT_TEXT_DOMAIN), $redirect ) );
                            //return false;

                        }
                    } else {

                        //make link
                        $name = $this->step_names[3];
                        if ( WPC()->permalinks ) {
                            $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1&message=" . urlencode( $first_payment->getResponseText() );
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1, 'message' => urlencode( $first_payment->getResponseText() ) ), get_home_url()  );
                        }

                        //echo (sprintf(__('There was a problem finalizing your purchase: "%s" ', WPC_CLIENT_TEXT_DOMAIN), $auth_only->getResponseText() ) );
                        //echo '<br>';
                        //echo (sprintf(__('<a href="%s">Please try again</a>', WPC_CLIENT_TEXT_DOMAIN), $redirect ) );
                        //return false;
                    }
                    WPC()->redirect( $redirect );
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

        } else {

            //one time order
            switch( $step ) {
                case 3: {
                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                    //make link
                    $name = $this->step_names[4];
                    if ( WPC()->permalinks ) {
                        $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                    } else {
                        $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                    }

                    if( !empty( $_GET['message'] ) ) {
                        $content .= '<div class="wpc_error wpc_notice" style="width: 100%;">' . $_GET['message'] . '</div>';
                    }

                    $content .= '<span class="wpc_notice wpc_info" style="float:left;display:block;margin:0 0 20px 0;">' . __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'] . '</span>';

                    //empty API
                    if ( empty( $this->API_Username ) || empty( $this->API_Password ) ) {
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

                    add_action( 'wpc_payment_getaway_before_field_authorize', array( &$this, 'payment_getaway_before_field' ) );

                    $fields = array();
                    $payment_data = get_user_meta( $order['client_id'], 'wpc_payment_profile_data', true );

                    if( is_array( $payment_data ) && count( $payment_data ) ) {
                        $options = array(
                            ''  => __( 'Use new card', WPC_CLIENT_TEXT_DOMAIN )
                        );

                        foreach( $payment_data as $key => $val ) {
                            $options[$key] = 'XXXX ' . $val[0] . ' (' . $val[1] . ')';
                        }

                        $fields[] = array(
                            'type'      => 'select',
                            'id'        => 'wpc_payment_profile',
                            'name'      => 'payment_profile',
                            'label'     => __( 'Payment Profile', WPC_CLIENT_TEXT_DOMAIN ),
                            'options'   => $options
                        );
                    }

                    $fields[] = array(
                        'type'      => 'card_number',
                        'id'        => 'card_num',
                        'name'      => 'card_num',
                        'classes'   => array(
                                        'credit_card_number',
                                        'input_field',
                                        'noautocomplete',
                                        'new_card_fields'
                                       ),
                        'required'  => true,
                        'validation'=> array(
                                        'required' => __('Credit Card Number is required', WPC_CLIENT_TEXT_DOMAIN)
                                       ),
                        'label'     => __( 'Credit Card Number', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '22'
                        )
                    );

                    $fields[] = array(
                        'type'      => 'exp_date',
                        'm_id'      => 'exp_month',
                        'm_name'    => 'exp_month',
                        'y_id'      => 'exp_year',
                        'y_name'    => 'exp_year',
                        'classes'   => array(
                                        'new_card_fields'
                                       ),
                        'required'  => true,
                        'validation'=> array(
                                        'required' => __('Expiration Date is required', WPC_CLIENT_TEXT_DOMAIN)
                                       ),
                        'label'     => __( 'Expiration Date', WPC_CLIENT_TEXT_DOMAIN ),
                    );

                    $fields[] = array(
                        'type'      => 'text',
                        'id'        => 'card_code',
                        'name'      => 'card_code',
                        'classes'   => array(
                                        'input_field',
                                        'noautocomplete',
                                        'new_card_fields'
                                       ),
                        'required'  => true,
                        'validation'=> array(
                                        'required' => __('CVV is required', WPC_CLIENT_TEXT_DOMAIN)
                                       ),
                        'label'     => __( 'CVV', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '4',
                            'style'     => 'width:70px;'
                        )
                    );

                    $fields[] = array(
                        'type'      => 'text',
                        'id'        => 'wpc_cart__fname',
                        'name'      => 'fname',
                        'classes'   => array(
                                        'input_field',
                                        'noautocomplete',
                                        'credit_fname'
                                       ),
                        'label'     => __( 'First Name', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '50'
                        )
                    );

                    $fields[] = array(
                        'type'      => 'text',
                        'id'        => 'wpc_cart__lname',
                        'name'      => 'lname',
                        'classes'   => array(
                                        'input_field',
                                        'noautocomplete',
                                        'credit_lname'
                                       ),
                        'label'     => __( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ),
                        'attributes'=> array(
                            'maxlength' => '50'
                        )
                    );

                    if ( !empty( $wpc_gateways[ $this->plugin_name ]['avs'] ) ) {
                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__address',
                            'name'      => 'address',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_address'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                            'required' => __('Billing Address is required', WPC_CLIENT_TEXT_DOMAIN)
                                           ),
                            'label'     => __( 'Billing Address', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '60'
                            )
                        );

                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__city',
                            'name'      => 'city',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_city'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                            'required' => __('City is required', WPC_CLIENT_TEXT_DOMAIN)
                                           ),
                            'label'     => __( 'City', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '40'
                            )
                        );

                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__state',
                            'name'      => 'state',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_state'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                            'required' => __('State is required', WPC_CLIENT_TEXT_DOMAIN)
                                           ),
                            'label'     => __( 'State', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '40'
                            )
                        );

                        $fields[] = array(
                            'type'      => 'text',
                            'id'        => 'wpc_cart__zip',
                            'name'      => 'zip',
                            'classes'   => array(
                                'input_field',
                                'noautocomplete',
                                'credit_zip'
                            ),
                            'required'  => true,
                            'validation'=> array(
                                            'required' => __('ZIP Code is required', WPC_CLIENT_TEXT_DOMAIN)
                                           ),
                            'label'     => __( 'ZIP Code', WPC_CLIENT_TEXT_DOMAIN ),
                            'attributes'=> array(
                                'maxlength' => '20'
                            )
                        );

                    }

                    $fields[] = array(
                        'type'      => 'checkbox',
                        'id'        => 'save_card_data',
                        'name'      => 'save_card_data',
                        'classes'   => array(
                            'new_card_fields',
                        ),
                        'value'     => '1',
                        'label'     => __( 'Save card information to Authorize.Net server for future use', WPC_CLIENT_TEXT_DOMAIN ),
                        'position'  => 'right'
                    );

                    /*our_hook_
                       hook_name: wpc_client_payment_form_fields
                       hook_title: Payment Form Fields (for some payment gateways)
                       hook_description: Hook runs before render form fields.
                       hook_type: filter
                       hook_in: wp-client
                       hook_location payment gateways classes
                       hook_param: array $fields, string $gateway_name
                       hook_since: 4.5.0
                   */
                    $fields = apply_filters( 'wpc_client_payment_form_fields', $fields, $this->plugin_name );

                    $args = array(
                        'action'    => $redirect,
                        'submit_id' => 'wpc_payment_confirm',
                        'fields'    => $fields
                    );

                    $content .= $wpc_payments_core->build_checkout_form( 'authorize', $args );
                    break;
                }

                case 4: {
                    $wpc_gateways = WPC()->get_settings( 'gateways' );

                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                    if( empty( $_POST['payment_profile'] ) ) {
                        $delim_data = ( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) && 'yes' == $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? true : false;

                        $payment = new WPC_Gateway_Worker_AuthorizeNet_AIM($this->API_Endpoint,
                          $delim_data,
                          $wpc_gateways[ $this->plugin_name ]['delim_char'],
                          $wpc_gateways[ $this->plugin_name ]['encap_char'],
                          $this->API_Username,
                          $this->API_Password,
                          ( $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox') );

                        $payment->transaction($_POST['card_num']);

                        $payment->addLineItem( $order['client_id'], urlencode( substr( $item_name, 0, 31 ) ), '', 1, $order['amount'] );
                        // Billing Info

                        $payment->setParameter('x_first_name', $_POST['fname']);
                        $payment->setParameter('x_last_name', $_POST['lname']);
                        if ( !empty( $wpc_gateways[ $this->plugin_name ]['avs'] ) ) {
                            $payment->setParameter('x_address', $_POST['address']);
                            $payment->setParameter('x_city', $_POST['city']);
                            $payment->setParameter('x_state', $_POST['state']);
                            $payment->setParameter('x_zip', $_POST['zip']);
                        }
                        $payment->setParameter("x_card_code", $_POST['card_code']);
                        $payment->setParameter("x_exp_date ", $_POST['exp_month'] . '/' . $_POST['exp_year']);
                        $payment->setParameter("x_amount", $order['amount']);
                        $payment->setParameter("x_currency_code", $order['currency']);

                        // Order Info
                        $payment->setParameter("x_description", urlencode( substr( $item_name, 0, 31 ) ) );
                        $payment->setParameter("x_invoice_num",  $order['order_id'] );
                        $payment->setParameter("x_test_request", false);
                        $payment->setParameter("x_duplicate_window", 30);

                        // E-mail
                        $payment->setParameter("x_header_email_receipt", $wpc_gateways[ $this->plugin_name ]['header_email_receipt']);
                        $payment->setParameter("x_footer_email_receipt", $wpc_gateways[ $this->plugin_name ]['footer_email_receipt']);
                        $payment->setParameter( "x_email_customer", strtoupper( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) );

                        $payment->setParameter("x_customer_ip", $_SERVER['REMOTE_ADDR']);

                        $payment->process();

                        if ( $payment->isApproved() ) {

                            $payment_data = array();
                            $payment_data['transaction_status'] = "Completed";
                            $payment_data['subscription_id'] = null;
                            $payment_data['subscription_status'] = null;
                            $payment_data['parent_txn_id'] = null;
                            $payment_data['transaction_type'] = 'paid';
                            $payment_data['transaction_id'] = $payment->getTransactionID();


                            $wpc_payments_core->order_update( $order['id'], $payment_data );
                            if( isset( $_POST['save_card_data'] ) && '1' == $_POST['save_card_data'] ) {
                                $customerProfileId = get_user_meta( $order['client_id'], 'authorize_net_customerProfileId', true );
                                if( $customerProfileId ) {
                                    $merchant_id = $cim_request->getCustomerProfile( $customerProfileId );
                                    if( $merchant_id != $order['client_id'] ) {
                                        $user_data = get_userdata( $order['client_id'] );
                                        if( !empty( $user_data->user_email ) ) {
                                            $customerProfileId = $cim_request->createCustomerProfile( $user_data->user_email );
                                            update_user_meta( $order['client_id'], 'authorize_net_customerProfileId', $customerProfileId );
                                        } else {
                                            die( __( 'Client does not exists', WPC_CLIENT_TEXT_DOMAIN ) );
                                        }
                                    }
                                } else {
                                    $user_data = get_userdata( $order['client_id'] );
                                    if( !empty( $user_data->user_email ) ) {
                                        $customerProfileId = $cim_request->createCustomerProfile( $user_data-> user_email );
                                        update_user_meta( $order['client_id'], 'authorize_net_customerProfileId', $customerProfileId );
                                    } else {
                                        die( __( 'Client does not exists', WPC_CLIENT_TEXT_DOMAIN ) );
                                    }
                                }

                                $paymentProfile = array(
                                    'first_name' => get_user_meta( $order['client_id'], 'first_name', true ) ? get_user_meta( $order['client_id'], 'first_name', true ) : get_user_meta( $order['client_id'], 'wpc_cl_business_name', true ),
                                    'last_name' => get_user_meta( $order['client_id'], 'last_name', true ),
                                    'card' => $_POST['card_num'],
                                    'code' => $_POST['card_code'],
                                    'exp' => $_POST['exp_year'] . '-' . $_POST['exp_month']
                                );
                                $customerPaymentProfileId = $cim_request->createCustomerPaymentProfile( $customerProfileId, $paymentProfile );
                                if( $customerPaymentProfileId > 0 ) {
                                    $payment_data = get_user_meta( $order['client_id'], 'wpc_payment_profile_data', true );
                                    if( !( is_array( $payment_data ) && count( $payment_data ) ) ) {
                                        $payment_data = array();
                                    }
                                    $payment_data[ $customerPaymentProfileId ] = array(
                                        substr( $_POST['card_num'], -4 ),
                                        $payment->getMethod()
                                    );

                                    update_user_meta( $order['client_id'], 'wpc_payment_profile_data', $payment_data );
                                }
                            }

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
                                $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1&message=" . urlencode( $payment->getResponseText() );
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1, 'message' => urlencode( $payment->getResponseText() ) ), get_home_url()  );
                            }

                            //$content .= sprintf(__('There was a problem finalizing your purchase: "%s" ', WPC_CLIENT_TEXT_DOMAIN), $payment->getResponseText() );
                            //$content .= '<br>';
                            //$content .= sprintf(__('<a href="%s">Please try again</a>', WPC_CLIENT_TEXT_DOMAIN), $redirect );
                        }
                        WPC()->redirect( $redirect );
                    } else {
                        $customerProfileId = get_user_meta( $order['client_id'], 'authorize_net_customerProfileId', true );
                        if( $customerProfileId ) {
                            $merchant_id = $cim_request->getCustomerProfile( $customerProfileId );
                            if( $merchant_id != $order['client_id'] ) {
                                die( __( 'Wrong client profile.', WPC_CLIENT_TEXT_DOMAIN ) );
                            }
                        } else {
                            die( __( 'Client profile does not exists.', WPC_CLIENT_TEXT_DOMAIN ) );
                        }

                        $customerPaymentProfileId = $_POST['payment_profile'];

                        $transaction_data = array(
                            'amount' => $order['amount'],
                            'invoice_num' => $order['order_id']
                        );
                        $result = $cim_request->createCustomerProfileTransaction( $customerProfileId, $customerPaymentProfileId, $transaction_data );
                        if( $result ) {
                            $payment_data = array();
                            $payment_data['transaction_status'] = "Completed";
                            $payment_data['subscription_id'] = null;
                            $payment_data['subscription_status'] = null;
                            $payment_data['parent_txn_id'] = null;
                            $payment_data['transaction_type'] = 'paid';
                            $payment_data['transaction_id'] = $cim_request->getTransactionID();


                            $wpc_payments_core->order_update( $order['id'], $payment_data );

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

                            WPC()->redirect( $redirect );

                        } else {
                            //make link
                            $name = $this->step_names[3];
                            if ( WPC()->permalinks ) {
                                $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1&message=" . urlencode( $cim_request->getResponseText() );
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1, 'message' => urlencode( $cim_request->getResponseText() ) ), get_home_url()  );
                            }
                        }

                        $content .= sprintf(__( 'There was a problem finalizing your purchase: "%s" ', WPC_CLIENT_TEXT_DOMAIN ), $cim_request->getResponseText() );
                        $content .= '<br>';
                        $content .= sprintf(__( '<a href="%s">Please try again</a>', WPC_CLIENT_TEXT_DOMAIN ), $redirect );
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


    /**
     * @param $field
     */
    function payment_getaway_before_field( $field ) {
        if ( isset( $field['id'] ) && $field['id'] == 'card_num' ) {
            apply_filters('wpc_checkout_error_card_num', '');
        } elseif ( isset( $field['m_id'] ) && $field['m_id'] == 'exp_month' ) {
            apply_filters( 'wpc_checkout_error_exp', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'card_code' ) {
            apply_filters( 'wpc_checkout_error_card_code', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'wpc_cart__fname' ) {
            apply_filters( 'wpc_checkout_error_fname', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'wpc_cart__lname' ) {
            apply_filters( 'wpc_checkout_error_lname', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'wpc_cart__address' ) {
            apply_filters( 'wpc_checkout_error_address', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'wpc_cart__city' ) {
            apply_filters( 'wpc_checkout_error_city', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'wpc_cart__state' ) {
            apply_filters( 'wpc_checkout_error_state', '' );
        } elseif ( isset( $field['id'] ) && $field['id'] == 'wpc_cart__zip' ) {
            apply_filters( 'wpc_checkout_error_zip', '' );
        }
    }


    /*
    *
    */
    function _ipn( $order ) {

        if ( !empty( $_POST['x_subscription_id'] ) ) {

            global $wpc_payments_core;

            $order = $wpc_payments_core->get_order_by( $_POST['x_invoice_num'], 'order_id' );
            if ( !$order ) {
                die( 'Order Incorrect' );
            }

            if ( 1 == $_POST['x_response_code'] ) {
                $subscription_id = isset( $_POST['x_subscription_id'] ) ? $_POST['x_subscription_id'] : '';

                $payment_data = array();
                $payment_data['transaction_status'] = 'Completed';
                $payment_data['transaction_id'] = $_POST['x_trans_id'];
                $payment_data['subscription_id'] = $subscription_id;
                $payment_data['subscription_status'] = 'active';
                $payment_data['parent_txn_id'] = null;
                $payment_data['transaction_type'] = 'subscription_payment';
                $payment_data['amount'] = !empty( $_POST['x_amount'] ) ? $_POST['x_amount'] : '';

                $wpc_payments_core->order_update( $order['id'], $payment_data );

                $function = ( !empty( $order['function'] ) ) ? $order['function'] : '';
                $expire_subscription = apply_filters( 'wpc_client_check_expire_subscription_'. $function
                        , '', $order );

                if ( $expire_subscription ) {
                    $wpc_payments_core->update_payments_of_recurring( $subscription_id, $this->plugin_name, 'expired' );
                }

            }
        }
    }






    function _print_year_dropdown($sel='', $pfp = false) {
        $localDate=getdate();
        $minYear = $localDate["year"];
        $maxYear = $minYear + 15;

        $output = "";
        for($i=$minYear; $i<$maxYear; $i++) {
            if ($pfp) {
                    $output .= "<option value='". substr($i, 0, 4) ."'".($sel==(substr($i, 0, 4))?' selected':'').
                    ">". $i ."</option>";
            } else {
                    $output .= "<option value='". substr($i, 2, 2) ."'".($sel==(substr($i, 2, 2))?' selected':'').
            ">". $i ."</option>";
            }
        }
        return($output);
    }

    function _print_month_dropdown($sel='') {
        $output =  "<option " . ($sel==1?' selected':'') . " value='01'>01 - Jan</option>";
        $output .=  "<option " . ($sel==2?' selected':'') . "  value='02'>02 - Feb</option>";
        $output .=  "<option " . ($sel==3?' selected':'') . "  value='03'>03 - Mar</option>";
        $output .=  "<option " . ($sel==4?' selected':'') . "  value='04'>04 - Apr</option>";
        $output .=  "<option " . ($sel==5?' selected':'') . "  value='05'>05 - May</option>";
        $output .=  "<option " . ($sel==6?' selected':'') . "  value='06'>06 - Jun</option>";
        $output .=  "<option " . ($sel==7?' selected':'') . "  value='07'>07 - Jul</option>";
        $output .=  "<option " . ($sel==8?' selected':'') . "  value='08'>08 - Aug</option>";
        $output .=  "<option " . ($sel==9?' selected':'') . "  value='09'>09 - Sep</option>";
        $output .=  "<option " . ($sel==10?' selected':'') . "  value='10'>10 - Oct</option>";
        $output .=  "<option " . ($sel==11?' selected':'') . "  value='11'>11 - Nov</option>";
        $output .=  "<option " . ($sel==12?' selected':'') . "  value='12'>12 - Dec</option>";

        return($output);
    }


      /**
       * Echo a settings meta box with whatever settings you need for you gateway.
       *  Form field names should be prefixed with wpc_gateway[plugin_name], like "wpc_gateway[plugin_name][mysetting]".
       *  You can access saved settings via $wpc_gateways array.
       */
      function create_settings_form( $wpc_gateways ) {
        //make link
        if ( WPC()->permalinks ) {
            $ipn_url = WPC()->make_url( '/wpc-ipn-handler-url/' . $this->plugin_name . '/', get_home_url() );
        } else {
            $ipn_url = add_query_arg( array( 'wpc_page' => 'payment_ipn', 'wpc_page_value' => $this->plugin_name ), get_home_url()  );
        }

        ?>
        <div id="wpc_<?php echo $this->plugin_name ?>" class="postbox">
          <h3 class='hndle'><span><?php _e('Authorize.net AIM Settings', WPC_CLIENT_TEXT_DOMAIN); ?></span></h3>
          <div class="inside">
            <span class="description"><?php _e('Authorize.net AIM is a customizable payment processing solution that gives the merchant control over all the steps in processing a transaction. An SSL certificate is required to use this gateway. USD is the only currency supported by this gateway.', WPC_CLIENT_TEXT_DOMAIN) ?></span>
            <span class="description"><?php _e('Interval Length for recurring billing must be a value from 7 through 365 for day based subscriptions.', WPC_CLIENT_TEXT_DOMAIN) ?></span>
            <table class="form-table">
                      <tr>
                        <th scope="row"><?php _e('Mode', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                        <p>
                          <select name="wpc_gateway[<?php echo $this->plugin_name ?>][mode]">
                            <option value="sandbox" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) ? $wpc_gateways[ $this->plugin_name ]['mode'] : '', 'sandbox' ) ?>><?php _e('Sandbox', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                            <option value="live" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) ? $wpc_gateways[ $this->plugin_name ]['mode'] : '', 'live' ) ?>><?php _e('Live', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                          </select>
                        </p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row"><?php _e( 'Allow Recurring', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                        <td>
                            <p>
                                <label>
                                    <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][allow_recurring]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) && $wpc_gateways[ $this->plugin_name ]['allow_recurring'], '1' ) ?> value="1" />
                                    <?php _e( 'Allow to use this gateway for recurring payments', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row"><?php _e('Gateway Credentials', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                              <span class="description"><?php printf(__('You must login to Authorize.net merchant dashboard to obtain the API login ID and API transaction key. <a target="_blank" href="%s">Instructions &raquo;</a>', WPC_CLIENT_TEXT_DOMAIN), "http://www.authorize.net/"); ?></span>
                              <p>
                                <label><?php _e('Login ID', WPC_CLIENT_TEXT_DOMAIN) ?><br />
                                  <input value="<?php echo esc_attr( isset( $wpc_gateways[ $this->plugin_name ]['api_user'] ) ? $wpc_gateways[ $this->plugin_name ]['api_user'] : '' ); ?>" size="30" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_user]" type="text" />
                                </label>
                              </p>
                              <p>
                                <label><?php _e('Transaction Key', WPC_CLIENT_TEXT_DOMAIN) ?><br />
                                  <input value="<?php echo esc_attr( isset( $wpc_gateways[ $this->plugin_name ]['api_key'] ) ? $wpc_gateways[ $this->plugin_name ]['api_key'] : '' ); ?>" size="30" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_key]" type="text" />
                                </label>
                              </p>
                                <p>
                                    <label><a title="<?php _e('The payment gateway generated MD5 hash value that can be used to authenticate the transaction response. You should set the same value like in your Authorize.net Settings', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Security: MD5 Hash', WPC_CLIENT_TEXT_DOMAIN); ?></a><br/>
                                      <input value="<?php echo isset( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) : ''; ?>" size="32" name="wpc_gateway[<?php echo $this->plugin_name ?>][md5_hash]" type="text" />
                                    </label>
                                </p>
                        </td>
                      </tr>

                      <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "IPN URL (Silent Post)", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <label>
                                    <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][set_ipn]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) && $wpc_gateways[ $this->plugin_name ]['set_ipn'], '1' ) ?> value="1" />
                                    <?php _e( 'I certify that I have properly set my IPN alert URL', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                                <br />
                                <br />
                                <b><?php echo $ipn_url ?></b>
                                <span style="float: left; font-size: 11px;" class="description"><?php _e( 'Use this URL in your Authorize.net "Silent Post URL" Settings.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
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
                                <?php _e( "AVS", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                <label>
                                    <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][avs]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['avs'] ) && $wpc_gateways[ $this->plugin_name ]['avs'], '1' ) ?> value="1" />
                                    <?php _e( 'Address Verification Service', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
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
                        <th scope="row"><?php _e('Advanced Settings', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                          <span class="description"><?php _e('Optional settings to control advanced options', WPC_CLIENT_TEXT_DOMAIN) ?></span>
                              <p>
                                <label><a title="<?php _e('Authorize.net default is \',\'. Otherwise, get this from your credit card processor. If the transactions are not going through, this character is most likely wrong.', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Delimiter Character', WPC_CLIENT_TEXT_DOMAIN); ?></a><br />
                                  <input value="<?php echo ( ( isset( $wpc_gateways[ $this->plugin_name ]['delim_char'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['delim_char'] ) ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['delim_char'] ) : "," ); ?>" size="2" name="wpc_gateway[<?php echo $this->plugin_name ?>][delim_char]" type="text" />
                                </label>
                              </p>

                              <p>
                                <label><a title="<?php _e('Authorize.net default is blank. Otherwise, get this from your credit card processor. If the transactions are going through, but getting strange responses, this character is most likely wrong.', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Encapsulation Character', WPC_CLIENT_TEXT_DOMAIN); ?></a><br />
                                  <input value="<?php echo isset( $wpc_gateways[ $this->plugin_name ]['encap_char'] ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['encap_char'] ) : ''; ?>" size="2" name="wpc_gateway[<?php echo $this->plugin_name ?>][encap_char]" type="text" />
                                </label>
                              </p>

                              <p>
                                <label><?php _e('Email Customer (on success):', WPC_CLIENT_TEXT_DOMAIN); ?><br />
                                  <select name="wpc_gateway[<?php echo $this->plugin_name ?>][email_customer]">
                                    <option value="yes" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) ? $wpc_gateways[ $this->plugin_name ]['email_customer'] : '', 'yes' ) ?>><?php _e('Yes', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                                    <option value="no" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) ? $wpc_gateways[ $this->plugin_name ]['email_customer'] : '', 'no' ) ?>><?php _e('No', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                                  </select>
                                </label>
                              </p>

                              <p>
                                <label><a title="<?php _e('This text will appear as the header of the email receipt sent to the customer.', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Customer Receipt Email Header', WPC_CLIENT_TEXT_DOMAIN); ?></a><br/>
                                  <input value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['header_email_receipt'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['header_email_receipt'] ) ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['header_email_receipt'] ) : __('Thanks for your payment!', WPC_CLIENT_TEXT_DOMAIN); ?>" size="40" name="wpc_gateway[<?php echo $this->plugin_name ?>][header_email_receipt]" type="text" />
                                </label>
                          </p>

                              <p>
                                <label><a title="<?php _e('This text will appear as the footer on the email receipt sent to the customer.', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Customer Receipt Email Footer', WPC_CLIENT_TEXT_DOMAIN); ?></a><br/>
                                  <input value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['footer_email_receipt'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['footer_email_receipt'] ) ) ?  esc_attr( $wpc_gateways[ $this->plugin_name ]['footer_email_receipt'] ) : ''; ?>" size="40" name="wpc_gateway[<?php echo $this->plugin_name ?>][footer_email_receipt]" type="text" />
                                </label>
                          </p>
                            <p>
                                <label><a title="<?php _e('Request a delimited response from the payment gateway.', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Delim Data:', WPC_CLIENT_TEXT_DOMAIN); ?></a><br/>
                                    <select name="wpc_gateway[<?php echo $this->plugin_name ?>][delim_data]">
                                        <option value="yes" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? $wpc_gateways[ $this->plugin_name ]['delim_data'] : '', 'yes' ) ?>><?php _e('Yes', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                                        <option value="no" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? $wpc_gateways[ $this->plugin_name ]['delim_data'] : '', 'no' ) ?>><?php _e('No', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                                    </select>
                                </label>
                            </p>
                            <p>
                                <label><a title="<?php _e('Many other gateways have Authorize.net API emulators. To use one of these gateways input their API post url here.', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Custom API URL', WPC_CLIENT_TEXT_DOMAIN) ?></a><br />
                                    <input value="<?php echo isset( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) : ''; ?>" size="50" name="wpc_gateway[<?php echo $this->plugin_name ?>][custom_api]" type="text" />
                                </label>
                            </p>

                        </td>
                      </tr>
                      <tr>
                        <th scope="row">

                        </th>
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
}




if(!class_exists('WPC_Gateway_Worker_AuthorizeNet_AIM')) {
  class WPC_Gateway_Worker_AuthorizeNet_AIM
  {
    var $login;
    var $transkey;
    var $params   = array();
    var $results  = array();
    var $line_items = array();

    var $approved = false;
    var $declined = false;
    var $error    = true;
    var $method   = "";

    var $fields;
    var $response;

    var $instances = 0;

    function __construct($url, $delim_data, $delim_char, $encap_char, $gw_username, $gw_tran_key, $gw_test_mode)
    {
      if ($this->instances == 0)
      {
    $this->url = $url;

    $this->params['x_delim_data']     = $delim_data;
    $this->params['x_delim_char']     = $delim_char;
    $this->params['x_encap_char']     = $encap_char;
    $this->params['x_relay_response'] = "FALSE";
    $this->params['x_url']            = "FALSE";
    $this->params['x_version']        = "3.1";
    $this->params['x_method']         = "CC";
    $this->params['x_type']           = "AUTH_CAPTURE";
    $this->params['x_login']          = $gw_username;
    $this->params['x_tran_key']       = $gw_tran_key;
    $this->params['x_test_request']   = $gw_test_mode;

    $this->instances++;
      } else {
    return false;
      }
    }

    function transaction($cardnum)
    {
      $this->params['x_card_num']  = trim($cardnum);
    }

    function addLineItem($id, $name, $description, $quantity, $price, $taxable = 0)
    {
      $this->line_items[] = "{$id}<|>{$name}<|>{$description}<|>{$quantity}<|>{$price}<|>{$taxable}";
    }

    function process($retries = 1)
    {
      $this->_prepareParameters();
      $query_string = rtrim($this->fields, "&");

      $count = 0;
      while ($count < $retries)
      {
        //$args['user-agent'] = "WPC-Client/" . WPC_CLIENT_VER . ": http://wpclient.com | Authorize.net AIM Plugin/" . WPC_CLIENT_VER;
        $args['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
        $args['body'] = $query_string;
        $args['sslverify'] = false;
                $args['timeout'] = 30;

        //use built in WP http class to work with most server setups
        $response = wp_remote_post($this->url, $args);

        if (is_array($response) && isset($response['body'])) {
          $this->response = $response['body'];
        } else {
          $this->response = "";
          $this->error = true;
          return;
        }

    $this->parseResults();

    if ($this->getResultResponseFull() == "Approved")
    {
          $this->approved = true;
      $this->declined = false;
      $this->error    = false;
          $this->method   = $this->getMethod();
      break;
    } else if ($this->getResultResponseFull() == "Declined")
    {
          $this->approved = false;
      $this->declined = true;
      $this->error    = false;
      break;
    }
    $count++;
      }
    }

    function parseResults()
    {
      $this->results = explode($this->params['x_delim_char'], $this->response);
    }

    function setParameter($param, $value)
    {
      $param                = trim($param);
      $value                = trim($value);
      $this->params[$param] = $value;
    }

    function setTransactionType($type)
    {
      $this->params['x_type'] = strtoupper(trim($type));
    }

    function _prepareParameters()
    {
      foreach($this->params as $key => $value)
      {
    $this->fields .= "$key=" . urlencode($value) . "&";
      }
      for($i=0; $i<count($this->line_items); $i++) {
        $this->fields .= "x_line_item={$this->line_items[$i]}&";
      }
    }

    function getMethod()
    {
      if (isset($this->results[51]))
      {
        return str_replace($this->params['x_encap_char'],'',$this->results[51]);
      }
      return "";
    }

    function getGatewayResponse()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[0]);
    }

    function getResultResponseFull()
    {
      $response = array("", "Approved", "Declined", "Error");
      return $response[str_replace($this->params['x_encap_char'],'',$this->results[0])];
    }

    function isApproved()
    {
      return $this->approved;
    }

    function isDeclined()
    {
      return $this->declined;
    }

    function isError()
    {
      return $this->error;
    }

    function getResponseText()
    {
      return $this->results[3];
      $strip = array($this->params['x_delim_char'],$this->params['x_encap_char'],'|',',');
      return str_replace($strip,'',$this->results[3]);
    }

    function getAuthCode()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[4]);
    }

    function getAVSResponse()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[5]);
    }

    function getTransactionID()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[7]);
    }
  }
}



//helper function for parsing response
function wpc_auth_substring_between($haystack,$start,$end)
{
    if (strpos($haystack,$start) === false || strpos($haystack,$end) === false)
    {
        return false;
    }
    else
    {
        $start_position = strpos($haystack,$start)+strlen($start);
        $end_position = strpos($haystack,$end);
        return substr($haystack,$start_position,$end_position-$start_position);
    }
}


//register payment gateway plugin
wpc_register_gateway_plugin( 'WPC_Gateway_AuthorizeNet_AIM', 'authorizenet-aim', __('Authorize.net AIM Checkout', WPC_CLIENT_TEXT_DOMAIN) );