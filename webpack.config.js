const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		admin: './assets/js/src/admin.js',
		settings: './assets/js/src/settings.js',
	},
	output: {
		path: path.resolve( __dirname, 'assets/js/build' ),
		filename: '[name].min.js',
	},
	plugins: [
		...defaultConfig.plugins,
		new DependencyExtractionWebpackPlugin( {
			requestToExternal( request ) {
				if ( 'jquery-effects-shake' === request ) {
					return 'jquery-effects-shake';
				}

				if ( 'jquery-ui-dialog' === request ) {
					return 'jquery-ui-dialog';
				}
			},
		} ),
	],
};
