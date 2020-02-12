<form method="post" name="edit_clientpage" id="edit_clientpage" class="wpc_form">
    <input type="hidden" name="wpc_action" id="wpc_action" value="" />
    <input type="hidden" name="wpc_wpnonce" id="wpc_wpnonce" value="<?php echo wp_create_nonce( 'wpc_edit_clientpage' . $clientpage['ID'] ) ?>" />

    <div id="message" class="wpc_notice wpc_apply" style="display: none;"></div>

    <div id="titlewrap">
        <input type="text" name="clientpage_title" autocomplete="off"  value="<?php echo ( isset( $_POST['clientpage_title'] ) ) ? $_POST['clientpage_title'] : $clientpage['post_title'] ?>" style="width: 100%;" >
    </div>

    <div class="postarea" id="postdivrich">
        <?php $clientpage_content = ( isset( $_POST['clientpage_content'] ) ) ? $_POST['clientpage_content'] : $clientpage['post_content'];
        //$show = ( current_user_can( 'wpc_add_media' ) ) ? true : false ; ,array( 'media_buttons' => $show )
        wp_editor( $clientpage_content, 'clientpage_content' ); ?>
    </div>

    <br clear="all" />
    <br clear="all" />
    <div>
       <input type="submit" name="" id="wpc_update" class="wpc_submit" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
       <input type="button" name="" id="wpc_cancel" class="wpc_button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
       <input type="button" name="" id="wpc_delete" class="wpc_button" value="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
    </div>
</form>