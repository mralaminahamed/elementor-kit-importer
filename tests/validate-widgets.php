<?php
/**
 * Widget structure audit across every legacy template.
 * Recursively walks content tree, validates element shape, tallies widget types.
 *
 * Elementor element invariants:
 *   - id        : string
 *   - elType    : section|column|container|widget|...
 *   - settings  : array
 *   - elements  : array (children)
 *   - widgetType: string (required when elType='widget')
 *
 * Usage: php tests/validate-widgets.php
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$root        = dirname( __DIR__ );
$templates   = glob( $root . '/demos/legacy/templates/*.json' );
$totals      = [ 'pass' => 0, 'fail' => 0 ];
$widget_tally = [];
$elt_tally    = [];
$errors       = [];

/**
 * Recursively walk element tree. Returns [ ok, error_list ].
 */
function walk( array $el, string $path, array &$widget_tally, array &$elt_tally ): array {
	$errs = [];

	if ( ! isset( $el['id'] ) || ! is_string( $el['id'] ) ) {
		$errs[] = "{$path}: missing/invalid id";
	}

	$el_type = $el['elType'] ?? null;
	if ( ! is_string( $el_type ) || $el_type === '' ) {
		$errs[] = "{$path}: missing elType";
		return $errs;
	}

	$elt_tally[ $el_type ] = ( $elt_tally[ $el_type ] ?? 0 ) + 1;

	if ( ! isset( $el['settings'] ) || ! is_array( $el['settings'] ) ) {
		$errs[] = "{$path}: settings missing or not array (elType={$el_type})";
	}

	if ( ! isset( $el['elements'] ) || ! is_array( $el['elements'] ) ) {
		$errs[] = "{$path}: elements missing or not array (elType={$el_type})";
	}

	if ( $el_type === 'widget' ) {
		$wt = $el['widgetType'] ?? null;
		if ( ! is_string( $wt ) || $wt === '' ) {
			$errs[] = "{$path}: widget missing widgetType";
		} else {
			$widget_tally[ $wt ] = ( $widget_tally[ $wt ] ?? 0 ) + 1;
		}
	}

	$children = $el['elements'] ?? [];
	foreach ( $children as $i => $child ) {
		if ( ! is_array( $child ) ) {
			$errs[] = "{$path}.elements[{$i}]: not an array";
			continue;
		}
		$child_path = $path . '.elements[' . $i . ']';
		$errs = array_merge( $errs, walk( $child, $child_path, $widget_tally, $elt_tally ) );
	}

	return $errs;
}

echo "\n=== Widget structure audit (legacy templates) ===\n";

foreach ( $templates as $path ) {
	$name = basename( $path );
	$data = json_decode( file_get_contents( $path ), true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		printf( "  [FAIL] %s — invalid json\n", $name );
		$totals['fail']++;
		continue;
	}

	$content = $data['content'] ?? [];

	if ( empty( $content ) ) {
		printf( "  [SKIP] %-24s — empty content (no widgets)\n", $name );
		continue;
	}

	$file_errs = [];
	$top_count = 0;
	foreach ( $content as $i => $el ) {
		if ( ! is_array( $el ) ) {
			$file_errs[] = "content[{$i}]: not an array";
			continue;
		}
		$file_errs = array_merge( $file_errs, walk( $el, "content[{$i}]", $widget_tally, $elt_tally ) );
		$top_count++;
	}

	if ( ! empty( $file_errs ) ) {
		printf( "  [FAIL] %-24s — %d issue(s)\n", $name, count( $file_errs ) );
		foreach ( $file_errs as $e ) {
			echo "         ↳ {$e}\n";
		}
		$totals['fail']++;
		$errors[ $name ] = $file_errs;
	} else {
		printf( "  [PASS] %-24s — %d top-level elements, tree valid\n", $name, $top_count );
		$totals['pass']++;
	}
}

echo "\n=== Element type tally (all templates) ===\n";
arsort( $elt_tally );
foreach ( $elt_tally as $type => $n ) {
	printf( "  %-15s %d\n", $type, $n );
}

echo "\n=== Widget type tally (widgetType field) ===\n";
arsort( $widget_tally );
printf( "  %d distinct widget types, %d total widget instances\n\n",
	count( $widget_tally ),
	array_sum( $widget_tally )
);
foreach ( $widget_tally as $type => $n ) {
	printf( "  %-32s %d\n", $type, $n );
}

echo "\n--- Summary ---\n";
printf( "Pass: %d  Fail: %d\n", $totals['pass'], $totals['fail'] );

exit( $totals['fail'] > 0 ? 1 : 0 );
