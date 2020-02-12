<?php
/**
 * Template Name: Files (Table): Headers Part
 * Template Description: This template for [wpc_client_files_table] shortcode. Headers template
 * Template Tags: Files, Table View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/table/headers.php.
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
__( 'Files (Table): Headers Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_table] shortcode. Headers template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Table View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

$sort_class = $show_sort ? 'wpc_sortable' : '';

?>

<?php if( $show_bulk_actions && !empty( $bulk_actions_array ) ) { ?>
    <th class="wpc_th_bulk_action">
        <input type="checkbox" class="bulk_ids_all" name="bulk_ids_all" value="all">
    </th>
<?php } ?>


<th class="wpc_th_filename wpc_primary_column <?php echo $sort_class; ?> <?php echo $sort == 'name' ? "wpc_sort_" . $dir : ''; ?>" <?php echo $show_sort ? 'data-wpc_sort="name"' : ''; ?> >
    <?php _e( 'Filename', WPC_CLIENT_TEXT_DOMAIN ); ?>
</th>

<?php if ( $show_size ) { ?>

    <th class="wpc_th_size <?php echo $sort_class; ?>" <?php echo $show_sort ? 'data-wpc_sort="size"' : ''; ?> >
        <?php _e( 'Size', WPC_CLIENT_TEXT_DOMAIN ); ?>
    </th>

<?php } ?>

<?php if ( $show_author ) { ?>

    <th class="wpc_th_author <?php echo $sort_class; ?>" <?php echo $show_sort ? 'data-wpc_sort="author"' : ''; ?> >
        <?php _e( 'Author', WPC_CLIENT_TEXT_DOMAIN ); ?>
    </th>

<?php } ?>

<?php if ( $show_date ) { ?>

    <th class="wpc_th_time_added <?php echo $sort_class; ?> <?php echo $sort == 'date' ? "wpc_sort_" . $dir : '' ?>" <?php echo $show_sort ? 'data-wpc_sort="time"' : ''; ?> >
        <?php _e( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ); ?>
    </th>

<?php } ?>

<?php if ( $show_last_download_date ) { ?>

    <th class="wpc_th_last_download_time <?php echo $sort_class; ?>" <?php echo $show_sort ? 'data-wpc_sort="download_time"' : ''; ?> >
        <?php _e( 'Downloaded', WPC_CLIENT_TEXT_DOMAIN ); ?>
    </th>

<?php } ?>

<?php if ( $show_file_cats ) { ?>

    <th class="wpc_th_category_name <?php echo $sort_class; ?> <?php echo $sort == 'category' ? "wpc_sort_" . $dir : '' ?>" <?php echo $show_sort ? 'data-wpc_sort="download_time"' : ''; ?> >
        <?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ); ?>
    </th>

<?php } ?>
