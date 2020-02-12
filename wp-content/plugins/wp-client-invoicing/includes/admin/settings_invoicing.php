<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
    $this->redirect_available_page();
}

global $wpdb, $wpc_gateway_plugins, $wpc_payments_core;

$wpc_payments_core->load_gateway_plugins();

//save settings
if ( !empty( $_POST['update_settings'] ) && !empty( $_POST['settings'] ) ) {
    $wpc_invoicing = $this->prepare_invoicing_settings( $_POST['settings'] );
    WPC()->settings()->update( $wpc_invoicing, 'invoicing' );
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients_invoicing&tab=settings&msg=u' );
    exit;
}

$settings      = WPC()->get_settings( 'invoicing' );
$wpc_invoicing = $this->prepare_invoicing_settings( $settings );

$wpc_gateways = WPC()->get_settings( 'gateways' );

//Set date format
if ( get_option( 'date_format' ) ) {
    $date_format = get_option( 'date_format' );
} else {
    $date_format = 'm/d/Y';
}

if ( get_option( 'time_format' ) ) {
    $time_format = get_option( 'time_format' );
} else {
    $time_format = 'g:i:s A';
}

$next_number = $this->get_next_number( false );
$next_number_est = $this->get_next_number( false, 'est' );


?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_inv_settings" style="position: relative;">
            <p><?php _e( 'From here, you can manage settings for Estimate/Invoice.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>


            <?php if ( !empty( $_GET['msg'] ) ) { ?>
                <div id="message" class="updated">
                    <p>
                    <?php
                        switch( $_GET['msg'] ) {
                            case 'u':
                                _e( 'Settings Updated.', WPC_CLIENT_TEXT_DOMAIN );
                                break;
                        }
                    ?>
                    </p>
                </div>
            <?php } ?>

            <form action="" method="post" name="wpc_settings" id="wpc_settings" >
                <h3 class='hndle'></h3>
                <div class="inside">
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e( 'VAT:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="number" name="settings[vat]" min="0" max="100" step="0.1" value="<?php echo esc_attr( $wpc_invoicing['vat'] ) ?>" />
                                %
                                <br>
                                <br>
                                <label>
                                    <input type="checkbox" name="settings[vat_set]" value="yes" <?php checked( 'yes', $wpc_invoicing['vat_set'] ) ?> />
                                    <?php _e( 'Checked by default for new invoices.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e( 'Invoice Number Preview:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <span id="number_preview"></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="prefix"><?php _e( 'Invoice Prefix:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[prefix]" id="prefix" value="<?php echo esc_attr( $wpc_invoicing['prefix'] ) ?>" />
                                <br>
                                <span class="description"><?php _e( 'This prefix will be added to Invoice number', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="next_number"><?php _e( 'Invoice Next Number:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[next_number]" id="next_number" value="<?php echo ( isset( $next_number ) ) ? $next_number : '' ?>" />
                                <br>
                                <span class="description"><?php _e( 'The next INV created will be this value', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e( 'Display Zeros for Invoice:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="settings[display_zeros]" id="display_zeros" value="yes" <?php checked( 'yes', $wpc_invoicing['display_zeros'] ) ?> />
                                    <?php _e( 'Display the preceding zeros in the invoice number?', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </td>
                        </tr>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Invoice Number of Digits:', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'digits_count',
                                'value' => $wpc_invoicing['digits_count'],
                                'options' =>  range(3, 20),
                                'description' => __( 'How may digits would you like in your INV ID numbers?', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>

                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e( 'Estimate Number Preview:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <span id="number_preview_est"></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="prefix_est"><?php _e( 'Estimate Prefix:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[prefix_est]" id="prefix_est" value="<?php echo esc_attr( $wpc_invoicing['prefix_est'] ) ?>" />
                                <br>
                                <span class="description"><?php _e( 'This prefix will be added to Estimate number', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="next_number_est"><?php _e( 'Estimate Next Number:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[next_number_est]" id="next_number_est" value="<?php echo ( isset( $next_number_est ) ) ? $next_number_est : '' ?>" />
                                <br>
                                <span class="description"><?php _e( 'The next EST created will be this value', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e( 'Display Zeros for Estimate:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="settings[display_zeros_est]" id="display_zeros_est" value="yes" <?php checked( 'yes', $wpc_invoicing['display_zeros_est'] ) ?> />
                                    <?php _e( 'Display the preceding zeros in the estimate number?', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </td>
                        </tr>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Estimate Number of Digits:', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'digits_count_est',
                                'value' => $wpc_invoicing['digits_count_est'],
                                'options' =>  range(3, 20),
                                'description' => __( 'How may digits would you like in your EST ID numbers?', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Rate capacity:', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'rate_capacity',
                                'value' => $wpc_invoicing['rate_capacity'],
                                'options' =>  range(2, 5),
                                'description' => __( 'How may digits after point would you like in rate item?', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>

                        <tr valign="top">
                            <th scope="row">
                                <label for="thousands_separator"><?php _e( 'Thousands separator:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[thousands_separator]" id="thousands_separator" value="<?php echo esc_attr( $wpc_invoicing['thousands_separator'] ) ?>" />
                                <br>
                                <span class="description"><?php _e( 'What kind of thousands separator would you like in rate item?', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="inv_filename"><?php _e( 'File Name of Invoice for PDF:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[inv_filename]" id="inv_filename" value="<?php echo esc_attr( $wpc_invoicing['inv_filename'] ) ?>" />
                                <br>
                                <span class="description"><?php _e( 'You may use {number_inv}; {Y}, {m}, {d} - for date', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="est_filename"><?php _e( 'File Name of Estimate for PDF:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="settings[est_filename]" id="est_filename" value="<?php echo esc_attr( $wpc_invoicing['est_filename'] ) ?>" />
                                <br>
                                <span class="description"><?php _e( 'You may use {number_est}; {Y}, {m}, {d} - for date', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>
                        <?php echo $this->get_setting_selectbox( array(
                                'title' => sprintf( __( 'Auto-convert to Invoices for %s-approved Estimates', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                'key'   => 'est_auto_convert',
                                'value' => $wpc_invoicing['est_auto_convert'],
                            )); ?>
                        <tr valign="top">
                            <th scope="row">
                                <label for="rest_convert_to"><?php _e( 'Auto Convert Estimate Requests to:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <?php

                                $object = ( 'inv' == $wpc_invoicing['rest_convert_to'] ) ? 'inv' : 'est';
                                $values = array(
                                    'est' => __( 'Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                                    'inv' => __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                                );
                                foreach ( $values as $key => $val ){
                                    echo '<label><input type="radio" name="settings[rest_convert_to]" value="' . $key . '"' . ( ( $key == $object ) ? ' checked="checked"' : '' ) . '>' . $val . '</label>&nbsp;&nbsp;&nbsp;';

                                }
                                ?>
                                <br>
                            </td>
                        </tr>
                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Items are required field for Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'items_required',
                                'value' => $wpc_invoicing['items_required'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => sprintf( __( 'Attach PDF to Invoice/Estimate Email Notification to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                'key'   => 'attach_pdf',
                                'value' => $wpc_invoicing['attach_pdf'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => sprintf( __( 'Attach PDF to Email for Payment Reminder to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                'key'   => 'attach_pdf_reminder',
                                'value' => $wpc_invoicing['attach_pdf_reminder'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Send Estimates/Invoices to me for Review?', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'send_for_review',
                                'value' => $wpc_invoicing['send_for_review'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => sprintf( __( 'Send Email for %s After Paid Invoices?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                'key'   => 'send_for_paid',
                                'value' => $wpc_invoicing['send_for_paid'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                            'title' => sprintf( __( 'Attach PDF to Email for %s After Paid Invoices?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                            'key'   => 'send_pdf_for_paid',
                            'value' => $wpc_invoicing['send_pdf_for_paid'],
                        )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Notify when online payment is made', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'notify_payment_made',
                                'value' => $wpc_invoicing['notify_payment_made'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Send reminder emails for invoices?', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'reminder_days_enabled',
                                'value' => $wpc_invoicing['reminder_days_enabled'],
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Send first reminder email:', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'reminder_days',
                                'value' => $wpc_invoicing['reminder_days'],
                                'hide'  => 'yes' !== $wpc_invoicing['reminder_days_enabled'],
                                'class' => 'wpc_block_reminder_days',
                                'options' =>  range(1, 31),
                                'description' => __( 'days of due date', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Send Final Reminder?', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'reminder_one_day',
                                'value' => $wpc_invoicing['reminder_one_day'],
                                'hide'  => 'yes' !== $wpc_invoicing['reminder_days_enabled'],
                                'class' => 'wpc_block_reminder_days',
                                'options' =>  range(1, 31),
                                'description' => __( 'days of due date', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>

                        <?php
                            $reminder = 'yes' == $wpc_invoicing['reminder_days_enabled'];
                        ?>
                        <tr valign="top" class="wpc_block_reminder_days" <?php echo ( !$reminder ) ? 'style="display: none;"' : '' ?> >
                            <th scope="row">
                                <label for="reminder_one_day"><?php _e( 'Send Final Reminder?', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="settings[reminder_one_day]" id="reminder_one_day" value="yes" <?php checked( 'yes', $wpc_invoicing['reminder_one_day'] ) ?> />
                                    <?php _e( 'Send final reminder one day before due date.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </td>
                        </tr>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Send Email Reminder every', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'reminder_after',
                                'value' => $wpc_invoicing['reminder_after'],
                                'hide'  => 'yes' !== $wpc_invoicing['reminder_days_enabled'],
                                'class' => 'wpc_block_reminder_days',
                                'options' =>  range(1, 31),
                                'description' => __( 'day(s) after due date', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>

                        <?php echo $this->get_setting_selectbox( array(
                                'title' => __( 'Lock Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                                'key'   => 'lock_invoice',
                                'value' => $wpc_invoicing['lock_invoice'],
                                'description' => __( 'Lock Invoice for some time after payment attempt.', WPC_CLIENT_TEXT_DOMAIN ),
                            )); ?>
                        <?php
                            $lock_invoice = 'yes' == $wpc_invoicing['lock_invoice'];
                        ?>
                        <tr valign="top" id="wpc_tr_time_lock"<?php echo ($lock_invoice) ? '' : ' style="display: none;"';?>>
                            <th scope="row">
                                <label for="wpc_time_lock"><?php _e( 'Time of Lock Invoice', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <input type="number" name="settings[time_lock]" id="wpc_time_lock" style="width: 100px;"
                                       value="<?php esc_attr( $wpc_invoicing['time_lock'] ) ?>" />
                                <?php _e( 'minutes', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e( 'Payment Gateways', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </th>
                            <td>
                                <?php
                                foreach ( (array)$wpc_gateway_plugins as $code => $plugin ) {
                                    if ( isset( $wpc_gateways['allowed'] ) && in_array( $code, (array) $wpc_gateways['allowed'] ) ) {
                                        $value = 1;
                                        if ( isset( $wpc_invoicing['gateways'][ $code ] ) && 0 == $wpc_invoicing['gateways'][ $code ] ) {
                                            $value = 0;
                                        }
                                        echo '<label>
                                            <input type="checkbox" class="wpc_gateway_checkbox" value="' . $code .'" /> ' . esc_attr( $plugin[1] ) . '
                                            <input type="hidden" name="settings[gateways][' . $code . ']" value="' . $value . '" />
                                        </label><br />';
                                    }
                                }
                                ?>
                                <span class="description"><?php echo sprintf( __( 'To add or change payments gateway settings, please look in "%s"', WPC_CLIENT_TEXT_DOMAIN ), '<a href="admin.php?page=wpclients_settings&tab=gateways" >' . __( 'Payment Settings', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ) ?></span>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="cost"><?php _e( 'Payment Description', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </th>
                            <td>
                                <textarea style="width: 400px;" rows="2" name="settings[description]" id="description" ><?php echo esc_html( $wpc_invoicing['description'] ) ?></textarea>
                                <br />
                                <span class="description"><?php _e( 'Will be displayed on the payment page.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="ter_con"><?php _e( 'Terms & Conditions', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <div style="width: 400px;">
                                    <?php
                                    wp_editor( $wpc_invoicing['ter_con'],
                                        'ter_con',
                                        array(
                                            'textarea_name' => 'settings[ter_con]',
                                            'textarea_rows' => 7,
                                            'wpautop'       => false,
                                            'media_buttons' => false
                                        )
                                    );
                                    ?>
                                </div>
                                <span class="description"><?php _e( '  >> This template for use in the Estimates/Invoices - will be pre-loaded with this content', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="not_cus"><?php _e( 'Note to Customer', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            </th>
                            <td>
                                <div style="width: 400px;">
                                <?php
                                wp_editor( $wpc_invoicing['not_cus'],
                                    'not_cus',
                                    array(
                                        'textarea_name' => 'settings[not_cus]',
                                        'textarea_rows' => 7,
                                        'wpautop'       => false,
                                        'media_buttons' => false
                                    )
                                );
                                ?>
                                </div>
                                <span class="description"><?php _e( '  >> This template for use in the Estimates/Invoices - will be pre-loaded with this content', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            </td>
                        </tr>


                    </table>
                </div>

                <input type='submit' name='update_settings' class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />

            </form>
        </div>
    </div>
</div>


<script type="text/javascript" language="javascript">

    jQuery(document).ready(function(){

        jQuery('#lock_invoice').change(function() {
            jQuery( '#wpc_tr_time_lock' ).css( 'display', ( 'yes' === jQuery( this ).val() ) ? 'table-row' : 'none' );
        });

        jQuery('.wpc_gateway_checkbox').change(function() {
            jQuery(this).next('input').val( jQuery(this).is(':checked') ? 1 : 0 );
        });

        jQuery('.wpc_gateway_checkbox').each(function() {
            jQuery(this).prop('checked', jQuery(this).next('input').val() === '1' );
        });

        //
        jQuery( '#reminder_days_enabled' ).change( function() {
            if ( 'yes' === jQuery( this ).val() ) {
                jQuery( '.wpc_block_reminder_days' ).css( 'display', 'table-row' );
            } else {
                jQuery( '.wpc_block_reminder_days' ).css( 'display', 'none' );
            }
        });

        //change currency symbol
        jQuery( '#currency_symbol' ).change( function() {
            jQuery( this ).display_symbol();
        });

        //change display currency symbol
        jQuery( '#currency_symbol_align' ).change( function() {
            jQuery( this ).display_symbol();
        });


        //display currency symbol
        jQuery.fn.display_symbol = function () {
            var symbol = jQuery( '#currency_symbol' ).val();
            var align = jQuery( '#currency_symbol_align' ).val();

             jQuery( '#symbol_left' ).html( '' );
             jQuery( '#symbol_right' ).html( '' );

            if ( 'right' !== align ) {
                align = 'left';
            }

            jQuery( '#symbol_' + align ).html( symbol );

        };

        jQuery( this ).display_symbol();



        //change number preview for invoice
        jQuery( '#prefix, #next_number ' ).keyup( function() {
            jQuery( this ).gen_number_preview();
        });

        //change number preview for invoice
        jQuery( '#display_zeros, #digits_count' ).change( function() {
            jQuery( this ).gen_number_preview();
        });


        //gen number preview for invoice
        jQuery.fn.gen_number_preview = function () {
            var prefix = jQuery( '#prefix' ).val();
            var next_number = jQuery( '#next_number' ).val();
            var display_zeros = jQuery( '#display_zeros' ).attr( 'checked');
            var digits_count = jQuery( '#digits_count' ).val();

            if ( 'checked' === display_zeros ) {
                next_number = jQuery( this ).str_pad( next_number, digits_count, '0', 'STR_PAD_LEFT' );
            }

            jQuery( '#number_preview' ).html( prefix + next_number );

        };

        //change number preview for estimate
        jQuery( '#prefix_est, #next_number_est' ).keyup( function() {
            jQuery( this ).gen_number_preview_est();
        });

        //change number preview for estimate
        jQuery( '#display_zeros_est, #digits_count_est' ).change( function() {
            jQuery( this ).gen_number_preview_est();
        });


        //gen number preview for estimate
        jQuery.fn.gen_number_preview_est = function () {
            var prefix = jQuery( '#prefix_est' ).val();
            var next_number = jQuery( '#next_number_est' ).val();
            var display_zeros = jQuery( '#display_zeros_est' ).attr( 'checked');
            var digits_count = jQuery( '#digits_count_est' ).val();

            if ( 'checked' === display_zeros ) {
                next_number = jQuery( this ).str_pad( next_number, digits_count, '0', 'STR_PAD_LEFT' );
            }

            jQuery( '#number_preview_est' ).html( prefix + next_number );

        };


        //for add zero in number
        jQuery.fn.str_pad = function ( input, pad_length, pad_string, pad_type ) {

            var half = '', pad_to_go;

            var str_pad_repeater = function(s, len){
                    var collect = '', i;

                    while(collect.length < len) collect += s;
                    collect = collect.substr(0,len);

                    return collect;
                };

            if (pad_type !== 'STR_PAD_LEFT' && pad_type !== 'STR_PAD_RIGHT' && pad_type !== 'STR_PAD_BOTH') { pad_type = 'STR_PAD_RIGHT'; }
            if ((pad_to_go = pad_length - input.length) > 0) {
                if (pad_type === 'STR_PAD_LEFT') { input = str_pad_repeater(pad_string, pad_to_go) + input; }
                else if (pad_type === 'STR_PAD_RIGHT') { input = input + str_pad_repeater(pad_string, pad_to_go); }
                else if (pad_type === 'STR_PAD_BOTH') {
                    half = str_pad_repeater(pad_string, Math.ceil(pad_to_go/2));
                    input = half + input + half;
                    input = input.substr(0, pad_length);
                }
            }

            return input;
        };


        jQuery( this ).gen_number_preview();
        jQuery( this ).gen_number_preview_est();

    });

</script>