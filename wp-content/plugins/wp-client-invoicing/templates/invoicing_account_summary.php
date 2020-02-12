<?php
/**
 * Template Name: Invoicing: Account Summary
 * Template Description: This template for [wpc_client_inv_invoicing_account_summary] shortcode
 * Template Tags: Invoicing
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/invoicing/invoicing_account_summary.php.
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
__( 'Invoicing: Invoice Page', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_inv_invoicing_account_summary] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Invoicing', WPC_CLIENT_TEXT_DOMAIN );


if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_inv_invoicing_total_amount">

    <div class="clear"></div>

    <table>
        <tbody>

            <?php if ( $show_total_amount ) { ?>

                <tr>
                    <td>
                        <?php _e( 'Total Amount Of Invoices Generated', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <?php foreach( $total_amount as $amount ) { ?>
                        <td>
                            <?php echo $amount; ?>
                        </td>
                    <?php } ?>

                </tr>

            <?php } ?>

            <?php if ( $show_total_payments ) { ?>

                <tr>
                    <td>
                        <?php _e( 'Total Payments Received', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <?php foreach( $total_payments as $payments ) { ?>
                        <td>
                            <?php echo $payments; ?>
                        </td>
                    <?php } ?>

                </tr>

            <?php } ?>

            <?php if ( $show_balance ) { ?>

                <tr>
                    <td>
                        <?php _e( 'Balance', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <?php foreach ( $balance as $bal ) { ?>
                        <td>
                            <?php echo $bal; ?>
                        </td>
                    <?php } ?>

                </tr>

            <?php } ?>

        </tbody>
    </table>
</div>