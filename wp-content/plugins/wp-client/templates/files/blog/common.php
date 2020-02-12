<?php
/**
* Template Name: Files (Blog): Common Part
* Template Description: This template for [wpc_client_files_blog] shortcode. Main template
* Template Tags: Files, Blog View
*
* This template can be overridden by copying it to your_current_theme/wp-client/files/blog/common.php.
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
__( 'Files (Blog): Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_blog] shortcode. Main template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Blog View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_files_blog" data-form_id="<?php echo $files_form_id; ?>">

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

    <div class="wpc_filesblog">

        <?php
        if ( !empty( $files ) ) {
            WPC()->get_template( 'files/blog/items.php', '', $t_args, true );
        } else {
            WPC()->get_template( 'files/blog/no_items.php', '', $t_args, true );
        }
        ?>

    </div>

    <?php if ( $show_pagination && !empty( $count_pages ) ) { ?>
        <input type="button" class="files_pagination_block wpc_button" data-page_number="1" value="<?php _e( 'Show More', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
    <?php } ?>

    <div class="wpc_overlay" style="display: none;"></div>

</div>