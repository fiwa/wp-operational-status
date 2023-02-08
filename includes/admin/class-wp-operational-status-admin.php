<?php

/**
 * The admin-specific functionality of the plugin.
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WP_Operational_Status_Admin {
	private $plugin_name;
	private $version;
	private $replacement_variables = array(
		'current_user_capability' => 'manage_options',
		'cron_schedule' => 'hourly',
	);

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Boot the Carbon Fields library.
	 */
	public function load_carbon_fields() {
		require_once WP_OPERAIONAL_STATUS_PLUGIN_DIR . 'vendor/autoload.php';
		\Carbon_Fields\Carbon_Fields::boot();
	}

	/**
	 * Add plugin settings.
	 *
	 * Carbon Fields is used in order to register admin pages and fields.
	 *
	 * @link https://docs.carbonfields.net/learn/
	 */
	public function add_plugin_settings_page() {
		$current_user_capability = apply_filters( 'wpos_current_user_capability', $this->replacement_variables['current_user_capability'] );

		$top_level_options_container = Container::make( 'theme_options', __( 'WP Operational Status', 'wp-operational-status' ) )
			->set_page_file( 'wpos_settings' )
			->set_icon( 'dashicons-megaphone' )
			->where( 'current_user_capability', '=', $current_user_capability )
			->add_fields( array(
				Field::make( 'complex', 'wpos_monitors', __( 'Monitors', 'wp-operational-status' ) )
					->set_layout( 'grid' )
					->set_max( 2 )
					->setup_labels( array(
						'plural_name' => __( 'Monitors', 'wp-operational-status' ),
						'singular_name' => __( 'Monitor', 'wp-operational-status' ),
					) )
					->set_duplicate_groups_allowed( false )
					->add_fields( array(
						Field::make( 'text', 'wpos_url', __( 'URL', 'wp-operational-status' ) )
							->set_required( true )
							->set_attribute( 'type', 'url' ),
						Field::make( 'text', 'wpos_url_nice_name', __( 'Nicename', 'wp-operational-status' ) )
							->set_required( true ),
						Field::make( 'text', 'wpos_valid_response_code', __( 'Valid response code', 'wp-operational-status' ) )
							->set_required( true )
							->set_attribute( 'type', 'number' ),
					) )
					->set_header_template( '
						<% if (wpos_url_nice_name) { %>
							<%- wpos_url_nice_name %>
						<% } %>
					' )
			) );
	}

	/**
	 * Register cron job.
	 */
	public function register_cron_job() {
		if ( ! wp_next_scheduled( 'wp_operational_status_refresh' ) ) {
			$cron_schedule = apply_filters( 'wpos_cron_schedule', $this->replacement_variables['cron_schedule'] );

			wp_schedule_event( time(), $cron_schedule, 'wp_operational_status_refresh' );
		}
	}

	/**
	 * Run cron job.
	 */
	public function run_wp_operational_status_refresh() {
		$operational_status_theme_settings = WP_Operational_Status_Helpers::get_operational_status_theme_settings();
		$monitors = $operational_status_theme_settings['monitors'];

		if ( $monitors > 1 ) {
			foreach ( $monitors as $monitor ) {
				if ( self::ping_external_url( $monitor['wpos_url'], $monitor['wpos_valid_response_code'] ) ) {
					self::write_to_log( $monitor, 1 );
				} else {
					self::write_to_log( $monitor, 0 );
				}
			}
		} else {
			error_log( 'WP_Operational_Status_Admin::get_monitors() - No monitors found' );
		}
	}

	/**
	 * Write status to log table.
	 */
	public function write_to_log( $monitor = array(), $status = 0 ) {
		if ( ! is_array( $monitor ) ) {
			return false;
		}

		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE,
			array(
				'monitor_url'      => $monitor['wpos_url'],
				'date_time'        => current_time( 'mysql', 1 ),
				'status'           => $status,
			),
			array(
				'%s',
				'%s',
				'%d',
			),
		);
	}

	/**
	 * Ping external URL.
	 *
	 * @param string $url URL to ping.
	 * @param int    $valid_response_code Valid response code.
	 *
	 * @return bool
	 */
	public function ping_external_url( $url = null, $valid_response_code = 200 ) {
		if ( ! $url ) {
			return false;
		}

		try {
			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 45,
				),
			);

			$response_message = wp_remote_retrieve_response_message( $response );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( ( int ) $valid_response_code !== $response_code ) {
				return false;
			}

			return true;
		} catch ( Exception $e ) {
			error_log( json_encode( $e ) );
			return false;
		}
	}
}
