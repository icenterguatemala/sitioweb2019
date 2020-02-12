<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wp_roles;

if ( isset( $_POST['save_rules'] ) ) {

    if ( isset( $_POST['wpc_auto_convert_rules'] ) && is_array( $_POST['wpc_auto_convert_rules'] ) ) {
        WPC()->settings()->update( $_POST['wpc_auto_convert_rules'], 'auto_convert_rules' );
    } else {
        WPC()->settings()->update( array(), 'auto_convert_rules' );
    }

    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_auto_convert_rules = WPC()->get_settings( 'auto_convert_rules' );
$all_roles = $wp_roles->roles;


$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Auto-Convert Rules', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

</form>

<div class="wpc_tab_container_block wpc_auto_convert_rules">

    <form method="post" name="wpc_auto_convert_rules_form" id="wpc_auto_convert_rules_form" >

        <div class="rules_buttons">

            <select id="from_role" style="width: 130px;">
                <option value="">- <?php _e( 'From Role', WPC_CLIENT_TEXT_DOMAIN ) ?> -</option>
                <option value="__all_roles" <?php echo ( isset( $wpc_auto_convert_rules['__all_roles'] ) || count( $wpc_auto_convert_rules ) ) ? 'style="display: none;"' : '' ?> ><?php _e( 'All Roles', WPC_CLIENT_TEXT_DOMAIN ) ?></option>

                <?php
                foreach( $all_roles as $key => $role ) {
                    if( 'wpc_' == substr( $key, 0, 4 ) || 'administrator' == $key )
                        continue;

                    $hide = ( isset( $wpc_auto_convert_rules[$key] ) || isset( $wpc_auto_convert_rules['__all_roles'] ) ) ? 'style="display: none;"' : '';
                    ?>

                    <option value="<?php echo $key ?>" <?php echo $hide ?> ><?php echo $role['name'] ?></option>

                <?php } ?>
            </select>

            <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span>

            <select id="to_role" style="width: 130px" >
                <option value="">- <?php _e( 'To Role', WPC_CLIENT_TEXT_DOMAIN ) ?> -</option>

                <?php
                foreach( $all_roles as $key => $role ) {
                    if( 'wpc_' != substr( $key, 0, 4 ) )
                        continue;
                    ?>

                    <option value="<?php echo $key ?>"><?php echo $role['name'] ?></option>

                <?php } ?>
            </select>

            <input type="button" class="button" id="add_new_rule" value="<?php _e( 'Add Rule', WPC_CLIENT_TEXT_DOMAIN ) ?>" style="" />

        </div>

        <div id="rules-headers">

            <ul id="rules_list">
                <?php
                $first_tab = '';
                foreach( $wpc_auto_convert_rules as $key => $rule ) {
                    if ( empty( $first_tab ) )
                        $first_tab = $key;
                    ?>
                    <li class="show_rule <?php echo ( $key == $first_tab ? ' wpc_auto_convert_rule_active ' : '' )?>" rel="<?php echo $key ?>" >
                        <?php echo $rule['from_title'] ?> <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span> <?php echo $rule['to_title'] ?>
                    </li>
                    <?php
                }
                ?>
            </ul>

            <div class="rules_buttons2">
                <input type="submit" class="button-primary" id="save_rules" name="save_rules" value="<?php _e( 'Save Rules', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
            </div>

        </div>

        <div id="rules-container">
            <?php
                foreach( $wpc_auto_convert_rules as $key => $rule ) {
            ?>
                <table class="rule_table" id="rule_table_<?php echo $key ?>" style="<?php echo ( $key != $first_tab ? 'display: none;' : '' )?>">
                    <tr>
                        <td>
                            <div class="wpc_auto_convert_title"><?php echo $rule['from_title'] ?> <span class="dashicons dashicons-arrow-right-alt"></span> <?php echo $rule['to_title'] ?>
                                <div class="wpc_auto_convert_delete_rule">
                                    <a class="wpc_auto_convert_delete_rule_link" href="javascript: void(0);" rel="<?php echo $key ?>" >
                                        <?php _e( 'Delete Rule', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>


                    <?php
                    switch( $rule['to_role'] ) {
                        case 'wpc_client': {
                            ?>
                            <tr>
                                <td>
                                    <label for="wpc_<?php echo $key ?>_business_name_field"><?php _e( 'User Meta Fields Used For Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="text" name="wpc_auto_convert_rules[<?php echo $key ?>][business_name_field]" id="wpc_<?php echo $key ?>_business_name_field" value="<?php echo $rule['business_name_field'] ?>" />
                                    <br />
                                    <span class="description"><?php _e( 'By default "first_name" is used. If "first_name" is empty, then "user_login" is used.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="wpc_<?php echo $key ?>_create_client_page"><?php printf( __( 'Create %s Automatically', WPC_CLIENT_TEXT_DOMAIN ) , WPC()->custom_titles['portal_page']['s'] ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="wpc_auto_convert_rules[<?php echo $key ?>][create_page]" id="wpc_<?php echo $key ?>_create_page" style="width: 100px;">
                                        <option value="no" <?php echo ( !isset( $rule['create_page'] ) || 'no' == $rule['create_page'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $rule['create_page'] ) && 'yes' == $rule['create_page'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    <label for="wpc_<?php echo $key ?>_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="wpc_auto_convert_rules[<?php echo $key ?>][save_role]" id="wpc_<?php echo $key ?>_save_role" style="width: 100px;">
                                        <option value="no" <?php echo ( !isset( $rule['save_role'] ) || 'no' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $rule['save_role'] ) && 'yes' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                    <br>
                                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
                                        'text'    => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
                                        'data-input' => 'wpc_' . $key . '_wpc_circles'
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_auto_convert_rules[' . $key . '][wpc_circles]',
                                        'id'    => 'wpc_' . $key . '_wpc_circles',
                                        'value' => $rule['wpc_circles']
                                    );
                                    $additional_array = array(
                                        'counter_value' => ( '' != $rule['wpc_circles'] ) ? count( explode( ',', $rule['wpc_circles'] ) ) : 0
                                    );
                                    WPC()->assigns()->assign_popup( 'circle', '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                                        'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['p'],
                                        'data-input' => 'wpc_' . $key . '_wpc_managers'
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_auto_convert_rules[' . $key . '][wpc_managers]',
                                        'id'    => 'wpc_' . $key . '_wpc_managers',
                                        'value' => $rule['wpc_managers']
                                    );
                                    $additional_array = array(
                                        'counter_value' => ( '' != $rule['wpc_managers'] ) ? count( explode( ',', $rule['wpc_managers'] ) ) : 0
                                    );
                                    WPC()->assigns()->assign_popup( 'manager', '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>

                            <?php

                            break;
                        }

                        case 'wpc_client_staff': {
                            ?>
                            <tr>
                                <td valign="top">
                                    <label for="wpc_<?php echo $key ?>_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="wpc_auto_convert_rules[<?php echo $key ?>][save_role]" id="wpc_<?php echo $key ?>_save_role" style="width: 100px;">
                                        <option value="no" <?php echo ( !isset( $rule['save_role'] ) || 'no' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $rule['save_role'] ) && 'yes' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                    <br>
                                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    $user_login = '';
                                    if ( $rule['wpc_clients'] ) {
                                        $user = get_userdata( $rule['wpc_clients'] );

                                        if ( $user ) {
                                            $user_login = $user->user_login;
                                        }
                                    }


                                    $link_array = array(
                                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                                        'data-input' => 'wpc_' . $key . '_wpc_clients',
                                        'data-marks' => 'radio'
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_auto_convert_rules[' . $key . '][wpc_clients]',
                                        'id'    => 'wpc_' . $key . '_wpc_clients',
                                        'value' => $rule['wpc_clients']
                                    );

                                    $additional_array = array(
                                        'counter_value' => $user_login
                                    );
                                    WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>

                            <?php

                            break;
                        }

                        case 'wpc_manager': {
                            ?>

                            <tr>
                                <td valign="top">
                                    <label for="wpc_<?php echo $key ?>_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="wpc_auto_convert_rules[<?php echo $key ?>][save_role]" id="wpc_<?php echo $key ?>_save_role" style="width: 100px;">
                                        <option value="no" <?php echo ( !isset( $rule['save_role'] ) || 'no' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $rule['save_role'] ) && 'yes' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                    <br>
                                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                        'data-input' => 'wpc_' . $key . '_wpc_clients'
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_auto_convert_rules[' . $key . '][wpc_clients]',
                                        'id'    => 'wpc_' . $key . '_wpc_clients',
                                        'value' => $rule['wpc_clients']
                                    );
                                    $additional_array = array(
                                        'counter_value' => ( '' != $rule['wpc_clients'] ) ? count( explode( ',', $rule['wpc_clients'] ) ) : 0
                                    );
                                    WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                        'data-input' => 'wpc_' . $key . '_wpc_circles'
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_auto_convert_rules[' . $key . '][wpc_circles]',
                                        'id'    => 'wpc_' . $key . '_wpc_circles',
                                        'value' => $rule['wpc_circles']
                                    );
                                    $additional_array = array(
                                        'counter_value' => ( '' != $rule['wpc_circles'] ) ? count( explode( ',', $rule['wpc_circles'] ) ) : 0
                                    );
                                    WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>

                            <?php

                            break;
                        }

                        default: {
                            ?>

                            <tr>
                                <td valign="top">
                                    <label for="wpc_<?php echo $key ?>_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="wpc_auto_convert_rules[<?php echo $key ?>][save_role]" id="wpc_<?php echo $key ?>_save_role" style="width: 100px;">
                                        <option value="no" <?php echo ( !isset( $rule['save_role'] ) || 'no' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $rule['save_role'] ) && 'yes' == $rule['save_role'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                    <br>
                                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                                </td>
                            </tr>

                            <?php

                            break;
                        }


                    }

                    ?>
                        <tr>
                            <td>
                                <input type="hidden" name="wpc_auto_convert_rules[<?php echo $key ?>][from_title]" value="<?php echo $rule['from_title'] ?>"/>
                                <input type="hidden" name="wpc_auto_convert_rules[<?php echo $key ?>][to_title]" value="<?php echo $rule['to_title'] ?>"/>
                                <input type="hidden" name="wpc_auto_convert_rules[<?php echo $key ?>][to_role]" value="<?php echo $rule['to_role'] ?>"/>
                            </td>
                        </tr>
                    </table>


                    <?php

                }
            ?>

        </div>

    </form>

    <div id="temp_rule_wpc_client" style="display: none;">

        <table class="{rule_table}" id="rule_table_{rule_key}">
            <tr>
                <td>
                    <div class="wpc_auto_convert_title">{rule_title}
                        <div class="wpc_auto_convert_delete_rule">
                            <a class="wpc_auto_convert_delete_rule_link" href="javascript: void(0);" rel="{rule_key}" >
                                <?php _e( 'Delete Rule', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpc_{rule_key}_business_name_field"><?php _e( 'Which User Meta Field Use For Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="text" name="wpc_auto_convert_rules[{rule_key}][business_name_field]" id="wpc_{rule_key}_business_name_field" value="{first_name}" />
                    <br />
                    <span class="description"><?php _e( 'by default "first_name", or "user_login" if meta values and "first_name" is empty.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="wpc_{rule_key}_create_page"><?php printf( __( 'Create automatic %s', WPC_CLIENT_TEXT_DOMAIN ) , WPC()->custom_titles['portal_page']['s'] ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <select name="wpc_auto_convert_rules[{rule_key}][create_page]" id="wpc_{rule_key}_create_page" style="width: 100px;">
                        <option value="no" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="yes" ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <label for="wpc_{rule_key}_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <select name="wpc_auto_convert_rules[{rule_key}][save_role]" id="wpc_{rule_key}_save_role" style="width: 100px;">
                        <option value="no" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="yes" ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                    <br>
                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
                        'text'    => sprintf( __( 'Select %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ),
                        'data-input' => 'wpc_{rule_key}_wpc_circles'
                    );
                    $input_array = array(
                        'name'  => 'wpc_auto_convert_rules[{rule_key}][wpc_circles]',
                        'id'    => 'wpc_{rule_key}_wpc_circles',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0
                    );
                    WPC()->assigns()->assign_popup( 'circle', '', $link_array, $input_array, $additional_array );
                    ?>
                </td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td>
                    <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                        'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['p'],
                        'data-input' => 'wpc_{rule_key}_wpc_managers'
                    );
                    $input_array = array(
                        'name'  => 'wpc_auto_convert_rules[{rule_key}][wpc_managers]',
                        'id'    => 'wpc_{rule_key}_wpc_managers',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0
                    );
                    WPC()->assigns()->assign_popup( 'manager', '', $link_array, $input_array, $additional_array );
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    {rule_hidden}
                </td>
            </tr>
        </table>

    </div>


    <div id="temp_rule_wpc_client_staff" style="display: none;">

        <table class="{rule_table}" id="rule_table_{rule_key}">
            <tr>
                <td>
                    <div class="wpc_auto_convert_title">{rule_title}
                        <div class="wpc_auto_convert_delete_rule">
                            <a class="wpc_auto_convert_delete_rule_link" href="javascript: void(0);" rel="{rule_key}" >
                                <?php _e( 'Delete Rule', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <label for="wpc_{rule_key}_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <select name="wpc_auto_convert_rules[{rule_key}][save_role]" id="wpc_{rule_key}_save_role" style="width: 100px;">
                        <option value="no" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="yes" ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                    <br>
                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                        'data-input' => 'wpc_{rule_key}_wpc_clients',
                        'data-marks' => 'radio'
                    );
                    $input_array = array(
                        'name'  => 'wpc_auto_convert_rules[{rule_key}][wpc_clients]',
                        'id'    => 'wpc_{rule_key}_wpc_clients',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => ''
                    );
                    WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    {rule_hidden}
                </td>
            </tr>
        </table>

    </div>



    <div id="temp_rule_wpc_manager" style="display: none;">

        <table class="{rule_table}" id="rule_table_{rule_key}">
            <tr>
                <td>
                    <div class="wpc_auto_convert_title">{rule_title}
                        <div class="wpc_auto_convert_delete_rule">
                            <a class="wpc_auto_convert_delete_rule_link" href="javascript: void(0);" rel="{rule_key}" >
                                <?php _e( 'Delete Rule', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <label for="wpc_{rule_key}_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <select name="wpc_auto_convert_rules[{rule_key}][save_role]" id="wpc_{rule_key}_save_role" style="width: 100px;">
                        <option value="no" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="yes" ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                    <br>
                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                        'data-input' => 'wpc_{rule_key}_wpc_clients'
                    );
                    $input_array = array(
                        'name'  => 'wpc_auto_convert_rules[{rule_key}][wpc_clients]',
                        'id'    => 'wpc_{rule_key}_wpc_clients',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0
                    );
                    WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                    ?>
                </td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td>
                    <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                        'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                        'data-input' => 'wpc_{rule_key}_wpc_circles'
                    );
                    $input_array = array(
                        'name'  => 'wpc_auto_convert_rules[{rule_key}][wpc_circles]',
                        'id'    => 'wpc_{rule_key}_wpc_circles',
                        'value' => ''
                    );
                    $additional_array = array(
                        'counter_value' => 0
                    );
                    WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    {rule_hidden}
                </td>
            </tr>
        </table>

    </div>

    <div id="temp_rule__default" style="display: none;">

        <table class="{rule_table}" id="rule_table_{rule_key}">
            <tr>
                <td>
                    <div class="wpc_auto_convert_title">{rule_title}
                        <div class="wpc_auto_convert_delete_rule">
                            <a class="wpc_auto_convert_delete_rule_link" href="javascript: void(0);" rel="{rule_key}" >
                                <?php _e( 'Delete Rule', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <label for="wpc_{rule_key}_save_role"><?php _e( 'Save Current User Role', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <select name="wpc_auto_convert_rules[{rule_key}][save_role]" id="wpc_{rule_key}_save_role" style="width: 100px;">
                        <option value="no" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="yes" ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                    <br>
                    <span class="description"><?php printf( __( "If set to Yes, the user's current role will be saved, and %s role will be added.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ?></span>
                </td>
            <tr>
                <td>
                    {rule_hidden}
                </td>
            </tr>
        </table>

    </div>

</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        jQuery( '#wpc_auto_convert_rules_form' ).on( 'click', '.show_rule', function() {
            var rule_id = jQuery( this ).attr( 'rel' );

            jQuery( '.rule_table' ).hide();
            jQuery( '#rules_list li' ).removeClass( 'wpc_auto_convert_rule_active' );
            jQuery( this ).addClass( 'wpc_auto_convert_rule_active' );
            jQuery( '#rule_table_' + rule_id ).show();

        });


        jQuery( '#wpc_auto_convert_rules_form' ).on( 'click', '#add_new_rule', function() {

            if ( '' == jQuery( '#from_role').val() || '' == jQuery( '#to_role').val() ) {
                return false;
            }



            var key_from = jQuery( '#from_role').val();
            var key_to = jQuery( '#to_role').val();
            var from_title = jQuery( '#from_role option:selected' ).text();
            var to_title = jQuery( '#to_role option:selected' ).text();

            jQuery( '#from_role option[value=' + key_from + ']').hide();

            if ( '__all_roles' == key_from ) {
                jQuery( '#from_role option').hide();
                jQuery( '#from_role option[value=""]').show();
            } else {
                jQuery( '#from_role option[value="__all_roles"]').hide();
            }

            jQuery( '#from_role').val( '' );
            jQuery( '#to_role').val( '' );

            var new_rule_title = '<li class="show_rule wpc_auto_convert_rule_active" rel="' + key_from + '" ><span id="rule_title_label_' + key_from + '">' + from_title + ' <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span> ' + to_title + '</span></li>';
            var new_rule_table = jQuery( '#temp_rule_' + key_to ).html();

            if ( !new_rule_table ) {
                new_rule_table = jQuery( '#temp_rule__default' ).html();
            }

            var hidden_fields = '<input type="hidden" name="wpc_auto_convert_rules[' + key_from +'][from_title]" value="' + from_title +'"/>' +
                '<input type="hidden" name="wpc_auto_convert_rules[' + key_from +'][to_title]" value="' + to_title +'"/>' +
                '<input type="hidden" name="wpc_auto_convert_rules[' + key_from +'][to_role]" value="' + key_to +'"/>';


            new_rule_table = new_rule_table.replace( /{rule_key}/g, key_from );
            new_rule_table = new_rule_table.replace( /{rule_title}/g, from_title + ' <span class="dashicons dashicons-arrow-right-alt"></span> ' + to_title );
            new_rule_table = new_rule_table.replace( /{rule_table}/g, 'rule_table' );
            new_rule_table = new_rule_table.replace( /{rule_hidden}/g, hidden_fields );


            jQuery( '.rule_table' ).hide();
            jQuery( '#rules_list li' ).removeClass( 'wpc_auto_convert_rule_active' );
            jQuery( '#rules_list' ).append( new_rule_title );
            jQuery( '#rules-container' ).append( new_rule_table );




            //temp solution
            jQuery(".wpc_fancybox_link:not(.shutter_box_lightbox)").each(function() {
                var obj = jQuery(this);
                var href = obj.attr('href');
                var p_title = '';
                if( typeof wpc_popup_title[ href.substring(1) ][obj.data('type')] != 'undefined' ) {
                    p_title = wpc_popup_title[ href.substring(1) ][obj.data('type')];
                }

                obj.shutter_box({
                    view_type       : 'lightbox',
                    type            : 'inline',
                    width           : '800px',
                    href            : href,
                    title           : p_title,
                    self_init       : false,
                    onClose         : function() {
                        opened_link = false;
                    }
                });
            });

        });



        jQuery( '#wpc_auto_convert_rules_form' ).on( 'click', '.wpc_auto_convert_delete_rule_link', function() {

            if ( confirm( ' <?php echo esc_js( __( 'Are you sure to delete this rule?', WPC_CLIENT_TEXT_DOMAIN ) ) ?> ' ) ) {

                var rule_id = jQuery( this ).attr( 'rel' );

                var current_li = jQuery( '.wpc_auto_convert_rule_active' );
                var current_table = jQuery( '#rule_table_' + rule_id );

                var new_active_li = current_li.prev( 'li' );

                if ( new_active_li.length ) {
                    new_active_li.addClass( 'wpc_auto_convert_rule_active' );
                    //new_active_li.remove();
                } else {
                    new_active_li = current_li.next();
                    if ( new_active_li.length ) {
                        new_active_li.addClass( 'wpc_auto_convert_rule_active' );
                        //new_active_li.remove();
                    }
                }

                var new_active_table = current_table.prev();

                if ( new_active_table.length ) {
                    new_active_table.show();
                } else {
                    new_active_table = current_table.next();
                    if ( new_active_table.length ) {
                        new_active_table.show();
                    }
                }

                current_li.remove();
                current_table.remove();

                jQuery( '#from_role option[value=' + rule_id + ']').show();

                if ( '__all_roles' == rule_id ) {
                    jQuery( '#from_role option').show();
                } else {
                    if ( 0 == jQuery( '#rules_list li').length ) {
                        jQuery( '#from_role option[value="__all_roles"]').show();
                    }
                }

            }
            return false;

        });

    });
</script>


<form style="display: none">