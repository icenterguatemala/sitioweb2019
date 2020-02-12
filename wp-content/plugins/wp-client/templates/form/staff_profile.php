<?php
/**
 * Template Name: Staff Profile Form
 * Template Description: This template for [wpc_staff_profile] shortcode if user role is WP-Client Staff
 * Template Tags: Users, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/staff_profile.php.
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
__( 'Staff Profile Form', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_staff_profile] shortcode if user role is WP-Client Staff', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="profile_staff" id="client_profile">

    <?php if ( !empty( $message ) ) { ?>

        <div id="message" class="wpc_notice <?php echo $message_class; ?>">
            <?php echo $message; ?>
        </div>

    <?php } ?>

    <form action="" method="post" class="wpc_form" id="wpc_profile_form" enctype="multipart/form-data">
        <input type="hidden" name="wpc_action" value="staff_profile" />
        <?php echo $nonce; ?>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="avatar"><?php _e( 'Avatar', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field"><?php echo $avatar_field; ?></div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="wpc_staff_login"><?php _e( 'Login', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_staff_login" disabled="disabled" value="<?php echo $staff_login; ?>" />
                <span class="wpc_description"><?php _e( 'Login cannot be changed.', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>" for="wpc_staff_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?><span class="wpc_required" title="<?php _e( 'This field is required', WPC_CLIENT_TEXT_DOMAIN ); ?>">*</span></label>
            </div>
            <div class="wpc_form_field">
                <input type="email" id="wpc_staff_email" name="user_data[email]" class="required" data-required_field="1" value="<?php echo $staff_email; ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_wrong"><?php _e( 'Invalid Email, proper format "name@something.com"', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    <span class="wpc_field_required">
                        <?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="wpc_first_name"><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_first_name" name="user_data[first_name]" value="<?php echo $first_name; ?>" />
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="wpc_last_name"><?php _e( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_last_name" name="user_data[last_name]" value="<?php echo $last_name; ?>" />
            </div>
        </div>

        <?php WPC()->get_template('loop/custom_fields.php', '', $t_args, true); ?>

        <?php do_action( 'wpc_client_staff_profile_custom_html' ); ?>

        <?php
        if ( $modify_profile ) {

            if ( $reset_password ) { ?>
                <div class="wpc_form_line">
                    <div class="wpc_form_label">
                        <label for="wpc_contact_password"><?php _e( 'New Password', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                    </div>
                    <div class="wpc_form_field">
                        <input type="password" id="wpc_contact_password" name="contact_password" value="" />
                    </div>
                </div>

                <div class="wpc_form_line">
                    <div class="wpc_form_label">
                        <label for="wpc_contact_password2"><?php _e( 'Confirm New Password', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                    </div>
                    <div class="wpc_form_field">
                        <input type="password" id="wpc_contact_password2" name="contact_password2" value="" />
                        <br />
                        <br />
                        <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <br />
                        <br />
                        <div id="pass-strength-result" style="display: none;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ); ?></div>
                        <div class="indicator-hint">
                            <?php _e( '<strong>Hint:</strong> The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like <strong>! " ? $ % ^ & )</strong>.', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="wpc_form_line">
                <div class="wpc_form_label">&nbsp;</div>
                <div class="wpc_form_field">
                    <input type="submit" name="wpc_submit_profile" id="wpc_submit_profile" class="button-primary wpc_submit" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                </div>
            </div>
            <div class="wpc_form_line">
                <div class="wpc_form_label">&nbsp;</div>
                <div class="wpc_form_field">
                    <div class="wpc_submit_info"></div>
                </div>
            </div>

        <?php } ?>

    </form>
</div>