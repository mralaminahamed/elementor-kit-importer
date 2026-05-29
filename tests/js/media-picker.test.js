/**
 * Tests for the JSON media-picker (src/media-picker.js).
 *
 * A fake media frame stands in for wp.media so the select → field-update wiring
 * is exercised under jsdom without the real WordPress media library.
 */

const { applySelection, initMediaPicker } = require( '../../src/media-picker.js' );

/**
 * Build the admin form controls and append them to the document body.
 *
 * @return {HTMLElement} The wrapper element.
 */
function buildForm() {
	const wrap = document.createElement( 'div' );
	wrap.innerHTML = `
		<input type="hidden" id="json_attachment_id" value="">
		<button type="button" id="upload_json_btn"></button>
		<p id="selected_file"></p>
	`;
	document.body.appendChild( wrap );
	return wrap;
}

/**
 * Create a fake wp.media frame factory whose selection resolves to `attachment`.
 *
 * @param {Object} attachment Attachment JSON to return from the selection.
 * @return {{factory: Function, calls: Array, fire: Function}} Test handle.
 */
function fakeMedia( attachment ) {
	const handlers = {};
	const calls = [];
	let opened = 0;

	const frame = {
		on( event, cb ) {
			handlers[ event ] = cb;
		},
		open() {
			opened++;
		},
		state() {
			return {
				get() {
					return { first: () => ( { toJSON: () => attachment } ) };
				},
			};
		},
		get openedCount() {
			return opened;
		},
	};

	const factory = ( args ) => {
		calls.push( args );
		return frame;
	};

	return {
		factory,
		calls,
		frame,
		fireSelect: () => handlers.select && handlers.select(),
	};
}

const STRINGS = { title: 'Pick a file', button: 'Use this file', selected: 'Selected: ' };

beforeEach( () => {
	document.body.innerHTML = '';
} );

describe( 'applySelection', () => {
	test( 'writes the attachment id and filename into the controls', () => {
		const wrap = buildForm();
		applySelection( wrap, { id: 42, filename: 'site-settings.json' }, 'Selected: ' );

		expect( wrap.querySelector( '#json_attachment_id' ).value ).toBe( '42' );
		expect( wrap.querySelector( '#selected_file' ).textContent ).toBe( 'Selected: site-settings.json' );
	} );
} );

describe( 'initMediaPicker', () => {
	test( 'returns null when the button is absent', () => {
		const handle = initMediaPicker( document, { media: () => ( {} ), strings: STRINGS } );
		expect( handle ).toBeNull();
	} );

	test( 'returns null when no media factory is provided', () => {
		buildForm();
		expect( initMediaPicker( document, { media: undefined, strings: STRINGS } ) ).toBeNull();
	} );

	test( 'opens a media frame configured for JSON on click', () => {
		buildForm();
		const media = fakeMedia( { id: 7, filename: 'global.json' } );

		initMediaPicker( document, { media: media.factory, strings: STRINGS } );
		document.querySelector( '#upload_json_btn' ).click();

		expect( media.calls ).toHaveLength( 1 );
		expect( media.calls[ 0 ].library.type ).toBe( 'application/json' );
		expect( media.calls[ 0 ].multiple ).toBe( false );
		expect( media.frame.openedCount ).toBe( 1 );
	} );

	test( 'select handler populates the form fields', () => {
		buildForm();
		const media = fakeMedia( { id: 7, filename: 'global.json' } );

		initMediaPicker( document, { media: media.factory, strings: STRINGS } );
		document.querySelector( '#upload_json_btn' ).click();
		media.fireSelect();

		expect( document.querySelector( '#json_attachment_id' ).value ).toBe( '7' );
		expect( document.querySelector( '#selected_file' ).textContent ).toBe( 'Selected: global.json' );
	} );

	test( 'reuses the same frame on repeated clicks', () => {
		buildForm();
		const media = fakeMedia( { id: 7, filename: 'global.json' } );

		initMediaPicker( document, { media: media.factory, strings: STRINGS } );
		const btn = document.querySelector( '#upload_json_btn' );
		btn.click();
		btn.click();

		expect( media.calls ).toHaveLength( 1 ); // factory called once
		expect( media.frame.openedCount ).toBe( 2 ); // but opened twice
	} );
} );
