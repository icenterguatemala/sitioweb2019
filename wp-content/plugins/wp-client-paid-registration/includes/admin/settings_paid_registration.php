<?php
global $wpc_gateway_plugins;

$error = '';

//update settings
if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];

    // validate at php side
    $settings['cost'] = str_replace( ',', '.', $settings['cost'] );
    if ( empty( $settings['cost'] ) ) {
        $error .= __( 'You should set Cost of registration.<br/>', WPC_CLIENT_TEXT_DOMAIN );
    } elseif ( !is_numeric( $settings['cost'] ) ) {
        $error .= __( 'Cost should be numeric .<br/>', WPC_CLIENT_TEXT_DOMAIN );
    } elseif ( 0 >= $settings['cost'] ) {
        $error .= __( 'Cost of registration should be more than 0<br/>', WPC_CLIENT_TEXT_DOMAIN );
    }

    if ( empty( $error ) ) {
        WPC()->settings()->update( $settings, 'paid_registration' );

        WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
    }

    $settings = $_POST['registration'];
}

$settings = WPC()->get_settings( 'paid_registration' );

$wpc_gateways = WPC()->get_settings( 'gateways' );

$wpc_gateway_html = '';
$count_values = 0;
foreach ( (array)$wpc_gateway_plugins as $code => $plugin ) {
    if ( isset( $wpc_gateways['allowed'] ) && in_array( $code, (array) $wpc_gateways['allowed'] ) ) {
        $checked = '';

        if ( isset( $settings['gateways'][ $code ] ) && ( '1' == $settings['gateways'][ $code ] || 'yes' == $settings['gateways'][ $code ] ) ) {
            $checked = 'checked';
            //for know that any gateway is active
            $count_values++;
        }

        $field_data = array(
            'type' => 'checkbox_list',
            'id' => 'gateways_' . $code,
            'name' => 'wpc_settings[gateways][' . $code . ']',
            'always_send' => false,
            'description' => esc_attr( $plugin[1] ),
            'checked' => $checked,
        );

        $wpc_gateway_html .= WPC()->settings()->render_setting_field( $field_data );
    }
}


if ( isset( $settings['enable'] ) && 'yes' == $settings['enable'] && !$count_values ) {
    $error .= __( 'Note: The registration will not work until you select "Payment Gateways". Clients will see a message that "Registration temporarily unavailable".', WPC_CLIENT_TEXT_DOMAIN );
}


$wpc_currency = WPC()->get_settings( 'currency' );

$wpc_currency_html = '<select name="wpc_settings[currency]" >';

if ( isset( $settings['currency'] ) ) {
    foreach( $wpc_currency as $cur_key => $curr ) {
        $wpc_currency_html .= '<option value="' . $cur_key . '" ' . ( ( $cur_key == $settings['currency'] ) ? 'selected' : '' ) . '>' . $curr['code'] . ' (' . $curr['symbol'] . ')' . ( ( '' != $curr['title'] ) ? ' - ' . $curr['title'] : ''  ) . '</option>';
    }
} else {
    foreach( $wpc_currency as $cur_key => $curr ) {
        $wpc_currency_html .= '<option value="' . $cur_key . '" ' . ( ( 1 == $curr['default'] ) ? 'selected' : '' ) . '>' . $curr['code'] . ' (' . $curr['symbol'] . ')' . ( ( '' != $curr['title'] ) ? ' - ' . $curr['title'] : ''  ) . '</option>';
    }
}

$wpc_currency_html .= '</select>';

if ( !empty( $error ) ) {
    echo '<div id="message" class="error wpc_notice fade inline"><p>' . $error . '</p></div>';
}

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Paid Registration Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'enable',
        'type' => 'checkbox',
        'label' => __( 'Use Paid Registration', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $settings['enable'] ) ) ? $settings['enable'] : 'no',
        'description' => __( 'Enable Paid Registration', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'custom_payments_gateway',
        'type' => 'custom',
        'custom_html' => $wpc_gateway_html,
        'label' => __( 'Payment Gateways', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => sprintf( __( 'To add or change payments gateway settings, please go to %s', WPC_CLIENT_TEXT_DOMAIN ), '<a href="admin.php?page=wpclients_settings&tab=payment_gateways" >' . __( 'Payment Gateways Settings', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ),
        'conditional' => array( 'enable', '=', 'yes' ),
    ),

    array(
        'id' => 'cost',
        'type' => 'text',
        'size' => 'small',
        'after_field' => $wpc_currency_html,
        'label' => __( 'Registration Cost', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $settings['cost'] ) ) ? $settings['cost'] : '',
        'description' => sprintf( __( 'Registration cost for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'conditional' => array( 'enable', '=', 'yes' ),
    ),
    array(
        'id' => 'description',
        'type' => 'textarea',
        'label' => __( 'Payment Description', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $settings['description'] ) ) ? $settings['description'] : '',
        'description' => __( 'Will be displayed on the payment page', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'enable', '=', 'yes' ),
    ),
    array(
        'id' => 'autoreturn',
        'type' => 'text',
        'label' => __( 'Custom Auto-Return URL', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $settings['autoreturn'] ) ) ? $settings['autoreturn'] : '',
        'description' => sprintf( __( 'When paying, %s will be redirected here. Example: %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], '<b>' . get_home_url() . '</b>' ),
        'conditional' => array( 'enable', '=', 'yes' ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('.wpc_gateway_checkbox').change(function() {
            jQuery(this).next('input').val( jQuery(this).is(':checked') ? 1 : 0 );
        });

        jQuery('.wpc_gateway_checkbox').each(function() {
            jQuery(this).prop('checked', jQuery(this).next('input').val() == '1' );
        });
    });
</script>