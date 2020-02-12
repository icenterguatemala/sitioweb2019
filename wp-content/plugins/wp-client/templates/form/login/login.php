<?php
/**
 * Template Name: Login Form: Login Form Part
 * Template Description: This template for [wpc_client_loginf] shortcode
 * Template Tags: Login, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/login/login.php.
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
__( 'Login Form: Login Form Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_loginf] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Login', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

    <?php echo !empty( $somefields ) ? $somefields : ''; ?>

    <?php if ( !empty( $msg_ve ) ) { ?>
        <p class="wpc_notice <?php echo $msg_ve_class ?>"><?php echo $msg_ve; ?></p>
    <?php } ?>

    <?php if ( !empty( $error_msg ) ) { ?>
        <p class="wpc_notice <?php echo $error_class; ?>"><?php echo $error_msg; ?></p>
    <?php } ?>

    <p>
        <label for="user_login"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ); ?>:
            <br />
            <input type="text" tabindex="10" size="20" value="" class="input" id="user_login" name="log" />
        </label>
    </p>

    <p>
        <label for="user_pass"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ); ?>:
            <br />
            <input type="password" tabindex="20" size="20" value="" class="input" id="user_pass" name="pwd" autocomplete="off" />
        </label>
    </p>

    <?php echo do_action( 'login_form' ); ?>

    <p class="forgetmenot">
        <label for="rememberme">
            <input type="checkbox" tabindex="90" value="forever" id="rememberme" name="rememberme" />
            <?php _e( 'Remember Me', WPC_CLIENT_TEXT_DOMAIN ); ?>
        </label>
    </p>

    <p class="submit">
        <input type="submit" tabindex="100" value="<?php _e( 'Log In', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="button-primary wpc_submit" id="wp-submit" name="wp-submit" />
        <input type="hidden" value="" name="redirect_to" />
    </p>
</form>

<?php if ( !empty( $lostpassword_href ) ) { ?>
    <p id="nav">
        <label>
            <a title="<?php _e( 'Password Lost and Found.', WPC_CLIENT_TEXT_DOMAIN ); ?>" href="<?php echo $lostpassword_href; ?>">
                <?php _e( 'Lost your password?', WPC_CLIENT_TEXT_DOMAIN ); ?>
            </a>
        </label>
    </p>
<?php } ?>