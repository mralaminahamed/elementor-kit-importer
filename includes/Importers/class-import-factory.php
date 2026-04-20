<?php
/**
 * Importer factory
 *
 * @package Elementor_Settings_Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Factory for creating appropriate importer
 */
class Elementor_Settings_Updater_Import_Factory {
	/**
	 * Kit manager
	 *
	 * @var Elementor_Settings_Updater_Kit_Manager
	 */
	private $kit_manager;

	/**
	 * Constructor
	 *
	 * @param Elementor_Settings_Updater_Kit_Manager $kit_manager Kit manager.
	 */
	public function __construct( Elementor_Settings_Updater_Kit_Manager $kit_manager ) {
		$this->kit_manager = $kit_manager;
	}

	/**
	 * Get importer for data
	 *
	 * @param array $data Decoded JSON data.
	 * @return Elementor_Settings_Updater_Importer|null Importer instance or null if format unknown.
	 */
	public function get_importer( array $data ) {
		// Try legacy first (has explicit version check)
		if ( Elementor_Settings_Updater_Legacy_Importer::detect( $data ) ) {
			return new Elementor_Settings_Updater_Legacy_Importer( $this->kit_manager );
		}

		// Try v4
		if ( Elementor_Settings_Updater_V4_Importer::detect( $data ) ) {
			return new Elementor_Settings_Updater_V4_Importer( $this->kit_manager );
		}

		return null;
	}
}
