<?php
/**
 * Template Name: Staff Add/Edit Form
 * Template Description: This template for [wpc_client_add_staff_form] and [wpc_client_edit_staff_form] shortcodes
 * Template Tags: Users, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/staff_edit.php.
 *
 * HOWEVER, on occasion WP-Client will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 	WP-Client
 */

//needs for translation
__( 'Staff Add/Edit Form', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_add_staff_form] and [wpc_client_edit_staff_form] shortcodes', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_staff_form">

    <?php if ( !empty( $error ) ) { ?>

        <div id="message" class="wpc_notice wpc_error">
            <?php echo $error; ?>
        </div>

    <?php } ?>

    <form id="wpc_add_staff" class="wpc_form <?php echo ( $is_edit_staff_page ) ? 'wpc_edit_staff' : ''; ?>" name="wpc_add_staff" method="post" >

        <?php if ( $is_edit_staff_page ) { ?>
            <input type="hidden" id="user_ID" name="user_data[ID]" value="<?php echo ( isset( $user_data['ID'] ) ) ? $user_data['ID'] : ''; ?>" />
        <?php } ?>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php printf( __( '%s Login', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?>" for="wpc_user_login">
                    <?php printf( __( '%s Login', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?>
                    <?php if ( !$is_edit_staff_page ) { ?>
                        <span class="wpc_required" title="<?php _e( 'This field is marked as required by the administrator', WPC_CLIENT_TEXT_DOMAIN ); ?>.">*</span>
                    <?php } ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_user_login" name="user_data[user_login]" <?php echo ( $is_edit_staff_page ) ? 'disabled="disabled"' : 'data-required_field="1"'; ?> value="<?php echo ( isset( $user_data['user_login'] ) ) ? $user_data['user_login'] : ''; ?>" />

                <?php if ( $is_edit_staff_page ) { ?>
                    <span class="wpc_description"><?php _e( 'Username cannot be changed.', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                <?php } else { ?>
                    <div class="wpc_field_validation">
                        <span class="wpc_field_required"><?php _e( 'Username is required', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </div>
                <?php } ?>

            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_email"><?php _e( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ); ?>&nbsp;<span class="wpc_required" title="<?php _e( 'This field is marked as required by the administrator', WPC_CLIENT_TEXT_DOMAIN ); ?>.">*</span></label>
            </div>
            <div class="wpc_form_field">
                <input type="email" id="wpc_email" name="user_data[email]" data-required_field="1" value="<?php echo ( isset( $user_data['email'] ) ) ? $user_data['email'] : ''; ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_wrong"><?php _e( 'Invalid Email, proper format "name@something.com"', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    <span class="wpc_field_required"><?php _e( 'Email is required', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="wpc_first_name"><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_first_name" name="user_data[first_name]" value="<?php echo ( isset( $user_data['first_name'] ) ) ? $user_data['first_name'] : ''; ?>" />
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="wpc_last_name"><?php _e( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_last_name" name="user_data[last_name]" value="<?php echo ( isset( $user_data['last_name'] ) ) ? $user_data['last_name'] : '';  ?>" />
            </div>
        </div>

        <?php
        //block 'custom_field'
        WPC()->get_template( 'loop/custom_fields.php', '', $t_args, true );
        do_action( 'wpc_client_edit_staff_form_custom_html', $user_data );
        ?>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_pass1">
                    <?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ); ?>&nbsp;

                    <?php if ( !$is_edit_staff_page ) { ?>
                        <span class="wpc_required" title="<?php _e( 'This field is marked as required by the administrator', WPC_CLIENT_TEXT_DOMAIN ); ?>.">*</span>
                    <?php } ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="password" id="wpc_pass1" <?php echo ( ! $is_edit_staff_page ) ? 'data-required_field="1"' : ''; ?> name="user_data[pass1]" autocomplete="off" value="" />
                <?php if( ! $is_edit_staff_page ) { ?>
                    <div class="wpc_field_validation">
                        <span class="wpc_field_required"><?php _e( 'Password is required', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_pass2">
                    <?php _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN ); ?>&nbsp;
                    <?php if ( ! $is_edit_staff_page ) { ?>
                        <span class="wpc_required" title="<?php _e( 'This field is marked as required by the administrator', WPC_CLIENT_TEXT_DOMAIN ); ?>.">*</span>
                    <?php } ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="password" id="wpc_pass2" name="user_data[pass2]" autocomplete="off" value="" <?php echo ( ! $is_edit_staff_page ) ? 'data-required_field="1"' : ''; ?> />
                <?php if ( ! $is_edit_staff_page ) { ?>
                    <div class="wpc_field_validation">
                        <span class="wpc_field_required"><?php _e( 'Password confirmation is required', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </div>
                <?php } ?>
                <br />
                <br />
                <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <br />
                <br />
                <div id="pass-strength-result" style="display: none;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ); ?></div>
                <span class="description indicator-hint">
                    <?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </span>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">&nbsp;</div>
            <div class="wpc_form_field">
                <label>
                    <input type="checkbox" name="user_data[send_password]" id="wpc_send_password" value="1" />
                    &nbsp;<?php printf( __( 'Send this password to the %s by email.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?>
                </label>
                <span class="wpc_description"><?php _e( 'Check to Enable', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">&nbsp;</div>
            <div class="wpc_form_field">
                <?php if ( $is_edit_staff_page ) {
                    $submit_text = sprintf( __( 'Save %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                } else {
                    $submit_text = sprintf( __( 'Add new %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                } ?>
                <input type="submit" value="<?php echo ( $submit_text ); ?>" class="button-primary wpc_submit" id="wpc_update_staff" name="wpc_update_staff" />
                <input type="button" class="wpc_button" value="<?php _e( 'Back', WPC_CLIENT_TEXT_DOMAIN ); ?>" onclick="window.history.back();" />
            </div>
        </div>
        <div class="wpc_form_line">
            <div class="wpc_form_label">&nbsp;</div>
            <div class="wpc_form_field">
                <div class="wpc_submit_info"></div>
            </div>
        </div>
    </form>
</div>