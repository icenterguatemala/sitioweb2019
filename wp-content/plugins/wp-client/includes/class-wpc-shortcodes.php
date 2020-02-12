<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( "WPC_Shortcodes" ) ) :

class WPC_Shortcodes {

    /**
     * The single instance of the class.
     *
     * @var WPC_Shortcodes
     * @since 4.5
     */
    protected static $_instance = null;

    private $first_time_activity_show = true;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Shortcodes is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Shortcodes - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

    }

    public function shortcode_atts( $atts, $shortcode ) {
        WPC()->set_shortcode_data();

        if ( empty( WPC()->shortcode_data[ $shortcode ]['attributes'] ) )
            return array();

        $out = is_array( $atts ) ? $atts : array();
        foreach( WPC()->shortcode_data[ $shortcode ]['attributes'] as $key=>$attr_data ) {
            if( !isset( $out[ $key ] ) ) {
                $out[ $key ] = isset( $attr_data['value'] ) ? $attr_data['value'] : '';
            }
        }

        return $out;
    }

    function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
        $shortcodes[] = 'wpc_client_private';
        return $shortcodes;
    }

    function us_register_shortcode() {

        WPC()->set_shortcode_data();

        foreach( WPC()->shortcode_data as $key => $data ) {
            if( !empty( $data['callback'] ) && is_callable( $data['callback'] ) ) {
                add_shortcode( $key, $data['callback'] );
            }
        }

        add_filter( 'no_texturize_shortcodes', array( $this, 'shortcodes_to_exempt_from_wptexturize' ) );

    }


    /*
    * display user info
    */
    function shortcode_user_info( $attrs ) {

        if( !is_user_logged_in() ) {
            return '';
        }

        if( !isset( $attrs['field' ] ) || empty( $attrs['field' ] ) ) {
            return '';
        }

        $field = trim( $attrs['field' ] );


        global $current_user;

        if ( $current_user->get( $field ) != '' ) {
            return $current_user->get( $field );
        }


        $user_id = get_current_user_id();

        $field_value = get_user_meta( $user_id, $field, true );

        if ( false !== $field_value && '' != $field_value ) {
            return $field_value;
        }

        if( isset( $attrs['no_value' ] ) && !empty($attrs['no_value' ] ) ) {
            return $attrs['no_value' ];
        }

        return '';


    }


    function shortcode_avatar_preview( $attrs, $contents ) {
        return WPC()->get_template( 'user_avatar.php', '', array(
            'size' => !empty( $attrs['size'] ) ? $attrs['size'] : '128px'
        ) );
    }


    function shortcode_user_activity_alert( $attrs ) {
        global $wpdb;

        if( !is_user_logged_in() ) {
            return '';
        }

        if( $this->first_time_activity_show ) {
            $this->first_time_activity_show = false;
        } else {
            return '';
        }
        if( current_user_can('wpc_client_staff') ) {
            $user_id = get_user_meta( get_current_user_id(), 'parent_client_id', true );
        } else {
            $user_id = get_current_user_id();
        }
        $last_activity_date = get_user_meta( get_current_user_id(), 'wpc_last_activity_date', true );
        $last_activity_timestamp = $last_activity_date ? strtotime( $last_activity_date ) : 0;

        $file_ids = array();
        //Files from categories with clients access
        $client_file_caregories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'client', $user_id );
        $client_file_caregories = " f.cat_id IN('" . implode( "','", $client_file_caregories ) . "')";

        $results = $wpdb->get_col(
            "SELECT f.id
            FROM {$wpdb->prefix}wpc_client_files f
            WHERE " . $client_file_caregories
        );

        if ( 0 < count( $results ) )
            $file_ids = array_merge( $file_ids, $results );


        //Files with clients access
        $client_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'client', $user_id );

        if ( 0 < count( $client_files ) )
            $file_ids = array_merge( $file_ids, $client_files );

        $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

        if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
            foreach( $client_groups_id as $group_id ) {
                //Files in categories with group access
                $group_file_caregories = WPC()->assigns()->get_assign_data_by_assign( 'file_category', 'circle', $group_id );
                $group_file_caregories = " f.cat_id IN('" . implode( "','", $group_file_caregories ) . "')";

                $results = $wpdb->get_col(
                    "SELECT f.id
                    FROM {$wpdb->prefix}wpc_client_files f
                    WHERE " . $group_file_caregories
                );

                if ( 0 < count( $results ) )
                    $file_ids = array_merge( $file_ids, $results );

                //Files with group access
                $group_files = WPC()->assigns()->get_assign_data_by_assign( 'file', 'circle', $group_id );

                if ( 0 < count( $results ) )
                    $file_ids = array_merge( $file_ids, $group_files );
            }
        }

        $file_ids = array_unique( $file_ids );

        if ( 0 < count( $file_ids ) ) {
            $count_files = $wpdb->get_var( "SELECT COUNT(f.id)
                FROM {$wpdb->prefix}wpc_client_files f
                WHERE f.time >= $last_activity_timestamp AND f.id IN ('" . implode( "','", $file_ids ) . "')" );
        } else {
            $count_files = 0;
        }

        $user_id = get_current_user_id();
        //get markers for chains with new messages
        $client_new_messages = WPC()->assigns()->get_assign_data_by_assign( 'new_message', 'client', $user_id );

        $messages = $wpdb->get_row( $wpdb->prepare(
            "SELECT COUNT(id) AS messages_count,
                    COUNT(DISTINCT(author_id)) AS authors_count
            FROM {$wpdb->prefix}wpc_client_messages
            WHERE id IN('" . implode( "','", $client_new_messages ) . "') AND
                date >= %d",
            $last_activity_timestamp
        ), ARRAY_A );

        if( !( isset( $messages['messages_count'] ) && !empty( $messages['messages_count'] ) ) && !$count_files ) {
            return '';
        }

        wp_enqueue_script( 'wpc-user-activity-alert-js', false, array(), WPC_CLIENT_VER, true );

        wp_localize_script( 'wpc-user-activity-alert-js', 'wpc_activity', array(
            'admin_url' => admin_url(),
            'security' => wp_create_nonce( get_current_user_id() . SECURE_AUTH_SALT . $last_activity_date )
        ));

        $offset_x = 0;
        $offset_y = 0;
        if( isset( $attrs['offset_x'] ) ) {
            $offset_x = is_numeric( $attrs['offset_x'] ) ? $attrs['offset_x'] . 'px' : $attrs['offset_x'];
        }
        if( isset( $attrs['offset_y'] ) ) {
            $offset_y = is_numeric( $attrs['offset_y'] ) ? $attrs['offset_y'] . 'px' : $attrs['offset_y'];
        }
        $offset = array(
            'top' => $offset_y,
            'bottom' => $offset_y,
            'left' => $offset_x,
            'right' => $offset_x
        );

        $style = "bottom: {$offset['bottom']}px; right: {$offset['right']}px;";
        if( !empty( $attrs['position'] ) ) {
            $pos_array = explode( ' ', $attrs['position'] );
            if( count( $pos_array ) == count( array_intersect( $pos_array, array( 'top', 'right', 'bottom', 'left' ) ) ) ) {
                $style = "{$pos_array[0]}: {$offset[ $pos_array[0] ]}; {$pos_array[1]}: {$offset[ $pos_array[1] ]};";
            }
        }
        $title = isset( $attrs['title'] ) ? $attrs['title'] : __( 'Hub Activity', WPC_CLIENT_TEXT_DOMAIN );

        $text_advise = isset( $attrs['text_advise'] ) ? $attrs['text_advise'] : __( 'Please advise, you have:', WPC_CLIENT_TEXT_DOMAIN );

        ob_start();
        ?>
        <div id="wpc_activity_popup" style="<?php echo $style; ?>">
            <div class="wpc_activity_title"><?php echo $title; ?></div>
            <div class="wpc_close_button"></div>
            <div id="wpc_activity_message">
                <?php echo $text_advise; ?><br />
                <?php if( $count_files ) {
                    printf( __( '%d new file(s)', WPC_CLIENT_TEXT_DOMAIN ), $count_files );
                    ?>
                    <br />
                <?php }
                if ( isset( $messages['messages_count'] ) && !empty( $messages['messages_count'] ) && isset( $messages['authors_count'] ) && !empty( $messages['authors_count'] ) ) {
                    printf( __( '%d new message(s) from %d user(s)', WPC_CLIENT_TEXT_DOMAIN ), $messages['messages_count'], $messages['authors_count'] );
                } ?>
            </div>
        </div>
        <?php
        $content = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $content;
    }


    /**
     * Function for shortcode which display custom field on forms
     */
    function shortcode_custom_field( $atts, $contents = null ) {
        global $post;
        $custom_field_html = '';
        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

        if( !isset( $wpc_custom_fields[ $atts['name'] ] ) ) return '';

        $current_custom_field = $wpc_custom_fields[ $atts['name'] ];
        $current_custom_field['name'] = $atts['name'];

        $wpc_pages = WPC()->get_settings( 'pages' );
        $client_id = WPC()->members()->get_client_id();

        $current_role = 'client';
        if ( 0 < $client_id && current_user_can( 'wpc_client_staff' ) ) {
            $current_role = 'staff';
        }

        //get custom field value
        if ( 0 < $client_id ) {
            $cf_value = get_user_meta( $client_id, $atts['name'], true );
        }

        $disabled_field = '';
        $form = '';

        if( isset( $wpc_pages['client_registration_page_id'] ) && !empty( $post ) && $wpc_pages['client_registration_page_id'] == $post->ID ) {
            $form = 'user_add_client';
        } elseif( isset( $wpc_pages['profile_page_id'] ) && !empty( $post ) && $wpc_pages['profile_page_id'] == $post->ID ) {
            $form = 'user_edit_client';
        } elseif ( is_admin() ) {
            $form = 'admin_area';
            if ( isset( $current_custom_field['type'] ) && 'hidden' === $current_custom_field['type'] ) {
                $current_custom_field['type'] = 'text';
            }
        }

        if( empty( $current_custom_field ) ) return '';

        $field = WPC()->custom_fields()->render_custom_field( $current_custom_field, $form, $client_id );
        if( empty( $field ) ) return '';

        $custom_field_html = $field['type'] == 'hidden' ? $field['field'] :
            '<div class="wpc_form_line">
                    <div class="wpc_form_label">' . $field['label'] . '</div>
                    <div class="wpc_form_field">' .
            $field['field'] .
            '<span class="wpc_description">' . $field['description'] .'</span>' .
            '</div>
                </div>';

        return $custom_field_html;
    }


    /**
     * Function for shortcode which display custom field value
     */
    function shortcode_custom_field_value( $atts, $contents = null ) {
        $custom_field_html = '';
        $current_custom_field = '';

        if( !is_user_logged_in() ) return '';

        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
        if( !isset( $wpc_custom_fields[ $atts['name'] ] ) ) return '';

        $user_id = get_current_user_id();
        $cf_value = get_user_meta( $user_id, $atts['name'], true );
        $metadata_exists = metadata_exists('user', $user_id, $atts['name'] );

        if( $wpc_custom_fields[$atts['name']]['type'] != 'hidden' ) {
            $current_custom_field = $wpc_custom_fields[$atts['name']];
        }
        $current_custom_field['name'] = $atts['name'];
        $custom_field_html = WPC()->custom_fields()->render_custom_field_value( $current_custom_field, array(
            'user_id' => $user_id,
            'value' => $cf_value,
            'metadata_exists' => $metadata_exists,
            'empty_value' => isset( $atts['no_value'] ) ? $atts['no_value'] : __( 'None', WPC_CLIENT_TEXT_DOMAIN ),
            'atts' => $atts
        ) );
        return $custom_field_html;
    }


    /*
    * Shortcode
    */
    function shortcode_client_profile( $atts, $contents = null ) {
        global $wpdb;

	    $no_redirect = false;
        $data = array();
        $message = '';
        $message_class = 'wpc_error';
        $localize_array = array(
            'texts' => array(
                'fill_field'             => __( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ),
            )
        );

        $user_id = WPC()->checking_page_access();

	    /*our_hook_
			hook_name: wp_client_shortcodes_no_redirect
			hook_title: Builders Compatibility - NoRedirect
			hook_description: Hook runs before redirect condition.
			hook_type: filter
			hook_in: wp-client
			hook_location class.ajax.php
			hook_param: boolean $no_redirect
			hook_since: 4.5.4.1
		*/
	    $no_redirect = apply_filters( 'wp_client_shortcodes_no_redirect', $no_redirect );

        if ( ! $no_redirect && ( !current_user_can( 'wpc_client' ) || current_user_can( 'manage_network_options' ) ) ) {
            //fix for staff role to redirect Staff profile if it exist
            if ( current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) && WPC()->get_slug( 'staff_profile_page_id' ) ) {
                WPC()->redirect( WPC()->get_slug( 'staff_profile_page_id' ) );
            }

            WPC()->redirect( get_home_url() );
        }

        if ( ! $no_redirect && !current_user_can( 'wpc_view_profile' ) && !current_user_can( 'wpc_modify_profile' ) ) {
            WPC()->redirect( WPC()->get_hub_link() );
        }


        WPC()->members()->password_protect_css_js( true );

        WPC()->custom_fields()->add_custom_fields_scripts();

        //client profile
        wp_enqueue_script( 'wpc_client_profile' );

        wp_localize_script( "wpc_client_profile", 'wpc_profile_var', $localize_array );

        $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_edit_client', get_current_user_id() );

        extract( $_REQUEST );
        if ( isset( $wpc_submit_profile ) && current_user_can( 'wpc_modify_profile' ) ) {
            // validate at php side
            if ( empty( $contact_name ) ) // empty username
                $message .= __('The Contact Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

            if ( empty( $contact_email ) ) // empty email
                $message .= __('The email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

            $contact_email = apply_filters( 'pre_user_email', isset( $contact_email ) ? $contact_email : '' );
            $email_exists = email_exists( $contact_email );
            if( !empty( $ID ) && $email_exists && $email_exists != $ID ) {
                // email already exist
                $message .= __( 'Sorry, email address already in use!<br/>', WPC_CLIENT_TEXT_DOMAIN );
            }

            if ( !empty( $contact_password ) && !empty( $contact_password2 ) && $contact_password != $contact_password2 ) {
                $message .= __("Sorry, Passwords are not matched! .<br/>", WPC_CLIENT_TEXT_DOMAIN);
            }

            if( isset( $custom_fields ) && count( $custom_fields ) > count( $all_custom_fields ) ) {
                exit;
            } elseif( !( isset( $custom_fields ) && count( $custom_fields ) ) ) {
                $custom_fields = array();
            }

            if( isset( $_FILES['custom_fields'] ) ) {
                $files_custom_fields = array();
                foreach( $_FILES['custom_fields'] as $key1 => $value1 )
                    foreach( $value1 as $key2 => $value2 )
                        $files_custom_fields[$key2][$key1] = $value2;

                $custom_fields = array_merge( $custom_fields, $files_custom_fields );
            }

            if( isset( $custom_fields ) && is_array( $custom_fields ) && is_array( $all_custom_fields ) ) {

                foreach( $custom_fields as $key=>$value ) {
                    if( !array_key_exists( $key, $all_custom_fields ) ) {
                        exit;
                    }
                }

                foreach( $all_custom_fields as $all_key=>$all_value ) {
                    if ( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $custom_fields ) ) {
                        $custom_fields[$all_key] = '';
                    }

                    foreach( $custom_fields as $key=>$value ) {
                        if ( 'file' == $all_value['type']  ) {
                            if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value['name'] ) {
                                $message .= sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                            }
                        } else {
                            if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value ) {
                                $message .= sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                            }
                        }
                    }
                }

            }
            if( '' == $message ) {
                $message = __( "The changes have been successfully saved.<br/>", WPC_CLIENT_TEXT_DOMAIN);
                $message_class = 'wpc_apply';
            } else {
                $message_class = 'wpc_error';
            }
        }

        $client                     = get_userdata( $user_id );
        $business_name              = get_user_meta( $user_id, 'wpc_cl_business_name', true );
        $contact_name               = ( !empty( $client ) ) ? $client->display_name : '';
        $contact_email              = ( !empty( $client ) ) ? $client->user_email : '';
        $wp_contact_phone           = get_user_meta( $user_id, $wpdb->prefix . 'contact_phone', true );

        //block 'modify_profile'
        $data['modify_profile'] = false;
        if ( current_user_can( 'wpc_modify_profile' ) ) {
            $data['modify_profile'] = true;
            $data['reset_password'] = false;
            if ( current_user_can( 'wpc_reset_password' ) ) {
                $data['reset_password'] = true;
                $data['label_contact_password'] = __( 'New Password', WPC_CLIENT_TEXT_DOMAIN ) ;
                $data['contact_password'] = ( $message ) ? esc_html( $_REQUEST['contact_password'] ) : '';
                $data['label_contact_password2'] = __( 'Confirm New Password', WPC_CLIENT_TEXT_DOMAIN ) ;
                $data['contact_password2'] = ( $message ) ? esc_html( $_REQUEST['contact_password2'] ) : '';
                $data['label_strength_indicator'] = __( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ;
                $data['label_indicator_hint'] = __( '<strong>Hint:</strong> The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like <strong>! " ? $ % ^ & )</strong>.', WPC_CLIENT_TEXT_DOMAIN ) ;
            }

            $data['text_submit'] =  __( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ;
            $data['nonce'] = wp_nonce_field( 'verify_edit_user', 'edit_nonce_field', true, false );
        }

        //block 'custom_field'
        $data['custom_fields'] = $all_custom_fields;

        $data['message_class']              = ( 'wpc_error' == $message_class ) ? 'wpc_error' : 'wpc_apply';
        $data['message']                    = $message;

        $avatar                     = get_user_meta( $user_id, 'wpc_avatar', true );

        $data['avatar_field']       = WPC()->members()->build_avatar_field( 'avatar', $avatar, $user_id );
        $data['ID']                 = $user_id;
        $data['business_name']      = ( isset( $business_name ) ) ? $business_name : '';
        $data['contact_username']   = ( isset( $client ) ) ? $client->user_login : '';
        $data['contact_name']       = ( $message ) ? esc_html( $_REQUEST['contact_name'] ) : $contact_name;
        $data['contact_email']      = ( $message ) ? esc_html( $_REQUEST['contact_email'] ) : $contact_email;
        $data['contact_phone']      = ( $message ) ? esc_html( $_REQUEST['contact_phone'] ) : $wp_contact_phone;
        $data['required_text']  = __( ' <span style="color:red;" title="This field is marked as required by the administrator.">*</span>', WPC_CLIENT_TEXT_DOMAIN );

        return WPC()->get_template( 'form/client_profile.php', '', $data );
    }

    /*
    * Shortcode
    */
    function shortcode_staff_profile( $atts, $contents = null ) {
        global $wpdb, $current_user;

	    $no_redirect = false;
        $data = array();
        $message = '';
        $message_class = 'wpc_error';
        $localize_array = array(
            'texts' => array(
                'fill_field'             => __( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ),
            )
        );

        $user_id = WPC()->checking_page_access();

	    /*our_hook_
			hook_name: wp_client_shortcodes_no_redirect
			hook_title: Builders Compatibility - NoRedirect
			hook_description: Hook runs before redirect condition.
			hook_type: filter
			hook_in: wp-client
			hook_location class.ajax.php
			hook_param: boolean $no_redirect
			hook_since: 4.5.4.1
		*/
	    $no_redirect = apply_filters( 'wp_client_shortcodes_no_redirect', $no_redirect );

        if ( ! $no_redirect && ( !current_user_can( 'wpc_client_staff' ) || current_user_can( 'manage_network_options' ) ) ) {
            WPC()->redirect( get_home_url() );
        }

        if ( ! $no_redirect && !current_user_can( 'wpc_view_profile' ) && !current_user_can( 'wpc_modify_profile' ) ) {
            WPC()->redirect( WPC()->get_hub_link() );
        }

        WPC()->members()->password_protect_css_js( true );

        WPC()->custom_fields()->add_custom_fields_scripts();

        //staff profile
        wp_enqueue_script( 'wpc_client_staff_profile' );

        wp_localize_script( "wpc_client_profile", 'wpc_profile_var', $localize_array );

        $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_profile_staff', get_current_user_id() );

        extract($_REQUEST);
        if ( isset( $wpc_submit_profile ) && current_user_can( 'wpc_modify_profile' ) ) {

            //empty email
            if ( !isset( $_REQUEST['user_data']['email'] ) )
                $message .= __( 'The email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );

            //email already exists
            $user_email = apply_filters( 'pre_user_email', isset( $_REQUEST['user_data']['email'] ) ? $_REQUEST['user_data']['email'] : '' );
            if ( email_exists( $user_email ) ) {
                if ( get_current_user_id() != get_user_by( 'email', $user_email )->ID ) {
                    // email already exist
                    $message .= __( 'Sorry, email address already in use!<br/>', WPC_CLIENT_TEXT_DOMAIN );
                }
            }

            if ( !empty( $_REQUEST['user_data']['pass1'] ) && !empty(  $_REQUEST['user_data']['pass2'] ) &&  $_REQUEST['user_data']['pass1'] !=  $_REQUEST['user_data']['pass2'] ) {
                $message .= __("Sorry, Passwords are not matched!<br/>", WPC_CLIENT_TEXT_DOMAIN);
            }

            if( isset( $custom_fields ) && count( $custom_fields ) > count( $all_custom_fields ) ) {
                exit;
            } elseif( !( isset( $custom_fields ) && count( $custom_fields ) ) ) {
                $custom_fields = array();
            }

            if( isset( $_FILES['custom_fields'] ) ) {
                $files_custom_fields = array();
                foreach( $_FILES['custom_fields'] as $key1 => $value1 )
                    foreach( $value1 as $key2 => $value2 )
                        $files_custom_fields[$key2][$key1] = $value2;

                $custom_fields = array_merge( $custom_fields, $files_custom_fields );
            }

            if( isset( $custom_fields ) && is_array( $custom_fields ) && is_array( $all_custom_fields ) ) {

                foreach( $custom_fields as $key=>$value ) {
                    if( !array_key_exists( $key, $all_custom_fields ) ) {
                        exit;
                    }
                }

                foreach( $all_custom_fields as $all_key=>$all_value ) {
                    if ( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $custom_fields ) ) {
                        $custom_fields[$all_key] = '';
                    }

                    foreach( $custom_fields as $key=>$value ) {
                        if ( 'file' == $all_value['type']  ) {
                            if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value['name'] ) {
                                $message .= sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                            }
                        } else {
                            if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value ) {
                                $message .= sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                            }
                        }
                    }
                }
            }


            if( '' == $message ) {
                $message = __( "The changes have been successfully saved.<br/>", WPC_CLIENT_TEXT_DOMAIN );
                $message_class = 'wpc_apply';
            } else {
                $message_class = 'wpc_error';
            }
        }

        //get Employee data
        if ( isset( $_REQUEST['user_data'] ) ) {
            $user_data = $_REQUEST['user_data'];
        } else {
            $staff                      = get_userdata( $current_user->ID );
            $user_data['email']         = $staff->user_email;
            $user_data['first_name']    = get_user_meta( $current_user->ID, 'first_name', true );
            $user_data['last_name']     = get_user_meta( $current_user->ID, 'last_name', true );
        }

        //block 'modify_profile'
        $data['modify_profile'] = false;
        if ( current_user_can( 'wpc_modify_profile' ) ) {
            $data['modify_profile'] = true;
            $data['reset_password'] = false;
            if ( current_user_can( 'wpc_reset_password' ) ) {
                $data['reset_password'] = true;
                $data['contact_password'] = ( $message ) ? esc_html( $_REQUEST['contact_password'] ) : '';
                $data['contact_password2'] = ( $message ) ? esc_html( $_REQUEST['contact_password2'] ) : '';
            }

            $data['nonce'] = wp_nonce_field( 'verify_edit_user', 'edit_nonce_field', true, false );
        }

        //block 'custom_field'
        $data['custom_fields'] = $all_custom_fields;

        $data['message_class']          = ( 'red' == $message_class ) ? 'message_red' : 'message_green';
        $data['message']                = $message;

        $avatar                         = get_user_meta( $current_user->ID, 'wpc_avatar', true );

        $data['avatar_field']           = WPC()->members()->build_avatar_field( 'avatar', $avatar, $current_user->ID );
        $data['staff_login']            = $current_user->user_login;
        $data['staff_email']            = ( $user_data['email'] ) ? esc_html( $user_data['email'] ) : '';
        $data['first_name']             = ( $user_data['first_name'] ) ? esc_html( $user_data['first_name'] ) : '';
        $data['last_name']              = ( $user_data['last_name'] ) ? esc_html( $user_data['last_name'] ) : '';

        return WPC()->get_template( 'form/staff_profile.php', '', $data );
    }


    /*
    * Shortcode
    */
    function shortcode_wpclients($atts, $contents = null) {

        $contents .= "<style type='text/css'>.navigation .alignleft, .navigation .alignright {display:none;}</style>";
        $args['client_id'] = WPC()->checking_page_access();

        $contents = WPC()->replace_placeholders( $contents, $args );
        $contents = do_shortcode($contents);
        $contents = WPC()->replace_placeholders( $contents, $args );

        if ( ! current_user_can( 'wpc_client') ) {
            $contents = '';
        }

        return $contents;
    }


    /*
    * Shortcode for display content only for Clients
    */
    function shortcode_wpc_client_only( $atts, $contents = null ) {

        if ( is_user_logged_in() && current_user_can( 'wpc_client' ) && !current_user_can( 'manage_network_options' ) ) {
            $args['client_id'] = get_current_user_id();
            $contents = WPC()->replace_placeholders( $contents, $args );
            $contents = do_shortcode( $contents );
            $contents = WPC()->replace_placeholders( $contents, $args );

            return $contents;
        }

        return '';
    }


    /*
    * Shortcode for display content only for Staff
    */
    function shortcode_wpc_staff_only( $atts, $contents = null ) {

        if ( is_user_logged_in() && current_user_can( 'wpc_client_staff' ) && !current_user_can( 'manage_network_options' ) ) {
            $args['client_id'] = get_current_user_id();
            $contents = WPC()->replace_placeholders( $contents, $args );
            $contents = do_shortcode($contents);
            $contents = WPC()->replace_placeholders( $contents, $args );

            return $contents;
        }

        return '';
    }


    /*
    * Shortcode for display content only for Client and Staff
    */
    function shortcode_wpc_client_and_staff_only( $atts, $contents = null ) {

        if ( is_user_logged_in() && ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_client_staff' ) ) && !current_user_can( 'manage_network_options' ) ) {
            $contents = WPC()->replace_placeholders( $contents );
            $contents = do_shortcode( $contents );
            $contents = WPC()->replace_placeholders( $contents );

            return $contents;
        }

        return '';
    }


    /**
		 * Shortcode for display content only for Admins
		 */
    function shortcode_wpc_admin_only( $atts, $contents = null ) {

        if ( is_user_logged_in() && (current_user_can( 'wpc_admin' ) || current_user_can( 'administrator' )) && !current_user_can( 'manage_network_options' ) ) {
            return do_shortcode( $contents );
        }

        return '';
    }


    /**
     * Shortcode for display content only for Managers
     */
    function shortcode_wpc_manager_only( $atts, $contents = null ) {

        if ( is_user_logged_in() && current_user_can( 'wpc_manager' ) && !current_user_can( 'manage_network_options' ) ) {
            return do_shortcode( $contents );
        }

        return '';
    }


    /*
    * Shortcode for display content only for non-logged in users
    */
    function shortcode_wpc_non_logged_in_only( $atts, $contents = null ) {

        if ( ! is_user_logged_in() ) {
            return do_shortcode( $contents );
        }

        return '';
    }

    /*
    * Shortcode for display content only for logged in users
    */
    function wpc_logged_in_only( $atts, $contents = null ) {

        if ( is_user_logged_in() ) {
            $args['client_id'] = get_current_user_id();
            $contents = WPC()->replace_placeholders( $contents, $args );
            $contents = do_shortcode( $contents );
            $contents = WPC()->replace_placeholders( $contents, $args );

            return $contents;
        }

        return '';
    }


    /*
    * Shortcode
    */
    function shortcode_private( $atts, $contents = null ) {
        global $wpdb;

        $contents = do_shortcode( $contents );

        //fix for storm
        $privacy_type = '';
        $circle_condition = '';

        extract( shortcode_atts( array(
            'for'               => '',
            'for_circle'        => '',
            'privacy_type'      => 'include',
            'circle_condition'  => 'or',
        ), $atts ) );

        if ( is_user_logged_in() ) {
            $client_id = WPC()->current_plugin_page['client_id'];

            /*if( isset( $client_id ) && !empty( $client_id ) ) {
                $current_user = get_userdata( $client_id );
            }*/

            if( current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_see_private_content' ) ) {
                return WPC()->replace_placeholders( do_shortcode( $contents ), array( 'client_id' => $client_id ) );
            }


            $client_ids = array();

            if( isset( $for ) && !empty( $for ) ) {
                if( 'all' == $for ) {
                    $res = get_users( array(
                        'role'      => 'wpc_client',
                        'fields'    => 'ids'
                    ) );
                } else {
                    $clients_array = explode( ',', $for );

                    if( !empty( $clients_array[0] ) ) {
                        if( is_numeric( $clients_array[0] ) ) {
                            $res = $wpdb->get_col(
                                "SELECT u.ID
                                FROM {$wpdb->users} u
                                WHERE u.ID IN( '" . implode( "','", $clients_array ) . "' )"
                            );

                            if( empty( $res ) ) {
                                $res = $wpdb->get_col(
                                    "SELECT u.ID
                                    FROM {$wpdb->users} u
                                    WHERE u.user_login IN( '" . implode( "','", $clients_array ) . "' )"
                                );
                            }
                        } else {
                            $res = $wpdb->get_col(
                                "SELECT u.ID
                                FROM {$wpdb->users} u
                                WHERE u.user_login IN( '" . implode( "','", $clients_array ) . "' )"
                            );
                        }
                    } else {
                        $res = $wpdb->get_col(
                            "SELECT u.ID
                            FROM {$wpdb->users} u
                            WHERE u.ID IN( '" . implode( "','", $clients_array ) . "' )"
                        );
                    }
                }

                $client_ids = array_merge( $client_ids, $res );
            }


            if( isset( $for_circle ) && !empty( $for_circle ) ) {
                $res = array();

                if( 'all' == $for_circle ) {
                    $group_ids = WPC()->groups()->get_group_ids();
                } else {
                    $circles_array = explode( ',', $for_circle );

                    if( !empty( $circles_array[0] ) ) {
                        if( is_numeric( $circles_array[0] ) ) {
                            $group_ids = $wpdb->get_col(
                                "SELECT group_id
                                FROM {$wpdb->prefix}wpc_client_groups g
                                WHERE g.group_id IN( '" . implode( "','", $circles_array ) . "' )"
                            );

                            if( empty( $group_ids ) ) {
                                $group_ids = $wpdb->get_col(
                                    "SELECT group_id
                                    FROM {$wpdb->prefix}wpc_client_groups g
                                    WHERE g.group_name IN( '" . implode( "','", $circles_array ) . "' )"
                                );
                            }
                        } else {
                            $group_ids = $wpdb->get_col(
                                "SELECT group_id
                                FROM {$wpdb->prefix}wpc_client_groups g
                                WHERE g.group_name IN( '" . implode( "','", $circles_array ) . "' )"
                            );
                        }
                    } else {
                        $group_ids = $wpdb->get_col(
                            "SELECT group_id
                            FROM {$wpdb->prefix}wpc_client_groups g
                            WHERE g.group_id IN( '" . implode( "','", $circles_array ) . "' )"
                        );
                    }
                }

                if( 'and' == $circle_condition ) {
                    //get clients which assigned to all circles from list $for_circles
                    foreach( $group_ids as $group_id ) {
                        if( empty( $res ) ) {
                            $res = WPC()->groups()->get_group_clients_id( $group_id );
                        } else {
                            $res = array_intersect( $res, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }
                    }
                } else {

                    if( is_array( $group_ids ) && count( $group_ids ) ) {
                        foreach( $group_ids as $group_id ) {
                            $res = array_merge( $res, WPC()->groups()->get_group_clients_id( $group_id ) );
                        }
                    }

                }

                $client_ids = array_merge( $client_ids, $res );
            }


            $excluded_clients  = WPC()->members()->get_excluded_clients();
            $client_ids = array_diff( $client_ids, $excluded_clients );
            $client_ids = array_unique( $client_ids );

            if( in_array( $client_id, $client_ids ) ) {
                if( $privacy_type == 'exclude' ) {
                    return '';
                } else {
                    return WPC()->replace_placeholders( do_shortcode( $contents ), array( 'client_id' => $client_id ) );
                }
            } else {
                if( $privacy_type == 'exclude' ) {
                    return WPC()->replace_placeholders( do_shortcode( $contents ), array( 'client_id' => $client_id ) );
                } else {
                    return '';
                }
            }

        }

        return '';
    }


    /*
    * Shortcode
    */
    function shortcode_theme( $atts, $contents = null) {
        $url    = WPC()->plugin_url . 'images';
        $wpc_skins = WPC()->get_settings( 'skins' );

        if( !$wpc_skins ) {
            $wpc_skins = 'light';
        }

        $url .= "/" . $wpc_skins;

        return $url;
    }


    /*
    * Shortcode
    */
    function shortcode_loginf( $atts, $contents=null ) {
        $no_redirect = false;
        if( isset( $atts['no_redirect'] ) && 'true' == $atts['no_redirect'] ) {
            $no_redirect = true;
        }

	    /*our_hook_
			hook_name: wp_client_shortcodes_no_redirect
			hook_title: Builders Compatibility - NoRedirect
			hook_description: Hook runs before redirect condition.
			hook_type: filter
			hook_in: wp-client
			hook_location class.ajax.php
			hook_param: boolean $no_redirect
			hook_since: 4.5.4.1
		*/
	    $no_redirect = apply_filters( 'wp_client_shortcodes_no_redirect', $no_redirect );



        if( !is_user_logged_in() || ( is_user_logged_in() && ( $no_redirect || ( isset( $_REQUEST['wpc_action'] ) && $_REQUEST['wpc_action'] == 'temp_password' ) ) ) ) {
            wp_enqueue_script( 'wpc_login_page' );

            $localize_array = array(
                'texts' => array(
                    'empty_terms' => __( 'Sorry, you must agree to the Terms/Conditions to continue', WPC_CLIENT_TEXT_DOMAIN ),
                )
            );

            wp_localize_script( "wpc_login_page", 'wpc_login_var', $localize_array );

            wp_enqueue_style( 'wpc_login_page' );

            do_action( 'login_enqueue_scripts' );

            global $wpdb;

            $data['login_url'] = '';
            $data['error_msg'] = '';

            $data['somefields'] = '<input type="hidden" name="wpc_login" value="login_form">';

            $data['check_invalid'] = array(
                __('<strong>ERROR</strong>: Invalid key.', WPC_CLIENT_TEXT_DOMAIN)
                . '&nbsp;<a href="' . add_query_arg(array('wpc_action' => 'lostpassword'), WPC()->get_login_url()) . '">'
                . __('Get New Password', WPC_CLIENT_TEXT_DOMAIN)
                . '</a>',
            );

            $data['action'] = isset($_REQUEST['wpc_action']) ? $_REQUEST['wpc_action'] : 'login';
            $data['login_href'] = WPC()->get_login_url();

            $wpc_clients_staff = WPC()->get_settings('clients_staff');
            //check reset password link
            if (isset($wpc_clients_staff['lost_password']) && 'yes' == $wpc_clients_staff['lost_password']) {
                $data['lostpassword_href'] = add_query_arg(array('wpc_action' => 'lostpassword'), WPC()->get_login_url());
            }

            //errors of login
            if (isset($GLOBALS['wpclient_login_msg']) && '' != $GLOBALS['wpclient_login_msg']) {
                $data['error_msg'] = $GLOBALS['wpclient_login_msg'];
                $data['error_class'] = 'wpc_error';
            }

            /**
             * Handles sending password retrieval email to user.
             *
             * @uses $wpdb WordPress Database object
             *
             * @return bool|WP_Error True: when finish. WP_Error on error
             */
            if (!function_exists('retrieve_password')) {
                function retrieve_password()
                {
                    global $wpdb;

                    if (empty($_POST['user_login'])) {
                        $data['error_msg'] = __('<strong>ERROR</strong>: Enter a username or e-mail address.', WPC_CLIENT_TEXT_DOMAIN);
                        return $data['error_msg'];
                    } elseif ( strpos( $_POST['user_login'], '@' ) ) {
                        if (!is_email(trim($_POST['user_login']))) {
                            $data['error_msg'] = __('<strong>ERROR</strong>: Invalid E-mail.', WPC_CLIENT_TEXT_DOMAIN);
                            return $data['error_msg'];
                        } elseif (!email_exists(trim($_POST['user_login']))) {
                            $data['error_msg'] = __('<strong>ERROR</strong>: There is no user registered with that E-mail address.', WPC_CLIENT_TEXT_DOMAIN);
                            return $data['error_msg'];
                        } else {
                            $user_data = get_user_by('email', trim($_POST['user_login']));
                        }
                    } else {
                        $login = trim($_POST['user_login']);
                        $user_data = get_user_by('login', $login);

                        if (empty($user_data)) {
                            $data['error_msg'] = __('<strong>ERROR</strong>: There is no user registered with that Username.', WPC_CLIENT_TEXT_DOMAIN);
                            return $data['error_msg'];
                        }

                    }

                    //check permission for reset password
                    if (!user_can($user_data, 'wpc_reset_password') && ( user_can($user_data, 'wpc_manager') || user_can($user_data, 'wpc_client') || user_can($user_data, 'wpc_client_staff') || user_can($user_data, 'wpc_admin'))) {
                        $data['error_msg'] = __('<strong>ERROR</strong>:  You do not have permission to reset your password.', WPC_CLIENT_TEXT_DOMAIN);
                        return $data['error_msg'];
                    }


                    // redefining user_login ensures we return the right case in the email
                    $user_login = $user_data->user_login;
                    $user_email = $user_data->user_email;

                    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));

                    if (empty($key)) {
                        // Generate something random for a key...
                        $key = wp_generate_password(20, false);
                        // Now insert the new md5 key into the db
                        $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
                    }

                    $args = array(
                        'client_id' => $user_data->ID,
                        'reset_address' => htmlspecialchars(add_query_arg(array(
                            'wpc_action' => 'rp',
                            'key' => $key,
                            'login' => rawurlencode($user_login)
                        ), WPC()->get_login_url()))
                    );

                    //send email
                    if (!WPC()->mail('reset_password', $user_email, $args, 'reset_password'))
                        wp_die(__('The e-mail could not be sent.', WPC_CLIENT_TEXT_DOMAIN) . "<br />\n"
                            . __('Possible reason: your host may have disabled the mail() function...', WPC_CLIENT_TEXT_DOMAIN));

                    return true;
                }
            }

            /**
             * Retrieves a user row based on password reset key and login
             *
             * @uses $wpdb WordPress Database object
             *
             * @param string $key Hash to validate sending user's password
             * @param string $login The user login
             * @return object|WP_Error User's database row on success, error object for invalid keys
             */
            if (!function_exists('wpc_check_password_reset_key')) {
                function wpc_check_password_reset_key($key, $login)
                {
                    global $wpdb;

                    $key = preg_replace('/[^a-z0-9]/i', '', $key);

                    if (empty($key) || !is_string($key))
                        return __('<strong>ERROR</strong>: Invalid key.', WPC_CLIENT_TEXT_DOMAIN)
                        . '&nbsp;<a href="' . add_query_arg(array('wpc_action' => 'lostpassword'), WPC()->get_login_url()) . '">'
                        . __('Get New Password', WPC_CLIENT_TEXT_DOMAIN) . '</a>';

                    if (empty($login) || !is_string($login))
                        return __('<strong>ERROR</strong>: Invalid key.', WPC_CLIENT_TEXT_DOMAIN)
                        . '&nbsp;<a href="' . add_query_arg(array('wpc_action' => 'lostpassword'), WPC()->get_login_url()) . '">'
                        . __('Get New Password', WPC_CLIENT_TEXT_DOMAIN) . '</a>';

                    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_login = %s", $login));

                    if (preg_replace('/[^a-z0-9]/i', '', $user->user_activation_key) != $key)
                        return __('<strong>ERROR</strong>: Invalid key.', WPC_CLIENT_TEXT_DOMAIN)
                        . '&nbsp;<a href="' . add_query_arg(array('wpc_action' => 'lostpassword'), WPC()->get_login_url()) . '">'
                        . __('Get New Password', WPC_CLIENT_TEXT_DOMAIN) . '</a>';

                    return $user;
                }
            }

            /**
             * Handles resetting the user's password.
             *
             * @param object $user The user
             * @param string $new_pass New password for the user in plaintext
             */
            if (!function_exists('wpc_reset_password')) {
                function wpc_reset_password($user, $new_pass)
                {

                    wp_set_password($new_pass, $user->ID);

                    wp_password_change_notification($user);

                }
            }

            if (isset($_GET['key']))
                $data['action'] = 'resetpass';

            // validate action so as to default to the login screen
            if (!in_array($data['action'], array('postpass', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'login', 'temp_password'), true))
                $data['action'] = 'login';

            $classes = array();
            $classes = apply_filters('login_body_class', $classes, $data['action']);
            $data['classes'] = esc_attr(implode(' ', $classes));

            switch ($data['action']) {
                case 'login':
                    $data['terms_used'] = false;

                    break;
                case 'lostpassword':

                    //lost password link is hidden
                    if (!isset($wpc_clients_staff['lost_password']) || 'yes' != $wpc_clients_staff['lost_password']) {
                        WPC()->redirect( WPC()->get_login_url());
                    }

                    $data['error_msg'] = __('Please enter your username or email address. You will receive a link to create a new password via email.', WPC_CLIENT_TEXT_DOMAIN);
                    $data['error_class'] = 'wpc_info';

                    if ('POST' == $_SERVER['REQUEST_METHOD']) {
                        if (true === retrieve_password()) {
                            WPC()->redirect( add_query_arg(array('checkemail' => 'confirm'), WPC()->get_login_url()));
                        } else {
                            $data['error_msg'] = retrieve_password();
                            $data['error_class'] = 'wpc_error';
                        }
                    }

                    $data['user_login'] = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
                    break;
                case 'rp':
                case 'resetpass':
                    //lost password link is hidden
                    if (!isset($wpc_clients_staff['lost_password']) || 'yes' != $wpc_clients_staff['lost_password']) {
                        WPC()->redirect( WPC()->get_login_url());
                    }


                    $user = wpc_check_password_reset_key($_GET['key'], urldecode($_GET['login']));
                    if (is_string($user)) {
                        $data['error_msg'] = $user;
                        $data['error_class'] = 'wpc_error';
                    } else {
                        $data['error_msg'] = __('Enter your new password below.', WPC_CLIENT_TEXT_DOMAIN);
                        $data['error_class'] = 'wpc_info';
                        $data['user_login'] = urldecode(esc_attr($_GET['login']));

                        if (isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2']) {
                            $data['error_msg'] = __('The passwords do not match.', WPC_CLIENT_TEXT_DOMAIN);
                            $data['error_class'] = 'wpc_error';
                        } elseif (isset($_POST['pass1']) && isset($_POST['pass2']) && $_POST['pass1'] == $_POST['pass2']) {
                            if (!empty($_POST['pass1'])) {
                                wpc_reset_password($user, WPC()->prepare_password($_POST['pass1']));
                                WPC()->redirect( add_query_arg('msg', 'reset', WPC()->get_login_url()));
                            }
                        }
                    }

                    $data['user'] = $user;

                    break;
                case 'temp_password':
                    if (!is_user_logged_in()) {
                        WPC()->redirect( WPC()->get_login_url());
                    }
                    $data['error_msg'] = __('Your password is temporary. Please enter new password.', WPC_CLIENT_TEXT_DOMAIN);
                    $data['error_class'] = 'wpc_info';
                    $user_data = wp_get_current_user();
                    $user = $user_data->data;
                    $data['action'] = 'resetpass';
                    $data['lostpassword_href'] = '#';
                    if (isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2']) {
                        $data['error_msg'] = __('The passwords do not match.', WPC_CLIENT_TEXT_DOMAIN);
                        $data['error_class'] = 'wpc_error';
                    }

                    $data['user'] = $user;

                    break;
            }

            if (isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail']) {
                $data['error_msg'] = __('Check your e-mail for the confirmation link.', WPC_CLIENT_TEXT_DOMAIN);
                $data['error_class'] = 'wpc_info';
            }

            if (isset($_GET['msg']) && 've' == $_GET['msg']) {
                $data['msg_ve'] = __('Your e-mail address is verified.', WPC_CLIENT_TEXT_DOMAIN);
                $data['msg_ve_class'] = 'wpc_apply';
            }

            if (isset($_GET['msg']) && 'reset' == $_GET['msg']) {
                $data['msg_ve'] = __('Your password has been reset.', WPC_CLIENT_TEXT_DOMAIN);
                $data['msg_ve_class'] = 'wpc_apply';
            }

            //fixed warning
            $data['login_footer'] = '';

            add_action('wp_footer', '__wpc_login_footer', 999);

            if (!function_exists('__wpc_login_footer')) {
                function __wpc_login_footer()
                {
                    do_action('login_footer');
                }
            }

            // Check for custom text
            if ( $no_redirect && is_user_logged_in() ) {
                $data['error_msg'] = ( isset( $atts['no_redirect_text'] ) && !empty( $atts['no_redirect_text'] ) ) ? $atts['no_redirect_text'] : sprintf( __( '<p>%s already logged in.</p>', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['client']['s'] );
            }

            return WPC()->get_template( 'form/login/common.php', '', $data );
        } else {
            global $current_user;

            $url = WPC_Redirect_Rules::login_redirect_rules( get_home_url(), '', $current_user );

            if ( !empty( $url ) ) {
                WPC()->redirect( $url );
            } else {
                WPC()->redirect( add_query_arg( array( 'msg' => 've' ), get_home_url() ) );
            }
        }
    }


    /*
    * Shortcode
    */
    function shortcode_logoutb( $atts, $contents = null ) {

        if ( !is_user_logged_in() )
            return "";

        $data['logout_url']  = WPC()->get_logout_url();
        $data['logout_label'] = !empty( $atts['text'] ) ? $atts['text'] : __( 'LOGOUT', WPC_CLIENT_TEXT_DOMAIN );

        return WPC()->get_template( 'logout.php', '', $data );
    }


    /*
    * Shortcode for display file upload form
    */
    function shortcode_uploadf( $atts, $contents = null ) {
        global $post, $wpdb;

        //checking access
        $client_id = WPC()->checking_page_access();

        if ( false === $client_id ) {
            return '';
        }

        $msg = "";

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        //Regular uploade file
        if ( isset( $_POST['b'] ) && is_array( $_POST['b'] ) ) {

            //Check file size
            if ( isset( $wpc_file_sharing['file_size_limit'] ) && '' != $wpc_file_sharing['file_size_limit'] ) {
                if ( isset( $_FILES['file']['size'] ) && '' != $_FILES['file']['size'] ) {
                    $size = round( $_FILES['file']['size'] / 1024 );
                    if ( $size > apply_filters( 'wpc_client_upload_max_file_size', $wpc_file_sharing['file_size_limit'] ) ) {
                        $msg = __( 'The file size more than allowed!', WPC_CLIENT_TEXT_DOMAIN );
                    }
                }
            }

            //Check wp_nonce
            if ( isset( $_POST['verify_nonce'] ) && isset( $_POST['include_ext'] ) && isset( $_POST['exclude_ext'] ) ) {
                if ( ! wp_verify_nonce( $_POST['verify_nonce'], $_POST['include_ext'] . $_POST['exclude_ext'] . $client_id ) ) {
                    $msg = __( "Nonce check error!", WPC_CLIENT_TEXT_DOMAIN );
                }
            } else {
                $msg = __( "Nonce check error!", WPC_CLIENT_TEXT_DOMAIN );
            }

            /*our_hook_
                hook_name: wp_client_before_client_uploaded_file
                hook_title: Client Uploads File
                hook_description: Hook runs before client uploads file.
                hook_type: filter
                hook_in: wp-client
                hook_location class.user_shortcodes.php
                hook_param: string $error_message, string $filepath
                hook_since: 3.8.0
            */
            $msg = apply_filters( 'wp_client_before_client_uploaded_file', $msg, $_FILES['file']['tmp_name'] );

            if( empty( $msg ) ) {
                $include_extensions = ( isset( $wpc_file_sharing['include_extensions'] ) && !empty( $wpc_file_sharing['include_extensions'] ) ) ? $wpc_file_sharing['include_extensions'] : '';
                $include_extensions = ( isset( $atts['include'] ) && '' != $atts['include'] ) ? strtolower($atts['include'] ): strtolower($include_extensions);
                $include_filetypes = ( '' != $include_extensions ) ? explode( ',', $include_extensions ) : array();
                $include_filetypes = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $include_filetypes);
                $include_filetypes= array_filter($include_filetypes, function($el){
                    return !empty($el);
                });

                $exclude_extensions = ( isset( $wpc_file_sharing['exclude_extensions'] ) && !empty( $wpc_file_sharing['exclude_extensions'] ) ) ? $wpc_file_sharing['exclude_extensions'] : '';
                $exclude_extensions = ( isset( $atts['exclude'] ) && '' != $atts['exclude'] ) ? strtolower($atts['exclude']) : strtolower($exclude_extensions);
                $exclude_filetypes = ( '' != $exclude_extensions ) ? explode( ',', $exclude_extensions ) : array();
                $exclude_filetypes = array_map( array( WPC()->files(), 'ltrim_file_extension' ), $exclude_filetypes);
                $exclude_filetypes = array_filter($exclude_filetypes, function($el){
                    return !empty($el);
                });

                $ext = explode( '.', $_FILES['file']['name'] );
                $ext = $ext[ count( $ext ) - 1 ];
                $ext = strtolower($ext);


                if( ( 0 == count( $include_filetypes ) && 0 < count( $exclude_filetypes ) && !in_array( $ext, $exclude_filetypes ) ) ||
                    ( 0 < count( $include_filetypes ) && 0 == count( $exclude_filetypes ) && in_array( $ext, $include_filetypes ) ) ||
                    ( 0 < count( $include_filetypes ) && 0 < count( $exclude_filetypes ) && in_array( $ext, $include_filetypes ) && !in_array( $ext, $exclude_filetypes ) ) ||
                    ( 0 == count( $include_filetypes ) && 0 == count( $exclude_filetypes ) ) ) {

                    if ( isset( $_POST['file_cat_id'] ) && !empty($_POST['file_cat_id'] ) ) {
                        //get category by ID from selectbox
                        $category_data = $wpdb->get_row( $wpdb->prepare(
                            "SELECT cat_id, folder_name
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_id = %d",
                            $_POST['file_cat_id']
                        ), ARRAY_A );

                        if ( isset( $category_data ) && !empty( $category_data ) ) {
                            $cat_id = $category_data['cat_id'];
                            $folder_name = $category_data['folder_name'];
                        } else {
                            //if wrong categoty - get default category
                            $cat_id = $wpdb->get_var(
                                "SELECT cat_id
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                            );

                            if( !$cat_id ) {
                                //get new order for category
                                $category_order = $wpdb->get_var(
                                    "SELECT COUNT(cat_id)
                                    FROM {$wpdb->prefix}wpc_client_file_categories
                                    WHERE parent_id='0'"
                                );

                                $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                                $wpdb->insert(
                                    "{$wpdb->prefix}wpc_client_file_categories",
                                    array(
                                        'cat_name'      => 'General',
                                        'folder_name'   => 'General',
                                        'parent_id'     => '0',
                                        'cat_order'     => $category_order + 1
                                    )
                                );

                                $cat_id = $wpdb->insert_id;
                            }

                            $folder_name = 'General';
                        }

                    } elseif ( isset( $atts['category'] ) ) {
                        if ( is_numeric( $atts['category'] ) ) {
                            //get category by ID
                            $category_data = $wpdb->get_row( $wpdb->prepare(
                                "SELECT cat_id, folder_name
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_id = %d",
                                $atts['category']
                            ), ARRAY_A );

                            $cat_id = $category_data['cat_id'];
                            $folder_name = $category_data['folder_name'];
                        } else {
                            //get categoty by name
                            $category_data = $wpdb->get_row( $wpdb->prepare(
                                "SELECT cat_id, folder_name
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_name = _utf8 '%s' COLLATE utf8_bin",
                                $_POST['file_cat_id']
                            ), ARRAY_A );

                            $cat_id = $category_data['cat_id'];
                            $folder_name = $category_data['folder_name'];
                        }

                        //if wrong categoty - get default category
                        if ( !$cat_id ) {
                            $cat_id = $wpdb->get_var(
                                "SELECT cat_id
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                            );

                            if( !$cat_id ) {
                                //get new order for category
                                $category_order = $wpdb->get_var(
                                    "SELECT COUNT(cat_id)
                                    FROM {$wpdb->prefix}wpc_client_file_categories
                                    WHERE parent_id='0'"
                                );

                                $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                                $wpdb->insert(
                                    "{$wpdb->prefix}wpc_client_file_categories",
                                    array(
                                        'cat_name'      => 'General',
                                        'folder_name'   => 'General',
                                        'parent_id'     => '0',
                                        'cat_order'     => $category_order + 1
                                    )
                                );

                                $cat_id = $wpdb->insert_id;
                            }

                            $folder_name = 'General';
                        }
                    } else {
                        $cat_id = $wpdb->get_var(
                            "SELECT cat_id
                            FROM {$wpdb->prefix}wpc_client_file_categories
                            WHERE cat_name = _utf8 'General' COLLATE utf8_bin"
                        );

                        if( !$cat_id ) {
                            //get new order for category
                            $category_order = $wpdb->get_var(
                                "SELECT COUNT(cat_id)
                                FROM {$wpdb->prefix}wpc_client_file_categories
                                WHERE parent_id='0'"
                            );

                            $category_order = ( isset( $category_order ) && !empty( $category_order ) ) ? $category_order : 0;

                            $wpdb->insert(
                                "{$wpdb->prefix}wpc_client_file_categories",
                                array(
                                    'cat_name'      => 'General',
                                    'folder_name'   => 'General',
                                    'parent_id'     => '0',
                                    'cat_order'     => $category_order + 1
                                )
                            );

                            $cat_id = $wpdb->insert_id;
                        }

                        $folder_name = 'General';
                    }

                    //create folders for file destination if it was not created
                    WPC()->files()->create_file_category_folder( $cat_id, trim( $folder_name ) );

                    //Upload file
                    $orig_name      = $_FILES['file']['name'];
                    $new_name       = basename( rand(0000, 9999) . $orig_name );

                    $args = array(
                        'cat_id'    => $cat_id,
                        'filename'  => $new_name
                    );

                    $filepath    = WPC()->files()->get_file_path( $args );

                    if( move_uploaded_file( $_FILES['file']['tmp_name'], $filepath ) ) {

                        if( in_array( $ext, array( 'gif', 'png', 'jpg', 'jpeg' ) ) ) {
                            WPC()->files()->create_image_thumbnail( $args );
                        }

                        $note = isset( $_POST['note'] ) ? $_POST['note'] : '';

                        $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_files SET
                            user_id             = %d,
                            page_id             = %d,
                            time                = %d,
                            size                = %d,
                            filename            = '%s',
                            name                = '%s',
                            title               = '%s',
                            description         = '%s',
                            cat_id              = '%d',
                            last_download       = '',
                            external            = '0'
                            ", $client_id, $post->ID, time(), $_FILES['file']['size'], $new_name, $orig_name, $orig_name, $note, $cat_id ) );

                        $file_id = $wpdb->insert_id;
                        WPC()->assigns()->set_assigned_data( 'file', $file_id, 'client', array( $client_id ) );

                        $arguments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_files WHERE id=" . $file_id, ARRAY_A );

                        /*our_hook_
                            hook_name: wp_client_client_uploaded_file
                            hook_title: Client Uploads File
                            hook_description: Hook runs when client uploads file.
                            hook_type: action
                            hook_in: wp-client
                            hook_location class.user_shortcodes.php
                            hook_param: array $file_data
                            hook_since: 3.3.0
                        */
                        do_action( 'wp_client_client_uploaded_file', $arguments );

                        $msg        = "The file " . basename($_FILES['file']['name']) . " has been uploaded";

                        $file_category = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id = %d", $cat_id ) );

                        //email to admins
                        $args = array(
                            'role'      => 'wpc_admin',
                            'fields'    => array( 'user_email' )
                        );
                        $admin_emails = get_users( $args );
                        $emails_array = array();
                        if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                            foreach( $admin_emails as $admin_email ) {
                                $emails_array[] = $admin_email->user_email;
                            }
                        }

                        $emails_array[] = get_option( 'admin_email' );

                        $emails_array = array_unique( $emails_array );

                        $args = array( 'client_id' => $client_id, 'file_name' => $orig_name, 'file_category' => $file_category, 'file_download_link' => WPC()->files()->get_file_download_link($file_id, 'for_admin') );

                        foreach( $emails_array as $to_email ) {
                            if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                                WPC()->mail( 'client_uploaded_file', $to_email, $args, 'client_uploaded_file', $filepath );
                            } else {
                                WPC()->mail( 'client_uploaded_file', $to_email, $args, 'client_uploaded_file' );
                            }
                        }

                        //send message to client manager
                        //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $client_id );
                        $manager_ids = WPC()->members()->get_client_managers( $client_id );

                        if( is_array( $manager_ids ) && count( $manager_ids ) ) {
                            foreach( $manager_ids as $manager_id ) {
                                if ( 0 < $manager_id ) {
                                    $manager = get_userdata( $manager_id );
                                    if ( $manager ) {
                                        $manager_email = $manager->get( 'user_email' );

                                        if( isset( $wpc_file_sharing['attach_file_admin'] ) && 'yes' == $wpc_file_sharing['attach_file_admin'] ) {
                                            //send email
                                            WPC()->mail( 'client_uploaded_file', $manager_email, $args, 'client_uploaded_file', $filepath );
                                        } else {
                                            //send email
                                            WPC()->mail( 'client_uploaded_file', $manager_email, $args, 'client_uploaded_file' );
                                        }
                                    }
                                }
                            }
                        }

                        WPC()->redirect( add_query_arg( 'msg', 'success', ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ) );

                    } else {
                        $msg = __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN );
                    }
                } else {
                    $msg = __( 'Such an extension is not supported!', WPC_CLIENT_TEXT_DOMAIN );
                }
            }
        }

        if ( isset( $wpc_file_sharing['client_uploader_type'] ) && 'html5' == $wpc_file_sharing['client_uploader_type'] ) {
            //file uploader
            wp_enqueue_script( 'wp-client-uploadifive', false, array(), WPC_CLIENT_VER, true );


            wp_localize_script( 'wp-client-uploadifive', 'wpc_flash_uploader', array(
                'cancelled' => ' ' . __( "- Cancelled", WPC_CLIENT_TEXT_DOMAIN ),
                'completed' => ' ' . __( "- Completed", WPC_CLIENT_TEXT_DOMAIN ),
                'error_1'   => __( "404 Error", WPC_CLIENT_TEXT_DOMAIN ),
                'error_2'   => __( "403 Forbidden", WPC_CLIENT_TEXT_DOMAIN ),
                'error_3'   => __( "Forbidden File Type", WPC_CLIENT_TEXT_DOMAIN ),
                'error_4'   => __( "File Too Large", WPC_CLIENT_TEXT_DOMAIN ),
                'error_5'   => __( "Unknown Error", WPC_CLIENT_TEXT_DOMAIN )
            ));

            wp_localize_script( 'wp-client-uploadifive', 'wpc_flash_uploader_params', array(
                'params' => array(
                    'auto_upload' => ( isset( $atts['auto_upload'] ) && 'yes' == $atts['auto_upload'] ) ? true : false
                )
            ));

            wp_enqueue_style( 'wp-client-uploadifive' );

        } elseif( isset( $wpc_file_sharing['client_uploader_type'] ) && 'plupload' == $wpc_file_sharing['client_uploader_type'] ) {
            //plupload file uploader
            wp_enqueue_script( 'wp-client-jquery-queue-plupload', false, array(), WPC_CLIENT_VER, true );


            wp_localize_script( 'wp-client-plupload', 'wpc_plupload_uploader', array(
                'params' => array(
                    'auto_upload' => ( isset( $atts['auto_upload'] ) && 'yes' == $atts['auto_upload'] ) ? true : false
                )
            ));

            wp_enqueue_style( 'wp-client-plupload' );
        }

        //include script in after footer
        add_action( 'wp_footer', array( WPC()->hooks(), 'WPC_Files->upload_form_js' ), 99 );

        return ( include WPC()->plugin_dir . 'includes/user/upload.php' );
    }


    /*
    * Shortcode for upload file from hub - client area
    */
    function shortcode_fileslu( $atts, $contents = null ) {
        $atts['file_type'] = 'own';
        if ( isset( $atts['view_type'] ) && 'table' == $atts['view_type'] ) {
            return WPC()->files()->shortcode_files_table( $atts );
        } elseif( isset( $atts['view_type'] ) && 'tree' == $atts['view_type'] ) {
            return WPC()->files()->shortcode_files_tree( $atts );
        } elseif( isset( $atts['view_type'] ) && 'blog' == $atts['view_type'] ) {
            return WPC()->files()->shortcode_files_blog( $atts );
        } else {
            return WPC()->files()->shortcode_files_list( $atts );
        }
    }


    /*
    * Shortcode for display files for client
    */
    function shortcode_filesla( $atts, $contents = null ) {
        $atts['file_type'] = 'all';
        if ( isset( $atts['exclude_author'] ) && 'yes' == $atts['exclude_author'] ) {
            $atts['file_type'] = 'assigned';
        }

        if ( isset( $atts['view_type'] ) && 'table' == $atts['view_type'] ) {
            return WPC()->files()->shortcode_files_table( $atts );
        } elseif( isset( $atts['view_type'] ) && 'tree' == $atts['view_type'] ) {
            return WPC()->files()->shortcode_files_tree( $atts );
        } elseif( isset( $atts['view_type'] ) && 'blog' == $atts['view_type'] ) {
            return WPC()->files()->shortcode_files_blog( $atts );
        } else {
            return WPC()->files()->shortcode_files_list( $atts );
        }
    }


    public function shortcode_files_list( $atts ) {
        return WPC()->files()->shortcode_files_list( $atts );
    }


    public function shortcode_files_table( $atts ) {
        return WPC()->files()->shortcode_files_table( $atts );
    }


    public function shortcode_files_blog( $atts ) {
        return WPC()->files()->shortcode_files_blog( $atts );
    }


    public function shortcode_files_tree( $atts ) {
        return WPC()->files()->shortcode_files_tree( $atts );
    }


    /*
    * Shortcode
    */
    function shortcode_pagel($atts, $contents = null) {
        global $post, $wpdb, $wp_query;

        //checking access
        $user_id = WPC()->checking_page_access();

        if( !empty( $atts['show_sort'] ) ) {
            if( !in_array( strtolower( $atts['show_sort'] ), array('yes', 'no') ) ) {
                unset( $atts['show_sort'] );
            }
        }

        if( !empty( $atts['show_featured_image'] ) ) {
            if( !in_array( strtolower( $atts['show_featured_image'] ), array('yes', 'no') ) ) {
                unset( $atts['show_featured_image'] );
            }
        }

        if( !empty( $atts['show_search'] ) ) {
            if( !in_array( strtolower( $atts['show_search'] ), array('yes', 'no') ) ) {
                unset( $atts['show_search'] );
            }
        }

        if( !empty( $atts['sort_type'] ) ) {
            if( !in_array( strtolower( $atts['sort_type'] ), array('title', 'date' ) ) ) {
                unset( $atts['sort_type'] );
            }
        }

        if( !empty( $atts['sort'] ) ) {
            if( !in_array( strtolower( $atts['sort'] ), array('asc', 'desc') ) ) {
                unset( $atts['sort'] );
            }
        }

        if( !empty( $atts['show_date'] ) ) {
            if( !in_array( strtolower( $atts['show_date'] ), array('yes', 'no') ) ) {
                unset( $atts['show_date'] );
            }
        }

        /**
         *  $data - - - array which use in SMARTY as data array with texts and information about pages
         */
        $data  = array();

        $data['form_id'] = rand( 0, 10000 );

        //show date
        $data['show_date'] = true;
        if( isset( $atts['show_date'] ) && 'no' == strtolower( $atts['show_date'] ) ) {
            $data['show_date'] = false;
        }

        //show sort
        $data['show_sort'] = true;
        if( isset( $atts['show_sort'] ) && 'no' == strtolower( $atts['show_sort'] ) ) {
            $data['show_sort'] = false;
        }

        $data['show_featured_image'] = false;
        if( isset( $atts['show_featured_image'] ) && 'yes' == strtolower( $atts['show_featured_image'] ) ) {
            $data['show_featured_image'] = true;
        }

        //show search
        $data['show_search'] = true;
        if( isset( $atts['show_search'] ) && 'no' == strtolower( $atts['show_search'] ) ) {
            $data['show_search'] = false;
        }

        //no pages text
        $data['no_text'] = '';
        if( isset( $atts['no_text'] ) && !empty( $atts['no_text'] ) ) {
            $data['no_text'] = $atts['no_text'];
        }

        $data['sort_type'] = 'date';
        $data['sort'] = 'desc';
        if( isset( $atts['sort_type'] ) && !empty( $atts['sort_type'] ) && isset( $atts['sort'] ) && !empty( $atts['sort'] ) ) {
            $data['sort_type'] = $atts['sort_type'];
            $data['sort'] = $atts['sort'];
        }

        //part of code for displaying staff directory for clients with staff
        if( 'clientspage' != $post->post_type ) {

            //add some control pages
            if ( current_user_can( 'wpc_client' ) || current_user_can( 'administrator' ) || current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_manager' ) ) {

                //add status message
                if ( isset( $_GET['staff'] ) ) {
                    switch( $_GET['staff'] ) {
                        case 'a':
                            $data['message'] = sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                            break;
                        case 'd':
                            $data['message'] = sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                            break;
                        case 'e':
                            $data['message'] = sprintf( __( '%s <strong>Changed</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                            break;
                    }
                }

                //if client can add staff
                $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
                if ( isset( $wpc_clients_staff['staff_registration'] ) && 'yes' == $wpc_clients_staff['staff_registration'] ) {
                    $data['add_staff_url']  = WPC()->get_slug( 'add_staff_page_id' );

                    $data['add_staff_text'] = sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                }

                $args = array(
                    'role'          => 'wpc_client_staff',
                    'meta_key'      => 'parent_client_id',
                    'meta_value'    => $user_id,
                    'fields'        => 'ID',
                );

                $staff = get_users( $args );

                if ( is_array( $staff ) && 0 < count( $staff ) ) {
                    //get Staff directory page
                    $data['staff_directory_url']  = WPC()->get_slug( 'staff_directory_page_id' );
                    $data['staff_directory_text'] = sprintf( __( '%s Directory', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['p'] );
                }

            }

        }

        $show_current_page = '';
        if( ! ( isset( $atts['show_current_page'] ) && 'yes' == $atts['show_current_page'] ) ) {
            if( isset( $wp_query->query_vars['preview'] ) && 'true' == $wp_query->query_vars['preview'] && isset( $_REQUEST['preview_id'] ) && !empty( $_REQUEST['preview_id'] ) ) {
                $post_id = $_REQUEST['preview_id'];
            } elseif ( isset( $wp_query->query_vars['wpc_page_value'] ) ) {
                $new_post = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );

                if ( $new_post ) {
                    $post_id = $new_post->ID;
                }
            } else {
                $post_id = $post->ID;
            }
            //hide current portal page
            $show_current_page = "p.ID NOT LIKE '$post_id' AND";
        }

        $search_cat = '';
        if( isset( $atts['categories'] ) && '' != $atts['categories'] ) {

            $categories_list = explode( ',', $atts['categories'] );

            if( in_array( 'all', $categories_list ) ) {

                //get all categories with non categoties pages
                $categories_ids = array('0');
                $results = $wpdb->get_col(
                    "SELECT cat_id
                    FROM {$wpdb->prefix}wpc_client_portal_page_categories"
                );
                $categories_ids = array_merge( $categories_ids, $results );

            } else {
                $categories_ids = $categories_list;
            }

            $search_cat = " AND pm.meta_value IN('" . implode( "','", $categories_ids ) . "')";
        } else {
            //get all categories with non categoties pages
            $categories_ids = array('0');
            $results = $wpdb->get_col(
                "SELECT cat_id
                FROM {$wpdb->prefix}wpc_client_portal_page_categories"
            );
            $categories_ids = array_merge( $categories_ids, $results );
        }

        $data['categories'] = $categories_ids;
        $data['categories_ids'] = $categories_ids;
        $data['show_current_page'] = $show_current_page;

        if( isset( $atts['view_type'] ) && $atts['view_type'] == 'tree' ) {

            $data['texts'] = array(
                'actions'       => __( 'Actions', WPC_CLIENT_TEXT_DOMAIN ),
                'author'        => __( 'Author', WPC_CLIENT_TEXT_DOMAIN ),
                'folder'        => __( 'Folder', WPC_CLIENT_TEXT_DOMAIN ),
                'view'          => __( 'View', WPC_CLIENT_TEXT_DOMAIN ),
                'search_page'   => __( 'Search Page', WPC_CLIENT_TEXT_DOMAIN ),
                'clear_search'  => __( 'Clear Search', WPC_CLIENT_TEXT_DOMAIN ),
                'all'           => __( 'All', WPC_CLIENT_TEXT_DOMAIN ),
                'title'         => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
                'datetime'      => __( 'Date', WPC_CLIENT_TEXT_DOMAIN )
            );

            wp_enqueue_script( 'jquery-base64' );

            wp_enqueue_script( 'wpc-pagel-tree-shortcode-js', false, array(), WPC_CLIENT_VER, true );

            wp_enqueue_style( 'wp-pagel-tree-style' );

            wp_enqueue_script( 'wpc-treetable-js', false, array(), WPC_CLIENT_VER, true );

            wp_enqueue_style( 'wpc-pagel-tree-style' );

            $localize_arguments = array(
                'ajax_url'              => WPC()->get_ajax_url(),
                'data'                  => $data,
            );

            if( isset( WPC()->current_plugin_page['client_id'] ) && !empty( WPC()->current_plugin_page['client_id'] ) ) {
                $localize_arguments['client_id'] = WPC()->current_plugin_page['client_id'];
                $localize_arguments['_wpnonce'] = wp_create_nonce( WPC()->current_plugin_page['client_id'] . "client_security" );
            }

            wp_localize_script( 'wpc-pagel-tree-shortcode-js', 'wpc_pagel_pagination' . $data['form_id'], $localize_arguments );

            $data['tree_content'] = ' ';

            $post_contents = WPC()->get_template( 'portal-pages/tree/common.php', '', $data );

        } else {
            //list view
            wp_enqueue_script( 'jquery-base64' );

            wp_enqueue_script( 'wpc-pagel-list-shortcode-js', false, array(), WPC_CLIENT_VER, true );

            wp_enqueue_style( 'wp-pagel-list-style' );


            $data['pages'] = array();

            //show pagination
            $data['show_pagination'] = true;
            $data['show_pagination_by'] = '5';
            if( isset( $atts['show_pagination'] ) && 'no' == strtolower( $atts['show_pagination'] ) ) {
                $data['show_pagination'] = false;
                $data['show_pagination_by'] = false;
            }
            if( $data['show_pagination'] && isset( $atts['show_pagination_by'] ) && is_numeric( $atts['show_pagination_by'] ) ) {
                $data['show_pagination_by'] = $atts['show_pagination_by'];
            }

            //show categories title
            $data['show_categories_title'] = true;
            if ( isset( $atts['show_categories_titles'] ) && 'no' ==  strtolower( $atts['show_categories_titles'] ) ) {
                $data['show_categories_title'] = false;
            }

            $data['texts'] = array(
                'sort_by'       => __( 'Sort by', WPC_CLIENT_TEXT_DOMAIN ),
                'added'         => __( 'Added', WPC_CLIENT_TEXT_DOMAIN ),
                'name'          => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
                'asc'           => __( 'Asc', WPC_CLIENT_TEXT_DOMAIN ),
                'desc'          => __( 'Desc', WPC_CLIENT_TEXT_DOMAIN ),
                'edit'          => __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ),
                'title'         => __( 'Title', WPC_CLIENT_TEXT_DOMAIN ),
                'show_more'     => __( 'Show More', WPC_CLIENT_TEXT_DOMAIN ),
                'order_id'      => __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN ),
                'search_files'  => __( 'Search Files', WPC_CLIENT_TEXT_DOMAIN ),
                'clear_search'  => __( 'Clear Search', WPC_CLIENT_TEXT_DOMAIN ),
                'apply'         => __( 'Apply', WPC_CLIENT_TEXT_DOMAIN ),
                'category'      => __( 'Category', WPC_CLIENT_TEXT_DOMAIN ),
                'change_sort'   => __( 'Change Sort', WPC_CLIENT_TEXT_DOMAIN ),
                'search_page'   => __( 'Search Page', WPC_CLIENT_TEXT_DOMAIN )
            );


            //$mypages_id - - - array of pages which are available for client
            $mypages_id = array();

            //Portal pages in categories with clients access
            $client_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'client', $user_id );

            $results = $wpdb->get_col(
                "SELECT p.ID
                FROM $wpdb->posts p
                    INNER JOIN $wpdb->postmeta pm
                    ON pm.post_id = p.ID
                WHERE $show_current_page
                    p.post_type = 'clientspage' AND
                    p.post_status = 'publish' AND
                    pm.meta_key = '_wpc_category_id' AND
                    pm.meta_value IN('" . implode( "','", $client_portal_page_category_ids ) . "')
                    $search_cat"
            );

            if( isset( $results ) && 0 < count( $results ) ) {
                $mypages_id = array_merge( $mypages_id, $results );
            }

            //Portal pages with clients access
            $client_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'client', $user_id );

            if( isset( $client_portal_page_ids ) && !empty( $client_portal_page_ids ) ) {
                foreach( $client_portal_page_ids as $portal_key=>$client_portal_page_id ) {
                    $wpc_category = get_post_meta( $client_portal_page_id, '_wpc_category_id', true );
                    $wpc_category = ( isset( $wpc_category ) && !empty( $wpc_category ) ) ? $wpc_category : 0;

                    if( !in_array( $wpc_category, $categories_ids ) ) {
                        unset( $client_portal_page_ids[$portal_key] );
                    }
                }
            }

            if( isset( $client_portal_page_ids ) && 0 < count( $client_portal_page_ids ) ) {
                $mypages_id = array_merge( $mypages_id, $client_portal_page_ids );
            }

            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );

            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach( $client_groups_id as $group_id ) {

                    //Portal pages in categories with group access
                    $group_portal_page_category_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page_category', 'circle', $group_id );

                    $results = $wpdb->get_col(
                        "SELECT p.ID
                        FROM $wpdb->posts p
                            INNER JOIN $wpdb->postmeta pm
                            ON pm.post_id = p.ID
                        WHERE $show_current_page
                            p.post_type = 'clientspage' AND
                            p.post_status = 'publish' AND
                            pm.meta_key = '_wpc_category_id' AND
                            pm.meta_value IN('" . implode( "','", $group_portal_page_category_ids ) . "')
                            $search_cat"
                    );

                    if ( 0 < count( $results ) ) {
                        $mypages_id = array_merge( $mypages_id, $results );
                    }

                    //Portal pages with group access
                    $group_portal_page_ids = WPC()->assigns()->get_assign_data_by_assign( 'portal_page', 'circle', $group_id );

                    if( isset( $group_portal_page_ids ) && !empty( $group_portal_page_ids ) ) {
                        foreach( $group_portal_page_ids as $portal_key=>$group_portal_page_id ) {
                            $wpc_category = get_post_meta( $group_portal_page_id, '_wpc_category_id', true );
                            $wpc_category = ( isset( $wpc_category ) && !empty( $wpc_category ) ) ? $wpc_category : 0;

                            if( !in_array( $wpc_category, $categories_ids ) ) {
                                unset( $group_portal_page_ids[$portal_key] );
                            }
                        }
                    }

                    if( isset( $group_portal_page_ids ) && 0 < count( $group_portal_page_ids ) ) {
                        $mypages_id = array_merge( $mypages_id, $group_portal_page_ids );
                    }

                }
            }

            $mypages_id = array_unique( $mypages_id );

            if ( !empty( $mypages_id ) ) {
                $mypages_id = $wpdb->get_col(
                    "SELECT ID
                FROM $wpdb->posts
                WHERE post_type = 'clientspage' AND
                    post_status = 'publish' AND
                    ID IN ( " . implode(',', $mypages_id) . " )"
                );
            }

            $per_page = ( $data['show_pagination_by'] ) ? (int)$data['show_pagination_by'] : count( $mypages_id );

            $data['pagination_flag'] = false;
            $data['count_pages'] = 1;
            if( $per_page < count( $mypages_id ) ) {
                $data['pagination_flag'] = true;
                $data['count_pages'] = ceil( count( $mypages_id ) / $per_page );
            }

            $sort_by = ( isset( $atts['sort_type'] ) && !empty( $atts['sort_type'] ) ) ? $atts['sort_type'] : 'order_id';
            $sort = isset( $atts['sort'] ) ? $atts['sort'] : 'DESC';

            $data['sort'] = $sort_by;
            $data['dir'] = isset( $atts['sort'] ) ? $atts['sort'] : 'desc';

            $order_string = '';
            if( $data['show_categories_title'] ) {
                $cat_sort = 'ASC';
                if( $sort_by == 'category' ) {
                    $cat_sort = $sort;
                }

                $order_string .= "ppc.cat_name $cat_sort, ppc.cat_id ASC,";
            }

            if( $sort_by == 'date' ) {
                $sort_by = 'p.post_date';
            } elseif( $sort_by == 'title' ) {
                $sort_by = 'p.post_title';
            } else {
                $sort_by = 'CAST( pm1.meta_value AS unsigned ) = 0 OR ISNULL(pm1.meta_value), CAST( pm1.meta_value AS unsigned )';
            }

            $order_string .= " $sort_by $sort ";

            if( $data['show_sort'] ) {
                $data['sort_button'] = '';

                switch( $data['sort'] ) {
                    case 'title':
                        $data['sort_button'] = __( 'Title', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'date':
                        $data['sort_button'] = __( 'Date', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'category_name':
                        $data['sort_button'] = __( 'Category Name', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'order_id':
                        $data['sort_button'] = __( 'Order ID', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                }

                if( $data['dir'] == 'desc' ) {
                    $data['sort_button'] .= ' DESC';
                } else {
                    $data['sort_button'] .= ' ASC';
                }
            }


            $pages = $wpdb->get_results(
                "SELECT p.ID,
                    p.post_title,
                    p.post_name,
                    p.post_date,
                    pm1.meta_value AS order_id,
                    IFNULL( pm2.meta_value, 0 ) AS category_id,
                    ppc.cat_name AS category_name
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON( p.ID = pm1.post_id AND pm1.meta_key = '_wpc_order_id' )
                LEFT JOIN {$wpdb->postmeta} pm2 ON( p.ID = pm2.post_id AND pm2.meta_key = '_wpc_category_id' )
                LEFT JOIN {$wpdb->prefix}wpc_client_portal_page_categories ppc ON( pm2.meta_value = ppc.cat_id )
                WHERE p.ID IN('" . implode( "','", $mypages_id ) . "') AND
                    p.post_status = 'publish'
                ORDER BY $order_string
                LIMIT 0, $per_page",
                ARRAY_A );


            if( isset( $pages ) && is_array( $pages ) && count( $pages ) ) {
                foreach( $pages as $key=>$page ) {
                    $portal_page = array();

                    $portal_page['edit_link'] = '';

                    if( 1 == get_post_meta( $page['ID'], 'allow_edit_clientpage', true ) ) {
                        //make link
                        if( WPC()->permalinks ) {
                            $portal_page['edit_link'] = WPC()->get_slug( 'edit_portal_page_id' ) . $page['post_name'];
                        } else {
                            $portal_page['edit_link'] = add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $page['post_name'] ), WPC()->get_slug( 'edit_portal_page_id', false ) );
                        }
                    }

                    $portal_page['url']             = get_permalink( $page['ID'] );
                    $portal_page['id']              = $page['ID'];
                    $portal_page['title']           = nl2br( $page['post_title'] );
                    $portal_page['creation_date']   = strtotime( $page['post_date'] );
                    $portal_page['icon']            = get_the_post_thumbnail( $page['ID'], 'post-thumbnail', array( 'class' => 'wpc_pp_image' ) );

                    $portal_page['date']            = WPC()->date_format( strtotime( $page['post_date'] ), 'date' );
                    $portal_page['time']            = WPC()->date_format( strtotime( $page['post_date'] ), 'time' );
                    $portal_page['category_name']   = ( isset( $page['category_name'] ) && !empty( $page['category_name'] ) ) ? $page['category_name'] : __( 'No Category', WPC_CLIENT_TEXT_DOMAIN );

                    if( !( $data['show_categories_title'] && ( ( isset( $pages[$key - 1]['category_id'] ) && $page['category_id'] != $pages[$key - 1]['category_id'] ) || !isset( $pages[$key - 1]['category_id'] ) ) ) ) {
                        unset( $portal_page['category_name'] );
                    }

                    $data['pages'][] = $portal_page;
                }

                $last_page = end( $pages );
                $data['last_category_id'] = $last_page['category_id'];
            } else {
                $data['show_search'] = false;
            }

            $localize_arguments = array(
                'ajax_url'      => WPC()->get_ajax_url(),
                'data'          => $data,
            );

            if( isset( WPC()->current_plugin_page['client_id'] ) && !empty( WPC()->current_plugin_page['client_id'] ) ) {
                $localize_arguments['client_id'] = WPC()->current_plugin_page['client_id'];
                $localize_arguments['_wpnonce'] = wp_create_nonce( WPC()->current_plugin_page['client_id'] . "client_security" );
            }

            if( isset( $localize_arguments['data']['pages'] ) )
                unset( $localize_arguments['data']['pages'] );

            wp_localize_script( 'wpc-pagel-list-shortcode-js', 'wpc_pagel_pagination' . $data['form_id'], $localize_arguments );

            $post_contents = WPC()->get_template( 'portal-pages/list/common.php', '', $data );
        }

        return $post_contents;
    }


    /* Add js script for js sort pages */
    function portal_pages_sort_scripts() { ?>
        <style>
            .active_sort {
                color: #000;
            }

            .hub_content {
                min-height:200px;
            }

            .wpc-toolbar ul.nav {
                padding: 0;
            }

            .wpc_link_current {
                font-weight: 700;
            }

            .subsubsub li{
                display: inline;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('.sort_date_asc').click(function() {
                    var obj = jQuery(this).parents('.wpc_client_client_pages');
                    obj.children().removeClass('active_sort');
                    jQuery(this).addClass('active_sort');

                    obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                        jQuery(element).find('span.wpc_page_item').tsort('a:eq(0)',{ order:'asc', data:'timestamp' });
                    });
                });

                jQuery('.sort_date_desc').click(function() {
                    var obj = jQuery(this).parents('.wpc_client_client_pages');
                    obj.children().removeClass('active_sort');
                    jQuery(this).addClass('active_sort');

                    obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                        jQuery(element).find('span.wpc_page_item').tsort('a:eq(0)',{ order:'desc', data:'timestamp' });
                    });
                });

                jQuery('.sort_title_asc').click(function() {
                    var obj = jQuery(this).parents('.wpc_client_client_pages');
                    obj.children().removeClass('active_sort');
                    jQuery(this).addClass('active_sort');

                    obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                        jQuery(element).find('span.wpc_page_item').tsort('a:eq(0)',{order:'asc'});
                    });
                });
                jQuery('.sort_title_desc').click(function() {
                    var obj = jQuery(this).parents('.wpc_client_client_pages');
                    obj.children().removeClass('active_sort');
                    jQuery(this).addClass('active_sort');

                    obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                        jQuery(this).find('span.wpc_page_item').tsort('a:eq(0)',{order:'desc'});
                    });
                });
            });
        </script>
    <?php }


    /*
    * Shortcode
    */
    function shortcode_private_messages($atts, $contents = null, $shortcode) {
        global $wpdb, $current_user, $post;

        $atts = $this->shortcode_atts( $atts, $shortcode);
        //checking access
        $user_id = WPC()->checking_page_access();

        if ( false === $user_id ) {
            return '';
        }

        //for show more messages

        wp_enqueue_script( 'jquery-base64' );

        WPC()->custom_fields()->add_custom_datepicker_scripts();

        wp_enqueue_script( 'wpc-select-js' );
        wp_enqueue_style( 'wpc-select-style' );


        wp_enqueue_script( 'wpc_client_com', false, array(), WPC_CLIENT_VER, true );

        wp_localize_script( 'wpc_client_com', 'wpc_shortcode_messages_atts', $atts );

        wp_localize_script( 'wpc_client_com', 'wpc_private_message_data', array(
            'ajax_url'  => WPC()->get_ajax_url(),
            'texts'     => array(
                'error_to'      => __( '"To" field is required!', WPC_CLIENT_TEXT_DOMAIN ),
                'error_subject' => __( 'Subject is required!', WPC_CLIENT_TEXT_DOMAIN ),
                'error_content' => __( 'Message is required!', WPC_CLIENT_TEXT_DOMAIN ),
                'error_cc_email'=> __( 'CC Email must be an email address!', WPC_CLIENT_TEXT_DOMAIN ),
                'read'          => __( 'Message(s) was read', WPC_CLIENT_TEXT_DOMAIN ),
                'archived'      => __( 'Message(s) was archived', WPC_CLIENT_TEXT_DOMAIN ),
                'trashed'       => __( 'Message(s) was trashed', WPC_CLIENT_TEXT_DOMAIN ),
                'leaved'        => __( 'Message(s) was leaved', WPC_CLIENT_TEXT_DOMAIN ),
                'restored'      => __( 'Message(s) was restored', WPC_CLIENT_TEXT_DOMAIN ),
                'send_message'  => __( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ),
                'send_messages' => __( 'Send Separate Messages', WPC_CLIENT_TEXT_DOMAIN ),
                'group_dialog'  => __( 'Create Group Dialogue', WPC_CLIENT_TEXT_DOMAIN )
            ),
            'client_id' => get_current_user_id(),
            '_wpnonce'  => wp_create_nonce( get_current_user_id() . "client_security" )
        ));

        //field style
        wp_enqueue_style( 'wp-client-avatar-style' );

        wp_enqueue_style( 'wpc-private-messages-style' );

        $data['ajax_pagination'] = false;

        $data['to_users'] = WPC()->private_messages()->private_messages_build_to_list();

        $data['new_message_textarea'] = '<textarea class="new_message_content wpc_textarea" name="new_message[content]" style="width:100%; height:100px;resize: vertical;" placeholder="' . __( 'Type your private message here', WPC_CLIENT_TEXT_DOMAIN ) . '"></textarea>';

        $data['show_cc_email'] = false;
        $wpc_private_messages = WPC()->get_settings( 'private_messages' );
        if( isset( $wpc_private_messages['add_cc_email'] ) && 'yes' == $wpc_private_messages['add_cc_email'] ) {
            $data['show_cc_email'] = true;
        }

        $data['show_filters'] = true;
        if( isset( $atts['show_filters'] ) && 'no' == $atts['show_filters'] ) {
            $data['show_filters'] = false;
        }

        $data['display_name'] = !empty( $wpc_private_messages['display_name'] ) ? $wpc_private_messages['display_name'] : 'user_login';

        return WPC()->get_template( 'messages/common.php', '', $data );
    }


    /*
    * Shortcode
    */
    function shortcode_graphic() {
        $wpc_general = WPC()->get_settings( 'general' );
        if ( isset( $wpc_general['graphic'] ) && '' != $wpc_general['graphic'] ) {
            return "<img class='wpc_client_graphic' src='{$wpc_general['graphic']}' />";
        }

        return '';
    }


    /*
    * Shortcode
    */
    function shortcode_client_registration_form( $atts, $contents = null ) {
        $no_redirect = isset( $atts['no_redirect'] ) && $atts['no_redirect'] == 'true';

        /*our_hook_
            hook_name: wp_client_shortcodes_no_redirect
            hook_title: Builders Compatibility - NoRedirect
            hook_description: Hook runs before redirect condition.
            hook_type: filter
            hook_in: wp-client
            hook_location class.ajax.php
            hook_param: boolean $no_redirect
            hook_since: 4.5.4.1
        */
	    $no_redirect = apply_filters( 'wp_client_shortcodes_no_redirect', $no_redirect );

        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        $wpc_captcha = WPC()->get_settings( 'captcha' );
        $wpc_terms = WPC()->get_settings( 'terms' );
        $wpc_privacy = WPC()->get_settings( 'privacy' );

        if ( !isset( $wpc_clients_staff['client_registration'] ) || 'yes' != $wpc_clients_staff['client_registration'] ) {
            return __( 'Registration is disabled!', WPC_CLIENT_TEXT_DOMAIN );
        }

        if( is_user_logged_in() && $no_redirect ) {
            $html = WPC()->get_template('form/registration/no_redirect.php');
            return $html;
        }


        if( is_user_logged_in() && !$no_redirect ) {
            $redirect = get_home_url();
            if( current_user_can( 'wpc_client' ) &&  !current_user_can( 'manage_network_options' ) ) {
                WPC()->redirect( WPC()->get_hub_link() );
            } elseif ( current_user_can( 'wpc_admin' ) || current_user_can( 'wpc_manager' ) || current_user_can( 'administrator' ) ) {
                $redirect = get_admin_url();
            }
            WPC()->redirect( $redirect );
        }
        $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_add_client' );

        do_action( 'wpc_client_page_client_registration_form' );

        //get Avatar data
        $data['show_avatar'] = false;
        if( isset( $wpc_clients_staff['avatar_on_registration'] ) && 'yes' == $wpc_clients_staff['avatar_on_registration'] ) {
            $data['show_avatar'] = true;
            $data['avatar'] = WPC()->members()->build_avatar_field( 'avatar' );
            $data['labels']['avatar'] = __( 'Avatar', WPC_CLIENT_TEXT_DOMAIN );
        }

        if( isset( $wpc_captcha['enabled'] ) && 'yes' == $wpc_captcha['enabled'] &&
            !empty( $wpc_captcha['use_on'] ) && in_array( 'registration', $wpc_captcha['use_on'] ) ) {

            $data['captcha'] = WPC()->captcha()->generate();
        }

        //get Terms&Conditions Data
        $data['terms_used'] = false;
        if( isset( $wpc_terms['using_terms'] ) && 'yes' == $wpc_terms['using_terms'] && !empty( $wpc_terms['using_terms_form'] ) && in_array( 'registration', $wpc_terms['using_terms_form'] ) ) {
            $data['terms_used'] = true;
            $data['vals']['terms_default_checked'] = ( isset( $wpc_terms['terms_default_checked'] ) && 'yes' == $wpc_terms['terms_default_checked'] ) ? ' checked="checked"' : '';

            $data['vals']['terms_hyperlink'] = ( isset( $wpc_terms['terms_hyperlink'] ) && !empty( $wpc_terms['terms_hyperlink'] ) ) ? $wpc_terms['terms_hyperlink'] : '#';
            $data['terms_agree'] = ( !empty( $wpc_terms['terms_text'] ) ) ? $wpc_terms['terms_text'] : __( 'I agree.', WPC_CLIENT_TEXT_DOMAIN);
        }

        // get Privacy Policy
        $data['privacy_used'] = false;
        if( isset( $wpc_privacy['using_privacy_form'] ) && in_array( 'registration', $wpc_privacy['using_privacy_form'] ) && get_privacy_policy_url() ) {
            $data['privacy_used'] = true;
            $data['privacy_default_checked'] = isset( $wpc_privacy['privacy_default_checked'] ) && 'yes' == $wpc_privacy['privacy_default_checked'] ? true: false;
            $text_format = empty( $wpc_privacy['privacy_text'] ) ? __( 'I accept {link}', WPC_CLIENT_TEXT_DOMAIN) : $wpc_privacy['privacy_text'];
            $data['privacy_agree'] = str_replace( '{link}', get_the_privacy_policy_link(), $text_format );
        }

        /*our_hook_
            hook_name: wpc_client_before_registration_form_submit
            hook_title: Change $_REQUEST before submit Registration
            hook_description: Filter change $_REQUEST array before submit
            hook_type: filter
            hook_in: wp-client
            hook_location client_registration_form.php
            hook_param: array $_REQUEST
            hook_since: 3.8.2
        */
        $filtered_request = apply_filters( 'wpc_client_before_registration_form_submit', $_REQUEST );

        extract($filtered_request);

        $error = "";
        if( isset( $wpc_submit_registration ) ) {
            // validate at php side

            //validate main fields
            if ( empty( $contact_name ) ) // empty username
                $error .= __('The Contact Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

            if ( empty( $contact_username ) ) // empty username
                $error .= __('The Username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

            if ( empty( $contact_email ) ) // empty email
                $error .= __('The Email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

            if ( username_exists( $contact_username ) ) //  already exsits user name
                $error .= __('Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN);

            $contact_email = apply_filters( 'pre_user_email', isset( $contact_email ) ? $contact_email : '' );
            if ( email_exists( $contact_email ) ) // email already exists
                $error .= __('Sorry, email address already in use!<br/>', WPC_CLIENT_TEXT_DOMAIN);

            if ( empty( $contact_password ) || empty( $contact_password2 ) ) {
                if ( empty( $contact_password ) ) // password
                    $error .= __("Password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
                elseif ( empty( $contact_password2 ) ) // confirm password
                    $error .= __("Confirm password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
                elseif ( $contact_password != $contact_password2 )
                    $error .= __("Sorry, Passwords are not matched! .<br/>", WPC_CLIENT_TEXT_DOMAIN);
            }

            if( !isset( $custom_fields ) ) {
                $custom_fields = array();
            }

            if( isset( $_FILES['custom_fields'] ) ) {
                $files_custom_fields = array();
                foreach( $_FILES['custom_fields'] as $key1 => $value1)
                    foreach( $value1 as $key2 => $value2 )
                        $files_custom_fields[$key2][$key1] = $value2;

                $custom_fields = array_merge($custom_fields, $files_custom_fields );
            }

            if( isset( $all_custom_fields ) && is_array( $all_custom_fields ) ) {
                $validation = WPC()->custom_fields()->validate_custom_fields_post_data( 'custom_fields', $all_custom_fields );
                if( is_wp_error( $validation ) ) {
                    $error .= implode( '<br />', $validation->get_error_messages() ) . '<br />';
                }
            }

            if( isset( $data['captcha'] ) ) {
                $valid_captcha = WPC()->captcha()->validate();
                if( is_wp_error( $valid_captcha ) ) {
                    $error .= implode( '<br />', $valid_captcha->get_error_messages() ) . '<br />';
                }
            }

            //terms&conditions validate
            if( isset( $wpc_terms['using_terms'] ) && 'yes' == $wpc_terms['using_terms'] && !empty( $wpc_terms['using_terms_form'] ) && in_array( 'registration', $wpc_terms['using_terms_form'] ) ) {
                if( empty( $terms_agree ) ) {
                    $error .= ( isset( $wpc_terms['terms_notice'] ) && !empty( $wpc_terms['terms_notice'] ) ) ? $wpc_terms['terms_notice'] : __( 'Sorry, you must agree to the Terms/Conditions to continue!', WPC_CLIENT_TEXT_DOMAIN );
                }
            }

            /*our_hook_
                hook_name: wpc_client_registration_form_validation
                hook_title: Client Registration Form
                hook_description: Can be used for validation custom fields on Client Registration Form.
                hook_type: filter
                hook_in: wp-client
                hook_location client_registration_form.php
                hook_param: string $error
                hook_since: 3.9.0
            */
            $error = apply_filters( 'wpc_client_registration_form_validation', $error );

            if ( empty( $error ) ) {
                $userdata = array(
                    'user_pass'         => WPC()->prepare_password( $contact_password2 ),
                    'user_login'        => esc_attr( $contact_username ),
                    'display_name'      => esc_attr( trim( $contact_name ) ),
                    'user_email'        => $contact_email,
                    'role'              => 'wpc_client',
                    'business_name'     => isset( $business_name ) ? esc_attr( trim( $business_name ) ) : esc_attr( trim( $contact_name ) ),
                    'contact_phone'     => isset( $contact_phone ) ? esc_attr( trim( $contact_phone ) ) : '',
                    'send_password'     => isset( $_REQUEST['user_data']['send_password'] ) ? esc_attr( $_REQUEST['user_data']['send_password'] ) : '',
                    'self_registered'   => 1,
                    'avatar'            => !empty( $avatar ) ? $avatar : '',
                );

                    //approve the new client
                    if ( isset( $wpc_clients_staff['auto_client_approve'] ) && 'yes' == $wpc_clients_staff['auto_client_approve'] ) {
                        $userdata['to_approve'] = 'auto';
                    } else {
                        $userdata['to_approve'] = '1';
                    }

                    //set custom fields
                    $userdata['custom_fields'] = array();
                    if ( isset( $custom_fields ) )
                        $userdata['custom_fields'] = $custom_fields;

                    $user_id = WPC()->members()->client_update_func( $userdata );

                    /*our_hook_
                        hook_name: wpc_client_after_registration_redirect
                        hook_title: Redirect After Successful Registration
                        hook_description: Can be used for change default redirect to Successful Client Registration Page.
                        hook_type: filter
                        hook_in: wp-client
                        hook_location client_registration_form.php
                        hook_param: string $redirect_link
                        hook_since: 4.1.3
                    */
                    $redirect = apply_filters( 'wpc_client_after_registration_redirect', WPC()->get_slug( 'successful_client_registration_page_id' ) );

                    WPC()->redirect( $redirect );
            }
        }

        $data['error']          = $error;
        $data['required_text']  = __( ' <span style="color:red;" title="This field is marked as required by the administrator.">*</span>', WPC_CLIENT_TEXT_DOMAIN );

        WPC()->members()->password_protect_css_js( true );
        wp_enqueue_script( 'wpc_registration', false, array(), WPC_CLIENT_VER, true );
        wp_localize_script( "wpc_registration", 'wpc_registration_var', array(
            'texts' => array(
                'fill_field'          => __( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN )
            )
        ) );

        WPC()->custom_fields()->add_custom_fields_scripts();

        $data['vals']['business_name']        = isset( $_REQUEST['business_name'] ) ? esc_html( $_REQUEST['business_name'] ) : '';
        $data['vals']['contact_name']         = isset( $_REQUEST['contact_name'] ) ? esc_html( $_REQUEST['contact_name'] ) : '';
        $data['vals']['contact_email']        = isset( $_REQUEST['contact_email'] ) ? esc_html( $_REQUEST['contact_email'] ) : '';
        $data['vals']['contact_phone']        = isset( $_REQUEST['contact_phone'] ) ? esc_html( $_REQUEST['contact_phone'] ) : '';
        $data['vals']['contact_username']     = isset( $_REQUEST['contact_username'] ) ? esc_html( $_REQUEST['contact_username'] ) : '';
        $data['vals']['send_password']        = isset( $_REQUEST['send_password'] ) ? esc_html( $_REQUEST['send_password'] ) : '';
        $data['custom_fields'] = $all_custom_fields;

        $html = WPC()->get_template( 'form/registration/registration.php', '', $data );

        return $html;
    }


    /*
    * Shortcode
    */
    function shortcode_registration_successful( $atts, $contents = null ) {
        return WPC()->get_template( 'form/registration/successful.php' );
    }


    /*
    * Shortcode
    */
    function shortcode_add_staff_form( $atts, $contents = null ) {
        global $wp_query, $post;

        if ( !is_user_logged_in() ) {
            WPC()->redirect( WPC()->get_login_url() );
        }

        if ( !current_user_can( 'wpc_client' ) )
            return __( 'Sorry, you do not have permission to see this page!', WPC_CLIENT_TEXT_DOMAIN );

        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );
        if ( !isset( $wpc_clients_staff['staff_registration'] ) || 'yes' != $wpc_clients_staff['staff_registration'] ) {
            return sprintf( __( '%s registration is disabled!', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
        }

        $error = "";
        $wpc_pages          = WPC()->get_settings( 'pages' );

        //save user data
        if ( isset( $_REQUEST['wpc_update_staff'] ) ) {
            $form_data = isset( $_REQUEST['user_data'] ) ? $_REQUEST['user_data'] : array();
            $user_id = isset( $form_data['ID'] ) ? $form_data['ID'] : 0 ;

            // validate at php side
            if( $user_id && get_current_user_id() != get_user_meta( $user_id, 'parent_client_id', true ) ) {
                $error .= sprintf( __( 'You try to edit foreign %s.<br/>', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
            }

            //empty username
            if ( !$user_id && empty( $form_data['user_login'] ) )
                $error .= __( 'The username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
            //username already exsits
            if ( !$user_id && username_exists( $form_data['user_login'] ) )
                $error .= __( 'Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN );

            $form_data['email'] = apply_filters( 'pre_user_email', isset( $form_data['email'] ) ? $form_data['email'] : '' );
            //empty email
            if ( empty( $form_data['email'] ) )
                $error .= __( 'The email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN );
            // email already exists
            $email_exists = email_exists( $form_data['email'] );
            if ( $email_exists && $user_id != $email_exists ) {
                $error .= __( 'Sorry, email address already in use!<br/>', WPC_CLIENT_TEXT_DOMAIN );
            }

            $custom_fields = array();

            if( isset( $_REQUEST['custom_fields'] ) )
                $custom_fields = $_REQUEST['custom_fields'] ;

            if( isset( $_FILES['custom_fields'] ) ) {
                $files_custom_fields = array();
                foreach( $_FILES['custom_fields'] as $key1 => $value1)
                    foreach( $value1 as $key2 => $value2 )
                        $files_custom_fields[$key2][$key1] = $value2;

                $custom_fields = array_merge( $custom_fields, $files_custom_fields );
            }

            if( $user_id )
                $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_edit_staff', $user_id );
            else
                $all_custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_add_staff', $user_id );

            if ( isset( $custom_fields ) && is_array( $custom_fields ) && is_array( $all_custom_fields ) ) {

                foreach ( $all_custom_fields as $all_key=>$all_value ) {
                    if ( ( 'checkbox' == $all_value['type'] || 'radio' == $all_value['type'] || 'multiselectbox' == $all_value['type'] ) && !array_key_exists( $all_key, $custom_fields ) ) {
                        $custom_fields[$all_key] = '';
                    }

                    foreach ( $custom_fields as $key=>$value ) {
                        if ( 'file' == $all_value['type']  ) {
                            if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value['name'] ) {
                                $error .= sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                            }
                        } else {
                            if ( $key == $all_key && isset( $all_value['required'] ) && '1' == $all_value['required'] && '' == $value ) {
                                $error .= sprintf( __( "%s is required.<br/>", WPC_CLIENT_TEXT_DOMAIN), $all_custom_fields[$all_key]['title']);
                            }
                        }
                    }
                }
            }

            //check passwords except case when it is edit action and empty both passwords. In this case we leave old password
            if( !( $user_id && empty( $form_data['pass1'] ) && empty( $form_data['pass2'] ) ) ) {
                if ( empty( $form_data['pass1'] ) ) // password
                    $error .= __("Password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
                elseif ( empty( $form_data['pass2'] ) ) // confirm password
                    $error .= __("Confirm password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
                elseif ( $form_data['pass1'] != $form_data['pass2'] )
                    $error .= __("Sorry, Passwords mismatch! .<br/>", WPC_CLIENT_TEXT_DOMAIN);
            }

            if ( empty( $error ) ) {

                $userdata = array(
                    'user_pass'         => esc_sql( $form_data['pass2'] ),
                    'user_email'        => $form_data['email'],
                    'first_name'        => esc_sql( $form_data['first_name'] ),
                    'last_name'         => esc_sql( $form_data['last_name'] ),
                    'send_password'     => isset( $form_data['send_password'] ) ? '1' : '',

                );

                if ( $user_id ) {
                    $userdata['ID'] = $user_id;
                } else {
                    $userdata['user_login'] = esc_sql( $form_data['user_login'] );
                    $userdata['role'] = 'wpc_client_staff';
                }

                if ( !isset( $userdata['ID'] ) ) {
                    //insert new staff
                    $user_id = wp_insert_user( $userdata );

                    //send email to staff
                    if ( $userdata['send_password'] ) {
                        $args = array( 'client_id' => $user_id, 'user_password' => $userdata['user_pass'] );
                        WPC()->mail( 'staff_registered', $userdata['user_email'], $args, 'staff_registered' );
                    }

                    //email to admins
                    $args = array(
                        'role'      => 'wpc_admin',
                        'fields'    => array( 'user_email' )
                    );
                    $admin_emails = get_users( $args );
                    $emails_array = array();
                    if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                        foreach( $admin_emails as $admin_email ) {
                            $emails_array[] = $admin_email->user_email;
                        }
                    }
                    $args = array(
                        'role'      => 'administrator',
                        'fields'    => array( 'user_email' )
                    );
                    $admin_emails = get_users( $args );
                    if( isset( $admin_emails ) && is_array( $admin_emails ) && 0 < count( $admin_emails ) ) {
                        foreach( $admin_emails as $admin_email ) {
                            $emails_array[] = $admin_email->user_email;
                        }
                    }

                    $emails_array[] = get_option( 'admin_email' );

                    //email to managers if it can approve
                    $args = array(
                        'role'      => 'wpc_manager',
                        'fields'    => array( 'user_email', 'ID' )
                    );

                    $managers = get_users( $args );
                    if( isset( $managers ) && !empty( $managers ) ) {
                        foreach( $managers as $manager ) {
                            if( user_can( $manager->ID, 'wpc_approve_staff' ) ) {
                                $emails_array[] = $manager->user_email;
                            }
                        }
                    }

                    foreach( $emails_array as $to_email ) {
                        WPC()->mail( 'staff_created_admin_notify', $to_email, $args, 'staff_created_admin_notify' );
                    }


                    //assign Employee to client
                    update_user_meta( $user_id, 'parent_client_id', get_current_user_id() );

                    //automaticaly or manual approve of Staff
                    if( !( isset( $wpc_clients_staff['auto_client_staff_approve'] ) && 'yes' == $wpc_clients_staff['auto_client_staff_approve'] ) ) {
                        update_user_meta( $user_id, 'to_approve', '1' );
                    }


                } else {

                    if( empty( $userdata['user_pass'] ) ) {
                        unset( $userdata['user_pass'] );
                    }

                    add_filter( 'send_password_change_email', function() { return false; } );

                    wp_update_user( $userdata );

                    //send email to staff
                    if ( isset( $userdata['user_pass'] ) && '1' == $userdata['send_password'] ) {
                        $args = array( 'client_id' => $user_id, 'user_password' => $userdata['user_pass'] );
                        WPC()->mail( 'staff_registered', $userdata['user_email'], $args, 'staff_registered' );
                    }

                }

                //save custom fileds
                if ( isset( $custom_fields ) && 0 < count( $custom_fields ) ) {
                    $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );

                    foreach( $custom_fields as $key => $value ) {
                        if ( isset( $wpc_custom_fields[$key]['type'] ) && 'file' == $wpc_custom_fields[$key]['type'] ) {
                            //for file custom field
                            if ( !empty( $value['name'] ) ) {
                                $new_name = basename( rand( 0000, 9999 ) . $value['name'] );
                                $filepath = WPC()->get_upload_dir('wpclient/_custom_field_files/' . $key . '/') . $new_name;

                                if ( move_uploaded_file( $value['tmp_name'], $filepath ) ) {
                                    update_user_meta( $user_id, $key, array( 'origin_name' => $value['name'], 'filename' => $new_name ) );
                                }
                            }
                        } else {
                            update_user_meta( $user_id, $key, $value );
                        }
                        //set value to related user_meta with this custom feild
                        if ( isset( $wpc_custom_fields[$key]['relate_to'] ) && '' != trim( $wpc_custom_fields[$key]['relate_to'] ) ) {
                            update_user_meta( $user_id, trim( $wpc_custom_fields[$key]['relate_to'] ), $value );
                        }

                    }
                }

                /*our_hook_
                    hook_name: wpc_client_staff_saved
                    hook_title: Staff Saved
                    hook_description: Hook runs when Staff account is added or updated.
                    hook_type: action
                    hook_in: wp-client
                    hook_location class-wpc-shortcodes.php
                    hook_param: int $user_id, array $userdata
                    hook_since: 3.4.1
                */
                do_action( 'wpc_client_staff_saved', $user_id, $userdata );

                //redirect
                $hub_url = add_query_arg( array( 'msg' => isset( $userdata['ID'] ) ? 'e' : 'a' ), WPC()->get_slug( 'staff_directory_page_id', false ) );
                WPC()->redirect( $hub_url );
            }
        }

        $user_data = array();

        //get Employee data
        if ( isset( $_REQUEST['user_data'] ) ) {
            $user_data = $_REQUEST['user_data'];
        } elseif( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] && user_can( $wp_query->query_vars['wpc_page_value'], 'wpc_client_staff' ) ) {
            $user_data = get_userdata( $wp_query->query_vars['wpc_page_value'] );
            $user_data = (array)$user_data->data;
            $user_data['email'] = $user_data['user_email'];
            $user_data['first_name'] = get_user_meta( $wp_query->query_vars['wpc_page_value'], 'first_name', true );
            $user_data['last_name'] = get_user_meta( $wp_query->query_vars['wpc_page_value'], 'last_name', true );
        } elseif( isset( $wpc_pages['edit_staff_page_id'] ) && $post->ID == $wpc_pages['edit_staff_page_id'] ) {
            return __( 'Current user is not a staff!', WPC_CLIENT_TEXT_DOMAIN );
        }

        WPC()->members()->password_protect_css_js( true );

        wp_enqueue_script( 'wpc_add_staff' );

        $localize_array = array(
            'texts' => array(
                'fill_field'             => __( 'You need to fill', WPC_CLIENT_TEXT_DOMAIN ),
            )
        );

        wp_localize_script( "wpc_add_staff", 'wpc_add_staff_var', $localize_array );

        $user_id = ( isset( $user_data['ID'] ) ) ? $user_data['ID'] : 0 ;
        if( $user_id )
            $custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_edit_staff', $user_id );
        else
            $custom_fields = WPC()->custom_fields()->get_custom_fields( 'user_add_staff', $user_id );

        return WPC()->get_template( 'form/staff_edit.php', '', array(
            'error' => $error,
            'wpc_pages' => $wpc_pages,
            'post' => $post,
            'user_data' => $user_data,
            'custom_fields' => $custom_fields,
            'is_edit_staff_page' => isset( $wpc_pages['edit_staff_page_id'] ) && $post->ID == $wpc_pages['edit_staff_page_id']
        ) );
    }


    /*
    * Shortcode
    */
    function shortcode_staff_directory( $atts, $contents = null ) {
        global $wpdb;
        //checking access
        $user_id = WPC()->checking_page_access();

        wp_enqueue_style( 'wpc_staff_directory' ); ?>

        <script type="text/javascript">
            jQuery(document).ready( function() {
                jQuery(document).on('click', '.wpc_staff_line', function(e) {
                    jQuery(this).toggleClass('wpc_active_row');
                    e.stopPropagation();
                });

                jQuery(document).on('click', '.wpc_staff_line.wpc_active_row > td:not(:first-child)', function(e) {
                    e.stopPropagation();
                });

                jQuery(document).on('click', '.wpc_staff_line a', function(e) {
                    e.stopPropagation();
                });
            });
        </script>

        <?php
        if ( !is_user_logged_in() ) {
            WPC()->redirect( WPC()->get_login_url() );
        }

        if ( !current_user_can( 'wpc_client' ) )
           return '<div class="staff_directory">' . __( 'Sorry, you do not have permission to see this page!', WPC_CLIENT_TEXT_DOMAIN ) . '</div>';


        if ( isset( $_GET['wpc_client_action'] ) && 'delete_staff' == $_GET['wpc_client_action'] ) {

            require_once( ABSPATH . 'wp-admin/includes/user.php' );
            if( is_multisite() ) {
                wpmu_delete_user( $_GET['id'] );
            } else {
                wp_delete_user( $_GET['id'] );
            }

            //redirect
            if( WPC()->permalinks ) {
                $hub_url = add_query_arg( array( 'msg' => 'd' ), WPC()->get_slug( 'staff_directory_page_id', false ) );
            } else {
                $hub_url = add_query_arg( array( 'msg' => 'd' ), WPC()->get_slug( 'staff_directory_page_id', false ) );
            }
            WPC()->redirect( $hub_url );
        }

        $wpc_clients_staff = WPC()->get_settings( 'clients_staff' );

        $data = array();

        $data['message'] = '';
        if( isset( $_GET['msg'] ) ) {
            $msg = $_GET['msg'];
            switch( $msg ) {
                case 'a':
                    $data['message'] = sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                    break;
                case 'e':
                    $data['message'] = sprintf( __( '%s <strong>Changed</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                    break;
                case 'd':
                    $data['message'] = sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['staff']['s'] );
                    break;
            }
        }


        $data['add_staff_link'] = '';
        if ( isset( $wpc_clients_staff['staff_registration'] ) && 'yes' == $wpc_clients_staff['staff_registration'] ) {
            $data['add_staff_link'] = WPC()->get_slug( 'add_staff_page_id' );
        }

        $delete_link = add_query_arg( array( 'wpc_client_action' => 'delete_staff' ), WPC()->get_slug( 'staff_directory_page_id', false ) );
        $edit_staff_link = WPC()->get_slug( 'edit_staff_page_id', false );

        $args = array(
            'role'          => 'wpc_client_staff',
            'meta_key'      => 'parent_client_id',
            'meta_value'    => get_current_user_id(),
            'orderby'       => 'user_login',
            'order'         => 'ASC',
        );

        $staffs = get_users( $args );

        $custom_fields_staff = array();
        $wpc_custom_fields = WPC()->get_settings( 'custom_fields' );
        $wpc_custom_fields = array_filter( $wpc_custom_fields, function( $field ) {
            return ( 'staff' == $field['nature'] || 'both' == $field['nature'] ) &&
                isset( $field['view']['user_view_staff']['client'] ) && $field['view']['user_view_staff']['client'] != 'hide';
        });

        $user_ids = array_map( function( $user ) {
            return $user->ID;
        }, $staffs);

        $users_custom_fields = $wpdb->get_results("SELECT user_id as id, meta_key as k, meta_value as val FROM {$wpdb->usermeta} WHERE user_id IN ('" . implode( "','", $user_ids ) . "') AND meta_key IN ('" . implode( "','", array_keys( $wpc_custom_fields ) ) . "')", ARRAY_A );
        $new_array_cf = array();
        foreach( $users_custom_fields as $cf ) {
            $new_array_cf[ $cf['id'] ][ $cf['k'] ] = $cf['val'] ;
        }

        $data['custom_fields'] = $wpc_custom_fields;

        $data['staffs'] = array();
        foreach( $staffs as $staff ) {
            $staff = get_userdata( $staff->ID );

            $staff_data = array(
                'user_login' => $staff->user_login,
                'first_name' => $staff->first_name,
                'last_name'  => isset( $staff->last_name ) ? $staff->last_name : '',
                'user_email' => $staff->user_email,
                'to_approve' => get_user_meta( $staff->ID, 'to_approve', true ),
                'edit_link'  => ( WPC()->permalinks ) ? $edit_staff_link . '/' . $staff->ID : add_query_arg( array( 'wpc_page' => 'edit_staff', 'wpc_page_value' => $staff->ID ), $edit_staff_link ),
                'delete_link'  => $delete_link . '&id=' . $staff->ID
            );

            foreach( $wpc_custom_fields as $field_key=>$field ) {
                $wpc_custom_fields[ $field_key ]['name'] = $field_key;
                $staff_data[ $field_key ] = WPC()->custom_fields()->render_custom_field_value( $wpc_custom_fields[ $field_key ], array(
                    'user_id' => $staff->ID,
                    'value' => maybe_unserialize( isset( $new_array_cf[ $staff->ID ][ $field_key ] ) ? $new_array_cf[ $staff->ID ][ $field_key ] : '' ),
                    'metadata_exists' => isset( $new_array_cf[ $staff->ID ][ $field_key ] ),
                    'empty_value' => '<span title="' . __("Undefined", WPC_CLIENT_TEXT_DOMAIN) . '">-</span>'
                ));
            }

            $data['staffs'][] = $staff_data;
        }

        return WPC()->get_template( 'staff_directory.php', '', $data );
    }


    /*
    * Shortcode for show business name
    */
    function shortcode_business_name( $atts, $contents = null ) {
        if ( is_user_logged_in() ) {

            $client_id = ( isset( WPC()->current_plugin_page['client_id'] ) ) ? WPC()->current_plugin_page['client_id'] : get_current_user_id();

            return get_user_meta( $client_id, 'wpc_cl_business_name', true );
        }
        return '';
    }


    /*
    * Shortcode contact name
    */
    function shortcode_contact_name( $atts, $contents = null ) {
        if ( is_user_logged_in() ) {

            $client_id = ( isset( WPC()->current_plugin_page['client_id'] ) ) ? WPC()->current_plugin_page['client_id'] : get_current_user_id();

            $client = get_userdata( $client_id );

            if ( $client ) {
                return $client->get( 'display_name' );
            }

        }
        return '';
    }


    /*
    * Shortcode for Portal page
    */
    function shortcode_portal_page( $atts, $contents = null ) {
        global $post;
        if ( is_user_logged_in() ) {
            $scheme_key = get_post_meta( $post->ID, '_wpc_style_scheme', true );
            WPC()->pages()->add_scheme_style( $scheme_key );
        }
        return '';
    }


    /**
     * Shortcode for Get Page link
     **/
    function shortcode_get_page_link( $atts, $contents = null ) {
        if ( !empty( $atts['page'] ) ) {
            $url = $atts['page'] == 'hub' ? WPC()->get_hub_link() : WPC()->get_slug( $atts['page'] . '_page_id' );
            if ( '' != $url ) {
                $id     = ( isset( $atts['id'] ) && '' != $atts['id'] ) ?'id="' . $atts['id'] . '"' : '';
                $class  = ( isset( $atts['class'] ) && '' != $atts['class'] ) ? 'class="' . $atts['class'] . '"' : '';
                $style  = ( isset( $atts['style'] ) && '' != $atts['style'] ) ? 'style="' . $atts['style'] . '"' : '';
                $text   = ( isset( $atts['text'] ) && '' != $atts['text'] ) ? $atts['text'] : $atts['page'] . ' link';
                return '<a href="' . $url . '" ' . $id . ' ' . $class . ' ' . $style . '  >' . $text . '</a>';
            }
        }

        return '';
    }


    /**
     * Shortcode for Show Edit ClientPage content
     **/
    function shortcode_edit_portal_page( $atts, $contents = null ) {
        global $wp_query, $wpdb;
        $user_id = WPC()->checking_page_access();

        wp_enqueue_script( 'wpc_edit_portal_page', false, array(), WPC_CLIENT_VER, true );

        $localize_array = array(
            'texts' => array(
                'delete_confirm'     => __( 'Are you sure to delete?', WPC_CLIENT_TEXT_DOMAIN )
            )
        );

        wp_localize_script( "wpc_edit_portal_page", 'wpc_edit_portal_page_var', $localize_array );

        if ( !current_user_can( 'wpc_client' ) && !current_user_can( 'wpc_client_staff' ) )
           return __( 'Sorry, you do not have permission to see this page!', WPC_CLIENT_TEXT_DOMAIN );

        //remove buttons for editor
        //todelete?
        //remove_all_filters( 'mce_external_plugins' );
        $edit_page = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );
        if ( !$edit_page ) {
            WPC()->redirect( WPC()->get_hub_link() );
        }


        $user_ids       = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $edit_page->ID, 'client' );
        $groups_id      = WPC()->assigns()->get_assign_data_by_object( 'portal_page', $edit_page->ID, 'circle' );

        $user_ids = ( is_array( $user_ids ) && 0 < count( $user_ids ) ) ? $user_ids : array();

        //get clients from Client Circles
        if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
            foreach( $groups_id as $group_id ) {
                $user_ids = array_merge ( $user_ids, WPC()->groups()->get_group_clients_id( $group_id ) );
            }

        if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
            $user_ids = array_unique( $user_ids );

        //client hasn't access to this page
        if ( ( empty( $user_ids ) || !in_array( $user_id, $user_ids ) ) ) {
            WPC()->redirect( WPC()->get_hub_link() );
        }

        //portal page cann't be edited
        if ( 1 != get_post_meta( $edit_page->ID, 'allow_edit_clientpage', true ) ) {
            WPC()->redirect( WPC()->get_hub_link() );
        }


        //get portal page
        $clientpage = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts WHERE ID = %d AND post_type = 'clientspage' ", $edit_page->ID ), "ARRAY_A" );

        if ( !is_array( $clientpage ) )
            return sprintf( __( "Wrong %s.", WPC_CLIENT_TEXT_DOMAIN ), WPC()->custom_titles['portal_page']['s'] );

        ob_start();
        require 'user/portal_page.php';
        return ob_get_clean();
    }


    /**
     * Shortcode for Show NoAccessPage content
     **/
    function shortcode_error_image() {
        ob_start();
        if( is_user_logged_in() ) {
            if( isset( $_GET['type'] ) && 'approval' == $_GET['type'] ) {
                echo '<img id="wpc_error_image" class="wpc_no_approved_image" src="' . WPC()->plugin_url . 'images/NoApproved.png" alt="" >' ;
            } elseif ( isset( $_GET['type'] ) && 'verify_email' == $_GET['type'] ) {
                if ( isset( $_GET['send'] ) ) {

                    $user_id = WPC()->checking_page_access(
                        array(
                            'check_email' => false,
                            'check_approve' => false,
                            'check_need_pay' => false
                        ) );

                    $key = get_user_meta( $user_id, 'verify_email_key', true );

                    //make link
                    if ( WPC()->permalinks ) {
                        $link = WPC()->make_url( '/portal/acc-activation/' . $key, get_home_url() );
                    } else {
                        $link = add_query_arg( array( 'wpc_page' => 'acc_activation', 'wpc_page_value' => $key ), get_home_url() );
                    }

                    $args = array( 'client_id' => $user_id, 'verify_url' => $link );
                    $userdata = get_userdata( $user_id );
                    //send email
                    WPC()->mail( 'new_client_verify_email', $userdata->user_email, $args, 'new_client' );
                    WPC()->redirect( add_query_arg( array( 'type' => 'verify_email', 'sent' => 1 ), remove_query_arg( array( 'send' ) ) ) );
                } elseif ( isset( $_GET['sent'] ) ) {
                    _e( 'Email sent successfully', WPC_CLIENT_TEXT_DOMAIN ) ;
                } else {
                    _e( 'You have been registered successfully. A verification email has been sent to your email address. Please click on the link in that email to activate your account. If you do not receive the verification email, click on the button below to request a resend.', WPC_CLIENT_TEXT_DOMAIN ) ;
                    echo '<br /><br /><a href="' . add_query_arg( array( 'send' => 1 ) ) . '">' . __( 'Resend email for verification', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;
                }
            } else {
                echo '<img id="wpc_error_image" class="wpc_no_access_image" src="' . WPC()->plugin_url . 'images/NoAccess.png" alt="" >' ;
            }
        }
        $content_image = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $content_image;
    }


    /**
     * Shortcode for Business Info
     **/
    function shortcode_business_info( $atts ) {
        $return = '';

        if ( !empty( $atts['field'] ) ) {
            $wpc_business_info = WPC()->get_settings( 'business_info' );
            if ( isset( $wpc_business_info[ $atts['field'] ] ) ) {
                $return = $wpc_business_info[ $atts['field'] ];
            }
        }
        return $return;
    }


    /**
     * Shortcode for Show NoAccessPage content
     **/
    function shortcode_errors() {
        ob_start();
        if( is_user_logged_in() ) {
            if( isset( $_GET['type'] ) && 'approval' == $_GET['type'] ) {
                echo '<img id="wpc_error_image" class="wpc_no_approved_image" src="' . WPC()->plugin_url . 'images/NoApproved.png" alt="" >' ;
            } elseif ( isset( $_GET['type'] ) && 'verify_email' == $_GET['type'] ) {
                if ( isset( $_GET['send'] ) ) {

                    $user_id = WPC()->checking_page_access(
                        array(
                            'check_email' => false,
                            'check_approve' => false,
                            'check_need_pay' => false
                        ) );

                    $key = get_user_meta( $user_id, 'verify_email_key', true );

                    //make link
                    if ( WPC()->permalinks ) {
                        $link = WPC()->make_url( '/portal/acc-activation/' . $key, get_home_url() );
                    } else {
                        $link = add_query_arg( array( 'wpc_page' => 'acc_activation', 'wpc_page_value' => $key ), get_home_url() );
                    }
                    $args = array( 'client_id' => $user_id, 'verify_url' => $link );
                    $userdata = get_userdata( $user_id );

                    //send email
                    WPC()->mail( 'new_client_verify_email', $userdata->user_email, $args, 'new_client' );
                    WPC()->redirect( add_query_arg( array( 'type' => 'verify_email', 'sent' => 1 ), remove_query_arg( array( 'send' ) ) ) );
                } elseif ( isset( $_GET['sent'] ) ) {
                    _e( 'Email sent successfully', WPC_CLIENT_TEXT_DOMAIN ) ;
                } else {
                    _e( 'You have been registered successfully. A verification email has been sent to your email address. Please click on the link in that email to activate your account. If you do not receive the verification email, click on the button below to request a resend.', WPC_CLIENT_TEXT_DOMAIN ) ;
                    echo '<br /><br /><a href="' . add_query_arg( array( 'send' => 1 ) ) . '">' . __( 'Resend email for verification', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;
                }
            } else {
                echo '<img id="wpc_error_image" class="wpc_no_access_image" src="' . WPC()->plugin_url . 'images/NoAccess.png" alt="" >' ;
            }
        }
        $content_image = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
        return $content_image;
    }


    /**
     * Shortcode for Redirect to login or HUB page
     **/
    function shortcode_redirect_on_login_hub( $atts, $contents = null ) {
        if ( is_user_logged_in() ) {
            if( current_user_can('wpc_client') || current_user_can('wpc_client_staff') ) { //on HUB
	            WPC()->redirect( WPC()->get_hub_link() );
            } else if( current_user_can('administrator') || current_user_can('wpc_admin') ||
                       current_user_can('wpc_manager') ) { //in admin area
                WPC()->redirect( get_admin_url() );
            } else {
                WPC()->redirect( WPC()->get_slug( 'error_page_id' ) );
            }
        } else {
	        //on login form
	        WPC()->redirect( WPC()->get_login_url() );
        }
    }


    /*
    * Shortcode
    */
    function shortcode_client_managers( $atts, $contents = null ) {

        //checking access
        $user_id = WPC()->checking_page_access();

        //$manager_ids = WPC()->assigns()->get_assign_data_by_assign( 'manager', 'client', $user_id );
        $manager_ids = WPC()->members()->get_client_managers( $user_id );

        $managers = array();
        foreach( $manager_ids as $manager_id ) {
            $manager = get_userdata( $manager_id );
            $managers[$manager_id] = array(
                'nickname'         => $manager->user_login,
                'dispay_name'      => $manager->display_name,
                'first_name'       => get_user_meta( $manager_id,'first_name',true ),
                'last_name'        => get_user_meta( $manager_id,'last_name',true ),
                'contact_phone'    => get_user_meta( $manager_id,'contact_phone',true ),
                'address'          => get_user_meta( $manager_id,'address',true ),
                'email'            => $manager->user_email
            );
        }
        return WPC()->get_template( 'client_managers.php', '', array(
            'managers' => $managers
        ) );
    }



    /*
    * Shortcode start payment steps
    */
    function payment_process_func( $atts, $contents = null ) {

        global $wpc_payments_core, $wpc_gateway_active_plugins, $wpdb;

        //load gateways just on payment page
        $wpc_payments_core->load_gateway_plugins();

        add_filter( 'comments_open', function() { return false; }, 99 );
        add_filter( 'comments_close_text', function() { return ""; }, 99 );
        add_filter( 'comments_array', function() { return array(); }, 99 );

        $step_names = $wpc_payments_core->step_names;

        $order_id  = get_query_var( 'wpc_order_id' ) ? get_query_var( 'wpc_order_id' ) : 0;
        if ( get_query_var( 'wpc_page_value' ) ) {
            if ( $step = array_search( get_query_var( 'wpc_page_value' ), $step_names ) ) {
            } else {
                $step = get_query_var( 'wpc_page_value' );
            }
        } else {
            $step = 2;
        }
        if ( !$order_id ) {
            WPC()->redirect( get_home_url() );
        }

        $order = $wpc_payments_core->get_order_by( $order_id, 'order_id' );

        switch( $step ) {
            case 2:

                //for free payments
                if( 0 == $order['amount'] ) {
                     if ( 'paid' != $order['order_status'] ) {
                        if ( !isset( $order['payment_type'] ) || 'recurring' != $order['payment_type'] ) {
                            $payment_data = array();
                            $payment_data['transaction_status'] = "Completed";
                            $payment_data['subscription_id'] = null;
                            $payment_data['subscription_status'] = null;
                            $payment_data['parent_txn_id'] = null;
                            $payment_data['transaction_type'] = 'paid';
                            $payment_data['transaction_id'] = null;

                        } else {
                            $payment_data = array();
                            $payment_data['transaction_status'] = "Completed";
                            $payment_data['subscription_id'] = null;
                            $payment_data['subscription_status'] = 'active';
                            $payment_data['parent_txn_id'] = null;
                            $payment_data['transaction_type'] = 'subscription_payment';
                            $payment_data['transaction_id'] = null;
                        }

                        $wpc_payments_core->order_update( $order['id'], $payment_data );
                    }

                    /*our_hook_
                        hook_name: wpc_client_payment_thank_you_page_link
                        hook_title: Link for thank you page
                        hook_description: Hook runs before redirect to thank you page.
                        hook_type: filter
                        hook_in: wp-client
                        hook_location payment core
                        hook_param: string $redirect, array $order
                        hook_since: 3.8.8
                    */
                    $redirect = apply_filters( 'wpc_client_payment_thank_you_page_link', '', $order );

                    if ( empty( $redirect ) ) {
	                    //make link
	                    $name = $step_names[5];
	                    if ( WPC()->permalinks ) {
		                    $redirect = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
	                    } else {
		                    $redirect = add_query_arg( array( 'wpc_page'       => 'payment_process',
		                                                 'wpc_order_id'   => $order['order_id'],
		                                                 'wpc_page_value' => $name
		                    ), get_home_url() );
	                    }

	                    /*our_hook_
						  hook_name: wpc_client_payment_thank_you_page_link
						  hook_title: Link for thank you page
						  hook_description: Hook runs before redirect to thank you page.
						  hook_type: filter
						  hook_in: wp-client
						  hook_location payment gateways classes
						  hook_param: string $redirect, array $order
						  hook_since: 3.8.8
						*/
	                    $redirect = apply_filters( 'wpc_client_payment_thank_you_page_link', $redirect, $order );
                    }

                    WPC()->redirect( $redirect );
                }


                $function_activate_gateways = apply_filters( 'wpc_payment_get_activate_gateways_' . $order['function'], array() );
                if ( is_array( $wpc_gateway_active_plugins ) && count( $wpc_gateway_active_plugins )  ) {
                    $i = 0;
                    foreach( $wpc_gateway_active_plugins as $gateway_plugin ) {
                        if ( !in_array( $gateway_plugin->plugin_name, $function_activate_gateways ) ) {
                            unset( $wpc_gateway_active_plugins[$i] );
                        } elseif( isset( $order['payment_type'] ) && 'recurring' == $order['payment_type'] ) {
                            //clear gateways without recurring
                            if ( !isset( $gateway_plugin->recurring ) || true !== $gateway_plugin->recurring ) {
                                unset( $wpc_gateway_active_plugins[$i] );
                            }
                        }

                        //check valid currency in gateways
                        if ( isset( $gateway_plugin->valid_currencies ) && ( !in_array( '_any', $gateway_plugin->valid_currencies ) && !in_array( strtoupper( $order['currency'] ), $gateway_plugin->valid_currencies ) ) ) {
                            unset( $wpc_gateway_active_plugins[$i] );
                        }

                        $i++;
                    }
                }

                $selected_gateway = '';
                if ( is_array( $wpc_gateway_active_plugins ) && 1 == count( $wpc_gateway_active_plugins ) ) {
                    $wpc_gateway_active_plugins = array_values( $wpc_gateway_active_plugins );
                    $selected_gateway = $wpc_gateway_active_plugins[0]->plugin_name;
                } elseif( isset( $_POST['wpc_choose_gateway'] ) && !empty( $_POST['wpc_choose_gateway'] ) ) {
                    foreach( $wpc_gateway_active_plugins as $plugin ) {
                        if( $plugin->plugin_name == $_POST['wpc_choose_gateway'] ) {
                            $selected_gateway = $plugin->plugin_name;
                            break;
                        }
                    }
                }


                // gateway selected
                if ( !empty( $selected_gateway ) ) {
                    $wpc_payments_core->update_order_gateway( $order['id'], $selected_gateway );

                    //make link
                    $name = $step_names[3];
                    if ( WPC()->permalinks ) {
                        $url = WPC()->get_slug( 'payment_process_page_id' ) . $order['order_id'] . "/$name/";
                    } else {
                        $url = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => $name ), get_home_url() );
                    }

                    WPC()->redirect( $url );
                }

                break;

        }

        return $wpc_payments_core->payment_step_content( $order, $step );
    }


    /**
     * Shortcode - Show Client Payments History
     * @param $atts
     * @param null $content
     */
    function shortcode_wpc_payments_history( $atts, $content = null ) {
        global $wpdb;

        //checking access
        $client_id = WPC()->checking_page_access();

        if ( false === $client_id ) {
            return '';
        }

        // get client payments
        $payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpc_client_payments WHERE client_id={$client_id}", ARRAY_A);

        /*our_hook_
            hook_name: wpc_client_payments_history
            hook_title: Array of client payments history
            hook_description: Can be used for filter client payments history
            hook_type: filter
            hook_in: wp-client
            hook_location class-wpc-shortcodes.php
            hook_param: array $payments
            hook_since: 4.5.7.1
        */
        $payments = apply_filters( 'wpc_client_client_payments_history', $payments );

        if ( is_array( $payments ) && 0 < count( $payments ) ) {
            return WPC()->get_template( 'payments_history.php', '', array(
                'payments' => $payments
            ) );
        }
    }


}

endif;