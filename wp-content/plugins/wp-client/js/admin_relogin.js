jQuery(document).ready(function() {
    var style_array = {
        '.wpc_admin_login_block' : {
            'padding' : '5px',
            'line-height': '20px',
            'font-size' : '12px',
            'background': '#fff',
            'border': '1px solid #eee',
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'z-index' : '999999',
            'box-sizing' : 'content-box'
        },
        '.wpc_admin_return_button' : {
            'display' : 'block',
            'float' : 'right',
            'background': '#2ea2cc',
            'border-color' : '#0074a2',
            'box-sizing' : 'border-box',
            'border-radius' : '3px',
            'white-space' : 'nowrap',
            'font-size' : '9px',
            'line-height' : '20px',
            'height' : '20px',
            'margin' : '0',
            'padding' : '0 10px 1px',
            'cursor' : 'pointer',
            'border-width' : '1px',
            'border-style' : 'solid',
            '-webkit-appearance' : 'none'
        },
        '.wpc_select_client_relogin' : {
            'height' : '18px',
            'margin' : '0 5px',
            'font-size' : '12px'
        }
    };

    var user_select = '';
    if( typeof wpc_var.clients_list != 'undefined' ) {
        if( wpc_var.clients_list.length ) {
            for( i = 0; i < wpc_var.clients_list.length; i++ ) {
                user_select += '<option value="' + wpc_var.clients_list[ i ].ID + '" ' +
                ( ( typeof wpc_var.current_user_id != 'undefined' && wpc_var.current_user_id == wpc_var.clients_list[ i ].ID ) ? 'selected="selected"' : '' ) + '>' + wpc_var.clients_list[ i ].login + '</option>';
            }
        }
        user_select = ' <select class="wpc_select_client_relogin">' + user_select + '</select> ';
    }

    jQuery('body').prepend('<div class="wpc_admin_login_block">' + wpc_var.message +
        user_select +
        ' <input type="button" class="button-primary wpc_admin_return_button" value="' + wpc_var.button_value + '" /></div>');

    for( block in style_array ) {
        for( style_name in style_array[ block ] ) {
            jQuery( block ).css( style_name, style_array[ block ][ style_name ] );
        }
    }

    jQuery('body').on( 'click', '.wpc_admin_return_button', function() {
        jQuery.ajax({
            type     : 'POST',
            dataType : 'json',
            url      : wpc_var.ajax_url,
            data: 'action=wpc_return_to_admin_panel&secure_key=' + wpc_var.secure_key,
            success: function( data ){
                if( data.status ) {
                    window.location = data.message;
                } else {
                    alert( data.message );
                }
            }
        });
    });

    jQuery(document).on( 'change', '.wpc_select_client_relogin', function() {
        var user_id = jQuery(this).val();
        jQuery.ajax({
            type     : 'POST',
            dataType : 'json',
            url      : wpc_var.ajax_url,
            data: 'action=wpc_return_to_admin_panel&secure_key=' + wpc_var.secure_key + '&relogin=' + user_id + '&page_id=' + wpc_var.page_id,
            success: function( data ){
                if( data.status ) {
                    window.location = data.message;
                } else {
                    alert( data.message );
                }
            }
        });
    });

});