<?php
/**
 * Elementor Kit Importer
 *
 * Import global.json (legacy v0.4) or site-settings.json (Elementor v4).
 * Auto-detects format and applies colors, typography, and experiments.
 *
 * @link              https://alaminahamed.com
 * @since             2.0.0
 * @package           Elementor_Kit_Importer
 *
 * @wordpress-plugin
 * Plugin Name:       Elementor Kit Importer
 * Description:       Import global.json (legacy v0.4) or site-settings.json (Elementor v4). Auto-detects format and applies colors, typography, and experiments.
 * Version:           2.0.1
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       elementor-kit-importer
 * Domain Path:       /languages
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

define( 'ELEMENTOR_KIT_IMPORTER_VERSION', '2.0.1' );
define( 'ELEMENTOR_KIT_IMPORTER_FILE', __FILE__ );
define( 'ELEMENTOR_KIT_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ELEMENTOR_KIT_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'ELEMENTOR_KIT_IMPORTER_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Get the main plugin instance.
 *
 * @return \Elementor_Kit_Importer\Core\Plugin
 */
function elementor_kit_importer(): \Elementor_Kit_Importer\Core\Plugin {
	return \Elementor_Kit_Importer\Core\Plugin::get_instance();
}

elementor_kit_importer()->run();
