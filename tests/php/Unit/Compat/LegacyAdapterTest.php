<?php
/**
 * Unit tests for the legacy compatibility adapter.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Unit\Compat;

use Elementor_Kit_Importer\Compat\Legacy_Adapter;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class LegacyAdapterTest extends TestCase {

	private function adapter(): Legacy_Adapter {
		return new Legacy_Adapter( (object) array() );
	}

	public function test_compatibility_needed_for_v04(): void {
		$this->assertTrue( Legacy_Adapter::is_compatibility_needed( array( 'version' => '0.4' ) ) );
	}

	public function test_compatibility_not_needed_otherwise(): void {
		$this->assertFalse( Legacy_Adapter::is_compatibility_needed( array( 'version' => '2.0' ) ) );
		$this->assertFalse( Legacy_Adapter::is_compatibility_needed( array() ) );
	}

	public function test_compatibility_needed_accepts_meta_argument(): void {
		$this->assertTrue(
			Legacy_Adapter::is_compatibility_needed( array( 'version' => '0.4' ), array( 'referrer' => 'kit-library' ) )
		);
	}

	public function test_adapt_manifest_fills_defaults(): void {
		$manifest = $this->adapter()->adapt_manifest( array() );

		$this->assertSame( '2.0', $manifest['format_version'] );
		$this->assertSame( 'Imported Legacy Kit', $manifest['title'] );
		$this->assertSame( 'imported-legacy-kit', $manifest['name'] );
	}

	public function test_adapt_manifest_preserves_existing_values(): void {
		$manifest = $this->adapter()->adapt_manifest(
			array(
				'title' => 'My Kit',
				'name'  => 'my-kit',
			)
		);

		$this->assertSame( 'My Kit', $manifest['title'] );
		$this->assertSame( 'my-kit', $manifest['name'] );
	}

	public function test_adapt_site_settings_wraps_page_settings(): void {
		$adapted = $this->adapter()->adapt_site_settings(
			array(
				'page_settings' => array( 'system_colors' => array( 1 ) ),
				'templates'     => array( array( 'id' => 1 ) ),
			),
			array(),
			'/tmp/path'
		);

		$this->assertArrayHasKey( 'settings', $adapted );
		$this->assertSame( array( 'system_colors' => array( 1 ) ), $adapted['settings'] );
		$this->assertSame( array(), $adapted['experiments'] );
		$this->assertArrayHasKey( 'templates', $adapted );
	}

	public function test_adapt_site_settings_passes_through_v4(): void {
		$input   = array( 'settings' => array( 'a' => 1 ) );
		$adapted = $this->adapter()->adapt_site_settings( $input, array(), '/tmp/path' );

		$this->assertSame( $input, $adapted );
	}
}
