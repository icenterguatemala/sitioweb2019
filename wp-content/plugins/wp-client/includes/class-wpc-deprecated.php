<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Deprecated' ) ) :

class WPC_Deprecated {



    /**
     *  convert to time
     *
     * @deprecated 4.5.0
     * @deprecated Not Uses
     * @see
     **/
    static function convert_to_time( $date, $format ) {
        $keys = array(
            'Y' => array('year', '\d{4}'),
            'y' => array('year', '\d{2}'),
            'm' => array('month', '\d{2}'),
            'n' => array('month', '\d{1,2}'),
            'M' => array('month', '[A-Z][a-z]{3}'),
            'F' => array('month', '[A-Z][a-z]{2,8}'),
            'd' => array('day', '\d{2}'),
            'j' => array('day', '\d{1,2}'),
            'D' => array('day', '[A-Z][a-z]{2}'),
            'l' => array('day', '[A-Z][a-z]{6,9}'),
            'u' => array('hour', '\d{1,6}'),
            'h' => array('hour', '\d{2}'),
            'H' => array('hour', '\d{2}'),
            'g' => array('hour', '\d{1,2}'),
            'G' => array('hour', '\d{1,2}'),
            'i' => array('minute', '\d{2}'),
            's' => array('second', '\d{2}')
        );

        $regex = '';
        $chars = str_split($format);
        foreach ( $chars AS $n => $char ) {
            $lastChar = isset($chars[$n-1]) ? $chars[$n-1] : '';
            $skipCurrent = '\\' == $lastChar;
            if ( !$skipCurrent && isset($keys[$char]) ) {
                $regex .= '(?P<'.$keys[$char][0].'>'.$keys[$char][1].')';
            }
            else if ( '\\' == $char ) {
                $regex .= $char;
            }
            else {
                $regex .= preg_quote($char);
            }
        }

        $dt = array();
        if( preg_match('#^'.$regex.'$#', $date, $dt) ) {
            foreach ( $dt as $k => $v ){
                if ( is_int($k) ){
                    unset($dt[$k]);
                }
            }

            if( !( !empty( $dt['month'] ) && !empty( $dt['day'] ) && !empty( $dt['year'] ) && checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) ){
                return false;
            }

            $dt = array_merge( array(
                'hour'   => 0,
                'minute' => 0,
                'second' => 0
            ), $dt );

            return mktime( $dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year'] );
        } else {
            return false;
        }
    }

}

endif;