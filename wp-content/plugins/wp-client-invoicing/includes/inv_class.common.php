<?php
//{{FUNC_NOT_ENC:get_pdf}}

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Dompdf\Dompdf;
use Dompdf\Options;

if ( !class_exists( "WPC_INV_Common" ) ) {

    class WPC_INV_Common {

        var $extension_dir;
        var $extension_url;
        var $rate_capacity;
        var $thousands_separator;

        /**
        * constructor
        **/
        function inv_common_construct() {

            global $wp_version;

            $this->extension_dir = WPC()->extensions()->get_dir( 'inv' );
            $this->extension_url = WPC()->extensions()->get_url( 'inv' );

            add_action( 'init', array( &$this, 'pdf_downloader' ) );
            add_action( 'init', array( &$this, '_create_post_type' ) );

            add_action( 'wpc_client_inv_send_reminder', array( &$this, 'send_reminder' ) );

            //add in array excluded post types
            add_filter( 'wpc_added_excluded_post_types', array( &$this, 'added_excluded_pt' ) );

            //add rewrite rules
            add_filter( 'rewrite_rules_array', array( &$this, '_insert_rewrite_rules' ) );

            //permission for delete currency
            add_filter( 'wpc_currency_permission', array( &$this, 'currency_permission' ) );


            //get continue link for payment
            add_filter( 'wpc_payment_get_continue_link_invoicing', array( &$this, 'get_continue_link' ), 99, 3 );

            //get active payment gateways
            add_filter( 'wpc_payment_get_activate_gateways_invoicing', array( &$this, 'get_active_payment_gateways' ), 99 );

            add_action( 'wpc_client_payment_subscription_canceled_invoicing', array( &$this, 'subscription_canceled' ) );
            add_action( 'wpc_client_payment_paid_invoicing', array( &$this, 'order_paid' ) );
            add_action( 'wpc_client_payment_subscription_payment_invoicing', array( &$this, 'order_subscription' ) );
            add_action( 'wpc_client_payment_subscription_expired_invoicing', array( &$this, 'subscription_expired' ) );

            add_action( 'wpc_client_payment_subscription_start_invoicing', array( &$this, 'order_subscription_start' ) );


            add_action( 'wpc_change_status_expired', array( &$this, 'change_status_expired' ) );

            add_action( 'wpc_invoice_refund', array( &$this, 'invoice_refund' ) );

            add_action( 'wpc_invoice_cron', array( &$this, 'wpc_invoice_cron' ) );

            //add placeholders
            add_filter( 'wpc_client_replace_placeholders', array( &$this, '_replace_placeholders' ), 10, 3 );
            add_filter( 'wpc_shortcode_data_array', array( &$this, 'shortcode_data_array' ) );


            //check expire recurring profile
            add_filter( 'wpc_client_check_expire_subscription_invoicing', array( &$this, 'check_expire_profile' ), 10, 2 );

            add_filter( 'wpc_client_dashboard_view_all_link', array( &$this, 'dashboard_view_all_link' ), 10, 2 );

            //for replace '$invoice.wpc_inv_cf_' instead '$wpc_inv_cf_' in wpc_client_invoicing_list template
            add_filter( 'wpc_template_filter_wpc_client_invoicing_list', array( &$this, 'replace_template' ) );

            add_filter( 'wpc_extend_php_templates', array( $this, 'add_template_dir' ) );

            add_filter( 'wpc_all_crons', array( &$this, 'add_inv_crons' ), 10, 1 );

            // add action to Client tab
            add_filter( 'wpc_client_more_actions_clients', array( &$this, 'filter_add_client_more_actions' ), 10, 2 );

            // WP REST API
            if ( version_compare( $wp_version, '4.4', '>=' ) ) {
                if( function_exists('register_rest_route') ) {
                    add_action( 'rest_api_init', array( &$this, 'rest_api_cancel_reminder' ) );
                }else {
                    error_log('Function "register_rest_route" is not available. Upgrade your WP to use extra features');
                }
            }else {
                error_log('Your version is less then WP 4.4. Please, upgrade your WP to use extra features.');
            }
        }

        function add_inv_crons( $all_crons = array() ) {
            $all_crons['wpc_client_inv_send_reminder'] = array( 'period' => 'twicedaily' );
            $all_crons['wpc_invoice_cron'] = array( 'period' => 'hourly');

            return $all_crons;
        }


        function add_template_dir( $templates ) {

            $templates['invoicing'] = $this->extension_dir . 'templates';

            return $templates;
        }


        function get_html_for_custom_field( $data, $attrs = array() ) {
            $class = !empty( $attrs['class'] ) ? ' class="' . $attrs['class'] . '"' : '';
            $name = !empty( $attrs['name'] ) ? ' name="' . $attrs['name'] . '"' : '';
            $id = !empty( $attrs['id'] ) ? ' id="' . $attrs['id'] . '"' : '';
            $readonly = disabled( !empty( $data['field_readonly'] ), true, false );

            if ( isset( $attrs['value'] ) ) {
                $value = $attrs['value'];
            } else {
                $value = !empty( $data['default_value'] ) ? $data['default_value'] : '';
            }

            $type = !empty( $data['type'] ) && in_array( $data['type'], array( 'textarea', 'selectbox', 'checkbox' ) )
                    ? $data['type'] : 'text';

            $params = $id . $class . $name;

            $html = '';
            if ( $readonly ) {
                $html .= '<div style="position: relative;">';
                $html .= '<div style="background: #fff; opacity: 0.5; position: absolute; top: 0; bottom: 0; left: 0; right: 0;"></div>';
            }

            switch( $type ) {

                case 'textarea':
                    $html .= '<textarea' . $params . ' maxlength="300" style="height: 26px;">' . $value . '</textarea>' ;
                    break;

                case 'selectbox':
                    $html .= '<select' . $params . '>';
                    if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
                        if ( !empty( $value ) ) {
                            $selected_opt = $value;
                        } elseif ( isset( $data['default_option'] ) ) {
                            $selected_opt = $data['default_option'];
                        } else {
                            $selected_opt = '';
                        }
                        foreach( $data['options'] as $key => $option ) {
                            $selected = selected( $key, $selected_opt, false ) ;
                            $html .= '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>' ;
                        }
                    }
                    $html .= '</select>' ;
                    break;

                case 'checkbox':
                    $html .= '<input type="' . $type . '"' . $params . ' value="1" ' . checked( $value, true, false ) . ' />';
                    break;

                case 'text':
                default:
                    $html .= '<input type="text"' . $params . ' value="' . $value . '" />';
                    break;
            }

            if ( $readonly ) {
                $html .= '</div>';
            }

            return $html;
        }


        /**
         * Replace '$invoice.wpc_inv_cf_' instead '$wpc_inv_cf_' in wpc_client_invoicing_list template
         *
         * @param string $template
         * @return string
         */
        function replace_template( $template ) {
            $template = str_replace( '$wpc_inv_cf_', '$invoice.wpc_inv_cf_',  $template);

            return $template;
        }


        /**
         * Get last recent order for invoice for mark it as "In Process"
         *
         * @global object $wpdb
         * @param int $inv_id
         * @return int/string
         */
        function get_last_recent_order( $inv_id ) {
            global $wpdb;

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );
            $lock_invoice = ( isset( $wpc_invoicing['lock_invoice'] ) && 'yes' == $wpc_invoicing['lock_invoice'] ) ? true : false ;
            $time_lock = ( isset( $wpc_invoicing['time_lock'] ) ) ? 60*(int)$wpc_invoicing['time_lock'] : 0;

            if ( $lock_invoice && $time_lock ) {
                $order_id = $wpdb->get_var("SELECT `id` "
                    . "FROM {$wpdb->prefix}wpc_client_payments "
                    . "WHERE ( ISNULL(`order_status`) OR `order_status` != 'paid' ) "
                        . "AND `time_created` > ( UNIX_TIMESTAMP() - {$time_lock} ) "
                        . "AND `data` LIKE '%\"invoice_id\":\"{$inv_id}\"%' "
                    . "ORDER BY `id` DESC LIMIT 1");
            } else {
                $order_id = '';
            }

            return $order_id;
        }


        /**
         * Check expire recurring profile for the client
         *
         * @param boolean $expire_profile
         * @param int $profile_id
         * @param string $plan_id
         * @return boolean
         */
        function check_expire_profile( $expire_profile, $order ) {
            if ( empty( $order['data'] ) ) {
                return '';
            }

            if ( !empty( $expire_profile ) && ( $profile_id = intval( $expire_profile ) ) ) {
                // ONE EQUAL SING IT'S MUST BE SO !!!!!!!!!!!!!!!!!!!!!!!!!!
            } else {
                $order_data = json_decode($order['data'], true);
                $profile_id = isset( $order_data['profile_id'] ) ? $order_data['profile_id'] : '';
            }

            $transaction_id = isset( $order['transaction_id'] ) ? $order['transaction_id'] : 0;
            $payment_method = isset( $order['payment_method'] ) ? $order['payment_method'] : '';

            if ( !$profile_id || !$transaction_id || !$payment_method ) {
                return '';
            }

            global $wpdb;

            $cycles = intval( get_post_meta( $profile_id, 'wpc_inv_billing_cycle', true ) );
            $cycles_now = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) "
                . "FROM {$wpdb->prefix}wpc_client_payments "
                . "WHERE `transaction_id`='%s' AND `payment_method`='%s'"
                , $transaction_id, $payment_method ));

            return ( !empty( $cycles ) && $cycles_now >= $cycles ) ? true : false;
        }


        function dashboard_view_all_link( $view_all_link, $widget_id ) {

            if( $widget_id == 'wpc_inv_statistic_dashboard_widget' ) {
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_invoicing' ), get_admin_url() . 'admin.php' );
            }

            return $view_all_link;
        }


        function shortcode_data_array( $array ) {
            $array['wpc_client_inv_invoicing_account_summary'] = array(
                'title'         => __( 'Invoicing: Invoicing Account Summary', WPC_CLIENT_TEXT_DOMAIN ),
                'callback'      => array( &$this, 'shortcode_invoicing_account_summary' ),
                'categories'    => 'other',
                'attributes'    => array(
                    'show_total_amount' => array(
                        'label'  => __( 'Show Total Amount', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'yes'
                    ),
                    'show_total_payments' => array(
                        'label'  => __( 'Show Total Payments', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'yes'
                    ),
                    'show_balance' => array(
                        'label'  => __( 'Show Balance', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'yes'
                    )
                )
            );
            $array['wpc_client_invoicing_list'] = array(
                'title'         => __( 'Invoicing: Invoice List', WPC_CLIENT_TEXT_DOMAIN ),
                'name'         => 'invoicing_list',
                'callback'      => array( &$this, 'shortcode_invoicing_list' ),
                'categories'    => 'content',
                'hub_template' => array(
                    'text'    => __( 'Invoicing List', WPC_CLIENT_TEXT_DOMAIN ),
                ),
                'attributes'    => array(
                    'type' => array(
                        'label'  => __( 'Type', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'invoice' => __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                            'estimate' => __( 'Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                            'request_estimate' => __( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'invoice'
                    ),
                    'pay_now_links' => array(
                        'label'  => __( 'Pay Now Links', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'parent_name'  => 'type',
                        'parent_value' => 'invoice',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'show_date' => array(
                        'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'show_due_date' => array(
                        'label'  => __( 'Show Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'parent_name'  => 'type',
                        'parent_value' => array('invoice', 'estimate'),
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'show_description' => array(
                        'label'  => __( 'Show Description', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'parent_name'  => 'type',
                        'parent_value' => array('invoice', 'estimate'),
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'show_type_payment' => array(
                        'label'  => __( 'Show Payment Type', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'show_invoicing_currency' => array(
                        'label'  => __( 'Show Invoicing Currency', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'show_invoicing_amount' => array(
                        'label'  => __( 'Show Invoicing Amount', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'no'
                    ),
                    'status' => array(
                        'label'  => __( 'Invoice Status', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'null' => '',
                            'paid' => __( 'Paid', WPC_CLIENT_TEXT_DOMAIN ),
                            'inprocess' => __( 'In Process', WPC_CLIENT_TEXT_DOMAIN ),
                            'sent' => __( 'Sent', WPC_CLIENT_TEXT_DOMAIN ),
                            'open' => __( 'Open', WPC_CLIENT_TEXT_DOMAIN ),
                            'draft' => __( 'Draft', WPC_CLIENT_TEXT_DOMAIN ),
                            'partial' => __( 'Partial', WPC_CLIENT_TEXT_DOMAIN ),
                            'refunded' => __( 'Refunded', WPC_CLIENT_TEXT_DOMAIN ),
                            'new' => __( 'New', WPC_CLIENT_TEXT_DOMAIN ),
                            'waiting_client' => __( 'Waiting on Client', WPC_CLIENT_TEXT_DOMAIN ),
                            'waiting_admin' => __( 'Waiting on Admin', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => ''
                    ),
                    'hide_table_header' => array(
                        'label'  => __( 'Hide Table Header', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'parent_name'  => 'type',
                        'parent_value' => 'invoice',
                        'values' => array(
                            'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'yes',
                        'description'   => 'Ability to hide names of the columns'
                    ),
                )
            );
            $array['wpc_client_invoicing'] = array(
                'callback'      => array( &$this, 'shortcode_invoicing' ),
            );
            $array['wpc_client_inv_request_estimate'] = array(
                'callback'      => array( &$this, 'shortcode_request_estimate' ),
            );
            return $array;
        }


        function shortcode_request_estimate( $atts, $contents = null ) {
            global $wpdb;

            $client_id = WPC()->checking_page_access();
            if ( false === $client_id ) {
                WPC()->redirect( add_query_arg( array(
                    'wpc_to_redirect' => WPC()->get_current_url()
                ), WPC()->get_login_url() ) );
                exit;
            }

            if( isset( $_POST ) && count( $_POST ) ) {
                global $wp_query;
                $data = isset( $_POST['wpc_data'] ) ? $_POST['wpc_data'] : array();

                if ( !empty( $wp_query->query_vars['page'] ) ) {
                    $data['id'] = $wp_query->query_vars['page'];
                }

                if ( 'accept' == $data['action'] ) {
                    $id = ( !empty( $data['id'] ) ) ? $data['id'] : 0;
                    $message = $this->convert_request_estimate( $id, 'accept' );
                } else {
                    $return = $this->save_request_estimate( $data, 'client' );
                }

                // The Current URL
                $schema = is_ssl() ? 'https://' : 'http://';
                $url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                if ( !empty( $return['id'] ) && empty( $wp_query->query_vars['page'] ) ) {
                    $url .= $return['id'] . '/';
                }

                if ( !isset( $message ) ) {
                    $message = ( !empty( $return['msg'] ) ) ? $return['msg'] : '';
                }

                WPC()->redirect( add_query_arg( array( 'message' => $message ), $url ) );
                exit;
            } else {
                add_filter( 'comments_open', '__return_false' , 99 );
                add_filter( 'comments_close_text', '__return_empty_string' , 99 );
                add_filter( 'comments_array', '__return_empty_array' , 99 );

                wp_enqueue_script( 'jquery-ui-spinner' );

                wp_register_style( 'wpc_inv_request_estimate_style', $this->extension_url . 'css/pages/request_estimate.css', array(), WPC_INV_VER );
                wp_enqueue_style( 'wpc_inv_request_estimate_style', false, array(), WPC_INV_VER );

                ob_start();
                include_once( $this->extension_dir . 'includes/user/request_estimate.php' );
                $content = ob_get_contents();
                ob_end_clean();
                return $content;
            }
        }


        /**
         * convert Request Estimate to INT or EST
         *
         * @param int $id
         * @param string $action [convert,accept]
         * @param string $convert_to [inv,est]
         * @return string $message
         */
        function convert_request_estimate( $id, $action = 'convert', $convert_to = '' ) {

            $id = (int)$id;
            if ( !$id ) {
                return 'ae';
            }

            $data = $this->get_data( $id );

            if ( empty( $data ) ) {
                return 'ae';
            }

            $old_status = ( !empty( $data['status'] ) ) ? $data['status'] : get_post_status( $id ) ;
            if ( 'convert' == $action && !in_array( $old_status, array( 'new', 'waiting_admin' ) ) ||
                'accept' == $action && $old_status != 'waiting_client' ) {
                return 'ae';
            }

            global $wpdb;

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );
            $new_date = date( "Y-m-d H:i:s" );

            if ( !empty( $convert_to ) && in_array( $convert_to, array( 'inv', 'est' ) ) ) {
                $to_object = $convert_to;
            } else {
                $to_object = ( isset( $wpc_invoicing['rest_convert_to'] ) && 'inv' == $wpc_invoicing['rest_convert_to'] )
                    ? 'inv' : 'est';
            }

            $is_r_est = get_post_meta( $id, 'wpc_inv_post_type', true ) ;

            if ( 'r_est' != $is_r_est ) {
                return 'ae';
            }

            if ( 'inv' == $to_object ) {
                $full_type = 'invoice';
                $new_number = $this->get_next_number();
            } else {
                $full_type = 'estimate';
                $new_number = $this->get_next_number( true, 'est' );
            }

            update_post_meta( $id, 'wpc_inv_number', $new_number ) ;

            //change status
            update_post_meta( $id, 'wpc_inv_post_type', $to_object ) ;
            $wpdb->update( $wpdb->posts, array( 'post_date' => $new_date, 'post_status' => 'open' ), array( 'ID' => $id ) );
            $wpdb->update( $wpdb->prefix . 'wpc_client_objects_assigns', array( 'object_type' => $full_type ), array( 'object_type' => 'request_estimate', 'object_id' => $id ) );

            if ( !empty( $wpc_invoicing['ter_con'] ) ) {
                update_post_meta( $id, 'wpc_inv_terms', $wpc_invoicing['ter_con'] );
            }

            if ( !empty( $wpc_invoicing['not_cus'] ) ) {
                update_post_meta( $id, 'wpc_inv_note', $wpc_invoicing['not_cus'] );
            }

            $client_id = ( !empty( $data['client_id'] ) ) ? $data['client_id'] : 0 ;
            $userdata = get_userdata( $client_id );

            if ( $userdata ) {
                $args = array(
                    'invoice_id' => $id,
                    'to_object' => ( 'inv' == $to_object ) ? __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                    'client_id' => $client_id,
                    'inv_number' => $new_number,
                    'invoicing_title' => $data['title'],
                    'total_amount' => isset( $data['total'] ) ? $data['total'] : '',
                    'due_date' => isset( $data['due_date'] ) ? WPC()->date_format( $data['due_date'], 'date' ) : '',
                    'minimum_payment' => isset( $data['min_deposit'] ) ? $data['min_deposit']: '',
                );

                if ( 'convert' == $action ) {
                    $client_email = $userdata->get( 'user_email' );
                    WPC()->mail( 'convert_r_est_to', $client_email, $args, 'invoice_notify' );
                    if ( 'inv' == $to_object ) {
                        $this->send_invoice( $id );
                    } else {
                        $this->send_estimate( $id );
                    }
                } elseif ( 'accept' == $action ) {
                    $emails_array = $this->get_emails_of_admins();

                    foreach( $emails_array as $to_email ) {
                        WPC()->mail( 'accept_r_est', $to_email, $args, 'invoice_notify_admin' );
                    }
                }
            }

            return 'con';
        }


        /**
         * save data of Request Estimate
         */
        function save_request_estimate( $data, $for = 'admin' ) {
            global $wpdb;

            $action = !empty( $data['action'] ) ? $data['action'] : 'request';
            $id = !empty( $data['id'] ) ? $data['id'] : '';
            $date = date( "Y-m-d H:i:s" );
            $status = 'waiting_admin';
            $old_status = ( $id ) ? get_post_status( $id ) : false;
            $new_comment = ( !empty( $data['wpc_inv_message'] ) ) ? substr( $data['wpc_inv_message'], 0, 255 ) : '';

            if ( 'accept' == $action ) {
                if ( !$old_status || 'waiting_client' != $old_status ) {
                    return array( 'msg' => 'ae' );
                } else {
                    $this->convert_to_inv( $id, 'r_accept' );
                    exit;
                }
            } elseif ( 'comment' == $action ) {
                if ( !$id ) {
                    return array( 'msg' => 'ae' );
                } elseif( empty( $data['wpc_inv_message'] ) ) {
                    return array( 'msg' => 'ce' );
                } else {
                    $inv_notes = get_post_meta( $id, 'wpc_inv_notes', true );
                    $new_note = array(
                        'user_id'   => get_current_user_id(),
                        'message'   => $new_comment,
                        'date'      => time()
                    );
                    $inv_notes[] = $new_note;
                    update_post_meta( $id, 'wpc_inv_notes', $inv_notes );

                    $wpdb->update(
                        $wpdb->posts,
                        array( 'post_modified' => $date, 'post_status' => $status), array( 'ID' => $id ),
                        array( '%s', '%s'), array( '%d' )
                    );
                    return array( 'msg' => 'cs' );
                }
            }

            if ( empty( $data['title'] ) && !$id ) {
                return array( 'msg' => 'te' );
            }

            if ( 'request' === $action && 'admin' !== $for ) {
                $wpc_invoicing = WPC()->get_settings( 'invoicing' );

                if ( ( !isset( $wpc_invoicing['items_required'] ) || 'no' !== $wpc_invoicing['items_required'] )
                        && is_array( $data['items'] ) && 2 > count( $data['items'] ) ) {
                    return array( 'msg' => 'ir' );
                }
            }

            if ( ( $old_status && 'waiting_client' != $old_status ) &&
                ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
                && !current_user_can( 'wpc_manager' ) )) {
                return array( 'msg' => 'ae' );
            }


            $options = $new_message = array();
            $new_message['user_id'] = get_current_user_id();

            if ( !empty( $data['id'] ) && $id = $data['id'] ) {
                //if update
                if ( !empty( $data['wpc_sender'] ) && 'admin' == $data['wpc_sender'] ) {
                    $status = 'waiting_client';
                }

                $options['notes'] = get_post_meta( $id, 'wpc_inv_notes', true );

                $wpdb->update(
                    $wpdb->posts,
                    array( 'post_modified' => $date, 'post_status' => $status), array( 'ID' => $id ),
                    array( '%s', '%s'), array( '%d' )
                );

            } else {
                //if create new
                $status = 'new';

                $new_post = array(
                    'post_title'       => $data['title'],
                    'post_status'      => $status,
                    'post_type'        => 'wpc_invoice',
                    'post_date'        => $date,
                );

                $id = wp_insert_post( $new_post );

                $options['currency'] = ( !empty( $data['currency'] ) ) ? $data['currency'] : WPC()->get_default_currency();

                $options['notes'] = array();

                update_post_meta( $id, 'wpc_inv_post_type', 'r_est' );

                $client_id = get_current_user_id();
                $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns SET
                    object_type     = 'request_estimate',
                    object_id       = '%d',
                    assign_type     = 'client',
                    assign_id       = '%d'
                    ",
                    $id,
                    $client_id
                ));

                $args = array(
                    'invoice_id' => $id,
                    'client_id' => $client_id,
                    'invoicing_title' => $data['title'],
                    'total_amount' => isset( $data['total'] ) ? $data['total'] : '',
                );

                $emails_array = $this->get_emails_of_admins();

                $client_managers = WPC()->members()->get_client_managers( $client_id );
                if( count( $client_managers ) ) {
                    $managers = get_users( array(
                        'include' => $client_managers,
                        'fields'    => array( 'ID', 'user_email' )
                    ) );

                    if( is_array( $managers ) && 0 < count( $managers ) ) {
                        foreach( $managers as $manager ) {
                            if( user_can( $manager->ID, 'wpc_estimate_requests' ) )
                                $emails_array[] = $manager->user_email;
                        }
                    }
                }

                foreach( $emails_array as $to_email ) {
                    WPC()->mail( 'create_r_est', $to_email, $args, 'invoice_notify_admin' );
                }

            }


            if ( !empty( $data['wpc_inv_message'] ) ) {
                $new_message['message'] = $new_comment;
                $new_message['date'] = time();
                $options['notes'][] = $new_message;
            }

            $rate_capacity = $this->get_rate_capacity();

            //get items
            $items = $temp_item = array();
            $sub_total = 0;

            if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
                array_shift( $data['items'] );
                $new_items = array();
                if ( 'client' === $for ) {
                    $request_items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_invoicing_items WHERE use_r_est = 1", ARRAY_A );

                    $cool_items = array();
                    foreach( $request_items as $item ) {
                        $cool_items[ array_shift( $item ) ] = $item;
                    }
                    $old_data = $this->get_data($id, 'r_est');
                    $old_items = (!empty($old_data['items'])) ? unserialize($old_data['items']) : array();

                    foreach( $data['items'] as $item ) {
                        if ( !empty($item['item_id']) && 0 === strpos($item['item_id'], 'old_') ) {
                            $item_id = (int) substr( $item['item_id'], 4 ) - 1;
                            if (!empty($old_items[ $item_id ])) {
                                $temp = $old_items[ $item_id ];
                                unset($old_items[ $item_id ]);
                                $temp['quantity'] = (!empty($item['quantity'])) ? $item['quantity'] : 0;
                                $new_items[] = $temp;
                            }
                        } elseif ( !empty($item['item_id']) && $item_id = (int)$item['item_id'] ) {
                            if (!empty($cool_items[ $item_id ])) {
                                $temp = $cool_items[ $item_id ];
                                $temp['price'] = (!empty($temp['rate'])) ? $temp['rate'] : 0;
                                $temp['quantity'] = (!empty($item['quantity'])) ? $item['quantity'] : 0;
                                $new_items[] = $temp;
                            }
                        }
                    }

                    if ( count( $old_items ) ) {
                        foreach ($old_items as $v ) {
                            $v['deleted'] = true;
                            $new_items[] = $v;
                        }
                    }
                } else {
                    $new_items = $data['items'];
                }

                foreach( $new_items as $item ) {
                    if ( isset( $item['price'] ) && isset( $item['quantity'] ) ) {
                        $temp_item = $item;
                        $temp_item['quantity'] = ( is_numeric( $item['quantity'] ) && 0 < $item['quantity'] ) ? $item['quantity'] : '1';
                        $temp_item['price'] = '' . round( (float)$item['price'], $rate_capacity ) ;
                        if(empty($item['deleted'])) {
                            $sub_total += $temp_item['price'] * $temp_item['quantity'];
                        }
                        $items[] = $temp_item;
                    }
                }
            }

            $total_discount = 0;
            if ( 'client' === $for ) {
                $data['discounts'] = (isset($old_data['discounts'])) ? unserialize($old_data['discounts']) : array();
            }
            if ( isset( $data['discounts'] ) && is_array( $data['discounts'] ) ) {
                foreach ( $data['discounts'] as $disc ) {
                    if( isset( $disc['total'] ) && isset( $disc['type'] ) && isset( $disc['rate'] ) &&  0 < (float)$disc['total'] ) {
                        $discont_amount = ( 'amount' == $disc['type'] ) ? $disc['rate'] : round( $sub_total * $disc['rate'] / 100 , $rate_capacity );
                        $total_discount += $discont_amount;
                    }
                }
            } else {
                $data['discounts'] = array();
            }

            $total_tax = 0;
            if ( 'client' === $for ) {
                $data['taxes'] = (isset($old_data['taxes'])) ? unserialize($old_data['taxes']) : array();
            }
            if ( isset( $data['taxes'] ) && is_array( $data['taxes'] ) ) {
                foreach ( $data['taxes'] as $tax ) {
                    if( isset( $tax['total'] ) && isset( $tax['rate'] ) && isset( $tax['type'] ) &&  0 < (float)$tax['total'] ) {
                        $tax_amount = ( 'before' == $tax['type'] ) ? round( $sub_total * $tax['rate'] / 100 , $rate_capacity ) : round( ( $sub_total - $total_discount ) * $tax['rate'] / 100 , $rate_capacity );
                        $total_tax += $tax_amount;
                    }
                }
            } else {
                $data['taxes'] = array();
            }
            $options['taxes'] = $data['taxes'];

            $total_items = $sub_total - $total_discount + $total_tax ;

            $options['items'] = $items;
            $options['discounts'] = $data['discounts'];
            $options['taxes'] = $data['taxes'];
            $options['total'] = round( $total_items, $rate_capacity );
            $options['sub_total'] = round( $sub_total, $rate_capacity );
            $options['total_discount'] = round( $total_discount, $rate_capacity );
            $options['total_tax'] = round( $total_tax, $rate_capacity );

            if ( !empty( $data['currency'] ) ) {
                $options['currency'] =  $data['currency'];
            }

            foreach( $options as $key => $option ) {
                update_post_meta( $id, 'wpc_inv_' . $key, $option);
            }

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );
            if ( 'yes' == $wpc_invoicing['est_auto_convert'] ) {
                $this->convert_request_estimate( $id, 'convert', $wpc_invoicing['rest_convert_to'] );
            }

            return array( 'msg' => 'rs', 'id' => $id );
        }


        /**
         * Get table request notes for Request Estimate
         *
         * @param array $notes
         * @param string $for
         * @param boolean $disable_textarea
         * @return string $html
         */
        function get_table_request_notes( $notes, $for = 'admin', $disable_textarea = true, $inv_id ) {
            ob_start();
                foreach( $notes as $val ) {
                    $username = '';
                    $avatar = WPC()->members()->user_avatar( '', true );
                    if( isset( $val['user_id'] ) ) {
                        $user_info = get_userdata( $val['user_id'] );
                        $username = ( get_current_user_id() == $val['user_id'] ) ? __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) : $user_info->data->user_login;
                        $avatar = WPC()->members()->user_avatar( $val['user_id'] );
                    } elseif( isset( $val['who'] ) ) {
                        if( 'client' == $val['who'] ) {
                            $user_id = WPC()->assigns()->get_assign_data_by_object( 'request_estimate', $inv_id, 'client' );
                            if( isset( $user_id[0] ) ) {
                                $user_info = get_userdata( $user_id[0] );
                                $username = ( get_current_user_id() == $user_id[0] ) ? __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) : $user_info->data->user_login;
                                $avatar = WPC()->members()->user_avatar( $user_id[0] );
                            }
                        } else {
                            $username = ( $for == $val['who'] ) ? __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) : $val['who'];
                        }
                    }

                    $mess = ( isset( $val['message'] ) ) ? nl2br( htmlspecialchars( $val['message'] ) ) : '';
                    $date = ( isset( $val['date'] ) ) ? WPC()->date_format( $val['date'] ) : ''; ?>

                    <div class="wpc_inv_comment_line">
                        <div class="wpc_inv_comment_avatar">
                            <?php echo $avatar ?>
                        </div>
                        <div class="wpc_inv_comment_line_content">
                            <div class="wpc_inv_author_date">
                                <div class="wpc_inv_comment_author"><?php echo $username ?></div>
                                <div class="wpc_inv_comment_date"><?php echo $date ?></div>
                            </div>
                            <div class="wpc_inv_comment_content"><?php echo $mess ?></div>
                        </div>
                    </div>
                <?php } ?>

                <div class="wpc_inv_new_comment">
                    <div class="wpc_inv_comment_avatar">
                        <?php echo WPC()->members()->user_avatar( get_current_user_id() ) ?>
                    </div>
                    <div class="wpc_inv_new_comment_field">
                        <textarea name="wpc_data[wpc_inv_message]" id="wpc_inv_message" style="width: 100%;" rows="5" placeholder="<?php _e( 'New comment', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea>
                    </div>
                </div>

                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery('#wpc_inv_message').keyup( function() {
                            var limit = 255;
                            var wpc_inv_message = jQuery( this ).val();
                            //check if there are more characters then allowed
                            if ( wpc_inv_message.length > limit ) {
                                //and if there are use substr to get the text before the limit
                                wpc_inv_message = wpc_inv_message.substr( 0, limit );
                                jQuery( this ).val( wpc_inv_message );
                            }
                        });
                    });
                </script>

                <style type="text/css">
                    .wpc_inv_comment_line {
                        float:left;
                        width:100%;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                        margin: 0;
                        padding: 5px;
                        border-top:1px solid rgba( 204,204,204,0.4 );
                        background: rgba( 255, 255, 255, 0.8 );
                    }

                    .wpc_inv_comment_avatar {
                        float:left;
                        margin:0;
                        padding:0;
                        width:50px;
                        font-size:50px;
                        height:50px;
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                    }

                    .wpc_inv_comment_line_content {
                        float:left;
                        margin:0;
                        padding:0 0 0 10px;
                        width: calc( 100% - 50px );
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                    }

                    .wpc_inv_author_date {
                        float:left;
                        width:100%;
                        margin:0;
                        padding:0;
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                    }

                    .wpc_inv_comment_author {
                        float:left;
                        width:calc( 100% - 200px );
                        margin:0;
                        padding:0;
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                        font-weight: bold;
                    }

                    .wpc_inv_comment_date {
                        float:left;
                        width: 200px;
                        margin:0;
                        padding:0;
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                        text-align: right;
                    }

                    .wpc_inv_comment_content {
                        float:left;
                        width:100%;
                        margin:10px 0 0 0;
                        padding:0;
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                        opacity:0.9;
                        word-wrap: break-word;
                    }

                    .wpc_inv_new_comment {
                        float:left;
                        width:100%;
                        margin:0;
                        padding:30px 0 0 5px;
                        border-top:1px solid rgba( 204,204,204,0.4 );
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                    }

                    .wpc_inv_new_comment_field {
                        float:left;
                        margin:0;
                        padding:0 0 0 10px;
                        width: calc( 100% - 50px );
                        border:none;
                        box-sizing:border-box;
                        -webkit-box-sizing:border-box;
                        -moz-box-sizing:border-box;
                    }
                </style>
            <?php $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            return $content;
        }


        /**
        * add placeholder to array placegolders in wpc_core
        *
        * @param array $ph_data
        * @param array $args
        * @param string $label
        */
        function _replace_placeholders( $ph_data, $args, $label ) {

            if ( '' != $label ) {
                switch( $label ) {
                    case 'invoice_thank_you':
                        $ph_data['{invoice_amount}']         = ( isset( $args['invoice_amount'] ) ) ? $args['invoice_amount'] : '';
                        $ph_data['{invoice_date}']         = ( isset( $args['invoice_date'] ) ) ? $args['invoice_date'] : '';
                        break;
                    case 'invoice_notify_admin':
                    case 'invoice_notify':
                    case 'estimate_notify':
                    case 'invoice_reminder':
                    case 'invoicing_inv_template':
                    case 'invoicing_list.php':
                    case 'invoice.php':
                    case 'invoice_page.php':

                        $ph_data['{invoice_cancel_reminder_url}'] = empty( $args['invoice_cancel_reminder_url'] ) ? $this->get_cancel_reminder_url( $args['invoice_id'] ) : '';

                        //invoicing
                        $ph_data['{invoice_number}']    = !empty( $args['inv_number'] ) ? $args['inv_number'] : '';
                        $ph_data['{invoicing_title}']   = !empty( $args['invoicing_title'] ) ? $args['invoicing_title'] : '';
                        $ph_data['{estimate_number}']   = !empty( $args['inv_number'] ) ? $args['inv_number'] : '';
                        $ph_data['{accept_note}']       = !empty( $args['accept_note'] ) ? $args['accept_note'] : __( 'No comments', WPC_CLIENT_TEXT_DOMAIN );
                        $ph_data['{decline_note}']      = !empty( $args['decline_note'] ) ? $args['decline_note'] : __( ' without reason', WPC_CLIENT_TEXT_DOMAIN );
                        $ph_data['{to_object}']         = !empty( $args['to_object'] ) ? $args['to_object'] : '';

                        $ph_data['{total_amount}']      = isset( $args['total_amount'] ) ? $args['total_amount'] : '';
                        $ph_data['{due_date}']          = !empty( $args['due_date'] ) ? $args['due_date'] : '';
                        $ph_data['{minimum_payment}']   = !empty( $args['minimum_payment'] ) ? $args['minimum_payment'] : '';
                        $ph_data['{payment_method}']    = !empty( $args['payment_method'] ) ? $args['payment_method'] : '';
                        $ph_data['{invoice_pay_link}']  = !empty( $args['invoice_id'] ) ? $this->get_pay_now_link( $args['invoice_id'] ) : '';
                        $ph_data['{invoice_link}']  = !empty( $args['invoice_id'] ) ? $this->get_invoice_link( $args['invoice_id'] ) : '';
                        break;
                }

                if ( 0 === strpos( $label, 'invoicing_' ) ) {
                    $ph_data['{term_days}']   = ( !empty( $args['inv_date'] ) && !empty( $args['due_date'] ) )
                        ? intval( round( ( $args['due_date'] - $args['inv_date'] ) / 86400 ) ) : '' ;

                }
            }


            return $ph_data;
        }


        function wpc_invoice_cron() {
            global $wpdb;
            //create invoices from accumulating/recurring profiles
            $profile_inv_ids = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_next_create_inv' AND pm.meta_value < UNIX_TIMESTAMP() )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_recurring_type' )
                LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_post_type' )
                WHERE ( pm3.meta_value = 'repeat_inv' AND ( p.post_status = 'pending' OR pm2.meta_value = 'invoice_open' ) ) OR ( pm3.meta_value = 'accum_inv' AND p.post_status = 'active' )
                " );
            foreach ( $profile_inv_ids as $accum_inv_id ) {
                $this->create_inv_from_profile( $accum_inv_id );
            }

            //added Late Fee
            $inv_ids = $wpdb->get_results( "SELECT p.ID as id, pm1.meta_value as late_fee, pm2.meta_value as total
                                        FROM {$wpdb->posts} p
                                        INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                                        INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_due_date' AND pm.meta_value < UNIX_TIMESTAMP() )
                                        INNER JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_late_fee' AND pm1.meta_value > 0 )
                                        INNER JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_total' )
                                        LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_added_late_fee' )
                                        WHERE pm3.meta_value IS NULL
                                            AND p.post_type = 'wpc_invoice'
                                            AND ( p.post_status = 'open' OR p.post_status = 'sent' )
                                        ", ARRAY_A );


            foreach ( $inv_ids as $inv_id ) {
                update_post_meta( $inv_id['id'], 'wpc_inv_added_late_fee', $inv_id['late_fee'] ) ;
                update_post_meta( $inv_id['id'], 'wpc_inv_total', $inv_id['total'] + $inv_id['late_fee'] ) ;
            }

        }


        function added_excluded_pt( $excluded_post_types ) {
            $excluded_post_types[] = 'wpc_invoice';
            return $excluded_post_types;
        }

        function invoice_refund( $order_id ) {
            global $wpdb;

            $json_data = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM {$wpdb->prefix}wpc_client_payments WHERE id = '%s'", $order_id ) );
            $data = json_decode( $json_data, true );
            $id_inv = ( isset( $data['invoice_id'] ) ) ? $data['invoice_id']  : '';
            if( (int)$id_inv ) {
                $wpdb->update( $wpdb->posts, array( 'post_status' => 'refunded' ), array( 'ID' => $id_inv ), array( '%s' ), array( '%d' ) );
            }

        }


        function change_status_expired( $profile_id ) {
            if ( !$profile_id ) {
                return '';
            }

            $billing_cycle = get_post_meta( $profile_id, 'wpc_inv_billing_cycle', true );

            if( !$billing_cycle ) {
                return '';
            }

            global $wpdb;

            $all_payments = $wpdb->get_col( "SELECT count(id) as count FROM {$wpdb->prefix}wpc_client_payments
                                    WHERE order_status = 'paid'
                                    AND subscription_status = 'active'
                                    AND data LIKE '%\"profile_id\":\"" . (int)$profile_id . "\"%'
                                    GROUP BY client_id" );

            $all_canceled = $wpdb->get_var( "SELECT count( DISTINCT client_id ) FROM {$wpdb->prefix}wpc_client_payments
                                    WHERE subscription_status = 'canceled'
                                    AND data LIKE '%\"profile_id\":\"" . (int)$profile_id . "\"%'
                                    " );

            $all_clients = get_post_meta( $profile_id, 'wpc_inv_count_create_inv', true );


            $cycles = (int) $billing_cycle;
            if ( $cycles && $all_clients && (  $all_clients == count( $all_payments ) + $all_canceled ) ) {
                $status = 'expired';
                if ( $all_clients != $all_canceled ) {
                    foreach ( $all_payments as $value ) {
                        if ( $value != $cycles ) {
                            unset( $status );
                            break;
                        }
                    }
                }
            }

            if( isset( $status ) ) {
                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                    post_status = '%s'
                    WHERE id = '%d'
                    ",
                    $status,
                    $profile_id
                ));
            }
        }


        /*
        *  permission for delete currency
        */
        function currency_permission( $id ) {
            $use = get_posts( array(
                    'meta_key'        => 'wpc_inv_currency',
                    'meta_value'      => $id,
                    'post_type'       => 'wpc_invoice',
                    'post_status'     => 'any'
                    ) );
            return !$use;
        }


        /**
         * convert EST to INV
         */
        function convert_to_inv( $id, $action = 'convert' ) {
            global $wpdb;

            $new_number = $this->get_next_number();
            $new_date = date( "Y-m-d H:i:s" );

            $is_est = get_post_meta( $id, 'wpc_inv_post_type', true ) ;

            if ( 'est' == $is_est ) {
                update_post_meta( $id, 'wpc_inv_number', $new_number ) ;
                //change status of INV
                update_post_meta( $id, 'wpc_inv_post_type', 'inv' ) ;
                $wpdb->update( $wpdb->posts, array( 'post_date' => $new_date, 'post_status' => 'open' ), array( 'ID' => $id ) );
                $wpdb->update( $wpdb->prefix . 'wpc_client_objects_assigns', array( 'object_type' => 'invoice' ), array( 'object_type' => 'estimate', 'object_id' => $id ) );

                $clients = WPC()->assigns()->get_assign_data_by_object( 'invoice', $id, 'client' );
                $client_id = $clients[0];
                $userdata = get_userdata( $client_id );

                $data = $this->get_data( $id );

                $args = array(
                    'invoice_id' => $id,
                    'client_id' => $client_id,
                    'inv_number' => $new_number,
                    'invoicing_title' => $data['title'],
                    'accept_note' => '',
                    'total_amount' => isset( $data['total'] ) ? $data['total'] : '',
                    'minimum_payment' => isset( $data['min_deposit'] ) ? $data['min_deposit'] : '',
                    'due_date' => isset( $data['due_date'] ) ? WPC()->date_format( $data['due_date'], 'date' ) : '',
                );

                if ( 'convert' == $action ) {
                    WPC()->mail( 'convert_est_to_inv', $userdata->get( 'user_email' ), $args, 'invoice_notify' );
                } elseif ( 'accept' == $action ) {
                    $emails_array = $this->get_emails_of_admins();

                    foreach( $emails_array as $to_email ) {
                        WPC()->mail( 'accept_est', $to_email, $args, 'invoice_notify_admin' );
                    }
                }
            }

        }


        /*
        * Download PDF of INV\EST
        */
        function pdf_downloader() {

            if ( isset( $_GET['wpc_action'] ) && 'download_pdf' == $_GET['wpc_action'] && isset( $_GET['id'] ) && '' != $_GET['id'] ) {

                $invoice_id = $_GET['id'];
                $invoice_data = array();

                if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_create_invoices' ) ) {
                    $invoice_data = $this->get_data( $invoice_id );
                } else {
                    if ( current_user_can( 'wpc_client_staff' ) ) {
                        $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
                    } else {
                        $client_id = get_current_user_id();
                    }

                    $invoice_data = $this->get_data( $invoice_id );
                    if( isset( $invoice_data['type'] ) && 'est' == $invoice_data['type'] ) {
                        $invoices_client = WPC()->assigns()->get_assign_data_by_assign( 'estimate', 'client', $client_id );
                    } else {
                        $invoices_client = WPC()->assigns()->get_assign_data_by_assign( 'invoice', 'client', $client_id );
                    }

                    if ( !in_array( $_GET['id'], $invoices_client ) ) {
                        $invoice_data = array();
                    }
                }

                if ( !isset( $invoice_data['id'] ) ) {
                    return;
                }

                $content = $this->invoicing_put_values( $invoice_data );

                $this->get_pdf( $invoice_data, $content, false );

            }

            /**
             * Action Bulk Download PDF(s)
             */
            if( isset( $_GET['action'] ) && 'download_zip' == $_GET['action'] ) {
                check_admin_referer( 'bulk-' . sanitize_key( __( 'Invoices', WPC_CLIENT_TEXT_DOMAIN ) ) );
                $file_ids = array();

                if( isset( $_GET['item'] ) ) {
                    $file_ids = $_GET['item'];
                }

                if( ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
                    // Download ZIP if more than one
                    if( count( $file_ids ) > 1 ) {
                        $this->download_pdf_in_zip( $file_ids );
                    }else {
                        $invoice_data = $this->get_data( $file_ids );
                        $content = $this->invoicing_put_values( $invoice_data );
                        $this->get_pdf( $invoice_data, $content, false );
                    }
                }
            }

        }

        /**
         * helper: Zip downloader
         * @param $file_ids
         */
        function download_pdf_in_zip( $file_ids ) {
            $zip_name = 'invoices';
            $orig_zip_name = time() . '_' . uniqid() . '_bulk_download';
            // clear old temporary directories
            $parent_dir         = WPC()->get_upload_dir( 'wpclient/_temp_bulk_download/' );
            $old_temp_dir_names = array_diff( scandir( $parent_dir ), array( '.', '..' ) );
            foreach ( $old_temp_dir_names as $old_temp_dir_name ) {
                $old_temp_dir = $parent_dir . DIRECTORY_SEPARATOR . $old_temp_dir_name;
                if ( is_dir( $old_temp_dir ) && ( time() - filemtime( $old_temp_dir ) > 60 ) ) {
                    $old_temp_file_names = array_diff( scandir( $old_temp_dir ), array( '.', '..' ) );
                    foreach ( $old_temp_file_names as $old_temp_file_name ) {
                        $old_temp_file = $old_temp_dir . DIRECTORY_SEPARATOR . $old_temp_file_name;
                        if ( is_file( $old_temp_file ) ) {
                            unlink( $old_temp_file );
                        }
                    }
                    rmdir( $old_temp_dir );
                }
            }

            $file_temp_dir = WPC()->get_upload_dir( 'wpclient/_temp_bulk_download/' . $orig_zip_name . '/' );

            $items_array = array();
            foreach ( $file_ids as $file ) {
                // create pdf
                $invoice_data = $this->get_data( $file );
                $content = $this->invoicing_put_values( $invoice_data );

                // get server path to pdf
                $target_path = $this->get_pdf( $invoice_data, $content, true );
                $target_path_arr = explode( '/', $target_path );
                $file_name = end( $target_path_arr );

                // create tmp path for pdf
                $temp_name = $file_temp_dir . $file_name;

                if ( copy( $target_path, $temp_name ) ) {
                    $items_array[ $file ] = $temp_name;
                }
            }

            if( count( $items_array ) > 0 ) {
                if ( ! ini_get( 'safe_mode' ) ) {
                    @set_time_limit( 0 );
                }

                if ( exec( "cd $file_temp_dir; zip -r $orig_zip_name.zip *" ) && file_exists( $file_temp_dir . $orig_zip_name . '.zip' ) ) {
                    if ( isset( $items_array[ $file ] ) ) {
                        unlink( $items_array[ $file ] );
                    }

                    header( "Pragma: no-cache" );
                    header( "Expires: 0" );
                    header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
                    header( "Robots: none" );
                    header( "Content-Description: File Transfer" );
                    header( "Content-Transfer-Encoding: binary" );
                    header( 'Content-Type: application/octet-stream' );
                    header( "Content-length: " . filesize( $file_temp_dir . $orig_zip_name . '.zip' ) );
                    header( 'Content-disposition: attachment; filename="' . $zip_name . '.zip"' );

                    $levels = ob_get_level();
                    for ( $i = 0; $i < $levels; $i ++ ) {
                        @ob_end_clean();
                    }

                    WPC()->readfile_chunked( $file_temp_dir . $orig_zip_name . '.zip' );
                }

                if ( file_exists( $file_temp_dir . $orig_zip_name . '.zip' ) ) {
                    unlink( $file_temp_dir . $orig_zip_name . '.zip' );
                }
            }

            if ( is_dir( $file_temp_dir ) ) {
                system( 'rm -rf ' . $file_temp_dir );
            }

            exit();
        }


        /*
        * Register post types
        */
        function _create_post_type() {

            //Invoice post type
            $labels = array(
                'name'                  => __('Invoices', WPC_CLIENT_TEXT_DOMAIN ),
                'singular_name'         => __('Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'add_new'               => __( 'Add New', WPC_CLIENT_TEXT_DOMAIN ),
                'add_new_item'          => __( 'Add New Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'edit_item'             => __( 'Edit Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'new_item'              => __( 'New Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'view_item'             => __( 'View Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'search_items'          => __( 'Search Invoices', WPC_CLIENT_TEXT_DOMAIN ),
                'not_found'             => __( 'No invoices found', WPC_CLIENT_TEXT_DOMAIN ),
                'not_found_in_trash'    => __( 'No invoices found in Trash', WPC_CLIENT_TEXT_DOMAIN ),
                'parent_item_colon'     => ''
            );

            $args = array(
                'labels'                => $labels,
                'singular_label'        => __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'public'                => false,
                'show_ui'               => false,
                //'rewrite'               => array( 'slug' => $wp_properties[ 'configuration' ][ 'base_slug' ] ),
                //'query_var'             => $wp_properties[ 'configuration' ][ 'base_slug' ],
                'capability_type'       => 'wpc_invoice',
                //'capabilities'          => array( 'edit_posts' => 'edit_published_clientpages' ),
                '_edit_link'            => 'admin.php?page=wpclients_invoicing&tab=invoice_edit&id=%d',
                //'hierarchical'          => false,
                //'_builtin'              => false,
                //'supports'              => array( 'title', 'editor', 'thumbnail' )

            );

            register_post_type('wpc_invoice', $args);

        }


        /**
         * send reminder
         */
         function send_reminder() {
            global $wpdb;

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            if( isset( $wpc_invoicing['reminder_days_enabled'] ) && 'yes' == $wpc_invoicing['reminder_days_enabled'] ) {

                $remind_days = ( isset( $wpc_invoicing['reminder_days'] ) && 0 < $wpc_invoicing['reminder_days'] && 32 > $wpc_invoicing['reminder_days'] ) ? (int)$wpc_invoicing['reminder_days'] : 1;

                $time = time();

                //Send first reminder email
                $first_reminder = " pm4.meta_value IS NULL AND pm1.meta_value < '" . ( $time+60*60*24*$remind_days ) . "'";


                // Send final reminder one day before due date
                $reminder_one_day_before_due_date = ( !isset( $wpc_invoicing['reminder_one_day'] ) || 'yes' == $wpc_invoicing['reminder_one_day'] )
                        ? " OR ( pm4.meta_value = '1' AND pm1.meta_value < '" . ( $time+60*60*24 ) . "' )" : '';

                //Send Email Reminder after due date ( every X days )
                $reminder_after_every = ( isset( $wpc_invoicing['reminder_after'] ) && 0 < $wpc_invoicing['reminder_after'] && 32 > $wpc_invoicing['reminder_after'] ) ?
                " OR pm1.meta_value < '" . $time . "' AND
                    (
                        pm6.meta_value IS NULL OR                        
                        pm4.meta_value < pm1.meta_value OR
                        pm4.meta_value < '" . ( $time - 60*60*24*$wpc_invoicing['reminder_after'] ) . "'
                    )

                " : '';

                $wpdb->query("SET SESSION SQL_BIG_SELECTS=1");
                //reminder_before using for defining which template for reminder use,
                // $time - 60*60*24 because date of due date included for payment
                $invs_for_remind = $wpdb->get_results(
                    "SELECT p.ID as id, p.post_title as title, coa.assign_id as client_id, pm3.meta_value as number, pm4.meta_value as last_reminder, pm5.meta_value as cc_emails
                            , pm1.meta_value > '" . ( $time - 60*60*24 ) . "' as reminder_before, pm6.meta_value as cancel_repeat_reminder
                        FROM {$wpdb->posts} p
                        INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                        LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'invoice' )
                        LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_due_date' )
                        LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_number' )
                        LEFT JOIN {$wpdb->postmeta} pm4 ON ( p.ID = pm4.post_id AND pm4.meta_key = 'wpc_inv_last_reminder' )
                        LEFT JOIN {$wpdb->postmeta} pm5 ON ( p.ID = pm5.post_id AND pm5.meta_key = 'wpc_inv_cc_emails' )
                        LEFT JOIN {$wpdb->postmeta} pm6 ON ( p.ID = pm6.post_id AND pm6.meta_key = 'wpc_inv_cancel_repeat_reminder' )
                        WHERE p.post_type = 'wpc_invoice' AND pm1.meta_value > 0
                            AND ( p.post_status IS NULL OR p.post_status NOT IN ( 'paid', 'void', 'refunded', 'draft' ) )
                            AND (
                                pm1.meta_value > '" . $time . "'  
                                AND ( {$first_reminder} {$reminder_one_day_before_due_date}  ) 
                                
                                {$reminder_after_every}                                                         
                                ) 
                        ",
                    ARRAY_A );

                if ( is_array( $invs_for_remind ) && 0 < count( $invs_for_remind ) ) {

                    //send email to client
                    foreach ( $invs_for_remind as $inv ) {

                        if ( 0 < $inv['client_id'] ) {
                            $with_pdf = !empty( $wpc_invoicing['attach_pdf_reminder'] )
                                    && 'yes' === $wpc_invoicing['attach_pdf_reminder'];

                            //get data
                            $inv_data = $this->get_data( $inv['id'] );

                            $attachments = array();
                            if ( $with_pdf  ) {
                                $content = $this->invoicing_put_values( $inv_data );
                                $attachment = $this->get_pdf( $inv_data, $content );
                                $attachments[] = $attachment;
                            }

                            $userdata = get_userdata( $inv['client_id'] );

                            $payment_method = '';
                            if ( isset( $inv_data['order_id'] ) && is_array( $inv_data['order_id'] ) ) {
                                $payment_method = $this->get_payment_method( $inv_data['order_id'] );
                            }

                            $args = array(
                                'invoice_id' => $inv['id'],
                                'client_id' => $inv['client_id'],
                                'inv_number' => $inv['number'],
                                'invoicing_title' => $inv['title'],
                                'total_amount' => isset( $inv_data['total'] ) ? $inv_data['total'] : '',
                                'minimum_payment' => isset( $inv_data['min_deposit'] ) ? $inv_data['min_deposit'] : '',
                                'due_date' => isset( $inv_data['due_date'] ) ? WPC()->date_format( $inv_data['due_date'], 'date' ) : '',
                                'payment_method' => $payment_method,
                            );

                            //send emails
                            if ( !empty( $inv['cc_emails'] ) ) {
                                //for CC Emails
                                $emails = unserialize( $inv['cc_emails'] );
                            }
                            $emails[] = $userdata->get( 'user_email' );
                            $template = !empty( $inv['reminder_before'] ) ? 'pay_rem_before' : 'pay_rem';
                            foreach ( $emails as $email ) {
                                WPC()->mail( $template, $email, $args, 'invoice_reminder', $attachments );
                            }

                            if ( $with_pdf ) {
                                unlink( $attachment );
                            }

                            //update last reminder time
                            if ( $inv['last_reminder'] ) {
                                update_post_meta( $inv['id'], 'wpc_inv_last_reminder', $time ) ;
                            } else {
                                update_post_meta( $inv['id'], 'wpc_inv_last_reminder', '1' ) ;
                            }

                        }
                    }
                }
            }

        }


        /**
         * Adding a new rule
         **/
        function _insert_rewrite_rules( $rules ) {
            $newrules = array();

            //invoicing pages
            $newrules[WPC()->get_slug( 'invoicing_page_id', false, false ) . '/([\w\d_-]+)/?$'] = 'index.php?wpc_page=invoicing&wpc_page_value=$matches[1]';

            return $newrules + $rules;
        }


        /**
         * get status texts for display
         */
        function display_status_name( $status = '' ) {

            $return = __( 'New', WPC_CLIENT_TEXT_DOMAIN );

            if ( $status ) {
                switch( $status ) {
                    case 'new':
                        $return = __( 'New', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'refunded':
                        $return = __( 'Refunded', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'paid':
                        $return = __( 'Paid', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'partial':
                        $return  = __( 'Partial', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'inprocess':
                        $return = __( 'In-Process', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'active':
                        $return = __( 'Active', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'draft':
                        $return = __( 'Draft', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'void':
                        $return = __( 'Void', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'declined':
                        $return = __( 'Declined', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'accepted':
                        $return = __( 'Accepted', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'canceled':
                        $return = __( 'Canceled', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'ended':
                        $return = __( 'Ended', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'stopped':
                        $return = __( 'Stopped', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pending':
                        $return = __( 'Pending', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'expired':
                        $return = __( 'Expired', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'sent':
                        $return = __( 'Open (Sent)', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'open':
                        $return = __( 'Open', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'waiting_admin':
                    case 'waiting_client':
                        $user = str_replace( 'waiting_', '', $status );
                        $return = sprintf( __( 'Waiting on %s', WPC_CLIENT_TEXT_DOMAIN), WPC()->custom_titles[ $user ]['s'] );
                        break;

                }

            }

            return $return;

        }


        /**
         * Get payment method name for order
         *
         * @global object $wpdb
         * @param mixed $orders_id
         * @param string $method Optional if method is known
         * @return string
         */
        function get_payment_method( $orders_id, $method = null ) {
            if ( is_null( $method ) ) {
                global $wpdb;

                $order_id = ( is_array( $orders_id ) ) ? array_pop( $orders_id ) : $orders_id;

                $method = $wpdb->get_var( $wpdb->prepare( "SELECT `payment_method`"
                        . " FROM {$wpdb->prefix}wpc_client_payments"
                        . " WHERE `id` = %d", $order_id ));
            }

            $gateways = $this->get_manual_gateways();

            if ( isset( $gateways[ $method ] ) ) {
                $method_name = $gateways[ $method ];
            } else {
                $method_name = ucwords( str_replace( array( '_', '-' ), ' ', $method ) );
            }

            return $method_name;
        }


        /**
         * Get payment date for order
         *
         * @global object $wpdb
         * @param mixed $orders_id
         * @return string
         */
        function get_payment_date( $orders_id ) {
            global $wpdb;

            $order_id = ( is_array( $orders_id ) ) ? array_pop( $orders_id ) : $orders_id;

            $date = $wpdb->get_var( $wpdb->prepare( "SELECT `time_paid`"
                    . " FROM {$wpdb->prefix}wpc_client_payments"
                    . " WHERE `id` = %d", $order_id ));

            $payment_date = WPC()->date_format( $date, 'date' );

            return $payment_date;
        }



        function get_manual_gateways() {
            return array(
                'p_cash' => __( 'Cash', WPC_CLIENT_TEXT_DOMAIN ),
                'p_check' => __( 'Check', WPC_CLIENT_TEXT_DOMAIN ),
                'p_wire_transfer' => __( 'Wire Transfer', WPC_CLIENT_TEXT_DOMAIN ),
                'p_credit_card' => __( 'Credit Card', WPC_CLIENT_TEXT_DOMAIN ),
                'p_paypal' => __( 'PayPal', WPC_CLIENT_TEXT_DOMAIN ),
                'p_barter' => __( 'Barter', WPC_CLIENT_TEXT_DOMAIN ),
                'p_contribution' => __( 'Contribution', WPC_CLIENT_TEXT_DOMAIN ),
                'authorizenet-aim' => __( 'Authorize.net AIM', WPC_CLIENT_TEXT_DOMAIN ),
                'authorizenet-sim' => __( 'Authorize.net SIM', WPC_CLIENT_TEXT_DOMAIN ),
                'paypal-express' => __( 'PayPal Express', WPC_CLIENT_TEXT_DOMAIN ),
                'stripe' => __( 'Stripe', WPC_CLIENT_TEXT_DOMAIN ),
                '2checkout' => __( '2CheckOut', WPC_CLIENT_TEXT_DOMAIN ),
                'amazonpayments' => __( 'Amazon Payments', WPC_CLIENT_TEXT_DOMAIN ),
                'braintree' => __( 'Braintree', WPC_CLIENT_TEXT_DOMAIN ),
                'payfast' => __( 'PayFast', WPC_CLIENT_TEXT_DOMAIN ),
                'paymill' => __( 'PayMill', WPC_CLIENT_TEXT_DOMAIN ),
                'paywithamazon' => __( 'Pay With Amazon', WPC_CLIENT_TEXT_DOMAIN ),
                'payza' => __( 'Payza', WPC_CLIENT_TEXT_DOMAIN ),
                'skrill' => __( 'Skrill', WPC_CLIENT_TEXT_DOMAIN ),
                'p_other' => __( 'Other', WPC_CLIENT_TEXT_DOMAIN ),
            );
        }


        function get_rate_capacity() {
            if ( empty( $this->rate_capacity ) ) {

                $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                $this->rate_capacity = ( isset( $wpc_invoicing['rate_capacity'] )
                        && '2' < $wpc_invoicing['rate_capacity']
                        && '6' > $wpc_invoicing['rate_capacity'] )
                        ? $wpc_invoicing['rate_capacity'] : 2;
            }

            return $this->rate_capacity;
        }


        function get_thousands_separator() {
            if ( empty( $this->thousands_separator ) ) {

                $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                $this->thousands_separator = ( isset( $wpc_invoicing['thousands_separator'] )
                        && !empty( $wpc_invoicing['thousands_separator'] ) )
                        ? $wpc_invoicing['thousands_separator'] : '';
            }

            return $this->thousands_separator;
        }


        /**
         * get status texts for display
         */
        function get_currency( $number = 0, $span = false, $selected_curr = '', $without_curr = false ) {

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );
            $rate_capacity = $this->get_rate_capacity();
            $thousands_separator = $this->get_thousands_separator();

            $f_number = number_format( (float)$number, $rate_capacity, '.', $thousands_separator );

            if ( $span ) {
                $number = '<span class="amount" data-number="' . $number . '">' . $f_number . '</span>' ;
            } else {
                $number = $f_number;
            }

            if ($without_curr) {
                return $number;
            }

            $ver = get_option( 'wp_client_ver' );

            if ( version_compare( $ver, '3.5.0' ) ) {
                $wpc_currency = WPC()->get_settings( 'currency' );
                $def_currency = '';

                if ( '' != $selected_curr && isset( $wpc_currency[ $selected_curr ] ) ) {
                    $def_currency = $wpc_currency[ $selected_curr ];
                } else {
                    foreach( $wpc_currency as $key => $value ) {
                        if( 1 == $value['default'] ) {
                            $def_currency = $wpc_currency[ $key ];
                            break;
                        }
                    }
                }

                if ( 'left' ==  $def_currency['align'] ) {
                    $number = $def_currency['symbol'] . $number ;
                } else {
                    $number = $number . $def_currency['symbol'] ;
                }
            } else {

                $currency_symbol    = array(
                    'left'  => ( isset( $wpc_invoicing['currency_symbol'] )
                        && ( !isset( $wpc_invoicing['currency_symbol_align'] )
                        || 'left' == $wpc_invoicing['currency_symbol_align'] ) )
                        ? $wpc_invoicing['currency_symbol'] : '',
                    'right' => ( isset( $wpc_invoicing['currency_symbol'] )
                        && isset( $wpc_invoicing['currency_symbol_align'] )
                        && 'right' == $wpc_invoicing['currency_symbol_align'] )
                        ? $wpc_invoicing['currency_symbol'] : '',
                );


               $number = $currency_symbol['left'] . $number . $currency_symbol['right'];
               }

            return $number;
        }


        function get_currency_and_side( $invoice_id ) {
            $result = array();
            $ver = get_option( 'wp_client_ver' );

            if ( version_compare( $ver, '3.5.0' ) ) {
                $wpc_currency = WPC()->get_settings( 'currency' );
                $currency = '';
                $selected_curr = get_post_meta( $invoice_id, 'wpc_inv_currency', true );

                if ( $selected_curr && '' != $selected_curr ) {
                    $currency = $wpc_currency[ $selected_curr ];
                } else {
                    foreach( $wpc_currency as $key => $value ) {
                        if( 1 == $value['default'] ) {
                            $currency = $wpc_currency[ $key ];
                            break;
                        }
                    }
                }
                if ( 'left' ==  $currency['align'] ) {
                    $result['align'] = 'left';
                } else {
                    $result['align'] = 'right';
                }
                $result['symbol'] = $currency['symbol'];
            } else {
                $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                if ( !isset( $wpc_invoicing['currency_symbol_align'] ) || 'left' == $wpc_invoicing['currency_symbol_align']  ) {
                    $result['align'] = 'left';
                } else {
                    $result['align'] = 'right';
                }
                $result['symbol'] = ( isset( $wpc_invoicing['currency_symbol'] ) ) ? $wpc_invoicing['currency_symbol'] : '';
            }

            return $result;

        }


        /**
         * get step for slider for users
         */
        function get_step( $amount ) {
            if ( 100 >= $amount ) {
                $step = 1;
            } else if( 1000 >= $amount ) {
                $step = 5;
            } else {
                $step = 10;
            }
            return $step;
        }


        /**
         * Sanitize descriptions for items, discounts and taxes for generation of PDF
         *
         * @param string $text
         * @return string
         */
        function sanitize_description( $text ) {
            $text = nl2br(make_clickable(stripslashes(substr( $text, 0, 1000 ))));

            return $text;
        }


        /**
         * put values in plase holders
         */
        function invoicing_put_values( $data ) {

            if ( $data ) {
                $wpc_business_info  = WPC()->get_settings( 'business_info' );
                $wpc_invoicing = WPC()->get_settings( 'invoicing' );

                $selected_curr  = ( isset( $data['currency'] ) ) ? $data['currency'] : '' ;
                $LateFee        = ( isset( $data['late_fee'] ) ) ? $data['late_fee'] : 0;
                $is_late_fee = false;
                $count_late_fee = 0;
                if ( isset( $data['due_date'] ) && '' != $data['due_date'] && $data['due_date'] < time() && 0 < $LateFee ) {
                     $is_late_fee = true;
                     $count_late_fee = $LateFee;
                }

                $arr_data       = array (
                    'InvoiceNumber' => $data['number'],

                    'CustomerName' => '',
                    'CustomerBAddress' => '',
                    'CustomerBCity' => '',
                    'CustomerBState' => '',

                    'InvoiceDescription' => ( isset( $data['description'] ) ) ? nl2br(stripslashes( $data['description'] )) : '',

                    'InvoiceDate' => ( isset( $data['date'] ) && 0 < $data['date'] ) ? WPC()->date_format( strtotime( $data['date'] ), 'date' ) : '',

                    'DueDate' => ( isset( $data['due_date'] ) && '' != $data['due_date'] ) ? WPC()->date_format( $data['due_date'], 'date' ) : '',
                    //'DiffDate' => ( !empty( $arr_data['date'] ) && !empty( $arr_data['due_date'] ) ) ? ( strtotime( $data['date'] ) - $data['due_date'] ) : '',
                    'IsLateFee' => $is_late_fee,

                    'Notes' => ( isset( $data['note'] ) ) ? stripslashes( $data['note'] ) : '',

                    'InvoiceSubTotal' => '',

                    'TotalDiscount' => '',
                    'TaxName' => '',
                    'TaxRate' => '',
                    'TotalTax' => '',

                    'InvoiceTotal' => '',
                    'LateFee' =>  $this->get_currency( $LateFee, 0, $selected_curr ),
                    'PaymentMade' => '',
                    'PaymentDate' => '',

                );

                $arr_data += $this->get_cf_for_inv( $data['id'] );

                if ( !empty( $wpc_business_info['business_logo_url'] ) ) {
                    $arr_data['business_logo_url'] = $wpc_business_info['business_logo_url'];
                }

                if ( isset( $data['terms'] ) ) {
                    $arr_data['TermsAndCondition'] = stripslashes( $data['terms'] ) ;
                }

                //items
                $total_items = 0;
                $arr_data['CustomFields'] = $arr_data['TitleCustomFields'] = array();
                if ( '' != $data['items'] ) {
                    $data['items'] = unserialize( $data['items'] );
                    if ( isset( $data['custom_fields'] ) ) {
                        $wpc_inv_custom_fields = WPC()->get_settings( 'inv_custom_fields' );
                        if( isset( $data['custom_fields']['description'] ) && 1 == $data['custom_fields']['description'] ) {
                            $arr_data['show_description'] = true;
                        }
                        foreach( $wpc_inv_custom_fields as $key => $field ) {
                            if( isset( $data['custom_fields'][ $key ] ) && 1 == $data['custom_fields'][ $key ] ) {
                                $arr_data['CustomFields'][] = array( 'type' => $field['type'], 'slug' => $key, 'options' => ( ( isset( $field['options'] ) ) ? $field['options'] : '' ), );
                                $arr_data['TitleCustomFields'][] = htmlspecialchars( $field['title'] );
                            }
                        }
                    } else {
                        $arr_data['show_description'] = true;
                    }

                    if ( is_array( $data['items'] ) && 0 < count( $data['items'] ) ) {
                        foreach( $data['items'] as $item ) {
                            $quantity = ( isset( $item['quantity'] ) ) ? $item['quantity'] : '1';

                            $total_items = $total_items + $item['price'] * $quantity;

                            $array_cf = array();
                            foreach( $arr_data['CustomFields'] as $cf ) {
                                if ( 'checkbox' == $cf['type'] ) {
                                    if ( isset( $item[ $cf['slug'] ] ) && 1 == $item[ $cf['slug'] ] ) {
                                        $array_cf[ $cf['slug'] ] = '<img src="' . WPC()->plugin_url . 'images/checkbox_check.png" border="0" width="16" height="16" alt="checkbox.png">' ;
                                    } else {
                                        $array_cf[ $cf['slug'] ] = '<img src="' . WPC()->plugin_url . 'images/checkbox_uncheck.png" border="0" width="16" height="16" alt="checkbox.png">' ;
                                    }
                                    //$array_cf[ $cf['slug'] ] = '<img type="checkbox" ' . $checked . ' disabled />' ;
                                } elseif ( 'selectbox' == $cf['type']  ) {
                                    $array_cf[ $cf['slug'] ] = ( isset( $item[ $cf['slug'] ] ) && isset ( $cf['options'][ $item[ $cf['slug'] ] ] ) ) ? $cf['options'][ $item[ $cf['slug'] ] ]  : '';
                                } else {
                                    $array_cf[ $cf['slug'] ] = ( isset( $item[ $cf['slug'] ] ) ) ? htmlspecialchars( stripslashes( $item[ $cf['slug'] ] ) ) : '';
                                }
                            }

                            $arr_data['items'][] = array_merge( array (
                                'ItemName'          => ( isset( $item['name'] ) ) ? htmlspecialchars( $item['name'] ) : '',
                                'ItemDescription'   => ( isset( $item['description'] ) ) ? $this->sanitize_description( $item['description'] ) : '',
                                'ItemQuantity'      => $quantity,
                                'ItemRate'          => ( isset( $item['price'] ) ) ? $this->get_currency( $item['price'], 0, $selected_curr ) : '',
                                'ItemTotal'         => ( isset( $item['price'] ) ) ? $this->get_currency( $item['price'] * $quantity, 0, $selected_curr ) : '',
                            ),
                            $array_cf );

                        }

                        if( 0 == count( $arr_data['CustomFields'] ) ){
                            unset( $arr_data['CustomFields'] ) ;
                            unset( $arr_data['TitleCustomFields'] ) ;
                        }
                    }
                }

                $arr_data['widthItemName'] = 74;
                $arr_data['widthCF'] = 0;
                if ( isset( $arr_data['CustomFields'] ) ) {
                    $countCF = count( $arr_data['CustomFields'] );
                    if ( $countCF > 6 ) {
                        $arr_data['widthCF'] = round(60/$countCF);
                    } else {
                        $arr_data['widthCF'] = 10;
                    }
                    $arr_data['widthItemName'] -= $arr_data['widthCF'] * $countCF;
                }

                //only for temlate using embedded tables
                $arr_data['colspan_for_name'] = 1 ;
                if ( isset( $arr_data['CustomFields'] ) ) {
                    $arr_data['colspan_for_name'] += count( $arr_data['CustomFields'] ) ;
                }


                //discounts
                $total_discounts = 0;
                if ( '' != $data['discounts'] ) {
                    $data['discounts'] = unserialize( $data['discounts'] );

                    if ( is_array( $data['discounts'] ) && 0 < count( $data['discounts'] ) ) {
                        foreach( $data['discounts'] as $disc ) {
                            if ( isset( $disc['type'] ) ) {
                                $type = ( '' != $disc['type'] ) ? ucfirst( $disc['type'] ) : '' ;
                            } else {
                                $type = '';
                            }
                            $total_discounts = $total_discounts + $disc['total'] ;

                            $arr_data['discounts'][] = array (
                                'name'          => ( isset( $disc['name'] ) ) ? htmlspecialchars( $disc['name'] ) : '',
                                'description'   => ( isset( $disc['description'] ) ) ? $this->sanitize_description( $disc['description'] ) : '',
                                'type'          => $type,
                                'rate'          => $disc['rate'],
                                'total'         => ( isset( $disc['total'] ) ) ? $this->get_currency( $disc['total'], 0, $selected_curr ) : '',
                            );
                        }
                    }
                }

                //taxes
                $total_tax = 0;
                if ( '' != $data['taxes'] ) {
                    $data['taxes'] = unserialize( $data['taxes'] );

                    if ( is_array( $data['taxes'] ) && 0 < count( $data['taxes'] ) ) {
                        foreach( $data['taxes'] as $tax ) {
                            if ( isset( $tax['type'] ) ) {
                                if ( 'before' == $tax['type'] ) {
                                    $type = __( 'Before Discount', WPC_CLIENT_TEXT_DOMAIN ) ;
                                } else if ( 'after' == $tax['type'] ) {
                                    $type = __( 'After Discount', WPC_CLIENT_TEXT_DOMAIN ) ;
                                }
                            } else {
                                $type = '';
                            }
                            $total_tax = $total_tax + $tax['total'] ;

                            $arr_data['taxes'][] = array (
                                'name'          => ( isset( $tax['name'] ) ) ? htmlspecialchars( $tax['name'] ) : '',
                                'description'   => ( isset( $tax['description'] ) ) ? $this->sanitize_description( $tax['description'] ) : '',
                                'type'          => $type,
                                'rate'          => $tax['rate'],
                                'total'         => ( isset( $tax['total'] ) ) ? $this->get_currency( $tax['total'], 0, $selected_curr ) : '',
                            );

                        }
                    }
                }

                $invoice_total = $total_items - $total_discounts + $total_tax + $count_late_fee;
                $vat_total = $total_items - $total_discounts + $count_late_fee;


                $arr_data['TotalDiscount']      = $this->get_currency( $total_discounts, 0, $selected_curr ) ;
                $arr_data['IsTotalDiscount']    = ( $total_discounts ) ? true : false ;
                $arr_data['TotalTax']           = $this->get_currency( $total_tax, 0, $selected_curr ) ;
                $arr_data['IsTotalTax']         = ( $total_tax ) ? true : false ;
                $arr_data['ShowVAT']            = ( !empty( $data['show_vat'] ) ) ? true : false ;
                $vat = ( !empty( $wpc_invoicing['vat'] ) && 0 < (float)$wpc_invoicing['vat'] ) ? $wpc_invoicing['vat'] : 0;
                $total_vat = $vat_total * $vat / ( 100 + $vat );
                $arr_data['TotalVAT']           = $this->get_currency( $total_vat, 0, $selected_curr ) ;
                $total_net = $vat_total - $total_vat;
                $arr_data['TotalNet']           = $this->get_currency( $total_net, 0, $selected_curr ) ;
                $arr_data['InvoiceSubTotal']    = $this->get_currency( $total_items, 0, $selected_curr ) ;

                $arr_data['InvoiceTotal']       = $this->get_currency( $invoice_total, 0, $selected_curr ) ;

                if ( isset( $data['order_id'] ) && '' != $data['order_id'] ) {
                    $payment_amount = $this->get_amount_paid( $data['id'] );

                    $arr_data['PaymentMethod'] = $this->get_payment_method( $data['order_id'] ) ;
                    $arr_data['PaymentMade'] = $this->get_currency( $payment_amount, 0, $selected_curr ) ;
                    $arr_data['PaymentDate'] = $this->get_payment_date( $data['order_id'] ) ;

                    if ( !isset( $data['recurring_type'] )  ) {
						$remaining = $invoice_total < $payment_amount ? 0 : $invoice_total - $payment_amount;
                        $arr_data['TotalRemaining'] = $this->get_currency( $remaining, 0, $selected_curr  ) ;
                    }
                }

                $payment_method = '';
                if ( isset( $data['order_id'] ) && is_array( $data['order_id'] ) ) {
                    $payment_method = $this->get_payment_method( $data['order_id'] );
                }

                $arr_data = array_merge( $arr_data, array(
                    'client_id' => $data['client_id'],
                    'inv_date'  => ( !empty( $data['date'] ) ) ? strtotime( $data['date'] ) : '',
                    'due_date'  => ( !empty( $data['due_date'] ) ) ? $data['due_date'] : '',
                    'invoicing_title' => ( !empty( $data['title'] ) ) ? $data['title'] : '',
                    'total_amount'      => isset( $data['total'] ) ? $data['total'] : '',
                    'minimum_payment'   => isset( $data['min_deposit'] ) ? $data['min_deposit'] : '',
                    'due_date'          => isset( $data['due_date'] ) ? WPC()->date_format( $data['due_date'], 'date' ) : '',
                    'payment_method'    => $payment_method,

                    'invoicing_title' => ( !empty( $data['title'] ) ) ? $data['title'] : '',
                    'inv_number' => $data['number'],
                ) );

                return WPC()->get_template( $data['type'] == 'est' ? 'estimate.php' : 'invoice.php', 'invoicing', $arr_data);
            }

            return '';

        }


        /**
         * Get values of custom fields of invoice/estimate for templates
         *
         * @global object $wpc_client
         * @param integer $inv_id ID
         * @return array
         */
        function get_cf_for_inv( $inv_id ) {
            $arr_data = array();

            $invoice_cf  = WPC()->get_settings( 'invoice_cf' );

            $data_cf = get_post_meta( $inv_id, 'wpc_inv_invoice_cf', true );

            //replace Custom Fields for Invoices
            foreach ($invoice_cf as $slug => $value) {
                $arr_data[ $slug ] = $endDiv = '';
                if ( !empty( $value['title'] ) ) {
                    $descr = ( !empty( $value['description'] ) ) ? htmlspecialchars( $value['description'] ) : '';
                    $arr_data[ $slug ] .= '<div class="wpc_inv_cf" title="' . $descr . '"><span>' . htmlspecialchars( $value['title'] ) . ':&nbsp;</span>';
                    $endDiv = '</div>';
                }

                if ( empty( $value['type'] ) || ( in_array( $value['type'],
                    array( 'checkbox', 'radio', 'selectbox', 'multiselectbox' ) )
                        && empty( $value['options'] )) ) {
                    $arr_data[ $slug ] .= $endDiv;
                    continue;
                }

                if ( !empty( $data_cf[ $slug ] ) ) {
                    $valuation = $data_cf[ $slug ];
                    switch( $value['type']) {
                        case 'checkbox':
                            foreach ( $value['options'] as $key => $option ) {
                                $prefix = ( $valuation && in_array( $key, $valuation ) )
                                        ? '' : 'un';
                                $arr_data[ $slug ] .= '<br>' . '<img src="' . WPC()->plugin_url . 'images/checkbox_' . $prefix . 'check.png" border="0" width="16" height="16" alt="checkbox.png">'
                                        . htmlspecialchars( $option );
                            }
                        break;

                        case 'radio':
                            foreach ( $value['options'] as $key => $option ) {
                                if ( $key == $valuation ) {
                                    $arr_data[ $slug ] .= htmlspecialchars( $option );
                                }
                            }
                        break;

                        case 'multiselectbox':
                        case 'selectbox':
                            foreach ( $value['options'] as $key => $option ) {
                                if ( $valuation && ( is_array($valuation) && in_array( $key, $valuation )
                                        || is_string($valuation) && $key == $valuation )) {
                                    $arr_data[ $slug ] .= htmlspecialchars( $option );
                                }
                            }
                        break;

                        case 'textarea' :
                            $arr_data[ $slug ] .= '<p>' . nl2br( htmlspecialchars( $valuation) ) . '</p>';
                        break;

                        case 'datepicker':
                        case 'text':
                            $arr_data[ $slug ] .= htmlspecialchars( $valuation );
                        break;
                    }
                }

                $arr_data[ $slug ] .= $endDiv;
            }

            return $arr_data;
        }


        /**
         * Get items
         */
        function get_items( $format = false, $order = '' ) {
            global $wpdb;
            $items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_invoicing_items $order", ARRAY_A );

            if ( $format ) {
                foreach ( $items as $key => $item ) {
                    $new_rate = ( !empty( $item['rate'] ) ) ? $item['rate'] : 0;
                    $items[ $key ]['rate'] = $this->get_currency( $new_rate, true );
                }
            }
            return $items;
        }

        /**
         * Get items
         */
        function get_taxes() {
            $taxes = array();
            $wpc_invoicing = WPC()->get_settings( 'invoicing' );
            if( isset( $wpc_invoicing['taxes'] ) ) {
                $cols = $wpc_invoicing['taxes'];
                $i = 0;
                foreach ( $cols as $key => $value ) {
                    $i++;
                    $value['id'] = $i;
                    $value['name'] = $key;
                    $taxes[] = $value;
                }
            }

            return $taxes;
        }


        /**
         * make correct number format
         *
         * @deprecated 1.4.8
         * @deprecated Use get_number_format()
         */
        function get_number_format( $number, $pref = '', $custom_number = '', $type = 'inv' ) {
            if ( $custom_number ) {
                return $number;
            }

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            $ending = ( 'est' == $type ) ? '_est' : '' ;

            if ( !isset( $wpc_invoicing['display_zeros' . $ending] ) || 'yes' == $wpc_invoicing['display_zeros' . $ending] ) {
                if ( !isset( $wpc_invoicing['digits_count' . $ending] )
                        || !is_numeric( $wpc_invoicing['digits_count' . $ending] )
                        || 3 > $wpc_invoicing['digits_count' . $ending] ) {
                    $number = str_pad( $number, 8, '0', STR_PAD_LEFT );
                } else {
                    $number = str_pad( $number, $wpc_invoicing['digits_count' . $ending], '0', STR_PAD_LEFT );
                }
            }

            return $pref . $number;
        }

        /**
         * get already paid amount
         */
        function get_amount_paid( $invoice_id ) {
            global $wpc_payments_core;

            $amount_paid = 0;

            $inv = $this->get_data( $invoice_id );

            if ( isset( $inv['order_id'] ) && is_array( $inv['order_id'] ) ) {
                $orders = $wpc_payments_core->get_orders( $inv['order_id'] );

                if ( is_array( $orders ) && $orders ) {
                    foreach( $orders as $order ) {
                        if ( isset($order['order_status'] ) && ( 'paid' == $order['order_status'] || 'order_paid' == $order['order_status'] ) ) {
                            $amount_paid += $order['amount'];
                        }
                    }
                }
            }

            return $amount_paid;
        }


        /**
         * Get items
         */
        function get_data( $id, $type = '' ) {
            global $wpdb;

            if ( 'repeat_invoice' == $type ) {
                $data = $wpdb->get_row( $wpdb->prepare( "SELECT p.ID as id, p.post_title as title, p.post_content as description, pm1.meta_value as type, p.post_date as date, p.post_status as status
                                FROM {$wpdb->posts} p
                                LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_post_type' )
                                WHERE p.ID = %d
                            ", $id ), 'ARRAY_A' );

                $data['clients_id'] = implode( ',', WPC()->assigns()->get_assign_data_by_object( 'repeat_invoice', $id, 'client' ) ) ;
                $data['groups_id'] = implode( ',', WPC()->assigns()->get_assign_data_by_object( 'repeat_invoice', $id, 'circle' ) ) ;

            } else {
                $data = $wpdb->get_row( $wpdb->prepare( "SELECT p.ID as id, p.post_title as title, p.post_content as description, pm1.meta_value as type, coa.assign_id as client_id, p.post_date as date, p.post_status as status
                                FROM {$wpdb->posts} p
                                LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_post_type' )
                                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type IN ( 'invoice', 'estimate', 'accum_invoice', 'request_estimate' ) )
                                WHERE p.ID = %d
                            ", $id ), 'ARRAY_A' );
            }
            if ( $data ) {
                $all_meta_data = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d ", $id ), 'ARRAY_A' );
                $all_data = array();
                foreach ( $all_meta_data as $value ) {
                    $new_name_key = str_replace( 'wpc_inv_', '', $value['meta_key'] );
                    $all_data[ $new_name_key ] = $value['meta_value'];
                }
                $data = array_merge( $data, $all_data );

                if ( isset( $data['order_id'] ) && '' != $data['order_id'] ) {
                    $data['order_id'] = unserialize( $data['order_id'] );
                }

                if ( !empty( $data['custom_fields'] ) ) {
                    $data['custom_fields'] = unserialize( $data['custom_fields'] );
                }

                if ( !empty( $data['invoice_cf'] ) ) {
                    $data['invoice_cf'] = unserialize( $data['invoice_cf'] );
                }

                if ( isset( $data['description'] ) ) {
                    $data['description'] = stripslashes( $data['description'] );
                }

                if ( isset( $data['terms'] ) ) {
                    $data['terms'] = stripslashes( $data['terms'] );
                }

                if ( isset( $data['note'] ) ) {
                    $data['note'] = stripslashes( $data['note'] );
                }

                if ( !empty( $data['notes'] ) ) {
                    $data['notes'] = unserialize( $data['notes'] );
                }

                if ( empty( $data['items'] ) ) {
                    $data['items'] = 'a:0:{}';
                }

                if ( empty( $data['taxes'] ) ) {
                    $data['taxes'] = 'a:0:{}';
                }

                if ( empty( $data['discounts'] ) ) {
                    $data['discounts'] = 'a:0:{}';
                }

                if ( empty( $data['late_fee'] ) ) {
                    $data['late_fee'] = 0;
                }

            }

            return $data;
        }

        function create_inv_from_profile( $id_invoice_profile, $post_status = '', $only_client_id = '' ) {
            global $wpdb;

            $data = $this->get_data( $id_invoice_profile );

            $change_count = 0;
            if ( !isset( $data['total'] ) || 0 >= $data['total'] ) {
                $change_count = -1;
            }

            $count_created = ( isset( $data['count_created'] ) ) ? $data['count_created'] + 1 : 1 ;
            $count_created += $change_count;
            update_post_meta( $data['id'], 'wpc_inv_count_created', $count_created );

            $billing_period = ( isset( $data['billing_period'] ) ) ? $data['billing_period'] : 'day';
            $billing_every = ( isset( $data['billing_every'] ) ) ? $data['billing_every'] : 1 ;
            $time_create_inv = ( isset( $data['next_create_inv'] ) ) ? $data['next_create_inv'] : '';
            if ( $time_create_inv && ( !isset( $data['billing_cycle'] ) || $count_created < $data['billing_cycle'] ) ) {
                switch( $billing_period ) {
                    case 'week':
                        $next_create_inv = strtotime( "+$billing_every week", $time_create_inv );
                        break;
                    case 'month':
                        if ( isset( $data['last_day_month'] ) ) {
                            $next_create_inv = strtotime( date( "Y-m-d", strtotime( "last day of next month" ) ) . " 00:00:00" ) ;
                        } else {
                            $next_create_inv = strtotime( "+$billing_every month", $time_create_inv );
                        }
                        break;
                    case 'year':
                        $next_create_inv = strtotime( "+$billing_every year", $time_create_inv );
                        break;
                    case 'day':
                    default:
                        $next_create_inv = strtotime( "+$billing_every day", $time_create_inv );
                        break;
                }
            }

            if ( isset( $next_create_inv ) ) {
                $wpdb->update( $wpdb->posts, array( 'post_status' => 'active' ), array( 'ID' => $data['id'] ), array( '%s' ), array( '%d' ) );
                update_post_meta( $data['id'], 'wpc_inv_next_create_inv', $next_create_inv );
                $gmt_offset =  get_option( 'gmt_offset' );
                if ( false !== $gmt_offset ) {
                    $next_from_date = $next_create_inv + $gmt_offset * 3600;
                }
                update_post_meta( $data['id'], 'wpc_inv_from_date', date( "m/d/Y", $next_from_date ) );
            } else {
                if ( 'auto_charge' == $data['recurring_type'] ) {
                    $wpdb->update( $wpdb->posts, array( 'post_status' => 'active' ), array( 'ID' => $data['id'] ), array( '%s' ), array( '%d' ) );
                } else {
                    $wpdb->update( $wpdb->posts, array( 'post_status' => 'ended' ), array( 'ID' => $data['id'] ), array( '%s' ), array( '%d' ) );
                }
                delete_post_meta( $data['id'], 'wpc_inv_next_create_inv' );
                delete_post_meta( $data['id'], 'wpc_inv_from_date' );
            }

            if ( '' != $only_client_id ) {
                $data['client_id'] = $only_client_id ;
            }

            $data['items']      = ( !empty( $data['items'] ) ) ? unserialize( $data['items'] ) : array();
            $data['discounts']  = ( !empty( $data['discounts'] ) ) ? unserialize( $data['discounts'] ) : array();
            $data['taxes']      = ( !empty( $data['taxes'] ) ) ? unserialize( $data['taxes'] ) : array();
            $data['cc_emails']  = ( !empty( $data['cc_emails'] ) ) ? unserialize( $data['cc_emails'] ) : array();


            if ( isset( $data['type'] ) && 'repeat_inv' == $data['type'] && !$only_client_id ) {
                $clients_id = WPC()->assigns()->get_assign_data_by_object( 'repeat_invoice', $data['id'], 'client' );
                $groups_id = WPC()->assigns()->get_assign_data_by_object( 'repeat_invoice', $data['id'], 'circle' );

                $clients_of_groups = array();

                foreach( $groups_id as $group_id ) {
                    $clients_of_groups = array_merge( $clients_of_groups, WPC()->groups()->get_group_clients_id( $group_id ) );
                }

                $clients_id = array_unique( array_merge( $clients_id, $clients_of_groups ) ) ;
            } else {
                $clients_id = array( $data['client_id'] );
            }

            foreach ( $clients_id as $client_id ) {
                $data['client_id'] = $client_id;
                $id = $this->create_inv( $data, $post_status );

                if( !$id ) {
                    continue;
                }

                if ( 'repeat_inv' == $data['type'] && 'auto_charge' == $data['recurring_type'] ) {
                    update_post_meta( $id, 'wpc_inv_recurring_type', true );

                    if ( isset( $data['billing_every'] ) ) {
                        update_post_meta( $id, 'wpc_inv_billing_every', $data['billing_every'] );
                    }
                    if ( isset( $data['billing_cycle'] ) ) {
                        update_post_meta( $id, 'wpc_inv_billing_cycle', $data['billing_cycle'] );
                    }
                    if ( isset( $data['billing_period'] ) ) {
                        update_post_meta( $id, 'wpc_inv_billing_period', $data['billing_period'] );
                    }
                }

            }

            if ( 'accum_inv' == $data['type'] ) {

                $items = array();
                $new_value = 0;

                update_post_meta( $data['id'], 'wpc_inv_total', $new_value );
                update_post_meta( $data['id'], 'wpc_inv_sub_total', $new_value );
                update_post_meta( $data['id'], 'wpc_inv_total_tax', $new_value );
                update_post_meta( $data['id'], 'wpc_inv_items', $items );

                if ( isset( $data['not_delete_discounts'] ) && !$data['not_delete_discounts'] ) {
                    update_post_meta( $data['id'], 'wpc_inv_discounts', $items );
                }

                if ( isset( $data['not_delete_taxes'] ) && !$data['not_delete_taxes'] ) {
                    update_post_meta( $data['id'], 'wpc_inv_taxes', $items );
                }
            } else if ( 'auto_charge' == $data['recurring_type'] ) {
                $clients_info = get_post_meta( $id_invoice_profile, 'wpc_inv_clients_info', true );
                foreach ( $clients_id as $client_id ) {
                    if ( empty( $clients_info[ $client_id ] ) ) {
                        $clients_info[ $client_id ] = 'pending';
                    }
                }
                update_post_meta($id_invoice_profile, 'wpc_inv_clients_info', $clients_info );
            }

            if ( empty( $id ) ) {
                $id = 0;
            }
            return $id;

        }


        function create_inv( $data, $post_status = '' ) {
            global $wpdb;

            if ( !isset( $data['items'] ) || !is_array( $data['items'] ) || 0 >= count( $data['items'] ) ) {
                return false;
            }

            $items = $data['items'];

            if( isset( $data['sub_total'] ) ) {
                $sub_total = $data['sub_total'];
            } else {
                $sub_total = 0;
                if( is_array( $items ) ) {
                    foreach( $items as $item ) {
                        if ( isset( $item['price'] ) && isset( $item['quantity'] ) ) {
                            $sub_total += $item['price'] * $item['quantity'];
                        }
                    }
                }
            }

            if ( 0 == $sub_total ) {
                return false;
            }

            if( isset( $data['total_discount'] ) ) {
                $total_discount = $data['total_discount'];
            } else {
                $total_discount = 0;
                if ( isset( $data['discounts'] ) && is_array( $data['discounts'] ) ) {
                    foreach ( $data['discounts'] as $disc ) {
                        if( isset( $disc['total'] ) &&  0 < (float)$disc['total'] ) {
                            $total_discount += (float)$disc['total'];
                        }
                    }
                }
            }

            if( isset( $data['total_tax'] ) ) {
                $total_tax = $data['total_tax'];
            } else {
                $total_tax = 0;
                if ( isset( $data['taxes'] ) && is_array( $data['taxes'] ) ) {
                    foreach ( $data['taxes'] as $tax ) {
                        if( isset( $tax['total'] ) &&  0 < (float)$tax['total'] ) {
                            $total_tax += (float)$tax['total'];
                        }
                    }
                }
            }

            $data['sub_total'] = $sub_total ;
            $data['total_discount'] = $total_discount ;
            $data['total_tax'] = $total_tax ;
            $data['total'] = ( isset( $data['total'] ) ) ? $data['total'] : $sub_total - $total_discount + $total_tax ;
            if ( !isset( $data['currency'] ) ) {
                $wpc_currency = WPC()->get_settings( 'currency' );
                foreach( $wpc_currency as $key => $value ) {
                    if( 1 == $value['default'] ) {
                        $currency = $key;
                        break;
                    }
                }
                $data['currency'] = ( isset( $currency ) ) ? $currency : '' ;
            }

            //get client id
            if ( isset( $data['client_id'] ) ) {
                $client_id = $data['client_id'] ;
            } else {
                return false;
            }

            $data['due_date'] = '';
            if ( isset( $data['due_date_number'] ) && (int)$data['due_date_number'] ) {
                $days = (int)$data['due_date_number'];
                $data['due_date'] = strtotime( date("m/d/Y", mktime(0,0,0,date("m"), (date("d") + $days ), date("Y") )) . ' ' . date( 'H:i:s' ) );
            }

            $inv_number = ( isset( $data['inv_number'] ) ) ? $data['inv_number'] : '';

            $date = date( "Y-m-d H:i:s");

            if ( '' == $post_status ) {
                if ( isset( $data['recurring_type'] ) && 'invoice_draft' == $data['recurring_type'] || isset( $data['accum_type'] ) && 'invoice_draft' == $data['accum_type']  ) {
                    $post_status = 'draft';
                } elseif ( isset( $data['send_email_on_creation'] ) ) {
                    $post_status = 'sent';
                } else {
                    $post_status = 'open';
                }
            }

            kses_remove_filters();// for not transformation & to &amp;
            $new_post = array(
                'post_title'       => ( isset( $data['title'] ) ) ? $this->replace_date_placeholder( $data['title'] ) : '',
                'post_content'     => ( isset( $data['description'] ) ) ? $data['description'] : '',
                'post_status'      => $post_status,
                'post_type'        => 'wpc_invoice',
                'post_date'        => $date,
                //'post_author'      => $all_clients_id[0],
            );

            $id = wp_insert_post( $new_post  );

            update_post_meta( $id, 'wpc_inv_post_type', 'inv' );

            $i = 0;

            //get new number
            if ( '' == $inv_number ) {
                $number = $this->get_next_number();
            } else {
                do {
                    $i++;
                    $number = $inv_number . '-' . $i;
                    $yes = $wpdb->get_var( $wpdb->prepare( "SELECT pm.meta_value
                        FROM {$wpdb->posts} p
                        INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                        LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_number' )
                        WHERE ( p.post_type = 'wpc_invoice' ) AND pm.meta_value='%s'", $number ) );
                } while( $yes ) ;
            }

            update_post_meta( $id, 'wpc_inv_number', $number );


            if ( isset( $data['deposit'] ) ) {
                update_post_meta( $id, 'wpc_inv_deposit', $data['deposit'] );
                if ( isset( $data['min_deposit'] ) && 0 < (float)$data['min_deposit'] ) {
                    update_post_meta( $id, 'wpc_inv_min_deposit',(float)$data['min_deposit'] );
                }
            }


            update_post_meta( $id, 'wpc_inv_items', $data['items'] );

            if ( isset( $data['id'] ) ) {
                update_post_meta( $id, 'wpc_inv_parrent_id', $data['id'] );
            }

            if ( isset( $data['type'] ) ) {
                update_post_meta( $id, 'wpc_inv_parent_type', $data['type'] );
            }

            if ( isset( $data['late_fee'] ) ) {
                update_post_meta( $id, 'wpc_inv_late_fee', $data['late_fee'] );
            }

            if ( isset( $data['discounts'] ) ) {
                update_post_meta( $id, 'wpc_inv_discounts', $data['discounts'] );
            }

            if ( isset( $data['taxes'] ) ) {
                update_post_meta( $id, 'wpc_inv_taxes', $data['taxes'] );
            }

            update_post_meta( $id, 'wpc_inv_sub_total', $data['sub_total'] );
            update_post_meta( $id, 'wpc_inv_total_discount', $data['total_discount'] );
            update_post_meta( $id, 'wpc_inv_total_tax', $data['total_tax'] );
            update_post_meta( $id, 'wpc_inv_total', $data['total'] );
            update_post_meta( $id, 'wpc_inv_currency', $data['currency'] );

            if ( isset( $data['note'] ) ) {
                update_post_meta( $id, 'wpc_inv_note', $data['note'] );
            }

            if ( isset( $data['terms'] ) ) {
                update_post_meta( $id, 'wpc_inv_terms', $data['terms'] );
            }


            if ( isset( $data['cc_emails'] ) ) {
                update_post_meta( $id, 'wpc_inv_cc_emails', $data['cc_emails'] );
            }

            if ( isset( $data['custom_fields'] ) ) {
                update_post_meta( $id, 'wpc_inv_custom_fields', $data['custom_fields'] );
            }

            if ( isset( $data['due_date'] ) ) {
                update_post_meta( $id, 'wpc_inv_due_date', $data['due_date'] );
            }

            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns SET
                object_type     = 'invoice',
                object_id       = '%d',
                assign_type     = 'client',
                assign_id       = '%d'
                ",
                $id,
                $client_id

            ));

            if ( 'sent' == $post_status ) {
                $this->send_invoice( $id );
            }

            return $id;
        }


        function include_pdf() {
            if( !class_exists( 'Dompdf' ) ) {

                $uploads            = wp_upload_dir();
                $wpc_target_path    = $uploads['basedir'] . '/wpclient/_pdf_temp';

                if ( !is_dir( $wpc_target_path ) ) {
                    //create uploads dir
                    mkdir( $wpc_target_path, 0777 );
                }

                include( WPC()->plugin_dir . 'includes/libs/pdf/autoload.inc.php' );
            }
        }


        /**
         * send invoice
         */
        function send_invoice( $id ) {

            $invoice_ids = ( is_array( $id ) ) ? $id : (array) $id;

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            //send email to client
            foreach ( $invoice_ids as $invoice_id ) {
                //get data
                $inv = $this->get_data( $invoice_id );
                if ( 0 < $inv['client_id'] ) {
                    ob_start();

                    $content = $this->invoicing_put_values( $inv );

                    $with_pdf = empty( $wpc_invoicing['attach_pdf'] ) || 'no' !== $wpc_invoicing['attach_pdf'];

                    $attachments = array();
                    if ( $with_pdf  ) {
                        $attachment = $this->get_pdf( $inv, $content );
                        $attachments[] = $attachment;
                    }

                    ob_end_clean();

                    $userdata = get_userdata( $inv['client_id'] );

                    $payment_method = '';
                    if ( isset( $inv['order_id'] ) && is_array( $inv['order_id'] ) ) {
                        $payment_method = $this->get_payment_method( $inv['order_id'] );
                    }

                    $args = array(
                        'invoice_id' => $inv['id'],
                        'client_id' => $inv['client_id'],
                        'invoicing_title' => $inv['title'],
                        'inv_number' => $inv['number'],
                        'total_amount' => isset( $inv['total'] ) ? $inv['total'] : '',
                        'minimum_payment' => isset( $inv['min_deposit'] ) ? $inv['min_deposit'] : '',
                        'due_date' => isset( $inv['due_date'] ) ? WPC()->date_format( $inv['due_date'], 'date' ) : '',
                        'payment_method' => $payment_method,
                    );

                    //send email
                    $emails = array();
                    $emails[] = $userdata->get( 'user_email' );

                    $cc_emails = get_post_meta( $invoice_id, 'wpc_inv_cc_emails', true );

                    if ( is_array( $cc_emails ) && count( $cc_emails ) ) {
                        $emails = array_merge( $emails, $cc_emails );
                    }

                    foreach( $emails as $email ) {
                        if ( is_email( $email ) ) {
                            WPC()->mail( 'inv_not', $email, $args, 'invoice_notify', $attachments );
                        }
                    }

                    if ( $with_pdf ) {
                        unlink( $attachment );
                    }
                }
            }

        }


        /**
         * Generate PDF for attach or download
         *
         * @global object $wpc_client
         * @param array $data data of invoice or estimate
         * @param string $content content of pdf
         * @param bool $attach for attach or download
         * @return string full path
         */
        function get_pdf( $data, $content, $attach = true ) {

            $type = $data['type'];
            $uploads        = wp_upload_dir();
            $target_path    = $uploads['basedir'] . "/wpclient/_$type/";
            $wpc_invoicing  = WPC()->get_settings( 'invoicing' );

            if ( !ini_get( 'safe_mode' ) ) {
                $temp_memory_limit          = ini_get( "memory_limit" );
                $temp_max_execution_time    = ini_get( "max_execution_time" );
                ini_set( "memory_limit", "999M" );
                ini_set( "max_execution_time", "999" );
            }

            $pdf_name = isset( $wpc_invoicing[ $type . '_filename'] )
                ? $wpc_invoicing[ $type . '_filename']
                : $type . '_{number_' . $type . '}';
            $pdf_name_args = array(
                '{number_' . $type . '}'
                    => $data['number'],
            );

            if ( !empty( $data['date'] ) ) {
                $date = strtotime( $data['date'] );
                $pdf_name_args['{Y}'] = date( "Y", $date);
                $pdf_name_args['{d}'] = date( "d", $date);
                $pdf_name_args['{m}'] = date( "m", $date);
            }

            $pdf_name = str_replace( array_keys( $pdf_name_args ), $pdf_name_args, $pdf_name );
            $pdf_name = str_replace( '/', '_', $pdf_name ) . '.pdf';

            $html_header = '<html><head><meta content="text/html; charset=UTF-8" http-equiv="Content-Type"></head><body>';
            $html_footer = '</body></html>';

            $this->include_pdf();
            $options = new Options();
            $options->set('isRemoteEnabled', TRUE);
            $dompdf = new Dompdf( $options );
            $dompdf->loadHtml( $html_header . $content . $html_footer );
            $dompdf->setPaper( 'A4' , 'portrait' );
            $dompdf->render();

            if ( !$attach ) {
                $dompdf->stream( $pdf_name );
                exit;
            }

            $pdf = $dompdf->output();

            if ( isset( $temp_memory_limit ) && isset( $temp_max_execution_time ) ) {
                ini_set( "memory_limit", $temp_memory_limit );
                ini_set( "max_execution_time", $temp_max_execution_time );
            }

            if( !is_dir( $target_path ) ) {
                mkdir( $target_path, 0777);
            }

            $htp = fopen( $target_path . $pdf_name, 'w' );

            fputs( $htp, $pdf );
            return $target_path . $pdf_name;
        }


        /**
         * send estimate
         */
        function send_estimate( $id ) {

            $estimate_ids = ( is_array( $id ) ) ? $id : (array) $id;

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            //send email to client
            foreach ( $estimate_ids as $estimate_id) {
                //get data
                $est = $this->get_data( $estimate_id );
                if ( 0 < $est['client_id'] ) {

                    ob_start();

                    $content = $this->invoicing_put_values( $est );

                    $with_pdf = empty( $wpc_invoicing['attach_pdf'] ) || 'no' !== $wpc_invoicing['attach_pdf'];

                    $attachments = array();
                    if ( $with_pdf  ) {
                        $attachment = $this->get_pdf( $est, $content );
                        $attachments[] = $attachment;
                    }

                    ob_end_clean();


                    $userdata = get_userdata( $est['client_id'] );

                    $args = array(
                        'client_id' => $est['client_id'],
                        'invoicing_title' => $est['title'],
                        'inv_number' => $est['number'],
                        'total_amount' => isset( $est['total'] ) ? $est['total'] : '',
                        'minimum_payment' => isset( $est['min_deposit'] ) ? $est['min_deposit'] : '',
                        'due_date' => isset( $est['due_date'] ) ? WPC()->date_format( $est['due_date'], 'date' ) : '',
                        );

                    //send email
                    $emails = array();
                    $emails[] = $userdata->get( 'user_email' );

                    $cc_emails = get_post_meta( $estimate_id, 'wpc_inv_cc_emails', true );

                    if ( is_array( $cc_emails ) && count( $cc_emails ) ) {
                        $emails = array_merge( $emails, $cc_emails );
                    }

                    foreach( $emails as $email ) {
                        if ( is_email( $email ) ) {
                            WPC()->mail( 'est_not', $email, $args, 'estimate_notify', $attachments );
                        }
                    }

                    if ( $with_pdf ) {
                        unlink( $attachment );
                    }

                }
            }

        }


        /**
         * Get next inv\est number
         */
        function get_next_number( $increase = true, $type = 'inv' ) {
            global $wpdb;

            if ( 'est' !== $type ) {
                $type =  'inv' ;
            }

            $ending = ( 'est' == $type ) ? '_est' : '' ;

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            if ( empty( $wpc_invoicing['next_number' . $ending] ) ) {
                $next_number = 1;

                $number = $wpdb->get_var( "SELECT MAX(pm.meta_value)
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = '{$type}' )
                    LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_number' ) WHERE ( p.post_type = 'wpc_invoice' ) " );
                if ( $number ) {
                    $next_number = $number;
                }
            } else {
                $next_number = $wpc_invoicing['next_number' . $ending];
            }

            if ( $increase ) {
                $count_zeros = 0;
                if ( !isset( $wpc_invoicing['display_zeros' . $ending] ) || 'yes' == $wpc_invoicing['display_zeros' . $ending] ) {
                    if ( !isset( $wpc_invoicing['digits_count' . $ending] )
                            || !is_numeric( $wpc_invoicing['digits_count' . $ending] )
                            || 3 > $wpc_invoicing['digits_count' . $ending] ) {
                        $count_zeros = 8;
                    } else {
                        $count_zeros = $wpc_invoicing['digits_count' . $ending];
                    }
                }

                $pref = isset( $wpc_invoicing['prefix' . $ending] ) ? $this->replace_date_placeholder( $wpc_invoicing['prefix' . $ending] ) : '';

                $isset_number = true;
                while( $isset_number )  {
                    $number = $pref . str_pad( $next_number, $count_zeros, '0', STR_PAD_LEFT );

                    $isset_number = $wpdb->get_var( $wpdb->prepare( "SELECT pm.meta_value
                        FROM {$wpdb->posts} p
                        INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = '{$type}' )
                        LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_number' )
                        WHERE ( p.post_type = 'wpc_invoice' ) AND pm.meta_value='%s'", $number ) );
                    $next_number++;
                }
                $next_number--;

                $wpc_invoicing['next_number' . $ending] = $next_number + 1;

                $next_number = $number;

            }

            update_option( 'wpc_invoicing', $wpc_invoicing );


            return $next_number;
        }


        /**
         * get Continue link after payment
         **/
        function get_continue_link( $link, $order, $with_text = true ) {

            $data = json_decode( $order['data'], true );

            //get client INV
            $inv = $this->get_data( $data['invoice_id'] ) ;

            if ( $inv ) {
                //make link
                $url = $this->get_invoice_link( $inv['id'] );

                if ( $with_text ) {
                    $link = sprintf( __( 'To continue click <a href="%s">here</a>.', WPC_CLIENT_TEXT_DOMAIN ), $url );
                } else {
                    $link = $url;
                }
            }

            return $link;
        }


        /**
         * get active payment gateways
         **/
        function get_active_payment_gateways( $gateways ) {

            $wpc_gateways = WPC()->get_settings( 'gateways' );
            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            if( isset( $wpc_gateways['allowed'] ) && is_array( $wpc_gateways['allowed'] ) ) {
                foreach( $wpc_gateways['allowed'] as $value ) {
                    if( !( isset( $wpc_invoicing['gateways'][ $value ] ) && $wpc_invoicing['gateways'][ $value ] == 0 ) ) {
                        $gateways[] = $value;
                    }
                }
            }

            return $gateways;
        }


        /**
         * order subscription_start
         **/
        function subscription_canceled( $order ) {
            $client_id = ( !empty( $order['client_id'] ) ) ? $order['client_id'] : 0 ;
            $profile_id = 0;
            if ( !empty( $order['data'] ) ) {
                $data = json_decode( $order['data'], true ) ;
                $profile_id = ( !empty( $data['profile_id'] ) ) ? $data['profile_id'] : 0 ;
            }

            if ( $client_id && $profile_id ) {
                $meta_key = 'wpc_inv_clients_info';
                $clients_info = get_post_meta( $profile_id, $meta_key, true );
                if ( is_array( $clients_info ) ) {
                    $clients_info[ $client_id ] = 'canceled';
                    update_post_meta( $profile_id, $meta_key, $clients_info );
                }
            }

        }


        /**
         * Get count payments
         *
         * @global object $wpdb
         * @param int $profile_id
         * @param int $client_id
         * @param optional string $status
         * @return int
         */
        function get_count_payments( $profile_id, $client_id, $status = '' ) {
            global $wpdb;
            if ( '' == $status ) {
                $array_statuses = array( 'active', 'expired', 'canceled' );
                $where_status = " AND `subscription_status` IN ('" . implode( "','", $array_statuses ) . "')";
            } else {
                $where_status = " AND `subscription_status` = '" . $status . "'" ;
            }

            $count_payments = $wpdb->get_var( "SELECT COUNT(*) "
                . "FROM {$wpdb->prefix}wpc_client_payments "
                . "WHERE `data` LIKE '%\"profile_id\":\"{$profile_id}\"%' "
                . "AND `client_id` = '{$client_id}'"
                . $where_status );

            return $count_payments;
        }


        /**
         * order subscription_start
         **/
        function subscription_expired( $order ) {
            $client_id = ( !empty( $order['client_id'] ) ) ? $order['client_id'] : 0 ;
            $profile_id = 0;
            if ( !empty( $order['data'] ) ) {
                $data = json_decode( $order['data'], true ) ;
                $profile_id = ( !empty( $data['profile_id'] ) ) ? $data['profile_id'] : 0 ;
            }

            $expire = $this->check_expire_profile( '', $order );

            if ( $expire && $client_id && $profile_id ) {
                $meta_key = 'wpc_inv_clients_info';
                $clients_info = get_post_meta( $profile_id, $meta_key, true );
                if ( is_array( $clients_info ) ) {
                    $clients_info[ $client_id ] = 'expired';
                    update_post_meta( $profile_id, $meta_key, $clients_info );
                }
            }
        }


        /**
         * order subscription_start
         **/
        function order_subscription_start( $order ) {
            global $wpdb;

            $data = json_decode( $order['data'], true );
            //get client INV
            $inv = $this->get_data( $data['invoice_id'] ) ;

            if ( $inv ) {
                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                post_status = 'pending'
                WHERE id = '%d' AND post_status != 'paid'
                ",
                $inv['id']
                ));
            }
        }


        /**
         * order subscription
         **/
        function order_subscription( $order ) {
            global $wpdb;

            $data = json_decode( $order['data'], true );

            if ( !empty( $order['client_id'] ) && !empty( $data['profile_id'] ) ) {
                $meta_key = 'wpc_inv_clients_info';
                $clients_info = get_post_meta( $data['profile_id'], $meta_key, true );
                $clients_info[ $order['client_id'] ] = 'active';
                update_post_meta( $data['profile_id'], $meta_key, $clients_info );
            }

            //get client INV

            if ( isset( $data['profile_id'] ) ) {
                $inv = $this->get_data( $data['profile_id'] ) ;

                $orders = get_post_meta( $data['invoice_id'], 'wpc_inv_order_id', true ) ;

                if ( $orders ) {
                    $inv_id = $this->create_inv_from_profile( $data['profile_id'], 'paid', $order['client_id'] ) ;
                } else {
                    $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                        post_status = 'paid'
                        WHERE id = '%d'
                        ",
                        $data['invoice_id']
                    ));

                    $inv_id = $data['invoice_id'] ;
                }

                if ( $inv ) {

                    update_post_meta( $inv_id, 'wpc_inv_order_id', (array) $order['id'] ) ;

                    $this->change_status_expired( $data['profile_id'] );

                    //send email to admin
                    $wpc_invoicing = WPC()->get_settings( 'invoicing' );

                    //notify selected
                    if ( isset( $wpc_invoicing['notify_payment_made'] ) && 'yes' == $wpc_invoicing['notify_payment_made'] ) {

                        $inv_data = $this->get_data( $inv_id ) ;
                        $args = array(
                            'invoice_id' => $inv_id,
                            'client_id' => $order['client_id'],
                            'invoicing_title' => $inv['title'],
                            'inv_number' => isset( $inv_data['number'] ) ? $inv_data['number'] : '',
                            'total_amount' => isset( $order['amount'] ) ? $order['amount'] : '',
                            'minimum_payment' => isset( $inv_data['min_deposit'] ) ? $inv_data['min_deposit'] : '',
                            'due_date' => isset( $inv_data['due_date'] ) ? WPC()->date_format( $inv_data['due_date'], 'date' ) : '',
                            'payment_method' => isset( $order['payment_method'] ) ? $this->get_payment_method( 0, $order['payment_method'] ) : '',
                        );

                        $emails_array = $this->get_emails_of_admins();
                        foreach( $emails_array as $to_email ) {
                            WPC()->mail( 'admin_notify', $to_email, $args, 'invoice_notify_admin' );
                        }
                    }
                }
            } else {
                //for old recurring invoice, may optimize this code
                $inv = $this->get_data( $data['invoice_id'] ) ;

                if ( $inv ) {
                    $orders = get_post_meta( $inv['id'], 'wpc_inv_order_id', true );
                    if ( is_array( $orders ) ) {
                        $orders[] = $order['id'];
                    } else {
                        $orders = array( $order['id'] );
                    }

                    update_post_meta( $inv['id'], 'wpc_inv_order_id', $orders );

                    if ( isset( $inv['recurring_type'] ) || isset( $inv['accum_type'] ) ) {
                        global $wpc_payments_core;
                        $orders = $wpc_payments_core->get_orders( $inv['order_id'] );

                        if ( isset( $inv['billing_cycle']  ) && 0 == $inv['billing_cycle'] - count( $orders ) ) {
                            $status = 'expired';
                        } else {
                            $status = 'active';
                        }
                    } else {
                        $paid_total = $this->get_amount_paid( $inv['id'] );

                        if ( 0 == $inv['total'] - $paid_total ) {
                            $status = 'paid';
                        } else {
                            $status = 'partial';
                        }
                    }


                    $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                        post_status = '%s'
                        WHERE id = '%d'
                        ",
                        $status,
                        $inv['id']
                    ));

                    //send email to admin
                    $wpc_invoicing = WPC()->get_settings( 'invoicing' );

                    //notify selected
                    if ( isset( $wpc_invoicing['notify_payment_made'] ) && 'yes' == $wpc_invoicing['notify_payment_made'] ) {

                        $args = array(
                            'invoice_id' => $inv['id'],
                            'client_id' => $inv['client_id'],
                            'invoicing_title' => $inv['title'],
                            'inv_number' => $inv['number'],
                            'total_amount' => isset( $order['amount'] ) ? $order['amount'] : '',
                            'minimum_payment' => isset( $inv['min_deposit'] ) ? $inv['min_deposit'] : '',
                            'due_date' => isset( $inv['due_date'] ) ? WPC()->date_format( $inv['due_date'], 'date' ) : '',
                            'payment_method' => isset( $order['payment_method'] ) ? $this->get_payment_method( 0, $order['payment_method'] ) : '',
                        );

                        $emails_array = $this->get_emails_of_admins();
                        foreach( $emails_array as $to_email ) {
                            WPC()->mail( 'admin_notify', $to_email, $args, 'invoice_notify_admin' );
                        }
                    }
                }
            }

        }



        /**
         * order paid
         **/
        function order_paid( $order ) {
            global $wpdb;

            $data = json_decode( $order['data'], true );

            //get client INV
            $inv = $this->get_data( $data['invoice_id'] ) ;

            if ( $inv ) {
                $orders = get_post_meta( $inv['id'], 'wpc_inv_order_id', true );
                if ( is_array( $orders ) ) {
                    $orders[] = $order['id'];
                } else {
                    $orders = array( $order['id'] );
                }

                update_post_meta( $inv['id'], 'wpc_inv_order_id', $orders );

                    $paid_total = $this->get_amount_paid( $inv['id'] );

                    if ( 0 == $inv['total'] - $paid_total ) {
                        $status = 'paid';
                    } else {
                        $status = 'partial';
                    }


                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                    post_status = '%s'
                    WHERE id = '%d'
                    ",
                    $status,
                    $inv['id']
                ));

                //send email to admin
                $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                $payment_method = !empty( $order['payment_method'] ) ? $this->get_payment_method( 0, $order['payment_method'] ) : '';
                $args = array(
                    'invoice_id' => $inv['id'],
                    'client_id' => $inv['client_id'],
                    'invoicing_title' => $inv['title'],
                    'inv_number' => $inv['number'],
                    'total_amount' => isset( $inv['total'] ) ? $inv['total'] : '',
                    'due_date' => isset( $inv['due_date'] ) ? WPC()->date_format( $inv['due_date'], 'date' ) : '',
                    'minimum_payment' => isset( $inv['min_deposit'] ) ? $inv['min_deposit']: '',
                    'payment_method' => $payment_method,
                );

                //notify selected
                if ( isset( $wpc_invoicing['notify_payment_made'] ) && 'yes' == $wpc_invoicing['notify_payment_made'] ) {

                    $emails_array = $this->get_emails_of_admins();
                    foreach( $emails_array as $to_email ) {
                        WPC()->mail( 'admin_notify', $to_email, $args, 'invoice_notify_admin' );
                    }
                }

                if ( 'paid' == $status && isset( $inv['send_for_paid'] ) && 1 == $inv['send_for_paid'] ) {
                    $userdata = get_userdata( $inv['client_id'] );

                    $args = array_merge( $args , array(
                        'invoice_id' => $inv['id'],
                        'invoice_amount' => $order['amount'],
                        'invoice_date' => WPC()->date_format( $order['time_paid'] ),
                    ) );
                    //send email
                    WPC()->mail( 'pay_tha', $userdata->get( 'user_email' ), $args, 'invoice_thank_you' );
                }
            }

        }


        /**
        * Get array emails of admins
        *
        */
        function get_emails_of_admins() {
            $emails_array = array();
            //email to admins
            $args = array(
                'role'      => 'wpc_admin',
                'fields'    => array( 'user_email' )
            );
            $admin_emails = get_users( $args );
            if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                foreach( $admin_emails as $admin_email ) {
                     $emails_array[] = $admin_email->user_email;
                }
            }

            $emails_array[] = get_option( 'admin_email' );

            return $emails_array;
        }

        /**
         * Get title of invoice with replaced placeholder %CreationDateFormat%
         *
         * @global object $wpc_client
         * @param string $title
         * @param string $date
         */
        function replace_date_placeholder( $title ) {

            $pattern = '/%CreationDateFormat="([^"]*)"%/';
            $InvoiceDate = preg_match($pattern, $title, $format);
            if ($InvoiceDate) {
                $format = !empty($format[1]) ? $format[1] : '';
                $date = WPC()->date_format( time(), 'date', $format);
                $title = preg_replace($pattern, $date, $title);
            }

            return $title;
        }

        function get_pay_now_link( $invoice_id ) {
            return add_query_arg( array( 'pay_now' => '1' ), $this->get_invoice_link( $invoice_id ) );
        }

        function get_invoice_link( $invoice_id ) {
            if ( WPC()->permalinks ) {
                $link = WPC()->get_slug( 'invoicing_page_id' ) . $invoice_id;
            } else {
                $link = add_query_arg( array( 'wpc_page' => 'invoicing', 'wpc_page_value' => $invoice_id ), WPC()->get_slug( 'invoicing_page_id', false ) );
            }
            return $link;
        }

        /**
         * Register Custom Rout to cancel reminder
         */
        function rest_api_cancel_reminder() {
            register_rest_route( 'wpclient/inv', '/cancel-reminder/(?P<inv>\d+)/(?P<cancel_reminder>[A-Za-z0-9-]+)', array(
                'methods'   =>  'GET',
                'callback'  =>  array( &$this, 'rest_api_cancel_reminder_callback' ),
                'args'      => array(
                    'inv',
                    'cancel_reminder'
                )
            ) );
        }

        /**
         * Cancel Reminder
         * @param WP_REST_Request $request
         * @return WP_Error|WP_REST_Response
         */
        function rest_api_cancel_reminder_callback( WP_REST_Request $request ) {
            $error = new WP_Error();
            $inv_id = $request->get_param( 'inv' );
            $cancel_reminder = $request->get_param( 'cancel_reminder' );

            if ( empty( $cancel_reminder ) || empty( $inv_id ) ) {
                $error->add(400, __("Cancel Hash and INV ID are required.", 'wp-rest-user'), array('status' => 400));
                return $error;
            }

            if( get_post_meta( $inv_id, 'wpc_inv_cancel_reminder_hash', true ) == $cancel_reminder )
                update_post_meta( $inv_id, 'wpc_inv_cancel_repeat_reminder', 1 );

            $response['code'] = 200;
            $response['inv'] = $inv_id;
            $response['message'] = __("Invoice was Successfull unregister from reminder", WPC_CLIENT_TEXT_DOMAIN);


            return __("Invoice was Successfull unregister from reminder", WPC_CLIENT_TEXT_DOMAIN);
//            return new WP_REST_Response( $response );
        }

        function get_cancel_reminder_url( $invoice_id ) {

            $uniq_id = hash( 'sha256', random_bytes(8) );

            update_post_meta( $invoice_id, 'wpc_inv_cancel_reminder_hash', $uniq_id );
            return get_home_url(). "/wp-json/wpclient/inv/cancel-reminder/{$invoice_id}/{$uniq_id}";

        }


        /**
         * Add Send Invoice action link
         * @param $actions
         * @param $item
         * @return mixed
         */
        function filter_add_client_more_actions( $actions, $item ) {
            $actions['add_new_invoice'] = '<a href="admin.php?page=wpclients_invoicing&tab=invoice_edit&client_id=' .
                $item['id'] . '" >' . __( 'Add Invoice', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
            return $actions;
        }

    //end class
    }
}
