<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb, $wp_roles;

$rul_process_submit = '';
$rul_process_submit2 = '';
$rul_process_submit3 = '';
$rul_process_submit4 = '';

/*
* Stolen from wp_safe_redirect, which validates the URL
*/
if ( !function_exists( 'wpc_client_rul_safe_redirect' ) ) {
    function wpc_client_rul_safe_redirect( $location ) {
        global $rul_local_only;

        if( 2 == $rul_local_only || 1 == $rul_local_only ) {
            return $location;
        }

        // Need to look at the URL the way it will end up in wp_redirect()
        $location = wp_sanitize_redirect($location);

        // browsers will assume 'http' is your protocol, and will obey a redirect to a URL starting with '//'
        if ( substr($location, 0, 2) == '//' ) {
            $location = 'http:' . $location;
        }

        // In php 5 parse_url may fail if the URL query part contains http://, bug #38143
        $test = ( $cut = strpos($location, '?') ) ? substr( $location, 0, $cut ) : $location;

        $lp  = parse_url($test);
        $wpp = parse_url(get_home_url());

        $allowed_hosts = (array) apply_filters('allowed_redirect_hosts', array($wpp['host']), isset($lp['host']) ? $lp['host'] : '');

        if ( isset($lp['host']) && ( !in_array($lp['host'], $allowed_hosts) && $lp['host'] != strtolower($wpp['host'])) ) {
                return false;
        } else {
            return $location;
        }
    }
}
$circles_names = array();
$all_circles = $wpdb->get_results( "SELECT group_id as id, group_name as name FROM {$wpdb->prefix}wpc_client_groups", ARRAY_A );
foreach( $all_circles as $circle ) {
    $circles_names[$circle['id']] = $circle['name'];
}

if ( isset( $_POST['update_settings'] ) ) {

    // update option for all users
    if ( isset( $_POST['update_all'] ) ) {
        $wpc_default_redirects = array();
        $wpc_default_redirects['first_login'] = ( isset( $_POST['wpc_default_first_login_redirect'] ) && !empty( $_POST['wpc_default_first_login_redirect'] ) ) ? trim( $_POST['wpc_default_first_login_redirect'] ) : '';
        $wpc_default_redirects['login'] = ( isset( $_POST['wpc_default_login_redirect'] ) && !empty( $_POST['wpc_default_login_redirect'] ) ) ? trim( $_POST['wpc_default_login_redirect'] ) : '';
        $wpc_default_redirects['logout'] = ( isset( $_POST['wpc_default_logout_redirect'] ) && !empty( $_POST['wpc_default_logout_redirect'] ) ) ? trim( $_POST['wpc_default_logout_redirect'] ) : '';

        WPC()->settings()->update( $wpc_default_redirects, 'default_redirects' );

        WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
    }

    // update option for some roles
    elseif ( isset( $_POST['update_roles'] ) ) {

        $roles      = $_POST['rul_role'];
        $first_addresses  = $_POST['rul_first_roleaddress'];
        $addresses  = $_POST['rul_roleaddress'];
        $logout     = $_POST['rul_logout_roleaddress'];
        $rul_order  = 0;

        $rul_whitespace = '        ';

        // Open the informational div
        $rul_process_submit2 = '<div id="message" class="updated wpc_notice fade">' . "\n";

        // Code for closing the informational div
        $rul_process_close = $rul_whitespace . '</div>' . "\n";

        // ----------------------------------
        // Process the rule changes
        // ----------------------------------

        if( $roles && ( $addresses || $first_addresses || $logout ) ) {
            $rul_submit_success = true;
            $rul_roles_updated = array();
            $rul_role_keys = array_keys($roles);
            $rul_role_loop = 0;


            $rul_existing_rolenames = array();
            foreach( array_keys( $wp_roles->role_names ) as $role ) {
                $rul_existing_rolenames[$role] = $role;
            }

            $wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type = 'role'" );

            // Loop through all submitted roles
            foreach( $roles as $role ) {
                $i = $rul_role_keys[$rul_role_loop];

                if ( isset( $rul_existing_rolenames[$role] ) ) {

                    // Check to see whether it matches the "local URL" test
                    $first_address = ( isset( $first_addresses[$i] ) ) ? wpc_client_rul_safe_redirect( $first_addresses[$i] ) : '';
                    $address = ( isset( $addresses[$i] ) ) ? wpc_client_rul_safe_redirect( $addresses[$i] ) : '';
                    $lgt = ( isset( $logout[$i] ) ) ? wpc_client_rul_safe_redirect( $logout[$i] ) : '';
                    $rul_ord = ( isset( $rul_order[$i] ) ) ? $rul_order[$i] : 0;

                    if (!$first_addresses && !$address && !$lgt) {
                        $rul_submit_success = false;
                        $rul_process_submit2 .= '<p><strong>****' .__('ERROR: Non-local or invalid URL submitted for role ',WPC_CLIENT_TEXT_DOMAIN) . $role . '****</strong></p>' . "\n";
                    } else {
                        // Update the existing entry or insert a new one
                        $sql = "INSERT INTO {$wpdb->prefix}wpc_client_login_redirects SET rul_first_url = '%s', rul_url = '%s', rul_type = 'role', rul_value = '%s', rul_url_logout='%s', rul_order='%s' ";
                        $rul_update_role = $wpdb->query( $wpdb->prepare( $sql, $first_address, $address, $role, $lgt, $rul_ord ) );

                        if (!$rul_update_role) {
                            $rul_submit_success = false;
                            $rul_process_submit2 .= '<p><strong>' .__('ERROR:',WPC_CLIENT_TEXT_DOMAIN) . $wpdb->last_error . '</strong></p>' . "\n";
                        }
                    }

                    // Make a note that this role name was updated
                    $rul_roles_updated[] = $role;
                } elseif ($role != -1) {
                    $rul_submit_success = false;
                    $rul_process_submit2 .= '<p><strong>****' .__('ERROR: Non-existent role submitted ',WPC_CLIENT_TEXT_DOMAIN) .'****</strong></p>' . "\n";
                }

                ++$rul_role_loop;
            }

            // Built the "not in" MySQL query
            $rul_roles_notin = "'" . implode( "','", $rul_roles_updated ) . "'";

            // Delete all role rules in the database that weren't updated (in other words, the user unchecked the box next to it)
            $wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type = 'role' AND rul_value NOT IN ( {$rul_roles_notin} )" );

            if ($rul_submit_success) {
                WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u#wpc_roles' );
            }
        }

        // Close the informational div
        $rul_process_submit2 .= $rul_process_close;

    }

    // update option for some circles
    elseif ( isset( $_POST['update_circles'] ) ) {
        $rul_submit_success = true;
        $new_circles = '';
        $circles = $login = $logout = $order = array();
        $rul_existing_circle_ids = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups" );

        $rul_whitespace = '        ';

        // Open the informational div
        $rul_process_submit3 = '<div id="message" class="updated wpc_notice fade">' . "\n";

        // Code for closing the informational div
        $rul_process_close = $rul_whitespace . '</div>' . "\n";

        // ----------------------------------
        // Process the rule changes
        // ----------------------------------
        if( isset( $_POST['rul_circle'] ) ) {
            $circles    = $_POST['rul_circle'];
            $first_addressin  = $_POST['rul_circle_first_address'];
            $addressin  = $_POST['rul_circle_address'];
            $addressout = $_POST['rul_logout_circle_address'];
            $rul_order  = $_POST['rul_order'];
        }

        if ( isset( $_POST['wpc_circles'] ) && '' != $_POST['wpc_circles'] ) {
            $new_circles = explode( ',', $_POST['wpc_circles'] );

            foreach ( $new_circles as $new_circle ) {
                $circles[]       = $new_circle;
                $first_addressin[]     = $_POST['wpc_circle_first_address'];
                $addressin[]     = $_POST['wpc_circle_address'];
                $addressout[]    = $_POST['wpc_logout_circle_address'];
                $rul_order[]     = (int) $_POST['wpc_order'];
            }

         }

        $rul_circles_updated = array();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type = 'circle'" );
        // Loop through all submitted circles
        foreach( $circles as $key => $circle ) {
            if ( in_array( $circle, $rul_existing_circle_ids ) ) {

                // Check to see whether it matches the "local URL" test
                $first_address = ( isset( $first_addressin[$key] ) ) ? wpc_client_rul_safe_redirect( $first_addressin[$key] ) : '';
                $address = ( isset( $addressin[$key] ) ) ? wpc_client_rul_safe_redirect( $addressin[$key] ) : '';
                $lgt = ( isset( $addressout[$key] ) ) ? wpc_client_rul_safe_redirect( $addressout[$key] ) : '';
                $rul_ord = ( isset( $rul_order[$key] ) ) ? $rul_order[$key] : 0;
                $circle_name = $circles_names[$circle];
                if (!$first_address && !$address && !$lgt) {
                    $rul_submit_success = false;
                    $rul_process_submit3 .= '<p><strong>****' .__('ERROR: Non-local or invalid URL submitted for circle ',WPC_CLIENT_TEXT_DOMAIN) . $circle_name . '****</strong></p>' . "\n";
                } else {
                    // Update the existing entry or insert a new one
                    $sql = "INSERT INTO {$wpdb->prefix}wpc_client_login_redirects SET rul_first_url = '%s', rul_url = '%s', rul_type = 'circle', rul_value = '%s', rul_url_logout='%s', rul_order='%s' ";
                    $rul_update_circle = $wpdb->query( $wpdb->prepare( $sql, $first_address, $address, $circle, $lgt, $rul_ord ) );

                    if (!$rul_update_circle) {
                        $rul_submit_success = false;
                        $rul_process_submit3 .= '<p><strong>****' .__('ERROR: Unknown error updating circle-specific URL for circle ',WPC_CLIENT_TEXT_DOMAIN) . $circle_name . '****</strong></p>' . "\n";
                    }
                }

                // Make a note that this circle name was updated
                $rul_circles_updated[] = $circle;
            } else {
                $rul_submit_success = false;
                $rul_process_submit3 .= '<p><strong>****' .__('ERROR: Non-existent circle submitted ',WPC_CLIENT_TEXT_DOMAIN) .'****</strong></p>' . "\n";
            }
        }

        // Built the "not in" MySQL query
        $rul_circles_notin = "'" . implode( "','", $rul_circles_updated ) . "'";

        // Delete all circle rules in the database that weren't updated (in other words, the user unchecked the box next to it)
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type = 'circle' AND rul_value NOT IN ( {$rul_circles_notin} )" );

        if ($rul_submit_success) {
            WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u#wpc_specific_circles' );
        }
        //}
        // Close the informational div
        $rul_process_submit3 .= $rul_process_close;

    }

    // update option for some users
    elseif ( isset( $_POST['update_users'] ) ) {

        // Open the informational div
        $rul_process_submit = '<div id="message" class="error wpc_notice fade">' . "\n";

        // Code for closing the informational div
        $rul_process_close = '        </div>' . "\n";

        $usernames = $_POST['rul_username'];
        $first_addresses = $_POST['rul_username_first_address'];
        $addresses = $_POST['rul_usernameaddress'];
        $logout    = $_POST['rul_logout_usernameaddress'];

        if ( $usernames && ( $addresses || $first_addresses ) ) {
            $rul_submit_success     = true;
            $rul_usernames_updated  = array();
            $rul_username_keys      = array_keys( $usernames );
            $rul_username_loop      = 0;

            $wpdb->query( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type = 'user'" );

            // Loop through all submitted usernames
            foreach( $usernames as $username ) {
                $i = $rul_username_keys[$rul_username_loop];

                if ( username_exists( $username ) ) {

                    // Check to see whether it matches the "local URL" test
                    $first_address = wpc_client_rul_safe_redirect( $first_addresses[$i] );
                    $address = wpc_client_rul_safe_redirect( $addresses[$i] );
                    $lgt = ( isset( $logout[$i] ) ) ? wpc_client_rul_safe_redirect( $logout[$i] ) : '';

                    if (!$address) {
                        $rul_submit_success = false;
                        $rul_process_submit .= '<p><strong>****' .__('ERROR: Non-local or invalid URL submitted for user ',WPC_CLIENT_TEXT_DOMAIN)  . $username .  '****</strong></p>' . "\n";
                    } else {
                        // Update the existing entry or insert a new one
                        $sql = "INSERT INTO {$wpdb->prefix}wpc_client_login_redirects SET rul_first_url = '%s', rul_url = '%s', rul_type = 'user', rul_value = '%s', rul_url_logout='%s'";

                        $rul_update_username = $wpdb->query( $wpdb->prepare( $sql, $first_address, $address, $username, $lgt ) );

                        if ( !$rul_update_username ) {
                            $rul_submit_success = false;
                            $rul_process_submit .= '<p><strong>****' .__('ERROR: Unknown error updating user-specific URL for user ',WPC_CLIENT_TEXT_DOMAIN) . $username . '****</strong></p>' . "\n";
                        }
                    }
                } elseif ($username != -1) {
                    $rul_submit_success = false;
                    $rul_process_submit .= '<p><strong>****' .__('ERROR: Non-existent username submitted ',WPC_CLIENT_TEXT_DOMAIN) .'****</strong></p>' . "\n";
                }

                ++$rul_username_loop;
            }

            if ( $rul_submit_success ) {
                WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u#wpc_specific_users' );
            }
        }

        // Close the informational div
        $rul_process_submit .= $rul_process_close;
    }

    // update option for non-logged-in
    elseif ( isset( $_POST['update_non_login'] ) ) {

        $wpc_default_non_login_redirects = array();
        $wpc_default_non_login_redirects['url'] = ( isset( $_POST['wpc_non_login_redirect'] ) && !empty( $_POST['wpc_non_login_redirect'] ) ) ? trim( $_POST['wpc_non_login_redirect'] ) : '';

        WPC()->settings()->update( $wpc_default_non_login_redirects, 'default_non_login_redirects' );
        WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u#wpc_non_login' );
    }


}

$rules_array = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_login_redirects ORDER BY rul_type, rul_order, rul_value", "ARRAY_A" );


$users_rul_existing = array();
$roles_rul_existing = array();
$circles_rul_existing = array();
$users_rules_html = '';
$circles_rules_html = '';
$roles_rules_html = '';


if ( is_array( $rules_array ) && count( $rules_array ) ) {

    $i_user = $i_role = $i_circle = 0;

    foreach( $rules_array as $rule ) {
        if ( 'user' == $rule['rul_type'] ) {

            $rul_first_url = ( isset( $_POST['rul_username_first_address'][$i_user] ) ) ? $_POST['rul_username_first_address'][$i_user] : $rule['rul_first_url'];
            $rul_url = ( isset( $_POST['rul_usernameaddress'][$i_user] ) ) ? $_POST['rul_usernameaddress'][$i_user] : $rule['rul_url'];
            $rul_url_logout = ( isset( $_POST['rul_logout_usernameaddress'][$i_user] ) ) ? $_POST['rul_logout_usernameaddress'][$i_user] : $rule['rul_url_logout'];

            $field_data = array(
                'type' => 'checkbox',
                'id' => 'rul_username_' . $i_user,
                'name' => 'rul_username[' . $i_user . ']',
                'default_value' => $rule['rul_value'],
                'always_send' => false,
                'checked' => 'checked',
            );

            $users_rules_html .= '<tr>';
            $users_rules_html .= '    <td><p>' .  WPC()->settings()->render_setting_field( $field_data ) . $rule['rul_value'] . '</p></td>';
            $users_rules_html .= '    <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_username_first_address[' . $i_user . ']" value="' . $rul_first_url . '" /> </p></td>';
            $users_rules_html .= '    <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_usernameaddress[' . $i_user . ']" value="' . $rul_url . '" /> </p></td>';
            $users_rules_html .= '    <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_logout_usernameaddress[' . $i_user . ']" value="' . $rul_url_logout . '" /> </p></td>';
            $users_rules_html .= '</tr>';

            $users_rul_existing[] = $rule['rul_value'];
            ++$i_user;

        }  elseif( 'role' == $rule['rul_type'] ) {

            $field_data = array(
                'type' => 'checkbox',
                'id' => 'rul_role_' . $i_role,
                'name' => 'rul_role[' . $i_role . ']',
                'default_value' => $rule['rul_value'],
                'always_send' => false,
                'checked' => 'checked',
            );

            $roles_rules_html .= '<tr>';
            $roles_rules_html .= '   <td><p>' .  WPC()->settings()->render_setting_field( $field_data ) . $rule['rul_value'] . '</p></td>';
            $roles_rules_html .= '   <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_first_roleaddress[' . $i_role . ']" value="' . $rule['rul_first_url'] . '" /></p></td>';
            $roles_rules_html .= '   <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_roleaddress[' . $i_role . ']" value="' . $rule['rul_url'] . '" /></p></td>';
            $roles_rules_html .= '   <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_logout_roleaddress[' . $i_role . ']" value="' . $rule['rul_url_logout'] . '" /></p></td>';
            $roles_rules_html .= '</tr>';

            $roles_rul_existing[] = $rule['rul_value'];

            ++$i_role;
        }  elseif( 'circle' == $rule['rul_type'] ) {

            if( isset( $circles_names[$rule['rul_value']] ) && !empty( $circles_names[$rule['rul_value']] ) ) {

                $field_data = array(
                    'type' => 'checkbox',
                    'id' => 'rul_circle_' . $i_circle,
                    'name' => 'rul_circle[' . $i_circle . ']',
                    'default_value' => $rule['rul_value'],
                    'always_send' => false,
                    'checked' => 'checked',
                );

                $circles_rules_html .= '<tr>';
                $circles_rules_html .= '   <td><p>' .  WPC()->settings()->render_setting_field( $field_data ) . $circles_names[$rule['rul_value']] . '</p></td>';
                $circles_rules_html .= '   <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_circle_first_address[' . $i_circle . ']" value="' . $rule['rul_first_url'] . '" /></p></td>';
                $circles_rules_html .= '   <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_circle_address[' . $i_circle . ']" value="' . $rule['rul_url'] . '" /></p></td>';
                $circles_rules_html .= '   <td><p><input type="text" style="width: 150px;" maxlength="500" name="rul_logout_circle_address[' . $i_circle . ']" value="' . $rule['rul_url_logout'] . '" /></p></td>';
                $circles_rules_html .= '   <td><p><input type="number" style="width: 50px;" name="rul_order[' . $i_circle . ']" value="' . $rule['rul_order'] . '" /></p></td>';
                $circles_rules_html .= '</tr>';

                $circles_rul_existing[] = $rule['rul_value'];

                ++$i_circle;
            }
        }
    }
} else {
    $i_user = $i_role = $i_circle = 1;
}

$wpc_enable_custom_redirects = WPC()->get_settings( 'enable_custom_redirects', 'no' );
$wpc_default_redirects = WPC()->get_settings( 'default_redirects' );
$wpc_default_non_login_redirects = WPC()->get_settings( 'default_non_login_redirects' );


$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Custom Redirects Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

</form>

<form action="<?php echo WPC()->settings()->get_current_setting_url() ?>" method="post" name="wpc_settings" id="wpc_settings" >
    <p>
        <span style="font-size: 14px; font-weight: bold;"><?php _e( 'Enable custom redirects', WPC_CLIENT_TEXT_DOMAIN ) ?>:</span>
        <select name="wpc_enable_custom_redirects" id="wpc_enable_custom_redirects" style="width: 70px;">
            <option value="no" <?php echo ( isset( $wpc_enable_custom_redirects ) && 'no' == $wpc_enable_custom_redirects ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
            <option value="yes" <?php echo ( isset( $wpc_enable_custom_redirects ) && 'yes' == $wpc_enable_custom_redirects ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
        </select>
        <span id="ajax_result" style="display: inline;"></span>
    </p>
    <br />
    <div class="wpc_clear"></div>
</form>


<div id="redirects_tabs" style="width:100%;float:left;<?php echo ( isset( $wpc_enable_custom_redirects ) && 'yes' == $wpc_enable_custom_redirects ) ? '' : 'display: none;' ?>">
    <?php $tabs = array();

    $tabs = array(
        array(
            'label'     => __( 'Login/Logout For All<br />(low priority)', WPC_CLIENT_TEXT_DOMAIN ),
            'href'      => "#wpc_all_user",
            'active'    => true
        ),
        array(
            'label'     => __( 'Login/Logout For Roles<br />(medium priority)', WPC_CLIENT_TEXT_DOMAIN ),
            'href'      => "#wpc_roles",
            'active'    => false
        ),
        array(
            'label'     => sprintf( __( 'Login/Logout For %s<br />(high priority)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
            'href'      => "#wpc_specific_circles",
            'active'    => false
        ),
        array(
            'label'     => __( 'Login/Logout For Users<br />(highest priority)', WPC_CLIENT_TEXT_DOMAIN ),
            'href'      => "#wpc_specific_users",
            'active'    => false
        ),
        array(
            'label'     => __( 'For Non-logged-in', WPC_CLIENT_TEXT_DOMAIN ),
            'href'      => "#wpc_non_login",
            'active'    => false
        )
    );

    echo WPC()->admin()->gen_vertical_tabs( $tabs, array('width'=>'23%') ); ?>

<!--    <ul style="float:left; width: 23%; margin: 0;">
        <li><a href="#wpc_all_user"><?php /*_e( 'Login/Logout For All', WPC_CLIENT_TEXT_DOMAIN ) */?><br /><?php /*_e( '(low priority)', WPC_CLIENT_TEXT_DOMAIN ) */?></a></li>
        <li><a href="#wpc_roles"><?php /*_e( 'Login/Logout For Roles', WPC_CLIENT_TEXT_DOMAIN ) */?><br /><?php /*_e( '(medium priority)', WPC_CLIENT_TEXT_DOMAIN ) */?></a></li>
        <li><a href="#wpc_specific_circles"><?php /*printf( __( 'Login/Logout For %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ) */?><br /><?php /*_e( '(high priority)', WPC_CLIENT_TEXT_DOMAIN ) */?></a></li>
        <li><a href="#wpc_specific_users"><?php /*_e( 'Login/Logout For Users', WPC_CLIENT_TEXT_DOMAIN ) */?><br /><?php /*_e( '(highest priority)', WPC_CLIENT_TEXT_DOMAIN ) */?></a></li>
        <li><a href="#wpc_non_login"><?php /*_e( 'For Non-logged-in', WPC_CLIENT_TEXT_DOMAIN ) */?></a></li>
    </ul>-->
    <div id="tab-container" style="width: 76%;">
        <div id="wpc_all_user" style="float:left;width:100%;margin: 0;padding:0;" class="tab-content">
            <form action="<?php echo WPC()->settings()->get_current_setting_url() ?>" method="post" name="wpc_settings1" id="wpc_settings1" >
                <input type="hidden" name="update_settings" value="1" />

                <div class="postbox">
                    <h3 class='hndle'><span><?php _e( 'Manage Default Login/Logout Redirect rules', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
                    <div class="inside">

                        <p>
                            <span class="description"><?php _e( "Low Priority - Will work for any Users who do not have specific Login/Logout Redirect rules.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </p>

                        <label for="wpc_default_first_login_redirect"><?php _e( 'First Time Login Redirect', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br />
                        <input type="text" id="wpc_default_first_login_redirect" name="wpc_default_first_login_redirect" size="83" maxlength="500" value="<?php echo ( isset( $wpc_default_redirects['first_login']  ) ) ? $wpc_default_redirects['first_login'] : '' ?>"/>
                        <span class="description"><?php _e( 'first time login redirect for all users.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        <br />
                        <br />

                        <label for="wpc_default_login_redirect"><?php _e( 'Login Redirect', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br />
                        <input type="text" id="wpc_default_login_redirect" name="wpc_default_login_redirect" size="83" maxlength="500" value="<?php echo ( isset( $wpc_default_redirects['login']  ) ) ? $wpc_default_redirects['login'] : '' ?>"/>
                        <span class="description"><?php _e( 'default login redirect for all users.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        <br />
                        <br />

                        <label for="wpc_default_logout_redirect"><?php _e( 'Logout Redirect', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br />
                        <input type="text" id="wpc_default_logout_redirect" name="wpc_default_logout_redirect" size="83" maxlength="500" value="<?php echo ( isset( $wpc_default_redirects['logout']  ) ) ? $wpc_default_redirects['logout'] : '' ?>"/>
                        <span class="description"><?php _e( 'default logout redirect for all users.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        <br />
                        <br />

                        <input type="submit" class='button-primary' name="update_all" value="<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>" /><br />
                        <br />
                    </div>
                </div>
            </form>
        </div>


        <div id="wpc_roles" style="float:left;width:100%;margin: 0;padding:0;" class="tab-content invisible">
            <form action="<?php echo WPC()->settings()->get_current_setting_url() . '#wpc_roles' ?>" method="post" name="wpc_settings3" id="wpc_settings3" >
                <input type="hidden" name="update_settings" value="1" />
                <div class="postbox">
                    <h3 class='hndle'><span><?php _e( 'Manage Login/Logout Redirect rules for any role', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
                    <div class="inside">

                        <?php echo $rul_process_submit2 ?>

                        <h4><?php _e( 'Specific roles', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>

                        <p>
                            <span class="description"><?php _e( "Medium Priority - Will work for Users who have these roles and do not have specific Login/Logout Redirect rules.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </p>

                        <table>
                            <tr>
                                <td><?php _e( 'Add:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <select name="rul_role[<?php echo $i_role ?>]" >
                                        <option value="-1"><?php _e( 'Select a role', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <?php

                                        $roles_name = array();
                                        foreach( array_keys( $wp_roles->role_names ) as $role ) {
                                            $roles_name[$role] = $role;
                                        }

                                        // Build the option HTML
                                        if ( $roles_name ) {
                                            foreach( $roles_name as $role_name ) {
                                                if ( !in_array( $role_name, $roles_rul_existing ) ) {
                                                    echo '<option value="' . $role_name . '">' . $role_name . '</option>';
                                                }
                                            }
                                        }

                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'First Time Login URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="rul_first_roleaddress[<?php echo $i_role ?>]" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'Login URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="rul_roleaddress[<?php echo $i_role ?>]" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'Logout URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="rul_logout_roleaddress[<?php echo $i_role ?>]" />
                                </td>
                            </tr>
                        </table>

                        <table class="widefat">
                            <tr>
                                <th><?php _e( 'Role', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'First Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Logout URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                            </tr>
                            <?php echo $roles_rules_html ?>
                        </table>
                        <br />
                        <br />
                        <input type="submit" class='button-primary' name="update_roles" value="<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <br />
                        <br />
                    </div>
                </div>
            </form>
        </div>


        <div id="wpc_specific_users" style="float:left;width:100%;margin: 0;padding:0;" class="tab-content invisible">
            <form action="<?php echo WPC()->settings()->get_current_setting_url() . '#wpc_specific_users' ?>" method="post" name="wpc_settings2" id="wpc_settings2" >
                <input type="hidden" name="update_settings" value="1" />

                <div class="postbox">
                    <h3 class='hndle'><span><?php _e( 'Manage Login/Logout Redirect rules for any user', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
                    <div class="inside">

                        <?php echo $rul_process_submit ?>

                        <h4><?php _e( 'Specific users', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>
                        <p>
                            <span class="description"><?php _e( "Highest Priority - Will always work - Does not matter if user has any other Login/Logout Redirect rules.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </p>

                        <table>
                            <tr>
                                <td><?php _e( 'Add:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <select name="rul_username[<?php echo $i_user ?>]" >
                                        <option value="-1"><?php _e( 'Select a username', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <?php
                                        $exclude_users = "'" . implode( "','", $users_rul_existing ) . "'";

                                        $users = $wpdb->get_results( "SELECT user_login FROM {$wpdb->users} WHERE user_login NOT IN ( {$exclude_users} ) ORDER BY user_login", "ARRAY_A" );

                                        // Built the option HTML
                                        if ( $users ) {
                                            foreach( $users as $user ) {
                                                echo '<option value="' . $user['user_login'] . '">' .  $user['user_login'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'First Login URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="rul_username_first_address[<?php echo $i_user ?>]" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'Login URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="rul_usernameaddress[<?php echo $i_user ?>]" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'Logout URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="rul_logout_usernameaddress[<?php echo $i_user ?>]" />
                                </td>
                            </tr>
                        </table>

                        <table class="widefat">
                            <tr>
                                <th><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'First Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Logout URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                            </tr>
                            <?php echo $users_rules_html ?>
                        </table>
                        <br />
                        <br />
                        <input type="submit" class='button-primary' name="update_users" value="<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <br />
                        <br />
                    </div>
                </div>
            </form>
        </div>


        <div id="wpc_specific_circles" style="float:left;width:100%;margin: 0;padding:0;" class="tab-content invisible">
            <form action="<?php echo WPC()->settings()->get_current_setting_url() . '#wpc_specific_circles' ?>" method="post" name="wpc_settings3" id="wpc_settings3" >
                <input type="hidden" name="update_settings" value="1" />

                <div class="postbox">
                    <h3 class='hndle'><span><?php printf( __( 'Manage Login/Logout Redirect rules for any %s', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles['circle']['s'] ) ?></span></h3>
                    <div class="inside">

                        <?php echo $rul_process_submit3 ?>

                        <h4><?php printf( __( 'Specific %s', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles['circle']['p'] ) ?></h4>
                        <p>
                            <span class="description"><?php printf( __( "High Priority - Will work for Users who have these %s and do not have specific Login/Logout Redirect rules.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] )  ?></span>
                        </p>

                        <table>
                            <tr id="is_choose1">
                                <td colspan="2">
                                    <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                            'text'    => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] )
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_circles',
                                            'id'    => 'wpc_circles',
                                            'value' => ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => 0
                                        );

                                        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                        WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>
                            <tr id="is_choose4">
                                <td><?php _e( 'First Login URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="wpc_circle_first_address" id="wpc_circle_first_address" />
                                </td>
                            </tr>
                            <tr id="is_choose2">
                                <td><?php _e( 'Login URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="wpc_circle_address" id="wpc_circle_address" />
                                </td>
                            </tr>
                            <tr id="is_choose3">
                                <td><?php _e( 'Logout URL:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="wpc_logout_circle_address"  id="wpc_logout_circle_address" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e( 'Order:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                <td>
                                    <input type="number" name="wpc_order" value="0" />
                                </td>
                            </tr>
                        </table>

                        <table class="widefat">
                            <tr>
                                <th><?php printf( __( '%s Name', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></th>
                                <th><?php _e( 'First Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Login URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Logout URL', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                                <th><?php _e( 'Order', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                            </tr>
                            <?php echo $circles_rules_html ?>
                        </table>
                        <br />
                        <br />
                        <input type="submit" class='button-primary' name="update_circles" id="update_circles" value="<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <br />
                        <br />
                    </div>
                </div>
            </form>
        </div>


        <div id="wpc_non_login" style="float:left;width:100%;margin: 0;padding:0;" class="tab-content invisible">
            <form action="<?php echo WPC()->settings()->get_current_setting_url() . '#wpc_non_login' ?>" method="post" name="wpc_settings4" id="wpc_settings4" >
                <input type="hidden" name="update_settings" value="1" />

                <div class="postbox">
                    <h3 class='hndle'><span><?php _e( 'Default for Non-logged-in Redirects', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
                    <div class="inside">

                        <?php echo $rul_process_submit4 ?>

                        <table>
                            <tr>
                                <td><label for="wpc_non_login_redirect"><?php _e( 'Redirect to:', WPC_CLIENT_TEXT_DOMAIN ) ?></label></td>
                                <td>
                                    <input type="text" size="83" maxlength="500" name="wpc_non_login_redirect" id="wpc_non_login_redirect" value="<?php echo ( isset( $wpc_default_non_login_redirects['url'] ) ) ? $wpc_default_non_login_redirects['url'] : '' ?>" />
                                </td>
                            </tr>
                        </table>
                        <br />
                        <br />
                        <input type="submit" class='button-primary' name="update_non_login" id="update_non_login" value="<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <br />
                        <br />
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

<div class="wpc_clear"></div>

<form style="display: none">

<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';
    jQuery(document).ready(function() {

        var admin_url = '<?php echo get_admin_url();?>';

        jQuery( '#wpc_enable_custom_redirects' ).change( function() {
            var enable = jQuery( this ).val();

            jQuery( "#redirects_tabs" ).slideToggle( 'slow' );

            jQuery( "#ajax_result" ).html( '' );
            jQuery( "#ajax_result" ).show();
            jQuery( "#ajax_result" ).css( 'display', 'inline' );
            jQuery( "#ajax_result" ).html( '<span class="wpc_ajax_loading"></span>' );

            jQuery.ajax({
                type: "POST",
                url: admin_url+"/admin-ajax.php",
                data: "action=wpc_save_enable_custom_redirects&wpc_enable_custom_redirects=" + enable,
                dataType: "json",
                success: function( data ){
                    if ( data.status ) {
                        jQuery( "#ajax_result" ).css( 'color', 'green' );
                    } else {
                        jQuery( "#ajax_result" ).css( 'color', 'red' );
                    }
                    jQuery( "#ajax_result" ).html( data.message );
                    setTimeout( function() {
                        jQuery( "#ajax_result" ).fadeOut( 1500 );
                    }, 2500 );
                },
                error: function( data ) {
                    jQuery( "#ajax_result" ).css( 'color', 'red' );
                    jQuery( "#ajax_result" ).html( 'Unknown error.' );
                    setTimeout( function() {
                        jQuery( "#ajax_result" ).fadeOut( 1500 );
                    }, 2500 );
                }
            });
        });

        //wpc_client_rul_submit_username( $_POST['rul_username'], $_POST['rul_usernameaddress'], $_POST['rul_logout_usernameaddress'] );

    });

</script>