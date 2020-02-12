<?php

if ( !class_exists( "WPC_FW_User" ) ) {

    class WPC_FW_User extends WPC_FW_User_Shortcodes {

        /**
        * constructor
        **/
        function __construct() {

            $this->fw_common_construct();
            $this->fw_shortcodes_construct();

            //filter posts
            add_filter( 'the_posts', array( &$this, 'filter_posts' ), 99 );

            add_action( 'wp_enqueue_scripts', array( &$this, 'wp_css_js' ), 100 );

        }



        /**
         * filter posts
         */
        function filter_posts( $posts ) {
            global $wp_query, $wpdb;

            $filtered_posts = array();

            //if empty
            if ( empty( $posts ) || is_admin() )
                return $posts;

            $wpc_pages = WPC()->get_settings( 'pages' );

            $post_ids = array();
            foreach( $posts as $post ) {
                $post_ids[] = $post->ID;
            }
            $sticky_posts_array = array();
            if( ( isset( $wpc_pages['feedback_wizard_page_id'] ) && in_array( $wpc_pages['feedback_wizard_page_id'], $post_ids ) ) || ( isset( $wpc_pages['feedback_wizard_list_page_id'] ) && in_array( $wpc_pages['feedback_wizard_list_page_id'], $post_ids ) ) ) {
                $sticky_posts_array = get_option( 'sticky_posts' );
                $sticky_posts_array = ( is_array( $sticky_posts_array ) && 0 < count( $sticky_posts_array ) ) ? $sticky_posts_array : array();
            }

            //other filter
            foreach( $posts as $post ) {

                if( in_array( $post->ID, $sticky_posts_array ) ) {
                    continue;
                }

                if ( isset( $wpc_pages['feedback_wizard_page_id'] ) && $post->ID == $wpc_pages['feedback_wizard_page_id'] ) {

                    if ( is_user_logged_in() ) {

                        if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {

                            //actions from Edit ClientPage
                            if ( isset( $_POST['wpc_save_feedback_wizard'] ) && isset( $_POST['wpc_wpnonce'] ) && wp_verify_nonce( $_POST['wpc_wpnonce'], 'wpc_feedback_wizard' . $wp_query->query_vars['wpc_page_value'] ) ) {

                                if ( isset( $_POST['feedback'] ) && is_array( $_POST['feedback'] ) ) {

                                    $feedback = $_POST['feedback'];

                                    $client_id      = get_current_user_id();
                                    $business_name  = get_user_meta( $client_id, 'wpc_cl_business_name', true );

                                    $wizard_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_wizards WHERE wizard_id = %d", $feedback['wizard_id'] ), ARRAY_A );
                                    $message = '';
                                    $message .= '<div><h2>Wizard Name: ' . stripslashes( $wizard_data['name'] ) . ' (' . $wizard_data['version'] . ')</h2>';
                                    $message .= '<div><h3>Client: ' . get_userdata( $client_id )->get( 'user_login' ) . ' (' . $business_name . ')</h3>';

                                    if ( isset( $feedback['items'] ) && 0 < count( $feedback['items'] ) ) {
                                        foreach( $feedback['items'] as $item_id => $value ) {
                                            $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_items WHERE item_id = %d", $item_id  ), ARRAY_A );
                                            $item_feedback = ( is_array( $value['item_feedback'] ) ) ? implode( ', ', $value['item_feedback'] ) : $value['item_feedback'];

                                            $message .= '<p><b>Item Name:</b> ' . stripslashes( $item['name'] ) . '</p>';
                                            $message .= '<p><b>Item Description:</b> ' . stripslashes( $item['description'] ) . '</p>';

                                            $message .= '<p><b>Client Feedback:</b> ' . $item_feedback . '</p>';
                                            $message .= '<p><b>Client Comment:</b> ' . stripslashes( $value['item_comment'] ) . '</p>';
                                            $message .= '<br>';
                                        }
                                    }

                                    $message .= '<br><p><b>Final Client Comment:</b> ' . stripslashes( $feedback['final_comment'] ) . '</p>';
                                    $message .= '</div>';

                                    //send email to admin
                                    $subject = 'Client ' . get_userdata( $client_id )->get( 'user_login' ) . ' left Feedback for "' . stripslashes( $wizard_data['name'] ) . '" wizard.';


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

                                    foreach( $emails_array as $to_email ) {
                                        WPC()->wpc_mail( $to_email, $subject, $message );
                                    }

                                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_feedback_results SET
                                        wizard_id = %d,
                                        wizard_name = '%s',
                                        wizard_version = '%s',
                                        result_text = '%s',
                                        client_id = %d,
                                        time = '%s'
                                        ",$wizard_data['wizard_id'], $wizard_data['name'], $wizard_data['version'], $message, $client_id, time() ) );

                                    wp_redirect( WPC()->get_slug( 'feedback_wizard_sent_page_id' ) );
                                    exit;

                                }
                            }

                            $wp_query->is_page      = true;
                            $wp_query->is_home      = false;
                            $wp_query->is_singular  = true;
                            $filtered_posts[] = $post;
                            continue;

                        }
                    }
                    continue;

                }

                //add all other posts
                $filtered_posts[] = $post;

            }

            return $filtered_posts;
        }



        /*
        * Include JS\CSS
        */
        function wp_css_js() {
            global $wp_query;


            //load only on feedback wizard page
            if ( isset( $wp_query->query_vars['wpc_page'] ) && 'feedback_wizard' == $wp_query->query_vars['wpc_page'] && 0 < $wp_query->query_vars['wpc_page_value'] ) {

                wp_register_style( 'wp-client-feedback-wizard', WPC()->plugin_url . 'css/feedback_wizard.css' );
                wp_enqueue_style( 'wp-client-feedback-wizard' );

                //shutterbox init
                wp_register_script('wpc-shutter-box-script', WPC()->plugin_url . 'js/shutter-box/shutter_box_core.js');
                wp_enqueue_script('wpc-shutter-box-script');
                wp_register_style('wpc-shutter-box-style', WPC()->plugin_url . 'js/shutter-box/shutter_box.css');
                wp_enqueue_style('wpc-shutter-box-style');
            }
        }




    //end class
    }

}