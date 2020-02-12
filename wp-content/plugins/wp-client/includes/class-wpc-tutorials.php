<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Tutorials' ) ) :

class WPC_Tutorials {

    /**
     * The single instance of the class.
     *
     * @var WPC_Tutorials
     * @since 4.5
     */
    protected static $_instance = null;

    private $capability = array(
        'administrator' => 1,
        'wpc_admin' => 1
    );
    private $instructions = array();
    private $sections = array();

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Tutorials is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Tutorials - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {

    }

    function get_started_content( $content ) {
        $sections_list = array();
        foreach( $this->instructions as $key=>$section ) {
            foreach( $section as $step ) {
                $sections_list[ $key ] = 1;
            }
        }
        ob_start();
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('.wpc_start_tutorial').unbind('click');
                    jQuery('.wpc_start_tutorial').click(function(e) {
                        var section = jQuery(this).data('section');
                        var href = jQuery(this).prop('href');
                        jQuery(this).append('<img style="margin: 0 0 -3px 5px;" alt="" src="<?php echo WPC()->plugin_url; ?>/images/ajax_loading.gif" />');
                        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', {
                            action  : 'control-pointer',
                            step    : 0,
                            section : section
                        }, function() {
                            window.location = href;
                        });
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    });
                });
            </script>
            <h3 class="hndle"><?php _e( 'Getting Started with WP-Client', WPC_CLIENT_TEXT_DOMAIN ); ?></h3>
            <h3><?php _e( 'STEP ONE: Watch this video!', WPC_CLIENT_TEXT_DOMAIN ); ?></h3>
            <p><?php _e( 'Setting up a Client Portal that is uniquely functional to your website & business will take an investment of time from your side. The best possible thing you can do before getting started is to watch the videos provided on this page so that you understand the concepts behind WP-Client.', WPC_CLIENT_TEXT_DOMAIN ); ?></p>
            <h4 class="wpc_protip"><?php _e( 'PRO TIP: Don\'t ignore this advice!', WPC_CLIENT_TEXT_DOMAIN ); ?></h4>
            <a href="/wp-admin/admin.php?page=wpclients&tab=get_started" target="_blank" style="float:left; margin-bottom:15px;">(<?php _e( 'Click Here to open video in new tab to help follow along', WPC_CLIENT_TEXT_DOMAIN ); ?>)</a>
            <iframe src="https://player.vimeo.com/video/329186134" width="100%" height="534" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
            <br>
            <br>
            <br>


        <?php
        $content .= ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $content;
    }

    function set_array_help( $array, $method ) {
        $flag = false;
        if( count( $this->capability ) ) {
            foreach( array_keys( $this->capability ) as $cap ) {
                if( current_user_can( $cap ) ) {
                    $flag = true;
                    break;
                }
            }
        }
        if( !$flag ) return $array;

        $sections_list = array();
        foreach( $this->instructions as $key=>$section ) {
            foreach( $section as $step ) {
                if( isset( $_GET['page'] ) && $_GET['page'] == $step['page'] ) {
                    if( ( isset( $_GET['tab'] ) && isset( $step['tab'] ) && $_GET['tab'] == $step['tab'] ) || ( empty( $_GET['tab'] ) && empty( $step['tab'] ) ) ) {
                        $sections_list[ $key ] = 1;
                    }
                }
            }
        }
        ob_start();
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('.wpc_start_tutorial').click(function(e) {
                        var section = jQuery(this).data('section');
                        var href = jQuery(this).prop('href');
                        jQuery(this).append('<img style="margin: 0 0 -3px 5px;" alt="" src="<?php echo WPC()->plugin_url; ?>/images/ajax_loading.gif" />');
                        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', {
                            action  : 'control-pointer',
                            step    : 0,
                            section : section
                        }, function() {
                            window.location = href;
                        });
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    });
                });
            </script>
            <?php if( !empty( $array ) ) { ?>
                <h4><?php _e( 'Help', WPC_CLIENT_TEXT_DOMAIN ); ?></h4>
            <?php } ?>
            <ul>
                <?php if( !empty( $array ) ) { ?>
                    <li><a href="<?php echo add_query_arg( array( 'page' => 'wpclients', 'tab' => 'get_started' ), admin_url( 'admin.php' ) ); ?>" target="_blank"><?php _e( 'Get started page', WPC_CLIENT_TEXT_DOMAIN ); ?></a></li>
                <?php }
                foreach( array_keys( $sections_list ) as $val ) {
                    if( isset( $this->instructions[ $val ][0]['url'] ) ) {
                        $url = $this->instructions[ $val ][0]['url'];
                    } else if( isset( $this->instructions[ $val ][0]['page'] ) ) {
                        $query_array = array(
                            'page' => $this->instructions[ $val ][0]['page']
                        );
                        if( isset( $this->instructions[ $val ][0]['tab'] ) ) {
                            $query_array['tab'] = $this->instructions[ $val ][0]['tab'];
                        }
                        $url = add_query_arg( $query_array, admin_url('admin.php') );
                    }
                    ?>
                    <li><a href="<?php echo $url; ?>" class="wpc_start_tutorial" data-section="<?php echo $val; ?>"><?php printf( __( 'Tutorial: %s', WPC_CLIENT_TEXT_DOMAIN ), isset( $this->sections[ $val ] ) ? $this->sections[ $val ] : '' ); ?></a></li>
                <?php } ?>
            </ul>
        <?php
        $array['sidebar'] = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $array;
    }

    function init() {
        $flag = false;
        if( count( $this->capability ) ) {
            foreach( array_keys( $this->capability ) as $cap ) {
                if( current_user_can( $cap ) ) {
                    $flag = true;
                    break;
                }
            }
        }
        if( !$flag ) return false;

        $this->add_section( 'quick_overview', __( 'Quick Overview', WPC_CLIENT_TEXT_DOMAIN ) . ' <span class="description">(5 mins)</span>' );

        $this->add_step( 'quick_overview', '.wpc_logo', array(
            'page' => 'wpclients',
            'title' => __( 'Quick Overview: Introduction', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'WP-Client allows you to create private Client Areas in your site, and interact with your clients using Files, Private Messages, Invoices and more... Click Next to see how it works!', WPC_CLIENT_TEXT_DOMAIN )
        ) );

        $this->add_step( 'quick_overview', '.wp-submenu .current', array(
            'page' => 'wpclient_clients',
            'title' => __( 'Quick Overview: Members Menu', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'In the Members menu, you can manage all of your Clients, Staff, Managers, WPC-Admins, as well as create Custom Fields for Clients and Staff...', WPC_CLIENT_TEXT_DOMAIN )
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclient_clients',
            'title' => __( 'Quick Overview: Clients', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Clients are the core users of the plugin. When a client is created, they will have their own private HUB Page created, and you will be able assign them files, send them private messages, and more. From this tab, you can manage your existing clients, as well as add new ones.', WPC_CLIENT_TEXT_DOMAIN )
//                'description' => __( 'Client - it is main role in plugin. They can be registered them self (optional in settings) or created from admin area by Administrators, Plugin Admins, Managers.', WPC_CLIENT_TEXT_DOMAIN )
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclient_clients',
            'tab'   => 'staff',
            'title' => __( 'Quick Overview: Staff', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Client\'s Staff users essentially act as "child" accounts for Clients. They may be managers that work for your client, or employees that need access to the content on the client portal, such as uploaded files or estimates/invoices. When Client\'s Staff users login, they will see a portal that is nearly identical to that of their assigned "parent" Client. You can manage next and existing Staff users from this tab.', WPC_CLIENT_TEXT_DOMAIN )
//                'description' => __( 'Staff - it is related with Client role. They can be created by Client (optional in settings) or created from admin area by Administrators, Plugin Admins, Managers. And they see almost the same that Client but with less capabilities.', WPC_CLIENT_TEXT_DOMAIN )
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclient_clients',
            'tab'   => 'admins',
            'title' => __( 'Quick Overview: WPC-Admins', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'You can manage WPC-Admins from this tab. Users with the WPC-Admin role have full access to WP-Client settings, but no access to the rest of your WordPress installation. This is ideal if you want to delegate the admin duties of the plugin to a staff member, but don\'t want to give them full WordPress admin level capabilities.', WPC_CLIENT_TEXT_DOMAIN ),
//                'description' => __( 'Plugin Admin - it is role for manage all plugin feature but without any other capabilities on admin area. They can be just created from admin area by Administrators.', WPC_CLIENT_TEXT_DOMAIN )
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclient_clients',
            'tab'   => 'managers',
            'title' => __( 'Quick Overview: WPC-Managers', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'The WPC-Manager role falls somewhere between the admin and the client. You can think of them as admins, but with fewer permissions, and access to only specifically assigned clients. Manager permissions and access can be controlled from the Settings-->Capabilities menu. This role is great if you have multiple people in your company that need to access client data, but you only want them to be able to interact with certain clients (instead of all of them).', WPC_CLIENT_TEXT_DOMAIN ),
//                'description' => __( 'Manager - it is role for control Client and their content. They can be just created from admin area by Administrators, Plugin Admins.', WPC_CLIENT_TEXT_DOMAIN )
        ) );

        $this->add_step( 'quick_overview', '.wp-submenu .current', array(
            'page' => 'wpclients_content',
            'title' => __( 'Quick Overview: Content Menu', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'In the Content menu, you can manage all of the information and resources that your clients will see. This includes HUB Pages, Portal Pages, files, and Private Messages. You can also manage Circles from this menu. More details for each in the next step!', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_content',
            'title' => __( 'Quick Overview: Portal Pages', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Portal Pages are pages that can be assigned to one, some, or all clients in an installation. Any client that is assigned to a Portal Page will be able to access that page, and they will see generally the same info as any other client (with exceptions to placeholders and shortcodes that are in the page, which render unique information based on who is logged in). You can use Portal Pages if you have information that you want to display for multiple clients.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_content',
            'tab'   => 'portalhubs',
            'title' => __( 'Quick Overview: HUB Pages', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'The HUB Page is a page that is unique to each individual client, and can only be viewed by that client. By default, the HUB Page is essentially the client\'s "landing page", the first page they see after logging in. All clients have one HUB Page, and this page is automatically generated when the client is first created. The contents of the HUB Pages can be edited individually from this menu, or via the Templates menu.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_content',
            'tab'   => 'files',
            'title' => __( 'Quick Overview: File Sharing', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'In File Sharing, you can upload files via several different methods, and then assign those files to Clients and/or Circles. By default clients will be notified via email when a new file is uploaded and assigned to them, and they will be able to access that file once they login to their portal. Clients can also be allowed to upload their own files from their portal, and those files will appear in this menu as well.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_content',
            'tab'   => 'private_messages',
            'title' => __( 'Quick Overview: Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Private Messages allow you to send messages to one, some, or all users in your installation. This includes Clients, Staff, WPC-Managers, and WPC-Admins. You can choose to send direct messages to only one user, or you can create a Dialogue by including multiple users, which will allow you to all communicate together.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_content',
            'tab'   => 'circles',
            'title' => __( 'Quick Overview: Circles', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Circles are essentially client groups. You can group various clients into Circles, which will allow you to easily bulk assign things like files, pages, WPC-Managers, and other resources to multiple clients at one time. When creating Circles, you also have options to have that Circle automatically selected during various actions, such as to automatically assign all new clients to a particular Circle.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_templates',
            'tab'   => 'portal_page',
            'title' => __( 'Quick Overview: Portal Page Template', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'This Template will be used whenever a new Portal Page is generated. You can modify this Portal Page Template with your desired content to better suit your needs, and any changes will reflected in new Portal Pages that are created after the change. Existing Portal Pages will not be affected by any changes to the Portal Page Template.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_templates',
            'tab'   => 'emails',
            'title' => __( 'Quick Overview: Email Templates', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'From this menu, you can modify the content of all of the various email notifications that send out from WP-Client, such as when a client uploads a new file, when a new private message is received, etc. You can also optionally turn individual notifications on and off using the checkboxes next to the desired email.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients_templates',
            'tab'   => 'php_templates',
            'title' => __( 'Quick Overview: Shortcode Templates', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Shortcode Templates contain the actual code of the various shortcodes within the plugin, such as for display client\'s assigned files and Portal Pages. If you would like to customize these shortcodes, that can be done. However, we highly recommend only advanced users make changes here. Any changes can be undone using the "Reset to Default" button for each template.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );


        $this->add_step( 'quick_overview', '.wp-submenu .current', array(
            'page' => 'wpclients_extensions',
            'title' => __( 'Quick Overview: Extensions Menu', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'WP-Client uses add-ons called Extensions to expand the functionality of the core plugin. These can be installed/activated as needed from this menu. Different Extensions are available by default, based on what WP-Client license level you originally purchased. Extensions can also be purchased "a la carte" from your Control Panel. Extension functionality ranges from estimates/invoicing, to project management, to help desk functionality, and much more. You can browse the list in this menu to see what is available to you, and you can install/activate as you see fit. To begin the installation, just click Install under the desired Extension. You will then need to Activate the Extension, and enter the Extension\'s unique API Key.', WPC_CLIENT_TEXT_DOMAIN ),
        ) );

        $this->add_step( 'quick_overview', '.wpc-nav-tab-wrapper .nav-tab-active', array(
            'page' => 'wpclients',
            'tab' => 'get_started',
            'title' => __( 'Quick Overview: Finished', WPC_CLIENT_TEXT_DOMAIN ),
            'description' => __( 'Congratulations! You should have a better idea of how the plugin works now. The next tutorials will give you more detailed information about the various WP-Client features!', WPC_CLIENT_TEXT_DOMAIN ),
        ) );


//
//
//            $this->add_section( 'create_hub_template', __( 'Create HUB Template', WPC_CLIENT_TEXT_DOMAIN ) . ' <span class="description">(5 mins)</span>' );
//
//            $this->add_step( 'create_hub_template', '.wrap .menu .active', array(
//                'page' => 'wpclients_templates',
//                'tab' => 'hubpage',
//                'title' => __( 'Create HUB Template: Introduction', WPC_CLIENT_TEXT_DOMAIN ),
//                'description' => __( 'HUB Page Templates allow you to control what your clients see when they first login to your site. By default, there is one HUB Page Template, which can be modified to fit your needs. Alternatively, you can create multiple new HUB Page Templates, and fill those with your desired content (links, shortcodes, files, etc). You can assign Clients and/or Circles directly to specific HUB Page Templates. If a client is not assigned to a Template, they will see whatever Template is marked as “Default” when they login. There are 3 types of HUB Templates...', WPC_CLIENT_TEXT_DOMAIN )
//            ) );
//
//            $this->add_step( 'create_hub_template', '.wrap #add_ez_template', array(
//                'page' => 'wpclients_templates',
//                'tab' => 'hubpage',
//                'title' => __( 'Create HUB Template: EZ HUB Template', WPC_CLIENT_TEXT_DOMAIN ),
//                'description' => __( 'In EZ HUB Template you will see menu with selected elements (plugin shortcodes) and also you will be able to add custom content before/after this menu. You can set settings for shourtcodes and add\remove them from menu.', WPC_CLIENT_TEXT_DOMAIN )
//            ) );
//
//            $this->add_step( 'create_hub_template', '.wrap #add_advanced_template', array(
//                'page' => 'wpclients_templates',
//                'tab' => 'hubpage',
//                'title' => __( 'Create HUB Template: Advanced HUB Template', WPC_CLIENT_TEXT_DOMAIN ),
//                'description' => __( 'In Advanced HUB Template you will see menu with selected elements (plugin shortcodes) and also you will be able to add custom content before/after this menu', WPC_CLIENT_TEXT_DOMAIN )
//            ) );
//
//



        add_action( 'admin_enqueue_scripts', array( &$this, 'add_js_css' ) );
        add_action( 'admin_print_styles', array( &$this, 'admin_styles' ) );
        add_action( "wp_ajax_control-pointer", array( &$this, 'ajax_control_pointer' ) );

        return '';
    }

    function admin_styles() {
        ?>
        <style type="text/css">
            .wpc_dismiss_button {
                position: absolute;
                top: -15px;
                right: -15px;
                width: 30px;
                height: 30px;
                background: url(<?php echo WPC()->plugin_url; ?>/images/fancy_close.png) no-repeat top left;
                z-index: 999;
            }

            .wp-pointer-buttons > div {
                width: 100%;
                float: left;
            }

            .wp-pointer-buttons .next.button, .wp-pointer-buttons .prev.button, .wp-pointer-buttons .wpc-tut-step {
                float: right;
            }

            .wp-pointer-buttons .prev.prev_section.button {
                float: left;
            }

            .wp-pointer-buttons .wpc-tut-step {
                height: 28px;
                line-height: 28px;
                margin: 0 10px;
                display: block;
            }
        </style>
        <?php
    }

    function add_js_css() {
        $step_data = get_user_meta( get_current_user_id(), 'wpc_tutorial', true );

        if( !isset( $step_data['section'] ) || !isset( $this->instructions[ $step_data['section'] ][ $step_data['step'] ] ) ) return '';

        $current_step = $this->instructions[ $step_data['section'] ][ $step_data['step'] ];
        if( $this->compare_urls( isset( $current_step['page'] ) ? $current_step['page'] : '', isset( $current_step['tab'] ) ? $current_step['tab'] : '' ) ) {
            foreach( $this->instructions[ $step_data['section'] ] as $key=>$val ) {
                if( empty( $val['url'] ) && !empty( $val['page'] ) ) {
                    $query_array = array(
                        'page' => $val['page']
                    );
                    if( isset( $val['tab'] ) ) {
                        $query_array['tab'] = $val['tab'];
                    }
                    $this->instructions[ $step_data['section'] ][ $key ]['url'] = add_query_arg( $query_array, admin_url('admin.php') );
                }
            }

            $sections_array = array_keys( $this->instructions );
            $section_index = array_search( $step_data['section'], $sections_array );
            $prev_section_key = ( $section_index > 0 && isset( $sections_array[ $section_index - 1 ] ) ) ? $sections_array[ $section_index - 1 ] : '';
            $prev_section_url = '';
            if( $section_index > 0 ) {
                if( isset( $this->instructions[ $prev_section_key ][0]['url'] ) ) {
                    $prev_section_url = $this->instructions[ $prev_section_key ][0]['url'];
                } else if( isset( $this->instructions[ $prev_section_key ][0]['page'] ) ) {
                    $query_array = array(
                        'page' => $this->instructions[ $prev_section_key ][0]['page']
                    );
                    if( isset( $this->instructions[ $prev_section_key ][0]['tab'] ) ) {
                        $query_array['tab'] = $this->instructions[ $prev_section_key ][0]['tab'];
                    }
                    $prev_section_url = add_query_arg( $query_array, admin_url('admin.php') );
                }
            }

            $next_section_key = isset( $sections_array[ $section_index + 1 ] ) ? $sections_array[ $section_index + 1 ] : '';
            //var_Dump($next_section_key); exit;
            $next_section_url = '';
            if( isset( $this->instructions[ $next_section_key ][0]['url'] ) ) {
                $next_section_url = $this->instructions[ $next_section_key ][0]['url'];
            } else if( isset( $this->instructions[ $next_section_key ][0]['page'] ) ) {
                $query_array = array(
                    'page' => $this->instructions[ $next_section_key ][0]['page']
                );
                if( isset( $this->instructions[ $next_section_key ][0]['tab'] ) ) {
                    $query_array['tab'] = $this->instructions[ $next_section_key ][0]['tab'];
                }
                $next_section_url = add_query_arg( $query_array, admin_url('admin.php') );
            }


            wp_enqueue_style( 'wp-pointer' );
            wp_enqueue_script( 'wpc-tutorial', false, array( 'jquery', 'wp-pointer' ), WPC_CLIENT_VER, true );

            wp_localize_script( 'wpc-tutorial', 'wpc_instructions', $this->instructions[ $step_data['section'] ] );
            wp_localize_script( 'wpc-tutorial', 'wpc_tutorial', array(
                'ajax_url'            => admin_url('admin-ajax.php'),
                'plugin_url'          => WPC()->plugin_url,
                'prev_section_url'    => $prev_section_url,
                'prev_section_key'    => $prev_section_key,
                'current_url'         => WPC()->get_current_url(),
                'current_section_key' => $step_data['section'],
                'next_section_url'    => $next_section_url,
                'next_section_key'    => $next_section_key,
                'current_step'        => isset( $step_data['step'] ) ? $step_data['step'] : 0,
                'current_section'     => $step_data['section'],
                'text'                => array(
                    'previous'         => __( 'Previous', WPC_CLIENT_TEXT_DOMAIN ),
                    'previous_section' => __( 'Previous Section', WPC_CLIENT_TEXT_DOMAIN ),
                    'next'             => __( 'Next', WPC_CLIENT_TEXT_DOMAIN ),
                    'next_section'     => __( 'Next Section', WPC_CLIENT_TEXT_DOMAIN ),
                    'dismiss'          => __( 'Dismiss', WPC_CLIENT_TEXT_DOMAIN ),
                    'counter'          => sprintf( __( 'Step %s of %d', WPC_CLIENT_TEXT_DOMAIN ), '{step_num}', count( $this->instructions[ $step_data['section'] ] ) )
                )
            ) );
        }

        return '';
    }

    function ajax_control_pointer() {
        if( !empty( $_POST['dismiss'] ) ) {
            delete_user_meta( get_current_user_id(), 'wpc_tutorial' );
            return '';
        }
        $step_data = get_user_meta( get_current_user_id(), 'wpc_tutorial', true );
        $step_data = is_array( $step_data ) ? $step_data : array();

        if( isset( $_POST['section'] ) && $_POST['section'] !== '' ) {
            $step_data['section'] = $_POST['section'];
        }
        if( isset( $_POST['step'] ) && $_POST['step'] !== '' ) {
            $step_data['step'] = $_POST['step'];
        }

        update_user_meta( get_current_user_id(), 'wpc_tutorial', $step_data );

        return '';
    }

    function compare_urls( $page, $tab = '' ) {
        if( isset( $_GET['page'] ) && $_GET['page'] == $page ) {
            if( isset( $_GET['tab'] ) && $_GET['tab'] == $tab ) {
                return true;
            } else if( empty( $url['tab'] ) && empty( $tab ) ) {
                return true;
            }
        }
        return false;
    }

    function add_cap( $cap ) {
        $this->capability[ $cap ] = 1;
    }

    function add_step( $section, $selector, $args ) {
        $data = array(
            'selector' => $selector
        );
        $data['content'] = '<h3>' . WPC()->make_clickable( $args['title'], array( 'target' => '_blank' ) ) . '</h3>';
        $data['content'] .= '<p>' . WPC()->make_clickable( $args['description'], array( 'target' => '_blank' ) ) . '</p>';
        if( !empty( $args['page'] ) ) {
            $data['page'] = $args['page'];
        } else if( empty( $args['url'] ) ) {
            return false;
        }
        if( !empty( $args['tab'] ) ) {
            $data['tab'] = $args['tab'];
        }
        if( !empty( $args['url'] ) ) {
            $data['url'] = $args['url'];
        }
        if( !empty( $args['js'] ) ) {
            $data['js'] = $args['js'];
        }

        $data['position'] =  !empty( $args['position'] ) ? $args['position'] : 'top';
        $data['x'] =  !empty( $args['x'] ) ? $args['x'] : 0;
        $data['y'] =  !empty( $args['y'] ) ? $args['y'] : 0;

        $this->instructions[ $section ][] = $data;

        return '';
    }

    function add_section( $key, $title ) {
        $this->sections[ $key ] = $title;
    }
}

endif;