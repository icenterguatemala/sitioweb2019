<?php


if ( !class_exists( "WPC_TLC_Common" ) ) {

    class WPC_TLC_Common {

        var $extension_dir;
        var $extension_url;
        /**
        * constructor
        **/
        function common_construct() {

            $this->extension_dir = WPC()->extensions()->get_dir( 'tlc' );
            $this->extension_url = WPC()->extensions()->get_url( 'tlc' );

        }

        /**
         * Get array for select of period
         */
        function get_array_periods() {
            $array_periods = array(
                'day' => __( 'Day(s)', WPC_CLIENT_TEXT_DOMAIN ),
                'week' => __( 'Week(s)', WPC_CLIENT_TEXT_DOMAIN ),
                'month' => __( 'Month(s)', WPC_CLIENT_TEXT_DOMAIN ),
                'year' => __( 'Year(s)', WPC_CLIENT_TEXT_DOMAIN ),
            );
            return $array_periods;
        }


    //end class
    }
}