<?php
/**
 * Template Name: Client Registration Successful
 * Template Description: This template for [wpc_client_registration_successful] shortcode
 * Template Tags: Registration, Forms
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/registration/successful.php.
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
__( 'Client Registration Successful', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_registration_successful] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Registration', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<p>
    <?php _e( "You have successfully registered.", WPC_CLIENT_TEXT_DOMAIN ); ?>
</p>

<p>
    <?php _e( "After approval from the administrator, you will have full access to your account.", WPC_CLIENT_TEXT_DOMAIN ); ?>
</p>