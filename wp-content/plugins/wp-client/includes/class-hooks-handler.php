<?php

// Exit if executed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPC_Hooks' ) ) :

final class WPC_Hooks {

    /**
     * The single instance of the class.
     *
     * @var WPC_Hooks
     * @since 4.5
     */
    protected static $_instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of WPC_Hooks is loaded or can be loaded.
     *
     * @since 4.5
     * @static
     * @return WPC_Hooks - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
    * PHP 5 constructor
    **/
    function __call( $name, $args ) {

        //for static methods
        if ( strpos( $name, '>>' ) ) {
            $func = explode( '>>', $name );
            $class = $func[0];

            return forward_static_call_array( array( $class, $func[1] ), $args );
        }

        $func = explode( '->', $name );
        $class = $func[0];

        if ( method_exists( $class, 'instance' ) ) {
            $object = $class::instance();
        } else {
            $object = new $class();
        }

        return call_user_func_array( array( $object, $func[1] ), $args );
    }

} //end class

endif;