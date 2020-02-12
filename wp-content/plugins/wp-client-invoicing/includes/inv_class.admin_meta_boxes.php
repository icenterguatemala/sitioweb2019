<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( "WPC_INV_Admin_Meta_Boxes" ) ) {

    class WPC_INV_Admin_Meta_Boxes extends WPC_INV_Admin_Common {


        /**
        * Meta constructor
        **/
        function meta_construct() {

            add_action( 'wpc_client_add_meta_boxes', array( &$this, 'meta_init' ) );

        }


        /*
        * Add meta box
        */
        function meta_init() {

            //meta box
            if ( !( isset( $_GET['tab'] ) && 'request_estimate_edit' == $_GET['tab'] ) ) {
                add_meta_box( 'wpc_invoice_publish', __( 'Publish', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'publish' ), 'wp-client_page_wpclients_invoicing', 'side', 'high' );
            } else {
                add_meta_box( 'wpc_invoice_publish', __( 'Publish', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'update_for_r_est' ), 'wp-client_page_wpclients_invoicing', 'side', 'high' );
            }

            add_meta_box( 'wpc_invoice_assign', __( 'User Information', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'user_info' ), 'wp-client_page_wpclients_invoicing', 'side', 'high' );

            $wpc_invoice_cf = WPC()->get_settings( 'invoice_cf' );
            if ( !empty( $wpc_invoice_cf ) ) {
                add_meta_box( 'wpc_invoice_cf', __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'invoice_cf' ), 'wp-client_page_wpclients_invoicing', 'side', 'high', $wpc_invoice_cf );
            }

            if ( !( isset( $_GET['tab'] ) && 'request_estimate_edit' == $_GET['tab'] ) ) {
                add_meta_box( 'wpc_invoice_custom_fields', __( 'Item Fields', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'custom_fields' ), 'wp-client_page_wpclients_invoicing', 'side', 'high' );
            }

            if( isset( $_GET['tab'] ) && 'invoice_edit' == $_GET['tab'] && isset( $_GET['id']) && ($orders = get_post_meta( $_GET['id'], 'wpc_inv_order_id', true ) ) && is_array( $orders ) && count( $orders ) ) {
                add_meta_box( 'wpc_invoice_history', __( 'Invoice Status and History', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'history' ), 'wp-client_page_wpclients_invoicing', 'normal', 'high' );
            }
            $title_box = __( 'Invoice Items', WPC_CLIENT_TEXT_DOMAIN );
            if ( isset( $_GET['tab'] ) ) {
                switch ( $_GET['tab'] ) {
                    case 'estimate_edit':
                        $title_box = __( 'Estimate Items', WPC_CLIENT_TEXT_DOMAIN ) ;
                        break;
                    case 'repeat_invoice_edit':
                        $title_box = __( 'Recurring Profile Items', WPC_CLIENT_TEXT_DOMAIN ) ;
                        break;
                    case 'request_estimate_edit':
                        $title_box = __( 'Estimate Request Items', WPC_CLIENT_TEXT_DOMAIN ) ;
                        break;
                }
            }

            add_meta_box( 'wpc_invoice_inv_items', $title_box, array( &$this, 'inv_items' ), 'wp-client_page_wpclients_invoicing', 'normal', 'high' );

            if ( isset( $_GET['tab'] ) && 'request_estimate_edit' == $_GET['tab'] ) {
                add_meta_box( 'wpc_invoice_notes', __( 'Request Notes', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'request_notes' ), 'wp-client_page_wpclients_invoicing', 'normal', 'high' );
            }

            add_meta_box( 'wpc_invoice_payment_settings', __( 'Payment Settings', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'payment_settings' ), 'wp-client_page_wpclients_invoicing', 'normal', 'high' );

            if ( !( isset( $_GET['tab'] ) && 'request_estimate_edit' == $_GET['tab'] ) ) {
                add_meta_box( 'wpc_invoice_note', __( 'Additional Information', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'note' ), 'wp-client_page_wpclients_invoicing', 'normal', 'high' );
            }

            if ( !empty( $_GET['id'] ) && ( !isset( $_GET['tab'] ) || 'request_estimate_edit' !== $_GET['tab'] ) ) {
                $wpc_inv_notes = get_post_meta( $_GET['id'], 'wpc_inv_notes', true );
            }
            if ( !empty( $wpc_inv_notes ) ) {
                add_meta_box( 'wpc_invoice_request_notes_history', __( 'Request Notes History', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'request_notes' ), 'wp-client_page_wpclients_invoicing', 'normal', 'high', $wpc_inv_notes );
            }

            //accumulating
            add_meta_box( 'wpc_invoice_accumulating_profile_settings', __( 'Profile Settings', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'accumulating_profile_settings' ), 'wp-client_page_accumulating_invoice', 'side', 'high' );
            add_meta_box( 'wpc_invoice_inv_items', __( 'Accumulating Profile Items', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'inv_items' ), 'wp-client_page_accumulating_invoice', 'normal', 'high' );
            add_meta_box( 'wpc_invoice_payment_settings', __( 'Payment Settings', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'payment_settings' ), 'wp-client_page_accumulating_invoice', 'normal', 'high' );
            add_meta_box( 'wpc_invoice_note', __( 'Additional Information', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'note' ), 'wp-client_page_accumulating_invoice', 'normal', 'high' );
            add_meta_box( 'wpc_invoice_assign', __( 'User Information', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'user_info' ), 'wp-client_page_accumulating_invoice', 'side', 'high' );
            add_meta_box( 'wpc_invoice_custom_fields', __( 'Item Fields', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'custom_fields' ), 'wp-client_page_accumulating_invoice', 'side', 'high' );

            if( isset( $_GET['tab'] ) && isset( $_GET['id'] ) && ('repeat_invoice_edit' == $_GET['tab']  || 'accum_invoice_edit' == $_GET['tab'] ) ) {
                global $wpdb;
                $childrens = $wpdb->get_results( "SELECT p.ID as id, p.post_date as date, pm2.meta_value as number
                                                    FROM {$wpdb->posts} p
                                                    INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_parrent_id' AND pm.meta_value = " . (int)$_GET['id'] . " )
                                                    LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_number' )
                                                    ", ARRAY_A ) ;
                if ( count( $childrens ) ) {
                    add_meta_box( 'wpc_invoice_history_accum_inv', __( 'Accumulating Profile History', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'history_accum_inv' ), 'wp-client_page_accumulating_invoice', 'normal', 'high', $childrens );

                }
            }


            //recurring
            add_meta_box( 'wpc_invoice_recurring_profile_settings', __( 'Profile Settings', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'recurring_profile_settings' ), 'wp-client_page_recurring_invoices', 'side', 'high' );
            add_meta_box( 'wpc_invoice_inv_items', __( 'Recurring Profile Items', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'inv_items' ), 'wp-client_page_recurring_invoices', 'normal', 'high' );
            add_meta_box( 'wpc_invoice_payment_settings', __( 'Payment Settings', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'payment_settings' ), 'wp-client_page_recurring_invoices', 'normal', 'high' );
            add_meta_box( 'wpc_invoice_note', __( 'Additional Information', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'note' ), 'wp-client_page_recurring_invoices', 'normal', 'high' );
            add_meta_box( 'wpc_invoice_assign', __( 'User Information', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'user_info' ), 'wp-client_page_recurring_invoices', 'side', 'high' );
            add_meta_box( 'wpc_invoice_custom_fields', __( 'Item Fields', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'custom_fields' ), 'wp-client_page_recurring_invoices', 'side', 'high' );

            if( isset( $childrens ) && count( $childrens ) ) {
                add_meta_box( 'wpc_invoice_history_accum_inv', __( 'Recurring Profile History', WPC_CLIENT_TEXT_DOMAIN ), array( &$this, 'history_accum_inv' ), 'wp-client_page_recurring_invoices', 'normal', 'high', $childrens );
            }
        }


        //show metabox Invoice Custom Fields
        function invoice_cf( $data, $wpc_invoice_cf ) {

            $canEdit = ( $data['option']['can_edit'] ) ? true : false;

            //get data of custom fields
            $data_cf = ( isset( $data['data']['invoice_cf'] ) )
                    ? $data['data']['invoice_cf'] : array();

            $html = '<div id="wpc_inv_cf">';
            foreach( $wpc_invoice_cf['args'] as $slug => $value ) {
                $html .= '<div>';
                if ( !empty( $value['title'] ) ) {
                    $descr = ( !empty( $value['description'] ) ) ? htmlspecialchars( $value['description'] ) : '';
                    $html .= '<p title="' . $descr . '"><b>' .  htmlspecialchars( $value['title'] ) . '</b>';
                    if ( !empty($value['required']) && 1 == $value['required']) {
                        $html .= '&nbsp;<font color="red" title="'
                            . __( 'This field is marked as required.',WPC_CLIENT_TEXT_DOMAIN  ) . '">*</font>';
                    }
                    $html .= '</p>';
                }

                if ( empty( $value['type'] ) || ( in_array( $value['type'],
                        array( 'checkbox', 'radio', 'selectbox', 'multiselectbox' ) )
                        && empty( $value['options'] )) ) {
                    $html .= '</div>';
                    continue;
                }

                $valuation = ( isset( $data_cf[ $slug ] ) ) ? $data_cf[ $slug ] : '';

                if ( !$canEdit
                    || ( isset( $value['field_readonly'] ) && 1 == $value['field_readonly'] && !empty( $valuation ) ) ) {
                    $readonly = ' disabled="disabled"';
                } else {
                    $readonly = '';
                }

                $class = '';
                if ( isset( $value['required'] ) && 1 == $value['required'] ) {
                    $class = ' wpc_inv_cf_required';
                }

                $name = ' name="wpc_data[invoice_cf][' . $slug . ']"';


                //in future use get_html_for_custom_field() method
                switch( $value['type']) {
                    case 'checkbox':
                        foreach ( $value['options'] as $key => $option ) {
                            $checked = ( empty($_GET['id']) && !empty($value['default_option'])
                                    && $value['default_option'] == $key
                                    || $valuation && in_array( $key, $valuation ) )
                                    ? ' checked="checked"' : '';
                            $html .= '<input value="' . $key . '" type="checkbox"'
                                . str_replace( ']"', '][]"', $name ) . $readonly
                                . $checked . '>&nbsp;' . htmlspecialchars( $option ) . '<br>';
                        }
                    break;

                    case 'radio':
                        foreach ( $value['options'] as $key => $option ) {
                            $checked = ( empty($_GET['id']) && !empty($value['default_option'])
                                    && $value['default_option'] == $key
                                    || $key == $valuation ) ? ' checked="checked"' : '';
                            $html .= '<input ' . $name . ' value="' . $key . '" type="radio"'
                                . $readonly . $checked . '>'
                                . '&nbsp;' . htmlspecialchars( $option ) . '<br>';
                        }
                    break;

                    case 'multiselectbox':
                        $new_name = 'new_name_' . $slug;
                        $$new_name = str_replace( ']"', '][]"', $name );
                    case 'selectbox':
                        $new_name = 'new_name_' . $slug;
                        $html .= '<select ' . ( isset( $$new_name ) ? $$new_name : $name )
                            . ' style="min-width: 168px" class="'. $class . '" ' . $readonly
                            . ( 'multiselectbox' == $value['type'] ? ' multiple style=""' : '' ) . ' >' ;
                        foreach ( $value['options'] as $key => $option ) {
                            $selected = ( $valuation && ( is_array($valuation) && in_array( $key, $valuation )
                                || is_string($valuation) && $key == $valuation ))
                                ? ' selected="selected"' : '';
                            $html .= '<option value="' . $key . '"' . $selected . '>' . htmlspecialchars( $option ) . '</option>';
                        }
                            $html .= '</select>';
                    break;

                    case 'textarea' :
                        $html .= '<textarea class="' . $class . '"' . $name . $readonly . ' rows="3">';
                        $html .= htmlspecialchars( $valuation );
                        $html .= '</textarea>';
                    break;

                    case 'datepicker':
                        $class .= ' wpc_cf_datepicker';
                    case 'text':
                        $html .= '<input type="text"' . $name . $readonly
                            . ' class="' . $class . '" value="' . htmlspecialchars( $valuation ) . '">';
                    break;
                }
                $html .= '</div>';
            }
            $html .= '</div>';

            echo $html;
        }


        //show metabox Item Fields
        function custom_fields( $data ) {
            $readonly = ( !$data['option']['can_edit'] ) ? ' disabled' : '';
            $wpc_inv_custom_fields = WPC()->get_settings( 'inv_custom_fields' );

            $html = '<div id="all_custom_fields">';
            $checked = ( !isset( $data['data']['custom_fields'] ) || isset( $data['data']['custom_fields']['description'] ) && 1 == $data['data']['custom_fields']['description'] ) ? ' checked' : '';
            $html .= '<input id="description_display" type="checkbox"' . $checked . $readonly . ' name="wpc_data[custom_fields][description]" value="1">' .  __( 'Description', WPC_CLIENT_TEXT_DOMAIN ) . '<br>';

            foreach( $wpc_inv_custom_fields as $key => $settings ) {
                if( isset( $data['data']['custom_fields'] ) ) {
                    $checked = ( isset( $data['data']['custom_fields'][ $key ] ) && 1 == $data['data']['custom_fields'][ $key ] ) ? ' checked' : '';
                } elseif( isset( $_GET['id'] ) ) {
                    $checked = '';
                } else {
                    $checked = ( $settings['display'] ) ? ' checked' : '';
                }
                $html .= '<input type="checkbox"' . $checked . $readonly . ' name="wpc_data[custom_fields][' . $key . ']" value="1" data-slug="' . $key . '">' . htmlspecialchars( $settings['title'] ) . '<br>';
            }
            $html .= '</div>';
            $more_cur = '<a href="'. get_admin_url().'admin.php?page=wpclients_invoicing&tab=item_custom_fields" target="_blank">' . __( 'Item Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            $html .= '<br><span class="description margin_desc">' . sprintf( __( 'You can add more custom fields for use in Invoices from %s page.', WPC_CLIENT_TEXT_DOMAIN ), $more_cur ) . '</span>';

            echo $html;
            ?>
                <script type="text/javascript">
                    jQuery(document).ready( function() {
                        jQuery( '#all_custom_fields input' ).click( function() {
                            if ( this.id === 'description_display' ) {
                                jQuery( '.descr_display' ).toggleClass( 'cf_hide' );
                            } else {
                                var slug = jQuery( this ).data( 'slug' );
                                jQuery( '.icf_' + slug ).toggleClass( 'cf_hide' );
                            }
                        });

                        jQuery( '#edit_data' ).submit( function() {
                            jQuery( '#all_custom_fields input:not(:checked)' ).each( function() {
                                if ( this.id === 'description_display' ) {
                                    jQuery( '.descr_display' ).remove();
                                } else {
                                    var slug = jQuery( this ).data( 'slug' );
                                    jQuery( '.icf_' + slug ).remove();
                                }
                            });
                        });
                    });
                </script>
            <?php
        }


        function request_notes( $data, $wpc_inv_notes = false ) {
            global $wpc_inv;

            $disable_textarea = true;
            if ( $wpc_inv_notes['args'] ) {
                $notes = $wpc_inv_notes['args'];
                $disable_textarea = false;
            } else {
                $notes = ( isset( $data['data']['notes'] ) ) ? $data['data']['notes'] : array();
            }
            echo '<div class="clear"></div>' . $wpc_inv->get_table_request_notes( $notes, 'admin', $disable_textarea, $data['data']['id'] ) . '<div class="clear"></div>';
        }


        function history_accum_inv( $data, $childrens ) {

            foreach( $childrens['args'] as $ch ) {
                echo '<p>';
                printf( __( 'Invoice %s Created %s', WPC_CLIENT_TEXT_DOMAIN ),
                    '<a href="admin.php?page=wpclients_invoicing&tab=invoice_edit&id=' . $ch['id'] . '" target="_blank">#' . $ch['number'] . '</a>',
                     WPC()->date_format( strtotime( $ch['date'] ), 'date' )
                     );
                echo '</p>' ;
            }
        }


        function history( $data ) {
            global $wpc_payments_core;
            if ( isset( $data['data']['id'] ) ) {
                $orders = get_post_meta( $data['data']['id'], 'wpc_inv_order_id', true );
                ?>
                <div id="wpc_inv_history">
                    <table class="table_history" id="table_history">
                <?php

                if ( is_array( $orders ) && $orders ) {
                    $selected_curr = isset ( $data['data']['currency'] ) ? $data['data']['currency'] : '';
                    $orders = $wpc_payments_core->get_orders( $orders );
                    foreach( $orders as $order ) {
                        $order_data = !empty( $order['data'] ) ? json_decode($order['data'], true) : array();
                        $notes = !empty( $order_data['notes'] ) ? htmlspecialchars($order_data['notes']) : '';
                        ?>
                        <tr>
                            <td class="time_history"><?php echo WPC()->date_format( $order['time_paid'] ) ?></td>
                            <td class="text_history"><?php printf( __( '%s paid', WPC_CLIENT_TEXT_DOMAIN ), $this->get_currency( $order['amount'], true, $selected_curr ) ) ?></td>
                            <td class="text_history"><?php echo $notes ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                    </table>
                </div>
                <?php
            }
        }

        function update_for_r_est( $data ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <script type="text/javascript">


            function not_price(e) {
                if ( e.which === 44 ) {
                    this.value += '.';
                    return false;
                }
                if (!(e.which === 8 || e.which === 46 ||e.which === 0 ||(e.which>47 && e.which<58))) return false;
            }
            var admin_url = '<?php echo get_admin_url(); ?>';

            /*function not_d(e) {
                if (!(e.which==8 ||e.which==0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_due_date(e) {
                if ( !(e.which==8 ||e.which==0 ||(e.which>46 && e.which<58) )) return false;
            }*/


            jQuery(document).ready( function() {

                postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

                jQuery('#wpc_data_currency').change( function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: admin_url + 'admin-ajax.php',
                        data: 'action=inv_change_currency&selected_curr=' + jQuery(this).val(),
                        dataType: "json",
                        success: function( data ){
                            jQuery( '#wpc_data_currency_symbol' ).val( data.symbol );
                            jQuery( '#wpc_data_currency_align' ).val( data.align );
                            jQuery( '.amount' ).each( function() {
                                var f_number = jQuery( this ).html();
                                var number = jQuery( this ).data('number');
                                if( 'left' === data.align )
                                    jQuery( this ).parent().html( data.symbol + '<span class="amount" data-number="' + number + '">' + f_number + '</span>' );
                                else if( 'right' === data.align )
                                    jQuery( this ).parent().html( '<span class="amount" data-number="' + number + '">' + f_number + '</span>' + data.symbol );
                            });
                        }
                     });
                });

              });
            </script>

            <div class="for_buttom">

                <?php
                    if ( !empty( $data['data']['status'] ) && in_array( $data['data']['status'], array( 'new', 'waiting_admin' ) ) ) {
                    ?>

                    <input type="button" data-action="convert_to_inv" class="button wpc_button" value="<?php _e( 'Convert to Inv.', WPC_CLIENT_TEXT_DOMAIN ) ?>" >
                    <input type="button" data-action="convert_to_est" class="button wpc_button" value="<?php _e( 'Convert to Est.', WPC_CLIENT_TEXT_DOMAIN ) ?>" >
                    <?php
                    }
                ?>

                <input type="button" id="update" class="button-primary wpc_button" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" />

                <input type="button" name="data_cancel" id="data_cancel" class="button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />


                <?php

                if ( isset( $_GET['id'] ) ) {
                   if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_estimates' ) ) {
                       echo '<span class="perm_del">';
                       echo '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Estimate Request?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&tab=request_estimates&action=delete&id=' . $data['data']['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_request_estimate_delete' . $data['data']['id'] . get_current_user_id() ) .'">' . __( 'Delete&nbsp;Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                       echo '</span><br />';
                   }
                }
                ?>

               </div>
            <?php

        }


        //show metabox
        function publish( $data ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <script type="text/javascript">

            var admin_url = '<?php echo get_admin_url(); ?>';

            function not_d(e) {
                if (!(e.which===8 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_price(e) {
                if ( e.which === 44 ) {
                    this.value += '.';
                    return false;
                }
                if (!(e.which===8 || e.which===46 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_due_date(e) {
                if ( !(e.which===8 ||e.which===0 ||(e.which>46 && e.which<58) )) return false;
            }


            jQuery(document).ready( function() {

                jQuery( '#wpc_billing_period' ).keypress( not_d );
                jQuery( '#wpc_billing_cycle' ).keypress( not_d );

                //jQuery( '#wpc_data_inv_number' ).keypress( not_d );
                jQuery( '#wpc_data_due_date, .wpc_cf_datepicker' ).keypress( not_d );

                jQuery( '#wpc_data_late_fee' ).keypress( not_price );
                jQuery( '#wpc_minimum_deposit' ).keypress( not_price );

                jQuery('#wpc_billing_period_select').change( function() {
                    if ( 'month' === jQuery('#wpc_billing_period_select').val() ) {
                        jQuery('#block_last_day_month').css('display', 'block');
                    } else {
                        if ( jQuery('#wpc_last_day_month').prop("checked") )
                            jQuery('#wpc_last_day_month').click();
                        jQuery('#block_last_day_month').css('display', 'none');
                    }
                });

                /*jQuery('#wpc_last_day_month').change( function () {
                    if( jQuery(this).prop("checked") ) {
                        jQuery('#wpc_data_from_date').attr('disabled', true ) ;
                    }else{
                        jQuery('#wpc_data_from_date').removeAttr( 'disabled' ) ;
                    }
                });*/

                postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

                jQuery('#wpc_data_currency').change( function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: admin_url + 'admin-ajax.php',
                        data: 'action=inv_change_currency&selected_curr=' + jQuery(this).val(),
                        dataType: "json",
                        success: function( data ){
                            jQuery( '#wpc_data_currency_symbol' ).val( data.symbol );
                            jQuery( '#wpc_data_currency_align' ).val( data.align );
                            jQuery( '.amount' ).each( function() {
                                var f_number = jQuery( this ).html();
                                var number = jQuery( this ).data('number');
                                if( 'left' === data.align )
                                    jQuery( this ).parent().html( data.symbol + '<span class="amount" data-number="' + number + '">' + f_number + '</span>' );
                                else if( 'right' === data.align )
                                    jQuery( this ).parent().html( '<span class="amount" data-number="' + number + '">' + f_number + '</span>' + data.symbol );
                            });
                        }
                     });
                });

                jQuery('#wpc_deposit').change( function () {
                    if( jQuery(this).attr("checked") ) {
                        jQuery('#wpc_block_min').css('display', 'inline') ;
                        jQuery('#wpc_deposit').val('true') ;
                        jQuery('#label_rec').css('display', 'none') ;
                    }else{
                        jQuery('#wpc_block_min').css('display', 'none') ;
                        jQuery('#wpc_deposit').val('false') ;
                        jQuery('#label_rec').css('display', 'inline') ;
                    }
                });

                jQuery('#wpc_delete_late_fee').click( function () {
                    var id = jQuery(this).data('id');
                    jQuery( '#load_delete_late_fee' ).addClass( 'wpc_ajax_loading' );
                    jQuery.ajax({
                        type: 'POST',
                        url: admin_url + 'admin-ajax.php',
                        data: 'action=inv_delete_late_fee&inv_id=' + id,
                        dataType: "json",
                        success: function( total ) {
                            if ( undefined !== total ) {
                                jQuery('#late_fee .amount').parent().parent().parent().remove();
                                jQuery('#load_delete_late_fee').trigger( 'click' );
                                jQuery('.wpc_group_late_fee').css( 'display', 'block' );
                                jQuery('#wpc_delete_late_fee').css( 'display', 'none' );
                                jQuery( '#load_delete_late_fee' ).removeClass( 'wpc_ajax_loading' );
                            } else{
                                jQuery( '#load_delete_late_fee' ).removeClass( 'wpc_ajax_loading' );

                            }
                        },
                        error: function( data ) {
                            jQuery( '#load_delete_late_fee' ).removeClass( 'wpc_ajax_loading' );
                        }
                    });
                });

              });
            </script>

            <?php
                $tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : '';
                $est = ( 'estimate_edit' == $tab ) ? true : false;
                $readonly = ( !$data['option']['can_edit'] ) ? ' disabled' : '';


                $val_deposit = 'false';
                $min_deposit = 0 ;
                $checked_deposit = '';
                $display_deposit_settings = 'style = " display: none; "';
                if ( isset( $data['data']['deposit'] ) ) {
                    $checked_deposit = 'checked';
                    $val_deposit = 'true';
                    $display_deposit_settings = '';
                    $min_deposit = ( isset( $data['data']['min_deposit'] ) ) ? $data['data']['min_deposit'] : 0 ;
                }

               ?>

            <div class="misc-pub-section">
                <label for="wpc_deposit" id="label_deposit">
                    <input id="wpc_deposit" type="checkbox" value="<?php echo $val_deposit ?>" name="wpc_data[deposit]" <?php echo $readonly ?> <?php echo $checked_deposit ?> />
                    <?php _e( 'Allow Partial Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>
                <br />
                <label class="margin_desc" id="wpc_block_min" for="wpc_minimum_deposit" <?php echo  $display_deposit_settings ?> >
                    <?php _e( 'Minimum Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <input id="wpc_minimum_deposit" type="text" style="width: 75px" name="wpc_data[min_deposit]" value=<?php echo '"' . $min_deposit . '"' .  $readonly ?> />
                </label>
            </div>


            <div class="misc-pub-section">
                <?php
                $name_page = ( $est ) ? __( 'Estimate Number: ', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Invoice Number: ', WPC_CLIENT_TEXT_DOMAIN );

                if ( isset( $_GET['id'] ) && '' != $_GET['id'] && ( 'invoice_edit' == $_GET['tab'] || 'estimate_edit' == $_GET['tab'] ) ) {
                    echo '<label for="invoice_id">' . $name_page . '</label><span id="invoice_id">' . $data['data']['number'] . '</span>';
                } else {
                    echo '<label for="wpc_data_inv_number">' . $name_page . '</label><input type="text" style="width: 75px" name="wpc_data[inv_number]" id="wpc_data_inv_number" value="' . ( isset( $data['data']['inv_number'] ) ? $data['data']['inv_number'] : '' ) . '" /><br /><p class="description margin_desc">' . __( 'Leave blank for Invoice # to be auto-generated in sequence', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
                } ?>
            </div>

            <div class="misc-pub-section wpc_group_late_fee" <?php echo ( !empty( $data['data']['added_late_fee'] ) ) ? ' style=" display: none;"' : '' ?> >
                <label for="wpc_data_due_date">
                    <?php
                        echo __( 'Due Date', WPC_CLIENT_TEXT_DOMAIN );
                        echo WPC()->admin()->tooltip( __( 'Due Date is required to be set if setting a Late Fee', WPC_CLIENT_TEXT_DOMAIN ) );
                    ?>
                </label>

                <input type="text" style="width: 100px" id="wpc_data_due_date" name="wpc_data[due_date]" value="<?php echo ( isset( $data['data']['due_date'] ) ? $data['data']['due_date'] : '' ) ?>" <?php echo $readonly ?> />

            <?php
            if ( $data['option']['can_edit'] ) { ?>
                <div>
                    <a href="javascript:;" class="wpc_set_due_date" rel="<?php echo date( 'm/d/Y', ( time() + 3600*24*15 ) ) ?>">15&nbsp;</a>
                    |
                    <a href="javascript:;" class="wpc_set_due_date" rel="<?php echo date( 'm/d/Y', ( time() + 3600*24*30 ) ) ?>">30&nbsp;</a>
                    |
                    <a href="javascript:;" class="wpc_set_due_date" rel="<?php echo date( 'm/d/Y', ( time() + 3600*24*45 ) ) ?>">45&nbsp;</a>
                    |
                    <a href="javascript:;" class="wpc_set_due_date" rel="<?php echo date( 'm/d/Y', ( time() + 3600*24*60 ) ) ?>">60&nbsp;</a>
                    |
                    <a href="javascript:;" class="wpc_set_due_date" rel="<?php echo date( 'm/d/Y', ( time() + 3600*24*90 ) ) ?>">90&nbsp;<?php _e( 'Days', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </div>
            <?php
            }
            ?>


            </div>

            <?php if ( !$est ) { ?>
                 <div class="misc-pub-section wpc_group_late_fee" <?php echo ( !empty( $data['data']['added_late_fee'] ) ) ? ' style=" display: none;"' : '' ?>>
                    <label>
                        <?php _e( 'Late Fee', WPC_CLIENT_TEXT_DOMAIN ) ?>

                        <input type="text" style="width: 75px" name="wpc_data[late_fee]" id="wpc_data_late_fee" value="<?php echo ( isset( $data['data']['late_fee'] ) ) ? $data['data']['late_fee'] : '0' ?>" <?php echo $readonly ?> />
                    </label>
                 </div>
            <?php }

            if ( !empty( $data['data']['added_late_fee'] ) && !empty( $data['option']['can_edit'] ) ) {

            ?>
                <div class="misc-pub-section">
                    <a href="javascript:;" id="wpc_delete_late_fee" data-id="<?php echo ( !empty( $data['data']['id'] ) ) ? $data['data']['id'] : '' ?>" >
                        <?php _e( 'Delete Late Fee', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </a>
                    <span id="load_delete_late_fee"></span>
                </div>
            <?php
            }

            $wpc_invoicing  = WPC()->get_settings( 'invoicing' );

            ?>

            <div class="misc-pub-section">
                <label>
                    <?php
                        $checked = '';

                        if ( empty( $_GET['id'] ) && !empty( $wpc_invoicing['vat_set'] ) && 'yes' === $wpc_invoicing['vat_set'] ) {
                            $checked = ' checked="checked"' ;
                        } else {
                            if ( !empty( $data['data']['show_vat'] ) ) {
                                $checked = ' checked="checked"' ;
                            }
                        }
                    ?>
                    <input type="checkbox" name="wpc_data[show_vat]" id="wpc_data_show_vat" value="1" <?php echo $readonly . $checked ?> />
                    <?php _e( 'VAT', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>
             </div>


            <div class="misc-pub-section">
                <label>
                    <?php
                        $checked = '';

                        if ( empty( $_GET['id'] ) && !empty( $wpc_invoicing['send_for_paid'] ) && 'yes' == $wpc_invoicing['send_for_paid'] ) {
                            $checked = ' checked="checked"' ;
                        } else {
                            if ( !empty( $data['data']['send_for_paid'] ) ) {
                                $checked = ' checked="checked"' ;
                            }
                        }

                    ?>
                    <?php if ( !$est ) { ?>
                        <input type="checkbox" name="wpc_data[send_for_paid]" id="wpc_data_send_for_paid" value="1" <?php echo $readonly . $checked ?> />
                        <?php printf( __( 'Send Email for %s After Paid Invoice', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?>
                    <?php } ?>
                </label>
             </div>

             <hr />

                <div class="for_buttom">

                    <?php

                        if( !empty( $data['option']['can_edit'] ) ) {
                    ?>
                        <input type="button" name="save_open" id="save_open" class="button-primary" value="<?php _e( 'Save as Open', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <label><input id="send_email" type="checkbox" name="wpc_data[send_email]" value="1" /><?php _e( 'Send Email', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <?php echo WPC()->admin()->tooltip( sprintf( __( 'Send Email with PDF file to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ) ?>
                    <?php
                        }
                    ?>
                    <div class="wpc_clear"></div>

                    <?php if ( !isset( $_GET['id'] ) || 'draft' == $data['data']['status'] ) { ?>
                       <input type="button" name="save_draft" id="save_draft" class="button" value="<?php _e( 'Save as Draft', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                       <div class="wpc_clear"></div>
                    <?php } ?>

                    <input type="button" style="vertical-align: middle;" name="data_cancel" id="data_cancel" class="button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />

                    <?php
                    if ( isset( $_GET['id'] ) ) {
                        if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_invoices' ) ) {
                            echo '<span class="perm_del">';
                            if ( $est ) {
                                echo '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Estimate?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&tab=estimates&action=delete&id=' . $data['data']['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_estimate_delete' . $data['data']['id'] . get_current_user_id() ) .'">' . __( 'Delete&nbsp;Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                            } else {
                                echo '<a onclick=\'return confirm("' . __( 'Are you sure to delete this Invoice?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclients_invoicing&action=delete&id=' . $data['data']['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_invoice_delete' . $data['data']['id'] . get_current_user_id() ) .'">' . __( 'Delete&nbsp;Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                            }
                            echo '</span><br />';
                       }
                    }
                    ?>

                   <div class="wpc_clear"></div>

                    <?php if ( isset( $_GET['id']) ) { ?>
                        <a href="admin.php?page=wpclients_invoicing&wpc_action=download_pdf&id=<?php echo $data['data']['id'] ?>"><input type="button" name="" id="" class="button" value="<?php _e( 'Download&nbsp;PDF', WPC_CLIENT_TEXT_DOMAIN ) ?>" /></a>
                    <?php } ?>



                   </div>
            <?php
        }


        //show metabox
        function recurring_profile_settings( $data ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <script type="text/javascript">

            var id = '<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : ''; ?>';
            var admin_url = '<?php echo get_admin_url(); ?>';

            function not_d(e) {
                if (!(e.which===8 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_price(e) {
                if ( e.which === 44 ) {
                    this.value += '.';
                    return false;
                }
                if (!(e.which===8 || e.which===46 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_due_date(e) {
                if ( !(e.which===8 ||e.which===0 ||(e.which>46 && e.which<58) )) return false;
            }


            jQuery(document).ready( function() {

                jQuery.ajax({
                    type    : 'POST',
                    dataType: 'json',
                    url     : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data    : 'action=inv_is_active_subscriptions&id=' + id,
                    success : function( data ) {
                        if ( data )
                            jQuery( '#wpc_inv_active_subscriptions' ).css( 'display', 'table-row' );
                        else
                            jQuery( '#wpc_inv_active_subscriptions' ).css( 'display', 'none' );
                    },
                    error   : function( data ){
                        jQuery( '#wpc_inv_active_subscriptions' ).css( 'display', 'none' );
                    }
                });

                //open Delete Permanently
                jQuery( '.delete_permanently').each( function() {
                    jQuery( '.delete_permanently').shutter_box({
                        view_type       : 'lightbox',
                        width           : '400px',
                        type            : 'inline',
                        href            : '#delete_permanently',
                        title           : '<?php _e( 'Delete Recurring Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>',
                    });
                });

                //close Delete Permanently
                jQuery( '#close_delete_permanently' ).click( function() {
                    jQuery( '.delete_permanently').shutter_box('close');
                });

                //save option Delete Permanently
                jQuery( '#check_delete_permanently' ).click( function() {
                    var delete_option = jQuery( 'input[name="delete_option"]:checked' ).val();
                    var wpnonce = jQuery( 'input[name="_wpnonce_for_delete"]' ).val();
                    window.location.href="admin.php?page=wpclients_invoicing&tab=repeat_invoices&action=delete&_wpnonce=" + wpnonce + "&delete_option=" + delete_option + "&id=" + id;
                });

                jQuery( '#wpc_billing_period' ).keypress( not_d );
                jQuery( '#wpc_billing_cycle' ).keypress( not_d );

                jQuery( '#wpc_data_inv_number' ).keypress( not_d );
                jQuery( '#wpc_data_due_date' ).keypress( not_d );

                jQuery( '#wpc_data_late_fee' ).keypress( not_price );
                jQuery( '#wpc_minimum_deposit' ).keypress( not_price );

                jQuery('#wpc_billing_period_select').change( function() {
                    if ( 'month' === jQuery('#wpc_billing_period_select').val() ) {
                        jQuery('#block_last_day_month').css('display', 'block');
                    } else {
                        if ( jQuery('#wpc_last_day_month').prop("checked") )
                            jQuery('#wpc_last_day_month').click();
                        jQuery('#block_last_day_month').css('display', 'none');
                    }
                });

                /*jQuery('#wpc_last_day_month').change( function () {
                    if( jQuery(this).prop("checked") ) {
                        jQuery('#wpc_data_from_date').attr('disabled', true ) ;
                    }else{
                        jQuery('#wpc_data_from_date').removeAttr( 'disabled' ) ;
                    }
                });*/

                postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

                jQuery('#wpc_data_currency').change( function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: admin_url + 'admin-ajax.php',
                        data: 'action=inv_change_currency&selected_curr=' + jQuery(this).val(),
                        dataType: "json",
                        success: function( data ){
                            jQuery( '#wpc_data_currency_symbol' ).val( data.symbol );
                            jQuery( '#wpc_data_currency_align' ).val( data.align );
                            jQuery( '.amount' ).each( function() {
                                var f_number = jQuery( this ).html();
                                var number = jQuery( this ).data('number');
                                if( 'left' === data.align )
                                    jQuery( this ).parent().html( data.symbol + '<span class="amount" data-number="' + number + '">' + f_number + '</span>' );
                                else if( 'right' === data.align )
                                    jQuery( this ).parent().html( '<span class="amount" data-number="' + number + '">' + f_number + '</span>' + data.symbol );
                            });
                        }
                     });
                });

                jQuery('#wpc_deposit').change( function () {
                    if( jQuery(this).attr("checked") ) {
                        jQuery('#wpc_block_min').css('display', 'inline') ;
                        jQuery('#wpc_deposit').val('true') ;
                        jQuery('#label_rec').css('display', 'none') ;
                    }else{
                        jQuery('#wpc_block_min').css('display', 'none') ;
                        jQuery('#wpc_deposit').val('false') ;
                        jQuery('#label_rec').css('display', 'inline') ;
                    }
                });


                jQuery( "body" ).on( 'change', ".wpc_recurring_type", function( e ) {

                    var type = jQuery( 'input[name="wpc_data[recurring_type]"]:checked' ).val();
                    switch ( type ) {
                        case 'invoice_draft':
                            jQuery('.wpc_inv_auto, .wpc_inv_manually_only').addClass('wpc_inv_hide');
                            jQuery('.wpc_inv_manually').removeClass('wpc_inv_hide');
                        break;

                        case 'invoice_open':
                            jQuery('.wpc_inv_auto').addClass('wpc_inv_hide');
                            jQuery('.wpc_inv_manually_only, .wpc_inv_manually').removeClass('wpc_inv_hide');
                        break;

                        case 'auto_charge':
                            jQuery('.wpc_inv_manually, .wpc_inv_manually_only').addClass('wpc_inv_hide');
                            jQuery('.wpc_inv_auto').removeClass('wpc_inv_hide');
                        break;
                    }
                });



              });
            </script>

            <?php
                $readonly = ( !$data['option']['can_edit'] ) ? ' disabled' : '';

                $billing_every = ( isset( $data['data']['billing_every'] ) ) ? $data['data']['billing_every'] : '1' ;

                $billing_period = ( isset( $data['data']['billing_period'] ) ) ? $data['data']['billing_period'] : 'days' ;

                if ( isset( $data['data']['billing_cycle'] ) ) {
                    $billing_cycle = $data['data']['billing_cycle'] ;
                } else if ( isset( $_GET['id'] ) ) {
                    $billing_cycle = '' ;
                } else {
                    $billing_cycle = '1' ;
                }

                $last_day_month = false ;
                //$display_start_from = true ;
                $val_last_day_month = '';
                if ( 'month' == $billing_period ) {
                    $last_day_month = true ;
                    if ( isset( $data['data']['last_day_month'] ) ) {
                        //$display_start_from = false ;
                        $val_last_day_month = $data['data']['last_day_month'] ;
                    }
                }
                $val_deposit = 'false';
                $min_deposit = 0 ;
                $checked_deposit = '';
                $display_deposit_settings = 'style = " display: none; "';
                if ( isset( $data['data']['deposit'] ) ) {
                    $checked_deposit = 'checked';
                    $val_deposit = 'true';
                    $display_deposit_settings = '';
                    $min_deposit = ( isset( $data['data']['min_deposit'] ) ) ? $data['data']['min_deposit'] : 0 ;
                }



                $display_auto_charge = false;

                $from_date = ( isset( $data['data']['from_date'] ) ) ? $data['data']['from_date'] : '' ;

                $recurring_type = isset( $data['data']['recurring_type'] ) ? $data['data']['recurring_type'] : '';
                if( 'auto_charge' == $recurring_type ) {
                    $display_auto_charge = true ;
                }

                $display_manually_recurring = !$display_auto_charge ;

                if  ( '' == $readonly && isset( $data['data']['count_created'] ) && 0 < $data['data']['count_created'] ) {
                    $radio_readonly = ' disabled';
                } else {
                    $radio_readonly = $readonly;
                }
                ?>

            <div class="misc-pub-section">
                <label for="radio_invoice_draft">
                    <?php if ( isset( $data['data']['status'] ) && !in_array( $data['data']['status'], array('draft', 'pending')) && isset( $data['data']['recurring_type'] ) && 'auto_charge' == $data['data']['recurring_type']) { ?>
                        <input type="radio" disabled >
                        <span class="description"><?php _e( 'Create Invoices as Draft', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                    <?php } else { ?>
                        <input type="radio" class="wpc_recurring_type" id="radio_invoice_draft" value="invoice_draft" name="wpc_data[recurring_type]" <?php echo ( isset( $data['data']['recurring_type'] ) && 'invoice_draft' == $data['data']['recurring_type'] ) ? 'checked' : '' ?> >
                        <b><?php _e( 'Create Invoices as Draft', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
                    <?php } ?>
                    <?php echo WPC()->admin()->tooltip( sprintf( __( 'Invoices are saved as drafts. You can review and send them to %s for payment', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) ?>
                </label>

                <br />

                <label for="radio_invoice_open">
                    <?php if ( isset( $data['data']['status'] ) && !in_array( $data['data']['status'], array('draft', 'pending')) && isset( $data['data']['recurring_type'] ) && 'auto_charge' == $data['data']['recurring_type']) { ?>
                        <input type="radio" disabled >
                        <span class="description"><?php _e( 'Create Invoices as Open', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                    <?php } else { ?>
                        <input type="radio" class="wpc_recurring_type" id="radio_invoice_open" value="invoice_open" name="wpc_data[recurring_type]" <?php echo ( !isset( $_GET['id'] ) || isset( $data['data']['recurring_type'] ) && 'invoice_open' == $data['data']['recurring_type'] ) ? 'checked' : '' ?>>
                        <b><?php _e( 'Create Invoices as Open', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
                    <?php } ?>
                    <?php echo WPC()->admin()->tooltip( sprintf( __( 'Invoices are saved as open. So %s will see it in their Portals and pay. Also you can set option for Send Invoices by Email.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) ?>
                </label>

                <br />

                <label for="radio_auto_charge">
                    <?php if ( isset( $data['data']['status'] ) && !in_array( $data['data']['status'], array('draft', 'pending')) && isset( $data['data']['recurring_type'] ) && 'auto_charge' != $data['data']['recurring_type']) { ?>
                        <input type="radio" disabled >
                        <span class="description"><?php _e( 'Auto-Charge via selected gateway', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                    <?php } else { ?>
                        <input type="radio" class="wpc_recurring_type" id="radio_auto_charge" value="auto_charge" name="wpc_data[recurring_type]" <?php echo ( isset( $data['data']['recurring_type'] ) && 'auto_charge' == $data['data']['recurring_type'] ) ? 'checked' : '' ?>>
                        <b><?php _e( 'Auto-Charge via selected gateway', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
                    <?php } ?>
                    <?php echo WPC()->admin()->tooltip( sprintf( __( 'When invoices are generated, they are automatically sent to your %s via email with a PDF of the invoice attached. The invoice is also automatically charged according to the settings you have configured in the profile.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) ?>
                </label>

                <br />
            </div>

            <hr />

            <div id="wpc_block_rec">
                <div class="misc-pub-section">
                    <?php
                        echo '<span class="wpc_inv_auto' . ( $display_auto_charge ? '' : ' wpc_inv_hide' ) . '"><b>'
                                . __( 'Subscriptions Settings', WPC_CLIENT_TEXT_DOMAIN )
                            . '</b></span><br><br>';
                    ?>
                    <label for="wpc_billing_period">
                        <?php
                            echo '<span class="wpc_inv_auto' . ( $display_auto_charge ? '' : ' wpc_inv_hide' ) . '">'
                                . __( 'Bill Every', WPC_CLIENT_TEXT_DOMAIN )
                                . '</span>';
                            echo '<span class="wpc_inv_manually' . ( $display_manually_recurring ? '' : ' wpc_inv_hide' ) . '">'
                                . __( 'Create Invoice Every', WPC_CLIENT_TEXT_DOMAIN )
                                . '</span>';
                        ?>
                        <br />
                        <input id="wpc_billing_period" type="text" class="margin_desc" style="width: 75px; height:28px;" name="wpc_data[billing_every]" value="<?php echo $billing_every ?>" <?php echo $readonly ?> />
                    </label>
                    <label for="wpc_billing_period_select">
                        <select id="wpc_billing_period_select" name="wpc_data[billing_period]" style="vertical-align: bottom;" <?php echo $readonly ?> >
                            <?php
                                $array_select = array('day', 'week', 'month', 'year');
                                foreach( $array_select as $value ) {
                                    echo '<option value="' . $value . '"' . (( $value == $billing_period ) ? 'selected="selected"' : '' ) . ' >' . ucfirst( $value ) . '(s)</option>';
                                }
                            ?>
                        </select>
                    </label>
                </div>

                <div id="block_last_day_month" class="misc-pub-section wpc_inv_manually <?php echo $display_manually_recurring ? '' : ' wpc_inv_hide' ?>" <?php echo ( $last_day_month ) ? '' : 'style="display: none;"'?> >
                    <label for="wpc_last_day_month">
                        <input id="wpc_last_day_month" type="checkbox" value="1" <?php echo ( $val_last_day_month ) ? 'checked' : '' ?> name="wpc_data[last_day_month]" <?php echo $readonly ?> />
                        <?php _e( 'Last Day of Month', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                    <br />

                </div>

                <div class="misc-pub-section">
                    <?php
                        if ( isset( $data['data']['count_created'] ) ) {
                            $text_start = __( 'Next Creation Date', WPC_CLIENT_TEXT_DOMAIN ) ;
                        } else {
                            $text_start = __( 'First Creation Date', WPC_CLIENT_TEXT_DOMAIN ) ;
                        }
                    ?>
                    <label for="wpc_data_from_date"><?php echo $text_start ?></label>
                    <input type="text" style="width: 100px" id="wpc_data_from_date" name="wpc_data[from_date]" value="<?php echo $from_date ?>" <?php echo $readonly ?> />
                </div>

                <div class="misc-pub-section">
                    <label for="wpc_billing_cycle">
                        <?php _e( 'Billing Cycles', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        <input id="wpc_billing_cycle" type="text" style="width: 75px" name="wpc_data[billing_cycle]" value=<?php echo '"' . $billing_cycle . '"' .  $readonly ?> />
                        <?php echo WPC()->admin()->tooltip( __( 'If left blank, invoice will repeat indefinitely.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>
                    </label>
                </div>

                <div class="misc-pub-section wpc_inv_manually <?php echo $display_manually_recurring ? '' : ' wpc_inv_hide' ?>">
                    <label for="wpc_deposit" id="label_deposit">
                        <input id="wpc_deposit" type="checkbox" value="1" name="wpc_data[deposit]" <?php echo $readonly ?> <?php echo $checked_deposit ?> />
                        <?php _e( 'Allow Partial Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                    <br />
                    <label class="margin_desc" id="wpc_block_min" for="wpc_minimum_deposit" <?php echo  $display_deposit_settings ?> >
                        <?php _e( 'Minimum Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        <input id="wpc_minimum_deposit" type="text" style="width: 75px" name="wpc_data[min_deposit]" value=<?php echo '"' . $min_deposit . '"' .  $readonly ?> />
                    </label>
                </div>
            </div>

            <div class="misc-pub-section wpc_inv_manually_only <?php echo ( $display_manually_recurring && ( !isset( $data['data']['recurring_type'] ) || 'invoice_draft' != $data['data']['recurring_type'] )) ? '' : ' wpc_inv_hide' ?>">
               <label>
                   <input type="checkbox" name="wpc_data[send_email_on_creation]" <?php echo ( isset( $data['data']['send_email_on_creation'] ) ) ? 'checked' : '' ?> value="1" <?php echo $readonly ?> />
                   <?php _e( 'Send Email/PDF when generated', WPC_CLIENT_TEXT_DOMAIN ) ?> <?php echo WPC()->admin()->tooltip( sprintf( __( 'When invoices are generated, they are automatically sent to your clients via email with a PDF of the invoice attached. %s must pay manually either by logging into their Portal, or by some other traditional method.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) ?>
               </label>
            </div>

            <hr />

            <div class="misc-pub-section">
                <?php echo '<label for="wpc_data_inv_number">Invoice Number</label><input type="text" style="width: 75px" name="wpc_data[inv_number]" id="wpc_data_inv_number" value="' . ( isset( $data['data']['inv_number'] ) ? $data['data']['inv_number'] : '' ) . '" /><br /><p class="description margin_desc">' . __( 'Leave blank for Invoice # to be auto-generated in sequence', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' ?>
            </div>

            <div class="misc-pub-section">
            <label for="wpc_data_due_date">
                <?php
                    echo __( 'Due Date', WPC_CLIENT_TEXT_DOMAIN );
                    echo WPC()->admin()->tooltip( __( 'Due Date is required to be set if setting a Late Fee', WPC_CLIENT_TEXT_DOMAIN ) );
                ?>
            </label>

            <input type="number" style="width: 100px" id="wpc_data_due_date" name="wpc_data[due_date_number]" value="<?php echo ( isset( $data['data']['due_date_number'] ) ? $data['data']['due_date_number'] : '' ) ?>" <?php echo $readonly ?> />
            <?php
                _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ;
                echo '<br /><p class="description margin_desc">' . __( 'number of days after date of invoice creation"', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
            ?>

             </div>

             <div class="misc-pub-section">
                <label>
                    <?php _e( 'Late Fee', WPC_CLIENT_TEXT_DOMAIN ) ?>

                    <input type="text" style="width: 75px" name="wpc_data[late_fee]" id="wpc_data_late_fee" value="<?php echo ( isset( $data['data']['late_fee'] ) ) ? $data['data']['late_fee'] : '0' ?>" <?php echo $readonly ?> />
                </label>
             </div>

             <hr />

                <div class="for_buttom">


                    <?php if ( $data['option']['can_edit'] ) { ?>
                        <input type="button" name="save_data_send" id="save_data_send" class="button-primary" value="<?php _e( 'Save Profile as Active', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <?php } ?>

                    <div class="wpc_clear"></div>

                    <?php if ( !isset( $_GET['id'] ) || 'draft' == $data['data']['status'] || 'stopped' == $data['data']['status'] ) { ?>
                        <input type="button" name="save_data" id="save_data" class="button" value="<?php _e( 'Save Profile as Draft', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <?php } ?>

                        <input type="button" style="vertical-align: middle;" name="data_cancel" id="data_cancel" class="button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />

                    <?php if ( $data['option']['can_edit'] && isset( $_GET['id'] ) && 'active' == $data['data']['status'] ) { ?>
                        <input type="button" name="save_data_stop" id="save_data_stop" class="button" value="<?php _e( 'Stop Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <?php } ?>

                   <?php if ( isset( $_GET['id'] ) ) {
                       if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_repeat_invoices' ) ) {
                           echo '<span class="perm_del">';
                           echo '<a href="#delete_permanently" data-id="' . $_GET['id']  . '" class="delete_permanently">' . __( 'Delete&nbsp;Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                           echo '</span><br />';
                           ?>
                            <div class="wpc_delete_permanently" id="delete_permanently" style="display: none;">
                                <input type="hidden" name="_wpnonce_for_delete" value="<?php echo wp_create_nonce( 'wpc_repeat_invoice_delete' . $_GET['id'] . get_current_user_id() ) ?>" />
                                <table>
                                    <tr>
                                        <td>
                                            <?php _e( 'What should be done with created Invoices by this Recurring Profile?', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                            <ul>
                                                <li><label>
                                                    <input id="delete_option0" type="radio" value="delete" name="delete_option" checked="checked">
                                                    <?php
                                                         _e( 'Delete all Invoices', WPC_CLIENT_TEXT_DOMAIN );
                                                     ?>
                                                </label></li>
                                                <li><label>
                                                    <input id="delete_option1" type="radio" value="save" name="delete_option">
                                                    <?php
                                                         _e( 'Save Invoices', WPC_CLIENT_TEXT_DOMAIN );
                                                     ?>
                                                </label></li>
                                            </ul>
                                            <br />
                                            <br />
                                        </td>
                                    </tr>
                                        <tr id="wpc_inv_active_subscriptions" style="display:none;">
                                        <td>
                                            <?php
                                                _e( 'Attention: this Recurring Profile has not expired subscriptions. These subscriptions will be closed.', WPC_CLIENT_TEXT_DOMAIN ); ?>
                                        </td>
                                    </tr>
                                </table>
                                <br />
                                <div style="clear: both; text-align: center;">
                                    <input type="button" class='button-primary' id="check_delete_permanently" value="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                    <input type="button" class='button' id="close_delete_permanently" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                </div>
                            </div>
                           <?php
                       }
                   }
                   ?>

                   </div>


            <?php
        }


        //show metabox
        function accumulating_profile_settings( $data ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <script type="text/javascript">

            var admin_url = '<?php echo get_admin_url(); ?>';

            function not_d(e) {
                if (!(e.which===8 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_price(e) {
                if ( e.which === 44 ) {
                    this.value += '.';
                    return false;
                }
                if (!(e.which===8 || e.which===46 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
            }

            function not_due_date(e) {
                if ( !(e.which===8 ||e.which===0 ||(e.which>46 && e.which<58) )) return false;
            }


            jQuery(document).ready( function() {

                //open Delete Permanently
                jQuery( '.delete_permanently').each( function() {
                    jQuery( '.delete_permanently').shutter_box({
                        view_type       : 'lightbox',
                        width           : '400px',
                        type            : 'inline',
                        href            : '#delete_permanently',
                        title           : '<?php _e( 'Delete Accumulating Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>',
                    });
                });

                //close Delete Permanently
                jQuery( '#close_delete_permanently' ).click( function() {
                    jQuery( '.delete_permanently').shutter_box('close');
                });

                //save option Delete Permanently
                jQuery( '#check_delete_permanently' ).click( function() {
                    var delete_option = jQuery( 'input[name="delete_option"]:checked' ).val();
                    var wpnonce = jQuery( 'input[name="_wpnonce_for_delete"]' ).val();
                    var id = '<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : ''; ?>';
                    window.location.href="admin.php?page=wpclients_invoicing&tab=accum_invoices&action=delete&_wpnonce=" + wpnonce + "&delete_option=" + delete_option + "&id=" + id;
                });

                jQuery( '#wpc_billing_period' ).keypress( not_d );
                jQuery( '#wpc_billing_cycle' ).keypress( not_d );

                jQuery( '#wpc_data_inv_number' ).keypress( not_d );
                jQuery( '#wpc_data_due_date' ).keypress( not_d );

                jQuery( '#wpc_data_late_fee' ).keypress( not_price );
                jQuery( '#wpc_minimum_deposit' ).keypress( not_price );

                jQuery('#wpc_billing_period_select').change( function() {
                    if ( 'month' === jQuery('#wpc_billing_period_select').val() ) {
                        jQuery('#block_last_day_month').css('display', 'block');
                    } else {
                        if ( jQuery('#wpc_last_day_month').prop("checked") )
                            jQuery('#wpc_last_day_month').click();
                        jQuery('#block_last_day_month').css('display', 'none');
                    }
                });

                postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

                jQuery('#wpc_data_currency').change( function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: admin_url + 'admin-ajax.php',
                        data: 'action=inv_change_currency&selected_curr=' + jQuery(this).val(),
                        dataType: "json",
                        success: function( data ){
                            jQuery( '#wpc_data_currency_symbol' ).val( data.symbol );
                            jQuery( '#wpc_data_currency_align' ).val( data.align );
                            jQuery( '.amount' ).each( function() {
                                var f_number = jQuery( this ).html();
                                var number = jQuery( this ).data( 'number' );
                                if( 'left' === data.align )
                                    jQuery( this ).parent().html( data.symbol + '<span class="amount" data-number="' + number + '">' + f_number + '</span>' );
                                else if( 'right' === data.align )
                                    jQuery( this ).parent().html( '<span class="amount" data-number="' + number + '">' + f_number + '</span>' + data.symbol );
                            });
                        }
                     });
                });

                jQuery('#wpc_deposit').change( function () {
                    if( jQuery(this).attr("checked") ) {
                        jQuery('#wpc_block_min').css('display', 'inline') ;
                        jQuery('#wpc_deposit').val('true') ;
                        jQuery('#label_rec').css('display', 'none') ;
                    }else{
                        jQuery('#wpc_block_min').css('display', 'none') ;
                        jQuery('#wpc_deposit').val('false') ;
                        jQuery('#label_rec').css('display', 'inline') ;
                    }
                });

              });
            </script>

            <?php
                $readonly = ( !$data['option']['can_edit'] ) ? ' disabled' : '';
                $billing_every = ( isset( $data['data']['billing_every'] ) ) ? $data['data']['billing_every'] : '1' ;
                $billing_period = ( isset( $data['data']['billing_period'] ) ) ? $data['data']['billing_period'] : 'days' ;

                $last_day_month = false ;
                //$display_start_from = true ;
                $val_last_day_month = '';
                if ( 'month' == $billing_period ) {
                    $last_day_month = true ;
                    if ( isset( $data['data']['last_day_month'] ) ) {
                        //$display_start_from = false ;
                        $val_last_day_month = $data['data']['last_day_month'] ;
                    }
                }
                $val_deposit = 'false';
                $min_deposit = 0 ;
                $checked_deposit = '';
                $display_deposit_settings = 'style = " display: none; "';
                if ( isset( $data['data']['deposit'] ) ) {
                    $checked_deposit = 'checked';
                    $val_deposit = 'true';
                    $display_deposit_settings = '';
                    $min_deposit = ( isset( $data['data']['min_deposit'] ) ) ? $data['data']['min_deposit'] : 0 ;
                }

            ?>

            <div class="misc-pub-section">
                <label for="wpc_deposit" id="label_deposit">
                    <input id="wpc_deposit" type="checkbox" value="1" name="wpc_data[deposit]" <?php echo $readonly ?> <?php echo $checked_deposit ?> />
                    <?php _e( 'Allow Partial Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>
                <br />
                <label class="margin_desc" id="wpc_block_min" for="wpc_minimum_deposit" <?php echo  $display_deposit_settings ?> >
                    <?php _e( 'Minimum Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <input id="wpc_minimum_deposit" type="text" style="width: 75px" name="wpc_data[min_deposit]" value=<?php echo '"' . $min_deposit . '"' .  $readonly ?> />
                </label>
            </div>

            <div class="misc-pub-section">
                <label for="radio_invoice_draft">
                        <input type="radio" class="wpc_accum_type" id="radio_invoice_draft" value="invoice_draft" name="wpc_data[accum_type]" <?php echo ( isset( $data['data']['accum_type'] ) && 'invoice_draft' == $data['data']['accum_type'] ) ? 'checked' : '' ?> <?php echo $readonly ?> >
                        <b><?php _e( 'Create Invoices as Draft', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
                    <?php echo WPC()->admin()->tooltip( sprintf( __( 'Invoices are saved as drafts. You can review and send them to %s for payment', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) ?>
                </label>

                <br />

                <label for="radio_invoice_open">
                    <input type="radio" class="wpc_accum_type" id="radio_invoice_open" value="invoice_open" name="wpc_data[accum_type]" <?php echo ( !isset( $_GET['id'] ) || isset( $data['data']['accum_type'] ) && 'invoice_open' == $data['data']['accum_type'] ) ? 'checked' : '' ?> <?php echo $readonly ?> >
                    <b><?php _e( 'Create Invoices as Open', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
                    <?php echo WPC()->admin()->tooltip( sprintf( __( 'Invoices are saved as open. So %s will see it in their Portals and pay. Also you can set option for Send Invoices by Email.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ) ?>
                </label>

                <br />

                <label for="radio_invoice_send" >
                    <input type="radio" class="wpc_accum_type" id="radio_invoice_send" value="invoice_send" name="wpc_data[accum_type]" <?php echo ( isset( $data['data']['accum_type'] ) && 'invoice_send' == $data['data']['accum_type'] ) ? 'checked' : '' ?> <?php echo $readonly ?> >
                    <b><?php _e( 'Create Invoices as Live', WPC_CLIENT_TEXT_DOMAIN ) ?></b>
                    <?php echo WPC()->admin()->tooltip( sprintf( __( 'When invoices are generated %s will receive email with PDF file.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ) ?>
                </label>

                <br />
            </div>

            <hr />

            <div class="misc-pub-section">
                <label for="wpc_billing_period">
                    <?php _e( 'Create Invoice Every', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <br />
                    <input id="wpc_billing_period" type="text" class="margin_desc" style="width: 75px; height:28px;" name="wpc_data[billing_every]" value="<?php echo $billing_every ?>" <?php echo $readonly ?> />
                </label>
                <label for="wpc_billing_period_select">
                    <select id="wpc_billing_period_select" name="wpc_data[billing_period]" style="vertical-align: bottom;" <?php echo $readonly ?> >
                        <?php
                            $array_select = array('day', 'week', 'month', 'year');
                            foreach( $array_select as $value ) {
                                echo '<option value="' . $value . '"' . (( $value == $billing_period ) ? 'selected="selected"' : '' ) . ' >' . ucfirst( $value ) . '(s)</option>';
                            }
                        ?>
                    </select>
                </label>
            </div>


            <div id="block_last_day_month" class="misc-pub-section" <?php echo ( $last_day_month ) ? '' : 'style="display: none;"'?> >
                <label for="wpc_last_day_month">
                    <input id="wpc_last_day_month" type="checkbox" value="1" <?php echo ( $val_last_day_month ) ? 'checked' : '' ?> name="wpc_data[last_day_month]" <?php echo $readonly ?> />
                    <?php _e( 'Last Day of Month', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>
            <br />
            </div>


            <div class="misc-pub-section">
                <?php
                    if ( isset( $data['data']['count_created'] ) ) {
                        $text_start = __( 'Next Creation Date', WPC_CLIENT_TEXT_DOMAIN ) ;
                    } else {
                        $text_start = __( 'First Creation Date', WPC_CLIENT_TEXT_DOMAIN ) ;
                    }


                ?>
                <label for="wpc_data_from_date"><?php echo $text_start ?></label>
                <input type="text" style="width: 100px" id="wpc_data_from_date" name="wpc_data[from_date]" value="<?php echo ( isset( $data['data']['from_date'] ) ? $data['data']['from_date'] : '' ) ?>" <?php echo $readonly ?> <?php //echo ( $display_start_from ) ? '' : 'disabled'?>  />
            </div>


            <div class="misc-pub-section">
                <?php echo '<label for="wpc_data_inv_number">Invoice Number</label><input type="text" style="width: 75px" name="wpc_data[inv_number]" id="wpc_data_inv_number" value="' . ( isset( $data['data']['inv_number'] ) ? $data['data']['inv_number'] : '' ) . '" /><br /><p class="description margin_desc">' . __( 'Leave blank for Invoice # to be auto-generated in sequence', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' ?>
            </div>

            <div class="misc-pub-section">
                <label for="wpc_data_due_date">
                    <?php
                        echo __( 'Due Date', WPC_CLIENT_TEXT_DOMAIN );
                        echo WPC()->admin()->tooltip( __( 'Due Date is required to be set if setting a Late Fee', WPC_CLIENT_TEXT_DOMAIN ) );
                    ?>
                </label>

                <input type="number" style="width: 100px" id="wpc_data_due_date" name="wpc_data[due_date_number]" value="<?php echo ( isset( $data['data']['due_date_number'] ) ? $data['data']['due_date_number'] : '' ) ?>" <?php echo $readonly ?> />
                <?php
                    _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ;
                    echo '<br /><p class="description margin_desc">' . __( 'number of days after date of invoice creation"', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
                ?>

            </div>

            <div class="misc-pub-section">
                <label>
                    <?php _e( 'Late Fee', WPC_CLIENT_TEXT_DOMAIN ) ?>

                    <input type="text" style="width: 75px" name="wpc_data[late_fee]" id="wpc_data_late_fee" value="<?php echo ( isset( $data['data']['late_fee'] ) ) ? $data['data']['late_fee'] : '0' ?>" <?php echo $readonly ?> />
                </label>
            </div>

            <?php
                $not_delete_discounts = ( isset( $data['data']['not_delete_discounts'] ) ) ? 'checked' : '';
                $not_delete_taxes = ( isset( $data['data']['not_delete_taxes'] ) ) ? 'checked' : '';
            ?>
                 <div class="misc-pub-section">
                    <label>
                        <input id="wpc_not_delete_discounts" type="checkbox" value="1" <?php echo $not_delete_discounts ?> name="wpc_data[not_delete_discounts]" <?php echo $readonly ?> />
                        <?php _e( 'Do Not Delete Discounts', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                 </div>
                 <div class="misc-pub-section">
                    <label>
                        <input id="wpc_not_delete_taxes" type="checkbox" value="1" <?php echo $not_delete_taxes?> name="wpc_data[not_delete_taxes]" <?php echo $readonly ?> />
                        <?php _e( 'Do Not Delete Taxes', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                 </div>


            <hr />

                <div class="for_buttom">

                <?php if ( $data['option']['can_edit'] ) { ?>
                    <input type="button" name="save_data_send" id="save_data_send" class="button-primary" value="<?php _e( 'Save Profile as Active', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <?php } ?>
                    <div class="wpc_clear"></div>
                <?php if ( !isset( $_GET['id'] ) || 'draft' == $data['data']['status'] || 'stopped' == $data['data']['status'] ) { ?>
                    <input type="button" name="save_data" id="save_data" class="button" value="<?php _e( 'Save Profile as Draft', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <?php } ?>

                    <input type="button" style="vertical-align: middle;" name="data_cancel" id="data_cancel" class="button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />

                <?php if ( $data['option']['can_edit'] && isset( $_GET['id']) && 'active' == $data['data']['status'] ) { ?>
                    <input type="button" name="save_data_stop" id="save_data_stop" class="button" value="<?php _e( 'Stop Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <?php } ?>

                   <?php
                   if ( isset( $_GET['id'] ) ) {
                       if ( !current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_delete_accum_invoices' ) ) {
                           echo '<span class="perm_del">';
                           echo '<a href="#delete_permanently" data-id="' . $_GET['id']  . '" class="delete_permanently">' . __( 'Delete&nbsp;Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                           echo '</span><br />';
                           ?>

                           <div class="wpc_delete_permanently" id="delete_permanently" style="display: none;">
                                <input type="hidden" name="_wpnonce_for_delete" value="<?php echo wp_create_nonce( 'wpc_accum_invoice_delete' . $_GET['id'] . get_current_user_id() ) ?>" />
                                <table>
                                    <tr>
                                        <td>
                                                <?php _e( 'What should be done with created Invoices by this Accumulating Profile?', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                                <ul>
                                                    <li><label>
                                                        <input id="delete_option0" type="radio" value="delete" name="delete_option" checked="checked">
                                                        <?php
                                                             _e( 'Delete all Invoices', WPC_CLIENT_TEXT_DOMAIN );
                                                         ?>
                                                    </label></li>
                                                    <li><label>
                                                        <input id="delete_option1" type="radio" value="save" name="delete_option">
                                                        <?php
                                                             _e( 'Save Invoices', WPC_CLIENT_TEXT_DOMAIN );
                                                         ?>
                                                    </label></li>
                                                </ul>
                                            <br />
                                            <br />
                                        </td>
                                    </tr>
                                </table>
                                <br />
                                <div style="clear: both; text-align: center;">
                                    <input type="button" class='button-primary' id="check_delete_permanently" value="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                    <input type="button" class='button' id="close_delete_permanently" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                </div>
                           </div>
                           <?php
                       }
                   }
                   ?>

               </div>
            <?php
        }


        //show metabox
        function user_info( $data ) {
            $readonly = ( !$data['option']['can_edit'] ) ? ' disabled' : '';

            $current_page = ( isset( $_GET['page'] ) && isset( $_GET['tab'] ) ) ? $_GET['page'] . $_GET['tab'] : '';
            $auto_charge_recurring = ( isset( $data['data']['recurring_type'] )
                    && 'auto_charge' == $data['data']['recurring_type']
                    && !in_array( $data['data']['status'], array('draft', 'pending') ) );
            if( isset( $_GET['id'] ) && ( 'repeat_invoice_edit' != $_GET['tab'] || $auto_charge_recurring ) ) {
                if ( $auto_charge_recurring ) {
                    echo '<div class="misc-pub-section">';
                        $link_array = array(
                            'title'         => sprintf( __( 'View Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                            'text'          => sprintf( __( 'View Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                        );
                        $input_array = array(
                            'name'  => 'wpc_data[clients_id]',
                            'id'    => 'wpc_clients',
                            'value' => ( !empty( $data['data']['clients_id'] ) ) ? $data['data']['clients_id'] : '',
                        );
                        $additional_array = array(
                            'counter_value' => ( !empty( $data['data']['clients_id'] ) ) ? count( explode( ',', $data['data']['clients_id'] ) ) : 0,
                            'readonly'      => true
                        );
                        WPC()->assigns()->assign_popup('client', $current_page, $link_array, $input_array, $additional_array );
                    echo '</div>';

                    echo '<div class="misc-pub-section">';
                        $link_array = array(
                            'title'         => sprintf( __( 'View Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                            'text'          => sprintf( __( 'View Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                        );
                        $input_array = array(
                            'name'  => 'wpc_data[groups_id]',
                            'id'    => 'wpc_circles',
                            'value' => ( !empty( $data['data']['groups_id'] ) ) ? $data['data']['groups_id'] : '',
                        );
                        $additional_array = array(
                            'counter_value' => ( !empty( $data['data']['groups_id'] ) ) ? count( explode( ',', $data['data']['groups_id'] ) ) : 0,
                            'readonly'      => true
                        );
                        WPC()->assigns()->assign_popup('circle', $current_page, $link_array, $input_array, $additional_array );
                    echo '</div>';

                } else {
                    $user_info = get_userdata( $data['data']['client_id'] );
                    ?>
                    <table class="user_information" cellpadding="0" cellspacing="0">
                        <tr><td></td><td><?php _e( 'Username: ', WPC_CLIENT_TEXT_DOMAIN ) ?></td><td><?php echo ( !empty( $user_info->data->user_login ) ) ? $user_info->data->user_login : ''  ?></td></tr>
                        <tr><td></td><td><?php _e( 'Contact Name: ', WPC_CLIENT_TEXT_DOMAIN ) ?></td><td><?php echo ( !empty( $user_info->data->display_name ) ) ? $user_info->data->display_name : '' ?></td></tr>
                        <tr><td></td><td><?php _e( 'Business Name: ', WPC_CLIENT_TEXT_DOMAIN ) ?></td><td><?php echo get_user_meta( $data['data']['client_id'], 'wpc_cl_business_name', true ) ?></td></tr>
                        <tr><td></td><td><?php _e( 'Email: ', WPC_CLIENT_TEXT_DOMAIN ) ?></td><td><?php echo ( !empty( $user_info->data->user_email ) ) ? $user_info->data->user_email : '' ?></td></tr>
                    <?php
                }
            } else {
                if ( 'accum_invoice_edit' == $_GET['tab'] ) {
                    ?>
                    <div class="misc-pub-section">
                        <?php
                            $link_array = array(
                                'title'         => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                'text'          => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                'data-marks'    => 'radio'
                            );
                            $input_array = array(
                                'name'  => 'wpc_data[clients_id]',
                                'id'    => 'wpc_clients',
                                'value' => '',
                            );
                            WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array );
                        ?>
                    </div>
                <?php
                } else {



                    ?>
                    <div class="misc-pub-section">
                        <?php
                            $link_array = array(
                                'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_data[clients_id]',
                                'id'    => 'wpc_clients',
                                'value' => ( isset( $data['data']['clients_id'] ) ) ? $data['data']['clients_id'] : ''
                            );
                            $additional_array = array(
                                'counter_value' => !empty( $data['data']['clients_id'] ) ? count( explode( ',', $data['data']['clients_id'] ) ) : 0
                            );
                            WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>
                    </div>

                    <div class="misc-pub-section" >
                        <?php
                            $link_array = array(
                                'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_data[groups_id]',
                                'id'    => 'wpc_circles',
                                'value' => ( isset( $data['data']['groups_id'] ) ) ? $data['data']['groups_id'] : ''
                            );
                            $additional_array = array(
                                'counter_value' => ( isset( $data['data']['groups_id'] ) && '' != $data['data']['groups_id'] ) ? count( explode( ',', $data['data']['groups_id'] ) ) : 0
                            );
                            WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>
                    </div>
                <?php }
                ?>
                <table class="user_information" cellpadding="0" cellspacing="0">
                <?php
            }
            if ( isset( $data['data']['cc_emails'] ) ) {
                if ( !is_array( $data['data']['cc_emails'] ) && '' != $data['data']['cc_emails'] ) {
                    $data['data']['cc_emails'] = unserialize( $data['data']['cc_emails'] );
                }
                if ( is_array( $data['data']['cc_emails'] ) && count( $data['data']['cc_emails'] ) ) {
                    foreach( $data['data']['cc_emails'] as $cc_email ) {
                        if ( !empty( $cc_email ) ) {
                    ?>
                        <tr><td class="email_del" width="17"><span></span></td><td><?php _e( 'CC Email: ', WPC_CLIENT_TEXT_DOMAIN ) ?></td><td><input type="text" name="wpc_data[cc_emails][]" value="<?php echo $cc_email ?>" style="width: 130px" /></td></tr>
                    <?php
                        }
                    }
                }
            }

            if ( !$readonly && 'request_estimate_edit' != $_GET['tab'] && !$auto_charge_recurring ) {
                ?>
                <tr><td></td><td colspan="2"><a id="wpc_add_cc_email" href="javascript: void(0);">+<?php _e( 'Add CC Email', WPC_CLIENT_TEXT_DOMAIN ) ?></a></td></tr>
                <?php
            }
            ?>
                </table>

            <script type="text/javascript">

                jQuery(document).ready( function() {

                    jQuery('#wpc_add_cc_email').click( function () {
//                        jQuery( this ).parents( 'tr' ).remove() ;
                        jQuery( '<tr><td class="email_del" width="17"><span></span></td><td><?php _e( 'CC Email: ', WPC_CLIENT_TEXT_DOMAIN ) ?></td><td><input type="text" name="wpc_data[cc_emails][]" value="" style="width: 130px" /></td></tr>' ).insertBefore( jQuery( this ).parents( 'tr' ) );
                    });

                });

                jQuery( '#wpc_invoice_assign' ).on( 'click', '.email_del', function() {
                    jQuery(this).parent().remove();
                });

            </script>

            <?php

        }


        function note( $data ) {
            ?>
            <div>
                <label>
                    <?php _e( 'Terms & Conditions:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>

                <?php

                $terms = isset( $data['data']['terms'] ) ? $data['data']['terms'] : '';

                if  ( !$data['option']['can_edit'] ) {
                   echo '<br /><textarea name="wpc_data[note]" rows="5" id="wpc_data_note" readonly > ' . $terms . '</textarea>';
                } else {
                    wp_editor( $terms,
                        'wpc_data_tc',
                        array(
                            'textarea_name' => 'wpc_data[terms]',
                            'textarea_rows' => 7,
                            'wpautop'       => false,
                            'media_buttons' => false
                        )
                    );
                 }
                ?>

            </div>

            <br />

            <div>
                <label>
                    <?php _e( 'Note to Customer:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>

                <?php

                $note = isset( $data['data']['note'] ) ? $data['data']['note'] : '';

                if  ( !$data['option']['can_edit'] ) {
                   echo '<br /><textarea name="wpc_data[note]" rows="5" id="wpc_data_note" readonly > ' . $note . '</textarea>';
                } else {
                    wp_editor( $note,
                        'wpc_data_note',
                        array(
                            'textarea_name' => 'wpc_data[note]',
                            'textarea_rows' => 7,
                            'wpautop'       => false,
                            'media_buttons' => false
                        )
                    );
                 }
                 ?>

            </div>


       <?php  }

        function payment_settings( $data ) {

            $ver = get_option( 'wp_client_ver' );

            if ( version_compare( $ver, '3.5.0' ) ) {
                $wpc_currency = WPC()->get_settings( 'currency' );
                ?>
                <div>
                    <label for="wpc_data_currency"><?php _e( 'Currency', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                    <select id="wpc_data_currency" name="wpc_data[currency]" <?php echo ( !$data['option']['can_edit'] ) ? 'disabled' : '' ?>>
                        <?php
                            if ( isset( $data['data']['currency'] ) ) {
                                foreach( $wpc_currency as $key => $curr ) {
                                    echo '<option value="' . $key . '" ' . ( ( $key == $data['data']['currency'] ) ? 'selected' : '' ) . '>' . $curr['code'] . ' (' . $curr['symbol'] . ')' . ( ( '' != $curr['title'] ) ? ' - ' . $curr['title'] : ''  ) . '</option>';
                                }
                            } else {
                                foreach( $wpc_currency as $key => $curr ) {
                                    echo '<option value="' . $key . '" ' . ( ( 1 == $curr['default'] ) ? 'selected' : '' ) . '>' . $curr['code'] . ' (' . $curr['symbol'] . ')' . ( ( '' != $curr['title'] ) ? ' - ' . $curr['title'] : ''  ) . '</option>';
                                }
                            }
                        ?>
                    </select>
                    <span class="description margin_desc">
                        <?php
                            $more_cur = '<a href="'. get_admin_url().'admin.php?page=wpclients_settings&tab=general" target="_blank">' . __( 'General Settings', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                            printf( __( 'You can add more currencies for use in Invoices from %s page.', WPC_CLIENT_TEXT_DOMAIN ), $more_cur );
                        ?>
                    </span>
                    <input id="wpc_data_currency_symbol" type="hidden" name="wpc_data[currency_symbol]" value="<?php echo isset( $data['data']['currency_symbol'] ) ? $data['data']['currency_symbol'] : '' ?>" />
                    <input id="wpc_data_currency_align" type="hidden" name="wpc_data[currency_align]" value="<?php echo isset( $data['data']['currency_align'] ) ? $data['data']['currency_align'] : '' ?>" />
                </div>
            <?php }
        }

        //show metabox
        function inv_items( $data ) {

            $wpc_invoicing          = WPC()->get_settings( 'invoicing' );
            $vat = ( !empty($wpc_invoicing['vat']) ) ? $wpc_invoicing['vat'] : 0;
            $rate_capacity          = $this->get_rate_capacity();
            $thousands_separator    = $this->get_thousands_separator();

            $can_edit = ( $data['option']['can_edit'] ) ? true : false; ?>

            <script type="text/javascript">
                jQuery(document).ready( function() {

                    var vat = parseFloat('<?php echo $vat; ?>');
                    var rate_capacity = '<?php echo $rate_capacity; ?>';
                    var thousands_separator = '<?php echo $thousands_separator; ?>';
                    var price_null = '<?php echo number_format( 0, $rate_capacity, '.', $thousands_separator ); ?>';
                    var num_items = jQuery( '.row_del' ).length;
                    num_items = num_items - 1;

                    function addSeparatorsNF( nStr, inD, outD, sep ) {

                        if( sep === '' ) {
                            return nStr;
                        }

                        nStr += '';
                        var dpos = nStr.indexOf( inD );
                        var nStrEnd = '';
                        if (dpos !== -1) {
                            nStrEnd = outD + nStr.substring(dpos + 1, nStr.length);
                            nStr = nStr.substring(0, dpos);
                        }
                        var rgx = /(\d+)(\d{3})/;
                        while (rgx.test(nStr)) {
                            nStr = nStr.replace(rgx, '$1' + sep + '$2');
                        }
                        return nStr + nStrEnd;
                    }

                    function not_price_minus(e) {
                        if ( e.which === 44 ) {
                            this.value += '.';
                            return false;
                        }
                        if (!(e.which===45 || e.which===8 || e.which===46 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
                    }

                    function recalculation() {
                        var number;
                        var total;
                        var type;

                        number = jQuery(this).data('number' );
                        if ( jQuery(this).hasClass('discount_rate') || jQuery(this).hasClass('discount_type') )
                            type = 'disc';
                        else if ( jQuery(this).hasClass('tax_rate') || jQuery(this).hasClass('tax_type') )
                            type = 'tax';
                        else type = 'item';

                        if ( 'item' === type ) {

                            total = parseFloat( jQuery( '#item_quantity' + number ).val(), 10) * parseFloat( jQuery( '#item_price' + number ).val(), 10);

                        } else if ( 'disc' === type ) {

                            var count_total = 0;
                            jQuery( '.item_total' ).each( function() {
                                count_total = count_total + parseFloat( jQuery(this).data('total' ), 10 );
                            });

                            if ( 'amount' === jQuery( '#discount_type' + number ).val() )
                                total = parseFloat( jQuery( '#discount_rate' + number ).val(), 10);
                            else if ( 'percent' === jQuery( '#discount_type' + number ).val() )
                                total = parseFloat( jQuery( '#discount_rate' + number ).val(), 10) * count_total / 100;

                        } else if ( 'tax' === type ) {

                            var count_total = 0;
                            var count_discount = 0;

                            jQuery( '.item_total' ).each( function() {
                                count_total = count_total + parseFloat( jQuery(this).data('total' ), 10 );
                            });

                            jQuery( '.discount_total' ).each( function() {
                                if ( 'percent' === jQuery('#discount_type' + jQuery(this).data('number') ).val() ) {
                                    var discont;
                                    discont = count_total * parseFloat( jQuery( '#discount_rate' + jQuery(this).data('number') ).val(), 10 ) / 100 ;
                                    jQuery(this).html( addSeparatorsNF( discont.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                                }
                                count_discount = count_discount + parseFloat( jQuery(this).data('total' ), 10 );
                            });

                            if ( 'before' === jQuery( '#tax_type' + number ).val() )
                                total = parseFloat( jQuery( '#tax_rate' + number ).val(), 10) * count_total / 100;
                            if ( 'after' === jQuery( '#tax_type' + number ).val() )
                                total = parseFloat( jQuery( '#tax_rate' + number ).val(), 10) * ( count_total - count_discount ) / 100;

                        }

                        total = total.toFixed( rate_capacity );
                        if( isNaN( total ) )
                            total = 0;

                        var html_total = addSeparatorsNF( total, '.', '.', thousands_separator );

                        if ( 'item' !== type ) {
                            jQuery( '#hidden_total' + number ).val( total );
                        }
                        jQuery( '#item_total' + number ).data('total', total );
                        jQuery( '#item_total' + number ).html( html_total );
                        all_total();

                        if ( 'item' === type ) {
                            jQuery( ".tax_rate" ).each(function(){

                                jQuery(this).change();

                            });

                            jQuery( ".discount_rate" ).each(function(){

                                jQuery(this).change();

                            });
                        }
                    }

                    function all_total() {
                        var count_total = 0;
                        var vat_total = 0;
                        var count_discount = 0;
                        var count_tax = 0;
                        var count_late_fee = 0;

                        jQuery( '.item_total' ).each( function() {
                            count_total = count_total + parseFloat( jQuery(this).data('total' ), 10 );
                        });
                        if( isNaN( count_total ) ) count_total = 0;
                        jQuery( '#total_all_items .amount' ).html( addSeparatorsNF( count_total.toFixed( rate_capacity ), '.', '.', thousands_separator ) );

                        jQuery( '.discount_total' ).each( function() {
                            if ( 'percent' === jQuery('#discount_type' + jQuery(this).data('number') ).val() ) {
                                var discont;
                                discont = count_total * parseFloat( jQuery( '#discount_rate' + jQuery(this).data('number') ).val(), 10 ) / 100 ;
                                if( isNaN( discont ) ) discont = 0;
                                jQuery(this).html( addSeparatorsNF( discont.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                            }
                            count_discount = count_discount + parseFloat( jQuery(this).data('total' ), 10 );
                        });

                        if( isNaN( count_discount ) ) count_discount = 0;

                        jQuery( '.tax_total' ).each( function() {

                            var tax;
                            var this_number = jQuery(this).data('number');
                            if ( 'before' === jQuery('#tax_type' + this_number ).val() ) {

                                tax = count_total.toFixed( rate_capacity ) * parseFloat( jQuery( '#tax_rate' + jQuery(this).data('number') ).val(), 10 ) / 100 ;

                            } else if ( 'after' === jQuery('#tax_type' + jQuery(this).data('number') ).val() ) {

                                tax = ( count_total.toFixed( rate_capacity ) - count_discount.toFixed( rate_capacity ) ) * parseFloat( jQuery( '#tax_rate' + jQuery(this).data('number') ).val(), 10 ) / 100 ;

                            }
                            if( isNaN( tax ) ) tax = 0;
                            jQuery(this).html( addSeparatorsNF( tax.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                            jQuery( '#hidden_total' + this_number ).val( tax.toFixed( rate_capacity ) );

                            count_tax = count_tax + parseFloat( jQuery(this).data('total'), 10 );
                        });

                        vat_total = count_total - count_discount + count_late_fee;

                        count_late_fee = parseFloat( jQuery( '#late_fee .amount' ).html(), 10 ) || 0;
                        if( isNaN( count_tax ) ) count_tax = 0;
                        count_total = count_total - count_discount + count_tax + count_late_fee;
                        if( isNaN( count_total ) ) count_total = 0;

                        if ( jQuery('.item_total')[1] || jQuery('.discount_total')[0] || jQuery('.tax_total')[0] ){ // use [1] because one tr always isset
                            jQuery('#added_items').css('display', 'block');
                        } else {
                            jQuery('#added_items').css('display', 'none');
                        }

                        var total_vat = ( vat_total * vat / (100 + vat )).toFixed( rate_capacity );
                        jQuery( '#total_discount .amount' ).html( addSeparatorsNF( count_discount.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                        jQuery( '#total_tax .amount' ).html( addSeparatorsNF( count_tax.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                        jQuery( '#total_all .amount' ).html( addSeparatorsNF( count_total.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                        jQuery( '#total_all .real_amount' ).html( count_total.toFixed( rate_capacity ) );
                        jQuery( '#total_vat .amount' ).html( total_vat );
                        jQuery( '#total_net .amount' ).html( ( vat_total - total_vat ).toFixed( rate_capacity ) );
                        if ( jQuery( '#wpc_data_show_vat' ).is(':checked') ) {
                            jQuery( '.wpc_vat' ).css('display', 'table-row');
                        }
                    }

                    all_total();


                    function add_items( item_name, item_description, item_rate, cf ) {
                        num_items = num_items + 1;
                        var nice_item_rate = addSeparatorsNF( item_rate, '.', '.', thousands_separator );


                        var html = jQuery( '#table_items tbody tr' ).first().clone().wrap('<p>').css( 'display', 'table-row' ).parent().html().replace( /\{num_items\}/g , num_items );

                        jQuery( '#added_items #table_items tbody' ).append( html );

                        jQuery( '#item_total' + num_items  ).data( 'total', item_rate ).val( nice_item_rate );
                        jQuery( '#item_price' + num_items  ).val( item_rate );
                        jQuery( '#item_description' + num_items  ).val( item_description );
                        jQuery( '#item_name' + num_items  ).val( item_name );
                        jQuery( '#item_quantity' + num_items  ).addClass( 'item_qty' );

                        //for custom fields
                        for ( var key in cf ) {
                            jQuery( '#' + key + num_items ).val( cf[key] );
                        }

                        jQuery( '#item_quantity' + num_items ).spinner({
                            min: 1,
                            numberFormat: "n",
                            stop: recalculation
                        });

                        jQuery( '#item_price' + num_items ).trigger( 'change' );

                        jQuery('.all_items').shutter_box('close');
                        //all_total();
                    }


                    function add_taxes( name, description, rate) {
                        num_items = num_items + 1;

                        var html =
                        '<tr valign="top">' +
                            '<td class="row_del"><span></span></td>' +
                            '<td width="160px">' +
                                '<input type="text" size="15" class="tax_name" id="tax_name' + num_items + '" name="wpc_data[taxes][' + num_items + '][name]" value="' + name + '" />' +
                            '</td>' +
                            '<td>' +
                                '<textarea class="description_tax" id="tax_description' + num_items + '" name="wpc_data[taxes][' + num_items + '][description]">' + description + '</textarea>' +
                            '</td>' +
                            '<td width="145px">' +
                                '<select id="tax_type' + num_items  + '" class="tax_type" data-number="' + num_items  + '" name="wpc_data[taxes][' + num_items  + '][type]" >' +
                                    '<option value="before" selected="selected">' + '<?php _e( 'Before Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '</option>' +
                                    '<option value="after">' + '<?php _e( 'After Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '</option>' +
                                '</select>' +
                            '</td>' +
                            '<td width="60px" class="add_procent">' +
                                '<input type="text" class="tax_rate" data-number="' + num_items + '" id="tax_rate' + num_items + '" name="wpc_data[taxes][' + num_items + '][rate]" size="4" value="' + rate + '" />%' +
                            '</td>' +
                            '<td width="50px" align="right">' +
                                '&nbsp;<span class="tax_total" data-total="' + rate + '" data-number="' + num_items + '" id="item_total' + num_items + '"></span>' +
                                '<input type="hidden" name="wpc_data[taxes][' + num_items + '][total]" value="" id="hidden_total' + num_items + '"  />' +
                            '</td>' +
                        '</tr>';

                        jQuery( '#added_items #table_taxes tbody' ).append( html );

                        jQuery('.all_taxes' ).shutter_box('close');
                    }


                    jQuery( '.item_qty' ).each( function() {
                        jQuery(this).spinner({
                            min: 1,
                            numberFormat: "n",
                            stop: recalculation
                        });
                    });


                    <?php if ( $can_edit ) { ?>
                        //show content for add items
                        jQuery( '.all_items' ).shutter_box({
                            view_type       : 'lightbox',
                            width           : '500px',
                            type            : 'ajax',
                            dataType        : 'json',
                            href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                            ajax_data       : "action=inv_all_items",
                            setAjaxResponse : function( data ) {
                                jQuery( '.sb_lightbox_content_title' ).html( data.title );
                                jQuery( '.sb_lightbox_content_body' ).html( data.content );
                            }
                        });

                        //show content for add taxes
                        jQuery( '.all_taxes' ).shutter_box({
                            view_type       : 'lightbox',
                            width           : '500px',
                            type            : 'ajax',
                            dataType        : 'json',
                            href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                            ajax_data       : "action=inv_all_taxes",
                            setAjaxResponse : function( data ) {
                                jQuery( '.sb_lightbox_content_title' ).html( data.title );
                                jQuery( '.sb_lightbox_content_body' ).html( data.content );
                            }
                        });

                        //show content for add new item
                        jQuery('.add_new_item').shutter_box({
                            view_type       : 'lightbox',
                            width           : '550px',
                            type            : 'inline',
                            href            : '#add_new_item',
                            title           : '<?php _e( 'Add New Item', WPC_CLIENT_TEXT_DOMAIN ) ?>',
                            inlineBeforeLoad: function() {
                                jQuery( '#item_name' ).val( '' );
                                jQuery( '#item_description' ).val( '' );
                                jQuery( '#item_rate' ).val( '' );
                                jQuery( '#item_save' ).attr( 'checked', false ).prop( 'checked', false );
                            }
                        });
                    <?php } ?>

                    /*
                        jQuery( '.description_item, .description_tax' ).live( 'focus', function(){
                            jQuery(this).css( 'height', '142px' );
                        });

                        jQuery( '.description_item, .description_tax' ).live( 'blur', function(){
                        jQuery(this).css( 'height', '26px' );
                    */

                    jQuery( '#table_items, #table_taxes, #table_discounts' ).on( 'focus', '.description_tax, .description_item, .description_discount', function(){
                        jQuery(this).css( 'height', '142px' );
                    });

                    jQuery( '#table_items, #table_taxes, #table_discounts' ).on( 'blur', '.description_tax, .description_item, .description_discount', function(){
                        jQuery(this).css( 'height', '26px' );
                    });


                    jQuery('#add_new_item').on( 'keypress', '#item_rate', not_price );

                    //for run recalculation if delete late fee
                    jQuery('#load_delete_late_fee').on( 'click', recalculation );

                    jQuery('#wpc_data_show_vat').on( 'click', function() {
                        jQuery( '.wpc_vat' ).css('display', jQuery( this ).is(':checked') ? 'table-row' : 'none');
                    });

                    jQuery('#added_items').on( 'change', '.item_price, .tax_rate, .discount_rate, .item_qty, .discount_type, .tax_type', recalculation );
                    jQuery('#added_items').on( 'keyup', '.item_price, .tax_rate, .discount_rate, .item_qty', recalculation );
                    jQuery('#added_items').on( '.item_price, .discount_rate', not_price_minus );
                    jQuery('#added_items').on( 'keypress', '.tax_rate, .item_qty', not_price );
                    jQuery('#added_items').on( 'change', '.discount_type', function() {
                        var id_discount = jQuery( this ).data('number');
                        if ( 'amount' === jQuery( this ).val() ) {
                            jQuery( '#discount_rate' + id_discount ).next().css( 'display', 'none' );
                        } else {
                            jQuery( '#discount_rate' + id_discount ).next().css( 'display', 'inline' );}
                    });

                    //for delete item link
                    jQuery( '#table_items' ).on( 'click', '.row_del', function() {
                        jQuery(this).parent().remove();
                        recalculation();
                    });

                    //for delete discount link
                    jQuery( '#table_discounts' ).on( 'click', '.row_del', function() {
                        jQuery(this).parent().remove();
                        if ( !jQuery( '.discount_invoice' ).length ) {
                            jQuery( '#table_discounts thead' ).css( 'display', 'none' );
                        }
                        recalculation();
                    });

                    //for check all preset items
                    jQuery( 'body' ).on( 'change', '#check_all_preset_items', function(){
                        if ( jQuery(this).prop('checked') ) {
                            jQuery('.item_checkbox').attr( 'checked', true );
                        } else{
                            jQuery('.item_checkbox').attr( 'checked', false );
                        }
                    });

                    jQuery('.item_checkbox').live( 'change', function(){
                        if ( jQuery(this).is(':checked') ) {
                            if( jQuery('.item_checkbox').length === jQuery('.item_checkbox:checked').length )
                                jQuery('#check_all_preset_items').attr( 'checked', true );
                        } else{
                            jQuery('#check_all_preset_items').attr( 'checked', false );
                        }

                    });

                    //for delete tax link
                    jQuery( '#table_taxes' ).on( 'click', '.row_del', function() {
                        jQuery(this).parent().remove();
                        if ( !jQuery( '.tax_name' ).length ) {
                            jQuery( '#table_taxes thead' ).css( 'display', 'none' );
                        }
                        recalculation();
                    });

                    jQuery( '#button_add_item' ).live( 'click', function(){
                        jQuery( '.item_checkbox' ).each( function(){
                            if ( jQuery(this).attr("checked") ) {
                                var tds = jQuery(this).closest('tr').find('>td');
                                var name  = tds.eq(1).find('span').data('info');
                                var cf = tds.eq(1).find('span').data();
                                var description = tds.eq(1).find('span').attr('title');
                                var rate =  parseFloat( tds.eq(2).find('span').data( 'number' ).toString().replace( ',', "." ) ).toFixed(rate_capacity);

                                add_items( name, description, rate, cf );
                            }
                        });
                        recalculation();
                    });

                    jQuery('body').on('click', '#button_add_tax', function(){
                        jQuery( '.tax_checkbox' ).each( function(){
                            if ( jQuery(this).prop("checked") ) {
                                var tds = jQuery(this).closest('tr').find('>td');
                                var name  = tds.eq(1).find('span').data('info');
                                var description = tds.eq(2).find('span').data('info');
                                var rate =  parseFloat( tds.eq(3).find('span').text() ).toFixed(rate_capacity);

                                add_taxes( name, description, rate);
                            }
                        });
                        recalculation();
                    });

                    //Save item
                    jQuery( '#button_save_item' ).click( function() {
                        var errors = 0;

                        if ( '' === jQuery( "#item_name" ).val() ) {
                            jQuery( '#item_name' ).parent().parent().attr( 'class', 'wpc_error' );
                            errors = 1;
                        } else {
                            jQuery( '#item_name' ).parent().parent().removeClass( 'wpc_error' );
                        }

                        if ( '' === jQuery( "#item_rate" ).val() ) {
                            jQuery( '#item_rate' ).parent().parent().attr( 'class', 'wpc_error' );
                            errors = 1;
                        } else {
                            jQuery( '#item_rate' ).parent().parent().removeClass( 'wpc_error' );
                        }

                        if ( 0 === errors ) {

                            var item_name = jQuery( '#item_name' ).val();
                            var item_description = jQuery( '#item_description' ).val();
                            var item_rate = parseFloat( jQuery( '#item_rate' ).val().replace( ',', "." ), 10 );
                            item_rate = item_rate.toFixed( rate_capacity );

                            jQuery('.add_new_item').shutter_box('close');

                            add_items( item_name, item_description, item_rate );

                            recalculation();

                            if( jQuery('#item_save').prop("checked") ) {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: admin_url + 'admin-ajax.php',
                                    data: 'action=inv_save_new_item&name=' + item_name + '&description=' + item_description + '&rate=' + item_rate,
                                    dataType: "json",
                                    success: function( data ){}
                                });
                            }
                        }
                        return false;
                    });

                    //Add New Discount
                    jQuery( '#add_new_discount' ).click( function() {
                        var html;
                        num_items = num_items + 1;
                        html = html + '<tr class="discount_invoice" valign="top">';
                        html = html +      '<td class="row_del"><span></span></td>';
                        html = html +      '<td width="160px"><input type="text" class="discount_name" id="discount_name' + num_items  + '" name="wpc_data[discounts][' + num_items + '][name]" value="' + '<?php _e( 'New Item', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '" /></td>';
                        html = html +      '<td><textarea maxlength="300" class="description_discount" id="discount_description' + num_items + '" name="wpc_data[discounts][' + num_items + '][description]"></textarea></td>' ;
                        html = html +      '<td width="150px"><select id="discount_type' + num_items  + '" class="discount_type" data-number="' + num_items  + '" name="wpc_data[discounts][' + num_items  + '][type]" >';
                        html = html +           '<option value="amount" selected="selected">' + '<?php _e( 'Amount Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '</option>';
                        html = html +           '<option value="percent">' + '<?php _e( 'Percent Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '</option>';
                        html = html +      '</select></td>';
                        html = html +      '<td width="60px" class="add_procent"><input type="text" class="discount_rate" data-number="' + num_items  + '" id="discount_rate' + num_items  + '" name="wpc_data[discounts][' + num_items  + '][rate]" size="4" value="' + price_null + '" /><span style="display: none;">%</span></td>';
                        html = html +      '<td width="50px" align="right">&nbsp;<span class="discount_total" data-total="0" data-number="' + num_items  + '" id="item_total' + num_items  + '">' + price_null + '</span><input type="hidden" name="wpc_data[discounts][' + num_items + '][total]" value="" id="hidden_total' + num_items + '"  /></td>';
                        html = html + '</tr>';
                        jQuery( '#added_items #table_discounts tbody' ).append( html );

                        jQuery( '#added_items #table_discounts thead' ).css( 'display' , 'table-header-group' );

                        jQuery('#added_items').css('display', 'block');
                    });

                    //Add New Tax
                    jQuery( '#add_new_tax' ).click( function() {

                        num_items = num_items + 1;

                        var html = '<tr valign="top">';
                        html = html + '<td class="row_del"><span></span></td>';
                        html = html + '<td width="160px"><input type="text" size="15" class="tax_name" id="tax_name' + num_items + '" name="wpc_data[taxes][' + num_items + '][name]" value="' + '<?php _e( 'New Item', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '" /></td>';
                        html = html + '<td><textarea class="description_tax" id="tax_description' + num_items + '" name="wpc_data[taxes][' + num_items + '][description]"></textarea></td>' ;
                        html = html + '<td width="145px"><select id="tax_type' + num_items  + '" class="tax_type" data-number="' + num_items  + '" name="wpc_data[taxes][' + num_items  + '][type]" >';
                        html = html +   '<option value="before" selected="selected">' + '<?php _e( 'Before Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '</option>';
                        html = html +   '<option value="after">' + '<?php _e( 'After Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>' + '</option>';
                        html = html + '</select></td>';
                        html = html + '<td width="60px" class="add_procent"><input type="text" class="tax_rate" data-number="' + num_items + '" id="tax_rate' + num_items + '" name="wpc_data[taxes][' + num_items + '][rate]" size="4" value="' + price_null + '" />%</td>';
                        html = html + '<td width="50px" align="right">&nbsp;<span class="tax_total" data-total="0" data-number="' + num_items + '" id="item_total' + num_items + '">' + price_null + '</span><input type="hidden" name="wpc_data[taxes][' + num_items + '][total]" value="" id="hidden_total' + num_items + '"  /></td>';
                        html = html + '</tr>';
                        jQuery( '#added_items #table_taxes tbody' ).append( html );

                        jQuery( '#added_items #table_taxes thead' ).css( 'display', 'table-header-group' );

                        jQuery('#added_items').css('display', 'block');
                    });

                    // sortable
                    jQuery('tbody.wpc_sortable').nestedSortable({
                        items: 'tr',
                        opacity: .6,
                        revert: 250,
                        tolerance: 'pointer',
                        listType: 'tbody',
                        maxLevels: 1,
                        containment: 'parent'
                    });

                    // action add_new_invoice from Client action list
                    var url = new URL(window.location);
                    var clientID = url.searchParams.get('client_id');
                    if ( clientID > 0 ) {
                        jQuery('#wpc_clients').val(clientID);
                        jQuery('.counter_wpc_clients').html('(1)');
                    }

                });
            </script>

            <?php $readonly = ( !$data['option']['can_edit'] ) ? ' readonly' : '';

            if ( !isset( $data['data']['items'] ) ) {
                $data['data']['items'] = array();
            }

            $sub_total = ( isset( $data['data']['sub_total'] ) ) ? $data['data']['sub_total'] : '0';
            $total_discount = ( isset( $data['data']['total_discount'] ) ) ? $data['data']['total_discount'] : '0';
            $late_fee = ( isset( $data['data']['added_late_fee']) ) ? $data['data']['added_late_fee'] : '0';

            if ( 'request_estimate_edit' != $_GET['tab'] ) {
                $wpc_custom_fields = WPC()->get_settings( 'inv_custom_fields' );
            } else {
                $wpc_custom_fields = array();
            }

            $new_cols = array( 'thead' => '', 'tbody' => array() );

            if ( isset( $data['data']['custom_fields'] ) ) {
                if ( $data['data']['custom_fields'] ) {
                    $array_display_cf = array_keys( $data['data']['custom_fields'] );
                } else {
                    $array_display_cf = array();
                }
                if( !in_array( 'description', $array_display_cf) ) {
                    $add_class_hide_for_description = 'cf_hide';
                }
            }

            foreach ( $wpc_custom_fields as $key => $value ) {
                if ( isset( $array_display_cf ) ) {
                    $value['add_class_hide'] = ( !in_array( $key, $array_display_cf) ) ? 'cf_hide' : '';
                } elseif ( isset( $_GET['id'] ) ) {
                    $value['add_class_hide'] = 'cf_hide';
                } else {
                    $value['add_class_hide'] = empty( $value['display'] ) ? 'cf_hide' : '';
                }

                if ( $readonly ) {
                    $value['field_readonly'] = 1;
                }

                $new_cols['thead'] .= '<td title="' . esc_attr( $value['description'] ) . '" class="icf_' . $key . ' ' . $value['add_class_hide'] . '">' . esc_html( $value['title'] ) . '</td>';
                $new_cols['tbody'][ $key ] = $value ;
            }

            //for isset( $_POST[wpc_data] )
            if( isset( $data['data']['items'] ['{num_items}'] ) ) {
                unset( $data['data']['items'] ['{num_items}'] );
            } ?>

            <div id="added_items" <?php if ( 0 == count( $data['data']['items'] ) ) { echo 'style="display:none;"' ; } ?> >
                <table cellpadding="0" cellspacing="0" align="center" id="table_items">
                    <thead>
                        <tr>
                            <td></td>
                            <td width="160px"><?php _e( 'Name', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                            <td class="descr_display <?php echo ( isset( $add_class_hide_for_description ) ) ? $add_class_hide_for_description : '' ?>">
                                <?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                <?php echo WPC()->admin()->tooltip( __( 'Description will be reduced to 1000 characters to generate the PDF.', WPC_CLIENT_TEXT_DOMAIN ) ); ?>
                            </td>
                            <?php
                                echo $new_cols['thead'] ;
                            ?>
                            <td style="padding: 0 0 0 15px" width="60px"><?php _e( 'Qty.', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                            <td style="padding: 0 0 0 15px" width="60px"><?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                            <td align="right" width="50px"><?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        </tr>
                    </thead>
                    <tbody class="wpc_sortable">
                    <?php

                    array_unshift( $data['data']['items'], array( "name"=> "", "description" => "", "quantity" => 1, "price" => 0  ) );

                    foreach ( $data['data']['items'] as $item ) {

                        $add_class = '';
                        $tds_body = '' ;
                        if ( '' != $item['name'] ) {
                            $data['option']['num_items'] ++;
                            $num_items = $data['option']['num_items'];
                            $add_class = ' class="item_qty"';
                        } else {
                            $num_items = '{num_items}';
                        }

                        foreach ( $new_cols['tbody'] as $field_slug => $field_settings ) {
                            if( !isset( $item[ $field_slug ] ) ) {
                                $item[ $field_slug ] = isset( $field_settings['default_value'] )
                                        ? $field_settings['default_value'] : '';
                            }
                            $tds_body .= '<td class="icf_' . $field_slug . ' ' . $field_settings['add_class_hide'] . '">';
                            $attrs = array(
                                'class' => 'item_custom_field',
                                'name' => "wpc_data[items][$num_items][$field_slug]",
                                'id' => $field_slug . $num_items,
                                'value' => $item[ $field_slug ],
                            );
                            $tds_body .= $this->get_html_for_custom_field( $field_settings, $attrs );
                            $tds_body .= '</td>';
                        }

                        if (!empty($item['deleted'])) {
                            $class_deleted = ' tr_deleted';
                            $title_deleted = ' title="' . sprintf( __( 'This Item was deleted by %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '"';
                            $hidden_deleted = '<input type="hidden" name="wpc_data[items][' . $num_items  . '][deleted]" value="1">';
                            $data_total = 0;
                        } else {
                            $class_deleted = '';
                            $title_deleted = '';
                            $hidden_deleted = '';
                            $data_total = number_format( round( $item['price'] * $item['quantity'], $rate_capacity ), $rate_capacity, '.', '' );
                        }

                        echo '<tr class="invoice_items' . $class_deleted . '"' . $title_deleted
                                . ( ( '' == $item['name'] ) ? ' style="display:none;"' : '' ) . ' valign="top">
                                <td ' . ( ( $can_edit ) ? 'class="row_del"' : '' ) . '>' . $hidden_deleted . '<span></span></td>
                                <td><input type="text" class="item_name" id="item_name' . $num_items  . '" name="wpc_data[items][' . $num_items . '][name]" value="' . $item['name'] . '" ' . $readonly . ' /></td>
                                <td class="descr_display ' . ( ( isset( $add_class_hide_for_description ) ) ? $add_class_hide_for_description : '' ) . '"><textarea class="description_item" id="item_description' . $num_items  . '" name="wpc_data[items][' . $num_items  . '][description]" ' . $readonly . '>' . ( ( isset($item['description'] ) ) ? $item['description'] : '' ) . '</textarea></td>
                                ' . $tds_body . '
                                <td><input type="text" data-number="' . $num_items  . '" id="item_quantity' . $num_items  . '" name="wpc_data[items][' . $num_items  . '][quantity]" value="' . $item['quantity'] . '" size="1" ' . ( ( !$can_edit ) ? 'readonly' : $add_class ) . ' /></td>

                                <td><input type="text" class="item_price" data-number="' . $num_items  . '" id="item_price' . $num_items  . '" name="wpc_data[items][' . $num_items  . '][price]" size="4" value="' . $item['price'] . '" ' . $readonly . ' /></td>
                                <td align="right"><span class="item_total" id="item_total' . $num_items  . '" data-total="' . $data_total . '">' . number_format( round( $item['price'] * $item['quantity'], $rate_capacity ), $rate_capacity, '.', $thousands_separator ) . '</span></td>
                             </tr>';
                    }
                    ?>
                    </tbody>
                </table>
                <div style="display: none;"><?php  ?></div>

                <table cellpadding="0" cellspacing="0" align="center" id="table_discounts">
                    <thead <?php echo ( !isset( $data['data']['discounts'] ) || !$data['data']['discounts'] ) ? 'style="display: none;"' : '' ?>>
                        <tr>
                            <td></td>
                            <td colspan="5"><?php _e( 'Discounts', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        </tr>
                    </thead>
                    <tbody class="wpc_sortable">
                    <?php
                        if ( isset( $data['data']['discounts'] ) ) {
                            foreach ( $data['data']['discounts'] as $disc ) {
                            $data['option']['num_items'] ++;
                            $num_items = $data['option']['num_items'];
                            $total_discont = ( 'amount' == $disc['type'] ) ? number_format( $disc['rate'], $rate_capacity, '.', '' ) : number_format( round( $sub_total * $disc['rate'] / 100 , $rate_capacity ), $rate_capacity, '.', '' );
                            $total_discont_html = ( 'amount' == $disc['type'] ) ? number_format( $disc['rate'], $rate_capacity, '.', $thousands_separator ) : number_format( round( $sub_total * $disc['rate'] / 100 , $rate_capacity ), $rate_capacity, '.', $thousands_separator );
                            echo '<tr class="discount_invoice" valign="top">
                                    <td  ' . ( ( $can_edit ) ? 'class="row_del"' : '' ) . '><span></span></td>
                                    <td width="160px"><input type="text" class="discount_name" id="discount_name' . $num_items  . '" name="wpc_data[discounts][' . $num_items . '][name]" value="' . $disc['name'] . '" ' . $readonly . ' /></td>
                                    <td><textarea maxlength="300" class="description_discount" id="discount_description' . $num_items  . '" name="wpc_data[discounts][' . $num_items  . '][description]" ' . $readonly . '>' . (( isset( $disc['description'] ) ) ? $disc['description'] : '' ) . '</textarea></td>
                                    <td width="150px"><select id="discount_type' . $num_items  . '" class="discount_type" data-number="' . $num_items  . '" name="wpc_data[discounts][' . $num_items  . '][type]" ' . ( ( !$can_edit ) ? 'disabled' : '' ) . ' >
                                            <option value="amount" '. ( ( 'amount' == $disc['type'] ) ? 'selected="selected"' : '' ) . '>' . __( 'Amount Discount', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                                            <option value="percent" '. ( ( 'percent' == $disc['type'] ) ? 'selected="selected"' : '' ) . '>' . __( 'Percent Discount', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                                    </select></td>
                                    <td width="60px" class="add_procent"><input type="text" class="discount_rate" data-number="' . $num_items  . '" id="discount_rate' . $num_items  . '" name="wpc_data[discounts][' . $num_items  . '][rate]" size="4" value="' . $disc['rate'] . '" ' . $readonly . ' /><span style="display: ' . ( ( 'percent' == $disc['type'] ) ? 'inline' : 'none' ) . ';">%</span></td>
                                    <td width="50px" align="right">&nbsp;<span class="discount_total" data-number="' . $num_items  . '" id="item_total' . $num_items  . '" data-total="' . $total_discont . '">' . $total_discont_html . '</span><input type="hidden" name="wpc_data[discounts][' . $num_items  . '][total]" value="' . $total_discont . '" id="hidden_total' . $num_items  . '"  /></td>
                                 </tr>';
                        }
                        }

                    ?>
                    </tbody>
                </table>
                <table cellpadding="0" cellspacing="0" align="center" id="table_taxes">
                    <thead <?php echo ( !isset( $data['data']['taxes'] ) || !$data['data']['taxes'] ) ? 'style="display: none;"' : '' ?>>
                        <tr>
                            <td></td>
                            <td colspan="5"><?php _e( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        </tr>
                    </thead>
                    <tbody class="wpc_sortable">
                    <?php
                        if ( isset( $data['data']['taxes'] ) ) {
                            foreach ( $data['data']['taxes'] as $tax ) {
                            $data['option']['num_items'] ++;
                            $num_items = $data['option']['num_items'];
                            $total_tax = ( 'before' == $tax['type'] ) ? number_format( round( $sub_total * $tax['rate'] / 100, $rate_capacity ), $rate_capacity, '.', '' ) : number_format( round( ( $sub_total - $total_discount ) * $tax['rate'] / 100, $rate_capacity ), $rate_capacity, '.', '' );
                            $total_tax_html = ( 'before' == $tax['type'] ) ? number_format( round( $sub_total  * $tax['rate'] / 100 , $rate_capacity ), $rate_capacity, '.', $thousands_separator ) : number_format( round( ( $sub_total - $total_discount ) * $tax['rate'] / 100, $rate_capacity ), $rate_capacity, '.', $thousands_separator );


                            echo '<tr valign="top">
                                    <td ' . ( ( $can_edit ) ? 'class="row_del"' : '' ) . '><span></span></td>
                                    <td width="160px"><input type="text" size="15" class="tax_name" id="tax_name' . $num_items  . '" name="wpc_data[taxes][' . $num_items . '][name]" value="' . $tax['name'] . '" ' . $readonly . ' /></td>
                                    <td><textarea class="description_tax" id="tax_description' . $num_items  . '" name="wpc_data[taxes][' . $num_items  . '][description]" ' . $readonly . '>' . $tax['description'] . '</textarea></td>
                                    <td width="145px"><select id="tax_type' . $num_items  . '" class="tax_type" data-number="' . $num_items  . '" name="wpc_data[taxes][' . $num_items  . '][type]" ' . ( ( !$can_edit ) ? 'disabled' : '' ) . ' >
                                            <option value="before" '. ( ( 'before' == $tax['type'] ) ? 'selected="selected"' : '' ) . '>' . __( 'Before Discount', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                                            <option value="after" '. ( ( 'after' == $tax['type'] ) ? 'selected="selected"' : '' ) . '>' . __( 'After Discount', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                                    </select></td>
                                    <td width="60px" class="add_procent"><input type="text" class="tax_rate" data-number="' . $num_items  . '" id="tax_rate' . $num_items  . '" name="wpc_data[taxes][' . $num_items  . '][rate]" size="4" value="' . $tax['rate'] . '" ' . $readonly . ' />%</td>
                                    <td width="50px" align="right">&nbsp;<span class="tax_total" data-number="' . $num_items  . '" id="item_total' . $num_items  . '" data-total="' . $total_tax . '">' . $total_tax_html . '</span><input type="hidden" name="wpc_data[taxes][' . $num_items  . '][total]" value="' . $total_tax . '" id="hidden_total' . $num_items  . '" /></td>
                                 </tr>';
                            }
                        }
                    ?>
                    </tbody>
                </table>

                <hr />
                <?php
                    $selected_curr = isset ( $data['data']['currency'] ) ? $data['data']['currency'] : '' ;
                    $price_null = $this->get_currency( 0, true, $selected_curr );
                ?>
                <table class="total_all" align="right" cellpadding="0" cellspacing="0" >

                    <tr>
                        <td><?php _e( 'Sub Total:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td width="60px"><span id="total_all_items"><?php echo ( !empty( $sub_total ) ) ? $this->get_currency( $sub_total, true, $selected_curr ) : $price_null ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Discount:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td><span id="total_discount"><?php echo ( !empty( $total_discount ) ) ? $this->get_currency( $total_discount, true, $selected_curr ) : $price_null ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Tax:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td><span id="total_tax"><?php echo ( !empty( $data['data']['total_tax'] ) ) ? $this->get_currency( $data['data']['total_tax'], true, $selected_curr ) : $price_null ?></span></td>
                    </tr>
                    <?php
                        if ( !empty( $vat ) ) {
                            $total_vat = $sub_total - $total_discount + $late_fee;
                            $vat_value = round( $total_vat * $vat / (100+$vat), $this->get_rate_capacity() );
                        }
                    ?>
                    <tr class="wpc_vat">
                        <td><?php _e( 'Total Net:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td><span id="total_net"><?php echo ( !empty( $vat_value ) ) ? $this->get_currency( $total_vat - $vat_value, true, $selected_curr ) : $price_null ?></span></td>
                    </tr>
                    <tr class="wpc_vat">
                        <td><?php _e( 'VAT:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td><span id="total_vat"><?php echo ( !empty( $vat_value ) ) ? $this->get_currency( $vat_value, true, $selected_curr ) : $price_null ?></span></td>
                    </tr>
                    <?php
                    /* if ( isset( $data['data']['due_date'] ) && '' != $data['data']['due_date'] ) {
                        $due_date = strtotime( $data['data']['due_date'] . ' ' . date( 'H:i:s' ) );
                    }*/

                    if ( !empty( $late_fee ) ) {
                    ?>
                        <tr>
                            <td><?php _e( 'Late Fee:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                            <td><span id="late_fee"><?php echo $this->get_currency( $late_fee, true, $selected_curr ) ?></span></td>
                        </tr>
                    <?php
                        }
                        if ( !isset( $data['option']['payment_amount'] ) ) {
                            $data['option']['payment_amount'] = 0;
                        }
                    ?>
                    <tr class="total_all bold">
                        <td><?php _e( 'Total:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td>
                            <span id="total_all">
                                <?php echo ( isset( $data['data']['total'] ) ) ? $this->get_currency( $data['data']['total'] - $data['option']['payment_amount'], true, $selected_curr ) : $price_null ?>
                                <span class="real_amount" style="display: none;"><?php echo ( isset( $data['data']['total'] ) ) ? $data['data']['total'] - $data['option']['payment_amount'] : $price_null ?></span>
                            </span>
                        </td>
                    </tr>
                    <?php
                    if ( 'request_estimate_edit' != $_GET['tab'] ) {
                    ?>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php if ( !$can_edit && '' != $data['option']['payment_amount'] ) { ?>
                        <tr>
                            <td><?php _e( 'Amount Paid:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                            <td>
                                <span id="wpc_amount_paid">
                                    <?php echo $this->get_currency( $data['option']['payment_amount'], true, $selected_curr ) ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                            if( !isset( $data['data']['recurring_type'] ) ) {
                        ?>
                        <tr>
                            <td><?php _e( 'Total Remaining:', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                            <td>
                                <span id="total_remaining">
                                    <?php echo $this->get_currency( $data['data']['total'] - $data['option']['payment_amount'], true, $selected_curr ) ?>
                                    <span class="real_amount" style="display: none;"><?php echo $data['data']['total'] - $data['option']['payment_amount'] ?></span>
                                </span>
                            </td>
                        </tr>
                    <?php }
                        } else {
                        ?>
                        <tr>
                            <td></td>
                            <td><span id="wpc_amount_paid" style="display: none;"><?php echo $this->get_currency( 0, true, $selected_curr ) ?></span></td>
                        </tr>

                    <?php
                        }
                    }
                ?>
                </table>
                <div class="clear"></div>

            </div>
            <br />
            <?php if( $can_edit ) { ?>
                <span>
                    <a href="javascript:void(0);" class="add_new_item"><?php _e( 'Add New Item', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </span>
                &nbsp;&nbsp;&#124;&nbsp;&nbsp;
                <span>
                    <a href="javascript:void(0);" class="all_items"><?php _e( 'Add Preset Items', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </span>
                &nbsp;&nbsp;&#124;&nbsp;&nbsp;
                <span>
                   <a href="javascript:void(0);" id="add_new_discount"><?php _e( 'Add New Discount', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </span>
                &nbsp;&nbsp;&#124;&nbsp;&nbsp;
                <span>
                   <a href="javascript:void(0);" id="add_new_tax"><?php _e( 'Add New Tax', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </span>
                &nbsp;&nbsp;&#124;&nbsp;&nbsp;
                <span>
                   <a href="javascript:void(0);" class="all_taxes"><?php _e( 'Add Preset Tax', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </span>

                <div class="wpc_add_new_item" id="add_new_item" style="display: none;">
                    <form method="post" name="wpc_add_new_item" id="wpc_add_new_item" style="float:left;width:100%;">
                        <table style="float:left;width:100%;">
                            <tr>
                                <td>
                                    <label>
                                        <?php _e( 'Item Name:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                        <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <br />
                                        <input type="text" size="70" name="item_name" id="item_name" class="item_name" style="float:left;width:100%;" />
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <?php _e( 'Description:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                        <br />
                                        <textarea cols="70" rows="5" id="item_description" style="float:left;width:100%;"></textarea>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <?php _e( 'Rate:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                        <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <br />
                                        <input type="text" size="70" id="item_rate" style="float:left;width:100%;" />
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <br />
                                    <label>
                                        <input type="checkbox" id="item_save" />
                                        <?php _e( 'Save this Item as Preset Item for future use', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                    <br />
                                </td>
                            </tr>
                        </table>
                        <br />
                        <div style="clear: both; text-align: center;">
                            <input type="button" class="button-primary" id="button_save_item" value="<?php _e( 'Save Item', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </div>
                    </form>
                </div>
            <?php }
        }
    //end class
    }

}