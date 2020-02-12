<?php
/**
 * Template Name: Files (Tree): One Category Row
 * Template Description: This template for [wpc_client_files_tree] shortcode. Category row
 * Template Tags: Files, Tree View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/tree/category_row.php.
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
__( ' Files (Tree): One Category Row', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_tree] shortcode. Category row', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Tree View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

$parent_category = ! empty( $parent_cat_id ) ? 'data-tt-parent-id="category' . $parent_cat_id . '"' : '';
?>

<tr data-tt-id="category<?php echo $category_id; ?>" <?php echo $parent_category; ?> class="wpc_treetable_file_category" valign="top">

    <td class="wpc_td_filename wpc_folder">
        <span class="wpc_folder_block">
            <span class="wpc_folder_img <?php echo $subfolder_class; ?>">
                <img class="wpc_file_icon" width="20" height="20" src="<?php echo WPC()->plugin_url . 'images/folder.png'; ?>" class="attachment-80x60" alt="folder" title="folder" />
                <span class="wpc_foldername_block"><?php echo $category_name; ?></span>
            </span>
        </span>
    </td>

    <?php if ( $show_size ) { ?>
         <td class="wpc_td_size"><span class="wpc_folder_size">&lt;<?php _e( 'Folder', WPC_CLIENT_TEXT_DOMAIN ); ?>&gt;</span></td>
    <?php } ?>

    <?php if ( $show_author ) { ?>
        <td class="wpc_td_author">---</td>
    <?php } ?>

    <?php if ( $show_date ) { ?>
         <td class="wpc_td_time_added">---</td>
    <?php } ?>

    <?php  if ( $show_last_download_date ) { ?>
        <td class="wpc_td_last_download_time">---</td>
    <?php } ?>

</tr>