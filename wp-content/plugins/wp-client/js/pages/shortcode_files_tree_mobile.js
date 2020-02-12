var filter = new Object();

jQuery(document).ready(function() {

    jQuery( '.wpc_client_files_tree' ).each( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        var search = '';

        var order_by = '';
        var order = '';
        obj.find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_tree_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filters         : filters,
                search          : search,
                order_by        : order_by,
                order           : order,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.find( '.wpc_files_tree_content' ).html( '<table class="wpc_files_tree">' + data.html + '</table>' );

                    init_tooltips();
                    init_treetable( false, obj.find( '.wpc_files_tree' ) );

                    obj.find( '.wpc_ajax_overflow_tree' ).hide();
                    obj.find( '.wpc_files_tree' ).show();
                }
            }
        });
    });



    jQuery( '.wpc_client_files_tree' ).on( 'click', '.indenter', function(e) {
        var obj = jQuery(this).parents( '.wpc_treetable_file_category' );

        if( obj.hasClass( 'expanded' ) ) {
            var category_id = obj.data("tt-id").replace( 'category','' );

            if( obj.parents( '.wpc_client_files_tree').find('.wpc_hidden_files' + category_id).length > 0 ) {
                var files_form_id = obj.parents('.wpc_client_files_tree').data('form_id');
                var php_data = window['wpc_file_shortcode' + files_form_id];
                var client_id = 0;
                var _wpnonce = '';

                if ( php_data.client_id ) {
                    client_id = php_data.client_id;
                    _wpnonce = php_data._wpnonce;
                }


                var filters = '';
                if (typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0) {
                    filters = jQuery.base64Encode(JSON.stringify(filter[files_form_id])).replace( /\+/g, "-" );
                }

                var search = obj.parents('.wpc_client_files_tree').find('.wpc_files_search.wpc_searched').val();

                if (typeof search === "undefined") {
                    search = '';
                }

                var order_by = '';
                var order = '';
                obj.parents( '.wpc_client_files_tree' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
                    if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                        order_by = jQuery(this).data( 'wpc_sort' );
                        order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
                    }
                });

                obj.parents('.wpc_client_files_tree').find('.wpc_ajax_overflow_tree').show();
                jQuery.ajax({
                    type: 'POST',
                    url: php_data.ajax_url,
                    data: {
                        action          : 'wpc_files_shortcode_tree_get_files',
                        category_id     : category_id,
                        shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                        filters         : filters,
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
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .wpc_hidden_files' + category_id).after(data.html);
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .wpc_hidden_files' + category_id).remove();
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .indenter').remove();

                            var temp = [];
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .branch.expanded').each( function(){
                                temp.push( jQuery(this).data('tt-id') );
                            });

                            init_treetable(false, obj.parents('.wpc_client_files_tree').find('.wpc_files_tree'), true);
                            init_tooltips();

                            jQuery.each( temp, function(i) {
                                obj.parents('table.treetable').treetable("expandNode", temp[i]);
                            });

                            var $table = obj.parents('.wpc_files_tree');
                            var $bodyCells = $table.find('tbody tr');

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
                            });

                            var position = obj.position().top;
                            obj.parents('.wpc_files_tree_content').animate({
                                scrollTop: position
                            }, 500);

                            obj.parents('.wpc_client_files_tree').find('.wpc_ajax_overflow_tree').hide();
                        }
                    }
                });
            } else {
                var $table = obj.parents('.wpc_files_tree');
                var $bodyCells = $table.find('tbody tr');

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
                });
            }
        } else {
            var $table = obj.parents('.wpc_files_tree');
            var $bodyCells = $table.find('tbody tr');

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
            });
        }

        e.stopPropagation();
    });


    jQuery( '.wpc_client_files_tree' ).on( 'click', '.wpc_treetable_file_category', function() {
        var obj = jQuery(this);

        if( obj.hasClass( 'collapsed' ) ) {
            var category_id = obj.data("tt-id").replace( 'category','' );

            if( obj.parents( '.wpc_client_files_tree').find('.wpc_hidden_files' + category_id).length > 0 ) {
                var files_form_id = obj.parents('.wpc_client_files_tree').data('form_id');
                var php_data = window['wpc_file_shortcode' + files_form_id];
                var client_id = 0;
                var _wpnonce = '';
                var post_parent = obj.data('tt-parent-id');

                if ( php_data.client_id ) {
                    client_id = php_data.client_id;
                    _wpnonce = php_data._wpnonce;
                }


                var filters = '';
                if (typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0) {
                    filters = jQuery.base64Encode(JSON.stringify(filter[files_form_id])).replace( /\+/g, "-" );
                }

                var search = obj.parents('.wpc_client_files_tree').find('.wpc_files_search.wpc_searched').val();

                if (typeof search === "undefined") {
                    search = '';
                }

                var order_by = '';
                var order = '';
                obj.parents( '.wpc_client_files_tree' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
                    if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                        order_by = jQuery(this).data( 'wpc_sort' );
                        order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
                    }
                });

                obj.parents('.wpc_client_files_tree').find('.wpc_ajax_overflow_tree').show();
                jQuery.ajax({
                    type: 'POST',
                    url: php_data.ajax_url,
                    data: {
                        action          : 'wpc_files_shortcode_tree_get_files',
                        category_id     : category_id,
                        shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                        filters         : filters,
                        search          : search,
                        order_by        : order_by,
                        order           : order,
                        client_id       : client_id,
                        security        : _wpnonce,
                        post_parent     : post_parent
                    },
                    dataType: "json",
                    success: function (data) {
                        if (!data.status) {
                            alert(data.message);
                        } else {
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .wpc_hidden_files' + category_id).after(data.html);
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .wpc_hidden_files' + category_id).remove();
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .indenter').remove();

                            var temp = [];
                            obj.parents('.wpc_client_files_tree').find('.wpc_files_tree_content .branch.expanded').each( function(){
                                temp.push( jQuery(this).data('tt-id') );
                            });

                            init_treetable(false, obj.parents('.wpc_client_files_tree').find('.wpc_files_tree'), true);
                            init_tooltips();

                            jQuery.each( temp, function(i) {
                                obj.parents('table.treetable').treetable("expandNode", temp[i]);
                            });
                            obj.parents('table.treetable').treetable("expandNode", obj.data("tt-id"));

                            var $table = obj.parents('.wpc_files_tree');
                            var $bodyCells = $table.find('tbody tr');

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
                            });

                            var position = obj.position().top;
                            obj.parents('.wpc_files_tree_content').animate({
                                scrollTop: position
                            }, 500);

                            obj.parents('.wpc_client_files_tree').find('.wpc_ajax_overflow_tree').hide();
                        }
                    }
                });
            } else {
                obj.parents('table.treetable').treetable("expandNode", obj.data("tt-id"));

                var $table = obj.parents('.wpc_files_tree');
                var $bodyCells = $table.find('tbody tr');

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
                });
            }
        } else {
            obj.parents( 'table.treetable' ).treetable( "collapseNode", obj.data( "tt-id" ) );

            var $table = obj.parents('.wpc_files_tree');
            var $bodyCells = $table.find('tbody tr');

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
            });
        }
    });


    jQuery( '.wpc_files_tree_header th.wpc_sortable' ).on( 'click', function() {
        var obj = jQuery(this);
        var clsName = obj.find( '.wpc_cust_file_sort' ).attr('class').match(/\w*wpc_cust_sort_\w*/)[0].replace('wpc_cust_sort_','');

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        var client_id  = 0;
        var _wpnonce = '';

        if ( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }


        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_search.wpc_searched' ).val();

        if( typeof search === "undefined") {
            search = '';
        }

        var order_by = obj.data( 'wpc_sort' );
        var order = obj.hasClass( 'wpc_sort_asc' ) ? 'desc' : 'asc';

        if ( obj.hasClass( 'wpc_sort_asc' ) ) {
            obj.parents( '.wpc_client_files_tree' ).find('th.wpc_sortable').removeClass('wpc_sort_asc').removeClass('wpc_sort_desc');
            obj.addClass( 'wpc_sort_desc' );
        } else {
            obj.parents( '.wpc_client_files_tree' ).find('th.wpc_sortable').removeClass('wpc_sort_asc').removeClass('wpc_sort_desc');
            obj.addClass( 'wpc_sort_asc' );
        }

        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_tree_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filters         : filters,
                search          : search,
                order_by        : order_by,
                order           : order,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree_content' ).html( '<table class="wpc_files_tree">' + data.html + '</table>' );

                    init_tooltips();
                    init_treetable( false, obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ) );

                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ).show();
                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).hide();
                }
            }
        });

    });


    jQuery( '.wpc_client_files_tree' ).on( 'click', '.wpc_show_file_details', function() {
        var files_form_id = jQuery(this).parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( jQuery(this).parents('.wpc_filename_block').find( '.wpc_file_details' ).hasClass( 'is_details' ) ) {
            jQuery(this).parents('.wpc_filename_block').find( '.wpc_file_details' ).slideUp();
            jQuery(this).html( php_data.data.texts.show_details );
        } else {
            jQuery(this).parents('.wpc_filename_block').find( '.wpc_file_details' ).slideDown();
            jQuery(this).html( php_data.data.texts.hide_details );
        }
        jQuery(this).parents('.wpc_filename_block').find( '.wpc_file_details' ).toggleClass( 'is_details' );
    });


    /*filters js*/
    jQuery( '.wpc_client_files_tree .wpc_show_filters' ).click( function(e) {
        jQuery( '.wpc_show_filters' ).not( this ).parents( '.wpc_filters_select_wrapper' ).removeClass( 'filtered' );
        jQuery(this).parents( '.wpc_filters_select_wrapper' ).toggleClass( 'filtered' );

        e.stopPropagation();

        jQuery(document).on( 'click', 'body', function( event ) {
            if( jQuery('.ui-datepicker').find( '.' + jQuery( event.target ).attr('class').replace( ' ', '.' ) ).length == 0 ) {
                jQuery( '.wpc_show_filters' ).parents( '.wpc_filters_select_wrapper' ).removeClass( 'filtered' );
                jQuery(document).off( event );
            }
        });
    });

    jQuery( '.wpc_filters_contect' ).click( function(e) {
        e.stopPropagation();
    });

    jQuery( '.wpc_client_files_tree .wpc_filter_by' ).change( function() {
        var obj = jQuery(this);

        if( obj.val() == 'none' ) {
            obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_filter' ).hide();
            return false;
        }

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if ( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_search.wpc_searched' ).val();
        if( typeof search === "undefined") {
            search = '';
        }

        obj.parents('.wpc_filters_contect').find( '.wpc_ajax_content' ).addClass( 'wpc_is_loading' );
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_get_filter',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filter_by       : obj.val(),
                filters         : filters,
                search          : search,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_msg_filter_selectors' ).html( data.filter_html ).show();

                    if( obj.val() == 'creation_date' ) {
                        if( typeof( custom_datepicker_init ) !== 'undefined' ) {
                            custom_datepicker_init();
                        }

                        obj.parents('.wpc_filters_contect').find( '.from_date_field' ).datepicker( "option", {
                            minDate: new Date( data.mindate*1000 ),
                            maxDate: new Date( data.maxdate*1000 ),
                            onClose: function( selectedDate ) {
                                obj.parents('.wpc_filters_contect').find('.to_date_field').datepicker( "option", "minDate", selectedDate );
                            }
                        });

                        obj.parents('.wpc_filters_contect').find('.to_date_field').datepicker( "option", {
                            minDate: new Date( data.mindate*1000 ),
                            maxDate: new Date( data.maxdate*1000 ),
                            onClose: function( selectedDate ) {
                                obj.parents('.wpc_filters_contect').find('.from_date_field').datepicker( "option", "maxDate", selectedDate );
                            }
                        });
                    }

                    obj.parents('.wpc_filters_contect').find( '.wpc_ajax_content' ).removeClass( 'wpc_is_loading' );
                }
            }
        });
    });

    if( jQuery( '.wpc_client_files_tree .wpc_filter_by' ).length > 0 ) {
        jQuery( '.wpc_client_files_tree .wpc_filter_by' ).trigger( 'change' );
    }



    //change filtering reload filestable
    jQuery( '.wpc_client_files_tree .wpc_filters_wrapper' ).change( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if ( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_search.wpc_searched' ).val();

        if( typeof search === "undefined") {
            search = '';
        }

        var order_by = '';
        var order = '';
        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_tree_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filters         : filters,
                search          : search,
                order_by        : order_by,
                order           : order,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree_content' ).html( '<table class="wpc_files_tree">' + data.html + '</table>' );

                    init_tooltips();
                    init_treetable( false, obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ) );

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_show_filters').prop('disabled', false);
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_filter_by').html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_show_filters').prop('disabled', true);
                    }

                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).hide();
                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ).show();
                }
            }
        });
    });


    //add filter
    jQuery( '.wpc_client_files_tree .wpc_add_filter' ).click( function() {

        var obj = jQuery(this);

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var filter_before = filter[files_form_id];

        var filter_by = obj.parents( '.wpc_files_filter_block' ).find('.wpc_filter_by').val();
        var filter_id = obj.parents( '.wpc_files_filter_block' ).find('.wpc_filter').val()

        if( filter_by == 'creation_date' ) {
            if (obj.parents( '.wpc_files_filter_block' ).find('.from_date_field').next().val() == '' && obj.parents( '.wpc_files_filter_block' ).find('.to_date_field').next().val() == '') {
                return false;
            }

            if (obj.parents( '.wpc_files_filter_block' ).find('.to_date_field').next().val() == '') {
                var current_time = new Date().getTime();
                obj.parents( '.wpc_files_filter_block' ).find('.to_date_field').next().val(Math.floor(current_time / 1000));
            }

            if( typeof filter_before === 'undefined' ) {
                filter[files_form_id] = {};
                filter[files_form_id][filter_by] = [];
            }

            if( !filter[files_form_id].hasOwnProperty( filter_by ) ) {
                filter[files_form_id][filter_by] = [];
            }

            filter[files_form_id][filter_by] = {
                'from': obj.parents( '.wpc_files_filter_block' ).find('.from_date_field').next().val(),
                'to': obj.parents( '.wpc_files_filter_block' ).find('.to_date_field').next().val()
            };

            var php_data = window['wpc_file_shortcode' + files_form_id];

            jQuery.ajax({
                type: 'POST',
                url: php_data.ajax_url,
                data: {
                    action          : 'wpc_get_filter_data',
                    filter_by       : filter_by,
                    from            : filter[files_form_id][filter_by]['from'],
                    to              : filter[files_form_id][filter_by]['to']
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_filters_wrapper' ).append(
                            '<div class="wpc_filter_wrapper" data-filter_by="' + filter_by + '">' +
                            data.title + ': ' + data.from + ' - ' + data.to +
                            '<div class="wpc_remove_filter">&times;</div>' +
                            '</div>'
                        ).trigger( 'change' );
                    }
                }
            });
        } else {
            var in_array = false;

            if (typeof filter_before !== 'undefined' && filter_before.hasOwnProperty(filter_by)) {
                jQuery.map(filter_before[filter_by], function (elementOfArray, indexInArray) {
                    if (elementOfArray == filter_id) {
                        in_array = true;
                    }
                });
            }

            if (in_array) {
                return false;
            }

            if (typeof filter_before === 'undefined') {
                filter[files_form_id] = {};
                filter[files_form_id][filter_by] = [];
            }

            if (!filter[files_form_id].hasOwnProperty(filter_by)) {
                filter[files_form_id][filter_by] = [];
            }

            filter[files_form_id][filter_by].push(filter_id);

            add_filter(obj.parents('.wpc_client_files_tree'), filter_by, filter_id);
        }
        obj.parents( '.wpc_files_filter_block' ).find('.wpc_filters_select_wrapper').removeClass( 'filtered' );

    });


    jQuery( '.wpc_client_files_tree .wpc_files_search_button' ).click( function() {
        var obj = jQuery(this);
        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = jQuery.trim( obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_search' ).val() );
        if( search == '' ) {
            return false;
        }

        var order_by = '';
        var order = '';
        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_tree_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filters         : filters,
                search          : search,
                order_by        : order_by,
                order           : order,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree_content' ).html( '<table class="wpc_files_tree">' + data.html + '</table>' );

                    init_tooltips();
                    init_treetable( false, obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ) );

                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_search' ).addClass( 'wpc_searched' );
                    obj.parents( '.wpc_client_files_tree' ).find('.wpc_files_clear_search' ).show();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_show_filters').prop('disabled', false);
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_filter_by').html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_show_filters').prop('disabled', true);
                    }

                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).hide();
                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ).show();
                }
            }
        });
    });


    jQuery( '.wpc_client_files_tree .wpc_files_search' ).keyup( function(event) {
        if( event.keyCode == 13 ) {
            jQuery(this).parents( '.wpc_client_files_tree' ).find( '.wpc_files_search_button' ).trigger( 'click' );
            event.stopPropagation();
        }
    });


    jQuery( '.wpc_client_files_tree .wpc_client_files_form' ).submit( function(event) {
        return false;
    });


    jQuery( '.wpc_client_files_tree .wpc_files_clear_search' ).click( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }


        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = '';

        var order_by = '';
        var order = '';
        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_tree_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filters         : filters,
                search          : search,
                order_by        : order_by,
                order           : order,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {

                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree_content' ).html( '<table class="wpc_files_tree">' + data.html + '</table>' );

                    init_tooltips();
                    init_treetable( false, obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ) );

                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_search' ).removeClass( 'wpc_searched' ).val('');
                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_files_clear_search' ).hide();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_show_filters').prop('disabled', false);
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_filter_by').html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_tree' ).find('.wpc_show_filters').prop('disabled', true);
                    }

                    obj.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).hide();
                    obj.parents('.wpc_client_files_tree').find( '.wpc_files_tree' ).show();
                }
            }
        });
    });

    function init_treetable( with_loader, shortcode, force ) {
        if( with_loader ) {
            shortcode.treetable({
                expandable: true,
                onInitialized: function() {
                    shortcode.parents( '.wpc_client_files_tree' ).find( '.wpc_ajax_overflow_tree' ).hide();
                    shortcode.parents( '.wpc_client_files_tree' ).find( '.wpc_files_tree' ).show();
                }
            }, force);
        } else {
            shortcode.treetable({
                expandable: true,
                onInitialized: function() {
                    shortcode.parents( '.wpc_client_files_tree' ).find( '.wpc_files_tree' ).show();
                }
            }, force);
        }

        var $bodyCells = shortcode.find('tbody tr');

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
        });
    }

    
    function init_tooltips() {
        jQuery( '.wpc_img_file_icon' ).tooltip({
            position:  {
                my: "center",
                at: "center"
            },
            items: "[wpc_images]",
            content: function() {
                var element = jQuery( this );
                if( element.is( "[wpc_images]" ) ) {
                    var text = element.attr( 'wpc_images' );
                    return "<img alt='" + text + "' src='" + text + "' style='width:100%;'>";
                }
            },
            show: null, // show immediately
            open: function( event, ui ) {
                if( typeof( event.originalEvent ) === 'undefined') {
                    return false;
                }

                var $id = jQuery( ui.tooltip ).attr( 'id' );

                // close any lingering tooltips
                jQuery( 'div.ui-tooltip' ).not( '#' + $id ).remove();
                // ajax function to pull in data and add it to the tooltip goes here
            },
            close: function( event, ui ) {
                ui.tooltip.hover( function() {
                    jQuery( this ).stop( true ).fadeTo( 400, 1 );
                },
                function() {
                    jQuery( this ).fadeOut( '400', function() {
                        jQuery(this).remove();
                    });
                });
            }
        });
    }


    function add_filter( table, filter_by, filter_id ) {

        var files_form_id = table.data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: 'action=wpc_get_filter_data&filter_by=' + filter_by + '&filter_id=' + filter_id,
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    table.find( '.wpc_filters_wrapper' ).append(
                        '<div class="wpc_filter_wrapper" data-filter_by="' + filter_by + '" data-filter_id="' + filter_id + '">' +
                            data.title + ': ' + data.name +
                            '<div class="wpc_remove_filter">&times;</div>' +
                        '</div>'
                    ).trigger( 'change' );
                }
            }
        });

    }


    //remove filters
    jQuery( '.wpc_client_files_tree' ).on( 'click', '.wpc_remove_filter', function() {
        var obj = jQuery(this);

        var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
        var filter_before = filter[files_form_id];

        var filter_by = obj.parents( '.wpc_filter_wrapper' ).data('filter_by');
        var filter_id = obj.parents( '.wpc_filter_wrapper' ).data('filter_id');

        if( filter_by == 'creation_date' ) {
            delete filter[files_form_id].creation_date;

            obj.parents( '.wpc_filter_wrapper' ).find('.wpc_filter_by option[value="creation_date"]').attr('disabled', false);
            obj.parents( '.wpc_filter_wrapper' ).find('.wpc_filter_by').trigger( 'change' );
        } else {
            var filter_id = obj.parents( '.wpc_filter_wrapper' ).data('filter_id');

            var index = filter_before[filter_by].indexOf( filter_id.toString() );
            if( index > -1 ) {
                filter[files_form_id][filter_by].splice( index, 1 );
            }
        }

        obj.parents( '.wpc_filters_wrapper' ).trigger( 'change' );
        obj.parents( '.wpc_filter_wrapper' ).remove();
    });


    //add filter from tag
    jQuery( '.wpc_client_files_tree' ).on( 'click', '.wpc_tag', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_tree' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
            var filter_before = filter[files_form_id];

            var filter_by = 'tags';
            var filter_id = obj.data('term_id').toString();

            var in_array = false;

            if( typeof filter_before !== 'undefined' && filter_before.hasOwnProperty( filter_by ) ) {
                jQuery.map( filter_before[filter_by], function( elementOfArray, indexInArray ) {
                    if( elementOfArray == filter_id ) {
                        in_array = true;
                    }
                });
            }

            if( in_array ) {
                return false;
            }

            if( typeof filter_before === 'undefined' ) {
                filter[files_form_id] = {};
                filter[files_form_id][filter_by] = [];
            }

            if( !filter[files_form_id].hasOwnProperty( filter_by ) ) {
                filter[files_form_id][filter_by] = [];
            }

            filter[files_form_id][filter_by].push( filter_id );

            add_filter( obj.parents( '.wpc_client_files_tree' ), filter_by, filter_id );

        }
    });


    //add filter from author
    jQuery( '.wpc_client_files_tree' ).on( 'click', '.wpc_file_author_value', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_tree' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_tree' ).data( 'form_id' );
            var filter_before = filter[files_form_id];

            var filter_by = 'author';
            var filter_id = obj.data('author_id').toString();

            if( filter_id == '' ) {
                return false;
            }

            var in_array = false;

            if( typeof filter_before !== 'undefined' && filter_before.hasOwnProperty( filter_by ) ) {
                jQuery.map( filter_before[filter_by], function( elementOfArray, indexInArray ) {
                    if( elementOfArray == filter_id ) {
                        in_array = true;
                    }
                });
            }

            if( in_array ) {
                return false;
            }

            if( typeof filter_before === 'undefined' ) {
                filter[files_form_id] = {};
                filter[files_form_id][filter_by] = [];
            }

            if( !filter[files_form_id].hasOwnProperty( filter_by ) ) {
                filter[files_form_id][filter_by] = [];
            }

            filter[files_form_id][filter_by].push( filter_id );

            add_filter( obj.parents( '.wpc_client_files_tree' ), filter_by, filter_id );

        }
    });

});
