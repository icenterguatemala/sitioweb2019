<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( "WPC_Update" ) ):

class WPC_Update {

    /**
     * The single instance of the class.
     *
     * @var WPC_Update
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Update is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Update - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
    * PHP 5 constructor
    **/
    function __construct() {

    }

    function maybe_update_our_plugins() {

        $update_queue = $this->get_update_queue();

        if ( ! empty( $update_queue ) ) {
            $this->do_update();
        }
    }

    function maybe_crashed() {
        //check if we are crashed
        if ( $this->is_crashed() ) {
            $crash_updates =  $this->get_crash_updates();
            $crash_message = __( 'Unfortunately, in the process (%s) of updating the plugin "%s" to a newer version has errors. Please contact plugin support.', WPC_CLIENT_TEXT_DOMAIN );

            foreach( WPC()->extensions()->get_extensions() as $key => $data ) {
                if ( ! empty( $crash_updates[$key] ) ) {
                    $title = ! empty( $data['title'] ) ? $data['title'] : $key;
                    WPC()->notices()->add_all_pages_notice( sprintf( $crash_message, $crash_updates[$key], '<b>' . $title . '</b>' ), 'error' );
                }
            }
        }
    }


    function maybe_install() {

        if ( $this->is_update( 'core' ) && $this->can_install( 'core' ) ) {
            //run install and add updates in queue - once
            WPC()->install()->install();
        }

        foreach( WPC()->extensions()->get_extensions() as $key => $data ) {
            if ( $this->is_update( $key ) && $this->can_install( $key ) ) {

                /*our_hook_
                hook_name: wpc_client_extension_install
                hook_title: Run Extension Install
                hook_description:
                hook_type: action
                hook_in: wp-client
                hook_location class-hooks-common.php
                hook_param:
                hook_since: 4.5.0
                */
                do_action( 'wpc_client_extension_install_' . $key );
            }
        }
    }


    function do_update() {

        $update_queue = $this->get_update_queue();
        if ( ! empty( $update_queue ) ) {

            $ajax_url   = WPC()->get_ajax_url();
            $uid        = get_current_user_id();
            $token      = SECURE_AUTH_SALT;

            //add AJAX update if remote update will not work
            add_action( 'admin_print_scripts', array( &$this, 'send_ajax_update_script' ), 999 );

            if ( !ini_get( 'safe_mode' ) )
                @set_time_limit( 0 );

            foreach( $update_queue as $prefix => $version ) {

                $this->set_crash_updates( $prefix, $version );

                $postfields = array(
                    'action'    => 'wpc_updater',
                    'prefix'    => $prefix,
                    'version'   => $version,
                    '_nonce'     => md5( $uid . $token . $prefix . $version . 'remote_update' ),
                );

                $response = wp_remote_post( $ajax_url,
                    array(
                        'method'        => 'POST',
                        'timeout'       => 45,
                        'redirection'   => 5,
                        'httpversion'   => '1.0',
                        'blocking'      => true,
                        'headers'       => array(),
                        'body'          => $postfields,
                        'cookies'       => $_COOKIE
                    )
                );

                //fix for do not rewrite our roles
                wp_roles()->for_site();

            }


            //clear option from cache - because it's maybe changed in another process
            if ( wp_cache_get( 'wp_client_crash_updates', 'options' ) ) {
                wp_cache_delete( 'wp_client_crash_updates', 'options' );
            }

            //clear option from cache - because it's maybe changed in another process
            if ( wp_cache_get( 'wp_client_update_queue', 'options' ) ) {
                wp_cache_delete( 'wp_client_update_queue', 'options' );
            }

            //remove AJAX update if all updated fine
            $crash_updates = $this->get_crash_updates();
            if ( empty( $crash_updates ) ) {
                remove_action( 'admin_print_scripts', array( &$this, 'send_ajax_update_script' ), 999 );
            }
        }

    }

    function send_ajax_update_script() {
        $update_queue = $this->get_update_queue();

        //maybe return/exit
        if ( empty( $update_queue ) )
            return '';

        $ajax_url   = WPC()->get_ajax_url();
        $uid        = get_current_user_id();
        $token      = SECURE_AUTH_SALT;

        ?>

        <script type="text/javascript">

            jQuery( document ).ready( function() {
                var wpc_updater_queue_count = '<?php echo count( $update_queue ); ?>' * 1;
                var wpc_updater_count = 0;

                <?php

                foreach( $update_queue as $prefix => $version ) {

                    //remove current update from DB - for not duplicate updates
                    $this->delete_update_queue( $prefix );

                    ?>

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo $ajax_url; ?>',
                        data: 'action=wpc_updater&js_update=1&prefix=<?php echo $prefix; ?>&version=<?php echo $version; ?>&_nonce=<?php echo md5( $uid . $token . $prefix . $version . 'remote_update' ); ?>',
                        dataType: "json",
                        success: function( data ) {
                            wpc_updater_count++;
                            if ( wpc_updater_queue_count == wpc_updater_count ) {
                                window.location = window.location.href;
                            }
                        }
                    });

                    <?php
                }

                ?>

            });

        </script>


    <?php

    }


    function remote_update() {

        //fix for update files - if forgot write global $wpdb in some update
        global $wpdb;

        $_nonce     = !empty( $_REQUEST['_nonce'] ) ? $_REQUEST['_nonce'] : '';
        $prefix     = !empty( $_REQUEST['prefix'] ) ? $_REQUEST['prefix'] : '';
        $version    = !empty( $_REQUEST['version'] ) ? $_REQUEST['version'] : '';
        $uid        = get_current_user_id();
        $token      = SECURE_AUTH_SALT;


        //maybe return/exit
        if ( $_nonce != md5( $uid . $token . $prefix . $version . 'remote_update' ) ) {
            wp_die( json_encode( array( 'success' => false ) ) );
        }

        //if extension does not activated - exit from this function
        if ( ! $this->get_defined_version( $prefix )  ) {
            //remove current update from DB
            $this->delete_update_queue( $prefix );

            //remove crash update from DB
            $this->delete_crash_updates( $prefix );

            //return/exit
            wp_send_json_success();
        }

        $update_versions = $this->get_update_versions( $prefix );

        //updates
        $current_version = $this->get_current_version( $prefix );
        foreach ( $update_versions as $update_version => $update_file ) {
            if ( version_compare( $current_version, $update_version, '<' ) ) {

                //include update changes
                if ( file_exists( $update_file ) ) {
                    include_once $update_file;

                    //update current plugin\extension version to lst updated version
                    $this->update_current_version( $prefix, $update_version );
                }
            }
        }

        //update current plugin\extension version too latest version
        $this->update_current_version( $prefix, $this->get_defined_version( $prefix ) );

        //remove current update from DB
        $this->delete_update_queue( $prefix );

        //remove crash update from DB
        $this->delete_crash_updates( $prefix );

        if ( ob_get_length() ) {
            ob_end_clean();
        }

        wp_send_json_success();
    }


    function get_update_queue() {
        return get_option( 'wp_client_update_queue', array() );
    }

    function add_update_queue( $prefix = 'core', $version ) {

        $update_queue = $this->get_update_queue();

        $update_queue[$prefix] = $version;

        update_option( 'wp_client_update_queue', $update_queue, false );
    }

    function delete_update_queue( $prefix = 'core' ) {

        $update_queue = $this->get_update_queue();

        if ( ! empty( $update_queue[$prefix] ) ) {
            unset( $update_queue[$prefix] );
            update_option( 'wp_client_update_queue', $update_queue, false );
        }
    }

    function check_updates( $prefix = 'core' ) {
        $update_versions = $this->get_update_versions( $prefix );
        $current_version = $this->get_current_version( $prefix );
        foreach ( $update_versions as $update_version => $update_file ) {
            if ( version_compare( $current_version, $update_version, '<' ) ) {
                $this->add_update_queue( $prefix, $update_version );

                return '';
            }
        }

        $this->update_current_version( $prefix, $this->get_defined_version( $prefix ) );

        return '';
    }


    function get_update_dir( $prefix ) {
        if ( 'core' == $prefix ) {
            return WPC()->plugin_dir . 'includes' . DIRECTORY_SEPARATOR . 'updates';
        } elseif( '' != WPC()->extensions()->get_dir( $prefix ) ) {
            return WPC()->extensions()->get_dir( $prefix ) . 'includes' . DIRECTORY_SEPARATOR . 'updates';
        }
        return '';
    }


    function get_update_versions( $prefix ) {
        $dir = $this->get_update_dir( $prefix );

        $update_versions = array();

        if ( is_dir( $dir ) ) {
            $handle = opendir( $dir );
            while ( false !== ( $filename = readdir( $handle ) ) ) {
                if ( $filename != '.' && $filename != '..' ) {
                    $version = preg_replace( '/update-(.*?)\.php/i', '$1', $filename );
                    $update_versions[$version] = $dir . '/' . $filename;
                }
            }
            closedir( $handle );

            uksort( $update_versions, array( &$this, 'sort_update_versions' ) );
        }

        return $update_versions;
    }


    function sort_update_versions( $a, $b ) {
        return version_compare( $a, $b );
    }


    function get_crash_updates() {
        return get_option( 'wp_client_crash_updates', array() );
    }

    function set_crash_updates( $prefix = 'core', $version ) {

        $crash_updates = $this->get_crash_updates();

        $crash_updates[$prefix] = $version;

        update_option( 'wp_client_crash_updates', $crash_updates, false );
    }

    function delete_crash_updates( $prefix = 'core' ) {

        $crash_updates = $this->get_crash_updates();

        if ( ! empty( $crash_updates[$prefix] ) ) {
            unset( $crash_updates[$prefix] );
            update_option( 'wp_client_crash_updates', $crash_updates, false );
        }
    }


    function get_current_version( $prefix = 'core' ) {
        if ( 'core' == $prefix ) {
            return get_option( 'wp_client_ver', WPC_CLIENT_VER );
        } elseif( !empty( $prefix ) && defined( 'WPC_' . strtoupper( $prefix ) . '_VER' ) ) {
            return get_option( 'wpc_' . $prefix . '_ver', constant( 'WPC_' . strtoupper( $prefix ) . '_VER' ) );
        }

        return false;
    }

    function get_defined_version( $prefix = 'core' ) {
        if ( 'core' == $prefix ) {
            return constant( 'WPC_CLIENT_VER' );
        } elseif( !empty( $prefix ) && defined( 'WPC_' . strtoupper( $prefix ) . '_VER' ) ) {
            return constant( 'WPC_' . strtoupper( $prefix ) . '_VER' );
        }

        return false;
    }


    function update_current_version( $prefix = 'core', $version ) {
        if ( 'core' == $prefix ) {
            update_option( 'wp_client_ver', $version );
        } elseif( !empty( $prefix ) ) {
            update_option( 'wpc_' . $prefix . '_ver', $version );
        }
    }


    function can_install( $prefix = 'core' ) {
        $crash_updates = $this->get_crash_updates();
        $update_queue = $this->get_update_queue();

        if ( ! isset( $update_queue[$prefix] ) && ! isset( $crash_updates[$prefix] ) ) {
            return true;
        }

        return false;
    }

    function is_update( $prefix ) {

        if ( 'core' == $prefix ) {
            $current_version = get_option( 'wp_client_ver' );
            $defined_version = WPC_CLIENT_VER;
        } else {
            $current_version = get_option( 'wpc_' . $prefix . '_ver' );
            $defined_version = defined( 'WPC_' . strtoupper( $prefix ) . '_VER' ) ? constant( 'WPC_' . strtoupper( $prefix ) . '_VER' ) : '';
        }

        return version_compare( $current_version, $defined_version, '<' );
    }


    function is_crashed( $prefix = '' ) {
        //check if we are crashed
        $crash_updates =  $this->get_crash_updates();
        if ( ! empty( $prefix ) ) {
            return ! empty( $crash_updates[$prefix] );
        } elseif ( ! empty( $crash_updates ) ) {
            return true;
        }

        return false;
    }


    function deactivation( $prefix ) {

        $this->delete_crash_updates( $prefix );
        $this->delete_update_queue( $prefix );
    }

}

endif;