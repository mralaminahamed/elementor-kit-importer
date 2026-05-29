<?php
/**
 * Unit tests for the legacy v0.4 importer.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Unit\Importers;

use Brain\Monkey;
use Elementor_Kit_Importer\Importers\Legacy_Importer;
use Elementor_Kit_Importer\Kit\Kit_Manager;
use Mockery;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class LegacyImporterTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();
	}

	protected function tear_down(): void {
		Monkey\tearDown();
		parent::tear_down();
	}

	/**
	 * Build an importer with a kit manager mock.
	 *
	 * @param array|null $apply_result Result apply_settings() should return, or null to forbid the call.
	 * @return array{0: Legacy_Importer, 1: \Mockery\MockInterface}
	 */
	private function make( ?array $apply_result ): array {
		$kit_manager = Mockery::mock( Kit_Manager::class );
		if ( null === $apply_result ) {
			$kit_manager->shouldNotReceive( 'apply_settings' );
		} else {
			$kit_manager->shouldReceive( 'apply_settings' )->andReturn( $apply_result );
		}

		return array( new Legacy_Importer( $kit_manager ), $kit_manager );
	}

	public function test_detect_matches_explicit_version(): void {
		$this->assertTrue( Legacy_Importer::detect( array( 'version' => '0.4' ) ) );
	}

	public function test_detect_matches_page_settings_structure(): void {
		$this->assertTrue( Legacy_Importer::detect( array( 'page_settings' => array( 'a' => 1 ) ) ) );
	}

	public function test_detect_rejects_unknown(): void {
		$this->assertFalse( Legacy_Importer::detect( array( 'settings' => array() ) ) );
	}

	public function test_template_file_is_rejected(): void {
		[ $importer ] = $this->make( null );

		$result = $importer->import(
			array(
				'version'  => '0.4',
				'type'     => 'page',
				'title'    => 'Home',
				'content'  => array( array( 'id' => 1 ) ),
				'metadata' => array( 'template_type' => 'single-page' ),
			),
			false
		);

		$this->assertSame( 'error', $result['status'] );
		$this->assertStringContainsString( 'Template file detected', $result['message'] );
		$this->assertStringContainsString( 'page', $result['message'] );
	}

	public function test_template_rejection_handles_missing_type_without_notice(): void {
		[ $importer ] = $this->make( null );

		$result = $importer->import(
			array(
				'content'  => array( array( 'id' => 1 ) ),
				'metadata' => array( 'template_type' => 'single-page' ),
			),
			false
		);

		$this->assertSame( 'error', $result['status'] );
		$this->assertStringContainsString( 'unknown', $result['message'] );
	}

	public function test_empty_settings_errors(): void {
		[ $importer ] = $this->make( null );

		$result = $importer->import(
			array(
				'version'  => '0.4',
				'metadata' => array( 'template_type' => 'global-styles' ),
			),
			false
		);

		$this->assertSame( 'error', $result['status'] );
		$this->assertSame( 'No settings found in file.', $result['message'] );
	}

	public function test_successful_import_applies_settings(): void {
		[ $importer ] = $this->make( array( 'success' => true ) );

		$result = $importer->import(
			array(
				'version'       => '0.4',
				'metadata'      => array( 'template_type' => 'global-styles' ),
				'page_settings' => array(
					'system_colors' => array( 1, 2, 3, 4 ),
					'custom_colors' => array( 1, 2 ),
				),
			),
			false
		);

		$this->assertSame( 'success', $result['status'] );
		$this->assertSame( 4, $result['details']['System colors'] );
		$this->assertSame( 2, $result['details']['Custom colors'] );
	}

	public function test_apply_failure_surfaces_error(): void {
		[ $importer ] = $this->make(
			array(
				'success' => false,
				'error'   => 'No active Elementor kit found.',
			)
		);

		$result = $importer->import(
			array(
				'version'       => '0.4',
				'metadata'      => array( 'template_type' => 'global-styles' ),
				'page_settings' => array( 'system_colors' => array( 1 ) ),
			),
			false
		);

		$this->assertSame( 'error', $result['status'] );
		$this->assertSame( 'No active Elementor kit found.', $result['message'] );
	}
}
