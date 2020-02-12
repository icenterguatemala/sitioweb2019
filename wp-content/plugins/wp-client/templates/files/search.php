<?php
/**
 * Template Name: Files: Search Part
 * Template Description: This template for search at file's shortcode
 * Template Tags: Files
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/search.php.
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
__( 'Files: Search Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for search at file\'s shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_files_search_block">

    <div class="wpc_files_search_input_block">
        <input type="text" class="wpc_files_search wpc_text" value="" />

        <span class="wpc_files_search_button" title="<?php _e( 'Search Files', WPC_CLIENT_TEXT_DOMAIN ); ?>">&nbsp;</span>
        <span class="wpc_files_clear_search" title="<?php _e( 'Clear Search', WPC_CLIENT_TEXT_DOMAIN ); ?>">&nbsp;</span>
    </div>

</div>