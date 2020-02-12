<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( "WPC_INV_Install" ) ) {

    class WPC_INV_Install extends WPC_INV_Admin_Common {

        private static $instance = NULL;

        static public function getInstance() {
            if ( self::$instance === NULL )
                self::$instance = new WPC_INV_Install();
            return self::$instance;
        }

        /**
         * PHP 5 constructor
         **/
        function __construct() {
            $this->inv_common_construct();
            $this->inv_admin_common_construct();

        }

        function install() {

            //run CRON reminder
            $inv_crons = $this->add_inv_crons();
            WPC()->cron()->add_crons( $inv_crons );

            $this->creating_db();
            $this->default_settings();
            $this->default_templates();

            //first install
            if ( false === get_option( 'wpc_inv_ver', false ) ) {

                //create default pages
                WPC()->install()->create_pages( $this->pre_set_pages( array() ) );

                //update rewrite rules
                flush_rewrite_rules( false );

            }

            WPC()->update()->check_updates( 'inv' );
        }

        /*
        * Create DB tables
        */
        function creating_db() {
            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $charset_collate = '';

            if ( ! empty($wpdb->charset) ) {
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty($wpdb->collate) ) {
                $charset_collate .= " COLLATE $wpdb->collate";
            }

            // specific tables.
            $tables = "CREATE TABLE {$wpdb->prefix}wpc_client_invoicing_items (
 id int(11) NOT NULL AUTO_INCREMENT,
 name varchar(255) NULL,
 description text NULL,
 rate varchar(20) NULL,
 use_r_est int(1) NULL,
 data text NULL,
 PRIMARY KEY  (id)
) $charset_collate;";

            dbDelta( $tables );

        }


        /**
         * Set Default Settings
         **/
        function default_settings() {

            $wpc_default_settings['invoicing'] = array(
                'send_for_review'           => 'no',
                'send_for_paid'             => 'no',
                'prefix'                    => '',
                'next_number'               => '',
                'rate_capacity'             => 2,
                'thousands_separator'       => '',
                'reminder_days_enabled'     => 'no',
                'reminder_days'             => 1,
                'display_zeros'             => 'yes',
                'digits_count'              => 8,
                'notify_payment_made'       => 'no',
                'currency_symbol'           => '$',
                'ter_con'                   => __( 'Thank you, we really appreciate your business. Please send payment within 21 days of receiving this invoice.', WPC_CLIENT_TEXT_DOMAIN ),
                'not_cus'                   => __( 'Thanks for your business!', WPC_CLIENT_TEXT_DOMAIN ),

            );

            //Set settings
            foreach( $wpc_default_settings as $key => $values ) {
                add_option( 'wpc_' . $key, $values );

                if ( is_array( $values ) && count( $values ) ) {
                    $current_setting = get_option( 'wpc_' . $key );
                    $new_setting = array_merge( $values, $current_setting );
                    update_option( 'wpc_' . $key, $new_setting );
                }
            }

        }


        /**
         * Set Default Templates
         **/
        function default_templates() {
            $wpc_default_templates = array();

            //email when
            $wpc_default_templates['templates_emails']['inv_not'] = array(
                'subject'               => 'Invoice for {client_name} - {business_name}',
                'body'                  => '<p>Hi,</p>
<p>Thanks for your business.</p>
<p>Your invoice is available in your Client Portal, and is attached with this email.</p>
<p>Looking forward to working with you for many years.</p>
<p>Thanks,</p>
<p>{business_name}</p>
<p>----</p>
<p>You can login here: {login_url}</p>',
            );

            //email when
            $wpc_default_templates['templates_emails']['est_not'] = array(
                'subject'               => 'Estimate for {client_name} - {business_name}',
                'body'                  => '<p>Hi,</p>
<p>Thanks for your business.</p>
<p>Your estimate is available in your Client Portal, and is attached with this email.</p>
<p>Looking forward to working with you for many years.</p>
<p>Thanks,</p>
<p>{business_name}</p>
<p>----</p>
<p>You can login here: {login_url}</p>',
            );

            //email when
            $wpc_default_templates['templates_emails']['pay_tha'] = array(
                'subject'               => 'Thanks for you payment - {business_name}',
                'body'                  => "Hi,

We have received your payment.

Thanks for the payment and your business.

Please don't hesitate to call or email at anytime with questions,

{business_name}",
            );

            //email when
            $wpc_default_templates['templates_emails']['admin_notify'] = array(
                'subject'               => 'Payment made by {client_name}',
                'body'                  => '<p>Payment Notification:</p>
<p>{client_name} has paid an invoice online.</p>',
            );

            //email reminder after due date
            $wpc_default_templates['templates_emails']['pay_rem'] = array(
                'subject'               => 'Payment reminder for {client_name} - {business_name}',
                'body'                  => '<p>Hi,</p>
<p>May we kindly remind you that your invoice with us is overdue. If you have already paid for this invoice, accept our apologies and ignore this reminder.</p>
<p><a href="{invoice_cancel_reminder_url}">Cancel reminder for this invoice</a></p>
<p>Thanks in advance for the payment,</p>
<p>{business_name}</p>',
            );
            //email reminder before due date
            $wpc_default_templates['templates_emails']['pay_rem_before'] = array(
                'subject'               => 'Payment reminder for {client_name} - {business_name}',
                'body'                  => '<p>Hi,</p>
<p>May we kindly remind you that your invoice with us will overdue {due_date}. If you have already paid for this invoice, accept our apologies and ignore this reminder.</p>
<p>Thanks in advance for the payment,</p>
<p>{business_name}</p>',
            );
            //email when client declined estimate
            $wpc_default_templates['templates_emails']['est_declined'] = array(
                'subject'               => 'Estimate #{invoice_number} was declined by {client_name}',
                'body'                  => '<p>Payment Notification:</p>
<p>{client_name} was declined #{invoice_number} estimate by reason of: {decline_note}.</p>
<p>To view the entire thread of messages and send a reply, click <a href="{admin_url}">HERE</a></p>',
            );
            //email when admin convert estimate to invoice
            $wpc_default_templates['templates_emails']['convert_est_to_inv'] = array(
                'subject'               => 'Your Estimate was converted to an Invoice',
                'body'                  => '<p>Hi {client_name},</p>
<p>Your estimate has been converted to an invoice (ID#{invoice_number}), and is available for viewing in your portal. You can login to your portal to view and pay the invoice <a href="{login_url}">HERE</a></p>
<p>Looking forward to working with you for many years.</p>
<p>Thanks,</p>
<p>{business_name}</p>',
            );
            //email when client accept estimate
            $wpc_default_templates['templates_emails']['accept_est'] = array(
                'subject'               => '{client_name} has accepted Estimate #{invoice_number}',
                'body'                  => '<p>Admin Notification:</p>
<p>{client_name} has accepted their assigned estimate (ID#{invoice_number}), and it has been converted into an invoice.</p>
<p>You can view the new invoice by clicking <a href="{admin_url}">HERE</a></p>',
            );
            //email when client create Request estimate
            $wpc_default_templates['templates_emails']['create_r_est'] = array(
                'subject'               => '{client_name} has created Estimate Request',
                'body'                  => '<p>Admin Notification:</p>
<p>{client_name} has created request estimate.</p>
<p>You can view the new request estimate by clicking <a href="{admin_url}">HERE</a></p>',
            );
            //email when admin converted Request estimate to estimate or invoice
            $wpc_default_templates['templates_emails']['convert_r_est_to'] = array(
                'subject'               => 'Your  Estimate was converted to an {to_object}',
                'body'                  => '<p>Hi {client_name},</p>
<p>Your request estimate has been converted to an {to_object} (ID#{invoice_number}), and is available for viewing in your portal. You can login to your portal to view it <a href="{login_url}">HERE</a></p>
<p>Looking forward to working with you for many years.</p>
<p>Thanks,</p>
<p>{business_name}</p>',
            );
            //email when client accept Request estimate to estimate or invoice
            $wpc_default_templates['templates_emails']['accept_r_est'] = array(
                'subject'               => '{client_name} has accepted Estimate Request #{invoice_number}',
                'body'                  => '<p>Admin Notification:</p>
<p>{client_name} has accepted their assigned request estimate, and it has been converted into an {to_object} (ID#{invoice_number}).</p>
<p>You can view the new invoice or estimate by clicking <a href="{admin_url}">HERE</a></p>',
            );




            /*
             * SMS templates
             * */
            //SMS when
            $wpc_default_templates['templates_sms']['inv_not'] = array(
                'enable'               => 0,
                'subject'               => 'Invoice for {client_name} - {business_name}',
                'body'                  => 'Hi, your invoice is available in your Client Portal. Thanks,{business_name}',
            );

            //SMS when
            $wpc_default_templates['templates_sms']['est_not'] = array(
                'enable'               => 0,
                'subject'               => 'Estimate for {client_name} - {business_name}',
                'body'                  => 'Hi, your estimate is available in your Client Portal,Thanks,{business_name}'
            );

            //SMS when
            $wpc_default_templates['templates_sms']['pay_tha'] = array(
                'enable'               => 0,
                'subject'               => 'Thanks for you payment - {business_name}',
                'body'                  => 'Hi, we have received your payment. Thanks for the payment and your business. {business_name}',
            );

            //SMS when
            $wpc_default_templates['templates_sms']['admin_notify'] = array(
                'enable'               => 0,
                'subject'               => 'Payment made by {client_name}',
                'body'                  => 'Payment Notification: {client_name} has paid an invoice online.',
            );

            //SMS reminder after due date
            $wpc_default_templates['templates_sms']['pay_rem'] = array(
                'enable'               => 0,
                'subject'               => 'Payment reminder for {client_name} - {business_name}',
                'body'                  => 'Hi, may we kindly remind you that your invoice with us is overdue. If you have already paid for this invoice, accept our apologies and ignore this reminder. {business_name}',
            );

            //SMS reminder before due date
            $wpc_default_templates['templates_sms']['pay_rem_before'] = array(
                'enable'               => 0,
                'subject'               => 'Payment reminder for {client_name} - {business_name}',
                'body'                  => 'Hi, may we kindly remind you that your invoice with us will overdue {due_date}. If you have already paid for this invoice, accept our apologies and ignore this reminder. {business_name}',
            );

            //SMS when client declined estimate
            $wpc_default_templates['templates_sms']['est_declined'] = array(
                'enable'               => 0,
                'subject'               => 'Estimate #{invoice_number} was declined by {client_name}',
                'body'                  => 'Payment Notification: {client_name} was declined #{invoice_number} estimate by reason of: {decline_note}. {business_name}',
            );

            //SMS when admin convert estimate to invoice
            $wpc_default_templates['templates_sms']['convert_est_to_inv'] = array(
                'enable'               => 0,
                'subject'               => 'Your Estimate was converted to an Invoice',
                'body'                  => 'Hi {client_name}, your estimate has been converted to an invoice (ID#{invoice_number}), and is available for viewing in your portal. {business_name}',
            );

            //SMS when client accept estimate
            $wpc_default_templates['templates_sms']['accept_est'] = array(
                'enable'               => 0,
                'subject'               => '{client_name} has accepted Estimate #{invoice_number}',
                'body'                  => 'Admin Notification: {client_name} has accepted their assigned estimate (ID#{invoice_number}), and it has been converted into an invoice.',
            );

            //SMS when client create Request estimate
            $wpc_default_templates['templates_sms']['create_r_est'] = array(
                'enable'               => 0,
                'subject'               => '{client_name} has created Estimate Request',
                'body'                  => 'Admin Notification: {client_name} has created request estimate.',
            );

            //SMS when admin converted Request estimate to estimate or invoice
            $wpc_default_templates['templates_sms']['convert_r_est_to'] = array(
                'enable'               => 0,
                'subject'               => 'Your  Estimate was converted to an {to_object}',
                'body'                  => 'Hi {client_name}, your request estimate has been converted to an {to_object} (ID#{invoice_number}), and is available for viewing in your portal. {business_name}',
            );

            //SMS when client accept Request estimate to estimate or invoice
            $wpc_default_templates['templates_sms']['accept_r_est'] = array(
                'enable'               => 0,
                'subject'               => '{client_name} has accepted Estimate Request #{invoice_number}',
                'body'                  => 'Admin Notification: {client_name} has accepted their assigned request estimate, and it has been converted into an {to_object} (ID#{invoice_number}).',
            );





            //Set templates
            foreach ( $wpc_default_templates as $key => $values ) {
                add_option( 'wpc_' . $key, $values );

                if ( is_array( $values ) && count( $values ) ) {
                    $current_setting = get_option( 'wpc_' . $key );
                    $new_setting = array_merge( $values, $current_setting );
                    update_option( 'wpc_' . $key, $new_setting );
                }
            }

        }
        //end class
    }

}

return WPC_INV_Install::getInstance();