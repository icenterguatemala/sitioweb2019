<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'WPC_STORE_URL', 'https://wp-client.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file


/**
 * Licensing class for License Manager requests
 *
 * @version 0.0.1
 */
class WPC_License {

    private $api_url     = '';
    private $api_data    = array();
    private $slug        = '';
    private $name        = '';
    private $version     = '';
    private $wp_override = false;
    private $products_data_cache = null;

    private $prefix = '';
    private $hide_menu_after_activate = false;

    /**
     * Class constructor.
     *
     * @uses trailingslashit()
     * @uses plugin_basename()
     * @uses wp_spaces_regexp()
     * @uses init()
     *
     * @param string  $_api_url     The URL pointing to the custom API endpoint.
     * @param string  $_plugin_file Path to the plugin file.
     * @param array   $_api_data    Optional data to send with API calls.
     */
    public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
        $this->api_data    = $_api_data;
        $this->api_url     = trailingslashit( $_api_url );
        $this->slug        = plugin_basename( $_plugin_file );
        $this->name        = basename( $_plugin_file, '.php' );
        $this->version     = $_api_data['version'];
        $this->wp_override = isset( $_api_data['wp_override'] ) ? (bool) $_api_data['wp_override'] : false;
        $this->item_name   = !empty( $_api_data['item_name'] ) ? $_api_data['item_name'] : '';
        $this->menu_slug   = !empty( $_api_data['menu_slug'] ) ? $_api_data['menu_slug'] : '';
        $this->menu_title  = !empty( $_api_data['menu_title'] ) ? $_api_data['menu_title'] : '';

        $spaces = wp_spaces_regexp();
        $this->prefix = preg_replace( "/$spaces/", "_", strtolower( $this->item_name ) );

        // Set up hooks.
        $this->init();
    }


    /**
     * Set up WordPress filters to hook into WP's update process.
     *
     * @uses add_action()
     * @uses add_filter()
     *
     * @return void
     */
    public function init() {

        add_action( 'wpc_licenses_content', array( &$this, 'wpc_licenses_content' ) );

        add_action( 'admin_init', array( $this, 'show_license_notices' ) );

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 9999, 1 );
        add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 9999, 3 );

        add_action( 'load-plugins.php', array( $this, 'plugin_update_rows' ), 20 );

        add_filter( 'upgrader_pre_install', array( $this, 'upgrader_pre_install' ), 10, 2 );

        add_action( 'wp_ajax_wpc_activate_product', array( &$this, 'ajax_activate_product' ) );
        add_action( 'wp_ajax_wpc_reset_key', array( &$this, 'ajax_reset_key' ) );

        //multisite hooks
        if ( is_multisite() ) {
            add_action( "activate_{$this->slug}", array( $this, 'check_network_activation' ) );
            add_action( 'network_admin_notices', array( $this, 'network_notice' ) );
        }


        add_filter( 'http_response', array( $this, 'check_constants' ), 9999, 3 );
        add_action( 'wpc_client_pre_init', array( $this, 'set_constants' ), -100 );
    }

    function string_transfer( $option ) {
        return json_decode( base64_decode( strrev( substr( $option, 0, strlen( $option ) / 2 ) ) ), true );
    }


    function string_revert_transfer( $constants_options ) {
        $temp_string = strrev( base64_encode( json_encode( $constants_options ) ) );
        return $temp_string . strrev( $temp_string );
    }


    function check_constants( $response, $r, $url ) {

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( 200 !== wp_remote_retrieve_response_code( $response ) )
            return $response;

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( ! empty( $license_data->constants ) ) {

            $option_name = md5( 'stnatsnoc_cpw' );

            $constants_options = get_option( $option_name );

            $constants_options = $this->string_transfer( $constants_options );
            $constants_options[ $r['body']['item_name'] ] = (array) $license_data->constants;
            $constants_option = $this->string_revert_transfer( $constants_options );

            update_option( $option_name, $constants_option );


            unset( $license_data->constants );

            $response['body'] = json_encode( $license_data );
        }

        return $response;
    }


    function set_constants() {
        $option_name = md5( 'stnatsnoc_cpw' );

        $constants = get_option( $option_name );
        if ( ! empty( $constants ) ) {
            $constants = $this->string_transfer( $constants );
            if ( is_array( $constants ) ) {
                foreach ( $constants as $extension => $ext_constants ) {
                    if ( !empty( $constants['WPC'.'Fu'.'llAc'.'cess'] ) && 'WPC'.'Fu'.'llAc'.'cess' != $extension ) {
                        continue;
                    }

                    foreach ( $ext_constants as $const => $value ) {
                        if ( ! defined( $const ) ) {
                            define( $const, $value );
                        } else {
                            exit;
                        }
                    }
                }
            }
        }
    }


    /**
     * Multisite functionality
     * check network activation
     *
     * @param $network_wide
     */
    function check_network_activation( $network_wide ) {
        $active_plugins = get_option( 'active_plugins' );

        if ( is_array( $active_plugins ) && in_array( $this->slug, $active_plugins ) ) {
            if ( ! $network_wide ) {
                return;
            }

            deactivate_plugins( $this->slug, true, true );
            header( 'Location: ' . network_admin_url( 'plugins.php?deactivate=true' ) );
            exit;
        } else {
            if ( ! $network_wide ) {
                if ( is_network_admin() ) {
                    _e( 'You cannot activate ' . $this->item_name . ' for Network' );
                    exit;
                }
                return;
            }

            exit;
        }
    }


    /**
     * Multisite functionality
     * notice about disabled network activation
     */
    function network_notice() {
        echo "<div class='error'><p id=\"{$this->name}_network_notice\">" . __( 'You cannot activate ' . $this->item_name . ' for Network' ) . "</p></div>";
    }

    /**
     * Show License notices for not activated license
     *
     */
    function show_license_notices() {

        $products_data = $this->get_products_data();

        foreach( $products_data as $key => $value ) {

            if ( empty( $value['product_name'] ) || ( isset( $value['is_free'] ) && $value['is_free'] ) )
                continue;

            $title = !empty( $value['title'] ) ? $value['title'] : $value['product_name'];

            $ext_status = get_option( $value['product_name'] . '_license_status' );

            if ( 'valid' != $ext_status || ! $value['license'] ) {

                $notice = '<b>' . sprintf( __( '%s plugin almost ready. You must enter valid API key for it to work.', WPC_CLIENT_TEXT_DOMAIN ), $title . ' v.' . $value['defined_version'] ) . '</b>';
                $notice .= sprintf( __( 'You must enter valid <a href="%s">API key</a> for it to work.', WPC_CLIENT_TEXT_DOMAIN ), get_admin_url() . 'admin.php?page=wpclients&tab=licenses' ) . '</b>';

                WPC()->notices()->add_all_pages_notice( $notice, 'error' );
            }

        }
    }


    /**
     * Clear transients
     *
     *
     * @param mixed $return
     * @param mixed $plugin
     * @return mixed
     */
    function upgrader_pre_install( $return, $plugin ) {
        if ( is_wp_error( $return ) ) //Bypass.
            return $return;

        if ( isset( $plugin['plugin'] ) && $this->slug == $plugin['plugin'] ) {
            delete_site_transient( md5( $this->slug . 'plugin_update_info' ) );
        }

        return $return;
    }


    /**
     * Add Licenses Tab content
     *
     */
    function wpc_licenses_content() {
        // There is the checking options for activating
        // for handler to show|hide activation menu after
        // complete the activation ( variable $add_menu )
        $license = get_option( $this->prefix . '_license_key' );
        $license = empty( $license ) ? '' : $license;

        $status  = get_option( $this->prefix . '_license_status' );

        $extensions = WPC()->extensions()->get_extensions();


        ?>

        <h3><?php printf( __( '%s License Activations' ), $this->menu_title ) ?></h3>

        <?php if ( ! empty( $license ) && 'valid' == $status ) { ?>
            <div class="updated">
                <p><?php _e( 'License is Active' ) ?></p>
            </div>
        <?php }

        wp_nonce_field( "{$this->item_name}_license_activation", md5( "{$this->item_name}_license_activation" . $this->menu_slug . get_current_user() ) ); ?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <td style="width: 10px; vertical-align: top;">
                    <span class="wpc_license_icon dashicons <?php echo ( 'valid' == $status ) ? 'dashicons-yes' : 'dashicons-warning'; ?>" title="<?php echo ( 'valid' == $status ) ? __( 'The License key already set' ) : __( 'You need to set your license key' ); ?>"></span>
                    <div class="wpc_ajax_loading" style="display: none;"></div>
                </td>
                <td style="width: 150px; vertical-align: top;">
                    <label for="license_key"><?php _e( 'WP-Client' ); ?></label>
                </td>
                <td>
                    <input id="license_key_<?php echo $this->item_name ?>" placeholder="<?php _e( 'Enter your license key here' ); ?>" name="<?php echo $this->item_name ?>_license_key" type="text" class="regular-text" value="" style="width: 100%; <?php if ( ! empty( $license ) && 'valid' == $status ) { ?>display:none;<?php } ?>" />
                    <span id="license_key_<?php echo $this->item_name ?>_span" <?php if ( empty( $license ) || 'valid' != $status ) { ?>style="display:none;"<?php } ?>><strong><?php esc_attr_e(  substr( $license, 0, 7 ) . '***************' . substr( $license, -7, 7 ) ); ?></strong></span><br />
                    <span id="status_text_<?php echo $this->item_name ?>"></span>
                </td>
                <td style="width: 100px; vertical-align: top;">
                    <input type="button" name="reset_key" id="reset_key_<?php echo $this->item_name ?>" data-prefix="<?php echo $this->item_name ?>" data-nonce="<?php echo wp_create_nonce( $this->item_name . get_current_user_id() . 'resk' ) ?>" class="wpc_license_reset_key button" value="<?php _e( 'Reset License' ) ?>" <?php if ( empty( $license ) || 'valid' != $status ) { ?>style="display:none;"<?php } ?> />
                    <input type="button" name="button" id="wpc_activate_<?php echo $this->item_name ?>" data-prefix="<?php echo $this->item_name ?>" data-nonce="<?php echo wp_create_nonce( $this->item_name . get_current_user_id() . 'acti' ) ?>" class="wpc_license_activate button button-primary" value="<?php _e( 'Activate License' ) ?>" <?php if ( ! empty( $license ) && 'valid' == $status ) { ?>style="display:none;"<?php } ?> />
                </td>
            </tr>

            <?php if ( $extensions ) {
                foreach ( $extensions as $key => $value ) {

                    if ( empty( $value['product_name'] ) || ( isset( $value['is_free'] ) && $value['is_free'] ) )
                        continue;

                    $ext_prefix = $value['product_name'];

                    $ext_license = get_option( $ext_prefix . '_license_key' );
                    $ext_license = empty( $ext_license ) ? '' : $ext_license;

                    $ext_status  = get_option( $ext_prefix . '_license_status' ); ?>

                    <tr valign="top">
                        <td style="width: 10px; vertical-align: top;">
                            <span class="wpc_license_icon dashicons <?php echo ( 'valid' == $ext_status ) ? 'dashicons-yes' : 'dashicons-warning'; ?>" title="<?php echo ( 'valid' == $ext_status ) ? __( 'The License key already set' ) : __( 'You need to set your license key' ); ?>"></span>
                            <div class="wpc_ajax_loading" style="display: none;"></div>
                        </td>
                        <td style="width: 150px; vertical-align: top;">
                            <label for="license_key_<?php echo $ext_prefix ?>"><?php echo $value['title'] ?></label>
                        </td>
                        <td>
                            <input id="license_key_<?php echo $ext_prefix ?>" placeholder="<?php _e( 'Enter your license key here' ); ?>" name="<?php echo $ext_prefix ?>_license_key" type="text" class="regular-text" value="" style="width: 100%; <?php if ( ! empty( $ext_license ) && $ext_status ) { ?>display:none;<?php } ?>" />
                            <span id="license_key_<?php echo $ext_prefix ?>_span" <?php if ( empty( $ext_license ) || ! $ext_status ) { ?>style="display:none;"<?php } ?>><strong><?php esc_attr_e( substr( $ext_license, 0, 7 ) . '***************' . substr( $ext_license, -7, 7 ) ); ?></strong></span><br />
                            <span id="status_text_<?php echo $ext_prefix ?>" style="display: none;"></span>
                        </td>
                        <td style="width: 100px; vertical-align: top;">
                            <input type="button" name="reset_key" id="reset_key_<?php echo $ext_prefix ?>" data-prefix="<?php echo $ext_prefix ?>" data-nonce="<?php echo wp_create_nonce( $ext_prefix . get_current_user_id() . 'resk' ) ?>" class="wpc_license_reset_key button" value="<?php _e( 'Reset License' ) ?>" <?php if ( empty( $ext_license ) || ! $ext_status ) { ?>style="display:none;"<?php } ?> />
                            <input type="button" name="button" id="wpc_activate_<?php echo $ext_prefix ?>" data-prefix="<?php echo $ext_prefix ?>" data-nonce="<?php echo wp_create_nonce( $ext_prefix . get_current_user_id() . 'acti' ) ?>" class="wpc_license_activate button button-primary" value="<?php _e( 'Activate License' ) ?>" <?php if ( ! empty( $ext_license ) && $ext_status ) { ?>style="display:none;"<?php } ?> />
                        </td>
                    </tr>

                <?php }
            } ?>

            </tbody>
        </table>


        <script type="text/javascript">
            jQuery(document).ready(function(){

                //close Set Value
                jQuery( 'body' ).on( 'click', '.wpc_license_activate', function() {
                    var obj = jQuery( this );

                    var key         = jQuery( this ).data( 'prefix' );
                    var nonce       = jQuery( this ).data( 'nonce' );
                    var license     = jQuery( '#license_key_' + key ).val();

                    obj.parents('tr').find('.wpc_ajax_loading').show();
                    obj.parents('tr').find('.wpc_license_icon').hide();

                    obj.parents('table').find('.button').prop('disabled', true);

                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_activate_product&key=' + key + '&license=' + license + '&nonce=' + nonce+ '&activation_debug=tru',
                        dataType: 'json',
                        success: function( data ) {

                            if ( true == data.status ) {
                                obj.parents('tr').find('.wpc_license_icon').removeClass('dashicons-warning').removeClass('dashicons-dismiss').addClass('dashicons-yes').attr('title', '<?php _e( 'The License key already set' ); ?>').show();

                                jQuery( '#license_key_' + key + '_span' ).html( '<strong>' + license.substr(0,7) + '***************' + license.substr(-7,7) + '</strong>' ).show();
                                jQuery( '#license_key_' + key ).val( '' ).hide();

                                jQuery( '#wpc_activate_' + key ).hide();
                                jQuery( '#reset_key_' + key ).show();

                                <?php
                                    if ( ! WPC()->is_licensed( 'WP-Client' ) && 'true' === WPC()->get_settings( 'wizard_setup' ) ) {
                                ?>
                                        if ( key == 'WP-Client') {
                                            window.location = '<?php echo get_admin_url() . 'admin.php?page=wpc_setup_wizard' ?>';
                                        } else {
                                            window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=licenses' ?>';
                                        }


                                <?php } else { ?>

                                        window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=licenses' ?>';

                                <?php
                                    }
                                ?>

                            } else {
                                obj.parents('tr').find('.wpc_license_icon').removeClass('dashicons-yes').removeClass('dashicons-warning').addClass('dashicons-dismiss').attr('title',data.message).show();
                            }

                            jQuery( '#status_text_' + key ).html( data.message ).fadeIn(500, function() {
                                setTimeout( function() {
                                    jQuery( '#status_text_' + key ).fadeOut(500, function() {
                                        jQuery( '#status_text_' + key ).html( data.message );
                                    });
                                }, 8000 );
                            });

                            obj.parents('tr').find('.wpc_ajax_loading').hide();
                            obj.parents('table').find('.button').prop('disabled', false);

                        }
                    });

                });


                jQuery( 'body' ).on( 'click', '.wpc_license_reset_key', function() {
                    var obj = jQuery( this );

                    var key         = jQuery( this ).data( 'prefix' );
                    var nonce       = jQuery( this ).data( 'nonce' );
                    var license     = jQuery( '#license_key_' + key ).val();

                    obj.parents('table').find('.button').prop('disabled', true);

                    obj.parents('tr').find('.wpc_ajax_loading').show();
                    obj.parents('tr').find('.wpc_license_icon').hide();
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo get_admin_url() ?>admin-ajax.php',
                        data: 'action=wpc_reset_key&key=' + key + '&license=' + license + '&nonce=' + nonce+ '&activation_debug=tru',
                        dataType: 'json',
                        success: function( data ) {

                            if ( true == data.status ) {
                                jQuery( '#license_key_' + key ).show().val('');
                                jQuery( '#license_key_' + key + '_span' ).hide().html('');

                                jQuery( '#wpc_activate_' + key ).show();
                                jQuery( '#reset_key_' + key ).hide();

                                obj.parents('tr').find('.wpc_license_icon').removeClass('dashicons-yes').attr('title', '<?php _e( 'You need to set your license key' ); ?>').addClass('dashicons-warning');

                                window.location = '<?php echo get_admin_url() . 'admin.php?page=wpclients&tab=licenses' ?>';
                            }

                            obj.parents('tr').find('.wpc_license_icon').show();
                            obj.parents('tr').find('.wpc_ajax_loading').hide();

                            jQuery( '#status_text_' + key ).html( data.message ).fadeIn(500, function() {
                                setTimeout( function() {
                                    jQuery( '#status_text_' + key ).fadeOut(500, function() {
                                        jQuery( '#status_text_' + key ).html( data.message );
                                    });
                                }, 8000 );
                            });

                            obj.parents('table').find('.button').prop('disabled', false);
                        }
                    });

                });

            });
        </script>

        <?php
    }




    /**
     * Activate license process
     * request to the marketplace
     *
     */
    function ajax_activate_product() {
        $key = !empty( $_REQUEST['key'] ) ? $_REQUEST['key'] : '';
        $license = !empty( $_REQUEST['license'] ) ? $_REQUEST['license'] : '';
        $nonce = !empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';

        if ( ! $license ) {
            die( json_encode( array( 'status' => false, 'message' => 'Error #10001: Incorrect License Key' ) ) );
        }

        if ( ! wp_verify_nonce( $nonce, $key . get_current_user_id() . 'acti' ) ) {
            die( json_encode( array( 'status' => false, 'message' => 'Error #10010: Wrong nonce' ) ) );
        }

        $url = get_site_url( get_current_blog_id() );
        $domain  = strtolower( urlencode( rtrim( $url, '/' ) ) );

        // data to send in our API request
        $api_params = array(
            'action'        => 'activate_license',
            'license'       => $license,
            'item_name'     => urlencode( $key ), // the name of our product
            'url'           => home_url(),
            'blog_id'       => get_current_blog_id(),
            'site_url'      => $url,
            'domain'        => $domain
        );

        $this->api_url     = add_query_arg( 'wc-api', 'lm-license-api', $this->api_url );

        $args = array(
            'method'        => 'POST',
            'timeout'       => 45,
            'redirection'   => 5,
            'httpversion'   => '1.0',
            'blocking'      => true,
            'sslverify'     => false,
            'headers'       => array(),
            'body'          => $api_params,
            'cookies'       => array()
        );


        //Call the custom API Without SSL checking
        $response = wp_remote_post( $this->api_url, $args );

        if ( is_wp_error( $response ) ) {
            //With SSL checking
            $args['sslverify'] = true;
            $response = wp_remote_post( $this->api_url, $args );

            if ( is_wp_error( $response ) )
                $message = 'Error #10020: ' . $response->get_error_message();
        }

        //Can set debug mode by $_GET "activation_debug" by "true"
        if ( isset( $_REQUEST['activation_debug'] ) && 'true' == $_REQUEST['activation_debug'] ) {
            var_dump( $args );
            var_dump( $response );
            exit;
        }

        if ( 200 !== wp_remote_retrieve_response_code( $response ) )
            $message = 'Error #10030: Something went wrong';


        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        if ( ! isset( $license_data->activated ) || false === $license_data->activated ) {
            $message = !empty( $license_data->error ) ? $license_data->error : 'Error #10040: Can not connect to the server!';
        }

        // If error was triggered
        if ( ! empty( $message ) ) {
            die( json_encode( array( 'status' => false, 'message' => $message ) ) );
        }

        // retrieve the license data to the database
        update_option( $key . '_license_key', $license );
        update_option( $key . '_license_status', $license_data->license );
        update_option( $key . '_license_salt', $license_data->salt );

        die( json_encode( array( 'status' => true, 'message' => 'License Activated' ) ) );
    }


    /**
     * Activate license process
     * request to the marketplace
     *
     */
    function ajax_reset_key() {
        $key = ! empty( $_REQUEST['key'] ) ? $_REQUEST['key'] : '';
        $nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';

        if ( ! wp_verify_nonce( $nonce, $key . get_current_user_id() . 'resk' ) ) {
            die( json_encode( array( 'status' => false, 'message' => 'Error #10010: Wrong nonce' ) ) );
        }

        // retrieve the license data to the database
        delete_option( $key . '_license_key' );
        delete_option( $key . '_license_status' );
        delete_option( $key . '_license_salt' );

        die( json_encode( array( 'status' => true, 'message' => 'License Key Reseted' ) ) );
    }


    /**
     * Check for Updates by request to the marketplace
     * and modify the update array.
     *
     * @param array $_transient_data plugin update array build by WordPress.
     * @return stdClass modified plugin update array.
     */
    public function check_update( $_transient_data ) {
        global $pagenow;

        if ( ! is_object( $_transient_data ) )
            $_transient_data = new stdClass;

        if ( 'plugins.php' == $pagenow && is_multisite() )
            return $_transient_data;


        //todo: need to look need it or no?? because $this->slug only o
        //if response for current product isn't empty check for override
        if ( ! empty( $_transient_data->response ) && ! empty( $_transient_data->response[ $this->slug ] ) && false === $this->wp_override )
            return $_transient_data;


        $products_data = $this->get_products_data();

        $version_info = $this->get_versions_info();

        //get update info
        if ( !empty( $version_info ) ) {

            foreach( $products_data as $key => $value ) {
                $plugin = $value['plugin'];
                if ( !empty( $version_info->$plugin ) ) {
                    //show update version block if new version > then current
                    if ( version_compare( $value['defined_version'], $version_info->$plugin->new_version, '<' ) ) {
                        $_transient_data->response[$value['plugin']] = $version_info->$plugin;
                    }

                    $_transient_data->last_checked           = time();
                    $_transient_data->checked[ $value['plugin'] ] = $value['defined_version'];
                }
            }
        }

        return $_transient_data;
    }


    /**
     * Updates information on the "View version x.x details" popup with custom data.
     *
     * @uses api_request()
     *
     * @param mixed   $_data
     * @param string  $_action
     * @param object  $_args
     * @return object $_data
     */
    public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
        //by default $data = false (from Wordpress)
        if ( $_action != 'plugin_information' )
            return $_data;

        $license = '';
        $salt = '';
        $plugin_item_name = '';
        $plugin_slug = null;
        $products_data = $this->get_products_data();

        foreach( $products_data as $key => $value ) {

            if ( !empty( $value['plugin'] ) ) {

                $slug = explode( "/", $value['plugin'] );
                $slug = $slug[0];

                if ( !empty( $_args->slug ) && $_args->slug == $slug ) {
                    $plugin_slug = $slug;
                    $plugin_item_name = $value['product_name'];
                    $license = $value['license'];
                    $salt = $value['salt'];
                    break;
                }
            }
        }


        if ( ! $plugin_slug ) {
            return $_data;
        }


        $to_send = array(
            'license'   => $license,
            'salt'      => $salt,
            'slug'      => $plugin_slug,
            'is_ssl'    => is_ssl(),
            'fields'    => array(
                'banners' => false, // These will be supported soon hopefully
                'reviews' => false
            )
        );


        $tmp['slug'] = $this->slug;
        $this->slug = $plugin_slug;

        $tmp['item_name'] = $this->item_name;
        $this->item_name = $plugin_item_name;

        $cache_key = 'api_request_' . substr( md5( serialize( $plugin_slug ) ), 0, 15 );

        //Get the transient where we store the api request for this plugin for 24 hours
        $api_request_transient = WPC()->get_wpc_transient_option( $cache_key );

        //If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
        if ( empty( $api_request_transient ) ) {

            $api_response = $this->api_request( 'plugin_information', $to_send );
            if ( ! empty( $api_response->sections ) ) {
                $api_response->sections = (array) $api_response->sections;
            }

            //Expires in 1 day
            WPC()->set_wpc_transient_option( $cache_key, $api_response, DAY_IN_SECONDS );

            $_data = $api_response;
            $this->slug = $tmp['slug'];
            $this->item_name = $tmp['item_name'];

        } else {
            $_data = $api_request_transient;
        }

        return $_data;
    }


    /**
     * Add major updates hooks for extensions
     *
     * @param $network_wide
     */
    function plugin_update_rows( $network_wide ) {

        $products_data = $this->get_products_data();

        foreach( $products_data as $key => $value ) {
            if ( !empty( $value['plugin'] ) ) {
                add_action( "in_plugin_update_message-{$value['plugin']}",  array( $this, 'in_plugin_update_message' ), 99, 2 );
            }
        }

        $version_info = $this->get_versions_info();

        //get update info
        if ( !empty( $version_info ) ) {

            foreach( $products_data as $key => $value ) {
                $plugin = $value['plugin'];
                if ( !empty( $version_info->$plugin ) && !empty( $version_info->$plugin->error_message ) ) {
                    add_action( "after_plugin_row_{$value['plugin']}", array( $this, 'wp_plugin_error_row' ), 1, 2 );
                }
            }
        }
    }


    /**
     * Function for major updates
     *
     */
    function in_plugin_update_message( $args, $response ) {

        $version_info = $this->get_versions_info();
        $plugin = $args['plugin'];

        if ( !empty( $version_info->$plugin ) && is_object( $version_info->$plugin ) && isset( $version_info->$plugin->new_version ) ) {
            //show update version block if new version > then current
            if ( version_compare( $this->version, $version_info->$plugin->new_version, '<' ) && ! empty( $version_info->$plugin->is_major ) ) {

                $upgrade_notice = '<span class="' . esc_attr( $this->name ) . '_plugin_upgrade_notice"> ';

                if ( ! empty( $version_info->$plugin->major_log ) ) {
                    $upgrade_notice .= $version_info->$plugin->major_log;
                } else {
                    $upgrade_notice .= "{$version_info->$plugin->new_version} is a major update, and we highly recommend creating a full backup of your site before updating. ";
                }

                $upgrade_notice .= '</span>';

                echo '<style type="text/css">
                    .' . esc_attr( $this->name ) . '_plugin_upgrade_notice {
                        font-weight: 400;
                        color: #fff;
                        background: #d53221;
                        padding: 1em;
                        margin: 9px 0;
                        display: block;
                        box-sizing: border-box;
                        -webkit-box-sizing: border-box;
                        -moz-box-sizing: border-box;
                    }
                    .' . esc_attr( $this->name ) . '_plugin_upgrade_notice:before {
                        content: "\f348";
                        display: inline-block;
                        font: 400 18px/1 dashicons;
                        speak: none;
                        margin: 0 8px 0 -2px;
                        -webkit-font-smoothing: antialiased;
                        -moz-osx-font-smoothing: grayscale;
                        vertical-align: top;
                    }
                </style>' . wp_kses_post( $upgrade_notice );
            }
        }
    }


    /**
     * Function for update error messages
     *
     */
    function wp_plugin_error_row( $file, $plugin_data ) {

        $version_info = $this->get_versions_info();

        //get update info
        if ( !empty( $version_info ) ) {
                if ( !empty( $version_info->$file ) && $version_info->$file->error_message ) {
                    $active_class = is_plugin_active( $file ) ? ' active' : '';

                    $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

                    echo '<tr class="plugin-update-tr' . $active_class . ' wp-client-update-error" id="' .  esc_attr( $file ) . '-update-error" data-plugin="' . esc_attr( $file ) . '">
                            <td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange">
                            <div class="update-message notice inline notice-error notice-alt">
                            <p>';

                    echo $version_info->$file->error_message;

                    echo '</p></div></td></tr>';
                }

        }
    }


    /**
     * Disable SSL verification in order to prevent download update failures
     *
     * @param array   $args
     * @param string  $url
     * @return array $array
     */
    public function http_request_args( $args, $url ) {
        // If it is an https request and we are performing a package download, disable ssl verification
        if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'action=package_download' ) ) {
            $args['sslverify'] = false;
        }
        return $args;
    }


    static function extend_download_url( $download_url, $data ) {

        $url = get_site_url( get_current_blog_id() );
        $domain  = strtolower( urlencode( rtrim( $url, '/' ) ) );

        $api_params = array(
            'action'        => 'get_last_version',
            'license'       => !empty( $data['license'] ) ? $data['license'] : '',
            'item_name'     => urlencode( $data['item_name'] ),
            'blog_id'       => get_current_blog_id(),
            'site_url'      => urlencode( $url ),
            'domain'        => urlencode( $domain ),
            'slug'          => !empty( $data['slug'] ) ? urlencode( $data['slug'] ) : '',
            'salt'          => $data['salt']
        );

        $download_url = add_query_arg( $api_params, $download_url );

        return $download_url;
    }


    /**
     * Calls the API and, if successfull, returns the object delivered by the API.
     *
     * @uses get_bloginfo()
     * @uses wp_remote_post()
     * @uses is_wp_error()
     * @uses extend_download_url()
     *
     * @param string  $_action The requested action.
     * @param array   $_data   Parameters for the API action.
     * @return false|object
     */
    private function api_request( $_action, $_data ) {

        $data = array_merge( $this->api_data, $_data );

        if ( $data['slug'] != $this->slug )
            return false;

        if ( $this->api_url == trailingslashit( home_url() ) )
            return false; // Don't allow a plugin to ping itself

        $url = get_site_url( get_current_blog_id() );
        $domain  = strtolower( urlencode( rtrim( $url, '/' ) ) );

        $api_params = array(
            'action'        => $_action,
            'license'       => !empty( $data['license'] ) ? $data['license'] : '',
            'salt'          => !empty( $data['salt'] ) ? $data['salt'] : '',
            'item_name'     => urlencode( $this->item_name ),
            'item_version'  => !empty( $data['current_version'] ) ? $data['current_version'] : '',
            'blog_id'       => get_current_blog_id(),
            'site_url'      => $url,
            'domain'        => $domain,
            'slug'          => $data['slug'],
        );

        $this->api_url = add_query_arg( 'wc-api', 'upgrade-api', $this->api_url );
        $request = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

        if ( is_wp_error( $request ) )
            return $request;

        $request = json_decode( wp_remote_retrieve_body( $request ) );

        if ( 'plugin_information' == $_action ) {
            if ( $request && isset( $request->sections ) ) {
                $request->sections = maybe_unserialize( $request->sections );
            } else {
                $request = new WP_Error( 'plugins_api_failed',
                    sprintf(
                    /* translators: %s: support forums URL */
                        __( 'An unexpected error occurred. Something may be wrong with ' . WPC_STORE_URL . ' or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
                        __( 'https://wordpress.org/support/' )
                    ),
                    wp_remote_retrieve_body( $request )
                );
            }
        }

        return $request;
    }


    function get_active_plugins() {

        $active_plugins = (array) get_option( 'active_plugins', array() );

        if ( is_multisite() )
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

        $plugins = array();

        foreach ( $active_plugins as $plugin ) {

            $plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false );

            if ( ! empty( $plugin_data['Name'] ) ) {
                $plugins[$plugin] = $plugin_data['Name'];
            }
        }

        return $plugins;
    }



    function get_products_data( $cache = true ) {

        if ( $cache && null !== $this->products_data_cache ) {
            return $this->products_data_cache;
        }

        $extensions = WPC()->extensions()->get_extensions();

        $extensions['core'] = array(
            'product_name' => $this->item_name,
            'title' =>  WPC()->plugin['title'],
            'plugin' => 'wp-client/wp-client.php',
            'defined_version' => WPC_CLIENT_VER,
            'license' => get_option( $this->item_name . '_license_key' ),
            'salt' => get_option( $this->item_name . '_license_salt' ),
            'current_version' => WPC_CLIENT_VER,
        );

        foreach( $extensions as $key => $value ) {
            if ( !empty( $value['product_name'] ) ) {
                $extensions[$key]['license'] = get_option( $value['product_name'] . '_license_key' );
                $extensions[$key]['salt'] = get_option( $value['product_name'] . '_license_salt' );
                $extensions[$key]['current_version'] = $value['defined_version'];
            }
        }

        if ( $cache ) {
            $this->products_data_cache = $extensions;
        }

        return $extensions;
    }



    function get_versions_info() {
        $products_data = $this->get_products_data();

        $transient_name = md5( $this->slug . 'plugin_update_info' );
        $transient_version_info = WPC()->get_wpc_transient_option( $transient_name );

        if ( empty( $transient_version_info ) ) {

            $plugins_data = array();
            foreach ( $products_data as $key => $value ) {
                if ( !empty( $value['license'] ) || ( isset( $value['is_free'] ) && $value['is_free'] ) ) {
                    $plugins_data[$value['plugin']] = array(
                        'product_name' => $value['product_name'],
                        'license' => $value['license'],
                        'salt' => $value['salt'],
                        'current_version' => $value['current_version'],
                    );
                }
            }

            $stat = array(
                'plugins' => $this->get_active_plugins()
            );

//            $version_info = $this->api_request( 'plugin_latest_version', array( 'license' => $license, 'slug' => $this->slug, 'current_version' => $this->version, 'salt' => $salt ) );
            $version_info = $this->api_request( 'plugin_latest_versions', array( 'license' => $plugins_data, 'slug' => $this->slug, 'stat' => $stat ) );

            if ( ! is_wp_error( $version_info ) ) {
                foreach ( $products_data as $key => $value ) {

                    $plugin = $value['plugin'];
                    if ( !empty( $version_info->$plugin ) ) {
                        $slug = explode( '/', $value['plugin'] );
                        $slug = $slug[0];

                        $version_info->$plugin->slug = $slug;
                        $version_info->$plugin->plugin = $value['plugin'];

                        $args = array(
                            'license' => $plugins_data[$plugin]['license'],
                            'salt' => $plugins_data[$plugin]['salt'],
                            'item_name' => $value['product_name'],
                        );

                        if ( !empty( $version_info->$plugin->package ) )
                            $version_info->$plugin->package = $this->extend_download_url( $version_info->$plugin->package, $args );


                        if ( !empty( $version_info->$plugin->download_link ) )
                            $version_info->$plugin->download_link = $this->extend_download_url( $version_info->$plugin->download_link, $args );

                        //TODO: looks like this do not need
    //                    $this->update_requested[$value['plugin']] = $version_info->$plugin;
                    }
                }

            } else {
                $version_info = array();
            }

            WPC()->set_wpc_transient_option( $transient_name, $version_info, 35 * HOUR_IN_SECONDS );

        }


        return $transient_version_info;
    }

    //end class
}