<?php
/**
 * Template Name: Files (Table): Pagination
 * Template Description: This template for pagination at table file's shortcode
 * Template Tags: Files, Table View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/table/pagination.php.
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
__( 'Files (Table): Pagination', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for pagination at table file\'s shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Table View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_files_pagination wpc_files_uploaded">

    <span class="wpc_files_counter">
        <?php

        if ( $files_count > 0 ) {
            echo $files_count . ' ' . ( ( $files_count == 1 ) ? __( 'item', WPC_CLIENT_TEXT_DOMAIN ) :  __( 'items', WPC_CLIENT_TEXT_DOMAIN ) );
        } else {
            _e( 'No items', WPC_CLIENT_TEXT_DOMAIN );
        }
        ?>
    </span>

    <?php if ( $count_pages > 1 ) { ?>

        <a href="javascript:void(0);" class="wpc_pagination_links wpc_first" style="display:none;"><<</a>
        <a href="javascript:void(0);" class="wpc_pagination_links wpc_previous" style="display:none;"><</a>
        <a href="javascript:void(0);" class="wpc_pagination_links wpc_previous_pages" style="display:none;">...</a>
        <a href="javascript:void(0);" class="wpc_pagination_links wpc_active_page">1</a>

        <?php if ( $count_pages <= 3 ) {

            for ( $page = 2; $page <= $count_pages; $page++ ) { ?>

                <a href="javascript:void(0);" class="wpc_pagination_links"><?php echo $page; ?></a>

            <?php }

        } elseif ( $count_pages > 3 ) {

            for ( $page = 2; $page <= 3; $page++ ) { ?>

                <a href="javascript:void(0);" class="wpc_pagination_links"><?php echo $page; ?></a>

            <?php }

            for ( $page = 4; $page <= $count_pages; $page++ ) { ?>

                <a href="javascript:void(0);" class="wpc_pagination_links" style="display:none;"><?php echo $page; ?></a>

            <?php } ?>

            <a href="javascript:void(0);" class="wpc_pagination_links wpc_next_pages">...</a>

        <?php } ?>

        <a href="javascript:void(0);" class="wpc_pagination_links wpc_next">></a>
        <a href="javascript:void(0);" class="wpc_pagination_links wpc_last">>></a>

    <?php } ?>

</div>