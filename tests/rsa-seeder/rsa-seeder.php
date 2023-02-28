<?php
/**
 * Plugin Name: RSA seeder
 *
 * @package RSA Seeder
 */

add_filter( 'restricted_site_access_is_restricted', 'my_rsa_feed_override', 10, 2 );

function my_rsa_feed_override( $is_restricted, $wp ) {
	$unrestrcited_routes = array(
		'wp-json/rsa/v1/seed/activation-deactivation',
		'wp-json/rsa/v1/seed/add-invalid-addresses',
		'wp-json/rsa/v1/seed/add-valid-addresses',
		'wp-json/rsa/v1/seed/allow-unrestricted-users',
		'wp-json/rsa/v1/seed/allow-unrestricted-users/show-simple-message',
		'wp-json/rsa/v1/seed/allow-unrestricted-users/redirect-to-web-address',
		'wp-json/rsa/v1/seed/allow-unrestricted-users/send-to-login-screen',
		'wp-json/rsa/v1/seed/restrict-users/send-to-login-screen',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-web-address/with-301',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-web-address/with-302',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-web-address/with-307',
		'wp-json/rsa/v1/seed/restrict-users/show-simple-message',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-external-url',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-external-url-with-path',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-external-url-to-new-path',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-external-with-path-to-aurl-to-new-path',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-internal-url',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-internal-url-with-path-to-a-new-path',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-path',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-path-and-redirect-to-same-path',
		'wp-json/rsa/v1/seed/restrict-users/redirect-to-path-to-a-new-path',
	);

	if ( in_array( $wp->request, $unrestrcited_routes, true ) ) {
		$is_restricted = false;
	}

	return $is_restricted;
}

add_action(
	'rest_api_init',
	function () {
		$mock_rsa_mode    = 'enforce';
		$mock_blog_public = '2';

		$mock_rsa_options = array(
			'approach'      => 1,
			'message'       => 'Access to this site is restricted.',
			'redirect_path' => 0,
			'head_code'     => 302,
			'redirect_url'  => '',
			'page'          => 0,
			'allowed'       => array(),
			'comment'       => array(),
		);

		register_rest_route(
			'rsa/v1',
			'seed/activation-deactivation',
			array(
				'methods'  => 'GET',
				'callback' => function() {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
					deactivate_plugins( 'restricted-site-access/restricted_site_access.php', '', true );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/add-invalid-addresses',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/add-valid-addresses',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/allow-unrestricted-users',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/allow-unrestricted-users/show-simple-message',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 3;
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/allow-unrestricted-users/redirect-to-web-address',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/allow-unrestricted-users/send-to-login-screen',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 1;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/send-to-login-screen',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 1;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-web-address/with-301',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 301;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-web-address/with-302',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 302;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-web-address/with-307',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-external-url',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'https://www.google.com/';
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-external-url-with-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'https://www.google.com/main';
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-external-url-to-new-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'https://www.google.com/';
					$mock_rsa_options['redirect_path'] = 1;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-external-with-path-to-aurl-to-new-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'https://www.google.com/cool';
					$mock_rsa_options['redirect_path'] = 1;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-internal-url',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['redirect_path'] = 0;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-internal-url-with-path-to-a-new-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = 'http://localhost:8889/page-to-redirect/';
					$mock_rsa_options['redirect_path'] = 1;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = '/page-to-redirect';
					$mock_rsa_options['redirect_path'] = 0;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-path-and-redirect-to-same-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = '/page-to-redirect';
					$mock_rsa_options['redirect_path'] = 1;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/redirect-to-path-to-a-new-path',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 2;
					$mock_rsa_options['head_code'] = 307;
					$mock_rsa_options['redirect_url'] = '/page-to-redirect';
					$mock_rsa_options['redirect_path'] = 1;
					$mock_rsa_options['allowed'] = array();

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);

		register_rest_route(
			'rsa/v1',
			'seed/restrict-users/show-simple-message',
			array(
				'methods'  => 'GET',
				'callback' => function() use ( $mock_rsa_mode, $mock_blog_public, $mock_rsa_options ) {
					$mock_rsa_options['approach'] = 3;
					$mock_rsa_options['allowed'] = array(
						'172.13.24.5',
					);

					update_site_option( 'rsa_mode', $mock_rsa_mode );
					update_site_option( 'blog_public', $mock_blog_public );
					update_site_option( 'rsa_options', $mock_rsa_options );

					return true;
				},
			)
		);
	}
);
