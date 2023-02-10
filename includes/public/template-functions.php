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
		$safe_args = null;
		$default_args = array(
			'number_of_posts' => 10,
		);

		if ( $args ) {
			foreach (  $args as $key => $value ) {
				if ( ( $key === 'number_of_posts' ) && is_numeric( $value ) && ( $value > 0 ) ) {
					$safe_args[$key] = $value;
				}
			}
		}

		$args = wp_parse_args( $safe_args, $default_args );
		$return = array();
		$monitors = WP_Operational_Status_Admin::get_monitors();
		$logs = WP_Operational_Status_Helpers::get_operational_status_logs( $args );

		$cron_last_run = get_option( 'wpos_cron_last_run' );
		$cron_schedule = wp_get_schedule( 'wp_operational_status_refresh' );
		$cron_schedules = wp_get_schedules();
		$cron_interval = array_key_exists($cron_schedule, $cron_schedules) ? $cron_schedules[$cron_schedule]['interval'] : 0;

		foreach ( $monitors as $monitor ) {
			$status = 1;
			$monitor_logs = array_filter( $logs, function( $log ) use ( $monitor ) {
				return $log->monitor_id === $monitor->id;
			} );

			$last_log = array_shift( $monitor_logs );

			// If the last log is older than the cron run, then the monitor is up.
			if ( isset( $last_log ) && ! empty( $last_log ) ) {
				if ( strtotime( $cron_last_run ) >  strtotime( $last_log->date_time )) {
					$status = 1;
				} else {
					$status = 0;
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
