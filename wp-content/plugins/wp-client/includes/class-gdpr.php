<?php

/**
 * GDPR functionality for WP-Client plugin
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WPC_GDPR {

    /**
     * constructor
     */
    function __construct() {
        add_action( 'admin_init', array($this, 'add_privacy_content'), 20 );
        add_filter( 'wp_privacy_personal_data_erasers', array($this, 'add_privacy_eraser') );
        add_filter( 'wp_privacy_personal_data_exporters', array($this, 'add_privacy_exporter') );
    }

    /**
     * Add privacy policy text to the policy postbox.
     */
    public function add_privacy_content() {
        if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
            $content = file_get_contents( __DIR__ . "/data/privacy_content.php" );
            wp_add_privacy_policy_content( __( 'WP-Client', WPC_CLIENT_TEXT_DOMAIN ), $content );
        }
    }

    /**
     * Register eraser for WP-Client user data.
     * @param array $erasers
     * @return array
     */
    public function add_privacy_eraser( $erasers = array() ) {
        $erasers[] = array(
          'eraser_friendly_name' => __( 'WP-Client user data', WPC_CLIENT_TEXT_DOMAIN ),
          'callback'             => array($this, 'privacy_eraser'),
        );

        return $erasers;
    }

    /**
     * Register exporter for WP-Client user data.
     * @param array $exporters
     * @return array
     */
    public function add_privacy_exporter( $exporters ) {
        $exporters['WP-Client'] = array(
          'exporter_friendly_name' => __( 'WP-Client user data', WPC_CLIENT_TEXT_DOMAIN ),
          'callback'               => array($this, 'privacy_exporter'),
        );

        return $exporters;
    }

    /**
     * Eraser for WP-Client user data.
     * @param string $email_address
     * @return array
     */
    public function privacy_eraser( $email_address ) {

        if ( !empty( $email_address ) ) {
            $user = get_user_by( 'email', $email_address );
        }

        if ( empty( $user ) || !$user->exists() ) {
            return array(
              'items_removed'  => false,
              'items_retained' => false,
              'messages'       => array(),
              'done'           => true,
            );
        }

        $items_removed = false;
        $items_retained = false;
        $messages = array();

        $user_prop_to_erase = array(
          'first_name'       => __( 'First name', WPC_CLIENT_TEXT_DOMAIN ),
          'last_name'        => __( 'Last name', WPC_CLIENT_TEXT_DOMAIN ),
          'contact_name'     => __( 'Contact name', WPC_CLIENT_TEXT_DOMAIN ),
          'address'          => __( 'Address', WPC_CLIENT_TEXT_DOMAIN ),
          'contact_phone'    => __( 'Contact phone', WPC_CLIENT_TEXT_DOMAIN ),
          'verify_email_key' => __( 'Verify Email Key', WPC_CLIENT_TEXT_DOMAIN ),
          'parent_client_id' => __( 'Parent client', WPC_CLIENT_TEXT_DOMAIN ),
        );

        $user_meta = get_user_meta( $user->ID );
        $wpc_custom_fields = (array) WPC()->get_settings( 'custom_fields' );

        foreach ( $user_meta as $key => $value ) {
            if ( isset( $wpc_custom_fields[$key] ) && !empty( $wpc_custom_fields[$key]['gdpr_erase'] ) ) {

                $name = __( ucfirst( preg_replace( array('/^wpc_+(c\w_)?/i', '/_+/i'), array('', ' '), $key ) ), WPC_CLIENT_TEXT_DOMAIN );

                $user_prop_to_erase[$key] = $name;
            }
        }

        foreach ( $user_prop_to_erase as $key => $name ) {
            if ( isset( $user_meta[$key] ) ) {

                $deleted = delete_user_meta( $user->ID, $key );

                if ( $deleted ) {
                    $items_removed = true;
                }
                else {
                    $messages[] = sprintf( __( 'Your %s was unable to be removed at this time.' ), $name );
                    $items_retained = true;
                }
            }
        }

        return array(
          'items_removed'  => $items_removed,
          'items_retained' => $items_retained,
          'messages'       => $messages,
          'done'           => true,
        );
    }

    /**
     * Exporter for WP-Client user data.
     * @param string $email_address
     * @return array
     */
    public function privacy_exporter( $email_address ) {

        if ( !empty( $email_address ) ) {
            $user = get_user_by( 'email', $email_address );
        }

        if ( empty( $user ) || !$user->exists() ) {
            return array(
              'data' => array(),
              'done' => true,
            );
        }

        $user_prop_to_export = array(
          'first_name'       => __( 'First name', WPC_CLIENT_TEXT_DOMAIN ),
          'last_name'        => __( 'Last name', WPC_CLIENT_TEXT_DOMAIN ),
          'contact_name'     => __( 'Contact name', WPC_CLIENT_TEXT_DOMAIN ),
          'address'          => __( 'Address', WPC_CLIENT_TEXT_DOMAIN ),
          'contact_phone'    => __( 'Contact phone', WPC_CLIENT_TEXT_DOMAIN ),
          'verify_email_key' => __( 'Verify Email Key', WPC_CLIENT_TEXT_DOMAIN ),
          'parent_client_id' => __( 'Parent client', WPC_CLIENT_TEXT_DOMAIN ),
        );

        $user_meta = get_user_meta( $user->ID );
        $wpc_custom_fields = (array) WPC()->get_settings( 'custom_fields' );

        foreach ( $user_meta as $key => $value ) {
            if ( isset( $wpc_custom_fields[$key] ) && !empty( $wpc_custom_fields[$key]['gdpr_export'] ) ) {

                $name = __( ucfirst( preg_replace( array('/^wpc_+(c\w_)?/i', '/_+/i'), array('', ' '), $key ) ), WPC_CLIENT_TEXT_DOMAIN );

                $user_prop_to_export[$key] = $name;
            }
        }

        $user_data_to_export = array();

        foreach ( $user_prop_to_export as $key => $name ) {
            $value = '';
            if ( isset( $user->data->$key ) ) {
                $value = $user->data->$key;
            }
            elseif ( isset( $user_meta[$key] ) && is_array( $user_meta[$key] ) ) {
                $value_arr = unserialize( $user_meta[$key][0] );

                if ( !empty( $value_arr ) ) {
                    $value = json_encode( $value_arr, 128 | 256 );
                }
                else {
                    $value = $user_meta[$key][0];
                }
            }

            if ( !empty( $value ) ) {
                $user_data_to_export[] = array(
                  'name'  => $name,
                  'value' => $value,
                );
            }
        }

        $data_to_export[] = array(
          'group_id'    => 'WP-Client user',
          'group_label' => __( 'WP-Client', WPC_CLIENT_TEXT_DOMAIN ),
          'item_id'     => "WP-Client-user-{$user->ID}",
          'data'        => $user_data_to_export,
        );

        return array(
          'data' => $data_to_export,
          'done' => true,
        );
    }

}

new WPC_GDPR();
