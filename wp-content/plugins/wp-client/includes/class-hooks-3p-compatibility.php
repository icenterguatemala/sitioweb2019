<?php

// Exit if executed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPC_Hooks_3P_Compatibility' ) ) :

class WPC_Hooks_3P_Compatibility {

    /**
     * WPC_Hooks_3P_Compatibility Constructor.
     */
    public function __construct() {


        /*
         * Third-party Compatibility Parts
         *
         */


        //on Admin area
        if ( WPC()->is_request( 'admin' ) ) {

        } //on AJAX
        elseif ( WPC()->is_request( 'ajax' ) ) {


        } //on Front End
        else {

            //Beaver Builder compatibility
            add_filter( 'fl_builder_after_render_shortcodes', array( WPC()->hooks(), 'WPC_3P_Compatibility->beaver_builder_template_layout_content' ) );
        }


        //on Common

        //Woocommerce Compatibility
        add_filter( 'woocommerce_disable_admin_bar', array( WPC()->hooks(), 'WPC_3P_Compatibility->woocommerce_admin_access_fix' ), 10, 1 );
        add_filter( 'woocommerce_prevent_admin_access', array( WPC()->hooks(), 'WPC_3P_Compatibility->woocommerce_admin_prevent_access_fix' ), 10, 1 );


        //Divi Theme Builder compatibility
        if ( function_exists( 'et_builder_should_load_framework' ) && function_exists( 'is_et_pb_preview' ) ) {
            add_filter( 'wpc_is_portal_page_preview', array( WPC()->hooks(), 'WPC_3P_Compatibility->filter_divi_is_portal_page_preview' ), 10, 2 );
            add_filter( 'post_type_link', array( WPC()->hooks(), 'WPC_3P_Compatibility->filter_divi_builder_portalhub_compatibility' ), 10, 4 );

            add_filter( 'et_builder_post_types', array( WPC()->hooks(), 'WPC_3P_Compatibility->divi_expand_et_builder_post_types' ), 10, 1 );
            add_action( 'add_meta_boxes', array( WPC()->hooks(), 'WPC_3P_Compatibility->divi_add_meta_box' ) );
            add_action( 'admin_head', array( WPC()->hooks(), 'WPC_3P_Compatibility->divi_admin_js' ) );
        }



        //WPML Compatibility
        add_filter( 'wpc_change_portalhub_id', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_portalhub_id_compatibility' ), 10, 1 );
        add_filter( 'option_wpc_pages', array( WPC()->hooks(), 'WPC_3P_Compatibility->filter_wpml_page_options' ), 10, 1 );
        add_filter( 'wpc_get_slug_post', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_get_slug_post' ), 10, 2 );
        add_filter( 'wpc_get_portalhub_for_client', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_get_portalhub_for_client' ), 10, 1 );
        add_filter( 'wpc_portalhub_locale', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_portalhub_locale' ), 10, 2 );
        add_filter( 'wpc_portalhub_locale_home_url', array( WPC()->hooks(), 'WPC_3P_Compatibility->wpml_locale_home_url' ), 10, 2 );

        //Visual Composer compatibility
        add_filter( 'editable_roles', array( WPC()->hooks(), 'WPC_3P_Compatibility->vc_editable_roles' ), 10, 1 );

        //Elementor Editor
        add_filter( 'elementor/document/urls/preview', array( WPC()->hooks(), 'WPC_3P_Compatibility->elementor_preview_url' ), 99, 2 );

        //Beaver Builder Editor
        add_filter( 'fl_get_edit_url', array( WPC()->hooks(), 'WPC_3P_Compatibility->beaver_builder_preview_url' ), 99, 2 );



        add_filter('wp_client_shortcodes_no_redirect', array( WPC()->hooks(), 'WPC_3P_Compatibility->pre_shortcodes_no_redirect' ), 99 );

        add_filter('wp_client_is_hub_preview', array( WPC()->hooks(), 'WPC_3P_Compatibility->pre_is_hub_preview' ), 99 );

    }

} //end class

endif;

new WPC_Hooks_3P_Compatibility();