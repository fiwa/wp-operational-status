<?php

class WP_Operational_Status_Helpers {
	private $wpos_theme_settings = array();

	public static function get_monitors() {
		return carbon_get_theme_option( 'wpos_monitors' );
	}

	public static function get_operational_status_options() {
		global $wpos_theme_settings;

		if ( ! isset( $wpos_theme_settings[ 'monitors' ] ) ) {
			$wpos_theme_settings[ 'monitors' ] = self::get_monitors();
		}

		return $wpos_theme_settings;
	}

	public static function get_operational_status_logs() {
		global $wpdb;

		$wpdb->operational_status_log = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE;

		return $wpdb->get_results( "SELECT * FROM $wpdb->operational_status_log ORDER BY id DESC LIMIT 10" );
	}
}
