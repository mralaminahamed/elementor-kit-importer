# Elementor Kit Importer — Brand & Asset System

Everything in `.wordpress-org/` is generated. `icon.svg` and
`resources/brand/banner.html` are the only files edited by hand — never touch
the PNGs directly, they are overwritten on every render.

## Asset inventory

| File | Dimensions | Purpose |
|---|---|---|
| `icon.svg` | vector | Canonical icon; the directory consumes this directly when present |
| `icon-256x256.png` · `icon-128x128.png` | 256 · 128 | Directory hero and grid |
| `icon-512x512.png` | 512 | Channels outside the directory |
| `banner-1544x500.png` · `banner-772x250.png` | — | Desktop and mobile directory banners |
| `banner-1024x512.png` | 1024×512 | Square-ish crop for other channels |
| `screenshot-*.png` | 1200×900 | Listing screenshots, once there are any |

## Palette

**It lives in `tests/assets/brand.ts`, and only there.** `icon.svg` repeats the
values because SVG cannot import, and its header says so.

| Token | Hex |
|---|---|
| `ink` | `#500724` |
| `inkMid` | `#831843` |
| `inkLift` | `#9d174d` |
| `royal` | `#db2777` |
| `royalLight` | `#ec4899` |
| `sky` | `#f472b6` |
| `accent` | `#f9a8d4` |
| `glyphMid` | `#fdf2f8` |
| `glyphBase` | `#fbcfe8` |

Tailwind pink. Pink sits next to Elementor's own magenta without being it: near enough to belong in that ecosystem, far enough not to look official.

## The mark

An arrow going down into a tray. Import, in the shape every operating system has used for it, which is worth more than anything cleverer.

## Regenerating

```bash
yarn install        # once
yarn shots:banners  # icon + banner PNGs; no site needed
```

Screenshots need a running site and a `shots` project; this plugin has the
banner half of the pipeline only, until there are screens worth capturing.
