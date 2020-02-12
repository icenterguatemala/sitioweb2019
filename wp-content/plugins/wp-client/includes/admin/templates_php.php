<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//check auth
if ( !current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' )
        && !current_user_can( 'wpc_view_shortcode_templates' ) && !current_user_can( 'wpc_edit_shortcode_templates' ) ) {
    WPC()->redirect( get_admin_url( 'index.php' ) );
}

$can_edit = ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )
        || current_user_can( 'wpc_edit_shortcode_templates' ) ) ? true : false;

function wpc_get_diff_templates( $template_slug = '', $temp_dir = '' ) {
    if ( !empty( $template_slug ) ) {
        $wpc_shortcode_template = WPC()->get_settings( 'shortcode_template_' . $template_slug );

        $db_template = '';
        $template_id = $template_slug . '_diff_popup_block';
        $templates_dir = ( '' != $temp_dir ) ? $temp_dir : WPC()->plugin_dir . 'includes/templates/';

        if ( empty( $wpc_shortcode_template ) ) {
            if ( file_exists( $templates_dir . $template_slug . '.tpl' ) ) {
                $db_template = file_get_contents( $templates_dir . $template_slug . '.tpl'  );
            }
        } else {
            $db_template = $wpc_shortcode_template;
        }

        if ( file_exists( $templates_dir . $template_slug . '.tpl'  ) ) {
            $file_template = file_get_contents( $templates_dir . $template_slug . '.tpl'  );
        } else {
            $file_template = '';
        }


        ob_start(); ?>

        <div id="<?php echo $template_id ?>" style="display: none; width: 1050px;">
            <div class="postbox db" style="float: left; margin: 10px; width: 500px;">
                <h3 style="cursor: default; padding: 8px 0 8px 8px;"><?php _e( 'Your Template', WPC_CLIENT_TEXT_DOMAIN ) ?>:</h3>
                <input type="button" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-primary update_template" />
                <textarea class="db_template"><?php echo $db_template ?></textarea>
            </div>
            <div class="postbox file" style="float: left; margin: 10px; width: 500px;">
                <h3 style="cursor: default; padding: 8px 0 8px 8px;"><?php _e( 'Default Template', WPC_CLIENT_TEXT_DOMAIN ) ?>:</h3>
                <textarea class="file_template" readonly="readonly" disabled="disabled"><?php echo $file_template ?></textarea>
            </div>
            <br />
            <div class="postbox compare" style="float: left; margin: 10px; width: 1020px;">
                <h3 style="cursor: default; padding: 8px 0 8px 8px;"><?php _e( 'Compare', WPC_CLIENT_TEXT_DOMAIN ) ?>:</h3>
                <div class="compare_template"></div>
            </div>
            <br />
            <br />
        </div>

        <?php
        $new_content = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $new_content;
    } else {
        return '';
    }

}


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_templates&tab=php_templates';
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Shortcode_Templates_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $columns = array();
    var $bulk_actions = array();
    var $template_tags = array();

    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'Template', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'Templates', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => true
        ) );

        $this->no_items_message = $args['plural'] . ' ' . __(  'not found.', WPC_CLIENT_TEXT_DOMAIN );

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


    /**
     * Generates content for a single row of the table
     *
     * @since 3.1.0
     * @access public
     *
     * @param object $item The current item
     */
    public function single_row( $item ) {
        $classes = '';
        if ( ! empty( $item['tags'] ) ) {
            $tags_array = explode( ',', $item['tags'] );

            foreach ( $tags_array as $tag ) {
                $classes .= sanitize_title( trim( $tag ) ) . '_tag ';
            }
        }
        echo '<tr class="' . $classes . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }


    function set_columns( $args = array() ) {
        if( count( $this->bulk_actions ) ) {
            $args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
        }
        $this->columns = $args;
        return $this;
    }

    function column_template( $item ) {
        $actions = array();
        if( $item['endpoint_dir'] == $item['dir'] ) {
            $actions['view_template'] = '<a href="javascript: void(0);">' . __( 'View Template', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            $actions['copy_to_theme'] = '<a href="javascript: void(0);">' . __( 'Copy to Theme Directory', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        } else {
            $actions['edit_template'] = '<a href="javascript: void(0);">' . __( 'Edit Template', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            $actions['delete'] = '<a href="javascript: void(0);">' . __( 'Delete Template from Theme Directory', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        return '<div class="template_icon dashicons ' . ( $item['endpoint_dir'] == $item['dir'] ? 'dashicons-media-default grey' : 'dashicons-welcome-write-blog orange' ) . '" 
        title="' . ( $item['endpoint_dir'] == $item['dir'] ? __( 'Template situated at plugin dir', WPC_CLIENT_TEXT_DOMAIN ) : __( 'Template situated at theme dir and can be edit', WPC_CLIENT_TEXT_DOMAIN ) ) . '"
        data-lock_title="' . __( 'Template situated at plugin dir', WPC_CLIENT_TEXT_DOMAIN ) . '" 
        data-unlock_title="' . __( 'Template situated at theme dir and can be edit', WPC_CLIENT_TEXT_DOMAIN ) . '"></div>
        <div style="float:left;width:calc( 100% - 40px );">
        <span class="wpc_shortcode_template" data-name="' . $item['filename'] . '" data-path="' . $item['path'] . '"   
            data-nonce="' . wp_create_nonce( $item['path'] . $item['filename'] ) . '">' .
                $item['title'] .
            '</span> ' . $this->row_actions( $actions ) . '</div>';
    }


    function column_description( $item ) {
        $content = '';
        if ( ! empty( $item['tags'] ) ) {
            $tags_array = explode( ',', $item['tags'] );

            foreach ( $tags_array as $tag ) {
                $tag = trim( $tag );
                $content .= '<div class="template_tag_table" data-tag="' . sanitize_title( $tag ) . '">' . $tag . '</div>';
            }
        }

        return '<span style="float:left;clear:both;width:100%;">' . $item['description'] . '</span><div class="tags" style="float:right;clear:both;margin-top:7px;">' . $content . '</div>';
    }


    function wpc_set_pagination_args( $attr = array() ) {
        $this->set_pagination_args( $attr );
    }

    function extra_tablenav( $which ) {
        if ( 'top' == $which ) { ?>
            <div class="alignleft actions">
                <div class="template_tag active" data-tag=""><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                <?php foreach ( $this->template_tags as $tag ) { ?>
                    <div class="template_tag" data-tag="<?php echo sanitize_title( $tag ) ?>"><?php echo $tag ?></div>
                <?php } ?>
            </div>
        <?php }
    }
}

$ListTable = new WPC_Shortcode_Templates_List_Table();

//$per_page   = WPC()->admin()->get_list_table_per_page( 'wpc_templates_per_page' );
$per_page   = 99999;
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'template' => 'template',
) );

$ListTable->set_columns(array(
    'template' => __( 'Template Name', WPC_CLIENT_TEXT_DOMAIN ),
    'description' => __( 'Description', WPC_CLIENT_TEXT_DOMAIN )
));

$wpc_shortcodes_array = WPC()->templates()->get_php_templates();

if ( ! empty( $_GET['s'] ) ) {
    $wpc_shortcodes_array = array_filter( $wpc_shortcodes_array, function( $innerArray ) {
        $needle = strtolower( trim( $_GET['s'] ) );

        if ( ! empty( $innerArray['title'] ) ) {
            if ( strpos( strtolower( $innerArray['title'] ), $needle ) !== false || strpos( strtolower( $innerArray['description'] ), $needle ) !== false ) {
                return $innerArray;
            }
        }
    });
}

$tags = array();
foreach ( $wpc_shortcodes_array as $php_template ) {
    if ( ! empty( $php_template['tags'] ) ) {
        foreach ( explode( ',', $php_template['tags'] ) as $tag ) {
            $tags[] = trim( $tag );
        }
    }
}

$ListTable->template_tags = array_unique( $tags );

$ListTable->prepare_items();
$ListTable->items = array_slice( $wpc_shortcodes_array, ( $paged - 1 ) * $per_page, $per_page );
$ListTable->wpc_set_pagination_args( array( 'total_items' => count( $wpc_shortcodes_array ), 'per_page' => $per_page ) ); ?>

<style type="text/css">
    .dashicons.grey {
        color: #cfcfcf;
    }

    .dashicons.orange {
        color: #d54e21;
    }

    .template_icon {
        float:left;
        width:30px;
        line-height:30px;
        font-size:24px;
        margin-right:10px;
    }

    .column-description {
        width:65%;
    }
</style>
<script type="text/javascript" language="javascript">
    jQuery(document).ready(function($) {
        var lock_unlock = {};

        jQuery('body').on('click', ".template_tag", function() {
            var tag = jQuery(this).data('tag');
            var disp_arr;
            if ( tag == '' ) {
                clear_hash();
                jQuery(".template_tag").removeClass('active');
                var tag_rows = jQuery( 'table.templates tbody tr' );
                tag_rows.show();

                disp_arr = jQuery( '.displaying-num' ).html().split(' ');
                disp_arr[0] = tag_rows.length;
                jQuery( '.displaying-num' ).html( disp_arr.join(' ') );
                jQuery( this ).toggleClass('active');
                return;
            }

            jQuery('.template_tag[data-tag=""]').removeClass('active');
            jQuery( this ).toggleClass('active');
            jQuery( 'table.templates tbody tr' ).hide();
            hash_data = {};

            if ( ! jQuery('.template_tag.active').length ) {
                jQuery('.template_tag[data-tag=""]').trigger('click');
                return;
            }

            jQuery('.template_tag.active').each( function(e) {
                var tag = jQuery(this).data('tag');
                var tag_rows;

                tag_rows = jQuery( 'table.templates tbody tr.' + tag + '_tag' );
                hash_data[tag] = 1;
                tag_rows.show();
            });

            window.location.hash = get_hash_string();

            disp_arr = jQuery( '.displaying-num' ).html().split(' ');
            disp_arr[0] = jQuery('table.templates tbody tr:visible').length;
            jQuery( '.displaying-num' ).html( disp_arr.join(' ') );
        });


        //click at tag in table
        jQuery(".template_tag_table").click( function() {
            var tag = jQuery(this).data('tag');

            if ( ! jQuery('.template_tag.active[data-tag="' + tag + '"]').length )
                jQuery('.template_tag[data-tag="' + tag + '"]').trigger('click');
        });

        function init_view_template() {
            $('.wp-list-table .view_template a:not(.inited)').each( function() {
                var name = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('name'),
                    path = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('path'),
                    nonce = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('nonce'),
                    $obj = $(this);

                $(this).addClass('inited');

                $(this).shutter_box({
                    view_type       : 'lightbox',
                    width           : '10000px',
                    height          : '10000px',
                    type            : 'ajax',
                    dataType        : 'json',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data       : {
                        action : 'wpc_shortcode_templates',
                        operation : 'view_template',
                        filename : name,
                        path : path,
                        nonce : nonce
                    },
                    setAjaxResponse : function( data ) {
                        if( data.success ) {
                            $('.sb_lightbox_content_title').text(name);
                            $('.sb_lightbox_content_body').css('position', 'relative').html($('.wpc_template_form').html());
                            $('.sb_lightbox_content_body').find('.wpc_save_template').hide();
                            $('.sb_lightbox_content_body').find('.remove_from_theme').hide();
                            $('.sb_lightbox_content_body').find('.wpc_template_content').prop('disabled', true)
                                .data( 'name', name )
                                .data( 'path', path )
                                .data( 'nonce', nonce )
                                .val( data.data.content );
                        } else {
                            alert( data.data[0].message );
                        }
                    },
                    afterClose : function() {
                        if ( lock_unlock.hasOwnProperty(name)  ) {
                            if ( lock_unlock[name] == 'lock' ) {
                                jQuery('.wpc_shortcode_template[data-name="' + name + '"]').siblings('.row-actions').html(
                                    '<span class="view_template"><a href="javascript: void(0);"><?php _e( 'View Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a> | </span>' +
                                    '<span class="copy_to_theme"><a href="javascript: void(0);"><?php _e( 'Copy to Theme Directory', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>'
                                );
                                init_view_template();
                            } else if ( lock_unlock[name] == 'unlock' ) {
                                jQuery('.wpc_shortcode_template[data-name="' + name + '"]').siblings('.row-actions').html(
                                    '<span class="edit_template"><a href="javascript: void(0);"><?php _e( 'Edit Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a> | </span>' +
                                    '<span class="delete"><a href="javascript: void(0);"><?php _e( 'Delete Template from Theme Directory', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>'
                                );
                                init_edit_template();
                            }
                        }
                    }
                });
            });
        }

        function init_edit_template() {
            $('.wp-list-table .edit_template a:not(.inited)').each( function() {
                var name = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('name'),
                    path = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('path'),
                    nonce = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('nonce'),
                    $obj = $(this);

                $(this).addClass('inited');

                $(this).shutter_box({
                    view_type       : 'lightbox',
                    width           : '10000px',
                    height          : '10000px',
                    type            : 'ajax',
                    dataType        : 'json',
                    href            : '<?php echo get_admin_url() ?>admin-ajax.php',
                    ajax_data       : {
                        action : 'wpc_shortcode_templates',
                        operation : 'edit_template',
                        filename : name,
                        path : path,
                        nonce : nonce
                    },
                    setAjaxResponse : function( data ) {
                        if( data.success ) {
                            $('.sb_lightbox_content_title').text(name);
                            $('.sb_lightbox_content_body').css('position', 'relative').html($('.wpc_template_form').html());
                            $('.sb_lightbox_content_body').find('.copy_to_theme').hide();
                            $('.sb_lightbox_content_body').find('.wpc_template_content')
                                .data( 'name', name )
                                .data( 'path', path )
                                .data( 'nonce', nonce ).val( data.data.content );
                        } else {
                            alert( data.data[0].message );
                        }
                    },
                    afterClose : function() {
                        if ( lock_unlock.hasOwnProperty(name)  ) {
                            if ( lock_unlock[name] == 'lock' ) {
                                jQuery('.wpc_shortcode_template[data-name="' + name + '"]').siblings('.row-actions').html(
                                    '<span class="view_template"><a href="javascript: void(0);"><?php _e( 'View Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a> | </span>' +
                                    '<span class="copy_to_theme"><a href="javascript: void(0);"><?php _e( 'Copy to Theme Directory', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>'
                                );
                                init_view_template();
                            } else if ( lock_unlock[name] == 'unlock' ) {
                                jQuery('.wpc_shortcode_template[data-name="' + name + '"]').siblings('.row-actions').html(
                                    '<span class="edit_template"><a href="javascript: void(0);"><?php _e( 'Edit Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a> | </span>' +
                                    '<span class="delete"><a href="javascript: void(0);"><?php _e( 'Delete Template from Theme Directory', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>'
                                );
                                init_edit_template();
                            }
                        }
                    }
                });
            });
        }

        $('body').on( 'click', '.wp-list-table .copy_to_theme a', function() {
            var name = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('name'),
                path = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('path'),
                nonce = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('nonce'),
                $obj = $(this);
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: {
                    action : 'wpc_shortcode_templates',
                    operation : 'copy_to_theme',
                    filename : name,
                    path : path,
                    nonce : nonce
                },
                success: function( data ) {
                    if( data.success ) {
                        window.location.href = '<?php echo WPC()->get_current_url() ?>' + window.location.hash;
                        location.reload();
                    } else {
                        alert( data.data[0].message );
                    }
                }
            });
        });

        $('body').on('click', '.sb_lightbox_content_body .copy_to_theme', function() {
            var name = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('name'),
                path = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('path'),
                nonce = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('nonce'),
                $obj = $(this);
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: {
                    action : 'wpc_shortcode_templates',
                    operation : 'copy_to_theme',
                    filename : name,
                    path : path,
                    nonce : nonce
                },
                success: function( data ) {
                    if( data.success ) {
                        $obj.hide();
                        $obj.closest('.sb_lightbox_content_body').find('.wpc_save_template').show();
                        $obj.closest('.sb_lightbox_content_body').find('.remove_from_theme').show();
                        $obj.closest('.sb_lightbox_content_body').find('.wpc_template_content').prop('disabled', false);

                        var icon = jQuery('.wpc_shortcode_template[data-name="' + name + '"]').parents('.column-template').find('.template_icon');
                        icon.removeClass('dashicons-media-default grey').addClass('dashicons-welcome-write-blog orange').attr('title',icon.data('unlock_title'));

                        lock_unlock[name] = 'unlock';
                    } else {
                        alert( data.data[0].message );
                    }
                }
            });
        });

        $('body').on('click', '.sb_lightbox_content_body .remove_from_theme', function() {
            var name = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('name'),
                path = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('path'),
                nonce = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('nonce'),
                $obj = $(this);
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: {
                    action : 'wpc_shortcode_templates',
                    operation : 'delete',
                    filename : name,
                    path : path,
                    nonce : nonce
                },
                success: function( data ) {
                    if( data.success ) {
                        $obj.hide();
                        $obj.closest('.sb_lightbox_content_body').find('.wpc_save_template').hide();
                        $obj.closest('.sb_lightbox_content_body').find('.copy_to_theme').show();
                        $obj.closest('.sb_lightbox_content_body').find('.wpc_template_content').prop('disabled', true);
                        $obj.closest('.sb_lightbox_content_body').find('.wpc_template_content').val( data.data );

                        var icon = jQuery('.wpc_shortcode_template[data-name="' + name + '"]').parents('.column-template').find('.template_icon');
                        icon.addClass('dashicons-media-default grey').removeClass('dashicons-welcome-write-blog orange').attr('title',icon.data('lock_title'));

                        lock_unlock[name] = 'lock';
                    } else {
                        alert( data.data[0].message );
                    }
                }
            });
        });

        $('body').on('click', '.sb_lightbox_content_body .wpc_save_template', function() {
            var name = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('name'),
                path = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('path'),
                nonce = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').data('nonce'),
                content = $(this).closest('.sb_lightbox_content_body').find('.wpc_template_content').val(),
                $obj = $(this);
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: {
                    action : 'wpc_shortcode_templates',
                    operation : 'save_template',
                    filename : name,
                    path : path,
                    nonce : nonce,
                    content : jQuery.base64Encode( content )
                },
                success: function( data ) {
                    if( data.success ) {
                        $obj.parent().append('<span class="wpc_success_message" style="color: green;">Saved</span>');
                        setTimeout(function() {
                            $('.wpc_success_message').remove();
                        }, 2000 );
                    } else {
                        alert( data.data[0].message );
                    }
                }
            });
        });

        $('body').on('click', '.wp-list-table .delete a', function() {
            var name = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('name'),
                path = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('path'),
                nonce = $(this).closest('.row-actions').siblings('.wpc_shortcode_template').data('nonce'),
                $obj = $(this);
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_admin_url() ?>admin-ajax.php',
                data: {
                    action : 'wpc_shortcode_templates',
                    operation : 'delete',
                    filename : name,
                    path : path,
                    nonce : nonce
                },
                success: function( data ) {
                    if( data.success ) {
                        window.location.href = '<?php echo WPC()->get_current_url() ?>' + window.location.hash;
                        location.reload();
                    } else {
                        alert( data.data[0].message );
                    }
                }
            });
        });

        //init shutter boxes at first page load for "Edit Template" and "View Template" buttons
        init_view_template();
        init_edit_template();

        /**
         * history events when back/forward and change window.location.hash handler
         */
        window.addEventListener("popstate", function(e) {
            hash_data = parse_hash();

            jQuery(".template_tag").removeClass('active');
            //jQuery( this ).toggleClass('active');
            jQuery( 'table.templates tbody tr' ).hide();

            var disp_arr;
            jQuery.each( hash_data, function( e ) {
                jQuery('.template_tag[data-tag="' + e + '"]').toggleClass('active');
                var tag_rows;

                tag_rows = jQuery( 'table.templates tbody tr.' + e + '_tag' );
                tag_rows.show();
            });

            disp_arr = jQuery( '.displaying-num' ).html().split(' ');
            disp_arr[0] = jQuery('table.templates tbody tr:visible').length;
            jQuery( '.displaying-num' ).html( disp_arr.join(' ') );
        });


        //at first page load set tags from hash
        hash_data = parse_hash();
        jQuery.each( hash_data, function( e ) {
            jQuery('.template_tag[data-tag="' + e + '"]').trigger('click');
        });


        /**
         * Build hash string, using global variable "hash_data"
         */
        function get_hash_string() {
            var hash_array = [];
            for( var index in hash_data ) {
                hash_array.push( index + '=' + hash_data[index] );
            }
            hash_string = hash_array.join('&');

            if ( hash_string == '' )
                return '';

            return '#' + hash_string;
        }


        /**
         * Parse URLs hash
         */
        function parse_hash() {
            var hash_obj = {};
            var hash = window.location.hash.substring( 1, window.location.hash.length );

            if ( hash == '' ) {
                return hash_obj;
            }

            var hash_array = hash.split('&');

            for ( var index in hash_array ) {
                var temp = hash_array[index].split('=');
                hash_obj[temp[0]] = temp[1];
            }

            return hash_obj;
        }


        /**
         * Clear hash for remove tags
         */
        function clear_hash() {
            hash_data = {};
            window.location.hash = get_hash_string();
        }
    });
</script>

<div class="icon32" id="icon-link-manager"></div>
<p><?php _e( 'To customize any shortcode templates, you will first want to copy the desired template to your theme directory (click on "Copy to Theme Directory"). You will then be able to edit the corresponding template.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>

<form action="" method="get" id="other_tab_form" style="width: 100%;">
    <input type="hidden" name="page" value="wpclients_templates" />
    <input type="hidden" name="tab" value="php_templates" />
    <?php $ListTable->search_box( __( 'Search Templates', WPC_CLIENT_TEXT_DOMAIN ), 'search-submit' ); ?>
    <?php $ListTable->display(); ?>
</form>
<div class="wpc_template_form" style="display: none;">
    <textarea name="wpc_template_content" class="wpc_template_content"></textarea>
    <div class="wpc_shortcode_templates_actions_btn">
        <input type="button" class="button-primary wpc_save_template" value="<?php _e( 'Save Template', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
        <input type="button" class="button remove_from_theme" value="<?php _e( 'Delete Template From Theme', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
        <input type="button" class="button copy_to_theme" value="<?php _e( 'Copy Template To Theme', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
    </div>
</div>