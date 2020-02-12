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

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=client_page_categories';
}

if ( isset( $_POST['action'] ) ) {
    switch ( $_POST['action'] ) {

        //action for create new portal pages category
        case 'create_pp_category':
            check_admin_referer( 'wpc_create_pp_category' . get_current_user_id() );

            //if name is empty
            if( empty( $_POST['wpc_name'] ) ) {
                WPC()->redirect( add_query_arg( 'msg', 'null', $redirect ) );
            }

            //if name exists
            if ( WPC()->pages()->portalpage_category_exists( $_POST['wpc_name'] ) ) {
                WPC()->redirect( add_query_arg( 'msg', 'ce', $redirect ) );
            }

            $args = array(
                'name'    => $_POST['wpc_name'],
                'clients' => ( isset( $_POST['wpc_clients'] ) ) ? $_POST['wpc_clients'] : '',
                'circles' => ( isset( $_POST['wpc_circles'] ) ) ? $_POST['wpc_circles'] : '',
            );
            $id = WPC()->pages()->create_portalpage_category( $args );

            $msg = $id ? 'cr' : 'sw';
            WPC()->redirect( add_query_arg( 'msg', $msg, $redirect ) );

        break;

        //action for create edit portal pages category
        case 'edit_pp_category':
            if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )
                || current_user_can( 'edit_others_clientspages' ) ) {

                //if name is empty
                if( empty( $_POST['wpc_name'] ) || empty( $_POST['id'] ) ) {
                    WPC()->redirect( add_query_arg( 'msg', 'null', $redirect ) );
                }

                check_admin_referer( 'wpc_update_pp_category' . get_current_user_id() . $_POST['id'] );

                //if name exists
                if ( WPC()->pages()->portalpage_category_exists( $_POST['wpc_name'], $_POST['id'] ) ) {
                    WPC()->redirect( add_query_arg( 'msg', 'ce', $redirect ) );
                }

                $args = array(
                    'id'      => $_POST['id'],
                    'name'    => $_POST['wpc_name'],
                    'clients' => ( isset( $_POST['wpc_clients'] ) ) ? $_POST['wpc_clients'] : '',
                    'circles' => ( isset( $_POST['wpc_circles'] ) ) ? $_POST['wpc_circles'] : '',
                );
                WPC()->pages()->update_portalpage_category( $args );

                $msg = 's';
                WPC()->redirect( add_query_arg( 'msg', $msg, $redirect ) );
            }
        break;

        //action for delete portal pages category
        case 'delete_portalpage_category':
            if ( !empty( $_POST['id'] ) ) {

                check_admin_referer( 'wpc_delete_pp_category' . get_current_user_id() .  $_POST['id'] );

                if ( isset( $_POST['reassign_pp'] ) && isset( $_POST['cat_reassign'] ) && 0 < $_POST['cat_reassign'] ) {
                    WPC()->pages()->reassign_portalpage_from_category( $_POST['id'], $_POST['cat_reassign'] );
                }
                WPC()->pages()->delete_portalpage_category( $_POST['id'] );
                $msg = 'd';
            } else {
                $msg = 'sw';
            }
            WPC()->redirect( add_query_arg( 'msg', $msg, $redirect ) );

        break;

        //action for reassing files from one category to another
        case 'reassign_portalpage_from_category':
            WPC()->pages()->reassign_portalpage_from_category($_POST['old_cat_id'], $_POST['new_cat_id']);

            WPC()->redirect( add_query_arg('msg', 'ra', $redirect ));
        break;
    }
}



//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}


global $wpdb;

$order_by = 'cat_id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'cat_name' :
            $order_by = 'cat_name';
            break;
        case 'cat_id' :
            $order_by = 'cat_id';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_PP_Categories_List_Table extends WP_List_Table {

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['cat_id']
        );
    }

    function column_pages( $item ) {
        $args = array(
            'post_type'         => 'clientspage',
            'post_status'       => 'publish',
            'meta_key'          => '_wpc_category_id',
            'meta_value'        => $item['cat_id'],
            'posts_per_page'    => -1,
            'fields'            => 'ids'
         );

        $postslist = get_posts( $args );
        return count( $postslist );
    }

    function column_cat_name( $item ) {
        $actions = array();
        if ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )
                || current_user_can( 'edit_others_clientspages' ) ) {
            $actions['edit'] = '<a href="javascript:void(0);" data-id="' . $item['cat_id'] . '" class="wpc_edit_item">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            $actions['delete'] = '<a href="javascript:void(0);" data-id="' . $item['cat_id'] . '" class="wpc_delete_item">' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }
        return sprintf( '%1$s %2$s', '<span>' . $item['cat_name'] . '</span>', $this->row_actions( $actions ) ) ;
    }

    function column_circles( $item ) {
        $id_array = WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $item['cat_id'], 'circle' );

        $link_array = array(
            'data-id' => $item['cat_id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ) . $item['cat_name']
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['cat_id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => count( $id_array )
        );
        $return = WPC()->assigns()->assign_popup('circle', 'wpclientspage_categories', $link_array, $input_array, $additional_array, false );

        return $return;
    }

    function column_clients( $item ) {
        $id_array = WPC()->assigns()->get_assign_data_by_object( 'portal_page_category', $item['cat_id'], 'client' );

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
            'data-id' => $item['cat_id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . $item['cat_name']
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['cat_id'],
            'value' => implode( ',', $id_array )
        );
        $additional_array = array(
            'counter_value' => $user_count
        );
        $return = WPC()->assigns()->assign_popup('client', 'wpclientspage_categories', $link_array, $input_array, $additional_array, false );

        return $return;
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }
}


$ListTable = new WPC_PP_Categories_List_Table( array(
    'singular'  => __( 'Category', WPC_CLIENT_TEXT_DOMAIN ),
    'plural'    => __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ),
    'ajax'      => false
));

$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_portal_page_categories_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'cat_id'          => 'cat_id',
    'cat_name'        => 'cat_name',
) );

$ListTable->set_bulk_actions(array(
));

$ListTable->set_columns(array(
    'cat_id'            => __( 'Category ID', WPC_CLIENT_TEXT_DOMAIN ),
    'cat_name'          => __( 'Category Name', WPC_CLIENT_TEXT_DOMAIN ),
    'pages'             => __( 'Pages', WPC_CLIENT_TEXT_DOMAIN ),
    'clients'           => WPC()->custom_titles['client']['p'] ,
    'circles'           => WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ,
));

$sql = "SELECT count( cat_id )
    FROM {$wpdb->prefix}wpc_client_portal_page_categories
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT cat_id, cat_name
    FROM {$wpdb->prefix}wpc_client_portal_page_categories
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$groups = $wpdb->get_results( $sql, ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $groups;
$ListTable->wpc_set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block(); ?>

    <?php
        if ( isset( $_GET['msg'] ) ) {
            $msg = $_GET['msg'];
            switch( $msg ) {
                case 'null':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category name is null!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'ce':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category already exists!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'cr':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category has been created.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 's':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The changes of the Category are saved.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'd':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category is deleted.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'sw':
                    echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Something Wrong.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
                case 'ra':
                    echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Data of Categories are reassigned.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                    break;
            }
        }
    ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <a class="add-new-h2 wpc_form_link" id="wpc_new_cat">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>
            <a class="add-new-h2 wpc_form_link" id="wpc_reasign">
                <?php printf( __( 'Reassign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ?>
            </a>

            <form action="" method="get" name="edit_cat" id="edit_cat">
                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="client_page_categories" />
                <?php $ListTable->display(); ?>
            </form>
        </div>

        <div id="new_form_panel">
            <form method="post" action="" class="wpc_form">
                <table class="form-table">
                    <tr>
                        <td>
                            <input type="hidden" name="id" id="wpc_id" />
                            <input type="hidden" name="action" id="wpc_action" />
                            <input type="hidden" name="_wpnonce" id="wpc_wpnonce" />
                            <label for="wpc_name">
                            <?php _e( 'Category Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:<span class="required">*</span>
                            </label>
                            <input type="text" class="input" name="wpc_name" id="wpc_name" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to %s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['portal_page']['s'] ),
                                    'text'    => sprintf( __( 'Assign %s to %s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['portal_page']['s'] ),
                                );
                                $input_array = array(
                                    'name'  => 'wpc_clients',
                                    'id'    => 'wpc_clients',
                                    'value' => '',
                                );
                                $additional_array = array(
                                    'counter_value' => 0
                                );
                                WPC()->assigns()->assign_popup('client', 'wpclientspage_categories', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s to %s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] . ' ' . WPC()->custom_titles['circle']['p'], WPC()->custom_titles['portal_page']['s'] ),
                                    'text'    => sprintf( __( 'Assign %s to %s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] . ' ' . WPC()->custom_titles['circle']['p'], WPC()->custom_titles['portal_page']['s'] ),
                                );
                                $input_array = array(
                                    'name'  => 'wpc_circles',
                                    'id'    => 'wpc_circles',
                                    'value' => '',
                                );
                                $additional_array = array(
                                    'counter_value' => 0
                                );
                                WPC()->assigns()->assign_popup('circle', 'wpclientspage_categories', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                </table>
                <br>
                <div class="save_button">
                    <input type="submit" class="button-primary wpc_submit" id="save_pp_category" value="<?php printf( __( 'Save %s Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ) ?>" />
                </div>
            </form>
        </div>

        <div id="reasign_form_panel">
            <form method="post" class="wpc_form" name="reassign_portalpages_cat" id="reassign_portalpages_cat" >
                <input type="hidden" name="action" value="reassign_portalpage_from_category" />
                <table class="form-table">
                    <tr>
                        <td>
                            <?php _e( 'Category From', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                        </td>
                        <td>
                            <select name="old_cat_id" id="old_cat_id">
                                <?php
                                $categories = WPC()->categories()->get_clientspage_categories();
                                foreach( $categories as $cat) {
                                        echo '<option value="' . $cat['id'] . '">' . $cat['name'] . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php _e( 'Category To', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                        </td>
                        <td>
                            <select name="new_cat_id" id="new_cat_id">
                                <?php foreach( $categories as $cat) {
                                    echo '<option value="' . $cat['id'] . '">' . $cat['name'] . '</option>';
                                } ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="save_button">
                    <input type="submit" class="button-primary wpc_submit" name="reassign_portalpages" value="<?php _e( 'Reassign', WPC_CLIENT_TEXT_DOMAIN ) ?>" id="reassign_portalpages" />
                </div>
            </form>
        </div>

        <script type="text/javascript">
            function set_data( data ) {
                if( data.action === undefined ) {
                    //clear
                    jQuery( '#wpc_id' ).val( '' );
                    jQuery( '#wpc_action' ).val( '' );
                    jQuery( '#wpc_wpnonce' ).val( '' );
                    jQuery( '#wpc_name' ).val( '' );
                    jQuery( '#wpc_clients' ).val( '' );
                    jQuery( '.counter_wpc_clients' ).text( '(0)' );
                    jQuery( '#wpc_circles' ).val( '' );
                    jQuery( '.counter_wpc_circles' ).text( '(0)' );
                } else if( 'edit_pp_category' === data.action ) {
                    //edit
                    jQuery( '#wpc_id' ).val( data.id );
                    jQuery( '#wpc_action' ).val( data.action );
                    jQuery( '#wpc_wpnonce' ).val( data.wpnonce );
                    jQuery( '#wpc_name' ).val( data.params.name );
                    jQuery( '#wpc_clients' ).val( data.clients );
                    jQuery( '.counter_wpc_clients' ).text( '(' + data.count_clients + ')' );
                    jQuery( '#wpc_circles' ).val( data.circles );
                    jQuery( '.counter_wpc_circles' ).text( '(' + data.count_circles + ')' );
                } else {
                    //create
                    jQuery( '#wpc_id' ).val( '' );
                    jQuery( '#wpc_action' ).val( data.action );
                    jQuery( '#wpc_wpnonce' ).val( data.wpnonce );
                    jQuery( '#wpc_name' ).val( '' );
                    jQuery( '#wpc_clients' ).val( '' );
                    jQuery( '.counter_wpc_clients' ).text( '(0)' );
                    jQuery( '#wpc_circles' ).val( '' );
                    jQuery( '.counter_wpc_circles' ).text( '(0)' );
                }
            }

            jQuery( document ).ready( function() {

                //reassign file from Bulk Actions
                jQuery( '#doaction2' ).click( function() {
                    var action = jQuery( 'select[name="action2"]' ).val() ;
                    jQuery( 'select[name="action"]' ).attr( 'value', action );

                    return true;
                });

                jQuery( '#wpc_reasign' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'inline',
                    href            : '#reasign_form_panel',
                    title           : '<?php echo esc_js( sprintf( __( 'Reassign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['p'] ) ); ?>',
                    onClose         : function() {
                        set_data( '' );
                    }
                });

                jQuery( '#wpc_new_cat, .wpc_edit_item' ).each( function() {
                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '500px',
                        type            : 'inline',
                        href            : '#new_form_panel',
                        title           : ( 'wpc_new_cat' === jQuery( this ).prop('id') )
                            ? '<?php echo esc_js( sprintf( __( 'New %s Category', WPC_CLIENT_TEXT_DOMAIN ),
                    WPC()->custom_titles['portal_page']['s'] ) ); ?>'
                            : '<?php echo esc_js( sprintf( __( 'Edit %s Category', WPC_CLIENT_TEXT_DOMAIN ),
                    WPC()->custom_titles['portal_page']['s'] ) ); ?>',
                        onClose         : function() {
                            set_data( '' );
                        }
                    });
                });

                jQuery( '#wpc_new_cat, .wpc_edit_item').click( function() {
                    var obj = jQuery(this);
                    var id = obj.data('id');

                    obj.shutter_box('showPreLoader');
                    jQuery.ajax({
                        type        : 'POST',
                        dataType    : 'json',
                        url         : '<?php echo get_admin_url() ?>admin-ajax.php',
                        data        : "action=get_data_pp_category&id=" + id,
                        success     : function( data ) {
                            set_data( data );
                        },
                        error: function(data) {
//                            obj.shutter_box('close');
                        }
                    });
                });

                jQuery( '.wpc_delete_item').each( function() {
                    var id = jQuery(this).data('id');

                    jQuery(this).shutter_box({
                        view_type       : 'lightbox',
                        width           : '500px',
                        type            : 'ajax',
                        dataType        : 'json',
                        href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                        ajax_data       : "action=get_html_delete_pp_category&id=" + id,
                        setAjaxResponse : function( data ) {
                            jQuery( '.sb_lightbox_content_title' ).html( data.title );
                            jQuery( '.sb_lightbox_content_body' ).html( data.content );
                        }
                    });
                });

                //Click for Save
                jQuery('body').on('click', '#save_pp_category', function() {
                    if ( !jQuery(this).parents( 'form').find("#wpc_name" ).val() ) {
                        jQuery(this).parents( 'form').find("#wpc_name" ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    } else {
                        jQuery(this).parents('form').submit();
                    }
                });

                //Click for Delete
                jQuery('body').on('click', '#delete_pp_category, #wpc_delete_pp, #wpc_reassign_pp', function() {
                    jQuery(this).parents('form').submit();
                });


                //Reassign files to another cat
                jQuery( '#reassign_portalpages' ).click( function() {
                    if ( jQuery( '#old_cat_id' ).val() == jQuery( '#new_cat_id' ).val() ) {
                        jQuery( '#old_cat_id' ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    }
                    jQuery( '#reassign_portalpages_cat' ).submit();
                    return false;
                });

            });
        </script>

    </div>

</div>