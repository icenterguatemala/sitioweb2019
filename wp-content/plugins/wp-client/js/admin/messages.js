var filter;
var one_time_filter;
jQuery( document ).ready( function() {
    //variable for set/unset loading
    var loading = false;

    jQuery( '.nav_button' ).click( function() {
        if( loading ) {
            return false;
        }
        var obj = jQuery(this);

        //add select class to this element
        jQuery( '.nav_button' ).removeClass( 'selected' );
        jQuery(this).addClass( 'selected' );

        //hide new message form
        jQuery( '.add_new_message' ).hide();
        clear_new_message_form();
        jQuery( '.wpc_msg_chain_content' ).hide();

        //temp wrapper html
        var html = jQuery('.wpc_msg_content_wrapper_inner').html();
        jQuery('.wpc_msg_content_wrapper_inner').html('');

        //reset bulk check
        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        jQuery('.wpc_msg_pagination').data('pagenumber', 1);

        var filters = '';
        if( typeof one_time_filter !== 'undefined' && Object.keys(one_time_filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( one_time_filter ) );
        }

        filter = {};

        jQuery('.wpc_msg_search').val('');

        //show loader
        jQuery( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_list&type=' + jQuery(this).data('list') + '&filters=' + filters + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery('.wpc_msg_content_wrapper_inner').html( html );
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery('.wpc_msg_content_wrapper_inner').html( data.html ).show();

                    resize_messages_content();

                    jQuery('.bulk_actions').hide();
                    jQuery('.bulk_actions').prev().hide();
                    if( obj.data('list') == 'trash' ) {
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="restore"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="leave"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="delete"]').hide();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="archive"]').hide();
                    } else if( obj.data('list') == 'archive' ) {
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="restore"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="delete"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="leave"]').hide();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="archive"]').hide();
                    } else if( obj.data('list') == 'all' ) {
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="delete"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="restore"]').hide();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="leave"]').hide();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="archive"]').show();
                    } else {
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="delete"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="archive"]').show();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="restore"]').hide();
                        jQuery( '.wpc_msg_bulk_actions_wrapper .bulk_actions li[data-action="leave"]').hide();
                    }

                    jQuery('.wpc_msg_bulk_check').data( 'checked_all', false ).data( 'checked_chains', data.ids );

                    if( data.is_empty ) {
                        //hide navigation
                        jQuery( '.wpc_msg_top_nav_wrapper' ).hide();
                        jQuery( '.wpc_msg_pagination' ).hide();
                        jQuery( '.wpc_msg_bulk_all' ).hide();
                        jQuery( '.wpc_msg_filter' ).hide();
                    } else {
                        //show navigation
                        jQuery( '.wpc_msg_top_nav_wrapper' ).show();
                        jQuery( '.wpc_msg_pagination' ).show();
                        jQuery( '.wpc_msg_bulk_all' ).show();
                        jQuery( '.wpc_msg_filter' ).show();
                    }

                    if( data.pagination !== false ) {
                        jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    jQuery( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option[value="date"]').attr('disabled', false);

                    jQuery( '.wpc_msg_filter_by' ).trigger( 'change' );

                    if( typeof one_time_filter !== 'undefined' && Object.keys(one_time_filter).length > 0 ) {
                        filter = one_time_filter;
                        one_time_filter = {};
                    } else {
                        jQuery( '.wpc_msg_active_filters_wrapper').html('');
                        jQuery( '.wpc_msg_search').val('');
                        jQuery( '.wpc_ajax_overflow' ).hide();
                    }
                }
                loading = false;
            }
        });
    });

    jQuery( document ).on( 'click', '.wpc_msg_prev_button', function() {
        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        if( loading ) {
            return false;
        }

        var obj = jQuery(this);

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        //show loader
        jQuery( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_list&type=' + jQuery('.nav_button.selected').data('list') + '&page=' + ( jQuery(this).parents('.wpc_msg_pagination').data('pagenumber')*1 + 1 ) + '&search=' + jQuery('.wpc_msg_search').val() + '&filters=' + filters + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    obj.parents('.wpc_msg_pagination').data('pagenumber', obj.parents('.wpc_msg_pagination').data('pagenumber')*1 + 1 );

                    if( data.pagination !== false ) {
                        jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    if( jQuery('.wpc_msg_bulk_check').data( 'checked_all' ) ) {
                        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false);
                        jQuery( '.wpc_msg_content_wrapper' ).find('.wpc_msg_item').prop( 'checked', true );
                    } else {
                        jQuery('.bulk_actions').hide();
                        jQuery('.bulk_actions').prev().hide();
                    }

                    jQuery( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( document ).on( 'click', '.wpc_msg_next_button', function() {
        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        if( loading ) {
            return false;
        }

        var obj = jQuery(this);

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        //show loader
        jQuery( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_list&type=' + jQuery('.nav_button.selected').data('list') + '&page=' + ( jQuery(this).parents('.wpc_msg_pagination').data('pagenumber')*1 - 1 ) + '&search=' + jQuery('.wpc_msg_search').val() + '&filters=' + filters + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    obj.parents('.wpc_msg_pagination').data('pagenumber', obj.parents('.wpc_msg_pagination').data('pagenumber')*1 - 1 );

                    if( data.pagination !== false ) {
                        jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    if( jQuery('.wpc_msg_bulk_check').data( 'checked_all' ) ) {
                        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', true ).prop( 'indeterminate', false);
                        jQuery( '.wpc_msg_content_wrapper' ).find('.wpc_msg_item').prop( 'checked', true );
                    } else {
                        jQuery('.bulk_actions').hide();
                        jQuery('.bulk_actions').prev().hide();
                    }

                    jQuery( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });

    jQuery( document ).on( 'click', '.wpc_msg_refresh_button', function() {
        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        if( loading ) {
            return false;
        }

        var obj = jQuery(this);

        if( obj.data('object') == 'chains' ) {
            var filters = '';
            if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
                filters = jQuery.base64Encode( JSON.stringify( filter ) );
            }

            //reset bulk check
            jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
            jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);

            //show loader
            jQuery( '.wpc_ajax_overflow' ).show();
            loading = true;
            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_get_list&type=' + jQuery('.nav_button.selected').data('list') + '&page=' + ( jQuery(this).parents('.wpc_msg_pagination').data('pagenumber')*1 ) + '&search=' + jQuery('.wpc_msg_search').val() + '&filters=' + filters + '&client_id=' + client_id + '&security=' + _wpnonce,
                dataType: "json",
                success: function( data ) {
                    if( !data.status ) {
                        jQuery( '.wpc_ajax_overflow' ).hide();
                        alert( data.message );
                    } else {
                        jQuery('.wpc_msg_content_wrapper_inner').html( data.html );

                        resize_messages_content();

                        jQuery('.bulk_actions').hide();
                        jQuery('.bulk_actions').prev().hide();

                        jQuery('.wpc_msg_bulk_check').data( 'checked_all', false );

                        if( data.is_empty ) {
                            //hide navigation
                            jQuery( '.wpc_msg_top_nav_wrapper' ).hide();
                            jQuery( '.wpc_msg_pagination' ).hide();
                            jQuery( '.wpc_msg_bulk_all' ).hide();
                        } else {
                            //show navigation
                            jQuery( '.wpc_msg_top_nav_wrapper' ).show();
                            jQuery( '.wpc_msg_pagination' ).show();
                            jQuery( '.wpc_msg_bulk_all' ).show();
                        }

                        if( data.pagination !== false ) {
                            jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                            jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                            jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                            if( data.pagination.current_page > 1 ) {
                                jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                            } else {
                                jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                            }

                            if( data.pagination.pages_count == data.pagination.current_page ) {
                                jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                            } else {
                                jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                            }
                        }

                        jQuery( '.wpc_ajax_overflow' ).hide();
                    }
                    loading = false;
                }
            });
        } else if( obj.data('object') == 'chain' ) {
            //show loader
            jQuery( '.wpc_ajax_overflow' ).show();
            loading = true;
            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_get_chain&chain_id=' + obj.data('chain_id'),
                dataType: "json",
                success: function( data ) {
                    if( !data.status ) {
                        jQuery( '.wpc_ajax_overflow' ).hide();
                        alert( data.message );
                    } else {
                        jQuery( '.wpc_msg_chain_content' ).html( data.html );

                        if( jQuery('.expand_older_messages').length == 0 ) {
                            jQuery('.wpc_msg_collapse_button').addClass('disabled');
                        } else {
                            jQuery('.wpc_msg_collapse_button').removeClass('disabled');
                        }

                        jQuery( '.wpc_ajax_overflow' ).hide();
                    }
                    loading = false;
                }
            });
        }

    });


    jQuery( '.wpc_msg_search_button' ).click( function() {
        var obj = jQuery( this );

        var was_opened = obj.hasClass( 'opened' );
        if( !was_opened ) {
            obj.toggleClass( 'opened' );
        }

        obj.siblings( '.wpc_msg_search' ).animate({
            width: "toggle"
        }, 1000, function() {
            if( was_opened ) {
                obj.toggleClass( 'opened' );
            }
        });
    });

    jQuery( document ).on( 'change', '.wpc_msg_search', function() {
        if( loading ) {
            return false;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
        jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);

        //show loader
        jQuery( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_list&type=' + jQuery('.nav_button.selected').data('list') + '&search=' + jQuery(this).val() + '&filters=' + filters + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    jQuery('.bulk_actions').hide();
                    jQuery('.bulk_actions').prev().hide();

                    jQuery('.wpc_msg_bulk_check').data( 'checked_all', false ).data( 'checked_chains', data.ids );

                    jQuery('.wpc_msg_pagination').data('pagenumber', 1);

                    if( data.is_empty ) {
                        //hide navigation
                        jQuery( '.wpc_msg_pagination' ).hide();
                        jQuery( '.wpc_msg_bulk_all' ).hide();

                        if( !( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) ) {
                            jQuery( '.wpc_msg_filter' ).hide();
                        }
                    } else {
                        //show navigation
                        jQuery( '.wpc_msg_pagination' ).show();
                        jQuery( '.wpc_msg_bulk_all' ).show();
                        jQuery( '.wpc_msg_filter' ).show();
                    }

                    if( data.pagination !== false ) {
                        jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    jQuery( '.wpc_msg_filter_by' ).trigger( 'change' );

                    jQuery( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });


    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_item', function(e) {
        var checkedCount = jQuery( '.wpc_msg_item:checked' ).length;
        var checksCount = jQuery( '.wpc_msg_item' ).length;

        if( checkedCount == 0 ) {
            jQuery('.bulk_actions').hide();
            jQuery('.bulk_actions').prev().hide();
        } else {
            jQuery('.bulk_actions').show();
            jQuery('.bulk_actions').prev().show();
        }

        if( checkedCount == 0 ) {
            jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
            jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);
        } else if( checkedCount == checksCount ) {
            jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', true );
            jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);
        } else if( checkedCount > 0 && checkedCount < checksCount ) {
            jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
            jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', true);
        }

        jQuery('.wpc_msg_bulk_check').data('checked_all', false);

        e.stopPropagation();
    });

    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_bulk_check', function(e) {
        if( jQuery(this).is(':checked') ) {
            jQuery( '.wpc_msg_item' ).prop( 'checked', true );
            jQuery('.bulk_actions').show();
            jQuery('.bulk_actions').prev().show();
        } else {
            jQuery( '.wpc_msg_item' ).prop( 'checked', false );
            jQuery('.bulk_actions').hide();
            jQuery('.bulk_actions').prev().hide();
        }

        jQuery('.wpc_msg_bulk_check').data('checked_all', false);
        e.stopPropagation();
    });

    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_bulk_all', function(e) {
        jQuery(this).toggleClass( 'bulk_opened' );

        if( jQuery( '.wpc_msg_filter' ).hasClass( 'filter_opened' ) ) {
            jQuery( '.wpc_msg_filter' ).removeClass( 'filter_opened' );
        }

        e.stopPropagation();

        jQuery( 'body' ).bind( 'click', function( event ) {
            jQuery( '.wpc_msg_bulk_all' ).removeClass( 'bulk_opened' );
            jQuery( 'body' ).unbind( event );
        });
    });

    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_bulk_actions_wrapper', function(e){
        e.preventDefault();
        e.stopPropagation();
    });


    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.bulk_select li', function() {
        var select = jQuery(this).data( 'select' );

        if( select == 'all' ) {
            if( jQuery('.wpc_msg_bulk_check').data('checked_all') == true ) {
                jQuery('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
                return false;
            } else {
                jQuery( '.wpc_msg_bulk_check' ).data( 'checked_all', true ).prop( 'checked', true ).prop( 'indeterminate', false );
                jQuery( '.wpc_msg_item' ).prop( 'checked', true );
            }
            jQuery('.bulk_actions').show();
            jQuery('.bulk_actions').prev().show();
        } else if( select == 'all_page' ) {
            if( jQuery('.wpc_msg_bulk_check').is(':checked') ) {
                jQuery('.wpc_msg_bulk_check').data('checked_all', false);
                jQuery('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
                return false;
            } else {
                jQuery( '.wpc_msg_bulk_check' ).data('checked_all', false).prop( 'checked', true ).prop( 'indeterminate', false);
                jQuery( '.wpc_msg_item' ).prop( 'checked', true );
            }
            jQuery('.bulk_actions').show();
            jQuery('.bulk_actions').prev().show();
        } else if( select == 'none' ) {
            jQuery( '.wpc_msg_bulk_check' ).data('checked_all', false).prop( 'checked', false ).prop( 'indeterminate', false);
            jQuery( '.wpc_msg_item' ).prop( 'checked', false );

            jQuery('.bulk_actions').hide();
            jQuery('.bulk_actions').prev().hide();
        } else if( select == 'unread' ) {
            jQuery( '.wpc_msg_item[data-new="true"]' ).prop( 'checked', true );
            jQuery( '.wpc_msg_item[data-new="false"]' ).prop( 'checked', false );

            var checkedCount = jQuery( '.wpc_msg_item:checked' ).length;
            var checksCount = jQuery( '.wpc_msg_item' ).length;

            if( checkedCount == 0 ) {
                jQuery('.bulk_actions').hide();
                jQuery('.bulk_actions').prev().hide();
            } else {
                jQuery('.bulk_actions').show();
                jQuery('.bulk_actions').prev().show();
            }

            jQuery( '.wpc_msg_bulk_check' ).data('checked_all', false);
            if( checkedCount == 0 ) {
                jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
                jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);
            } else if( checkedCount == checksCount ) {
                jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', true );
                jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);
            } else if( checkedCount > 0 && checkedCount < checksCount ) {
                jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
                jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', true);
            }
        } else if( select == 'read' ) {
            jQuery( '.wpc_msg_item[data-new="true"]' ).prop( 'checked', false );
            jQuery( '.wpc_msg_item[data-new="false"]' ).prop( 'checked', true );

            var checkedCount = jQuery( '.wpc_msg_item:checked' ).length;
            var checksCount = jQuery( '.wpc_msg_item' ).length;

            if( checkedCount == 0 ) {
                jQuery('.bulk_actions').hide();
                jQuery('.bulk_actions').prev().hide();
            } else {
                jQuery('.bulk_actions').show();
                jQuery('.bulk_actions').prev().show();
            }

            jQuery( '.wpc_msg_bulk_check' ).data('checked_all', false);
            if( checkedCount == 0 ) {
                jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
                jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);
            } else if( checkedCount == checksCount ) {
                jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', true );
                jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);
            } else if( checkedCount > 0 && checkedCount < checksCount ) {
                jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
                jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', true);
            }
        }

        jQuery('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
    });


    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.bulk_actions li', function() {
        var action = jQuery(this).data( 'action' );

        if( jQuery( '.wpc_msg_item:checked' ).length <= 0 ) {
            return false;
        }

        var chain_ids = [];
        if( jQuery('.wpc_msg_bulk_check').data('checked_all') ) {
            chain_ids = jQuery('.wpc_msg_bulk_check').data('checked_chains');
        } else {
            jQuery( '.wpc_msg_item:checked' ).each( function() {
                chain_ids.push( jQuery(this).val() );
            });
        }
        chain_ids = jQuery.base64Encode( JSON.stringify( chain_ids ) );

        if( action == 'read' ) {
            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_chain_mark_read&chain_ids=' + chain_ids + '&client_id=' + client_id + '&security=' + _wpnonce,
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        wpc_msg_add_notice(texts.read, 'updated');

                        jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                    }
                }
            });
        } else if( action == 'archive' ) {
            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_chain_to_archive&chain_ids=' + chain_ids + '&client_id=' + client_id + '&security=' + _wpnonce,
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        if( data.count > 0 ) {
                            if (jQuery('.wpc_msg_prev_button').hasClass('disabled') && '1' != jQuery('.wpc_msg_pagination').data('pagenumber')) {
                                jQuery('.wpc_msg_pagination').data('pagenumber', jQuery('.wpc_msg_pagination').data('pagenumber') * 1 - 1);
                            }

                            wpc_msg_add_notice(texts.archived, 'updated');
                            jQuery('.nav_button.archive').trigger('click');
                        } else {
                            jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                }
            });
        } else if( action == 'delete' ) {
            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_chain_to_trash&chain_ids=' + chain_ids + '&client_id=' + client_id + '&security=' + _wpnonce,
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        if( data.count > 0 ) {
                            if (jQuery('.wpc_msg_prev_button').hasClass('disabled') && '1' != jQuery('.wpc_msg_pagination').data('pagenumber')) {
                                jQuery('.wpc_msg_pagination').data('pagenumber', jQuery('.wpc_msg_pagination').data('pagenumber') * 1 - 1);
                            }

                            wpc_msg_add_notice(texts.trashed, 'updated');

                            jQuery('.nav_button.trash').trigger('click');
                        } else {
                            jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                }
            });
        } else if( action == 'leave' ) {
            if( confirm( 'Are you sure?' ) ) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: 'action=wpc_message_leave_chain&chain_ids=' + chain_ids + '&client_id=' + client_id + '&security=' + _wpnonce,
                    dataType: "json",
                    success: function (data) {
                        if (!data.status) {
                            alert(data.message);
                        } else {
                            if (jQuery('.wpc_msg_prev_button').hasClass('disabled') && '1' != jQuery('.wpc_msg_pagination').data('pagenumber')) {
                                jQuery('.wpc_msg_pagination').data('pagenumber', jQuery('.wpc_msg_pagination').data('pagenumber') * 1 - 1);
                            }

                            wpc_msg_add_notice(texts.leaved, 'updated');

                            jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                });
            }
        } else if( action == 'delete_permanently' ) {
            if( confirm( 'Are you sure?' ) ) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: 'action=wpc_message_chain_delete_permanently&chain_ids=' + chain_ids + '&client_id=' + client_id + '&security=' + _wpnonce,
                    dataType: "json",
                    success: function (data) {
                        if (!data.status) {
                            alert(data.message);
                        } else {
                            if (jQuery('.wpc_msg_prev_button').hasClass('disabled') && '1' != jQuery('.wpc_msg_pagination').data('pagenumber')) {
                                jQuery('.wpc_msg_pagination').data('pagenumber', jQuery('.wpc_msg_pagination').data('pagenumber') * 1 - 1);
                            }

                            wpc_msg_add_notice(texts.deleted, 'updated');

                            jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                        }
                    }
                });
            }
        } else if( action == 'restore' ) {
            var current_list = jQuery('.nav_button.selected').data('list');

            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_chain_restore&chain_ids=' + chain_ids + '&from=' + current_list + '&client_id=' + client_id + '&security=' + _wpnonce,
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        if( jQuery('.wpc_msg_prev_button').hasClass('disabled') && '1' != jQuery('.wpc_msg_pagination').data('pagenumber') ) {
                            jQuery('.wpc_msg_pagination').data('pagenumber', jQuery('.wpc_msg_pagination').data('pagenumber')*1 - 1);
                        }

                        wpc_msg_add_notice(texts.restored, 'updated');

                        jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
                    }
                }
            });
        }

        jQuery('.wpc_msg_bulk_all').removeClass( 'bulk_opened' );
    });


    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_filter', function(e) {
        jQuery(this).toggleClass( 'filter_opened' );

        if( jQuery( '.wpc_msg_bulk_all' ).hasClass( 'bulk_opened' ) ) {
            jQuery( '.wpc_msg_bulk_all' ).removeClass( 'bulk_opened' );
        }

        e.stopPropagation();

        jQuery( 'body' ).bind( 'click', function( event ) {
            jQuery( '.wpc_msg_filter' ).removeClass( 'filter_opened' );
            jQuery( 'body' ).unbind( event );
        });
    });

    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_filter_wrapper', function(e){
        e.preventDefault();
        e.stopPropagation();
    });

    jQuery( '.wpc_msg_content_wrapper' ).on( 'change', '.wpc_msg_filter_by', function() {
        var obj = jQuery(this);

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        obj.parents('.wpc_msg_filter_wrapper').find( '.wpc_ajax_content' ).addClass( 'wpc_is_loading' );
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_filter&type=' + jQuery('.nav_button.selected').data('list') + '&search=' + jQuery('.wpc_msg_search').val() + '&by=' + jQuery(this).val() + '&filters=' + filters,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents('.wpc_msg_filter_wrapper').find( '.wpc_msg_filter_selectors' ).html( data.filter_html );

                    if( obj.val() == 'member' ) {
                        jQuery( '.wpc_msg_filter_members' ).wpc_select({
                            search:true,
                            opacity:'0.2'
                        });
                    } else if( obj.val() == 'date' ) {
                        if( typeof( custom_datepicker_init ) !== 'undefined' ) {
                            custom_datepicker_init();
                        }

                        jQuery('.from_date_field').datepicker( "option", {
                            minDate: new Date( data.mindate*1000 ),
                            maxDate: new Date( data.maxdate*1000 ),
                            onClose: function( selectedDate ) {
                                jQuery('.to_date_field').datepicker( "option", "minDate", selectedDate );
                            }
                        });

                        jQuery('.to_date_field').datepicker( "option", {
                            minDate: new Date( data.mindate*1000 ),
                            maxDate: new Date( data.maxdate*1000 ),
                            onClose: function( selectedDate ) {
                                jQuery('.from_date_field').datepicker( "option", "maxDate", selectedDate );
                            }
                        });
                    }

                    obj.parents('.wpc_msg_filter_wrapper').find( '.wpc_ajax_content' ).removeClass( 'wpc_is_loading' );
                }
            }
        });
    });


    //change filtering reload
    jQuery( '.wpc_msg_content_wrapper' ).on( 'change', '.wpc_msg_active_filters_wrapper', function() {
        if( loading ) {
            return false;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter ) );
        }

        //reset bulk check
        jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false ).prop( 'indeterminate', false);

        //show loader
        jQuery( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_list&type=' + jQuery('.nav_button.selected').data('list') + '&search=' + jQuery('.wpc_msg_search').val() + '&filters=' + filters + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery('.wpc_msg_content_wrapper_inner').html( data.html );

                    resize_messages_content();

                    jQuery('.bulk_actions').hide();
                    jQuery('.bulk_actions').prev().hide();

                    jQuery('.wpc_msg_bulk_check').data( 'checked_all', false ).data( 'checked_chains', data.ids );

                    jQuery('.wpc_msg_pagination').data('pagenumber', 1);

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
                        jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                        jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                        jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                        if( data.pagination.current_page > 1 ) {
                            jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                        }

                        if( data.pagination.pages_count == data.pagination.current_page ) {
                            jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                        } else {
                            jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                        }
                    }

                    jQuery( '.wpc_msg_filter_by' ).trigger( 'change' );

                    jQuery( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });


    //add filter
    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_msg_add_filter', function() {
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
                url: ajax_url,
                data: 'action=wpc_message_get_filter_data&filter_by=' + filter_by + '&member_id=' + member_id,
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        jQuery( '.wpc_msg_active_filters_wrapper' ).append(
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
                url: ajax_url,
                data: 'action=wpc_message_get_filter_data&filter_by=' + filter_by + '&from=' + filter[filter_by]['from'] + '&to=' + filter[filter_by]['to'],
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        jQuery( '.wpc_msg_active_filters_wrapper' ).append(
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

        jQuery('.wpc_msg_filter').removeClass( 'filter_opened' );
    });


    //remove filters
    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.wpc_remove_filter', function() {
        var obj = jQuery(this);

        var filter_before = filter;

        var filter_by = obj.parents( '.wpc_filter_wrapper' ).data('filter_by');

        if( filter_by == 'member' ) {
            var member_id = obj.parents( '.wpc_filter_wrapper' ).data('member_id');

            var index = filter_before[filter_by].indexOf( member_id.toString() );
            if( index > -1 ) {
                filter[filter_by].splice( index, 1 );
            }
        } else if( filter_by == 'date' ) {
            delete filter.date;

            jQuery( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option[value="date"]').attr('disabled', false);
            jQuery( '.wpc_msg_filter' ).find('.wpc_msg_filter_by').trigger( 'change' );
        }

        jQuery( '.wpc_msg_active_filters_wrapper' ).trigger( 'change' );
        obj.parents( '.wpc_filter_wrapper' ).remove();
    });




    // Toggle list table rows on small screens
    jQuery( '.wpc_msg_content_wrapper' ).on( 'click', '.toggle-row', function() {
        jQuery( this ).closest( 'tr' ).toggleClass( 'is-expanded' );
        resize_messages_content();
    });












    jQuery( '.wpc_tab_container_block' ).on( 'click', '.wpc_msg_new_message_button', function() {
        if( loading ) {
            return false;
        }

        jQuery( '.add_new_message' ).show();
        jQuery( '.wpc_msg_top_nav_wrapper' ).hide();
        jQuery( '.wpc_msg_content_wrapper_inner' ).hide();
        jQuery( '.wpc_msg_chain_content' ).hide().html('');
    });


    jQuery( '.wpc_tab_container_block' ).on( 'click', '#back_new_message', function() {
        jQuery( '.add_new_message' ).hide();
        jQuery( '.wpc_msg_top_nav_wrapper' ).show();
        jQuery( '.wpc_msg_content_wrapper_inner' ).show();
        jQuery( '.wpc_msg_chain_content' ).hide();

        if( jQuery( '.nav_button.selected').length > 0 ) {
            jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');
        } else {
            jQuery( '.nav_button' ).first().trigger( 'click' );
        }

        clear_new_message_form();
    });


    jQuery( '.wpc_tab_container_block' ).on( 'click', '#back_answer', function() {
        if( jQuery( '.nav_button.selected').length > 0 ) {
            jQuery('.wpc_msg_refresh_button[data-object="chains"]').trigger('click');

            jQuery('.add_new_message').hide();
            jQuery('.wpc_msg_top_nav_wrapper').show();
            jQuery('.wpc_msg_content_wrapper_inner').show();
            jQuery('.wpc_msg_chain_content').hide();
        } else {
            jQuery( '.nav_button' ).first().trigger( 'click' );
        }
    });


    jQuery( '#new_message_to' ).wpc_select({
        search:true,
        opacity:'0.2'
    });

    jQuery( '#new_message_to' ).change( function() {
        var obj = jQuery(this);
        var connected_field = obj.parents( '.add_new_message' ).find( '#new_message_cc' ).parents( '.new_message_line' );

        if( obj.val() == null ) {
            obj.parents( '.add_new_message' ).find( '#new_message_cc' ).attr( 'disabled', true ).html('');
            obj.parents( '.add_new_message' ).find( '#new_message_cc' ).wpc_select('destroy', jQuery( '#new_message_cc' ).data('wpc_select_unique'));
            connected_field.hide();
            return false;
        }

        if( jQuery(this).find( 'option:selected' ).length > 1 ) {
            jQuery('#send_new_message').val( texts.send_messages );
        } else {
            jQuery('#send_new_message').val( texts.send_message );
        }

        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_connected_members&to=' + jQuery.base64Encode( JSON.stringify( obj.val() ) ),
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    alert( data.message );
                } else {
                    if( data.selectbox_content.length > 0 ) {
                        obj.parents( '.add_new_message' ).find( '#new_message_cc' ).attr( 'disabled', false ).html( data.selectbox_content );

                        obj.parents( '.add_new_message' ).find( '#new_message_cc' ).wpc_select({
                            search:true,
                            opacity:'0.2',
                            onChangeValue:function() {
                                if( obj.parents( '.add_new_message' ).find( '#new_message_cc option:selected' ).length >= 1 ) {
                                    jQuery( '#new_message_to' ).wpc_select('disable', jQuery( '#new_message_to' ).data('wpc_select_unique'));
                                    jQuery('#send_new_message').val( texts.group_dialog );
                                } else {
                                    jQuery( '#new_message_to' ).wpc_select('enable', jQuery( '#new_message_to' ).data('wpc_select_unique'));
                                    jQuery('#send_new_message').val( texts.send_message );
                                }
                            }
                        });

                        connected_field.show();
                    } else {
                        obj.parents( '.add_new_message' ).find( '#new_message_cc' ).attr( 'disabled', true ).html('');
                        obj.parents( '.add_new_message' ).find( '#new_message_cc' ).wpc_select('destroy', jQuery( '#new_message_cc' ).data('wpc_select_unique'));
                        connected_field.hide();
                    }
                }
            }
        });
    });


    jQuery( '.wpc_tab_container_block' ).on( 'click', '.wp-list-table.messages tr:not(.no-items)', function() {
        if( loading ) {
            return false;
        }

        var obj = jQuery(this);
        var chain_id = obj.find( '.wpc_msg_item').val();

        jQuery( '.wpc_ajax_overflow' ).show();
        loading = true;
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_get_chain&chain_id=' + chain_id + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery( '.wpc_msg_chain_content' ).html( data.html).show();

                    if( jQuery('.expand_older_messages').length == 0 ) {
                        jQuery('.wpc_msg_collapse_button').addClass('disabled');
                    } else {
                        jQuery('.wpc_msg_collapse_button').removeClass('disabled');
                    }

                    jQuery( '.wpc_msg_top_nav_wrapper' ).hide();
                    jQuery( '.wpc_msg_content_wrapper_inner' ).hide();
                    jQuery( '.wpc_ajax_overflow' ).hide();
                }
                loading = false;
            }
        });
    });


    jQuery( '.wpc_msg_chain_content' ).on('click', '#send_answer', function() {
        if( loading ) {
            return false;
        }

        jQuery('#wpc_msg_notice').remove();

        if( jQuery( '#answer_content' ).val() == '' ) {
            wpc_msg_add_notice(texts.error_content, 'error');
            return false;
        }

        if( jQuery( '#answer_cc_email').length > 0 ) {
            if (jQuery('#answer_cc_email').val() != '' && !isValidEmailAddress(jQuery('#answer_cc_email').val())) {
                wpc_msg_add_notice(texts.error_cc_email, 'error');
                return false;
            }
        }

        var obj = jQuery(this);
        var chain_id = obj.data( 'chain_id' );

        var content = jQuery.base64Encode( JSON.stringify( jQuery( '#answer_content' ).val() ) ).replace( /\+/g, "-" );

        loading = true;
        obj.parent().find('.wpc_ajax_loading').show();
        obj.attr('disabled',true);
        jQuery.ajax({
            type: 'POST',
            url: ajax_url,
            data: 'action=wpc_message_reply&chain_id=' + chain_id + '&content=' + content + '&cc_email=' + jQuery( '#answer_cc_email' ).val() + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ) {
                if( !data.status ) {
                    jQuery( '.wpc_ajax_overflow' ).hide();
                    alert( data.message );
                } else {
                    jQuery( '.wpc_msg_chain_answer' ).before( data.html );
                    jQuery( '#answer_content').val('');
                    jQuery( '#answer_cc_email').val('');
                }
                loading = false;
                obj.parent().find('.wpc_ajax_loading').hide();
                obj.attr('disabled',false);
            }
        });
    });

    jQuery( '.wpc_msg_chain_content' ).on( 'click', '.wpc_msg_collapse_button', function() {
        if( loading ) {
            return false;
        }

        if( jQuery(this).hasClass('disabled') ) {
            return false;
        }

        var label = jQuery(this).attr('title');
        jQuery(this).attr( 'title', jQuery(this).data('alt_title') ).data( 'alt_title', label );


        if( jQuery(this).find('div').hasClass( 'wpc_msg_expand_image' ) ) {
            jQuery(this).find('div').toggleClass( 'wpc_msg_expand_image' ).toggleClass( 'wpc_msg_collapse_image' );

            jQuery( '.wpc_msg_message_line').show();
            jQuery( '.expand_older_messages').hide();
        } else {
            jQuery(this).find('div').toggleClass( 'wpc_msg_expand_image' ).toggleClass( 'wpc_msg_collapse_image' );
            jQuery( '.wpc_msg_message_line' ).hide();
            jQuery( '.wpc_msg_message_line:first, .wpc_msg_message_line:last' ).show();
            jQuery( '.expand_older_messages .expand_count' ).html( jQuery( '.wpc_msg_message_line').length - 2 );
            jQuery( '.expand_older_messages' ).show();
        }
    });

    jQuery( '.wpc_msg_chain_content' ).on( 'click', '.expand_older_messages', function() {
        jQuery('.wpc_msg_collapse_button').trigger('click');
    });


    jQuery('#send_new_message').click( function(){
        jQuery('#wpc_msg_notice').remove();

        if( jQuery( '#new_message_to').val() == null ) {
            wpc_msg_add_notice(texts.error_to, 'error');
            return false;
        }
        if( jQuery( '#new_message_subject').val() == '' ) {
            wpc_msg_add_notice(texts.error_subject, 'error');
            return false;
        }
        if( jQuery( '#new_message_content').val() == '' ) {
            wpc_msg_add_notice(texts.error_content, 'error');
            return false;
        }

        if( jQuery( '#new_message_cc_email').length > 0 && jQuery( '#new_message_cc_email').val() != '' && !isValidEmailAddress( jQuery( '#new_message_cc_email').val() ) ) {
            wpc_msg_add_notice(texts.error_cc_email, 'error');
            return false;
        }

        return true;
    });

    jQuery( window ).resize( function() {
        resize_messages_content();
    });

    //back to top js
    var offset = 220;
    var duration = 500;
    jQuery( window ).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
    });

    jQuery( '.back-to-top' ).click(function(event) {
        event.preventDefault();
        jQuery( 'html, body' ).animate({scrollTop: 0}, duration);
        return false;
    });

    if( typeof( window.location.search ) !== 'undefined' ) {
        //parse GET parameters
        var get_attr = {};
        var get_str = window.location.search.substring(1).split("&");

        for (var i=0; i<get_str.length; i++) {
            var temp = get_str[i].split("=");
            get_attr[temp[0]] = temp[1];
        }

        if( typeof( get_attr.send_message ) !== 'undefined' ) {
            //open new message to client form
            jQuery('#new_message_to').find( 'option[value="' + get_attr.send_message + '"]' ).attr( 'selected', true ).prop( 'selected', true );
            jQuery('#new_message_to').wpc_select('load_values');
            jQuery('#new_message_to').trigger( 'change' );
            jQuery('#new_message').trigger( 'click' );
        } else if( typeof( get_attr.filter_client ) !== 'undefined' ) {
            //open new message to client form
            if( typeof one_time_filter === 'undefined' ) {
                one_time_filter = {};
            }

            if( !one_time_filter.hasOwnProperty( 'member' ) ) {
                one_time_filter['member'] = [];
            }

            one_time_filter['member'].push( get_attr.filter_client );

            jQuery( '.nav_button' ).first().trigger( 'click' );

            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_get_filter_data&filter_by=member&member_id=' + get_attr.filter_client,
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        jQuery( '.wpc_msg_active_filters_wrapper' ).append(
                            '<div class="wpc_filter_wrapper" data-filter_by="member" data-member_id="' + get_attr.filter_client + '">' +
                            data.title + ': ' + data.name +
                            '<div class="wpc_remove_filter">&times;</div>' +
                            '</div>'
                        );

                        jQuery( '.wpc_ajax_overflow' ).hide();
                    }
                }
            });
        } else if( typeof( get_attr.read_reply ) !== 'undefined' ) {
            var obj = jQuery(this);
            var chain_id = get_attr.read_reply;

            jQuery( '.wpc_ajax_overflow' ).show();
            loading = true;
            jQuery.ajax({
                type: 'POST',
                url: ajax_url,
                data: 'action=wpc_message_get_chain&chain_id=' + chain_id,
                dataType: "json",
                success: function( data ) {
                    if( !data.status ) {
                        jQuery( '.wpc_ajax_overflow' ).hide();
                        alert( data.message );
                    } else {
                        jQuery( '.wpc_msg_chain_content' ).html( data.html).show();

                        if( jQuery('.expand_older_messages').length == 0 ) {
                            jQuery('.wpc_msg_collapse_button').addClass('disabled');
                        } else {
                            jQuery('.wpc_msg_collapse_button').removeClass('disabled');
                        }

                        jQuery( '.wpc_msg_top_nav_wrapper' ).hide();
                        jQuery( '.wpc_msg_content_wrapper_inner' ).hide();
                        jQuery( '.wpc_ajax_overflow' ).hide();
                    }
                    loading = false;
                }
            });
        } else {
            //first load trigger
            jQuery( '.nav_button' ).first().trigger( 'click' );
        }
    }

    function resize_messages_content() {
        jQuery( '.wpc_chain_subject' ).each( function() {
            jQuery(this).siblings( '.wpc_chain_last_message' ).css( 'width', 96 - Math.ceil((jQuery(this).width()*1) * 100/(jQuery(this).parent().width()*1)) + '%' );
        });
    }

    function isValidEmailAddress(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    }

    function clear_new_message_form() {
        jQuery( '#new_message_cc.is_wpc_class' ).wpc_select('enable', jQuery( '#new_message_cc' ).data('wpc_select_unique'));
        jQuery( '#new_message_cc.is_wpc_class' ).wpc_select('clear');
        jQuery( '#new_message_to.is_wpc_class' ).wpc_select('enable', jQuery( '#new_message_to' ).data('wpc_select_unique'));
        jQuery( '#new_message_to.is_wpc_class' ).wpc_select('clear');
        jQuery( '#new_message_cc_email').val('');
        jQuery( '#new_message_subject').val('');
        jQuery( '#new_message_content').val('');
        jQuery( '#new_message_cc').parents( '.new_message_line').hide();
    }

    function wpc_msg_add_notice( notice, notice_class ) {
        if( jQuery('#wpc_msg_notice').length == 0 ) {
            jQuery('.wpc_msg_content_wrapper').prepend('<div id="wpc_msg_notice" class="' + notice_class + ' wpc_notice fade"><p>' + notice + '</p></div>');

            setTimeout(function () {
                jQuery('#wpc_msg_notice').fadeOut(1500, function () {
                    jQuery(this).remove();
                });
            }, 2500);
        }
    }
});