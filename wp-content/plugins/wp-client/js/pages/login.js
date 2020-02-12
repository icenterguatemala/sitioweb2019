jQuery( document ).ready( function( $ ) {
    jQuery( "#wp-submit" ).on( 'click', function() {
        var form = jQuery(this).parents('form');

        var msg = '';
        if ( form.find( "input#terms_agree" ).length > 0 && !form.find( "input#terms_agree" ).is(':checked') ) {
            msg += wpc_login_var.texts.empty_terms + "<br/>";
        }
        if ( form.find( "input#privacy_agree" ).length > 0 && !form.find( "input#privacy_agree" ).is(':checked') ) {
            msg += wpc_login_var.texts.empty_privacy + "<br/>";
        }

        if ( msg != '' ) {
            jQuery(this).parents('form').find('.wpc_notice.wpc_error').remove();
            jQuery(this).parents('form').prepend('<p class="wpc_notice wpc_error">' + msg + '</p>');
            return false;
        }
    });

    //password strength block
    $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

    $( 'body' ).on( 'keyup', '#pass1, #pass2',
        function() {
            checkPasswordStrength(
                $('#pass1'),
                $('#pass2'),
                $('#pass-strength-result'),
                $('#wp-submit'),
                wpc_password_protect.blackList
            );
        }
    );

    wpc_generate_password( '#pass1', '#pass2' );
});