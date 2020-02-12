<?php
/**
 * Template Name: Files (Blog): No Items Text
 * Template Description: This template for [wpc_client_files_blog] shortcode. It text if no files
 * Template Tags: Files, Blog View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/blog/no_items.php.
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
__( 'Files (Blog): No Items Text', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_blog] shortcode. It text if no files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Blog View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<?php if ( $no_text ) { ?>

    <p><?php echo $no_text; ?></p>

<?php } ?>