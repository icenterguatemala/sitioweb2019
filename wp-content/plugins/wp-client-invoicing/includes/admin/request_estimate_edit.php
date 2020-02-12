<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_estimate_requests' ) ) {
    $this->redirect_available_page();
}

do_action( 'wpc_client_add_meta_boxes', 'wp-client_page_wpclients_invoicing', '' );
do_action( 'wpc_client_add_meta_boxes_wp-client_page_wpclients_invoicing' , '' );

//save data
if ( isset( $_POST['wpc_data'] ) ) {

    $_POST['wpc_data']['wpc_sender'] = 'admin';

    $action = ( !empty( $_POST['wpc_inv_action'] ) ) ? $_POST['wpc_inv_action'] : '' ;
    $id = ( !empty( $_POST['wpc_data']['id'] ) ) ? $_POST['wpc_data']['id'] : 0 ;

    if ( 'convert_to_inv' == $action ) {
        $msg = $this->convert_request_estimate( $id, 'convert', 'inv' );
    } elseif ( 'convert_to_est' == $action ) {
        $msg = $this->convert_request_estimate( $id, 'convert', 'est' );
    } else {
        $return = $this->save_request_estimate( $_POST['wpc_data'] );
    }
    $error = ( !empty( $return['error'] ) ) ? $return['error'] : '';

    if ( empty( $error ) ) {
        if ( !isset( $msg ) ) {
            $msg = ( !empty( $return['msg'] ) ) ? $return['msg'] : '';
        }
        WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=request_estimates' . '&msg=' . $msg );
    }
}


$rate_capacity = $this->get_rate_capacity();
$thousands_separator = $this->get_thousands_separator();

$option = array();
$num_items = 0;

$all_items = $this->get_items();
$all_items_new = array();
foreach( $all_items as $item ) {
    $all_items_new[ array_shift( $item ) ] = $item;
}

//get data
if ( isset( $_POST['wpc_data'] ) ) {
    $data = $_POST['wpc_data'];
} elseif ( isset( $_GET['id'] ) && 0 < $_GET['id'] ) {

    $data = $this->get_data( $_GET['id'] );

    //wrong ID
    if ( !$data ) {
        WPC()->redirect( get_admin_url(). 'admin.php?page=wpclients_invoicing' );
        exit;
    }

    if ( isset( $data['items'] ) && '' != $data['items'] ) {
        $data['items'] = unserialize( $data['items'] );
    } else {
       $data['items'] = array();
    }

    if ( isset( $data['discounts'] ) && '' != $data['discounts'] ) {
        $data['discounts'] = unserialize( $data['discounts'] );
    } else {
       $data['discounts'] = array();
    }

    if ( isset( $data['taxes'] ) && '' != $data['taxes'] ) {
        $data['taxes'] = unserialize( $data['taxes'] );
    } else {
        $data['taxes'] = array();
    }
} else {
    $data = array();
    $data['items'] = array();
}


$option['num_items'] = & $num_items;
$option['can_edit'] = & $can_edit;
$option['payment_amount'] = 0;

//set return url
$return_url = get_admin_url(). 'admin.php?page=wpclients_invoicing&tab=estimates';
if ( isset( $_SERVER['HTTP_REFERER'] ) && '' != $_SERVER['HTTP_REFERER'] ) {
    $return_url = $_SERVER['HTTP_REFERER'];
}

$status = '';
if ( isset( $data['status'] ) && '' != $data['status'] ) {
    $status = $data['status'];
}

$can_edit = 1 ;

?>
<div class="wrap">

     <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <h2>
    </h2>

    <div id="message" class="error" <?php echo ( empty( $error ) ) ? 'style="display: none;" ' : '' ?> ><?php echo ( !empty( $error ) ) ? $error : ''; ?></div>

<form id="edit_data" action="" method="post">
    <input type="hidden" name="wpc_data[id]" value="<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : '' ?>" />
    <input type="hidden" name="wpc_inv_action" id="wpc_inv_action" value="" />

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2 not_bold">
            <div id="post-body-content">
                <div id="titlediv">
                    <div id="titlewrap">
                        <label for="title"><?php _e( 'Title: ', WPC_CLIENT_TEXT_DOMAIN ) ?><?php echo ( isset( $data['title'] ) ) ? $data['title'] : '' ?></label><br />
                        <!--input type="text" name="wpc_data[title]" id="title" value="<?php /* echo ( isset( $data['title'] ) ) ? $data['title'] : '' ?>" <?php echo ( !$can_edit ) ? 'readonly' : ''*/ ?> /-->
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

        //Save data
        jQuery( '.wpc_button' ).click( function() {
            var errors = 0;

            /*if ( '' != jQuery( "#title" ).val() ) {
                jQuery( '#title' ).parent().removeClass( 'wpc_error' );
            } else {
                errors = 1
                jQuery( '#title' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#title' ).focus();
            }*/

            if ( 0 === errors ) {
                jQuery( '#wpc_inv_action' ).val( jQuery( this ).data( 'action' ) );
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