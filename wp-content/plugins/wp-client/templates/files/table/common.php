<?php
/**
 * Template Name: Files (Table): Common Part
 * Template Description: This template for [wpc_client_files_table] shortcode. Main template
 * Template Tags: Files, Table View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/table/common.php.
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
__( 'Files (Table): Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_table] shortcode. Main template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Table View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wpc_client_files_table" data-form_id="<?php echo $files_form_id; ?>">
    <form action="" method="post" name="wpc_client_files_table<?php echo $files_form_id; ?>" class="wpc_client_files_form">

        <div class="wpc_table_nav_top">

            <div class="wpc_nav_wrapper">

                <?php if ( $show_filters && count( $filters ) ) {
                    WPC()->get_template( 'files/filters/common.php', '', $t_args, true );
                } ?>

                <?php if ( $show_search && !empty( $files ) ) {
                    WPC()->get_template( 'files/search.php', '', $t_args, true );
                } ?>

            </div>

            <div class="wpc_nav_wrapper">

                <?php if ( $show_bulk_actions && !empty( $bulk_actions_array ) && count( $files ) > 1 ) {
                    WPC()->get_template( 'files/table/bulk_actions.php', '', $t_args, true );
                } ?>

                <?php if ( $show_pagination ) {
                    WPC()->get_template( 'files/table/pagination.php', '', $t_args, true );
                } ?>

            </div>
        </div>

        <div class="wpc_client_files_table_block">
            <table class="wpc_client_files wpc_table">
                <thead>
                    <tr valign="top">

                        <?php WPC()->get_template( 'files/table/headers.php', '', $t_args, true ); ?>

                    </tr>
                </thead>

                <tbody>

                    <?php
                    if ( isset( $files ) ) {
                        WPC()->get_template( 'files/table/items.php', '', $t_args, true );
                    } else {
                        WPC()->get_template( 'files/table/no_items.php', '', $t_args, true );
                    }
                    ?>

                </tbody>

                <tfoot>
                    <tr valign="top">

                        <?php WPC()->get_template( 'files/table/headers.php', '', $t_args, true ); ?>

                    </tr>
                </tfoot>
            </table>

            <div class="wpc_ajax_overflow_table"><div class="wpc_ajax_loading"></div></div>

        </div>

        <div class="wpc_table_nav_bottom">
            <div class="wpc_nav_wrapper">

                <?php if ( $show_pagination ) {
                    WPC()->get_template( 'files/table/pagination.php', '', array(
                        'files_count' => $files_count,
                        'count_pages' => $count_pages
                    ), true );
                } ?>

                <?php if ( $show_bulk_actions && !empty( $bulk_actions_array ) && count( $files ) > 1 ) {
                    WPC()->get_template( 'files/table/bulk_actions.php', '', $t_args, true );
                } ?>

            </div>
        </div>
    </form>
</div>
