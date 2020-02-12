<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


if ( !class_exists( "WPC_INV_User_Shortcodes" ) ) {

    class WPC_INV_User_Shortcodes extends WPC_INV_Common {

        /**
        * constructor
        **/
        function inv_shortcodes_construct() {
        }

        /*
        * Shortcode for Show Account Summary
        */
        function shortcode_invoicing_account_summary( $atts, $contents = null ) {
            global $wpdb;
            //checking access
            $client_id = WPC()->checking_page_access();

            if ( false === $client_id ) {
                return '';
            }

            //display blanck for Staff
            if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'wpc_view_invoices' ) && !current_user_can( 'administrator' ) ) {
                return '';
            }

            $data = array();

            $data['show_total_amount'] = ( !isset( $atts['show_total_amount'] ) || 'no' != $atts['show_total_amount'] ) ? true : false ;
            $data['show_total_payments'] = ( !isset( $atts['show_total_payments'] ) || 'no' != $atts['show_total_payments'] ) ? true : false;
            $data['show_balance'] = ( !isset( $atts['show_balance'] ) || 'no' != $atts['show_balance'] ) ? true : false;

            $data_all_currencies = $wpdb->get_results(
                "SELECT pm2.meta_value as currency, sum(pm.meta_value) as sum_amount, IFNULL( GROUP_CONCAT( pm3.meta_value ), '' ) as group_payments
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'invoice' )
                LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_total' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_order_id' )
                LEFT JOIN {$wpdb->prefix}wpc_client_payments pay ON ( pm3.meta_value = pay.order_id AND pay.order_status = 'paid' )
                WHERE
                    p.post_type = 'wpc_invoice'
                    AND coa.assign_id = '$client_id'
                    AND `post_status` != 'refunded'
                    AND `post_status` != 'draft'
                    AND `post_status` != 'void'
                GROUP BY pm2.meta_value
                ", ARRAY_A );

            $data['balance'] = $data['total_amount'] = $data['total_payments'] = array();
            if( $data_all_currencies ) {
                foreach( $data_all_currencies as $currency ) {
                    $sum_payments = 0;
                    $group_payments = explode( ',', $currency['group_payments'] );
                    if( is_array( $group_payments ) && 0 < count( $group_payments ) ) {
                        $all_payments_temp = $wpdb->get_results( $wpdb->prepare(
                                                "SELECT id, amount
                                                FROM {$wpdb->prefix}wpc_client_payments
                                                WHERE `client_id` ='%d'
                                                    AND `order_status` = 'paid'
                                                    ", $client_id
                                            ), ARRAY_A );
                        $all_payments = array();
                        foreach( $all_payments_temp as $val ) {
                            $all_payments[ $val['id'] ] = $val['amount'];
                        }
                        foreach( $group_payments as $payments ) {
                            $payments = unserialize( $payments );
                            if( is_array( $payments ) ) {
                                foreach( $payments as $payment ) {
                                    $sum_payments += ( isset( $all_payments[ $payment ] ) ) ? $all_payments[ $payment ] : 0 ;
                                }
                            }
                        }
                    }
                    $data['balance'][] = $this->get_currency( ($currency['sum_amount'] - $sum_payments), false, $currency['currency'] ) ;
                    $data['total_amount'][] = $this->get_currency( $currency['sum_amount'], false, $currency['currency'] );
                    $data['total_payments'][] = $this->get_currency( $sum_payments, false, $currency['currency'] );
                }
            }

            return WPC()->get_template( 'invoicing_account_summary.php', 'invoicing', $data );
        }


        /*
        * Shortcode for Show list of Invoices
        */
        function shortcode_invoicing_list( $atts, $contents = null, $shortcode ) {
            global $wpdb;
            $atts = WPC()->shortcodes()->shortcode_atts( $atts, $shortcode );
            //checking access
            $client_id = WPC()->checking_page_access();

            if ( false === $client_id ) {
                return '';
            }

            //display blanck for Staff
            if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'wpc_view_invoices' ) && !current_user_can( 'administrator' ) ) {
                return '';
            }

            $data = array();
            $data['invoices'] = array();

            $data['texts'] = array(
                'pay_now' => __('Pay Now', WPC_CLIENT_TEXT_DOMAIN )
            );

            $type = 'inv';
            $is_r_est = false;

            $atts['type'] = $type_long = ( !empty( $atts['type'] ) && in_array( $atts['type'], array( 'estimate', 'request_estimate' ) ) ) ? $atts['type'] : 'invoice';

            switch( $atts['type'] ) {
                case 'estimate':
                    $type = 'est';
                    break;
                case 'request_estimate':
                    $type = 'r_est';
                    $is_r_est = true;
                    break;
            }

            //no items text
            $data['no_text'] = __("You don't have any items", WPC_CLIENT_TEXT_DOMAIN );
            if ( isset( $atts['no_text'] ) ) {
                $data['no_text'] = $atts['no_text'];
            }

            $data['hide_table_header'] = isset( $atts['hide_table_header'] ) && 'yes' == $atts['hide_table_header'] ? 'yes' : 'no';
            $data['show_pay_now'] = ( isset( $atts['pay_now_links'] ) && 'yes' == $atts['pay_now_links'] && $type == 'inv' && ( !current_user_can( 'wpc_client_staff' ) || ( current_user_can( 'wpc_client_staff' ) && current_user_can( 'wpc_paid_invoices' ) ) ) ) ? true : false;
            $data['show_date'] = ( isset( $atts['show_date'] ) && 'yes' == $atts['show_date'] ) ? true : false;
            $data['show_due_date'] = ( isset( $atts['show_due_date'] ) && 'yes' == $atts['show_due_date'] ) ? true : false;
            $data['show_description'] = ( isset( $atts['show_description'] ) && 'yes' == $atts['show_description'] && in_array( $type, array( 'est', 'inv' ) ) ) ? true : false;
            $data['show_type_payment'] = ( isset( $atts['show_type_payment'] ) && 'yes' == $atts['show_type_payment'] ) ? true : false;
            //$data['show_invoicing_currency'] for temlate for old customer
            $data['show_invoicing_currency'] = $data['show_invoicing_amount'] = ( ( isset( $atts['show_invoicing_currency'] ) && 'yes' == $atts['show_invoicing_currency'] ) || ( isset( $atts['show_invoicing_amount'] ) && 'yes' == $atts['show_invoicing_amount'] ) ) ? true : false;

            //$status = " AND p.post_status != 'void'";
            $status = '';
            if ( isset( $atts['status'] ) && in_array( strtolower( $atts['status'] ), array( 'paid', 'inprocess', 'sent', 'open', 'draft', 'partial', 'refunded', 'new', 'waiting_client', 'waiting_admin' ) ) ) {
                if( 'open' == $atts['status'] ) {
                    $status = " AND ( p.post_status = 'open' OR p.post_status = 'sent' )";
                } elseif( 'waiting_admin' == $atts['status'] ) {
                    $status = " AND ( p.post_status = 'new' OR p.post_status = 'waiting_admin' )";
                } else {
                    $status = " AND p.post_status = '{$atts['status']}' ";
                }
            }

            //get invoices
            $invoices = $wpdb->get_results( $wpdb->prepare(
                "SELECT p.ID as id,
                    p.post_title as title,
                    p.post_content as description,
                    p.post_date as date,
                    p.post_modified as date_modified,
                    p.post_status as status,
                    coa.assign_id as client_id,
                    pm3.meta_value as due_date,
                    pm2.meta_value as number
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = '%s' )
                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = '%s' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_number' )
                LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_due_date' )
                WHERE p.post_type='wpc_invoice' AND
                    p.post_status != 'declined' AND
                    p.post_status != 'accepted' AND
                    coa.assign_id = %d" . $status,
                $type,
                $type_long,
                $client_id
            ), ARRAY_A );

            if ( is_array( $invoices ) && 0 < count( $invoices ) ) {
                foreach( $invoices as $key=>$invoice ) {

                    if ( $is_r_est ) {
                        //make link
                        if ( WPC()->permalinks ) {
                            $invoices[$key]['invoicing_link'] = WPC()->get_slug( 'request_estimate_page_id' ) . $invoice['id'] ;
                        } else {
                            $invoices[$key]['invoicing_link'] = add_query_arg( array( 'wpc_page' => 'request-estimate', 'wpc_page_value' => $invoice['id'] ), WPC()->get_slug( 'invoicing_page_id', false ) );
                        }
                        $invoices[$key]['invoicing_number'] = $invoices[$key]['title'] = $invoice['title'];

                    } else {
                        //make link
                        $invoices[$key]['invoicing_link'] = $this->get_invoice_link( $invoice['id'] );
                        $invoices[$key]['invoicing_number'] = $invoice['number'];

                        $invoices[$key]['title'] = $invoice['title'];
                    }

                    //if( $data['show_date'] ) {
                        $temp_date = ( "0000-00-00 00:00:00" != $invoice['date_modified'] ) ? $invoice['date_modified'] : $invoice['date'] ;
                        //$invoices[$key]['date'] = WPC()->date_format( strtotime( $temp_date ) ) ;
                        $invoices[$key]['date'] = WPC()->date_format( strtotime( $temp_date ), 'date' );
                        $invoices[$key]['time'] = WPC()->date_format( strtotime( $temp_date ), 'time' );

                        unset( $temp_date );
                    //}

                    $invoices[$key]['due_date'] = ( !empty( $invoice['due_date'] ) ) ? WPC()->date_format( $invoice['due_date'], 'date' ) : '';

                    //added CF of ivoice for displaying on template
                    $invoices[$key] += $this->get_cf_for_inv( $invoice['id'] );

                    //if( $data['show_description'] ) {
                    if ( !$is_r_est ) {
                        if( 19 < strlen( $invoice['description'] ) ) {
                            $description = substr( $invoice['description'], 0, 16 ) . '...';
                        } else {
                            $description = $invoice['description'] ;
                        }
                        $invoices[$key]['description'] = '<span title="' . $invoice['description'] . '">' . $description . '</span>';

                        $invoices[$key]['full_description'] = $invoice['description'] ;
                    } else {
                        $invoices[$key]['full_description'] = $invoices[$key]['description'] = '';
                    }

                    $recurring_type = get_post_meta( $invoice['id'], 'wpc_inv_recurring_type', true  ) ;
                    $invoices[$key]['type_payment'] = ( $recurring_type ) ? __( 'Recurring Payment', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Deposit Payment', WPC_CLIENT_TEXT_DOMAIN );

                    $total = get_post_meta( $invoice['id'], 'wpc_inv_total', true );
                    if ( !$total ) {
                        $total = 0;
                    } else {
                        if ( $invoice['status'] != 'paid' && $invoice['status'] != 'pending' && $invoice['status'] != 'refunded'  ) {
                            $total -= $this->get_amount_paid( $invoice['id'] );
                        }
                    }

                    $selected_curr = get_post_meta( $invoice['id'], 'wpc_inv_currency', true );
                    $invoices[$key]['invoicing_currency'] = $invoices[$key]['invoicing_amount'] = $this->get_currency( $total, true, $selected_curr );

                    if ( !$is_r_est ) {
                        if ( $invoice['status'] == 'paid' ) {
                            $invoices[$key]['inv_pay_now'] = __( 'Paid', WPC_CLIENT_TEXT_DOMAIN ) ;
                        } elseif ( $invoice['status'] == 'pending' ) {
                            $invoices[$key]['inv_pay_now'] = __( 'Pending', WPC_CLIENT_TEXT_DOMAIN ) ;
                        } elseif ( $invoice['status'] == 'refunded' ) {
                            $invoices[$key]['inv_pay_now'] = __( 'Refunded', WPC_CLIENT_TEXT_DOMAIN ) ;
                        } elseif ( 0 > $total ) {
                            $invoices[$key]['inv_pay_now']  = '';
                        } else {
                            $order = $this->get_last_recent_order( $invoice['id'] );
                            if ( $order ) {
                                $invoices[$key]['inv_pay_now']  = __( 'In Process', WPC_CLIENT_TEXT_DOMAIN ) ;
                            } else {
                                $invoices[$key]['inv_pay_now_link'] = $this->get_pay_now_link( $invoice['id'] );
                            }
                        }
                    } else {
                        //$invoices[$key]['inv_pay_now']  = '';
                    }
                }
                $data['invoices'] = $invoices;
            }

            return WPC()->get_template( 'invoicing_list.php', 'invoicing', $data);
        }


        /*
        * Shortcode for Show invoices
        */
        function shortcode_invoicing( $atts, $contents = null ) {
            $client_id = WPC()->checking_page_access();

            if ( false === $client_id ) {
                return '';
            }

            //display blanck for Staff
            if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'wpc_view_invoices' ) && !current_user_can( 'administrator' ) ) {
                return '';
            }

            wp_register_style( 'wpc-ui-style', WPC()->plugin_url . 'css/jqueryui/jquery-ui-1.10.3.css' );
            wp_enqueue_style( 'wpc-ui-style' );

            //wp_enqueue_script( 'jquery-ui-slider' );
            //wp_enqueue_style( 'jquery-ui-slider' );
            wp_enqueue_script( 'jquery-ui-spinner' );
            
            /**
             * @link https://github.com/marcj/css-element-queries
             */
            wp_enqueue_script( 'resize-sensor', WPC()->plugin_url . 'js/css-element-queries-master/ResizeSensor.js', null );
            wp_enqueue_script( 'resize-queries', WPC()->plugin_url . 'js/css-element-queries-master/ElementQueries.js', ['resize-sensor'] );            
            wp_enqueue_style( 'invoicing', $this->extension_url . 'css/pages/invoicing.css' );            
            
            ob_start();
                include $this->extension_dir . 'includes/user/invoicing_view.php' ;
                $new_content = ob_get_contents();
            ob_end_clean();            
            return $new_content;
        }



    //end class
    }
}