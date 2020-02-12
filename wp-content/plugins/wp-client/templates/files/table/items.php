<?php
/**
 * Template Name: Files (Table): Items Part
 * Template Description: This template for [wpc_client_files_table] shortcode items loop
 * Template Tags: Files, Table View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/table/items.php.
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
__( 'Files (Table): Items Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_table] shortcode items loop', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Table View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;


foreach( $files as $file ) { ?>

    <tr valign="top" class="wpc_file_row">

        <?php if ( $show_bulk_actions && !empty( $bulk_actions_array ) ) { ?>

            <td class="wpc_td_bulk_action">
                <input type="checkbox" class="bulk_ids" name="bulk_ids[]" value="<?php echo $file['id']; ?>">
            </td>

        <?php } ?>

        <td class="wpc_td_filename wpc_primary_column">

            <div class="wpc_show_details"></div>

            <?php if ( $show_thumbnails ) { ?>
                <div class="wpc_thumbnail_wrapper">
                    <?php echo isset( $file['icon'] ) ? $file['icon'] : ''; ?>
                </div>
            <?php } ?>

            <div class="wpc_filedata_wrapper <?php echo !$show_thumbnails ? 'wpc_fullwidth' : ''; ?>" >
                <span class="wpc_filedata wpc_file_name_value">

                    <?php if ( $file['new_page'] ) { ?>

                        <a href="<?php echo $file['view_url']; ?>" title="<?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" target="_blank">
                            <?php echo $file['title']; ?>
                        </a>

                    <?php } else { ?>

                        <a href="<?php echo $file['url']; ?>" title="<?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>">
                            <?php echo $file['title']; ?>
                        </a>

                    <?php } ?>

                </span>

                <?php if ( $show_description && $file['description'] ) { ?>

                    <span class="wpc_filedata wpc_file_description_value" title="<?php echo $file['description']; ?>" >
                        <?php echo $file['description']; ?>
                    </span>

                <?php } ?>

                <?php if ( $show_tags && !empty( $file['tags'] ) ) { ?>

                    <span class="wpc_filedata wpc_file_tags_value">

                        <?php foreach( $file['tags'] as $tag ) { ?>
                            <span class="wpc_tag" data-term_id="<?php echo $tag['term_id']; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Tag', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $tag['name']; ?>">
                                <?php echo $tag['name']; ?>
                            </span>
                        <?php } ?>

                    </span>

                <?php } ?>

                <span class="wpc_file_actions">

                    <?php if ( $file['new_page'] ) { ?>

                        <a href="<?php echo $file['view_url']; ?>" title="<?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" target="_blank" class="wp_file_view_link">
                            <?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </a>&nbsp;|&nbsp;

                    <?php } ?>

                  <?php if( $file['popup'] ) { ?>
                      <a href="javascript:void(0);" title="<?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" class="wpc-video-popup" data-id="<?php echo $file['id']; ?>" data-title="<?php echo $file['title']; ?>" data-src="<?php echo $file['view_url']; ?>"><?php _e( 'Watch', WPC_CLIENT_TEXT_DOMAIN ); ?></a> |
                  <?php } ?>

                    <a href="<?php echo $file['url']; ?>" title="<?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php echo $file['title']; ?>" <?php echo $file['new_page'] ? 'target="_blank"' : ''; ?> class="wp_file_download_link">
                        <?php _e( 'Download', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </a>

                    <?php if ( !empty( $file['delete_url'] ) ) { ?>

                        &nbsp;|&nbsp;
                        <a onclick="return confirm( '<?php _e('Are you sure you want to delete this file?', WPC_CLIENT_TEXT_DOMAIN ); ?>' );" href="<?php echo $file['delete_url']; ?>" title="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?> <?php echo $file['title']; ?>" class="wp_file_delete_link">
                            <?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </a>

                    <?php } ?>

                </span>
            </div>
        </td>

        <?php if ( $show_size ) { ?>

            <td class="wpc_td_size" data-size="<?php echo $file['size']; ?>" data-wpc_colname="<?php _e( 'Size', WPC_CLIENT_TEXT_DOMAIN ); ?>:">
                <span class="wpc_filedata wpc_file_size_value">
                    <?php echo $file['size']; ?>
                </span>
            </td>

        <?php } ?>

        <?php if ( $show_author ) { ?>

            <td class="wpc_td_author" data-wpc_colname="<?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>:">
                <span class="wpc_filedata wpc_file_author_value" data-author_id="<?php echo $file['author_id'] ? $file['author_id'] : ''; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file['author']; ?>" >
                    <?php echo $file['author']; ?>
                </span>
            </td>

        <?php } ?>

        <?php if ( $show_date ) { ?>

            <td class="wpc_td_time" data-timestamp="<?php echo $file['timestamp']; ?>"
                data-wpc_colname="<?php _e( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ); ?>:">
                <span class="wpc_filedata wpc_file_added_value">
                    <?php echo $file['date'] . ' ' . $file['time']; ?>
                </span>
            </td>

        <?php } ?>

        <?php if ( $show_last_download_date ) { ?>

            <td class="wpc_td_download_time" data-wpc_colname="<?php _e( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ); ?>:" data-timestamp="<?php echo isset( $file['last_download']['date'] ) ? $file['last_download']['date'] : ''; ?>" >
                <span class="wpc_filedata wpc_file_downloaded_value">
                    <?php if ( isset( $file['last_download']['date'] ) ) {
                        echo $file['last_download']['date'] . ' ' . $file['last_download']['time'];
                    } ?>
                </span>
            </td>

        <?php } ?>

        <?php if ( $show_file_cats ) { ?>

            <td class="wpc_td_category" data-wpc_colname="<?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ); ?>:">

                <?php if ( isset( $file['category_name'] ) ) { ?>

                    <span class="wpc_filedata wpc_file_category_value" data-category_id="<?php echo $file['category_id'] ? $file['category_id'] : ''; ?>" title="<?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?> <?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ); ?>: <?php echo $file['category_name']; ?>">
                        <?php echo $file['category_name']; ?>

                    </span>
                <?php } ?>

            </td>

        <?php } ?>

    </tr>

<?php } ?>