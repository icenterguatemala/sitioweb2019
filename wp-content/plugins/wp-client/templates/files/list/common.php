<?php
/**
 * Template Name: Files (List): Common Part
 * Template Description: This template for [wpc_client_files_list] shortcode. Main template
 * Template Tags: Files, List View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/list/common.php.
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
__( 'Files (List): Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_list] shortcode. Main template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_files_list" data-form_id="<?php echo $files_form_id; ?>">
    <div class="wpc_table_nav_top">
        <div class="wpc_nav_wrapper">

            <?php if ( $show_filters && count( $filters ) ) {
                WPC()->get_template( 'files/filters/common.php', '', $t_args, true );
            } ?>

            <?php if ( $show_sort && !empty( $files ) ) {
                WPC()->get_template( 'files/sorting.php', '', $t_args, true );
            } ?>

            <?php if ( $show_search && !empty( $files ) ) {
                WPC()->get_template( 'files/search.php', '', $t_args, true );
            } ?>

        </div>
    </div>

    <div class="wpc_filelist">

        <?php
        if ( ! empty( $files ) ) {
            WPC()->get_template( 'files/list/items.php', '', $t_args, true );
        } else {
            WPC()->get_template( 'files/list/no_items.php', '', $t_args, true );
        }
        ?>

    </div>

    <?php if ( $show_pagination && isset( $count_pages ) && $count_pages > 1 ) { ?>
        <input type="button" class="files_pagination_block wpc_button" data-last_category_id="<?php echo $last_category_id; ?>" data-page_number="1" value="<?php _e( 'Show More', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
    <?php } ?>

    <div class="wpc_overlay" style="display: none;"></div>
</div>