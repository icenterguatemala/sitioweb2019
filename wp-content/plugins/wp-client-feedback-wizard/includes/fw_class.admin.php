<?php

if ( !class_exists( 'WPC_FW_Admin' ) ) {

    class WPC_FW_Admin extends WPC_FW_Admin_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->fw_common_construct();
            $this->fw_admin_common_construct();

            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'add_admin_submenu' ) );

            add_action( 'admin_enqueue_scripts', array( &$this, 'load_css_js' ), 100 );

            //add template email tab
            add_filter( 'wpc_client_templates_emails_array', array( &$this, 'add_template_tab' ) );

            add_filter( 'wpc_client_templates_emails_tags_array', array( &$this, 'add_template_tags' ) );

            //add subsubmenu
            add_filter( 'wpc_client_add_subsubmenu', array( &$this, 'add_subsubmenu' ) );

            //add array help
            add_filter( 'wpc_set_array_help', array( &$this, 'wpc_set_array_help' ), 10, 2 );

            add_filter( 'wpc_screen_options_pagination', array( &$this, 'screen_options_pagination' ), 10 );

            //add screen options for client Page
            add_action( 'admin_head', array( &$this, 'add_screen_options' ), 5 );
        }

        function add_screen_options() {

            if ( isset( $_GET['page'] ) && 'wpclients_feedback_wizard' == $_GET['page'] ) {
                if ( !isset( $_GET['tab'] ) ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Feedback Wizards', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_fw_wizards_per_page'
                        )
                    );
                } elseif ( 'items' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_fw_items_per_page'
                        )
                    );
                } elseif ( 'results' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Results', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_fw_results_per_page'
                        )
                    );
                } elseif ( 'feedback_type' == $_GET['tab'] ) {
                    add_screen_option(
                        'per_page',
                        array(
                            'label' => __( 'Feedback Types', WPC_CLIENT_TEXT_DOMAIN ),
                            'default' => WPC()->admin()->list_table_per_page,
                            'option' => 'wpc_fw_types_per_page'
                        )
                    );
                }
            }
        }


        function screen_options_pagination( $wpc_screen_options ) {

            $wpc_screen_options = array_merge( $wpc_screen_options, array(
                'wpc_fw_wizards_per_page',
                'wpc_fw_items_per_page',
                'wpc_fw_results_per_page',
                'wpc_fw_types_per_page',
            ) );

            return $wpc_screen_options;
        }


        function wpc_set_array_help( $array_help, $method ) {
            switch( $method ) {

                case '_add_wpclients_feedback_wizard_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'The Feedback Wizard is essentially a unique, professional, secure & efficient method whereby the administrator of the site can bundle together a specific set of images, documents, files or links - and effectively present to a %s a simple and easy to follow process that allows them to provide formalized and focused feedback. You can view, edit, assign, and delete the existing Feedback Wizards from this page.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break;

                case '_add_wpclients_feedback_wizardcreate_wizard_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'Once you have your items and feedback types setup, you can create a new Feedback Wizard on this page. Simply drag-n-drop the items you would like to add to the Wizard, choose the desired feedback type, and optionally set the version number.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break ;

                case '_add_wpclients_feedback_wizarditems_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'You can view a list of existing Feedback Wizard items from this page. From here you can edit and delete any existing items in your installation.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break ;

                case '_add_wpclients_feedback_wizardadd_item_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . __( 'A Feedback Wizard is a collection items, so you will want to add items from this page. You can choose from images, PDFs, or attachments. Choose a item name, add an optional description, and then upload the appropriate file.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break ;

                case '_add_wpclients_feedback_wizardresults_page_help' :
                    $array_help = array(
                        'tabs' =>
                            array(
                                array(
                                    'id' => 'dr-main',
                                    'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                    'content' => '<p>' . sprintf( __( 'From this tab you can view the results of previously created Feedback Wizards. This is where you can find the actual feedback from %s for their assigned Feedback Wizards. You can optionally filter the results by the assigned %s, to narrow down the list.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], WPC()->custom_titles['client']['s'] ) . '</p>' .
                                        '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                ),
                            ),
                            'sidebar' => '',
                            'clear' => true
                    ) ;
                break ;

                case '_add_wpclients_feedback_wizardfeedback_type_page_help' :
                    if ( isset( $_GET['add'] ) && 1 == $_GET['add'] ) {
                        $array_help = array(
                            'tabs' =>
                                array(
                                    array(
                                        'id' => 'dr-main',
                                        'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                        'content' => '<p>' . __( 'From this page you will be able to add a new feedback type. This will include naming the feedback type, providing an optional description, and setting the actual "type". You can choose from buttons, radio buttons, checkboxes, and a select box. You will then want to fill out each individual option, and optionally set a default value.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                    ),
                                ),
                                'sidebar' => '',
                                'clear' => true
                        ) ;
                    } else {
                        $array_help = array(
                            'tabs' =>
                                array(
                                    array(
                                        'id' => 'dr-main',
                                        'title' => __( 'MAIN', WPC_CLIENT_TEXT_DOMAIN ),
                                        'content' => '<p>' . __( 'You can view the current existing feedback types from this page. You can choose to edit or delete your existing feedback types, or choose "Add New Feedback Type" to create a new one.', WPC_CLIENT_TEXT_DOMAIN ) . '</p>' .
                                            '<p><a href="https://support.webportalhq.com/support/solutions/articles/1000011816-extensions" target="_blank">' . __( 'Extensions Basics', WPC_CLIENT_TEXT_DOMAIN ) . '</a></p>',
                                    ),
                                ),
                                'sidebar' => '',
                                'clear' => true
                        ) ;
                    }
                break ;
            }
            return $array_help;
        }


        /*
        * Add subsubmenu
        */
        function add_subsubmenu( $subsubmenu ) {
            $admin = current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' );
            $add_items = array(
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_feedback_wizard',
                    'menu_title'        => __( 'Create Wizard', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => $admin || current_user_can( 'wpc_modify_feedback_wizards' ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_feedback_wizard&tab=create_wizard',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_feedback_wizard',
                    'menu_title'        => __( 'Items', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => $admin || current_user_can( 'wpc_modify_feedback_items' ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_feedback_wizard&tab=items',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_feedback_wizard',
                    'menu_title'        => __( 'Add Item', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => $admin || current_user_can( 'wpc_modify_feedback_items' ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_feedback_wizard&tab=add_item',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_feedback_wizard',
                    'menu_title'        => __( 'Results', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => $admin || current_user_can( 'wpc_show_feedback_results' ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_feedback_wizard&tab=results',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_feedback_wizard',
                    'menu_title'        => __( 'Feedback Type', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => $admin ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_feedback_wizard&tab=feedback_type',
                    ),
            );

            $subsubmenu = array_merge( $subsubmenu, $add_items );

            return $subsubmenu;
        }


        /*
        * Add template tab
        */
        function add_template_tab( $wpc_emails_array ) {
            $wpc_emails_array['wizard_notify'] = array(
                'tab_label'             => __( 'FW: Notification', WPC_CLIENT_TEXT_DOMAIN ),
                'label'                 => sprintf( __( 'Feedback Wizard: Send %s Notification', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] ),
                'description'           => sprintf( __( '  >> This email will be sent %s when Admin click on "Send Email to %s" on Wizards page.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'],WPC()->custom_titles['client']['p'] ),
                'subject_description'   => __( '{wizard_name} will not be changed as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
                'body_description'      => __( '{user_name} and {wizard_url} will not be change as these placeholders will be used in the email.', WPC_CLIENT_TEXT_DOMAIN ),
                'tags'                  => 'client_recipient admin feedback_wizard'
            );

            return $wpc_emails_array;
        }


        /**
         * Add template tags
         *
         * @param array $wpc_email_tags_array notifications tags
         * @return array
         */
        function add_template_tags( $wpc_email_tags_array ) {
            $wpc_email_tags_array = array_merge( $wpc_email_tags_array, array(
                'feedback_wizard'   => __( 'Feedback Wizard', WPC_CLIENT_TEXT_DOMAIN ),
            ) );

            return $wpc_email_tags_array;
        }


        /*
        * Function for adding admin submenu
        */
        function add_admin_submenu( $plugin_submenus ) {

            if ( current_user_can( 'wpc_admin' ) && !current_user_can( 'administrator' ) ) {
                $cap = "wpc_admin";
            } else if ( current_user_can( 'wpc_manager' ) && !current_user_can( 'administrator' ) && ( current_user_can( 'wpc_modify_feedback_wizards' ) || current_user_can( 'wpc_modify_feedback_items' ) || current_user_can( 'wpc_show_feedback_results' ) ) ) {
                $cap = "wpc_manager";
            } else {
                $cap = "manage_options";
            }


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

            $plugin_submenus['wpclients_feedback_wizard'] = array(
                'page_title'        => __( 'Feedback Wizard', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Feedback Wizard', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_feedback_wizard',
                'capability'        => $cap,
                'function'          => array( &$this, 'feedback_wizard_pages' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 110,
            );

            return $plugin_submenus;
        }


        /*
        * display Feedback Wizard page
        */
        function feedback_wizard_pages() {
            if ( !isset( $_GET['tab'] ) || 'wizards' == $_GET['tab'] )
                include_once $this->extension_dir . 'includes/admin/fw_wizards.php';
            elseif ( isset( $_GET['tab'] ) && 'view_result' == $_GET['tab'] )
                include_once $this->extension_dir . 'includes/admin/fw_view_result.php';
            elseif ( isset( $_GET['tab'] ) && ( 'create_wizard' == $_GET['tab'] || 'edit_wizard' == $_GET['tab'] ) )
                include_once $this->extension_dir . 'includes/admin/fw_wizard_edit.php';
            elseif ( isset( $_GET['tab'] ) && ( 'add_item' == $_GET['tab'] || 'edit_item' == $_GET['tab'] ) )
                include_once $this->extension_dir . 'includes/admin/fw_item_edit.php';
            elseif ( isset( $_GET['tab'] ) && ( 'items' == $_GET['tab'] ) )
                include_once $this->extension_dir . 'includes/admin/fw_items.php';
            elseif ( isset( $_GET['tab'] ) && ( 'feedback_type' == $_GET['tab'] && ( isset( $_GET['add'] ) || isset( $_GET['edit'] ) ) ) )
                include_once $this->extension_dir . 'includes/admin/fw_type_edit.php';
            elseif ( isset( $_GET['tab'] ) && ( 'feedback_type' == $_GET['tab'] ) )
                include_once $this->extension_dir . 'includes/admin/fw_type.php';
            elseif ( isset( $_GET['tab'] ) && ( 'results' == $_GET['tab'] ) )
                include_once $this->extension_dir . 'includes/admin/fw_results.php';
        }


        /**
         * Gen tabs manu
         */
        function gen_feedback_tabs_menu() {
            $admin = current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' );

            $tabs = '<h2 class="nav-tab-wrapper wpc-nav-tab-wrapper">';

            if ( $admin || current_user_can( 'wpc_modify_feedback_wizards' ) ) {
                $active = ( !isset( $_GET['tab'] ) || 'wizards' == $_GET['tab'] || 'create_wizard' == $_GET['tab'] || 'edit_wizard' == $_GET['tab'] ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_feedback_wizard" class="nav-tab ' . $active . '">' .  __( 'Feedback Wizards', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
            
            if ( $admin || current_user_can( 'wpc_modify_feedback_items' ) ) {
                $active = ( isset( $_GET['tab'] ) && ( 'items' == $_GET['tab'] || 'add_item' == $_GET['tab'] || 'edit_item' == $_GET['tab'] ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_feedback_wizard&tab=items" class="nav-tab ' . $active . '">' .  __( 'Items', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
            
            if ( $admin || current_user_can( 'wpc_show_feedback_results' ) ) {
                $active = ( isset( $_GET['tab'] ) && ( 'results' == $_GET['tab'] || 'view_result' == $_GET['tab'] ) ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_feedback_wizard&tab=results" class="nav-tab ' . $active . '">' .  __( 'Results', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
        
            if ( $admin ) {
                $active = ( isset( $_GET['tab'] ) && 'feedback_type' == $_GET['tab'] ) ? 'nav-tab-active' : '';
                $tabs .= '<a href="admin.php?page=wpclients_feedback_wizard&tab=feedback_type" class="nav-tab ' . $active . '">' .  __( 'Feedback Type', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
            }
            
            return $tabs . '</h2>';
        }


        /**
         * create Thumbnail for item
         */
        function create_thumbnail( $target_path, $new_file_name, $thumbnail_width ) {

            $file_info = getimagesize( $target_path . $new_file_name );

            //load image and
            switch( $file_info[2] ) {
                //gif
                case 1:
                    $img = imagecreatefromgif( $target_path . $new_file_name );
                    break;
                //jpg
                case 2:
                    $img = imagecreatefromjpeg( $target_path . $new_file_name );
                    break;
                //png
                case 3:
                    $img = imagecreatefrompng( $target_path . $new_file_name );
                    break;

            }

            //get image size
            $width = imagesx( $img );
            $height = imagesy( $img );

            //calculate thumbnail size
            $new_width = $thumbnail_width;
            $new_height = floor( $height * ( $thumbnail_width / $width ) );

            //create a new temporary image
            $tmp_img = imagecreatetruecolor( $new_width, $new_height );


            //for PNG
            if( 3 == $file_info[2] ) {
                imagealphablending( $tmp_img, false );
                imagesavealpha( $tmp_img,true );
                $transparent = imagecolorallocatealpha( $tmp_img, 255, 255, 255, 127 );
                imagefilledrectangle( $tmp_img, 0, 0, $new_width, $new_height, $transparent );
            }


            //copy and resize old image into new image
            imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

            //for GIF
            if( 1 == $file_info[2] ) {
                $color = imagecolorallocate( $tmp_img, 0, 0, 0 );
                imagecolortransparent( $tmp_img, $color );
            }



            //save thumbnail into a file
            switch( $file_info[2] ) {
                //gif
                case 1:
                    imagegif( $tmp_img, $target_path . 't_' . $new_file_name );
                    imagedestroy($tmp_img);
                    break;
                //jpg
                case 2:
                    imagejpeg( $tmp_img, $target_path . 't_' . $new_file_name );
                    imagedestroy($tmp_img);
                    break;
                //png
                case 3:
                    imagepng( $tmp_img, $target_path . 't_' . $new_file_name );
                    imagedestroy($tmp_img);
                    break;
            }
        }


         /**
         * Load css and js
         */
        function load_css_js() {
            if ( isset( $_GET['page'] ) && 'wpclients_feedback_wizard' == $_GET['page'] ) {

            }
        }
        
        
        /**
         * Redirect to available page after open disabled page
         */
        function redirect_available_page() {
            if ( current_user_can( 'wpc_modify_feedback_wizards' ) ) {
                $adress = 'admin.php?page=wpclients_feedback_wizard';
            } else if ( current_user_can( 'wpc_modify_feedback_items' ) ) {
                $adress = 'admin.php?page=wpclients_feedback_wizard&tab=items';
            } else if ( current_user_can( 'wpc_show_feedback_results' ) ) {
                $adress = 'admin.php?page=wpclients_feedback_wizard&tab=results';
            } else {
                $adress = 'admin.php?page=wpclient_clients';
            }
            
            WPC()->redirect( get_admin_url() . $adress );
        }

    //end class
    }
}