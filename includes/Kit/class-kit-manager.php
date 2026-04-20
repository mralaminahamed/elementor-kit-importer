<?php
/**
 * Kit settings manager
 *
 * @package Elementor_Kit_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Manage Elementor kit settings and cache
 */
class Elementor_Kit_Importer_Kit_Manager {
	/**
	 * Apply settings to active kit
	 *
	 * @param array $settings Settings to apply.
	 * @return array Result with success flag and optional error.
	 */
	public function apply_settings( array $settings ): array {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return [
				'success' => false,
				'error'   => 'Elementor plugin is not active.',
			];
		}

		$elementor = \Elementor\Plugin::$instance;

		if ( empty( $elementor->kits_manager ) ) {
			return [
				'success' => false,
				'error'   => 'Elementor Kits Manager not available. Please update Elementor.',
			];
		}

		$active_kit = $elementor->kits_manager->get_active_kit_for_frontend();
		if ( ! $active_kit ) {
			return [
				'success' => false,
				'error'   => 'No active Elementor kit found.',
			];
		}

		$save_result = $active_kit->save( [ 'settings' => $settings ] );
		$this->clear_cache( $elementor );

		if ( ! $save_result ) {
			return [
				'success' => false,
				'error'   => 'Failed to save settings to active kit.',
			];
		}

		return [ 'success' => true ];
	}

	/**
	 * Clear Elementor cache
	 *
	 * @param object $elementor Elementor plugin instance.
	 */
	private function clear_cache( $elementor ): void {
		if ( method_exists( $elementor->files_manager, 'clear_cache' ) ) {
			$elementor->files_manager->clear_cache();
		}
	}
}
