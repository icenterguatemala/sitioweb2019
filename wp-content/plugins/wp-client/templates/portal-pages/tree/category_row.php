<?php
/**
 * Template Name: Portal Pages (Tree): One Category Row
 * Template Description: This template for [wpc_client_pagel view_type="tree"] shortcode. Category row
 * Template Tags: Portal Pages, Tree View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/portal-pages/tree/category_row.php.
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
__( 'Portal Pages (Tree): One Category Row', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_pagel view_type="tree"] shortcode. Category row.', WPC_CLIENT_TEXT_DOMAIN );
__( 'Portal Pages', WPC_CLIENT_TEXT_DOMAIN );
__( 'Tree View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<tr data-tt-id="category<?php echo $category_id; ?>" valign="top" class="wpc_treetable_portal_pages_category">

    <td class="wpc_td_title wpc_folder">
        <span class="wpc_folder_block">
            <img class="wpc_folder_img" src="<?php echo $category_icon; ?>" alt="folder" title="folder" />
            <span class="wpc_foldername_block"><?php echo $category_name; ?></span>
        </span>
    </td>

    <?php if ( $show_date ) { ?>

         <td class="wpc_td_datetime">---</td>

    <?php } ?>

</tr>