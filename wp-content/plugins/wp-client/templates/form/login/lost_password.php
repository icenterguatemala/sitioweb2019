<?php
/**
 * Template Name: Login Form: Lost Password Form Part
 * Template Description: This template for [wpc_client_loginf] shortcode
 * Template Tags: Login, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/login/lost_password.php.
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
__( 'Login Form: Lost Password Form Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_loginf] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Login', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

    <?php if ( !empty( $error_msg ) ) { ?>
        <p class="wpc_notice <?php echo $error_class; ?>">
            <?php echo $error_msg; ?>
        </p>
    <?php } ?>

    <p>
        <label for="user_login"><?php _e( 'Username or E-mail', WPC_CLIENT_TEXT_DOMAIN ); ?>:
            <br />
            <input type="text" tabindex="10" size="35" value="" class="input" id="user_login" name="user_login">
        </label>
    </p>

    <?php echo do_action( 'lostpassword_form' ); ?>

    <p class="submit">
        <label>
            <input type="submit" tabindex="100" value="<?php _e( 'Get New Password', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_submit button-primary" id="wp-submit" name="wp-submit" />
        </label>
    </p>
</form>

<p id="nav">
    <label>
        <a title="<?php _e( 'Back to Login Page.', WPC_CLIENT_TEXT_DOMAIN ); ?>" href="<?php echo $login_href; ?>"><?php _e( 'Remember your password?', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
    </label>
</p>