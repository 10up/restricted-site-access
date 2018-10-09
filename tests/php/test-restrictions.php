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

		// Add some allowed IPs.
		$options = $rsa::get_options( is_multisite() );
		$options['allowed'][] = '127.0.0.1';

		rsa_tests_update_options( $options );

		$options = $rsa::get_options( is_multisite() );
		$this->assertContains( '127.0.0.1', $options['allowed'] );

		// Now set the client IP to one of the whitelisted IPs.
		$_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertEmpty( $results );

		unset( $_SERVER['HTTP_CLIENT_IP'] );

		$options['allowed'][] = [];

		rsa_tests_update_options( $options );
	}

	public function test_restrict_access_show_them_a_page() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set site to to restricted.
		update_option( 'blog_public', 2 );

		$options = $rsa::get_options( is_multisite() );
		$options['approach'] = 4; // Show them a page.
		$options['page'] = 99999; // First test with an invalid page.

		rsa_tests_update_options( $options );

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
		$page_id = 	$post_id = self::factory()->post->create(
			[
				'post_type' => 'page',
				'post_title' => 'Restrcted Landing Page',
				'post_status' => 'publish',
			]
		);

		$this->assertGreaterThan( 0, $page_id );

		$options['page'] = $page_id;
		rsa_tests_update_options( $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		// We should be redirected to the landing page.
		$this->assertNotEmpty( $results );
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

		$options = $rsa::get_options( is_multisite() );
		$options['approach'] = 3; // Show them a message.
		$options['message'] = 'You shall not pass!';

		rsa_tests_update_options( $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );
		$this->assertSame( 403, $results['die_code'] );
		$this->assertSame( get_bloginfo( 'name' ) . ' - Site Access Restricted', $results['die_title'] );
		$this->assertContains( 'You shall not pass!', $results['die_message'] );
	}
}
