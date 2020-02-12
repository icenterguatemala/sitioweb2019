<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


$wpc_gateways = WPC()->get_settings( 'gateways' );
if ( !empty( $wpc_gateways['allowed'] ) && in_array( 'stripe', $wpc_gateways['allowed'] ) ) {
    //for Stripe update lib
    $new_notices['stripe_update_lib_2017_06_05'] =
        'Stripe API library was updated to the latest version 2017-06-05. Please login to your Stripe account and verify that you are running the latest Stripe API version.' .
        '<br> See more details <a target="_blank" href="https://dashboard.stripe.com/account/apikeys">here</a>.';

    WPC()->admin()->add_wpc_notices( $new_notices );
}

global $wp_roles;

$capability_map = array_merge(
    array_values( WPC()->get_post_type_caps_map( 'clientspage' ) ),
    array_values( WPC()->get_post_type_caps_map( 'portalhub' ) )
);

//set capability for Portal Pages to Admin
foreach ( $capability_map as $capability ) {
    $wp_roles->add_cap( 'administrator', $capability );
    $wp_roles->add_cap( 'wpc_admin', $capability );
    $wp_roles->add_cap( 'wpc_manager', $capability );
}

//update rewrite rules
WPC()->reset_rewrite_rules();