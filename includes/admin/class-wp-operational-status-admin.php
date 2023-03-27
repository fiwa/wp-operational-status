<?php

/**
 * The admin-specific functionality of the plugin.
 */
class WP_Operational_Status_Admin {
	private $plugin_name;
	private $version;
	private $replacement_variables;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $plugin_name, $version, $replacement_variables ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->replacement_variables = $replacement_variables;
	}

	/**
	 * Add plugin settings page.
	 */
	public function add_plugin_settings_page() {
		if (is_multisite() && get_current_blog_id() !== 1) {
			return;
		}

		add_menu_page(
			__( 'WP Operational Status', 'wp-operational-status' ),
			__( 'WP Operational Status', 'wp-operational-status' ),
			$this->replacement_variables['current_user_capability'],
			$this->plugin_name,
			false,
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Monitors', 'wp-operational-status' ),
			__( 'Monitors', 'wp-operational-status' ),
			$this->replacement_variables['current_user_capability'],
			$this->plugin_name,
			array( $this, 'output_admin_page' )
		);
	}

	/**
	 * Load JS and CSS used by this plugin
	 *
	 * @param string $hook_suffix
	 */
	public function admin_scripts( $hook_suffix ) {
		if ( 'toplevel_page_wp-operational-status' === strtolower( $hook_suffix ) ) {
			wp_enqueue_script(
				'wp-operational-status-admin-script',
				WP_OPERAIONAL_STATUS_PLUGIN_DIR_URL . 'js/wp-operational-status-admin.js',
				array( 'jquery' ),
				filemtime( WP_OPERAIONAL_STATUS_PLUGIN_DIR . 'js/wp-operational-status-admin.js' ),
				true,
			);

			wp_localize_script(
				'wp-operational-status-admin-script',
				'wpOperationalStatusAdminScriptL10n',
				array(
					'admin_ajax_url'          => admin_url( 'admin-ajax.php' ),
					'confirm_delete'          => __( 'Are you sure you want to delete the monitor \'{{name}}\'?', 'wp-operational-status' ),
					'error_url'               => __( 'Invalid monitor URL', 'wp-operational-status' ),
					'error_name'              => __( 'Invalid monitor name', 'wp-operational-status' ),
					'error_response_coode'    => __( 'Invalid monitor responce code', 'wp-operational-status' ),
					'empty_nonce'             => __( 'Nonce is empty', 'wp-operational-status' ),
				)
			);
		}
	}

	/**
	 * Plugin ajax.
	 */
	public function admin_actions_ajax() {
		$output = array( 'error' =>  __( 'No actions specified', 'wp-operational-status' ) );

		if ( isset( $_POST['action'] ) && 'wp_operational_status_admin' == $_POST['action'] ) {
			if ( ! empty( $_POST['do'] ) ) {
				$nonce_error = array( 'error' =>  __( 'Unable to verify nonce', 'wp-operational-status' ) );

				switch( $_POST['do'] ) {
					case 'add_monitor':
						if ( wp_verify_nonce( $_POST['_ajax_nonce'], 'wp-operational-status-add-monitor' ) ) {
							$output = $this->process_add_monitor( $_POST );
						} else {
							$output = $nonce_error;
						}
					break;
					case 'delete':
						if ( wp_verify_nonce( $_POST['_ajax_nonce'], 'wp-operational-status-delete-monitor-' . intval( $_POST['id'] ) ) ) {
							$output = $this->process_delete_monitor( $_POST );
						} else {
							$output = $nonce_error;
						}
						break;
				}
			}
		}

		echo json_encode( $output );
		exit();
	}

	/**
	 * Process delete monitor.
	 */
	private function process_delete_monitor( $params ) {
		global $wpdb;

		$monitor = $this->get_monitor( $params['url'] );

		$wpdb->operational_status_monitors = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_monitors';
		$wpdb->operational_status_log = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_log';

		$delete_monitor_sql = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->operational_status_monitors WHERE url = %s", $params['url'] ) );
		$delete_monitor_logs_sql = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->operational_status_log WHERE monitor_id = %d", $monitor->id ) );

		if ( $delete_monitor_sql && $delete_monitor_logs_sql ) {
			return array(
				'success'             => __( 'Monitor deleted', 'wp-operational-status' ),
				'deleted_monitor'     => $monitor,
			);
		} else {
			return array(
				'error'               => __( 'Error deleting monitor', 'wp-operational-status' )
			);
		}
	}

	/**
	 * Process add monitor.
	 */
	private function process_add_monitor( $params ) {
		global $wpdb;

		$html = '';
		$output = array(
			'error' =>  array(),
		);

		$url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_STRING);
		$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
		$response_code = filter_input(INPUT_POST, 'response_code', FILTER_VALIDATE_INT);

		if ( ! wp_http_validate_url( $url ) ) {
			$output['error'][] = __( 'Invalid monitor URL', 'wp-operational-status' );
		} else {
			$url = esc_url_raw( $url );
		}

		if ( empty( $name ) ) {
			$output['error'][] = __( 'Invalid monitor name', 'wp-operational-status' );
		} else {
			$name = sanitize_text_field( $name );
		}

		if ( ! is_numeric( $response_code ) ) {
			$output['error'][] = __( 'Invalid monitor responce code', 'wp-operational-status' );
		} else {
			$response_code = intval( $response_code );
		}

		if ( count( $output['error'] ) === 0 ) {
			$wpdb->operational_status_monitors = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_monitors';

			$check_duplicate = $this->get_monitor( $url );

			if ( ! empty( $check_duplicate ) ) {
				$output['error'][] = __( 'Monitor already exists', 'wp-operational-status' );
			} else {
				$wpdb->insert(
					$wpdb->operational_status_monitors,
					array(
						'url'             => $url,
						'name'            => $name,
						'response_code'   => $response_code,
						'date_time'    => current_time( 'mysql', 1 ),
					),
					array(
						'%s',
						'%s',
						'%d',
						'%s'
					)
				);

				if ( $wpdb->insert_id ) {
					$added_monitor = $this->get_monitor( $url );
					ob_start();
					$this->print_table_row( $added_monitor );
					$html = ob_get_contents();
					ob_end_clean();

					$output = array(
						'success' =>  __( 'Monitor added successfully', 'wp-operational-status' ),
						'html'    => $html,
					);
				} else {
					$output['error'][] = __( 'Unable to add monitor', 'wp-operational-status' );
				}
			}
		}

		return $output;
	}

	/**
	 * Get monitors.
	 *
	 * @return object
	 */
	public static function get_monitors() {
		global $wpdb;
		$wpdb_prefix = $wpdb->prefix;

		if (is_multisite()) {
			$main_blog_id = WP_Operational_Status_Helpers::get_main_blog_id();
			$main_blog_db_prefix = $wpdb->get_blog_prefix($main_blog_id);
			$wpdb_prefix = $main_blog_db_prefix;
		}

		$wpdb->operational_status_monitors = $wpdb_prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_monitors';

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->operational_status_monitors ORDER BY id DESC LIMIT %d", 10 ) );
	}

	/**
	 * Get monitor.
	 *
	 * @param string $url Monitor URL.
	 *
	 * @return object
	 */
	private function get_monitor( $url ) {
		global $wpdb;
		$wpdb->operational_status_monitors = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_monitors';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->operational_status_monitors WHERE url = %s", $url ) );
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
		$monitors = $this->get_monitors();

		update_option( 'wpos_cron_last_run', current_time( 'mysql', 1 ), false );

		if ( $monitors > 1 ) {
			foreach ( $monitors as $monitor ) {
				if ( ! self::ping_external_url( $monitor->url, $monitor->response_code ) ) {
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
	public function write_to_log( $monitor = null, $status = 0 ) {
		if ( empty( $monitor ) ) {
			return false;
		}

		global $wpdb;
		$wpdb->operational_status_log = $wpdb->prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_log';

		$wpdb->insert(
			$wpdb->operational_status_log,
			array(
				'monitor_id'       => intval( $monitor->id ),
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

	/**
	 * Print table row.
	 */
	private function print_table_row( $monitor ) {
		$delete_nonce = wp_create_nonce( 'wp-operational-status-delete-monitor-' . $monitor->id );
		?>
		<tr id="<?php printf( 'monitor-%s', $monitor->id ) ?>">
			<td>
				<?php echo $monitor->url; ?>
				<div class="row-actions hide-if-no-js">
					<span class="trash">
						<a
						href="#"
						class="delete"
						title="<?php _e( 'Delete', 'wp-operational-status' ); ?>"
						data-id="<?php echo $monitor->id; ?>"
						data-name="<?php echo esc_js( $monitor->name ); ?>"
						data-url="<?php echo esc_js( $monitor->url ); ?>"
						data-nonce="<?php echo $delete_nonce; ?>">
							<?php _e( 'Delete', 'wp-operational-status' ); ?>
						</a>
					</span>
				</div>
			</td>
			<td><?php echo $monitor->name; ?></td>
			<td><?php echo $monitor->response_code; ?></td>
			<td><?php echo $monitor->date_time; ?></td>
		</tr>
		<?php
	}

	/**
	 * Output admin menu page.
	 */
	public function output_admin_page() {
		$monitors = $this->get_monitors();
		?>
			<div class="wrap">
				<h1>
					<?php _e( 'WP Operational Status', 'wp-operational-status' ); ?>
				</h1>

				<div id="<?php printf( '%s-js-messages', $this->plugin_name ); ?>" style="display: none;"></div>

				<h2>
					<?php _e( 'Add monitor', 'wp-operational-status' ); ?>
				</h2>

				<form
				id="<?php printf( '%s-add-monitor', $this->plugin_name ); ?>"
				action="<?php echo admin_url( sprintf( 'admin.php?page=%s', $this->plugin_name ) ); ?>"
				method="post">
					<?php wp_nonce_field( 'wp-operational-status-add-monitor', 'wp-operational-status-add-monitor-nonce' ); ?>

					<table class="widefat">
						<tbody>
							<tr>
								<th scope="row" style="width: 20%">
									<label for="monitor_url"><?php _e( 'URL', 'wp-operational-status' ); ?></label>
								</th>
								<td style="width: 80%">
									<input
									type="url"
									id="monitor_url"
									name="monitor_url"
									required>
								</td>
							</tr>
							<tr>
								<th scope="row" style="width: 20%">
									<label for="monitor_name"><?php _e( 'Name', 'wp-operational-status' ); ?></label>
								</th>
								<td style="width: 80%">
									<input
									type="text"
									id="monitor_name"
									name="monitor_name"
									required>
								</td>
							</tr>
							<tr>
								<th scope="row" style="width: 20%">
									<label for="monitor_reponse_code"><?php _e( 'Valid response code', 'wp-operational-status' ); ?></label>
								</th>
								<td style="width: 80%">
									<input
									type="number"
									id="monitor_reponse_code"
									name="monitor_reponse_code"
									required>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr class="alternate">
								<td>&nbsp;</td>
								<td>
									<input
									type="submit"
									class="button"
									name="<?php printf( '%s_add_monitor_submit', str_replace( '-', '_', $this->plugin_name ) ); ?>"
									id="<?php printf( '%s_add_monitor_submit', str_replace( '-', '_', $this->plugin_name ) ); ?>"
									value="<?php _e( 'Add monitor', 'wp-operational-status' ); ?>" />
								</td>
							</tr>
						</tfoot>
					</table>
				</form>

				<h3><?php _e( 'Existing monitors', 'wp-operational-status' ); ?></h3>
				<table id="<?php printf( '%s-current-monitors', $this->plugin_name ); ?>" class="widefat <?php printf( '%s-current-monitors-table', $this->plugin_name ); ?>">
				<thead>
				<tr>
						<th><?php _e( 'URL', 'wp-operational-status' ); ?></th>
						<th><?php _e( 'Name', 'wp-operational-status' ); ?></th>
						<th><?php _e( 'Response code', 'wp-operational-status' ); ?></th>
						<th><?php _e( 'Date added', 'wp-operational-status' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $monitors ) ) : ?>
							<?php foreach (  $monitors as $monitor ) : ?>
								<?php $this->print_table_row( $monitor ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
				</table>
			</div>
		<?php
	}
}
