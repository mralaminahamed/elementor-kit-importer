=== Elementor Site Settings JSON Updater ===
Contributors: mralaminahamed
Tags: elementor, settings, import, global styles, kit
Requires at least: 5.9
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import Elementor global settings from legacy v0.4 and Elementor v4 JSON formats. Auto-detects format and applies colors, typography, and experiments.

== Description ==

Upload a `global.json` (legacy Elementor v0.4 format) or `site-settings.json` (Elementor v4 format) via the WordPress Media Library. The plugin auto-detects the format and applies settings to the active Elementor kit.

**Supported formats:**

* **Legacy v0.4** — `global.json` exported from Elementor before version 4.x. Contains `page_settings` with colors, typography, layout, and button styles.
* **Elementor v4** — `site-settings.json` exported from Elementor 4.x. Contains `settings`, `experiments`, and `theme` data.

**What gets imported:**

* System colors and custom colors
* System typography and custom typography
* Body, heading, link, and button styles
* Container and viewport settings
* Elementor feature flags / experiments (v4, opt-in)

**After import:** Go to Elementor → Tools → Regenerate CSS & Data.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the Plugins menu in WordPress.
3. Go to **Elementor → Global Settings Updater**.
4. Select your JSON file from the Media Library and click **Apply & Import Settings**.

== Frequently Asked Questions ==

= Why can't I see JSON files in the Media Library? =

The plugin registers `application/json` as an allowed upload type automatically. If your file doesn't appear, try uploading it directly via the media uploader button on the plugin page.

= Does this overwrite my existing settings? =

Yes. The imported settings are applied directly to the active Elementor kit, replacing existing values. Back up your current kit before importing.

= What is the "Import experiments" option? =

Elementor v4 exports include feature flag states (e.g. Atomic Widgets, Global Classes). Checking this option applies those states to your current installation. Legacy imports ignore this option.

== Changelog ==

= 2.0.0 =
* Added Elementor v4 `site-settings.json` format support
* Added experiments / feature flags import (v4)
* Fixed: admin notice never displayed (Post-Redirect-Get pattern via transient)
* Fixed: inline script attached to style handle — now a proper enqueued JS file
* Added server-side MIME validation on attachment
* Added `upload_mimes` filter to allow JSON uploads in WP media library
* Extracted HTML to `templates/updater.php` and `templates/notice.php`
* Added `wp_localize_script` for i18n strings in JS
* Added WP Media frame reuse and pre-select on reopen
* Added `composer release` script for ZIP builds

= 1.6 =
* Fixed WP Media Uploader compatibility on submenu pages
* Auto-detects official Elementor v0.4 global settings format

== Upgrade Notice ==

= 2.0.0 =
Adds full Elementor v4 format support. No database changes. Safe to upgrade.
