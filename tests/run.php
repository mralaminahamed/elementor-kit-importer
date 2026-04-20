<?php
/**
 * Standalone test runner. No WP required.
 * Usage: php tests/run.php
 */

require __DIR__ . '/test-legacy-importer.php';
require __DIR__ . '/test-v4-importer.php';
require __DIR__ . '/test-all-legacy.php';

$suites = [
	'Legacy Importer'   => new Test_Legacy_Importer(),
	'V4 Importer'       => new Test_V4_Importer(),
	'All Legacy Files'  => new Test_All_Legacy(),
];

$totals = [ 'pass' => 0, 'fail' => 0, 'skip' => 0 ];

foreach ( $suites as $label => $suite ) {
	echo "\n=== {$label} ===\n";
	$results = $suite->run();
	foreach ( $results as $name => $r ) {
		$status = $r['status'];
		$totals[ $status ] = ( $totals[ $status ] ?? 0 ) + 1;
		$icon = [ 'pass' => 'PASS', 'fail' => 'FAIL', 'skip' => 'SKIP' ][ $status ];
		printf( "  [%s] %s — %s\n", $icon, $name, $r['message'] );
	}
}

echo "\n--- Summary ---\n";
printf( "Pass: %d  Fail: %d  Skip: %d\n", $totals['pass'], $totals['fail'], $totals['skip'] );

exit( $totals['fail'] > 0 ? 1 : 0 );
