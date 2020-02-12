<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//create custom post type
WPC_Hooks_Pre_Loads::_create_post_type();

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