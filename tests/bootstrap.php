<?php
/**
 * Bootstraps the PHPUnit integration tests.
 *
 * @package RestrictedSiteAccess
 */

define( 'TESTS_REPO_ROOT_DIR', dirname( __DIR__ ) );

/*
 * Network-activated mode. Only the WP_TESTS_MULTISITE constant turns it on;
 * the WP_MULTISITE=1 environment variable (npm run test:php:multisite) runs
 * the suite on multisite with the plugin activated per site.
 */
if ( defined( 'WP_TESTS_MULTISITE' ) ) {
	// Tells the plugin it is network active.
	define( 'RSA_IS_NETWORK', true );
	define( 'WP_NETWORK_ADMIN', true );
	if ( ! defined( 'RSA_IP_WHITELIST' ) ) {
		define( 'RSA_IP_WHITELIST', 123 ); // For a test in the get_config_ips() function.
	}
}

// Composer autoload also loads the PHPUnit Polyfills the WP test library requires.
if ( file_exists( TESTS_REPO_ROOT_DIR . '/vendor/autoload.php' ) ) {
	require_once TESTS_REPO_ROOT_DIR . '/vendor/autoload.php';
}

// Find the WordPress test library. wp-env sets WP_TESTS_DIR; wp-phpunit sets WP_PHPUNIT__DIR.
if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$_test_root = getenv( 'WP_TESTS_DIR' );
} elseif ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$_test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( false !== getenv( 'WP_PHPUNIT__DIR' ) ) {
	$_test_root = getenv( 'WP_PHPUNIT__DIR' );
} else {
	$_test_root = '/tmp/wordpress-tests-lib';
}

// Gives access to tests_add_filter().
require_once $_test_root . '/includes/functions.php';

// Load the plugin as a must-use plugin so it is active for every test.
tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require TESTS_REPO_ROOT_DIR . '/restricted_site_access.php';
		define( 'RSA_TEST_PLUGIN_BASENAME', plugin_basename( TESTS_REPO_ROOT_DIR . '/restricted_site_access.php' ) );
		define( 'PHP_UNIT_TESTS_ENV', true );
	}
);

// Start up the WP testing environment.
require $_test_root . '/includes/bootstrap.php';

// Helpers shared by the integration tests.
require_once __DIR__ . '/Integration/fixtures/private-access.php';
