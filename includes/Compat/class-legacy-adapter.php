<?php
/**
 * Legacy import-export compatibility adapter.
 *
 * Kept for older Elementor versions whose import-export module supported
 * third-party compatibility adapters. Modern Elementor (4.x) routes kit imports
 * through the import-export-customization module and rebuilds its adapter list
 * internally, so this adapter is a best-effort shim for legacy installs only.
 *
 * @package Elementor_Kit_Importer
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Compat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adapts a legacy v0.4 kit payload to the structure Elementor's importer expects.
 */
class Legacy_Adapter {

	/**
	 * The Elementor import process instance.
	 *
	 * @var object
	 */
	private $import;

	/**
	 * Constructor.
	 *
	 * @param object $import Elementor import process instance.
	 */
	public function __construct( $import ) {
		$this->import = $import;
	}

	/**
	 * Whether compatibility handling is needed for the manifest.
	 *
	 * The second argument matches Elementor's Base_Adapter contract (referrer
	 * metadata, etc.); it is unused here.
	 *
	 * @param array $manifest_data Manifest data.
	 * @param array $meta          Adapter meta (unused).
	 * @return bool
	 */
	public static function is_compatibility_needed( array $manifest_data, array $meta = array() ): bool {
		return isset( $manifest_data['version'] ) && '0.4' === $manifest_data['version'];
	}

	/**
	 * Adapt the manifest to the v4 structure.
	 *
	 * @param array $manifest_data Manifest data.
	 * @return array
	 */
	public function adapt_manifest( array $manifest_data ): array {
		if ( ! isset( $manifest_data['format_version'] ) ) {
			$manifest_data['format_version'] = '2.0';
		}

		if ( ! isset( $manifest_data['title'] ) ) {
			$manifest_data['title'] = 'Imported Legacy Kit';
		}

		if ( ! isset( $manifest_data['name'] ) ) {
			$manifest_data['name'] = 'imported-legacy-kit';
		}

		return $manifest_data;
	}

	/**
	 * Adapt the site settings to the v4 structure.
	 *
	 * @param array  $site_settings Site settings.
	 * @param array  $manifest_data Manifest data.
	 * @param string $path          Extraction path.
	 * @return array
	 */
	public function adapt_site_settings( array $site_settings, array $manifest_data, $path ): array {
		// Legacy format keeps settings under 'page_settings'; wrap for v4.
		if ( isset( $site_settings['page_settings'] ) && is_array( $site_settings['page_settings'] ) ) {
			$adapted = array(
				'settings'    => $site_settings['page_settings'],
				'experiments' => array(),
			);

			if ( isset( $site_settings['templates'] ) ) {
				$adapted['templates'] = $site_settings['templates'];
			}

			return $adapted;
		}

		return $site_settings;
	}

	/**
	 * Adapt a template (no-op for the legacy format).
	 *
	 * @param array $template_data     Template data.
	 * @param array $template_settings Template settings.
	 * @return array
	 */
	public function adapt_template( array $template_data, array $template_settings ): array {
		return $template_data;
	}
}
