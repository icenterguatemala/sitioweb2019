<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'WPC_Gateway_Stripe' ) ) {
    class WPC_Gateway_Stripe {

        //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
        var $plugin_name = 'stripe';

        //name of your gateway, for the admin side.
        var $admin_name = '';

        //public name of your gateway, for lists and such.
        var $public_name = '';

        //url for an image for your checkout method. Displayed on checkout form if set
        var $method_img_url = '';

        //url for an submit button image for your checkout method. Displayed on checkout form if set
        var $method_button_img_url = '';

        //whether or not ssl is needed for checkout page
        var $force_ssl;

        //has recurring
        var $recurring = true;

        //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
        var $ipn_url;

        //whether if this is the only enabled gateway it can skip the payment_form step
        var $skip_form = false;

        //api vars
        var $publishable_key, $private_key;

        private $step_names;

        //zero-decimal currencies - do not need * 100
        var $zero_decimal_currencies = array( 'BIF', 'DJF', 'JPY', 'KRW', 'PYG', 'VND', 'XAF', 'XPF', 'CLP', 'GNF', 'KMF', 'MGA', 'RWF', 'VUV', 'XOF' );


        /**
        * Runs when your class is instantiated.
        */
        function __construct( $gateway = NULL ) {
            global $wpc_payments_core;

            $this->step_names = $wpc_payments_core->step_names;

            $wpc_gateways = WPC()->get_settings( 'gateways' );

            //set names here to be able to translate
            $this->admin_name = __('Stripe', WPC_CLIENT_TEXT_DOMAIN);
            $this->public_name = __('Stripe', WPC_CLIENT_TEXT_DOMAIN);

            if ( isset( $wpc_gateways[ $this->plugin_name ]['public_name'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['public_name'] ) ) {
                $this->public_name = $wpc_gateways[ $this->plugin_name ]['public_name'];
            }

            if ( !isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) || 1 != $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) {
                $this->recurring = false;
            }

            $this->method_img_url = WPC()->plugin_url . 'images/stripe.png';
            $this->method_button_img_url = WPC()->plugin_url . 'images/stripe.png';

            if ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ) {
                $this->method_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
            }


            $this->publishable_key = ( isset( $wpc_gateways[ $this->plugin_name ]['publishable_key'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['publishable_key'] ) : '';
            $this->private_key = ( isset( $wpc_gateways[ $this->plugin_name ]['private_key'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['private_key'] ) : '';

            $this->force_ssl = ( isset( $wpc_gateways[ $this->plugin_name ]['is_ssl'] ) ) ? (bool)$wpc_gateways[ $this->plugin_name ]['is_ssl'] : false;

            $this->enqueue_scripts();

            $this->gateway = $gateway;

            add_action( 'wpc_cancel_subscription_' . $this->plugin_name, array( &$this, 'cancel_subscription' ) );

            return $this->gateway;
        }


        /**
         *
         */
        function cancel_subscription( $payments ) {
            if ( is_numeric( $payments ) )
                $payments = array( $payments );

            if ( !is_array( $payments ) )
                return '';

            global $wpdb;
            $data = $wpdb->get_results( "SELECT `id`, `data`, `transaction_id`, `subscription_id` "
                . "FROM {$wpdb->prefix}wpc_client_payments "
                . "WHERE id IN ('" . implode( "','", $payments ) . "') "
                . "GROUP BY `subscription_id`" , ARRAY_A );
            foreach ( $data as $plan ) {
                $payment_data = json_decode($plan['data'], true);
                if( !empty( $plan['transaction_id'] ) && !empty( $payment_data['customer_id'] ) ) {
                    $plan_id = $plan['transaction_id'];
                    $customer_id = $payment_data['customer_id'];
                    $this->delete_recurring_subscription( $plan_id, $customer_id );
                }
            }
        }


        /**
         * Delete the stripe customer and plan
         */
        function delete_recurring_subscription( $plan_id, $customer_id, $action = 'delete', $ipn_delete = false ) {
            global $wpdb, $wpc_payments_core;

            $status = ( 'delete' == $action ) ? 'canceled' : $action;

            $subscription_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT `subscription_id` FROM {$wpdb->prefix}wpc_client_payments "
                    . "WHERE `order_id`='%s'"
                            , $plan_id));

            $wpc_payments_core->update_payments_of_recurring( $subscription_id, $this->plugin_name, $status );

            require_once(WPC()->plugin_dir .
                "includes/payment_gateways/stripe-files/lib/Stripe.php");
            Stripe::setApiKey( $this->private_key );

            if ( !$ipn_delete ) {
                try {
                    $cu = Stripe_Customer::retrieve( $customer_id );
                    $cu->delete();
                } catch (Exception $e) {
                    error_log( $e->getMessage() );
                }
            }

            try {
                $plan = Stripe_Plan::retrieve( $plan_id );
                $plan->delete();
            } catch (Exception $e) {
                error_log( $e->getMessage() );
            }
        }


        function enqueue_scripts() {
            global $wp_query, $wpc_payments_core;

            if ( isset( $wp_query->query_vars['wpc_page_value'] ) && $wpc_payments_core->step_names['3'] == $wp_query->query_vars['wpc_page_value'] ) {

                wp_enqueue_script( 'stripe-token', false, array('js-stripe'), WPC_CLIENT_VER, true );

                wp_localize_script( 'stripe-token', 'stripe', array('publisher_key' => $this->publishable_key,
                    'name' =>__('Please enter the full Cardholder Name.', WPC_CLIENT_TEXT_DOMAIN),
                    'number' => __('Please enter a valid Credit Card Number.', WPC_CLIENT_TEXT_DOMAIN),
                    'expiration' => __('Please choose a valid expiration date.', WPC_CLIENT_TEXT_DOMAIN),
                    'cvv2' => __('Please enter a valid card security code. This is the 3 digits on the signature panel, or 4 digits on the front of Amex cards.', WPC_CLIENT_TEXT_DOMAIN)
                ) );
            }
        }


        function payment_process( &$order, $step = 3 ) {
            global $wpc_payments_core;

            $content = '';

            switch( $step ) {
                case 3: {

                    $wpc_gateways = WPC()->get_settings( 'gateways' );

                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                    //make link
                    $name = $this->step_names[4];
                    if ( WPC()->permalinks ) {
                        $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                    } else {
                        $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
                    }


                    $content .= '<span class="wpc_notice wpc_info" style="float:left;display:block;margin:0 0 20px 0;">' . __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'] . '</span>';

                    //empty API
                    if ( empty( $this->publishable_key ) || empty( $this->private_key ) ) {

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

                    ob_start();

                    $amount_for_checkout = ( !in_array( $order['currency'], $this->zero_decimal_currencies ) ) ? $order['amount'] * 100 : $order['amount'];

                    ?>
                    <br>
                    <br>
                    <form class="wpc_form"><input type="submit" id="wpc_payment_button" class="wpc_submit" value="<?php _e( "Pay now", WPC_CLIENT_TEXT_DOMAIN ); ?>" /></form>
                    <form method="post" style="display: none;" id="wpc_gateway_form"  action="<?php echo $redirect; ?>">
                    </form>
                    <?php
                    $content .= ob_get_contents();
                    if( ob_get_length() ) {
                        ob_end_clean();
                    }

                    ob_start();
                    ?>

                    <script src="https://checkout.stripe.com/checkout.js"></script>
                    <script>
                      var handler = '';
                      jQuery(document).ready(function(e) {
                        handler = StripeCheckout.configure({
                            key: stripe.publisher_key,
                            <?php echo !empty( $wpc_gateways[ $this->plugin_name ]['avs'] ) ? ' billingAddress: true,' : '' ?>
                            token: function(token) {
                                var $input = jQuery('<input type="hidden" name="stripeToken" />').val( token.id );
                                jQuery('#wpc_gateway_form').append($input).submit();
                            }
                        });

                        // Open Checkout with further options
                        open_payment_popup();
                        jQuery('#wpc_payment_button').click( open_payment_popup );
                      });

                      function open_payment_popup() {
                          handler.open({
                              name: '<?php echo esc_js( $item_name ); ?>',
                              description: '<?php echo esc_js( $item_name ) . ' - ' . $order['amount'] . ' ' . $order['currency']; ?>',
                              amount: '<?php echo $amount_for_checkout; ?>',
                              currency: '<?php echo $order['currency']; ?>'
                            });
                          return false;
                      }
                    </script>

                    <?php
                    $script = ob_get_contents();
                    if( ob_get_length() ) {
                        ob_end_clean();
                    }
                    echo $script;

                    break;
                }

                case 4: {

                    //make sure token is set at this point
                    if ( !isset( $_POST['stripeToken'] ) ) {
                        $content .= __('The Stripe Token was not generated correctly. Please go back and try again.', WPC_CLIENT_TEXT_DOMAIN);
                        break;
                    }

                    require_once(WPC()->plugin_dir . "includes/payment_gateways/stripe-files/lib/Stripe.php");
                    Stripe::setApiKey( $this->private_key );
                    try {
                        $token = Stripe_Token::retrieve( $_POST['stripeToken'] );
                    } catch (Exception $e) {
                        $content .= sprintf(__('%s. Please go back and try again.', WPC_CLIENT_TEXT_DOMAIN), $e->getMessage());
                        break;
                    }
                    $total      = $order['amount'];
                    $data       = json_decode( $order['data'], true );

                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                    try {
                        if( 'recurring' === $order['payment_type'] ) {

                            global $current_user;

                            $wpc_gateways = WPC()->get_settings( 'gateways' );

                            $plan_id = $order['order_id'];

                            //if set first payment
                            if ( !empty( $data['a1'] ) && !empty( $data['a3'] )
                                    && ( $data['a1'] != $data['a3'] ) ) {
                                $account_balance = $data['a3'] - $data['a1'];
                                $total = $data['a3'];
                            }

                            $amount_for_checkout = ( !in_array( $order['currency'], $this->zero_decimal_currencies ) ) ? $total * 100 : $total;


                            Stripe_Plan::create(array(
                                "id" => $plan_id,
                                "name" => substr( $item_name, 0, 100 ) . ' - ' . $order['order_id'],
                                "amount" => (float)$amount_for_checkout, // amount in cents, again
                                "currency" => $order['currency'],
                                "interval" => $data['t3'],
                                "interval_count" => $data['p3'],
                                "statement_descriptor" => !empty( $wpc_gateways[ $this->plugin_name ]['statement_descriptor'] )
                                                                ? $wpc_gateways[ $this->plugin_name ]['statement_descriptor']
                                                                : "WP-Client",
                            ));

                            $args = array(
                                "source" => $_POST['stripeToken'],
                                "plan" => $plan_id,
                                "email" => $current_user->user_email
                            );

                            if ( !empty( $account_balance ) ) {
                                $args['account_balance'] = ( !in_array( $order['currency'], $this->zero_decimal_currencies ) ) ? -$account_balance * 100 : -$account_balance;
                            }

                            Stripe_Customer::create( $args );

                        } else {

                            $amount_for_checkout = ( !in_array( $order['currency'], $this->zero_decimal_currencies ) ) ? $total * 100 : $total;

                            // create the charge on Stripe's servers - this will charge the user's card
                            $charge = Stripe_Charge::create(array(
                                "amount" => (float)$amount_for_checkout, // amount in cents, again
                                "currency" => $order['currency'],
                                "card" => $_POST['stripeToken'],
                                "description" => $item_name
                            )
                            );

                            if ($charge->paid == 'true') {

                                $payment_data = array();
                                $payment_data['transaction_status'] = "paid";
                                $payment_data['subscription_id'] = null;
                                $payment_data['subscription_status'] = null;
                                $payment_data['parent_txn_id'] = null;
                                $payment_data['transaction_type'] = 'paid';
                                $payment_data['transaction_id'] = $charge->id;
                                $wpc_payments_core->order_update( $order['id'], $payment_data );
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

                        WPC()->redirect( $redirect );

                    } catch (Exception $e) {
                        //make link
                        $name = $this->step_names[3];
                        if ( WPC()->permalinks ) {
                            $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/?error=1";
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name, 'error' => 1 ), get_home_url()  );
                        }
                    }

                    $content .= sprintf(__('There was an error processing your card: "%s" ', WPC_CLIENT_TEXT_DOMAIN), $e->getMessage() );
                    $content .= '<br>';
                    $content .= '<a href="' . $redirect . '">' . __('Please try again', WPC_CLIENT_TEXT_DOMAIN) . '</a>';

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


        function _ipn( $order ) {
            require_once(WPC()->plugin_dir . "includes/payment_gateways/stripe-files/lib/Stripe.php");
            Stripe::setApiKey( $this->private_key );

            // Retrieve the request's body and parse it as JSON
            $input = @file_get_contents("php://input");


            $event_json = json_decode($input, true);

            $array_events = array( 'invoice.payment_succeeded', 'customer.subscription.deleted' );
            if ( empty( $event_json['type'] ) || !in_array( $event_json['type'], $array_events ) )
                return '';

            switch( $event_json['type'] ) {
                case 'invoice.payment_succeeded':

                    if ( empty( $event_json['data']['object']['lines']['data'][0]['plan']['id'] ) )
                        return '';

                    $subscription = $event_json['data']['object']['lines']['data'][0];
                    $subscription_id = ( !empty($subscription['id']) ) ? $subscription['id'] : '';
                    $subscription_amount = ( !empty($subscription['amount']) ) ? $subscription['amount'] : '';

                    $customer_id = isset( $event_json['data']['object']['customer'] )
                            ? $event_json['data']['object']['customer'] : '';
                    $plan_id = $order_id = $subscription['plan']['id'];

                    global $wpc_payments_core;
                    $order_data = $wpc_payments_core->get_order_by( $order_id, 'order_id' );

                    if ( !$order_data )
                        return '';


                    $temp = json_decode($order_data['data'], true);

                    if ( empty( $temp['customer_id'] ) ) {
                        //first ipn
                        $temp['customer_id'] = $customer_id;
                        $order_data['data'] = json_encode($temp);
                        $order_data['subscription_status'] = 'active';
                        $order_data['subscription_id'] = $subscription_id;
                    } else {
                        $order_data['data'] = json_encode($temp);
                    }
                    $order_data['transaction_id'] = $plan_id;
                    $order_data['transaction_type'] = 'subscription_payment';
                    $order_data['transaction_status'] = 'paid';
                    if( !empty( $subscription_amount ) && !in_array( $order['currency'], $this->zero_decimal_currencies ) ) {
                        $order_data['amount'] = $subscription_amount / 100;
                    } else {
                        $order_data['amount'] = $subscription_amount;
                    }

                    $wpc_payments_core->order_update( $order_data['id'], $order_data );

                    $function = ( !empty( $order_data['function'] ) ) ? $order_data['function'] : '';
                    $expire_subscription = apply_filters( 'wpc_client_check_expire_subscription_'. $function
                            , '', $order_data );

                    if ( $expire_subscription )
                        $this->delete_recurring_subscription( $plan_id, $customer_id, 'expired' );

                    break;

                case 'customer.subscription.deleted':
                    global $wpc_payments_core;
                    if ( empty( $event_json['data']['object']['plan']['id'] ) )
                        return '';
                    $subscription = $event_json['data']['object'];
                    $subscription_id = ( !empty($subscription['id']) ) ? $subscription['id'] : '';
                    $customer_id = isset( $event_json['data']['object']['customer'] )
                            ? $event_json['data']['object']['customer'] : '';
                    $plan_id = $order_id = $subscription['plan']['id'];

                    $order_data = $wpc_payments_core->get_order_by( $order_id, 'order_id' );
                    if ( empty( $order_data ) )
                        return '';

                    if ( !empty( $order_data['subscription_status'] )
                            && 'expired' != $order_data['subscription_status'] ) {

                        $this->delete_recurring_subscription( $plan_id, $customer_id, 'delete', true );

                        $order_data['transaction_type'] = 'subscription_canceled';
                        $wpc_payments_core->order_update( $order_data['id'], $order_data );
                    }

                    break;
            }

            //header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        }


        /**
         * Print the years
         */
        function _print_year_dropdown($sel='', $pfp = false) {
            $localDate=getdate();
            $minYear = $localDate["year"];
            $maxYear = $minYear + 15;

            $output = "<option value=''>--</option>";
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


        /**
         * Print the months
         */
        function _print_month_dropdown($sel='') {
            $output =  "<option value=''>--</option>";
            $output .=  "<option " . ($sel==1?' selected':'') . " value='01'>01 - Jan</option>";
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
        *  You can access saved settings via $settings array.
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
                <h3 class='hndle'><span><?php _e('Stripe', WPC_CLIENT_TEXT_DOMAIN) ?></span> - <span class="description"><?php _e('Stripe makes it easy to start accepting credit cards directly on your site with full PCI compliance', WPC_CLIENT_TEXT_DOMAIN); ?></span></h3>
                <div class="inside">
                    <span class="description"><?php _e("Accept Visa, MasterCard, American Express, Discover, JCB, and Diners Club cards directly on your site. You don't need a merchant account or gateway. Stripe handles everything, including storing cards, subscriptions, and direct payouts to your bank account. Credit cards go directly to Stripe's secure environment, and never hit your servers so you can avoid most PCI requirements.", WPC_CLIENT_TEXT_DOMAIN); ?> <a href="https://stripe.com/" target="_blank"><?php _e('More Info &raquo;', WPC_CLIENT_TEXT_DOMAIN) ?></a></span>
                    <table class="form-table">
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
                        <tr valign="top">
                        <th scope="row"><?php _e('Stripe Mode', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                            <span class="description"><?php _e('When in live mode Stripe recommends you have an SSL certificate setup for the site where the checkout form will be displayed.', WPC_CLIENT_TEXT_DOMAIN); ?> <a href="https://stripe.com/help/ssl" target="_blank"><?php _e('More Info &raquo;', WPC_CLIENT_TEXT_DOMAIN) ?></a></span><br/>
                            <select name="wpc_gateway[<?php echo $this->plugin_name ?>][is_ssl]">
                                <option value="0" <?php echo isset( $wpc_gateways[ $this->plugin_name ]['is_ssl'] ) && 0 == $wpc_gateways[ $this->plugin_name ]['is_ssl'] ? 'selected' : '' ?>><?php _e('No SSL (Testing)', WPC_CLIENT_TEXT_DOMAIN) ?></option>
                                <option value="1" <?php echo isset( $wpc_gateways[ $this->plugin_name ]['is_ssl'] ) && 1 == $wpc_gateways[ $this->plugin_name ]['is_ssl'] ? 'selected' : '' ?>><?php _e('Force SSL (Live Site)', WPC_CLIENT_TEXT_DOMAIN) ?></option>

                            </select>
                        </td>
                        </tr>
                        <tr>
                        <th scope="row"><?php _e('Stripe API Credentials', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                            <span class="description"><?php _e('You must login to Stripe to <a target="_blank" href="https://manage.stripe.com/#account/apikeys">get your API credentials</a>. You can enter your test credentials, then live ones when ready.', WPC_CLIENT_TEXT_DOMAIN) ?></span>
                            <p><label><?php _e('Secret key', WPC_CLIENT_TEXT_DOMAIN) ?><br />
                            <input value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['private_key'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['private_key'] ) ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['private_key'] ) : ''; ?>" size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][private_key]" type="text" />
                            </label></p>
                            <p><label><?php _e('Publishable key', WPC_CLIENT_TEXT_DOMAIN) ?><br />
                            <input value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['publishable_key'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['publishable_key'] ) ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['publishable_key'] ) : ''; ?>" size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][publishable_key]" type="text" />
                            </label></p>
                        </td>
                        </tr>

                        <tr valign="top" style="height: 50px;">
                              <th scope="row" width="25%">
                                  <?php _e( "IPN URL (Webhooks)", WPC_CLIENT_TEXT_DOMAIN ) ?>
                              </th>
                              <td width="75%">
                                  <label>
                                      <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][set_ipn]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) && $wpc_gateways[ $this->plugin_name ]['set_ipn'], '1' ) ?> value="1" />
                                      <?php _e( 'I certify that I have properly set my IPN alert URL', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                  </label>
                                  <br />
                                  <br />
                                  <b><?php echo $ipn_url ?></b>
                                  <span style="float: left; font-size: 11px;" class="description"><?php _e( 'Use this URL in your Stripe "Webhooks" Account Settings.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
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
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Statement Descriptor", WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </th>
                            <td width="75%">
                                 <input size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][statement_descriptor]" type="text" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['statement_descriptor'] ) ? $wpc_gateways[ $this->plugin_name ]['statement_descriptor'] : '' ) ?>" />
                                 <span style="float: left; font-size: 11px;" class="description">
                                     <?php printf( __( 'Extra information about a charge. This will appear on your customer\'s credit card %sstatement%s.', WPC_CLIENT_TEXT_DOMAIN ),
                                         '<a href="https://stripe.com/docs/api#create_plan-statement_descriptor" target="_blank">', '</a>' ) ?></span>
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
}

//register payment gateway plugin
wpc_register_gateway_plugin( 'WPC_Gateway_Stripe', 'stripe', __('Stripe', WPC_CLIENT_TEXT_DOMAIN) );