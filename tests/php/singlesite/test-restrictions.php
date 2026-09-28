<?php

class Restricted_Site_Access_Test_Singlesite_Restrictions extends WP_UnitTestCase {

	use PrivateAccess;

	public function test_singlesite_restrict_access_not_restricted() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to not restricted.
		update_option( 'blog_public', 1 );

		// First, test the filter.
		add_filter( 'restricted_site_access_is_restricted', '__return_false' );

		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$this->assertEmpty( $rsa::restrict_access_check( $wp ) );

		remove_filter( 'restricted_site_access_is_restricted', '__return_false' );

		// Now test it without the filter.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$this->assertEmpty( $rsa::restrict_access_check( $wp ) );
	}

	public function test_singlesite_restrict_access_restricted_default() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		// Check the default results.
		$url = add_query_arg( 'redirect_to', rawurlencode( '/' ), home_url( 'wp-login.php' ) );

		$this->assertNotEmpty( $results );
		$this->assertSame( 302, $results['code'] );
		$this->assertSame( $url, $results['url'] );

		// Login a user and verify they can access the site.
		wp_set_current_user( 1 );
		$this->assertTrue( is_user_logged_in() );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertEmpty( $results );

		// Logout the user.
		wp_destroy_current_session();
		wp_set_current_user( 0 );
		$this->assertFalse( is_user_logged_in() );

		// Test the filter that lets us access the site.
		add_filter( 'restricted_site_access_user_can_access', '__return_true' );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertEmpty( $results );

		remove_filter( 'restricted_site_access_user_can_access', '__return_true' );
	}

	public function test_singlesite_restrict_access_restricted_whitelist() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		// Check the default results.
		$url = add_query_arg( 'redirect_to', rawurlencode( '/' ), home_url( 'wp-login.php' ) );

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );
		$this->assertSame( 302, $results['code'] );
		$this->assertSame( $url, $results['url'] );

		// Add our IP to the allowed list.
		$options              = $rsa::get_options( false );
		$options['allowed'][] = '127.0.0.1';

		update_option( 'rsa_options', $options );

		// Now set the client IP to one of the whitelisted IPs.
		$_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

		$this->assertSame( '127.0.0.1', $rsa::get_client_ip_address() );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertEmpty( $results );

		unset( $_SERVER['HTTP_CLIENT_IP'] );

		// Reset the site's whitelist.
		$options['allowed'] = array();
		update_option( 'rsa_option', $options );

		// TODO we'll need some way to network activate the plugin as a separate test suite
		// so we can test the RSA_IS_NETWORK define.
	}

	public function test_singlesite_restrict_access_show_them_a_page() {

		global $wp_rewrite;
		$wp_rewrite->init();

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		$options             = $rsa::get_options( false );
		$options['approach'] = 4; // Show them a page.
		$options['page']     = 99999; // First test with an invalid page.

		update_option( 'rsa_options', $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		// An invalid page should redirect to the login page.
		$url = add_query_arg( 'redirect_to', rawurlencode( '/' ), home_url( 'wp-login.php' ) );

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );
		$this->assertSame( 302, $results['code'] );
		$this->assertSame( $url, $results['url'] );

		// Now test it with a valid page.
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Restrcted Landing Page',
				'post_status' => 'publish',
			)
		);

		$this->assertGreaterThan( 0, $page_id );

		$options['page'] = $page_id;

		update_option( 'rsa_options', $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		// We should be redirected to the landing page.
		$this->assertNotEmpty( $results );

		$this->assertArrayNotHasKey( 'die_code', $results );

		$this->assertArrayHasKey( 'code', $results );
		$this->assertArrayHasKey( 'url', $results );

		$this->assertSame( 302, $results['code'] );
		$this->assertSame( get_permalink( $page_id ), $results['url'] );

		// Go to the landing page.
		$this->go_to( get_permalink( $page_id ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		// We should not be redirected since we're already on that page.
		$this->assertEmpty( $results );

		// Now turn on nice permalinks.
		update_option( 'permalink_structure', '/%postname%/' );
		$wp_rewrite->init();

		// Go to the landing page.
		$this->go_to( get_permalink( $page_id ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		// We should not be redirected since we're already on that page.
		$this->assertEmpty( $results );

		// Reset permalinks.
		update_option( 'permalink_structure', '/%postname%/' );
		$wp_rewrite->init();
	}

	public function test_add_remove_set_ips() {
		$rsa       = Restricted_Site_Access::get_instance();
		$my_ip     = '127.0.0.1';
		$not_my_ip = '10.9.8.7';

		$options = $rsa::get_options();
		$rsa::set_ips( array() ); // Remove all IPs.
		$this->assertEmpty( $options['allowed'] );

		$rsa::add_ips( array( $my_ip, $not_my_ip ) ); // Add two IPs.
		$options = $rsa::get_options();
		$this->assertContains( $my_ip, $options['allowed'] );

		$rsa::remove_ips( array( $my_ip ) ); // Remove one IP.
		$options = $rsa::get_options();
		$this->assertNotContains( $my_ip, $options['allowed'] );

		$rsa::set_ips( array() ); // Remove all IPs.
		$options = $rsa::get_options();
		$this->assertEmpty( $options['allowed'] );
	}

	public function test_singlesite_restrict_access_show_them_a_message() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		$options             = $rsa::get_options( false );
		$options['approach'] = 3; // Show them a message.
		$options['message']  = 'You shall not pass!';

		update_option( 'rsa_options', $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );

		$this->assertArrayNotHasKey( 'url', $results );
		$this->assertArrayHasKey( 'die_code', $results );
		$this->assertArrayHasKey( 'die_title', $results );
		$this->assertArrayHasKey( 'die_message', $results );

		$this->assertSame( 403, $results['die_code'] );
		$this->assertSame( get_bloginfo( 'name' ) . ' - Site Access Restricted', $results['die_title'] );
		$this->assertStringContainsString( 'You shall not pass!', $results['die_message'] );
	}

	public function test_singlesite_restrict_access_redirect_to_url() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		$options                  = $rsa::get_options( false );
		$options['approach']      = 2; // Redirect them to a specified web address.
		$options['redirect_url']  = 'https://10up.com';
		$options['redirect_path'] = 0;
		$options['head_code']     = 301;

		update_option( 'rsa_options', $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );
		$this->assertArrayNotHasKey( 'die_code', $results );
		$this->assertArrayHasKey( 'code', $results );
		$this->assertArrayHasKey( 'url', $results );

		$this->assertSame( 301, $results['code'] );
		$this->assertSame( 'https://10up.com', $results['url'] );

		// Update the site options.
		$options                  = $rsa::get_options( false );
		$options['redirect_url']  = 'https://10up.com';
		$options['redirect_path'] = 1; // Send them to the same path at the new URL.
		$options['head_code']     = 302;

		update_option( 'rsa_options', $options );

		// Go to the home page.
		$this->go_to( home_url( '/custom-page' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );

		$this->assertSame( 302, $results['code'] );
		$this->assertSame( 'https://10up.com/custom-page', $results['url'] );
	}

	/**
	 * Put the site into the configuration that exercises the redirect loop
	 * prevention cookie: restricted, redirecting, and preserving the path.
	 */
	private function set_up_redirect_with_path() {

		$rsa = Restricted_Site_Access::get_instance();

		update_option( 'blog_public', 2 );

		$options                  = $rsa::get_options( false );
		$options['approach']      = 2; // Redirect them to a specified web address.
		$options['redirect_url']  = 'https://fueled.com';
		$options['redirect_path'] = 1; // Send them to the same path at the new URL.
		$options['head_code']     = 302;
		$options['allowed']       = array();

		update_option( 'rsa_options', $options );
	}

	/**
	 * A cookie built the way the plugin used to build it must no longer be
	 * accepted.
	 *
	 * The old value was an unkeyed MD5 over a URL the visitor already knows and
	 * wp_create_nonce( 'redirection_nonce' ), which is identical for every
	 * logged out visitor. Anyone able to reproduce that value could mint a
	 * cookie for any URL on the site and have the restricted page served.
	 */
	public function test_singlesite_redirect_cookie_rejects_legacy_nonce_construction() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->set_up_redirect_with_path();

		$this->go_to( home_url( '/custom-page' ) );
		$wp = $GLOBALS['wp'];

		$request_uri = $rsa::get_request_uri( $wp );

		$_COOKIE['wp-rsa_redirect'] = md5(
			'rsa_redirect:' . trailingslashit( home_url( $request_uri ) ) . wp_create_nonce( 'redirection_nonce' )
		);

		$results = $rsa::restrict_access_check( $wp );

		unset( $_COOKIE['wp-rsa_redirect'] );

		// The visitor is still sent away rather than served the restricted page.
		$this->assertNotEmpty( $results );
		$this->assertSame( 'https://fueled.com/custom-page', $results['url'] );
		$this->assertNotSame( home_url( $request_uri ), $results['url'] );
	}

	/**
	 * A cookie the plugin never issued must not be accepted.
	 */
	public function test_singlesite_redirect_cookie_rejects_arbitrary_value() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->set_up_redirect_with_path();

		$this->go_to( home_url( '/custom-page' ) );
		$wp = $GLOBALS['wp'];

		$request_uri = $rsa::get_request_uri( $wp );

		$_COOKIE['wp-rsa_redirect'] = str_repeat( 'a', 64 );

		$results = $rsa::restrict_access_check( $wp );

		unset( $_COOKIE['wp-rsa_redirect'] );

		$this->assertNotEmpty( $results );
		$this->assertSame( 'https://fueled.com/custom-page', $results['url'] );
		$this->assertNotSame( home_url( $request_uri ), $results['url'] );
	}

	/**
	 * A cookie the plugin did issue must still break the redirect loop, or the
	 * visitor is bounced between the site and the redirect target forever.
	 */
	public function test_singlesite_redirect_cookie_still_prevents_the_loop() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->set_up_redirect_with_path();

		$this->go_to( home_url( '/custom-page' ) );
		$wp = $GLOBALS['wp'];

		$request_uri = $rsa::get_request_uri( $wp );

		$_COOKIE['wp-rsa_redirect'] = $this->call_private_method(
			$rsa,
			'generate_redirection_cookie',
			array( home_url( $request_uri ) )
		);

		$results = $rsa::restrict_access_check( $wp );

		unset( $_COOKIE['wp-rsa_redirect'] );

		// The redirect target becomes the current URL, which is what stops the loop.
		$this->assertNotEmpty( $results );
		$this->assertSame( home_url( $request_uri ), $results['url'] );
	}

	/**
	 * A cookie sent as an array must not be passed to hash_equals(), which
	 * raises a TypeError on anything that is not a string.
	 */
	public function test_singlesite_redirect_cookie_rejects_non_string_value() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->set_up_redirect_with_path();

		$this->go_to( home_url( '/custom-page' ) );
		$wp = $GLOBALS['wp'];

		$_COOKIE['wp-rsa_redirect'] = array( 'first', 'second' );

		$results = $rsa::restrict_access_check( $wp );

		unset( $_COOKIE['wp-rsa_redirect'] );

		$this->assertNotEmpty( $results );
		$this->assertSame( 'https://fueled.com/custom-page', $results['url'] );
	}
}
