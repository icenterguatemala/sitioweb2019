<?php
/**
 * Template Name: Files: Filter Common Part
 * Template Description: This template for filters at files shortcode. Main template
 * Template Tags: Files
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/filters/common.php.
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
__( 'Files: Filter Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for filters at files shortcode. Main template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_files_filter_block">
    <div class="wpc_filters_select_wrapper">
        <input type="button" class="wpc_show_filters wpc_button" value="<?php _e( 'Add Filter', WPC_CLIENT_TEXT_DOMAIN ); ?>"/>

        <div class="wpc_filters_contect">

            <label>
                <?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?>&nbsp;

                <select class="wpc_filter_by wpc_selectbox">
                    <?php WPC()->get_template( 'files/filters/items.php', '', $t_args, true ); ?>
                </select>
            </label>

            <div class="wpc_ajax_content">
                <div class="wpc_loading_overflow">
                    <div class="wpc_small_ajax_loading"></div>
                </div>

                <div class="wpc_overflow_content">
                    <div class="wpc_msg_filter_selectors"></div>
                    <input type="button" value="<?php _e( 'Apply Filter', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_add_filter wpc_button" />
                </div>
            </div>

        </div>
    </div>

    <div class="wpc_filters_wrapper"></div>
</div>