<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


?>
<div class="wrap">

    <style type="text/css">

        .wpc_slider_notice {
            display: none;
        }

    </style>

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <h2><?php _e( 'Update Crashed!', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

    <h4>
        <?php _e( 'Unfortunately, in the process of updating the plugin to a newer version has errors. Please contact plugin support.', WPC_CLIENT_TEXT_DOMAIN ) ?>
    </h4>

    <div class="wpc_clear"></div>

    <br />
    <br />
    <br />


    <h4><?php _e( 'System Status', WPC_CLIENT_TEXT_DOMAIN ) ?></h4>
    <?php

    include_once( WPC()->plugin_dir . 'includes/admin/dashboard_system_status.php' );

    ?>

</div>