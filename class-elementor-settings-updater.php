<?php
/**
 * Elementor Site Settings JSON Updater - Main Plugin Class
 *
 * @since   2.0.0
 * @version 2.0.0
 * @author  Al Amin Ahamed
 * @package Elementor_Settings_Updater
 */

use Elementor_Settings_Updater\Compat\Legacy_Adapter;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Load classes
require_once ELEMENTOR_SETTINGS_UPDATER_PATH . 'includes/Kit/class-kit-manager.php';
require_once ELEMENTOR_SETTINGS_UPDATER_PATH . 'includes/Importers/interface-importer.php';
require_once ELEMENTOR_SETTINGS_UPDATER_PATH . 'includes/Importers/class-legacy-importer.php';
require_once ELEMENTOR_SETTINGS_UPDATER_PATH . 'includes/Importers/class-v4-importer.php';
require_once ELEMENTOR_SETTINGS_UPDATER_PATH . 'includes/Importers/class-import-factory.php';

/**
 * Main plugin class
 */
class Elementor_Site_Settings_Updater {
	const TRANSIENT_KEY = 'elementor_settings_updater_result';

	/**
	 * Singleton instance
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_notices', [ $this, 'show_admin_notice' ] );
		add_action( 'admin_init', [ $this, 'process_form' ] );
		add_filter( 'upload_mimes', [ $this, 'allow_json_upload' ] );
		add_action( 'elementor/import-export/import-kit', [ $this, 'register_legacy_adapter' ], 10, 1 );
	}

	/**
	 * Enable JSON uploads in WordPress media library
	 */
	public function allow_json_upload( array $mimes ): array {
		$mimes['json'] = 'application/json';
		return $mimes;
	}

	/**
	 * Register admin page
	 */
	public function add_admin_page() {
		add_submenu_page(
			'elementor',
			'Elementor Global Settings Updater',
			'Global Settings Updater',
			'manage_options',
			'elementor-global-settings-updater',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'admin_page_elementor-global-settings-updater' ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'elementor-settings-updater',
			plugin_dir_url( ELEMENTOR_SETTINGS_UPDATER_FILE ) . 'assets/updater.js',
			[ 'jquery' ],
			ELEMENTOR_SETTINGS_UPDATER_VERSION,
			true
		);

		wp_localize_script(
			'elementor-settings-updater',
			'elementorSettingsUpdater',
			[
				'title'    => __( 'Select global.json or site-settings.json', 'elementor-settings-updater' ),
				'button'   => __( 'Use this file', 'elementor-settings-updater' ),
				'selected' => __( 'Selected: ', 'elementor-settings-updater' ),
			]
		);
	}

	/**
	 * Process form submission
	 */
	public function process_form() {
		if ( ! isset( $_POST['elementor_settings_updater_submit'] ) ) {
			return;
		}

		check_admin_referer( 'elementor_settings_update_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'elementor-settings-updater' ) );
		}

		$attachment_id      = intval( $_POST['json_attachment_id'] ?? 0 );
		$import_experiments = ! empty( $_POST['import_experiments'] );

		$result = $this->import_json_file( $attachment_id, $import_experiments );

		set_transient(
			self::TRANSIENT_KEY . '_' . get_current_user_id(),
			$result,
			60
		);

		wp_safe_redirect( admin_url( 'admin.php?page=elementor-global-settings-updater' ) );
		exit();
	}

	/**
	 * Import JSON file using appropriate importer
	 */
	private function import_json_file( int $attachment_id, bool $import_experiments ): array {
		if ( $attachment_id <= 0 ) {
			return [
				'status'  => 'error',
				'message' => 'Please select a valid JSON file.',
			];
		}

		$mime = get_post_mime_type( $attachment_id );
		if ( $mime !== 'application/json' ) {
			return [
				'status'  => 'error',
				'message' => 'Invalid file type. Only JSON files are accepted.',
			];
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return [
				'status'  => 'error',
				'message' => 'File not found on server.',
			];
		}

		$json_content = file_get_contents( $file_path );
		$data         = json_decode( $json_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return [
				'status'  => 'error',
				'message' => 'Invalid JSON: ' . json_last_error_msg(),
			];
		}

		// Use factory to get appropriate importer
		$kit_manager = new Elementor_Settings_Updater_Kit_Manager();
		$factory = new Elementor_Settings_Updater_Import_Factory( $kit_manager );
		$importer = $factory->get_importer( $data );

		if ( ! $importer ) {
			return [
				'status'  => 'error',
				'message' => 'Unsupported format. Upload a valid global.json (legacy v0.4) or site-settings.json (Elementor v4).',
			];
		}

		return $importer->import( $data, $import_experiments );
	}

	/**
	 * Register legacy compatibility adapter for Elementor imports
	 */
	public function register_legacy_adapter( $import ) {
		require_once plugin_dir_path( ELEMENTOR_SETTINGS_UPDATER_FILE ) . 'includes/Compat/legacy-adapter.php';

		$manifest = $import->get_manifest();
		if ( Legacy_Adapter::is_compatibility_needed( $manifest ) ) {
			$adapters = $import->get_adapters();
			$adapters[] = new Legacy_Adapter( $import );

			$reflection = new \ReflectionClass( $import );
			$property = $reflection->getProperty( 'adapters' );
			$property->setAccessible( true );
			$property->setValue( $import, $adapters );
		}
	}

	/**
	 * Render admin page
	 */
	public function render_page() {
		$this->load_template( 'updater' );
	}

	/**
	 * Load template file with scoped variables
	 */
	private function load_template( string $name, array $vars = [] ): void {
		$path = plugin_dir_path( ELEMENTOR_SETTINGS_UPDATER_FILE ) . "templates/{$name}.php";

		if ( ! file_exists( $path ) ) {
			return;
		}

		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $path;
	}

	/**
	 * Show admin notice
	 */
	public function show_admin_notice() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'elementor-global-settings-updater' ) === false ) {
			return;
		}

		$transient_key = self::TRANSIENT_KEY . '_' . get_current_user_id();
		$result        = get_transient( $transient_key );

		if ( ! $result ) {
			return;
		}

		delete_transient( $transient_key );

		$this->load_template( 'notice', [
			'class'   => $result['status'] === 'success' ? 'notice-success' : 'notice-error',
			'message' => $result['message'],
			'details' => $result['details'] ?? [],
		] );
	}
}
