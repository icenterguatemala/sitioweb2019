<?php
/**
 * Template Name: Client Registration (no redirect)
 * Template Description: This template for [wpc_client_registration_form] shortcode when user is logged in
 * Template Tags: Registration
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/form/registration/no_redirect.php.
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
__( 'Client Registration (no redirect)', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_registration_form] shortcode when user is logged in', WPC_CLIENT_TEXT_DOMAIN );
__( 'Registration', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<p>
    <?php printf( __( '%s already registered.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ); ?>
</p>