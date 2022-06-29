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
};
