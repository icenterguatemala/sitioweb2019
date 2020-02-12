<?php
/**
 * Template Name: Custom Fields loop
 * Template Description: Displays on forms which contain custom fields
 * Template Tags: Users, Forms, Custom Fields
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/loop/custom_fields.php.
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
__( 'Custom Fields loop', WPC_CLIENT_TEXT_DOMAIN );
__( 'Displays on forms which contain custom fields', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );
__( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty( $custom_fields ) ) {
    foreach( $custom_fields as $key => $custom_field ) {
        WPC()->get_template( 'single_custom_field.php', '', array( 'custom_field' => $custom_field ), true );
    }
}