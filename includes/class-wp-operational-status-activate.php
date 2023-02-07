<?php

class WP_Operational_Status_Activate {
	public static function activate() {
		if ( is_multisite() && $network_wide ) {
			$ms_sites = wp_get_sites();

			if ( 0 < sizeof( $ms_sites ) ) {
				foreach ( $ms_sites as $ms_site ) {
					switch_to_blog( $ms_site['blog_id'] );
					self::plugin_activated();
				}
			}

			restore_current_blog();
		} else {
			self::plugin_activated();
		}
	}

	public static function plugin_activated() {
		global $wpdb;

		$sql = array();
		$wpdb->operational_status_log = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE;
		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE IF NOT EXISTS $wpdb->operational_status_log (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			monitor_url varchar(255) NOT NULL,
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
