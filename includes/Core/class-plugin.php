<?php
/**
 * Core plugin class.
 *
 * @package Elementor_Kit_Importer
 */

declare( strict_types=1 );

namespace Elementor_Kit_Importer\Core;

use Elementor_Kit_Importer\Compat\Legacy_Adapter;
use Elementor_Kit_Importer\Importers\Import_Factory;
use Elementor_Kit_Importer\Kit\Kit_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstraps and wires the plugin. Singleton.
 */
final class Plugin {

	/**
	 * Transient key prefix for the import result notice.
	 *
	 * @var string
	 */
	public const TRANSIENT_KEY = 'elementor_kit_importer_result';

	/**
	 * Admin asset handle.
	 *
	 * @var string
	 */
	public const ASSET_HANDLE = 'elementor-kit-importer';

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Whether hooks have already been registered.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Register all WordPress hooks. Safe to call multiple times.
	 *
	 * @return void
	 */
	public function run(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
		add_action( 'admin_init', array( $this, 'process_form' ) );
		add_filter( 'upload_mimes', array( $this, 'allow_json_upload' ) );
		add_action( 'elementor/import-export/import-kit', array( $this, 'register_legacy_adapter' ), 10, 1 );
	}

	/**
	 * Enable JSON uploads in the WordPress media library.
	 *
	 * @param array $mimes Allowed mime types.
	 * @return array
	 */
	public function allow_json_upload( array $mimes ): array {
		$mimes['json'] = 'application/json';
		return $mimes;
	}

	/**
	 * Register the admin page.
	 *
	 * @return void
	 */
	public function add_admin_page(): void {
		add_submenu_page(
			'elementor',
			'Elementor Kit Importer',
			'Kit Importer',
			'manage_options',
			'elementor-kit-importer',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ): void {
		if ( 'admin_page_elementor-kit-importer' !== $hook ) {
			return;
		}

		wp_enqueue_media();

		$asset = $this->asset_manifest();

		wp_enqueue_script(
			self::ASSET_HANDLE,
			ELEMENTOR_KIT_IMPORTER_URL . 'build/index.js',
			array_merge( array( 'jquery' ), $asset['dependencies'] ),
			$asset['version'],
			true
		);

		wp_localize_script(
			self::ASSET_HANDLE,
			'elementorSettingsUpdater',
			array(
				'title'    => __( 'Select global.json or site-settings.json', 'elementor-kit-importer' ),
				'button'   => __( 'Use this file', 'elementor-kit-importer' ),
				'selected' => __( 'Selected: ', 'elementor-kit-importer' ),
			)
		);
	}

	/**
	 * Read the wp-scripts build manifest (build/index.asset.php).
	 *
	 * Falls back to the plugin version when the build is missing, so an
	 * un-built checkout never fatals.
	 *
	 * @return array<string, mixed> Manifest with 'dependencies' and 'version' keys.
	 */
	private function asset_manifest(): array {
		$path = ELEMENTOR_KIT_IMPORTER_PATH . 'build/index.asset.php';

		$default = array(
			'dependencies' => array(),
			'version'      => ELEMENTOR_KIT_IMPORTER_VERSION,
		);

		if ( ! is_readable( $path ) ) {
			return $default;
		}

		$manifest = require $path;

		return is_array( $manifest ) ? wp_parse_args( $manifest, $default ) : $default;
	}

	/**
	 * Process the form submission.
	 *
	 * @return void
	 */
	public function process_form(): void {
		if ( ! isset( $_POST['elementor_kit_importer_submit'] ) ) {
			return;
		}

		check_admin_referer( 'elementor_settings_update_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'elementor-kit-importer' ) );
		}

		$attachment_id      = intval( $_POST['json_attachment_id'] ?? 0 );
		$import_experiments = ! empty( $_POST['import_experiments'] );

		$result = $this->import_json_file( $attachment_id, $import_experiments );

		set_transient(
			self::TRANSIENT_KEY . '_' . get_current_user_id(),
			$result,
			60
		);

		wp_safe_redirect( admin_url( 'admin.php?page=elementor-kit-importer' ) );
		exit;
	}

	/**
	 * Import a JSON file using the appropriate importer.
	 *
	 * @param int  $attachment_id      Attachment ID of the uploaded file.
	 * @param bool $import_experiments  Whether to import experiments.
	 * @return array
	 */
	private function import_json_file( int $attachment_id, bool $import_experiments ): array {
		if ( $attachment_id <= 0 ) {
			return array(
				'status'  => 'error',
				'message' => 'Please select a valid JSON file.',
			);
		}

		$mime = get_post_mime_type( $attachment_id );
		if ( 'application/json' !== $mime ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid file type. Only JSON files are accepted.',
			);
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return array(
				'status'  => 'error',
				'message' => 'File not found on server.',
			);
		}

		$json_content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data         = json_decode( $json_content, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid JSON: ' . json_last_error_msg(),
			);
		}

		$factory  = new Import_Factory( new Kit_Manager() );
		$importer = $factory->get_importer( $data );

		if ( ! $importer ) {
			return array(
				'status'  => 'error',
				'message' => 'Unsupported format. Upload a valid global.json (legacy v0.4) or site-settings.json (Elementor v4).',
			);
		}

		return $importer->import( $data, $import_experiments );
	}

	/**
	 * Register the legacy compatibility adapter for Elementor imports.
	 *
	 * @param object $import Elementor import process instance.
	 * @return void
	 */
	public function register_legacy_adapter( $import ): void {
		$manifest = $import->get_manifest();

		if ( ! Legacy_Adapter::is_compatibility_needed( $manifest ) ) {
			return;
		}

		$adapters   = $import->get_adapters();
		$adapters[] = new Legacy_Adapter( $import );

		$reflection = new \ReflectionClass( $import );
		$property   = $reflection->getProperty( 'adapters' );
		$property->setAccessible( true );
		$property->setValue( $import, $adapters );
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$this->load_template( 'updater' );
	}

	/**
	 * Load a template file with scoped variables.
	 *
	 * @param string $name Template name (without extension).
	 * @param array  $vars Variables to expose to the template.
	 * @return void
	 */
	private function load_template( string $name, array $vars = array() ): void {
		$path = ELEMENTOR_KIT_IMPORTER_PATH . "templates/{$name}.php";

		if ( ! file_exists( $path ) ) {
			return;
		}

		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $path;
	}

	/**
	 * Show the import result admin notice.
	 *
	 * @return void
	 */
	public function show_admin_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'elementor-kit-importer' ) ) {
			return;
		}

		$transient_key = self::TRANSIENT_KEY . '_' . get_current_user_id();
		$result        = get_transient( $transient_key );

		if ( ! $result ) {
			return;
		}

		delete_transient( $transient_key );

		$this->load_template(
			'notice',
			array(
				'class'   => 'success' === $result['status'] ? 'notice-success' : 'notice-error',
				'message' => $result['message'],
				'details' => $result['details'] ?? array(),
			)
		);
	}
}
