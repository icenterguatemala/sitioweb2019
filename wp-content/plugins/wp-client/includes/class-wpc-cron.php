<?php
if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly


if ( ! class_exists( 'WPC_Cron' ) ) :

final class WPC_Cron {

	/**
	 * The single instance of the class.
	 *
	 * @var WPC_Cron
	 * @since 4.5
	 */
	protected static $_instance = null;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of WPC_Cron is loaded or can be loaded.
	 *
	 * @since 4.5
	 * @static
	 * @return WPC_Cron - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	function wpc_cron_add_periods( $schedules ) {
        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );

        if( isset( $wpc_file_sharing['ftp_synchronize'] ) && $wpc_file_sharing['ftp_synchronize'] == 'yes' && !empty( $wpc_file_sharing['ftp_synchronize_period'] ) ) {

            $schedules['wpc_ftp_synchronize'] = array(
                'interval' => $wpc_file_sharing['ftp_synchronize_period'] * 60,
                'display' => __( 'FTP Synchronization' )
            );

        }

        return $schedules;
    }

	function get_core_crons() {
        $crons = array();

        $wpc_file_sharing = WPC()->get_settings( 'file_sharing' );
        if( isset( $wpc_file_sharing['ftp_synchronize'] ) && 'yes' == $wpc_file_sharing['ftp_synchronize']
            && !empty( $wpc_file_sharing['ftp_synchronize_period'] ) ) {
            $crons['wpc_client_ftp_synchronization'] = array( 'period' => 'wpc_ftp_synchronize' );
        }
        $crons['wpc_payments_core_daily'] = array( 'period' => 'daily' );

        return $crons;
    }

    function add_crons( $crons ) {
        foreach ( $crons as $tag => $value ) {
            if ( ! wp_next_scheduled( $tag ) ) {
                $time = ! empty( $value['time'] ) ? $value['time'] : time();
                wp_schedule_event( $time, $value['period'], $tag );
            }
        }
    }

    function main_cron() {
        $all_crons = $this->get_core_crons();

        $all_crons = apply_filters( 'wpc_all_crons', $all_crons );

        $this->add_crons( $all_crons );
    }
}

endif;