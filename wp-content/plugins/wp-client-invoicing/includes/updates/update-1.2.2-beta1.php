<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wpc_client_invoicing'" ) == "{$wpdb->prefix}wpc_client_invoicing" ) {
    $invoices = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_invoicing", ARRAY_A );
    if ( count( $invoices ) ) {
        foreach( $invoices as $inv ) {
            $status = ( 'new' == $inv['status'] ) ? 'sent' : $inv['status'];
            $all_total_old = 0;
            $items = unserialize( $inv['items'] );
            if( is_array( $items ) ) {
                foreach( $items as $key => $item ) {
                    $all_total_old += $item['rate'] * $item['quantity'];
                    $items[ $key ]['price'] = $item['rate'];
                    unset($items[ $key ]['rate']);
                }
            }

            $discounts = array();
            $total_discount = 0;
            if( 0 < (int)$inv['discount'] ) {
                $discounts[0] = ( 'percent' == $inv['discount_type'] ) ? array( 'name' => '', 'type' => 'percent', 'rate' => (int)$inv['discount'], 'total' => round( $all_total_old * (int)$inv['discount'] / 100, 2 )  ) : array( 'name' => '', 'type' => 'amount', 'rate' => (int)$inv['discount'], 'total' => (int)$inv['discount'] );
                $total_discount = $discounts[0]['total'];
            }


            $taxes_old = unserialize( $inv['tax'] );
            $taxes = array();
            if( is_array( $taxes_old ) ) {
                foreach ( $taxes_old as $key => $tax_old ) {
                    if ( is_array( $tax_old ) ){
                        $total_tax = round( ( $all_total_old - $total_discount ) * $tax_old['rate'] / 100, 2 ) ;
                        $taxes[0] = array_merge( $tax_old, array( 'name' => $key, 'total' => $total_tax, 'type' => 'after' )) ;
                    }
                }
            }

            $ver_wpc_client = get_option( 'wpc_client_ver' );
            if ( version_compare( $ver_wpc_client, '3.5.0' ) ) {
                $wpc_currency = WPC()->get_settings( 'currency' );
                foreach( $wpc_currency as $key => $curr ) {
                    if ( 1 == $curr['default'] ) {
                        $selected_currency = $key;
                        break;
                    }
                }
            }


            $wpdb->insert( $wpdb->posts, array(
                    'post_title'       => '',
                    'post_type'        => $inv['type'],
                    'post_content'     => $inv['description'],
                    'post_date'        => date( "Y-m-d H:i:s", $inv['date'] ),
                    'post_status'      => $status,
                )
            );


            $id = $wpdb->insert_id ;

            update_post_meta( $id, 'wpc_inv_items', $items );
            update_post_meta( $id, 'wpc_inv_prefix', $inv['prefix'] );
            update_post_meta( $id, 'wpc_inv_number', $inv['number'] );
            update_post_meta( $id, 'wpc_inv_discounts', $discounts );
            update_post_meta( $id, 'wpc_inv_taxes', $taxes );
            update_post_meta( $id, 'wpc_inv_due_date', $inv['due_date'] );
            update_post_meta( $id, 'wpc_inv_late_fee', $inv['late_fee'] );
            update_post_meta( $id, 'wpc_inv_total', $inv['total'] );
            update_post_meta( $id, 'wpc_inv_total_discount', $total_discount );
            update_post_meta( $id, 'wpc_inv_sub_total', $all_total_old );
            update_post_meta( $id, 'wpc_inv_terms', $inv['terms'] );
            update_post_meta( $id, 'wpc_inv_note', $inv['note'] );
            update_post_meta( $id, 'wpc_inv_deposit', true );
            if ( isset( $selected_currency ) ) {
                update_post_meta( $id, 'wpc_inv_currency', $selected_currency );
            }

            if( !empty($inv['order_id']) ) {
                $id_payments = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wpc_client_payments WHERE `order_id` = '%s' ", $inv['order_id'] ) ) ;
                update_post_meta( $id, 'wpc_inv_order_id', $id_payments );
            }

            if( !empty($inv['last_reminder']) ) {
                if ( ( $inv['due_date'] - 60*24*24 ) > time() ) {
                    update_post_meta( $id, 'wpc_inv_last_reminder', '1' );
                } else {
                    update_post_meta( $id, 'wpc_inv_last_reminder', '2' );
                }
            }

            if( !empty($inv['void_note']) ) {
                update_post_meta( $id, 'wpc_inv_void_note', $inv['void_note'] );
            }

            $object_type = ( 'inv' == $inv['type'] ) ? 'invoice' : 'estimate';
            $wpdb->insert( $wpdb->prefix . 'wpc_client_objects_assigns',
                array(
                    'object_type'     => $object_type,
                    'object_id'       => $id,
                    'assign_type'     => 'client',
                    'assign_id'       => $inv['client_id'],
                )
            );
        }
    }
}