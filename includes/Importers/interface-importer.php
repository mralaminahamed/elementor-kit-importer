<?php
/**
 * Importer interface
 *
 * @package Elementor_Kit_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Importer contract
 */
interface Elementor_Kit_Importer_Importer {
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
