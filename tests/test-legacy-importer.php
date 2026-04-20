<?php
/**
 * Legacy Importer Tests
 */

class Test_Legacy_Importer {
	private $importer_class = 'Elementor_Kit_Importer_Legacy_Importer';
	private $legacy_file;
	private $template_file;

	public function __construct() {
		$this->legacy_file = dirname( __DIR__ ) . '/demos/legacy/site-kit-settings/global.json';
		$this->template_file = dirname( __DIR__ ) . '/demos/legacy/templates/home.json';
	}

	/**
	 * Test legacy format detection
	 */
	public function test_detect_legacy_format() {
		if ( ! file_exists( $this->legacy_file ) ) {
			return [ 'status' => 'skip', 'message' => 'Legacy file not found' ];
		}

		$data = json_decode( file_get_contents( $this->legacy_file ), true );

		// Check version field
		if ( ! isset( $data['version'] ) || $data['version'] !== '0.4' ) {
			return [ 'status' => 'fail', 'message' => 'Version not 0.4' ];
		}

		// Check page_settings
		if ( ! isset( $data['page_settings'] ) || ! is_array( $data['page_settings'] ) ) {
			return [ 'status' => 'fail', 'message' => 'page_settings missing or not array' ];
		}

		// Check metadata
		if ( ! isset( $data['metadata']['template_type'] ) ) {
			return [ 'status' => 'fail', 'message' => 'template_type missing' ];
		}

		return [ 'status' => 'pass', 'message' => 'Legacy format detected correctly' ];
	}

	/**
	 * Test global-styles file detection
	 */
	public function test_detect_global_styles() {
		if ( ! file_exists( $this->legacy_file ) ) {
			return [ 'status' => 'skip', 'message' => 'Legacy file not found' ];
		}

		$data = json_decode( file_get_contents( $this->legacy_file ), true );

		$template_type = $data['metadata']['template_type'] ?? 'unknown';
		if ( $template_type !== 'global-styles' ) {
			return [ 'status' => 'fail', 'message' => "Expected global-styles, got {$template_type}" ];
		}

		$content = $data['content'] ?? [];
		if ( ! empty( $content ) ) {
			return [ 'status' => 'fail', 'message' => 'Global styles should have empty content array' ];
		}

		return [ 'status' => 'pass', 'message' => 'Global styles file detected correctly' ];
	}

	/**
	 * Test settings extraction
	 */
	public function test_extract_settings() {
		if ( ! file_exists( $this->legacy_file ) ) {
			return [ 'status' => 'skip', 'message' => 'Legacy file not found' ];
		}

		$data = json_decode( file_get_contents( $this->legacy_file ), true );
		$settings = $data['page_settings'] ?? [];

		if ( empty( $settings ) ) {
			return [ 'status' => 'fail', 'message' => 'Settings empty' ];
		}

		$checks = [
			'system_colors' => isset( $settings['system_colors'] ) ? count( $settings['system_colors'] ) : 0,
			'custom_colors' => isset( $settings['custom_colors'] ) ? count( $settings['custom_colors'] ) : 0,
			'system_typography' => isset( $settings['system_typography'] ) ? count( $settings['system_typography'] ) : 0,
			'custom_typography' => isset( $settings['custom_typography'] ) ? count( $settings['custom_typography'] ) : 0,
		];

		if ( $checks['system_colors'] === 0 ) {
			return [ 'status' => 'fail', 'message' => 'No system colors found' ];
		}

		if ( $checks['custom_colors'] === 0 ) {
			return [ 'status' => 'fail', 'message' => 'No custom colors found' ];
		}

		return [
			'status' => 'pass',
			'message' => "Settings extracted: " . implode( ', ', array_map( fn( $k, $v ) => "{$k}={$v}", array_keys( $checks ), $checks ) ),
		];
	}

	/**
	 * Test template file rejection
	 */
	public function test_reject_template_file() {
		if ( ! file_exists( $this->template_file ) ) {
			return [ 'status' => 'skip', 'message' => 'Template file not found' ];
		}

		$data = json_decode( file_get_contents( $this->template_file ), true );

		$template_type = $data['metadata']['template_type'] ?? 'unknown';
		if ( $template_type === 'global-styles' ) {
			return [ 'status' => 'fail', 'message' => 'Template should not be global-styles' ];
		}

		$content = $data['content'] ?? [];
		if ( empty( $content ) ) {
			return [ 'status' => 'fail', 'message' => 'Template should have content elements' ];
		}

		$title = $data['title'] ?? 'Unknown';
		return [ 'status' => 'pass', 'message' => "Template file detected: {$title} ({$data['type']})" ];
	}

	/**
	 * Run all tests
	 */
	public function run() {
		$tests = [
			'test_detect_legacy_format',
			'test_detect_global_styles',
			'test_extract_settings',
			'test_reject_template_file',
		];

		$results = [];
		foreach ( $tests as $test ) {
			$results[ $test ] = $this->$test();
		}

		return $results;
	}
}
