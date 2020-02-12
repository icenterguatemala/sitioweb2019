<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wp_query;

$request_items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_invoicing_items WHERE use_r_est = 1", ARRAY_A );


if ( !empty( $wp_query->query_vars['page'] ) ) {
    $r_est_id = $wp_query->query_vars['page'];

    $isset_post = $wpdb->get_row( $wpdb->prepare( "SELECT p.ID as id, coa.assign_id as client_id, coa.object_type as object "
        . "FROM {$wpdb->posts} p "
        . "LEFT JOIN {$wpdb->prefix}wpc_client_objects_assigns coa ON ( p.ID = coa.object_id AND coa.assign_type = 'client' ) "
        . "WHERE p.ID = '%s'"
        , $r_est_id
        ), ARRAY_A );

    $id_post = ( !empty( $isset_post['id'] ) ) ? $isset_post['id'] : 0;

    if ( !empty( $id_post ) ) {
        if ( empty( $isset_post['client_id'] ) || $isset_post['client_id'] != $client_id ) {
            WPC()->redirect( WPC()->get_hub_link() );
        }

        if ( 'request_estimate' != $isset_post['object'] ) {
            $no_exist = $_GET['message'] = 'con_yet';
        } else {
            $data = $this->get_data( $id_post ) ;
        }
    } else {
        $no_exist = $_GET['message'] = 'd';
    }

}

$all_num_items = 0;

$can_edit = ( empty( $data['status'] ) || 'waiting_client' == $data['status'] ) ? true : false;
$accept = ( !empty( $data['status'] ) && 'waiting_client' == $data['status'] ) ? true : false;

$readonly = ( !$can_edit || $accept ) ? ' readonly="readonly"' : '';
$selected_curr = isset ( $data['currency'] ) ? $data['currency'] : '' ;

if( isset( $_GET['message'] ) ) {
    switch( $_GET['message'] ) {
        case 'rs':
             echo '<div class="wpc_notice wpc_apply">'
                . __( 'Estimate Request Successfully Sent.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
        case 'cs':
             echo '<div class="wpc_notice wpc_apply">'
                . __( 'Comment Successfully Sent.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
        case 'con':
             echo '<div class="wpc_notice wpc_apply">'
                . __( 'Convert Estimate Request Successfully. ', WPC_CLIENT_TEXT_DOMAIN )
                . '<a href="' . WPC()->get_hub_link() . '">'
                . __( 'Return to HUB', WPC_CLIENT_TEXT_DOMAIN )
                . '</a></div>';
            break;
        case 'con_yet':
             echo '<div class="wpc_notice wpc_apply">'
                . __( 'This Estimate Request was Converted. ', WPC_CLIENT_TEXT_DOMAIN )
                . '<a href="' . WPC()->get_hub_link() . '">'
                . __( 'Return to HUB', WPC_CLIENT_TEXT_DOMAIN )
                . '</a></div>';
            break;
        case 'd':
             echo '<div class="wpc_notice wpc_apply">'
                . __( 'This Estimate Request was Deleted.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
        case 'ae':
             echo '<div class="wpc_notice wpc_error">'
                . __( 'Error of Action.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
        case 'ce':
             echo '<div class="wpc_notice wpc_error">'
                . __( 'Comment is Empty.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
        case 'te':
             echo '<div class="wpc_notice wpc_error">'
                . __( 'Title is Empty.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
        case 'ir':
             echo '<div class="wpc_notice wpc_error">'
                . __( 'Items are Required.', WPC_CLIENT_TEXT_DOMAIN )
                . '</div>';
            break;
    } ?>
    <div class="wpc_clear"></div>
    <?php
}

if ( isset( $no_exist ) ) {
    return '';
}

$required = '<span style="color:red;" title="' . __( 'This field is marked as required.',WPC_CLIENT_TEXT_DOMAIN  ) . '">*</span>';
?>
<form method="POST" action="" id="wpc_inv_request_estimate" class="wpc_form">
    <input type="hidden" id="wpc_inv_action" name="wpc_data[action]" value="comment">

    <?php if ( empty( $r_est_id ) ) { ?>
        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?>" for="r_est_title">
                    <?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <?php echo $required ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <?php $title = ( !empty( $data['title'] ) ) ? $data['title'] : sprintf( __( '%s Estimate Request', WPC_CLIENT_TEXT_DOMAIN ), date( "Y-m-d" ) ); ?>
                <input type="text" name="wpc_data[title]" id="r_est_title" data-required_field="1" value="<?php echo $title ?>" />
                <div class="wpc_field_validation">
                    <span class="wpc_field_required"><?php _e( 'Request title is required', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                </div>
            </div>
        </div>
    <?php } else {
        $title = !empty( $data['title'] ) ? $data['title'] : ''; ?>
        <h3><?php echo $title ?></h3>
        <input type="hidden" id="r_est_title" value="<?php echo $title ?>" />
    <?php } ?>

    <?php
    $items_required = false;
    if ( empty( $r_est_id ) ) { ?>
        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Items', WPC_CLIENT_TEXT_DOMAIN ) ?>" for="wpc_inv_table_items">
                    <?php _e( 'Items', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <?php
                        $wpc_invoicing = WPC()->get_settings( 'invoicing' );
                        $items_required = !isset( $wpc_invoicing['items_required'] ) || 'no' !== $wpc_invoicing['items_required'];
                        if ( $items_required ) {
                            echo $required;
                        }
                    ?>
                </label>
            </div>
            <div class="wpc_form_field">
    <?php } ?>

    <div id="added_items" style="float:left;width: 100%;">
        <table id="wpc_inv_table_items" <?php echo $items_required ? 'data-required_items="1"' : '' ?>>
            <thead>
                <tr>
                    <th class="wpc_inv_actions_th"></th>
                    <th class="wpc_inv_name_th"><?php _e( 'Name', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_description_th"><?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_quantity_th"><?php _e( 'Qty.', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_rate_th"><?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_total_th"><?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rate_capacity          = $this->get_rate_capacity();
                $thousands_separator    = $this->get_thousands_separator();

                $items = ( !empty( $data['items'] ) ) ? unserialize( $data['items'] ) : array();
                $discounts = ( !empty( $data['discounts'] ) ) ? unserialize( $data['discounts'] ) : array();
                $taxes = ( !empty( $data['taxes'] ) ) ? unserialize( $data['taxes'] ) : array();
                $sub_total = ( isset( $data['sub_total'] ) ) ? $data['sub_total'] : '0';
                $total_discount = ( isset( $data['total_discount'] ) ) ? $data['total_discount'] : '0';

                foreach ( $items as $key => $value ) {
                    if (!empty($value['deleted'])) {
                        unset($items[ $key ]);
                    }
                }
                array_unshift( $items, array( "name"=> "", "description" => "", "quantity" => 1, "price" => ""  ) );

                foreach ( $items as $item ) {
                    if ( '' != $item['name'] ) {
                        $all_num_items ++;
                        $num_items = $all_num_items;
                    } else {
                        $num_items = '{num_items}';
                    }
                    $item['price'] = !empty( $item['price'] ) ? $item['price'] : 0 ;
                    ?>
                    <tr class="wpc_inv_item_row <?php echo ( '' == $item['name'] ) ? 'wpc_inv_hidden' : '' ?>" valign="top">
                        <td class="wpc_inv_delete_row <?php if ( $can_edit && !$readonly ) { ?>row_del<?php } ?>">
                            <span></span>
                            <input type="hidden" id="item_id<?php echo $num_items ?>" name="wpc_data[items][<?php echo $num_items ?>][item_id]" value="old_<?php echo $num_items ?>" />
                        </td>
                        <td id="item_name<?php echo $num_items ?>">
                            <?php echo $item['name'] ?>
                            <?php if ( !( $can_edit && !$readonly ) ) { ?>
                                <input type="hidden" id="item_id<?php echo $num_items ?>" name="wpc_data[items][<?php echo $num_items ?>][item_id]" value="old_<?php echo $num_items ?>" />
                            <?php } ?>
                        </td>
                        <td id="item_description<?php echo $num_items ?>"><?php echo isset( $item['description'] ) ? nl2br( $item['description'] ) : '' ?></td>
                        <td class="wpc_inv_item_quantity">
                            <input type="number" data-number="<?php echo $num_items ?>" id="item_quantity<?php echo $num_items ?>" name="wpc_data[items][<?php echo $num_items ?>][quantity]" value="<?php echo $item['quantity'] ?>" <?php if ( !$can_edit || $accept ) { ?>readonly<?php } ?> size="1" min="1" />
                        </td>
                        <td id="item_price<?php echo $num_items ?>" data-number="<?php echo $num_items ?>" class="item_price"><?php echo number_format( $item['price'], $rate_capacity, '.', $thousands_separator ) ?></td>
                        <td align="right">
                            <span class="item_total" id="item_total<?php echo $num_items ?>" data-total="<?php echo number_format( round( $item['price'] * $item['quantity'], 2 ), $rate_capacity, '.', '' ) ?>">
                               <?php echo number_format( round( $item['price'] * $item['quantity'], 2 ), $rate_capacity, '.', $thousands_separator ) ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
                <tr class="wpc_inv_item_row wpc_inv_no_items <?php if ( count( $items ) > 1 ) { ?>wpc_inv_hidden<?php } ?>">
                    <td colspan="5" style="text-align: center;"><?php _e( 'No Items', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                </tr>
            </tbody>
        </table>
        <table id="wpc_inv_table_discounts" <?php echo ( empty( $discounts ) ) ? 'style="display: none;"' : '' ?>>
            <thead>
                <tr>
                    <th class="wpc_inv_name_th"><?php _e( 'Discounts', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_description_th"></th>
                    <th class="wpc_inv_quantity_th"></th>
                    <th class="wpc_inv_rate_th"></th>
                    <th class="wpc_inv_total_th"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $discounts as $disc ) {
                    $num_items ++;
                    $total_discont = ( 'amount' == $disc['type'] ) ? number_format( $disc['rate'], $rate_capacity, '.', '' ) : number_format( round( $sub_total * $disc['rate'] / 100 , 2 ), $rate_capacity, '.', '' );
                    $total_discont_html = ( 'amount' == $disc['type'] ) ? number_format( $disc['rate'], $rate_capacity, '.', $thousands_separator ) : number_format( round( $sub_total * $disc['rate'] / 100 , 2 ), $rate_capacity, '.', $thousands_separator ); ?>

                    <tr class="wpc_inv_discount_row" valign="top">
                        <td class="wpc_inv_name_td">
                            <?php echo $disc['name'] ?><br />
                            (<?php echo ( 'amount' == $disc['type'] ) ? __( 'Amount Discount', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Percent Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>)
                        </td>
                        <td class="wpc_inv_description_td" colspan="2"><?php echo isset( $disc['description'] ) ? nl2br( $disc['description'] ): '' ?></td>
                        <td class="wpc_inv_rate_td">
                            <?php if ( 'percent' == $disc['type'] ) {
                                echo $disc['rate'] . '%';
                            } else {
                                echo number_format( $disc['rate'], $rate_capacity, '.', $thousands_separator );
                            } ?>
                        </td>
                        <td class="wpc_inv_total_td discount_total"><?php echo $total_discont_html ?></td>
                     </tr>
                <?php } ?>
            </tbody>
        </table>
        <table id="wpc_inv_table_taxes" <?php echo ( empty( $taxes ) ) ? 'style="display: none;"' : '' ?>>
            <thead>
                <tr>
                    <th class="wpc_inv_name_th"><?php _e( 'Taxes', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_description_th"></th>
                    <th class="wpc_inv_quantity_th"></th>
                    <th class="wpc_inv_rate_th"></th>
                    <th class="wpc_inv_total_th"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $taxes as $key=>$tax ) {
                    $total_tax = ( 'before' == $tax['type'] ) ? number_format( round( $sub_total * $tax['rate'] / 100 , 2 ), $rate_capacity, '.', '' ) : number_format( round( ( $sub_total - $total_discount ) * $tax['rate'] / 100 , 2 ), $rate_capacity, '.', '' );
                    $total_tax_html = ( 'before' == $tax['type'] ) ? number_format( round( $sub_total  * $tax['rate'] / 100 , 2 ), $rate_capacity, '.', $thousands_separator ) : number_format( round( ( $sub_total - $total_discount ) * $tax['rate'] / 100 , 2 ), $rate_capacity, '.', $thousands_separator ); ?>

                    <tr class="wpc_inv_tax_row" valign="top">
                        <td class="wpc_inv_name_td" id="tax_type<?php echo $key ?>" data-type="<?php echo $tax['type'] ?>">
                            <?php echo $tax['name'] ?><br />
                            (<?php echo ( 'before' == $tax['type'] ) ?  __( 'Before Discount', WPC_CLIENT_TEXT_DOMAIN ) : __( 'After Discount', WPC_CLIENT_TEXT_DOMAIN ) ?>)
                        </td>
                        <td class="wpc_inv_description_td" colspan="2"><?php echo isset( $tax['description'] ) ? nl2br( $tax['description'] ): '' ?></td>
                        <td class="wpc_inv_rate_td"><span id="tax_rate<?php echo $key ?>"><?php echo $tax['rate'] ?></span>%</td>
                        <td class="wpc_inv_total_td tax_total" data-number="<?php echo $key ?>">&nbsp;<?php echo $total_tax_html ?></td>
                     </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php $price_null = $this->get_currency( 0, true, $selected_curr ); ?>
        <table id="wpc_inv_table_total_all">
            <thead>
                <tr>
                    <th style="width: calc( 70% - 180px );"></th>
                    <th style="width: 30%;"></th>
                    <th style="width: 60px;"></th>
                    <th style="width: 60px;"><?php _e( 'Total:', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    <th class="wpc_inv_total_th">
                        <span id="total_all">
                            <?php echo ( isset( $data['total'] ) ) ? $this->get_currency( $data['total'], true, $selected_curr ) : $price_null ?>
                            <span class="real_amount" style="display: none;"><?php echo ( isset( $data['total'] ) ) ? $data['total']  : $price_null ?></span>
                        </span>
                    </th>
                </tr>
            </thead>
        </table>
        <div class="clear"></div>
    </div>

    <?php if( count( $request_items ) && $can_edit ) { ?>
        <div id="wpc_inv_block_add_item" style="margin-top:7px;float:left;width:100%;<?php echo ( $accept ) ? 'display:none;' : '' ?>">
            <label style="float:left;width:calc( 80% - 20px );margin: 0 20px 0 0;padding:0;">
                <select name="request_item" id="request_item">
                    <option value=""><?php _e( 'Select Item', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php foreach( $request_items as $key => $val ) {
                        $price = $this->get_currency( $val['rate'] ); ?>
                        <option value="<?php echo $val['id']; ?>"
                                title="<?php echo $val['description']; ?>"
                                data-name="<?php echo $val['name']; ?>"
                                data-rate="<?php echo $val['rate']; ?>">
                            <?php echo $val['name']; ?> (<?php echo $price; ?>)
                        </option>
                    <?php } ?>
                </select>
            </label>
            <input type="button" style="float:left;width:20%;margin: 0;" class="wpc_button" id="wpc_inv_add_item" value="<?php _e( "Add Item", WPC_CLIENT_TEXT_DOMAIN ); ?>">
        </div>
    <?php }

    if ( empty( $r_est_id ) ) { ?>
            </div>
        </div>
    <?php }

    if( $can_edit && !empty( $id_post ) ) { ?>
        <div class="wpc_inv_block_accept" style="margin-top:7px;float:left;width:100%;">
            <input type="submit" class="wpc_submit" id="wpc_inv_accept" value="<?php _e( "Accept", WPC_CLIENT_TEXT_DOMAIN ) ?>">&nbsp;
            <input type="button" class="wpc_button" id="wpc_inv_edit" value="<?php _e( "Edit", WPC_CLIENT_TEXT_DOMAIN ) ?>">&nbsp;
            <input type="button" class="wpc_button" id="wpc_inv_return_to_HUB" value="<?php _e( "Return to HUB", WPC_CLIENT_TEXT_DOMAIN ) ?>">&nbsp;
        </div>
    <?php } ?>


    <?php if ( empty( $r_est_id ) ) { ?>
        <div class="wpc_form_line">
            <div class="wpc_form_label">
                <label data-title="<?php _e( 'Comment', WPC_CLIENT_TEXT_DOMAIN ) ?>" for="wpc_inv_message">
                    <?php _e( 'Comment', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </label>
            </div>
            <div class="wpc_form_field">
                <textarea name="wpc_data[wpc_inv_message]" id="wpc_inv_message" style="width: 100%;" rows="5"></textarea>
            </div>
        </div>
    <?php } else { ?>
        <div id="wpc_inv_add_comments" style="margin:20px 0 10px 0;float:left;width:100%;">
            <span><strong><?php _e( 'Comments:', WPC_CLIENT_TEXT_DOMAIN ) ?></strong></span><br>
            <?php $notes = ( !empty( $data['notes'] ) ) ? $data['notes'] : array();
            $show_textarea = ( $accept ) ? false : true;
            echo $this->get_table_request_notes( $notes, 'client', true, $id_post ); ?>
        </div>
    <?php } ?>

    <div class="wpc_form_line">
        <div class="wpc_form_label" <?php if ( !$can_edit || !empty( $id_post ) ) { ?>style="width:65px !important;"<?php } ?>>
            &nbsp;
        </div>
        <div class="wpc_form_field">
            <?php if ( !$can_edit ) { ?>
                <input type="submit" class="button-primary wpc_submit" id="wpc_inv_comment_send" value="<?php _e( "Send Comment", WPC_CLIENT_TEXT_DOMAIN ) ?>" />
            <?php } else {
                if ( !empty( $id_post ) ) { ?>
                    <div class="wpc_inv_block_accept">
                        <input type="submit" class="button-primary wpc_submit" id="wpc_inv_comment_send" value="<?php _e( "Send Comment", WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </div>
                    <div id="wpc_inv_block_cancel" style="display:none;">
                        <input type="submit" class="button-primary wpc_submit" id="wpc_inv_request_send" value="<?php _e( "Send Request", WPC_CLIENT_TEXT_DOMAIN ) ?>">&nbsp;
                        <input type="button" class="wpc_button" id="wpc_inv_cancel" value="<?php _e( "Cancel", WPC_CLIENT_TEXT_DOMAIN ) ?>">
                    </div>
                <?php } else { ?>
                    <input type="submit" class="button-primary wpc_submit" id="wpc_inv_request_send" value="<?php _e( "Send Request", WPC_CLIENT_TEXT_DOMAIN ) ?>" style="float: left;">
                    <div class="wpc_submit_info" style="float: left;margin-left:10px; line-height: 44px;"></div>
                <?php }
            } ?>
        </div>
    </div>
</form>

<script type="text/javascript">
    jQuery( document ).ready( function($) {
        var rate_capacity = '<?php echo $rate_capacity; ?>';
        var thousands_separator = '<?php echo $thousands_separator; ?>';

        function not_price(e) {
            if ( e.which === 44 ) {
                this.value += '.';
                return false;
            }
            if (!(e.which===8 || e.which===46 ||e.which===0 ||(e.which>47 && e.which<58))) return false;
        }

        var rate_capacity = '<?php echo $rate_capacity; ?>';
        var thousands_separator = '<?php echo $thousands_separator; ?>';

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

        function all_total() {
            var count_total = 0;
            var count_discount = 0;
            var count_tax = 0;

            jQuery( '.item_total' ).each( function() {
                count_total = count_total + parseFloat( jQuery(this).data('total' ), 10 );
            });
            if( isNaN( count_total ) ) count_total = 0;
            jQuery( '#total_all_items .amount' ).html( addSeparatorsNF( count_total.toFixed( rate_capacity ), '.', '.', thousands_separator ) );

            if( isNaN( count_total ) ) count_total = 0;

            jQuery( '.discount_total' ).each( function() {
                if ( 'percent' === jQuery('#discount_type' + jQuery(this).data('number') ).val() ) {
                    var discont;
                    discont = count_total * parseFloat( jQuery( '#discount_rate' + jQuery(this).data('number') ).val(), 10 ) / 100 ;
                    if( isNaN( discont ) ) discont = 0;
                    jQuery(this).html( addSeparatorsNF( discont.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
                }
                count_discount = count_discount + parseFloat( jQuery(this).text(), 10 );
            });

            if( isNaN( count_discount ) ) count_discount = 0;

            jQuery( '.tax_total' ).each( function() {

                var tax;
                var this_number = jQuery(this).data('number');

                if ( 'before' === jQuery('#tax_type' + this_number ).data('type') ) {

                    tax = count_total.toFixed( rate_capacity ) * parseFloat( jQuery( '#tax_rate' + this_number ).text(), 10 ) / 100 ;

                } else if ( 'after' === jQuery('#tax_type' + this_number ).data('type') ) {

                    tax = ( count_total.toFixed( rate_capacity ) - count_discount.toFixed( rate_capacity ) ) * parseFloat( jQuery( '#tax_rate' + this_number ).text(), 10 ) / 100 ;

                }
                if( isNaN( tax ) ) tax = 0;
                jQuery(this).text( tax.toFixed( rate_capacity ) );

                count_tax = count_tax + parseFloat( jQuery(this).text(), 10 );
            });

            if( isNaN( count_tax ) ) count_tax = 0;

            count_total = count_total - count_discount + count_tax;

            jQuery( '#total_all .amount' ).html( addSeparatorsNF( count_total.toFixed( rate_capacity ), '.', '.', thousands_separator ) );
            jQuery( '#total_all .real_amount' ).html( count_total.toFixed( rate_capacity ) );
        }

        all_total();

        var num_items = jQuery( '.wpc_inv_delete_row' ).length;
        num_items = num_items - 1;
        function add_items( item_id, item_name, item_description, item_rate ) {
            jQuery( '#added_items' ).css( 'display', 'block' );
            jQuery( '.wpc_inv_actions_th' ).addClass('row_del');
            jQuery('.wpc_inv_no_items').addClass('wpc_inv_hidden');

            num_items = num_items + 1;
            var nice_item_rate = addSeparatorsNF( item_rate, '.', '.', thousands_separator );

            var html = jQuery( '#wpc_inv_table_items tbody tr' ).first().clone().wrap('<p>').css( 'display', 'table-row' ).parent().html().replace( /\{num_items\}/g , num_items );
            jQuery( '#added_items #wpc_inv_table_items .wpc_inv_no_items' ).before( html );

            //jQuery( '#item_total' + num_items ).data( 'total', item_rate ).val( nice_item_rate );
            jQuery( '#item_price' + num_items ).text( nice_item_rate );
            jQuery( '#item_description' + num_items  ).text( item_description );
            jQuery( '#item_id' + num_items  ).val( item_id );
            jQuery( '#item_name' + num_items  ).text( item_name );

            jQuery( '#item_price' + num_items ).trigger( 'change' );
        }

        function recalculation() {
            var number;
            var total;

            number = jQuery(this).data('number' );

            total = parseFloat( jQuery( '#item_quantity' + number ).val(), 10) * parseFloat( jQuery( '#item_price' + number ).text().replace(thousands_separator, ''), 10);
            total = total.toFixed( rate_capacity );
            if( isNaN( total ) )
                total = 0;

            var html_total = addSeparatorsNF( total, '.', '.', thousands_separator );

            jQuery( '#item_total' + number ).data('total', total );
            jQuery( '#item_total' + number ).html( html_total );
            all_total();

            jQuery( ".tax_rate" ).each(function(){

                jQuery(this).change();

            });

            jQuery( ".discount_rate" ).each(function(){

                jQuery(this).change();

            });
        }

        //Add item
        jQuery( '#wpc_inv_add_item' ).click( function() {
            var errors = 0;

            if ( '' === jQuery( "#request_item" ).val() ) {
                jQuery( '#request_item' ).parent().attr( 'class', 'wpc_error' );
                errors = 1;
            } else {
                jQuery( '#request_item' ).parent().removeClass( 'wpc_error' );
            }

            if ( 0 === errors ) {
                var item_id = jQuery( '#request_item option:selected' ).val();
                var item_name = jQuery( '#request_item option:selected' ).data('name');
                var item_rate = parseFloat( jQuery( '#request_item option:selected' ).data('rate'), 10 ).toFixed( rate_capacity );
                var item_description = jQuery( '#request_item option:selected' ).attr('title');
                add_items( item_id, item_name, item_description, item_rate );

                triggerSubmit();
                recalculation();
            }


            return false;

        });


        function infoSubmit() {
            var html = '';
            var form = $( "#wpc_inv_request_estimate" );

            if( form.find('*[data-required_field="1"]').length > 0 ) {
                form.find('*[data-required_field="1"]').each(function () {
                    var label = form.find('label[for="' + jQuery(this).attr('id') + '"]').data('title');
                    if ( jQuery(this).val() == '' ) {
                        html = '<?php _e( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ) ?> "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                        return false;
                    }
                });
            }
            if( form.find('*[data-required_items="1"]').length > 0 ) {
                form.find('*[data-required_items="1"]').each(function () {
                    var label = form.find('label[for="' + jQuery(this).attr('id') + '"]').data('title');
                    if ( !jQuery(this).find('.wpc_inv_no_items.wpc_inv_hidden').length ) {
                        html = '<?php _e( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ) ?> "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                        return false;
                    }
                });
            }

            if( form.find('.wpc_submit_info').html() != html ) {
                form.find('.wpc_submit_info').html(html);
            }

            return '' === html;
        }

        jQuery( '#wpc_inv_request_estimate' ).on( 'click', '#wpc_inv_request_send', function() {
            var result;
            result = infoSubmit();
            if ( !result ) {
                return false;
            } else {
                jQuery( '#wpc_inv_action' ).val('request');
                jQuery( '#wpc_inv_request_estimate' ).submit();
            }
        });

        jQuery( '#wpc_inv_comment_send' ).click( function() {
            var errors = 0;
            if ( '' === jQuery( '#wpc_inv_message' ).val() ) {
                jQuery( '#wpc_inv_message' ).parent().parent().attr( 'class', 'wpc_error' );
                errors = 1;
            } else {
                jQuery( '#wpc_inv_message' ).parent().parent().removeClass( 'wpc_error' );
            }

            if ( errors )
                return false;
            else {
                jQuery( '#wpc_inv_action' ).val('comment');
                jQuery( '#wpc_inv_request_estimate' ).submit();
            }
        });

        jQuery( '#wpc_inv_accept' ).click( function() {
            jQuery( '#wpc_inv_action' ).val('accept');
            jQuery( '#wpc_inv_request_estimate' ).submit();
        });

        jQuery( '#wpc_inv_edit' ).click( function() {
            jQuery( '.wpc_inv_block_accept' ).css('display', 'none');
            jQuery( '#wpc_inv_block_cancel' ).css('display', 'block');
            jQuery( '#wpc_inv_client_comment' ).css('display', 'table-row');
            jQuery( '#wpc_inv_block_add_item' ).css('display', 'block');
            jQuery( '.wpc_inv_item_quantity input' ).attr('readonly', false);
            jQuery( '.wpc_inv_delete_row, .wpc_inv_actions_th' ).addClass('row_del');
        });

        jQuery( '#wpc_inv_cancel' ).click( function() {
            location.reload();
            return false;
        });

        jQuery( '#wpc_inv_return_to_HUB' ).click( function() {
            window.location = '<?php echo WPC()->get_hub_link(); ?>';
            return false;
        });

        //for delete item link
        jQuery( '#wpc_inv_table_items' ).on( 'click', '.row_del', function() {
            jQuery(this).parent().remove();
            if( jQuery( '.wpc_inv_delete_row' ).length == 1 ) {
                jQuery('.wpc_inv_actions_th').removeClass('row_del');
                jQuery('.wpc_inv_no_items').removeClass('wpc_inv_hidden');
            }

            triggerSubmit();
            recalculation();
        });

        jQuery('#added_items').on( 'change', '.item_price, .wpc_inv_item_quantity input', recalculation );
        jQuery('#added_items').on( 'keyup', '.item_price, .wpc_inv_item_quantity input', recalculation );
        jQuery('#added_items').on( 'keypress', '.item_price, .wpc_inv_item_quantity input', not_price );

        <?php if ( empty( $r_est_id ) ) { ?>
            var form = $( "#wpc_inv_request_estimate" );

            //input fields
            form.find('input').focusout( function() {
                //check field on required value
                var field = $(this).parents('.wpc_form_field');
                if( $(this).data('required_field') ) {

                    //another fields
                    if ( $(this).val() == '' ) {
                        //if field empty
                        showValidationMessage( field, 'required' );
                    } else {
                        //if field not empty
                        //check field content
                        hideValidationMessage( field );
                    }

                    triggerSubmit();
                }
            });

            form.find('input').change( function() {
                //check field on required value
                var field = $(this).parents('.wpc_form_field');
                if( $(this).data('required_field') ) {
                    triggerSubmit();
                }
            });

            form.on('keyup', '.wpc_form_field.wpc_validate_error input', function() {
                //check field on required value
                var field = $(this).parents('.wpc_form_field');

                if( $(this).data('required_field') && $(this).val() == '' ) {
                    //if field required and empty
                    showValidationMessage( field, 'required' );
                } else {
                    //if field not required or required and not empty
                    //check field content
                    hideValidationMessage( field );
                }

                triggerSubmit();
            });


            function showValidationMessage( field, type ) {
                field.find( '.wpc_field_validation' ).children().hide();
                field.find( '.wpc_field_' + type ).show();
                field.addClass( 'wpc_validate_error' );
            }

            function hideValidationMessage( field ) {
                field.find( '.wpc_field_validation' ).children().hide();
                field.removeClass( 'wpc_validate_error' );
            }


            function triggerSubmit() {
                var validated_fields;
                validated_fields = form.find('*[data-required_field="1"]').length;
                validated_fields += form.find('*[data-required_items="1"]').length;
                if( validated_fields > 0 ) {
                    var validated = 0;

                    form.find('*[data-required_field="1"]').each(function () {
                        if (jQuery(this).val() != '') {
                            validated++;
                        }
                    });
                    form.find('*[data-required_items="1"]').each(function () {
                        if ( form.find('.wpc_inv_no_items.wpc_inv_hidden').length ) {
                            validated++;
                        }
                    });
                    if( validated_fields === validated ) {
                        form.find('input[type="submit"]').prop('disabled',false).attr('disabled',false);
                    } else {
                        form.find('input[type="submit"]').prop('disabled',true).attr('disabled',true);
                    }
                } else {
                    form.find('input[type="submit"]').prop('disabled',false).attr('disabled',false);
                }

                infoSubmit();
            }
        <?php } else { ?>
            function triggerSubmit() {
            }
        <?php } ?>
    });
</script>