<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'WPC_Widgets' ) ) :

class WPC_Widgets {

    /**
     * The single instance of the class.
     *
     * @var WPC_Widgets
     * @since 4.5
     */
    protected static $_instance = null;

    var $notices = array();

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Widgets is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Widgets - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {


    }


    // CALLED VIA 'sidebar_admin_setup' ACTION
    // adds in the admin control per widget
    function widget_expand_control() {
        global $wp_registered_widgets, $wp_registered_widget_controls;
        foreach ( $wp_registered_widgets as $id => $widget ) {
            if ( !isset( $wp_registered_widget_controls[ $id ] ) ) {
                wp_register_widget_control( $id, $widget['name'], array( &$this, 'widget_empty_control' ) );
            }
            $wp_registered_widget_controls[ $id ]['callback_wpc_redirect'] = $wp_registered_widget_controls[ $id ]['callback'];
            $wp_registered_widget_controls[ $id ]['callback'] = array( &$this, 'widget_extra_control' );
            array_push( $wp_registered_widget_controls[ $id ]['params'], $id );
        }

    }


    function in_widget_form( $widget_instance ) {
        $settings = $widget_instance->get_settings();
        $num = isset( $widget_instance->number ) ? $widget_instance->number : 0;
        $value = !empty( $settings[ $num ]['wpc_show_widget'] ) ? $settings[ $num ]['wpc_show_widget'] : 'default';
        ?>
        <p>
            <label>
                <b><?php printf( __( '%s Display Options', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] ); ?></b>:<br />
                <select name="wpc_show_page" style="width: 100%;">
                    <option value="default" <?php selected( $value, 'default' ); ?>><?php _e( 'Default', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <option value="hub" <?php selected( $value, 'hub' ); ?>><?php _e( 'Show on HUB Page', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <option value="portal" <?php selected( $value, 'portal' ); ?>><?php printf( __( 'Show on %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ); ?></option>
                    <option value="hub_portal" <?php selected( $value, 'hub_portal' ); ?>><?php printf( __( 'Show on both HUB and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ); ?></option>
                    <option value="not_hub" <?php selected( $value, 'not_hub' ); ?>><?php _e( "Don't show on HUB Page", WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <option value="not_portal" <?php selected( $value, 'not_portal' ); ?>><?php printf( __( "Don't show on %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ); ?></option>
                    <option value="not_hub_portal" <?php selected( $value, 'not_hub_portal' ); ?>><?php printf( __( "Don't show on either HUB or %s", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] ); ?></option>
                </select>
            </label>
        </p>
        <?php
    }


    function widget_extra_control() {
        $params = func_get_args();
        $this->widget_show_setting( $params );
    }


    function widget_show_setting( $params ) {
        global $wp_registered_widget_controls;
        if( !is_array( $params ) ) {
            return;
        }

        $id = array_pop( $params );
        if( !empty( $wp_registered_widget_controls[ $id ]['callback_wpc_redirect'] ) ) {
            $callback = $wp_registered_widget_controls[ $id ]['callback_wpc_redirect'];
            if ( is_callable( $callback ) )
                call_user_func_array( $callback, $params );
        }

    }


    // added to widget functionality in 'widget_logic_expand_control' (above)
    function widget_empty_control() {
        return;
    }



    // CALLED VIA 'widget_update_callback' FILTER (ajax update of a widget)
    function widget_ajax_update_callback( $instance, $new_instance, $this_widget, $obj ) {
        if ( isset( $_POST['wpc_show_page'] ) ) {
            $instance['wpc_show_widget'] = $_POST['wpc_show_page'];
            /*$options = get_option('wpc_widget_show_settings', array());
                $options = array_merge( $options, $_POST['wpc_show_page'] );
                update_option( 'wpc_widget_show_settings', $options );*/
        }
        return $instance;
    }


    // CALLED ON 'sidebars_widgets' FILTER
    function widget_filter_sidebars_widgets( $sidebars_widgets ) {
        global $post, $wp_widget_factory;

        if( empty( $post ) || !isset( $post->ID ) ) {
            return $sidebars_widgets;
        }

        if ( empty( $sidebars_widgets ) )
            return $sidebars_widgets;

        foreach( $sidebars_widgets as $widget_area => $widget_list ) {
            if( !is_array( $widget_list ) || !count( $widget_list ) ) continue;
            foreach($widget_list as $pos => $widget_id) {
                $widget_base = _get_widget_id_base( $widget_id );

                if ( $widget_id && preg_match("/^{$widget_base}\-(?P<id>\d+)$/", $widget_id, $matches) ) {
                    $id = $matches['id'];
                } else {
                    continue;
                }

                $options = get_option('widget_' . $widget_base);
                if ( !isset( $options[ $id ] ) ) continue;
                $settings = $options[$id];

                if( !isset( $settings['wpc_show_widget'] ) ) continue;

                switch( $settings['wpc_show_widget'] ) {
                    case 'hub':
                        if ( $post->post_type != 'portalhub' ) {
                            unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                        }
                        break;
                    case 'portal':
                        if ( $post->post_type != 'clientspage' ) {
                            unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                        }
                        break;
                    case 'hub_portal':
                        if ( ! ( $post->post_type == 'portalhub' || $post->post_type == 'clientspage' ) ) {
                            unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                        }
                        break;
                    case 'not_hub':
                        if ( $post->post_type == 'portalhub' ) {
                            unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                        }
                        break;
                    case 'not_portal':
                        if ( $post->post_type == 'clientspage' ) {
                            unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                        }
                        break;
                    case 'not_hub_portal':
                        if ( $post->post_type == 'portalhub' || $post->post_type == 'clientspage' ) {
                            unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                        }
                        break;
                }
            }
        }

        return $sidebars_widgets;
    }


    function widget_controls( $widget_id ) {

        $widget_options = get_user_meta( get_current_user_id(), 'wpc_client_widget_' . $widget_id, true );
        $color = ( isset( $widget_options['color'] ) && !empty( $widget_options['color'] ) ) ? $widget_options['color'] : 'white';
        //$view_all_link = ( isset( $widget_options['all_view_link'] ) && !empty( $widget_options['all_view_link'] ) ) ? $widget_options['all_view_link'] : '#';

        $view_all_link = '';
        switch( $widget_id ) {
            case 'wpc_clients_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclient_clients' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_client_staff_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclient_clients', 'tab' => 'staff' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_files_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_content', 'tab' =>'files' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_private_messages_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_content', 'tab' => 'private_messages' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_portal_pages_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_content' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_client_circles_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_content', 'tab' => 'circles' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_managers_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclient_clients', 'tab'=>'managers' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_top_files_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_content', 'tab'=>'files_downloads' ), get_admin_url() . 'admin.php' );
                break;
            case 'wpc_settings_info_dashboard_widget':
                $view_all_link = add_query_arg( array( 'page'=>'wpclients_settings' ), get_admin_url() . 'admin.php' );
                break;
            default:
                $view_all_link = apply_filters( 'wpc_client_dashboard_view_all_link', $view_all_link, $widget_id );
                break;
        }


        ob_start(); ?>

        <a href="<?php echo $view_all_link ?>" target="blank_" class="control_button widget_all" title="<?php _e( 'View All', WPC_CLIENT_TEXT_DOMAIN ) ?>"><?php _e( 'View All', WPC_CLIENT_TEXT_DOMAIN ) ?></a>

        <div class="widget_custom_palette">
            <a href="javascript:void(0);" class="control_button widget_colorize" data-value="blue" title="<?php _e( 'Select Widget\'s Color', WPC_CLIENT_TEXT_DOMAIN ) ?>"></a>
            <div class="colorize_palette">
                <table>
                    <tr>
                        <td><div style="background: #1dbcb1;" class="widget_color <?php echo( 'light-green' == $color ) ? 'selected' : '' ?>" data-value="light-green"></div></td>
                        <td><div style="background: #00aae3;" class="widget_color <?php echo( 'light-blue' == $color ) ? 'selected' : '' ?>" data-value="light-blue"></div></td>
                        <td><div style="background: #f67877;" class="widget_color <?php echo( 'light-red' == $color ) ? 'selected' : '' ?>" data-value="light-red"></div></td>
                        <td><div style="background: #bcaccd;" class="widget_color <?php echo( 'light-purple' == $color ) ? 'selected' : '' ?>" data-value="light-purple"></div></td>
                        <td><div style="background: #ffffff;" class="widget_color <?php echo( 'white' == $color ) ? 'selected' : '' ?>" data-value="white"></div></td>
                    </tr>
                    <tr>
                        <td><div style="background: #0aa699;" class="widget_color <?php echo( 'green' == $color ) ? 'selected' : '' ?>" data-value="green"></div></td>
                        <td><div style="background: #0090d9;" class="widget_color <?php echo( 'blue' == $color ) ? 'selected' : '' ?>" data-value="blue"></div></td>
                        <td><div style="background: #f35958;" class="widget_color <?php echo( 'red' == $color ) ? 'selected' : '' ?>" data-value="red"></div></td>
                        <td><div style="background: #a793bc;" class="widget_color <?php echo( 'purple' == $color ) ? 'selected' : '' ?>" data-value="purple"></div></td>
                        <td><div style="background: #808080;" class="widget_color <?php echo( 'grey' == $color ) ? 'selected' : '' ?>" data-value="grey"></div></td>
                    </tr>
                    <tr>
                        <td><div style="background: #029587;" class="widget_color <?php echo( 'dark-green' == $color ) ? 'selected' : '' ?>" data-value="dark-green"></div></td>
                        <td><div style="background: #007dd0;" class="widget_color <?php echo( 'dark-blue' == $color ) ? 'selected' : '' ?>" data-value="dark-blue"></div></td>
                        <td><div style="background: #f04443;" class="widget_color <?php echo( 'dark-red' == $color ) ? 'selected' : '' ?>" data-value="dark-red"></div></td>
                        <td><div style="background: #9680ae;" class="widget_color <?php echo( 'dark-purple' == $color ) ? 'selected' : '' ?>" data-value="dark-purple"></div></td>
                        <td><div style="background: #292929;" class="widget_color <?php echo( 'black' == $color ) ? 'selected' : '' ?>" data-value="black"></div></td>
                    </tr>
                </table>
            </div>
        </div>

        <a href="javascript:void(0);" class="control_button widget_toggle" title="<?php _e( 'Toogle Collapsing This Widget', WPC_CLIENT_TEXT_DOMAIN ) ?>"></a>
        <a href="javascript:void(0);" class="control_button widget_reload" title="<?php _e( 'Reload This Widget', WPC_CLIENT_TEXT_DOMAIN ) ?>"></a>
        <!--                        <a href="javascript:;" class="widget_close"></a>-->
        <?php $html = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }

        return $html;
    }





}

endif;