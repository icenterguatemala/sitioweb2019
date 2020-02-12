<?php
/**
 * Template Name: Files: Filter Dropdown Part
 * Template Description: This template for filters at file shortcode
 * Template Tags: Files
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/files/filters/dropdown.php.
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
__( 'Files: Filter Dropdown Part', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for filters at files shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Files', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

switch( $filter_by ) {
    case 'category':
    case 'author':
    case 'tags': { ?>

        <label>
            <?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ); ?>&nbsp;
            <select class="wpc_filter wpc_selectbox">
                <?php foreach( $options as $value=>$option ) { ?>

                    <option value="<?php echo $value; ?>" <?php echo ( !empty( $option['disabled'] ) ) ? 'class="wpc_option_disabled" disabled' : ''; ?>><?php echo $option['title']; ?></option>

                <?php } ?>
            </select>
        </label>

    <?php break;
    }

    case 'creation_date': { ?>
        <label><?php _e( 'From', WPC_CLIENT_TEXT_DOMAIN ); ?>:<br />
            <input type="text" name="fake_from_date" class="from_date_field custom_datepicker_field" value="" />
            <input type="hidden" name="from_date" value="<?php echo $from_date; ?>" />
        </label>

        <br />

        <label><?php _e( 'To', WPC_CLIENT_TEXT_DOMAIN ); ?>:<br />
            <input type="text" name="fake_to_date" class="to_date_field custom_datepicker_field" value="" />
            <input type="hidden" name="to_date" value="<?php echo $to_date; ?>" />
        </label>

        <?php break;
    }
} ?>