<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Files' ) ) :

final class WPC_Files {

    /**
     * The single instance of the class.
     *
     * @var WPC_Files
     * @since 4.5
     */
    protected static $_instance = null;

    var $categories = array();
    var $categories_html = array();

    var $cache = array();

    var $files_for_google_doc_view = array(
        "ai"         =>    "application/postscript",
        "doc"        =>    "application/msword",
        "docx"       =>    "application/vnd.openxmlformats-officedocument.wordprocessingml",
        "dxf"        =>    "application/dxf",
        "eps"        =>    "application/postscript",
        "otf"        =>    "font/opentype",
        "pages"      =>    "application/x-iwork-pages-sffpages",
        "pdf"        =>    "application/pdf",
        "pps"        =>    "application/vnd.ms-powerpoint",
        "ppt"        =>    "application/vnd.ms-powerpoint",
        "pptx"       =>    "application/vnd.openxmlformats-officedocument.presentationml",
        "ps"         =>    "application/postscript",
        "psd"        =>    "image/photoshop",
        "rar"        =>    "application/rar",
        "svg"        =>    "image/svg+xml",
        "tif"        =>    "image/tiff",
        "tiff"       =>    "image/tiff",
        "ttf"        =>    "application/x-font-ttf",
        "xls"        =>    "application/vnd.ms-excel",
        "xlsx"       =>    "application/vnd.openxmlformats-officedocument.spreadsheetml",
        "xps"        =>    "application/vnd.ms-xpsdocument",
        "zip"        =>    "application/zip"
    );

    var $files_for_regular_view = array(
        'bmp', 'css', 'gif', 'html', 'jpg', 'jpeg', 'pdf', 'png', 'txt', 'xml'
    );

    var $file_video_formats = array(
        'mp4', 'm4v', 'webm', 'ogv', 'wmv', 'flv'
    );

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Files is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Files - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_filter( 'shortcode_atts_wpc_client_files_list', array( &$this, 'shortcode_atts' ), 10, 4 );
        add_filter( 'shortcode_atts_wpc_client_files_blog', array( &$this, 'shortcode_atts' ), 10, 4 );
        add_filter( 'shortcode_atts_wpc_client_files_table', array( &$this, 'shortcode_atts' ), 10, 4 );
        add_filter( 'shortcode_atts_wpc_client_files_tree', array( &$this, 'shortcode_atts' ), 10, 4 );
    }


    public function shortcode_atts( $out, $pairs, $atts, $shortcode ) {
        WPC()->set_shortcode_data();

        if ( empty( WPC()->shortcode_data[ $shortcode ]['attributes'] ) )
            return $out;

        $out = $atts;
        foreach( WPC()->shortcode_data[ $shortcode ]['attributes'] as $key=>$attr_data ) {
            if( !isset( $out[ $key ] ) ) {
                $out[ $key ] = isset( $attr_data['value'] ) ? $attr_data['value'] : '';
            }

            if( isset( $attr_data['values'] ) && is_array( $attr_data['values'] ) ) {
                $values_array = array_keys( $attr_data['values'] );
                if( count( array_intersect( $values_array, array( 'yes', 'no' ) ) ) == count( $values_array ) ) {
                    $out[ $key ] = $out[ $key ] !== 'no';
                }
            }
        }

        return $out;
    }


    public function get_file_ids( $atts ) {
        global $wpdb;

        $where = '';
        $categories_ids = array();
        $file_ids = array();

        $categories_list = ! empty( $atts['category'] ) ? array_map( 'trim', explode( ',', $atts['category'] ) ) : array();
        if ( 0 < count( $categories_list ) && ! in_array( 'all', $categories_list ) ) {

            //check categories by ID
            $categories_ids = $wpdb->get_col(
                "SELECT fc.cat_id
                FROM {$wpdb->prefix}wpc_client_file_categories fc
                WHERE fc.cat_id IN( '" . implode( "','", $categories_list ) . "' )"
            );

            //if empty categories by ID, check categories by name
            if ( empty( $categories_ids ) ) {
                $res = $wpdb->get_col(
                    "SELECT fc.cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories fc
                    WHERE fc.cat_name IN( '" . implode( "','", $categories_list ) . "' )"
                );

                if ( ! empty( $res ) )
                    $categories_ids = $res;
            }

            if ( count( $categories_ids ) ) {

                if ( $atts['with_subcategories'] ) {
                    //if "with_subcategories" attribute is active select subcategories
                    $parent_categories = $categories_ids;

                    foreach ( $categories_ids as $category_id ) {
                        $children_categories = $this->get_category_children_ids( $category_id );
                        $parent_categories = array_merge( $parent_categories, $children_categories );
                    }

                    $categories_ids = $parent_categories;
                }

                //get file IDs from selected categories
                $file_ids = $wpdb->get_col(
                    "SELECT id
                    FROM {$wpdb->prefix}wpc_client_files
                    WHERE cat_id IN('" . implode( "','", $categories_ids ) . "')"
                );
            } else {
                //wrong format of "category" attribute
                $categories_ids = array( '-1' );
            }

            //there are not files at selected categories
            if ( ! count( $file_ids ) )
                return array();
        }

        if ( ! empty( $atts['search'] ) ) {
            $where .= WPC()->admin()->get_prepared_search( $atts['search'], array(
                'f.name',
                'f.title',
                'f.description',
            ) );
        }

        if ( ! empty( $atts['filters'] ) ) {
            $atts['filters'] = WPC()->decode_ajax_data( $atts['filters'] );

            if ( is_array( $atts['filters'] ) ) {
                foreach ( $atts['filters'] as $by=>$ids ) {
                    if ( empty( $ids ) )
                        continue;

                    switch ( $by ) {
                        case 'category':
                            $categories_list = array();
                            foreach( $ids as $id ) {
                                $categories_list[] = $id;
                            }
                            $where .= " AND f.cat_id IN('" . implode( "','", $categories_list ) . "') ";
                            break;
                        case 'author':
                            $authors_list = array();
                            foreach( $ids as $id ) {
                                $authors_list[] = $id;
                            }
                            $where .= " AND f.user_id IN('" . implode( "','", $authors_list ) . "') ";
                            break;
                        case 'tags':
                            $num_filters = 0;
                            $where .= " AND ( ";

                            foreach ( $ids as $id ) {
                                if ( $num_filters > 0 ) {
                                    if ( isset( $atts['filter_condition'] ) && $atts['filter_condition'] == 'and' ) {
                                        $where .= " AND ";
                                    } else {
                                        $where .= " OR ";
                                    }
                                }

                                if ( $id == 0 ) {
                                    //get files without tags
                                    $all_files = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_files" );
                                    $all_file_tags = wp_get_object_terms( $all_files, 'wpc_file_tags', array( 'fields' => 'ids' ) );
                                    $tag_files = get_objects_in_term( $all_file_tags, 'wpc_file_tags', array( 'fields' => 'ids' ) );

                                    $where .= " f.id NOT IN('" . implode( "','", $tag_files ) . "')";
                                } else {
                                    //get files with current tag
                                    $tag_files = get_objects_in_term( $id, 'wpc_file_tags', array( 'fields' => 'ids' ) );
                                    $where .= " f.id IN('" . implode( "','", $tag_files ) . "')";
                                }
                                $num_filters++;
                            }

                            $where .= ") ";
                            break;
                        case 'creation_date':
                            $value = (array)$ids;

                            $value['from'] = mktime( 0, 0, 0, date( "m", $value['from'] ), date( "d", $value['from'] ), date( "y", $value['from'] ) );
                            $value['to'] = mktime( 0, 0, 0, date( "m", $value['to'] ), date( "d", $value['to'] ) + 1, date( "y", $value['to'] ) ) - 1;

                            $where .= " AND ( f.time >= {$value['from']} AND f.time <= {$value['to']} )";
                            break;
                    }
                }
            }
        }

        if ( count( $categories_ids ) )
            $where .= " AND cat_id IN('" . implode( "','", $categories_ids ) . "')";


        $file_ids = $wpdb->get_col( "SELECT f.id FROM {$wpdb->prefix}wpc_client_files f WHERE 1=1 {$where}" );

        $user_id = $this->get_client_id();
        $author_file_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_files WHERE user_id = {$user_id}");
        if ( $atts['file_type'] == 'own' ) {
            //$all_file_ids = count( $file_ids ) ? array_intersect( $author_file_ids, $file_ids ) : $author_file_ids;
            $all_file_ids = count( $file_ids ) ? array_intersect( $author_file_ids, $file_ids ) : array();
        } else {
            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
            $client_file_caregories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $user_id );

            //if nested assigns turn ON
            if ( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
                $temp_cat_array = array();
                foreach ( $client_file_caregories as $file_category ) {
                    $children_categories = $this->get_category_children_ids( $file_category );
                    $temp_cat_array = array_merge( $temp_cat_array, $children_categories );
                }
                $client_file_caregories = array_merge( $client_file_caregories, $temp_cat_array );
            }

            $assigned_file_ids = $wpdb->get_col(
                "SELECT f.id
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE cat_id IN('" . implode( "','", ( count( $categories_ids ) ? array_intersect( $categories_ids, $client_file_caregories ) : $client_file_caregories ) ) . "')"
            );

            //Files with clients access
            $direct_assigned_file_ids = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $user_id );
            $assigned_file_ids = array_merge( $assigned_file_ids,
                count( $file_ids ) ? array_intersect( $direct_assigned_file_ids, $file_ids ) : $direct_assigned_file_ids
            );

            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );
            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach ( $client_groups_id as $group_id ) {
                    //Files in categories with group access
                    $group_file_caregories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $group_id );

                    //if nested assigns turn ON
                    if ( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
                        $temp_cat_array = array();
                        foreach( $group_file_caregories as $file_category ) {
                            $children_categories = $this->get_category_children_ids( $file_category );
                            $temp_cat_array = array_merge( $temp_cat_array, $children_categories );
                        }
                        $group_file_caregories = array_merge( $group_file_caregories, $temp_cat_array );
                    }

                    $assigned_fcat_file_ids = $wpdb->get_col(
                        "SELECT f.id
                        FROM {$wpdb->prefix}wpc_client_files f
                        WHERE cat_id IN('" . implode( "','", ( count( $categories_ids ) ? array_intersect( $categories_ids, $group_file_caregories ) : $group_file_caregories ) ) . "')"
                    );

                    //Files with group access
                    $group_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $group_id );
                    $assigned_file_ids = array_merge( $assigned_file_ids, $assigned_fcat_file_ids,
                        count( $file_ids ) ? array_intersect( $group_files, $file_ids ) : $group_files
                    );
                }
            }

            $assigned_file_ids = count( $file_ids ) ? array_intersect( $assigned_file_ids, $file_ids ) : array();

            $all_file_ids = array_unique( $assigned_file_ids );

            if ( $atts['file_type'] == 'assigned' ) {
                $all_file_ids = array_diff( $all_file_ids, $author_file_ids );
            }
        }


        /*our_hook_
        hook_name: wpc_client_all_file_ids
        hook_title: Client's Files
        hook_description: Hook runs when get client's files IDs.
        hook_type: filter
        hook_in: wp-client
        hook_location class.files.php
        hook_param: array $all_file_ids, array $atts
        hook_since: 4.4.5.8
        */
        return apply_filters( 'wpc_client_all_file_ids', $all_file_ids, $atts );
    }


    /**
     * Function for different filters conditions
     *
     * @param $data
     * @param $already_tags
     * @return array
     */
    private function get_unfiltered_files( $data, &$already_tags ) {
        $filters = WPC()->decode_ajax_data( $_POST['filters'] );

        if ( ! empty( $filters['tags'] ) )
            $already_tags = $filters['tags'];

        if ( empty( $data['filter_condition'] ) || $data['filter_condition'] == 'or' ) {
            if ( ! empty( $filters['tags'] ) )
                unset( $filters['tags'] );

            $_POST['filters'] = WPC()->encode_ajax_data( $filters );
        }

        return $this->get_file_ids( array(
            'category'              => isset( $data['category'] ) ? $data['category'] : '',
            'with_subcategories'    => $data['with_subcategories'],
            'file_type'             => $data['file_type'],
            'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
            'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
            'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
        ) );
    }


    /**
     * Get filters types
     *
     * @param $data
     * @param $files
     * @return array
     */
    public function get_filters_data( $data, $files ) {
        global $wpdb;

        //construct array of file categories for filter in template
        $filters = array();
        if ( ! $data['show_filters'] || count( $files ) <= 1 )
            return $filters;

        //get all categories of files
        $cats = $this->render_category_list_items( array(
            'format'    => 'array',
            'by'        => 'file_ids',
            'value'     => $files
        ) );

        if ( count( $cats ) > 1 )
            $filters['categories'] = $cats;

        //get all file's authors
        if ( $data['file_type'] != 'own' && $data['show_author'] ) {
            $authors = $wpdb->get_col(
                "SELECT DISTINCT( f.user_id )
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE f.id IN('" . implode( "','", $files ) . "')"
            );

            if ( count( $authors ) > 1 )
                $filters['authors'] = $authors;
        }

        //get file's uploaded date
        $dates = $wpdb->get_col(
            "SELECT DISTINCT TO_DAYS( FROM_UNIXTIME( f.time ) ) AS uploaded
            FROM {$wpdb->prefix}wpc_client_files f
            WHERE f.id IN('" . implode( "','", $files ) . "')"
        );

        if ( count( $dates ) > 1 )
            $filters['dates'] = $dates;

        $already_tags = array();
        if ( ! empty( $_POST['filters'] ) )
            $files = $this->get_unfiltered_files( $data, $already_tags );

        //get all file's tags
        $tags = wp_get_object_terms( $files, 'wpc_file_tags', array( 'fields' => 'ids' ) );
        if ( count( $tags ) ) {
            //get files without tags
            $file_in_terms = get_objects_in_term( $tags, 'wpc_file_tags', array( 'fields' => 'ids' ) );
            if ( is_array( $file_in_terms ) && count( array_diff( $files, $file_in_terms ) ) )
                $tags[] = 0;

            //remove tags which already selected
            $tags = array_diff( $tags, $already_tags );

            if ( count( $tags ) > 1 ) {
                $filters['tags'] = $tags;
            } elseif ( count( $tags ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                $filters['tags'] = $tags;
            }
        }

        return $filters;
    }


    /**
     * Get filter dropdown by filter type
     */
    public function ajax_files_shortcode_get_filter() {
        global $wpdb;

        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

        $filter_by = ! empty( $_POST['filter_by'] ) ? $_POST['filter_by'] : '';

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $data['category'] ) ? $data['category'] : '',
            'with_subcategories'    => $data['with_subcategories'],
            'file_type'             => $data['file_type'],
            'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
            'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
            'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
        ) );

        $filter_html = '';
        $mindate = '';
        $maxdate = '';
        if ( ! empty( $all_file_ids ) ) {
            $options = array();
            switch( $filter_by ) {
                case 'category': {
                    $options = $this->render_category_list_items( array(
                        'format'    => 'array',
                        'by'        => 'file_ids',
                        'value'     => $all_file_ids
                    ) );

                    break;
                }
                case 'author': {

                    $authors = $wpdb->get_results(
                        "SELECT DISTINCT( u.user_login ), f.user_id AS id
                        FROM {$wpdb->prefix}wpc_client_files f
                        LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
                        WHERE f.id IN('" . implode( "','", $all_file_ids ) . "')
                        ORDER BY f.user_id",
                        ARRAY_A );

                    if ( ! empty( $authors ) ) {
                        foreach ( $authors as $key=>$author ) {
                            $options[$author['id']] = array(
                                'title'     => ! empty( $author['user_login'] ) ? $author['user_login'] : __( 'None', WPC_CLIENT_TEXT_DOMAIN ),
                            );
                        }
                    }
                    break;
                }

                case 'tags': {

                    $already_tags = array();
                    $all_file_ids = $this->get_unfiltered_files( $data, $already_tags );

                    $tags = wp_get_object_terms( $all_file_ids, 'wpc_file_tags', array( 'fields' => 'all' ) );

                    $term_ids = wp_get_object_terms( $all_file_ids, 'wpc_file_tags', array( 'fields' => 'ids' ) );
                    $file_in_terms = get_objects_in_term( $term_ids, 'wpc_file_tags', array( 'fields' => 'ids' ) );

                    if ( is_array( $file_in_terms ) && count( array_diff( $all_file_ids, $file_in_terms ) ) ) {
                        if ( empty( $already_tags ) || ! in_array( 0, $already_tags ) ) {
                            $options[0] = array(
                                'title'     => __( 'None', WPC_CLIENT_TEXT_DOMAIN ),
                            );
                        }
                    }

                    if ( ! is_wp_error( $tags ) ) {
                        foreach ( $tags as $key=>$tag ) {
                            if ( empty( $already_tags ) || ! in_array( $tag->term_id, $already_tags ) ) {
                                $options[$tag->term_id] = array(
                                    'title'     => $tag->name,
                                );
                            }
                        }
                    }
                    break;
                }

                case 'creation_date': {
                    $files = $wpdb->get_col(
                        "SELECT f.time
                        FROM {$wpdb->prefix}wpc_client_files f
                        WHERE f.id IN('" . implode( "','", $all_file_ids ) . "')"
                    );

                    $mindate = min($files);
                    $maxdate = max($files);
                    break;
                }
            }

            $filter_html = WPC()->get_template( 'files/filters/dropdown.php', '', array(
                'filter_by' => $filter_by,
                'options'   => $options,
                'from_date' => $mindate,
                'to_date'   => $maxdate
            ), false );
        }

        wp_die( json_encode( array(
            'status'        => true,
            'filter_html'   => $filter_html,
            'mindate'       => $mindate,
            'maxdate'       => $maxdate
        ) ) );
    }


    /**
     * Get single filter data by filter ID
     */
    public function ajax_get_filter_data() {
        global $wpdb;

        if ( ! empty( $_POST['filter_by'] ) ) {
            $data['filter_html'] = '';

            switch( $_POST['filter_by'] ) {
                case 'category': {
                    if( !isset( $_POST['filter_id'] ) ) {
                        echo json_encode( array( 'status' => false, 'message' => __( 'Invalid Data', WPC_CLIENT_TEXT_DOMAIN ) ) );
                    }

                    $title = __( 'Category', WPC_CLIENT_TEXT_DOMAIN );

                    $cat_name = $wpdb->get_var( $wpdb->prepare(
                        "SELECT cat_name
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id=%d",
                        $_POST['filter_id']
                    ) );

                    $name = ( isset( $cat_name ) && !empty( $cat_name ) ) ? $cat_name : $name = __( 'Undefined Category', WPC_CLIENT_TEXT_DOMAIN );

                    break;
                }
                case 'author': {
                    if( !isset( $_POST['filter_id'] ) ) {
                        echo json_encode( array( 'status' => false, 'message' => __( 'Invalid Data', WPC_CLIENT_TEXT_DOMAIN ) ) );
                    }

                    $title = __( 'Author', WPC_CLIENT_TEXT_DOMAIN );
                    $name = __( 'Without author', WPC_CLIENT_TEXT_DOMAIN );

                    if( '0' != $_POST['filter_id'] ) {
                        $userdata = get_userdata( $_POST['filter_id'] );
                        $name = ( isset( $userdata->user_login ) && !empty( $userdata->user_login ) ) ? $userdata->user_login : __( 'Without author', WPC_CLIENT_TEXT_DOMAIN );
                    }

                    break;
                }

                case 'tags': {
                    if( !isset( $_POST['filter_id'] ) ) {
                        echo json_encode( array( 'status' => false, 'message' => __( 'Invalid Data', WPC_CLIENT_TEXT_DOMAIN ) ) );
                    }

                    $title = __( 'Tag', WPC_CLIENT_TEXT_DOMAIN );
                    $name = __( 'Without tags', WPC_CLIENT_TEXT_DOMAIN );

                    if( '0' != $_POST['filter_id'] ) {
                        $tag = get_term( $_POST['filter_id'], 'wpc_file_tags' );
                        $name = isset( $tag->name ) && !empty( $tag->name ) ? $tag->name : __( 'Without tags', WPC_CLIENT_TEXT_DOMAIN );
                    }

                    break;
                }

                case 'creation_date': {

                    /*$from = mktime( 0, 0, 0, date( "m", $_POST['from'] ), date( "d", $_POST['from'] ), date( "y", $_POST['from'] ) );
                    $to = mktime( 0, 0, 0, date( "m", $_POST['to'] ), date( "d", $_POST['to'] ) + 1, date( "y", $_POST['to'] ) ) - 1;*/

                    $from = $_POST['from'];
                    $to = $_POST['to'];

                    exit( json_encode( array(
                        'status'    => true,
                        'title'     => __( 'Uploaded Date', WPC_CLIENT_TEXT_DOMAIN ),
                        'to'        => WPC()->date_format( $to, 'date' ),
                        'from'      => WPC()->date_format( $from, 'date' ),
                    ) ) );

                    break;
                }
            }

            echo json_encode( array( 'status' => true, 'title' => $title, 'name' => $name ) );
        } else {
            echo json_encode( array( 'status' => false, 'message' => __( 'Invalid Data', WPC_CLIENT_TEXT_DOMAIN ) ) );
        }
        exit;
    }


    /**
     * Prepare file's data for PHP templates
     *
     * @param $file
     * @param $data
     * @param $user_id
     * @param $view_type
     * @return array
     */
    function build_filedata( $file, $data, $user_id, $view_type ) {
        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        $filedata = array();

        $filedata['id'] = $file['id'];

        //take type from real file name\url
        $file_type = explode( '.', $file['title'] );

        if ( 1 == count( $file_type ) ) {
            //take type from file name
            $file_type = explode( '.', $file['name'] );
        }

        if ( 1 == count( $file_type ) ) {
            //take type from file title
            $file_type = explode( '.', $file['filename'] );
        }



        $file_type = strtolower( end( $file_type ) );
        $file_type = ( 6 >= strlen( $file_type ) ) ? $file_type : 'unknown';

        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $current_page = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
        } else {
            global $post;
            $current_page = ( !empty( $post ) && isset( $post->ID ) ) ? get_permalink( $post->ID ) : '';
        }


        $view_url       = $data['view_url'];
        $url            = $data['download_url'];

        if( $view_type == 'blog' ) {
            if( in_array( $file_type, array( 'gif', 'jpg', 'jpeg', 'png' ) ) && !$file['external'] ) {
                $this->create_image_thumbnail( $file );
                $filedata['icon']      = '<img width="50%" height="100" class="wpc_file_thumbnail" src="' . add_query_arg( array( 'id' => $file['id'] ), $view_url ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" />';
            } elseif( in_array( $file_type, $this->file_video_formats ) ) {
                $filedata['icon']      = '[video ' . $file_type . '="' . add_query_arg( array( 'id' => $file['id'] . '.' . $file_type ), $view_url ) . '" height="300" /]';
            } else {
                $filedata['icon']      = '<img class="wpc_img_file_icon" width="40" height="40" src="' . $this->get_fileicon( $file_type ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" />';
            }
        } else {
            if( in_array( $file_type, array( 'gif', 'jpg', 'jpeg', 'png' ) ) && !$file['external'] ) {
                $this->create_image_thumbnail( $file );
                $filedata['icon']       = '<img class="wpc_img_file_icon" wpc_images="' . add_query_arg( array( 'id' => $file['id'], 'thumbnail' => true ), $view_url ) . '" src="' . add_query_arg( array( 'id' => $file['id'], 'thumbnail' => true ), $view_url ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" />';
            }
            elseif ( in_array( $file_type, array( 'webm', 'asf', 'wma', 'wmv', 'wm', 'avi', 'mpg', 'mpeg', 'mp4', 'wav', 'mov', 'm4a', 'm4v', 'mp4v', '3gp', '3gpp', 'mkv', 'flv', 'swf' ) ) && !$file['external'] ){
                $filedata['icon']       = '<img class="wpc_img_file_icon" src="' . $this->get_fileicon( $file_type ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" />';
            }
            else {
                $filedata['icon']       = '<img class="wpc_img_file_icon" src="' . $this->get_fileicon( $file_type ) . '" class="attachment-80x60" alt="' . $file_type . '" title="' . $file_type . '" />';
            }
        }

        $filedata['url']  = '';
        $filedata['new_page']       = false;
        $filedata['popup']          = false;
        
        if( !$file['external'] || 1 == $file['protect_url'] ) {
            $filedata['url']        = add_query_arg( array( 'id' => $file['id'] ), $url );
            if( ( isset( $wpc_file_sharing['google_doc_embed'] ) && 'yes' == $wpc_file_sharing['google_doc_embed']
                  && in_array( $file_type, array_keys( $this->files_for_google_doc_view ) ) )
                  || in_array( $file_type, $this->files_for_regular_view )) {

                $filedata['view_url']   = add_query_arg( array( 'id' => $file['id'] ), $view_url );
                $filedata['new_page'] = true;
            }
            if( in_array( $file_type, array( 'webm', 'asf', 'wma', 'wmv', 'wm', 'avi', 'mpg', 'mpeg', 'mp4', 'wav', 'mov', 'm4a', 'm4v', 'mp4v', '3gp', '3gpp', 'mkv', 'flv', 'swf' ) ) ) {
              $filedata['view_url']   = add_query_arg( array( 'id' => $file['id'] ), $view_url );
              $filedata['popup'] = true;
            }
        } else {
            $filedata['url']        = $file['filename'];
            $filedata['view_url']   = $file['filename'];
            $filedata['new_page']   = true;
        }


        $filedata['name']           = ( isset( $file['title'] ) && '' != $file['title'] ) ? $file['title'] : $file['name'];
        $filedata['title']          = ( isset( $file['title'] ) && '' != $file['title'] ) ? $file['title'] : $file['name'];
        $filedata['filename']       = $file['filename'];
        $filedata['description']    = $file['description'];
        $filedata['size']           = ( $file['size'] ) ? WPC()->format_bytes( $file['size'] ) : '';


        $filedata['author_id']      = $file['user_id'];
        $userdata                   = get_userdata( $file['user_id'] );
        $filedata['author']         = ( isset( $userdata->user_login ) && !empty( $userdata->user_login ) ) ? $userdata->user_login : '';


        $filedata['category_id']    = ( isset( $file['category_id'] ) && !empty( $file['category_id'] ) ) ? $file['category_id'] : false;
        $filedata['category_name']  = ( isset( $file['category_name'] ) && !empty( $file['category_name'] ) ) ? $file['category_name'] : false;

        $filetags = wp_get_object_terms( $file['id'], 'wpc_file_tags', array( 'fields' => 'all') );

        $filedata['tags'] = array();
        foreach( $filetags as $tag ) {
            $filedata['tags'][] = array(
                'term_id'   => $tag->term_id,
                'name'      => $tag->name
            );
        }
      
        $filedata['date']          = WPC()->date_format( $file['time'], 'date' );
        $filedata['time']          = WPC()->date_format( $file['time'], 'time' );
        $filedata['timestamp']     = $file['time'];

        $filedata['last_download'] = false;
        if ( isset( $file['client_last_download'] ) && '' != $file['client_last_download'] ) {
            $filedata['last_download']['text'] = __( 'Last Download:', WPC_CLIENT_TEXT_DOMAIN );
            $filedata['last_download']['date'] = WPC()->date_format( $file['client_last_download'], 'date' );
            $filedata['last_download']['time'] = WPC()->date_format( $file['client_last_download'], 'time' );
            $filedata['last_download']['timestamp'] = '';
        } elseif ( isset( $file['last_download'] ) && '' != $file['last_download'] ) {
            $filedata['last_download']['text'] = __( 'Last Download:', WPC_CLIENT_TEXT_DOMAIN );
            $filedata['last_download']['date'] = WPC()->date_format( $file['last_download'], 'date' );
            $filedata['last_download']['time'] = WPC()->date_format( $file['last_download'], 'time' );
            $filedata['last_download']['timestamp'] = '';
        }

        $filedata['order']         = ( isset( $file['order_id'] ) ) ? $file['order_id'] : '';

        if( $file['user_id'] == $user_id && current_user_can( 'wpc_delete_uploaded_files' ) ) { //delete uploaded files
            $filedata['delete_url']    = add_query_arg( array( 'wpc_act' => 'delete_file', 'id' => $file['id'], ), $current_page );
        } elseif ( $file['user_id'] != $user_id && current_user_can( 'wpc_delete_assigned_files' ) ) { //delete assigned files
            $filedata['delete_url']    = add_query_arg( array( 'wpc_act' => 'delete_file', 'id' => $file['id'], ), $current_page );
        }

        /*hook_name: wpc_client_build_shortcode_filedata
        hook_title: Change filedata for file's shortcodes
        hook_description: Hook filtered filedata before output it to display.
        hook_type: filter
        hook_in: wp-client
        hook_location class.common.php
        hook_param: array $filedata,array $file,array $data
        hook_since: 3.8.6
        */
        $filedata = apply_filters( 'wpc_client_build_shortcode_filedata', $filedata, $file, $data );

        return $filedata;
    }


    /**
     * Shortcode handler when page load
     *
     * @param $atts
     * @return string|void
     */
    public function shortcode_files_list( $atts ) {
        global $wpdb;

        //checking access
        if ( ! WPC()->checking_page_access() ) {
            return '';
        }

        WPC()->custom_fields()->add_custom_datepicker_scripts();
        wp_enqueue_script( 'jquery-base64' );

        wp_enqueue_script( 'jquery-ui-tooltip' );

        wp_enqueue_script( 'wpc-files-shortcode-list-js', false, array(), WPC_CLIENT_VER, true );

        wp_enqueue_style( 'wp-client-files-list-style' );

        $this->add_scripts_to_front_files();

        $user_id = $this->get_client_id();
        $data = $atts = shortcode_atts( array(), $atts, 'wpc_client_files_list' );

        $data['files_form_id'] = rand( 0, 10000 );
        $data['home_url'] = get_home_url( get_current_blog_id() );

        if ( WPC()->permalinks ) {
            $data['view_url']       = WPC()->make_url( '/wpc_downloader/core/?wpc_action=view', $data['home_url'] );
            $data['download_url']   = WPC()->make_url( '/wpc_downloader/core/?wpc_action=download', $data['home_url'] );
        } else {
            $data['view_url']       = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'view' ), $data['home_url'] );
            $data['download_url']   = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'download' ), $data['home_url'] );
        }

        $order_string = $data['categories_sort'] = $data['categories_dir'] = '';
        if ( $data['show_file_cats'] ) {

            $data['categories_sort'] = 'name';
            $cat_sort = 'fc.cat_name';
            if( isset( $atts['categories_sort_type'] ) && $atts['categories_sort_type'] == 'order_id' ) {
                $cat_sort = 'fc.cat_order';
                $data['categories_sort'] = 'order_id';
            }

            $data['categories_dir'] = 'asc';
            $cat_dir = 'ASC';
            if( isset( $atts['categories_sort'] ) && $atts['categories_sort'] == 'desc' ) {
                $cat_dir = 'DESC';
                $data['categories_dir'] = 'desc';
            }

            $order_string = "$cat_sort $cat_dir, fc.cat_id ASC,";
        }

        $data['sort'] = 'order_id';
        $data['dir'] = 'asc';
        if( !empty( $atts['sort_type'] ) && !empty( $atts['sort'] ) ) {

            if( $atts['sort'] == 'desc' ) {
                $data['dir'] = $atts['sort'];
                $order = $atts['sort'];
            } else {
                $data['dir'] = 'asc';
                $order = 'asc';
            }

            if( $atts['sort_type'] == 'date' ) {
                $data['sort'] = 'time';
                $order_by = 'f.time';
            } elseif( $atts['sort_type'] == 'name' ) {
                $data['sort'] = 'name';
                $order_by = 'f.title';
            } elseif( $atts['sort_type'] == 'order_id' ) {
                $data['sort'] = 'order_id';
                $order_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
            }

            $order_string .= "$order_by $order";

            if( $atts['sort_type'] == 'order_id' ) {
                $order_string .= ", f.title ASC";
            }
        } else {
            $order_string .= 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id ASC, f.title ASC';
        }

        if ( $data['show_sort'] ) {
            $data['sort_button'] = '';

            switch ( $data['sort'] ) {
                case 'name':
                    $data['sort_button'] = __( 'Filename', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'time':
                    $data['sort_button'] = __( 'Uploaded', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'order_id':
                    $data['sort_button'] = __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN );
                    break;
            }

            if ( $data['dir'] == 'desc' ) {
                $data['sort_button'] .= ' DESC';
            } else {
                $data['sort_button'] .= ' ASC';
            }
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $atts['category'] ) ? $atts['category'] : '',
            'with_subcategories'    => $atts['with_subcategories'],
            'file_type'             => $atts['file_type'],
        ) );
        $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

        $count_files = count( $all_file_ids );

        if ( $data['show_pagination'] ) {
            $per_page = (int)$data['show_pagination_by'] > 0 ? (int)$data['show_pagination_by'] : $count_files;
        } else {
            $per_page = $count_files;
        }

        $data['count_pages'] = 1;
        if ( $per_page < $count_files ) {
            $data['count_pages'] = ceil( $count_files / $per_page );
        }

        //client's files
        $files = $wpdb->get_results(
            "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
            FROM {$wpdb->prefix}wpc_client_files f
            INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
            LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
            WHERE 1=1 $where
            GROUP BY f.id
            ORDER BY $order_string
            LIMIT 0, $per_page",
        ARRAY_A );

        $data['files'] = array();
        if ( ! empty( $files ) ) {
            foreach( $files as $key=>$file ) {
                $temp = $this->build_filedata( $file, $data, $user_id, 'list' );
                if ( !( $data['show_file_cats'] && ( ( isset( $files[$key - 1]['category_id'] ) && $file['category_id'] != $files[$key - 1]['category_id'] ) || !isset( $files[$key - 1]['category_id'] ) ) ) ) {
                    unset( $temp['category_name'] );
                }
                $data['files'][] = $temp;
            }
            $last_file = end( $files );
            $data['last_category_id'] = $last_file['category_id'];
        }

        $localize_data = $data;
        unset( $localize_data['files'] );

        $localize_arguments = array(
            'ajax_url'      => WPC()->get_ajax_url(),
            'data'          => $localize_data,
            'count_pages'   => $data['count_pages'],
        );

        //$localize_arguments['exclude_author'] = $atts['file_type'] == 'assigned';

        if ( ! empty( WPC()->current_plugin_page['client_id'] ) ) {
            $localize_arguments['client_id'] = WPC()->current_plugin_page['client_id'];
            $localize_arguments['_wpnonce'] = wp_create_nonce( WPC()->current_plugin_page['client_id'] . "client_security" );
        }

        wp_localize_script( 'wpc-files-shortcode-js', 'wpc_file_shortcode' . $data['files_form_id'], $localize_arguments );

        //construct array of file categories for filter in template
        $data['filters'] = $this->get_filters_data( $data, $all_file_ids );



        return WPC()->get_template( 'files/list/common.php', '', $data );
    }


    public function ajax_files_shortcode_list_pagination() {
        global $wpdb;

        $user_id = $this->get_client_id();
        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

        $order_string = '';
        if ( $data['show_file_cats'] ) {

            $cat_sort = 'fc.cat_name';
            if( isset( $data['categories_sort'] ) && $data['categories_sort'] == 'order_id' ) {
                $cat_sort = 'fc.cat_order';
            }

            $cat_dir = 'ASC';
            if( isset( $data['categories_dir'] ) && $data['categories_dir'] == 'desc' ) {
                $cat_dir = 'DESC';
            }

            $order_string = "$cat_sort $cat_dir, fc.cat_id ASC,";
        }

        if ( ! empty( $_POST['sorting'] ) ) {
            $sorting_array = explode( '_', $_POST['sorting'] );

            if ( ! empty( $sorting_array[0] ) ) {
                switch ( $sorting_array[0] ) {
                    case 'orderid':
                        $sort_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
                        break;
                    case 'filename':
                        $sort_by = 'f.title';
                        break;
                    case 'date':
                        $sort_by = 'f.time';
                        break;
                    case 'downloaded':
                        $sort_by = 'f.last_download';
                        break;
                    case 'size':
                        $sort_by = 'f.size';
                        break;
                    case 'author':
                        $sort_by = 'u.user_login';
                        break;
                    case 'category':
                        $order_string = '';
                        $sort_by = 'fc.cat_name';
                        break;
                }
            }

            $sort_dir = strtoupper( $sorting_array[1] );
            $order_string .= "$sort_by $sort_dir";

            if ( isset( $sorting_array[0] ) && 'orderid' == $sorting_array[0] ) {
                $order_string .= ", f.title ASC";
            }

            if ( isset( $sorting_array[0] ) && 'category' == $sorting_array[0] ) {
                $order_string .= ',f.order_id = 0 OR ISNULL(f.order_id), f.order_id ASC, f.title ASC';
            }

        } else {

            if( isset( $data['sort'] ) && !empty( $data['sort'] ) && isset( $data['dir'] ) && !empty( $data['dir'] ) ) {
                if( $data['dir'] == 'desc' ) {
                    $order = $data['dir'];
                } else {
                    $order = 'asc';
                }

                if( $data['sort'] == 'time' ) {
                    $order_by = 'f.time';
                } elseif( $data['sort'] == 'name' ) {
                    $order_by = 'f.title';
                } elseif( $data['sort'] == 'order_id' ) {
                    $order_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
                }

                $order_string .= "$order_by $order";

                if( $data['sort'] == 'order_id' ) {
                    $order_string .= ", f.title ASC";
                }
            } else {
                $order_string .= 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id ASC, f.title ASC';
            }
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $data['category'] ) ? $data['category'] : '',
            'with_subcategories'    => $data['with_subcategories'],
            'file_type'             => $data['file_type'],
            'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
            'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
            'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
        ) );
        $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

        //pagination
        $count_files = count( $all_file_ids );
        $start_count = 0;

        if ( $data['show_pagination'] ) {
            $per_page = ( $data['show_pagination_by'] ) ? (int)$data['show_pagination_by'] : $count_files;

            $current_page = ! empty( $_POST['current_page'] ) ? $_POST['current_page'] : 0;
            $data['count_pages'] = 1;
            $data['pagination'] = false;
            if( empty( $_POST['sort_button'] ) ) {
                if( $per_page < $count_files ) {

                    $data['count_pages'] = ceil( $count_files / $per_page );

                    $data['pagination'] = true;
                    if( $current_page == $data['count_pages'] - 1 ) {
                        $data['pagination'] = false;
                    }

                    $start_count = $current_page * $per_page;
                }
            } else {
                $start_count = $current_page * $per_page;
            }

        } else {
            $per_page = $count_files;
        }

        $files = $wpdb->get_results(
            "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
            FROM {$wpdb->prefix}wpc_client_files f
            INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
            LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
            WHERE 1=1 $where
            GROUP BY f.id
            ORDER BY $order_string
            LIMIT $start_count, $per_page",
        ARRAY_A );


        if ( ! empty( $files ) ) {
            foreach ( $files as $key=>$file ) {
                $temp = $this->build_filedata( $file, $data, $user_id, 'list' );
                if ( !( $data['show_file_cats'] && (
                        ( isset( $files[$key - 1]['category_id'] ) && $file['category_id'] != $files[$key - 1]['category_id'] ) ||
                        ( !isset( $files[$key - 1]['category_id'] ) && $file['cat_id'] != $_POST['last_category_id'] ) ) ) ) {
                    unset( $temp['category_name'] );
                }

                $data['files'][] = $temp;
            }

            $last_file = end( $files );
            $data['last_category_id'] = $last_file['category_id'];
        }

        $data['filter_html'] = WPC()->get_template( 'files/filters/items.php', '', array(
            'filters' => $this->get_filters_data( $data, $all_file_ids )
        ) );

        wp_die( json_encode( array(
            'status'            => true,
            'html'              => WPC()->get_template( 'files/list/items.php', '', $data ),
            'pagination'        => $data['pagination'],
            'last_category_id'  => $data['last_category_id'],
            'filter_html'       => $data['filter_html']
        ) ) );
    }


    public function shortcode_files_blog( $atts ) {
        global $wpdb;

        //checking access
        if ( ! WPC()->checking_page_access() ) {
            return '';
        }

        WPC()->custom_fields()->add_custom_datepicker_scripts();
        wp_enqueue_script( 'jquery-base64' );


        wp_enqueue_script( 'wpc-files-blog-shortcode-js', false, array(), WPC_CLIENT_VER, true );

        wp_enqueue_style( 'wp-client-blog-style' );

        $this->add_scripts_to_front_files();

        wp_enqueue_style( 'wp-mediaelement' );
        wp_enqueue_script( 'wp-mediaelement' );


        $user_id = $this->get_client_id();
        $data = $atts = shortcode_atts( array(), $atts, 'wpc_client_files_blog' );

        $data['files_form_id'] = rand( 0, 10000 );
        $data['home_url'] = get_home_url( get_current_blog_id() );

        if ( WPC()->permalinks ) {
            $data['view_url']       = WPC()->make_url( '/wpc_downloader/core/?wpc_action=view', $data['home_url'] );
            $data['download_url']   = WPC()->make_url( '/wpc_downloader/core/?wpc_action=download', $data['home_url'] );
        } else {
            $data['view_url']       = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'view' ), $data['home_url'] );
            $data['download_url']   = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'download' ), $data['home_url'] );
        }

        $data['sort'] = 'time';
        $data['dir'] = 'desc';
        if ( ! empty( $atts['sort_type'] ) && ! empty( $atts['sort'] ) ) {
            if ( $atts['sort'] == 'asc' )
                $data['dir'] = 'asc';

            if ( $atts['sort_type'] == 'name' ) {
                $data['sort'] = 'name';
            } elseif ( $atts['sort_type'] == 'order_id' ) {
                $data['sort'] = 'order_id';
            }
        }

        $order_string = $data['sort'] . ' ' . $data['dir'];

        if ( $data['show_sort'] ) {
            $data['sort_button'] = '';

            switch( $data['sort'] ) {
                case 'name':
                    $data['sort_button'] = __( 'Filename', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'time':
                    $data['sort_button'] = __( 'Uploaded', WPC_CLIENT_TEXT_DOMAIN );
                    break;
                case 'order_id':
                    $data['sort_button'] = __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN );
                    break;
            }

            if( $data['dir'] == 'desc' ) {
                $data['sort_button'] .= ' DESC';
            } else {
                $data['sort_button'] .= ' ASC';
            }
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $atts['category'] ) ? $atts['category'] : '',
            'with_subcategories'    => $atts['with_subcategories'],
            'file_type'             => $atts['file_type'],
        ) );
        $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

        $count_files = count( $all_file_ids );
        if ( $data['show_pagination'] ) {
            $per_page = (int)$data['show_pagination_by'] > 0 ? (int)$data['show_pagination_by'] : $count_files;
        } else {
            $per_page = $count_files;
        }

        $data['count_pages'] = 1;
        if ( $per_page < $count_files ) {
            $data['count_pages'] = ceil( $count_files / $per_page );
        }

        //client's files
        $files = $wpdb->get_results(
            "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
            FROM {$wpdb->prefix}wpc_client_files f
            INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
            LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
            WHERE 1=1 $where
            GROUP BY f.id
            ORDER BY $order_string
            LIMIT 0, $per_page",
        ARRAY_A );

        $data['files'] = array();
        if ( ! empty( $files ) ) {
            foreach( $files as $key=>$file ) {
                $data['files'][] = $this->build_filedata( $file, $data, $user_id, 'blog' );
            }
        }

        $localize_data = $data;
        unset( $localize_data['files'] );

        $localize_arguments = array(
            'ajax_url'      => WPC()->get_ajax_url(),
            'data'          => $localize_data,
            'count_pages'   => $data['count_pages'],
        );

        if ( ! empty( WPC()->current_plugin_page['client_id'] ) ) {
            $localize_arguments['client_id'] = WPC()->current_plugin_page['client_id'];
            $localize_arguments['_wpnonce'] = wp_create_nonce( WPC()->current_plugin_page['client_id'] . "client_security" );
        }

        wp_localize_script( 'wpc-files-blog-shortcode-js', 'wpc_file_shortcode' . $data['files_form_id'], $localize_arguments );

        //construct array of file categories for filter in template
        $data['filters'] = $this->get_filters_data( $data, $all_file_ids );

        return WPC()->get_template( 'files/blog/common.php', '', $data );
    }


    public function ajax_files_shortcode_blog_pagination() {
        global $wpdb;

        $user_id = $this->get_client_id();
        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

        $order_string = '';
        if ( ! empty( $_POST['sorting'] ) ) {
            $sorting_array = explode( '_', $_POST['sorting'] );

            if ( ! empty( $sorting_array[0] ) ) {
                switch( $sorting_array[0] ) {
                    case 'orderid':
                        $sort_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
                        break;
                    case 'filename':
                        $sort_by = 'f.title';
                        break;
                    case 'date':
                        $sort_by = 'f.time';
                        break;
                    case 'downloaded':
                        $sort_by = 'f.last_download';
                        break;
                    case 'size':
                        $sort_by = 'f.size';
                        break;
                    case 'author':
                        $sort_by = 'u.user_login';
                        break;
                    case 'category':
                        $order_string = '';
                        $sort_by = 'fc.cat_name';
                        break;
                }
            }

            $sort_dir = strtoupper( $sorting_array[1] );
            $order_string .= "$sort_by $sort_dir";

            if ( isset( $sorting_array[0] ) && 'category' == $sorting_array[0] ) {
                $order_string .= ',f.order_id = 0 OR ISNULL(f.order_id), f.order_id ASC';
            }

        } else {
            $order_string .= 'f.time DESC';
        }


        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $data['category'] ) ? $data['category'] : '',
            'with_subcategories'    => $data['with_subcategories'],
            'file_type'             => $data['file_type'],
            'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
            'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
            'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
        ) );
        $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

        //pagination
        $count_files = count( $all_file_ids );

        $start_count = 0;
        if ( $data['show_pagination'] ) {
            $per_page = ( $data['show_pagination_by'] ) ? (int)$data['show_pagination_by'] : $count_files;

            $current_page = ! empty( $_POST['current_page'] ) ? $_POST['current_page'] : 0;

            $data['count_pages'] = 1;
            $data['pagination'] = false;
            if ( empty( $_POST['sort_button'] ) ) {
                if ( $per_page < $count_files ) {

                    $data['count_pages'] = ceil( $count_files / $per_page );

                    $data['pagination'] = true;
                    if( $current_page == $data['count_pages'] - 1 ) {
                        $data['pagination'] = false;
                    }

                    $start_count = $current_page * $per_page;
                }
            } else {
                $start_count = $current_page * $per_page;
            }
        } else {
            $per_page = $count_files;
        }

        $files = $wpdb->get_results(
            "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
            FROM {$wpdb->prefix}wpc_client_files f
            INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
            LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
            WHERE 1=1 {$where}
            GROUP BY f.id
            ORDER BY $order_string
            LIMIT $start_count, $per_page",
        ARRAY_A );

        if ( ! empty( $files ) ) {
            foreach( $files as $key=>$file ) {
                $data['files'][] = $this->build_filedata( $file, $data, $user_id, 'blog' );
            }
        }

        $data['filter_html'] = WPC()->get_template( 'files/filters/items.php', '', array(
            'filters' => $this->get_filters_data( $data, $all_file_ids )
        ) );

        //for compatibility with WP Video Player
        $content = WPC()->get_template( 'files/blog/items.php', '', $data );
        $content = do_shortcode( $content );

        wp_die( json_encode( array(
            'status'        => true,
            'html'          => $content,
            'pagination'    => $data['pagination'],
            'filter_html'   => $data['filter_html']
        ) ) );
    }


    public function shortcode_files_table( $atts ) {
        global $wpdb;

        //checking access
        if ( ! WPC()->checking_page_access() ) {
            return '';
        }

        WPC()->custom_fields()->add_custom_datepicker_scripts();
        wp_enqueue_script( 'jquery-base64' );

        wp_enqueue_script( 'jquery-ui-tooltip' );

        wp_enqueue_script( 'wpc-files-table-shortcode-js', false, array(), WPC_CLIENT_VER, true );

        wp_enqueue_style( 'wp-client-files-table-style' );

        $this->add_scripts_to_front_files();

        $user_id = $this->get_client_id();
        $data = $atts = shortcode_atts( array(), $atts, 'wpc_client_files_table' );

        $data['files_form_id'] = rand( 0, 10000 );
        $data['home_url'] = get_home_url( get_current_blog_id() );

        if ( WPC()->permalinks ) {
            $data['view_url']       = WPC()->make_url( '/wpc_downloader/core/?wpc_action=view', $data['home_url'] );
            $data['download_url']   = WPC()->make_url( '/wpc_downloader/core/?wpc_action=download', $data['home_url'] );
        } else {
            $data['view_url']       = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'view' ), $data['home_url'] );
            $data['download_url']   = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'download' ), $data['home_url'] );
        }

        $data['bulk_actions_array'] = array();

        $data['no_files_colspan'] = 1;

        if( $data['show_size'] ) {
            $data['no_files_colspan']++;
        }
        if( $data['show_author'] ) {
            $data['no_files_colspan']++;
        }
        if( $data['show_date'] ) {
            $data['no_files_colspan']++;
        }
        if( $data['show_last_download_date'] ) {
            $data['no_files_colspan']++;
        }
        if( $data['show_file_cats'] ) {
            $data['no_files_colspan']++;
        }

        $order_string = ' f.order_id = 0 OR ISNULL(f.order_id), f.order_id ASC, f.id ASC';

        $data['sort'] = '';
        $data['dir'] = '';
        if ( ! empty( $atts['sort_type'] ) && ! empty( $atts['sort'] ) ) {

            $data['sort'] = $atts['sort_type'];
            $data['dir'] = $atts['sort'];

            $order_by = 'f.time';
            if ( $atts['sort_type'] == 'name' ) {
                $order_by = 'f.title';
            } elseif ( $atts['sort_type'] == 'category' ) {
                $order_by = 'fc.cat_name';
            } elseif ( $atts['sort_type'] == 'order_id' ) {
                $order_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
            }

            $order = 'desc';
            if ( $atts['sort'] == 'asc' ) {
                $order = 'asc';
            }
            $order_string = $order_by . ' ' . $order;

            if ( $atts['sort_type'] == 'order_id' ) {
                $order_string .= ', f.id ASC';
            }
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $atts['category'] ) ? $atts['category'] : '',
            'with_subcategories'    => $atts['with_subcategories'],
            'file_type'             => $atts['file_type'],
        ) );
        $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

        $count_files = count( $all_file_ids );
        if ( $data['show_pagination'] ) {
            $per_page = (int)$data['show_pagination_by'] > 0 ? (int)$data['show_pagination_by'] : $count_files;
        } else {
            $per_page = $count_files;
        }

        $data['files_count'] = $count_files;
        $data['count_pages'] = $per_page < $count_files ? ceil( $count_files / $per_page ) : 1;

        //client's files
        $files = $wpdb->get_results(
            "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
            FROM {$wpdb->prefix}wpc_client_files f
            INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
            LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
            WHERE 1=1 $where
            GROUP BY f.id
            ORDER BY $order_string
            LIMIT 0, $per_page",
        ARRAY_A );


        $data['files'] = array();
        if ( ! empty( $files ) ) {
            $data['bulk_actions_array']['download'] = __( 'Download', WPC_CLIENT_TEXT_DOMAIN );

            foreach ( $files as $file ) {
                if ( $file['page_id'] != 0 && $file['user_id'] == $user_id ) {
                    if ( current_user_can( 'wpc_delete_uploaded_files' ) ) {
                        $data['bulk_actions_array']['delete'] = __( 'Delete', WPC_CLIENT_TEXT_DOMAIN );
                    }
                } else {
                    if ( current_user_can( 'wpc_delete_assigned_files' ) ) {
                        $data['bulk_actions_array']['delete'] = __( 'Delete', WPC_CLIENT_TEXT_DOMAIN );
                    }
                }

                $data['files'][] = $this->build_filedata( $file, $data, $user_id, 'table' );
            }
        }

        $localize_data = $data;
        unset( $localize_data['files'] );

        $localize_arguments = array(
            'ajax_url'      => WPC()->get_ajax_url(),
            'data'          => $localize_data,
            'count_pages'   => $data['count_pages'],
        );

        if ( ! empty( WPC()->current_plugin_page['client_id'] ) ) {
            $localize_arguments['client_id'] = WPC()->current_plugin_page['client_id'];
            $localize_arguments['_wpnonce'] = wp_create_nonce( WPC()->current_plugin_page['client_id'] . "client_security" );
        }

        wp_localize_script( 'wpc-files-table-shortcode-js', 'wpc_file_shortcode' . $data['files_form_id'], $localize_arguments );

        //construct array of file categories for filter in template
        $data['filters'] = $this->get_filters_data( $data, $all_file_ids );

        return WPC()->get_template( 'files/table/common.php', '', $data );
    }


    public function ajax_files_shortcode_table_pagination() {
        global $wpdb;

        $user_id = $this->get_client_id();
        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

        $order_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
        if( isset( $_POST['order_by'] ) && !empty( $_POST['order_by'] ) ) {
            switch( $_POST['order_by'] ) {
                case 'name':
                    $order_by = 'f.title';
                    break;
                case 'time':
                    $order_by = 'f.time';
                    break;
                case 'size':
                    $order_by = 'f.size';
                    break;
                case 'download_time':
                    $order_by = 'f.last_download';
                    break;
                case 'cat':
                    $order_by = 'fc.cat_name';
                    break;
            }
        } elseif( isset( $data['sort_type'] ) && !empty( $data['sort_type'] ) ) {
            if( $data['sort_type'] == 'time' ) {
                $order_by = 'f.time';
            } elseif( $data['sort_type'] == 'name' ) {
                $order_by = 'f.title';
            } elseif( $data['sort_type'] == 'category' ) {
                $order_by = 'fc.cat_name';
            }
        }

        $order = 'asc';
        if( isset( $_POST['order'] ) && !empty( $_POST['order'] ) ) {
            $order = ( $_POST['order'] == 'asc' || $_POST['order'] == 'desc' ) ? $_POST['order'] : 'asc';
        } elseif( isset( $data['sort'] ) && !empty( $data['sort'] ) ) {
            $order = ( $data['sort'] == 'asc' || $data['sort'] == 'desc' ) ? $data['sort'] : 'asc';
        }

        if( $order_by == 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id' ) {
            $order .= ', f.id ASC';
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $data['category'] ) ? $data['category'] : '',
            'with_subcategories'    => $data['with_subcategories'],
            'file_type'             => $data['file_type'],
            'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
            'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
            'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
        ) );
        $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

        $count_files = count( $all_file_ids );
        if ( $data['show_pagination'] ) {
            $per_page = ( $data['show_pagination_by'] ) ? (int)$data['show_pagination_by'] : $count_files;
        } else {
            $per_page = $count_files;
        }

        $start_count = ! empty( $_POST['current_page'] ) ? ( (int)$_POST['current_page'] - 1 ) * $per_page : 0;

        $files = $wpdb->get_results(
            "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
            FROM {$wpdb->prefix}wpc_client_files f
            INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
            LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
            WHERE 1=1 {$where}
            GROUP BY f.id
            ORDER BY $order_by $order
            LIMIT $start_count, $per_page",
        ARRAY_A );

        if ( ! empty( $files ) ) {
            foreach( $files as $file ) {
                $data['files'][] = $this->build_filedata( $file, $data, $user_id, 'table' );
            }
        }

        $filter_html = WPC()->get_template( 'files/filters/items.php', '', array(
            'filters' => $this->get_filters_data( $data, $all_file_ids )
        ) );

        $pagination_html = WPC()->get_template( 'files/table/pagination.php', '', array(
            'show_pagination'   => $data['show_pagination'],
            'files_count'       => $count_files,
            'count_pages'       => $per_page < $count_files ? ceil( $count_files / $per_page ) : 1
        )  );

        wp_die( json_encode( array(
            'status'        => true,
            'html'          => WPC()->get_template( 'files/table/items.php', '', $data ),
            'pagination'    => $pagination_html,
            'count_pages'   => $per_page < $count_files ? ceil( $count_files / $per_page ) : 1,
            'filter_html'   => $filter_html
        ) ) );
    }


    public function shortcode_files_tree( $atts ) {
        //checking access
        if ( ! WPC()->checking_page_access() ) {
            return '';
        }

        WPC()->custom_fields()->add_custom_datepicker_scripts();
        wp_enqueue_script( 'jquery-base64' );

        wp_enqueue_script( 'jquery-ui-tooltip' );


        wp_enqueue_style( 'wp-client-tree-style' );

        wp_enqueue_script( 'wpc-treetable-js', false, array(), WPC_CLIENT_VER, true );

        wp_enqueue_style( 'wpc-files-tree-style' );

        $this->add_scripts_to_front_files();

        $user_agent     =   $_SERVER['HTTP_USER_AGENT'];
        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        $fix_array       =   array(
            'Mac OS X',
            'Mac OS 9',
            'Linux',
            'Ubuntu',
            'iPhone',
            'iPod',
            'iPad',
            'Android',
            'BlackBerry',
            'Mobile'
        );

        foreach( $os_array as $regex => $value ) {
            if( preg_match( $regex, $user_agent ) ) {
                $os_platform    =   $value;
            }
        }

        if( wp_is_mobile() || in_array( $os_platform, $fix_array ) ) {

            wp_enqueue_script( 'wpc-files-tree-shortcode-mobile-js', false, array(), WPC_CLIENT_VER, true ); ?>

            <style type="text/css">
                .wpc_files_tree_content {
                    max-height: none !important;
                    overflow: visible !important;
                    height:auto !important;
                }

                .treetable td {
                    font-size: 11px;
                }

                .wpc_scroll_column {
                    width:0 !important;
                    padding:0 !important;
                }
            </style>
        <?php } else {
            wp_enqueue_script( 'wpc-files-tree-shortcode-js', false, array(), WPC_CLIENT_VER, true );
        }

        $data = $atts = shortcode_atts( array(), $atts, 'wpc_client_files_tree' );

        $data['no_files_colspan'] = 1;

        if ( $data['show_size'] )
            $data['no_files_colspan']++;
        if ( $data['show_author'] )
            $data['no_files_colspan']++;
        if ( $data['show_date'] )
            $data['no_files_colspan']++;
        if ( $data['show_last_download_date'] )
            $data['no_files_colspan']++;

        $data['files_form_id'] = rand( 0, 10000 );
        $data['home_url'] = get_home_url( get_current_blog_id() );

        if ( WPC()->permalinks ) {
            $data['view_url']       = WPC()->make_url( '/wpc_downloader/core/?wpc_action=view', $data['home_url'] );
            $data['download_url']   = WPC()->make_url( '/wpc_downloader/core/?wpc_action=download', $data['home_url'] );
        } else {
            $data['view_url']       = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'view' ), $data['home_url'] );
            $data['download_url']   = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'download' ), $data['home_url'] );
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $atts['category'] ) ? $atts['category'] : '',
            'with_subcategories'    => $atts['with_subcategories'],
            'file_type'             => $atts['file_type'],
        ) );

        $localize_data = $data;

        $data['tree_content'] = '';
        $data['files'] = $all_file_ids;

        $localize_arguments = array(
            'ajax_url'      => WPC()->get_ajax_url(),
            'data'          => $localize_data,
        );

        if ( ! empty( WPC()->current_plugin_page['client_id'] ) ) {
            $localize_arguments['client_id'] = WPC()->current_plugin_page['client_id'];
            $localize_arguments['_wpnonce'] = wp_create_nonce( WPC()->current_plugin_page['client_id'] . "client_security" );
        }

        wp_localize_script( 'wpc-files-tree-shortcode-js', 'wpc_file_shortcode' . $data['files_form_id'], $localize_arguments );
        wp_localize_script( 'wpc-files-tree-shortcode-mobile-js', 'wpc_file_shortcode' . $data['files_form_id'], $localize_arguments );

        //construct array of file categories for filter in template
        $data['filters'] = $this->get_filters_data( $data, $all_file_ids );

        return WPC()->get_template( 'files/tree/common.php', '', $data );
    }


    /**
    * Add scripts for files shortcode to front-end
    */
    public function add_scripts_to_front_files() {
        wp_enqueue_script( 'wp-util' );

        wp_enqueue_script( 'wpc-files-shortcode-js' );

        wp_enqueue_script( 'wpc-shutter-box-script' );
        wp_enqueue_style('wpc-shutter-box-style');
    }


    /**
     * Get all access categories IDs by client ID
     *
     * @param $user_id
     * @return array
     */
    private function get_client_file_categories( $user_id ) {
        $client_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $user_id );

        $group_file_categories = array();
        $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );
        foreach ( $client_groups_id as $group_id ) {
            //Files in categories with group access
            $group_file_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $group_id );
        }

        $file_categories = array_merge( $client_categories, $group_file_categories );

        //if nested assigns turn ON
        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
        if ( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
            $temp_cat_array = array();
            foreach ( $file_categories as $file_category ) {
                $children_categories = $this->get_category_children_ids( $file_category );
                $temp_cat_array = array_merge( $temp_cat_array, $children_categories );
            }

            $file_categories = array_merge( $file_categories, $temp_cat_array );
        }

        /*our_hook_
            hook_name: wpc_client_clients_file_categories
            hook_title: Client's File Categories
            hook_description: Hook runs when get client's file categories list.
            hook_type: filter
            hook_in: wp-client
            hook_location class.files.php
            hook_param: array $file_categories, int $user_id
            hook_since: 4.4.5.8
            */
        return apply_filters( 'wpc_client_clients_file_categories', $file_categories, $user_id );
    }


    public function ajax_files_shortcode_tree_pagination() {
        global $wpdb;

        if ( ! ini_get( 'safe_mode' ) )
            @set_time_limit(0);

        $user_id = $this->get_client_id();
        $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

        $data['sort_type'] = ( isset( $_POST['order_by'] ) && !empty( $_POST['order_by'] ) ) ? $_POST['order_by'] : $data['sort_type'];
        $data['sort'] = ( isset( $_POST['order'] ) && !empty( $_POST['order'] ) ) ? $_POST['order'] : $data['sort'];

        $data['search_cat'] = array( '0' );
        if ( isset( $data['category'] ) && '' != $data['category'] ) {
            $categories_list = explode( ',', $data['category'] );

            if ( ! in_array( 'all', $categories_list ) ) {
                $data['search_cat'] = $categories_list;
                if ( is_numeric( $categories_list[0] ) ) {
                    $res = $wpdb->get_col(
                        "SELECT fc.cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories fc
                        WHERE fc.cat_id IN( '" . implode( "','", $categories_list ) . "' )"
                    );

                    if ( empty( $res ) ) {
                        $res = $wpdb->get_col(
                            "SELECT fc.cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories fc
                            WHERE fc.cat_name IN( '" . implode( "','", $categories_list ) . "' )"
                        );
                    }
                } else {
                    $res = $wpdb->get_col(
                        "SELECT fc.cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories fc
                        WHERE fc.cat_name IN( '" . implode( "','", $categories_list ) . "' )"
                    );
                }

                if ( !empty( $res ) )
                    $data['search_cat'] = $res;
            }
        }

        $all_file_ids = $this->get_file_ids( array(
            'category'              => isset( $data['category'] ) ? $data['category'] : '',
            'with_subcategories'    => $data['with_subcategories'],
            'file_type'             => $data['file_type'],
            'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
            'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
            'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
        ) );
        $data['file_ids'] = $all_file_ids;

        if ( $data['show_empty_cats'] )
            $data['client_categories'] = $this->get_client_file_categories( $user_id );

        if ( isset( $data['category'] ) && '' != $data['category'] ) {
            $data['tree_content'] = '';

            if ( count( $data['search_cat'] ) == 1 ) {

                $data['tree_content'] .= $this->get_treetable_content( $data['search_cat'][0], $user_id, $data );

            } else {
                //remove child categories from main foreach for removing duplicate nested categories
                $parent_categories = array();
                foreach ( $data['search_cat'] as $category_id ) {
                    $child_categories = $wpdb->get_col(
                        "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE parent_id = '$category_id'"
                    );

                    if ( ! empty( $child_categories ) && count( array_intersect( $data['search_cat'], $child_categories ) ) > 0 )
                        $parent_categories = array_merge( $parent_categories, array_diff( $data['search_cat'], $child_categories ) );
                }

                if ( empty( $parent_categories ) )
                    $parent_categories = $data['search_cat'];
                else
                    $parent_categories = array_unique( $parent_categories );

                foreach ( $parent_categories as $category_id ) {
                    $category = $wpdb->get_row(
                        "SELECT fc.*
                        FROM {$wpdb->prefix}wpc_client_file_categories fc
                        WHERE fc.cat_id = $category_id",
                    ARRAY_A );

                    $ph_data = array(
                        'parent_value'    => '',
                        'subfolder_class' => '',
                        'category_id'     => $category['cat_id'],
                        'category_name'   => $category['cat_name'],
                    );

                    $children_html = $this->get_treetable_content( $category_id, $user_id, $data );

                    if ( $data['show_empty_cats'] ) {
                        if ( $children_html != '' ) {
                            $data['tree_content'] .= WPC()->get_template( 'files/tree/category_row.php', '', array_merge( $data, $ph_data ) );

                            if ( strpos( $children_html, 'class="wpc_treetable_file' ) !== false )
                                $data['tree_content'] .= $children_html;

                        } else {
                            if ( in_array( $category_id, $data['client_categories'] ) ) {
                                $data['tree_content'] .= WPC()->get_template( 'files/tree/category_row.php', '', array_merge( $data, $ph_data ) ) . $children_html;
                            }
                        }
                    } else {
                        if ( $children_html != '' ) {
                            $data['tree_content'] .= WPC()->get_template( 'files/tree/category_row.php', '', array_merge( $data, $ph_data ) );

                            if ( strpos( $children_html, 'class="wpc_treetable_file' ) !== false )
                                $data['tree_content'] .= $children_html;
                        }
                    }
                }
            }
        } else {
            $data['tree_content'] = $this->get_treetable_content( 0, $user_id, $data );
        }

        $data['filter_html'] = WPC()->get_template( 'files/filters/items.php', '', array(
            'filters' => $this->get_filters_data( $data, $all_file_ids )
        ) );


        if ( !empty( $data['tree_content'] ) ) {
            wp_die( json_encode( array(
                'status'        => true,
                'html'          => WPC()->get_template( 'files/tree/items.php', '', $data ),
                'filter_html'   => $data['filter_html']
            ) ) );
        } else {
            wp_die( json_encode( array(
                'status'        => true,
                'html'          => WPC()->get_template( 'files/tree/no_items.php', '', $data ),
                'filter_html'   => $data['filter_html']
            ) ) );
        }


    }


    public function ajax_files_shortcode_tree_get_files() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) )
            @set_time_limit(0);

        if ( ! empty( $_POST['category_id'] ) ) {
            $user_id = $this->get_client_id();
            $data = WPC()->decode_ajax_data( $_POST['shortcode_data'] );

            $sort_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
            if ( ! empty( $_POST['order_by'] ) ) {
                switch( $_POST['order_by'] ) {
                    case 'name':
                        $sort_by = 'f.title';
                        break;
                    case 'time':
                        $sort_by = 'f.time';
                        break;
                    case 'download_time':
                        $sort_by = 'f.last_download';
                        break;
                    case 'size':
                        $sort_by = 'f.size';
                        break;
                    case 'author':
                        $sort_by = 'u.user_login';
                        break;
                    default:
                        $sort_by = 'f.order_id = 0 OR ISNULL(f.order_id), f.order_id';
                        break;
                }
            } elseif ( ! empty( $data['sort_type'] ) ) {
                if ( $data['sort_type'] == 'time' ) {
                    $sort_by = 'f.time';
                } elseif ( $data['sort_type'] == 'name' ) {
                    $sort_by = 'f.title';
                }
            }

            $sort = 'asc';
            if ( ! empty( $_POST['order'] ) ) {
                $sort = ( $_POST['order'] == 'asc' || $_POST['order'] == 'desc' ) ? $_POST['order'] : 'asc';
            } elseif ( ! empty( $data['sort'] ) ) {
                $sort = ( $data['sort'] == 'asc' || $data['sort'] == 'desc' ) ? $data['sort'] : 'asc';
            }

            $order_string = "$sort_by $sort";

            if( empty( $_POST['post_parent'] ) ) {
                $data['with_subcategories'] = 0;
            }

            $all_file_ids = $this->get_file_ids( array(
                'category'              => $_POST['category_id'],
                'with_subcategories'    => $data['with_subcategories'],
                'file_type'             => $data['file_type'],
                'search'                => ! empty( $_POST['search'] ) ? $_POST['search'] : '',
                'filters'               => ! empty( $_POST['filters'] ) ? $_POST['filters'] : '',
                'filter_condition'      => ( isset( $data['filter_condition'] ) && $data['filter_condition'] == 'and' ) ? 'and' : 'or'
            ) );
            $where = " AND f.id IN('" . implode( "','", $all_file_ids ) . "')";

            $files = $wpdb->get_results(
                "SELECT max( fdl.download_date ) AS client_last_download,
                    f.*,
                    fc.cat_name AS category_name,
                    fc.cat_id AS category_id
                FROM {$wpdb->prefix}wpc_client_files f
                INNER JOIN {$wpdb->prefix}wpc_client_file_categories fc ON f.cat_id = fc.cat_id
                LEFT JOIN {$wpdb->prefix}wpc_client_files_download_log fdl ON f.id = fdl.file_id AND fdl.client_id = {$user_id}
                WHERE 1=1 {$where}
                GROUP BY f.id
                ORDER BY $order_string",
            ARRAY_A );

            $content = '';
            if ( ! empty( $files ) ) {
                foreach ( $files as $category_file ) {

                    $filedata = $this->build_filedata( $category_file, $data, $user_id, 'tree' );
                    $tags_html = '';
                    if( $data['show_tags'] ) {
                        foreach( $filedata['tags'] as $tag ) {
                            $tags_html .= '<span class="wpc_tag" data-term_id="' . $tag['term_id'] . '" title="' .  __( 'Filter by tag', WPC_CLIENT_TEXT_DOMAIN ) . ': ' . $tag['name'] . '">' . $tag['name'] . '</span>';
                        }
                    }

                    $ph_data = array (
                        'parent_cat_id'           =>  $_POST['category_id'],
                        'file_id'                 => $filedata['id'],
                        'file_title'              => $filedata['title'],
                        'file_icon'               => ( $data['show_thumbnails'] ) ? $filedata['icon'] : '',
                        'file_description'        => $filedata['description'],
                        'file_tags'               => $tags_html,
                        'file_size'               => $filedata['size'],
                        'file_author_id'          => $filedata['author_id'],
                        'file_author'             => $filedata['author'],
                        'file_date'               => $filedata['date'],
                        'file_time'               => $filedata['time'],
                        'file_last_download_date' => $filedata['last_download']['date'],
                        'file_last_download_time' => $filedata['last_download']['time'],
                        'file_view_url'           => ( $filedata['new_page'] ) ? $filedata['view_url'] : '',
                        'file_watch_video'        => ( $filedata['popup'] ) ? $filedata['view_url'] : '',
                        'file_url'                => $filedata['url'],
                        'file_delete_url'         => ( isset( $filedata['delete_url'] ) && !empty( $filedata['delete_url'] ) ) ? $filedata['delete_url'] : '',
                        'after_view_link'         => ( $filedata['new_page'] ) ? '&nbsp;|&nbsp;' : '',
                        'before_delete_link'      => ( isset( $filedata['delete_url'] ) && !empty( $filedata['delete_url'] ) ) ? '&nbsp;|&nbsp;' : '',
                        'before_details_link'     => ( $data['show_description'] && $filedata['description'] ) ? '&nbsp;|&nbsp;' : '',
                        'download_target_blank'   => ( $filedata['new_page'] ) ? ' targer="_blank" ' : '',
                        'delete_link_visibility'  => ( isset( $filedata['delete_url'] ) && !empty( $filedata['delete_url'] ) ) ? '' : 'style="display:none;"',
                        'details_link_visibility' => ( $data['show_description'] && $filedata['description'] ) ? '' : 'style="display:none;"',
                        'view_link_visibility'    => ( $filedata['new_page'] ) ? '' : 'style="display:none;"'
                    );

                    $content .= WPC()->get_template( 'files/tree/item_row.php', '', array_merge( $data, $ph_data ) );
                }
            }
            $content = do_shortcode( $content );
            $content = WPC()->replace_placeholders( $content, array( 'client_id' => $user_id ) );

            wp_die( json_encode( array(
                'status'    => true,
                'html'      => $content
            ) ) );
        } else {
            wp_die( json_encode( array(
                'status' => false,
                'message' => __( 'Invalid Data', WPC_CLIENT_TEXT_DOMAIN )
            ) ) );
        }
    }


    /**
     * Function for recursively get children categories on user side
     */
    function get_treetable_content( $parent_id, $user_id, $tree_data ) {
        global $wpdb;

        $tree_content = '';
        if ( ( $tree_data['search_cat'] != array( '0' ) && 1 == count( $tree_data['search_cat'] ) && $tree_data['with_subcategories'] ) ||
            ( $tree_data['search_cat'] != array( '0' ) && 1 < count( $tree_data['search_cat'] ) ) ||
            $tree_data['search_cat'] == array( '0' ) ) {

            $order_by = 'fc.cat_order';
            $order = 'ASC';

            if( isset( $tree_data['sort_type'] ) && $tree_data['sort_type'] == 'name' ) {
                $order_by = 'fc.cat_name';
                $order = $tree_data['sort'];
            }

            $order_string = $order_by . ' ' . $order;

            $parent_categories = $wpdb->get_results(
                "SELECT fc.*
                FROM {$wpdb->prefix}wpc_client_file_categories fc
                WHERE fc.parent_id = '$parent_id'
                ORDER BY $order_string",
            ARRAY_A );

            if ( ! empty( $parent_categories ) ) {

                foreach ( $parent_categories as $category ) {

                    $ph_data = array(
                        'parent_cat_id'      => ( ( is_array( $tree_data['search_cat'] ) && ! in_array( $parent_id, $tree_data['search_cat'] ) ) || 1 < count( $tree_data['search_cat'] ) ) ? $parent_id : '',
                        'subfolder_class'   => ( is_array( $tree_data['search_cat'] ) && ! in_array( $parent_id, $tree_data['search_cat'] ) ) ? 'wpc_subfolder' : '',
                        'category_id'       => $category['cat_id'],
                        'category_name'     => $category['cat_name'],
                    );
                    $children_html = $this->get_treetable_content( $category['cat_id'], $user_id, $tree_data );

                    if ( $tree_data['show_empty_cats'] ) {
                        if ( $children_html != '' ) {
                            $tree_content .= WPC()->get_template( 'files/tree/category_row.php', '', array_merge( $tree_data, $ph_data ) );
                            if ( strpos( $children_html, 'class="wpc_treetable_file' ) !== false )
                                $tree_content .= $children_html;
                        } else {
                            if ( in_array( $category['cat_id'], $tree_data['client_categories'] ) )
                                $tree_content .= WPC()->get_template( 'files/tree/category_row.php', '', array_merge( $tree_data, $ph_data ) ) . $children_html;
                        }
                    } else {
                        if ( $children_html != '' ) {
                            $tree_content .= WPC()->get_template( 'files/tree/category_row.php', '', array_merge( $tree_data, $ph_data ) );

                            if ( strpos( $children_html, 'class="wpc_treetable_file' ) !== false )
                                $tree_content .= $children_html;
                        }
                    }
                }
            }
        }

        $order_by = 'order_id = 0 OR ISNULL(order_id), order_id';
        $order = 'ASC';

        if ( isset( $tree_data['sort_type'] ) && ( $tree_data['sort_type'] == 'date' || $tree_data['sort_type'] == 'time' ) ) {
            $order_by = 'time';
            $order = $tree_data['sort'];
        } elseif ( isset( $tree_data['sort_type'] ) && $tree_data['sort_type'] == 'name' ) {
            $order_by = 'title';
            $order = $tree_data['sort'];
        } elseif ( isset( $tree_data['sort_type'] ) && $tree_data['sort_type'] == 'size' ) {
            $order_by = 'size';
            $order = $tree_data['sort'];
        } elseif ( isset( $tree_data['sort_type'] ) && $tree_data['sort_type'] == 'download_time' ) {
            $order_by = 'last_download';
            $order = $tree_data['sort'];
        }

        $order_string = $order_by . ' ' . $order;


        $filter = ( isset( $tree_data['filter'] ) && !empty( $tree_data['filter'] ) ) ? $tree_data['filter'] : '';
        $search = ( isset( $tree_data['search'] ) && !empty( $tree_data['search'] ) ) ? $tree_data['search'] : '';


        if ( ! empty( $tree_data['file_ids'] ) ) {
            if ( ! empty( $_POST['search'] ) ) {

                $category_files = $wpdb->get_results( $wpdb->prepare(
                    "SELECT f.*, u.user_login AS author
                    FROM {$wpdb->prefix}wpc_client_files f
                    LEFT JOIN {$wpdb->users} u ON u.ID = f.user_id
                    WHERE f.id IN('" . implode( "','", $tree_data['file_ids'] ) . "') AND
                        f.cat_id = %d
                        $search
                        $filter
                    ORDER BY $order_string" ,
                    $parent_id,
                    '%' . $_POST['search'] . '%',
                    '%' . $_POST['search'] . '%',
                    '%' . $_POST['search'] . '%'
                ), ARRAY_A );

            } else {

                $category_files = $wpdb->get_results( $wpdb->prepare(
                    "SELECT f.*, u.user_login AS author
                    FROM {$wpdb->prefix}wpc_client_files f
                    LEFT JOIN {$wpdb->users} u ON u.ID = f.user_id
                    WHERE f.id IN('" . implode( "','", $tree_data['file_ids'] ) . "') AND
                        f.cat_id = %d
                        $search
                        $filter
                    ORDER BY $order_string",
                    $parent_id
                ), ARRAY_A );

            }
        }

        if ( ! empty( $category_files ) ) {
            if ( $tree_data['search_cat'] != array( '0' ) && 1 == count( $tree_data['search_cat'] ) && in_array( $parent_id, $tree_data['search_cat'] ) ) {
                foreach ( $category_files as $category_file ) {

                    $filedata = $this->build_filedata( $category_file, $tree_data, $user_id, 'tree' );
                    $tags_html = '';
                    if ( $tree_data['show_tags'] ) {
                        foreach ( $filedata['tags'] as $tag ) {
                            $tags_html .= '<span class="wpc_tag" data-term_id="' . $tag['term_id'] . '" title="' . $tree_data['texts']['filter_by'] . ' ' . $tree_data['texts']['tag'] . ': ' . $tag['name'] . '">' . $tag['name'] . '</span>';
                        }
                    }

                    $ph_data = array (
                        'parent_cat_id'           => ( ( is_array( $tree_data['search_cat'] ) && !in_array( $parent_id, $tree_data['search_cat'] ) ) || 1 < count( $tree_data['search_cat'] ) ) ? $parent_id : '',
                        'file_id'                 => $filedata['id'],
                        'file_title'              => $filedata['title'],
                        'file_icon'               => ( $tree_data['show_thumbnails'] ) ? $filedata['icon'] : '',
                        'file_description'        => $filedata['description'],
                        'file_tags'               => $tags_html,
                        'file_size'               => $filedata['size'],
                        'file_author_id'          => $filedata['author_id'],
                        'file_author'             => $filedata['author'],
                        'file_date'               => $filedata['date'],
                        'file_time'               => $filedata['time'],
                        'file_last_download_date' => $filedata['last_download']['date'],
                        'file_last_download_time' => $filedata['last_download']['time'],
                        'file_view_url'           => ( $filedata['new_page'] ) ? $filedata['view_url'] : '',
                        'file_watch_video'        => ( $filedata['popup'] ) ? $filedata['view_url'] : '',
                        'file_url'                => $filedata['url'],
                        'file_delete_url'         => ( isset( $filedata['delete_url'] ) && !empty( $filedata['delete_url'] ) ) ? $filedata['delete_url'] : '',
                        'after_view_link'         => ( $filedata['new_page'] ) ? '&nbsp;|&nbsp;' : '',
                        'before_delete_link'      => ( isset( $filedata['delete_url'] ) && !empty( $filedata['delete_url'] ) ) ? '&nbsp;|&nbsp;' : '',
                        'before_details_link'     => ( $tree_data['show_description'] && $filedata['description'] ) ? '&nbsp;|&nbsp;' : '',
                        'download_target_blank'   => ( $filedata['new_page'] ) ? ' targer="_blank" ' : '',
                        'delete_link_visibility'  => ( isset( $filedata['delete_url'] ) && !empty( $filedata['delete_url'] ) ) ? '' : 'style="display:none;"',
                        'details_link_visibility' => ( $tree_data['show_description'] && $filedata['description'] ) ? '' : 'style="display:none;"',
                        'view_link_visibility'    => ( $filedata['new_page'] ) ? '' : 'style="display:none;"'
                    );

                    $tree_content .= WPC()->get_template( 'files/tree/item_row.php', '', array_merge( $tree_data, $ph_data ) );
                }
            } else {
                $tree_content .= '<tr data-tt-id="file%file_id%" ' . ( ( ( is_array( $tree_data['search_cat'] ) && !in_array( $parent_id, $tree_data['search_cat'] ) ) || 1 < count( $tree_data['search_cat'] ) ) ? 'data-tt-parent-id="category' . $parent_id . '"' : '') . ' class="wpc_treetable_file wpc_hidden_files' . $parent_id . '" valign="top"><td></td></tr>';
            }
        }

        return $tree_content;
    }


    /**
     * Function for recursively get category lists in selectboxes in users pages with disabled categories
     *
     * @param array $file_ids
     * @param int $parent_id
     * @param int $depth
     * @return array
     */
    function get_categories_tree_list( $file_ids = array(), $parent_id = 0, $depth = -1 ) {
        global $wpdb;

        if ( ! isset( $this->cache['categories_with_files'] ) ) {
            $this->cache['categories_with_files'] = $wpdb->get_col(
                "SELECT DISTINCT fc.cat_id
                FROM {$wpdb->prefix}wpc_client_file_categories fc
                LEFT JOIN {$wpdb->prefix}wpc_client_files f ON f.cat_id = fc.cat_id
                WHERE f.id IN( '" . implode( "','", $file_ids ) . "' )
                ORDER BY fc.cat_order ASC"
            );
        }

        $categories_with_files = $this->cache['categories_with_files'];

        $depth++;
        $categories_list = array();

        $depth_categories = $wpdb->get_results(
            "SELECT fc.cat_id,
                    fc.cat_name AS category_name,
                    fc.parent_id
            FROM {$wpdb->prefix}wpc_client_file_categories fc
            WHERE fc.parent_id='{$parent_id}'
            ORDER BY fc.cat_order ASC",
        ARRAY_A );

        if ( ! empty( $depth_categories ) ) {
            foreach ( $depth_categories as $category_data ) {
                if ( in_array( $category_data['cat_id'], $categories_with_files ) ) {

                    $categories_list[$category_data['cat_id']] = array(
                        'cat_id'        => $category_data['cat_id'],
                        'category_name' => $category_data['category_name'],
                        'parent_id'     => $category_data['parent_id'],
                        'depth'         => $depth,
                        'enable'        => true
                    );

                    $children_categories = $this->get_categories_tree_list( $file_ids, $category_data['cat_id'], $depth );

                    if ( ! empty( $children_categories ) )
                        $categories_list += $children_categories;

                } else {

                    $children_categories = $this->get_categories_tree_list( $file_ids, $category_data['cat_id'], $depth );

                    if ( ! empty( $children_categories ) ) {
                        $categories_list[$category_data['cat_id']] = array(
                            'cat_id'        => $category_data['cat_id'],
                            'category_name' => $category_data['category_name'],
                            'parent_id'     => $category_data['parent_id'],
                            'depth'         => $depth,
                            'enable'        => false
                        );
                        $categories_list += $children_categories;
                    }
                }
            }
        }

        return $categories_list;
    }


    /**
     * Function for getting array with
     * file categories and its depth for displaying information
     * in selectboxes:
     * Parent Category
     *   - Children Category 1
     *   - Children Category 2
     *
     * @param array $child_cats
     * @param int $depth
     * @param int $parent_id
     * @param bool|false $cat_array
     * @return array
     */
    function get_all_file_categories( $child_cats = array(), $depth = 0, $parent_id = 0, $cat_array = false ) {
        global $wpdb;

        if ( ! $cat_array ) {
            $all_categories = $wpdb->get_results(
                "SELECT *
                FROM {$wpdb->prefix}wpc_client_file_categories
                ORDER BY cat_id",
            ARRAY_A );

            $cat_array = array();
            foreach ( $all_categories as $category ) {
                if ( !empty( $cat_array[$category['cat_id']] ) ) {
                    $cat_array[$category['cat_id']] = array_merge( $cat_array[$category['cat_id']], $category );
                } else {
                    $cat_array[$category['cat_id']] = $category;
                }

                if ( !empty( $category['parent_id'] ) ) {
                    $cat_array[$category['parent_id']]['children'][] = $category['cat_id'];
                }
            }

            $for_foreach = array();
            foreach ( $cat_array as $cat_id=>$category ) {
                if ( isset( $category['parent_id'] ) && $category['parent_id'] == 0 ) {
                    $for_foreach[$cat_id]= $category;
                }
            }
        } else {
            $for_foreach = array();
            foreach ( $child_cats as $cat_id ) {
                $for_foreach[$cat_id] = $cat_array[$cat_id];
            }
        }

        @uasort( $for_foreach, function( $a, $b ) {
            return ( $a["cat_order"] < $b["cat_order"] ) ? -1 : ( $a["cat_order"] == $b["cat_order"] ? 0 : 1 );
        } );

        $categories = array();
        foreach ( $for_foreach as $cat_id=>$category ) {
            if ( $category['parent_id'] == $parent_id ) {

                $enable = true;

                $categories[$cat_id] = array(
                    'cat_id'        => $cat_id,
                    'category_name' => $category['cat_name'],
                    'parent_id'     => $category['parent_id'],
                    'depth'         => $depth,
                    'enable'        => $enable
                );

                if ( !empty( $category['children'] ) ) {
                    $categories += $this->get_all_file_categories( $category['children'], $depth + 1, $cat_id, $cat_array );
                }
            }
        }

        return $categories;
    }


    /**
     * @param $by
     * @param $value
     * @param array $include
     * @param array $exclude
     * @return array
     */
    function get_file_categories_by( $by, $value, $include = array(), $exclude = array() ) {
        global $wpdb;

        $all_categories = $this->get_all_file_categories();

        $hash_args = md5( serialize( array( $by, $value ) ) );
        if ( empty( $this->categories[$hash_args] ) ) {

            $categories = array();

            switch ( $by ) {
                case 'all' :
                    $categories = $all_categories;
                    break;
                case 'file_ids':
                    $categories = $this->get_categories_tree_list( $value );
                    break;
                case 'user_access':
                    $categories = $all_categories;

                    if ( is_admin() ) {
                        $where_manager = '';
                        if ( user_can( $value, 'wpc_manager' ) && ! user_can( $value, 'administrator' ) ) {

                            if ( ! user_can( $value, 'wpc_show_all_file_categories' ) ) {

                                $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $value, 'client' );
                                $manager_circles = WPC()->assigns()->get_assign_data_by_object( 'manager', $value, 'circle' );
                                foreach ( $manager_circles as $c_id ) {
                                    $manager_clients = array_merge( $manager_clients, WPC()->groups()->get_group_clients_id( $c_id ) );
                                }
                                $manager_clients = array_unique( $manager_clients );

                                foreach ( $manager_clients as $client_id ) {
                                    $manager_circles = array_merge( $manager_circles, WPC()->groups()->get_client_groups_id( $client_id ) );
                                }
                                $manager_circles = array_unique( $manager_circles );

                                $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $manager_clients );
                                $circle_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $manager_circles );
                                $client_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $manager_clients );
                                $circle_categories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $manager_circles );
                                $cc_categories = array_unique( array_merge( $client_categories, $circle_categories ) );
                                foreach ( $cc_categories as $cat_id ) {
                                    $cc_categories = array_merge( $cc_categories, $this->get_category_children_ids( $cat_id ) );
                                }
                                $cc_categories = array_unique( $cc_categories );

                                $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
                                if ( isset( $wpc_file_sharing['nesting_category_assign'] ) && 'yes' == $wpc_file_sharing['nesting_category_assign'] ) {
                                    $temp_cat_array = array();
                                    foreach ( $cc_categories as $file_category ) {
                                        $children_categories = $this->get_category_children_ids( $file_category );
                                        $temp_cat_array = array_merge( $temp_cat_array, $children_categories );
                                    }

                                    $cc_categories = array_merge( $cc_categories, $temp_cat_array );
                                }

                                $all_files = array_merge( $client_files, $circle_files );
                                $all_files = array_unique( $all_files );

                                $where = count( $cc_categories ) ? "f.cat_id IN ( '" . implode( "','", $cc_categories ) . "' ) OR " : '';

                                if ( user_can( $value, 'wpc_view_admin_managers_files' ) ) {
                                    $where_manager .= " AND (
                                        $where
                                        f.page_id = 0 OR
                                        f.id IN('" . implode( "','", $all_files ) . "') OR
                                        f.user_id IN('" . implode( "','", $manager_clients ) . "')
                                    )";
                                } else {
                                    $where_manager .= " AND (
                                        $where
                                        f.user_id = " . $value . " OR
                                        f.id IN('" . implode( "','", $all_files ) . "') OR
                                        f.user_id IN('" . implode( "','", $manager_clients ) . "')
                                    )";
                                }

                                $access_categories = $wpdb->get_col(
                                    "SELECT DISTINCT f.cat_id
                                    FROM {$wpdb->prefix}wpc_client_files f
                                    WHERE 1=1 {$where_manager}"
                                );
                            } else {
                                $access_categories = $wpdb->get_col(
                                    "SELECT DISTINCT cat_id
                                    FROM {$wpdb->prefix}wpc_client_file_categories"
                                );
                            }

                            $include = array_merge( $include, $access_categories );
                        }
                    } else {
                        $access_categories = $this->get_client_file_categories( $value );
                        $include = array_merge( $include, $access_categories );
                    }

                    break;
            }

            $this->categories[$hash_args] = $categories;
        } else {
            $categories = $this->categories[$hash_args];
        }

        $temp = array();
        if ( ! empty( $exclude ) ) {
            foreach ( $categories as $k=>$category_data ) {
                if ( ! empty( $category_data['cat_id'] ) && ! in_array( $category_data['cat_id'], $exclude ) )
                    $temp[$k] = $category_data;
            }
            $categories = $temp;
        }

        $temp = array();
        if ( ! empty( $include ) ) {
            foreach ( $categories as $k=>$category_data ) {
                if ( ! empty( $category_data['cat_id'] ) && in_array( $category_data['cat_id'], $include ) )
                    $temp[$k] = $category_data;
            }
            $categories = $temp;
        } elseif ( $by == 'user_access' && ( ! is_admin() || ( user_can( $value, 'wpc_manager' ) && ! user_can( $value, 'wpc_show_all_file_categories' ) && ! user_can( $value, 'administrator' ) ) ) ) {
            return array();
        }

        return $this->remove_extra_depth( $categories, $all_categories );
    }


    function get_available_parent( $category_parent_id, $categories, $all_categories ) {
        if ( $category_parent_id == 0 )
            return -1;

        if ( ! empty( $categories[$category_parent_id] ) ) {
            return $categories[$category_parent_id]['depth'];
        } else {
            return $this->get_available_parent( $all_categories[$category_parent_id]['parent_id'], $categories, $all_categories );
        }
    }


    /**
     * Operate with categories depth for remove and sort categories
     *
     * @param $categories
     * @param $all_categories
     * @return mixed
     */
    function remove_extra_depth( $categories, $all_categories ) {

        foreach ( $categories as $cat_id => $category ) {

            if ( $category['depth'] == 0 )
                continue;

            $categories[$cat_id]['depth'] = $this->get_available_parent( $category['parent_id'], $categories, $all_categories ) + 1;

        }

        return $categories;
    }


    /**
     * @param array $args
     * @param string $selected
     * @param bool $echo
     * @return string
     */
    public function render_category_list_items( $args = array(), $selected = '', $echo = true ) {

        $defaults = array(
            'by'        => 'user_access',
            'value'     => get_current_user_id(),
            'include'   => array(),
            'exclude'   => array(),
            'format'    => 'html',
        );
        $args = wp_parse_args( $args, $defaults );

        //use only include or only exclude arguments
        $args['exclude'] = empty( $args['include'] ) ? $args['exclude'] : array();

        $args_hash = md5( serialize( $args ) );
        if ( ! empty( $this->categories_html[ $args_hash ] ) ) {
            if ( $args['format'] == 'html' ) {
                if ( $echo ) {
                    echo $this->categories_html[ $args_hash ];
                    return '';
                } else {
                    return $this->categories_html[ $args_hash ];
                }
            } elseif ( $args['format'] == 'array' ) {
                return $this->categories_html[ $args_hash ];
            }
        }

        $categories = $this->get_file_categories_by( $args['by'], $args['value'], $args['include'], $args['exclude'] );

        if ( $args['format'] == 'html' ) {

            ob_start();

            foreach ( $categories as $cat_id=>$value ) { ?>
                <option value="<?php echo $cat_id ?>" <?php disabled( ! $value['enable'] ) ?> <?php selected( $selected == $cat_id ) ?> data-cat_name="<?php echo $value['category_name'] ?>">
                    <?php $tab_string = str_repeat( '&nbsp;', $value['depth'] );
                    echo ( ! empty( $tab_string ) ? $tab_string . '&mdash; ' : ' ' ) . $value['category_name']; ?>
                </option>
            <?php }

            $html = ob_get_clean();

            $this->categories_html[ $args_hash ] = $html;

            if ( $echo ) {
                echo $html;
                return '';
            } else {
                return $html;
            }
        } elseif ( $args['format'] == 'array' ) {

            $options = array();
            foreach ( $categories as $cat_id=>$value ) {
                $tab_string = str_repeat( '&nbsp;', $value['depth'] );
                $options[$cat_id] = array(
                    'cat_id'    => $cat_id,
                    'title'     => ( !empty( $tab_string ) ? $tab_string . '&mdash; ' : ' ' ) . $value['category_name'],
                    'disabled'  => ! $value['enable']
                );
            }

            $this->categories_html[ $args_hash ] = $options;

            return $options;
        }

        return '';
    }


    //build uploader category selectbox
    function build_uploader_category_selectbox( $client_id, $uploader_id, $atts ) {
        global $wpdb;

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
        $categories = array();
        $options_html = '';
        ob_start();

        if ( ! isset( $atts['category'] ) && ( ! isset( $wpc_file_sharing['allow_file_cats'] ) || 'yes' == $wpc_file_sharing['allow_file_cats'] ) ) {
            //'category' attr is empty and show categories selectbox
            if ( ! empty( $atts['show_categories'] ) ) {
                //new logic
                switch ( $atts['show_categories'] ) {
                    case 'all': {
                        $options_html = $this->render_category_list_items( array(
                            'by' => 'all'
                        ), '', false );

                        $categories = $this->get_all_file_categories();
                        break;
                    }
                    case 'assigned': {
                        $options_html = $this->render_category_list_items( array(
                            'by' => 'user_access',
                            'value' => get_current_user_id(),
                        ), '', false );
                        $categories = $this->get_client_file_categories( $client_id );

                        $categories = $wpdb->get_results(
                            "SELECT cat_id, cat_name AS category_name
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id IN('" . implode( "','", $categories ) . "')",
                        ARRAY_A );

                        break;
                    }
                    case 'custom': {
                        $atts['categories'] = ( isset( $atts['categories'] ) && !empty( $atts['categories'] ) ) ? explode( ',', $atts['categories'] ) : array();

                        $options_html = $this->render_category_list_items( array(
                            'by'        => 'all',
                            'include'   => $atts['categories']
                        ), '', false );

                        $categories = $wpdb->get_results(
                            "SELECT cat_id, cat_name AS category_name
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id IN('" . implode( "','", $atts['categories'] ) . "')",
                        ARRAY_A );

                        break;
                    }
                }

            } else {
                //old logic without 'show categories' attr
                $atts['categories'] = ( isset( $atts['categories'] ) && !empty( $atts['categories'] ) ) ? explode( ',', $atts['categories'] ) : array();

                if ( isset( $atts['categories'] ) && !empty( $atts['categories'] ) && !in_array( 'all', $atts['categories'] ) ) {

                    $options_html = $this->render_category_list_items( array(
                        'by'        => 'all',
                        'include'   => $atts['categories']
                    ), '', false );

                    $categories = $wpdb->get_results(
                        "SELECT cat_id, cat_name AS category_name
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id IN('" . implode( "','", $atts['categories'] ) . "')",
                    ARRAY_A );

                } else {
                    $categories = $this->get_all_file_categories();
                    $options_html = $this->render_category_list_items( array(
                        'by' => 'all'
                    ), '', false );
                }
            }

            if ( ! empty( $categories ) ) {
                if ( 1 == count( $categories ) ) {
                    $cat_id = array_values( $categories );
                    $cat_id = $cat_id[0]['cat_id']; ?>
                    <input type="hidden" name="file_cat_id" id="file_cat_id_<?php echo $uploader_id ?>" value="<?php echo $cat_id ?>"/>
                <?php } else { ?>
                    <div style="float:left;width:100%;margin:0 0 10px 0;padding:0;">
                        <label for="file_cat_id_<?php echo $uploader_id ?>"><?php _e( 'Select category', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <select name="file_cat_id" id="file_cat_id_<?php echo $uploader_id ?>">
                            <?php echo $options_html ?>
                        </select>
                    </div>
                <?php }
            } else {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_name = 'General' AND
                        parent_id='0'"
                ); ?>

                <input type="hidden" name="file_cat_id" id="file_cat_id_<?php echo $uploader_id ?>" value="<?php echo $cat_id ?>" />
            <?php }

        } else {

            //get category ID from shortcode attribute
            if ( ! empty( $atts['categories'] ) ) {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_id = '{$atts['categories']}'"
                );
            } elseif ( ! empty( $atts['category'] ) ) {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_id = '{$atts['category']}' OR cat_name = '{$atts['category']}'"
                );
            }

            //if there isn't category get General
            if ( empty( $cat_id ) ) {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_name = 'General' AND
                        parent_id='0'"
                );
            } ?>

            <input type="hidden" name="file_cat_id" id="file_cat_id_<?php echo $uploader_id ?>" value="<?php echo $cat_id ?>" />
        <?php }

        $selectbox = ob_get_contents();
        ob_end_clean();

        return $selectbox;
    }


    /**
     * Create/Edit new file Category
     **/
    function create_file_category( $args ) {
        global $wpdb;

        if ( '0' != $args['cat_id'] ) {
            //edit file category

            $old_path = $this->get_category_path( $args['cat_id'] );

            //update when edit category
            $wpdb->query( $wpdb->prepare(
                "UPDATE {$wpdb->prefix}wpc_client_file_categories
                    SET cat_name = '%s',
                        folder_name = '%s'
                    WHERE cat_id = %d",
                trim( $args['cat_name'] ),
                trim( $args['folder_name'] ),
                $args['cat_id']
            ) );

            $new_path = $this->get_category_path( $args['cat_id'] );

            //rename folder on FTP
            if( is_dir( $old_path ) ) {
                rename( $old_path, $new_path );
            }

        } else {
            //create new category

            //get order number for new category
            $cat_order = $wpdb->get_var( $wpdb->prepare(
                "SELECT
                    COUNT(cat_id)
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE parent_id=%d",
                $args['parent_id']
            ) );
            $cat_order++;
            //insert when add new category
            $wpdb->insert(
                "{$wpdb->prefix}wpc_client_file_categories",
                array(
                    'cat_name'      => trim( $args['cat_name'] ),
                    'folder_name'   => trim( $args['folder_name'] ),
                    'parent_id'     => $args['parent_id'],
                    'cat_order'     => $cat_order
                ),
                array( '%s', '%s', '%d', '%d' )
            );

            $category_id = $wpdb->insert_id;

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
                WPC()->assigns()->set_assigned_data( 'file_category', $category_id, 'client', $clients_array );

                //set Client Circle
                $circles_array = array();
                if ( isset( $args['cat_circles'] ) && !empty( $args['cat_circles'] ) )  {
                    if( $args['cat_circles'] == 'all' ) {
                        $circles_array = WPC()->groups()->get_group_ids();
                    } else {
                        $circles_array = explode( ',', $args['cat_circles'] );
                    }
                }
                WPC()->assigns()->set_assigned_data( 'file_category', $category_id, 'circle', $circles_array );
            }

            //create category folder
            $this->create_file_category_folder( $category_id, trim( $args['folder_name'] ) );

            $args['sync'] = false;

            /*our_hook_
                hook_name: wpc_client_file_category_created
                hook_title: Hook after file category created
                hook_description:
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $category_id, array $category_data
                hook_since: 3.8.1
            */
            do_action( 'wpc_client_file_category_created', $category_id, $args );

            return $category_id;
        }

        return '';
    }


    /**
     * Function creating forder for file category with
     * checking existence of folders on FTP server
     */
    function create_file_category_folder( $category_id, $folder_name ) {
        global $wpdb;

        //create category folder
        $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );

        $parent_category_ids = $this->get_category_parent_ids( $category_id );

        if( is_array( $parent_category_ids ) && 0 < count( $parent_category_ids ) ) {

            foreach( $parent_category_ids as $parent_category_id ) {

                $current_folder_name = $wpdb->get_var(
                    "SELECT folder_name
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id='$parent_category_id'"
                );

                $target_path .= $current_folder_name;

                if ( !is_dir( $target_path ) ) {
                    mkdir( $target_path, 0777 );
                }

                $target_path .= DIRECTORY_SEPARATOR;

            }
        }

        $target_path .= $folder_name;

        if ( !is_dir( $target_path ) ) {
            mkdir( $target_path, 0777 );
        }

    }


    /**
     * Function for getting filepath on
     * FTP server. returning string with filepath.
     */
    function get_file_path( $file, $thumbnail = false ) {

        $filepath = $this->get_category_path( $file['cat_id'] );

        if( $thumbnail ) {

            $temp = $filepath . DIRECTORY_SEPARATOR . 'thumbnails_' . $file['filename'];
            /*Fix for file's thumbnails with high register extension *.JPG for example */
            if( !file_exists( $temp ) ) {
                $pathinfo = pathinfo( $file['filename'] );
                $filepath .= DIRECTORY_SEPARATOR . 'thumbnails_' . $pathinfo['filename'] . '.' . strtolower( $pathinfo['extension'] );
            } else {
                $filepath = $temp;
            }

        } else {
            $filepath .= DIRECTORY_SEPARATOR . $file['filename'];
        }
        return $filepath;
    }


    /**
     * Function for getting category path on FTP
     */
    function get_category_path( $category_id ) {
        global $wpdb;

        $categorypath = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );

        $parent_category_ids = $this->get_category_parent_ids( $category_id );

        if( is_array( $parent_category_ids ) && 0 < count( $parent_category_ids ) ) {

            foreach( $parent_category_ids as $parent_category_id ) {

                $current_category_folder = $wpdb->get_var(
                    "SELECT folder_name
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id='$parent_category_id'"
                );

                $categorypath .= $current_category_folder . DIRECTORY_SEPARATOR;

            }

        }

        $folder_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT folder_name
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE cat_id=%d",
            $category_id
        ) );

        $categorypath .= $folder_name;

        return $categorypath;
    }



    /**
     * Delete File
     */
    function delete_file( $file_id ) {
        global $wpdb;

        $file = $wpdb->get_row( $wpdb->prepare(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_files
                WHERE id = %d",
            $file_id
        ), ARRAY_A );

        if( !$file['external'] ) {

            $filepath = $this->get_file_path( $file );
            //unlink( $filepath );

            if( file_exists( $filepath ) ) {
                unlink( $filepath );
            }

            $wpdb->query( $wpdb->prepare(
                "DELETE
                    FROM {$wpdb->prefix}wpc_client_files
                    WHERE id = %d",
                $file_id
            ) );

            WPC()->assigns()->delete_all_object_assigns( 'file', $file_id );

            wp_delete_object_term_relationships ( $file_id, 'wpc_file_tags' );

            $filedata_array = explode( ".", $file['name'] );
            if( 1 < count( $filedata_array ) ) {
                $ext = strtolower( $filedata_array[ count( $filedata_array ) - 1 ] );
            } else {
                $ext = '';
            }

            if( in_array( $ext, array( 'gif', 'jpg', 'jpeg', 'png' ) ) ) {
                $thumbnail_filepath = $this->get_file_path( $file, true );

                if( file_exists( $thumbnail_filepath ) ) {
                    unlink( $thumbnail_filepath );
                }
            }

        } else {
            $wpdb->query( $wpdb->prepare(
                "DELETE
                    FROM {$wpdb->prefix}wpc_client_files
                    WHERE id = %d",
                $file_id
            ) );

            WPC()->assigns()->delete_all_object_assigns( 'file', $file_id );

        }

        //clear files_download_log
        $wpdb->query( $wpdb->prepare(
            "DELETE
                FROM {$wpdb->prefix}wpc_client_files_download_log
                WHERE file_id = %d",
            $file_id
        ) );

        /*our_hook_
        hook_name: wpc_client_file_deleted
        hook_title: File Deleted
        hook_description: Hook runs when File is deleted.
        hook_type: action
        hook_in: wp-client
        hook_location class.common.php
        hook_param: int $file_id
        hook_since: 4.4.0
        */
        //action file deletes
        do_action( 'wpc_client_file_deleted', $file_id );

    }


    /**
     * Function for recursively create child categories
     * and files for current new folder(category) in database
     */
    function sync_create_category( $user_id, $parent_dir, $category_name, $parent_id = 0 ) {
        global $wpdb;

        //get new order for category
        $category_order = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(cat_id)
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE parent_id=%d",
            $parent_id
        ) );

        $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

        //get category name
        //$category_name = $file;

        //insert new category to database
        $wpdb->insert(
            "{$wpdb->prefix}wpc_client_file_categories",
            array(
                'cat_name'  => $category_name,
                'folder_name'  => $category_name,
                'parent_id' => $parent_id,
                'cat_order' => $category_order + 1
            )
        );

        $new_category_id = $wpdb->insert_id;

        $args = array(
            'sync'          => true,
            'cat_id'        => $new_category_id,
            'folder_name'   => $category_name,
            'parent_id'     => $parent_id,
            'cat_order'     => $category_order + 1,
            'cat_name'      => $category_name
        );
        /*our_hook_
            hook_name: wpc_client_file_category_created
            hook_title: Hook after file category created when synchronization running
            hook_description:
            hook_type: action
            hook_in: wp-client
            hook_location class.common.php
            hook_param: int $category_id, array $category_data
            hook_since: 4.1.6
        */
        do_action( 'wpc_client_file_category_created', $new_category_id, $args );


        $category_path = $parent_dir . DIRECTORY_SEPARATOR . $category_name;

        if( is_dir( $category_path ) ) {
            //scan folder
            $files = scandir( $category_path );

            foreach( $files as $file ) {
                if( $file != "." && $file != "..") {
                    //check path $dir is folder or is file
                    if( is_dir( $category_path . DIRECTORY_SEPARATOR . $file ) ) {

                        $this->sync_create_category( $user_id, $category_path, $file, $new_category_id );

                    } elseif( file_exists( $category_path . DIRECTORY_SEPARATOR . $file ) ) {

                        if( strpos( $file, 'thumbnails_' ) === 0 ) {
                            continue;
                        }

                        if( preg_match( '/\.part$/', $file ) ) {
                            continue;
                        }

                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_files",
                            array(
                                'filename'  => $file,
                                'user_id'   => $user_id,
                                'page_id'   => 0,
                                'time'      => time(),
                                'size'      => filesize( $category_path . DIRECTORY_SEPARATOR . $file ),
                                'name'      => $file,
                                'title'     => $file,
                                'cat_id'    => $new_category_id,
                                'external'  => '0'
                            )
                        );

                        $args = array(
                            'cat_id'    => $new_category_id,
                            'filename'  => $file
                        );

                        $file_type = explode( '.', $file );
                        $file_type = strtolower( end( $file_type ) );

                        if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
                            $this->create_image_thumbnail( $args );
                        }

                    }
                }
            }
        }
    }


    /**
     * Function for scanning FTP filesystem in
     * folder wpclient/_file_sharing
     */
    function scanning_ftp( $settings, $user_id, $dir, $parent_id = 0 ) {
        global $wpdb;

        $files = scandir( $dir );
        foreach( $files as $file ) {
            if( $file != "." && $file != ".." ) {
                //check path $dir is folder or is file
                if( is_dir( $dir . DIRECTORY_SEPARATOR . $file ) ) {

                    //get category
                    $category_in_database = $wpdb->get_var( $wpdb->prepare(
                        "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE folder_name = _utf8 '%s' COLLATE utf8_bin AND
                                parent_id=%d",
                        $file,
                        $parent_id
                    ) );

                    if( !( isset( $category_in_database ) && !empty( $category_in_database ) ) ) {
                        $this->sync_create_category( $user_id, $dir, $file, $parent_id );
                    } else {
                        $this->scanning_ftp( $settings, $user_id, $dir . DIRECTORY_SEPARATOR . $file, $category_in_database );
                    }

                } elseif( file_exists( $dir . DIRECTORY_SEPARATOR . $file ) ) {
                    if( $parent_id > 0 ) {
                        if( strpos( $file, 'thumbnails_' ) === 0 ) {
                            continue;
                        }

                        if( preg_match( '/\.part$/', $file ) ) {
                            continue;
                        }

                        $file_in_database = $wpdb->get_var( $wpdb->prepare(
                            "SELECT id
                                FROM {$wpdb->prefix}wpc_client_files
                                WHERE filename='%s' AND
                                    cat_id=%d",
                            $file,
                            $parent_id
                        ) );

                        if( !$file_in_database ) {

                            $wpdb->insert(
                                "{$wpdb->prefix}wpc_client_files",
                                array(
                                    'filename'  => $file,
                                    'user_id'   => $user_id,
                                    'page_id'   => 0,
                                    'time'      => time(),
                                    'size'      => filesize( $dir . DIRECTORY_SEPARATOR . $file ),
                                    'name'      => $file,
                                    'title'     => $file,
                                    'cat_id'    => $parent_id,
                                    'external'  => '0'
                                )
                            );

                            $file_id = $wpdb->insert_id;

                            $auto_assign_circles = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_files = 1 " );
                            WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $auto_assign_circles );


                            /*our_hook_
                                hook_name: wpc_client_sync_file_saved
                                hook_title: Sync File Saved
                                hook_description: Hook runs when File was inserted after FTP sync.
                                hook_type: action
                                hook_in: wp-client
                                hook_location class.common.php
                                hook_param: array $file
                                hook_since: 3.7.9.3
                            */
                            do_action( 'wpc_client_sync_file_saved', array(
                                'id'                => $file_id,
                                'filename'          => $file,
                                'user_id'           => $user_id,
                                'page_id'           => 0,
                                'time'              => time(),
                                'size'              => filesize( $dir . DIRECTORY_SEPARATOR . $file ),
                                'name'              => $file,
                                'title'             => $file,
                                'cat_id'            => $parent_id,
                                'external'          => '0',
                                'folder_path'       => $dir
                            ) );


                            $args = array(
                                'cat_id'    => $parent_id,
                                'filename'  => $file
                            );

                            $file_type = explode( '.', $file );
                            $file_type = strtolower( end( $file_type ) );

                            if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
                                $this->create_image_thumbnail( $args );
                            }


                            // Send notify to assigned client and staff
                            if( isset( $settings['sync_notification'] ) && 'yes' == $settings['sync_notification'] ) {

                                $send_client_ids = array();

                                $category_clients = WPC()->assigns()->get_assign_data_by_object( 'file_category', $parent_id, 'client' );
                                $send_client_ids = array_merge( $send_client_ids, $category_clients );

                                $category_circles = WPC()->assigns()->get_assign_data_by_object( 'file_category', $parent_id, 'circle' );
                                if( isset( $category_circles ) && !empty( $category_circles ) ) {

                                    foreach( $category_circles as $circle_id ) {

                                        $circle_clients = WPC()->groups()->get_group_clients_id( $circle_id );
                                        $send_client_ids = array_merge( $send_client_ids, $circle_clients );

                                    }

                                }

                                $send_client_ids = array_unique( $send_client_ids );


                                //send notify
                                if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {

                                    $category_name = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $parent_id ) );

                                    foreach( $send_client_ids as $send_client_id ) {
                                        $send_client_user = get_userdata( $send_client_id );

                                        if( '' != $send_client_id && false !== $send_client_user ) {

                                            $email_args = array(
                                                'client_id'     => $send_client_id,
                                                'file_name'     => $file,
                                                'file_category' => $category_name,
                                                'file_download_link' => $this->get_file_download_link($file_id)
                                            );

                                            $client = get_userdata( $send_client_id );
                                            if ( $client ) {
                                                $client_email = $client->get( 'user_email' );
                                                //send email to client
                                                WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff' );
                                            }


                                            //get client staff
                                            $args = array(
                                                'role'          => 'wpc_client_staff',
                                                'meta_key'      => 'parent_client_id',
                                                'meta_value'    => $send_client_id,
                                            );
                                            $staffs = get_users( $args );

                                            //send email to staff
                                            if ( is_array( $staffs ) && 0 < count( $staffs ) ) {
                                                foreach( $staffs as $staff ) {
                                                    $email_args = array(
                                                        'client_id'     => $staff->ID,
                                                        'file_name'     => $file,
                                                        'file_category' => $category_name,
                                                        'file_download_link' => $this->get_file_download_link($file_id)
                                                    );
                                                    //send email
                                                    WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff' );
                                                }
                                            }
                                        }
                                    }

                                }
                            }


                        }
                    }
                }
            }
        }

    }


    /**
     * Function for synchronize FTP filesystem in
     * folder "wpclient/_file_sharing" and Database
     */
    function synchronize_with_ftp() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $wpdb->query("SET SESSION SQL_BIG_SELECTS=1");

        $basedir = WPC()->get_upload_dir( 'wpclient/_file_sharing' );

        //all folders to lower register
        /*
        to delete
        $filesystem = scandir( $basedir );
         foreach( $filesystem as $element ) {
             if( $element != "." && $element != ".." ) {
                 //check path $dir is folder or is file
                 if( is_dir( "$basedir/$element" ) ) {
                     rename( "$basedir/$element", "$basedir/" . strtolower( $element ) );
                 }
             }
         }*/


        //------------------- STEP (-I)---------------------//
        //create General category if it was deleted
        $isset_general = $wpdb->get_var(
            "SELECT cat_id FROM
                {$wpdb->prefix}wpc_client_file_categories
                WHERE cat_name = _utf8 'General' COLLATE utf8_bin AND
                parent_id='0'"
        );

        if( !$isset_general ) {

            //get new order for category
            $category_order = $wpdb->get_var(
                "SELECT COUNT(cat_id)
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE parent_id='0'"
            );

            $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

            $wpdb->insert(
                "{$wpdb->prefix}wpc_client_file_categories",
                array(
                    'cat_name'      => 'General',
                    'folder_name'   => 'General',
                    'parent_id'     => '0',
                    'cat_order'     => $category_order + 1
                )
            );

            $category_path = $this->get_category_path( $wpdb->insert_id );
            if( !is_dir( $category_path ) ) {
                mkdir( $category_path, 0777 );
            }
        } else {
            $category_path = $this->get_category_path( $isset_general );
            if( !is_dir( $category_path ) ) {
                mkdir( $category_path, 0777 );
            }
        }

        //-------------------FIRST STEP (I)---------------------//

        //Deleting categories from database, which was deleted or cut from FTP (I.a)//

        //getting categories with external files
        $external_file_category_ids = $wpdb->get_col(
            "SELECT DISTINCT cat_id
                FROM {$wpdb->prefix}wpc_client_files
                WHERE external='0'"
        );

        $external_file_category_ids = ( isset( $external_file_category_ids ) && !empty( $external_file_category_ids ) ) ? $external_file_category_ids : array();

        //getting all categories exclude with external files
        $categories = $wpdb->get_results(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE cat_id NOT IN('" . implode( "','", $external_file_category_ids ) . "')",
            ARRAY_A );

        if( isset( $categories ) && !empty( $categories ) ) {
            foreach( $categories as $category ) {
                $category_path = $this->get_category_path( $category['cat_id'] );

                //if category is not general
                if( !is_dir( $category_path ) && 'general' != strtolower( $category['cat_name'] ) ) {
                    //delete category and it's children
                    $wpdb->query( $wpdb->prepare(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id=%d",
                        $category['cat_id']
                    ) );

                    WPC()->assigns()->delete_all_object_assigns( 'file_category', $category['cat_id'] );

                    //delete category files
                    $wpdb->query( $wpdb->prepare(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE cat_id=%d",
                        $category['cat_id']
                    ) );
                } elseif( !is_dir( $category_path ) && 'general' == strtolower( $category['cat_name'] ) ) {
                    //create general category if it is not exist
                    mkdir( $category_path, 0777 );
                }
            }
        }

        //Deleting files from database, which was deleted or cut from FTP (I.b)//
        $files = $wpdb->get_results(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_files
                WHERE size <> 0",
            ARRAY_A );

        if( isset( $files ) && !empty( $files ) ) {
            foreach( $files as $key=>$file ) {
                $file_path = $this->get_file_path( $file );

                if( !file_exists( $file_path ) ) {
                    //delete file
                    $wpdb->query( $wpdb->prepare(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE id=%d",
                        $file['id']
                    ) );

                    WPC()->assigns()->delete_all_object_assigns( 'file', $file['id'] );

                    $thumbnail_file_path = $this->get_file_path( $file, true );
                    //delete thumbnail for file if it exists
                    if( file_exists( $thumbnail_file_path ) ) {
                        unlink( $thumbnail_file_path );
                    }
                }

                unset( $files[$key] );
            }
        }


        //-------------------SECOND STEP (II)---------------------//
        //do scanning uploads DIR for detect added files
        $settings = WPC()->get_settings( 'file_sharing' );

        $user_id = 0;
        if( isset( $settings['manual_sync_author'] ) && 'current_user' == $settings['manual_sync_author'] && !( defined('DOING_CRON') && DOING_CRON ) && !( defined('DOING_AJAX') && DOING_AJAX ) ) {
            $user_id = get_current_user_id();
        }
        $this->scanning_ftp( $settings, $user_id, $basedir );


        //-------------------THIRD STEP (III)---------------------//
        //email notification
        //to do

    }



    /**
     * Function for recursively get children categories ids
     */
    function get_category_children_ids( $category_id ) {
        global $wpdb;

        $parent_categories = $wpdb->get_col(
            "SELECT cat_id
                FROM {$wpdb->prefix}wpc_client_file_categories fc
                WHERE fc.parent_id = '$category_id'"
        );
        $categories = array();

        if( isset( $parent_categories ) && !empty( $parent_categories ) ) {
            foreach( $parent_categories as $category ) {
                $categories[] = $category;
                $children_categories = $this->get_category_children_ids( $category );
                $categories = array_merge( $categories, $children_categories );
            }
        }
        return $categories;
    }



    /**
     * Function for getting all parents (all levels) of current category
     * with order from main parent to its children
     * renurning not accosiated array (1,5,7...)
     */
    function get_category_parent_ids( $category_id ) {
        global $wpdb;

        $category_parents = array();

        while( 0 != $category_id ) {
            $parent_id = $wpdb->get_var(
                "SELECT parent_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_id='$category_id'"
            );
            if( $parent_id != 0 ) {
                $category_parents[] = $parent_id;
            }
            $category_id = $parent_id;
        }

        return array_reverse( $category_parents );
    }



    /**
     * Function for getting array with
     * file categories orders
     * returning array [category_id] => caterory_order
     */
    function get_categories_order( $parent_id = 0 ) {
        global $wpdb;

        $old_orders = array();

        $categories = $wpdb->get_results(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE parent_id='$parent_id'",
            ARRAY_A );

        if( isset( $categories ) && !empty( $categories ) ) {

            foreach( $categories as $category ) {
                $old_orders[$category['cat_id']] = $category['parent_id'];
                $result = $this->get_categories_order( $category['cat_id'] );
                $old_orders += $result;
            }
        }

        return $old_orders;
    }



    /**
     * Old create file categories. Deprecated since 3.5.7.2
     *
     * @param $category_id
     * @param $category_name
     */
    function old_create_file_category_folder( $category_id, $category_name ) {
        global $wpdb;

        $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );
        $parent_category_ids = $this->get_category_parent_ids( $category_id );

        if( is_array( $parent_category_ids ) && 0 < count( $parent_category_ids ) ) {

            foreach( $parent_category_ids as $parent_category_id ) {

                $current_category_name = $wpdb->get_var(
                    "SELECT LOWER(cat_name)
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id='$parent_category_id'"
                );

                $target_path .= $current_category_name . '__' . $parent_category_id;

                if ( !is_dir( $target_path ) ) {
                    mkdir( $target_path, 0777 );
                }

                $target_path .= DIRECTORY_SEPARATOR;

            }
        }

        $target_path .= strtolower( $category_name ) . '__' . $category_id;

        if ( !is_dir( $target_path ) ) {
            mkdir( $target_path, 0777 );
        }

    }


    function get_fileicon( $file_type ) {

        //available filetype icons
        $default_ext_icons = array(
            'acc', 'ai', 'aif', 'app', 'atom', 'avi', 'bmp', 'cdr', 'css', 'doc', 'docx', 'eps', 'exe', 'fla','flv', 'gif', 'gzip', 'html',
            'indd', 'jpg', 'js', 'mov', 'mp3', 'mp4', 'm4v', 'otf', 'pdf','php', 'png', 'ppt', 'pptx', 'psd', 'rar', 'raw', 'rss', 'rtf', 'sql',
            'svg', 'swf', 'tar', 'tiff', 'ttf', 'txt', 'wav', 'wmv', 'xls', 'xlsx', 'xml', 'zip',
        );

        $upload_folder = wp_upload_dir();

        $default_icons_folder = WPC()->plugin_dir . 'images' . DIRECTORY_SEPARATOR . 'filetype_icons' . DIRECTORY_SEPARATOR;
        $default_icons_url = WPC()->plugin_url . 'images/filetype_icons/';

        $custom_icons_folder = WPC()->get_upload_dir( 'wpclient/_filetype_icons/', 'allow' );
        $custom_icons_url = $upload_folder['baseurl'] . '/wpclient/_filetype_icons/';

        $icon_path = '';

        if( in_array( $file_type, $default_ext_icons ) ) {

            if( file_exists( $custom_icons_folder . $file_type . '.png' ) ) {
                $icon_path = $custom_icons_url . $file_type . '.png';
            } elseif( file_exists( $default_icons_folder . $file_type . '.png' ) ) {
                $icon_path = $default_icons_url . $file_type . '.png';
            }

        } elseif( file_exists( $custom_icons_folder . $file_type . '.png' ) ) {

            $icon_path = $custom_icons_url . $file_type . '.png';

        } elseif( file_exists( $default_icons_folder . 'unknown.png' ) ) {

            $icon_path = $default_icons_url . 'unknown.png';

        }

        return $icon_path;
    }



    /**
     * Get all access ID (Clients, groiups) for file
     **/
    function get_file_access_id( $file_id, $for = 'clients_id' ) {
        global $wpdb;

        if ( !in_array( $for, array( 'clients_id', 'groups_id' ) ) )
            return array();

        $file_access      = $wpdb->get_var( $wpdb->prepare( "SELECT $for FROM {$wpdb->prefix}wpc_client_files WHERE id = %d",  $file_id ) );
        $file_access_id   = array();

        if ( '' != $file_access )
            $file_access_id = explode( ',', str_replace( '#', '', $file_access ) );

        return $file_access_id;
    }



    function file_sharing_checkbox( $data, $params ) {
        if( isset( $params['current_page'] ) && 'wpclients_files' == $params['current_page'] && isset( $params['send_ajax'] ) && 1 == $params['send_ajax'] ) {
            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

            $data = array_merge( $data, array(
                array(
                    'ref' => '.wpc_assign_popup_after_list',
                    'code' => '<div style="clear: both; height: 15px;float:left;width:100%;"><hr /></div>
                        <label>
                            <input type="checkbox" name="send_file_notification" class="send_file_notification" value="1" ' . checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, false ) . ' />
                            <span class="description" style="font-size: 10px;">' . sprintf( __( "Send email notification for new assign %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</span>
                        </label>
                        <br /><br />
                        <label>
                            <input type="checkbox" name="send_attach_file_user" class="send_attach_file_user" value="1" />
                            <span class="description" style="font-size: 10px;">' . sprintf( __( "Attach file to the email notification sent to %s and associated %s (size may be limited by email providers)", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) . '</span>
                        </label>
                        <br /><br />'
                )
            ) );
        } elseif ( isset( $params['current_page'] ) && 'wpclients_files' == $params['current_page'] && isset( $params['input_ref'] ) && ( 'bulk_assign_wpc_clients' == $params['input_ref'] || 'bulk_assign_wpc_circles' == $params['input_ref'] ) ) {
            $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

            $data = array_merge( $data, array(
                array(
                    'ref' => '.wpc_assign_popup_after_list',
                    'code' => '<div style="clear: both; height: 15px;float:left;width:100%;"><hr /></div>
                        <label>
                            <input type="checkbox" name="send_file_notification" class="send_file_notification" value="1" ' . checked( isset( $wpc_file_sharing['default_notify_checkbox'] ) && 'yes' == $wpc_file_sharing['default_notify_checkbox'], true, false ) . ' />
                            <span class="description" style="font-size: 10px;">' . sprintf( __( "Send email notification for new assign %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</span>
                        </label>
                        <br /><br />
                        <label>
                            <input type="checkbox" name="send_attach_file_user" class="send_attach_file_user" value="1" />
                            <span class="description" style="font-size: 10px;">' . sprintf( __( "Attach file to the email notification sent to %s and associated %s (size may be limited by email providers)", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['staff']['p'] ) . '</span>
                        </label>
                        <br /><br />'
                )
            ) );
        }

        return $data;
    }



    /**
     * Function for getting filepath on
     * FTP server. returning string with filepath.
     */
    function old_get_file_path( $file, $thumbnail = false ) {
        global $wpdb;

        $uploads    = wp_upload_dir();
        $uploads['basedir'] = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] );
        $filepath   = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'wpclient' . DIRECTORY_SEPARATOR . '_file_sharing' . DIRECTORY_SEPARATOR;

        $parent_category_ids = $this->get_category_parent_ids( $file['cat_id'] );
        $category_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT cat_name
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE cat_id=%d",
            $file['cat_id']
        ) );

        if( is_array( $parent_category_ids ) && 0 < count( $parent_category_ids ) ) {

            foreach( $parent_category_ids as $parent_category_id ) {

                $current_category_name = $wpdb->get_var(
                    "SELECT LOWER(cat_name)
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id='$parent_category_id'"
                );

                $filepath .= $current_category_name . '__' . $parent_category_id . DIRECTORY_SEPARATOR;

            }

        }

        $filepath .= strtolower( $category_name ) . '__' . $file['cat_id'] . DIRECTORY_SEPARATOR;

        if( $thumbnail ) {
            $filepath .= 'thumbnails_' . $file['filename'];
        } else {
            $filepath .= $file['filename'];
        }

        return $filepath;
    }



    /**
     * Function for scanning FTP filesystem in
     * folder wpclient/_file_sharing
     */
    function old_scanning_ftp( $dir, $parent_id = 0 ) {
        global $wpdb;

        $files = scandir( $dir );
        foreach( $files as $file ) {
            if( $file != "." && $file != ".." && !( is_dir( "$dir" . DIRECTORY_SEPARATOR . "$file" ) && $file == '_uberloader_temp' ) ) {
                //check path $dir is folder or is file
                if( is_dir( "$dir" . DIRECTORY_SEPARATOR . "$file" ) ) {

                    //get category name if folder name is $category_name . '__' . $category_id
                    $category_name = explode( '__', $file );
                    $category_name = ( is_array( $category_name ) ) ? $category_name[0] : '';

                    //if category name is not empty then add category to filesystem
                    if( $category_name != '' ) {

                        $category_in_database = $wpdb->get_var( $wpdb->prepare(
                            "SELECT cat_id
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_name='%s' AND parent_id=%d",
                            $category_name,
                            $parent_id
                        ) );

                        if( !( isset( $category_in_database ) && !empty( $category_in_database ) ) ) {
                            $this->old_sync_create_category( $dir, $file, $parent_id );
                        } else {
                            $this->old_scanning_ftp( "$dir" . DIRECTORY_SEPARATOR . "$file", $category_in_database );
                        }
                    }
                } elseif( file_exists( "$dir" . DIRECTORY_SEPARATOR . "$file" ) ) {
                    if( strpos( $file, 'thumbnails_' ) === 0 ) {
                        continue;
                    }

                    if( preg_match( '/\.part$/', $file ) ) {
                        continue;
                    }

                    $file_in_database = $wpdb->get_var( $wpdb->prepare(
                        "SELECT id
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE filename='%s' AND cat_id=%d",
                        $file,
                        $parent_id
                    ) );

                    if( !$file_in_database ) {

                        $new_name = basename( rand( 0000,9999 ) . $file );
                        rename( "$dir" . DIRECTORY_SEPARATOR . "$file", "$dir" . DIRECTORY_SEPARATOR . "$new_name" );

                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_files",
                            array(
                                'filename'  => $new_name,
                                'user_id'   => get_current_user_id(),
                                'page_id'   => 0,
                                'time'      => time(),
                                'size'      => filesize( "$dir" . DIRECTORY_SEPARATOR . "$new_name" ),
                                'name'      => $file,
                                'title'     => $file,
                                'cat_id'    => $parent_id
                            )
                        );

                        $args = array(
                            'cat_id'    => $parent_id,
                            'filename'  => $new_name
                        );

                        $file_type = explode( '.', $file );
                        $file_type = strtolower( end( $file_type ) );

                        if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {

                            $this->old_create_image_thumbnail( $args );

                        }
                    }
                }
            }
        }

    }


    /**
     * Function for recursively create child categories
     * and files for current new folder(category) in database
     */
    function old_sync_create_category( $dir, $file, $parent_id = 0 ) {
        global $wpdb;

        //get new order for category
        $category_order = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(cat_id)
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE parent_id=%d",
            $parent_id
        ) );

        $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

        //get category name if folder name is $category_name . '__' . $category_id
        $category_name = explode( '__', $file );
        $category_name = ( is_array( $category_name ) ) ? $category_name[0] : '';

        //insert new ccategory to database
        $wpdb->insert(
            "{$wpdb->prefix}wpc_client_file_categories",
            array(
                'cat_name'  => $category_name,
                'parent_id' => $parent_id,
                'cat_order' => $category_order + 1
            )
        );

        $new_category_id = $wpdb->insert_id;

        //rename folder on FTP
        $new_name = $category_name . '__' . $new_category_id;

        if( is_dir( "$dir" . DIRECTORY_SEPARATOR . "$file" ) ) {
            if( rename( "$dir" . DIRECTORY_SEPARATOR . "$file", "$dir" . DIRECTORY_SEPARATOR . "$new_name" ) ) {
                //scan folder
                $files = scandir( "$dir" . DIRECTORY_SEPARATOR . "$new_name" );

                foreach( $files as $file ) {
                    if( $file != "." && $file != "..") {
                        //check path $dir is folder or is file
                        if( is_dir( "$dir" . DIRECTORY_SEPARATOR . "$new_name" . DIRECTORY_SEPARATOR . "$file" ) ) {

                            //get category name if folder name is $category_name . '__' . $category_id
                            $category_name = explode( '__', $file );
                            $category_name = ( is_array( $category_name ) ) ? $category_name[0] : '';

                            //if category name is not empty then add category to filesystem
                            if( $category_name != '' ) {

                                $this->old_sync_create_category( "$dir" . DIRECTORY_SEPARATOR . "$new_name", $file, $new_category_id );

                            }
                        } elseif( file_exists( "$dir" . DIRECTORY_SEPARATOR . "$new_name" . DIRECTORY_SEPARATOR . "$file" ) ) {

                            if( strpos( $file, 'thumbnails_' ) === 0 ) {
                                continue;
                            }

                            if( preg_match( '/\.part$/', $file ) ) {
                                continue;
                            }

                            $new_filename = basename( rand( 0000,9999 ) . $file );
                            rename( "$dir" . DIRECTORY_SEPARATOR . "$new_name" . DIRECTORY_SEPARATOR . "$file", "$dir" . DIRECTORY_SEPARATOR . "$new_name" . DIRECTORY_SEPARATOR . "$new_filename" );

                            $wpdb->insert(
                                "{$wpdb->prefix}wpc_client_files",
                                array(
                                    'filename'  => $new_filename,
                                    'user_id'   => get_current_user_id(),
                                    'page_id'   => 0,
                                    'time'      => time(),
                                    'size'      => filesize( "$dir" . DIRECTORY_SEPARATOR . "$new_name" . DIRECTORY_SEPARATOR . "$new_filename" ),
                                    'name'      => $file,
                                    'title'     => $file,
                                    'cat_id'    => $new_category_id
                                )
                            );

                            $args = array(
                                'cat_id'    => $new_category_id,
                                'filename'  => $new_filename
                            );

                            $file_type = explode( '.', $file );
                            $file_type = strtolower( end( $file_type ) );

                            if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {

                                $this->old_create_image_thumbnail( $args );

                            }

                        }
                    }
                }
            }
        }
    }



    /**
     * Function for creating image thumbnails
     */
    function old_create_image_thumbnail( $file, $settings = '' ) {

        $filepath = $this->old_get_file_path( $file );
        $thumbnail_filepath = $this->old_get_file_path( $file, true );

        if( !file_exists( $thumbnail_filepath ) && file_exists( $filepath ) ) {

            if( isset( $settings['wp_thumbnail'] ) && 'yes' == $settings['wp_thumbnail'] ) {

                $thumbnail_width = get_option( 'thumbnail_size_w' );
                $thumbnail_height = get_option( 'thumbnail_size_h' );
                $thumbnail_crop = get_option( 'thumbnail_crop' );

            } elseif( isset( $settings['wp_thumbnail'] ) && 'no' == $settings['wp_thumbnail'] ) {

                $thumbnail_width = $settings['thumbnail_size_w'];
                $thumbnail_height = $settings['thumbnail_size_h'];
                $thumbnail_crop = $settings['thumbnail_crop'];

                $thumbnail_width = ( isset( $thumbnail_width ) && !empty( $thumbnail_width ) ) ? $thumbnail_width : get_option( 'thumbnail_size_w' );
                $thumbnail_height = ( isset( $thumbnail_height ) && !empty( $thumbnail_height ) ) ? $thumbnail_height : get_option( 'thumbnail_size_h' );
                $thumbnail_crop = ( isset( $thumbnail_crop ) && !empty( $thumbnail_crop ) ) ? true : get_option( 'thumbnail_crop' );

            } else {

                $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

                if( !isset( $wpc_file_sharing['wp_thumbnail'] ) || ( isset( $wpc_file_sharing['wp_thumbnail'] ) && $wpc_file_sharing['wp_thumbnail'] == 'yes' ) ) {
                    $thumbnail_width = get_option( 'thumbnail_size_w' );
                    $thumbnail_height = get_option( 'thumbnail_size_h' );
                    $thumbnail_crop = get_option( 'thumbnail_crop' );

                } else {
                    $thumbnail_width = $wpc_file_sharing['thumbnail_size_w'];
                    $thumbnail_height = $wpc_file_sharing['thumbnail_size_h'];
                    $thumbnail_crop = $wpc_file_sharing['thumbnail_crop'];

                    $thumbnail_width = ( isset( $thumbnail_width ) && !empty( $thumbnail_width ) ) ? $thumbnail_width : get_option( 'thumbnail_size_w' );
                    $thumbnail_height = ( isset( $thumbnail_height ) && !empty( $thumbnail_height ) ) ? $thumbnail_height : get_option( 'thumbnail_size_h' );
                    $thumbnail_crop = ( isset( $thumbnail_crop ) && !empty( $thumbnail_crop ) ) ? true : get_option( 'thumbnail_crop' );

                }

            }

            $thumbnail_width = ( isset( $thumbnail_width ) && !empty( $thumbnail_width ) ) ? $thumbnail_width : 100;
            $thumbnail_height = ( isset( $thumbnail_height ) && !empty( $thumbnail_height ) ) ? $thumbnail_height : 100;
            $thumbnail_crop = ( isset( $thumbnail_crop ) && !empty( $thumbnail_crop ) ) ? true : false;

            $image = wp_get_image_editor( $filepath );
            if ( !is_wp_error( $image ) ) {
                $image->resize( $thumbnail_width, $thumbnail_height, $thumbnail_crop );
                $image->save( $thumbnail_filepath );
            }
        }

    }




    /**
     * BLOCK
     * Functions for old updating file sharing system
     *
     */
    /**
     * Function for synchronize FTP filesystem in
     * folder "wpclient/_file_sharing" and Database
     */
    function old_synchronize_with_ftp() {
        global $wpdb;

        if ( !ini_get( 'safe_mode' ) ) {
            @set_time_limit(0);
        }

        $uploads    = wp_upload_dir();
        $uploads['basedir'] = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] );

        $basedir    = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'wpclient' . DIRECTORY_SEPARATOR . '_file_sharing';

        //-------------------FIRST STEP (I)---------------------//

        //Deleting categories from database, which was deleted or cut from FTP (I.a)//
        $categories = $wpdb->get_results(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_file_categories",
            ARRAY_A );

        if( isset( $categories ) && !empty( $categories ) ) {
            foreach( $categories as $category ) {
                $category_path = $this->old_get_category_path( $category['cat_id'] );
                if( !is_dir( $category_path ) && 'general' != strtolower( $category['cat_name'] ) ) {
                    //delete category and it's children
                    $wpdb->query( $wpdb->prepare(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id=%d",
                        $category['cat_id']
                    ) );

                    WPC()->assigns()->delete_all_object_assigns( 'file_category', $category['cat_id'] );

                    //delete category files
                    $wpdb->query( $wpdb->prepare(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE cat_id=%d",
                        $category['cat_id']
                    ) );
                } elseif( !is_dir( $category_path ) && 'general' == strtolower( $category['cat_name'] ) ) {
                    mkdir( $category_path, 0777 );
                }
            }
        }

        //Deleting files from database, which was deleted or cut from FTP (I.b)//
        $files = $wpdb->get_results(
            "SELECT *
                FROM {$wpdb->prefix}wpc_client_files",
            ARRAY_A );

        if( isset( $files ) && !empty( $files ) ) {
            foreach( $files as $file ) {
                $file_path = $this->old_get_file_path( $file );
                $thumbnail_file_path = $this->old_get_file_path( $file, true );

                if( !file_exists( $file_path ) ) {
                    //delete file
                    $wpdb->query( $wpdb->prepare(
                        "DELETE
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE id=%d",
                        $file['id']
                    ) );

                    WPC()->assigns()->delete_all_object_assigns( 'file', $file['id'] );

                    //delete thumbnail for file if it exists
                    if( file_exists( $thumbnail_file_path ) ) {
                        unlink( $thumbnail_file_path );
                    }
                }
            }
        }

        //-------------------SECOND STEP (II)---------------------//
        //do scanning uploads DIR for detect added files
        $this->old_scanning_ftp( $basedir );
    }


    /**
     * Function for getting category path on FTP
     */
    function old_get_category_path( $category_id ) {
        global $wpdb;

        $uploads    = wp_upload_dir();
        $uploads['basedir'] = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] );
        $categorypath   = $uploads['basedir'] . DIRECTORY_SEPARATOR . 'wpclient' . DIRECTORY_SEPARATOR . '_file_sharing' . DIRECTORY_SEPARATOR;

        $parent_category_ids = $this->get_category_parent_ids( $category_id );
        $category_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT cat_name
                FROM {$wpdb->prefix}wpc_client_file_categories
                WHERE cat_id=%d",
            $category_id
        ) );

        if( is_array( $parent_category_ids ) && 0 < count( $parent_category_ids ) ) {

            foreach( $parent_category_ids as $parent_category_id ) {

                $current_category_name = $wpdb->get_var(
                    "SELECT LOWER(cat_name)
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id='$parent_category_id'"
                );

                $categorypath .= $current_category_name . '__' . $parent_category_id . DIRECTORY_SEPARATOR;

            }

        }
        $categorypath .= strtolower( $category_name ) . '__' . $category_id;

        return $categorypath;
    }


    function generate_google_view( $file_id, $ext, $plugin = 'core' ) {
        $user_id = get_current_user_id();
        $hash = md5( $file_id . NONCE_SALT . $user_id  . NONCE_SALT . date('Y-m-d') );

        if ( is_multisite() ) {
            $home_url = get_home_url( get_current_blog_id() );
        } else {
            $home_url = get_home_url();
        }

        if( WPC()->permalinks ) {
            $url = WPC()->make_url( '/wpc_downloader/' . $plugin . '/' . $hash . '_' . $file_id . '_' . $user_id . '/' . $ext, $home_url );
        } else {
            $url = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => $plugin, 'wpc_google_hash' => $hash . '_' . $file_id . '_' . $user_id, 'ext' => $ext ), $home_url );
        }
        ?>
        <html>
        <head>
            <style type="text/css">
                body {
                    margin:   0;
                    padding:   0;
                    overflow: hidden;
                    position: relative;
                }

                #iframe {
                    position: relative;
                    top:      0;
                    left:     0;
                    bottom:   0;
                    right:    0;
                    margin:   0;
                    padding:  0;
                    width:    100%;
                    height:   100%;
                }
            </style>
        </head>

        <body>
        <iframe id="iframe" allowfullscreen="allowfullscreen" src="//docs.google.com/viewer?url=<?php echo urlencode( $url ); ?>&hl=<?php echo get_locale(); ?>&embedded=true" frameborder="0"></iframe>
        </body>
        </html>
        <?php
        exit;
    }


    function wpc_cron_synchronization() {
        $this->synchronize_with_ftp();
        exit;
    }


    /**
     * Get download link of file for email temlates
     *
     * @param int $file_id
     * @param string $for
     * @return string
     */
    function get_file_download_link( $file_id, $for = 'for_user' ) {
        if ( 'for_admin' == $for )
            $redirect_to_download =  get_admin_url() . 'admin.php?wpc_action=download&nonce=' . wp_create_nonce( get_current_user_id() . AUTH_KEY . $file_id ) . '&id='  . $file_id;
        else {

            if ( is_multisite() ) {
                $home_url = get_home_url( get_current_blog_id() );
            } else {
                $home_url = get_home_url();
            }

            if( WPC()->permalinks ) {
                $redirect_to_download = WPC()->make_url( '/wpc_downloader/core/?wpc_action=download&id=' . $file_id, $home_url );
            } else {
                $redirect_to_download = add_query_arg( array( 'wpc_page' => 'wpc_downloader', 'wpc_page_value' => 'core', 'wpc_action' => 'download', 'id' => $file_id ), $home_url );
            }
        }

        $login_url = ( '' != WPC()->get_slug( 'login_page_id' ) ) ? WPC()->get_slug( 'login_page_id' ) : wp_login_url();
        $download_link = add_query_arg( array( 'wpc_to_redirect' => urlencode( $redirect_to_download ) ), $login_url );
        return $download_link;

    }


    function download_files_zip( $file_arr, $notifications = true ) {
        global $wpdb;

        $user_id = WPC()->checking_page_access();

        if ( false === $user_id || 0 == $user_id ) {
            return '';
        }

        if( isset( $file_arr ) && !empty( $file_arr ) ) {
            $settings_file_sharing = WPC()->get_settings( 'file_sharing' );
            $zip_name              = ( isset( $settings_file_sharing['bulk_download_zip'] ) && ! empty( $settings_file_sharing['bulk_download_zip'] ) ) ? $settings_file_sharing['bulk_download_zip'] : 'files';

            $orig_zip_name = time() . '_' . uniqid() . '_bulk_download';

            // clear old temporary directories
            $parent_dir         = WPC()->get_upload_dir( 'wpclient/_temp_bulk_download/' );
            $old_temp_dir_names = array_diff( scandir( $parent_dir ), array( '.', '..' ) );
            foreach ( $old_temp_dir_names as $old_temp_dir_name ) {
                $old_temp_dir = $parent_dir . DIRECTORY_SEPARATOR . $old_temp_dir_name;
                if ( is_dir( $old_temp_dir ) && ( time() - filemtime( $old_temp_dir ) > 60 ) ) {
                    $old_temp_file_names = array_diff( scandir( $old_temp_dir ), array( '.', '..' ) );
                    foreach ( $old_temp_file_names as $old_temp_file_name ) {
                        $old_temp_file = $old_temp_dir . DIRECTORY_SEPARATOR . $old_temp_file_name;
                        if ( is_file( $old_temp_file ) ) {
                            unlink( $old_temp_file );
                        }
                    }
                    rmdir( $old_temp_dir );
                }
            }

            $file_temp_dir = WPC()->get_upload_dir( 'wpclient/_temp_bulk_download/' . $orig_zip_name . '/' );

            $files = $wpdb->get_results( "SELECT *
                      FROM {$wpdb->prefix}wpc_client_files f
                      WHERE f.id IN('" . implode( "','", $file_arr ) . "')",
                ARRAY_A );

            $items_array = array();
            if ( isset( $files ) && ! empty( $files ) ) {
                foreach ( $files as $file ) {
                    if ( ! $file['external'] ) {
                        $target_path = WPC()->files()->get_file_path( $file );
                        if ( file_exists( $target_path ) ) {
                            if ( file_exists( $file_temp_dir . $file['name'] ) ) {
                                $ii        = 1;
                                $file_type = explode( '.', $file['name'] );
                                $file_type = strtolower( end( $file_type ) );

                                $file_name = preg_replace( "/\." . $file_type . "$/is", '', $file['name'] );

                                while ( file_exists( $file_temp_dir . $file_name . '(' . $ii . ').' . $file_type ) ) {
                                    $ii ++;
                                }

                                $temp_name = $file_temp_dir . $file_name . '(' . $ii . ').' . $file_type;
                            } else {
                                $temp_name = $file_temp_dir . $file['name'];
                            }

                            $customRead = apply_filters( 'wp_client_decrypted_file_copy_tmp_dir', false, $target_path, $temp_name );

                            if ( $customRead !== false )
                                continue;

                            if ( copy( $target_path, $temp_name ) ) {
                                $items_array[ $file['id'] ] = $temp_name;
                            }
                        }
                    }
                }
            }

            if ( count( $items_array ) > 0 ) {
                if ( ! ini_get( 'safe_mode' ) ) {
                    @set_time_limit( 0 );
                }

                $archive = WPC()->create_archive( $file_temp_dir, $orig_zip_name .'.zip', $file_temp_dir );

                if ( $archive && file_exists( $file_temp_dir . $orig_zip_name .'.zip' ) ) {
                    foreach ( $files as $file ) {
                        if ( isset( $items_array[ $file['id'] ] ) ) {
                            unlink( $items_array[ $file['id'] ] );

                            $wpdb->update(
                                "{$wpdb->prefix}wpc_client_files",
                                array(
                                    'last_download' => time()
                                ),
                                array(
                                    'id' => $file['id']
                                )
                            );

                            //update download_log
                            $wpdb->insert(
                                "{$wpdb->prefix}wpc_client_files_download_log",
                                array(
                                    'file_id'       => $file['id'],
                                    'client_id'     => $user_id,
                                    'download_date' => time()
                                )
                            );

                            if( $notifications ) {
                                //email to admins
                                $args = array(
                                    'role'      => 'wpc_admin',
                                    'fields'    => array( 'user_email' )
                                );
                                $admin_emails = get_users( $args );
                                $emails_array = array();
                                if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                                    foreach( $admin_emails as $admin_email ) {
                                        $emails_array[] = $admin_email->user_email;
                                    }
                                }

                                $emails_array[] = get_option( 'admin_email' );

                                $args = array(
                                    'client_id' => $user_id,
                                    'file_name' => $file['name']
                                );
                                foreach( $emails_array as $to_email ) {
                                    WPC()->mail( 'client_downloaded_file', $to_email, $args, 'client_downloaded_file' );
                                }


                                //send message to client manager
                                //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $user_id );
                                $manager_ids = WPC()->members()->get_client_managers( $user_id );

                                if( is_array( $manager_ids ) && count( $manager_ids ) ) {
                                    foreach( $manager_ids as $manager_id ) {
                                        if ( 0 < $manager_id ) {
                                            $manager = get_userdata( $manager_id );
                                            if ( $manager ) {
                                                $manager_email = $manager->get( 'user_email' );
                                                //send email
                                                WPC()->mail( 'client_downloaded_file', $manager_email, $args, 'client_downloaded_file' );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    header( "Pragma: no-cache" );
                    header( "Expires: 0" );
                    header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
                    header( "Robots: none" );
                    header( "Content-Description: File Transfer" );
                    header( "Content-Transfer-Encoding: binary" );
                    header( 'Content-Type: application/octet-stream' );
                    header( "Content-length: " . filesize( $file_temp_dir . $orig_zip_name . '.zip' ) );
                    header( 'Content-disposition: attachment; filename="' . $zip_name . '.zip"' );

                    $levels = ob_get_level();
                    for ( $i = 0; $i < $levels; $i ++ ) {
                        @ob_end_clean();
                    }

                    WPC()->readfile_chunked( $file_temp_dir . $orig_zip_name . '.zip' );
                }

                if ( file_exists( $file_temp_dir . $orig_zip_name . '.zip' ) ) {
                    unlink( $file_temp_dir . $orig_zip_name . '.zip' );
                }
            }

            if ( is_dir( $file_temp_dir ) ) {
                rmdir( $file_temp_dir );
            }

            exit();
        }
    }

    /**
     * Function for creating image thumbnails
     *
     */
    function create_image_thumbnail( $file, $settings = '' ) {

        //todo: add description
        if ( ! apply_filters( 'wp_client_file_sharing_generate_thumbnails', true ) ) {
            return '';
        }

        $filepath = $this->get_file_path( $file );
        $thumbnail_filepath = $this->get_file_path( $file, true );

        if( !file_exists( $thumbnail_filepath ) && file_exists( $filepath ) ) {

            if( isset( $settings['wp_thumbnail'] ) && 'yes' == $settings['wp_thumbnail'] ) {
                $thumbnail_width = get_option( 'thumbnail_size_w' );
                $thumbnail_height = get_option( 'thumbnail_size_h' );
                $thumbnail_crop = get_option( 'thumbnail_crop' );

            } elseif( isset( $settings['wp_thumbnail'] ) && 'no' == $settings['wp_thumbnail'] ) {
                $thumbnail_width = ( !empty( $settings['thumbnail_size_w'] ) ) ? $settings['thumbnail_size_w'] : get_option( 'thumbnail_size_w' );
                $thumbnail_height = ( !empty( $settings['thumbnail_size_h'] ) ) ? $settings['thumbnail_size_h'] : get_option( 'thumbnail_size_h' );
                $thumbnail_crop = ( !empty( $settings['thumbnail_crop'] ) ) ? true : get_option( 'thumbnail_crop' );

            } else {

                $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

                if( !isset( $wpc_file_sharing['wp_thumbnail'] ) || 'yes' == $wpc_file_sharing['wp_thumbnail'] ) {
                    $thumbnail_width = get_option( 'thumbnail_size_w' );
                    $thumbnail_height = get_option( 'thumbnail_size_h' );
                    $thumbnail_crop = get_option( 'thumbnail_crop' );

                } else {
                    $thumbnail_width = ( !empty( $wpc_file_sharing['thumbnail_size_w'] ) ) ? $wpc_file_sharing['thumbnail_size_w'] : get_option( 'thumbnail_size_w' );
                    $thumbnail_height = ( !empty( $wpc_file_sharing['thumbnail_size_h'] ) ) ? $wpc_file_sharing['thumbnail_size_h'] : get_option( 'thumbnail_size_h' );
                    $thumbnail_crop = ( !empty( $wpc_file_sharing['thumbnail_crop'] ) && 'yes' == $wpc_file_sharing['thumbnail_crop'] ) ? true : get_option( 'thumbnail_crop' );

                }

            }

            $thumbnail_width = ( !empty( $thumbnail_width ) ) ? $thumbnail_width : 100;
            $thumbnail_height = ( !empty( $thumbnail_height ) ) ? $thumbnail_height : 100;

            $image = wp_get_image_editor( $filepath );
            if ( !is_wp_error( $image ) ) {
                $image->resize( $thumbnail_width, $thumbnail_height, $thumbnail_crop );
                $image->save( $thumbnail_filepath );
            }

        }
    }



    /**
     * Add access to file
     **/
    function save_file_access() {
        global $wpdb;

        $file_id            = $_POST['assign_id'];
        $file_access_id     = ( isset( $_POST['file_access_id'] ) ) ? $_POST['file_access_id'] : array();
        $field              = ( isset( $_POST['access_field'] ) && in_array( $_POST['access_field'] , array( 'clients_id', 'groups_id' ) ) ) ? $_POST['access_field'] : '';

        //Add clients to the Client Circle
        if ( '' != $field ) {

            //update clients permissions for page
            if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                //updating from manager
                $checked_clients    = array();
                $unchecked_clients  = array();
                $file_access_id     = array();


                if( isset( $_POST['file_access_id'] ) )
                    $checked_clients = $_POST['file_access_id'];


                $clients_id = $this->get_file_access_id( $file_id, 'clients_id' );

                $clients = WPC()->members()->get_all_clients_manager();
                //checked\unchecked clients
                if ( is_array( $clients ) && 0 < count( $clients ) )
                    foreach ( $clients as $client ) {
                        if ( in_array( $client->ID, $checked_clients ) ) {
                            $clients_id[] = $client->ID;
                        } else {
                            $unchecked_clients[] = $client->ID;
                        }
                    }

                //remove duplicate
                $clients_id = array_unique( $clients_id );

                //unchecked clients
                foreach ( $clients_id as $client_id ) {
                    if ( !in_array( $client_id, $unchecked_clients ) ) {
                        $file_access_id[] = $client_id;
                    }
                }
            }


            $str_access_id = '';

            if ( is_array( $file_access_id ) && 0 < count( $file_access_id ) ) {
                foreach ( $file_access_id as $access_id ) {
                    $str_access_id .= '#' . $access_id . ',';
                }
            }

            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_files SET $field = '%s' WHERE id = %d",  $str_access_id, $file_id ) );

            $msg = '';
            if ( 'clients_id' == $field )
                $msg = __( 'Clients are assigned!', WPC_CLIENT_TEXT_DOMAIN );
            elseif( 'groups_id' == $field )
                $msg = __( 'Client Circles are assigned!', WPC_CLIENT_TEXT_DOMAIN );

            WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'updated' => 'true', 'dmsg' => urlencode( $msg ) ), 'admin.php' ) );

        }

        WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'er_as2' ), 'admin.php' ) );
    }



    /**
     * Function for recursively delete
     * all files and folders in current folder
     */
    function recursive_delete_files( $dir ) {
        if( is_dir( $dir ) ) {
            $files = scandir( $dir );
            foreach( $files as $file ) {
                if( $file != "." && $file != "..") {
                    $this->recursive_delete_files( "$dir/$file" );
                }
            }
            rmdir( $dir );
            return true;
        } elseif( file_exists( $dir ) ) {
            unlink( $dir );
            return true;
        }
        return false;
    }


    /**
     * Delete File Category
     **/
    function delete_file_category( $cat_id ) {
        global $wpdb;

        //reassing files and folders(only if delete category)
        if ( 0 < $cat_id ) {

            $category_folder = $this->get_category_path( $cat_id );

            $this->recursive_delete_files( $category_folder );

            //get category parent_id
            $parent_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT parent_id FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_id = %d",
                $cat_id
            ) );
            $parent_id = ( isset( $parent_id ) && !empty( $parent_id ) ) ? $parent_id : 0;

            //delete category
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_id = %d",
                $cat_id
            ) );

            WPC()->assigns()->delete_all_object_assigns( 'file_category', $cat_id );

            //Update category order
            $cat_ids = $wpdb->get_results(
                "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE parent_id='{$parent_id}'
                    ORDER BY cat_order",
                ARRAY_A );

            if ( is_array( $cat_ids ) && 0 < count( $cat_ids ) ) {
                $i = 0;
                foreach( $cat_ids as $cat ) {
                    $i++;
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}wpc_client_file_categories
                            SET cat_order = %d
                            WHERE cat_id = %d",
                        $i,
                        $cat['cat_id']
                    ) );
                }
            }

            //delete files
            $files = $wpdb->get_col( $wpdb->prepare(
                "SELECT id
                    FROM {$wpdb->prefix}wpc_client_files
                    WHERE cat_id=%d",
                $cat_id
            ) );

            if( isset( $files ) && !empty( $files ) ) {
                foreach( $files as $file_id ) {
                    $this->delete_file( $file_id );
                }
            }

            $children_categories = $wpdb->get_col( $wpdb->prepare(
                "SELECT cat_id FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE parent_id = %d",
                $cat_id
            ) );

            if( isset( $children_categories ) && !empty( $children_categories ) ) {
                foreach( $children_categories as $children_category ) {
                    $this->delete_file_category( $children_category );
                }
            }

        }

    }


    /**
     * Reassign files from one Category to another
     * @param int $cat_id - Category ID where files now
     * @param int $reassign_cat_id - Category ID to move files
     **/
    function reassign_files_from_category( $old_cat_id, $reassign_cat_id, $with_folders = false, $file_id = 0 ) {
        global $wpdb;

        //reassing files and folders(only if delete category)
        if ( 0 < $old_cat_id && 0 < $reassign_cat_id ) {
            if( $file_id == 0 ) {

                //for files
                $wpdb->query( $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}wpc_client_files
                        SET cat_id = %d
                        WHERE cat_id = %d",
                    $reassign_cat_id,
                    $old_cat_id
                ) );

                //if reassign with folders
                if( $with_folders ) {
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}wpc_client_file_categories
                            SET parent_id = %d
                            WHERE parent_id = %d",
                        $reassign_cat_id,
                        $old_cat_id
                    ) );
                }

                $old_category_folder = $this->get_category_path( $old_cat_id );
                $new_category_folder = $this->get_category_path( $reassign_cat_id );

                if( $handle = opendir( $old_category_folder ) ) {
                    while( false !== ( $entry = readdir( $handle ) ) ) {

                        if( in_array( $entry, array( ".", ".." ) ) )
                            continue;

                        if( !$with_folders && is_dir( $old_category_folder . '/' . $entry ) ) {
                            continue;
                        }

                        if( file_exists( $old_category_folder . '/' . $entry ) || is_dir( $old_category_folder . '/' . $entry ) ) {
                            rename( $old_category_folder . '/' . $entry, $new_category_folder . '/' . $entry );
                        }

                    }
                    closedir( $handle );
                }

            } else {
                //for current selected file
                $file = $wpdb->get_row( $wpdb->prepare(
                    "SELECT *
                        FROM {$wpdb->prefix}wpc_client_files
                        WHERE id = %d",
                    $file_id
                ), ARRAY_A );

                if( isset( $file ) && !empty( $file ) ) {

                    if( !$file['external'] ) {
                        $ext = explode( '.', $file['filename'] );
                        $ext = strtolower( end( $ext ) );

                        $old_path = $this->get_file_path( $file );

                        $old_thumbnail_path = '';
                        if( in_array( $ext, array( 'jpg', 'jpeg', 'gif', 'png' ) ) ) {
                            $old_thumbnail_path = $this->get_file_path( $file, true );
                        }
                    }

                    $wpdb->query( $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}wpc_client_files
                            SET cat_id = %d
                            WHERE cat_id = %d AND
                                id = %d",
                        $reassign_cat_id,
                        $old_cat_id,
                        $file_id
                    ) );

                    $file = $wpdb->get_row( $wpdb->prepare(
                        "SELECT *
                            FROM {$wpdb->prefix}wpc_client_files
                            WHERE id = %d",
                        $file_id
                    ), ARRAY_A );

                    if( !$file['external'] ) {

                        $new_path = $this->get_file_path( $file );

                        if( file_exists( $old_path ) ) {
                            rename( $old_path, $new_path );
                        }

                        if( in_array( $ext, array( 'jpg', 'jpeg', 'gif', 'png' ) ) ) {
                            $new_thumbnail_path = $this->get_file_path( $file, true );

                            if( file_exists( $old_thumbnail_path ) ) {
                                rename( $old_thumbnail_path, $new_thumbnail_path );
                            }
                        }

                    }

                }
            }

        }
    }


    /**
     * Add new file (admin) /REGULAR UPLOADER
     */
    function admin_upload_file() {
        global $wpdb;

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        //Check file size
        if ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) {
            if ( isset( $_FILES['file']['size'] ) && '' != $_FILES['file']['size'] ) {
                $size = round( $_FILES['file']['size'] / 1024 );
                if ( $size > $wpc_file_sharing['file_size_limit'] ) {
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'm' ), 'admin.php' ) );
                }
            }
        }

        //add new category from admin side
        if ( isset( $_POST['file_category_new'] ) && !empty( $_POST['file_category_new'] ) && ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) ) {
            $category_name = $_POST['file_category_new'];

            if( preg_match( "/[\/\:\*\?\"\<\>\\\|\%\$]/", $category_name ) ) {
                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'cerr' ), 'admin.php' ) );
            }

            //checking that category not exist with other ID
            $category_exist_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_file_categories
                    WHERE cat_name = _utf8 '%s' COLLATE utf8_bin AND
                        parent_id='0'",
                $category_name
            ) );

            if ( $category_exist_id ) {
                $cat_id = $category_exist_id;
            } else {
                //create new category
                $args = array(
                    'cat_id'      => '0',
                    'cat_name'    => trim( $category_name ),
                    'folder_name' => trim( $category_name ),
                    'parent_id'   => '0',
                    'cat_clients' => '',
                    'cat_circles' => '',
                );

                //checking that category with folder_name not exist with other ID
                $result = $wpdb->get_row( $wpdb->prepare(
                    "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE folder_name = _utf8 '%s' COLLATE utf8_bin AND
                            parent_id = '0'",
                    $args['folder_name']
                ), ARRAY_A );

                //if new category exist with other ID
                if( isset( $result ) && !empty( $result ) ) {
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'fne' ), 'admin.php' ) );
                }

                $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );
                if( is_dir( $target_path . $args['folder_name'] ) ) {
                    WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'fe' ), 'admin.php' ) );
                }

                $cat_id = $this->create_file_category( $args );
            }

        } else {
            //use exist category from admin or client (Regular uploader) side
            if ( isset( $_POST['file_cat_id'] ) && 0 < $_POST['file_cat_id'] ) {
                $cat_id = $_POST['file_cat_id'];
                $category_name = $wpdb->get_var( $wpdb->prepare(
                    "SELECT folder_name
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_id=%d",
                    $cat_id
                ) );
            } else {
                $cat_id = $wpdb->get_var(
                    "SELECT cat_id
                        FROM {$wpdb->prefix}wpc_client_file_categories
                        WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                );

                if( !$cat_id ) {
                    //get new order for category
                    $category_order = $wpdb->get_var(
                        "SELECT COUNT(cat_id)
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE parent_id='0'"
                    );

                    $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                    $wpdb->insert(
                        "{$wpdb->prefix}wpc_client_file_categories",
                        array(
                            'cat_name'      => 'General',
                            'folder_name'   => 'General',
                            'parent_id'     => '0',
                            'cat_order'     => $category_order + 1
                        )
                    );

                    $cat_id = $wpdb->insert_id;
                }

                $category_name = 'General';
            }
        }

        if ( isset( $_FILES['file']['name'] ) && '' != $_FILES['file']['name'] ) {

            //create folders for file destination if it was not created
            $this->create_file_category_folder( $cat_id, trim( $category_name ) );

            //Upload file
            $owner_id       = ( 0 < get_current_user_id() ) ? get_current_user_id() : 0;
            $orig_name      = $_FILES['file']['name'];
            $new_name       = basename( rand( 0000,9999 ) . $orig_name );

            $args = array(
                'cat_id'    => $cat_id,
                'filename'  => $new_name
            );
            $filepath    = $this->get_file_path( $args );

            $title          = ( isset( $_POST['file_title'] ) && '' != $_POST['file_title'] ) ? $_POST['file_title'] : $orig_name;
            $description    = ( isset( $_POST['file_description'] ) && '' != $_POST['file_description'] ) ? $_POST['file_description'] : '';

            if ( move_uploaded_file( $_FILES['file']['tmp_name'], $filepath ) ) {

                $file_type = explode( '.', $orig_name );
                $file_type = strtolower( end( $file_type ) );

                //create thumbnail if file is image
                if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {

                    $this->create_image_thumbnail( $args );

                }

                //insert new file
                $wpdb->insert(
                    "{$wpdb->prefix}wpc_client_files",
                    array(
                        'user_id'             => $owner_id,
                        'page_id'             => 0,
                        'time'                => time(),
                        'size'                => $_FILES['file']['size'],
                        'filename'            => $new_name,
                        'name'                => $orig_name,
                        'title'               => $title,
                        'description'         => $description,
                        'cat_id'              => $cat_id,
                        'last_download'       => '',
                        'external'            => '0'
                    ),
                    array( '%d','%d','%d','%d','%s','%s','%s','%s','%d','%s' )
                );

                $file_id = $wpdb->insert_id;

                if( isset( $_REQUEST['file_tags'] ) && is_string( $_REQUEST['file_tags'] ) && isset( $file_id ) && !empty( $file_id ) ) {
                    $file_tags = preg_replace( '/^\[|\]$/', '', stripcslashes( $_REQUEST['file_tags'] ) ) ;
                    $file_tags = explode( ",", $file_tags ) ;
                    foreach ( $file_tags as $key => $tag ) {
                        $temp_tag = preg_replace( '/^\"|\"$/', '', $tag );
                        $file_tags[ $key ] = stripcslashes( $temp_tag ) ;
                    }
                    wp_set_object_terms( $file_id, $file_tags, 'wpc_file_tags' );

                }

                $redirect = add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'up' ), 'admin.php' );
            } else {
                WPC()->redirect( add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'er' ), 'admin.php' ) );
            }
        } elseif( isset( $_POST['ftp_selected_files'] ) && '' != $_POST['ftp_selected_files'] ) {
            //assign file
            if( is_array( $_POST['ftp_selected_files'] ) && 0 < count( $_POST['ftp_selected_files'] ) ) {
                $file_ids = array();
                $target_paths = array();
                foreach( $_POST['ftp_selected_files'] as $selected_file ) {
                    $target_path = WPC()->get_upload_dir( 'wpclient/_file_sharing/' );

                    if ( !file_exists( $target_path . $selected_file ) ) {
                        continue;
                    }

                    //create folders for file destination if it was not created
                    $this->create_file_category_folder( $cat_id, trim( $category_name ) );

                    $owner_id       = ( 0 < get_current_user_id() ) ? get_current_user_id() : 0;

                    $orig_name      = $selected_file;
                    $new_name       = basename( rand( 0000,9999 ) . $orig_name );

                    $args = array(
                        'cat_id'    => $cat_id,
                        'filename'  => $new_name
                    );
                    $filepath    = $this->get_file_path( $args );

                    $title          = ( isset( $_POST['file_title'] ) && '' != $_POST['file_title'] ) ? $_POST['file_title'] : $selected_file;
                    $description    = ( isset( $_POST['file_description'] ) && '' != $_POST['file_description'] ) ? $_POST['file_description'] : '';

                    if( rename( $target_path . $orig_name, $filepath ) ) {

                        $file_type = explode( '.', $orig_name );
                        $file_type = strtolower( end( $file_type ) );

                        if( in_array( $file_type, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {

                            $this->create_image_thumbnail( $args );

                        }

                        $wpdb->insert(
                            "{$wpdb->prefix}wpc_client_files",
                            array(
                                'user_id'             => $owner_id,
                                'page_id'             => 0,
                                'time'                => time(),
                                'size'                => filesize( $filepath ),
                                'filename'            => $new_name,
                                'name'                => $orig_name,
                                'title'               => $title,
                                'description'         => $description,
                                'cat_id'              => $cat_id,
                                'last_download'       => '',
                                'external'            => '0'
                            ),
                            array( '%d','%d','%d','%d','%s','%s','%s','%s','%d','%s' )
                        );

                        $file_ids[] = $wpdb->insert_id;
                        $target_paths[] = $filepath;
                    }
                }
                $redirect = add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'as' ), 'admin.php' );
            }
        } else {
            $owner_id       = ( 0 < get_current_user_id() ) ? get_current_user_id() : 0;
            //Add file URL
            $file_url       = $_POST['file_url'];
            $protect_url    = isset( $_POST['file_protect_url'] ) ? 1 : 0;
            $file_name      = $_POST['file_name'];
            $title          = ( isset( $_POST['file_title2'] ) && '' != $_POST['file_title2'] ) ? $_POST['file_title2'] : $file_name;
            $description    = ( isset( $_POST['file_description2'] ) && '' != $_POST['file_description2'] ) ? $_POST['file_description2'] : '';


            $headers = get_headers( $file_url, 1 );

            $size = !empty( $headers['Content-Length'] ) ? $headers['Content-Length'] : 0;


            $wpdb->insert(
                "{$wpdb->prefix}wpc_client_files",
                array(
                    'user_id'             => $owner_id,
                    'page_id'             => 0,
                    'time'                => time(),
                    'size'                => $size,
                    'filename'            => $file_url,
                    'name'                => $file_name,
                    'title'               => $title,
                    'description'         => $description,
                    'cat_id'              => $cat_id,
                    'protect_url'         => $protect_url,
                    'last_download'       => '',
                    'external'            => '1'
                ),
                array( '%d','%d','%d','%s','%s','%s','%s','%s','%d','%s','%s' )
            );

            $file_id = $wpdb->insert_id;

            $redirect = add_query_arg( array( 'page' => 'wpclients_content', 'tab' => 'files', 'msg' => 'ad' ), 'admin.php' );
        }

        //set clients
        $clients_array = array();
        if ( isset( $_POST['wpc_clients'] ) && !empty( $_POST['wpc_clients'] ) )  {
            $clients_array = explode( ',', $_POST['wpc_clients'] );
        }
        //set Client Circle
        $circles_array = array();
        if ( isset( $_POST['wpc_circles'] ) && !empty( $_POST['wpc_circles'] ) )  {
            $circles_array = explode( ',', $_POST['wpc_circles'] );
        }

        $auto_assign_circles = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_files = 1 " ) ;

        $circles_array = array_merge( $circles_array, $auto_assign_circles ) ;
        $circles_array = array_unique( $circles_array ) ;

        if( isset( $file_ids ) && is_array( $file_ids ) && 0 < count( $file_ids ) ) {
            foreach( $file_ids as $file_id ) {
                //assigned process
                if( isset( $file_id ) && !empty( $file_id ) ) {
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', $clients_array );
                    WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $circles_array );
                }
            }
        } else {
            //assigned process
            if( isset( $file_id ) && !empty( $file_id ) ) {

                WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', $clients_array );
                WPC()->assigns()->set_assigned_data( 'file', $file_id, 'circle', $circles_array );
            }
        }


        /*our_hook_
            hook_name: wp_client_admin_uploaded_file
            hook_title: Admin Uploads File
            hook_description: Hook runs when admin uploads file.
            hook_type: action
            hook_in: wp-client
            hook_location class.admin.php
            hook_param: int $file_id
            hook_since: 4.4.0
        */
        do_action( 'wp_client_admin_uploaded_file', $file_id );


        // Send notify to assigned client and staff
        if ( isset( $_POST['new_file_notify'] ) && '1' == $_POST['new_file_notify'] ) {
            //get clients id
            $send_client_ids = $clients_array;
            //get clients id from Client Circles
            $send_group_ids = $circles_array;

            if ( is_array( $send_group_ids ) && 0 < count( $send_group_ids ) ) {
                foreach( $send_group_ids as $group_id ) {
                    $send_client_ids = array_merge( $send_client_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                }
            }
            $send_client_ids = array_unique( $send_client_ids );

            //send notify
            if ( is_array( $send_client_ids ) && 0 < count( $send_client_ids ) ) {

                $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

                if ( isset( $_FILES['file']['name'] ) && '' != $_FILES['file']['name'] ) {
                    $orig_name = $_FILES['file']['name'];
                } elseif ( isset( $_POST['ftp_selected_files'] ) && '' != $_POST['ftp_selected_files'] ) {
                    if( is_array( $_POST['ftp_selected_files'] ) && 0 < count( $_POST['ftp_selected_files'] ) ) {
                        $orig_names = $_POST['ftp_selected_files'];
                    }
                } else {
                    $orig_name = $_POST['file_name'];
                }

                if( isset( $orig_names ) && is_array( $orig_names ) && 0 < count( $orig_names ) ) {
                    foreach( $orig_names as $key=>$orig_name ) {
                        foreach( $send_client_ids as $send_client_id ) {
                            $send_client_user = get_userdata( $send_client_id );

                            if ( '' != $send_client_id && false !== $send_client_user ) {

                                $email_args = array(
                                    'client_id' => $send_client_id,
                                    'file_name' => $orig_name,
                                    'file_category' => $file_category,
                                    'file_download_link' => $this->get_file_download_link($file_id)
                                );

                                $client = get_userdata( $send_client_id );

                                if ( $client ) {
                                    $client_email = $client->get( 'user_email' );
                                    //send email to client
                                    if( isset( $_POST['attach_file_user'] ) && '1' == $_POST['attach_file_user'] && isset( $target_paths[$key] ) ) {
                                        WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff', $target_paths[$key] );
                                    } else {
                                        WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff' );
                                    }
                                }


                                //get client staff
                                $args = array(
                                    'role'          => 'wpc_client_staff',
                                    'meta_key'      => 'parent_client_id',
                                    'meta_value'    => $send_client_id,
                                );
                                $staffs = get_users( $args );

                                //send email to staff
                                if ( is_array( $staffs ) && 0 < count( $staffs ) ) {
                                    foreach( $staffs as $staff ) {
                                        $email_args = array( 'client_id' => $staff->ID, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => $this->get_file_download_link($file_id) );
                                        if( isset( $_POST['attach_file_user'] ) && '1' == $_POST['attach_file_user'] && isset( $target_paths[$key] ) ) {
                                            //send email
                                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff', $target_paths[$key] );
                                        } else {
                                            //send email
                                            WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff' );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    foreach( $send_client_ids as $send_client_id ) {
                        $send_client_user = get_userdata( $send_client_id );

                        if ( '' != $send_client_id && false !== $send_client_user ) {

                            $email_args = array( 'client_id' => $send_client_id, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => $this->get_file_download_link($file_id) );

                            $client = get_userdata( $send_client_id );
                            if ( $client ) {
                                $client_email = $client->get( 'user_email' );
                                //send email to client
                                if( isset( $_POST['attach_file_user'] ) && '1' == $_POST['attach_file_user'] && isset( $filepath ) ) {
                                    WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff', $filepath );
                                } else {
                                    WPC()->mail( 'new_file_for_client_staff', $client_email, $email_args, 'new_file_for_client_staff' );
                                }
                            }


                            //get client staff
                            $args = array(
                                'role'          => 'wpc_client_staff',
                                'meta_key'      => 'parent_client_id',
                                'meta_value'    => $send_client_id,
                            );
                            $staffs = get_users( $args );

                            //send email to staff
                            if ( is_array( $staffs ) && 0 < count( $staffs ) ) {
                                foreach( $staffs as $staff ) {
                                    $email_args = array( 'client_id' => $staff->ID, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => $this->get_file_download_link($file_id) );
                                    if( isset( $_POST['attach_file_user'] ) && '1' == $_POST['attach_file_user'] && isset( $filepath ) ) {
                                        //send email
                                        WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff', $filepath );
                                    } else {
                                        //send email
                                        WPC()->mail( 'new_file_for_client_staff', $staff->user_email, $email_args, 'new_file_for_client_staff' );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        WPC()->redirect( $redirect );

    }


    /**
     * Function for recursively get children categories HTML in File Categories pages
     */
    function render_children_file_categories_html( $parent_id = 0 ) {
        global $wpdb;
        $return = '';
        $current_page = ( isset( $_GET['page'] ) && isset( $_GET['tab'] ) ) ? $_GET['page'] . $_GET['tab'] : '';
        $children_cats = $wpdb->get_results(
            "SELECT fc.cat_id AS cat_id, cat_name, folder_name, cat_order, parent_id, COUNT(f.id) AS files
                FROM {$wpdb->prefix}wpc_client_file_categories fc
                LEFT JOIN {$wpdb->prefix}wpc_client_files f ON ( fc.cat_id = f.cat_id )
                WHERE fc.parent_id = '$parent_id'
                GROUP BY fc.cat_id
                ORDER BY fc.cat_order",
            ARRAY_A );

        $count_childrens = $wpdb->get_col(
            "SELECT COUNT(fc2.cat_id) AS parent_count
                FROM {$wpdb->prefix}wpc_client_file_categories fc
                LEFT JOIN {$wpdb->prefix}wpc_client_file_categories fc2 ON ( fc2.parent_id = fc.cat_id )
                WHERE fc.parent_id = '$parent_id'
                GROUP BY fc.cat_id
                ORDER BY fc.cat_order"
        );

        if ( ! empty( $children_cats ) ) {
            foreach ( $children_cats as $key=>$children_cat ) {
                $return .=
                    '<li id="list_' . $children_cat['cat_id'] . '" class="sortable_item">' .
                    '<div class="category">' .
                    '<span class="disclose">&nbsp;</span>' .
                    '<div class="category_name">' .
                    '<span id="cat_name_block_' . $children_cat['cat_id'] . '">' . $children_cat['cat_name'] . '</span>' .
                    '<span id="cat_id_block_' . $children_cat['cat_id'] . '"> (#' . $children_cat['cat_id'] . ')</span>' .
                    '<div id="save_or_close_block_' . $children_cat['cat_id'] . '" style="display:none">' .
                    '<a href="javascript:void(0);" id="close_button_' . $children_cat['cat_id'] . '" onclick="jQuery(this).editGroup(' . $children_cat['cat_id'] . ', \'close\' );" >' .
                    __( 'Close', WPC_CLIENT_TEXT_DOMAIN ) .
                    '</a>&nbsp;|&nbsp;' .
                    '<a onClick="jQuery(this).saveGroup();" href="javascript:void(0);">' . __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' .
                    '</div>';

                if ( 'General' != $children_cat['cat_name'] ) {
                    $return .= '<div class="row-actions">' .
                        '<span class="edit">' .
                        '<a id="edit_button_' . $children_cat['cat_id'] . '" onclick="jQuery(this).editGroup( ' . $children_cat['cat_id'] . ', \'edit\' );">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' .
                        '<span id="save_block_' . $children_cat['cat_id'] . '"></span> | ' .
                        '</span>';
                    $show_or_delete = ( 0 < $children_cat['files'] ) ? 'show' : 'delete';
                    $return .= '<span class="delete">' .
                        '<a class="group_delete" onclick="jQuery(this).deleteCat( ' . $children_cat['cat_id'] . ', \'' . $show_or_delete . '\');">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' .
                        '</span>' .
                        '</div>';

                    if ( 0 < $children_cat['files'] ) {
                        $return .= '<div class="cat_reassign_block" id="cat_reassign_block_' . $children_cat['cat_id'] . '">' .
                            '<hr /><span><strong>' . __( 'Category have Files. What do with Files', WPC_CLIENT_TEXT_DOMAIN ) . ':</strong></span><br />' .
                            '<select name="cat_reassign">';

                        $exclude_cats = $this->get_category_children_ids( $children_cat['cat_id'] );
                        $exclude_cats[] = $children_cat['cat_id'];

                        $return .= $this->render_category_list_items( array( 'exclude' => $exclude_cats ), '', false );
                        $return .= '</select>&nbsp;' .
                            '<input type="button" value="' . __( 'Reassign Files', WPC_CLIENT_TEXT_DOMAIN ) . '" onclick="jQuery(this).deleteCat( ' . $children_cat['cat_id'] . ', \'reassign\' );" />&nbsp;' .
                            __( 'or', WPC_CLIENT_TEXT_DOMAIN ) .
                            '&nbsp;<input type="button" value="' . __( 'Delete Files', WPC_CLIENT_TEXT_DOMAIN ) . '" onclick="jQuery(this).deleteCat( ' . $children_cat['cat_id'] . ', \'delete\' );" />' .
                            '</div>';
                    }

                }

                $id_array = WPC()->assigns()->get_assign_data_by_object( 'file_category', $children_cat['cat_id'], 'client' );

                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $manager_clients = WPC()->members()->get_all_clients_manager();
                }
                $user_count = 0;
                foreach ( $id_array as $client_id ) {
                    if ( 0 < $client_id ) {
                        //if manager - skip not manager's clients
                        if ( isset( $manager_clients ) && !in_array( $client_id, $manager_clients ) )
                            continue;
                        if( !empty( $client_id ) ) {
                            $user_count++;
                        }
                    }
                }

                $link_array = array(
                    'data-id' => $children_cat['cat_id'],
                    'data-ajax' => 1,
                    'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . $children_cat['cat_name']
                );
                $input_array = array(
                    'name'  => 'wpc_clients_ajax[]',
                    'id'    => 'wpc_clients_' . $children_cat['cat_id'],
                    'value' => implode( ',', $id_array )
                );
                $additional_array = array(
                    'counter_value' => $user_count
                );
                $client_popup_html = WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

                $id_array = WPC()->assigns()->get_assign_data_by_object( 'file_category', $children_cat['cat_id'], 'circle' );
                $link_array = array(
                    'data-id' => $children_cat['cat_id'],
                    'data-ajax' => 1,
                    'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ) . $children_cat['cat_name'], WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                );
                $input_array = array(
                    'name'  => 'wpc_circles_ajax[]',
                    'id'    => 'wpc_circles_' . $children_cat['cat_id'],
                    'value' => implode( ',', $id_array )
                );
                $additional_array = array(
                    'counter_value' => count( $id_array )
                );
                $circle_popup_html = WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

                $return .= '</div>' .
                    '<div style="float: right;width: 600px;">' .
                    '<div style="float: left;width:300px;height:33px;"><span style="float: left;width:100%;height:33px;" id="folder_name_block_' . $children_cat['cat_id'] . '">' . $children_cat['folder_name'] . '</span></div>' .
                    '<div style="float: left;width:100px;text-align: center;">' . $children_cat['files'] . '</div>' .
                    '<div style="float: left;width:100px;text-align: center;">' .
                    $client_popup_html .
                    '</div>' .
                    '<div style="float: left;width:100px;text-align: center;">';

                $return .= $circle_popup_html;
                $return .= '</div>' .
                    '</div>' .
                    '</div>';
                if( isset( $count_childrens[$key] ) && (int)$count_childrens[$key] > 0 ) {
                    $return .= '<ol>' . $this->render_children_file_categories_html( $children_cat['cat_id'] ) . '</ol>';
                }
                $return .= '</li>';
            }
        }
        return $return;
    }


    /*
    * Add JS for upload form
    */
    function upload_form_js() {
        global $post;

        $client_id  = WPC()->checking_page_access();

        if ( false === $client_id ) {
            return '';
        }

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
        $show_file_note = ( isset( $wpc_file_sharing['show_file_note'] ) && 'yes' == $wpc_file_sharing['show_file_note'] ) ? 1 : 0;
        if ( isset( $wpc_file_sharing['client_uploader_type'] ) && 'html5' == $wpc_file_sharing['client_uploader_type'] ) {
            //Flash uploader
            $timestamp  = time();

            $max_filesize = ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) ? $wpc_file_sharing['file_size_limit'] : '';
            $max_filesize = apply_filters( 'wpc_client_upload_max_file_size', $max_filesize );

            ob_start(); ?>

            <style type="text/css">
                .wpc_uploader_warning {
                    display: none;
                }

                .wpc_uploader_successful {
                    display: none;
                }
            </style>

            <script type="text/javascript">
                var show_file_note = <?php echo $show_file_note; ?>;
                jQuery(document).ready(function() {

                    jQuery( '.wpc_client_upload_form .wpc_file_upload' ).each( function (i) {
                        var id = jQuery( this ).attr( 'data-form_id' );
                        var include_ext = jQuery( '#include_ext_' + id ).val();
                        var exclude_ext = jQuery( '#exclude_ext_' + id ).val();

                        //file upload
                        jQuery( '#wpc_file_upload_' + id ).uploadifive({
                            'auto'              : wpc_flash_uploader_params.params.auto_upload,
                            'sizeLimit'         : '<?php echo $max_filesize ?>',
                            'itemTemplate'      : '<div class="uploadifive-queue-item"><a class="close" href="#">X</a><div><span class="filename"></span><span class="fileinfo"></span></div><div class="progress"><div class="progress-bar"></div></div>' + ( 1 == show_file_note ? '<textarea name="note_' + id + '[]" class="note_field_' + id + '" rows="3" cols="50" placeholder="<?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea>' : '' ) + '</div>',
                            'formData'          : '',
                            'queueID'           : 'queue_' + id,
                            'uploadScript'      : '<?php echo WPC()->get_ajax_url() ?>?action=wpc_client_upload_files&include_ext=' + include_ext + '&exclude_ext=' + exclude_ext ,
                            'onUpload'          : function( file ) {
                                this.data( 'uploadifive' ).settings.formData = {
                                    'timestamp'     : '<?php echo $timestamp ?>',
                                    'token'         : '<?php echo md5( 'unique_salt' . $timestamp ) ?>',
                                    'file_cat_id'   : jQuery( '#file_cat_id_' + id ).val(),
                                    'post_id'       : "<?php echo $post->ID ?>",
                                    'client_id'     : "<?php echo $client_id ?>",
                                    'verify_nonce'  : jQuery('#plupload_nonce').val(),
                                };
                            },
                            'onBeforeUploadFile'          : function( file ) {
                                if( typeof file.queueItem != 'undefined' && file.queueItem.length && jQuery( file.queueItem[0] ).find( '.note_field_' + id ).length && 1 == show_file_note ) {
                                    this.formData.note = jQuery( file.queueItem[0] ).find( '.note_field_' + id ).val();
                                }
                            },
                            'onUploadComplete' : function( file, data ) {
                                jQuery( file.queueItem[0] ).find( '.note_field_' + id ).remove();
                            },
                            'onCancel'     : function( file ) {
                                if( jQuery( '#queue_' + id ).find('span.filename').length > 1 ) {
                                    var notice = 0;

                                    var include_array = include_ext.split(",");
                                    var exclude_array = exclude_ext.split(",");

                                    if( include_array == '' ) {
                                        var include_null = true;
                                    }

                                    var filetypes = [];
                                    var i=0;
                                    for( i=0; i < include_array.length; i++ ) {
                                        filetypes[i] = include_array[i];
                                    }

                                    for( i=0; i < filetypes.length; i++ ) {
                                        for( var j=0; j<exclude_array.length; j++ ) {
                                            if( exclude_array[j] == filetypes[i] ){
                                                filetypes.splice( i, 1 );
                                            }
                                        }
                                    }
                                    for( i=0; i < filetypes.length; i++ ) {
                                        filetypes[i] = "*." + filetypes[i];
                                    }

                                    jQuery( '#queue_' + id ).find('span.filename').each( function(element) {
                                        if( file.name != jQuery(this).html() ) {
                                            var field_value = jQuery(this).html().split(".");
                                            field_value = field_value[field_value.length - 1];

                                            if( ( include_array.indexOf( field_value ) == -1 || exclude_array.indexOf( field_value ) != -1 ) && !include_null ) {
                                                notice++;
                                            }
                                        }
                                    });

                                    if( notice > 0 ) {
                                        jQuery('.wpc_uploader_warning').html( "<?php echo esc_js( __( "Other file extensions except", WPC_CLIENT_TEXT_DOMAIN ) ); ?> " + filetypes.join(', ') + " <?php echo esc_js( __( "will not be loaded.", WPC_CLIENT_TEXT_DOMAIN ) ); ?>" ).slideDown('slow');
                                    } else {
                                        jQuery('.wpc_uploader_warning').slideUp('slow').html( "" );
                                    }
                                } else {
                                    jQuery('.wpc_uploader_warning').slideUp('slow').html( "" );

                                    //disable upload button if no files to upload
                                    jQuery( '#wpc_start_upload_' + id ).prop( 'disabled', true );
                                }
                            },
                            'onSelect' : function(file) {
                                if( jQuery("span").is('.filename') ) {
                                    jQuery('.filename').each( function(element) {
                                        var field_value = jQuery(this).html().split(".");
                                        field_value = field_value[field_value.length - 1];

                                        var include_array = include_ext.split(",");
                                        var exclude_array = exclude_ext.split(",");

                                        if( include_array == '' ) {
                                            var include_null = true;
                                        }

                                        var filetypes = [];
                                        var i=0;
                                        for( i=0; i < include_array.length; i++ ) {
                                            filetypes[i] = include_array[i];
                                        }

                                        for( i=0; i < filetypes.length; i++ ) {
                                            for( var j=0; j<exclude_array.length; j++ ) {
                                                if( exclude_array[j] == filetypes[i] ){
                                                    filetypes.splice( i, 1 );
                                                }
                                            }
                                        }
                                        for( i=0; i < filetypes.length; i++ ) {
                                            filetypes[i] = "*." + filetypes[i];
                                        }

                                        if( ( include_array.indexOf( field_value ) == -1 || exclude_array.indexOf( field_value ) != -1 ) && !include_null ) {
                                            jQuery('.wpc_uploader_warning').html( "<?php echo esc_js( __( "Other file extensions except", WPC_CLIENT_TEXT_DOMAIN ) ); ?> " + filetypes.join(', ') + " <?php echo esc_js( __( "will not be loaded.", WPC_CLIENT_TEXT_DOMAIN ) ); ?>" ).slideDown('slow');
                                        }
                                    });

                                    //Enable upload button if there are files to upload
                                    jQuery( '#wpc_start_upload_' + id ).prop( 'disabled', false );
                                }
                            },
                            'onQueueComplete' : function(file, data) {
                                jQuery( '#uploadifive-wpc_file_upload_' + id).parents('.wpc_client_upload_form').find('.wpc_uploader_successful').show('slow');
                                jQuery(this).uploadifive('clearQueue');
                                var timeout = setTimeout( function(){
                                    jQuery( '#uploadifive-wpc_file_upload_' + id).parents('.wpc_client_upload_form').find('.wpc_uploader_successful').hide('slow');
                                    clearTimeout( timeout );
                                    self.location.href = '<?php echo ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>';
                                    return false;
                                }, 4000 );
                            },
                            'buttonText' : ( wpc_flash_uploader_params.params.auto_upload ) ? '<?php echo esc_js( __( "Select&Upload Files", WPC_CLIENT_TEXT_DOMAIN ) ); ?>' : '<?php echo esc_js( __( "Select Files", WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                        });

                        if( wpc_flash_uploader_params.params.auto_upload ) {
                            jQuery( '#uploadifive-wpc_file_upload_' + id).addClass('wpc_button');
                        }

                    });

                });
            </script>


            <?php $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }
            echo $content;

        } elseif( isset( $wpc_file_sharing['client_uploader_type'] ) && 'plupload' == $wpc_file_sharing['client_uploader_type'] ) {

            $max_filesize = ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) ? $wpc_file_sharing['file_size_limit'] : '0';

            $max_filesize = apply_filters( 'wpc_client_upload_max_file_size', $max_filesize );

            ob_start(); ?>

            <style type="text/css">
                .wpc_uploader_message {
                    display: none;
                }

                .wpc_uploader_warning {
                    display: block;
                }

                .wpc_queue_wrapper {
                    float:left;
                    width:100%;
                    margin: 0;
                    padding: 0;
                }

                .plupload_container {
                    padding: 0;
                }
            </style>

            <script type="text/javascript">
                var show_file_note = <?php echo $show_file_note; ?>;
                jQuery(document).ready(function() {
                    plupload.addI18n({
                        "Stop Upload":"<?php echo esc_js( __( 'Stop Upload', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Upload URL might be wrong or doesn't exist.":"<?php echo esc_js( __( 'Upload URL might be wrong or doesn\'t exist.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "tb":"<?php echo esc_js( __( 'tb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Size":"<?php echo esc_js( __( 'Size', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Close":"<?php echo esc_js( __( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Init error.":"<?php echo esc_js( __( 'Init error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Add files to the upload queue and click the start button.":"<?php echo esc_js( __( 'Add files to the upload queue and click the start button.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Filename":"<?php echo esc_js( __( 'Filename', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Image format either wrong or not supported.":"<?php echo esc_js( __( 'Image format either wrong or not supported.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Status":"<?php echo esc_js( __( 'Status', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "HTTP Error.":"<?php echo esc_js( __( 'HTTP Error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Start Upload":"<?php echo esc_js( __( 'Start Upload', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "mb":"<?php echo esc_js( __( 'mb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "kb":"<?php echo esc_js( __( 'kb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Duplicate file error.":"<?php echo esc_js( __( 'Duplicate file error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File size error.":"<?php echo esc_js( __( 'File size error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "N/A":"<?php echo esc_js( __( 'N/A', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "gb":"<?php echo esc_js( __( 'gb', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Error: Invalid file extension:":"<?php echo esc_js( __( 'Error: Invalid file extension:', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Select files":"<?php echo esc_js( __( 'Select files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "%s already present in the queue.":"<?php echo esc_js( __( '%s already present in the queue.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File: %s":"<?php echo esc_js( __( 'File: %s', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "b":"<?php echo esc_js( __( 'b', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Uploaded %d/%d files":"<?php echo esc_js( __( 'Uploaded %d/%d files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Upload element accepts only %d file(s) at a time. Extra files were stripped.":"<?php echo esc_js( __( 'Upload element accepts only %d file(s) at a time. Extra files were stripped.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "%d files queued":"<?php _e( '%d files queued', WPC_CLIENT_TEXT_DOMAIN ) ?>",
                        "File: %s, size: %d, max file size: %d":"<?php echo esc_js( __( 'File: %s, size: %d, max file size: %d', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Drag files here.":"<?php echo esc_js( __( 'Drag files here.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Runtime ran out of available memory.":"<?php echo esc_js( __( 'Runtime ran out of available memory.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File count error.":"<?php echo esc_js( __( 'File count error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "File extension error.":"<?php echo esc_js( __( 'File extension error.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>",
                        "Error: File too large:":"<?php echo esc_js( sprintf( __( 'Error: File too large. Max %s Kb.', WPC_CLIENT_TEXT_DOMAIN ), $max_filesize ) ) ?>",
                        "Add Files":"<?php echo esc_js( __( 'Add Files', WPC_CLIENT_TEXT_DOMAIN ) ) ?>"
                    });

                    jQuery( '.wpc_plupload_queue' ).each( function (i) {
                        var id = jQuery( this ).attr( 'data-form_id' );
                        var include_ext = jQuery( '#include_ext_' + id ).val();
                        var exclude_ext = jQuery( '#exclude_ext_' + id ).val();

                        var file_cat_id = '';
                        var timeout;
                        var files_added;
                        if ( ! wpc_plupload_uploader.params.auto_upload ) {
                            files_added = function( uploader, files ) {
                                if ( 1 == show_file_note ) {
                                    for ( key in files ) {
                                        jQuery( '#' + files[ key ].id ).append('<textarea name="note[]" id="note_' + files[ key ].id + '" class="note_field" rows="3" cols="50" placeholder="<?php _e( 'File Description', WPC_CLIENT_TEXT_DOMAIN ) ?>"></textarea>');
                                    }
                                }
                            }
                        } else {
                            files_added = function( uploader ) {
                                uploader.start();
                            }
                        }

                        var uploader_box = jQuery( "#queue_" + id );

                        //file upload
                        uploader_box.pluploadQueue({
                            // General settings
                            runtimes : 'html5,browserplus,silverlight,flash,gears,html4',
                            url : '<?php echo WPC()->get_ajax_url() ?>?action=wpc_client_plupload_upload_files',

                            chunk_size : '<?php echo apply_filters( 'wpc_client_plupload_chunk_size', '9mb' ) ?>',
                            rename : true,
                            dragdrop: true,
                            max_retries : 3,
                            filters : {
                                <?php if ( ! empty( $max_filesize ) ) { ?>
                                // Maximum file size
                                max_file_size : '<?php echo $max_filesize ?>kb'
                                <?php } ?>
                            },
                            init : {
                                FilesAdded: files_added,
                                BeforeUpload: function(uploader, file) {
                                    // Called right before the upload for a given file starts, can be used to cancel it if required
                                    include_ext = jQuery( '#include_ext_' + id ).val();
                                    exclude_ext = jQuery( '#exclude_ext_' + id ).val();

                                    file_cat_id = jQuery( '#file_cat_id_' + id ).val();

                                    uploader.settings.url = '<?php echo WPC()->get_ajax_url() ?>?action=wpc_client_plupload_upload_files&include_ext=' + encodeURIComponent( include_ext ) + '&exclude_ext=' + encodeURIComponent( exclude_ext ) + '&client_id=<?php echo $client_id ?>' + '&file_cat_id=' + file_cat_id + '&post_id=<?php echo $post->ID ?>' + ( 1 == show_file_note ? '&note=' + jQuery('#note_' + file.id).val() : '' )+'&verify_nonce='+jQuery('#plupload_nonce').val();
                                },
                                FileUploaded: function( up, file, response ) {
                                    try {
                                        response = jQuery.parseJSON( response.response );
                                        if ( response.hasOwnProperty( 'error' ) ) {
                                            jQuery('#' + file.id).attr( 'class', 'plupload_failed' ).find('a').attr('title', response.error.message );
                                            jQuery('#' + file.id).append( '<span>' + response.error.message + '</span>' );
                                            file.status = plupload.FAILED;
                                            file.hint = response.error.message;
                                        } else {
                                            file.status = plupload.DONE;
                                        }

                                        up.trigger( 'UpdateList', file );
                                        up.trigger( 'QueueChanged' );
                                    }
                                    catch (e) {
                                        alert('Code Error: ' + e);
                                    }
                                },
                                UploadComplete: function( uploader, files ) {
                                    var files_uploaded = 0;
                                    jQuery.each( files, function(e) {
                                        if ( files[e].status == plupload.FAILED )
                                            jQuery('#' + files[e].id).append( '<span>' + files[e].hint + '</span>' );
                                        else
                                            files_uploaded++;
                                    });


                                    // Called when all files are either uploaded or failed
                                    var uploader_wrapper = uploader_box.parents('.wpc_client_upload_form');
                                    var uploader_message = uploader_wrapper.find('.wpc_uploader_message');
                                    uploader_message.removeClass( 'wpc_error' );

                                    if ( files_uploaded == files.length ) {
                                        uploader_message.html('<?php _e( 'All files were uploaded successfully!', WPC_CLIENT_TEXT_DOMAIN ) ?>').addClass('wpc_apply').show('slow');
                                        //uploader.splice( 0, files.length );
                                        uploader_wrapper.find( '.plupload_buttons' ).show();
                                        uploader_wrapper.find( '.plupload_start' ).addClass( 'plupload_disabled' );
                                        uploader_wrapper.find( '.plupload_upload_status' ).hide();
                                        uploader_wrapper.find( '.plupload_total_file_size' ).html('0 b');
                                        uploader_wrapper.find( '.plupload_total_status' ).html('0%');

                                        timeout = setTimeout( function(){
                                            uploader_wrapper.find('.wpc_uploader_successful').hide('slow');
                                            clearTimeout( timeout );
                                            self.location.href = '<?php echo ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>';
                                            return false;
                                        }, 4000 );
                                    } else if ( files_uploaded == 0 ) {
                                        uploader_message.html( '<?php _e( 'All files were not uploaded!', WPC_CLIENT_TEXT_DOMAIN ) ?>' ).addClass('wpc_error').show('slow');

                                        uploader_wrapper.find( '.plupload_buttons' ).show();
                                        uploader_wrapper.find( '.plupload_start' ).addClass( 'plupload_disabled' );
                                        uploader_wrapper.find( '.plupload_upload_status' ).hide();
                                        uploader_wrapper.find( '.plupload_total_file_size' ).html('0 b');
                                        uploader_wrapper.find( '.plupload_total_status' ).html('0%');

                                        <?php if ( apply_filters( 'wpc_client_uploader_refresh_on_error', false ) ) { ?>

                                        timeout = setTimeout( function(){
                                            uploader_wrapper.find('.wpc_uploader_successful').hide('slow');
                                            clearTimeout( timeout );
                                            self.location.href = '<?php echo ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>';
                                            return false;
                                        }, 4000 );

                                        <?php } ?>

                                    } else {
                                        //var files_uploaded = files.length - errors;
                                        uploader_message.html( files_uploaded + ' <?php _e( 'out of', WPC_CLIENT_TEXT_DOMAIN ) ?> ' + files.length + ' <?php _e( 'files were uploaded successfully!', WPC_CLIENT_TEXT_DOMAIN ) ?>' ).addClass('wpc_warning').show('slow');

                                        uploader_wrapper.find( '.plupload_buttons' ).show();
                                        uploader_wrapper.find( '.plupload_start' ).addClass( 'plupload_disabled' );
                                        uploader_wrapper.find( '.plupload_upload_status' ).hide();
                                        uploader_wrapper.find( '.plupload_total_file_size' ).html('0 b');
                                        uploader_wrapper.find( '.plupload_total_status' ).html('0%');

                                        <?php if ( apply_filters( 'wpc_client_uploader_refresh_on_error', false ) ) { ?>

                                        timeout = setTimeout( function(){
                                            uploader_wrapper.find('.wpc_uploader_successful').hide('slow');
                                            clearTimeout( timeout );
                                            self.location.href = '<?php echo ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>';
                                            return false;
                                        }, 4000 );

                                        <?php } ?>
                                    }
                                },
                                Error: function( uploader, error ) {
                                }
                            },
                            // Flash settings
                            flash_swf_url : '<?php echo WPC()->plugin_url ?>js/plupload/Moxie.swf',

                            // Silverlight settings
                            silverlight_xap_url : '<?php echo WPC()->plugin_url ?>js/plupload/Moxie.xap'
                        });

                        if ( wpc_plupload_uploader.params.auto_upload ) {
                            jQuery( '.plupload_start' ).hide();
                        }

                    });

                });
            </script>


            <?php $content = ob_get_contents();
            if ( ob_get_length() ) {
                ob_end_clean();
            }
            echo $content;

        } else { ?>
            <style type="text/css">
                .wpc_uploader_warning {
                    display: none;
                }

                .wpc_uploader_successful {
                    display: block;
                }

                .wpc_start_upload.wpc_button {
                    background: #0092d5;
                    border: 2px solid;
                    border-left-color: #0092d5;
                    border-top-color: #0092d5;
                    border-right-color: #0071a5;
                    border-bottom-color: #0071a5;
                    color: #fff;
                    padding: 5px 10px;
                    margin: 0;
                    text-transform: uppercase;
                    font-size: 12px;
                    line-height: 15px !important;
                    box-shadow: none;
                    text-shadow: none;
                    border-radius:0;
                    outline: none !important;
                    box-sizing:border-box;
                    -webkit-box-sizing:border-box;
                    -moz-box-sizing:border-box;
                    height: 26px !important;
                    position: relative !important;
                    width: 150px !important;
                    cursor:pointer !important;
                    float:left;
                    display: block;
                    clear: both;
                }

                .wpc_start_upload.wpc_button:hover {
                    background: #0092d5;
                }

                .wpc_start_upload.wpc_button:focus,
                .wpc_start_upload.wpc_button:active {
                    background: #0092d5 !important;
                    border: 2px solid !important;
                    border-left-color: #0071a5 !important;
                    border-top-color: #0071a5 !important;
                    border-right-color: #0092d5 !important;
                    border-bottom-color: #0092d5 !important;
                    padding: 5px 10px !important;
                }

                #file {
                    float:left;
                    clear: both;
                    margin: 7px 0;
                }
            </style>

            <script type="text/javascript">
                function checkform(){
                    if( document.getElementById('file').value == "" ) {
                        alert("<?php echo esc_js( __( 'Please select file to upload.', WPC_CLIENT_TEXT_DOMAIN ) ) ?>");
                        return false;
                    }
                    return true;
                }

                jQuery( document ).ready( function() {
                    jQuery('#file').change( function() {
                        var field_value = jQuery('#file').val();
                        var include_array = jQuery('#include_ext').val();
                        var exclude_array = jQuery('#exclude_ext').val();
                        var auto_upload = jQuery('#wpc_auto_upload').val();

                        if( include_array == '' ) {
                            var include_null = true;
                        }

                        include_array = include_array.split(",");
                        exclude_array = exclude_array.split(",");

                        field_value = field_value.split(".");
                        field_value = field_value[field_value.length - 1];
                        field_value = field_value.toLowerCase();


                        var filetypes = [];
                        for(  var i=0; i<include_array.length; i++ ) {
                            filetypes[i] = include_array[i];
                        }

                        for( var i=0; i<filetypes.length; i++ ) {
                            for( var j=0; j<exclude_array.length; j++ ) {
                                if( exclude_array[j] == filetypes[i] ){
                                    filetypes.splice( i, 1 );
                                }
                            }
                        }
                        for( var i=0; i < filetypes.length; i++ ) {
                            filetypes[i] = "*." + filetypes[i];
                        }

                        if( ( include_array.indexOf( field_value ) == -1 || exclude_array.indexOf( field_value ) != -1 ) && !include_null ) {
                            jQuery('.wpc_uploader_warning').html( "Other file extensions except " + filetypes.join(', ') + " will not be loaded" );
                            jQuery('.wpc_uploader_warning').slideDown('slow');
                        } else {
                            jQuery('.wpc_uploader_warning').slideUp('slow');

                            if( auto_upload == 'yes' ) {
                                jQuery('#uploader_submit').trigger('click');
                            }
                        }
                    });

                    jQuery('#uploader_submit').click( function() {
                        var field_value = jQuery('#file').val();
                        var include_array = jQuery('#include_ext').val();
                        var exclude_array = jQuery('#exclude_ext').val();

                        if( include_array == '' ) {
                            var include_null = true;
                        }

                        include_array = include_array.split(",");
                        exclude_array = exclude_array.split(",");

                        field_value = field_value.split(".");
                        field_value = field_value[field_value.length - 1];
                        field_value = field_value.toLowerCase();

                        var filetypes = [];
                        for(  var i=0; i<include_array.length; i++ ) {
                            filetypes[i] = include_array[i];
                        }

                        for( var i=0; i<filetypes.length; i++ ) {
                            for( var j=0; j<exclude_array.length; j++ ) {
                                if( exclude_array[j] == filetypes[i] ){
                                    filetypes.splice( i, 1 );
                                }
                            }
                        }
                        for( var i=0; i < filetypes.length; i++ ) {
                            filetypes[i] = "*." + filetypes[i];
                        }

                        if( ( include_array.indexOf( field_value ) == -1 || exclude_array.indexOf( field_value ) != -1 ) && !include_null ) {
                            jQuery('.wpc_uploader_warning').html( "Other file extensions except " + filetypes.join(', ') + " will not be loaded" );
                            jQuery('.wpc_uploader_warning').slideDown('slow');
                            return false;
                        } else {
                            return checkform();
                        }
                    });
                });

            </script>
            <?php

        }

        return '';
    }


    function ltrim_file_extension( $var ) {
        return trim( ltrim( trim( $var ), '.' ) );
    }


    public function get_client_id() {
        $client_id = 0;
        if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) {
            $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
        } elseif ( current_user_can( 'wpc_client' ) && !current_user_can( 'manage_network_options' ) ) {
            $client_id = get_current_user_id();
        }
        return $client_id;
    }


    function core_file_downloader( $id = '' ) {
        require_once WPC()->plugin_dir . 'includes/downloader.php';
    }


    function custom_fields_file_downloader( $id = '' ) {
        require_once WPC()->plugin_dir . 'includes/downloader.custom_fields.php';
    }


    function files_shortcode_actions_handler() {
        global $wpdb;

        if( !empty( $_GET['wpc_act'] ) ) {
            $user_id = WPC()->checking_page_access();

            if ( false === $user_id || 0 == $user_id ) {
                return '';
            }

            switch( $_GET['wpc_act'] ) {
                case 'delete_file':

                    //delete files action
                    $file_id = $_GET['id'];

                    $file = '';
                    if( current_user_can( 'wpc_delete_assigned_files' ) ) {

                        $access_files_ids = array();

                        $group_ids = WPC()->groups()->get_client_groups_id( $user_id );

                        if( is_array( $group_ids ) && 0 < count( $group_ids ) ) {
                            foreach( $group_ids as $group_id ) {

                                $group_files = WPC()->assigns()->get_assign_data_by_object( 'file', 'circle', $group_id );
                                $access_files_ids = array_merge( $access_files_ids, $group_files );

                            }
                        }

                        $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $user_id );
                        $access_files_ids = array_merge( $access_files_ids, $client_files );

                        $access_files_ids = array_unique( $access_files_ids );

                        $sql_in = " OR id IN('" . implode( "','", $access_files_ids ) . "')";

                        $file = $wpdb->get_row( $wpdb->prepare(
                            "SELECT *
                                FROM {$wpdb->prefix}wpc_client_files
                                WHERE id = %d AND
                                    ( user_id=%d $sql_in )",
                            $file_id,
                            $user_id
                        ), ARRAY_A );
                    }

                    if ( !$file && current_user_can( 'wpc_delete_uploaded_files' ) ) {
                        $file = $wpdb->get_row( $wpdb->prepare(
                            "SELECT *
                                FROM {$wpdb->prefix}wpc_client_files
                                WHERE id = %d AND
                                    user_id = %d",
                            $file_id,
                            $user_id
                        ), ARRAY_A );
                    }

                    if ( $file ) {
                        WPC()->files()->delete_file( $file_id );

                        WPC()->redirect( remove_query_arg( array( 'wpc_act', 'id' ), get_permalink() ) );
                    }

                    break;
            }

        }


        //bulk actions handler
        if( isset( $_POST['wpc_files_bulk_action'] ) && !empty( $_POST['wpc_files_bulk_action'] ) ) {

            $user_id = WPC()->checking_page_access();

            if ( false === $user_id || 0 == $user_id ) {
                return '';
            }

            switch( $_POST['wpc_files_bulk_action'] ) {
                case 'download': {

                    if( isset( $_POST['bulk_ids'] ) && !empty( $_POST['bulk_ids'] ) ) {
                        $settings_file_sharing = WPC()->get_settings( 'file_sharing' );
                        $zip_name = ( isset( $settings_file_sharing['bulk_download_zip'] ) && !empty( $settings_file_sharing['bulk_download_zip'] ) ) ? $settings_file_sharing['bulk_download_zip'] : 'files';
                        $orig_zip_name = time() . '_' . uniqid() . '_bulk_download';

                    // clear old temporary directories
                    $parent_dir = WPC()->get_upload_dir( 'wpclient/_temp_bulk_download/' );
                    $old_temp_dir_names = array_diff( scandir( $parent_dir ), array('.', '..') );
                    foreach ( $old_temp_dir_names as $old_temp_dir_name ) {
                        $old_temp_dir = $parent_dir . DIRECTORY_SEPARATOR . $old_temp_dir_name;
                        if ( is_dir( $old_temp_dir ) && ( time() - filemtime ( $old_temp_dir ) > 60 ) ) {
                            $old_temp_file_names = array_diff( scandir( $old_temp_dir ), array('.', '..') );
                            foreach ( $old_temp_file_names as $old_temp_file_name ) {
                                $old_temp_file = $old_temp_dir . DIRECTORY_SEPARATOR . $old_temp_file_name;
                                if ( is_file( $old_temp_file ) ) {
                                    unlink( $old_temp_file );
                                }
                            }
                            rmdir( $old_temp_dir );
                        }
                    }

                        // create new temporary directory
                        $file_temp_dir = WPC()->get_upload_dir( 'wpclient/_temp_bulk_download/' . $orig_zip_name . '/' );

                        // get files
                        $files = $wpdb->get_results( "
                          SELECT *
                          FROM {$wpdb->prefix}wpc_client_files f
                          WHERE f.id IN('" . implode( "','", $_POST['bulk_ids'] ) . "')",
                            ARRAY_A );

                        $items_array = array();
                        if( isset( $files ) && !empty( $files ) ) {
                            foreach( $files as $file ) {
                                if( !$file['external'] ) {
                                    $target_path = WPC()->files()->get_file_path( $file );
                                    if( file_exists( $target_path ) ) {
                                        if ( file_exists( $file_temp_dir . $file['name'] ) ) {
                                            $ii = 1;
                                            $file_type = explode( '.', $file['name'] );
                                            $file_type = strtolower( end( $file_type ) );

                                            $file_name = preg_replace( "/\." . $file_type . "$/is", '', $file['name'] );

                                            while( file_exists( $file_temp_dir . $file_name . '(' . $ii . ').'. $file_type ) ) {
                                                $ii++;
                                            }

                                            $temp_name = $file_temp_dir . $file_name . '(' . $ii . ').'. $file_type;
                                        } else {
                                            $temp_name = $file_temp_dir . $file['name'];
                                        }

                                        $customRead = apply_filters( 'wp_client_decrypted_file_copy_tmp_dir', false, $target_path, $temp_name );

                                        if ( $customRead !== false )
                                            continue;

                                        if( copy( $target_path, $temp_name ) ) {
                                            $items_array[ $file['id'] ] = $temp_name;
                                        }
                                    }
                                }
                            }
                        }

                        if( count( $items_array ) > 0 ) {
                            if ( !ini_get( 'safe_mode' ) ) {
                                @set_time_limit( 0 );
                            }

                            $archive = WPC()->create_archive( $file_temp_dir, $orig_zip_name .'.zip', $file_temp_dir );

                            if ( $archive && file_exists( $file_temp_dir . $orig_zip_name .'.zip' ) ) {
                                foreach( $files as $file ) {
                                    if ( isset( $items_array[ $file['id'] ] ) ) {
                                        unlink( $items_array[ $file['id'] ] );

                                        $wpdb->update(
                                            "{$wpdb->prefix}wpc_client_files",
                                            array(
                                                'last_download' => time()
                                            ),
                                            array(
                                                'id' => $file['id']
                                            )
                                        );

                                        //update download_log
                                        $wpdb->insert(
                                            "{$wpdb->prefix}wpc_client_files_download_log",
                                            array(
                                                'file_id' => $file['id'],
                                                'client_id' => $user_id,
                                                'download_date' => time()
                                            )
                                        );

                                        //email to admins
                                        $args = array(
                                            'role'      => 'wpc_admin',
                                            'fields'    => array( 'user_email' )
                                        );
                                        $admin_emails = get_users( $args );
                                        $emails_array = array();
                                        if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                                            foreach( $admin_emails as $admin_email ) {
                                                $emails_array[] = $admin_email->user_email;
                                            }
                                        }

                                        $emails_array[] = get_option( 'admin_email' );

                                        $args = array(
                                            'client_id' => $user_id,
                                            'file_name' => $file['name']
                                        );
                                        foreach( $emails_array as $to_email ) {
                                            WPC()->mail( 'client_downloaded_file', $to_email, $args, 'client_downloaded_file' );
                                        }


                                        //send message to client manager
                                        //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $user_id );
                                        $manager_ids = WPC()->members()->get_client_managers( $user_id );

                                        if( is_array( $manager_ids ) && count( $manager_ids ) ) {
                                            foreach( $manager_ids as $manager_id ) {
                                                if ( 0 < $manager_id ) {
                                                    $manager = get_userdata( $manager_id );
                                                    if ( $manager ) {
                                                        $manager_email = $manager->get( 'user_email' );
                                                        //send email
                                                        WPC()->mail( 'client_downloaded_file', $manager_email, $args, 'client_downloaded_file' );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                header("Pragma: no-cache");
                                header("Expires: 0");
                                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                                header("Robots: none");
                                header("Content-Description: File Transfer");
                                header("Content-Transfer-Encoding: binary");
                                header('Content-Type: application/octet-stream');
                                header("Content-length: " . filesize( $file_temp_dir . $orig_zip_name .'.zip' ) );
                                header('Content-disposition: attachment; filename="' . $zip_name . '.zip"');

                                $levels = ob_get_level();
                                for ( $i = 0; $i < $levels; $i++ ) {
                                    @ob_end_clean();
                                }

                                WPC()->readfile_chunked( $file_temp_dir . $orig_zip_name . '.zip' );
                            }

                            if( file_exists( $file_temp_dir . $orig_zip_name .'.zip' ) ) {
                                unlink( $file_temp_dir . $orig_zip_name . '.zip' );
                            }
                        }

                        if( is_dir( $file_temp_dir ) ) {
                            system( 'rm -rf ' . escapeshellarg( $file_temp_dir ) );
                        }

                        exit;
                    }

                    WPC()->files()->download_files_zip( $_POST['bulk_ids'], true );
                    break;
                }
                case 'delete': {
                    if( isset( $_POST['bulk_ids'] ) && !empty( $_POST['bulk_ids'] ) ) {

                        $files = $wpdb->get_results(
                            "SELECT *
                                FROM {$wpdb->prefix}wpc_client_files f
                                WHERE f.id IN('" . implode( "','", $_POST['bulk_ids'] ) . "')",
                            ARRAY_A );

                        if( isset( $files ) && !empty( $files ) ) {
                            foreach( $files as $file ) {
                                $to_delete = false;

                                if( $file['page_id'] != 0 && $file['user_id'] == $user_id ) {
                                    if( current_user_can( 'wpc_delete_uploaded_files' ) ) {
                                        $to_delete = true;
                                    }
                                } else {
                                    if( current_user_can( 'wpc_delete_assigned_files' ) ) {
                                        $to_delete = true;
                                    }
                                }

                                if( $to_delete ) {
                                    WPC()->files()->delete_file( $file['id'] );
                                }
                            }
                        }
                    }

                    WPC()->redirect( get_permalink() );

                    break;
                }
            }
        }

        return '';
    }




}

endif;