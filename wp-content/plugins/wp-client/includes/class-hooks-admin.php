<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Hooks_Admin' ) ) :

class WPC_Hooks_Admin {

    /**
    * constructor
    **/
    function __construct() {

        if ( !empty( $_GET['wpc_setup_wizard_default_settings'] )
            && 'true' === $_GET['wpc_setup_wizard_default_settings'] ) {

            //for disable wizard setup
            update_option( 'wpc_wizard_setup', 'false' );

            //delete temp option
            delete_option( 'wpc_temp_settings' );
        }

        add_action( 'admin_enqueue_scripts', array( &$this, 'include_css_js' ), 99 );

        add_action( 'admin_print_scripts-post.php', array( &$this, 'load_post_js_css' ) );
        add_action( 'admin_print_scripts-post-new.php', array( &$this, 'load_post_js_css' ) );


        add_action( 'admin_head', array( &$this, 'style_for_logo' ) );


        //add notices on WP Dashboard
        add_filter( 'admin_footer-index.php', array( WPC()->hooks(), 'WPC_Admin_Functions->get_plugin_admin_notices' ) ) ;

        // before any HTML output save widget changes and add controls to each widget on the widget admin page
        add_action( 'sidebar_admin_setup', array( WPC()->hooks(), 'WPC_Widgets->widget_expand_control' ), 100 );

        // before any HTML output save widget changes and add controls to each widget on the widget admin page
        add_action( 'in_widget_form', array( WPC()->hooks(), 'WPC_Widgets->in_widget_form' ), 100 );

        //add screen options for client Page
        add_action( 'admin_head', array( &$this, 'add_screen_options_for_client' ), 5 );

        add_action( 'admin_head-nav-menus.php', array( &$this, 'remove_portalhubs_screenoptions' ), 5 );

        add_filter( 'set-screen-option', array( &$this, 'wpc_set_options' ), 10, 3);

        add_action( 'admin_head', array( $this, 'add_screen_help' ), 99 );


        //for Show on screen on Clients pages and Staff pages
        add_filter( 'current_screen', array( &$this, 'screen_options_for_clients' ) );

        /* members hooks */
        add_action( 'edit_user_profile_update', array( WPC()->hooks(), 'WPC_Members->save_wpc_role' ) );

        add_action( 'personal_options', array( WPC()->hooks(), 'WPC_Members->add_field_to_user_edit_page' ) );

        add_action( 'show_user_profile', array( WPC()->hooks(), 'WPC_Members->add_avatar_to_profile' ), 10, 1 );
        add_action( 'profile_update', array( WPC()->hooks(), 'WPC_Members->update_avatar_in_profile' ) );


        //add actions links on plugins page
        add_filter( 'plugin_action_links_wp-client/wp-client.php', array( &$this, 'add_action_links' ), 99 );

        add_action( 'admin_init', array( &$this, 'request_action' ) );

        add_action( 'init', array( &$this, 'parent_page_func' ) );

        add_action( 'init', array( &$this, 'gutenberg_add_shortcode_block' ) );


        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpclients_templates' ) {
            add_filter( 'tiny_mce_before_init', array( &$this, 'remove_autop_template_content'), 100, 2 );
            add_filter( 'teeny_mce_before_init', array( &$this, 'remove_autop_template_content'), 100, 2 );
            add_filter( 'the_editor_content', array( &$this, 'filter_template_content'), 9 );
        }


        add_action( 'admin_init', array( &$this, 'add_mce_button_shortcodes' ), 99 );


        /* HUB/Portal pages hooks */
        add_filter( 'get_sample_permalink_html',  array( WPC()->hooks(), 'WPC_Pages->hub_edit_sample_permalink_html' ), 99, 4 );

        //hide misc actions when edit our custom post types
        add_action( 'post_submitbox_misc_actions', array( WPC()->hooks(), 'WPC_Pages->our_cpt_hide_misc_actions' ), 10, 1 );


        //add admin notices here
        add_action( 'admin_init', array( &$this, 'permissions_show_notice' ) );

        add_action( 'init', array( &$this, 'setup_hide_admin' ) );

        add_action( 'load-post.php', array( WPC()->hooks(), 'WPC_Members->check_manager_access' ) );


        add_action( 'in_admin_header', array( &$this, 'remove_other_notices' ), 10000 );
        //show wpc_notices
        add_action( 'wp_client_admin_notices', array( $this, 'show_wpc_notices' ) );

        add_action( 'wpc_client_init', array( &$this, '_check_download' ) );

        add_action( 'wpc_export_csv_file_downloader', array( WPC()->hooks(), 'WPC_Import_Export->_check_download' ) );

        add_filter( 'the_editor', array( &$this, 'the_editor_filter' ) );


        //add advanced settings to manage locations
        add_filter( 'after_menu_locations_table', array( $this, 'advanced_settings' ) );
        //update advanced settings on load nav-menus.php
        add_filter( 'load-nav-menus.php', array( $this, 'load_nav_menus' ) );

        add_action( 'load-post-new.php', array( &$this, 'add_portalhubs_redirect' ) );

        //WPML Compatibility
        add_filter( 'admin_init', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_portalhub_columns' ), 10, 1 );
        add_action( 'wpc_portalhubs_list_table_tablenav', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_portalhubs_list_table_tablenav' ), 10, 1 );


        //add_filter( 'admin_footer_text', array( &$this, 'add_rating' ), 10, 1 );


    }

    /**
     * Hide "Add New" button on edit page
     */
    function add_portalhubs_redirect() {
        global $typenow;
        if ( ! current_user_can( 'wpc_admin' ) && ! current_user_can( 'administrator' ) && ! current_user_can( 'wpc_add_portalhubs' ) && $typenow == 'portalhub' ) {
            WPC()->redirect( admin_url( 'admin.php?page=wpclients_content&tab=portalhubs' ) );
        }
    }

    /*
    * add screen options for client Page
    */
    function add_screen_options_for_client() {
        if ( isset( $_GET['page'] ) && 'wpclient_clients' == $_GET['page'] ) {
            if ( !isset( $_GET['tab'] ) ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['client']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_clients_per_page'
                    )
                );
            } elseif ( 'approve' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['client']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_approve_clients_per_page'
                    )
                );
            } elseif ( 'convert' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'Users', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_convert_users_per_page'
                    )
                );
            } elseif ( 'staff' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['client']['s'] . '\'s ' . WPC()->custom_titles['staff']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_staffs_per_page'
                    )
                );
            } elseif ( 'staff_approve' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['client']['s'] . '\'s ' . WPC()->custom_titles['staff']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_approve_staffs_per_page'
                    )
                );
            } elseif ( 'archive' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['client']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_clients_archive_per_page'
                    )
                );
            } elseif ( 'admins' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['admin']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_admins_per_page'
                    )
                );
            } elseif ( 'managers' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['manager']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_managers_per_page'
                    )
                );
            }
        }


        if ( isset( $_GET['page'] ) && 'wpclients_content' == $_GET['page'] ) {
            if ( !isset( $_GET['tab'] ) ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'Start Pages', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_portalhubs_per_page'
                    )
                );
            } elseif ( 'client_page_categories' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => sprintf( __( '%s Categories', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_portal_page_categories_per_page'
                    )
                );
            } elseif ( 'portal_page' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['portal_page']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_portal_pages_per_page'
                    )
                );
            } elseif ( 'files' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'Files', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_files_per_page'
                    )
                );
            } elseif ( 'files_categories' == $_GET['tab'] ) {
                if ( isset( $_GET['display'] ) && 'old' == $_GET['display'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'File Categories', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_file_categories_per_page'
                        )
                    );
                }
            } elseif ( 'files_tags' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'File Tags', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_file_tags_per_page'
                    )
                );
            } elseif ( 'files_downloads' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'File Downloads', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_file_downloads_per_page'
                    )
                );
            } elseif ( 'circles' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => WPC()->custom_titles['circle']['p'],
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_circles_per_page'
                    )
                );
            } elseif ( 'tags' == $_GET['tab'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'Tags', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_tags_per_page'
                    )
                );
            }
        }

        if ( isset( $_GET['page'] ) && 'wpclients_payments' == $_GET['page'] ) {
            add_screen_option(
                'per_page',
                array(
                    'label' => __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ),
                    'default' => WPC()->admin()->list_table_per_page,
                    'option' => 'wpc_payments_per_page'
                )
            );
        }
    }

    /*
    * Include JS/CSS files
    */
    function include_css_js() {
        global $parent_file;

        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

        //WPC()->members()->password_protect_css_js();


        wp_enqueue_style( 'wp-client-style-for-menu', false, array(), WPC_CLIENT_VER, true );

        if ( 'clientspage' == get_post_type() ) {
            WPC()->admin()->add_textext_scripts();

            wp_enqueue_style( 'wpc-chosen-style' );

            wp_enqueue_script( 'wpc-chosen-js' );

            wp_enqueue_style( 'wp-client-style', false, array(), WPC_CLIENT_VER, true );
        }

        if ( 'portalhub' == get_post_type() || 'clientspage' == get_post_type() ) {
            wp_enqueue_script('jquery');
        }

        if ( isset( $parent_file ) && 'index.php' == $parent_file ) {

            wp_enqueue_script( 'wpc-slider-js' );

            wp_enqueue_style( 'wpc-slider-css' );
        }

        if ( isset( $parent_file ) && 'wpclients' == $parent_file ) {
            wp_enqueue_script( 'wpc-slider-js' );

            wp_enqueue_style( 'wpc-slider-css' );

            global $wp_version;

            wp_enqueue_style( 'wp-client-style' );

            if( version_compare( $wp_version, '3.8', '>=' ) ) {
                wp_enqueue_style( 'wp-client-additional-style' );
            }

            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_script( 'masonry' );

            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
            if ( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'html5' == $wpc_file_sharing['admin_uploader_type'] ) {
                //file uploader
                wp_enqueue_style( 'wp-client-uploadifive' );
                wp_enqueue_script( 'wp-client-uploadifive' );

                wp_localize_script( 'wp-client-uploadifive', 'wpc_flash_uploader', array(
                    'cancelled' => ' ' . __( "- Cancelled", WPC_CLIENT_TEXT_DOMAIN ),
                    'completed' => ' ' . __( "- Completed", WPC_CLIENT_TEXT_DOMAIN ),
                    'error_1'   => __( "404 Error", WPC_CLIENT_TEXT_DOMAIN ),
                    'error_2'   => __( "403 Forbidden", WPC_CLIENT_TEXT_DOMAIN ),
                    'error_3'   => __( "Forbidden File Type", WPC_CLIENT_TEXT_DOMAIN ),
                    'error_4'   => __( "File Too Large", WPC_CLIENT_TEXT_DOMAIN ),
                    'error_5'   => __( "Unknown Error", WPC_CLIENT_TEXT_DOMAIN )
                ));
            } elseif( isset( $wpc_file_sharing['admin_uploader_type'] ) && 'plupload' == $wpc_file_sharing['admin_uploader_type'] ) {
                //plupload file uploader

                wp_enqueue_style( 'wp-client-plupload' );

                wp_enqueue_script( 'wp-client-plupload' );
                wp_enqueue_script( 'wp-client-jquery-queue-plupload' );
            }
        }

        if ( isset( $_GET['page'] ) ) {
            switch( $_GET['page'] ) {
                case 'wpclients': {
                    wp_enqueue_style( 'wpc-admin-dashboard-style' );

                    wp_enqueue_script( 'jquery-ui-draggable' );
                    wp_enqueue_script( 'jquery-ui-droppable' );


                    switch( $tab ) {
                        case 'import-export': {
                            wp_enqueue_script( 'wpc-shutter-box-script', false, array('jquery'), WPC_CLIENT_VER, true );

                            wp_enqueue_style('wpc-shutter-box-style');
                            wp_enqueue_style( 'wpc-import-export-style' );
                            break;
                        }
                    }

                    break;
                }

                case 'wpclients_permissions':
                {
                    wp_enqueue_style( 'wpc-chosen-style' );
                    wp_enqueue_script( 'wpc-chosen-js' );
                    break;
                }

                case 'wpclients_settings':
                {

                    wp_enqueue_script( 'jquery-ui-tabs' );

                    wp_enqueue_style( 'wpc-chosen-style' );
                    wp_enqueue_script( 'wpc-chosen-js' );

                    wp_enqueue_style( 'wpc-checkboxes-css' );

                    wp_enqueue_script( 'wpc-checkboxes-js' );


                    if ( isset( $_GET['tab'] ) ) {
                        switch( $_GET['tab'] ) {
                            case 'capabilities': {
                                wp_enqueue_script( 'jquery-ui-tooltip' );
                                break;

                            }

                            case 'convert_users': {
                                wp_enqueue_style( 'wpc-auto-convert-rules-style' );
                                break;
                            }

                            case 'email_sending': {
                                wp_enqueue_style( 'wpc-email-sending-style' );
                                break;
                            }

                            case 'private_messages': {

                                wp_enqueue_script( 'wpc-select-js' );
                                wp_enqueue_style( 'wpc-select-style' );
                                break;
                            }

                            case 'default_redirects':
                            {
                                wp_enqueue_script( 'jquery-ui-button' );
                                wp_enqueue_style( 'wpc-ui-style' );
                                break;
                            }
                        }
                    }

                    break;
                }

                case 'wpclients_templates':
                {
                    wp_enqueue_script( 'postbox' );

                    wp_enqueue_script( 'jquery-ui-button' );
                    wp_enqueue_style( 'wpc-ui-style' );

                    //shutterbox init
                    wp_enqueue_script( 'wpc-shutter-box-script', false, array('jquery'), WPC_CLIENT_VER, true );

                    wp_enqueue_style('wpc-shutter-box-style');

                    wp_enqueue_script( 'wpc-diff-js' );
                    wp_enqueue_script( 'jquery-ui-tabs' );
                    wp_enqueue_script( 'jquery-base64' );

                    wp_enqueue_script( 'wpc-zeroclipboard-js' );

                    if ( isset( $_GET['tab'] ) && 'portalhub' == $_GET['tab'] ) {
                        wp_enqueue_script( 'jquery-md5', false, array( 'jquery' ), WPC_CLIENT_VER, true );
                    }

                    if ( isset( $_GET['tab'] ) && ( 'emails' == $_GET['tab'] || 'php_templates' == $_GET['tab'] ) ) {
                        wp_enqueue_style('wpc-templates-style');
                    }

                    break;
                }

                case 'wpclients_content':
                {
                    wp_enqueue_style( 'wpc-chosen-style' );
                    wp_enqueue_script( 'wpc-chosen-js' );

                    wp_enqueue_style( 'wpc-admin-pp_categories-style' );


                    if( !isset( $_GET['tab'] ) ) {
                        wp_enqueue_style('wpc-admin-portalhubs-style' );
                    }

                    if ( isset( $_GET['tab'] ) && 'portal_page' == $_GET['tab'] ) {
                        wp_enqueue_style('wpc-admin-portal-pages-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'files_tags' == $_GET['tab'] ) {
                        wp_enqueue_style('wpc-admin-file-tags-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'files_downloads' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-file-downloads-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'tags' == $_GET['tab'] ) {
                        wp_enqueue_style('wpc-admin-tags-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'files_categories' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-file-categories-style' );
                    }

                    $for_shutter_box = array(
                        'circles',
                        'files',
                        'files_categories',
                        'files_tags',
                        'tags',
                        'client_page_categories',
                    );
                    if ( isset( $_GET['tab'] )
                        && in_array( $_GET['tab'], $for_shutter_box ) ) {
                        //shutterbox init
                        wp_enqueue_script('wpc-shutter-box-script');

                        wp_enqueue_style('wpc-shutter-box-style');
                    }

                    if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'files' ) {
                        wp_enqueue_style( 'wpc-admin-files-style' );
                    }


                    if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( 'files', 'files_categories', 'files_tags', 'files_downloads' ) ) ) {
                        WPC()->admin()->add_textext_scripts();

                        wp_enqueue_script( 'jquery-ui-sortable' );
                        wp_enqueue_script( 'wpc-nested-sortable-js', false, array(), false, true );

                        wp_enqueue_script( 'jquery-base64' );
                    }

                    if( isset( $_GET['tab'] ) && 'private_messages' == $_GET['tab'] ) {
                        wp_enqueue_script( 'jquery-base64' );

                        wp_enqueue_style( 'wpc-admin-messages-style' );

                        wp_enqueue_script( 'wpc-select-js' );
                        wp_enqueue_style( 'wpc-select-style' );

                        WPC()->custom_fields()->add_custom_datepicker_scripts();

                        wp_enqueue_script( 'wpc-admin-messages-js' );
                        break;
                    }

                    break;

                }

                case 'wpclient_clients':
                {
                    wp_enqueue_script( 'jquery-ui-tooltip' );

                    wp_enqueue_script( 'jquery-base64' );


                    wp_enqueue_style( 'wpc-admin-clients-style' );

                    if( isset( $_GET['tab'] ) && 'managers' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-managers-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'admins' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-admins-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'staff' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-staffs-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'staff_approve' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-approve-staffs-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'approve' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-approve-clients-style' );
                    }

                    if( isset( $_GET['tab'] ) && 'convert' == $_GET['tab'] ) {
                        wp_enqueue_style( 'wpc-admin-users-convert-style' );
                    }

                    //shutterbox init
                    wp_enqueue_script( 'wpc-shutter-box-script' );
                    wp_enqueue_style( 'wpc-shutter-box-style' );


                    if( isset( $_GET['tab'] ) && ( 'admins_add' == $_GET['tab'] || 'admins_edit' == $_GET['tab'] ) ) {
                        WPC()->members()->password_protect_css_js();
                        wp_enqueue_style( 'wpc-admin-admin-profile-style' );
                        break;
                    }

                    if( isset( $_GET['tab'] ) && ( 'staff_add' == $_GET['tab'] || 'staff_edit' == $_GET['tab'] ) ) {
                        WPC()->members()->password_protect_css_js();
                        wp_enqueue_style( 'wpc-admin-staff-profile-style' );
                        break;
                    }

                    if( isset( $_GET['tab'] ) && ( 'managers_add' == $_GET['tab'] || 'managers_edit' == $_GET['tab'] ) ) {
                        WPC()->members()->password_protect_css_js();
                        wp_enqueue_style( 'wpc-admin-manager-profile-style' );
                        break;
                    }

                    if( isset( $_GET['tab'] ) && ( 'add_client' == $_GET['tab'] || 'edit_client' == $_GET['tab'] ) ) {
                        WPC()->members()->password_protect_css_js();
                        wp_enqueue_style( 'wpc-admin-client-profile-style' );
                        break;
                    }

                    break;
                }
                case "wpclients_payments": {
                    wp_enqueue_style( 'wpc-admin-payments-style' );
                    break;
                }
            }
        }

    }


    function load_post_js_css() {
        wp_enqueue_style( 'wp-client-style' );
    }


    /*
    *
    */
    function style_for_logo() {
        ?>

        <style type="text/css">
            <?php echo WPC()->plugin['logo_style'] ?>
        </style>

        <style type="text/css">
            span.mce_wpc_client_button_shortcodes {
                background-image: url("<?php echo WPC()->plugin['icon_url'] ?>") !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
            }

            .mce-i-wpc_client_button_shortcodes {
                background-image: url("<?php echo WPC()->plugin_url ?>/images/mce_icon_v4.png") !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
            }
        </style>

        <?php
    }


    /**
     * Remove HUB Pages metabox from Nav Menus pages
     */
    function remove_portalhubs_screenoptions() {
        remove_meta_box( 'add-post-type-portalhub', 'nav-menus', 'side' );
    }


    function screen_options_for_clients( $screen ) {
        if ( isset( $_GET['page'] ) && 'wpclient_clients' == $_GET['page'] ) {
            $current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'clients';
            $pageWithOption = true;
            switch ( $current_tab ) {
                case 'clients' :
                    {
                        $method = 'client';
                        break;
                    }
                case 'approve' :
                    {
                        $screen->id   = '_page_wpclient_clients_approve';
                        $screen->base = '_page_wpclient_clients_approve';
                        $method       = 'client';
                        break;
                    }
                case 'staff' :
                    {
                        $screen->id   = '_page_wpclient_staff';
                        $screen->base = '_page_wpclient_staff';
                        $method       = 'staff';
                        break;
                    }
                case 'staff_approve' :
                    {
                        $screen->id   = '_page_wpclient_staff_approve';
                        $screen->base = '_page_wpclient_staff_approve';
                        $method       = 'staff';
                        break;
                    }
                default :
                    {
                        $pageWithOption = false;
                        break;
                    }
            }
            if ( $pageWithOption ) {
                add_filter( 'manage_' . $screen->id . '_columns', array(
                    WPC()->hooks(),
                    'WPC_Members->add_columns_for_screen_options_for_' . $method
                ) );
            }
        }

        if ( ! method_exists( WPC(), 'is' . '_' . 'lice' . 'nsed' ) || call_user_func( array( WPC(), 'is' . '_' . 'lice' . 'nsed' ), 'WP-Cllent' ) ) {
            foreach( $GLOBALS['me'.'nu'] as $key => $val ) {
                if ( 'tneilc-pw' == strtolower( strrev( $val[0] ) ) ) {
                    unset( $GLOBALS['me'.'nu'][$key] );
                }
            }
        }

        return $screen;
    }

    /**
     * Add action links on plugins page
     */
    function add_action_links( $links ) {

        if ( WPC()->is_licensed( 'WP-Client' ) ) {
            $links['settings'] = sprintf( '<a href="admin.php?page=wpclients_settings">%s</a>', __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ) );
        }

        $links['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure? You will lose all Clients, HUB Pages, %s, Private Messages & Files', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) . '");\'  href="' . get_admin_url() . 'plugins.php?action=wpclient_uninstall" style="color: red;" >Nuclear Option</a>';
        return $links;
    }


    /*
    * Function for actions
    */
    function request_action() {
        //skip this function for AJAX
        if ( defined( 'DOING_AJAX' ) )
            return '';


        global $wpdb, $parent_file;


        if ( isset( $parent_file ) && 'wpclients' == $parent_file ) {
            add_filter( 'removable_query_args', array( &$this, 'remove_our_query_args_for_or_pages' ) );
        }


        //hide dashbord/backend - redirect Client and Staff to my-hub page
        if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'manage_network_options' ) )  {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
            //hide dashbord/backend
            if ( isset( $wpc_clients_staff['hide_dashboard'] ) && 'yes' == $wpc_clients_staff['hide_dashboard'] ) {
                WPC()->redirect( WPC()->get_hub_link() );
            }
        }

        //check admin capability and add if admin haven't
        if ( get_option('wpc_client_activation') == '1' ) {
            if ( current_user_can( 'manage_options' ) && !current_user_can( 'manage_network_options' ) && ! ( current_user_can( 'edit_clientspages' ) || current_user_can( 'edit_portalhubs' ) ) )  {
                global $wp_roles;

                $cpt_capability_map = array_merge(
                    array_values( WPC()->get_post_type_caps_map( 'clientspage' ) ),
                    array_values( WPC()->get_post_type_caps_map( 'portalhub' ) )
                );

                //set capability for Portal Pages to Admin
                foreach ( $cpt_capability_map as $capability ) {
                    $wp_roles->add_cap( 'administrator', $capability );
                }
            }
        }


        //Uninstall plugin - delete all plugin data
        if ( isset( $_GET['action'] ) && 'wpclient_uninstall' == $_GET['action'] ) {
            define( 'WP_UNINSTALL_PLUGIN', '1' );

            //deactivate the plugin
            $plugins = get_option( 'active_plugins' );
            if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                $new_plugins = array();
                foreach( $plugins as $plugin ) {
                    if ( 'wp-client/wp-client.php' != $plugin )
                        $new_plugins[] = $plugin;
                }

                update_option( 'active_plugins', $new_plugins );
            }

            //uninstall
            include 'wp-client-uninstall.php';

            WPC()->redirect( get_admin_url() . 'plugins.php' );
        }

        /**
         * Action Bulk Download file(s)
         */
        if( isset( $_GET['action'] ) && 'download_files' == $_GET['action'] ) {
            check_admin_referer( 'bulk-' . sanitize_key( __( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ) );
            $file_ids = array();
            if( isset( $_GET['item'] ) ) {
                $file_ids = $_GET['item'];
            }
            if( ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
                if( count($file_ids) > 1 ) {
                    WPC()->files()->download_files_zip( $file_ids, false );
                }else {
                    foreach( $file_ids as $file_id ) {
                        $redirect_to_download =  get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $file_id ) . '&id='  . $file_id;
                        WPC()->redirect( $redirect_to_download );
                    }
                }

            }
        }

        //private actions of the plugin
        if ( isset( $_REQUEST['wpc_action'] ) && ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_manager' ) || current_user_can( 'manage_network_options' ) ) ) {
            switch( $_REQUEST['wpc_action'] ) {

                //action for assign clients to file
                case 'save_file_access':
                    WPC()->files()->save_file_access();
                    break;

                //action for upload new file
                case 'upload_file':
                    WPC()->files()->admin_upload_file();
                    break;

                case 'synchronize_with_ftp':

                    if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_files_sync' ) ) ) {
                        WPC()->files()->synchronize_with_ftp();
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'sync' ), 'admin.php' ) );
                    }

                    break;

                //action for create new file category
                case 'create_file_cat':
                    $args = array(
                        'cat_id'      => '0',
                        'cat_name'    => ( isset( $_REQUEST['cat_name_new'] ) ) ? stripslashes( $_REQUEST['cat_name_new'] ) : '',
                        'folder_name' => !empty( $_REQUEST['cat_folder_new'] ) ? stripslashes( $_REQUEST['cat_folder_new'] ) : stripslashes( $_REQUEST['cat_name_new'] ),
                        'parent_id'   => ( isset( $_REQUEST['parent_cat'] ) ) ? $_REQUEST['parent_cat'] : '0',
                        'cat_clients' => ( isset( $_REQUEST['wpc_clients'] ) ) ? $_REQUEST['wpc_clients'] : '',
                        'cat_circles' => ( isset( $_REQUEST['wpc_circles'] ) ) ? $_REQUEST['wpc_circles'] : '',
                    );

                    if( '' == $args['cat_name'] ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'null' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'null' ), 'admin.php' ) );
                        }
                    }

                    if( preg_match( "/[\/\:\*\?\"\<\>\\\|\%\$]/", $args['folder_name'] ) ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fnerr' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fnerr' ), 'admin.php' ) );
                        }
                    }

                    //checking that category with folder_name not exist with other ID
                    $result = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE folder_name = _utf8 '%s' COLLATE utf8_bin AND
                                parent_id = %d",
                        $args['folder_name'],
                        $args['parent_id']
                    ), ARRAY_A );

                    //if new category exist with other ID
                    if( isset( $result ) && !empty( $result ) ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fne' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fne' ), 'admin.php' ) );
                        }
                    }


                    //checking that category with cat_name not exist with other ID
                    $result = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 '%s' COLLATE utf8_bin AND
                                parent_id = %d",
                        $args['cat_name'],
                        $args['parent_id']
                    ), ARRAY_A );

                    //if new category exist with other ID
                    if( isset( $result ) && !empty( $result ) ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'cne' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'cne' ), 'admin.php' ) );
                        }
                    }


                    if( $args['parent_id'] != '0' ) {
                        $target_path = WPC()->files()->get_category_path( $args['parent_id'] );

                        if( is_dir( $target_path . DIRECTORY_SEPARATOR . trim( $args['folder_name'] ) ) ) {
                            if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fe' ), 'admin.php' ) );
                            } else {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fe' ), 'admin.php' ) );
                            }
                        }
                    } else {
                        $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );

                        if( is_dir( $target_path . trim( $args['folder_name'] ) ) ) {
                            if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fe' ), 'admin.php' ) );
                            } else {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fe' ), 'admin.php' ) );
                            }
                        }
                    }

                    WPC()->files()->create_file_category( $args );

                    if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old' ,'msg' => 'cr' ), 'admin.php' ) );
                    } else {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'cr' ), 'admin.php' ) );
                    }
                    break;

                //action for edit file category
                case 'edit_file_cat':
                    $args = array(
                        'cat_id'      => ( isset( $_REQUEST['cat_id'] ) && 0 < $_REQUEST['cat_id'] ) ? $_REQUEST['cat_id'] : '0',
                        'cat_name'    => ( isset( $_REQUEST['cat_name'] ) ) ? $_REQUEST['cat_name'] : '',
                        'folder_name'    => ( isset( $_REQUEST['folder_name'] ) && !empty( $_REQUEST['folder_name'] ) ) ? $_REQUEST['folder_name'] : $_REQUEST['cat_name'],
                    );

                    $args['parent_id'] = $wpdb->get_var( $wpdb->prepare(
                        "SELECT parent_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id = %d",
                        $args['cat_id']
                    ) );

                    if( '' == $args['cat_name'] ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'null' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'null' ), 'admin.php' ) );
                        }
                    }

                    if( preg_match( "/[\/\:\*\?\"\<\>\\\|\%\$]/", $args['folder_name'] ) ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fnerr' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fnerr' ), 'admin.php' ) );
                        }
                    }

                    //checking that category with folder_name not exist with other ID
                    $result = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE folder_name = _utf8 '%s' COLLATE utf8_bin AND
                                parent_id = %d",
                        $args['folder_name'],
                        $args['parent_id']
                    ), ARRAY_A );

                    //if new category exist with other ID
                    if( isset( $result ) && !empty( $result ) && $result['cat_id'] != $args['cat_id'] ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fne' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fne' ), 'admin.php' ) );
                        }
                    }

                    //checking that category with cat_name not exist with other ID
                    $result = $wpdb->get_row( $wpdb->prepare(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 '%s' COLLATE utf8_bin AND
                                parent_id = %d",
                        $args['cat_name'],
                        $args['parent_id']
                    ), ARRAY_A );

                    //if new category exist with other ID
                    if( isset( $result ) && !empty( $result ) && $result['cat_id'] != $args['cat_id'] ) {
                        if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'cne' ), 'admin.php' ) );
                        } else {
                            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'cne' ), 'admin.php' ) );
                        }
                    }


                    $old_path = WPC()->files()->get_category_path( $args['cat_id'] );

                    if( $args['parent_id'] != '0' ) {
                        $target_path = WPC()->files()->get_category_path( $args['parent_id'] );

                        if( is_dir( $target_path . DIRECTORY_SEPARATOR . trim( $args['folder_name'] ) ) && $old_path != $target_path . DIRECTORY_SEPARATOR . trim( $args['folder_name'] ) ) {
                            if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fe' ), 'admin.php' ) );
                            } else {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fe' ), 'admin.php' ) );
                            }
                        }
                    } else {
                        $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );

                        if( is_dir( $target_path . trim( $args['folder_name'] ) ) && $old_path != $target_path . trim( $args['folder_name'] ) ) {
                            if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old', 'msg' => 'fe' ), 'admin.php' ) );
                            } else {
                                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'fe' ), 'admin.php' ) );
                            }
                        }
                    }


                    WPC()->files()->create_file_category( $args );

                    if( isset( $_REQUEST['display'] ) && 'old' == $_REQUEST['display'] ) {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'display' => 'old' ,'msg' => 's' ), 'admin.php' ) );
                    } else {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 's' ), 'admin.php' ) );
                    }
                    break;

                //action for delete file category
                case 'delete_file_category':
                    if ( isset( $_REQUEST['reassign_cat_id'] ) && 0 < $_REQUEST['reassign_cat_id'] )
                        WPC()->files()->reassign_files_from_category( $_REQUEST['cat_id'], $_REQUEST['reassign_cat_id'], true );

                    WPC()->files()->delete_file_category( $_REQUEST['cat_id'] );

                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'd' ), 'admin.php' ) );
                    break;

                //action for reassing files from one category to another
                case 'reassign_files_from_category':
                    WPC()->files()->reassign_files_from_category( $_REQUEST['old_cat_id'], $_REQUEST['new_cat_id'] );

                    if( isset( $_REQUEST['tab'] ) && 'files_categories' == $_REQUEST['tab'] ) {
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files_categories', 'msg' => 'reas' ), 'admin.php' ) );
                    }
                    break;

                //action approve client
                case 'client_approve':
                    if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpc_client_approve' ) ) {
                        if( isset( $_REQUEST['wpc_circles'] ) && !empty( $_REQUEST['wpc_circles'] ) ) {
                            if( $_REQUEST['wpc_circles'] == 'all' ) {
                                $groups_id = WPC()->groups()->get_group_ids();
                            } else {
                                $groups_id = explode( ',', $_REQUEST['wpc_circles'] );
                            }
                        } else {
                            $groups_id = array();
                        }

                        $client_ids = explode( ',', $_REQUEST['client_id'] );
                        if( is_array( $client_ids ) ) {
                            foreach( $client_ids as $client_id ) {
                                WPC()->members()->client_approve( $client_id, $groups_id );
                            }
                        } else {
                            WPC()->members()->client_approve( $client_ids, $groups_id );
                        }
                        WPC()->redirect( add_query_arg( array( 'page' => 'wpclient_clients', 'tab' => 'approve', 'msg' => 'a' ), 'admin.php' ) );
                    }
                    break;

                case 'reassign_category':
                    if( isset( $_REQUEST['category_type'] ) && !empty( $_REQUEST['category_type'] ) ) {
                        WPC()->categories()->reassign_object_from_category( $_REQUEST['category_type'], $_REQUEST['old_cat_id'], $_REQUEST['new_cat_id'] );
                    }
                    break;

                case 'relogin':
                    if( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
                        $id = $_GET['id'];
                        if( !( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'relogin' . get_current_user_id() . $id ) ) ) {
                            exit('Wrong nonce');
                        }
                    } else {
                        if( isset( $_GET['page_name'] ) && 'portal_page' == $_GET['page_name'] && !empty( $_GET['page_id'] ) ) {
                            if( !( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'relogin' . get_current_user_id() . $_GET['page_id'] ) ) ) {
                                exit('Wrong nonce');
                            }
                            $users = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $_GET['page_id'], 'client' );
                            $page_circles = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $_GET['page_id'], 'circle' );
                            foreach ( $page_circles as $group_id ) {
                                $add_client = WPC()->groups()->get_group_clients_id( $group_id );
                                $users = array_merge( $users, $add_client );
                            }
                            $users = array_unique( $users );

                            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' )  ) {
                                //show only manager clients
                                $clients = WPC()->members()->get_all_clients_manager();
                                $users = array_intersect( $users, $clients );
                            }

                            if( isset( $_GET['client_id'] ) ) {
                                if( in_array( $_GET['client_id'], $users ) ) {
                                    $id = $_GET['client_id'];
                                } else {
                                    exit('Access denied');
                                }
                            } else {
                                if( count( $users ) ) {
                                    list( $id ) = array_values( $users );
                                } else {
                                    if( !empty( $_GET['referer_url'] ) ) {
                                        WPC()->redirect( add_query_arg( 'msg', 'empty_clients', $_GET['referer_url'] ) );
                                    } else {
                                        WPC()->redirect( add_query_arg( 'msg', 'empty_clients', get_admin_url(). 'admin.php?page=wpclient_clients' ) );
                                    }
                                }
                            }
                        } else {
                            WPC()->redirect( add_query_arg( 'msg', 'id', get_admin_url(). 'admin.php?page=wpclient_clients' ) );
                        }
                    }

                    $key = wp_generate_password(20);

                    $array = array(
                        'key' => md5( $key ),
                        'client_id' => $id,
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'end_date' => time() + 1800,
                    );
                    if( !empty( $_GET['referer_url'] ) ) {
                        $array['return_url'] = $_GET['referer_url'];
                    }
                    update_user_meta( get_current_user_id(), 'wpc_client_admin_secure_data', $array );

                    wp_set_auth_cookie( $id, true );

                    $cookie_expiration = time() + 48*3600;
                    WPC()->setcookie( "wpc_key", $key, $cookie_expiration );

                    if( !empty( $_GET['page_name'] ) ) {
                        if( 'portal_page' == $_GET['page_name'] && !empty( $_GET['page_id'] ) ) {
                            WPC()->redirect( get_permalink( $_GET['page_id'] ) );
                        } else if( 'hub' == $_GET['page_name'] ) {
                            WPC()->redirect( WPC()->get_hub_link() );
                        }
                    } else {
                        $url = apply_filters( 'login_redirect', '', '', get_userdata( $id ) );
                        WPC()->redirect( $url );
                    }
                    break;

            }
        }

        return '';
    }


    function parent_page_func() {
        //delete files for not apache server
        if ( isset( $_GET['act'] ) && 'delete_file' == $_GET['act'] && isset( $_GET['id'] ) && 0 < $_GET['id'] ) {
            $user_id    = WPC()->checking_page_access();
            global $wpdb;

            //access to delete another files
            if ( current_user_can( 'wpc_delete_assigned_files' ) ) {
                WPC()->files()->delete_file( $_GET['id'] );
            }
            if ( current_user_can( 'wpc_delete_uploaded_files' ) ) {
                $file = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wpc_client_files WHERE id='%d' AND user_id='%d'", $_GET['id'], $user_id ) );
                if ( $file ) WPC()->files()->delete_file( $_GET['id'] );
            }

            if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                //redirect on previos page
                WPC()->redirect( $_SERVER['HTTP_REFERER'] );
            } else {
                //on hub page
                WPC()->redirect( WPC()->get_hub_link() );
            }
        }

    }


    /*
    * fix for wpautop in templates
    */
    function remove_autop_template_content( $init, $editor_id = -1 ) {
        $init['apply_source_formatting'] = true;
        //$init['wpautop'] = false;
        $init['remove_linebreaks'] = false;
        return $init;
    }


    /*
    * Add Gutenberg shortcodes block
    */
    function gutenberg_add_shortcode_block() {
        wp_register_script( 'wp-client/add-shortcode-block', WPC()->plugin_url . 'js/gutenberg/wpc_add_shortcode_block.js', array('wp-blocks', 'wp-element') );

        if( function_exists( 'register_block_type') ){
            register_block_type( 'wp-client/add-shortcode-block', array(
                'editor_script'		 => 'wp-client/add-shortcode-block',
            ) );
        }
    }



    /*
    * fix for wpautop in templates
    */
    function filter_template_content( $content ) {
        if ( ! function_exists( 'wpc_formatting_template' ) ) {
            function wpc_formatting_template( $content ) {
                $content = addslashes( htmlspecialchars( $content, ENT_NOQUOTES ) );
                return stripslashes( "$content" );
            }
        }

        add_filter( 'the_editor_content', 'wpc_formatting_template', 90 );

        return $content;
    }


    /*
    * Add MCE button for plugin's shortcodes
    */
    function add_mce_button_shortcodes() {
        if ( ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_view_tinymce_button' )
                || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )
            ) && 'true' == get_user_option( 'rich_editing' ) ) {
            add_action( 'media_buttons', array( $this, 'add_button_shortcodes_to_editor' ), 1 );
            add_action( 'wp_enqueue_media', array( $this, 'include_button_shortcodes_js_files' ) );
        }
    }

    /**
     * Added admin notices here
     *
     */
    function permissions_show_notice() {
        //check WP uploads dir on "writable"
        $dir = WPC()->get_upload_dir( 'wpclient' );
        if( !( @is_dir( $dir ) && wp_is_writable( $dir ) ) ) {
            $message = sprintf( __( '%s upload directory for files "<i>' . $dir . '"</i> does not exist or is not writable.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] );
            WPC()->notices()->add_notice( $message, 'error', 'all_pages' );
        }
    }


    /**
     * Setup hiding wp-admin
     **/
    function setup_hide_admin() {

        //display wizard
        if ( isset( $_GET['page'] ) && 'wpc_setup_wizard' == $_GET['page'] ) {
            if ( current_user_can('wpc_admin') || current_user_can('administrator') ) {
                add_action( 'admin_init', array( WPC()->hooks(), 'WPC_Setup_Wizard->wpc_setup_wizard' ) );
            }
        }

        $wpc_common_secure = WPC()->get_settings( 'common_secure' );

        $hide_admin = ( isset( $wpc_common_secure['hide_admin'] ) && !empty( $wpc_common_secure['hide_admin'] ) ) ? $wpc_common_secure['hide_admin'] : 'no';

        // Nope, for defaut permalinks.
        if( !WPC()->permalinks )
            return;

        // Nope, they didn't enable it.
        if ( !isset( $wpc_common_secure ) || 'yes' != $hide_admin )
            return;

        // We only will hide it if we are in admin (/wp-admin/)
        if ( is_admin() ) {
            // Non logged in users.
            if ( !is_user_logged_in() )
                WPC()->throw_404();
        }
    }


    function remove_other_notices() {

        global $parent_file;
        if ( isset( $parent_file ) && 'wpclients' == $parent_file ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
            add_action( 'admin_notices', array( &$this, 'admin_notices_hook' ) );
        }

        add_action( 'admin_notices', array( &$this, 'admin_notices_hook_all_pages' ) );
    }


    function show_wpc_notices() {
        $notices = WPC()->get_settings( 'notices' );

        if ( empty( $notices ) ) {
            return;
        }

        foreach ( $notices as $key => $notice ) {
            ?>
            <div data-key="<?php echo $key ?>" class="wpc_notices notice notice-warning wpc_notice fade is-dismissible">
                <p> <?php echo $notice ?> </p>
            </div>
            <?php
        }

        ?>
        <script>
            jQuery(document).ready( function() {

                jQuery('.wpc_notices').on('click', '.notice-dismiss', function() {
                    var key = jQuery( this ).parent().data('key');

                    if ( key !== 'undefined' ) {
                        jQuery.ajax({
                            type     : 'POST',
                            dataType : 'json',
                            url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                            data     : 'action=wpc_delete_notice&key=' + key,
                            success  : function(){
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }



    /*
    * check download action
    */
    function _check_download() {
        if ( isset( $_GET['wpc_action'] ) && in_array( $_GET['wpc_action'], array( 'download', 'view' ) ) && isset( $_GET['id'] ) ) {
            if( empty( $_GET['module'] ) ) {
                WPC()->files()->core_file_downloader( $_GET['id'] );
            } else {
                do_action( 'wpc_' . $_GET['module'] . '_file_downloader', $_GET['id'] );
            }
        }

        if ( isset( $_GET['wpc_action'] ) && $_GET['wpc_action'] == 'download_tpl' ) {
            WPC()->admin()->download_edited_tpls();
        }
    }


    function wpc_set_options( $status, $option, $value ) {
        $wpc_screen_options_for_clients = array(
            'wpc_clients_per_page',
            'wpc_approve_clients_per_page',
            'wpc_convert_users_per_page',
            'wpc_staffs_per_page',
            'wpc_approve_staffs_per_page',
            'wpc_clients_archive_per_page',
            'wpc_admins_per_page',
            'wpc_managers_per_page',
            'wpc_portal_pages_per_page',
            'wpc_portal_page_categories_per_page',
            'wpc_portalhubs_per_page',
            'wpc_files_per_page',
            'wpc_file_categories_per_page',
            'wpc_file_tags_per_page',
            'wpc_file_downloads_per_page',
            'wpc_circles_per_page',
            'wpc_tags_per_page',
            'wpc_payments_per_page',
        );

        $wpc_screen_options_for_clients = apply_filters( 'wpc_screen_options_pagination', $wpc_screen_options_for_clients );

        if ( in_array( $option, $wpc_screen_options_for_clients )  )
            return $value;

        return $status;
    }


    function add_screen_help () {

        if ( WPC()->plugin['hide_help_menu'] )
            return '';

        $array_help = array();

        if ( isset( $_GET['page'] ) ) {
            $tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : '' ;
            $method = "_add_" . $_GET['page'] . $tab . "_page_help";

            switch( $method ) {
                case '_add_wpclients_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From the %s Dashboard you can get a "snapshot" view of your current plugin setup. See general info like your current number of %s, number of unread messages, and the total size of all uploaded files. Also get a quick glance at important settings, such as whether %s self-registration is enabled, and if you have custom login setup. Additionally, depending on your current active extensions, you can also see your current number of outstanding and past due invoices, and their totals.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], WPC()->custom_titles['client']['p'] , WPC()->custom_titles['client']['s'] ) . '</p>',
                                ),
                                array(
                                    'id' => 'dr-links',
                                    'title' => __( 'LINKS', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'Also listed on this page are the links to the plugin\'s main site, our translation portal where you can view and download the plugin\'s most recent translation files, and to our helpdesk system. From our support link you can view common questions and answers, find the most up-to-date help documentation available, and submit a support ticket.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>'
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclientssystem_status_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From the "System Status" tab you can see an overview of your installation info. This includes server info, PHP settings, your current installed plugins, and other general useful information. If you\'re ever unsure about what version of %s you are running, or what your PHP.ini settings are, you can check that all here.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</p>',
                                ),
                                array(
                                    'id' => 'dr-links',
                                    'title' => __( 'LINKS', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'Also listed on this page are the links to the plugin\'s main site, our translation portal where you can view and download the plugin\'s most recent translation files, and to our helpdesk system. From our support link you can view common questions and answers, find the most up-to-date help documentation available, and submit a support ticket.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>'
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_permissions_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'This Permissions Report tool is designed to show comprehensive reports of the assigned permissions given based on %s or %s affiliation, and by what path those permissions were assigned. This helps you ensure that your permissions are set up the way you intended and helps identify and possible accidental assignment of permissions that you did not intend.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['s'] ) . '</p>',
                                ),
                                array(
                                    'id' => 'dr-methods',
                                    'title' => __( 'METHODS', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'You can use this tool in two ways.
    1. Show the resources that are permissioned to any %1$s or %2$s by selecting %1$s or %2$s from first select box, and then selecting a specific %1$s or %2$s from the select box below that. Then, you can make your selection of all or specific resources from the select box on the right and click "Report"
    Click the directional arrow button in the middle to change to Method 2
    2. Select a resource type from the top select box, and then use the 2nd select box to choose a specific resource from that category. Then, use the select box on the right to choose to view permissions based on %1$s or %2$s assignment.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000027770-permissions" target="_blank">' . __( 'Permission Report Documentation', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>'
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clients_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-overview',
                                    'title' => __( 'OVERVIEW', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'On this page you will find all of the basic information about your current %1$s, including their username, contact name, business name, email address, and creation date. You can also assign %1$s to %2$s from this page, as well as send them a private message, and perform other actions.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'] ) . '</p>',
                                ),
                                array(
                                    'id' => 'dr-available_actions',
                                    'title' => __( 'AVAILABLE ACTIONS', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you hover over an individual %1$s, you can perform advanced actions: "Edit" takes you to a page where you can change the %1$s\'s info, such as updating their email address or phone number. "View" brings up a lightbox where you can view all of the data associated with a %1$s, without the ability to edit the info. Choosing to "Archive" will disable a %1$s\'s login info, making their HUB Page inaccessible, but the %1$s and their\' associated info will remain in the database until permanently deleted. "Files" will display a list of files associated with that %1$s, both uploaded and assigned. "Messages" will display all private messages to and from that %1$s. "Internal Notes" will bring up a lightbox, allowing you write notes about the %1$s that are only viewable by %2$s and %3$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['admin']['p'], WPC()->custom_titles['manager']['p'] ) . '</p>'
                                ),
                                array(
                                    'id' => 'dr-importing_clients',
                                    'title' => __( 'IMPORTING CLIENTS', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you have an existing list of %1$s that you would like to add as %1$s into your site, you can do so using the "Import %1$s from CSV File" option. You will need all of your %1$s in a specifically formatted CSV file (see documentation link to the right). When importing %1$s, you also have the option of "bulk" assigning them to a %2$s. You can choose to skip this step, and assign the %1$s to %3$s later after importing. Additionally, we suggest testing the import with a sample CSV with only a few %1$s, to verify that the process is giving you the desired result.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'], WPC()->custom_titles['circle']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002792-importing" target="_blank">' . sprintf( __( 'Importing %s Documentation', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</a></p>'
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsadd_client_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you would like to add a %1$s manually, you can do so from this page. You can fill out all of the appropriate fields, set their password, and choose to notify the %1$s about their new account by checking the box for "Send this password to the new user by email".
    <br />
    <br />
    Please note that all default fields except "Phone" are required, and the "Business Name" and "Username" fields cannot be changed after the %1$s is created. Also, the "Username" and "Email" fields must be unique to each user in your installation, both inside and outside %2$s. This is a WordPress limitation, and applies to all users in your installation.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000030081-wp-client" target="_blank">' . __( 'WP-Client Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsapprove_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you have %1$s self-registration turned on, and automatic approval turned off, you will need to approve any %2$s who register themselves before they will be able to login and access their HUB. From this page, you can "View" the %1$s\'s details to verify their info meets your requirements, and subsequently "Approve" or "Delete" them. You can also approve or delete several %2$s at once using the "Bulk Actions" options.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000030081-wp-client" target="_blank">' . __( 'WP-Client Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsconvert_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you have users already created in your site, you can easily "convert" them to %1$s roles from this page. You can choose to fully convert them to %1$s roles, or optionally choose to "Save Current User Role". This will essentially "add-on" the %1$s role permissions to the user\'s current role, giving their login "dual purpose". We suggest testing the conversion process first with only a few %2$s, to verify that you get the desired result.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000030081-wp-client" target="_blank">' . __( 'WP-Client Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsstaff_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Any users assigned the role of %1$s\'s %2$s will appear on this page. The %1$s\'s %2$s role is generally reserved for users who work in the same organization as the corresponding %1$s. For example, you may have your %1$s "Bob", and Bob has employees "Sue" and "Joe". Sue and Joe can be assigned as %1$s\'s %2$s, allowing them to have the same access as %1$s Bob, but using unique logins and passwords.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['staff']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002414-explanation-of" target="_blank">' . __( 'WP-Client Roles', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsstaff_add_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this page a site admin can manually add %1$s\'s %3$s users and assign them to %2$s. If you would like to allow %2$s to register their own %1$s\'s %3$s users, you can do so from the "%2$s/%3$s" tab in settings. NOTE: The "Username" and "Email" fields must be unique to each user in your installation, both inside and outside %4$s. This is a WordPress limitation, and applies to all users in your installation.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['s'], WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002414-explanation-of" target="_blank">' . __( 'WP-Client Roles', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsstaff_approve_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you allow your %2$s to register their own %1$s\'s %3$s users, those %3$s accounts will appear on this page. Any %1$s\'s %3$s accounts who have been registered by %2$s will need to be approved by a site admin before the %3$s\'s account will be active. If you would like to allow %2$s to register their own %1$s\'s %3$s users, you can do so from the "%2$s/%3$s" tab in settings.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['s'] ) . '</p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientscustom_fields_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you would like to collect more information than what is natively done by %2$s, you can use Custom Fields. This feature allows you to associate new data fields with your %1$s, allowing you to gather additional information. For instance, each of %1$s may have a unique business ID you would like to keep track of, or perhaps you would like to collect a mailing address to use for billing purposes. Any quantifiable data that you need collect can most likely be done using Custom Fields.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000007181-custom" target="_blank">' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsarchive_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'If you choose to "Archive" a %1$s from the "%2$s" tab, they will be moved to this page. From this page, an archived %1$s account can be restored, or permanently deleted using the appropriate options. NOTE: Once a %1$s account has been deleted, it cannot be recovered.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'] ) . '</p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsmanagers_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Any WPC-Managers that exist in your installation will appear on this page. This role allows you to assign a specific group of %2$s to one "%3$s" within your organization. You can assign WPC-Managers to existing %2$s and/or %1$s from this page, as well edit and delete existing WPC-Managers.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['manager']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002414-explanation-of" target="_blank">' . __( 'WP-Client Roles', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsmanagers_add_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this page a site admin can manually add WPC-Manager users, and assign them to %2$s and/or %1$s. Additionally, you can choose to have all future %2$s automatically assigned to this new WPC-Manager, by checking the appropriate box. NOTE: The "Username" and "Email" fields must be unique to each user in your installation, both inside and outside %3$s. This is a WordPress limitation, and applies to all users in your installation.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['p'], WPC()->plugin['title'] ) . '</p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclient_clientsadmins_page_help' :
                case '_add_wpclient_clientsadmins_add_page_help' :
                case '_add_wpclient_clientsadmins_edit_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Any WPC-Admin users that exist in your install will appear on this page. The WPC-Admin role has full "%2$s" control over %3$s, but has no access to any other part of your WordPress %2$s dashboard. This allows you to easily delegate the %1$s portal duties of your site to a user, without giving them more access than you are comfortable with.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] , WPC()->custom_titles['admin']['s'], WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002414-explanation-of" target="_blank">' . __( 'WP-Client Roles', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_adminsadd_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this page a site %s can manually add a WPC-Admin user. NOTE: The "Username" and "Email" fields must be unique to each user in your installation, both inside and outside %s. This is a WordPress limitation, and applies to all users in your installation.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['admin']['s'], WPC()->plugin['title'] ) . '</p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_contentcircles_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( '%4$s allow you to group %2$s together into different categories. This gives you more flexibility when managing a large %1$s base. Possibly you have %2$s who work for different companies that you service, or maybe you have employees in multiple offices that you would like to segment. From this page you can manage your existing %4$s, including what %2$s are assigned to each, and create new %4$s. Settings include choosing to auto-select %4$s when adding new Portal Pages, auto-adding new %2$s to %4$s, and assigning all existing %2$s to a new %3$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'], WPC()->custom_titles['circle']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028349-" target="_blank">' . WPC()->custom_titles['circle']['p'] . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_contentfiles_categories_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'File Categories allow you to easily organize the files that are uploaded within %5$s, either from the backend dashboard, or from the frontend %1$s HUB Page. Additionally, you can easily "bulk assign" files, by adding files to a particular category, and assigning that File Category to %2$s/%4$s. Once a %1$s/%3$s is assigned to a File Category, any files that are added to that category will be automatically assigned to the corresponding %2$s/%4$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'], WPC()->custom_titles['circle']['p'], WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028673-file" target="_blank">' . __( 'File Sharing Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_contentfiles_page_help' :
                    $array_help =  array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this page, you can upload, delete, and assign files for your %2$s and corresponding %1$s. You can choose to upload files through the traditional file uploader, assign files via FTP, and/or assign existing external files from services like Dropbox or Amazon S3. You can also adjust file permissions for %2$s and %1$s, and delete previously uploaded files.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028673-file" target="_blank">' . __( 'File Sharing Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_contentprivate_messages_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'All of your sent and received private messages will appear on this page. If you are logged in as the site admin, this will include messages sent to and from %2$s, %3$s, and %4$s. You can filter the messages that appear using the "New", "Yesterday", "30 days", and "3 months" tabs. You can also send private messages to %2$s and/or %1$s from this page.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['manager']['p'], WPC()->custom_titles['admin']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028676-private" target="_blank">' . __( 'Private Messaging Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_payments_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'The payments tab is simply a payment history where you can see all payments made by %s in your installation, including paid invoices, and registrations, by what method, and the associated payment information for each.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_templates_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'From here you can edit the template of the newly created Portal Pages. You can use custom HTML here, or design the Portal Page using the Visual Editor.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028681-" target="_blank">' . __( 'Templates Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;


                case '_add_wpclients_templatesemails_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From here you can edit the templates of each of the email notifications that are sent by %s. Use placeholders to dynamically insert information into the templates, giving the emails a personalized feel. ( Please refer to Placeholder table for specific items and uses)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028681-" target="_blank">' . __( 'Templates Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;


                case '_add_wpclients_templatesphp_templates_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'Shortcode Templates allow advanced users to modify the actual output of shortcodes. Advanced users only should attempt changes here. Please only edit html, and don\'t change anything inside curly brackets {} If you run into a problem, then please click "Reset to default" button at bottom right.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028681-" target="_blank">' . __( 'Templates Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;


                case '_add_wpclients_extensions_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'You can view all of your current available from this page, included what extensions are installed and activated. You can install, activate and/or deactivate all of your available extensions from this menu, as well as view the API keys for each extension.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                //two copy for general settings
                case '_add_wpclients_settings_page_help' :
                case '_add_wpclients_settingsgeneral_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'You can adjust various general settings from this tab, including navigation settings, and email notifications.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsclients_staff_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'On this page you can adjust settings related to your %2$s and their %3$s, including allowing %1$s self-registration, allowing %2$s to reset their password, and turning on a Captcha form on the %1$s Registration Form.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsfile_sharing_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this tab you can adjust various file sharing settings, such as turning on/off the more advanced HTML5 file uploader, and setting a filesize upload limit for your %s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsbusiness_info_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'This section is where you will add information about the business which %s represents. The logo from here will be used in the Estimates/Invoicing component as well as the business information. Placeholders to represent each of the fields are noted.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingspages_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Here is where you will assign pages for %1$s to use for it\'s components. The core %1$s pages need selecting so that the plugin knows where they are. These pages should have been created upon installation of the plugin. If not, you will need to create and assign them. If you have successfully chosen a page, and %1$s sees the proper shortcode on that page, you will end up with a green check beside that selection.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002295-hub-and-portal-pages-have-menus-on" target="_blank">' . __( 'Theme Link Pages FAQ', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' ,
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingscustom_titles_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Use this section to change the standard titles of %s to something that applies to your particular installation better.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingscapabilities_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Use this section to select which capabilities will be granted to each role within %3$s. For example, you can choose to allow %2$s the ability to delete files are assigned to them, or view and modify their %1$s Profile, and allow WPC-Managers to create and assign invoices to their %2$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingscustom_login_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( '%1$s furthers it\'s highly customizable platform by allowing you to modify the Login Screen your %2$s will see when they login to their HUB and Portal Pages. This gives your site a more professional appearance. You can choose whether or not you want to use this feature, but %1$s has it enabled by default.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsdefault_redirects_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'This setting allows you to define custom URLs to which different users will be redirected upon successful login and logout. By default, a %s is setup with a definition to redirect them to their Hub Page upon successful login. You can change that redirect here. You can choose to setup login/logout redirects for all users, for all users of a certain role, or for specific users, depending on your particular need. NOTE: If you setup custom login/logout redirects, be sure that both LOGIN and LOGOUT are correctly filled in. If one is left blank, an error will occur upon login or logout.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingslogin_alerts_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'If you\'re in need of login auditing for your website, you can setup email alerts in this section. Enter your email address and turn Successful or Failed Logins on or off to begin receiving these alerts.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsemail_sending_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'Use the options on this page to setup SMTP for all outgoing emails from your installation. This is useful if you are finding the %s email notifications sent from your domain are being filtered out as spam by your %s/Members. Use the "Test email" box to enter a working email address, and click "Test" to send a test email to that address using your entered SMTP settings.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000032979-" target="_blank">' . __( 'SMTP Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsgateways_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this tab you can manage your current payment gateways, including setting up and maintaining your current settings. %s currently has PayPal, Authorize.Net, and Stripe supported, with plans to add more in the coming months.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingsabout_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'This will show you what version of %s you have installed, as well as all of the legal policies, terms, and disclaimers of the product.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_settingseasy_mode_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'This will show you what version of %s you have installed, as well as all of the legal policies, terms, and disclaimers of the product.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028677-" target="_blank">' . __( 'Settings Overview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;

                case '_add_wpclients_contentclient_page_categories_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'You can choose to create %1$s Categories, and assign %2$s to them. This allows you to "bulk assign" multiple %2$s at once, by simply assigning a category to a %3$s/%5$s, instead of individual %2$s. Once a %3$s/%5$s is assigned to a %1$s Category, any %2$s that are added to that category will be automatically assigned to the corresponding %4$s/%6$s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'], WPC()->custom_titles['portal_page']['p'], WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['s'], WPC()->custom_titles['circle']['p'] ) . '</p>',
                                ),
                            ),
                        'sidebar' => '',
                        'clear' => true
                    ) ;
                    break;


                case '_add_wpclients_content_page_help' :
                    if ( !isset( $_GET['filter_status'] ) || 'publish' == $_GET['filter_status'] ) {
                        $array_help = array(
                            'tabs' =>
                                array(
                                    array(
                                        'id' => 'dr-main',
                                        'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                        'content' => '<p>' . sprintf( __( 'Any %1$s that has been created will appear on this page. This includes %2$s that are automatically created when a new %3$s is added (this action can be turned off in "%4$s/%5$s" settings), as well as any %2$s that are manually created by %5$s. From this page, you can edit the various %1$s contents, adjust %3$s and %7$s assignments, and delete existing pages. New %2$s can be added by clicking "Add New", and %1$s Categories can be adjusted by selecting "Categories".', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'], WPC()->custom_titles['portal_page']['p'], WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'], WPC()->custom_titles['admin']['p'], WPC()->custom_titles['circle']['s'] ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028674-portal" target="_blank">' . sprintf( __( '%s Basics', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</a></p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002916-hub-page-vs-portal" target="_blank">' . sprintf( __( 'HUB Page VS. %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</a></p>',
                                    ),
                                ),
                            'sidebar' => '',
                            'clear' => true
                        ) ;
                    } elseif ( 'trash' == $_GET['filter_status'] ) {
                        $array_help = array(
                            'tabs' =>
                                array(
                                    array(
                                        'id' => 'dr-main',
                                        'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                        'content' => '<p>' . sprintf( __( 'Any %2$s that you manually delete will appear on this page. You can choose to restore the %1$s, or delete it permanently. Any %3$s/%4$s assignments will also be restored when a %1$s is restored.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'], WPC()->custom_titles['portal_page']['p'], WPC()->custom_titles['client']['s'], WPC()->custom_titles['circle']['s'] ) . '</p>',
                                    ),
                                ),
                            'sidebar' => '',
                            'clear' => true
                        );
                    }
                    break;

                case '_add_wpclients_contentportalhubs_page_help' :

                    if ( !isset( $_GET['filter_status'] ) || 'publish' == $_GET['filter_status'] ) {
                        $array_help = array(
                            'tabs' =>
                                array(
                                    array(
                                        'id' => 'dr-main',
                                        'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                        'content' => '<p>' . sprintf( __( 'From this page you can view all of your %s\'s existing HUB Pages, and choose to delete them or edit their content. The actual content of the HUB Page will depend on the particular type of HUB system you are using. (Please refer to links to the right)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000028674-portal" target="_blank">' . sprintf( __( '%s Basics', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</a></p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000002916-hub-page-vs-portal" target="_blank">' . sprintf( __( 'HUB Page VS. %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) . '</a></p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000015049-designing-the-hub" target="_blank">' . __( 'Designing the HUB Page', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                    ),
                                ),
                            'sidebar' => '',
                            'clear' => true
                        ) ;
                    } elseif ( 'trash' == $_GET['filter_status'] ) {
                        $array_help = array(
                            'tabs' =>
                                array(
                                    array(
                                        'id' => 'dr-main',
                                        'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                        'content' => '<p>' . sprintf( __( 'Any HUB Pages that you manually delete will appear on this page. You can choose to restore the HUB Page, or delete it permanently. NOTE: If you delete a %s account, their corresponding HUB Page will automatically be deleted. It will not appear in the Trash.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>',
                                    ),
                                ),
                            'sidebar' => '',
                            'clear' => true
                        ) ;
                    }
                    break;


                default:
                    $array_help = array();
                    break;
            }

            $array_help = apply_filters( 'wpc_set_array_help', $array_help, $method );

            if( !empty( $array_help ) ) {
                $screen = get_current_screen();
                if ( !is_object( $screen ) )
                    return false;


                $clear = ( @$array_help['clear'] );
                if ($clear) $screen->remove_help_tabs();

                $screen->set_help_sidebar( @$array_help['sidebar'] );

                if( isset( $array_help['tabs'] ) ) {
                    foreach ( $array_help['tabs'] as $tab ) {
                        if( isset( $tab['cap'] ) && !current_user_can( $tab['cap'] ) ) {
                            continue;
                        }
                        $screen->add_help_tab( $tab );
                    }
                }

            }
        }

        return '';
    }


    function the_editor_filter( $content ) {
        add_filter('admin_footer', array( WPC()->hooks(), 'WPC_Assigns->admin_footer_popup' ) );
        return $content;
    }


    function add_rating( $text ) {
        if( isset( $_GET['page'] ) && 0 === strpos( $_GET['page'] , 'wpclient' ) ) {

            $rating = get_option( 'wpc_client_rate' );

            if( !$rating ) {
                //shutterbox init
                wp_enqueue_script('wpc-shutter-box-script');
                wp_enqueue_style('wpc-shutter-box-style');

                ob_start(); ?>

                <script type="text/javascript">
                    jQuery( document ).ready( function() {
                        var link = jQuery(".wpc_rating_link");
                        var star = 0;

                        link.shutter_box({
                            view_type       : 'lightbox',
                            type            : 'inline',
                            width           : '500px',
                            href            : '#wpc_rating_popup',
                            title           : "<?php echo esc_js( __( 'Please rate WP-Client', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                            onClose         : function() {
                                if( star == 4 || star == 5 ) {
                                    setTimeout( function() {
                                        window.open( 'https://wordpress.org/support/view/plugin-reviews/web-portal-lite-client-portal-secure-file-sharing-private-messaging?filter=5#postform', '_blank' );
                                    }, 500 );
                                } else {
                                    setTimeout( function() {
                                        window.open( 'https://webportalhq.com', '_blank' );
                                    }, 500 );
                                }
                            }
                        });

                        jQuery( ".star_rating .wpc_ratings_stars" ).mouseover( function() {
                            var current_index = jQuery(this).index( "div.wpc_ratings_stars" );
                            jQuery( ".wpc_ratings_stars" ).each( function() {
                                if( jQuery(this).index( "div.wpc_ratings_stars" ) <= current_index ) {
                                    jQuery(this).addClass( "wpc_star_active" );
                                }
                            });
                        });


                        jQuery( ".star_rating" ).mouseout( function() {
                            jQuery( ".wpc_ratings_stars" ).removeClass( "wpc_star_active" );
                        });


                        jQuery( ".wpc_ratings_stars" ).click( function() {
                            star = jQuery(this).data( "star" );

                            jQuery( ".star_rating" ).html( '<div class="ajax_sort_loading"></div>' );

                            link.shutter_box('close');

                            jQuery.ajax({
                                type: "POST",
                                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                                data: 'action=wpc_set_rating',
                                dataType: 'json',
                                success: function( data ){
                                    jQuery( "#footer-thankyou" ).remove();
                                }
                            });
                        });
                    });
                </script>

                <span id="footer-thankyou">
                        <?php _e( 'How are you liking WP-Client? Give us a rating: ', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <a class="wpc_rating_link" href="#wpc_rating_popup" style="font-style: normal;">&#9733;&#9733;&#9733;&#9733;&#9733;</a>
                    </span>

                <div style="display: none;">
                    <div id="wpc_rating_popup" style="clear: both;">
                        <div class="star_rating" style="margin-top:20px;">
                            <div data-star="1" class="wpc_ratings_stars"></div>
                            <div data-star="2" class="wpc_ratings_stars"></div>
                            <div data-star="3" class="wpc_ratings_stars"></div>
                            <div data-star="4" class="wpc_ratings_stars"></div>
                            <div data-star="5" class="wpc_ratings_stars"></div>
                        </div>
                    </div>
                </div>

                <?php $text = ob_get_contents();
                if( ob_get_length() ) {
                    ob_end_clean();
                }

            }
        }

        return $text;
    }


    /**
     * generate advanced settings for locations
     */
    function advanced_settings() {

        //single load for one page
        if ( isset( WPC()->flags['single_load']['advanced_settings'] ) ) {
            return;
        }
        WPC()->flags['single_load']['advanced_settings'] = true;

        $locations = get_registered_nav_menus();
        $nav_menus = wp_get_nav_menus();
        $wpc_data = WPC()->get_settings( 'advanced_locations' );

        $fors = WPC()->admin()->get_type_person_for_location();

        echo '<div id="wpc_advanced_settings" style="display: none;">';
        foreach ( $locations as $location => $name ) {
            echo '<table><tbody>';
            foreach ( $fors as $for => $title_end ) {
                $menu_locations = isset( $wpc_data[ $for ] ) ? $wpc_data[ $for ] : array();
                if ( 'circle' === $for ) {
                    if ( isset( $menu_locations[ $location ] ) ) {
                        foreach ( $menu_locations[ $location ] as $k => $menu_location ) {
                            if ( empty( $menu_locations['wpc_circles_' . $location ][ $k ] ) ) {
                                $menu_locations['wpc_circles_' . $location ][ $k ] = '';
                            }
                            echo WPC()->admin()->get_line_advanced_location( $location, $name, $for, $title_end, $nav_menus,
                                array(
                                    $location => $menu_location,
                                    'wpc_circles_' . $location => $menu_locations['wpc_circles_' . $location ][ $k ],
                                ), $k );
                        }
                    } else {
                        echo WPC()->admin()->get_line_advanced_location( $location, $name, $for, $title_end, $nav_menus,
                            array(
                                $location => '',
                                'wpc_circles_' . $location => array(),
                            ), 0 );
                    }
                } else {
                    echo WPC()->admin()->get_line_advanced_location( $location, $name, $for, $title_end, $nav_menus, $menu_locations );
                }
            }
            echo '</tbody></table>';
        }

        wp_enqueue_script( 'wpc-admin-advanced_menu_settings-js' );

        echo '</div>';
    }


    //update advanced settings
    function load_nav_menus() {
        if ( 'locations' === filter_input( INPUT_GET, 'action' ) ) {
            //update advanced settings for manage locations
            add_action( 'check_admin_referer',  array( WPC()->hooks(), 'WPC_Admin_Functions->update_nav_menu' ), 99, 2 );
        }
    }


    function admin_notices_hook_all_pages() {
        do_action( 'wp_client_admin_notices_all_pages' ) ;
    }


    function admin_notices_hook() {
        do_action( 'wp_client_admin_notices' ) ;
    }


    /*
    * Add shortcodes button to editor
    */
    function add_button_shortcodes_to_editor( $editor_id ) {
        $img = '<img src="' . WPC()->plugin_url . 'client-icon.png' . '">';
        printf( '<button type="button" id="wpc_add_shortcode" class="button" data-editor="%s">%s</button>',
            $editor_id, $img . __( 'Add Shortcode', WPC_CLIENT_TEXT_DOMAIN )
        );
    }


    /*
    * Include js-file for shortcodes button on editor
    */
    function include_button_shortcodes_js_files() {

        WPC()->set_shortcode_data();

        $shortcodes = array();
        foreach( WPC()->shortcode_data as $key=>$val ) {
            $shortcodes[ $key ] = array(
                'title' => isset( $val['title'] ) ? $val['title'] : '',
                'categories' => isset( $val['categories'] ) ? $val['categories'] : '',
                'attributes' => isset( $val['attributes'] ) ? $val['attributes'] : array(),
                'content' => isset( $val['content'] ) ? $val['content'] : '',
                'close_tag' => isset( $val['close_tag'] ) ? $val['close_tag'] : false
            );
        }

        //shutterbox init
        wp_enqueue_script( 'wpc-shutter-box-script', false, array('jquery'), WPC_CLIENT_VER, true );


        wp_enqueue_style('wpc-shutter-box-style');

        wp_enqueue_script( 'wpc-new-assign-popup-js', false, array('jquery'), WPC_CLIENT_VER, true );

        wp_enqueue_script( 'jquery-md5', false, array( 'jquery' ), WPC_CLIENT_VER, true );


        wp_enqueue_script( 'jquery-ui-accordion');

        wp_enqueue_style( 'wpc-add_shortcodes' );

        wp_enqueue_script( 'wpc_media_button', false, array('jquery'), WPC_CLIENT_VER, true );

        wp_localize_script( 'wpc_media_button', 'wpc_var', array(
            'main_title' => __( 'Add Shortcode or Placeholder', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ) );
        wp_localize_script( 'wpc_media_button', 'wpc_shortcodes', $shortcodes );
    }


    function remove_our_query_args_for_or_pages( $removable_query_args ) {
        $removable_query_args[] = 'msg';
        return $removable_query_args;
    }



}

endif;

new WPC_Hooks_Admin();