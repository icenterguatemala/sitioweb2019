<?php
/**
 * Template Name: Staff Directory
 * Template Description: This template for [wpc_staff_directory] shortcode
 * Template Tags: Users
 *
 * This template can be overridden by copying it to your_current_theme/wp-client/staff_directory.php.
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
__( 'Staff Directory', WPC_CLIENT_TEXT_DOMAIN );
__( 'This template for [wpc_staff_directory] shortcode', WPC_CLIENT_TEXT_DOMAIN );
__( 'Users', WPC_CLIENT_TEXT_DOMAIN );

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpc_staff_directory">
    <div id="message" class="wpc_notice <?php echo $message_class; ?>" style="<?php echo empty( $message ) ? 'display: none;' : ''; ?> " >
        <?php echo !empty( $message ) ? $message : ''; ?>
    </div>

    <div class="wpc_staff_directory_hub_link">
        <?php echo do_shortcode( '[wpc_client_get_page_link page="hub" text="HUB Page" /]' ); ?>
        <?php if ( !empty( $add_staff_link ) ) { ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo $add_staff_link; ?>"><?php printf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?></a>
        <?php } ?>
    </div>

    <table class="wpc_staff_directory_table wpc_table">
        <thead>
            <tr>
                <th class="wpc_staff_login_th wpc_primary_column"><?php echo WPC()->custom_titles['staff']['s']; ?></th>
                <th class="wpc_staff_first_name_th"><?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                <th class="wpc_staff_email_th"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                <th class="wpc_staff_status_th"><?php _e( 'Status', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                <?php foreach( $custom_fields as $k => $custom_field ) { ?>
                    <th class="<?php echo $k ?>_th"><?php echo $custom_field['title']; ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach( $staffs as $staff ) { ?>
                <tr class="wpc_staff_line">
                    <td class="wpc_staff_login_td wpc_primary_column">
                        <div class="wpc_show_details"></div>
                        <strong><?php echo $staff['user_login']; ?></strong>
                        <span class="wpc_staff_actions">
                            <a class="wpc_staff_action_edit" href="<?php echo $staff['edit_link']; ?>"><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ); ?></a> |
                            <a class="wpc_staff_action_delete" onclick="return confirm('<?php printf( __( 'Are you sure to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?>');" href="<?php echo $staff['delete_link']; ?>"><?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?></a>
                        </span>
                    </td>
                    <td class="wpc_staff_first_name_td" data-wpc_colname="<?php _e( 'First Name', WPC_CLIENT_TEXT_DOMAIN ); ?>:"><?php echo $staff['first_name']; ?></td>
                    <td class="wpc_staff_email_td" data-wpc_colname="<?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); ?>:"><?php echo $staff['user_email']; ?></td>
                    <td class="wpc_staff_status_td" data-wpc_colname="<?php _e( 'Status', WPC_CLIENT_TEXT_DOMAIN ); ?>:">
                        <?php if ( 1 == $staff['to_approve'] ) {
                            _e( 'Waiting for approval', WPC_CLIENT_TEXT_DOMAIN );
                        } else {
                            _e( 'Approved', WPC_CLIENT_TEXT_DOMAIN );
                        } ?>
                    </td>
                    <?php foreach( $custom_fields as $k => $custom_field ) { ?>
                        <td class="<?php echo $k; ?>_td" data-wpc_colname="<?php echo $custom_field['title']; ?>:"><?php echo $staff[$k]; ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>