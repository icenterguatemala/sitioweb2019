<?php
/**
 * Template Name: Client Registration Form
 * Template Description: This template for [wpc_client_registration_form] shortcode
 * Template Tags: Registration, Users, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/registration/registration.php.
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
__( 'Client Registration Form', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_registration_form] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Registration', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_registration_block">

    <?php if ( !empty( $error ) ) { ?>

        <div id="wpc_registration_message" class="wpc_notice wpc_error">
            <?php echo $error; ?>
        </div>

    <?php } ?>

    <form action="" method="post" id="wpc_registration_form" class="wpc_form" enctype="multipart/form-data">

        <?php if ( $show_avatar ) { ?>

            <div class="wpc_form_line">
                <div class="wpc_form_label"><label for="avatar"><?php  _e( 'Avatar', WPC_CLIENT_TEXT_DOMAIN ); ?></label></div>
                <div class="wpc_form_field"><?php echo $avatar; ?></div>
            </div>

        <?php } ?>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Business or Client Name', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_business_name">
                    <?php
                        _e( 'Business or Client Name', WPC_CLIENT_TEXT_DOMAIN );
                        echo $required_text;
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_business_name" name="business_name" data-required_field="1" value="<?php echo ( $error ) ? $vals['business_name'] : ''; ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_required">
                        <?php _e( 'Business or Client Name', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_contact_name">
                    <?php
                        _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN );
                        echo $required_text;
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_contact_name" name="contact_name" data-required_field="1" value="<?php echo ( $error ) ? $vals['contact_name'] : ''; ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_required">
                        <?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_contact_email">
                    <?php
                        _e( 'Email', WPC_CLIENT_TEXT_DOMAIN );
                        echo $required_text;
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="email" id="wpc_contact_email" name="contact_email" data-required_field="1" value="<?php echo ( $error ) ? $vals['contact_email'] : ''; ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_wrong"><?php _e( 'Invalid Email, proper format "name@something.com"', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                    <span class="wpc_field_required">
                        <?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label for="wpc_contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_contact_phone" name="contact_phone" value="<?php echo ( $error ) ? $vals['contact_phone'] : ''; ?>" />
            </div>
        </div>

        <?php WPC()->get_template( 'loop/custom_fields.php', '', $t_args, true ); ?>

        <?php do_action( 'wpc_client_registration_form_custom_html' ); ?>

        <hr class="wpc_delimiter" />

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?>" for="wpc_contact_username">
                    <?php
                        _e( 'Username', WPC_CLIENT_TEXT_DOMAIN );
                        echo $required_text;
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="text" id="wpc_contact_username" name="contact_username" data-required_field="1" value="<?php echo ( $error ) ? $vals['contact_username'] : ''; ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_required">
                        <?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_contact_password">
                    <?php
                        _e( 'Password', WPC_CLIENT_TEXT_DOMAIN );
                        echo $required_text;
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="password" id="wpc_contact_password" name="contact_password" data-required_field="1" value="" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_required">
                        <?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_contact_password2">
                    <?php
                        _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN );
                        echo $required_text;
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <input type="password" id="wpc_contact_password2" name="contact_password2" data-required_field="1" value="" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_required">
                        <?php _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
                <br />
                <br />
                <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <br />
                <br />
                <div id="pass-strength-result" style="display: none;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ); ?></div>
                <div class="indicator-hint">
                    <?php _e( '>> <strong>HINT:</strong> The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like <strong>! " ? $ % ^ & )</strong>.', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </div>
            </div>
        </div>

        <div class="wpc_form_line">
            <div class="wpc_form_label">&nbsp;</div>
            <div class="wpc_form_field">
                <input type="checkbox" <?php checked( $vals['send_password'] == 1 ); ?> name="user_data[send_password]" id="wpc_send_password" value="1" />
                &nbsp;<label for="wpc_send_password"><?php _e( 'Send this password to email?', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                <span class="wpc_description"><?php _e( 'Check to Enable', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
            </div>
        </div>


        <?php if ( $terms_used ) { ?>
            <!-- terms -->
            <div class="wpc_form_line">
                <div class="wpc_form_label">&nbsp;</div>
                <div class="wpc_form_field">
                    <label class="terms_label" data-title="<?php _e( 'Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_terms_agree">
                        <input type="checkbox" id="wpc_terms_agree" name="terms_agree" value="1" data-required_field="1" <?php echo $vals['terms_default_checked']; ?> /> <?php echo $terms_agree; ?> 
                        <a href="<?php echo $vals['terms_hyperlink']; ?>" target="_blank" title="<?php _e( 'Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ); ?>"><?php _e( 'Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ); ?></a></label>
                    <div class="wpc_field_validation">
                        <span class="wpc_field_required">
                            <?php _e( 'Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php } ?>


        <?php if ( $privacy_used ) { ?>
            <!-- privacy -->
            <div class="wpc_form_line">
                <div class="wpc_form_label">&nbsp;</div>
                <div class="wpc_form_field">
                    <label class="privacy_label" data-title="<?php _e( 'Privacy Policy', WPC_CLIENT_TEXT_DOMAIN ); ?>" for="wpc_privacy_agree">
                        <input type="checkbox" id="wpc_privacy_agree" name="privacy_agree" value="1" data-required_field="1" <?= $privacy_default_checked; ?> />
                        <?= $privacy_agree; ?></label>
                    <div class="wpc_field_validation">
                        <span class="wpc_field_required">
                            <?php _e( 'Privacy Policy', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php } ?>


        <?php if ( isset( $captcha ) ) { ?>

            <div class="wpc_form_line">
                <div class="wpc_form_label">&nbsp;</div>
                <div id="wpc_block_captcha" class="wpc_form_field"><?php echo $captcha ?></div>
            </div>

        <?php } ?>

        <div class="wpc_form_line">
            <div class="wpc_form_label">&nbsp;</div>
            <div class="wpc_form_field">
                <input type="submit" name="wpc_submit_registration" id="wpc_submit_registration" class="button-primary wpc_submit" value="<?php  _e( 'Submit Registration', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
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