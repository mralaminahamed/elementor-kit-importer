# Elementor Kit Importer

**Version 2.0.2** · Requires Elementor · PHP 7.4+ · WordPress 5.9+

Import Elementor global settings from **legacy v0.4** (`global.json`) and **Elementor v4** (`site-settings.json`) formats into your active Elementor kit.

## Features

- Auto-detects legacy v0.4 and Elementor v4 export formats
- Imports system/custom colors and typography
- Imports body, heading, link, button, container, and viewport settings
- Imports Elementor experiments / feature flags (v4, opt-in)
- Uses WordPress Media Library for file selection
- Post-Redirect-Get pattern — no stale notice bug
- Server-side MIME validation on every import

## Supported Formats

| Format | File | Key |
|--------|------|-----|
| Legacy v0.4 | `global.json` | `page_settings` |
| Elementor v4 | `site-settings.json` | `settings` + `experiments` |

## Installation

```bash
composer release
# Upload release/elementor-kit-importer.zip via WP Admin → Plugins → Add New
```

Or copy the plugin folder to `wp-content/plugins/` and activate.

## Usage

1. Go to **Elementor → Kit Importer**
2. Click **Select / Upload JSON File** and choose your export file
3. Optionally check **Import experiments** (v4 only)
4. Click **Apply & Import Settings**
5. Go to **Elementor → Tools → Regenerate CSS & Data**

## Development

```bash
# PHP dependencies (PHPUnit, Brain Monkey, Mockery, PHPCS, PHPStan)
composer install

# JS dependencies + build (wp-scripts / webpack)
yarn install
yarn build          # src/ → build/

# Tests
composer test:unit          # PHPUnit unit suite (Brain Monkey, no DB)
composer test:integration   # PHPUnit integration suite (needs a MySQL test DB)
yarn test:js                # Jest (jsdom)

# Static analysis & coding standards
composer lint               # PHPCS (WordPress standard)
composer analyse            # PHPStan level 5

# Build release ZIP
composer release            # → release/elementor-kit-importer.zip
```

Integration tests use `wp-phpunit`. Point them at a throwaway database via
`WP_DB_NAME` / `WP_DB_USER` / `WP_DB_PASS` / `WP_DB_HOST` (defaults:
`elementor_kit_importer_tests`, `root`, `password`, `localhost`).

## Architecture

All classes live under the `Elementor_Kit_Importer` namespace, autoloaded by
Composer (classmap):

| Namespace | Responsibility |
|-----------|----------------|
| `Core\Plugin` | Bootstrap, admin page, form handling |
| `Importers\Import_Factory` | Format detection → importer selection |
| `Importers\Legacy_Importer` / `V4_Importer` | Per-format import logic |
| `Kit\Kit_Manager` | Apply settings to the active Elementor kit |
| `Compat\Legacy_Adapter` | Legacy Elementor import-export shim |

## File Structure

```
elementor-kit-importer/
├── elementor-kit-importer.php      # Main plugin file (constants + bootstrap)
├── includes/
│   ├── Core/class-plugin.php           # Core\Plugin
│   ├── Importers/                      # interface + factory + importers
│   ├── Kit/class-kit-manager.php       # Kit\Kit_Manager
│   └── Compat/class-legacy-adapter.php # Compat\Legacy_Adapter
├── src/                            # JS source (wp-scripts entry)
│   ├── index.js
│   └── media-picker.js
├── build/                          # Compiled JS (generated)
├── templates/                      # Admin page + notice templates
├── tests/
│   ├── php/Unit/                       # PHPUnit unit suite
│   ├── php/Integration/                # PHPUnit integration suite
│   └── js/                             # Jest suite
├── demos/                          # Sample legacy + v4 export files
├── composer.json
└── package.json
```

## Changelog

### 2.0.1

- **Fixed:** strip non-portable WooCommerce page IDs (`woocommerce_*_page_id`) on v4 import
- **Fixed:** PHP notice in the legacy importer when a template file lacks a `type` key
- **Fixed:** `Legacy_Adapter::is_compatibility_needed()` matches Elementor's `Base_Adapter` contract
- **Changed:** all classes namespaced under `Elementor_Kit_Importer\*` with Composer autoloading
- **Changed:** admin script built with `@wordpress/scripts` (webpack)
- **Added:** PHPUnit (unit + integration) + Jest suites, PHPCS, PHPStan; dependency headers

Full history in [CHANGELOG.md](CHANGELOG.md).

## Author

**Al Amin Ahamed** — [alaminahamed.com](https://alaminahamed.com)

## License

GPL-2.0-or-later — see [LICENSE.txt](LICENSE)
