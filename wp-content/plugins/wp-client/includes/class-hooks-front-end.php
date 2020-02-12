<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Hooks_Front_End' ) ) :

class WPC_Hooks_Front_End {

    /**
    * constructor
    **/
    function __construct() {

        WPC()->current_plugin_page['hub_id'] = 0;
        WPC()->current_plugin_page['client_id'] = get_current_user_id();

        add_action( 'init', array( &$this, 'checking_for_set_global_vars' ) );

        add_action( 'init', array( &$this, 'change_password_page' ), 2 );

        add_action( 'wp_enqueue_scripts', array( &$this, 'wp_css_js' ), 99 );

        //change template
        add_filter( 'page_template', array( &$this, 'get_page_template' ) ) ;

        /* members hooks */
        add_action( 'init', array( WPC()->hooks(), 'WPC_Members->save_password_after_edit') );
        add_filter ( 'authenticate', array( WPC()->hooks(), 'WPC_Members->verification_archive_user' ), 120, 3 );
        add_action( 'login_enqueue_scripts', array( WPC()->hooks(), 'WPC_Members->password_protect_css_js' ), 99 );
        add_action( 'init', array( WPC()->hooks(), 'WPC_Members->client_login_from_' ), 10 );
        add_action( 'wp_login', array( WPC()->hooks(), 'WPC_Members->alert_login_successful'), 10, 2 );
        add_action( 'wp_login_failed', array( WPC()->hooks(), 'WPC_Members->alert_login_failed') );


        /*
        HUB/Portal pages hooks
        */
        add_filter( 'posts_request', array( WPC()->hooks(), 'WPC_Pages->query_for_wpc_client_pages' ) );
        //protect clientpage and hubpage
        add_filter( 'the_posts', array( WPC()->hooks(), 'WPC_Pages->filter_posts' ), 99, 2 );
        add_filter( 'the_posts', array( WPC()->hooks(), 'WPC_Pages->filter_posts_clientspage_new' ), 99, 3 );
        add_filter( 'the_posts', array( WPC()->hooks(), 'WPC_Pages->filter_posts_portalhub' ), 99, 4 );
        //get template for Portal Page \ HUB
        add_filter( 'template_include',  array( WPC()->hooks(), 'WPC_Pages->get_clientpage_template' ), 99 );
        add_filter( 'body_class', array( WPC()->hooks(), 'WPC_Pages->body_class_for_clientpages' ), 99 );
        add_filter( "previous_post_link", array( WPC()->hooks(), 'WPC_Pages->remove_portal_pages_pagination' ), 999, 5 );
        add_filter( "next_post_link", array( WPC()->hooks(), 'WPC_Pages->remove_portal_pages_pagination' ), 999, 5 );
        add_filter( 'request', array( WPC()->hooks(), 'WPC_Pages->portalhub_request' ), 10, 1 );
        add_filter( 'wpml_is_redirected', array( WPC()->hooks(), 'WPC_Pages->portalhub_redirect' ), 10, 3 );
        add_action( 'wp', array( WPC()->hooks(), 'WPC_Pages->add_no_cache_headers' ), 999 );
        //add_action( 'pre_get_posts', array( WPC()->hooks(), 'WPC_Pages->exclude_portalhubs' ), 10, 1 );


        add_filter( 'sidebars_widgets', array( WPC()->hooks(), 'WPC_Widgets->widget_filter_sidebars_widgets' ), 10);

        add_action( 'wp_head', array( &$this, 'add_custom_scripts' ), 10 );

        $wpc_custom_style = WPC()->get_settings( 'custom_style' );
        if ( !empty( $wpc_custom_style['in_footer'] ) ) {
            add_action( 'wp_footer', array( &$this, 'add_custom_style' ), 99 );
        } else {
            add_action( 'wp_head', array( &$this, 'add_custom_style' ), 99 );
        }

        add_action( 'init', array( WPC()->hooks(), 'WPC_Files->files_shortcode_actions_handler' ), 30 );


        //custom login
        add_action( 'login_head', array( &$this, 'custom_login_bm' ), 99 );
        add_filter( 'login_headerurl', array( &$this, 'custom_login_logo_url' ), 99 );
        add_filter( 'login_headertitle', array( &$this, 'custom_login_logo_title' ), 99 );
        add_filter( 'login_form_login', array( &$this, '_set_login_form_flag' ), 99 );


        add_filter( 'page_template', array( &$this, 'logn_page_template' ) ) ;

        //tocheck
        add_filter( 'wp_nav_menu_args',  array( &$this, 'custom_menu' ),  9 );

        //captcha for login form
        add_action( 'wp_authenticate', array( WPC()->hooks(), 'WPC_Captcha->check_login_tools' ) );
        add_action( 'login_form', array( WPC()->hooks(), 'WPC_Captcha->init_captcha' ), 1000 );

        add_action( 'wp_login_errors', array( &$this, 'wp_login_errors' ) );

        add_action( 'login_form', array( &$this, 'terms_on_login_form' ), 999 );
        add_action( 'login_form', array( &$this, 'privacy_on_login_form' ), 999 );
        add_action( 'register_form', array( &$this, 'privacy_on_register_form' ), 999 );

        //redirect for not loged in users
        add_action( 'template_redirect', array( &$this, 'hide_site' ) );

    }


    /*
    * Checking for run set_global_vars
    */
    function checking_for_set_global_vars() {
        if ( current_user_can( 'wpc_manager' ) || current_user_can( 'administrator' ) ) {
            add_action( 'wp', array( WPC(), 'set_global_vars' ) );
        } else {
            WPC()->set_global_vars();
        }
    }

    function change_password_page() {
        global $wpdb;

        if ( is_user_logged_in() && ! wp_doing_ajax() && ! ( isset( $_REQUEST['wpc_action'] ) && 'temp_password' == $_REQUEST['wpc_action'] ) ) {
            $user_id = get_current_user_id();

            $passwords = $wpdb->get_row( $wpdb->prepare("SELECT u.user_pass, um.meta_value as temp_pass
                    FROM {$wpdb->users} u, {$wpdb->usermeta} um
                    WHERE u.ID = um.user_id AND u.ID = %d AND um.meta_key = 'wpc_temporary_password'", $user_id), ARRAY_A);

            if( isset( $passwords['temp_pass'] ) && !isset( $_COOKIE['wpc_key'] ) ) {
                if( $passwords['temp_pass'] == md5( $passwords['user_pass'] ) ) {
                    WPC()->redirect( add_query_arg( array(
                        'wpc_action' => 'temp_password',
                        'wpc_to_redirect' => WPC()->get_current_url()
                    ), WPC()->get_login_url() ) );
                } else {
                    delete_user_meta( $user_id, 'wpc_temporary_password' );
                }
            }
        }
    }


    /*
    * Filter the template path to page{}.php templates.
    */
    function get_page_template( $template ) {
        global $wp_query;

        if ( isset( $wp_query->query_vars['wpc_page'] ) && '' != $wp_query->query_vars['wpc_page'] ) {
            if ( file_exists( get_template_directory() . "/page-wpc_{$wp_query->query_vars['wpc_page']}.php" ) )
                return get_template_directory() . "/page-wpc_{$wp_query->query_vars['wpc_page']}.php";
        }

        return $template;
    }


    /*
    * Include JS\CSS
    */
    function wp_css_js() {

        //hardcode option for some customers with another jQuery
        //this option was set in customer's database manually
        if ( !get_option( 'wpc_disable_jquery' ) ) {
            wp_enqueue_script( 'jquery' );
        }

        //custom style
        $uploads = wp_upload_dir();
        if ( file_exists( $uploads['basedir'] . '/wpc_custom_style.css' ) ) {
            wp_enqueue_style( 'wpc_custom_style' );
        }
        wp_enqueue_style( 'wpc_user_style' );
        wp_enqueue_style( 'wpc_user_general_style' );
        wp_enqueue_style( 'wp-client-ez-hub-bar-style' );

        wp_enqueue_script( 'wp-client-ez_hub_bar', false, array(), WPC_CLIENT_VER, true );
    }


    function add_custom_scripts() {
        global $post;

        $wpc_pages = WPC()->get_settings( 'pages' );

        if( isset( $post->ID ) && array_search( $post->ID, $wpc_pages ) !== false ) {

            $wpc_pages_keys = array_flip( $wpc_pages );
            $page_key = str_replace( '_page_id', '', $wpc_pages_keys[$post->ID] );

            /*our_hook_
                hook_name: wpc_client_add_custom_scripts_to_page
                hook_title: Add custom JS & CSS to WP-Client Pages
                hook_description: Hook runs when WP-Client Page is loaded.
                hook_type: action
                hook_in: wp-client
                hook_location class.user.php
                hook_param: string $page_key
                hook_since: 3.7.8
            */
            do_action( 'wpc_client_add_custom_scripts_to_page', $page_key );
        }
    }


    function add_custom_style() {
        $wpc_custom_style = WPC()->get_settings( 'custom_style' );

        if( isset( $wpc_custom_style['style'] ) && !empty( $wpc_custom_style['style'] ) ) {
            ?>
            <style>

                <?php echo $wpc_custom_style['style'] ?>

            </style>
            <?php
        }
    }


    /*
    * Custom login - CSS
    */
    function custom_login_bm() {
        if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
            $wpc_custom_login = WPC()->get_settings( 'custom_login' );

            if ( !isset( $wpc_custom_login['cl_enable'] ) || 'yes' == $wpc_custom_login['cl_enable'] ) {
                // output styles
                echo '<link rel="stylesheet" type="text/css" href="' . WPC()->plugin_url . 'css/custom-login.css' . '" />';
                echo '<style>';

                if ( !empty( $wpc_custom_login['cl_background'] ) ) {
                    ?>

                    #login h1 a {
                    background-image: none !important;
                    background-color: transparent !important;
                    }

                    #login h1 a {
                    width:312px;
                    height:67px;
                    margin-left:7px;
                    border-radius:3px;
                    margin-bottom:10px;
                    }

                    #login {
                    background:url(<?php echo $wpc_custom_login['cl_background'] ?>) top center no-repeat;
                    padding: 125px 0px 10px 0px !important;
                    }
                    <?php
                }

                // text colour
                if ( !empty( $wpc_custom_login['cl_color'] ) ) {
                    ?>
                    #login,
                    #login label {
                    color:#<?php echo $wpc_custom_login['cl_color'] ?>;
                    }

                    <?php
                }


                // hide form border
                if ( isset( $wpc_custom_login['cl_form_border'] ) && 'yes' == $wpc_custom_login['cl_form_border'] ) {
                    ?>
                    #login {
                    border: none;
                    box-shadow: none;

                    }
                    <?php
                }


                // text colour
                if ( !empty( $wpc_custom_login['cl_error_color'] ) ) {
                    ?>
                    #login #login_error {
                    color:#<?php echo $wpc_custom_login['cl_error_color'] ?>;
                    }
                    <?php
                }

                // text colour
                if ( !empty( $wpc_custom_login['cl_backgroundColor'] ) ) {
                    ?>
                    html,
                    body.login {
                    background:#<?php echo $wpc_custom_login['cl_backgroundColor'] ?> !important;
                    }
                    <?php
                }

                // text colour
                if ( !empty( $wpc_custom_login['cl_linkColor'] ) ) {
                    ?>
                    .login #login a {
                    color:#<?php echo $wpc_custom_login['cl_linkColor'] ?> !important;
                    }
                    <?php
                }

                echo '</style>';
            }
        }
    }


    /*
    * Custom login - link
    */
    function custom_login_logo_url() {
        $wpc_custom_login = WPC()->get_settings( 'custom_login' );

        if ( !isset( $wpc_custom_login['cl_enable'] ) || 'yes' == $wpc_custom_login['cl_enable'] ) {
            //logo link
            if ( !empty ( $wpc_custom_login['cl_logo_link'] ) ) {
                return $wpc_custom_login['cl_logo_link'];
            }
        }

        return '';
    }


    /*
    * Custom login - text
    */
    function custom_login_logo_title() {
        $wpc_custom_login = WPC()->get_settings( 'custom_login' );

        if ( !isset( $wpc_custom_login['cl_enable'] ) || 'yes' == $wpc_custom_login['cl_enable'] ) {
            //logo text
            if ( !empty( $wpc_custom_login['cl_logo_title'] ) ) {
                return $wpc_custom_login['cl_logo_title'];
            }
        }

        return '';
    }


    function _set_login_form_flag() {
        WPC()->flags['login_form'] = true;
    }


    /*
    * Filter the template path to page{}.php templates.
    */
    function logn_page_template( $template ) {
        global $post;

        $wpc_pages = WPC()->get_settings( 'pages' );

        if( isset( $wpc_pages['login_page_id'] ) && $wpc_pages['login_page_id'] == $post->ID ) {
            do_action( 'login_init' );

            $action = isset( $_REQUEST['wpc_action'] ) ? $_REQUEST['wpc_action'] : 'login';

            if( isset( $_GET['key'] ) )
                $action = 'resetpass';

            //validate action so as to default to the login screen
            if ( !in_array( $action, array( 'postpass', 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login' ), true ) && false === has_filter( 'login_form_' . $action ) )
                $action = 'login';

            do_action( 'login_form_' . $action );
        }

        return $template;
    }


    /*
    * Set custom menu
    */
    function custom_menu( $args ) {
        $location = isset( $args['theme_location'] ) ? $args['theme_location'] : '';
        if ( empty( $location ) ) {
            return $args;
        }

        $option = WPC()->get_settings( 'advanced_locations' );

        if ( is_user_logged_in() ) {
            //only for clients and staff

            if ( '1' != get_user_meta( get_current_user_id(), 'to_approve', true )
                && ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) ) {

                if ( ! empty( $option['circle'][ $location ] ) ) {
                    //if exist settings for circles
                    $client_groups = WPC()->groups()->get_client_groups_id( WPC()->current_plugin_page['client_id'] );
                    foreach ( $option['circle'][ $location ] as $key => $menu ) {
                        $circles = ! empty( $option['circle']['wpc_circles_' . $location ][ $key ] )
                            ? explode( ',', $option['circle']['wpc_circles_' . $location ][ $key ] )
                            : array();
                        if ( ! empty( $menu ) && ! empty( $circles ) && array_intersect( $client_groups, $circles ) ) {
                            $new_menu = $menu;
                            break;
                        }
                    }
                }

                if ( ! isset( $new_menu ) && ! empty( $option['login'][ $location ] ) ) {
                    $new_menu = $option['login'][ $location ];
                }
            }
        } elseif ( ! empty( $option['logout'][ $location ] ) ) {
            //for Not logged-in
            $new_menu = $option['logout'][ $location ];
        }

        //change menu
        if ( isset( $new_menu ) && is_numeric( $new_menu ) ) {
            $menu = get_term( $new_menu, 'nav_menu' );
            if ( !empty( $menu ) && isset( $menu->name ) ) {
                $args['menu'] = $menu->name;
            }
        }
        return $args;
    }


    function wp_login_errors( $errors ) {

        if ( isset( $_GET['wpc_login_error'] ) && 'captcha' == $_GET['wpc_login_error'] ) {
            $errors->add('invalid_captcha', __( 'Invalid captcha', WPC_CLIENT_TEXT_DOMAIN ), 'error');
        }

        if ( isset( $_GET['wpc_login_error'] ) && 'terms' == $_GET['wpc_login_error'] ) {
            $errors->add('invalid_captcha', __( 'Sorry, you must agree to the Terms/Conditions to continue', WPC_CLIENT_TEXT_DOMAIN ), 'error');
        }
        
        if ( isset( $_GET['wpc_login_error'] ) && 'privacy' == $_GET['wpc_login_error'] ) {
            $errors->add('invalid_captcha', __( 'Sorry, you must agree to the Privacy Policy to continue', WPC_CLIENT_TEXT_DOMAIN ), 'error');
        }

        return $errors;
    }
    
    
    /**
     * Add "privacy_agree" checkbox to login form
     * @return string html
     */
    function privacy_on_login_form() {
        $wpc_privacy = WPC()->get_settings( 'privacy' );

        if ( !isset( $wpc_privacy['using_privacy'] ) || 'yes' != $wpc_privacy['using_privacy'] ) {
            return '';
        }

        if ( !empty( $wpc_privacy['using_privacy_form'] )
            && in_array( 'login', $wpc_privacy['using_privacy_form'] )
            && get_privacy_policy_url() ) {

            $text_format = empty( $wpc_privacy['privacy_text'] ) ? __( 'I accept {link}', WPC_CLIENT_TEXT_DOMAIN) : $wpc_privacy['privacy_text'];
            $text = str_replace( '{link}', get_the_privacy_policy_link(), $text_format );
            ?>
            <p class="wpc_clear">
                <input type="hidden" name="privacy_agree" value="0" />
                <label class="privacy_label" for="privacy_agree"><input type="checkbox" id="privacy_agree" name="privacy_agree" value="1" <?php checked( isset( $wpc_privacy['privacy_default_checked'] ) && 'yes' == $wpc_privacy['privacy_default_checked'] ); ?> /> <?= $text; ?></label>
            </p>
            <?php
        }
    }


    /**
     * Add "privacy_agree" checkbox to registration form
     * @return string html
     */
    function privacy_on_register_form() {
        $wpc_privacy = WPC()->get_settings( 'privacy' );

        if ( !isset( $wpc_privacy['using_privacy'] ) || 'yes' != $wpc_privacy['using_privacy'] ) {
            return '';
        }

        if ( !empty( $wpc_privacy['using_privacy_form'] )
            && in_array( 'registration', $wpc_privacy['using_privacy_form'] )
            && get_privacy_policy_url() ) {

            $text_format = empty( $wpc_privacy['privacy_text'] ) ? __( 'I accept {link}', WPC_CLIENT_TEXT_DOMAIN) : $wpc_privacy['privacy_text'];
            $text = str_replace( '{link}', get_the_privacy_policy_link(), $text_format );
            ?>
            <p class="wpc_clear">
                <input type="hidden" name="privacy_agree" value="0" />
                <label class="privacy_label" for="privacy_agree"><input type="checkbox" id="privacy_agree" name="privacy_agree" value="1" <?php checked( isset( $wpc_privacy['privacy_default_checked'] ) && 'yes' == $wpc_privacy['privacy_default_checked'] ); ?> /> <?= $text; ?></label>
            </p>
            <?php
        }
    }

    
    function terms_on_login_form() {
        $wpc_terms = WPC()->get_settings( 'terms' );

        if ( !isset( $wpc_terms['using_terms'] ) || 'yes' != $wpc_terms['using_terms'] )
            return '';

        if ( !empty( $wpc_terms['using_terms_form'] ) && in_array( 'login', $wpc_terms['using_terms_form'] ) ) {

            //NOTE: wrote label below in one line - for delete BR in html code for some sites
            ?>
            <div class="wpc_clear"></div>
            <p>
                <input type="hidden" name="terms_agree" value="0" />
                <label class="terms_label" for="terms_agree"><input type="checkbox" style="margin: 0;" id="terms_agree" name="terms_agree" value="1" <?php checked( isset( $wpc_terms['terms_default_checked'] ) && 'yes' == $wpc_terms['terms_default_checked'] ); ?> /> <?php echo ( !empty( $wpc_terms['terms_text'] ) ) ? $wpc_terms['terms_text'] : __( 'I agree.', WPC_CLIENT_TEXT_DOMAIN); ?> <a href="<?php echo !empty( $wpc_terms['terms_hyperlink'] ) ? $wpc_terms['terms_hyperlink'] : '#'; ?>" target="_blank" title="<?php _e('Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN); ?>"><?php _e('Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN); ?></a></label>
            </p>
            <div class="wpc_clear"></div>
            <?php
        }
    }


    function hide_site() {
        $wpc_common_secure = WPC()->get_settings( 'common_secure' );

        if ( !is_user_logged_in()
            && isset( $wpc_common_secure['hide_site'] )
            && 'yes' == $wpc_common_secure['hide_site'] ) {

            $request_url = str_replace( 'index.php/', '', $_SERVER['REQUEST_URI'] );

            $url_parts = explode( '?', $request_url, 2 );

            // Remove slash
            $base = rtrim( $url_parts[0], "/" );
            $exp = explode( '/', $base );
            $super_base = end( $exp );

            $valid_bases = array(
                'lost-password',
            );

            global $post;
            $wpc_pages = WPC()->get_settings( 'pages' );


            $page_ids = array();
            if ( ! empty( $wpc_pages['login_page_id'] ) ) {
                $page_ids[] = $wpc_pages['login_page_id'];
            }
            if ( ! empty( $wpc_pages['client_registration_page_id'] ) ) {
                $page_ids[] = $wpc_pages['client_registration_page_id'];
            }
            $white_list = array();
            if ( ! empty( $wpc_common_secure['pages_white_list'] ) ) {
                $white_list = explode( "\n", str_replace( array( "\n\r", "\r\n", "\r" ), "\n", $wpc_common_secure['pages_white_list'] ) );
            }
            $current_url = WPC()->get_current_url();
            $v = in_array( $current_url, $white_list );

            if( ( empty( $post->ID ) || !in_array( $post->ID, $page_ids ) )
                && !in_array( $super_base, $valid_bases ) && !empty( $wpc_pages['login_page_id'] )
                && !in_array( $current_url, $white_list )  ) {
                WPC()->redirect( add_query_arg(
                    array( 'wpc_to_redirect' => $current_url ),
                    WPC()->get_login_url()
                ) );
            }
        }

    }

}

endif;

new WPC_Hooks_Front_End();