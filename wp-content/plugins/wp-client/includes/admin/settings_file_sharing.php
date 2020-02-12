<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_POST['wpc_settings'] ) ) {

    $settings = $_POST['wpc_settings'];

    if ( !empty( $settings['bulk_download_zip'] ) ) {
        $settings['bulk_download_zip'] = trim( $settings['bulk_download_zip'] );
    }

    if ( isset( $settings['file_size_limit'] ) ) {
        $settings['file_size_limit'] = (int)$settings['file_size_limit'];
        $settings['file_size_limit'] = empty( $settings['file_size_limit'] ) ? '' : $settings['file_size_limit'];
    }


    WPC()->settings()->update( $settings, 'file_sharing' );
    wp_clear_scheduled_hook( 'wpc_client_ftp_synchronization' );

    if( isset( $settings['ftp_synchronize'] ) && 'yes' == $settings['ftp_synchronize'] && !empty( $settings['ftp_synchronize_period'] ) ) {
        //run CRON reminder
        $crons = WPC()->cron()->get_core_crons();
        WPC()->cron()->add_crons( $crons );
    }


    WPC()->redirect( WPC()->settings()->get_current_setting_url() . '&msg=u' );
}

$wpc_file_sharing = WPC()->get_settings( 'file_sharing' );


$section_fields = array(
    array(
        'type' => 'title',
        'label' => __( 'File Display Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'google_doc_embed',
        'type' => 'checkbox',
        'label' => __( 'Google Docs Embed', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['google_doc_embed'] ) ) ? $wpc_file_sharing['google_doc_embed'] : 'no',
        'description' => __( 'Yes, allow to view Files via Google Docs Embed', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'default_notify_checkbox',
        'type' => 'checkbox',
        'label' => __( 'Checkbox of New File Notification', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['default_notify_checkbox'] ) ) ? $wpc_file_sharing['default_notify_checkbox'] : 'no',
        'description' => sprintf( __( 'Make checkbox for sending email notification to %s about new files uploaded to be checked by default', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'nesting_category_assign',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'label' => __( 'Nesting File Category Assigns', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['nesting_category_assign'] ) ) ? $wpc_file_sharing['nesting_category_assign'] : 'no',
        'description' => sprintf( __( 'Allow %s to have access to subcategories, if they have access to parent category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'bulk_download_zip',
        'type' => 'text',
        'size' => 'small',
        'after_field' => '.zip',
        'label' => __( 'Name of Bulk Download ZIP', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['bulk_download_zip'] ) ) ? $wpc_file_sharing['bulk_download_zip'] : 'files',
        'description' => __( 'ATTENTION! Only letters without spaces are to be used', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'type' => 'title',
        'label' => __( 'File Upload Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'show_file_note',
        'type' => 'checkbox',
        'label' => __( 'File Description in Uploader', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['show_file_note'] ) ) ? $wpc_file_sharing['show_file_note'] : 'no',
        'description' => sprintf( __( 'Allow %s to write description of files in Uploader', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'allow_file_cats',
        'type' => WPC()->flags['easy_mode'] ? 'hidden' : 'checkbox',
        'label' => __( 'Category Choice in Uploader', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['allow_file_cats'] ) ) ? $wpc_file_sharing['allow_file_cats'] : 'yes',
        'description' => sprintf( __( 'Allow %s to choose file category in Uploader. If it is checked, files will be uploaded to "General" category.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'admin_uploader_type',
        'type' => 'selectbox',
        'label' => __( 'Uploader for Admin Area', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['admin_uploader_type'] ) ) ? $wpc_file_sharing['admin_uploader_type'] : 'plupload',
        'options' => array(
            'regular' => __( 'Regular', WPC_CLIENT_TEXT_DOMAIN ),
            'html5' => __( 'HTML5', WPC_CLIENT_TEXT_DOMAIN ),
            'plupload' => __( 'uberLOADER', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'description' => '<input type="hidden" id="wpc_descr_regular" value="' . __( 'Standard browser upload form', WPC_CLIENT_TEXT_DOMAIN ) . '">
                        <input type="hidden" id="wpc_descr_html5" value="' . __( 'Uploader with progress bar, allows to upload multiple files', WPC_CLIENT_TEXT_DOMAIN ) . '">
                        <input type="hidden" id="wpc_descr_plupload" value="' . __( 'Uploader with progress bar, allows to upload multiple files and large files', WPC_CLIENT_TEXT_DOMAIN ) . '">
                        <span class="description" id="wpc_uploader_admin_descr"></span>
                        <br><br>
                        <div id="wpc_uploader_admin_image" style="width:80%;"></div>',
    ),
    array(
        'id' => 'client_uploader_type',
        'type' => 'selectbox',
        'label' => sprintf( __( 'Uploader for %s Area', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
        'value' => ( isset( $wpc_file_sharing['client_uploader_type'] ) ) ? $wpc_file_sharing['client_uploader_type'] : 'plupload',
        'options' => array(
            'regular' => __( 'Regular', WPC_CLIENT_TEXT_DOMAIN ),
            'html5' => __( 'HTML5', WPC_CLIENT_TEXT_DOMAIN ),
            'plupload' => __( 'uberLOADER', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'description' => '<span class="description" id="wpc_uploader_client_descr"></span>
                        <br><br>
                        <div id="wpc_uploader_client_image" style="width:80%;"></div>',
    ),
    array(
        'id' => 'file_size_limit',
        'type' => 'text',
        'size' => 'small',
        'after_field' => __( 'Remember', WPC_CLIENT_TEXT_DOMAIN ) . ': 1M = 1024Kb',
        'label' => __( 'Max File Size (Kb)', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['file_size_limit'] ) ) ? $wpc_file_sharing['file_size_limit'] : '',
        'description' => __( 'When left blank, unlimited file size is allowed.', WPC_CLIENT_TEXT_DOMAIN ) .
                '<br>' . __( 'Note: This setting does not change your server settings. If you are experiencing issues, please try to change your server settings.', WPC_CLIENT_TEXT_DOMAIN ) .
                '<br>' . __( 'Your current server settings:', WPC_CLIENT_TEXT_DOMAIN ) .
                '<br><span class="description"><b>' . __ ( 'upload_max_filesize', WPC_CLIENT_TEXT_DOMAIN ) .
                '</b> = ' . ini_get( 'upload_max_filesize' ) . '</span>' .
                '<br><span class="description"><b>' . __ ( 'post_max_size', WPC_CLIENT_TEXT_DOMAIN ) .
                '</b> = ' . ini_get( 'post_max_size' ) . '</span>',
    ),
    array(
        'id' => 'include_extensions',
        'type' => 'textarea',
        'label' => __( 'Allow File Extensions', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['include_extensions'] ) ) ? $wpc_file_sharing['include_extensions'] : '',
        'description' => __( 'Only these file extensions can be uploaded', WPC_CLIENT_TEXT_DOMAIN ) . ' (' . __( 'comma separated for example', WPC_CLIENT_TEXT_DOMAIN ) . ': .pdf,.txt,.png)',
    ),
    array(
        'id' => 'exclude_extensions',
        'type' => 'textarea',
        'label' => __( 'Deny File Extensions', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['exclude_extensions'] ) ) ? $wpc_file_sharing['exclude_extensions'] : '',
        'description' => __( 'These file extensions cannot be uploaded', WPC_CLIENT_TEXT_DOMAIN ) . ' (' . __( 'comma separated for example', WPC_CLIENT_TEXT_DOMAIN ) . ': .pdf,.txt,.png)',
    ),
    array(
        'id' => 'attach_file_admin',
        'type' => 'checkbox',
        'label' => __( 'Attach Files to Notification', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['attach_file_admin'] ) ) ? $wpc_file_sharing['attach_file_admin'] : 'no',
        'description' => sprintf( __( 'When %s uploads new files, they are attached to email notification', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . ' ' . __( '(file size may be limited by email providers)', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'type' => 'title',
        'label' => __( 'FTP Synchronization Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'manual_sync_author',
        'type' => 'selectbox',
        'label' => __( 'Author of Manual Synchronization', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['manual_sync_author'] ) ) ? $wpc_file_sharing['manual_sync_author'] : 'sync',
        'options' => array(
            'sync' => __( 'Synchronization', WPC_CLIENT_TEXT_DOMAIN ),
            'current_user' => __( 'Current User', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'description' => '',
    ),
    array(
        'id' => 'ftp_synchronize',
        'type' => 'checkbox',
        'label' => __( 'Auto Synchronization', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['ftp_synchronize'] ) ) ? $wpc_file_sharing['ftp_synchronize'] : 'no',
        'description' => __( 'Yes, automatically synchronize files and file categories with FTP', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'ftp_synchronize_period',
        'type' => 'text',
        'size' => 'small',
        'after_field' => __( 'minutes', WPC_CLIENT_TEXT_DOMAIN ),
        'label' => __( 'Synchronization period every', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['ftp_synchronize_period'] ) ) ? $wpc_file_sharing['ftp_synchronize_period'] : '',
        'description' => '',
    ),
    array(
        'id' => 'sync_notification',
        'type' => 'checkbox',
        'label' => sprintf( __( 'Notify %s After Synchronization', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
        'value' => ( isset( $wpc_file_sharing['sync_notification'] ) ) ? $wpc_file_sharing['sync_notification'] : 'no',
        'description' => sprintf( __( 'Yes, notify %s about new files assigning via FTP Synchronization', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
    ),
    array(
        'id' => 'remote_sync',
        'type' => 'custom',
        'label' => __( 'Remote Synchronization Link', WPC_CLIENT_TEXT_DOMAIN ),
        'custom_html' => '<a href="' . add_query_arg( array( 'action'=>'wpc_client_remote_sync', 'key'=>get_option( 'wpc_client_sync_key' ) ), admin_url( 'admin-ajax.php' ) ) . '" >' . add_query_arg( array( 'action'=>'wpc_client_remote_sync', 'key'=>get_option( 'wpc_client_sync_key' ) ), admin_url( 'admin-ajax.php' ) ) . '</a>',
        'description' => __( 'You can use this link for remote synchronization via server CRON job', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'type' => 'title',
        'label' => __( 'Thumbnails Settings', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'wp_thumbnail',
        'type' => 'checkbox',
        'label' => __( 'Thumbnail Size', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['wp_thumbnail'] ) ) ? $wpc_file_sharing['wp_thumbnail'] : 'yes',
        'description' => __( 'Use WP Media settings for thumbnail size', WPC_CLIENT_TEXT_DOMAIN ),
    ),
    array(
        'id' => 'thumbnail_size_w',
        'type' => 'text',
        'size' => 'small',
        'after_field' => 'px',
        'label' => __( 'Thumbnail Width', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['thumbnail_size_w'] ) ) ? $wpc_file_sharing['thumbnail_size_w'] : '',
        'description' => '',
        'conditional' => array( 'wp_thumbnail', '=', 'no' ),
    ),
    array(
        'id' => 'thumbnail_size_h',
        'type' => 'text',
        'size' => 'small',
        'after_field' => 'px',
        'label' => __( 'Thumbnail Height', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['thumbnail_size_h'] ) ) ? $wpc_file_sharing['thumbnail_size_h'] : '',
        'description' => '',
        'conditional' => array( 'wp_thumbnail', '=', 'no' ),
    ),
    array(
        'id' => 'thumbnail_crop',
        'type' => 'checkbox',
        'label' => __( 'Crop Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
        'value' => ( isset( $wpc_file_sharing['thumbnail_crop'] ) ) ? $wpc_file_sharing['thumbnail_crop'] : 'no',
        'description' => __( 'Yes, crop thumbnails to get exact dimensions (normally thumbnails are proportional)', WPC_CLIENT_TEXT_DOMAIN ),
        'conditional' => array( 'wp_thumbnail', '=', 'no' ),
    ),
    array(
        'id' => 'thumbnail_resize_now',
        'type' => 'custom',
        'label' => __( 'Resize all thumbnails now?', WPC_CLIENT_TEXT_DOMAIN ),
        'custom_html' => '<input type="button" class="button" id="wpc_settings_thumbnail_resize_now" value="' . __( 'Resize', WPC_CLIENT_TEXT_DOMAIN ) . '" />' .
                        '<span id="resize_ajax_loader" class="wpc_ajax_loading" style="display: none;"></span>' .
                        '<span id="resize_ajax_answer" style="display: none;width: 200px;"></span>',
        'description' => '',
    ),
);

WPC()->settings()->render_settings_section( $section_fields );

?>

<script type="text/javascript">
    jQuery( document ).ready( function() {
        var plugin_url = '<?php echo WPC()->plugin_url ?>';

        jQuery( "#wpc_settings_thumbnail_resize_now" ).click( function() {
            var wp_thumbnail = jQuery( "#wpc_settings_wp_thumbnail" ).is(':checked') ? 'yes' : 'no';
            var thumbnail_size_w = jQuery( "#wpc_settings_thumbnail_size_w" ).val();
            var thumbnail_size_h = jQuery( "#wpc_settings_thumbnail_size_h" ).val();

            if ( wp_thumbnail == 'no' && ( thumbnail_size_w == '' || thumbnail_size_h == '' ) ) {
                return false;
            }

            var thumbnail_crop = jQuery( "#wpc_settings_thumbnail_crop" ).prop( "checked" );

            jQuery( '#resize_ajax_loader' ).show();
            jQuery.ajax({
                type: 'POST',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: 'action=wpc_resize_all_thumbnails&wp_thumbnail=' + wp_thumbnail + '&thumbnail_size_w=' + thumbnail_size_w + '&thumbnail_size_h=' + thumbnail_size_h + '&thumbnail_crop=' + thumbnail_crop,
                dataType: "json",
                success: function( data ){
                    jQuery( '#resize_ajax_loader' ).hide();
                    if( data.status ) {
                        jQuery( "#resize_ajax_answer" ).css('color', 'green');
                    } else {
                        jQuery( "#resize_ajax_answer" ).css('color', 'red');
                    }
                    jQuery( "#resize_ajax_answer" ).html( data.message ).fadeIn(1500);
                    setTimeout( function() {
                        jQuery( '#resize_ajax_answer' ).fadeOut(1500);
                    }, 2500 );
                }
            });
        });

        jQuery( '#wpc_settings_bulk_download_zip' ).keypress( function(e) {
            if( ( ( e.which == 0 || e.which == 8 ) && jQuery( this ).val().length > 0 ) || ( e.which > 47 && e.which < 58 ) || ( e.which > 64 && e.which < 91 ) || ( e.which > 96 && e.which < 123 ) ) {
                return true;
            }
            return false;
        });

        jQuery( '#wpc_settings_file_size_limit' ).keypress( function(e) {
            if( ( ( e.which == 0 || e.which == 8 ) && jQuery( this ).val().length > 0 ) || ( e.which > 47 && e.which < 58 ) ) {
                return true;
            }
            return false;
        });


        jQuery('#wpc_settings_admin_uploader_type').change( function() {
            var val = jQuery( this ).val();
            jQuery('#wpc_uploader_admin_descr').html( jQuery('#wpc_descr_' + val ).val() );
            jQuery('#wpc_uploader_admin_image').html( '<img src="<?php echo WPC()->plugin_url . 'images/setup_wizard/'?>' + val + '.png">' );
        });

        jQuery('#wpc_settings_client_uploader_type').on( 'change', function() {
            //return true;
            var val = jQuery( this ).val();
            jQuery('#wpc_uploader_client_descr').html( jQuery('#wpc_descr_' + val ).val() );
            jQuery('#wpc_uploader_client_image').html( '<img src="<?php echo WPC()->plugin_url . 'images/setup_wizard/'?>' + val + '.png">' );
        });

        jQuery('#wpc_settings_admin_uploader_type').trigger('change');
        jQuery('#wpc_settings_client_uploader_type').trigger('change');

    });
</script>