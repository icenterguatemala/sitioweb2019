<?php
/**
 * Template Name: Login Form: Common Part
 * Template Description: This template for [wpc_client_loginf] shortcode
 * Template Tags: Login
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/login/common.php.
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
__( 'Login Form: Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_loginf] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Login', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

echo do_action( 'login_head' );

?>

<div class="main_loginform_block <?php echo $classes; ?>">
    <div id="login">

        <form method="post" action="<?php echo !empty( $login_url ) ? $login_url : ''; ?>" id="loginform" name="loginform" class="wpc_form wpc_form_<?php echo $action ?>">

        <?php
            if( 'login' == $action ) {

                //display login form
                WPC()->get_template( 'form/login/login.php', '', $t_args, true );

            } elseif ( 'lostpassword' == $action && !empty( $lostpassword_href ) ) {

                //display lost password form
                WPC()->get_template( 'form/login/lost_password.php', '', $t_args, true );

            } elseif ( ( 'rp' == $action || 'resetpass' == $action ) && !empty( $lostpassword_href ) ) {

                //display reset password form
                WPC()->get_template( 'form/login/reset_password.php', '', $t_args, true );
            }
        ?>

    </div>
</div>