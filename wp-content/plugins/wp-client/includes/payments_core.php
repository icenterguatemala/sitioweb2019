<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
Core for Payments
*/

if ( !class_exists( 'WPC_Payments_Core' ) ) {

    class WPC_Payments_Core {

        public $step_names = array(
            1 => 'generate-payment',
            2 => 'select-gateway',
            3 => 'input-credentials',
            4 => 'processing-payment',
            5 => 'payment-completed',
        );

        /**
        * PHP 5 constructor
        **/
        function __construct() {
            //load gateways just on settings page
            if ( is_admin() && isset( $_GET['page'] ) && 'wpclients_settings' == $_GET['page'] ) {
                add_action( 'wpc_client_init', array(&$this, 'load_gateway_plugins'), 99 );
            }


            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'add_admin_submenu' ) );

            add_filter( 'wpc_client_settings_tabs', array( &$this, 'add_settings_tab' ), 10 );
            add_action( 'wpc_client_settings_tab_gateways', array( &$this, 'show_settings_page' ) );

            //hook daily cron
            add_action( 'wpc_payments_core_daily', array( &$this, 'daily_cron' ) );

        }


        public function daily_cron() {
            $this->load_gateway_plugins();

            global $wpc_gateway_active_plugins;

            foreach ( (array)$wpc_gateway_active_plugins as $plugin ) {
                do_action( 'wpc_check_status_subscription_' . $plugin->plugin_name );
            }
        }


        /*
        * Function for adding admin submenu
        */
        function add_admin_submenu( $plugin_submenus ) {
            //add separater before addons submenu block
            if ( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) )
                $cap = "wpc_admin";
            else
                $cap = "manage_options";

            $plugin_submenus['wpclients_payments'] = array(
                'page_title'        => __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_payments',
                'capability'        => $cap,
                'function'          => array( &$this, 'payments_history_page' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 10,
            );

            return $plugin_submenus;
        }


        /*
        * Add settings tab
        */
        function add_settings_tab( $tabs ) {
            if ( ! WPC()->flags['easy_mode'] ) {
                $tabs['gateways'] = array(
                    'title'     => __( 'Payment Gateways', WPC_CLIENT_TEXT_DOMAIN ),
                );
            }

            return $tabs;
        }



        /*
        * Show settings page
        */
        function show_settings_page() {
            include_once( WPC()->plugin_dir . 'includes/admin/settings_payment_gateways.php' );
        }


        /*
        *  returns a new unique order id
        */
        function generate_order_id() {
            global $wpdb;

            $count = true;
            while ( $count ) { //make sure it's unique
              $order_id = substr( sha1( uniqid( '' ) ), rand( 1, 24 ), 12 );
              $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpc_client_payments WHERE order_id = '" . $order_id . "'" );
            }

            return $order_id;
        }


        /*
        * save order    -  is deprecated remove after version 3.6.0
        */
        function create_order( $function, $amount, $currency, $client_id = null , $data = array() ) {
            global $wpdb;

            //remove old blank orders
            $old_orders = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_payments WHERE ( order_status IS NULL OR order_status = 'selected_gateway' ) AND time_created < '" . ( time() - 3600*24*5 ) . "'" );
            if ( $old_orders ) {
                $wpdb->query( "DELETE  FROM {$wpdb->prefix}wpc_client_payments WHERE id IN( ". rtrim( implode( ',', $old_orders ), ',') . ") " );
            }

            //create new order
            $client_id = $client_id ? $client_id : get_current_user_id();

            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_payments SET
                order_id = %s,
                function = %s,
                client_id = '%d',
                amount = '%s',
                currency = '%s',
                data = '%s',
                payment_type = 'one_time',
                time_created = '%s'
                ",
                $this->generate_order_id(),
                $function,
                $client_id,
                $amount,
                $currency,
                json_encode( $data ),
                time()
            ) );

            return $wpdb->insert_id;
        }


        /*
        * create new order
        */
        function create_new_order( $args = array() ) {
            global $wpdb;

            $default = array(
                'function' => '',
                'client_id' => null,
                'amount' => 0,
                'currency' => 'USD',
                'payment_type' => 'one_time',
                'payment_method' => null,
                'data' => array(),
            );

            $args = array_merge( $default, $args );

            //remove old blank orders
            $old_orders = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_payments WHERE ( order_status IS NULL OR order_status = 'selected_gateway' ) AND time_created < '" . ( time() - 3600*24*5 ) . "'" );
            if ( $old_orders ) {
                $wpdb->query( "DELETE  FROM {$wpdb->prefix}wpc_client_payments WHERE id IN( ". rtrim( implode( ',', $old_orders ), ',') . ") " );
            }

            $wpdb->insert( $wpdb->prefix . 'wpc_client_payments', array(
                'order_id' => $this->generate_order_id(),
                'function' => $args['function'],
                'client_id' => $args['client_id'],
                'amount' => (float)$args['amount'],
                'currency' => $args['currency'],
                'data' => json_encode( $args['data'] ),
                'payment_type' => $args['payment_type'],
                'payment_method' => $args['payment_method'],
                'time_created' => time()
            ), array(
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ));

            return $wpdb->insert_id;
        }


        function update_order_gateway( $order_id, $selected_gateway ) {
            global $wpdb;

            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET payment_method = %s, order_status = 'selected_gateway' WHERE id = %d ", $selected_gateway, $order_id ) );
        }


        /*
        * get order
        */
        function get_order_by( $order_id, $by = 'id' ) {
            global $wpdb;

            if ( empty( $order_id ) )
              return false;

            if ( !in_array( $by, array( 'id', 'order_id' ) ) ) {
                $by = 'id';
            }


            $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_payments WHERE {$by} = %s ", $order_id ), "ARRAY_A" );

            if ( $order )
                return $order;
            else
                return false;
        }


        /*
        * get orders with the same order_id for partial payments
        */
        function get_orders( $order_ids ) {
            global $wpdb;

            if ( !is_array( $order_ids ) || !count( $order_ids ) )
              return false;

            $orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_payments WHERE id IN ( " . rtrim( implode( ',', $order_ids ), ',' ) . " ) ", "ARRAY_A" );

            if ( $orders )
                return $orders;
            else
                return false;
        }


        function status_on_hold( $order_id, $note ) {
            global $wpdb;

            $order_data = $wpdb->get_var(
                    $wpdb->prepare( "SELECT `data` FROM {$wpdb->prefix}wpc_client_payments "
                    . "WHERE id = %s", $order_id ) );

            if ( empty( $order_data ) ) {
                $order_data = (object)array();
            } else {
                $order_data = json_decode( $order_data );
            }
            $order_data->ipn_note = $note;

            $wpdb->update( $wpdb->prefix . 'wpc_client_payments',
                    array( 'order_status' => 'on_hold', 'data' => json_encode( $order_data ) ),
                    array( 'id' => $order_id ),
                    array( '%s', '%s' ),
                    array( '%d' )
             );
        }


        /*
        *
        */
        function order_update( $order_id, $payment_data ) {
            global $wpdb;

            //get the order
            $order = $this->get_order_by( $order_id );

            if ( $order ) {

                $valid_transaction_types = array(
                    'paid',
                    'pending',
                    'failed',
                    'refunded',
                    'subscription_canceled',
                    'subscription_start',
                    'subscription_payment',
                    'subscription_suspended',
                );

                if ( !isset( $payment_data["transaction_type"] ) || !in_array( $payment_data["transaction_type"], $valid_transaction_types ) ) {
                    return false;
                }

                $actions = array( $payment_data["transaction_type"] );

                switch( $payment_data["transaction_type"] ) {
                    //payment paid
                    case 'paid': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'paid', transaction_id = '%s', transaction_status = '%s', time_paid = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], time(), $order['id'] ) );
                    }
                    break;

                    //payment pending
                    case 'pending': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'pending', transaction_id = '%s', transaction_status = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], $order['id'] ) );
                    }
                    break;

                    //payment failed
                    case 'failed': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'failed', transaction_id = '%s', transaction_status = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], $order['id'] ) );
                    }
                    break;

                    //refund and cancel subscription payments
                    case 'refunded': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'refunded', transaction_id = '%s', transaction_status = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], $order['id'] ) );
                        do_action( 'wpc_invoice_refund', $order['id'] );
                    }
                    break;

                    //start subscription payments
                    case 'subscription_start': {

                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET
                            subscription_id = '%s'
                            WHERE id = '%s'",
                            $payment_data['subscription_id'],
                            $order_id
                        ) );

                    }
                    break;


                    //cancel subscription in payments
                    case 'subscription_canceled': {

                        //furute - for subscription
                        $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT id, subscription_status FROM {$wpdb->prefix}wpc_client_payments WHERE subscription_id = '%s' AND `subscription_status` != 'expire'", $payment_data['subscription_id'] ), ARRAY_A );

                        if ( $subscriptions ) {
                            foreach( $subscriptions as $subscription ) {
                                if ( isset( $subscription['subscription_status'] ) && 'canceled' != $subscription['subscription_status'] ) {
                                    $wpdb->update( $wpdb->prefix . 'wpc_client_payments',
                                        array(
                                            'subscription_status' => 'canceled'
                                        ),
                                        array(
                                            'id' => $subscription['id']
                                        )
                                    );
                                }
                            }
                        }

                    }
                    break;

                    //suspend subscription in payments
                    case 'subscription_suspended': {

                        //furute - for subscription
                        $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT id, subscription_status FROM {$wpdb->prefix}wpc_client_payments WHERE subscription_id = '%s'", $payment_data['subscription_id'] ), ARRAY_A );

                        if ( $subscriptions ) {
                            foreach( $subscriptions as $subscription ) {
                                if ( isset( $subscription['subscription_status'] ) && 'canceled' != $subscription['subscription_status'] ) {
                                    $wpdb->update( $wpdb->prefix . 'wpc_client_payments',
                                        array(
                                            'subscription_status' => 'suspended'
                                        ),
                                        array(
                                            'id' => $subscription['id']
                                        )
                                    );
                                }
                            }
                        }

                    }
                    break;

                    case 'subscription_payment': {
                        $next_payment_date = ( isset( $payment_data['next_payment_date'] ) ) ? strtotime( $payment_data['next_payment_date'] ) : '';
                        $next_payment_date = ( '' != $next_payment_date ) ? date( 'Y-m-d H:i:s', $next_payment_date ) : '';
                        if ( isset( $order['order_status'] ) && 'paid' != $order['order_status'] ||
                                ( empty( $order['subscription_status'] ) || 'active' != $order['subscription_status'] )
                            ) {
                            if ( in_array( $payment_data['transaction_status'], array( 'Completed', 'paid' ) ) ) {
                                $status = 'paid';
                            } else {
                                $status = 'pending';
                            }

                            $payment_data['data'] = isset( $payment_data['data'] )
                                    ? $payment_data['data'] : $order['data'];

                            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET
                                order_status = '%s',
                                transaction_id = '%s',
                                transaction_status = '%s',
                                subscription_id = '%s',
                                subscription_status = '%s',
                                next_payment_date = '%s',
                                time_paid = '%s',
                                `data` = '%s'
                                WHERE id = '%s'",
                                $status,
                                $payment_data['transaction_id'],
                                $payment_data['transaction_status'],
                                $payment_data['subscription_id'],
                                $payment_data['subscription_status'],
                                $next_payment_date,
                                time(),
                                $payment_data['data'],
                                $order_id
                            ) );

                        } else {
                            $args = array(
                                'function' => $order['function'],
                                'client_id' => $order['client_id'],
                                'amount' => !empty( $payment_data['amount'] ) ? $payment_data['amount'] : $order['amount'],
                                'currency' => $order['currency'],
                                'payment_method' => $order['payment_method'],
                                'payment_type' => 'recurring',
                                'data' => isset( $order['data'] ) ? json_decode( $order['data'], true ) : array(),
                            );


                            $order_id = $this->create_new_order( $args );


                            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET
                                order_status = 'paid',
                                transaction_id = '%s',
                                transaction_status = '%s',
                                subscription_id = '%s',
                                subscription_status = '%s',
                                next_payment_date = '%s',
                                time_paid = '%s'
                                WHERE id = '%s'",
                                $payment_data['transaction_id'],
                                $payment_data['transaction_status'],
                                $payment_data['subscription_id'],
                                $payment_data['subscription_status'],
                                $next_payment_date,
                                time(),
                                $order_id
                            ) );

                        }

                        //for check if that subscription expired
                        $actions[] = 'subscription_expired';

                    }
                    break;
                }

                //get new order
                $order = $this->get_order_by( $order_id );

                foreach ( $actions as $val ) {
                    do_action( 'wpc_client_payment_' . $val . '_' . $order['function'], $order );
                }

            }

            return true;
        }


        function update_payments_of_recurring( $subscription_id, $gateway, $new_status ) {
            global $wpdb;
            $orders_of_subscription = $wpdb->get_col( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wpc_client_payments "
                . "WHERE `subscription_id`='%s' AND `payment_method`='%s'"
                    , $subscription_id, $gateway ));

            $wpdb->query( $wpdb->prepare(
                "UPDATE {$wpdb->prefix}wpc_client_payments "
                . "SET `subscription_status`= '%s' WHERE `id` IN ('"
                . implode( "','", $orders_of_subscription ) . "')", $new_status ));
        }


        function payment_step_content( $order, $step = 2 ) {
            global $wpc_gateway_active_plugins;
            //add action for 1st step if some extensions need
            if ( 1 == $step ) {
                $action = get_query_var( 'wpc_order_id' );

                if ( !empty( $action ) ) {
                    $html = apply_filters( 'wpc_client_payment_process_' . $action, '' );
                    return $html;
                }

            }

            $content = '';

            if ( 2 == $step ) {

                    $data       = json_decode( $order['data'], true );

                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';

                    $content .= '<span class="wpc_notice wpc_info" style="float:left;display:block;margin:0 0 20px 0;">' . __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'] . '</span>';

                    $content .= '<form id="wpc_payment_form" method="post" class="wpc_form" action="">';

                    if ( 0 < count( $wpc_gateway_active_plugins ) ) {
                        $content .= '<table class="wpc_cart_payment_methods">';
                        $content .= '<thead><tr>';
                        $content .= '<th>'.__('Choose a Payment Method:', WPC_CLIENT_TEXT_DOMAIN).'</th>';
                        $content .= '</tr></thead>';
                        $content .= '<tbody><tr><td><ul class="wpc_payment_gateways_list">';
                        foreach ((array)$wpc_gateway_active_plugins as $plugin) {
                            $content .= '<li><label>';
                            $content .= '<input type="radio" class="wpc_choose_gateway" name="wpc_choose_gateway" value="'.$plugin->plugin_name.'" /> ';
                            if ($plugin->method_img_url) {
                                $content .= '<img src="' . $plugin->method_img_url . '" alt="' . $plugin->public_name . '" />';
                                $content .= ' ' . $plugin->public_name;
                            } else {
                                $content .= $plugin->public_name;
                            }
                            $content .= '</label></li>';
                        }
                        $content .= '</ul></td>';
                        $content .= '</tr>';
                        $content .= '</tbody>';
                        $content .= '</table>';
                    } else {
                        $content .= '<br/>'.__('No payment method is configured: Please contact to the site administrator.', WPC_CLIENT_TEXT_DOMAIN).'<br/><br/>';
                    }

                    $content .= '<p class="wpc_cart_direct_checkout"><input type="submit" name="wpc_payment_submit" class="wpc_submit" id="wpc_payment_confirm" value="Continue"></p>';
                    $content .= '</form>';

            } else {
                foreach ((array)$wpc_gateway_active_plugins as $plugin) {
                    if( $plugin->plugin_name == $order['payment_method'] ) {
                        $active_gateway = $plugin;
                        break;
                    }
                }

                if ( !empty( $active_gateway ) ) {
                    $content = $active_gateway->payment_process( $order, $step );
                }

                if ( 5 === $step && 0 == $order['amount'] ) {
                    return __( 'Thank you for the payment.', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . $this->get_continue_link( $order, true );
                }
            }

            return $content;
        }


        /*
        * start IPN
        */
        function handle_ipn( $order, $page_value ) {
            global $wpc_gateway_active_plugins;

            //load gateways just for IPN
            $this->load_gateway_plugins();

            $active_gateway = '';

            if ( $order && '' != $order['payment_method'] ) {
                foreach ( (array )$wpc_gateway_active_plugins as $plugin ) {
                    if( $plugin->plugin_name == $order['payment_method'] ) {
                        $active_gateway = $plugin;
                        break;
                    }
                }

            } else {
                foreach ( (array )$wpc_gateway_active_plugins as $plugin ) {
                    if( $plugin->plugin_name == $page_value ) {
                        $active_gateway = $plugin;
                        break;
                    }
                }
            }

            if ( !empty( $active_gateway ) && method_exists( $active_gateway, '_ipn' )) {
                $active_gateway->_ipn( $order );
            }

            exit();

        }


        /*
        * display Paymants pages
        */
        function payments_history_page() {
            include WPC()->plugin_dir . 'includes/admin/payments_history.php';

        }


        /*
        * display Paymants pages
        */
        function get_continue_link( $order, $with_text = true ) {

            $link = '';

            if ( isset( $order['function'] ) && '' != $order['function'] ) {
                $link = apply_filters( 'wpc_payment_get_continue_link_' . $order['function'], $link, $order, $with_text );
            }

            return $link;
        }


        /*
        * cancel recurring
        */
        function cancel_recurring( $order_id ) {
            global $wpc_gateway_active_plugins;

            $this->load_gateway_plugins();

            $order = $this->get_order_by( $order_id );

            if ( isset( $order['payment_type'] ) && 'recurring' == $order['payment_type']
                && !empty( $order['subscription_id'] ) && 'canceled' != $order['subscription_id'] ) {

                if ( !empty( $order['payment_method'] ) ) {
                    foreach( (array)$wpc_gateway_active_plugins as $plugin ) {
                        if ( $plugin->plugin_name == $order['payment_method'] ) {
                            $active_gateway = $plugin;
                            break;
                        }
                    }

                    if ( !empty( $active_gateway ) && method_exists( $active_gateway, 'subscription_cancel' ) ) {
                        $active_gateway->subscription_cancel( $order['subscription_id'] );
                    }
                }

            }

        }


        /*
        *
        */
        function load_gateway_plugins() {
            global $wpc_gateway_plugins, $wpc_gateway_active_plugins;


            //get gateway plugins dir
            $dir = WPC()->plugin_dir . 'includes/payment_gateways/';

            //search the dir for files
            $gateway_plugins = array();
            if ( !is_dir( $dir ) )
                return;
            if ( ! $dh = opendir( $dir ) )
                return;
            while ( ( $plugin = readdir( $dh ) ) !== false ) {
                if ( substr( $plugin, -4 ) == '.php' )
                    $gateway_plugins[] = $dir . $plugin;
            }
            closedir( $dh );


            //get extra custom gateway plugins
            $dir = WPC()->get_upload_dir( 'wpclient/_payment_gateways/' );

            if ( $dh = opendir( $dir ) ) {
                while ( ( $plugin = readdir( $dh ) ) !== false ) {
                    if ( substr( $plugin, -4 ) == '.php' )
                        $gateway_plugins[] = $dir . $plugin;
                }
                closedir( $dh );
            }



            //get extra custom gateway plugins from url
            $dir = apply_filters( 'wpc_client_external_gateways_dir', '' );

            if ( !empty( $dir ) && is_dir( $dir ) ) {
                if ( $dh = opendir( $dir ) ) {
                    while ( ( $plugin = readdir( $dh ) ) !== false ) {
                        if ( substr( $plugin, -4 ) == '.php' )
                            $gateway_plugins[] = $dir . $plugin;
                    }
                    closedir( $dh );
                }
            }

            sort( $gateway_plugins );

            //include them suppressing errors
            foreach ( $gateway_plugins as $file )
                include_once( $file );

            //load chosen plugin classes
            $wpc_gateways = WPC()->get_settings( 'gateways' );


            foreach ( (array)$wpc_gateway_plugins as $code => $plugin ) {
                $class = $plugin[0];
                if ( isset( $wpc_gateways['allowed'] ) && in_array( $code, (array)$wpc_gateways['allowed'] ) && class_exists( $class ) )
                    $wpc_gateway_active_plugins[] = new $class;
            }

        }


        function build_checkout_form( $getaway, $args ) {
            ob_start(); ?>

            <style type="text/css">
                #card_num {
                    width: calc( 100% - 48px ) !important;
                    float:left;
                    margin: 0 8px 0 0 !important;
                }

                .wpc_payment_form .wpc_form_label {
                    width:25%;
                }

                .wpc_payment_form .wpc_form_field {
                    width:75%;
                }

            </style>
            <script type="text/javascript">
                function wpc_card_pick(card_image, card_num){
                    if (card_image == null) {
                        card_image = "#wpc_cardimage";
                    }
                    if (card_num == null) {
                        card_num = "#card_num";
                    }

                    numLength = jQuery(card_num).val().length;
                    number = jQuery(card_num).val();
                    if (numLength > 10) {
                        if((number.charAt(0) == "4") && ((numLength == 13)||(numLength==16))) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("wpc_cardimage visa_card"); }
                        else if((number.charAt(0) == "5" && ((number.charAt(1) >= "1") && (number.charAt(1) <= "5"))) && (numLength==16)) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("wpc_cardimage mastercard"); }
                        else if(number.substring(0,4) == "6011" && (numLength==16))     { jQuery(card_image).removeClass(); jQuery(card_image).addClass("wpc_cardimage amex"); }
                        else if((number.charAt(0) == "3" && ((number.charAt(1) == "4") || (number.charAt(1) == "7"))) && (numLength==15)) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("wpc_cardimage discover_card"); }
                        else { jQuery(card_image).removeClass(); jQuery(card_image).addClass("wpc_cardimage nocard"); }
                    }
                }

                var form = "";
                jQuery(document).ready( function($) {
                    form = $( "#wpc_payment_form_<?php echo $getaway ?>" );

                    if( form.find('*[data-required_field="1"]').length > 0 ) {
                        form.find('input[type="submit"]').prop("disabled", true).attr("disabled",true);
                        infoSubmit();
                    }

                    //input fields
                    form.find("input").focusout( function() {
                        //check field on required value
                        var field = $(this).parents(".wpc_form_field");
                        if( $(this).data("required_field") ) {
                            if ( $(this).val() == "" ) {
                                //if field empty
                                showValidationMessage( field, "required" );
                            } else {
                                hideValidationMessage( field );
                            }

                            triggerSubmit();
                        }
                    });


                    form.on("keyup", ".wpc_form_field.wpc_validate_error input", function() {
                        //check field on required value
                        var field = $(this).parents(".wpc_form_field");

                        if( $(this).data("required_field") && $(this).val() == "" ) {
                            //if field required and empty
                            showValidationMessage( field, "required" );
                        } else {
                            //if field not required or required and not empty
                            //check field content
                            hideValidationMessage( field );
                        }

                        triggerSubmit();
                    });


                    //focus out from selectbox fields
                    form.find("select").focusout( function() {
                        //check field on required value
                        var field = $(this).parents(".wpc_form_field");

                        if( $(this).data("required_field") ) {
                            if ( $(this).val() == "" || $(this).val() == null ) {
                                //if field required and empty
                                showValidationMessage( field, "required" );
                            } else {
                                //if field not required or required and not empty
                                hideValidationMessage( field );
                            }

                            triggerSubmit();
                        }
                    });

                    form.on("change", ".wpc_form_field.wpc_validate_error select", function() {
                        //check field on required value
                        var field = $(this).parents(".wpc_form_field");

                        if( $(this).data("required_field") ) {
                            if ( $(this).val() == "" || $(this).val() == null ) {
                                //if field required and empty
                                showValidationMessage( field, "required" );
                            } else {
                                //if field not required or required and not empty
                                hideValidationMessage( field );
                            }

                            triggerSubmit();
                        }
                    });

                    jQuery(".noautocomplete").attr("autocomplete", "off");

                    jQuery("#wpc_payment_profile").change(function() {
                        var value = jQuery(this).val();
                        if( value === "" ) {
                            jQuery(".new_card_fields").prop("disabled", false).removeClass('hide_required_field');
                            jQuery(".new_card_fields").parents('.wpc_form_line').slideDown();
                        } else {
                            jQuery(".new_card_fields").prop("disabled", true).addClass('hide_required_field' );
                            jQuery(".new_card_fields").parents('.wpc_form_line').slideUp();
                        }
                        triggerSubmit();
                    });
                });

                function showValidationMessage( field, type ) {
                    field.find( ".wpc_field_validation" ).children().hide();
                    field.find( ".wpc_field_" + type ).show();
                    field.addClass( "wpc_validate_error" );
                }

                function hideValidationMessage( field ) {
                    field.find( ".wpc_field_validation" ).children().hide();
                    field.removeClass( "wpc_validate_error" );
                }


                function triggerSubmit() {
                    if( form.find('*[data-required_field="1"]').length > 0 ) {
                        var validated = 0;

                        form.find('*[data-required_field="1"]').each(function () {
                            if( !jQuery(this).hasClass( 'hide_required_field' ) ) {
                                if (jQuery(this).prop("tagName").toLowerCase() == "select") {
                                    if (!( jQuery(this).val() == "" || jQuery(this).val() == null )) {
                                        //if field not required or required and not empty
                                        validated++;
                                    }
                                } else if (jQuery(this).prop("tagName").toLowerCase() == "input") {
                                    if (jQuery(this).val() != "") {
                                        //if field not empty
                                        //check field content
                                        validated++;
                                    }
                                }
                            }
                        });

                        if( form.find('*[data-required_field="1"]:not(.hide_required_field)').length == validated ) {
                            form.find('input[type="submit"]').prop("disabled",false).attr("disabled",false);
                        } else {
                            form.find('input[type="submit"]').prop("disabled",true).attr("disabled",true);
                        }
                    } else {
                        form.find('input[type="submit"]').prop("disabled",false).attr("disabled",false);
                    }

                    infoSubmit();
                }

                function infoSubmit() {
                    var html = "";
                    if( form.find('*[data-required_field="1"]').length > 0 ) {
                        form.find('*[data-required_field="1"]').each( function() {
                            if( !jQuery(this).hasClass( 'hide_required_field' ) ) {
                                var label = form.find('label[for="' + jQuery(this).attr("id") + '"]').data("title");
                                if (jQuery(this).prop("tagName").toLowerCase() == "select") {
                                    if ( jQuery(this).val() == "" || jQuery(this).val() == null ) {
                                        //if field not required or required and not empty
                                        html =  "<?php echo esc_js( __( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ) ) ?> \"<a href=\"#" + jQuery(this).attr("id") + "\">" + label + "</a>\"";
                                        return false;
                                    }
                                } else if (jQuery(this).prop("tagName").toLowerCase() == "input") {
                                    if (jQuery(this).val() == "") {
                                        html = "<?php echo esc_js( __( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ) ) ?> \"<a href=\"#" + jQuery(this).attr("id") + "\">" + label + "</a>\"";
                                        return false;
                                    }
                                }
                            }
                        });
                    }

                    if( form.find(".wpc_submit_info").html() != html ) {
                        form.find(".wpc_submit_info").html(html);
                    }
                }
            </script>

            <form id="wpc_payment_form_<?php echo $getaway ?>" method="post" class="wpc_form wpc_payment_form" action="<?php echo $args['action'] ?>">
                <?php foreach ( $args['fields'] as $key=>$field ) {
                    if ( !empty( $field['classes'] ) ) {
                        $field['classes'] = implode( ' ', $field['classes'] );
                    } else {
                        $field['classes'] = '';
                    }

                    if( empty( $field['required'] ) ) {
                        $field['required'] = false;
                    }


                    $attributes = '';
                    if( !empty( $field['attributes'] ) ) {
                        foreach( $field['attributes'] as $a_key=>$attribute ) {
                            $attributes .= " $a_key=\"$attribute\"";
                        }
                    }

                    $position = 'full';
                    if( !empty( $field['position'] ) ) {
                        $position = $field['position'];
                    }

                    if( $field['type'] == 'hidden' ) { ?>
                        <input type="<?php echo $field['type'] ?>" id="<?php echo $field['id'] ?>" name="<?php echo $field['name'] ?>" value="<?php echo isset( $field['value'] ) ? $field['value'] : '' ?>" class="<?php echo $field['classes'] ?>" <?php if ( $field['required'] ) { ?>data-required_field="1"<?php } ?> <?php echo $attributes ?> />
                        <?php continue;
                    } ?>

                    <div class="wpc_form_line">
                        <div class="wpc_form_label">
                            <?php if( $position == 'full' ) {
                                if ( $field['type'] == 'exp_date' ) { ?>
                                    <label data-title="<?php echo $field['label'] ?>" for="<?php echo $field['m_id'] ?>">
                                        <?php echo $field['label'] ?>&nbsp;
                                        <?php if ( $field['required'] ) { ?>
                                            <span style="color:red;">*</span>
                                        <?php } ?>
                                    </label>
                                    <label data-title="<?php echo $field['label'] ?>" for="<?php echo $field['y_id'] ?>" style="display: none;"></label>
                                <?php } else { ?>
                                    <label data-title="<?php echo $field['label'] ?>" for="<?php echo $field['id'] ?>">
                                        <?php echo $field['label'] ?>&nbsp;
                                        <?php if ( $field['required'] ) { ?>
                                            <span style="color:red;">*</span>
                                        <?php } ?>
                                    </label>
                                <?php }
                            } else { ?>
                                &nbsp;
                            <?php } ?>
                        </div>
                        <div class="wpc_form_field">
                            <?php do_action( 'wpc_payment_getaway_before_field_' . $getaway, $field );

                            if ( $field['type'] == 'exp_date' ) {
                                if ( !empty( $field['m_classes'] ) ) {
                                    $field['m_classes'] = implode( ' ', $field['m_classes'] );
                                } else {
                                    $field['m_classes'] = '';
                                }

                                if ( !empty( $field['y_classes'] ) ) {
                                    $field['y_classes'] = implode( ' ', $field['y_classes'] );
                                } else {
                                    $field['y_classes'] = '';
                                } ?>
                                <select name="<?php echo $field['m_name'] ?>" id="<?php echo $field['m_id'] ?>" class="<?php echo $field['m_classes'] ?>" <?php if ( $field['required'] ) { ?>data-required_field="1"<?php } ?> style="width: 90px;">
                                    <option value="01">01 - Jan</option>
                                    <option value="02">02 - Feb</option>
                                    <option value="03">03 - Mar</option>
                                    <option value="04">04 - Apr</option>
                                    <option value="05">05 - May</option>
                                    <option value="06">06 - Jun</option>
                                    <option value="07">07 - Jul</option>
                                    <option value="08">08 - Aug</option>
                                    <option value="09">09 - Sep</option>
                                    <option value="10">10 - Oct</option>
                                    <option value="11">11 - Nov</option>
                                    <option value="12">12 - Dec</option>
                                </select>
                                /
                                <select name="<?php echo $field['y_name'] ?>" id="<?php echo $field['y_id'] ?>" class="<?php echo $field['y_classes'] ?>" <?php if ( $field['required'] ) { ?>data-required_field="1"<?php } ?> style="width: 70px;">
                                    <?php $localDate = getdate();
                                    $minYear = $localDate["year"];
                                    $maxYear = $minYear + 15;

                                    for( $i = $minYear; $i < $maxYear; $i++ ) { ?>
                                        <option value="<?php echo substr($i, 0, 4) ?>"><?php echo $i ?></option>
                                    <?php } ?>
                                </select>
                            <?php } elseif ( $field['type'] == 'card_number' ) { ?>
                                <input type="text" id="<?php echo $field['id'] ?>" name="<?php echo $field['name'] ?>" class="<?php echo $field['classes'] ?>"
                                       <?php if ( $field['required'] ) { ?>data-required_field="1"<?php } ?> onkeyup="wpc_card_pick('#wpc_cardimage', '#<?php echo $field['id'] ?>');"
                                       <?php echo $attributes ?> />
                                <div class="hide_after_success nocard wpc_cardimage" id="wpc_cardimage"></div>
                            <?php } elseif ( $field['type'] == 'select' ) { ?>
                                <select id="<?php echo $field['id'] ?>" name="<?php echo $field['name'] ?>" class="<?php echo $field['classes'] ?>" <?php echo $attributes ?>>
                                    <?php foreach ( $field['options'] as $opt_key=>$opt_label ) { ?>
                                        <option value="<?php echo $opt_key ?>"><?php echo $opt_label ?></option>
                                    <?php } ?>
                                </select>
                            <?php } else {
                                if( $position == 'full' ) { ?>
                                    <input type="<?php echo $field['type'] ?>" id="<?php echo $field['id'] ?>" name="<?php echo $field['name'] ?>" value="<?php echo isset( $field['value'] ) ? $field['value'] : '' ?>" class="<?php echo $field['classes'] ?>" <?php if ( $field['required'] ) { ?>data-required_field="1"<?php } ?> <?php echo $attributes ?> />
                                <?php } else { ?>
                                    <label>
                                        <input type="<?php echo $field['type'] ?>" id="<?php echo $field['id'] ?>" name="<?php echo $field['name'] ?>" value="<?php echo isset( $field['value'] ) ? $field['value'] : '' ?>" class="<?php echo $field['classes'] ?>" <?php if ( $field['required'] ) { ?>data-required_field="1"<?php } ?> <?php echo $attributes ?> />&nbsp;
                                        <?php echo $field['label'] ?>
                                    </label>
                                <?php }
                            } ?>

                            <?php if ( $field['required'] ) { ?>
                                <div class="wpc_field_validation">
                                    <?php foreach ( $field['validation'] as $v_key=>$validation ) { ?>
                                        <span class="wpc_field_<?php echo $v_key ?>"><?php echo $validation ?></span>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="wpc_form_line">
                    <div class="wpc_form_label">
                        &nbsp;
                    </div>
                    <div class="wpc_form_field">
                        <input type="submit" class="wpc_submit" name="wpc_payment_submit" id="<?php echo ( !empty( $args['submit_id'] ) ) ? $args['submit_id'] : 'wpc_payment_confirm' ?>" value="<?php _e( 'Confirm Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                    </div>
                </div>
                <div class="wpc_form_line">
                    <div class="wpc_form_label">&nbsp;</div>
                    <div class="wpc_form_field">
                        <div class="wpc_submit_info" style="float: left;width: 100%;"></div>
                    </div>
                </div>
            </form>

            <?php $html = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            return $html;
        }


        //end class
    }


    $GLOBALS['wpc_gateway_plugins'] = array();
    $GLOBALS['wpc_gateway_active_plugins'] = array();
    $GLOBALS['wpc_payments_core'] = new WPC_Payments_Core();

    /**
     * Use this function to register your gateway plugin class
     *
     * @param string $class_name - the case sensitive name of your plugin class
     * @param string $plugin_name - the sanitized private name for your plugin
     * @param string $admin_name - pretty name of your gateway, for the admin side.
     */
    function wpc_register_gateway_plugin($class_name, $plugin_name, $admin_name) {
      global $wpc_gateway_plugins;

      if ( !is_array( $wpc_gateway_plugins ) ) {
            $wpc_gateway_plugins = array();
        }

        if ( class_exists( $class_name ) ) {
            $wpc_gateway_plugins[ $plugin_name ] = array( $class_name, $admin_name );
        } else {
            return false;
        }

        return true;
    }

}