<?php
if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! function_exists( 'wp_doing_ajax' ) ) {
    /**
     * Determines whether the current request is a WordPress Ajax request.
     *
     * @since 4.7.0
     *
     * @return bool True if it's a WordPress Ajax request, false otherwise.
     */
    function wp_doing_ajax() {
        /**
         * Filters whether the current request is a WordPress Ajax request.
         *
         * @since 4.7.0
         *
         * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
         */
        return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
    }
}
