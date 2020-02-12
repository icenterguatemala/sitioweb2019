var unknown_text = sure_delete_text = admin_url = wp_nonce = '';

if (undefined !== typeof (wpc_email_sending_data) ) {
//    var all_data = ['unknown_text', 'sure_delete_text', 'admin_url', 'wp_nonce'];
//    all_data.forEach(function(e) {
//
//    });
    if (undefined !== typeof (wpc_email_sending_data.unknown_text)) {
        unknown_text = wpc_email_sending_data.unknown_text;
    }
    if (wpc_email_sending_data.sure_delete_text) {
        sure_delete_text = wpc_email_sending_data.sure_delete_text;
    }
    if (wpc_email_sending_data.admin_url) {
        admin_url = wpc_email_sending_data.admin_url;
    }
    if (wpc_email_sending_data.wp_nonce) {
        wp_nonce = wpc_email_sending_data.wp_nonce;
    }
}

jQuery(document).ready(function () {
    var nonce = jQuery('input[name=wpc_ajax_key]').val();

    var container_blocks = jQuery( '#wpc_save_button, #wpc_all_settings, #wpc_email_testing_block' );

    // On/Off waiting of ajax
    function set_loading( bool ) {
        if ( bool ) {
            jQuery( 'body' ).css( 'cursor', 'wait' );
            container_blocks.css( 'opacity', '0.5' );
            jQuery( '.wpc_ajax_overflow' ).show();
        } else {
            jQuery( '.wpc_ajax_overflow' ).hide();
            container_blocks.css( 'opacity', '1' );
            jQuery('body').css( 'cursor', 'default' );
        }
    }

    // Show notice
    function wpc_msg_add_notice( notice, type ) {
        jQuery('#wpc_block_message').append('<span id="wpc_msg_noitce" class="wpc_msg_' + type + '">' + notice + '</span>');

        setTimeout( function() {
            jQuery( '#wpc_msg_noitce' ).fadeOut( 1500, function() {
                jQuery( this ).remove();
            });
        }, 2500 );

    }

    // Show settings of profile
    function show_settings( profile_id, method ) {
        var additional_options;
        var data_method = '';
        if ( !profile_id ) {
            if ( undefined === method ) {
                var first_set = jQuery( '#wpc_first_set' );
                var first_set_val = first_set.val();

                if ( '-1' === first_set_val ) {
                    first_set.addClass('wpc_error');
                    return false;
                } else {
                    first_set.removeClass('wpc_error');
                }
                first_set.val( '-1' );

                additional_options = '&type=' + first_set_val;
                data_method = first_set_val;
            } else {
                data_method = method;
                additional_options = '&type=' + method;
            }
        } else {
            additional_options = '&profile_id=' + profile_id;
        }

        jQuery('#wpc_list li').removeClass( 'wpc_item_active' );

        //show loader
        set_loading( true );

        jQuery.ajax({
            type: 'POST',
            url: admin_url + 'admin-ajax.php',
            data: 'action=wpc_get_email_profile' + additional_options + '&nonce=' + nonce,
            dataType: "json",
            success: function (data) {
                set_loading( false );

                if ( data.status ) {
                    jQuery( '#wpc_all_settings' ).html(data.html);

                    //set name of Sending Method by value
                    var type = jQuery('#email_sending_type_val').val();

                    if ( undefined === type ) {
                        type = '';
                    }
                    var type_name = jQuery('#wpc_first_set option[value="' + type + '"]').text();
                    jQuery( '#email_sending_type' ).text( type_name );

                    if ( !profile_id && undefined === method ) {
                        //add li to list for new items
                        var new_li = '<li class="wpc_item_active" data-id="' + jQuery( '#email_sending_item_id' ).val() + '"'
                                + ' data-method="' + data_method + '">'
                                + '<div class="dashicons dashicons-no-alt"></div>'
                                + jQuery( '#profile_name' ).val() + ' (' + type_name + ')</li>';
                        jQuery('#wpc_list').prepend( new_li );

                        //save new profile
                        //jQuery('#wpc_save').trigger( 'click' );
                    }
                } else {
                    wpc_msg_add_notice( data.message, 'error' );
                }

            },
            error: function (data) {
                set_loading( false );
                wpc_msg_add_notice(unknown_text, 'error');
            }
        });

    }

    // Hide/Show Test Email
    jQuery('#wpc_email_testing_block').on( 'click', '#wpc_show_test_link', function() {
        if ( jQuery('#wpc_hide_block').is( ":visible" ) ) {
            jQuery('#wpc_show_test_link span').text(' >>');
        } else {
            jQuery('#wpc_show_test_link span').text(' <<');
        }
        jQuery('#wpc_hide_block').toggle( 1000 );
    });

    //Add new profile
    jQuery( '#wpc_main_form' ).on( 'click', '#wpc_add_item', function() {
        show_settings( '' );
    });

    //Select profile
    jQuery( '#wpc_main_form' ).on( 'click', '#wpc_list li', function() {
        var method = jQuery( this ).data( 'method' );
        if ( method ) {
            show_settings( '', method );
        } else {
            show_settings( jQuery( this ).data( 'id' ) );
        }
        jQuery( this ).addClass( 'wpc_item_active' );
    });

    jQuery( '#wpc_list li:first' ).trigger( 'click' );

    //Save profile
    jQuery( '#wpc_main_form' ).on( 'click', '#wpc_save', function() {
        //scroll up
        jQuery( 'html, body' ).animate({
            scrollTop: jQuery('#wpc_block_message').offset().top - 100
            }, 500);

        var title = jQuery( '#profile_name' );
        var title_val = title.val();

        if ( '' === title_val ) {
            title.addClass('wpc_error');
            return false;
        } else {
            title.removeClass('wpc_error');
        }

        //show loader
        set_loading( true );

        jQuery.ajax({
            type: 'POST',
            url: admin_url + 'admin-ajax.php',
            data: 'action=wpc_save_email_profile&nonce=' + nonce + '&' + jQuery('#wpc_main_form').serialize(),
            dataType: "json",
            success: function (data) {
                set_loading( false );

                if ( data.status ) {
                    jQuery('#wpc_list li.wpc_item_active').removeData('method').removeAttr('data-method');
                    var new_name = title_val + ' (' + jQuery('#email_sending_type').text() + ')';
                    jQuery('#wpc_list li.wpc_item_active').contents().last()[0].textContent
                            = new_name;

                    if ( data.text_button ) {
                        jQuery('#wpc_save').val( data.text_button );

                        //new option to Core Settings
                        var new_option = new Option( new_name, jQuery('#email_sending_item_id').val() );
                        var selectbox = jQuery('#wpc_email_profile_core');
                        selectbox[0].options[selectbox[0].options.length] = new_option;
                    }
                    wpc_msg_add_notice( data.message, 'updated' );
                } else {
                    wpc_msg_add_notice( data.message, 'error' );
                }

            },
            error: function (data) {
                set_loading( false );
                wpc_msg_add_notice(unknown_text, 'error');
            }
        });

    });

    //Delete profile
    jQuery( '#wpc_main_form' ).on( 'click', '.dashicons-no-alt', function(e) {

        if (!confirm(sure_delete_text)) {
            return false;
        }

        //scroll up
        jQuery( 'html, body' ).animate({
            scrollTop: jQuery('#wpc_block_message').offset().top - 100
            }, 500);

        var obj_li = jQuery( this ).parent();

        var profile_id = obj_li.data( 'id' );

        jQuery.ajax({
            type: 'POST',
            url: admin_url + 'admin-ajax.php',
            data: 'action=wpc_delete_email_profile&nonce=' + nonce + '&profile_id=' + profile_id,
            dataType: "json",
            success: function (data) {
                jQuery( obj_li ).hide( 'slow', function() {
                    //if this li is active
                    if ( obj_li.hasClass( 'wpc_item_active' ) ) {
                        var next_li = jQuery( this ).prev('li');
                        if ( !next_li.length ) {
                            next_li = jQuery( this ).next('li');
                        }
                        if ( next_li.length ) {
                            next_li.trigger( 'click' );
                        } else {
                            jQuery( '#wpc_all_settings' ).html( '' );
                        }
                    }

                    jQuery( this ).remove();
                    jQuery('#wpc_email_profile_core option[value=' + profile_id + ']').remove();
                });

                if ( data.status ) {
                    wpc_msg_add_notice( data.message, 'updated' );
                } else {
                    wpc_msg_add_notice( data.message, 'error' );
                }

            },
            error: function (data) {
                wpc_msg_add_notice(unknown_text, 'error');
            }
        });

        e.stopPropagation();

    });

    // Send test message
    jQuery("#wpc_send_test_email").click(function() {
        var data_feilds = jQuery(this).parents('form').serializeObject();

        jQuery("#ajax_result").html('').show().css('display', 'inline').html('<span class="ajax_loading"></span>');

        jQuery(".wpc_ajax_loading").show('fast');
        jQuery.ajax({
            type: "POST",
            url: admin_url + 'admin-ajax.php',
            data : {
                action : 'wpc_send_test_email',
                security: wp_nonce,
                feilds : data_feilds
            },
            dataType: "json",
            success: function(data){
                jQuery(".wpc_ajax_loading").hide('fast');
                if(data.status) {
                    jQuery("#ajax_result").css('color', 'green');

                    jQuery("#ajax_result").html(data.message);

                    setTimeout(function() {
                        jQuery("#ajax_result").fadeOut(1500);
                    }, 2500);

                } else {
                    data.message = data.message.replace( /(\\r\\n)|(\\n\\r)|(\\n\\t)|(\\t)|(\\n)/g, '<br>' );
                    data.message = data.message.replace( /\\"/g, '"' );
                    data.message = data.message.replace( /\\\//g, '/' );

                    jQuery("#ajax_result").css('color', 'red');
                    jQuery("#ajax_result").html(data.message);
                }

            },
            error: function(data) {
                jQuery(".wpc_ajax_loading").hide('fast');
                jQuery("#ajax_result").css('color', 'red');
                jQuery("#ajax_result").html('Unknown error' );
                setTimeout(function() {
                    jQuery("#ajax_result").fadeOut(1500);
                }, 2500);
            }
        });

    });

    // Show settings for NTLM
    jQuery( '.wpc_tab_container_block' ).on( 'change', '#email_sending_auth_type', function() {
        if ( 'NTLM' === jQuery(this).val() ) {
            jQuery( '.wpc_email_for_ntlm' ).css('display','table-row');
        } else {
            jQuery( '.wpc_email_for_ntlm' ).css('display','none');
        }
    });


    //Save Email Sending setting for Core
    jQuery( 'table' ).on( 'change', '#wpc_email_profile_core', function() {

        var profile_id = jQuery( this ).val();

        if ( undefined === profile_id ) {
            return false;
        }

        //show loader
        jQuery("#ajax_loading_for_select").html('').show().css('display', 'inline').html('<span class="wpc_ajax_loading"></span>');

        jQuery.ajax({
            type: 'POST',
            url: admin_url + 'admin-ajax.php',
            data: 'action=wpc_save_selected_email_profile&nonce=' + nonce + '&area=core&profile_id=' + profile_id,
            dataType: "json",
            success: function (data) {
                if ( data.status ) {
                    jQuery("#ajax_loading_for_select").css('color', 'green');
                } else {
                    jQuery("#ajax_loading_for_select").css('color', 'red');
                }
                jQuery("#ajax_loading_for_select").html(data.message);

                setTimeout(function() {
                    jQuery("#ajax_loading_for_select").fadeOut(1500);
                }, 2500);
            },
            error: function (data) {
                jQuery("#ajax_loading_for_select").css('color', 'red').html(unknown_text);

                setTimeout(function() {
                    jQuery("#ajax_loading_for_select").fadeOut(1500);
                }, 2500);
            }
        });

    });

    (function(jQuery){
        jQuery.fn.serializeObject = function(){

            var self = this,
                json = {},
                push_counters = {},
                patterns = {
                    "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                    "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
                    "push":     /^$/,
                    "fixed":    /^\d+$/,
                    "named":    /^[a-zA-Z0-9_]+$/
                };


            this.build = function(base, key, value){
                base[key] = value;
                return base;
            };

            this.push_counter = function(key){
                if(push_counters[key] === undefined){
                    push_counters[key] = 0;
                }
                return push_counters[key]++;
            };

            jQuery.each(jQuery(this).serializeArray(), function(){

                // skip invalid keys
                if(!patterns.validate.test(this.name)){
                    return;
                }

                var k,
                    keys = this.name.match(patterns.key),
                    merge = this.value,
                    reverse_key = this.name;

                while((k = keys.pop()) !== undefined){

                    // adjust reverse_key
                    reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                    // push
                    if(k.match(patterns.push)){
                        merge = self.build([], self.push_counter(reverse_key), merge);
                    }

                    // fixed
                    else if(k.match(patterns.fixed)){
                        merge = self.build([], k, merge);
                    }

                    // named
                    else if(k.match(patterns.named)){
                        merge = self.build({}, k, merge);
                    }
                }

                json = jQuery.extend(true, json, merge);
            });

            return json;
        };
    })(jQuery);


});