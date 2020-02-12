<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

$cpt_capability_map = array_merge(
    array_values( WPC()->get_post_type_caps_map( 'clientspage' ) ),
    array_values( WPC()->get_post_type_caps_map( 'portalhub' ) )
);

$cpt_capability_map = array_fill_keys( $cpt_capability_map, true );

return array(
    'wpc_manager' => array(
        'variable' => array(
            'wpc_add_clients'                   => array( 'cap' => false, 'label' => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_approve_clients'               => array( 'cap' => false, 'label' => sprintf( __( 'Approve Pending Self-Registered %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_add_staff'                     => array( 'cap' => false, 'label' => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ) ),
            'wpc_approve_staff'                 => array( 'cap' => false, 'label' => sprintf( __( 'Approve Pending Registered by %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['p'] ) ),
            'wpc_edit_clients'                  => array( 'cap' => false, 'label' => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_edit_cap_clients'              => array( 'cap' => false, 'label' => sprintf( __( 'Edit Individual Capabilities for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_archive_clients'               => array( 'cap' => false, 'label' => sprintf( __( 'Archive %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_restore_clients'               => array( 'cap' => false, 'label' => sprintf( __( 'Restore %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_delete_clients'                => array( 'cap' => false, 'label' => sprintf( __( 'Delete %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_view_client_details'           => array( 'cap' => false, 'label' => sprintf( __( 'View %s Details (Without Edit)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
            'wpc_update_client_internal_notes'  => array( 'cap' => false, 'label' => __( 'Add/Edit Internal Notes', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_client_internal_notes'    => array( 'cap' => false, 'label' => __( 'View Internal Notes (Without Edit)', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_portalhubs'               => array( 'cap' => true, 'label' => sprintf( __( "View %s's HUB Pages", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
            'wpc_add_portalhubs'                => array( 'cap' => true, 'label' => __( "Add HUB Pages", WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_edit_portalhubs'               => array( 'cap' => true, 'label' => sprintf( __( "Edit %s's HUB Pages", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ),
            'view_others_clientspages'          => array( 'cap' => true, 'label' => sprintf( __( "View %s's %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['portal_page']['p'] ) ),
            'edit_others_clientspages'          => array( 'cap' => true, 'label' => sprintf( __( "Edit %s's %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['portal_page']['p'] ) ),
            'wpc_show_portal_page_tags'         => array( 'cap' => false, 'label' => __( 'Show Portal Tags Page', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_add_portal_page_tags'          => array( 'cap' => false, 'label' => __( 'Add Portal Tags', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_delete_portal_page_tags'       => array( 'cap' => false, 'label' => __( 'Delete Portal Tags', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_email_templates'          => array( 'cap' => false, 'label' => __( "View Email Pages", WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_edit_email_templates'          => array( 'cap' => false, 'label' => __( "Edit Email Pages", WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_shortcode_templates'      => array( 'cap' => false, 'label' => __( "View Shortcode Templates", WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_edit_shortcode_templates'      => array( 'cap' => false, 'label' => __( "Edit Shortcode Templates", WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_admin_managers_files'     => array( 'cap' => false, 'label' => sprintf( __( 'View %s & %s Files', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['p'], WPC()->custom_titles['manager']['p'] ) ),
            'wpc_delete_admin_managers_files'   => array( 'cap' => false, 'label' => sprintf( __( 'Delete %s & %s Files', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['p'], WPC()->custom_titles['manager']['p'] ) ),
            'upload_files'                      => array( 'cap' => false, 'label' => __( 'Upload Media Files', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_all_file_categories'      => array( 'cap' => false, 'label' => __( 'Show All File Categories', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_file_categories'          => array( 'cap' => false, 'label' => __( 'Show File Categories Page', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_file_tags'                => array( 'cap' => false, 'label' => __( 'Show File Tags Page', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_add_file_tags'                 => array( 'cap' => false, 'label' => __( 'Add File Tags', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_delete_file_tags'              => array( 'cap' => false, 'label' => __( 'Delete File Tags', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_download_log'             => array( 'cap' => false, 'label' => __( 'Show Files Download Log', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_files_sync'               => array( 'cap' => false, 'label' => __( 'Show FTP Sync', WPC_CLIENT_TEXT_DOMAIN ) ),
            'view_privat_post_type'             => array( 'cap' => false, 'label' => __( 'View Private Post Types', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_admin_user_login'              => array( 'cap' => false, 'label' => sprintf( __( "Login in %s account", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
            'wpc_show_dashboard'                => array( 'cap' => false, 'label' => __( 'Show Dashboard', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_circles'                  => array( 'cap' => false, 'label' => sprintf( __( "Create/Manage %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ) ),
            'wpc_view_private_messages'         => array( 'cap' => true, 'label' => __( 'View Private Messages', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_show_all_private_messages'     => array( 'cap' => true, 'label' => __( 'Show All Private Messages', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_see_private_content'           => array( 'cap' => false, 'label' => __( 'Can See Content Of Any [wpc_client_private] Shortcode', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_tinymce_button'           => array( 'cap' => true, 'label' => sprintf( __( 'Can See %s button in tinyMCE', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ),
            'wpc_create_custom_fields'          => array( 'cap' => false, 'label' => __( "Create Custom Fields", WPC_CLIENT_TEXT_DOMAIN ) ),
        ),
        'permanent' => array_merge( $cpt_capability_map, array(
            'read'                          => true,
            'wpc_reset_password'            => true
        ) )
    ),
    'wpc_client' => array(
        'variable' => array(
            'wpc_delete_assigned_files' => array( 'cap' => false, 'label' => __( 'Delete Assigned Files', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_delete_uploaded_files' => array( 'cap' => true, 'label' => __( 'Delete Uploaded Files', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_reset_password'        => array( 'cap' => false, 'label' => __( 'Reset Password', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_profile'          => array( 'cap' => false, 'label' => __( 'View Profile', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_modify_profile'        => array( 'cap' => false, 'label' => __( 'Modify Profile', WPC_CLIENT_TEXT_DOMAIN ) ),
            'upload_files'              => array( 'cap' => true, 'label' => __( 'Add Media', WPC_CLIENT_TEXT_DOMAIN ) )
        ),
        'permanent' => array(
            'read'              => true,
        )
    ),
    'wpc_client_staff' => array(
        'variable' => array(
            'wpc_reset_password'        => array( 'cap' => false, 'label' => __( 'Reset Password', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_view_profile'          => array( 'cap' => false, 'label' => __( 'View Profile', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_modify_profile'        => array( 'cap' => false, 'label' => __( 'Modify Profile', WPC_CLIENT_TEXT_DOMAIN ) )
        ),
        'permanent' => array(
            'read' => true
        )
    ),
    'wpc_admin' => array(
        'variable' => array(
            'edit_posts'            => array( 'cap' => false, 'label' => __( 'Edit Posts', WPC_CLIENT_TEXT_DOMAIN ) ),
            'edit_published_posts'  => array( 'cap' => false, 'label' => __( 'Edit Published Posts', WPC_CLIENT_TEXT_DOMAIN ) ),
            'edit_others_posts'     => array( 'cap' => false, 'label' => __( 'Edit Others Posts', WPC_CLIENT_TEXT_DOMAIN ) ),
            'view_privat_post_type' => array( 'cap' => false, 'label' => __( 'View Private Post Types', WPC_CLIENT_TEXT_DOMAIN ) ),
            'wpc_admin_user_login'  => array( 'cap' => false, 'label' => sprintf( __( "Login in %s account", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ),
        ),
        'permanent' => array_merge( $cpt_capability_map, array(
            'wpc_add_clients'                   => true,
            'wpc_edit_clients'                  => true,
            'wpc_delete_clients'                => true,
            'wpc_view_client_details'           => true,
            'wpc_update_client_internal_notes'  => true,
            'wpc_view_client_internal_notes'    => true,
            'wpc_view_admin_managers_files'     => true,
            'wpc_delete_admin_managers_files'   => true,
            'wpc_delete_assigned_files'         => true,
            'wpc_view_profile'                  => true,
            'wpc_modify_profile'                => true,
            'wpc_reset_password'                => true,
            'read'                              => true,
            'upload_files'                      => true
        ) )
    )
);