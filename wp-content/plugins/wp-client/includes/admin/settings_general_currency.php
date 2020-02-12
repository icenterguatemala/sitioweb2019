<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$wpc_currency = WPC()->get_settings( 'currency' );

$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'Currency Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

<style type="text/css">
    #wpc_update_settings {
        display: none;
    }

</style>


<a class="button-primary add_currency" href="javascript:void(0);"><?php _e( 'Add Currency', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
<br />
<br />

<table class="wp-list-table widefat fixed currency_table" cellspacing="0">
        <thead>
        <tr>
            <th scope="col" id="default" class="manage-column column-default" style="width: 50px;"><?php _e( 'Default', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
            <th scope="col" id="title" class="manage-column column-title" style=""><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
            <th scope="col" id="code" class="manage-column column-code" style=""><?php _e( 'Alphabetic Code', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
            <th scope="col" id="symbol" class="manage-column column-symbol" style="width: 50px;"><?php _e( 'Symbol', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
            <th scope="col" id="align" class="manage-column column-align" style="width: 250px;"><?php _e( 'Align', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php foreach( $wpc_currency as $key=>$val ) { ?>
            <tr>
                <td align="center">
                    <input type="radio" name="default" class="wpc_default" value="<?php echo $key; ?>" <?php checked( $val['default'], 1 ); ?> />
                </td>
                <td>
                    <strong><span class="currency_title"><?php echo $val['title']; ?></span></strong>
                    <div class="row-actions">
                        <span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="<?php echo $key; ?>">Edit</a> | </span>
                        <span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="<?php echo $key; ?>">Delete Permanently</a></span>
                    </div>
                </td>
                <td>
                    <span class="currency_code"><?php echo $val['code']; ?></span>
                </td>
                <td>
                    <span id="currency_symbol"><?php echo $val['symbol']; ?></span>
                </td>
                <td>
                    <span id="currency_align"><?php echo ucfirst( $val['align'] ); ?></span>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>


<script type="text/javascript">
    jQuery(document).ready(function() {
        var wpc_busy = 0;

        jQuery('.add_currency').click(function() {
            jQuery('.wpc_add_row').remove();
            jQuery('.currency_table tbody').prepend('<tr class="wpc_add_row">' +
                '<td>' +
                '<input type="radio" name="default_check" class="wpc_default_new" value="1" />' +
                '</td>' +
                '<td>' +
                '<input type="text" name="title" class="wpc_title" value="" />' +
                '</td>' +
                '<td>' +
                '<input type="text" name="code" class="wpc_code" value="" />' +
                '</td>' +
                '<td>' +
                '<input type="text" name="symbol" class="wpc_symbol" value="" />' +
                '</td>' +
                '<td>' +
                '<select name="align" class="wpc_align">' +
                '<option value="left"><?php _e( 'Left', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                '<option value="right"><?php _e( 'Right', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                '</select>' +
                '<a href="javascript:void(0);" class="wpc_remove_currency_row"><?php echo esc_js( __( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ); ?></a>' +
                '<a href="javascript:void(0);" class="wpc_add_currency_row"><?php echo esc_js( __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) ); ?></a>' +
                '</td>' +
                '</tr>');
        });

        jQuery('.currency_table').on('click', '.wpc_remove_currency_row', function() {
            jQuery('.wpc_add_row').remove();
        });

        jQuery('.currency_table').on('click', '.wpc_add_currency_row', function() {
            if( !wpc_busy ) {
                wpc_busy = 1;
                var wpc_default = jQuery(this).parents('.currency_table tr').find('.wpc_default_new:checked').length;
                var title = jQuery(this).parents('.currency_table tr').find('.wpc_title').val();
                if( title.length == 0 ) {
                    alert('<?php echo esc_js( __( 'Title is empty', WPC_CLIENT_TEXT_DOMAIN ) ); ?>');
                    return false;
                }
                var code = jQuery(this).parents('.currency_table tr').find('.wpc_code').val();
                if( code.length == 0 ) {
                    alert('<?php echo esc_js( __( 'Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ) ); ?>');
                    return false;
                }
                code = code.toUpperCase();

                var symbol = jQuery(this).parents('.currency_table tr').find('.wpc_symbol').val();
                if( symbol.length == 0 ) {
                    alert('<?php echo esc_js( __( 'Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ) ); ?>');
                    return false;
                }
                var align = jQuery(this).parents('.currency_table tr').find('.wpc_align').val();

                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=add&default=' + wpc_default + '&title=' + title + '&code=' + code + '&symbol=' + symbol + '&align=' + align,
                    success  : function( data ){
                        if( data.status ) {
                            jQuery('.wpc_add_row').remove();
                            jQuery('.currency_table tbody').prepend('<tr>' +
                                '<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + data.message + '" ' + ( wpc_default == 1 ? 'checked="checked"' : '' ) + ' />' +
                                '</td>' +
                                '<td>' +
                                '<strong><span class="currency_title">' + title + '</span></strong>' +
                                '<div class="row-actions">' +
                                '<span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="' + data.message + '">Edit</a> | </span>' +
                                '<span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="' + data.message + '">Delete Permanently</a></span>' +
                                '</div>' +
                                '</td>' +
                                '<td>' +
                                '<span class="currency_code">' + code + '</span>' +
                                '</td>' +
                                '<td>' +
                                '<span id="currency_symbol">' + symbol + '</span>' +
                                '</td>' +
                                '<td>' +
                                '<span id="currency_align">' + align.charAt(0).toUpperCase() + align.substr(1) + '</span>' +
                                '</td>' +
                                '</tr>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
            return false;
        });

        jQuery('.currency_table').on('click', '.wpc_edit_currency_row', function() {
            if( !wpc_busy ) {
                wpc_busy = 1;
                var id = jQuery(this).data('id');

                var wpc_default = jQuery(this).parents('.currency_table tr').find('.wpc_default:checked').length;
                var title = jQuery(this).parents('.currency_table tr').find('.wpc_title').val();
                if( title.length == 0 ) {
                    alert('<?php echo esc_js( __( 'Title is empty', WPC_CLIENT_TEXT_DOMAIN ) ); ?>');
                    return false;
                }
                var code = jQuery(this).parents('.currency_table tr').find('.wpc_code').val();
                if( code.length == 0 ) {
                    alert('<?php echo esc_js( __( 'Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ) ); ?>');
                    return false;
                }
                code = code.toUpperCase();

                var symbol = jQuery(this).parents('.currency_table tr').find('.wpc_symbol').val();
                if( symbol.length == 0 ) {
                    alert('<?php echo esc_js( __( 'Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ) ); ?>');
                    return false;
                }
                var align = jQuery(this).parents('.currency_table tr').find('.wpc_align').val();
                var obj = jQuery(this);
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=edit&id=' + id + '&default=' + wpc_default + '&title=' + title + '&code=' + code + '&symbol=' + symbol + '&align=' + align,
                    success  : function( data ){
                        if( data.status ) {
                            obj.parents('.currency_table tr').html('<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + id + '" ' + ( data.message.wpc_default == '1' ? 'checked="checked"' : '' ) + ' />' +
                                '</td>' +
                                '<td>' +
                                '<strong><span class="currency_title">' + title + '</span></strong>' +
                                '<div class="row-actions">' +
                                '<span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="' + id + '">Edit</a> | </span>' +
                                '<span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="' + id + '">Delete Permanently</a></span>' +
                                '</div>' +
                                '</td>' +
                                '<td>' +
                                '<span class="currency_code">' + code + '</span>' +
                                '</td>' +
                                '<td>' +
                                '<span id="currency_symbol">' + symbol + '</span>' +
                                '</td>' +
                                '<td>' +
                                '<span id="currency_align">' + align.charAt(0).toUpperCase() + align.substr(1) + '</span>' +
                                '</td>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
            return false;
        });

        jQuery('.currency_table').on('click', '.wpc_back_currency_row', function() {
            var id = jQuery(this).data('id');
            if( !wpc_busy ) {
                wpc_busy = 1;
                var obj = jQuery(this);
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=get_data&id=' + id,
                    success  : function( data ){
                        if( data.status ) {
                            obj.parents('.currency_table tr').html('<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + id + '" ' + ( data.message['default'] == '1' ? 'checked="checked"' : '' ) + ' />' +
                                '</td>' +
                                '<td>' +
                                '<strong><span class="currency_title">' + data.message.title + '</span></strong>' +
                                '<div class="row-actions">' +
                                '<span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="' + id + '">Edit</a> | </span>' +
                                '<span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="' + id + '">Delete Permanently</a></span>' +
                                '</div>' +
                                '</td>' +
                                '<td>' +
                                '<span class="currency_code">' + data.message.code.toUpperCase() + '</span>' +
                                '</td>' +
                                '<td>' +
                                '<span id="currency_symbol">' + data.message.symbol + '</span>' +
                                '</td>' +
                                '<td>' +
                                '<span id="currency_align">' + data.message.align.charAt(0).toUpperCase() + data.message.align.substr(1) + '</span>' +
                                '</td>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
            return false;
        });

        jQuery('.currency_table').on('click', '.delete_currency', function() {
            var id = jQuery(this).data('id');
            if( jQuery(this).parents('.currency_table tr').find('.wpc_default:checked').length ) {
                alert("<?php echo esc_js( __( "You can't remove currency with default mark", WPC_CLIENT_TEXT_DOMAIN ) ); ?>");
                return;
            }
            var obj = jQuery(this);
            if( id.length > 0 && !wpc_busy ) {
                wpc_busy = 1;
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=delete&id=' + id,
                    success  : function( data ){
                        if( data.status ) {
                            obj.parents('.currency_table tr').remove();
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
        });

        jQuery('.currency_table').on('click', '.edit_currency', function() {
            var id = jQuery(this).data('id');
            if( id.length > 0 && !wpc_busy ) {
                wpc_busy = 1;
                var obj = jQuery(this);
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=get_data&id=' + id,
                    success  : function( data ){
                        if( data.status ) {
                            jQuery('.wpc_add_row').remove();
                            obj.parents('.currency_table tr').html('<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + id + '" ' + ( data.message['default'] == '1' ? 'checked="checked"' : '' ) + ' />' +
                                '</td>' +
                                '<td>' +
                                '<input type="text" name="title" class="wpc_title" value="' + data.message.title + '" />' +
                                '</td>' +
                                '<td>' +
                                '<input type="text" name="code" class="wpc_code" value="' + data.message.code.toUpperCase() + '" />' +
                                '</td>' +
                                '<td>' +
                                '<input type="text" name="symbol" class="wpc_symbol" value="' + data.message.symbol + '" />' +
                                '</td>' +
                                '<td>' +
                                '<select name="align" class="wpc_align">' +
                                '<option value="left" ' + ( data.message.align == 'left' ? 'selected="selected"' : '' ) + '><?php _e( 'Left', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                                '<option value="right" ' + ( data.message.align == 'right' ? 'selected="selected"' : '' ) + '><?php _e( 'Right', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                                '</select>' +
                                '<a href="javascript:void(0);" class="wpc_back_currency_row" data-id="' + id + '"><?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?></a>' +
                                '<a href="javascript:void(0);" class="wpc_edit_currency_row" data-id="' + id + '"><?php _e( 'Save', WPC_CLIENT_TEXT_DOMAIN ); ?></a>' +
                                '</td>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
        });

        jQuery('.currency_table').on('click', '.wpc_default', function() {
            var id = jQuery(this).val();
            if( id.length > 0 && !wpc_busy ) {
                wpc_busy = 1;
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=set_default&id=' + id,
                    success  : function( data ){
                        if( !data.status ) {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
        });

    });
</script>