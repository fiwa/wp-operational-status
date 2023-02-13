<?php

if ( ! defined( 'WP_OPERAIONAL_STATUS_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Register template functions for the plugin.
 */
if ( ! function_exists( 'wpos_get_monitors' ) ) {
	/**
	 * Get monitors and logs.
	 *
	 * @param array $args.
	 *
	 * @return array
	 */
	function wpos_get_monitors( $args = null ) {
		$return = array();
		$monitors = WP_Operational_Status_Admin::get_monitors();
		$logs = WP_Operational_Status_Helpers::get_operational_status_logs( $args );

		$cron_last_run = get_option( 'wpos_cron_last_run' );
		$cron_schedule = wp_get_schedule( 'wp_operational_status_refresh' );
		$cron_schedules = wp_get_schedules();
		$cron_interval = array_key_exists( $cron_schedule, $cron_schedules ) ? $cron_schedules[$cron_schedule]['interval'] : 0;

		foreach ( $monitors as $monitor ) {
			$monitor_logs = array();
			$last_log = array();
			$status = 1;

			if ( $logs ) {
				$monitor_logs = array_filter( $logs, function( $log ) use ( $monitor ) {
					return $log->monitor_id === $monitor->id;
				} );

				$last_log = array_shift( $monitor_logs );

				// If the last log is older than the cron run, then the monitor is up.
				if ( isset( $last_log ) && ! empty( $last_log ) ) {
					if ( strtotime( $cron_last_run ) >  strtotime( $last_log->date_time ) ) {
						$status = 1;
					} else {
						$status = 0;
					}
				}
			}

			$return[] = array(
				'instance' => $monitor,
				'logs' => $monitor_logs,
				'last_log' => $last_log,
				'status' => $status,
			);
		}

		return $return;
	}
}
