<?php
/**
 * Template Name: Portal Pages (List): Pages Items
 * Template Description: This template for [wpc_client_pagel] shortcode. It displays portal pages items.
 * Template Tags: Portal Pages, List View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/portal-pages/list/items.php.
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
__( 'Portal Pages (List): Pages Items', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_pagel] shortcode. It displays portal pages items', WPC_CLIENT_TEXT_DOMAIN );
__( 'Portal Pages', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

//display pages items
foreach( $pages as $page ) {
    if ( !empty( $page['category_name'] ) ) { ?>

        <div class="wpc_category_line">
            <h4><?php echo $page['category_name']; ?></h4>
        </div>

    <?php } ?>

    <div class="wpc_page">

        <?php if ( $show_featured_image && isset( $page['icon'] ) ) { ?>

            <div class="wpc_featured_image_wrapper">
                <?php echo $page['icon']; ?>
            </div>

        <?php } ?>

        <div class="wpc_pagedata_wrapper">

            <div class="wpc_pagetitle">
                <strong><a href="<?php echo $page['url']; ?>"><?php echo $page['title']; ?></a></strong>

                <?php if ( $show_date ) { ?>

                    <span class="wpc_filedata">[<?php echo $page['date']; ?> <?php echo $page['time']; ?>]</span>

                <?php } ?>

            </div>

            <?php if ( !empty( $page['edit_link'] ) ) { ?>

                <div class="wpc_page_actions">
                    <a href="<?php echo $page['edit_link']; ?>" ><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
                </div>

            <?php } ?>

        </div>
    </div>

<?php } ?>