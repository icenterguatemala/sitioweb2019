<?php

if ( !class_exists( 'WPC_FW_Admin_Common' ) ) {

    class WPC_FW_Admin_Common extends WPC_FW_Common {


        /**
        * constructor
        **/
        function fw_admin_common_construct() {

            //add ez hub settings
            add_filter( 'wpc_client_ez_hub_feedback_wizards_list', array( &$this, 'add_ez_hub_settings' ), 12, 4 );
            add_filter( 'wpc_client_get_ez_shortcode_feedback_wizards_list', array( &$this, 'get_ez_shortcode_feedback_wizards_list' ), 10, 2 );
            add_filter( 'wpc_client_get_shortcode_elements', array( &$this, 'get_shortcode_element' ), 10 );
            add_filter( 'wp_client_capabilities_maps', array( &$this, 'add_capabilities_maps' ) );

            add_filter( 'wpc_client_pre_set_pages_array', array( &$this, 'pre_set_pages' ) );
            //add_action( 'admin_enqueue_scripts', array( &$this, 'include_css_js' ), 100 );
        }


        /*
        * Pre set pages
        */
        function pre_set_pages( $wpc_pre_pages_array ) {
            //pre set pages
            $wpc_pages = array(
                array(
                    'title'     => __( 'Feedback Wizard', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Feedback Wizard',
                    'desc'      => __( 'Page content: [wpc_client_feedback_wizard]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'feedback_wizard_page_id',
                    'old_id'    => 'feedback_wizard',
                    'shortcode' => true,
                    'content'   => '[wpc_client_feedback_wizard]',
                ),
                array(
                    'title'     => __( 'Feedback Wizard List', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Feedback Wizard List',
                    'desc'      => __( 'Page content: [wpc_client_feedback_wizards_list]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'feedback_wizard_list_page_id',
                    'old_id'    => '',
                    'shortcode' => true,
                    'content'   => '[wpc_client_feedback_wizards_list]',
                ),
                array(
                    'title'     => __( 'Feedback Wizard Sent', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Feedback Wizard Sent',
                    'desc'      => __( 'Page content: ', WPC_CLIENT_TEXT_DOMAIN ) . htmlspecialchars( '<p>Thank you for your feedback.</p>' ),
                    'id'        => 'feedback_wizard_sent_page_id',
                    'old_id'    => 'feedback_wizard_sent',
                    'shortcode' => false,
                    'content'   => '<p>Thank you for your feedback.</p>',
                ),
            );

            if ( is_array( $wpc_pre_pages_array ) ) {
                $wpc_pre_pages_array = array_merge( $wpc_pre_pages_array, $wpc_pages );
            } else {
                $wpc_pre_pages_array = $wpc_pages;
            }

            return $wpc_pre_pages_array;
        }


        /*
        * add capability for maneger
        */
        function add_capabilities_maps( $capabilities_maps ) {

            $additional_capabilities = array(
                'wpc_manager' => array(
                    'variable' => array(
                        'wpc_modify_feedback_wizards'   => array( 'cap' => false, 'label' => __( "Modify Feedback Wizards", WPC_CLIENT_TEXT_DOMAIN ) ),
                        'wpc_modify_feedback_items'     => array( 'cap' => false, 'label' => __( "Modify Feedback Items", WPC_CLIENT_TEXT_DOMAIN ) ),
                        'wpc_show_feedback_results'     => array( 'cap' => false, 'label' => __( "Show Feedback Results", WPC_CLIENT_TEXT_DOMAIN ) ),
                    )
                ),
            );
            
            return WPC()->admin()->merge_capabilities( $capabilities_maps, $additional_capabilities );
        }


        /*function include_css_js() {
            if ( isset( $_GET['page'] ) && 'wpclients_feedback_wizard' == $_GET['page'] ) {
                wp_localize_script( 'jquery', 'wpc_assign_popup', array(
                    'wpc_ajax_prefix' => 'fw',
                ));
            }
        }*/


        /*
        * Add ez hub settings
        */
        function add_ez_hub_settings( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {
            $title = __( 'Feedback Wizard List', WPC_CLIENT_TEXT_DOMAIN ) ;
            $text_copy = '{feedback_wizards_list_' . $item_number . '}' ;

            ob_start();
            ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="feedback_wizards_list_text_<?php echo $item_number ?>"><?php _e( 'Text: "Feedback Wizard List"',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][feedback_wizards_list][text]" id="feedback_wizards_list_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Feedback Wizard List', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo WPC()->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <input type="hidden" name="hub_settings[<?php echo $item_number ?>][feedback_wizards_list][hidden]">
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
        <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez shortcode
        */
        function get_ez_shortcode_feedback_wizards_list( $tabs_items, $hub_settings = array() ) {

            $temp_arr = array();
            $temp_arr['menu_items']['feedback_wizards_list'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            $temp_arr['page_body'] = '[wpc_client_feedback_wizards_list ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * get shortcode element
        */
        function get_shortcode_element( $elements ) {
            $elements['feedback_wizards_list'] = __( 'Feedback Wizard List', WPC_CLIENT_TEXT_DOMAIN );
            return $elements;
        }


    //end class
    }

}
