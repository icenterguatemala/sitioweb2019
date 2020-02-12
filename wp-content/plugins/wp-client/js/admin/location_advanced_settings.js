jQuery(document).ready( function($) {
    var i = -1;

    $( '#wpc_advanced_settings table tbody' ).each( function() {
        i++;
        //removing class for assign popups
        $(this).find('.wpc_assign_popup_initialized').removeClass('wpc_assign_popup_initialized');
        
        //copy content
        $('#menu-locations-table .menu-locations-row').not('.wpc_advanced_locations').eq(i).after(
           $( this ).html()
        );
           
    });

    //remove content
    $( '#wpc_advanced_settings' ).remove();
//    if ( 'wpNavMenu' in window && 'initManageLocations' in wpNavMenu ) {
//        wpNavMenu.initManageLocations();        
//    }
    
    
    //Delete action
    $( '#menu-locations-table' ).on( 'click', '.wpc_delete_line', function() {
        $( this ).closest( 'tr' ).remove();
    });
    
    //Another Circles
    $( '#menu-locations-table' ).on( 'click', '.wpc_clone_line', function() {
        var tr;
        var line;
        var action_assign;
        var location;
        var numeric_i;
        var new_id;
        var old_id;

        tr = $( this ).closest( 'tr' );
        line = tr.clone();
        
        //removing class for assign popups
        line.find('.wpc_assign_popup_initialized').removeClass('wpc_assign_popup_initialized');
        
        //removing similar action link 'Another Circles'
        line.find('.wpc_clone_line').parent('span').remove();
        
        //add Delete action
        line.find('.locations-row-links>span:last').css( 'display', 'inline' );
        
        action_assign = line.find('.wpc_fancybox_link');
        
        //change class for delete separate
//        line.find('.locations-edit-menu-link:last').removeClass('locations-edit-menu-link').addClass('locations-add-menu-link');
        
        location = action_assign.data('location');
        old_id = action_assign.data('input');
        numeric_i = action_assign.data( 'numeric_' + location );
        new_id = action_assign.data('input').replace(/[0-9]/g,'')
        do {
            numeric_i++;
        } while( 0 != $( '#' + new_id + numeric_i ).length );
        new_id += numeric_i;
        
        //change some attributes for new assign popup
        action_assign.data('input', new_id );
        line.find('.counter_' + old_id ).removeClass('counter_' + old_id ).addClass('counter_' + new_id );
        line.find('#' + old_id ).prop('id', new_id);
        
        //reset count of selected circles
        line.find('.circles_field').prop( 'value', '' ).next().text( '(0)' );
        
        //clone line
        tr.after( line );

    });
});
