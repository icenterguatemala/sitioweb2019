<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'WPC_INV_Admin_Common' ) ) {

    class WPC_INV_Admin_Common extends WPC_INV_Common {


        /**
        * constructor
        **/
        function inv_admin_common_construct() {

            //$this->inv_common_construct();


            //add ez hub settings
            add_filter( 'wpc_client_ez_hub_invoicing_list', array( &$this, 'add_ez_hub_settings' ), 12, 4 );
            add_filter( 'wpc_client_get_ez_shortcode_invoicing_list', array( &$this, 'get_ez_shortcode_invoicing_list' ), 10, 2 );
            add_filter( 'wpc_client_get_shortcode_elements', array( &$this, 'get_shortcode_element' ), 10 );
            add_filter( 'wp_client_capabilities_maps', array( &$this, 'add_capabilities_maps' ), 10 );

            add_filter( 'wpc_client_pre_set_pages_array', array( &$this, 'pre_set_pages' ) );
        }

        /*
        * Pre set pages
        */
        function pre_set_pages( $wpc_pre_pages_array ) {
            $wpc_pages = array(
                array(
                    'title'     => __( 'Invoicing', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Invoicing',
                    'desc'      => __( 'Page content: [wpc_client_invoicing]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'invoicing_page_id',
                    'old_id'    => 'invoicing',
                    'shortcode' => true,
                    'content'   => '[wpc_client_invoicing]',
                ),
                array(
                    'title'     => __( 'Invoicing List', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Invoicing List',
                    'desc'      => __( 'Page content: [wpc_client_invoicing_list]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'invoicing_list_page_id',
                    'old_id'    => '',
                    'shortcode' => true,
                    'content'   => '[wpc_client_invoicing_list]',
                ),
                array(
                    'title'     => __( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Estimate Request',
                    'desc'      => __( 'Page content: [wpc_client_inv_request_estimate]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'request_estimate_page_id',
                    'old_id'    => '',
                    'shortcode' => true,
                    'content'   => '[wpc_client_inv_request_estimate]',
                ),
            );

            if ( is_array( $wpc_pre_pages_array ) ) {
                $wpc_pre_pages_array = array_merge( $wpc_pre_pages_array, $wpc_pages );
            } else {
                $wpc_pre_pages_array = $wpc_pages;
            }

            return $wpc_pre_pages_array;
        }


        /**
         * Save items
         */
        function save_items( $item ) {
            global $wpdb;

                $errors = '';

                if ( !isset( $item['name'] ) || '' == $item['name'] ) {
                    $errors .= __( 'A Item Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
                } else {
                    $name = stripslashes( $item['name'] );
                }

                if ( !isset( $item['rate'] ) || '' == $item['rate'] ) {
                    $errors .= __( 'A Item Rate is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
                }

                if ( '' == $errors ) {

                    $item_description = '';
                    if ( isset( $item['description'] ) && '' != $item['description'] ) {
                        //check if there are more characters then allowed
                        $item_description = stripslashes( $item['description'] );
                    }

                    //custom fields
                    $cf = array();
                    $custom_fields = WPC()->get_settings( 'inv_custom_fields' );
                    foreach ( $custom_fields as $key => $val ) {
                        $cf[ $key ] = isset( $item[ $key ] ) ? $item[ $key ] : '';
                    }

                    $table = $wpdb->prefix . 'wpc_client_invoicing_items';
                    $data = array(
                                    'name'          => $name,
                                    'description'   => $item_description,
                                    'rate'          => $item['rate'],
                                    'use_r_est'     => !empty( $item['use_r_est'] ) ? '1' : '',
                                    'data'          => serialize( $cf ),
                                    );
                    $format = array(
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%s',
                                    );

                    if ( empty( $item['id'] ) ) {
                        $wpdb->insert( $table, $data, $format );
                    } else {
                        $where = array(
                            'id' => $item['id'],
                        );
                        $where_format = array(
                            '%d',
                        );
                        $wpdb->update( $table, $data, $where, $format, $where_format );
                    }

                }
                return $errors;
        }



        /*
        * Add ez hub settings
        */
        function add_ez_hub_settings( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {
            $title = __( 'Invoicing List', WPC_CLIENT_TEXT_DOMAIN ) ;
            $text_copy = '{invoicing_list_' . $item_number . '}' ;

            ob_start();
            ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="invoicing_list_text_<?php echo $item_number ?>"><?php _e( 'Text: "Invoicing List"',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][invoicing_list][text]" id="invoicing_list_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Invoicing List', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo WPC()->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_type_<?php echo $item_number ?>"><?php _e( 'Type', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][type]" id="invoicing_list_type_<?php echo $item_number ?>" class="wpc_invoicing_list_type" data-number="<?php echo $item_number ?>">
                                        <option value="invoice" <?php echo ( !isset( $hub_settings['type'] ) || 'invoice' == $hub_settings['type'] ) ? 'selected' : '' ?>><?php _e( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="estimate" <?php echo ( isset( $hub_settings['type'] ) && 'estimate' == $hub_settings['type'] ) ? 'selected' : '' ?>><?php _e( 'Estimate', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="request_estimate" <?php echo ( isset( $hub_settings['type'] ) && 'request_estimate' == $hub_settings['type'] ) ? 'selected' : '' ?>><?php _e( 'Estimate Request', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="invoicing_list_status_<?php echo $item_number ?>"><?php _e( 'Status', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][status]" id="invoicing_list_status_<?php echo $item_number ?>">
                                        <option value="" <?php echo ( !isset( $hub_settings['status'] ) || '' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php _e( '---', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option class="wpc_option_show_<?php echo $item_number ?>" value="sent" <?php echo ( isset( $hub_settings['status'] ) && 'sent' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php _e( 'Open', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option class="wpc_option_show_<?php echo $item_number ?>" value="draft" <?php echo ( isset( $hub_settings['status'] ) && 'draft' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php _e( 'Draft', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option class="wpc_option_show_<?php echo $item_number ?>" value="partial" <?php echo ( isset( $hub_settings['status'] ) && 'partial' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php _e( 'Partial', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option class="wpc_option_show_<?php echo $item_number ?>" value="paid" <?php echo ( isset( $hub_settings['status'] ) && 'paid' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php _e( 'Paid', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option class="wpc_option_show_<?php echo $item_number ?>" value="refunded" <?php echo ( isset( $hub_settings['status'] ) && 'refunded' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php _e( 'Refunded', WPC_CLIENT_TEXT_DOMAIN ) ?></option>

                                        <option class="wpc_option_hide_<?php echo $item_number ?>" value="waiting_client" <?php echo ( isset( $hub_settings['status'] ) && 'waiting_client' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php printf( __( 'Waiting on %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                                        <option class="wpc_option_hide_<?php echo $item_number ?>" value="waiting_admin" <?php echo ( isset( $hub_settings['status'] ) && 'waiting_admin' == $hub_settings['status'] ) ? 'selected' : '' ?>><?php printf( __( 'Waiting on %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_show_date_<?php echo $item_number ?>"><?php _e( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][show_date]" id="invoicing_list_show_date_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['show_date'] ) || 'no' == $hub_settings['show_date'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['show_date'] ) && 'yes' == $hub_settings['show_date'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_show_due_date_<?php echo $item_number ?>"><?php _e( 'Show Due Date', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][show_due_date]" id="invoicing_list_show_due_date_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['show_due_date'] ) || 'yes' !== $hub_settings['show_due_date'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['show_due_date'] ) && 'yes' == $hub_settings['show_due_date'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_show_description_<?php echo $item_number ?>"><?php _e( 'Show Description', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][show_description]" id="invoicing_list_show_description_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['show_description'] ) || 'no' == $hub_settings['show_description'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['show_description'] ) && 'yes' == $hub_settings['show_description'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_show_type_payment_<?php echo $item_number ?>"><?php _e( 'Show Type Payment', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][show_type_payment]" id="invoicing_list_show_type_payment_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['show_type_payment'] ) || 'no' == $hub_settings['show_type_payment'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['show_type_payment'] ) && 'yes' == $hub_settings['show_type_payment'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_show_invoicing_currency_<?php echo $item_number ?>"><?php _e( 'Show Total Amount', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][show_invoicing_currency]" id="invoicing_list_show_invoicing_currency_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['show_invoicing_currency'] ) || 'no' == $hub_settings['show_invoicing_currency'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['show_invoicing_currency'] ) && 'yes' == $hub_settings['show_invoicing_currency'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="invoicing_list_pay_now_links_<?php echo $item_number ?>"><?php _e( 'Show "Pay Now" Links', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][invoicing_list][pay_now_links]" id="invoicing_list_pay_now_links_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['pay_now_links'] ) || 'no' == $hub_settings['pay_now_links'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['pay_now_links'] ) && 'yes' == $hub_settings['pay_now_links'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready( function() {
                        jQuery( '.wpc_invoicing_list_type' ).change( function() {
                            var number = jQuery( this ).data( 'number' );
                            if ( jQuery( this ).val() === 'request_estimate' ) {
                                jQuery( '.wpc_option_hide_' + number ).css( 'display', 'block' );
                                jQuery( '.wpc_option_show_' + number ).css( 'display', 'none' );
                                jQuery( '#invoicing_list_status_' + number ).val('');
                            } else {
                                jQuery( '.wpc_option_hide_' + number ).css( 'display', 'none' );
                                jQuery( '.wpc_option_show_' + number ).css( 'display', 'block' );
                                jQuery( '#invoicing_list_status_' + number ).val('');
                            }
                        });
                        jQuery( '.wpc_invoicing_list_type' ).each( function() {
                            //jQuery( this ).trigger( 'click' );
                            var number = jQuery( this ).data( 'number' );
                            if ( jQuery( this ).val() === 'request_estimate' ) {
                                jQuery( '.wpc_option_hide_' + number ).css( 'display', 'block' );
                                jQuery( '.wpc_option_show_' + number ).css( 'display', 'none' );
                            } else {
                                jQuery( '.wpc_option_hide_' + number ).css( 'display', 'none' );
                                jQuery( '.wpc_option_show_' + number ).css( 'display', 'block' );
                            }
                        });
                    });
                </script>
            <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez shortcode
        */
        function get_ez_shortcode_invoicing_list( $tabs_items, $hub_settings = array() ) {
            $temp_arr = array();
            $temp_arr['menu_items']['invoicing_list'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            if ( isset( $hub_settings['type'] ) && '' != $hub_settings['type'] ) {
                $attrs .= ' type="' . $hub_settings['type'] . '" ';
            } else {
                $attrs .= ' type="invoice" ';
            }

            if ( isset( $hub_settings['status'] ) && '' != $hub_settings['status'] ) {
                $attrs .= ' status="' . $hub_settings['status'] . '" ';
            } else {
                $attrs .= ' status="" ';
            }

            if ( isset( $hub_settings['show_date'] ) && '' != $hub_settings['show_date'] ) {
                $attrs .= ' show_date="' . $hub_settings['show_date'] . '" ';
            } else {
                $attrs .= ' show_date="no" ';
            }

            if ( isset( $hub_settings['show_due_date'] ) && '' != $hub_settings['show_due_date'] ) {
                $attrs .= ' show_due_date="' . $hub_settings['show_due_date'] . '" ';
            } else {
                $attrs .= ' show_due_date="no" ';
            }

            if ( isset( $hub_settings['show_description'] ) && '' != $hub_settings['show_description'] ) {
                $attrs .= ' show_description="' . $hub_settings['show_description'] . '" ';
            } else {
                $attrs .= ' show_description="no" ';
            }

            if ( isset( $hub_settings['show_type_payment'] ) && '' != $hub_settings['show_type_payment'] ) {
                $attrs .= ' show_type_payment="' . $hub_settings['show_type_payment'] . '" ';
            } else {
                $attrs .= ' show_type_payment="no" ';
            }

            if ( isset( $hub_settings['show_invoicing_currency'] ) && '' != $hub_settings['show_invoicing_currency'] ) {
                $attrs .= ' show_invoicing_currency="' . $hub_settings['show_invoicing_currency'] . '" ';
            } else {
                $attrs .= ' show_invoicing_currency="no" ';
            }

            if ( isset( $hub_settings['pay_now_links'] ) && '' != $hub_settings['pay_now_links'] ) {
                $attrs .= ' pay_now_links="' . $hub_settings['pay_now_links'] . '" ';
            } else {
                $attrs .= ' pay_now_links="no" ';
            }

            $temp_arr['page_body'] = '[wpc_client_invoicing_list ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * get shortcode element
        */
        function get_shortcode_element( $elements ) {
            $elements['invoicing_list'] = __( 'Invoicing List', WPC_CLIENT_TEXT_DOMAIN );
            return $elements;
        }


        /*
        * add capability for maneger
        */
        function add_capabilities_maps( $capabilities_maps ) {
            $additional_capabilities = array(
                'wpc_manager' => array(
                    'variable' => array(
                        'wpc_create_invoices'               => array( 'cap' => false, 'label' => sprintf( __( "Create Invoices to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_delete_invoices'               => array( 'cap' => false, 'label' => sprintf( __( "Delete Invoices of %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_create_repeat_invoices'        => array( 'cap' => false, 'label' => sprintf( __( "Create Recurring Profiles to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_delete_repeat_invoices'        => array( 'cap' => false, 'label' => sprintf( __( "Delete Recurring Profiles to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_create_accum_invoices'         => array( 'cap' => false, 'label' => sprintf( __( "Create Accumulating Profiles to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_delete_accum_invoices'         => array( 'cap' => false, 'label' => sprintf( __( "Delete Accumulating Profiles to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_add_payment'                   => array( 'cap' => false, 'label' => __( "Add Payment to Invoice", WPC_CLIENT_TEXT_DOMAIN ) ),
                        'wpc_create_estimates'              => array( 'cap' => false, 'label' => sprintf( __( "Create Estimates to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_delete_estimates'              => array( 'cap' => false, 'label' => sprintf( __( "Delete Estimates to %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                        'wpc_modify_items'                  => array( 'cap' => false, 'label' => __( "Modify Invoicing Items", WPC_CLIENT_TEXT_DOMAIN ) ),
                        'wpc_modify_taxes'                  => array( 'cap' => false, 'label' => __( "Modify Invoicing Taxes", WPC_CLIENT_TEXT_DOMAIN ) ),
                        'wpc_create_inv_custom_fields'      => array( 'cap' => false, 'label' => __( "Create Custom Fields for Invoice", WPC_CLIENT_TEXT_DOMAIN ) ),
                        'wpc_estimate_requests'             => array( 'cap' => false, 'label' => sprintf( __( "Access to %s Estimate Requests", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
                    )
                ),
                'wpc_client_staff' => array(
                    'variable' => array(
                        'wpc_view_invoices'               => array( 'cap' => false, 'label' => sprintf( __( "View %s Invoices", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
                        'wpc_paid_invoices'               => array( 'cap' => false, 'label' => sprintf( __( "Paid %s Invoices", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
                    )
                )
            );
            return WPC()->admin()->merge_capabilities( $capabilities_maps, $additional_capabilities );
        }


    //end class
    }

}
