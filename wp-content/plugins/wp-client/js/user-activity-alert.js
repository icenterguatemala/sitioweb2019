jQuery(document).ready(function($) {
    var css = $('#wpc_activity_popup').attr('style');
    $('#wpc_activity_popup').animate({
        opacity: 1
    }, 2000, function() {
        $('#wpc_activity_popup').attr('style', css + ' opacity: 1;');
    });

    $('.wpc_close_button').click(function() {
        $('#wpc_activity_popup').animate({
            opacity: 0
        }, 1000, function() {
            $('#wpc_activity_popup').remove();
        });
        jQuery.ajax({
            type: "POST",
            url: wpc_activity.admin_url + "admin-ajax.php",
            data: {
                action : 'wpc_client_update_last_activity',
                security : wpc_activity.security
            }
        });
    });
});