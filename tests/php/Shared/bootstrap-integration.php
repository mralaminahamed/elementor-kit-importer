<?php
/**
 * Integration test bootstrap.
 *
 * Boots a real WordPress stack against the test database, then loads the plugin
 * under test during the muplugins_loaded phase.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

if ( getenv( 'WP_TESTS_DIR' ) ) {
	$_tests_dir = getenv( 'WP_TESTS_DIR' );
} elseif ( getenv( 'WP_PHPUNIT__DIR' ) ) {
	$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
} else {
	$_tests_dir = TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_DIR . '/vendor/wp-phpunit/wp-phpunit';
}

define( 'WP_TESTS_DIR', $_tests_dir );

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php — install wp-phpunit (composer install)." . PHP_EOL;
	exit( 1 );
}

if ( ! getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	$_env_config = $_ENV['WP_PHPUNIT__TESTS_CONFIG'] ?? '';
	if ( $_env_config ) {
		putenv( "WP_PHPUNIT__TESTS_CONFIG={$_env_config}" );
	}
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * Load the plugin under test during muplugins_loaded.
 *
 * @return void
 */
function _elementor_kit_importer_manually_load_plugin(): void {
	require TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_FILE;
}
tests_add_filter( 'muplugins_loaded', '_elementor_kit_importer_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
