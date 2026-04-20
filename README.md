# Elementor Kit Importer

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
# Install dependencies
composer install

# Build release ZIP
composer release
# → release/elementor-kit-importer.zip
```

## File Structure

```
elementor-kit-importer/
├── elementor-kit-importer.php  # Main plugin file
├── assets/
│   └── updater.js                  # Media uploader JS
├── templates/
│   ├── updater.php                 # Admin page template
│   └── notice.php                  # Admin notice template
├── demos/
│   ├── legacy/global.json          # Legacy v0.4 sample
│   └── v4/site-settings.json       # Elementor v4 sample
└── composer.json
```

## Author

**Al Amin Ahamed** — [alaminahamed.com](https://alaminahamed.com)

## License

GPL-2.0-or-later — see [LICENSE.txt](LICENSE.txt)
