<?php
/**
 * Integration validator: every demo file + edge cases through real Import_Factory.
 * Stubs Kit_Manager. No WP/Elementor required.
 *
 * Usage: php tests/validate-all.php
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$root = dirname( __DIR__ );

require $root . '/includes/Importers/interface-importer.php';
require $root . '/includes/Kit/class-kit-manager.php';
require $root . '/includes/Importers/class-legacy-importer.php';
require $root . '/includes/Importers/class-v4-importer.php';
require $root . '/includes/Importers/class-import-factory.php';

class Stub_Kit_Manager extends Elementor_Kit_Importer_Kit_Manager {
	public $captured   = [];
	public $call_count = 0;

	public function apply_settings( array $settings ): array {
		$this->captured = $settings;
		$this->call_count++;
		return [ 'success' => true ];
	}
}

$totals = [ 'pass' => 0, 'fail' => 0 ];

function check( string $name, $expected_class, string $expected_status, array $data, $factory, &$totals, array $extra = [] ): void {
	$importer = $factory->get_importer( $data );

	if ( $expected_class === null ) {
		$ok = $importer === null;
		$icon = $ok ? 'PASS' : 'FAIL';
		$got = $importer ? get_class( $importer ) : 'null';
		printf( "  [%s] %s — expected=null got=%s\n", $icon, $name, $got );
		$totals[ $ok ? 'pass' : 'fail' ]++;
		return;
	}

	if ( ! $importer instanceof $expected_class ) {
		printf( "  [FAIL] %s — expected=%s got=%s\n", $name, $expected_class, $importer ? get_class( $importer ) : 'null' );
		$totals['fail']++;
		return;
	}

	$import_experiments = $extra['experiments'] ?? false;
	$result = $importer->import( $data, $import_experiments );
	$ok = $result['status'] === $expected_status;
	$icon = $ok ? 'PASS' : 'FAIL';
	printf( "  [%s] %s — expected=%s got=%s :: %s\n", $icon, $name, $expected_status, $result['status'], $result['message'] );
	$totals[ $ok ? 'pass' : 'fail' ]++;

	if ( isset( $extra['check_details'] ) ) {
		$extra['check_details']( $result, $totals );
	}
}

$legacy_dir = $root . '/demos/legacy';
$v4_dir     = $root . '/demos/v4';
$kit        = new Stub_Kit_Manager();
$factory    = new Elementor_Kit_Importer_Import_Factory( $kit );

// ────── Legacy global styles ──────
echo "\n=== Legacy: site-kit-settings/global.json (expect accept) ===\n";
$data = json_decode( file_get_contents( $legacy_dir . '/site-kit-settings/global.json' ), true );
check( 'global.json', Elementor_Kit_Importer_Legacy_Importer::class, 'success', $data, $factory, $totals, [
	'check_details' => function ( $result, &$totals ) {
		$d = $result['details'] ?? [];
		$ok = ( $d['System colors'] ?? 0 ) === 4
			&& ( $d['Custom colors'] ?? 0 ) === 14
			&& ( $d['System typography'] ?? 0 ) === 4
			&& ( $d['Custom typography'] ?? 0 ) === 9;
		printf( "    %s details: sys_c=%d custom_c=%d sys_t=%d custom_t=%d\n",
			$ok ? '[PASS]' : '[FAIL]',
			$d['System colors'] ?? -1, $d['Custom colors'] ?? -1,
			$d['System typography'] ?? -1, $d['Custom typography'] ?? -1
		);
		$totals[ $ok ? 'pass' : 'fail' ]++;
	},
] );

// ────── Legacy templates (all 19) ──────
echo "\n=== Legacy: templates/*.json (expect reject) ===\n";
$template_files = glob( $legacy_dir . '/templates/*.json' );
$pre_count      = $kit->call_count;

foreach ( $template_files as $path ) {
	$name = basename( $path );
	$data = json_decode( file_get_contents( $path ), true );
	check( $name, Elementor_Kit_Importer_Legacy_Importer::class, 'error', $data, $factory, $totals );
}

if ( $kit->call_count !== $pre_count ) {
	printf( "  [FAIL] kit_manager called %d time(s) during template loop — should be 0\n", $kit->call_count - $pre_count );
	$totals['fail']++;
} else {
	echo "  [PASS] kit_manager never called for any template\n";
	$totals['pass']++;
}

// ────── V4 site-settings ──────
echo "\n=== V4: site-kit-settings/site-settings.json (expect accept) ===\n";
$data = json_decode( file_get_contents( $v4_dir . '/site-kit-settings/site-settings.json' ), true );
check( 'site-settings.json', Elementor_Kit_Importer_V4_Importer::class, 'success', $data, $factory, $totals, [
	'experiments'   => false,
	'check_details' => function ( $result, &$totals ) {
		$d = $result['details'] ?? [];
		$ok = ( $d['Format'] ?? '' ) === 'Elementor v4'
			&& isset( $d['System colors'] )
			&& isset( $d['Custom colors'] );
		printf( "    %s details: format=%s sys_c=%d custom_c=%d sys_t=%d custom_t=%d\n",
			$ok ? '[PASS]' : '[FAIL]',
			$d['Format'] ?? '?',
			$d['System colors'] ?? -1, $d['Custom colors'] ?? -1,
			$d['System typography'] ?? -1, $d['Custom typography'] ?? -1
		);
		$totals[ $ok ? 'pass' : 'fail' ]++;
	},
] );

// ────── V4 manifest (not a settings file — factory should reject) ──────
echo "\n=== V4: site-kit-settings/manifest.json (expect factory null — not a settings file) ===\n";
$data = json_decode( file_get_contents( $v4_dir . '/site-kit-settings/manifest.json' ), true );
check( 'manifest.json', null, '', $data, $factory, $totals );

// ────── Edge cases ──────
echo "\n=== Edge cases ===\n";
check( 'empty object {}', null, '', [], $factory, $totals );
check( 'unknown format', null, '', [ 'foo' => 'bar' ], $factory, $totals );
check( 'legacy w/o version but has page_settings', Elementor_Kit_Importer_Legacy_Importer::class, 'error', [
	'page_settings' => [],
], $factory, $totals );
check( 'v4 with page_settings (ambiguous — legacy wins)', Elementor_Kit_Importer_Legacy_Importer::class, 'error', [
	'settings'      => [ 'foo' => 'bar' ],
	'page_settings' => [],
], $factory, $totals );

echo "\n--- Summary ---\n";
printf( "Pass: %d  Fail: %d\n", $totals['pass'], $totals['fail'] );

exit( $totals['fail'] > 0 ? 1 : 0 );
