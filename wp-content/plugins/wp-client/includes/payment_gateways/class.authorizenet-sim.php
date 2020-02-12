<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'WPC_Gateway_AuthorizeNet_SIM' ) ) {
    class WPC_Gateway_AuthorizeNet_SIM {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'authorizenet-sim';

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
    var $loginID, $transactionKey, $SandboxFlag, $API_Endpoint, $API_recurring, $version, $currencyCode, $locale, $test_mode, $md5_hash;

    /****** Below are the public methods you may overwrite via a plugin ******/

    /**
    * Runs when your class is instantiated.
    */
    function __construct() {
        global $wpc_payments_core;
        $wpc_gateways = WPC()->get_settings( 'gateways' );

        $this->step_names = $wpc_payments_core->step_names;

        //set names here to be able to translate
        $this->admin_name = __('Authorize.net SIM Checkout', WPC_CLIENT_TEXT_DOMAIN);
        $this->public_name = __('Authorize.net SIM Checkout', WPC_CLIENT_TEXT_DOMAIN);

        if ( isset( $wpc_gateways[ $this->plugin_name ]['public_name'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['public_name'] ) ) {
            $this->public_name = $wpc_gateways[ $this->plugin_name ]['public_name'];
        }

        $this->method_img_url = WPC()->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = WPC()->plugin_url . 'images/cc-button.png';

        if ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ) {
            $this->method_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
        }

        //set credit card vars
        if ( isset( $wpc_gateways[ $this->plugin_name ] ) ) {

          $this->loginID          = ( isset( $wpc_gateways[ $this->plugin_name ]['api_user'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['api_user'] ) : '';
          $this->transactionKey   = ( isset( $wpc_gateways[ $this->plugin_name ]['api_key'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['api_key'] ) : '';
          $this->currencyCode     = ( isset( $wpc_gateways[ $this->plugin_name ]['currency'] ) ) ? $wpc_gateways[ $this->plugin_name ]['currency'] : '';
          $this->md5_hash         = ( isset( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) ) ? trim( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) : '';
          $this->set_ipn          = ( isset( $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) &&  1 == $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) ? true : false;

          //set api urls
          if ( !isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) || $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox' )    {
            $this->API_Endpoint = "https://test.authorize.net/gateway/transact.dll";
            $this->test_mode = 'true';
          } else {
            $this->API_Endpoint = "https://secure.authorize.net/gateway/transact.dll";
            $this->test_mode = 'false';
          }
        }
    }


    function payment_process( &$order, $step = 3 ) {
        $content = '';

        if( 'recurring' == $order['payment_type'] ) {
            //make link
            $name = $this->step_names[2];
            if ( WPC()->permalinks ) {
                $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
            } else {
                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url()  );
            }

            $content .= '<br /><br />';
            $content .= __( 'You can not use this gateway for recurring payments.', WPC_CLIENT_TEXT_DOMAIN ) . ' ';
            $content .= '<a href="' . $redirect . '">' . __( 'Return', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

            return $content;
        }

        //one time order
        switch( $step ) {
            case 3: {
                $wpc_gateways = WPC()->get_settings( 'gateways' );
                $data       = json_decode( $order['data'], true );
                $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : __( 'Order', WPC_CLIENT_TEXT_DOMAIN );
                $sequence   = rand(1, 1000);
                $timeStamp    = time();

                if ( WPC()->permalinks ) {
                    $ipn_url = WPC()->make_url( '/wpc-ipn-handler-url/' . $this->plugin_name . '/', get_home_url() );
                } else {
                    $ipn_url = add_query_arg( array( 'wpc_page' => 'payment_ipn', 'wpc_page_value' => $this->plugin_name ), get_home_url()  );
                }

                $content .= '<span class="wpc_notice wpc_info" style="float:left;display:block;margin:0 0 20px 0;">' . __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'] . '</span>';

                //empty API
                if ( empty( $this->loginID ) || empty( $this->transactionKey ) ) {
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

                if( phpversion() >= '5.1.2' ) {
                    $fingerprint = hash_hmac("md5", $this->loginID . "^" . $sequence . "^" . $timeStamp . "^" . $order['amount'] . "^", $this->transactionKey);
                } else {
                    $fingerprint = bin2hex(mhash(MHASH_MD5, $this->loginID . "^" . $sequence . "^" . $timeStamp . "^" . $order['amount'] . "^", $this->transactionKey));
                }

                $content .= "<form method='post' id=\"wpc_payment_data_form\" action='{$this->API_Endpoint}' >
                    <input type='hidden' name='x_login' value='{$this->loginID}' />
                    <input type='hidden' name='x_amount' value='{$order['amount']}' />
                    <input type='hidden' name='x_duplicate_window' value='30' />
                    <input type='hidden' name='x_description' value='" . urlencode( substr( $item_name, 0, 31 ) ) . "' />
                    <input type='hidden' name='x_invoice_num' value='{$order['order_id']}' />
                    <input type='hidden' name='x_fp_sequence' value='$sequence' />
                    <input type='hidden' name='x_fp_timestamp' value='$timeStamp' />
                    <input type='hidden' name='x_fp_hash' value='$fingerprint' />
                    <input type='hidden' name='x_test_request' value='false' />
                    <input type='hidden' name='x_header_email_receipt' value='{$wpc_gateways[ $this->plugin_name ]['header_email_receipt']}' />
                    <input type='hidden' name='x_footer_email_receipt' value='{$wpc_gateways[ $this->plugin_name ]['footer_email_receipt']}' />
                    <input type='hidden' name='x_email_customer' value='" . strtoupper( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) . "' />
                    <input type='hidden' name='x_customer_ip' value='{$_SERVER['REMOTE_ADDR']}' />
                    <input type='hidden' name='x_relay_response' value='TRUE' />
                    <input type='hidden' name='x_relay_url' value='$ipn_url' />
                    <input type='hidden' name='x_show_form' value='PAYMENT_FORM' />
                </form>" .
                "<script type=\"text/javascript\">
                    jQuery('#wpc_payment_data_form').submit();
                </script>
                ";

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




    /*
    *
    */
    function _ipn( $order ) {
        if ( !empty( $_POST['x_invoice_num'] ) && !empty( $_POST['x_trans_id'] ) ) {

            global $wpc_payments_core;

            $order = $wpc_payments_core->get_order_by( $_POST['x_invoice_num'], 'order_id' );
            if ( !$order ) {
                die( 'Order Incorrect' );
            }

            $our_hash_api = md5( $this->md5_hash . $_POST['x_trans_id'] . $_POST['x_amount'] );
            $our_hash_sp  = md5( $this->md5_hash . $this->loginID . $_POST['x_trans_id'] . $_POST['x_amount'] );

            $error = 1;
            if ( strcmp( strtoupper( $our_hash_api ), $_POST['x_MD5_Hash'] ) === 0 ) {
                // Match
                $error = 0;
            } else if ( strcmp( strtoupper( $our_hash_sp ), $_POST['x_MD5_Hash'] ) === 0 ) {
                // Match
                $error = 0;
            }

            if ( $error ) {
                die('IPN verification failed!');
            }

            if ( 1 == $_POST['x_response_code'] ) {

                $payment_data = array();
                $payment_data['transaction_status'] = "Completed";
                $payment_data['subscription_id'] = null;
                $payment_data['subscription_status'] = null;
                $payment_data['parent_txn_id'] = null;
                $payment_data['transaction_type'] = 'paid';
                $payment_data['transaction_id'] = $_POST['x_trans_id'];


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
            }
        }
    }






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
          <h3 class='hndle'><span><?php _e('Authorize.net SIM Settings', WPC_CLIENT_TEXT_DOMAIN); ?></span></h3>
          <div class="inside">
            <span class="description"><?php _e('Authorize.net SIM is a customizable payment processing solution that gives the merchant control over all the steps in processing a transaction. An SSL certificate is required to use this gateway. USD is the only currency supported by this gateway.', WPC_CLIENT_TEXT_DOMAIN) ?></span>
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
                        <th scope="row"><?php _e('Gateway Credentials', WPC_CLIENT_TEXT_DOMAIN) ?></th>
                        <td>
                              <span class="description"><?php printf(__('You must login to Authorize.net merchant dashboard to obtain the API login ID and API transaction key. <a target="_blank" href="%s">Instructions &raquo;</a>', WPC_CLIENT_TEXT_DOMAIN), "http://www.authorize.net"); ?></span>
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

//register payment gateway plugin
wpc_register_gateway_plugin( 'WPC_Gateway_AuthorizeNet_SIM', 'authorizenet-sim', __('Authorize.net SIM Checkout', WPC_CLIENT_TEXT_DOMAIN) );