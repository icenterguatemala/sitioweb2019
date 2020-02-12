<?php

//Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WPC_Admin_Functions' ) ) :

/**
 * functions of WPC_Admin_Functions Class
 *
 * @class WPC_Admin_Functions
 * @version    1.0.0
 */
class WPC_Admin_Functions {


    public $list_table_per_page = 25;

    /**
     * The single instance of the class.
     *
     * @var WPC_Admin_Functions
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Admin_Functions is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Admin_Functions - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /*
     * get plugin logo block on admin area
     *
     * @return string
     */
    function get_plugin_logo_block() {
        $html = '<div class="wpc_logo_hover"><div class="wpc_logo_block"><div class="wpc_logo">' . WPC()->plugin['logo_content'] . '</div></div>';
        ?>


        <?php if ( false === get_option( 'whtlwpc_settings' ) && !isset( $GLOBALS['wpc_wl_admin'] ) ) {


            ob_start();
            ?>


            <script type="text/javascript">
                jQuery(document).ready(function(){

                    jQuery( '.wpc_logo_hover, #wpc_logo_customize' ).mouseover( function(event) {
                        jQuery( '#wpc_logo_customize' ).show();

                    });

                    jQuery( '.wpc_logo_hover, #wpc_logo_customize' ).mouseout( function() {
                        jQuery( '#wpc_logo_customize' ).hide();

                    });

                });
            </script>

            <span id="wpc_logo_customize" style="display: none; position: absolute; top: 0; left: 270px; padding: 55px 30px 20px 40px;" >
                    <span style="background-color: #fff; padding: 5px 10px 5px 10px; border: 2px solid #7AD03A; -webkit-border-radius: 20px 20px 20px 20px; -moz-border-radius: 20px 20px 20px 20px; border-radius: 20px 20px 20px 20px;" >
                        <a href="https://wp-client.com/product/white-label/" target="_blank" ><?php _e( 'Customize this?', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    </span>
                </span>

        <?php

            $html .= ob_get_contents();
            ob_end_clean();

        }
        
        return $html . '</div>' . $this->get_plugin_admin_notices();
    }


    /*
     * get admin notices for plugin
     *
     * @return void
     */
    function get_plugin_admin_notices() {

        $html = '';
        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {

            $settings = get_option( 'whtlwpc_settings');
            if ( !isset( $settings['hide_announcement'] ) || 1 != $settings['hide_announcement'] ) {

                //cache admin_notices for lower requests
                $wpc_admin_notices = WPC()->get_settings( 'admin_notices' );

                if ( !isset( $wpc_admin_notices['time'] ) || ( time() - ( 3600 * 24 ) ) > $wpc_admin_notices['time'] ) {
                    $wpc_admin_notices['time'] =  time();
                    $wpc_admin_notices['contents'] = WPC()->remote_download( "https://wp-client.com/_remote/clients/wpc_admin_notice.txt" );
//                        $wpc_admin_notices['contents'] = WPC()->remote_download( "https://webportalhq.com/_remote/clients/test_wpc_admin_notice.txt" );

                    WPC()->settings()->update( $wpc_admin_notices, 'admin_notices' );
                }

                if ( isset( $wpc_admin_notices['contents'] ) && '' != $wpc_admin_notices['contents'] ) {

                    $wpc_admin_notices['contents'] = str_replace( '{admin_url}', get_admin_url(), $wpc_admin_notices['contents'] );
                    $wpc_admin_notices['contents'] = str_replace( '{plugin_title}', WPC()->plugin['title'], $wpc_admin_notices['contents'] );

                    $wpc_admin_notices['contents'] = json_decode( $wpc_admin_notices['contents'], true );

                    $messages = '';
                    if ( is_array( $wpc_admin_notices['contents'] ) ) {

                        $wpc_dismiss_admin_notice = WPC()->get_settings( 'dismiss_admin_notice' );
                        $wpc_dismiss_admin_notice = is_array( $wpc_dismiss_admin_notice ) ? $wpc_dismiss_admin_notice : array();

                        foreach( $wpc_admin_notices['contents'] as $key=>$content ) {
                            if ( !isset( $content['id'] ) || in_array( $content['id'], $wpc_dismiss_admin_notice ) ) {
                                continue;
                            }


                            $content['last_plugin_version'] = isset( $content['last_plugin_version'] ) ? $content['last_plugin_version'] : '100000000';
                            $content['from_plugin_version'] = isset( $content['from_plugin_version'] ) ? $content['from_plugin_version'] : '0';

                            if ( isset( $content['message'] ) && version_compare( WPC_CLIENT_VER, $content['from_plugin_version'], '>=' ) && version_compare( WPC_CLIENT_VER, $content['last_plugin_version'], '<=' ) ) {
                                $messages .= '<li>' . $content['message'] . '<p class="hide-if-no-js" style="text-align: right;">
                                                <span class="wpc_ajax_loading" style="display:none;"></span>
                                                <a href="javascript:void(0);" data-id="' . $content['id'] . '" class="welcome-panel-close wpc_notice_dismiss">' . __( 'Dismiss', WPC_CLIENT_TEXT_DOMAIN ) . '</a>
                                                </li>';
                            }
                        }
                    }

                    if( !empty( $messages ) ) {

                        ob_start();

                        ?>

                            <style type="text/css">
                                .wpc_slider_notice {
                                    background: #fff;
                                    margin-top: 15px;
                                    border:none !important;
                                    padding:0 !important;
                                    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                                    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                                    height: 120px;
                                }

                                .wpc_slider_counter {
                                    position: absolute;
                                    top:3px;
                                    right:10px;
                                    font-weight: bold;
                                }

                                @media (max-width: 782px) {
                                    .wpc_slider_notice {
                                        height: 190px;
                                    }
                                }

                                .wpc_slider_content ul {
                                    height: 100%;
                                    float: left;
                                    padding: 0;
                                    margin: 0;
                                    position: relative;
                                    list-style: none;
                                    display: block;
                                    box-sizing: border-box;
                                    -moz-box-sizing: border-box;
                                    -webkit-box-sizing: border-box;
                                }

                                .wpc_slider_content li {
                                    height: 100%;
                                    position: relative;
                                    display: inline-block;
                                    margin: 0;
                                    padding: 10px;
                                    box-sizing: border-box;
                                    -moz-box-sizing: border-box;
                                    -webkit-box-sizing: border-box;
                                    vertical-align: top;
                                }
                            </style>

                            <div id="message" class="updated1 notice1 wpc_slider_notice">
                                <div class="wpc_slider_content">
                                    <ul><?php echo $messages ?></ul>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <script type="text/javascript">
                                jQuery( document ).ready( function() {

                                    jQuery( '.wpc_slider_notice' ).wpc_slider({
                                        element: 'ul li',
                                        autoPlay: true,
                                        autoPlayDelay:7,
                                        next_button_label : '<?php echo esc_js( __( 'Next Notice', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                                        prev_button_label : '<?php echo esc_js( __( 'Previous Notice', WPC_CLIENT_TEXT_DOMAIN ) ) ?>',
                                        show_counter : true
                                    });


                                    jQuery( '.wpc_notice_dismiss' ).click( function() {
                                        var link = jQuery( this );
                                        var id = jQuery( this ).attr( 'data-id' );
                                        jQuery( this).siblings('.wpc_ajax_loading').show();
                                        jQuery( this).hide();
                                        jQuery.ajax({
                                            type: 'POST',
                                            dataType    : 'json',
                                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                                            data: 'action=wpc_dismiss_admin_notice&id=' + id,
                                            success: function( json_data ) {
                                                link.parents( 'li' ).fadeOut( 'slow', function() {
                                                    jQuery( this ).remove();
                                                    if( jQuery( '.wpc_slider_notice').find('li').length > 1 ) {
                                                        jQuery( '.wpc_slider_notice' ).wpc_slider( 'rebuild' );
                                                    } else if( jQuery( '.wpc_slider_notice').find('li').length == 0 ) {
                                                        jQuery( '.wpc_slider_notice').hide();
                                                    } else {
                                                        jQuery( '.wpc_slider_notice' ).wpc_slider( 'rebuild' );
                                                        jQuery( '.wpc_slider_notice' ).wpc_slider( 'destroy' );
                                                    }
                                                });

                                            }
                                        });
                                    });


                                });
                            </script>

                    <?php

                        $html .= ob_get_contents();
                        ob_end_clean();

                    }
                }
            }
        }

        return $html;

    }

    /**
     * get array of settings tab
     *
     * @return array
     */
    function get_tabs_of_settings() {
        $tabs = array(
            'general' => array(
                'title'     => __( 'General', WPC_CLIENT_TEXT_DOMAIN ),
                'subtabs'   => array(
                    'global'       => __( 'General', WPC_CLIENT_TEXT_DOMAIN ),
                    'business_info' => __( 'Business Info', WPC_CLIENT_TEXT_DOMAIN ),
                    'currency'      => __( 'Currency', WPC_CLIENT_TEXT_DOMAIN ),
                    'pages'         => __( 'Theme Link Pages', WPC_CLIENT_TEXT_DOMAIN ),
                ),
            ),
            'clients_staff' => array(
                'title'     => ( !WPC()->flags['easy_mode'] ) ? sprintf( __( '%s/%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) : sprintf( __( '%s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
            ),
            'file_sharing' => array(
                'title'     => __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN ),
            ),
            'private_messages' => array(
                'title'     => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
            ),
            'capabilities' => array(
                'title'     => __( 'Capabilities', WPC_CLIENT_TEXT_DOMAIN ),
            ),
            'custom' => array(
                'title'     => __( 'Customization', WPC_CLIENT_TEXT_DOMAIN ),
                'subtabs'   => array(
                    'login'  => __( 'Custom Login', WPC_CLIENT_TEXT_DOMAIN ),
                    'titles' => __( 'Custom Titles', WPC_CLIENT_TEXT_DOMAIN ),
                    'style'  => __( 'Custom Style', WPC_CLIENT_TEXT_DOMAIN ),
                ),
            ),
            'redirects' => array(
                'title'     => __( 'Redirects', WPC_CLIENT_TEXT_DOMAIN ),
            ),
            'email_sending' => array(
                'title'     => __( 'Email Sending', WPC_CLIENT_TEXT_DOMAIN ),
            ),
            'convert_users' => array(
                'title'     => __( 'Convert Users', WPC_CLIENT_TEXT_DOMAIN ),
                'subtabs'   => array(
                    'rules'     => __( 'Convert Rules', WPC_CLIENT_TEXT_DOMAIN ),
                    'defaults'  => __( 'Default Settings', WPC_CLIENT_TEXT_DOMAIN ),
                ),
            ),
            'security' => array(
                'title'     => __( 'Security', WPC_CLIENT_TEXT_DOMAIN ),
                'subtabs'   => array(
                    'common'        => __( 'Common', WPC_CLIENT_TEXT_DOMAIN ),
                    'password'      => __( 'Password Requirements', WPC_CLIENT_TEXT_DOMAIN ),
                    'captcha'       => __( 'Captcha', WPC_CLIENT_TEXT_DOMAIN ),
                    'terms'         => __( 'Terms & Conditions', WPC_CLIENT_TEXT_DOMAIN ),
                    'privacy'       => __( 'Privacy Policy', WPC_CLIENT_TEXT_DOMAIN ),
                    'limit_ips'     => __( 'IP Access Restriction', WPC_CLIENT_TEXT_DOMAIN ),
                    'login_alerts'  => __( 'Login Alerts', WPC_CLIENT_TEXT_DOMAIN ),
                ),
            ),
        );

        $tabs = apply_filters( 'wpc_client_settings_tabs', $tabs );

        //Remove some settings tabs/subtabs for easy mode
        if ( WPC()->flags['easy_mode'] ) {
            $remove_tabs = array(
                'convert_users',
                'currency',
                'capabilities',
                'redirects',
                'style',
                'titles',
                'limit_ips',
                'login_alerts',
            );

            $remove_tabs = apply_filters( 'wpc_client_remove_settings_tabs', $remove_tabs );

            if ( $tabs ) {
                foreach ( $tabs as $key => $tab ) {
                    if ( in_array( $key, $remove_tabs ) ) {
                        unset( $tabs[$key] );
                        continue;
                    }

                    if ( !empty( $tab['subtabs'] ) ) {
                        foreach ( $tab['subtabs'] as $subkey => $subtab ) {
                            if ( in_array( $subkey, $remove_tabs ) ) {
                                unset( $tab['subtabs'][$subkey] );
                            }
                        }
                    }
                }
            }
        }


        asort($tabs);

        //put general tab to 1st place
        if ( !empty( $tabs['general'] ) ) {
            $tmp['general'] = $tabs['general'];
            unset( $tabs['general'] );

            $tabs = $tmp + $tabs;
        }

        //hide about tab
        if ( ! WPC()->plugin['hide_about_tab'] ) {
            $tabs['about'] = array(
                'title'     => __( 'About', WPC_CLIENT_TEXT_DOMAIN ),
            );
        }

        return $tabs;
    }


    /**
     * Gen tabs menu
     *
     * @param string $page
     * @return string
     */
    function gen_tabs_menu( $page = 'clients' ) {
        ob_start();
        ?>
        <style type="text/css">
            h2 {
                padding-left: 5px !important;
            }
            h2 a {
                padding: 6px 9px !important;
            }
        </style>

        <?php
        $tabs = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        $tabs .= '<h2 class="nav-tab-wrapper wpc-nav-tab-wrapper">';

        switch( $page ) {
            case 'clients':
                $clients_tabs = array();

                $clients_tabs[''] = WPC()->custom_titles['client']['p'];

                if ( current_user_can( 'wpc_show_circles' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                    $clients_tabs['circles'] = WPC()->custom_titles['circle']['p'];

                if ( current_user_can( 'wpc_approve_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                    $not_approved_clients   = WPC()->members()->get_excluded_clients( 'to_approve' );
                    $clients_tabs['approve'] = sprintf( __( 'Approve %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . ' ('. count( $not_approved_clients ) . ')';
                }

                if( !WPC()->flags['easy_mode'] ) {
                    if ( current_user_can( 'wpc_add_staff' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $clients_tabs['staff'] = WPC()->custom_titles['client']['s'] . "'s " . WPC()->custom_titles['staff']['s'];

                    if ( current_user_can( 'wpc_approve_staff' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                        $not_approved_staff     = get_users( array( 'role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID', ) );
                        $clients_tabs['staff_approve'] = WPC()->custom_titles['staff']['s'] . ' ' . __( 'Approve', WPC_CLIENT_TEXT_DOMAIN ) . ' ('. count( $not_approved_staff ) . ')';
                    }

                    //just for admin
                    if ( current_user_can( 'administrator' ) )
                        $clients_tabs['admins'] = WPC()->custom_titles['admin']['p'];

                    //just for admin
                    if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $clients_tabs['managers'] = WPC()->custom_titles['manager']['p'];

                    //just for admin
                    if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $clients_tabs['convert'] = __( 'Convert Users', WPC_CLIENT_TEXT_DOMAIN );


                    //just for admin
                    if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_create_custom_fields' ) )
                        $clients_tabs['custom_fields'] = __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN );
                }

                if ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_restore_clients' ) || current_user_can( 'wpc_delete_clients' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                    $archive_client          = WPC()->members()->get_excluded_clients( 'archive' );
                    $clients_tabs['archive'] = __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ) . ' ('. count( $archive_client ) . ')';
                }




                /*our_hook_
                    hook_name: wpc_client_clients_tabs_array
                    hook_title: Clients Tabs
                    hook_description: Hook runs when Clients Tabs init.
                    hook_type: filter
                    hook_in: wp-client
                    hook_location class.admin_menu.php
                    hook_param: array $clients_tabs
                    hook_since: 4.1.3
                */
                $clients_tabs = apply_filters( 'wpc_client_clients_tabs_array', $clients_tabs );

                $current_tab = ( empty( $_GET['tab'] ) || ( isset( $_GET['tab'] ) && ( 'add_client' == $_GET['tab'] || 'edit_client' == $_GET['tab'] ) ) ) ? '' : urldecode( $_GET['tab'] );

                if ( 'admins_add' == $current_tab || 'admins_edit' == $current_tab ) {
                    $current_tab = 'admins';
                } elseif ( 'managers_add' == $current_tab || 'managers_edit' == $current_tab ) {
                    $current_tab = 'managers';
                } elseif ( 'staff_add' == $current_tab || 'staff_edit' == $current_tab ) {
                    $current_tab = 'staff';
                }

                foreach ( $clients_tabs as $name=>$label ) {
                    $active = ( $current_tab == $name ) ? 'nav-tab-active' : '';
                    $get = empty( $name ) ? '' : '&tab=' . $name;

                    $tabs .= '<a href="' . admin_url( 'admin.php?page=wpclient_clients' . $get ) . '" class="nav-tab ' . $active . '">' . $label . '</a>';
                }

                break;

            case 'content':
                $content_tabs = array();

                if ( current_user_can( 'wpc_view_portalhubs' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                    $content_tabs[''] = __( 'Start Pages', WPC_CLIENT_TEXT_DOMAIN );

                if ( current_user_can( 'view_others_clientspages' ) || current_user_can( 'edit_others_clientspages' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                    $content_tabs['portal_page'] = WPC()->custom_titles['portal_page']['p'];
                    $content_tabs['client_page_categories'] = sprintf( __( '%s Categories', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] );
                }

                if ( !WPC()->flags['easy_mode'] ) {
                    if ( current_user_can( 'wpc_show_portal_page_tags' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $content_tabs['tags'] = sprintf( __( '%s Tags', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] );
                }

                if ( current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                    $content_tabs['files'] = __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN );

                if( !WPC()->flags['easy_mode'] ) {
                    if ( current_user_can( 'wpc_show_file_categories' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $content_tabs['files_categories'] = __( 'File Categories', WPC_CLIENT_TEXT_DOMAIN );

                    if ( current_user_can( 'wpc_show_file_tags' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $content_tabs['files_tags'] = __( 'File Tags', WPC_CLIENT_TEXT_DOMAIN );

                    if ( current_user_can( 'wpc_show_download_log' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                        $content_tabs['files_downloads'] = __( 'File Download Logs', WPC_CLIENT_TEXT_DOMAIN );
                }

                if ( ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_view_private_messages' ) ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                    $content_tabs['private_messages'] = __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN );

                /*our_hook_
                    hook_name: wpc_client_content_tabs_array
                    hook_title: Content Tabs
                    hook_description: Hook runs when Content Tabs init.
                    hook_type: filter
                    hook_in: wp-client
                    hook_location class.admin_menu.php
                    hook_param: array $content_tabs
                    hook_since: 4.1.3
                */
                $content_tabs = apply_filters( 'wpc_client_content_tabs_array', $content_tabs );

                $current_tab = empty( $_GET['tab'] ) ? '' : urldecode( $_GET['tab'] );

                foreach ( $content_tabs as $name=>$label ) {
                    $active = ( $current_tab == $name ) ? 'nav-tab-active' : '';
                    $get = empty( $name ) ? '' : '&tab=' . $name;

                    $tabs .= '<a href="' . admin_url( 'admin.php?page=wpclients_content' . $get ) . '" class="nav-tab ' . $active . '">' . $label . '</a>';
                }

                break;
            case 'templates':
                $template_tabs = array();

                if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                    $template_tabs['portal_page'] = sprintf( __( '%s Template', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] );

                if ( current_user_can('wpc_view_email_templates') || current_user_can('wpc_edit_email_templates') ||
                    current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
                    $template_tabs['emails'] = __( 'Email Templates', WPC_CLIENT_TEXT_DOMAIN );

                if( !WPC()->flags['easy_mode'] ) {
                    if ( current_user_can('wpc_view_shortcode_templates') || current_user_can('wpc_edit_shortcode_templates') ||
                        current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) )
//                            $template_tabs['shortcodes'] = __( 'Shortcode Templates', WPC_CLIENT_TEXT_DOMAIN );
                        $template_tabs['php_templates'] = __( 'Shortcode Templates', WPC_CLIENT_TEXT_DOMAIN );
                }

                $template_tabs = apply_filters( 'wpc_client_templates_tabs_array', $template_tabs );

                $current_tab = ( empty( $_GET['tab'] ) ) ? 'portal_page' : urldecode( $_GET['tab'] );

                foreach ( $template_tabs as $name=>$label ) {
                    $active = ( $current_tab == $name ) ? 'nav-tab-active' : '';
                    $tabs .= '<a href="' . admin_url( 'admin.php?page=wpclients_templates&tab=' . $name ) . '" class="nav-tab ' . $active . '">' . $label . '</a>';
                }

                do_action( 'wpc_client_templates_tabs' );
                break;
            case 'dashboard':

                if ( WPC()->is_licensed( 'WP-Client' ) ) {
                    $dashboard_tabs = array(
                        'dashboard' => __( 'Dashboard', WPC_CLIENT_TEXT_DOMAIN ),
                    );
                }


                if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                    if ( ! WPC()->plugin['hide_system_status_tab'] ) {
                        $dashboard_tabs['system_status'] = __('System Status', WPC_CLIENT_TEXT_DOMAIN);
                    }

                    if ( ! WPC()->plugin['hide_get_started_tab'] && WPC()->is_licensed( 'WP-Client' ) ) {
                        $dashboard_tabs['get_started'] = __( 'Get Started', WPC_CLIENT_TEXT_DOMAIN );
                    }

                    if ( ! WPC()->plugin['hide_licenses_tab'] && current_user_can( 'manage_options' ) ) {
                        $dashboard_tabs['licenses'] = __( 'Licenses', WPC_CLIENT_TEXT_DOMAIN );
                    }
                }

                $current_tab = !empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'dashboard';

                foreach ( $dashboard_tabs as $name=>$label ) {
                    $active = ( $current_tab == $name ) ? 'nav-tab-active' : '';
                    $tabs .= '<a href="' . admin_url( 'admin.php?page=wpclients&tab=' . $name ) . '" class="nav-tab ' . $active . '">' . $label . '</a>';
                }
                break;
            default:
                $tabs = apply_filters( 'wpc_gen_tabs_menu_' . $page, $tabs );
                break;
        }

        return $tabs . '</h2>';
    }

    /**
     * Get prepared search text for sql request
     *
     * @global object $wpdb
     * @param string $text
     * @param array $sql_fields
     * @return string
     */
    function get_prepared_search( $text, $sql_fields ) {
        $text = strtolower( trim( $text ) );

        $string = $return = '';

        $search = array();
        foreach ( $sql_fields as $field ) {
            $search[] = '%' . $text . '%';

            if ( ! is_array( $field ) ) {
                $string .= 'LOWER(' . $field . ') LIKE %s OR ';
            } else {
                if ( strpos( $field['meta_key'], '%' ) !== false ) {
                    $field['meta_key'] = str_replace( '%', '%%', $field['meta_key'] );
                    $string .= '( ' . $field['table'] . '.meta_key LIKE \'' . $field['meta_key'] . '\' AND LOWER(' . $field['meta_value'] . ') LIKE %s ) OR ';
                } else {
                    $string .= '( ' . $field['table'] . '.meta_key = \'' . $field['meta_key'] . '\' AND LOWER(' . $field['meta_value'] . ') LIKE %s ) OR ';
                }
            }
        }

        $string = substr( $string, 0, -4 );

        if ( !empty( $string ) ) {
            global $wpdb;
            $return = $wpdb->prepare( ' AND ( ' . $string . ' )', $search );
        }

        return $return;
    }

    /**
     * get keys from multidimensional array
     **/
    function show_keys($ar) {
        $temp = array();
        foreach ($ar as $k => $v ) {
            $temp[] = $k;
            if (is_array($v)) {
                $temp = array_merge($temp, $this->show_keys($v));
            }
        }
        return $temp;
    }


    /*
     * Render tooltip
     */
    function tooltip( $message ) {
        wp_enqueue_style( 'wpc-ui-style' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        ob_start();
        ?>

        <a href="javascript:void(0);" class="wpc_tooltip_icon" title="<?php echo htmlspecialchars($message) ?>">
            <img src="<?php echo WPC()->plugin_url . 'images/icon_q.png' ?>" width="15" height="15"  alt="" />
        </a>

        <?php if ( !isset( WPC()->flags['tooltip_rendered'] ) ) {
            WPC()->flags['tooltip_rendered'] = true;

            ?>

            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('.wpc_tooltip_icon').tooltip({
                        content: function () {
                            return jQuery(this).attr('title');
                        }
                    });
                });
            </script>

            <style>
                .ui-tooltip {
                    padding: 8px;
                    background-color: #fff;
                    position: absolute;
                    z-index: 1000000;
                    max-width: 300px;
                    -webkit-box-shadow: 0 0 5px #aaa;
                    box-shadow: 0 0 5px #aaa;
                }

            </style>

            <?php
        }

        $tooltip = ob_get_contents();
        ob_end_clean();

        return $tooltip;
    }

    /**
     * Add wpc_notices
     *
     * @param array $new_notices
     */
    function add_wpc_notices( $new_notices ) {
        $old_notices = WPC()->get_settings( 'notices' );

        WPC()->settings()->update( array_merge( $old_notices, $new_notices ), 'notices');
    }


    /**
     * Delete wpc_notice
     *
     * @param string $key
     */
    function delete_wpc_notice( $key ) {
        $notices = WPC()->get_settings( 'notices' );

        unset( $notices[ $key ] );

        WPC()->settings()->update( $notices, 'notices');
    }


    function merge_capabilities( $cap, $additional_capabilities ) {
        foreach( $additional_capabilities as $role => $role_caps ) {
            foreach( $role_caps as $type => $caps_list ) {
                foreach( $caps_list as $cap_name => $cap_value ) {
                    if( is_array( $cap_value ) ) {
                        if( !( isset( $cap[ $role ][ $type ][ $cap_name ] ) && is_array( $cap[ $role ][ $type ][ $cap_name ] ) ) ) {
                            $cap[ $role ][ $type ][ $cap_name ] = array();
                        }
                        $cap[ $role ][ $type ][ $cap_name ] = array_merge( $cap[ $role ][ $type ][ $cap_name ], $cap_value );
                    } else {
                        $cap[ $role ][ $type ][ $cap_name ] = $cap_value;
                    }
                }
            }
        }
        return $cap;
    }


    function is_our_role( $role ) {

        $wpc_roles = include( WPC()->plugin_dir . 'includes/data/data-roles.php' );

        if ( is_array( $wpc_roles ) && in_array( $role, $wpc_roles ) ) {
            return true;
        }

        return false;
    }


    /**
     * decode64 multidimensional array
     **/
    function array_base64_decode( $array = array() ) {
        if(is_array($array)) {
            foreach($array as $k=>$val) {
                if(is_array($val)) {
                    $array[$k] = $this->array_base64_decode($val);
                } else if(is_string($val)) {
                    $array[$k] = base64_decode( str_replace( array( '-', '*' ), array( '+', '/' ),$val ) );
                }
            }
        }
        return $array;
    }


    /*
    * For more actions
    */
    function more_actions( $id, $link_name, $actions ) {

        wp_enqueue_script( 'jquery-ui-tooltip' );

        ob_start();

        ?>

        <a href="javascript:void(0);" class="wpc_tooltip_more_actions" data-id="<?php echo $id ?>" title="">
            <?php
            echo $link_name ;
            ?>
        </a>
        <div class="arrow_for_actions"></div>
        <div class="wpc_more_actions" id="wpc_more_actions_<?php echo $id ?>">
            <?php
            echo '<ul class="wpc_ul_actions">';

            $end_actions = array();

            // 'Delete' action always in down
            if ( !empty($actions['delete']) ) {
                $end_actions['delete'] = $actions['delete'];
                unset( $actions['delete'] );
            }


            if ( ! function_exists( 'wpc_more_actions_sort' ) ) {
                function wpc_more_actions_sort( $a, $b ) {
                    $first = strip_tags( $a );
                    $second = strip_tags( $b );
                    if ( $first == $second ) {
                        return 0;
                    } elseif( $first )
                        return ($first < $second) ? -1 : 1;
                }
            }

            // Sort of alphabetic exclude html-tags
            @uasort( $actions, 'wpc_more_actions_sort' );

            $actions = array_merge($actions, $end_actions);
            foreach( $actions as $name => $action ) {
                echo '<li><span class="' . $name . '">' . $action . '</span></li>';
            }
            echo '</ul>';
            ?>
        </div>

        <script type="text/javascript">

            jQuery( document ).ready( function() {
                //var wpc_hide_flag = 1;

                jQuery( '.wpc_tooltip_more_actions' )
                    .tooltip({
                        content: jQuery( "#wpc_more_actions_" + jQuery( this ).data('id') ).text(),
                        items: 'a'
                    })
                    .off( "mouseover" )
                    .on( "click", function(){
                        jQuery( this ).tooltip( "open" );
                        var top_block = 0 - Math.round ( jQuery( "#wpc_more_actions_" + jQuery( this ).data('id') ).height() / 2 );
                        jQuery( '.wpc_more_actions' ).css('display', 'none');
                        jQuery( '.arrow_for_actions' ).css('display', 'none');
                        jQuery( 'div.row-actions ' ).removeClass('visible');
                        jQuery( '.username' ).css('overflow', 'visible');
                        jQuery( "#wpc_more_actions_" + jQuery( this ).data('id') ).css('display', 'block').css( 'top', top_block + 'px' ).prev('div.arrow_for_actions').css('display', 'block');
                        return false;
                    })
                    .attr( "title", "" ).css({ cursor: "pointer" });
                /*jQuery( '.wpc_ul_actions' ).live('click', function(){
                 wpc_hide_flag = 0;
                 });*/

                jQuery( '.wpc_more_actions' ).click( function( e ) {
                    if( event.target.tagName.toLowerCase() !== 'a' ) {
                        e.stopPropagation();
                    }
                });

                jQuery( document ).click( function() {
                    if ( !jQuery( this ).hasClass('wpc_tooltip_more_actions') ) { //   && wpc_hide_flag
                        jQuery( '.wpc_more_actions' ).css('display', 'none');
                        jQuery( '.arrow_for_actions' ).css('display', 'none');
                    }
                    //wpc_hide_flag = 1;
                });

            });

        </script>

        <style>



            .ui-tooltip {
                position: absolute;
            }

        </style>

        <?php

        $more_actions = ob_get_contents();
        ob_end_clean();

        return $more_actions;
    }


    function generate_field_shortcode_attribute( $type, $attributes ) {
        if( empty( $type ) ) return '';
        $return = '';
        switch( $type ) {
            case 'hidden':
                $return = '<input type="hidden" ';
                $return .= 'name="' . $attributes['name'] . '" ';
                $return .= 'value="' . ( isset( $attributes['value'] ) ? $attributes['value'] : '' ) . '" ';
                $return .= '/>';
                break;
            case 'text':
                $return = '<input type="text" ';
                $return .= 'name="' . $attributes['name'] . '" ';
                $return .= 'id="' . $attributes['id'] . '" ';
                $return .= 'class="wpc_attr_field ';
                if( !empty( $attributes['parent_name'] ) && isset( $attributes['parent_value'] ) ) {
                    $return .= 'wpc_has_parent_' . $attributes['parent_name'];
                    if( is_string( $attributes['parent_value'] ) ) {
                        $return .= ' ' . $attributes['parent_name'] . '_' . md5( $attributes['parent_value'] );
                    } else if( is_array( $attributes['parent_value'] ) ) {
                        foreach( $attributes['parent_value'] as $val ) {
                            $return .= ' ' . $attributes['parent_name'] . '_' . md5( $val );
                        }
                    }
                }
                $return .= '" ';
                $return .= 'data-key="' . $attributes['key'] . '" ';
                $return .= 'value="' . ( isset( $attributes['value'] ) ? $attributes['value'] : '' ) . '" ';
                $return .= 'style="width: 100%;" ';
                $return .= '/>';
                break;
            case 'selectbox':
                $value = isset( $attributes['value'] ) ? $attributes['value'] : '';
                $return = '<select name="' . $attributes['name'] . '" id="' . $attributes['id'] . '" ';
                $return .= 'class="wpc_attr_field ';
                if( !empty( $attributes['parent_name'] ) && isset( $attributes['parent_value'] ) ) {
                    $return .= 'wpc_has_parent_' . $attributes['parent_name'];
                    if( is_string( $attributes['parent_value'] ) ) {
                        $return .= ' ' . $attributes['parent_name'] . '_' . md5( $attributes['parent_value'] );
                    } else if( is_array( $attributes['parent_value'] ) ) {
                        foreach( $attributes['parent_value'] as $val ) {
                            $return .= ' ' . $attributes['parent_name'] . '_' . md5( $val );
                        }
                    }
                }
                $return .= '" ';
                $return .= 'data-key="' . $attributes['key'] . '" ';
                $return .= 'style="width: 100%;">';
                if( !empty( $attributes['values'] ) && is_array( $attributes['values'] ) ) {
                    foreach( $attributes['values'] as $key=>$val ) {
                        if( $key == 'null' ) {
                            $key = '';
                        }
                        $return .= '<option value="' . $key . '" ' . selected( $value, $key, false ) . '>' . $val . '</option>';
                    }
                }
                $return .= '</select>';
                break;
            case 'multiselect':
                $value = isset( $attributes['value'] ) ? ( is_array( $attributes['value'] ) ? $attributes['value'] : array( $attributes['value'] ) ) : array();
                $return = '<select multiple size="4" name="' . $attributes['name'] . '[]" id="' . $attributes['id'] . '" ';
                $return .= 'class="wpc_attr_field ';
                if( !empty( $attributes['parent_name'] ) && isset( $attributes['parent_value'] ) ) {
                    $return .= 'wpc_has_parent_' . $attributes['parent_name'];
                    if( is_string( $attributes['parent_value'] ) ) {
                        $return .= ' ' . $attributes['parent_name'] . '_' . md5( $attributes['parent_value'] );
                    } else if( is_array( $attributes['parent_value'] ) ) {
                        foreach( $attributes['parent_value'] as $val ) {
                            $return .= ' ' . $attributes['parent_name'] . '_' . md5( $val );
                        }
                    }
                }
                $return .= '" ';
                $return .= 'data-key="' . $attributes['key'] . '" ';
                $return .= 'style="width: 100%;">';
                if( !empty( $attributes['values'] ) && is_array( $attributes['values'] ) ) {
                    foreach( $attributes['values'] as $key=>$val ) {
                        if( $key == 'null' ) {
                            $key = '';
                        }
                        $return .= '<option value="' . $key . '" ' . selected( in_array( (string)$key, $value ), true, false ) . '>' . $val . '</option>';
                    }
                }
                $return .= '</select>';
                break;
            case 'assign_popup':
                $value = !empty( $attributes['value'] ) ? $attributes['value'] : '';
                $class = '';
                if( !empty( $attributes['parent_name'] ) && isset( $attributes['parent_value'] ) ) {
                    $class .= 'wpc_has_parent_' . $attributes['parent_name'];
                    if( is_string( $attributes['parent_value'] ) ) {
                        $class .= ' ' . $attributes['parent_name'] . '_' . md5( $attributes['parent_value'] );
                    } else if( is_array( $attributes['parent_value'] ) ) {
                        foreach( $attributes['parent_value'] as $val ) {
                            $class .= ' ' . $attributes['parent_name'] . '_' . md5( $val );
                        }
                    }
                }

                switch( !empty( $attributes['object'] ) ? $attributes['object'] : '' ) {
                    case 'client':
                        $class .= ' clients_field';

                        $link_array = array(
                            'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                            'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['client']['p'],
                            'data-callback' => 'shortcode_popup',
                            'data-input' => $attributes['id']
                        );
                        $input_array = array(
                            'name'     => $attributes['name'],
                            'id'       => $attributes['id'],
                            'data-key' => $attributes['key'],
                            'value'    => $value,
                            'class'    => $class
                        );
                        $additional_array = array(
                            'counter_value' => 0
                        );

                        $return = WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );
                        break;
                    case 'circle':
                        $class .= ' circles_field';

                        $link_array = array(
                            'title'   => sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['p'] ),
                            'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . WPC()->custom_titles['circle']['p'],
                            'data-callback' => 'shortcode_popup',
                            'data-input' => $attributes['id']
                        );
                        $input_array = array(
                            'name'     => $attributes['name'],
                            'id'       => $attributes['id'],
                            'data-key' => $attributes['key'],
                            'value'    => $value,
                            'class'    => $class
                        );
                        $additional_array = array(
                            'counter_value' => 0
                        );

                        $return = WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );
                        break;
                }

                break;
        }
        if( !empty( $attributes['description'] ) ) {
            $return .= '<br/>
                    <span class="description">' . $attributes['description'] . '</span>';
        }
        return $return;
    }


    function get_list_table_per_page( $screen_option_key ) {
        $per_page = get_user_meta( get_current_user_id(), $screen_option_key, true );

        if ( empty( $per_page ) )
            return $this->list_table_per_page;

        if ( (int)$per_page > 200 ) {
            $per_page = 200;
            update_user_meta( get_current_user_id(), $screen_option_key, $per_page );
        }

        return $per_page;
    }



    function add_textext_scripts() {
        wp_enqueue_script( 'wpc-textext-core-js' );
        wp_enqueue_style( 'wpc-textext-core-css' );

        wp_enqueue_script( 'wpc-textext-focus-js' );
        wp_enqueue_style( 'wpc-textext-focus-css' );

        wp_enqueue_script( 'wpc-textext-tags-js' );
        wp_enqueue_style( 'wpc-textext-tags-css' );

        wp_enqueue_script( 'wpc-textext-prompt-js' );
        wp_enqueue_style( 'wpc-textext-prompt-css' );

        wp_enqueue_script( 'wpc-textext-autocomplete-js' );
        wp_enqueue_style( 'wpc-textext-autocomplete-css' );

        wp_enqueue_script( 'wpc-textext-ajax-js' );

        wp_enqueue_script( 'wpc-textext-arrow-js' );
        wp_enqueue_style( 'wpc-textext-arrow-css' );
    }


    /**
     * Get type access for each location
     *
     * @return array
     */
    function get_type_person_for_location() {
        return array(
            'login'     => sprintf( __( ' for %1$s & %2$s', WPC_CLIENT_TEXT_DOMAIN ),
                WPC()->custom_titles['client']['p'],
                WPC()->custom_titles['staff']['p'] ),
            'logout'    => ' ' . __( 'for Not-logged Users', WPC_CLIENT_TEXT_DOMAIN ),
            'circle'    => ' ' . sprintf( __( 'for Assigned %s(s)', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['circle']['s'] ),
        );
    }

    /**
     *
     *
     * @param string $action
     * @param bool $result
     */
    function update_nav_menu( $action, $result ) {
        if ( 'save-menu-locations' !== $action || !$result ) {
            return;
        }
        //if this hook fired again
        if ( isset( WPC()->flags['single_load']['update_nav_menu'] ) ) {
            return;
        }
        WPC()->flags['single_load']['update_nav_menu'] = true;

        $setting_name = 'advanced_locations';

        $wpc_settings =  isset( $_POST['wpc_data'] )
            ? $_POST['wpc_data'] : array();

        /* if need to delete when empty in assign circles
        if ( !empty( $wpc_settings['circle'] ) ) {
            foreach ( $wpc_settings['circle'] as $location => $val ) {
                foreach ( $val as $k => $v ) {
                    if ( 0 !== $k && empty( $v ) ) {
                        unset( $wpc_settings['circle'][ $location ][ $k ] );
                    }
                }
            }
        }*/

        WPC()->settings()->update( $wpc_settings, $setting_name );
    }


    /**
     * Generate line of advanced settings of locations
     *
     * @param string $location type of location
     * @param string $name title of location
     * @param string $for type of access
     * @param string $title_end title of access
     * @param array $nav_menus all exist menus
     * @param array $menu_locations
     * @param int $i for several line for circles
     * @return string html
     */
    function get_line_advanced_location( $location, $name, $for, $title_end, $nav_menus, $menu_locations, $i = '' ) {
        $assign_line = !in_array( $for, array( 'login', 'logout' ) );
        $id = 'wpc_' . $for . '_' . $location . $i;

        ob_start();
        ?>
        <tr class="menu-locations-row wpc_advanced_locations">
            <td class="menu-location-title"><label for="<?php echo $id ?>"><?php echo $name . $title_end; ?></label></td>
            <td class="menu-location-menus">
                <select name="wpc_data[<?php echo $for ?>][<?php echo $location; ?>]<?php echo $assign_line ? '[]' : '' ?>" id="<?php echo $id ?>">
                    <option value=""><?php printf( '&mdash; %s &mdash;', esc_html__( 'Select a Menu' ) ); ?></option>
                    <?php foreach ( $nav_menus as $menu ) : ?>
                        <?php $selected = isset( $menu_locations[$location] ) && $menu_locations[$location] == $menu->term_id; ?>
                        <option <?php if ( $selected ) { echo 'data-orig="true"'; } ?> <?php selected( $selected ); ?>
                            value="<?php echo $menu->term_id; ?>">
                            <?php echo wp_html_excerpt( $menu->name, 40, '&hellip;' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="locations-row-links">
                    <?php if ( !empty( $menu_locations[ $location ] ) && is_numeric( $menu_locations[ $location ] ) ) { ?>
                        <span class="<?php echo $assign_line ? 'locations-edit-menu-link' : 'locations-add-menu-link' ?>">
                            <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'menu' => $menu_locations[$location] ), admin_url( 'nav-menus.php' ) ) ); ?>">
                                <span aria-hidden="true"><?php _ex( 'Edit', 'menu' ); ?></span><span class="screen-reader-text"><?php _e( 'Edit selected menu' ); ?></span>
                            </a>
                        </span>
                    <?php } ?>
                    <?php if ( $assign_line ) { ?>
                        <span class="locations-edit-menu-link">
                            <?php
                            $current_circle_ids = empty( $menu_locations['wpc_circles_' . $location ] ) ? array()
                                : explode( ',', $menu_locations['wpc_circles_' . $location ]  );

                            $link_id = 'wpc_data_' . $for . '_' . $location . $i;
                            $link_array = array(
                                'title'         => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                'text'          => __( 'Assign', WPC_CLIENT_TEXT_DOMAIN ),
                                'data-input'    => $link_id,
                                'data-location' => $location,
                                'data-numeric_' . $location => $i,
                            );
                            $input_array = array(
                                'name'  => 'wpc_data[' . $for . '][wpc_circles_' . $location . '][]',
                                'id'    => $link_id,
                                'value' => implode( ',', $current_circle_ids ),
                            );
                            $additional_array = array(
                                'counter_value' => count( $current_circle_ids ),
                            );

                            WPC()->assigns()->assign_popup( 'circle', '', $link_array, $input_array, $additional_array );
                            ?>
                        </span>
                        <span class="locations-add-menu-link" <?php echo 0 === $i ? ' style="display: none;"' : '' ?>>
                            <a href="javascript:void(0);" class="wpc_delete_line">
                                <?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ); ?>
                            </a>
                        </span>
                        <?php if ( 0 === $i ) { ?>
                            <span class="locations-add-menu-link">
                                <a href="javascript:void(0);" class="wpc_clone_line">
                                    <?php _e( 'Add another...', WPC_CLIENT_TEXT_DOMAIN ); ?>
                                </a>
                            </span>
                        <?php } ?>
                    <?php } ?>
                </div><!-- .locations-row-links -->
            </td><!-- .menu-location-menus -->
        </tr><!-- .menu-locations-row -->
        <?php
        return ob_get_clean();
    }


    function download_edited_tpls() {
        $backup_folder = WPC()->get_upload_dir( 'wpclient/tpl_backup/' );
        $zip_name = 'shortcode_templates_backup';

        global $wpdb;

        $templates = $wpdb->get_results( $wpdb->prepare(
            "SELECT *
                FROM {$wpdb->options}
                WHERE option_name LIKE %s",
            "wpc_shortcode_template_wpc_client_%"
        ), ARRAY_A );

        if ( empty( $templates ) )
            return;

        $filenames = array();
        foreach ( $templates as $template_data ) {
            $key = str_replace( 'wpc_shortcode_template_wpc_client_', '', $template_data['option_name'] );

            $filename = $backup_folder . $key . '.tpl';
            $filenames[] = $filename;

            $template_file = fopen( $filename, 'w+' );
            fwrite( $template_file, maybe_unserialize( $template_data['option_value'] ) );
            fclose( $template_file );
        }


        if ( empty( $filenames ) )
            return;


        if ( !ini_get( 'safe_mode' ) )
            @set_time_limit( 0 );

        if ( exec( "cd $backup_folder; zip -r $zip_name.zip *" ) && file_exists( $backup_folder . $zip_name .'.zip' ) ) {

            foreach ( $filenames as $filename ) {
                unlink( $filename );
            }

            header("Pragma: no-cache");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Robots: none");
            header("Content-Description: File Transfer");
            header("Content-Transfer-Encoding: binary");
            header('Content-Type: application/octet-stream');
            header("Content-length: " . filesize( $backup_folder . $zip_name .'.zip' ) );
            header('Content-disposition: attachment; filename="' . $zip_name . '.zip"');

            $levels = ob_get_level();
            for ( $i=0; $i < $levels; $i++ )
                @ob_end_clean();

            WPC()->readfile_chunked( $backup_folder . $zip_name . '.zip' );
        }

        WPC()->files()->recursive_delete_files( $backup_folder );

        $this->delete_wpc_notice( 'tpl_archive_notice' );

        exit;
    }


    /**
     * @param string $message
     */
    function get_install_page_notice( $message = '' ) {
        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
            $defaul_message = sprintf( __( 'The <b>Theme Link Page</b> for this feature is not currently selected, and as a result this feature may not function correctly. Please check your <a href="%s" target="_blank">Theme Link Pages Settings</a> to verify this feature\'s pages are selected.', WPC_CLIENT_TEXT_DOMAIN ), get_admin_url() . 'admin.php?page=wpclients_settings&tab=pages' );

            $message = ( $message ) ? $message : $defaul_message;

            echo  '<div id="wpc_message_install_page" class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>';
        }
    }


    /**
     * Function for building Filter Field on WP List Table
     *
     * @param array $attr parameters of filter
     * @param bool|false $ajax_filter AJAX loading filter's values
     * @return string Filters HTML
     */
    function build_filter_field( $attr = array(), $ajax_filter = false ) {
        //check filtered on page
        $keys = array_keys( $attr );

        $filtered = array();
        foreach( $keys as $k=>$key ) {
            if( isset( $_GET['wpc_filter_' . $key] ) ) {
                $filtered[] = $key;
            }
        }

        $lists_count = 0;
        foreach( $attr as $key=>$value ) {
            $lists_count += count( $value['list'] );
        }

        ob_start(); ?>

        <style type="text/css">
            .wpc_filter_block {
                float:left;
                width:410px;
                margin:0;
                padding:3px 8px 0 0;
                box-sizing:border-box;
                -webkit-box-sizing:border-box;
                -moz-box-sizing:border-box;
            }

            .wpc_filter {
                width: 80px;
                float:left;
                margin: 0px 10px 0px 0px;
                padding: 0px 0px 0px 10px;
                background: transparent;
                border-radius: 2px;
                position:relative;
                color: rgba( 0, 0, 0, 0.6 );
                border:1px solid;
                border-color: rgba( 204, 204, 204, 0.4 );
                cursor:pointer;
                box-sizing:border-box;
                -webkit-box-sizing:border-box;
                -moz-box-sizing:border-box;
                line-height: 26px;
                height: 28px;
            }

            .wpc_filter:after {
                position:absolute;
                top:0;
                bottom:0;
                right:6px;
                content:'\25bc';
                color:rgba( 0, 0, 0, 0.6 );
                font-size: 10px;
            }

            .wpc_filter.filter_opened {
                border-radius: 2px 2px 0px 0px;
                background: rgba( 155, 155, 155, 0.1 );
            }

            .wpc_filter.filter_opened:after {
                content:'\25b2';
            }

            .wpc_filter:hover {
                background: rgba( 155, 155, 155, 0.1 );
            }

            .wpc_filter_selector {
                float:left;
                width:100%;
                margin-bottom: 10px;
            }

            .wpc_add_filter {
                margin: 0 auto 7px !important;
                display: block !important;
                margin-top:7px !important;
            }


            .wpc_filter .wpc_filter_wrapper {
                display: none;
                top:100%;
                left:-1px;
                width:300px;
                background: black;
                position: absolute;
                z-index:100;
                border:1px solid;
                border-color: rgba( 204, 204, 204, 0.4 );
                background: #e8e8e8;
                border-radius: 0px 2px 2px 2px;
                box-sizing:border-box;
                -webkit-box-sizing:border-box;
                -moz-box-sizing:border-box;
                padding: 5px;
            }

            .wpc_filter.filter_opened .wpc_filter_wrapper {
                display: block;
            }

            .wpc_filter_by,
            .from_date_field,
            .to_date_field {
                padding: 2px !important;
                line-height: 28px !important;
                height: 28px !important;
                font-size: 14px !important;
            }


            .wpc_filter_wrapper .wpc_ajax_content {
                width:100%;
                float:left;
                margin:0;
                padding:0;
                position: relative;
            }

            .wpc_filter_wrapper .wpc_loading_overflow {
                background: transparent;
                position: absolute;
                top:0;
                bottom:0;
                width:100%;
            }

            .wpc_filter_wrapper .wpc_small_ajax_loading {
                position: absolute;
                top:50%;
                left:50%;
                background: url("<?php echo WPC()->plugin_url ?>/images/ajax_loading.gif");
                width:15px;
                height:15px;
                margin: -7px 0px 0px -7px;
            }

            .wpc_filter_wrapper .wpc_ajax_load_content {
                position: relative;
            }

            .wpc_filter_wrapper .wpc_overflow_content {
                visibility: visible;
            }
            .wpc_filter_wrapper .wpc_is_loading .wpc_overflow_content {
                visibility: hidden;
            }

            .wpc_filter_wrapper .wpc_loading_overflow {
                visibility: hidden;
            }
            .wpc_filter_wrapper .wpc_is_loading .wpc_loading_overflow {
                visibility: visible;
            }


            .wpc_active_filters_wrapper {
                float:left;
                display: block;
                width: calc( 100% - 90px );
                box-sizing:border-box;
                -webkit-box-sizing:border-box;
                -moz-box-sizing:border-box;
            }

            .wpc_active_filter_wrapper {
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                margin: 0px 10px 10px 0px;
                padding: 1px 4px;
                border: 1px solid #ccc;
                -webkit-border-radius: 2px;
                -moz-border-radius: 2px;
                border-radius: 2px;
                font-size: 11px;
                line-height: 24px;
                background: #fafafa;
                float:left;
                display: inline-block;
                cursor: default;
                color: #000;
                height: 28px;
            }

            .wpc_active_filter_wrapper:hover {
                border-color: #bbb;
                background: #ddd;
            }

            .wpc_remove_filter {
                float:right;
                margin-left: 10px;
                margin-right: 2px;
                padding: 0;
                font-size: 14px;
                line-height: 24px;
                font-weight: bold;
                cursor: pointer;
                color:#a00;
            }

            .wpc_remove_filter:hover {
                color:red;
            }
        </style>

        <script type="text/javascript">
            jQuery( document).ready( function() {
                jQuery( 'body' ).on( 'click', '.wpc_filter', function(e) {
                    jQuery(this).toggleClass( 'filter_opened' );

                    e.stopPropagation();

                    jQuery( 'body' ).bind( 'click', function( event ) {
                        jQuery( '.wpc_filter' ).removeClass( 'filter_opened' );
                        jQuery( 'body' ).unbind( event );
                    });
                });

                jQuery( 'body' ).on( 'click', '.wpc_filter_wrapper', function(e){
                    e.preventDefault();
                    e.stopPropagation();
                });

                jQuery( 'body' ).on( 'change', '.wpc_filter_by', function() {
                    var obj = jQuery(this);

                    <?php if( $ajax_filter ) { ?>
                    obj.parents('.wpc_filter_wrapper').find( '.wpc_ajax_content' ).addClass( 'wpc_is_loading' );
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_message_get_filter&type=' + jQuery('.nav_button.selected').data('list') + '&search=' + jQuery('.wpc_msg_search').val() + '&by=' + jQuery(this).val(),
                        dataType: "json",
                        success: function( data ){
                            if( !data.status ) {
                                alert( data.message );
                            } else {
                                obj.parents('.wpc_filter_wrapper').find( '.wpc_filter_selectors' ).html( data.filter_html );

                                if( obj.val() == 'member' ) {
                                    jQuery( '.wpc_filter_members' ).wpc_select({
                                        search:true,
                                        opacity:'0.2'
                                    });
                                } else if( obj.val() == 'date' ) {
                                    if( typeof( custom_datepicker_init ) !== 'undefined' ) {
                                        custom_datepicker_init();
                                    }

                                    jQuery('.from_date_field').datepicker( "option", {
                                        minDate: new Date( data.mindate*1000 ),
                                        maxDate: new Date( data.maxdate*1000 ),
                                        onClose: function( selectedDate ) {
                                            jQuery('.to_date_field').datepicker( "option", "minDate", selectedDate );
                                        }
                                    });

                                    jQuery('.to_date_field').datepicker( "option", {
                                        minDate: new Date( data.mindate*1000 ),
                                        maxDate: new Date( data.maxdate*1000 ),
                                        onClose: function( selectedDate ) {
                                            jQuery('.from_date_field').datepicker( "option", "maxDate", selectedDate );
                                        }
                                    });
                                }

                                obj.parents('.wpc_filter_wrapper').find( '.wpc_ajax_content' ).removeClass( 'wpc_is_loading' );
                            }
                        }
                    });
                    <?php } else { ?>
                    jQuery( '.wpc_filter_selector').hide();
                    jQuery( '.wpc_filter_selector.' + obj.val()).show();
                    <?php } ?>
                });

                <?php if( $ajax_filter ) { ?>
                //change filtering reload
                jQuery( 'body' ).on( 'change', '.wpc_active_filters_wrapper', function() {
                    if( loading ) {
                        return false;
                    }

                    var filters = '';
                    if( typeof filter !== 'undefined' && Object.keys(filter).length > 0 ) {
                        filters = jQuery.base64Encode( JSON.stringify( filter ) );
                    }

                    //reset bulk check
                    jQuery( '.wpc_msg_bulk_check' ).prop( 'checked', false );
                    jQuery( '.wpc_msg_bulk_check' ).prop( 'indeterminate', false);

                    //show loader
                    jQuery( '.wpc_ajax_overflow' ).show();
                    loading = true;
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_message_get_list&type=' + jQuery('.nav_button.selected').data('list') + '&search=' + jQuery('.wpc_msg_search').val() + '&filters=' + filters,
                        dataType: "json",
                        success: function( data ) {
                            if( !data.status ) {
                                jQuery( '.wpc_ajax_overflow' ).hide();
                                alert( data.message );
                            } else {
                                jQuery('.wpc_msg_content_wrapper_inner').html( data.html );

                                resize_messages_content();

                                jQuery('.wpc_msg_pagination').data('pagenumber', 1);

                                if( data.is_empty ) {
                                    //hide navigation
                                    jQuery( '.wpc_msg_pagination' ).hide();
                                    jQuery( '.wpc_msg_bulk_all' ).hide();
                                    jQuery( '.wpc_msg_filter' ).hide();

                                    if( jQuery('.wpc_msg_search').val() == '' ) {
                                        jQuery( '.wpc_msg_search_line' ).hide();
                                    }
                                } else {
                                    //show navigation
                                    jQuery( '.wpc_msg_pagination' ).show();
                                    jQuery( '.wpc_msg_bulk_all' ).show();
                                    jQuery( '.wpc_msg_search_line' ).show();
                                    jQuery( '.wpc_msg_filter' ).show();
                                }

                                if( data.pagination !== false ) {
                                    jQuery( '.wpc_msg_pagination_text .total_count' ).html( data.pagination.count );
                                    jQuery( '.wpc_msg_pagination_text .start_count' ).html( data.pagination.start );
                                    jQuery( '.wpc_msg_pagination_text .end_count' ).html( data.pagination.end );

                                    if( data.pagination.current_page > 1 ) {
                                        jQuery( '.wpc_msg_next_button' ).removeClass( 'disabled' );
                                    } else {
                                        jQuery( '.wpc_msg_next_button' ).addClass( 'disabled' );
                                    }

                                    if( data.pagination.pages_count == data.pagination.current_page ) {
                                        jQuery( '.wpc_msg_prev_button' ).addClass( 'disabled' );
                                    } else {
                                        jQuery( '.wpc_msg_prev_button' ).removeClass( 'disabled' );
                                    }
                                }

                                jQuery( '.wpc_msg_filter_by' ).trigger( 'change' );

                                jQuery( '.wpc_ajax_overflow' ).hide();
                            }
                            loading = false;
                        }
                    });
                });
                <?php } ?>

                //add filter
                jQuery( 'body' ).on( 'click', '.wpc_add_filter', function() {
                    var obj = jQuery(this);

                    <?php if( $ajax_filter ) { ?>

                    var filter_before = filter;
                    var filter_by = obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by').val();

                    if( typeof filter_before === 'undefined' ) {
                        filter = {};
                        filter[filter_by] = [];
                    }

                    if( !filter.hasOwnProperty( filter_by ) ) {
                        filter[filter_by] = [];
                    }

                    if( filter_by == 'member' ) {
                        var member_id = obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_members').val();

                        var in_array = false;

                        if( typeof filter_before !== 'undefined' && filter_before.hasOwnProperty( filter_by ) ) {
                            jQuery.map( filter_before[filter_by], function( elementOfArray, indexInArray ) {
                                if( elementOfArray == member_id ) {
                                    in_array = true;
                                }
                            });
                        }

                        if( in_array ) {
                            return false;
                        }

                        filter[filter_by].push( member_id );

                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                            data: 'action=wpc_message_get_filter_data&filter_by=' + filter_by + '&member_id=' + member_id,
                            dataType: "json",
                            success: function( data ){
                                if( !data.status ) {
                                    alert( data.message );
                                } else {
                                    jQuery( '.wpc_msg_active_filters_wrapper' ).append(
                                        '<div class="wpc_filter_wrapper" data-filter_by="' + filter_by + '" data-member_id="' + member_id + '">' +
                                        data.title + ': ' + data.name +
                                        '<div class="wpc_remove_filter">&times;</div>' +
                                        '</div>'
                                    ).trigger( 'change' );
                                }
                            }
                        });

                    } else if( filter_by == 'date' ) {
                        filter[filter_by] = {
                            'from': obj.parents( '.wpc_msg_filter' ).find('.from_date_field').next().val(),
                            'to': obj.parents( '.wpc_msg_filter' ).find('.to_date_field').next().val()
                        };

                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_url,
                            data: 'action=wpc_message_get_filter_data&filter_by=' + filter_by + '&from=' + filter[filter_by]['from'] + '&to=' + filter[filter_by]['to'],
                            dataType: "json",
                            success: function( data ){
                                if( !data.status ) {
                                    alert( data.message );
                                } else {
                                    jQuery( '.wpc_msg_active_filters_wrapper' ).append(
                                        '<div class="wpc_filter_wrapper" data-filter_by="' + filter_by + '">' +
                                        data.title + ': ' + data.from + ' - ' + data.to +
                                        '<div class="wpc_remove_filter">&times;</div>' +
                                        '</div>'
                                    ).trigger( 'change' );
                                }
                            }
                        });

                        obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option[value="date"]').attr('disabled', true);
                        obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option:not("disabled")').first().prop('selected', true);
                        obj.parents( '.wpc_msg_filter' ).find('.wpc_msg_filter_by').trigger( 'change' );
                    }

                    jQuery('.wpc_msg_filter').removeClass( 'filter_opened' );

                    <?php } else { ?>

                    window.location = window.location + '&wpc_filter_' + jQuery('.wpc_filter_by').val() + '=' + jQuery( '.wpc_filter_selector.' + jQuery('.wpc_filter_by').val()).find('.wpc_filter_value').val();

                    <?php } ?>
                });

                //remove filters
                jQuery( 'body' ).on( 'click', '.wpc_remove_filter', function() {
                    var obj = jQuery(this);
                    <?php if( $ajax_filter ) { ?>

                    var filter_before = filter;

                    var filter_by = obj.parents( '.wpc_filter_wrapper' ).data('filter_by');

                    if( filter_by == 'member' ) {
                        var member_id = obj.parents( '.wpc_filter_wrapper' ).data('member_id');

                        var index = filter_before[filter_by].indexOf( member_id.toString() );
                        if( index > -1 ) {
                            filter[filter_by].splice( index, 1 );
                        }
                    } else if( filter_by == 'date' ) {
                        delete filter.date;

                        jQuery( '.wpc_msg_filter' ).find('.wpc_msg_filter_by option[value="date"]').attr('disabled', false);
                        jQuery( '.wpc_msg_filter' ).find('.wpc_msg_filter_by').trigger( 'change' );
                    }

                    jQuery( '.wpc_msg_active_filters_wrapper' ).trigger( 'change' );
                    obj.parents( '.wpc_filter_wrapper' ).remove();

                    <?php } else { ?>

                    var get_str = window.location.search.substring(1).split("&");
                    var get_attr = {};

                    for (var i=0; i<get_str.length; i++) {
                        var temp = get_str[i].split("=");
                        if( 'wpc_filter_' + jQuery(this).parents('.wpc_active_filter_wrapper').data('filter_by') != temp[0] ) {
                            if( typeof( temp[1] ) !== 'undefined' ) {
                                get_attr[temp[0]] = temp[1];
                            }
                        }
                    }

                    var location = '';
                    jQuery.each( get_attr, function(index, value){
                        location += index + '=' + value + '&';
                    });
                    location = '?' + location.substring( 0, location.length - 1 );
                    window.location = window.location.origin + window.location.pathname + location;
                    <?php } ?>
                });

                jQuery('.wpc_filter_by').trigger( 'change' );
            });
        </script>

        <div class="wpc_filter_block">
            <?php if( count( $filtered ) != count( $keys ) && $lists_count != 0 ) { ?>
                <div class="wpc_filter">
                    <?php _e( 'Filters', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <div class="wpc_filter_wrapper">
                        <label style="float: left;width:100%;"><?php _e( 'Filter By', WPC_CLIENT_TEXT_DOMAIN ) ?>:<br />
                            <select style="float: left;width:100%;" class="wpc_filter_by">
                                <?php foreach( $attr as $key=>$value ) {
                                    if( !isset( $_GET['wpc_filter_' . $key ] ) ) { ?>
                                        <option value="<?php echo $key ?>"><?php echo $value['title'] ?></option>
                                    <?php }
                                } ?>
                            </select>
                        </label>

                        <?php if( $ajax_filter ) { ?>
                            <div class="wpc_ajax_content">
                                <div class="wpc_loading_overflow">
                                    <div class="wpc_small_ajax_loading"></div>
                                </div>
                                <div class="wpc_overflow_content">
                                    <div class="wpc_filter_selectors"></div>
                                    <input type="button" value="<?php _e( 'Apply Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_add_filter button-primary">
                                </div>
                            </div>
                        <?php } else { ?>
                            <?php foreach( $attr as $key=>$value ) {
                                if( !isset( $_GET['wpc_filter_' . $key ] ) && isset( $value['list'] ) && !empty( $value['list'] ) ) { ?>
                                    <div class="wpc_filter_selector <?php echo $key ?>">
                                        <label style="float: left;width:100%;"><?php echo $value['title'] ?>:<br />
                                            <select style="float: left;width:100%;" class="wpc_filter_value">
                                                <?php foreach( $value['list'] as $list_value=>$list_title ) { ?>
                                                    <option value="<?php echo $list_value ?>"><?php echo $list_title ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </div>
                                <?php }
                            } ?>
                            <input type="button" value="<?php _e( 'Apply Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_add_filter button-primary">
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <div class="wpc_active_filters_wrapper">
                <?php foreach( $filtered as $key ) {
                    if( isset( $attr[$key]['list'][$_GET['wpc_filter_' . $key]] ) ) { ?>
                        <div class="wpc_active_filter_wrapper" data-filter_by="<?php echo $key ?>" data-filter_value="<?php echo $_GET['wpc_filter_' . $key] ?>">
                            <?php echo $attr[$key]['title'] . ': ' . $attr[$key]['list'][$_GET['wpc_filter_' . $key]] ?>
                            <div class="wpc_remove_filter">&times;</div>
                        </div>
                    <?php }
                } ?>
            </div>
        </div>
        <?php $field = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        return $field;
    }


    function build_filter_hiddens( $fields = array() ) {
        if( count( $fields ) == 0 ) {
            return '';
        }
        ob_start(); ?>
        <?php foreach( $fields as $key ) { ?>
            <?php if( isset( $_REQUEST['wpc_filter_' . $key] ) ) { ?>
                <input type="hidden" name="wpc_filter_<?php echo $key ?>" value="<?php echo $_REQUEST['wpc_filter_' . $key] ?>" />
            <?php } ?>
        <?php } ?>
        <?php $html = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        return $html;
    }


    function parse_wpc_filter( $filters = array() ) {
        global $wpdb;
        $where_filter = '';

        foreach( $filters as $key=>$query_string ) {
            if ( isset( $_GET['wpc_filter_' . $key] ) && !empty( $_GET['wpc_filter_' . $key] ) ) {
                $where_filter .= $wpdb->prepare( $query_string, $_GET['wpc_filter_' . $key] );
            }
        }

        return $where_filter;
    }


    function gen_vertical_tabs( $tabs, $args = array() ) {
        ob_start(); ?>

        <script type="text/javascript">

            jQuery(document).ready( function() {
                jQuery('body').on('click', "#tab-headers li", function() {
                    if( jQuery(this).hasClass('disabled') ) {
                        return false;
                    }

                    if( jQuery(this).hasClass('active') ) {
                        return false;
                    }

                    if( jQuery(this).find('a').attr('href').indexOf("#") == 0 && jQuery(jQuery(this).find('a').attr('href')).length > 0 ) {
                        jQuery("#tab-container").find(".tab-content").addClass('invisible');
                        jQuery("#tab-container").find(jQuery(this).find('a').attr('href')).removeClass('invisible');

                        jQuery(this).parents("#tab-headers").find('li').removeClass('active');
                        jQuery(this).addClass('active');

                        return false;
                    } else if( jQuery(this).find('a').attr('href').indexOf("admin-ajax.php") != -1 ) {
                        var obj = jQuery(this);
                        if( jQuery("#wpc_tab_" + obj.data('tab_id')).html() == '' ) {
                            jQuery.ajax({
                                type: "POST",
                                url: jQuery(this).find('a').attr('href'),
                                dataType: "html",
                                success: function (data) {

                                    jQuery(".tab-content").addClass('invisible');
                                    jQuery("#wpc_tab_" + obj.data('tab_id')).html(data).removeClass('invisible');

                                    obj.parents("#tab-headers").find('li').removeClass('active');
                                    obj.addClass('active');

                                    <?php echo ( !empty( $args['ajax_response'] ) ) ? $args['ajax_response'] : '' ?>
                                }
                            });
                        } else {
                            jQuery(".tab-content").addClass('invisible');
                            jQuery("#wpc_tab_" + obj.data('tab_id')).removeClass('invisible');

                            obj.parents("#tab-headers").find('li').removeClass('active');
                            obj.addClass('active');
                        }
                        return false;
                    }
                });
            });

        </script>

        <ul id="tab-headers" style="<?php echo ( !empty( $args['width'] ) ) ? 'width:' . $args['width'] . ';' : '' ?>">
            <?php
            $i = 0;
            foreach ( $tabs as $tab ) { ?>
                <li class="<?php echo ( $tab['active'] ) ? 'active' : '' ?> <?php echo ( !empty( $tab['disabled'] ) && $tab['disabled'] ) ? 'disabled' : '' ?> <?php echo ( !empty( $tab['before_label'] ) ) ? 'inner_content' : '' ?>" data-tab_id="<?php echo $i ?>">
                    <?php echo ( !empty( $tab['before_label'] ) ) ? $tab['before_label'] : '' ?>
                    <a href="<?php echo $tab['href'] ?>" ><?php echo $tab['label'] ?></a>
                </li>
                <?php $i++;
            } ?>
        </ul>

        <?php $vertical_tabs = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $vertical_tabs;
    }

}

endif;