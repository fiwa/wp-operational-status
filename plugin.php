<?php

/**
 * Plugin Name:       WP Operational Status
 * Plugin URI:        https://github.com/fiwa/wp-operational-status
 * Description:       A plugin that displays the operational status of one or more websites.
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
 * Currently plugin version.
 */
define( 'WP_OPERAIONAL_STATUS_VERSION', '0.1.0' );

/**
 * Plugin directory path
 */
define( 'WP_OPERAIONAL_STATUS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-operational-status.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_wp_operational_status() {

	$plugin = new WP_Operational_Status();
	$plugin->run();

}
run_wp_operational_status();
