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
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-operational-status-i18n.php';

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
