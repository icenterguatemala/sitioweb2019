<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
if( isset( $wpc_clients_staff['registration_using_terms'] ) && $wpc_clients_staff['registration_using_terms'] == 'yes' ) {
    $wpc_clients_staff['using_terms'] = 'yes';
    $wpc_clients_staff['using_terms_form'] = array('registration');
} else {
    $wpc_clients_staff['using_terms'] = 'no';
}
WPC()->settings()->update( $wpc_clients_staff, 'clients_staff' );