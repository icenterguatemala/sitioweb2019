<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'WPC_INV_AJAX' ) ) {

    class WPC_INV_AJAX extends WPC_INV_Admin_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->inv_common_construct();
            $this->inv_admin_common_construct();

            //get all items
            add_action( 'wp_ajax_inv_all_items', array( &$this, 'ajax_inv_all_items' ) );

            //get all taxes
            add_action( 'wp_ajax_inv_all_taxes', array( &$this, 'ajax_inv_all_taxes' ) );

            //actions for change currency
            add_action( 'wp_ajax_inv_change_currency', array( &$this, 'ajax_inv_change_currency' ) );

            //actions for save new item on metabox 'Items'
            add_action( 'wp_ajax_inv_save_new_item', array( &$this, 'ajax_inv_save_new_item' ) );

            add_action( 'wp_ajax_change_inv_custom_field_order', array( &$this, 'ajax_change_inv_custom_field_order' ) );

            //actions for change invoice status on Invoices Page
            add_action( 'wp_ajax_inv_change_status', array( &$this, 'ajax_inv_change_status' ) );

            //action for update assigned data
            add_action( 'wpc_assign_popup_update_additional_data', array( &$this, 'update_assigned_data' ) );

            //action for delete late fee
            add_action( 'wp_ajax_inv_delete_late_fee', array( &$this, 'ajax_inv_delete_late_fee' ) );

            //for dashboard
            add_action( 'wp_ajax_wpc_inv_statistic_dashboard_widget', array( &$this, 'inv_statistic_dashboard_widget' ) );

            add_action( 'wp_ajax_wpc_inv_get_graph_data_widget', array( &$this, 'inv_get_graph_data_widget' ) );

            //Check Profile has active subscriptions
            add_action( 'wp_ajax_inv_is_active_subscriptions', array( &$this, 'profile_has_active_subscriptions' ) );

            //get add payment form for invoice
            add_action( 'wp_ajax_inv_get_invoice_data', array( &$this, 'inv_get_invoice_data' ) );


            //get edit item form
            add_action( 'wp_ajax_inv_get_item_data', array( &$this, 'inv_get_item_data' ) );

            //get edit tax form
            add_action( 'wp_ajax_inv_get_tax_data', array( &$this, 'inv_get_tax_data' ) );

        }

        function inv_get_invoice_data() {
            if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
                echo json_encode( array(
                    'title'     => __( 'Add Payment', WPC_CLIENT_TEXT_DOMAIN ),
                    'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
                ) );
                exit;
            }

            $id = $_POST['id'];

            global $wpdb;

            $wpdb->query("SET SESSION SQL_BIG_SELECTS=1");
            $invoice = $wpdb->get_row( $wpdb->prepare(
                "SELECT p.ID AS id,
                    p.post_title AS title,
                    p.post_date AS date,
                    coa.assign_id AS client_id,
                    p.post_status AS status,
                    u.user_login AS client_login,
                    pm1.meta_value AS total,
                    pm5.meta_value AS parent_id,
                    pm3.meta_value AS number,
                    pm4.meta_value AS type,
                    pm6.meta_value AS currency
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.object_type = 'invoice' )
                LEFT JOIN {$wpdb->users} u ON ( u.ID = coa.assign_id )
                LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
                LEFT JOIN {$wpdb->postmeta} pm3 ON ( p.ID = pm3.post_id AND pm3.meta_key = 'wpc_inv_number' )
                LEFT JOIN {$wpdb->postmeta} pm4 ON ( p.ID = pm4.post_id AND pm4.meta_key = 'wpc_inv_recurring_type' )
                LEFT JOIN {$wpdb->postmeta} pm5 ON ( p.ID = pm5.post_id AND pm5.meta_key = 'wpc_inv_parrent_id' )
                LEFT JOIN {$wpdb->postmeta} pm6 ON ( p.ID = pm6.post_id AND pm6.meta_key = 'wpc_inv_currency' )
                WHERE p.id=%d AND p.post_type='wpc_invoice'",
                $id
            ), ARRAY_A );

            if( !empty( $invoice ) ) {

                $amount_paid = 0;
                if( 'partial' == $invoice['status'] ) {
                    $amount_paid = $this->get_amount_paid( $invoice['id'] );
                }

                $allow_partial = get_post_meta( $invoice['id'], 'wpc_inv_deposit', true );

                ob_start(); ?>

                <form method="post" name="wpc_add_payment" id="wpc_add_payment" style="float:left;width:100%;">
                    <input type="hidden" name="wpc_payment[inv_id]" id="wpc_payment_inv_id" value="<?php echo $id ?>" />
                    <input type="hidden" name="wpc_payment[currency]" id="wpc_payment_currency" value="<?php echo $invoice['currency'] ?>" />
                    <table style="float:left;width:100%;">
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Invoice Total:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <span id="wpc_add_payment_total"><?php echo $this->get_currency( $invoice['total'], true, $invoice['currency'] ) ?></span>
                                </label>
                            </td>
                        </tr>
                        <?php if( $amount_paid > 0 ) { ?>
                            <tr>
                                <td>
                                    <label>
                                        <?php _e( 'Amount Paid:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                        <span id="wpc_add_payment_amount_paid"><?php echo $this->get_currency( $amount_paid, true, $invoice['currency'] ) ?></span>
                                    </label>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <label>
                                    <br />
                                    <?php _e( 'Amount Received:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    <br />
                                    <input type="text" style="float:left;width:100%;" name="wpc_payment[amount]" id="wpc_payment_amount"  value="<?php echo $invoice['total'] - $amount_paid ?>" <?php if( !$allow_partial ) { ?>readonly="readonly" <?php } ?>/>
                                </label>
                                <?php if( $allow_partial ) { ?>
                                    <br />
                                    <span class="description" id="wpc_payment_amount_description">
                                        <?php _e( "Not to be more than total. Can be a partial payment.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    <br />
                                    <br />
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>
                                            <label>
                                                <?php _e( 'Payment date:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                                <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                                <br />
                                                <input type="text" name="wpc_payment[date]" id="wpc_payment_date" value="<?php echo date( 'm/d/Y', time() ) ?>" />
                                            </label>
                                            <br />
                                        </td>
                                        <td width="50"></td>
                                        <td>
                                            <label>
                                                <?php
                                                    $gateways = $this->get_manual_gateways();
                                                    _e( 'Payment Method:', WPC_CLIENT_TEXT_DOMAIN );
                                                ?>
                                                <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                                <br />
                                                <select name="wpc_payment[method]" id="wpc_payment_method" >
                                                    <?php
                                                        $selected = ' selected="selected"';
                                                        foreach ($gateways as $key => $gateway) {
                                                            echo '<option value="' . $key . '"' . $selected . '>' . $gateway . '</option>';
                                                            $selected = '';
                                                        }
                                                    ?>
                                                </select>
                                            </label>
                                            <br />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Notes:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <textarea style="float:left;width:100%;" rows="3" name="wpc_payment[notes]" id="wpc_payment_notes" ></textarea>
                                </label>
                                <br />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="checkbox" name="wpc_payment[thanks]" id="wpc_payment_thanks"  value="1" />
                                    <?php _e( 'Send the "thank you" note for this payment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div style="clear: both; text-align: center;">
                        <input type="button" class="button-primary" id="save_add_payment" value="<?php _e( 'Add Payment', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        <input type="button" class="button" id="close_add_payment" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>


                <?php $content = ob_get_contents();
                if (ob_get_length()) {
                    ob_end_clean();
                }

                echo json_encode(array(
                    'title' => __( 'Add Payment', WPC_CLIENT_TEXT_DOMAIN ),
                    'content' => $content
                ));
                exit;
            }

            echo json_encode( array(
                'title'     => __( 'Add Payment', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }


        function inv_get_item_data() {
            $data = array();

            $id = filter_input( INPUT_POST, 'id' );

            if ( $id ) {
                global $wpdb;

                $data = $wpdb->get_row( $wpdb->prepare(
                        "SELECT *
                        FROM {$wpdb->prefix}wpc_client_invoicing_items
                        WHERE id=%d",
                        $id
                    ), ARRAY_A );

                if( empty( $data ) ) {
                    echo json_encode( array(
                        'title'     => __( 'Edit Item', WPC_CLIENT_TEXT_DOMAIN ),
                        'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
                    ) );
                    exit;
                }
            }

            if ( $id ) {
                $button_text = __( 'Update Item', WPC_CLIENT_TEXT_DOMAIN );
                $name = esc_attr( $data['name'] );
                $description = esc_html( $data['description'] );
                $rate = esc_attr( $data['rate'] );
                $use_r_est = checked( $data['use_r_est'], 1, false );
            } else {
                $button_text = __( 'Save Item', WPC_CLIENT_TEXT_DOMAIN );
                $name = $description = $rate = $use_r_est = '';
            }

            ob_start(); ?>

            <form method="post" name="wpc_edit_item" id="wpc_edit_item" style="float:left;width:100%;">
                <table style="float:left;width:100%;">
                    <tr>
                        <td>
                            <label>
                                <?php _e( 'Item Name:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                <br />
                                <input type="text" name="save_item[name]" style="float:left;width:100%;" id="item_edit_name" class="item_name"  value="<?php echo $name ?>" />
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <?php _e( 'Description:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                <br />
                                <textarea name="save_item[description]" style="float:left;width:100%;" rows="5" id="item_edit_description" class="item_description" ><?php echo $description ?></textarea>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <?php _e( 'Rate:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                <br>
                                <input type="text" name="save_item[rate]" style="float:left;width:100%;" id="item_edit_rate" class="item_rate" value="<?php echo $rate ?>" />
                            </label>
                        </td>
                    </tr>
                    <?php

                    $custom_fields = WPC()->get_settings( 'inv_custom_fields' );
                    $item_cf = maybe_unserialize( $data['data'] );
                    foreach ( $custom_fields as $key => $data_cf ) {
                        $data_cf['field_readonly'] = '';
                        $attrs = array(
                                'name' => "save_item[$key]",
                            );
                        if ( isset( $data['id'] ) ) {
                            $attrs['value'] = isset( $item_cf[ $key ] ) ? $item_cf[ $key ] : '';
                        }

                    ?>
                        <tr>
                            <td>
                                <label>
                                    <?php if ( !empty( $data_cf['title'] ) ) echo esc_html( $data_cf['title'] ); ?>
                                    <br />
                                    <?php echo $this->get_html_for_custom_field( $data_cf, $attrs ) ?>
                                    <br />
                                    <span class="description">
                                        <?php echo !empty( $data_cf['description'] ) ? '&nbsp;&nbsp;' .$data_cf['description'] : '' ?>
                                    </span>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <br>
                            <input type="button" class="button-primary" id="save_item" name="save_item" value="<?php echo $button_text ?>" />
                            <label>
                                <input type="checkbox" name="save_item[use_r_est]" <?php echo $use_r_est ?>>
                                <?php _e( 'Use this Item for Estimate Requests', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </label>
                            <br>
                        </td>
                    </tr>
                </table>
                <br />
                <div style="clear: both; text-align: center;">
                    <input type="hidden" id="action" name="action" value="save" />
                    <input type="hidden" name="save_item[id]" id="item_id" value="<?php echo $id ?>" />
                    <input type="hidden" value="<?php echo wp_create_nonce( 'wpc_inv_items_save' . get_current_user_id() ) ?>" name="_wpnonce" id="_wpnonce" />
                </div>
            </form>

            <?php $content = ob_get_clean();

            $title = $id ? sprintf( __( 'Edit Item: %s', WPC_CLIENT_TEXT_DOMAIN ), $name )
                    : __( 'New Item', WPC_CLIENT_TEXT_DOMAIN );

            echo json_encode(array(
                'title' => $title,
                'content' => $content
            ));
            exit;

        }


        function inv_get_tax_data() {
            if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
                echo json_encode( array(
                    'title'     => __( 'Edit Tax', WPC_CLIENT_TEXT_DOMAIN ),
                    'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
                ) );
                exit;
            }

            $id = $_POST['id'];

            $taxes = $this->get_taxes();

            $temp = array();
            foreach( $taxes as $tax ) {
                if( $tax['id'] == $id ) {
                    $temp = $tax;
                    break;
                }
            }

            $tax = $temp;

            if( !empty( $tax ) ) {
                ob_start(); ?>

                <form method="post" name="wpc_edit_tax" id="wpc_edit_tax" style="float:left;width:100%;">
                    <table style="float:left;width:100%;">
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Tax Name:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <input type="text" name="tax[name]" style="float:left;width:100%;" id="tax_edit_name" class="tax_name" value="<?php echo htmlspecialchars( $tax['name'] ) ?>" />
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Tax Description:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <textarea name="tax[description]" style="float:left;width:100%;" rows="5" id="tax_edit_description" class="tax_description"><?php echo htmlspecialchars( $tax['description'] ) ?></textarea>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <?php _e( 'Tax Rate:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    <br />
                                    <input type="text" name="tax[rate]" style="float:left;width:100%;" id="tax_edit_rate" class="tax_rate" value="<?php echo $tax['rate'] ?>" />
                                </label>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div style="clear: both; text-align: center;">
                        <input type="hidden" id="action" name="action" value="save" />
                        <input type="hidden" value="<?php echo wp_create_nonce( 'wpc_tax_save' . get_current_user_id() ) ?>" name="_wpnonce" id="_wpnonce" />
                        <input type="button" class="button-primary" id="save_tax" name="save_tax" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>

                <?php $content = ob_get_contents();
                if (ob_get_length()) {
                    ob_end_clean();
                }

                echo json_encode(array(
                    'title' => sprintf( __( 'Edit Tax: %s', WPC_CLIENT_TEXT_DOMAIN ), htmlspecialchars( $tax['name'] ) ),
                    'content' => $content
                ));
                exit;
            }

            echo json_encode( array(
                'title'     => __( 'Edit Tax', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        function profile_has_active_subscriptions() {
            $profile_id = ( !empty( $_POST['id'] ) ) ? $_POST['id'] : 0;
            global $wpdb;
            $active_subscriptions = $wpdb->get_var("SELECT count(id) "
                . "FROM {$wpdb->prefix}wpc_client_payments WHERE `data` LIKE '%\"profile_id\":\"{$profile_id}\"%' "
                . "AND `subscription_status`='active' ");
            $return = ( $active_subscriptions ) ? true : false;
            echo $return;
            exit;
        }


        function inv_get_graph_data_widget() {
            global $wpdb;

            if( isset( $_POST['period'] ) && !empty( $_POST['period'] ) && isset( $_POST['currency'] ) && !empty( $_POST['currency'] ) && isset( $_POST['page'] ) ) {

                $wpc_currency = WPC()->get_settings( 'currency' );

                $currency = $_POST['currency'];
                $currency_key = '';
                foreach( $wpc_currency as $key=>$curr ) {
                    if( $currency == $curr['code'] ) {
                        $currency_key = $key;
                    }
                }
                switch( $_POST['period'] ) {
                    case 'day':
                        $min_date = mktime( 0, 0, 0, date('n'), date('d') + $_POST['page'], date('Y') );
                        $max_date = mktime( 0, 0, 0, date('n'), date('d') + $_POST['page'] + 1, date('Y') ) - 1;

                        $current_date = date( "d F Y", $min_date ) . ' ' . __( 'year', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'week':
                        $min_date = mktime( 0, 0, 0, date('n'), date('d') - (int)date('w') + $_POST['page']*7 + 1, date('Y') );
                        $max_date = mktime( 0, 0, 0, date('n'), date('d') - (int)date('w') + $_POST['page']*7 + 7 + 1, date('Y') ) - 1;

                        $current_date = date( "W", $min_date ) . ' ' . __( 'week', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . __( 'of', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . date( "Y", $min_date ) . ' ' . __( 'year', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'month':
                        $min_date = mktime( 0, 0, 0, date('n') + $_POST['page'], 1, date('Y') );
                        $max_date = mktime( 0, 0, 0, date('n') + $_POST['page'] + 1, 1, date('Y') ) - 1;

                        $current_date = date( "F Y", $min_date ) . ' ' . __( 'year', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'quarter':
                        $min_date = mktime( 0, 0, 0, floor( ( date('n') - 1 ) / 3 )*3 + 1 + $_POST['page']*3, 1, date('Y') );
                        $max_date = mktime( 0, 0, 0, floor( ( date('n') - 1 ) / 3 )*3 + 1 + $_POST['page']*3 + 3, 1, date('Y') ) - 1;

                        $current_date = date( "F", $min_date ) . ' - ' . date( "F Y", $max_date ) . ' ' . __( 'year', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'year':
                        $min_date = mktime( 0, 0, 0, 1, 1, date('Y') + $_POST['page'] );
                        $max_date = mktime( 0, 0, 0, 1, 1, date('Y') + $_POST['page'] + 1 ) - 1;

                        $current_date = date('Y') + $_POST['page'] . ' ' . __( 'year', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                }


                //BUILD TOTAL STATISTIC
                $total_paid_invoices_amount = $wpdb->get_var(
                    "SELECT SUM( p.amount )
                    FROM {$wpdb->prefix}wpc_client_payments p
                    WHERE p.order_status = 'paid' AND
                        p.function = 'invoicing' AND
                        p.time_paid >= {$min_date} AND
                        p.time_paid < {$max_date} AND
                        currency = '{$currency}'"
                );

                if ( empty( $total_paid_invoices_amount ) ) {
                    $total_paid_invoices_amount = 0;
                 }


                $total_past_due_invoices_amount = $wpdb->get_row(
                    "SELECT SUM(pm1.meta_value) AS sum_amount,
                        COUNT(*) AS count
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                    LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_due_date' )
                    LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
                    LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                    WHERE p.post_type = 'wpc_invoice' AND
                        p.post_status != 'paid' AND
                        pm.meta_value < " . $max_date . " AND
                        pm.meta_value >= " . $min_date . " AND
                        pm2.meta_value = '{$currency_key}'
                    GROUP BY pm2.meta_value",
                ARRAY_A );

                if( empty( $total_past_due_invoices_amount ) ) {
                    $total_past_due_invoices_amount = array( 'sum_amount' => 0, 'count' => 0  );
                }


                $total_outstanding_invoices_amount = $wpdb->get_row(
                    "SELECT SUM(pm.meta_value) AS sum_amount,
                        COUNT(*) AS count
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                    LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_total' )
                    LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                    WHERE p.post_type = 'wpc_invoice' AND
                        ( p.post_status = 'draft' OR p.post_status = 'sent' OR p.post_status = 'open' ) AND
                        pm2.meta_value = '{$currency_key}' AND
                        date_format( p.post_date, '%Y-%m-%d %T') >= '" . date( "Y-m-d H:i:s", $min_date ) . "' AND
                        date_format( p.post_date, '%Y-%m-%d %T') < '" . date( "Y-m-d H:i:s", $max_date ) . "'
                    GROUP BY pm2.meta_value",
                ARRAY_A );

                if( empty( $total_outstanding_invoices_amount ) ) {
                    $total_outstanding_invoices_amount = array( 'sum_amount' => 0, 'count' => 0  );
                } else {
                    if( isset( $total_past_due_invoices_amount['sum_amount'] ) && !empty( $total_past_due_invoices_amount['sum_amount'] ) ) {
                        $total_outstanding_invoices_amount['sum_amount'] = $total_past_due_invoices_amount['sum_amount'] + ( $total_outstanding_invoices_amount['sum_amount'] - $total_past_due_invoices_amount['sum_amount'] );
                    }
                }


                $total_html = '<div class="widget_stats">
                    <div class="wrapper">
                        <span class="item_title" style="font-size: 15px;">' . __( 'Total Income', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                        <span class="item_count" style="font-size: 17px;">' . WPC()->get_price_string( $total_paid_invoices_amount, $currency_key ) . '</span>
                    </div>
                </div>
                <div class="widget_stats">
                    <div class="wrapper">
                        <span class="item_title" style="font-size: 13px;">' . __( 'Total Outstanding', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                        <span class="item_count" style="font-size: 15px;">' . WPC()->get_price_string( $total_outstanding_invoices_amount['sum_amount'], $currency_key ) . '</span>
                    </div>
                </div>
                <div class="widget_stats">
                    <div class="wrapper">
                        <span class="item_title" style="font-size: 13px;">' . __( 'Total due', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                        <span class="item_count" style="font-size: 15px;">' . WPC()->get_price_string( $total_past_due_invoices_amount['sum_amount'], $currency_key ) . '</span>
                    </div>
                </div>';

                $xaxis = array();
                if( $_POST['period'] == 'quarter' || $_POST['period'] == 'year' ) {
                    //BUILD GRAPH
                    $income_graph_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid >= {$min_date} AND
                            p.time_paid < {$max_date} AND
                            currency = '{$currency}'
                        GROUP BY MONTH(FROM_UNIXTIME( p.time_paid ))
                        ORDER BY date_day",
                    ARRAY_A );

                    $min_x = 0;

                    $previous_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid < {$min_date} AND
                            currency = '{$currency}'
                        GROUP BY MONTH(FROM_UNIXTIME( p.time_paid ))
                        ORDER BY date_day",
                    ARRAY_A );

                    $next_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid >= {$max_date} AND
                            currency = '{$currency}'
                        GROUP BY MONTH(FROM_UNIXTIME( p.time_paid ))
                        ORDER BY date_day",
                    ARRAY_A );

                } elseif( $_POST['period'] == 'week' || $_POST['period'] == 'month' ) {

                    //BUILD GRAPH
                    $income_graph_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m-%d' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid >= {$min_date} AND
                            p.time_paid < {$max_date} AND
                            currency = '{$currency}'
                        GROUP BY date_day
                        ORDER BY date_day",
                    ARRAY_A );


                    $min_x = date( "Y-m-d", $min_date );

                    $previous_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m-%d' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid < {$min_date} AND
                            currency = '{$currency}'
                        GROUP BY date_day
                        ORDER BY date_day",
                    ARRAY_A );

                    $next_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m-%d' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid >= {$max_date} AND
                            currency = '{$currency}'
                        GROUP BY date_day
                        ORDER BY date_day",
                    ARRAY_A );

                } else {
                    //BUILD GRAPH
                    $income_graph_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%H' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid >= {$min_date} AND
                            p.time_paid < {$max_date} AND
                            currency = '{$currency}'
                        GROUP BY date_day
                        ORDER BY date_day",
                    ARRAY_A );


                    $min_x = date( "Y-m-d H:i", $min_date );

                    $previous_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%H' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid < {$min_date} AND
                            currency = '{$currency}'
                        GROUP BY date_day
                        ORDER BY date_day",
                    ARRAY_A );

                    $next_data = $wpdb->get_results(
                        "SELECT SUM( p.amount ) AS day_sales,
                            date_format( FROM_UNIXTIME( p.time_paid ), '%H' ) AS date_day
                        FROM {$wpdb->prefix}wpc_client_payments p
                        WHERE p.order_status = 'paid' AND
                            p.function = 'invoicing' AND
                            p.time_paid >= {$max_date} AND
                            currency = '{$currency}'
                        GROUP BY date_day
                        ORDER BY date_day",
                    ARRAY_A );

                }

                $previous_enable = ( isset( $previous_data ) && !empty( $previous_data ) ) ? true : false;
                $next_enable = ( isset( $next_data ) && !empty( $next_data ) ) ? true : false;

                $index = 0;
                $graph_output = array();
                if( isset( $income_graph_data ) && !empty( $income_graph_data ) ) {
                    while( (int)$min_date <= (int)$max_date ) {
                        $day_sales = '0';
                        foreach( $income_graph_data as $value ) {

                            if( $_POST['period'] == 'quarter' || $_POST['period'] == 'year' ) {
                                if( $value['date_day'] == date( "Y-m", $min_date ) ) {
                                    $day_sales = $value['day_sales'];
                                }
                            } elseif( $_POST['period'] == 'week' || $_POST['period'] == 'month' ) {
                                if( $value['date_day'] == date( "Y-m-d", $min_date ) ) {
                                    $day_sales = $value['day_sales'];
                                }
                            } else {
                                if( $value['date_day'] == date( "H", $min_date ) ) {
                                    $day_sales = $value['day_sales'];
                                }
                            }
                        }

                        if( $_POST['period'] == 'quarter' || $_POST['period'] == 'year' ) {
                            $index++;
                            $graph_output[] = array( $index, (float)$day_sales );
                            $xaxis[] = date( "Y-m", $min_date );
                            $min_date = mktime( 0, 0, 0, date( 'n', $min_date ) + 1, 1, date( 'Y', $min_date ) );
                        } elseif( $_POST['period'] == 'week' || $_POST['period'] == 'month' ) {
                            $graph_output[] = array( date( "Y-m-d", $min_date ), (float)$day_sales );
                            $min_date += 60*60*24;
                        } else {
                            $graph_output[] = array( date( "Y-m-d H:i", $min_date ), (float)$day_sales );
                            $min_date += 60*60;
                        }
                    }
                } else {
                    while( (int)$min_date <= (int)$max_date ) {
                        $day_sales = '0';

                        if( $_POST['period'] == 'quarter' || $_POST['period'] == 'year' ) {
                            $index++;
                            $graph_output[] = array( $index, (float)$day_sales );
                            $xaxis[] = date( "Y-m", $min_date );
                            $min_date = mktime( 0, 0, 0, date( 'n', $min_date ) + 1, 1, date( 'Y', $min_date ) );
                        } elseif( $_POST['period'] == 'week' || $_POST['period'] == 'month' ) {
                            $graph_output[] = array( date( "Y-m-d", $min_date ), (float)$day_sales );
                            $min_date += 60*60*24;
                        } else {
                            $graph_output[] = array( date( "Y-m-d H:i", $min_date ), (float)$day_sales );
                            $min_date += 60*60;
                        }
                    }
                }

                echo json_encode( array( 'status' => true, 'graph_data' => $graph_output, 'total_html' => $total_html, 'min_x' => $min_x, 'current_date' => $current_date, 'previous_enable' => $previous_enable, 'next_enable' => $next_enable, 'xaxis' => $xaxis ) );
                exit;
            } else {
                echo json_encode( array( 'status' => false ) );
                exit;
            }
        }


        function inv_statistic_dashboard_widget() {
            global $wpdb;

            $wpc_currency = WPC()->get_settings( 'currency' );

            $default_key = WPC()->get_default_currency();


            $meta_currensies = $wpdb->get_col(
                "SELECT DISTINCT( meta_value )
                FROM {$wpdb->postmeta}
                WHERE meta_key='wpc_inv_currency'"
            );

            $all_currencies = array();

            $invoices_currencies = array_intersect_key( $wpc_currency, array_flip( array_intersect( array_keys( $wpc_currency ), $meta_currensies ) ) );
            foreach( $invoices_currencies as $invoices_currency ) {
                $all_currencies[] = $invoices_currency['code'];
            }

            //set time period
            $min_date = mktime( 0, 0, 0, date('n'), 1, date('Y') );
            $max_date = time();


            //BUILD TOTAL STATISTIC
            $total_paid_invoices_amount = $wpdb->get_var(
                "SELECT SUM( p.amount )
                FROM {$wpdb->prefix}wpc_client_payments p
                WHERE p.order_status = 'paid' AND
                    p.function = 'invoicing' AND
                    p.time_paid >= {$min_date} AND
                    currency = '{$wpc_currency[$default_key]['code']}'"
            );

            if ( empty( $total_paid_invoices_amount ) ) {
                $total_paid_invoices_amount = 0;
            }


            $total_past_due_invoices_amount = $wpdb->get_row(
                "SELECT SUM(pm1.meta_value) AS sum_amount,
                    COUNT(*) AS count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_due_date' )
                LEFT JOIN {$wpdb->postmeta} pm1 ON ( p.ID = pm1.post_id AND pm1.meta_key = 'wpc_inv_total' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                WHERE p.post_type = 'wpc_invoice' AND
                    p.post_status != 'paid' AND
                    pm.meta_value <= " . $max_date . " AND
                    pm.meta_value > " . $min_date . " AND
                    pm2.meta_value = '{$default_key}'
                GROUP BY pm2.meta_value",
            ARRAY_A );

            if( empty( $total_past_due_invoices_amount ) ) {
                $total_past_due_invoices_amount = array( 'sum_amount' => 0, 'count' => 0  );
            }


            $total_outstanding_invoices_amount = $wpdb->get_row(
                "SELECT SUM(pm.meta_value) AS sum_amount,
                    COUNT(*) AS count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm0 ON ( p.ID = pm0.post_id AND pm0.meta_key = 'wpc_inv_post_type' AND pm0.meta_value = 'inv' )
                LEFT JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'wpc_inv_total' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'wpc_inv_currency' )
                WHERE p.post_type = 'wpc_invoice' AND
                    ( p.post_status = 'draft' OR p.post_status = 'sent' OR p.post_status = 'open' ) AND
                    pm2.meta_value = '{$default_key}' AND
                    date_format( p.post_date, '%Y-%m-%d %T') > '" . date( "Y-m-d H:i:s", $min_date ) . "'
                GROUP BY pm2.meta_value",
            ARRAY_A );

            if( empty( $total_outstanding_invoices_amount ) ) {
                $total_outstanding_invoices_amount = array( 'sum_amount' => 0, 'count' => 0  );
            } else {
                if( isset( $total_past_due_invoices_amount['sum_amount'] ) && !empty( $total_past_due_invoices_amount['sum_amount'] ) ) {
                    $total_outstanding_invoices_amount['sum_amount'] = $total_past_due_invoices_amount['sum_amount'] + ( $total_outstanding_invoices_amount['sum_amount'] - $total_past_due_invoices_amount['sum_amount'] );
                }
            }

            $graph_output = array();
            //BUILD GRAPH
            $income_graph_data = $wpdb->get_results(
                "SELECT SUM( p.amount ) AS day_sales,
                    date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m-%d' ) AS date_day
                FROM {$wpdb->prefix}wpc_client_payments p
                WHERE p.order_status = 'paid' AND
                    p.function = 'invoicing' AND
                    p.time_paid >= {$min_date} AND
                    currency = '{$wpc_currency[$default_key]['code']}'
                GROUP BY date_day
                ORDER BY date_day",
            ARRAY_A );

            $previous_data = $wpdb->get_results(
                "SELECT SUM( p.amount ) AS day_sales,
                    date_format( FROM_UNIXTIME( p.time_paid ), '%Y-%m-%d' ) AS date_day
                FROM {$wpdb->prefix}wpc_client_payments p
                WHERE p.order_status = 'paid' AND
                    p.function = 'invoicing' AND
                    p.time_paid < {$min_date} AND
                    currency = '{$wpc_currency[$default_key]['code']}'
                GROUP BY date_day
                ORDER BY date_day",
            ARRAY_A );

            if( isset( $income_graph_data ) && !empty( $income_graph_data ) ) {

                $max_date = mktime( 0, 0, 0, date('n') + 1, 1, date('Y') ) - 1;

                while( (int)$min_date <= (int)$max_date ) {
                    $day_sales = '0';
                    foreach( $income_graph_data as $value ) {
                        if( $value['date_day'] == date( "Y-m-d", $min_date ) ) {
                            $day_sales = $value['day_sales'];
                        }
                    }
                    $graph_output[] = array( date( "Y-m-d", $min_date ), (float)$day_sales );

                    $min_date += 60*60*24;
                }
            }

            $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_inv_statistic_dashboard_widget', true );
            $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';


            ob_start(); ?>

            <script type="text/javascript">
                var loading = false;
                var plot1 = false;

                function build_graph( widget, period, currency, page ) {

                    if( plot1 !== undefined && plot1 !== false ) {
                        plot1.destroy();
                    }
                    jQuery( window ).unbind('resize');

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_inv_get_graph_data_widget&period=' + period + '&currency=' + currency + '&page=' + page,
                        dataType: 'json',
                        success: function( data ) {
                            if( data.status !== undefined && data.status === true ) {
                                widget.find('.total_stats').css( 'height', 'auto' ).html( data.total_html );

                                widget.find('.graph_wrapper').html(
                                    '<div id="inv_graph"></div>' +
                                    '<div class="graph_period" data-period_pagination="' + page + '">' + data.current_date + '</div>'
                                );

                                if( data.previous_enable ) {
                                    widget.find('.widget_prev_wrapper').removeClass('disabled_period');
                                } else {
                                    widget.find('.widget_prev_wrapper').addClass('disabled_period');
                                }

                                if( data.next_enable ) {
                                    widget.find('.widget_next_wrapper').removeClass('disabled_period');
                                } else {
                                    widget.find('.widget_next_wrapper').addClass('disabled_period');
                                }

                                //buid plot
                                var line1 = data.graph_data;

                                if( period === 'day' || period === 'week' || period === 'month' ) {
                                    plot1 = jQuery.jqplot( 'inv_graph', [line1], {
                                        seriesDefaults:{
                                          fill: true,
                                          fillAndStroke: true,
                                          fillAlpha: 0.8,
                                          shadow: false
                                        },
                                        series:[
                                            {
                                                label:'Income',
                                                showMarker:false,
                                                rendererOptions: {
                                                    smooth: true
                                                }
                                            }
                                        ],
                                        seriesColors:['#4bb2c5'],
                                        axesDefaults: {
                                            tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer ,
                                            tickOptions: {
                                                fontSize: '10px'
                                            },
                                            labelOptions: {
                                                fontSize: '10px'
                                            }
                                        },
                                        axes: {
                                            xaxis: {
                                              pad: 0,
                                              min: data.min_x,
                                              renderer: jQuery.jqplot.DateAxisRenderer,
                                              labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                                              tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
                                              tickOptions: {
                                                  angle: -15,
                                                  formatString:( period !== 'day' ) ? '%F' : '%T',
                                                  showGridline: false
                                              }
                                            },
                                            yaxis: {
                                              min: 0,
                                              label: '<?php echo __( 'Income', WPC_CLIENT_TEXT_DOMAIN ) ?> (' + currency + ')',
                                              labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                                              tickOptions:{
                                                  formatString:'%.2f ' + currency,
                                                  showGridline: false
                                              }
                                            }
                                        },
                                        grid: {
                                            background: 'rgba(57,57,57,0.0)',
                                            drawBorder: false,
                                            shadow: false,
                                            gridLineColor: '#666666',
                                            gridLineWidth: 1
                                        },
                                        highlighter: {
                                            show: true,
                                            sizeAdjust: 7.5
                                        }
                                    });
                                } else {
                                    plot1 = jQuery.jqplot( 'inv_graph', [line1], {
                                        seriesDefaults:{
                                            renderer: jQuery.jqplot.BarRenderer,
                                            rendererOptions: {
                                                barPadding: 1,
                                                barMargin: 15,
                                                barWidth: ( period === 'quarter' ) ? 60 : 15
                                            },
                                            shadow: false
                                        },
                                        series:[
                                            {
                                                label:'Income'
                                            }
                                        ],
                                        seriesColors:['#4bb2c5'],
                                        axesDefaults: {
                                            tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
                                            tickOptions: {
                                                fontSize: '10px'
                                            },
                                            labelOptions: {
                                                fontSize: '10px'
                                            }
                                        },
                                        axes: {
                                            xaxis: {
                                                renderer: jQuery.jqplot.CategoryAxisRenderer,
                                                ticks: data.xaxis,
                                                tickOptions: {
                                                    angle: ( period === 'quarter' ) ? 0 : -90 ,
                                                    showGridline: false
                                                }
                                            },
                                            yaxis: {
                                                label: '<?php echo __( 'Income', WPC_CLIENT_TEXT_DOMAIN ) ?> (' + currency + ')',
                                                labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                                                tickOptions:{
                                                    formatString:'%.2f ' + currency,
                                                    showGridline: false
                                                }
                                            }
                                        },
                                        grid: {
                                            background: 'rgba(57,57,57,0.0)',
                                            drawBorder: false,
                                            shadow: false,
                                            gridLineColor: '#666666',
                                            gridLineWidth: 1
                                        },
                                        highlighter: {
                                            show: true,
                                            sizeAdjust: 7.5,
                                            tooltipAxes: 'y'
                                        }
                                    });
                                }

                                jQuery( window ).bind('resize', function(event, ui) {
                                    if( jQuery( '.wpc_inv_statistic_dashboard_widget:not(.collapsed) #inv_graph' ).length ) {
                                        if( period === 'day' || period === 'month' || period === 'week' ) {
                                            plot1.replot({
                                                resetAxes:true,
                                                axes: {
                                                    xaxis: {
                                                      pad: 0,
                                                      min: data.min_x
                                                    },
                                                    yaxis: {
                                                      min: 0
                                                    }
                                                }
                                            });
                                        } else {
                                            plot1.replot({
                                                resetAxes:true
                                            });
                                        }
                                    }
                                });

                            } else {
                                alert('<?php _e( 'Something went wrong!', WPC_CLIENT_TEXT_DOMAIN ) ?>');
                            }
                            loading = false;
                        }
                    });
                }

                jQuery( document ).ready( function() {

                    //change plot's period
                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'change', '.inv_change_period', function(e) {
                        if( !loading ) {
                            var widget = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget');

                            var period = jQuery(this).find('.selectbox_value').data('value');
                            var currency = widget.find('.inv_change_currency').find('.selectbox_value').data('value');
                            var page = 0;

                            widget.find('.total_stats').css( 'height', widget.find('.total_stats').height() ).html('<div class="ajax_bar_loading"></div>');
                            widget.find('.graph_wrapper').html('<div class="ajax_widget_loading"></div>');
                            loading = true;

                            build_graph( widget, period, currency, page );
                        }
                    });

                    //change plot's currency
                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'change', '.inv_change_currency', function(e) {
                        if( !loading ) {
                            var widget = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget');

                            var period = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find('.inv_change_period').find('.selectbox_value').data('value');
                            var currency = jQuery(this).find('.selectbox_value').data('value');
                            var page = 0;

                            widget.find('.total_stats').css( 'height', widget.find('.total_stats').height() ).html('<div class="ajax_bar_loading"></div>');
                            widget.find('.graph_wrapper').html('<div class="ajax_widget_loading"></div>');
                            loading = true;

                            build_graph( widget, period, currency, page );
                        }
                    });

                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'click', '.widget_next_wrapper:not(.disabled_period)', function(e) {
                        if( !loading ) {
                            var widget = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget');

                            var period = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find('.inv_change_period').find('.selectbox_value').data('value');
                            var currency = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find('.inv_change_currency').find('.selectbox_value').data('value');
                            var page = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find( '.graph_period' ).data( 'period_pagination' )*1 + 1;

                            widget.find('.total_stats').css( 'height', widget.find('.total_stats').height() ).html('<div class="ajax_bar_loading"></div>');
                            widget.find('.graph_wrapper').html('<div class="ajax_widget_loading"></div>');
                            loading = true;

                            build_graph( widget, period, currency, page );
                        }
                    });

                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'click', '.widget_prev_wrapper:not(.disabled_period)', function(e) {
                        if( !loading ) {
                            var widget = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget');

                            var period = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find('.inv_change_period').find('.selectbox_value').data('value');
                            var currency = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find('.inv_change_currency').find('.selectbox_value').data('value');
                            var page = jQuery(this).parents('.wpc_inv_statistic_dashboard_widget').find( '.graph_period' ).data( 'period_pagination' )*1 - 1;

                            widget.find('.total_stats').css( 'height', widget.find('.total_stats').height() ).html('<div class="ajax_bar_loading"></div>');
                            widget.find('.graph_wrapper').html('<div class="ajax_widget_loading"></div>');
                            loading = true;

                            build_graph( widget, period, currency, page );
                        }
                    });


                    //custom selectbox handlers
                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'click', '.widget_custom_selectbox', function(e) {
                        jQuery( '.widget_custom_selectbox' ).not( this ).removeClass( 'is_focus' );
                        jQuery( '.widget_custom_palette' ).removeClass( 'is_focus' );
                        jQuery(this).toggleClass( 'is_focus' );

                        e.stopPropagation();

                        jQuery( 'body' ).bind( 'click', function( event ) {
                            jQuery( '.widget_custom_selectbox' ).removeClass( 'is_focus' );
                            jQuery( 'body' ).unbind( event );
                        });
                    });

                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'click', '.widget_custom_selectbox ul li:not(.selected)', function(e) {
                        jQuery(this).parents( '.widget_custom_selectbox' ).find( '.selectbox_value' ).html( jQuery(this).data('value') ).data('value', jQuery(this).data('value') );
                        jQuery(this).parents( '.widget_custom_selectbox' ).find( 'li' ).removeClass( 'selected' );
                        jQuery(this).addClass( 'selected' );
                        jQuery( this ).parents( '.widget_custom_selectbox' ).trigger( 'change' );
                    });

                    <?php if( !empty( $graph_output ) ) { ?>
                        //buid plot
                        var line1 = <?php echo json_encode( $graph_output ); ?>;
                        plot1 = jQuery.jqplot( 'inv_graph', [line1], {
                            seriesDefaults:{
                              fill: true,
                              fillAndStroke: true,
                              fillAlpha: 0.8,
                              shadow: false
                            },
                            series:[
                                {
                                    label:'Income',
                                    showMarker:false,
                                    rendererOptions: {
                                        smooth: true
                                    }
                                }
                            ],
                            seriesColors:['#4bb2c5'],
                            axesDefaults: {
                                tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer ,
                                tickOptions: {
                                    fontSize: '10px'
                                },
                                labelOptions: {
                                    fontSize: '10px'
                                }
                            },
                            axes: {
                                xaxis: {
                                  pad: 0,
                                  min: '<?php echo ( isset( $graph_output[0][0] ) ) ? $graph_output[0][0] : 0 ?>',
                                  renderer: jQuery.jqplot.DateAxisRenderer,
                                  labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                                  tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
                                  tickOptions: {
                                      angle: -15,
                                      formatString:'%F',
                                      showGridline: false
                                  }
                                },
                                yaxis: {
                                  min: 0,
                                  label: '<?php echo __( 'Income', WPC_CLIENT_TEXT_DOMAIN ) . ' (' . $wpc_currency[$default_key]['code'] . ')' ?>',
                                  labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
                                  tickOptions:{
                                      formatString:'%.2f <?php echo $wpc_currency[$default_key]['code'] ?>',
                                      showGridline: false
                                  }
                                }
                            },
                            grid: {
                                background: 'rgba(57,57,57,0.0)',
                                drawBorder: false,
                                shadow: false,
                                gridLineColor: '#666666',
                                gridLineWidth: 1
                            },
                            highlighter: {
                                show: true,
                                sizeAdjust: 7.5
                            }
                        });

                        //add responsive for plot
                        jQuery( window ).bind('resize', function(event, ui) {
                            if( jQuery( '.wpc_inv_statistic_dashboard_widget:not(.collapsed) #inv_graph' ).length ) {
                                 plot1.replot({
                                    resetAxes:true,
                                    axes: {
                                        xaxis: {
                                          pad: 0,
                                          min: '<?php echo ( isset( $graph_output[0][0] ) ) ? $graph_output[0][0] : 0 ?>'
                                        },
                                        yaxis: {
                                          min: 0
                                        }
                                    }
                                });
                            }
                        });
                    <?php } ?>

                    //reset all when widget reloads
                    jQuery( '.wpc_inv_statistic_dashboard_widget' ).on( 'click', '.widget_reload', function(e) {
                        //destroy all handlers for widget
                        <?php if( !empty( $graph_output ) ) { ?>
                            plot1.destroy();
                            jQuery( window ).unbind('resize');
                        <?php } ?>
                        jQuery( '.wpc_inv_statistic_dashboard_widget' ).off( 'click', "**" );
                        jQuery( '.wpc_inv_statistic_dashboard_widget' ).off( 'change', "**" );
                    });
                });
            </script>

            <!--  Invoices Widget  -->
            <div class="tiles <?php echo $widget_options['color'] ?>">
                <div class="tile_body">
                    <div class="widget_header">
                        <div class="widget_control">

                            <div class="widget_custom_selectbox inv_change_period" style="width:80px;">
                                <a href="javascript:;" class="widget_all" title="<?php _e( 'Change Period', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    <span class="widget_calendar" style="float: left;margin:4px 0px;" title="<?php _e( 'Select Date', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                                    <span class="selectbox_value" data-value="month"><?php _e( 'Month', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                </a>
                                <ul>
                                    <li data-value="day"><?php _e( 'Day', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-value="week"><?php _e( 'Week', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-value="month" class="selected"><?php _e( 'Month', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-value="quarter"><?php _e( 'Quarter', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-value="year"><?php _e( 'Year', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                </ul>
                            </div>

                            <div class="widget_custom_selectbox inv_change_currency"  style="width:45px;">
                                <a href="javascript:;" class="widget_all" title="<?php _e( 'Change Currency', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    <span class="selectbox_value" data-value="<?php echo $wpc_currency[$default_key]['code']; ?>"><?php echo $wpc_currency[$default_key]['code']; ?></span>
                                </a>
                                <ul>
                                    <?php foreach( $wpc_currency as $cur_id=>$currency ) {
                                        if( isset( $all_currencies ) && in_array( $currency['code'], $all_currencies ) ) { ?>
                                            <li data-value="<?php echo $currency['code'] ?>" class="<?php if( isset( $default_key ) && $cur_id == $default_key ) { ?>selected<?php } ?>"><?php echo $currency['code'] ?></li>
                                        <?php }
                                    } ?>
                                </ul>
                            </div>

                            <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_inv_statistic_dashboard_widget' ) ?></div>
                        </div>
                        <div class="tile_title"><?php _e( 'Invoices', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                    </div>
                    <div class="widget_content statistic">
                        <div class="total_stats">
                            <div class="widget_stats">
                                <div class="wrapper">
                                    <span class="item_title" style="font-size: 15px;"><?php _e( 'Total Income', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    <span class="item_count" style="font-size: 17px;"><?php echo WPC()->get_price_string( $total_paid_invoices_amount, $default_key ) ?></span>
                                </div>
                            </div>
                            <div class="widget_stats">
                                <div class="wrapper">
                                    <span class="item_title" style="font-size: 13px;"><?php _e( 'Total Outstanding', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    <span class="item_count" style="font-size: 15px;"><?php echo WPC()->get_price_string( $total_outstanding_invoices_amount['sum_amount'], $default_key ) ?></span>
                                </div>
                            </div>
                            <div class="widget_stats">
                                <div class="wrapper">
                                    <span class="item_title" style="font-size: 13px;"><?php _e( 'Total due', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    <span class="item_count" style="font-size: 15px;"><?php echo WPC()->get_price_string( $total_past_due_invoices_amount['sum_amount'], $default_key ) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <?php if( ( isset( $graph_output ) && !empty( $graph_output ) ) || ( isset( $previous_data ) && !empty( $previous_data ) ) ) { ?>
                            <div class="tile_graph" style="background-color: #e9ecee;height:250px;">
                                <div class="widget_prev_wrapper <?php if( !( isset( $previous_data ) && !empty( $previous_data ) ) ) { ?>disabled_period<?php } ?>" style="float: left;" title="<?php _e( 'Previous Period', WPC_CLIENT_TEXT_DOMAIN ) ?>"><span class="control_button widget_prev" style="position:absolute;top:50%;left:2px;margin-top:-5px;"></span></div>
                                <div class="graph_wrapper">
                                    <div id="inv_graph"></div>
                                    <div class="graph_period" data-period_pagination="0"><?php echo date( 'F Y', mktime( 0, 0, 0, date('n'), date('j'), date('Y') ) ) . ' ' . __( 'year', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                                </div>
                                <div class="widget_next_wrapper disabled_period" style="float: right;" title="<?php _e( 'Next Period', WPC_CLIENT_TEXT_DOMAIN ) ?>"><span class="control_button widget_next" style="position:absolute;top:50%;right:2px;margin-top:-5px;"></span></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php $widget = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }
            echo $widget;
            exit;
        }


        /**
        * AJAX delete late fee
        */
        function ajax_inv_delete_late_fee() {
            if ( !empty( $_POST['inv_id'] ) && ( $id = (int)$_POST['inv_id'] ) ) {
                $late_fee = get_post_meta( $id, 'wpc_inv_added_late_fee', true );
                $total = get_post_meta( $id, 'wpc_inv_total', true );
                if ( (float)$late_fee && (float)$total ) {
                    $total -= $late_fee;
                }

                update_post_meta( $id, 'wpc_inv_total', $total );
                delete_post_meta( $id, 'wpc_inv_added_late_fee' );
                echo $total;
            }
            exit;
        }


        /**
        * AJAX update assigned clients\cicles
        */
        function update_assigned_data() {

             $current_page = $_POST['current_page'];
             $datatype = $_POST['data_type'];

             switch( $current_page ) {

                case 'wpclients_invoicingrepeat_invoices':
                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'repeat_invoice', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'repeat_invoice', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                        break;
                    }
                    break;

             }

        }


        /**
        * AJAX - Change invoice status on Invoices Page
        **/
        function ajax_inv_change_status() {
            global $wpdb;

            $all_statuses = array( 'open', 'sent', 'void', 'refunded', 'paid', 'partial', 'draft', 'pending', 'inprocess' ) ;

            if( isset( $_POST['id'] ) && 0 < $_POST['id'] && isset( $_POST['new_status'] ) && in_array( $_POST['new_status'], $all_statuses ) ) {
                $wpdb->update( $wpdb->posts, array( 'post_status' => $_POST['new_status'] ), array( 'ID' => $_POST['id'] ), array( '%s' ), array( '%d' ) ) ;
            }
            exit;
        }


        /**
        * AJAX - Change order for custom fields
        **/
        function ajax_change_inv_custom_field_order() {

            if ( isset( $_REQUEST['new_order'] ) ) {
                $cf_names =  explode( ',', str_replace( 'field_', '', $_REQUEST['new_order'] ) );

                if ( is_array( $cf_names ) && 0 < count( $cf_names ) ) {
                    $settings_name = ( isset( $_REQUEST['type'] ) && 'inv' == $_REQUEST['type'] )
                            ? 'invoice_cf' : 'inv_custom_fields';
                    $wpc_custom_fields = WPC()->get_settings( $settings_name );

                    $new_wpc_custom_fields = array();
                    foreach( $cf_names  as $cf_name ) {
                        $new_wpc_custom_fields[$cf_name] = $wpc_custom_fields[$cf_name];
                    }

                    WPC()->settings()->update( $new_wpc_custom_fields, $settings_name );
                }
                die( 'ok' );
            }
            exit;
        }

        function ajax_inv_save_new_item() {
            $item = array();
            $item['name']           = ( isset( $_POST['name'] ) ) ? stripslashes( $_POST['name'] ) : '';
            $item['description']    = ( isset( $_POST['description'] ) ) ? stripslashes( $_POST['description'] ) : '';
            $item['rate']           = ( isset( $_POST['rate'] ) ) ? $_POST['rate'] : '';
            $error = $this->save_items( $item );
            echo $error;
            exit;
        }

        function ajax_inv_change_currency() {
            $key = $_REQUEST['selected_curr'];
            $wpc_currency = WPC()->get_settings( 'currency' );

            echo json_encode( array( 'symbol' => $wpc_currency[ $key ]['symbol'], 'align' => $wpc_currency[ $key ]['align'] ) );
            exit;
        }

        function ajax_inv_all_items() {
            $all_items = $this->get_items( true, 'ORDER BY `name` ASC' );

            if( !empty( $all_items ) ) {

                $custom_fields = WPC()->get_settings( 'inv_custom_fields' );

                ob_start(); ?>

                <form method="post" name="wpc_add_item" id="wpc_add_item">
                    <div id="block_all_items">
                        <table cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <td width="50px"><input type="checkbox" id="check_all_preset_items" /></td>
                                    <td width="410px" style="padding-left: 10px;"><?php _e( 'Name', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                    <td width="90px" style="padding-left: 10px;"><?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $all_items as $item ) {
                                    $item_cf = maybe_unserialize( $item['data'] );

                                    $data_cf = '';
                                    foreach ( $custom_fields as $key => $val ) {
                                        if ( isset( $item_cf[ $key ] ) ) {
                                            $data_cf .= ' data-' . $key . '="' . $item_cf[ $key ] . '"';
                                        }
                                    }

                                    $name = htmlspecialchars( $item['name'] ); ?>
                                    <tr>
                                        <td><input type="checkbox" class="item_checkbox" value="<?php echo $item['id'] ?>" /></td>
                                        <td><span data-info="<?php echo $name ?>"<?php echo $data_cf ?>
                                                  title="<?php echo htmlspecialchars( $item['description'] ) ?>">
                                                      <?php echo $name ?></span></td>
                                        <td><?php echo $item['rate'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <br />
                    <div style="clear: both; text-align: center;">
                        <input type="button" class='button-primary' id="button_add_item" value="<?php _e( 'Add Preset Items', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                </form>

                <?php $content = ob_get_contents();
                if (ob_get_length()) {
                    ob_end_clean();
                }

                echo json_encode(array(
                    'title' => __('Preset Items', WPC_CLIENT_TEXT_DOMAIN),
                    'content' => $content
                ));
                exit;
            }

            echo json_encode( array(
                'title'     => __( 'Preset Items', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => __( 'Items not found', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        function ajax_inv_all_taxes() {
            $all_taxes = $this->get_taxes();

            if( !empty( $all_taxes ) ) {
                ob_start(); ?>

                <form method="post" name="wpc_add_tax" id="wpc_add_tax">
                    <div id="block_all_taxes">
                        <table cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <td width="50px"></td>
                                    <td width="210px"><?php _e('Name', WPC_CLIENT_TEXT_DOMAIN) ?></td>
                                    <td width="200px"><?php _e('Description', WPC_CLIENT_TEXT_DOMAIN) ?></td>
                                    <td width="90px"><?php _e('Rate', WPC_CLIENT_TEXT_DOMAIN) ?></td>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $all_taxes as $tax ) {
                                $name = ( 20 >= strlen( $tax['name'] ) ) ? $tax['name'] : substr( $tax['name'], 0, 17 ) . '...' ;
                                $description = ( 60 >= strlen( $tax['description'] ) ) ? $tax['description'] : substr( $tax['description'], 0, 57 ) . '...' ; ?>
                                <tr>
                                    <td><input type="checkbox" class="tax_checkbox" value="<?php echo $tax['id'] ?>" /></td>
                                    <td><span data-info="<?php echo htmlspecialchars( $tax['name'] ) ?>" title="<?php echo htmlspecialchars( $tax['name'] ) ?>"><?php echo htmlspecialchars( $name ) ?></span></td>
                                    <td><span data-info="<?php echo htmlspecialchars( $tax['description'] ) ?>"><?php echo htmlspecialchars( $description ) ?></span></td>
                                    <td><span><?php echo $tax['rate'] ?></span></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <br/>

                    <div style="clear: both; text-align: center;">
                        <input type="button" class='button-primary' id="button_add_tax"
                               value="<?php _e( 'Add Preset Taxes', WPC_CLIENT_TEXT_DOMAIN) ?>"/>
                    </div>
                </form>

                <?php $content = ob_get_contents();
                if (ob_get_length()) {
                    ob_end_clean();
                }

                echo json_encode(array(
                    'title' => __('Preset Taxes', WPC_CLIENT_TEXT_DOMAIN),
                    'content' => $content
                ));
                exit;
            }

            echo json_encode( array(
                'title'     => __( 'Preset Taxes', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => __( 'Taxes not found', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }


    //end class
    }

}
