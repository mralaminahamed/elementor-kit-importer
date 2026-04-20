<?php
/**
 * Admin page template for Elementor Kit Importer.
 *
 * @var string $nonce_field wp_nonce_field output — call before including this template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Elementor Kit Importer', 'elementor-kit-importer' ); ?></h1>
	<p>
		<?php
		printf(
			/* translators: 1: global.json 2: site-settings.json */
			esc_html__( 'Upload %1$s (legacy v0.4) or %2$s (Elementor v4). Format is auto-detected.', 'elementor-kit-importer' ),
			'<strong>global.json</strong>',
			'<strong>site-settings.json</strong>'
		);
		?>
	</p>

	<form method="post">
		<?php wp_nonce_field( 'elementor_settings_update_nonce' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<?php esc_html_e( 'JSON File', 'elementor-kit-importer' ); ?>
				</th>
				<td>
					<input type="hidden" id="json_attachment_id" name="json_attachment_id" value="">
					<button type="button" class="button button-secondary" id="upload_json_btn">
						<?php esc_html_e( 'Select / Upload JSON File', 'elementor-kit-importer' ); ?>
					</button>
					<p id="selected_file" style="margin:10px 0; font-style:italic; color:#006799;"></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Options', 'elementor-kit-importer' ); ?>
				</th>
				<td>
					<fieldset>
						<label>
							<input type="checkbox" name="import_experiments" value="1">
							<?php esc_html_e( 'Import experiments / feature flags', 'elementor-kit-importer' ); ?>
							<span style="color:#888;"><?php esc_html_e( '(v4 only)', 'elementor-kit-importer' ); ?></span>
						</label>
						<p class="description">
							<?php esc_html_e( 'Updates Elementor feature flag states to match the exported file.', 'elementor-kit-importer' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input
				type="submit"
				name="elementor_kit_importer_submit"
				class="button button-primary button-large"
				value="<?php esc_attr_e( 'Apply & Import Settings', 'elementor-kit-importer' ); ?>"
			>
		</p>
	</form>

	<div class="notice notice-info inline" style="margin-top:16px;">
		<p>
			<strong><?php esc_html_e( 'Supported formats:', 'elementor-kit-importer' ); ?></strong><br>
			&bull; <strong><?php esc_html_e( 'Legacy v0.4', 'elementor-kit-importer' ); ?></strong>
			&mdash; <code>global.json</code> <?php esc_html_e( 'exported from Elementor &lt; 4.x', 'elementor-kit-importer' ); ?><br>
			&bull; <strong><?php esc_html_e( 'Elementor v4', 'elementor-kit-importer' ); ?></strong>
			&mdash; <code>site-settings.json</code> <?php esc_html_e( 'exported from Elementor 4.x', 'elementor-kit-importer' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'After import:', 'elementor-kit-importer' ); ?></strong>
			<?php esc_html_e( 'Go to', 'elementor-kit-importer' ); ?>
			<strong><?php esc_html_e( 'Elementor → Tools → Regenerate CSS & Data', 'elementor-kit-importer' ); ?></strong>.
		</p>
	</div>
</div>
