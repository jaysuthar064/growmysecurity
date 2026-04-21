<?php
/**
 * Plugin Name: GMS WPvivid Clone Transport
 * Description: Forces WPvivid clone and send-to-site requests to use the Streams transport to avoid local cURL socket send failures.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects WPvivid clone/send-to-site HTTP requests.
 *
 * @param array $args Request args passed to the WP HTTP API.
 * @return bool
 */
function gms_is_wpvivid_clone_http_request( $args ) {
	if ( ! isset( $args['body'] ) || ! is_array( $args['body'] ) ) {
		return false;
	}

	if ( isset( $args['body']['wpvivid_action'] ) ) {
		$action = (string) $args['body']['wpvivid_action'];

		return 0 === strpos( $action, 'send_to_site' ) || 'clear_backup_cache' === $action;
	}

	return false;
}

add_filter(
	'use_curl_transport',
	static function ( $use_curl, $args ) {
		if ( gms_is_wpvivid_clone_http_request( $args ) ) {
			return false;
		}

		return $use_curl;
	},
	20,
	2
);

add_filter(
	'use_streams_transport',
	static function ( $use_streams, $args ) {
		if ( gms_is_wpvivid_clone_http_request( $args ) ) {
			return true;
		}

		return $use_streams;
	},
	20,
	2
);