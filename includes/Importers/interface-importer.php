<?php
/**
 * Importer interface.
 *
 * @package Elementor_Kit_Importer
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Importers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Importer contract.
 */
interface Importer {

	/**
	 * Check if this importer applies to the given data.
	 *
	 * @param array $data Decoded JSON data.
	 * @return bool
	 */
	public static function detect( array $data ): bool;

	/**
	 * Import the data.
	 *
	 * @param array $data               Decoded JSON data.
	 * @param bool  $import_experiments  Whether to import experiments.
	 * @return array Result with status, message, details.
	 */
	public function import( array $data, bool $import_experiments ): array;
}
