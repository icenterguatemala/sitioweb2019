<?php
if ( isset( $_POST['wpc_settings'] ) ) {

    WPC()->settings()->update( $_POST['wpc_settings'], 'time_limited_clients' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$settings = WPC()->get_settings( 'time_limited_clients' );

$array_select = $this->get_array_periods();
reset($array_select);
$default_expiration_period = ( isset( $settings['def_period'] ) && array_key_exists( $settings['def_period'], $array_select ) ) ? $settings['def_period'] : key($array_select);

$after_field_html = '<select id="wpc_tlc_default_expiration_period" name="wpc_settings[def_period]">';
       foreach( $array_select as $key => $value ) {
           $after_field_html .= '<option value="' . $key . '"' . (( $key == $default_expiration_period ) ? 'selected="selected"' : '' ) . ' >'
                    . $value. '</option>';
        }
$after_field_html .= '</select>';


$section_fields = array(
    array(
        'type' => 'title',
        'label' => sprintf( __( 'Time Limited %s Settings', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'tlc_error_text',
        'type' => 'text',
        'label' => __( 'Error Login Text', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $settings['tlc_error_text'] ) ) ? $settings['tlc_error_text'] : __( 'Sorry, your access permission has expired', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'def_number',
        'type' => 'number',
        'size' => 'min',
        'label' => __( 'Default Expiration', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $settings['def_number'] ) ) ? $settings['def_number'] : '',
        'after_field' => $after_field_html,
    ),
);

WPC()->settings()->render_settings_section( $section_fields );