<?php
/**
 * Elementor class stubs for unit tests.
 *
 * The importer only touches the kit/experiments side of Elementor's API
 * (Plugin::$instance->kits_manager / files_manager / experiments), so a minimal
 * Plugin container is enough. Tests assign their own mocks to the public
 * properties. Guards are class_exists() so a real Elementor (if present) is
 * never double-declared.
 *
 * @package Elementor_Kit_Importer\Tests
 */

declare( strict_types=1 );

namespace Elementor;

if ( ! class_exists( 'Elementor\\Plugin' ) ) {
	/**
	 * Minimal stand-in for \Elementor\Plugin.
	 */
	class Plugin {

		/**
		 * Singleton instance (set by tests).
		 *
		 * @var Plugin|null
		 */
		public static $instance;

		/**
		 * Kits manager (mock assigned by tests).
		 *
		 * @var mixed
		 */
		public $kits_manager;

		/**
		 * Files manager (mock assigned by tests).
		 *
		 * @var mixed
		 */
		public $files_manager;

		/**
		 * Experiments manager (mock assigned by tests).
		 *
		 * @var mixed
		 */
		public $experiments;
	}
}
