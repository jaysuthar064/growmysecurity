<?php
/**
 * Raise import and execution limits for site migration tools.
 *
 * @package GrowMySecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gms_import_ini_limits = [
	'memory_limit'       => '512M',
	'upload_max_filesize'=> '512M',
	'post_max_size'      => '512M',
	'max_execution_time' => '600',
	'max_input_time'     => '600',
];

foreach ( $gms_import_ini_limits as $directive => $value ) {
	@ini_set( $directive, $value );
}

add_filter(
	'ai1wm_max_file_size',
	static function () {
		return 512 * 1024 * 1024;
	}
);
