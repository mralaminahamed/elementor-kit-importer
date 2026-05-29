<?php
/**
 * Legacy v0.4 importer.
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
 * Import the legacy v0.4 (`global.json`) format.
 */
class Legacy_Importer implements Importer {

	/**
	 * Kit manager instance.
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
	 * Detect legacy v0.4 format.
	 *
	 * @param array $data Decoded JSON data.
	 * @return bool
	 */
	public static function detect( array $data ): bool {
		// Explicit version marker.
		if ( isset( $data['version'] ) && '0.4' === $data['version'] ) {
			return true;
		}

		return isset( $data['page_settings'] ) && is_array( $data['page_settings'] );
	}

	/**
	 * Import the legacy format.
	 *
	 * @param array $data               Decoded JSON data.
	 * @param bool  $import_experiments  Unused for the legacy format.
	 * @return array
	 */
	public function import( array $data, bool $import_experiments ): array {
		$settings      = $data['page_settings'] ?? array();
		$content       = $data['content'] ?? array();
		$template_type = $data['metadata']['template_type'] ?? 'unknown';

		$label = isset( $data['version'] ) && '0.4' === $data['version']
			? 'Legacy v0.4 format detected.'
			: 'Legacy site-settings format detected.';

		$is_global_styles      = 'global-styles' === $template_type;
		$has_template_elements = is_array( $content ) && ! empty( $content );

		// Reject template files.
		if ( ! $is_global_styles && $has_template_elements ) {
			return array(
				'status'  => 'error',
				'message' => 'Template file detected (' . ( $data['type'] ?? 'unknown' ) . ': ' . ( $data['title'] ?? 'Unknown' ) . '). Import via Elementor → Tools → Import Kit.',
				'details' => array(
					'File type'      => ucfirst( $data['type'] ?? 'unknown' ),
					'Template title' => $data['title'] ?? 'Untitled',
					'Elements'       => count( $content ),
				),
			);
		}

		if ( empty( $settings ) ) {
			return array(
				'status'  => 'error',
				'message' => 'No settings found in file.',
			);
		}

		$result = $this->kit_manager->apply_settings( $settings );

		if ( ! $result['success'] ) {
			return array(
				'status'  => 'error',
				'message' => $result['error'] ?? 'Failed to apply settings.',
			);
		}

		return array(
			'status'  => 'success',
			'message' => $label . ' Settings applied to active kit.',
			'details' => array(
				'Format'            => 'Legacy v0.4',
				'File type'         => 'Global Styles',
				'System colors'     => count( $settings['system_colors'] ?? array() ),
				'Custom colors'     => count( $settings['custom_colors'] ?? array() ),
				'System typography' => count( $settings['system_typography'] ?? array() ),
				'Custom typography' => count( $settings['custom_typography'] ?? array() ),
			),
		);
	}
}
