<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

//set plugin roles

/*our_hook_
    hook_name: wpc_client_our_roles_array
    hook_title: Array of our roles keys
    hook_description: Can be used for filter our roles keys.
    hook_type: filter
    hook_in: wp-client
    hook_location
    hook_param: array $role_keys
    hook_since: 4.5.0
*/
return apply_filters( 'wpc_client_our_roles_array', array(
    'wpc_client',
    'wpc_client_staff',
    'wpc_manager',
    'wpc_admin'
    ) );