<?php
if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];

    WPC()->settings()->update( $settings, 'private_post_types' );

    $wpc_capabilities = WPC()->get_settings( 'capabilities' );

    if ( isset( $wpc_capabilities[ 'wpc_admin' ]['view_privat_post_type'] ) && $wpc_capabilities[ 'wpc_admin' ]['view_privat_post_type'] ) {
        $this->added_capability_for_post_type( 'wpc_admin', $settings ) ;
    }

    if ( isset( $wpc_capabilities[ 'wpc_manager' ]['view_privat_post_type'] ) && $wpc_capabilities[ 'wpc_manager' ]['view_privat_post_type'] ) {
        $this->added_capability_for_post_type( 'wpc_manager', $settings ) ;
    }

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}


$settings = WPC()->get_settings( 'private_post_types' );

$post_types         = get_post_types();
$exclude_types      = $this->get_excluded_post_types();

$post_types_html = '';
if ( is_array( $post_types ) && count( $post_types ) ) {
    foreach( $post_types as $key => $value ) {
        if ( in_array( $key, $exclude_types ) )
            continue;

        $checked = ( isset( $settings['types'][$key] ) && ( 1 == $settings['types'][$key] ) || 'yes' == $settings['types'][$key] ) ? 'checked="checked"' : '';


        $field_data = array(
            'type' => 'checkbox_list',
            'id' => 'private_post_types_' . $key,
            'name' => 'wpc_settings[types][' . $key . ']',
            'always_send' => false,
            'description' => $value,
            'checked' => $checked,
        );

        $post_types_html .= WPC()->settings()->render_setting_field( $field_data );
    }
}

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Private Post Types Settings', WPC_CLIENT_TEXT_DOMAIN ),
        'description' => sprintf( __( "The majority of %s related uses can be accomplished using Portal Pages, but in special cases where you might need a similar functionality on a special post type provided by another plugin, you may use the settings below", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ),
    ),
    array(
        'id' => 'action',
        'type' => 'selectbox',
        'label' => sprintf( __( 'Action for %s without access', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'value' => ( isset( $settings['action'] ) ) ? $settings['action'] : 'redirect',
        'description' => sprintf( __( 'Select action for %s without access when opening page with private content', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'options' => array(
            'redirect' => __( 'Redirect to Error page', WPC_CLIENT_TEXT_DOMAIN ),
            'exclude' => __( 'Exclude (not show) protected items', WPC_CLIENT_TEXT_DOMAIN ),
            'leave_on_search' => __( 'Exclude (not show) protected items, but show preview in site Search', WPC_CLIENT_TEXT_DOMAIN ),
        ),
    ),
    array(
        'id' => 'types',
        'type' => 'custom',
        'label' => __( 'Private Post Types', WPC_CLIENT_TEXT_DOMAIN ),
        'custom_html' => $post_types_html,
        'description' => __( 'Select post types to be used as private.', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );