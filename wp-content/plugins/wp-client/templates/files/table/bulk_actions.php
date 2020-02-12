<?php
/**
 * Template Name: Files (Table): Bulk Action Part
 * Template Description: This template for [wpc_client_files_table] shortcode. Bulk Action template
 * Template Tags: Files, Table View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/table/bulk_actions.php.
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
__( 'Files (Table): Bulk Action Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_files_table] shortcode. Bulk Action template', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );
__( 'Table View', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_files_bulk_actions_block">
    <select class="wpc_files_bulk_action wpc_selectbox" name="wpc_files_bulk_action">
        <option value="none"><?php _e( 'Bulk Actions', WPC_CLIENT_TEXT_DOMAIN ); ?></option>

        <?php foreach( $bulk_actions_array as $k => $bulk_action ) { ?>
            <option value="<?php echo $k; ?>"><?php echo $bulk_action; ?></option>
        <?php } ?>

    </select>

    <input type="button" value="<?php _e( 'Apply', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_files_bulk_actions_apply_button wpc_button" />
</div>
