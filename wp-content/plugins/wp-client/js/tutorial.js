var tutorial_current_step = '';
var prev_selector = '';
jQuery(document).ready( function($) {
    var ajaxurl = wpc_tutorial.ajax_url;
    tutorial_current_step = wpc_tutorial.current_step;
    open_tutorial();

    jQuery(document).on('click.pointer', '.button.prev', function() {
        var section = wpc_tutorial.current_section_key;
        var step = '';
        if( jQuery(this).data('type') == 'section' ) {
            section = wpc_tutorial.prev_section_key;
            tutorial_current_step = step = 0;
        } else {
            if( tutorial_current_step*1 > 0 ) {
                tutorial_current_step = step = tutorial_current_step*1 - 1;
            }
        }

        if( typeof( wpc_instructions[ tutorial_current_step ].url ) != 'undefined' &&
            wpc_tutorial.current_url == wpc_instructions[ tutorial_current_step ].url &&
            wpc_tutorial.current_section_key === section ) {
            $.post( ajaxurl, {
                action  : 'control-pointer',
                step    : step,
                section : section
            });
            open_tutorial();
        } else {
            $('.wp-pointer-buttons > div').remove();
            $('.wp-pointer-content > p').html('<img alt="" src="' + wpc_tutorial.plugin_url + '/images/ajax_loading.gif" />');
            $('.wp-pointer-content > p').css('text-align', 'center');
            var redirect = wpc_instructions[ tutorial_current_step ].url;
            if( wpc_tutorial.current_section_key !== section ) {
                redirect = wpc_tutorial.prev_section_url;
            }
            $.post( ajaxurl, {
                action  : 'control-pointer',
                step    : step,
                section : section
            }, function() {
                window.location = redirect;
            });
        }
        return false;
    });

    jQuery(document).on('click.pointer', '.button-primary.next', function() {
        var section = wpc_tutorial.current_section_key;
        var step = '';
        if( jQuery(this).data('type') == 'section' ) {
            section = wpc_tutorial.next_section_key;
            tutorial_current_step = step = 0;
        } else {
            if( tutorial_current_step*1 + 1 < wpc_instructions.length ) {
                tutorial_current_step = step = tutorial_current_step*1 + 1;
            }
        }

        if( typeof( wpc_instructions[ tutorial_current_step ].url ) != 'undefined' &&
            wpc_tutorial.current_url == wpc_instructions[ tutorial_current_step ].url &&
            wpc_tutorial.current_section_key === section ) {
            $.post( ajaxurl, {
                action  : 'control-pointer',
                step    : step,
                section : section
            });
            open_tutorial();
        } else {
            $('.wp-pointer-buttons > div').remove();
            $('.wp-pointer-content > p').html('<img alt="" src="' + wpc_tutorial.plugin_url + '/images/ajax_loading.gif" />');
            $('.wp-pointer-content > p').css('text-align', 'center');
            var redirect = wpc_instructions[ tutorial_current_step ].url;
            if( wpc_tutorial.current_section_key !== section ) {
                redirect = wpc_tutorial.next_section_url;
            }
            $.post( ajaxurl, {
                action  : 'control-pointer',
                step    : step,
                section : section
            }, function() {
                window.location = redirect;
            });
        }
        return false;
    });

    jQuery(document).on('click', '.wpc_dismiss_button', function() {
        $.post( ajaxurl, {
            action  : 'control-pointer',
            dismiss : 1
        });
        jQuery( prev_selector ).pointer('close');
        tutorial_current_step = '';
        prev_selector = '';
    });
});

function open_tutorial() {
    if( typeof( wpc_instructions[ tutorial_current_step ].selector ) != 'undefined' ){
        if( prev_selector !== '' ) {
            jQuery( prev_selector ).pointer('close');
        }

        if( typeof( wpc_instructions[ tutorial_current_step ].js ) != 'undefined' ) {
            eval( wpc_instructions[ tutorial_current_step ].js );
        }

        var min = 1;
        var max = 999999;
        var uniqid = 'wpc-pointer-' + Math.floor(Math.random() * (max - min)) + min;

        jQuery( wpc_instructions[ tutorial_current_step ].selector ).pointer({
            pointerWidth: 400,
            position: wpc_instructions[ tutorial_current_step ].position,
            //nudgehorizontal: wpc_instructions[ tutorial_current_step ].x,
            //nudgevertical: wpc_instructions[ tutorial_current_step ].y,
            pointerClass: uniqid,
            content: function() {
                if( typeof( wpc_instructions[ tutorial_current_step ].content ) ){
                    return '<a class="wpc_dismiss_button" href="#" title="' + wpc_tutorial.text.dismiss + '"></a>' +
                    wpc_instructions[ tutorial_current_step ].content;
                }
                return '';
            },
            buttons: function( event, obj ) {
                var next_text = tutorial_current_step*1 + 1 < wpc_instructions.length ? wpc_tutorial.text.next : wpc_tutorial.text.next_section;

                var buttons = '<div>';
                if( tutorial_current_step == 0 && wpc_tutorial.prev_section_url !== '' ) {
                    buttons += '<a class="prev prev_section button" data-type="section" href="#" title="' + wpc_tutorial.text.previous_section + '">' + wpc_tutorial.text.previous_section + '</a>';
                }
                if( wpc_tutorial.next_section_url !== '' || tutorial_current_step*1 + 1 < wpc_instructions.length ) {
                    buttons += '<a class="next button-primary" data-type="' + ( tutorial_current_step*1 + 1 < wpc_instructions.length ? 'step' : 'section' ) + '" href="#" title="' + next_text + '">' + next_text + '</a>';
                }
                buttons += '<span class="wpc-tut-step">' + wpc_tutorial.text.counter.split('{step_num}').join( tutorial_current_step * 1 + 1 ) + '</span>';
                if( tutorial_current_step*1 > 0 ) {
                    buttons += '<a class="prev button" data-type="step" href="#" title="' + wpc_tutorial.text.previous + '">' + wpc_tutorial.text.previous + '</a>';
                }
                buttons += '</div>';
                return jQuery( buttons );
            }
        }).pointer('open');

        jQuery('.' + uniqid + ' .wp-pointer-arrow').css('left', '');
        var selector_position = jQuery( wpc_instructions[ tutorial_current_step ].selector ).offset();
        var pointer_position = jQuery('.wp-pointer-arrow').offset();

        if( selector_position.left - pointer_position.left > 0 ) {
            jQuery('.' + uniqid + ' .wp-pointer-arrow').css('left', ( selector_position.left - pointer_position.left + jQuery( wpc_instructions[ tutorial_current_step ].selector ).width()/2 + 50 ) + 'px');
        }

        if( jQuery('.wpc-pointer').length ) {
            jQuery(window).scrollTop(jQuery('.' + uniqid + ' .wpc-pointer').offset().top - 300);
        }
        prev_selector = wpc_instructions[ tutorial_current_step ].selector;
    }
}