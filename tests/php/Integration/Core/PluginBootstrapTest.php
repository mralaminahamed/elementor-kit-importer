<?php
/**
 * Integration tests for plugin bootstrap.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Integration\Core;

use Elementor_Kit_Importer\Core\Plugin;
use WP_UnitTestCase;

final class PluginBootstrapTest extends WP_UnitTestCase {

	public function test_constants_are_defined(): void {
		$this->assertTrue( defined( 'ELEMENTOR_KIT_IMPORTER_VERSION' ) );
		$this->assertTrue( defined( 'ELEMENTOR_KIT_IMPORTER_FILE' ) );
		$this->assertTrue( defined( 'ELEMENTOR_KIT_IMPORTER_PATH' ) );
		$this->assertTrue( defined( 'ELEMENTOR_KIT_IMPORTER_URL' ) );
		$this->assertTrue( defined( 'ELEMENTOR_KIT_IMPORTER_BASENAME' ) );
	}

	public function test_accessor_returns_singleton(): void {
		$this->assertInstanceOf( Plugin::class, elementor_kit_importer() );
		$this->assertSame( elementor_kit_importer(), Plugin::get_instance() );
	}

	public function test_core_hooks_are_registered(): void {
		$plugin = Plugin::get_instance();

		$this->assertNotFalse( has_action( 'admin_menu', array( $plugin, 'add_admin_page' ) ) );
		$this->assertNotFalse( has_action( 'admin_init', array( $plugin, 'process_form' ) ) );
		$this->assertNotFalse( has_filter( 'upload_mimes', array( $plugin, 'allow_json_upload' ) ) );
		$this->assertNotFalse( has_action( 'elementor/import-export/import-kit', array( $plugin, 'register_legacy_adapter' ) ) );
	}

	public function test_json_mime_is_allowed(): void {
		$mimes = apply_filters( 'upload_mimes', array() );
		$this->assertArrayHasKey( 'json', $mimes );
		$this->assertSame( 'application/json', $mimes['json'] );
	}
}
