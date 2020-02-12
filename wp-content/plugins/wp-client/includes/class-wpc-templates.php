<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Templates' ) ) :

final class WPC_Templates {

    /**
     * All data of templates
     *
     * @var array
     */
    public $php_templates = array();


    /**
     * The single instance of the class.
     *
     * @var WPC_Templates
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Templates is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Templates - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

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
        if ( ! empty( $t_args ) && is_array( $t_args ) ) {
            extract( $t_args );
        }

        $located = $this->locate_template( $template_name, $path );

        if ( ! file_exists( $located ) ) {
            _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
            return '';
        }

        $located = apply_filters( 'wpc_client_get_template', $located, $template_name, $path, $t_args );

        ob_start();
        do_action( 'wpc_client_before_template_part', $template_name, $path, $located, $t_args );
        include( $located );
        do_action( 'wpc_client_after_template_part', $template_name, $path, $located, $t_args );
        $html = ob_get_clean();
        $html = do_shortcode( $html );
        $html = WPC()->replace_placeholders( $html, $t_args, $template_name );

        if ( ! $echo ) {
            return $html;
        }

        echo $html;

        return '';
    }

    /**
     * Method returns expected path for template
     *
     * @access public
     * @param string $location
     * @param string $template_name
     * @param string $path (default: '')
     * @return string
     */
    function get_template_file( $location, $template_name, $path = '' ) {
        $template_path = '';
        switch( $location ) {
            case 'theme':

                if ( WPC()->is_wp_com() ) {
                    $template_path = WPC()->get_upload_dir('wpclient/templates/' ) . get_stylesheet() . '/' . $path . $template_name;
                } else {
                    $template_path = trailingslashit( get_stylesheet_directory() . '/wp-client/' . $path ) . $template_name;
                }
                break;

            case 'plugin':
                $template_path = $this->path( $path ) . 'templates/' . $template_name;
                break;
        }

        return apply_filters( 'wpc_client_template_location', $template_path, $location, $template_name, $path );
    }


    /**
     * Get the path.
     *
     * @param string $path_name
     * @return string
     */
    public function path( $path_name = '' ) {
        $path = WPC()->plugin_dir;
        if ( ! empty( $path_name ) ) {
            $path = apply_filters( 'wp_client_path_' . $path_name, $path );
        }
        return trailingslashit( $path );
    }


    /**
     * Locate a template and return the path for inclusion.
     *
     * @access public
     * @param string $template_name
     * @param string $path (default: '')
     * @return string
     */
    function locate_template( $template_name, $path = '' ) {


        if ( WPC()->is_wp_com() ) {
            $template_path = WPC()->get_upload_dir('wpclient/templates/' ) . get_stylesheet() . '/' . $path . $template_name;
            if ( file_exists( $template_path ) ) {
                $template = $template_path;
            }
        } else {
            // check if there is template at theme folder
            $template = locate_template( array(
                trailingslashit( 'wp-client/' . $path ) . $template_name
            ) );
        }


        //if there isn't template at theme folder get template file from plugin dir
        if ( ! $template && ! empty( $this->php_templates[ $path ] ) )
            $template = trailingslashit( $this->php_templates[ $path ] ) . $template_name;

        // Return what we found.
        return apply_filters( 'wpc_client_locate_template', $template, $template_name, $path );
    }



    /**
     * Get PHP shortcode template data
     *
     * @param $name
     * @param $path
     * @return array|string
     */
    function get_template_data( $name, $path ) {
        $endpoint_dir = $this->locate_template( $name, $path );
        //var_dump( 'ep:' . $endpoint_dir );
        $template_data = implode( '', file( $endpoint_dir ) );

        $title = $desc = $tags = '';
        if ( preg_match( '|Template Name:(.*)$|mi', $template_data, $match ) ) {
            $title = _cleanup_header_comment( $match[1] );
        } else {
            return '';
        }

        if ( preg_match( '|Template Description:(.*)$|mi', $template_data, $match ) ) {
            $desc = _cleanup_header_comment( $match[1] );
        }

        if ( preg_match( '|Template Tags:(.*)$|mi', $template_data, $match ) ) {
            $tags = _cleanup_header_comment( $match[1] );
        }

        $template_dir = $this->php_templates[$path];

        return array(
            'title'         => __( $title, WPC_CLIENT_TEXT_DOMAIN ),
            'description'   => __( $desc, WPC_CLIENT_TEXT_DOMAIN ),
            'filename'      => $name,
            'path'          => $path,
            'dir'           => trailingslashit( $template_dir ) . $name,
            'endpoint_dir'  => $endpoint_dir,
            'tags'          => $tags,
            'template_dir'  => $template_dir,
        );
    }


    function get_php_templates() {
        $files = array();
        foreach ( $this->php_templates as $extension_dir => $template_dir ) {
            $files = array_merge( $files, $this->get_php_template_files( $template_dir, $extension_dir ) );
        }

        return $files;
    }


    /**
     * Get all files from templates directory
     *
     * @param $template_dir
     * @param string $extension_dir
     * @param string $dir
     * @return array
     */
    function get_php_template_files( $template_dir, $extension_dir = '', $dir = '' ) {
        $files = array();

        if ( ! empty( $dir ) )
            $dir = trailingslashit( $dir );

        $abs_path = trailingslashit( $template_dir ) . untrailingslashit( $dir );
        $ffs = scandir( $abs_path );
        foreach ( $ffs as $ff ) {
            if ( $ff == '.' || $ff == '..' ) continue;

            if ( is_dir( $abs_path . '/' . $ff ) ) {
                $files = array_merge( $files, $this->get_php_template_files( $template_dir, $extension_dir, $dir . $ff ) );
            } else {

                $template_data = $this->get_template_data( $dir . $ff, $extension_dir );
                if ( ! $template_data )
                    continue;

                $files[] = $template_data;
            }
        }
        return $files;
    }




}

endif;