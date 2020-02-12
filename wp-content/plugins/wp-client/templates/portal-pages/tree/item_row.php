<?php
/**
 * Template Name: Portal Pages (Tree): One Item Row
 * Template Description: This template for [wpc_client_pagel view_type="tree"] shortcode. Page row.
 * Template Tags: Portal Pages, Tree View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/portal-pages/tree/item_row.php.
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
__( 'Portal Pages (Tree): One Item Row', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_pagel view_type="tree"] shortcode. One page row.', WPC_CLIENT_TEXT_DOMAIN );
__( 'Portal Pages', WPC_CLIENT_TEXT_DOMAIN );
__( 'Tree View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

$parent_category = ! empty( $parent_cat_id ) ? 'data-tt-parent-id="category' . $parent_cat_id . '"' : '';

?>

<tr data-tt-id="page<?php echo $page_id; ?>" <?php echo $parent_category; ?> class="wpc_treetable_page" valign="top">
    <td class="wpc_td_title">
        <span class="wpc_page_block">

            <?php if ( $show_featured_image ) { ?>

                <span class="wpc_page_tree_featured_image">
                    <?php echo $featured_image; ?>
                </span>

            <?php } ?>

            <span class="wpc_page_tree_link_block">
                <a href="<?php echo $page_link; ?>" class="wpc_page_link"><strong><?php echo $page_title; ?></strong></a>
                <span class="wpc_portal_page_action_links" style="<?php echo $show_edit_link; ?>" ><?php echo $edit_link; ?></span>
            </span>

        </span>
    </td>

    <?php if ( $show_date ) { ?>

        <td class="wpc_td_datetime"><?php echo $page_date . ' ' . $page_time; ?></td>

    <?php } ?>
</tr>