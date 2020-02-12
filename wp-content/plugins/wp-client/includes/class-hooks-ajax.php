<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Hooks_Ajax' ) ) :

class WPC_Hooks_Ajax {

    /**
    * constructor
    **/
    function __construct() {
        
        //ajax actions
        add_action( 'wp_ajax_wpc_view_client', array( &$this, 'ajax_view_client' ) );
        add_action( 'wp_ajax_wpc_approve_client', array( &$this, 'ajax_approve_client' ) );
        add_action( 'wp_ajax_wpc_get_client_internal_notes', array( &$this, 'ajax_get_client_internal_notes' ) );
        add_action( 'wp_ajax_wpc_update_client_internal_notes', array( &$this, 'ajax_update_client_internal_notes' ) );
        add_action( 'wp_ajax_wpc_get_user_capabilities', array( &$this, 'ajax_get_user_capabilities' ) );
        add_action( 'wp_ajax_wpc_update_capabilities', array( &$this, 'ajax_update_capabilities' ) );
        add_action( 'wp_ajax_wpc_file_edit_form', array( &$this, 'ajax_file_edit_form' ) );
        add_action( 'wp_ajax_wpc_check_page_shortcode', array( &$this, 'ajax_check_page_shortcode' ) );

        add_action( 'wp_ajax_wpc_portal_pages_update_order', array( &$this, 'ajax_portal_pages_update_order' ) );
        add_action( 'wp_ajax_wpc_files_update_order', array( &$this, 'ajax_files_update_order' ) );

        add_action( 'wp_ajax_change_cat_order', array( &$this, 'ajax_change_cat_order' ) );
        add_action( 'wp_ajax_change_custom_field_order', array( &$this, 'ajax_change_custom_field_order' ) );
        add_action( 'wp_ajax_get_all_groups', array( &$this, 'ajax_get_all_groups' ) );
        add_action( 'wp_ajax_get_name', array( &$this, 'ajax_get_name' ) );

        //Get html for create/edit circle
        add_action( 'wp_ajax_get_data_circle', array( &$this, 'ajax_get_data_circle' ) );
        //Get html for create/edit PP category
        add_action( 'wp_ajax_get_data_pp_category', array( &$this, 'ajax_get_data_pp_category' ) );
        //Get html for delete PP category
        add_action( 'wp_ajax_get_html_delete_pp_category', array( &$this, 'ajax_get_html_delete_pp_category' ) );
        //Set Default for Custom Fields
        add_action( 'wp_ajax_wpc_custom_field_set_value', array( $this, 'ajax_custom_field_set_value' ) );

        //ajax - get all file tags
        add_action( 'wp_ajax_wpc_get_all_file_tags', array( &$this, 'ajax_get_all_file_tags' ) );
        add_action( 'wp_ajax_wpc_get_all_tags', array( &$this, 'ajax_get_all_tags' ) );

        add_action( 'wp_ajax_wpc_paste_portal_page_content', array( &$this, 'ajax_paste_portal_page_content' ) );

        //ajax - get all tags for file_id
        //add_action( 'wp_ajax_wpc_get_file_tags', array( &$this, 'ajax_get_file_tags' ) );


        //client upload files
        add_action( 'wp_ajax_nopriv_wpc_client_upload_files', array( &$this, 'ajax_upload_files' ) );
        add_action( 'wp_ajax_wpc_client_upload_files', array( &$this, 'ajax_upload_files' ) );

        add_action( 'wp_ajax_nopriv_wpc_client_plupload_upload_files', array( &$this, 'ajax_plupload_upload_files' ) );
        add_action( 'wp_ajax_wpc_client_plupload_upload_files', array( &$this, 'ajax_plupload_upload_files' ) );


        //admin upload files
        add_action( 'wp_ajax_nopriv_wpc_client_admin_upload_files', array( &$this, 'ajax_admin_upload_files' ) );
        add_action( 'wp_ajax_wpc_client_admin_upload_files', array( &$this, 'ajax_admin_upload_files' ) );

        add_action( 'wp_ajax_nopriv_wpc_client_admin_plupload_upload_files', array( &$this, 'ajax_admin_plupload_upload_files' ) );
        add_action( 'wp_ajax_wpc_client_admin_plupload_upload_files', array( &$this, 'ajax_admin_plupload_upload_files' ) );

        //ajax update file data
        add_action( 'wp_ajax_wpc_update_file_data', array( &$this, 'ajax_update_file_data' ) );

        //admin save template
        add_action( 'wp_ajax_wpc_save_template', array( &$this, 'ajax_admin_save_template' ) );

        //get capabilities for role
        add_action( 'wp_ajax_wpc_get_capabilities', array( &$this, 'ajax_get_capabilities' ) );

        //assign clients/circles
        add_action( 'wp_ajax_update_assigned_data', array( &$this, 'update_assigned_data' ) );

        //save enable custom redirects
        add_action( 'wp_ajax_wpc_save_enable_custom_redirects', array( &$this, 'ajax_save_enable_custom_redirects' ) );

        add_action( 'wp_ajax_wpc_send_test_email', array( &$this, 'ajax_send_test_email' ) );
        add_action( 'wp_ajax_wpc_send_test_template_email', array( &$this, 'ajax_send_test_template_email' ) );
        add_action( 'wp_ajax_wpc_client_update_last_activity', array( &$this, 'ajax_update_last_activity' ) );

        //set portal page client for preview
        add_action( 'wp_ajax_wpc_set_portal_page_client', array( &$this, 'ajax_set_portal_page_client' ) );

        //get options filter for files
        add_action( 'wp_ajax_wpc_get_options_filter_for_files', array( &$this, 'ajax_get_options_filter_for_files' ) );

        add_action( 'wp_ajax_wpc_get_options_filter_for_files_download_log', array( &$this, 'ajax_get_options_filter_for_files_download_log' ) );

        //get options filter for payment history
        add_action( 'wp_ajax_wpc_get_options_filter_for_payments', array( &$this, 'ajax_get_options_filter_for_payments' ) );

        //get options filter for managers
        add_action( 'wp_ajax_wpc_get_options_filter_for_managers', array( &$this, 'ajax_get_options_filter_for_managers' ) );

        //get options filter for permissions report
        add_action( 'wp_ajax_wpc_get_options_filter_for_permissions', array( &$this, 'ajax_get_options_filter_for_permissions' ) );

        //get report for permissions report
        add_action( 'wp_ajax_wpc_get_report_for_permissions', array( &$this, 'ajax_get_report_for_permissions' ) );

        //get sections for style scheme
        add_action( 'wp_ajax_wpc_customizer_get_sections', array( &$this, 'ajax_customizer_get_sections' ) );

        //save allowed gateways
        add_action( 'wp_ajax_wpc_save_allow_gateways', array( &$this, 'ajax_save_allow_gateways' ) );

        //get gateway settings
        add_action( 'wp_ajax_wpc_get_gateway_setting', array( &$this, 'ajax_get_gateway_setting' ) );

        //dismiss admin notice
        add_action( 'wp_ajax_wpc_dismiss_admin_notice', array( &$this, 'ajax_dismiss_admin_notice' ) );

        //fileslu/filesla shortcode ajax actions
        add_action( 'wp_ajax_wpc_files_shortcode_get_filter', array( &$this, 'ajax_files_shortcode_get_filter' ) );
        add_action( 'wp_ajax_wpc_get_filter_data', array( &$this, 'ajax_get_filter_data' ) );
        add_action( 'wp_ajax_wpc_files_shortcode_table_pagination', array( &$this, 'ajax_files_shortcode_table_pagination' ) );
        add_action( 'wp_ajax_wpc_files_shortcode_list_pagination', array( &$this, 'ajax_files_shortcode_list_pagination' ) );
        add_action( 'wp_ajax_wpc_files_shortcode_blog_pagination', array( &$this, 'ajax_files_shortcode_blog_pagination' ) );
        add_action( 'wp_ajax_wpc_files_shortcode_tree_pagination', array( &$this, 'ajax_files_shortcode_tree_pagination' ) );
        add_action( 'wp_ajax_wpc_files_shortcode_tree_get_files', array( &$this, 'ajax_files_shortcode_tree_get_files' ) );

        //pagel shortcode ajax
        add_action( 'wp_ajax_wpc_pagel_shortcode_tree_pagination', array( &$this, 'ajax_pagel_shortcode_tree_pagination' ) );
        add_action( 'wp_ajax_wpc_pagel_shortcode_tree_get_pages', array( &$this, 'ajax_pagel_shortcode_tree_get_pages' ) );
        add_action( 'wp_ajax_wpc_pagel_shortcode_list_pagination', array( &$this, 'ajax_pagel_shortcode_list_pagination' ) );


        add_action( 'wp_ajax_wpc_settings', array( &$this, 'ajax_settings' ) );

        add_action( 'wp_ajax_wpc_resize_all_thumbnails', array( &$this, 'ajax_resize_all_thumbnails' ) );

        // widget changes submitted by ajax method
        add_filter( 'widget_update_callback', array( WPC()->hooks(), 'WPC_Widgets->widget_ajax_update_callback' ), 11, 4);

        add_action( 'wp_ajax_wpc_hub_set_default', array( &$this, 'ajax_hub_set_default' ), 11, 4);
        add_action( 'wp_ajax_wpc_return_to_admin_panel', array( &$this, 'ajax_return_to_admin_panel' ) );


        add_action( 'wp_ajax_nopriv_wpc_client_remote_sync', array( &$this, 'ajax_remote_sync' ) );
        add_action( 'wp_ajax_wpc_client_remote_sync', array( &$this, 'ajax_remote_sync' ) );

        add_action( 'wp_ajax_wpc_get_user_list', array( &$this, 'ajax_get_user_list' ) );

        add_action( 'wp_ajax_wpc_save_priority', array( &$this, 'ajax_save_priority' ) );

        add_action( 'wp_ajax_wpc_generate_password', array( &$this, 'ajax_generate_password' ) );
        add_action( 'wp_ajax_nopriv_wpc_generate_password', array( &$this, 'ajax_generate_password' ) );

        add_action( 'wp_ajax_wpc_set_rating', array( &$this, 'ajax_set_rating' ) );



        add_action( 'wp_ajax_wpc_clients_dashboard_widget', array( &$this, 'wpc_clients_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_client_staff_dashboard_widget', array( &$this, 'wpc_client_staff_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_private_messages_dashboard_widget', array( &$this, 'wpc_private_messages_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_portal_pages_dashboard_widget', array( &$this, 'wpc_portal_pages_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_client_circles_dashboard_widget', array( &$this, 'wpc_client_circles_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_managers_dashboard_widget', array( &$this, 'wpc_managers_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_files_dashboard_widget', array( &$this, 'wpc_files_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_top_files_dashboard_widget', array( &$this, 'wpc_top_files_dashboard_widget' ) );
        add_action( 'wp_ajax_wpc_settings_info_dashboard_widget', array( &$this, 'wpc_settings_info_dashboard_widget' ) );

        add_action( 'wp_ajax_wpc_update_widgets_order', array( &$this, 'update_widgets_order' ) );
        add_action( 'wp_ajax_wpc_update_widgets_color', array( &$this, 'update_widgets_color' ) );
        add_action( 'wp_ajax_wpc_collapse_widget', array( &$this, 'collapse_widget' ) );

        add_action( 'wp_ajax_wpc_get_shortcode_attributes_form', array( &$this, 'ajax_get_shortcode_attributes_form' ) );

        //for avatars
        add_action( 'wp_ajax_wpc_upload_avatar', array( &$this, 'ajax_upload_avatar' ) );
        add_action( 'wp_ajax_nopriv_wpc_upload_avatar', array( &$this, 'ajax_upload_avatar' ) );
        add_action( 'wp_ajax_wpc_avatar_remove', array( &$this, 'ajax_avatar_remove' ) );

        //ajax for messages
        add_action( 'wp_ajax_wpc_message_get_connected_members', array( &$this, 'ajax_message_get_cc_members' ) );
        add_action( 'wp_ajax_wpc_message_get_list', array( &$this, 'ajax_message_get_list' ) );
        add_action( 'wp_ajax_wpc_message_get_filter', array( &$this, 'ajax_message_get_filter' ) );
        add_action( 'wp_ajax_wpc_message_get_filter_data', array( &$this, 'ajax_message_get_filter_data' ) );
        add_action( 'wp_ajax_wpc_message_chain_mark_read', array( &$this, 'ajax_message_chain_mark_read' ) );
        add_action( 'wp_ajax_wpc_message_chain_to_trash', array( &$this, 'ajax_message_chain_to_trash' ) );
        add_action( 'wp_ajax_wpc_message_chain_to_archive', array( &$this, 'ajax_message_chain_to_archive' ) );
        add_action( 'wp_ajax_wpc_message_leave_chain', array( &$this, 'ajax_message_leave_chain' ) );
        add_action( 'wp_ajax_wpc_message_chain_delete_permanently', array( &$this, 'ajax_message_chain_delete_permanently' ) );
        add_action( 'wp_ajax_wpc_message_chain_restore', array( &$this, 'ajax_message_chain_restore' ) );
        add_action( 'wp_ajax_wpc_message_get_chain', array( &$this, 'ajax_message_get_chain' ) );
        add_action( 'wp_ajax_wpc_message_reply', array( &$this, 'ajax_message_reply' ) );

        add_action( 'wp_ajax_wpc_message_front_end_get_list', array( &$this, 'ajax_message_front_end_get_list' ) );
        add_action( 'wp_ajax_wpc_message_front_end_get_chain', array( &$this, 'ajax_message_front_end_get_chain' ) );
        add_action( 'wp_ajax_wpc_message_front_end_new_message', array( &$this, 'ajax_message_front_end_new_message' ) );

        //ajax for email sending profiles
        //Get isset or new profile
        add_action( 'wp_ajax_wpc_get_email_profile', array( &$this, 'ajax_get_email_profile' ) );
        //Save email profile
        add_action( 'wp_ajax_wpc_save_email_profile', array( &$this, 'ajax_save_email_profile' ) );
        //Delete email profile
        add_action( 'wp_ajax_wpc_delete_email_profile', array( &$this, 'ajax_delete_email_profile' ) );

        //Save SELECTED email profile
        add_action( 'wp_ajax_wpc_save_selected_email_profile', array( &$this, 'ajax_save_selected_email_profile' ) );

        //Delete wpc_notice
        add_action( 'wp_ajax_wpc_delete_notice', array( $this, 'wpc_delete_notice' ) );

        //Pop-up for Shortcodes&Placeholders for content Editor
        add_action( 'wp_ajax_wpc_get_shortcodes_and_placeholders', array( $this, 'get_shortcodes_and_placeholders' ) );

        add_action( 'wp_ajax_wpc_shortcode_templates', array( &$this, 'ajax_shortcode_templates' ) );

        //email notifications
        add_action( 'wp_ajax_get_email_template_data', array( &$this, 'get_email_template_data' ) );

        add_action( 'wp_ajax_get_clientpage_template_data', array( &$this, 'get_clientpage_template_data' ) );

        //Ajax action - should be here!!!!
        add_action( 'wp_ajax_get_popup_pagination_data', array( WPC()->hooks(), 'WPC_Assigns->ajax_get_popup_pagination_data' ), 100 );


        //Ajax actions for Import\Export
        add_action( 'wp_ajax_nopriv_wpc_get_import_items', array( WPC()->hooks(), 'WPC_Import_Export->ajax_get_import_items' ) );
        add_action( 'wp_ajax_wpc_get_import_items', array( WPC()->hooks(), 'WPC_Import_Export->ajax_get_import_items' ) );
        add_action( 'wp_ajax_wpc_get_export_item_data', array( WPC()->hooks(), 'WPC_Import_Export->ajax_get_export_item_data' ) );

        // Save export,import template
        add_action( 'wp_ajax_wpc_save_export_import_template', array( WPC()->hooks(), 'WPC_Import_Export->save_export_import_template' ) );
        add_action( 'wp_ajax_wpc_download_export_import_templates', array( WPC()->hooks(), 'WPC_Import_Export->download_export_import_templates' ) );
        add_action( 'wp_ajax_wpc_use_export_template', array( WPC()->hooks(), 'WPC_Import_Export->use_export_template' ) );
        add_action( 'wp_ajax_wpc_delete_export_import_template', array( WPC()->hooks(), 'WPC_Import_Export->delete_export_import_template' ) );

        // Ajax popup to watch video
        add_action( 'wp_ajax_wpc_watch_video_in_popup', array( &$this, 'ajax_wpc_watch_video_in_popup' ) );
        add_action( 'wp_ajax_nopriv_wpc_watch_video_in_popup', array( &$this, 'ajax_wpc_watch_video_in_popup' ) );


    }



    function ajax_shortcode_templates() {
        if ( empty( $_POST['operation'] ) )
            wp_send_json_error( new WP_Error( 'wrong_ajax_request', __( 'Wrong ajax request data', WPC_CLIENT_TEXT_DOMAIN ) ) );

        if ( empty( $_POST['filename'] ) )
            wp_send_json_error( new WP_Error( 'wrong_ajax_request', __( 'Wrong ajax request data', WPC_CLIENT_TEXT_DOMAIN ) ) );

        $operation = $_POST['operation'];
        $path = isset( $_POST['path'] ) ? $_POST['path'] : '';
        $filename = $_POST['filename'];

        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $path . $filename ) )
            wp_send_json_error( new WP_Error( 'wrong_ajax_request', __( 'Wrong ajax request data', WPC_CLIENT_TEXT_DOMAIN ) ) );

        $template = WPC()->templates()->locate_template( $filename, $path );
        if ( ! file_exists( $template ) )
            wp_send_json_error( new WP_Error( 'template_not_exists', __( 'Template does not exists', WPC_CLIENT_TEXT_DOMAIN ) ) );

        switch( $operation ) {
            case 'view_template':
            case 'edit_template':

                $handle = fopen( $template, "r");
                $content = fread( $handle, filesize( $template ) );
                fclose($handle);

                wp_send_json_success( array(
                    'content' => $content
                ) );
                break;
            case 'copy_to_theme':
                if( strpos( $template, WP_PLUGIN_DIR ) === false ) {
                    wp_send_json_error( new WP_Error( 'template_in_theme', __( 'Template already in theme', WPC_CLIENT_TEXT_DOMAIN ) ) );
                }

                $theme_template_path = WPC()->templates()->get_template_file( 'theme', $filename, $path );


                if ( WPC()->is_wp_com() ) {
                    $temp_path = str_replace( WPC()->get_upload_dir('wpclient/templates/' ), '', $theme_template_path );
                    $theme_dir = WPC()->get_upload_dir('wpclient/templates/' );
                } else {
                    $temp_path = str_replace( trailingslashit( get_stylesheet_directory() ), '', $theme_template_path );
                    $theme_dir = trailingslashit( get_stylesheet_directory() );
                }


                $temp_path = str_replace( '/', DIRECTORY_SEPARATOR, $temp_path );
                $folders = explode( DIRECTORY_SEPARATOR, $temp_path );
                $folders = array_splice( $folders, 0, count( $folders ) - 1 );
                $cur_folder = '';

                foreach( $folders as $folder ) {
                    $prev_dir = $cur_folder;
                    $cur_folder .= $folder . DIRECTORY_SEPARATOR;
                    if ( !is_dir( $theme_dir . $cur_folder ) && wp_is_writable( $theme_dir . $prev_dir ) ) {
                        mkdir( $theme_dir . $cur_folder, 0777 );
                    }
                }

                if( copy( $template, $theme_template_path ) ) {
                    wp_send_json_success();
                } else {
                    wp_send_json_error( new WP_Error( 'template_not_exists', __( 'Can not copy template to theme', WPC_CLIENT_TEXT_DOMAIN ) ) );
                }
                break;

            case 'delete':
                if( strpos( $template, WP_PLUGIN_DIR ) !== false ) {
                    wp_send_json_error( new WP_Error( 'template_in_theme', __( 'Template does not exists in theme', WPC_CLIENT_TEXT_DOMAIN ) ) );
                }

                if ( unlink( $template ) ) {
                    $template = WPC()->templates()->locate_template( $filename, $path );
                    $handle = fopen( $template, "r");
                    $content = fread( $handle, filesize( $template ) );
                    fclose( $handle );
                    wp_send_json_success( $content );
                } else {
                    wp_send_json_error( new WP_Error( 'template_not_exists', __( 'Can not remove template from theme', WPC_CLIENT_TEXT_DOMAIN ) ) );
                }
                break;
            case 'save_template':
                $content = isset( $_POST['content'] ) ? base64_decode( $_POST['content'] ) : '';

                if( strpos( $template, WP_PLUGIN_DIR ) !== false || !file_exists( $template ) ) {
                    wp_send_json_error( new WP_Error( 'template_in_theme', __( 'Template does not exists in theme', WPC_CLIENT_TEXT_DOMAIN ) ) );
                }

                $fp = fopen( $template, "w" );
                $result = fputs( $fp, $content );
                fclose( $fp );
                if( $result !== false ) {
                    wp_send_json_success();
                } else {
                    wp_send_json_error( new WP_Error( 'template_nwrite_error', __( 'Can not rewrite Template. Please check Theme folder permisions', WPC_CLIENT_TEXT_DOMAIN ) ) );
                }
                break;
        }
    }


    function get_email_template_data() {
        if ( empty( $_POST['slug'] ) ) {
            wp_die( json_encode( array(
                'status'    => false,
            ) ) );
        }

        $slug = $_POST['slug'];
        $wpc_templates_emails   = WPC()->get_settings( 'templates_emails' );

        if ( empty( $wpc_templates_emails[$slug] ) ) {
            wp_die( json_encode( array(
                'status'    => false,
            ) ) );
        }

        $template_data = array(
            'subject'   => $wpc_templates_emails[$slug]['subject'],
            'body'      => $wpc_templates_emails[$slug]['body'],
            'enable'    => isset( $wpc_templates_emails[$slug]['enable'] ) ? $wpc_templates_emails[$slug]['enable'] : true
        );

        wp_die( json_encode( array(
            'status'    => true,
            'template'  => $template_data
        ) ) );
    }


    function get_clientpage_template_data() {
        if ( empty( $_POST['slug'] ) ) {
            wp_die( json_encode( array(
                'status'    => false,
            ) ) );
        }

        $slug = $_POST['slug'];
        $wpc_templates_clientpage = html_entity_decode( WPC()->get_settings( $slug, '' ) );

        wp_die( json_encode( array(
            'status'    => true,
            'template'  => $wpc_templates_clientpage,
        ) ) );
    }


    function get_shortcodes_and_placeholders() {
        WPC()->set_shortcode_data();
        $shortcodes = array();
        foreach( WPC()->shortcode_data as $key=>$val ) {
            if ( !empty( $val['categories'] ) ) {
                $shortcodes[ $val['categories'] ][ $key ] = array(
                    'title' => isset( $val['title'] ) ? $val['title'] : '',
                    'attributes' => isset( $val['attributes'] ) ? $val['attributes'] : array(),
                    'content' => isset( $val['content'] ) ? $val['content'] : '',
                    'close_tag' => isset( $val['close_tag'] ) ? $val['close_tag'] : false
                );
            }
        }

        $placeholders = array(
            'general' => array(
                'title' => __( 'General', WPC_CLIENT_TEXT_DOMAIN ),
                'items' => array(
                    '{site_title}' => '',
                    '{contact_name}' => '',
                    '{client_business_name}' => '',
                    '{client_name}' => '',
                    '{client_phone}' => '',
                    '{client_email}' => '',
                    '{client_registration_date}' => '',
                    '{user_name}' => '',
                    '{login_url}' => '',
                    '{logout_url}' => '',
                    '{user_display_name}' => '',
                    '{user_first_name}' => '',
                    '{user_last_name}' => '',
                    '{user_email}' => '',
                    '{user_login}' => '',
                ),
            ),
            'business' => array(
                'title' => __( 'Business', WPC_CLIENT_TEXT_DOMAIN ),
                'items' => array(
                    '{business_logo_url}' => __( 'Logo URL', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_name}' => __( 'Official Business Name', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_address}' => __( 'Business Address', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_mailing_address}' => __( 'Mailing Address', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_website}' => __( 'Website', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_email}' => __( 'Email', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_phone}' => __( 'Phone', WPC_CLIENT_TEXT_DOMAIN ),
                    '{business_fax}' => __( 'Fax', WPC_CLIENT_TEXT_DOMAIN ),
                ),
            ),
            'specific' => array(
                'title' => __( 'Specific', WPC_CLIENT_TEXT_DOMAIN ),
                'items' => array(
                    '{admin_url}' => '',
                    '{approve_url}' => '',
                    '{user_password}' => '',
                    '{page_title}' => '',
                    '{admin_file_url}' => '',
                    '{subject}' => '',
                    '{message}' => '',
                    '{file_name}' => '',
                    '{estimate_number}' => '',
                    '{invoice_number}' => '',
                ),
            ),
        );

        if ( WPC()->flags['easy_mode'] ) {
            $add = array(
                '{manager_name}' => '',
                '{staff_display_name}' => '',
                '{staff_first_name}' => '',
                '{staff_last_name}' => '',
                '{staff_email}' => '',
                '{staff_login}' => '',
            );
            $placeholders['general']['items'] = array_merge( $placeholders['general']['items'], $add );

            $placeholders['specific']['items']['{file_category}'] = '';
        }

        ob_start(); ?>

        <form id="wpc_shortcodes_and_placeholders" method="get">
            <div class="wpc_left_side">

                <div class="wpc_switching_button">
                    <div class="wpc_switching_button_val">
                        <input name="wpc_shortcodes_on" value="on" id="wpc_shortcodes" checked="checked" type="radio">
                        <label for="wpc_shortcodes"><?php _e( 'Shortcodes', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                    </div>
                    <div class="wpc_switching_button_val">
                        <input name="wpc_shortcodes_on" value="off" id="wpc_placeholders" type="radio">
                        <label for="wpc_placeholders"><?php _e( 'Placeholders', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                    </div>
                </div>

                <div id="wpc_list_shortcodes">
                    <div class="wpc_accordion">
                        <?php
                        $all_sh_categories = array(
                            'files' => __( 'Files', WPC_CLIENT_TEXT_DOMAIN ),
                            'content' => __( 'Content', WPC_CLIENT_TEXT_DOMAIN ),
                            'pages' => __( 'Pages', WPC_CLIENT_TEXT_DOMAIN ),
                            'clients' => __( 'Users', WPC_CLIENT_TEXT_DOMAIN ),
                            'other' => __( 'Others', WPC_CLIENT_TEXT_DOMAIN ),
                        );

                        foreach ( $all_sh_categories as $category => $val ) {
                            echo '<h3>' . $val . '</h3>';
                            echo '<div><p>';
                            foreach ( $shortcodes[$category] as $key => $sh ) {
                                echo '<a href="#" class="wpc_accordion_link" data-name="' . $key . '"><span>' . $sh['title'] . '</span></a>';
                            }
                            echo '</p></div>';
                        }
                        ?>
                    </div>
                </div>
                <div id="wpc_list_placeholders">
                    <div class="wpc_accordion">
                        <?php
                        foreach ( $placeholders as $category => $value ) {
                            echo '<h3>' . $value['title']  . '</h3>';
                            echo '<div><p>';
                            foreach ( $value['items'] as $key => $sh ) {
                                $desc = !empty( $sh ) ? __( 'Description:', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . $sh : '';
                                echo '<a href="#" class="wpc_accordion_link" data-name="' . $key . '" data-description="' . $desc . '"><span>' . $key . '</span></a>';
                            }
                            echo '</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="wpc_right_side">
                <div class="wpc_right_side_content"></div>

                <div class="action_buttons_block">
                    <input type="button" class="button-primary add_shortcode_button" value="<?php _e( 'Add Shortcode', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <input type="button" class="button-primary add_placeholder_button" value="<?php _e( 'Add Placeholder', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <input type="button" class="button cancel_shortcode_button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                </div>
            </div>

            <input type="hidden" id="wpc_item_name" value="">
        </form>

        <?php $content = ob_get_clean();

        echo json_encode( array(
            'title'     => __( 'Add Shortcode or Placeholder', WPC_CLIENT_TEXT_DOMAIN ),
            'content'   => $content
        ) );
        exit;
    }

    function ajax_paste_portal_page_content() {
        if ( empty( $_POST['from_id'] ) ) {
            wp_die( json_encode( array(
                'status'    => false,
            ) ) );
        }

        if ( ! check_ajax_referer( $_POST['action'] . '_security_' . WPC()->members()->get_client_id(), 'security', false ) ) {
            wp_die( json_encode( array(
                'status'    => false,
            ) ) );
        }

        global $wpdb;

        if ( $_POST['from_id'] == 'wpc_portal_page_template' ) {
            $content = WPC()->get_settings( 'templates_clientpage', '' );
            $content = html_entity_decode( $content );
        } else {
            $content = $wpdb->get_var( $wpdb->prepare(
                "SELECT post_content
                    FROM $wpdb->posts
                    WHERE ID = %d",
                $_POST['from_id']
            ) );
        }

        wp_die( json_encode( array(
            'status'    => true,
            'content'   => $content,
        ) ) );
    }


    function ajax_custom_field_set_value() {
        $title  = __( 'Set Value', WPC_CLIENT_TEXT_DOMAIN );
        $content = __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN );

        if ( !empty( $_POST['name'] ) ) {
            $name = explode( '__', $_POST['name'] );
        }

        //check name and hash
        if ( !empty( $name[0] ) && !empty( $name[1] ) && md5( 'wpc_custom_field' . SECURE_AUTH_SALT . $name[0] ) == $name[1] ) {
            $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
        }

        if ( !empty( $name[0] ) && !empty( $wpc_custom_fields[ $name[0] ] ) ) {
            $custom_field           = $wpc_custom_fields[ $name[0] ];
            $custom_field['name']   = $name[0];

            if ( !empty( $custom_field['nature'] ) && 'both' === $custom_field['nature'] ) {
                $for = sprintf( __( '%s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] );
            } else if ( !empty( $custom_field['nature'] ) && 'staff' === $custom_field['nature'] ) {
                $for = WPC()->custom_titles['staff']['p'];
            } else {
                $for = WPC()->custom_titles['client']['p'];
            }

            ob_start(); ?>
            <form method="post" name="wpc_set_value" id="wpc_set_value" style="float:left;width:100%;">
                <input type="hidden" name="wpc_name" value="<?php echo $custom_field['name'] ?>">
                <div><?php printf( __( 'Set values of "%s" custom field for all %s', WPC_CLIENT_TEXT_DOMAIN ), $custom_field['name'], $for ) ?></div>
                <br>
                <?php echo WPC()->custom_fields()->shortcode_custom_field( $custom_field ); ?>
                <br>
                <label><input type="checkbox" name="wpc_only_undefined" value="1">
                    <span><?php _e( 'Only if current value is undefined', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                </label>
                <br>
                <br>
                <br>
                <div style="clear: both; text-align: center;">
                    <input type="button" class="button-primary" id="wpc_update_value" value="<?php _e( 'Set Value', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                    <input type="button" class="button" id="wpc_close_set_value" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                </div>
            </form>

            <?php $content = ob_get_clean();
        }

        echo json_encode( array(
            'title'     => $title,
            'content'   => $content,
        ) );
        exit;
    }


    function wpc_delete_notice() {
        $key = !empty( $_POST['key'] ) ? $_POST['key'] : '';
        if ( $key ) {
            WPC()->admin()->delete_wpc_notice( $key );
        }
    }


    /**
     * Get data of circle
     */
    function ajax_get_data_circle() {
        if ( $id = (int)filter_input( INPUT_POST, 'id' ) ) {
            //EDIT
            $result['id'] = $id;
            $result['action'] = 'edit_group';
            $result['wpnonce'] = wp_create_nonce( 'wpc_update_circle' . get_current_user_id() . $id );

            $result = array_merge( $result, WPC()->groups()->get_group( $id ) );

            $clients_id = WPC()->groups()->get_group_clients_id( $id );
            $result['count_clients'] = count($clients_id);
            $result['clients'] = implode(',', $clients_id);
        } else {
            //CREATE
            $result['action'] = 'create_group';
            $result['wpnonce'] = wp_create_nonce( 'wpc_create_circle' . get_current_user_id() );
        }

        /*our_hook_
            hook_name: wpc_get_circle_data
            hook_title: Add/Edit Circle Form Ajax Response
            hook_description: Hook runs on ajax response Add/Edit Circle Form.
            hook_type: filter
            hook_in: wp-client
            hook_location class.ajax.php
            hook_since: 4.1.6
            */
        $result = apply_filters( 'wpc_get_circle_data', $result, $id );

        echo json_encode( $result );
        exit;
    }


    /**
     * Get data of pp category for add/edit form
     */
    function ajax_get_data_pp_category() {
        if ( $id = (int)filter_input( INPUT_POST, 'id' ) ) {
            //EDIT
            $result['id'] = $id;
            $result['action'] = 'edit_pp_category';
            $result['wpnonce'] = wp_create_nonce( 'wpc_update_pp_category' . get_current_user_id() . $id );

            $result['params'] = WPC()->categories()->get_pp_category( $id );

            $clients_id = WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $id, 'client' );
            $result['count_clients'] = count($clients_id);
            $result['clients'] = implode(',', $clients_id);

            $circles_id = WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $id, 'circle' );
            $result['count_circles'] = count($circles_id);
            $result['circles'] = implode(',', $circles_id);
        } else {
            //CREATE
            $result['action'] = 'create_pp_category';
            $result['wpnonce'] = wp_create_nonce( 'wpc_create_pp_category' . get_current_user_id() );
        }

        echo json_encode( $result );
        exit;
    }


    /**
     * Get html
     */
    function ajax_get_html_delete_pp_category() {
        if ( ! ( $id = (int)filter_input( INPUT_POST, 'id' ) ) ) {
            exit;
        }

        $result['title'] = sprintf( __( 'Delete %s Category', WPC_CLIENT_TEXT_DOMAIN ),
            WPC()->custom_titles['portal_page']['s'] );

        $wpnonce = wp_create_nonce( 'wpc_delete_pp_category' . get_current_user_id() . $id );

        $args = array(
            'post_type' => 'clientspage',
            'post_status' => 'publish',
            'meta_key' => '_wpc_category_id',
            'meta_value' => $id
        );
        $postslist = get_posts( $args );

        ob_start();
        ?>
        <form method="post" action="" class="wpc_form">
            <input type="hidden" name="id" value="<?php echo $id ?>" />
            <input type="hidden" name="_wpnonce" value="<?php echo $wpnonce ?>" />
            <input type="hidden" name="action" value="delete_portalpage_category" />

            <div class="wpc_text_center">
                <?php
                if ( count( $postslist ) ) {
                    ?>
                    <br>
                    <span><?php printf( __( 'Category have %1$s. What do with %1$s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?>:</span>
                    <br><br><br>
                    <?php
                    $categories = WPC()->categories()->get_clientspage_categories();
                    if ( 1 < count( $categories ) ) {
                        ?>
                        <select name="cat_reassign">
                            <?php
                            foreach( $categories as $cat) {
                                if ( $id != $cat['id'] ) {
                                    echo "<option value=\"{$cat['id']}\">{$cat['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                        <button name="reassign_pp" id="wpc_reassign_pp" class="button-secondary wpc_button" value="1"><?php printf( __( 'Reassign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?></button>
                        <br><br>
                    <?php } ?>
                    <input type="submit" id="wpc_delete_pp" class="button-primary wpc_submit" value="<?php printf( __( 'Delete %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?>" />
                    <?php
                } else {
                    ?>
                    <br><br>
                    <?php
                    printf( __( 'Are you sure you want to delete this %s Category?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] );
                    ?>
                    <br><br><br>
                    <input type="submit" class="button-primary wpc_submit" id="delete_pp_category" value="<?php printf( __( 'Delete %s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) ?>" />
                    <?php
                }
                ?>
            </div>
        </form>
        <?php $content = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        $result['content'] = $content;

        echo json_encode( $result );
        exit;
    }


    /*
        * Ajax function for get client details
        *
        * @return array json answer to js
        */
    function ajax_view_client() {
        global $wpdb;

        if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
            echo json_encode( array(
                'title'     => sprintf( __( 'View %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        $id = explode( '_', $_POST['id'] );

        //check id and hash
        if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientview_' . $id[0] ) == $id[1] ) {
            $client = get_userdata( $id[0] );
            $client_contact_phone = get_user_meta( $id[0], $wpdb->prefix . 'contact_phone', true );

            $current_manager_ids = WPC()->members()->get_client_managers( $id[0] );

            $managers = array();
            if ( is_array( $current_manager_ids ) && count( $current_manager_ids ) ) {
                foreach( $current_manager_ids as $key=>$current_manager_id ) {
                    $managers[$key] = get_userdata( $current_manager_id );
                    $managers[$key] = ( isset( $managers[$key] ) ) ? $managers[$key]->user_login : '';
                }
            }

            $client_groups = WPC()->groups()->get_client_groups_id( $id[0] );

            $groups = array();
            if ( is_array( $client_groups ) && count( $client_groups ) ) {
                foreach ( $client_groups as $key=>$group_id ) {
                    $groups[$key] = WPC()->groups()->get_group( $group_id );
                    $groups[$key] = ( isset( $groups[$key] ) ) ? $groups[$key]['group_name'] : '';
                }
            }

            $business_name = get_user_meta( $id[0], 'wpc_cl_business_name', true );

            ob_start(); ?>

            <table id="wpc_client_details_content" class="form-table">
                <?php if( !WPC()->flags['easy_mode'] ) { ?>
                    <tr>
                        <td>
                            <label><?php printf( '%s %s', WPC()->custom_titles['client']['s'], WPC()->custom_titles['manager']['p'] ) ?>:</label><br />
                            <?php echo ( is_array( $managers ) && count( $managers ) ) ? '<span>' . implode( ', ', $managers ) . '</span> ' : __( 'None', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>
                        <label><?php printf( '%s %s', WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ) ?>:</label><br/>
                        <?php echo ( is_array( $groups ) && count( $groups ) ) ? '<span>' . implode( ', ', $groups ) . '</span> ' : __( 'None', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="business_name"><?php _e( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?>: </label> <br/>
                        <input type="text" readonly="readonly" value="<?php echo $business_name;?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="contact_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                        <input type="text" readonly="readonly" value="<?php echo $client->display_name; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="contact_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                        <input type="text" readonly="readonly" value="<?php echo $client->user_email ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                        <input type="text" readonly="readonly" value="<?php echo $client_contact_phone ?>" />
                    </td>
                </tr>

                <?php if( !WPC()->flags['easy_mode'] ) {
                    $custom_fields = WPC()->custom_fields()->get_custom_fields( 'admin_edit_client', $id[0], true );
                    if ( is_array( $custom_fields ) && 0 < count( $custom_fields ) ) {
                        foreach( $custom_fields as $key => $value ) {
                            if ( 'hidden' == $value['type'] ) {
                                echo $value['field'];
                            } else {
                                echo '<tr><td>';
                                echo ( !empty( $value['label'] ) ) ? $value['label'] . '<br />' : '';
                                echo ( !empty( $value['field'] ) ) ? $value['field'] : '';
                                echo ( !empty( $value['description'] ) ) ? '<br />' . $value['description']: '';
                                echo '</td></tr>';
                            }
                        }
                    }
                }

                /*our_hook_
                        hook_name: wpc_client_view_client_after_custom_fields
                        hook_title: View Client Form
                        hook_description: Can be used for adding custom html on View Client Form.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.ajax.php
                        hook_param: int $client_id
                        hook_since: 3.3.5
                    */
                do_action( 'wpc_client_view_client_after_custom_fields', $id[0] ); ?>
            </table>

            <style type="text/css">
                #wpc_client_details_content input[type=text] {
                    width:400px;
                }

                #wpc_client_details_content input[type=password] {
                    width:400px;
                }
            </style>

            <script type="text/javascript">
                custom_datepicker_init();
            </script>

            <?php $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            echo json_encode( array(
                'title'     => sprintf( __( 'View %s: %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], $client->user_login ),
                'content'   => $content
            ) );
            exit;
        }

        echo json_encode( array(
            'title'     => sprintf( __( 'View %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
            'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
        ) );
        exit;
    }


    /*
        * Ajax function for approve client form
        *
        * @return array json answer to js
        */
    function ajax_approve_client() {
        if ( empty( $_POST['id'] ) && empty( $_POST['ids'] ) ) {
            echo json_encode( array(
                'title'     => sprintf( __( 'Approve %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }
        if ( !empty( $_POST['id'] ) ) {
            $id = explode( '_', $_POST['id'] );

            //check id and hash
            if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientapprove_' . $id[0] ) == $id[1] ) {
                $client = get_userdata( $id[0] );
                ob_start(); ?>

                <form name="approve_client" method="post" >
                    <input type="hidden" name="wpc_action" value="client_approve" />
                    <input type="hidden" name="client_id" id="client_id" value="<?php echo $id[0] ?>" />
                    <input type="hidden" value="<?php echo wp_create_nonce( 'wpc_client_approve' ) ?>" name="_wpnonce" id="_wpnonce">

                    <h3 id="assign_name"></h3>

                    <table style="width:100%;float:left;">
                        <?php
                        //get managers
                        $args = array(
                            'role'      => 'wpc_manager',
                            'orderby'   => 'user_login',
                            'order'     => 'ASC',
                            'fields'    => array( 'ID','user_login' ),

                        );

                        $managers = get_users( $args );

                        if ( is_array( $managers ) && 0 < count( $managers ) ) { ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                                        'text'    => sprintf( __( 'Assign To %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['manager']['p'] )
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_managers',
                                        'id'    => 'wpc_managers',
                                        'value' => ''
                                    );
                                    $additional_array = array(
                                        'counter_value' => 0
                                    );

                                    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                    WPC()->assigns()->assign_popup('manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php
                                $groups = WPC()->groups()->get_groups();
                                $selected_groups = array();
                                foreach ( $groups as $group ) {
                                    if( '1' == $group['auto_select'] ) {
                                        $selected_groups[] = $group['group_id'];
                                    }
                                }

                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                    'text'    => sprintf( __( 'Assign To %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] )
                                );
                                $input_array = array(
                                    'name'  => 'wpc_circles',
                                    'id'    => 'wpc_circles',
                                    'value' => implode(',', $selected_groups)
                                );
                                $additional_array = array(
                                    'counter_value' => count( $selected_groups )
                                );

                                $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>
                        <tr style="height:15px;"><td>&nbsp;</td></tr>
                        <tr>
                            <td style="text-align: center;">
                                <input type="submit" class="button button-primary" name="save" id="save_popup" value="<?php _e( 'Approve', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                                <input type="button" class="button" name="cancel" id="cancel_popup" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                            </td>
                        </tr>
                    </table>
                </form>

                <?php $content = ob_get_contents();
                if( ob_get_length() ) {
                    ob_end_clean();
                }

                echo json_encode( array(
                    'title'     => sprintf( __( 'Approve %s: %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], $client->user_login ),
                    'content'   => $content
                ) );
                exit;
            }
        } elseif ( !empty( $_POST['ids'] ) ) {
            ob_start(); ?>
            <form name="approve_client" method="post" >
                <input type="hidden" name="wpc_action" value="client_approve" />
                <input type="hidden" name="client_id" id="client_id" value="<?php echo $_POST['ids'] ?>" />
                <input type="hidden" value="<?php echo wp_create_nonce( 'wpc_client_approve' ) ?>" name="_wpnonce" id="_wpnonce">

                <h3 id="assign_name"></h3>

                <table style="width:100%;float:left;">
                    <?php
                    //get managers
                    $args = array(
                        'role'      => 'wpc_manager',
                        'orderby'   => 'user_login',
                        'order'     => 'ASC',
                        'fields'    => array( 'ID','user_login' ),

                    );

                    $managers = get_users( $args );

                    if ( is_array( $managers ) && 0 < count( $managers ) ) { ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ),
                                    'text'    => sprintf( __( 'Assign To %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['manager']['p'] )
                                );
                                $input_array = array(
                                    'name'  => 'wpc_managers',
                                    'id'    => 'wpc_managers',
                                    'value' => ''
                                );
                                $additional_array = array(
                                    'counter_value' => 0
                                );

                                $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                                WPC()->assigns()->assign_popup('manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php
                            $groups = WPC()->groups()->get_groups();
                            $selected_groups = array();
                            foreach ( $groups as $group ) {
                                if( '1' == $group['auto_select'] ) {
                                    $selected_groups[] = $group['group_id'];
                                }
                            }

                            $link_array = array(
                                'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                                'text'    => sprintf( __( 'Assign To %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_circles',
                                'id'    => 'wpc_circles',
                                'value' => implode(',', $selected_groups)
                            );
                            $additional_array = array(
                                'counter_value' => count( $selected_groups )
                            );

                            $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                            WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <tr style="height:15px;"><td>&nbsp;</td></tr>
                    <tr>
                        <td style="text-align: center;">
                            <input type="submit" class="button button-primary" name="save" id="save_popup" value="<?php _e( 'Approve', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                            <input type="button" class="button" name="cancel" id="cancel_popup" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </td>
                    </tr>
                </table>
            </form>

            <?php $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            echo json_encode( array(
                'title'     => sprintf( __( 'Approve selected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'content'   => $content
            ) );
            exit;
        }

        echo json_encode( array(
            'title'     => sprintf( __( 'Approve %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
            'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
        ) );
        exit;
    }


    /**
     * Ajax function for get File Edit form
     *
     * @return array json answer to js
     */
    function ajax_file_edit_form() {
        if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
            echo json_encode( array(
                'title'     => __( 'Edit File', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        $id = $_POST['id'];
        global $wpdb;
        $file = $wpdb->get_row( $wpdb->prepare(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_files
                WHERE id=%d",
            $id
        ), ARRAY_A );

        if ( ! empty( $file ) ) {
            $file_title = (isset($file['title']) && '' != $file['title']) ? stripslashes($file['title']) : stripslashes($file['name']);
            $file_category = ( ! empty( $file['cat_id'] ) ) ? $file['cat_id'] : '';

            $external = (isset($file['external']) && $file['external'] == '1') ? '1' : '';


            $data_file_tags = wp_get_object_terms($file['id'], 'wpc_file_tags', array('fields' => 'names'));

            foreach ($data_file_tags as $key => $tag) {
                $data_file_tags[$key] = addslashes($tag);
            }

            $data_file_tags = '"' . implode('","', $data_file_tags) . '"';
            if ('""' == $data_file_tags)
                $data_file_tags = '[]';
            else
                $data_file_tags = '[' . $data_file_tags . ']';

            ob_start(); ?>

            <form method="post" name="wpc_edit_file" id="wpc_edit_file"
                  style="float:left;width:100%;margin:0;padding:0;">
                <input type="hidden" name="edit_file_id" id="edit_file_id" value="<?php echo $id ?>"/>
                <input type="hidden" name="edit_external_file" id="edit_external_file"
                       value="<?php echo $external ?>"/>
                <table style="float:left;width:100%;margin:0;padding:0;table-layout:fixed;">
                    <tr>
                        <td>
                            <label>
                                <?php _e('File Title:', WPC_CLIENT_TEXT_DOMAIN) ?>
                                <br/>
                                <input type="text" name="edit_file_title" size="70" id="edit_file_title"
                                       value="<?php echo $file_title ?>"/>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <?php _e('File Category:', WPC_CLIENT_TEXT_DOMAIN) ?>
                                <br/>
                                <select name="edit_file_cat_id" id="edit_file_cat_id" >
                                    <?php WPC()->files()->render_category_list_items( array(), $file_category ); ?>
                                </select>
                            </label>
                        </td>
                    </tr>
                    <tr <?php if( WPC()->flags['easy_mode'] ) { ?>style="display: none;" <?php } ?>>
                        <td>
                            <?php _e('File Tags:', WPC_CLIENT_TEXT_DOMAIN) ?>
                            <br/>
                            <span id="span_for_file_tags" data-file_tags='<?php echo $data_file_tags ?>'>
                                <textarea id="edit_file_tags" name="edit_file_tags" rows="1"></textarea>
                            </span>
                                <span
                                    class="description"><?php _e('Please press Enter when you wrote title of tag.', WPC_CLIENT_TEXT_DOMAIN) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <?php _e('File Description:', WPC_CLIENT_TEXT_DOMAIN) ?>
                                <br/>
                                    <textarea name="edit_file_description" rows="5"
                                              id="edit_file_description"><?php echo stripslashes($file['description']) ?></textarea>
                            </label>
                        </td>
                    </tr>
                    <?php if (!empty($external)) { ?>
                        <tr>
                            <td>
                                <label>
                                    <?php _e('File URL:', WPC_CLIENT_TEXT_DOMAIN) ?>
                                    <br/>
                                    <input type="text" name="edit_file_url" size="70" id="edit_file_url"
                                           value="<?php echo $file['filename'] ?>"/>
                                </label>
                            </td>
                        </tr>
                        <tr class="edit_file_protect_url_content">
                            <td>
                                <label>
                                    <input type="checkbox" name="edit_file_protect_url" id="edit_file_protect_url"
                                           value="1" <?php checked(isset($file['protect_url']) && $file['protect_url']) ?>>
                                    <b><label
                                            for="edit_file_protect_url"><?php _e('Protect URL', WPC_CLIENT_TEXT_DOMAIN) ?></label></b>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <div style="clear: both; text-align: center;width:100%;float:left;">
                                <input type="button" class="button-primary" id="update_file" name="update_file"
                                       value="<?php _e('Update', WPC_CLIENT_TEXT_DOMAIN) ?>"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>

            <style type="text/css">
                #edit_file_title,
                #edit_file_url {
                    float: left;
                    width: 100%;
                }

                #span_for_file_tags {
                    float: left;
                    width: 100%;
                    position: relative;
                }

                #edit_file_tags {
                    float: left;
                    width: 474px;
                }

                #edit_file_description {
                    float: left;
                    width: 100%;
                    resize: vertical;
                }

                .text-core .text-wrap .text-tags.text-tags-on-top {
                    box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                }
            </style>

            <?php $content = ob_get_contents();
            if (ob_get_length()) {
                ob_end_clean();
            }

            echo json_encode(array(
                'title' => sprintf(__('Edit File: %s', WPC_CLIENT_TEXT_DOMAIN), $file_title),
                'content' => $content
            ));
            exit;
        }


        echo json_encode( array(
            'title'     => __( 'Edit File', WPC_CLIENT_TEXT_DOMAIN ),
            'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
        ) );
        exit;
    }

    /*
        * Ajax function for get client internal notes
        *
        * @return array json answer to js
        */
    function ajax_get_client_internal_notes() {

        if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
            echo json_encode( array(
                'title'     => sprintf( __( '%s Internal Notes', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        if ( !( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_view_client_internal_notes' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
            echo json_encode( array(
                'title'     => sprintf( __( '%s Internal Notes', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        $id = explode( '_', $_POST['id'] );

        //check id and hash
        if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientinternalnote_' . $id[0] ) == $id[1] ) {
            $client = get_userdata( $id[0] );

            $internal_notes = get_user_meta( $id[0], 'wpc__internal_notes', true );
            $internal_notes = ( $internal_notes ) ? $internal_notes : '';

            $readonly_textarea = (!current_user_can('wpc_update_client_internal_notes') && !current_user_can('wpc_admin') && !current_user_can('administrator')) ? true : false;
            ob_start(); ?>

            <?php if( $readonly_textarea ) {
                if( empty( $internal_notes ) ) { ?>
                    <div class="empty_notes"><?php _e( 'Empty Notes', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                <?php } else { ?>
                    <div style="float:left;width:100%;clear:both;"><?php echo $internal_notes ?></div>
                <?php }
            } else { ?>
                <input type="hidden" id="wpc_client_id" value="<?php echo $id[0] . '_' . md5( 'wpcclientinternalnote_' . $id[0] ) ?>" />
                <label style="width:100%;float:left;clear:both;">
                    <?php _e( 'Notes:', WPC_CLIENT_TEXT_DOMAIN ) ?><br />
                    <textarea style="width:100%;float:left;resize:vertical;height:200px;" id="wpc_internal_notes"><?php echo $internal_notes ?></textarea>
                </label>
                <div style="clear: both; text-align: center;width:100%;float:left;margin-top:10px;">
                    <input type="button" class="button-primary" id="update_internal_notes" value="<?php _e( 'Save Notes', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <div class="wpc_ajax_result"></div>
                </div>
            <?php } ?>

            <style type="text/css">
                #update_internal_notes {
                    float:left;
                    margin: 0;
                }

                .wpc_ajax_result {
                    float:left;
                    margin: 0 0 0 15px;
                    padding:0;
                    line-height: 26px;
                }

                .empty_notes {
                    float:left;
                    width:100%;
                    clear:both;
                    height:250px;
                    line-height:250px;
                    text-align:center;
                }
            </style>

            <?php $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            echo json_encode( array(
                'title'     => $client->user_login . ' ' . __( 'Internal Notes', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => $content
            ) );
            exit;
        }

        echo json_encode( array(
            'title'     => sprintf( __( '%s Internal Notes', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
            'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
        ) );
        exit;
    }


    /*
        * Ajax function for update client internal notes
        *
        * @return array json answer to js
        */
    function ajax_update_client_internal_notes() {

        if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
            die( json_encode( array('status' => false, 'message' => __( 'Some problem with update.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        $id = explode( '_', $_POST['id'] );
        //check id and hash
        if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientinternalnote_' . $id[0] ) == $id[1] ) {
            $client = get_userdata( $id[0] );

            if ( $client ) {
                $internal_notes = ( isset( $_POST['notes'] ) ) ? base64_decode( str_replace( '-', '+', $_POST['notes'] ) ) : '';

                update_user_meta( $id[0], 'wpc__internal_notes', $internal_notes );
                die( json_encode( array('status' => true, 'message' => __( 'Notes is updated.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }
        }

        die( json_encode( array('status' => false, 'message' => __( 'Some problem with update.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    /*
        * Ajax function for get user capabilities
        *
        * @return array json answer to js
        */
    function ajax_get_user_capabilities() {

        if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
            echo json_encode( array(
                'title'     => __( 'Individual Capabilitites', WPC_CLIENT_TEXT_DOMAIN ),
                'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
            ) );
            exit;
        }

        $id = explode( '_', $_POST['id'] );

        //check id and hash
        if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && isset( $_POST['wpc_role'] ) && md5( $_POST['wpc_role'] . SECURE_AUTH_SALT . $id[0] ) == $id[1] ) {
            $client = get_userdata( $id[0] );

            $capabilities = '';
            $capabilities_maps = WPC()->get_capabilities_maps();

            if( !isset( $capabilities_maps[ $_POST['wpc_role'] ]['variable'] ) ) {
                exit( json_encode( array( 'capabilities' => '' ) ) );
            }

            $i = 0;
            $center = round( count( $capabilities_maps[ $_POST['wpc_role'] ]['variable'] ) / 2 ) - 1;
            foreach ( $capabilities_maps [$_POST['wpc_role'] ]['variable'] as $cap_name => $cap_val ) {
                if ( '' != $cap_name ) {
                    if ( user_can( $id[0], $cap_name ) ) {
                        $checked = 'checked';
                    } else {
                        $checked = '';
                    }

                    $field_data = array(
                        'type' => 'checkbox',
                        'id' => $cap_name,
                        'name' => $cap_name,
                        'description' => $cap_val['label'],
                        'checked' => $checked,
                    );

                    $capabilities .=  WPC()->settings()->render_setting_field( $field_data ) . '<br style="margin-top: 5px;" />';

                }
                if ( 'wpc_manager' == $_POST['wpc_role'] ) {
                    if( $i == $center )
                        $capabilities .= '</div><div class="wpc_left_col">' ;
                }
                $i++;

            }

            if ( 'wpc_manager' == $_POST['wpc_role'] ) {
                $capabilities = '<div class="wpc_left_col">' . $capabilities . '</div>';
            }

            $content = ( $capabilities ) ? $capabilities :  __( 'Empty Capabilitites', WPC_CLIENT_TEXT_DOMAIN );

            ob_start(); ?>
            <div class="wpc_capabilities_wrapper">
                <input type="hidden" id="wpc_capability_id" value="<?php echo $_POST['id'] ?>" />
                <div id="wpc_all_capabilities"><?php echo $content ?></div>
                <input type="button" class="button-primary" id="update_wpc_capabilities" value="<?php _e( 'Save Capabilities', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                <div class="wpc_ajax_result"></div>
            </div>

            <style type="text/css">
                #update_wpc_capabilities {
                    float:left;
                    margin: 0;
                }

                .wpc_ajax_result {
                    float:left;
                    margin: 0 0 0 15px;
                    padding:0;
                    line-height: 26px;
                }

                #wpc_all_capabilities {
                    float:left;
                    width:100%;
                    margin:0 0 10px 0;
                    padding:0;
                }

                .wpc_capabilities_wrapper {
                    float:left;
                    width:100%;
                    margin:0;
                    padding:0;
                }
            </style>

            <?php $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            echo json_encode( array(
                'title'     => sprintf( __( 'Capabilitites for: %s', WPC_CLIENT_TEXT_DOMAIN ), $client->user_login ),
                'content'   => $content
            ) );
            exit;
        }

        echo json_encode( array(
            'title'     => __( 'Individual Capabilitites', WPC_CLIENT_TEXT_DOMAIN ),
            'content'   => __( 'Wrong Data', WPC_CLIENT_TEXT_DOMAIN )
        ) );
        exit;
    }


    /*
        * Ajax function for update user capabilies
        *
        * @return array json answer to js
        */
    function ajax_update_capabilities() {

        if ( !current_user_can( 'wpc_edit_cap_clients' ) && !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
            die( json_encode( array('status' => false, 'message' => __( 'Permission Error.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        } elseif ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
            die( json_encode( array('status' => false, 'message' => __( 'Some problem with update.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        $id = explode( '_', $_POST['id'] );

        //check id and hash
        if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && isset( $_POST['wpc_role'] ) && md5( $_POST['wpc_role'] . SECURE_AUTH_SALT . $id[0] ) == $id[1] ) {

            $client = get_userdata( $id[0] );

            if ( $client && isset( $_POST['capabilities'] ) ) {
                $user = new WP_User( $id[0] );

                update_user_meta( $id[0], 'wpc_individual_caps', true );

                $capabilities_maps = WPC()->get_capabilities_maps();
                $capabilities = json_decode( stripslashes(  $_POST['capabilities'] ), true );
                foreach ( $capabilities as $cap_name => $cap_value ) {
                    if( isset( $capabilities_maps[ $_POST['wpc_role'] ]['variable'][$cap_name] ) ) {
                        $cap_value = ( 'yes' == $cap_value ) ? true : false ;
                        $user->add_cap( $cap_name, $cap_value ) ;
                    }

                }
                die( json_encode( array('status' => true, 'message' => __( 'Capabilities is updated.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }
        }

        die( json_encode( array('status' => false, 'message' => __( 'Some problem with update.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }



    function ajax_upload_avatar() {
        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $avatars_dir = WPC()->get_upload_dir( 'wpclient/_avatars/', 'allow' );
        $fileinfo = pathinfo( $_FILES['Filedata']['name'] );

        $new_name       = 'temp_' . time() . '_' . uniqid() . '.' . $fileinfo['extension'];
        $target_path    = $avatars_dir . DIRECTORY_SEPARATOR . $new_name;

        $image_sizes = getimagesize( $_FILES['Filedata']['tmp_name'] );

        if( isset( $image_sizes['mime'] ) && ( $image_sizes['mime'] == 'image/png' || $image_sizes['mime'] == 'image/jpeg' ) ) {
            $width = $image_sizes[0];
            $height = $image_sizes[1];

	        $default_sizes = array(
		        'height' => 128,
		        'width' => 128,
	        );

	        /*our_hook_
				hook_name: wp_client_avatar_sizes
				hook_title: Client Avatar Sizes
				hook_description: Hook runs before client avatar resize.
				hook_type: filter
				hook_in: wp-client
				hook_location class.ajax.php
				hook_param: array $default_sizes
				hook_since: 4.5.4.1
			*/
	        $default_sizes = apply_filters( 'wp_client_avatar_sizes', $default_sizes );

            $image = wp_get_image_editor( $_FILES['Filedata']['tmp_name'] );
	        if( !is_wp_error( $image ) ) {
		        if( ! empty( $default_sizes['width'] ) && ! empty( $default_sizes['height'] ) ) {
			        if ( $width == $height ) {
				        $image->resize( $default_sizes['width'], $default_sizes['height'], false );
			        } else {
				        if ( $width > $height ) {
					        $image->crop(ceil( ( $width - $height ) / 2), 0, $height, $height );
					        $image->resize ( $default_sizes['width'], $default_sizes['height'], false );
				        } else {
					        $image->crop( 0, ceil(( $height - $width ) / 2), $width, $width );
					        $image->resize( $default_sizes['width'], $default_sizes['height'], false );
				        }
			        }
		        }

		        $image->save( $target_path );
	        } else {
		        if( !move_uploaded_file( $_FILES['Filedata']['tmp_name'], $target_path ) ) {
			        $msg = __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN );
			        exit( $msg );
		        }
	        }
        } else {
            $msg = __( 'Your file is not image, please try again!', WPC_CLIENT_TEXT_DOMAIN );
            exit( $msg );
        }

        echo $new_name;
        exit;
    }


    function ajax_avatar_remove() {
        if( isset( $_POST['user_id'] ) && !empty( $_POST['user_id'] ) ) {
            delete_user_meta( $_POST['user_id'], 'wpc_avatar' );

            $current_avatar = WPC()->members()->wpc_get_avatar_src( $_POST['user_id'] );
            $current_avatar = html_entity_decode( $current_avatar );
            exit( json_encode( array( 'status' => true, 'current_avatar' => $current_avatar, 'is_gravatar' => WPC()->members()->is_avatar_gravatar( $_POST['user_id'] ) ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_get_cc_members() {
        $selectbox_content = '';

        if( isset( $_POST['to'] ) && !empty( $_POST['to'] ) ) {
            $to = json_decode( base64_decode( $_POST['to'] ) );

            $wpc_private_messages = WPC()->get_settings( 'private_messages' );
            $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

            if( is_array( $to ) && count( $to ) == 1 ) {
                if( is_numeric( $to[0] ) ) {
                    //administrator or WPC Admin selected
                    if( user_can( $to[0], 'administrator' ) || user_can( $to[0], 'wpc_admin' ) ) {

                        //managers
                        if( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) {
                            if( ( current_user_can( 'wpc_client' ) && !( isset( $wpc_private_messages['relate_client_manager'] ) && 'no' == $wpc_private_messages['relate_client_manager'] ) ) ||
                                ( current_user_can( 'wpc_client_staff' ) && !( isset( $wpc_private_messages['relate_staff_manager'] ) && 'no' == $wpc_private_messages['relate_staff_manager'] ) ) ) {

                                if ( current_user_can( 'wpc_client' ) ) {
                                    $client_id = get_current_user_id();
                                } elseif (current_user_can('wpc_client_staff')) {
                                    $client_id = get_user_meta(get_current_user_id(), 'parent_client_id', true);
                                }

                                $client_managers = WPC()->assigns()->get_assign_data_by_assign('manager', 'client', $client_id);
                                $client_circles = WPC()->groups()->get_client_groups_id($client_id);
                                $circles_managers = WPC()->assigns()->get_assign_data_by_assign('manager', 'circle', $client_circles);
                                $managers = array_merge($client_managers, $circles_managers);
                                $managers = array_diff($managers, array($to[0], get_current_user_id()));

                                if( !empty( $managers ) ) {
                                    $args = array(
                                        'role' => 'wpc_manager',
                                        'include' => $managers,
                                        'exclude' => array($to[0], get_current_user_id()),
                                        'orderby' => 'user_login',
                                        'order' => 'ASC',
                                    );
                                }
                            }
                        } elseif( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                            if( !WPC()->flags['easy_mode'] ) {
                                $args = array(
                                    'role'      => 'wpc_manager',
                                    'exclude'   => array($to[0], get_current_user_id()),
                                    'orderby'   => 'user_login',
                                    'order'     => 'ASC',
                                );
                            }
                        }
                        $wpc_managers = ( isset( $args ) ) ? get_users( $args ) : '';


                        if( is_array( $wpc_managers ) && !empty( $wpc_managers ) ) {
                            $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['manager']['p'] . '" data-single_title="' . WPC()->custom_titles['manager']['s'] . '" data-color="#dc832d">';
                            foreach( $wpc_managers as $user ) {
                                $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                            }
                            $selectbox_content .= '</optgroup>';
                        }

                        //admins
                        $wpc_admins = array();
                        $administrators = array();
                        if( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                            if( !WPC()->flags['easy_mode'] ) {
                                $args = array(
                                    'role'      => 'wpc_admin',
                                    'exclude'   => array( $to[0], get_current_user_id() ),
                                    'orderby'   => 'user_login',
                                    'order'     => 'ASC',
                                );
                                $wpc_admins = get_users( $args );
                            }

                            $args = array(
                                'role'      => 'administrator',
                                'exclude'   => array( $to[0], get_current_user_id() ),
                                'orderby'   => 'user_login',
                                'order'     => 'ASC',
                            );
                            $administrators = get_users( $args );
                        } else {
                            if( isset( $wpc_private_messages['front_end_admins'] ) && !empty( $wpc_private_messages['front_end_admins'] ) ) {

                                $wpc_private_messages['front_end_admins'] = array_diff( $wpc_private_messages['front_end_admins'], array( $to[0] ) );

                                if ( $wpc_private_messages['front_end_admins'] ) {

                                    $args = array(
                                        'role' => 'wpc_admin',
                                        'include' => $wpc_private_messages['front_end_admins'],
                                        'exclude'   => array( $to[0], get_current_user_id() ),
                                        'orderby' => 'user_login',
                                        'order' => 'ASC',
                                    );
                                    $wpc_admins = get_users($args);

                                    $args = array(
                                        'role' => 'administrator',
                                        'include' => $wpc_private_messages['front_end_admins'],
                                        'exclude'   => array( $to[0], get_current_user_id() ),
                                        'orderby' => 'user_login',
                                        'order' => 'ASC',
                                    );
                                    $administrators = get_users($args);
                                }
                            }
                        }

                        $administrators = array_merge( $administrators, $wpc_admins );

                        if( is_array( $administrators ) && !empty( $administrators ) ) {
                            $selectbox_content .= '<optgroup label="' . __( 'Admins', WPC_CLIENT_TEXT_DOMAIN ) . '" data-single_title="' . __( 'Admin', WPC_CLIENT_TEXT_DOMAIN ) . '" data-color="#b63ad0">';
                            foreach( $administrators as $user ) {
                                $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                            }
                            $selectbox_content .= '</optgroup>';
                        }

                    } elseif( user_can( $to[0], 'wpc_manager' ) ) {
                        //manager selected

                        //clients
                        if( !current_user_can( 'wpc_client' ) && !current_user_can( 'wpc_client_staff' ) ) {
                            $manager_clients = WPC()->assigns()->get_assign_data_by_object('manager', $to[0], 'client');

                            if ( !empty( $manager_clients ) ) {
                                $args = array(
                                    'role' => 'wpc_client',
                                    'include' => $manager_clients,
                                    'exclude' => get_current_user_id(),
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $wpc_clients = get_users($args);

                                if (is_array($wpc_clients) && !empty($wpc_clients)) {
                                    $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['client']['p'] . '" data-single_title="' . WPC()->custom_titles['client']['s'] . '" data-color="#0073aa">';
                                    foreach ($wpc_clients as $user) {
                                        $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                                    }
                                    $selectbox_content .= '</optgroup>';
                                }

                                $args = array(
                                    'role' => 'wpc_client_staff',
                                    'meta_query' => array(array(
                                        'key' => 'parent_client_id',
                                        'value' => $manager_clients,
                                        'compare' => 'IN'
                                    )),
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );

                                $wpc_client_staffs = get_users($args);

                                if (is_array($wpc_client_staffs) && !empty($wpc_client_staffs)) {
                                    $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['p'] . '" data-single_title="' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['s'] . '" data-color="#2da3dc">';
                                    foreach ($wpc_client_staffs as $user) {

                                        $staff_client_id = get_user_meta($user->ID, 'parent_client_id', true);
                                        $staff_client = get_user_by('id', $staff_client_id);

                                        $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . ' (' . ( !empty( $staff_client->$display_name ) ? $staff_client->$display_name : $staff_client->user_login ). ' ' . WPC()->custom_titles['client']['s'] . ')</option>';
                                    }
                                    $selectbox_content .= '</optgroup>';
                                }
                            }
                        }

                        unset( $args );
                        //managers
                        if( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) {
                            if( ( current_user_can( 'wpc_client' ) && !( isset( $wpc_private_messages['relate_client_manager'] ) && 'no' == $wpc_private_messages['relate_client_manager'] ) ) ||
                                ( current_user_can( 'wpc_client_staff' ) && !( isset( $wpc_private_messages['relate_staff_manager'] ) && 'no' == $wpc_private_messages['relate_staff_manager'] ) ) ) {

                                if ( current_user_can( 'wpc_client' ) ) {
                                    $client_id = get_current_user_id();
                                } elseif ( current_user_can( 'wpc_client_staff' ) ) {
                                    $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
                                }

                                $client_managers = WPC()->assigns()->get_assign_data_by_assign('manager', 'client', $client_id);
                                $client_circles = WPC()->groups()->get_client_groups_id($client_id);
                                $circles_managers = WPC()->assigns()->get_assign_data_by_assign('manager', 'circle', $client_circles);
                                $managers = array_merge($client_managers, $circles_managers);
                                $managers = array_diff($managers, array($to[0], get_current_user_id()));
                                if( !empty( $managers ) ) {
                                    $args = array(
                                        'role' => 'wpc_manager',
                                        'include' => $managers,
                                        'exclude' => array($to[0], get_current_user_id()),
                                        'orderby' => 'user_login',
                                        'order' => 'ASC',
                                    );
                                }
                            }
                        } elseif( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                            $args = array(
                                'role'      => 'wpc_manager',
                                'exclude'   => array($to[0], get_current_user_id()),
                                'orderby'   => 'user_login',
                                'order'     => 'ASC',
                            );
                        }
                        $wpc_managers = ( isset( $args ) ) ? get_users( $args ) : '';

                        if( is_array( $wpc_managers ) && !empty( $wpc_managers ) ) {
                            $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['manager']['p'] . '" data-single_title="' . WPC()->custom_titles['manager']['s'] . '" data-color="#dc832d">';
                            foreach( $wpc_managers as $user ) {
                                $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                            }
                            $selectbox_content .= '</optgroup>';
                        }

                        //admins
                        if( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) {
                            if ( isset($wpc_private_messages['front_end_admins'] ) && !empty( $wpc_private_messages['front_end_admins'] ) ) {
                                $args = array(
                                    'role' => 'wpc_admin',
                                    'include' => $wpc_private_messages['front_end_admins'],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $wpc_admins = get_users($args);

                                $args = array(
                                    'role' => 'administrator',
                                    'include' => $wpc_private_messages['front_end_admins'],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $administrators = get_users($args);
                            } else {
                                $administrators = array();
                                $wpc_admins = array();
                            }
                        } else {
                            $args = array(
                                'role' => 'wpc_admin',
                                'exclude' => array($to[0], get_current_user_id()),
                                'orderby' => 'user_login',
                                'order' => 'ASC',
                            );
                            $wpc_admins = get_users($args);

                            $args = array(
                                'role' => 'administrator',
                                'exclude' => array($to[0], get_current_user_id()),
                                'orderby' => 'user_login',
                                'order' => 'ASC',
                            );
                            $administrators = get_users( $args );
                        }

                        $administrators = array_merge( $administrators, $wpc_admins );

                        if ( is_array( $administrators ) && !empty( $administrators ) ) {
                            $selectbox_content .= '<optgroup label="' . __('Admins', WPC_CLIENT_TEXT_DOMAIN) . '" data-single_title="' . __('Admin', WPC_CLIENT_TEXT_DOMAIN) . '" data-color="#b63ad0">';
                            foreach ($administrators as $user) {
                                $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                            }
                            $selectbox_content .= '</optgroup>';
                        }

                    } elseif( user_can( $to[0], 'wpc_client' ) ) {
                        //client selected

                        //staff
                        if( !WPC()->flags['easy_mode'] ) {
                            if( !current_user_can( 'wpc_client_staff' ) ) {
                                $args = array(
                                    'role' => 'wpc_client_staff',
                                    'meta_key' => 'parent_client_id',
                                    'meta_value' => $to[0],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );

                                $wpc_client_staffs = get_users($args);

                                if (is_array($wpc_client_staffs) && !empty($wpc_client_staffs)) {
                                    $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['p'] . '" data-single_title="' . WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['s'] . '" data-color="#2da3dc">';
                                    foreach ($wpc_client_staffs as $user) {
                                        $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                                    }
                                    $selectbox_content .= '</optgroup>';
                                }
                            }


                            unset( $args );
                            //managers
                            if( !current_user_can( 'wpc_manager' ) ) {
                                if( !current_user_can( 'wpc_client_staff' ) || !( isset( $wpc_private_messages['relate_staff_manager'] ) && 'no' == $wpc_private_messages['relate_staff_manager'] ) ) {
                                    $client_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $to[0] );

                                    $client_circles = WPC()->groups()->get_client_groups_id( $to[0] );
                                    $circles_managers = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'circle', $client_circles );

                                    $managers = array_merge( $client_managers, $circles_managers );
                                    if( !empty( $managers ) ) {
                                        $args = array(
                                            'role' => 'wpc_manager',
                                            'include' => $managers,
                                            'exclude' => get_current_user_id(),
                                            'orderby' => 'user_login',
                                            'order' => 'ASC',
                                        );
                                    }
                                }
                            }

                            $managers = ( isset( $args ) ) ? get_users( $args ) : '';

                            if( is_array( $managers ) && !empty( $managers ) ) {
                                $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['manager']['p'] . '" data-single_title="' . WPC()->custom_titles['manager']['s'] . '" data-color="#dc832d">';
                                foreach( $managers as $user ) {
                                    $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                                }
                                $selectbox_content .= '</optgroup>';
                            }
                        }

                        //admins
                        $administrators = array();
                        $wpc_admins = array();
                        if( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) {
                            if ( isset($wpc_private_messages['front_end_admins'] ) && !empty( $wpc_private_messages['front_end_admins'] ) ) {
                                $args = array(
                                    'role' => 'wpc_admin',
                                    'include' => $wpc_private_messages['front_end_admins'],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $wpc_admins = get_users($args);

                                $args = array(
                                    'role' => 'administrator',
                                    'include' => $wpc_private_messages['front_end_admins'],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $administrators = get_users($args);
                            }
                        } else {
                            if( !WPC()->flags['easy_mode'] ) {
                                $args = array(
                                    'role' => 'wpc_admin',
                                    'exclude' => array($to[0], get_current_user_id()),
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $wpc_admins = get_users($args);
                            }

                            $args = array(
                                'role' => 'administrator',
                                'exclude' => array($to[0], get_current_user_id()),
                                'orderby' => 'user_login',
                                'order' => 'ASC',
                            );
                            $administrators = get_users( $args );
                        }

                        $administrators = array_merge( $administrators, $wpc_admins );

                        if ( is_array( $administrators ) && !empty( $administrators ) ) {
                            $selectbox_content .= '<optgroup label="' . __('Admins', WPC_CLIENT_TEXT_DOMAIN) . '" data-single_title="' . __('Admin', WPC_CLIENT_TEXT_DOMAIN) . '" data-color="#b63ad0">';
                            foreach ($administrators as $user) {
                                $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                            }
                            $selectbox_content .= '</optgroup>';
                        }

                    } elseif( user_can( $to[0], 'wpc_client_staff' ) ) {
                        //staff selected

                        //client
                        $client_id = get_user_meta( $to[0], 'parent_client_id', true );

                        if( $client_id != get_current_user_id() ) {
                            $client = get_user_by('id', $client_id);
                            $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['client']['p'] . '" data-single_title="' . WPC()->custom_titles['client']['s'] . '" data-color="#0073aa">
                                    <option value="' . $client_id . '">' . ( !empty( $client->$display_name ) ? $client->$display_name : $client->user_login ) . '</option>
                                </optgroup>';
                        }

                        //manager
                        if( !current_user_can( 'wpc_manager' ) ) {
                            if( !current_user_can( 'wpc_client' ) || !( isset( $wpc_private_messages['relate_client_manager'] ) && 'no' == $wpc_private_messages['relate_client_manager'] ) ) {

                                $client_managers = WPC()->assigns()->get_assign_data_by_assign('manager', 'client', $client_id);
                                $client_circles = WPC()->groups()->get_client_groups_id($client_id);
                                $circles_managers = WPC()->assigns()->get_assign_data_by_assign('manager', 'circle', $client_circles);
                                $managers = array_merge($client_managers, $circles_managers);

                                if (!empty($managers)) {
                                    $args = array(
                                        'role' => 'wpc_manager',
                                        'include' => $managers,
                                        'exclude' => get_current_user_id(),
                                        'orderby' => 'user_login',
                                        'order' => 'ASC',
                                    );
                                    $managers = get_users($args);

                                    if (is_array($managers) && !empty($managers)) {
                                        $selectbox_content .= '<optgroup label="' . WPC()->custom_titles['manager']['p'] . '" data-single_title="' . WPC()->custom_titles['manager']['s'] . '" data-color="#dc832d">';
                                        foreach ($managers as $user) {
                                            $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                                        }
                                        $selectbox_content .= '</optgroup>';
                                    }
                                }
                            }
                        }

                        //admins
                        if( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) {
                            if ( isset($wpc_private_messages['front_end_admins'] ) && !empty( $wpc_private_messages['front_end_admins'] ) ) {
                                $args = array(
                                    'role' => 'wpc_admin',
                                    'include' => $wpc_private_messages['front_end_admins'],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $wpc_admins = get_users($args);

                                $args = array(
                                    'role' => 'administrator',
                                    'include' => $wpc_private_messages['front_end_admins'],
                                    'orderby' => 'user_login',
                                    'order' => 'ASC',
                                );
                                $administrators = get_users($args);
                            } else {
                                $administrators = array();
                                $wpc_admins = array();
                            }
                        } else {
                            $args = array(
                                'role' => 'wpc_admin',
                                'exclude' => array($to[0], get_current_user_id()),
                                'orderby' => 'user_login',
                                'order' => 'ASC',
                            );
                            $wpc_admins = get_users($args);

                            $args = array(
                                'role' => 'administrator',
                                'exclude' => array($to[0], get_current_user_id()),
                                'orderby' => 'user_login',
                                'order' => 'ASC',
                            );
                            $administrators = get_users( $args );
                        }

                        $administrators = array_merge( $administrators, $wpc_admins );

                        if ( is_array( $administrators ) && !empty( $administrators ) ) {
                            $selectbox_content .= '<optgroup label="' . __('Admins', WPC_CLIENT_TEXT_DOMAIN) . '" data-single_title="' . __('Admin', WPC_CLIENT_TEXT_DOMAIN) . '" data-color="#b63ad0">';
                            foreach ($administrators as $user) {
                                $selectbox_content .= '<option value="' . $user->ID . '">' . ( !empty( $user->$display_name ) ? $user->$display_name : $user->user_login ) . '</option>';
                            }
                            $selectbox_content .= '</optgroup>';
                        }
                    }
                }
            }

            exit( json_encode( array( 'status' => true, 'selectbox_content' => $selectbox_content ) ) );
        } else {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }
    }


    function ajax_message_get_list() {
        global $wpdb;

        if( isset( $_POST['type'] ) && !empty( $_POST['type'] ) ) {

            if( !in_array( $_POST['type'], array( 'all', 'inbox', 'sent', 'archive', 'trash' ) ) ) {
                exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }

            $items_count = 0;
            $chains = array();

            $GLOBALS['hook_suffix'] = 'wpc_message_load_chains_list';

            if( !class_exists( 'WPC_Messages_List_Table' ) ) {
                require_once( WPC()->plugin_dir . 'includes/admin/class.messages_list_table.php' );
            }

            $ListTable = new WPC_Messages_List_Table( array(
                'singular'  => __( 'Message', WPC_CLIENT_TEXT_DOMAIN ),
                'plural'    => __( 'Messages', WPC_CLIENT_TEXT_DOMAIN ),
                'ajax'      => false
            ));

            $per_page   = 25;
            $paged      = ( isset( $_POST['page'] ) && !empty( $_POST['page'] ) ) ? $_POST['page'] : 1;

            $ListTable->set_columns(array(
                'cb'               => '<input type="checkbox" />',
                'client_ids'       => __( 'From', WPC_CLIENT_TEXT_DOMAIN ),
                'message_text'     => '',
                'date'             => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
            ));

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $current_user_id = $_POST['client_id'];
            } else {
                $current_user_id = get_current_user_id();
            }

            $search = '';
            if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                $search = " AND ( c.subject LIKE '%{$_POST['search']}%' OR m.content LIKE '%{$_POST['search']}%')";
            }


            $filter = '';
            if( isset( $_POST['filters'] ) && !empty( $_POST['filters'] ) ) {

                $_POST['filters'] = json_decode( base64_decode( $_POST['filters'] ) );
                $_POST['filters'] = (array)$_POST['filters'];

                foreach( $_POST['filters'] as $type=>$value ) {

                    if( empty( $value ) ) {
                        continue;
                    }

                    if( 'date' == $type ) {
                        $value = (array)$value;

                        if( $filter != '' ) {
                            $filter .= " AND ";
                        }

                        if( empty( $value['from'] ) ) {
                            $value['from'] = 0;
                        }

                        $value['from'] = mktime( 0, 0, 0, date( "m", $value['from'] ), date( "d", $value['from'] ), date( "y", $value['from'] ) );
                        $value['to'] = mktime( 0, 0, 0, date( "m", $value['to'] ), date( "d", $value['to'] ) + 1, date( "y", $value['to'] ) ) - 1;

                        $filter .= " ( m.date >= {$value['from']} AND m.date <= {$value['to']} )";
                    } elseif( 'member' == $type ) {
                        if( $filter != '' ) {
                            $filter .= " AND ";
                        }

                        $chain_ids = array();

                        foreach( $value as $client_id ) {
                            if( !empty( $client_id ) ) {
                                $result = WPC()->assigns()->get_assign_data_by_assign('chain', 'client', $client_id);

                                if (!empty($result)) {
                                    $chain_ids = array_merge($chain_ids, $result);
                                }
                            }
                        }

                        $chain_ids = array_unique( $chain_ids );
                        $chain_ids = implode( "','", $chain_ids );

                        $filter .= " c.id IN( '{$chain_ids}' )";
                    }
                }

                if( $filter != '' ) {
                    $filter = " AND (" . $filter . ")";
                }
            }

            //get archived chains
            $client_archive_chains = WPC()->assigns()->get_assign_data_by_assign( 'archive_chain', 'client', $current_user_id );

            //get trashed chains
            $client_trash_chains = WPC()->assigns()->get_assign_data_by_assign( 'trash_chain', 'client', $current_user_id );

            //get markers for chains with new messages
            $client_new_messages = WPC()->assigns()->get_assign_data_by_assign( 'new_message', 'client', $current_user_id );

            $new_query = '';
            $not_new_query = '';
            $new_marker_chains = 0;
            $wpc_private_messages = WPC()->get_settings( 'private_messages' );
            if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] ) {
                $new_marker_chains = $wpdb->get_col(
                    "SELECT DISTINCT c.id
                        FROM {$wpdb->prefix}wpc_client_chains c,
                             {$wpdb->prefix}wpc_client_messages m
                        WHERE c.id = m.chain_id AND
                              m.id IN('" . implode( "','", $client_new_messages ) . "')"
                );

                if( !empty( $new_marker_chains ) ) {
                    $new_query = " AND c.id IN('" . implode( "','", $new_marker_chains ) . "')";
                    $not_new_query = " AND c.id NOT IN('" . implode( "','", $new_marker_chains ) . "')";
                } else {
                    $new_marker_chains = 0;
                }
            }

            $ids = array();
            switch( $_POST['type'] ) {
                case 'all': {
                    $manager_query = '';
                    if( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_all_private_messages' ) ) {
                        $managers_chain_ids = array();
                        $clients_ids = WPC()->members()->get_all_clients_manager();

                        foreach( $clients_ids as $client_id ) {
                            $result = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $client_id );

                            if( !empty( $result ) ) {
                                $managers_chain_ids = array_merge( $managers_chain_ids, $result );
                            }
                        }

                        $manager_query = " AND c.id IN('" . implode( "','", $managers_chain_ids ) . "')";
                    }

                    $sql = "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id
                                $manager_query
                                $search
                                $filter
                            GROUP BY m.chain_id";
                    $ids = $wpdb->get_col( $sql );
                    $items_count = count( $ids );

                    $new_chains = array();
                    if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                        $new_marker_chains = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id
                                    $new_query
                                    $manager_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $new_marker_chains = count( $new_marker_chains );

                        $pages_with_new = ceil( $new_marker_chains / $per_page );
                        if( $pages_with_new == 0 )
                            $pages_with_new = 1;

                        if( $pages_with_new >= $paged ) {
                            $new_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '1' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id
                                        $new_query
                                        $manager_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                ARRAY_A );
                        }
                    }

                    $old_chains = array();
                    if( count( $new_chains ) < $per_page ) {
                        $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                        $old_chains = $wpdb->get_results(
                            "SELECT *,
                                      COUNT( m.id ) AS messages_count,
                                      m.id AS message_id,
                                      c.id AS c_id,
                                      '0' AS is_new
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id
                                    $not_new_query
                                    $manager_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id
                                ORDER BY m.date DESC
                                LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                            ARRAY_A );
                    }

                    $chains = array_merge( $new_chains, $old_chains );


                    $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                    $inbox = $wpdb->get_col(
                        "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                WHERE m.author_id <> '{$current_user_id}'
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id AND
                                c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                $search
                                $filter
                            GROUP BY m.chain_id"
                    );

                    $sent = $wpdb->get_col(
                        "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                WHERE m.author_id='{$current_user_id}'
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id AND
                                c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                $search
                                $filter
                            GROUP BY m.chain_id"
                    );

                    $ListTable->inbox = $inbox;
                    $ListTable->sent = $sent;
                    $ListTable->archive = $client_archive_chains;
                    $ListTable->trash = $client_trash_chains;
                    break;
                }
                case 'inbox': {
                    $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                    $ids = $wpdb->get_col(
                        "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                WHERE m.author_id <> '{$current_user_id}'
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id AND
                                c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                $search
                                $filter
                            GROUP BY m.chain_id"
                    );
                    $items_count = count( $ids );

                    $new_chains = array();
                    if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                        $new_marker_chains = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                      FROM {$wpdb->prefix}wpc_client_messages m
                                      WHERE m.author_id <> '{$current_user_id}'
                                      ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $new_marker_chains = count( $new_marker_chains );

                        $pages_with_new = ceil( $new_marker_chains / $per_page );
                        if( $pages_with_new == 0 )
                            $pages_with_new = 1;

                        if( $pages_with_new >= $paged ) {
                            $new_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '1' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                          FROM {$wpdb->prefix}wpc_client_messages m
                                          WHERE m.author_id <> '{$current_user_id}'
                                          ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                ARRAY_A );
                        }
                    }

                    $old_chains = array();
                    if( count( $new_chains ) < $per_page ) {
                        $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                        $old_chains = $wpdb->get_results(
                            "SELECT *,
                                      COUNT( m.id ) AS messages_count,
                                      m.id AS message_id,
                                      c.id AS c_id,
                                      '0' AS is_new
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                      FROM {$wpdb->prefix}wpc_client_messages m
                                      WHERE m.author_id <> '{$current_user_id}'
                                      ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $not_new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id
                                ORDER BY m.date DESC
                                LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                            ARRAY_A );
                    }

                    $chains = array_merge( $new_chains, $old_chains );

                    foreach( $chains as $key=>$chain ) {
                        $all_chain_messages = $wpdb->get_col(
                            "SELECT id
                                FROM {$wpdb->prefix}wpc_client_messages
                                WHERE chain_id='{$chain['c_id']}'"
                        );

                        $chains[$key]['messages_count'] = count($all_chain_messages);
                    }
                    break;
                }
                case 'sent': {
                    $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                    $ids = $wpdb->get_col(
                        "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                WHERE m.author_id='{$current_user_id}'
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id AND
                                c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                $search
                                $filter
                            GROUP BY m.chain_id"
                    );
                    $items_count = count( $ids );

                    $new_chains = array();
                    if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                        $new_marker_chains = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id='{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $new_marker_chains = count( $new_marker_chains );

                        $pages_with_new = ceil( $new_marker_chains / $per_page );
                        if( $pages_with_new == 0 )
                            $pages_with_new = 1;

                        if( $pages_with_new >= $paged ) {
                            $new_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '1' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        WHERE m.author_id='{$current_user_id}'
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                ARRAY_A );
                        }
                    }

                    $old_chains = array();
                    if( count( $new_chains ) < $per_page ) {
                        $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                        $old_chains = $wpdb->get_results(
                            "SELECT *,
                                      COUNT( m.id ) AS messages_count,
                                      m.id AS message_id,
                                      c.id AS c_id,
                                      '0' AS is_new
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id='{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $not_new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id
                                ORDER BY m.date DESC
                                LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                            ARRAY_A );
                    }

                    $chains = array_merge( $new_chains, $old_chains );

                    foreach( $chains as $key=>$chain ) {
                        $all_chain_messages = $wpdb->get_col(
                            "SELECT id
                                FROM {$wpdb->prefix}wpc_client_messages
                                WHERE chain_id='{$chain['c_id']}'"
                        );

                        $chains[$key]['messages_count'] = count($all_chain_messages);
                    }
                    break;
                }
                case 'archive': {
                    if( !empty( $client_archive_chains ) ) {
                        $ids = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $items_count = count( $ids );

                        $new_chains = array();
                        if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                            $new_marker_chains = $wpdb->get_col(
                                "SELECT c.id
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id"
                            );
                            $new_marker_chains = count( $new_marker_chains );

                            $pages_with_new = ceil( $new_marker_chains / $per_page );
                            if( $pages_with_new == 0 )
                                $pages_with_new = 1;

                            if( $pages_with_new >= $paged ) {
                                $new_chains = $wpdb->get_results(
                                    "SELECT *,
                                              COUNT( m.id ) AS messages_count,
                                              m.id AS message_id,
                                              c.id AS c_id,
                                              '1' AS is_new
                                        FROM {$wpdb->prefix}wpc_client_chains c,
                                            ( SELECT *
                                            FROM {$wpdb->prefix}wpc_client_messages m
                                            ORDER BY m.date DESC ) AS m
                                        WHERE m.chain_id = c.id AND
                                            c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                            $new_query
                                            $search
                                            $filter
                                        GROUP BY m.chain_id
                                        ORDER BY m.date DESC
                                        LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                    ARRAY_A );
                            }
                        }

                        $old_chains = array();
                        if( count( $new_chains ) < $per_page ) {
                            $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                            $old_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '0' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                        $not_new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                                ARRAY_A );
                        }

                        $chains = array_merge( $new_chains, $old_chains );
                    }
                    break;
                }
                case 'trash': {
                    if( !empty( $client_trash_chains ) ) {
                        $ids = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $items_count = count( $ids );

                        $new_chains = array();
                        if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                            $new_marker_chains = $wpdb->get_col(
                                "SELECT c.id
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id"
                            );
                            $new_marker_chains = count( $new_marker_chains );

                            $pages_with_new = ceil( $new_marker_chains / $per_page );
                            if( $pages_with_new == 0 )
                                $pages_with_new = 1;

                            if( $pages_with_new >= $paged ) {
                                $new_chains = $wpdb->get_results(
                                    "SELECT *,
                                              COUNT( m.id ) AS messages_count,
                                              m.id AS message_id,
                                              c.id AS c_id,
                                              '1' AS is_new
                                        FROM {$wpdb->prefix}wpc_client_chains c,
                                            ( SELECT *
                                            FROM {$wpdb->prefix}wpc_client_messages m
                                            ORDER BY m.date DESC ) AS m
                                        WHERE m.chain_id = c.id AND
                                            c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                            $new_query
                                            $search
                                            $filter
                                        GROUP BY m.chain_id
                                        ORDER BY m.date DESC
                                        LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                    ARRAY_A );
                            }
                        }

                        $old_chains = array();
                        if( count( $new_chains ) < $per_page ) {
                            $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                            $old_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '0' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $not_new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                                ARRAY_A );
                        }

                        $chains = array_merge( $new_chains, $old_chains );
                    }

                    break;
                }
            }

            //set empty marker if 0 chains
            $is_empty = false;
            $pagination = array(
                'current_page'  => $paged,
                'start'         => $per_page * ( $paged - 1 ) + 1,
                'end'           => ( $per_page * ( $paged - 1 ) + $per_page < $items_count ) ? $per_page * ( $paged - 1 ) + $per_page : $items_count,
                'count'         => $items_count,
                'pages_count'   => ceil( $items_count/$per_page )
            );
            if( empty( $chains ) ) {
                $is_empty = true;
                $pagination = false;
            }

            //build HTML for table
            $ListTable->prepare_items();
            $ListTable->items = $chains;
            $ListTable->list_type = $_POST['type'];

            ob_start();

            $ListTable->display();

            $html = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            exit( json_encode( array(
                'status'        => true,
                'html'          => $html,
                'is_empty'      => $is_empty,
                'pagination'    => $pagination,
                'ids'           => $ids
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_get_filter() {
        global $wpdb;

        if( isset( $_POST['type'] ) && !empty( $_POST['type'] ) && isset( $_POST['by'] ) && !empty( $_POST['by'] ) ) {

            if( !in_array( $_POST['type'], array( 'all', 'inbox', 'sent', 'archive', 'trash' ) ) ) {
                exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }

            $chains = array();

            $current_user_id = get_current_user_id();

            $search = '';
            if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                $search = " AND ( c.subject LIKE '%{$_POST['search']}%' OR m.content LIKE '%{$_POST['search']}%')";
            }

            $filter = '';
            $already_filtered = array();
            if( isset( $_POST['filters'] ) && !empty( $_POST['filters'] ) ) {

                $_POST['filters'] = json_decode( base64_decode( $_POST['filters'] ) );
                $_POST['filters'] = (array)$_POST['filters'];

                foreach( $_POST['filters'] as $type=>$value ) {

                    if( empty( $value ) ) {
                        continue;
                    }

                    if( 'date' == $type ) {
                        $value = (array)$value;

                        if( $filter != '' ) {
                            $filter .= " AND ";
                        }

                        $value['from'] = mktime( 0, 0, 0, date( "m", $value['from'] ), date( "d", $value['from'] ), date( "y", $value['from'] ) );
                        $value['to'] = mktime( 0, 0, 0, date( "m", $value['to'] ), date( "d", $value['to'] ) + 1, date( "y", $value['to'] ) ) - 1;

                        $filter .= " ( m.date >= {$value['from']} AND m.date <= {$value['to']} )";
                    } elseif( 'member' == $type ) {
                        $already_filtered = $value;
                    }
                }

                if( $filter != '' ) {
                    $filter = " AND (" . $filter . ")";
                }
            }

            //get archived chains
            $client_archive_chains = WPC()->assigns()->get_assign_data_by_assign( 'archive_chain', 'client', $current_user_id );
            //get trashed chains
            $client_trash_chains = WPC()->assigns()->get_assign_data_by_assign( 'trash_chain', 'client', $current_user_id );

            $html = '';
            if( $_POST['by'] == 'member' ) {

                switch( $_POST['type'] ) {
                    case 'all': {
                        $manager_query = '';
                        if( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_all_private_messages' ) ) {
                            $managers_chain_ids = array();

                            $clients_ids = WPC()->members()->get_all_clients_manager();

                            foreach( $clients_ids as $client_id ) {
                                $result = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $client_id );
                                if( !empty( $result ) ) {
                                    $managers_chain_ids = array_merge( $managers_chain_ids, $result );
                                }
                            }

                            $manager_query = " AND c.id IN('" . implode( "','", $managers_chain_ids ) . "')";
                        }

                        $sql = "SELECT *, COUNT( m.id ) AS messages_count, m.id AS message_id, c.id AS c_id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id
                                    $manager_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id";

                        $chains = $wpdb->get_results( $sql, ARRAY_A );

                        break;
                    }
                    case 'inbox': {
                        $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                        $sql = "SELECT *, COUNT( m.id ) AS messages_count, m.id AS message_id, c.id AS c_id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id <> '{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                      c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                      $search
                                      $filter
                                GROUP BY m.chain_id";

                        $chains = $wpdb->get_results( $sql, ARRAY_A );

                        break;
                    }
                    case 'sent': {
                        $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                        $sql = "SELECT *, COUNT( m.id ) AS messages_count, m.id AS message_id, c.id AS c_id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id='{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                      c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                      $search
                                      $filter
                                GROUP BY m.chain_id";

                        $chains = $wpdb->get_results( $sql, ARRAY_A );

                        break;
                    }
                    case 'archive': {
                        if( !empty( $client_archive_chains ) ) {
                            $sql = "SELECT *, COUNT( m.id ) AS messages_count, m.id AS message_id, c.id AS c_id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                    $search
                                    $filter
                                GROUP BY m.chain_id";

                            $chains = $wpdb->get_results( $sql, ARRAY_A );
                        } else {
                            $chains = array();
                        }

                        break;
                    }
                    case 'trash': {
                        if( !empty( $client_trash_chains ) ) {
                            $sql = "SELECT *, COUNT( m.id ) AS messages_count, m.id AS message_id, c.id AS c_id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $search
                                    $filter
                                GROUP BY m.chain_id";

                            $chains = $wpdb->get_results( $sql, ARRAY_A );
                        } else {
                            $chains = array();
                        }

                        break;
                    }
                }

                $all_members = array();
                foreach( $chains as $chain ) {
                    $chain_clients = WPC()->assigns()->get_assign_data_by_object( 'chain', $chain['c_id'], 'client' );
                    if( empty( $chain_clients ) ) {
                        $chain_clients = array();
                    }
                    $all_members = array_merge( $all_members, $chain_clients );
                }

                $all_members = array_unique( $all_members );
                $all_members = array_diff( $all_members, array( $current_user_id ) );
                $all_members = array_diff( $all_members, $already_filtered );

                $users = array();

                $excluded_clients  = WPC()->members()->get_excluded_clients();
                $excluded_clients = array_merge( $excluded_clients, array( $current_user_id ) );

                $args = array(
                    'role'      => 'wpc_client',
                    'include'   => $all_members,
                    'exclude'   => $excluded_clients,
                    'orderby'   => 'user_login',
                    'order'     => 'ASC'
                );

                $users['wpc_client'] = get_users( $args );

                $args = array(
                    'role'      => 'wpc_client_staff',
                    'include'   => $all_members,
                    'exclude'   => get_current_user_id(),
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                );
                $users['wpc_client_staff'] = get_users( $args );


                $args = array(
                    'role'      => 'wpc_manager',
                    'include'   => $all_members,
                    'exclude'   => get_current_user_id(),
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                );
                $users['wpc_managers'] = get_users( $args );

                if( WPC()->flags['easy_mode'] && ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
                    unset( $users['wpc_client_staff'] );
                    unset( $users['wpc_managers'] );
                }

                $args = array(
                    'role'      => 'wpc_admin',
                    'include'   => $all_members,
                    'exclude'   => get_current_user_id(),
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                );
                $wpc_admins = get_users( $args );
                if( WPC()->flags['easy_mode'] && ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
                    $wpc_admins = array();
                }

                $args = array(
                    'role'      => 'administrator',
                    'include'   => $all_members,
                    'exclude'   => get_current_user_id(),
                    'orderby'   => 'user_login',
                    'order'     => 'ASC',
                );
                $administrators = get_users( $args );

                $users['admins'] = array_merge( $administrators, $wpc_admins );

                $html .= '<label>' . __( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) . ':<br /><select class="wpc_msg_filter_members" placeholder="' . __( 'Select Member', WPC_CLIENT_TEXT_DOMAIN ) . '">';
                $html .= '<option value="" data-hidden="1"></option>';

                $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

                foreach( $users as $role=>$members ) {
                    switch( $role ) {
                        case 'admins':
                            $label = __( 'Admins', WPC_CLIENT_TEXT_DOMAIN );
                            $color = '#b63ad0';
                            break;
                        case 'wpc_managers':
                            $label = WPC()->custom_titles['manager']['p'];
                            $color = '#dc832d';
                            break;
                        case 'wpc_client_staff':
                            $label = WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['staff']['p'];
                            $color = '#2da3dc';
                            break;
                        case 'wpc_client':
                            $label = WPC()->custom_titles['client']['p'];
                            $color = '#0073aa';
                            break;
                    }

                    if( count( $members ) > 0 ) {
                        $html .= '<optgroup label="' . $label . '" data-color="' . $color . '">';

                        foreach ($members as $member) {
                            $html .= '<option value="' . $member->ID . '">' . ( !empty( $member->$display_name )? $member->$display_name : $member->user_login ) . '</option>';
                        }

                        $html .= '</optgroup>';
                    }
                }
                $html .= '</select></label>';

                $mindate = '';
                $maxdate = '';
            } elseif( $_POST['by'] == 'date' ) {

                switch( $_POST['type'] ) {
                    case 'all': {
                        $manager_query = '';
                        if( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_all_private_messages' ) ) {
                            $managers_chain_ids = array();

                            $clients_ids = WPC()->members()->get_all_clients_manager();

                            foreach( $clients_ids as $client_id ) {
                                $result = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $client_id );
                                if( !empty( $result ) ) {
                                    $managers_chain_ids = array_merge( $managers_chain_ids, $result );
                                }
                            }

                            $manager_query = " AND c.id IN('" . implode( "','", $managers_chain_ids ) . "')";
                        }

                        $sql = "SELECT m.date AS chain_last_modify
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id
                                    $manager_query
                                    $search
                                GROUP BY m.chain_id";

                        $chains = $wpdb->get_col( $sql );

                        break;
                    }
                    case 'inbox': {
                        $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                        $sql = "SELECT m.date AS chain_last_modify
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id <> '{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                      c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                      $search
                                GROUP BY m.chain_id";

                        $chains = $wpdb->get_col( $sql );

                        break;
                    }
                    case 'sent': {
                        $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                        $sql = "SELECT m.date AS chain_last_modify
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id='{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                      c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                      c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                      $search
                                GROUP BY m.chain_id";

                        $chains = $wpdb->get_col( $sql );

                        break;
                    }
                    case 'archive': {
                        if( !empty( $client_archive_chains ) ) {
                            $sql = "SELECT m.date AS chain_last_modify
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                    $search
                                GROUP BY m.chain_id";

                            $chains = $wpdb->get_col( $sql );
                        } else {
                            $chains = array();
                        }

                        break;
                    }
                    case 'trash': {
                        if( !empty( $client_trash_chains ) ) {
                            $sql = "SELECT m.date AS chain_last_modify
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $search
                                GROUP BY m.chain_id";

                            $chains = $wpdb->get_col( $sql );
                        } else {
                            $chains = array();
                        }

                        break;
                    }
                }

                $html .= '<label>' . __( 'From', WPC_CLIENT_TEXT_DOMAIN ) . ':<br />
                        <input type="text" name="fake_from_date" class="from_date_field custom_datepicker_field" value="" style="width:100%;" />
                        <input type="hidden" name="from_date" value="' . min($chains) . '" />
                    </label><br />
                    <label>' . __( 'To', WPC_CLIENT_TEXT_DOMAIN ) . ':<br />
                        <input type="text" name="fake_to_date" class="to_date_field custom_datepicker_field" value="" style="width:100%;" />
                        <input type="hidden" name="to_date" value="' . max($chains) . '" />
                    </label>';

                $mindate = min($chains);
                $maxdate = max($chains);
            }

            exit( json_encode( array(
                'status'        => true,
                'filter_html'   => $html,
                'mindate'       => $mindate,
                'maxdate'       => $maxdate
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_get_filter_data() {
        if( isset( $_POST['filter_by'] ) && !empty( $_POST['filter_by'] ) ) {
            switch( $_POST['filter_by'] ) {
                case 'member':
                    $user = get_user_by( 'id', $_POST['member_id'] );

                    if( empty( $user ) ) {
                        exit( json_encode( array(
                            'status' => false,
                            'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN )
                        ) ) );
                    }

                    $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                    $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

                    exit( json_encode( array(
                        'status'    => true,
                        'title'     => __( 'Member', WPC_CLIENT_TEXT_DOMAIN ),
                        'name'      => !empty( $user->$display_name ) ? $user->$display_name : $user->user_login
                    ) ) );

                    break;
                case 'date':

                    exit( json_encode( array(
                        'status'    => true,
                        'title'     => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                        'to'        => WPC()->date_format( $_POST['to'], 'date' ),
                        'from'      => WPC()->date_format( $_POST['from'], 'date' ),
                    ) ) );

                    break;
            }
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_chain_mark_read() {
        global $wpdb;

        if ( isset( $_POST['chain_ids'] ) && !empty( $_POST['chain_ids'] ) ) {

            $_POST['chain_ids'] = json_decode( base64_decode( $_POST['chain_ids'] ) );

            if ( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $messages = $wpdb->get_col(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_messages
                    WHERE chain_id IN( '" . implode( "','", $_POST['chain_ids'] ) . "' )"
            );

            if ( isset( $messages ) && !empty( $messages ) ) {
                foreach( $messages as $message_id ) {
                    WPC()->assigns()->delete_object_by_assign( 'new_message', $message_id, 'client', $user_id );
                }
            }

            exit( json_encode( array(
                'status'    => true,
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_chain_to_trash() {
        global $wpdb;

        if( isset( $_POST['chain_ids'] ) && !empty( $_POST['chain_ids'] ) ) {

            $_POST['chain_ids'] = json_decode( base64_decode( $_POST['chain_ids'] ) );

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $chains = $wpdb->get_col(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id IN( '" . implode( "','", $_POST['chain_ids'] ) . "' )"
            );

            $user_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $user_id );

            if( isset( $chains ) && !empty( $chains ) ) {

                $chains = array_intersect( $chains, $user_chains );

                if( !empty( $chains ) ) {
                    foreach ($chains as $chain_id) {
                        WPC()->assigns()->delete_object_by_assign('archive_chain', $chain_id, 'client', $user_id);
                    }

                    $already_trashed = WPC()->assigns()->get_assign_data_by_assign('trash_chain', 'client', $user_id);
                    $chains = array_merge($chains, $already_trashed);
                    WPC()->assigns()->set_reverse_assigned_data('trash_chain', $chains, 'client', $user_id);
                }
            }

            exit( json_encode( array(
                'status'    => true,
                'count'     => count( $chains )
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_chain_to_archive() {
        global $wpdb;

        if( isset( $_POST['chain_ids'] ) && !empty( $_POST['chain_ids'] ) ) {

            $_POST['chain_ids'] = json_decode( base64_decode( $_POST['chain_ids'] ) );

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $chains = $wpdb->get_col(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id IN( '" . implode( "','", $_POST['chain_ids'] ) . "' )"
            );

            $user_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $user_id );

            if( isset( $chains ) && !empty( $chains ) ) {
                $chains = array_intersect( $chains, $user_chains );

                if( !empty( $chains ) ) {
                    foreach ($chains as $chain_id) {
                        WPC()->assigns()->delete_object_by_assign( 'trash_chain', $chain_id, 'client', $user_id );
                    }

                    $already_archived = WPC()->assigns()->get_assign_data_by_assign( 'archive_chain', 'client', $user_id );
                    $chains = array_merge( $chains, $already_archived );
                    WPC()->assigns()->set_reverse_assigned_data( 'archive_chain', $chains, 'client', $user_id );
                }
            }

            exit( json_encode( array(
                'status'    => true,
                'count'     => count( $chains )
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_leave_chain() {
        global $wpdb;

        if( isset( $_POST['chain_ids'] ) && !empty( $_POST['chain_ids'] ) ) {

            $_POST['chain_ids'] = json_decode( base64_decode( $_POST['chain_ids'] ) );

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $chains = $wpdb->get_results(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id IN( '" . implode( "','", $_POST['chain_ids'] ) . "' )",
                ARRAY_A );

            if( isset( $chains ) && !empty( $chains ) ) {
                foreach( $chains as $chain ) {
                    $chain_id = $chain['id'];

                    WPC()->assigns()->delete_object_by_assign( 'chain', $chain_id, 'client', $user_id );
                    WPC()->assigns()->delete_object_by_assign( 'trash_chain', $chain_id, 'client', $user_id );

                    $client_ids = WPC()->assigns()->get_assign_data_by_object( 'chain', $chain['id'], 'client' );

                    if( empty( $client_ids ) ) {
                        $wpdb->delete(
                            "{$wpdb->prefix}wpc_client_chains",
                            array( 'id' => $chain_id )
                        );

                        WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id );
                        WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id );
                        WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id );

                        $message_ids = $wpdb->get_col(
                            "SELECT id FROM {$wpdb->prefix}wpc_client_messages
                                WHERE chain_id={$chain_id}"
                        );

                        if( isset( $message_ids ) && !empty( $message_ids ) ) {
                            foreach( $message_ids as $message_id ) {
                                WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                            }
                        }

                        $wpdb->delete(
                            "{$wpdb->prefix}wpc_client_messages",
                            array( 'chain_id' => $chain_id )
                        );
                    }
                }
            }

            exit( json_encode( array(
                'status'    => true,
                'message' => __( 'Message Deleted', WPC_CLIENT_TEXT_DOMAIN )
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_chain_delete_permanently() {
        global $wpdb;

        if( !( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
            $user_id = $_POST['client_id'];
        } else {
            $user_id = get_current_user_id();
        }

        if( isset( $_POST['chain_ids'] ) && !empty( $_POST['chain_ids'] ) ) {

            $_POST['chain_ids'] = json_decode( base64_decode( $_POST['chain_ids'] ) );

            $chains = $wpdb->get_col(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id IN( '" . implode( "','", $_POST['chain_ids'] ) . "' )"
            );

            if( isset( $chains ) && !empty( $chains ) ) {
                foreach( $chains as $chain_id ) {
                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_chains",
                        array( 'id' => $chain_id )
                    );

                    WPC()->assigns()->delete_all_object_assigns( 'chain', $chain_id  );
                    WPC()->assigns()->delete_all_object_assigns( 'trash_chain', $chain_id  );
                    WPC()->assigns()->delete_all_object_assigns( 'archive_chain', $chain_id  );

                    $message_ids = $wpdb->get_col(
                        "SELECT id FROM {$wpdb->prefix}wpc_client_messages
                            WHERE chain_id={$chain_id}"
                    );

                    if( isset( $message_ids ) && !empty( $message_ids ) ) {
                        foreach( $message_ids as $message_id ) {
                            WPC()->assigns()->delete_all_object_assigns( 'new_message', $message_id );
                        }
                    }

                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_messages",
                        array( 'chain_id' => $chain_id )
                    );
                }
            }

            exit( json_encode( array(
                'status'    => true,
                'message' => __( 'Message Deleted', WPC_CLIENT_TEXT_DOMAIN )
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_chain_restore() {
        global $wpdb;

        if( isset( $_POST['chain_ids'] ) && !empty( $_POST['chain_ids'] ) && isset( $_POST['from'] ) && !empty( $_POST['from'] ) ) {

            if( !in_array( $_POST['from'], array( 'archive', 'trash' ) ) ) {
                exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $_POST['chain_ids'] = json_decode( base64_decode( $_POST['chain_ids'] ) );

            $chains = $wpdb->get_col(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id IN( '" . implode( "','", $_POST['chain_ids'] ) . "' )"
            );

            if( isset( $chains ) && !empty( $chains ) ) {
                if( 'trash' == $_POST['from'] ) {
                    $object = 'trash_chain';
                } elseif( 'archive' == $_POST['from'] ) {
                    $object = 'archive_chain';
                }

                foreach( $chains as $chain_id ) {
                    WPC()->assigns()->delete_object_by_assign( $object, $chain_id, 'client', $user_id);
                }
            }

            exit( json_encode( array(
                'status'    => true,
                'message' => __( 'Message Restored', WPC_CLIENT_TEXT_DOMAIN )
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_get_chain() {
        global $wpdb;

        if( isset( $_POST['chain_id'] ) && !empty( $_POST['chain_id'] ) ) {

            $chain = $wpdb->get_row( $wpdb->prepare(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id=%d",
                $_POST['chain_id']
            ), ARRAY_A );

            $chain_clients = WPC()->assigns()->get_assign_data_by_object( 'chain', $_POST['chain_id'], 'client' );

            $messages = $wpdb->get_results( $wpdb->prepare(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_messages
                    WHERE chain_id=%d",
                $_POST['chain_id']
            ), ARRAY_A );

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $client_new_messages = WPC()->assigns()->get_assign_data_by_assign( 'new_message', 'client', $user_id );

            $html = '<div class="wpc_msg_chain_subject">' .
                $chain['subject'] .
                '<div class="wpc_msg_refresh_button" data-object="chain" data-chain_id="' . $_POST['chain_id'] . '" title="' . __( 'Refresh', WPC_CLIENT_TEXT_DOMAIN ) . '">' .
                '<div class="wpc_msg_refresh_image"></div>' .
                '</div>' .
                '<div class="wpc_msg_collapse_button" title="' . __( 'Expand All', WPC_CLIENT_TEXT_DOMAIN ) . '" data-alt_title="' . __( 'Collapse All', WPC_CLIENT_TEXT_DOMAIN ) . '">' .
                '<div class="wpc_msg_expand_image"></div>' .
                '</div>' .
                '</div>';

            if( isset( $messages ) && !empty( $messages ) ) {
                $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

                foreach( $messages as $key=>$message ) {
                    //if there are new messages
                    if( is_array( $client_new_messages ) && in_array( $message['id'], $client_new_messages ) ) {
                        WPC()->assigns()->delete_object_by_assign( 'new_message', $message['id'], 'client', $user_id );
                    }

                    $author = get_user_by( 'id', $message['author_id'] );

                    $author_login = __( '(deleted user)', WPC_CLIENT_TEXT_DOMAIN );
                    if( !empty( $author ) ) {
                        $author_login = !empty( $author->$display_name ) ? $author->$display_name : $author->user_login;
                    }

                    $show_class = false;
                    if( count( $messages ) <= 4 || ( count( $messages ) > 4 && ( $key == 0 || $key == count( $messages ) - 1 ) ) ) {
                        $show_class = true;
                    }

                    $html .= '<div class="wpc_msg_message_line ' . ( ( $show_class ) ? '' : 'wpc_msg_for_hidden' ) . '" data-message_id="' . $message['id'] . '">' .
                        '<div class="wpc_msg_avatar">' .
                        WPC()->members()->user_avatar( $message['author_id'] ) .
                        '</div>' .
                        '<div class="wpc_msg_line_content">' .
                        '<div class="wpc_msg_author_date">' .
                        '<div class="wpc_msg_message_author">' . ( ( $message['author_id'] != $user_id ) ? $author_login : __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) ) . '</div>' .
                        '<div class="wpc_msg_message_date">' . WPC()->date_format( $message['date'] ) . '</div>' .
                        '</div>' .
                        '<div class="wpc_msg_message_content">' . make_clickable( nl2br( stripslashes( $message['content'] ) ) ) . '</div>' .
                        '</div>' .
                        '</div>';

                    if( count( $messages ) > 4 && $key == 0 ) {
                        $html .= '<div class="expand_older_messages">' . sprintf( __( 'Show <span class="expand_count">%s</span> Older Messages', WPC_CLIENT_TEXT_DOMAIN ), ( count( $messages ) - 2 ) ) . '</div>';
                    }

                }
            }

            if( count( $chain_clients ) > 1 || ( count( $chain_clients ) == '1' && !in_array( $user_id, $chain_clients ) ) ) {
                $html .= '<div class="wpc_msg_chain_answer">' .
                    '<div class="wpc_msg_avatar">' .
                    WPC()->members()->user_avatar( get_current_user_id() ) .
                    '</div>' .
                    '<div class="wpc_msg_answer_field">';
                $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                if ( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) {
                    $html .= '<div class="wpc_answer_line" style="width:100%;margin: 0 0 10px 0;">
                            <input type="text" id="answer_cc_email" style="width:100%;" name="answer[cc_email]" value="" placeholder="' . __('CC Email', WPC_CLIENT_TEXT_DOMAIN) . '" />
                            <span class="description">' . __( 'Add an email address here to copy them once on the initial message', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                        </div>';
                }

                $html .= '<textarea id="answer_content" name="answer[content]" style="width:100%; height:100px;resize: vertical;" placeholder="' . __('Type your private message here', WPC_CLIENT_TEXT_DOMAIN) . '"></textarea>' .
                    '</div>' .
                    '<div class="wpc_msg_answer_actions">' .
                    '<input type="button" id="send_answer" data-chain_id="' . $_POST['chain_id'] . '" class="button-primary" value="' . __('Send', WPC_CLIENT_TEXT_DOMAIN) . '" />' .
                    '<input type="button" id="back_answer" class="button" value="' . __( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) . '" />' .
                    '<span class="wpc_ajax_loading" style="display: none;float:left;margin: 6px 0 0 10px;"></span>' .
                    '</div>' .
                    '</div>';
            } else {
                $html .= '<div class="wpc_msg_answer_actions">' .
                    '<input type="button" id="back_answer" class="button" value="' . __( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) . '" />' .
                    '</div>';
            }
            exit( json_encode( array(
                'status'    => true,
                'html'      => $html
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_reply() {
        if ( isset( $_POST['chain_id'] ) && !empty( $_POST['chain_id'] ) && isset( $_POST['content'] ) && !empty( $_POST['content'] ) ) {

            $chain_clients = WPC()->assigns()->get_assign_data_by_object( 'chain', $_POST['chain_id'], 'client' );

            if ( isset( $chain_clients ) && !empty( $chain_clients ) ) {
                if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                    $user_id = $_POST['client_id'];
                } else {
                    $user_id = get_current_user_id();
                }

                if ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_show_all_private_messages' ) ) {
                    if ( !in_array( $user_id, $chain_clients ) ) {
                        $chain_clients = array_merge( $chain_clients, array( $user_id ) );

                        WPC()->assigns()->set_assigned_data( 'chain', $_POST['chain_id'], 'client', $chain_clients );
                    }
                }

                //check if users exists
                $temp_users = array();
                foreach( $chain_clients as $member_id ) {
                    $userdata = get_userdata( $member_id );
                    if( $userdata !== false ) {
                        $temp_users[] = $member_id;
                    }
                }
                $chain_clients = $temp_users;

                foreach( $chain_clients as $member_id ) {
                    //remove from archive
                    WPC()->assigns()->delete_object_by_assign( 'archive_chain', $_POST['chain_id'], 'client', $member_id );
                    if( $member_id == $user_id ) {
                        //self reply remove from trash
                        WPC()->assigns()->delete_object_by_assign( 'trash_chain', $_POST['chain_id'], 'client', $member_id );
                    }
                }

                $assign_new = array_diff( $chain_clients, array( $user_id ) );

                $time = time();
                $_POST['content'] = stripslashes( WPC()->decode_ajax_data( $_POST['content'] ) );

                $data= array(
                    'chain_id'  => $_POST['chain_id'],
                    'content'   => addslashes( htmlspecialchars( $_POST['content'] ) ),
                    'author_id' => $user_id,
                    'date'      => $time,
                    'assign_new'=> $assign_new
                );
                $message_id = WPC()->private_messages()->private_messages_create_message( $data );

                $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                if( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) {
                    if( isset( $_POST['cc_email'] ) && is_email( $_POST['cc_email'] ) ) {
                        $author = get_userdata( get_current_user_id() );

                        global $wpdb;
                        $subject = $wpdb->get_var( $wpdb->prepare(
                            "SELECT subject
                                FROM {$wpdb->prefix}wpc_client_chains
                                WHERE id = %d",
                            $data['chain_id']
                        ) );

                        $args = array(
                            'user_name' => $author->get( 'user_login' ),
                            'message'   => make_clickable( nl2br( htmlspecialchars( stripslashes( $_POST['content'] ) ) ) ),
                            'subject'   => nl2br( htmlspecialchars( stripslashes( $subject ) ) )
                        );
                        WPC()->mail( 'notify_cc_about_message', $_POST['cc_email'], $args, 'notify_cc_about_message' );
                    }
                }

                $html = '<div class="wpc_msg_message_line" data-message_id="' . $message_id . '">' .
                    '<div class="wpc_msg_avatar">' .
                    '<div class="wpc_avatar">' .
                    WPC()->members()->user_avatar( $user_id ) .
                    '</div>' .
                    '</div>' .
                    '<div class="wpc_msg_line_content">' .
                    '<div class="wpc_msg_author_date">' .
                    '<div class="wpc_msg_message_author">' . __( 'Me', WPC_CLIENT_TEXT_DOMAIN ) . '</div>' .
                    '<div class="wpc_msg_message_date">' . WPC()->date_format( $time ) . '</div>' .
                    '</div>' .
                    '<div class="wpc_msg_message_content">' . make_clickable( nl2br( htmlspecialchars( $_POST['content'] ) ) ) . '</div>' .
                    '</div>' .
                    '</div>';

                exit( json_encode( array(
                    'status'    => true,
                    'message' => __( 'Message Sent', WPC_CLIENT_TEXT_DOMAIN ),
                    'html'      => $html
                ) ) );
            }
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_front_end_get_list() {
        global $wpdb;

        if( isset( $_POST['type'] ) && !empty( $_POST['type'] ) ) {

            if( !in_array( $_POST['type'], array( 'trash', 'inbox', 'sent', 'archive' ) ) ) {
                exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }

            $items_count = 0;
            $chains = array();

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $current_user_id = $_POST['client_id'];
            } else {
                $current_user_id = get_current_user_id();
            }

            $per_page   = ( isset( $_POST['per_page'] ) && is_numeric( $_POST['per_page'] ) ) ? $_POST['per_page'] : 5;
            $paged      = ( isset( $_POST['page'] ) && !empty( $_POST['page'] ) ) ? $_POST['page'] : 1;

            $search = '';
            if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                $search = " AND ( c.subject LIKE '%{$_POST['search']}%' OR m.content LIKE '%{$_POST['search']}%')";
            }

            $filter = '';
            if( isset( $_POST['filters'] ) && !empty( $_POST['filters'] ) ) {

                $_POST['filters'] = json_decode( base64_decode( $_POST['filters'] ) );
                $_POST['filters'] = (array)$_POST['filters'];

                foreach( $_POST['filters'] as $type=>$value ) {

                    if( empty( $value ) ) {
                        continue;
                    }

                    if( 'date' == $type ) {
                        $value = (array)$value;

                        if( $filter != '' ) {
                            $filter .= " AND ";
                        }

                        $value['from'] = mktime( 0, 0, 0, date( "m", $value['from'] ), date( "d", $value['from'] ), date( "y", $value['from'] ) );
                        $value['to'] = mktime( 0, 0, 0, date( "m", $value['to'] ), date( "d", $value['to'] ) + 1, date( "y", $value['to'] ) ) - 1;

                        $filter .= " ( m.date >= {$value['from']} AND m.date <= {$value['to']} )";
                    } elseif( 'member' == $type ) {
                        if( $filter != '' ) {
                            $filter .= " AND ";
                        }

                        $chain_ids = array();
                        foreach( $value as $client_id ) {
                            $result = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $client_id );
                            if( !empty( $result ) ) {
                                $chain_ids = array_merge( $chain_ids, $result );
                            }
                        }

                        $chain_ids = array_unique( $chain_ids );
                        $chain_ids = implode( "','", $chain_ids );

                        $filter .= " c.id IN( '{$chain_ids}' )";
                    }
                }

                if( $filter != '' ) {
                    $filter = " AND (" . $filter . ")";
                }
            }

            //get archived chains
            $client_archive_chains = WPC()->assigns()->get_assign_data_by_assign( 'archive_chain', 'client', $current_user_id );
            //get trashed chains
            $client_trash_chains = WPC()->assigns()->get_assign_data_by_assign( 'trash_chain', 'client', $current_user_id );
            //get markers for chains with new messages
            $client_new_messages = WPC()->assigns()->get_assign_data_by_assign( 'new_message', 'client', $current_user_id );


            $new_query = '';
            $not_new_query = '';
            $new_marker_chains = 0;
            $wpc_private_messages = WPC()->get_settings( 'private_messages' );
            if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] ) {
                $new_marker_chains = $wpdb->get_col(
                    "SELECT DISTINCT c.id
                        FROM {$wpdb->prefix}wpc_client_chains c,
                             {$wpdb->prefix}wpc_client_messages m
                        WHERE c.id = m.chain_id AND
                              m.id IN('" . implode( "','", $client_new_messages ) . "')"
                );

                if( !empty( $new_marker_chains ) ) {
                    $new_query = " AND c.id IN('" . implode( "','", $new_marker_chains ) . "')";
                    $not_new_query = " AND c.id NOT IN('" . implode( "','", $new_marker_chains ) . "')";
                } else {
                    $new_marker_chains = 0;
                }
            }

            $ids = array();
            switch( $_POST['type'] ) {
                case 'inbox': {
                    $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                    $ids = $wpdb->get_col(
                        "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                WHERE m.author_id <> '{$current_user_id}'
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id AND
                                c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                $search
                                $filter
                            GROUP BY m.chain_id"
                    );
                    $items_count = count( $ids );

                    $new_chains = array();
                    if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                        $new_marker_chains = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                      FROM {$wpdb->prefix}wpc_client_messages m
                                      WHERE m.author_id <> '{$current_user_id}'
                                      ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $new_marker_chains = count( $new_marker_chains );

                        $pages_with_new = ceil( $new_marker_chains / $per_page );
                        if( $pages_with_new == 0 )
                            $pages_with_new = 1;

                        if( $pages_with_new >= $paged ) {
                            $new_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '1' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                          FROM {$wpdb->prefix}wpc_client_messages m
                                          WHERE m.author_id <> '{$current_user_id}'
                                          ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                ARRAY_A );
                        }
                    }

                    $old_chains = array();
                    if( count( $new_chains ) < $per_page ) {
                        $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                        $old_chains = $wpdb->get_results(
                            "SELECT *,
                                      COUNT( m.id ) AS messages_count,
                                      m.id AS message_id,
                                      c.id AS c_id,
                                      '0' AS is_new
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                      FROM {$wpdb->prefix}wpc_client_messages m
                                      WHERE m.author_id <> '{$current_user_id}'
                                      ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $not_new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id
                                ORDER BY m.date DESC
                                LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                            ARRAY_A );
                    }
                    $chains = array_merge( $new_chains, $old_chains );

                    foreach( $chains as $key=>$chain ) {
                        $all_chain_messages = $wpdb->get_col(
                            "SELECT id
                                FROM {$wpdb->prefix}wpc_client_messages
                                WHERE chain_id='{$chain['c_id']}'"
                        );

                        $chains[$key]['messages_count'] = count($all_chain_messages);
                    }
                    break;
                }
                case 'sent': {
                    $client_chains = WPC()->assigns()->get_assign_data_by_assign( 'chain', 'client', $current_user_id );

                    $ids = $wpdb->get_col(
                        "SELECT c.id
                            FROM {$wpdb->prefix}wpc_client_chains c,
                                ( SELECT *
                                FROM {$wpdb->prefix}wpc_client_messages m
                                WHERE m.author_id='{$current_user_id}'
                                ORDER BY m.date DESC ) AS m
                            WHERE m.chain_id = c.id AND
                                c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                $search
                                $filter
                            GROUP BY m.chain_id"
                    );
                    $items_count = count( $ids );

                    $new_chains = array();
                    if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                        $new_marker_chains = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id='{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $new_marker_chains = count( $new_marker_chains );

                        $pages_with_new = ceil( $new_marker_chains / $per_page );
                        if( $pages_with_new == 0 )
                            $pages_with_new = 1;

                        if( $pages_with_new >= $paged ) {
                            $new_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '1' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        WHERE m.author_id='{$current_user_id}'
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                        c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                ARRAY_A );
                        }
                    }

                    $old_chains = array();
                    if( count( $new_chains ) < $per_page ) {
                        $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                        $old_chains = $wpdb->get_results(
                            "SELECT *,
                                      COUNT( m.id ) AS messages_count,
                                      m.id AS message_id,
                                      c.id AS c_id,
                                      '0' AS is_new
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    WHERE m.author_id='{$current_user_id}'
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_archive_chains ) . "' ) AND
                                    c.id NOT IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $not_new_query
                                    $search
                                    $filter
                                GROUP BY m.chain_id
                                ORDER BY m.date DESC
                                LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                            ARRAY_A );
                    }

                    $chains = array_merge( $new_chains, $old_chains );

                    foreach( $chains as $key=>$chain ) {
                        $all_chain_messages = $wpdb->get_col(
                            "SELECT id
                                FROM {$wpdb->prefix}wpc_client_messages
                                WHERE chain_id='{$chain['c_id']}'"
                        );

                        $chains[$key]['messages_count'] = count($all_chain_messages);
                    }
                    break;
                }
                case 'archive': {
                    if( !empty( $client_archive_chains ) ) {
                        $ids = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $items_count = count( $ids );

                        $new_chains = array();
                        if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                            $new_marker_chains = $wpdb->get_col(
                                "SELECT c.id
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id"
                            );
                            $new_marker_chains = count( $new_marker_chains );

                            $pages_with_new = ceil( $new_marker_chains / $per_page );
                            if( $pages_with_new == 0 )
                                $pages_with_new = 1;

                            if( $pages_with_new >= $paged ) {
                                $new_chains = $wpdb->get_results(
                                    "SELECT *,
                                              COUNT( m.id ) AS messages_count,
                                              m.id AS message_id,
                                              c.id AS c_id,
                                              '1' AS is_new
                                        FROM {$wpdb->prefix}wpc_client_chains c,
                                            ( SELECT *
                                            FROM {$wpdb->prefix}wpc_client_messages m
                                            ORDER BY m.date DESC ) AS m
                                        WHERE m.chain_id = c.id AND
                                            c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                            $new_query
                                            $search
                                            $filter
                                        GROUP BY m.chain_id
                                        ORDER BY m.date DESC
                                        LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                    ARRAY_A );
                            }
                        }

                        $old_chains = array();
                        if( count( $new_chains ) < $per_page ) {
                            $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                            $old_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '0' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_archive_chains ) . "' )
                                        $not_new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                                ARRAY_A );
                        }

                        $chains = array_merge( $new_chains, $old_chains );
                    }
                    break;
                }
                case 'trash': {
                    if( !empty( $client_trash_chains ) ) {
                        $ids = $wpdb->get_col(
                            "SELECT c.id
                                FROM {$wpdb->prefix}wpc_client_chains c,
                                    ( SELECT *
                                    FROM {$wpdb->prefix}wpc_client_messages m
                                    ORDER BY m.date DESC ) AS m
                                WHERE m.chain_id = c.id AND
                                    c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                    $search
                                    $filter
                                GROUP BY m.chain_id"
                        );
                        $items_count = count( $ids );

                        $new_chains = array();
                        if ( isset( $wpc_private_messages['first_new_chains'] ) && 'yes' == $wpc_private_messages['first_new_chains'] && !empty( $new_marker_chains ) ) {
                            $new_marker_chains = $wpdb->get_col(
                                "SELECT c.id
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id"
                            );
                            $new_marker_chains = count( $new_marker_chains );

                            $pages_with_new = ceil( $new_marker_chains / $per_page );
                            if( $pages_with_new == 0 )
                                $pages_with_new = 1;

                            if( $pages_with_new >= $paged ) {
                                $new_chains = $wpdb->get_results(
                                    "SELECT *,
                                              COUNT( m.id ) AS messages_count,
                                              m.id AS message_id,
                                              c.id AS c_id,
                                              '1' AS is_new
                                        FROM {$wpdb->prefix}wpc_client_chains c,
                                            ( SELECT *
                                            FROM {$wpdb->prefix}wpc_client_messages m
                                            ORDER BY m.date DESC ) AS m
                                        WHERE m.chain_id = c.id AND
                                            c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                            $new_query
                                            $search
                                            $filter
                                        GROUP BY m.chain_id
                                        ORDER BY m.date DESC
                                        LIMIT " . $per_page * ( $paged - 1 ) . ", $per_page",
                                    ARRAY_A );
                            }
                        }

                        $old_chains = array();
                        if( count( $new_chains ) < $per_page ) {
                            $start_count = $per_page * ( $paged - 1 ) - ( $new_marker_chains - ( $per_page*floor( $new_marker_chains/$per_page ) ) ) + count( $new_chains );

                            $old_chains = $wpdb->get_results(
                                "SELECT *,
                                          COUNT( m.id ) AS messages_count,
                                          m.id AS message_id,
                                          c.id AS c_id,
                                          '0' AS is_new
                                    FROM {$wpdb->prefix}wpc_client_chains c,
                                        ( SELECT *
                                        FROM {$wpdb->prefix}wpc_client_messages m
                                        ORDER BY m.date DESC ) AS m
                                    WHERE m.chain_id = c.id AND
                                        c.id IN( '" . implode( "','", $client_trash_chains ) . "' )
                                        $not_new_query
                                        $search
                                        $filter
                                    GROUP BY m.chain_id
                                    ORDER BY m.date DESC
                                    LIMIT " . $start_count . "," . ( $per_page - count( $new_chains ) ),
                                ARRAY_A );
                        }

                        $chains = array_merge( $new_chains, $old_chains );
                    }

                    break;
                }
            }

            $wpc_private_messages = WPC()->get_settings( 'private_messages' );
            $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

            foreach( $chains as $key=>$chain ) {
                $client_ids = WPC()->assigns()->get_assign_data_by_object( 'chain', $chain['c_id'], 'client' );

                $title = '';
                $members_html = '';
                if( !empty( $client_ids ) ) {
                    $members = get_users(array(
                        'include' => $client_ids,
                        'order_by' => $display_name,
                        'order' => 'ASC'
                    ));

                    foreach ($members as $k => $member) {
                        if ($member->ID == get_current_user_id()) {
                            $me_key = $k;
                        }
                    }

                    if (isset($me_key)) {
                        $buf = $members[0];
                        $members[0] = $members[$me_key];
                        $members[$me_key] = $buf;
                    }


                    foreach ($members as $member) {
                        if ($member->ID == get_current_user_id()) {
                            $members_html .= __('Me', WPC_CLIENT_TEXT_DOMAIN) . ', ';
                            $title .= __('Me', WPC_CLIENT_TEXT_DOMAIN) . ', ';
                        } else {
                            $members_html .= ( !empty( $member->$display_name ) ? $member->$display_name : $member->user_login ) . ', ';
                            $title .= ( !empty( $member->$display_name ) ? $member->$display_name : $member->user_login ) . ', ';
                        }
                    }
                }
                $chains[$key]['members'] = substr( $members_html, 0, -2 );
                $chains[$key]['members_title'] = substr( $title, 0, -2 );
                $chains[$key]['date'] = WPC()->date_format( $chain['date'] );
                $chains[$key]['is_new'] = $chain['is_new'] ? 'true' : 'false';
                $chains[$key]['content'] = make_clickable( nl2br( stripslashes( $chain['content'] ) ) );
                $strip_content = explode( '<br />', $chains[$key]['content'] );
                $chains[$key]['content'] = $strip_content[0];
            }

            //set empty marker if 0 chains
            $is_empty = false;
            $pagination = array(
                'current_page'  => $paged,
                'start'         => $per_page * ( $paged - 1 ) + 1,
                'end'           => ( $per_page * ( $paged - 1 ) + $per_page < $items_count ) ? $per_page * ( $paged - 1 ) + $per_page : $items_count,
                'count'         => $items_count,
                'pages_count'   => ceil( $items_count/$per_page )
            );
            if( empty( $chains ) ) {
                $is_empty = true;
                $pagination = false;
            }


            $data['chains'] = $chains;
            //build HTML for table
            $html = WPC()->get_template( 'messages/chains.php', '', $data );

            exit( json_encode( array(
                'status'        => true,
                'html'          => $html,
                'is_empty'      => $is_empty,
                'pagination'    => $pagination,
                'ids'           => $ids
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_front_end_get_chain() {
        global $wpdb;

        if( isset( $_POST['chain_id'] ) && !empty( $_POST['chain_id'] ) ) {

            if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $chain = $wpdb->get_row( $wpdb->prepare(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_chains
                    WHERE id=%d",
                $_POST['chain_id']
            ), ARRAY_A );

            $chain_clients = WPC()->assigns()->get_assign_data_by_object( 'chain', $_POST['chain_id'], 'client' );

            $messages = $wpdb->get_results( $wpdb->prepare(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_messages
                    WHERE chain_id=%d",
                $_POST['chain_id']
            ), ARRAY_A );


            $data['chain_id'] = $_POST['chain_id'];

            $data['subject'] = $chain['subject'];
            $data['avatar'] = WPC()->members()->user_avatar( $user_id );
            $data['answer_message_textarea'] = '<textarea class="wpc_msg_answer_content" name="answer[content]" class="wpc_textarea" placeholder="' . __( 'Type your private message here', WPC_CLIENT_TEXT_DOMAIN ) . '"></textarea>';

            if( isset( $messages ) && !empty( $messages ) ) {
                $client_new_messages = WPC()->assigns()->get_assign_data_by_assign( 'new_message', 'client', $user_id );

                $wpc_private_messages = WPC()->get_settings( 'private_messages' );
                $display_name = ( isset( $wpc_private_messages['display_name'] ) && !empty( $wpc_private_messages['display_name'] ) ) ? $wpc_private_messages['display_name'] : 'user_login';

                foreach( $messages as $key=>$message ) {
                    //if there are new messages
                    if( is_array( $client_new_messages ) && in_array( $message['id'], $client_new_messages ) ) {
                        WPC()->assigns()->delete_object_by_assign( 'new_message', $message['id'], 'client', $user_id );
                    }

                    $author = get_user_by( 'id', $message['author_id'] );

                    $author_login = __( '(deleted user)', WPC_CLIENT_TEXT_DOMAIN );
                    if( !empty( $author ) ) {
                        $author_login = !empty( $author->$display_name ) ? $author->$display_name : $author->user_login;
                    }

                    $messages[$key]['avatar'] = WPC()->members()->user_avatar( $message['author_id'] );
                    $messages[$key]['author'] = ( $message['author_id'] != $user_id ) ? $author_login : __( 'Me', WPC_CLIENT_TEXT_DOMAIN );
                    $messages[$key]['date'] = WPC()->date_format( $message['date'] );
                    $messages[$key]['content'] = make_clickable( nl2br( stripslashes( $message['content'] ) ) );
                }
            }

            $data['messages'] = $messages;

            $data['show_cc_email'] = false;
            $wpc_private_messages = WPC()->get_settings( 'private_messages' );
            if( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) {
                $data['show_cc_email'] = true;
            }

            $data['hide_reply'] = true;
            if( count( $chain_clients ) > 1 ) {
                $data['hide_reply'] = false;
            }

            $html = WPC()->get_template( 'messages/chain.php', '', $data );

            exit( json_encode( array(
                'status'    => true,
                'html'      => $html
            ) ) );
        }

        exit( json_encode( array( 'status' => false, 'message' => __( 'Wrong data', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_message_front_end_new_message() {

        $send_to = '';

        //create new message
        if ( !empty( $_POST['to'] ) ) {
            $send_to = json_decode( base64_decode( $_POST['to'] ) );
        }

        if ( empty( $send_to ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Empty To field', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }


        if( !( isset( $_POST['subject'] ) && !empty( $_POST['subject'] ) ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Empty Subject', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if( !( isset( $_POST['content'] ) && !empty( $_POST['content'] ) ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Empty Message', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if( !( isset( $_POST['cc'] ) && !empty( $_POST['cc'] ) ) ) {
            $_POST['cc'] = array();
        } else {
            $_POST['cc'] = json_decode( base64_decode( $_POST['cc'] ) );
        }

        if( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
            $user_id = $_POST['client_id'];
        } else {
            $user_id = get_current_user_id();
        }

        $_POST['content'] = stripslashes( WPC()->decode_ajax_data( $_POST['content'] ) );

        foreach( $send_to as $to ) {
            $args = array(
                'to'        => array( $to ),
                'subject'   => $_POST['subject'],
                'content'   => $_POST['content'],
                'cc'        => $_POST['cc']
            );

            WPC()->private_messages()->private_messages_create_chain( $args );
        }

        $wpc_private_messages = WPC()->get_settings( 'private_messages' );
        if( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) {
            if( isset( $_POST['cc_email'] ) && is_email( $_POST['cc_email'] ) ) {

                $author = get_userdata( $user_id );

                $args = array(
                    'user_name' => $author->get( 'user_login' ),
                    'message'   => make_clickable( nl2br( htmlspecialchars( stripslashes( $_POST['content'] ) ) ) ),
                    'subject'   => nl2br( htmlspecialchars( stripslashes( $_POST['subject'] ) ) )
                );
                WPC()->mail( 'notify_cc_about_message', $_POST['cc_email'], $args, 'notify_cc_about_message' );
            }
        }

        exit( json_encode( array( 'status' => true, 'message' => __( 'Message Sent', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_get_email_profile() {
        $wpc_email = array();

        $type = !empty( $_POST['type'] ) ? $_POST['type'] : '';
        $profile_id = !empty( $_POST['profile_id'] ) ? $_POST['profile_id'] : '';
        $nonce = !empty( $_POST['nonce'] ) ? $_POST['nonce'] : '';

        if ( !$nonce || !wp_verify_nonce( $nonce, get_current_user_id() . 'approve_ajax' ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Nonce is Incorrect', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if ( $profile_id ) {
            $all_profiles = WPC()->get_settings( 'email_sending_profiles' );
            if ( !empty( $all_profiles[ $profile_id ] ) ) {
                $wpc_email = $all_profiles[ $profile_id ];
            }

            $type = !empty( $wpc_email['type'] ) ? $wpc_email['type'] : '';
        }

        ob_start();
        ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label>
                        <?php _e( 'Sending Method', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                    </label>
                </th>
                <td>
                    <span id="email_sending_type"></span>
                    <input type="hidden" name="wpc_email_settings[type]" id="email_sending_type_val" value="<?php echo $type ?>">
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="profile_name"><?php _e( 'Profile Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                </th>
                <td>
                    <input type="text" name="wpc_email_settings[profile_name]" id="profile_name" value="<?php echo isset( $wpc_email['profile_name'] ) ? $wpc_email['profile_name'] : __( 'New Profile', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                    <input type="hidden" name="wpc_email_settings[item_id]" id="email_sending_item_id" value="<?php echo ( $profile_id ) ? $profile_id : uniqid(); ?>">
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="sender_name"><?php _e( 'Sender Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                </th>
                <td>
                    <input type="text" name="wpc_email_settings[sender_name]" id="sender_name" value="<?php echo isset( $wpc_email['sender_name'] ) ? $wpc_email['sender_name'] : ''; ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="sender_email"><?php _e( 'Sender Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                </th>
                <td>
                    <input type="text" name="wpc_email_settings[sender_email]" id="sender_email" value="<?php echo isset( $wpc_email['sender_email'] ) ? $wpc_email['sender_email'] : ''; ?>" />
                    <br>
                        <span class="description">
                            <?php
                            $domain = (!empty( $_SERVER['SERVER_NAME'] )) ? $_SERVER['SERVER_NAME'] : 'your_domain';
                            if ( 0 === strpos($domain, 'www.') ) {
                                $domain = substr($domain, 4);
                            }

                            printf( __( 'Note: If you want to use email address different from %s, please ensure that your mail server is supporting emails with another domain.'
                                , WPC_CLIENT_TEXT_DOMAIN ), '"***@' . $domain . '"' ) ?>
                        </span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="reply_email"><?php _e( 'Reply to Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                </th>
                <td>
                    <input type="text" name="wpc_email_settings[reply_email]" id="reply_email" value="<?php echo isset( $wpc_email['reply_email'] ) ? $wpc_email['reply_email'] : ''; ?>" />
                </td>
            </tr>

            <?php

            if ( 'smtp' === $type ) {
                ?>
                <tr>
                    <td>
                        <label for="email_sending_auth_type">
                            <?php _e( 'Auth Type', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                        </label>
                    </td>
                    <td>
                        <?php
                        $array_auth_types = array( 'PLAIN', 'LOGIN', 'NTLM', 'CRAM-MD5' );
                        ?>
                        <select name="wpc_email_settings[smtp][auth_type]" id="email_sending_auth_type">
                            <?php
                            foreach ( $array_auth_types as $v ) {
                                echo '<option value="' . $v . '" ' . selected( !empty( $wpc_email['smtp']['auth_type'] ) ? $wpc_email['smtp']['auth_type'] : 'LOGIN', $v ) . '>' . $v . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="wpc_email_for_ntlm">
                    <td><label for="wpc_email_auth_realm_ntlm"><?php _e( 'Auth Realm', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td><input type="text" id="wpc_email_auth_realm_ntlm" name="wpc_email_settings[smtp][auth_realm]" value="<?php echo ( !empty( $wpc_email['smtp']['auth_realm'] ) ? $wpc_email['smtp']['auth_realm'] : '' ); ?>"/></td>
                </tr>
                <tr class="wpc_email_for_ntlm">
                    <td><label for="wpc_email_auth_workstation_ntlm"><?php _e( 'Auth Workstation', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td><input type="text" id="wpc_email_auth_workstation_ntlm" name="wpc_email_settings[smtp][auth_workstation]" value="<?php echo ( !empty( $wpc_email['smtp']['auth_workstation'] ) ? $wpc_email['smtp']['auth_workstation'] : '' ); ?>"/></td>
                </tr>
                <tr>
                    <td><label for="wpc_smtp_host"><?php _e( 'Host', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td><input type="text" id="wpc_smtp_host" name="wpc_email_settings[smtp][host]" value="<?php echo ( !empty( $wpc_email['smtp']['host'] ) ? $wpc_email['smtp']['host'] : '' ); ?>"/></td>
                </tr>
                <tr>
                    <td><label for="wpc_smtp_port"><?php _e( 'Port', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td><input type="text" style="width: 40px;" id="wpc_smtp_port" name="wpc_email_settings[smtp][port]" value="<?php echo ( !empty( $wpc_email['smtp']['port'] ) ? $wpc_email['smtp']['port'] : '' ); ?>"/>
                        <label style="margin-left: 25px;" for="wpc_secure_prefix"><?php _e( 'Protocol', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <select id="wpc_secure_prefix" name="wpc_email_settings[smtp][secure]">
                            <option value="" <?php selected( empty( $wpc_email['smtp']['secure'] ) ); ?>>None</option>
                            <option value="ssl" <?php selected( !empty( $wpc_email['smtp']['secure'] ) ? $wpc_email['smtp']['secure'] : '', 'ssl' ); ?>>ssl</option>
                            <option value="tls" <?php selected( !empty( $wpc_email['smtp']['secure'] ) ? $wpc_email['smtp']['secure'] : '', 'tls' ); ?>>tls</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="wpc_smtp_username"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td><input type="text" id="wpc_smtp_username" name="wpc_email_settings[smtp][username]" value="<?php echo ( !empty( $wpc_email['smtp']['username'] ) ? $wpc_email['smtp']['username'] : '' ); ?>"/></td>
                </tr>
                <tr>
                    <td><label for="wpc_smtp_password"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td><input type="password" id="wpc_smtp_password" name="wpc_email_settings[smtp][password]" value="" placeholder="<?php echo ( !empty( $wpc_email['smtp']['password'] ) ? '********' : '' ); ?>" /></td>
                </tr>
                <tr>
                    <td><label for="wpc_smtp_keep_alive"><?php _e( 'Keep Alive Connection', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label></td>
                    <td>
                        <select id="wpc_smtp_keep_alive" name="wpc_email_settings[smtp][keep_alive]">
                            <option value="yes" <?php selected( !empty( $wpc_email['smtp']['keep_alive'] ) ? $wpc_email['smtp']['keep_alive'] : '', 'yes' ); ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php selected( !empty( $wpc_email['smtp']['keep_alive'] ) ? $wpc_email['smtp']['keep_alive'] : '', 'no' ); ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description">
                            <?php _e( 'Note: When this option is disabled, your server might block sending of several emails one by another (example: New Registration or Sending Newsletters).', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" height="10px"></td>
                </tr>
                <?php
            } elseif ( '' !== $type ) {
                WPC()->mailer()->load_email_senders();

                /*our_hook_
                    hook_name: wpc_client_email_settings_for_(mandrill, sendgrid, etc)
                    hook_title: Add html to email settings page
                    hook_description: Hook add html to email settings page.
                    hook_type: action
                    hook_in: wp-client
                    hook_location class.common.php
                    hook_since: 3.9.7
                    */
                do_action( 'wpc_client_email_settings_for_' . $type, $wpc_email );
            }

            ?>
        </table>

        <!--Update button-->
        <?php
        $button_text = $profile_id ? __( 'Update Profile', WPC_CLIENT_TEXT_DOMAIN )
            : __( 'Save Profile', WPC_CLIENT_TEXT_DOMAIN );
        ?>
        <div id="wpc_save_button">
            <input type="button" class="button-primary" id="wpc_save" name="save" value="<?php echo $button_text ?>" />
        </div>

        <?php

        $html = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        exit( json_encode( array( 'status' => true, 'html' => $html ) ) );

    }


    function ajax_save_email_profile() {
        $return = array();
        $settings = !empty( $_POST['wpc_email_settings'] ) ? $_POST['wpc_email_settings'] : '';
        $nonce = !empty( $_POST['nonce'] ) ? $_POST['nonce'] : '';

        if ( !$nonce || !wp_verify_nonce( $nonce, get_current_user_id() . 'approve_ajax' ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Nonce is Incorrect', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if ( empty( $settings['item_id'] ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Empty Profile Id', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }
        $item_id = $settings['item_id'];

        $all_profiles = WPC()->get_settings( 'email_sending_profiles' );

        //Validation of settings
        $result = WPC()->mailer()->prepare_profile_settings( $settings );
        if ( is_string( $result ) ) {
            exit( json_encode( array( 'status' => false, 'message' => $result ) ) );
        }

        if ( isset( $all_profiles[ $item_id ] ) ) {
            //update profile
            $old_data = $all_profiles[ $item_id ];
            $new_data = array_merge( $old_data, $result );
            $all_profiles[ $item_id ] = $new_data;
        } else {
            //create profile
            $new_array[ $item_id ] = $result;
            $all_profiles = array_merge($new_array, $all_profiles);
            $return['text_button'] = __( 'Update Profile', WPC_CLIENT_TEXT_DOMAIN );
        }

        //save all profiles
        WPC()->settings()->update( $all_profiles, 'email_sending_profiles' );

        $return['status'] = true;
        $return['message'] = __( 'Profile Has Been Saved', WPC_CLIENT_TEXT_DOMAIN );
        exit( json_encode( $return ) );

    }


    function ajax_delete_email_profile() {
        $profile_id = !empty( $_POST['profile_id'] ) ? $_POST['profile_id'] : '';
        $nonce = !empty( $_POST['nonce'] ) ? $_POST['nonce'] : '';

        if ( !$nonce || !wp_verify_nonce( $nonce, get_current_user_id() . 'approve_ajax' ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Nonce is Incorrect', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if ( empty( $profile_id ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Empty Profile Id', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        global $wpdb;
        $use = $wpdb->get_var( $wpdb->prepare( "SELECT `option_name` FROM {$wpdb->options} "
            . "WHERE `option_value` = %s "
            . "AND `option_name` LIKE 'wpc_email_sending_profile_for_%%'",
            $profile_id ) );
        if ( $use ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'This Email Profile Already in Use', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        $all_profiles = WPC()->get_settings( 'email_sending_profiles' );

        if ( isset( $all_profiles[ $profile_id ] ) ) {
            //delete profile
            unset( $all_profiles[ $profile_id ] );

            //save other profiles
            WPC()->settings()->update( $all_profiles, 'email_sending_profiles' );

            exit( json_encode( array( 'status' => true, 'message' => __( 'Profile Has Been Deleted', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        } else {
            exit( json_encode( array( 'status' => false, 'message' => __( 'This Profile Not Found', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }
    }


    function ajax_save_selected_email_profile() {
        $profile_id = !empty( $_POST['profile_id'] ) ? $_POST['profile_id'] : '';
        $area = !empty( $_POST['area'] ) ? $_POST['area'] : '';
        $nonce = !empty( $_POST['nonce'] ) ? $_POST['nonce'] : '';

        if ( !$nonce || !wp_verify_nonce( $nonce, get_current_user_id() . 'approve_ajax' ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Nonce is Incorrect', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

//            if ( empty( $profile_id ) ) {
//                exit( json_encode( array( 'status' => false, 'message' => __( 'Empty Email Profile Id', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
//            }
        if ( empty( $area ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'Empty Area', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        $all_profiles = WPC()->get_settings( 'email_sending_profiles' );

        if ( !empty( $profile_id ) && empty( $all_profiles[ $profile_id ] ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( 'This Email Profile not Found', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        //save selected profile
        WPC()->settings()->update( $profile_id, 'email_sending_profile_for_' . $area );

        exit( json_encode( array( 'status' => true, 'message' => __( 'Saved', WPC_CLIENT_TEXT_DOMAIN ) ) ) );

    }


    function ajax_get_shortcode_attributes_form() {
        WPC()->set_shortcode_data();

        if( empty( $_POST['shortcode'] ) || !isset( WPC()->shortcode_data[ $_POST['shortcode'] ] ) ) {
            status_header( 400 );
        }

        $data = WPC()->shortcode_data[ $_POST['shortcode'] ];

        ob_start(); ?>
        <div class="wpc_options">
            <?php if( !empty( $data['attributes'] ) && is_array( $data['attributes'] ) ) { ?>

                <table border="0" class="wpc_shortcodes_options_block">

                    <?php
                    foreach( $data['attributes'] as $key=>$val ) {
                        $val['name'] = $key;
                        $val['key'] = $key;
                        $val['id'] = 'wpc_' . $key . '_attribute';
                        $type = isset( $val['type'] ) ? $val['type'] : '';
                        $label = isset( $val['label'] ) ? $val['label'] : ''; ?>
                        <tr>
                            <td class="wpc_shortcodes_options_label">
                                <label for="<?php echo $val['id'] ?>"><?php echo $label ?></label>
                            </td>
                            <td class="wpc_shortcodes_options_setting">
                                <?php echo WPC()->admin()->generate_field_shortcode_attribute( $type, $val ) ?>
                            </td>
                        </tr>
                    <?php } ?>

                </table>

            <?php } else {

                if ( !empty( $data['content'] ) ) {
                    echo '[' . $_POST['shortcode'] . '] ';
                    echo $data['content'];
                    echo ' [/' . $_POST['shortcode'] . ']';
                } else {
                    echo '[' . $_POST['shortcode'] . ' /]';
                }

                if ( !empty( $data['description'] ) ) {
                    echo '<br>' . __( 'Description:', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . $data['description'] ;
                }
            } ?>
        </div>

        <?php $content = ob_get_clean();

        echo json_encode( $content );
        exit;
    }


    function update_widgets_order() {
        if( isset( $_POST['order'] ) && !empty( $_POST['order'] ) ) {
            $_POST['order'] = explode( ',', $_POST['order'] );

            update_user_meta( get_current_user_id(), 'wpc_client_widgets', $_POST['order'] );

            die('ok');
        }
    }


    function update_widgets_color() {
        if( isset( $_POST['color'] ) && !empty( $_POST['color'] ) && isset( $_POST['widget_id'] ) && !empty( $_POST['widget_id'] ) ) {

            $widget = get_user_meta( get_current_user_id(), 'wpc_client_widget_' . $_POST['widget_id'], true );

            $widget['color'] = $_POST['color'];

            update_user_meta( get_current_user_id(), 'wpc_client_widget_' . $_POST['widget_id'], $widget );

            die('ok');
        }
    }


    function collapse_widget() {
        if( isset( $_POST['collapse'] ) && isset( $_POST['widget_id'] ) && !empty( $_POST['widget_id'] ) ) {

            $widget = get_user_meta( get_current_user_id(), 'wpc_client_widget_' . $_POST['widget_id'], true );

            $widget['collapsed'] = ( isset( $_POST['collapse'] ) && 'true' == $_POST['collapse'] ) ? true : false;

            update_user_meta( get_current_user_id(), 'wpc_client_widget_' . $_POST['widget_id'], $widget );

            die('ok');
        }
    }

    function wpc_clients_dashboard_widget() {
        global $wpdb;

        $mananger_clients = '';
        $clients_ids = array();
        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $clients_ids = WPC()->members()->get_all_clients_manager();
            $mananger_clients = " AND ID IN ('" . implode( "','", $clients_ids ) . "')";
        }


        $not_approved_clients   = WPC()->members()->get_excluded_clients( 'to_approve' );
        $archive_client         = WPC()->members()->get_excluded_clients( 'archive' );
        if( 0 < count( $clients_ids ) ) {
            $archive_client     = array_intersect( $archive_client, $clients_ids );
        }
        $exclude_ids = array_merge( $not_approved_clients, $archive_client );

        $args = array(
            'blog_id'   => get_current_blog_id(),
            'role'      => 'wpc_client',
            'fields'    => 'ids',
            'exclude'   => $exclude_ids
        );
        $wpc_clients = get_users( $args );

        if( 0 < count( $clients_ids ) ) {
            $wpc_clients = array_intersect( $wpc_clients, $clients_ids );
        }

        $wpc_clients_count = 0;
        if( isset( $wpc_clients ) && !empty( $wpc_clients ) ) {
            $wpc_clients_count = count( $wpc_clients );
        }

        $start_current_month = strtotime( date( "Y-m", time() ) );
        $start_previous_month = strtotime( date( "Y-m", $start_current_month - 1 ) );

        $args = array(
            'blog_id'   => get_current_blog_id(),
            'role'      => 'wpc_client',
            'fields'    => 'ids'
        );
        $wpc_clients = get_users( $args );

        $previous_month_registrations = $wpdb->get_var(
            "SELECT COUNT(ID)
                FROM {$wpdb->users}
                WHERE ID IN( '" . implode( "','", $wpc_clients ) . "' )
                    $mananger_clients AND
                    user_registered < '" . date( "Y-m-d H:i:s", $start_current_month ) . "' AND
                    user_registered > '" . date( "Y-m-d H:i:s", $start_previous_month ) . "'" );

        $current_month_registrations = $wpdb->get_var(
            "SELECT COUNT(ID)
                FROM {$wpdb->users}
                WHERE ID IN( '" . implode( "','", $wpc_clients ) . "' )
                    $mananger_clients AND
                    user_registered >= '" . date( "Y-m-d H:i:s", $start_current_month ) . "'" );

        if( $previous_month_registrations == 0 ) {
            $previous_month_registrations = 1;
            $difference = round( ( $current_month_registrations * 100 ) / $previous_month_registrations, 2 );
        } else {
            $difference = round( ( $current_month_registrations * 100 ) / $previous_month_registrations - 100, 2 );
        }


        if( $difference >= 0 ) {
            $difference = $difference . '% ' . __( 'higher', WPC_CLIENT_TEXT_DOMAIN );
        } else {
            $difference = $difference * -1 . '% ' . __( 'less', WPC_CLIENT_TEXT_DOMAIN );
        }

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_clients_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'blue';

        ob_start(); ?>

        <!--  Client Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_clients_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php printf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) ?></div>
                </div>
                <div class="widget_content statistic">
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'Active', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo $wpc_clients_count ?></span>
                        </div>
                    </div>
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'For Approval', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo count( $not_approved_clients ) ?></span>
                        </div>
                    </div>
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'In Archive', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo count( $archive_client ) ?></span>
                        </div>
                    </div>
                    <div class="description"><span><?php echo $current_month_registrations ?>&nbsp;<span class="blend"><?php _e( 'registrations in this month', WPC_CLIENT_TEXT_DOMAIN ) ?></span></span></div>
                    <?php if( $current_month_registrations > 0 ) { ?>
                        <div class="description"><span><?php echo $difference ?>&nbsp;<span class="blend"><?php _e( 'than last month', WPC_CLIENT_TEXT_DOMAIN ) ?></span></span></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_client_staff_dashboard_widget() {
        global $wpdb;

        $mananger_staff = '';
        $staffs_ids = array();
        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $clients_ids = WPC()->members()->get_all_clients_manager();

            $staffs_ids = $wpdb->get_col( "SELECT u.ID
                    FROM {$wpdb->users} u
                    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'parent_client_id'
                    WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND
                        um.meta_value LIKE '%s:16:\"wpc_client_staff\";%' AND
                        um2.meta_value IN ('" . implode( "','", $clients_ids ) . "')"
            );

            $mananger_staff = " AND ID IN ('" . implode( "','", $staffs_ids ) . "')";
        }


        $not_approved_staff     = get_users( array( 'role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID' ) );
        if( 0 < count( $staffs_ids ) ) {
            $not_approved_staff = array_intersect( $not_approved_staff, $staffs_ids );
        }

        $args = array(
            'blog_id'   => get_current_blog_id(),
            'role'      => 'wpc_client_staff',
            'fields'    => 'ids',
            'exclude'   => $not_approved_staff
        );
        $wpc_client_staffs = get_users( $args );

        if( 0 < count( $staffs_ids ) ) {
            $wpc_client_staffs = array_intersect( $wpc_client_staffs, $staffs_ids );
        }

        $wpc_client_staffs_count = 0;
        if( isset( $wpc_client_staffs ) && !empty( $wpc_client_staffs ) ) {
            $wpc_client_staffs_count = count( $wpc_client_staffs );
        }

        $start_current_month = strtotime( date( "Y-m", time() ) );
        $start_previous_month = strtotime( date( "Y-m", $start_current_month - 1 ) );

        $args = array(
            'blog_id'   => get_current_blog_id(),
            'role'      => 'wpc_client_staff',
            'fields'    => 'ids',
        );
        $wpc_client_staffs = get_users( $args );

        $previous_month_registrations = $wpdb->get_var(
            "SELECT COUNT(ID)
                FROM {$wpdb->users}
                WHERE ID IN( '" . implode( "','", $wpc_client_staffs ) . "' )
                    $mananger_staff AND
                    user_registered < '" . date( "Y-m-d H:i:s", $start_current_month ) . "' AND
                    user_registered > '" . date( "Y-m-d H:i:s", $start_previous_month ) . "'" );

        $current_month_registrations = $wpdb->get_var(
            "SELECT COUNT(ID)
                FROM {$wpdb->users}
                WHERE ID IN( '" . implode( "','", $wpc_client_staffs ) . "' )
                    $mananger_staff AND
                    user_registered >= '" . date( "Y-m-d H:i:s", $start_current_month ) . "'" );

        if( $previous_month_registrations == 0 ) {
            $previous_month_registrations = 1;
            $difference = round( ( $current_month_registrations * 100 ) / $previous_month_registrations, 2 );
        } else {
            $difference = round( ( $current_month_registrations * 100 ) / $previous_month_registrations - 100, 2 );
        }


        if( $difference >= 0 ) {
            $difference = $difference . '% ' . __( 'higher ', WPC_CLIENT_TEXT_DOMAIN );
        } else {
            $difference = $difference * -1 . '% ' . __( 'less ', WPC_CLIENT_TEXT_DOMAIN );
        }

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_client_staff_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'green';

        ob_start(); ?>

        <!--  Staff Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_client_staff_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php printf( '%s %s', WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['p'] ) ?></div>
                </div>
                <div class="widget_content statistic">
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'Active', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo $wpc_client_staffs_count ?></span>
                        </div>
                    </div>
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'For Approval', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo count( $not_approved_staff ) ?></span>
                        </div>
                    </div>
                    <div class="description"><span><?php echo $current_month_registrations ?>&nbsp;<span class="blend"><?php _e( 'registrations in this month', WPC_CLIENT_TEXT_DOMAIN ) ?></span></span></div>
                    <?php if( $current_month_registrations > 0 ) { ?>
                        <div class="description"><span><?php echo $difference ?>&nbsp;<span class="blend"><?php _e( 'than last month', WPC_CLIENT_TEXT_DOMAIN ) ?></span></span></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_private_messages_dashboard_widget() {
        global $wpdb;

        $current_user_id = get_current_user_id();

        //get markers for chains with new messages
        $client_new_messages = WPC()->assigns()->get_assign_data_by_assign('new_message', 'client', $current_user_id);

        $chains_with_new_messages = $wpdb->get_col(
            "SELECT DISTINCT m.chain_id
                FROM {$wpdb->prefix}wpc_client_messages m
                WHERE m.id IN('" . implode( "','", $client_new_messages ) . "')"
        );

        $sql = "SELECT c.id
            FROM {$wpdb->prefix}wpc_client_chains c,
                ( SELECT *
                FROM {$wpdb->prefix}wpc_client_messages m
                ORDER BY m.date DESC ) AS m
            WHERE m.chain_id = c.id AND
                c.id IN( '" . implode( "','", $chains_with_new_messages ) . "' )
            GROUP BY m.chain_id";
        $items_count = $wpdb->get_col( $sql );
        $items_count = count( $items_count );

        $sql = "SELECT *, COUNT( m.id ) AS messages_count, m.id AS message_id, c.id AS c_id, u.user_login AS author_login
            FROM {$wpdb->prefix}wpc_client_chains c,
                {$wpdb->users} u,
                ( SELECT *
                FROM {$wpdb->prefix}wpc_client_messages m
                ORDER BY m.date DESC ) AS m
            WHERE m.chain_id = c.id  AND m.author_id = u.ID  AND
                c.id IN( '" . implode( "','", $chains_with_new_messages ) . "' )
            GROUP BY m.chain_id
            ORDER BY m.date DESC";

        $chains = $wpdb->get_results( $sql, ARRAY_A );

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_private_messages_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';

        ob_start(); ?>

        <!--  Private Messages Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_private_messages_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php _e( 'New Private Messages', WPC_CLIENT_TEXT_DOMAIN ) ?><?php echo ' (' . $items_count . ')' ?></div>
                </div>
                <div class="widget_content scrollbox">
                    <?php if( isset( $chains ) && !empty( $chains ) ) {
                        foreach( $chains as $chain ) { ?>
                            <div class="scroll_item wpc_private_message">
                                <div class="left_wrapper">
                                    <div class="item_header"><?php echo $chain['subject'] . ' (' . $chain['messages_count'] . ')' ?></div>
                                    <div class="item_content"><strong><?php echo $chain['author_login'] ?>: </strong><?php echo $chain['content'] ?></div>
                                    <div class="action_links">
                                        <a href="admin.php?page=wpclients_content&tab=private_messages&action=mark&_wpnonce=<?php echo wp_create_nonce( 'wpc_message_read' . $chain['c_id'] . get_current_user_id() ) ?>&id=<?php echo $chain['c_id'] ?>" title="<?php _e( 'Mark as Read', WPC_CLIENT_TEXT_DOMAIN ) ?>" target="_blank"><?php _e( 'Mark as Read', WPC_CLIENT_TEXT_DOMAIN ) ?></a> |
                                        <a href="admin.php?page=wpclients_content&tab=private_messages&read_reply=<?php echo $chain['c_id'] ?>" title="<?php _e( 'Read & Reply', WPC_CLIENT_TEXT_DOMAIN ) ?>" target="_blank"><?php _e( 'Read & Reply', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                    </div>
                                </div>
                                <div class="right_wrapper"><?php echo WPC()->date_format( $chain['date'] ) ?></div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="empty_content"><?php _e( 'You don\'t have New Private Messages', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_portal_pages_dashboard_widget() {
        global $wpdb;

        $portal_pages = $wpdb->get_results(
            "SELECT p.ID AS id,
                    p.post_title AS title,
                    p.post_status AS status,
                    p.post_date AS date,
                    cppc.cat_name AS category,
                    pm2.meta_value AS page_order
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_category_id' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON( p.ID = pm2.post_id AND pm2.meta_key = '_wpc_order_id' )
                LEFT JOIN {$wpdb->prefix}wpc_client_portal_page_categories cppc ON pm1.meta_value = cppc.cat_id
                WHERE p.post_type = 'clientspage' AND p.post_status='publish'
                ORDER BY p.post_modified DESC
                LIMIT 0, 10",
            ARRAY_A );

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_portal_pages_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';

        ob_start(); ?>

        <!--  Portal Pages Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_portal_pages_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php printf( __( 'Last 10 %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?></div>
                </div>
                <div class="widget_content scrollbox">
                    <?php if( isset( $portal_pages ) && !empty( $portal_pages ) ) {
                        foreach( $portal_pages as $portal_page ) { ?>
                            <div class="scroll_item wpc_portal_page">
                                <div class="left_wrapper">
                                    <div class="item_header"><?php echo $portal_page['title'] ?></div>

                                    <?php if( current_user_can('wpc_admin_user_login') ) {
                                        if( isset( $_SERVER['HTTP_REFERER'] )&& !empty( $_SERVER['HTTP_REFERER'] ) ) {
                                            $current_url = $_SERVER['HTTP_REFERER'];
                                        } else {
                                            $schema = is_ssl() ? 'https://' : 'http://';
                                            $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                                        }

                                        $pp_preview_url = get_admin_url( null,'admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . get_current_user_id() . $portal_page['id'] ) . '&page_name=portal_page&page_id=' . $portal_page['id'] . '&referer_url=' . urlencode( $current_url ) );
                                    } else {
                                        $pp_preview_url = get_permalink( $portal_page['id'] );
                                    } ?>

                                    <div class="action_links">
                                        <a href="<?php echo $pp_preview_url ?>" onclick="return confirm('<?php printf( __( "You will be re-logged-in under the role of %s to preview this page. Continue?", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?>');"><?php _e( 'View', WPC_CLIENT_TEXT_DOMAIN ) ?></a> |
                                        <a href="post.php?post=<?php echo $portal_page['id'] ?>&action=edit" target="blank_" title="<?php esc_attr( __( 'Edit this item', WPC_CLIENT_TEXT_DOMAIN ) ) ?>"><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                    </div>
                                </div>
                                <div class="right_wrapper"><?php echo WPC()->date_format( strtotime( $portal_page['date'] ) ) ?></div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="empty_content"><?php printf( __( 'You don\'t have %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_client_circles_dashboard_widget() {
        global $wpdb;

        $groups = $wpdb->get_results(
            "SELECT c.*, COUNT( cc.client_id ) AS num_clients
                FROM {$wpdb->prefix}wpc_client_groups c
                LEFT JOIN {$wpdb->prefix}wpc_client_group_clients cc ON c.group_id = cc.group_id
                GROUP BY cc.group_id
                ORDER BY num_clients DESC
                LIMIT 0, 5",
            ARRAY_A );

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_client_circles_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';

        ob_start(); ?>

        <!--  Client Circles Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_client_circles_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php printf( __( 'Top-5 %s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['p'] ) ?></div>
                </div>
                <div class="widget_content scrollbox">
                    <?php if( isset( $groups ) && !empty( $groups ) ) {
                        foreach( $groups as $group ) {
                            if( $group['num_clients'] == 0 ) {
                                continue;
                            } ?>
                            <div class="scroll_item wpc_circle">
                                <div class="left_wrapper">
                                    <div class="item_header"><?php echo $group['group_name'] ?></div>
                                </div>
                                <div class="right_wrapper">
                                    <?php echo $group['num_clients'] . ' ' . sprintf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ); ?>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="empty_content"><?php printf( __( 'You don\'t have %s with %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['s'] ) ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_managers_dashboard_widget() {
        global $wpdb;

        $managers = $wpdb->get_results(
            "SELECT u.ID as id,
                    u.user_login as username,
                    u.user_nicename as nickname,
                    u.user_email as email,
                    um2.meta_value as auto_add_clients,
                    um3.meta_value as time_resend
                FROM {$wpdb->users} u
                LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'wpc_auto_assigned_clients'
                LEFT JOIN {$wpdb->usermeta} um3 ON ( u.ID = um3.user_id AND um3.meta_key = 'wpc_send_welcome_email' )
                WHERE
                    um.meta_key = '{$wpdb->prefix}capabilities'
                    AND um.meta_value LIKE '%s:11:\"wpc_manager\";%'
                ORDER BY u.user_registered DESC",
            ARRAY_A );

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_managers_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';

        ob_start(); ?>

        <!--  Client Circles Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_managers_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php printf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ) ?><?php echo ' (' . count( $managers ) . ')' ?></div>
                </div>
                <div class="widget_content scrollbox">
                    <?php if( isset( $managers ) && !empty( $managers ) ) {
                        foreach( $managers as $manager ) { ?>
                            <div class="scroll_item wpc_manager">
                                <div class="left_wrapper">
                                    <div class="item_header"><?php echo $manager['username'] ?></div>
                                    <div class="item_content"><?php echo $manager['email'] ?></div>
                                </div>
                                <div class="right_wrapper">
                                    <a href="admin.php?page=wpclient_clients&tab=managers_edit&id=<?php echo $manager['id'] ?>" target="blank_"><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="empty_content"><?php printf( __( 'You don\'t have %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['p'] ) ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_files_dashboard_widget() {
        global $wpdb;

        //information for manager
        $where_manager = '';
        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
            $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            foreach( $manager_circles as $c_id ) {
                $manager_clients = array_merge( $manager_clients, WPC()->groups()->get_group_clients_id( $c_id ) );
            }
            $manager_clients = array_unique( $manager_clients );

            foreach( $manager_clients as $client_id ) {
                $manager_circles = array_merge( $manager_circles, WPC()->groups()->get_client_groups_id( $client_id ) );
            }
            $manager_circles = array_unique( $manager_circles );

            $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $manager_clients );
            $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $manager_circles );
            $client_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $manager_clients );
            $circle_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $manager_circles );
            $cc_categories = array_unique( array_merge( $client_categories, $circle_categories ) );

            $cc_categories_temp = $cc_categories;
            foreach( $cc_categories as $cat_id ) {
                $cc_categories_temp = array_merge( $cc_categories_temp, WPC()->files()->get_category_children_ids( $cat_id ) );
            }
            $cc_categories = array_unique( $cc_categories_temp );
            $all_files = array_merge( $client_files, $circle_files );
            $all_files = array_unique( $all_files );

            if ( current_user_can( 'wpc_view_admin_managers_files' ) ) {
                if( count( $cc_categories ) ) {
                    $where = "f.cat_id IN ( '" . implode( "','", $cc_categories ) . "' ) OR";
                } else {
                    $where = '';
                }
                $where_manager .= " AND ( $where ( f.page_id = 0 ) OR f.id IN('" . implode( "','", $all_files ) . "') OR f.user_id IN('" . implode( "','", $manager_clients ) . "'))";
            } else {
                if( count( $cc_categories ) ) {
                    $where = "OR ( f.cat_id IN ( '" . implode( "','", $cc_categories ) . "' ) AND ( f.page_id != 0 OR
                                (
                                    f.page_id = 0 AND
                                    f.user_id = 0
                                )
                            )
                        )";
                } else {
                    $where = '';
                }
                $where_manager .= " AND (
                        f.user_id = " . get_current_user_id() . " OR
                        (
                            f.id IN('" . implode( "','", $all_files ) . "') AND
                            (
                                f.page_id != 0 OR
                                (
                                    f.page_id = 0 AND
                                    f.user_id = 0
                                )
                            )
                        ) OR
                        f.user_id IN('" . implode( "','", $manager_clients ) . "')
                        $where )";
            }
        }

        $statistic = $wpdb->get_row(
            "SELECT COUNT(f.id) AS total,
                    SUM(f.size) AS size
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE 1=1 $where_manager",
            ARRAY_A );

        $files_count = ( isset( $statistic['total'] ) && !empty( $statistic['total'] ) ) ? $statistic['total'] : '0';
        $files_size = ( isset( $statistic['size'] ) && !empty( $statistic['size'] ) ) ? $statistic['size'] : '0';

        $files_avg_size = 0;
        if( isset( $files_count ) && !empty( $files_count ) && isset( $files_size ) && !empty( $files_size ) ) {
            $files_avg_size = round( $files_size/$files_count );
        }


        $start_current_month = strtotime( date( "Y-m", time() ) );
        $start_previous_month = strtotime( date( "Y-m", $start_current_month - 1 ) );

        $previous_month_files = $wpdb->get_var(
            "SELECT COUNT(f.id)
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE time < '" . $start_current_month . "' AND
                    time > '" . $start_previous_month . "'
                    $where_manager" );

        $current_month_files = $wpdb->get_var(
            "SELECT COUNT(f.id)
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE f.time >= '" . $start_current_month . "'
                    $where_manager" );

        if( $previous_month_files == 0 ) {
            $previous_month_files = 1;
            $difference = round( ( $current_month_files * 100 ) / $previous_month_files, 2 );
        } else {
            $difference = round( ( $current_month_files * 100 ) / $previous_month_files - 100, 2 );
        }


        if( $difference >= 0 ) {
            $difference = $difference . '% ' . __( 'higher', WPC_CLIENT_TEXT_DOMAIN );
        } else {
            $difference = $difference * -1 . '% ' . __( 'less', WPC_CLIENT_TEXT_DOMAIN );
        }

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_files_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'red';


        ob_start(); ?>

        <!--  Files Statistic Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_files_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php _e( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                </div>
                <div class="widget_content statistic">
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'Total', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo $files_count ?></span>
                        </div>
                    </div>
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'Size', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo WPC()->format_bytes( $files_size ) ?></span>
                        </div>
                    </div>
                    <div class="widget_stats">
                        <div class="wrapper">
                            <span class="item_title"><?php _e( 'Avg. Size', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <span class="item_count"><?php echo WPC()->format_bytes( $files_avg_size ) ?></span>
                        </div>
                    </div>
                    <div class="description"><span><?php echo $current_month_files ?>&nbsp;<span class="blend"><?php _e( 'files uploaded in this month', WPC_CLIENT_TEXT_DOMAIN ) ?></span></span></div>
                    <?php if( $current_month_files > 0 ) { ?>
                        <div class="description"><span><?php echo $difference ?>&nbsp;<span class="blend"><?php _e( 'than last month', WPC_CLIENT_TEXT_DOMAIN ) ?></span></span></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_top_files_dashboard_widget() {
        global $wpdb;

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        $files = $wpdb->get_results(
            "SELECT f.*, COUNT( dl.id ) AS num_downloads
                FROM {$wpdb->prefix}wpc_client_files f
                LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log dl ON dl.file_id = f.id
                GROUP BY f.id
                ORDER BY num_downloads DESC
                LIMIT 0, 10",
            ARRAY_A );

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_top_files_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';

        ob_start(); ?>

        <!--  Top-10 Files Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_top_files_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php _e( 'Top-10 Downloaded Files', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                </div>
                <div class="widget_content scrollbox">
                    <?php if( isset( $files ) && !empty( $files ) ) {
                        $zero_count = 0;
                        foreach( $files as $file ) {
                            if( $file['num_downloads'] == 0 ) {
                                $zero_count++;
                                continue;
                            }

                            $file_type = explode( '.', $file['filename'] );
                            $file_type = strtolower( end( $file_type ) );
                            $actions['view'] = '';
                            if( ( isset( $wpc_file_sharing['google_doc_embed'] ) && 'yes' == $wpc_file_sharing['google_doc_embed'] && in_array( $file_type, array_keys( WPC()->files()->files_for_google_doc_view ) ) ) ||
                                in_array( $file_type, WPC()->files()->files_for_regular_view ) ) {
                                $actions['view'] = '<a href="' . get_admin_url() . 'admin.php?wpc_action=view&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $file['id'] ) . '&id=' . $file['id'] . '&d=false&t=' . $file_type .'" target="_blank" title="' . __( 'view', WPC_CLIENT_TEXT_DOMAIN ) . '" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ). '</a> |';
                            }

                            $actions['download'] = '<a href="' . get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $file['id'] ) . '&id=' . $file['id'] . '" title="' . __( 'download', WPC_CLIENT_TEXT_DOMAIN ) . ' \' ' . $file['name'] . '\'" >' . __( 'Download', WPC_CLIENT_TEXT_DOMAIN ). '</a>'; ?>

                            <div class="scroll_item wpc_file">
                                <div class="left_wrapper">
                                    <div class="item_header"><?php echo $file['title'] ?></div>
                                    <div class="item_content"><?php echo ( isset( $file['description'] ) ) ? $file['description'] : '' ?></div>
                                    <div class="action_links">
                                        <?php echo $actions['view'] . ' ' . $actions['download'] ?>
                                    </div>
                                </div>
                                <div class="right_wrapper">
                                    <?php echo $file['num_downloads'] . ' ' . __( 'Downloads', WPC_CLIENT_TEXT_DOMAIN ); ?>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }
                        if( $zero_count == count( $files ) ) { ?>
                            <div class="empty_content"><?php _e( 'You don\'t have Downloaded Files', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                        <?php }
                    } else { ?>
                        <div class="empty_content"><?php _e( 'You don\'t have Files', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function wpc_settings_info_dashboard_widget() {

        $wpc_general = WPC()->get_settings( 'general' );
        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        $wpc_custom_login = WPC()->get_settings( 'custom_login' );

        $summary_array = array(
            array(
                'title' => sprintf( __( '%s Self-Registration Enabled?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'value' => ( isset( $wpc_clients_staff['client_registration'] ) && 'yes' == $wpc_clients_staff['client_registration'] ) ? __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) : __( 'No', WPC_CLIENT_TEXT_DOMAIN )
            ),
            array(
                'title' => sprintf( __( '%s %s Registration Enabled?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['s'] ),
                'value' => ( isset( $wpc_clients_staff['staff_registration'] ) && 'yes' == $wpc_clients_staff['staff_registration'] ) ? __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) : __( 'No', WPC_CLIENT_TEXT_DOMAIN )
            ),
            array(
                'title' => __( 'Custom Login at /wp-admin Enabled?', WPC_CLIENT_TEXT_DOMAIN ),
                'value' => ( !isset( $wpc_custom_login['cl_enable'] ) || 'no' == $wpc_custom_login['cl_enable'] ) ? __( 'No', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Yes', WPC_CLIENT_TEXT_DOMAIN )
            )
        );

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_wpc_settings_info_dashboard_widget', true );
        $widget_options['color'] = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';

        ob_start(); ?>

        <!--  Summary Settings Widget  -->
        <div class="tiles <?php echo $widget_options['color'] ?>">
            <div class="tile_body">
                <div class="widget_header">
                    <div class="widget_control"><?php echo WPC()->widgets()->widget_controls( 'wpc_settings_info_dashboard_widget' ) ?></div>
                    <div class="tile_title"><?php _e( 'Settings Summary', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                </div>
                <div class="widget_content scrollbox">
                    <?php foreach( $summary_array as $setting ) { ?>
                        <div class="scroll_item wpc_setting">
                            <div class="left_wrapper">
                                <div class="item_header"><?php echo $setting['title'] ?></div>
                            </div>
                            <div class="right_wrapper"><?php echo $setting['value'] ?></div>
                            <div class="clearfix"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php $widget = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        echo $widget;
        exit;
    }


    function ajax_set_rating() {
        update_option( 'wpc_client_rate', true );
        exit( json_encode( array( 'status' => true ) ) );
    }

    function ajax_generate_password() {
        exit( json_encode( array( 'status' => true, 'message' => WPC()->members()->generate_password() ) ) );
    }


    function ajax_save_priority() {
        if( !empty( $_POST['type'] ) ) {
            $id = 0;
            if( !empty( $_POST['id'] ) ) {
                $id = $_POST['id'];
            }
            switch( $_POST['type'] ) {
                case 'portalhub':
                    //check permission
                    if ( ! current_user_can( 'wpc_admin' ) && ! current_user_can( 'administrator' ) && ! current_user_can( 'edit_others_portalhubs' ) ) {
                        exit( json_encode( array( 'status' => false, 'message' => __( "Error of permissions.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    if( !empty( $_POST['value'] ) && (int)$_POST['value'] > 0 ) {
                        $value = (int)$_POST['value'];
                    } else {
                        $value = 0;
                    }

                    $portahub = get_post( $id );
                    if( ! empty( $portahub ) ) {
                        update_post_meta( $id, 'wpc_template_priority', $value );
                        exit( json_encode( array( 'status' => true, 'message' => $value ) ) );
                    } else {
                        exit( json_encode( array( 'status' => false, 'message' => 'Wrong ID' ) ) );
                    }
                    break;
                default:
                    exit( json_encode( array( 'status' => false, 'message' => 'Wrong type' ) ) );
                    break;
            }
        } else {
            exit( json_encode( array( 'status' => false, 'message' => 'Wrong type' ) ) );
        }
    }


    function ajax_get_user_list() {
        global $wpdb, $wp_roles;
        $where = '';
        if( !empty( $_POST['exclude'] ) ) {
            $where = ' AND u.ID NOT IN(' . $_POST['exclude'] . ')';
        }

        $wpc_admin_list = $wpdb->get_results( "SELECT DISTINCT u.ID, u.user_login
                FROM {$wpdb->users} u, {$wpdb->usermeta} um
                WHERE u.ID = um.user_id AND
                    um.meta_key = '{$wpdb->prefix}capabilities' AND
                    um.meta_value LIKE '%\"wpc_admin\"%'" . $where, ARRAY_A );

        $admin_list = $wpdb->get_results( "SELECT DISTINCT u.ID, u.user_login
                FROM {$wpdb->users} u, {$wpdb->usermeta} um
                WHERE u.ID = um.user_id AND
                    um.meta_key = '{$wpdb->prefix}capabilities' AND
                    um.meta_value LIKE '%\"administrator\"%'" . $where, ARRAY_A );
        $manager_list = $wpdb->get_results( "SELECT DISTINCT u.ID, u.user_login
                FROM {$wpdb->users} u, {$wpdb->usermeta} um
                WHERE u.ID = um.user_id AND
                    um.meta_key = '{$wpdb->prefix}capabilities' AND
                    um.meta_value LIKE '%\"wpc_manager\"%'" . $where, ARRAY_A );
        $client_list = $wpdb->get_results( "SELECT DISTINCT u.ID, u.user_login
                FROM {$wpdb->users} u, {$wpdb->usermeta} um
                WHERE u.ID = um.user_id AND
                    um.meta_key = '{$wpdb->prefix}capabilities' AND
                    um.meta_value LIKE '%\"wpc_client\"%'" . $where, ARRAY_A );


        $output_list = array();
        if( count( $admin_list ) ) {
            $output_list[] = array(
                'title' => $wp_roles->roles['administrator']['name'],
                'items' => $admin_list
            );
        }
        if( count( $wpc_admin_list ) ) {
            $output_list[] = array(
                'title' => $wp_roles->roles['wpc_admin']['name'],
                'items' => $wpc_admin_list
            );
        }
        if( count( $manager_list ) ) {
            $output_list[] = array(
                'title' => $wp_roles->roles['wpc_manager']['name'],
                'items' => $manager_list
            );
        }
        if( count( $client_list ) ) {
            $output_list[] = array(
                'title' => $wp_roles->roles['wpc_client']['name'],
                'items' => $client_list
            );
        }

        ob_start(); ?>

        <form id="delete_user_settings" method="get" style="float:left;width:100%;">
            <h2>
                <?php $user_id = explode( ',', $_POST['exclude'] );
                if ( user_can( $user_id[0], 'wpc_client' ) ) {
                    printf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] );
                } elseif ( user_can( $user_id[0], 'wpc_manager' ) ) {
                    printf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] );
                } elseif ( user_can( $user_id[0], 'wpc_admin' ) ) {
                    printf( __( 'Are you sure you want to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] );
                } ?>
            </h2>

            <h3><?php _e( 'What should be done with files owned by this user?', WPC_CLIENT_TEXT_DOMAIN ); ?></h3>
            <p>
                <label>
                    <input type="radio" name="delete_user_settings[files]" value="remove" checked="checked" />
                    <?php _e( 'Remove files', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </label> <br />

                <label>
                    <input type="radio" name="delete_user_settings[files]" value="reassign" />
                    <?php _e( 'Reassign files to', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </label>
                <select name="delete_user_settings[files_user]" id="delete_settings_user_list">
                    <?php foreach( $output_list as $output ) { ?>
                        <optgroup label="<?php echo $output['title'] ?>">
                            <?php foreach( $output['items'] as $item ) { ?>
                                <option value="<?php echo $item['ID'] ?>"><?php echo $item['user_login'] ?></option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                </select>
            </p>

            <?php $user_id = explode( ',', $_POST['exclude'] );
            if( user_can( $user_id[0], 'wpc_client' ) ) { ?>
                <hr />
                <h3><?php printf( __( 'What should be done with %s %s?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['s'] ); ?></h3>
                <p>
                    <label>
                        <input type="radio" name="delete_user_settings[staff]" value="remove" checked="checked" />
                        <?php printf( __( 'Remove %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ); ?>
                    </label> <br />

                    <label>
                        <input type="radio" name="delete_user_settings[staff]" value="unassign" />
                        <?php printf( __( 'Unassign %s from %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'], WPC()->custom_titles['client']['s'] ); ?>
                    </label>
                </p>
            <?php } ?>

            <hr />
            <h3><?php _e( 'What should be done with messages sent by this user?', WPC_CLIENT_TEXT_DOMAIN ); ?></h3>
            <p>
                <label>
                    <input type="radio" name="delete_user_settings[messages]" value="remove" checked="checked" />
                    <?php _e( 'Remove messages', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </label> <br />

                <label>
                    <input type="radio" name="delete_user_settings[messages]" value="leave" />
                    <?php _e( 'Leave in message history', WPC_CLIENT_TEXT_DOMAIN ); ?>
                </label>
            </p>

            <?php $user_id = explode( ',', $_POST['exclude'] );
            if( user_can( $user_id[0], 'wpc_manager' ) &&  count( $manager_list ) ) { ?>
                <hr />
                <h3><?php printf( __( 'What should be done with %s and %s assigned this user?', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ); ?></h3>
                <p>
                    <label>
                        <input type="radio" name="delete_user_settings[assign]" value="remove" checked="checked" />
                        <?php _e( 'Remove assigns', WPC_CLIENT_TEXT_DOMAIN ); ?>
                    </label> <br />

                    <label>
                        <input type="radio" name="delete_user_settings[assign]" value="reassign" />
                        <?php printf( __( 'Reassign %s and %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ); ?>
                    </label>
                    <select name="delete_user_settings[user_assign]" id="delete_settings_manager_list">
                        <?php foreach( $manager_list as $item ) { ?>
                            <option value="<?php echo $item['ID'] ?>"><?php echo $item['user_login'] ?></option>
                        <?php } ?>
                    </select>
                </p>
            <?php } ?>
            <p>
                <input type="button" class="button-primary delete_user_button" value="<?php _e( 'Delete user', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
                <input type="button" class="button cancel_delete_button" style="float: right;" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?>" />
            </p>
        </form>

        <?php $content = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        echo json_encode( array(
            'title'     => __( 'Delete User', WPC_CLIENT_TEXT_DOMAIN ),
            'content'   => $content
        ) );
        exit;

        exit( json_encode( array( 'status' =>true, 'message' => array( 'all' => $output_list , 'managers' => $manager_list ) ) ) );
    }


    function ajax_update_last_activity() {
        $user_id = get_current_user_id();
        $last_activity_date = get_user_meta( $user_id, 'wpc_last_activity_date', true );
        check_ajax_referer( get_current_user_id() . SECURE_AUTH_SALT . $last_activity_date, 'security' );
        update_user_meta( $user_id, 'wpc_last_activity_date', date('Y-m-d H:i:s') );
        exit;
    }


    function ajax_get_all_file_tags() {
        $args = array(
            'hide_empty'        => false,
            'fields'            => 'names',
        );
        $all_file_tags = get_terms( 'wpc_file_tags', $args ) ;
        echo json_encode( $all_file_tags ) ;
        exit;
    }


    function ajax_get_all_tags() {
        $args = array(
            'hide_empty'        => false,
            'fields'            => 'names',
        );
        $all_file_tags = get_terms( 'wpc_tags', $args ) ;
        echo json_encode( $all_file_tags ) ;
        exit;
    }


    /*function ajax_get_file_tags( $file_id ) {
            echo("hfgjd");exit;
            $file_tags = array();

            if ( 0 < (int)$file_id )
                $file_tags = wp_get_object_terms( (int)$file_id, 'wpc_file_tags', array( 'fields' => 'names') ) ;

            echo json_encode( $file_tags ) ;
            exit;
        }   */


    function ajax_remote_sync() {

        $sync_key = get_option( 'wpc_client_sync_key' );

        if( isset( $_REQUEST['key'] ) && $_REQUEST['key'] == $sync_key ) {

            WPC()->files()->synchronize_with_ftp();

        }
        exit;
    }


    function ajax_return_to_admin_panel() {
        global $wpdb;
        if( !empty( $_POST['secure_key'] ) ) {
            $verify = $_POST['secure_key'];
        } else {
            exit( json_encode( array( 'status' => false, 'message' => __( "Wrong data", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if( !empty( $_COOKIE['wpc_key'] ) && is_user_logged_in() ) {
            $key = $_COOKIE['wpc_key'];
            $user_data = $wpdb->get_row( $wpdb->prepare( "SELECT umeta_id, user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'wpc_client_admin_secure_data' AND meta_value LIKE '%s'", '%"' . md5( $key ) . '"%' ), ARRAY_A );

            if( isset( $user_data['user_id'] ) && user_can( $user_data['user_id'], 'wpc_admin_user_login') && wp_verify_nonce( $verify, get_current_user_id() . $user_data['user_id'] ) ) {
                if( !empty( $user_data['meta_value'] ) ) {
                    $secure_array = unserialize( $user_data['meta_value'] );
                    if( isset( $secure_array['end_date'] ) && $secure_array['end_date'] > time() &&
                        isset( $secure_array['client_id'] ) && get_current_user_id() == $secure_array['client_id'] &&
                        isset( $secure_array['ip'] ) && $_SERVER['REMOTE_ADDR'] == $secure_array['ip'] ) {


                        wp_set_auth_cookie( $user_data['user_id'], true );

                        wp_get_current_user();

                        $wpdb->delete( $wpdb->usermeta,
                            array(
                                'umeta_id' => $user_data['umeta_id']
                            )
                        );

                        WPC()->setcookie( "wpc_key", '', time() - 1 );

                        if( !empty( $secure_array['return_url'] ) ) {
                            $url = $secure_array['return_url'];
                        } else {
                            $url = admin_url('admin.php?page=wpclient_clients');
                        }

                        if( !empty( $_POST['relogin'] ) && !empty( $_POST['page_id'] ) ) {
                            $id = $_POST['relogin'];
                            $url = get_admin_url( null, 'admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . $user_data['user_id'] . $_POST['page_id'] ) . '&page_name=portal_page&client_id=' . $id . '&page_id=' . $_POST['page_id'] . '&referer_url=' . urlencode( $secure_array['return_url'] ) );
                        }

                        exit( json_encode( array( 'status' => true, 'message' => $url ) ) );
                    }
                }
            }
        }

        exit( json_encode( array( 'status' => false, 'message' => __( "Wrong data", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
    }


    function ajax_hub_set_default() {
        //check permission
        if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && ! current_user_can( 'edit_others_portalhubs' ) ) {
            exit( json_encode( array( 'status' => false, 'message' => __( "Error of permissions.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }

        if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
            $id = $_POST['id'];

            $portahub = get_post( $id );
            if ( ! empty( $portahub ) ) {
                $portahubs = get_posts( array(
                    'post_type'         => 'portalhub',
                    'post_status'       => 'any',
                    'posts_per_page'    => -1,
                    'fields'            => 'ids'
                ) );

                foreach( $portahubs as $portahub_id ) {
                    delete_post_meta( $portahub_id, 'wpc_default_template' );
                }
                update_post_meta( $id, 'wpc_default_template', true );
                exit( json_encode( array( 'status' => true, 'message' => __( "Success", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            } else {
                exit( json_encode( array( 'status' => false, 'message' => __( "Hub template does not exists.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }
        } else {
            exit( json_encode( array( 'status' => false, 'message' => __( "Hub ID does not exists.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }
    }



    function ajax_resize_all_thumbnails() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $files = $wpdb->get_results(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE size IS NOT NULL AND
                    external = '0' AND
                    (f.filename LIKE '%.jpg' OR
                    f.filename LIKE '%.jpeg' OR
                    f.filename LIKE '%.png' OR
                    f.filename LIKE '%.gif')",
            ARRAY_A );

        if( isset( $files ) && !empty( $files ) ) {

            $settings = array(
                'wp_thumbnail'      => ( !empty( $_POST['wp_thumbnail'] ) ) ? $_POST['wp_thumbnail'] : 'yes',
                'thumbnail_size_w'  => ( !empty( $_POST['thumbnail_size_w'] ) ) ? $_POST['thumbnail_size_w'] : 0,
                'thumbnail_size_h'  => ( !empty( $_POST['thumbnail_size_h'] ) ) ? $_POST['thumbnail_size_h'] : 0,
                'thumbnail_crop'    => ( !empty( $_POST['thumbnail_crop'] ) ) ? $_POST['thumbnail_crop'] : false,
            );

            foreach( $files as $file ) {

                if( isset( $file['filename'] ) && !empty( $file['filename'] ) ) {

                    $thumbnail_filepath = WPC()->files()->get_file_path( $file, true );

                    if( file_exists( $thumbnail_filepath ) ) {
                        unlink( $thumbnail_filepath );
                    }

                    WPC()->files()->create_image_thumbnail( $file, $settings );
                }

            }

            $answer = json_encode( array( 'status' => true, 'message' => __( 'Resize was completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
        } else {
            $answer = json_encode( array( 'status' => false, 'message' => __( 'No Files', WPC_CLIENT_TEXT_DOMAIN ) ) );
        }

        echo $answer;
        exit;

    }


    function ajax_settings() {
        if( isset( $_POST['tab'] ) && !empty( $_POST['tab'] ) ) {
            $tab = $_POST['tab'];
            $action = isset( $_POST['act'] ) ? $_POST['act'] : '';
            switch( $action ) {
                case 'add':
                    $default = isset( $_POST['default'] ) ? $_POST['default'] : 0;
                    if( isset( $_POST['title'] ) && !empty( $_POST['title'] ) ) {
                        $title = $_POST['title'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Title is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    if( isset( $_POST['code'] ) && !empty( $_POST['code'] ) ) {
                        $code = $_POST['code'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    if( isset( $_POST['symbol'] ) && !empty( $_POST['symbol'] ) ) {
                        $symbol = $_POST['symbol'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    $align = isset( $_POST['align'] ) ? $_POST['align'] : 'left';

                    $wpc_currency = WPC()->get_settings( $tab );
                    if( $default ) {
                        foreach( $wpc_currency as $k=>$val ) {
                            $wpc_currency[ $k ]['default'] = 0;
                        }
                    }

                    $key = uniqid();
                    $wpc_currency[ $key ] = array(
                        'default' => $default,
                        'title' => $title,
                        'code' => $code,
                        'symbol' => $symbol,
                        'align' => $align
                    );

                    WPC()->settings()->update( $wpc_currency, $tab );
                    exit( json_encode( array( 'status' => true, 'message' => $key ) ) );
                    break;
                case 'edit':
                    if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                        $id = $_POST['id'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Code does not exists.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    $default = isset( $_POST['default'] ) ? $_POST['default'] : 0;
                    if( isset( $_POST['title'] ) && !empty( $_POST['title'] ) ) {
                        $title = $_POST['title'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Title is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    if( isset( $_POST['code'] ) && !empty( $_POST['code'] ) ) {
                        $code = $_POST['code'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    if( isset( $_POST['symbol'] ) && !empty( $_POST['symbol'] ) ) {
                        $symbol = $_POST['symbol'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }
                    $align = isset( $_POST['align'] ) ? $_POST['align'] : 'left';

                    $wpc_currency = WPC()->get_settings( $tab );

                    if( $default ) {
                        foreach( $wpc_currency as $k=>$val ) {
                            $wpc_currency[ $k ]['default'] = 0;
                        }
                    }

                    $wpc_currency[ $id ] = array(
                        'default' => $default,
                        'title' => $title,
                        'code' => $code,
                        'symbol' => $symbol,
                        'align' => $align
                    );

                    WPC()->settings()->update( $wpc_currency, $tab );
                    exit( json_encode( array( 'status' => true, 'message' => 'Success' ) ) );
                    break;
                case 'delete':
                    if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                        $id = $_POST['id'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Key is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    $wpc_currency = WPC()->get_settings( $tab );
                    if( isset( $wpc_currency[ $id ]['default'] ) && $wpc_currency[ $id ]['default'] == 1 ) {
                        exit( json_encode( array( 'status' =>false, 'message' => __("You can't remove currency with default mark", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    $permission = apply_filters( 'wpc_currency_permission', $id );
                    if( isset( $permission ) && $permission != $id && !$permission ) {
                        exit( json_encode( array( 'status' =>false, 'message' => __("Currency already in use", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    if( isset( $wpc_currency[ $id ] ) ) {
                        unset( $wpc_currency[ $id ] );
                    }

                    WPC()->settings()->update( $wpc_currency, $tab );
                    exit( json_encode( array( 'status' => true, 'message' => 'Success' ) ) );
                    break;
                case 'set_default':
                    if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                        $id = $_POST['id'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Key is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    $wpc_currency = WPC()->get_settings( $tab );
                    foreach( $wpc_currency as $k=>$val ) {
                        $wpc_currency[ $k ]['default'] = 0;
                    }
                    if( isset( $wpc_currency[ $id ] ) ) {
                        $wpc_currency[ $id ]['default'] = 1;
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Key is wrong', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    WPC()->settings()->update( $wpc_currency, $tab );
                    exit( json_encode( array( 'status' => true, 'message' => 'Success' ) ) );
                    break;
                case 'get_data':
                    if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                        $id = $_POST['id'];
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Key is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    $wpc_currency = WPC()->get_settings( $tab );
                    if( isset( $wpc_currency[ $id ] ) ) {
                        exit( json_encode( array( 'status' => true, 'message' => $wpc_currency[ $id ] ) ) );
                    } else {
                        exit( json_encode( array( 'status' =>false, 'message' => __('Key is wrong', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    break;
            }
        }
        exit;
    }



    function ajax_files_shortcode_get_filter() {
        WPC()->files()->ajax_files_shortcode_get_filter();
    }


    function ajax_get_filter_data() {
        WPC()->files()->ajax_get_filter_data();
    }


    function ajax_files_shortcode_table_pagination() {
        WPC()->files()->ajax_files_shortcode_table_pagination();
    }


    function ajax_files_shortcode_list_pagination() {
        WPC()->files()->ajax_files_shortcode_list_pagination();
    }


    function ajax_files_shortcode_blog_pagination() {
        WPC()->files()->ajax_files_shortcode_blog_pagination();
    }


    function ajax_files_shortcode_tree_pagination() {
        WPC()->files()->ajax_files_shortcode_tree_pagination();
    }


    function ajax_files_shortcode_tree_get_files() {
        WPC()->files()->ajax_files_shortcode_tree_get_files();
    }




    function ajax_pagel_shortcode_tree_pagination() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );
        $data['ajax_pagination'] = true;

        //defined client_id
        if( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) {
            $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
        } elseif( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
            $user_id = $_POST['client_id'];
        } else {
            $user_id = get_current_user_id();
        }

        $data['sort_type'] = ( isset( $_POST['order_by'] ) && !empty( $_POST['order_by'] ) ) ? $_POST['order_by'] : $data['sort_type'];
        $data['sort'] = ( isset( $_POST['order'] ) && !empty( $_POST['order'] ) ) ? $_POST['order'] : $data['sort'];

        $search = '';
        if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
            $search = " AND p.post_title LIKE '%s' ";
        }

        $portal_page_categories = array();
        if( isset( $data['categories'] ) && '' != $data['categories'] ) {

            if( isset( $_POST['order_by'] ) && 'date' == $_POST['order_by'] ) {
                $order_string = ' cat_id ';
            } else {
                $order_string = ' cat_name ';
            }

            if( isset( $_POST['order'] ) && 'desc' == $_POST['order'] ) {
                $order_string .= ' DESC ';
            } else {
                $order_string .= ' ASC ';
            }

            $result = $wpdb->get_results(
                "SELECT *
                FROM {$wpdb->prefix}wpc_client_portal_page_categories
                WHERE cat_id IN('" . implode( "','", $data['categories'] ) . "')
                ORDER BY $order_string",
            ARRAY_A );

            $portal_page_categories = array_merge( $portal_page_categories, $result );

            if( in_array( '0', $data['categories'] ) ) {
                $portal_page_categories[] = array( 'cat_id' => '0', 'cat_name' => __( 'No Category', WPC_CLIENT_TEXT_DOMAIN ) );
            }
        }


        //$mypages_id - - - array of pages which are available for client
        $mypages_id = array();

        //Portal pages in categories with clients access
        $client_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $user_id );

        $results = $wpdb->get_col(
            "SELECT p.ID
            FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
            WHERE {$data['show_current_page']}
                p.post_type = 'clientspage' AND
                p.post_status = 'publish' AND
                pm.meta_key = '_wpc_category_id' AND
                pm.meta_value IN('" . implode( "','", $client_portal_page_category_ids ) . "')"
        );

        if( isset( $results ) && 0 < count( $results ) ) {
            $mypages_id = array_merge( $mypages_id, $results );
        }

        //Portal pages with clients access
        $client_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $user_id );

        if( isset( $client_portal_page_ids ) && 0 < count( $client_portal_page_ids ) ) {
            $mypages_id = array_merge( $mypages_id, $client_portal_page_ids );
        }

        $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

        if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
            foreach( $client_groups_id as $group_id ) {

                //Portal pages in categories with group access
                $group_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $group_id );

                $results = $wpdb->get_col(
                    "SELECT p.ID
                    FROM $wpdb->posts p
                    INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
                    WHERE {$data['show_current_page']}
                        p.post_type = 'clientspage' AND
                        p.post_status = 'publish' AND
                        pm.meta_key = '_wpc_category_id' AND
                        pm.meta_value IN('" . implode( "','", $group_portal_page_category_ids ) . "')"
                );

                if ( 0 < count( $results ) ) {
                    $mypages_id = array_merge( $mypages_id, $results );
                }

                //Portal pages with group access
                $group_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $group_id );

                if( isset( $group_portal_page_ids ) && 0 < count( $group_portal_page_ids ) ) {
                    $mypages_id = array_merge( $mypages_id, $group_portal_page_ids );
                }

            }
        }
        $mypages_id = array_unique( $mypages_id );
        if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
            $mypages_id = $wpdb->get_col( $wpdb->prepare(
                "SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.ID IN( '" . implode( "','", $mypages_id ) . "' )
                        $search",
                '%' . $_POST['search']  . '%'
            ) );
        } else {
            $mypages_id = $wpdb->get_col(
                "SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.ID IN( '" . implode( "','", $mypages_id ) . "' )"
            );
        }

        $data['tree_content'] = '';

        foreach( $portal_page_categories as $category ) {

            $current_portal_pages = array();
            foreach( $mypages_id as $mypage_id ) {
                $page_category = get_post_meta( $mypage_id, '_wpc_category_id', true );

                $page_category = ( '' == $page_category ) ? '0' : $page_category;

                if( $category['cat_id'] == $page_category ) {
                    $current_portal_pages[] = $mypage_id;
                }
            }

            if ( $category['cat_id'] != '0' ) {
                if ( 0 < count( $current_portal_pages ) ) {
                    $data['tree_content'] .= WPC()->get_template( 'portal-pages/tree/category_row.php', '', array_merge( $data, array(
                        'category_id' => $category['cat_id'],
                        'category_icon' => WPC()->plugin_url . 'images/folder.png',
                        'category_name' => $category['cat_name']
                    ) ) );

                    $data['tree_content'] .= '<tr data-tt-id="page%page_id%" data-tt-parent-id="category' . $category['cat_id'] . '" class="wpc_treetable_page wpc_hidden_pages' . $category['cat_id'] . '" valign="top"><td></td></tr>';
                }
            } else {

                //display pages without categories

                if( isset( $data['sort_type'] ) && isset( $data['sort'] ) ) {
                    $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $data['sort_type'], $data['sort'] );
                } elseif( isset( $data['sort_type'] ) && !isset( $data['sort'] ) ) {
                    $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $data['sort_type'] );
                }

                foreach( $current_portal_pages as $page_id ) {
                    $mypage = get_post( $page_id, 'ARRAY_A' );
                    if( 'publish' != $mypage['post_status'] )
                        continue;

                    $edit_link = '';
                    if ( 1 == get_post_meta( $mypage['ID'], 'allow_edit_clientpage', true ) ) {
                        //make link
                        if ( WPC()->permalinks ) {
                            $edit_link = '<a href="' . WPC()->get_slug( 'edit_portal_page_id' ) . $mypage['post_name'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                        } else {
                            $edit_link = '<a href="' . add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $mypage['post_name'] ), WPC()->get_slug( 'edit_portal_page_id', false ) ) . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                        }
                    }

                    //make link
                    $featured_image = get_the_post_thumbnail( $page_id, 'post-thumbnail', array( 'class' => 'wpc_pp_image' ) );

                    $data['tree_content'] .=  WPC()->get_template( 'portal-pages/tree/item_row.php', '', array_merge( $data, array(
                        'parent_cat_id'=> !empty( $category['cat_id'] ) ? $category['cat_id'] : '',
                        'page_id'     => $category['cat_id'],
                        'page_link'   => get_permalink( $page_id ),
                        'page_title'  => nl2br( $mypage['post_title'] ),
                        'show_edit_link'=> ( $edit_link != '' ) ? '' : 'display:none;',
                        'edit_link'   => $edit_link,
                        'featured_image'=> $featured_image,
                        'page_date'   => WPC()->date_format( strtotime( $mypage['post_date'] ), 'date' ),
                        'page_time'   => WPC()->date_format( strtotime( $mypage['post_date'] ), 'time' )
                    ) ) );
                }
            }

            //sorting
            /*if( isset( $data['sort_type'] ) && isset( $data['sort'] ) ) {
                $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $data['sort_type'], $data['sort'] );
            } elseif( isset( $data['sort_type'] ) && !isset( $data['sort'] ) ) {
                $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $data['sort_type'] );
            }

            if( 0 < count( $current_portal_pages ) ) {
                foreach( $current_portal_pages as $page_id ) {
                    $mypage = get_post( $page_id, 'ARRAY_A' );
                    if( 'publish' != $mypage['post_status'] )
                        continue;

                    $edit_link = '';
                    if ( 1 == get_post_meta( $mypage['ID'], 'allow_edit_clientpage', true ) ) {
                        //make link
                        if ( WPC()->permalinks ) {
                            $edit_link = '<a href="' . WPC()->get_slug( 'edit_portal_page_id' ) . $mypage['post_name'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                        } else {
                            $edit_link = '<a href="' . add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $mypage['post_name'] ), WPC()->get_slug( 'edit_portal_page_id', false ) ) . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                        }
                    }

                    //make link
                    $featured_image = get_the_post_thumbnail( $page_id, 'post-thumbnail', array( 'class' => 'wpc_pp_image' ) );

                    $data['tree_content'] .=  WPC()->get_template( 'portal-pages/tree/item_row.php', '', array_merge( $data, array(
                        'parent_cat_id'=> !empty( $category['cat_id'] ) ? $category['cat_id'] : '',
                        'page_id'     => $category['cat_id'],
                        'page_link'   => get_permalink( $page_id ),
                        'page_title'  => nl2br( $mypage['post_title'] ),
                        'show_edit_link'=> ( $edit_link != '' ) ? '' : 'display:none;',
                        'edit_link'   => $edit_link,
                        'featured_image'=> $featured_image,
                        'page_date'   => WPC()->date_format( strtotime( $mypage['post_date'] ), 'date' ),
                        'page_time'   => WPC()->date_format( strtotime( $mypage['post_date'] ), 'time' )
                    ) ) );
                }
            }*/
        }

        $content = WPC()->get_template('portal-pages/tree/items.php', '', $data );

        echo json_encode( array( 'status' => true, 'html' => $content ) );
        exit;
    }


    function ajax_pagel_shortcode_tree_get_pages() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) )
            @set_time_limit(0);

        if ( ! empty( $_POST['category_id'] ) ) {

            //defined client_id
            if( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) {
                $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
            } elseif( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
                $user_id = $_POST['client_id'];
            } else {
                $user_id = get_current_user_id();
            }

            $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

            $sort_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
            if ( ! empty( $_POST['order_by'] ) ) {
                switch( $_POST['order_by'] ) {
                    case 'name':
                        $sort_by = 'f.title';
                        break;
                    case 'time':
                        $sort_by = 'f.time';
                        break;
                    case 'download_time':
                        $sort_by = 'f.last_download';
                        break;
                    case 'size':
                        $sort_by = 'f.size';
                        break;
                    case 'author':
                        $sort_by = 'u.user_login';
                        break;
                    default:
                        $sort_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
                        break;
                }
            }

            $sort = ( isset( $_POST['order'] ) && ! empty( $_POST['order'] ) ) ? $_POST['order'] : $data['sort'];
            //$order_string = "$sort_by $sort";

            $search = '';
            if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                $search = " AND p.post_title LIKE '%s' ";
            }

/*            $portal_page_categories = array();
            if( isset( $data['categories'] ) && '' != $data['categories'] ) {

                if( isset( $_POST['order_by'] ) && 'date' == $_POST['order_by'] ) {
                    $order_string = ' cat_id ';
                } else {
                    $order_string = ' cat_name ';
                }

                if( isset( $_POST['order'] ) && 'desc' == $_POST['order'] ) {
                    $order_string .= ' DESC ';
                } else {
                    $order_string .= ' ASC ';
                }

                $result = $wpdb->get_results(
                    "SELECT *
                FROM {$wpdb->prefix}wpc_client_portal_page_categories
                WHERE cat_id IN('" . implode( "','", $data['categories'] ) . "')
                ORDER BY $order_string",
                    ARRAY_A );

                $portal_page_categories = array_merge( $portal_page_categories, $result );

                if( in_array( '0', $data['categories'] ) ) {
                    $portal_page_categories[] = array( 'cat_id' => '0', 'cat_name' => __( 'No Category', WPC_CLIENT_TEXT_DOMAIN ) );
                }
            }*/


            //$mypages_id - - - array of pages which are available for client
            $mypages_id = array();

            //Portal pages in categories with clients access
            $client_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $user_id );

            $results = $wpdb->get_col(
                "SELECT p.ID
            FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
            WHERE {$data['show_current_page']}
                p.post_type = 'clientspage' AND
                p.post_status = 'publish' AND
                pm.meta_key = '_wpc_category_id' AND
                pm.meta_value IN('" . implode( "','", $client_portal_page_category_ids ) . "')"
            );

            if( isset( $results ) && 0 < count( $results ) ) {
                $mypages_id = array_merge( $mypages_id, $results );
            }

            //Portal pages with clients access
            $client_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $user_id );

            if( isset( $client_portal_page_ids ) && 0 < count( $client_portal_page_ids ) ) {
                $mypages_id = array_merge( $mypages_id, $client_portal_page_ids );
            }

            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach( $client_groups_id as $group_id ) {

                    //Portal pages in categories with group access
                    $group_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $group_id );

                    $results = $wpdb->get_col(
                        "SELECT p.ID
                    FROM $wpdb->posts p
                    INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
                    WHERE {$data['show_current_page']}
                        p.post_type = 'clientspage' AND
                        p.post_status = 'publish' AND
                        pm.meta_key = '_wpc_category_id' AND
                        pm.meta_value IN('" . implode( "','", $group_portal_page_category_ids ) . "')"
                    );

                    if ( 0 < count( $results ) ) {
                        $mypages_id = array_merge( $mypages_id, $results );
                    }

                    //Portal pages with group access
                    $group_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $group_id );

                    if( isset( $group_portal_page_ids ) && 0 < count( $group_portal_page_ids ) ) {
                        $mypages_id = array_merge( $mypages_id, $group_portal_page_ids );
                    }

                }
            }
            $mypages_id = array_unique( $mypages_id );
            if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                $mypages_id = $wpdb->get_col( $wpdb->prepare(
                    "SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.ID IN( '" . implode( "','", $mypages_id ) . "' )
                        $search",
                    '%' . $_POST['search']  . '%'
                ) );
            } else {
                $mypages_id = $wpdb->get_col(
                    "SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.ID IN( '" . implode( "','", $mypages_id ) . "' )"
                );
            }


            $current_portal_pages = array();
            foreach( $mypages_id as $mypage_id ) {
                $page_category = get_post_meta( $mypage_id, '_wpc_category_id', true );

                $page_category = ( '' == $page_category ) ? '0' : $page_category;

                if( $_POST['category_id'] == $page_category ) {
                    $current_portal_pages[] = $mypage_id;
                }
            }

            //display pages without categories
            if ( isset( $_POST['order_by'] ) && isset( $_POST['order'] ) ) {
                $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $_POST['order_by'], $_POST['order'] );
            } elseif ( isset( $data['sort_type'] ) && isset( $data['sort'] ) ) {
                $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $data['sort_type'], $data['sort'] );
            } elseif ( isset( $data['sort_type'] ) && !isset( $data['sort'] ) ) {
                $current_portal_pages = WPC()->pages()->sort_portalpages_for_client( $current_portal_pages, $data['sort_type'] );
            }

            $data['tree_content'] = '';
            
            foreach( $current_portal_pages as $page_id ) {
                $mypage = get_post( $page_id, 'ARRAY_A' );
                if( 'publish' != $mypage['post_status'] )
                    continue;

                $edit_link = '';
                if ( 1 == get_post_meta( $mypage['ID'], 'allow_edit_clientpage', true ) ) {
                    //make link
                    if ( WPC()->permalinks ) {
                        $edit_link = '<a href="' . WPC()->get_slug( 'edit_portal_page_id' ) . $mypage['post_name'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                    } else {
                        $edit_link = '<a href="' . add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $mypage['post_name'] ), WPC()->get_slug( 'edit_portal_page_id', false ) ) . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                    }
                }

                //make link
                $featured_image = get_the_post_thumbnail( $page_id, 'post-thumbnail', array( 'class' => 'wpc_pp_image' ) );

                $data['tree_content'] .=  WPC()->get_template( 'portal-pages/tree/item_row.php', '', array_merge( $data, array(
                    'parent_cat_id'     => $_POST['category_id'],
                    'page_id'           => $page_id,
                    'page_link'         => get_permalink( $page_id ),
                    'page_title'        => nl2br( $mypage['post_title'] ),
                    'show_edit_link'    => ( $edit_link != '' ) ? '' : 'display:none;',
                    'edit_link'         => $edit_link,
                    'featured_image'    => $featured_image,
                    'page_date'         => WPC()->date_format( strtotime( $mypage['post_date'] ), 'date' ),
                    'page_time'         => WPC()->date_format( strtotime( $mypage['post_date'] ), 'time' )
                ) ) );
            }

            wp_die( json_encode( array(
                'status'    => true,
                'html'      => $data['tree_content']
            ) ) );
        } else {
            wp_die( json_encode( array(
                'status' => false,
                'message' => __( 'Invalid Data', WPC_CLIENT_TEXT_DOMAIN )
            ) ) );
        }
    }


    function ajax_pagel_shortcode_list_pagination() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );
        $data['ajax_pagination'] = true;

        //defined client_id
        if( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) {
            $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
        } elseif( isset( $_POST['client_id'] ) && !empty( $_POST['client_id'] ) && check_ajax_referer( $_POST['client_id'] . 'client_security', 'security' ) ) {
            $user_id = $_POST['client_id'];
        } else {
            $user_id = get_current_user_id();
        }

        $search = '';
        if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
            $search = " AND p.post_title LIKE '%s' ";
        }

        $portal_page_categories = '';
        if( isset( $data['categories'] ) && '' != $data['categories'] ) {
            if( !in_array( 'all', $data['categories'] ) ) {
                if( in_array( '0', $data['categories'] ) ) {
                    $portal_page_categories = " AND ( pm2.meta_value IN('" . implode( "','", $data['categories'] ) . "') OR pm2.meta_value IS NULL )";
                } else {
                    $portal_page_categories = " AND pm2.meta_value IN('" . implode( "','", $data['categories'] ) . "')";
                }
            }
        }

        //$mypages_id - - - array of pages which are available for client
        $mypages_id = array();

        //Portal pages in categories with clients access
        $client_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $user_id );

        $results = $wpdb->get_col(
            "SELECT p.ID
                FROM $wpdb->posts p
                    INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
                WHERE {$data['show_current_page']}
                    p.post_type = 'clientspage' AND
                    p.post_status = 'publish' AND
                    pm.meta_key = '_wpc_category_id' AND
                    pm.meta_value IN('" . implode( "','", $client_portal_page_category_ids ) . "')"
        );

        if( isset( $results ) && 0 < count( $results ) ) {
            $mypages_id = array_merge( $mypages_id, $results );
        }

        //Portal pages with clients access
        $client_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $user_id );

        if( isset( $client_portal_page_ids ) && 0 < count( $client_portal_page_ids ) ) {
            $mypages_id = array_merge( $mypages_id, $client_portal_page_ids );
        }

        $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

        if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
            foreach( $client_groups_id as $group_id ) {

                //Portal pages in categories with group access
                $group_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $group_id );

                $results = $wpdb->get_col(
                    "SELECT p.ID
                        FROM $wpdb->posts p
                            INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
                        WHERE {$data['show_current_page']}
                            p.post_type = 'clientspage' AND
                            p.post_status = 'publish' AND
                            pm.meta_key = '_wpc_category_id' AND
                            pm.meta_value IN('" . implode( "','", $group_portal_page_category_ids ) . "')"
                );

                if ( 0 < count( $results ) ) {
                    $mypages_id = array_merge( $mypages_id, $results );
                }

                //Portal pages with group access
                $group_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $group_id );

                if( isset( $group_portal_page_ids ) && 0 < count( $group_portal_page_ids ) ) {
                    $mypages_id = array_merge( $mypages_id, $group_portal_page_ids );
                }

            }
        }
        $mypages_id = array_unique( $mypages_id );
        if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
            $mypages_id = $wpdb->get_col( $wpdb->prepare(
                "SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.ID IN( '" . implode( "','", $mypages_id ) . "' )
                        $search",
                '%' . $_POST['search']  . '%'
            ) );
        } else {
            $mypages_id = $wpdb->get_col(
                "SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.ID IN( '" . implode( "','", $mypages_id ) . "' )"
            );
        }


        $order_string = '';
        if( $data['show_categories_title'] ) {
            $cat_sort = 'ASC';
            if( isset( $_POST['sorting'] ) && !empty( $_POST['sorting'] ) ) {
                $sorting_array = explode( '_', $_POST['sorting'] );
                if( isset( $sorting_array[0] ) && 'category' == $sorting_array[0] ) {
                    $cat_sort = strtoupper( $sorting_array[1] );
                }
            }

            $order_string .= "ppc.cat_name $cat_sort, ppc.cat_id ASC,";
        }

        if( isset( $_POST['sorting'] ) && !empty( $_POST['sorting'] ) ) {
            $sorting_array = explode( '_', $_POST['sorting'] );

            if( isset( $sorting_array[0] ) && 'category' == $sorting_array[0] ) {
                $order_string .= 'pm1.meta_value = 0 OR ISNULL(pm1.meta_value), pm1.meta_value ASC';
            } else {
                if( isset( $sorting_array[0] ) && !empty( $sorting_array[0] ) ) {
                    switch( $sorting_array[0] ) {
                        case 'orderid':
                            $sort_by = 'pm1.meta_value = 0 OR ISNULL(pm1.meta_value), pm1.meta_value';
                            break;
                        case 'title':
                            $sort_by = 'p.post_title';
                            break;
                        case 'date':
                            $sort_by = 'p.post_date';
                            break;
                    }
                }

                $sort_dir = strtoupper( $sorting_array[1] );
                $order_string .= "$sort_by $sort_dir";
            }
        } else {
            if( $data['sort_type'] == 'date' ) {
                $sort_by = 'p.post_date';
            } elseif( $data['sort_type'] == 'title' ) {
                $sort_by = 'p.post_title';
            } else {
                $sort_by = 'CAST( pm1.meta_value AS unsigned ) = 0 OR ISNULL(pm1.meta_value), CAST( pm1.meta_value AS unsigned )';
            }

            $sort = isset( $data['dir'] ) ? 'ASC' : 'DESC';

            $order_string .= " $sort_by $sort ";
        }

        $count_pages = $wpdb->get_var(
            "SELECT COUNT( p.ID )
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_order_id' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON( p.ID = pm2.post_id AND pm2.meta_key = '_wpc_category_id' )
                LEFT JOIN {$wpdb->prefix}wpc_client_portal_page_categories ppc ON( pm2.meta_value = ppc.cat_id )
                WHERE p.ID IN('" . implode( "','", $mypages_id ) . "') AND
                    p.post_status = 'publish'
                    $portal_page_categories"
        );


        $per_page = ( $data['show_pagination_by'] ) ? (int)$data['show_pagination_by'] : $count_pages;
        $data['count_pages'] = 1;
        $data['pagination'] = false;
        if( !( isset( $_POST['sort_button'] ) && !empty( $_POST['sort_button'] ) ) ) {
            if( $per_page < $count_pages ) {
                $data['count_pages'] = ceil( $count_pages / $per_page );

                $data['pagination'] = true;
                if( $_POST['current_page'] == $data['count_pages'] - 1 ) {
                    $data['pagination'] = false;
                }
            }
        }
        $start_count = $_POST['current_page'] * $per_page;

        // Show More - only for first time to get double count to replace old for new list
        if( $_POST['current_page'] == 1 ) {
            $start_count = 0;
            $per_page += $per_page;
        }

        $pages = $wpdb->get_results(
            "SELECT p.ID,
                    p.post_title,
                    p.post_name,
                    p.post_date,
                    pm1.meta_value AS order_id,
                    IFNULL( pm2.meta_value, 'none' ) AS category_id,
                    ppc.cat_name AS category_name
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_order_id' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON( p.ID = pm2.post_id AND pm2.meta_key = '_wpc_category_id' )
                LEFT JOIN {$wpdb->prefix}wpc_client_portal_page_categories ppc ON( pm2.meta_value = ppc.cat_id )
                WHERE p.ID IN('" . implode( "','", $mypages_id ) . "') AND
                    p.post_status = 'publish'
                    $portal_page_categories
                ORDER BY $order_string
                LIMIT $start_count, $per_page",
            ARRAY_A );


        if( isset( $pages ) && is_array( $pages ) && count( $pages ) ) {
            foreach( $pages as $key=>$page ) {
                $portal_page = array();

                $portal_page['edit_link'] = '';

                if( 1 == get_post_meta( $page['ID'], 'allow_edit_clientpage', true ) ) {
                    //make link
                    if( WPC()->permalinks ) {
                        $portal_page['edit_link'] = WPC()->get_slug( 'edit_portal_page_id' ) . $page['post_name'];
                    } else {
                        $portal_page['edit_link'] = add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $page['post_name'] ), WPC()->get_slug( 'edit_portal_page_id', false ) );
                    }
                }

                $portal_page['url']             = get_permalink( $page['ID'] );
                $portal_page['id']              = $page['ID'];
                $portal_page['title']           = nl2br( $page['post_title'] );
                $portal_page['creation_date']   = strtotime( $page['post_date'] );
                $portal_page['icon']            = get_the_post_thumbnail( $page['ID'], 'post-thumbnail', array( 'class' => 'wpc_pp_image' ) );

                $portal_page['date']            = WPC()->date_format( strtotime( $page['post_date'] ), 'date' );
                $portal_page['time']            = WPC()->date_format( strtotime( $page['post_date'] ), 'time' );
                $portal_page['category_name']   = ( isset( $page['category_name'] ) && !empty( $page['category_name'] ) ) ? $page['category_name'] : __( 'No Category', WPC_CLIENT_TEXT_DOMAIN );

                /*                    if( !( $pages[0] === $pages[$key] && $_POST['current_page'] > 1 ) ) {*/
                if( !( $data['show_categories_title'] && (
                        ( isset( $pages[$key - 1]['category_id'] ) && $page['category_id'] != $pages[$key - 1]['category_id'] ) ||
                        ( !isset( $pages[$key - 1]['category_id'] ) && $page['category_id'] != $_POST['last_category_id'] ) ) ) ) {
                    unset( $portal_page['category_name'] );
                }
                /*                    }*/
                if( $_POST['current_page'] == '0' && $pages[0] === $pages[$key] ) {
                    $portal_page['category_name'] = ( isset( $page['category_name'] ) && !empty( $page['category_name'] ) ) ? $page['category_name'] : __( 'No Category', WPC_CLIENT_TEXT_DOMAIN );
                }

                $data['pages'][] = $portal_page;
            }

            $last_page = end( $pages );
            $data['last_category_id'] = $last_page['category_id'];
        } else {
            $data['show_search'] = false;
        }


        $content = WPC()->get_template( 'portal-pages/list/items.php', '', $data );
        $content = do_shortcode( $content );

        echo json_encode( array( 'status' => true, 'html' => $content, 'pagination' => $data['pagination'], 'last_category_id' => $data['last_category_id'] ) );
        exit;
    }


    /*
        * Ajax function for checked content of page is consist shortcode
        *
        * @return array json answer to js
        */
    function ajax_check_page_shortcode() {

        if ( !isset( $_REQUEST['page_id'] ) || !$_REQUEST['page_id'] ) {
            echo json_encode( array( 'id' => '', 'warning' => true ) );
            exit;
        }

        if ( !isset( $_REQUEST['shortcode_type'] ) || !$_REQUEST['shortcode_type'] ) {
            echo json_encode( array( 'warning' => false ) );
            exit;
        }

        WPC()->set_shortcode_data();

        $shortcode_type = $_REQUEST['shortcode_type'];
        $page_id = $_REQUEST['page_id'];

        $page = get_post( $page_id );

        $pattern = "\[(\[?)(" . implode( '|', array_keys( WPC()->shortcode_data ) ) . ")(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)";

        if ( preg_match_all( '/'. $pattern .'/s', $page->post_content, $matches ) && array_key_exists( 2, $matches ) &&
            in_array( substr( $shortcode_type, 1, -1 ), $matches[2] ) ) {
            // shortcode is being used
            echo json_encode( array( 'id' => $page_id, 'warning' => false ) );
        } else {
            echo json_encode( array( 'nes_shortcode' => $shortcode_type, 'id' => $page_id, 'warning' => true ) );
        }
        exit;

    }


    /*
        * Ajax function for update order of "Portal Pages" page
        */
    function ajax_portal_pages_update_order() {
        if ( isset( $_POST['post_id'] ) ) {
            $order =  ( isset( $_POST['clientpage_order'] ) && '' != (int) $_POST['clientpage_order']  && 0 < (int) $_POST['clientpage_order'] ) ? (int) $_POST['clientpage_order'] : 0;
            update_post_meta( $_POST['post_id'], '_wpc_order_id', $order );
            $value = get_post_meta(  $_POST['post_id'], '_wpc_order_id', true );
            echo json_encode( array( 'my_value' => $value ) );
            exit;
        }
    }


    /*
        * Ajax function for update order of "File Sharing" page
        */
    function ajax_files_update_order() {
        global $wpdb;
        if ( isset( $_POST['file_id'] ) ) {
            $order =  ( isset( $_POST['file_order'] ) && '' != (int) $_POST['file_order']  && 0 < (int) $_POST['file_order'] ) ? $_POST['file_order'] : 0;
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_files SET order_id = %d WHERE id = %d", $order, $_POST['file_id'] ) );
            $value = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}wpc_client_files WHERE id = %d ", $_POST['file_id'] ) );
            echo json_encode( array( 'my_value' => $value ) );
            exit;
        }
    }


    /**
     * AJAX - Change order for files categories
     **/
    function ajax_change_cat_order() {
        global $wpdb;

        //database change order
        if( isset( $_POST['new_order'] ) && isset( $_POST['current_item'] ) ) {

            $_POST['new_order'] = json_decode( base64_decode( $_POST['new_order'] ) );
            $_POST['current_item'] = json_decode( base64_decode( $_POST['current_item'] ) );
            $_POST['current_item'][0] = (array)$_POST['current_item'][0];

            $old_orders = WPC()->files()->get_categories_order();

            if( is_array( $_POST['new_order'] ) && 0 < count( $_POST['new_order'] ) ) {
                $i = array();
                foreach( $_POST['new_order'] as $new_order ) {
                    $new_order = (array)$new_order;

                    if( isset( $new_order['item_id'] ) && !empty( $new_order['item_id'] ) ) {

                        if( $new_order['parent_id'] == '' )
                            $new_order['parent_id'] = 0;

                        if( $_POST['current_item'][0]['parent_id'] == '' )
                            $_POST['current_item'][0]['parent_id'] = 0;

                        if( !in_array( $new_order['parent_id'], array( $_POST['current_item'][0]['parent_id'], $old_orders[$_POST['current_item'][0]['item_id']] ) ) /*$_REQUEST['current_item'][0]['parent_id'] != $new_order['parent_id'] && $old_orders[$_REQUEST['current_item'][0]['item_id']] != $new_order['parent_id']*/ ) {
                            continue;
                        }

                        if( $old_orders[$new_order['item_id']] != $new_order['parent_id'] ) {

                            $old_path = WPC()->files()->get_category_path( $new_order['item_id'] );

                            $parent_folder_path = WPC()->files()->get_category_path( $new_order['parent_id'] );
                            $parent_folder_path = ( $new_order['parent_id'] == '0' ) ? $parent_folder_path : $parent_folder_path . DIRECTORY_SEPARATOR;

                            $folder_name = $wpdb->get_var( $wpdb->prepare(
                                "SELECT folder_name
                                    FROM {$wpdb->prefix}wpc_client_file_categories
                                    WHERE cat_id=%d",
                                $new_order['item_id']
                            ) );

                            if( is_dir( $parent_folder_path . $folder_name ) ) {
                                die( __( 'This folder is already exist in this level', WPC_CLIENT_TEXT_DOMAIN ) );
                            }

                            $i[$new_order['parent_id']] = ( isset( $i[$new_order['parent_id']] ) && !empty( $i[$new_order['parent_id']] ) ) ? $i[$new_order['parent_id']] : 0;
                            $i[$new_order['parent_id']]++;

                            $wpdb->update(
                                "{$wpdb->prefix}wpc_client_file_categories",
                                array(
                                    'cat_order' => $i[$new_order['parent_id']],
                                    'parent_id' => $new_order['parent_id']
                                ),
                                array( 'cat_id' => $new_order['item_id'] )
                            );

                            $new_path = WPC()->files()->get_category_path( $new_order['item_id'] );

                            if( is_dir( $old_path ) ) {
                                rename( $old_path, $new_path );
                            }

                        } else {
                            $i[$new_order['parent_id']] = ( isset( $i[$new_order['parent_id']] ) && !empty( $i[$new_order['parent_id']] ) ) ? $i[$new_order['parent_id']] : 0;
                            $i[$new_order['parent_id']]++;

                            $wpdb->update(
                                "{$wpdb->prefix}wpc_client_file_categories",
                                array(
                                    'cat_order' => $i[$new_order['parent_id']],
                                    'parent_id' => $new_order['parent_id']
                                ),
                                array( 'cat_id' => $new_order['item_id'] )
                            );
                        }
                    }
                }
            }
        }
        die( 'ok' );
    }


    /**
     * AJAX - Change order for custom fields
     **/
    function ajax_change_custom_field_order() {

        if ( isset( $_REQUEST['new_order'] ) ) {
            $cf_names =  explode( ',', str_replace( 'field_', '', $_REQUEST['new_order'] ) );

            if ( is_array( $cf_names ) && 0 < count( $cf_names ) ) {
                $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

                $new_wpc_custom_fields = array();
                foreach( $cf_names  as $cf_name ) {
                    $new_wpc_custom_fields[$cf_name] = $wpc_custom_fields[$cf_name];
                }

                WPC()->settings()->update( $new_wpc_custom_fields, 'custom_fields' );
            }
            die( 'ok' );
        }
    }


    /**
     * AJAX - Get all Client Circles
     **/
    function ajax_get_all_groups() {

        $groups = WPC()->groups()->get_groups();

        if ( is_array( $groups ) && 0 < count( $groups ) ) {

            $i = 0;
            $n = ceil( count( $groups ) / 5 );

            $html = '';
            $html .= '<ul class="clients_list">';



            foreach ( $groups as $group ) {
                if ( $i%$n == 0 && 0 != $i )
                    $html .= '</ul><ul class="clients_list">';

                $html .= '<li><label>';
                $html .= '<input type="checkbox" name="groups_id[]" value="' . $group['group_id'] . '" /> ';
                $html .= $group['group_id'] . ' - ' . $group['group_name'];
                $html .= '</label></li>';

                $i++;
            }

            $html .= '</ul>';
        } else {
            $html = 'false';
        }

        die( $html );

    }


    /*
         *Get client login or  circle name by ajax request
         */
    function ajax_get_name() {
        if( isset( $_POST['type'] ) && isset( $_POST['id'] ) ) {
            switch( $_POST['type'] ) {
                case 'wpc_clients':
                    $userdata = get_userdata( $_POST['id'] );
                    echo json_encode( array( 'status' => true, 'name' => $userdata->get('user_login') ) );
                    break;
                case 'wpc_circles':
                    $res = WPC()->groups()->get_group( $_POST['id'] );
                    echo json_encode( array( 'status' => true, 'name' => $res['group_name'] ) );
                    break;
                default:
                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong type', WPC_CLIENT_TEXT_DOMAIN ) ) );
                    break;
            }
        }
        exit;
    }


    /**
     * AJAX upload files
     */
    function ajax_upload_files() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        //get client id
        if ( isset( $_POST['client_id'] ) && 0 < $_POST['client_id'] ) {
            //checking access
            if ( isset( $_REQUEST['verify_nonce'] ) && isset( $_REQUEST['include_ext'] ) && isset( $_REQUEST['exclude_ext'] ) ) {
                if ( ! wp_verify_nonce( $_REQUEST['verify_nonce'], $_REQUEST['include_ext'] . $_REQUEST['exclude_ext'] . $_POST['client_id'] ) ) {
                    die( "You haven't access!" );
                }
            }else{
                die( "You haven't access!" );
            }

            $user_id = $_POST['client_id'];
        } else {
            die('no user ID!');
        }

        $msg = '';

        //Check file size
        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        if ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) {
            if ( isset( $_FILES['Filedata']['size'] ) && '' != $_FILES['Filedata']['size'] ) {
                $size = round( $_FILES['Filedata']['size'] / 1024 );
                if ( $size > $wpc_file_sharing['file_size_limit'] ) {
                    $msg = __( 'The file size more than allowed!', WPC_CLIENT_TEXT_DOMAIN );
                }
            }
        }


        /*our_hook_
                hook_name: wp_client_before_client_uploaded_file
                hook_title: Client Uploads File
                hook_description: Hook runs before client uploads file.
                hook_type: filter
                hook_in: wp-client
                hook_location class.ajax.php
                hook_param: string $error_message, string $filepath
                hook_since: 3.8.0
            */
        $msg = apply_filters( 'wp_client_before_client_uploaded_file', $msg, $_FILES['Filedata']['tmp_name'] );

        if ( empty( $msg ) ) {

            $include_filetypes = ( isset( $_REQUEST['include_ext'] ) && '' != $_REQUEST['include_ext'] ) ? explode( ',', $_REQUEST['include_ext'] ) : array();
            $exclude_filetypes = ( isset( $_REQUEST['exclude_ext'] ) && '' != $_REQUEST['exclude_ext'] ) ? explode( ',', $_REQUEST['exclude_ext'] ) : array();

            foreach( $include_filetypes as $key=>$include_filetype ) {
                $include_filetypes[$key] = trim( $include_filetype );
            }

            foreach( $exclude_filetypes as $key=>$exclude_filetype ) {
                $exclude_filetypes[$key] = trim( $exclude_filetype );
            }

            $ext = explode( '.', $_FILES['Filedata']['name'] );
            $ext = $ext[ count( $ext ) - 1 ];

            if( ( 0 == count( $include_filetypes ) && 0 < count( $exclude_filetypes ) && !in_array( $ext, $exclude_filetypes ) ) ||
                ( 0 < count( $include_filetypes ) && 0 == count( $exclude_filetypes ) && in_array( $ext, $include_filetypes ) ) ||
                ( 0 < count( $include_filetypes ) && 0 < count( $exclude_filetypes ) && in_array( $ext, $include_filetypes ) && !in_array( $ext, $exclude_filetypes ) ) ||
                ( 0 == count( $include_filetypes ) && 0 == count( $exclude_filetypes ) ) ) {

                if ( isset( $_POST['file_cat_id'] ) && !empty($_POST['file_cat_id'] ) ) {
                    if ( is_numeric( $_POST['file_cat_id'] ) ) {
                        //get category by ID
                        $category_data = $wpdb->get_row( $wpdb->prepare(
                            "SELECT cat_id, folder_name
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_id = %d",
                            $_POST['file_cat_id']
                        ), ARRAY_A );

                        $cat_id = $category_data['cat_id'];
                        $folder_name = $category_data['folder_name'];

                    } else {
                        //get categoty by name
                        $category_data = $wpdb->get_row( $wpdb->prepare(
                            "SELECT cat_id, folder_name
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_name = _utf8 '%s' COLLATE utf8_bin",
                            $_POST['file_cat_id']
                        ), ARRAY_A );

                        $cat_id = $category_data['cat_id'];
                        $folder_name = $category_data['folder_name'];
                    }

                    //if wrong category - get default category
                    if ( !$cat_id ) {
                        $cat_id = $wpdb->get_var(
                            "SELECT cat_id
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                        );

                        if( !$cat_id ) {
                            //get new order for category
                            $category_order = $wpdb->get_var(
                                "SELECT COUNT(cat_id)
                                    FROM {$wpdb->prefix}wpc_client_file_categories
                                    WHERE parent_id='0'"
                            );

                            $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                            $wpdb->insert(
                                "{$wpdb->prefix}wpc_client_file_categories",
                                array(
                                    'cat_name'      => 'General',
                                    'folder_name'   => 'General',
                                    'parent_id'     => '0',
                                    'cat_order'     => $category_order + 1
                                )
                            );

                            $cat_id = $wpdb->insert_id;
                        }

                        $folder_name = 'General';
                    }
                } else {
                    $cat_id = $wpdb->get_var(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                    );

                    if( !$cat_id ) {
                        //get new order for category
                        $category_order = $wpdb->get_var(
                            "SELECT COUNT(cat_id)
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE parent_id='0'"
                        );

                        $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_file_categories",
                            array(
                                'cat_name'      => 'General',
                                'folder_name'   => 'General',
                                'parent_id'     => '0',
                                'cat_order'     => $category_order + 1
                            )
                        );

                        $cat_id = $wpdb->insert_id;
                    }

                    $folder_name = 'General';
                }

                //create folders for file destination if it was not created
                WPC()->files()->create_file_category_folder( $cat_id, trim( $folder_name ) );

                //Upload file
                $orig_name      = $_FILES['Filedata']['name'];
                $new_name       = basename( rand( 0000,9999 ) . $orig_name );

                //todo: add description (old:wp_client_protects_file)
                $new_name = apply_filters( 'wp_client_file_upload_new_name', $new_name );

                $args = array(
                    'cat_id'    => $cat_id,
                    'filename'  => $new_name
                );

                $filepath    = WPC()->files()->get_file_path( $args );

                if( move_uploaded_file( $_FILES['Filedata']['tmp_name'], $filepath ) ) {

                    if( in_array( $ext, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
                        WPC()->files()->create_image_thumbnail( $args );

                        /* if( !file_exists( $uploads['basedir'] . "/wpclient/_file_sharing/thumbnails_" . $new_name ) ) {
                                $image_thumbnail_path = $uploads['basedir'] . "/wpclient/_file_sharing/thumbnails_" . $new_name;

                                $thumbnail_image = $uploads['basedir'] . "/wpclient/_file_sharing/" . $new_name;
                                $width = 100; //*** Fix Width & Heigh (Autu caculate) ***
                                $size = GetimageSize( $thumbnail_image );
                                $height = round( $width*$size[1] / $size[0] );

                                if( $height > 100 ) {
                                    $height = 100;
                                    $width = round( $height*$size[0] / $size[1] );
                                }
                                if (extension_loaded('gd') && function_exists('gd_info')) {
                                    switch ($ext) {
                                        case 'gif':
                                            $images_orig = imageCreateFromGif($thumbnail_image);
                                            break;
                                        case 'jpeg':
                                        case 'jpg':
                                            $images_orig = imageCreateFromJpeg($thumbnail_image);
                                            break;
                                        case 'png' :
                                            $images_orig = imageCreateFromPng($thumbnail_image);
                                            break;
                                    }

                                    $photoX = ImagesX( $images_orig );
                                    $photoY = ImagesY( $images_orig );
                                    $images_fin = ImageCreateTrueColor( $width, $height );

                                    ImageCopyResampled( $images_fin, $images_orig, 0, 0, 0, 0, $width+1, $height+1, $photoX, $photoY );

                                    ImageJPEG( $images_fin, $image_thumbnail_path);

                                    ImageDestroy( $images_orig );
                                    ImageDestroy( $images_fin );
                                }
                            }*/
                    }

                    $note = isset( $_POST['note'] ) ? $_POST['note'] : '';

                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_files SET
                            user_id             = %d,
                            page_id             = %d,
                            time                = %d,
                            size                = %d,
                            filename            = '%s',
                            name                = '%s',
                            title               = '%s',
                            description         = '%s',
                            cat_id              = '%d',
                            last_download       = '',
                            external            = '0'
                            ", $user_id, $_POST['post_id'], time(), $_FILES['Filedata']['size'], $new_name, $orig_name, $orig_name, $note, $cat_id ) );

                    $file_id = $wpdb->insert_id;
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', array( $user_id ) );

                    $arguments = $wpdb->get_results( "select * from {$wpdb->prefix}wpc_client_files WHERE id=" . $file_id, ARRAY_A );

                    /*our_hook_
                            hook_name: wp_client_client_uploaded_file
                            hook_title: Client Uploads File
                            hook_description: Hook runs when client uploads file.
                            hook_type: action
                            hook_in: wp-client
                            hook_location class.ajax.php
                            hook_param: array $file_data
                            hook_since: 3.3.0
                        */
                    do_action( 'wp_client_client_uploaded_file', $arguments );

                    $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

                    //email to admins
                    $args = array(
                        'role'      => 'wpc_admin',
                        'fields'    => array( 'user_email' )
                    );
                    $admin_emails = get_users( $args );
                    $emails_array = array();
                    if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                        foreach( $admin_emails as $admin_email ) {
                            $emails_array[] = $admin_email->user_email;
                        }
                    }

                    $emails_array[] = get_option( 'admin_email' );

                    $emails_array = array_unique( $emails_array );

                    $args = array(
                        'client_id' => $user_id,
                        'file_name' => $orig_name,
                        'file_category' => $file_category,
                        'file_download_link' => WPC()->files()->get_file_download_link($file_id, 'for_admin')
                    );
                    foreach( $emails_array as $to_email ) {
                        if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                            WPC()->mail( 'client_uploaded_file', $to_email, $args, 'client_uploaded_file', $filepath );
                        } else {
                            WPC()->mail( 'client_uploaded_file', $to_email, $args, 'client_uploaded_file' );
                        }
                    }

                    /* to delete
                        if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                            WPC()->mail( 'client_uploaded_file', get_option( 'admin_email' ), $args, 'client_uploaded_file', $target_path );
                        } else {
                            WPC()->mail( 'client_uploaded_file', get_option( 'admin_email' ), $args, 'client_uploaded_file' );
                        }
                          */

                    //send message to client manager
                    //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $user_id );
                    $manager_ids = WPC()->members()->get_client_managers( $user_id );

                    if( is_array( $manager_ids ) && count( $manager_ids ) ) {
                        foreach( $manager_ids as $manager_id ) {
                            if ( 0 < $manager_id ) {
                                $manager = get_userdata( $manager_id );
                                if ( $manager ) {
                                    $manager_email = $manager->get( 'user_email' );
                                    if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                                        //send email
                                        WPC()->mail( 'client_uploaded_file', $manager_email, $args, 'client_uploaded_file', $filepath );
                                    } else {
                                        //send email
                                        WPC()->mail( 'client_uploaded_file', $manager_email, $args, 'client_uploaded_file' );
                                    }
                                }

                            }
                        }
                    }

                    //echo '1' if all ok
                    exit( '1' );

                } else {
                    $msg = __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN );
                }
            } else {
                $msg = __( 'Such an extension is not supported!', WPC_CLIENT_TEXT_DOMAIN );
            }
        }

        status_header(301);
        exit( $msg );

    }


    /**
     * AJAX upload files
     */
    function ajax_plupload_upload_files() {
        global $wpdb;

        if( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }



        if( isset( $_REQUEST['client_id'] ) && 0 < $_REQUEST['client_id'] ) {
            //checking access
            if ( isset( $_REQUEST['verify_nonce'] ) && isset( $_REQUEST['include_ext'] ) && isset( $_REQUEST['exclude_ext'] ) ) {
                if ( ! wp_verify_nonce( $_REQUEST['verify_nonce'], $_REQUEST['include_ext'] . $_REQUEST['exclude_ext'] . $_REQUEST['client_id'] ) ) {
                    die( "You haven't access!" );
                }
            }else{
                die( "You haven't access!" );
            }

            $user_id = $_REQUEST['client_id'];
        } else {
            die('no user ID!');
        }

        // Get a file name
        if ( isset( $_REQUEST["name"] ) ) {
            $orig_name = $_REQUEST["name"];
        } elseif ( !empty( $_FILES ) ) {
            $orig_name = $_FILES["file"]["name"];
        } else {
            $orig_name = uniqid( "file_" );
        }

        $include_filetypes = ( isset( $_REQUEST['include_ext'] ) && '' != $_REQUEST['include_ext'] ) ? explode( ',', urldecode( $_REQUEST['include_ext'] ) ) : array();
        $exclude_filetypes = ( isset( $_REQUEST['exclude_ext'] ) && '' != $_REQUEST['exclude_ext'] ) ? explode( ',', urldecode( $_REQUEST['exclude_ext'] ) ) : array();

        foreach( $include_filetypes as $key=>$include_filetype ) {
            $include_filetypes[$key] = trim( $include_filetype );
        }

        foreach( $exclude_filetypes as $key=>$exclude_filetype ) {
            $exclude_filetypes[$key] = trim( $exclude_filetype );
        }

        $ext = explode( '.', $orig_name );
        $ext = strtolower( end( $ext ) );

        if( !( ( 0 == count( $include_filetypes ) && 0 < count( $exclude_filetypes ) && !in_array( $ext, $exclude_filetypes ) ) ||
            ( 0 < count( $include_filetypes ) && 0 == count( $exclude_filetypes ) && in_array( $ext, $include_filetypes ) ) ||
            ( 0 < count( $include_filetypes ) && 0 < count( $exclude_filetypes ) && in_array( $ext, $include_filetypes ) && !in_array( $ext, $exclude_filetypes ) ) ||
            ( 0 == count( $include_filetypes ) && 0 == count( $exclude_filetypes ) ) ) ) {

            die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "' . __( 'Wrong file extension!', WPC_CLIENT_TEXT_DOMAIN ) . '"}, "id" : "id"}' );
        }

        //Upload file
        $tempDir = WPC()->get_upload_dir( 'wpclient/_uberloader_temp/' );

        $filepath = $tempDir . basename( $orig_name );

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 100 * 3600; // Temp file age in seconds


        // Chunking might be enabled
        $chunk = isset( $_REQUEST["chunk"] ) ? intval( $_REQUEST["chunk"] ) : 0;
        $chunks = isset( $_REQUEST["chunks"] ) ? intval( $_REQUEST["chunks"] ) : 0;


        // Remove old temp files
        if ( $cleanupTargetDir ) {
            if( !is_dir( $tempDir ) || !$dir = opendir( $tempDir ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}' );
            }

            while( ( $file = readdir( $dir ) ) !== false ) {
                $tmpfilePath = $tempDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if( $tmpfilePath == "{$filepath}.part" ) {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if( preg_match( '/\.part$/', $file ) && ( filemtime( $tmpfilePath ) < time() - $maxFileAge ) ) {
                    @unlink( $tmpfilePath );
                }
            }
            closedir( $dir );
        }


        // Open temp file
        if( !$out = @fopen( "{$filepath}.part", $chunks ? "ab" : "wb" ) ) {
            die( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
        }

        if( !empty( $_FILES ) ) {
            if( $_FILES["file"]["error"] || !is_uploaded_file( $_FILES["file"]["tmp_name"] ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}' );
            }

            // Read binary input stream and append it to temp file
            if( !$in = @fopen( $_FILES["file"]["tmp_name"], "rb" ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
            }
        } else {
            if( !$in = @fopen( "php://input", "rb" ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
            }
        }

        while( $buff = fread( $in, 4096 ) ) {

            //todo: add description
            $buff = apply_filters( 'wp_client_uploader_file_content', $buff, $ext );

            fwrite( $out, $buff );
        }

        @fclose( $out );
        @fclose( $in );

        // Check if file has been uploaded
        if ( !$chunks || $chunk == $chunks - 1 ) {
            // Strip the temp .part suffix off
            $msg = '';

            /*our_hook_
                    hook_name: wp_client_before_client_uploaded_file
                    hook_title: Client Uploads File
                    hook_description: Hook runs before client uploads file.
                    hook_type: filter
                    hook_in: wp-client
                    hook_location class.ajax.php
                    hook_param: string $error_message, string $filepath
                    hook_since: 3.8.0
                */
            $msg = apply_filters( 'wp_client_before_client_uploaded_file', $msg, "{$filepath}.part" );

            if ( ! empty( $msg ) )
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "' . $msg . '"}, "id" : "id"}' );

            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

            //use exist category
            if ( ! empty( $_REQUEST['file_cat_id'] ) ) {
                if ( is_numeric( $_REQUEST['file_cat_id'] ) ) {
                    //get category by ID
                    $category_data = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id, folder_name
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id = %d",
                        $_REQUEST['file_cat_id']
                    ), ARRAY_A );

                    $cat_id = $category_data['cat_id'];
                    $folder_name = $category_data['folder_name'];

                } else {
                    //get categoty by name
                    $category_data = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id, folder_name
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 '%s' COLLATE utf8_bin",
                        $_REQUEST['file_cat_id']
                    ), ARRAY_A );

                    $cat_id = $category_data['cat_id'];
                    $folder_name = $category_data['folder_name'];
                }

                //if wrong categoty - get default category
                if ( !$cat_id ) {
                    $cat_id = $wpdb->get_var(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                    );

                    if( !$cat_id ) {
                        //get new order for category
                        $category_order = $wpdb->get_var(
                            "SELECT COUNT(cat_id)
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE parent_id='0'"
                        );

                        $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_file_categories",
                            array(
                                'cat_name'      => 'General',
                                'folder_name'   => 'General',
                                'parent_id'     => '0',
                                'cat_order'     => $category_order + 1
                            )
                        );

                        $cat_id = $wpdb->insert_id;
                    }

                    $folder_name = 'General';
                }
            } else {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                );

                if( !$cat_id ) {
                    //get new order for category
                    $category_order = $wpdb->get_var(
                        "SELECT COUNT(cat_id)
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE parent_id='0'"
                    );

                    $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                    $wpdb->insert(
                        "{$wpdb->prefix}wpc_client_file_categories",
                        array(
                            'cat_name'      => 'General',
                            'folder_name'   => 'General',
                            'parent_id'     => '0',
                            'cat_order'     => $category_order + 1
                        )
                    );

                    $cat_id = $wpdb->insert_id;
                }

                $folder_name = 'General';
            }


            $new_name = basename( rand( 0000,9999 ) . $orig_name );

            //todo: add description (old:wp_client_protects_file)
            $new_name = apply_filters( 'wp_client_file_upload_new_name', $new_name );

            $args = array(
                'cat_id'    => $cat_id,
                'filename'  => $new_name
            );
            $new_filepath = WPC()->files()->get_file_path( $args );

            $filedir_array      = explode( '/', $new_filepath );
            unset( $filedir_array[ count( $filedir_array ) - 1 ] );
            $filedir            = implode( '/', $filedir_array );

            if ( !is_dir( $filedir ) ) {
                //create folders for file destination if it was not created
                WPC()->files()->create_file_category_folder( $cat_id, trim( $folder_name ) );
            }

            if( rename( "{$filepath}.part", $new_filepath ) ) {

                //creating thumbnails for images
                if( in_array( $ext, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
                    WPC()->files()->create_image_thumbnail( $args );
                }

                $note = isset( $_REQUEST['note'] ) ? $_REQUEST['note'] : '';

                $filesize = filesize( $new_filepath );
                $wpdb->insert(
                    "{$wpdb->prefix}wpc_client_files",
                    array(
                        'user_id'             => $user_id,
                        'page_id'             => isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0,
                        'time'                => time(),
                        'size'                => ( isset( $filesize ) && !empty( $filesize ) ) ? $filesize : 0,
                        'filename'            => $new_name,
                        'name'                => $orig_name,
                        'title'               => $orig_name,
                        'cat_id'              => $cat_id,
                        'description'         => $note,
                        'external'            => '0'
                    ),
                    array( '%d','%d','%d','%d','%s','%s','%s','%d','%s' )
                );

                $file_id = $wpdb->insert_id;

                //assigned process
                WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', array( $user_id ) );

                $arguments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_files WHERE id=" . $file_id, ARRAY_A );

                /*our_hook_
                        hook_name: wp_client_client_uploaded_file
                        hook_title: Client Uploads File
                        hook_description: Hook runs when client uploads file.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.ajax.php
                        hook_param: array $file_data
                        hook_since: 3.3.0
                    */
                do_action( 'wp_client_client_uploaded_file', $arguments );

                $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

                //email to admins
                $args = array(
                    'role'      => 'wpc_admin',
                    'fields'    => array( 'user_email' )
                );
                $admin_emails = get_users( $args );
                $emails_array = array();
                if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                    foreach( $admin_emails as $admin_email ) {
                        $emails_array[] = $admin_email->user_email;
                    }
                }

                $emails_array[] = get_option( 'admin_email' );

                $emails_array = array_unique( $emails_array );

                $args = array(
                    'client_id' => $user_id,
                    'file_name' => $orig_name,
                    'file_category' => $file_category,
                    'file_download_link' => WPC()->files()->get_file_download_link($file_id, 'for_admin')
                );
                foreach( $emails_array as $to_email ) {
                    if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                        WPC()->mail( 'client_uploaded_file', $to_email, $args, 'client_uploaded_file', $new_filepath );
                    } else {
                        WPC()->mail( 'client_uploaded_file', $to_email, $args, 'client_uploaded_file' );
                    }
                }

                //send message to client manager
                //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $user_id );
                $manager_ids = WPC()->members()->get_client_managers( $user_id );

                if( is_array( $manager_ids ) && count( $manager_ids ) ) {
                    foreach( $manager_ids as $manager_id ) {
                        if ( 0 < $manager_id ) {
                            $manager = get_userdata( $manager_id );
                            if ( $manager ) {
                                $manager_email = $manager->get( 'user_email' );
                                if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                                    //send email
                                    WPC()->mail( 'client_uploaded_file', $manager_email, $args, 'client_uploaded_file', $new_filepath );
                                } else {
                                    //send email
                                    WPC()->mail( 'client_uploaded_file', $manager_email, $args, 'client_uploaded_file' );
                                }
                            }

                        }
                    }
                }
            }


            // Return Success JSON-RPC response
            die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }


    /**
     * AJAX Admin Upload/add new file
     **/
    function ajax_admin_upload_files() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        //Check file size
        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
        if ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) {
            if ( isset( $_FILES['Filedata']['size'] ) && '' != $_FILES['Filedata']['size'] ) {
                $size = round( $_FILES['Filedata']['size'] / 1024 );
                if ( $size > $wpc_file_sharing['file_size_limit'] ) {
                    exit;
                }
            }
        }

        //add new category from admin side
        if ( isset( $_POST['file_category_new'] ) && !empty( $_POST['file_category_new'] ) ) {
            $category_name = $_POST['file_category_new'];

            if( preg_match( "/[\/\:\*\?\"\<\>\\\|\%\$]/", $category_name ) ) {
                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'cerr' ), 'admin.php' ) );
            }

            //checking that category not exist with other ID
            $category_exist_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_name = _utf8 '%s' COLLATE utf8_bin AND
                        parent_id='0'",
                $category_name
            ) );

            if ( $category_exist_id ) {
                $cat_id = $category_exist_id;
            } else {
                //create new category
                $args = array(
                    'cat_id'      => '0',
                    'cat_name'    => trim( $category_name ),
                    'folder_name' => trim( $category_name ),
                    'parent_id'   => '0',
                    'cat_clients' => '',
                    'cat_circles' => '',
                );

                //checking that category with folder_name not exist with other ID
                $result = $wpdb->get_row( $wpdb->prepare(
                    "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE folder_name = _utf8 '%s' COLLATE utf8_bin AND
                            parent_id = '0'",
                    $args['folder_name']
                ), ARRAY_A );

                //if new category exist with other ID
                if( isset( $result ) && !empty( $result ) ) {
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'fne' ), 'admin.php' ) );
                }

                $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );
                if( is_dir( $target_path . $args['folder_name'] ) ) {
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'fe' ), 'admin.php' ) );
                }

                $cat_id = WPC()->files()->create_file_category( $args );
            }

        } else {
            //use exist category from admin or client (Regular uploader) side
            if ( isset( $_POST['file_cat_id'] ) && 0 < $_POST['file_cat_id'] ) {
                $cat_id = $_POST['file_cat_id'];
                $category_name = $wpdb->get_var( $wpdb->prepare(
                    "SELECT folder_name
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id=%d",
                    $cat_id
                ) );

            } else {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                );

                if( !$cat_id ) {
                    //get new order for category
                    $category_order = $wpdb->get_var(
                        "SELECT COUNT(cat_id)
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE parent_id='0'"
                    );

                    $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                    $wpdb->insert(
                        "{$wpdb->prefix}wpc_client_file_categories",
                        array(
                            'cat_name'      => 'General',
                            'folder_name'   => 'General',
                            'parent_id'     => '0',
                            'cat_order'     => $category_order + 1
                        )
                    );

                    $cat_id = $wpdb->insert_id;
                }

                $category_name = 'General';
            }
        }


        if ( isset( $_FILES['Filedata']['name'] ) && '' != $_FILES['Filedata']['name'] ) {

            //create folders for file destination if it was not created
            WPC()->files()->create_file_category_folder( $cat_id, trim( $category_name ) );

            $owner_id       = ( 0 < get_current_user_id() ) ? get_current_user_id() : 0;
            $orig_name      = $_FILES['Filedata']['name'];
            $new_name       = basename( rand( 0000,9999 ) . $orig_name );

            //todo: add description (old:wp_client_protects_file)
            $new_name = apply_filters( 'wp_client_file_upload_new_name', $new_name );

            $args = array(
                'cat_id'    => $cat_id,
                'filename'  => $new_name
            );
            $filepath    = WPC()->files()->get_file_path( $args );

            //Upload file
            if ( move_uploaded_file( $_FILES['Filedata']['tmp_name'], $filepath ) ) {

                $ext = explode( '.', $orig_name );
                $ext = strtolower( end( $ext ) );

                if( in_array( $ext, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {

                    WPC()->files()->create_image_thumbnail( $args );

                }

                $wpdb->insert(
                    "{$wpdb->prefix}wpc_client_files",
                    array(
                        'user_id'             => $owner_id,
                        'page_id'             => 0,
                        'time'                => time(),
                        'size'                => $_FILES['Filedata']['size'],
                        'filename'            => $new_name,
                        'name'                => $orig_name,
                        'title'               => $orig_name,
                        'description'         => isset( $_POST['file_description'] ) ? $_POST['file_description'] : '',
                        'cat_id'              => $cat_id,
                        'last_download'       => '',
                        'external'            => '0'
                    ),
                    array( '%d','%d','%d','%d','%s','%s','%s','%s','%d','%s' )
                );

                //assigned process
                $file_id = $wpdb->insert_id;

                if( isset( $file_id ) && !empty( $file_id ) ) {
                    //set file tags
                    if( isset( $_REQUEST['file_tags'] ) && is_string( $_REQUEST['file_tags'] ) ) {
                        $file_tags = preg_replace( '/^\[|\]$/', '', stripcslashes( $_REQUEST['file_tags'] ) ) ;
                        $file_tags = explode( ",", $file_tags ) ;
                        foreach ( $file_tags as $key => $tag ) {
                            $temp_tag = preg_replace( '/^\"|\"$/', '', $tag );
                            $file_tags[ $key ] = stripcslashes( $temp_tag ) ;
                        }
                        wp_set_object_terms( $file_id, $file_tags, 'wpc_file_tags' );

                    }

                    //set clients
                    $clients_array = array();
                    if ( isset( $_POST['wpc_clients'] ) && !empty( $_POST['wpc_clients'] ) )  {
                        if( $_POST['wpc_clients'] == 'all' ) {
                            $clients_array = WPC()->members()->get_client_ids();
                        } else {
                            $clients_array = explode( ',', $_POST['wpc_clients'] );
                        }
                    }
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', $clients_array );

                    //set Client Circle
                    $circles_array = array();
                    if ( isset( $_POST['wpc_circles'] ) && !empty( $_POST['wpc_circles'] ) )  {
                        if( $_POST['wpc_circles'] == 'all' ) {
                            $circles_array = WPC()->groups()->get_group_ids();
                        } else {
                            $circles_array = explode( ',', $_POST['wpc_circles'] );
                        }
                    }
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $circles_array );
                }


                /*our_hook_
                        hook_name: wp_client_admin_uploaded_file
                        hook_title: Admin Uploads File
                        hook_description: Hook runs when admin uploads file.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.ajax.php
                        hook_param: int $file_id
                        hook_since: 4.4.0
                    */
                do_action( 'wp_client_admin_uploaded_file', $file_id );


                /*
                    * Send notify to assigned client and staff
                    */
                if ( isset( $_POST['new_file_notify'] ) && '1' == $_POST['new_file_notify'] ) {

                    //get clients id
                    $send_client_ids = $clients_array;

                    //get clients id from Client Circles
                    $send_group_ids = $circles_array;
                    if ( is_array( $send_group_ids ) && 0 < count( $send_group_ids ) )
                        foreach( $send_group_ids as $group_id )
                            $send_client_ids = array_merge( $send_client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );

                    $send_client_ids = array_unique( $send_client_ids );

                    //send notify
                    if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {

                        $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

                        foreach( $send_client_ids as $send_client_id ) {
                            if ( '' != $send_client_id ) {

                                $email_args = array( 'client_id' => $send_client_id, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id) );

                                $client = get_userdata( $send_client_id );
                                if ( $client ) {
                                    $client_email = $client->get( 'user_email' );
                                    //send email to client
                                    if( isset( $_POST['attach_file_user'] ) && '1' == $_POST['attach_file_user'] ) {
                                        WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff', $filepath );
                                    } else {
                                        WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff' );
                                    }
                                }

                                //get client staff
                                $args = array(
                                    'role'          => 'wpc_client_staff',
                                    'meta_key'      => 'parent_client_id',
                                    'meta_value'    => $send_client_id,
                                );
                                $staffs = get_users( $args );

                                //send email to staff
                                if ( is_array( $staffs ) && 0 < count( $staffs ) ) {
                                    foreach( $staffs as $staff ) {
                                        $email_args = array( 'client_id' => $staff->ID, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id) );
                                        if( isset( $_POST['attach_file_user'] ) && '1' == $_POST['attach_file_user'] ) {
                                            //send email
                                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff', $filepath );
                                        } else {
                                            //send email
                                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff' );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }


                if ( !defined( "DOING_AJAX" ) )
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'updated' => 'true', 'dmsg' => urlencode( 'The file '. basename( $_FILES['Filedata']['name'] ). ' has been uploaded!' ) ), 'admin.php' ) );
            } else {
                if ( !defined( "DOING_AJAX" ) )
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'updated' => 'true', 'dmsg' => urlencode( __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) ) ), 'admin.php' ) );
            }
        }

    }


    /**
     * AJAX Admin Upload/add new file
     **/
    function ajax_admin_plupload_upload_files() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        //Upload file
        $tempDir = WPC()->get_upload_dir( 'wpclient/_uberloader_temp/' );

        // Get a file name
        if( isset( $_REQUEST["name"] ) ) {
            $orig_name = $_REQUEST["name"];
        } elseif ( !empty( $_FILES ) ) {
            $orig_name = $_FILES["file"]["name"];
        } else {
            $orig_name = uniqid( "file_" );
        }

        $ext = explode( '.', $orig_name );
        $ext = strtolower( end( $ext ) );

        $filepath = $tempDir . basename( $orig_name );

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 100 * 3600; // Temp file age in seconds


        // Chunking might be enabled
        $chunk = isset( $_REQUEST["chunk"] ) ? intval( $_REQUEST["chunk"] ) : 0;
        $chunks = isset( $_REQUEST["chunks"] ) ? intval( $_REQUEST["chunks"] ) : 0;

        // Remove old temp files
        if( $cleanupTargetDir ) {
            if( !is_dir( $tempDir ) || !$dir = opendir( $tempDir ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}' );
            }

            while( ( $file = readdir( $dir ) ) !== false ) {
                $tmpfilePath = $tempDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if( $tmpfilePath == "{$filepath}.part" ) {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if( preg_match( '/\.part$/', $file ) && ( filemtime( $tmpfilePath ) < time() - $maxFileAge ) ) {
                    @unlink( $tmpfilePath );
                }
            }
            closedir( $dir );
        }


        // Open temp file
        if( !$out = @fopen( "{$filepath}.part", $chunks ? "ab" : "wb" ) ) {
            die( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
        }

        if( !empty( $_FILES ) ) {
            if( $_FILES["file"]["error"] || !is_uploaded_file( $_FILES["file"]["tmp_name"] ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}' );
            }

            // Read binary input stream and append it to temp file
            if( !$in = @fopen( $_FILES["file"]["tmp_name"], "rb" ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
            }
        } else {
            if( !$in = @fopen( "php://input", "rb" ) ) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
            }
        }

        while( $buff = fread( $in, 4096 ) ) {

            //todo: add description
            $buff = apply_filters( 'wp_client_uploader_file_content', $buff, $ext );

            fwrite( $out, $buff );
        }

        @fclose( $out );
        @fclose( $in );

        // Check if file has been uploaded
        if( !$chunks || $chunk == $chunks - 1 ) {
            // Strip the temp .part suffix off

            //add new category from admin side
            if ( isset( $_REQUEST['file_category_new'] ) && !empty( $_REQUEST['file_category_new'] ) ) {
                $category_name = $_REQUEST['file_category_new'];

                if( preg_match( "/[\/\:\*\?\"\<\>\\\|\%\$]/", $category_name ) ) {
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'cerr' ), 'admin.php' ) );
                }

                //checking that category not exist with other ID
                $category_exist_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_name = _utf8 '%s' COLLATE utf8_bin AND
                            parent_id='0'",
                    $category_name
                ) );

                if ( $category_exist_id ) {
                    $cat_id = $category_exist_id;
                } else {

                    //create new category
                    $args = array(
                        'cat_id'      => '0',
                        'cat_name'    => trim( $category_name ),
                        'folder_name' => trim( $category_name ),
                        'parent_id'   => '0',
                        'cat_clients' => '',
                        'cat_circles' => '',
                    );

                    //checking that category with folder_name not exist with other ID
                    $result = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE folder_name = _utf8 '%s' COLLATE utf8_bin AND
                                parent_id = '0'",
                        $args['folder_name']
                    ), ARRAY_A );

                    //if new category exist with other ID
                    if( isset( $result ) && !empty( $result ) ) {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'fne' ), 'admin.php' ) );
                    }

                    $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );
                    if( is_dir( $target_path . $args['folder_name'] ) ) {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'fe' ), 'admin.php' ) );
                    }

                    $cat_id = WPC()->files()->create_file_category( $args );
                }



            } else {
                //use exist category from admin or client (Regular uploader) side
                if ( isset( $_REQUEST['file_cat_id'] ) && 0 < $_REQUEST['file_cat_id'] ) {
                    $cat_id = $_REQUEST['file_cat_id'];
                    $category_name = $wpdb->get_var( $wpdb->prepare(
                        "SELECT folder_name
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id=%d",
                        $cat_id
                    ) );
                } else {
                    $cat_id = $wpdb->get_var(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                    );

                    if( !$cat_id ) {
                        //get new order for category
                        $category_order = $wpdb->get_var(
                            "SELECT COUNT(cat_id)
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE parent_id='0'"
                        );

                        $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_file_categories",
                            array(
                                'cat_name'      => 'General',
                                'folder_name'   => 'General',
                                'parent_id'     => '0',
                                'cat_order'     => $category_order + 1
                            )
                        );

                        $cat_id = $wpdb->insert_id;
                    }

                    $category_name = 'General';
                }
            }

            $new_name = basename( rand( 0000,9999 ) . $orig_name );

            //todo: add description (old:wp_client_protects_file)
            $new_name = apply_filters( 'wp_client_file_upload_new_name', $new_name );

            $args = array(
                'cat_id'    => $cat_id,
                'filename'  => $new_name
            );
            $new_filepath = WPC()->files()->get_file_path( $args );

            $filedir_array      = explode( '/', $new_filepath );
            unset( $filedir_array[ count( $filedir_array ) - 1 ] );
            $filedir            = implode( '/', $filedir_array );

            if ( !is_dir( $filedir ) ) {
                //create folders for file destination if it was not created
                WPC()->files()->create_file_category_folder( $cat_id, trim( $category_name ) );
            }

            if( rename( "{$filepath}.part", $new_filepath ) ) {

                $ext = explode( '.', $orig_name );
                $ext = strtolower( end( $ext ) );
                //creating thumbnails for images
                if( in_array( $ext, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
                    WPC()->files()->create_image_thumbnail( $args );
                }

                $owner_id = ( 0 < get_current_user_id() ) ? get_current_user_id() : 0;

                $filesize = filesize( $new_filepath );
                $wpdb->insert(
                    "{$wpdb->prefix}wpc_client_files",
                    array(
                        'user_id'             => $owner_id,
                        'page_id'             => 0,
                        'time'                => time(),
                        'size'                => ( isset( $filesize ) && !empty( $filesize ) ) ? $filesize : 0,
                        'filename'            => $new_name,
                        'name'                => $orig_name,
                        'title'               => $orig_name,
                        'description'         => isset( $_REQUEST['file_description'] ) ? $_REQUEST['file_description'] : '',
                        'cat_id'              => $cat_id,
                        'last_download'       => '',
                        'external'            => '0'
                    ),
                    array( '%d','%d','%d','%d','%s','%s','%s','%s','%d','%s' )
                );

                //assigned process
                $file_id = $wpdb->insert_id;

                if( isset( $file_id ) && !empty( $file_id ) ) {
                    //set clients
                    $clients_array = array();
                    if ( isset( $_REQUEST['wpc_clients'] ) && !empty( $_REQUEST['wpc_clients'] ) ) {
                        $wpc_clients = urldecode( $_REQUEST['wpc_clients'] );
                        if( $wpc_clients == 'all' ) {
                            $clients_array = WPC()->members()->get_client_ids();
                        } else {
                            $clients_array = explode( ',', $wpc_clients );
                        }
                    }
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', $clients_array );

                    //set Client Circle
                    $circles_array = array();
                    if ( isset( $_REQUEST['wpc_circles'] ) && !empty( $_REQUEST['wpc_circles'] ) ) {
                        $wpc_circles = urldecode( $_REQUEST['wpc_circles'] );
                        $circles_array = explode( ',', $wpc_circles );
                    }


                    if( isset( $_REQUEST['file_tags'] ) && is_string( $_REQUEST['file_tags'] ) ) {
                        $file_tags = preg_replace( '/^\[|\]$/', '', stripcslashes( $_REQUEST['file_tags'] ) ) ;
                        $file_tags = explode( ",", $file_tags ) ;
                        foreach ( $file_tags as $key => $tag ) {
                            $temp_tag = preg_replace( '/^\"|\"$/', '', $tag );
                            $file_tags[ $key ] = stripcslashes( $temp_tag ) ;
                        }
                        wp_set_object_terms( $file_id, $file_tags, 'wpc_file_tags' );

                    }

                    $auto_assign_circles = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_files = 1 " ) ;

                    $circles_array = array_merge( $circles_array, $auto_assign_circles ) ;
                    $circles_array = array_unique( $circles_array ) ;
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $circles_array );
                }

                /*our_hook_
                        hook_name: wp_client_admin_uploaded_file
                        hook_title: Admin Uploads File
                        hook_description: Hook runs when admin uploads file.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.ajax.php
                        hook_param: int $file_id
                        hook_since: 4.4.0
                    */
                do_action( 'wp_client_admin_uploaded_file', $file_id );


                //Send notify to assigned client and staff
                if ( isset( $_REQUEST['new_file_notify'] ) && '1' == $_REQUEST['new_file_notify'] ) {

                    //get clients id
                    $send_client_ids = $clients_array;

                    //get clients id from Client Circles
                    $send_group_ids = $circles_array;
                    if ( is_array( $send_group_ids ) && 0 < count( $send_group_ids ) )
                        foreach( $send_group_ids as $group_id )
                            $send_client_ids = array_merge( $send_client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );

                    $send_client_ids = array_unique( $send_client_ids );

                    //send notify
                    if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {

                        $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

                        foreach( $send_client_ids as $send_client_id ) {
                            if ( '' != $send_client_id ) {

                                $email_args = array( 'client_id' => $send_client_id, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id) );

                                $client = get_userdata( $send_client_id );
                                if ( $client ) {
                                    $client_email = $client->get( 'user_email' );
                                    //send email to client
                                    if( isset( $_REQUEST['attach_file_user'] ) && '1' == $_REQUEST['attach_file_user'] ) {
                                        WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff', $new_filepath );
                                    } else {
                                        WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff' );
                                    }
                                }

                                //get client staff
                                $args = array(
                                    'role'          => 'wpc_client_staff',
                                    'meta_key'      => 'parent_client_id',
                                    'meta_value'    => $send_client_id,
                                );
                                $staffs = get_users( $args );

                                //send email to staff
                                if ( is_array( $staffs ) && 0 < count( $staffs ) ) {
                                    foreach( $staffs as $staff ) {
                                        $email_args = array( 'client_id' => $staff->ID, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id) );
                                        if( isset( $_REQUEST['attach_file_user'] ) && '1' == $_REQUEST['attach_file_user'] ) {
                                            //send email
                                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff', $new_filepath );
                                        } else {
                                            //send email
                                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff' );
                                        }
                                    }
                                }
                            }

                        }
                    }

                    /*our_hook_
                       hook_name: wp_client_admin_uploaded_file_after_notifications
                       hook_title: Admin Sent Mail
                       hook_description: Hook runs after admin uploads file and sent mail.
                       hook_type: action
                       hook_in: wp-client
                       hook_location class.ajax.php
                       hook_param: int $file_id
                       hook_since: 4.5.7
                   */
                    do_action( 'wp_client_admin_uploaded_file_after_notifications', $file_id );

                }


            }
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }


    /**
     * AJAX update file data
     */
    function ajax_update_file_data() {
        global $wpdb;

        if ( isset( $_POST['file_id'] ) && '' != $_POST['file_id'] ) {

            $file = $wpdb->get_row( $wpdb->prepare(
                "SELECT * 
                    FROM {$wpdb->prefix}wpc_client_files 
                    WHERE id=%d",
                $_POST['file_id']
            ), ARRAY_A );

            if ( ! empty( $_POST['url'] ) ) {

                $update_data = array(
                    'title'         => base64_decode( $_POST['title'] ),
                    'description'   => base64_decode( $_POST['description'] ),
                    'filename'      => $_POST['url'],
                    'protect_url'   => $_POST['protect_url']
                );

            } else {

                $update_data = array(
                    'title'         => base64_decode( $_POST['title'] ),
                    'description'   => base64_decode( $_POST['description'] ),
                );

            }

            if ( ! empty( $_POST['category'] ) ) {
                $old_cat_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT cat_id 
                        FROM {$wpdb->prefix}wpc_client_files WHERE id=%d",
                    $_POST['file_id']
                ) );

                if ( $_POST['category'] != $old_cat_id ) {
                    $update_data['cat_id'] = $_POST['category'];

                    //empty URL - not external file, so move real file at FTP from old folder to new folder
                    if ( empty( $_POST['url'] ) ) {
                        $filepath    = WPC()->files()->get_file_path( $file );
                        $new_category_folder = WPC()->files()->get_category_path( $update_data['cat_id'] );

                        if ( file_exists( $filepath ) )
                            rename( $filepath, $new_category_folder . DIRECTORY_SEPARATOR . $file['filename'] );
                    }
                }
            }

            $wpdb->update(
                "{$wpdb->prefix}wpc_client_files",
                $update_data,
                array( 'id' => $_POST['file_id'] )
            );

            $file_tags = ( isset( $_POST['file_tags'] ) && is_string( $_POST['file_tags'] ) ) ? preg_replace( '/^\[|\]$/', '', stripcslashes( $_POST['file_tags'] ) ) : '' ;
            $file_tags = explode( ",", $file_tags ) ;

            foreach ( $file_tags as $key => $tag ) {
                $temp_tag = preg_replace( '/^\"|\"$/', '', $tag );
                $file_tags[ $key ] = stripcslashes( $temp_tag ) ;
            }
            wp_set_object_terms( $_POST['file_id'], $file_tags, 'wpc_file_tags' );

            /*$wpdb->query( $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}wpc_client_files
                    SET title = '%s',
                        description = '%s'
                    WHERE id = %d",
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['file_id']
                 ) );*/
            if( isset( $_POST['url'] ) && !empty( $_POST['url'] ) ) {
                die( json_encode( array( 'id' => $_POST['file_id'], 'title' => $_POST['title'], 'file_tags' => $file_tags, 'description' => $_POST['description'], 'url' => $_POST['url'], 'protect_url' => $_POST['protect_url'] ) ) );
            } else {
                die( json_encode( array( 'id' => $_POST['file_id'], 'title' => $_POST['title'], 'file_tags' => $file_tags, 'description' => $_POST['description'] ) ) );
            }

        }
        die();
    }


    /**
     * AJAX save template
     **/
    function ajax_admin_save_template() {
        if ( isset( $_POST['wpc_templates'] ) && is_array( $_POST['wpc_templates'] ) ) {
            $opt_key = array_keys( $_POST['wpc_templates'] );
            $opt_name = $opt_key[0];
            $template = $_POST['wpc_templates'][$opt_name];

            //update settings
            if ( 'wpc_templates_shortcodes' == $opt_name ) {

                //check permission
                if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
                    && !current_user_can('wpc_edit_shortcode_templates') ) {
                    exit( json_encode( array( 'status' => false, 'message' => __( "Error of permissions.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                }

                $temp_key = array_keys( $template );
                $temp_key = $temp_key[0];

                $wpc_templates_shortcodes_settings = WPC()->get_settings( 'templates_shortcodes_settings' );

                if ( isset( $_POST['wpc_templates_settings']['wpc_templates_shortcodes'][$temp_key]['allow_php_tag'] ) && 'yes' == $_POST['wpc_templates_settings']['wpc_templates_shortcodes'][$temp_key]['allow_php_tag'] ) {
                    $wpc_templates_shortcodes_settings[$temp_key]['allow_php_tag'] = 'yes';
                } else {
                    $wpc_templates_shortcodes_settings[$temp_key]['allow_php_tag'] = 'no';
                }

                WPC()->settings()->update( $wpc_templates_shortcodes_settings, 'templates_shortcodes_settings' );

                $res    = WPC()->admin()->array_base64_decode( $template );
                WPC()->settings()->update( $res[ $temp_key ], 'shortcode_template_' . $temp_key );
                echo json_encode( array( 'status' => true, 'message' => __( 'Template success updated.', WPC_CLIENT_TEXT_DOMAIN ) ) );
            } else {

                //check permission
                if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
                    && !current_user_can('wpc_edit_email_templates') ) {
                    exit( json_encode( array( 'status' => false, 'message' => __( "Error of permissions.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                }

                $templates_data = get_option( $opt_name );
                $res    = WPC()->admin()->array_base64_decode( $template );
                $keys   = WPC()->admin()->show_keys( $res );
                switch( count( $keys ) ) {
                    case 1:
                        $templates_data[$keys[0]] = $res[$keys[0]];
                        break;
                    case 2:
                        $templates_data[$keys[0]][$keys[1]] = $res[$keys[0]][$keys[1]];
                        break;
                    case 3:
                        //                        $templates_data[$keys[0]][$keys[1]][$keys[2]] = $res[$keys[0]][$keys[1]][$keys[2]];
                        $templates_data[$keys[0]][$keys[1]] = $res[$keys[0]][$keys[1]];
                        $templates_data[$keys[0]][$keys[2]] = $res[$keys[0]][$keys[2]];
                        break;
                }

                update_option( $opt_name, $templates_data );
                echo json_encode( array( 'status' => true, 'message' => __( 'Template success updated.', WPC_CLIENT_TEXT_DOMAIN ) ) );

            }


        }

        exit;
    }


    /**
     * AJAX get options filter for files
     * backend Files list table filter
     **/
    function ajax_get_options_filter_for_files() {
        global $wpdb;
        if ( isset( $_POST['filter'] ) ) {
            $t_name = $wpdb->prefix . "wpc_client_files";
            switch( $_POST['filter'] ) {
                case 'author':
                    $where_manager = '';
                    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                        $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                        foreach( $manager_circles as $c_id ) {
                            $manager_clients = array_merge( $manager_clients, WPC()->groups()->get_group_clients_id( $c_id ) );
                        }
                        $manager_clients = array_unique( $manager_clients );

                        foreach( $manager_clients as $client_id ) {
                            $manager_circles = array_merge( $manager_circles, WPC()->groups()->get_client_groups_id( $client_id ) );
                        }
                        $manager_circles = array_unique( $manager_circles );

                        $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $manager_clients );
                        $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $manager_circles );
                        $client_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $manager_clients );
                        $circle_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $manager_circles );
                        $cc_categories = array_unique( array_merge( $client_categories, $circle_categories ) );
                        foreach( $cc_categories as $cat_id ) {
                            $cc_categories = array_merge( $cc_categories, WPC()->files()->get_category_children_ids( $cat_id ) );
                        }
                        $cc_categories = array_unique( $cc_categories );
                        $all_files = array_merge( $client_files, $circle_files );
                        $all_files = array_unique( $all_files );

                        if( count( $cc_categories ) ) {
                            $where = "f.cat_id IN ( '" . implode( "','", $cc_categories ) . "' ) OR ";
                        } else {
                            $where = '';
                        }
                        if ( current_user_can( 'wpc_view_admin_managers_files' ) ) {
                            $where_manager .= " AND (
                                    $where
                                    f.page_id = 0 OR
                                    f.id IN('" . implode( "','", $all_files ) . "') OR
                                    f.user_id IN('" . implode( "','", $manager_clients ) . "')
                                )";
                        } else {
                            $where_manager .= " AND (
                                    $where
                                    f.user_id = " . get_current_user_id() . " OR
                                    f.id IN('" . implode( "','", $all_files ) . "') OR
                                    f.user_id IN('" . implode( "','", $manager_clients ) . "')
                                )";
                        }
                    }

                    $all_authors = $wpdb->get_col(
                        "SELECT DISTINCT user_id
                            FROM {$wpdb->prefix}wpc_client_files f
                            WHERE 1=1 " . " " . $where_manager
                    );

                    ?>
                    <option value="-1" selected="selected"><?php _e( 'Select Author', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php
                    if ( is_array( $all_authors ) && 0 < count( $all_authors ) ) {
                        foreach( $all_authors as $author_id ) {
                            echo '<option value="' . $author_id . '" >' . ( $author_id ? get_userdata( $author_id )->user_login : __( 'Synchronization', WPC_CLIENT_TEXT_DOMAIN ) ) . '</option>';
                        }
                    }
                    break;
                case 'tag':
                    $args = array(
                        'fields'            => 'id=>name',
                    );
                    $all_file_tags = get_terms( 'wpc_file_tags', $args ) ;
                    ?>
                    <option value="-1" selected="selected"><?php _e( 'Select Tag', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php
                    if ( is_array( $all_file_tags ) && 0 < count( $all_file_tags ) ) {
                        foreach( $all_file_tags as $id_tag => $name_tag ) {
                            echo '<option value="' . $id_tag . '" >' . $name_tag . '</option>';
                        }
                    }
                    break;

                case 'client_username':
                    $unique_client = WPC()->assigns()->get_assign_data_by_object_assign( 'file', 'client' );

                    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        $manager_clients = WPC()->members()->get_all_clients_manager();
                        $unique_client = array_intersect( $manager_clients, $unique_client );
                    }

                    $unique_client_new = $wpdb->get_results( "SELECT distinct 
                                                          um.ID, um.display_name, 
                                                          um2.meta_value as nickname, 
                                                          um3.meta_value as business_name 
                                                  FROM {$wpdb->users} um
                                                  LEFT JOIN {$wpdb->usermeta} um2 ON um2.user_id = um.ID
                                                  LEFT JOIN {$wpdb->usermeta} um3 ON um3.user_id = um.ID
                                                  WHERE um.ID IN('" . implode( "','", $unique_client ) . "')
                                                  AND um2.meta_key='nickname'
                                                  AND um3.meta_key='wpc_cl_business_name'
                                                  ORDER BY um2.meta_value ASC", ARRAY_A );

                    ?>
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    if ( is_array( $unique_client ) && 0 < count( $unique_client ) )
                        foreach( $unique_client_new as $client ) {
                            if ( '' != $client['ID'] ) {
                                echo '<option value="' . $client['ID'] . '" >' . $client['nickname'] . '</option>';
                            }
                        }
                    break;

                case 'client_business_name':
                    $unique_client = WPC()->assigns()->get_assign_data_by_object_assign( 'file', 'client' );

                    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        $manager_clients = WPC()->members()->get_all_clients_manager();
                        $unique_client = array_intersect( $manager_clients, $unique_client );
                    }

                    $unique_client_new = $wpdb->get_results( "SELECT distinct 
                                                          um.ID, um.display_name, 
                                                          um2.meta_value as nickname, 
                                                          um3.meta_value as business_name 
                                                  FROM {$wpdb->users} um
                                                  LEFT JOIN {$wpdb->usermeta} um2 ON um2.user_id = um.ID
                                                  LEFT JOIN {$wpdb->usermeta} um3 ON um3.user_id = um.ID
                                                  WHERE um.ID IN('" . implode( "','", $unique_client ) . "')
                                                  AND um2.meta_key='nickname'
                                                  AND um3.meta_key='wpc_cl_business_name'
                                                  ORDER BY um3.meta_value ASC", ARRAY_A );

                    ?>
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    if ( is_array( $unique_client ) && 0 < count( $unique_client ) )
                        foreach( $unique_client_new as $client ) {
                            if ( '' != $client['ID'] ) {
                                echo '<option value="' . $client['ID'] . '" >' . $client['business_name'] . '</option>';
                            }
                        }
                    break;

                case 'client_contact_name':
                    $unique_client = WPC()->assigns()->get_assign_data_by_object_assign( 'file', 'client' );

                    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        $manager_clients = WPC()->members()->get_all_clients_manager();
                        $unique_client = array_intersect( $manager_clients, $unique_client );
                    }

                    $unique_client_new = $wpdb->get_results( "SELECT distinct 
                                                          um.ID, um4.display_name as contact_name, 
                                                          um2.meta_value as nickname, 
                                                          um3.meta_value as business_name 
                                                  FROM {$wpdb->users} um
                                                  LEFT JOIN {$wpdb->usermeta} um2 ON um2.user_id = um.ID
                                                  LEFT JOIN {$wpdb->usermeta} um3 ON um3.user_id = um.ID
                                                  LEFT JOIN {$wpdb->users} um4 ON um4.ID = um.ID
                                                  WHERE um.ID IN('" . implode( "','", $unique_client ) . "')
                                                  AND um2.meta_key='nickname'
                                                  AND um3.meta_key='wpc_cl_business_name'
                                                  ORDER BY um.display_name ASC", ARRAY_A );

                    ?>
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    if ( is_array( $unique_client ) && 0 < count( $unique_client ) )
                        foreach( $unique_client_new as $client ) {
                            if ( '' != $client['ID'] ) {
                                echo '<option value="' . $client['ID'] . '" >' . $client['contact_name'] . '</option>';
                            }
                        }
                    break;

                case 'circle':

                    $unique_circle = WPC()->assigns()->get_assign_data_by_object_assign( 'file', 'circle' );

                    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                        $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                        $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                        foreach( $manager_clients as $client_id ) {
                            $manager_circles = array_merge( $manager_circles, WPC()->groups()->get_client_groups_id( $client_id ) );
                        }
                        $manager_circles = array_unique( $manager_circles );
                        $unique_circle = array_intersect( $manager_circles, $unique_circle );
                        if ( !current_user_can( 'wpc_view_admin_managers_files' ) ) {
                            foreach( $unique_circle as $k=>$circle ) {
                                $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $circle );
                                if( !count( $circle_files ) ) {
                                    unset( $unique_circle[ $k ] );
                                }
                            }
                        }
                    }
                    $unique_circle = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN('" . implode( "','", $unique_circle ) . "')", ARRAY_A );
                    ?>
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></option>
                    <?php
                    if ( is_array( $unique_circle ) && 0 < count( $unique_circle ) )
                        foreach( $unique_circle as $circle_id ) {
                            if ( '' != $circle_id['group_id'] ) {
                                echo '<option value="' . $circle_id['group_id'] . '">' . $circle_id['group_name'] . '</option>';
                            }
                        }

                    break;

                case 'category': ?>

                    <option value="-1" selected="selected"><?php _e( 'Select Category', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php WPC()->files()->render_category_list_items();

                    break;
            }
        }
        exit;
    }


    /**
     * AJAX get options filter for files
     **/
    function ajax_get_options_filter_for_files_download_log() {
        global $wpdb;
        if ( isset( $_POST['filter'] ) ) {
            $t_name = $wpdb->prefix . "wpc_client_files_download_log";
            switch( $_POST['filter'] ) {
                case 'downloaded_by':

                    if( isset( $_POST['select_filter'] ) && !empty( $_POST['select_filter'] ) ) {

                        $all_files = $wpdb->get_results( $wpdb->prepare(
                            "SELECT DISTINCT fdl.file_id,
                                    f.name,
                                    f.title
                                FROM {$wpdb->prefix}wpc_client_files_download_log fdl,
                                    {$wpdb->prefix}wpc_client_files f
                                WHERE f.id = fdl.file_id AND
                                    fdl.client_id = %d",
                            $_POST['select_filter']
                        ), ARRAY_A );

                        /*if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                                $all_authors = array_intersect( $manager_clients, $all_authors );
                            } */
                        ?>
                        <option value="-1" selected="selected"><?php _e( 'Select File', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="all"><?php _e( 'All Files', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <?php
                        if ( is_array( $all_files ) && 0 < count( $all_files ) ) {
                            foreach( $all_files as $file ) {
                                $filename = ( isset( $file['title'] ) && '' != $file['title'] ) ? $file['title'] : $file['name'];
                                echo '<option value="' . $file['file_id'] . '" >' . $filename . '</option>';
                            }
                        }

                    } else {

                        $all_authors = $wpdb->get_col( "SELECT DISTINCT client_id FROM $t_name" );
                        if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                            $manager_clients = WPC()->members()->get_all_clients_manager();
                            $all_authors = array_intersect( $manager_clients, $all_authors );
                        }
                        ?>
                        <option value="-1" selected="selected"><?php _e( 'Select Client', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <?php
                        if ( is_array( $all_authors ) && 0 < count( $all_authors ) ) {
                            foreach( $all_authors as $author_id ) {
                                echo '<option value="' . $author_id . '" >' . get_userdata( $author_id )->user_login . '</option>';
                            }
                        }

                    }
                    break;
                case 'file':
                    if( isset( $_POST['select_filter'] ) && !empty( $_POST['select_filter'] ) ) {
                        $all_authors = $wpdb->get_col( $wpdb->prepare(
                            "SELECT DISTINCT client_id
                                FROM {$wpdb->prefix}wpc_client_files_download_log
                                WHERE file_id=%d",
                            $_POST['select_filter']
                        ) );

                        if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                            $manager_clients = WPC()->members()->get_all_clients_manager();
                            $all_authors = array_intersect( $manager_clients, $all_authors );
                        }
                        ?>
                        <option value="-1" selected="selected"><?php _e( 'Select Client', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="all"><?php _e( 'All Clients', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <?php
                        if ( is_array( $all_authors ) && 0 < count( $all_authors ) ) {
                            foreach( $all_authors as $author_id ) {
                                echo '<option value="' . $author_id . '" >' . get_userdata( $author_id )->user_login . '</option>';
                            }
                        }
                    } else {

                        $all_files = $wpdb->get_results(
                            "SELECT DISTINCT fdl.file_id,
                                    f.name,
                                    f.title
                                FROM {$wpdb->prefix}wpc_client_files_download_log fdl,
                                    {$wpdb->prefix}wpc_client_files f
                                WHERE f.id = fdl.file_id" ,
                            ARRAY_A );

                        /*if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                                $all_authors = array_intersect( $manager_clients, $all_authors );
                            } */
                        ?>
                        <option value="-1" selected="selected"><?php _e( 'Select File', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <?php
                        if ( is_array( $all_files ) && 0 < count( $all_files ) ) {
                            foreach( $all_files as $file ) {
                                $filename = ( isset( $file['title'] ) && '' != $file['title'] ) ? $file['title'] : $file['name'];
                                echo '<option value="' . $file['file_id'] . '" >' . $filename . '</option>';
                            }
                        }

                    }
                    break;
            }
        }
        exit;
    }


    /**
     * AJAX get options filter for managers
     **/
    function ajax_get_options_filter_for_managers() {
        global $wpdb;
        if ( isset( $_POST['filter'] ) ) {
            switch( $_POST['filter'] ) {
                case 'client':

                    $clients = WPC()->assigns()->get_assign_data_by_object_assign( 'manager' , 'client' );
                    ?>
                    <option value="-1" selected><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    foreach( $clients as $client_id ) {
                        echo '<option value="' . $client_id . '">' . get_userdata( $client_id )->user_login . '</option>';
                    }
                    break;

                case 'circle':

                    $unique_circle = WPC()->assigns()->get_assign_data_by_object_assign( 'manager', 'circle' );

                    $unique_circle = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN(" . implode( ',', $unique_circle ) . ")", ARRAY_A );
                    ?>
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) ?></option>
                    <?php
                    if ( is_array( $unique_circle ) && 0 < count( $unique_circle ) )
                        foreach( $unique_circle as $circle_id ) {
                            if ( '' != $circle_id['group_id'] ) {
                                echo '<option value="' . $circle_id['group_id'] . '">' . $circle_id['group_name'] . '</option>';
                            }
                        }
                    break;
            }
        }
        exit;
    }


    /**
     * AJAX get options filter for permissions report
     **/
    function ajax_get_options_filter_for_permissions() {
        global $wpdb;
        if ( isset( $_POST['left_select'] ) ) {
            switch( $_POST['left_select'] ) {
                case 'client':
                    $excluded_clients = "'" . implode( "','", WPC()->members()->get_excluded_clients() ) . "'";

                    echo '<option value="all">' . sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</option>';
                    $clients = $wpdb->get_results( "SELECT u.ID as id, u.user_login as login
                                    FROM {$wpdb->users} u
                                    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                    WHERE
                                        um.meta_key = '{$wpdb->prefix}capabilities'
                                        AND um.meta_value LIKE '%s:10:\"wpc_client\";%'
                                        AND u.ID NOT IN ({$excluded_clients})
                                    ", ARRAY_A );

                    if ( 0 < count( $clients ) ) {
                        foreach( $clients as $client ) {
                            echo '<option value="' . $client['id'] . '">' . $client['login'] . '</option>';
                        }
                    }
                    break;

                case 'circle':

                    $circles = $wpdb->get_results( "SELECT group_id as id, group_name as name
                                    FROM {$wpdb->prefix}wpc_client_groups
                                    ", ARRAY_A );

                    echo '<option value="all">' . sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ) . '</option>';
                    if ( 0 < count( $circles ) ) {
                        foreach( $circles as $circle ) {
                            echo '<option value="' . $circle['id'] . '">' . $circle['name'] . '</option>';
                        }
                    }
                    break;

                case 'file':
                    $files = $wpdb->get_results( "SELECT id, name, filename FROM {$wpdb->prefix}wpc_client_files", ARRAY_A );

                    echo '<option value="all">' . __( 'Select File', WPC_CLIENT_TEXT_DOMAIN ) . '</option>';
                    if ( 0 < count( $files ) ) {
                        foreach( $files as $file ) {
                            echo '<option value="' . $file['id'] . '">' . $file['name'] . ' (' . $file['filename'] . ')</option>';
                        }
                    }
                    break;

                case 'file_category':
                    $categories = $wpdb->get_results( "SELECT cat_id as id, cat_name as name FROM {$wpdb->prefix}wpc_client_file_categories", ARRAY_A );

                    echo '<option value="all">' . __( 'Select Category', WPC_CLIENT_TEXT_DOMAIN ) . '</option>';
                    if ( 0 < count( $categories ) ) {
                        foreach( $categories as $category ) {
                            echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                        }
                    }
                    break;

                case 'portal_page':
                    $portal_pages = $wpdb->get_results( "SELECT ID as id, post_title as name FROM {$wpdb->posts} WHERE post_type='clientspage'", ARRAY_A );

                    echo '<option value="all">' . sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</option>';
                    if ( 0 < count( $portal_pages ) ) {
                        foreach( $portal_pages as $portal_page ) {
                            echo '<option value="' . $portal_page['id'] . '">' . $portal_page['name'] . '</option>';
                        }
                    }
                    break;

                case 'portal_page_category':
                    $categories = WPC()->categories()->get_clientspage_categories();

                    echo '<option value="all">' . __( 'Select Category', WPC_CLIENT_TEXT_DOMAIN ) . '</option>';
                    if ( 0 < count( $categories ) ) {
                        foreach( $categories as $category ) {
                            echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                        }
                    }
                    break;
                default:
                    do_action( 'wpc_get_filter_for_permissions_' . $_POST['left_select'] );
                    break;
            }
        }
        exit;
    }


    /**
     * AJAX get report for permissions report
     **/
    function ajax_get_report_for_permissions() {
        global $wpdb;
        //add key to the end array
        $members_entity = array(
            'client' => WPC()->custom_titles['client']['s'],
            'circle' => WPC()->custom_titles['circle']['s']
        );

        $members_entity = apply_filters( 'wpc_permission_reports_members_entity', $members_entity );

        $content_entity = array(
            'file'                  => __( 'File', WPC_CLIENT_TEXT_DOMAIN ),
            'portal_page'           => WPC()->custom_titles['portal_page']['s'],
            'file_category'         => __( 'File Category', WPC_CLIENT_TEXT_DOMAIN ),
            'portal_page_category'  => sprintf( __( '%s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] )
        );
        $content_entity = apply_filters( 'wpc_permission_reports_content_entity', $content_entity );

        $array_key = array_merge( $members_entity, $content_entity );
        $all_left_key = array_keys( $array_key );
        $all_right_key = array_merge( $all_left_key, array('all') );

        if ( isset( $_POST['left_key'] ) && in_array( $_POST['left_key'], $all_left_key ) && isset( $_POST['left_value'] ) && is_numeric( $_POST['left_value'] ) && $_POST['right_key'] && in_array( $_POST['right_key'], $all_right_key ) ) {

            if ( 'there' == $_POST['course'] )
                $there = true;
            else $there = false;

            //create circle name array
            $circles = $wpdb->get_results( "SELECT group_id as id, group_name as name FROM {$wpdb->prefix}wpc_client_groups", ARRAY_A );
            foreach ( $circles as $value ) $name_circles[ $value['id'] ] = $value['name'];

            $name_cats_file = $name_cats_portal_page = array();
            //create category name array
            if ( $there or ( !$there && ('client' == $_POST['right_key'] or 'all' == $_POST['right_key'] ) ) ) {
                if ( 'file' == $_POST['left_key'] || 'file' == $_POST['right_key'] || ( $there && 'all' == $_POST['right_key'] ) ) {
                    $cats = $wpdb->get_results( "SELECT cat_id as id, cat_name as name FROM {$wpdb->prefix}wpc_client_file_categories", ARRAY_A );
                    foreach ( $cats as $cat ) $name_cats_file[ $cat['id'] ] = $cat['name'];
                }
                if ( 'portal_page' == $_POST['left_key'] || 'portal_page' == $_POST['right_key'] || ( $there && 'all' == $_POST['right_key'] ) ) {
                    $cats = WPC()->categories()->get_clientspage_categories();
                    foreach ( $cats as $cat ) $name_cats_portal_page[ $cat['id'] ] = $cat['name'];
                }
            }

            //which blocks report
            if ( 'all' == $_POST['right_key'] ) {
                if ( $there ) {
                    $blocks_report = array_keys( $content_entity );
                } else {
                    $blocks_report = array( 'client', 'circle' );
                }
            } else {
                $blocks_report = array( $_POST['right_key'] );
            }

            //bloks
            foreach ( $blocks_report as $block ) {
                $items = $ids_circle = array();
                if ( $there ) {
                    $where_object_type  = " WHERE object_type='$block'";
                    $and_assign_type    = " AND assign_type='{$_POST['left_key']}'";
                    $and_id             = " AND assign_id='{$_POST['left_value']}'";
                    $what_select        = "object_id";
                    $which_class        = "block_left";

                    //items circles
                    if (  'client' == $_POST['left_key'] ) {
                        $ids_circle = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id='{$_POST['left_value']}'" );
                        $sql_items_circle = "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type AND assign_type='circle' AND assign_id='%d'";

                        //items circle->category
                        if ( 'file' == $block || 'portal_page' == $block ) {
                            $name_array_cat = 'name_cats_' . $block;
                            $name_cats = $$name_array_cat;
                            $temp = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN ('" . implode( "','", $ids_circle ) . "')", ARRAY_A );
                            foreach ( $temp as $value ) $names_circles[ $value['group_id'] ] = $value['group_name'];
                            foreach ( $ids_circle as $circle ) {
                                $ids_circle_cats = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns  WHERE object_type='{$block}_category' AND assign_type='circle' AND assign_id='$circle'" );
                                foreach ( $ids_circle_cats as $cat ) {
                                    if ( 'file' == $block )
                                        $ids_items_circle_cat = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_files WHERE cat_id='$cat'" );
                                    else if ( 'portal_page' == $block )
                                        $ids_items_circle_cat = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND meta_value='$cat'" );
                                    $items = array_merge( $items, $ids_items_circle_cat );
                                    foreach ( $ids_items_circle_cat as $item )
                                        $all_access[ $item ][] = '
                                                <span class="block_left">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'circle', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span class="value_block block_blue" title="' . $names_circles[ $circle ] .'">' . $names_circles[ $circle ] . '</span>
                                                    <span class="unname_block"></span>
                                                </span>
                                                <span class="block_right">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span  class="value_block block_yellow" title="' . $name_cats[ $cat ] . '">' . $name_cats[ $cat ] . '</span>
                                                    <span class="unname_block"></span>
                                                </span>' ;
                                }
                            }
                        }
                    }
                    //items categories
                    if ( 'file' == $block || 'portal_page' == $block ) {
                        $name_array_cat = 'name_cats_' . $block;
                        $name_cats = $$name_array_cat;
                        $ids_cats_assign = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$block}_category' $and_assign_type $and_id" );
                        //items categories circles
                        if ( 'file' == $block )
                            $sql_items_cat = "SELECT id FROM {$wpdb->prefix}wpc_client_files WHERE cat_id='%d'";
                        if ( 'portal_page' == $block )
                            $sql_items_cat = "SELECT post_id as id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND meta_value='%d'";
                        foreach ( $ids_cats_assign as $cat ) {
                            $ids_items_cat = $wpdb->get_col( $wpdb->prepare( $sql_items_cat, $cat ) );
                            $items = array_merge( $items, $ids_items_cat );
                            foreach ( $ids_items_cat as $id_item_cat )
                                $all_access[ $id_item_cat ][] = '
                                        <span class="block_right">
                                            <span class="block_arrow"></span>
                                            <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                            <span class="value_block block_yellow" title="' . $name_cats[ $cat ] .'">' . $name_cats[ $cat ] . '</span>
                                            <span class="unname_block"></span>
                                        </span>' ;
                        }

                    }
                } else { //if ( !$there )
                    $where_object_type  = " WHERE object_type='" . esc_sql( $_POST['left_key'] ) . "'";
                    $and_assign_type    = " AND assign_type='$block'";
                    $and_id             = " AND object_id='" . esc_sql( $_POST['left_value'] ) . "'";
                    $what_select        = "assign_id";
                    $which_class        = "block_right";
                    //circles
                    if (  'client' == $block ) {
                        $ids_circle = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type AND assign_type='circle' $and_id" );
                        $sql_items_circle = "SELECT client_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id='%d'";
                    }
                    //categories
                    if ( 'file' == $_POST['left_key'] || 'portal_page' == $_POST['left_key'] ) {
                        if ( 'file' == $_POST['left_key'] )
                            $id_cat_object = $wpdb->get_var( "SELECT cat_id as id FROM {$wpdb->prefix}wpc_client_files WHERE id='{$_POST['left_value']}'" );

                        if ( 'portal_page' == $_POST['left_key'] )
                            $id_cat_object = $wpdb->get_var( "SELECT meta_value as id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND post_id='{$_POST['left_value']}'" );

                        if ( isset( $id_cat_object ) ) {
                            $ids_items_cat = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$_POST['left_key']}_category' $and_assign_type  AND object_id='$id_cat_object'" );
                            $items = array_merge( $items, $ids_items_cat );
                            $cat_name = $wpdb->get_var( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_{$_POST['left_key']}_categories WHERE cat_id='$id_cat_object'" );
                            foreach ( $ids_items_cat as $id_item_cat )
                                $all_access[ $id_item_cat ][] = '
                                        <span class="block_left">
                                            <span class="block_arrow"></span>
                                            <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                            <span class="value_block block_yellow" title="' . $cat_name .'">' . $cat_name . '</span>
                                            <span class="unname_block"></span>
                                        </span>';
                            if (  'client' == $block ) {
                                $ids_cat_circles = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$_POST['left_key']}_category' AND object_id='$id_cat_object' AND assign_type='circle'" );
                                foreach( $ids_cat_circles as $circle ) {
                                    $ids_items_cat_circle = $wpdb->get_col( "SELECT client_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id='$circle'" );
                                    $items = array_merge( $items, $ids_items_cat_circle );
                                    foreach ( $ids_items_cat_circle as $item )
                                        $all_access[ $item ][] = '
                                                <span class="block_left">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span class="value_block block_yellow" title="' . $cat_name .'">' . $cat_name . '</span>
                                                    <span class="unname_block"></span>
                                                </span>
                                                <span class="block_right">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'circle', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span class="value_block block_blue" title="' . $name_circles[ $circle ] .'">' . $name_circles[ $circle ] . '</span>
                                                    <span class="unname_block"></span>
                                                </span>';
                                }
                            }
                        }
                    }
                }
                //universal
                //direct
                $ids_items_direct = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type $and_assign_type $and_id" );

                foreach ( $ids_items_direct as $id_item_direct ) $all_access[ $id_item_direct ][] = '<span>' . __( 'DIRECT', WPC_CLIENT_TEXT_DOMAIN ) . '<br /><br /></span>';
                $items = array_merge( $items, $ids_items_direct ) ; //may be kick

                //items of circles
                if ( 0 < count( $ids_circle ) ) {
                    $temp = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN ('" . implode( "','", $ids_circle ) . "')", ARRAY_A );
                    foreach ( $temp as $value ) $names_circles[ $value['group_id'] ] = $value['group_name'];
                    foreach ( $ids_circle as $id_circle ) {
                        $ids_items_circle = $wpdb->get_col( $wpdb->prepare( $sql_items_circle, $id_circle ) );
                        $items = array_merge( $items, $ids_items_circle );
                        foreach ( $ids_items_circle as $id_item_circle )
                            $all_access[ $id_item_circle ][] = '
                                    <span class="' . $which_class . '">
                                        <span class="block_arrow"></span>
                                        <span class="name_block">' . __( 'circle', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                        <span class="value_block block_blue" title="' . $name_circles[ $id_circle ] .'">' . $name_circles[ $id_circle ] . '</span>
                                        <span class="unname_block"></span>
                                    </span>';
                    }
                }

                //items of categories
                if ( isset( $ids_cat ) ) {

                }




                echo '<table class="wc_status_table widefat cellspassing_up" width="750px"><caption>' . $array_key[ $block ] . '</caption><thead><tr><th  width="600px"><b>' . __( 'Access granted via...', WPC_CLIENT_TEXT_DOMAIN ) . '</b></th><th width="150px"><b>' . __( 'Name', WPC_CLIENT_TEXT_DOMAIN ) . '</b></th></tr></thead><tbody>';
                $items = array_unique( $items );
                $temp = array();

                switch( $block ){
                    case 'client':
                        $temp = $wpdb->get_results( "SELECT ID as id, user_login as name FROM {$wpdb->users} WHERE ID IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                        break;
                    case 'circle':
                        $temp = $wpdb->get_results( "SELECT group_id as id, group_name as name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                        break;
                    case 'file':
                        $temp = $wpdb->get_results( "SELECT id, title as name FROM {$wpdb->prefix}wpc_client_files WHERE id IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                        break;
                    case 'file_category':
                        $temp = $wpdb->get_results( "SELECT cat_id as id, cat_name as name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                        break;
                    case 'portal_page':
                        $temp = $wpdb->get_results( "SELECT ID as id, post_title as name FROM {$wpdb->posts} WHERE ID IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                        break;
                    case 'portal_page_category':
                        $temp = $wpdb->get_results( "SELECT cat_id as id, cat_name as name FROM {$wpdb->prefix}wpc_client_portal_page_categories WHERE cat_id IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                        break;
                    default:
                        $temp = apply_filters( 'wpc_get_permission_reports_' . $block, $temp, $items );
                        break;
                }
                if ( count( $temp ) ) {
                    foreach ( $temp as $value ) {
                        $item = $value['id'];
                        if ( isset( $all_access[ $item ] ) ) {
                            $this_block = false;

                            foreach ( $all_access[ $item ] as $access ) {
                                echo '<tr class="tr_permissions"><td class="td_permissions">' . $access . '</td>';
                                if( !$this_block ) {
                                    echo  '<td class="td_name_permissions" rowspan="' . count( $all_access[ $item ] ) . '" ><span>' . $value['name'] . '</span></td>';
                                    $this_block = true;
                                }
                                echo '</tr>';
                            }

                        }
                    }
                } else echo '<tr><td colspan="2" align="center">' . __( 'Nothing found', WPC_CLIENT_TEXT_DOMAIN ) . '</td></tr>';

                echo '</tbody></table>';
            }
        }
        exit;
    }


    /**
     * AJAX get options filter for payments
     **/
    function ajax_get_options_filter_for_payments() {

        global $wpdb;
        if ( isset( $_POST['filter'] ) ) {
            switch( $_POST['filter'] ) {
                case 'client':
                    $unique_clients = $wpdb->get_col( "SELECT DISTINCT client_id FROM {$wpdb->prefix}wpc_client_payments" );
                    ?>
                    <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ?></option>
                    <?php
                    if ( is_array( $unique_clients ) && 0 < count( $unique_clients ) ) {
                        foreach( $unique_clients as $client_id ) {
                            if ( '' != $client_id ) {
                                echo '<option value="' . $client_id . '">' . get_userdata( $client_id )->user_login . '</option>';
                            }
                        }
                    }
                    break;

                case 'function':
                    $all_functions = $wpdb->get_col( "SELECT DISTINCT function FROM {$wpdb->prefix}wpc_client_payments" );
                    ?>
                    <option value="-1" selected="selected"><?php _e( 'Select Function', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    <?php
                    if ( is_array( $all_functions ) && 0 < count( $all_functions ) ) {
                        foreach( $all_functions as $function ) {
                            if ( '' != $function ) {
                                echo '<option value="' . $function . '">' . $function . '</option>';
                            }
                        }
                    }
                    break;
            }
        }
        exit;
    }


    /*
        * AJAX get capabilities for role
        */
    function ajax_get_capabilities() {
        if ( isset( $_POST['wpc_role'] ) && '' != $_POST['wpc_role'] ) {

            $wpc_capabilities = WPC()->get_settings( 'capabilities' );

            $capabilities_maps = WPC()->get_capabilities_maps();

            if ( isset( $capabilities_maps[ $_POST['wpc_role'] ]['variable'] ) ) {

                $args = array(
                    'role'          => $_POST['wpc_role'],
                    'meta_key'      => 'wpc_individual_caps',
                    'meta_value'    => true,
                    'fields'        => 'ID',
                    'number'        => 1
                );
                $isset_individual_user = count( get_users( $args ) );

                $table_begin = ( $isset_individual_user ) ? '<table border="0" cellspacing="0" cellpadding="2" class="table_settings_capability">
                        <thead>
                            <tr><th style="width: 150px; padding-right: 20px;">' . __( 'Update Individual', WPC_CLIENT_TEXT_DOMAIN ) . WPC()->admin()->tooltip( sprintf( __( 'You have individual capabilities for %s. Set this checkbox if you want apply the setting for them too.', WPC_CLIENT_TEXT_DOMAIN ), $_POST['wpc_role'] ) ) .'</th><th>' . __( 'Capability Title', WPC_CLIENT_TEXT_DOMAIN )  . '</th></tr></thead><tbody>' : '<table border="0" cellspacing="0" cellpadding="0" class="table_settings_capability"><thead><tr><th>' . __( 'Capability Title', WPC_CLIENT_TEXT_DOMAIN )  . '</th></tr></thead><tbody>' ;
                $table_end = '</tbody></table>' ;

                $caps = $table_begin;

                $s_caps =  isset( $wpc_capabilities[ $_POST['wpc_role'] ] ) ? $wpc_capabilities[ $_POST['wpc_role'] ] : array();

                //$i = 0;
                foreach ( $capabilities_maps[ $_POST['wpc_role'] ]['variable'] as $cap_key => $cap_val ) {
                    if ( '' != $cap_key ) {
                        $checked = '';
                        if ( !isset( $s_caps[$cap_key] ) && true == $cap_val['cap'] ) {
                            $checked = 'checked';
                        } elseif ( isset( $s_caps[$cap_key] ) && 1 == $s_caps[$cap_key] ) {
                            $checked = 'checked';
                        }


                        $caps .= '<tr>' ;
                        if ( $isset_individual_user ) {
                            
                            $field_data = array(
                                'type' => 'checkbox_list',
                                'id' => $cap_key,
                                'name' => 'individual[' . $cap_key . ']',
                                'title' => __( 'Included Individual', WPC_CLIENT_TEXT_DOMAIN ),
                                'class' => 'check_for_individual',
                                'data-field_id' => $cap_key,
                            );

                            $caps .= '<td align="center">' . WPC()->settings()->render_setting_field( $field_data ) . '</td>';
                        }

                        $field_data = array(
                            'type' => 'checkbox_list',
                            'id' => $cap_key,
                            'name' => 'capabilities[' . $cap_key . ']',
                            'description' => $cap_val['label'],
                            'checked' => $checked,
                            'data-field_id' => $cap_key,
                        );

                        $caps .= '<td>' . WPC()->settings()->render_setting_field( $field_data ) . '</td>';
                        $caps .= '</tr>';
                    }
                }
            }
        }

        $caps .= $table_end;

        echo json_encode( array( 'caps' => $caps ) );
        exit;
    }

    /**
     * AJAX update assigned clients\cicles
     **/
    function update_assigned_data() {
        global $wpdb;

        if( isset($_POST['data_type']) && !empty($_POST['data_type']) && isset($_POST['current_page']) && !empty($_POST['current_page']) ) {
            $current_page = $_POST['current_page'];
            $datatype = $_POST['data_type'];
            do_action( 'wpc_assign_popup_update_additional_data', $_POST, array(
                'current_page' => $current_page,
                'data_type' => $datatype
            ) );

            switch( $current_page ) {
                case 'wpclient_clients':
                    switch($datatype) {
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $check_id_groups = array();
                                if( !empty( $_POST['data'] ) ) {
                                    $check_id_groups = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                /*our_hook_
                                hook_name: wpc_before_ajax_update_assigned_data_circle_clients
                                hook_title: Clients assign to Circle
                                hook_description: Hook runs when Clients assign to Circle.
                                hook_type: action
                                hook_in: wp-client
                                hook_location class.ajax.php
                                hook_param: int $client_id, array $circle_ids
                                hook_since: 4.1.6
                                */

                                do_action('wpc_before_ajax_update_assigned_data_client_circles', $id, $check_id_groups );

                                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                                    $all_id_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
                                } else {
                                    $all_id_groups = WPC()->groups()->get_group_ids();
                                }

                                //WPC()->groups()->assign( $all_id_groups, $id );

                                $delete_grous = implode( ',' , $all_id_groups );
                                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = %d AND group_id IN($delete_grous)", $id ) );

                                if ( count( $check_id_groups ) ) {
                                    $values = '';
                                    foreach( $check_id_groups as $id_group ) {
                                        $values .= "( '$id_group', '$id'  ),";
                                    }

                                    $values = substr( $values, 0, -1 );
                                    $wpdb->query( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients ( `group_id`, `client_id` ) VALUES $values" );
                                }

                                /*our_hook_
                                    hook_name: wpc_after_ajax_update_assigned_data_client_circles
                                    hook_title: Clients assign to Circle
                                    hook_description: Hook runs after when Clients assign to Circle.
                                    hook_type: action
                                    hook_in: wp-client
                                    hook_location class.ajax.php
                                    hook_param: int $client_id, array $circle_ids
                                    hook_since: 4.4.0
                                    */

                                do_action('wpc_after_ajax_update_assigned_data_client_circles', $id, $check_id_groups );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;

                        case 'wpc_managers':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];
                                $args = array(
                                    'role'      => 'wpc_manager',
                                    'fields'    => array( 'ID' ),
                                );

                                $managers = get_users( $args );
                                $manager_ids = array();
                                foreach( $managers as $manager ) {
                                    $manager_ids[] = $manager->ID;
                                }

                                if( !empty( $_POST['data'] ) ) {
                                    $check_id_managers = array_unique( explode( ',', $_POST['data'] ) );
                                } else {
                                    $check_id_managers = array();
                                }

                                WPC()->assigns()->delete_assign_data_by_assign( 'manager', 'client', $id );
                                if ( count( $check_id_managers ) ) {
                                    $values = '';
                                    foreach( $check_id_managers as $id_manager ) {
                                        $values .= "( 'manager', '$id_manager', 'client', '$id'  ),";
                                    }
                                    $values = substr( $values, 0, -1 );
                                    $wpdb->query( "INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns ( `object_type`, `object_id`, `assign_type`, `assign_id` ) VALUES $values" );
                                }
                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        //case: ''
                    }

                    break;

                case 'wpclients_client_pages':
                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'portal_page', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'portal_page', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                    }
                    break;

                case 'wpclients_files':
                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'file', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'file', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                    }
                    break;

                case 'wpclients_managers':
                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset( $_POST['data'] ) ) {
                                $id = $_POST['id'];
                                //assign process
                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'manager', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'manager', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }

                            break;
                    }
                    break;

                case 'wpclients_filescat':
                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'file_category', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'file_category', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                    }
                    break;

                case 'wpclientspage_categories':
                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'portal_page_category', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();
                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'portal_page_category', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => 'Completed' ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                    }
                    break;

                case 'wpclients_groups':
                    if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                        $id = $_POST['id'];
                        $where = '';
                        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                            $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'client' );
                            if( count( $manager_clients ) ) {
                                $where .= " AND client_id IN (" . implode( ',', $manager_clients ) . ")";
                            }
                        }
                        $excluded_clients  = WPC()->members()->get_excluded_clients();
                        if( count( $excluded_clients ) ) {
                            $where .= " AND client_id NOT IN(" . implode( ',', $excluded_clients ) . ")";
                        }

                        $data = array();
                        if( !empty( $_POST['data'] ) ) {
                            $data = array_unique( explode( ',', $_POST['data'] ) );
                            $data = array_diff( $data, $excluded_clients );
                        }

                        /*our_hook_
                            hook_name: wpc_before_ajax_update_assigned_data_circle_clients
                            hook_title: Clients assign to Circle
                            hook_description: Hook runs when Clients assign to Circle.
                            hook_type: action
                            hook_in: wp-client
                            hook_location class.ajax.php
                            hook_param: int $group_id, array $clients_id
                            hook_since: 4.1.6
                            */

                        do_action('wpc_before_ajax_update_assigned_data_circle_clients', $id, $data );
                        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d $where", $id ) );

                        foreach ( $data as $data_item ) {
                            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $id,  $data_item ) );
                        }

                        /*our_hook_
                            hook_name: wpc_after_ajax_update_assigned_data_circle_clients
                            hook_title: Clients assign to Circle
                            hook_description: Hook runs after when Clients assign to Circle.
                            hook_type: action
                            hook_in: wp-client
                            hook_location class.ajax.php
                            hook_param: int $group_id, array $clients_id
                            hook_since: 4.4.0
                            */

                        do_action('wpc_after_ajax_update_assigned_data_circle_clients', $id, $data );

                        echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                    } else {
                        echo json_encode( array( 'status' => false, 'message' => __( 'Empty ID or data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                    }
                    break;

                case 'wpc_client_portalhubs':
                    //check permission
                    if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) && ! current_user_can( 'edit_others_portalhubs' ) ) {
                        exit( json_encode( array( 'status' => false, 'message' => __( "Error of permissions.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                    }

                    switch($datatype) {
                        case 'wpc_clients':
                            if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) && isset( $_POST['data'] ) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'portalhub', $id, 'client', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode (array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                        case 'wpc_circles':
                            if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                $id = $_POST['id'];

                                $assign_data = array();

                                if( !empty( $_POST['data'] ) ) {
                                    $assign_data = array_unique( explode( ',', $_POST['data'] ) );
                                }

                                WPC()->assigns()->set_assigned_data( 'portalhub', $id, 'circle', $assign_data );

                                echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            } else {
                                echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                            }
                            break;
                    }
                    break;
            }
        } else {
            echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
        }
        exit;
    }


    /**
     * AJAX save template
     **/
    function ajax_save_enable_custom_redirects() {
        if ( isset( $_POST['wpc_enable_custom_redirects'] ) && in_array( $_POST['wpc_enable_custom_redirects'], array( 'yes', 'no' ) ) ) {
            update_option( 'wpc_enable_custom_redirects', $_POST['wpc_enable_custom_redirects'] );

            echo json_encode( array( 'status' => true, 'message' => __( 'Saved!', WPC_CLIENT_TEXT_DOMAIN ) ) );

        }
        exit;
    }


    /**
     * AJAX Function for testing smtp settings
     */
    function ajax_send_test_email() {

        check_ajax_referer( get_current_user_id() . SECURE_AUTH_SALT . "wpc_send_test_email", 'security' );

        $type = !empty( $_POST['feilds']['wpc_email_settings']['type'] ) ? $_POST['feilds']['wpc_email_settings']['type'] : 'Default WP';

        $subject = !empty( $_POST['feilds']['subject'] ) ? $_POST['feilds']['subject'] : sprintf( __( 'The test email from %s for %s Sending method', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], strtoupper( $type ) );

        if ( !empty( $_POST['feilds']['email'] ) && is_email( $_POST['feilds']['email'] ) ) {
            $to = $_POST['feilds']['email'];
        } else {
            echo json_encode( array( 'status' => false, 'message' => __( 'Wrong Test Email address!', WPC_CLIENT_TEXT_DOMAIN ) ) );
            exit;
        }

        $message = !empty( $_POST['feilds']['message'] ) ? $_POST['feilds']['message'] : sprintf( __( 'This is a test email. If you get it, then congratulations, your %s Sending method settings on %s are correct and should work normally. Save the settings in admin.', WPC_CLIENT_TEXT_DOMAIN ), strtoupper( $type ), WPC()->plugin['title'] );

        $key = ( isset( $_POST['key'] ) ) ? $_POST['key'] : '';

        $sent = WPC()->mailer()->send_test( $to, $subject, $message, '', '', $key );


        if ( true === $sent ) {
            echo json_encode( array( 'status' => true, 'message' => sprintf( __( 'This is an email testing the email settings in the %s Core plugin. Congratulations! If you are receiving this email, it means your email settings are correct and functioning properly. Please click "Update Settings".', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ) );
        } else {
            echo json_encode( array( 'status' => false, 'message' => json_encode( $sent ) ) );
        }

        exit;
    }


    /**
     * AJAX Function for testing smtp settings
     */
    function ajax_send_test_template_email() {

        check_ajax_referer( get_current_user_id() . SECURE_AUTH_SALT . "wpc_send_test_template_email", 'security' );

        if ( empty( $_POST['fields']['template_key'] ) ) {
            echo json_encode( array( 'status' => false, 'message' => __( 'Wrong Template Key!', WPC_CLIENT_TEXT_DOMAIN ) ) );
            exit;
        }

        if ( !empty( $_POST['fields']['email'] ) && is_email( $_POST['fields']['email'] ) ) {
            $to = $_POST['fields']['email'];
        } else {
            echo json_encode( array( 'status' => false, 'message' => __( 'Wrong Test Email address!', WPC_CLIENT_TEXT_DOMAIN ) ) );
            exit;
        }

        $template_key = $_POST['fields']['template_key'];

        if ( ! empty( $_POST['fields']['edit'] ) ) {
            $wpc_templates_emails   = WPC()->get_settings( 'templates_emails' );

            $subject = ! empty( $wpc_templates_emails[$template_key]['subject'] ) ? $wpc_templates_emails[$template_key]['subject'] : sprintf( __( 'The test email from %s for %s Template', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], $template_key );
            $message = ! empty( $wpc_templates_emails[$template_key]['body'] ) ? $wpc_templates_emails[$template_key]['body'] : sprintf( __( 'This is a test template\'s email. If you get it, then congratulations, your %s template on %s are correct and should work normally. Save the settings in admin.'), $template_key, WPC()->plugin['title'] );
        } else {
            $subject = !empty( $_POST['fields']['subject'] ) ? $_POST['fields']['subject'] : sprintf( __( 'The test email from %s for %s Template', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], $template_key );
            $message = !empty( $_POST['fields']['message'] ) ? $_POST['fields']['message'] : sprintf( __( 'This is a test template\'s email. If you get it, then congratulations, your %s template on %s are correct and should work normally. Save the settings in admin.'), $template_key, WPC()->plugin['title'] );
        }


        WPC()->mailer()->test_email_sender = true;
        $sent = WPC()->mailer()->send( $to, $subject, $message );

        if ( ob_get_length() ) {
            ob_end_clean();
        }

        if ( true === $sent ) {
            echo json_encode( array( 'status' => true, 'message' => sprintf( __( 'This is an email testing the email template in the %s Core plugin. Congratulations! If you are receiving this email, it means your template are correct and functioning properly. Please click "Update" template.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) ) );
        } else {
            echo json_encode( array( 'status' => false, 'message' => json_encode( $sent ) ) );
        }

        exit;
    }


    /**
     * AJAX Function for set portal page client
     */
    function ajax_set_portal_page_client() {
        if ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
            WPC()->setcookie( "wpc_preview_client", $_POST['id'], time() + 24*3600 );
            echo json_encode( array( 'status' => true ) );
        } else {
            echo json_encode( array( 'status' => false ) );
        }
        exit;
    }


    /**
     * AJAX Function for set portal page client
     */
    function ajax_customizer_get_sections() {
        if ( !isset( $_POST['wpc_scheme'] ) || empty( $_POST['wpc_scheme'] ) )
            die( '' );

        $wpc_customize = new WPC_Customize();

//            ob_start();

        $content = $wpc_customize->_get_sections( $_POST['wpc_scheme'] );


//                $content = ob_get_contents();

//            ob_end_clean();


        $sections_header_content = $wpc_customize->get_sections_header( $_POST['wpc_scheme'] );


//            $content = preg_replace( '~>\s+<~m', '><', $content );

        echo json_encode( array( 'status' => true, 'content' => $content, 'sections_header_content' => $sections_header_content ) );

        die();
    }


    /**
     * AJAX Function for save allowed gateways
     */
    function ajax_save_allow_gateways() {
        if ( isset( $_POST['name'] ) && isset( $_POST['enable'] ) ) {

            $wpc_gateways = WPC()->get_settings( 'gateways' );

            //see if there are checkboxes checked
            if ( '1' == $_POST['enable'] ) {
                if ( isset( $wpc_gateways['allowed'] ) ) {

                    $wpc_gateways['allowed'][] = $_POST['name'];
                    $wpc_gateways['allowed'] = array_unique( $wpc_gateways['allowed'] );

                }

            } else {
                if ( isset( $wpc_gateways['allowed'] ) ) {

                    $wpc_gateways['allowed'] = array_diff ( $wpc_gateways['allowed'], array( $_POST['name'] ) );

                }

            }

            WPC()->settings()->update( $wpc_gateways, 'gateways' );


        }

        die();
    }


    /**
     * AJAX Function get settings for gateways
     */
    function ajax_get_gateway_setting() {
        global $wpc_payments_core, $wpc_gateway_active_plugins;

        //load gateways just on settings page
        $wpc_payments_core->load_gateway_plugins();

        if( count( $wpc_gateway_active_plugins ) && isset( $_GET['plugin'] ) ) {
            $wpc_gateways = WPC()->get_settings( 'gateways' );

            foreach( $wpc_gateway_active_plugins as $plugin ) {
                if ( isset( $plugin->plugin_name ) && $plugin->plugin_name == $_GET['plugin'] ) {
                    $plugin->create_settings_form( $wpc_gateways );
                }
            }
        }

        die();
    }


    /**
     * AJAX Function dismiss admin notice
     */
    function ajax_dismiss_admin_notice() {
        if ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
            $wpc_dismiss_admin_notice = WPC()->get_settings( 'dismiss_admin_notice' );
            $wpc_dismiss_admin_notice[] = $_POST['id'];
            array_unique( $wpc_dismiss_admin_notice );

            WPC()->settings()->update( $wpc_dismiss_admin_notice, 'dismiss_admin_notice' );
        } else if( isset( $_POST['id'] ) && is_string( $_POST['id'] ) ) {
            if( is_array( explode( ',', $_POST['id'] ) ) ) {
                $wpc_dismiss_admin_notice = WPC()->get_settings('dismiss_admin_notice');

                if ( empty( $wpc_dismiss_admin_notice ) )
                    $wpc_dismiss_admin_notice = array();

                $wpc_dismiss_admin_notice = array_merge( $wpc_dismiss_admin_notice, explode( ',', $_POST['id'] ) );
                array_unique( $wpc_dismiss_admin_notice );

                WPC()->settings()->update( $wpc_dismiss_admin_notice, 'dismiss_admin_notice' );
            }
        }

        die();
    }


    /**
    *   PopUp for watching video on front page
    */
    function ajax_wpc_watch_video_in_popup() {
        if ( !empty( $_POST['id'] ) ) {
            $video_id = $_POST['id'];
        }
        if ( !empty( $_POST['video_src'] ) ) {
            $video_src = $_POST['video_src'];
        }
        if ( !empty( $_POST['video_title'] ) ) {
            $video_title = $_POST['video_title'];
        }
        ob_start(); ?>
        <video id="wpc-video-popup-watch" width="600" height="380" controls autoplay>
            <source src="<?php echo $video_src . '&id=' . $video_id; ?>" type="video/mp4">
        </video>
        <?php $content = ob_get_clean();

        echo json_encode( array(
            'title'     => $video_title,
            'content'   => $content,
        ) );

        exit;
    }


}

endif;

new WPC_Hooks_Ajax();