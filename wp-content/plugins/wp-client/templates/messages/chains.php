<?php
/**
 * Template Name: Private Messages: Chains list
 * Template Description: This template for [wpc_client_com] shortcode. Chains list.
 * Template Tags: Private Messages
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/messages/chains.php.
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
__( 'Private Messages: Chains list', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_com] shortcode. Chains list.', WPC_CLIENT_TEXT_DOMAIN );
__( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<table class="wpc_private_messages_table">
    <tbody>

        <?php if ( !empty( $chains ) ) {
            foreach( $chains as $chain ) {
               $new_message_class = $chain['is_new'] == 'true' ? 'wpc_msg_new_message' : '';

                ?>

                <tr>
                    <th class="wpc_msg_check-column">
                        <input type="checkbox" class="wpc_msg_item" name="item[]" value="<?php echo $chain['c_id']; ?>" data-new="<?php echo $chain['is_new']; ?>" />
                    </th>
                    <td class="wpc_msg_column-client_ids <?php echo $new_message_class; ?>" >
                        <span class="wpc_messages_count" title="<?php _e( 'Messages in chain', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                            <?php echo $chain['messages_count'] > 1 ? $chain['messages_count'] : ''; ?>
                        </span>
                        <span class="wpc_chain_members" title="<?php echo $chain['members_title']; ?>"><?php echo $chain['members']; ?></span>
                    </td>
                    <td class="wpc_msg_column-message_text">
                        <span class="wpc_chain_subject <?php echo $new_message_class; ?>">
                            <?php echo $chain['subject']; ?>
                        </span>
                        <span class="wpc_chain_last_message"><?php echo $chain['content']; ?></span>
                    </td>
                    <td class="wpc_msg_column-date">
                        <span class="<?php echo $new_message_class; ?>">
                            <?php echo $chain['date']; ?>
                        </span>
                    </td>
                </tr>

            <?php }
        } else { ?>

            <tr class="wpc_msg_no-items">
                <td colspan="4">
                    <?php _e( 'No Messages', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </td>
            </tr>

        <?php } ?>

    </tbody>
</table>