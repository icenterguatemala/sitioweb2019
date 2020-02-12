<?php
/**
 * Template Name: Files (List|Blog): Sorting Part
 * Template Description: This template for sorting at file's shortcode
 * Template Tags: Files, List View, Blog View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/sorting.php.
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
__( 'Files (List|Blog): Sorting Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for sorting at file\'s shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );
__( 'Blog View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_sort_block">
    <input type="button" class="wpc_add_sort wpc_button" value="<?php echo $sort_button; ?>" data-hover_value="<?php _e( 'Change', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
    <div class="wpc_sort_contect">

        <label><?php _e( 'Sort by', WPC_CLIENT_TEXT_DOMAIN ) ?>&nbsp;
            <select class="wpc_sorting wpc_selectbox">
                <option value="orderid_asc" <?php selected( $sort == 'order_id' && $dir == 'asc' ); ?> ><?php echo __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="orderid_desc" <?php selected( $sort == 'order_id' && $dir == 'desc' ); ?> ><?php echo __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="filename_asc" <?php selected( $sort == 'name' && $dir == 'asc' ); ?> ><?php echo __( 'Filename', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="filename_desc" <?php selected( $sort == 'name' && $dir == 'desc' ); ?> ><?php echo __( 'Filename', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="date_asc" <?php selected( $sort == 'time' && $dir == 'asc' ); ?> ><?php echo __( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="date_desc" <?php selected( $sort == 'time' && $dir == 'desc' ); ?> ><?php echo __( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="downloaded_asc"><?php echo __( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="downloaded_desc"><?php echo __( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="size_asc"><?php echo __( 'Size', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="size_desc"><?php echo __( 'Size', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="author_asc"><?php echo __( 'Author', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="author_desc"><?php echo __( 'Author', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __('DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="category_asc"><?php echo __( 'Category', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

                <option value="category_desc"><?php echo __( 'Category', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'DESC', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
            </select>
        </label>

        <input type="button" value="<?php _e( 'Apply', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_apply_sort wpc_button" />

    </div>
</div>