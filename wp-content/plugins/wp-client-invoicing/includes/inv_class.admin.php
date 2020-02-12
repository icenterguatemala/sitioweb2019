<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'WPC_INV_Admin' ) ) {

    class WPC_INV_Admin extends WPC_INV_Admin_Meta_Boxes {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->inv_common_construct();
            $this->inv_admin_common_construct();
            $this->meta_construct();

            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'add_admin_submenu' ) );

            add_action( 'admin_enqueue_scripts', array( &$this, 'load_css_js' ), 100 );


            add_action( 'wpc_client_dashboard_tables', array( &$this, 'show_dashboard_tables' ) );

            //add templates emails
            add_filter( 'wpc_client_templates_emails_array', array( &$this, 'add_templates_emails' ) );
            add_filter( 'wpc_client_templates_emails_tags_array', array( &$this, 'add_template_tags' ) );

            //add templates sms
            add_filter( 'wpc_client_templates_sms_array', array( &$this, 'add_templates_sms' ) );

            //add templates shortcodes
            add_filter( 'wpc_client_templates_shortcodes_array', array( &$this, 'add_templates_shortcodes' ) );

            //add subsubmenu
            add_filter( 'wpc_client_add_subsubmenu', array( &$this, 'add_subsubmenu' ) );

            //uninstall
            add_action( 'wp_client_uninstall', array( &$this, 'uninstall_extension' ) );

            //delete client
            add_action( 'wpc_client_delete_client', array( &$this, 'delete_client' ) );



            //add_filter( 'cron_schedules', array( &$this, 'cron_add_five_min' ) );

            //add array help
            add_filter( 'wpc_set_array_help', array( &$this, 'wpc_set_array_help' ), 10, 2 );

            add_filter( 'wpc_client_dashboard_widgets_list', array( &$this, 'dashboard_widgets_list_extension' ), 10, 1 );

            add_filter( 'wpc_screen_options_pagination', array( &$this, 'screen_options_pagination' ), 10 );

            //add screen options for client Page
            add_action( 'admin_head', array( &$this, 'add_screen_options' ), 5 );

            // Add Settings link when activate plugin
            add_filter( 'plugin_action_links_wp-client-invoicing/wp-client-invoicing.php', array( &$this, 'filter_action_links' ), 99 );

            register_deactivation_hook( 'wp-client-invoicing/wp-client-invoicing.php', array( &$this, 'deactivation' ) );
        }


        //deactivation extension
        function deactivation() {

            //delete crons
            wp_clear_scheduled_hook( 'wpc_invoice_cron' );
            wp_clear_scheduled_hook( 'wpc_client_inv_send_reminder' );

        }


        function get_setting_selectbox( $data ) {
            $default_data = array (
                'title' => '',
                'key' => '',
                'value' => '',
                'options' => array(
                    'yes'   => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'    => __( 'No', WPC_CLIENT_TEXT_DOMAIN ),
                ),
            );
            $data = array_merge( $default_data, $data );

            //hide this field
            $hide = !empty( $data['hide'] ) ? ' style="display: none;"' : '';
            //add class
            $class = !empty( $data['class'] ) ? ' class="' . $data['class'] . '"' : '';

            $html = '<tr valign="top"' . $class . $hide . '>
                <th scope="row">
                    <label for="' . $data['key'] . '">' . $data['title'] . '</label>
                </th><td>
                    <select name="settings[' . $data['key'] . ']" id="' . $data['key'] . '" style="width: 100px;">';
                    //add options
                    foreach ( $data['options'] as $k => $v ) {
                        if( is_int($v) )
                            ++$k;

                        $html .= '<option value="' . $k . '" ' . selected( $k, $data['value'], false ) . '>' . $v . '</option>';
                    }
            $html .= '</select>';
            //add description
            $html .= !empty( $data['description'] )
                    ? '<span class="description">' . $data['description'] . '</span>' : '';

            $html .= '</td></tr>';

            return $html;
        }


        function prepare_invoicing_settings( $data ) {
            //validation
            $data['prefix']        = ( isset( $data['prefix'] ) && '' != $data['prefix'] ) ? $data['prefix'] : '';
            $data['next_number']   = ( isset( $data['next_number'] ) && '' != $data['next_number'] ) ? $data['next_number'] : $this->get_next_number( false );
            $data['digits_count']  = ( isset( $data['digits_count'] ) && is_numeric( $data['digits_count'] ) && 2 < $data['digits_count'] ) ? $data['digits_count'] : 8;
            $data['display_zeros'] = ( isset( $data['display_zeros'] ) && 'yes' == $data['display_zeros'] ) ? 'yes' : 'no';

            $data['prefix_est']        = ( isset( $data['prefix_est'] ) && '' != $data['prefix_est'] ) ? $data['prefix_est'] : '';
            $data['next_number_est']   = ( isset( $data['next_number_est'] ) && '' != $data['next_number_est'] ) ? $data['next_number_est'] : $this->get_next_number( false, 'est' );
            $data['digits_count_est']  = ( isset( $data['digits_count_est'] ) && is_numeric( $data['digits_count_est'] ) && 2 < $data['digits_count_est'] ) ? $data['digits_count_est'] : 8;
            $data['display_zeros_est'] = ( isset( $data['display_zeros_est'] ) && 'yes' == $data['display_zeros_est'] ) ? 'yes' : 'no';

            $data['rate_capacity']         = ( isset( $data['rate_capacity'] ) && '2' < $data['rate_capacity'] && '6' > $data['rate_capacity'] ) ? $data['rate_capacity'] : 2;
            $data['thousands_separator']   = ( isset( $data['thousands_separator'] ) && '' != $data['thousands_separator'] ) ? $data['thousands_separator'] : '';
            $data['send_for_review']       = ( isset( $data['send_for_review'] ) && 'yes' == $data['send_for_review'] ) ? 'yes' : 'no';
            $data['send_for_paid']         = ( isset( $data['send_for_paid'] ) && 'yes' == $data['send_for_paid'] ) ? 'yes' : 'no';
            $data['send_pdf_for_paid']     = ( isset( $data['send_pdf_for_paid'] ) && 'yes' == $data['send_pdf_for_paid'] ) ? 'yes' : 'no';
            $data['notify_payment_made']   = ( isset( $data['notify_payment_made'] ) && 'yes' == $data['notify_payment_made'] ) ? 'yes' : 'no';
            $data['reminder_days_enabled'] = ( isset( $data['reminder_days_enabled'] ) && 'yes' == $data['reminder_days_enabled'] ) ? $data['reminder_days_enabled'] : 'no';
            $data['reminder_days']         = ( isset( $data['reminder_days'] ) && 0 < $data['reminder_days'] && 32 > $data['reminder_days'] ) ? $data['reminder_days'] : 1;
            $data['reminder_one_day']      = ( isset( $data['reminder_one_day'] ) && 'yes' == $data['reminder_one_day'] && 32 > $data['reminder_one_day'] ) ? 'yes' : 'no';
            $data['reminder_after']        = ( isset( $data['reminder_after'] ) && 0 <= $data['reminder_after'] && 32 > $data['reminder_after'] ) ? $data['reminder_after'] : 0;
            $data['currency_symbol']       = ( isset( $data['currency_symbol'] ) ) ? $data['currency_symbol'] : '';
            $data['currency_symbol_align'] = ( isset( $data['currency_symbol_align'] ) ) ? $data['currency_symbol_align'] : 'left';
            $data['gateways']              = ( isset( $data['gateways'] ) ) ? $data['gateways'] : array();
            $data['description']           = ( isset( $data['description'] ) ) ? $data['description'] : '';
            $data['ter_con']               = ( isset( $data['ter_con'] ) ) ? $data['ter_con'] : '';
            $data['not_cus']               = ( isset( $data['not_cus'] ) ) ? $data['not_cus'] : '';
            $data['est_auto_convert']      = ( isset( $data['est_auto_convert'] ) ) ? $data['est_auto_convert'] : '';
            $data['rest_convert_to']       = ( isset( $data['rest_convert_to'] ) ) ? $data['rest_convert_to'] : '';
            $data['inv_filename']          = ( isset( $data['inv_filename'] ) ) ? $data['inv_filename'] : 'inv_{number_inv}';
            $data['est_filename']          = ( isset( $data['est_filename'] ) ) ? $data['est_filename'] : 'est_{number_est}';
            $data['vat']                   = ( isset( $data['vat'] ) && 0 < (float) $data['vat'] && 100 > (float) $data['vat'] ) ? (float) $data['vat'] : '';
            $data['vat_set']               = ( isset( $data['vat_set'] ) && 'yes' === $data['vat_set'] ) ? 'yes' : 'no';
            $data['attach_pdf']            = ( isset( $data['attach_pdf'] ) && 'no' === $data['attach_pdf'] ) ? 'no' : 'yes';
            $data['attach_pdf_reminder']   = ( isset( $data['attach_pdf_reminder'] ) && 'yes' === $data['attach_pdf_reminder'] ) ? 'yes' : 'no';
            $data['items_required']        = (!isset( $data['items_required'] ) || 'no' !== $data['items_required'] ) ? 'yes' : 'no';

            $data['lock_invoice'] = ( isset( $data['lock_invoice'] ) && 'yes' === $data['lock_invoice'] ) ? 'yes' : 'no';
            $data['time_lock']    = ( isset( $data['time_lock'] ) && 0 < (int) $data['time_lock'] ) ? $data['time_lock'] : '';

            return $data;
        }


        function add_screen_options() {

            if ( isset( $_GET['page'] ) && 'wpclients_invoicing' == $_GET['page'] ) {
                if ( !isset( $_GET['tab'] ) ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Invoices', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_invoices_per_page'
                        )
                    );
                } elseif ( 'repeat_invoices' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Recurring Profiles', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_repeat_invoices_per_page'
                        )
                    );
                } elseif ( 'accum_invoices' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Accumulating Profiles', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_accum_invoices_per_page'
                        )
                    );
                } elseif ( 'estimates' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Estimates', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_estimates_per_page'
                        )
                    );
                } elseif ( 'request_estimates' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_request_estimates_per_page'
                        )
                    );
                } elseif ( 'invoicing_items' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_invoicing_items_per_page'
                        )
                    );
                } elseif ( 'invoicing_taxes' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_inv_invoicing_taxes_per_page'
                        )
                    );
                }
            }
        }


        function screen_options_pagination( $wpc_screen_options ) {

            $wpc_screen_options = array_merge( $wpc_screen_options, array(
                'wpc_inv_invoices_per_page',
                'wpc_inv_repeat_invoices_per_page',
                'wpc_inv_accum_invoices_per_page',
                'wpc_inv_estimates_per_page',
                'wpc_inv_request_estimates_per_page',
                'wpc_inv_invoicing_items_per_page',
                'wpc_inv_invoicing_taxes_per_page'
            ) );

            return $wpc_screen_options;
        }


        function dashboard_widgets_list_extension( $list ) {

            if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) ) {
                $list['wpc_inv_statistic_dashboard_widget'] = array(
                    'collapsed'     => false,
                    'color'         => 'white',
                );
            }

            return $list;
        }


        function wpc_set_array_help( $array_help, $method ) {
            switch( $method ) {
                case '_add_wpclients_invoicing_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'You will see a list of all existing invoices in this tab. You can view/edit each invoice, download as a PDF, mark them as "Void", and delete them permanently. You can also filter the list by invoice status such as "Open" or "Pending", and by assigned %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002674-" target="_blank">' . __( 'Estimates/Invoices Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002673-quick-start" target="_blank">' . __( 'Estimates/Invoices Quick Start Guide', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_invoicinginvoice_edit_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'When you create a new Invoice, you are given multiple options. You can assign to specific %s or %s. Additionally, you can choose to drag-and-drop your previously created Items into the Invoice, or add new Items. You can also set a due date, and type a unique message for the %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002674-" target="_blank">' . __( 'Estimates/Invoices Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002673-quick-start" target="_blank">' . __( 'Estimates/Invoices Quick Start Guide', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_invoicingestimates_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'You will see a list of all existing estimates in this tab. You can view/edit each estimate, download as a PDF, and delete them permanently. Additionally, choosing the "Convert to Invoice" option will automatically switch the estimate over to a invoice, keeping it\'s %1$s assignment. You can also filter the list by assigned %1$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002674-" target="_blank">' . __( 'Estimates/Invoices Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002673-quick-start" target="_blank">' . __( 'Estimates/Invoices Quick Start Guide', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_invoicingestimate_edit_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'Estimates can be thought of in a very similar manner as Invoices, and almost as "pre-Invoices". An estimate consists of one or more items with their associated title, description and price point. You can set a date for the estimate is good until, add tax to the estimate, add a discount to the estimate, set your terms and conditions and add a special note to the customer as needed.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002674-" target="_blank">' . __( 'Estimates/Invoices Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002673-quick-start" target="_blank">' . __( 'Estimates/Invoices Quick Start Guide', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_invoicinginvoicing_items_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'The building blocks for Estimates and Invoices are what is known as Items. Items can be thought of as a line item title and description that quantifies a billable service or particular product or sku. The items created on this page are reusable, so for example you can create an item for "One Billable Hour of Design Work", and add it to as many Estimates/Invoices as you like, including adding multiples of each item to an Estimate or Invoice.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002674-" target="_blank">' . __( 'Estimates/Invoices Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002673-quick-start" target="_blank">' . __( 'Estimates/Invoices Quick Start Guide', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_invoicinginvoicing_taxes_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'You can create and adjust your desired taxes from this page. You can setup multiple tax levels if desired, and then choose the appropriate tax on an individual Estimate/Invoice basis. The default tax rate is calculated in percent.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002674-" target="_blank">' . __( 'Estimates/Invoices Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002673-quick-start" target="_blank">' . __( 'Estimates/Invoices Quick Start Guide', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_settingsinvoicing_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Adjust various Estimates/Invoices settings from this tab, including how the Estimate/Invoice numbers are formatted, what payment gateways your %1$s can use, what currency symbol to use, and whether or not you and your %1$s will receive email notifications related to Estimates and Invoices.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p>' .
                                    '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;
            }
            return $array_help ;
        }


        function add_subsubmenu( $subsubmenu ) {
            $add_items = array(
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Add Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_invoices' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=invoice_edit',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Recurring Profiles', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_repeat_invoices' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=repeat_invoices',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Add Recurring Profile', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_repeat_invoices' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=repeat_invoice_edit',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Accumulating Profiles', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_accum_invoices' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=accum_invoices',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Add Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_accum_invoices' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=accum_invoice_edit',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Estimates', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_estimates' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=estimates',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Add Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_estimates' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=estimate_edit',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_estimate_requests' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=request_estimates',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_inv_custom_fields' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=custom_fields',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_modify_items' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=invoicing_items',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Item Custom Fields', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_create_inv_custom_fields' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=item_custom_fields',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_modify_taxes' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=invoicing_taxes',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_invoicing',
                    'menu_title'        => __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_invoicing&tab=settings',
                    ),
            );

            return array_merge( $subsubmenu, $add_items );
        }


        function cron_add_five_min( $schedules ) {
            $schedules['five_min'] = array(
                'interval' => 30,
                'display' => __( 'Once Five Min', WPC_CLIENT_TEXT_DOMAIN )
            );
            return $schedules;
        }


        /*
        * Function unisntall
        */
        function uninstall_extension() {

            global $wpdb;

            /*
            * Delete all tables
            */
            //tables name
            $tables = array(
                'wpc_client_invoicing',
                'wpc_client_invoicing_items',

            );

            //remove all tables
            foreach( $tables as $key ) {
                if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}" ) {
                    $wpdb->query( "DROP TABLE {$wpdb->prefix}{$key}" );
                }
            }


            WPC()->delete_settings( 'invoice_settings' );
            WPC()->delete_settings( 'invoicing' );



            //deactivate the extension
            $plugins = get_option( 'active_plugins' );
            if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                $new_plugins = array();
                foreach( $plugins as $plugin ) {
                    if ( 'wp-client-invoicing/wp-client-invoicing.php' != $plugin ) {
                        $new_plugins[] = $plugin;
                    }
                }
            }

            update_option( 'active_plugins', $new_plugins );
        }


        /*
        * Add templates emails
        */
        function add_templates_emails( $wpc_emails_array ) {

            $wpc_emails_array['inv_not'] = array(
                'tab_label'             => __( 'INV: Invoice Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Invoice Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s that they have a new Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing client_recipient invoice',
            );

            $wpc_emails_array['est_not'] = array(
                'tab_label'             => __( 'INV: Estimate Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s that they have a new Estimate', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{estimate_number}, {invoicing_title}, {total_amount}, {due_date}, {minimum_payment}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing client_recipient estimate',
            );

            $wpc_emails_array['pay_tha'] = array(
                'tab_label'             => __( 'INV: Payment Thank-You', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Payment Thank-You', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           =>  sprintf( __( '  >> This template for sending the %s a thank you for payment', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {invoice_amount}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoice_date}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing client_recipient other new_payment',
            );

            $wpc_emails_array['admin_notify'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Payment', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Payment', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of successful online payment', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoicing_title}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing admin_recipient other new_payment',
            );

            $wpc_emails_array['pay_rem'] = array(
                'tab_label'             => __( 'INV: Payment Reminders After Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Payment Reminders After Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s of overdue invoices', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoicing_title}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}, {invoice_cancel_reminder_url}.',
                'tags'                  => 'invoicing payment_reminder client_recipient'
            );

            $wpc_emails_array['pay_rem_before'] = array(
                'tab_label'             => __( 'INV: Payment Reminders Before Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Payment Reminders Before Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s about that invoices will be overdue', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoicing_title}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}, {invoice_cancel_reminder_url}.',
                'tags'                  => 'invoicing payment_reminder client_recipient'
            );

            $wpc_emails_array['est_declined'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Estimate Declined', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Estimate Declined', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of declined estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {invoicing_title}, {site_title}, {client_name}, {admin_url}, {decline_note} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            $wpc_emails_array['convert_est_to_inv'] = array(
                'tab_label'             => __( 'INV: Estimate Converted to Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Converted to Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s of converted estimate to Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {invoicing_title}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing client_recipient estimate invoice'
            );

            $wpc_emails_array['accept_est'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Accepted Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Accepted Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of accepted estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {accept_note}, {total_amount}, {due_date}, {minimum_payment}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            $wpc_emails_array['create_r_est'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Created Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Created Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of created request estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            $wpc_emails_array['convert_r_est_to'] = array(
                'tab_label'             => __( 'INV: Estimate Request Converted to Estimate or Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Request Converted to Estimate or Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s of converted request estimate to Estimate or Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {to_object}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing client_recipient estimate invoice'
            );

            $wpc_emails_array['accept_r_est'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Accepted Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Accepted Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of accepted request estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {to_object}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            return $wpc_emails_array;
        }


        /*
        * Add templates SMS
        */
        function add_templates_sms( $wpc_sms_array ) {

            $wpc_sms_array['inv_not'] = array(
                'tab_label'             => __( 'INV: Invoice Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Invoice Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s that they have a new Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing client_recipient invoice',
            );

            $wpc_sms_array['est_not'] = array(
                'tab_label'             => __( 'INV: Estimate Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s that they have a new Estimate', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{estimate_number}, {invoicing_title}, {total_amount}, {due_date}, {minimum_payment}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing client_recipient estimate',
            );

            $wpc_sms_array['pay_tha'] = array(
                'tab_label'             => __( 'INV: Payment Thank-You', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Payment Thank-You', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           =>  sprintf( __( '  >> This template for sending the %s a thank you for payment', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {invoice_amount}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoice_date}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing client_recipient other new_payment',
            );

            $wpc_sms_array['admin_notify'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Payment', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Payment', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of successful online payment', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoicing_title}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}.',
                'tags'                  => 'invoicing admin_recipient other new_payment',
            );

            $wpc_sms_array['pay_rem'] = array(
                'tab_label'             => __( 'INV: Payment Reminders After Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Payment Reminders After Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s of overdue invoices', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoicing_title}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}, {invoice_cancel_reminder_url}.',
                'tags'                  => 'invoicing payment_reminder client_recipient'
            );

            $wpc_sms_array['pay_rem_before'] = array(
                'tab_label'             => __( 'INV: Payment Reminders Before Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Payment Reminders Before Due Date', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s about that invoices will be overdue', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {payment_method}, {invoicing_title}, {site_title}, {client_name}, {login_url}, {business_logo_url}, {business_name}, {business_address},{business_mailing_address}, {business_website}, {business_email}, {business_phone}, {business_fax}, {invoice_cancel_reminder_url}.',
                'tags'                  => 'invoicing payment_reminder client_recipient'
            );

            $wpc_sms_array['est_declined'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Estimate Declined', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Estimate Declined', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of declined estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {invoicing_title}, {site_title}, {client_name}, {admin_url}, {decline_note} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            $wpc_sms_array['convert_est_to_inv'] = array(
                'tab_label'             => __( 'INV: Estimate Converted to Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Converted to Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s of converted estimate to Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {total_amount}, {due_date}, {minimum_payment}, {invoicing_title}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing client_recipient estimate invoice'
            );

            $wpc_sms_array['accept_est'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Accepted Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Accepted Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of accepted estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {accept_note}, {total_amount}, {due_date}, {minimum_payment}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            $wpc_sms_array['create_r_est'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Created Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Created Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of created request estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            $wpc_sms_array['convert_r_est_to'] = array(
                'tab_label'             => __( 'INV: Estimate Request Converted to Estimate or Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Request Converted to Estimate or Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( '  >> This template for notifying the %s of converted request estimate to Estimate or Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {to_object}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing client_recipient estimate invoice'
            );

            $wpc_sms_array['accept_r_est'] = array(
                'tab_label'             => __( 'INV: Notify Admin of Accepted Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Notify Admin of Accepted Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for notifying the admin of accepted request estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'subject_description'   => '',
                'body_description'      => '{invoice_number}, {invoicing_title}, {total_amount}, {to_object}, {site_title}, {client_name}, {admin_url} will not be change as these placeholders will be used in the email.',
                'tags'                  => 'invoicing admin_recipient estimate'
            );

            return $wpc_sms_array;
        }


        /**
         * Add template tags
         *
         * @param array $wpc_email_tags_array notifications tags
         * @return array
         */
        function add_template_tags( $wpc_email_tags_array ) {
            $wpc_email_tags_array = array_merge( $wpc_email_tags_array, array(
                'estimate'   => __( 'Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'invoice'   => __( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'new_payment'   => __( 'New Payment', WPC_CLIENT_TEXT_DOMAIN ),
                'invoicing'   => __( 'Estimates/Invoices', WPC_CLIENT_TEXT_DOMAIN ),
            ) );

            return $wpc_email_tags_array;
        }


        /*
        * Add template tab
        */
        function add_templates_shortcodes( $wpc_shortcodes_array ) {

            //shortcode Invoice
            $wpc_shortcodes_array['wpc_client_inv_inv'] = array(
                'tab_label'             => __( 'INV: Invoice', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Invoice Template', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for use in generating an Invoice - will be created in this format', WPC_CLIENT_TEXT_DOMAIN ),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            //shortcode Invoice Page
            $wpc_shortcodes_array['wpc_client_inv_inv_page'] = array(
                'tab_label'             => __( 'INV: Invoice Page', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Invoice Page Template', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for use on Invoice page', WPC_CLIENT_TEXT_DOMAIN ),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            //shortcode Estimate
            $wpc_shortcodes_array['wpc_client_inv_est'] = array(
                'tab_label'             => __( 'INV: Estimate', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Template', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for use in generating an Estimate - will be created in this format', WPC_CLIENT_TEXT_DOMAIN ),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            //shortcode Estimate Page
            $wpc_shortcodes_array['wpc_client_inv_est_page'] = array(
                'tab_label'             => __( 'INV: Estimate Page', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Estimate Page Template', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for use on Estimate page', WPC_CLIENT_TEXT_DOMAIN ),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            //shortcode Invoice
            $wpc_shortcodes_array['wpc_client_invoicing_list'] = array(
                'tab_label'             => __( 'INV: Invoicing List', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Invoicing List Template', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for use in displaying Invoicing List on pages', WPC_CLIENT_TEXT_DOMAIN ),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            //shortcode Invoice Account Summary
            $wpc_shortcodes_array['wpc_client_inv_invoicing_account_summary'] = array(
                'tab_label'             => __( 'INV: Invoicing Account Summary', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Invoicing: Invoicing Account Summary Template', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => __( '  >> This template for [wpc_client_inv_invoicing_account_summary] shortcode', WPC_CLIENT_TEXT_DOMAIN ),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            return $wpc_shortcodes_array;
        }


        /*
        * Function for adding admin submenu
        */
        function add_admin_submenu( $plugin_submenus ) {
            //add separater before addons submenu block
            if ( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
                $cap = "wpc_admin";
            } else if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) &&
                ( current_user_can( 'wpc_create_invoices' ) ||
                    current_user_can( 'wpc_create_repeat_invoices' ) ||
                    current_user_can( 'wpc_create_accum_invoices' ) ||
                    current_user_can( 'wpc_create_estimates' ) ||
                    current_user_can( 'wpc_estimate_requests' ) ||
                    current_user_can( 'wpc_modify_items' ) ||
                    current_user_can( 'wpc_modify_taxes' ) ||
                    current_user_can( 'wpc_create_inv_custom_fields' ) ) ) {
                $cap = "wpc_manager";
            } else {
                $cap = "manage_options";
            }

            $plugin_submenus['separator_2'] = array(
                'page_title'        => '',
                'menu_title'        => '- - - - - - - - - -',
                'slug'              => '#',
                'capability'        => $cap,
                'function'          => '',
                'hidden'            => false,
                'real'              => false,
                'order'             => 100,
            );

            $plugin_submenus['wpclients_invoicing'] = array(
                'page_title'        => __( 'Estimates/Invoices', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Estimates/Invoices', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_invoicing',
                'capability'        => $cap,
                'function'          => array( &$this, 'wpc_invoicing_pages' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 120,
            );

            return $plugin_submenus;
        }


        /*
        * display Invoicing page
        */
        function wpc_invoicing_pages() {
            if ( !isset( $_GET['tab'] ) || 'invoices' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/invoices.php';
            } elseif ( isset( $_GET['tab'] ) && 'invoice_edit' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/invoice_edit.php';
            } elseif ( isset( $_GET['tab'] ) && 'repeat_invoices' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/repeat_invoices.php';
            } elseif ( isset( $_GET['tab'] ) && 'repeat_invoice_edit' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/repeat_invoice_edit.php';
            } elseif ( isset( $_GET['tab'] ) && 'accum_invoices' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/accumulating_invoices.php';
            } elseif ( isset( $_GET['tab'] ) && 'accum_invoice_edit' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/accumulating_invoice_edit.php';
            } elseif ( isset( $_GET['tab'] ) && 'estimates' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/estimates.php';
            } elseif ( isset( $_GET['tab'] ) && 'estimate_edit' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/estimate_edit.php';
            } elseif ( isset( $_GET['tab'] ) && 'request_estimates' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/request_estimates.php';
            } elseif ( isset( $_GET['tab'] ) && 'request_estimate_edit' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/request_estimate_edit.php';
            } elseif ( isset( $_GET['tab'] ) && 'invoicing_items' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/inv_items.php';
            } elseif ( isset( $_GET['tab'] ) && 'invoicing_taxes' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/inv_taxes.php';
            } elseif ( isset( $_GET['tab'] ) && 'item_custom_fields' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/item_custom_fields.php';
            } elseif ( isset( $_GET['tab'] ) && 'custom_fields' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/custom_fields.php';
            } elseif ( isset( $_GET['tab'] ) &&
                    ( 'item_custom_field_edit' == $_GET['tab'] || 'inv_custom_field_edit' == $_GET['tab']  ) ) {
                include $this->extension_dir . 'includes/admin/custom_field_edit.php';
            } elseif ( isset( $_GET['tab'] ) && 'settings' == $_GET['tab'] ) {
                include $this->extension_dir . 'includes/admin/settings_invoicing.php';
            }
        }


        /**
         * Gen tabs manu
         */
        function gen_tabs_menu() {

            $tabs = '<h2 class="nav-tab-wrapper wpc-nav-tab-wrapper">';

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_invoices' ) ) {
                $active = ( !isset( $_GET['tab'] ) || 'invoices' == $_GET['tab'] || 'invoice_edit' == $_GET['tab'] ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing" class="nav-tab ' . $active . '">' . __( 'Invoices', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_repeat_invoices' ) ) {
                $active = ( isset( $_GET['tab'] ) && ( 'repeat_invoices' == $_GET['tab'] || 'repeat_invoice_edit' == $_GET['tab'] ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=repeat_invoices" class="nav-tab ' . $active . '">' . __( 'Recurring Profiles', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_accum_invoices' ) ) {
                $active = ( isset( $_GET['tab'] ) && ( 'accum_invoices' == $_GET['tab'] || 'accum_invoice_edit' == $_GET['tab'] ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=accum_invoices" class="nav-tab ' . $active . '">' . __( 'Accumulating Profiles', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_estimates' ) ) {
                $active = ( isset( $_GET['tab'] ) && ( 'estimates' == $_GET['tab'] || 'estimate_edit' == $_GET['tab'] ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=estimates" class="nav-tab ' . $active . '">' . __( 'Estimates', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_estimate_requests' ) ) {
                $active = ( isset( $_GET['tab'] ) && ( 'request_estimates' == $_GET['tab'] || 'request_estimate_edit' == $_GET['tab'] ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=request_estimates" class="nav-tab ' . $active . '">' . __( 'Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_inv_custom_fields' ) ) {
                $active = ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'custom_fields', 'inv_custom_field_edit' ) ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=custom_fields" class="nav-tab ' . $active . '">' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_modify_items' ) ) {
                $active = ( isset( $_GET['tab'] ) && 'invoicing_items' == $_GET['tab'] ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=invoicing_items" class="nav-tab ' . $active . '">' . __( 'Items', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_create_inv_custom_fields' ) ) {
                $active = ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'item_custom_fields', 'item_custom_field_edit' ) ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=item_custom_fields" class="nav-tab ' . $active . '">' . __( 'Item Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_modify_taxes' ) ) {
                $active = ( isset( $_GET['tab'] ) && 'invoicing_taxes' == $_GET['tab'] ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=invoicing_taxes" class="nav-tab ' . $active . '">' . __( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( !current_user_can( 'wpc_manager' ) ) {
                $tabs .= '<a href="admin.php?page=wpclients_payments&filter_function=invoicing&change_filter=function" class="nav-tab">' . __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                $active = ( isset( $_GET['tab'] ) && 'settings' == $_GET['tab'] ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_invoicing&tab=settings" class="nav-tab ' . $active . '">' . __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            return $tabs . '</h2>';
        }


        /**
         * Redirect to available page after open disabled page
         */
        function redirect_available_page() {
            if ( current_user_can( 'wpc_create_invoices' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing';
            } else if ( current_user_can( 'wpc_create_estimates' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=estimates';
            } else if ( current_user_can( 'wpc_create_repeat_invoices' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=repeat_invoices';
            } else if ( current_user_can( 'wpc_create_accum_invoices' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=accum_invoices';
            } else if ( current_user_can( 'wpc_create_inv_custom_fields' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=custom_fields';
            } else if ( current_user_can( 'wpc_modify_items' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=invoicing_items';
            } else if ( current_user_can( 'wpc_create_inv_custom_fields' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=item_custom_fields';
            } else if ( current_user_can( 'wpc_modify_taxes' ) ) {
                $adress = 'admin.php?page=wpclients_invoicing&tab=invoicing_taxes';
            } else {
                $adress = 'admin.php?page=wpclient_clients';
            }

            WPC()->redirect( get_admin_url() . $adress );
        }


        /**
         * Load css and js
         */
        function load_css_js() {

            if ( isset( $_GET['page'] ) && 'wpclients_invoicing' == $_GET['page'] ) {

                wp_enqueue_script( 'jquery-ui-tooltip' );

                wp_enqueue_script( 'postbox' );

                //shutterbox init
                wp_register_script('wpc-shutter-box-script', WPC()->plugin_url . 'js/shutter-box/shutter_box_core.js');
                wp_enqueue_script('wpc-shutter-box-script');
                wp_register_style('wpc-shutter-box-style', WPC()->plugin_url . 'js/shutter-box/shutter_box.css');
                wp_enqueue_style('wpc-shutter-box-style');

                wp_enqueue_script('jquery-ui-datepicker');

                wp_register_style( 'wpc-ui-datepicker', WPC()->plugin_url . 'css/datapiker/ui_datapiker.min.css' );
                wp_enqueue_style( 'wpc-ui-datepicker' );

                wp_register_style( 'wpc-invoices-style', $this->extension_url . 'css/style.css' );
                wp_enqueue_style( 'wpc-invoices-style' );

                if ( isset( $_GET['tab'] ) ) {
                    switch( $_GET['tab'] ) {
                        case 'invoicing_settings':
                        case 'invoicing_templates':
                            wp_enqueue_script( 'jquery-ui-tabs' );
                            wp_enqueue_script( 'jquery-ui' );
                            wp_enqueue_script( 'jquery-base64', WPC()->plugin_url . 'js/jquery.b_64.min.js', array( 'jquery' ) );
                            break;

                        case 'repeat_invoices':
                        case 'repeat_invoice_edit':

                        case 'accum_invoices':
                        case 'accum_invoice_edit':

                        case 'invoice_edit':
                        case 'estimate_edit':
                        case 'request_estimate_edit':

                            wp_enqueue_script( 'jquery-ui-spinner' );

                            wp_enqueue_script( 'jquery-ui-sortable' );
                            wp_enqueue_script( 'wpc-nested-sortable-js', WPC()->plugin_url . 'js/jquery.mjs.nestedSortable.js', array(), false, true );

                            wp_register_style( 'wpc-jqueryui', WPC()->plugin_url . 'css/jqueryui/jquery-ui-1.10.3.css' );
                            wp_enqueue_style( 'wpc-jqueryui' );

                            wp_enqueue_script( 'jquery-base64', WPC()->plugin_url . 'js/jquery.b_64.min.js', array( 'jquery' ) );
                            break;

                    }
                }
            }

        }


        function delete_client( $user_id ) {
            $ids_inv = WPC()->assigns()->get_assign_data_by_assign( 'invoice', 'client', $user_id );
            $ids_est = WPC()->assigns()->get_assign_data_by_assign( 'estimate', 'client', $user_id );
            $ids = array_merge( $ids_inv, $ids_est ) ;
            $this->delete_data( $ids ) ;
        }


        /*
        * Show dashboard tables
        */
        function show_dashboard_tables() {
            global $wpdb;

            $wpc_currency = WPC()->get_settings( 'currency' );

            $total_outstanding_invoices_amount = $wpdb->get_results(
                "SELECT pm2.meta_value as currency, sum(pm.meta_value) as sum_amount, count(*) as count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_total' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                WHERE p.post_type = 'wpc_invoice' AND
                ( p.post_status = 'draft' OR p.post_status = 'sent' OR p.post_status = 'open' )
                GROUP BY pm2.meta_value
                ", ARRAY_A
            );

            if( 0 < count( $total_outstanding_invoices_amount ) ) {
                $count = count( $total_outstanding_invoices_amount ) + 1;
            } else {
                $count = 2;
                $total_outstanding_invoices_amount = array( 0 => array ( 'currency' => '', 'sum_amount' => 0, 'count' => 0 ) ) ;
            }

            $timestamp = time();

            $total_past_due_invoices_amuont = $wpdb->get_results(
                "SELECT pm2.meta_value as currency, sum(pm1.meta_value) as sum_amount, count(*) as count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_due_date' )
                LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                WHERE p.post_type = 'wpc_invoice' AND
                    p.post_status != 'paid' AND
                    pm.meta_value <= " . $timestamp . "
                GROUP BY pm2.meta_value
                ", ARRAY_A
            );

            if( 0 < count( $total_past_due_invoices_amuont ) ) {
                $count2 = count( $total_past_due_invoices_amuont ) + 1;
            } else {
                $count2 = 2;
                $total_past_due_invoices_amuont = array( 0 => array( 'currency' => '', 'sum_amount' => 0, 'count' => 0 ) ) ;
            }

        ?>
            <table class="wc_status_table widefat" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php _e( 'Outstanding Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                         <?php
                             foreach( $total_outstanding_invoices_amount as $currency ) {
                                 echo '<th>' . ( ( $currency['currency'] ) ? $wpc_currency[ $currency['currency'] ]['code'] : '' ) . '</th>' ;
                             }
                         ?>
                    </tr>
                </thead>
                <tbody>
                     <tr>
                         <td><?php _e( 'No. of Outstanding Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                         <?php
                             foreach( $total_outstanding_invoices_amount as $currency ) {
                                 echo '<td>' . $currency['count'] . '</td>' ;
                             }
                         ?>
                     </tr>
                    <tr>
                         <td><?php _e( 'Total amount of Outstanding Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                         <?php
                             foreach( $total_outstanding_invoices_amount as $currency ) {
                                 echo '<td>' . $this->get_currency( $currency['sum_amount'], false, $currency['currency'] ) . '</td>' ;
                             }
                         ?>
                     </tr>
                </tbody>
            </table>
            <table class="wc_status_table widefat" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php _e( 'Past Due Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                         <?php
                             foreach( $total_past_due_invoices_amuont as $currency ) {
                                 echo '<th>' . ( ( $currency['currency'] ) ? $wpc_currency[ $currency['currency'] ]['code'] : '' ) . '</th>' ;
                             }
                         ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e( 'No. of Past Due Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                         <?php
                             foreach( $total_past_due_invoices_amuont as $currency ) {
                                 echo '<td>' . $currency['count'] . '</td>' ;
                             }
                         ?>
                    </tr>
                    <tr>
                        <td><?php _e( 'Total amount of Past Due Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                         <?php
                             foreach( $total_past_due_invoices_amuont as $currency ) {
                                 echo '<td>' . $this->get_currency( $currency['sum_amount'], false, $currency['currency'] ) . '</td>' ;
                             }
                         ?>
                    </tr>
                </tbody>
            </table>
        <?php
        }



        /**
         * Save tax
         */
        function save_tax() {

            if ( isset( $_POST['tax']['name'] ) && '' != $_POST['tax']['name'] ) {
                $wpc_invoicing = WPC()->get_settings( 'invoicing' );

                $wpc_invoicing['taxes'][$_POST['tax']['name']]['description'] = isset( $_POST['tax']['description'] ) ? $_POST['tax']['description'] : '';
                $wpc_invoicing['taxes'][$_POST['tax']['name']]['rate'] = isset( $_POST['tax']['rate'] ) ? $_POST['tax']['rate'] : 1;

                WPC()->settings()->update( $wpc_invoicing, 'invoicing' );
                WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients_invoicing&tab=invoicing_taxes&msg=s' );
                exit;
            }

            WPC()->redirect( get_admin_url() . 'admin.php?page=wpclients_invoicing&tab=invoicing_taxes' );
            exit;

        }


        /**
         * Delete tax
         */
        function delete_tax( $id ) {

                $wpc_invoicing = WPC()->get_settings( 'invoicing' );

                if ( isset( $wpc_invoicing['taxes'][ $id ] ) ) {
                    unset( $wpc_invoicing['taxes'][ $id ] );

                    WPC()->settings()->update( $wpc_invoicing, 'invoicing' );
                }
        }


        /**
         * Delete items
         */
        function delete_items( $id ) {
            global $wpdb;

                $item_ids = ( is_array( $id ) ) ? $id : (array) $id;
                foreach ( $item_ids as $item_id) {
                   //delete item
                   $wpdb->delete( $wpdb->prefix . 'wpc_client_invoicing_items', array( 'id' => $item_id ), array( '%d' ) );


                    //delete item from est/inv
//                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_invoicing_items WHERE item_id = %d", $item_id ) );
                }


//            $items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_invoicing_items ", "ARRAY_A" );
//            return $items;
        }


        /**
         * save data of INV\EST
         */
        function save_data( $data ) {

            global $wpdb;

            $error = '';
            $options_update = array();
            $options_delete = array();

            //set type of data
            $type = 'inv';
            if( isset( $data['tab'] ) && 'invoice_edit' == $data['tab'] ) {
                $type = 'inv';
                $return_url = ( isset( $_POST['return_url'] ) ) ? $_POST['return_url'] : get_admin_url(). 'admin.php?page=wpclients_invoicing';
            } else {
                if ( isset( $_GET['tab'] ) ) {
                    switch( $_GET['tab'] ) {
                        case 'invoice_edit':
                            $type = 'inv';
                            $return_url = ( isset( $_POST['return_url'] ) ) ? $_POST['return_url'] : get_admin_url(). 'admin.php?page=wpclients_invoicing';
                            break;
                        case 'estimate_edit':
                            $type = 'est';
                            $return_url = ( isset( $_POST['return_url'] ) ) ? $_POST['return_url'] : get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=estimates';
                            break;

                    }
                }
            }


            //get clients ids
            $clients_id = array();
            if ( isset( $data['clients_id'] ) && '' != $data['clients_id'] ) {
                $clients_id = explode(',', $data['clients_id']);
            }
            $all_clients_id = $clients_id;

            //get client id from circles
            $groups_id = array();
            if ( isset( $data['groups_id'] ) && '' != $data['groups_id'] ) {
                $groups_id = explode( ',', $data['groups_id'] );

                $clients_of_grops = array();

                foreach( $groups_id as $group_id ) {
                    $clients_of_grops = array_merge( $clients_of_grops, WPC()->groups()->get_group_clients_id( $group_id ) );
                }

                $all_clients_id = array_unique( array_merge( $all_clients_id, $clients_of_grops ) );
            }

            $status = ( isset( $data['status'] ) ) ? $data['status'] : '' ;


            //not edit action
            if ( !( isset( $_GET['id'] ) ) && ( 'draft' != $status ) )  {
                //error no any clients
                if ( ( !is_array( $all_clients_id ) ||  0 >= count( $all_clients_id ) )  ) {
                    $error .= __( "Sorry, you should select clients or not empty circles.<br>", WPC_CLIENT_TEXT_DOMAIN ) ;
                }
            }


            $inv_number = '';
            if ( isset( $data['inv_number'] ) ) {
                $inv_number = $data['inv_number'];
                $new_number = $wpdb->get_var( $wpdb->prepare( "SELECT pm.meta_value
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND ( pm0.meta_value = '{$type}' ) )
                    LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_number' )
                    WHERE p.post_type = 'wpc_invoice' AND pm.meta_value='%s'", $inv_number ) );

                if( $new_number ) {
                    $error .= __( "Sorry, your invoice number already exists.<br>", WPC_CLIENT_TEXT_DOMAIN );
                }
            }

            $wpc_invoice_cf = WPC()->get_settings( 'invoice_cf' );

            $oldCF = array();
            if ( !empty( $_GET['id'] ) ) {
                $oldData = $this->get_data( $_GET['id'] );
                $oldCF = ( isset( $oldData['invoice_cf'] ) )
                        ? $oldData['invoice_cf'] : array();
            }

            foreach ( $wpc_invoice_cf as $slug => $cf ) {
                if ( !empty( $cf['field_readonly'] ) && '1' == $cf['field_readonly'] &&
                        !empty( $oldCF[ $slug ] ) ) {
                    $data['invoice_cf'][ $slug ] = $oldCF[ $slug ];
                }
                if ( !empty( $cf['required'] ) && '1' == $cf['required'] &&
                        empty( $data['invoice_cf'][ $slug ] ) ) {
                    $error .= sprintf( __( "Sorry, custom field '%s' is require.<br>", WPC_CLIENT_TEXT_DOMAIN ),
                        ( isset( $cf['title'] ) ? $cf['title'] : $slug ) );
                }
            }

            /*our_hook_
                hook_name: wpc_inv_custom_validation
                hook_title: Custom Validation
                hook_description: Hook checks additional field for validating
                hook_type: filter
                hook_in: wp-client-invoicing
                hook_location inv_class.admin.php
                hook_param: string $error, array $data
                hook_since: 1.9.1
            */
            $error = apply_filters( 'wpc_inv_custom_validation', $error, $data );

            //save data
            if ( '' == $error ) {

                $rate_capacity = $this->get_rate_capacity();

                if ( isset( $data['due_date'] ) && '' != $data['due_date'] ) {
                    //set date
                    $options_update['due_date'] = strtotime( $data['due_date'] . ' ' . date( 'H:i:s' ) );
                } else {
                    $options_delete[] = 'due_date';
                }


                $date = date( "Y-m-d H:i:s" );

                $title = ( isset( $data['title'] ) ) ? wp_unslash( $data['title'] ) : '' ;
                $description = ( isset( $data['description'] ) ) ? $data['description'] : '' ;
                $options_update['note'] = ( isset( $data['note'] ) ) ? $data['note'] : '' ;
                $options_update['terms'] = ( isset( $data['terms'] ) ) ? $data['terms'] : '' ;
                $options_update['currency'] = ( isset( $data['currency'] ) ) ? $data['currency'] : '' ;
                $options_update['custom_fields'] = ( isset( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ) ? $data['custom_fields'] : '' ;

                if ( isset( $data['deposit'] ) && ( !isset( $data['recurring'] ) || 'auto_recurring' != $data['recurring'] ) ) {
                    $options_update['deposit'] = $data['deposit'];
                    if ( isset( $data['min_deposit'] ) && 0 < (float)$data['min_deposit'] ) {
                        $options_update['min_deposit'] = round( (float)$data['min_deposit'], $rate_capacity ) ;
                    } else {
                        $options_delete[] = 'min_deposit';
                    }
                } else {
                    $options_delete[] = 'deposit';
                }

                if ( isset( $data['invoice_cf'] ) && is_array( $data['invoice_cf'] ) ) {
                    $options_update['invoice_cf'] = $data['invoice_cf'] ;
                } else {
                    $options_delete[] = 'invoice_cf';
                }

                if ( isset( $data['send_for_paid'] ) ) {
                    $options_update['send_for_paid'] = $data['send_for_paid'] ;
                } else {
                    $options_delete[] = 'send_for_paid';
                }

                if ( isset( $data['send_pdf_for_paid'] ) ) {
                    $options_update['send_pdf_for_paid'] = $data['send_pdf_for_paid'] ;
                } else {
                    $options_delete[] = 'send_pdf_for_paid';
                }

                if ( isset( $data['show_vat'] ) ) {
                    $options_update['show_vat'] = $data['show_vat'] ;
                } else {
                    $options_delete[] = 'show_vat';
                }

                //CC emails
                $options_update['cc_emails'] = ( isset( $data['cc_emails'] ) && is_array( $data['cc_emails'] ) ) ? $data['cc_emails'] : array();

                $send = ( isset( $data['send_email'] ) && 1 == $data['send_email'] ) ? 1 : 0 ;

                //update exist
                if ( isset( $data['id'] ) && 0 < $data['id'] ) {

                    $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                        post_title       = '%s',
                        post_content     = '%s',
                        post_modified    = '%s',
                        post_status      = '%s'
                        WHERE id = %d
                        ",
                        $title,
                        $description,
                        $date,
                        $status,
                        $data['id']
                    ) );


                    //convert EST to INV
                    if ( isset( $data['convert'] ) && '1' == $data['convert'] ) {
                        $this->convert_to_inv( $data['id'] );
                        $msg = 'c';
                    }

                    $this->calculate_items( $data, $data['id'] );
                    $this->save_meta_data( $data['id'], $options_update, $options_delete ) ;


                    //send INV to client
                    if ( $send ) {
                        $msg = 'us';
                        if ( 'inv' == $type ) {
                            $this->send_invoice( $data['id'] );
                        } else if ( 'est' == $type ) {
                            $this->send_estimate( $data['id'] );
                        }
                    } else {
                        $msg = 'u';
                    }

                } else {
                    //create new

                    if( !count( $all_clients_id ) ) {
                        $all_clients_id = array( 0 );
                    }

                    $i = 0;
                    foreach( $all_clients_id as $client_id ) {

                        $new_post = array(
                            'post_title'       => $title,
                            'post_content'     => $description,
                            'post_status'      => $status,
                            'post_type'        => 'wpc_invoice',
                            'post_date'        => $date,
                            //'post_author'      => $all_clients_id[0],
                        );

                        $id = wp_insert_post( $new_post  );

                        update_post_meta( $id, 'wpc_inv_post_type', $type );


                        //get new number
                        if ( '' == $inv_number ) {
                            $number = $this->get_next_number( true, $type );
                        } else {
                            if ( 0 == $i ) {
                                $number = $inv_number;
                                $i++;
                            } else {
                                do {
                                    $number = $inv_number . '-' . $i;
                                    $yes = $wpdb->get_var( $wpdb->prepare( "SELECT pm.meta_value
                                        FROM {$wpdb->posts} p
                                        INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = '{$type}' )
                                        LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_number' )
                                        WHERE ( p.post_type = 'wpc_invoice' ) AND pm.meta_value='%s'", $number ) );
                                    $i++;
                                } while( $yes ) ;
                            }
                        }

                        if( isset( $number ) && '' != $number ) {
                            update_post_meta( $id, 'wpc_inv_number', $number );
                        }



                        switch( $type ) {
                            case 'inv':
                                 $object_type = 'invoice' ;
                            break;
                            case 'est':
                                 $object_type = 'estimate';
                            break;
                        }


                        $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns SET
                            object_type     = '%s',
                            object_id       = '%d',
                            assign_type     = 'client',
                            assign_id       = '%d'
                            ",
                            $object_type,
                            $id,
                            $client_id
                        ));

                        $this->calculate_items( $data, $id );
                        $this->save_meta_data( $id, $options_update, $options_delete ) ;


                        //send INV to client
                        if ( $send ) {
                            $msg = 'as';
                            if ( 'inv' == $type ) {
                                $this->send_invoice( $id );
                            } else if ( 'est' == $type ) {
                                $this->send_estimate( $id );
                            }
                        } else {
                            $msg = 'a';
                        }

                    }
                }

                WPC()->redirect( $return_url . '&msg=' . $msg );
                exit;

            }
            return $error;
        }



        /**
         * save data of profile
         */
        function save_profile( $data ) {
            global $wpdb;

            $error = '';
            $options_update = array();
            $options_delete = array();

            //set type of data
            $type = 'inv';
            if ( isset( $_GET['tab'] ) ) {
                switch( $_GET['tab'] ) {
                    case 'accum_invoice_edit':
                        $type = 'accum_inv';
                        $return_url = ( isset( $_POST['return_url'] ) ) ? $_POST['return_url'] : get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=accum_invoices';
                        break;
                    case 'repeat_invoice_edit':
                        $type = 'repeat_inv';
                        $return_url = ( isset( $_POST['return_url'] ) ) ? $_POST['return_url'] : get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=repeat_invoices';
                        break;
                }
            }


            //stop accum profile
            if ( 'accum_inv' == $type && isset( $_GET['id'] ) && isset( $data['status'] ) && 'stopped' == $data['status'] ) {
                $wpdb->update( $wpdb->posts, array( 'post_status' => 'stopped' ), array( 'ID' => $_GET['id'] ), array( '%s' ), array( '%d' ) );
                delete_post_meta( $_GET['id'], 'wpc_inv_next_create_inv' ) ;

                WPC()->redirect( $return_url . '&msg=s' );
                exit;
            }



            //get clients ids
            $clients_id = array();
            if ( isset( $data['clients_id'] ) && '' != $data['clients_id'] ) {
                $clients_id = explode(',', $data['clients_id']);
            }
            $all_clients_id = $clients_id;


            //get client id from circles
            $groups_id = array();
            if ( isset( $data['groups_id'] ) && '' != $data['groups_id'] ) {
                $groups_id = explode( ',', $data['groups_id'] );

                $clients_of_groups = array();
                foreach( $groups_id as $group_id ) {
                    $clients_of_groups = array_merge( $clients_of_groups, WPC()->groups()->get_group_clients_id( $group_id ) );
                }

                $all_clients_id = array_unique( array_merge( $clients_of_groups, $all_clients_id ) );
            }

            $status = ( isset( $data['status'] ) ) ? $data['status'] : '' ;


            //not edit action
            if ( 'accum_inv' == $type && !isset( $_GET['id'] ) || 'repeat_inv' == $type && 'draft' != $status && 'stopped' != $status ) {
                //error no any clients
                if ( ( !is_array( $all_clients_id ) ||  0 >= count( $all_clients_id ) )  ) {
                    $error .= __( "Sorry, you should select clients or not empty circles.<br>", WPC_CLIENT_TEXT_DOMAIN ) ;
                }
            }


            $inv_number = '';
            if ( isset( $data['inv_number'] ) && ( $inv_number = $data['inv_number'] ) && $inv_number && ( 'inv' == $type || 'est' == $type ) ) {
                $new_number = $wpdb->get_var( $wpdb->prepare( "SELECT pm.meta_value
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND ( pm0.meta_value = 'inv' OR pm0.meta_value = 'est' ) )
                    LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_number' )
                    WHERE ( p.post_type = 'wpc_invoice' ) AND pm.meta_value='%s'", $inv_number ) );

                if( $new_number ) {
                    $error .= __( "Sorry, your invoice number already exists.<br>", WPC_CLIENT_TEXT_DOMAIN );
                }
            }

            if ( !empty( $inv_number ) ) {
                $options_update['inv_number'] = $inv_number;
            } else {
                $options_delete[] = 'inv_number';
            }


            $title = ( isset( $data['title'] ) ) ? wp_unslash( $data['title'] ) : '' ;
            if ( '' == $title ) {
                $error .= __( "Sorry, you should enter title.<br>", WPC_CLIENT_TEXT_DOMAIN ) ;
            }


            //save data
            if ( '' == $error ) {

                $rate_capacity = $this->get_rate_capacity();

                if ( isset( $data['due_date_number'] ) && '' != $data['due_date_number'] ) {
                    $options_update['due_date_number'] = $data['due_date_number'];
                } else {
                    $options_delete[] = 'due_date_number';
                }

                if ( isset( $data['accum_type'] ) ) {
                    $options_update['accum_type'] = $data['accum_type'] ;
                } else{
                    $options_delete[] = 'accum_type';
                }

                if ( !empty( $data['send_email_on_creation'] ) && ( !isset( $data['recurring_type'] ) || 'auto_charge' != $data['recurring_type'] ) || isset( $data['accum_type'] ) && 'invoice_send' == $data['accum_type'] ) {
                    $options_update['send_email_on_creation'] = 1;
                } else {
                    $options_delete[] = 'send_email_on_creation';
                }

                $date = date( "Y-m-d H:i:s" );

                $description = ( isset( $data['description'] ) ) ? $data['description'] : '' ;
                $options_update['note'] = ( isset( $data['note'] ) ) ? $data['note'] : '' ;
                $options_update['terms'] = ( isset( $data['terms'] ) ) ? $data['terms'] : '' ;
                $options_update['currency'] = ( isset( $data['currency'] ) ) ? $data['currency'] : '' ;

                $options_update['custom_fields'] = ( isset( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ) ? $data['custom_fields'] : '' ;


                if ( isset( $data['deposit'] ) && ( !isset( $data['recurring_type'] ) || 'auto_charge' != $data['recurring_type'] ) ) {
                    $options_update['deposit'] = $data['deposit'];
                    if ( isset( $data['min_deposit'] ) && 0 < (float)$data['min_deposit'] ) {
                        $options_update['min_deposit'] = round( (float)$data['min_deposit'], $rate_capacity ) ;
                    } else {
                        $options_delete[] = 'min_deposit';
                    }
                } else {
                    $options_delete[] = 'deposit';
                }



                //CC emails
                $options_update['cc_emails'] = ( isset( $data['cc_emails'] ) && is_array( $data['cc_emails'] ) ) ? $data['cc_emails'] : array();


                if ( isset( $data['recurring_type'] ) || 'accum_inv' == $type ) {

                    if ( isset( $data['recurring_type'] ) ) {
                        $options_update['recurring_type'] = $data['recurring_type'] ;
                    }

                    $array_per = array('day', 'week', 'month', 'year') ;

                    if ( isset( $data['billing_every'] ) && ($d = (int)$data['billing_every']) && 0 < $d ) {
                        $options_update['billing_every'] = $d;
                    } else {
                        $options_update['billing_every'] = 1;
                    }


                    if ( isset( $data['billing_cycle'] ) && ($d = (int)$data['billing_cycle']) && 0 < $d ) {
                        $options_update['billing_cycle'] = $d;
                    } else {
                        $options_delete[] = 'billing_cycle';
                    }

                    if ( isset( $data['billing_period'] ) && in_array( $data['billing_period'], $array_per ) ) {
                        $options_update['billing_period'] =  $data['billing_period'];
                    } else {
                        $options_update['billing_period'] = 'day';
                    }

                    if ( isset( $data['last_day_month'] ) && (!isset( $data['recurring_type'] ) || 'auto_charge' != $data['recurring_type']) ) {
                        $options_update['last_day_month'] = 1;
                        if ( isset( $data['from_date'] ) && preg_replace( '/\d{2}\/\d{2}\/\d{4}/', 'true', $data['from_date'] ) ) {
                            //$options_update['from_date'] = $data['from_date'] ;
                            $temp_date = strtotime( $data['from_date'] );
                        } else {
                            $temp_date = time();
                        }

                        $next_date = strtotime( date( "Y-m-d", strtotime( "last day of this month", $temp_date ) ) . " 00:00:00" ) ;
                        if ( 'draft' != $status ) {
                            $options_update['next_create_inv'] = $next_date;
                        }
                        $options_update['from_date'] = date( "m/d/Y", $next_date ) ;
                    } else {
                        $options_delete[] = 'last_day_month';

                        if ( isset( $data['from_date'] ) && preg_replace( '/\d{2}\/\d{2}\/\d{4}/', 'true', $data['from_date'] ) ) {
                            $options_update['from_date'] = $data['from_date'] ;
                        }

                        if ( isset( $options_update['from_date'] ) ) {
                            if ( 'draft' != $status ) {
                                $options_update['next_create_inv'] = strtotime( $options_update['from_date'] ) ;
                            }
                        }

                        if ( isset( $options_update['next_create_inv'] ) ) {
                            $gmt_offset =  get_option( 'gmt_offset' );
                            if ( false !== $gmt_offset ) {
                                $options_update['next_create_inv'] -= $gmt_offset * 3600;
                            }
                        }

                    }

                    if ( !isset( $options_update['from_date'] ) ) {
                        $options_delete[] = 'from_date' ;
                    }

                    if ( isset( $data['not_delete_discounts'] ) ) {
                        $options_update['not_delete_discounts'] = 1;
                    } else {
                        $options_delete[] = 'not_delete_discounts';
                    }

                    if ( isset( $data['not_delete_taxes'] ) ) {
                        $options_update['not_delete_taxes'] = 1;
                    } else {
                        $options_delete[] = 'not_delete_taxes';
                    }


                    if ( isset( $data['status_draft'] ) ) {
                        $options_update['status_draft'] = 1;
                    } else {
                        $options_delete[] = 'status_draft';
                    }

                } else {
                    $options_delete[] = 'recurring_type';
                }

                $id_profile = '';

                //update exist
                if ( isset( $data['id'] ) && 0 < $data['id'] ) {

                    $id_profile = $data['id'];

                    $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET
                        post_title       = '%s',
                        post_content     = '%s',
                        post_modified    = '%s',
                        post_status      = '%s'
                        WHERE id = %d
                        ",
                        $title,
                        $description,
                        $date,
                        $status,
                        $data['id']
                    ) );


                    $this->calculate_items( $data, $data['id'] );
                    $this->save_meta_data( $data['id'], $options_update, $options_delete ) ;


                    $msg = 'u';


                    if ( 'repeat_inv' == $type ) {
                        WPC()->assigns()->delete_all_object_assigns( 'repeat_invoice', $data['id'] ) ;
                        WPC()->assigns()->set_assigned_data( 'repeat_invoice', $data['id'], 'client', $clients_id );
                        WPC()->assigns()->set_assigned_data( 'repeat_invoice', $data['id'], 'circle', $groups_id );
                    }

                } else {
                    //create new
                    $new_post = array(
                            'post_title'       => $title,
                            'post_content'     => $description,
                            'post_status'      => $status,
                            'post_type'        => 'wpc_invoice',
                            'post_date'        => $date,
                            //'post_author'      => $all_clients_id[0],
                    );

                    $id = $id_profile = wp_insert_post( $new_post  );

                    update_post_meta( $id, 'wpc_inv_post_type', $type );
                    update_post_meta( $id, 'wpc_inv_count_create_inv', count( $all_clients_id ) );

                    switch( $type ) {
                        case 'accum_inv':
                             $object_type = 'accum_invoice';
                        break;
                        case 'repeat_inv':
                             $object_type = 'repeat_invoice';
                        break;
                    }

                    if ( 'accum_inv' == $type ) {
                        WPC()->assigns()->set_assigned_data( $object_type, $id, 'client', array( $all_clients_id[0] ) );
                    } else {
                        WPC()->assigns()->set_assigned_data( $object_type, $id, 'client', $clients_id );
                        WPC()->assigns()->set_assigned_data( $object_type, $id, 'circle', $groups_id );
                    }
                    /*{
                        foreach ( $clients_id as $client ) {
                            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns SET
                                object_type     = '%s',
                                object_id       = '%d',
                                assign_type     = 'client',
                                assign_id       = '%d'
                                ",
                                $object_type,
                                $id,
                                $client
                            ));
                        }
                        foreach ( $groups_id as $group ) {
                            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns SET
                                object_type     = '%s',
                                object_id       = '%d',
                                assign_type     = 'circle',
                                assign_id       = '%d'
                                ",
                                $object_type,
                                $id,
                                $group
                            ));
                        }
                    }*/

                    $this->calculate_items( $data, $id );
                    $this->save_meta_data( $id, $options_update, $options_delete ) ;

                    $msg = 'a';

                }

                if ( 'draft' != $status && 'stopped' != $status && isset( $options_update['next_create_inv'] ) && time() > $options_update['next_create_inv'] ) {
                    $this->create_inv_from_profile( $id_profile ) ;
                    $msg .= 'i';
                }


                WPC()->redirect( $return_url . '&msg=' . $msg );
                exit;

            }
            return $error;
        }



        function calculate_items( $data, $id ) {

            $rate_capacity = $this->get_rate_capacity();

            //get items
            $items = $options = array();

            $options['late_fee'] = 0;
            if ( isset( $data['late_fee'] ) && '' != $data['late_fee'] ) {
                $options['late_fee'] = round( (float)$data['late_fee'], $rate_capacity );
            }

            $total_items = 0;

            if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
                array_shift( $data['items'] );
                foreach ( $data['items'] as $item ) {

                    //$temp_item = (array) json_decode( base64_decode( $item ) );
                    $temp_item = $item;
                    $temp_item['quantity'] = ( isset( $temp_item['quantity'] ) && is_numeric( $temp_item['quantity'] ) && 0 < $temp_item['quantity'] ) ? $temp_item['quantity'] : '1';
                    $temp_item['price'] = '' . round( (float)$temp_item['price'], $rate_capacity ) ;
                    $total_items += round( $temp_item['price'] * $temp_item['quantity'], $rate_capacity );
                    $items[] = $temp_item;
                }
            }
            $options['sub_total'] = '' . $total_items;
            $options['items'] = $items;

            //get discounts
            $discounts = array();
            $total_discount = 0;
            if ( isset( $data['discounts'] ) && is_array( $data['discounts'] ) ) {
                foreach ( $data['discounts'] as $discount ) {
                    $temp_disc = $discount;
                    $temp_disc['rate'] = ( isset( $discount['rate'] ) ) ? '' . round( (float)$discount['rate'], $rate_capacity ) : '0';

                    if ( isset( $temp_disc['type'] ) && 'amount' == $temp_disc['type'] ) {
                        $total_discount += $temp_disc['rate'];
                    } else if ( isset( $temp_disc['type'] ) && 'percent' == $temp_disc['type'] ) {
                        $total_discount += round( $total_items * $temp_disc['rate'] / 100, $rate_capacity );
                    }
                    $discounts[] = $temp_disc;
                }
            }
            $options['discounts'] = $discounts ;
            $options['total_discount'] = $total_discount ;

            //get tax
            $total_tax = 0;
            $taxes = array();
            if ( isset( $data['taxes'] ) && is_array( $data['taxes'] ) ) {
                foreach ( $data['taxes'] as $tax ) {
                    $temp_tax = $tax;
                    $temp_tax['rate'] = ( isset( $tax['rate'] ) ) ? '' . round( (float)$tax['rate'], $rate_capacity ) : '0';
                    if ( isset( $tax['type'] ) && 'before' == $tax['type'] ) {
                        $total_tax += round( $total_items * $temp_tax['rate'] / 100, $rate_capacity );
                    } else if ( isset( $tax['type'] ) && 'after' == $tax['type'] ) {
                        $total_tax += round( ( $total_items - $total_discount ) * $temp_tax['rate'] / 100, $rate_capacity );
                    }
                    $taxes[] = $temp_tax;
                }
            }
            $options['total_tax'] = $total_tax ;
            $options['taxes'] = $taxes ;

            $added_late_fee = 0;
            if ( isset( $_GET['id'] ) ) {
                $added_late_fee = get_post_meta( $_GET['id'], 'wpc_inv_added_late_fee', true );
            }

            $options['total'] = round( $total_items - $total_discount + $total_tax + $added_late_fee, $rate_capacity );

            foreach( $options as $key => $option ) {
                update_post_meta( $id, 'wpc_inv_' . $key, $option);
            }

        }





        function save_meta_data( $id, $options_update, $options_delete = array() ) {

            if ( is_array( $options_update ) && count( $options_update ) ) {
                foreach( $options_update as $key => $option ) {
                    update_post_meta( $id, 'wpc_inv_' . $key, $option);
                }
            }


            if ( is_array( $options_delete ) && count( $options_delete ) ) {
                foreach( $options_delete as $key ) {
                    delete_post_meta( $id, 'wpc_inv_' . $key);
                }
            }

        }


        function cancel_subscription( $bill, $payments ) {
            global $wpc_payments_core;

            $wpc_payments_core->load_gateway_plugins();

            do_action( 'wpc_cancel_subscription_' . $bill, $payments );
        }


        /**
         * Delete invoice
         */
        function delete_data( $id ) {
            global $wpdb;
            $invoice_ids = ( is_array( $id ) ) ? $id : (array) $id;
            $object_type = 'invoice';
            if( isset( $_GET['tab'] ) ) {
                switch( $_GET['tab'] ) {
                    case 'accum_invoices':
                    case 'accum_invoice_edit':
                        $object_type = 'accum_invoice';
                    break;
                    case 'repeat_invoices':
                    case 'repeat_invoice_edit':
                        $object_type = 'repeat_invoice';
                    break;
                    case 'estimates':
                    case 'estimate_edit':
                        $object_type = 'estimate';
                    break;
                    case 'request_estimates':
                    case 'request_estimate_edit':
                        $object_type = 'request_estimate';
                    break;
                }
            }

            foreach ( $invoice_ids as $invoice_id ) {
                $orders = get_post_meta( $invoice_id, 'wpc_inv_order_id', true );

                if ( 'repeat_invoice' == $object_type  ) {
                    $profile_payments = $wpdb->get_results( "SELECT `id`, `payment_method`, `client_id` "
                        . "FROM {$wpdb->prefix}wpc_client_payments "
                        . "WHERE `data` LIKE '%\"profile_id\":\"{$invoice_id}\"%' "
                        . "AND `subscription_status` != 'expired' "
                        . "AND `subscription_id` IS NOT NULL "
                        . "GROUP BY `subscription_id`"
                            , ARRAY_A );
                    $sort_payments = array();
                    foreach ( $profile_payments as $payment ) {
                        if ( !empty( $payment['payment_method'] ) ) {
                            $sort_payments[ $payment['payment_method'] ][] = $payment['id'];

                            $client_id = $payment['client_id'] ? $payment['client_id'] : 0 ;

                            if ( $client_id ) {
                                $meta_key = 'wpc_inv_clients_info';
                                $clients_info = get_post_meta( $id, $meta_key, true);
                                if ( is_array( $clients_info ) ) {
                                    $clients_info[ $client_id ] = 'canceled';
                                    update_post_meta($id, $meta_key, $clients_info);
                                }
                            }

                        }
                    }
                    foreach ( $sort_payments as $bill => $payments ) {
                        $this->cancel_subscription( $bill, $payments );
                    }

                }

                if( $orders ) {
                    if( !is_array( $orders ) ) {
                        $orders = (array)$orders ;
                    }
                    foreach ( $orders as $order_id ) {
                        $wpdb->delete( $wpdb->prefix . 'wpc_client_payments', array( 'id' => $order_id ) );
                    }
                }

                //delete item
                $wpdb->delete( $wpdb->posts, array( 'id' => $invoice_id ) );
                $wpdb->delete( $wpdb->postmeta, array( 'post_id' => $invoice_id ) );
                $wpdb->delete( $wpdb->prefix . 'wpc_client_objects_assigns', array( 'object_type' => $object_type, 'object_id' => $invoice_id ) );
            }
        }



        /**
         * save payment of invoice
         */
        function save_payment( $data ) {
            global $wpdb;

            /*our_hook_
                    hook_name: wpc_client_invoice_save_payment_data
                    hook_title: Change data on save invoice payment
                    hook_description:
                    hook_type: filter
                    hook_in: wp-client-invoicing
                    hook_location
                    hook_param: array $data
                    hook_since: 1.8.4
                */
            $data = apply_filters( 'wpc_client_invoice_save_payment_data', $data );

            $wpc_invoicing = WPC()->get_settings( 'invoicing' );

            $rate_capacity = $this->get_rate_capacity();

            $error = '';

            if ( isset( $data['inv_id'] ) && '' == trim( $data['inv_id'] ) ) {
                $error .= __( "Sorry, wrong Invoice number.<br>", WPC_CLIENT_TEXT_DOMAIN );
            }

            if ( isset( $data['amount'] ) && '' == trim( $data['amount'] ) ) {
                $error .= __( "Sorry, Payment amount is required.<br>", WPC_CLIENT_TEXT_DOMAIN );
            }

            if ( isset( $data['date'] ) && '' == trim( $data['date'] ) ) {
                $error .= __( "Sorry, Payment date is required.<br>", WPC_CLIENT_TEXT_DOMAIN );
            }

            if ( isset( $data['method'] ) && '' == trim( $data['method'] ) ) {
                $error .= __( "Sorry, Payment method is required.<br>", WPC_CLIENT_TEXT_DOMAIN );
            }

            $inv = $this->get_data( $data['inv_id'] );

            if ( !empty( $inv['status'] ) && 'paid' == $inv['status'] ) {
                $error .= __( "Sorry, This Invoice already paid.<br>", WPC_CLIENT_TEXT_DOMAIN );
            }


            //save data
            if ( '' == $error ) {

                $paid_total = 0;
                $status     = 'paid';

                $data['amount'] = number_format( $data['amount'], $rate_capacity, '.', '' );

                if ( isset( $inv['order_id'] ) && $inv['order_id'] ) {

                    $order_id = $inv['order_id'] ;

                    //get alredy paid total
                    $amounts_arr = $wpdb->get_col( "SELECT amount FROM {$wpdb->prefix}wpc_client_payments WHERE id IN ('" . implode( "','", $order_id ) . "')" );

                    if ( is_array( $amounts_arr ) && $amounts_arr ) {
                        foreach ( $amounts_arr as $amount ) {
                            $amount = str_replace( ',', '.', $amount );
                            $paid_total += $amount;
                        }
                    }

                    $paid_total += str_replace( ',', '.', $data['amount'] );
                    if ( $paid_total < $inv['total'] ) {
                        $status = 'partial';
                    }

                } else {

                    if ( $data['amount'] < $inv['total'] ) {
                        $status = 'partial';
                    }

                    $order_id = array();
                }

                $timestamp = strtotime( $data['date'] );

                if ( isset( $data['currency'] ) ) {
                    $currency =  $data['currency'] ;
                } else {
                    $currency = WPC()->get_default_currency();
                }

                $currencies = WPC()->get_settings( 'currency' );

                $currency = ( isset( $currencies[ $currency ]['code'] ) ) ? $currencies[ $currency ]['code'] : 'USD' ;

                $notes = ( !empty( $data['notes'] ) ) ? json_encode( array( 'notes' => $data['notes'] ) ) : '' ;

                $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_payments SET
                    order_status = %s,
                    function = %s,
                    payment_method = '%s',
                    client_id = '%d',
                    amount = '%s',
                    currency = '%s',
                    transaction_id = '%s',
                    transaction_status = '%s',
                    time_created = '%s',
                    time_paid = '%s',
                    data = %s
                    ",
                    //order_id = %s,
                    //$order_id,
                    'paid',
                    'invoicing',
                    $data['method'],
                    $inv['client_id'],
                    (float)$data['amount'],
                    $currency,
                    '',
                    '',
                    $timestamp,
                    $timestamp,
                    $notes
                ) );

                $order_id[] = $wpdb->insert_id;

                //change status of INV
                $wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'id' => $inv['id'] ) ) ;
                update_post_meta( $inv['id'], 'wpc_inv_order_id', $order_id);

                $args = array(
                    'invoice_id' => $data['inv_id'],
                    'invoice_amount' => $data['amount'],
                    'invoice_date' => WPC()->date_format( time() ),
                    'client_id' => $inv['client_id'],
                    'invoicing_title' => $inv['title'],
                    'inv_number' => $inv['number'],
                    'total_amount' => isset( $inv['total'] ) ? $inv['total'] : '',
                    'minimum_payment' => isset( $inv['min_deposit'] ) ? $inv['min_deposit'] : '',
                    'due_date' => isset( $inv['due_date'] ) ? WPC()->date_format( $inv['due_date'], 'date' ) : '',
                    'payment_method' => $this->get_payment_method( 0, $data['method'] ),
                );

                $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                $attach_pdf = empty( $wpc_invoicing['send_pdf_for_paid'] ) || 'no' !== $wpc_invoicing['send_pdf_for_paid'];

                $userdata       = get_userdata( $inv['client_id'] );
                //client are exist
                if ( $userdata ) {
                //send thank you message to client
                    if ( 'paid' == $status && (
                            isset( $inv['send_for_paid'] ) && 1 == $inv['send_for_paid']
                            && !$attach_pdf )
                            || ( isset( $data['thanks'] ) && 1 == $data['thanks'] )
                    ) {
                        WPC()->mail( 'pay_tha', $userdata->get( 'user_email' ), $args, 'invoice_thank_you' );
                    }elseif( 'paid' == $status && (
                            isset( $inv['send_for_paid'] ) && 1 == $inv['send_for_paid']
                            && $attach_pdf )
                    ) {
                        $invoice_data = $this->get_data( $data['inv_id'] );
                        ob_start();
                        $content = $this->invoicing_put_values( $invoice_data );
                        $attachments = array();
                        $attachment = $this->get_pdf( $invoice_data, $content );
                        $attachments[] = $attachment;
                        ob_end_clean();
                        WPC()->mail( 'pay_tha', $userdata->get( 'user_email' ), $args, 'invoice_thank_you', $attachments );
                    }
                }

            }

            return $error;
        }

        /**
         * Add Setting link at plugin page
         * @param $links
         * @return mixed
         */
        public function filter_action_links( $links ) {

            if ( WPC()->is_licensed( 'WP-Client' ) ) {

                $links['settings'] = sprintf( '<a href="admin.php?page=wpclients_invoicing&tab=settings">%s</a>', __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ) );

            }

            return $links;

        }

    //end class
    }

}