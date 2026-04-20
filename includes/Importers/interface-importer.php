<?php
/**
 * Importer interface
 *
 * @package Elementor_Settings_Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Importer contract
 */
interface Elementor_Settings_Updater_Importer {
	/**
	 * Check if format applies
	 *
	 * @param array $data Decoded JSON data.
	 * @return bool
	 */
	public static function detect( array $data ): bool;

	/**
	 * Import data
	 *
	 * @param array $data Decoded JSON data.
	 * @param bool  $import_experiments Whether to import experiments.
	 * @return array Result with status, message, details.
	 */
	public function import( array $data, bool $import_experiments ): array;
}
