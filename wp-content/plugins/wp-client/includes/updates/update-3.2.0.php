<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//for very old installs

include_once WPC()->plugin_dir . 'includes/class.old_update.php';

$wpc_client_old_update = new WPC_Old_Update();
$wpc_client_old_update->updating( $ver );