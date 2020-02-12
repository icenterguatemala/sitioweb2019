<?php
/**
 * Template Name: Client Payments
 * Template Description: This template for [wpc_client_payments_history] shortcode
 * Template Tags: Payments, Users
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/payments_history.php.
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
__( 'Client Payments', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_payments_history] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Content', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wpc_client_client_payments">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo _e( 'Date', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php echo _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php echo _e( 'Payment method', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php echo _e( 'Transaction ID', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php echo _e( 'Amount', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php echo _e( 'Status', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $payments as $key => $payment ): ?>
            <tr>
                <td><?php echo ++$key ?></td>

                <!-- Paid date -->
                <?php if( ! empty( $payment['time_paid'] ) ): ?>
                    <td><?php echo date('d.m.Y H:i', $payment['time_paid']) ?></td>
                <?php else: ?>
                    <td>-</td>
                <?php endif; ?>

                <!-- Description -->
                <?php if( ! empty( $payment['description'] ) ): ?>
                    <td><?php echo $payment['description'] ?></td>
                <?php else: ?>
                    <td><?php echo ucfirst( $payment['function'] ) ?></td>
                <?php endif; ?>

                <!-- Payment method -->
                <?php if( ! empty( $payment['payment_method'] ) ): ?>
                    <td><?php echo $payment['payment_method'] ?></td>
                <?php else: ?>
                    <td>-</td>
                <?php endif; ?>

                <!-- Transaction ID -->
                <?php if( ! empty( $payment['transaction_id'] ) ): ?>
                    <td><?php echo $payment['transaction_id'] ?></td>
                <?php else: ?>
                    <td>-</td>
                <?php endif; ?>

                <!-- Amount -->
                <?php if( ! empty( $payment['amount'] ) ): ?>
                    <td><?php echo WPC()->get_price_string( $payment['amount'], '', $payment['currency'] ); ?></td>
                <?php else: ?>
                    <td><?php echo WPC()->get_price_string( 0, '', $payment['currency'] ); ?></td>
                <?php endif; ?>

                <!-- Status -->
                <?php if( ! empty( $payment['order_status'] ) ): ?>
                    <td><?php echo ucfirst( $payment['order_status'] ) ?></td>
                <?php else: ?>
                    <td>-</td>
                <?php endif; ?>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>