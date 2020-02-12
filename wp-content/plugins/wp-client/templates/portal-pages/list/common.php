<?php
/**
 * Template Name: Portal Pages (List): Common Part
 * Template Description: This template for [wpc_client_pagel] shortcode. Main template.
 * Template Tags: Portal Pages, List View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/portal-pages/list/common.php.
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
__( 'Portal Pages (List): Common Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_pagel] shortcode. It displays portal pages list', WPC_CLIENT_TEXT_DOMAIN );
__( 'Portal Pages', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_client_pages_list" data-form_id="<?php echo $form_id; ?>">

    <?php if ( !empty( $message ) ) { ?>
        <span id="message" class="updated fade"><?php echo $message; ?></span><br />
    <?php } ?>

    <?php if ( !empty( $add_staff_url ) ) { ?>
        <strong><a href="<?php echo $add_staff_url ?>"><?php echo $add_staff_text ?></a></strong><br />
    <?php } ?>

    <?php if ( !empty( $staff_directory_url ) ) { ?>
        <strong><a href="<?php echo $staff_directory_url; ?>"><?php echo $staff_directory_text; ?></a></strong>
    <?php } ?>


    <div class="wpc_table_nav_top">
        <div class="wpc_nav_wrapper">

            <?php if ( $show_sort && !empty( $pages ) ) { ?>

                <div class="wpc_sort_block">
                    <input type="button" class="wpc_add_sort wpc_button" value="<?php echo $sort_button; ?>" data-hover_value="<?php _e( 'Change Sort', WPC_CLIENT_TEXT_DOMAIN ); ?>" />

                    <div class="wpc_sort_contect">
                        <label>
                            <?php _e( 'Sort by', WPC_CLIENT_TEXT_DOMAIN ) ?>&nbsp;

                            <select class="wpc_sorting wpc_selectbox">
                                <option value="orderid_asc" <?php selected( $sort == 'order_id' && $dir == 'asc' ); ?> ><?php echo __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Asc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="orderid_desc" <?php selected( $sort == 'order_id' && $dir == 'desc' ); ?> ><?php echo __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Desc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="title_asc" <?php selected( $sort == 'title' && $dir == 'asc' ); ?> ><?php echo __( 'Title', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Asc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="title_desc" <?php selected( $sort == 'title' && $dir == 'desc' ); ?> ><?php echo __( 'Title', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Desc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="date_asc" <?php selected( $sort == 'date' && $dir == 'asc' ); ?> ><?php echo __( 'Added', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Asc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="date_desc" <?php selected( $sort == 'date' && $dir == 'desc' ); ?> ><?php echo __( 'Added', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Desc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="category_asc" <?php selected( $sort == 'category_name' && $dir == 'asc' ); ?> ><?php echo __( 'Category', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Asc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                                <option value="category_desc" <?php selected( $sort == 'category_name' && $dir == 'desc' ); ?> ><?php echo __( 'Category', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'Desc', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                            </select>
                        </label>

                        <input type="button" value="<?php _e( 'Apply', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_apply_sort wpc_button" />
                    </div>

                </div>

            <?php } ?>

            <?php if ( $show_search ) { ?>

                <div class="wpc_pages_search_block">
                    <div class="wpc_pages_search_input_block">
                        <input type="text" class="wpc_pages_search wpc_text" value="" />
                        <span class="wpc_pages_search_button" title="<?php _e( 'Search Page', WPC_CLIENT_TEXT_DOMAIN ); ?>">&nbsp;</span>
                        <span class="wpc_pages_clear_search" title="<?php _e( 'Clear Search', WPC_CLIENT_TEXT_DOMAIN ); ?>">&nbsp;</span>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>

    <div class="wpc_pagelist">

        <?php
        if ( !empty( $pages ) ) {
            //display items of portal pages
            WPC()->get_template( 'portal-pages/list/items.php', '', $t_args, true );
        } else {
            //display no items text
            WPC()->get_template( 'portal-pages/list/no_items.php', '', $t_args, true );
        }
        ?>

    </div>

    <?php if ( !empty( $pages ) ) {
        $pagi_block_style = !( $show_pagination && isset( $count_pages ) && $count_pages > 1 ) ? 'display: none;' : '';
        ?>
        <input type="button" class="pages_pagination_block wpc_button" data-last_category_id="<?php echo $last_category_id; ?>" data-page_number="1" style="<?php echo $pagi_block_style ?>" value="<?php _e( 'Show More', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
    <?php } ?>

    <div class="wpc_overlay" style="display: none;"></div>
</div>