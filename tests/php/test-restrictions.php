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

		if ( is_multisite() ) {
			update_site_option( 'rsa_options', $options );
		} else {
			update_option( 'rsa_options', $options );
		}

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

		if ( is_multisite() ) {
			update_site_option( 'rsa_options', $options );
		} else {
			update_option( 'rsa_options', $options );
		}

	}

}
