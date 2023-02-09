<?php

/**
 * Fired during plugin activation.
 */
class WP_Operational_Status_Activate {
	public static function activate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			wp_die(__('WP Operational Status: This plugin cannot be activated network wide.', 'wp-operational-status'));
		} else {
			self::plugin_activated();
		}
	}

	public static function plugin_activated() {
		add_option( 'wpos_cron_last_run', '' , '', 'no' );

		self::create_log_table();
	}

	/**
	 * Create the database log table.
	 */
	public static function create_log_table() {
		global $wpdb;

		$sql = array();
		$wpdb->operational_status_monitors = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_monitors';
		$wpdb->operational_status_log = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_log';
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE IF NOT EXISTS $wpdb->operational_status_monitors (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			url varchar(255) NOT NULL,
			name varchar(255) NOT NULL,
			response_code varchar(255) NOT NULL,
			date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql[] = "CREATE TABLE IF NOT EXISTS $wpdb->operational_status_log (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			monitor_id bigint(20) NOT NULL,
			date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			status varchar(255) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}
}
