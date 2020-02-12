<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_save_profiles = WPC()->get_all_email_profiles();


WPC()->mailer()->load_email_senders();

$types = array(
    '' => __( 'Default WP', WPC_CLIENT_TEXT_DOMAIN ),
    'smtp' => __( 'SMTP', WPC_CLIENT_TEXT_DOMAIN ),
);
foreach ( (array)WPC()->mailer()->senders as $code => $plugin ) {
    $types[ $code ] = !empty( $plugin[1] ) ? $plugin[1] : $code;
}

?>

</form>

<div class="wpc_tab_container_block">

    <h2><?php _e( 'Email Sending Settings', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

    <table>
        <tr>
            <td>
                <?php _e( 'Sending Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>
                <?php echo WPC()->admin()->tooltip( sprintf( __( 'Select Email Profile for sending %s email notifications', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ) ?>
            </td>
            <td>
                <?php
                    $core_profile = WPC()->get_settings( 'email_sending_profile_for_core' );
                    if ( empty( $core_profile ) ) {
                        $core_profile = '';
                    }
                ?>
                <select id="wpc_email_profile_core">
                    <option value="" <?php selected( empty($core_profile), true ) ?>> &mdash;
                        <?php _e( 'Without Email Profile', WPC_CLIENT_TEXT_DOMAIN ) ?> &mdash;
                    </option>

                    <?php
                    echo '<optgroup label="' . __( 'Profiles', WPC_CLIENT_TEXT_DOMAIN ) . '">';
                        foreach ( $wpc_save_profiles as $key => $value ) {
                            echo '<option value="' . $key . '" ' . selected( $key, $core_profile ) . '>';
                                echo $value['profile_name'];
                                if ( isset( $value['type_name'] ) ) {
                                    echo ' ( ' . $value['type_name'] . ' )';
                                }
                            echo '</option>';
                        }
                    echo '</optgroup>';
                    ?>
                </select>
                <div id="ajax_loading_for_select" style="display: inline;"><span class="ajax_loading"></span></div>
            </td>
        </tr>
    </table>
    <br>
    <br>

    <h3><?php _e( 'Email Profiles', WPC_CLIENT_TEXT_DOMAIN ) ?></h3>
    <form method="post" name="wpc_main_form" id="wpc_main_form" >

        <div class="adding_block">

            <select id="wpc_first_set" style="width: 130px;">
                <option value="-1" selected="selected"><?php _e( 'Select Method', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                <?php foreach ( $types as $key => $name ) { ?>
                    <option value="<?php echo $key; ?>">
                        <?php echo $name; ?>
                    </option>
                <?php } ?>
            </select>

            <input type="button" class="button" id="wpc_add_item" value="<?php _e( 'Add Profile', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
            <span id="wpc_block_message"></span>
        </div>

        <div id="wpc_save_items">

            <ul id="wpc_list">
                <?php

                foreach( $wpc_save_profiles as $key => $value ) {
                    echo '<li data-id="' . $key . '">';
                        echo '<div class="dashicons dashicons-no-alt"></div>';
                        echo $value['profile_name'];
                        $type = !empty( $value['type'] ) ? $value['type'] : '';
                        if ( isset( $types[ $type ] ) ) {
                            echo ' ( ' . $types[ $type ] . ' )';
                        }
                    echo '</li>';
                }
                ?>
            </ul>


        </div>

        <div id="wpc_main_container">

            <!--Settings block-->
            <div id="wpc_all_settings">
            </div>

            <!--Test Email block-->
            <div id="wpc_email_testing_block">
                <hr />
                <br />
                <a id="wpc_show_test_link"><b><?php _e( 'Send Test Email', WPC_CLIENT_TEXT_DOMAIN ); ?><span> >></span></b></a>
                <div id="wpc_hide_block">
                    <label>
                        <?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); echo WPC()->red_star(); ?>:<br />
                        <input type="text" name="email" id="test_email" value="" />
                    </label>
                    <br />
                    <label>
                        <?php _e( 'Subject', WPC_CLIENT_TEXT_DOMAIN ); ?>:<br />
                        <input type="text" name="subject" id="test_subject" value="" />
                    </label>
                    <br />
                    <label>
                        <?php _e( 'Message', WPC_CLIENT_TEXT_DOMAIN ); ?>:<br />
                        <textarea cols="50" rows="5" name="message" id="test_message"></textarea>
                    </label>
                    <br />
                    <p>
                        <input type="button" class="button" id="wpc_send_test_email" value="<?php _e( 'Send Test Message', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    </p>
                    <p>
                        <span class="wpc_ajax_loading" style="margin: 0 0 0 7px; display: none;" id="login_page_id_loading"></span>
                        <div id="ajax_result" style="display: inline;"></div>
                    </p>
                </div>
            </div>

            <!--Overflow AJAX loading-->
            <div class="wpc_ajax_overflow"><div class="wpc_table_ajax_loading"></div></div>

        </div>

        <input type="hidden" name="wpc_ajax_key" value="<?php echo wp_create_nonce( get_current_user_id() . 'approve_ajax' ) ?>" />

    </form>

</div>


<form style="display: none;" >

<?php
    wp_enqueue_script( 'wpc_email_sending', false, array(), WPC_CLIENT_VER, true );

    $wpc_email_sending_data = array(
        'unknown_text' => __( 'Unknown error', WPC_CLIENT_TEXT_DOMAIN ),
        'sure_delete_text' => __( 'Are you sure you want to Delete this Email Profile?', WPC_CLIENT_TEXT_DOMAIN ),
        'wp_nonce' => wp_create_nonce( get_current_user_id() . SECURE_AUTH_SALT . "wpc_send_test_email" ),
        'admin_url' => get_admin_url(),
    );

    wp_localize_script( 'wpc_email_sending', 'wpc_email_sending_data', $wpc_email_sending_data );

?>
