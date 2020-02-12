jQuery( document ).ready( function() {
    //update clientpage
    jQuery( '#wpc_update' ).click( function() {
        jQuery( '#wpc_action' ).val( 'update' );
        jQuery( '#edit_clientpage' ).submit();
        return false;
    });

    //cancel edit clientpage
    jQuery( '#wpc_cancel' ).click( function() {
        jQuery( '#wpc_action' ).val( 'cancel' );
        jQuery( '#edit_clientpage' ).submit();
        return false;
    });

    //delete clientpage
    jQuery( '#wpc_delete' ).click( function() {
        if( confirm( wpc_edit_portal_page_var.texts.delete_confirm ) ) {
            jQuery('#wpc_action').val('delete');
            jQuery('#edit_clientpage').submit();
        }
        return false;
    });
});