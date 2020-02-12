jQuery(document).ready(function() {

    jQuery('body').on( 'click', '.wpc-video-popup', function() {
        var video_src   = jQuery( this ).data( 'src' );
        var video_title = jQuery( this ).data( 'title' );
        var videoId     = jQuery( this ).data( 'id' );
        jQuery(this).shutter_box({
            view_type       : 'lightbox',
            width           : '600px',
            height          : '390px',
            type            : 'ajax',
            dataType        : 'json',
            href            : wp.ajax.settings.url,
            ajax_data       : "action=wpc_watch_video_in_popup&video_src=" + video_src + "&video_title=" + video_title + "&id=" + videoId,
            setAjaxResponse : function( data ) {
                jQuery( '.sb_lightbox_content_title' ).html( data.title );
                jQuery( '.sb_lightbox_content_body' ).html( data.content );
            }
        });
        jQuery(this).shutter_box('show');
    });

});
