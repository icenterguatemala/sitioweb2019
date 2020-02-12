<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

if ( isset( $_POST['update_settings'] ) ) {

    if ( isset( $_POST['wpc_settings'] ) ) {
        $settings = $_POST['wpc_settings'];

        $settings['client_wpc_circles']     = ( isset( $_POST['client_wpc_circles'] ) ) ? $_POST['client_wpc_circles'] : '' ;
        $settings['client_wpc_managers']    = ( isset( $_POST['client_wpc_managers'] ) ) ? $_POST['client_wpc_managers'] : '' ;

        $settings['staff_wpc_clients']      = ( isset( $_POST['staff_wpc_clients'] ) ) ? $_POST['staff_wpc_clients'] : '' ;

        $settings['manager_wpc_clients']    = ( isset( $_POST['manager_wpc_clients'] ) ) ? $_POST['manager_wpc_clients'] : '' ;
        $settings['manager_wpc_circles']    = ( isset( $_POST['manager_wpc_circles'] ) ) ? $_POST['manager_wpc_circles'] : '' ;
    } else {
        $settings = array();
    }

    WPC()->settings()->update( $settings, 'convert_users' );

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}


$wpc_convert_users = WPC()->get_settings( 'convert_users' );

$client_wpc_managers    = ( isset( $wpc_convert_users['client_wpc_managers'] ) ) ? $wpc_convert_users['client_wpc_managers'] : '';
$staff_wpc_clients      = ( isset( $wpc_convert_users['staff_wpc_clients'] ) ) ? $wpc_convert_users['staff_wpc_clients'] : '';
$manager_wpc_clients    = ( isset( $wpc_convert_users['manager_wpc_clients'] ) ) ? $wpc_convert_users['manager_wpc_clients'] : '';
$manager_wpc_circles    = ( isset( $wpc_convert_users['manager_wpc_circles'] ) ) ? $wpc_convert_users['manager_wpc_circles'] : '';


$section_fields = array(
    array(
        'type' => 'title',
        'label' => sprintf( __( 'Default Settings for Converting Users to WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'client_business_name_field',
        'type' => 'text',
        'label' => __( 'User Meta Fields Used For Business Name', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_convert_users['client_business_name_field'] ) ) ? $wpc_convert_users['client_business_name_field'] : '{first_name}',
        'description' => __( 'By default "first_name" is used. If "first_name" is empty, then "user_login" is used.', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'client_create_page',
        'type' => 'checkbox',
        'label' => WPC()->custom_titles['portal_page']['s'],
        'value' => ( isset( $wpc_convert_users['client_create_page'] ) ) ? $wpc_convert_users['client_create_page'] : 'no',
        'description' => sprintf( __( 'Create %s automatically', WPC_CLIENT_TEXT_DOMAIN ) , WPC()->custom_titles['portal_page']['s'] ),
    ),
    array(
        'id' => 'client_save_role',
        'type' => 'checkbox',
        'label' => __( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_convert_users['client_save_role'] ) ) ? $wpc_convert_users['client_save_role'] : 'no',
        'description' => sprintf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

    <table class="form-table wpc-settings-section" id="captcha_hiding_settings">
        <tr class="wpc-settings-line">
            <th><?php printf( __( 'Preselected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ) ?></th>
            <td>
                <?php

                if ( isset( $wpc_convert_users['client_wpc_circles'] ) ) {
                    $client_wpc_circles = $wpc_convert_users['client_wpc_circles'] ;
                } else {
                    $client_wpc_circles = array();
                    $groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_groups ORDER BY group_name ASC", ARRAY_A );
                    if ( is_array( $groups ) && 0 < count( $groups ) ) {
                        foreach ( $groups as $group ) {
                            if( '1' == $group['auto_select'] ) {
                                $client_wpc_circles[] = $group['group_id'];
                            }
                        }
                    }
                    $client_wpc_circles = implode( ',', $client_wpc_circles );
                }
                ?>

                <?php
                $link_array = array(
                    'title'   => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
                    'text'    => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
                    'data-input' => 'client_wpc_circles'
                );
                $input_array = array(
                    'name'  => 'client_wpc_circles',
                    'id'    => 'client_wpc_circles',
                    'value' => $client_wpc_circles
                );
                $additional_array = array(
                    'counter_value' => ( '' != $client_wpc_circles ) ? count( explode( ',', $client_wpc_circles ) ) : 0
                );
                WPC()->assigns()->assign_popup( 'circle', '', $link_array, $input_array, $additional_array );
                ?>
            </td>
        </tr>
        <tr class="wpc-settings-line">
            <th><?php printf( __( 'Preselected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ) ?></th>
            <td>
                <?php
                $link_array = array(
                    'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                    'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['p'],
                    'data-input' => 'client_wpc_managers'
                );
                $input_array = array(
                    'name'  => 'client_wpc_managers',
                    'id'    => 'client_wpc_managers',
                    'value' => $client_wpc_managers
                );
                $additional_array = array(
                    'counter_value' => ( '' != $client_wpc_managers ) ? count( explode( ',', $client_wpc_managers ) ) : 0
                );
                WPC()->assigns()->assign_popup( 'manager', '', $link_array, $input_array, $additional_array );
                ?>
            </td>
        </tr>
    </table>


<?php

$section_fields = array(
    array(
        'type' => 'title',
        'label' => sprintf( __( 'Default Settings for Converting Users to WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
    ),
    array(
        'id' => 'staff_save_role',
        'type' => 'checkbox',
        'label' => __( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_convert_users['staff_save_role'] ) ) ? $wpc_convert_users['staff_save_role'] : 'no',
        'description' => sprintf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

    <table class="form-table wpc-settings-section" id="captcha_hiding_settings">
        <tr class="wpc-settings-line">
            <th><?php printf( __( 'Preselected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></th>
            <td>
                <?php
                $link_array = array(
                    'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                    'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                    'data-input' => 'staff_wpc_clients',
                    'data-marks' => 'radio'
                );
                $input_array = array(
                    'name'  => 'staff_wpc_clients',
                    'id'    => 'staff_wpc_clients',
                    'value' => $staff_wpc_clients
                );
                $additional_array = array(
                    'counter_value' => ( $staff_wpc_clients ) ? get_userdata( $staff_wpc_clients )->user_login : ''
                );
                WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>
            </td>
        </tr>
    </table>


<?php

$section_fields = array(
    array(
        'type' => 'title',
        'label' => sprintf( __( 'Default Settings for Converting Users to WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
    ),
    array(
        'id' => 'manager_save_role',
        'type' => 'checkbox',
        'label' => __( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_convert_users['manager_save_role'] ) ) ? $wpc_convert_users['manager_save_role'] : 'no',
        'description' => sprintf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

    <table class="form-table wpc-settings-section" id="captcha_hiding_settings">
        <tr class="wpc-settings-line">
            <th><?php printf( __( 'Preselected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></th>
            <td>
                <?php
                $link_array = array(
                    'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                    'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                    'data-input' => 'manager_wpc_clients'
                );
                $input_array = array(
                    'name'  => 'manager_wpc_clients',
                    'id'    => 'manager_wpc_clients',
                    'value' => $manager_wpc_clients
                );
                $additional_array = array(
                    'counter_value' => ( '' != $manager_wpc_clients ) ? count( explode( ',', $manager_wpc_clients ) ) : 0
                );
                WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>
            </td>
        </tr>
        <tr class="wpc-settings-line">
            <th><?php printf( __( 'Preselected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ) ?></th>
            <td>
                <?php
                $link_array = array(
                    'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                    'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                    'data-input' => 'manager_wpc_circles'
                );
                $input_array = array(
                    'name'  => 'manager_wpc_circles',
                    'id'    => 'manager_wpc_circles',
                    'value' => $manager_wpc_circles
                );
                $additional_array = array(
                    'counter_value' => ( '' != $manager_wpc_circles ) ? count( explode( ',', $manager_wpc_circles ) ) : 0
                );
                WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>
            </td>
        </tr>
    </table>



<?php

$section_fields = array(
    array(
        'type' => 'title',
        'label' => sprintf( __( 'Default Settings for Converting Users to WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['p'] ),
    ),
    array(
        'id' => 'admin_save_role',
        'type' => 'checkbox',
        'label' => __( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_convert_users['admin_save_role'] ) ) ? $wpc_convert_users['admin_save_role'] : 'no',
        'description' => sprintf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );