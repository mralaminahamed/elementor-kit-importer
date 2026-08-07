/**
 * The brand palette every listing asset is painted with.
 *
 * One module, imported by the icon rasteriser, the banner renderer and the
 * screenshot frames alike. Three copies of the same hex is exactly how an icon
 * and a banner drift apart.
 *
 * Every value is a Tailwind pink stop, so the ramp is already
 * contrast-tested against itself rather than hand-picked to be close to
 * something. Pink sits next to Elementor's own magenta without being it: near enough to belong in that ecosystem, far enough not to look official.
 *
 * `.wordpress-org/icon.svg` repeats these values because SVG cannot import;
 * its header names this file as canonical.
 */
export const BRAND = {
	/** What the assets call the plugin. One place, so a rename is one edit. */
	name: 'Kit Importer',
	nameAccent: 'for Elementor',
	tagline: 'Import a kit of either generation',

	/** Ground, darkest first. */
	ink: '#500724',
	inkMid: '#831843',
	inkLift: '#9d174d',

	/** Accent, deep to bright. */
	royal: '#db2777',
	royalLight: '#ec4899',
	sky: '#f472b6',

	/** The wordmark's gradient, and anything that must stay legible on the ground. */
	accent: '#f9a8d4',

	/** Shadow colour under white cards, so shadows read as the same hue. */
	shadow: '80, 7, 36',

	/** The glyph fill, top to bottom. */
	glyphTop: '#ffffff',
	glyphMid: '#fdf2f8',
	glyphBase: '#fbcfe8',
} as const;

/**
 * The field, as stacked CSS backgrounds — glow, specular, ground, in the order
 * CSS paints them. `angle` tilts the ground ramp.
 *
 * @param angle Ground ramp angle, in degrees.
 */
export function field( angle = 135 ): string {
	return [
		'radial-gradient(70% 100% at 78% 104%, rgba(219, 39, 119, .38) 0%, rgba(219, 39, 119, 0) 64%)',
		'linear-gradient(to bottom, rgba(255, 255, 255, .10) 0%, rgba(255, 255, 255, 0) 34%)',
		`linear-gradient(${ angle }deg, ${ BRAND.ink } 0%, ${ BRAND.inkMid } 42%, ${ BRAND.inkLift } 100%)`,
	].join( ', ' );
}

/** Alias, so the banner and the frames can each call it by the name that fits. */
export const glassField = field;

/**
 * The mark, without its squircle — a tile inside a branded field would be a
 * panel on a panel.
 *
 * @param size Rendered size in pixels; the viewBox is always the 256 master.
 */
export function markSvg( size: number ): string {
	return `<svg width="${ size }" height="${ size }" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <defs>
    <linearGradient id="mark-glyph" x1="50%" y1="0%" x2="50%" y2="100%">
      <stop offset="0" stop-color="${ BRAND.glyphTop }"/>
      <stop offset="0.6" stop-color="${ BRAND.glyphMid }"/>
      <stop offset="1" stop-color="${ BRAND.glyphBase }"/>
    </linearGradient>
  </defs>
	<rect x="112" y="40" width="32" height="82" rx="10" fill="url(#mark-glyph)"/>
	<path d="M88 108 L168 108 L128 166 Z" fill="url(#mark-glyph)"/>
	<path d="M46 150 V190 a22 22 0 0 0 22 22 H188 a22 22 0 0 0 22-22 V150" fill="none" stroke="url(#mark-glyph)" stroke-width="20" stroke-linecap="round"/>
</svg>`;
}
