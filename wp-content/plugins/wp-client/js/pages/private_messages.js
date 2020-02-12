var filter;
jQuery( document ).ready( function() {
    //variable for set/unset loading
    var loading = false;

    var per_page = 5;
    if ( typeof( wpc_shortcode_messages_atts.show_number ) !== 'undefined' ) {
        per_page = parseInt( wpc_shortcode_messages_atts.show_number );
    }

    jQuery( '.wpc_private_messages_shortcode .wpc_nav_button' ).click( function() {
        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        //add select class to this element
        parent_wrapper.find( '.wpc_nav_button' ).removeClass( 'selected' );
        jQuery(this).addClass( 'selected' );

        parent_wrapper.find('.wpc_msg_nav_list_collapsed').removeClass('inbox').removeClass('sent').removeClass('archive').removeClass('trash').addClass( obj.data('list') );

        //hide new message form
        parent_wrapper.find( '.wpc_msg_add_new_wrapper' ).hide();
        clear_new_message_form(parent_wrapper);
        parent_wrapper.find( '.wpc_msg_chain_content' ).hide();

        //temp wrapper html
        var html = parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).html();
        parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).html('');

        //reset bulk check
        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        parent_wrapper.find( '.wpc_msg_pagination' ).data( 'pagenumber', 1 );

        parent_wrapper.find( '.wpc_msg_search' ).val( '' );
        parent_wrapper.find( '.wpc_msg_active_filters_wrapper').html('');

        filter = {};
        //show loader
        parent_wrapper.find( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_get_list',
                type : jQuery(this).data('list'),
                per_page : per_page,
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).html( html );
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();

                    wpc_msg_add_notice( data.message, 'error' );

                } else {
                    parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).html( data.html ).show();

                    resize_messages_content();

                    parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                    parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
                    if( obj.data('list') == 'trash' ) {
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="restore"]').show();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="leave"]').show();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="delete"]').hide();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="archive"]').hide();
                    } else if( obj.data('list') == 'archive' ) {
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="restore"]').show();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="delete"]').show();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="leave"]').hide();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="archive"]').hide();
                    } else {
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="delete"]').show();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="archive"]').show();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="restore"]').hide();
                        parent_wrapper.find( '.wpc_msg_bulk_actions_wrapper .wpc_msg_bulk_actions li[data-action="leave"]').hide();
                    }

                    parent_wrapper.find('.wpc_msg_bulk_check').data( 'checked_all', false ).data( 'checked_chains', data.ids );

                    if( data.is_empty ) {
                        //hide navigation
                        parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).hide();
                        parent_wrapper.find( '.wpc_msg_pagination' ).hide();
                        parent_wrapper.find( '.wpc_msg_bulk_all' ).hide();
                        parent_wrapper.find( '.wpc_msg_filter' ).hide();
                    } else {
                        //show navigation
                        parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).show();
                        parent_wrapper.find( '.wpc_msg_pagination' ).show();
                        parent_wrapper.find( '.wpc_msg_bulk_all' ).show();
                        parent_wrapper.find( '.wpc_msg_filter' ).show();
                    }

                    if( data.pagination !== false ) {
                        parent_wrapper.find( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        parent_wrapper.find( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        parent_wrapper.find( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            parent_wrapper.find( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    parent_wrapper.find( '.wpc_msg_filter .wpc_msg_filter_by option[value="date"]').attr('disabled', false);
                    parent_wrapper.find( '.wpc_msg_filter_by' ).trigger( 'change' );

                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_prev_button', function() {
        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        //show loader
        parent_wrapper.find( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_get_list',
                type : parent_wrapper.find('.wpc_nav_button.selected').data('list'),
                page : jQuery(this).parents('.wpc_msg_pagination').data('pagenumber')*1 + 1,
                search : parent_wrapper.find('.wpc_msg_search').val(),
                filters : filters,
                per_page : per_page,
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    parent_wrapper.find('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    obj.parents('.wpc_msg_pagination').data('pagenumber', obj.parents('.wpc_msg_pagination').data('pagenumber')*1 + 1 );

                    if( data.pagination !== false ) {
                        parent_wrapper.find( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        parent_wrapper.find( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        parent_wrapper.find( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            parent_wrapper.find( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    if( parent_wrapper.find('.wpc_msg_bulk_check').data( 'checked_all' ) ) {
                        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false);
                        parent_wrapper.find('.wpc_msg_item').prop( 'checked', true );
                    } else {
                        parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                        parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
                    }

                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_next_button', function() {
        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false );
        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);

        //show loader
        parent_wrapper.find( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_get_list',
                type : parent_wrapper.find('.wpc_nav_button.selected').data('list'),
                page : jQuery(this).parents('.wpc_msg_pagination').data('pagenumber')*1 - 1,
                search : parent_wrapper.find('.wpc_msg_search').val(),
                filters : filters,
                per_page : per_page,
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    parent_wrapper.find('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    obj.parents('.wpc_msg_pagination').data('pagenumber', obj.parents('.wpc_msg_pagination').data('pagenumber')*1 - 1 );

                    if( data.pagination !== false ) {
                        parent_wrapper.find( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        parent_wrapper.find( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        parent_wrapper.find( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            parent_wrapper.find( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    if( parent_wrapper.find('.wpc_msg_bulk_check').data( 'checked_all' ) ) {
                        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false);
                        parent_wrapper.find('.wpc_msg_item').prop( 'checked', true );
                    } else {
                        parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                        parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
                    }

                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_refresh_button', function() {
        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( obj.data('object') == 'chains' ) {
            var filters = '';
            if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
                filters = jQuery.base64Encode( JSON.stringify( filter ) );
            }

            //reset bulk check
            parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

            //show loader
            parent_wrapper.find( '.wpc_ajax_overflow' ).show();
            loading = true;
            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_front_end_get_list',
                    type : parent_wrapper.find('.wpc_nav_button.selected').data('list'),
                    page : Query(this).parents('.wpc_msg_pagination').data('pagenumber')*1,
                    search : parent_wrapper.find('.wpc_msg_search').val(),
                    filters : filters,
                    per_page : per_page,
                    client_id : wpc_private_message_data.client_id,
                    security : wpc_private_message_data._wpnonce
                },
                dataType: "json",
                success: function( data ) {
                    if( !data.status ) {
                        parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        parent_wrapper.find('.wpc_msg_content_wrapper_inner').html( data.html );

                        resize_messages_content();

                        parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                        parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();

                        parent_wrapper.find('.wpc_msg_bulk_check').data( 'checked_all', false );

                        if( data.is_empty ) {
                            //hide navigation
                            parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).hide();
                            parent_wrapper.find( '.wpc_msg_pagination' ).hide();
                            parent_wrapper.find( '.wpc_msg_bulk_all' ).hide();
                        } else {
                            //show navigation
                            parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).show();
                            parent_wrapper.find( '.wpc_msg_pagination' ).show();
                            parent_wrapper.find( '.wpc_msg_bulk_all' ).show();
                        }

                        if( data.pagination !== false ) {
                            parent_wrapper.find( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                            parent_wrapper.find( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                            parent_wrapper.find( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                            if( data.pagination.current_page > 1 ) {
                                parent_wrapper.find( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                            } else {
                                parent_wrapper.find( '.wpc_msg_next_button' ).addClass( 'disabled' );
                            }

                            if( data.pagination.pages_count == data.pagination.current_page ) {
                                parent_wrapper.find( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                            } else {
                                parent_wrapper.find( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                            }
                        }

                        parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    }
                    loading = false;
                }
            });
        } else if( obj.data('object') == 'chain' ) {
            //show loader
            parent_wrapper.find( '.wpc_ajax_overflow' ).show();
            loading = true;
            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_front_end_get_chain',
                    chain_id : obj.data('chain_id')
                },
                dataType: "json",
                success: function( data ) {
                    if( !data.status ) {
                        parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        parent_wrapper.find( '.wpc_msg_chain_content' ).html( data.html );

                        if( parent_wrapper.find('.wpc_expand_older_messages').length == 0 ) {
                            parent_wrapper.find('.wpc_msg_collapse_button').addClass('disabled');
                        } else {
                            parent_wrapper.find('.wpc_msg_collapse_button').removeClass('disabled');
                        }

                        parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    }
                    loading = false;
                }
            });
        }

    });

    jQuery( '.wpc_private_messages_shortcode .wpc_msg_search_button' ).click( function() {
        var obj = jQuery( this );

        var was_opened = obj.hasClass( 'opened' );
        if( !was_opened ) {
            obj.toggleClass( 'opened' );
        }

        obj.parents('.wpc_msg_search_line').find( '.wpc_msg_search' ).animate({
            width: "toggle"
        }, 1000, function() {
            if( was_opened ) {
                obj.toggleClass( 'opened' );
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'change', '.wpc_msg_search', function() {
        if( loading ) {
            return false;
        }

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        //show loader
        parent_wrapper.find( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_get_list',
                type : parent_wrapper.find('.wpc_nav_button.selected').data('list'),
                search : jQuery(this).val(),
                filters : filters,
                per_page : per_page,
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    parent_wrapper.find('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                    parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();

                    parent_wrapper.find('.wpc_msg_bulk_check').data( 'checked_all', false ).data( 'checked_chains', data.ids );

                    parent_wrapper.find('.wpc_msg_pagination').data('pagenumber', 1);

                    if( data.is_empty ) {
                        //hide navigation
                        parent_wrapper.find( '.wpc_msg_pagination' ).hide();
                        parent_wrapper.find( '.wpc_msg_bulk_all' ).hide();

                        if( !( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) ) {
                            parent_wrapper.find( '.wpc_msg_filter' ).hide();
                        } else {
                            parent_wrapper.find( '.wpc_msg_filter' ).show();
                        }
                    } else {
                        //show navigation
                        parent_wrapper.find( '.wpc_msg_pagination' ).show();
                        parent_wrapper.find( '.wpc_msg_bulk_all' ).show();
                        parent_wrapper.find( '.wpc_msg_filter' ).show();
                    }

                    if( data.pagination !== false ) {
                        parent_wrapper.find( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        parent_wrapper.find( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        parent_wrapper.find( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            parent_wrapper.find( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    parent_wrapper.find( '.wpc_msg_filter_by' ).trigger( 'change' );

                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_item', function(e) {
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        var checkedCount = parent_wrapper.find( '.wpc_msg_item:checked' ).length;
        var checksCount = parent_wrapper.find( '.wpc_msg_item' ).length;

        if( checkedCount == 0 ) {
            parent_wrapper.find('.wpc_msg_bulk_actions').hide();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
        } else {
            parent_wrapper.find('.wpc_msg_bulk_actions').show();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().show();
        }

        if( checkedCount == 0 ) {
            parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);
        } else if( checkedCount == checksCount ) {
            parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false);
        } else if( checkedCount > 0 && checkedCount < checksCount ) {
            parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', true);
        }

        parent_wrapper.find('.wpc_msg_bulk_check').data('checked_all', false);

        e.stopPropagation();
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_bulk_check', function(e) {
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( jQuery(this).is(':checked') ) {
            parent_wrapper.find( '.wpc_msg_item' ).prop( 'checked', true );
            parent_wrapper.find('.wpc_msg_bulk_actions').show();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().show();
        } else {
            parent_wrapper.find( '.wpc_msg_item' ).prop( 'checked', false );
            parent_wrapper.find('.wpc_msg_bulk_actions').hide();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
        }

        parent_wrapper.find('.wpc_msg_bulk_check').data('checked_all', false);

        e.stopPropagation();
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_bulk_all', function(e) {
        jQuery(this).toggleClass( 'bulk_opened' );

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( parent_wrapper.find( '.wpc_msg_filter' ).hasClass( 'filter_opened' ) ) {
            parent_wrapper.find( '.wpc_msg_filter' ).removeClass( 'filter_opened' );
        }

        e.stopPropagation();

        jQuery( 'body' ).bind( 'click', function( event ) {
            parent_wrapper.find( '.wpc_msg_bulk_all' ).removeClass( 'bulk_opened' );
            jQuery( 'body' ).unbind( event );
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_bulk_actions_wrapper', function(e){
        e.preventDefault();
        e.stopPropagation();
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_bulk_select li', function() {
        var select = jQuery(this).data( 'select' );

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( select == 'all' ) {
            if( parent_wrapper.find('.wpc_msg_bulk_check').data('checked_all') == true ) {
                parent_wrapper.find('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
                return false;
            } else {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).data( 'checked_all', true ).prop( 'checked', true ).prop( 'indeterminate', false);
                parent_wrapper.find( '.wpc_msg_item' ).prop( 'checked', true );
            }

            parent_wrapper.find('.wpc_msg_bulk_actions').show();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().show();
        } else if( select == 'all_page' ) {
            if( parent_wrapper.find('.wpc_msg_bulk_check').is(':checked') ) {
                parent_wrapper.find('.wpc_msg_bulk_check').data('checked_all', false);
                parent_wrapper.find('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
                return false;
            } else {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).data('checked_all', false).prop( 'checked', true ).prop( 'indeterminate', false);
                parent_wrapper.find( '.wpc_msg_item' ).prop( 'checked', true );
            }
            parent_wrapper.find('.wpc_msg_bulk_actions').show();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().show();
        } else if( select == 'none' ) {
            parent_wrapper.find( '.wpc_msg_bulk_check' ).data('checked_all', false).prop( 'checked', false ).prop( 'indeterminate', false);
            parent_wrapper.find( '.wpc_msg_item' ).prop( 'checked', false );
            parent_wrapper.find('.wpc_msg_bulk_actions').hide();
            parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
        } else if( select == 'unread' ) {
            parent_wrapper.find( '.wpc_msg_item[data-new="true"]' ).prop( 'checked', true );
            parent_wrapper.find( '.wpc_msg_item[data-new="false"]' ).prop( 'checked', false );

            var checkedCount = parent_wrapper.find( '.wpc_msg_item:checked' ).length;
            var checksCount = parent_wrapper.find( '.wpc_msg_item' ).length;

            if( checkedCount == 0 ) {
                parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
            } else {
                parent_wrapper.find('.wpc_msg_bulk_actions').show();
                parent_wrapper.find('.wpc_msg_bulk_actions').prev().show();
            }

            parent_wrapper.find( '.wpc_msg_bulk_check' ).data('checked_all', false);

            if( checkedCount == 0 ) {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false );
            } else if( checkedCount == checksCount ) {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false );
            } else if( checkedCount > 0 && checkedCount < checksCount ) {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', true );
            }
        } else if( select == 'read' ) {
            parent_wrapper.find( '.wpc_msg_item[data-new="true"]' ).prop( 'checked', false );
            parent_wrapper.find( '.wpc_msg_item[data-new="false"]' ).prop( 'checked', true );

            var checkedCount = parent_wrapper.find( '.wpc_msg_item:checked' ).length;
            var checksCount = parent_wrapper.find( '.wpc_msg_item' ).length;

            if( checkedCount == 0 ) {
                parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();
            } else {
                parent_wrapper.find('.wpc_msg_bulk_actions').show();
                parent_wrapper.find('.wpc_msg_bulk_actions').prev().show();
            }

            parent_wrapper.find( '.wpc_msg_bulk_check' ).data('checked_all', false);

            if( checkedCount == 0 ) {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false );
            } else if( checkedCount == checksCount ) {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false );
            } else if( checkedCount > 0 && checkedCount < checksCount ) {
                parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', true );
            }
        }

        parent_wrapper.find('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_bulk_actions li', function() {
        var action = jQuery(this).data( 'action' );

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( parent_wrapper.find( '.wpc_msg_item:checked' ).length <= 0 ) {
            return false;
        }

        var chain_ids = [];
        if( parent_wrapper.find('.wpc_msg_bulk_check').data('checked_all') ) {
            chain_ids = parent_wrapper.find('.wpc_msg_bulk_check').data('checked_chains');
        } else {
            parent_wrapper.find( '.wpc_msg_item:checked' ).each( function() {
                chain_ids.push( jQuery(this).val() );
            });
        }
        chain_ids = jQuery.base64Encode( JSON.stringify( chain_ids ) );

        if( action == 'read' ) {
            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_chain_mark_read',
                    chain_ids : chain_ids,
                    client_id : wpc_private_message_data.client_id,
                    security : wpc_private_message_data._wpnonce
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        wpc_msg_add_notice( wpc_private_message_data.texts.read, 'updated');
                        parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                    }
                }
            });
        } else if( action == 'archive' ) {
            var current_list = parent_wrapper.find('.wpc_nav_button.selected').data('list');

            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_chain_to_archive',
                    chain_ids : chain_ids,
                    from : current_list,
                    client_id : wpc_private_message_data.client_id,
                    security : wpc_private_message_data._wpnonce
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        if( data.count > 0 ) {
                            if (parent_wrapper.find('.wpc_msg_prev_button').hasClass('disabled') && '1' != parent_wrapper.find('.wpc_msg_pagination').data('pagenumber')) {
                                parent_wrapper.find('.wpc_msg_pagination').data('pagenumber', parent_wrapper.find('.wpc_msg_pagination').data('pagenumber') * 1 - 1);
                            }

                            wpc_msg_add_notice(wpc_private_message_data.texts.archived, 'updated');
                            parent_wrapper.find('.wpc_nav_button.archive').trigger('click');
                        } else {
                            parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                }
            });
        } else if( action == 'leave' ) {
            if( confirm( 'Are you sure?' ) ) {
                jQuery.ajax({
                    type: 'POST',
                    url: wpc_private_message_data.ajax_url,
                    data: {
                        action : wpc_message_leave_chain,
                        chain_ids : chain_ids,
                        client_id : wpc_private_message_data.client_id,
                        security : wpc_private_message_data._wpnonce
                    },
                    dataType: "json",
                    success: function (data) {
                        if (!data.status) {
                            wpc_msg_add_notice( data.message, 'error' );
                        } else {
                            if( parent_wrapper.find('.wpc_msg_prev_button').hasClass('disabled') && '1' != parent_wrapper.find('.wpc_msg_pagination').data('pagenumber') ) {
                                parent_wrapper.find('.wpc_msg_pagination').data('pagenumber', parent_wrapper.find('.wpc_msg_pagination').data('pagenumber')*1 - 1);
                            }

                            wpc_msg_add_notice(wpc_private_message_data.texts.leaved, 'updated');

                            parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                });
            }
        } else if( action == 'delete' ) {
            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_chain_to_trash',
                    chain_ids : chain_ids,
                    client_id : wpc_private_message_data.client_id,
                    security : wpc_private_message_data._wpnonce
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        if( data.count > 0 ) {
                            if (parent_wrapper.find('.wpc_msg_prev_button').hasClass('disabled') && '1' != parent_wrapper.find('.wpc_msg_pagination').data('pagenumber')) {
                                parent_wrapper.find('.wpc_msg_pagination').data('pagenumber', parent_wrapper.find('.wpc_msg_pagination').data('pagenumber') * 1 - 1);
                            }

                            wpc_msg_add_notice(wpc_private_message_data.texts.trashed, 'updated');
                            parent_wrapper.find('.wpc_nav_button.trash').trigger('click');
                        } else {
                            parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                }
            });
        } else if( action == 'restore' ) {
            var current_list = parent_wrapper.find('.wpc_nav_button.selected').data('list');

            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_chain_restore',
                    chain_ids : chain_ids,
                    from : current_list,
                    client_id : wpc_private_message_data.client_id,
                    security : wpc_private_message_data._wpnonce
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        if( parent_wrapper.find('.wpc_msg_prev_button').hasClass('disabled') && '1' != parent_wrapper.find('.wpc_msg_pagination').data('pagenumber') ) {
                            parent_wrapper.find('.wpc_msg_pagination').data('pagenumber', parent_wrapper.find('.wpc_msg_pagination').data('pagenumber')*1 - 1);
                        }

                        parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        wpc_msg_add_notice(wpc_private_message_data.texts.restored, 'updated');
                    }
                }
            });
        }

        parent_wrapper.find('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
    });



    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_filter', function(e) {
        jQuery(this).toggleClass( 'filter_opened' );

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( parent_wrapper.find( '.wpc_msg_bulk_all' ).hasClass( 'bulk_opened' ) ) {
            parent_wrapper.find( '.wpc_msg_bulk_all' ).removeClass( 'bulk_opened' );
        }

        e.stopPropagation();

        jQuery( 'body' ).bind( 'click', function( event ) {
            parent_wrapper.find( '.wpc_msg_filter' ).removeClass( 'filter_opened' );
            jQuery( 'body' ).unbind( event );
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_filter_wrapper', function(e){
        e.preventDefault();
        e.stopPropagation();
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'change', '.wpc_msg_filter_by', function() {
        var obj = jQuery(this);

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        obj.parents('.wpc_msg_filter_wrapper').find( '.wpc_ajax_content' ).addClass( 'wpc_is_loading' );
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_get_filter',
                type : parent_wrapper.find('.wpc_nav_button.selected').data('list'),
                search : parent_wrapper.find('.wpc_msg_search').val(),
                by : jQuery(this).val(),
                filters : filters
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    obj.parents('.wpc_msg_filter_wrapper').find( '.wpc_msg_filter_selectors' ).html( data.filter_html );

                    if( obj.val() == 'member' ) {
                        parent_wrapper.find( '.wpc_msg_filter_members' ).wpc_select({
                            search:true,
                            opacity:'0.2'
                        });
                    } else if( obj.val() == 'date' ) {
                        if( typeof( custom_datepicker_init ) !== 'undefined' ) {
                            custom_datepicker_init();
                        }

                        parent_wrapper.find( '.from_date_field' ).datepicker( "option", {
                            minDate: new Date( data.mindate*1000 ),
                            maxDate: new Date( data.maxdate*1000 ),
                            onClose: function( selectedDate ) {
                                parent_wrapper.find('.to_date_field').datepicker( "option", "minDate", selectedDate );
                            }
                        });

                        parent_wrapper.find('.to_date_field').datepicker( "option", {
                            minDate: new Date( data.mindate*1000 ),
                            maxDate: new Date( data.maxdate*1000 ),
                            onClose: function( selectedDate ) {
                                parent_wrapper.find('.from_date_field').datepicker( "option", "maxDate", selectedDate );
                            }
                        });
                    }

                    obj.parents('.wpc_msg_filter_wrapper').find( '.wpc_ajax_content' ).removeClass( 'wpc_is_loading' );
                }
            }
        });
    });

    //change filtering reload
    jQuery( '.wpc_private_messages_shortcode' ).on( 'change', '.wpc_msg_active_filters_wrapper', function() {
        if( loading ) {
            return false;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        //reset bulk check
        parent_wrapper.find( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        //show loader
        parent_wrapper.find( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_get_list',
                type : parent_wrapper.find('.wpc_nav_button.selected').data('list'),
                search : parent_wrapper.find('.wpc_msg_search').val(),
                filters : filters,
                per_page : per_page,
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    parent_wrapper.find('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    parent_wrapper.find('.wpc_msg_bulk_actions').hide();
                    parent_wrapper.find('.wpc_msg_bulk_actions').prev().hide();

                    parent_wrapper.find('.wpc_msg_bulk_check').data( 'checked_all', false ).data( 'checked_chains', data.ids );

                    parent_wrapper.find('.wpc_msg_pagination').data('pagenumber', 1);

                    if( data.is_empty ) {
                        //hide navigation
                        jQuery( '.wpc_msg_pagination' ).hide();
                        jQuery( '.wpc_msg_bulk_all' ).hide();
                        jQuery( '.wpc_msg_filter' ).hide();

                        if( jQuery('.wpc_msg_search').val() == '' ) {
                            jQuery( '.wpc_msg_search_line' ).hide();
                        }
                    } else {
                        //show navigation
                        jQuery( '.wpc_msg_pagination' ).show();
                        jQuery( '.wpc_msg_bulk_all' ).show();
                        jQuery( '.wpc_msg_search_line' ).show();
                        jQuery( '.wpc_msg_filter' ).show();
                    }

                    if( data.pagination !== false ) {
                        parent_wrapper.find( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        parent_wrapper.find( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        parent_wrapper.find( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            parent_wrapper.find( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            parent_wrapper.find( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    parent_wrapper.find( '.wpc_msg_filter_by' ).trigger( 'change' );

                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    //add filter
    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_add_filter', function() {
        var obj = jQuery(this);

        var filter_before = filter;
        var filter_by = obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by').val();

        if( typeof filter_before === 'undefined' ) {
            filter = {};
            filter[filter_by] = [];
        }

        if( !filter.hasOwnProperty( filter_by ) ) {
            filter[filter_by] = [];
        }

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( filter_by == 'member' ) {
            var member_id = obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_members').val();

            if( member_id == '' ) {
                return false;
            }

            var in_array = false;

            if( typeof filter_before !== 'undefined' && filter_before.hasOwnProperty( filter_by ) ) {
                jQuery.map( filter_before[filter_by], function( elementOfArray, indexInArray ) {
                    if( elementOfArray == member_id ) {
                        in_array = true;
                    }
                });
            }

            if( in_array ) {
                return false;
            }

            filter[filter_by].push( member_id );

            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_get_filter_data',
                    filter_by : filter_by,
                    member_id : member_id
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        parent_wrapper.find( '.wpc_msg_active_filters_wrapper' ).append(
                            '<div class="wpc_filter_wrapper" data-filter_by="' + filter_by + '" data-member_id="' + member_id + '">' +
                            data.title + ': ' + data.name +
                            '<div class="wpc_remove_filter">&times;</div>' +
                            '</div>'
                        ).trigger( 'change' );
                    }
                }
            });

        } else if( filter_by == 'date' ) {
            if( obj.parents( '.wpc_msg_filter' ).find('.from_date_field').next().val() == '' && obj.parents( '.wpc_msg_filter' ).find('.to_date_field').next().val() == '' ) {
                return false;
            }

            if( obj.parents( '.wpc_msg_filter' ).find('.to_date_field').next().val() == '' ) {
                var current_time = new Date().getTime();
                obj.parents( '.wpc_msg_filter' ).find('.to_date_field').next().val( Math.floor( current_time / 1000 ) );
            }

            filter[filter_by] = {
                'from': obj.parents( '.wpc_msg_filter' ).find('.from_date_field').next().val(),
                'to': obj.parents( '.wpc_msg_filter' ).find('.to_date_field').next().val()
            };

            jQuery.ajax({
                type: 'POST',
                url: wpc_private_message_data.ajax_url,
                data: {
                    action : 'wpc_message_get_filter_data',
                    filter_by : filter_by,
                    from : filter[filter_by]['from'],
                    to : filter[filter_by]['to']
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        wpc_msg_add_notice( data.message, 'error' );
                    } else {
                        parent_wrapper.find( '.wpc_msg_active_filters_wrapper' ).append(
                            '<div class="wpc_filter_wrapper" data-filter_by="' + filter_by + '">' +
                            data.title + ': ' + data.from + ' - ' + data.to +
                            '<div class="wpc_remove_filter">&times;</div>' +
                            '</div>'
                        ).trigger( 'change' );
                    }
                }
            });

            obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option[value="date"]').attr('disabled', true);
            obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option:not("disabled")').first().prop('selected', true);
        }

        parent_wrapper.find('.wpc_msg_filter').removeClass( 'filter_opened' );
    });

    //remove filters
    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_remove_filter', function() {
        var obj = jQuery(this);

        var filter_before = filter;

        var filter_by = obj.parents( '.wpc_filter_wrapper' ).data('filter_by');

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        if( filter_by == 'member' ) {
            var member_id = obj.parents( '.wpc_filter_wrapper' ).data('member_id');

            var index = filter_before[filter_by].indexOf( member_id.toString() );
            if( index > -1 ) {
                filter[filter_by].splice( index, 1 );
            }
        } else if( filter_by == 'date' ) {
            delete filter.date;

            parent_wrapper.find( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option[value="date"]').attr('disabled', false);
            parent_wrapper.find( '.wpc_msg_filter' ).find('.wpc_msg_filter_by').trigger( 'change' );
        }

        parent_wrapper.find( '.wpc_msg_active_filters_wrapper' ).trigger( 'change' );
        obj.parents( '.wpc_filter_wrapper' ).remove();
    });



    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_new_message_button', function() {
        if( loading ) {
            return false;
        }

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        parent_wrapper.find( '.wpc_msg_add_new_wrapper' ).show();
        parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).hide();
        parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).hide();
        parent_wrapper.find( '.wpc_msg_chain_content' ).hide().html('');
    });

    jQuery( '.new_message_to' ).wpc_select({
        search:true,
        opacity:'0.2'
    });

    jQuery( '.new_message_to' ).change( function() {
        var obj = jQuery(this);
        var connected_field = obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).parents( '.wpc_msg_new_message_line' );

        if( obj.val() == null ) {
            obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).attr( 'disabled', true ).html('');
            obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).wpc_select('destroy', jQuery( '.new_message_cc' ).data('wpc_select_unique'));
            connected_field.hide();
            return false;
        }

        if( jQuery(this).find( 'option:selected' ).length > 1 ) {
            obj.parents( '.wpc_msg_add_new_wrapper' ).find('.wpc_msg_send_new_message').val( wpc_private_message_data.texts.send_messages );
        } else {
            obj.parents( '.wpc_msg_add_new_wrapper' ).find('.wpc_msg_send_new_message').val( wpc_private_message_data.texts.send_message );
        }

        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_get_connected_members',
                to : jQuery.base64Encode( JSON.stringify( obj.val() ) )
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    if( data.selectbox_content.length > 0 ) {
                        obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).attr( 'disabled', false ).html( data.selectbox_content );

                        obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).wpc_select({
                            search:true,
                            opacity:'0.2',
                            onChangeValue:function() {
                                if( obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc option:selected' ).length >= 1 ) {
                                    jQuery( '.new_message_to' ).wpc_select('disable', jQuery( '.new_message_to' ).data('wpc_select_unique'));
                                    obj.parents( '.wpc_msg_add_new_wrapper' ).find('.wpc_msg_send_new_message').val( wpc_private_message_data.texts.group_dialog );
                                } else {
                                    jQuery( '.new_message_to' ).wpc_select('enable', jQuery( '.new_message_to' ).data('wpc_select_unique'));
                                    obj.parents( '.wpc_msg_add_new_wrapper' ).find('.wpc_msg_send_new_message').val( wpc_private_message_data.texts.send_message );
                                }
                            }
                        });

                        connected_field.show();
                    } else {
                        obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).attr( 'disabled', true ).html('');
                        obj.parents( '.wpc_msg_add_new_wrapper' ).find( '.new_message_cc' ).wpc_select('destroy', jQuery( '.new_message_cc' ).data('wpc_select_unique'));
                        connected_field.hide();
                    }
                }
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_send_new_message', function() {
        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        var content = parent_wrapper.find( '.new_message_content').val();

        jQuery('#wpc_msg_noitce').remove();

        if( parent_wrapper.find( '.new_message_to').val() == null ) {
            wpc_msg_add_notice( wpc_private_message_data.texts.error_to, 'error' );
            return false;
        }

        if( parent_wrapper.find( '.new_message_subject').val() == '' ) {
            wpc_msg_add_notice( wpc_private_message_data.texts.error_subject, 'error' );
            return false;
        }

        if( content == '' ) {
            wpc_msg_add_notice( wpc_private_message_data.texts.error_content, 'error' );
            return false;
        }

        if( parent_wrapper.find( '.new_message_cc_email').length > 0 && parent_wrapper.find( '.new_message_cc_email').val() != '' && !isValidEmailAddress( parent_wrapper.find( '.new_message_cc_email').val() ) ) {
            wpc_msg_add_notice( wpc_private_message_data.texts.error_cc_email, 'error');
            return false;
        }


        content = jQuery.base64Encode( JSON.stringify( content ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );

        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_new_message',
                to : jQuery.base64Encode( JSON.stringify( parent_wrapper.find( '.new_message_to').val() ) ),
                cc : jQuery.base64Encode( JSON.stringify( parent_wrapper.find( '.new_message_cc').val() ) ),
                subject : parent_wrapper.find( '.new_message_subject').val(),
                content : content,
                cc_email : parent_wrapper.find( '.new_message_cc_email').val(),
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    //parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                    parent_wrapper.find( '.wpc_msg_add_new_wrapper' ).hide();
                    parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).show();
                    parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).show();
                    parent_wrapper.find( '.wpc_msg_chain_content' ).hide();


                    parent_wrapper.find( '.new_message_to').wpc_select('clear');
                    parent_wrapper.find( '.new_message_cc').wpc_select('clear');
                    parent_wrapper.find( '.new_message_subject').val('');
                    parent_wrapper.find( '.new_message_content').val('');
                    parent_wrapper.find( '.new_message_cc_email').val('');

                    loading = false;
                    parent_wrapper.find( '.wpc_nav_button.sent' ).trigger( 'click' );

                    wpc_msg_add_notice( data.message, 'updated' );
                }
                loading = false;
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_back_new_message', function() {
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');

        parent_wrapper.find( '.wpc_msg_add_new_wrapper' ).hide();
        parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).show();
        parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).show();
        parent_wrapper.find( '.wpc_msg_chain_content' ).hide();

        clear_new_message_form(parent_wrapper);
    });



    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_private_messages_table tr:not(.wpc_msg_no-items)', function() {
        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );
        var chain_id = obj.find( '.wpc_msg_item').val();

        parent_wrapper.find( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_front_end_get_chain',
                chain_id : chain_id,
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    parent_wrapper.find( '.wpc_msg_chain_content' ).html( data.html ).show();

                    if( parent_wrapper.find('.wpc_expand_older_messages').length == 0 ) {
                        parent_wrapper.find('.wpc_msg_collapse_button').addClass('disabled');
                    } else {
                        parent_wrapper.find('.wpc_msg_collapse_button').removeClass('disabled');
                    }

                    parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).hide();
                    parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).hide();
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( '.wpc_msg_chain_content' ).on( 'click', '.wpc_msg_send_answer', function() {
        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );
        var chain_id = obj.data( 'chain_id' );

        jQuery('#wpc_msg_noitce').remove();

        if( parent_wrapper.find( '.wpc_msg_answer_content').val() == '' ) {
            wpc_msg_add_notice( wpc_private_message_data.texts.error_content, 'error');
            return false;
        }

        if( parent_wrapper.find( '.wpc_msg_answer_cc_email').length > 0 ) {
            if ( parent_wrapper.find('.wpc_msg_answer_cc_email').val() != '' && !isValidEmailAddress( parent_wrapper.find('.wpc_msg_answer_cc_email').val())) {
                wpc_msg_add_notice(wpc_private_message_data.texts.error_cc_email, 'error');
                return false;
            }
        }

        var content = jQuery.base64Encode( JSON.stringify( parent_wrapper.find( '.wpc_msg_answer_content').val() ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );

        loading = true;
        obj.parent().find('.wpc_ajax_loading').show();
        obj.attr('disabled',true);
        jQuery.ajax({
            type: 'POST',
            url: wpc_private_message_data.ajax_url,
            data: {
                action : 'wpc_message_reply',
                chain_id : chain_id,
                content : content,
                cc_email : parent_wrapper.find( '.wpc_msg_answer_cc_email').val(),
                client_id : wpc_private_message_data.client_id,
                security : wpc_private_message_data._wpnonce
            },
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    parent_wrapper.find( '.wpc_ajax_overflow' ).hide();
                    wpc_msg_add_notice( data.message, 'error' );
                } else {
                    parent_wrapper.find( '.wpc_msg_chain_answer' ).before( data.html );
                    parent_wrapper.find( '.wpc_msg_answer_content').val('');
                    parent_wrapper.find( '.wpc_msg_answer_cc_email').val('');

                    wpc_msg_add_notice( data.message, 'updated' );
                }
                loading = false;
                obj.parent().find('.wpc_ajax_loading').hide();
                obj.attr('disabled',false);
            }
        });
    });

    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_back_answer', function() {
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        parent_wrapper.find('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');

        parent_wrapper.find( '.wpc_msg_add_new_wrapper' ).hide();
        parent_wrapper.find( '.wpc_msg_top_nav_wrapper' ).show();
        parent_wrapper.find( '.wpc_msg_content_wrapper_inner' ).show();
        parent_wrapper.find( '.wpc_msg_chain_content' ).hide();
    });

    jQuery( '.wpc_msg_chain_content' ).on( 'click', '.wpc_msg_collapse_button', function() {
        if( loading ) {
            return false;
        }

        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        var label = jQuery(this).attr('title');
        jQuery(this).attr( 'title', jQuery(this).data('alt_title') ).data( 'alt_title', label );

        if( jQuery(this).find('div').hasClass( 'wpc_msg_expand_image' ) ) {
            jQuery(this).find('div').toggleClass( 'wpc_msg_expand_image' ).toggleClass( 'wpc_msg_collapse_image' );
            parent_wrapper.find( '.wpc_msg_message_line').show();
            parent_wrapper.find( '.wpc_expand_older_messages').hide();
        } else {
            jQuery(this).find('div').toggleClass( 'wpc_msg_expand_image' ).toggleClass( 'wpc_msg_collapse_image' );
            parent_wrapper.find( '.wpc_msg_message_line' ).hide();
            parent_wrapper.find( '.wpc_msg_message_line:first, .wpc_msg_message_line:last' ).show();
            parent_wrapper.find( '.wpc_expand_older_messages .wpc_expand_count' ).html( parent_wrapper.find( '.wpc_msg_message_line').length - 2 );
            parent_wrapper.find( '.wpc_expand_older_messages' ).show();
        }
    });

    jQuery( '.wpc_msg_chain_content' ).on( 'click', '.wpc_expand_older_messages', function() {
        var parent_wrapper = jQuery(this).parents( '.wpc_private_messages_shortcode' );

        parent_wrapper.find('.wpc_msg_collapse_button').trigger('click');
    });


    jQuery( '.wpc_private_messages_shortcode' ).on( 'click', '.wpc_msg_nav_list_collapsed', function(e) {
        if( !jQuery(this).hasClass( 'wpc_msg_menu_opened' ) ) {
            var obj = jQuery(this);
            jQuery( 'body' ).bind( 'click', function( event ) {
                obj.removeClass( 'wpc_msg_menu_opened' );
                jQuery( 'body' ).unbind( event );
            });
        }
        jQuery(this).toggleClass( 'wpc_msg_menu_opened' );
        e.stopPropagation();
    });


    //first load trigger
    jQuery( '.wpc_nav_button' ).first().trigger( 'click' );


    jQuery( window ).resize( function() {
        resize_messages_content();
    });

    function resize_messages_content() {
        if( jQuery(window).width() > 563 ) {
            jQuery( '.wpc_chain_subject' ).each( function() {
                jQuery(this).siblings( '.wpc_chain_last_message' ).css( 'width', 96 - Math.ceil((jQuery(this).width()*1) * 100/(jQuery(this).parent().width()*1)) + '%' );
            });
        } else {
            jQuery( '.wpc_chain_subject' ).each( function() {
                jQuery(this).siblings( '.wpc_chain_last_message' ).css( 'width', ( jQuery(this).parents('tr').width() - 40 - jQuery(this).width() ) + 'px' );
            });
        }
    }

    function isValidEmailAddress(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    }

    function wpc_msg_add_notice( notice, type ) {
        if( jQuery('#wpc_msg_noitce').length == 0 ) {
            jQuery('.wpc_msg_content_wrapper').prepend('<div id="wpc_msg_noitce" class="wpc_msg_' + type + '">' + notice + '</div>');

            setTimeout(function () {
                jQuery('#wpc_msg_noitce').fadeOut(1500, function () {
                    jQuery(this).remove();
                });
            }, 2500);
        }
    }

    function clear_new_message_form( parent_wrapper ) {
        parent_wrapper.find( '.new_message_cc.is_wpc_class' ).wpc_select('enable', parent_wrapper.find( '.new_message_cc' ).data('wpc_select_unique'));
        parent_wrapper.find( '.new_message_cc.is_wpc_class' ).wpc_select('clear');
        parent_wrapper.find( '.new_message_to.is_wpc_class' ).wpc_select('enable', parent_wrapper.find( '.new_message_to' ).data('wpc_select_unique'));
        parent_wrapper.find( '.new_message_to.is_wpc_class' ).wpc_select('clear');
        parent_wrapper.find( '.new_message_cc_email').val('');
        parent_wrapper.find( '.new_message_subject').val('');
        parent_wrapper.find( '.new_message_content').val('');
        parent_wrapper.find( '.new_message_cc').parents( '.wpc_msg_new_message_line').hide();
    }
});