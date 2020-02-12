<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

if ( ! empty( $wpc_custom_fields ) ) {
    foreach ( $wpc_custom_fields as $key => $val ) {
        if( !isset( $val['view'] ) ) continue;
        $view = $val['view'];
        $wpc_custom_fields[$key]['view'] = array(
            'admin_add_client' => isset( $view['admin_add'] ) ? $view['admin_add'] : 'hide',
            'admin_add_staff' => isset( $view['admin_add'] ) ? $view['admin_add'] : 'hide',
            'admin_edit_client' => isset( $view['admin_edit'] ) ? $view['admin_edit'] : 'hide',
            'admin_edit_staff' => isset( $view['admin_edit'] ) ? $view['admin_edit'] : 'hide',
            'user_add_client' => array(
                'client' => isset( $view['user_add']['client'] ) ? $view['user_add']['client'] : 'hide'
            ),
            'user_add_staff' => array(
                'client' => isset( $view['user_add']['client'] ) ? $view['user_add']['client'] : 'hide'
            ),
            'user_edit_client' => array(
                'client' => isset( $view['user_edit']['client'] ) ? $view['user_edit']['client'] : 'hide'
            ),
            'user_edit_staff' => array(
                'client' => isset( $view['user_edit']['client'] ) ? $view['user_edit']['client'] : 'hide'
            ),
            'user_profile_staff' => array(
                'staff' => isset( $view['user_edit']['staff'] ) ? $view['user_edit']['staff'] : 'hide'
            ),
        );
    }

    foreach ( $wpc_custom_fields as $k => $custom_field ) {
        if( isset( $custom_field['type'] ) && $custom_field['type'] == 'hidden' ) {
            $wpc_custom_fields[$k]['default_value'] = isset( $custom_field['options'][1] ) ?
                $custom_field['options'][1] : '';
        }
        if( !isset( $custom_field['type'] ) ||
            !in_array( $custom_field['type'], array( 'radio', 'checkbox', 'selectbox', 'multiselectbox' ) ) ||
            !isset( $custom_field['options'] ) ) continue;

        $zero_id = uniqid('zero');
        $options = ( isset( $custom_field['zero_value'] ) && $custom_field['zero_value'] == '1' ) ?
            array(
                (string)$zero_id => array( 'label' => '', 'value' => '' )
            ) : array();
        foreach( $custom_field['options'] as $key=>$option ) {
            $index = uniqid('old');
            $options[ $index ] = array(
                'value' => (string)$key,
                'label' => $option
            );
            if( isset( $custom_field['default_option'] ) ) {
                if( ( is_array( $custom_field['default_option'] ) && in_array( $key, $custom_field['default_option'] ) ) ||
                    ( is_string( $custom_field['default_option'] ) && $key == $custom_field['default_option'] ) ) {

                    $options[ $index ]['default'] = '1';
                }
            }
        }

        if( count( $options ) ) {
            $wpc_custom_fields[$k]['show_value_field'] = '1';
        }

        $wpc_custom_fields[$k]['options'] = $options;
    }
    WPC()->settings()->update( $wpc_custom_fields, 'custom_fields' );
}