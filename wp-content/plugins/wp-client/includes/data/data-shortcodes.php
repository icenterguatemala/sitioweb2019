<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;

$pp_categories = $wpdb->get_results(
    "SELECT cat_id as id, cat_name as name
    FROM {$wpdb->prefix}wpc_client_portal_page_categories", ARRAY_A
);
$pp_cats_values_array = array(
    'all' => __( 'All', WPC_CLIENT_TEXT_DOMAIN ),
    '0' => __( 'Without Category', WPC_CLIENT_TEXT_DOMAIN )
);
foreach( $pp_categories as $val ) {
    $pp_cats_values_array[ $val['id'] ] = $val['name'];
}

$categories = WPC()->files()->get_all_file_categories();

$file_categories = array( 'all' => __( 'All', WPC_CLIENT_TEXT_DOMAIN ) );
foreach( $categories as $key => $value ) {
    $tab_string = str_repeat( '&nbsp;', $value['depth'] );
    $file_categories[ $key ] = ( !empty( $tab_string ) ? $tab_string . '&mdash; ' : ' ' ) . $value['category_name'];
}

$wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
$custom_fields = $staff_directory_attributes = $cf_file = array();
foreach( $wpc_custom_fields as $key=>$val ) {
    $custom_fields[ $key ] = '{' . $key . '} - ' . ( isset( $val['title'] ) ? $val['title'] : '' );
    if( $val['type'] == 'file' ) {
        $cf_file[] = $key;
    }
    if ( 'staff' == $val['nature'] || 'both' == $val['nature'] ) {
        $staff_directory_attributes['show_' . $key] = array(
            'label'  => sprintf( __( 'Show %s Custom field', WPC_CLIENT_TEXT_DOMAIN ), $key ),
            'type'   => 'selectbox',
            'values' => array(
                'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                'no' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
            ),
            'value'  => 'yes'
        );
    }
}

$business_info_fields = WPC()->get_business_info_fields();

return apply_filters( 'wpc_shortcode_data_array', array(
    'wpc_client_portal_page' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_portal_page' )
    ),
    'wpc_client_edit_portal_page' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_edit_portal_page' )
    ),
    'wpc_client_staff_directory' => array(
        'title'         => __( 'Staff Directory', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_staff_directory' ),
        'categories'    => 'clients',
        'attributes'    => $staff_directory_attributes
    ),
    'wpc_client_add_staff_form' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_add_staff_form' )
    ),
    'wpc_client_edit_staff_form' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_add_staff_form' )
    ),
    'wpc_client_error_image' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_error_image' )
    ),
    'wpc_client_profile' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_client_profile' )
    ),
    'wpc_staff_profile' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_staff_profile' )
    ),
    'wpc_client_payment_process' => array(
        'callback'      => array( WPC()->shortcodes(), 'payment_process_func' )
    ),
    'wpc_client_theme' => array(
        'callback'      => array( WPC()->shortcodes(), 'shortcode_theme' )
    ),
    'wpc_client_avatar_preview' => array(
        'title'         => __( 'Avatar Preview', WPC_CLIENT_TEXT_DOMAIN ),
        'categories'    => 'clients',
        'attributes'    => array(
            'field' => array(
                'label' => __( 'Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => '128px'
            )
        ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_avatar_preview' )
    ),
    'wpc_client_user_info' => array(
        'title'         => __( 'Current user information', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_user_info' ),
        'categories'    => 'clients',
        'attributes'    => array(
            'field' => array(
                'label' => __( 'Field', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            )
        )
    ),
    'wpc_client' => array(
        'title'         => 'wpc_client',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpclients' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_client_only' => array(
        'title'         => 'Private Content only for Client',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_client_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for Client here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_staff_only' => array(
        'title'         => 'Private Content only for Staff',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_staff_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for Staff here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_client_and_staff_only' => array(
        'title'         => 'Private Content only for Client and Staff',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_client_and_staff_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for Client and Staff here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_admin_only' => array(
        'title'         => 'Private Content only for Admin',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_admin_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for Admin here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_manager_only' => array(
        'title'         => 'Private Content only for Manager',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_manager_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for Manager here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_non_logged_in_only' => array(
        'title'         => 'Private Content only for non-logged in users',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_non_logged_in_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for non-logged in users here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_logged_in_only' => array(
        'title'         => 'Private Content only for logged in users',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_logged_in_only' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'content'       => __( 'Enter protected text for logged in users here', WPC_CLIENT_TEXT_DOMAIN )
    ),
    'wpc_client_private' => array(
        'title'         => 'Private Content',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_private' ),
        'categories'    => 'other',
        'close_tag'     => true,
        'attributes'    => array(
            'for' => array(
                'label'  => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'type'   => 'assign_popup',
                'object' => 'client',
                'value'  => ''
            ),
            'for_circle' => array(
                'label'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                'type'    => 'assign_popup',
                'object'  => 'circle',
                'value'   => ''
            ),
            'privacy_type'              => array(
                'label'  => __( 'Rule Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'include' => __( 'Include', WPC_CLIENT_TEXT_DOMAIN ),
                    'exclude' => __( 'Exclude', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'include'
            ),
            'circle_condition'              => array(
                'label'  => __( 'Operator', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and' => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            )
        )
    ),
    'wpc_client_loginf' => array(
        'title'         => 'Login Form',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_loginf' ),
        'categories'    => 'pages',
        'attributes'    => array(
            'no_redirect'              => array(
                'label'  => __( 'Without Redirect', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'true' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'false' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'false'
            ),
            'no_redirect_text' => array(
                'label' => __( 'Text for not logged in users', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            )
        )
    ),
    'wpc_client_logoutb' => array(
        'title'         => __( 'Logout', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'logout_link',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_logoutb' ),
        'categories'    => 'other',
        'hub_template' => array(
            'text'    => __( 'Logout Link', WPC_CLIENT_TEXT_DOMAIN )
        ),
        'attributes'    => array(
            'text' => array(
                'label' => __( 'Text for logout link', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => '',
                'description' => __( 'Leave empty if you want to use string from translate file', WPC_CLIENT_TEXT_DOMAIN )
            )
        )
    ),
    'wpc_client_files_list' => array(
        'title'         => __( 'Files List', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'files_list',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_files_list' ),
        'categories'    => 'files',
        'attributes'    => array(
            'file_type' => array(
                'label'  => __( 'File Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'own'      => __( 'Own', WPC_CLIENT_TEXT_DOMAIN ),
                    'assigned' => __( 'Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                    'all'      => __( 'All available', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'all'
            ),
            'show_sort' => array(
                'label'  => __( 'Show sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'sort_type' => array(
                'label'  => __( 'Default Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                    'category'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'order_id'
            ),
            'sort' => array(
                'label'  => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'desc'
            ),
            'categories_sort_type' => array(
                'label'  => __( 'File Categories Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                ),
                'value'  => 'name'
            ),
            'categories_sort' => array(
                'label'  => __( 'File Categories Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'asc'
            ),
            'show_tags' => array(
                'label'  => __( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_description' => array(
                'label'  => __( 'Show Description', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_date' => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_size' => array(
                'label'  => __( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_author' => array(
                'label'  => __( 'Show Author', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'category' => array(
                'label'  => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'values' => $file_categories,
                'value'  => ''
            ),
            'with_subcategories' => array(
                'label'  => __( 'With Subcategories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_file_cats' => array(
                'label'  => __( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_last_download_date' => array(
                'label'  => __( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_thumbnails' => array(
                'label'  => __( 'Show Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_search' => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'filter_condition' => array(
                'label'  => __( 'Tags filter\'s condition', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_filters',
                'parent_value' => 'yes',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and'  => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            ),
            'show_pagination' => array(
                'label'  => __( 'Show Pagination', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination_by' => array(
                'label' => __( 'Pagination Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_pagination',
                'parent_value' => 'yes',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '5'
            ),
            'no_text' => array(
                'label' => __( 'Empty value text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'You don\'t have any files'
            )
        ),
    ),
    'wpc_client_files_table' => array(
        'title'         => __( 'Files Table', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'files_table',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_files_table' ),
        'categories'    => 'files',
        'attributes'    => array(
            'file_type' => array(
                'label'  => __( 'File Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'own'      => __( 'Own', WPC_CLIENT_TEXT_DOMAIN ),
                    'assigned' => __( 'Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                    'all'      => __( 'All available', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'all'
            ),
            'show_sort' => array(
                'label'  => __( 'Show sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'sort_type' => array(
                'label'  => __( 'Default Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                    'category'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'order_id'
            ),
            'sort' => array(
                'label'  => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'desc'
            ),
            'show_tags' => array(
                'label'  => __( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_description' => array(
                'label'  => __( 'Show Description', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_date' => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_size' => array(
                'label'  => __( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_author' => array(
                'label'  => __( 'Show Author', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'category' => array(
                'label'  => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'values' => $file_categories,
                'value'  => ''
            ),
            'with_subcategories' => array(
                'label'  => __( 'With Subcategories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_file_cats' => array(
                'label'  => __( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_last_download_date' => array(
                'label'  => __( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_thumbnails' => array(
                'label'  => __( 'Show Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_search' => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'filter_condition' => array(
                'label'  => __( 'Tags filter\'s condition', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_filters',
                'parent_value' => 'yes',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and'  => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            ),
            'show_bulk_actions' => array(
                'label'  => __( 'Show Bulk Actions', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination' => array(
                'label'  => __( 'Show Pagination', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination_by' => array(
                'label' => __( 'Pagination Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_pagination',
                'parent_value' => 'yes',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '5'
            ),
            'no_text' => array(
                'label' => __( 'Empty value text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'You don\'t have any files'
            )
        ),
    ),
    'wpc_client_files_blog' => array(
        'title'         => __( 'Files Blog', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'files_blog',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_files_blog' ),
        'categories'    => 'files',
        'attributes'    => array(
            'file_type' => array(
                'label'  => __( 'File Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'own'      => __( 'Own', WPC_CLIENT_TEXT_DOMAIN ),
                    'assigned' => __( 'Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                    'all'      => __( 'All available', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'all'
            ),
            'show_sort' => array(
                'label'  => __( 'Show sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'sort_type' => array(
                'label'  => __( 'Default Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                    'category'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'order_id'
            ),
            'sort' => array(
                'label'  => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'desc'
            ),
            'show_tags' => array(
                'label'  => __( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_description' => array(
                'label'  => __( 'Show Description', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_date' => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_size' => array(
                'label'  => __( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_author' => array(
                'label'  => __( 'Show Author', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'category' => array(
                'label'  => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'values' => $file_categories,
                'value'  => ''
            ),
            'with_subcategories' => array(
                'label'  => __( 'With Subcategories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_file_cats' => array(
                'label'  => __( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_last_download_date' => array(
                'label'  => __( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_thumbnails' => array(
                'label'  => __( 'Show Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_search' => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'filter_condition' => array(
                'label'  => __( 'Tags filter\'s condition', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_filters',
                'parent_value' => 'yes',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and'  => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            ),
            'show_pagination' => array(
                'label'  => __( 'Show Pagination', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination_by' => array(
                'label' => __( 'Pagination Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_pagination',
                'parent_value' => 'yes',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '5'
            ),
            'no_text' => array(
                'label' => __( 'Empty value text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'You don\'t have any files'
            )
        ),
    ),
    'wpc_client_files_tree' => array(
        'title'         => __( 'Files Tree', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'files_tree',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_files_tree' ),
        'categories'    => 'files',
        'attributes'    => array(
            'file_type' => array(
                'label'  => __( 'File Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'own'      => __( 'Own', WPC_CLIENT_TEXT_DOMAIN ),
                    'assigned' => __( 'Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                    'all'      => __( 'All available', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'all'
            ),
            'show_sort' => array(
                'label'  => __( 'Show sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'sort_type' => array(
                'label'  => __( 'Default Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                    'category'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'order_id'
            ),
            'sort' => array(
                'label'  => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'desc'
            ),
            'show_tags' => array(
                'label'  => __( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_date' => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_size' => array(
                'label'  => __( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_author' => array(
                'label'  => __( 'Show Author', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'category' => array(
                'label'  => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'values' => $file_categories,
                'value'  => ''
            ),
            'with_subcategories' => array(
                'label'  => __( 'With Subcategories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_empty_cats' => array(
                'label'  => __( 'Show Empty Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_description' => array(
                'label'  => __( 'Show Description', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_last_download_date' => array(
                'label'  => __( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_thumbnails' => array(
                'label'  => __( 'Show Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_search' => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'filter_condition' => array(
                'label'  => __( 'Tags filter\'s condition', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_filters',
                'parent_value' => 'yes',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and'  => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            ),
            'no_text' => array(
                'label' => __( 'Empty value text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'You don\'t have any files'
            )
        ),
    ),
    'wpc_client_filesla' => array(
        'title'         => __( 'Files Client Have Access To', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'files_access',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_filesla' ),
        'attributes'    => array(
            'view_type' => array(
                'label'  => __( 'View Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'list'  => __( 'List', WPC_CLIENT_TEXT_DOMAIN ),
                    'table' => __( 'Table', WPC_CLIENT_TEXT_DOMAIN ),
                    'tree'  => __( 'Tree', WPC_CLIENT_TEXT_DOMAIN ),
                    'blog'  => __( 'Blog', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'list'
            ),
            'show_sort' => array(
                'label'  => __( 'Show sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'sort_type' => array(
                'label'  => __( 'Default Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                    'category'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'order_id'
            ),
            'sort' => array(
                'label'  => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'desc'
            ),
            'categories_sort_type' => array(
                'label'  => __( 'File Categories Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'list',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                ),
                'value'  => 'name'
            ),
            'categories_sort' => array(
                'label'  => __( 'File Categories Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'list',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'asc'
            ),
            'show_tags' => array(
                'label'  => __( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_date' => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_size' => array(
                'label'  => __( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_author' => array(
                'label'  => __( 'Show Author', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'category' => array(
                'label'  => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'values' => $file_categories,
                'value'  => ''
            ),
            'with_subcategories' => array(
                'label'  => __( 'With Subcategories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_empty_cats' => array(
                'label'  => __( 'Show Empty Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'tree',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_file_cats' => array(
                'label'  => __( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => array( 'list', 'table', 'blog' ),
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_last_download_date' => array(
                'label'  => __( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_thumbnails' => array(
                'label'  => __( 'Show Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_search' => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'filter_condition' => array(
                'label'  => __( 'Tags filter\'s condition', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_filters',
                'parent_value' => 'yes',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and'  => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            ),
            'show_bulk_actions' => array(
                'label'  => __( 'Show Bulk Actions', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'table',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination' => array(
                'label'  => __( 'Show Pagination', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => array( 'list', 'table', 'blog' ),
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination_by' => array(
                'label' => __( 'Pagination Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_pagination',
                'parent_value' => 'yes',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '5'
            ),
            'exclude_author' => array(
                'label'  => __( 'Exclude Author', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
        )
    ),
    'wpc_client_fileslu' => array(
        'title'         => __( 'Files From Client', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'files_uploaded',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_fileslu' ),
        'attributes'    => array(
            'view_type' => array(
                'label'  => __( 'View Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'list'  => __( 'List', WPC_CLIENT_TEXT_DOMAIN ),
                    'table' => __( 'Table', WPC_CLIENT_TEXT_DOMAIN ),
                    'tree'  => __( 'Tree', WPC_CLIENT_TEXT_DOMAIN ),
                    'blog'  => __( 'Blog', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'list'
            ),
            'show_sort' => array(
                'label'  => __( 'Show sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'sort_type' => array(
                'label'  => __( 'Default Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'  => __( 'Date', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'order_id'
            ),
            'sort' => array(
                'label'  => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'desc'
            ),
            'categories_sort_type' => array(
                'label'  => __( 'File Categories Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'list',
                'values' => array(
                    'name' => __( 'Name', WPC_CLIENT_TEXT_DOMAIN ),
                    'order_id'  => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                ),
                'value'  => 'name'
            ),
            'categories_sort' => array(
                'label'  => __( 'File Categories Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'list',
                'values' => array(
                    'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc'  => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'asc'
            ),
            'show_tags' => array(
                'label'  => __( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_date' => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_size' => array(
                'label'  => __( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'category' => array(
                'label'  => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'values' => $file_categories,
                'value'  => ''
            ),
            'with_subcategories' => array(
                'label'  => __( 'With Subcategories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_empty_cats' => array(
                'label'  => __( 'Show Empty Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'tree',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_file_cats' => array(
                'label'  => __( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => array( 'list', 'table', 'blog' ),
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_last_download_date' => array(
                'label'  => __( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_thumbnails' => array(
                'label'  => __( 'Show Thumbnails', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_search' => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'filter_condition' => array(
                'label'  => __( 'Tags filter\'s condition', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_filters',
                'parent_value' => 'yes',
                'values' => array(
                    'or' => __( 'OR', WPC_CLIENT_TEXT_DOMAIN ),
                    'and'  => __( 'AND', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'or'
            ),
            'show_bulk_actions' => array(
                'label'  => __( 'Show Bulk Actions', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'table',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination' => array(
                'label'  => __( 'Show Pagination', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => array( 'list', 'table', 'blog' ),
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination_by' => array(
                'label' => __( 'Pagination Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_pagination',
                'parent_value' => 'yes',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '5'
            )
        )
    ),
    'wpc_client_uploadf' => array(
        'title'         => __( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'upload_files',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_uploadf' ),
        'categories'    => 'files',
        'hub_template' => array(
            'text'    => __( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN )
        ),
        'attributes'    => array(
            'show_categories' => array(
                'label'  => __( 'Show Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'all' => __( 'All', WPC_CLIENT_TEXT_DOMAIN ),
                    'assigned'  => __( 'Assigned To Client', WPC_CLIENT_TEXT_DOMAIN ),
                    'custom'  => __( 'Custom', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'all'
            ),
            'categories' => array(
                'label'  => __( 'Select Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'multiselect',
                'parent_name'  => 'show_categories',
                'parent_value' => 'custom',
                'values' => $file_categories,
                'value'  => ''
            ),
            'auto_upload' => array(
                'label'  => __( 'Auto-Upload After Select', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'include' => array(
                'label' => __( 'Include Filetypes', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            ),
            'exclude' => array(
                'label' => __( 'Exclude Filetypes', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            )
        )
    ),
    'wpc_client_com' => array(
        'title'         => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'private_messages',
        'callback'      => array( WPC()->shortcodes(), 'shortcode_private_messages' ),
        'categories'    => 'other',
        'hub_template' => array(
            'text'    => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN )
        ),
        'attributes'    => array(
            'redirect_after' => array(
                'label' => __( 'Redirect Link', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            ),
            'show_filters' => array(
                'label'  => __( 'Show Filters', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_number' => array(
                'label'  => __( 'Messages Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '25'  => '25',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '10'
            )
        )
    ),
    'wpc_client_graphic' => array(
        'title'         => __( 'Graphic', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_graphic' ),
        'categories'    => 'other'
    ),
    'wpc_client_pagel'     => array(
        'title'        => __( 'Pages you have access to', WPC_CLIENT_TEXT_DOMAIN ),
        'name'         => 'pages_access',
        'callback'     => array( WPC()->shortcodes(), 'shortcode_pagel' ),
        'categories'   => 'content',
        'hub_template' => array(
            'text'    => __( 'Pages you have access to', WPC_CLIENT_TEXT_DOMAIN ),
        ),
        'attributes'       => array(
            'view_type'              => array(
                'label'  => __( 'View Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'list' => __( 'List', WPC_CLIENT_TEXT_DOMAIN ),
                    'tree' => __( 'Tree', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'list'
            ),
            'categories'             => array(
                'label'       => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'type'        => 'multiselect',
                'description' => __( 'Please select Categories', WPC_CLIENT_TEXT_DOMAIN ),
                'values'      => $pp_cats_values_array,
                'value'       => 'all'
            ),
            'sort_type'              => array(
                'label'        => __( 'Default Sort Type', WPC_CLIENT_TEXT_DOMAIN ),
                'type'         => 'selectbox',
                'values'       => array(
                    ''         => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                    'date'     => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                    'title'    => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
                    'category_name' => __( 'Category Title', WPC_CLIENT_TEXT_DOMAIN ),
                )
            ),
            'sort'             => array(
                'label'        => __( 'Default Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'         => 'selectbox',
                'values'       => array(
                    'asc'  => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                    'desc' => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'        => 'desc'
            ),
            'show_categories_titles' => array(
                'label'        => __( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ),
                'type'         => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => 'list',
                'values'       => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                )
            ),
            'show_featured_image' => array(
                'label'  => __( 'Show Featured Image', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_search'        => array(
                'label'  => __( 'Show Search', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                )
            ),
            'show_date'              => array(
                'label'  => __( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                )
            ),
            'show_current_page'      => array(
                'label'  => __( 'Show Current Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'no'
            ),
            'show_sort'              => array(
                'label'        => __( 'Show Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'type'         => 'selectbox',
                'values'       => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'        => 'no'
            ),
            'show_pagination' => array(
                'label'  => __( 'Show Pagination', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'view_type',
                'parent_value' => array( 'list' ),
                'values' => array(
                    'yes' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'no'  => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'yes'
            ),
            'show_pagination_by' => array(
                'label' => __( 'Pagination Per Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'parent_name'  => 'show_pagination',
                'parent_value' => 'yes',
                'values' => array(
                    '5'   => '5',
                    '10'  => '10',
                    '20'  => '20',
                    '30'  => '30',
                    '50'  => '50'
                ),
                'value'  => '5'
            ),
            'no_text' => array(
                'label' => __( 'Empty value text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'You don\'t have any pages'
            )
        )
    ),
    'wpc_client_registration_form' => array(
        'title'         => __( 'Client Registration', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_client_registration_form' ),
        'categories'    => 'pages',
        'attributes'    => array(
            'no_redirect'              => array(
                'label'  => __( 'Without Redirect', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'true' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                    'false' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'false'
            ),
            'no_redirect_text' => array(
                'label' => __( 'Text after user registration', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            )
        )
    ),
    'wpc_client_business_info' => array(
        'title'         => __( 'Business Info', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( $this, 'shortcode_business_info' ),
        'categories'    => 'other',
        'attributes'    => array(
            'field'     => array(
                'label'  => __( 'Field', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => $business_info_fields,
            ),
        )
    ),
    'wpc_client_errors' => array(
        'title'         => __( 'Errors', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_errors' ),
        'categories'    => 'other'
    ),
    'wpc_client_registration_successful' => array(
        'title'         => __( 'Successful Client Registration', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_registration_successful' ),
        'categories'    => 'pages'
    ),
    'wpc_client_business_name' => array(
        'title'         => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_business_name' ),
        'categories'    => 'clients'
    ),
    'wpc_client_contact_name' => array(
        'title'         => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_contact_name' ),
        'categories'    => 'clients'
    ),
    'wpc_client_get_page_link' => array(
        'title'         => __( 'Page Url', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_get_page_link' ),
        'categories'    => 'other',
        'attributes'    => array(
            'page'      => array(
                'label'  => __( 'Page', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => apply_filters('wpc_pages_for_get_page_link_shortcode', array(
                    'hub' => __( 'Hub Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'login' => __( 'Login Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'client_registration' => __( 'Registration Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'staff_directory' => sprintf( __( '%s Directory', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] ),
                    'add_staff' => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] ),
                )),
                'value'  => 'false'
            ),
            'text' => array(
                'label' => __( 'Link Text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'Some Link'
            ),
            'id' => array(
                'label' => __( 'Link id attribute', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            ),
            'class' => array(
                'label' => __( 'Link class attribute', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            ),
            'style' => array(
                'label' => __( 'Link style attribute', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ''
            )
        )
    ),
    'wpc_redirect_on_login_hub' => array(
        'title'         => __( 'Redirect on Login or HUB', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_redirect_on_login_hub' ),
        'categories'    => 'other'
    ),
    'wpc_client_client_managers' => array(
        'title'         => __( 'Client Managers', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_client_managers' ),
        'categories'    => 'clients'
    ),
    'wpc_client_custom_field' => array(
        'title'         => __( 'Custom Field', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_custom_field' ),
        'categories'    => 'clients',
        'attributes'    => array(
            'name'      => array(
                'label'  => __( 'Field', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => $custom_fields,
                'value'  => 'false'
            )
        )
    ),
    'wpc_client_custom_field_value' => array(
        'title'         => __( 'Custom Field Value', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_custom_field_value' ),
        'categories'    => 'clients',
        'attributes'    => array(
            'name'      => array(
                'label'  => __( 'Field', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => $custom_fields,
                'value'  => 'false'
            ),
            'only_link' => array(
                'label' => __( 'Show', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    '1' => __( 'View Link', WPC_CLIENT_TEXT_DOMAIN ),
                    '2' => __( 'Image', WPC_CLIENT_TEXT_DOMAIN ),
                    '' => __( 'Download Link', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => '',
                'parent_name'  => 'name',
                'parent_value' => $cf_file,
            ),
            'delimiter' => array(
                'label' => __( 'Delimiter', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => ','
            ),
            'no_value' => array(
                'label' => __( 'No Value', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'None'
            )
        )
    ),
    'wpc_client_user_activity_alert' => array(
        'title'         => __( 'Hub Activity', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_user_activity_alert' ),
        'categories'    => 'other',
        'attributes'    => array(
            'title' => array(
                'label' => __( 'Text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'Hub Activity'
            ),
            'text_advise' => array(
                'label' => __( 'Text', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => 'Please advise, you have:'
            ),
            'position'      => array(
                'label'  => __( 'Position', WPC_CLIENT_TEXT_DOMAIN ),
                'type'   => 'selectbox',
                'values' => array(
                    'bottom right' => __( 'Bottom Right', WPC_CLIENT_TEXT_DOMAIN ),
                    'bottom left' => __( 'Bottom Left', WPC_CLIENT_TEXT_DOMAIN ),
                    'top right' => __( 'Top Right', WPC_CLIENT_TEXT_DOMAIN ),
                    'top left' => __( 'Top Left', WPC_CLIENT_TEXT_DOMAIN )
                ),
                'value'  => 'bottom right'
            ),
            'offset_x' => array(
                'label' => __( 'Offset X', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => '0'
            ),
            'offset_y' => array(
                'label' => __( 'Offset Y', WPC_CLIENT_TEXT_DOMAIN ),
                'type'  => 'text',
                'value' => '0'
            )
        )
    ),
    'wpc_client_payments_history'  =>  array(
        'title'         => __( 'Client Payments History', WPC_CLIENT_TEXT_DOMAIN ),
        'callback'      => array( WPC()->shortcodes(), 'shortcode_wpc_payments_history' ),
        'categories'    => 'content',
    ),
) );