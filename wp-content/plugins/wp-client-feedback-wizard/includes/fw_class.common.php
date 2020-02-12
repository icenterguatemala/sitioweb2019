<?php


if ( !class_exists( "WPC_FW_Common" ) ) {

    class WPC_FW_Common {

        var $extension_dir;
        var $extension_url;

        /**
        * constructor
        **/
        function fw_common_construct() {

            $this->extension_dir = WPC()->extensions()->get_dir( 'fw' );
            $this->extension_url = WPC()->extensions()->get_url( 'fw' );

            //add rewrite rules
            add_filter( 'rewrite_rules_array', array( &$this, '_insert_rewrite_rules' ) );

            add_filter( 'wpc_shortcode_data_array', array( &$this, 'shortcode_data_array' ) );

        }

        function shortcode_data_array( $array ) {
            $array['wpc_client_feedback_wizards_list'] = array(
                'title'         => __( 'Feedback Wizard: Wizards List', WPC_CLIENT_TEXT_DOMAIN ),
                'name'         => 'feedback_wizards_list',
                'callback'      => array( &$this, 'shortcode_wizards_list' ),
                'categories'    => 'content',
                'hub_template' => array(
                    'text'    => __( 'Feedback Wizard List', WPC_CLIENT_TEXT_DOMAIN ),
                ),
                'attributes'    => array(
                    'empty_text' => array(
                        'label' => __( 'Empty List Text', WPC_CLIENT_TEXT_DOMAIN ),
                        'type'  => 'text',
                        'value' => '',
                    )
                )
            );
            $array['wpc_client_feedback_wizard'] = array(
                'callback'      => array( &$this, 'shortcode_feedback_wizard' )
            );
            return $array;
        }


        /**
         * Adding a new rule
         **/
        function _insert_rewrite_rules( $rules ) {
            $newrules = array();

            //feedback pages
            $newrules[WPC()->get_slug( 'feedback_wizard_page_id', false, false ) . '/(\d*)/?$'] = 'index.php?wpc_page=feedback_wizard&wpc_page_value=$matches[1]';

            return $newrules + $rules;
        }

    //end class
    }
}
