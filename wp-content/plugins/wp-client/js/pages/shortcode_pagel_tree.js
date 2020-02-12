jQuery(document).ready(function() {
    var pp_tree = jQuery( '.wpc_client_client_pages_tree' );

    //first ajax loading
    pp_tree.each( function() {
        var obj = jQuery(this);

        var form_id = obj.data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var search = '';

        var order_by = '';
        var order = '';
        obj.find( '.wpc_client_pages th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_tree_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ) + '&search=' + search + '&order_by=' + order_by + '&order=' + order + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.find( '.wpc_client_pages_tree_content' ).html( '<table class="wpc_client_pages_tree">' + data.html + '</table><div class="wpc_ajax_overflow_tree"><div class="wpc_ajax_loading"></div></div>' );

                    wpc_pp_init_treetable( false, obj.find( '.wpc_client_pages_tree' ), false );

                    obj.find( '.wpc_ajax_overflow_tree' ).hide();
                }
            }
        });
    });


    // expanding/collapsing
    pp_tree.on( 'click', '.indenter', function(e) {
        var obj = jQuery(this).parents( '.wpc_treetable_portal_pages_category' );

        if( obj.hasClass( 'expanded' ) ) {
            var category_id = obj.data("tt-id").replace( 'category','' );

            if ( obj.parents( '.wpc_client_client_pages_tree').find('.wpc_hidden_pages' + category_id).length > 0 ) {
                var pp_form_id = obj.parents('.wpc_client_client_pages_tree').data('form_id');
                var php_data = window['wpc_pagel_pagination' + pp_form_id];

                var client_id = 0;
                var _wpnonce = '';

                if ( php_data.client_id ) {
                    client_id = php_data.client_id;
                    _wpnonce = php_data._wpnonce;
                }

                var search = obj.parents('.wpc_client_client_pages_tree').find('.wpc_files_search.wpc_searched').val();
                if ( typeof search === "undefined" ) {
                    search = '';
                }

                var order_by = '';
                var order = '';
                obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_client_pages th.wpc_sortable' ).each( function(){
                    if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                        order_by = jQuery(this).data( 'wpc_sort' );
                        order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
                    }
                });

                obj.parents('.wpc_client_client_pages_tree').find('.wpc_ajax_overflow_tree').show();
                jQuery.ajax({
                    type: 'POST',
                    url: php_data.ajax_url,
                    data: {
                        action          : 'wpc_pagel_shortcode_tree_get_pages',
                        category_id     : category_id,
                        shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                        search          : search,
                        order_by        : order_by,
                        order           : order,
                        client_id       : client_id,
                        security        : _wpnonce
                    },
                    dataType: "json",
                    success: function (data) {
                        if (!data.status) {
                            alert(data.message);
                        } else {
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .wpc_hidden_pages' + category_id).after(data.html);
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .wpc_hidden_pages' + category_id).remove();
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .indenter').remove();

                            var temp = [];
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .branch.expanded').each( function(){
                                temp.push( jQuery(this).data('tt-id') );
                            });

                            wpc_pp_init_treetable( false, obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree'), true );

                            jQuery.each( temp, function(i) {
                                obj.parents( 'table.treetable' ).treetable( "expandNode", temp[i] );
                            });

                            wpc_pp_bodyCellsLoop( obj.parents('.wpc_client_pages_tree').find('tbody tr'), obj.parents('.wpc_client_pages_tree') );

                            var position = obj.position().top;
                            obj.parents('.wpc_client_pages_tree_content').animate({
                                scrollTop: position
                            }, 500);

                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_ajax_overflow_tree').hide();
                        }
                    }
                });
            } else {
                //standard collapse if hidden data loaded
                wpc_pp_bodyCellsLoop( obj.parents('.wpc_client_pages_tree').find('tbody tr'), obj.parents('.wpc_client_pages_tree') );
            }
        } else {
            //standard expand if hidden data loaded
            wpc_pp_bodyCellsLoop( obj.parents('.wpc_client_pages_tree').find('tbody tr'), obj.parents('.wpc_client_pages_tree') );
        }

        e.stopPropagation();
    });


    pp_tree.on( 'click', '.wpc_treetable_portal_pages_category', function() {
        var obj = jQuery(this);

        if ( obj.hasClass( 'collapsed' ) ) {
            var category_id = obj.data("tt-id").replace( 'category','' );

            if( obj.parents( '.wpc_client_client_pages_tree').find('.wpc_hidden_pages' + category_id).length > 0 ) {

                var pp_form_id = obj.parents('.wpc_client_client_pages_tree').data('form_id');
                var php_data = window['wpc_pagel_pagination' + pp_form_id];

                var client_id = 0;
                var _wpnonce = '';

                if ( php_data.client_id ) {
                    client_id = php_data.client_id;
                    _wpnonce = php_data._wpnonce;
                }

                var search = obj.parents('.wpc_client_client_pages_tree').find('.wpc_files_search.wpc_searched').val();
                if ( typeof search === "undefined" ) {
                    search = '';
                }

                var order_by = '';
                var order = '';
                obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_client_pages th.wpc_sortable' ).each( function(){
                    if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                        order_by = jQuery(this).data( 'wpc_sort' );
                        order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
                    }
                });

                obj.parents('.wpc_client_client_pages_tree').find('.wpc_ajax_overflow_tree').show();
                jQuery.ajax({
                    type: 'POST',
                    url: php_data.ajax_url,
                    data: {
                        action          : 'wpc_pagel_shortcode_tree_get_pages',
                        category_id     : category_id,
                        shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                        search          : search,
                        order_by        : order_by,
                        order           : order,
                        client_id       : client_id,
                        security        : _wpnonce
                    },
                    dataType: "json",
                    success: function (data) {
                        if (!data.status) {
                            alert(data.message);
                        } else {
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .wpc_hidden_pages' + category_id).after(data.html);
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .wpc_hidden_pages' + category_id).remove();
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .indenter').remove();

                            var temp = [];
                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree_content .branch.expanded').each( function(){
                                temp.push( jQuery(this).data('tt-id') );
                            });

                            wpc_pp_init_treetable(false, obj.parents('.wpc_client_client_pages_tree').find('.wpc_client_pages_tree'), true);

                            jQuery.each( temp, function(i) {
                                obj.parents('table.treetable').treetable("expandNode", temp[i]);
                            });
                            obj.parents('table.treetable').treetable("expandNode", obj.data("tt-id"));

                            wpc_pp_bodyCellsLoop( obj.parents('.wpc_client_pages_tree').find('tbody tr'), obj.parents('.wpc_client_pages_tree') );

                            var position = obj.position().top;
                            obj.parents('.wpc_client_pages_tree_content').animate({
                                scrollTop: position
                            }, 500);

                            obj.parents('.wpc_client_client_pages_tree').find('.wpc_ajax_overflow_tree').hide();
                        }
                    }
                });
            } else {
                obj.parents('table.treetable').treetable("expandNode", obj.data("tt-id"));
                wpc_pp_bodyCellsLoop( obj.parents('.wpc_client_pages_tree').find('tbody tr'), obj.parents('.wpc_client_pages_tree') );
            }
        } else {
            obj.parents( 'table.treetable' ).treetable( "collapseNode", obj.data( "tt-id" ) );
            wpc_pp_bodyCellsLoop( obj.parents('.wpc_client_pages_tree').find('tbody tr'), obj.parents('.wpc_client_pages_tree') );
        }
    });


    //searching
    jQuery( '.wpc_client_client_pages_tree .wpc_pages_search_button' ).click( function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_tree' ).data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var search = jQuery.trim( obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_pages_search' ).val() );
        if( search == '' ) {
            return false;
        }

        var order_by = '';
        var order = '';
        obj.find( '.wpc_client_pages th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_tree_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ) + '&search=' + search + '&order_by=' + order_by + '&order=' + order + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    obj.parents('.wpc_client_client_pages_tree').find( '.wpc_client_pages_tree_content' ).html( '<table class="wpc_client_pages_tree">' + data.html + '</table><div class="wpc_ajax_overflow_tree"><div class="wpc_ajax_loading"></div></div>' );

                    wpc_pp_init_treetable( false, obj.parents('.wpc_client_client_pages_tree').find( '.wpc_client_pages_tree' ), false );

                    obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_pages_search' ).addClass( 'wpc_searched' );
                    obj.parents( '.wpc_client_client_pages_tree' ).find('.wpc_pages_clear_search' ).show();

                    obj.parents('.wpc_client_client_pages_tree').find( '.wpc_ajax_overflow_tree' ).hide();
                }
            }
        });
    });


    jQuery( '.wpc_client_client_pages_tree .wpc_pages_search' ).keyup( function(event) {
        if( event.keyCode == 13 ) {
            jQuery(this).parents( '.wpc_client_client_pages_tree' ).find( '.wpc_pages_search_button' ).trigger( 'click' );
            event.stopPropagation();
        }
    });


    jQuery( '.wpc_client_client_pages_tree .wpc_pages_clear_search' ).click( function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_tree' ).data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';

        var search = '';

        var order_by = '';
        var order = '';
        obj.find( '.wpc_client_pages th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_ajax_overflow_tree' ).show();

        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_tree_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ) + '&search=' + search + '&order_by=' + order_by + '&order=' + order + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    obj.parents('.wpc_client_client_pages_tree').find( '.wpc_client_pages_tree_content' ).html( '<table class="wpc_client_pages_tree">' + data.html + '</table><div class="wpc_ajax_overflow_tree"><div class="wpc_ajax_loading"></div></div>' );

                    wpc_pp_init_treetable( false, obj.parents('.wpc_client_client_pages_tree').find( '.wpc_client_pages_tree' ), false );

                    obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_pages_search' ).removeClass( 'wpc_searched' ).val('');
                    obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_pages_clear_search' ).hide();

                    obj.parents('.wpc_client_client_pages_tree').find( '.wpc_ajax_overflow_tree' ).hide();

                }
            }
        });
    });


    //sorting
    jQuery( '.wpc_client_pages_tree_header th.wpc_sortable' ).on( 'click', function() {
        var obj = jQuery(this);

        var form_id = obj.parents( '.wpc_client_client_pages_tree' ).data( 'form_id' );
        var php_data = window['wpc_pagel_pagination' + form_id];

        var client_id = ( php_data.client_id ) ? php_data.client_id : 0;
        var _wpnonce = ( php_data.client_id ) ? php_data._wpnonce : '';


        var search = obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_pages_search.wpc_searched' ).val();

        if( typeof search === "undefined") {
            search = '';
        }

        var order_by = obj.data( 'wpc_sort' );
        var order = obj.hasClass( 'wpc_sort_asc' ) ? 'desc' : 'asc';

        if ( obj.hasClass( 'wpc_sort_asc' ) ) {
            obj.parents( '.wpc_client_client_pages_tree' ).find('th.wpc_sortable').removeClass('wpc_sort_asc').removeClass('wpc_sort_desc');
            obj.addClass( 'wpc_sort_desc' );
        } else {
            obj.parents( '.wpc_client_client_pages_tree' ).find('th.wpc_sortable').removeClass('wpc_sort_asc').removeClass('wpc_sort_desc');
            obj.addClass( 'wpc_sort_asc' );
        }

        obj.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_ajax_overflow_tree' ).show();

        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_pagel_shortcode_tree_pagination&shortcode_data=' + jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ) + '&search=' + search + '&order_by=' + order_by + '&order=' + order + '&client_id=' + client_id + '&security=' + _wpnonce,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents('.wpc_client_client_pages_tree').find( '.wpc_client_pages_tree_content' ).html( '<table class="wpc_client_pages_tree">' + data.html + '</table><div class="wpc_ajax_overflow_tree"><div class="wpc_ajax_loading"></div></div>' );

                    wpc_pp_init_treetable( false, obj.parents('.wpc_client_client_pages_tree').find( '.wpc_client_pages_tree' ), false );

                    obj.parents('.wpc_client_client_pages_tree').find( '.wpc_ajax_overflow_tree' ).hide();
                }
            }
        });

    });


    function wpc_pp_init_treetable( with_loader, shortcode, force ) {
        if( with_loader ) {
            shortcode.treetable({
                expandable: true,
                onInitialized: function() {
                    shortcode.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_ajax_overflow_tree' ).hide();
                    shortcode.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_client_pages_tree' ).show();
                }
            }, force );
        } else {
            shortcode.treetable({
                expandable: true,
                onInitialized: function() {
                    shortcode.parents( '.wpc_client_client_pages_tree' ).find( '.wpc_client_pages_tree' ).show();
                }
            }, force );
        }

        wpc_pp_bodyCellsLoop( shortcode.find('tbody tr'), shortcode );
    }

    function wpc_pp_bodyCellsLoop( $bodyCells, shortcode ) {
        var item = 0;
        $bodyCells.each( function() {
            if( jQuery( this ).is( ':visible' ) ) {
                item++;
                if( item%2 == 0 ) {
                    jQuery( this ).css( 'background', '#eee' );
                } else {
                    jQuery( this ).css( 'background', '#fff' );
                }
            }

            if( shortcode.height() < 300 ) {
                if( jQuery( this ).find( '.wpc_scroll_column' ).length == 0 ) {
                    jQuery( this ).append( '<td class="wpc_scroll_column"></td>' );
                }
            } else {
                jQuery( this ).find( '.wpc_scroll_column' ).remove();
            }
        });
    }

});