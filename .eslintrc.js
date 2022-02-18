
/**
 * Internal dependencies
 */

module.exports = {
	extends: 'plugin:@wordpress/eslint-plugin/recommended-with-formatting',
	globals: {
		window: 'readonly',
		$: 'readonly',
		jQuery: 'readonly',
		rsaAdmin: 'readonly',
		rsaSettings: 'readonly',
		ajaxurl: 'readonly',
	},
};
