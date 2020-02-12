<?php

if ( !class_exists( 'WPC_TLC_Admin' ) ) {

    class WPC_TLC_Admin extends WPC_TLC_Common {

        var $extension_dir;
        var $extension_url;

        /**
        * PHP 5 constructor
        **/
        function __construct() {
            $this->common_construct();

            add_action( 'admin_enqueue_scripts', array( &$this, 'load_css_js' ), 100 );

            add_filter( 'wpc_client_settings_tabs', array( &$this, 'add_settings_tab' ) );
            add_action( 'wpc_client_settings_tab_time_limited_clients', array( &$this, 'show_settings_page' ) );

            add_action( 'wpc_client_view_client_after_custom_fields', array( &$this, 'show_view_field' ) );
            add_action( 'wpc_client_add_client_after_username', array( &$this, 'show_add_field' ) );
            add_action( 'wpc_client_edit_client_after_username', array( &$this, 'show_edit_field' ) );


            add_action( 'wpc_client_new_client_added', array( &$this, 'save_user_meta' ), 10, 2 );
            add_action( 'wpc_client_client_updated', array( &$this, 'save_user_meta' ), 10, 2 );

            //uninstall
            add_action( 'wp_client_uninstall', array( &$this, 'uninstall_extension' ) );

            //add array help
            add_filter( 'wpc_set_array_help', array( &$this, 'wpc_set_array_help' ), 10, 2 );

            //add column on Clients page
            add_filter( 'wpc_client_columns_of_clients', array( $this, 'add_column_for_clients' ) );
            add_filter( 'wpc_client_sortable_columns_of_clients', array( $this, 'add_sortable_column_for_clients' ) );
            add_filter( 'wpc_client_clients_table_query_args', array( $this, 'change_query_args_for_clients' ) );
            add_filter( 'wpc_client_expiration_date_custom_column_of_clients', array( $this, 'change_value_of_expiration_date' ) );
            add_filter( 'wpc_client_order_by_of_clients', array( $this, 'change_column_order_by' ), 10, 2 );
        }

        
        function change_query_args_for_clients( $vars ) {
            global $wpdb;

            $vars['select'] .= ', tlc_um.meta_value as expiration_date';
            $vars['left_joins'] .= " LEFT JOIN {$wpdb->usermeta} tlc_um ON ( u.ID = tlc_um.user_id AND tlc_um.meta_key = 'wpc_expiration_date' )";

            return $vars;
         }


        function change_value_of_expiration_date( $value ) {
            $value = !empty( $value ) ? WPC()->date_format( $value ) : $value ;
            return $value;
        }


        function change_column_order_by( $order_by, $get_orderby ) {
            if ( 'expiration_date' === $get_orderby ) {
                $order_by = 'tlc_um.meta_value';
            }

            return $order_by;
        }


        function add_column_for_clients( $columns ) {
            $columns['expiration_date'] = __( 'Expiration Date', WPC_CLIENT_TEXT_DOMAIN );
            return $columns;
        }


        function add_sortable_column_for_clients( $columns ) {
            $columns['expiration_date'] = 'expiration_date';
            return $columns;
        }


        function wpc_set_array_help( $array_help, $method ) {
            switch( $method ) {
                case '_add_wpclients_settingstime_limited_clients_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this page you can adjust text that a user receives after login when their accounts have expired. By default the text reads "Sorry, your access permission has expired". This is the text that an expired %s will see when they attempt to login to view their HUB Page.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;
            }
            return $array_help;
        }


        /*
        * Function unisntall
        */
        function uninstall_extension() {
            WPC()->delete_settings( 'time_limited_clients' );

            //deactivate the extension
            $plugins = get_option( 'active_plugins' );
            if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                $new_plugins = array();
                foreach( $plugins as $plugin )
                    if ( 'wp-client-time-limited-clients/wp-client-time-limited-clients.php' != $plugin )
                        $new_plugins[] = $plugin;
            }
            update_option( 'active_plugins', $new_plugins );

        }


        /*
        * Save User Meta
        */
        function save_user_meta( $client_id, $userdata ) {

            if ( isset( $_REQUEST['expiration_date'] ) ) {
                update_user_meta( $client_id, 'wpc_expiration_date', $_REQUEST['expiration_date'] );
            } elseif( isset( $userdata['expiration_date'] ) && !empty( $userdata['expiration_date'] ) ) {
                update_user_meta( $client_id, 'wpc_expiration_date', $userdata['expiration_date'] );
            }
        }


        /*
        * Add settings tab
        */
        function add_settings_tab( $tabs ) {
            $tabs['time_limited_clients'] = array(
                'title'     => sprintf( __( 'Time Limited %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
            );

            return $tabs;
        }


        /*
        * Show settings page
        */
        function show_settings_page() {
            include_once( $this->extension_dir . 'includes/admin/settings_time_limited_clients.php' );
        }


        /*
        * Show view field
        */
        function show_view_field( $client_id ) {

            $wpc_expiration_date = get_user_meta( $client_id, 'wpc_expiration_date', true );
            if ( false == $wpc_expiration_date ) {
                $wpc_expiration_date = '';
            } else {
                $wpc_expiration_date = date( 'm/d/Y', $wpc_expiration_date );
            }
            ?>
            <tr>
                <td>
                    <label>
                        <?php _e( 'Expiration Date:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                    <br />
                    <input type="text" style="width: 90px" readonly="readonly" value="<?php echo $wpc_expiration_date ?>"/>
               </td>
            </tr>
            <?php
        }


        /*
        * Show edit field
        */
        function show_add_field() {
            ?>
            <tr>
                <th>
                    <label>
                        <?php _e( 'Expiration Date:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                </th>
                <td>
                    <?php
                        if ( isset( $_REQUEST['expiration_date'] ) ) {
                            $expiration_date = $_REQUEST['expiration_date'];
                        } else {
                            $wpc_tlc = WPC()->get_settings( 'time_limited_clients' );
                            $array_periods = $this->get_array_periods();
                            if ( !empty( $wpc_tlc['def_number'] ) && ( $number = $wpc_tlc['def_number'] )
                                && !empty( $wpc_tlc['def_period'] ) && array_key_exists( $wpc_tlc['def_period'], $array_periods ) ) {

                                switch( $wpc_tlc['def_period'] ) {
                                    case 'week':
                                        $number *= 7;
                                        $expiration_date = strtotime( "+$number day" );
                                        break;

                                    case 'month':
                                        $expiration_date = strtotime( "+$number month" );
                                        break;

                                    case 'year':
                                        $expiration_date = strtotime( "+$number year" );
                                        break;

                                    case 'day':
                                    default:
                                        $expiration_date = strtotime( "+$number day" );
                                        break;
                                }
                            }
                        }
                    ?>
                    <input type="text" style="width: 50%" id="fake_expiration_date" class="custom_datepicker_field" name="fake_expiration_date" value="" />
                    <input type="hidden" name="expiration_date" id="expiration_date" value="<?php echo ( isset( $expiration_date ) ) ? $expiration_date : '' ?>" />
                    <br />
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+15 days');  ?>">15 <?php _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+30 days') ?>">30 <?php _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+90 days') ?>">90 <?php _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+6 months') ?>">6 <?php _e( 'Months', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+1 year') ?>">1 <?php _e( 'Year', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </td>
            </tr>
            <script type="text/javascript">
                jQuery(document).ready( function() {
                    custom_datepicker_init();

                    //Set pre-set expiration date
                    jQuery( '.wpc_set_expiration_date' ).click( function() {
                        jQuery( '#expiration_date' ).val( jQuery( this ).attr( 'rel' ) );
                        jQuery( '#expiration_date' ).trigger('change');
                    });
                });
            </script>
            <?php
        }


        /*
        * Show edit field
        */
        function show_edit_field( $client_id ) {
            $wpc_expiration_date = get_user_meta( $client_id, 'wpc_expiration_date', true );
            if ( false == $wpc_expiration_date ) {
                $wpc_expiration_date = '';
            }
            ?>
            <tr>
                <th>
                    <label>
                        <?php _e( 'Expiration Date:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>
                </th>
                <td>
                    <input type="text" style="width: 50%" id="fake_expiration_date" class="custom_datepicker_field" name="fake_expiration_date" value="" />
                    <input type="hidden" name="expiration_date" id="expiration_date" value="<?php echo ( isset( $_REQUEST['expiration_date'] ) ) ? $_REQUEST['expiration_date'] : $wpc_expiration_date ?>" />
                    <br />
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+15 days');  ?>">15 <?php _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+30 days') ?>">30 <?php _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+90 days') ?>">90 <?php _e( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+6 months') ?>">6 <?php _e( 'Months', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    |
                    <a href="javascript:void(0);" class="wpc_set_expiration_date" rel="<?php echo strtotime('+1 year') ?>">1 <?php _e( 'Year', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </td>
            </tr>

            <script type="text/javascript">
                jQuery(document).ready( function() {
                    custom_datepicker_init();

                    //Set pre-set expiration date
                    jQuery( '.wpc_set_expiration_date' ).click( function() {
                        jQuery( '#expiration_date' ).val( jQuery( this ).attr( 'rel' ) );
                        jQuery( '#expiration_date' ).trigger('change');
                    });
                });
            </script>

            <?php
        }


        /**
         * Load css and js
         */
        function load_css_js() {
            if ( isset( $_GET['page'] ) && 'wpclient_clients' == $_GET['page'] && isset( $_GET['tab'] ) && ( 'add_client' == $_GET['tab'] || 'edit_client' == $_GET['tab'] ) ) {
                /*wp_enqueue_script( 'jquery-ui-datepicker' );

                wp_register_style( 'wpc-ui-datepicker', WPC()->plugin_url . 'css/datapiker/ui_datapiker.min.css' );
                wp_enqueue_style( 'wpc-ui-datepicker' );*/

                WPC()->custom_fields()->add_custom_datepicker_scripts();
            }
        }

    //end class
    }
}
