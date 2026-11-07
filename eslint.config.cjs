/**
 * ESLint flat config.
 *
 * ESLint 10 (shipped with `@wordpress/scripts`) no longer reads `.eslintrc`
 * or `.eslintignore`. The wp-scripts default config is reused rather than
 * reaching for `@wordpress/eslint-plugin` directly, so the two can never
 * drift out of step.
 */

/**
 * External dependencies
 */
const globals = require( 'globals' );

/**
 * Internal dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	{
		// `dist` holds generated bundles; the wp-scripts default only knows
		// about the conventional `build` directory.
		ignores: [
			'dist/**',
			'build/**',
			'node_modules/**',
			'vendor/**',
			'languages/**',
		],
	},

	...defaultConfig,

	{
		languageOptions: {
			globals: {
				...globals.browser,
			},
		},
	},
];
