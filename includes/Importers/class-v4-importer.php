<?php
/**
 * Elementor v4 importer
 *
 * @package Elementor_Settings_Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Import Elementor v4 format
 */
class Elementor_Settings_Updater_V4_Importer implements Elementor_Settings_Updater_Importer {
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
	 * Detect v4 format
	 */
	public static function detect( array $data ): bool {
		return isset( $data['settings'] ) && is_array( $data['settings'] ) && ! isset( $data['page_settings'] );
	}

	/**
	 * Import v4 format
	 */
	public function import( array $data, bool $import_experiments ): array {
		$settings = $data['settings'] ?? [];
		$templates = $data['templates'] ?? [];
		$messages = [];
		$details = [ 'Format' => 'Elementor v4' ];
		$status = 'success';

		// Strip non-portable site identity fields
		foreach ( [ 'site_name', 'site_description', 'site_logo', 'site_favicon' ] as $key ) {
			unset( $settings[ $key ] );
		}

		// Apply kit settings
		if ( ! empty( $settings ) ) {
			$result = $this->kit_manager->apply_settings( $settings );
			if ( $result['success'] ) {
				$messages[] = 'Kit settings applied.';
				$details['System colors'] = count( $settings['system_colors'] ?? [] );
				$details['Custom colors'] = count( $settings['custom_colors'] ?? [] );
				$details['System typography'] = count( $settings['system_typography'] ?? [] );
				$details['Custom typography'] = count( $settings['custom_typography'] ?? [] );
			} else {
				$messages[] = 'Failed to apply kit settings.';
				$status = 'error';
			}
		} else {
			$messages[] = 'No kit settings found in file (settings block is empty).';
			$details['Settings'] = 'empty';
		}

		// Apply experiments
		if ( $import_experiments ) {
			if ( ! empty( $data['experiments'] ) ) {
				$count = $this->apply_experiments( $data['experiments'] );
				$messages[] = "Experiments: {$count} feature flag(s) updated.";
				$details['Experiments updated'] = $count;
			} else {
				$messages[] = 'No experiments data found in file.';
			}
		}

		// Report templates
		if ( ! empty( $templates ) ) {
			$template_count = is_array( $templates ) ? count( $templates ) : 1;
			$messages[] = "Template data found ({$template_count}). Use Elementor → Tools → Import Kit for full template import.";
			$details['Templates available'] = $template_count;
		}

		return [
			'status'  => $status,
			'message' => implode( ' ', $messages ),
			'details' => $details,
		];
	}

	/**
	 * Apply experiments/feature flags
	 */
	private function apply_experiments( array $experiments_data ): int {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return 0;
		}

		$elementor = \Elementor\Plugin::$instance;
		if ( empty( $elementor->experiments ) ) {
			return 0;
		}

		$manager = $elementor->experiments;
		$current_features = $manager->get_features();
		$count = 0;

		foreach ( $experiments_data as $feature_name => $feature_data ) {
			if ( ! isset( $current_features[ $feature_name ] ) ) {
				continue;
			}

			$new_state = $feature_data['state'] ?? 'default';
			$current_state = $current_features[ $feature_name ]['state'] ?? 'default';

			if ( $current_state === $new_state ) {
				continue;
			}

			if ( ! in_array( $new_state, [ 'default', 'active', 'inactive' ], true ) ) {
				continue;
			}

			$option_key = $manager->get_feature_option_key( $feature_name );

			if ( $new_state === 'default' ) {
				delete_option( $option_key );
			} else {
				update_option( $option_key, $new_state );
			}

			$count++;
		}

		return $count;
	}
}
