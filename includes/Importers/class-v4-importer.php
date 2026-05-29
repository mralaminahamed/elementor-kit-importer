<?php
/**
 * Elementor v4 importer.
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
 * Import the Elementor v4 (`site-settings.json`) format.
 */
class V4_Importer implements Importer {

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
	 * Detect v4 format.
	 *
	 * @param array $data Decoded JSON data.
	 * @return bool
	 */
	public static function detect( array $data ): bool {
		return isset( $data['settings'] ) && is_array( $data['settings'] ) && ! isset( $data['page_settings'] );
	}

	/**
	 * Import the v4 format.
	 *
	 * @param array $data               Decoded JSON data.
	 * @param bool  $import_experiments  Whether to import experiments.
	 * @return array
	 */
	public function import( array $data, bool $import_experiments ): array {
		$settings  = $data['settings'] ?? array();
		$templates = $data['templates'] ?? array();
		$messages  = array();
		$details   = array( 'Format' => 'Elementor v4' );
		$status    = 'success';

		$settings = $this->strip_non_portable_settings( $settings );

		// Apply kit settings.
		if ( ! empty( $settings ) ) {
			$result = $this->kit_manager->apply_settings( $settings );
			if ( $result['success'] ) {
				$messages[]                   = 'Kit settings applied.';
				$details['System colors']     = count( $settings['system_colors'] ?? array() );
				$details['Custom colors']     = count( $settings['custom_colors'] ?? array() );
				$details['System typography'] = count( $settings['system_typography'] ?? array() );
				$details['Custom typography'] = count( $settings['custom_typography'] ?? array() );
			} else {
				$messages[] = 'Failed to apply kit settings.';
				$status     = 'error';
			}
		} else {
			$messages[]          = 'No kit settings found in file (settings block is empty).';
			$details['Settings'] = 'empty';
		}

		// Apply experiments.
		if ( $import_experiments ) {
			if ( ! empty( $data['experiments'] ) ) {
				$count                          = $this->apply_experiments( $data['experiments'] );
				$messages[]                     = "Experiments: {$count} feature flag(s) updated.";
				$details['Experiments updated'] = $count;
			} else {
				$messages[] = 'No experiments data found in file.';
			}
		}

		// Report templates.
		if ( ! empty( $templates ) ) {
			$template_count                 = is_array( $templates ) ? count( $templates ) : 1;
			$messages[]                     = "Template data found ({$template_count}). Use Elementor → Tools → Import Kit for full template import.";
			$details['Templates available'] = $template_count;
		}

		return array(
			'status'  => $status,
			'message' => implode( ' ', $messages ),
			'details' => $details,
		);
	}

	/**
	 * Remove settings that are non-portable across sites.
	 *
	 * Strips site identity fields and WooCommerce page references — the latter
	 * hold source-site post IDs that would break cart/checkout/account links on
	 * the target site.
	 *
	 * @param array $settings Settings to clean.
	 * @return array
	 */
	private function strip_non_portable_settings( array $settings ): array {
		foreach ( array( 'site_name', 'site_description', 'site_logo', 'site_favicon' ) as $key ) {
			unset( $settings[ $key ] );
		}

		foreach ( array_keys( $settings ) as $key ) {
			if ( 0 === strpos( $key, 'woocommerce_' ) && '_page_id' === substr( $key, -8 ) ) {
				unset( $settings[ $key ] );
			}
		}

		return $settings;
	}

	/**
	 * Apply experiments / feature flags.
	 *
	 * @param array $experiments_data Experiments keyed by feature name.
	 * @return int Number of flags updated.
	 */
	private function apply_experiments( array $experiments_data ): int {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return 0;
		}

		$elementor = \Elementor\Plugin::$instance;
		if ( empty( $elementor->experiments ) ) {
			return 0;
		}

		$manager          = $elementor->experiments;
		$current_features = $manager->get_features();
		$count            = 0;

		foreach ( $experiments_data as $feature_name => $feature_data ) {
			if ( ! isset( $current_features[ $feature_name ] ) ) {
				continue;
			}

			$new_state     = $feature_data['state'] ?? 'default';
			$current_state = $current_features[ $feature_name ]['state'] ?? 'default';

			if ( $current_state === $new_state ) {
				continue;
			}

			if ( ! in_array( $new_state, array( 'default', 'active', 'inactive' ), true ) ) {
				continue;
			}

			$option_key = $manager->get_feature_option_key( $feature_name );

			if ( 'default' === $new_state ) {
				delete_option( $option_key );
			} else {
				update_option( $option_key, $new_state );
			}

			++$count;
		}

		return $count;
	}
}
