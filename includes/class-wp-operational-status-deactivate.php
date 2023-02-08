<?php

/**
 * Fired during plugin deactivation.
 */
class WP_Operational_Status_Deactivate {
	public static function deactivate( $network_wide ) {
		self::plugin_deactivated();
	}

	public static function plugin_deactivated() {

		/**
		 * Remove cron job.
		 */
		wp_clear_scheduled_hook('wp_operational_status_refresh');
	}
}
