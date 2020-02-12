<?php

if ( !class_exists( "WPC_FW_User_Shortcodes" ) ) {

    class WPC_FW_User_Shortcodes extends WPC_FW_Common {

        /**
        * constructor
        **/
        function fw_shortcodes_construct() {


        }


        /**
        * Shortcode for Show feedback wizard list
        **/
        function shortcode_wizards_list($atts, $contents = null) {
            global $wpdb;

            //checking access
            $user_id = WPC()->checking_page_access();

            if ( false === $user_id ) {
                return '';
            }

            $post_contents = "";

            //get wizards
            //to delete $wizard_ids = $wpdb->get_col( "SELECT wizard_id FROM {$wpdb->prefix}wpc_client_feedback_wizards WHERE clients_id LIKE '%#$user_id,%' ");
            $wizard_ids = WPC()->assigns()->get_assign_data_by_assign( 'feedback_wizard', 'client', $user_id );
            //get clientpages by groups_id
            $client_groups_id = WPC()->groups()->get_client_groups_id( $user_id );
            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach ( $client_groups_id as $groups_id )  {
                    $groups_wizard_ids = WPC()->assigns()->get_assign_data_by_assign( 'feedback_wizard', 'circle', $groups_id );
                    // to delete $groups_wizard_ids = $wpdb->get_col( "SELECT wizard_id FROM {$wpdb->prefix}wpc_client_feedback_wizards WHERE groups_id LIKE '%#$groups_id,%' " );
                    $wizard_ids = array_merge( $wizard_ids, $groups_wizard_ids );
                }
            }

            $wizard_ids = array_unique( $wizard_ids );



            if ( is_array( $wizard_ids ) && 0 < count( $wizard_ids ) ) {
                $wizards = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_feedback_wizards WHERE wizard_id IN ('" . implode( "','", $wizard_ids ) . "') ", ARRAY_A );

                foreach( $wizards as $wizard ) {
                    //check if not sent feedback for this version
                    $sql = "SELECT result_id FROM {$wpdb->prefix}wpc_client_feedback_results WHERE wizard_id = %d AND client_id = %d AND wizard_version = '%s' ";
                    $result_id = $wpdb->get_var( $wpdb->prepare( $sql, $wizard['wizard_id'], $user_id, $wizard['version'] ) );
                    if ( empty( $result_id ) || 0 > $result_id  ) {
                        //make link
                        if ( WPC()->permalinks ) {
                            $wizard_link = WPC()->get_slug( 'feedback_wizard_page_id' ) . $wizard['wizard_id'];
                        } else {
                            $wizard_link = add_query_arg( array( 'wpc_page' => 'feedback_wizard', 'wpc_page_value' => $wizard['wizard_id'] ), WPC()->get_slug( 'feedback_wizard_page_id', false ) );
                        }

                        $post_contents .= '<a href="' . $wizard_link . '">' . nl2br( stripslashes( $wizard['name'] ) ) . '</a><br/>';
                    }
                }

            } else if( isset( $atts['empty_text'] ) ) {
                $post_contents .= $atts['empty_text'];
            }
            $post_contents .= '<style type="text/css">.navigation .alignleft, .navigation .alignright {display:none;}</style>';

            $post_contents = '<div class="wpc_client_wizards_list">' . $post_contents . '</div>';


            return $post_contents;
        }


        /**
        * Shortcode for Show feedback wizard
        **/
        function shortcode_feedback_wizard($atts, $contents = null) {

            //checking access
            $user_id = WPC()->checking_page_access();

            if ( false === $user_id ) {
                return '';
            }

            wp_register_style( 'wpc_fw_form_style', $this->extension_url . 'css/fw_form.css', array(), WPC_FW_VER );
            wp_enqueue_style( 'wpc_fw_form_style', false, array(), WPC_FW_VER );

            return ( include_once $this->extension_dir . 'includes/user/fw_form.php' );
        }


    //end class
    }
}
