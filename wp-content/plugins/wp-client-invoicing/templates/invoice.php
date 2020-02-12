<?php
/**
 * Template Name: Invoicing: Invoice Content
 * Template Description: This template for build invoice content
 * Template Tags: Invoicing
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/invoicing/invoice.php.
 *
 * HOWEVER, on occasion WP-Client will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 	WP-Client
 */

//needs for translation
__( 'Invoicing: Invoice Content', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for build invoice content', WPC_CLIENT_TEXT_DOMAIN );
__( 'Invoicing', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<style type="text/css">
div.table {
    width: 100%;
    height:auto;
    overflow:auto;
    border-width: 0px;
}
div.tr {
    width: 100%;
    height:auto;
    overflow:auto;
    clear: both;
    border-width: 0;
    padding: 0 0 6px 0;
    min-height: 20px;
}
div.td {
    border:none !important;
    float: left;
    padding: 0 5px;
    width:50%;
}

table, tbody, tr {
    width: 100%;
}
table {
    table-layout: fixed;
    border-width: 0;
    border-spacing: 0;
    padding: 0;
    margin: 0;
    color: black;
}
table thead {
    font-weight: normal;
    background-color: #363636;
    color: #fff;
}
table td {
    border-width: 0px;
    vertical-align: top;
}
table thead td {
    padding: 6px 0 6px 3px;
}
table tbody td {
    padding: 2px 0 2px 4px;
}

.embedded_table {
    margin-top: 15px;
    border: 1px solid black;
}
.color_black {
    color: #000;
}    
</style>

<div style="font-family: WPCCyr;">

    <table width="100%" border="0" cellspacing="0" cellpadding="0" color="black">
        <tbody>
            <tr>
                <td width="45%" rowspan="3" style="padding-bottom:6px; vertical-align: top;">
                    <?php if ( isset( $business_logo_url ) ) { ?>
                        <img src="<?php echo $business_logo_url; ?>" width="290" height="110" style="box-shadow: 0 0 0 0; height: auto;" />
                    <?php } ?>
                </td>

                <td style="text-align: right; line-height:14px; text-transform: uppercase;">
                    <?php _e( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </td>
            </tr>

            <tr>
                <td valign="top" align="right" style="text-align: right; color: #757575; line-height:14px; font-weight: bold;">
                    <?php _e( 'Invoice', WPC_CLIENT_TEXT_DOMAIN ); ?># <?php echo $InvoiceNumber; ?>
                </td>
            </tr>

            <tr>
                <td class="color_black" style="text-align: right; line-height:14px; font-weight: bold;">
                    <?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    <br />
                    <span style="font-size: small;" ><?php echo $InvoiceTotal; ?></span>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <b class="color_black">{business_name}</b>
                    <font size="1" style="color: #757575;">
                        <br />
                        {business_address}
                        <br />
                        {business_mailing_address}
                        <br />
                        <?php _e( 'Website', WPC_CLIENT_TEXT_DOMAIN ) ?>: {business_website}
                        <br />
                        <?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>: {business_email}
                        <br />
                        <?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?>: {business_phone}
                        <br />
                        <?php _e( 'Fax', WPC_CLIENT_TEXT_DOMAIN ) ?>: {business_fax}
                        <br />
                    </font>
                </td>
            </tr>

            <tr>
                <td valign="bottom" style="vertical-align: bottom !important;">
                    <span style="color: #757575;"><?php _e( 'Bill To', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    <br />
                    <span class="color_black">{client_name}</span>
                </td>

                <td align="right">

                    <table cellspacing="0" cellpadding="5" bordercolor="#000000" id="date_informs" style="width: 100%; margin-bottom: 0;">
                        <tbody>
                            <tr>
                                <td align="right">
                                    <span style="color: #757575;"><?php _e( 'Invoice Date', WPC_CLIENT_TEXT_DOMAIN ); ?> :</span>
                                </td>

                                <td align="right">
                                    <span class="color_black"><?php echo $InvoiceDate; ?></span>
                                </td>
                            </tr>

                            <tr>
                                <td align="right">
                                    <span style="color: #757575;"><?php _e( 'Due Date', WPC_CLIENT_TEXT_DOMAIN ); ?> :</span>
                                </td>

                                <td align="right">
                                    <span class="color_black"><?php echo $DueDate; ?></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </td>
            </tr>
        </tbody>
    </table>
                                
    <div class="embedded_table">
        <table title="<?php _e( 'Items', WPC_CLIENT_TEXT_DOMAIN ); ?>">
            <thead>
                <tr>
                    <td style="width: <?php echo $widthItemName; ?>%;">
                        <?php _e( 'Item', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php if ( isset( $show_description ) ) { ?>
                            & <?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        <?php } ?>
                    </td>

                    <?php if( isset( $TitleCustomFields ) ) {
                        foreach ( $TitleCustomFields as $field ) { ?>
                            <td style="width: <?php echo $widthCF; ?>%;">
                                <?php echo $field; ?>
                            </td>
                        <?php }
                    } ?>

                    <td style="width: 6%;">
                        <?php _e( 'Qty', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td style="width: 10%;">
                        <?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td style="width: 10%;">
                        <?php _e( 'Amount', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>
                </tr>
            </thead>
        </table>

        <?php if ( isset( $items ) ) {
            foreach ( $items as $item ) { ?>

                <table>
                    <tbody>
                        <tr>
                            <td style="width: <?php echo $widthItemName; ?>%;">
                                <span><?php echo $item['ItemName']; ?></span>
                                <?php if ( isset( $show_description ) ) { ?>
                                    <br/>
                                    <span style="color: #757575;">
                                        <?php echo $item['ItemDescription']; ?>
                                    </span>
                                <?php } ?>
                            </td>

                            <?php if ( isset( $CustomFields ) ) {
                                foreach ( $CustomFields as $field ) { ?>
                                    <td style="width: <?php echo $widthCF; ?>%;" title="<?php echo $field; ?>">
                                        <?php echo $item[$field['slug']]; ?>
                                    </td>
                                <?php }
                            } ?>

                            <td style="width: 6%;" title="<?php _e( 'Qty', WPC_CLIENT_TEXT_DOMAIN ); ?>"><?php echo $item['ItemQuantity']; ?></td>

                            <td style="width: 10%;" title="<?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ); ?>"><?php echo $item['ItemRate']; ?></td>

                            <td style="width: 10%;" title="<?php _e( 'Amount', WPC_CLIENT_TEXT_DOMAIN ); ?>"><?php echo $item['ItemTotal']; ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php }
        } else { ?>

            <table>
                <tbody>
                    <tr height="20">
                        <td style="width: 100%;"></td>
                    </tr>
                </tbody>
            </table>

        <?php } ?>
    </div>

    <?php if ( isset( $discounts ) ) { ?>

        <div class="embedded_table">
            <table title="<?php _e( 'Discount', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                <thead>
                    <tr>
                        <td style="width: 70%;">
                            <?php _e( 'Discount Name & Description', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>

                        <td style="width: 10%;">
                            <?php _e( 'Type', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>

                        <td style="width: 10%;">
                            <?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>

                        <td style="width: 10%;">
                            <?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>
                    </tr>
                </thead>
            </table>

            <?php foreach ( $discounts as $discount ) { ?>

                <table>
                    <tbody>
                        <tr height="20" style="padding: 3px 0;">
                            <td style="padding-left: 5px; width: 70%">
                                <span><?php echo $discount['name']; ?></span>
                                <br />
                                <span style="color: #757575;">
                                    <?php echo $discount['description']; ?>
                                </span>
                            </td>

                            <td style="width: 10%;" title="<?php _e( 'Type', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                                <?php echo $discount['type']; ?>
                            </td>

                            <td style="width: 10%;" title="<?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                                <?php echo $discount['rate']; ?>
                            </td>

                            <td style="width: 10%;" title="<?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                                <?php echo $discount['total']; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

            <?php } ?>

        </div>
    <?php } ?>

    <?php if ( isset( $taxes ) ) { ?>

        <div class="embedded_table">
            <table title="<?php _e( 'Tax', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                <thead>
                    <tr bgcolor="#363636" height="20">
                        <td valign="bottom" style="font-weight: normal; padding:6px 0px 6px 3px; width: 67%;">
                            <?php _e( 'Tax Name & Description', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>

                        <td style="width: 17%; font-weight: normal;">
                            <?php _e( 'Type', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>

                        <td style="width: 6%; font-weight: normal;">
                            <?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>

                        <td style="width: 10%; font-weight: normal;">
                            <?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>
                    </tr>
                </thead>
            </table>

            <?php foreach ( $taxes as $tax ) { ?>

                <table>
                    <tbody>
                        <tr>
                            <td style="width: 67%;">
                                <span><?php echo $tax['name']; ?></span>
                                <br>
                                <span style="color: #757575;">
                                    <?php echo $tax['description']; ?>
                                </span>
                            </td>

                            <td class="td" style="width: 17%;" title="<?php _e( 'Type', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                                <?php echo $tax['type']; ?>
                            </td>

                            <td style="width: 6%;" title="<?php _e( 'Rate', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                                <?php echo $tax['rate']; ?>%
                            </td>

                            <td style="width: 10%;" title="<?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                                <?php echo $tax['total']; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

            <?php } ?>

        </div>

    <?php } ?>

    <table width="100%" border="0" cellspacing="0" cellpadding="5" id="sub_block" style="margin-top: 20px;">
        <tbody>
            <tr>
                <td width="60%">&nbsp;</td>

                <td width="20%" align="right">
                    <?php _e( 'Sub Total', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </td>

                <td width="20%" align="right">
                    <?php echo $InvoiceSubTotal; ?>
                </td>
            </tr>

            <?php if ( $IsTotalDiscount ) { ?>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right">
                        <?php _e( 'Discount', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right">
                        <?php echo $TotalDiscount; ?>
                    </td>
                </tr>

            <?php } ?>

            <?php if ( $IsTotalTax ) { ?>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right">
                        <?php _e( 'Tax', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right">
                        <?php echo $TotalTax; ?>
                    </td>
                </tr>

            <?php } ?>

            <?php if ( $ShowVAT ) { ?>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right">
                        <?php _e( 'Total Net', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right">
                        <?php echo $TotalNet; ?>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right">
                        <?php _e( 'VAT', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right">
                        <?php echo $TotalVAT; ?>
                    </td>
                </tr>

            <?php } ?>

            <?php if ( $IsLateFee ) { ?>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right">
                        <?php _e( 'Late Fee', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right">
                        <?php echo $LateFee; ?>
                    </td>
                </tr>

            <?php } ?>

            <tr>
                <td>&nbsp;</td>

                <td align="right" style="border-top:1px solid black; font-weight: bold;">
                    <?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </td>

                <td align="right" style="border-top:1px solid black; font-weight: bold;">
                    <?php echo $InvoiceTotal; ?>
                </td>
            </tr>

            <?php if ( isset( $TotalRemaining ) ) { ?>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right" style="">
                        <?php _e( 'Payment Made', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right" style="">
                        <?php echo $PaymentMade; ?>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right" style="">
                        <?php _e( 'Payment Date', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right" style="">
                        <?php echo $PaymentDate; ?>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>

                    <td align="right" style="">
                        <?php _e( 'Total Remaining', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right" style="">
                        <?php echo $TotalRemaining; ?>
                    </td>
                </tr>

            <?php } ?>

            <?php if ( $payment_method ) { ?>
                <tr>
                    <td>&nbsp;</td>

                    <td align="right" style="">
                       <?php _e( 'Payment Method', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>

                    <td align="right" style="">
                        <?php echo $payment_method; ?>
                    </td>
                </tr>
            <?php } ?>

        </tbody>
    </table>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" color="black">
        <tbody>
            <tr>
                <td colspan="2" valign="top">
                    <span style="color: #757575"><?php _e( 'Notes', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    <br />
                    <br />
                    <font class="color_black">
                        <?php echo $Notes; ?>
                    </font>
                </td>
            </tr>

            <?php if ( isset( $TermsAndCondition ) ) { ?>

                <tr>
                    <td colspan="2" style="padding-top:20px;">
                        <span style="background-color: rgb(255, 255, 255);">
                            <?php echo $TermsAndCondition; ?>
                        </span>
                    </td>
                </tr>

            <?php } ?>

        </tbody>
    </table>

    <?php echo $InvoiceDescription; ?>

</div>

<br>