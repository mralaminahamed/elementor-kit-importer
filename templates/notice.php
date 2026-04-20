<?php
/**
 * Admin notice template for Elementor Kit Importer.
 *
 * @var string $class   CSS notice class — 'notice-success' or 'notice-error'.
 * @var string $message Human-readable result message.
 * @var array  $details Optional key-value detail rows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div class="notice <?php echo esc_attr( $class ); ?> is-dismissible">
	<p><strong><?php echo esc_html( $message ); ?></strong></p>

	<?php if ( ! empty( $details ) ) : ?>
		<ul style="margin:4px 0 8px 20px; list-style:disc;">
			<?php foreach ( $details as $label => $value ) : ?>
				<li><?php echo esc_html( $label ); ?>: <strong><?php echo esc_html( (string) $value ); ?></strong></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
