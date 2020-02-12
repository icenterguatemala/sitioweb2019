<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Extensions' ) ):

class WPC_Extensions {

    /**
     * @var $ext_min_vers array Minimal version of extensions requirements for current core version
     */
    public $min_vers = array(
        'ams'   => '1.6.0',
        'fw'    => '1.5.0',
        'frmw'  => '1.3.1',
        'inv'   => '1.9.1',
        'll'    => '1.4.0',
        'mc'    => '1.2.0',
        'na'    => '1.3.1',
        'pr'    => '1.4.0',
        'pg'    => '1.3.0',
        'ppt'   => '1.6.0',
        'pm'    => '1.7.3',
        'sht'    => '1.6.0',
        'smsn'   => '1.4.0',
        'st'    => '1.3.0',
        'tlc'   => '1.4.0',
        'wl'   => '1.4.0',
    );


    protected $extensions_data = array();

    /**
     * The single instance of the class.
     *
     * @var WPC_Extensions
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Extensions is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Extensions - Main instance.
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


    /**
     * Add extension
     **/
    function add( $prefix, $data = array() ) {

        $this->extensions_data[$prefix] = $data;

    }


    /**
     * Get extension dir
     **/
    function get_dir( $prefix ) {

        if ( !empty( $this->extensions_data[$prefix]['dir'] ) ) {
            return $this->extensions_data[$prefix]['dir'];
        }

        return '';
    }


    /**
     * Get extension url
     **/
    function get_url( $prefix ) {

        if ( !empty( $this->extensions_data[$prefix]['url'] ) ) {
            return $this->extensions_data[$prefix]['url'];
        }

        return '';
    }

    /**
     * Get extension plugin value
     **/
    function get_plugin( $prefix ) {

        if ( !empty( $this->extensions_data[$prefix]['plugin'] ) ) {
            return $this->extensions_data[$prefix]['plugin'];
        }

        return '';
    }


    /**
     * Get defined version
     *
     **/
    function get_defined_version( $prefix ) {

        if ( !empty( $this->extensions_data[$prefix]['defined_version'] ) ) {
            return $this->extensions_data[$prefix]['defined_version'];
        }

        return '';
    }

    /**
     * Get required core version
     *
     **/
    function get_required_version( $prefix ) {

        if ( !empty( $this->extensions_data[$prefix]['required_version'] ) ) {
            return $this->extensions_data[$prefix]['required_version'];
        }

        return '';
    }

    /**
     * Get title
     *
     **/
    function get_title( $prefix ) {

        if ( !empty( $this->extensions_data[$prefix]['title'] ) ) {
            return $this->extensions_data[$prefix]['title'];
        }

        return '';
    }


    /**
     * Get all extensions data
     *
     **/
    function get_extensions() {
        return ( !empty( $this->extensions_data ) ) ? $this->extensions_data : array();
    }



}

endif;