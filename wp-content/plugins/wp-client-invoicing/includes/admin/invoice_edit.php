<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_invoices' ) ) {
    $this->redirect_available_page();
}

do_action( 'wpc_client_add_meta_boxes', 'wp-client_page_wpclients_invoicing', '' );
do_action( 'wpc_client_add_meta_boxes_wp-client_page_wpclients_invoicing' , '' );

//save data
if ( isset( $_POST['wpc_data'] ) ) {
    $error = $this->save_data( $_POST['wpc_data'] );
}

global $wpdb;
$wpc_currency = WPC()->get_settings( 'currency' );

$wpc_invoicing = WPC()->get_settings( 'invoicing' );
$rate_capacity = $this->get_rate_capacity();
$thousands_separator = $this->get_thousands_separator();

$option = array();
$num_items = $can_edit = 0;

$all_items = $this->get_items();
$all_items_new = array();
foreach( $all_items as $item ) {
    $all_items_new[ array_shift( $item ) ] = $item;
}

//get data
if ( isset( $_POST['wpc_data'] ) ) {
    $data = array();
    if ( !empty( $_GET['id'] ) ) {
        $data = $this->get_data( $_GET['id'] );
    }

    $data = array_merge( $data, $_POST['wpc_data'] );
} elseif ( isset( $_GET['id'] ) && 0 < $_GET['id'] ) {
    $data = $this->get_data( $_GET['id'] );

    //wrong ID
    if ( !$data ) {
        WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_invoicing' );
        exit;
    }

    if ( isset( $data['discounts'] ) && '' != $data['discounts'] ) {
        $data['discounts'] = unserialize( $data['discounts'] );
    } else {
       $data['discounts'] = array();
    }



    if ( isset( $data['due_date'] ) && '' != $data['due_date'] ) {
        $data['due_date'] = date(  'm/d/Y', $data['due_date'] );
    }

    if ( isset( $data['items'] ) && '' != $data['items'] ) {
        $data['items'] = unserialize( $data['items'] );
    } else {
       $data['items'] = array();
    }

    if ( isset( $data['taxes'] ) && '' != $data['taxes'] ) {
        $data['taxes'] = unserialize( $data['taxes'] );
    } else {
        $data['taxes'] = array();
    }

    if ( isset( $data['order_id'] ) && '' != $data['order_id'] ) {
       $payment_amount = $this->get_amount_paid( $_GET['id'] );
    }

} else {
    $data = array();
    $data['items'] = array();
    $data['discounts'] = array();
    $data['taxes'] = array();

    if ( isset( $wpc_invoicing['ter_con'] ) ) {
        $data['terms'] = $wpc_invoicing['ter_con'];
    }

    if ( isset( $wpc_invoicing['not_cus'] ) ) {
        $data['note'] = $wpc_invoicing['not_cus'];
    }
}

$option['num_items'] = & $num_items;
$option['can_edit'] = & $can_edit;
$option['payment_amount'] = ( isset( $payment_amount ) ) ? $payment_amount : 0;

//set return url
$return_url = get_admin_url(). 'admin.php?page=wpclients_invoicing';
if ( isset( $_SERVER['HTTP_REFERER'] ) && '' != $_SERVER['HTTP_REFERER'] ) {
    $return_url = $_SERVER['HTTP_REFERER'];
}

$status = '';
if ( isset( $data['status'] ) && '' != $data['status'] ) {
    $status = $data['status'];
}

$can_add_payment    = ( 'paid' != $status && 'void' != $status && 'refunded' != $status ) ? 1 : 0;
$can_edit           = ( in_array( $status, array( 'paid', 'pending', 'partial', 'void', 'active', 'refunded' ) ) ) ? 0 : 1;

?>
<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <h2>
    <?php
        if ( isset( $_GET['id'] ) && '' != $_GET['id'] ) {
            _e( 'Edit Invoice', WPC_CLIENT_TEXT_DOMAIN );
            echo ' #' . $data['number'];
            //display status
            if ( $this->display_status_name( $data['status'] ) ) {
                echo ' - ' . $this->display_status_name( $data['status'] ) ;
            }
        } else {
            _e( 'Add Invoice', WPC_CLIENT_TEXT_DOMAIN );
        }

        if ( isset( $data['parrent_id'] ) ) {
            $data['parrent_id'] = (int)$data['parrent_id'] ;
            $parrent_type = get_post_meta( $data['id'], 'wpc_inv_parent_type', true ) ;
            $isset_parrent = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d", $data['parrent_id'] ) ) ;
            if( $isset_parrent ) {
                switch ( $parrent_type ) {
                    case 'accum_inv':
                        echo '<p>' . __( 'This Invoice Created by - Accumulating Profile ', WPC_CLIENT_TEXT_DOMAIN )
                            . '<a href="admin.php?page=wpclients_invoicing&tab=accum_invoice_edit&id=' . $data['parrent_id'] . '" target="_blank">'
                            . htmlspecialchars( $wpdb->get_var( "SELECT post_title FROM {$wpdb->posts} WHERE ID = " . $data['parrent_id'] ) ) . '</a></p>';
                    break;
                    case 'repeat_inv':
                        echo '<p>' . __( 'This Invoice Created by - Recurring Profile ', WPC_CLIENT_TEXT_DOMAIN )
                            . '<a href="admin.php?page=wpclients_invoicing&tab=repeat_invoice_edit&id=' . $data['parrent_id'] . '" target="_blank">'
                            . htmlspecialchars( $wpdb->get_var( "SELECT post_title FROM {$wpdb->posts} WHERE ID = " . $data['parrent_id'] ) ) . '</a></p>';
                    break;
                }
            } else {
                switch ( $parrent_type ) {
                    case 'accum_inv':
                        echo '<p>' . __( 'This Invoice Created by - Accumulating Profile (deleted)', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
                    break;
                    case 'repeat_inv':
                        echo '<p>' . __( 'This Invoice Created by - Recurring Profile (deleted)', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
                    break;
                }
            }
        }
    ?>
    </h2>

    <div id="message" class="error" <?php echo ( !isset( $error ) || empty( $error ) ) ? 'style="display: none;" ' : '' ?> ><?php echo ( isset( $error ) ) ? $error : '' ?></div>

    <form id="edit_data" action="" method="post">
        <input type="hidden" name="wpc_data[id]" value="<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : '' ?>" />
        <input type="hidden" name="wpc_data[status]" id="inv_status" value="<?php echo ( isset( $data['status'] ) ) ? $data['status'] : '' ?>" />
        <?php
            if ( isset( $data['parrent_id'] ) ) {
                echo '<input type="hidden" name="wpc_data[parrent_id]" value="' . $data['parrent_id'] . '" />';
            }
        ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2 not_bold">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label for="title"><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?></label><br />
                            <input type="text" name="wpc_data[title]" id="title" value="<?php echo ( isset( $data['title'] ) ) ? htmlspecialchars( $data['title'] ) : '' ?>" <?php echo ( !$can_edit ) ? 'readonly' : '' ?> />
                        </div>
                    </div>
                    <div id="postdivrich" class="postarea edit-form-section">
                        <label><?php _e( 'Description', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <div class="postarea">

                        <?php
                            $settings = array( 'media_buttons' => false, 'textarea_rows' => 5, 'tinymce' => 0   );
                            $description = ( isset( $data['description'] ) ) ? $data['description'] : '';
                            if ( $can_edit ) {
                                wp_editor( $description, 'wpc_data[description]', $settings );
                            } else {
                                echo '<textarea style="width: 100%;" rows="5" readonly>' . $description . '</textarea>';
                            }
                        ?>

                        </div>
                    </div>
                </div><!-- #post-body-content -->
                <div id="postbox-container-1" class="postbox-container">

                    <?php
                        do_meta_boxes( 'wp-client_page_wpclients_invoicing', 'side', array( 'data' => $data, 'option' => $option )  ) ;
                    ?>
                 </div>
                 <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes('wp-client_page_wpclients_invoicing', 'normal', array( 'data' => $data, 'option' => $option ) ); ?>
                </div>
            </div><!-- #post-body -->
        </div> <!-- #poststuff -->
    </form>
</div>

<script type="text/javascript" language="javascript">

    jQuery( document ).ready( function() {

        <?php if ( $can_edit ) { ?>
            //data piker
            jQuery( '#wpc_data_due_date, .wpc_cf_datepicker' ).datepicker({
                dateFormat : 'mm/dd/yy'
            });
        <?php } ?>

        //Set pre-set due data
        jQuery( '.wpc_set_due_date' ).click( function() {
            jQuery( '#wpc_data_due_date' ).val( jQuery( this ).attr( 'rel' ) );
        });

        //Save Draft data
        jQuery( '#save_draft' ).click( function() {
            var errors = 0;

            if ( jQuery( "#wpc_clients" ).val() !== '' || jQuery( "#wpc_circles" ).val() !== '' ) {
                jQuery( '#wpc_clients' ).parent().removeClass( 'wpc_error' );
                jQuery( '#wpc_circles' ).parent().removeClass( 'wpc_error' );
            } else {
                errors = 1;
                jQuery( '#wpc_clients' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#wpc_circles' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#save_data' ).focus();
            }

            if ( 0 === errors ) {
                jQuery( '#inv_status' ).val( 'draft' );
                jQuery( '#edit_data' ).submit();
            }
            return false;
        });

        //Save data
        var wpc_process_flag = false;
        jQuery( '#save_open' ).click( function() {
            var errors = 0;
            if( wpc_process_flag ) {
                return false;
            }
            wpc_process_flag = true;

            if ( jQuery( "#wpc_clients" ).val() !== '' || jQuery( "#wpc_circles" ).val() !== '' ) {
                jQuery( '#wpc_clients' ).parent().removeClass( 'wpc_error' );
                jQuery( '#wpc_circles' ).parent().removeClass( 'wpc_error' );
            } else {
                errors = 1;
                jQuery( '#wpc_clients' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#wpc_circles' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#save_data' ).focus();
            }

            jQuery( '.wpc_inv_cf_required' ).each( function () {
                if ( !jQuery( this ).val() ) {
                    errors = 1;
                    jQuery( this ).parent().attr( 'class', 'wpc_error' );
                } else {
                    jQuery( this ).parent().removeClass( 'wpc_error' );
                    jQuery( '#save_data' ).focus();
                }
            });


            if ( 0 === errors ) {
                if ( jQuery( '#send_email' ).prop("checked") )
                    jQuery( '#inv_status' ).val( 'sent' );
                else
                    jQuery( '#inv_status' ).val( 'open' );
                jQuery( '#edit_data' ).submit();
            }
            return false;
        });

        //cancel edit INV
        jQuery( '#data_cancel' ).click( function() {
            self.location.href="<?php echo $return_url ?>";
            return false;
        });

    });

</script>