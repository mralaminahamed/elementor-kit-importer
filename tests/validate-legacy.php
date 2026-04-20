<?php
/**
 * Integration validator: runs real importer classes against every legacy demo file.
 * Stubs Kit_Manager (no WP/Elementor required).
 *
 * Usage: php tests/validate-legacy.php
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$root = dirname( __DIR__ );

require $root . '/includes/Importers/interface-importer.php';
require $root . '/includes/Kit/class-kit-manager.php';
require $root . '/includes/Importers/class-legacy-importer.php';
require $root . '/includes/Importers/class-v4-importer.php';
require $root . '/includes/Importers/class-import-factory.php';

/**
 * Test double — captures apply_settings() calls, never touches Elementor.
 */
class Stub_Kit_Manager extends Elementor_Kit_Importer_Kit_Manager {
	public $captured;
	public $call_count = 0;

	public function apply_settings( array $settings ): array {
		$this->captured = $settings;
		$this->call_count++;
		return [ 'success' => true ];
	}
}

$legacy_dir = $root . '/demos/legacy';
$kit        = new Stub_Kit_Manager();
$factory    = new Elementor_Kit_Importer_Import_Factory( $kit );

$totals = [ 'pass' => 0, 'fail' => 0 ];

function report( string $name, string $expected, array $result, array &$totals ): void {
	$actual = $result['status'];
	$ok     = $actual === $expected;
	$totals[ $ok ? 'pass' : 'fail' ]++;
	$icon = $ok ? 'PASS' : 'FAIL';
	printf(
		"  [%s] %s — expected=%s got=%s :: %s\n",
		$icon,
		$name,
		$expected,
		$actual,
		$result['message']
	);
}

echo "\n=== Global styles (site-kit-settings/global.json) ===\n";
$global_path = $legacy_dir . '/site-kit-settings/global.json';
$data        = json_decode( file_get_contents( $global_path ), true );
$importer    = $factory->get_importer( $data );

if ( ! $importer instanceof Elementor_Kit_Importer_Legacy_Importer ) {
	echo "  [FAIL] factory did not return Legacy_Importer (got " . ( $importer ? get_class( $importer ) : 'null' ) . ")\n";
	$totals['fail']++;
} else {
	$result = $importer->import( $data, false );
	report( 'global.json', 'success', $result, $totals );

	// Verify details populated with counts from stub capture
	$captured = $kit->captured;
	$sys_c    = count( $captured['system_colors'] ?? [] );
	$cus_c    = count( $captured['custom_colors'] ?? [] );
	$sys_t    = count( $captured['system_typography'] ?? [] );
	$cus_t    = count( $captured['custom_typography'] ?? [] );
	printf( "         → kit_manager received: sys_colors=%d custom_colors=%d sys_typo=%d custom_typo=%d\n", $sys_c, $cus_c, $sys_t, $cus_t );
}

echo "\n=== Templates (templates/*.json) — expect rejection ===\n";
$template_files = glob( $legacy_dir . '/templates/*.json' );
$pre_call_count = $kit->call_count;

foreach ( $template_files as $path ) {
	$name     = basename( $path );
	$data     = json_decode( file_get_contents( $path ), true );
	$importer = $factory->get_importer( $data );

	if ( ! $importer instanceof Elementor_Kit_Importer_Legacy_Importer ) {
		printf( "  [FAIL] %s — factory returned %s\n", $name, $importer ? get_class( $importer ) : 'null' );
		$totals['fail']++;
		continue;
	}

	$result   = $importer->import( $data, false );
	$has_content = ! empty( $data['content'] );
	$expected    = $has_content ? 'error' : 'error'; // both empty and non-empty templates should error (no page_settings)

	// Empty-content templates hit the "No settings found" branch — still error, good.
	report( $name, $expected, $result, $totals );
}

// Invariant: kit_manager must NOT have been called for any template
$template_calls = $kit->call_count - $pre_call_count;
if ( $template_calls !== 0 ) {
	printf( "\n  [FAIL] kit_manager was called %d time(s) for template files — should be 0\n", $template_calls );
	$totals['fail']++;
} else {
	echo "\n  [PASS] kit_manager never called for template files (correctly rejected before apply)\n";
	$totals['pass']++;
}

echo "\n--- Summary ---\n";
printf( "Pass: %d  Fail: %d\n", $totals['pass'], $totals['fail'] );

exit( $totals['fail'] > 0 ? 1 : 0 );
