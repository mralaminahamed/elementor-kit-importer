<?php
/**
 * Admin page template for Elementor Site Settings Updater.
 *
 * @var string $nonce_field wp_nonce_field output — call before including this template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Elementor Global Settings Updater', 'elementor-settings-updater' ); ?></h1>
	<p>
		<?php
		printf(
			/* translators: 1: global.json 2: site-settings.json */
			esc_html__( 'Upload %1$s (legacy v0.4) or %2$s (Elementor v4). Format is auto-detected.', 'elementor-settings-updater' ),
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
					<?php esc_html_e( 'JSON File', 'elementor-settings-updater' ); ?>
				</th>
				<td>
					<input type="hidden" id="json_attachment_id" name="json_attachment_id" value="">
					<button type="button" class="button button-secondary" id="upload_json_btn">
						<?php esc_html_e( 'Select / Upload JSON File', 'elementor-settings-updater' ); ?>
					</button>
					<p id="selected_file" style="margin:10px 0; font-style:italic; color:#006799;"></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Options', 'elementor-settings-updater' ); ?>
				</th>
				<td>
					<fieldset>
						<label>
							<input type="checkbox" name="import_experiments" value="1">
							<?php esc_html_e( 'Import experiments / feature flags', 'elementor-settings-updater' ); ?>
							<span style="color:#888;"><?php esc_html_e( '(v4 only)', 'elementor-settings-updater' ); ?></span>
						</label>
						<p class="description">
							<?php esc_html_e( 'Updates Elementor feature flag states to match the exported file.', 'elementor-settings-updater' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input
				type="submit"
				name="elementor_settings_updater_submit"
				class="button button-primary button-large"
				value="<?php esc_attr_e( 'Apply & Import Settings', 'elementor-settings-updater' ); ?>"
			>
		</p>
	</form>

	<div class="notice notice-info inline" style="margin-top:16px;">
		<p>
			<strong><?php esc_html_e( 'Supported formats:', 'elementor-settings-updater' ); ?></strong><br>
			&bull; <strong><?php esc_html_e( 'Legacy v0.4', 'elementor-settings-updater' ); ?></strong>
			&mdash; <code>global.json</code> <?php esc_html_e( 'exported from Elementor &lt; 4.x', 'elementor-settings-updater' ); ?><br>
			&bull; <strong><?php esc_html_e( 'Elementor v4', 'elementor-settings-updater' ); ?></strong>
			&mdash; <code>site-settings.json</code> <?php esc_html_e( 'exported from Elementor 4.x', 'elementor-settings-updater' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'After import:', 'elementor-settings-updater' ); ?></strong>
			<?php esc_html_e( 'Go to', 'elementor-settings-updater' ); ?>
			<strong><?php esc_html_e( 'Elementor → Tools → Regenerate CSS & Data', 'elementor-settings-updater' ); ?></strong>.
		</p>
	</div>
</div>
