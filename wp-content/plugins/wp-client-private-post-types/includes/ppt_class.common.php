<?php


if ( !class_exists( "WPC_PPT_Common" ) ) {

    class WPC_PPT_Common {

        var $extension_dir;
        var $extension_url;
        /**
        * constructor
        **/
        function ppt_common_construct() {

            $this->extension_dir = WPC()->extensions()->get_dir( 'ppt' );
            $this->extension_url = WPC()->extensions()->get_url( 'ppt' );


            add_filter( 'wpc_shortcode_data_array', array( &$this, 'shortcode_data_array' ) );

            add_filter( 'wpc_permission_reports_content_entity', array( &$this, 'permission_reports_content' ) );
            add_action( 'wpc_get_filter_for_permissions_private_post', array( &$this, 'get_filter_for_permissions' ) );
            add_filter( 'wpc_get_permission_reports_private_post', array( &$this, 'get_permission_reports' ), 10, 2 );

            add_filter( 'wpc_extend_php_templates', array( $this, 'add_template_dir' ) );

        }


        function add_template_dir( $templates ) {

            $templates['private_post_types'] = $this->extension_dir . 'templates';

            return $templates;
        }


        function permission_reports_content( $entity ) {
            global $wpdb;

            $private_pages = array();
            $types = array();
            $private_post_types = get_option( 'wpc_private_post_types' );
            if( isset( $private_post_types['types'] ) && count( $private_post_types['types'] ) ) {
                foreach( $private_post_types['types'] as $key=>$val ) {
                    if( $val == '1' ) {
                        $types[] = $key;
                    }
                }
                if( count( $types ) ) {
                    $private_pages = $wpdb->get_results(
                        "SELECT p.ID AS id,
                                p.post_title AS title
                        FROM {$wpdb->posts} p
                        LEFT JOIN {$wpdb->postmeta} pm1
                        ON p.ID = pm1.post_id
                        WHERE
                            p.post_status != 'trash' AND
                            pm1.meta_key = '_wpc_protected' AND
                            pm1.meta_value = '1' AND
                            p.post_type IN('" . implode( "','", $types ) . "')
                        ORDER BY title ASC",
                        ARRAY_A );
                }

            }

            if ( 0 < count( $private_pages ) ) {
                $entity['private_post'] = __('Private Post Types', WPC_CLIENT_TEXT_DOMAIN );
            }
            return $entity;
        }


        function get_filter_for_permissions() {
            global $wpdb;

            $private_pages = array();
            $types = array();
            $private_post_types = get_option( 'wpc_private_post_types' );
            if( isset( $private_post_types['types'] ) && count( $private_post_types['types'] ) ) {
                foreach( $private_post_types['types'] as $key=>$val ) {
                    if( $val == '1' ) {
                        $types[] = $key;
                    }
                }
                if( count( $types ) ) {
                    $private_pages = $wpdb->get_results(
                        "SELECT p.ID AS id,
                                p.post_title AS title
                        FROM {$wpdb->posts} p
                        LEFT JOIN {$wpdb->postmeta} pm1
                        ON p.ID = pm1.post_id
                        WHERE
                            p.post_status != 'trash' AND
                            pm1.meta_key = '_wpc_protected' AND
                            pm1.meta_value = '1' AND
                            p.post_type IN('" . implode( "','", $types ) . "')
                        ORDER BY title ASC",
                    ARRAY_A );
                }

            }

            echo '<option value="all">' . __( 'Select Private Post Types', WPC_CLIENT_TEXT_DOMAIN ) . '</option>';
            if ( 0 < count( $private_pages ) ) {
                foreach( $private_pages as $private_page ) {
                    echo '<option value="' . $private_page['id'] . '">' . $private_page['title'] . '</option>';
                }
            }
        }


        function get_permission_reports( $temp, $items ) {
            global $wpdb;

            $temp = $wpdb->get_results(
                "SELECT p.ID as id,
                        p.post_title as name
                FROM {$wpdb->posts} p
                WHERE p.ID IN ('" . implode( "','", $items ) . "')",
            ARRAY_A );

            return $temp;
        }


        function shortcode_data_array( $array ) {
            $all_post_types = get_post_types();
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            $exclude_types      = $this->get_excluded_post_types();
            $post_types = array(
                'null' => __( 'All', WPC_CLIENT_TEXT_DOMAIN )
            );
            if ( isset( $wpc_private_post_types['types'] ) && is_array( $wpc_private_post_types['types'] ) ) {
                foreach( $wpc_private_post_types['types'] as $key => $value ) {
                    if ( in_array( $key, $exclude_types ) || $value != '1' )
                        continue;

                    if( !empty( $all_post_types[ $key ] ) ) {
                        $post_types[ $key ] = $all_post_types[ $key ];
                    }
                }
            }
            $array['wpc_client_private_post_types'] = array(
                'title'         => __( 'Private Post Types: Page List', WPC_CLIENT_TEXT_DOMAIN ),
                'name'         => 'private_post_types',
                'callback'      => array( &$this, 'shortcode_private_post_types' ),
                'categories'    => 'content',
                'hub_template' => array(
                    'text'    => __( 'Private Post Type List', WPC_CLIENT_TEXT_DOMAIN ),
                ),
                'attributes'    => array(
                    'private_post_types' => array(
                        'label'  => __( 'Post Type', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => $post_types,
                        'value'  => ''
                    ),
                    'sort_type' => array(
                        'label'  => __( 'Sort By', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'date' => __( 'Date', WPC_CLIENT_TEXT_DOMAIN ),
                            'title' => __( 'Title', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'date'
                    ),
                    'sort' => array(
                        'label'  => __( 'Sort', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'asc' => __( 'ASC', WPC_CLIENT_TEXT_DOMAIN ),
                            'desc' => __( 'DESC', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'asc'
                    ),
                    'no_redirect'       => array(
                        'label'  => __( 'Without Redirect', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'   => 'selectbox',
                        'values' => array(
                            'true' => __( 'Yes', WPC_CLIENT_TEXT_DOMAIN ),
                            'false' => __( 'No', WPC_CLIENT_TEXT_DOMAIN )
                        ),
                        'value'  => 'false'
                    ),
                    'no_redirect_text'  => array(
                        'label' => __( 'Text for not logged in users', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'  => 'text',
                        'value' => ''
                    )
                )
            );
            return $array;
        }


        function get_excluded_post_types() {
            $excluded_post_types = array( 'attachment', 'revision', 'nav_menu_item', 'clientspage', 'hubpage' );
            $excluded_post_types = apply_filters( 'wpc_added_excluded_post_types', $excluded_post_types );
            return $excluded_post_types;
        }


    //end class
    }
}

