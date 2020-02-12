<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

ob_start();
global $post, $wpdb, $current_user;

if ( !ini_get( 'safe_mode' ) ) {
    @set_time_limit(0);
}

$wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
$include_extensions = !empty( $wpc_file_sharing['include_extensions'] ) ? $wpc_file_sharing['include_extensions'] : '';
if ( !empty( $include_extensions ) ) {
    $include_extensions = strtolower( $include_extensions );
    $extesions_array = explode( ',', $include_extensions );
    $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array);
    $extesions_array = array_filter($extesions_array, function($el){
        return !empty($el);
    });
    $include_extensions = implode( ',', $extesions_array );
}

$exclude_extensions = !empty( $wpc_file_sharing['exclude_extensions'] ) ? $wpc_file_sharing['exclude_extensions'] : '';
if ( !empty( $exclude_extensions ) ) {
    $exclude_extensions = strtolower($exclude_extensions);
    $extesions_array = explode( ',', $exclude_extensions );
    $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array);
    $extesions_array = array_filter($extesions_array, function($el){
        return !empty($el);
    });
    $exclude_extensions = implode( ',', $extesions_array );
} ?>

<div class="wpc_client_upload_form">

<?php
if ( is_multisite() && !is_upload_space_available() ) {
    echo '<p>' . __( 'Sorry, you have used all of your storage quota.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>';
} else {
    $uploader_id = rand( 0, 10000 );

    if ( isset( $wpc_file_sharing['client_uploader_type'] ) && 'html5' == $wpc_file_sharing['client_uploader_type'] ) { ?>
        <div class="wpc_uploader_successful wpc_notice wpc_apply"><?php _e( 'File was uploaded successfully!', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
        <div class="wpc_uploader_warning wpc_notice wpc_warning"></div>
        <form enctype="multipart/form-data" method="post">

            <?php $include_ext = $include_extensions;
            if ( !empty( $atts['include'] ) ) {
                $extesions_array = explode( ',', strtolower($atts['include']) );
                $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array );
                $extesions_array = array_filter($extesions_array, function($el){
                    return !empty($el);
                });
                $include_ext = implode( ',', $extesions_array );
            }

            $exclude_ext = $exclude_extensions;
            if ( !empty( $atts['exclude'] ) ) {
                $extesions_array = explode( ',', strtolower($atts['exclude']) );
                $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array );
                $extesions_array = array_filter($extesions_array, function($el){
                    return !empty($el);
                });
                $exclude_ext = implode( ',', $extesions_array );
            }

            $client_id  = WPC()->checking_page_access();
            $nonce = wp_create_nonce( $include_ext . $exclude_ext . $client_id );
            ?>

            <input type="hidden" name="include_ext" id="include_ext_<?php echo $uploader_id ?>" value="<?php echo $include_ext ?>">
            <input type="hidden" name="exclude_ext" id="exclude_ext_<?php echo $uploader_id ?>" value="<?php echo $exclude_ext ?>">
            <input type="hidden" name="verify_nonce" id="plupload_nonce" value="<?php echo $nonce?>">

            <?php echo WPC()->files()->build_uploader_category_selectbox( $client_id, $uploader_id, $atts ); ?>

            <div class="wpc_queue_wrapper <?php if( isset( $atts['auto_upload'] ) && 'yes' == $atts['auto_upload'] ) { ?>wpc_autoupload_files<?php } ?>">
                <div id="queue_<?php echo $uploader_id ?>" class="wpc_uploadifive_queue"></div>
                <input id="wpc_file_upload_<?php echo $uploader_id ?>" class="wpc_file_upload" name="Filedata" data-form_id="<?php echo $uploader_id ?>" type="file" multiple="multiple">
            </div>

            <?php if( !( isset( $atts['auto_upload'] ) && 'yes' == $atts['auto_upload'] ) ) { ?>
                <input type="button" class="wpc_button wpc_start_upload" id="wpc_start_upload_<?php echo $uploader_id ?>" onclick="javascript:jQuery( '#wpc_file_upload_<?php echo $uploader_id ?>' ).uploadifive('upload');" value="<?php _e( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ) ?>" disabled />
            <?php } ?>
        </form>
    <?php } elseif( isset( $wpc_file_sharing['client_uploader_type'] ) && 'plupload' == $wpc_file_sharing['client_uploader_type'] ) { ?>
        <div class="wpc_uploader_message wpc_notice"></div>

        <?php if( !empty( $include_extensions ) || !empty( $exclude_extensions ) ||
            !empty( $atts['include'] ) || !empty( $atts['exclude'] ) ) {

            $include_array = ( isset( $atts['include'] ) && '' != $atts['include'] ) ? strtolower($atts['include']) : $include_extensions;
            $include_array = ( '' != $include_array ) ? explode( ',', $include_array ) : array();
            $include_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $include_array);

            $exclude_array = ( isset( $atts['exclude'] ) && '' != $atts['exclude'] ) ? strtolower($atts['exclude']) : $exclude_extensions;
            $exclude_array = ( '' != $exclude_array ) ? explode( ',', $exclude_array ) : array();
            $exclude_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $exclude_array);

            if( !empty( $include_extensions ) || ( isset( $atts['include'] ) && '' != $atts['include'] ) ) {
                if( is_array( $exclude_array ) && 0 < count( $exclude_array ) ) {
                    $include_array = array_diff( $include_array, $exclude_array );
                }

                $text = sprintf( __( 'You can upload only %s files', WPC_CLIENT_TEXT_DOMAIN ), implode( ', ', $include_array ) );
            } elseif( ( !empty( $exclude_extensions ) || ( isset( $atts['exclude'] ) && '' != $atts['exclude'] ) ) && !( !empty( $include_extensions ) || ( isset( $atts['include'] ) && '' != $atts['include'] ) ) ) {
                $text = sprintf( __( '%s files cannot be uploaded', WPC_CLIENT_TEXT_DOMAIN ), implode( ', ', $exclude_array ) );
            }

            if( isset( $text ) && !empty( $text ) ) { ?>
                <div class="wpc_uploader_warning wpc_notice wpc_warning"><?php echo $text ?></div>
            <?php }
        }

        $include_ext = $include_extensions;
        if ( !empty( $atts['include'] ) ) {
            $extesions_array = explode( ',', strtolower($atts['include']) );
            $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array );
            $extesions_array = array_filter($extesions_array, function($el){
                return !empty($el);
            });
            $include_ext = implode( ',', $extesions_array );
        }

        $exclude_ext = $exclude_extensions;
        if ( !empty( $atts['exclude'] ) ) {
            $extesions_array = explode( ',', strtolower($atts['exclude']) );
            $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array );
            $extesions_array = array_filter($extesions_array, function($el){
                return !empty($el);
            });
            $exclude_ext = implode( ',', $extesions_array );
        }

        $client_id  = WPC()->checking_page_access();
        $nonce = wp_create_nonce( $include_ext . $exclude_ext . $client_id );
        ?>

        <form enctype="multipart/form-data" method="post">
            <input type="hidden" name="include_ext" id="include_ext_<?php echo $uploader_id ?>" value="<?php echo $include_ext ?>">
            <input type="hidden" name="exclude_ext" id="exclude_ext_<?php echo $uploader_id ?>" value="<?php echo $exclude_ext ?>">
            <input type="hidden" name="verify_nonce" id="plupload_nonce" value="<?php echo $nonce?>">

            <?php echo WPC()->files()->build_uploader_category_selectbox( $client_id, $uploader_id, $atts ); ?>

            <div class="wpc_queue_wrapper">
                <div id="queue_<?php echo $uploader_id ?>" class="wpc_plupload_queue" data-form_id="<?php echo $uploader_id ?>">
                    <p><?php _e( "Your browser doesn't have Flash, Silverlight or HTML5 support.", WPC_CLIENT_TEXT_DOMAIN ) ?></p>
                </div>
            </div>
        </form>
    <?php } else {
        //Regular uploader
        if ( isset( $_GET['msg'] ) && 'success' == $_GET['msg'] ) { ?>
            <div class="wpc_uploader_successful wpc_notice wpc_apply"><?php _e( 'File was uploaded successfully!', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
        <?php }

        if ( isset( $msg ) && !empty( $msg ) ) { ?>
            <br />
            <p class="wpc_notice wpc_error"><?php echo $msg ?></p>
        <?php } ?>

        <div class="wpc_uploader_warning wpc_notice wpc_warning"><?php _e( 'File will be not uploaded', WPC_CLIENT_TEXT_DOMAIN ) ?></div>

        <form action="" method="post" enctype="multipart/form-data">
            <?php $include_ext = $include_extensions;
            if ( !empty( $atts['include'] ) ) {
                $extesions_array = explode( ',', strtolower($atts['include']) );
                $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array );
                $extesions_array = array_filter($extesions_array, function($el){
                    return !empty($el);
                });
                $include_ext = implode( ',', $extesions_array );
            }

            $exclude_ext = $exclude_extensions;
            if ( !empty( $atts['exclude'] ) ) {
                $extesions_array = explode( ',', strtolower($atts['exclude']) );
                $extesions_array = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $extesions_array );
                $extesions_array = array_filter($extesions_array, function($el){
                    return !empty($el);
                });
                $exclude_ext = implode( ',', $extesions_array );
            }

            $client_id  = WPC()->checking_page_access();
            $nonce = wp_create_nonce( $include_ext . $exclude_ext . $client_id );
            ?>
            <input type="hidden" name="include_ext" id="include_ext" value="<?php echo $include_ext ?>">
            <input type="hidden" name="exclude_ext" id="exclude_ext" value="<?php echo $exclude_ext ?>">
            <input type="hidden" name="verify_nonce" id="plupload_nonce" value="<?php echo $nonce?>">
            <input type="hidden" name="wpc_auto_upload" id="wpc_auto_upload" value="<?php echo ( isset( $atts['auto_upload'] ) && '' != $atts['auto_upload'] ) ? $atts['auto_upload'] : '' ?>">

            <?php echo WPC()->files()->build_uploader_category_selectbox( $client_id, $uploader_id, $atts ); ?>

            <input type="file" name="file" id="file" />
            <?php if( isset( $wpc_file_sharing['show_file_note'] ) && 'yes' == $wpc_file_sharing['show_file_note'] ) { ?>
                <textarea class="note_field" name="note" rows="5" cols="50" placeholder="<?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea>
            <?php } ?>

            <input type="submit" class="wpc_button wpc_start_upload" value="<?php _e( 'Upload File', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="b[upload]" id="uploader_submit" <?php if( isset( $atts['auto_upload'] ) && 'yes' == $atts['auto_upload'] ) { ?>style="display: none;"<?php } ?> />
        </form>
    <?php }
} ?>
</div>

<?php $out2 = ob_get_contents();
if( ob_get_length() ) {
    ob_end_clean();
}
return $out2;