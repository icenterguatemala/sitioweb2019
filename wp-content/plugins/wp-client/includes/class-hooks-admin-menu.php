<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Hooks_Admin_Menu' ) ) :

class WPC_Hooks_Admin_Menu {

    var $plugin_submenus;

    /**
    * constructor
    **/
    function __construct() {

        //admin menu
        add_action( 'admin_menu', array( &$this, 'adminmenu' ) );
        add_action( 'adminmenu', array( &$this, 'add_subsubmenu' ) );
        add_action( 'admin_menu', array( &$this, 'hide_add_new_custom_type' ) );
        add_action( 'in_admin_header', array( &$this, 'return_admin_submenu' ) );
        add_filter( 'admin_body_class', array( &$this, 'hide_admin_submenu' ) );

        add_action( 'load-edit.php', array( &$this, 'hide_wp_list_tables' ) );

        //add admin submenu
        add_filter( 'wpc_client_admin_submenus', array( &$this, 'admin_menu_change_capabilities' ) );

        //for easymode
        add_filter( 'wpc_client_admin_submenus', array( &$this, 'easy_mode_hide_submenus' ), 10, 1 );
        add_filter( 'wpc_client_add_subsubmenu', array( &$this, 'easy_mode_hide_subsubmenus' ), 10, 1 );
    }


    /**
     * Remove some submenus for easy mode
     *
     * @param $submenus
     * @return mixed
     */
    function easy_mode_hide_submenus( $submenus ) {
        if( WPC()->flags['easy_mode'] ) {
            unset( $submenus['wpclients_permissions'] );
            unset( $submenus['wpclients_customize'] );
        }
        return $submenus;
    }


    /**
     * Remove some subsubmenus for easy mode
     *
     * @param $subsubmenus
     * @return mixed
     */
    function easy_mode_hide_subsubmenus( $subsubmenus ) {
        if( WPC()->flags['easy_mode'] ) {
            $temp = array();
            $hide_slugs = array(
                'admin.php?page=wpclient_clients&tab=convert',
                'admin.php?page=wpclient_clients&tab=staff',
                'admin.php?page=wpclient_clients&tab=custom_fields',
                'admin.php?page=wpclient_clients&tab=admins',
                'admin.php?page=wpclient_clients&tab=managers',
                'admin.php?page=wpclients_content&tab=files_downloads',
                'admin.php?page=wpclients_content&tab=tags',
                'admin.php?page=wpclients_templates&tab=shortcodes'
            );
            foreach( $subsubmenus as $subsubmenu ) {
                if( !( isset( $subsubmenu['slug'] ) && in_array( $subsubmenu['slug'], $hide_slugs ) ) ) {
                    $temp[] = $subsubmenu;
                }
            }
            $subsubmenus = $temp;
        }
        return $subsubmenus;
    }


    function add_subsubmenu() {
        $array_parent_slug = array();
        if ( isset( $this->plugin_subsubmenus ) ) {
            foreach ( $this->plugin_subsubmenus as $key => $values ) {
                if ( !isset( $values['capability'] ) || ( isset( $values['capability'] ) && 'yes' == $values['capability'] ) ) {
                    $array_parent_slug[ $values['parent_slug'] ][] = $values;
                }
            }
        }
        wp_enqueue_script( 'add-subsubmenu' );
        wp_localize_script( 'add-subsubmenu', 'MySubsubmenu', $array_parent_slug );
    }


    function adminmenu() {
        global $current_user;

        $cap = "manage_options";
        $manager_cap = "manage_options";

        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
            $manager_cap = "wpc_manager";
        } elseif( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
            $cap = "wpc_admin";
            $manager_cap = "wpc_admin";
        }


        $crash_updates = get_option( 'wp_client_crash_updates', array() );
        //Crash update menu
        if ( isset( $crash_updates['core'] ) ) {
            add_menu_page( WPC()->plugin['title'], WPC()->plugin['title'], $manager_cap, 'wpclients', array( &$this, 'wpclients_crash_update_func' ), WPC()->plugin['icon_url'], '2,00000000002' );
            return;
        }

        $this->plugin_submenus = array(
            'wpclients_clients'    => array(
                'page_title'        => __( 'Members', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Members', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclient_clients',
                'capability'        => $manager_cap,
                'function'          => array( &$this, 'wpc_clients_func' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 5,
            ),
            'wpclients_content'    => array(
                'page_title'        => __( 'Portals', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Portals', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_content',
                'capability'        => $manager_cap,
                'function'          => array( &$this, 'wpc_content_func' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 7,
            ),
            'wpclients_templates'   => array(
                'page_title'        => __( 'Templates', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Templates', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_templates',
                'capability'        => ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ||
                    current_user_can('wpc_view_email_templates') || current_user_can('wpc_edit_email_templates') ||
                    current_user_can('wpc_view_shortcode_templates') || current_user_can('wpc_edit_shortcode_templates')
                    ) ? $manager_cap : false,
                'function'          => array( &$this, 'wpclients_templates' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 200,
            ),
            'wpclients_customize'   => array(
                'page_title'        => __( 'Customize', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Customize', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_customize',
                'capability'        => $cap,
                'function'          => array( &$this, 'wpclients_customize' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 195,
            ),
            'wpclients_permissions'       => array(
                'page_title'        => __( 'Permissions Report', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Permissions Report', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_permissions',
                'capability'        => $cap,
                'function'          => array( &$this, 'wpclients_permissions' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 0,
            ),
            'wpclients_extensions'  => array(
                'page_title'        => __( 'Extensions', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Extensions', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_extensions',
                'capability'        => $cap,
                'function'          => array( &$this, 'wpclients_extensions' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 209,
            ),
            'wpclients_settings'    => array(
                'page_title'        => __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_settings',
                'capability'        => $cap,
                'function'          => array( &$this, 'wpclients_settings' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 210,
            ),
            'wpclients_help'        => array(
                'page_title'        => sprintf( __( '%s Wordpress Client Management Portal | Documentation & Tips', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ),
                'menu_title'        => __( 'Help', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_help',
                'capability'        => $cap,
                'function'          => array( &$this, 'wpclients_help' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 220,
            ),
            'separator_0'           => array(
                'page_title'        => '',
                'menu_title'        => '- - - - - - - - - -',
                'slug'              => '#',
                'capability'        => $cap,
                'function'          => '',
                'hidden'            => false,
                'real'              => false,
                'order'             => 1,
            ),

            'separator_3'           => array(
                'page_title'        => '',
                'menu_title'        => '- - - - - - - - - -',
                'slug'              => '#',
                'capability'        => $cap,
                'function'          => '',
                'hidden'            => false,
                'real'              => false,
                'order'             => 190,
            ),

        );

        $subsubmenu = array(
            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => WPC()->custom_titles['client']['p'],
                'capability'        => ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => WPC()->custom_titles['circle']['p'],
                'capability'        => ( current_user_can( 'wpc_show_circles' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=circles',
            ),
            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => __( 'Convert Users', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=convert',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => sprintf( __( '%s\'s %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['s']  ),
                'capability'        => ( current_user_can( 'wpc_add_staff' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=staff',
                ),

            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_create_custom_fields' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=custom_fields',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_restore_clients' ) || current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=archive',
                ),


            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => WPC()->custom_titles['admin']['p'],
                'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=admins',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclient_clients',
                'menu_title'        => WPC()->custom_titles['manager']['p'],
                'capability'        => ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclient_clients&tab=managers',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_content',
                'menu_title'        => __( 'Start Pages', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_view_portalhubs' ) || current_user_can( 'wpc_admin' )
                    || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_content',
            ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_content',
                'menu_title'        => WPC()->custom_titles['portal_page']['p'],
                'capability'        => ( current_user_can( 'view_others_clientspages' ) || current_user_can( 'edit_others_clientspages' )
                                        || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_content&tab=portal_page',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_content',
                'menu_title'        => __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_content&tab=files',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_content',
                'menu_title'        => __( 'Download Log', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_show_download_log' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_content&tab=files_downloads',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_content',
                'menu_title'        => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_view_private_messages' ) ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_content&tab=private_messages',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_templates',
                'menu_title'        => sprintf( __( '%s Template', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ),
                'capability'        => ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_templates&tab=portal_page',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_templates',
                'menu_title'        => __( 'Email Templates', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_view_email_templates' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_templates&tab=emails',
                ),
            array(
                'parent_slug'       => 'admin.php?page=wpclients_templates',
                'menu_title'        => __( 'Shortcode Templates', WPC_CLIENT_TEXT_DOMAIN ),
                'capability'        => ( current_user_can( 'wpc_view_shortcode_templates' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                'slug'              => 'admin.php?page=wpclients_templates&tab=php_templates',
                ),
            );

        $tabs_of_settings = WPC()->admin()->get_tabs_of_settings();
        $capability_of_settings = ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no';

        if ( $tabs_of_settings ) {
            foreach ( $tabs_of_settings as $key => $tab ) {
                $subsubmenu[] = array(
                    'parent_slug'       => 'admin.php?page=wpclients_settings',
                    'menu_title'        => $tab['title'],
                    'capability'        => $capability_of_settings,
                    'slug'              => 'admin.php?page=wpclients_settings&tab=' . $key,
                    );
            }
        }



        $this->plugin_subsubmenus = apply_filters( 'wpc_client_add_subsubmenu', $subsubmenu );


        if ( WPC()->plugin['hide_extensions_menu'] ) {
            if ( isset( $this->plugin_submenus['wpclients_extensions'] ) ) {
                unset( $this->plugin_submenus['wpclients_extensions'] );
            }
        }

        if ( WPC()->plugin['hide_help_menu'] ) {
            if ( isset( $this->plugin_submenus['wpclients_help'] ) ) {
                unset( $this->plugin_submenus['wpclients_help'] );
            }
        }

        $this->plugin_submenus = apply_filters( 'wpc_client_admin_submenus', $this->plugin_submenus );

        @uasort( $this->plugin_submenus, array( &$this, 'sort_menu' ) );


        //add main menu and sub menu for WP Clients

        if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'administrator' ) ) {
            add_menu_page( __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN ), __( 'My HUB Page', WPC_CLIENT_TEXT_DOMAIN ), 'wpc_client', 'wpclients2', array( &$this, 'wpclients_func2' ), WPC()->plugin['icon_url'] );
        }

        //display setup wizard
        if ( WPC()->is_licensed( 'WP-Client' ) && 'true' === WPC()->get_settings( 'wizard_setup' ) ) {
            add_menu_page( WPC()->plugin['title'], WPC()->plugin['title'], $cap, 'wpc_setup_wizard', array( &$this, 'wpc_setup_wizard' ),  WPC()->plugin['icon_url'], '2,00000000002' );
            return;
        }

        //add main plugin menu
        add_menu_page(  WPC()->plugin['title'],  WPC()->plugin['title'], $manager_cap, 'wpclients', array(&$this, 'wpclients_func'),  WPC()->plugin['icon_url'], '2,00000000002' );


        if ( WPC()->is_licensed( 'WP-Client' ) ) {
            //add submenu
            if ( is_array( $this->plugin_submenus ) && count( $this->plugin_submenus ) ) {
                foreach ( $this->plugin_submenus as $key => $values ) {
                    if ( isset( $values['real'] ) && true == $values['real'] ) {
                        add_submenu_page( 'wpclients', $values['page_title'], $values['menu_title'], $values['capability'], $values['slug'], $values['function'] );
                    }
                }
            }
        }

        //add ability to view add PP by wpc_admin/manager
        if ( ( current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_manager' ) ) && !current_user_can( 'administrator' ) ) {
            global $_wp_submenu_nopriv;
            unset( $_wp_submenu_nopriv["edit.php"]['post-new.php'] );
        }
    }

    function hide_add_new_custom_type() {
        global $menu, $submenu;

        if ( isset( $submenu['wpclients'] ) ) {

            //temp menu for hide in future
            $GLOBALS['wpclients_temp_menu'] = array();
            if ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                $submenu['wpclients'][0][0] = __( 'Dashboard', WPC_CLIENT_TEXT_DOMAIN );
            }

            $main_menu = $submenu['wpclients'][0];
            unset( $submenu['wpclients'] );
            $submenu['wpclients'][] = $main_menu;

            if ( is_array( $this->plugin_submenus ) && count( $this->plugin_submenus ) ) {
                foreach ( $this->plugin_submenus as $key => $values ) {
                    if( $values['slug'] != $submenu['wpclients'][0][2] ) {
                        $submenu['wpclients'][] = array( $values['menu_title'], $values['capability'], $values['slug'], $values['page_title'] );
                    }
                }
            }
            //add separaters
            $menu['2,00000000001'] = array( '', 'read', 'separator001', '', 'wp-menu-separator' );
            $menu['2,00000000003'] = array( '', 'read', 'separator003', '', 'wp-menu-separator02' );
        }


    }





    /*
    * Return admin submenu variable for display pages
    */
    function return_admin_submenu() {
        global $submenu;
        if ( isset( $GLOBALS['wpclients_temp_menu'] ) )
            $submenu['wpclients'] = $GLOBALS['wpclients_temp_menu'];
    }


    /*
    * Hide admin submenu from list of menu
    */
    function hide_admin_submenu( $class ) {
        global $submenu, $pagenow;

        //hide some menu
        if ( is_array( $this->plugin_submenus ) && count( $this->plugin_submenus ) ) {

            $n = ( isset( $submenu['wpclients'] ) ) ? count( $submenu['wpclients'] ) : 0;

            foreach ( $this->plugin_submenus as $key => $values ) {
                if ( isset( $values['hidden'] ) && true == $values['hidden'] ) {

                    for( $i = 0; $i < $n; $i++ ) {
                        if ( isset( $submenu['wpclients'][$i] ) && in_array( $values['slug'], $submenu['wpclients'][$i] ) )
                            unset( $submenu['wpclients'][$i] );
                    }
                }
            }
        }


        //change parent file when add/edit HUB/Portal Page form opened
        //for display WP-Client menu active
        if ( isset( $submenu['wpclients'] ) ) {
            if ( isset( $_GET['post_type'] ) && ( 'portalhub' == $_GET['post_type'] || 'clientspage' == $_GET['post_type'] ) ) {
                add_filter( 'parent_file', array( &$this, 'change_parent_file' ), 200 );
            }

            if ( 'post.php' == $pagenow && ( isset( $_GET['post'] ) && ( 'portalhub' == get_post_type( $_GET['post'] ) || 'clientspage' == get_post_type( $_GET['post'] ) ) ) ) {
                add_filter( 'parent_file', array( &$this, 'change_parent_file' ), 200 );
            }
        }
        return $class;
    }


    /*
    * Return admin submenu variable for display pages
    */
    function change_parent_file( $parent_file ) {
        global $pagenow;
        $pagenow = 'admin.php';
        $parent_file = 'wpclients';
        return $parent_file;
    }


    /**
     * Redirect from default WP tables to custom WPC
     *
     */
    function hide_wp_list_tables() {
        if ( isset( $_GET['post_type'] ) ) {
            if ( 'portalhub' == $_GET['post_type'] ) {
                WPC()->redirect( add_query_arg( array( 'page'=>'wpclients_content', 'tab' => 'portalhubs' ), admin_url( 'admin.php' ) ) );
            } elseif ( 'clientspage' == $_GET['post_type'] ) {
                WPC()->redirect( add_query_arg( array( 'page'=>'wpclients_content' ), admin_url( 'admin.php' ) ) );
            }
        }
    }


    /*
    * sorting Menu array by order
    */
    function sort_menu( $a, $b ) {
        //name of key for sort
        $key = 'order';

        if ( strtolower( $a[$key] ) == strtolower( $b[$key] ) )
            return 0;

        return ( strtolower( $a[$key] ) < strtolower( $b[$key] ) ) ? -1 : 1;
    }



    /*
    * display extensions page
    */
    function wpclients_extensions() {
        include  WPC()->plugin_dir . 'includes/admin/extensions.php';
    }


    /*
    * display settings page
    */
    function wpclients_settings() {
        include  WPC()->plugin_dir . 'includes/admin/settings.php';
    }


    function wpclients_func() {
        if ( isset( $_GET['tab'] ) && 'import-export' == $_GET['tab'] ) {
            include  WPC()->plugin_dir . 'includes/admin/import_export.php';
        } else {
            include  WPC()->plugin_dir . 'includes/admin/dashboard.php';
        }

    }

    function wpclients_crash_update_func() {
        include_once( WPC()->plugin_dir . 'includes/admin/dashboard_crash_update.php' );
    }


    function wpc_content_func() {

        if ( isset( $_GET['tab'] ) )
            $tab = $_GET['tab'];
        else
            $tab = 'portalhubs';

        switch( $tab ) {
            case 'portal_page':
                include  WPC()->plugin_dir . 'includes/admin/portal_pages.php';
                break;

            case 'client_page_categories':
                include  WPC()->plugin_dir . 'includes/admin/clientspage_categories.php';
                break;

            case 'portalhubs':
                if ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_view_portalhubs' ) ) {
                    include WPC()->plugin_dir . 'includes/admin/portalhubs.php';
                }
                break;

            case 'files':
                include  WPC()->plugin_dir . 'includes/admin/files.php';
                break;

            case 'files_categories':
                if ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_show_file_categories' ) ) {

                    $meta_key = 'wpc_file_categories_view';
                    $user_id = get_current_user_id();
                    $view = get_user_meta( $user_id, $meta_key, true );
                    $view = ( !empty( $view ) && 'old' === $view ) ? 'old' : 'new';

                    //update filecat view
                    if( isset( $_GET['display'] ) && in_array( $_GET['display'], array( 'new', 'old' ) ) ) {
                        if ( $_GET['display'] !== $view ) {
                            $view = $_GET['display'];
                            update_user_meta( $user_id, $meta_key, $view );
                        }
                    }

                    if( 'old' == $view ) {
                        include  WPC()->plugin_dir . 'includes/admin/files_cat_old.php';
                    } else {
                        include  WPC()->plugin_dir . 'includes/admin/files_cat.php';
                    }
                }
                break;

            case 'files_tags':
                if ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_show_file_tags' ) ) {
                    include  WPC()->plugin_dir . 'includes/admin/file_tags.php';
                }
                break;

            case 'tags':
                if ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_manager' ) ) {
                    include  WPC()->plugin_dir . 'includes/admin/tags.php';
                }
                break;

            case 'files_downloads':
                if ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_show_download_log' ) ) {
                    include  WPC()->plugin_dir . 'includes/admin/files_download_log.php';
                }
                break;

            case 'private_messages':

                include  WPC()->plugin_dir . 'includes/admin/messages.php';
                break;

        }

        /*our_hook_
            hook_name: wpc_client_custom_tab_content
            hook_title: Add template for extra tab on wp-client page
            hook_description: Can be used for adding template of extra tabs.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: string $tab
            hook_since: 4.5.8
        */
        apply_filters( 'wpc_client_admin_custom_tab_content', $tab );
    }


    function wpc_clients_func() {

        if ( isset( $_GET['tab'] ) )
            $tab = $_GET['tab'];
        else
            $tab = 'clients';

        switch( $tab ) {
            case 'clients':
                include  WPC()->plugin_dir . 'includes/admin/clients.php';
                break;

            case 'circles':
                include  WPC()->plugin_dir . 'includes/admin/groups.php';
                break;

            case 'add_client':
            case 'edit_client':
                include  WPC()->plugin_dir . 'includes/admin/client_edit.php';
                break;

            case 'approve':
                include  WPC()->plugin_dir . 'includes/admin/clients_approve.php';
                break;

            case 'convert':
                include  WPC()->plugin_dir . 'includes/admin/clients_convert.php';
                break;

            case 'staff':
                include  WPC()->plugin_dir . 'includes/admin/clients_staff.php';
                break;

            case 'staff_add':
            case 'staff_edit':
                include  WPC()->plugin_dir . 'includes/admin/clients_staff_edit.php';
                break;

            case 'staff_approve':
                include  WPC()->plugin_dir . 'includes/admin/clients_staff_approve.php';
                break;

            case 'custom_fields':
                if ( ( isset( $_GET['add'] ) && '1' == $_GET['add'] ) || ( isset( $_GET['edit'] ) && '' != $_GET['edit'] ) ) {
                    include  WPC()->plugin_dir . 'includes/admin/clients_custom_field_edit.php';
                } else {
                    include  WPC()->plugin_dir . 'includes/admin/clients_custom_fields.php';
                }

                break;

            case 'archive':
                include  WPC()->plugin_dir . 'includes/admin/clients_archive.php';
                break;

            case 'admins':
                if ( current_user_can( 'administrator' ) ) {
                    include  WPC()->plugin_dir . 'includes/admin/admins.php';
                }
                break;

            case 'admins_edit':
            case 'admins_add':
                include  WPC()->plugin_dir . 'includes/admin/admin_edit.php';
                break;


            case 'managers':
                include  WPC()->plugin_dir . 'includes/admin/managers.php';
                break;

            case 'managers_edit':
            case 'managers_add':
                include  WPC()->plugin_dir . 'includes/admin/manager_edit.php';
                break;

        }

        /*our_hook_
            hook_name: wpc_client_custom_tab_content
            hook_title: Add template for extra tab on wp-client page
            hook_description: Can be used for adding template of extra tabs.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: string $tab
            hook_since: 4.5.8
        */
        apply_filters( 'wpc_client_admin_custom_tab_content', $tab );
    }


    /*
    * templates functions
    */
    function wpclients_templates() {
        include  WPC()->plugin_dir . 'includes/admin/templates.php';
    }


    /*
    * templates functions
    */
    function wpclients_customize() {
        include  WPC()->plugin_dir . 'includes/admin/customize.php';
    }

    //page Files
    function wpclients_permissions() {
        include  WPC()->plugin_dir . 'includes/admin/permissions.php';
    }

    //page Help
    function wpclients_help() {

        $content =  WPC()->remote_download("https://wp-client.com/_remote/clients/wpc_help.txt");

        echo '<div class="wpc_clear"></div>';

        echo WPC()->admin()->get_plugin_logo_block();

        echo "<h3>" . sprintf( __( '%s Wordpress Client Management Portal | Documentation & Tips', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . "</h3>";
        echo $content;
    }


    /*
    * redirect client on HUB from admin menu
    */
    function wpclients_func2() {
        echo "You will be redirected to the page in a few seconds, if it doesn't redirect , please click <a href='" . WPC()->get_hub_link() . "'>here</a>";
        echo "<script type='text/javascript'>document.location='" . WPC()->get_hub_link() . "';</script>";
    }


    /*
    *  Change capabilities for admin submenu
    */
    function admin_menu_change_capabilities( $plugin_submenus ) {

        //set for manager
        if ( current_user_can( 'wpc_manager' ) && !current_user_can('administrator') )
            $cap = "wpc_manager";
        elseif ( !current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_admin' ) && !current_user_can('administrator') )
            $cap = "wpc_admin";
        else
            return $plugin_submenus;


//            if ( isset( $plugin_submenus['add_client_page']['capability'] ) )
//                $plugin_submenus['add_client_page']['capability'] = $cap;

        if ( isset( $plugin_submenus['wpclients_content']['capability'] ) )
            $plugin_submenus['wpclients_content']['capability'] = $cap;

        if ( isset( $plugin_submenus['client_pages']['capability'] ) )
            $plugin_submenus['client_pages']['capability'] = $cap;

        return $plugin_submenus;
    }


    /*
    * display wizard page - should be empty, see WPC_Hooks_Admin->setup_hide_admin()
    */
    function wpc_setup_wizard() {
    }

}

endif;

new WPC_Hooks_Admin_Menu();