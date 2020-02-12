<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb; ?>

<div class="wpc_dashboard">
<?php
/*our_hook_
    hook_name: wpc_client_dashboard_before_widgets
    hook_title: HTML before dashboard widgets
    hook_description: Hook runs when Dashboard loaded.
    hook_type: filter
    hook_in: wp-client
    hook_location dashboard_dashboard.php
    hook_param: string $html
    hook_since: 3.8.8
*/
echo apply_filters( 'wpc_client_dashboard_before_widgets', '' );

if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) && current_user_can( 'wpc_show_dashboard' ) ) ) {
    $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
    $wpc_general = WPC()->get_settings( 'general' );
    $wpc_custom_login = WPC()->get_settings( 'custom_login' );

    wp_enqueue_style( 'wp-client-jqplot-style' );

    wp_enqueue_script( 'wpc-jqplot', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-logAxisRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-canvasTextRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-canvasAxisLabelRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-canvasAxisTickRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-dateAxisRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-categoryAxisRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-barRenderer', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-highlighter', false, array('jquery'), WPC_CLIENT_VER, true );
    wp_enqueue_script( 'wpc-cursor', false, array('jquery'), WPC_CLIENT_VER, true );

    $default_widgets_array = array(
        'wpc_clients_dashboard_widget'          => array(
            'collapsed'     => false,
            'color'         => 'blue',
        ),
        'wpc_client_staff_dashboard_widget'     => array(
            'collapsed'     => false,
            'color'         => 'green',
        ),
        'wpc_files_dashboard_widget'            => array(
            'collapsed'     => false,
            'color'         => 'red',
        ),
        'wpc_portal_pages_dashboard_widget'     => array(
            'collapsed'     => false,
            'color'         => 'white',
        ),
        'wpc_client_circles_dashboard_widget'   => array(
            'collapsed'     => false,
            'color'         => 'white',
        ),
        'wpc_managers_dashboard_widget'         => array(
            'collapsed'     => false,
            'color'         => 'white',
        ),
        'wpc_top_files_dashboard_widget'        => array(
            'collapsed'     => false,
            'color'         => 'white',
        ),
        'wpc_settings_info_dashboard_widget'    => array(
            'collapsed'     => false,
            'color'         => 'white',
        ),
    );

    if ( ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_view_private_messages' ) ) || current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) ) {
        $default_widgets_array['wpc_private_messages_dashboard_widget'] = array(
            'collapsed'     => false,
            'color'         => 'white',
        );
    }

    $default_widgets_array = apply_filters( 'wpc_client_dashboard_widgets_list', $default_widgets_array );
    $widgets_list = $def_widgets_list = array_keys( $default_widgets_array );

    $user_widgets = get_user_meta( get_current_user_id(), 'wpc_client_widgets', true );
    if( isset( $user_widgets ) && !empty( $user_widgets ) ) {
        $widgets_list = array_merge( $user_widgets, array_diff( $widgets_list, $user_widgets ) );
        $widgets_list = array_intersect( $widgets_list, $def_widgets_list );
    }


    if( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
        $widgets_list = array_flip( $widgets_list );
        unset( $widgets_list['wpc_settings_info_dashboard_widget'] );
        unset( $widgets_list['wpc_client_circles_dashboard_widget'] );
        unset( $widgets_list['wpc_managers_dashboard_widget'] );
        unset( $widgets_list['wpc_portal_pages_dashboard_widget'] );
        unset( $widgets_list['wpc_top_files_dashboard_widget'] );
        $widgets_list = array_flip( $widgets_list );
    } ?>

    <script type="text/javascript">
        jQuery( document ).ready( function() {
            var is_loaded = 0;
            var count_widgets = <?php echo count( $widgets_list ) ?>;

            jQuery( 'body' ).on( 'click', '.widget_reload', function() {
                var obj = jQuery(this);
                var widget = obj.parents( '.halfwidth_place' ).data( 'widget' );

                obj.parents( '.halfwidth_place' ).css({
                    'background':'#f1f1f1',
                    'height': ( obj.parents( '.halfwidth_place' ).height() - 10 ) + 'px',
                    'box-sizing':'border-box',
                    'border':'1px dashed #cccccc',
                    'padding':'0',
                    'margin-left':'5px',
                    'margin-right':'5px',
                    'margin-bottom':'10px',
                    'width':'calc(50% - 10px)'
                }).html( '<div class="ajax_widget_loading"></div>' );

                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=' + widget,
                    dataType: 'html',
                    success: function( data ) {
                        if( data != '0' ) {
                            jQuery( '.' + widget ).css({
                                'background':'transparent',
                                'height':'auto',
                                'border':'none',
                                'width':'50%',
                                'margin':'0',
                                'padding-right':'5px',
                                'padding-left':'5px'
                            }).html( data );
                        }
                    }
                });
            });

            <?php if( is_array( $widgets_list ) && count( $widgets_list ) > 0 ) {
                foreach( $widgets_list as $widget ) {

                    $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_' . $widget, true );

                    if( empty( $widget_options ) ) {
                        $widget_options = $default_widgets_array[$widget];
                        update_user_meta( get_current_user_id(), 'wpc_client_widget_' . $widget, $widget_options );
                    }

                    $widget_options['collapsed'] = ( isset( $widget_options['collapsed'] ) && true == $widget_options['collapsed'] ) ? true : false;

                    if( $widget_options['collapsed'] ) { ?>
                        jQuery('.wpc_dashboard .collapsed_widgets').show().append( '<div class="<?php echo $widget ?> halfwidth_place <?php echo ( $widget_options['collapsed'] ) ? 'collapsed' : '' ?>" data-widget="<?php echo $widget ?>"><div class="ajax_widget_loading"></div></div>' );
                    <?php } else { ?>
                        jQuery('.wpc_dashboard .active_widgets').append( '<div class="<?php echo $widget ?> halfwidth_place" data-widget="<?php echo $widget ?>"><div class="ajax_widget_loading"></div></div>' );
                    <?php } ?>


                    jQuery( '.halfwidth_place' ).css({
                        'background':'#f1f1f1',
                        'height':'200px',
                        'box-sizing':'border-box',
                        'border':'1px dashed #cccccc',
                        'width':'48%',
                        'margin':'5px'
                    });

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=<?php echo $widget ?>',
                        dataType: 'html',
                        success: function( data ) {
                            if( data != '0' ) {
                                jQuery( '.<?php echo $widget ?>' ).css({
                                    'background':'transparent',
                                    'height':'auto',
                                    'border':'none',
                                    'width':'50%',
                                    'margin':'0'
                                }).html( data );

                                is_loaded ++;

                                if( is_loaded == count_widgets ) {
                                    jQuery('.wpc_dashboard .active_widgets').masonry({
                                        // options
                                        itemSelector: '.halfwidth_place',
                                        // use element for option
                                        columnWidth: '.grid-sizer',
                                        percentPosition: true
                                    });
                                }
                            }
                        }
                    });
                <?php }
            } ?>

            jQuery('.wpc_dashboard .active_widgets').sortable({
                handle: ".tile_title",
                //placeholder: "widget_placeholder ui-corner-all",
                update: function( event, ui ) {
                    var sort_array = [];
                    jQuery( '.halfwidth_place' ).each( function() {
                        sort_array.push( jQuery(this).data('widget') );
                    });

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_update_widgets_order&order=' + sort_array,
                        dataType: 'html',
                        success: function( data ) {}
                    });
                },
                start: function (e, ui) {
                    ui.item.removeClass( 'masonry-item' );
                    jQuery('.wpc_dashboard .active_widgets').masonry( 'reloadItems' ).masonry( 'layout' );
                },
                change: function (e, ui) {
                    jQuery( '.wpc_dashboard .active_widgets' ).masonry('reloadItems');
                },
                stop: function (e, ui) {
                    ui.item.addClass( 'masonry-item' );
                    jQuery( '.wpc_dashboard .active_widgets' ).masonry('reloadItems').masonry( 'layout' );
                }
            });

            //custom selectbox handlers
            jQuery( 'body' ).on( 'click', '.widget_custom_palette', function(e) {
                jQuery( '.widget_custom_palette' ).not( this ).removeClass( 'is_focus' );
                jQuery( '.widget_custom_selectbox' ).removeClass( 'is_focus' );
                jQuery(this).toggleClass( 'is_focus' );

                e.stopPropagation();

                jQuery( 'body' ).bind( 'click', function( event ) {
                    jQuery( '.widget_custom_palette' ).removeClass( 'is_focus' );
                    jQuery( 'body' ).unbind( event );
                });
            });


            jQuery( 'body' ).on( 'change', '.widget_custom_palette', function(e) {
                var color = jQuery( this ).find( '.widget_colorize' ).data( 'value' );
                var widget_id = jQuery(this).parents( '.halfwidth_place' ).data( 'widget' );
                var obj = jQuery(this);

                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_update_widgets_color&color=' + color + '&widget_id=' + widget_id,
                    dataType: 'html',
                    success: function( data ) {
                        obj.parents( '.tiles' ).removeClass().addClass( 'tiles' ).addClass( color );
                    }
                });
            });


            jQuery( 'body' ).on( 'click', '.widget_custom_palette .widget_color:not(.selected)', function(e) {
                jQuery(this).parents( '.widget_custom_palette' ).find( '.widget_colorize' ).data( 'value', jQuery(this).data('value') );
                jQuery(this).parents( '.widget_custom_palette' ).find( '.widget_color' ).removeClass( 'selected' );
                jQuery(this).addClass( 'selected' );
                jQuery( this ).parents( '.widget_custom_palette' ).trigger( 'change' );
            });


            jQuery( 'body' ).on( 'click', '.widget_toggle', function(e) {

                jQuery(this).parents( '.halfwidth_place' ).toggleClass( 'collapsed' );
                var collapsed = jQuery(this).parents( '.halfwidth_place' ).hasClass( 'collapsed' );
                var widget_id = jQuery(this).parents( '.halfwidth_place' ).data( 'widget' );

                if( collapsed ) {
                    $outerhtml = jQuery( '<div>' ).append( jQuery(this).parents( '.halfwidth_place' ).css({
                        'height': 'auto',
                        'box-sizing': 'border-box',
                        'border': 'none',
                        'width': '50%',
                        'margin': '0px',
                        'background': 'transparent',
                        'top': '0px',
                        'left': '0px',
                        'position': 'static'
                    }).clone() ).html();

                    jQuery(this).parents( '.halfwidth_place' ).remove();
                    jQuery('.wpc_dashboard .collapsed_widgets').append( $outerhtml );
                } else {
                    $outerhtml = jQuery( '<div>' ).append( jQuery(this).parents( '.halfwidth_place' ).clone() ).html();
                    jQuery(this).parents( '.halfwidth_place' ).remove();
                    jQuery('.wpc_dashboard .active_widgets').append( $outerhtml );
                }

                if( jQuery('.wpc_dashboard .collapsed_widgets .halfwidth_place').length > 0 ) {
                    jQuery('.wpc_dashboard .collapsed_widgets').show();
                } else {
                    jQuery('.wpc_dashboard .collapsed_widgets').hide();
                }

                jQuery('.wpc_dashboard .active_widgets').masonry( 'reloadItems' ).masonry( 'layout' );

                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_collapse_widget&collapse=' + collapsed + '&widget_id=' + widget_id,
                    dataType: 'html',
                    success: function( data ) {}
                });

                var sort_array = [];
                jQuery( '.halfwidth_place' ).each( function() {
                    sort_array.push( jQuery(this).data('widget') );
                });

                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo get_admin_url() ?>admin-ajax.php',
                    data: 'action=wpc_update_widgets_order&order=' + sort_array,
                    dataType: 'html',
                    success: function( data ) {}
                });
            });
        });
    </script>

    <div class="active_widgets"><div class="grid-sizer"></div></div>
    <div class="collapsed_widgets" style="display: none;"><hr /></div>

    <?php do_action( 'wpc_client_dashboard_widgets_footer'  );
}

/*our_hook_
    hook_name: wpc_client_dashboard_after_widgets
    hook_title: HTML after dashboard widgets
    hook_description: Hook runs when Dashboard loaded.
    hook_type: filter
    hook_in: wp-client
    hook_location dashboard_dashboard.php
    hook_param: string $html
    hook_since: 3.8.8
*/
echo apply_filters( 'wpc_client_dashboard_after_widgets', '' ); ?>
</div>