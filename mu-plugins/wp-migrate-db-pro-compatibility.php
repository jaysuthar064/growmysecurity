<?php
/*
Plugin Name: WP Migrate Lite Compatibility
Plugin URI: http://deliciousbrains.com/wp-migrate-db-pro/
Description: Prevents 3rd party plugins from being loaded during WP Migrate DB specific operations
Author: Delicious Brains
Version: 1.3
Author URI: http://deliciousbrains.com
*/

defined( 'ABSPATH' ) || exit;

if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	return;
}

if ( defined( 'WP_PLUGIN_DIR' ) ) {
	$plugins_dir = trailingslashit( WP_PLUGIN_DIR );

} else if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
	$plugins_dir = trailingslashit( WPMU_PLUGIN_DIR );

} else if ( defined( 'WP_CONTENT_DIR' ) ) {
	$plugins_dir = trailingslashit( WP_CONTENT_DIR ) . 'plugins/';

} else {
	$plugins_dir = plugin_dir_path( __FILE__ ) . '../plugins/';
}

$compat_class_path = 'class/Common/Compatibility/Compatibility.php';
$compat_class_name = 'DeliciousBrains\WPMDB\Common\Compatibility\Compatibility';
$compat_class_file = '';

foreach (
	array(
		$plugins_dir . 'wp-migrate-db-pro/' . $compat_class_path,
		$plugins_dir . 'wp-migrate-db/' . $compat_class_path,
	) as $candidate
) {
	if ( is_readable( $candidate ) ) {
		$compat_class_file = $candidate;
		break;
	}
}

if ( '' === $compat_class_file ) {
	$GLOBALS['wpmdb_compatibility']['active'] = false;
	return;
}

$GLOBALS['wpmdb_compatibility']['active'] = true;

include_once $compat_class_file;

if ( class_exists( $compat_class_name ) ) {
	$compatibility = new $compat_class_name;
	$compatibility->register();
}
