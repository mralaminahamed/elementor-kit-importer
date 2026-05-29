<?php
/**
 * Unit tests for the Elementor v4 importer.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Unit\Importers;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Elementor\Plugin as ElementorPlugin;
use Elementor_Kit_Importer\Importers\V4_Importer;
use Elementor_Kit_Importer\Kit\Kit_Manager;
use Mockery;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class V4ImporterTest extends TestCase {

	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();
	}

	protected function tear_down(): void {
		ElementorPlugin::$instance = null;
		Monkey\tearDown();
		parent::tear_down();
	}

	public function test_detect_matches_settings_without_page_settings(): void {
		$this->assertTrue( V4_Importer::detect( array( 'settings' => array( 'a' => 1 ) ) ) );
	}

	public function test_detect_rejects_when_page_settings_present(): void {
		$this->assertFalse(
			V4_Importer::detect(
				array(
					'settings'      => array( 'a' => 1 ),
					'page_settings' => array(),
				)
			)
		);
	}

	public function test_strips_site_identity_and_woocommerce_page_ids(): void {
		$captured    = null;
		$kit_manager = Mockery::mock( Kit_Manager::class );
		$kit_manager->shouldReceive( 'apply_settings' )->andReturnUsing(
			static function ( $settings ) use ( &$captured ) {
				$captured = $settings;
				return array( 'success' => true );
			}
		);

		$importer = new V4_Importer( $kit_manager );
		$importer->import(
			array(
				'settings' => array(
					'site_logo'                  => array( 'id' => 5 ),
					'site_name'                  => 'Source Site',
					'woocommerce_cart_page_id'   => 12,
					'woocommerce_shop_page_id'   => 13,
					'system_colors'              => array( 1, 2, 3, 4 ),
				),
			),
			false
		);

		$this->assertArrayNotHasKey( 'site_logo', $captured );
		$this->assertArrayNotHasKey( 'site_name', $captured );
		$this->assertArrayNotHasKey( 'woocommerce_cart_page_id', $captured );
		$this->assertArrayNotHasKey( 'woocommerce_shop_page_id', $captured );
		$this->assertArrayHasKey( 'system_colors', $captured );
	}

	public function test_reports_templates_block(): void {
		$kit_manager = Mockery::mock( Kit_Manager::class );
		$kit_manager->shouldReceive( 'apply_settings' )->andReturn( array( 'success' => true ) );

		$importer = new V4_Importer( $kit_manager );
		$result   = $importer->import(
			array(
				'settings'  => array( 'system_colors' => array( 1 ) ),
				'templates' => array( array( 'id' => 1 ), array( 'id' => 2 ) ),
			),
			false
		);

		$this->assertSame( 'success', $result['status'] );
		$this->assertSame( 2, $result['details']['Templates available'] );
		$this->assertStringContainsString( 'Template data found', $result['message'] );
	}

	public function test_empty_settings_reports_empty(): void {
		$kit_manager = Mockery::mock( Kit_Manager::class );
		$kit_manager->shouldNotReceive( 'apply_settings' );

		$importer = new V4_Importer( $kit_manager );
		$result   = $importer->import( array( 'settings' => array() ), false );

		$this->assertSame( 'empty', $result['details']['Settings'] );
		$this->assertStringContainsString( 'No kit settings found', $result['message'] );
	}

	public function test_experiments_update_changed_flags_only(): void {
		Functions\when( 'delete_option' )->justReturn( true );
		Functions\when( 'update_option' )->justReturn( true );

		$experiments = Mockery::mock();
		$experiments->shouldReceive( 'get_features' )->andReturn(
			array(
				'container'    => array( 'state' => 'inactive' ),
				'e_local_font' => array( 'state' => 'active' ),
			)
		);
		$experiments->shouldReceive( 'get_feature_option_key' )->andReturnUsing(
			static fn( $name ) => 'elementor_experiment-' . $name
		);

		ElementorPlugin::$instance              = new ElementorPlugin();
		ElementorPlugin::$instance->experiments = $experiments;

		$kit_manager = Mockery::mock( Kit_Manager::class );
		$kit_manager->shouldReceive( 'apply_settings' )->andReturn( array( 'success' => true ) );

		$importer = new V4_Importer( $kit_manager );
		$result   = $importer->import(
			array(
				'settings'    => array( 'system_colors' => array( 1 ) ),
				'experiments' => array(
					'container'    => array( 'state' => 'active' ),   // changed → update
					'e_local_font' => array( 'state' => 'active' ),   // unchanged → skip
					'unknown_flag' => array( 'state' => 'active' ),   // not a feature → skip
				),
			),
			true
		);

		$this->assertSame( 1, $result['details']['Experiments updated'] );
	}
}
