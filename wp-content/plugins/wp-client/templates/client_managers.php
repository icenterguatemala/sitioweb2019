<?php
/**
 * Template Name: Client Managers
 * Template Description: This template for [wpc_client_client_managers] shortcode
 * Template Tags: Users
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/client_managers.php.
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
__( 'Client Managers', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_client_managers] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_client_managers">
    <?php foreach( $managers as $manager ) { ?>
        <h5><?php echo $manager['nickname']; ?></h5>
        <ul class="wpc_client_client_managers_list">
            <li><?php _e( 'Nickname', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['nickname']; ?></li>

            <?php if ( !empty( $manager['first_name'] ) ) { ?>
                <li><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['first_name']; ?></li>
            <?php } ?>

            <?php if ( !empty( $manager['last_name'] ) ) { ?>
                <li><?php _e( 'Last Name', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['last_name']; ?></li>
            <?php } ?>

            <?php if ( !empty( $manager['contact_phone'] ) ) { ?>
                <li><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['contact_phone']; ?></li>
            <?php } ?>

            <?php if ( !empty( $manager['address'] ) ) { ?>
                <li><?php _e( 'Address', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['address']; ?></li>
            <?php } ?>

            <li><?php _e( 'Name', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['dispay_name']; ?></li>

            <li><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $manager['email']; ?></li>
        </ul>
        <br/>
    <?php } ?>
</div>