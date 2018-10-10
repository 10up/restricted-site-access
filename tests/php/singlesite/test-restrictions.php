<?php

class Restricted_Site_Access_Test_Restrictions extends WP_UnitTestCase {

	public function test_restrict_access_not_restricted() {

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

	public function test_restrict_access_restricted_default() {

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
	}

	public function test_restrict_access_restricted_whitelist() {

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
		$options = $rsa::get_options( false );
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
		$options['allowed'] = [];
		update_option( 'rsa_option', $options );

		// TODO we'll need some way to network activate the plugin as a separate test suite
		// so we can test the RSA_IS_NETWORK define.
	}

	public function test_restrict_access_show_them_a_page() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		$options = $rsa::get_options( false );
		$options['approach'] = 4; // Show them a page.
		$options['page'] = 99999; // First test with an invalid page.

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
		$page_id = $post_id = self::factory()->post->create(
			[
				'post_type' => 'page',
				'post_title' => 'Restrcted Landing Page',
				'post_status' => 'publish',
			]
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

		update_option( 'permalink_structure', '/%postname%/' );

		flush_rewrite_rules();

		// Go to the landing page.
		$this->go_to( get_permalink( $page_id ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		// We should not be redirected since we're already on that page.
		$this->assertEmpty( $results );

		// Reset permalinks.
		update_option( 'permalink_structure', '/%postname%/' );

		flush_rewrite_rules();
	}

	public function test_restrict_access_show_them_a_message() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		$options = $rsa::get_options( false );
		$options['approach'] = 3; // Show them a message.
		$options['message'] = 'You shall not pass!';

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
		$this->assertContains( 'You shall not pass!', $results['die_message'] );
	}
}
