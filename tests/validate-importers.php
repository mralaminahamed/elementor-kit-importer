<?php
/**
 * Importer class structure audit via Reflection.
 * Verifies every importer conforms to the interface contract + factory wiring.
 *
 * Contract:
 *   - implements Elementor_Kit_Importer_Importer
 *   - public static detect(array): bool
 *   - public import(array, bool): array
 *   - constructor takes Kit_Manager dependency
 *   - factory returns correct type per format
 *
 * Usage: php tests/validate-importers.php
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$root = dirname( __DIR__ );

require $root . '/includes/Importers/interface-importer.php';
require $root . '/includes/Kit/class-kit-manager.php';
require $root . '/includes/Importers/class-legacy-importer.php';
require $root . '/includes/Importers/class-v4-importer.php';
require $root . '/includes/Importers/class-import-factory.php';

$totals = [ 'pass' => 0, 'fail' => 0 ];

function ok( string $name, bool $cond, string $detail, array &$totals ): void {
	$icon = $cond ? 'PASS' : 'FAIL';
	$totals[ $cond ? 'pass' : 'fail' ]++;
	printf( "  [%s] %s — %s\n", $icon, $name, $detail );
}

$importers = [
	Elementor_Kit_Importer_Legacy_Importer::class,
	Elementor_Kit_Importer_V4_Importer::class,
];

echo "\n=== Interface contract ===\n";

$iface = 'Elementor_Kit_Importer_Importer';
ok( 'interface exists', interface_exists( $iface ), $iface, $totals );

$iface_ref = new ReflectionClass( $iface );
$iface_methods = array_map( fn( $m ) => $m->getName(), $iface_ref->getMethods() );
sort( $iface_methods );
ok( 'interface declares detect + import', $iface_methods === [ 'detect', 'import' ], 'methods: ' . implode( ',', $iface_methods ), $totals );

// detect signature
$detect = $iface_ref->getMethod( 'detect' );
ok( 'detect is static', $detect->isStatic(), '', $totals );
ok( 'detect returns bool', (string) $detect->getReturnType() === 'bool', 'return: ' . $detect->getReturnType(), $totals );
$detect_params = $detect->getParameters();
ok( 'detect takes 1 array param', count( $detect_params ) === 1 && (string) $detect_params[0]->getType() === 'array', '', $totals );

// import signature
$import = $iface_ref->getMethod( 'import' );
ok( 'import is instance (not static)', ! $import->isStatic(), '', $totals );
ok( 'import returns array', (string) $import->getReturnType() === 'array', 'return: ' . $import->getReturnType(), $totals );
$import_params = $import->getParameters();
$sig_ok = count( $import_params ) === 2
	&& (string) $import_params[0]->getType() === 'array'
	&& (string) $import_params[1]->getType() === 'bool';
ok( 'import signature (array, bool)', $sig_ok, 'params: ' . count( $import_params ), $totals );

echo "\n=== Per-importer class audit ===\n";

$kit = new Elementor_Kit_Importer_Kit_Manager();

foreach ( $importers as $class ) {
	echo "\n--- {$class} ---\n";

	ok( 'class exists', class_exists( $class ), '', $totals );

	$ref = new ReflectionClass( $class );

	ok( 'implements interface', $ref->implementsInterface( $iface ), '', $totals );
	ok( 'not abstract', ! $ref->isAbstract(), '', $totals );
	ok( 'is instantiable', $ref->isInstantiable(), '', $totals );

	// constructor takes Kit_Manager
	$ctor = $ref->getConstructor();
	if ( ! $ctor ) {
		ok( 'has constructor', false, 'no constructor defined', $totals );
	} else {
		$params = $ctor->getParameters();
		$dep_ok = count( $params ) === 1 && (string) $params[0]->getType() === 'Elementor_Kit_Importer_Kit_Manager';
		ok( 'constructor takes Kit_Manager', $dep_ok, 'params: ' . count( $params ), $totals );
	}

	// detect + import implemented (not inherited from interface only)
	$detect_m = $ref->getMethod( 'detect' );
	ok( 'detect declared on class', $detect_m->getDeclaringClass()->getName() === $class, 'declared on: ' . $detect_m->getDeclaringClass()->getName(), $totals );

	$import_m = $ref->getMethod( 'import' );
	ok( 'import declared on class', $import_m->getDeclaringClass()->getName() === $class, 'declared on: ' . $import_m->getDeclaringClass()->getName(), $totals );

	// Smoke: instantiate without error
	try {
		$inst = new $class( $kit );
		ok( 'instantiates cleanly', $inst instanceof $iface, get_class( $inst ), $totals );
	} catch ( Throwable $e ) {
		ok( 'instantiates cleanly', false, $e->getMessage(), $totals );
	}

	// detect() callable statically without instance
	try {
		$res = $class::detect( [] );
		ok( 'detect() callable statically with empty input', is_bool( $res ), 'returned: ' . var_export( $res, true ), $totals );
	} catch ( Throwable $e ) {
		ok( 'detect() callable statically', false, $e->getMessage(), $totals );
	}
}

echo "\n=== Factory wiring ===\n";

$factory = new Elementor_Kit_Importer_Import_Factory( $kit );
$factory_ref = new ReflectionClass( $factory );

ok( 'factory has get_importer', $factory_ref->hasMethod( 'get_importer' ), '', $totals );

$gi = $factory_ref->getMethod( 'get_importer' );
$gi_params = $gi->getParameters();
ok( 'get_importer takes array', count( $gi_params ) === 1 && (string) $gi_params[0]->getType() === 'array', '', $totals );

// dispatch table
$cases = [
	'legacy v0.4 marker'          => [ [ 'version' => '0.4', 'page_settings' => [] ], Elementor_Kit_Importer_Legacy_Importer::class ],
	'legacy via page_settings'    => [ [ 'page_settings' => [] ], Elementor_Kit_Importer_Legacy_Importer::class ],
	'v4 via settings key'         => [ [ 'settings' => [ 'x' => 1 ] ], Elementor_Kit_Importer_V4_Importer::class ],
	'ambiguous → legacy priority' => [ [ 'settings' => [ 'x' => 1 ], 'page_settings' => [] ], Elementor_Kit_Importer_Legacy_Importer::class ],
	'empty object → null'         => [ [], null ],
	'unknown shape → null'        => [ [ 'foo' => 'bar' ], null ],
	'v4 manifest → null'          => [ [ 'name' => 'x', 'title' => 'y', 'version' => '1.0', 'elementor_version' => '3.27' ], null ],
];

foreach ( $cases as $label => [ $data, $expected ] ) {
	$got = $factory->get_importer( $data );
	$got_class = $got ? get_class( $got ) : null;
	$cond = $got_class === $expected;
	ok( "dispatch: {$label}", $cond, 'expected=' . ( $expected ?? 'null' ) . ' got=' . ( $got_class ?? 'null' ), $totals );
}

echo "\n=== Loaded importer inventory ===\n";
$all = get_declared_classes();
$impls = array_filter( $all, fn( $c ) => in_array( $iface, class_implements( $c ) ?: [], true ) );
printf( "  %d class(es) implement %s:\n", count( $impls ), $iface );
foreach ( $impls as $c ) {
	printf( "    • %s\n", $c );
}

// Every importer listed must be wired into the factory dispatch for at least one detect case
foreach ( $impls as $c ) {
	$detected_by = false;
	foreach ( $cases as [ $data, $expected ] ) {
		if ( $expected === $c ) {
			$detected_by = true;
			break;
		}
	}
	ok( "factory dispatches to {$c}", $detected_by, '', $totals );
}

echo "\n--- Summary ---\n";
printf( "Pass: %d  Fail: %d\n", $totals['pass'], $totals['fail'] );

exit( $totals['fail'] > 0 ? 1 : 0 );
