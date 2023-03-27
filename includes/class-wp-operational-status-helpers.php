<?php

/**
 * Define the helper functionality.
 */
class WP_Operational_Status_Helpers {
	/**
	 * Return log entries from the database.
	 *
	 * @param array $args.
	 */
	public static function get_operational_status_logs( $args = null ) {
		global $wpdb;
		$wpdb_prefix = $wpdb->prefix;

		if (is_multisite()) {
			$main_blog_id = self::get_main_blog_id();
			$main_blog_db_prefix = $wpdb->get_blog_prefix( $main_blog_id );
			$wpdb_prefix = $main_blog_db_prefix;
		}

		$default_args = array(
			'date' => null,
			'limit' => null,
		);
		$sql_parts = array(
			'where' => array(
				'sql' => '',
				'arg' => ''
			),
			'limit' => array(
				'sql' => '',
				'arg' => ''
			),
		);

		$args = wp_parse_args( $args, $default_args );

		if ( $args ) {
			foreach (  $args as $key => $value ) {
				switch ( $key ) {
					case 'limit':
						if ( is_numeric( $value ) && ( $value > 0 ) ) {
							$sql_parts['limit'] = array(
								'sql' => 'LIMIT %d',
								'arg' => $value,
							);
						}
						break;
					case 'date':
						if ( is_string( $value ) && strtotime( $value ) ) {
							$sql_parts['where'] = array(
								'sql' => 'WHERE date_time >= %s',
								'arg' => $value,
							);
						}
						break;
					default:
				}
			}
		}

		$wpdb->operational_status_log = $wpdb_prefix . WP_OPERAIONAL_STATUS_DB_TABLE_PREFIX . '_log';

		$sql = sprintf(
			"SELECT * FROM $wpdb->operational_status_log %s ORDER BY id DESC %s",
			$sql_parts['where']['sql'],
			$sql_parts['limit']['sql']
		);

		$sql_args = array_filter( wp_list_pluck( $sql_parts, 'arg' ) );

		if ( count( $sql_args ) < 1 ) {
			$result = $wpdb->get_results( $sql );
		} else {
			$result = $wpdb->get_results(
				$wpdb->prepare(
					$sql,
					$sql_args
				)
			);
		}

		return $result;
	}

	/**
	 * Return main blog id.
	 *
	 * @param int.
	 */
	public static function get_main_blog_id () {
		global $current_site;
		global $wpdb;

		return $wpdb->get_var ( $wpdb->prepare ( "SELECT `blog_id` FROM `$wpdb->blogs` WHERE `domain` = '%s' AND `path` = '%s' ORDER BY `blog_id` ASC LIMIT 1", $current_site->domain, $current_site->path ) );
	  }
}
