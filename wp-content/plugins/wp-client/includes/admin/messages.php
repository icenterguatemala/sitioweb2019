<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$count_sent = 0;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=private_messages';
}


if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        // Mark as Read
        case 'mark':
            if ( isset( $_GET['id'] )  ) {
                $user_id = get_current_user_id();
                check_admin_referer( 'wpc_message_read' .  $_GET['id'] . $user_id );

                $messages = $wpdb->get_results( $wpdb->prepare(
                    "SELECT *
                    FROM {$wpdb->prefix}wpc_client_messages
                    WHERE chain_id=%d",
                    $_GET['id']
                ), ARRAY_A );

                if( isset( $messages ) && !empty( $messages ) ) {
                    $client_new_messages = WPC()->assigns()->get_assign_data_by_assign( 'new_message', 'client', $user_id );

                    foreach( $messages as $key=>$message ) {
                        //if there are new messages
                        if( is_array( $client_new_messages ) && in_array( $message['id'], $client_new_messages ) ) {
                            WPC()->assigns()->delete_object_by_assign( 'new_message', $message['id'], 'client', $user_id );
                        }
                    }
                }

                WPC()->redirect( add_query_arg( 'msg', 'r', $redirect ) );
            } else {
                WPC()->redirect( $redirect );
            }
            break;
    }
}

if( isset( $_GET['read_reply'] ) && !empty( $_GET['read_reply'] ) ) {
    $current_user_id = get_current_user_id();

    $user_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

    if( empty( $user_chains ) || !in_array( $_GET['read_reply'], $user_chains ) ) {
        WPC()->redirect( $redirect );
    }
}


if( isset( $_POST['new_message'] ) && !empty( $_POST['new_message'] ) ) {
    //create new message
    if( !( isset( $_POST['new_message']['to'] ) && !empty( $_POST['new_message']['to'] ) ) ) {
        WPC()->redirect( add_query_arg( array( 'msg' => 'te' ), $redirect ) );
    }

    if( !( isset( $_POST['new_message']['subject'] ) && !empty( $_POST['new_message']['subject'] ) ) ) {
        WPC()->redirect( add_query_arg( array( 'msg' => 'se' ), $redirect ) );
    }

    if( !( isset( $_POST['new_message']['content'] ) && !empty( $_POST['new_message']['content'] ) ) ) {
        WPC()->redirect( add_query_arg( array( 'msg' => 'ce' ), $redirect ) );
    }

    if( isset( $_POST['new_message']['cc_email'] ) && !empty( $_POST['new_message']['cc_email'] ) && !is_email( $_POST['new_message']['cc_email'] ) ) {
        WPC()->redirect( add_query_arg( array( 'msg' => 'wcc' ), $redirect ) );
    }

    if( !( isset( $_POST['new_message']['cc'] ) && !empty( $_POST['new_message']['cc'] ) ) ) {
        $_POST['new_message']['cc'] = array();
    }

    foreach( $_POST['new_message']['to'] as $to ) {

        if( strpos( $to, 'circle_' ) !== false ) {
            $circle_id = str_replace( 'circle_', '', $to );
            $client_ids = WPC()->groups()->get_group_clients_id( $circle_id );

            if( !empty( $client_ids ) ) {
                foreach( $client_ids as $client_id ) {
                    $args = array(
                        'to'        => array( $client_id ),
                        'subject'   => stripslashes( $_POST['new_message']['subject'] ),
                        'content'   => stripslashes( $_POST['new_message']['content'] ),
                        'cc'        => $_POST['new_message']['cc']
                    );

                    WPC()->private_messages()->private_messages_create_chain( $args );
                }
            }
        } else {
            $args = array(
                'to'        => array( $to ),
                'subject'   => stripslashes( $_POST['new_message']['subject'] ),
                'content'   => stripslashes( $_POST['new_message']['content'] ),
                'cc'        => $_POST['new_message']['cc']
            );
            WPC()->private_messages()->private_messages_create_chain( $args );
        }
    }

    $wpc_private_messages = WPC()->get_settings( 'private_messages' );
    if( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) {
        if( isset( $_POST['new_message']['cc_email'] ) && is_email( $_POST['new_message']['cc_email'] ) ) {
            $author = get_userdata( get_current_user_id() );

            $args = array(
                'user_name' => $author->get( 'user_login' ),
                'message'   => nl2br( htmlspecialchars( stripslashes( $_POST['new_message']['content'] ) ) ),
                'subject'   => nl2br( htmlspecialchars( stripslashes( $_POST['new_message']['subject'] ) ) )
            );
            WPC()->mail( 'notify_cc_about_message', $_POST['new_message']['cc_email'], $args, 'notify_cc_about_message' );
        }
    }

    WPC()->redirect( add_query_arg( 'msg', 'ms', $redirect ) );
}


$to_users = WPC()->private_messages()->private_messages_build_to_list();

$count_to_users = 0;
if( isset($to_users['wpc_client']) ) {
    $count_to_users += count( $to_users['wpc_client'] );
}
if( isset($to_users['wpc_circles']) ) {
    $count_to_users += count( $to_users['wpc_circles'] );
}
if( isset($to_users['wpc_client_staff']) ) {
    $count_to_users += count( $to_users['wpc_client_staff'] );
}
if( isset($to_users['wpc_managers']) ) {
    $count_to_users += count( $to_users['wpc_managers'] );
}
if( isset($to_users['admins']) ) {
    $count_to_users += count( $to_users['admins'] );
} ?>

<script type="text/javascript">
    var ajax_url = '<?php echo get_admin_url() ?>admin-ajax.php';
    var client_id = '<?php echo get_current_user_id() ?>';
    var _wpnonce = '<?php echo wp_create_nonce( get_current_user_id() . "client_security" ) ?>';
    var texts = {
        'error_to':'<?php echo esc_js( __( '"To" field is required!', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'error_subject':'<?php echo esc_js( __( 'Subject is required!', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'error_content':'<?php echo esc_js( __( 'Message is required!', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'error_cc_email':'<?php echo esc_js( __( 'CC Email must be an email address!', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'read':'<?php echo esc_js( __( 'Message(s) was read', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'archived':'<?php echo esc_js( __( 'Message(s) was archived', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'trashed':'<?php echo esc_js( __( 'Message(s) was trashed', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'leaved':'<?php echo esc_js( __( 'Message(s) was leaved', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'deleted':'<?php echo esc_js( __( 'Message(s) was deleted permanently', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'restored':'<?php echo esc_js( __( 'Message(s) was restored', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'send_message':'<?php echo esc_js( __( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'send_messages':'<?php echo esc_js( __( 'Send Separate Messages', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
        'group_dialog':'<?php echo esc_js( __( 'Create Group Dialogue', WPC_CLIENT_TEXT_DOMAIN ) ) ?>'
    }
</script>

<div class="wrap">
    <?php echo WPC()->admin()->get_plugin_logo_block(); ?>
    <div class="wpc_clear"></div>
    <?php if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'te':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Please select members and try again', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'se':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Subject cannot be empty', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'ce':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Content cannot be empty.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'wcc':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'CC Email must be an email address.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'r':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Message was read.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'ms':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Message was sent.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
        }
    } ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">
        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>
        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">
            <!--     Back To Top       -->
            <a href="#new_message" class="back-to-top" title="<?php _e( 'Top', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                <span class="back-to-top-image"></span>
            </a>

            <!--     Messages Navigation       -->
            <div class="wpc_msg_nav_wrapper">
                <?php if( $count_to_users > 0 ) { ?>
                    <div id="new_message" class="wpc_msg_new_message_button">
                        <?php _e( 'New Message', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </div>
                <?php } ?>
                <div class="wpc_msg_nav_list_wrapper">
                    <ul class="wpc_msg_nav_list">
                        <?php if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_show_all_private_messages' ) ) { ?>
                            <li class="nav_button all" data-list="all"><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                        <?php } ?>
                        <li class="nav_button inbox" data-list="inbox"><?php _e( 'Inbox', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                        <li class="nav_button sent" data-list="sent"><?php _e( 'Sent', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                        <li class="nav_button archive" data-list="archive"><?php _e( 'Archive', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                        <li class="nav_button trash" data-list="trash"><?php _e( 'Trash', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                    </ul>
                </div>
            </div>

            <div class="wpc_msg_content_wrapper">
                <!--Chain's Lists Navigation-->
                <div class="wpc_msg_top_nav_wrapper">
                    <div class="wpc_msg_active_filters_wrapper"></div>
                    <div class="wpc_msg_controls_line">
                        <div class="wpc_msg_bulk_all">
                            <input type="checkbox" name="wpc_msg_bulk_check" class="wpc_msg_bulk_check" title="<?php _e( 'Select All on this page', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                            <div class="wpc_msg_bulk_actions_wrapper">
                                <ul class="bulk_select">
                                    <li data-select="all"><?php _e( 'Select All', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-select="all_page"><?php _e( 'Select All on this Page', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-select="none"><?php _e( 'Unselect All', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-select="read"><?php _e( 'Select Read', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-select="unread"><?php _e( 'Select Unread', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                </ul>
                                <hr style="clear:both;"/>
                                <ul class="bulk_actions">
                                    <li data-action="read"><?php _e( 'Mark As Read', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-action="archive"><?php _e( 'Move to Archive', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-action="delete"><?php _e( 'Move to Trash', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-action="restore"><?php _e( 'Restore', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <li data-action="leave"><?php _e( 'Leave Chain', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <?php if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) ) { ?>
                                        <li data-action="delete_permanently"><?php _e( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) ?></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>

                        <div class="wpc_msg_filter">
                            <?php _e( 'Filters', WPC_CLIENT_TEXT_DOMAIN ) ?>

                            <div class="wpc_msg_filter_wrapper">
                                <label style="float: left;width:100%;"><?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ) ?>:<br />
                                    <select style="float: left;width:100%;" class="wpc_msg_filter_by">
                                        <option value="member"><?php _e( 'Member', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="date"><?php _e( 'Date', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </label>

                                <div class="wpc_ajax_content">

                                    <div class="wpc_loading_overflow">
                                        <div class="wpc_small_ajax_loading"></div>
                                    </div>

                                    <div class="wpc_overflow_content">
                                        <div class="wpc_msg_filter_selectors"></div>
                                        <input type="button" value="<?php _e( 'Apply Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_msg_add_filter button-primary">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="wpc_msg_search_line">
                            <div class="wpc_msg_search_button" title="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                <div class="wpc_msg_search_image"></div>
                            </div>
                            <input type="text" name="wpc_msg_search" class="wpc_msg_search" placeholder="<?php _e( 'Search in messages', WPC_CLIENT_TEXT_DOMAIN ) ?>"/>
                        </div>
                    </div>

                    <div class="wpc_msg_pagination" data-pagenumber="1">
                        <div class="wpc_msg_refresh_button" data-object="chains" title="<?php _e( 'Refresh', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                            <div class="wpc_msg_refresh_image"></div>
                        </div>
                        <div class="wpc_msg_pagination_buttons">
                            <div class="wpc_msg_next_button disabled" title="<?php _e( 'Newer', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                <div class="wpc_msg_next_image"></div>
                            </div>
                            <div class="wpc_msg_prev_button" title="<?php _e( 'Older', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                <div class="wpc_msg_prev_image"></div>
                            </div>
                        </div>
                        <div class="wpc_msg_pagination_text">
                            <strong><span class="start_count"></span> - <span class="end_count"></span></strong> of <strong><span class="total_count"></span></strong>
                        </div>
                    </div>

                </div>

                <!--Chain's Lists Content-->
                <div class="wpc_msg_content_wrapper_inner"></div>

                <div class="wpc_msg_chain_content"></div>

                <?php if( $count_to_users > 0 ) {
                    $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                    $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login'; ?>
                    <!--New message form-->
                    <div class="add_new_message">
                        <form action="" method="post" name="wpc_new_message_form" id="wpc_new_message_form">
                            <div class="new_message_line">
                                <div class="new_message_label"><label for="new_message_to"><?php _e( 'To', WPC_CLIENT_TEXT_DOMAIN ) ?><span style="color: red;">&nbsp;*</span></label></div>
                                <div class="new_message_field">
                                    <select id="new_message_to" style="width:100%;" name="new_message[to][]" placeholder="<?php _e( 'Select Members', WPC_CLIENT_TEXT_DOMAIN ) ?>" multiple>
                                        <?php if( isset( $to_users['wpc_client'] ) && !empty( $to_users['wpc_client'] ) ) { ?>
                                            <optgroup label="<?php echo WPC()->custom_titles['client']['p'] ?>" data-single_title="<?php echo WPC()->custom_titles['client']['s'] ?>" data-color="#0073aa">
                                                <?php foreach( $to_users['wpc_client'] as $user ) { ?>
                                                    <option value="<?php echo $user->ID ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ?></option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php }

                                        if( isset( $to_users['wpc_circles'] ) && !empty( $to_users['wpc_circles'] ) ) { ?>
                                            <optgroup label="<?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>" data-single_title="<?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['s'] ?>" data-color="#7ad03a">
                                                <?php foreach( $to_users['wpc_circles'] as $circle ) { ?>
                                                    <option value="circle_<?php echo $circle['group_id'] ?>"><?php echo $circle['group_name'] ?></option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php }

                                        if( isset( $to_users['wpc_client_staff'] ) && !empty( $to_users['wpc_client_staff'] ) ) { ?>
                                            <optgroup label="<?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['p'] ?>" data-single_title="<?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['s'] ?>" data-color="#2da3dc">
                                                <?php foreach( $to_users['wpc_client_staff'] as $user ) {
                                                    $staff_client_id = get_user_meta( $user->ID, 'parent_client_id', true );
                                                    $staff_client = get_user_by('id', $staff_client_id); ?>
                                                    <option value="<?php echo $user->ID ?>"><?php echo ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . ' (' . ( !empty( $staff_client->$display_name ) ? $staff_client->$display_name : $staff_client->user_login ) . ' ' . WPC()->custom_titles['client']['s'] . ')' ?></option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php }

                                        if( isset( $to_users['wpc_managers'] ) && !empty( $to_users['wpc_managers'] ) ) { ?>
                                            <optgroup label="<?php echo WPC()->custom_titles['manager']['p'] ?>" data-single_title="<?php echo WPC()->custom_titles['manager']['s'] ?>" data-color="#dc832d">
                                                <?php foreach( $to_users['wpc_managers'] as $user ) { ?>
                                                    <option value="<?php echo $user->ID ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ?></option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php }

                                        if( isset( $to_users['admins'] ) && !empty( $to_users['admins'] ) ) { ?>
                                            <optgroup label="<?php _e( 'Admins', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-single_title="<?php _e( 'Admin', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-color="#b63ad0">
                                                <?php foreach( $to_users['admins'] as $user ) { ?>
                                                    <option value="<?php echo $user->ID ?>"><?php echo !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ?></option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php } ?>
                                    </select>
                                    <span class="description"><?php _e( 'Select one member will create a Dialogue, selecting multiple members will create separate message threads', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                </div>
                            </div>
                            <div class="new_message_line" style="display: none;">
                                <div class="new_message_label">
                                    <label for="new_message_cc">
                                        <?php _e( 'Group Members', WPC_CLIENT_TEXT_DOMAIN ); ?>
                                        <span style="vertical-align: middle !important;">&nbsp;
                                        <?php echo WPC()->admin()->tooltip( sprintf( __( 'Use this field to add additional users to the Dialogue. Group Members will be able to see replies from other users in the Group Dialogue. Only users who are "related" to the main message recipient can be selected in this field (Ex: a %s assigned %s or %s)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['manager']['s'], WPC()->custom_titles['staff']['s'] ) ); ?>
                                        </span>
                                    </label>
                                </div>
                                <div class="new_message_field">
                                    <select id="new_message_cc" style="width:100%;" name="new_message[cc][]" placeholder="<?php _e( 'Select Group Members', WPC_CLIENT_TEXT_DOMAIN ) ?>" multiple></select>
                                    <span class="description"><?php _e( 'You can add more members to a Dialogue to create a Group Dialogue', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                </div>
                            </div>
                            <?php $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                            if( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) { ?>
                                <div class="new_message_line">
                                    <div class="new_message_label"><label for="new_message_cc_email"><?php _e( 'CC Email', WPC_CLIENT_TEXT_DOMAIN ) ?></label></div>
                                    <div class="new_message_field">
                                        <input type="text" id="new_message_cc_email" style="width:100%;" name="new_message[cc_email]" value="" placeholder="<?php _e( 'CC Email', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                        <span class="description"><?php _e( 'Add an email address here to copy them once on the initial message', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="new_message_line">
                                <div class="new_message_label"><label for="new_message_subject"><?php _e( 'Subject', WPC_CLIENT_TEXT_DOMAIN ) ?><span style="color: red;">&nbsp;*</span></label></div>
                                <div class="new_message_field"><input type="text" id="new_message_subject" style="width:100%;" name="new_message[subject]" value="" placeholder="<?php _e( 'Message Subject', WPC_CLIENT_TEXT_DOMAIN ) ?>" /></div>
                            </div>
                            <div class="new_message_line">
                                <div class="new_message_label"><label for="new_message_content"><?php _e( 'Message', WPC_CLIENT_TEXT_DOMAIN ) ?><span style="color: red;">&nbsp;*</span></label></div>
                                <div class="new_message_field"><textarea id="new_message_content" name="new_message[content]" style="width:100%; height:100px;" placeholder="<?php _e( 'Type your private message here', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea></div>
                            </div>
                            <div class="new_message_line">
                                <div class="new_message_label">&nbsp;</div>
                                <div class="new_message_field">
                                    <input type="submit" id="send_new_message" class="button-primary" value="<?php _e( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                    <input type="button" id="back_new_message" class="button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                </div>
                            </div>
                        </form>
                    </div>
                <?php } ?>

                <!--Overflow AJAX loading-->
                <div class="wpc_ajax_overflow"><div class="wpc_ajax_loading"></div></div>
            </div>
        </div>
    </div>
</div>