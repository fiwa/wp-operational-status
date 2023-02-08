<?php

if ( ! defined( 'WP_OPERAIONAL_STATUS_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! function_exists( 'wpos_get_monitors' ) ) {
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
		$operational_status_theme_settings = WP_Operational_Status_Helpers::get_operational_status_theme_settings();
		$monitors = $operational_status_theme_settings['monitors'];
		$logs = WP_Operational_Status_Helpers::get_operational_status_logs( $args );
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
