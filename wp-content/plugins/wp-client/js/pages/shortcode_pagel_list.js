var sort = new Object();

jQuery(document).ready(function() {

    /* Sorting */
    if( jQuery( '.wpc_client_client_pages_list .wpc_sorting' ).length > 0 ) {
        jQuery( '.wpc_client_client_pages_list .wpc_sorting' ).each( function() {
            sort[jQuery(this).parents( '.wpc_client_client_pages_list' ).data( 'form_id' )] = jQuery(this).val();
        });
    }

    jQuery( '.wpc_client_client_pages_list .wpc_add_sort' ).click( function(e) {

        jQuery( '.wpc_add_sort' ).not( this ).parents( '.wpc_sort_block' ).removeClass( 'sorted' );
        jQuery(this).parents( '.wpc_sort_block' ).toggleClass( 'sorted' );

        e.stopPropagation();

        jQuery( 'body' ).bind( 'click', function( event ) {
            jQuery( '.wpc_add_sort' ).parents( '.wpc_sort_block' ).removeClass( 'sorted' );
            jQuery( 'body' ).unbind( event );
        });
    });

    jQuery( '.wpc_client_client_pages_list .wpc_sort_contect' ).click( function(e) {
        e.stopPropagation();
    });

    jQuery( '.wpc_client_client_pages_list .wpc_apply_sort' ).click( function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_list' ).data( 'form_id' );

        var sort_by = obj.parents( '.wpc_sort_contect' ).find( '.wpc_sorting' ).val();
        if( sort[form_id] == sort_by ) {
            return false;
        }
        sort[form_id] = sort_by;

        obj.parents( '.wpc_sort_block' ).removeClass( 'sorted' );
        obj.parents( '.wpc_sort_block' ).find( '.wpc_add_sort' ).val( obj.parents( '.wpc_sort_block' ).find( '.wpc_sorting option:selected' ).html() );

        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var page_number = obj.parents( '.wpc_client_client_pages_list' ).find('.pages_pagination_block').data( 'page_number' );
        var last_category_id = obj.parents( '.wpc_client_client_pages_list' ).find('.pages_pagination_block').data( 'last_category_id' );

        var search = jQuery.trim( obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_search.wpc_searched' ).val() );
        if( typeof search === "undefined" ) {
            search = '';
        }

        var sorting = '';
        if( typeof sort[form_id] !== "undefined" ) {
            sorting = sort[form_id];
        }

        obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_list_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ) + '&search=' + search + '&sorting=' + sorting + '&current_page=' + page_number + '&last_category_id=' + last_category_id + '&client_id=' + client_id + '&security=' + _wpnonce + '&sort_button=1',
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pagelist' ).html( data.html );

                    obj.parents( '.wpc_client_client_pages_list' ).find('.pages_pagination_block').data( 'last_category_id', data.last_category_id );
                }
                obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });
    });

    /*search*/
    jQuery( '.wpc_client_client_pages_list .wpc_pages_search_button' ).click( function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_list' ).data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var page_number = 0;
        var last_category_id = 0;

        var search = jQuery.trim( obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_search' ).val() );
        if( search == '' ) {
            return false;
        }

        var sorting = '';
        if( typeof sort[form_id] !== "undefined" ) {
            sorting = sort[form_id];
        }

        obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_list_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ) + '&search=' + search + '&sorting=' + sorting + '&current_page=' + page_number + '&last_category_id=' + last_category_id + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pagelist' ).html( data.html );

                    obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_search' ).addClass( 'wpc_searched' );
                    obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_pages_clear_search' ).show();

                    if( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_client_pages_list' ).find( '.pages_pagination_block' ).data( 'page_number', 1 ).data( 'last_category_id', data.last_category_id );

                        if( !data.pagination ) {
                            obj.parents( '.wpc_client_client_pages_list' ).find( '.pages_pagination_block' ).hide();
                        } else {
                            obj.parents( '.wpc_client_client_pages_list' ).find( '.pages_pagination_block' ).show();
                        }
                    }

                }

                obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });
    });

    jQuery( '.wpc_client_client_pages_list .wpc_pages_search' ).keyup( function(event) {
        if( event.keyCode == 13 && jQuery(this).val() != '' ) {
            jQuery(this).parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_search_button' ).trigger( 'click' );
            event.stopPropagation();
        }
    });

    jQuery( '.wpc_client_client_pages_list .wpc_pages_clear_search' ).click( function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_list' ).data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var page_number = 0;
        var last_category_id = 0;

        var search = '';

        var sorting = '';
        if( typeof sort[form_id] !== "undefined" ) {
            sorting = sort[form_id];
        }

        obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_list_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ) + '&search=' + search + '&sorting=' + sorting + '&current_page=' + page_number + '&last_category_id=' + last_category_id + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pagelist' ).html( data.html );

                    obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_search' ).removeClass( 'wpc_searched' ).val('');
                    obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_clear_search' ).hide();

                    if( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_client_pages_list' ).find( '.pages_pagination_block' ).data( 'page_number', 1 ).data( 'last_category_id', data.last_category_id );

                        if( !data.pagination ) {
                            obj.parents( '.wpc_client_client_pages_list' ).find( '.pages_pagination_block' ).hide();
                        } else {
                            obj.parents( '.wpc_client_client_pages_list' ).find( '.pages_pagination_block' ).show();
                        }
                    }

                }

                obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );

            }
        });
    });


    /*pagination*/
    var showMoreCounter = false;
    jQuery( '.wpc_client_client_pages_list .pages_pagination_block' ).click( function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_list' ).data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var page_number = obj.data( 'page_number' );
        var last_category_id = obj.data( 'last_category_id' );

        var search = obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pages_search.wpc_searched' ).val();
        if( typeof search === "undefined" ) {
            search = '';
        }

        var sorting = '';
        if( typeof sort[form_id] !== "undefined" ) {
            sorting = sort[form_id];
        }

        obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_list_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ) + '&search=' + search + '&sorting=' + sorting + '&current_page=' + page_number + '&last_category_id=' + last_category_id + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    if( !showMoreCounter) {
                        // clear prev
                        obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pagelist' ).empty();
                        obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_pagelist' ).append( data.html );
                        showMoreCounter = true;
                    }else {
                        obj.parents( '.wpc_client_client_pages_list' ).find( '.wpc_page:last' ).after( data.html );
                    }
                    obj.data('page_number', page_number + 1 );
                    obj.data( 'last_category_id', data.last_category_id );

                    if( !data.pagination ) {
                        obj.hide();
                    }
                }

                obj.parents( '.wpc_client_client_pages_list' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });

    });
});
