/**
 * ESLint flat config.
 *
 * Matches the rules from the old .eslintrc.js. `@wordpress/scripts` v37 uses
 * ESLint v9 and no longer reads .eslintrc files.
 */
const wpPlugin = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [
			'**/build/**',
			'**/node_modules/**',
			'**/vendor/**',
			'tests/bin/set-wp-config.js',
		],
	},
	...wpPlugin.configs[ 'recommended-with-formatting' ],
	{
		languageOptions: {
			globals: {
				window: 'readonly',
				$: 'readonly',
				jQuery: 'readonly',
				rsaAdmin: 'readonly',
				rsaSettings: 'readonly',
				ajaxurl: 'readonly',
			},
		},
		rules: {
			'import/no-unresolved': 'off',
			// ESLint v9 also checks function arguments. Keep the old behaviour.
			'comma-dangle': [
				'error',
				{
					arrays: 'always-multiline',
					objects: 'always-multiline',
					imports: 'always-multiline',
					exports: 'always-multiline',
					functions: 'ignore',
				},
			],
		},
	},
];
