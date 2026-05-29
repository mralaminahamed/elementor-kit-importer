// Extends the @wordpress/scripts unit-test preset (jsdom + Babel for ESM/JSX)
// and points the matcher at this plugin's test suite.
const base = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...base,
	testMatch: [ '**/tests/js/**/*.test.[jt]s?(x)' ],
	clearMocks: true,
};
