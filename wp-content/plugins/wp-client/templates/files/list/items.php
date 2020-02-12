<?php
/**
 * Template Name: Files (List): Items Part
 * Template Description: This template for [wpc_client_files_list] shortcode items loop
 * Template Tags: Files, List View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/list/items.php.
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
__( 'Files (List): Items Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_list] shortcode items loop', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;


foreach ( $files as $file ) {

    if ( ! empty( $file['category_name'] ) ) { ?>
        <div class="wpc_category_line">
            <h4><?php echo $file['category_name']; ?></h4>
        </div>
    <?php } ?>

    <div class="file_item">

        <div class="wpc_thumbnail_wrapper">
            <?php echo ( $show_thumbnails && isset( $file['icon'] ) ) ? $file['icon'] : ''; ?>
        </div>

        <div class="wpc_filedata_wrapper">

            <div class="wpc_filename">
                <a href="<?php echo $file['new_page'] ? $file['view_url'] : $file['url']; ?>" <?php echo ( $file['new_page'] ) ? 'target="_blank"' : ''; ?> data-timestamp="<?php echo $file['timestamp']; ?>"><?php echo $file['title']; ?></a>
            </div>

            <div class="wpc_file_details">

                <?php if ( $show_description && !empty( $file['description'] ) ) { ?>
                    <span class="wpc_filedata"><?php echo $file['description']; ?></span>
                <?php } ?>

                <?php if ( $show_tags && !empty( $file['tags'] ) ) { ?>
                    <span class="wpc_filedata">
                        <?php foreach( $file['tags'] as $tag ) { ?>
                            <span class="wpc_tag" data-term_id="<?php echo $tag['term_id']; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Tag', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $tag['name']; ?>"><?php echo $tag['name']; ?></span>
                        <?php } ?>
                    </span>
                <?php } ?>

                <?php if ( $show_size ) { ?>
                    <span class="wpc_filedata"><strong><?php _e( 'Size', WPC_CLIENT_TEXT_DOMAIN ); ?>:</strong> <?php echo $file['size']; ?></span>
                <?php } ?>

                <?php if ( $show_author && $file['author'] ) { ?>
                    <span class="wpc_filedata">
                        <strong><?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>:</strong>
                        <span class="wpc_file_author_value" data-author_id="<?php echo $file['author_id']; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file['author']; ?>"><?php echo $file['author']; ?></span>
                    </span>
                <?php } ?>

                <?php if ( $show_date ) { ?>
                    <span class="wpc_filedata"><strong><?php _e( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ); ?>:</strong> <?php echo $file['date']; ?> <?php echo $file['time']; ?></span>
                <?php } ?>

                <?php if ( $show_last_download_date && isset( $file['last_download']['date'] ) ) { ?>
                        <span class="wpc_filedata"><strong><?php _e( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ); ?>:</strong> <?php echo $file['last_download']['date']; ?> <?php echo $file['last_download']['time']; ?></span>
                <?php } ?>

            </div>


            <div class="wpc_file_actions">

                <a href="javascript:void(0);" title="<?php echo $file['title']; ?> <?php _e( 'Details', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_file_details_link" data-hide_text="<?php _e( 'Hide Details', WPC_CLIENT_TEXT_DOMAIN ); ?>"><?php _e( 'Details', WPC_CLIENT_TEXT_DOMAIN ); ?></a>&nbsp;|&nbsp;

                <?php if ( $file['new_page'] ) { ?>
                    <a href="<?php echo $file['view_url']; ?>" title="<?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" target="_blank" class="wp_file_view_link"><?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?></a>&nbsp;|&nbsp;
                <?php } ?>

                <?php if ( $file['popup'] ) { ?>
                    <a href="javascript:void(0);" title="<?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" class="wpc-video-popup" data-id="<?php echo $file['id']; ?>" data-title="<?php echo $file['title']; ?>" data-src="<?php echo $file['view_url']; ?>"><?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?></a> |
                <?php } ?>

                <a href="<?php echo $file['url']; ?>" title="<?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" <?php echo ( $file['new_page'] ) ? 'target="_blank"' : ''; ?> class="wp_file_download_link"><?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

                <?php if ( !empty( $file['delete_url'] ) ) { ?>
                    &nbsp;|&nbsp;<a onclick="return confirm( '<?php _e( 'Are you sure you want to delete this file?', WPC_CLIENT_TEXT_DOMAIN ); ?>' );" href="<?php echo $file['delete_url']; ?>" title="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" class="wp_file_delete_link"><?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
                <?php } ?>

            </div>

        </div>

    </div>

<?php } ?>
