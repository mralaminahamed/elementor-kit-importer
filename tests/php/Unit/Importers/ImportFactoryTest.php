<?php
/**
 * Unit tests for the importer factory.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Tests\Unit\Importers;

use Elementor_Kit_Importer\Importers\Import_Factory;
use Elementor_Kit_Importer\Importers\Legacy_Importer;
use Elementor_Kit_Importer\Importers\V4_Importer;
use Elementor_Kit_Importer\Kit\Kit_Manager;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class ImportFactoryTest extends TestCase {

	private function factory(): Import_Factory {
		return new Import_Factory( new Kit_Manager() );
	}

	public function test_returns_legacy_importer_for_v04(): void {
		$importer = $this->factory()->get_importer( array( 'version' => '0.4', 'page_settings' => array() ) );
		$this->assertInstanceOf( Legacy_Importer::class, $importer );
	}

	public function test_returns_v4_importer_for_settings_payload(): void {
		$importer = $this->factory()->get_importer( array( 'settings' => array( 'a' => 1 ) ) );
		$this->assertInstanceOf( V4_Importer::class, $importer );
	}

	public function test_returns_null_for_unknown_payload(): void {
		$this->assertNull( $this->factory()->get_importer( array( 'foo' => 'bar' ) ) );
	}

	public function test_legacy_wins_when_ambiguous(): void {
		// Has both page_settings (legacy) and settings (v4) — legacy is checked first.
		$importer = $this->factory()->get_importer(
			array(
				'page_settings' => array( 'a' => 1 ),
				'settings'      => array( 'b' => 2 ),
			)
		);
		$this->assertInstanceOf( Legacy_Importer::class, $importer );
	}
}
