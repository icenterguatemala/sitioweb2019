var filter = new Object();
var submit_marker = false;
jQuery(document).ready(function() {

    init_tooltips();

    jQuery('body').on('click', '.wpc_file_row', function(e) {
        jQuery(this).toggleClass('wpc_active_row');
        e.stopPropagation();
    });

    jQuery('body').on('click', '.wpc_file_row.wpc_active_row > td:not(.wpc_primary_column):not(.wpc_th_bulk_action)', function(e) {
        e.stopPropagation();
    });

    jQuery('body').on('click', '.wpc_file_row a', function(e) {
        e.stopPropagation();
    });

    /*bulk actions js*/
    jQuery( '.wpc_client_files_table' ).on( 'click', '.bulk_ids', function() {
        if( jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids:checked' ).length == jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids' ).length ) {
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids_all' ).prop( 'checked', true );
        } else {
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids_all' ).prop( 'checked', false );
        }
    });


    jQuery( '.wpc_client_files_table' ).on( 'click', '.bulk_ids_all', function() {
        if( jQuery(this).is(':checked') ) {
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids' ).prop( 'checked', true );
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids_all' ).prop( 'checked', true );
        } else {
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids' ).prop( 'checked', false );
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids_all' ).prop( 'checked', false );
        }
    });


    jQuery( '.wpc_client_files_table' ).on( 'click', '.wpc_files_bulk_actions_apply_button', function() {
        if( jQuery(this).siblings( '.wpc_files_bulk_action' ).val() == 'none' ) {
            return false;
        }
        if( jQuery(this).siblings( '.wpc_files_bulk_action' ).val() != 'none' &&
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.bulk_ids:checked' ).length == 0 ) {
            return false;
        }

        jQuery(this).parents( '.wpc_client_files_table' ).find( '.wpc_files_bulk_action' ).val( jQuery(this).siblings( '.wpc_files_bulk_action' ).val() );

        submit_marker = true;
        jQuery(this).parents( '.wpc_client_files_table' ).find( '.wpc_client_files_form' ).submit();
        submit_marker = false;
    });


    /*filters js*/
    jQuery( '.wpc_client_files_table .wpc_show_filters' ).click( function(e) {
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

    jQuery( '.wpc_client_files_table .wpc_filter_by' ).change( function() {
        var obj = jQuery(this);

        if( obj.val() == 'none' ) {
            obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_filter' ).hide();
            return false;
        }

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if ( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search.wpc_searched' ).val();
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
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_msg_filter_selectors' ).html( data.filter_html ).show();

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

    if( jQuery( '.wpc_client_files_table .wpc_filter_by' ).length > 0 ) {
        jQuery( '.wpc_client_files_table .wpc_filter_by' ).trigger( 'change' );
    }



    //change filtering reload filestable
    jQuery( '.wpc_client_files_table .wpc_filters_wrapper' ).change( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search.wpc_searched' ).val();
        if ( typeof search === "undefined") {
            search = '';
        }

        var order_by = '';
        var order = '';
        obj.parents( '.wpc_client_files_table' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_table_pagination',
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

                    obj.parents( '.wpc_client_files_table' ).find( 'tbody' ).html( data.html );
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).hide();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_show_filters').prop('disabled', false);
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_filter_by').html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_show_filters').prop('disabled', true);
                    }

                    if ( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_pagination' ).html( data.pagination );
                        php_data.count_pages = data.count_pages;
                    }

                    init_tooltips();
                }
            }
        });
    });


    //add filter
    jQuery( '.wpc_client_files_table .wpc_add_filter' ).click( function() {

        var obj = jQuery(this);

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
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
                    shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                    filter_by       : filter_by,
                    from            : filter[files_form_id][filter_by]['from'],
                    to              : filter[files_form_id][filter_by]['to']
                },
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        obj.parents( '.wpc_client_files_table' ).find( '.wpc_filters_wrapper' ).append(
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

            add_filter(obj.parents('.wpc_client_files_table'), filter_by, filter_id);
        }
        obj.parents( '.wpc_files_filter_block' ).find('.wpc_filters_select_wrapper').removeClass( 'filtered' );

    });






    jQuery( '.wpc_client_files_table .wpc_client_files th.wpc_sortable' ).on( 'click', function() {
        var obj = jQuery(this);

        var page_number = obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_active_page' ).html();
        if( typeof page_number === 'undefined' ) {
            page_number = '1';
        }

        var search = obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search.wpc_searched' ).val();

        if( typeof search === "undefined") {
            search = '';
        }

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var order_by = obj.data( 'wpc_sort' );
        var order = obj.hasClass( 'wpc_sort_asc' ) ? 'desc' : 'asc';

        obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_table_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                filters         : filters,
                search          : search,
                current_page    : page_number,
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

                    obj.parents( '.wpc_client_files_table' ).find( 'tbody' ).html( data.html );
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).hide();

                    if ( obj.hasClass( 'wpc_sort_asc' ) ) {
                        obj.parents( '.wpc_client_files' ).find('th.wpc_sortable').removeClass('wpc_sort_asc').removeClass('wpc_sort_desc');
                        obj.addClass( 'wpc_sort_desc' );
                    } else {
                        obj.parents( '.wpc_client_files' ).find('th.wpc_sortable').removeClass('wpc_sort_asc').removeClass('wpc_sort_desc');
                        obj.addClass( 'wpc_sort_asc' );
                    }

                    init_tooltips();
                }
            }
        });

    });


    jQuery( '.wpc_client_files_table .wpc_files_search_button' ).click( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = jQuery.trim( obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search' ).val() );
        if( search == '' ) {
            return false;
        }


        var order_by = '';
        var order = '';
        obj.parents( '.wpc_client_files_table' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });


        obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_table_pagination',
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

                    obj.parents( '.wpc_client_files_table' ).find( 'tbody' ).html( data.html );
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).hide();

                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search' ).addClass( 'wpc_searched' );
                    obj.parents( '.wpc_client_files_table' ).find('.wpc_files_clear_search' ).show();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_show_filters').prop('disabled', false);
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_filter_by').html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_show_filters').prop('disabled', true);
                    }

                    if ( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_pagination' ).html( data.pagination );
                        php_data.count_pages = data.count_pages;
                    }

                    init_tooltips();
                }
            }
        });
    });

    jQuery( '.wpc_client_files_table .wpc_files_search' ).keyup( function(event) {
        if( event.keyCode == 13 ) {
            jQuery(this).parents( '.wpc_client_files_table' ).find( '.wpc_files_search_button' ).trigger( 'click' );
            event.stopPropagation();
        }
    });

    jQuery( '.wpc_client_files_table .wpc_client_files_form' ).submit( function(event) {
        if( !submit_marker ) {
            return false;
        }
    });


    jQuery( '.wpc_client_files_table .wpc_files_clear_search' ).click( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
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
        obj.parents( '.wpc_client_files_table' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });

        obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_table_pagination',
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

                    obj.parents( '.wpc_client_files_table' ).find( 'tbody' ).html( data.html );
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).hide();

                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search' ).removeClass( 'wpc_searched' ).val('');
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_clear_search' ).hide();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_show_filters').prop('disabled', false);
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_filter_by').html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_table' ).find('.wpc_show_filters').prop('disabled', true);
                    }

                    if( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_pagination' ).html( data.pagination );
                        php_data.count_pages = data.count_pages;
                    }

                    init_tooltips();
                }
            }
        });
    });


    jQuery( '.wpc_client_files_table' ).on( 'click', 'a.wpc_pagination_links', function() {
        var obj = jQuery(this);
        if( obj.hasClass( 'wpc_active_page' ) )
            return false;

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" );
        }

        var search = obj.parents( '.wpc_client_files_table' ).find( '.wpc_files_search.wpc_searched' ).val();
        if( typeof search === "undefined") {
            search = '';
        }

        var order_by = '';
        var order = '';
        obj.parents( '.wpc_client_files_table' ).find( '.wpc_client_files th.wpc_sortable' ).each( function(){
            if ( jQuery(this).hasClass( 'wpc_sort_desc' ) || jQuery(this).hasClass( 'wpc_sort_asc' ) ) {
                order_by = jQuery(this).data( 'wpc_sort' );
                order = jQuery(this).hasClass( 'wpc_sort_desc' ) ? 'desc' : 'asc';
            }
        });


        var page_number;
        if( obj.hasClass( 'wpc_last' ) ) {
            page_number = php_data.count_pages;
        } else if( obj.hasClass( 'wpc_first' ) ) {
            page_number = 1;
        } else if( obj.hasClass( 'wpc_next' ) ) {
            page_number = obj.siblings('.wpc_active_page').html()*1 + 1;
        } else if( obj.hasClass( 'wpc_previous' ) ) {
            page_number = obj.siblings('.wpc_active_page').html()*1 - 1;
        } else if( obj.hasClass( 'wpc_next_pages' ) )  {
            page_number = ( Math.floor( ( obj.siblings('.wpc_active_page').html()*1 - 1 ) / 3 ) + 1 )*3 + 1;
        } else if( obj.hasClass( 'wpc_previous_pages' ) ) {
            page_number = ( Math.floor( ( obj.siblings('.wpc_active_page').html()*1 - 1 ) / 3 ) )*3;
        } else {
            page_number = obj.html()*1;
        }


        obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).show();
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_table_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ),
                current_page    : page_number,
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

                    obj.parents( '.wpc_client_files_table' ).find( 'tbody' ).html( data.html );
                    obj.parents( '.wpc_client_files_table' ).find( '.wpc_ajax_overflow_table' ).hide();

                    if( obj.hasClass( 'wpc_last' ) ) {

                        obj.parents( '.wpc_client_files_table' ).find('.wpc_last').hide();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).hide();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).hide();

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).prev().addClass( 'wpc_active_page' ).show();

                        if( page_number*1 > 3 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).show();
                        }
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous' ).show();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_first' ).show();

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( jQuery(this).html()*1 > ( Math.floor( ( page_number*1 - 1 ) / 3 ) )*3 && jQuery(this).html()*1 < ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 ) {
                                jQuery(this).show();
                            } else if( jQuery.isNumeric( jQuery(this).html() ) ) {
                                jQuery(this).hide();
                            }
                        });

                    } else if( obj.hasClass( 'wpc_first' ) ) {

                        obj.parents( '.wpc_client_files_table' ).find('.wpc_first').hide();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).hide();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous' ).hide();

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).next().addClass( 'wpc_active_page' ).show();

                        if( page_number*1 < ( ( Math.ceil( php_data.count_pages/3 ) - 1)*3 + 1 ) ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).show();
                        }
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).show();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).show();

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( jQuery(this).html()*1 > ( Math.floor( ( page_number*1 - 1 ) / 3 ) )*3 && jQuery(this).html()*1 < ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 ) {
                                jQuery(this).show();
                            } else if( jQuery.isNumeric( jQuery(this).html() ) ) {
                                jQuery(this).hide();
                            }
                        });

                    } else if( obj.hasClass( 'wpc_next' ) ) {

                        var before_active = obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_active_page' );
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );
                        before_active.next().addClass( 'wpc_active_page' ).show();

                        if( page_number*1 > 1 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous' ).show();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_first' ).show();
                        }

                        if( page_number*1 > 3 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).show();
                        }

                        if( page_number*1 == php_data.count_pages*1 ) {
                            obj.parents( '.wpc_client_files_table' ).find( '.wpc_next' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).hide();
                        }

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( jQuery(this).html()*1 > ( Math.floor( ( page_number*1 - 1 ) / 3 ) )*3 && jQuery(this).html()*1 < ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 ) {
                                jQuery(this).show();
                            } else if( jQuery.isNumeric( jQuery(this).html() ) ) {
                                jQuery(this).hide();
                            }
                        });

                        if( ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 > php_data.count_pages ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).hide();
                        }

                    } else if( obj.hasClass( 'wpc_previous' ) ) {

                        var before_active = obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_active_page' );
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );
                        before_active.prev().addClass( 'wpc_active_page' ).show();

                        if( page_number*1 < php_data.count_pages*1 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).show();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).show();
                        }

                        if( page_number*1 < ( ( Math.ceil( php_data.count_pages/3 ) - 1)*3 + 1 ) ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).show();
                        }

                        if( page_number*1 == 1 ) {
                            obj.parents( '.wpc_client_files_table' ).find( '.wpc_previous' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_first' ).hide();
                        }

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( jQuery(this).html()*1 > ( Math.floor( ( page_number*1 - 1 ) / 3 ) )*3 && jQuery(this).html()*1 < ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 ) {
                                jQuery(this).show();
                            } else if( jQuery.isNumeric( jQuery(this).html() ) ) {
                                jQuery(this).hide();
                            }
                        });

                        if( Math.floor( ( page_number*1 - 1 ) / 3 ) == 0 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).hide();
                        }

                    } else if( obj.hasClass( 'wpc_next_pages' ) )  {

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous_pages' ).show();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous' ).show();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_first' ).show();

                        if( ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 > php_data.count_pages ) {
                            obj.parents( '.wpc_client_files_table' ).find( '.wpc_next_pages' ).hide();
                        }

                        if( page_number*1 == php_data.count_pages ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).hide();
                        }

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( jQuery(this).html()*1 > ( Math.floor( ( page_number*1 - 1 ) / 3 ) )*3 && jQuery(this).html()*1 < ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 ) {
                                jQuery(this).show();
                            } else if( jQuery.isNumeric( jQuery(this).html() ) ) {
                                jQuery(this).hide();
                            }

                            if( jQuery(this).html()*1 == page_number*1 ) {
                                jQuery(this).addClass( 'wpc_active_page' );
                            }
                        });

                    } else if( obj.hasClass( 'wpc_previous_pages' ) ) {
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next_pages' ).show();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).show();
                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).show();

                        if( Math.floor( ( page_number*1 - 1 ) / 3 ) == 0 ) {
                            obj.parents( '.wpc_client_files_table' ).find('.wpc_previous_pages').hide();
                        }

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( jQuery(this).html()*1 > ( Math.floor( ( page_number*1 - 1 ) / 3 ) )*3 && jQuery(this).html()*1 < ( Math.floor( ( page_number*1 - 1 ) / 3 ) + 1 )*3 + 1 ) {
                                jQuery(this).show();
                            } else if( jQuery.isNumeric( jQuery(this).html() ) ) {
                                jQuery(this).hide();
                            }

                            if( jQuery(this).html()*1 == page_number*1 ) {
                                jQuery(this).addClass( 'wpc_active_page' );
                            }
                        });

                    } else {

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).removeClass( 'wpc_active_page' );

                        obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links' ).each( function() {
                            if( obj.html() == jQuery(this).html() ) {
                                jQuery(this).addClass( 'wpc_active_page' );
                            }
                        });

                        if( page_number*1 < php_data.count_pages*1 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).show();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).show();
                        } else {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_next' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_last' ).hide();
                        }

                        if( page_number*1 > 1 ) {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous' ).show();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_first' ).show();
                        } else {
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_previous' ).hide();
                            obj.parents( '.wpc_client_files_table' ).find( 'a.wpc_pagination_links.wpc_first' ).hide();
                        }
                    }

                    init_tooltips();
                }
            }
        });

    });


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
    jQuery( '.wpc_client_files_table' ).on( 'click', '.wpc_remove_filter', function() {
        var obj = jQuery(this);

        var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
        var filter_before = filter[files_form_id];

        var filter_by = obj.parents( '.wpc_filter_wrapper' ).data('filter_by');

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
    jQuery( '.wpc_client_files_table' ).on( 'click', '.wpc_tag', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_table' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
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

            add_filter( obj.parents( '.wpc_client_files_table' ), filter_by, filter_id );

        }
    });


    //add filter from category
    jQuery( '.wpc_client_files_table' ).on( 'click', '.wpc_file_category_value', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_table' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
            var filter_before = filter[files_form_id];

            var filter_by = 'category';
            var filter_id = obj.data('category_id').toString();

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

            add_filter( obj.parents( '.wpc_client_files_table' ), filter_by, filter_id );

        }
    });


    //add filter from author
    jQuery( '.wpc_client_files_table' ).on( 'click', '.wpc_file_author_value', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_table' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_table' ).data( 'form_id' );
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

            add_filter( obj.parents( '.wpc_client_files_table' ), filter_by, filter_id );

        }
    });

});