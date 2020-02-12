<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$wpc_pages = WPC()->get_settings( 'pages' );

if ( empty( $wpc_pages['portal_page_id'] ) ) {
    $wpc_pages['portal_page_slug'] = 'portal/portal-page';
} else {
    $portal_page_post = get_post( $wpc_pages['portal_page_id'] );

    if ( empty( $portal_page_post ) ) {
        $wpc_pages['portal_page_slug'] = 'portal/portal-page';
    } else {
        $url_array = parse_url( get_permalink( $portal_page_post->ID ) );
        $site_url = get_site_url();

        $site_url = trim( str_replace( $url_array['scheme'], '', $site_url ), ':/' );
        $site_subdir = trim( str_replace( $url_array['host'], '', $site_url ), '/' );

        $wpc_pages['portal_page_slug'] = trim( str_replace( $site_subdir, '', $url_array['path'] ), '/' );
    }
}

WPC()->settings()->update( $wpc_pages, 'pages' );


//Change Portal Pages Page templates
$query = new WP_Query;
$post_ids_insert = $query->query( array(
    'post_type' => 'clientspage',
    'fields'      => 'ids',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key'   => '_wp_page_template',
            'compare' => 'NOT EXISTS'
        )
    )
) );

$post_ids_update = $query->query( array(
    'post_type' => 'clientspage',
    'fields'      => 'ids',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key'   => '_wp_page_template',
            'value' => '__use_same_as_portal_page'
        ),
        array(
            'key'   => '_wpc_use_page_settings',
            'value' => '1'
        )
    )
) );

$posts_ids = array_merge( $post_ids_update, $post_ids_insert );

if ( ! empty( $posts_ids ) ) {
    $pp_template = get_post_meta( $wpc_pages['portal_page_id'], '_wp_page_template', true );

    if ( ! empty( $post_ids_insert ) ) {
        $values = '';
        foreach ( $post_ids_insert as $id ) {
            $template = $pp_template;
            $wpc_use_page_settings = get_post_meta( $id, '_wpc_use_page_settings', true );
            if ( ! empty( $wpc_use_page_settings ) ) {
                $template_meta = get_post_meta( $id, '_wp_page_template', true );

                if ( $template_meta != '__use_same_as_portal_page' ) {
                    $template = $template_meta;
                }
            }

            $values .= "( {$id}, '_wp_page_template', '{$template}' ),";
        }
        $values = substr( $values, 0, -1 );

        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta}
            ( post_id, meta_key, meta_value )
            VALUES {$values}"
        );
    }

    if ( ! empty( $post_ids_update ) ) {
        $wpdb->query(
            "UPDATE {$wpdb->postmeta}
            SET meta_value = '{$pp_template}'
            WHERE meta_key = '_wp_page_template' AND 
                  post_id IN('" . implode( ',', $post_ids_update ) . "')"
        );
    }
}


//!!!Important: For remove kses sanitizes for post content
kses_remove_filters();

//HUBs
if ( empty( $wpc_pages['hub_page_id'] ) ) {
    $wpc_pages['portal_hub_slug'] = '';
    $portalhub_title_new = __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN );
} else {
    $hub_page_post = get_post( $wpc_pages['hub_page_id'] );

    if ( empty( $hub_page_post ) ) {
        $wpc_pages['portal_hub_slug'] = '';
        $portalhub_title_new = __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN );
    } else {
        $front_page_id = get_option( 'page_on_front' );
        if ( $front_page_id && $hub_page_post->ID == $front_page_id ) {
            $wpc_pages['portal_hub_slug'] = '';
        } else {
            $url_array = parse_url( get_permalink( $hub_page_post->ID ) );
            $site_url = get_site_url();

            $site_url = trim( str_replace( $url_array['scheme'], '', $site_url ), ':/' );
            $site_subdir = trim( str_replace( $url_array['host'], '', $site_url ), '/' );

            $wpc_pages['portal_hub_slug'] = trim( str_replace( $site_subdir, '', $url_array['path'] ), '/' );
        }

        $wpc_general = WPC()->get_settings( 'general' );
        if ( isset( $wpc_general['show_hub_title'] ) && $wpc_general['show_hub_title'] == 'yes' )
            $portalhub_title_new = '{client_business_name}';
        else
            $portalhub_title_new = ! empty( $hub_page_post->post_title ) ? $hub_page_post->post_title : __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN );
    }
}

WPC()->settings()->update( $wpc_pages, 'pages' );

if ( ! empty( $hub_page_post ) ) {

    $wp_hub_content = $hub_page_post->post_content;

    $default_template_id = false;
    $not_delete = false;

    $wp_page_template = get_post_meta( $hub_page_post->ID, '_wp_page_template', true );

    $hub_divi_builder = get_post_meta( $hub_page_post->ID, '_et_pb_use_builder', true );
    $hub_divi_builder = ! empty( $hub_divi_builder ) ? $hub_divi_builder : 'off';

    $hub_visual_composer = get_post_meta( $hub_page_post->ID, '_wpb_vc_js_status', true );
    $hub_visual_composer = ! empty( $hub_visual_composer ) ? $hub_visual_composer : false;

    $wpc_clients = get_users( array(
        'blog_id'   => get_current_blog_id(),
        'role'      => 'wpc_client',
        'fields'    => 'ids',
    ) );

    $wpc_ez_hub_templates = WPC()->get_settings( 'ez_hub_templates' );

    //for transfer not used HUB templates
    $transferred_templates = array();

    $hubs_hashes = array();
    $all_assigns = array();
    $portalhub_titles = array();

    $template_index = 0;
    foreach ( $wpc_clients as $client_id ) {
        $hub_page_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );
        $hub_page = get_post( $hub_page_id );

        if ( ! empty( $hub_page ) ) {
            $content = $hub_page->post_content;

            $wpc_use_page_settings = get_post_meta( $hub_page->ID, '_wpc_use_page_settings', true );
            if ( ! empty( $wpc_use_page_settings ) ) {
                $template_meta = get_post_meta( $hub_page->ID, '_wp_page_template', true );

                if ( $template_meta != '__use_same_as_hub_page' ) {
                    $wp_page_template = $template_meta;
                }
            }

            $divi_builder = get_post_meta( $hub_page->ID, '_et_pb_use_builder', true );
            $divi_builder = ! empty( $divi_builder ) ? $divi_builder : 'off';

            $visual_composer = get_post_meta( $hub_page->ID, '_wpb_vc_js_status', true );
            $visual_composer = ! empty( $visual_composer ) ? $visual_composer : false;

            $template = '';
            $style_scheme = '';
            $is_default = false;
            $priority = 0;
            $client_assigns = array( $client_id );
            $circle_assigns = array();
            $portalhub_admin_label['real_hub_title'] = $hub_page->post_title;

            if ( strpos( $content, '[wpc_client_hub_page_template]' ) !== false || strpos( $content, '[wpc_client_hub_page_template /]' ) !== false || strpos( $content, '[wpc_client_hub_page_template/]' ) !== false ) {

                foreach ( $wpc_ez_hub_templates as $key => $tpl ) {
                    if ( isset( $tpl['not_delete'] ) || ( isset( $tpl['is_default'] ) && 1 == $tpl['is_default'] ) )
                        $template = $key;
                }

                $maybe_template = array();
                foreach ( $wpc_ez_hub_templates  as $key => $values ) {
                    //check individual assign
                    $user_ids = WPC()->assigns()->get_assign_data_by_object( 'ez_hub', $key, 'client' );
                    //get clients from Client Circles
                    $groups_ids = WPC()->assigns()->get_assign_data_by_object( 'ez_hub', $key, 'circle' );
                    foreach( $groups_ids as $group_id ) {
                        $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                    }

                    $user_ids = array_unique( $user_ids );

                    if ( in_array( $client_id, $user_ids ) )
                        $maybe_template[] = $key;
                }

                if ( count( $maybe_template ) ) {
                    $template = $maybe_template[0];
                    $current_priority = 0;
                    foreach ( $maybe_template as $val ) {
                        if ( ( !empty( $wpc_ez_hub_templates[ $val ]['priority'] ) && $current_priority >= $wpc_ez_hub_templates[ $val ]['priority'] ) ||
                            $current_priority == 0 ) {
                            $current_priority = !empty( $wpc_ez_hub_templates[ $val ]['priority'] ) ? $wpc_ez_hub_templates[ $val ]['priority'] : 0;
                            $template = $val;
                        }
                    }
                }

                $hub_content = $tabs_content =  '' ;

                if ( $template ) {
                    $handle = fopen( WPC()->get_upload_dir( 'wpclient/_hub_templates/' ) . $template . '_hub_content.txt', 'rb' );
                    if ( $handle !== false ) {
                        rewind( $handle ) ;
                        while ( !feof( $handle ) ) {
                            $hub_content .= fread( $handle, 8192 );
                        }
                    }
                    fclose( $handle );

                    $handle = fopen( WPC()->get_upload_dir( 'wpclient/_hub_templates/' ) . $template . '_hub_tabs_content.txt', 'rb' );
                    if ( $handle !== false ) {
                        rewind( $handle ) ;
                        while ( !feof( $handle ) ) {
                            $tabs_content .= fread( $handle, 8192 );
                        }
                    }
                    fclose( $handle );
                }

                if ( isset( $wpc_ez_hub_templates[$template]['type'] ) && 'ez' == $wpc_ez_hub_templates[$template]['type'] ) {
                    if ( strpos( $hub_content, '{ez_hub_bar}' ) === false ) {
                        $hub_page_template = $hub_content . $tabs_content;
                    } else {
                        $hub_page_template = str_replace( '{ez_hub_bar}', $tabs_content, $hub_content );
                    }
                } else {
                    $hub_page_template = $tabs_content;
                }

                $portalhub_admin_label['hub_template_title'] = isset( $wpc_ez_hub_templates[ $template ]['name'] ) ? $wpc_ez_hub_templates[ $template ]['name'] : '';

                $style_scheme = isset( $wpc_ez_hub_templates[ $template ]['general']['scheme'] ) ? $wpc_ez_hub_templates[ $template ]['general']['scheme'] : '';
                $priority = isset( $wpc_ez_hub_templates[ $template ]['priority'] ) ? $wpc_ez_hub_templates[ $template ]['priority'] : 0;

                $transferred_templates[] = $template;

                $content = str_replace( '[wpc_client_hub_page_template]', $hub_page_template, str_replace( '[wpc_client_hub_page_template /]', $hub_page_template, str_replace( '[wpc_client_hub_page_template/]', $hub_page_template, $content ) ) );
            }

            if ( strpos( $wp_hub_content, '[wpc_client_hub_page]' ) !== false || strpos( $wp_hub_content, '[wpc_client_hub_page /]' ) !== false || strpos( $wp_hub_content, '[wpc_client_hub_page/]' ) !== false ) {
                $content = str_replace( '[wpc_client_hub_page]', $content, str_replace( '[wpc_client_hub_page /]', $content, str_replace( '[wpc_client_hub_page/]', $content, $wp_hub_content ) ) );
            }

            $hash_content = md5( $content );

            if ( ! in_array( $hash_content, $hubs_hashes ) ) {
                $post_id = wp_insert_post( array(
                    'post_title'    => $portalhub_title_new,
                    'post_type'     => 'portalhub',
                    'post_content'  => $content,
                    'post_status'   => 'publish',
                ) );

                $all_assigns[ $post_id ] = array(
                    'client' => $client_assigns,
                    'circle' => $circle_assigns,
                );

                update_post_meta( $post_id, '_wpc_style_scheme', $style_scheme );
                update_post_meta( $post_id, 'wpc_template_priority', $priority );
                update_post_meta( $post_id, '_wp_page_template', $wp_page_template );
                $portalhub_titles[ $post_id ] = $portalhub_admin_label;

                //for DIVI theme
                update_post_meta( $post_id, '_et_pb_use_builder', $divi_builder );
                //for VisualComposer
                update_post_meta( $post_id, '_wpb_vc_js_status', $visual_composer );

                $hubs_hashes[ $post_id ] = $hash_content;
            } else {
                $post_id = array_search( $hash_content, $hubs_hashes );
                $all_assigns[ $post_id ]['client'] = array_merge( $all_assigns[ $post_id ]['client'], array( $client_id ) );
            }

            if ( ! $default_template_id )
                $default_template_id = ( isset( $wpc_ez_hub_templates[$template]['is_default'] ) && 1 == $wpc_ez_hub_templates[$template]['is_default'] ) ? $post_id : false;

            if ( ! $not_delete )
                $not_delete = isset( $wpc_ez_hub_templates[$template]['not_delete'] ) ? $post_id : false;
        }

        clean_post_cache( $hub_page_id );
        clean_user_cache( $client_id );
    }

    //another HUB Templates, which not assigned to clients or small order
    $all_templates = array_keys( $wpc_ez_hub_templates );
    $not_transferred_templates = array_diff( $all_templates, $transferred_templates );
    $wp_page_template = get_post_meta( $hub_page_post->ID, '_wp_page_template', true );
    foreach ( $wpc_ez_hub_templates as $key => $tpl ) {
        if ( in_array( $key, $not_transferred_templates ) ) {

            $hub_content = $tabs_content =  '' ;

            $handle = fopen( WPC()->get_upload_dir( 'wpclient/_hub_templates/' ) . $key . '_hub_content.txt', 'rb' );
            if ( $handle !== false ) {
                rewind( $handle ) ;
                while ( ! feof( $handle ) ) {
                    $hub_content .= fread( $handle, 8192 );
                }
            }
            fclose( $handle );

            $handle = fopen( WPC()->get_upload_dir( 'wpclient/_hub_templates/' ) . $key . '_hub_tabs_content.txt', 'rb' );
            if ( $handle !== false ) {
                rewind( $handle ) ;
                while ( ! feof( $handle ) ) {
                    $tabs_content .= fread( $handle, 8192 );
                }
            }
            fclose( $handle );


            if ( isset( $tpl['type'] ) && 'ez' == $tpl['type'] ) {
                if ( strpos( $hub_content, '{ez_hub_bar}' ) === false ) {
                    $content = $hub_content . $tabs_content;
                } else {
                    $content = str_replace( '{ez_hub_bar}', $tabs_content, $hub_content );
                }
            } else {
                $content = $tabs_content;
            }

            if ( strpos( $wp_hub_content, '[wpc_client_hub_page]' ) !== false || strpos( $wp_hub_content, '[wpc_client_hub_page /]' ) !== false || strpos( $wp_hub_content, '[wpc_client_hub_page/]' ) !== false ) {
                $content = str_replace( '[wpc_client_hub_page]', $content, str_replace( '[wpc_client_hub_page /]', $content, str_replace( '[wpc_client_hub_page/]', $content, $wp_hub_content ) ) );
            }

            $admin_label = isset( $tpl['name'] ) ? $tpl['name'] : '';
            if ( empty( $admin_label ) ) {
                $template_index++;
                $admin_label = sprintf( __( 'HUB Page %s', WPC_CLIENT_TEXT_DOMAIN ), $template_index );
            }
            $style_scheme = isset( $tpl['general']['scheme'] ) ? $tpl['general']['scheme'] : '';
            $priority = isset( $tpl['priority'] ) ? $tpl['priority'] : 0;

            $portalhub_admin_label['real_hub_title'] = $admin_label;
            $portalhub_admin_label['hub_template_title'] = $admin_label;

            $post_id = wp_insert_post( array(
                'post_title'    => $portalhub_title_new,
                'post_type'     => 'portalhub',
                'post_content'  => $content,
                'post_status'   => 'publish',
            ) );

            $all_assigns[ $post_id ] = array(
                'client' =>  WPC()->assigns()->get_assign_data_by_object( 'ez_hub', $key, 'client' ),
                'circle' =>  WPC()->assigns()->get_assign_data_by_object( 'ez_hub', $key, 'circle' ),
            );

            update_post_meta( $post_id, '_wpc_style_scheme', $style_scheme );
            update_post_meta( $post_id, 'wpc_template_priority', $priority );
            update_post_meta( $post_id, '_wp_page_template', $wp_page_template );
            $portalhub_titles[ $post_id ] = $portalhub_admin_label;

            //for DIVI theme
            update_post_meta( $post_id, '_et_pb_use_builder', $hub_divi_builder );

            //for VisualComposer
            update_post_meta( $post_id, '_wpb_vc_js_status', $hub_visual_composer );


            if ( ! $default_template_id && isset( $tpl['is_default'] ) && 1 == $tpl['is_default'] )
                $default_template_id = $post_id;

            if ( ! $not_delete && isset( $tpl['not_delete'] ) )
                $not_delete = $post_id;

        }
    }

    foreach ( $all_assigns as $p_id => $data ) {
        if ( ! empty( $data['client'] ) )
            WPC()->assigns()->set_assigned_data( 'portalhub', $p_id, 'client', $data['client'] );

        if ( ! empty( $data['circle'] ) )
            WPC()->assigns()->set_assigned_data( 'portalhub', $p_id, 'circle', $data['circle'] );
    }


    foreach ( $portalhub_titles as $p_id => $data ) {
        if ( count( $all_assigns[ $p_id ]['client'] ) == 1 && isset( $data['real_hub_title'] ) ) {
            update_post_meta( $p_id, 'wpc_admin_label', $data['real_hub_title'] );
        } else {
            update_post_meta( $p_id, 'wpc_admin_label', isset( $data['hub_template_title'] ) ? $data['hub_template_title'] : '' );
        }
    }

    //set default HUB template
    if ( $default_template_id ) {
        update_post_meta( $default_template_id, 'wpc_default_template', true );
    } elseif( ! $default_template_id && $not_delete ) {
        update_post_meta( $not_delete, 'wpc_default_template', true );
    }
}


//delete WP Pages, base for Portal Page and HUB page
//do this after all upgrade script, for users which set HUB and Portal the same page
if ( ! empty( $portal_page_post ) )
    wp_delete_post( $portal_page_post->ID, true );

if ( ! empty( $hub_page_post ) )
    wp_delete_post( $hub_page_post->ID, true );


WPC()->reset_rewrite_rules();