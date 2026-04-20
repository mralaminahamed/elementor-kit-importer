<?php
/**
 * V4 Importer Tests
 */

class Test_V4_Importer {
	private $v4_file;

	public function __construct() {
		$this->v4_file = dirname( __DIR__ ) . '/demos/v4/site-kit-settings/site-settings.json';
	}

	/**
	 * Test v4 format detection
	 */
	public function test_detect_v4_format() {
		if ( ! file_exists( $this->v4_file ) ) {
			return [ 'status' => 'skip', 'message' => 'V4 file not found' ];
		}

		$data = json_decode( file_get_contents( $this->v4_file ), true );

		// Check settings key
		if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			return [ 'status' => 'fail', 'message' => 'settings key missing or not array' ];
		}

		// Should NOT have page_settings
		if ( isset( $data['page_settings'] ) ) {
			return [ 'status' => 'fail', 'message' => 'V4 should not have page_settings' ];
		}

		return [ 'status' => 'pass', 'message' => 'V4 format detected correctly' ];
	}

	/**
	 * Test manifest structure
	 */
	public function test_manifest_structure() {
		if ( ! file_exists( $this->v4_file ) ) {
			return [ 'status' => 'skip', 'message' => 'V4 file not found' ];
		}

		$data = json_decode( file_get_contents( $this->v4_file ), true );

		// V4 kit export schema (top-level): content, settings, metadata, theme, experiments
		$required_keys = [ 'settings', 'experiments' ];
		$missing = [];

		foreach ( $required_keys as $key ) {
			if ( ! isset( $data[ $key ] ) ) {
				$missing[] = $key;
			}
		}

		if ( ! empty( $missing ) ) {
			return [ 'status' => 'fail', 'message' => 'Missing keys: ' . implode( ', ', $missing ) ];
		}

		$keys = implode( ',', array_keys( $data ) );
		return [ 'status' => 'pass', 'message' => "V4 schema ok (top-level: {$keys})" ];
	}

	/**
	 * Test experiments/feature flags present
	 */
	public function test_experiments_present() {
		if ( ! file_exists( $this->v4_file ) ) {
			return [ 'status' => 'skip', 'message' => 'V4 file not found' ];
		}

		$data = json_decode( file_get_contents( $this->v4_file ), true );

		if ( ! isset( $data['experiments'] ) ) {
			return [ 'status' => 'fail', 'message' => 'experiments key missing' ];
		}

		if ( ! is_array( $data['experiments'] ) || empty( $data['experiments'] ) ) {
			return [ 'status' => 'fail', 'message' => 'experiments should be non-empty array' ];
		}

		$count = count( $data['experiments'] );
		return [ 'status' => 'pass', 'message' => "Found {$count} experiments/feature flags" ];
	}

	/**
	 * Run all tests
	 */
	public function run() {
		$tests = [
			'test_detect_v4_format',
			'test_manifest_structure',
			'test_experiments_present',
		];

		$results = [];
		foreach ( $tests as $test ) {
			$results[ $test ] = $this->$test();
		}

		return $results;
	}
}
