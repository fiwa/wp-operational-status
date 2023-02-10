<?php

/**
 * Plugin Name:       WP Operational Status
 * Plugin URI:        https://github.com/fiwa/wp-operational-status
 * Description:       A plugin that logs the operational status of one or more websites.
 * Version:           0.1.0
 * Author:            Filip Wastman
 * Author URI:        https://aventyret.com
 * Text Domain:       wp-operational-status
 * Domain Path:       /languages
 */

 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin constants.
 */
define( 'WP_OPERAIONAL_STATUS_VERSION', '0.1.0' );
define( 'WP_OPERAIONAL_STATUS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_OPERAIONAL_STATUS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX', 'operational_status' );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_operational_status($network_wide) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-operational-status-activate.php';
	WP_Operational_Status_Activate::activate($network_wide);
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_operational_status($network_wide) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-operational-status-deactivate.php';
	WP_Operational_Status_Deactivate::deactivate($network_wide);
}

register_activation_hook( __FILE__, 'activate_wp_operational_status' );
register_deactivation_hook( __FILE__, 'deactivate_wp_operational_status' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-operational-status.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_operational_status() {
	$plugin = new WP_Operational_Status();
	$plugin->run();
}
run_wp_operational_status();
