<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( "WPC_INV_User" ) ) {

    class WPC_INV_User extends WPC_INV_User_Shortcodes {

        /**
        * constructor
        **/
        function __construct() {

            $this->inv_common_construct();
            $this->inv_shortcodes_construct();

            add_action( 'wp_enqueue_scripts', array( &$this, 'load_css' ), 100 );

            //filter posts
            add_filter( 'the_posts', array( &$this, 'filter_posts' ), 99 );

            // filter payments data
            add_filter( 'wpc_client_client_payments_history', array( $this, 'filter_payments_data' ), 10, 1 );

        }


        function load_css() {
            wp_register_style( 'wpc-inv_user-style', $this->extension_url . 'css/user.css' );
            wp_enqueue_style( 'wpc-inv_user-style' );
        }


        /*
        * Start payment steps
        */
        function start_payment_steps( $invoice_id, $client_id ) {
            global $wpc_payments_core;

            //get client INV
            $invoice_data = $this->get_data( $invoice_id ) ;

            //haven't permision or wrong INV number
            if ( !isset( $invoice_data['id'] )  ) {
                WPC()->redirect( WPC()->get_hub_link() );
            }

            $paid_total = $this->get_amount_paid( $invoice_data['id'] );
            if( $paid_total ) { $invoice_data['total'] -= $paid_total; }
            $slide_amount = ( isset( $_REQUEST['slide_amount'] ) && $_REQUEST['slide_amount'] ) ? (float)$_REQUEST['slide_amount'] : 0;

             if ( $slide_amount && $invoice_data['total'] > $slide_amount ) {
                if ( !isset( $invoice_data['min_deposit'] ) ) {
                    $invoice_data['total'] = $slide_amount ;
                } elseif ( $invoice_data['min_deposit'] <= $slide_amount ) {
                    $step = $this->get_step( $invoice_data['total'] );

                    $rest = $slide_amount - floor( $slide_amount / $step ) * $step;
                    if ( 0 == $rest || $invoice_data['min_deposit'] == $slide_amount ) {
                        $invoice_data['total'] = $slide_amount ;
                    }
                }
            }

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );


            $data = array();

            if ( isset( $wpc_invoicing['description'] ) ) {
                $data['item_name'] = $wpc_invoicing['description'] . $invoice_data['number'];
            } else {
                $data['item_name'] = $invoice_data['number'];
            }


            $data['invoice_id']     = $invoice_id;

            $payment_type = 'one_time';
            if ( isset( $invoice_data['recurring_type'] ) && true == $invoice_data['recurring_type'] ) {
                $data['profile_id'] = ( isset( $invoice_data['parrent_id'] ) ) ? $invoice_data['parrent_id'] : '';
                $data['a3']         = $invoice_data['total'];
                $data['t3']         = isset( $invoice_data['billing_period'] ) ? $invoice_data['billing_period'] : '';
                $data['p3']         = isset( $invoice_data['billing_every'] ) ? $invoice_data['billing_every'] : '';
                $data['c']          = isset( $invoice_data['billing_cycle'] ) ? $invoice_data['billing_cycle'] : '';

                $payment_type = 'recurring';
            }

            //get correct currency
            $wpc_currency = WPC()->get_settings( 'currency' );

            $currency = 'USD';
            if ( isset( $invoice_data['currency'] ) && isset( $wpc_currency[$invoice_data['currency']]['code'] ) ) {
                $currency = $wpc_currency[$invoice_data['currency']]['code'];
            }

            $args = array(
                'function' => 'invoicing',
                'client_id' => $client_id,
                'amount' => $invoice_data['total'],
                'currency' => $currency,
                'payment_type' => $payment_type,
                'data' => $data,
            );

            //create new order
            $order_id = $wpc_payments_core->create_new_order( $args );


            if ( $order_id ) {
                $order = $wpc_payments_core->get_order_by( $order_id );

                //make link
                $name = $wpc_payments_core->step_names[2];
                if ( WPC()->permalinks ) {
                    $payment_link = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] ."/$name/";
                } else {
                    $payment_link = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url() );
                }

                WPC()->redirect( $payment_link );
                exit;
            }

            die( 'error' );

        }


        /**
         * filter posts
         */
        function filter_posts( $posts ) {
            global $wp_query;

            $filtered_posts = array();

            //if empty
            if ( empty( $posts ) || is_admin() ) {
                return $posts;
            }

            $wpc_pages = WPC()->get_settings( 'pages' );

            $post_ids = array();
            foreach( $posts as $post ) {
                $post_ids[] = $post->ID;
            }
            $sticky_posts_array = array();
            if( ( isset( $wpc_pages['invoicing_page_id'] ) && in_array( $wpc_pages['invoicing_page_id'], $post_ids ) ) || ( isset( $wpc_pages['invoicing_list_page_id'] ) && in_array( $wpc_pages['invoicing_list_page_id'], $post_ids ) ) ) {
                $sticky_posts_array = get_option( 'sticky_posts' );
                if ( !is_array( $sticky_posts_array ) || 0 >= count( $sticky_posts_array ) ) {
                    $sticky_posts_array = array();
                }
            }



            //other filter
            foreach( $posts as $post ) {

                if( in_array( $post->ID, $sticky_posts_array ) ) {
                    continue;
                }

                if ( isset( $wpc_pages['invoicing_page_id'] ) && $post->ID == $wpc_pages['invoicing_page_id'] ) {

                    if ( is_user_logged_in() ) {

                        if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {

                            $wp_query->is_page      = true;
                            $wp_query->is_home      = false;
                            $wp_query->is_singular  = true;
                            $filtered_posts[] = $post;
                            continue;

                        }
                    }
                    continue;
                }

                //add all other posts
                $filtered_posts[] = $post;

            }

            return $filtered_posts;
        }


        /**
         * filter: Add Title of invoice to shortcode [wpc_client_payments_history]
         * @param $payments
         * @return mixed
         */
        function filter_payments_data( $payments ) {
            global $wpdb;

            // array of payments_ids
            $payments_ids = array();
            foreach ( $payments as $key => $payment ) {
                if( $payment['function'] == 'invoicing' ) {
                    $payments_ids[] = "a:1:{i:0;i:".$payment['id'].";}";
                }
            }

            // get inv ids list
            $inv_ids = $wpdb->get_results("
                SELECT 
                  p.post_id,
                  pn.meta_value as order_id
                FROM {$wpdb->postmeta} p
                LEFT JOIN {$wpdb->postmeta} pn ON pn.post_id = p.post_id
                WHERE p.meta_value IN('" . implode( "','", $payments_ids ) . "')
                AND pn.meta_key = 'wpc_inv_order_id'
                ", ARRAY_A);

            // sort array
            $inv_ids_list = array();
            foreach ( $inv_ids as $key => $value ) {
                $inv_ids_list[] = $value['post_id'];
            }

            // get extra data
            $extra_data = $wpdb->get_results("
                SELECT DISTINCT 
                          pn.post_title,
                          pn.post_content,
                          p.post_id,
                          pm.meta_value as order_id
                FROM      {$wpdb->postmeta} p 
                LEFT JOIN {$wpdb->posts} pn ON pn.ID = p.post_id
                LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.post_id
                WHERE     p.post_id IN('" . implode( "','", $inv_ids_list ) . "')
                AND       pm.meta_key = 'wpc_inv_order_id'
                ", ARRAY_A);

            // sort array
            $extra_data_list = array();
            foreach ( $extra_data as $key => $value ) {
                if( $value['order_id'] ) {
                    $order_id = unserialize( $value['order_id'] );
                    $extra_data_list[$order_id[0]]['order_id'] = $order_id[0];
                    $extra_data_list[$order_id[0]]['post_title'] = $value['post_title'];
                    $extra_data_list[$order_id[0]]['post_content'] = $value['post_content'];
                    $extra_data_list[$order_id[0]]['post_id'] = $value['post_id'];
                }
            }

            // add extra data to $payment array
            $payments_list = array();
            foreach ( $payments as $key => $value ) {
                $payments_list[] = $value;
                if( $value['id'] == $extra_data_list[$value['id']]['order_id'] ) {
                    $payments_list[$key]['description'] = $extra_data_list[$value['id']]['post_title'];
                }
            }

            return $payments = $payments_list;
        }

    //end class
    }

}