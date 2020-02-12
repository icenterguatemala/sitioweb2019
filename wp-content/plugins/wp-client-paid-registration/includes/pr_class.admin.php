<?php

if ( !class_exists( 'WPC_PR_Admin' ) ) {

    class WPC_PR_Admin extends WPC_PR_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->pr_common_construct();

            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'add_admin_submenu' ) );

            add_filter( 'wpc_client_settings_tabs', array( &$this, 'add_settings_tab' ) );
            add_action( 'wpc_client_settings_tab_paid_registration', array( &$this, 'show_settings_page' ) );

            //uninstall
            add_action( 'wp_client_uninstall', array( &$this, 'uninstall_extension' ) );

            //add array help
            add_filter( 'wpc_set_array_help', array( &$this, 'wpc_set_array_help' ), 10, 2 );

            add_filter( 'wpc_screen_options_pagination', array( &$this, 'screen_options_pagination' ), 10 );

            //add screen options for client Page
            add_action( 'admin_head', array( &$this, 'add_screen_options' ), 5 );

            // Add Settings link when activate plugin
            add_filter( 'plugin_action_links_wp-client-paid-registration/wp-client-paid-registration.php', array( &$this, 'filter_action_links' ), 99 );

        }


        function add_screen_options() {
            if ( isset( $_GET['page'] ) && 'wpclients_paid_registration' == $_GET['page'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'Registrations', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_pr_registrations_per_page'
                    )
                );
            }
        }


        function screen_options_pagination( $wpc_screen_options ) {

            $wpc_screen_options = array_merge( $wpc_screen_options, array(
                'wpc_pr_registrations_per_page',
            ) );

            return $wpc_screen_options;
        }


        function wpc_set_array_help( $array_help, $method ) {
            switch( $method ) {
                case '_add_wpclients_settingspaid_registration_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'You can adjust your Paid Registration settings from this tab, including the cost of registration, what payment gateways are provided to your %s, and a custom redirect URL for after payment.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ) . '</p>' .
                                    '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    );
                break;
            }
            return $array_help ;
        }


        /*
        * Function unisntall
        */
        function uninstall_extension() {
            WPC()->delete_settings( 'paid_registration' );

            //deactivate the extension
            $plugins = get_option( 'active_plugins' );
            if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                $new_plugins = array();
                foreach( $plugins as $plugin )
                    if ( 'wp-client-paid-registration/wp-client-paid-registration.php' != $plugin )
                        $new_plugins[] = $plugin;
            }

            update_option( 'active_plugins', $new_plugins );
        }


        /*
        * Function for adding admin submenu
        */
        function add_admin_submenu( $plugin_submenus ) {

            if ( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) )
                $cap = "wpc_admin";
            else
                $cap = "manage_options";

            //add separater before addons submenu block
            $plugin_submenus['separator_2'] = array(
                'page_title'        => '',
                'menu_title'        => '- - - - - - - - - -',
                'slug'              => '#',
                'capability'        => $cap,
                'function'          => '',
                'hidden'            => false,
                'real'              => false,
                'order'             => 100,
            );

            $plugin_submenus['wpclients_paid_registration'] = array(
                'page_title'        => __( 'Paid Registration', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Paid Registration', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_paid_registration',
                'capability'        => $cap,
                'function'          => array( &$this, 'paid_registration_pages' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 115,
            );

            return $plugin_submenus;
        }


        /*
        * display page
        */
        function paid_registration_pages() {
            include_once $this->extension_dir . 'includes/admin/registrations.php';
        }


        /*
        * Add settings tab
        */
        function add_settings_tab( $tabs ) {
            $tabs['paid_registration'] = array(
                'title'     => __( 'Paid Registration', WPC_CLIENT_TEXT_DOMAIN ),
            );

            return $tabs;
        }


        /*
        * Show settings page
        */
        function show_settings_page() {
            include_once( $this->extension_dir . 'includes/admin/settings_paid_registration.php' );
        }


        /**
         * Add Setting link at plugin page
         * @param $links
         * @return mixed
         */
        public function filter_action_links( $links ) {

            if ( WPC()->is_licensed( 'WP-Client' ) ) {

                $links['settings'] = sprintf( '<a href="admin.php?page=wpclients_settings&tab=paid_registration">%s</a>', __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ) );

            }

            return $links;

        }

        //end class
    }

}
