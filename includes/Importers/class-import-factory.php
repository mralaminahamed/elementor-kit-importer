<?php
/**
 * Importer factory.
 *
 * @package Elementor_Kit_Importer
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Importers;

use Elementor_Kit_Importer\Kit\Kit_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the appropriate importer for a given payload.
 */
class Import_Factory {

	/**
	 * Kit manager.
	 *
	 * @var Kit_Manager
	 */
	private $kit_manager;

	/**
	 * Constructor.
	 *
	 * @param Kit_Manager $kit_manager Kit manager.
	 */
	public function __construct( Kit_Manager $kit_manager ) {
		$this->kit_manager = $kit_manager;
	}

	/**
	 * Get the importer for the given data.
	 *
	 * @param array $data Decoded JSON data.
	 * @return Importer|null Importer instance, or null if the format is unknown.
	 */
	public function get_importer( array $data ): ?Importer {
		// Try legacy first (it has an explicit version check).
		if ( Legacy_Importer::detect( $data ) ) {
			return new Legacy_Importer( $this->kit_manager );
		}

		if ( V4_Importer::detect( $data ) ) {
			return new V4_Importer( $this->kit_manager );
		}

		return null;
	}
}
