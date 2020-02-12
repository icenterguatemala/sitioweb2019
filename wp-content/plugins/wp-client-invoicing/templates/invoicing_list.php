<?php
/**
 * Template Name: Invoicing: List Invoices/Estimates
 * Template Description: This template for [wpc_client_invoicing_list] shortcode
 * Template Tags: Invoicing, List View
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/invoicing/invoicing_list.php.
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
__( 'Invoicing: List Invoices/Estimates', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_invoicing_list] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Invoicing', WPC_CLIENT_TEXT_DOMAIN );
__( 'List View', WPC_CLIENT_TEXT_DOMAIN );


if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_client_invoicing_list">

    <div class="clear"></div>

    <table>
        <?php if ( isset( $hide_table_header ) && 'no' == $hide_table_header ) { ?>
            <thead>
                <tr>
                    <th><?php echo __( '#', WPC_CLIENT_TEXT_DOMAIN ) ?></th>

                    <?php if( isset( $show_date ) && 'yes' == $show_date ): ?>
                        <th> | <?php echo __( 'Date', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <?php endif; ?>

                    <?php if( isset( $show_due_date ) && 'yes' == $show_due_date ):?>
                        <th> | <?php echo __( 'Due Date', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <?php endif; ?>

                    <?php if( isset( $show_description ) && 'yes' == $show_description ): ?>
                        <th> | <?php echo __( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <?php endif; ?>

                    <?php if( isset( $show_type_payment ) && 'yes' == $show_type_payment ): ?>
                        <th> | <?php echo __( 'Type Payment', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <?php endif; ?>

                    <?php if( isset( $show_invoicing_amount ) && 'yes' == $show_invoicing_amount ): ?>
                        <th> | <?php echo __( 'Total', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <?php endif; ?>

                    <?php if( isset( $show_pay_now ) && 'yes' == $show_pay_now ): ?>
                        <th> | <?php echo __( 'Status', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
        <?php } ?>
        <tbody>

            <?php if ( ! empty( $invoices ) ) {
                foreach ( $invoices as $invoice ) { ?>

                    <tr>
                        <td>
                            <a href="<?php echo $invoice['invoicing_link']; ?>"># <?php echo $invoice['invoicing_number']; ?></a>
                        </td>

                        <?php if ( $show_date ) { ?>

                            <td>
                                |&nbsp;&nbsp;
                                <?php echo $invoice['date']; ?> <?php echo $invoice['time']; ?>
                            </td>

                        <?php } ?>

                        <?php if ( $show_due_date ) { ?>

                            <td>
                                |&nbsp;&nbsp;
                                <?php echo $invoice['due_date']; ?>
                            </td>

                        <?php } ?>

                        <?php if ( $show_description ) { ?>

                            <td>
                                |&nbsp;&nbsp;
                                <?php echo $invoice['description']; ?>
                            </td>

                        <?php } ?>

                        <?php if ( $show_type_payment ) { ?>

                            <td>
                                |&nbsp;&nbsp;
                                <?php echo $invoice['type_payment']; ?>
                            </td>

                        <?php } ?>

                        <?php if ( $show_invoicing_amount ) { ?>

                            <td>
                                |&nbsp;&nbsp;
                                <?php echo $invoice['invoicing_amount']; ?>
                            </td>

                        <?php } ?>

                        <?php if ( $show_pay_now ) { ?>

                            <td>
                                |&nbsp;&nbsp;
                                <?php if ( isset( $invoice['inv_pay_now_link'] ) ) { ?>
                                    <a href="<?php echo $invoice['inv_pay_now_link']; ?>"><?php _e('Pay Now', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
                                <?php } else { ?>
                                    <?php echo $invoice['inv_pay_now']; ?>
                                <?php } ?>

                            </td>
                        <?php } ?>

                    </tr>

                <?php }
            } else { ?>

                <tr>
                    <td>
                        <?php
                            if ( !empty( $no_text ) ) {
                                echo $no_text;
                            }
                        ?>
                    </td>
                </tr>

            <?php } ?>

        </tbody>
    </table>

</div>