<?php
/**
 * Template Name: Private Messages: One Chain Content
 * Template Description: This template for [wpc_client_com] shortcode. Chain content.
 * Template Tags: Private Messages
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/messages/chain.php.
 *
 * HOWEVER, on occasion WP-Client will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 	WP-Client
 */

//needs for translation
__( 'Private Messages: One Chain Content', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_com] shortcode. Chain content.', WPC_CLIENT_TEXT_DOMAIN );
__( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_msg_chain_subject">
    <div class="wpc_msg_chain_subject_text"><?php echo $subject; ?></div>

    <div class="wpc_msg_refresh_button" data-object="chain" data-chain_id="<?php echo $chain_id; ?>" title="<?php _e( 'Refresh', WPC_CLIENT_TEXT_DOMAIN ); ?>">
        <div class="wpc_msg_refresh_image"></div>
    </div>

    <div class="wpc_msg_collapse_button" title="<?php _e( 'Expand All', WPC_CLIENT_TEXT_DOMAIN ); ?>" data-alt_title="<?php _e( 'Collapse All', WPC_CLIENT_TEXT_DOMAIN ); ?>">
        <div class="wpc_msg_expand_image"></div>
    </div>
</div>

<?php foreach( $messages as $k => $message ) {
    $message_line_class = !(count($messages) <= 4 || (count($messages) > 4 && ($k == 0 || $k == count($messages) - 1))) ? 'wpc_msg_for_hidden' : '';

    ?>

    <div class="wpc_msg_message_line <?php echo $message_line_class; ?>" data-message_id="<?php echo $message['id']; ?>">

        <div class="wpc_msg_avatar">
            <?php echo $message['avatar']; ?>
        </div>

        <div class="wpc_msg_line_content">
            <div class="wpc_msg_author_date">
                <div class="wpc_msg_message_author"><?php echo $message['author']; ?></div>
                <div class="wpc_msg_message_date"><?php echo $message['date']; ?></div>
            </div>

            <div class="wpc_msg_message_content"><?php echo $message['content']; ?></div>
        </div>
    </div>

    <?php if ( count( $messages ) > 4 && $k == 0) { ?>
        <div class="wpc_expand_older_messages">
            <?php _e( 'Show', WPC_CLIENT_TEXT_DOMAIN ); ?>
            <span class="wpc_expand_count"><?php echo ( count( $messages ) - 2 ); ?></span>
            <?php _e( 'Older Messages', WPC_CLIENT_TEXT_DOMAIN ); ?>
        </div>
    <?php }
} ?>

<?php if ( $hide_reply ) { ?>

    <div class="wpc_msg_answer_actions">
        <input type="button" class="wpc_msg_back_answer wpc_button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
    </div>

<?php } else { ?>

    <div class="wpc_msg_chain_answer">
        <div class="wpc_msg_avatar">
            <?php echo $avatar ?>
        </div>

        <div class="wpc_msg_answer_field">

            <?php if ( $show_cc_email ) { ?>
                <div class="wpc_answer_line">
                    <input type="text" class="wpc_msg_answer_cc_email" name="answer[cc_email]" value="" placeholder="<?php _e( 'CC Email', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                    <span class="wpc_description"><?php _e( 'Add an email address here to copy them once on the initial message', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                </div>
            <?php } ?>

            <?php echo $answer_message_textarea; ?>
        </div>

        <div class="wpc_msg_answer_actions">
            <input type="button" data-chain_id="<?php echo $chain_id; ?>" class="wpc_msg_send_answer wpc_button" value="<?php _e( 'Send', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
            <input type="button" class="wpc_msg_back_answer wpc_button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
            <span class="wpc_ajax_loading"></span>
        </div>
    </div>

<?php } ?>