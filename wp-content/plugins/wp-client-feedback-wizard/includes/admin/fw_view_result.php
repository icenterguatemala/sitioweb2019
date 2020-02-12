<?php

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && !current_user_can( 'wpc_show_feedback_results' ) ) {
    $this->redirect_available_page();
}

global $wpdb;

if ( isset( $_GET['result_id'] ) && 0 < $_GET['result_id'] ) {
    $result_id = $_GET['result_id'];
} else {
    wp_redirect( add_query_arg( array( 'page' => 'wpclients_feedback_wizard'), 'admin.php' ) );
    exit;
}

$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_results WHERE result_id = %d", $result_id ), ARRAY_A );

//Set date format
if ( get_option( 'date_format' ) ) {
    $date_format = get_option( 'date_format' );
} else {
    $date_format = 'm/d/Y';
}
if ( get_option( 'time_format' ) ) {
    $time_format = get_option( 'time_format' );
} else {
    $time_format = 'g:i:s A';
}




?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo $this->gen_feedback_tabs_menu() ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block wpc_fw_result_view" style="position: relative;">
            <h2><?php _e( 'Result For:', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
            <?php echo stripslashes( $result['result_text'] ) ?>
        </div>
    </div>


</div>