<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
$wpc_general = WPC()->get_settings( 'general' );
$wpc_custom_login = WPC()->get_settings( 'custom_login' );

?>
<table width="70%" style="float: left;">
    <tr>
        <td valign="top">
            <table class="wc_status_table widefat" cellspacing="0">

                <thead>
                    <tr>
                        <th colspan="2"><?php _e( 'General Information', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    </tr>
                </thead>

            </table>

        </td>

    </tr>
</table>