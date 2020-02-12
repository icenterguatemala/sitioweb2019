<?php

if ( !class_exists( 'WPC_PPT_Admin_Common' ) ) {

    class WPC_PPT_Admin_Common extends WPC_PPT_Common {

        /**
        * PHP 5 constructor
        **/
        function ppt_admin_common_construct() {

            //add ez hub settings
            add_filter( 'wpc_client_ez_hub_private_post_types', array( &$this, 'add_ez_hub_settings' ), 12, 4 );
            add_filter( 'wpc_client_get_ez_shortcode_private_post_types', array( &$this, 'get_ez_shortcode_private_post_types' ), 10, 2 );
            add_filter( 'wpc_client_get_shortcode_elements', array( &$this, 'get_shortcode_element' ), 10 );

            //add assign clients and circles for new category
            add_action( 'category_add_form_fields', array( &$this, 'new_add_form_fields' ) );
            //save assign clients and circles for new categiry
            add_action( 'created_category', array( &$this, 'create_assignment' ), 15, 2 );

            //add assign clients and circles for edit category
            add_action( 'edit_category_form_fields', array( &$this, 'edit_add_form_fields' ) );
            //save assign clients and circles for edit categiry
            add_action( 'edited_category', array( &$this, 'update_assignment' ), 14, 2 );

            //add header row assign clients and circles for table category
            add_filter( 'manage_edit-category_columns', array( &$this, 'change_columns' ) );
            //add table row assign clients and circles for table category
            add_filter( 'manage_category_custom_column', array( &$this, 'add_column' ), 16, 3 );

            add_action( 'admin_enqueue_scripts', array( &$this, 'include_css_js' ), 100 );

        }


        function include_css_js() {
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if ( isset( $_GET['taxonomy'] ) && 'category' == $_GET['taxonomy'] && isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {
                wp_localize_script( 'jquery', 'wpc_assign_popup', array(
                    'wpc_ajax_prefix' => 'ppt',
                ));
            }
        }


        /*
        * Function add assign clients and circles for new categiries
        */
        function new_add_form_fields() {
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if( isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {
                wp_register_style( 'wp-client-style', WPC()->plugin_url . 'css/style.css' );
                wp_enqueue_style( 'wp-client-style', false, array(), false, true ); ?>

                <div>
                    <script type="text/javascript">
                        var site_url = '<?php echo site_url();?>';
                    </script>
                    <?php
                        $link_array = array(
                            'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                            'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                        );
                        $input_array = array(
                            'name'  => 'wpc_protected_clients',
                            'id'    => 'wpc_clients',
                            'value' => ''
                        );
                        $additional_array = array(
                            'counter_value' => 0,
                        );
                        WPC()->assigns()->assign_popup('client', 'wpc_ppt_categories', $link_array, $input_array, $additional_array );
                    ?>

                    <br />

                    <?php
                        $link_array = array(
                            'title'   => sprintf( __( 'assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                            'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                        );
                        $input_array = array(
                            'name'  => 'wpc_protected_circles',
                            'id'    => 'wpc_circles',
                            'value' => ''
                        );
                        $additional_array = array(
                            'counter_value' => 0,
                        );
                        WPC()->assigns()->assign_popup('circle', 'wpc_ppt_categories', $link_array, $input_array, $additional_array );
                    ?>

                </div>
            <?php }
        }


        /*
        * Function add assign clients and circles for edit categiries
        */
        function edit_add_form_fields() {
            global $wpdb;
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if( isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {
                wp_register_style( 'wp-client-style', WPC()->plugin_url . 'css/style.css' );
                wp_enqueue_style( 'wp-client-style', false, array(), false, true ); ?>

                    <tr class="form-field">
                        <th scope="row" valign="top"></th>
                        <td>
                            <script type="text/javascript">
                                var site_url = '<?php echo site_url();?>';
                            </script>
                            <?php
                                $clients_ids = $wpdb->get_col( $wpdb->prepare( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'client' AND object_type = 'post_category' AND object_id = %d", $_GET['tag_ID'] ) );
                                $circles_ids = $wpdb->get_col( $wpdb->prepare( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'circle' AND object_type = 'post_category' AND object_id = %d", $_GET['tag_ID'] ) );

                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] ),
                                    'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] )
                                );
                                $input_array = array(
                                    'name'  => 'wpc_protected_clients',
                                    'id'    => 'wpc_clients',
                                    'value' => implode( ',', $clients_ids )
                                );
                                $additional_array = array(
                                    'counter_value' => count( $clients_ids ),
                                );
                                WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row" valign="top"></th>
                        <td>
                            <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'assign %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] ),
                                    'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] . ' ' . WPC()->custom_titles['circle']['p'] )
                                );
                                $input_array = array(
                                    'name'  => 'wpc_protected_circles',
                                    'id'    => 'wpc_circles',
                                    'value' => implode( ',', $circles_ids )
                                );
                                $additional_array = array(
                                    'counter_value' => count( $circles_ids ),
                                );
                                WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <?php
            }
        }


        /*
        * Update assign clients and circles for edit categiry
        */
        function update_assignment( $term_id, $tt_id ) {
            global $wpdb;
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if( isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {
                $term_taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d", $term_id ) );
                if ( isset( $term_taxonomy ) && 'category' == $term_taxonomy ) {
                    $old_assign_clients = $wpdb->get_col( $wpdb->prepare( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'client' AND object_type = 'post_category' AND object_id = %d", $term_id ) );
                    if ( isset( $_POST['wpc_protected_clients'] ) ) {
                        if ( 'all' == $_POST['wpc_protected_clients'] ) {
                            $new_assign_clients = WPC()->members()->get_client_ids();
                        } else $new_assign_clients = explode( ',', $_POST['wpc_protected_clients'] );
                    } else $new_assign_clients = array();
                    $add_assign_clients = array_diff( $new_assign_clients, $old_assign_clients );
                    $del_assign_clients = array_diff( $old_assign_clients, $new_assign_clients );

                    foreach ( $add_assign_clients as $add_client ) {
                        if ( 0 != $add_client) {
                            $wpdb->query( $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns (`object_type`, `object_id`, `assign_type`, `assign_id`) VALUES ('post_category', %d, 'client', %d)", $term_id, $add_client ) );
                        }
                    }
                    foreach ( $del_assign_clients as $del_client ) {
                        if ( 0 != $del_client) {
                            $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'client' AND object_type = 'post_category' AND object_id = %d AND assign_id = %d", $term_id, $del_client ) );
                        }
                    }

                    $old_assign_circles = $wpdb->get_col( $wpdb->prepare( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'circle' AND object_type = 'post_category' AND object_id = %d", $term_id ) );
                    if ( isset( $_POST['wpc_protected_circles'] ) ) {
                        if ( 'all' == $_POST['wpc_protected_circles'] ) {
                            $new_assign_circles = WPC()->groups()->get_group_ids();
                        } else $new_assign_circles = explode( ',', $_POST['wpc_protected_circles'] );
                    } else $new_assign_circles = array();
                    $add_assign_circles = array_diff( $new_assign_circles, $old_assign_circles );
                    $del_assign_circles = array_diff( $old_assign_circles, $new_assign_circles );

                    foreach ( $add_assign_circles as $add_circle ) {
                        if ( 0 != $add_circle ) {
                            $wpdb->query( $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns (`object_type`, `object_id`, `assign_type`, `assign_id`) VALUES ('post_category', %d, 'circle', %d)", $term_id, $add_circle ) );
                        }
                    }
                    foreach ( $del_assign_circles as $del_circle ) {
                        if ( 0 != $del_circle ) {
                            $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'circle' AND object_type = 'post_category' AND object_id = %d AND assign_id = %d", $term_id, $del_circle ) );
                        }
                    }
                }

            }
        }


        /*
        * Create assign clients and circles for edit categiry
        */
        function create_assignment( $term_id, $tt_id ) {
            global $wpdb;
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if( isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {
                $term_taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d", $term_id ) );
                if ( isset( $term_taxonomy ) && 'category' == $term_taxonomy ) {
                    if ( isset( $_POST['wpc_protected_clients'] ) ) {
                        if ( 'all' == $_POST['wpc_protected_clients'] ) {
                            $add_assign_clients = WPC()->members()->get_client_ids();
                        } else  $add_assign_clients = explode( ',', $_POST['wpc_protected_clients'] );
                    } else $add_assign_clients = array();

                    foreach ( $add_assign_clients as $add_client ) {
                        if ( 0 != $add_client) {
                            $wpdb->query( $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns (`object_type`, `object_id`, `assign_type`, `assign_id`) VALUES ('post_category', %d, 'client', %d)", $term_id, $add_client ) );
                        }
                    }
                    if ( isset( $_POST['wpc_protected_circles'] ) ) {
                        if ( 'all' == $_POST['wpc_protected_circles'] ) {
                            $add_assign_circles = WPC()->groups()->get_group_ids();
                        } else $add_assign_circles = explode( ',', $_POST['wpc_protected_circles'] );
                    } else $add_assign_circles = array();

                    foreach ( $add_assign_circles as $add_circle ) {
                        if ( 0 != $add_circle ) {
                            $wpdb->query( $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpc_client_objects_assigns (`object_type`, `object_id`, `assign_type`, `assign_id`) VALUES ('post_category', %d, 'circle', %d)", $term_id, $add_circle ) );
                        }
                    }
                }
            }
        }


       /*
        * Add assign clients and circles for table categiries
        */
        function change_columns( $columns ) {
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if( isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {

                $new_columns = array (
                  'assign_clients' => WPC()->custom_titles['client']['p'],
                  'assign_circles' => WPC()->custom_titles['client']['p'] . ' ' . WPC()->custom_titles['circle']['p'],
                );
                $columns = array_merge( $columns, $new_columns );

                return $columns;
            }
            else return $columns;
        }


       /*
        * Add assign clients and circles for table categiries
        */
        function add_column( $content, $column_name, $tag_term_id  ) {
            global $wpdb;
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            if( isset( $wpc_private_post_types['types']['post'] ) && $wpc_private_post_types['types']['post'] ) {
                if ( 'assign_clients' == $column_name ) {
                    $id_array = $wpdb->get_col( $wpdb->prepare( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'client' AND object_type = 'post_category' AND object_id = %d", $tag_term_id) );

                    $link_array = array(
                        'data-id' => $tag_term_id,
                        'data-ajax' => 1,
                        'title'   => sprintf( __( "Assign %s to '%s'", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'], get_category($tag_term_id)->name )
                    );
                    $input_array = array(
                        'name'  => 'wpc_clients_ajax[]',
                        'id'    => 'wpc_clients_' . $tag_term_id,
                        'value' => implode( ',', $id_array )
                    );
                    $additional_array = array(
                        'counter_value' => count( $id_array ),
                    );
                    WPC()->assigns()->assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                    ?>
                   <?php
                } elseif ( 'assign_circles' == $column_name ) {
                    $id_array = $wpdb->get_col( $wpdb->prepare( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE assign_type = 'circle' AND object_type = 'post_category' AND object_id = %d", $tag_term_id) );

                    $link_array = array(
                        'data-id' => $tag_term_id,
                        'data-ajax' => 1,
                        'title'   => sprintf( __( 'Assign %s to %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['p'] . ' ' . WPC()->custom_titles['circle']['p'], get_category($tag_term_id)->name )
                    );
                    $input_array = array(
                        'name'  => 'wpc_circles_ajax[]',
                        'id'    => 'wpc_circles_' . $tag_term_id,
                        'value' => implode( ',', $id_array )
                    );
                    $additional_array = array(
                        'counter_value' => count( $id_array ),
                    );
                    WPC()->assigns()->assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                }
            }
        }


        /*
        * Add ez hub settings
        */
        function add_ez_hub_settings( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {
            $wpc_private_post_types = WPC()->get_settings( 'private_post_types' );
            $post_types         = get_post_types();
            $title = __( 'Private Post Type List', WPC_CLIENT_TEXT_DOMAIN ) ;
            $text_copy = '{private_post_types_' . $item_number . '}' ;

            ob_start(); ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="private_post_types_text_<?php echo $item_number ?>"><?php _e( 'Text: "Private Post Type List"',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][private_post_types][text]" id="private_post_types_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Private Post Type List', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo WPC()->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="width:250px;">
                                    <label for="private_post_types_type_<?php echo $item_number ?>"><?php _e( 'Post Type Filter', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][private_post_types][type]" id="private_post_types_type_<?php echo $item_number ?>">
                                        <option value="" >---</option>
                                        <?php if ( is_array( $wpc_private_post_types['types'] ) ) {
                                            foreach( $post_types as $key => $value ) {
                                                if ( isset( $wpc_private_post_types['types'][$key] ) && ( 1 == $wpc_private_post_types['types'][$key] || 'yes' == $wpc_private_post_types['types'][$key] ) ) { ?>
                                                    <option value="<?php echo $key ?>" <?php echo ( isset( $hub_settings['type'] ) && $hub_settings['type'] == $key ) ? 'selected="selected"' : '' ?>><?php echo $value ?></option>
                                                <?php }
                                            }
                                        } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="private_post_types_sort_type_<?php echo $item_number ?>"><?php _e( 'Sort By', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][private_post_types][sort_type]" id="private_post_types_sort_type_<?php echo $item_number ?>">
                                        <option value="date" <?php echo ( !isset( $hub_settings['sort_type'] ) || 'date' == $hub_settings['sort_type'] ) ? 'selected' : '' ?>><?php _e( 'Date', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="title" <?php echo ( isset( $hub_settings['sort_type'] ) && 'title' == $hub_settings['sort_type'] ) ? 'selected' : '' ?>><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="private_post_types_sort_<?php echo $item_number ?>"><?php _e( 'Sort', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][private_post_types][sort]" id="private_post_types_sort_<?php echo $item_number ?>">
                                        <option value="asc" <?php echo ( !isset( $hub_settings['sort'] ) || 'asc' == $hub_settings['sort'] ) ? 'selected' : '' ?>><?php _e( 'ASC', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="desc" <?php echo ( isset( $hub_settings['sort'] ) && 'desc' == $hub_settings['sort'] ) ? 'selected' : '' ?>><?php _e( 'DESC', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <?php $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez shortcode
        */
        function get_ez_shortcode_private_post_types( $tabs_items, $hub_settings = array() ) {

            $temp_arr = array();
            $temp_arr['menu_items']['post_types_sort'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            if ( isset( $hub_settings['type'] ) && '' != $hub_settings['type'] ) {
                $attrs .= ' private_post_types="' . $hub_settings['type'] . '" ';
            } else {
                $attrs .= ' private_post_types="" ';
            }

            if ( !empty( $hub_settings['term_ids'] ) ) {
                $attrs .= ' term_ids="' . $hub_settings['term_ids'] . '" ';
            } else {
                $attrs .= ' term_ids="" ';
            }

            if ( isset( $hub_settings['sort_type'] ) && '' != $hub_settings['sort_type'] ) {
                $attrs .= ' sort_type="' . $hub_settings['sort_type'] . '" ';
            } else {
                $attrs .= ' sort_type="date" ';
            }

            if ( isset( $hub_settings['sort'] ) && '' != $hub_settings['sort'] ) {
                $attrs .= ' sort="' . $hub_settings['sort'] . '" ';
            } else {
                $attrs .= ' sort="asc" ';
            }

            $temp_arr['page_body'] = '[wpc_client_private_post_types ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * get shortcode element
        */
        function get_shortcode_element( $elements ) {
            $elements['private_post_types'] = __( 'Private Post Type List', WPC_CLIENT_TEXT_DOMAIN );
            return $elements;
        }


        //end class
    }

}

