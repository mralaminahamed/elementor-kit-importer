<?php
/**
 * Kit settings manager.
 *
 * @package Elementor_Kit_Importer
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage Elementor kit settings and cache.
 */
class Kit_Manager {

	/**
	 * Apply settings to the active kit.
	 *
	 * @param array $settings Settings to apply.
	 * @return array Result with success flag and optional error.
	 */
	public function apply_settings( array $settings ): array {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return array(
				'success' => false,
				'error'   => 'Elementor plugin is not active.',
			);
		}

		$elementor = \Elementor\Plugin::$instance;

		if ( empty( $elementor->kits_manager ) ) {
			return array(
				'success' => false,
				'error'   => 'Elementor Kits Manager not available. Please update Elementor.',
			);
		}

		$active_kit = $elementor->kits_manager->get_active_kit_for_frontend();
		if ( ! $active_kit ) {
			return array(
				'success' => false,
				'error'   => 'No active Elementor kit found.',
			);
		}

		$save_result = $active_kit->save( array( 'settings' => $settings ) );
		$this->clear_cache( $elementor );

		if ( ! $save_result ) {
			return array(
				'success' => false,
				'error'   => 'Failed to save settings to active kit.',
			);
		}

		return array( 'success' => true );
	}

	/**
	 * Clear the Elementor cache.
	 *
	 * @param object $elementor Elementor plugin instance.
	 * @return void
	 */
	private function clear_cache( $elementor ): void {
		if ( method_exists( $elementor->files_manager, 'clear_cache' ) ) {
			$elementor->files_manager->clear_cache();
		}
	}
}
