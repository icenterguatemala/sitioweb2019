<?php

$wpc_import_export = new WPC_Import_Export();

?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <h2></h2>

    <?php echo $wpc_import_export->render_block() ?>

</div>