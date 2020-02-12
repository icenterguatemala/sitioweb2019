<?php
/**
 * Template Name: User Avatar
 * Template Description: This template for [wpc_client_avatar_preview] shortcode
 * Template Tags: Users
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/user_avatar.php.
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
__( 'User Avatar', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_avatar_preview] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div style="width: <?php echo $size ?>; height: <?php echo $size ?>;">
	<?php echo WPC()->members()->user_avatar( get_current_user_id() ) ?>
</div>
