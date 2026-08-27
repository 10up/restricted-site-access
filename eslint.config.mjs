import wordpress from '@wordpress/eslint-plugin';

export default [
	{
		ignores: [ 'tests/bin/set-wp-config.js' ],
	},
	...wordpress.configs[ 'recommended-with-formatting' ],
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