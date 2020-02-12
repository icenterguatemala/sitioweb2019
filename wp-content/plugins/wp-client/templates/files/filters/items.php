<?php
/**
 * Template Name: Files: Filter Items Part
 * Template Description: This template for filters at file shortcode
 * Template Tags: Files
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/filters/items.php.
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
__( 'Files: Filter Items Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for filters at files shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! empty( $filters['categories'] ) ) { ?>
    <option value="category"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
<?php } ?>

<?php if ( ! empty( $filters['authors'] ) ) { ?>
    <option value="author"><?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
<?php } ?>

<?php if ( ! empty( $filters['tags'] ) ) { ?>
    <option value="tags"><?php _e( 'Tags', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
<?php } ?>

<?php if ( ! empty( $filters['dates'] ) ) { ?>
    <option value="creation_date"><?php _e( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
<?php } ?>