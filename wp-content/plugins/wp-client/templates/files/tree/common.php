<?php
/**
 * Template Name: Files (Tree): Common Part
 * Template Description: This template for [wpc_client_files_tree] shortcode. Main template
 * Template Tags: Files, Tree View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/tree/common.php.
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
__( 'Files (Tree): Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_tree] shortcode. Main template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Tree View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

$sort_class = $show_sort ? 'wpc_sortable' : '';

?>

<div class="wpc_client_files_tree" data-form_id="<?php echo $files_form_id; ?>">

    <div class="wpc_table_nav_top">
        <div class="wpc_nav_wrapper">

            <?php if ( $show_filters && count( $filters ) ) {
                WPC()->get_template( 'files/filters/common.php', '', $t_args, true );
            } ?>

            <?php if ( $show_search && !empty( $files ) ) {
                WPC()->get_template( 'files/search.php', '', $t_args, true );
            } ?>

        </div>
    </div>

    <div class="wpc_client_files wpc_treecontent">
        <div class="wpc_files_tree_header">
            <table>
                <thead>
                    <tr valign="top">

                        <th class="wpc_th_filename wpc_primary_column <?php echo $sort_class; ?> <?php echo $sort_type == 'name' ? "wpc_sort_" . $sort : ''; ?>" <?php echo $show_sort ? 'data-wpc_sort="name"' : ''; ?> >
                            <?php _e( 'Filename', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </th>

                        <?php if ( $show_size ) { ?>

                            <th class="wpc_th_size <?php echo $sort_class; ?>" <?php echo $show_sort ? 'data-wpc_sort="size"' : ''; ?> >
                                <?php _e( 'Size', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </th>

                        <?php } ?>

                        <?php if ( $show_author ) { ?>

                            <th class="wpc_th_author">
                                <?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </th>

                        <?php } ?>

                        <?php if ( $show_date ) { ?>

                            <th class="wpc_th_time_added <?php echo $sort_class; ?> <?php echo $sort_type == 'date' ? "wpc_sort_" . $sort : '' ?>" <?php echo $show_sort ? 'data-wpc_sort="time"' : ''; ?> >
                                <?php _e( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </th>

                        <?php } ?>

                        <?php if ( $show_last_download_date ) { ?>

                            <th class="wpc_th_last_download_time <?php echo $sort_class; ?>" <?php echo $show_sort ? 'data-wpc_sort="download_time"' : ''; ?> >
                                <?php _e( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </th>

                        <?php } ?>

                        <th class="wpc_scroll_column"></th>

                    </tr>
                </thead>
            </table>
        </div>

        <div class="wpc_files_tree_content">
            <table class="wpc_files_tree">

                <?php
                if ( strlen( $tree_content ) > 0 ) {
                    WPC()->get_template( 'files/tree/items.php', '', $t_args, true );
                } else {
                    WPC()->get_template( 'files/tree/no_items.php', '', $t_args, true );
                }
                ?>

            </table>
        </div>

        <div class="wpc_ajax_overflow_tree">
            <div class="wpc_ajax_loading"></div>
        </div>
    </div>

</div>
