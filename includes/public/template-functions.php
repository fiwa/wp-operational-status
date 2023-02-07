<?php

if ( ! defined( 'WP_OPERAIONAL_STATUS_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! function_exists( 'wpos_get_monitors' ) ) {
	function wpos_get_monitors() {
		$monitors = WP_Operational_Status_Helpers::get_monitors();
		$logs = WP_Operational_Status_Helpers::get_operational_status_logs();
		$return = array();

		foreach ( $monitors as $monitor ) {
			$monitor_logs = array_filter( $logs, function( $log ) use ( $monitor ) {
				return $log->monitor_url === $monitor['wpos_url'];
			} );

			$return[] = array(
				'instance' => $monitor,
				'logs' => $monitor_logs,
				'last_log' => array_shift( $monitor_logs ),
			);
		}

		return $return;
	}
}
