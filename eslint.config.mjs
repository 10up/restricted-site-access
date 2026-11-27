/**
 * ESLint flat config. Extends the `@wordpress/scripts` default config.
 */

/**
 * WordPress dependencies
 */
import defaultConfig from '@wordpress/scripts/config/eslint.config.cjs';

export default [
	{
		ignores: [
			'assets/js/build/**',
			'node_modules/**',
			'vendor/**',
			'tests/**',
		],
	},
	...defaultConfig,
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
		},
	},
];
