if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
  }
}

function checkPasswordStrength( $pass1,
                                $pass2,
                                $strengthResult,
                                $submitButton,
                                blacklistArray,
                                user ) {
    var pass1 = $pass1.val();
    var pass2 = $pass2.val();

    jQuery(".wpc_requirement_min_length").css('text-decoration', '');
    jQuery(".wpc_requirement_mixed_case").css('text-decoration', '');
    jQuery(".wpc_requirement_numeric_digits").css('text-decoration', '');
    jQuery(".wpc_requirement_special_chars").css('text-decoration', '');
    jQuery(".wpc_requirement_level").css('text-decoration', '');

    if( pass1 == pass2 && pass2 == '' ) {
        if( user ) {
            var validated = 0;
            $submitButton.parents('form').find('*[data-required_field="1"]').each( function() {
                if( jQuery(this).prop("tagName").toLowerCase() == 'textarea' ) {
                    if( jQuery(this).val() != '' ) {
                        //if field not required or required and not empty
                        validated++;
                    }
                } else if( jQuery(this).prop("tagName").toLowerCase() == 'select' ) {
                    if( !( jQuery(this).val() == '' || jQuery(this).val() == null ) ) {
                        //if field not required or required and not empty
                        validated++;
                    }
                } else if( jQuery(this).prop("tagName").toLowerCase() == 'input' ) {
                    if( jQuery(this).attr('type') == 'radio' || jQuery(this).attr('type') == 'checkbox' ) {
                        var name = jQuery(this).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
                        if( 'undefined' != typeof jQuery( 'input[name=' + name + ']:checked' ).val() ) {
                            validated++;
                        }
                    } else {
                        if ( jQuery(this).val() != '' ) {
                            //if field not empty
                            //check field content
                            if ( jQuery(this).attr('type' ) == 'email') {
                                var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                                if ( emailReg.test( jQuery(this).val() ) ) {
                                    validated++;
                                }
                            } else {
                                validated++;
                            }
                        }
                    }
                }
            });

            if( $submitButton.parents('form').find('*[data-required_field="1"]').length == validated ) {
                $submitButton.removeAttr( 'disabled' );
            }
        } else {
            $submitButton.removeAttr( 'disabled' );
        }
        $strengthResult.removeClass( 'short bad good strong' ).html( wpc_text_var.pwsL10n.empty );
        return false;
    }

    $submitButton.attr( 'disabled', 'disabled' );
    $strengthResult.removeClass( 'short bad good strong' );

    if( wpc_password_protect.min_length > 1 && pass1.length < wpc_password_protect.min_length ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.short );
        var strength = 6;
    } else if( wpc_password_protect.min_length > 1 && pass1.length >= wpc_password_protect.min_length ) {
        jQuery(".wpc_requirement_min_length").css('text-decoration', 'line-through');
    }

    if( wpc_password_protect.mixed_case == 'yes' && ( pass1.toUpperCase() == pass1 || pass1.toLowerCase() == pass1 ) ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.mixed_case );
        var strength = 6;
    } else if( wpc_password_protect.mixed_case == 'yes' && pass1.toUpperCase() != pass1 && pass1.toLowerCase() != pass1 ) {
        jQuery(".wpc_requirement_mixed_case").css('text-decoration', 'line-through');
    }

    if( wpc_password_protect.numeric_digits == 'yes' && !/\d/.test(pass1) ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.numbers );
        var strength = 6;
    } else if( wpc_password_protect.numeric_digits == 'yes' && /\d/.test(pass1) ) {
        jQuery(".wpc_requirement_numeric_digits").css('text-decoration', 'line-through');
    }

    if( wpc_password_protect.special_chars == 'yes' && /^[0-9A-Za-z ]+$/.test( pass1 ) ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.special_chars );
        var strength = 6;
    } else if( wpc_password_protect.special_chars == 'yes' && !/^[0-9A-Za-z ]+$/.test( pass1 ) ) {
        jQuery(".wpc_requirement_special_chars").css('text-decoration', 'line-through');
    }

    if( typeof strength == 'undefined' ) {
        blacklistArray = blacklistArray.concat( wp.passwordStrength.userInputBlacklist() );
        var strength = wp.passwordStrength.meter( pass1, blacklistArray, pass2 );
        switch ( strength ) {
            case 2:
                $strengthResult.addClass( 'bad' ).html( wpc_text_var.pwsL10n.bad );
                break;
            case 3:
                $strengthResult.addClass( 'good' ).html( wpc_text_var.pwsL10n.good );
                break;
            case 4:
                $strengthResult.addClass( 'strong' ).html( wpc_text_var.pwsL10n.strong );
                break;
            case 5:
                $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.mismatch );
                break;
            default:
                if( in_array( pass1, wpc_password_protect.blackList ) ) {
                    $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.blacklist );
                } else if( pass1.length < 6 && wpc_password_protect.min_length == '1' ) {
                    $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.short );
                } else if ( pass1.length < 6 && wpc_password_protect.min_length > 1 ) {
                    $strengthResult.addClass( 'bad' ).html( wpc_text_var.pwsL10n.bad );
                } else {
                    $strengthResult.addClass( 'bad' ).html( wpc_text_var.pwsL10n.weak );
                }
        }
    }

    switch( wpc_password_protect.strength ) {
        case '2':
            if ( 0 <= strength && 4 >= strength && '' !== pass2.trim() ) {
                if( user ) {
                    var validated = 0;
                    $submitButton.parents('form').find('*[data-required_field="1"]').each( function() {
                        if( jQuery(this).prop("tagName").toLowerCase() == 'textarea' ) {
                            if( jQuery(this).val() != '' ) {
                                //if field not required or required and not empty
                                validated++;
                            }
                        } else if( jQuery(this).prop("tagName").toLowerCase() == 'select' ) {
                            if( !( jQuery(this).val() == '' || jQuery(this).val() == null ) ) {
                                //if field not required or required and not empty
                                validated++;
                            }
                        } else if( jQuery(this).prop("tagName").toLowerCase() == 'input' ) {
                            if( jQuery(this).attr('type') == 'radio' || jQuery(this).attr('type') == 'checkbox' ) {
                                var name = jQuery(this).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
                                if( 'undefined' != typeof jQuery( 'input[name=' + name + ']:checked' ).val() ) {
                                    validated++;
                                }
                            } else {
                                if ( jQuery(this).val() != '' ) {
                                    //if field not empty
                                    //check field content
                                    if ( jQuery(this).attr('type' ) == 'email') {
                                        var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                                        if ( emailReg.test( jQuery(this).val() ) ) {
                                            validated++;
                                        }
                                    } else {
                                        validated++;
                                    }
                                }
                            }
                        }
                    });

                    if( $submitButton.parents('form').find('*[data-required_field="1"]').length == validated ) {
                        $submitButton.removeAttr( 'disabled' );
                    }
                } else {
                    $submitButton.removeAttr( 'disabled' );
                }
            }
            if( 2 == strength || 3 == strength || 4 == strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
        case '3':
            if ( ( 3 == strength || 4 == strength ) && '' !== pass2.trim() ) {
                if( user ) {
                    var validated = 0;
                    $submitButton.parents('form').find('*[data-required_field="1"]').each( function() {
                        if( jQuery(this).prop("tagName").toLowerCase() == 'textarea' ) {
                            if( jQuery(this).val() != '' ) {
                                //if field not required or required and not empty
                                validated++;
                            }
                        } else if( jQuery(this).prop("tagName").toLowerCase() == 'select' ) {
                            if( !( jQuery(this).val() == '' || jQuery(this).val() == null ) ) {
                                //if field not required or required and not empty
                                validated++;
                            }
                        } else if( jQuery(this).prop("tagName").toLowerCase() == 'input' ) {
                            if( jQuery(this).attr('type') == 'radio' || jQuery(this).attr('type') == 'checkbox' ) {
                                var name = jQuery(this).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
                                if( 'undefined' != typeof jQuery( 'input[name=' + name + ']:checked' ).val() ) {
                                    validated++;
                                }
                            } else {
                                if ( jQuery(this).val() != '' ) {
                                    //if field not empty
                                    //check field content
                                    if ( jQuery(this).attr('type' ) == 'email') {
                                        var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                                        if ( emailReg.test( jQuery(this).val() ) ) {
                                            validated++;
                                        }
                                    } else {
                                        validated++;
                                    }
                                }
                            }
                        }
                    });

                    if( $submitButton.parents('form').find('*[data-required_field="1"]').length == validated ) {
                        $submitButton.removeAttr( 'disabled' );
                    }
                } else {
                    $submitButton.removeAttr( 'disabled' );
                }
            }
            if( 3 == strength || 4 == strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
        case '4':
            if ( 4 == strength && '' !== pass2.trim() ) {
                if( user ) {
                    var validated = 0;
                    $submitButton.parents('form').find('*[data-required_field="1"]').each( function() {
                        if( jQuery(this).prop("tagName").toLowerCase() == 'textarea' ) {
                            if( jQuery(this).val() != '' ) {
                                //if field not required or required and not empty
                                validated++;
                            }
                        } else if( jQuery(this).prop("tagName").toLowerCase() == 'select' ) {
                            if( !( jQuery(this).val() == '' || jQuery(this).val() == null ) ) {
                                //if field not required or required and not empty
                                validated++;
                            }
                        } else if( jQuery(this).prop("tagName").toLowerCase() == 'input' ) {
                            if( jQuery(this).attr('type') == 'radio' || jQuery(this).attr('type') == 'checkbox' ) {
                                var name = jQuery(this).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
                                if( 'undefined' != typeof jQuery( 'input[name=' + name + ']:checked' ).val() ) {
                                    validated++;
                                }
                            } else {
                                if ( jQuery(this).val() != '' ) {
                                    //if field not empty
                                    //check field content
                                    if ( jQuery(this).attr('type' ) == 'email') {
                                        var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                                        if ( emailReg.test( jQuery(this).val() ) ) {
                                            validated++;
                                        }
                                    } else {
                                        validated++;
                                    }
                                }
                            }
                        }
                    });

                    if( $submitButton.parents('form').find('*[data-required_field="1"]').length == validated ) {
                        $submitButton.removeAttr( 'disabled' );
                    }
                } else {
                    $submitButton.removeAttr( 'disabled' );
                }
            }
            if( 4 == strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
        default:
            if ( !( 6 == strength || pass1 != pass2 ) && '' !== pass2.trim() ) {
                if( user ) {
                    if( $submitButton.parents('form').find('.wpc_form_field.wpc_validate_error').length == 0 ) {
                        $submitButton.removeAttr( 'disabled' );
                    }
                } else {
                    $submitButton.removeAttr( 'disabled' );
                }
            }
            if( 6 != strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
    }

    return strength;
}

function wpc_generate_password( pass1, pass2 ) {
    jQuery('.wpc_generate_password_button').click(function() {
        var obj = jQuery(this);
        obj.next('.wpc_show_password').remove();
        jQuery( pass1 ).val('');
        jQuery( pass2 ).val('');
        jQuery( pass1 ).trigger('keyup');
        obj.after(' <span class="wpc_ajax_loading"></span>');
        jQuery.ajax({
            type     : 'POST',
            dataType : 'json',
            url      : wpc_password_protect.ajax_url,
            data     : 'action=wpc_generate_password',
            success: function( data ){
                if( data.status ) {
                    obj.next('.wpc_ajax_loading').remove();
                    jQuery( pass1 ).val( data.message );
                    jQuery( pass2 ).val( data.message );
                    jQuery( pass1 ).trigger('keyup');
                    jQuery( pass1 ).trigger('change');
                    obj.after(' <span class="wpc_show_password">' + data.message + '</span>');
                } else {
                    alert( data.message );
                }
            }
        });
    });
}

jQuery( document ).ready( function( $ ) {
    $( '#pass1, #pass2, #pass1-text' ).off('keyup');

    $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

    $( 'body' ).on( 'pwupdate', '#pass1', function( event ) {
        checkPasswordStrength(
            $('#pass1-text'),
            $('#pass1-text'),
            $('#pass-strength-result'),
            $('#submit, #wp-submit'),
            wpc_password_protect.blackList
        );
    });

    $( 'body' ).on( 'keyup', '#pass1, #pass2, #pass1-text', function( event ) {
        if( $(this).attr('id') == 'pass1-text' ) {
            checkPasswordStrength(
                $('#pass1-text'),
                $('#pass1-text'),
                $('#pass-strength-result'),
                $('#submit, #wp-submit'),
                wpc_password_protect.blackList
            );
        } else {
            checkPasswordStrength(
                $('#pass1'),
                $('#pass2'),
                $('#pass-strength-result'),
                $('#submit, #wp-submit'),
                wpc_password_protect.blackList
            );
        }
    });
});

function in_array(needle, haystack, strict) {
    var found = false, key, strict = !!strict;

    for (key in haystack) {
        if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
            found = true;
            break;
        }
    }

    return found;
}
