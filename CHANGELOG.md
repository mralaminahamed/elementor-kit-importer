# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] - 2026-05-30

### Fixed
- Strip non-portable WooCommerce page IDs (`woocommerce_*_page_id`) on v4 import — prevented broken cart/checkout/account links from source-site post IDs
- Undefined-index notice in legacy importer when a template file lacks a `type` key
- `Legacy_Adapter::is_compatibility_needed()` now accepts the `$meta` argument to match Elementor's `Base_Adapter` contract
- Version string drift — plugin header now `2.0.0` to match readme and constant

### Added
- `Requires Plugins: elementor`, `Requires PHP`, and `Requires at least` plugin headers

## [2.0.0] - 2026-04-17

### Added
- Elementor v4 `site-settings.json` format support
- Experiments / feature flags import for v4 format (opt-in checkbox)
- Server-side MIME validation via `get_post_mime_type()` before processing
- `upload_mimes` filter — registers `application/json` so WP media library accepts JSON files
- `wp_localize_script` — passes i18n strings to JS (`title`, `button`, `selected`)
- WP Media frame reuse — single frame instance, no duplicate modals on repeated clicks
- Pre-select previously chosen attachment when media frame reopens
- `templates/updater.php` — extracted admin page HTML
- `templates/notice.php` — extracted admin notice HTML
- `assets/updater.js` — extracted JS from inline to dedicated file
- `load_template()` private helper with scoped `extract()` for passing vars to templates
- `composer release` script — produces clean distributable ZIP via rsync + zip
- `.distignore` — controls which files are excluded from release ZIP
- `index.php` silence files in root, `assets/`, and `templates/`
- `LICENSE.txt`, `readme.txt`, `CHANGELOG.md`, `.gitignore`, `.editorconfig`

### Fixed
- Admin notice never displayed — processing moved to `admin_init` hook using Post-Redirect-Get with transient storage
- Inline script was attached to a style handle (`wp-mediaelement`) — now a proper `wp_register_script` / `wp_enqueue_script` flow
- Redundant `wp_enqueue_style` calls removed — `wp_enqueue_media()` covers all dependencies
- `library: { type: 'application/json' }` now works correctly after adding `upload_mimes` filter

### Changed
- Non-portable site identity fields (`site_name`, `site_description`, `site_logo`, `site_favicon`) stripped before applying v4 settings
- Notice display scoped to plugin page only via `get_current_screen()` check
- JS dependencies reduced to `['jquery', 'media-upload']` — no unnecessary handles

## [1.6.0] - 2025-xx-xx

### Added
- Auto-detection of official Elementor v0.4 `global.json` format via `version` key

### Fixed
- WP Media Uploader compatibility on submenu pages — switched from `thickbox` to `wp.media` frame
