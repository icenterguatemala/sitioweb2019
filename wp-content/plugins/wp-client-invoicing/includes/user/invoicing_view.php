<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb, $wp_query;

$wpc_invoicing = WPC()->get_settings( 'invoicing' );

/*
* Show Invocing
*/
if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {
    $invoice_id = $wp_query->query_vars['wpc_page_value'];
}

$id_inv = $wpdb->get_var( $wpdb->prepare( "SELECT p.ID FROM {$wpdb->posts} p
                                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND ( coa.object_type = 'invoice' OR coa.object_type = 'estimate' ) )
                                WHERE p.ID = '%s' AND coa.assign_id = %d
                                ",
                                $invoice_id,
                                $client_id
                                ));

$invoice_data = $this->get_data( $id_inv ) ;

//have not access
if ( !isset( $invoice_data['id'] ) ) {
    return 'err';
}


//is void
if ( 'void' == $invoice_data['status'] ) {
    WPC()->redirect( WPC()->get_hub_link() );
    exit;
}



//payment process
if ( isset( $_GET['pay_now'] ) && 1 == $_GET['pay_now'] ) {

    if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'wpc_paid_invoices' ) ) {
        return '';
    }

    //start payment process
    $this->start_payment_steps( $invoice_data['id'], $client_id );

}

if( isset( $_POST['wpc_est_form'] ) ) {

    if ( isset( $_POST['wpc_accept'] ) ) {
        if ( !isset( $wpc_invoicing['est_auto_convert'] ) || 'yes' == $wpc_invoicing['est_auto_convert'] ) {
            $wpdb->update( $wpdb->posts, array( 'post_status' => 'sent' ), array( 'id' => $id_inv ) ) ;
            $this->convert_to_inv( $id_inv, 'accept' );
        } else {
            $wpdb->update( $wpdb->posts, array( 'post_status' => 'accepted' ), array( 'id' => $id_inv ) );
            $wpc_note = ( !empty( $_POST['wpc_est_accept_note'] ) ) ? $_POST['wpc_est_accept_note'] : '';
            update_post_meta( $id_inv, 'wpc_inv_comment', $wpc_note );

            $args = array(
                'invoice_id' => $invoice_data['id'],
                'client_id' => $invoice_data['client_id'],
                'invoicing_title' => $invoice_data['title'],
                'inv_number' => $invoice_data['number'],
                'accept_note' => $wpc_note,
                'total_amount' => isset( $invoice_data['total'] ) ? $invoice_data['total'] : '',
                'minimum_payment' => isset( $invoice_data['min_deposit'] ) ? $invoice_data['min_deposit'] : '',
                'due_date' => isset( $invoice_data['due_date'] ) ? WPC()->date_format( $invoice_data['due_date'], 'date' ) : '',
            );

            $emails_array = $this->get_emails_of_admins();
            foreach( $emails_array as $to_email ) {
                //send email
                WPC()->mail( 'accept_est', $to_email, $args, 'invoice_notify_admin' );

            }
        }

        WPC()->redirect( WPC()->get_hub_link() );
        exit;
    } elseif ( isset( $_POST['wpc_decline'] )) {

        $wpdb->update( $wpdb->posts, array( 'post_status' => 'declined' ), array( 'id' => $id_inv ) );
        $wpc_note = ( !empty( $_POST['wpc_est_decline_note'] ) ) ? $_POST['wpc_est_decline_note'] : '';
        update_post_meta( $id_inv, 'wpc_inv_comment', $wpc_note );

        $args = array(
            'invoice_id' => $invoice_data['id'],
            'client_id' => $invoice_data['client_id'],
            'invoicing_title' => $invoice_data['title'],
            'inv_number' => $invoice_data['number'],
            'decline_note' => $wpc_note,
            'total_amount' => isset( $invoice_data['total'] ) ? $invoice_data['total'] : '',
            'minimum_payment' => isset( $invoice_data['min_deposit'] ) ? $invoice_data['min_deposit'] : '',
            'due_date' => isset( $invoice_data['due_date'] ) ? WPC()->date_format( $invoice_data['due_date'], 'date' ) : '',
        );

        $emails_array = $this->get_emails_of_admins();
        foreach( $emails_array as $to_email ) {
            //send email
            WPC()->mail( 'est_declined', $to_email, $args, 'invoice_notify_admin' );

        }

        WPC()->redirect( WPC()->get_hub_link() );
        exit;
    }
}


$wpnonce = wp_create_nonce( 'wpc_invoice_view' . $invoice_id );
if ( is_array( $invoice_data ) ) {

    $date = ( !empty( $invoice_data['date'] ) ) ? $invoice_data['date'] : '';
    $invoice_title = !empty( $invoice_data['title'] ) ? $invoice_data['title'] : '';

    if( 'inv' == $invoice_data['type'] ) {
        $data['invoice_data'] = $invoice_data;
        $data['invoice_title'] = ( !empty( $invoice_title ) ) ? $invoice_title : __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN );
        $data['invoice_number'] = $invoice_data['number'];
        $data['invoice_status'] = ( isset( $invoice_data['status'] ) && 'new' != $invoice_data['status'] ) ? ' - ' . $this->display_status_name( $invoice_data['status'] ) : '';
    } else {
        $data['estimate_data'] = $invoice_data;
        $data['estimate_title'] = ( !empty( $invoice_title ) ) ? $invoice_title : __( 'Estimate', WPC_CLIENT_TEXT_DOMAIN );
        $data['estimate_number'] = $invoice_data['number'];
        $data['estimate_status'] = ( isset( $invoice_data['status'] ) && 'new' != $invoice_data['status'] ) ? '(' . $this->display_status_name( $invoice_data['status'] ) . ')' : '';
    }

    //make link
    if ( WPC()->permalinks ) {
        $data['download_link'] = add_query_arg( array( 'wpc_action' => 'download_pdf', 'id' => $invoice_data['id'] ), WPC()->get_slug( 'invoicing_page_id' ) . $invoice_data['id'] . '/' );
    } else {
        $data['download_link'] = add_query_arg( array( 'wpc_page' => 'invoicing', 'wpc_page_value' => $invoice_data['id'], 'wpc_action' => 'download_pdf', 'id' => $invoice_data['id'] ), WPC()->get_slug( 'invoicing_page_id', false ) );
    }

    $gateways = apply_filters( 'wpc_payment_get_activate_gateways_invoicing', array() );

    if ( ! empty( $gateways ) ) {
        if ( isset( $invoice_data['status'] ) && 'inv' == $invoice_data['type'] && !in_array( $invoice_data['status'], array( 'pending', 'paid', 'refunded' ) ) && 0 < $invoice_data['total'] ) {
            if( !current_user_can( 'wpc_client_staff' ) || ( current_user_can( 'wpc_client_staff' ) && current_user_can( 'wpc_paid_invoices' ) ) ) {
                //make link
                if ( WPC()->permalinks ) {
                    $data['paid_link'] = add_query_arg( array( 'pay_now' => '1' ), WPC()->get_slug( 'invoicing_page_id' ) . $invoice_data['id'] . '/' );
                } else {
                    $data['paid_link'] = add_query_arg( array( 'wpc_page' => 'invoicing', 'wpc_page_value' => $invoice_data['id'], 'pay_now' => '1' ), WPC()->get_slug( 'invoicing_page_id', false ) );
                }

                $order = $this->get_last_recent_order( $invoice_data['id'] );
            }
        }
    }

    if( isset( $invoice_data['status'] ) && 'est' == $invoice_data['type'] ) {
        $data['convert_estimate'] = true;
        $data['accept_button_type'] = 'submit';
        $data['accept_button_class'] = '';
        if ( isset( $wpc_invoicing['est_auto_convert'] ) && 'no' == $wpc_invoicing['est_auto_convert'] ) {
            $data['accept_button_type'] = 'button';
            $data['accept_button_class'] = 'wpc_area_on';
        }
    }

    $currency = $this->get_currency_and_side( $invoice_data['id'] );
    if ('left' == $currency['align'] ) {
        $data['left_currency'] = '<span style="color:#f6931f; font-weight:bold;">' . $currency['symbol'] . '</span>' ;
    } else {
        $data['right_currency'] = '<span style="color:#f6931f; font-weight:bold;">' . $currency['symbol'] . '</span>' ;
    }

    $total = $invoice_data['total'] - $this->get_amount_paid( $invoice_data['id'] );

    $selected_curr = ( isset( $invoice_data['currency'] ) ) ? $invoice_data['currency'] : '';
    $data['max_amount'] = $total;

    $data['step'] = $step = $this->get_step( $total );
    $data['input_width'] = 20 + 10 * strlen( $total );

    if( isset( $invoice_data['min_deposit'] ) && 0 < $invoice_data['min_deposit'] ) {
        $data['min_amount'] = $invoice_data['min_deposit'];
        $slide_max = floor( ( $total - $invoice_data['min_deposit'] ) / $step ) * $step + 1;
        $slide_min = ( $invoice_data['min_deposit'] < $step ) ? 0 : ( floor( $invoice_data['min_deposit'] / $step ) * $step ) ;
        $min_deposit = $invoice_data['min_deposit'];
    } else {
        $min_deposit =  $step;
        $data['min_amount'] = $step;
        $slide_max = floor( $total / $step ) * $step ;
        $slide_min = $step;
    }
    if ( isset( $invoice_data['deposit'] ) && $invoice_data['deposit'] && $total >= (2*$min_deposit) ) {
        $data['show_slide'] = true;
    }

    $rate_capacity = $this->get_rate_capacity();
    $show_total = number_format( (float)$total, $rate_capacity, '.', '' );
    $thousands_separator = $this->get_thousands_separator();

    $show_total = number_format( round( $show_total , 2 ), $rate_capacity, '.', $thousands_separator );

    //added CF of ivoice for displaying on template
    $data += $this->get_cf_for_inv( $invoice_data['id'] );
    $data['invoice_content'] = $this->invoicing_put_values( $invoice_data );
    
    WPC()->get_template( ( $invoice_data['type'] == 'est' ? 'estimate' : 'invoice' ) . '_page.php', 'invoicing', $data, true);
}

?>

<script type="text/javascript">

    jQuery( document ).ready( function(){
        var min = 1 * jQuery( '#text_amount' ).data( 'min' ) ;
        var max = 1 * jQuery( '#text_amount' ).data( 'max' ) ;
        var step = 1 * jQuery( '#text_amount' ).data( 'step' ) ;

        jQuery( '#text_amount' ).on( 'keypress', function(e) {
            if (!(e.which===8 || e.which===46 || e.which===39 || e.which===37 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
        });

        if ( max ) {
            jQuery( '#text_amount' ).spinner({
                min: min,
                max: max,
                step: step,
                start: max,
                numberFormat: "n"
            });
        }

        jQuery( '#text_amount' ).on( 'blur', function() {
            var val = jQuery( this ).val();
            if ( val < min )
                jQuery( this ).val( min );
            else if ( val > max )
                jQuery( this ).val( max );
        });

        jQuery( '.wpc_area_on' ).click( function() {
            jQuery( '.wpc_area' ).css( 'display', 'none' );
            if ( 'decline' === jQuery( this ).data( 'action' ) ) {
                jQuery( '#decline_area' ).css( 'display', 'block' );
            } else {
                jQuery( '#accept_area' ).css( 'display', 'block' );
            }
            jQuery( '.wpc_area_accept, .wpc_area_on' ).hide();
        });

        jQuery( '.wpc_close_area' ).click( function() {
            jQuery( '.wpc_area' ).css( 'display', 'none' );
            jQuery( '.wpc_area_accept, .wpc_area_on' ).show();
        });


    });

</script>