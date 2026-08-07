<div align="center">

# Elementor Kit Importer — Developer Guide

**Imports a kit's global settings whether the file came from Elementor v0.4 or v4, by detecting the format rather than asking you which one you have.**

[![Version](https://img.shields.io/badge/version-2.0.2-2563eb.svg)](https://github.com/mralaminahamed/elementor-kit-importer)
[![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![Elementor](https://img.shields.io/badge/Elementor-required-92003B.svg?logo=elementor&logoColor=white)](https://elementor.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg?logo=php&logoColor=white)](https://php.net/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE)

</div>

## What it is

Elementor changed the shape of a kit's settings file between v0.4 and v4.
`global.json` became `site-settings.json`, and the settings moved from
`page_settings` to `settings`. A kit exported from an older site therefore does
not import into a newer one, and the person holding the file usually has no idea
which generation it belongs to — the extension is `.json` either way.

So this plugin does not ask. It reads the file, decides which format it is, and
runs the importer for that format. Both importers implement the same interface,
so the choosing is a factory returning one of two objects and nothing downstream
knows the difference.

There is a second, separate path: when Elementor's own import runs, the plugin
registers an adapter on `elementor/import-export/import-kit` so a legacy kit
imported *through Elementor's UI* is translated on the way through, rather than
having to come through this plugin at all.

## Features

- **Format auto-detection** — no dropdown, no guessing
- Imports **colours, typography and experiments** from either generation
- **Experiments are opt-in** per import, since turning experiments on is not something to do silently
- An **adapter for Elementor's own importer**, so legacy kits work through the native flow too
- JSON is temporarily allowed as an upload type through `upload_mimes`, which WordPress otherwise refuses
- Hard dependency on Elementor via the `Requires Plugins` header — WordPress blocks activation without it

## Requirements

- WordPress 5.9+
- Elementor
- PHP 7.4+

## Installation

```bash
git clone https://github.com/mralaminahamed/elementor-kit-importer.git
cd elementor-kit-importer
composer install
yarn install && yarn build
```

## Development

```bash
composer lint              # WordPress coding standards
composer lint:fix          # auto-fix what it can
composer analyse           # PHPStan
composer analyse-baseline  # regenerate the baseline
composer test              # both suites
composer test:unit
composer test:integration
composer i18n              # regenerate the POT file

yarn start                 # asset watch
yarn build                 # production assets
yarn test:js               # Jest
```

## Architecture

```
elementor-kit-importer.php            entry point and constants
includes/
├── Core/class-plugin.php             singleton; the admin page, the form, the hooks
├── Importers/
│   ├── interface-importer.php        detect() and import() — the whole contract
│   ├── class-import-factory.php      picks an importer from the decoded JSON
│   ├── class-legacy-importer.php     v0.4 — global.json
│   └── class-v4-importer.php         v4 — site-settings.json
├── Kit/class-kit-manager.php         applies settings to the active kit, then clears Elementor's cache
└── Compat/class-legacy-adapter.php   translates a legacy kit inside Elementor's own import
templates/notice.php
```

### The contract is two methods

```php
interface Importer {
    public static function detect( array $data ): bool;
    public function import( array $data, bool $import_experiments ): array;
}
```

`detect()` is static because the factory has to ask before it has an instance —
the question is about the data, not about the importer. Adding a third format
means a third class and one line in the factory; nothing else in the plugin
learns a new name.

### How detection actually works

| Importer | Test |
|---|---|
| Legacy | `version === '0.4'`, **or** a `page_settings` array is present |
| v4 | a `settings` array is present **and** `page_settings` is absent |

The factory tries legacy first, and the comment in the source says why: legacy is
the only format carrying an explicit version marker, so it can answer with
certainty. v4 is inferred from shape, which is a weaker signal and therefore goes
second. The `! isset( $data['page_settings'] )` clause is what stops a legacy file
matching both.

### Applying the settings

`Kit_Manager::apply_settings()` writes into the active kit and then calls
`clear_cache()`. Elementor caches compiled CSS per kit, so settings written
without clearing it produce a site that has the new colours in the database and
the old ones on screen.

### Data

No tables and no options of its own. Everything is written into Elementor's
active kit, which is where Elementor expects to find it — so uninstalling this
plugin leaves an imported kit exactly as it was.

## License

GPL-2.0-or-later. See [`LICENSE`](LICENSE).
