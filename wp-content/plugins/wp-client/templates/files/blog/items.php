<?php
/**
 * Template Name: Files (Blog): Items Part
 * Template Description: This template for [wpc_client_files_blog] shortcode items loop
 * Template Tags: Files, Blog View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/blog/items.php.
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
__( 'Files (Blog): Items Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_blog] shortcode items loop', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Blog View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

foreach( $files as $file ) { ?>

    <div class="file_item">
        <div class="wpc_blogitem_head">
            <div class="wpc_blogitem_date">
                <p title="<?php _e( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                <?php echo $show_date ? $file['date'] . ' ' . $file['time'] : '';

                if ( $show_author && $file['author'] ) { ?>
                    &nbsp;by&nbsp;
                    <span class="wpc_filedata wpc_file_author_value" data-author_id="<?php echo $file['author_id']; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file['author']; ?>"><?php echo $file['author']; ?></span>
                <?php } ?>
                </p>
            </div>

            <div class="wpc_blogitem_category">
                <?php if ( $show_file_cats && !empty( $file['category_name'] ) ) { ?>
                    <span class="wpc_filedata wpc_file_category_value" data-category_id="<?php echo $file['category_id']; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file['category_name']; ?>"><?php echo $file['category_name']; ?></span>
                <?php } ?>
            </div>
        </div>

        <h4>
            <span class="wpc_blogitem_title">
                <?php echo $file['title']; ?>
            </span>

            <?php if ( $show_size ) { ?>
                <span class="wpc_file_size">(<?php echo $file['size']; ?>)</span>
            <?php } ?>
        </h4>

        <div class="wpc_thumbnail_wrapper">
            <?php if ( $show_thumbnails && isset( $file['icon'] ) && ! isset( $file['popup'] ) ) {
                echo $file['icon'];
            } ?>
        </div>

        <div class="wpc_file_actions">
            <?php if ( $file['new_page'] ) { ?>
                <a href="<?php echo $file['view_url']; ?>" title="<?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" target="_blank" class="wp_file_view_link"><?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?></a>&nbsp;|&nbsp;
            <?php } ?>

            <?php if( $file['popup'] ) { ?>
                <a href="javascript:void(0);" title="<?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" class="wpc-video-popup" data-id="<?php echo $file['id']; ?>" data-title="<?php echo $file['title']; ?>" data-src="<?php echo $file['view_url']; ?>"><?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?></a> |
            <?php } ?>

            <a href="<?php echo $file['url']; ?>" title="<?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" <?php echo $file['new_page'] ? 'target="_blank"' : ''; ?> class="wp_file_download_link"><?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

            <?php if ( !empty( $file['delete_url'] ) ) { ?>
                &nbsp;|&nbsp;<a onclick="return confirm( '<?php _e( 'Are you sure you want to delete this file?', WPC_CLIENT_TEXT_DOMAIN ); ?>' );" href="<?php echo $file['delete_url']; ?>" title="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title'] ?>" class="wp_file_delete_link"><?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
            <?php } ?>
        </div>

        <div class="wpc_blogitem_content">
            <?php if ( $show_description && !empty( $file['description'] ) ) { ?>
                <p class="wpc_file_description"><?php echo $file['description']; ?></p>
            <?php } ?>

            <div class="wpc_blogitem_footer">

                <div class="wpc_blogitem_tags">
                    <?php if ( $show_tags && !empty( $file['tags'] ) ) { ?>
                        <span class="wpc_filedata">
                            <strong><?php _e( 'Tags', WPC_CLIENT_TEXT_DOMAIN ); ?></strong>:
                            <?php foreach( $file['tags'] as $tag ) { ?>
                                <span class="wpc_tag" data-term_id="<?php echo $tag['term_id']; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Tag', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $tag['name']; ?>"><?php echo $tag['name']; ?></span>
                            <?php } ?>
                        </span>
                    <?php } ?>
                </div>

                <div class="wpc_blogitem_downloaded">
                    <?php if ( $show_last_download_date && !empty( $file['last_download']['date'] ) ) { ?>
                        <p><strong><?php _e( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ); ?></strong>: <?php echo $file['last_download']['date']; ?> <?php echo $file['last_download']['time']; ?></p>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>

<?php } ?>