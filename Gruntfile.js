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
					'assets/js/settings.min.js': ['assets/js/src/settings.js'],
					'assets/js/admin.min.js': ['assets/js/src/admin.js'],
				}
			}
		},

		watch: {
			files: [
				'assets/js/src/*'
			],
			tasks: ['uglify:js']
		}

	});

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'i18n', ['makepot'] );
	grunt.registerTask( 'default', ['uglify:js'] );
};
