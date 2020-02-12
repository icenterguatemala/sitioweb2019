<?php
/**
 * Template Name: Private Messages: Common Block
 * Template Description: This template for [wpc_client_com] shortcode. Main template.
 * Template Tags: Private Messages
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/messages/common.php.
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
__( 'Private Messages: Common Block', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_client_com] shortcode. Main template.', WPC_CLIENT_TEXT_DOMAIN );
__( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_private_messages_shortcode">

    <div class="wpc_msg_nav_wrapper">

        <input type="button" class="wpc_msg_new_message_button wpc_button" value="<?php _e( 'New', WPC_CLIENT_TEXT_DOMAIN ); ?>" title="<?php _e( 'New Message', WPC_CLIENT_TEXT_DOMAIN ); ?>"/>

        <div class="wpc_msg_nav_list_wrapper">

            <div class="wpc_msg_nav_list_collapsed"></div>

            <ul class="wpc_msg_nav_list">
                <li class="wpc_nav_button inbox" data-list="inbox"><?php _e( 'Inbox', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                <li class="wpc_nav_button sent" data-list="sent"><?php _e( 'Sent', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                <li class="wpc_nav_button archive" data-list="archive"><?php _e( 'Archive', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                <li class="wpc_nav_button trash" data-list="trash"><?php _e( 'Trash', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
            </ul>
        </div>


    </div>

    <div class="wpc_msg_content_wrapper">

        <div class="wpc_msg_top_nav_wrapper">

            <div class="wpc_msg_active_filters_wrapper"></div>

            <div class="wpc_msg_controls_line">
                <div class="wpc_msg_bulk_all">
                    <input type="checkbox" name="wpc_msg_bulk_check" class="wpc_msg_bulk_check" title="<?php _e( 'Select All on this page', WPC_CLIENT_TEXT_DOMAIN ); ?>" />

                    <div class="wpc_msg_bulk_actions_wrapper">
                        <ul class="wpc_msg_bulk_select">
                            <li data-select="all"><?php _e( 'Select All', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-select="all_page"><?php _e( 'Select All on this page', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-select="none"><?php _e( 'Unselect All', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-select="read"><?php _e( 'Select Read', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-select="unread"><?php _e( 'Select Unread', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                        </ul>

                        <hr style="clear:both;"/>

                        <ul class="wpc_msg_bulk_actions">
                            <li data-action="read"><?php _e( 'Mark As Read', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-action="archive"><?php _e( 'Move To Archive', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-action="delete"><?php _e( 'Move To Trash', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-action="restore"><?php _e( 'Restore', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                            <li data-action="leave"><?php _e( 'Leave Chain', WPC_CLIENT_TEXT_DOMAIN ); ?></li>
                        </ul>
                    </div>
                </div>

                <?php if ( $show_filters ) { ?>

                    <div class="wpc_msg_filter">
                        <?php _e( 'Filters', WPC_CLIENT_TEXT_DOMAIN ); ?>

                        <div class="wpc_msg_filter_wrapper">
                            <label><?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ); ?>:
                                <br />
                                <select class="wpc_msg_filter_by">
                                    <option value="member"><?php _e( 'Member', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                                    <option value="date"><?php _e( 'Date', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                                </select>
                            </label>

                            <div class="wpc_ajax_content">

                                <div class="wpc_loading_overflow">
                                    <div class="wpc_small_ajax_loading"></div>
                                </div>

                                <div class="wpc_overflow_content">
                                    <div class="wpc_msg_filter_selectors"></div>
                                    <input type="button" value="<?php _e( 'Apply Filter', WPC_CLIENT_TEXT_DOMAIN ); ?>" class="wpc_msg_add_filter wpc_button">
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>

                <div class="wpc_msg_search_line">
                    <div class="wpc_msg_search_button" title="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                        <div class="wpc_msg_search_image"></div>
                    </div>

                    <input type="text" name="wpc_msg_search" class="wpc_msg_search wpc_text" placeholder="<?php _e( 'Search in messages', WPC_CLIENT_TEXT_DOMAIN ); ?>"/>
                </div>

            </div>

            <div class="wpc_msg_pagination" data-pagenumber="1">

                <div class="wpc_msg_pagination_text">
                    <strong><span class="start_count"></span> - <span class="end_count"></span></strong> of <strong><span class="total_count"></span></strong>
                </div>

                <div class="wpc_msg_pagination_buttons">
                    <div class="wpc_msg_next_button disabled" title="<?php _e( 'Newer', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                        <div class="wpc_msg_next_image"></div>
                    </div>

                    <div class="wpc_msg_prev_button" title="<?php _e( 'Older', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                        <div class="wpc_msg_prev_image"></div>
                    </div>
                </div>

                <div class="wpc_msg_refresh_button" data-object="chains" title="<?php _e( 'Refresh', WPC_CLIENT_TEXT_DOMAIN ); ?>">
                    <div class="wpc_msg_refresh_image"></div>
                </div>

            </div>

        </div>

        <div class="wpc_msg_content_wrapper_inner"></div>

        <div class="wpc_msg_chain_content"></div>

        <div class="wpc_msg_add_new_wrapper">

            <form action="" method="post" name="wpc_new_message_form" class="wpc_new_message_form">

                <div class="wpc_msg_new_message_line">

                    <div class="wpc_msg_new_message_label">
                        <label for="new_message_to"><?php _e( 'To', WPC_CLIENT_TEXT_DOMAIN ); ?><span class="wpc_required">&nbsp;*</span></label>
                    </div>

                    <div class="wpc_msg_new_message_field">
                        <select class="new_message_to wpc_selectbox" name="new_message[to][]" placeholder="<?php _e( 'Select Members', WPC_CLIENT_TEXT_DOMAIN ); ?>" multiple>

                            <?php if ( ! empty( $to_users['wpc_client'] ) ) { ?>

                                <optgroup label="<?php echo WPC()->custom_titles['client']['p']; ?>" data-single_title="<?php echo WPC()->custom_titles['client']['p']; ?>" data-color="#0073aa">
                                    <?php foreach( $to_users['wpc_client'] as $user ) { ?>
                                        <option value="<?php echo $user->ID; ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login; ?></option>
                                    <?php } ?>
                                </optgroup>

                            <?php } ?>

                            <?php if ( ! empty( $to_users['wpc_client_staff'] ) ) { ?>

                                <optgroup label="<?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['p']; ?>" data-single_title="<?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['s']; ?>" data-color="#2da3dc">
                                    <?php foreach( $to_users['wpc_client_staff'] as $user ) { ?>
                                        <option value="<?php echo $user->ID; ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login; ?></option>
                                    <?php } ?>
                                </optgroup>

                            <?php } ?>

                            <?php if( !empty( $to_users['wpc_managers'] ) ) { ?>

                                <optgroup label="<?php echo WPC()->custom_titles['manager']['p']; ?>" data-single_title="<?php echo WPC()->custom_titles['manager']['p']; ?>" data-color="#dc832d">
                                    <?php foreach( $to_users['wpc_managers'] as $user ) { ?>
                                        <option value="<?php echo $user->ID; ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login; ?></option>
                                    <?php } ?>
                                </optgroup>

                            <?php } ?>

                            <?php if( !empty( $to_users['admins'] ) ) { ?>

                                <optgroup label="<?php _e( 'Admins', WPC_CLIENT_TEXT_DOMAIN ); ?>" data-single_title="<?php _e( 'Admins', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-color="#b63ad0">
                                    <?php foreach( $to_users['admins'] as $user ) { ?>
                                        <option value="<?php echo $user->ID; ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login; ?></option>
                                    <?php } ?>
                                </optgroup>

                            <?php } ?>

                        </select>

                        <span class="wpc_description"><?php _e( 'Select one member will create a Dialogue, selecting multiple members will create separate message threads', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </div>
                </div>

                <div class="wpc_msg_new_message_line" style="display: none;">
                    <div class="wpc_msg_new_message_label">
                        <label for="new_message_cc">
                            <?php _e( 'CC Members', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </label>
                    </div>

                    <div class="wpc_msg_new_message_field">
                        <select class="new_message_cc wpc_selectbox" name="new_message[cc][]" placeholder="<?php _e( 'Select CC Members', WPC_CLIENT_TEXT_DOMAIN ); ?>" multiple ></select>
                        <span class="wpc_description"><?php _e( 'You can add more members to a Dialogue to create a Group Dialogue', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </div>
                </div>


                <?php if ( $show_cc_email ) { ?>

                    <div class="wpc_msg_new_message_line">
                        <div class="wpc_msg_new_message_label">
                            <label for="new_message_cc_email"><?php _e( 'CC Email', WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                        </div>

                        <div class="wpc_msg_new_message_field">
                            <input class="new_message_cc_email wpc_text" type="text" name="new_message[cc_email]" value="" placeholder="<?php _e( 'CC Email', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                            <span class="wpc_description"><?php _e( 'Add an email address here to copy them once on the initial message', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                        </div>
                    </div>

                <?php } ?>

                <div class="wpc_msg_new_message_line">
                    <div class="wpc_msg_new_message_label">
                        <label for="new_message_subject">
                            <?php _e( 'Subject', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            <span class="wpc_required">&nbsp;*</span>
                        </label>
                    </div>

                    <div class="wpc_msg_new_message_field">
                        <input type="text" class="new_message_subject wpc_text" name="new_message[subject]" value="" placeholder="<?php _e( 'Message Subject', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                    </div>
                </div>

                <div class="wpc_msg_new_message_line">
                    <div class="wpc_msg_new_message_label">
                        <label for="new_message_content">
                            <?php _e( 'Message', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            <span class="wpc_required">&nbsp;*</span>
                        </label>
                    </div>

                    <div class="wpc_msg_new_message_field"><?php echo $new_message_textarea; ?></div>
                </div>

                <div class="wpc_msg_new_message_line">
                    <div class="wpc_msg_new_message_label">&nbsp;</div>

                    <div class="wpc_msg_new_message_field">
                        <input type="button" class="wpc_msg_send_new_message wpc_button" value="<?php _e( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                        <input type="button" class="wpc_msg_back_new_message wpc_button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                    </div>
                </div>

            </form>
        </div>

        <div class="wpc_ajax_overflow"><div class="wpc_ajax_loading"></div></div>
    </div>
</div>