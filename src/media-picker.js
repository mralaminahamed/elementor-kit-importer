/**
 * JSON media-picker for the Elementor Kit Importer admin page.
 *
 * A testable, framework-free rewrite of the old jQuery implementation. The
 * WordPress media frame is injected so the wiring can be exercised under jsdom
 * without the real `wp.media` global.
 */

/**
 * Write the chosen attachment into the hidden field and the visible label.
 *
 * @param {ParentNode} root           Element (or document) containing the form controls.
 * @param {Object}     attachment     Attachment JSON ({ id, filename }).
 * @param {string}     selectedPrefix Localised "Selected: " prefix.
 * @return {{id: (number|string), filename: string}} The applied selection.
 */
export function applySelection( root, attachment, selectedPrefix ) {
	const input = root.querySelector( '#json_attachment_id' );
	const label = root.querySelector( '#selected_file' );

	if ( input ) {
		input.value = attachment.id;
	}

	if ( label ) {
		label.textContent = `${ selectedPrefix }${ attachment.filename }`;
	}

	return { id: attachment.id, filename: attachment.filename };
}

/**
 * Wire the "Select / Upload JSON File" button to a WordPress media frame.
 *
 * @param {ParentNode} root             Element (or document) containing the controls.
 * @param {Object}     deps             Dependencies.
 * @param {Function}   deps.media       Media frame factory (e.g. window.wp.media).
 * @param {Object}     deps.strings     Localised strings ({ title, button, selected }).
 * @return {{open: Function}|null} A small handle, or null when wiring is not possible.
 */
export function initMediaPicker( root, { media, strings } ) {
	const button = root.querySelector( '#upload_json_btn' );

	if ( ! button || typeof media !== 'function' ) {
		return null;
	}

	let frame;

	button.addEventListener( 'click', ( event ) => {
		event.preventDefault();

		// Reuse the frame so repeated clicks do not spawn duplicate modals.
		if ( frame ) {
			frame.open();
			return;
		}

		frame = media( {
			title: strings.title,
			button: { text: strings.button },
			multiple: false,
			library: { type: 'application/json' },
		} );

		frame.on( 'select', () => {
			const attachment = frame.state().get( 'selection' ).first().toJSON();
			applySelection( root, attachment, strings.selected );
		} );

		frame.open();
	} );

	return { open: () => button.click() };
}
