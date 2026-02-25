<?php
/**
 * PHPUnit bootstrap file
 */

if ( defined( 'WP_TESTS_MULTISITE' ) ) {
	// Tells the plugin it is network active.
	define( 'RSA_IS_NETWORK', true );
	define( 'WP_NETWORK_ADMIN', true );
	if ( ! defined( 'RSA_IP_WHITELIST' ) ) {
		define( 'RSA_IP_WHITELIST', 123 ); // For a test in the get_config_ips() function.
	}
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Activate the plugin.
tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__, 2 ) . '/restricted_site_access.php';
		define( 'RSA_TEST_PLUGIN_BASENAME', plugin_basename( dirname( __DIR__, 2 ) . '/restricted_site_access.php' ) );
		define( 'PHP_UNIT_TESTS_ENV', true );
	}
);

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
