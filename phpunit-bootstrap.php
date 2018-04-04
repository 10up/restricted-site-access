<?php
/**
 * Bootstrap the tests, mocking some functions that are expected.
 */

/*function _x( $a ) { return $a; }
function __( $a ) { return $a; }
function add_action() { return true; }
function plugin_basename() { return true; }
function get_site_option() { return array(); }
function is_multisite() { return false; }
function register_uninstall_hook() { return false; }
function get_option() {
	return array(
		'page' => 1,
	);
}
function is_admin() { return false; }
function is_user_logged_in() { return false; }
function do_action() { return true; }
function home_url() { return 'https://wordpress.local'; }
function wp_login_url() { return 'https://wordpress.local/wp-admin/'; }
function wp_redirect( $url, $status ) {};
function apply_filters( $name, $value ) {
	switch ( $name ) {
		case 'restricted_site_access_approach':
			return 4;
		case 'restricted_site_access_is_restricted':
			return true;
		default:
			return $value;
	}
}
function wp_die() {};
function get_bloginfo() {};
function get_post() {};
/*function get_permalink( $post_id ) {
	if ( 1 === $post_id ) {
		return 'https://wordpress.local/home';
	}
	return '';
};
*/
require_once 'vendor/autoload.php';
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();


$mocked_functions = array(
	array(
		'name'   => 'plugin_basename',
		'return' => true,
		'times'  => 2,
	),
	array(
		'name'   => 'is_multisite',
		'return' => false,
		'times'  => 1,
	),
	array(
		'name'   => 'register_uninstall_hook',
		'return' => false,
	),
	array(
		'name'   => 'get_post',
		'return' => false,
	),
	array(
		'name'   => 'get_permalink',
		'return' => false,
	),
	array(
		'name'   => 'wp_redirect',
		'return' => false,
	),
	array(
		'name'   => 'is_admin',
		'return' => false,
		'times'  => 0,
	),
	array(
		'name'   => 'register_uninstall_hook',
		'return' => false,
		'times'  => '0,'
	),
	array(
		'name'   => 'get_site_option',
		'return' => false,
		'times'  => 1,
	),
	array(
		'name'   => 'is_user_logged_in',
		'return' => false,
	),

);

foreach ( $mocked_functions as $mocked_function ) {
	//echo json_encode( $mocked_function, JSON_PRETTY_PRINT );
	$args = isset( $mocked_function['return'] ) ?
		array(
			'return' => $mocked_function['return'],
			'times'  => isset( $mocked_function['times'] ) ? $mocked_function['times'] : 0,
		) :
		array(
			'return_arg' => 0,
			//'times'       => isset( $mocked_function['times'] ) ? $mocked_function['times'] : 1,
		);

	\WP_Mock::userFunction(
		$mocked_function['name'],
		$args
	);
}

require_once 'restricted_site_access.php';
