<?php
/**
 * Template Name: Logout Link
 * Template Description: This template for [wpc_client_logoutb] shortcode
 * Template Tags: Logout
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/logout.php.
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
__( 'Logout Link', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_logoutb] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Logout', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<a href="<?php echo $logout_url; ?>" class="wpc_logout_link"><?php echo $logout_label; ?></a>