/*
 * Slider Plugin
 */

(function( $, undefined ) {
    var options;

    var default_options = {
        element             : '.wpc_slider_item',
        visible             : 1,
        autoPlay            : false,  // true or false
        autoPlayDelay       : 10,  // delay in seconds
        next_button_label   : 'Next',
        prev_button_label   : 'Previous',
        show_counter        : false  // true or false
    };

    var methods = {
        init : function( settings ) {
            var slider = $( this );

            //add class to element
            if( slider.hasClass( 'wpc_slider' ) ) {
                return false;
            }
            slider.addClass( 'wpc_slider' );

            //merge default & current options
            options = $.extend( {}, default_options, settings );
            this.data( 'options', options );

            //add count items to options
            options.items = $( this ).find( '.wpc_slider_content ' + options.element ).length;
            this.data( 'options', options );

            if( options.items > 1 ) {
                //build HTML for selector
                $(this).prepend(
                    '<div class="wpc_slide_right" title="' + options.next_button_label + '"></div>' +
                    '<div class="wpc_slide_left" title="' + options.prev_button_label + '"></div>'
                );

                if( options.show_counter ) {
                    $(this).prepend(
                        '<div class="wpc_slider_counter">' +
                            '<span class="wpc_slider_current_slide">1</span>/' +
                            '<span class="wpc_slider_slides_count">' + options.items + '</span>' +
                        '</div>'
                    );
                }
            }
            slider.find( '.wpc_slider_content ' + options.element ).parent().css('left',0);

            //resize actions
            methods.resize.call( $( this ), options );
            $( this ).resize( function() {
                methods.resize.call( $( this ), options );
            });

            $(window).resize( function () {
                slider.find( '.wpc_slider_content ' + options.element ).parent().css({
                    'width':(slider.width() - 60 )*options.items + 'px',
                    'left':(options.current_item - 1)*(slider.width() - 60 )*-1 + 'px'
                });
                slider.find( '.wpc_slider_content ' + options.element ).css( 'width', ( slider.width() - 60 ) + 'px' );
            });

            if( options.items > 1 ) {
                //button click actions
                $('.wpc_slide_right').click(function () {
                    if ($(this).hasClass('disabled')) {
                        return false;
                    }

                    methods.slide.apply($(this), [options, 'right']);
                });
                $('.wpc_slide_left').click(function () {
                    if ($(this).hasClass('disabled')) {
                        return false;
                    }

                    methods.slide.apply($(this), [options, 'left']);
                });

                if ( options.autoPlay ) {
                    function aPlay() {
                        $('.wpc_slide_right').trigger('click');
                    }

                    options.delId = setInterval(aPlay, options.autoPlayDelay * 1000);
                    this.data( 'options', options );

                    $(this).hover(
                        function () {
                            clearInterval( options.delId );
                        },
                        function () {
                            options.delId = setInterval(aPlay, options.autoPlayDelay * 1000);
                            $(this).data( 'options', options );
                        }
                    );
                }
            }

        },
        slide : function( options, direction ) {
            var obj = $( this );

            methods.disableControls.apply( obj );

            var itemWidth = ( $(this).parents('.wpc_slider').width() - 60 );
            var list = $(this).parents('.wpc_slider').find( '.wpc_slider_content ' + options.element).parent();
            var end_left = '0px';
            var current_item = 1;

            if( direction == 'right' ) {
                if ( list.css('left') == '-' + ( itemWidth * ( options.items - 1 ) ) + 'px') {
                    end_left = '0px';
                    current_item = 1;
                } else {
                    end_left = ( parseInt( list.css('left') ) - itemWidth ) + 'px';
                    current_item = ( parseInt( list.css('left') ) - itemWidth )*-1 / itemWidth + 1;
                }
            } else {
                if( list.css('left') == '0px' ) {
                    end_left = '-' + ( itemWidth * ( options.items - 1 ) ) + 'px';
                    current_item = options.items;
                } else {
                    end_left = ( parseInt( list.css('left') ) + itemWidth ) + 'px';
                    current_item = ( parseInt( list.css('left') ) + itemWidth )*-1 / itemWidth + 1;
                }
            }

            options.current_item = current_item;
            this.data( 'options', options );

            if ( options.autoPlay ) {
                clearInterval( options.delId );
            }
            list.animate({left: end_left}, 300, function() {
                methods.enableControls.apply( obj );
                if( options.show_counter ) {
                    $('.wpc_slider_counter .wpc_slider_current_slide').html( current_item );
                }
                if ( options.autoPlay ) {
                    function aPlay() {
                        $('.wpc_slide_right').trigger('click');
                    }
                    options.delId = setInterval(aPlay, options.autoPlayDelay * 1000);
                    obj.data( 'options', options );
                }
            });
        },
        enableControls : function(options) {
            $( '.wpc_slide_right, .wpc_slide_left' ).removeClass( 'disabled' );
        },
        disableControls : function(options) {
            $( '.wpc_slide_right, .wpc_slide_left' ).addClass( 'disabled' );
        },
        resize : function( options ) {
            var item = $( this ).find( '.wpc_slider_content ' + options.element );

            item.parent().css({
                'width' : ( $(this).width() - 60 )*options.items + 'px',
                'left':(options.current_item - 1)*($(this).width() - 60 )*-1 + 'px'
            });
            item.css( 'width', ( $(this).width() - 60 ) + 'px' );
        },
        rebuild : function() {
            var options = this.data( 'options' );
            options.items = $( this ).find( '.wpc_slider_content ' + options.element ).length;
            this.data( 'options', options );
            $(this).find( '.wpc_slider_content ' + options.element ).parent().css('left',0);

            if( options.show_counter ) {
                $('.wpc_slider_counter .wpc_slider_current_slide').html( 1 );
                $('.wpc_slider_counter .wpc_slider_slides_count').html( options.items );
            }

            $(window).trigger('resize');
        },
        destroy : function() {
            var options = this.data( 'options' );
            $('.wpc_slide_right,.wpc_slide_left,.wpc_slider_counter').remove();
            $(this).find( '.wpc_slider_content ' + options.element ).parent().css('left',0);
            $(this).unbind('hover');
            clearInterval( options.delId );
        }
    };

    $.fn.wpc_slider = function( method ) {
        if( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist for jQuery.wpc_slider plugin' );
        }
    };

})( jQuery );