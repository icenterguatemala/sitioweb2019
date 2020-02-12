<?php

if ( !class_exists( 'WPC_PPT_Admin' ) ) {

    class WPC_PPT_Admin extends WPC_PPT_Admin_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->ppt_common_construct();
            $this->ppt_admin_common_construct();

            //add metabox
            add_action( 'admin_init', array( &$this, 'meta_init' ) );

            //save metabox data
            add_action( 'save_post', array( &$this, 'save_meta' ), 10 );

            //add js/css to add\edit posts
            add_action( 'admin_print_scripts-post.php', array( &$this, 'load_css_js' ) );
            add_action( 'admin_print_scripts-post-new.php', array( &$this, 'load_css_js' ) );

            add_filter( 'wpc_client_settings_tabs', array( &$this, 'add_settings_tab' ) );
            add_action( 'wpc_client_settings_tab_private_post_types', array( &$this, 'show_settings_page' ) );

            add_filter( 'wpc_client_templates_emails_array', array( &$this, 'add_email_template' ) );
            add_filter('wpc_client_templates_sms_array', array( &$this, 'templates_sms_array' ));

            //uninstall
            add_action( 'wp_client_uninstall', array( &$this, 'uninstall_extension' ) );

            //add manager or wpc_admin capability View Private Post Types
            add_filter( 'wp_client_change_caps', array( &$this, 'settings_update' ) );

            //add array help
            add_filter( 'wpc_set_array_help', array( &$this, 'wpc_set_array_help' ), 10, 2 );
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'admin_menu' ) );

            //add templates shortcodes
            add_filter( 'wpc_client_templates_shortcodes_array', array( &$this, 'add_templates_shortcodes' ) );

            add_filter( 'wpc_screen_options_pagination', array( &$this, 'screen_options_pagination' ), 10 );

            //add screen options for client Page
            add_action( 'admin_head', array( &$this, 'add_screen_options' ), 5 );

            // Add Settings link when activate plugin
            add_filter( 'plugin_action_links_wp-client-private-post-types/wp-client-private-post-types.php', array( &$this, 'filter_action_links' ), 99 );
        }


        public function templates_sms_array( $templates ) {
            //email when updated Private Post type page
            $templates['private_post_type'] = array(
                'tab_label'             => __( 'PPT: New Item Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Private Post Type: New Item Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( "  >> This sms will be sent to %s when new item will be assigned to them.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => __( '{contact_name}, {page_title} and {business_name} will not be change as these placeholders will be used in the sms.', WPC_CLIENT_TEXT_DOMAIN )
            );
            return $templates;
        }


        function add_screen_options() {
            if ( isset( $_GET['page'] ) && 'wpc_private_post_types' == $_GET['page'] ) {
                add_screen_option(
                    'per_page',
                    array(
                        'label' => __( 'Posts', WPC_CLIENT_TEXT_DOMAIN ),
                        'default' => WPC()->admin()->list_table_per_page,
                        'option' => 'wpc_ppt_posts_per_page'
                    )
                );
            }
        }


        function screen_options_pagination( $wpc_screen_options ) {

            $wpc_screen_options = array_merge( $wpc_screen_options, array(
                'wpc_ppt_posts_per_page',
            ) );

            return $wpc_screen_options;
        }


        function add_templates_shortcodes( $wpc_shortcodes_array ) {

            //shortcode List
            $wpc_shortcodes_array['wpc_client_ppt_list'] = array(
                'tab_label'             => __( 'PPT: Private List', WPC_CLIENT_TEXT_DOMAIN),
                'label'                 => __( 'Private Post Types: Private List', WPC_CLIENT_TEXT_DOMAIN),
                'description'           => __( '  >> This template for [wpc_client_private_post_types] shortcode', WPC_CLIENT_TEXT_DOMAIN),
                'templates_dir'         => $this->extension_dir . 'includes/templates/',
            );

            return $wpc_shortcodes_array;
        }

        function admin_menu( $menu ) {
            $cap = 'manage_options';
            if ( current_user_can( 'wpc_manager' ) && current_user_can( 'view_privat_post_type' ) && !current_user_can( 'administrator' ) ) {
                $cap = "wpc_manager";
            } elseif( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
                $cap = "wpc_admin";
            }

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

            $menu['wpc_private_post_type'] = array(
                'page_title'        => __( 'Private Post Types', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Private Post Types', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpc_private_post_types',
                'capability'        => $cap,
                'function'          => array( &$this, 'main' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 121,
            );
            return $menu;
        }


        function main() {
            include_once( $this->extension_dir . 'includes/admin/private_posts_list.php' );
        }


        function wpc_set_array_help( $array_help, $method ) {
            switch( $method ) {
                case '_add_wpclients_paid_registration_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From the Paid Registration page you can view all of your pending %2$s, who are %2$s that have registered using the %1$s Registration Form, but have not yet paid your preset registration fee. If a %1$s registers, but does not pay the registration fee, their account will be created along with their HUB Page, but the account will not be active, and the %1$s will not have access to their HUB Page until after the registration fee has been paid.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_settingsprivate_post_types_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'The Private Post Types Extension allows you to make any page, post or custom post type part of your Portal. You can easily assign permissions, restrict public viewing, and include links to these resources in your %s\'s HUBs and Portal Pages. Simply select the post types you want to protect from this tab, and then navigate to the corresponding post to begin assigning to %s/%s.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'], WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ) . '</p>' .
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


        /**
         * Add capadility for Private Post Types.
         */
        function settings_update( $wpc_capabilities ) {
            if( 'wpc_manager' == $_POST['wpc_role'] || 'wpc_admin' == $_POST['wpc_role'] ) {
                if( !empty( $wpc_capabilities[ $_POST['wpc_role'] ]['view_privat_post_type'] ) ) {

                    $wpc_private_post_types = WPC()->get_settings( 'private_post_types' ); //check post type etc
                    if( isset( $wpc_private_post_types['types'] ) ) {
                        $wpc_private_post_types = $wpc_private_post_types['types'];
                        $post_types = get_post_types(); //all post type
                        $excluded_post_type = $this->get_excluded_post_types();
                        $post_types = array_diff( $post_types, $excluded_post_type );
                        if( is_array( $post_types ) && $post_types && is_array( $wpc_private_post_types ) && $wpc_private_post_types ) {
                            foreach( $post_types as $type ) {
                                if ( isset( $wpc_private_post_types[ $type ] ) && ( 1 == $wpc_private_post_types[ $type ] || 'yes' == $wpc_private_post_types[ $type ] ) ) {

                                    $new_caps = get_post_type_object( $type );
                                    $new_caps = $new_caps->cap;
                                    foreach ( $new_caps as $cap ) {
                                        $wpc_capabilities[ $_POST['wpc_role'] ][ $cap ] = true ;
                                    }

                                }
                            }
                        }
                    }
                }
            }

            return $wpc_capabilities;

        }


        /*
        * Function unisntall
        */
        function uninstall_extension() {
            WPC()->delete_settings( 'private_post_types' );

            //deactivate the extension
            $plugins = get_option( 'active_plugins' );
            if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                $new_plugins = array();
                foreach( $plugins as $plugin )
                    if ( 'wp-client-private-post-types/wp-client-private-post-types.php' != $plugin )
                        $new_plugins[] = $plugin;
            }
            update_option( 'active_plugins', $new_plugins );

        }


        /*
        * Add settings tab
        */
        function add_settings_tab( $tabs ) {
            $tabs['private_post_types'] = array(
                'title'     => __( 'Private Post Types', WPC_CLIENT_TEXT_DOMAIN ),
            );

            return $tabs;
        }


        /*
        * Show settings page
        */
        function show_settings_page() {
            include_once( $this->extension_dir . 'includes/admin/settings_private_post_types.php' );
        }


        /*
        * Add email template
        */
        function add_email_template( $wpc_emails_array ) {
            //email when updated Private Post type page
            $wpc_emails_array['private_post_type'] = array(
                'tab_label'             => __( 'Private Post Type', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => __( 'Private Post Type: New Item Assigned', WPC_CLIENT_TEXT_DOMAIN ),
                'description'           => sprintf( __( "  >> This email will be sent to %s when new item will be assigned to them.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                'subject_description'   => '',
                'body_description'      => __( '{contact_name}, {page_title} and {page_id} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
                'tags'                  => 'client_recipient other'
            );


            return $wpc_emails_array;
        }



        /*
        * Add meta box
        */
        function meta_init() {
            $private_post_types = get_option( 'wpc_private_post_types' );
            if ( !empty( $private_post_types['types'] ) && is_array( $private_post_types['types'] ) ) {
                foreach( $private_post_types['types'] as $key => $value ) {
                    if ( 1 == $value || 'yes' == $value ) {
                        //metabox for Clients and Circles
                        add_meta_box( 'wpclients_userids-meta', sprintf( __( 'Allowed %s and %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['circle']['p'] ), array( &$this, 'allowed_client_circles_options' ), $key, 'side', 'default' );
                    }
                }
            }
        }


        /*
        * load CSS JS
        */
        function load_css_js() {
            global $post;

            //skip include for not protected post types
            if ( isset( $post ) && isset( $post->post_type ) ) {

                $private_post_types = get_option( 'wpc_private_post_types' );

                if ( !isset( $private_post_types['types'] ) || !is_array( $private_post_types['types'] ) || !array_key_exists( $post->post_type, $private_post_types['types'] ) ) {
                    return '';
                }
            }

            wp_register_style( 'wp-client-style', WPC()->plugin_url . 'css/style.css' );
            wp_enqueue_style( 'wp-client-style' );
        }

        /*
        * display metabox
        */
        function allowed_client_circles_options() {
            global $post;

            $wpc_protected = get_post_meta( $post->ID, '_wpc_protected', true );

            //get clients from Client Circles
            $user_ids = WPC()->assigns()->get_assign_data_by_object( 'private_post', $post->ID, 'client' );
            $user_ids = is_array( $user_ids ) ? $user_ids : array();

            $groups_id      = WPC()->assigns()->get_assign_data_by_object( 'private_post', $post->ID, 'circle' );
            $groups_id = is_array( $groups_id ) ? $groups_id : array();
            $current_page = 'ppt_metabox';
            ?>
            <div>
                <p>
                    <label class="selectit" >
                        <input type="checkbox" name="wpc_protected" id="wpc_protected" value="1" <?php echo ( 1 == $wpc_protected ) ? 'checked="checked"' : '' ?> />
                        <?php _e( 'Protect this page', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </label>

                </p>
                <div class="wpc_clear"></div>
                <div class="protect_settings" <?php echo ( 1 != $wpc_protected ) ? 'style="display:none;"' : '' ?>>
                    <?php
                    $wpc_pages = WPC()->get_settings('pages');
                    if( is_array( $wpc_pages ) ) {
                        $pages = array_values( $wpc_pages );
                    } else {
                        $pages = array();
                    }

                    if ( !empty( $_GET['post'] ) && in_array( $_GET['post'], $pages ) ) {

                    ?>
                    <p style="color: red;"><?php _e( 'Be careful when protecting this page, as it is used for Theme Link Pages.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>

                    <?php } ?>
                    <p>
                        <?php
                            $link_array = array(
                                'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_protected_clients',
                                'id'    => 'wpc_clients',
                                'value' => implode( ',', $user_ids )
                            );
                            $additional_array = array(
                                'counter_value' => count($user_ids),
                            );
                            WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>
                    </p>
                    <p>
                        <?php
                            $link_array = array(
                                'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_protected_circles',
                                'id'    => 'wpc_circles',
                                'value' => implode( ',', $groups_id )
                            );
                            $additional_array = array(
                                'counter_value' => count( $groups_id ),
                            );
                            WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>

                    </p>


                    <p>
                        <label class="selectit" >
                            <input type="checkbox" name="wpc_send_notify" id="wpc_send_notify" value="1" />
                        <?php
                        if ( !isset( $_GET['post'] ) ) {
                            printf( __( 'Send notify about new item to selected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                        } else {
                            printf( __( 'Send notify about update item to selected %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] );
                        }?>
                        </label>
                    </p>


                    <p id="block_send" style="display: none;" >
                        <?php
                            $link_array = array(
                                'data-input' => 'send_wpc_clients',
                                'id'      => 'send_a_wpc_clients',
                                'title'   => sprintf( __( 'Send to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                'text'    => sprintf( __( 'Send %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'send_wpc_clients',
                                'id'    => 'send_wpc_clients',
                                'value' => implode( ',', $user_ids ),
                                'data-include' => ( $user_ids ) ? implode( ',', $user_ids ) : '-1'
                            );
                            $additional_array = array(
                                'counter_value'     => count( $user_ids ),
                            );

                            WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>

                        <br />

                        <?php
                            $link_array = array(
                                'data-input' => 'send_wpc_circles',
                                'title'   => sprintf( __( 'Send to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                'text'    => sprintf( __( 'Send %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'send_wpc_circles',
                                'id'    => 'send_wpc_circles',
                                'value' => implode( ',', $groups_id ),
                                'data-include' => ( $groups_id ) ? implode( ',', $groups_id ) : '-1'
                            );
                            $additional_array = array(
                                'counter_value'     => count( $groups_id ),
                            );
                            WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>

                    </p>
                </div>
            </div>
            <script type="text/javascript">
                var site_url = '<?php echo site_url();?>';

                jQuery(document).ready(function(){

                    jQuery( "#wpc_send_notify" ).change( function() {
                        jQuery('#block_send').toggle( jQuery(this).is(':checked') );
                    });

                    //jQuery('.protect_settings').toggle( jQuery(this).is(':checked') );
                    jQuery( "#wpc_protected" ).change( function() {
                        jQuery('.protect_settings').toggle( jQuery(this).is(':checked') );
                    });

                    jQuery('#wpc_clients').change(function() {
                        //alert( jQuery(this).val() )
                        var value = jQuery(this).val()
                        if ( '' == value )
                            value = -1
                        jQuery('#send_wpc_clients').data('include', value);
                        jQuery('#send_wpc_clients').val( jQuery(this).val() );
                        var count = jQuery(this).val().split(",");
                        if( '' != count)
                            count = count.length
                        else
                            count = 0
                        jQuery('#send_wpc_clients').next().text( '(' + count + ')' );
                    });

                    jQuery('#wpc_circles').change(function() {
                        var value = jQuery(this).val()
                        if ( '' == value )
                            value = -1
                        jQuery('#send_wpc_circles').data('include', value);
                        jQuery('#send_wpc_circles').val( jQuery(this).val() );
                        var count = jQuery(this).val().split(",");
                        if( '' != count)
                            count = count.length
                        else
                            count = 0
                        jQuery('#send_wpc_circles').next().text( '(' + count + ')' );
                    });
                });
            </script>
            <?php
        }


        /*
        * Save data from metabox
        */
        function save_meta( $post_id ) {
            //for quick edit
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                return $post_id;
            }

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post_id;
            }

            if ( defined( 'WPC_CLIENT_NOT_SAVE_META' ) && WPC_CLIENT_NOT_SAVE_META ) {
                return $post_id;
            }

            //exist data from our meta
            if ( isset( $_POST['wpc_protected'] ) || isset( $_POST['wpc_protected_clients'] ) ||isset( $_POST['wpc_protected_circles'] ) ) {

                $tmp_post = get_post( $post_id );

                //no post or it's revision
                if ( !$tmp_post || 'revision' == $tmp_post->post_type )
                    return $post_id;


                //mark as protected
                if ( isset( $_POST['wpc_protected'] ) && '1' == $_POST['wpc_protected'] ) {
                    update_post_meta( $post_id, '_wpc_protected', '1' );
                } else {
                    delete_post_meta( $post_id, '_wpc_protected' );
                }

                if ( isset( $_POST['wpc_protected_clients'] ) && '' != $_POST['wpc_protected_clients'] ) {
                    $selected_clients = explode( ',', $_POST['wpc_protected_clients'] );

                    if ( is_array( $selected_clients ) && count( $selected_clients ) ) {
                        WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'client', $selected_clients );
                    } else {
                        WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'client', array() );
                    }
                } else {
                    WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'client', array() );
                }


                //save client Client Circles for Portal Page
                if ( isset( $_POST['wpc_protected_circles'] ) && '' != $_POST['wpc_protected_circles'] ) {
                    $selected_circles = explode( ',', $_POST['wpc_protected_circles'] );

                    if ( is_array( $selected_circles ) && count( $selected_circles ) ) {
                        WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'circle', $selected_circles );
                    } else {
                        WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'circle', array() );
                    }
                } else {
                    WPC()->assigns()->set_assigned_data( 'private_post', $post_id, 'circle', array() );
                }


                // send updates to client
                if ( isset( $_POST['wpc_send_notify'] ) && '1' == $_POST['wpc_send_notify'] ) {

                    $user_ids = ( isset( $_POST['send_wpc_clients'] ) ) ? explode( ',', $_POST['send_wpc_clients'] ) : array();
                    $groups_id = ( isset( $_POST['send_wpc_circles'] ) ) ? explode( ',', $_POST['send_wpc_circles'] ) : array();

                    //get clients from Client Circles
                    if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                        foreach( $groups_id as $group_id ) {
                            $user_ids = array_merge( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }

                    $user_ids = array_unique( $user_ids );
                } else {
                    $user_ids = array();
                }

                //add clients staff to list
                if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
                    $not_approved_staff = get_users( array( 'role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID', ) );
                    $staff_ids = array();
                    foreach( $user_ids as $user_id ) {

                        $args = array(
                            'role'          => 'wpc_client_staff',
                            'orderby'       => 'ID',
                            'order'         => 'ASC',
                            'meta_key'      => 'parent_client_id',
                            'meta_value'    => $user_id,
                            'exclude'       => $not_approved_staff,
                            'fields'        => 'ID',
                        );

                        $user_ids = array_merge( $user_ids, get_users( $args ) );

                    }
                }
                $user_ids = array_unique( $user_ids );

                //send update email to selected clients
                foreach ( $user_ids as $user_id ) {

                    $userdata   = (array) get_userdata( $user_id );
                    if ( !$userdata )
                        continue;
                    $link       = get_permalink( $post_id );

                    $args = array(
                        'client_id' => $user_id,
                        'page_id' => $link,
                        'page_title' => get_the_title( $post_id )
                    );

                    //send email
                    WPC()->mail( 'private_post_type', $userdata['data']->user_email, $args, 'private_post_type' );
                }

            }
        }

        function added_capability_for_post_type( $role, $privat_post_types ) {
            $privat_post_types = ( isset( $privat_post_types[ 'types' ] ) ) ? $privat_post_types[ 'types' ] : array() ;
            $types = array();
            if ( is_string( $role ) && in_array( $role, array( 'wpc_admin', 'wpc_manager' ) ) && is_array( $privat_post_types ) ) {
                foreach ( $privat_post_types as $name_type => $type ) {
                    if ( 1 == $type || 'yes' == $type ) {
                        $types[] = $name_type;
                    }
                }
                $wpc_capabilities = WPC()->get_settings( 'capabilities' );
                $_POST[ 'wpc_role' ] = $role;
                $wpc_capabilities = apply_filters( 'wp_client_change_caps', $wpc_capabilities );
                WPC()->members()->added_role( $role, $wpc_capabilities ) ;

            }

        }

        /**
         * Add Setting link at plugin page
         * @param $links
         * @return mixed
         */
        public function filter_action_links( $links ) {

            if ( WPC()->is_licensed( 'WP-Client' ) ) {

                $links['settings'] = sprintf( '<a href="admin.php?page=wpclients_settings&tab=private_post_types">%s</a>', __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ) );

            }

            return $links;

        }

        //end class
    }

}
