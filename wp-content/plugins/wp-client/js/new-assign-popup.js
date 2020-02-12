var wpc_marks_type = 'checkbox';
var checkbox_session = new Array();
var wpc_input_ref = '';
var wpc_readonly = false;
var data_type = '';
var wpc_current_page = '';
var wpc_object_id = 0;
var data_send_ajax = 0;
var data_callback = '';
var wpc_page_num = 1;
var full_ids_list = new Array();
var checkbox_name = '';
var included_ids = new Array();
var additional_vars = new Array();

var opened_link = false;
var ajax_ref_array = new Array();
var bulk_action_runned = false;

jQuery( document ).ready( function() {
    wpc_current_page = ( typeof wpc_popup_var != 'undefined' && wpc_popup_var.hasOwnProperty('current_page') ) ? wpc_popup_var.current_page : '';

    jQuery('form').submit(function(){
        if( bulk_action_runned ) {
            return false;
        }

        for( key in ajax_ref_array ) {
            if( jQuery(this).find( '#' + ajax_ref_array[ key ] ).length ) {
                jQuery(this).find( '#' + ajax_ref_array[ key ] ).remove();
            }
        }
        return true;
    });


    jQuery('body').on( 'click', ".wpc_fancybox_link", function() {

        // init popup links
        jQuery(".wpc_fancybox_link").each(function() {
            if( jQuery(this).hasClass( 'wpc_assign_popup_initialized' ) ) {
                return true;
            }

            jQuery(this).addClass( 'wpc_assign_popup_initialized' );

            data_send_ajax = jQuery(this).data('ajax');
            if( data_send_ajax ) {
                wpc_input_ref = jQuery(this).data('input');
                if( typeof jQuery(this).data('id') != 'undefined' ) {
                    wpc_input_ref += '_' + jQuery(this).data('id');
                }
                ajax_ref_array.push( wpc_input_ref );
            }

            var obj = jQuery(this);
            var href = obj.attr('href');
            var p_title = '';
            if( typeof wpc_popup_title[ href.substring(1) ][obj.data('type')] != 'undefined' ) {
                p_title = wpc_popup_title[ href.substring(1) ][obj.data('type')];
            }
            obj.shutter_box({
                view_type       : 'lightbox',
                type            : 'inline',
                width           : '1070px',
                href            : href,
                title           : p_title,
                self_init       : false,
                onClose         : function() {
                    opened_link = false;
                }
            });
        });

        var obj = jQuery(this);
        opened_link = obj;

        var href = jQuery(this).attr('href');
        wpc_input_ref = jQuery(this).data('input');
        data_type = jQuery(this).data('type');
        data_send_ajax = jQuery(this).data('ajax');
        data_callback = jQuery(this).data('callback');

        if( data_callback != '' && typeof( window[ data_callback + '_link_click' ] ) == 'function' ) {
            window[ data_callback + '_link_click' ].apply( this );
        }

        if( jQuery(this).data('id') != 'undefined' ) {
            wpc_object_id = jQuery(this).data('id');
        } else {
            wpc_object_id = 0;
        }
        if( typeof jQuery(this).data('id') != 'undefined' ) {
            wpc_input_ref += '_' + jQuery(this).data('id');
        }
        if( jQuery(this).data('marks') == 'radio' || jQuery(this).data('marks') == 'checkbox' ) {
            wpc_marks_type = jQuery(this).data('marks');
        } else {
            wpc_marks_type = 'checkbox';
        }
        var include = jQuery("#" + wpc_input_ref).data('include');
        if( typeof include != 'undefined' && include.toString().length ) {
            included_ids = jQuery("#" + wpc_input_ref).data('include').toString().split(',');
        } else {
            included_ids = new Array();
        }

        wpc_readonly = jQuery("#" + wpc_input_ref).attr('readonly') == 'readonly' ? 1 : 0;

        obj.shutter_box('showPreLoader');

        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.admin_url + "admin-ajax.php",
            data: {
                action : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' ) + 'get_popup_pagination_data',
                marks_type : wpc_marks_type,
                page : 1,
                goto : 'first',
                search : "",
                data_type : data_type,
                open_popup : true,
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page,
                readonly : wpc_readonly
            },
            dataType: "json",
            success: function(data) {
                if(data.html) {
                    jQuery(href).find(".wpc_inside").html( data.html );
                    jQuery(href).wpcResetPopup();

                    full_ids_list = data.ids_list;
                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                jQuery(href).find(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                jQuery(href).find(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        jQuery(href).find(".wpc_page_num").html( data.page );
                    } else {
                        jQuery(href).find(".wpc_page_num").html('');
                    }

                    if( jQuery("#" + wpc_input_ref).val().length ) {
                        checkbox_session = jQuery("#" + wpc_input_ref).val().split(',');
                    }

                    for( key in checkbox_session ) {
                        if( checkbox_session.hasOwnProperty( key ) ) {
                            jQuery(href).find('.wpc_inside input[type="' + wpc_marks_type + '"][value="' + checkbox_session[ key ] + '"]').prop('checked', true);
                        }
                    }

                    if( checkbox_session.length != 0 && checkbox_session.length == data.count ) {
                        jQuery(href).find('.wpc_select_all').prop('checked', true);
                    } else if( jQuery(href).find(".wpc_inside input[type=" + wpc_marks_type + "]:checked").length == jQuery(href).find(".wpc_inside input[type=" + wpc_marks_type + "]").length && jQuery(href).find(".wpc_inside input[type=" + wpc_marks_type + "]").length != 0 ) {
                        jQuery(href).find('.wpc_select_all_at_page').prop('checked', true);
                    }

                    jQuery(href).find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                    jQuery(href).find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );

                } else {
                    alert( data );
                    obj.shutter_box('hidePreLoader');
                    return;
                }

                obj.shutter_box('show');

                if( typeof( data.display ) != 'undefined' ) {
                    jQuery('.sb_lightbox_content_body .wpc_show').val( data.display );
                }

                if( typeof( data.order ) != 'undefined' ) {
                    jQuery('.sb_lightbox_content_body .wpc_order').val( data.order );
                }

                additional_vars = new Array();
                ref_array = new Array();
                if( typeof data.blocks != 'undefined' ) {
                    if( data.blocks.length == 0 ) {
                        jQuery( '.sb_lightbox_content_body .wpc_assign_popup_after_list' ).html('');
                    } else {
                        for( key in data.blocks ) {
                            if( typeof data.blocks[ key ].ref != 'undefined' && typeof data.blocks[ key ].code != 'undefined' ) {
                                if( wpc_array_search( data.blocks[ key ].ref, ref_array ) === false ) {
                                    ref_array.push( data.blocks[ key ].ref );
                                    jQuery( '.sb_lightbox_content_body ' + data.blocks[ key ].ref ).html('');
                                }
                                jQuery( '.sb_lightbox_content_body ' + data.blocks[ key ].ref ).append( data.blocks[ key ].code );

                                jQuery( '.sb_lightbox_content_body ' + data.blocks[ key ].ref ).find("select, textarea, input").each(function() {
                                    if( jQuery( this ).prop('name') != 'undefined' ) {
                                        additional_vars.push( jQuery( this ).prop('name') );
                                    }
                                });
                            }
                        }
                    }
                }
                obj.shutter_box('resize');
            },
            error: function(data) {
                obj.shutter_box('hidePreLoader');
                jQuery(href).find(".wpc_inside").html(data.html);
            }
        });

    });

    jQuery("body").on( 'change', '.wpc_show', function() {
        display = jQuery(this).val();
        order = jQuery(this).parents('.wpc_assign_popup').find('.wpc_order').val();
        var goto = 'first';
        var page = 1;
        var search_text = jQuery(this).parents('.wpc_assign_popup').find('.wpc_search_field').val();
        if( search_text == wpc_popup_var.search_text ) {
            search_text = '';
        }

        var link = jQuery(this);
        opened_link.shutter_box('showLoader');
        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.admin_url + "admin-ajax.php",
            data: {
                action : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                marks_type : wpc_marks_type,
                data_type : data_type,
                page : page,
                goto : goto,
                display : display,
                order : order,
                search : search_text,
                current_page : wpc_current_page,
                already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page,
                readonly : wpc_readonly
            },
            dataType: "json",
            success: function(data){
                if(data.html) {
                    link.parents('.wpc_assign_popup').find(".wpc_inside").html(data.html);

                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                link.parent().find(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                link.parent().find(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        link.parent().find(".wpc_page_num").html(data.page);
                    } else {
                        link.parent().find(".wpc_page_num").html('');
                    }

                    wpc_page_num = data.page;
                    if( checkbox_session.length ) {
                        for( key in checkbox_session ) {
                            link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }

                    link.find(".wpc_select_all_at_page").prop('checked', false);
                    if( link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]:checked").length == link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length && link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length != 0 ) {
                        link.find(".wpc_select_all_at_page").prop('checked', true);
                    }

                    link.parents('.wpc_assign_popup').find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                    link.parents('.wpc_assign_popup').find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
                    opened_link.shutter_box('hideLoader');
                }
            },
            error: function(data) {
                link.parent().parent().find(".wpc_inside").html(data.html);
                opened_link.shutter_box('hideLoader');
            }
        });

    });

    jQuery("body").on( 'change', '.wpc_order', function() {
        display = jQuery(this).parents('.wpc_assign_popup').find('.show').val();
        order = jQuery(this).val();
        var goto = 'first';
        var page = 1;
        var search_text = jQuery(this).parents('.wpc_assign_popup').find('.wpc_search_field').val();
        if( search_text == wpc_popup_var.search_text ) {
            search_text = '';
        }

        var link = jQuery(this);
        opened_link.shutter_box('showLoader');
        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.admin_url + "admin-ajax.php",
            data: {
                action       : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                marks_type   : wpc_marks_type,
                data_type    : data_type,
                page         : page,
                goto         : goto,
                display      : display,
                order        : order,
                search       : search_text,
                current_page : wpc_current_page,
                already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page,
                readonly : wpc_readonly
            },
            dataType: "json",
            success: function(data){
                if(data.html) {
                    link.parents('.wpc_assign_popup').find(".wpc_inside").html(data.html);

                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                link.parents('.wpc_assign_popup').find(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                link.parents('.wpc_assign_popup').find(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        link.parents('.wpc_assign_popup').find(".wpc_page_num").html( data.page );
                    } else {
                        link.parents('.wpc_assign_popup').find(".wpc_page_num").html('');
                    }

                    wpc_page_num = data.page;
                    if( checkbox_session.length ) {
                        for( key in checkbox_session ) {
                            link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }

                    link.find(".wpc_select_all_at_page").prop('checked', false);
                    if( link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]:checked").length == link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length && link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length != 0 ) {
                        link.find(".wpc_select_all_at_page").prop('checked', true);
                    }

                    link.parents('.wpc_assign_popup').find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                    link.parents('.wpc_assign_popup').find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
                    opened_link.shutter_box('hideLoader');
                }
            },
            error: function(data) {
                link.parent().parent().find(".wpc_inside").html(data.html);
                opened_link.shutter_box('hideLoader');
            }
        });
    });

    jQuery("body").on( 'keypress', '.wpc_search_field', function( e ) {
        if(e.which == 13) {

            jQuery('.wpc_select_all').prop('checked', false);
            jQuery('.wpc_select_all_at_page').prop('checked', false);

            search_text = jQuery(this).val();
            var goto = 'first';
            var page = 1;
            display = jQuery(this).parents('.wpc_assign_popup').find('.wpc_show').val();
            order = jQuery(this).parents('.wpc_assign_popup').find('.wpc_order').val();

            var link = jQuery(this);
            opened_link.shutter_box('showLoader');
            jQuery.ajax({
                type: "POST",
                url: wpc_popup_var.admin_url + "admin-ajax.php",
                data: {
                    action           : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                    marks_type       : wpc_marks_type,
                    data_type        : data_type,
                    page             : page,
                    goto             : goto,
                    display          : display,
                    order            : order,
                    search           : search_text,
                    current_page     : wpc_current_page,
                    already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                    open_popup : true,
                    included_ids : included_ids.join(','),
                    send_ajax : data_send_ajax,
                    input_ref : wpc_input_ref,
                    current_page : wpc_current_page,
                    readonly : wpc_readonly
                },
                dataType: "json",
                success: function(data){
                    if(data.html) {
                        full_ids_list = data.ids_list;
                        link.parents('.wpc_assign_popup').find(".wpc_inside").html(data.html);
                        for(key in data.buttons) {
                            if( data.buttons.hasOwnProperty( key ) ) {
                                if(data.buttons[key]) {
                                    link.parents('.wpc_assign_popup').find(".wpc_pagination_links[rel="+key+"]").show();
                                } else {
                                    link.parents('.wpc_assign_popup').find(".wpc_pagination_links[rel="+key+"]").hide();
                                }
                            }
                        }
                        if(data.count > data.per_page) {
                            link.parents('.wpc_assign_popup').find(".wpc_page_num").html(data.page);
                        } else {
                            link.parents('.wpc_assign_popup').find(".wpc_page_num").html('');
                        }

                        wpc_page_num = data.page;
                        if( checkbox_session.length ) {
                            for( key in checkbox_session ) {
                                link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                            }
                        }

                        link.find(".wpc_select_all_at_page").prop('checked', false);
                        if( link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]:checked").length == link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length && link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length != 0 ) {
                            link.find(".wpc_select_all_at_page").prop('checked', true);
                        }

                        link.parents('.wpc_assign_popup').find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                        link.parents('.wpc_assign_popup').find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
                        opened_link.shutter_box('hideLoader');
                    }
                },
                error: function(data) {
                    link.parents('.wpc_assign_popup').find(".wpc_inside").html(data.html);
                    opened_link.shutter_box('hideLoader');
                }
            });
        }
    });

    jQuery("body").on( 'click', '.wpc_pagination_links', function( e ) {
        var goto = jQuery(this).attr('rel');
        var display = jQuery(this).parents('.wpc_assign_popup').find('.wpc_show').val();
        var order = jQuery(this).parents('.wpc_assign_popup').find('.wpc_order').val();
        var search_text = jQuery(this).parents('.wpc_assign_popup').find('.wpc_search_field').val();
        if( search_text == wpc_popup_var.search_text ) {
            search_text = '';
        }
        if( !(typeof(wpc_page_num) == 'number' || !isNaN(wpc_page_num)) )
            wpc_page_num = 1;

        var link = jQuery(this);
        opened_link.shutter_box('showLoader');
        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.admin_url + "admin-ajax.php",
            data: {
                action           : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                marks_type       : wpc_marks_type,
                data_type        : data_type,
                page             : wpc_page_num,
                goto             : goto,
                display          : display,
                order            : order,
                search           : search_text,
                current_page     : wpc_current_page,
                already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page,
                readonly : wpc_readonly
            },
            dataType: "json",
            success: function(data){
                if(data.html) {
                    link.parents('.wpc_assign_popup').find(".wpc_inside").html(data.html);
                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                link.parent().children(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                link.parent().children(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        link.parents('.wpc_assign_popup').find(".wpc_page_num").html(data.page);
                    } else {
                        link.parents('.wpc_assign_popup').find(".wpc_page_num").html('');
                    }

                    wpc_page_num = data.page;
                    if( checkbox_session.length ) {
                        for( key in checkbox_session ) {
                            link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }
                    jQuery(".wpc_select_all_at_page").prop('checked', false);
                    if( link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]:checked").length == link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length && link.parents('.wpc_assign_popup').find(".wpc_inside input[type=" + wpc_marks_type + "]").length != 0 ) {
                        jQuery(".wpc_select_all_at_page").prop('checked', true);
                    }
                    opened_link.shutter_box('hideLoader');
                }
            },
            error: function(data) {
                link.parent('.wpc_assign_popup').find(".wpc_inside").html(data.html);
                opened_link.shutter_box('hideLoader');
            }
        });

    });

    //Cancel Assign block
    jQuery( "body" ).on( 'click', '.wpc_select_all', function( e ) {
        jQuery(this).parents('.wpc_assign_popup').find( '.wpc_select_all_at_page' ).prop( 'checked', false );

        if(jQuery(this).is(":checked")) {
            checkbox_session = full_ids_list;
            jQuery(this).parents('.wpc_assign_popup').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', true );
        } else {
            jQuery(this).parents('.wpc_assign_popup').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', false );
            checkbox_session = new Array();
        }
        jQuery( this ).parents(".wpc_assign_popup").find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
    });

    //Cancel Assign block
    jQuery( "body" ).on( 'click', ".wpc_cancel_popup", function( e ) {
        //tb_remove();
        opened_link.shutter_box('close');
        if( data_callback != '' && typeof( window[ data_callback + '_assign_cancel' ] ) == 'function' ) {
            window[ data_callback + '_assign_cancel' ]();
        }
    });

    //Ok Assign block
    jQuery( "body" ).on( 'click', ".wpc_ok_popup", function( e ) {
        if( data_send_ajax ) {
            opened_link.shutter_box('showLoader');
            var input_vars = new Array();
            for( key in additional_vars ) {
                if( jQuery('*[name=' + additional_vars[ key ] + ']').length ) {
                    if( jQuery('*[name=' + additional_vars[ key ] + ']').is(':radio') || jQuery('*[name=' + additional_vars[ key ] + ']').is(':checkbox') ) {
                        input_vars[ additional_vars[ key ] ] = jQuery('*[name=' + additional_vars[ key ] + ']').is(":checked") ? 1 : 0;
                    } else {
                        input_vars[ additional_vars[ key ] ] = jQuery('*[name=' + additional_vars[ key ] + ']').val();
                    }
                }
            }
            var data = {
                action : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'update_assigned_data',
                data_type : data_type,
                id : wpc_object_id,
                data : checkbox_session.join(','),
                current_page : wpc_current_page
            };
            data = jQuery.extend({}, data, input_vars);
            jQuery.ajax({
                type: "POST",
                url: wpc_popup_var.admin_url + "admin-ajax.php",
                data: data,
                dataType: "json",
                success: function(data) {
                    if( data.status ) {
                        jQuery( '#' + wpc_input_ref ).val( checkbox_session.join(',') );
                        jQuery( '#' + wpc_input_ref ).trigger('change');
                        if( 'radio' == wpc_marks_type ) {
                            if( checkbox_name.length != 0 ) {
                                jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_name + ")");
                            }
                        } else {
                            jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_session.length + ")");
                        }
                        jQuery( '#' + wpc_input_ref ).triggerHandler( 'wpc_change_assign_value' );
                    } else {
                        alert( data.message );
                    }
                    opened_link.shutter_box('hideLoader');
                    opened_link.shutter_box('close');
                },
                error: function(data) {
                    opened_link.shutter_box('hideLoader');
                    alert('Can not update assign data.');
                    opened_link.shutter_box('close');
                }
            });
        } else {
            jQuery( '#' + wpc_input_ref ).val( checkbox_session.join(',') );
            jQuery( '#' + wpc_input_ref ).trigger('change');
            if( 'radio' == wpc_marks_type ) {
                if( checkbox_name.length != 0 ) {
                    jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_name + ")");
                }
            } else {
                jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_session.length + ")");
            }
            jQuery( '#' + wpc_input_ref ).triggerHandler( 'wpc_change_assign_value' );

            opened_link.shutter_box('close');
        }

        if( data_callback != '' && typeof( window[ data_callback + '_assign_process' ] ) == 'function' ) {
            window[ data_callback + '_assign_process' ]( wpc_input_ref, checkbox_session );
        }
    });

    //Select/Un-select all clients at page
    jQuery( "body" ).on( 'click', ".wpc_select_all_at_page", function(e) {
        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery(this).parents('.wpc_assign_popup').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', true );
        } else {
            jQuery(this).parents('.wpc_assign_popup').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', false );
        }
        jQuery(this).parents('.wpc_assign_popup').find( '.wpc_inside input[type="checkbox"]' ).each(function() {
            if( jQuery(this).is(':checked') ) {
                checkbox_session.push( jQuery(this).val() );
            } else {
                key = wpc_array_search( jQuery(this).val(),checkbox_session );
                if( key !== false ) {
                    checkbox_session.splice( key, 1);
                }
            }
        });
        checkbox_session = wpc_array_unique( checkbox_session );
        jQuery(this).parents(".wpc_assign_popup").find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
    });

    //Select/Un-select checkbox
    jQuery( "body" ).on( 'change', '.wpc_inside input', function() {
        if( 'radio' == wpc_marks_type ) {
            checkbox_session = new Array();
            checkbox_session.push( jQuery(this).val() );
            checkbox_name = jQuery(this).data('name');
        } else {
            if( jQuery(this).is(':checked') ) {
                checkbox_session.push( jQuery(this).val() );
            } else {
                key = wpc_array_search( jQuery(this).val(),checkbox_session );
                if( key !== false ) {
                    checkbox_session.splice( key, 1);
                }
            }
            checkbox_session = wpc_array_unique( checkbox_session );
            jQuery(this).parents(".wpc_assign_popup").find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
            jQuery(this).parents(".wpc_assign_popup").find( ".wpc_select_all" ).removeAttr('checked');
            jQuery(this).parents(".wpc_assign_popup").find( ".wpc_select_all_at_page" ).removeAttr('checked');
        }
    });

});

jQuery.fn.extend({
    wpcResetPopup: function() {
        this.find(".wpc_search_field").val( wpc_popup_var.search_text );
        this.find(".wpc_select_all").prop('checked', false);
        this.find(".wpc_select_all_at_page").prop('checked', false);
        this.find(".wpc_pagination_links[rel=first]").hide();
        this.find(".wpc_pagination_links[rel=prev]").hide();
        this.find(".wpc_page_num").html('1');
        this.find(".wpc_inside input[type=" + wpc_marks_type + "]").removeAttr('checked');
        this.find(".wpc_select_all_at_page").removeAttr('checked');
        this.find('.wpc_show option').removeAttr('selected');
        this.find('.wpc_order option').removeAttr('selected');

        if( 'radio' == wpc_marks_type ) {
            this.find('.wpc_select_all_at_page').parent().hide();
            this.find('.wpc_select_all').parent().hide();
        } else {
            this.find('.wpc_select_all_at_page').parent().show();
            this.find('.wpc_select_all').parent().show();
        }
        checkbox_session = new Array();
        checkbox_name = '';
        full_ids_list = new Array();
        wpc_page_num = 1;
        return true;
    }
});

(function(a){a.fn.addBack=a.fn.addBack||a.fn.andSelf;
a.fn.extend({actual:function(b,l){if(!this[b]){throw'$.actual => The jQuery method "'+b+'" you called does not exist';}var f={absolute:false,clone:true,includeMargin:false};
var i=a.extend(f,l);var e=this.eq(0);var h,j;if(i.clone===true){h=function(){var m="position: absolute !important; top: -1000 !important; ";e=e.clone().attr("style",m).appendTo("body");
};j=function(){e.remove();};}else{var g=[];var d="";var c;h=function(){c=e.parents().addBack().filter(":hidden");d+="visibility: hidden !important; display: block !important; ";
if(i.absolute===true){d+="position: absolute !important; ";}c.each(function(){var m=a(this);var n=m.attr("style");g.push(n);m.attr("style",n?n+";"+d:d);
});};j=function(){c.each(function(m){var o=a(this);var n=g[m];if(n===undefined){o.removeAttr("style");}else{o.attr("style",n);}});};}h();var k=/(outer)/.test(b)?e[b](i.includeMargin):e[b]();
j();return k;}});})(jQuery);

jQuery.fn.values = function(data) {
    var els = jQuery(this).find(':input').get();

    if(typeof data != 'object') {
        // return all data
        data = {};

        jQuery.each(els, function() {
            if (this.name && !this.disabled && (this.checked
                            || /select|textarea/i.test(this.nodeName)
                            || /text|hidden|password/i.test(this.type))) {
                data[this.name] = jQuery(this).val();
            }
        });
        return data;
    } else {
        jQuery.each(els, function() {
            if (this.name && data[this.name]) {
                if(this.type == 'checkbox' || this.type == 'radio') {
                    jQuery(this).attr("checked", (data[this.name] == $(this).val()));
                } else {
                    jQuery(this).val(data[this.name]);
                }
            }
        });
        return jQuery(this);
    }
};

function wpc_array_unique( inArr ){
    var uniHash={}, outArr=[], i=inArr.length;
    while( i-- ) uniHash[ inArr[ i ] ]=i;
    for( i in uniHash ) outArr.push( i );
    return outArr;
}

function wpc_array_search( needle, haystack, strict ) {
    var strict = !!strict;
    for(var key in haystack){
        if( (strict && haystack[key] === needle) || (!strict && haystack[key] == needle) && haystack.hasOwnProperty( key ) ){
            return key;
        }
    }
    return false;
}