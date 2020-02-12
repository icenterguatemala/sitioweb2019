/*
* Plugin for customize selectbox
*/

(function( $, undefined ) {
    var options;
    var unique;

    var default_options = {
        'placeholder'   : null,
        'search'        : false,
        'empty_text'    : 'No results match'
    };

    var methods = {
        init : function( settings ) {
            var selectbox = $( this );
            //add class to element
            if( selectbox.hasClass( 'is_wpc_class' ) ) {
                return false;
            }
            selectbox.addClass( 'is_wpc_class' );

            unique = Math.floor(Math.random() * 1000) + 1;
            selectbox.data('wpc_select_unique', unique);

            //merge defau;t & current options
            options = $.extend( {}, default_options, settings );
            this.data( 'options', options );

            //find label for selectbox
            var label = jQuery( 'label[for="' + selectbox.attr( 'id' ) + '"]' );

            //select options for multiple
            var multiple = $( this ).attr( 'multiple' );
            if( typeof( multiple ) !== 'undefined' && multiple != '' ) {
                multiple = 'wpc_multiple';
            } else {
                multiple = 'wpc_single';
            }


            //select options for placeholder
            var placeholder = $( this ).attr( 'placeholder' );
            if( typeof( placeholder ) !== 'undefined' && placeholder != '' ) {
                placeholder = '<a href="javascript:;" class="wpc_selector_placeholder">' + placeholder +'</a>';
            } else {
                placeholder = '';
            }


            //select options for search
            var search = '';
            if( options.search === true ) {
                search = '<div class="wpc_drop_search_wrapper">' +
                    '<div class="wpc_drop_search_image"></div>' +
                    '<input type="text" autocomplete="off" class="wpc_drop_search" />' +
                '</div>';
            }

            //build values list
            var values_list = '';
            var option_index = 0;
            var option_group_index = 0;
            $(this).children().each( function() {
                if ( this.tagName.toLowerCase() == 'option' ) {
                    option_index++;
                    if ( $( this ).data( 'hidden' ) != "1" ) {
                        values_list += '<li class="wpc_selector_option" data-option_index="' + option_index + '">' + $( this ).html() + '</li>';
                    }
                } else if ( this.tagName.toLowerCase() == 'optgroup' ) {
                    option_group_index++;

                    var color = '';
                    if( typeof( $( this ).data('color') ) !== 'undefined' ) {
                        color = 'style="color:' + $( this ).data('color') + ' !important;"';
                    }

                    values_list += '<li class="option_group" ' + color + '>' + $( this ).attr( 'label' ) + '</li>';

                    $( this ).find( 'option' ).each( function(i) {
                        option_index++;
                        values_list += '<li class="wpc_selector_option wpc_ingroup" data-optgroup="' + option_group_index + '" data-option_index="' + option_index + '">' + $( this ).html() + '</li>';
                    });
                }
            });

            /*if( $( this ).find( 'optgroup' ).length > 0 ) {

                var option_index = 0;
                $( this ).find( 'optgroup' ).each( function(i) {
                    var color = '';

                    if( typeof( $( this ).data('color') ) !== 'undefined' ) {
                        color = 'style="color:' + $( this ).data('color') + ' !important;"';
                    }

                    values_list += '<li class="option_group" ' + color + '>' + $( this ).attr( 'label' ) + '</li>';

                    $( this ).find( 'option' ).each( function() {
                        option_index++;
                        values_list += '<li class="wpc_selector_option wpc_ingroup" data-optgroup="' + ( i*1 + 1 ) + '" data-option_index="' + option_index + '">' + $( this ).html() + '</li>';
                    });

                });

            } else {

                $( this ).find( 'option' ).each( function(e) {
                    values_list += '<li class="wpc_selector_option" data-option_index="' + ( e*1 + 1 ) + '">' + $( this ).html() + '</li>';
                });

            }*/


            //build HTML for selector
            $( this ).hide().after(
                '<div class="wpc_selector_wrapper" data="' + unique + '">' +
                    '<div class="wpc_selector ' + multiple + '">' +
                    placeholder +
                    '</div>' +
                    '<div class="wpc_selector_drop">' +
                        search +
                        '<ul class="wpc_selector_options">' +
                            values_list +
                        '</ul>' +
                        '<div class="wpc_empty_search"></div>' +
                    '</div>' +
                '</div>'
            );

            //click on selectbox for dropdown list
            $('.wpc_selector_wrapper[data="' + unique + '"]').on( 'click', '.wpc_selector', function(e) {
                var obj = $( this );

                if( obj.hasClass( 'wpc_disabled' ) ) {
                    return false;
                }

                if( obj.parents('.wpc_selector_wrapper').hasClass( 'wpc_empty_selectbox' ) ) {
                    return false;
                }

                if( !obj.hasClass( 'wpc_dropped' ) ) {
                    $('.wpc_selector').removeClass('wpc_dropped');
                }

                obj.toggleClass( 'wpc_dropped' );

                e.preventDefault();
                e.stopPropagation();

                if( obj.hasClass( 'wpc_dropped' ) ) {
                    $('body').bind( 'click', function(event) {
                        obj.removeClass( 'wpc_dropped' );
                        $('body').unbind( event );
                    });
                }
            });

            //click on selectbox label
            label.click( function(e) {
                $('.wpc_selector_wrapper[data="' + $('#' + $( this ).attr('for')).data('wpc_select_unique') + '"] .wpc_selector').trigger( 'click' );
                e.preventDefault();
                e.stopPropagation();
            });

            $('.wpc_selector_wrapper[data="' + unique + '"]').on( 'click', '.wpc_drop_search, .option_group', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });


            //search here
            $('.wpc_selector_wrapper[data="' + unique + '"]').on( 'keyup', '.wpc_drop_search', function(e) {
                $( this ).parents('.wpc_selector_wrapper').find('.wpc_empty_search').html('').hide();
                $( this ).parents('.wpc_selector_wrapper').find('.wpc_selector_option').removeClass('wpc_searched').show();
                $( this ).parents('.wpc_selector_wrapper').find('.option_group').show();

                var search_value = $( this ).val();
                if( search_value != '' ) {

                    $( this ).parents('.wpc_selector_wrapper').find('.wpc_selector_option').removeClass('wpc_searched').hide().each( function() {
                        if( $( this ).html().toLowerCase().indexOf( search_value ) >= 0 ) {
                            $( this ).addClass( 'wpc_searched' ).show();
                        }
                    });

                    $( this ).parents('.wpc_selector_wrapper').find('.option_group').each( function(i) {
                        if( $( this ).parents('.wpc_selector_wrapper').find('.wpc_selector_option.wpc_searched[data-optgroup="' + ( i*1 + 1 ) + '"]').length > 0 ) {
                            $( this ).show();
                        } else {
                            $( this ).hide();
                        }
                    });

                    if( $( this ).parents('.wpc_selector_wrapper').find('.wpc_selector_option.wpc_searched').length <= 0 ) {
                        $( this ).parents('.wpc_selector_wrapper').find('.wpc_empty_search').html( options.empty_text + ' "' + search_value + '"' ).show();
                    }

                }
            });


            //click on selected item
            $('.wpc_selector_wrapper[data="' + unique + '"]').on( 'click', '.wpc_selector_option', function(e) {
                var obj = $( this );
                if( obj.hasClass( 'wpc_disabled' ) ) {
                    return false;
                }

                var select_value = obj.data( 'option_index' );
                if( obj.parents('.wpc_selector_wrapper').find('.wpc_selector').hasClass( 'wpc_multiple' ) ) {
                    //multiple selectbox
                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector_placeholder').hide();
                    var color = '';

                    if( typeof( selectbox.find( 'optgroup:eq(' + (obj.data('optgroup')*1 - 1 ) + ')' ).data('color') ) !== 'undefined' ) {
                        // note: hexStr should be #rrggbb
                        var hex = parseInt(selectbox.find( 'optgroup:eq(' + (obj.data('optgroup')*1 - 1 ) + ')' ).data('color').substring(1), 16);
                        var r = (hex & 0xff0000) >> 16;
                        var g = (hex & 0x00ff00) >> 8;
                        var b = hex & 0x0000ff;

                        var opacity = '0.5';
                        if( typeof( options.opacity ) !== 'undefined' ) {
                            opacity = options.opacity;
                        }

                        color = 'style="background-color:rgba(' + r + ',' + g + ',' + b + ',' + opacity + ') !important;"';
                    }

                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector').append(
                        '<div class="wpc_selector_values" data-value="' + select_value + '" ' + color + ' title="' + selectbox.find( 'optgroup:eq(' + ( obj.data('optgroup')*1 - 1 ) + ')' ).data('single_title') + '">' + selectbox.find( 'option:eq(' + ( select_value*1 - 1 ) + ')' ).html() +'<div class="wpc_selector_value_remove">&times;</div></div>'
                    );

                } else {
                    //single selectbox
                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector_value').remove();
                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option').removeClass( 'wpc_disabled' );
                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector_placeholder').hide().after(
                        '<a href="javascript:;" class="wpc_selector_value">' + selectbox.find( 'option:eq(' + ( select_value*1 - 1 ) + ')' ).html() +'</a>'
                    );

                    selectbox.find( 'option' ).prop( 'selected', false );
                }

                obj.addClass( 'wpc_disabled' );


                if( obj.siblings( '.option_group').length > 0 ) {
                    if( obj.parents('.wpc_selector_options').find('.wpc_selector_option[data-optgroup="' + obj.data('optgroup') +'"]:not(.wpc_disabled)').length == 0 ) {
                        obj.parents('.wpc_selector_options').find('.option_group:eq(' + ( obj.data('optgroup')*1 - 1 ) + ')').hide();
                    }
                }

                if( obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option:not(.wpc_disabled)').length == 0 ) {
                    obj.parents('.wpc_selector_wrapper').addClass( 'wpc_empty_selectbox' );
                }

                selectbox.find( 'option:eq(' + ( select_value*1 - 1 ) + ')' ).prop( 'selected', true );

                obj.parents('.wpc_selector_wrapper').find('.wpc_selector').removeClass( 'wpc_dropped' );

                if( typeof options.onChangeValue === "function" ) {
                    options.onChangeValue.apply( $( this ), [unique] );
                }

                selectbox.trigger( 'change' );
            });


            if( typeof( $( this ).attr( 'multiple' ) ) !== 'undefined' && $( this ).attr( 'multiple' ) != '' ) {
                //remove multi value
                $('.wpc_selector_wrapper[data="' + unique + '"]').on('click', '.wpc_selector_value_remove', function (e) {
                    var obj = $(this);
                    var removed_value = obj.parent().data('value');

                    if (obj.parents('.wpc_selector_wrapper').find('.wpc_selector').hasClass('wpc_disabled')) {
                        return false;
                    }

                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option.wpc_disabled[data-option_index="' + removed_value + '"]').removeClass('wpc_disabled');

                    var optgroup = obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option[data-option_index="' + removed_value + '"]').data('optgroup');
                    if (obj.parents('.wpc_selector_wrapper').find('.option_group').length > 0) {

                        if (obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option[data-optgroup="' + optgroup + '"]:not(.wpc_disabled)').length > 0) {
                            obj.parents('.wpc_selector_wrapper').find('.option_group:eq(' + ( optgroup * 1 - 1 ) + ')').show();
                        }
                    }

                    if (obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option:not(.wpc_disabled)').length > 0) {
                        obj.parents('.wpc_selector_wrapper').removeClass('wpc_empty_selectbox');
                    }

                    selectbox.find('option:eq(' + ( removed_value * 1 - 1 ) + ')').prop('selected', false);

                    if (obj.parents('.wpc_selector').find('.wpc_selector_values').length == 1) {
                        obj.parents('.wpc_selector_wrapper').find('.wpc_selector_placeholder').show();
                    }

                    obj.parent().remove();

                    if (typeof options.onChangeValue === "function") {
                        options.onChangeValue.apply($(this), [unique]);
                    }

                    selectbox.trigger('change');

                    e.preventDefault();
                    e.stopPropagation();
                });
            }

            //load selected values
            methods.load_values.apply( $(this) );
        },
        enable : function( settings ) {
            $('.wpc_selector_wrapper[data="' + settings + '"] .wpc_selector').removeClass( 'wpc_disabled' );
        },
        disable : function( settings ) {
            $('.wpc_selector_wrapper[data="' + settings + '"] .wpc_selector').addClass( 'wpc_disabled' );
        },
        load_values: function() {
            var selectbox = $( this );
            selectbox.find( 'option:selected' ).each( function() {
                if( $(this).val() != '' ) {
                    $('.wpc_selector_wrapper[data="' + selectbox.data('wpc_select_unique') + '"] .wpc_selector_option[data-option_index="' + ( $(this).parents('select').find('option').index(this)*1 + 1 ) + '"]').trigger( 'click' );
                }
            });
        },
        clear : function( settings ) {
            var selectbox = $( this );

            $('.wpc_selector_wrapper[data="' + selectbox.data('wpc_select_unique') + '"]').find( '.wpc_selector_value_remove').each( function() {
                var obj = $( this );
                var removed_value = obj.parent().data('value');

                obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option.wpc_disabled[data-option_index="' + removed_value + '"]').removeClass( 'wpc_disabled' );

                var optgroup = obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option[data-option_index="' + removed_value + '"]').data( 'optgroup' );
                if( obj.parents('.wpc_selector_wrapper').find( '.option_group' ).length > 0 ) {

                    if( obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option[data-optgroup="' + optgroup +'"]:not(.wpc_disabled)').length > 0 ) {
                        obj.parents('.wpc_selector_wrapper').find('.option_group:eq(' + ( optgroup*1 - 1 ) + ')').show();
                    }
                }

                if( obj.parents('.wpc_selector_wrapper').find('.wpc_selector_option:not(.wpc_disabled)').length > 0 ) {
                    obj.parents('.wpc_selector_wrapper').removeClass( 'wpc_empty_selectbox' );
                }

                selectbox.find( 'option:eq(' + ( removed_value*1 - 1 ) + ')' ).prop( 'selected', false );

                if( obj.parents('.wpc_selector').find( '.wpc_selector_values' ).length == 1 ) {
                    obj.parents('.wpc_selector_wrapper').find('.wpc_selector_placeholder').show();
                }

                obj.parent().remove();
            });
        },
        destroy : function( settings ) {
            var selectbox = $( this );
            selectbox.removeClass( 'is_wpc_class' );
            $('.wpc_selector_wrapper[data="' + settings + '"]').remove();
        }
    };

    $.fn.wpc_select = function( method ) {
        if( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist for jQuery.wpc_select plugin' );
        }
    };

})( jQuery );