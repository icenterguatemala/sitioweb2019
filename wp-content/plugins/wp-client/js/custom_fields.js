jQuery(document).ready(function() {

    //validation
    /*jQuery( 'input[type="submit"]:not(#loginform #wp-submit)' ).click( function() {
        var error = false;

        jQuery( '.wpc_text_required, .wpc_datepicker_required, .wpc_cost_required, .wpc_textarea_required, .wpc_sbox_required, .wpc_msbox_required' ).each( function() {
            if( '' == jQuery( this ).val() || null == jQuery( this ).val() ) {
                jQuery( this ).addClass( 'wpc_error' );
                error = true
            } else {
                jQuery( this ).removeClass( 'wpc_error' );
            }
        });

        jQuery( '.wpc_radio_required, .wpc_checkbox_required' ).each( function() {
            var name = jQuery( this ).attr( 'name' ).replace( /\[/g, '\\[').replace( /\]/g, '\\]') ;
            if( 'undefined' == typeof jQuery( 'input[name=' + name + ']:checked' ).val() ) {
                jQuery( this ).parent().addClass( 'wpc_error' );
                error = true
            } else {
                jQuery( this ).parent().removeClass( 'wpc_error' );
            }
        });

        if( error )
            return false;
    });


    jQuery('body').on('keyup', '.wpc_text_required, .wpc_datepicker_required, .wpc_cost_required, .wpc_textarea_required', function() {
        if( '' != jQuery( this ).val() ) {
            jQuery( this ).removeClass( 'wpc_error' );
        } else {
            jQuery( this ).addClass( 'wpc_error' );
        }
    });*/

    jQuery('.wpc_cf_numbers_only').mask('#', {clearIfNotMatch: true});

    jQuery('.wpc_cf_text.wpc_field_mask_date').mask('00/00/0000', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_time').mask('00:00:00', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_datetime').mask('00/00/0000 00:00:00', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_zip_code').mask('00000-000', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_zip_code2').mask('0-00-00-00', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_phone').mask('0000-0000', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_phone2').mask('(00) 0000-0000', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_phone3').mask('(000) 000-0000', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_cpf').mask('000.000.000-00', {reverse: true, clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_money').mask("#.##0,00", {reverse: true, clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_ip').mask('099.099.099.099', {clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_percent').mask('#0,00%', {reverse: true, clearIfNotMatch: true});
    jQuery('.wpc_cf_text.wpc_field_mask_custom').each(function() {
        var mask = jQuery(this).data('mask-value');
        jQuery(this).mask( mask, {clearIfNotMatch: true, reverse: jQuery(this).hasClass('wpc_field_mask_reverse') } );
    });

});