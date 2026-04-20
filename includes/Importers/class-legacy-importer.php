<?php
/**
 * Legacy v0.4 importer
 *
 * @package Elementor_Settings_Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Import legacy v0.4 format
 */
class Elementor_Settings_Updater_Legacy_Importer implements Elementor_Settings_Updater_Importer {
	/**
	 * Kit manager instance
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
	 * Detect legacy v0.4 format
	 */
	public static function detect( array $data ): bool {
		// Check for explicit version marker
		if ( isset( $data['version'] ) && $data['version'] === '0.4' ) {
			return true;
		}

		return isset( $data['page_settings'] ) && is_array( $data['page_settings'] );
	}

	/**
	 * Import legacy format
	 */
	public function import( array $data, bool $import_experiments ): array {
		$settings = $data['page_settings'] ?? [];
		$content = $data['content'] ?? [];
		$template_type = $data['metadata']['template_type'] ?? 'unknown';

		$label = isset( $data['version'] ) && $data['version'] === '0.4'
			? 'Legacy v0.4 format detected.'
			: 'Legacy site-settings format detected.';

		$is_global_styles = $template_type === 'global-styles';
		$has_template_elements = is_array( $content ) && ! empty( $content );

		// Reject template files
		if ( ! $is_global_styles && $has_template_elements ) {
			return [
				'status'  => 'error',
				'message' => 'Template file detected (' . $data['type'] . ': ' . ( $data['title'] ?? 'Unknown' ) . '). Import via Elementor → Tools → Import Kit.',
				'details' => [
					'File type'      => ucfirst( $data['type'] ?? 'unknown' ),
					'Template title' => $data['title'] ?? 'Untitled',
					'Elements'       => count( $content ),
				],
			];
		}

		if ( empty( $settings ) ) {
			return [
				'status'  => 'error',
				'message' => 'No settings found in file.',
			];
		}

		// Apply settings
		$result = $this->kit_manager->apply_settings( $settings );

		if ( ! $result['success'] ) {
			return [
				'status'  => 'error',
				'message' => $result['error'] ?? 'Failed to apply settings.',
			];
		}

		// Build response
		return [
			'status'  => 'success',
			'message' => $label . ' Settings applied to active kit.',
			'details' => [
				'Format'            => 'Legacy v0.4',
				'File type'         => 'Global Styles',
				'System colors'     => count( $settings['system_colors'] ?? [] ),
				'Custom colors'     => count( $settings['custom_colors'] ?? [] ),
				'System typography' => count( $settings['system_typography'] ?? [] ),
				'Custom typography' => count( $settings['custom_typography'] ?? [] ),
			],
		];
	}
}
