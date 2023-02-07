<?php

class WP_Operational_Status_Public {
	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function load_template_functions() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/template-functions.php';
	}
}
