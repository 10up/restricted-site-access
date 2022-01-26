<?php
define( 'WP_PLUGIN_DIR', dirname( dirname( dirname( __FILE__ ) ) ) );

if ( defined( 'WP_TESTS_MULTISITE' ) ) {
	// Tells the plugin it is network active.
	define( 'RSA_IS_NETWORK', true );
	define( 'WP_NETWORK_ADMIN', true );

	if ( ! defined( 'RSA_IP_WHITELIST' ) ) {
		define( 'RSA_IP_WHITELIST', 123 ); // For a test in the get_config_ips() function.
	}
}

class Restricted_Site_Access_Tests_Bootstrap {

	/**
	 * Setup the unit test environment
	 *
	 * @return void
	 */
	public function bootstrap() {
		$_tests_dir = getenv( 'WP_TESTS_DIR' );

		if ( ! $_tests_dir ) {
			$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
		}
		
		if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
			echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
			exit( 1 );
		}
		
		// Give access to tests_add_filter() function.
		require_once $_tests_dir . '/includes/functions.php';
		
		/**
		 * Manually load the plugin being tested.
		 */
		function _manually_load_plugin() {
			require WP_PLUGIN_DIR . '/restricted_site_access.php';
			define( 'RSA_TEST_PLUGIN_BASENAME', plugin_basename( 'restricted_site_access.php' ) );
			define( 'PHP_UNIT_TESTS_ENV', true );
		}
		tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
		
		// Start up the WP testing environment.
		require $_tests_dir . '/includes/bootstrap.php';
	}

	/**
	 * Manually load the plugin being tested.
	 */
	public function manually_load_plugin() {
		require WP_PLUGIN_DIR . '/restricted_site_access.php';
		define( 'RSA_TEST_PLUGIN_BASENAME', plugin_basename( 'restricted_site_access.php' ) );
		define( 'PHP_UNIT_TESTS_ENV', true );
	}
}

$rsa_tests = new Restricted_Site_Access_Tests_Bootstrap();
$rsa_tests->bootstrap();
