<?php
/**
 * Plugin Name:       Elementor Site Settings JSON Updater
 * Description:       Import global.json (legacy v0.4) or site-settings.json (Elementor v4). Auto-detects format and applies colors, typography, and experiments.
 * Version:           2.0
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * Text Domain:       elementor-settings-updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'ELEMENTOR_SETTINGS_UPDATER_VERSION', '2.0' );
define( 'ELEMENTOR_SETTINGS_UPDATER_FILE', __FILE__ );
define( 'ELEMENTOR_SETTINGS_UPDATER_URL', plugin_dir_url( __FILE__ ) );
define( 'ELEMENTOR_SETTINGS_UPDATER_PATH', plugin_dir_path( __FILE__ ) );

require_once ELEMENTOR_SETTINGS_UPDATER_PATH . 'class-elementor-settings-updater.php';

/**
 * Get main plugin instance
 *
 * @return Elementor_Site_Settings_Updater
 */
function elementor_settings_updater() {
	return Elementor_Site_Settings_Updater::get_instance();
}

elementor_settings_updater();
