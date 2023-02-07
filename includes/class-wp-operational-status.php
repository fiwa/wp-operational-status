<?php

class WP_Operational_Status {
	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version = WP_OPERAIONAL_STATUS_VERSION;
		$this->plugin_name = 'wp-operational-status';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-helpers.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-wp-operational-status-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-wp-operational-status-public.php';

		$this->loader = new WP_Operational_Status_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new WP_Operational_Status_i18n();

		$this->loader->add_action(
			'plugins_loaded',
			$plugin_i18n,
			'load_plugin_textdomain'
		);
	}

	private function define_admin_hooks() {
		$plugin_admin = new WP_Operational_Status_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'after_setup_theme', $plugin_admin, 'load_carbon_fields' );
		$this->loader->add_action( 'carbon_fields_register_fields', $plugin_admin, 'add_plugin_settings_page' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_cron_job' );
		$this->loader->add_action( 'wp_operational_status_refresh', $plugin_admin, 'run_wp_operational_status_refresh' );
	}

	private function define_public_hooks() {
		$plugin_public = new WP_Operational_Status_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'load_template_functions' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
