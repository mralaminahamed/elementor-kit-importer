<?php
/**
 * Unit-test bootstrap.
 *
 * Defines plugin constants and Elementor stubs so the plugin classes can be
 * autoloaded and exercised without a WordPress install. WordPress functions are
 * stubbed per-test via Brain\Monkey.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 3 ) . '/' );
}

if ( ! defined( 'ELEMENTOR_KIT_IMPORTER_VERSION' ) ) {
	define( 'ELEMENTOR_KIT_IMPORTER_VERSION', '2.0.2' );
}

if ( ! defined( 'ELEMENTOR_KIT_IMPORTER_FILE' ) ) {
	define( 'ELEMENTOR_KIT_IMPORTER_FILE', dirname( __DIR__, 3 ) . '/elementor-kit-importer.php' );
}

if ( ! defined( 'ELEMENTOR_KIT_IMPORTER_PATH' ) ) {
	define( 'ELEMENTOR_KIT_IMPORTER_PATH', dirname( __DIR__, 3 ) . '/' );
}

if ( ! defined( 'ELEMENTOR_KIT_IMPORTER_URL' ) ) {
	define( 'ELEMENTOR_KIT_IMPORTER_URL', 'http://example.org/wp-content/plugins/elementor-kit-importer/' );
}

if ( ! defined( 'ELEMENTOR_KIT_IMPORTER_BASENAME' ) ) {
	define( 'ELEMENTOR_KIT_IMPORTER_BASENAME', 'elementor-kit-importer/elementor-kit-importer.php' );
}

// Elementor stubs must exist before any class that touches them is autoloaded.
require_once __DIR__ . '/stubs-elementor.php';
