<?php

/**
 * The core plugin class.
 */
class WP_Operational_Status {
	protected $loader;
	protected $plugin_name;
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->version = WP_OPERAIONAL_STATUS_VERSION;
		$this->plugin_name = 'wp-operational-status';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-helpers.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-wp-operational-status-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-wp-operational-status-public.php';

		$this->loader = new WP_Operational_Status_Loader();
	}

	/**
	 * Define the locale for the plugin internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new WP_Operational_Status_i18n();

		$this->loader->add_action(
			'plugins_loaded',
			$plugin_i18n,
			'load_plugin_textdomain'
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new WP_Operational_Status_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_admin, 'register_cron_job' );
		$this->loader->add_action( 'wp_operational_status_refresh', $plugin_admin, 'run_wp_operational_status_refresh' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_settings_page' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'admin_scripts' );
		$this->loader->add_action( 'wp_ajax_wp_operational_status_admin', $plugin_admin, 'admin_actions_ajax' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {
		$plugin_public = new WP_Operational_Status_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'load_template_functions' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
