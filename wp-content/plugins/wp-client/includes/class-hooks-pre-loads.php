<?php

// Exit if executed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPC_Hooks_Pre_Loads' ) ) :

class WPC_Hooks_Pre_Loads {

    /**
     * WPC_Hooks_Common Constructor.
     */
    public function __construct() {

        if ( get_option( 'permalink_structure' ) )
            WPC()->permalinks = true;

        //set plugin data
        $this->_set_plugin_data();

        add_action( 'plugins_loaded', array( &$this, 'wpc_client_loaded' ), -10 );
        add_action( 'init', array( &$this, 'wpc_client_pre_init' ), -100 );
        add_action( 'init', array( &$this, 'wpc_client_init' ), -10 );


        if ( WPC()->is_request( 'admin' ) ) {
            add_action( 'wpc_client_pre_init', array( &$this, '_check_updates' ), -9 );
        } elseif ( WPC()->is_request( 'ajax' ) ) {
            add_action( 'wp_ajax_wpc_updater', array( WPC()->hooks(), 'WPC_Update->remote_update' ) );
        }

        add_action( 'wpc_client_pre_init', array( &$this, 'init_includes' ), -8 );

        //init custom titles
        add_action( 'wpc_client_pre_init', array( &$this, 'set_custom_titles' ), -7  );

        add_action( 'init', array( &$this, '_create_post_type' ), 2 );

        //add query vars
        add_filter( 'query_vars', array( &$this, '_insert_query_vars' ) );

        //get tplt data
//        add_filter( 'option_WP-Client_license_status', array( WPC()->hooks(), 'WPC_3P_Compatibility->wptplt_data' ), 99999, 2 );
    }


    function _check_updates() {

        if ( current_user_can( 'manage_options' ) ) {

            //updates from queue - only once
            WPC()->update()->maybe_install();

            //updates from queue - only once
            WPC()->update()->maybe_update_our_plugins();

            //check maybe crashed
            WPC()->update()->maybe_crashed();

        }
    }


    /*
    * WP-Client loaded and now extensions can be load
    */
    static function wpc_client_loaded() {
        /*our_hook_
        hook_name: wpc_client_loaded
        hook_title: When WP-Client Core is loaded
        hook_description:
        hook_type: action
        hook_in: wp-client
        hook_location class-hooks-pre-loads.php
        hook_param:
        hook_since: 4.5.0
        */
        do_action( 'wpc_client_loaded' );
    }

    /*
    * WP-Client init
    */
    static function wpc_client_pre_init() {

        // retrieve our license key from the DB
        $license_key = trim( get_option( 'wp-client_license_key' ) );

        // setup the updater
        new WPC_License( WPC_STORE_URL, WPC_PLUGIN_FILE, array(
            'version'   => WPC_CLIENT_VER,
            'item_name' => 'WP-Client',
            'menu_slug' => 'wp-client-license',
            'menu_title' => 'WP-Client',
            'license'   => $license_key,
        ) );

        /*our_hook_
        hook_name: wpc_client_pre_init
        hook_title: WP-Client Pre Init
        hook_description:
        hook_type: action
        hook_in: wp-client
        hook_location class-hooks-pre-loads.php
        hook_param:
        hook_since: 4.5.0
        */
        do_action( 'wpc_client_pre_init' );

    }


    /*
    * WP-Client init
    */
    static function wpc_client_init() {
        /*our_hook_
        hook_name: wpc_client_init
        hook_title: WP-Client init
        hook_description:
        hook_type: action
        hook_in: wp-client
        hook_location class-hooks-pre-loads.php
        hook_param:
        hook_since: 4.5.0
        */
        do_action( 'wpc_client_init' );

    }


    /**
     * Include required core files used in admin and on the frontend.
     */
    public function init_includes() {

        require_once( WPC()->plugin_dir . 'includes/class-wpc-enqueue.php' );

        if ( WPC()->update()->is_crashed( 'core' ) || ! WPC()->is_licensed( 'WP-Client' ) ) {
            if ( WPC()->is_request( 'admin' ) ) {
                require_once( WPC()->plugin_dir . 'includes/class-hooks-admin.php' );
                require_once( WPC()->plugin_dir . 'includes/class-hooks-admin-menu.php' );
            }
            return '';
        }

        require_once( WPC()->plugin_dir . 'includes/class-hooks-common.php' );

        /////////// Add widget login/logout ///////////////
        require_once WPC()->plugin_dir . 'includes/widget.php';

        /////////// Add widget Portal Page list ///////////////
        require_once WPC()->plugin_dir . 'includes/widget_pp.php';

        if ( WPC()->is_request( 'ajax' ) ) {
            require_once( WPC()->plugin_dir . 'includes/class-gdpr.php' );
            require_once( WPC()->plugin_dir . 'includes/class-hooks-ajax.php' );

        } elseif ( WPC()->is_request( 'admin' ) ) {
            require_once( WPC()->plugin_dir . 'includes/class-gdpr.php' );
            require_once( WPC()->plugin_dir . 'includes/class-hooks-admin.php' );
            require_once( WPC()->plugin_dir . 'includes/class-hooks-admin-menu.php' );
            require_once( WPC()->plugin_dir . 'includes/class-hooks-admin-meta-boxes.php' );

        } elseif ( WPC()->is_request( 'frontend' ) ) {
            require_once( WPC()->plugin_dir . 'includes/class-hooks-front-end.php' );
        }

        //run hooks for WPC_Hooks_3P_Compatibility
        require_once( WPC()->plugin_dir . 'includes/class-hooks-3p-compatibility.php' );

    }

    /*
    * Register our post types
    */
    static function _create_post_type() {

        register_taxonomy( 'wpc_file_tags', 'file' );
        register_taxonomy( 'wpc_tags', null );

        $wpc_general = WPC()->get_settings( 'general' );
        $wpc_pages = WPC()->get_settings( 'pages' );

        //to do create option for Portal Page Base
        $portal_page_base = ! empty( $wpc_pages['portal_page_slug'] ) ? $wpc_pages['portal_page_slug'] : 'portal/portal-page';

        $exclude_from_search = true;
        if( isset( $wpc_general['exclude_pp_from_search'] ) && $wpc_general['exclude_pp_from_search'] == 'no' ) {
            $exclude_from_search = false;
        }

        $post_types = array(
            'clientspage' => array(
                'labels'                => array(
                    'name'                  => WPC()->custom_titles['portal_page']['p'],
                    'singular_name'         => WPC()->custom_titles['portal_page']['s'],
                    'edit_item'             => sprintf( __('Edit %s Item', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                    'view_item'             => sprintf( __('View %s Item', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                    'search_items'          => sprintf( __('Search %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                    'not_found'             => __('Nothing found', WPC_CLIENT_TEXT_DOMAIN ),
                    'not_found_in_archive'    => __('Nothing found in Archive', WPC_CLIENT_TEXT_DOMAIN ),
                    'parent_item_colon'     => ''
                ),
                'show_in_rest'          => true,
                'public'                => true,
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'query_var'             => true,
                'show_in_menu'          => false,
                'show_in_admin_bar'     => false,
                'capability_type'       => 'clientspage',
                'map_meta_cap'          => true,
                'hierarchical'          => true,
                'exclude_from_search'   => $exclude_from_search,
                'menu_position'         => 145,
                'supports'              => array('title', 'editor', 'thumbnail', 'meta', 'revisions'),
                'rewrite'               => array( 'slug' => $portal_page_base, 'with_front' => false, 'pages' => false, ),
            ),

            'portalhub' => array(
                'labels'                => array(
                    'name'                  => _x('HUB Pages', 'post type general name', WPC_CLIENT_TEXT_DOMAIN ),
                    'singular_name'         => _x('HUB Page', 'post type singular name', WPC_CLIENT_TEXT_DOMAIN ),
                    'add_new_item'          => __('Add New HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'new_item'              => __('New HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'edit_item'             => __('Edit HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'view_item'             => __('View HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'insert_into_item'      => __('Insert to HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'uploaded_to_this_item' => __('Uploaded to this HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'search_items'          => __('Search HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'not_found'             => __('Nothing found', WPC_CLIENT_TEXT_DOMAIN ),
                    'not_found_in_archive'  => __('Nothing found in Archive', WPC_CLIENT_TEXT_DOMAIN ),
                    'parent_item_colon'     => '',
                ),
                'show_in_rest'          => true,
                'public'                => true,
                'publicly_queryable'    => true, //fix for show other HUB pages like /hub-page/alphatest2/
                'show_ui'               => true,
                'query_var'             => true,
                'show_in_menu'          => false,
                'show_in_admin_bar'     => false,
                'capability_type'       => 'portalhub',
                'map_meta_cap'          => true,
                'hierarchical'          => true,
                'exclude_from_search'   => true,
                'supports'              => array( 'title', 'editor', 'thumbnail', 'meta', 'revisions' ),
                'rewrite'               => array( 'slug' => '', 'with_front' => false, 'pages' => false ),
            ),

        );

        /*our_hook_
        hook_name: wpc_client_add_post_types
        hook_title: Add our custom post types
        hook_description: Hook runs before register_post_type and get list of our post types.
        hook_type: filter
        hook_in: wp-client
        hook_location class-hooks-common.php
        hook_param: array $post_types
        hook_since: 4.5.0
        */
        $post_types = apply_filters( 'wpc_client_add_post_types', $post_types );

        if ( is_array( $post_types ) ) {
            foreach( $post_types as $key => $value ) {
                register_post_type( $key, $value );
            }
        }

    }

    /**
     * Adding query vars for HUB page
     *
     * @param array $vars
     *
     * @return array
     */
    function _insert_query_vars( $vars ) {

        array_push( $vars, 'wpc_page' );
        array_push( $vars, 'wpc_page_value' );
        array_push( $vars, 'wpc_google_hash' );
        array_push( $vars, 'ext' );

        if ( defined( 'WPC_CLIENT_PAYMENTS' ) ) {
            array_push( $vars, 'wpc_order_id' );
        }

        return $vars;
    }



    /**
     * Set plugin data
     *
     * @return void
     */
    static function _set_plugin_data() {
        global $wp_version;

        WPC()->flags['easy_mode'] = false;
        $general_settings = WPC()->get_settings( 'general' );
        if ( isset( $general_settings['easy_mode'] ) && 'yes' == $general_settings['easy_mode'] ) {
            WPC()->flags['easy_mode'] = true;
        }


        if ( version_compare( $wp_version, '3.8', '>=' ) ) {
            WPC()->plugin['logo_style'] = ".wpc_logo {
                    background: url( '" . WPC()->plugin_url . "images/page_header_gray.png' ) no-repeat transparent;
                    width: 625px;
                    height: 60px;
                }";
        } else {
            WPC()->plugin['logo_style'] = ".wpc_logo {
                    background: url( '" . WPC()->plugin_url . "images/page_header.png' ) no-repeat transparent;
                    width: 625px;
                    height: 60px;
                }";
        }
        //default values
        WPC()->plugin['title'] = 'WP-Client';
        WPC()->plugin['old_title'] = WPC()->plugin['title'];
        WPC()->plugin['logo_content'] = '';

        WPC()->plugin['icon_url'] = WPC()->plugin_url . 'client-icon.png';
        WPC()->plugin['hide_about_tab'] = 0;
        WPC()->plugin['hide_help_menu'] = 0;
        WPC()->plugin['hide_extensions_menu'] = 0;
        WPC()->plugin['hide_system_status_tab'] = 0;
        WPC()->plugin['hide_get_started_tab'] = 0;
        WPC()->plugin['hide_licenses_tab'] = 0;


        //get custom values
        $new_plugin_info = get_option( 'whtlwpc_settings' );

        //set title
        if ( isset( $new_plugin_info['title'] ) && '' != trim( $new_plugin_info['title'] ) ) {
            WPC()->plugin['title'] = stripslashes( trim( $new_plugin_info['title'] ) ) ;
        }

        //set description
        if ( isset( $new_plugin_info['description'] ) ) {
            WPC()->plugin['description'] = stripslashes( trim( $new_plugin_info['description'] ) );
        } else {
            WPC()->plugin['description'] = 'Client Portal is a Client Management Plugin that gives you the ultimate in flexibility. Integrate powerful client management and relations features into your current site.';
        }

        //set author
        if ( isset( $new_plugin_info['author'] ) ) {
            WPC()->plugin['author'] = stripslashes( trim( $new_plugin_info['author'] ) );
        } else {
            WPC()->plugin['author'] = '';
        }

        //set author URI
        if ( isset( $new_plugin_info['author_uri'] ) ) {
            WPC()->plugin['author_uri'] = stripslashes( trim( $new_plugin_info['author_uri'] ) );
        } else {
            WPC()->plugin['author_uri'] = '';
        }

        //set plugin URI
        if ( isset( $new_plugin_info['plugin_uri'] ) ) {
            WPC()->plugin['plugin_uri'] = stripslashes( trim( $new_plugin_info['plugin_uri'] ) );
        } else {
            WPC()->plugin['plugin_uri'] = '';
        }

        //disable admin logo image
        if ( isset( $new_plugin_info['disable_admin_logo_img'] ) && 1 == $new_plugin_info['disable_admin_logo_img'] ) {
            WPC()->plugin['logo_style'] = '';
        }

        //set admin pages logo content
        if ( isset( $new_plugin_info['logo_content'] ) && '' != trim( $new_plugin_info['logo_content'] ) ) {
            WPC()->plugin['logo_content'] = stripslashes( trim( $new_plugin_info['logo_content'] ) );
        }

        //set admin pages logo style
        if ( isset( $new_plugin_info['logo_style'] ) && '' != trim( $new_plugin_info['logo_style'] ) ) {
            WPC()->plugin['logo_style'] = stripslashes( trim( $new_plugin_info['logo_style'] ) );
        }

        //set icon url
        if ( isset( $new_plugin_info['icon_url'] ) && '' != trim( $new_plugin_info['icon_url'] ) ) {
            WPC()->plugin['icon_url'] = stripslashes( trim( $new_plugin_info['icon_url'] ) );
        }

        //hide help menu
        if ( isset( $new_plugin_info['hide_help_menu'] ) && 1 ==  $new_plugin_info['hide_help_menu'] )  {
            WPC()->plugin['hide_help_menu'] = $new_plugin_info['hide_help_menu'];
        }

        //hide extensions menu
        if ( isset( $new_plugin_info['hide_extensions_menu'] ) && 1 ==  $new_plugin_info['hide_extensions_menu'] )  {
            WPC()->plugin['hide_extensions_menu'] = $new_plugin_info['hide_extensions_menu'];
        }

        //hide about tab
        if ( isset( $new_plugin_info['hide_about_tab'] ) && 1 == $new_plugin_info['hide_about_tab'] ) {
            WPC()->plugin['hide_about_tab'] = $new_plugin_info['hide_about_tab'];
        }

        //hide system status tab
        if ( isset( $new_plugin_info['hide_system_status_tab'] ) && 1 == $new_plugin_info['hide_system_status_tab'] ) {
            WPC()->plugin['hide_system_status_tab'] = $new_plugin_info['hide_system_status_tab'];
        }

        //hide get started tab
        if ( isset( $new_plugin_info['hide_get_started_tab'] ) && 1 == $new_plugin_info['hide_get_started_tab'] ) {
            WPC()->plugin['hide_get_started_tab'] = $new_plugin_info['hide_get_started_tab'];
        }

        //hide licenses tab
        if ( isset( $new_plugin_info['hide_licenses_tab'] ) && 1 == $new_plugin_info['hide_licenses_tab'] ) {
            WPC()->plugin['hide_licenses_tab'] = $new_plugin_info['hide_licenses_tab'];
        }

    }

    /**
     * Set custom titles
     *
     * @return void
     */
    static function set_custom_titles() {

        $default_titles = WPC()->get_default_titles();

        /*our_hook_
        hook_name: wpc_default_custom_titles
        hook_title: Add custom titles
        hook_description: Hook add default custom titles.
        hook_type: filter
        hook_in: wp-client
        hook_location class.common.php
        hook_param: array $default_custom_titles
        hook_since: 3.7.7.1
        */
        $default_titles = apply_filters( 'wpc_default_custom_titles', $default_titles );

        $wpc_custom_titles = WPC()->get_settings( 'custom_titles' );

        WPC()->custom_titles = ( is_array( $wpc_custom_titles ) ) ? array_merge( $default_titles, $wpc_custom_titles ) : $default_titles;
    }



} //end class

endif;

new WPC_Hooks_Pre_Loads();