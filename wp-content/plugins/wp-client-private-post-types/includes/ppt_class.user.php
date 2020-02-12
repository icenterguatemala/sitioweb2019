<?php

if ( !class_exists( "WPC_PPT_User" ) ) {

    class WPC_PPT_User extends WPC_PPT_Common {

        /**
         * constructor
         **/
        function __construct() {

            $this->ppt_common_construct();

            //protect posts types
            add_filter( 'the_posts', array( &$this, 'filter_protected_posts' ), 99, 2 );

            //protect pages for wp_list_pages func
            add_filter( 'get_pages', array( &$this, 'filter_protected_posts' ), 99, 2 );


            //filter menu items
            add_filter( 'wp_nav_menu_objects', array( &$this, 'filter_menu' ), 99, 2 );
        }


        /**
         * Protect Post Types pages
         */
        function filter_protected_posts( $posts, $query ) {
            global $wp_query, $wpdb;

            $filtered_posts = array();


            //if empty
            if ( empty( $posts ) )
                return $posts;


            //get protected post types
            $private_post_types = get_option( 'wpc_private_post_types' );
            $private_post_types['types'] = ( isset( $private_post_types['types'] ) && is_array( $private_post_types['types'] ) ) ? $private_post_types['types'] : array();

            $wpc_pages = WPC()->get_settings('pages');

            //other filter
            foreach( $posts as $post ) {

                if ( array_key_exists( $post->post_type, $private_post_types['types'] ) &&
                    get_post_meta( $post->ID, '_wpc_protected', true ) ) {
                    //for protected posts
                    if ( is_user_logged_in() ) {

                        if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'administrator' ) )
                            $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
                        else
                            $user_id = get_current_user_id();

                        //block not appoved clients
                        if ( '1' == get_user_meta( $user_id, 'to_approve', true ) )
                            continue;

                        $user_ids = WPC()->assigns()->get_assign_data_by_object( 'private_post', $post->ID, 'client' );
                        $user_ids = ( is_array( $user_ids ) && 0 < count( $user_ids ) ) ? $user_ids : array();

                        //get clients from Client Circles
                        $groups_id      = WPC()->assigns()->get_assign_data_by_object( 'private_post', $post->ID, 'circle' );
                        if ( is_array( $groups_id ) && 0 < count( $groups_id ) ) {
                            foreach( $groups_id as $group_id ) {
                                $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                            }
                        }

                        if( 'post' == $post->post_type ) {
                            $post_cats = wp_get_post_categories( $post->ID );

                            if ( is_array( $post_cats ) && 0 < count( $post_cats ) ) {
                                foreach ( $post_cats as $post_cat ) {
                                    $user_ids = array_merge( $user_ids, WPC()->assigns()->get_assign_data_by_object( 'post_category', $post_cat, 'client' ) ) ;
                                    $cat_groups_ids = WPC()->assigns()->get_assign_data_by_object( 'post_category', $post_cat, 'circle' ) ;
                                    foreach( $cat_groups_ids as $cat_group_id ) {
                                        $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $cat_group_id ) );
                                    }
                                }
                            }
                        }

                        if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                            $user_ids = array_unique( $user_ids );

                        //added post in post list if have access
                        if ( ( !empty( $user_ids ) && in_array( $user_id, $user_ids ) ) || current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                            $filtered_posts[] = $post;
                            continue;
                        }

                        if( !isset( $private_post_types['action'] ) || ( 'redirect' == $private_post_types['action'] && 'the_posts' == current_filter() ) ) {
                            if ( isset( $wp_query->queried_object_id ) && isset( $wpc_pages['error_page_id'] ) && $wp_query->queried_object_id == $wpc_pages['error_page_id'] ) {
                                continue;
                            } else {
                                if ( headers_sent() ) {
                                    WPC()->redirect( WPC()->get_slug( 'error_page_id' ) );
                                } else {
                                    wp_redirect( WPC()->get_slug( 'error_page_id' ) );
                                }
                                exit;
                            }
                        } elseif( is_search() && 'leave_on_search' == $private_post_types['action'] && is_object( $query ) && $query->is_main_query() ) {
                            $filtered_posts[] = $post;
                            continue;
                        } elseif( is_single() && is_object( $query ) && $query->is_main_query() ) {
                            if ( headers_sent() ) {
                                WPC()->redirect( WPC()->get_slug( 'error_page_id' ) );
                            } else {
                                wp_redirect( WPC()->get_slug( 'error_page_id' ) );
                            }
                            exit;
                        }

                    } else {

                        if( isset( $private_post_types['action'] ) && 'redirect' == $private_post_types['action'] && 'the_posts' == current_filter() ) {
                            if ( isset( $wp_query->queried_object_id ) && isset( $wpc_pages['login_page_id'] ) && $wp_query->queried_object_id == $wpc_pages['login_page_id'] ) {
                                continue;
                            } else {
                                if ( $query->is_main_query() ) {
                                    if ( headers_sent() ) {
                                        WPC()->redirect( add_query_arg( array( 'wpc_to_redirect' => urlencode( get_permalink( $post ) ) ), WPC()->get_slug( 'login_page_id' ) ) );
                                    } else {
                                        wp_redirect( add_query_arg( array( 'wpc_to_redirect' => urlencode( get_permalink( $post ) ) ), WPC()->get_slug( 'login_page_id' ) ) );
                                    }
                                } else {
                                    if ( headers_sent() ) {
                                        WPC()->redirect( WPC()->get_slug( 'login_page_id' ) );
                                    } else {
                                        wp_redirect( WPC()->get_slug( 'login_page_id' ) );
                                    }
                                }
                                exit;
                            }

                        } elseif( is_search() && 'leave_on_search' == $private_post_types['action'] && is_object( $query ) && $query->is_main_query() ) {
                            $filtered_posts[] = $post;
                            continue;
                        } elseif( is_single() && is_object( $query ) && $query->is_main_query() ) {
                            if ( headers_sent() ) {
                                WPC()->redirect( add_query_arg( array( 'wpc_to_redirect' => urlencode( get_permalink( $post ) ) ), WPC()->get_slug( 'login_page_id' ) ) );
                            } else {
                                wp_redirect( add_query_arg( array( 'wpc_to_redirect' => urlencode( get_permalink( $post ) ) ), WPC()->get_slug( 'login_page_id' ) ) );
                            }
                            exit;
                        }

                    }
                } else {
                    //add all other posts
                    $filtered_posts[] = $post;
                }
            }

            return $filtered_posts;
        }

        /**
         * Protect Post Types pages
         */
        function filter_menu( $menu_items, $args ) {
            $filtered_items = array();

            //if empty
            if ( empty( $menu_items ) )
                return $menu_items;


            //get protected post types
            $private_post_types = get_option( 'wpc_private_post_types' );
            $private_post_types['types'] = ( isset( $private_post_types['types'] ) && is_array( $private_post_types['types'] ) ) ? $private_post_types['types'] : array();

            //other filter
            foreach ( $menu_items as $menu_item ) {

                if ( !empty( $menu_item->object_id ) && !empty( $menu_item->object ) ) {
                    if ( array_key_exists( $menu_item->object, $private_post_types['types'] ) &&
                        get_post_meta( $menu_item->object_id, '_wpc_protected', true )
                    ) {

                        //for protected posts
                        if ( is_user_logged_in() ) {

                            if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'administrator' ) )
                                $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
                            else
                                $user_id = get_current_user_id();

                            //block not appoved clients
                            if ( '1' == get_user_meta( $user_id, 'to_approve', true ) )
                                continue;

                            $user_ids = WPC()->assigns()->get_assign_data_by_object( 'private_post', $menu_item->object_id, 'client' );
                            $user_ids = ( is_array( $user_ids ) && 0 < count( $user_ids ) ) ? $user_ids : array();

                            //get clients from Client Circles
                            $groups_id = WPC()->assigns()->get_assign_data_by_object( 'private_post', $menu_item->object_id, 'circle' );
                            if ( is_array( $groups_id ) && 0 < count( $groups_id ) ) {
                                foreach ( $groups_id as $group_id ) {
                                    $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                                }
                            }

                            if ( 'post' == $menu_item->object ) {
                                $post_cats = wp_get_post_categories( $menu_item->object_id );

                                if ( is_array( $post_cats ) && 0 < count( $post_cats ) ) {
                                    foreach ( $post_cats as $post_cat ) {
                                        $user_ids = array_merge( $user_ids, WPC()->assigns()->get_assign_data_by_object( 'post_category', $post_cat, 'client' ) );
                                        $cat_groups_ids = WPC()->assigns()->get_assign_data_by_object( 'post_category', $post_cat, 'circle' );
                                        foreach ( $cat_groups_ids as $cat_group_id ) {
                                            $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $cat_group_id ) );
                                        }
                                    }
                                }
                            }

                            if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                                $user_ids = array_unique( $user_ids );

                            //added post in post list if have access
                            if ( ( !empty( $user_ids ) && in_array( $user_id, $user_ids ) ) || current_user_can( 'wpc_manager' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
                                $filtered_items[] = $menu_item;
                                continue;
                            }

                        }
                        //for not logged in or have no access
                        continue;
                    }
                }

                //add all other posts
                $filtered_items[] = $menu_item;

            }

            return $filtered_items;
        }


        /**
         * Shortcode for private post type
         **/
        function shortcode_private_post_types( $atts, $contents = null ) {
            global $wpdb;



            $no_redirect = false;
            if ( isset( $atts['no_redirect'] ) && 'true' == $atts['no_redirect'] ) {
                $no_redirect = true;
            }


            if( ! $no_redirect ) {
                //checking access
                $user_id = WPC()->checking_page_access();
            } else {
                if( ! is_user_logged_in() ) {
                    $data = array();

                    $data['hide_content'] = true;
                    $data['no_redirect'] = true;
                    $data['no_redirect_text'] = ( isset( $atts['no_redirect_text'] ) && !empty( $atts['no_redirect_text'] ) ) ? $atts['no_redirect_text'] : __( 'Please login for see this content', WPC_CLIENT_TEXT_DOMAIN );
                    return WPC()->get_template( 'list.php', 'private_post_types', $data);
                }
            }


            $post_contents  = '';
            $data = array();
            $data['hide_content'] = false;

            //show date
            $data['show_date'] = true;
            if ( isset( $atts['show_date'] ) && 'no' == strtolower( $atts['show_date'] ) ) {
                $data['show_date'] = false;
            }

            if ( isset( $atts['private_post_types'] ) && '' != $atts['private_post_types'] ) {
                //show current portal page
                $post_type_filter = "$wpdb->posts.post_type = '". $atts['private_post_types'] . "' AND";
            } else {
                $post_type_filter = "$wpdb->posts.post_type != 'clientspage' AND $wpdb->posts.post_type != 'hubpage' AND ";
            }

            if ( !empty( $atts['term_ids'] ) ) {
                $term_ids_filter = " $wpdb->term_relationships.term_taxonomy_id IN ( '". implode( "','", explode(',', $atts['term_ids'] ) ) . "' ) AND ";
                $left_join_for_term_ids_filter = "LEFT JOIN $wpdb->term_relationships ON $wpdb->term_relationships.object_id = $wpdb->posts.ID";
            } else {
                $term_ids_filter = $left_join_for_term_ids_filter = '';
            }

            $post_ids = WPC()->assigns()->get_assign_data_by_assign( 'private_post', 'client', $user_id );

            //get clientpages by user_ids
            $mypages_id = $wpdb->get_col(
                "SELECT $wpdb->posts.ID
                FROM $wpdb->posts
                $left_join_for_term_ids_filter
                WHERE $post_type_filter $term_ids_filter
                    $wpdb->posts.post_status = 'publish' AND
                    $wpdb->posts.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wpc_protected' AND meta_value = 1 ) AND
                    $wpdb->posts.ID IN('" . implode( "','", $post_ids ) . "')
                GROUP BY $wpdb->posts.ID "
            );

            //get clientpages by groups_id
            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

            if( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach ( $client_groups_id as $groups_id )  {
                    $post_ids = WPC()->assigns()->get_assign_data_by_assign( 'private_post', 'circle', $groups_id );

                    $mypages_id2 = $wpdb->get_col(
                        "SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID
                        $left_join_for_term_ids_filter
                        WHERE $post_type_filter $term_ids_filter
                            $wpdb->posts.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wpc_protected' AND meta_value = 1 ) AND
                            $wpdb->posts.post_status = 'publish' AND
                            $wpdb->posts.ID IN('" . implode( "','", $post_ids ) . "')
                        GROUP BY $wpdb->posts.ID "
                    );

                    $mypages_id = array_merge( $mypages_id, $mypages_id2 );
                }
            }

            $mypages_id = array_unique( $mypages_id );

            //sorting
            if ( ! isset( $atts['sort_type'] ) || 'date' == strtolower( $atts['sort_type'] ) ) {
                //by date
                if ( isset( $atts['sort'] ) && 'desc' == strtolower( $atts['sort'] ) )
                    rsort( $mypages_id );
                else
                    sort( $mypages_id );
            } elseif ( 'title' == strtolower( $atts['sort_type'] ) ) {
                //by alphabetical
                if ( is_array( $mypages_id ) && $mypages_id ) {
                    foreach( $mypages_id as $page_id ) {
                        $mypage = get_post( $page_id, 'ARRAY_A' );
                        $for_sort[nl2br( $mypage['post_title'] )] = $page_id;
                    }

                    if ( isset( $atts['sort'] ) && 'desc' == strtolower( $atts['sort'] ) )
                        krsort( $for_sort );
                    else
                        ksort( $for_sort );

                    $mypages_id = array_values( $for_sort );
                }
            }


            foreach ( $mypages_id as $page_id ) {
                $mypage = get_post( $page_id, 'ARRAY_A' );

                $page = array();

                $page['url']            = get_permalink( $page_id );
                $page['title']          = nl2br( $mypage['post_title'] );

                $page['creation_date']  = strtotime( $mypage['post_date'] );
                $page['date']           = WPC()->date_format( strtotime( $mypage['post_date'] ), 'date' );
                $page['time']           = WPC()->date_format( strtotime( $mypage['post_date'] ), 'time' );

                $data['pages'][]        = $page;
            }

            return WPC()->get_template( 'list.php', 'private_post_types', $data );
        }
        //end class
    }

}