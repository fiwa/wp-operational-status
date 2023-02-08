<?php

/**
 * Define the helper functionality.
 */
class WP_Operational_Status_Helpers {
	private $wpos_theme_settings = array();

	/**
	 * Return monitors.
	 */
	public static function get_monitors() {
		return carbon_get_theme_option( 'wpos_monitors' );
	}

	/**
	 * Return plugin theme settings.
	 */
	public static function get_operational_status_theme_settings() {
		global $wpos_theme_settings;

		if ( ! isset( $wpos_theme_settings[ 'monitors' ] ) ) {
			$wpos_theme_settings[ 'monitors' ] = self::get_monitors();
		}

		return $wpos_theme_settings;
	}

	/**
	 * Return log entries from the database.
	 *
	 * @param array $args.
	 */
	public static function get_operational_status_logs( $args = null ) {
		global $wpdb;

		$default_args = array(
			'number_of_posts' => 10,
		);
		$args = wp_parse_args( $args, $default_args );
		$wpdb->operational_status_log = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->operational_status_log ORDER BY id DESC LIMIT %d" ,
				$args['number_of_posts']
			)
		);
	}
}
