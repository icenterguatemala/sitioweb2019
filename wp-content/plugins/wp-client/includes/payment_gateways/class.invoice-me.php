<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'WPC_Gateway_Invoice_Me' ) ) {
    class WPC_Gateway_Invoice_Me {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'invoice-me';

    var $valid_currencies = array(
        '_any',//ANY
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

        $this->recurring = false;

        //set names here to be able to translate
        $this->admin_name = __('Invoice Me', WPC_CLIENT_TEXT_DOMAIN);
        $this->public_name = __('Invoice Me', WPC_CLIENT_TEXT_DOMAIN);

        if ( isset( $wpc_gateways[ $this->plugin_name ]['public_name'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['public_name'] ) ) {
            $this->public_name = $wpc_gateways[ $this->plugin_name ]['public_name'];
        }

        $this->method_img_url = WPC()->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = WPC()->plugin_url . 'images/cc-button.png';

        if ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ) {
            $this->method_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
        }

    }


    function payment_process( &$order, $step = 3 ) {
        global $wpc_payments_core;

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

                add_action( 'wpc_payment_getaway_before_field_' . $this->plugin_name, array( &$this, 'payment_getaway_before_field' ) );

                ob_start();

                ?>


                <form id="wpc_payment_form_<?php echo $this->plugin_name ?>" method="post" class="wpc_form wpc_payment_form" action="<?php echo $redirect ?>">

                    <div class="wpc_form_line">
                        <span class="description"><?php _e( 'Click on button below to request Invoice for manual payment. We will send it soon as possible.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                    </div>

                    <div class="wpc_form_line">
                        <div class="wpc_form_label">
                            &nbsp;
                        </div>
                        <div class="wpc_form_field">
                            <input type="submit" class="wpc_submit" name="wpc_payment_submit" id="wpc_payment_confirm" value="<?php _e( 'Send Invoice To Me', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                        </div>
                    </div>
                </form>


                <?php $content .= ob_get_contents();
                    if( ob_get_length() ) {
                        ob_end_clean();
                    }


                break;
            }

            case 4: {
                $wpc_gateways = WPC()->get_settings( 'gateways' );

                $data       = json_decode( $order['data'], true );
                $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

                if ( !empty( $_POST['wpc_payment_submit'] ) ) {

                    $payment_data = array();
                    $payment_data['transaction_status'] = "Pending";
                    $payment_data['subscription_id'] = null;
                    $payment_data['subscription_status'] = null;
                    $payment_data['parent_txn_id'] = null;
                    $payment_data['transaction_type'] = 'pending';
                    $payment_data['transaction_id'] = md5( time() );


                    $wpc_payments_core->order_update( $order['id'], $payment_data );

                    if ( !empty( $wpc_gateways[ $this->plugin_name ]['email'] ) && is_email( $wpc_gateways[ $this->plugin_name ]['email'] ) ) {
                        $title = 'Request of Invoice Me.';
                        $body = '<p>The user {user_name} requested "Invoice Me" on {site_title}</p>';

                        $args = array( 'client_id' => $order['client_id']  );

                        WPC()->wpc_mail( $wpc_gateways[ $this->plugin_name ]['email'], WPC()->replace_placeholders( $title, $args ), WPC()->replace_placeholders( $body, $args ) );
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

                } else {
                    //make link
                    $name = $this->step_names[3];
                    if ( WPC()->permalinks ) {
                        $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                    } else {
                        $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url() );
                    }

                    $content .= sprintf(__( 'There was a problem finalizing your purchase: "%s" ', WPC_CLIENT_TEXT_DOMAIN ), 'Some Errors' );
                    $content .= '<br>';
                    $content .= sprintf(__( '<a href="%s">Please try again</a>', WPC_CLIENT_TEXT_DOMAIN ), $redirect );
                }
                break;
            }

            case 5: {
                global $wpc_payments_core;
                $content .= __( 'Thank you. We will send Invoice soon.', WPC_CLIENT_TEXT_DOMAIN );
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

      /**
       * Echo a settings meta box with whatever settings you need for you gateway.
       *  Form field names should be prefixed with wpc_gateway[plugin_name], like "wpc_gateway[plugin_name][mysetting]".
       *  You can access saved settings via $wpc_gateways array.
       */
      function create_settings_form( $wpc_gateways ) {
        ?>
        <div id="wpc_<?php echo $this->plugin_name ?>" class="postbox">
          <h3 class='hndle'><span><?php _e('Invoice Me Settings', WPC_CLIENT_TEXT_DOMAIN); ?></span></h3>
          <div class="inside">
            <span class="description"><?php _e('Invoice Me allows your clients to choose to defer payment when registering, and you will be notified via email when this payment option is selected, so you can generate the necessary invoice to send to the client.', WPC_CLIENT_TEXT_DOMAIN) ?></span>
            <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Email For Notifications', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                            <input value="<?php echo esc_attr( isset( $wpc_gateways[ $this->plugin_name ]['email'] ) ? $wpc_gateways[ $this->plugin_name ]['email'] : '' ); ?>" size="30" name="wpc_gateway[<?php echo $this->plugin_name ?>][email]" type="text" />
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

//register payment gateway plugin
wpc_register_gateway_plugin( 'WPC_Gateway_Invoice_Me', 'invoice-me', __('Invoice Me', WPC_CLIENT_TEXT_DOMAIN) );