<?php

class Restricted_Site_Access_Tests_Bootstrap {

	protected $plugin_root = '';

	/**
	 * Setup the unit test environment
	 *
	 * @return void
	 */
	public function bootstrap() {

		// Run this in your shell to point to whever you cloned WP.
		// git clone git@github.com:WordPress/wordpress-develop.git
		// Example: export WP_DEVELOP_DIR="/Users/petenelson/projects/wordpress/wordpress-develop/"
		$wp_develop_dir = getenv( 'WP_DEVELOP_DIR' );

		if ( empty( $wp_develop_dir ) ) {
			throw new Exception(
				'ERROR' . PHP_EOL . PHP_EOL .
				'You must define the WP_DEVELOP_DIR environment variable.' . PHP_EOL
			);
		}

		// Load the Composer autoloader.
		define( 'WP_PLUGIN_DIR', dirname( dirname( dirname( __FILE__ ) ) ) );
		$this->plugin_root = WP_PLUGIN_DIR;

		if ( ! file_exists( $this->plugin_root . '/vendor/autoload.php' ) ) {
			throw new Exception(
				'ERROR' . PHP_EOL . PHP_EOL .
				'You must use Composer to install the test suite\'s dependencies.' . PHP_EOL
			);
		}
		$autoloader = require_once $this->plugin_root . '/vendor/autoload.php';

		// Give access to tests_add_filter() function.
		require_once $wp_develop_dir . '/tests/phpunit/includes/functions.php';

		if ( defined( 'WP_TESTS_MULTISITE' ) ) {
			// Tells the plugin it is network active.
			define( 'RSA_IS_NETWORK', true );
			define( 'WP_NETWORK_ADMIN', true );

			if ( ! defined( 'RSA_IP_WHITELIST' ) ) {
				define( 'RSA_IP_WHITELIST', 123 ); // For a test in the get_config_ips() function.
			}
		}

		tests_add_filter( 'muplugins_loaded', [ $this, 'manually_load_plugin' ] );

		// Start up the WP testing environment.
		require $wp_develop_dir . '/tests/phpunit/includes/bootstrap.php';
	}

	/**
	 * Manually load the plugin being tested.
	 */
	public function manually_load_plugin() {
		require $this->plugin_root . '/restricted-site-access.php';
		define( 'RSA_TEST_PLUGIN_BASENAME', plugin_basename( 'restricted-site-access.php' ) );
	}
}

$rsa_tests = new Restricted_Site_Access_Tests_Bootstrap();
$rsa_tests->bootstrap();
