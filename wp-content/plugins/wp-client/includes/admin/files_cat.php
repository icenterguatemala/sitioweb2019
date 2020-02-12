<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( WPC()->flags['easy_mode'] ) {
    WPC()->redirect( admin_url( 'admin.php?page=wpclients_content' ) );
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_content&tab=files_categories';
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    WPC()->redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}


global $wpdb;

$order_by = 'fc.cat_order';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'cat_name' :
            $order_by = 'fc.cat_name';
            break;
        case 'cat_id' :
            $order_by = 'fc.cat_id';
            break;
        case 'cat_order' :
            $order_by = 'fc.cat_order';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'DESC' : 'ASC';

$sql = "SELECT count( cat_id )
    FROM {$wpdb->prefix}wpc_client_file_categories
    ";
$items_count = $wpdb->get_var( $sql ); ?>

<div class="wrap">

    <?php echo WPC()->admin()->get_plugin_logo_block() ?>

    <?php
        if ( isset( $_GET['msg'] ) ) {
            $msg = $_GET['msg'];
            switch($msg) {
            case 'null':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category name is null!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fnull':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category Folder Name is null!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'cne':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category with this name already exists!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fne':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category with this folder already exists!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fnerr':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'Category Folder Name Error!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'fe':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Category Folder already exists on FTP!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'cr':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category has been created!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'reas':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category is reassigned!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 's':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'The changes of the Category are saved!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Category is deleted!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            }
        }
    ?>

    <div class="wpc_clear"></div>

    <div id="wpc_container">

        <?php echo WPC()->admin()->gen_tabs_menu( 'content' ) ?>

        <span class="wpc_clear"></span>

        <div class="wpc_tab_container_block">

            <a class="add-new-h2 wpc_form_link" id="wpc_new">
                <?php _e( 'Add New', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>
            <a class="add-new-h2 wpc_form_link" id="wpc_reasign">
                <?php _e( 'Reassign Files', WPC_CLIENT_TEXT_DOMAIN ) ?>
            </a>
            <span class="display_link_block">
                <a class="display_link selected_link" href="#"><?php _e( 'Tree View', WPC_CLIENT_TEXT_DOMAIN ) ?></a> |
                <a class="display_link" href="admin.php?page=wpclients_content&tab=files_categories&display=old"><?php _e( 'List View', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
            </span>

            <div id="new_form_panel">
                <form method="post" name="new_cat" id="new_cat" >
                    <input type="hidden" name="wpc_action" value="create_file_cat" />
                    <table class="">
                        <tr>
                            <td style="width: 120px;">
                                <label for="cat_name_new"><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="cat_name_new" id="cat_name_new" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="cat_folder_new"><?php _e( 'Folder name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="cat_folder_new" id="cat_folder_new" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="parent_cat"><?php _e( 'Parent', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="parent_cat" id="parent_cat">
                                    <option value="0"><?php _e( '(no parent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign %s to File Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                        'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_clients',
                                        'id'    => 'wpc_clients',
                                        'value' => ''
                                    );
                                    $additional_array = array(
                                        'counter_value' => 0
                                    );
                                    WPC()->assigns()->assign_popup('client', 'wpclients_filescat', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?>:</label>
                            </td>
                            <td>
                                <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign %s to File Category', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                        'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                                    );
                                    $input_array = array(
                                        'name'  => 'wpc_circles',
                                        'id'    => 'wpc_circles',
                                        'value' => ''
                                    );
                                    $additional_array = array(
                                        'counter_value' => 0
                                    );
                                    WPC()->assigns()->assign_popup('circle', 'wpclients_filescat', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div class="save_button">
                        <input type="submit" class='button-primary' value="<?php _e( 'Create Category', WPC_CLIENT_TEXT_DOMAIN ) ?>" name="create_cat" />
                    </div>
                </form>
            </div>

            <div id="reasign_form_panel">
                <form method="post" name="reassign_files_cat" id="reassign_files_cat" >
                    <input type="hidden" name="wpc_action" id="wpc_action3" value="" />
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 120px;">
                                <label for="old_cat_id"><?php _e( 'Category From', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="old_cat_id" id="old_cat_id">
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="new_cat_id"><?php _e( 'Category To', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                            </td>
                            <td>
                                <select name="new_cat_id" id="new_cat_id">
                                    <?php WPC()->files()->render_category_list_items(); ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <br />
                    <div class="save_button">
                        <input type="button" class="button-primary" name="" value="<?php _e( 'Reassign', WPC_CLIENT_TEXT_DOMAIN ) ?>" id="reassign_files" />
                    </div>
                </form>
            </div>

            <p>
                <span class="description" >
                    <?php _e( 'Drag&Drop the below items to change the hierarchy of categories, such as for creating sub-categories.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </span>
            </p>

            <form action="" method="get" name="edit_cat" id="edit_cat">
                <input type="hidden" name="wpc_action" id="wpc_action2" value="" />
                <input type="hidden" name="cat_id" id="cat_id" value="" />
                <input type="hidden" name="reassign_cat_id" id="reassign_cat_id" value="" />

                <input type="hidden" name="page" value="wpclients_content" />
                <input type="hidden" name="tab" value="files_categories" />

                <table style="margin-top:5px;float: left;width:100%;font-weight: 400;font-size: 14px;font-family: 'Open Sans',sans-serif;line-height: 1.4em;color: #333;">
                    <tr>
                        <td><?php _e( 'Category Name (#ID)', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td style="width: 290px;"><?php _e( 'Folder Name', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td style="width: 100px;text-align: center;"><?php _e( 'Files', WPC_CLIENT_TEXT_DOMAIN ) ?></td>
                        <td style="width: 100px;text-align: center;"><?php echo WPC()->custom_titles['client']['p'] ?></td>
                        <td style="width: 100px;text-align: center;"><?php echo WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ?></td>
                    </tr>
                </table>
                <ol class="sortable hidden_content" style="position:relative;">
                    <li class="sortable_loading" style="background:#fff;height:100%;position:absolute;width:100%;top:0;left:0;margin:0;padding:0;"><div class="ajax_sort_loading"></div></li>
                    <?php echo WPC()->files()->render_children_file_categories_html(); ?>
                </ol>
            </form>


        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';

            jQuery( document ).ready( function() {

                jQuery( '#wpc_new' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'inline',
                    href            : '#new_form_panel',
                    title           : '<?php echo esc_js( __( 'New File Category', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                });

                jQuery( '#wpc_reasign' ).shutter_box({
                    view_type       : 'lightbox',
                    width           : '500px',
                    type            : 'inline',
                    href            : '#reasign_form_panel',
                    title           : '<?php echo esc_js( __( 'Reassign Files Category', WPC_CLIENT_TEXT_DOMAIN ) ); ?>'
                });


                var group_name  = "";
                var folder_name = "";

                jQuery.fn.editGroup = function ( id, action ) {
                    if ( action == 'edit' ) {
                        jQuery( '#cat_id_block_' + id ).hide();

                        group_name = jQuery( '#cat_name_block_' + id ).html();
                        group_name = group_name.replace(/(^\s+)|(\s+$)/g, "");

                        folder_name = jQuery( '#folder_name_block_' + id ).html();
                        folder_name = folder_name.replace(/(^\s+)|(\s+$)/g, "");

                        jQuery( '#cat_name_block_' + id ).html( '<input type="text" name="cat_name" size="15" id="edit_cat_name"  value="' + group_name + '" /><input type="hidden" name="cat_id" value="' + id + '" />' );
                        jQuery( '#folder_name_block_' + id ).html( '<input type="text" name="folder_name" size="15" id="edit_folder_name"  value="' + folder_name + '" />' );

                        jQuery( '#edit_cat input[type="button"]' ).attr( 'disabled', true );

                        jQuery( this ).parent().parent().attr('style', "display:none" );
                        jQuery( '#save_or_close_block_' + id ).attr('style', "display:block;" );

                        return '';

                    } else if ( action == 'close' ) {
                        jQuery( '#cat_id_block_' + id ).show();
                        jQuery( '#cat_name_block_' + id ).html( group_name );
                        jQuery( '#folder_name_block_' + id ).html( folder_name );

                        jQuery( '#save_or_close_block_' + id ).attr('style', "display:none;" );
                        jQuery( this ).parent().next().attr('style', "display:block" );

                        return '';
                    }


                };


                jQuery.fn.saveGroup = function ( ) {

                    jQuery( '#edit_cat_name' ).parent().parent().removeClass( 'wpc_error' );

                    if ( '' == jQuery( '#edit_cat_name' ).val() ) {
                        jQuery( '#edit_cat_name' ).parent().parent().addClass( 'wpc_error' );
                        return false;
                    }

                    jQuery( '#wpc_action2' ).val( 'edit_file_cat' );
                    jQuery( '#edit_cat' ).submit();
                };


                //block for delete cat
                jQuery.fn.deleteCat = function ( id, act ) {
                    if ( 'show' == act ) {
                        jQuery( '#cat_reassign_block_' + id ).slideToggle( 'slow' );

                        if( jQuery(this).html() == '<?php echo esc_js( __( 'Cancel Delete', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' ) {
                            jQuery(this).html( '<?php echo esc_js( __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' );
                        } else {
                            jQuery(this).html( '<?php echo esc_js( __( 'Cancel Delete', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' );
                        }

                    } else if( 'reassign' == act ) {
                        if( confirm("<?php echo esc_js( __( 'Are you sure want to delete permanently this category and reassign all files and parent categories to another category? ', WPC_CLIENT_TEXT_DOMAIN ) ) ?>") ) {
                            jQuery( '#wpc_action2' ).val( 'delete_file_category' );
                            jQuery( '#cat_id' ).val( id );
                            jQuery( '#reassign_cat_id' ).val( jQuery( '#cat_reassign_block_' + id + ' select' ).val() );
                            jQuery( '#edit_cat' ).submit();
                        }
                    } else if( 'delete' == act ) {
                        if( confirm("<?php echo esc_js( __( 'Are you sure want to delete permanently this category with all files and parent categories? ', WPC_CLIENT_TEXT_DOMAIN ) ) ?>") ) {
                            jQuery( '#wpc_action2' ).val( 'delete_file_category' );
                            jQuery( '#cat_id' ).val( id );
                            jQuery( '#edit_cat' ).submit();
                        }
                    }
                };


                //Reassign files to another cat
                jQuery( '#reassign_files' ).click( function() {
                    if ( jQuery( '#old_cat_id' ).val() == jQuery( '#new_cat_id' ).val() ) {
                        jQuery( '#old_cat_id' ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    }
                    jQuery( '#wpc_action3' ).val( 'reassign_files_from_category' );
                    jQuery( '#reassign_files_cat' ).submit();
                    return false;
                });


                jQuery( 'input[name=create_cat]' ).click( function() {
                    if( jQuery( '#cat_name_new' ).val() != '' ) {
                        return true;
                    }
                    return false;
                });

                // sortable
                jQuery('ol.sortable').nestedSortable({
                    forcePlaceholderSize: true,
                    handle: 'div',
                    helper: 'clone',
                    items: 'li:not(.sortable_loading)',
                    opacity: .6,
                    placeholder: 'placeholder',
                    revert: 250,
                    tabSize: 25,
                    tolerance: 'pointer',
                    toleranceElement: '> div',
                    listType: 'ol',

                    isTree: true,
                    expandOnHover: 700,
                    startCollapsed: true,
                    hoveringClass: 'wpc_category-hovering',
                    collapsedClass: 'wpc_category-collapsed',
                    expandedClass: 'wpc_category-expanded',
                    branchClass: 'wpc_category-branch',
                    leafClass: 'wpc_category-leaf',

                    update: function (event, ui) {
                        var data = jQuery(this).nestedSortable( 'toArray' );
                        var currentItem = jQuery(this).nestedSortable( 'getCurrentItem', {currentID: jQuery( ui.item ).attr('id')} );

                        var height = jQuery( 'ol.sortable' ).height();
                        jQuery( 'ol.sortable' ).prepend( '<li class="sortable_loading" style="background:rgba(255, 255, 255, 0.75);height:' + height + 'px;position:absolute;width:100%;top:0;left:0;margin:0;padding:0;"><div class="ajax_sort_loading"></div></li>' );

                        jQuery( 'body' ).css( 'cursor', 'wait' );
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo get_admin_url() ?>admin-ajax.php',
                            data: {
                                'action':'change_cat_order',
                                'new_order': jQuery.base64Encode( window.JSON.stringify(data) ),
                                'current_item': jQuery.base64Encode( window.JSON.stringify(currentItem) )
                            },
                            success: function( html ) {
                                if( html == '<?php echo esc_js( __( 'This folder is already exist in this level', WPC_CLIENT_TEXT_DOMAIN ) ) ?>' ) {
                                    alert( html );
                                    jQuery( 'ol.sortable .sortable_loading' ).remove();
                                    jQuery( 'ol.sortable' ).nestedSortable( 'cancel' );
                                } else {
                                    jQuery( 'ol.sortable .sortable_loading' ).remove();
                                    jQuery( 'body' ).css( 'cursor', 'default' );
                                }
                            }
                        });
                    },
                    create: function( event, ui ) {
                        jQuery( 'ol.sortable .sortable_loading' ).remove();
                        jQuery( 'ol.sortable' ).removeClass('hidden_content');
                    }
                });


                jQuery('.disclose').on('click', function() {
                    jQuery(this).parent().parent().toggleClass('wpc_category-collapsed').toggleClass('wpc_category-expanded');
                });

            });
        </script>

    </div>

</div>