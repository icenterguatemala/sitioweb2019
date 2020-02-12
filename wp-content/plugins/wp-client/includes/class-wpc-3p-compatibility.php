<?php
if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'WPC_3P_Compatibility' ) ) :

class WPC_3P_Compatibility {

    /**
     * The single instance of the class.
     *
     * @var WPC_3P_Compatibility
     * @since 4.5
     */
    protected static $_instance = null;


    /**
     * Instance.
     *
     * Ensures only one instance of WPC_3P_Compatibility is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_3P_Compatibility - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct() {

    }


    public function woocommerce_admin_access_fix( $option ) {
        return false;
    }


    public function woocommerce_admin_prevent_access_fix( $prevent_access ) {
        return false;
    }


    function divi_add_meta_box() {
        if ( function_exists( 'et_single_settings_meta_box' ) ) {
            $clientspage = get_post_type_object( 'clientspage' );
            $portalhub = get_post_type_object( 'portalhub' );
            add_meta_box('et_settings_meta_box', sprintf(__('Divi %s Settings', 'Divi'), $clientspage->labels->singular_name), 'et_single_settings_meta_box', 'clientspage', 'side', 'high');
            add_meta_box('et_settings_meta_box', sprintf(__('Divi %s Settings', 'Divi'), $portalhub->labels->singular_name), 'et_single_settings_meta_box', 'portalhub', 'side', 'high');
        }
    }


    function divi_admin_js() {
        $s = get_current_screen();
        if ( ! empty( $s->post_type ) && ( $s->post_type == 'clientspage' || $s->post_type == 'portalhub' ) ) { ?>
            <script>
                jQuery( function ($) {
                    $('#et_pb_layout').insertAfter( $('#et_pb_main_editor_wrap') );
                });
            </script>
        <?php }
    }


    function divi_expand_et_builder_post_types( $post_types ) {
        $post_types[] = 'clientspage';
        $post_types[] = 'portalhub';

        return $post_types;
    }


    /**
     * Expand Visual Composer Roles who can add/edit Portal Pages
     *
     * @param $all_roles
     * @return mixed
     */
    function vc_editable_roles( $all_roles ) {
        $all_roles['wpc_admin']['capabilities']['edit_posts'] = true;
        $all_roles['wpc_manager']['capabilities']['edit_posts'] = true;

        //Visual Composer has caps compability
        add_filter( 'role_has_cap', array( &$this, 'vc_add_caps' ), 10, 3 );

        return $all_roles;
    }


    /**
     * Add capability for WPC_Admin & WPC_Manager "edit_posts"
     * when Visual Composer get roles+capabilities
     *
     * @param $capabilities
     * @param $cap
     * @param $name
     * @return mixed
     */
    function vc_add_caps( $capabilities, $cap, $name ) {
        if ( $name == 'wpc_admin' || $name == 'wpc_manager' ) {
            if ( 'edit_posts' == $cap ) {
                $capabilities[$cap] = true;
            }
        }

        return $capabilities;
    }


    /**
     * Check active WPML plugin
     *
     * @return bool|mixed
     */
    function is_wpml_active() {
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            global $sitepress;

            return $sitepress->get_setting( 'setup_complete' );
        }

        return false;
    }


    /**
     * Check translatable ability for Portal Start Pages
     *
     * @return bool|mixed|void
     */
    function is_portalhub_translatable() {
        global $sitepress;

        return $sitepress->is_translated_post_type( 'portalhub' );
    }


    /**
     * WPML compatibility with Portal/Start Pages list table columns
     *
     */
    function wpml_portalhub_columns() {
        if ( $this->is_wpml_active() && $this->is_portalhub_translatable() ) {
            global $sitepress;

            $custom_columns = new WPML_Custom_Columns( $sitepress );
            add_filter( 'manage_portalhubs_columns', array( $custom_columns, 'add_posts_management_column' ) );
            add_action( 'manage_portalhubs_custom_column_content', array( $custom_columns, 'add_content_for_posts_management_column' ) );
        }
    }


    /**
     * Get translated HUB Page ID
     *
     * @param int $post_id
     * @return bool|null|string
     */
    function wpml_portalhub_id_compatibility( $post_id ) {
        if ( ! $this->is_wpml_active() || ! $this->is_portalhub_translatable() )
            return $post_id;

        global $wpdb, $wpml_post_translations;

        //get translation ID for current post ID
        $trid = $wpdb->get_var( $wpdb->prepare(
            "SELECT trid 
            FROM {$wpdb->prefix}icl_translations
            WHERE element_type='post_portalhub' AND
                  element_id = %d",
            $post_id
        ) );

        $post_id = $wpml_post_translations->get_original_post_ID( $trid );

        return $post_id;
    }


    function filter_wpml_page_options( $wpc_pages ) {
        if ( $this->is_wpml_active() ) {
            global $sitepress;
            foreach ( $wpc_pages as $key => $page ) {
                $trid = $sitepress->get_element_trid( $page, 'post_page' );
                $all_translations = $sitepress->get_element_translations( $trid, 'post_page' );
                $current_lang = $sitepress->get_current_language();

                if ( ! empty( $all_translations[$current_lang]->element_id ) ) {
                    $wpc_pages[ $key ] = $all_translations[$current_lang]->element_id;
                }
            }
        }

        return $wpc_pages;
    }


    function wpml_portalhubs_list_table_tablenav( $which ) {
        if ( 'top' == $which ) {
            if ( $this->is_wpml_active() && $this->is_portalhub_translatable() ) {
                global $sitepress;

                $current_language          = $sitepress->get_current_language();
                $active_languages          = $sitepress->get_active_languages();

                global $wpdb;
                $posts_count = $wpdb->get_results( $wpdb->prepare(
                    "SELECT language_code, COUNT( p.ID ) AS c
                    FROM {$wpdb->prefix}icl_translations t
                    JOIN {$wpdb->posts} p ON t.element_id = p.ID AND t.element_type = CONCAT( 'post_', p.post_type )
                    WHERE p.post_type = %s AND 
                          t.language_code IN ('" . implode( "','", array_keys( $active_languages ) ) . "') AND
                          p.post_status = 'publish'
                    GROUP BY language_code",
                    'portalhub',
                    'post_portalhub'
                ), ARRAY_A );

                $counts = array();
                foreach ( $posts_count as $count ) {
                    $counts[ $count['language_code'] ] = $count['c'];
                } ?>

                <ul class="subsubsub">
                    <div class="icl_subsubsub" style="clear: both;">
                        <ul>
                            <?php $i = 1; $count = count( $active_languages );
                            foreach ( $active_languages as $key => $lang ) { ?>

                                <li class="language_<?php echo $key ?>">
                                    <?php if ( $current_language == $key ) { ?>
                                        <strong><?php echo $lang['display_name'] ?> <span class="count <?php echo $key ?>">(<?php echo $counts[ $key ] ?>)</span></strong>
                                    <?php } else { ?>
                                        <a href="?page=wpclients_content&tab=portalhubs&lang=<?php echo $key ?>">
                                            <?php echo $lang['display_name'] ?> <span class="count <?php echo $key ?>">(<?php echo $counts[ $key ] ?>)</span>
                                        </a>
                                    <?php }
                                    if ( $count != $i ) { ?>
                                        &nbsp;|&nbsp;
                                    <?php }
                                    $i++; ?>
                                </li>

                            <?php } ?>
                        </ul>
                    </div>
                </ul>
            <?php }
        }
    }


    function wpml_get_slug_post( $post, $page_id ) {

        if ( ! $this->is_wpml_active() )
            return $post;

        global $sitepress;

        $trid = $sitepress->get_element_trid( $page_id, 'post_page' );
        $all_translations = $sitepress->get_element_translations( $trid, 'post_page' );
        $current_lang = $sitepress->get_current_language();

        if ( ! empty( $all_translations[ $current_lang ] ) ) {
            $post = get_post( $all_translations[ $current_lang ]->element_id );
        }

        return $post;
    }


    function wpml_get_portalhub_for_client( $portalhub ) {

        if ( ! $this->is_wpml_active() || ! $this->is_portalhub_translatable() )
            return $portalhub;

        global $sitepress;
        $current_lang_portalhub_id = icl_object_id( $portalhub->ID, 'portalhub', true, ICL_LANGUAGE_CODE );
        $default_lang_portalhub_id = icl_object_id( $portalhub->ID, 'portalhub', true, $sitepress->get_default_language() );

        if ( ! empty( $current_lang_portalhub_id ) && $portalhub->ID != $current_lang_portalhub_id ) {
            $portalhub  = get_post( $current_lang_portalhub_id );
        } elseif ( ! empty( $default_lang_portalhub_id ) && $portalhub->ID != $default_lang_portalhub_id ) {
            $portalhub  = get_post( $default_lang_portalhub_id );
        }

        return $portalhub;
    }


    function wpml_locale_home_url( $home_url, $locale ) {
        if ( ! $locale || ! $this->is_wpml_active() )
            return $home_url;

        global $sitepress;
        $home_url = $sitepress->language_url( $locale );

        return $home_url;
    }


    function wpml_portalhub_locale( $locale, $post ) {
        if ( ! $this->is_wpml_active() )
            return $locale;

        global $sitepress;
        $locale = $sitepress->get_language_for_element( $post->ID, 'post_' . $post->post_type );

        return $locale;
    }


    function wptplt_data( $data, $place ) {
        if ( md5( str_replace( '_', '', 'val_id' ) . get_option( 'WP-Client_license_salt' ) ) == $data ) {
            return str_replace( '_', '', 'val_id' );
        }
        return false;
    }

	/**
     * Elementor compatibility editor with HUB and Portal Page
	 * @param $url
	 * @param $obj
	 *
	 * @return string
	 */
    function elementor_preview_url( $url, $obj ) {

        $post = $obj->get_main_post();

        if ( $post && 'portalhub' == $post->post_type ) {
            $url = add_query_arg( array( 'wpc_hub_preview_id' => $obj->get_main_id(), 'wpc_hub_preview_key' => wp_create_nonce( 'wpc_hub_preview_' . $obj->get_main_id() ) ), $url );
        }

        return $url;
    }

	/**
     * Beaver Builder compatibility editor with HUB and Portal Page
	 * @param $url
	 * @param $post
	 *
	 * @return string
	 */
	function beaver_builder_preview_url( $url, $post ) {

        if ( $post && 'portalhub' == $post->post_type ) {
            $url = add_query_arg( array( 'wpc_hub_preview_id'  => $post->ID,
                                         'wpc_hub_preview_key' => wp_create_nonce( 'wpc_hub_preview_' . $post->ID )
            ), $url );
        }

        return $url;
	}

	/**
	 * Beaver Builder template layouts content
	 * @param string $content
	 *
	 * @return string
	 */
	function beaver_builder_template_layout_content( $content ) {

	    if( FLBuilderModel::is_builder_enabled() && ! isset( $_GET['fl_builder'] ) ) {
            $content = WPC()->pages()->replace_placeholders_in_content( $content );
        }

        return $content;
	}

    /**
     * Custom Hack for earlier compatibility with function
     * @param bool $no_redirect
     *
     * @return bool
     */
    function pre_shortcodes_no_redirect( $no_redirect ) {

        if ( ! current_user_can('edit_pages' ) )
            return $no_redirect;


        //Elementor forced shutdown redirect for shortcodes
        if ( isset( $_GET['elementor-preview'] ) ) {
            return true;
        }


        //Beaver Builder forced shutdown redirect for shortcodes
        if ( method_exists( 'FLBuilderModel', 'is_builder_enabled' ) && FLBuilderModel::is_builder_enabled() && isset( $_GET['fl_builder'] ) ) {
            return true;
        }


        //Divi Builder forced shutdown redirect for shortcodes
        if ( function_exists( 'et_builder_should_load_framework' ) && function_exists( 'is_et_pb_preview' ) ) {
            if( is_et_pb_preview() || isset( $_GET['et_fb'] ) ) {
                return true;
            }
        }


        //zn_pb Builder forced shutdown redirect for shortcodes
        if ( isset( $_GET['zn_pb_edit'] ) ) {
            return true;
        }

        return $no_redirect;
    }


    /**
     * Custom Hack for earlier compatibility with function
     * @param bool $hub_preview
     *
     * @return bool
     */
    function pre_is_hub_preview( $hub_preview ) {

        if ( ! current_user_can('edit_pages' ) )
            return $hub_preview;


        //ZION Builder forced shutdown redirect for shortcodes
        if ( current_user_can( 'administrator' ) && isset( $_GET['zn_pb_edit'] ) ) {
            $hub_preview = true;
        }

        return $hub_preview;
    }

    /**
     * Divi Builder edit Portal Page
     * @param $is_preview
     * @param $user_id
     * @return bool
     */
    function filter_divi_is_portal_page_preview( $is_preview, $user_id ) {
        if( current_user_can('edit_pages') && ( is_et_pb_preview() || isset( $_GET['et_fb'] ) ) ) {
            return true;
        }
        return $is_preview;
    }

    /**
     * Divi Builder compatibility editor with HUB and Portal Page
     * @param $post_link
     * @param $post
     * @param $leavename
     * @param $sample
     *
     * @return string
     */
    function filter_divi_builder_portalhub_compatibility( $post_link, $post, $leavename, $sample ) {

        if( $post && 'portalhub' == $post->post_type ) {
            $post_link = add_query_arg( array( 'wpc_hub_preview_id'  => $post->ID,
                                               'wpc_hub_preview_key' => wp_create_nonce( 'wpc_hub_preview_' . $post->ID )
            ), $post_link );
        }

        return $post_link;
    }

}

endif;