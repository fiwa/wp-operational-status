<?php

class WP_Operational_Status_Deactivate {
	public static function deactivate() {
		// Remove cron job
		wp_clear_scheduled_hook('wp_operational_status_refresh');
	}
}
