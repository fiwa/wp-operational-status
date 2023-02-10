<?php

/**
 * The public-facing functionality of the plugin.
 */
class WP_Operational_Status_Public {
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
	 * Load template functions.
	 */
	public function load_template_functions() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/template-functions.php';
	}
}
