<?php
/**
 * Plugin Name:       Elementor Site Settings JSON Updater
 * Description:       Import global.json (legacy v0.4) or site-settings.json (Elementor v4). Auto-detects format and applies colors, typography, and experiments.
 * Version:           2.0
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * Text Domain:       elementor-settings-updater
 */

if ( ! defined( "ABSPATH" ) ) {
	exit();
}

class Elementor_Site_Settings_Updater {
	const TRANSIENT_KEY = "elementor_settings_updater_result";

	public function __construct() {
		add_action( "admin_menu", [ $this, "add_admin_page" ] );
		add_action( "admin_enqueue_scripts", [ $this, "enqueue_scripts" ] );
		add_action( "admin_notices", [ $this, "show_admin_notice" ] );
		add_action( "admin_init", [ $this, "process_form" ] );
		add_filter( "upload_mimes", [ $this, "allow_json_upload" ] );
	}

	// WordPress blocks JSON uploads by default — this enables them for the media library.
	public function allow_json_upload( array $mimes ): array {
		$mimes["json"] = "application/json";

		return $mimes;
	}

	public function add_admin_page() {
		add_submenu_page(
			"elementor",
			"Elementor Global Settings Updater",
			"Global Settings Updater",
			"manage_options",
			"elementor-global-settings-updater",
			[ $this, "render_page" ],
        );
	}

	public function enqueue_scripts( $hook ) {
		error_log( "hook: " . $hook );
		if ( $hook !== "admin_page_elementor-global-settings-updater" ) {
			return;
		}

		// Loads all required media uploader scripts, styles, and localized data.
		wp_enqueue_media();

		wp_enqueue_script(
			"elementor-settings-updater",
			plugin_dir_url( __FILE__ ) . "assets/updater.js",
			[ "jquery" ],
			"2.0.0",
			true,
        );

		// Pass translatable strings and config to JS via localized data object.
		wp_localize_script(
			"elementor-settings-updater",
			"elementorSettingsUpdater",
			[
				"title"    => __(
					"Select global.json or site-settings.json",
					"elementor-settings-updater",
                ),
				"button"   => __( "Use this file", "elementor-settings-updater" ),
				"selected" => __( "Selected: ", "elementor-settings-updater" ),
			],
        );
	}

	// Processes POST before admin_notices fires — fixes the notice-never-shows bug.
	public function process_form() {
		if ( ! isset( $_POST["elementor_settings_updater_submit"] ) ) {
			return;
		}

		check_admin_referer( "elementor_settings_update_nonce" );

		if ( ! current_user_can( "manage_options" ) ) {
			wp_die(
				esc_html__(
					"Insufficient permissions.",
					"elementor-settings-updater",
                ),
            );
		}

		$attachment_id      = intval( $_POST["json_attachment_id"] ?? 0 );
		$import_experiments = ! empty( $_POST["import_experiments"] );

		$result = $this->validate_and_import(
			$attachment_id,
			$import_experiments,
        );

		set_transient(
			self::TRANSIENT_KEY . "_" . get_current_user_id(),
			$result,
			60,
        );

		wp_safe_redirect(
			admin_url( "admin.php?page=elementor-global-settings-updater" ),
        );
		exit();
	}

	private function validate_and_import( int $attachment_id, bool $import_experiments ): array {
		if ( $attachment_id <= 0 ) {
			return [
				"status"  => "error",
				"message" => "Please select a valid JSON file.",
			];
		}

		// Server-side MIME validation — client-side filter is bypassable.
		$mime = get_post_mime_type( $attachment_id );
		if ( $mime !== "application/json" ) {
			return [
				"status"  => "error",
				"message" => "Invalid file type. Only JSON files are accepted.",
			];
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return [
				"status"  => "error",
				"message" => "File not found on server.",
			];
		}

		return $this->import_settings( $file_path, $import_experiments );
	}

	private function import_settings( string $json_path, bool $import_experiments ): array {
		$json_content = file_get_contents( $json_path );
		$data         = json_decode( $json_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return [
				"status"  => "error",
				"message" => "Invalid JSON: " . json_last_error_msg(),
			];
		}

		if ( ! class_exists( "\Elementor\Plugin" ) ) {
			return [
				"status"  => "error",
				"message" => "Elementor plugin is not active.",
			];
		}

		$elementor = \Elementor\Plugin::$instance;

		if ( empty( $elementor->kits_manager ) ) {
			return [
				"status"  => "error",
				"message" =>
					"Elementor Kits Manager not available. Please update Elementor.",
			];
		}

		switch ( $this->detect_format( $data ) ) {
			case "legacy":
				return $this->import_legacy( $data, $elementor );
			case "v4":
				return $this->import_v4( $data, $elementor, $import_experiments );
			default:
				return [
					"status"  => "error",
					"message" =>
						"Unsupported format. Upload a valid global.json (legacy v0.4) or site-settings.json (Elementor v4).",
				];
		}
	}

	/**
	 * Legacy:  { "page_settings": { ... } }  — version 0.4 or unversioned
	 * v4:      { "settings": { ... }, "experiments": { ... }, "theme": { ... } }
	 */
	private function detect_format( array $data ): string {
		if ( isset( $data["page_settings"] ) && is_array( $data["page_settings"] ) ) {
			return "legacy";
		}

		if (
			isset( $data["settings"] ) &&
			is_array( $data["settings"] ) &&
			! isset( $data["page_settings"] )
		) {
			return "v4";
		}

		return "unknown";
	}

	private function import_legacy( array $data, $elementor ): array {
		$settings = $data["page_settings"];
		$label    =
			isset( $data["version"] ) && $data["version"] === "0.4"
				? "Legacy v0.4 format detected."
				: "Legacy site-settings format detected.";

		$active_kit = $elementor->kits_manager->get_active_kit_for_frontend();
		if ( ! $active_kit ) {
			return [
				"status"  => "error",
				"message" => "No active Elementor kit found.",
			];
		}

		$save_result = $active_kit->save( [ "settings" => $settings ] );
		$this->clear_cache( $elementor );

		if ( ! $save_result ) {
			return [
				"status"  => "error",
				"message" => "Failed to save settings to active kit.",
			];
		}

		return [
			"status"  => "success",
			"message" => $label . " Settings applied to active kit.",
			"details" => [
				"Format"            => "Legacy v0.4",
				"System colors"     => count( $settings["system_colors"] ?? [] ),
				"Custom colors"     => count( $settings["custom_colors"] ?? [] ),
				"System typography" => count(
					$settings["system_typography"] ?? [],
                ),
				"Custom typography" => count(
					$settings["custom_typography"] ?? [],
                ),
			],
		];
	}

	private function import_v4( array $data, $elementor, bool $import_experiments ): array {
		$settings = $data["settings"] ?? [];
		$messages = [];
		$details  = [ "Format" => "Elementor v4" ];
		$status   = "success";

		// Strip non-portable site identity fields.
		foreach (
			[ "site_name", "site_description", "site_logo", "site_favicon" ]
			as $key
		) {
			unset( $settings[ $key ] );
		}

		$active_kit = $elementor->kits_manager->get_active_kit_for_frontend();
		if ( ! $active_kit ) {
			return [
				"status"  => "error",
				"message" => "No active Elementor kit found.",
			];
		}

		// Apply kit settings.
		if ( ! empty( $settings ) ) {
			$saved = $active_kit->save( [ "settings" => $settings ] );
			if ( $saved ) {
				$messages[]                   = "Kit settings applied.";
				$details["System colors"]     = count(
					$settings["system_colors"] ?? [],
                );
				$details["Custom colors"]     = count(
					$settings["custom_colors"] ?? [],
                );
				$details["System typography"] = count(
					$settings["system_typography"] ?? [],
                );
				$details["Custom typography"] = count(
					$settings["custom_typography"] ?? [],
                );
			} else {
				$messages[] = "Failed to apply kit settings.";
				$status     = "error";
			}
		} else {
			$messages[]          =
				"No kit settings found in file (settings block is empty).";
			$details["Settings"] = "empty";
		}

		// Apply experiments when requested.
		if ( $import_experiments ) {
			if ( ! empty( $data["experiments"] ) ) {
				$count                          = $this->apply_experiments(
					$data["experiments"],
					$elementor,
                );
				$messages[]                     = "Experiments: {$count} feature flag(s) updated.";
				$details["Experiments updated"] = $count;
			} else {
				$messages[] = "No experiments data found in file.";
			}
		}

		$this->clear_cache( $elementor );

		return [
			"status"  => $status,
			"message" => implode( " ", $messages ),
			"details" => $details,
		];
	}

	private function apply_experiments( array $experiments_data, $elementor ): int {
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

			$new_state     = $feature_data["state"] ?? "default";
			$current_state =
				$current_features[ $feature_name ]["state"] ?? "default";

			if ( $current_state === $new_state ) {
				continue;
			}

			if (
				! in_array( $new_state, [ "default", "active", "inactive" ], true )
			) {
				continue;
			}

			$option_key = $manager->get_feature_option_key( $feature_name );

			if ( $new_state === "default" ) {
				delete_option( $option_key );
			} else {
				update_option( $option_key, $new_state );
			}

			$count ++;
		}

		return $count;
	}

	private function clear_cache( $elementor ): void {
		if ( method_exists( $elementor->files_manager, "clear_cache" ) ) {
			$elementor->files_manager->clear_cache();
		}
	}

	public function render_page() {
		$this->load_template( "updater" );
	}

	private function load_template( string $name, array $vars = [] ): void {
		$path = plugin_dir_path( __FILE__ ) . "templates/{$name}.php";

		if ( ! file_exists( $path ) ) {
			return;
		}

		// Extract vars into local scope so templates can use $class, $message, etc.
		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $path;
	}

	public function show_admin_notice() {
		$screen = get_current_screen();
		if (
			! $screen ||
			strpos( $screen->id, "elementor-global-settings-updater" ) === false
		) {
			return;
		}

		$transient_key = self::TRANSIENT_KEY . "_" . get_current_user_id();
		$result        = get_transient( $transient_key );

		if ( ! $result ) {
			return;
		}

		delete_transient( $transient_key );

		$this->load_template( "notice", [
			"class"   =>
				$result["status"] === "success"
					? "notice-success"
					: "notice-error",
			"message" => $result["message"],
			"details" => $result["details"] ?? [],
		] );
	}
}

new Elementor_Site_Settings_Updater();
