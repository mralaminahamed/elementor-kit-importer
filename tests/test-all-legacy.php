<?php
/**
 * Scan every legacy demo file and verify importer classification.
 *
 * Rules:
 *  - site-kit-settings/global.json  => global-styles (accept, import settings)
 *  - templates/*.json               => template files (reject with guidance)
 */

class Test_All_Legacy {
	private $legacy_dir;

	public function __construct() {
		$this->legacy_dir = dirname( __DIR__ ) . '/demos/legacy';
	}

	public function run() {
		$results = [];

		// 1. Global styles file
		$results['global_styles'] = $this->check_global_styles();

		// 2. Templates
		$templates_dir = $this->legacy_dir . '/templates';
		if ( ! is_dir( $templates_dir ) ) {
			$results['templates'] = [ 'status' => 'skip', 'message' => 'templates dir missing' ];
			return $results;
		}

		$files = glob( $templates_dir . '/*.json' );
		foreach ( $files as $file ) {
			$name = basename( $file );
			$results[ "template:{$name}" ] = $this->check_template( $file );
		}

		return $results;
	}

	private function check_global_styles() {
		$path = $this->legacy_dir . '/site-kit-settings/global.json';
		if ( ! file_exists( $path ) ) {
			return [ 'status' => 'skip', 'message' => 'global.json missing' ];
		}

		$data = json_decode( file_get_contents( $path ), true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return [ 'status' => 'fail', 'message' => 'invalid json: ' . json_last_error_msg() ];
		}

		// Must be v0.4
		if ( ( $data['version'] ?? '' ) !== '0.4' ) {
			return [ 'status' => 'fail', 'message' => 'version != 0.4' ];
		}

		// Must be global-styles
		$template_type = $data['metadata']['template_type'] ?? '';
		if ( $template_type !== 'global-styles' ) {
			return [ 'status' => 'fail', 'message' => "template_type={$template_type}, expected global-styles" ];
		}

		// page_settings must exist and hold colors/typography
		$ps = $data['page_settings'] ?? [];
		if ( empty( $ps ) ) {
			return [ 'status' => 'fail', 'message' => 'page_settings empty' ];
		}

		$sys_c = count( $ps['system_colors'] ?? [] );
		$cus_c = count( $ps['custom_colors'] ?? [] );
		$sys_t = count( $ps['system_typography'] ?? [] );
		$cus_t = count( $ps['custom_typography'] ?? [] );

		return [
			'status'  => 'pass',
			'message' => "sys_colors={$sys_c} custom_colors={$cus_c} sys_typo={$sys_t} custom_typo={$cus_t}",
		];
	}

	private function check_template( $path ) {
		$data = json_decode( file_get_contents( $path ), true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return [ 'status' => 'fail', 'message' => 'invalid json' ];
		}

		if ( ( $data['version'] ?? '' ) !== '0.4' ) {
			return [ 'status' => 'fail', 'message' => 'version != 0.4' ];
		}

		$template_type = $data['metadata']['template_type'] ?? '';
		$type          = $data['type'] ?? 'unknown';
		$title         = $data['title'] ?? 'untitled';
		$content       = $data['content'] ?? [];
		$elements      = count( $content );

		// Template should not be global-styles (importer would then treat as settings file)
		if ( $template_type === 'global-styles' ) {
			return [ 'status' => 'fail', 'message' => 'template misclassified as global-styles' ];
		}

		$note = $elements === 0 ? ' (empty)' : '';
		return [
			'status'  => 'pass',
			'message' => "type={$type} tt={$template_type} elements={$elements}{$note} title=\"{$title}\"",
		];
	}
}
