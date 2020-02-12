<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Pages' ) ) :

final class WPC_Pages {

    /**
     * The single instance of the class.
     *
     * @var WPC_Pages
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Pages is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Pages - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

    }


    function replace_wp_title( $title, $id = null ) {
        $post = get_post( $id );
        if ( ! empty( $post ) && $post->post_type == 'portalhub' ) {
            $args = array(
                'client_id' => WPC()->members()->get_client_id()
            );
            $title = WPC()->replace_placeholders( $title, $args );
        }

        return $title;
    }


    function wp_head() {
        global $post;
        if ( ! empty( $post ) && $post->post_type == 'portalhub' ) { ?>
            <title><?php wp_title( '', true, 'right' ); ?></title>
        <?php }
    }


    function replace_placeholders_in_content( $content ) {
        global $post;
        if ( ! empty( $post ) && $post->post_type == 'portalhub' ) {
            $args = array(
                'client_id' => WPC()->members()->get_client_id()
            );
            $content = WPC()->replace_placeholders( $content, $args );
        }

        return $content;
    }


    function portalhub_permalink( $permalink, $post ) {

        if ( $post->post_type == 'portalhub' ) {
            $locale = apply_filters( 'wpc_portalhub_locale', false, $post );
            return WPC()->get_hub_link( $locale );
        }

        return $permalink;
    }


    /*
    * create default content for clients
    */
    function create_hub_page( $args = array(), $create_portal = false ) {
        $this->remove_shortcodes();

        if ( $create_portal ) {

            // add Portal Page for this user
            $wpc_templates_clientpage = WPC()->get_settings( 'templates_clientpage', '' );
            $wpc_templates_clientpage = html_entity_decode( $wpc_templates_clientpage );
            $wpc_templates_clientpage = str_replace( "{client_business_name}", $args['business_name'], $wpc_templates_clientpage );
            $wpc_templates_clientpage = str_replace( "{page_title}", $args['business_name'], $wpc_templates_clientpage );

            $client_page = array(
                'comment_status'    => 'closed',
                'ping_status'       => 'closed',
                'post_author'       => 1,
                'post_content'      => $wpc_templates_clientpage,
                'post_name'         => $args['business_name'],
                'post_status'       => 'publish',
                'post_title'        => $args['business_name'],
                'post_type'         => 'clientspage'
            );

            $client_page_id = wp_insert_post( $client_page );

            WPC()->assigns()->set_assigned_data( 'portal_page', $client_page_id, 'client', array( $args['client_id'] ) );

        }
    }



    /**
     * Remove our shortcodes
     */
    //ToDo: check if need it - or rewrite it.
    function remove_shortcodes() {
        $shortcodes = array(
            'wpc_client',
            'wpc_client_private',
            'wpc_client_theme',
            'wpc_client_loginf',
            'wpc_client_logoutb',
            'wpc_client_filesla',
            'wpc_client_uploadf',
            'wpc_client_fileslu',
            'wpc_client_pagel',
            'wpc_client_com',
            'wpc_client_graphic',
            'wpc_client_registration_form',
            'wpc_client_registration_successful',
            'wpc_client_business_info',
            'wpc_client_add_staff_form',
            'wpc_client_staff_directory',
            'wpc_client_business_name',
            'wpc_client_contact_name',
            'wpc_client_hub_page',
            'wpc_client_portal_page',
            'wpc_client_get_page_link',
            'wpc_client_edit_portal_page',
            'wpc_redirect_on_login_hub',
            'wpc_client_error_image',
        );

        foreach( $shortcodes as $shortcode ) {
            remove_shortcode( $shortcode );
        }
    }


    /*
    * Sort Prtal pages
    */
    function sort_portalpages_for_client( $mypages_id, $sort_type = '', $sort = '' ) {
        //sorting
        if ( isset( $sort_type ) && 'date' == strtolower( $sort_type ) ) {
            //by date
            if ( isset( $sort ) && 'desc' == strtolower( $sort ) )
                rsort( $mypages_id );
            else
                sort( $mypages_id );
        } elseif (  isset( $sort_type ) && 'title' == strtolower( $sort_type ) ) {
            //by alphabetical
            if ( is_array( $mypages_id ) && $mypages_id ) {

                foreach( $mypages_id as $page_id ) {
                    $mypage = get_post( $page_id, 'ARRAY_A' );
                    $for_sort[$page_id] = strtolower( nl2br( $mypage['post_title'] ) );
                }

                if ( isset( $sort ) && 'desc' == strtolower( $sort ) )
                    arsort( $for_sort );
                else
                    asort( $for_sort );

                $mypages_id = array_keys( $for_sort );
            }
        }
        return $mypages_id;
    }


    /**
     * Get post for PortalHUB for current client
     *
     * @param int $user_id
     * @return array|null|WP_Post
     */
    function get_portalhub_for_client( $user_id ) {

        if ( user_can( $user_id, 'wpc_client_staff' ) ) {
            $user_id = get_user_meta( $user_id, 'parent_client_id', true );
        }

        $template_ids = WPC()->assigns()->get_assign_data_by_assign( 'portalhub', 'client', $user_id );
        $circle_ids = WPC()->groups()->get_client_groups_id( $user_id );

        foreach ( $circle_ids as $circle_id ) {
            $circle_templates = WPC()->assigns()->get_assign_data_by_assign( 'portalhub', 'circle', $circle_id );
            $template_ids = array_merge( $template_ids, $circle_templates );
        }

        $template_post = get_posts( array(
            'post_type'     => 'portalhub',
            'post_status'   => 'publish',
            'meta_key'      => 'wpc_default_template',
            'meta_value'    => true,
            'posts_per_page'=> 1
        ) );
        $template_post = $template_post[0];

        if ( ! empty( $template_ids  ) ) {

            sort( $template_ids );

            $current_priority = 0;
            foreach ( $template_ids as $template_id ) {
                $priority = get_post_meta( $template_id, 'wpc_template_priority' );

                if ( $priority >= $current_priority ) {
                    $temp_post = get_post( $template_id );
                    if( $temp_post->post_status != 'publish' ) continue;
                    $current_priority = $priority;
                    $template_post = $temp_post;
                }
            }
        }


        $template_post = apply_filters( 'wpc_get_portalhub_for_client', $template_post );
        return $template_post;
    }



    /*
    *  Preview link on edit HUB page
    */
    function hub_edit_sample_permalink_html( $return, $id, $new_title, $new_slug ) {
        $post = get_post( $id );

        // The Current URL
        $schema = is_ssl() ? 'https://' : 'http://';
        $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        if ( $post && 'clientspage' == $post->post_type ) {
            $return = '<strong>' . __( 'Permalink:', WPC_CLIENT_TEXT_DOMAIN ) . '</strong> ' . '<span id="sample-permalink" tabindex="-1">' . get_permalink( $post->ID ) . '</span>';

            //make link
            if( current_user_can('wpc_admin_user_login') ) {
                $hub_preview_url = get_admin_url( null,'admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . get_current_user_id() . $post->ID ) . '&page_name=portal_page&page_id=' . $post->ID . '&referer_url=' . urlencode( $current_url ) );
            } else {
                $hub_preview_url = get_permalink( $post->ID );
            }

            $return .= ' <span id="view-post-btn"><a href="'. $hub_preview_url .'" class="button button-small" onclick=\'return confirm("' . sprintf( __( "You will be re-logged-in under the role of %s to preview this page. Continue?", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\'>' . __( 'Preview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
        } elseif ( $post && 'portalhub' == $post->post_type ) {
            $return = '<strong>' . __( 'Permalink:', WPC_CLIENT_TEXT_DOMAIN ) . '</strong> ' . '<span id="sample-permalink" tabindex="-1">' . WPC()->get_hub_link() . '</span>';

            //make link
            if ( current_user_can( 'wpc_admin_user_login' ) ) {
                $hub_preview_url = get_admin_url( null,'admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . get_current_user_id() . $post->ID ) . '&page_name=hub&page_id=' . $post->ID . '&referer_url=' . urlencode( $current_url ) );
            } else {
                $hub_preview_url = WPC()->get_hub_link();
            }

            $return .= ' <span id="view-post-btn"><a href="'. $hub_preview_url .'" class="button button-small" onclick=\'return confirm("' . sprintf( __( "You will be re-logged-in under the role of %s to preview this page. Continue?", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\'>' . __( 'Preview', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';
        }
        return $return;
    }



    function our_cpt_hide_misc_actions( $post ) {
        if( 'portalhub' == $post->post_type || 'clientspage' == $post->post_type ) { ?>
            <style type="text/css">
                .misc-pub-section.misc-pub-post-status {
                    display: none;
                }
                .misc-pub-section.misc-pub-revisions {
                    display: none;
                }
                .misc-pub-section.misc-pub-visibility {
                    display: none;
                }
            </style>
        <?php }
    }



    /**
     * Create new portal page Category
     **/
    function create_portalpage_category( $args ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'wpc_client_portal_page_categories',
            array( 'cat_name'  => trim( $args['name'] ) ),
            array( '%s' )
        );

        $category_id = $wpdb->insert_id;

        if( !empty( $category_id ) ) {
            //set clients
            $clients_array = !empty( $args['clients'] ) ? explode( ',', $args['clients'] ) : array();
            WPC()->assigns()->set_assigned_data( 'portal_page_category', $category_id, 'client', $clients_array );

            //set Client Circle
            $circles_array = !empty( $args['circles'] ) ? explode( ',', $args['circles'] ) : array();
            WPC()->assigns()->set_assigned_data( 'portal_page_category', $category_id, 'circle', $circles_array );

            return $category_id;
        }

        return false;
    }


    /**
     * Update portal page Category
     **/
    function update_portalpage_category( $args ) {
        global $wpdb;

        $category_id = $args['id'];
        $wpdb->update(
            $wpdb->prefix . 'wpc_client_portal_page_categories',
            array( 'cat_name'  => trim( $args['name'] ) ),
            array( 'cat_id'  => $category_id ),
            array( '%s' ),
            array( '%d' )
        );

        if ( isset( $args['clients'] ) && isset( $args['circles'] ) ) {
            //delete all assigns
            WPC()->assigns()->delete_all_object_assigns( 'portal_page_category', $category_id );

            //set clients
            $clients_array = !empty( $args['clients'] ) ? explode( ',', $args['clients'] ) : array();
            WPC()->assigns()->set_assigned_data( 'portal_page_category', $category_id, 'client', $clients_array );

            //set Client Circle
            $circles_array = !empty( $args['circles'] ) ? explode( ',', $args['circles'] ) : array();
            WPC()->assigns()->set_assigned_data( 'portal_page_category', $category_id, 'circle', $circles_array );
        }

    }


    /**
     * Check unique name of PP category
     *
     * @global object $wpdb
     * @param string $cat_name
     * @param array $exclude
     * @return boolean
     */
    function portalpage_category_exists( $cat_name, $exclude = array() ) {
        global $wpdb;

        if ( !empty( $exclude ) && !is_array( $exclude ) ) {
            $exclude = (array)$exclude;
        }
        //checking that category not exist with other ID
        $result = $wpdb->get_var( $wpdb->prepare(
            "SELECT cat_id
                FROM {$wpdb->prefix}wpc_client_portal_page_categories
                WHERE LOWER(cat_name) = '%s'
                AND cat_id NOT IN('" . implode( "','", $exclude ) . "')",
            strtolower( $cat_name )
        ) );

        return (bool)$result;
    }


    /**
     * Delete portal page Category
     **/
    function delete_portalpage_category( $cat_id ) {
        global $wpdb;
        //delete category
        $wpdb->query( $wpdb->prepare(
            "DELETE
                FROM {$wpdb->prefix}wpc_client_portal_page_categories
                WHERE cat_id = %d",
            $cat_id
        ) );

        WPC()->assigns()->delete_all_object_assigns( 'portal_page_category', $cat_id );

        $args = array(
            'post_type' => 'clientspage',
            'meta_query' => array(
                array(
                    'key' => '_wpc_category_id',
                    'value' => $cat_id
                )
            )
        );

        $postslist = get_posts( $args );

        if( 0 < count( $postslist ) ) {
            foreach( $postslist as $post ) {
                wp_delete_post( $post->ID, true );
            }
        }
    }


    /**
     * Reassign  portal page from one Category to another
     * @param int $cat_id - Category ID where files now
     * @param int $reassign_cat_id - Category ID to move portal page
     **/
    function reassign_portalpage_from_category( $cat_id, $reassign_cat_id ) {
        $args = array(
            'post_type' => 'clientspage',
            'meta_query' => array(
                array(
                    'key' => '_wpc_category_id',
                    'value' => $cat_id
                )
            )
        );

        $postslist = get_posts( $args );

        foreach( $postslist as $post ) {
            update_post_meta( $post->ID, '_wpc_category_id', $reassign_cat_id );
        }
    }


    /**
     * Change query for show wpc pages
     **/
    function query_for_wpc_client_pages( $q ) {
        global $wp_query, $wpdb;

        if ( $q == $wp_query->request ) {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
            $client_id = get_current_user_id();

            //for hub page set wp_query params (for body class)
            if( !empty( $wp_query->query['portalhub'] ) ) {
                $wp_query->is_page = true;
                $wp_query->is_single = false;
            }

            //for edit portal page
            if ( isset( $wp_query->query_vars['wpc_page'] ) && 'edit_portal_page' == $wp_query->query_vars['wpc_page'] ) {
                if ( is_user_logged_in() ) {

                    if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] && get_user_meta( $client_id, 'verify_email_key', true ) ) {
                        WPC()->redirect( add_query_arg( array( 'type' => 'verify_email' ), WPC()->get_slug( 'error_page_id' ) ) );
                    }

                    $wpc_pages = WPC()->get_settings( 'pages' );

                    if ( isset( $wpc_pages['edit_portal_page_id'] ) && 0 < $wpc_pages['edit_portal_page_id'] ) {
                        $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['edit_portal_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                    }
                } else {
                    WPC()->redirect( WPC()->get_login_url() );
                }
            }
            //for verify email
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'acc_activation' == $wp_query->query_vars['wpc_page'] ) {
                $key = ( isset( $wp_query->query_vars['wpc_page_value'] ) ) ? $wp_query->query_vars['wpc_page_value'] : '';
                $user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'verify_email_key' AND meta_value = '%s'", $key ) );
                if ( $user ) {
                    delete_user_meta( $user, 'verify_email_key', $key );
                }

                if( is_user_logged_in() ) {
                    if ( !empty( $wpc_clients_staff['url_after_verify'] ) ) {
                        $link = $wpc_clients_staff['url_after_verify'];
                    } else {
                        $link = add_query_arg( array( 'msg' => 've' ), WPC()->get_hub_link() );
                    }
                    WPC()->redirect( $link );
                } else {
                    WPC()->redirect( WPC()->get_login_url() );
                }
            }
            //for edit staff page
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'edit_staff' == $wp_query->query_vars['wpc_page'] ) {
                if ( is_user_logged_in() ) {
                    $wpc_pages = WPC()->get_settings( 'pages' );

                    if ( isset( $wpc_pages['edit_staff_page_id'] ) && 0 < $wpc_pages['edit_staff_page_id'] ) {
                        $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['edit_staff_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                    }
                }  else {
                    WPC()->redirect( WPC()->get_login_url() );
                }
            }
            //for feedback wizard page
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'feedback_wizard' == $wp_query->query_vars['wpc_page'] ) {
                if ( is_user_logged_in() ) {
                    $wpc_pages = WPC()->get_settings( 'pages' );

                    if ( isset( $wpc_pages['feedback_wizard_page_id'] ) && 0 < $wpc_pages['feedback_wizard_page_id'] ) {
                        $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['feedback_wizard_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                    }
                }  else {
                    WPC()->redirect( WPC()->get_login_url() );
                }
            }
            //for invoicing/invoicing payment pages
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && ( 'invoicing' == $wp_query->query_vars['wpc_page'] || 'invoicing_payment' == $wp_query->query_vars['wpc_page'] ) ) {
                if ( is_user_logged_in() ) {
                    $wpc_pages = WPC()->get_settings( 'pages' );

                    if ( isset( $wpc_pages['invoicing_page_id'] ) && 0 < $wpc_pages['invoicing_page_id'] ) {
                        $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['invoicing_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                    }
                }  else {
                    WPC()->redirect( WPC()->get_login_url() );
                }
            }
            //for paid registration payment pages
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'paid_registration' == $wp_query->query_vars['wpc_page'] ) {

                if ( !is_user_logged_in() || get_user_meta( get_current_user_id(), 'wpc_need_pay', true ) ) {
                    $wpc_pages = WPC()->get_settings( 'pages' );

                    if ( isset( $wpc_pages['client_registration_page_id'] ) && 0 < $wpc_pages['client_registration_page_id'] ) {
                        $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['client_registration_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                    }
                } else {
                    WPC()->redirect( get_home_url() );
                }
            }
            //for payment process pages
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'payment_process' == $wp_query->query_vars['wpc_page'] ) {

                $wpc_pages = WPC()->get_settings( 'pages' );

                if ( isset( $wpc_pages['payment_process_page_id'] ) && 0 < $wpc_pages['payment_process_page_id'] ) {
                    $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['payment_process_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                }
            }
            //start IPN
            elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'payment_ipn' == $wp_query->query_vars['wpc_page'] ) {
                if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {
                    global $wpc_payments_core;

                    $order = $wpc_payments_core->get_order_by( $wp_query->query_vars['wpc_page_value'], 'order_id' );

                    $wpc_payments_core->handle_ipn( $order, $wp_query->query_vars['wpc_page_value'] );
                }
            }
            //downloader
            elseif( isset( $wp_query->query_vars['wpc_page'] ) && 'wpc_downloader' == $wp_query->query_vars['wpc_page'] && isset( $wp_query->query_vars['wpc_page_value'] ) && !empty( $wp_query->query_vars['wpc_page_value'] ) ) {
                do_action( 'wpc_' . $wp_query->query_vars['wpc_page_value'] . '_file_downloader', isset( $_GET['id'] ) ? $_GET['id'] : '' );
            }
        }

        return $q;
    }


    function filter_posts_clientspage_new( $posts, $query ) {
        global $wpdb;

        $filtered_posts = array();

        //if empty
        if ( empty( $posts ) )
            return $posts;

        foreach( $posts as $post ) {

            //Don't exclude not Portal Page posts
            if ( 'clientspage' != $post->post_type ) {
                $filtered_posts[] = $post;
                continue;
            }

            //filter Portal Pages access script below
            // hide Portal Page for not logged in users
            if ( ! is_user_logged_in() ) {
                $wpc_enable_custom_redirects = WPC()->get_settings( 'enable_custom_redirects', 'no' );
                $default_non_login_redirects = WPC()->get_settings( 'default_non_login_redirects' ) ;
                if ( 'yes' == $wpc_enable_custom_redirects && ! empty( $default_non_login_redirects['url'] ) ) {
                    WPC()->redirect( $default_non_login_redirects['url'] );
                }
                continue;
            }

            //hide Portal Page if valify Email feature in on and client has not verified Email
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
            if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] && get_user_meta( get_current_user_id(), 'verify_email_key', true ) )
                continue;

            //block not appoved clients
            if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'administrator' ) ) {
                $user_id = WPC()->current_plugin_page['client_id'];

                if ( '1' == get_user_meta( $user_id, 'to_approve', true ) ) {
                    WPC()->redirect( WPC()->get_slug( 'error_page_id' ) );
                }
            }

            //enqueue styles if necessary
            $scheme_key = get_post_meta( $post->ID, '_wpc_style_scheme', true );
            $this->add_scheme_style( $scheme_key );


            $category_id = get_post_meta( $post->ID, '_wpc_category_id', true );
            //Portal Pages in Portal Pages Categories with Clients access
            $users_category = ( isset( $category_id ) ) ? WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $category_id, 'client' ) : array();

            //Portal Pages with Clients access
            $user_ids = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'client' );
            $user_ids = array_merge( $users_category, $user_ids );

            //Portal Pages in Portal Pages Categories with Client Circles access
            $groups_category = ( isset( $category_id ) ) ? WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $category_id, 'circle' ) : array();

            //Portal Pages with Client Circles access
            $groups_id = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'circle' );
            $groups_id = array_merge( $groups_category, $groups_id );

            //get clients from Client Circles
            foreach ( $groups_id as $group_id ) {
                $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
            }

            if ( ! empty( $user_ids ) )
                $user_ids = array_unique( $user_ids );

            //preview for manager
            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                $clients_ids = WPC()->members()->get_all_clients_manager();

                if ( is_array( $clients_ids ) && 0 < count( $clients_ids ) ) {

                    $temp_user_id = 0;
                    $temp_users_id = array_intersect( $clients_ids, $user_ids );

                    if ( count( $temp_users_id ) ) {

                        if ( isset( $_COOKIE['wpc_preview_client'] ) &&
                            in_array( $_COOKIE['wpc_preview_client'], $user_ids ) &&
                            in_array( $_COOKIE['wpc_preview_client'], $clients_ids ) ) {

                            $temp_user_id = $_COOKIE['wpc_preview_client'];
                        }

                        $user_id = ( $temp_user_id ) ? $temp_user_id : $temp_users_id[0];
                        WPC()->current_plugin_page['client_id'] = $user_id;

                        WPC()->setcookie( "wpc_preview_client", $user_id, time() + 24*3600 );
                    }

                }

            }
            //preview for admins
            elseif( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                if ( is_array( $user_ids ) && count( $user_ids ) ) {

                    $temp_user_id = 0;

                    if ( isset( $_COOKIE['wpc_preview_client'] ) &&
                        in_array( $_COOKIE['wpc_preview_client'], $user_ids ) ) {

                        $temp_user_id = $_COOKIE['wpc_preview_client'];
                    }

                    $user_id = ( $temp_user_id ) ? $temp_user_id : $user_ids[0];
                    WPC()->current_plugin_page['client_id'] = $user_id;

                    WPC()->setcookie( "wpc_preview_client", $user_id, time() + 24*3600 );
                }
            }


            if ( empty( $user_id ) ) {
                $user_id = get_current_user_id();
            }

            $is_preview = apply_filters( 'wpc_is_portal_page_preview', false, $user_id );

            if ( $is_preview || ( ! empty( $user_ids ) && in_array( $user_id, $user_ids ) ) ) {

                $category_name = $wpdb->get_var( $wpdb->prepare(
                    "SELECT cat_name
                        FROM {$wpdb->prefix}wpc_client_portal_page_categories
                        WHERE cat_id = %d", $category_id
                ) );

                if ( empty( $category_name ) )
                    $category_name = '';

                //replace placeholders in content
                if ( isset( $post->post_content ) ) {
                    $args = array( 'client_id' => $user_id, 'portal_page_category' => $category_name, 'page_title' => $post->post_title );
                    $post->post_content = WPC()->replace_placeholders( $post->post_content, $args, 'portal_page' );
                }

                $filtered_posts[] = $post;
                continue;
            }

            WPC()->redirect( WPC()->get_slug( 'error_page_id' ) );

        }

        $posts = $filtered_posts;

        return $posts;
    }


    function filter_posts_portalhub( $posts, $query ) {

        //if empty
        if ( empty( $posts ) )
            return $posts;

        foreach( $posts as $key=>$post ) {
            //Don't exclude not Portal Page posts
            if ( 'portalhub' == $post->post_type ) {
                $user_id = get_current_user_id();

                if ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'administrator' ) ) {
                    $user_id = WPC()->current_plugin_page['client_id'];
                }

                if ( '1' == get_user_meta( $user_id, 'to_approve', true ) ) {
                    if( $query->is_main_query() ) {
                        wp_redirect( WPC()->get_slug( 'error_page_id' ) );
                        exit;
                    } else {
                        continue;
                    }
                }

                //replace placeholders for Start Pages title
                $args = array(
                    'client_id' => WPC()->members()->get_client_id()
                );
                $post->post_title = WPC()->replace_placeholders( $post->post_title, $args );

                //replace placeholders in content
                if ( ! empty( $post->post_content ) ) {
                    $args = array( 'client_id' => $user_id, 'page_title' => $post->post_title );
                    $post->post_content = WPC()->replace_placeholders( $post->post_content, $args, 'hub_page' );
                    $posts[$key] = $post;
                }

                //enqueue styles if necessary
                $scheme_key = get_post_meta( $post->ID, '_wpc_style_scheme', true );
                $this->add_scheme_style( $scheme_key );

            }
        }

        return $posts;
    }


    /**
     * Protect Cleint page and HUB from not logged user and Search Engine
     */
    function filter_posts( $posts, $query ) {
        global $wp_query, $wpdb;

        $filtered_posts = array();

        //if empty
        if ( empty( $posts ) )
            return $posts;

        $wpc_pages = WPC()->get_settings( 'pages' );
        $post_ids = array();
        foreach( $posts as $post ) {
            $post_ids[] = $post->ID;
        }

        $sticky_posts_array = array();
        if( ( isset( $wpc_pages['login_page_id'] ) && in_array( $wpc_pages['login_page_id'], $post_ids ) ) ||
            ( isset( $wpc_pages['edit_portal_page_id'] ) && in_array( $wpc_pages['edit_portal_page_id'], $post_ids ) ) ||
            ( isset( $wpc_pages['edit_staff_page_id'] ) && in_array( $wpc_pages['edit_staff_page_id'], $post_ids ) ) ) {
            $sticky_posts_array = get_option( 'sticky_posts' );
            $sticky_posts_array = ( is_array( $sticky_posts_array ) && 0 < count( $sticky_posts_array ) ) ? $sticky_posts_array : array();
        }

        foreach( $posts as $post ) {

            if( in_array( $post->ID, $sticky_posts_array ) ) {
                continue;
            }

            //add no follow, no index on plugin pages
            if ( ( ( isset( $wpc_pages )
                        && is_array( $wpc_pages )
                        && in_array( $post->ID, array_values( $wpc_pages ) )
                    )
                    || 'portalhub' == $post->post_type
                    || 'clientspage' == $post->post_type ) && $query->is_main_query() ) {

                add_action( 'wp_head', array( &$this, 'add_meta_to_plugin_pages' ), 99 );
            }

            //for logout
            if ( isset( $_REQUEST['logout'] ) && 'true' == $_REQUEST['logout'] ) {
                nocache_headers();
                wp_logout();
            }

            if ( isset( $wpc_pages['edit_portal_page_id'] ) && $post->ID == $wpc_pages['edit_portal_page_id'] ) {

                if ( is_user_logged_in() ) {

                    if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {

                        $edit_page = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );
                        if ( !$edit_page ) {
                            WPC()->redirect( WPC()->get_hub_link() );
                        }

                        if ( current_user_can( 'wpc_client_staff' ) )
                            $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
                        else
                            $user_id = get_current_user_id();

                        //block not appoved clients
                        if ( '1' == get_user_meta( $user_id, 'to_approve', true ) )
                            continue;

                        $user_ids       = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $edit_page->ID, 'client' );
                        $groups_id      = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $edit_page->ID, 'circle' );

                        $user_ids = ( is_array( $user_ids ) && 0 < count( $user_ids ) ) ? $user_ids : array();

                        //get clients from Client Circles
                        if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                            foreach( $groups_id as $group_id ) {
                                $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                            }

                        if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                            $user_ids = array_unique( $user_ids );

                        //client hasn't access to this page
                        if ( ( empty( $user_ids ) || !in_array( $user_id, $user_ids ) ) ) {
                            continue;
                        }

                        //actions from Edit ClientPage
                        if ( isset( $_POST['wpc_wpnonce'] ) && wp_verify_nonce( $_POST['wpc_wpnonce'], 'wpc_edit_clientpage' . $edit_page->ID ) ) {
                            //update ClientPage
                            if ( isset( $_POST['wpc_action'] ) && 'update' == $_POST['wpc_action'] ) {
                                $arg = array (
                                    'ID'            => $edit_page->ID,
                                    'post_title'    => $_POST['clientpage_title'],
                                    'post_content'  => $_POST['clientpage_content'],
                                );

                                define( 'WPC_CLIENT_NOT_SAVE_META', '1' );
                                wp_update_post( $arg );

                                //make link
                                if ( WPC()->permalinks ) {
                                    $redirect_link = WPC()->get_slug( 'edit_portal_page_id' ) . $edit_page->post_name ;
                                } else {
                                    $redirect_link = add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $edit_page->post_name ), WPC()->get_slug( 'edit_portal_page_id', false ) );
                                }

                                WPC()->redirect( $redirect_link );

                            }
                            //Delete ClientPage
                            elseif ( isset( $_POST['wpc_action'] ) && 'delete' == $_POST['wpc_action'] )  {
                                wp_delete_post( $edit_page->ID );
                                WPC()->redirect( WPC()->get_hub_link() );
                            }
                            //Cancel = return to HUB page
                            elseif ( isset( $_POST['wpc_action'] ) && 'cancel' == $_POST['wpc_action'] )  {
                                WPC()->redirect( WPC()->get_hub_link() );
                            }
                        }

                        $wp_query->is_page      = true;
                        $wp_query->is_home      = false;
                        $wp_query->is_singular  = true;
                        $filtered_posts[] = $post;
                        continue;
                    }
                }
                continue;
            }
            elseif ( isset( $wpc_pages['edit_staff_page_id'] ) && $post->ID == $wpc_pages['edit_staff_page_id'] ) {

                if ( is_user_logged_in() ) {

                    if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {

                        $wp_query->is_page      = true;
                        $wp_query->is_home      = false;
                        $wp_query->is_singular  = true;
                        $filtered_posts[] = $post;
                        continue;
                    }
                }
                continue;
            }
            elseif ( isset( $wpc_pages['payment_process_page_id'] ) && $post->ID == $wpc_pages['payment_process_page_id'] ) {
                $wp_query->is_page      = true;
                $wp_query->is_home      = false;
                $wp_query->is_singular  = true;
                $filtered_posts[] = $post;

                continue;
            }
            elseif( is_search() && $query->is_main_query() ) {
                if( isset( $post->post_type ) && 'clientspage' == $post->post_type ) {
                    if( is_user_logged_in() ) {
                        $user_id = WPC()->current_plugin_page['client_id'];

                        $category_id = get_post_meta( $post->ID, '_wpc_category_id', true );
                        //Portal Pages in Portal Pages Categories with Clients access
                        $users_category = ( isset( $category_id ) ) ? WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $category_id, 'client' ) : array();

                        //Portal Pages with Clients access
                        $user_ids = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'client' );
                        $user_ids = array_merge( $users_category, $user_ids );

                        //Portal Pages in Portal Pages Categories with Client Circles access
                        $groups_category = ( isset( $category_id ) ) ? WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $category_id, 'circle' ) : array();

                        //Portal Pages with Client Circles access
                        $groups_id = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $post->ID, 'circle' );
                        $groups_id = ( is_array( $groups_id ) ) ? array_merge( $groups_category, $groups_id ) : $groups_id;

                        //get clients from Client Circles
                        if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                            foreach( $groups_id as $group_id ) {
                                $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                            }

                        if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                            $user_ids = array_unique( $user_ids );

                        //preview for manager
                        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                            global $wpdb;

                            $clients_ids = WPC()->members()->get_all_clients_manager();

                            if ( is_array( $clients_ids ) && 0 < count( $clients_ids ) ) {

                                $temp_user_id = 0;
                                $temp_users_id = array_intersect( $clients_ids, $user_ids );

                                if ( count( $temp_users_id ) ) {

                                    if ( isset( $_COOKIE['wpc_preview_client'] ) &&
                                        in_array( $_COOKIE['wpc_preview_client'], $user_ids ) &&
                                        in_array( $_COOKIE['wpc_preview_client'], $clients_ids ) ) {

                                        $temp_user_id = $_COOKIE['wpc_preview_client'];
                                    }

                                    $user_id = ( $temp_user_id ) ? $temp_user_id : $temp_users_id[0];
                                    WPC()->current_plugin_page['client_id'] = $user_id;

                                    WPC()->setcookie( "wpc_preview_client", $user_id, time() + 24*3600 );
                                }

                            }

                        }
                        //preview for admins
                        elseif( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                            if ( is_array( $user_ids ) && count( $user_ids ) ) {

                                $temp_user_id = 0;

                                if ( isset( $_COOKIE['wpc_preview_client'] ) &&
                                    in_array( $_COOKIE['wpc_preview_client'], $user_ids ) ) {

                                    $temp_user_id = $_COOKIE['wpc_preview_client'];
                                }

                                $user_id = ( $temp_user_id ) ? $temp_user_id : $user_ids[0];
                                WPC()->current_plugin_page['client_id'] = $user_id;

                                WPC()->setcookie( "wpc_preview_client", $user_id, time() + 24*3600 );
                            }
                        }

                        if ( ( !empty( $user_ids ) && in_array( $user_id, $user_ids ) ) ) {
                            $filtered_posts[] = $post;
                            continue;
                        }
                    } else {

                        $wpc_enable_custom_redirects = WPC()->get_settings( 'enable_custom_redirects', 'no' );
                        $default_non_login_redirects = WPC()->get_settings( 'default_non_login_redirects' ) ;

                        if ( 'yes' == $wpc_enable_custom_redirects && ! empty( $default_non_login_redirects['url'] ) ) {
                            WPC()->redirect( $default_non_login_redirects['url'] );
                        }

                    }

                    continue;
                }
            }
            //add all other posts
            $filtered_posts[] = $post;
        }

        return $filtered_posts;
    }


    /**
     * Get template for Portal/HUB Pages from the assigned WP pages
     */
    function get_clientpage_template( $template ) {
        global $post;
        //for Start Pages and Portal Pages
        if ( ! empty( $post->post_type ) && ( 'clientspage' == $post->post_type || 'portalhub' == $post->post_type ) ) {

            $new_template = $template;
            $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
            if ( !empty( $page_template ) && 'default' != $page_template && $temp_path = locate_template( $page_template ) ) {
                $new_template = $temp_path;
            } else if( 'clientspage' == $post->post_type && $temp_path = locate_template('single-clientspage.php') ) {
                $new_template = $temp_path;
                $page_template = 'single-clientspage.php';
            } else if( 'portalhub' == $post->post_type && $temp_path = locate_template('single-portalhub.php') ) {
                $new_template = $temp_path;
                $page_template = 'single-portalhub.php';
            } else if( 'default' == $page_template ) {
                if( $new_template = get_page_template() ):
                elseif( $new_template = get_singular_template() ):
                else:
                    $new_template = get_index_template();
                endif;
            }

            if( $new_template != $template && !empty( $page_template ) ) {
                WPC()->current_plugin_page['post_id']   = $post->ID;
                WPC()->current_plugin_page['template']  = $page_template;

                //use filter for change template - for some themes
                add_filter( 'get_post_metadata', array( &$this, 'change_portal_page_template' ), 99, 4 );
            }

            $template = $new_template;
        }

        return $template;
    }


    /*
    * Filter for full-width for Portal pages (may not work for some themes)
    */
    function body_class_for_clientpages( $classes ) {
        global $post;

        if ( is_single() && 'clientspage' == $post->post_type ) {
            $page_template = get_post_meta( $post->ID, '_wp_page_template', true );

            if ( !empty( $page_template ) && 'page-templates/full-width.php' == $page_template )
                $classes[] = 'full-width';

        }

        return $classes;

    }


    /**
     * Remove
     *
     * @param $output
     * @param $format
     * @param string $link
     * @param string $post
     * @param string $adjacent
     * @return string
     */
    function remove_portal_pages_pagination( $output, $format, $link = '', $post = '', $adjacent = '' ) {
        if ( is_object( $post ) && ( 'clientspage' == $post->post_type || 'portalhub' == $post->post_type ) ) {
            return '';
        }

        return $output;
    }


    function portalhub_request( $query_request ) {

        $pages = WPC()->get_settings( 'pages' );
        $portalhub_slug = isset( $pages['portal_hub_slug'] ) ? $pages['portal_hub_slug'] : 'portal/portal-hub';

        $schema = is_ssl() ? 'https://' : 'http://';
        $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        if ( ( isset( $query_request['wpc_page'] ) && 'portalhub' == $query_request['wpc_page'] ) || ( empty( $portalhub_slug ) && home_url('/') == $current_url ) ) {
            if ( is_user_logged_in() ) {
                $portalhub = $this->get_portalhub_for_client( get_current_user_id() );

                //HUB preview feature for some Visual editors as Elementor;
                if ( !empty( $_GET['wpc_hub_preview_key'] ) && WPC()->is_hub_preview() ) {
                    $portalhub = get_post( $_GET['wpc_hub_preview_id'] );
                }

                if ( ! empty( $portalhub ) && ( !current_user_can( 'administrator' ) || WPC()->is_hub_preview() ) ) {
                    $query_request = array(
                        'portalhub' => $portalhub->post_name,
                        'post_type' => 'portalhub',
                        'ID'        => $portalhub->ID,
                        'name'      => $portalhub->post_name,
                        //'pagename'  => $portalhub_post->rewrite['slug'] . '/' . $portalhub->post_name
                        'pagename'  => $portalhub->post_name
                    );

                } else {
                    WPC()->redirect( home_url() );
                }
            } else {
                WPC()->redirect( WPC()->get_login_url() );
            }
        } elseif ( isset( $query_request['post_type'] ) && 'portalhub' == $query_request['post_type'] ) {
            WPC()->redirect( home_url() );
        }

        return $query_request;
    }


    /**
     * Turn off redirect by WPML for compatibility
     * with WPC portal hubs
     *
     * @param $redirect
     * @param $post_id
     * @param $q
     * @return bool
     */
    function portalhub_redirect( $redirect, $post_id, $q ) {

        $post = get_post( $post_id );

        if ( $post && 'portalhub' == $post->post_type ) {
            return false;
        }

        return $redirect;
    }


    /**
     * @param WP_Query() $query
     */
    function exclude_portalhubs( $query ) {
        if ( ! $query->is_main_query() ) {
            global $wpdb;
            $all_portalhubs = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'portalhub' ORDER BY post_date DESC");
            $query->query_vars['post__not_in'] = $all_portalhubs;
        }
    }


    function add_no_cache_headers() {
        global $post;
        if( is_user_logged_in() && !headers_sent() ) {
            $wpc_pages = WPC()->get_settings( 'pages' );
            if( isset( $post->ID ) && array_search( $post->ID, $wpc_pages ) !== false ) {
                header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
                header('Pragma: no-cache'); // HTTP 1.0.
                header('Expires: 0'); // Proxies.
            }
        }
    }


    /**
     * filter for change portal page template (for some themes)
     */
    function change_portal_page_template( $meta_type, $object_id, $meta_key = '', $single = false ) {
        if ( '_wp_page_template' == $meta_key  ) {
            if ( isset( WPC()->current_plugin_page['post_id'] ) && $object_id == WPC()->current_plugin_page['post_id'] ) {
                return WPC()->current_plugin_page['template'];
            }
        }
        return $meta_type;
    }


    /*
    * add meta on plughin pages
    */
    function add_meta_to_plugin_pages() {
        echo '<meta name="robots" content="noindex"/>';
        echo '<meta name="robots" content="nofollow"/>';
        echo '<meta name="Cache-Control" content="no-cache"/>';
        echo '<meta name="Pragma" content="no-cache"/>';
        echo '<meta name="Expires" content="0"/>';
    }


    /*
    * Add Scheme style on page
    *
    * @param string $scheme_key
    *
    * @return void
    */
    function add_scheme_style( $scheme_key ) {

        //enqueue styles if necessary
        if ( ! empty( $scheme_key ) ) {
            $uploads = wp_upload_dir();
            $uploads['basedir'] = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] );
            if ( file_exists( $uploads['basedir'] . '/wpc_custom_style_' . $scheme_key . '.css' ) ) {
                wp_enqueue_style( 'wpc_custom_style_' . $scheme_key, $uploads['baseurl'] . '/wpc_custom_style_' . $scheme_key . '.css', array(), WPC_CLIENT_VER );
            }
        }
    }




}

endif;