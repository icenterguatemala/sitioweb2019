<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_repeat_invoices' ) ) {
    $this->redirect_available_page();
}

do_action( 'wpc_client_add_meta_boxes', 'wp-client_page_recurring_invoicesv', '' );
do_action( 'wpc_client_add_meta_boxes_wp-client_page_recurring_invoices' , '' );

//save data
if ( isset( $_POST['wpc_data'] ) ) {
    $error = $this->save_profile( $_POST['wpc_data'] );
}

$wpc_invoicing = WPC()->get_settings( 'invoicing' );
$option = array();
$num_items = $can_edit = 0;

$all_items = $this->get_items();
$all_items_new = array();
foreach( $all_items as $item ) {
    $all_items_new[ array_shift( $item ) ] = $item;
}

//get data
if ( isset( $_POST['wpc_data'] ) ) {
    $data = $_POST['wpc_data'];
} elseif ( isset( $_GET['id'] ) && 0 < $_GET['id'] ) {
    $data = $this->get_data( $_GET['id'], 'repeat_invoice' );

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

//set return url
$return_url = get_admin_url(). 'admin.php?page=wpclients_invoicing';
if ( isset( $_SERVER['HTTP_REFERER'] ) && '' != $_SERVER['HTTP_REFERER'] ) {
    $return_url = $_SERVER['HTTP_REFERER'];
}

$status = '';
if ( isset( $data['status'] ) && '' != $data['status'] ) {
    $status = $data['status'];
}

$can_edit = ( in_array( $status, array( 'ended', 'expired' ) ) ) ? 0 : 1;
if ( isset( $data['recurring_type'] ) && 'auto_charge' == $data['recurring_type'] &&'active' == $status ) {
    $can_edit = 0;
}
?>
<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block() ?>
    <h2>

        <?php

            if ( isset( $_GET['id'] ) && '' != $_GET['id'] ) {
                _e( 'Edit Recurring Profile', WPC_CLIENT_TEXT_DOMAIN );
            } else {
                _e( 'Add Recurring Profile', WPC_CLIENT_TEXT_DOMAIN );
            }
        ?>

    </h2>

    <div id="message" class="error" <?php echo ( !isset( $error ) || empty( $error ) ) ? 'style="display: none;" ' : '' ?> ><?php echo ( isset( $error ) ) ? $error : '' ?></div>

    <form id="edit_data" action="" method="post">
        <input type="hidden" name="wpc_data[id]" value="<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : '' ?>" />
        <input type="hidden" name="wpc_data[status]" id="inv_status" value="" />

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2 not_bold">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label for="title"><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?></label><br />
                            <input type="text" name="wpc_data[title]" id="title" value="<?php echo ( isset( $data['title'] ) ) ? htmlspecialchars( $data['title'] ) : '' ?>" <?php echo ( !$can_edit ) ? 'readonly' : '' ?> />
                            <span class="description"><?php printf( __( "Use %s placeholder for displaying creation date of invoice.", WPC_CLIENT_TEXT_DOMAIN ), '<b>%CreationDateFormat="<a href="http://php.net/manual/en/function.date.php" target="_blank">your_date_formate</a>"%</b>') ?></span>
                            <br>
                            <br>
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
                        do_meta_boxes( 'wp-client_page_recurring_invoices', 'side', array( 'data' => $data, 'option' => $option )  ) ;
                    ?>
                 </div>
                 <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes('wp-client_page_recurring_invoices', 'normal', array( 'data' => $data, 'option' => $option ) ); ?>
                </div>
            </div><!-- #post-body -->
      </div> <!-- #poststuff -->
    </form>
</div>
<script type="text/javascript" language="javascript">


    jQuery( document ).ready( function() {

        jQuery( '#wpc_data_from_date' ).datepicker({
            minDate: "0",
            dateFormat : 'mm/dd/yy'
        });

        //Save data
        jQuery( '#save_data' ).click( function() {
            var errors = 0;

            if ( '' !== jQuery( "#title" ).val() ) {
                jQuery( '#wpc_title' ).parent().removeClass( 'wpc_error' );
            } else {
                errors = 1;
                jQuery( '#title' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#title' ).focus();
            }

            //console.log(errors);
            if ( 0 === errors ) {
                jQuery( '#inv_status' ).val( 'draft' );
                jQuery( '#edit_data' ).submit();
            }
            return false;
        });

        jQuery( '#save_data_stop' ).click( function() {
            jQuery( '#inv_status' ).val( 'stopped' );
            jQuery( '#edit_data' ).submit();
            return false;
        });


        //Save & send data
        jQuery( '#save_data_send' ).click( function() {
            var errors = 0;

            if ( '' !== jQuery( "#title" ).val() ) {
                jQuery( '#title' ).parent().removeClass( 'wpc_error' );
            } else {
                errors = 1;
                jQuery( '#title' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#title' ).focus();
            }


            if ( ( 'auto_charge' !== jQuery( ".wpc_recurring_type:checked" ).val() && ( '' !== jQuery( "#wpc_data_from_date" ).val() || jQuery("#wpc_last_day_month").prop("checked") ) )
            || ( 'auto_charge' === jQuery( ".wpc_recurring_type:checked" ).val() && '' !== jQuery( "#wpc_data_from_date" ).val() ) ) {
                jQuery( '#wpc_last_day_month' ).parent().removeClass( 'wpc_error' );
                jQuery( '#wpc_data_from_date' ).parent().removeClass( 'wpc_error' );
            } else {
                errors = 2;

                jQuery( '#wpc_data_from_date' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#wpc_last_day_month' ).parent().attr( 'class', 'wpc_error' );
                jQuery( '#save_data' ).focus();
            }

            if ( jQuery( "#wpc_clients" ).val() !== '' || jQuery( "#wpc_circles" ).val() !== '' ) {
                jQuery( '#wpc_clients' ).parent().parent().removeClass( 'wpc_error' );
                jQuery( '#wpc_circles' ).parent().parent().removeClass( 'wpc_error' );
            } else {
                errors = 3;
                jQuery( '#wpc_clients' ).parent().parent().attr( 'class', 'wpc_error' );
                jQuery( '#wpc_circles' ).parent().parent().attr( 'class', 'wpc_error' );
                jQuery( '#save_data' ).focus();
            }

            if ( 0 === errors ) {
                var status = '<?php echo $status ? $status : ''?>';
                if ('' === status || 'draft' === status) {
                    status = 'pending';
                }
                jQuery( '#inv_status' ).val( status );
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