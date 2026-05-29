<?php
/**
 * Unit tests for the kit manager.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Unit\Kit;

use Brain\Monkey;
use Elementor\Plugin as ElementorPlugin;
use Elementor_Kit_Importer\Kit\Kit_Manager;
use Mockery;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class KitManagerTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();
		ElementorPlugin::$instance = new ElementorPlugin();
	}

	protected function tear_down(): void {
		ElementorPlugin::$instance = null;
		Monkey\tearDown();
		parent::tear_down();
	}

	public function test_errors_when_kits_manager_missing(): void {
		ElementorPlugin::$instance->kits_manager = null;

		$result = ( new Kit_Manager() )->apply_settings( array( 'a' => 1 ) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'Kits Manager not available', $result['error'] );
	}

	public function test_errors_when_no_active_kit(): void {
		$kits = Mockery::mock();
		$kits->shouldReceive( 'get_active_kit_for_frontend' )->andReturn( null );
		ElementorPlugin::$instance->kits_manager = $kits;

		$result = ( new Kit_Manager() )->apply_settings( array( 'a' => 1 ) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'No active Elementor kit', $result['error'] );
	}

	public function test_errors_when_save_fails(): void {
		$kit = Mockery::mock();
		$kit->shouldReceive( 'save' )->andReturn( false );

		$kits = Mockery::mock();
		$kits->shouldReceive( 'get_active_kit_for_frontend' )->andReturn( $kit );

		$files = Mockery::mock();
		$files->shouldReceive( 'clear_cache' );

		ElementorPlugin::$instance->kits_manager  = $kits;
		ElementorPlugin::$instance->files_manager = $files;

		$result = ( new Kit_Manager() )->apply_settings( array( 'a' => 1 ) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'Failed to save', $result['error'] );
	}

	public function test_success_saves_and_clears_cache(): void {
		$kit = Mockery::mock();
		$kit->shouldReceive( 'save' )->once()->with( array( 'settings' => array( 'a' => 1 ) ) )->andReturn( 99 );

		$kits = Mockery::mock();
		$kits->shouldReceive( 'get_active_kit_for_frontend' )->andReturn( $kit );

		// A real declared method, so Kit_Manager's method_exists() guard passes.
		$files = new class() {
			public bool $cleared = false;
			public function clear_cache(): void {
				$this->cleared = true;
			}
		};

		ElementorPlugin::$instance->kits_manager  = $kits;
		ElementorPlugin::$instance->files_manager = $files;

		$result = ( new Kit_Manager() )->apply_settings( array( 'a' => 1 ) );

		$this->assertTrue( $result['success'] );
		$this->assertTrue( $files->cleared, 'files_manager->clear_cache() should be called.' );
	}
}
