jQuery( document ).ready( function() {
    //jQuery( '.wpc-hub-toolbar-dropdown .dropdown-toggle' ).dropdown();

    jQuery( '.dropdown-menu div' ).click( function() {
        jQuery(this).parents('.dropdown').find('.dropdown-menu div').removeClass( 'active' );
        jQuery( this ).addClass( 'active' );
        jQuery(this).parents( '.dropdown-menu' ).hide();
        var content_id = jQuery( this ).find( 'a.bar-link' ).attr( 'rel' );

        if ( jQuery('.wpc_ez_hub_wrapper').length > 0 ) {
            jQuery(this).parents('.wpc_ez_hub_wrapper').find( '.hub_content' ).hide();
            jQuery(this).parents('.wpc_ez_hub_wrapper').find( content_id ).show();

            jQuery(this).parents('.wpc_ez_hub_wrapper').find( '.dropdown-toggle' ).html( jQuery( this ).find( 'a.bar-link' ).html() + '<span class="caret"></span>' );
        } else {
            jQuery( '.hub_content' ).hide();
            jQuery( content_id ).show();

            jQuery( '.dropdown-toggle' ).html( jQuery( this ).find( 'a.bar-link' ).html() + '<span class="caret"></span>' );
        }

    });

    jQuery( '.dropdown' ).mouseover( function() {
        jQuery(this).find( '.dropdown-menu' ).show();
    });

    jQuery( '.dropdown, .dropdown-menu' ).mouseleave( function() {
        jQuery(this).find( '.dropdown-menu' ).hide();
    });

    if ( jQuery('.wpc_ez_hub_wrapper').length > 0 ) {
        jQuery('.wpc_ez_hub_wrapper').each(function(){
            jQuery(this).find( '.hub_content:first' ).show();
        });
    } else {
        jQuery( '.hub_content:first' ).show();
    }
});