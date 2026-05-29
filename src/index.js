/**
 * Admin entry point for the Elementor Kit Importer.
 *
 * Boots the JSON media-picker once the DOM is ready, reading the localised
 * strings and the `wp.media` factory from the global scope.
 */

import { initMediaPicker } from './media-picker';

/**
 * Initialise the media-picker against the current document.
 *
 * @return {void}
 */
function boot() {
	const wp = window.wp || {};
	const strings = window.elementorSettingsUpdater || {
		title: '',
		button: '',
		selected: '',
	};

	initMediaPicker( document, { media: wp.media, strings } );
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
