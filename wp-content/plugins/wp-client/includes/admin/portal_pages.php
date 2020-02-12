<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
        && !current_user_can( 'view_others_clientspages' ) && !current_user_can( 'edit_others_clientspages' ) ) {

    if ( current_user_can( 'view_others_portalhubs' ) || current_user_can( 'edit_others_portalhubs' ) )
        $adress = 'admin.php?page=wpclients_content&tab=portalhubs';
    else
        $adress = 'admin.php?page=wpclients_content&tab=files';

    WPC()->redirect( get_admin_url() . $adress );
}

global $wpdb;

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=portal_page';
}

if ( isset( $_GET['action'] ) ) {
    switch ( $_GET['action'] ) {
        /* trash action */
        case 'trash':

            $pp_ids = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_portal_page_trash' .  $_REQUEST['id'] . get_current_user_id() );
                $pp_ids = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['portal_page']['p'] ) );
                $pp_ids = $_REQUEST['item'];
            }

            if ( count( $pp_ids ) ) {
                foreach ( $pp_ids as $pp_id ) {
                    //trash portal page
                    $type = $wpdb->get_var( $wpdb->prepare( "SELECT post_status FROM {$wpdb->posts} WHERE ID = '%d'", $pp_id ) );
                    if( $type )
                        add_post_meta( $pp_id, 'wpc_hubpage_old_status', $type ) ;
                    $wpdb->update( $wpdb->posts , array( 'post_status' => 'trash' ), array( 'ID' => $pp_id ) );
                }
                WPC()->redirect( add_query_arg( 'trashed', count( $pp_ids ), $redirect ) );
            }
            WPC()->redirect( $redirect );

        break;

        /* untrash action */
        case 'untrash':

            $pp_ids = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_portal_page_untrash' .  $_REQUEST['id'] . get_current_user_id() );
                $pp_ids = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['portal_page']['p'] ) );
                $pp_ids = $_REQUEST['item'];
            }

            if ( count( $pp_ids ) ) {
                foreach ( $pp_ids as $pp_id ) {
                    //untrash portal page
                    $new_status = get_post_meta( $pp_id, 'wpc_hubpage_old_status', true ) ;

                    $wpdb->update( $wpdb->posts , array( 'post_status' => $new_status ), array( 'ID' => $pp_id ) );
                }
                WPC()->redirect( add_query_arg( 'untrashed', count( $pp_ids ), $redirect ) );
            }
            WPC()->redirect( $redirect );

        break;

        /* delete action */
        case 'delete':

            $pp_ids = array();
            if ( isset( $_REQUEST['id'] ) ) {
                check_admin_referer( 'wpc_portal_page_delete' .  $_REQUEST['id'] . get_current_user_id() );
                $pp_ids = (array) $_REQUEST['id'];
            } elseif( isset( $_REQUEST['item'] ) )  {
                check_admin_referer( 'bulk-' . sanitize_key( WPC()->custom_titles['portal_page']['p'] ) );
                $pp_ids = $_REQUEST['item'];
            }

            if ( count( $pp_ids ) ) {
                foreach ( $pp_ids as $pp_id ) {
                    //delete portal page
                    wp_delete_post( $pp_id );
                    WPC()->assigns()->delete_all_object_assigns( 'portal_page', $pp_id );
                }
                WPC()->redirect( add_query_arg( 'deleted', count( $pp_ids ), $redirect ) );
            }
            WPC()->redirect( $redirect );

        break;

    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}


$visible_status = array( 'trash', 'publish', 'future', 'draft', 'pending', 'private'  ); //trash should be first in array
$where_status = '';
if( !empty( $_GET['filter_status'] ) && in_array( $_GET['filter_status'], $visible_status ) ) {
    $where_status = " AND p.post_status = '" . esc_sql( $_GET['filter_status'] ) . "'";
} else {
    $for_all_visible_status = $visible_status;
    array_shift( $for_all_visible_status );
    $where_status = " AND p.post_status IN ('" . implode( "','", $for_all_visible_status ) . "') ";
}

$where_clause = '';
if( !empty( $_GET['s'] ) ) {
    $where_clause = WPC()->admin()->get_prepared_search( $_GET['s'], array(
        'p.post_title',
    ) );
}

if( !empty( $_GET['tag'] ) ) {
    $post_ids = get_objects_in_term( $_GET['tag'], 'wpc_tags' );
    if( is_array( $post_ids ) && count( $post_ids ) ) {
        $where_clause .= " AND p.ID IN (" . implode( ', ', $post_ids ) . ")";
    } else {
        $where_clause .= " AND 0 = 1";
    }
}

$where_manager = '';
if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
    $manager_id = get_current_user_id();

    $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'client' );
    $manager_groups = WPC()->assigns()->get_assign_data_by_object( 'manager', $manager_id, 'circle' );

    $clients_groups = array();
    foreach ( $manager_groups as $group_id ) {
        $clients_groups = array_merge( $clients_groups, WPC()->groups()->get_group_clients_id( $group_id ) );
    }
    $manager_all_clients = array_unique( array_merge( $manager_clients, $clients_groups ) );

    $groups_clients = array();
    foreach ( $manager_clients as $client_id ) {
        $groups_clients = array_merge( $groups_clients, WPC()->groups()->get_client_groups_id( $client_id ) );
    }
    $manager_all_groups = array_unique( array_merge( $manager_groups, $groups_clients ) );

    $post_of_clients = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $manager_all_clients );
    $post_of_groups = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $manager_all_groups );
    $cat_of_clients = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $manager_all_clients );
    $cat_of_groups = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $manager_all_groups );
    $all_cats = array_unique( array_merge( $cat_of_clients, $cat_of_groups ) );
    $posts_of_cats = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = '_wpc_category_id' AND pm.meta_value IN ('" . implode( "','", $all_cats ) . "') ) WHERE p.post_type = 'clientspage'" );
    $manager_all_post = array_unique( array_merge( $post_of_clients, $post_of_groups, $posts_of_cats ) );
    $where_manager = " AND ( p.ID IN ('" . implode( "','", $manager_all_post ) . "') OR p.post_author = '" . get_current_user_id() . "' )" ;

    unset( $manager_clients, $manager_all_clients, $manager_groups, $manager_all_groups, $clients_groups, $groups_clients, $post_of_clients, $post_of_groups, $cat_of_clients, $cat_of_groups, $all_cats, $posts_of_cats, $manager_all_post );
}

$where_cat = '';
if( isset( $_GET['wpc_pp_category'] ) &&  (int)$_GET['wpc_pp_category'] ) {
    $search_text = (int)$_GET['wpc_pp_category'];
    $where_cat = " AND cppc.cat_id = '" . $search_text . "'";
}

$where_date = '';
$m = ( isset( $_GET['m'] ) ) ? (int)$_GET['m'] : 0 ;
if ( 0 < $m && 6 == strlen( $m ) ) {
    $year = substr( $m, 0, 4 );
    $month = substr( $m, 4, 6 );
    $next_month = (int) $month + 1;
    //var_dump( $year, $month, date( "d-m-Y", mktime( 0, 0, 0, $month,1 , $year ) ) );
    $where_date = " AND p.post_modified > '{$year}-{$month}-01 00:00:00' AND p.post_modified < '{$year}-{$next_month}-01 00:00:00'";
}

$order_by = 'p.ID';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'date' :
            $order_by = 'p.post_modified';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Portalpages_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();

    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        $this->no_items_message = $args['plural'] . ' ' . __( 'not found.', WPC_CLIENT_TEXT_DOMAIN );

        parent::__construct( $args );


    }

    function __call( $name, $arguments ) {
        return call_user_func_array( array( $this, $name ), $arguments );
    }

    function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {
        if( isset( $item[ $column_name ] ) ) {
            return $item[ $column_name ];
        } else {
            return '';
        }
    }

    function no_items() {
        echo $this->no_items_message;
    }

    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach( $args as $k=>$val ) {
            if( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } else if( is_string( $k ) ) {
                $return_args[ $k ] = array( $val, $k == $this->default_sorting_field );
            } else {
                continue;
            }
        }
        $this->sortable_columns = $return_args;
        return $this;
    }

    function get_sortable_columns() {
        return $this->sortable_columns;
    }

    function set_columns( $args = array() ) {
        if( count( $this->bulk_actions ) ) {
            $args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
        }
        $this->columns = $args;
        return $this;
    }

    function get_columns() {
        return $this->columns;
    }

    function set_actions( $args = array() ) {
        $this->actions = $args;
        return $this;
    }

    function get_actions() {
        return $this->actions;
    }

    function set_bulk_actions( $args = array() ) {
        $this->bulk_actions = $args;
        return $this;
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_title( $item ) {
        $actions = array();

        if ( isset( $_GET['filter_status'] ) && 'trash' == $_GET['filter_status'] ) {

            if ( current_user_can( 'edit_others_clientspages' ) ) {
                $actions['untrash'] = '<a href="admin.php?page=wpclients_content&tab=portal_page&action=untrash&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_portal_page_untrash' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" title="' . esc_attr( __( 'Restore this item from the Trash', WPC_CLIENT_TEXT_DOMAIN ) ) . '"  >' . __( 'Restore', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

                $actions['delete'] = '<a href="admin.php?page=wpclients_content&tab=portal_page&action=delete&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_portal_page_delete' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" title="' . esc_attr( __( 'Delete this item permanently', WPC_CLIENT_TEXT_DOMAIN ) ) . '" >' . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

        } else {

            if ( current_user_can( 'edit_others_clientspages' ) ) {
                $actions['edit'] = '<a href="post.php?post=' . $item['id'] . '&action=edit" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';


                $actions['delete'] = '<a href="admin.php?page=wpclients_content&tab=portal_page&action=trash&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_portal_page_trash' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" title="' . esc_attr( __( 'Move this item to the Trash', WPC_CLIENT_TEXT_DOMAIN ) ) . '"  >' . __( 'Trash', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }

            if( current_user_can('wpc_admin_user_login') ) {
                $schema = is_ssl() ? 'https://' : 'http://';
                $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                $pp_preview_url = get_admin_url( null,'admin.php?wpc_action=relogin&nonce=' . wp_create_nonce( 'relogin' . get_current_user_id() . $item['id'] ) . '&page_name=portal_page&page_id=' . $item['id'] . '&referer_url=' . urlencode( $current_url ) );
            } else {
                $pp_preview_url = get_permalink( $item['id'] );
            }

            $actions['view'] = '<a href="'. $pp_preview_url .'" onclick=\'return confirm("' . sprintf( __( "You will be re-logged-in under the role of %s to preview this page. Continue?", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '");\' >' . __( 'Preview', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        $status = '';
        if ( 'draft' == $item['status'] ) {
            $status = ' &mdash; <span class="post-state">' . __( 'Draft', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
        } elseif ( 'future' == $item['status'] ) {
            $status = ' &mdash; <span class="post-state">' . __( 'Scheduled', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
        }

        return sprintf('%1$s %2$s', '<b><a href="post.php?post=' . $item['id'] . '&action=edit">' . ( ( '' != $item['title'] ) ? $item['title'] : '(' . __( 'no title', WPC_CLIENT_TEXT_DOMAIN )  . ')' ) . '</a>' . $status . '</b>', $this->row_actions( $actions ) );
    }

    function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            ?>
            <div class="alignleft actions">
                <?php $this->months_dropdown( 'clientspage' );

                $categories = WPC()->categories()->get_clientspage_categories();

                foreach( $categories as $key=>$category ) {
                    $args = array(
                        'meta_key'      => '_wpc_category_id',
                        'meta_value'    => $category['id'],
                        'post_type'     => 'clientspage'
                    );

                    $posts = get_posts( $args );
                    if( ( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) ) && !( isset( $posts ) && !empty( $posts ) ) ) {
                        unset( $categories[$key] );
                        continue;
                    }
                    if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {

                        $visible_marker = false;

                        $client_ids = WPC()->members()->get_all_clients_manager();

                        foreach( $posts as $key=>$post ) {

                             //Portal Pages in Portal Pages Categories with Clients access
                            $category_id = $category['id'];

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
                            if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                                foreach( $groups_id as $group_id ) {
                                    $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                                }

                            if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                                $user_ids = array_unique( $user_ids );

                            if ( is_array( $client_ids ) && 0 < count( $client_ids ) ) {
                                foreach( $client_ids as $client_id ) {
                                    if ( in_array( $client_id, $user_ids ) ) {
                                        $visible_marker = true;
                                    }
                                }
                            }
                            if( !$visible_marker ) {
                                unset( $posts[$key] );
                                continue;
                            }
                            $visible_marker = false;
                        }

                        if( !( isset( $posts ) && !empty( $posts ) ) ) {
                            unset( $categories[$key] );
                            continue;
                        }
                    }

                }

                if ( ! WPC()->flags['easy_mode'] ) {
                    $tags = get_terms('wpc_tags', 'hide_empty=0');
                } ?>

                <select name="wpc_pp_category">
                    <option value=""><?php _e( 'View all categories ', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php $current_v = isset( $_GET['wpc_pp_category'] ) ? $_GET['wpc_pp_category'] : '';
                        foreach( $categories as $category ) { ?>
                            <option value="<?php echo $category['id'] ?>" <?php if( $current_v == $category['id'] ) { ?> selected="selected"<?php } ?>><?php echo $category['name'] ?></option>',
                        <?php } ?>
                </select>

                <?php if ( ! WPC()->flags['easy_mode'] ) { ?>
                <select name="tag">
                    <option value=""><?php _e( 'View all tags', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php $current = isset( $_GET['tag'] ) ? $_GET['tag'] : '';
                        foreach( $tags as $tag ) { ?>
                            <option value="<?php echo $tag->term_id; ?>" <?php selected( $current, $tag->term_id ); ?>><?php echo $tag->name; ?></option>',
                        <?php } ?>
                </select>
                <?php }

                submit_button( __( 'Filter', WPC_CLIENT_TEXT_DOMAIN ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) ); ?>
            </div>
        <?php }
    }

    function column_clients( $item ) {
        $users = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $item['id'], 'client' );

        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' )  ) {
            //show only manager clients
            $clients = WPC()->members()->get_all_clients_manager();
            $menedger_users = array();
            foreach ( $users as $client_id ) {
                if ( 0 < $client_id ) {
                    if ( !isset( $clients ) || !in_array( $client_id, $clients ) )
                        continue;
                    if( !empty( $client_id ) ) {
                        $menedger_users[] = $client_id;
                    }
                }
            }
            $users = $menedger_users;
        }

        echo '<div class="scroll_data">';

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['id'],
            'value' => implode( ',', $users )
        );
        $additional_array = array(
            'counter_value' => count( $users )
        );
        WPC()->assigns()->assign_popup( 'client', 'wpclients_client_pages', $link_array, $input_array, $additional_array );

        echo '</div>';
    }

    function column_groups( $item ) {
        echo '<div class="scroll_data">';

        $id_array = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $item['id'], 'circle' );
        if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' )  ) {
            $manager_clients = WPC()->assigns()->get_assign_data_by_object( 'manager', get_current_user_id(), 'circle' );
            $id_array = array_intersect( $id_array, $manager_clients );
        }

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => count( $id_array )
        );

        WPC()->assigns()->assign_popup( 'circle', 'wpclients_client_pages', $link_array, $input_array, $additional_array );

        echo '</div>';
    }


    function column_category( $item ) {
        if ( isset( $item['category'] ) ) {
            echo '<div class="scroll_data">' . $item['category'] . '</div>' ;
        }
    }


    function column_tags( $item ) {
        $tags_array = wp_get_object_terms( $item['id'], 'wpc_tags' );
        $tags = array();
        $schema = is_ssl() ? 'https://' : 'http://';
        $current_url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        foreach( $tags_array as $tag ) {
            $tags[] = '<a href="' . add_query_arg( array( 'tag' => $tag->term_id ), $current_url ) . '" class="wpc_tag" title="' . sprintf( __( 'Filter by tag: %s', WPC_CLIENT_TEXT_DOMAIN ), $tag->name ) . '">' . $tag->name . '</a>';
        }
        return '<span class="wpc_file_tags_value">' . implode( ' ', $tags ) . '</span>';
    }


    function column_order( $item ) {
        echo '<div class="scroll_data">';
        echo '<input type="number" name="clientpage_order_' . $item['id'] . '" id="clientpage_order_' . $item['id'] . '" style="width: 70px;" value="' . $item['page_order'] . '" onblur="update_order(' . $item['id'] . ')" />' ;
        echo '<span class="wpc_ajax_loading" style="display:none" id="order_' . $item['id'] . '"></span>' ;
        echo '</div>' ;
    }


    function column_date( $item ) {
        $status = __( 'Last Modified', WPC_CLIENT_TEXT_DOMAIN );
        if ( 'future' == $item['status'] ) {
            $status = __( 'Scheduled', WPC_CLIENT_TEXT_DOMAIN );
        } elseif ( 'publish' == $item['status'] ) {
            $status = __( 'Published', WPC_CLIENT_TEXT_DOMAIN );
        }
        
        return $status . '<br />' . WPC()->date_format( strtotime( $item['date'] ) );
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_Portalpages_List_Table( array(
    'singular'  => WPC()->custom_titles['portal_page']['s'],
    'plural'    => WPC()->custom_titles['portal_page']['p'],
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_portal_pages_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'title'     => 'title',
    'date'      => 'date',
) );

if ( isset( $_GET['filter_status'] ) && 'trash' == $_GET['filter_status'] ) {
    $ListTable->set_bulk_actions(array(
        'untrash'   => __( 'Restore', WPC_CLIENT_TEXT_DOMAIN ),
        'delete'    => __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ),
    ));
} else {
    $ListTable->set_bulk_actions(array(
        'trash'    => __( 'Move to Trash', WPC_CLIENT_TEXT_DOMAIN ),
    ));
}

$columns = array(
    'cb'             => '<input type="checkbox" />',
    'title'          => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
    'clients'        => WPC()->custom_titles['client']['s'],
    'groups'         => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'],
    'category'       => __( 'Category', WPC_CLIENT_TEXT_DOMAIN ),
);

if( !WPC()->flags['easy_mode'] ) {
    $columns['tags'] = __( 'Tags', WPC_CLIENT_TEXT_DOMAIN );
}

$columns['order'] = __( 'Order', WPC_CLIENT_TEXT_DOMAIN );
$columns['date'] = __( 'Date', WPC_CLIENT_TEXT_DOMAIN );

$ListTable->set_columns( $columns );

$sql = "SELECT count( p.ID )
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_category_id' )
    LEFT JOIN {$wpdb->prefix}wpc_client_portal_page_categories cppc ON pm1.meta_value = cppc.cat_id
    WHERE
        p.post_type = 'clientspage'
        {$where_manager}
        {$where_status}
        {$where_clause}
        {$where_date}
        {$where_cat}
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT p.ID as id, p.post_title as title, p.post_date_gmt as date, p.post_status as status, cppc.cat_name as category, pm2.meta_value as page_order
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_category_id' )
    LEFT JOIN {$wpdb->postmeta} pm2 ON( p.ID = pm2.post_id AND pm2.meta_key = '_wpc_order_id' )
    LEFT JOIN {$wpdb->prefix}wpc_client_portal_page_categories cppc ON pm1.meta_value = cppc.cat_id
    WHERE
        p.post_type = 'clientspage'
        {$where_manager}
        {$where_status}
        {$where_clause}
        {$where_date}
        {$where_cat}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$pages = $wpdb->get_results( $sql, ARRAY_A );
$ListTable->prepare_items();
$ListTable->items = $pages;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php if( isset( $_GET['trashed'] ) && 0 < (int)$_GET['trashed'] ) {
        if ( 1 == (int)$_GET['trashed'] ) {
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( '1 post Moved to the Trash.', WPC_CLIENT_TEXT_DOMAIN ). '</p></div>';
        } else {
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s posts Moved to the Trash.', WPC_CLIENT_TEXT_DOMAIN ), (int)$_GET['trashed'] ) . '</p></div>';
        }
    } elseif ( isset( $_GET['untrashed'] ) && 0 < (int)$_GET['untrashed'] ) {
        if ( 1 == (int)$_GET['untrashed'] ) {
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( '1 post Restored from the Trash.', WPC_CLIENT_TEXT_DOMAIN ). '</p></div>';
        } else {
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s posts Restored from to the Trash.', WPC_CLIENT_TEXT_DOMAIN ), (int)$_GET['untrashed'] ) . '</p></div>';
        }
    } elseif ( isset( $_GET['deleted'] ) && 0 < (int)$_GET['deleted'] ) {
        if ( 1 == (int)$_GET['deleted'] ) {
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( '1 post Permanently Deleted.', WPC_CLIENT_TEXT_DOMAIN ). '</p></div>';
        } else {
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s posts Permanently Deleted.', WPC_CLIENT_TEXT_DOMAIN ), (int)$_GET['deleted'] ) . '</p></div>';
        }
    } elseif ( !empty( $_GET['msg'] ) && 'empty_clients' == $_GET['msg'] ) {
        echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Page has not assigned clients.', WPC_CLIENT_TEXT_DOMAIN ). '</p></div>';
    } ?>

    <div class="wpc_clear"></div>

    <style type="text/css">
        .portalpages .column-clients, .portalpages .column-groups, .portalpages .column-order {
            width: 120px;
        }
    </style>
    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <?php global $wpdb;

            $count_all = 0;
            $all_count_status = $wpdb->get_results( "SELECT post_status, count(p.ID) as count
                FROM {$wpdb->posts} p
                WHERE post_type = 'clientspage'
                    {$where_manager}
                    AND post_status IN ('" . implode( "','", $visible_status ) . "')
                GROUP BY post_status", ARRAY_A );
            foreach ( $all_count_status as $status ) {
                if ( 'trash' != $status['post_status'] )
                    $count_all += $status['count'];
            }

            $filter_status = (string)@$_GET['filter_status']; ?>

            <form action="" method="get" name="wpc_portal_pages_form" id="wpc_portal_pages_form" style="width: 100%;">
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="portal_page" />
                <?php if( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_manager' ) ) { ?>
                    <a href="post-new.php?post_type=clientspage" class="add-new-h2" style="float: left;"><?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                <?php } ?>
                <ul class="subsubsub">
                    <li class="all"><a class="<?php echo ( '' == $filter_status ) ? 'current' : '' ?>" href="admin.php?page=wpclients_content&tab=portal_page"><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="count">(<?php echo $count_all ?>)</span></a></li>
                    <?php foreach ( $all_count_status as $status ) {
                        $stat = strtolower( $status['post_status'] );
                        if ( !in_array( $stat, $visible_status )) {
                            continue;
                        }
                        $class = ( $stat == $filter_status ) ? 'current' : '';
                        echo ' | <li class="image"><a class="' . $class . '" href="admin.php?page=wpclients_content&tab=portal_page&filter_status=' . $stat . '">' . ( ( 'publish' == $stat ) ? __( 'Published', WPC_CLIENT_TEXT_DOMAIN ) : ucfirst( $stat ) ) . '<span class="count">(' . $status['count'] . ')</span></a></li>';
                    } ?>
                </ul>
                <?php $ListTable->search_box( sprintf( __( 'Search %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ), 'search-submit' ); ?>
                <?php $ListTable->display(); ?>
            </form>
        </div>

    </div>

    <script type="text/javascript">
        function update_order( post_id ) {
            clientpage_order = jQuery( '#clientpage_order_' + post_id ).val();
            jQuery( '#order_' + post_id ).css( 'display', 'inline-block' );
            jQuery.ajax({
                type: 'POST',
                dataType    : 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: 'action=wpc_portal_pages_update_order&post_id='+post_id+'&clientpage_order='+clientpage_order,
                success: function( json_data ){
                            jQuery( '#order_' + post_id ).css( 'display', 'none' );
                            jQuery( '#clientpage_order_' + post_id ).val( json_data.my_value );
                        }
             });
        }

        jQuery(document).ready(function(){

            //reassign file from Bulk Actions
            jQuery( '#doaction2' ).click( function() {
                var action = jQuery( 'select[name="action2"]' ).val() ;
                jQuery( 'select[name="action"]' ).attr( 'value', action );

                return true;
            });

            //remove extra fields before submit form
            jQuery( '#wpc_portal_pages_form' ).submit( function() {
                jQuery( '.clients_field' ).remove();
                jQuery( '.circles_field' ).remove();
                jQuery( '.column-order input' ).remove();
                return true;
            });
        });
    </script>

</div>