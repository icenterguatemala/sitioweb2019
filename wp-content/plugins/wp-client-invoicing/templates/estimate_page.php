<?php
/**
 * Template Name: Invoicing: Estimate Page
 * Template Description: This template for [wpc_client_invoicing] shortcode
 * Template Tags: Invoicing
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/invoicing/estimate_page.php.
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
__( 'Invoicing: Estimate Page', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_invoicing] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Invoicing', WPC_CLIENT_TEXT_DOMAIN );


if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wrap">

    <?php if ( isset( $estimate_data ) ) { ?>

        <h1 class="invoicing_title">
            <?php echo $estimate_title; ?>
            #<?php echo $estimate_number; ?>
            <?php echo $estimate_status; ?>
        </h1>

        <a href="<?php echo $download_link; ?>"><?php _e( 'Download&nbsp;PDF', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

        <hr/>
        <br/>

        <div class="wpc_inv_estimate_content">
            <?php echo $invoice_content; ?>
        </div>

        <?php if ( isset( $convert_estimate ) ) { ?>

            <form method="post" action="" class="wpc_form">
                <input type="hidden" name="wpc_est_form" value="1" />
                <input type="<?php echo $accept_button_type; ?>" value="<?php _e( 'Accept', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_area_accept wpc_submit <?php echo $accept_button_class; ?>" data-action="accept" name="wpc_accept" />
                <input type="button" value="<?php _e( 'Decline', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_area_on wpc_button" data-action="decline" />

                <div id="accept_area" style="display: none" class="wpc_area">
                    <br/>
                    <p><label for="wpc_est_accept_note"><?php _e( 'Your comment', WPC_CLIENT_TEXT_DOMAIN ); ?></label></p>
                    <textarea cols="50" rows="5" name="wpc_est_accept_note" class="textarea_wpc_note" id="wpc_est_accept_note"></textarea>
                    <br/>
                    <input type="submit" value="<?php _e( 'Confirm Accept', WPC_CLIENT_TEXT_DOMAIN ); ?>" name="wpc_accept" class="wpc_submit" />
                    <input type="button" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_close_area wpc_button" />
                </div>

                <div id="decline_area" style="display: none" class="wpc_area">
                    <br/>
                    <p><label for="wpc_est_decline_note"><?php _e( 'Why have you declined the estimate?', WPC_CLIENT_TEXT_DOMAIN ); ?></label></p>
                    <textarea cols="50" rows="5" name="wpc_est_decline_note" class="textarea_wpc_note" id="wpc_est_decline_note"></textarea>
                    <br/>
                    <input type="submit" value="<?php _e( 'Confirm Decline', WPC_CLIENT_TEXT_DOMAIN ); ?>" name="wpc_decline" class="wpc_submit" />
                    <input type="button" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_close_area wpc_button" />
                </div>
            </form>

        <?php }
    } ?>

</div>