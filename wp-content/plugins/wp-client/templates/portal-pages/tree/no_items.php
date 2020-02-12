<?php
/**
 * Template Name: Portal Pages (Tree): No Items Text
 * Template Description: This template for [wpc_client_pagel] shortcode. It text if no portal pages items
 * Template Tags: Portal Pages, Tree View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/portal-pages/tree/no_items.php.
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
__( 'Portal Pages (Tree): No Items Text', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_pagel] shortcode. It text if no portal pages items', WPC_CLIENT_TEXT_DOMAIN );
__( 'Portal Pages', WPC_CLIENT_TEXT_DOMAIN );
__( 'Tree View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

if ( $no_text ) { ?>

    <tr>
        <td colspan="<?php echo $no_pages_colspan ?>" class="wpc_no_items"><?php echo $no_text ?></td>
    </tr>

<?php }