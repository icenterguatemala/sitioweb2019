<?php
/**
 * Template Name: Files (Tree): One Item Row
 * Template Description: This template for [wpc_client_files_tree] shortcode. File row
 * Template Tags: Files, Tree View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/tree/item_row.php.
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
__( 'Files (Tree): One Item Row', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_tree] shortcode. File row', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Tree View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

$parent_category = !empty( $parent_cat_id ) ? 'data-tt-parent-id="category' . $parent_cat_id . '"' : '';

?>

<tr data-tt-id="file<?php echo $file_id; ?>" <?php echo $parent_category; ?> class="wpc_treetable_file" valign="top">

    <td class="wpc_td_filename">
        <span class="wpc_file_block">
            <span class="wpc_thumbnail_wrapper">
                <?php if ( $show_thumbnails ) echo $file_icon; ?>
            </span>

            <span class="wpc_filename_block">

                <span class="wpc_filename"><?php echo $file_title; ?></span>

                <?php if ( $show_tags ) { ?>
                    <span class="wpc_tags">
                        <?php echo $file_tags; ?>
                    </span>
                <?php } ?>

                <?php if ( $show_description ) { ?>
                    <span class="wpc_file_details closed"><?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file_description; ?></span>
                <?php } ?>

                <span class="wpc_file_action_links">
                    <a href="<?php echo $file_view_url; ?>" title="<?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file_title; ?>" <?php echo $view_link_visibility; ?> target="_blank" class="wp_file_view_link"><?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

                    <?php if( $file_watch_video) { ?>
                        <a href="javascript:void(0);" title="<?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file_title; ?>" class="wpc-video-popup" data-id="<?php echo $file_id; ?>" data-title="<?php echo $file_title; ?>" data-src="<?php echo $file_watch_video; ?>"><?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?></a> |
                    <?php } ?>

                    <?php echo $after_view_link; ?>

                    <a href="<?php echo $file_url; ?>" title="<?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file_title; ?>" <?php echo $download_target_blank; ?> class="wp_file_download_link"><?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

                    <?php echo $before_delete_link ?>

                    <a onclick="return confirm( '<?php _e( 'Are you sure you want to delete this file?', WPC_CLIENT_TEXT_DOMAIN ); ?>' );" <?php echo $delete_link_visibility; ?>  href="<?php echo $file_delete_url; ?>" title="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file_title; ?>" class="wp_file_delete_link"><?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

                    <?php if ( $show_description ) { ?>
                        <?php echo $before_details_link; ?><a href="javascript:void(0);" <?php echo $details_link_visibility; ?> class="wpc_show_file_details"><?php _e( 'Show Details', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
                    <?php } ?>
                </span>

            </span>
        </span>
    </td>

    <?php if( $show_size ) { ?>
         <td class="size wpc_td_size" data-size="<?php echo $file_size; ?>"><?php echo $file_size; ?></td>
    <?php } ?>

    <?php if( $show_author ) { ?>
        <td class="wpc_td_author">
            <span class="wpc_file_author_value" data-author_id="<?php echo $file_author_id; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file_author; ?>"><?php echo $file_author; ?></span>
        </td>
    <?php } ?>

    <?php if( $show_date ) { ?>
        <td class="wpc_td_time_added"><?php echo $file_date . ' ' . $file_time; ?></td>
    <?php } ?>

    <?php if( $show_last_download_date ) { ?>
        <td class="wpc_td_last_download_time">
            <?php echo $file_last_download_date . ' ' . $file_last_download_time; ?>
        </td>
    <?php } ?>

</tr>