<?php
/**
 * Template Name: Single Custom Field Row
 * Template Description: Displays on forms which contain custom fields
 * Template Tags: Users, Forms, Custom Fields
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/single_custom_field.php.
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
__( 'Single Custom Field Row', WPC_CLIENT_TEXT_DOMAIN );
__( 'Displays on forms which contain custom fields', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );
__( 'Forms', WPC_CLIENT_TEXT_DOMAIN );
__( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

if ( 'hidden' == $custom_field['type'] ) {
    echo $custom_field['field'];
} else { ?>
    <div class="wpc_form_line">
        <div class="wpc_form_label">
            <?php echo isset( $custom_field['label'] ) ? $custom_field['label'] : ''; ?>
        </div>
        <div class="wpc_form_field">
            <?php echo isset( $custom_field['field'] ) ? $custom_field['field'] : '';
            if ( !empty( $custom_field['description'] ) ) { ?>
                <span class="wpc_description"><?php echo $custom_field['description']; ?></span>
            <?php }

            if ( !empty( $custom_field['required'] ) ) { ?>
                <div class="wpc_field_validation">
                    <span class="wpc_field_required">
                        <?php echo $custom_field['title']; ?>
                        <?php _e( 'is required', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </span>
                </div>
            <?php } ?>
        </div>
    </div>
<?php }