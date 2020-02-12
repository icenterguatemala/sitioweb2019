<?php

//Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WPClient_Functions' ) ) :

/**
 * functions of WPClient_Functions Class
 *
 * @class WPClient_Functions
 * @version    1.0.0
 */
class WPClient_Functions {

    /**
     * WPClient plugin dir
     *
     * @var string
     */
    public $plugin_dir = '';

    /**
     * WPClient plugin url
     *
     * @var string
     */
    public $plugin_url = '';

    /**
     * Cache for our settings
     *
     * @var array
     */
    public $cache_settings = array();

    /**
     * Custom titles
     *
     * @var array
     */
    var $custom_titles = array();

    /**
     * Plugin data
     *
     * @var array
     */
    var $plugin = array();

    /**
     * Plugin upload dir
     *
     * @var string
     */
    var $upload_dir = null;

    /**
     * permalinks settings
     *
     * @var string
     */
    var $permalinks = false;

    /**
     * Plugin upload URL
     *
     * @var string
     */
    var $upload_url = '';

    /**
     * All plugin flags for anything
     *
     * @var array
     */
    var $flags = array();

    /**
     * Classes objects for updates (core + extensions)
     *
     * @var array
     */
    var $update_classes = array();

    /**
     * All data of shortcodes
     *
     * @var array
     */
    public $shortcode_data = array();


    public $current_plugin_page = array();


    /**
     * Define constant if not already set.
     *
     * @param string $name
     * @param string|bool $value
     */
    protected function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     * @return bool
     */
    function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return ( ! defined( 'DOING_AJAX' ) && is_admin() );
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }

        return false;
    }

    /**
     * Get plugin settings
     *
     * @param string $key
     * @param array $default_value
     *
     * @return array|bool
     */
    function get_settings( $key, $default_value = array() ) {

        if ( empty( $key ) ) {
            return false;
        }


        //cache settings
        if ( isset( $this->cache_settings[$key] ) ) {
            $s = $this->cache_settings[$key];
        } else {
            $option = 'wpc_' . $key;
            $s = $this->recursive_strip_slashes( get_option( $option, $default_value ) );
            $this->cache_settings[$key] = $s;

            //delete option from wp-cache
            $alloptions = wp_cache_get( 'alloptions', 'options' );
            if ( is_array( $alloptions ) && isset( $alloptions[$option] ) ) {
                unset( $alloptions[$option] );
                wp_cache_set( 'alloptions', $alloptions, 'options' );
            }
        }

        return $s;
    }

    /**
     * Recursive strip slashes for plugin settings from DB
     *
     * @param string|array $data
     *
     * @return array
     */
    function recursive_strip_slashes( $data ) {
        if( is_string( $data ) ) {
            return stripslashes( $data );
        } else if( is_array( $data ) ) {
            $result = array();
            foreach( $data as $k=>$val ) {
                $result[ $k ] = self::recursive_strip_slashes( $val );
            }
            return $result;
        } else {
            return $data;
        }
    }

    /**
     * Delete plugin settings
     *
     * @param string $key
     *
     * @return bool
     */
    function delete_settings( $key ) {

        if ( empty( $key ) ) {
            return false;
        }

        return delete_option( 'wpc_' . $key );
    }

    /**
     * Make correct URL
     *
     * @param string $link
     * @param string $url
     *
     * @return string
     */
    function make_url( $link, $url = '' ) {
        global $wp_rewrite;

        if ( ! empty( $url ) ) {
            $url = rtrim( $url, '/' ) . '/';
        }

        if ( ! empty( $wp_rewrite ) && $wp_rewrite->using_index_permalinks() ) {
            $url .= $wp_rewrite->index . '/' . ltrim( $link, '/' );
        } else {
            $url .= ltrim( $link, '/' );
        }

        return $url;
    }

    /**
     * Get slug for wpc_page
     *
     * @param string $page
     * @param bool|true $with_end_slash
     * @param bool|true $full_url
     *
     * @return mixed|string
     */
    function get_slug( $page = '', $with_end_slash = true, $full_url = true ) {

        if ( '' != $page ) {
            $wpc_pages = WPC()->get_settings( 'pages' );

            if ( isset( $wpc_pages[$page] ) && 0 < $wpc_pages[$page] ) {

                if ( 'page' == get_option( 'show_on_front' ) && $wpc_pages[$page] == get_option( 'page_on_front' ) ) {
                    return home_url();
                }

                $post = get_post( $wpc_pages[$page] );
                $post = apply_filters( 'wpc_get_slug_post', $post, $wpc_pages[$page] );

                if ( isset( $post->post_name ) && '' != $post->post_name ) {
                    $url = get_page_uri( $post->ID );

                    if ( $full_url ) {
                        if ( is_multisite() ) {
                            $url = get_home_url( get_current_blog_id(), $url );
                        } else {
                            if ( WPC()->permalinks ) {
                                $url = get_home_url( null, $url );
                            } else {
                                $url = _get_page_link( $post );
                            }

                        }
                    }
                    $query_params = parse_url($url, PHP_URL_QUERY);
                    $url = rtrim( $url, '/' );
                    if ( $with_end_slash && WPC()->permalinks && !$query_params ) {
                        $url = $url . '/';
                    }

                    //fix for build links in HTTPS AJAX
                    if( defined('DOING_AJAX') && DOING_AJAX && is_ssl() ) {
                        $url = str_replace( 'http://', 'https://', $url );
                    }

                    return $url;
                }
            } else if( $page == 'hub_page_id' ) {
                return WPC()->get_hub_link();
            }

        }

        return '';
    }

    /**
     * Function for getting post capabilities using capability type
     *
     *
     * @param string $capability_type
     * @return array|bool
     */
    function get_post_type_caps_map( $capability_type ) {

        if ( empty( $capability_type ) )
            return false;

        return array(
            'edit_post'                 => "edit_{$capability_type}",
            'read_post'                 => "read_{$capability_type}",
            'delete_post'               => "delete_{$capability_type}",

            'edit_posts'                => "edit_{$capability_type}s",
            'edit_others_posts'         => "edit_others_{$capability_type}s",
            'publish_posts'             => "publish_{$capability_type}s",
            'read_private_posts'        => "read_private_{$capability_type}s",

            'delete_posts'              => "delete_{$capability_type}s",
            'delete_private_posts'      => "delete_private_{$capability_type}s",
            'delete_published_posts'    => "delete_published_{$capability_type}s",
            'delete_others_posts'       => "delete_others_{$capability_type}s",
            'edit_private_posts'        => "edit_private_{$capability_type}s",
            'edit_published_posts'      => "edit_published_{$capability_type}s",
            'create_posts'              => "edit_{$capability_type}s",
        );
    }

    /**
     * Reset our rewrite rules
     *
     * @return void;
     */
    function reset_rewrite_rules() {
        update_option( 'wpc_flush_rewrite_rules', 1 );
    }

    /**
     * Reset Rewrite rules if need it.
     *
     * @return void
     */
    function maybe_flush_rewrite_rules() {
        if ( get_option( 'wpc_flush_rewrite_rules' ) ) {
            flush_rewrite_rules( false );
            delete_option( 'wpc_flush_rewrite_rules' );
        }
    }

    /**
     * Get plugin capabilities maps
     *
     * @return array
     *
     */
    function get_capabilities_maps() {

        $capabilities_maps = include( WPC()->plugin_dir . 'includes/data/data-capabilities-maps.php' );

        $capabilities_maps = apply_filters( 'wp_client_capabilities_maps', $capabilities_maps );

        return $capabilities_maps;
    }


    /**
     * Get upload dir of plugin
     *
     * @param string $dir
     * @param string $dir_access
     *
     * @return string
     */
    function get_upload_dir( $dir = '', $dir_access = '' ) {

        if( empty( $this->upload_dir ) ) {
            $wpc_general = WPC()->get_settings( 'general' );

            if( isset( $wpc_general['resources_folder'] ) && !empty( $wpc_general['resources_folder'] ) ) {
                $this->upload_dir = $wpc_general['resources_folder'];
                if( substr( $this->upload_dir, -1 ) != DIRECTORY_SEPARATOR ) {
                    $this->upload_dir .= DIRECTORY_SEPARATOR;
                }
            } else {
                $uploads            = wp_upload_dir();
                $this->upload_dir   = str_replace( '/', DIRECTORY_SEPARATOR, $uploads['basedir'] . DIRECTORY_SEPARATOR );
            }
        }

        $dir = str_replace( '/', DIRECTORY_SEPARATOR, $dir );

        //check and create folder
        if ( !empty( $dir ) ) {
            $folders = explode( DIRECTORY_SEPARATOR, $dir );
            $cur_folder = '';
            foreach( $folders as $folder ) {
                $prev_dir = $cur_folder;
                $cur_folder .= $folder . DIRECTORY_SEPARATOR;
                if ( !is_dir( $this->upload_dir . $cur_folder ) && wp_is_writable( $this->upload_dir . $prev_dir ) ) {
                    mkdir( $this->upload_dir . $cur_folder, 0777 );
                    if( 'wpclient' == $folder ) {
                        $htp = fopen( $this->upload_dir . $cur_folder . DIRECTORY_SEPARATOR . '.htaccess', 'w' );
                        fputs( $htp, 'deny from all' ); // $file being the .htpasswd file
                    } elseif( $dir_access == 'deny' ) {
                        $htp = fopen( $this->upload_dir . $cur_folder . DIRECTORY_SEPARATOR . '.htaccess', 'w' );
                        fputs( $htp, 'deny from all' ); // $file being the .htpasswd file
                    } elseif( $dir_access == 'allow' ) {
                        $htp = fopen( $this->upload_dir . $cur_folder . DIRECTORY_SEPARATOR . '.htaccess', 'w' );
                        fputs( $htp, 'allow from all' ); // $file being the .htpasswd file
                    }
                }
            }
        }

        //return dir path
        return $this->upload_dir . $dir;
    }

    /*
    *  Function for get uploads url
    *
    */
    function get_upload_url( $url = '' ) {
        if( empty( $this->upload_url ) ) {
            $wpc_general = WPC()->get_settings( 'general' );

            if( isset( $wpc_general['resources_folder'] ) && !empty( $wpc_general['resources_folder'] ) ) {
                $this->upload_url = str_replace( '\\', '/', $wpc_general['resources_folder'] );
                if( substr( $this->upload_url, -1 ) != '/' ) {
                    $this->upload_url .= '/';
                }
            } else {
                $uploads            = wp_upload_dir();
                $this->upload_url   = $uploads['baseurl'] . '/';
            }
        }



        $url = str_replace( '\\', '/', $url );

        //return dir path
        return $this->upload_url . $url;

    }

    /*
    * get remove content
    *
    * @param string $url
    *
    * @return string|bool
    */
    function remote_download( $url ) {

        $response = wp_remote_get( $url,
            array(
                'method'        => 'GET',
                'timeout'       => 45,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,
                'sslverify'     => false,
                'headers'       => array(),
                'cookies'       => array()
            )
        );

        if ( is_wp_error( $response ) ) {
            return 'Error #30303: ' . $response->get_error_message();
        }

        if ( isset( $response['body'] ) ) {
            return $response['body'];
        }

        return false;
    }



    /*
     * Get Hub link
     *
     * @param bool $locale
     *
     * @return string
     */
    function get_hub_link( $locale = false ) {
        $pages = WPC()->get_settings( 'pages' );
        $slug = isset( $pages['portal_hub_slug'] ) ? $pages['portal_hub_slug'] : 'portal/portal-hub';

        $home_url = apply_filters( 'wpc_portalhub_locale_home_url', get_home_url(), $locale );

        if ( WPC()->permalinks ) {
            return WPC()->make_url( $slug, $home_url );
        } else {
            return add_query_arg( array( 'wpc_page' => 'portalhub' ), $home_url );
        }
    }

    /**
     * Set Data for shortcode
     *
     * @return void
     */
    function set_shortcode_data() {
        if ( empty( $this->shortcode_data ) )
            $this->shortcode_data = include( WPC()->plugin_dir . 'includes/data/data-shortcodes.php' );
    }

    /*
     * redirect
     *
     * @param string $url
     */
    function redirect( $url ) {
        if ( headers_sent() || empty( $url ) ) {
            $this->js_redirect( $url );
        } else {
            nocache_headers();
            wp_redirect( $url );
        }
        exit;
    }


    /*
     * JS redirect
     *
     * @param string $url
     */
    function js_redirect( $url ) {

        //for blank redirects
        if ( '' == $url ) {
            $url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        }

		    register_shutdown_function( function( $url ) {
			    echo "<script data-cfasync=\"false\" type=\"text/javascript\">window.location = '" . $url . "'</script>";
		    }, $url);

        if ( 1 < ob_get_level() ) {
            while ( ob_get_level() > 1 ) {
                ob_end_clean();
            }
        }

        ?>
        <script data-cfasync='false' type="text/javascript">
            window.location = '<?php echo $url; ?>';
        </script>
        <?php
        exit;
    }


    /**
     * Get date/time with timezone.
     *
     * @param int $timestamp
     * @param string $datetime_type
     * @param string $format
     * @return string
     */
    function date_format( $timestamp, $datetime_type = 'date_time', $format = '', $local_time = true ) {
        if ( empty( $timestamp ) ) return '';

        if ( empty( $format ) ) {
            $datetime_type = ( 'date' == $datetime_type || 'time' == $datetime_type ) ? $datetime_type : 'date_time';

            $format = '';

            if ( 'date' == $datetime_type || 'date_time' == $datetime_type ) {
                //Set date format
                if( get_option( 'date_format' ) ) {
                    $format .= get_option( 'date_format' );
                } else {
                    $format .= 'm/d/Y';
                }
            }

            if ( 'date_time' == $datetime_type )
                $format .= ' ';

            if ( 'time' == $datetime_type || 'date_time' == $datetime_type ) {
                //Set time format
                if( get_option( 'time_format' ) ) {
                    $format .= get_option( 'time_format' );
                } else {
                    $format .= 'g:i:s A';
                }
            }
        }

        if( $local_time ) {
	        $gmt_offset = get_option( 'gmt_offset' );
	        if ( false === $gmt_offset ) {
		        //$timestamp = $timestamp;
		        $timestamp = $timestamp - ( time() - current_time( 'timestamp' ) );
	        } else {
		        $timestamp = $timestamp + $gmt_offset * 3600;
	        }
        }
        return date_i18n( $format, $timestamp );
    }

    /**
     * encode ajax data
     *
     * @param $data
     * @return string
     */
    function encode_ajax_data( $data ) {
        return addslashes( str_replace( array( '+', '/' ),array( '-', '*' ), base64_encode( json_encode( $data ) ) ) );
    }

    /**
     * decode ajax data
     *
     * @param $data
     * @return array|mixed|object
     */
    function decode_ajax_data( $data ) {

        $decoded_data = json_decode( base64_decode( str_replace( array( '-', '*' ), array( '+', '/' ), stripslashes( $data ) ) ), true );

        return $decoded_data;
    }


    /**
     * get ajax url
     *
     * @return string
     */
    function get_ajax_url() {
        $ajax_url = admin_url( 'admin-ajax.php' );
        if( !is_admin() ) {
            $ajax_url = set_url_scheme( $ajax_url, 'admin' );
        }
        return $ajax_url;
    }


    /**
     * get current url
     *
     * @param array $args
     * @return string
     */
    function get_current_url( $args = array() ) {
        $current_url = ( is_ssl() ? "https://" : "http://" ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if( is_array( $args ) && count( $args ) ) {
            $current_url = add_query_arg( $args, $current_url );
        } else if( is_string( $args ) && strlen( $args ) ) {
            $array = explode('=', $args);
            if( count( $array ) == 2 ) {
                $current_url = add_query_arg( $array[0], $array[1], $current_url );
            }
        }
        return $current_url;
    }


    /**
     * Send mail with template
     */
    function mail( $key, $to, $args = array(), $placeholders_label = '', $attachments = array() ) {
        if( isset( $args['client_id'] ) && 0 < $args['client_id'] ) {
            $excluded_clients  = WPC()->members()->get_excluded_clients( 'archive' );
            if( in_array( $args['client_id'], $excluded_clients ) ) {
                return false;
            }
        }

        $data_array = array(
            'to'            => $to,
            'args'          => $args,
            'attachments'   => $attachments
        );

        /*our_hook_
        hook_name: wpc_client_send_email_data
        hook_title: Change email data before send
        hook_description: Hook filtered email data before send notification.
        hook_type: filter
        hook_in: wp-client
        hook_location class.common.php
        hook_param: array $data_array, string $key, string $placeholders_label
        hook_since: 3.8.5
        */
        $data_array = apply_filters( 'wpc_client_send_email_data', $data_array, $key, $placeholders_label );

        $to = ( !empty( $data_array['to'] ) ) ? $data_array['to'] : '';
        $args = ( !empty( $data_array['args'] ) ) ? $data_array['args'] : array();
        $attachments = ( !empty( $data_array['attachments'] ) ) ? $data_array['attachments'] : array();

        do_action( 'wpc_client_send_email', $key, $to, $args, $placeholders_label, $attachments );

        $wpc_templates_emails = WPC()->get_settings( 'templates_emails' );

        //no template
        if ( !isset( $wpc_templates_emails[$key] ) )
            return false;

        /*our_hook_
            hook_name: wpc_client_cc_mail_template
            hook_title: Email template filter for cc_mail method
            hook_description: Email template filter for cc_mail method. You can use it to edit\remove email template.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: array $placeholders, array $args, string $label
            hook_since: 4.0.1
        */
        $wpc_templates_emails[$key] = apply_filters( "wpc_client_cc_mail_template", $wpc_templates_emails[$key], $args, $placeholders_label );

        //notification is disabled
        if ( isset( $wpc_templates_emails[$key]['enable'] ) && 0 == $wpc_templates_emails[$key]['enable'] )
            return false;

        if ( !is_email( $to ) )
            return false;


        $subject = WPC()->replace_placeholders( $wpc_templates_emails[$key]['subject'], $args, $placeholders_label );
        $subject = str_replace( "_", '-', $subject );

        $message = WPC()->replace_placeholders( $wpc_templates_emails[$key]['body'], $args, $placeholders_label );

        $result = $this->mailer()->send( $to, $subject, $message, '', $attachments) ;

        /*our_hook_
            hook_name: wp_client_after_sent_mail
            hook_title: After Mail Sent
            hook_description: Hook runs after mail has been sent
            hook_type: action
            hook_in: wp-client
            hook_location class.ajax.php
            hook_param: boolean $result, array $args, string $placeholders_label, array|string $attachments
            hook_since: 4.5.7
        */
        do_action( 'wp_client_after_sent_mail', $result, $args, $placeholders_label, $attachments );

        return $result;

    }


    /**
     * Send mail regular email via our sending profiles
     */
    function wpc_mail( $to, $subject, $message, $headers = '', $attachments = '' ) {

        if ( !is_email( $to ) )
            return false;

        if ( empty( $subject ) )
            return false;


        return $this->mailer()->send( $to, $subject, $message, $headers, $attachments );

    }

    /**
     * make clickable links in content
     */
    function make_clickable( $text, $args = array() ) {
        $text = make_clickable( $text );
        if( isset( $args['target'] ) && '_blank' == $args['target'] ) {
            $text = preg_replace( '/<a /', '<a target="_blank" ', $text );
        }
        return $text;
    }

    /*
    * Set cookie with JS if headers already sent
    */
    function setcookie( $name, $value, $expire = 0, $secure = false ) {
        $secure = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
        if ( ! headers_sent() ) {
            nocache_headers();
            setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
            if ( SITECOOKIEPATH != COOKIEPATH )
                setcookie( $name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
        } else {
            $expire_js = 0;
            if ( $expire ) {
                $expire_js = $expire - time();
            }

            $secure_js = 'false';
            if ( $secure ) {
                $secure_js = 'true';
            }

            ?>
            <script src="<?php echo WPC()->plugin_url ?>/js/cookies.min.js"></script>

            <script type="text/javascript">
                <?php
                    echo "Cookies.set( '{$name}', '{$value}', {
                        expires: {$expire_js},
                        secure: {$secure_js},
                        domain: '" . COOKIE_DOMAIN . "',
                        path: '" . COOKIEPATH . "'
                    }); ";
                    if ( SITECOOKIEPATH != COOKIEPATH )
                        echo "Cookies.set( '{$name}', '{$value}', {
                            expires: {$expire_js},
                            secure: {$secure_js},
                            domain: '" . COOKIE_DOMAIN . "',
                            path: '" . SITECOOKIEPATH . "'
                        }); ";
                ?>
            </script>
            <?php

        }

    }



    /*
    * Download file by parts
    */
    function readfile_chunked( $filename, $retbytes = true ) {
        $customRead = apply_filters( 'wp_client_file_read_chunk_custom', false, $filename );

        if ( $customRead !== false )
            return true;

        $chunksize = 1 *( 1024 * 1024 ); // how many bytes per chunk
        $cnt = 0;
        // $handle = fopen($filename, 'rb');
        $handle = fopen( $filename, 'rb' );
        if ( $handle === false ) {
            return false;
        }

        while ( !feof( $handle ) ) {
            $buffer = fread( $handle, $chunksize );

            //todo: add description (old:wp_client_download_ftp_file)
            $buffer = apply_filters( 'wp_client_file_read_chunk', $buffer, $filename );

            echo $buffer;
            if ( $retbytes ) {
                $cnt += strlen( $buffer );
            }
        }
        $status = fclose( $handle );
        if ( $retbytes && $status ) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;

    }


    /*
    *  return label for file sizes
    */
    function format_bytes( $a_bytes ) {

        if ($a_bytes < 1024) {
            return $a_bytes .' B';
        } elseif ($a_bytes < 1048576) {
            return round($a_bytes / 1024, 2) .' K';
        } elseif ($a_bytes < 1073741824) {
            return round($a_bytes / 1048576, 2) . ' MB';
        } elseif ($a_bytes < 1099511627776) {
            return round($a_bytes / 1073741824, 2) . ' GB';
        } elseif ($a_bytes < 1125899906842624) {
            return round($a_bytes / 1099511627776, 2) .' TB';
        } elseif ($a_bytes < 1152921504606846976) {
            return round($a_bytes / 1125899906842624, 2) .' PB';
        } elseif ($a_bytes < 1180591620717411303424) {
            return round($a_bytes / 1152921504606846976, 2) .' EB';
        } elseif ($a_bytes < 1208925819614629174706176) {
            return round($a_bytes / 1180591620717411303424, 2) .' ZB';
        } else {
            return round($a_bytes / 1208925819614629174706176, 2) .' YB';
        }
    }

    /**
     * Compare WPC and extension versions
     *
     * @param string $wpc_required_ver
     * @param string $ext_ver
     * @param string $ext_key
     * @param string $ext_title
     * @return bool
     */
    public function compare_versions( $prefix ) {

        $wpc_required_ver = WPC()->extensions()->get_required_version( $prefix );
        $ext_ver = WPC()->extensions()->get_defined_version( $prefix );
        $ext_title = WPC()->extensions()->get_title( $prefix );

        if ( version_compare( WPC_CLIENT_VER, $wpc_required_ver, '<' )
            || ( ! empty( WPC()->extensions()->min_vers[$prefix] ) && version_compare( WPC()->extensions()->min_vers[$prefix], $ext_ver, '>' ) ) ) {

            if ( !defined( 'DOING_AJAX' ) && is_admin() && current_user_can( 'install_plugins' ) ) {

                $message = '';
                if ( version_compare( WPC_CLIENT_VER, $wpc_required_ver, '<' ) ) {
                    $message = sprintf( __( 'Sorry, but for this version of extension "%s" is required version of the %s core not lower than %s.', WPC_CLIENT_TEXT_DOMAIN ), $ext_title, WPC()->plugin['title'], $wpc_required_ver ) .
                        '<br />' .
                        sprintf( __( 'Please update %s core to latest version or install previous versions of this extension.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'] );
                } elseif ( ! empty( WPC()->extensions()->min_vers[$prefix] ) && version_compare( WPC()->extensions()->min_vers[$prefix], $ext_ver, '>' ) ) {
                    $message = sprintf( __( 'Sorry, but this version of %s does not work with extension "%s" %s version.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->plugin['title'], $ext_title, $ext_ver ) .
                        '<br />' .
                        sprintf( __( 'Please update extension "%s" to the latest version, or install previous versions of %s.', WPC_CLIENT_TEXT_DOMAIN ), $ext_title, WPC()->plugin['title'] );
                }

                WPC()->notices()->add_all_pages_notice( $message, 'error' );
            }

            return false;
        }

        return true;
    }

    /**
     * decode password special chars
     *
     * @param $pass
     * @return string
     */
    function prepare_password( $pass ) {
        return html_entity_decode( esc_attr( trim( $pass ) ) );
    }


    function set_currency( $price, $currency, $echo = true ) {
        $wpc_currency = WPC()->get_settings( 'currency' );
        $result = '';
        if( isset( $wpc_currency[ $currency ] ) && is_array( $wpc_currency[ $currency ] ) ) {
            if( isset( $wpc_currency[ $currency ]['align'] ) && 'right' == $wpc_currency[ $currency ]['align'] ) {
                $result = $price . ( isset( $wpc_currency[ $currency ]['symbol'] ) ? $wpc_currency[ $currency ]['symbol'] : '' );
            } else {
                $result = ( isset( $wpc_currency[ $currency ]['symbol'] ) ? $wpc_currency[ $currency ]['symbol'] : '' ) . $price;
            }
        }

        if( $echo ) {
            echo $result;
        } else {
            return $result;
        }

        return '';
    }


    function get_default_currency() {

        $currencies = WPC()->get_settings( 'currency' );

        if( isset( $currencies ) && !empty( $currencies ) ) {
            foreach( $currencies as $key=>$currency ) {
                if( $currency['default'] == '1' ) {
                    return $key;
                }
            }
        }

        return '';
    }


    /*
    * Replace placeholders
    */
    function replace_placeholders( $content, $args = '', $label = '' ) {
        global $wpdb;
        $content = stripslashes( $content );

        $client = false;
        $staff = false;
        $client_id = '';
        if ( isset( $args['client_id'] ) && 0 < $args['client_id'] ) {
            $client_id = (int)$args['client_id'];
        } else if ( is_user_logged_in() && current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) {
            $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
        } else if( is_user_logged_in() && current_user_can( 'wpc_client' ) && !current_user_can( 'manage_network_options' ) ) {
            $client_id = get_current_user_id();
        }

        if( (int)$client_id > 0 ) {
            $client = get_userdata( $client_id );
        }


        if ( get_current_user_id() && ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) ) {
            $staff = get_userdata( get_current_user_id() );
        }

        $user = get_userdata( get_current_user_id() );

        if ( isset( $args['client_id'] ) && 0 < $args['client_id'] && strpos( $content, '{manager_name}' ) !== false ) {
            //$user_manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client_id );
            $user_manager_ids = WPC()->members()->get_client_managers( $client_id );

            if( is_array( $user_manager_ids ) && count( $user_manager_ids ) ) {
                $managers = array();
                foreach( $user_manager_ids as $key=>$user_manager_id ) {
                    $manager = get_userdata( $user_manager_id );
                    if ( $manager ) {
                        $managers[$key] = $manager->get( 'display_name' );
                    }
                }

                $managers = ( count( $managers ) ) ? implode( ', ', $managers ) : '';
            }
        }

        $ph_data = array (
            '{site_title}'              => get_option( 'blogname' ),
            '{site_url}'                => site_url(),
            '{blog_name}'               => get_option( 'blogname' ),
            '{client_id}'               => ( $client ) ? $client->get( 'ID' ) : '',
            '{contact_name}'            => ( $client ) ? $client->get( 'display_name' ) : '',
            '{client_business_name}'    => ( $client ) ? get_user_meta( $client_id, 'wpc_cl_business_name', true ) : '',
            '{client_phone}'            => ( $client ) ? get_user_option( 'contact_phone', $client_id ) : '',
            '{client_email}'            => ( $client ) ? $client->get( 'user_email' ) : '',
            '{client_name}'             => ( $client ) ? $client->get( 'display_name' ) : '',
            '{user_name}'               => ( $client ) ? $client->get( 'user_login' ) : '',
            '{login_url}'               => ( '' != WPC()->get_slug( 'login_page_id' ) ) ? WPC()->get_slug( 'login_page_id' ) : wp_login_url(),
            '{logout_url}'              => WPC()->get_logout_url(),
            '{admin_url}'               => WPC()->get_login_url( true ),

            '{portal_page_category}'        => ( isset( $args['portal_page_category'] ) ) ? $args['portal_page_category'] : '',

            '{approve_url}'     => '',
            '{verify_url}'      => '',
            '{password}'        => '',
            '{page_title}'      => ( isset( $args['page_title'] ) ) ? $args['page_title'] : '',
            '{page_id}'         => '',
            '{admin_file_url}'  => '',
            '{message}'         => '',
            '{file_name}'       => '',
            '{file_category}'   => '',
            '{reset_address}'   => '',

            '{manager_name}'   => ( isset( $managers ) && !empty( $managers ) ) ? $managers : __( 'No manager', WPC_CLIENT_TEXT_DOMAIN ),
            '{client_registration_date}'   => ( $client ) ? $this->date_format( strtotime( $client->get( 'user_registered' ) ) ) : '',

            '{staff_display_name}'  => ( $staff ) ? $staff->get( 'display_name' ) : '',
            '{staff_first_name}'    => ( $staff ) ? get_user_meta( $staff->get( 'ID' ), 'first_name', true ) : '',
            '{staff_last_name}'     => ( $staff ) ? get_user_meta( $staff->get( 'ID' ), 'last_name', true ) : '',
            '{staff_email}'         => ( $staff ) ? $staff->get( 'user_email' ) : '',
            '{staff_login}'         => ( $staff ) ? $staff->get( 'user_login' ) : '',


            '{user_display_name}'  => ( $user ) ? $user->get( 'display_name' ) : '',
            '{user_first_name}'    => ( $user ) ? get_user_meta( $user->get( 'ID' ), 'first_name', true ) : '',
            '{user_last_name}'     => ( $user ) ? get_user_meta( $user->get( 'ID' ), 'last_name', true ) : '',
            '{user_email}'         => ( $user ) ? $user->get( 'user_email' ) : '',
            '{user_login}'         => ( $user ) ? $user->get( 'user_login' ) : '',



        );

        $wpc_business_info = WPC()->get_settings( 'business_info' );
        $fields = WPC()->get_business_info_fields();
        foreach ( $fields as $key => $name ) {
            $ph_data["{{$key}}"] = isset( $wpc_business_info[$key] ) ? $wpc_business_info[$key] : '';
        }

        if ( '' != $label ) {
            switch( $label ) {
                case 'profile_updated':
                    $ph_data['{contact_name}'] = ( isset( $args['contact_name'] ) ) ? $args['contact_name'] : '';
                    break;
                case 'notify_client_about_message':
                case 'notify_cc_about_message':
                case 'notify_admin_about_message':
                    $ph_data['{user_name}'] = ( isset( $args['user_name'] ) ) ? $args['user_name'] : '';
                    $ph_data['{message}'] = ( isset( $args['message'] ) ) ? $args['message'] : '';
                    $ph_data['{subject}'] = ( isset( $args['subject'] ) ) ? $args['subject'] : '';
                    break;

                case 'to_approve':
                case 'staff_created_admin_notify':
                    $ph_data['{approve_url}'] = get_admin_url() . 'admin.php?page=wpclient_clients&tab=approve';
                    break;

                case 'new_client':
                case 'client_updated':
                case 'manager_created':
                case 'manager_updated':
                case 'admin_created':
                case 'staff_created':
                case 'staff_registered':
                case 'convert_to_wp_user':
                    $ph_data['{user_name}']     = ( $client ) ? $client->get( 'user_login' ) : '';
                    $ph_data['{password}']      = ( isset( $args['user_password'] ) ) ? $args['user_password'] : '';
                    $ph_data['{user_password}'] = ( isset( $args['user_password'] ) ) ? $args['user_password'] : '';
                    $ph_data['{page_id}']       = ( isset( $args['page_id'] ) ) ? $args['page_id'] : '';
                    $ph_data['{verify_url}']    = ( isset( $args['verify_url'] ) ) ? $args['verify_url'] : '';

                    $ph_data['{user_role}'] = '';
                    $user_roles = get_user_meta( $client_id, $wpdb->prefix . 'capabilities', true ) ;
                    foreach( $user_roles as $key => $role ) {
                        if( $role && 'wpc_' == substr( $key, 0, 4 ) ) {
                            $role_data = get_role( $key );
                            if( !empty( $role_data->name ) ) {
                                $ph_data['{user_role}'] = $role_data->name;
                                break;
                            }
                        }
                    }

                    break;

                case 'portal_page_updated':
                case 'private_post_type':
                    $ph_data['{page_id}']       = ( isset( $args['page_id'] ) ) ? $args['page_id'] : '';
                    break;

                case 'new_file_for_client_staff':
                case 'client_uploaded_file':
                    $ph_data['{admin_file_url}'] = get_admin_url() . "admin.php?page=wpclients_content&tab=files&filter=" . $client_id;
                    $ph_data['{file_name}']     = ( isset( $args['file_name'] ) ) ? $args['file_name'] : '';
                    $ph_data['{file_category}'] = ( isset( $args['file_category'] ) ) ? $args['file_category'] : '';
                    $ph_data['{file_download_link}']  = ( isset( $args['file_download_link'] ) ) ? $args['file_download_link'] : '';
                    break;

                case 'client_downloaded_file':
                    $ph_data['{file_name}']         = ( isset( $args['file_name'] ) ) ? $args['file_name'] : '';
                    break;

                case 'wizard_notify':
                    $ph_data['{wizard_name}']   = ( isset( $args['wizard_name'] ) ) ? $args['wizard_name'] : '';
                    $ph_data['{wizard_url}']    = ( isset( $args['wizard_url'] ) ) ? $args['wizard_url'] : '';
                    break;

                case 'reset_password':
                    $ph_data['{reset_address}'] = ( isset( $args['reset_address'] ) ) ? $args['reset_address'] : '';
                    break;
                case 'la_login_successful':
                    $ph_data['{current_time}'] = ( isset( $args['current_time'] ) ) ? $args['current_time'] : '';
                    $ph_data['{ip_address}'] = ( isset( $args['ip_address'] ) ) ? $args['ip_address'] : '';
                    break;
                case 'la_login_failed':
                    $ph_data['{current_time}'] = ( isset( $args['current_time'] ) ) ? $args['current_time'] : '';
                    $ph_data['{ip_address}'] = ( isset( $args['ip_address'] ) ) ? $args['ip_address'] : '';
                    $ph_data['{la_status}'] = ( isset( $args['la_status'] ) ) ? $args['la_status'] : '';
                    $ph_data['{la_user_name}'] = ( isset( $args['la_user_name'] ) ) ? $args['la_user_name'] : '';
                    break;
            }
        }



        /*
        * Get Custom Fields values for placeholders
        */
        if ( '' != $client_id ) {
            $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

            if ( isset( $wpc_custom_fields ) && !empty( $wpc_custom_fields ) && is_array( $wpc_custom_fields ) ) {
                foreach( $wpc_custom_fields as $key => $value ) {
                    $ph_key = '{' . $key . '}';

                    if( $value['nature'] == 'staff' ) {
                        $id = $staff ? $staff->get( 'ID' ) : 0;
                        $cf_value = $staff ? get_user_meta( $staff->get( 'ID' ), $key, true ) : '';
                    } else {
                        $id = $client_id;
                        $cf_value = get_user_meta( $client_id, $key, true );
                    }
                    $metadata_exists = metadata_exists('user', $id, $key );

                    $value['name'] = $key;
                    $ph_data[$ph_key] = WPC()->custom_fields()->render_custom_field_value( $value, array(
                        'user_id' => $client_id,
                        'value' => $cf_value,
                        'metadata_exists' => $metadata_exists,
                        'empty_value' => ''
                    ));
                }
            }
        }


        foreach ( $ph_data as $key => $value ) {
            if  ( is_string( $value ) ) {
                //replace {} into html
                $ph_data[$key] = str_replace( array( '{', '}' ), array( '&#123;', '&#125;' ), $value );
            }
        }


        /*our_hook_
            hook_name: wpc_client_replace_placeholders
            hook_title: Replace Placeholders
            hook_description: Hook runs before placeholders are replaced. You can use it to add\edit\remove placeholders and their values.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: array $placeholders, array $args, string $label
            hook_since: 3.3.5
        */
        $ph_data = apply_filters( "wpc_client_replace_placeholders", $ph_data, $args, $label );
        /*our_hook_
            hook_name: wpc_client_replace_placeholders_content
            hook_title: Replace content in method cc_replace_placeholders()
            hook_description: Hook runs before placeholders are replaced. You can use it to edit\remove content.
            hook_type: filter
            hook_in: wp-client
            hook_location class.common.php
            hook_param: array $placeholders, array $args, string $label
            hook_since: 4.0.1
        */
        $content = apply_filters( "wpc_client_replace_placeholders_content", $content, $args, $label );
        $content = str_replace( array_keys( $ph_data ), array_values( $ph_data ), $content );

        return $content;
    }


    public function get_placeholder_value( $placeholder, $args = '', $label = '' ) {
        return WPC()->replace_placeholders('{' . $placeholder . '}', $args, $label);
    }


    /**
     * Get all fields of business info
     *
     * @return array
     */
    function get_business_info_fields() {
        $fields = array(
            'business_logo_url'         => __( 'Logo URL', WPC_CLIENT_TEXT_DOMAIN ),
            'business_name'             => __( 'Official Business Name', WPC_CLIENT_TEXT_DOMAIN ),
            'business_address'          => __( 'Business Address', WPC_CLIENT_TEXT_DOMAIN ),
            'business_mailing_address'  => __( 'Mailing Address', WPC_CLIENT_TEXT_DOMAIN ),
            'business_website'          => __( 'Website', WPC_CLIENT_TEXT_DOMAIN ),
            'business_email'            => __( 'Email', WPC_CLIENT_TEXT_DOMAIN ),
            'business_phone'            => __( 'Phone', WPC_CLIENT_TEXT_DOMAIN ),
            'business_fax'              => __( 'Fax', WPC_CLIENT_TEXT_DOMAIN ),
        );

        return $fields;
    }


    /**
     * Checking access for page
     *
     * @param  array( 'check_email' => true, 'check_approve' => true, 'check_need_pay' => true )
     * @return int $client_id - client ID
     */
    function checking_page_access( $access = array() ) {
        $access = array_merge( array( 'check_email' => true, 'check_approve' => true, 'check_need_pay' => true ), $access );
        //block not logged clients
        if ( !is_user_logged_in() )  {
            //Sorry, you do not have permission to see this page
            WPC()->redirect( WPC()->get_login_url() );
        }

        if ( isset( WPC()->current_plugin_page['client_id'] ) && 0 < WPC()->current_plugin_page['client_id'] )
            $client_id = WPC()->current_plugin_page['client_id'];
        else
            $client_id = get_current_user_id();

        //block not verify email
        if ( $access['check_email'] ) {
            $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
            if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] && get_user_meta( $client_id, 'verify_email_key', true ) ) {
                WPC()->redirect( add_query_arg( array( 'type' => 'verify_email' ), WPC()->get_slug( 'error_page_id' ) ) );
            }
        }

        //block not approved clients
        if ( $access['check_approve'] && '1' == get_user_meta( $client_id, 'to_approve', true ) ) {
            WPC()->redirect( add_query_arg( array( 'type' => 'approval' ), WPC()->get_slug( 'error_page_id' ) ) );
        }

        //block not paid clients
        if ( $access['check_need_pay'] && '1' == get_user_meta( $client_id, 'wpc_need_pay', true ) ) {
            $wpc_ams_level = get_user_meta( $client_id, 'wpc_ams_level', true );

            if ( $wpc_ams_level && isset( $wpc_ams_level['redirect'] ) && '' != $wpc_ams_level['redirect'] ) {

                WPC()->redirect( $wpc_ams_level['redirect'] );
            } else {

                /*our_hook_
                    hook_name: wpc_client_need_pay_for_access
                    hook_title: Need Pay For Get Access
                    hook_description: Can be used for checking access to portals, and redirecting to payment page if np access.
                    hook_type: action
                    hook_in: wp-client
                    hook_location class.common.php
                    hook_param: int $client_id
                    hook_since: 3.5.4
                */

                do_action( 'wpc_client_need_pay_for_access', $client_id );
            }
        }

        return $client_id;
    }

    /**
     * Return html with red star for requred fields
     *
     * @return string
     */
    function red_star() {
        return '<font color="red">*</font>';
    }


    /**
     * Get all email profiles
     */
    function get_all_email_profiles() {
        $all_email_profiles = WPC()->get_settings( 'email_sending_profiles' );

        $types = array(
            '' => __( 'Default WP', WPC_CLIENT_TEXT_DOMAIN ),
            'smtp' => __( 'SMTP', WPC_CLIENT_TEXT_DOMAIN ),
        );

        $this->mailer()->load_email_senders();
        foreach ( (array)$this->mailer()->senders as $code => $plugin ) {
            $types[ $code ] = !empty( $plugin[1] ) ? $plugin[1] : $code;
        }

        foreach ( $all_email_profiles as $key => $profile ) {
            $all_email_profiles[ $key ]['type_name'] = ( isset( $profile['type'] )
                && isset( $types[ $profile['type'] ] ) )
                ? $types[ $profile['type'] ] : $types[''];
        }

        return $all_email_profiles;
    }


    /**
     * make price with currency
     *
     * @param $price
     * @param $currency_id
     * @param string $currency_code
     * @return string
     */
    function get_price_string( $price, $currency_id, $currency_code = '' ) {

        $price_string = $price;
        $currencies = WPC()->get_settings( 'currency' );


        if ( !empty( $currency_code )) {

            if ( !empty( $currencies ) ) {
                $price_string = $price . ' ' . $currency_code;
                foreach( $currencies as $currency ) {
                    if ( isset( $currency['code'] ) && $currency['code'] == $currency_code  ) {
                        if (  isset( $currency['symbol'] ) && !empty( $currency['symbol'] ) && isset( $currency['align'] ) && !empty( $currency['align'] ) ) {
                            switch( $currency['align'] ) {
                                case 'left':
                                    $price_string = $currency['symbol'] . $price;
                                    break;
                                case 'right':
                                    $price_string = $price . $currency['symbol'];
                                    break;
                                default:
                                    $price_string = $currency['symbol'] . $price;
                                    break;
                            }

                            break;
                        }
                    }
                }
            }
        } else {
            if ( isset( $currencies[$currency_id] ) && !empty( $currencies[$currency_id] ) ) {
                $current_currency = $currencies[$currency_id];
                if ( isset( $current_currency['symbol'] ) && !empty( $current_currency['symbol'] ) && isset( $current_currency['align'] ) && !empty( $current_currency['align'] ) ) {

                    switch( $current_currency['align'] ) {
                        case 'left':
                            $price_string = $current_currency['symbol'] . $price;
                            break;
                        case 'right':
                            $price_string = $price . $current_currency['symbol'];
                            break;
                        default:
                            $price_string = $current_currency['symbol'] . $price;
                            break;
                    }
                }
            }
        }

        return $price_string;
    }


    function is_wp_login() {

        if ( !isset( WPC()->flags['is_wp_login'] ) ) {

            // The blog's URL
            $blog_url = trailingslashit( get_bloginfo( 'url' ) );
            $blog_url = str_replace( 'https://', '', $blog_url );
            $blog_url = str_replace( 'http://', '', $blog_url );

            // The Current URL
            $current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $request_url = str_replace( $blog_url, '', $current_url );
            $request_url = str_replace( 'index.php/', '', $request_url );

            $url_parts = explode( '?', $request_url, 2 );
            $base = $url_parts[0];

            // Remove trailing slash
            $base = rtrim( $base, "/" );
            $exp = explode( '/', $base, 2 );
            $super_base = end( $exp );


            if ( $super_base == 'wp-login.php' ) {
                WPC()->flags['is_wp_login'] = true;
            } else {
                WPC()->flags['is_wp_login'] = false;
            }

        }

        return WPC()->flags['is_wp_login'];
    }


    /**
     * Get login URL
     *
     * @param bool $from_placeholders
     * @return mixed|string|void
     */
    function get_login_url( $from_placeholders = false ) {
        global $wp_query, $pagenow, $post;

        $login_url = ( '' != WPC()->get_slug( 'login_page_id' ) ) ? WPC()->get_slug( 'login_page_id' ) : wp_login_url();
        $wpc_pages = WPC()->get_settings( 'pages' );

        $is_login_url = false;
        if ( $pagenow === 'wp-login.php' ||
            ( isset( $post->ID ) && isset( $wpc_pages['login_page_id'] ) && $post->ID == $wpc_pages['login_page_id'] ) ||
            isset( $_GET['logout'] ) )
            $is_login_url = true;

        if ( $is_login_url !== false || $from_placeholders ) {
            return $login_url;
        } else if ( isset( $wp_query->query_vars['wpc_page'] ) && 'acc_activation' == $wp_query->query_vars['wpc_page'] ) {
            return add_query_arg( array( 'msg' => 've' ), $login_url  );
        } else {
            $wpc_enable_custom_redirects = WPC()->get_settings( 'enable_custom_redirects', 'no' );
            $default_non_login_redirects = WPC()->get_settings( 'default_non_login_redirects' ) ;

            if ( 'yes' == $wpc_enable_custom_redirects && ! empty( $default_non_login_redirects['url'] ) ) {
                return $default_non_login_redirects['url'];
            } else {
                return add_query_arg( array( 'wpc_to_redirect' => urlencode( $this->get_current_url() ) ), $login_url ) ;
            }
        }
    }


    /**
     * Get logout URL
     *
     * @return string
     */
    function get_logout_url() {
        $logout_url = ( '' != WPC()->get_slug( 'login_page_id' ) ) ? add_query_arg( array( 'logout' => 'true' ), WPC()->get_slug( 'login_page_id' ) ) : wp_logout_url();
        return $logout_url;
    }


    /**
     * Get other templates (e.g. files table) passing attributes and including the file.
     *
     * @access public
     * @param string $template_name
     * @param string $path (default: '')
     * @param array $t_args (default: array())
     * @param bool $echo
     *
     * @return string|void
     */
    function get_template( $template_name, $path = '', $t_args = array(), $echo = false ) {
        return WPC()->templates()->get_template( $template_name, $path, $t_args, $echo );
    }


    function throw_404() {

        //trying get 404 content for random page
        $html = wp_remote_get( get_home_url() . '/' . md5( time() ) . '/', array(
            'method'        => 'GET',
            'timeout'       => 45,
            'redirection'   => 5,
            'httpversion'   => '1.0',
            'blocking'      => true,
            'sslverify'     => false,
        ) );


        if ( is_array( $html ) && isset( $html['body'] ) ) {
            status_header( 404 );

            //return 404 content
            echo $html['body'];
            exit;
        }


        // Change WP Query
        global $wp_query;

        $wp_query->set_404();
        status_header( 404 );

        // Disable that pesky Admin Bar
        add_filter( 'show_admin_bar', '__return_false', 900 );
        remove_action( 'admin_footer', 'wp_admin_bar_render', 10 );
        remove_action( 'wp_head', 'wp_admin_bar_header', 10 );
        remove_action( 'wp_head', '_admin_bar_bump_cb', 10 );
        wp_dequeue_script( 'admin-bar' );
        wp_dequeue_style( 'admin-bar' );

        // Template
        $four_tpl = apply_filters( 'LD_404', get_404_template() );

        // Handle the admin bar
        @define('APP_REQUEST', TRUE);
        @define('DOING_AJAX', TRUE);

        if ( empty($four_tpl) OR ! file_exists($four_tpl) ) {
            // We're gonna try and get TwentyTen's one
            $twenty_ten_tpl = apply_filters( 'LD_404_FALLBACK', WP_CONTENT_DIR . '/themes/twentythirteen/404.php');

            if ( file_exists( $twenty_ten_tpl ) )
                require( $twenty_ten_tpl );
            else
                wp_die( '404 - File not found!', '', array( 'response' => 404 ) );
        } else {
            // Their theme has a template!
            require( $four_tpl );
        }
        // Either way, it's gonna stop right here.
        exit;
    }


    function get_default_titles() {
        $default_titles = include( WPC()->plugin_dir . 'includes/data/data-default-titles.php' );

        return $default_titles;
    }

    /**
     * Get plugin/extension dir
     *
     * @param string $file (__FILE__)
     *
     * @return string
     */
    function gen_plugin_dir( $file ) {

        return dirname( $file ) . '/';
    }

    /**
     * Get plugin/extension URL
     *
     * @param string $file __FILE__
     *
     * @return string
     */
    function gen_plugin_url( $file ) {

        return str_replace( array( 'http:', 'https:' ), '', plugins_url( '', $file ) ) . '/';
    }


    function is_licensed( $prefix ) {
        if ( 'd043e82432b881f68c5390d4171f9f7e' == md5( md5( get_option( $prefix . '_license_status' ) ) ) ) {
            return true;
        }

        return false;
    }

    /*
    * set global vars for client
    */
    function set_global_vars() {
        //block not logged clients
        if ( !is_user_logged_in() )  {
            return '';
        }


        //for staff
        if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'administrator' ) ) {
            $client_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );

            $to_approve = get_user_meta( get_current_user_id(), 'to_approve', true );

            if ( user_can( $client_id, 'wpc_client' ) ) {
                $wpc_cl_hubpage_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

                WPC()->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;

                if( isset( $to_approve ) && '1' == $to_approve ) {
                    WPC()->current_plugin_page['client_id'] = get_current_user_id();
                } else {
                    WPC()->current_plugin_page['client_id'] = $client_id;
                }
                return '';
            }

        }
        //for client
        elseif ( current_user_can( 'wpc_client' ) && !current_user_can( 'administrator' ) ) {
            $client_id = get_current_user_id();
            $wpc_cl_hubpage_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

            WPC()->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;
            WPC()->current_plugin_page['client_id'] = $client_id;
            return '';

        }
        //for admins and managers
        elseif ( current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_manager' ) ) {
            if ( isset( $_COOKIE['wpc_preview_client'] ) && 0 < $_COOKIE['wpc_preview_client'] ) {
                $client_id = $_COOKIE['wpc_preview_client'];
                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $client_ids = WPC()->members()->get_all_clients_manager();
                    if( !in_array( $client_id, $client_ids ) ) {
                        return '';
                    }
                }

                $wpc_cl_hubpage_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

                WPC()->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;
                WPC()->current_plugin_page['client_id'] = $client_id;
                return '';
            } else if(  isset( $_GET['hub_preview'] ) && $this->is_hub_preview() ) {

                global $wp_query;
                $client_id = (int)$_GET['hub_preview'];

                if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) ) {
                    $client_ids = WPC()->members()->get_all_clients_manager();
                    if( !in_array( $client_id, $client_ids ) ) {
                        return '';
                    }
                }

                $wpc_cl_hubpage_id = WPC()->pages()->get_portalhub_for_client( $client_id )->ID;

                WPC()->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;
                WPC()->current_plugin_page['client_id'] = $client_id;
                return '';
            }
        }
        return '';
    }

    /**
     * Checking that plugin is activated and for network too
     *
     * @param string $plugin
     *
     * @return bool
     */
    function is_plugin_active( $plugin ) {
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || $this->is_plugin_active_for_network( $plugin );
    }

    /**
     * Checking that plugin is activated for network
     *
     * @param string $plugin
     *
     * @return bool
     */
    function is_plugin_active_for_network( $plugin ) {
        if ( !is_multisite() )
            return false;

        $plugins = get_site_option( 'active_sitewide_plugins');
        if ( isset($plugins[$plugin]) )
            return true;

        return false;
    }

    /**
     * Checking that current page is HUB preview
     *
     *
     * @return bool
     */
    function is_hub_preview() {

        $hub_preview = false;

        if ( !empty( $_GET['wpc_hub_preview_id'] ) && !empty( $_GET['wpc_hub_preview_key'] ) && wp_verify_nonce( $_GET['wpc_hub_preview_key'], 'wpc_hub_preview_' . $_GET['wpc_hub_preview_id'] ) ) {
            $hub_preview = true;
        }

        if ( isset( $_GET['hub_preview'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'customizer' . $_GET['hub_preview'] ) ) {
            $hub_preview = true;
        }

        /*our_hook_
            hook_name: wp_client_is_hub_preview
            hook_title: Builders Compatibility - is hub preview
            hook_description: Hook runs before redirect condition.
            hook_type: filter
            hook_in: wp-client
            hook_location class-functions.php
            hook_param: boolean $hub_preview
            hook_since: 4.5.9.8
        */
        $hub_preview = apply_filters( 'wp_client_is_hub_preview', $hub_preview );

        return $hub_preview;
    }

    /**
     * Create archive from dir
     *
     * @param string $file_dir
     * @param string $zip_name
     * @param string $zip_path
     * @param string $folder_zip_name
     *
     * @return bool
     */
    function create_archive( $file_dir, $zip_name, $zip_path, $folder_zip_name='' ) {

        if ( class_exists( 'ZipArchive', false ) ) {
            $folder_zip_name = ( ! empty( $folder_zip_name ) ) ? rtrim($folder_zip_name,'/').'/': '';
            $zip = new ZipArchive();
            if ( $zip->open( $zip_path . $zip_name, ZIPARCHIVE::CREATE ) !== true ) {
                return false;
            }

            if ( file_exists( $file_dir ) ) {
                foreach ( glob( $file_dir . '*' ) as $file ) {
                    $zip->addFile( realpath( $file ), $folder_zip_name . basename( $file ) );
                }
            }

            $zip->close();

            return true;
        } elseif ( file_exists( ABSPATH . 'wp-admin/includes/class-pclzip.php' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

            $archive = new PclZip( $zip_path . $zip_name );
            $result  = $archive->create( realpath( $file_dir ), PCLZIP_OPT_REMOVE_PATH, $file_dir,
                PCLZIP_OPT_ADD_PATH, $folder_zip_name );
            if ( ! $result ) {
                return false;
            }

            return true;
        } else {
            exec( "cd $file_dir; zip -r $zip_name *" );

            if ( file_exists( $file_dir . $zip_name ) ) {
                return false;
            }
        }

        return false;
    }

    /**
     * Remove dir with files and sub dirs
     *
     * @param string $dir
     *
     * @return void
     */
    function remove_dir( $dir ) {
        if ( is_dir( $dir ) ) {
            $objects = scandir( $dir );
            foreach ( $objects as $object ) {
                if ( $object != '.' && $object != '..' ) {
                    if ( is_dir( $dir . DIRECTORY_SEPARATOR . $object ) ) {
                        $this->remove_dir( $dir . '/' . $object );
                    } else {
                        unlink( $dir . DIRECTORY_SEPARATOR . $object );
                    }
                }
            }
            rmdir( $dir );
        }
    }


    /**
     *  Get the value of a wpc transient option.
     *
     * @param string $transient Transient name. Expected to not be SQL-escaped.
     *
     * @return mixed Value of transient.
     */
    function get_wpc_transient_option( $transient ) {

        $transient_option = '_wpc_transientopt_' . $transient;
        $transient_timeout = '_wpc_transientopt_timeout_' . $transient;

        $timeout           = get_option( $transient_timeout );
        if ( false !== $timeout && $timeout < time() ) {
            $value = false;
        } else {
            $value = get_option( $transient_option );
        }

        return $value;
    }


    /**
     *  Set/update the value of a wpc transient option.
     *
     * @param string $transient  Transient name. Expected to not be SQL-escaped. Must be
     *                           167 characters or fewer in length.
     * @param mixed  $value      Transient value. Expected to not be SQL-escaped.
     * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     *
     * @return bool False if value was not set and true if value was set.
     */
    function set_wpc_transient_option( $transient, $value, $expiration = 0 ) {

        $expiration = (int) $expiration;

        $transient_timeout = '_wpc_transientopt_timeout_' . $transient;
        $option            = '_wpc_transientopt_' . $transient;

        if ( $expiration ) {
            update_site_option( $transient_timeout, time() + $expiration );
        }

        $result = update_option( $option, $value );

        return $result;
    }


    /**
     *  Check if site runs on WordPress.com
     *
     * @return bool
     */
    function is_wp_com() {

        if ( ( defined( 'IS_WPCOM' ) && IS_WPCOM ) || defined( 'WPCOMSH_VERSION' ) ) {
            return true;
        }

        return false;
    }



}

endif;