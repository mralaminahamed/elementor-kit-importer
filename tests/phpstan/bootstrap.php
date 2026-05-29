<?php
/**
 * PHPStan bootstrap — defines plugin constants so analysis can resolve them.
 * Not loaded at runtime; only used during static analysis.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

define( 'ELEMENTOR_KIT_IMPORTER_VERSION', '2.0.1' );
define( 'ELEMENTOR_KIT_IMPORTER_FILE', dirname( __DIR__, 2 ) . '/elementor-kit-importer.php' );
define( 'ELEMENTOR_KIT_IMPORTER_PATH', dirname( __DIR__, 2 ) . '/' );
define( 'ELEMENTOR_KIT_IMPORTER_URL', 'http://localhost/wp-content/plugins/elementor-kit-importer/' );
define( 'ELEMENTOR_KIT_IMPORTER_BASENAME', 'elementor-kit-importer/elementor-kit-importer.php' );
