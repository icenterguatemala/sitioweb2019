<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Categories' ) ) :

final class WPC_Categories {

    /**
     * The single instance of the class.
     *
     * @var WPC_Categories
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Categories is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Categories - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

    }


    function create_category( $args ) {
        global $wpdb;

        if( !( isset( $args['type'] ) && !empty( $args['type'] ) ) ) {
            WPC()->redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'invalid' ), 'admin.php' ) );
        }

        //if new or edit category name is empty
        if( '' == $args['name'] ) {
            WPC()->redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'null' ), 'admin.php' ) );
        }

        //checking that new category not exist with other ID
        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT id
                FROM {$wpdb->prefix}wpc_client_categories
                WHERE LOWER(name) = '%s' AND
                    type='%s'",
            strtolower( $args['name'] ),
            $args['type']
        ), ARRAY_A );


        //if new category exist with other ID
        if( isset( $result ) && !empty( $result ) && !( "0" != $args['id'] && $result['id'] == $args['id'] ) ) {
            WPC()->redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'ce' ), 'admin.php' ) );
        }


        if ( '0' != $args['id'] ) {

            $old_path = '';
            if( isset( $args['type'] ) && $args['type'] == 'file' ) {
                $old_path = WPC()->files()->get_category_path( $args['id'] );
            }

            //update when edit category
            $wpdb->update(
                "{$wpdb->prefix}wpc_client_categories",
                array( 'name' => trim( $args['name'] ) ),
                array( 'id' => $args['id'] )
            );

            if( isset( $args['type'] ) && $args['type'] == 'file' ) {
                $new_path = WPC()->files()->get_category_path( $args['id'] );

                //rename folder on FTP
                if( is_dir( $old_path ) ) {
                    rename( $old_path, $new_path );
                }
            }

            WPC()->redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'u' ), 'admin.php' ) );

        } else {
            //create new category

            //get order number for new category
            $cat_order = $wpdb->get_var( $wpdb->prepare(
                "SELECT
                    COUNT(id)
                    FROM {$wpdb->prefix}wpc_client_categories
                    WHERE parent_id=%d AND
                        type='%s'",
                $args['parent_id'],
                $args['type']
            ) );
            $cat_order++;

            //insert when add new category
            $wpdb->insert(
                "{$wpdb->prefix}wpc_client_categories",
                array(
                    'name'      => trim( $args['name'] ),
                    'type'      => $args['type'],
                    'parent_id' => $args['parent_id'],
                    'cat_order' => $cat_order
                ),
                array( '%s', '%s', '%d', '%d' )
            );

            $category_id = $wpdb->insert_id;

            if( isset( $args['type'] ) && $args['type'] == 'file' ) {
                //create category folder
                WPC()->files()->create_file_category_folder( $category_id, trim( $args['folder_name'] ) );
            }

            //assigned process
            if( isset( $category_id ) && !empty( $category_id ) ) {
                //set clients
                $clients_array = array();
                if ( isset( $args['cat_clients'] ) && !empty( $args['cat_clients'] ) )  {
                    if( $args['cat_clients'] == 'all' ) {
                        $clients_array = WPC()->members()->get_client_ids();
                    } else {
                        $clients_array = explode( ',', $args['cat_clients'] );
                    }
                }

                WPC()->assigns()->set_assigned_data( $args['type'] . '_category', $category_id, 'client', $clients_array );

                //set Client Circle
                $circles_array = array();
                if ( isset( $args['cat_circles'] ) && !empty( $args['cat_circles'] ) )  {
                    if( $args['cat_circles'] == 'all' ) {
                        $circles_array = WPC()->groups()->get_group_ids();
                    } else {
                        $circles_array = explode( ',', $args['cat_circles'] );
                    }
                }
                WPC()->assigns()->set_assigned_data( $args['type'] . '_category', $category_id, 'circle', $circles_array );
            }

        }

    }


    /**
     *  Function for reassign objects:
     *       1) Files;
     *       2) Portal_pages,
     *       3) Galleries (only in Shutter extension)
     *  in wpc_client categories with types:
     *       1) File;
     *       2) Portal_page,
     *       3) Shutter (only in Shutter extension)
     */
    function reassign_object_from_category( $type, $old_id, $new_id ) {

        if( isset( $type ) && 'shutter' == $type ) {

            $args = array(
                'post_type' => 'wps-gallery',
                'meta_query' => array(
                    array(
                        'key' => '_wpc_category_id',
                        'value' => $old_id
                    )
                )
            );

            $postslist = get_posts( $args );

            foreach( $postslist as $post ) {
                update_post_meta( $post->ID, '_wpc_category_id', $new_id );
            }

        } elseif( isset( $type ) && 'shutter_size' == $type ) {

            $args = array(
                'post_type' => 'wpc-sht-print-size',
                'meta_query' => array(
                    array(
                        'key' => 'wpc_sht_size_category',
                        'value' => $old_id
                    )
                )
            );

            $postslist = get_posts( $args );

            foreach( $postslist as $post ) {
                update_post_meta( $post->ID, 'wpc_sht_size_category', $new_id );
            }

        } elseif( isset( $type ) && 'portal_page' == $type ) {

            $args = array(
                'post_type' => 'clientspage',
                'meta_query' => array(
                    array(
                        'key' => '_wpc_category_id',
                        'value' => $old_id
                    )

                )
            );

            $postslist = get_posts( $args );

            foreach( $postslist as $post ) {
                update_post_meta( $post->ID, '_wpc_category_id', $new_id );
            }

        } elseif( isset( $type ) && 'file' == $type ) {

        }
    }


    /**
     *  Function for delete wpc_client categories with all types:
     */
    function delete_category( $id, $type ) {
        global $wpdb;

        //delete category from database
        $wpdb->delete(
            "{$wpdb->prefix}wpc_client_categories",
            array(
                'id'    =>  $id
            )
        );

        //delete all assigns for category
        WPC()->assigns()->delete_all_object_assigns( $type . '_category', $id );
    }


    /**
     *  Function for getting wpc_client categories:
     *  1) File;
     *  2) Portal_page,
     *  3) Shutter (only in Shutter extension),
     *  4) shutter_size (only in Shutter extension),
     *  5) ticket_cats (only in Support extension),
     *  6) ticket_types (only in Support extension),
     *
     * ============= default struct ==============
     * $args = array(
     *       'type'
     *       'order_by'
     *       'order'
     *       'limit'
     *       'search'
     * );
     * ===========================================
     */
    function get_categories( $args ) {
        global $wpdb;

        $categories = array();

        $types = array(
            'file',
            'portal_page',
            'shutter',
            'shutter_size',
            'ticket_cats',
            'ticket_types',
        );

        if( isset( $args['type'] ) && in_array( $args['type'], $types ) ) {

            $args['order_by'] = ( isset( $args['order_by'] ) && !empty( $args['order_by'] ) ) ? $args['order_by'] : 'id';
            $args['order'] = ( isset( $args['order'] ) && !empty( $args['order'] ) ) ? $args['order'] : 'ASC';
            $args['search'] = ( isset( $args['search'] ) && !empty( $args['search'] ) ) ? $args['search'] : '';
            $args['limit'] = ( isset( $args['limit'] ) && !empty( $args['limit'] ) ) ? $args['limit'] : '';

            $categories = $wpdb->get_results(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_categories
                    WHERE type='{$args['type']}' {$args['search']}
                    ORDER BY " . $args['order_by'] . ' ' . $args['order'] .
                ' ' . $args['limit'],
                ARRAY_A );
        }

        return $categories;
    }


    /**
     *  Function for create wpc_client categories:
     *  1) File;
     *  2) Portal_page,
     *  3) Shutter (only in Shutter extension)*
     *  4) shutter_size (only in Shutter extension),
     *  5) ticket_cats (only in Support extension),
     *  6) ticket_types (only in Support extension),
     *
     * ============= default struct ==============
     * $args = array(
     *       'id'
     *       'name'
     *       'parent_id'
     *       'type'
     *       'cat_clients'
     *       'cat_circles'
     *       'page'
     *       'tab'
     * );
     * ===========================================
     */
    function update_category( $args ) {
        global $wpdb;

        $args['item_label']     = ( isset( $args['item_label'] ) ) ? $args['parent_id'] : __( 'Category', WPC_CLIENT_TEXT_DOMAIN );
        $args['parent_id']      = ( isset( $args['parent_id'] ) ) ? $args['parent_id'] : 0;
        $args['id']             = ( isset( $args['id'] ) ) ? $args['id'] : 0;

        if ( !( isset( $args['type'] ) && !empty( $args['type'] ) ) ) {
            return new WP_Error( 'error', __( 'Invalid type of Category', WPC_CLIENT_TEXT_DOMAIN ) );
        }

        //if new or edit category name is empty
        if ( '' == $args['name'] ) {
            return new WP_Error( 'error', sprintf ( __( '%s Name is Required', WPC_CLIENT_TEXT_DOMAIN ), $args['item_label'] ) );
        }

        //checking that new category not exist with other ID
        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT id
                FROM {$wpdb->prefix}wpc_client_categories
                WHERE LOWER(name) = '%s' AND
                    type='%s'",
            strtolower( $args['name'] ),
            $args['type']
        ), ARRAY_A );


        //if new category exist with other ID
        if ( isset( $result ) && !empty( $result ) && !( "0" != $args['id'] && $result['id'] == $args['id'] ) ) {
            return new WP_Error( 'error', sprintf ( __( ' The %s with this name already exists', WPC_CLIENT_TEXT_DOMAIN ), $args['item_label'] ) );
        }


        if ( 0 < $args['id'] ) {

            //update when edit category
            $wpdb->update(
                "{$wpdb->prefix}wpc_client_categories",
                array( 'name' => trim( $args['name'] ) ),
                array( 'id' => $args['id'] )
            );

            return true;

        } else {
            //create new category

            //get order number for new category
            $cat_order = $wpdb->get_var( $wpdb->prepare(
                "SELECT
                    COUNT(id)
                    FROM {$wpdb->prefix}wpc_client_categories
                    WHERE parent_id=%d AND
                        type='%s'",
                $args['parent_id'],
                $args['type']
            ) );
            $cat_order++;

            //insert when add new category
            $wpdb->insert(
                "{$wpdb->prefix}wpc_client_categories",
                array(
                    'name'      => trim( $args['name'] ),
                    'type'      => $args['type'],
                    'parent_id' => $args['parent_id'],
                    'cat_order' => $cat_order
                ),
                array( '%s', '%s', '%d', '%d' )
            );

            $category_id = $wpdb->insert_id;

            //assigned process
            if( isset( $category_id ) && !empty( $category_id ) ) {

                if ( !empty( $args['cat_clients'] ) ) {
                    //set clients
                    if( $args['cat_clients'] == 'all' ) {
                        $clients_array = WPC()->members()->get_client_ids();
                    } else {
                        $clients_array = explode( ',', $args['cat_clients'] );
                    }

                    WPC()->assigns()->set_assigned_data( $args['type'] . '_category', $category_id, 'client', $clients_array );
                }


                if ( !empty( $args['cat_circles'] ) ) {
                    //set Client Circle
                    if( $args['cat_circles'] == 'all' ) {
                        $circles_array = WPC()->groups()->get_group_ids();
                    } else {
                        $circles_array = explode( ',', $args['cat_circles'] );
                    }

                    WPC()->assigns()->set_assigned_data( $args['type'] . '_category', $category_id, 'circle', $circles_array );
                }
            }
        }

        return '';
    }



    /**
     *  Function for getting wpc_client categories:
     *  1) File;
     *  2) Portal_page,
     *  3) Shutter (only in Shutter extension),
     *  4) shutter_size (only in Shutter extension),
     *  5) ticket_cats (only in Support extension),
     *  6) ticket_types (only in Support extension),
     *
     * ============= default struct ==============
     * $args = array(
     *       'type'
     *       'order_by'
     *       'order'
     *       'limit'
     *       'search'
     * );
     * ===========================================
     */
    function get_category( $id, $type ) {
        global $wpdb;

        $types = array(
            'file',
            'portal_page',
            'shutter',
            'shutter_size',
            'ticket_cats',
            'ticket_types',
        );

        if ( !empty( $type ) && in_array( $type, $types ) ) {

            $category = $wpdb->get_row(
                "SELECT *
                    FROM {$wpdb->prefix}wpc_client_categories
                    WHERE type='{$type}' AND id = '{$id}' ",
                ARRAY_A );

            return $category;
        }

        return false;
    }



    /**
     * Function to get data portal page category
     *
     * @global object $wpdb
     * @param int $id
     * @return array
     */
    function get_pp_category( $id ) {
        global $wpdb;

        $data = $wpdb->get_row( $wpdb->prepare(
            "SELECT cat_id as id, cat_name as name FROM {$wpdb->prefix}wpc_client_portal_page_categories
                WHERE `cat_id` = %d", $id )
            , ARRAY_A );

        return $data;

    }



    /**
     * Function to get categories for portal pages
     *
     */
    function get_clientspage_categories( $order_by = '', $order = '' ) {
        global $wpdb;

        $order_by = !empty( $order_by ) ? $order_by : 'cat_name';
        $order = !empty( $order ) ? $order : 'ASC';

        $categories = $wpdb->get_results(
            "SELECT cat_id as id, cat_name as name
                FROM {$wpdb->prefix}wpc_client_portal_page_categories
                ORDER BY {$order_by} {$order}",
            ARRAY_A );

        return $categories;

    }




}

endif;