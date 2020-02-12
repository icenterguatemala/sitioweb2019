<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_login_alerts = WPC()->get_settings( 'login_alerts' );
$wpc_email_templates = WPC()->get_settings( 'templates_emails' );

if( isset( $wpc_login_alerts['successful'] ) && '1' == $wpc_login_alerts['successful'] ) {
    $wpc_email_templates['la_login_successful']['enable'] = '1';
} else {
    $wpc_email_templates['la_login_successful']['enable'] = '0';
}

if( isset( $wpc_login_alerts['failed'] ) && '1' == $wpc_login_alerts['failed'] ) {
    $wpc_email_templates['la_login_failed']['enable'] = '1';
} else {
    $wpc_email_templates['la_login_failed']['enable'] = '0';
}

WPC()->settings()->update( $wpc_email_templates, 'templates_emails' );