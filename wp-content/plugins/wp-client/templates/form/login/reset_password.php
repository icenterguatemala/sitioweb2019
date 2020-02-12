<?php
/**
 * Template Name: Login Form: Reset Password Form Part
 * Template Description: This template for [wpc_client_loginf] shortcode
 * Template Tags: Login, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/login/reset_password.php.
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
__( 'Login Form: Reset Password Form Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_loginf] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Login', WPC_CLIENT_TEXT_DOMAIN );


if ( ! defined( 'ABSPATH' ) ) exit;

?>

    <?php if ( !empty( $error_msg ) ) { ?>
        <p class="wpc_notice <?php echo $error_class; ?>"><?php echo $error_msg; ?></p>
    <?php } ?>

    <?php if ( ! in_array( $error_msg, $check_invalid ) ) { ?>

        <input type="hidden" id="user_login" value="<?php echo !empty( $user_login ) ? $user_login : ''; ?>" autocomplete="off" />

        <p>
            <label for="pass1">
                <?php _e( 'New password:', WPC_CLIENT_TEXT_DOMAIN ); ?>
                <br />
                <input type="password" name="pass1" id="pass1" class="input" size="35" value="" autocomplete="off" />
            </label>
        </p>

        <p>
            <label for="pass2">
                <?php _e( 'Confirm new password:', WPC_CLIENT_TEXT_DOMAIN ); ?>
                <br />
                <input type="password" name="pass2" id="pass2" class="input" size="35" value="" autocomplete="off" />
            </label>
        </p>
        <p>
            <input type="button" class="wpc_generate_password_button button" value="<?php _e( 'Generate Password', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
        </p>

        <div id="pass-strength-result"><?php _e( 'Strength Indicator', WPC_CLIENT_TEXT_DOMAIN ); ?></div>

        <p class="description indicator-hint">
            <?php _e( '<strong>Hint</strong>: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like <strong>! " ? $ % ^ &amp; ).</strong>', WPC_CLIENT_TEXT_DOMAIN ); ?>
        </p>

        <?php echo do_action( 'resetpass_form', $user ); ?>

        <br class="clear"/>

        <p class="submit">
            <label>
                <input type="submit" name="wp-submit" id="wp-submit" class="wpc_submit button-primary" value="<?php _e('Reset Password', WPC_CLIENT_TEXT_DOMAIN ) ?>"/>
            </label>
        </p>

    <?php } ?>

</form>