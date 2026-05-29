<?php

namespace Elementor_Kit_Importer\Compat;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Legacy_Adapter {
	private $import;

	public function __construct( $import ) {
		$this->import = $import;
	}

	public static function is_compatibility_needed( array $manifest_data, array $meta = [] ): bool {
		// Detect legacy v0.4 format: has 'version' = '0.4' OR old structure indicator.
		// $meta is accepted to match Elementor's Base_Adapter contract (referrer, etc.); unused here.
		return isset( $manifest_data['version'] ) && $manifest_data['version'] === '0.4';
	}

	public function adapt_manifest( array $manifest_data ): array {
		// Ensure v4 structure in manifest
		if ( ! isset( $manifest_data['format_version'] ) ) {
			$manifest_data['format_version'] = '2.0';
		}

		// Add missing v4 fields
		if ( ! isset( $manifest_data['title'] ) ) {
			$manifest_data['title'] = 'Imported Legacy Kit';
		}

		if ( ! isset( $manifest_data['name'] ) ) {
			$manifest_data['name'] = 'imported-legacy-kit';
		}

		return $manifest_data;
	}

	public function adapt_site_settings( array $site_settings, array $manifest_data, $path ): array {
		// Legacy format: settings directly in root as 'page_settings'
		// Wrap in 'settings' key for v4 compatibility
		if ( isset( $site_settings['page_settings'] ) && is_array( $site_settings['page_settings'] ) ) {
			$adapted = [
				'settings'    => $site_settings['page_settings'],
				'experiments' => [],
			];

			// Preserve template metadata if present
			if ( isset( $site_settings['templates'] ) ) {
				$adapted['templates'] = $site_settings['templates'];
			}

			return $adapted;
		}

		// Already v4 format
		return $site_settings;
	}

	public function adapt_template( array $template_data, array $template_settings ): array {
		return $template_data;
	}
}
