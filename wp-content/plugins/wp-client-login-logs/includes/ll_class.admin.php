<?php
if ( ! class_exists( 'WPC_LL_Admin' ) ) {

    class WPC_LL_Admin extends WPC_LL_Common {

        var $extension_dir;
        var $extension_url;

        /**
        * PHP 5 constructor
        **/
        function __construct() {
            $this->common_construct();

            //check on SSL
            if ( function_exists( 'set_url_scheme' ) ) {
                $this->extension_url = set_url_scheme( $this->extension_url );
            }

            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'add_admin_submenu' ) );

            //uninstall
            add_action( 'wp_client_uninstall', array( &$this, 'uninstall_extension' ) );

            add_action( 'wpc_client_delete_client', array( &$this, 'delete_client' ) );

            //add array help
            add_filter( 'wpc_set_array_help', array( &$this, 'wpc_set_array_help' ), 10, 2 );

            add_filter( 'wpc_screen_options_pagination', array( &$this, 'screen_options_pagination' ), 10 );

            //add screen options for client Page
            add_action( 'admin_head', array( &$this, 'add_screen_options' ), 5 );
        }


        function add_screen_options() {

            if ( isset( $_GET['page'] ) && 'wpclients_login_logs' == $_GET['page'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'User log ins', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_ll_login_logs_per_page'
                    )
                );
            }
        }


        function screen_options_pagination( $wpc_screen_options ) {

            $wpc_screen_options = array_merge( $wpc_screen_options, array(
                'wpc_ll_login_logs_per_page',
            ) );

            return $wpc_screen_options;
        }


        function delete_client( $user_id ) {
            global $wpdb;
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_login_logs WHERE user_id=%d ", $user_id ) );
        }


        function wpc_set_array_help( $array_help, $method ) {
            switch( $method ) {
                case '_add_wpclients_login_logs_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'The Login Logs extension allows you to monitor failed and successful login attempts in your installation. From this page you can view the entire log of login attempts, including associated usernames, timestamps, and reasons for failed login such as "incorrect password".', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;
            }
            return $array_help ;
        }

        /*
        * Function unisntall
        */
        function uninstall_extension() {
            global $wpdb;

            /*
            * Delete all tables
            */
            //tables name
            $tables = array(
                'wpc_client_login_logs',
            );

            //remove all tables
            foreach( $tables as $key ) {
                if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}" ) {
                    $wpdb->query( "DROP TABLE {$wpdb->prefix}{$key}" );
                }
            }


            //deactivate the extension
            $plugins = get_option( 'active_plugins' );
            if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                $new_plugins = array();
                foreach( $plugins as $plugin )
                    if ( 'wp-client-login-logs/wp-client-login-logs.php' != $plugin )
                        $new_plugins[] = $plugin;
            }
            update_option( 'active_plugins', $new_plugins );

        }


        /*
        * Function for adding admin sub
        */
        function add_admin_submenu( $plugin_submenus ) {
            global $current_user;
            if ( current_user_can( 'wpc_manager' ) && current_user_can( 'wpc_show_login_logs' ) && !current_user_can( 'administrator' ) ) {
                $cap = "wpc_manager";
            } else if ( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) )
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

            //add addon submenu
            $plugin_submenus['wpclients_login_logs'] = array(
                'page_title'        => __( 'Login Logs', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Login Logs', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_login_logs',
                'capability'        => $cap,
                'function'          => array( &$this, 'login_logs_table' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 150,
            );

            return $plugin_submenus;
        }


        function login_logs_table() {
            include $this->extension_dir . 'includes/admin/ll_table.php';
        }


    //end class
    }
}