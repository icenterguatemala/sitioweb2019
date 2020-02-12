<?php
/**
 * Template Name: Invoicing: Invoice Page
 * Template Description: This template for [wpc_client_invoicing] shortcode
 * Template Tags: Invoicing
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/invoicing/invoice_page.php.
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
__( 'INV Invoice Page', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_invoicing] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Invoicing', WPC_CLIENT_TEXT_DOMAIN );


if ( ! defined( 'ABSPATH' ) ) exit;

?>


<div class="wrap resize-queries">

    <?php if ( isset( $invoice_data ) ) { ?>

        <h1 class="invoicing_title" style="display: inline-block; margin-right: 20px">
            <?php echo $invoice_title; ?>
            #<?php echo $invoice_number; ?>
            <?php echo $invoice_status; ?>
        </h1>

        <a href="<?php echo $download_link; ?>" style="line-height: 80px "><?php _e( 'Download&nbsp;PDF', WPC_CLIENT_TEXT_DOMAIN ); ?></a>

        <div style="clear: both;"></div>

        <?php if ( isset( $paid_link ) ) {
            if ( ! isset( $inprocess_text ) ) { ?>

                <form method="post" action="<?php echo $paid_link; ?>" id="wpc_paid_link_form" class="wpc_form">

                    <p>
                        <label for="text_amount" style="margin-right: 10px;"><?php _e( 'Payment Amount', WPC_CLIENT_TEXT_DOMAIN ); ?></label>

                        <?php echo isset( $left_currency ) ? $left_currency : ''; ?>

                        <input type="text" name="slide_amount" id="text_amount" value="<?php echo $max_amount; ?>" <?php echo empty( $show_slide ) ? 'readonly="readonly"' : "data-step=\"{$step}\" data-max=\"{$max_amount}\" data-min=\"{$min_amount}\"" ?> style="width: <?php echo $input_width; ?>px; border:0; padding-left: 0; padding-right: 0; color:#f6931f; font-weight:bold;" />

                        <?php echo isset( $right_currency ) ? $right_currency : ''; ?>

                        <input type="submit" value="<?php _e( 'Pay now!', WPC_CLIENT_TEXT_DOMAIN ); ?>" id="wpc_paid_now_link" class="wpc_submit" />
                    </p>

                </form>

            <?php } else {
                _e( 'In Process', WPC_CLIENT_TEXT_DOMAIN );
            }
        } ?>

        <hr>
        <br>

        <div class="">
            <?php echo $invoice_content; ?>
        </div>

    <?php } ?>

</div>