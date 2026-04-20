<?php
/**
 * Plugin Name:       Elementor Kit Importer
 * Description:       Import global.json (legacy v0.4) or site-settings.json (Elementor v4). Auto-detects format and applies colors, typography, and experiments.
 * Version:           2.0
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * Text Domain:       elementor-kit-importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'ELEMENTOR_KIT_IMPORTER_VERSION', '2.0' );
define( 'ELEMENTOR_KIT_IMPORTER_FILE', __FILE__ );
define( 'ELEMENTOR_KIT_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'ELEMENTOR_KIT_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );

require_once ELEMENTOR_KIT_IMPORTER_PATH . 'class-elementor-kit-importer.php';

/**
 * Get main plugin instance
 *
 * @return Elementor_Kit_Importer
 */
function elementor_kit_importer() {
	return Elementor_Kit_Importer::get_instance();
}

elementor_kit_importer();
