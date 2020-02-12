<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclient_clients' ) );
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_create_custom_fields' ) ) {
    WPC()->redirect( get_admin_url() . 'admin.php?page=wpclient_clients' );
}

$error = "";

if ( isset( $_POST['submit'] ) ) {
    $custom_field_name = ( isset( $_POST['custom_field']['name'] ) ) ? $_POST['custom_field']['name'] : '';
    $custom_field_name = strtolower( $custom_field_name );
    $custom_field_name = str_replace( ' ', '_', $custom_field_name );
    $custom_field_name = preg_replace( '/[^a-z0-9_]/i', '', $custom_field_name );

    $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

    // validate at php side
    //field name
    if ( empty( $custom_field_name ) ) {
        $error .= __( 'A Custom Field Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
    } elseif ( isset( $_GET['add'] ) && '1' == $_GET['add'] ) {
        //add prefix to field
        $custom_field_name = 'wpc_cf_' . $custom_field_name;
        //check that field not already exist
        if ( isset( $wpc_custom_fields[$custom_field_name] ) )
            $error .= sprintf( __( 'A Custom Field with this name "%s" already exist already.<br/>', WPC_CLIENT_TEXT_DOMAIN ), $custom_field_name );

    }

    //field type
    if ( empty( $_POST['custom_field']['type'] ) && empty( $_GET['edit'] ) )
        $error .= __( 'A Custom Field Type is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );

    //save the custom field
    if ( empty( $error ) ) {
        $custom_field = $_POST['custom_field'];

        unset( $custom_field['name'] );
        $custom_field['required'] = ( isset( $custom_field['required'] ) && '1' == $custom_field['required'] ) ? '1' : '0';

        /* gdpr */
        $custom_field['gdpr_erase'] = (int) isset( $custom_field['gdpr_erase'] );
        $custom_field['gdpr_export'] = (int) isset( $custom_field['gdpr_export'] );
        
        $custom_field['zero_value'] = ( isset( $custom_field['zero_value'] ) && '1' == $custom_field['zero_value'] ) ? '1' : '0';
        $custom_field['track_number'] = ( !empty( $_POST['track_number'] ) && (int)$_POST['track_number'] ) ? (int)$_POST['track_number'] : '0';
        $custom_field['type'] = !empty( $_GET['edit'] ) ? $wpc_custom_fields[$custom_field_name]['type'] : $custom_field['type'];

        $wpc_custom_fields[$custom_field_name] = $custom_field;

        WPC()->settings()->update( $wpc_custom_fields, 'custom_fields' );

        if ( isset( $_GET['add'] ) && '1' == $_GET['add'] ) {
            //on create new custom field check option to show it at Clients table
            //if option is empty then update all users meta to hide CF column at clients page
            if ( empty( $custom_field['display_screen_options'] ) && isset( $custom_field['nature'] ) &&
                ( 'client' == $custom_field['nature'] || 'both' == $custom_field['nature'] ) ) {
                //get All WPC backend Roles + Administrator
                $args = array(
                    'blog_id'      => get_current_blog_id(),
                    'role__in'     => array('administrator', 'wpc_manager', 'wpc_admin'),
                    'fields'       => 'ids',
                );
                $wpc_user_ids = get_users( $args );

                foreach ( $wpc_user_ids as $user_id ) {
                    //update meta value for hidden screen options fields
                    $hidden_columns = get_user_meta( $user_id, 'managewp-client_page_wpclient_clientscolumnshidden', true );

                    if ( empty( $hidden_columns ) )
                        $hidden_columns = array();

                    $hidden_columns[] = $custom_field_name;
                    update_user_meta( $user_id, 'managewp-client_page_wpclient_clientscolumnshidden', $hidden_columns );
                }
            }
        }

        WPC()->redirect( 'admin.php?page=wpclient_clients&tab=custom_fields&msg=a' );
    }
}


//get custom field data
if ( isset( $_REQUEST['custom_field'] ) ) {
    $custom_field = $_REQUEST['custom_field'];
} elseif ( isset( $_GET['edit'] ) &&  '' != $_GET['edit'] ) {
    $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
    if ( isset( $wpc_custom_fields[$_GET['edit']] ) ) {
        $custom_field           = $wpc_custom_fields[$_GET['edit']];
        $custom_field['name']   = $_GET['edit'];
        $custom_field['type']   = $wpc_custom_fields[ $_GET['edit'] ]['type'];
        unset( $wpc_custom_fields );
    } else {
        WPC()->redirect( 'admin.php?page=wpclient_clients&tab=custom_fields&msg=n' );
    }
}

//change text
if ( isset( $_GET['add'] ) && '1' == $_GET['add'] )
    $title_text = __( 'Add Custom Field', WPC_CLIENT_TEXT_DOMAIN );
else
    $title_text = __( 'Update Custom Field', WPC_CLIENT_TEXT_DOMAIN );
?>

<style type="text/css">
    .wrap input[type=text] {
        width:400px;
    }

    .wrap input[type=password] {
        width:400px;
    }
</style>


<script type="text/javascript" language="javascript">
    jQuery( document ).ready( function() {

        jQuery('.wpc_options_table tbody').on('click', 'input[type="radio"]', function() {
            var $td = jQuery(this).closest('td'),
                index = jQuery(this).closest('tr').children().index( $td );
            jQuery( '.wpc_options_table tbody tr' ).each(function() {
                jQuery(this).children().eq( index ).find( 'input' ).prop('checked', false );
            });
            jQuery(this).prop('checked', true );
        });

        jQuery( '#type' ).bind( 'change', function() {
            var type = jQuery( '#type' ).val();
            jQuery( '.ct-field-type-options' ).hide();
            jQuery( '#field_title_box' ).hide();
            jQuery( '#field_description_box' ).hide();
            jQuery( '#wpc_field_view' ).hide();
            jQuery( '#wpc_field_required' ).hide();
            
            /* gdpr */
            jQuery( '#wpc_field_gdpr_erase' ).hide();
            jQuery( '#wpc_field_gdpr_export' ).hide();
            
            jQuery( '.ct-field-hiden-value' ).hide();
            jQuery( '.ct-field-currency-type' ).hide();
            jQuery( '.mask_field' ).hide();
            jQuery('#wpc_field_display_screen_options').show();
            if ( type === 'radio' || type === 'selectbox' || type === 'multiselectbox' || type === 'checkbox' ) {
                jQuery( '.ct-field-type-options' ).show();
                jQuery( '#field_title_box' ).show();
                jQuery( '#field_description_box' ).show();
                jQuery( '#wpc_field_view' ).show();
                jQuery( '#wpc_field_required' ).show();
            
                /* gdpr */
                jQuery( '#wpc_field_gdpr_erase' ).show();
                jQuery( '#wpc_field_gdpr_export' ).show();

                var $obj = jQuery('.wpc_default_column'),
                    input_type = ( type === 'multiselectbox' || type === 'checkbox' ) ? 'checkbox' : 'radio',
                    index = jQuery('.wpc_options_table thead th').index( $obj ),
                    remove_checked = jQuery( '.wpc_options_table tbody tr' ).children().eq(index).find('input[type="checkbox"]').length > 0 &&
                    input_type == 'radio';


                jQuery('.wpc_options_table tbody tr').each(function () {
                    $obj = jQuery(this).children().eq(index).find('input');
                    $obj.attr('type', input_type);
                    if( remove_checked ) {
                        $obj.removeAttr('checked');
                    }
                });
            }
            // else if ( type === 'text' || type === 'datepicker' || type === 'cost' || type === 'textarea' || type === 'password' ) {
            else if ( type === 'text' || type === 'datepicker' || type === 'textarea' || type === 'password' ) {
                if ( type === 'text' ) {
                    jQuery( '.mask_field' ).show();
                }
                jQuery( '#field_title_box' ).show();
                jQuery( '#field_description_box' ).show();
                jQuery( '#wpc_field_view' ).show();
                jQuery( '#wpc_field_required' ).show();
            
                /* gdpr */
                jQuery( '#wpc_field_gdpr_erase' ).show();
                jQuery( '#wpc_field_gdpr_export' ).show();
            }
            else if ( type === 'file' ) {
                jQuery( '#field_title_box' ).show();
                jQuery( '#field_description_box' ).show();
                jQuery( '#wpc_field_view' ).show();
                jQuery( '#wpc_field_required' ).show();                
            
                /* gdpr */
                jQuery( '#wpc_field_gdpr_erase' ).show();
                jQuery( '#wpc_field_gdpr_export' ).show();
            } else if ( type === 'hidden' ) {
                jQuery( '.ct-field-hiden-value' ).show();
                jQuery('#wpc_field_display_screen_options').hide();
            } else if ( type === 'cost' ) {
                jQuery( '.ct-field-currency-type' ).show();
            }
        } ).triggerHandler('change');

        //hide column of view table for type cf as client
        jQuery('#nature').change( function() {
            if ( undefined === typeof( jQuery( this ).val() )
                || 'client' === jQuery( this ).val() ) {
                jQuery('.wpc_td_staff').css('display', 'none');
                //jQuery('.wpc_tr_admin_screen').css('display', 'table-row');
                jQuery('.client_row').show();
                jQuery('.staff_row').hide();
                jQuery('#wpc_field_display_screen_options').show();
            } else if ( 'both' === jQuery( this ).val() ) {
                //jQuery('.wpc_tr_admin_screen').css('display', 'table-row');
                jQuery('.client_row').show();
                jQuery('.staff_row').show();
                jQuery('.wpc_td_staff').css('display', 'table-cell');
                jQuery('#wpc_field_display_screen_options').show();
            } else {
                jQuery('.wpc_td_staff').css('display', 'table-cell');
                //jQuery('.wpc_tr_admin_screen').css('display', 'none');
                jQuery('.client_row').hide();
                jQuery('.staff_row').show();
                jQuery('#wpc_field_display_screen_options').hide();
            }
        }).trigger('change');


        jQuery('#wpc_add_many_options').shutter_box({
            view_type       : 'lightbox',
            width           : '500px',
            type            : 'inline',
            href            : '#wpc_block_for_many_options',
            title           : '<?php echo esc_js( __( 'Add Multiple Options', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
        });

        jQuery(document).on('click', '.cancel_add_option', function() {
            jQuery('#wpc_add_many_options').shutter_box('close');
            jQuery('#wpc_textarea_many_options').val('');
            return false;
        });

        jQuery(document).on('click', '.submit_add_options', function() {
            var data = jQuery('#wpc_textarea_many_options').val(),
                arr = data.split('\n'),
                label_column_index = jQuery('.wpc_options_table thead th').index( jQuery('.wpc_label_column') ),
                value_column_index = jQuery('.wpc_options_table thead th').index( jQuery('.wpc_value_column') ),
                option_array;
            for( index in arr ) {
                if( arr[index] == '' ) continue;
                if( jQuery('.wpc_options_table tbody tr:last td:eq(' + label_column_index + ')').find('input').val() != '' &&
                jQuery('.wpc_options_table tbody tr:last td:eq(' + value_column_index + ')').find('input').val() != '' ) {
                    jQuery('.ct-field-add-option:last').trigger('click');
                }
                option_array = arr[index].split('|');
                jQuery('.wpc_options_table tbody tr:last td:eq(' + label_column_index + ')').find('input')
                    .val( option_array[0] );
                jQuery('.wpc_options_table tbody tr:last td:eq(' + value_column_index + ')').find('input')
                    .val( typeof option_array[1] != 'undefined' ? option_array[1] : option_array[0] );
            }

            jQuery('#wpc_add_many_options').shutter_box('close');
            jQuery('#wpc_textarea_many_options').val('');
            return false;
        });

        // custom fields remove options
        jQuery('#wpc_client_custom_filed_form').on('submit', function() {
            if ( jQuery( '#type' ).val() === 'hidden' ) {
                jQuery( '.ct-field-type-options' ).remove();
                jQuery( '#field_title_box' ).remove();
                jQuery( '#field_description_box' ).remove();
                jQuery( '#wpc_field_view' ).remove();
                jQuery( '#wpc_field_required' ).remove();

                /* gdpr */
                jQuery( '#wpc_field_gdpr_erase' ).remove();
                jQuery( '#wpc_field_gdpr_export' ).remove();
            } else {
                if( !jQuery('#show_value_field').is(':checked') ) {
                    var label_column_index = jQuery('.wpc_options_table thead th').index(jQuery('.wpc_label_column')),
                        value_column_index = jQuery('.wpc_options_table thead th').index(jQuery('.wpc_value_column'));
                    jQuery('.wpc_options_table tbody tr').each(function () {
                        var label = jQuery(this).children('td:eq(' + label_column_index + ')').find('input').val();
                        jQuery(this).children('td:eq(' + value_column_index + ')').find('input').val( label );
                    });
                }
                jQuery( '.ct-field-hiden-value' ).remove();
            }
        });


        // custom fields add options
        jQuery('body').on('click', '.ct-field-add-option', function() {
            var key = uniqid(),
                type = jQuery('#type').val(),
                input_type = ( type == 'checkbox' || type == 'multiselectbox' ) ? 'checkbox' : 'radio';

            jQuery('.wpc_options_table tbody').append('<tr>' +
                '<td>' +
                    '<input type="' + input_type + '"  title="<?php _e( 'Preset value', WPC_CLIENT_TEXT_DOMAIN ); ?>" value="1" name="custom_field[options][' + key + '][default]" />' +
                '</td>' +
                '<td>' +
                    '<input type="text" name="custom_field[options][' + key + '][label]" value="" />' +
                '</td>' +
                '<td>' +
                    '<input type="text" name="custom_field[options][' + key + '][value]" value="" />' +
                '</td>' +
                '<td>' +
                    '<a href="javascript:void(0);" class="dashicons ct-field-add-option"></a> ' +
                    '<a href="javascript:void(0);" class="dashicons ct-field-delete-option"></a>' +
                '</td>' +
            '</tr>');
            jQuery('#show_value_field').triggerHandler('click');
            jQuery('.ct-field-delete-option').show();
        });

        // custom fields remove options
        jQuery('body').on('click', '.ct-field-delete-option', function() {
            jQuery(this).closest('tr').remove();
            if( jQuery('.ct-field-delete-option').length == 1 ) {
                jQuery('.ct-field-delete-option').hide();
            } else {
                jQuery('.ct-field-delete-option').show();
            }
        });

        jQuery('#mask_type').change(function() {
            var type = jQuery(this).val();
            if( 'custom' != type ) {
                var mask = jQuery(this).children('option:selected').data('mask');
                var reverse = jQuery(this).children('option:selected').data('reverse');
                jQuery('#mask_value').val( mask );
                jQuery('#mask_reverse').prop( 'checked', reverse == '1' );
            }
        }).change();

        jQuery('#mask_value').keypress(function() {
            jQuery('#mask_type').val('custom');
        });

        jQuery('#mask_reverse').click(function() {
            jQuery('#mask_type').val('custom');
        });
//debugger;

        jQuery('#show_value_field').on('click', function() {
            var $obj = jQuery('.wpc_value_column'),
                index = jQuery('.wpc_options_table thead th').index( $obj );
            if( jQuery(this).is(':checked') ) {
                $obj.show();
                jQuery( '.wpc_options_table tbody tr' ).each(function() {
                    jQuery(this).children().eq( index ).show();
                });
            } else {
                $obj.hide();
                jQuery( '.wpc_options_table tbody tr' ).each(function() {
                    jQuery(this).children().eq( index ).hide();
                });
            }
        }).triggerHandler('click');

        function uniqid() {
            var ts=String(new Date().getTime()), i = 0, out = '';
            for(i=0;i<ts.length;i+=2) {
               out+=Number(ts.substr(i, 2)).toString(36);
            }
            return out;
        }
    });

</script>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'clients' ) ?>
        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block custom_fields">

            <h2><?php echo $title_text ?></h2>

            <div id="message" class="updated wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

            <p class="description">
            <?php _e( 'These fields will show on the self registration form ( by default, found at /client-registration ) You can also use the data entered in these fields in your Start Pages, Portal Pages & Emails using placeholders. The default format for these placeholders will be {wpc_cf_xxxxx} with xxxxx being replaced with the slug you enter in the "Field Slug" field ', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </p>

            <form action="" method="post" name="wpc_client_custom_filed_form" id="wpc_client_custom_filed_form">
                <?php if ( !empty( $_GET['edit'] ) ) { ?>
                    <input type="hidden" name="custom_field[name]" value="<?php echo ( isset( $custom_field['name'] ) ) ? $custom_field['name'] : '' ?>" />
                    <input type="hidden" name="custom_field[display_screen_options]" value="<?php echo ! empty( $custom_field['display_screen_options'] ) ? $custom_field['display_screen_options'] : '' ?>" />
                <?php } ?>

                <table class="form-table">
                    <tr>
                        <th>
                            <label for="name"><?php _e( 'Field Slug', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="required">(<?php _e( 'required', WPC_CLIENT_TEXT_DOMAIN ) ?>)</span></label>
                        </th>
                        <td>
                            <?php if ( !empty( $_GET['edit'] ) ) { ?>
                                <input type="text" disabled value="<?php echo ( isset( $custom_field['name'] ) ) ? $custom_field['name'] : '' ?>" />
                                <br>
                                <span class="description"><?php _e( "Can't be changed.", WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <?php } else { ?>
                                wpc_cf_<input type="text" name="custom_field[name]" id="name" style="width: 372px;" value="<?php echo ( isset( $custom_field['name'] ) ) ? $custom_field['name'] : '' ?>" />
                                <br>
                                <span class="description"><?php _e( 'The name used to identify the custom field. Should consist only of these characters "a-z" and the underscore symbol "_" <br> - (not displayed on the form).', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="type"><?php _e( 'Field Type', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="required">(<?php _e( 'required', WPC_CLIENT_TEXT_DOMAIN) ?>)</span></label>
                        </th>
                        <td>
                            <select name="custom_field[type]" id="type" <?php disabled( !empty( $_GET['edit'] ) ); ?>>
                                <option value="text" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'text' ) ? 'selected' : '' ?>><?php _e( 'Text Box', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="textarea" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'textarea' ) ? 'selected' : ''  ?>><?php _e( 'Multi-line Text Box', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="radio" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'radio' ) ? 'selected' : ''  ?>><?php _e( 'Radio Buttons', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="checkbox" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'checkbox' ) ? 'selected' : ''  ?>><?php _e( 'Checkboxes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="selectbox" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'selectbox' ) ? 'selected' : ''  ?>><?php _e( 'Select Box', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="multiselectbox" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'multiselectbox' ) ? 'selected' : ''  ?>><?php _e( 'Multi Select Box', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="hidden" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'hidden' ) ? 'selected' : ''  ?>><?php _e( 'Hidden Field', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="file" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'file' ) ? 'selected' : ''  ?>><?php _e( 'File', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="datepicker" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'datepicker' ) ? 'selected' : '' ?>><?php _e( 'Datepicker', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="cost" <?php echo ( isset( $custom_field['type'] ) && $custom_field['type'] == 'cost' ) ? 'selected' : '' ?>><?php _e( 'Cost', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="password" <?php echo (isset( $custom_field['type'] ) && $custom_field['type'] == 'password' ) ? 'selected' : '' ?>><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            </select>
                            <br>
                            <span class="description"><?php _e( 'Select type of the custom field.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>

                            <div class="ct-field-type-options">
                                <h4><?php _e( 'Fill in the options for this field', WPC_CLIENT_TEXT_DOMAIN ) ?>:</h4>
                                <p style="float: left;">
                                    <label>
                                        <?php _e( 'Order By', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                                        <select name="custom_field[sort_order]">
                                            <option value="default" <?php echo ( isset( $custom_field['sort_order'] ) && 'default' == $custom_field['sort_order'] ) ? 'selected' : '' ?> ><?php _e( 'Order Entered', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                            <option value="asc" <?php echo ( isset( $custom_field['sort_order'] ) && 'asc' == $custom_field['sort_order'] ) ? 'selected' : '' ?> ><?php _e( 'Name - Ascending', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                            <option value="desc" <?php echo ( isset( $custom_field['sort_order'] ) && 'desc' == $custom_field['sort_order'] ) ? 'selected' : '' ?> ><?php _e( 'Name - Descending', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        </select>
                                    </label>

                                    <label>
                                        <input type="checkbox" id="show_value_field" name="custom_field[show_value_field]" value="1"
                                        <?php checked( isset( $custom_field['show_value_field'] ) && $custom_field['show_value_field'] == '1' ); ?> />
                                        <?php _e( 'Use Custom Values', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </p>
                                <p style="padding-top: 10px;">
                                    <a style="margin-left: 15px;" href="javascript: void(0);" id="wpc_add_many_options">
                                        <?php _e( 'Add Multiple Options', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </a>
                                </p>
                                <br>
                                <div id="wpc_block_for_many_options" style="display: none;float:left;width:100%;">
                                    <form id="delete_user_settings" method="get" style="float:left;width:100%;">
                                        <p style="float:left;width:100%;">
                                            <label for="wpc_textarea_many_options">
                                                <?php _e( 'Options:', WPC_CLIENT_TEXT_DOMAIN ); ?>
                                            </label>
                                            <br>
                                            <textarea id="wpc_textarea_many_options" style="float:left;width:100%;resize:vertical;" rows="18"></textarea>
                                            <br>
                                            <span class="description">
                                                <?php _e( 'Each option in new line', WPC_CLIENT_TEXT_DOMAIN ); ?>
                                            </span>
                                        </p>

                                        <p style="float:left;width:100%;">
                                            <input type="button" class="button-primary submit_add_options" value="<?php _e( 'Add options', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                                            <input type="button" class="button cancel_add_option" style="float: right;" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                                        </p>
                                    </form>
                                </div>

                                <p>
                                    <table class="wpc_options_table">
                                        <thead>
                                            <tr>
                                                <th class="wpc_default_column" style="width: 20px;">&nbsp;</th>
                                                <th class="wpc_label_column"><?php _e( 'Label', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                                                <th class="wpc_value_column"><?php _e( 'Value', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                                                <th style="width: 60px;">&nbsp;</th>
                                            </tr>
                                        </thead>
                                    <?php if( !( isset( $custom_field['options'] ) && is_array( $custom_field['options'] ) ) ) {
                                        $custom_field['options'] = array('');
                                    }
                                    foreach ( $custom_field['options'] as $key=>$field_option ) { ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" value="1" title="<?php _e( 'Preset value', WPC_CLIENT_TEXT_DOMAIN ); ?>" name="custom_field[options][<?php echo( $key ) ?>][default]"
                                                    <?php checked( isset( $field_option['default'] ) && $field_option['default'] == '1' ); ?> />
                                            </td>
                                            <td>
                                                <input type="text" name="custom_field[options][<?php echo( $key ) ?>][label]"
                                                       value="<?php echo isset( $field_option['label'] ) ?
                                                           esc_attr( $field_option['label'] ) : '' ?>" />
                                            </td>
                                            <td>
                                                <input type="text" name="custom_field[options][<?php echo( $key ) ?>][value]"
                                                       value="<?php echo isset( $field_option['value'] ) ?
                                                           esc_attr( $field_option['value'] ) : '' ?>" />
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="dashicons ct-field-add-option"></a>
                                                <a href="javascript:void(0);" class="dashicons ct-field-delete-option"></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </table>
                                </p>
                            </div>


                            <div class="ct-field-hiden-value">
                                <p>
                                    <?php _e( 'Fill in the value for this field', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                                    <input type="text" name="custom_field[default_value]" value="<?php echo( isset( $custom_field['default_value'] ) ? $custom_field['default_value'] : '' ) ?>" />
                                </p>
                            </div>

                            <div class="ct-field-currency-type">
                                <p>
                                    <label for="currency_type_code">
                                        <input type="radio"
                                               id="currency_type_code"
                                               name="custom_field[currency_type]"
                                               value="code"
                                               <?php echo ( $custom_field['currency_type'] === 'code' )
                                                   ? 'checked' : '' ?>
                                                />
                                        <?php _e( 'Use Alphabetic Code', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </p>
                                <p>
                                    <label for="currency_type_symbol">
                                        <input type="radio"
                                               id="currency_type_symbol"
                                               name="custom_field[currency_type]"
                                               value="symbol"
                                               <?php echo ( $custom_field['currency_type'] === 'symbol' )
                                                   ? 'checked' : '' ?>
                                               />
                                        <?php _e( 'Use Symbol', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </p>
                            </div>

                        </td>
                    </tr>
                    <tr id="field_title_box">
                        <th>
                            <label for="title"><?php _e( 'Field Title', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                            <input type="text" name="custom_field[title]" id="title" value="<?php echo ( isset( $custom_field['title'] ) ) ? $custom_field['title'] : '' ?>" />
                            <br>
                            <span class="description"><?php _e( 'The title of the custom field (displayed on the form).', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </td>
                    </tr>
                    <tr id="field_description_box">
                        <th>
                            <label for="description"><?php _e( 'Field Description', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                           <textarea name="custom_field[description]" rows="3" cols="69" id="description" ><?php echo ( isset( $custom_field['description'] ) ) ? $custom_field['description'] : '' ?></textarea>
                           <br>
                           <span class="description"><?php _e( 'Description for the custom field (displayed on the form).', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </td>
                    </tr>
                    <tr id="wpc_field_required">
                        <th>
                            <label for="display"><?php _e( 'Required Field', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="custom_field[required]" id="required" value="1" <?php echo ( isset( $custom_field['required'] ) && '1' == $custom_field['required'] ) ? 'checked' : '' ?> />
                        </td>
                    </tr>
                    
                    <!-- gdpr -->
                    <tr id="wpc_field_gdpr_erase">
                        <th>
                            <label for="display"><?php _e( 'GDPR erase', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                            <label><input type="checkbox" name="custom_field[gdpr_erase]" id="gdpr_erase" value="1" <?php echo ( isset( $custom_field['gdpr_erase'] ) && '1' == $custom_field['gdpr_erase'] ) ? 'checked' : '' ?> /><?php _e( 'Erase this field using "Data Erasure Request"', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </td>
                    </tr>
                    <tr id="wpc_field_gdpr_export">
                        <th>
                            <label for="display"><?php _e( 'GDPR export', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                            <label><input type="checkbox" name="custom_field[gdpr_export]" id="gdpr_export" value="1" <?php echo ( isset( $custom_field['gdpr_export'] ) && '1' == $custom_field['gdpr_export'] ) ? 'checked' : '' ?> /><?php _e( 'Include this field in the "Data Export Request" result', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </td>
                    </tr>
                    
                    <tr id="field_relate_to">
                        <th>
                            <label for="relate_to"><?php _e( 'Relate to User Meta', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                           <input type="text" name="custom_field[relate_to]" id="relate_to" value="<?php echo ( isset( $custom_field['relate_to'] ) ) ? $custom_field['relate_to'] : '' ?>" />
                           <br>
                           <span class="description"><?php _e( 'You can relate this field value with User Meta. Example: first_name', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </td>
                    </tr>
                    <tr id="field_nature">
                        <th>
                            <label for="nature"><?php _e( 'Field For', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                           <select name="custom_field[nature]" id="nature" ><?php echo ( isset( $custom_field['nature'] ) ) ? $custom_field['nature'] : '' ?>
                                <option value="client" <?php echo ( !isset( $custom_field['nature'] ) || 'client' == $custom_field['nature'] ) ? 'selected' : '' ?>><?php echo WPC()->custom_titles['client']['p'] ?></option>
                                <option value="staff" <?php echo ( isset( $custom_field['nature'] ) && 'staff' == $custom_field['nature'] ) ? 'selected' : '' ?>><?php echo WPC()->custom_titles['staff']['p'] ?></option>
                                <option value="both" <?php echo ( isset( $custom_field['nature'] ) && 'both' == $custom_field['nature'] ) ? 'selected' : '' ?>><?php printf( __( '%s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) ?></option>
                           </select>
                           <br>
                           <span class="description"><?php _e( 'Select users of the custom field.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </td>
                    </tr>
                    <?php if ( isset( $_GET['add'] ) && '1' == $_GET['add'] ) { ?>
                        <tr id="wpc_field_display_screen_options">
                            <th>
                                <label for="display_screen_options">
                                    <?php printf( __( 'Display on %s table', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?>
                                    <br>
                                    <?php _e( '(just on create)', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="custom_field[display_screen_options]" id="display_screen_options" value="1" <?php echo ( isset( $custom_field['display_screen_options'] ) && '1' == $custom_field['display_screen_options'] ) ? 'checked' : '' ?> />
                                    <?php printf( __( 'Add this custom field as column to %s table', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?>
                                </label>
                                <br />
                                <span class="description"><?php printf( __( 'This option will set a default value for screen options of column in the %s table for users. Each user can individually change it on their screen options.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></span>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr class="mask_field">
                        <th>
                            <label for="display"><?php _e( 'Field Mask', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                            <?php
                                $mask_type = isset( $custom_field['mask_type'] ) ? $custom_field['mask_type'] : '';
                            ?>
                            <select name="custom_field[mask_type]" id="mask_type">
                                <option value="" data-mask="" data-reverse="0" <?php selected( $mask_type, '' ); ?>><?php _e( ' - Without Mask - ', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                                <option value="date" data-mask="00/00/0000" data-reverse="0" <?php selected( $mask_type, 'date' ); ?>><?php echo __( 'Date', WPC_CLIENT_TEXT_DOMAIN ) . ': ' . date('m/d/Y') ?></option>
                                <option value="time" data-mask="00:00:00" data-reverse="0" <?php selected( $mask_type, 'time' ); ?>><?php echo __( 'Time', WPC_CLIENT_TEXT_DOMAIN ) . ': ' . date('H:i:s') ?></option>
                                <option value="datetime" data-mask="00/00/0000 00:00:00" data-reverse="0" <?php selected( $mask_type, 'datetime' ); ?>><?php echo __( 'DateTime', WPC_CLIENT_TEXT_DOMAIN ) . ': ' . date('m/d/Y H:i:s') ?></option>
                                <option value="zip_code" data-mask="00000-000" data-reverse="0" <?php selected( $mask_type, 'zip_code' ); ?>><?php echo __( 'Zip Code', WPC_CLIENT_TEXT_DOMAIN ) . ': 12345-678' ?></option>
                                <option value="zip_code2" data-mask="0-00-00-00" data-reverse="0" <?php selected( $mask_type, 'zip_code2' ); ?>><?php echo __( 'Zip Code', WPC_CLIENT_TEXT_DOMAIN ) . ': 1-23-45-67' ?></option>
                                <option value="phone" data-mask="0000-0000" data-reverse="0" <?php selected( $mask_type, 'phone' ); ?>><?php echo __( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) . ': 1234-5678' ?></option>
                                <option value="phone2" data-mask="(00) 0000-0000" data-reverse="0" <?php selected( $mask_type, 'phone2' ); ?>><?php echo __( 'Phone with Code Area', WPC_CLIENT_TEXT_DOMAIN ) . ': (12) 3456-7890' ?></option>
                                <option value="phone3" data-mask="(000) 000-0000" data-reverse="0" <?php selected( $mask_type, 'phone3' ); ?>><?php echo __( 'Phone with Code Area', WPC_CLIENT_TEXT_DOMAIN ) . ': (123) 456-7890' ?></option>
                                <option value="cpf" data-mask="000.000.000-00" data-reverse="1" <?php selected( $mask_type, 'cpf' ); ?>><?php echo __( 'CPF', WPC_CLIENT_TEXT_DOMAIN ) . ': 123.456.789-01' ?></option>
                                <option value="money" data-mask="#.##0,00" data-reverse="1" <?php selected( $mask_type, 'money' ); ?>><?php echo __( 'Money', WPC_CLIENT_TEXT_DOMAIN ) . ': 1.234,00' ?></option>
                                <option value="ip" data-mask="099.099.099.099" data-reverse="0" <?php selected( $mask_type, 'ip' ); ?>><?php echo __( 'IP Address', WPC_CLIENT_TEXT_DOMAIN ) . ': ' . $_SERVER['REMOTE_ADDR'] ?></option>
                                <option value="percent" data-mask="#0,00%" data-reverse="1" <?php selected( $mask_type, 'percent' ); ?>><?php echo __( 'Percent', WPC_CLIENT_TEXT_DOMAIN ) . ': 100,00%' ?></option>
                                <option value="custom" <?php selected( $mask_type, 'custom' ); ?>><?php echo __( 'Custom', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            </select><br />
                            <input type="text" name="custom_field[mask]" id="mask_value" value="<?php echo isset( $custom_field['mask'] ) ? $custom_field['mask'] : ''; ?>" /> <span class="description mask_desc"><?php _e( 'For custom mask type', WPC_CLIENT_TEXT_DOMAIN ); ?></span><br />
                            <input type="hidden" name="custom_field[mask_reverse]" value="0" />
                            <label>
                                <input type="checkbox" name="custom_field[mask_reverse]" id="mask_reverse" value="1" <?php checked( !empty( $custom_field['mask_reverse'] ) ); ?> />
                                <?php _e( 'Using a reversible mask ', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </label><br />
                            <span class="description mask_desc">
                                A - <?php _e( 'Numbers and Letters', WPC_CLIENT_TEXT_DOMAIN ); ?><br />
                                S - <?php _e( 'Only Letters', WPC_CLIENT_TEXT_DOMAIN ); ?><br />
                                0 - <?php _e( 'Only Numbers', WPC_CLIENT_TEXT_DOMAIN ); ?><br />
                                9 - <?php _e( 'Only Numbers( can be optional )', WPC_CLIENT_TEXT_DOMAIN ); ?><br />
                                # - <?php _e( 'Recursive Numbers', WPC_CLIENT_TEXT_DOMAIN ); ?><br />
                            </span>
                        </td>
                    </tr>

                    <tr id="wpc_field_view">
                        <th>
                            <label><?php _e( 'Field View', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </th>
                        <td>
                            <?php
                                $wpc_roles = array(
                                    'administrator' => __( 'Administrators', WPC_CLIENT_TEXT_DOMAIN ),
                                    'admin'         => sprintf( __( 'WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['p'] ),
                                    'manager'       => sprintf( __( 'WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                                    'client'        => sprintf( __( 'WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'staff'         => sprintf( __( 'WPC-%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                );
                                $all_places = array(
                                    'admin_add_client'  => sprintf( __( 'Display on Admin Add %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'admin_add_staff'   => sprintf( __( 'Display on Admin Add %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                    'admin_edit_client' => sprintf( __( 'Display on Admin Edit %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'admin_edit_staff'  => sprintf( __( 'Display on Admin Edit %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                    'user_add_client'   => sprintf( __( 'Display on Registration %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'user_add_staff'    => sprintf( __( 'Display on Add %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                    'user_edit_client'  => sprintf( __( 'Display on %s Profile Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'user_profile_staff' => sprintf( __( 'Display on %s Profile Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                    'user_edit_staff'    => sprintf( __( 'Display on Edit %s Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                    'user_view_staff'    => sprintf( __( 'Display in %s Directory Table', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                                    'admin_screen'       => sprintf( __( 'Display on %s Page in Screen Options', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                );
                                $all_variants = array(
                                    'view' => __( 'View', WPC_CLIENT_TEXT_DOMAIN ),
                                    'edit' => __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ),
                                    'hide' => __( 'Hide', WPC_CLIENT_TEXT_DOMAIN ),
                                );
                            ?>

                            <table cellspacing="6" id="wpc_cf_view">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <?php foreach( $wpc_roles as $role => $title ) {
                                            echo '<th class="wpc_td_' . $role . '">' . $title . '</th>';
                                        } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach( $all_places as $place => $name_place ) {
                                        $variants = $all_variants ;
                                        if ( strpos( $place, 'add' ) !== false || 'admin_screen' == $place ) {
                                            array_shift($variants);
                                        }
                                        if ( 'user_view_staff' == $place ) {
                                            unset($variants['edit']);
                                        }

                                        $class = '';
                                        if( strpos( $place, 'client' ) !== false ) {
                                            $class .= ' client_row';
                                        } else if( strpos( $place, 'staff' ) !== false ) {
                                            $class .= ' staff_row';
                                        } else {
                                            $class .= ' client_row';
                                        }

                                        ?>
                                        <tr valign="top" class="wpc_tr_<?php echo $place . $class; ?>">
                                            <th scope="row">
                                                <?php echo $name_place ?>
                                            </th>

                                            <?php foreach( $wpc_roles as $role=>$name_role ) {
                                                $empty = true;
                                                if( 'admin_' == substr( $place, 0, 6 ) && in_array( $role, array( 'administrator', 'admin', 'manager' ) )
                                                    || 'user_' == substr( $place, 0, 5 ) && in_array( $role, array( 'client', 'staff' ) ) ) {

                                                    if( $role == 'staff' ) {
                                                        if( $place == 'user_profile_staff' ) {
                                                            $empty = false;
                                                        }
                                                    } else if( $role == 'client' ) {
                                                         if( $place != 'user_profile_staff' ) {
                                                             $empty = false;
                                                         }
                                                    } else {
                                                        $empty = false;
                                                    }
                                                }

                                                echo '<td class="wpc_td_' . $role . '">';

                                                if ( !$empty ) { ?>
                                                    <select name="custom_field[view][<?php echo $place ?>][<?php echo $role ?>]">
                                                    <?php foreach( $variants as $variant=>$name_variant ) {

                                                        $choosed = ( !empty( $custom_field['view'][ $place ][ $role ] )
                                                                && in_array( $custom_field['view'][ $place ][ $role ], array_keys( $variants ) ) )
                                                                ?  $custom_field['view'][ $place ][ $role ] : 'edit';

                                                        $selected = selected( $choosed, $variant, false);

                                                        echo '<option value="' . $variant . '" ' . $selected . '>'
                                                            . $name_variant . '</option>';

                                                    } ?>
                                                    </select>
                                                <?php }
                                                echo '</td>';
                                            } ?>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button-primary" name="submit" value="<?php _e( 'Save Custom Field', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                </p>
            </form>

        </div>
    </div>

</div>