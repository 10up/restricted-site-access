module.exports = function(grunt) {

	grunt.initConfig({

		makepot: {
			target: {
				options: {
					domainPath: 'localization/',
					mainFile: 'restricted_site_access.php',
					type: 'wp-plugin',
					updateTimestamp: false,
					updatePoFiles: true
				}
			}
		},

		uglify: {
			js: {
				files: {
					'assets/js/restricted-site-access.min.js': ['assets/js/src/restricted-site-access.js'],
				}
			}
		}

	});
	
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );

	grunt.registerTask( 'i18n', ['makepot'] );
	grunt.registerTask( 'default', ['uglify:js'] );
};
