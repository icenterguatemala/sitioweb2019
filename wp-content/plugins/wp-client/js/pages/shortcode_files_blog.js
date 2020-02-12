var filter = new Object();
var sort = new Object();

jQuery(document).ready(function() {

    //init_videoplayer();

    /*filters js*/
    jQuery( '.wpc_client_files_blog .wpc_show_filters' ).click( function(e) {
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

    jQuery( '.wpc_client_files_blog .wpc_filters_contect' ).click( function(e) {
        e.stopPropagation();
    });

    jQuery( '.wpc_client_files_blog .wpc_filter_by' ).change( function() {
        var obj = jQuery(this);

        if( obj.val() == 'none' ) {
            obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_filter' ).hide();
            return false;
        }

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );
        }

        var search = obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search.wpc_searched' ).val();
        if( typeof search === "undefined") {
            search = '';
        }

        obj.parents('.wpc_filters_contect').find( '.wpc_ajax_content' ).addClass( 'wpc_is_loading' );
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_get_filter',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
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
                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_msg_filter_selectors' ).html( data.filter_html ).show();

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

    if( jQuery( '.wpc_client_files_blog .wpc_filter_by' ).length > 0 ) {
        jQuery( '.wpc_client_files_blog .wpc_filter_by' ).trigger( 'change' );
    }


    //change filtering reload filestable
    jQuery( '.wpc_client_files_blog .wpc_filters_wrapper' ).change( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var page_number = 0;

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );
        }

        var search = obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search.wpc_searched' ).val();
        if( typeof search === "undefined") {
            search = '';
        }

        var sorting = '';
        if( typeof sort[files_form_id] !== "undefined" ) {
            sorting = sort[files_form_id];
        }

        obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_blog_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                current_page    : page_number,
                sorting         : sorting,
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

                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filesblog' ).html( data.html );

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_show_filters' ).prop( 'disabled', false );
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filter_by' ).html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_blog' ).find('.wpc_show_filters').prop( 'disabled', true );
                    }


                    if( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).data( 'page_number', 1 ).data( 'last_category_id', data.last_category_id );

                        if( !data.pagination ) {
                            obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).hide();
                        } else {
                            obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).show();
                        }
                    }

                    init_videoplayer( obj.parents( '.wpc_client_files_blog' ), false );
                }

                obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });
    });


    //add filter
    jQuery( '.wpc_client_files_blog .wpc_add_filter' ).click( function() {

        var obj = jQuery(this);

        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
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
                data: 'action=wpc_get_filter_data&filter_by=' + filter_by + '&from=' + filter[files_form_id][filter_by]['from'] + '&to=' + filter[files_form_id][filter_by]['to'],
                dataType: "json",
                success: function( data ){
                    if( !data.status ) {
                        alert( data.message );
                    } else {
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filters_wrapper' ).append(
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

            add_filter(obj.parents('.wpc_client_files_blog'), filter_by, filter_id);
        }
        obj.parents( '.wpc_files_filter_block' ).find('.wpc_filters_select_wrapper').removeClass( 'filtered' );

    });




    /* Sorting */
    if( jQuery( '.wpc_client_files_blog .wpc_sorting' ).length > 0 ) {
        jQuery( '.wpc_client_files_blog .wpc_sorting' ).each( function() {
            sort[jQuery(this).parents( '.wpc_client_files_blog' ).data( 'form_id' )] = jQuery(this).val();
        });
    }

    jQuery( '.wpc_client_files_blog .wpc_add_sort' ).click( function(e) {
        /*var change_value = jQuery(this).data('hover_value');
        jQuery(this).data( 'hover_value', jQuery(this).val() ).val( change_value );  */

        jQuery( '.wpc_add_sort' ).not( this ).parents( '.wpc_sort_block' ).removeClass( 'sorted' );
        jQuery(this).parents( '.wpc_sort_block' ).toggleClass( 'sorted' );

        e.stopPropagation();

        jQuery( 'body' ).bind( 'click', function( event ) {
            jQuery( '.wpc_add_sort' ).parents( '.wpc_sort_block' ).removeClass( 'sorted' );
            jQuery( 'body' ).unbind( event );
        });
    });


    jQuery( '.wpc_client_files_blog .wpc_sort_contect' ).click( function(e) {
        e.stopPropagation();
    });



    jQuery( '.wpc_client_files_blog .wpc_apply_sort' ).click( function() {
        var obj = jQuery(this);

        var sort_by = obj.parents( '.wpc_sort_contect' ).find( '.wpc_sorting' ).val();
        var form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );

        if( sort[form_id] == sort_by ) {
            return false;
        }

        sort[form_id] = sort_by;
        obj.parents( '.wpc_sort_block' ).removeClass( 'sorted' );
        obj.parents( '.wpc_sort_block' ).find( '.wpc_add_sort' ).val( obj.parents( '.wpc_sort_block' ).find( '.wpc_sorting option:selected' ).html() );



        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            var client_id = php_data.client_id;
            var _wpnonce = php_data._wpnonce;
        }

        var page_number = obj.parents( '.wpc_client_files_blog' ).find('.files_pagination_block').data( 'page_number' );

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );
        }

        var search = obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search.wpc_searched' ).val();
        if( typeof search === "undefined" ) {
            search = '';
        }

        var sorting = '';
        if( typeof sort[files_form_id] !== "undefined" ) {
            sorting = sort[files_form_id];
        }


        obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_blog_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                current_page    : page_number,
                sorting         : sorting,
                filters         : filters,
                search          : search,
                sort_button     : 1,
                client_id       : client_id,
                security        : _wpnonce
            },
            dataType: "json",
            success: function( data ){
                if( !data.status ) {
                    alert( data.message );
                } else {
                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filesblog' ).html( data.html );

                    init_videoplayer( obj.parents( '.wpc_client_files_blog' ), false );
                }
                obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });
    });


    jQuery( '.wpc_client_files_blog .wpc_files_search_button' ).click( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var page_number = 0;

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );
        }

        var search = jQuery.trim( obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search' ).val() );
        if( search == '' ) {
            return false;
        }

        var sorting = '';
        if( typeof sort[files_form_id] !== "undefined" ) {
            sorting = sort[files_form_id];
        }

        obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_blog_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                current_page    : page_number,
                sorting         : sorting,
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
                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filesblog' ).html( data.html );

                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search' ).addClass( 'wpc_searched' );
                    obj.parents( '.wpc_client_files_blog' ).find('.wpc_files_clear_search' ).show();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_show_filters' ).prop( 'disabled', false );
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filter_by' ).html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_blog' ).find('.wpc_show_filters').prop( 'disabled', true );
                    }


                    if( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).data( 'page_number', 1 ).data( 'last_category_id', data.last_category_id );

                        if( !data.pagination ) {
                            obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).hide();
                        } else {
                            obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).show();
                        }
                    }

                    init_videoplayer( obj.parents( '.wpc_client_files_blog' ), false );
                }

                obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });
    });

    jQuery( '.wpc_client_files_blog .wpc_files_search' ).keyup( function(event) {
        if( event.keyCode == 13 && jQuery(this).val() != '' ) {
            jQuery(this).parents( '.wpc_client_files_blog' ).find( '.wpc_files_search_button' ).trigger( 'click' );
            event.stopPropagation();
        }
    });

    jQuery( '.wpc_client_files_blog .wpc_files_clear_search' ).click( function() {
        var obj = jQuery(this);

        var client_id  = 0;
        var _wpnonce = '';

        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            client_id = php_data.client_id;
            _wpnonce = php_data._wpnonce;
        }

        var page_number = 0;

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );
        }

        var sorting = '';
        if( typeof sort[files_form_id] !== "undefined" ) {
            sorting = sort[files_form_id];
        }

        var search = '';

        obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_blog_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                current_page    : page_number,
                sorting         : sorting,
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

                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filesblog' ).html( data.html );

                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search' ).removeClass( 'wpc_searched' ).val('');
                    obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_clear_search' ).hide();

                    if( data.filter_html != '' ) {
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_show_filters' ).prop( 'disabled', false );
                        obj.parents( '.wpc_client_files_blog' ).find( '.wpc_filter_by' ).html( data.filter_html ).trigger( 'change' );
                    } else {
                        obj.parents( '.wpc_client_files_blog' ).find('.wpc_show_filters').prop( 'disabled', true );
                    }


                    if( php_data.data.show_pagination ) {
                        obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).data( 'page_number', 1 ).data( 'last_category_id', data.last_category_id );

                        if( !data.pagination ) {
                            obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).hide();
                        } else {
                            obj.parents( '.wpc_client_files_blog' ).find( '.files_pagination_block' ).show();
                        }
                    }

                    init_videoplayer( obj.parents( '.wpc_client_files_blog' ), false );
                }

                obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );

            }
        });
    });




    jQuery( '.wpc_client_files_blog .files_pagination_block' ).click( function() {
        var obj = jQuery(this);
        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
        var php_data = window['wpc_file_shortcode' + files_form_id];

        if( php_data.client_id ) {
            var client_id = php_data.client_id;
            var _wpnonce = php_data._wpnonce;
        }

        var page_number = obj.data( 'page_number' );

        var filters = '';
        if( typeof filter !== 'undefined' && typeof filter[files_form_id] !== 'undefined' && Object.keys(filter[files_form_id]).length > 0 ) {
            filters = jQuery.base64Encode( JSON.stringify( filter[files_form_id] ) ).replace( /\+/g, "-" ).replace( /\//g, "*" );
        }

        var sorting = '';
        if( typeof sort[files_form_id] !== "undefined" ) {
            sorting = sort[files_form_id];
        }

        var search = obj.parents( '.wpc_client_files_blog' ).find( '.wpc_files_search.wpc_searched' ).val();
        if( typeof search === "undefined" ) {
            search = '';
        }

        obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').show();
        jQuery('body').css('cursor', 'wait');
        jQuery.ajax({
            type: 'POST',
            url: php_data.ajax_url,
            data: {
                action          : 'wpc_files_shortcode_blog_pagination',
                shortcode_data  : jQuery.base64Encode( JSON.stringify( php_data.data ) ).replace( /\+/g, "-" ).replace( /\//g, "*" ),
                current_page    : page_number,
                sorting         : sorting,
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

                    var before_video = obj.parents( '.wpc_client_files_blog' ).find('.wp-video-shortcode');

                    obj.parents( '.wpc_client_files_blog' ).find( '.file_item:last' ).after( data.html );
                    obj.data( 'page_number', obj.data( 'page_number' )*1 + 1 );

                    if( !data.pagination ) {
                        obj.hide();
                    }

                    init_videoplayer( obj.parents( '.wpc_client_files_blog' ), before_video );
                }



                obj.parents( '.wpc_client_files_blog' ).find('.wpc_overlay').hide();
                jQuery('body').css( 'cursor', 'default' );
            }
        });

    });





    //remove filters
    jQuery( '.wpc_client_files_blog' ).on( 'click', '.wpc_remove_filter', function() {
        var obj = jQuery(this);

        var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
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
    jQuery( '.wpc_client_files_blog' ).on( 'click', '.wpc_tag', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_blog' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
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

            add_filter( obj.parents( '.wpc_client_files_blog' ), filter_by, filter_id );

        }
    });


    //add filter from category
    jQuery( '.wpc_client_files_blog' ).on( 'click', '.wpc_file_category_value', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_blog' ).find( '.wpc_show_filters' ).prop('disabled') ) {

            var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
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

            add_filter( obj.parents( '.wpc_client_files_blog' ), filter_by, filter_id );

        }
    });


    //add filter from author
    jQuery( '.wpc_client_files_blog' ).on( 'click', '.wpc_file_author_value', function() {
        var obj = jQuery(this);

        if( !obj.parents( '.wpc_client_files_blog' ).find( '.wpc_show_filters' ).prop( 'disabled' ) ) {

            var files_form_id = obj.parents( '.wpc_client_files_blog' ).data( 'form_id' );
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

            add_filter( obj.parents( '.wpc_client_files_blog' ), filter_by, filter_id );

        }
    });


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


    function init_videoplayer( blog, before_videos ) {
        var settings = {};

        if ( typeof _wpmejsSettings !== 'undefined' ) {
            settings = _wpmejsSettings;
        }

        settings.success = settings.success || function (mejs) {
            var autoplay, loop;

            if ( 'flash' === mejs.pluginType ) {
                autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
                loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

                autoplay && mejs.addEventListener( 'canplay', function () {
                    mejs.play();
                }, false );

                loop && mejs.addEventListener( 'ended', function () {
                    mejs.play();
                }, false );
            }
        };

        if( before_videos ) {
            blog.find('.wp-video-shortcode').not( before_videos ).mediaelementplayer( settings );
        } else {
            blog.find('.wp-video-shortcode').mediaelementplayer( settings );
        }
    }
});