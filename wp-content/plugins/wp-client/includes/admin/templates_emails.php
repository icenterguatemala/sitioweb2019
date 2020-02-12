<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
        && !current_user_can( 'wpc_view_email_templates' ) && !current_user_can( 'wpc_edit_email_templates' ) ) {
    if ( current_user_can( 'wpc_view_shortcode_templates' ) || current_user_can( 'wpc_edit_shortcode_templates' ) )
        $adress = get_admin_url() . 'admin.php?page=wpclients_templates&tab=shortcodes';
    else
        $adress = get_admin_url( 'index.php' );

    WPC()->redirect( $adress );
}

if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
    $redirect = remove_query_arg( array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_templates&tab=emails';
}

$wpc_templates_emails   = WPC()->get_settings( 'templates_emails' );

//email when Client created
$wpc_emails_array['new_client_password'] = array(
    'tab_label'             => sprintf( __( 'New %s Created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( 'New %s Created by Admin', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'new_user client client_recipient'
);

//email when Client created
$wpc_emails_array['self_client_registration'] = array(
    'tab_label'             => sprintf( __( '%s Self-Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( 'New %s Registration', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'new_user client client_recipient'
);

//email when user convert to client
$wpc_emails_array['convert_to_client'] = array(
    'tab_label'             => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent when a user is converted to a WPC-%s role', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'convert_users client_recipient'
);

//email when user convert to staff
$wpc_emails_array['convert_to_staff'] = array(
    'tab_label'             => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'label'                 => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent when a user is converted to a WPC-%s role', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'convert_users staff_recipient'
);

//email when user convert to manager
$wpc_emails_array['convert_to_manager'] = array(
    'tab_label'             => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'label'                 => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent when a user is converted to a WPC-%s role', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'convert_users manager_recipient'
);

//email when user convert to admin
$wpc_emails_array['convert_to_admin'] = array(
    'tab_label'             => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'label'                 => sprintf( __( 'Convert User - %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent when a user is converted to a WPC-%s role', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'convert_users admin_recipient',
);

//verification email when Client created
$wpc_emails_array['new_client_verify_email'] = array(
    'tab_label'             => __( 'Verify Email', WPC_CLIENT_TEXT_DOMAIN ),
    'label'                 => __( "Client's Email verification", WPC_CLIENT_TEXT_DOMAIN ),
    'description'           => sprintf( __( '  >> This email will be sent to %s for verify email address', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'new_user client_recipient client'
);

//email when Client updated
$wpc_emails_array['client_updated'] = array(
    'tab_label'             => sprintf( __( '%s Password Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( '%s Password Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) ,
    'subject_description'   => '',
    'body_description'      => '',
    'tags'                  => 'client client_recipient',
);

//email when Client registered
$wpc_emails_array['new_client_registered'] = array(
    'tab_label'             => sprintf( __( 'New %s Registers', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( 'New %s registers using Self-Registration Form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to Admin after a new %s registers with client registration form', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{site_title}, {contact_name}, {user_name} and {approve_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user client admin_recipient'
);

//email when Client approved
$wpc_emails_array['account_is_approved'] = array(
    'tab_label'             => sprintf( __( '%s Account is approved', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( '%s Account is approved', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s after their account will approved (if "Send approval email" is checked).', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => __( '{site_title} and {contact_name} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'body_description'      => __( '{site_title}, {contact_name}, {user_name} and {login_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user client client_recipient'
);

//email when Staff created
$wpc_emails_array['staff_created'] = array(
    'tab_label'             => sprintf( __( '%s Created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'label'                 => sprintf( __( '%s Created by website Admin', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{contact_name}, {user_name}, {password} and {admin_url} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user staff staff_recipient'
);

//email when Client registered Staff
$wpc_emails_array['staff_registered'] = array(
    'tab_label'             => sprintf( __( '%s Registered', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'label'                 => sprintf( __( '%s Registered by %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'], WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s after %s registered him (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'], WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{contact_name}, {user_name}, {password} and {admin_url} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user staff staff_recipient'
);

//email to admin when Client registered Staff
$wpc_emails_array['staff_created_admin_notify'] = array(
    'tab_label'             => sprintf( __( 'Notify %s %s Registered', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->custom_titles['staff']['s'] ),
    'label'                 => sprintf( __( 'Notify %s %s Registered by %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->custom_titles['staff']['s'], WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s after %s registered %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{approve_url} and {admin_url} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user staff admin_recipient'
);

//email when Manager created
$wpc_emails_array['manager_created'] = array(
    'tab_label'             => sprintf( __( '%s Created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'label'                 => sprintf( __( '%s Created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{contact_name}, {user_name}, {password} and {admin_url} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user manager manager_recipient'
);

//email when Manager created
$wpc_emails_array['manager_updated'] = array(
    'tab_label'             => sprintf( __( '%s Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'label'                 => sprintf( __( '%s Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{contact_name}, {user_name}, {password} and {admin_url} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'manager manager_recipient'
);

//email when WPC Admin created
$wpc_emails_array['admin_created'] = array(
    'tab_label'             => sprintf( __( '%s Created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'label'                 => sprintf( __( '%s Created', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Password" is checked)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{contact_name}, {user_name}, {password} and {admin_url} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'new_user admin admin_recipient'
);

//email when Portal Page is updated
$wpc_emails_array['client_page_updated'] = array(
    'tab_label'             => sprintf( __( '%s Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
    'label'                 => sprintf( __( '%s Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s (if "Send Update to selected %s is checked") when %s updating', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['portal_page']['s'] ),
    'subject_description'   => __( '{contact_name}, {user_name}, {page_title} and {page_id} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'body_description'      => __( '{contact_name}, {page_title} and {page_id} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'portal_page client_recipient'
);

//email when Admin/Manager uploaded file
$wpc_emails_array['new_file_for_client_staff'] = array(
    'tab_label'             => __( 'Admin uploads new file', WPC_CLIENT_TEXT_DOMAIN ),
    'label'                 => sprintf( __( 'Admin uploads new file for %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s and his %s when Admin or %s uploads a new file for %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['p'], WPC()->custom_titles['manager']['s'], WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{site_title}, {file_name}, {file_category} and {login_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'file client_recipient'
);

//email to Admin and Managers when Client uploaded the file
$wpc_emails_array['client_uploaded_file'] = array(
    'tab_label'             => sprintf( __( '%s Uploads new file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( '%s Uploads new file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to Admin and %s when %s uploads file(s)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['s'], WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{user_name}, {site_title}, {file_name}, {file_category} and {admin_file_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'file manager_recipient admin_recipient'
);

//email to Admin and Managers when Client downloaded the file
$wpc_emails_array['client_downloaded_file'] = array(
    'tab_label'             => sprintf( __( '%s Downloaded File', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( '%s Downloaded File', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to Admin and %s when %s Download file', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['manager']['s'], WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{user_name}, {site_title}, {file_name} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'file manager_recipient admin_recipient'
);


//email when Admin send message to Client
$wpc_emails_array['notify_client_about_message'] = array(
    'tab_label'             => sprintf( __( 'Private Message To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( 'Private Message: Notify Message To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s when Admin/%s sent private message.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['manager']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{user_name}, {site_title}, {subject}, {message} and {login_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'private_message client_recipient'
);

//email when Client send message to CC
$wpc_emails_array['notify_cc_about_message'] = array(
    'tab_label'             => __( 'Private Message To CC Email', WPC_CLIENT_TEXT_DOMAIN ),
    'label'                 => __( 'Private Message: Notify Message To CC Email', WPC_CLIENT_TEXT_DOMAIN ),
    'description'           => sprintf( __( '  >> This email will be sent to CC Email when %s sent private message (if "Add CC Email for Private Messaging" is selected in plugin settings and %s added CC Email).', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{user_name}, {site_title}, {subject} and {message} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'private_message other'
);

//email when Client send message to Admin/Manager
$wpc_emails_array['notify_admin_about_message'] = array(
    'tab_label'             => sprintf( __( 'Private Message To %s/%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->custom_titles['manager']['s'] ),
    'label'                 => sprintf( __( 'Private Message: Notify Message To %s/%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->custom_titles['manager']['s'] ),
    'description'           => sprintf( __( '  >> This email will be sent to %s/%s when %s sent private message.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->custom_titles['manager']['s'], WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'] ),
    'subject_description'   => '',
    'body_description'      => __( '{user_name}, {site_title}, {subject}, {message} and {admin_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'private_message manager_recipient admin_recipient'
);

//email when Client reset it`s password
$wpc_emails_array['reset_password'] = array(
    'tab_label'             => sprintf( __( 'Reset %s Password', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( 'Reset %s Password', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( "  >> This email will be sent to %s when %s forgot it`s password and try to reset it.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{site_title}, {user_name} and {reset_address} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'user other'
);

//email when Client update profile
$wpc_emails_array['profile_updated'] = array(
    'tab_label'             => sprintf( __( '%s Profile Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'label'                 => sprintf( __( '%s Profile Updated', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'description'           => sprintf( __( "  >> This email will be sent to Admins when %s update own profile.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'subject_description'   => '',
    'body_description'      => __( '{site_title}, {admin_url}, {user_name}, {business_name} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'user client admin_recipient manager_recipient'
);

$wpc_emails_array['la_login_successful'] = array(
    'tab_label'             => __( 'Login Alert: Login Successful', WPC_CLIENT_TEXT_DOMAIN ),
    'label'                 => __( 'Login Alert: Login Successful', WPC_CLIENT_TEXT_DOMAIN ),
    'description'           => __( '  >> This email will be sent to selected email address when user login was successful', WPC_CLIENT_TEXT_DOMAIN ),
    'subject_description'   => __( '{user_name} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'body_description'      => __( '{ip_address} and {current_time} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'user other admin_recipient'
);

$wpc_emails_array['la_login_failed'] = array(
    'tab_label'             => __( 'Login Alert: Login Failed', WPC_CLIENT_TEXT_DOMAIN ),
    'label'                 => __( 'Login Alert: Login Failed', WPC_CLIENT_TEXT_DOMAIN ),
    'description'           => __( '  >> This email will be sent to selected email address when user login was failed', WPC_CLIENT_TEXT_DOMAIN ),
    'subject_description'   => __( '{la_user_name} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'body_description'      => __( '{la_user_name}, {la_status}, {ip_address} and {current_time} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
    'tags'                  => 'user other admin_recipient'
);

/*our_hook_
            hook_name: wpc_client_{$column_name}_custom_column_of_clients
            hook_title: Change default value of columns on Clients page
            hook_description: Hook runs before echo default value of columns on Clients page.
            hook_type: filter
            hook_in: wp-client
            hook_location clients.php
            hook_param: mixed $value
            hook_since: 4.3.1
            */
$wpc_emails_array = apply_filters( 'wpc_client_templates_emails_array', $wpc_emails_array );

foreach( $wpc_emails_array as $key => $values ) {
    $wpc_emails_array[$key]['key'] = $key;
    $wpc_emails_array[$key]['subject'] = $wpc_templates_emails[$key]['subject'];
    $wpc_emails_array[$key]['body'] = $wpc_templates_emails[$key]['body'];
    $wpc_emails_array[$key]['enable'] = isset( $wpc_templates_emails[$key]['enable'] ) ? $wpc_templates_emails[$key]['enable'] : true;
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Email_Templates_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $bulk_actions = array();
    var $columns = array();
    var $notification_tags = array();

    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        $this->no_items_message = $args['plural'] . ' ' . __(  'not found.', WPC_CLIENT_TEXT_DOMAIN );

        parent::__construct( $args );

    }

    function __call( $name, $arguments ) {
        return call_user_func_array( array( $this, $name ), $arguments );
    }

    function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = get_hidden_columns( $this->screen );
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {
        echo isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
    }

    function no_items() {
        echo $this->no_items_message;
    }


    /**
     * Generates content for a single row of the table
     *
     * @since 3.1.0
     * @access public
     *
     * @param object $item The current item
     */
    public function single_row( $item ) {
        $classes = '';
        if ( ! empty( $item['tags'] ) ) {
            $tags_array = explode( ' ', $item['tags'] );

            foreach ( $tags_array as $tag_key ) {
                $classes .= $tag_key . '_tag ';
            }
        }
        echo '<tr class="' . $classes . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }


    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach( $args as $k=>$val ) {
            if( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } else if( is_string( $k ) ) {
                $return_args[ $k ] = array( $val, $k == $this->default_sorting_field );
            } else {
                continue;
            }
        }
        $this->sortable_columns = $return_args;
        return $this;
    }

    function get_sortable_columns() {
        return $this->sortable_columns;
    }

    function set_columns( $args = array() ) {
        if( count( $this->bulk_actions ) ) {
            $args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
        }
        $this->columns = $args;
        return $this;
    }

    function get_columns() {
        return $this->columns;
    }

    function set_bulk_actions( $args = array() ) {
        $this->bulk_actions = $args;
        return $this;
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }


    function column_subject( $item ) {
        $content = '';
        if ( ! empty( $item['tags'] ) ) {
            $tags_array = explode( ' ', $item['tags'] );

            foreach ( $tags_array as $tag_key ) {
                $content .= '<div class="template_tag_table" data-tag="' . $tag_key . '">' . $this->notification_tags[$tag_key] . '</div>';
            }
        }

        return '<span style="float:left;clear:both;width:100%;" class="wpc_email_template_subject_column" data-slug="' . $item['key'] . '">' . $item['subject'] . '</span><div class="tags" style="float:right;clear:both;margin-top:7px;">' . $content . '</div>';
    }


    function column_tab_label( $item ) {

        $actions = array();

        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_edit_email_templates' ) ) {
            $actions['edit'] = '<a href="javascript:void(0);" class="ajax_popup edit_template_link" data-slug="' . $item['key'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }
        $actions['send_test'] = '<a href="javascript:void(0);" class="send_test_link" data-slug="' . $item['key'] . '">' . __( 'Send Test', WPC_CLIENT_TEXT_DOMAIN ). '</a>';

        if ( $item['enable'] != '0' ) {
            $actions['delete'] = '<a href="javascript:void(0);" class="wpc_templates_enable deactivate" data-slug="' . $item['key'] . '">' . __( 'Deactivate', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        } else {
            $actions['activate'] = '<a href="javascript:void(0);" class="wpc_templates_enable activate" data-slug="' . $item['key'] . '">' . __( 'Activate', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        return sprintf( '%1$s %2$s</div>', '<div class="template_icon dashicons ' . ( $item['enable'] != '0' ? 'dashicons-yes green' : 'dashicons-dismiss red' ) . '"
        title="' . ( $item['enable'] != '0' ? __( 'Active', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Inactive', WPC_CLIENT_TEXT_DOMAIN ) ) . '"
        data-title_active="' . __( 'Active', WPC_CLIENT_TEXT_DOMAIN ) . '" 
        data-title_inactive="' . __( 'Inactive', WPC_CLIENT_TEXT_DOMAIN ) . '"></div>
        <div style="float:left;width:calc( 100% - 40px );"><strong><a href="javascript:void(0);" class="ajax_popup edit_template_link" data-slug="' . $item['key'] . '">' . $item['tab_label'] .'</a></strong>', $this->row_actions( $actions ) );
    }


    function extra_tablenav( $which ) {
        if ( 'top' == $which ) { ?>
            <div class="alignleft actions">
                <?php foreach ( $this->notification_tags as $tag_key=>$title ) { ?>
                    <div class="template_tag <?php if ( empty( $tag_key ) ) { ?> active <?php } ?>" data-tag="<?php echo $tag_key ?>"><?php echo $title ?></div>
                <?php } ?>
            </div>
        <?php }
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

}

$ListTable = new WPC_Email_Templates_List_Table( array(
    'singular'  => __( 'Email Template', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Email Templates', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = 99999;
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array() );

$ListTable->set_bulk_actions(array(
/*    'activate'      => __( 'Activate', WPC_CLIENT_TEXT_DOMAIN ),
    'deactivate'    => __( 'Deactivate', WPC_CLIENT_TEXT_DOMAIN ),*/
));

$ListTable->set_columns(array(
    'tab_label'     => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
    'subject'       => __( 'Subject', WPC_CLIENT_TEXT_DOMAIN ),
//    'tags'       => __( 'Tags', WPC_CLIENT_TEXT_DOMAIN ),
));


$notification_tags = array(
    ''                          => __( 'All', WPC_CLIENT_TEXT_DOMAIN ),
    'convert_users'             => __( 'Convert Users', WPC_CLIENT_TEXT_DOMAIN ),
    'new_user'                  => __( 'New Users', WPC_CLIENT_TEXT_DOMAIN ),
    'user'                      => __( 'User', WPC_CLIENT_TEXT_DOMAIN ),
    'client'                    => WPC()->custom_titles['client']['p'],
    'staff'                     => WPC()->custom_titles['staff']['p'],
    'manager'                   => WPC()->custom_titles['manager']['p'],
    'admin'                     => WPC()->custom_titles['admin']['p'],
    'portal_page'               => WPC()->custom_titles['portal_page']['p'],
    'file'                      => __( 'Files', WPC_CLIENT_TEXT_DOMAIN ),
    'private_message'           => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
    'client_recipient'          => sprintf( __( '%s Recipient', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
    'manager_recipient'         => sprintf( __( '%s Recipient', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['manager']['s'] ),
    'admin_recipient'           => sprintf( __( '%s Recipient', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'] ),
    'staff_recipient'           => sprintf( __( '%s Recipient', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
    'other'                     => __( 'Other', WPC_CLIENT_TEXT_DOMAIN ),
);
$notification_tags = apply_filters( 'wpc_client_templates_emails_tags_array', $notification_tags );


if ( ! empty( $_GET['s'] ) ) {
    $wpc_emails_array = array_filter( $wpc_emails_array, function( $innerArray ) {
        $needle = $_GET['s'];

        if ( ! empty( $innerArray['tab_label'] ) ) {
            if ( strpos( strtolower( $innerArray['tab_label'] ), $needle ) !== false || strpos( strtolower( $innerArray['subject'] ), $needle ) !== false ) {
                return $innerArray;
            }
        }
    });

    $current_tags = array( '' => __( 'All', WPC_CLIENT_TEXT_DOMAIN ) );
    foreach ( $wpc_emails_array as $template ) {
        if ( ! empty( $template['tags'] ) ) {
            $tags = explode( ' ', $template['tags'] );
            foreach ( $tags as $tag ) {
                $current_tags[$tag] = $notification_tags[$tag];
            }
        }
    }

    $notification_tags = $current_tags;
}

$items_count = count( $wpc_emails_array );

$ListTable->prepare_items();
$ListTable->items = $wpc_emails_array;
$ListTable->notification_tags = $notification_tags;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<style type="text/css">

    input[type="text"]{
        width: 100% ! important;
    }

    .dashicons.green {
        color: darkgreen;
    }

    .dashicons.red {
        color: darkred;
        font-size:24px;
    }

    .template_icon {
        float:left;
        width:30px;
        line-height:30px;
        font-size:30px;
        margin-right:10px;
    }

    .column-subject {
        width:65%;
    }

    .column-tags {
        width:20%;
    }
</style>

<div style="display: none;">
<?php wp_editor( '',
    'wpc_template_body',
    array(
        'textarea_name' => 'wpc_template_body',
        'textarea_rows' => 15,
        'wpautop'       => false,
        'media_buttons' => false
    )
); ?>
</div>

<div class="icon32" id="icon-link-manager"></div>
<p><?php _e( 'From here you can edit the email templates and settings.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>

<form action="" method="get" name="wpc_email_templates_form" id="wpc_email_templates_form" style="width: 100%;">
    <input type="hidden" name="page" value="wpclients_templates" />
    <input type="hidden" name="tab" value="emails" />
    <?php $ListTable->search_box( __( 'Search Templates', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' ); ?>
    <?php $ListTable->display(); ?>
</form>

<div id="wpc_email_templates_edit_form" style="display: none; width: 100%;">
    <input id="wpc_template_key" type="hidden" name="wpc_template_key" value="" />
    <h3 id="wpc_template_title"></h3>
    <span id="wpc_template_description" class="description"></span>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <td colspan="2">
                    <label for="wpc_template_subject"><?php _e( 'Email Subject', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    <br>
                    <input id="wpc_template_subject" type="text" name="wpc_template_subject" value="" />
                    <span id="wpc_template_subject_description" class="description"></span>
                </td>
            </tr>
            <tr valign="top">
                <td colspan="2">
                    <label for="wpc_template_body"><?php _e( 'Email Body', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    <span id="wpc_template_body_description" class="description"></span>
                </td>
            </tr>
            <tr>
                <td align="left" style="width:30%;vertical-align: top;">
                    <input type="button" name="submit_template" class="button-primary submit_email" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                    <div id="ajax_result_submit_email" style="display: inline;"></div>
<!--                    <input type="button" name="reset_template" class="button" value="--><?php //_e( 'Reset to default', WPC_CLIENT_TEXT_DOMAIN ) ?><!--" />-->
                </td>
                <td valign="top" align="right" style="width:70%;vertical-align: top;">
                    <div style="float: right; width:70%;">
                        <a class="wpc_show_test_link" style="float: right;line-height: 28px;display: block;" href="javascript:void(0);"><b><< <?php _e( 'Send Test Email', WPC_CLIENT_TEXT_DOMAIN ); ?></b></a>
                        <div class="wpc_hide_block" style="display:none;">
                            <div style="position: relative;float:left;width:100%;margin: 0;padding:0;">
                                <input type="button" class="button wpc_cancel_test_email" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" style="float:right;" />
                                <input type="button" class="button wpc_send_test_email" value="<?php _e( 'Send Test Email', WPC_CLIENT_TEXT_DOMAIN ) ?>" style="float:right;" />
                                <label style="float:right;width:60%;margin: 0;padding:0;line-height: 28px;">
                                    <input type="text" name="email" class="test_email" value="" style="float: right;width:calc( 80% - 15px ) !important;" />
                                    <span style="float: right;width: 20%;margin-right:10px;text-align: right;"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ); echo WPC()->red_star(); ?></span>
                                </label>
                                <span class="wpc_ajax_loading" style="margin: 10px 0 0 7px;display: none;float: right;clear: both;"></span>
                                <span class="ajax_result" style="display: inline;width: 100%;text-align: right;padding-top: 10px;box-sizing: border-box;float:right;"></span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="wpc_email_templates_send_test" style="display: none; width: 100%;">
    <input id="wpc_test_template_key" type="hidden" name="wpc_template_key" value="" />
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <td colspan="2">
                <label for="wpc_test_email"><?php _e( 'Send test to Email', WPC_CLIENT_TEXT_DOMAIN ); echo WPC()->red_star(); ?> :</label>
                <br />
                <input id="wpc_test_email" type="text" name="email" class="test_email" value="" />
            </td>
        </tr>
        <tr valign="top">
            <td colspan="2">
                <label for="wpc_test_template_subject"><?php _e( 'Email Subject', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                <br>
                <input id="wpc_test_template_subject" type="text" readonly disabled value="" />
            </td>
        </tr>
        <tr valign="top">
            <td colspan="2">
                <label for="wpc_test_template_body"><?php _e( 'Email Body', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                <br>
                <textarea id="wpc_test_template_body" readonly disabled style="float:left;width:100%;" rows="8"></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="button" class="button button-primary wpc_send_test_email_popup" value="<?php _e( 'Send Test Email', WPC_CLIENT_TEXT_DOMAIN ) ?>" style="float:left;" />
                <span class="wpc_ajax_loading" style="margin: 7px 0 0 7px;display: none;float: left;"></span>
                <span class="ajax_result" style="display: inline;width: 80%;text-align: left;margin: 7px 0 0 7px;box-sizing: border-box;float:right;"></span>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {
        var hash_data = {};

        var $tiny_editor = {};
        var site_url = '<?php echo site_url();?>';

        var notifications_array = <?php echo json_encode( $wpc_emails_array ) ?>;

        jQuery('body').on('click', ".template_tag", function() {
            var tag = jQuery(this).data('tag');
            var disp_arr;
            if ( tag == '' ) {
                clear_hash();
                jQuery(".template_tag").removeClass('active');
                var tag_rows = jQuery( 'table.emailtemplates tbody tr' );
                tag_rows.show();

                disp_arr = jQuery( '.displaying-num' ).html().split(' ');
                disp_arr[0] = tag_rows.length;
                jQuery( '.displaying-num' ).html( disp_arr.join(' ') );
                jQuery( this ).toggleClass('active');
                return;
            }

            jQuery('.template_tag[data-tag=""]').removeClass('active');
            jQuery( this ).toggleClass('active');
            jQuery( 'table.emailtemplates tbody tr' ).hide();
            hash_data = {};

            if ( ! jQuery('.template_tag.active').length ) {
                jQuery('.template_tag[data-tag=""]').trigger('click');
                return;
            }

            jQuery('.template_tag.active').each( function(e) {
                var tag = jQuery(this).data('tag');
                var tag_rows;

                tag_rows = jQuery( 'table.emailtemplates tbody tr.' + tag + '_tag' );
                hash_data[tag] = 1;
                tag_rows.show();
            });

            window.location.hash = get_hash_string();

            disp_arr = jQuery( '.displaying-num' ).html().split(' ');
            disp_arr[0] = jQuery('table.emailtemplates tbody tr:visible').length;
            jQuery( '.displaying-num' ).html( disp_arr.join(' ') );
        });


        //click at tag in table
        jQuery(".template_tag_table").click( function() {
            var tag = jQuery(this).data('tag');

            if ( ! jQuery('.template_tag.active[data-tag="' + tag + '"]').length )
                jQuery('.template_tag[data-tag="' + tag + '"]').trigger('click');
        });

        jQuery(".wpc_templates_enable").click( function() {
            var obj = jQuery( this );
            if ( obj.hasClass('is_ajax_load') )
                return;

            var name = obj.data('slug');

            var value = '';
            if ( obj.hasClass('deactivate') ) {
                value    = jQuery.base64Encode( '0' );
                value    = value.replace(/\+/g, "-");
            } else {
                value    = jQuery.base64Encode( '1' );
                value    = value.replace(/\+/g, "-");
            }

            obj.addClass( 'is_ajax_load' );
            obj.parents('td').find('.template_icon').removeClass('dashicons-dismiss dashicons-yes').append('<span class="wpc_ajax_loading" style="margin: 10px 0 0 7px;float: left;"></span>')
            jQuery.ajax({
                type: "POST",
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: "action=wpc_save_template&wpc_templates[wpc_templates_emails][" + name + "][enable]=" + value,
                dataType: "json",
                success: function(data) {
                    obj.toggleClass('activate').toggleClass('deactivate');

                    var icon = obj.parents('td').find('.template_icon');
                    if ( obj.hasClass('deactivate') ) {
                        obj.parent().addClass('delete');
                        obj.html('<?php _e( 'Deactivate', WPC_CLIENT_TEXT_DOMAIN ) ?>');
                        icon.removeClass('dashicons-dismiss red').addClass('dashicons-yes green').attr('title',icon.data('title_active'));
                    } else {
                        obj.parent().removeClass('delete');
                        obj.html('<?php _e( 'Activate', WPC_CLIENT_TEXT_DOMAIN ) ?>');
                        icon.addClass('dashicons-dismiss red').removeClass('dashicons-yes green').attr('title',icon.data('title_inactive'));
                    }

                    obj.parents('td').find('.template_icon .wpc_ajax_loading').remove();
                    obj.removeClass( 'is_ajax_load' );
                }
            });
        });


        jQuery('.ajax_popup').each( function() {
            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '1050px',
                height          : '700px',
                type            : 'inline',
                href            : '#wpc_email_templates_edit_form',
                title           : '<?php echo esc_js( __( 'Edit Email Template', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                self_init       : false,
                inlineBeforeLoad : function() {

                },
                onClose: function() {
                    tinyMCE.triggerSave();
                    jQuery('#wp-wpc_template_body-wrap').remove();

                    jQuery('.wpc_tiny_placeholder').replaceWith( jQuery( $tiny_editor ).html() );
                    jQuery('.wpc_cancel_test_email').trigger('click');
                }
            });
        });


        jQuery('body').on( 'click', '.edit_template_link', function() {
            var obj = jQuery(this);
            obj.shutter_box('showPreLoader');

            jQuery.ajax({
                type: "POST",
                url: '<?php echo WPC()->get_ajax_url() ?>',
                data: {
                    action : 'get_email_template_data',
                    slug : obj.data('slug')
                },
                dataType: "json",
                success: function( data ) {
                    if ( data.status ) {
                        //data.template.enable
                        jQuery( '#wpc_template_subject' ).val( data.template.subject );
                        jQuery( '#wpc_template_key' ).val( obj.data('slug') );
                        jQuery( '#wpc_template_title' ).html( notifications_array[obj.data('slug')]['label'] );
                        jQuery( '#wpc_template_subject_description' ).html( notifications_array[obj.data('slug')]['subject_description'] );
                        jQuery( '#wpc_template_body_description' ).html( notifications_array[obj.data('slug')]['body_description'] );
                        jQuery( '#wpc_template_description' ).html( notifications_array[obj.data('slug')]['description'] );
                        obj.shutter_box('show');

                        var id = 'wpc_template_body';
                        var object = jQuery('#' + id);

                        if ( tinyMCE.get( id ) !== null ) {
                            tinyMCE.triggerSave();
                            tinyMCE.EditorManager.execCommand( 'mceRemoveEditor',true, id );
                            "4" === tinyMCE.majorVersion ? window.tinyMCE.execCommand("mceRemoveEditor", !0, id) : window.tinyMCE.execCommand("mceRemoveControl", !0, id);
                            $tiny_editor = jQuery('<div>').append( object.parents('#wp-' + id + '-wrap').clone() );
                            object.parents('#wp-' + id + '-wrap').replaceWith('<div class="wpc_tiny_placeholder"></div>');
                            jQuery( 'label[for="wpc_template_body"]' ).after( '<br />' + jQuery( $tiny_editor ).html() );

                            var init;
                            if( typeof tinyMCEPreInit.mceInit[ id ] == 'undefined' ){
                                init = tinyMCEPreInit.mceInit[ id ] = tinyMCE.extend( {}, tinyMCEPreInit.mceInit[ id ] );
                            } else {
                                init = tinyMCEPreInit.mceInit[ id ];
                            }
                            if ( typeof(QTags) == 'function' ) {
                                QTags( tinyMCEPreInit.qtInit[ id ] );
                                QTags._buttonsInit();
                            }
                            window.switchEditors.go( id );
                            tinyMCE.init( init );
                            tinyMCE.get( id ).setContent( data.template.body );
                            object.html( data.template.body );
                        } else {
                            $tiny_editor = jQuery('<div>').append( object.parents('#wp-' + id + '-wrap').clone() );
                            object.parents('#wp-' + id + '-wrap').replaceWith('<div class="wpc_tiny_placeholder"></div>');
                            jQuery( 'label[for="wpc_template_body"]' ).after( '<br />' + jQuery( $tiny_editor ).html() );


                            if ( typeof(QTags) == 'function' ) {
                                QTags( tinyMCEPreInit.qtInit[ id ] );
                                QTags._buttonsInit();
                            }

                            jQuery('#' + id).html( data.template.body );
                        }

                        jQuery( 'body' ).on( 'click','.wp-switch-editor', function() {
                            var target = jQuery(this);

                            if ( target.hasClass( 'wp-switch-editor' ) ) {
                                var mode = target.hasClass( 'switch-tmce' ) ? 'tmce' : 'html';
                                window.switchEditors.go( id, mode );
                            }
                        });

                        obj.shutter_box('resize');
                    } else {
                        obj.shutter_box('hidePreLoader');
                    }
                },
                error: function(data) {
                    obj.shutter_box( 'hidePreLoader' );
                }
            });
        });


        // Hide/Show Test Email
        jQuery('body').on( 'click', '.wpc_show_test_link', function() {
            var obj = jQuery(this);

            jQuery(this).hide( 10, function() {
                obj.parents('.form-table').find('.wpc_hide_block').show(10);
            });
        });


        jQuery('body').on( 'click', '.wpc_cancel_test_email', function() {
            var obj = jQuery(this);
            jQuery('.wpc_show_test_link').show( 10, function() {
                obj.parents('.form-table').find('.wpc_hide_block').hide(10);
            });
        });


        jQuery('body').on( 'click', '.wpc_send_test_email', function() {
            var obj = jQuery(this);
            if ( obj.parents('.form-table').find('.test_email').val() === '' ) {
                return false;
            }

            //get content from editor
            var content = '';
            if ( jQuery( '#wp-wpc_template_body-wrap' ).hasClass( 'tmce-active' ) ) {
                content = tinyMCE.get( 'wpc_template_body' ).getContent();
            } else {
                content = jQuery( '#wpc_template_body' ).val();
            }

            var data_feilds = {
                'email': obj.parents('.form-table').find('.test_email').val(),
                'subject': obj.parents('.form-table').find('#wpc_template_subject').val(),
                'message': content,
                'template_key': jQuery('#wpc_template_key').val()
            };

            obj.parents('.wpc_hide_block').find(".ajax_result").html('').show().css('display', 'inline').html('<span class="ajax_loading"></span>');
            obj.parents('.wpc_hide_block').find(".wpc_ajax_loading").show('fast');
            obj.prop('disabled',true);
            obj.parents('.form-table').find('.test_email').prop('disabled',true);

            jQuery('.sb_lightbox_content_body').animate({
                scrollTop: obj.parents('.wpc_hide_block').find(".wpc_ajax_loading").offset().top
            }, 2000);

            jQuery.ajax({
                type: "POST",
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data : {
                    action : 'wpc_send_test_template_email',
                    security: '<?php echo wp_create_nonce( get_current_user_id() . SECURE_AUTH_SALT . "wpc_send_test_template_email" ) ?>',
                    fields : data_feilds
                },
                dataType: "json",
                success: function(data) {
                    jQuery(".wpc_ajax_loading").hide('fast');
                    if(data.status) {
                        obj.parents('.wpc_hide_block').find(".ajax_result").css('color', 'green').html(data.message);
                    } else {
                        data.message = data.message.replace( /(\\r\\n)|(\\n\\r)|(\\n\\t)|(\\t)|(\\n)/g, '<br>' );
                        data.message = data.message.replace( /\\"/g, '"' );
                        data.message = data.message.replace( /\\\//g, '/' );

                        obj.parents('.wpc_hide_block').find(".ajax_result").css('color', 'red').html(data.message);
                    }

                    setTimeout(function() {
                        obj.parents('.wpc_hide_block').find(".ajax_result").fadeOut(1500);
                    }, 2500);

                    obj.prop('disabled',false);
                    obj.parents('.form-table').find('.test_email').prop('disabled',false);

                },
                error: function(data) {
                    jQuery(".wpc_ajax_loading").hide('fast');
                    obj.parents('.wpc_hide_block').find(".ajax_result").css( 'color', 'red' ).html( 'Unknown error' );
                    setTimeout( function() {
                        obj.parents('.wpc_hide_block').find( ".ajax_result" ).fadeOut(1500);
                    }, 2500);

                    obj.prop('disabled',false);
                    obj.parents('.form-table').find('.test_email').prop('disabled',false);

                }
            });

        });


        jQuery('.send_test_link').each( function() {
            jQuery(this).shutter_box({
                view_type       : 'lightbox',
                width           : '1050px',
                height          : '500px',
                type            : 'inline',
                href            : '#wpc_email_templates_send_test',
                title           : '<?php echo esc_js( __( 'Send Test Notification', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                self_init       : false
            });
        });


        jQuery('body').on( 'click', '.send_test_link', function() {
            var obj = jQuery(this);
            obj.shutter_box('showPreLoader');

            jQuery.ajax({
                type: "POST",
                url: '<?php echo WPC()->get_ajax_url() ?>',
                data: {
                    action : 'get_email_template_data',
                    slug : obj.data('slug')
                },
                dataType: "json",
                success: function( data ) {
                    if ( data.status ) {
                        //data.template.enable
                        jQuery( '#wpc_test_template_subject' ).val( data.template.subject );
                        jQuery( '#wpc_test_template_body' ).val( data.template.body );
                        jQuery( '#wpc_test_template_key' ).val( obj.data('slug') );

                        obj.shutter_box('show');
                        obj.shutter_box('resize');
                    } else {
                        obj.shutter_box('hidePreLoader');
                    }
                },
                error: function(data) {
                    obj.shutter_box( 'hidePreLoader' );
                }
            });
        });


        jQuery('body').on( 'click', '.wpc_send_test_email_popup', function() {
            var obj = jQuery(this);
            if ( jQuery('#wpc_test_email').val() === '' ) {
                return false;
            }

            var data_feilds = {
                'edit': true,
                'email': jQuery('#wpc_test_email').val(),
                'template_key': jQuery('#wpc_test_template_key').val()
            };

            obj.parents('td').find(".ajax_result").html('').show().css('display', 'inline');
            obj.parents('td').find(".wpc_ajax_loading").show();
            obj.prop('disabled',true);
            jQuery('#wpc_test_email').prop('disabled',true);

            jQuery.ajax({
                type: "POST",
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data : {
                    action : 'wpc_send_test_template_email',
                    security: '<?php echo wp_create_nonce( get_current_user_id() . SECURE_AUTH_SALT . "wpc_send_test_template_email" ) ?>',
                    fields : data_feilds
                },
                dataType: "json",
                success: function(data) {
                    jQuery(".wpc_ajax_loading").hide();
                    if ( data.status ) {
                        obj.parents('td').find(".ajax_result").css('color', 'green').html( data.message );
                    } else {
                        data.message = data.message.replace( /(\\r\\n)|(\\n\\r)|(\\n\\t)|(\\t)|(\\n)/g, '<br>' );
                        data.message = data.message.replace( /\\"/g, '"' );
                        data.message = data.message.replace( /\\\//g, '/' );

                        obj.parents('td').find(".ajax_result").css('color', 'red').html( data.message );
                    }

                    setTimeout(function() {
                        obj.parents('td').find(".ajax_result").fadeOut(1500);
                    }, 2500);

                    obj.prop('disabled',false);
                    jQuery('#wpc_test_email').prop('disabled',false);
                },
                error: function(data) {
                    jQuery(".wpc_ajax_loading").hide();
                    obj.parents('td').find(".ajax_result").css( 'color', 'red' ).html( 'Unknown error' );
                    setTimeout( function() {
                        obj.parents('td').find( ".ajax_result" ).fadeOut(1500);
                    }, 2500);

                    obj.prop('disabled',false);
                    jQuery('#wpc_test_email').prop('disabled',false);
                }
            });

        });


        jQuery('body').on( 'click', ".submit_email", function() {
            var name    = jQuery('#wpc_template_key').val();

            var subject = jQuery( '#wpc_template_subject' ).val();
            var crypt_subject    = jQuery.base64Encode( subject );
            crypt_subject        = crypt_subject.replace(/\+/g, "-");


            //get content from editor
            var content = '';
            if ( jQuery( '#wp-wpc_template_body-wrap' ).hasClass( 'tmce-active' ) ) {
                content = tinyMCE.get( 'wpc_template_body' ).getContent();
            } else {
                content = jQuery( '#wpc_template_body' ).val();
            }
            var crypt_content    = jQuery.base64Encode( content );
            crypt_content        = crypt_content.replace(/\+/g, "-").replace( /\//g, "*" );

            jQuery("#ajax_result_submit_email").html('').show().css('display', 'inline').html('<div class="wpc_ajax_loading"></div>');

            jQuery.ajax({
                type: "POST",
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: "action=wpc_save_template&wpc_templates[wpc_templates_emails][" + name + "][subject]=" + crypt_subject + "&wpc_templates[wpc_templates_emails][" + name + "][body]=" + crypt_content,
                dataType: "json",
                success: function(data){
                    if ( data.status ) {

                        jQuery('.wpc_email_template_subject_column[data-slug="' + name + '"]').html( subject );

                        jQuery("#ajax_result_submit_email").css('color', 'green').html( data.message );
                    } else {
                        jQuery("#ajax_result_submit_email").css('color', 'red').html( data.message );
                    }
                    setTimeout(function() {
                        jQuery( "#ajax_result_submit_email" ).fadeOut(1500);
                    }, 2500);
                },
                error: function(data) {
                    jQuery( "#ajax_result_submit_email" ).css('color', 'red').html('Unknown error.');
                    setTimeout( function() {
                        jQuery( "#ajax_result_submit_email" ).fadeOut(1500);
                    }, 2500);
                }
            });
        });


        /**
         * history events when back/forward and change window.location.hash handler
         */
        window.addEventListener("popstate", function(e) {
            hash_data = parse_hash();

            jQuery(".template_tag").removeClass('active');
            //jQuery( this ).toggleClass('active');
            jQuery( 'table.emailtemplates tbody tr' ).hide();

            var disp_arr;
            jQuery.each( hash_data, function( e ) {
                jQuery('.template_tag[data-tag="' + e + '"]').toggleClass('active');
                var tag_rows;

                tag_rows = jQuery( 'table.emailtemplates tbody tr.' + e + '_tag' );
                tag_rows.show();
            });

            disp_arr = jQuery( '.displaying-num' ).html().split(' ');
            disp_arr[0] = jQuery('table.emailtemplates tbody tr:visible').length;
            jQuery( '.displaying-num' ).html( disp_arr.join(' ') );
        });


        //at first page load set tags from hash
        hash_data = parse_hash();
        jQuery.each( hash_data, function( e ) {
            jQuery('.template_tag[data-tag="' + e + '"]').trigger('click');
        });


        /**
         * Build hash string, using global variable "hash_data"
         */
        function get_hash_string() {
            var hash_array = [];
            for( var index in hash_data ) {
                hash_array.push( index + '=' + hash_data[index] );
            }
            hash_string = hash_array.join('&');

            if ( hash_string == '' )
                return '';

            return '#' + hash_string;
        }


        /**
         * Parse URLs hash
         */
        function parse_hash() {
            var hash_obj = {};
            var hash = window.location.hash.substring( 1, window.location.hash.length );

            if ( hash == '' ) {
                return hash_obj;
            }

            var hash_array = hash.split('&');

            for ( var index in hash_array ) {
                var temp = hash_array[index].split('=');
                hash_obj[temp[0]] = temp[1];
            }

            return hash_obj;
        }


        /**
         * Clear hash for remove tags
         */
        function clear_hash() {
            hash_data = {};
            window.location.hash = get_hash_string();
        }
    });
</script>