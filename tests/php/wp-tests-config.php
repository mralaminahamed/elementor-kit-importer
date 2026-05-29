<?php
/**
 * wp-phpunit test config.
 *
 * @see https://github.com/WordPress/wordpress-develop/blob/trunk/wp-tests-config-sample.php
 *
 * @package Elementor_Kit_Importer\Tests
 */

$wordpress_dir = dirname( __DIR__, 2 ) . '/wordpress/';
if ( ! is_dir( $wordpress_dir ) ) {
	$wordpress_dir = dirname( __DIR__, 5 ) . '/';
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $wordpress_dir );
}

define( 'WP_DEFAULT_THEME', 'default' );
define( 'WP_DEBUG', true );

// ** Database settings — DROPS ALL TABLES with the configured prefix. Never share with prod. ** //
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'elementor_kit_importer_tests' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASS' ) ?: 'password' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Elementor Kit Importer Test' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );

// Test-only salts.
define( 'AUTH_KEY', 'elementor-kit-importer-auth-key' );
define( 'SECURE_AUTH_KEY', 'elementor-kit-importer-secure-auth-key' );
define( 'LOGGED_IN_KEY', 'elementor-kit-importer-logged-in-key' );
define( 'NONCE_KEY', 'elementor-kit-importer-nonce-key' );
define( 'AUTH_SALT', 'elementor-kit-importer-auth-salt' );
define( 'SECURE_AUTH_SALT', 'elementor-kit-importer-secure-auth-salt' );
define( 'LOGGED_IN_SALT', 'elementor-kit-importer-logged-in-salt' );
define( 'NONCE_SALT', 'elementor-kit-importer-nonce-salt' );
