<?php

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'WPC_Notices' ) ) :

class WPC_Notices {

    /**
     * The single instance of the class.
     *
     * @var WPC_Notices
     * @since 4.5
     */
    protected static $_instance = null;

    var $notices = array();

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Notices is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Notices - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {

        add_action( 'wp_client_admin_notices', array( &$this, 'render_admin_notices_our_pages' ) );
        add_action( 'wp_client_admin_notices_all_pages', array( &$this, 'render_admin_notices_all_pages' ) );
    }


    /**
     * Add notice to queue
     *
     * @param string $message The text to display in the notice.
     * @param string $type The singular name of the notice type - either error, updated or notice. [optional]
     * @param string $place The singular name of the notice place - either our_pages, all_pages. [optional]
     */
    function add_notice( $message, $type = 'updated', $place = 'our_pages' ) {

        $this->notices[$place][$type][] = $message;
    }

    /**
     * Add all pages notice to queue
     *
     * @param string $message The text to display in the notice.
     * @param string $type The singular name of the notice type - either error, updated or notice. [optional]
     */
    function add_all_pages_notice( $message, $type = 'updated' ) {

        $this->notices['all_pages'][$type][] = $message;
    }

    /**
     * Render one admin notice
     *
     * @param string $message The text to display in the notice.
     * @param string $type The singular name of the notice type - either error, updated or notice. [optional]
     * @return void
     */
    function render_admin_notice( $message, $type = 'updated' ) {

        if ( empty( $message ) )
            return;

        echo '<div class="' . $type . ' wpc_notice fade">
            <p>' . $message . '</p>
        </div>';
    }

    /**
     * Render all admin notices from queue on our pages
     *
     * @return void
     */
    function render_admin_notices_our_pages() {
        if ( empty( $this->notices['our_pages'] ) )
            return;

        foreach( $this->notices['our_pages'] as $type => $notices ) {
            foreach( $notices as $message ) {
                $this->render_admin_notice( $message, $type );
            }
            unset( $this->notices['our_pages'][$type] );
        }
    }

    /**
     * Render all admin notices from queue on all pages
     *
     * @return void
     */
    function render_admin_notices_all_pages() {
        if ( empty( $this->notices['all_pages'] ) )
            return;

        foreach( $this->notices['all_pages'] as $type => $notices ) {
            foreach( $notices as $message ) {
                $this->render_admin_notice( $message, $type );
            }
            unset( $this->notices['all_pages'][$type] );
        }
    }

}

endif;