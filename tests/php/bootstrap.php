<?php
/**
 * Unified test bootstrap for all Elementor Kit Importer test suites.
 *
 * Detects the active suite via ELEMENTOR_KIT_IMPORTER_TEST_SUITE and delegates
 * to the matching suite-specific bootstrap. Supported values: 'unit' (default),
 * 'integration'.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

$autoload = dirname( __DIR__, 2 ) . '/vendor/autoload.php';
if ( ! file_exists( $autoload ) ) {
	fwrite( STDERR, "vendor/autoload.php not found — run 'composer install' first.\n" );
	exit( 1 );
}
require_once $autoload;

if ( ! defined( 'TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_DIR' ) ) {
	define( 'TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_DIR', dirname( __DIR__, 2 ) );
}

if ( ! defined( 'TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_FILE' ) ) {
	define( 'TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_FILE', TEST_ELEMENTOR_KIT_IMPORTER_PLUGIN_DIR . '/elementor-kit-importer.php' );
}

$suite = strtolower( (string) ( getenv( 'ELEMENTOR_KIT_IMPORTER_TEST_SUITE' ) ?: ( $_ENV['ELEMENTOR_KIT_IMPORTER_TEST_SUITE'] ?? 'unit' ) ) );

$suite_bootstrap_map = array(
	'unit'        => __DIR__ . '/Shared/bootstrap-unit.php',
	'integration' => __DIR__ . '/Shared/bootstrap-integration.php',
);

if ( ! array_key_exists( $suite, $suite_bootstrap_map ) ) {
	printf(
		'Unknown ELEMENTOR_KIT_IMPORTER_TEST_SUITE value "%s". Expected one of: %s' . PHP_EOL,
		$suite,
		implode( ', ', array_keys( $suite_bootstrap_map ) )
	);
	exit( 1 );
}

require_once $suite_bootstrap_map[ $suite ];
