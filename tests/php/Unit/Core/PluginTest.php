<?php
/**
 * Unit tests for the core Plugin class.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Unit\Core;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Elementor_Kit_Importer\Core\Plugin;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class PluginTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();
		$this->reset_plugin();
	}

	protected function tear_down(): void {
		$this->reset_plugin();
		Monkey\tearDown();
		parent::tear_down();
	}

	private function reset_plugin(): void {
		$instance = new \ReflectionProperty( Plugin::class, 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, null );
	}

	public function test_get_instance_is_singleton(): void {
		$this->assertSame( Plugin::get_instance(), Plugin::get_instance() );
	}

	public function test_allow_json_upload_registers_mime(): void {
		$mimes = Plugin::get_instance()->allow_json_upload( array( 'jpg|jpeg' => 'image/jpeg' ) );

		$this->assertSame( 'application/json', $mimes['json'] );
		$this->assertArrayHasKey( 'jpg|jpeg', $mimes );
	}

	public function test_run_registers_expected_hooks(): void {
		$hooks = array();
		Functions\when( 'add_action' )->alias(
			static function ( $hook ) use ( &$hooks ) {
				$hooks[] = $hook;
			}
		);
		Functions\when( 'add_filter' )->alias(
			static function ( $hook ) use ( &$hooks ) {
				$hooks[] = $hook;
			}
		);

		Plugin::get_instance()->run();

		$this->assertContains( 'admin_menu', $hooks );
		$this->assertContains( 'admin_enqueue_scripts', $hooks );
		$this->assertContains( 'admin_notices', $hooks );
		$this->assertContains( 'admin_init', $hooks );
		$this->assertContains( 'upload_mimes', $hooks );
		$this->assertContains( 'elementor/import-export/import-kit', $hooks );
	}

	public function test_run_is_idempotent(): void {
		$count = 0;
		Functions\when( 'add_action' )->alias(
			static function () use ( &$count ) {
				++$count;
			}
		);
		Functions\when( 'add_filter' )->alias(
			static function () use ( &$count ) {
				++$count;
			}
		);

		$plugin = Plugin::get_instance();
		$plugin->run();
		$after_first = $count;
		$plugin->run();

		$this->assertSame( $after_first, $count, 'run() must not re-register hooks.' );
	}
}
