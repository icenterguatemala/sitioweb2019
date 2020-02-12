jQuery( document ).ready( function( $ ) {
    var form = $( "#wpc_add_staff" );

    $('#pass-strength-result').show();
    $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

    $( 'body' ).on( 'keyup', '#wpc_pass1, #wpc_pass2',
        function() {
            checkPasswordStrength(
                $('#wpc_pass1'),
                $('#wpc_pass2'),
                $('#pass-strength-result'),
                form.find('input[type="submit"]'),
                wpc_password_protect.blackList,
                true
            );
        }
    );

    wpc_generate_password( '#wpc_pass1', '#wpc_pass2' );

    if( form.find('*[data-required_field="1"]').length > 0 ) {
        if( !form.hasClass('wpc_edit_staff') ) {
            form.find('input[type="submit"]').prop('disabled', true).attr('disabled',true);
        }
        infoSubmit();
    }

    //input fields
    form.find('input').focusout( function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');
        if( $(this).data('required_field') ) {
            if( $(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox' ) {
                var name = $(this).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
                if( 'undefined' == typeof $( 'input[name=' + name + ']:checked' ).val() ) {
                    showValidationMessage( field, 'required' );
                } else {
                    hideValidationMessage( field );
                }
            } else {
                //another fields
                if ( $(this).val() == '' ) {
                    //if field empty
                    showValidationMessage( field, 'required' );
                } else {
                    //if field not empty
                    //check field content
                    if ( $(this).attr('type' ) == 'email') {
                        var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                        if ( !emailReg.test( $(this).val() ) ) {
                            showValidationMessage( field, 'wrong');
                        } else {
                            hideValidationMessage( field );
                        }
                    } else {
                        hideValidationMessage( field );
                    }
                }
            }

            triggerSubmit();
        }
    });

    form.find('input').change( function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');
        if( $(this).data('required_field') ) {
            triggerSubmit();
        }
    });

    form.on('keyup', '.wpc_form_field.wpc_validate_error input', function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');

        if( !( $(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox' ) ) {
            if( $(this).data('required_field') && $(this).val() == '' ) {
                //if field required and empty
                showValidationMessage( field, 'required' );
            } else {
                //if field not required or required and not empty
                //check field content
                if( $(this).attr('type') == 'email' ) {
                    var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                    if ( !emailReg.test( $(this).val() ) ) {
                        showValidationMessage( field, 'wrong' );
                    } else {
                        hideValidationMessage( field );
                    }
                } else {
                    hideValidationMessage( field );
                }
            }
        }

        triggerSubmit();
    });

    form.on('change', '.wpc_form_field.wpc_validate_error input', function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');

        if( $(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox' ) {
            var name = $(this).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
            if( 'undefined' == typeof $( 'input[name=' + name + ']:checked' ).val() ) {
                showValidationMessage( field, 'required' );
            } else {
                hideValidationMessage( field );
            }
        } else if( $(this).attr('type') == 'file' ) {
            if( $(this).data('required_field') && $(this).val() == '' ) {
                //if field required and empty
                showValidationMessage( field, 'required' );
            } else {
                //if field not required or required and not empty
                hideValidationMessage( field );
            }
        }

        triggerSubmit();
    });


    //focus out from text fields
    form.find('textarea').focusout( function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');

        if( $(this).data('required_field') ) {
            if ( $(this).val() == '' ) {
                //if field required and empty
                showValidationMessage( field, 'required' );
            } else {
                //if field not required or required and not empty
                hideValidationMessage( field );
            }

            triggerSubmit();
        }
    });

    form.on('keyup', '.wpc_form_field.wpc_validate_error textarea', function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');

        if( $(this).data('required_field') ) {
            if ( $(this).val() == '' ) {
                //if field required and empty
                showValidationMessage( field, 'required' );
            } else {
                //if field not required or required and not empty
                hideValidationMessage( field );
            }

            triggerSubmit();
        }
    });


    //focus out from selectbox fields
    form.find('select').focusout( function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');

        if( $(this).data('required_field') ) {
            if ( $(this).val() == '' || $(this).val() == null ) {
                //if field required and empty
                showValidationMessage( field, 'required' );
            } else {
                //if field not required or required and not empty
                hideValidationMessage( field );
            }

            triggerSubmit();
        }

    });

    form.on('change', '.wpc_form_field.wpc_validate_error select', function() {
        //check field on required value
        var field = $(this).parents('.wpc_form_field');

        if( $(this).data('required_field') ) {
            if ( $(this).val() == '' || $(this).val() == null ) {
                //if field required and empty
                showValidationMessage( field, 'required' );
            } else {
                //if field not required or required and not empty
                hideValidationMessage( field );
            }

            triggerSubmit();
        }
    });


    function showValidationMessage( field, type ) {
        field.find( '.wpc_field_validation' ).children().hide();
        field.find( '.wpc_field_' + type ).show();
        field.addClass( 'wpc_validate_error' );
    }

    function hideValidationMessage( field ) {
        field.find( '.wpc_field_validation' ).children().hide();
        field.removeClass( 'wpc_validate_error' );
    }


    function triggerSubmit() {
        if( form.find('*[data-required_field="1"]').length > 0 ) {
            var validated = 0;

            form.find('*[data-required_field="1"]').each(function () {
                if (jQuery(this).prop("tagName").toLowerCase() == 'textarea') {
                    if (jQuery(this).val() != '') {
                        //if field not required or required and not empty
                        validated++;
                    }
                } else if (jQuery(this).prop("tagName").toLowerCase() == 'select') {
                    if (!( jQuery(this).val() == '' || jQuery(this).val() == null )) {
                        //if field not required or required and not empty
                        validated++;
                    }
                } else if (jQuery(this).prop("tagName").toLowerCase() == 'input') {
                    if (jQuery(this).attr('type') == 'radio' || jQuery(this).attr('type') == 'checkbox') {
                        var name = jQuery(this).attr('name').replace(/\[/g, '\\[').replace(/\]/g, '\\]');
                        if ('undefined' != typeof jQuery('input[name=' + name + ']:checked').val()) {
                            validated++;
                        }
                    } else {
                        if (jQuery(this).val() != '') {
                            //if field not empty
                            //check field content
                            if (jQuery(this).attr('type') == 'email') {
                                var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                                if (emailReg.test($(this).val())) {
                                    validated++;
                                }
                            } else {
                                validated++;
                            }
                        }
                    }
                }
            });
            if( form.find('*[data-required_field="1"]').length == validated ) {
                form.find('input[type="submit"]').prop('disabled',false).attr('disabled',false);
            } else {
                form.find('input[type="submit"]').prop('disabled',true).attr('disabled',true);
            }
        } else {
            form.find('input[type="submit"]').prop('disabled',false).attr('disabled',false);
        }

        checkPasswordStrength(
            $('#wpc_pass1'),
            $('#wpc_pass2'),
            $('#pass-strength-result'),
            form.find('input[type="submit"]'),
            wpc_password_protect.blackList,
            true
        );

        infoSubmit();
    }

    function infoSubmit() {
        var html = '';
        if( form.find('*[data-required_field="1"]').length > 0 ) {
            form.find('*[data-required_field="1"]').each(function () {
                var label = form.find('label[for="' + jQuery(this).attr('id') + '"]').data('title');
                if (jQuery(this).prop("tagName").toLowerCase() == 'textarea') {
                    if (jQuery(this).val() == '') {
                        //if field not required or required and not empty
                        html = wpc_add_staff_var.texts.fill_field + ' "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                        return false;
                    }
                } else if (jQuery(this).prop("tagName").toLowerCase() == 'select') {
                    if ( jQuery(this).val() == '' || jQuery(this).val() == null ) {
                        //if field not required or required and not empty
                        html = wpc_add_staff_var.texts.fill_field + ' "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                        return false;
                    }
                } else if (jQuery(this).prop("tagName").toLowerCase() == 'input') {
                    if (jQuery(this).attr('type') == 'radio' || jQuery(this).attr('type') == 'checkbox') {
                        var name = jQuery(this).attr('name').replace(/\[/g, '\\[').replace(/\]/g, '\\]');
                        if ('undefined' == typeof jQuery('input[name=' + name + ']:checked').val()) {
                            html = wpc_add_staff_var.texts.fill_field + ' "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                            return false;
                        }
                    } else {
                        if (jQuery(this).val() != '') {
                            //if field not empty
                            //check field content
                            if (jQuery(this).attr('type') == 'email') {
                                var emailReg = /^([\w-'+\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                                if (!emailReg.test($(this).val())) {
                                    html = wpc_add_staff_var.texts.fill_field + ' "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                                    return false;
                                }
                            }
                        } else {
                            html = wpc_add_staff_var.texts.fill_field + ' "<a href="#' + jQuery(this).attr('id') + '">' + label + '</a>"';
                            return false;
                        }
                    }
                }
            });
        }

        if( form.find('.wpc_submit_info').html() != html ) {
            form.find('.wpc_submit_info').html(html);
        }
    }
});